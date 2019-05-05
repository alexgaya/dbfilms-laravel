<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

use App\Http\Middleware\ApiAuthMiddleware;


// User
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}', 'UserController@getImage')->middleware(ApiAuthMiddleware::class); 
Route::get('api/user/detail/{id}', 'UserController@detail')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/likefilm/{id}', 'UserController@likeSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/favfilm/{id}', 'UserController@favouriteSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/pendingfilm/{id}', 'UserController@pendingSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/seenfilm/{id}', 'UserController@seenSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/likeserie/{id}', 'UserController@likeSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/favserie/{id}', 'UserController@favouriteSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/pendingserie/{id}', 'UserController@pendingSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/seenserie/{id}', 'UserController@seenSerie')->middleware(ApiAuthMiddleware::class);




// Film
Route::resource('api/film', 'FilmController')->middleware(ApiAuthMiddleware::class);
Route::post('api/film/upload', 'FilmController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/film/image/{filename}', 'FilmController@getImage')->middleware(ApiAuthMiddleware::class);
Route::get('api/film/user/{id}', 'FilmController@getFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmlike', 'FilmController@getLikedFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmseen', 'FilmController@getSeenFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmpending', 'FilmController@getPendingFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmfavourite', 'FilmController@getFavouriteFilmsByUser')->middleware(ApiAuthMiddleware::class);


// Serie
Route::resource('api/serie', 'SerieController')->middleware(ApiAuthMiddleware::class);
Route::post('api/serie/upload', 'SerieController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/serie/image/{filename}', 'SerieController@getImage')->middleware(ApiAuthMiddleware::class);
Route::get('api/serie/user/{id}', 'SerieController@getSeriesByUser')->middleware(ApiAuthMiddleware::class); 


// List
Route::resource('api/list', 'ListsController')->middleware(ApiAuthMiddleware::class);
Route::post('api/listfilm/{id}/{idFilm}', 'ListsController@storeFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/listserie/{id}/{idSerie}', 'ListsController@storeSerie')->middleware(ApiAuthMiddleware::class);
Route::delete('api/listfilm/{id}/{idFilm}', 'ListsController@deleteFilm')->middleware(ApiAuthMiddleware::class);
Route::delete('api/listserie/{id}/{idSerie}', 'ListsController@deleteSerie')->middleware(ApiAuthMiddleware::class);