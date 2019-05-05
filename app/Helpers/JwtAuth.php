<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth {

    private $key;

    public function __construct() {
        $this->key = '297131hj&/(")1..2138!"@@¨{w12_232-J|ººº]JWhjjjw7762LÑÑ´l';
    }

    public function signup($email, $password, $getToken = null) {

        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();

        $signup = false;

        if (is_object($user)) {
            $signup = true;
        }

        if ($signup) {
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'perms' => $user->perms,
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24 * 7) // 1 week
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            if (is_null($getToken)) {
                //$data = $jwt;
                $data = [
                    'token' => $jwt,
                    'code' => 200,
                    'status' => 'success'
                ];
            } else {
                //$data = $decoded;
                $data = [
                    'token_decoded' => $decoded,
                    'code' => 200,
                    'status' => 'success'
                ];
            }
        } else {

            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Credenciales incorrectas'
            ];
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;
        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }

}
