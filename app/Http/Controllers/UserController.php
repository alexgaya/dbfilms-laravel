<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
use Illuminate\Validation\Rule;
use App\Helpers\JwtAuth;
use App\Helpers\myHelpers;
use App\UserFilm;
use App\UserSerie;
use App\Film;
use App\Serie;

class UserController extends Controller {

    public function register(Request $request) {

        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));

        $data = $this->data('error', 400, 'El usuario no se ha creado');

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'nick' => 'required',
                        'email' => 'required|email|unique:User',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
                $user_created = $this->setUser($params_array);
            }
        }

        if (isset($user_created) && $user_created) {
            $data = $this->data('success', 200, 'El usuario se ha creado correctamente');
            $data['user'] = $user_created;
        }

        return response()->json($data, $data['code']);
    }

    private function data($status, $code, $message) {

        $data = [
            'status' => $status,
            'code' => $code,
            'message' => $message
        ];

        return $data;
    }

    private function setUser($params) {
        $pwd = hash('sha256', $params['password']);

        $user = new User();
        $user->nick = $params['nick'];
        $user->email = $params['email'];
        $user->password = $pwd;
        $user->banned = 0;
        $user->hidden = 0;
        $user->status = 1;
        $user->perms = 1;
        //$user->role = 'ROLE_USER';

        if ($user->save()) {
            return $user;
        } else {
            return false;
        }
    }

    public function login(Request $request) {

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = $this->data('error', 400, 'Error con las credenciales');

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'email' => 'required|email',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
                $jwtAuth = new \JwtAuth();

                $email = $params_array['email'];
                $pwd = hash('sha256', $params_array['password']);

                $data = (!empty($params_array['getToken'])) ? $jwtAuth->signup($email, $pwd, true) : $jwtAuth->signup($email, $pwd);
            }
        }
        return response()->json($data, $data['code']);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = $this->data('error', 400, 'El usuario no está identificado');

        if ($checkToken && !empty($params_array)) {

            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array, [
                        'nick' => 'required',
                        'email' => [
                            'required',
                            'email',
                            Rule::unique('users')->ignore($user->sub),
                        ],
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
                unset($params_array['id']);
                //unset($params_array['role']);
                unset($params_array['perms']);
                unset($params_array['created_at']);
                unset($params_array['remember_token']);
                unset($params_array['password']);

                $user_update = User::where(['id' => $user->sub])->update($params_array);
            }
        }

        if (isset($user_update) && $user_update) {
            $data = $this->data('success', 200, 'Los cambios se han guardado');
            $data['user'] = $user;
            $data['changes'] = $params_array;
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger los datos de la petición
        $image = $request->file('file0');

        // Validación de la imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen
        if (!$image || $validate->fails()) {
            $data = $this->data('error', 400, 'Error al subir imagen');
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = $this->data('success', 200, $image_name);
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = $this->data('error', 404, 'La imagen no existe');

            return response()->json($data, $data['code']);
        }
    }

    public function detail($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = $this->data('success', 200, 'Usuario encontrado');
            $data['user'] = $user;
        } else {
            $data = $this->data('error', 404, 'El usuario no existe');
        }

        return response()->json($data, $data['code']);
    }

//    public function getFavouritePosts(Request $request) {
//        $token = $request->header('Authorization');
//
//        $jwtAuth = new \JwtAuth();
//        $user = $jwtAuth->checkToken($token, true);
//
//        $posts = User::find($user->sub)->favourite()->get();
//
//        $data = $this->data('success', 200, 'OK');
//        $data['posts'] = $posts;
//
//        return response()->json($data, $data['code']);
//    }

    public function likeFilm($id, Request $request) {
        
        $filmExists = Film::find($id);
        
        if(empty($filmExists))
            return response()->json(["status" => "error", "message" => "This film does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserFilm::where('film_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, liked");
        
        if (empty($check)) {
            // Crear + like 1
            $userFilm = new UserFilm();
            $userFilm->user_id = $user->sub;
            $userFilm->film_id = $id; 
            $userFilm->like = 1;
            $userFilm->save();
        } else if (!empty($check) && $check->like) {
            // like 0
            $check->like = 0;
            $check->save();
            $data["message"] = "Changes saved, unliked";
        } else if (!empty($check) && !$check->like) {
            // like 1
            $check->like = 1;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function favouriteFilm($id, Request $request) {
        $filmExists = Film::find($id);
        
        if(empty($filmExists))
            return response()->json(["status" => "error", "message" => "This film does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserFilm::where('film_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, fav");
        
        if (empty($check)) {
            // Crear + like 1
            $userFilm = new UserFilm();
            $userFilm->user_id = $user->sub;
            $userFilm->film_id = $id; 
            $userFilm->favourite = 1;
            $userFilm->save();
        } else if (!empty($check) && $check->favourite) {
            // like 0
            $check->favourite = 0;
            $check->save();
            $data["message"] = "Changes saved, unfav";
        } else if (!empty($check) && !$check->favourite) {
            // like 1
            $check->favourite = 1;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function pendingFilm($id, Request $request) {
        $filmExists = Film::find($id);
        
        if(empty($filmExists))
            return response()->json(["status" => "error", "message" => "This film does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserFilm::where('film_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, pending");
        
        if (empty($check)) {
            $userFilm = new UserFilm();
            $userFilm->user_id = $user->sub;
            $userFilm->film_id = $id; 
            $userFilm->pending = 1;
            $userFilm->save();
        } else if (!empty($check) && $check->pending) {
            $check->pending = 0;
            $check->save();
            $data["message"] = "Changes saved, unpending";
        } else if (!empty($check) && !$check->pending) {
            $check->pending = 1;
            $check->seen = 0;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function seenFilm($id, Request $request) {
        $filmExists = Film::find($id);
        
        if(empty($filmExists))
            return response()->json(["status" => "error", "message" => "This film does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserFilm::where('film_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, seen");
        
        if (empty($check)) {
            $userFilm = new UserFilm();
            $userFilm->user_id = $user->sub;
            $userFilm->film_id = $id; 
            $userFilm->seen = 1;
            $userFilm->save();
        } else if (!empty($check) && $check->seen) {
            $check->seen = 0;
            $check->save();
            $data["message"] = "Changes saved, unseen";
        } else if (!empty($check) && !$check->seen) {
            $check->seen = 1;
            $check->pending = 0;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function likeSerie($id, Request $request) {
        
        $serieExists = Serie::find($id);
        
        if(empty($serieExists))
            return response()->json(["status" => "error", "message" => "This serie does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserSerie::where('serie_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, liked");
        
        if (empty($check)) {
            // Crear + like 1
            $userSerie = new UserSerie();
            $userSerie->user_id = $user->sub;
            $userSerie->serie_id = $id; 
            $userSerie->like = 1;
            $userSerie->save();
        } else if (!empty($check) && $check->like) {
            // like 0
            $check->like = 0;
            $check->save();
            $data["message"] = "Changes saved, unliked";
        } else if (!empty($check) && !$check->like) {
            // like 1
            $check->like = 1;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function favouriteSerie($id, Request $request) {
        $serieExists = Serie::find($id);
        
        if(empty($serieExists))
            return response()->json(["status" => "error", "message" => "This serie does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserSerie::where('serie_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, fav");
        
        if (empty($check)) {
            // Crear + like 1
            $userSerie = new UserSerie();
            $userSerie->user_id = $user->sub;
            $userSerie->serie_id = $id; 
            $userSerie->favourite = 1;
            $userSerie->save();
        } else if (!empty($check) && $check->favourite) {
            // like 0
            $check->favourite = 0;
            $check->save();
            $data["message"] = "Changes saved, unfav";
        } else if (!empty($check) && !$check->favourite) {
            // like 1
            $check->favourite = 1;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function pendingSerie($id, Request $request) {
        $serieExists = Serie::find($id);
        
        if(empty($serieExistseExists))
            return response()->json(["status" => "error", "message" => "This serie does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserSerie::where('serie_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, pending");
        
        if (empty($check)) {
            $userSerie = new UserSerie();
            $userSerie->user_id = $user->sub;
            $userSerie->serie_id = $id; 
            $userSerie->pending = 1;
            $userSerie->save();
        } else if (!empty($check) && $check->pending) {
            $check->pending = 0;
            $check->save();
            $data["message"] = "Changes saved, unpending";
        } else if (!empty($check) && !$check->pending) {
            $check->pending = 1;
            $check->seen = 0;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    
    public function seenSerie($id, Request $request) {
        $serieExists = Serie::find($id);
        
        if(empty($serieExists))
            return response()->json(["status" => "error", "message" => "This serie does not exist"], 404);
        
        $user = $this->getIdentity($request);
        
        $check = UserSerie::where('serie_id', $id)
                ->where('user_id', $user->sub)
                ->first();
 
        $data = myHelpers::data("success", 200, "Changes saved, seen");
        
        if (empty($check)) {
            $userSerie = new UserSerie();
            $userSerie->user_id = $user->sub;
            $userSerie->serie_id = $id; 
            $userSerie->seen = 1;
            $userSerie->save();
        } else if (!empty($check) && $check->seen) {
            $check->seen = 0;
            $check->save();
            $data["message"] = "Changes saved, unseen";
        } else if (!empty($check) && !$check->seen) {
            $check->seen = 1;
            $check->pending = 0;
            $check->save();
        } else {
            $data = myHelpers::data("error", 404, "Error");
        }
        
        return response()->json($data, $data['code']);
    }
    

    private function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    /* public function getFavouriteFilms(Request $request) {
      $token = $request->header('Authorization');

      $jwtAuth = new \JwtAuth();
      $user = $jwtAuth->checkToken($token, true);

      $films = \Illuminate\Support\Facades\DB::table('posts')
      ->join('categories', function ($join) {
      $join->on('posts.category_id', '=', 'categories.id')
      ->where('categories.id', '=', 1);
      })
      ->get();

      $films_user = \Illuminate\Support\Facades\DB::table('user_favourite_post')
      ->select('post_id')
      ->where('user_id', $user->sub)
      ->get();
      $asd = null;
      foreach ($films_user as $e) {
      $asd .= \Illuminate\Support\Facades\DB::table('posts')
      ->select('*')
      ->where('id', $e->post_id)
      ->get();
      }


      return response()->json($asd, 200);


      } */
}
