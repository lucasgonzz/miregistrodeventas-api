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

Route::post('login-owner', 'auth\LoginController@loginOwner');
Route::post('login-employee', 'auth\LoginController@loginEmployee');
Route::post('login-admin', 'Auth\LoginController@loginAdmin');
Route::post('logout', 'auth\LoginController@logout');

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/sales/pdf/{sales_id}/{company_name}/{articles_cost}/{articles_subtotal_cost}/{articles_total_price}/{articles_total_cost}/{borders}', 'SaleController@pdf');
