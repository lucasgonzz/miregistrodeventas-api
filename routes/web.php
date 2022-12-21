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

Route::get('/order-production/image-pdf/{link}', function($link) {
	return response()->file(storage_path().'/app/public/pdf/'.$link);
});

Route::get('/delete-subs', 'SubscriptionController@deleteAll');
Route::get('/afip/{sale_id}', 'AfipWsController@init');

Route::get('/articles/pdf/{ids}', 'ArticleController@pdf');

Route::get('/budget/pdf/{id}', 'BudgetController@pdf');
Route::get('/order-production/pdf/{id}', 'OrderProductionController@pdf');
Route::get('/order-productions/articles-pdf/{id}', 'OrderProductionController@articlesPdf');

Route::get('/provider-order/pdf/{id}', 'ProviderOrderController@pdf');

Route::get('/clients/check-saldos/{client_id}', 
	'ClientController@checkCurrentAcounts'
);

Route::get('/providers/set-num/{company_name}', 'HelperController@setProvidersNum');
Route::get('/clients/set-saldos/{company_name}', 'HelperController@setClientsSaldos');
Route::get('/budgets/set-articles/{company_name}', 'HelperController@setArticlesFromBudgets');
Route::get('/order-productions/set-articles/{company_name}', 'HelperController@setArticlesFromOrderProductions');
Route::get('/articles/set-hosting-images/{company_name}', 'HelperController@setArticlesHostingImages');
Route::get('/user/set-hosting-image/{company_name}', 'HelperController@setUserHostingImage');
Route::get('/titles/set-hosting-image/{company_name}', 'HelperController@setTitlesHostingImages');
Route::get('/orders/set-status/{company_name}', 'HelperController@setOrdersStatus');
Route::get('/articles/set-provider/{company_name}', 'HelperController@setArticlesProvider');
Route::get('/articles/set-category/{company_name}', 'HelperController@setArticlesCategory');
Route::get('/articles/set-price/{company_name}', 'HelperController@setArticlesPrices');
Route::get('/articles/check-price/{company_name}', 'HelperController@getArticlesWithPrices');
Route::get('/articles/set-final-price/{company_name}', 'Helpers\ArticleHelper@setArticlesFinalPrice');

Route::get('a', function() {
	dd((float)'');
});


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

Route::post('/login', 'AuthController@login');
Route::post('/login-super', 'AuthController@loginSuper');
Route::post('/logout', 'AuthController@logout');
Route::post('/users', 'UserController@store');

Route::get('/clients/pdf/{seller_id}', 'ClientController@pdf');
Route::get('/current-acounts/pdf/{model_name}/{model_id}/{months_ago}', 'CurrentAcountController@pdfFromModel');
Route::get('/current-acounts/pdf/{ids}/{model_name}', 'CurrentAcountController@pdf');

Route::get('/sales/pdf/{sales_id}/{for_commerce}', 'SaleController@pdf');
Route::get('/sales/pdf/{sales_id}/{for_commerce}/{afip_ticket?}', 'SaleController@pdf');
Route::get('/sales/afip-ticket/pdf/{sale_id}', 'SaleController@pdfAfipTicket');


Route::get('/sale/new-pdf/{id}/{with_prices}', 'SaleController@newPdf');
Route::get('/sale/pdf/delivered-articles/{id}', 'SaleController@deliveredArticlesPdf');

Route::get('/sales/tickets/pdf/{sale_id}/{address_id?}', 'SaleController@ticketPdf');

// Exel
Route::get('/articles/excel/export', 'ArticleController@export');
Route::get('/provider/excel/export', 'ProviderController@export');

// Imprimir articulos
Route::get('/pdf/{columns}/{articles_ids}/{orientation}/{header?}', 'PdfController@articles');
Route::get('/prices-list/pdf/{id}', 'PricesListController@pdf');

Route::get('/sales/cliente/{company_name}/{borders}/{sale_id}', 'PdfController@sale_client');
Route::get('/sales/comercio/{company_name}/{borders}/{sale_id}', 'PdfController@sale_commerce');
Route::get('/imprimir-precios/{articles_id}/{company_name}', 'PdfController@printTicket');

// SuperBudget
Route::get('/pdf/super-budget/{id}', 'SuperBudgetController@pdf');