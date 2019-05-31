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
use App\Follow;
use App\PrivMessage;
use App\UserList;

class UserController extends Controller {

    public function register(Request $request) {

        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));

        $data = $this->data('error', 400, 'Error, the user was not created. Please try again.');

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
            $data = $this->data('success', 200, 'The user has been created successfully');
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

        $data = $this->data('error', 400, 'Credentials error');

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

    public function changePassword(Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $user = $this->getIdentity($request);

        $data = $this->data('error', 400, 'Credentials error');

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'password' => 'required',
                        'newPassword' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
//                $jwtAuth = new \JwtAuth();
//
//                $email = $params_array['email'];
                $pwd = hash('sha256', $params_array['password']);
                $newPwd = hash('sha256', $params_array['newPassword']);
//                $data = (!empty($params_array['getToken'])) ? $jwtAuth->signup($email, $pwd, true) : $jwtAuth->signup($email, $pwd);
                $updateUser = User::find($user->sub);
                //var_dump([$updateUser->password, $pwd, $newPwd]); die();

                if ($updateUser->password == $pwd) {
                    $updateUser->password = $newPwd;
                    $updateUser->save();
                    $data = $this->data("success", 200, "Changes Saved");
                }
            }
        }
        return response()->json($data, $data['code']);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');
        if (empty($token)) {
            $headers = apache_request_headers();
            $token = $headers['Authorization'];
        }

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
                            Rule::unique('User')->ignore($user->sub),
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

    public function likeFilm($id, Request $request) {

        $filmExists = Film::find($id);

        if (empty($filmExists))
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

        if (empty($filmExists))
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

        if (empty($filmExists))
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

        if (empty($filmExists))
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

        if (empty($serieExists))
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

        if (empty($serieExists))
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

        if (empty($serieExistseExists))
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

        if (empty($serieExists))
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

    public function followList(Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $data = myHelpers::data("error", 404, "error");

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'list_id' => 'required'
            ]);

            //$list = Lists::find($params_array['list_id']);

//            if (empty($list)) {
//                $data['message'] = "List not found";
//                return response()->json($data, $data['code']);
//            }

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
                return response()->json($data, $data['code']);
            } else {
//                $userList = new UserList();
//                $userList->list_id = $params_array['list_id'];
//                $userList->user_id = $user->sub;

                $user = $this->getIdentity($request);

                $check = UserList::where('list_id', $params_array['list_id'])
                        ->where('user_id', $user->sub)
                        ->first();

                

                if (empty($check)) {
                    $userList = new UserList();
                    $userList->user_id = $user->sub;
                    $userList->list_id = $params_array['list_id'];
                    $userList->follow = 1;
                    $userList->save();
                    $data = myHelpers::data("success", 200, "Changes saved, followed");
                } else if (!empty($check) && $check->follow) {
                    $check->follow = 0;
                    $check->save();
                    $data = myHelpers::data("success", 200, "Changes saved, unfollowed");
                } else if (!empty($check) && !$check->seen) {
                    $check->follow = 1;
                    $check->save();
                    $data = myHelpers::data("success", 200, "Changes saved, followed");
                } else {
                    $data = myHelpers::data("error", 404, "Error");
                }
            }
        }
        
        return response()->json($data, $data['code']);
    }

    public function getFollowingList(Request $request) {
        $user = $this->getIdentity($request);

        $usersFollowing = [];
        $following = Follow::All()->where('user_id', $user->sub);
        foreach ($following as $f) {
            array_push($usersFollowing, $f->userFollowed);
        }

        $data = myHelpers::data("success", 200, "OK");
        $data['following'] = $usersFollowing;
        return response()->json($data, $data['code']);
    }

    public function getFollowersList(Request $request) {
        $user = $this->getIdentity($request);

        $usersFollowers = [];
        $following = Follow::All()->where('user_followed', $user->sub);
        foreach ($following as $f) {
            array_push($usersFollowers, $f->userFollowMe);
        }

        $data = myHelpers::data("success", 200, "OK");
        $data['followers'] = $usersFollowers;
        return response()->json($data, $data['code']);
    }

    public function follow($id, Request $request) {
        $userExists = User::find($id);

        if (empty($userExists))
            return response()->json(["status" => "error", "message" => "This user does not exist"], 404);

        $user = $this->getIdentity($request);

        $check = Follow::where('user_id', $user->sub)
                ->where('user_followed', $id)
                ->first();

        $data = myHelpers::data("success", 200, "Followed $userExists->nick");

        if (empty($check)) {
            $follow = new Follow();
            $follow->user_id = $user->sub;
            $follow->user_followed = $id;
            $follow->save();
        } else {
            $check->delete();
            $data = myHelpers::data("success", 200, "Unfollowed $userExists->nick");
        }

        return response()->json($data, $data['code']);
    }

    public function sendPrivateMessage($id, Request $request) {
        $userExists = User::find($id);

        if (empty($userExists))
            return response()->json(["status" => "error", "message" => "This user does not exist"], 404);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        $user = $this->getIdentity($request);

        $data = $this->data('error', 400, 'error');

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'text' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
                $privMes = new PrivMessage();
                $privMes->sender_id = $user->sub;
                $privMes->receiver_id = $id;
                $privMes->text = $params_array['text'];
                $privMes->save();
                $data = myHelpers::data("success", 200, "Private message send to $userExists->nick");
            }
        }
        return response()->json($data, $data['code']);
    }

    public function getMainData(Request $request) {
        $user = $this->getIdentity($request);
        $unReadMessages = User::find($user->sub)
                ->unReadMessages;


        foreach ($unReadMessages as $mess) {
            $mess->load('senderUser');
        }

        $readMessages = User::find($user->sub)->readMessages;

        foreach ($readMessages as $readMess) {
            $readMess->load('senderUser');
        }


//        $usersFollowing = [];
//        $following = Follow::All()->where('user_id', $user->sub);
//        foreach($following as $f){
//            array_push($usersFollowing, $f->userFollowed);
//        }

        $countFollowing = Follow::where('user_id', $user->sub)->count();

        $countFollowers = Follow::where('user_followed', $user->sub)->count();

        $data = myHelpers::data("success", 200, "OK");
        $data['unReadMessages'] = $unReadMessages;
        $data['readMessages'] = $readMessages;
        $data['countFollowing'] = $countFollowing;
        $data['countFollowers'] = $countFollowers;
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
