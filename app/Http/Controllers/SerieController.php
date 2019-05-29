<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Serie;
use App\Helpers\JwtAuth;
use App\UserSerie;
use App\Helpers\myHelpers;
use App\Comment;
use Illuminate\Support\Facades\DB;

class SerieController extends Controller {

    public function index(Request $request) {
        $user = $this->getIdentity($request);
        $series = Serie::select('user_id', 'id', 'name', 'image')->paginate(12);

        foreach ($series as $serie) {
            if (UserSerie::where('serie_id', $serie->id)
                            ->where('user_id', $user->sub)
                            ->where('seen', true)
                            ->exists()) {
                $serie->seen = true;
            } else {
                $serie->seen = false;
            }
        }
        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'series' => $series
                        ], 200);
    }

    public function getSeriesWithoutPaginate(Request $request) {
        $user = $this->getIdentity($request);
        $series = Serie::select('user_id', 'id', 'name')
                ->where('user_id', $user->sub)
                ->get();
        $data = myHelpers::data("success", 200, "OK");
        $data['series'] = $series;
        return response()->json($data, $data['code']);
    }

    public function show($id, Request $request) {
        $user = $this->getIdentity($request);
        $serie = Serie::find($id);

        if (is_object($serie)) {
            $serie->load('user');
            $serie->load('genre');
            $serie->load('comment');
            $serie->load('chapters');

            foreach ($serie->comment as $comment) {
                $comment->load('user');
            }
            $seasons = 0;
            foreach ($serie->chapters as $chapter) {
                if ($chapter->season > $seasons)
                    $seasons = $chapter->season;
            }

            $serie->seasons = $seasons;

            if (UserSerie::where('serie_id', $serie->id)
                            ->where('user_id', $user->sub)
                            ->where('favourite', true)
                            ->exists()) {
                $serie->fav = true;
            } else {
                $serie->fav = false;
            }

            $data = [
                'code' => 200,
                'status' => 'success',
                'serie' => $serie
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
                        'genres' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Serie not saved, missing data'
                ];
            } else {
                $serie = new Serie();
                $serie->user_id = $user->sub;
                $serie->name = $params->name;
                $serie->description = !empty($params->description) ? $params->description : null;
                $serie->image = !empty($params->image) ? $params->image : null;
                $serie->trailer = !empty($params->trailer) ? $params->trailer : null;
                $serie->save();


                if (!empty($params->genres) && count($params->genres) > 0) {
                    foreach ($params->genres as $genre) {
                        DB::table('Genre_has_Serie')
                                ->insert([
                                    "serie_id" => $serie->id,
                                    "genre_id" => $genre
                        ]);
                    }
                }

                $serie->load("genre");

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'serie' => $serie
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
            $serie = Serie::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if (!empty($serie) && is_object($serie)) {
                // Actualizar el registro en concreto
                $serie->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'serie' => $serie,
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
        $serie = Serie::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if ((!empty($serie) && $user->perms == 2) || $user->perms == 3) {

            // Borrar el registro
            $serie->delete();

            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'serie' => $serie
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'This serie does not exist'
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

    public function getSeriesByUser($id) {
        $series = Serie::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'serie' => $series
                        ], 200);
    }

    public function getSeenSeriesByUser(Request $request) {
        $user = $this->getIdentity($request);
        $series = \Illuminate\Support\Facades\DB::table('Serie')
                ->join('User_serie', function($join) use($user) {
                    $join->on('Serie.id', '=', 'User_serie.serie_id')
                    ->where('User_serie.user_id', $user->sub)
                    ->where('User_serie.seen', true);
                })
                ->select('Serie.user_id', 'Serie.id', 'Serie.name', 'Serie.image')
                ->paginate(12);

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'series' => $series
        ]);
    }

    public function comment($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = myHelpers::data("error", 500, "Server Error, Please try again.");

        if (empty($params_array)) {
            $data = myHelpers::data("error", 400, "Content message is required.");
            return response()->json($data, $data['code']);
        }

        $validate = \Validator::make($params_array, [
                    'content' => 'required'
        ]);

        if ($validate->fails()) {
            $data = myHelpers::data("error", 400, "Content message is required.");
            $data['errors'] = $validate->errors();
            return response()->json($data, $data['code']);
        }

        $user = $this->getIdentity($request);

        $serie = Serie::find($id);


        if (empty($serie)) {
            $data = myHelpers::data("error", 404, "Serie does not exist");
        } else {
            $comment = new Comment();
            $comment->serie_id = $id;
            $comment->user_id = $user->sub;
            $comment->content = $params_array['content'];
            if ($comment->save()) {
                $data = myHelpers::data("success", 200, "Done");
            }
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        if (empty($token)) {
            $headers = apache_request_headers();
            $token = $headers['Authorization'];
        }
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

}
