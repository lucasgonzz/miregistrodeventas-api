<?php

use App\Article;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CurrentAcountController;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\Sale\Commissioners as SaleHelper_Commissioners;
use App\Http\Controllers\SaleController;
use App\Notifications\OrderConfirmed;
use App\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Exports\ArticlesExport;
use Maatwebsite\Excel\Facades\Excel;

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

Route::get('/aaa', function() {
	dd(strlen('dino goma pinchado , ver que en su rto no se desconto el 20 de la nc'));
});
Route::get('/afip/{sale_id}', 'AfipWsController@init');

Route::get('/articles/pdf', 'ArticleController@pdf');

// Devuelve las comisiones de las ventas que le corresponden al vendedor
Route::get('/slugs', function() {
	$articles = Article::where('user_id', 1)->get();
	foreach ($articles as $article) {
		$article->slug = '';
		$article->save();
	}

	foreach ($articles as $article) {
		$article->slug = ArticleHelper::slug($article->name);
		$article->save();
		echo $article->slug.'</br>';
	}
	echo "listo";
});
Route::get('/check-saldos', function() {
	$clients = App\Client::where('user_id', 2)->get();
	foreach ($clients as $client) {
		$controller = new ClientController();
		$controller->checkSaldos($client->id);
	}
	echo "listo";
});
// Se usa para eliminar las cuentas corrientes y volver a hacerlas
Route::get('/check-sales', function() {
	$sales = App\Sale::where('user_id', 2)
						->where('created_at', '>', Carbon\Carbon::now()->subWeek())
						->get();
	foreach ($sales as $sale) {
		$controller = new SaleController();
		$controller->updateCurrentAcountsAndCommissions($sale);
	}
	echo "listo";
});

Route::get('/entrada', 
	'EntradaController@entrada'
);
Route::get('/email', 
	'MailController@order'
);
Route::get('/emails/{ids}', 
	'MailController@articles'
);
Route::get('/view/{ids}', 
	'MailController@articles'
);
Route::get('/clients/check-saldos/{client_id}', 
	'ClientController@checkSaldos'
);
Route::get('/clients/check-pagos/{client_id}', 
	'CurrentAcountController@checkPagos'
);
Route::get('/refresh', 
	'PaymentController@refresh'
);
Route::get('/customer/{id}', 
	'PaymentController@customer'
);

Route::get('/orders/deliver/{order_id}', 
	'OrderController@deliver'
);

Route::post('/payment-notification', 'PaymentController@notification');

Route::post('/login', 'LoginController@login');
Route::post('/login-super', 'LoginController@loginSuper');
Route::post('/logout', 'LoginController@logout');
Route::post('/users', 'UserController@store');
// Route::post('/login-owner', 'LoginController@loginOwner');
// Route::post('login-employee', 'Auth\LoginController@loginEmployee');
// Route::post('login-admin', 'Auth\LoginController@loginAdmin');
// Route::post('register', 'Auth\RegisterController@registerCommerce');

Route::get('/clients/pdf/{seller_id}', 'ClientController@pdf');
Route::get('/current-acounts/pdf/{client_id}/{months_ago}', 'CurrentAcountController@pdfFromClient');
Route::get('/current-acounts/pdf/{ids}', 'CurrentAcountController@pdf');
Route::get('/sales/pdf/{sales_id}/{for_commerce}', 'SaleController@pdf');

// Exel
Route::get('/articles/ecxel', 'ArticleController@export');

// Imprimir articulos
Route::get('/pdf/{columns}/{articles_ids}/{orientation}/{header?}', 'PdfController@articles');
Route::get('/prices-lists/{id}', 'PricesListController@pdf');

Route::get('/sales/cliente/{company_name}/{borders}/{sale_id}', 'PdfController@sale_client');
Route::get('/sales/comercio/{company_name}/{borders}/{sale_id}', 'PdfController@sale_commerce');
Route::get('/imprimir-precios/{articles_id}/{company_name}', 'PdfController@printTicket');