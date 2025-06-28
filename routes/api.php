<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::namespace('Api\V1')->group(function () {
    Route::group(['middleware' => 'ApiTokenAuthentication'], function () {
        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::get('user', 'UserController@index');
        });
    });
});