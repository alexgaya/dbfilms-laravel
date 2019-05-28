<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Language;
use App\Helpers\myHelpers;

class LanguageController extends Controller
{
    public function index() {
        $languages = Language::all();
        $data = myHelpers::data("success", 200, "OK");
        $data['languages'] = $languages;
        return response()->json($data, $data['code']);
    }
}
