<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Film;
use App\Helpers\JwtAuth;
use App\User;
use App\Helpers\myHelpers;

class FilmController extends Controller {

//    public function __construct() {
//        $this->middleware('api.auth');
//    }

    public function index() {

        $films = Film::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'films' => $films
        ]);
    }

    public function show($id) {
        $film = Film::find($id);

//                ->load('user');

        if (is_object($film)) {
            $film->load('user');
            $data = [
                'code' => 200,
                'status' => 'success',
                'film' => $film
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La entrada no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Conseguir el usuario identificado
            $user = $this->getIdentity($request);

            if ($user->perms == 1) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                            //'description' => 'required',
                            //'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el film, faltan datos'
                ];
            } else {
                $film = new Film();
                $film->user_id = $user->sub;

                $film->name = $params->name;
//                $film->description = $params->description;
//                $film->image = $params->image;
                $film->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'film' => $film
                ];
            }

            // Guardar el artículo (post)
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envía los datos correctamente'
            ];
        }

        // Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        // Datos para devolver
        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectamente'
        ];

        if (!empty($params_array)) {

            // Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'description' => 'required'
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }


            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // COnseguir usuario identificado
            $user = $this->getIdentity($request);

            /* if($user->sub != $params_array['user_id']) {
              return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
              } */


            // Buscar el registro
            $film = Film::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if ((!empty($film) && is_object($film) && $user->perms == 2) || $user->perms == 3) {
                // Actualizar el registro en concreto
                $film->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'film' => $film,
                    'changes' => $params_array
                ];
            }

            /* $where = [
              'id' => $id,
              'user_id' => $user->sub
              ];
              $post = Post::updateOrCreate($where, $params_array); */
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        // Conseguir usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir el registro
        $film = Film::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if ((!empty($film) && $user->perms == 2) || $user->perms == 3) {

            // Borrar el registro
            $film->delete();

            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'film' => $film
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'This film does not exist'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger la imagen de la petición
        $image = $request->file('file0');

        // Validar imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        // Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);

            // Devolver la imagen
            return new Response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        // Mostrar posible error
        return response()->json($data, $data['code']);
    }

    public function getFilmsByUser($id) {
        $films = Film::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'films' => $films
                        ], 200);
    }

//    public function getLikedFilmsByUser($id, Request $request) {
//        $user = $this->getIdentity($request);
//        
//        if ($user->sub != $id && $user->perms != 3)
//            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
//        
//        $films = User::find($id)->load('likedFilms');
//        
//        if(empty($films)) {
//            $data = myHelpers::data("error", 404, "Not found");
//        } else {
//            $data = myHelpers::data("success", 200, "Found");
//            $data["films"] = $films;
//        }
//        return response()->json($data, $data["code"]);
//    }

    public function getLikedFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('likedFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }
    
    public function getSeenFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('seenFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }
    
    public function getPendingFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('pendingFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }
    
    public function getFavouriteFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('favouriteFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }

    private function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

}
