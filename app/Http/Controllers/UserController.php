<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class UserController extends Controller {

    public function register(Request $request) {

        $json = $request->input('json', null);
        $params_array = array_map('trim', json_decode($json, true));

        $data = $this->data('error', 400, 'El usuario no se ha creado');

        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'email' => 'required|email|unique:users',
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
        $user->name = $params['name'];
        $user->email = $params['email'];
        $user->password = $pwd;
        $user->role = 'ROLE_USER';

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

                $data = (!empty($params_array['getToken'])) 
                        ? $jwtAuth->signup($email, $pwd, true) 
                        : $jwtAuth->signup($email, $pwd);
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

        if ($checkToken && !empty($params_array)) {

            $user = $jwtAuth->checkToken($token, true);

            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'email' => 'required|email|unique:users' . $user->sub
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no está identificado'
                ];
                return response()->json($data, $data['code']);
            }

            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);
            unset($params_array['password']);

            // Actualizar usuario en bbdd
            $user_update = User::where('id', $user->sub)->update($params_array);

            // Devolver array con el resultado
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no está identificado'
            ];
        }
        return response()->json($data, $data['code']);
    }

}
