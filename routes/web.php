<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

Route::get('/orders/deliver/{order_id}', 
	'OrderController@deliver'
);
// Route::post('login-owner', function(Request $request) {
// 	return response()->json(['askd' => $request->password], 200);
// });
// Route::post('/login-owner', function() {
// 	return ['sad' => 23];
// });
Route::post('/login-owner', 'LoginController@loginOwner');
Route::post('login-employee', 'Auth\LoginController@loginEmployee');
Route::post('login-admin', 'Auth\LoginController@loginAdmin');
Route::post('register', 'Auth\RegisterController@registerCommerce');
Route::post('logout', 'Auth\LoginController@logout');

Route::get('/sales/pdf/{sales_id}/{company_name}/{articles_cost}/{articles_subtotal_cost}/{articles_total_price}/{articles_total_cost}/{borders}', 'SaleController@pdf');
// Imprimir articulos
Route::get('/pdf/{columns}/{articles_ids}/{orientation}/{header?}', 'PdfController@articles');

Route::get('/sales/cliente/{company_name}/{borders}/{sale_id}', 'PdfController@sale_client');
Route::get('/sales/comercio/{company_name}/{borders}/{sale_id}', 'PdfController@sale_commerce');
Route::get('/imprimir-precios/{articles_id}/{company_name}', 'PdfController@printTicket');