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
Route::put('/api/user/update', 'UserController@update')
        ->middleware(ApiAuthMiddleware::class);
Route::post('api/user/upload', 'UserController@upload')
        ->middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}', 'UserController@getImage')
        ->middleware(ApiAuthMiddleware::class); 
Route::get('api/user/detail/{id}', 'UserController@detail')
        ->middleware(ApiAuthMiddleware::class);
Route::get('api/user/favourite', 'UserController@getFavouritePosts')
        ->middleware(ApiAuthMiddleware::class);
/*Route::get('api/user/favourite/films', 'UserController@getFavouriteFilms')
        ->middleware(ApiAuthMiddleware::class);*/

// Category
Route::resource('api/category', 'CategoryController')
        ->middleware(ApiAuthMiddleware::class);

// Post
Route::resource('api/post', 'PostController')
        ->middleware(ApiAuthMiddleware::class);
