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
    return redirect()->route('home');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);


Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

    // SALES ORDER
    Route::group(['middleware' => 'permission:sales order access'], function() {
        Route::get('sales-order', 'App\Http\Controllers\SalesOrderController@index')->name('sales-order.index');
        Route::get('sales-order/create', 'App\Http\Controllers\SalesOrderController@create')->name('sales-order.create')->middleware('permission:sales order create');
        Route::post('sales-order', 'App\Http\Controllers\SalesOrderController@store')->name('sales-order.store')->middleware('permission:sales order create');

        Route::get('sales-order/{id}/edit', 'App\Http\Controllers\SalesOrderController@edit')->name('sales-order.edit')->middleware('permission:sales order edit');
        Route::post('sales-order/{id}', 'App\Http\Controllers\SalesOrderController@update')->name('sales-order.update')->middleware('permission:sales order edit');
    });

    // COMPANY
    Route::group(['middleware' => 'permission:company access'], function() {
        Route::get('company', 'App\Http\Controllers\CompanyController@index')->name('company.index');
        Route::get('company/create', 'App\Http\Controllers\CompanyController@create')->name('company.create')
        ->middleware('permission:company create');
        Route::post('company', 'App\Http\Controllers\CompanyController@store')->name('company.store')->middleware('permission:company create');

        Route::get('company/{id}/edit', 'App\Http\Controllers\CompanyController@edit')->name('company.edit')->middleware('permission:company edit');
        Route::post('company/{id}', 'App\Http\Controllers\CompanyController@update')->name('company.update')->middleware('permission:company edit');
    });

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

    // BRANCHES
    Route::group(['middleware' => 'permission:branch access'], function() {
        Route::get('branch', 'App\Http\Controllers\BranchController@index')->name('branch.index');
        Route::get('branch/create', 'App\Http\Controllers\BranchController@create')->name('branch.create')->middleware('permission:branch create');
        Route::post('branch', 'App\Http\Controllers\BranchController@store')->name('branch.store')->middleware('permission:branch create');

        Route::get('branch/{id}/edit', 'App\Http\Controllers\BranchController@edit')->name('branch.edit')->middleware('permission:branch edit');
        Route::post('branch/{id}', 'App\Http\Controllers\BranchController@update')->name('branch.update')->middleware('permission:branch edit');
    });

    // INVOICE TERMS
    Route::group(['middleware' => 'permission:invoice term access'], function() {
        Route::get('invoice-term', 'App\Http\Controllers\InvoiceTermController@index')->name('invoice-term.index');
        Route::get('invoice-term/create', 'App\Http\Controllers\InvoiceTermController@create')->name('invoice-term.create')->middleware('permission:invoice term create');
        Route::post('invoice-term', 'App\Http\Controllers\InvoiceTermController@store')->name('invoice-term.store')->middleware('permission:invoice term create');

        Route::get('invoice-term/{id}/edit', 'App\Http\Controllers\InvoiceTermController@edit')->name('invoice-term.edit')->middleware('permission:invoice term edit');
        Route::post('invoice-term/{id}', 'App\Http\Controllers\InvoiceTermController@update')->name('invoice-term.udpate')->middleware('permission:invoice term edit');
    });

    // PRODUCTS
    Route::group(['middleware' => 'permission:product access'], function() {
        Route::get('product', 'App\Http\Controllers\ProductController@index')->name('product.index');
        Route::get('product/create', 'App\Http\Controllers\ProductController@create')->name('product.create')->middleware('permission:product create');
        Route::post('product', 'App\Http\Controllers\ProductController@store')->name('product.store')->middleware('permission:product create');

        Route::get('product/{id}/edit', 'App\Http\Controllers\ProductController@edit')->name('product.edit')->middleware('permission:product edit');
        Route::post('product/{id}', 'App\Http\Controllers\ProductController@update')->name('product.update')->middleware('permission:product edit');
    });

    // PRICE CODES
    Route::group(['middleware' => 'permission:price code access'], function() {
        Route::get('price-code', 'App\Http\Controllers\PriceCodeController@index')->name('price-code.index');
        Route::get('price-code/create', 'App\Http\Controllers\PriceCodeController@create')->name('price-code.create')->middleware('permission:price code create');
        Route::post('price-code', 'App\Http\Controllers\PriceCodeController@store')->name('price-code.store')->middleware('permission:price code create');

        Route::get('price-code/{id}/edit', 'App\Http\Controllers\PriceCodeController@edit')->name('price-code.edit')->middleware('permission:price code edit');
        Route::post('price-code/{id}', 'App\Http\Controllers\PriceCodeController@update')->name('price-code.update')->middleware('permission:price code edit');
    });

    // SALES PEOPLE
    Route::group(['middleware' => 'permission:sales people access'], function() {
        Route::get('sales-people', 'App\Http\Controllers\SalesPersonController@index')->name('sales-people.index');
        Route::get('sales-people/create', 'App\Http\Controllers\SalesPersonController@create')->name('sales-person.create')->middleware('permission:sales person create');
        Route::post('sales-people', 'App\Http\Controllers\SalesPersonController@store')->name('sales-person.store')->middleware('permission:sales person create');

        Route::get('sales-people/{id}/edit', 'App\Http\Controllers\SalesPersonController@edit')->name('sales-person.edit')->middleware('permission:sales person edit');
        Route::post('sales-people/{id}', 'App\Http\Controllers\SalesPersonController@update')->name('sales-person.update')->middleware('permission:sales person edit');
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

