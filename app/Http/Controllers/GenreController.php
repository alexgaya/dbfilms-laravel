<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Genre;
use App\Helpers\myHelpers;

class GenreController extends Controller
{
    public function getAllGenres() {
        $genres = Genre::all();
        $data = myHelpers::data("success", 200, "OK");
        $data['genres'] = $genres;
        return response()->json($data, $data['code']);
    }
}
