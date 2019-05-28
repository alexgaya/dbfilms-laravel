<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\myHelpers;
use App\User;

class AdminController extends Controller {

    public function isAdmin(Request $request) {
        $user = myHelpers::getIdentity($request);
        $isAdmin = User::find($user->sub);

        $data = myHelpers::data("error", 401, "Unauthorized");

        if ($isAdmin->perms == 3) {
            $data = myHelpers::data("success", 200, "Authorized");
        }

        return response()->json($data, $data['code']);
    }

    public function getUsers(Request $request) {
        if (!myHelpers::checkAdmin($request)) {
            $data = myHelpers::data("error", 401, "Unauthorized");
            return response()->json($data, $data['code']);
        }
        $users = User::paginate(12);

        $data = myHelpers::data("success", 200, "OK");
        $data['users'] = $users;

        return response()->json($data, $data['code']);
    }

    public function updateUser(Request $request) {
        if (!myHelpers::checkAdmin($request)) {
            $data = myHelpers::data("error", 401, "Unauthorized");
            return response()->json($data, $data['code']);
        }

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $validate = \Validator::make($params_array, [
                    'nick' => 'required',
                    'banned' => 'required',
                    'hidden' => 'required',
                    'email' => 'required',
                    'id' => 'required',
                    'status' => 'required',
                    'perms' => 'required'
        ]);

        if ($validate->fails()) {
            $data = myHelpers::data("error", 400, "validation error");
            $data['errors'] = $validate->errors();
            return response()->json($data, $data['code']);
        }


        unset($params_array['description']);
        unset($params_array['created_at']);
        unset($params_array['image']);
        unset($params_array['created_at']);
        unset($params_array['updated_at']);


        $userToUpdate = User::find($params_array['id']);
        if ($userToUpdate->update($params_array)) {
            $data = myHelpers::data("success", 200, "OK");
        } else {
            $data = myHelpers::data("error", 500, "Failed on update");
        }

        return response()->json($data, $data['code']);
    }

    public function createUser(Request $request) {
        if (!myHelpers::checkAdmin($request)) {
            $data = myHelpers::data("error", 401, "Unauthorized");
            return response()->json($data, $data['code']);
        }
        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));    
        $data = myHelpers::data('error', 400, 'Error, the user was not created. Please try again.');

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
            $data = myHelpers::data('success', 200, 'The user has been created successfully');
            $data['user'] = $user_created;
        }

        return response()->json($data, $data['code']);
    }
    
    private function setUser($params) {
        $pwd = hash('sha256', $params['password']);
        
        $user = new User();
        $user->nick = $params['nick'];
        $user->email = $params['email'];
        $user->password = $pwd;
        $user->banned = $params['banned'] ? 1 : 0;
        $user->hidden = $params['hidden'] ? 1 : 0; 
        $user->status = $params['status'] ? 1 : 0;
        $user->perms = $params['perms'];
        //$user->role = 'ROLE_USER';

        if ($user->save()) {
            return $user;
        } else {
            return false;
        }
    }

}
