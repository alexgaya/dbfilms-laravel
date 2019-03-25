<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller {

    public function index() {
        $categories = Category::all();
        return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'categories' => $categories
                        ], 200);
    }

    public function show($id) {
        $category = Category::find($id);

        $data = \myHelpers::data('error', 404, 'La categoría no existe');

        if (is_object($category)) {
            $data = \myHelpers::data('success', 200, 'OK');
            $data['category'] = $category;
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = \myHelpers::data('error', 404, 'No has enviado ninguna categoría');


        if (!empty($params_array)) {
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {
                $category_created = $this->setCategory($params_array['name']);
            }
        }

        if (isset($category_created) && $category_created) {
            $data = \myHelpers::data('success', 200, 'OK');
            $data['category'] = $category_created;
        }

        return response()->json($data, $data['code']);
    }

    private function setCategory($name) {
        $category = new Category();
        $category->name = $name;


        if ($category->save()) {
            return $category;
        } else {
            return false;
        }
    }

    public function update($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = \myHelpers::data('error', 400, 'No has enviado ninguna categoría');

        if (!empty($params_array)) {
            
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            if ($validate->fails()) {
                $data['error'] = $validate->errors();
            } else {

                unset($params_array['id']);
                unset($params_array['created_at']);

                $category_update = Category::where('id', $id)->update($params_array);
            }
        }
        
        if (isset($category_update) && $category_update) {
            $data = \myHelpers::data('success', 200, 'OK');
            $data['category'] = $params_array;
        }

        return response()->json($data, $data['code']);
    }

}
