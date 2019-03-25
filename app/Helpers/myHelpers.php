<?php

namespace App\Helpers;

class myHelpers {


    public static function data($status, $code, $message) {
        $data = [
            'status' => $status,
            'code' => $code,
            'message' => $message
        ];

        return $data;
    }

}
