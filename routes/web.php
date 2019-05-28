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
Route::get('api/user/avatar/{filename}', 'UserController@getImage'); 
Route::get('api/user/detail/{id}', 'UserController@detail')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/likefilm/{id}', 'UserController@likeFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/favfilm/{id}', 'UserController@favouriteFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/pendingfilm/{id}', 'UserController@pendingFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/seenfilm/{id}', 'UserController@seenSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/likeserie/{id}', 'UserController@likeSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/favserie/{id}', 'UserController@favouriteSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/pendingserie/{id}', 'UserController@pendingSerie')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/seenserie/{id}', 'UserController@seenSerie')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/main', 'UserController@getMainData')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/following', 'UserController@getFollowingList')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/followers', 'UserController@getFollowersList')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/follow/{id}', 'UserController@follow')->middleware(ApiAuthMiddleware::class);
Route::post('api/user/md/{id}', 'UserController@sendPrivateMessage')->middleware(ApiAuthMiddleware::class);
Route::put('api/user/password', 'UserController@changePassword')->middleware(ApiAuthMiddleware::class);


// Admin
Route::get('api/admin', 'AdminController@isAdmin')->middleware(ApiAuthMiddleware::class);
Route::get('api/admin/users', 'AdminController@getUsers')->middleware(ApiAuthMiddleware::class);
Route::put('api/admin', 'AdminController@updateUser')->middleware(ApiAuthMiddleware::class);
Route::post('api/admin', 'AdminController@createUser')->middleware(ApiAuthMiddleware::class);


// Film
Route::resource('api/film', 'FilmController');
Route::post('api/film/upload', 'FilmController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/film/image/{filename}', 'FilmController@getImage');
Route::get('api/film/user/{id}', 'FilmController@getFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmlike', 'FilmController@getLikedFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmseen', 'FilmController@getSeenFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmpending', 'FilmController@getPendingFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmfavourite', 'FilmController@getFavouriteFilmsByUser')->middleware(ApiAuthMiddleware::class);
Route::get('api/film/links/{id}', 'FilmController@getAllLinksFromFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/film/{id}/comment', 'FilmController@comment')->middleware(ApiAuthMiddleware::class);
Route::get('api/filmfilter', 'FilmController@getFilmsByFilter')->middleware(ApiAuthMiddleware::class);


// Serie
Route::resource('api/serie', 'SerieController')->middleware(ApiAuthMiddleware::class);
Route::post('api/serie/upload', 'SerieController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/serie/image/{filename}', 'SerieController@getImage');
Route::get('api/serie/user/{id}', 'SerieController@getSeriesByUser')->middleware(ApiAuthMiddleware::class); 
Route::get('api/serieseen', 'SerieController@getSeenSeriesByUser')->middleware(ApiAuthMiddleware::class); 
Route::post('api/serie/{id}/comment', 'SerieController@comment')->middleware(ApiAuthMiddleware::class);
Route::get('api/serienopag', 'SerieController@getSeriesWithoutPaginate')->middleware(ApiAuthMiddleware::class);

// Chapter / Episode
//Route::resource('api/chapter', 'ChapterController')->middleware(ApiAuthMiddleware::class);
Route::get('api/chapter', 'ChapterController@index')->middleware(ApiAuthMiddleware::class);
Route::get('api/chapter/{id}', 'ChapterController@show')->middleware(ApiAuthMiddleware::class);
Route::post('api/chapter', 'ChapterController@store')->middleware(ApiAuthMiddleware::class);
Route::put('api/chapter/{id}', 'ChapterController@update')->middleware(ApiAuthMiddleware::class);
Route::get('api/chapter/links/{id}', 'ChapterController@getAllLinksFromChapter')->middleware(ApiAuthMiddleware::class);

// List
Route::resource('api/list', 'ListsController')->middleware(ApiAuthMiddleware::class);
Route::post('api/listfilm/{id}/{idFilm}', 'ListsController@storeFilm')->middleware(ApiAuthMiddleware::class);
Route::post('api/listserie/{id}/{idSerie}', 'ListsController@storeSerie')->middleware(ApiAuthMiddleware::class);
Route::delete('api/listfilm/{id}/{idFilm}', 'ListsController@deleteFilm')->middleware(ApiAuthMiddleware::class);
Route::delete('api/listserie/{id}/{idSerie}', 'ListsController@deleteSerie')->middleware(ApiAuthMiddleware::class);

// Comments
Route::get('api/comment', 'CommentController@getComments')->middleware(ApiAuthMiddleware::class);
Route::delete('api/comment/{id}', 'CommentController@destroy')->middleware(ApiAuthMiddleware::class);

// Genres
Route::get('api/genre', 'GenreController@GetAllGenres')->middleware(ApiAuthMiddleware::class);

// Languages
Route::get('api/language', 'LanguageController@index')->middleware(ApiAuthMiddleware::class);