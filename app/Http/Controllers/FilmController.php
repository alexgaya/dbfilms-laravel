<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Film;
use App\Helpers\JwtAuth;
use App\User;
use App\Comment;
use App\Helpers\myHelpers;
use App\UserFilm;
use Illuminate\Support\Facades\DB;
use App\Link;

class FilmController extends Controller {

    public function index(Request $request) {
        $user = $this->getIdentity($request);
        $films = Film::select('user_id', 'id', 'name', 'image')->paginate(12);
        foreach ($films as $film) {
            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('like', true)
                            ->exists()) {
                $film->like = true;
            } else {
                $film->like = false;
            }

            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('seen', true)
                            ->exists()) {
                $film->seen = true;
            } else {
                $film->seen = false;
            }
        }


        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'films' => $films
        ]);
    }

    public function show($id, Request $request) {
        $user = $this->getIdentity($request);
        $film = Film::find($id);

        if (is_object($film)) {
            $film->load('user');
            $film->load('genre');
            $film->load('comment');
            $film->load('link');

            foreach ($film->link as $link) {
                $link->load('user');
                $link->load('language');
            }

            foreach ($film->comment as $comment) {
                $comment->load('user');
            }


            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('favourite', true)
                            ->exists()) {
                $film->fav = true;
            } else {
                $film->fav = false;
            }


            $data = [
                'code' => 200,
                'status' => 'success',
                'film' => $film
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

    public function store(Request $request) {
        // Recoger datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Conseguir el usuario identificado
            $user = $this->getIdentity($request);

            if ($user->perms == 1) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }

            // Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'duration' => 'required',
                        'genres' => 'required',
                        'links' => 'required'
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Film not saved, missing data'
                ];
                $data["errors"] = $validate->errors();
            } else {
                $film = new Film();
                $film->user_id = $user->sub;
                $film->name = $params->name;
                $film->description = !empty($params->description) ? $params->description : null;
                $film->image = !empty($params->image) ? $params->image : null;
                $film->trailer = !empty($params->trailer) ? $params->trailer : null;
                $film->duration = $params->duration;
                //LOS Géneros!!!!!!!!!!!!!!
                $film->save();

                if (!empty($params->genres) && count($params->genres) > 0) {
                    foreach ($params->genres as $genre) {
                        DB::table('Genre_has_Film')
                                ->insert([
                                    "film_id" => $film->id,
                                    "genre_id" => $genre
                        ]);
                    }
                }

                if (!empty($params->links) && count($params->links) > 0) {
                    foreach ($params->links as $link) {
                        $linkk = new Link();
                        $linkk->film_id = $film->id;
                        $linkk->user_id = $user->sub;
                        $linkk->url = $link->url;
                        $linkk->language_id = $link->language->id;
                        $linkk->save();
                    }
                }

                $film->load("genre");
                $film->load("link");


                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'film' => $film
                ];
            }

            // Guardar el artículo (post)
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Envía los datos correctamente'
            ];
        }

        // Devolver la respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        // Datos para devolver
        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectamente'
        ];

        if (!empty($params_array)) {

            // Validar los datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'description' => 'required'
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }


            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // COnseguir usuario identificado
            $user = $this->getIdentity($request);

            /* if($user->sub != $params_array['user_id']) {
              return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
              } */


            // Buscar el registro
            $film = Film::where('id', $id)
                    ->where('user_id', $user->sub)
                    ->first();

            if ((!empty($film) && is_object($film) && $user->perms == 2) || $user->perms == 3) {
                // Actualizar el registro en concreto
                $film->update($params_array);

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'film' => $film,
                    'changes' => $params_array
                ];
            }

            /* $where = [
              'id' => $id,
              'user_id' => $user->sub
              ];
              $post = Post::updateOrCreate($where, $params_array); */
        }

        // Devolver respuesta
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {
        // Conseguir usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir el registro
        $film = Film::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

        if ((!empty($film) && $user->perms == 2) || $user->perms == 3) {

            // Borrar el registro
            $film->delete();

            // Devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'film' => $film
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'This film does not exist'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {
        // Recoger la imagen de la petición
        $image = $request->file('file0');

        // Validar imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('images')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        // Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);

        if ($isset) {
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);

            // Devolver la imagen
            return new Response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        // Mostrar posible error
        return response()->json($data, $data['code']);
    }

    public function getFilmsByUser($id) {
        $films = Film::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'films' => $films
                        ], 200);
    }

    public function getLikedFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = DB::table('Film')
                ->join('User_film', function($join) use($user) {
                    $join->on('Film.id', '=', 'User_film.film_id')
                    ->where('User_film.user_id', $user->sub)
                    ->where('User_film.like', true);
                })
                ->select('Film.user_id', 'Film.id', 'Film.name', 'Film.image')
                ->paginate(12);

        foreach ($films as $film) {
            $film->like = true;

            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('seen', true)
                            ->exists()) {
                $film->seen = true;
            } else {
                $film->seen = false;
            }
        }
        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'films' => $films
        ]);
    }

    public function getFilmsByFilter(Request $request) {
        $user = $this->getIdentity($request);
        if (!empty($request->input('lang')) && empty($request->input('genre'))) {
            $lang = $request->input('lang');
            $films = DB::table('Film')
                    ->join('Link', function($join) {
                        $join->on('Film.id', '=', 'Link.film_id');
                    })
                    ->join('Language', function($join) use($lang) {
                        $join->on('Link.language_id', '=', 'Language.id')
                        ->where('Language.id', $lang);
                    })
                    ->select('Film.user_id', 'Film.id', 'Film.name', 'Film.image')
                    ->distinct()
                    ->paginate(12);
        } else if (!empty($request->input('lang')) && !empty($request->input('genre'))) {
            $genre = $request->input('genre');
            $lang = $request->input('lang');
            $films = DB::table('Film')
                    ->join('Link', function($join) {
                        $join->on('Film.id', '=', 'Link.film_id');
                    })
                    ->join('Language', function($join) use($lang) {
                        $join->on('Link.language_id', '=', 'Language.id')
                        ->where('Language.id', $lang);
                    })
                    ->join('Genre_has_Film', function($join) use($genre) {
                        $join->on('Genre_has_Film.film_id', '=', 'Film.id')
                        ->where('Genre_has_Film.genre_id', $genre);
                    })
                    ->select('Film.user_id', 'Film.id', 'Film.name', 'Film.image')
                    ->distinct()
                    ->paginate(12);
        } else if (empty($request->input('lang')) && !empty($request->input('genre'))) {
            $genre = $request->input('genre');
            $films = DB::table('Film')
                    ->join('Genre_has_Film', function($join) use($genre) {
                        $join->on('Genre_has_Film.film_id', '=', 'Film.id')
                        ->where('Genre_has_Film.genre_id', $genre);
                    })
                    ->select('Film.user_id', 'Film.id', 'Film.name', 'Film.image')
                    ->distinct()
                    ->paginate(12);
        }

        foreach ($films as $film) {
            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('like', true)
                            ->exists()) {
                $film->like = true;
            } else {
                $film->like = false;
            }

            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('seen', true)
                            ->exists()) {
                $film->seen = true;
            } else {
                $film->seen = false;
            }
        }
        $data = myHelpers::data("success", 200, "OK");
        $data['films'] = $films;

        return response()->json($data, $data['code']);
    }

    public function getSeenFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = DB::table('Film')
                ->join('User_film', function($join) use($user) {
                    $join->on('Film.id', '=', 'User_film.film_id')
                    ->where('User_film.user_id', $user->sub)
                    ->where('User_film.seen', true);
                })
                ->select('Film.user_id', 'Film.id', 'Film.name', 'Film.image')
                ->paginate(12);

        foreach ($films as $film) {
            if (UserFilm::where('film_id', $film->id)
                            ->where('user_id', $user->sub)
                            ->where('like', true)
                            ->exists()) {
                $film->like = true;
            } else {
                $film->like = false;
            }

            $film->seen = true;
        }
        
        return response()->json([
                    'code' => 200,
                    'status' => 'success',
                    'films' => $films
        ]);
    }

    public function getPendingFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('pendingFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }

    public function getFavouriteFilmsByUser(Request $request) {
        $user = $this->getIdentity($request);
        $films = User::find($user->sub)->load('favouriteFilms');
        $data = myHelpers::data("success", 200, "done");

        if (empty($films)) {
            $data = myHelpers::data("error", 500, "Internal error");
        } else {
            $data["films"] = $films;
        }

        return response()->json($data, $data["code"]);
    }

    public function getAllLinksFromFilm($id) {
        $film = Film::find($id);

        if (empty($film)) {
            $data = myHelpers::data("error", 404, "Film does not exist");
        } else {
            $links = $film->link;
            $data = myHelpers::data("success", 200, "Done");
            $data["links"] = $links;
        }

        return response()->json($data, $data['code']);
    }

    public function comment($id, Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $data = myHelpers::data("error", 500, "Server Error, Please try again.");

        if (empty($params_array)) {
            $data = myHelpers::data("error", 400, "Content message is required.");
            return response()->json($data, $data['code']);
        }

        $validate = \Validator::make($params_array, [
                    'content' => 'required'
        ]);

        if ($validate->fails()) {
            $data = myHelpers::data("error", 400, "Content message is required.");
            $data['errors'] = $validate->errors();
            return response()->json($data, $data['code']);
        }

        $user = $this->getIdentity($request);

        $film = Film::find($id);


        if (empty($film)) {
            $data = myHelpers::data("error", 404, "Film does not exist");
        } else {
            $comment = new Comment();
            $comment->film_id = $id;
            $comment->user_id = $user->sub;
            $comment->content = $params_array['content'];
            if ($comment->save()) {
                $data = myHelpers::data("success", 200, "Done");
            }
        }

        return response()->json($data, $data['code']);
    }

    private function getIdentity(Request $request) {
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        if (empty($token)) {
            $headers = apache_request_headers();
            $token = $headers['Authorization'];
        }
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

}
