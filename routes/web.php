<?php

use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Sale;
use Illuminate\Http\Request;
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


// Devuelve las comisiones de las ventas que le corresponden al vendedor
Route::get('/prueba', function() {
	$sales = Sale::where('user_id', 1)->get();
	if (array_key_exists(100, $sales->toArray())) {
		var_dump($sales[2]);
	} else {
		echo "no";
	}
});

Route::post('/procesar-pago', 
	function(Request $request) {
		User::create([
			'name' => $request->transactionAmount,
			'password' => 'asd',
		]);
	}
);

Route::get('/orders/deliver/{order_id}', 
	'OrderController@deliver'
);

Route::post('/login', 'LoginController@login');
// Route::post('/login-owner', 'LoginController@loginOwner');
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