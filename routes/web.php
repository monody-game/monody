<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app');
})->where('any', '(?!api|storage|assets).*');

Route::get('/assets/{path}', 'ImageController@show')->where('path', '.*');
