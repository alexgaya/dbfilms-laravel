<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {

    public function register(Request $request) {

        $json = $request->input('json', null);
        $params_array_untrimmed = json_decode($json, true);

        if (empty($params_array_untrimmed)) {
            return response()->json($this->registerError(), 400);
        }

        $params_array = array_map('trim', $params_array_untrimmed);

        $validate = \Validator::make($params_array, [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required'
        ]);

        if ($validate->fails()) {
            $data = $this->registerError();
            $data['error'] = $validate->errors();
            return response()->json($data, $data['code']);
        }
        
        $user = $this->setUser($params_array);
        
        if (!$this->registerSave($user)) {
            return response()->json($this->registerError(), 400);
        }

        return response()->json($this->registerSuccess($user), 200);
    }

    private function registerError() {
        return $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha creado'
        ];
    }

    private function registerSuccess($user) {
        return $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'El usuario se ha creado correctamente',
            'user' => $user
        ];
    }
    
    private function setUser($params) {
        $pwd = password_hash($params['password'], PASSWORD_BCRYPT, ['cost' => 4]);

        $user = new User();
        $user->name = $params['name'];
        $user->email = $params['email'];
        $user->password = $pwd;
        $user->role = 'ROLE_USER';
        
        return $user;
    }

    private function registerSave($user) {
        
        $is_saved = $user->save();

        if ($is_saved) {
            return true;
        } else {
            return false;
        }
    }

    public function login(Request $request) {
        return 'Acción de login de usuarios';
    }

}
