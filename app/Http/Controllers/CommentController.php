<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;
use App\Helpers\myHelpers;
use App\Helpers\JwtAuth;

class CommentController extends Controller {

    public function getComments(Request $request) {
        $params_array = $request->query();
        $data = myHelpers::data("error", 400, "Content required.");
        if (empty($params_array))
            return response()->json($data, $data['code']);

        $target = $this->getTarget($params_array);

        if (!$target)
            return response()->json($data, $data['code']);

        $comments = Comment::where($target['toSearch'], $target['target'])
                ->where('user_id', $params_array['user_id'])
                ->latest()
                ->first();

        /* if (count($comments) < 1) {
          $data = myHelpers::data("error", 404, "There are no comments in this page");
          } else { */
//            foreach($comments as $comment) {
//                $comment->load('user');
//            }
        $comments->load('user');
        $data = myHelpers::data("success", 200, "OK");
        $data['comments'] = $comments;
        //}
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
//        $json = $request->input('json', null);
//        $params_array = json_decode($json, true);
//
//        if (empty($params_array)) {
//            $data = myHelpers::data("error", 400, "Bad request");
//            return response()->json($data, $data['code']);
//        }
//
//        $validate = \Validator::make($params_array, [
//                    'comment_id' => 'required'
//        ]);
//
//        if ($validate->fails()) {
//            $data = [
//                'code' => 400,
//                'status' => 'error',
//                'message' => 'Content required'
//            ];
//            $data['errors'] = $validate->errors();
//            return response()->json($data, $data['code']);
//        }
//        $comment = Comment::find($params_array['comment_id']);
//        
//        if (is_object($comment) && $comment->delete()){
//            $data = myHelpers::data("success", 200, "OK");
//        } else {
//            $data = myHelpers::data("error", 500, "Server Error. Please try again.");
//        }
//        
//        return response()->json($data, $data['code']);
        
        $user = $this->getIdentity($request);

        // Conseguir el registro
        $comment = Comment::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();
        
        if (!empty($comment) && is_object($comment) && $comment != null && ($user->sub == $comment->user_id || $user->perms == 3)) {

            // Borrar el registro
            $comment->delete();

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
                'message' => 'This comment does not exist'
            ];
        }

        return response()->json($data, $data['code']);
    }

    private function getTarget($params) {
        if (array_key_exists('film_id', $params)) {
            return [
                'toSearch' => 'film_id',
                'target' => $params['film_id']
            ];
        }

        if (array_key_exists('serie_id', $params)) {
            return [
                'toSearch' => 'serie_id',
                'target' => $params['serie_id']
            ];
        }

        if (array_key_exists('chapter_id', $params)) {
            return [
                'toSearch' => 'chapter_id',
                'target' => $params['chapter_id']
            ];
        }

        return 0;
    }
    
    private function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

}
