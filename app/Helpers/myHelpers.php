<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;

class myHelpers {

    public static function data($status, $code, $message) {
        $data = [
            'status' => $status,
            'code' => $code,
            'message' => $message
        ];

        return $data;
    }

    public static function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }
    
    public static function checkAdmin(Request $request) {
//        $jwtAuth = new JwtAuth();
//        $token = $request->header('Authorization', null);
//        $user = $jwtAuth->checkToken($token, true);
        $user = myHelpers::getIdentity($request);
        if ($user->perms === 3) {
            return true;
        } else {
            return false;
        }
    }

}
