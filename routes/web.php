<?php

use App\Article;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Notifications\OrderConfirmed;
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
Route::get('/slugs', function() {
	$articles = Article::where('user_id', 1)->get();
	foreach ($articles as $article) {
		$article->slug = ArticleHelper::slug($article->name);
		$article->save();
		echo $article->slug.'</br>';
	}
	echo "listo";
});

Route::get('/clients/check-saldos/{client_id}', 
	'ClientController@checkSaldos'
);

Route::get('/orders/deliver/{order_id}', 
	'OrderController@deliver'
);

Route::post('/payment-notification', 'PaymentController@notification');

Route::post('/login', 'LoginController@login');
// Route::post('/login-owner', 'LoginController@loginOwner');
Route::post('login-employee', 'Auth\LoginController@loginEmployee');
Route::post('login-admin', 'Auth\LoginController@loginAdmin');
Route::post('register', 'Auth\RegisterController@registerCommerce');
Route::post('logout', 'Auth\LoginController@logout');

Route::get('/clients/pdf/{seller_id}', 'ClientController@pdf');
Route::get('/current-acounts/pdf/{client_id}/{months_ago}', 'CurrentAcountController@pdf');
Route::get('/sales/pdf/{sales_id}/{company_name}/{articles_cost}/{articles_subtotal_cost}/{articles_total_price}/{articles_total_cost}/{borders}', 'SaleController@pdf');
// Imprimir articulos
Route::get('/pdf/{columns}/{articles_ids}/{orientation}/{header?}', 'PdfController@articles');

Route::get('/sales/cliente/{company_name}/{borders}/{sale_id}', 'PdfController@sale_client');
Route::get('/sales/comercio/{company_name}/{borders}/{sale_id}', 'PdfController@sale_commerce');
Route::get('/imprimir-precios/{articles_id}/{company_name}', 'PdfController@printTicket');