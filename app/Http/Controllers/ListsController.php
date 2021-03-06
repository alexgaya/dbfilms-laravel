<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Lists;
use App\ListFilm;
use App\ListSerie;
use App\Helpers\JwtAuth;
use App\UserFilm;
use App\UserSerie;
use App\Helpers\myHelpers;
use App\UserList;

class ListsController extends Controller {

    public function index(Request $request) {
        $user = myHelpers::getIdentity($request);
        //$lists = Lists::all()->load('filmsLimited')->load('seriesLimited');
        $lists = Lists::select('id', 'name', 'user_id')
//                ->load('filmsLimited')
//                ->load('seriesLimited')
                ->paginate(12);

        foreach ($lists as $list) {
            $list->load('filmsLimited')
                    ->load('seriesLimited');
            $list->current_user_following = UserList::where('user_id', $user->sub)->where('list_id', $list->id)->where('follow', 1)->exists();
            $list->followers_count = $list->followers()->count();
            $list->films_count = $list->films()->count();
            $list->series_count = $list->series()->count();
        }

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'lists' => $lists
        ]);
    }

    public function show($id, Request $request) {

        $list = Lists::find($id);

        if (is_object($list)) {
            $user = $this->getIdentity($request);
            $list->load('films');

            foreach ($list->films as $film) {
                if (UserFilm::where('film_id', $film->id)
                                ->where('user_id', $user->sub)
                                ->where('like', true)
                                ->exists()) {
                    $film->like = true;
                } else {
                    $film->like = false;
                }

                if (UserFilm::where('film_id', $film->id)
                                ->where('user_id', $user->sub)
                                ->where('seen', true)
                                ->exists()) {
                    $film->seen = true;
                } else {
                    $film->seen = false;
                }
            }

            $list->load('series');

            foreach ($list->series as $serie) {
                if (UserSerie::where('serie_id', $serie->id)
                                ->where('user_id', $user->sub)
                                ->where('like', true)
                                ->exists()) {
                    $serie->like = true;
                } else {
                    $serie->like = false;
                }

                if (UserSerie::where('serie_id', $serie->id)
                                ->where('user_id', $user->sub)
                                ->where('seen', true)
                                ->exists()) {
                    $serie->seen = true;
                } else {
                    $serie->seen = false;
                }
            }

            $data = [
                'code' => 200,
                'status' => 'success',
                'list' => $list
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



            // Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                            //'description' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la lista, faltan datos'
                ];
            } else {
                $list = new Lists();
                $list->user_id = $user->sub;

                $list->name = $params->name;
//                $film->description = $params->description;
                $list->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'list' => $list
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
    
    public function destroy($id, Request $request) {
        // Conseguir usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir el registro
        $list = Lists::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (!empty($list)) {

            // Borrar el registro
            $list->delete();

            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'OK'
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'This list does not exist'
            ];
        }

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

            // COnseguir usuario identificado
            $user = $this->getIdentity($request);

            /* if($user->sub != $params_array['user_id']) {
              return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
              } */


            // Buscar el registro
            $list = Lists::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if (!empty($list) && is_object($list)) {
                // Actualizar el registro en concreto
                $list->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'list' => $list,
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

    public function getUserLists(Request $request) {
        $user = $this->getIdentity($request);
        $lists = Lists::select('id', 'name', 'user_id')
                        ->where('user_id', $user->sub)->get();

        foreach ($lists as $list) {
            $list->load('films');
            $list->load('series');
        }
        $data = myHelpers::data("success", 200, "OK");
        $data['lists'] = $lists;
        return response()->json($data, $data['code']);
    }


    public function storeFilm($id, $idFilm, Request $request) {
        $user = $this->getIdentity($request);

        $check = Lists::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (empty($check) || !is_object($check))
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $list = new ListFilm();

        $list->list_id = $id;

        $list->film_id = $idFilm;

        $list->save();

        $data = [
            'code' => 200,
            'status' => 'success',
            'list' => $list
        ];

        return response()->json($data, $data['code']);
    }

    public function storeSerie($id, $idSerie, Request $request) {

        $user = $this->getIdentity($request);

        $check = Lists::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if (empty($check) || !is_object($check))
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $list = new ListSerie();

        $list->list_id = $id;

        $list->serie_id = $idSerie;

        $list->save();

        $data = [
            'code' => 200,
            'status' => 'success',
            'list' => $list
        ];

        return response()->json($data, $data['code']);
    }

    public function deleteFilm($id, $idFilm, Request $request) {
        $user = $this->getIdentity($request);

        $check = Lists::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if ((empty($check) || !is_object($check)) || $user->perms != 3)
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $film = ListFilm::where('film_id', $idFilm)
                ->where('list_id', $id)
                ->first();

        if (empty($film)) {
            $data = \myHelpers::data('error', 404, 'This film is not in the list');
            return response()->json($data, $data['code']);
        }

        $film->delete();

        $data = [
            'code' => 200,
            'status' => 'success',
            'list' => $film
        ];

        return response()->json($data, $data['code']);
    }

    public function deleteSerie($id, $idSerie, Request $request) {
        $user = $this->getIdentity($request);

        $check = Lists::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if ((empty($check) || !is_object($check)))
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

        $serie = ListSerie::where('serie_id', $idSerie)
                ->where('list_id', $id)
                ->first();

        if (empty($serie)) {
            $data = \myHelpers::data('error', 404, 'This serie is not in the list');
            return response()->json($data, $data['code']);
        }

        $serie->delete();

        $data = [
            'code' => 200,
            'status' => 'success',
            'list' => $serie
        ];

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
