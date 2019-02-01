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

Route::get('/', function () {
    return view('welcome');
});

//under construction
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::group(['middleware' => 'auth.check'], function () {
    //common routes
    Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
    Route::get('/user/profile', 'HomeController@profileView')->name('user.profile');
    Route::post('/user/profile', 'HomeController@profileUpdate')->name('user.profile.action');

    Route::group(['middleware' => ['user.role:0']], function () {
        //company
        Route::resource('company', 'companyController');
    });
    //user routes
    Route::group(['middleware' => ['user.role:0,1,2']], function () {

        //account
        Route::resource('account', 'AccountController');

        //staff
        Route::resource('employee', 'EmployeeController');

        //excavator
        Route::resource('excavator', 'ExcavatorController');

        //site
        Route::resource('site', 'SiteController');

        //wage
        Route::resource('employee-wage', 'EmployeeWageController');

        //excavator reading
        Route::resource('excavator-reading', 'ExcavatorReadingController');

        //excavator rent
        Route::resource('excavator-rent', 'ExcavatorRentController');

        //expenses
        Route::resource('expense', 'ExpenseController');

        //vouchers
        Route::resource('voucher', 'VoucherController');

        //reports
        Route::get('reports/account-statement', 'ReportController@accountStatement')->name('report.account-statement');
        Route::get('reports/credit-list', 'ReportController@creditList')->name('report.credit.list');

        //ajax urls
        Route::group(['middleware' => 'is.ajax'], function () {
            Route::get('/ajax/account/details/{id}', 'AccountController@getDetails')->name('ajax.account.details');
            Route::get('/ajax/employee-wage/next-wage-date', 'EmployeeWageController@getNextWageDate')->name('ajax.employee-wage.next-wage-date');
            //Route::get('/ajax/excavator-reading/last', 'ExcavatorRentController@getNextRentDate')->name('ajax.excavator-rent.last');
        });
    });
});
