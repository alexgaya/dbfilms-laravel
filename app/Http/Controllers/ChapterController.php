<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;
use App\Helpers\myHelpers;
use App\Chapter;
use App\Serie;
use App\Link;

class ChapterController extends Controller {

    public function index() {
        $chapters = Chapter::all();

        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'chapters' => $chapters
        ]);
    }

    public function show($id) {
        $chapter = Chapter::find($id);
        //->load('link');

        if (!empty($chapter) && is_object($chapter)) {
            $chapter->load('comment');
            $chapter->load('link');
            foreach ($chapter->link as $link) {
                $link->load('language');
                $link->load('user');
            }
            foreach ($chapter->comment as $comment) {
                $comment->load('user');
            }
            $data = [
                'code' => 200,
                'status' => 'success',
                'chapter' => $chapter
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
                        'serie_id' => 'required',
                        'id_ep' => 'required',
                        'season' => 'required',
                        'links' => 'required',
                        'duration' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Episode not saved, missing data.'
                ];
                $data['errors'] = $validate->errors();
            } else {

                $check = Serie::find($params->serie_id);

                if (empty($check))
                    return response()->json(['status' => 'error', 'message' => 'Serie does not exist'], 404);

//                if($check->user_id != $user->sub)
//                    return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);

                $chapter = new Chapter();
                $chapter->name = $params->name;
                $chapter->user_id = $user->sub;
                $chapter->serie_id = $params->serie_id;
                $chapter->id_ep = $params->id_ep;
                $chapter->duration = $params->duration;
                $chapter->season = $params->season;

                $chapter->save();


                if (!empty($params->links) && count($params->links) > 0) {
                    foreach ($params->links as $link) {
                        $linkk = new Link();
                        $linkk->chapter_id = $chapter->id;
                        $linkk->user_id = $user->sub;
                        $linkk->url = $link->url;
                        $linkk->language_id = $link->language->id;
                        $linkk->save();
                    }
                }

                $chapter->load("link");

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'chapter' => $chapter
                ];
            }

            // Guardar el artículo (post)
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Bad request'
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
            $chapter = Chapter::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if ((!empty($chapter) && is_object($chapter) && $user->perms == 2) || $user->perms == 3) {
                // Actualizar el registro en concreto
                $chapter->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'chapter' => $chapter,
                    'changes' => $params_array
                ];
            }
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function getAllLinksFromChapter($id) {
        $chapter = Chapter::find($id);

        if (empty($chapter)) {
            //return response()->json(['status' => 'error', 'message' => 'Chapter does not exist'], 401);
            $data = myHelpers::data("error", 404, "Chapter does not exist");
        } else {
            $links = $chapter->link;
            foreach ($links as $link) {
                $link->load("language");
            }
            $data = myHelpers::data("success", 200, "Done");
            $data["links"] = $links;
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
