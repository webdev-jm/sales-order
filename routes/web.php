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

    // DISCOUNTS
    Route::group(['middleware' => 'permission:discount access'], function() {
        Route::get('discount', 'App\Http\Controllers\DiscountController@index')->name('discount.index')->middleware('permission:discount access');
        Route::get('discount/create', 'App\Http\Controllers\DiscountController@create')->name('discount.create')->middleware('permission:discount create');
        Route::post('discount', 'App\Http\Controllers\DiscountController@store')->name('discount.store')->middleware('permission:discount create');

        Route::get('discount/{id}/edit', 'App\Http\Controllers\DiscountController@edit')->name('discount.edit')->middleware('permission:discount edit');
        Route::post('discount/{id}', 'App\Http\Controllers\DiscountController@update')->name('discount.update')->middleware('permission:discount edit');
    });

    // ACCOUNTS
    Route::group(['middleware' => 'permission:account access'], function() {
        Route::get('account', 'App\Http\Controllers\AccountController@index')->name('account.index');
        Route::get('account/create', 'App\Http\Controllers\AccountController@create')->name('account.create')->middleware('permission:account create');
        Route::post('account', 'App\Http\Controllers\AccountController@store')->name('account.store')->middleware('permission:account create');

        Route::get('account/{id}/create', 'App\Http\Controllers\AccountController@edit')->name('account.edit')->middleware('permission:account edit');
        Route::post('account/{id}', 'App\Http\Controllers\AccountController@update')->name('account.update')->middleware('permission:account edit');
    });

    // INVOICE TERMS
    Route::group(['middleware' => 'permission:invoice term access'], function() {
        Route::get('invoice-term', 'App\Http\Controllers\InvoiceTermController@index')->name('invoice-term.index');
        Route::get('invoice-term/create', 'App\Http\Controllers\InvoiceTermController@create')->name('invoice-term.create')->middleware('permission:invoice term create');
        Route::post('invoice-term', 'App\Http\Controllers\InvoiceTermController@store')->name('invoice-term.store')->middleware('permission:invoice term create');

        Route::get('invoice-term/{id}/edit', 'App\Http\Controllers\InvoiceTermController@edit')->name('invoice-term.edit')->middleware('permission:invoice term edit');
        Route::post('invoice-term/{id}', 'App\Http\Controllers\InvoiceTermController@update')->name('invoice-term.udpate')->middleware('permission:invoice term edit');
    });

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

