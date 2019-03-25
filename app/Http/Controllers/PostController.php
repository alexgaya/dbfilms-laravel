<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Post;
use App\User;

class PostController extends Controller {

    public function index() {
        
        $posts = Post::all()->load('category')
                ->load('favourite');
        
        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'posts' => $posts
        ]);
    }

    public function show($id) {
        $post = Post::find($id)->load('category')
                ->load('user');
        
        //$post->favourite()->attach(1);

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
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

}
