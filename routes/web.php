<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\InvoiceTermController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PriceCodeController;
use App\Http\Controllers\SalesPersonController;
use App\http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AccountLoginController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OperationProcessController;
use App\Http\Controllers\UserBranchScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\AreaController;

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

Route::get('/test', function() {
    return view('welcome');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

    Route::get('profile', [ProfileController::class, 'index'])->name('profile');

    // SYSTEM LOGS
    Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs');
    // ->middleware('permission:system logs')

    // AJAX
    Route::post('user/ajax', [UserController::class, 'ajax'])->name('user.ajax');
    Route::post('account/ajax',[AccountController::class, 'ajax'])->name('account.ajax');

    Route::get('user/get-ajax/{id}', [UserController::class, 'getAjax'])->name('user.getAjax');
    Route::get('account/get-ajax/{id}', [AccountController::class, 'getAjax'])->name('account.getAjax');

    // DASHBOARD
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // SALES ORDER
    Route::group(['middleware' => 'permission:sales order access'], function() {
        Route::get('sales-order', [SalesOrderController::class, 'index'])->name('sales-order.index');
        Route::get('sales-order/create', [SalesOrderController::class, 'create'])->name('sales-order.create')->middleware('permission:sales order create');
        Route::post('sales-order', [SalesOrderController::class, 'store'])->name('sales-order.store')->middleware('permission:sales order create');
        
        Route::get('sales-order/{id}', [SalesOrderController::class, 'show'])->name('sales-order.show');
        
        Route::get('sales-order/{id}/edit', [SalesOrderController::class, 'edit'])->name('sales-order.edit')->middleware('permission:sales order edit');
        Route::post('sales-order/{id}', [SalesOrderController::class, 'update'])->name('sales-order.update')->middleware('permission:sales order edit');

        Route::get('list-sales-order/list', [SalesOrderController::class, 'list'])->name('sales-order.list')->middleware('permission:sales order list');
    });

    // CALENDAR
    Route::group(['middleware' => 'permission:calendar access'], function() {
        Route::get('calendar', [UserBranchScheduleController::class, 'index'])->name('calendar.index');

        Route::post('calendar/upload', [UserBranchScheduleController::class, 'upload'])->name('calendar.upload')->middleware('permission:calendar create');
    });

    // COMPANY
    Route::group(['middleware' => 'permission:company access'], function() {
        Route::get('company', [CompanyController::class, 'index'])->name('company.index');
        Route::get('company/create', [CompanyController::class, 'create'])->name('company.create')
        ->middleware('permission:company create');
        Route::post('company', [CompanyController::class, 'store'])->name('company.store')->middleware('permission:company create');

        Route::get('company/{id}/edit', [CompanyController::class, 'edit'])->name('company.edit')->middleware('permission:company edit');
        Route::post('company/{id}', [CompanyController::class, 'update'])->name('company.update')->middleware('permission:company edit');
    });

    // DISCOUNTS
    Route::group(['middleware' => 'permission:discount access'], function() {
        Route::get('discount', [DiscountController::class, 'index'])->name('discount.index')->middleware('permission:discount access');
        Route::get('discount/create', [DiscountController::class, 'create'])->name('discount.create')->middleware('permission:discount create');
        Route::post('discount', [DiscountController::class, 'store'])->name('discount.store')->middleware('permission:discount create');

        Route::post('discount/upload', [DiscountController::class, 'upload'])->name('discount.upload')->middleware('permission:discount create');

        Route::get('discount/{id}/edit', [DiscountController::class, 'edit'])->name('discount.edit')->middleware('permission:discount edit');
        Route::post('discount/{id}', [DiscountController::class, 'update'])->name('discount.update')->middleware('permission:discount edit');

        
    });

    // ACCOUNTS
    Route::group(['middleware' => 'permission:account access'], function() {
        Route::get('account', [AccountController::class, 'index'])->name('account.index');
        Route::get('account/create', [AccountController::class, 'create'])->name('account.create')->middleware('permission:account create');
        Route::post('account', [AccountController::class, 'store'])->name('account.store')->middleware('permission:account create');

        Route::post('account/upload', [AccountController::class, 'upload'])->name('account.upload')->middleware('permission:account create');

        Route::get('account/{id}/edit', [AccountController::class, 'edit'])->name('account.edit')->middleware('permission:account edit');
        Route::post('account/{id}', [AccountController::class, 'update'])->name('account.update')->middleware('permission:account edit');
    });

    // SHIPPING ADDRESS
    Route::group(['middleware' => 'permission:shipping address access'], function() {
        Route::get('shipping-address/{id}', [ShippingAddressController::class, 'index'])->name('shipping-address.index');
        Route::get('shipping-address/{id}/create', [ShippingAddressController::class, 'create'])->name('shipping-address.create')->middleware('permission:shipping address create');
        Route::post('shipping-address', [ShippingAddressController::class, 'store'])->name('shipping-address.store')->middleware('permission:shipping address create');
        
        Route::post('shipping-address/upload', [ShippingAddressController::class, 'upload'])->name('shipping-address.upload')->middleware('permission:shipping address create');

        Route::get('shipping-address/{id}/edit', [ShippingAddressController::class, 'edit'])->name('shipping-address.edit')->middleware('permission:shipping address edit');
        Route::post('shipping-address/{id}', [ShippingAddressController::class, 'update'])->name('shipping-address.update')->middleware('permission:shipping address edit');
    });

    // BRANCHES
    Route::group(['middleware' => 'permission:branch access'], function() {
        Route::get('branch', [BranchController::class, 'index'])->name('branch.index');
        Route::get('branch/create', [BranchController::class, 'create'])->name('branch.create')->middleware('permission:branch create');
        Route::post('branch', [BranchController::class, 'store'])->name('branch.store')->middleware('permission:branch create');

        Route::post('branch/upload', [BranchController::class, 'upload'])->name('branch.upload')->middleware('permission:branch create');

        Route::get('branch/{id}/edit', [BranchController::class, 'edit'])->name('branch.edit')->middleware('permission:branch edit');
        Route::post('branch/{id}', [BranchController::class, 'update'])->name('branch.update')->middleware('permission:branch edit');
    });

    // REGIONS
    Route::group(['middleware' => 'permission:region access'], function() {
        Route::get('region', [RegionController::class, 'index'])->name('region.index');
        Route::get('region/create', [RegionController::class, 'create'])->name('region.create')->middleware('permission:region create');
        Route::post('region', [RegionController::class, 'store'])->name('region.store')->middleware('permission:region create');

        Route::get('region/{id}', [RegionController::class, 'show'])->name('region.show');
        
        Route::post('region/upload', [RegionController::class, 'upload'])->name('region.upload')->middleware('permission:region create');

        Route::get('region/{id}/edit', [RegionController::class, 'edit'])->name('region.edit')->middleware('permission:region edit');
        Route::post('region/{id}', [RegionController::class, 'update'])->name('region.update')->middleware('permission:region edit');
    });

    // CLASSIFICATIONS
    Route::group(['middleware' => 'permission:classification access'], function() {
        Route::get('classification', [ClassificationController::class, 'index'])->name('classification.index');
        Route::get('classification/create', [ClassificationController::class, 'create'])->name('classification.create')->middleware('permission:classification create');
        Route::post('classification', [ClassificationController::class, 'store'])->name('classification.store')->middleware('permission:classification create');

        Route::post('classification/upload', [ClassificationController::class, 'upload'])->name('classification.upload')->middleware('permission:classification create');

        Route::get('classification/{id}/edit', [ClassificationController::class, 'edit'])->name('classification.edit')->middleware('permission:classification edit');
        Route::post('classification/{id}', [ClassificationController::class, 'update'])->name('classification.update')->middleware('permission:classification edit');
    });

    // AREAS
    Route::group(['middleware' => 'permission:area access'], function() {
        Route::get('area', [AreaController::class, 'index'])->name('area.index');
        Route::get('area/create', [AreaController::class, 'create'])->name('area.create')->middleware('permission:area create');
        Route::post('area', [AreaController::class, 'store'])->name('area.store')->middleware('permission:area create');

        Route::post('area/upload', [AreaController::class, 'upload'])->name('area.upload')->middleware('permission:area create');

        Route::get('area/{id}/edit', [AreaController::class, 'edit'])->name('area.edit')->middleware('permission:area edit');
        Route::post('area/{id}', [AreaController::class, 'update'])->name('area.update')->middleware('permission:area edit');
    });

    // INVOICE TERMS
    Route::group(['middleware' => 'permission:invoice term access'], function() {
        Route::get('invoice-term', [InvoiceTermController::class, 'index'])->name('invoice-term.index');
        Route::get('invoice-term/create', [InvoiceTermController::class, 'create'])->name('invoice-term.create')->middleware('permission:invoice term create');
        Route::post('invoice-term', [InvoiceTermController::class, 'store'])->name('invoice-term.store')->middleware('permission:invoice term create');

        Route::post('invoice-term/upload', [InvoiceTermController::class, 'upload'])->name('invoice-term.upload')->middleware('permission:invoice term create');

        Route::get('invoice-term/{id}/edit', [InvoiceTermController::class, 'edit'])->name('invoice-term.edit')->middleware('permission:invoice term edit');
        Route::post('invoice-term/{id}', [InvoiceTermController::class, 'update'])->name('invoice-term.udpate')->middleware('permission:invoice term edit');
    });

    // PRODUCTS
    Route::group(['middleware' => 'permission:product access'], function() {
        Route::get('product', [ProductController::class, 'index'])->name('product.index');
        Route::get('product/create', [ProductController::class, 'create'])->name('product.create')->middleware('permission:product create');
        Route::post('product', [ProductController::class, 'store'])->name('product.store')->middleware('permission:product create');

        Route::post('product/upload', [ProductController::class, 'upload'])->name('product.upload')->middleware('permission:product create');

        Route::get('product/{id}/edit', [ProductController::class, 'edit'])->name('product.edit')->middleware('permission:product edit');
        Route::post('product/{id}', [ProductController::class, 'update'])->name('product.update')->middleware('permission:product edit');
    });

    // PRICE CODES
    Route::group(['middleware' => 'permission:price code access'], function() {
        Route::get('price-code', [PriceCodeController::class, 'index'])->name('price-code.index');
        Route::get('price-code/create', [PriceCodeController::class, 'create'])->name('price-code.create')->middleware('permission:price code create');
        Route::post('price-code', [PriceCodeController::class, 'store'])->name('price-code.store')->middleware('permission:price code create');

        Route::post('price-code/upload', [PriceCodeController::class, 'upload'])->name('price-code.upload')->middleware('permission:price code create');

        Route::get('price-code/{id}/edit', [PriceCodeController::class, 'edit'])->name('price-code.edit')->middleware('permission:price code edit');
        Route::post('price-code/{id}', [PriceCodeController::class, 'update'])->name('price-code.update')->middleware('permission:price code edit');
    });

    // SALES PEOPLE
    Route::group(['middleware' => 'permission:sales people access'], function() {
        Route::get('sales-people', [SalesPersonController::class, 'index'])->name('sales-people.index');
        Route::get('sales-people/create', [SalesPersonController::class, 'create'])->name('sales-person.create')->middleware('permission:sales person create');
        Route::post('sales-people', [SalesPersonController::class, 'store'])->name('sales-person.store')->middleware('permission:sales person create');

        Route::get('sales-people/{id}/edit', [SalesPersonController::class, 'edit'])->name('sales-person.edit')->middleware('permission:sales person edit');
        Route::post('sales-people/{id}', [SalesPersonController::class, 'update'])->name('sales-person.update')->middleware('permission:sales person edit');
    });

    // OPERATION PROCESS
    Route::group(['middleware' => 'permission:operation process access'], function() {
        Route::get('operation-process', [OperationProcessController::class, 'index'])->name('operation-process.index');
        Route::get('operation-process/create', [OperationProcessController::class, 'create'])->name('operation-process.create')->middleware('permission:operation process create');
        Route::post('operation-process', [OperationProcessController::class, 'store'])->name('operation-process.store')->middleware('permission:operation process create');

        Route::post('operation-process/upload', [OperationProcessController::class, 'upload'])->name('operation-process.upload')->middleware('permission:operation process create');

        Route::get('operation-process/{id}/edit', [OperationProcessController::class, 'edit'])->name('operation-process.edit')->middleware('permission:operation process edit');
        Route::post('operation-process/{id}', [OperationProcessController::class, 'update'])->name('operation-process.update')->middleware('permission:operation process edit');
    });

    // ACCOUNT LOGINS
    Route::group(['middleware' => 'permission:account login access'], function() {
        Route::get('login-account', [AccountLoginController::class, 'index'])->name('account-login.index');
        Route::get('login-account/{id}', [AccountLoginController::class, 'show'])->name('account-login.show');
    });

    // USERS
    Route::group(['middleware' => 'permission:user access'], function() {
        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user create');
        Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user create');

        Route::post('user/upload', [UserController::class, 'upload'])->name('user.upload')->middleware('permission:user upload');

        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user edit');
    });

    // ROLES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('role', 'App\Http\Controllers\RoleController@index')->name('role.index');
        Route::get('role/create', 'App\Http\Controllers\RoleController@create')->name('role.create')->middleware('permission:role create');
        Route::post('role', 'App\Http\Controllers\RoleController@store')->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}/edit', 'App\Http\Controllers\RoleController@edit')->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', 'App\Http\Controllers\RoleController@update')->name('role.update')->middleware('permission:role edit');
    });

    // SETTINGS
    Route::group(['middleware' => 'permission:settings access'], function() {
        Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
        Route::post('setting/{id}', [SettingController::class, 'update'])->name('setting.update');
        Route::post('po-number/upload', [SettingController::class, 'upload'])->name('po-number.upload');
    });
});