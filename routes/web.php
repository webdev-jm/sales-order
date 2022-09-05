<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);


Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // USERS
    Route::group(['middleware' => 'permission:user access'], function() {
        Route::get('user', 'App\Http\Controllers\UserController@index')->name('user.index');
        Route::get('user/create', 'App\Http\Controllers\UserController@create')->name('user.create')->middleware('permission:user create');
        Route::post('user', 'App\Http\Controllers\UserController@store')->name('user.store')->middleware('permission:user create');

        Route::get('user/{id}/edit', 'App\Http\Controllers\UserController@edit')->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', 'App\Http\Controllers\UserController@update')->name('user.update')->middleware('permission:user edit');
    });

    // ROLES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('role', 'App\http\Controllers\RoleController@index')->name('role.index');
        Route::get('role/create', 'App\Http\Controllers\RoleController@create')->name('role.create')->middleware('permission:role create');
        Route::post('role', 'App\Http\Controllers\RoleController@store')->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}/edit', 'App\Http\Controllers\RoleController@edit')->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', 'App\Http\Controllers\RoleController@update')->name('role.update')->middleware('permission:role edit');
    });
});

