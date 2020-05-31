<?php

use Illuminate\Support\Facades\Route;

Route::post('login', 'UserController@login');
Route::post('register', 'UserController@register');
Route::post('refreshtoken', 'UserController@refreshToken');

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', 'UserController@logout');
    Route::post('details', 'UserController@details');
});
