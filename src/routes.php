<?php

use Sifra\Http\Routing\Route;


Route::get('/', 'App\Http\Controllers\HomeController@index');

// USERS
Route::get('/users', 'App\Http\Controllers\UserController@index');

Route::get('/users/create', 'App\Http\Controllers\UserController@create');
Route::post('/users/create', 'App\Http\Controllers\UserController@store');

Route::get('/users/edit/:id', 'App\Http\Controllers\UserController@edit');
Route::post('/users/edit/:id', 'App\Http\Controllers\UserController@update');

Route::post('/users/delete/:id', 'App\Http\Controllers\UserController@destroy');
