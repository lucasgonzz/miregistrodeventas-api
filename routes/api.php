<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Route::get('/clients', 
// 	'ClientController@index'
// );

Route::middleware('auth:sanctum')->group(function () {

	Route::get('/user', function(Request $request) {
		$user = Auth::user();
		$user = App\User::where('id', $user->id)->with('permissions')->with('roles')->first();
		return response()->json(['user' => $user], 200);
	});

	// -----------------------CONFIGURACION------------------------------------------
	Route::put('/user/password', 
		'UserController@updatePassword'
	);
	Route::put('/user', 
		'UserController@update'
	);
	Route::get('/user/trial/contratar-servicio', 
		'UserController@contratarServicio'
	);

	// -----------------------SUPER--------------------------------------------------
	Route::get('super/admins', 
		'SuperController@admins'
	);
	// Registrar de comercios y administradores
	Route::post('super/commerce', 
		'SuperController@registerCommerce'
	);
	Route::post('super/admin', 
		'SuperController@registerAdmin'
	);
	// Cobrar
	Route::get('super/cobrar/{admin_id}', 
		'SuperController@cobrar'
	);

	// -----------------------ADMIN---------------------------------------------------
	// Recommendations
	Route::get('admin/recommendations/', 
		'AdminController@recommendations'
	);
	Route::get('admin/confirm-recommendation/{recommendation_id}', 
		'AdminController@confirmRecommendation'
	);
	// Usuarios de prueba sin uso
	Route::get('admin/users/for-trial', 
		'AdminController@getUsersForTrial'
	);
	Route::get('admin/users/start-trial/{user_id}', 
		'AdminController@userStartTrial'
	);
	// Usuarios probandoce
	Route::get('admin/users/trial', 
		'AdminController@getUsersTrial'
	);
	// Usuarios en uso
	Route::get('/admin/users/in-use', 
		'AdminController@getUsersInUse'
	);
	Route::get('/admin/users/cobrar/{id}', 
		'AdminController@cobrar'
	);
	Route::get('/admin/collect/{commerce_id}/{months_to_collect}', 
		'AdminController@collect'
	);
	Route::get('/admin/collections', 
		'AdminController@getCollections'
	);
	Route::get('/admin/collections-without-delivered', 
		'AdminController@getCollectionsWithoutDelivered'
	);
	Route::put('/permissions/{id}', 
		'AdminController@updateUserPermissions'
	);
		
	// -----------------------VENDER--------------------------------------------------
	
	// vender
	Route::post('/sales', 
		'SaleController@store'
	);

	// Tarjeta
	Route::get('/set-percentage-card/{percetane_card}', 
		'UserController@setPercentageCard'
	);
	

	// Actualizar venta
	Route::get('/sales/previus-next/{index}', 
		'SaleController@previusNext'
	);
	Route::put('/sales/{id}', 
		'SaleController@update'
	);

	// --------------------------------------------------------------------------------------
	// INGRESAR
		Route::post('/articles', 
			'ArticleController@store'
		);
		Route::get('/articles/get-by-bar-code/{bar_code}', 
			'ArticleController@getByBarCode'
		);
		Route::get('/articles/bar-codes', 
			'ArticleController@getBarCodes'
		);
		Route::get('/articles/names', 
			'ArticleController@names'
		);
		Route::get('/articles/previus-next/{index}', 
			'ArticleController@previusNext'
		);
		// Provedores de comercios
		Route::get('/providers', 
			'ProviderController@index'
		);
		Route::post('/providers', 
			'ProviderController@store'
		);
		Route::delete('/providers/{id}', 
			'ProviderController@delete'
		);
		// Categorias
		Route::get('/categories', 
			'CategoryController@index'
		);
		Route::post('/categories', 
			'CategoryController@store'
		);
		Route::delete('/categories/{id}', 
			'CategoryController@delete'
		);
		// Codigos de barra
		Route::get('/bar-codes', 
			'BarCodeController@index'
		);
		Route::post('/bar-codes', 
			'BarCodeController@store'
		);
		Route::get('/bar-codes/{bar_code}/{amount}/{size}/{text}', 
			'BarCodeController@store'
		);
		Route::delete('/bar-codes/{id}', 
			'BarCodeController@delete'
		);
		// Precios especials
		Route::get('special-prices', 
			'SpecialPriceController@index'
		);
		Route::post('special-prices', 
			'SpecialPriceController@store'
		);
		Route::delete('special-prices/{id}', 
			'SpecialPriceController@delete'
		);

	// --------------------------------------------------------------------------------------

	// VENDER
		Route::post('/sales', 
			'SaleController@store'
		);

	// --------------------------------------------------------------------------------------

	// LISTADO
	Route::get('/articles', 
		'ArticleController@index'
	);
	Route::get('/articles/paginated', 
		'ArticleController@paginated'
	);
	Route::get('/articles/{id}', 
		'ArticleController@show'
	);
	Route::get('/articles/search/{query}', 
		'ArticleController@search'
	);
	Route::put('/articles', 
		'ArticleController@update'
	);
	Route::post('/articles/filter', 
		'ArticleController@filter'
	);
	Route::delete('/articles/{ids}', 
		'ArticleController@delete'
	);

	// Categorias
	Route::post('/articles/category', 
		'ArticleController@updateCategory'
	);

	// Aumentar porcentaje
	Route::post('/articles/update-by-porcentage', 
		'ArticleController@updateByPorcentage'
	);
	
	// Imagenes
	Route::delete('/images/{image_id}', 
		'ImageController@delete'
	);
	Route::get('/articles/set-first-image/{image_id}', 
		'ArticleController@setFirstImage'
	);
	Route::post('/articles/image/{article_id}', 
		'ArticleController@addImage'
	);
	Route::get('/articles/set-first-image/{image_id}', 
		'ArticleController@setFirstImage'
	);
	Route::get('/articles/delete-image/{image_id}', 
		'ArticleController@deleteImage'
	);

	// Marcadores
	Route::get('/markers', 
		'MarkerController@index'
	);
	Route::post('/markers', 
		'MarkerController@store'
	);
	Route::delete('/markers/{id}', 
		'MarkerController@delete'
	);
	Route::get('/marker-groups', 
		'MarkerGroupController@index'
	);
	Route::get('/marker-groups/only-with-markers', 
		'MarkerGroupController@indexOnlyWithMarkers'
	);
	Route::post('/marker-groups', 
		'MarkerGroupController@store'
	);
	Route::delete('/marker-groups/{id}', 
		'MarkerGroupController@delete'
	);
	Route::get('/articles/with-marker/{id}', 
		'ArticleController@withMarker'
	);
	Route::get('/marker-groups/add-marker-to-group/{marker_group_id}/{article_id}', 
		'MarkerGroupController@addMarkerToGroup'
	);

	// Featured
	Route::get('/articles/set-featured/{article_id}',
		'ArticleController@setFeatured'
	);

	// --------------------------------------------------------------------------------------

	// Ventas
	Route::get('/sales', 
		'SaleController@index'
	);
	Route::delete('/sales/{sales_id}', 
		'SaleController@deleteSales'
	);

	// Descuentos
	Route::get('/discounts', 
		'DiscountController@index'
	);
	Route::put('/discounts', 
		'DiscountController@update'
	);
	Route::post('/discounts', 
		'DiscountController@store'
	);

	// Vendedores
	Route::get('/sellers', 
		'SellerController@index'
	);
	// Devuelve las comisiones de las ventas que le corresponden al vendedor
	Route::get('/commissions/from-commissioner/{commissioner_id}', 
		'CommissionController@fromCommissioner'
	);
	Route::post('/commissioners/update-percetage', 
		'CommissionController@updatePercentage'
	);

	// Comisionados
	Route::get('/commissioners', 
		'CommissionerController@index'
	);
	Route::post('/commissioners/pago', 
		'CommissionController@pagoForCommissioner'
	);

	// Tipos de venta
	Route::get('/sale-types', 
		'SaleTypeController@index'
	);

	// Clientes
	Route::get('/clients', 
		'ClientController@index'
	);
	Route::post('/clients', 
		'ClientController@store'
	);
	Route::put('/clients', 
		'ClientController@update'
	);
	Route::delete('/clients/{id}', 
		'ClientController@delete'
	);
	Route::get('/clients/current-acounts/{id}', 
		'ClientController@currentAcounts'
	);
	Route::post('/clients/saldo-inicial', 
		'ClientController@saldoInicial'
	);
	Route::post('/clients/pago', 
		'CurrentAcountController@pagoFromClient'
	);
	Route::post('/clients/nota-credito', 
		'CurrentAcountController@notaCredito'
	);
	// Ventas de un cliente
	Route::get('/sales/client/{client_id}', 
		'SaleController@saleClient'
	);
	Route::get('/sales/pagar-deuda/{sale_id}/{debt}', 
		'SaleController@pagarDeuda'
	);
	// PreviusDays
	Route::get('/sales/days-previus-sales/{index}/{retroceder}/{fecha_limite?}', 
		'SaleController@daysPreviusSales'
	);
	Route::get('/sales/prev/{index}', 
		'SaleController@previusDays'
	);
	// Buscar por fecha
	Route::get('/sales/from-date/{from}/{to}/{last_day_inclusive}', 
		'SaleController@fromDate'
	);
	Route::get('/sales/only-one-date/{date}', 
		'SaleController@onlyOneDate'
	);
	// Horarios de ventas
	Route::get('/sale-time', 
		'SaleTimeController@index'
	);
	Route::get('/sale-time/allowed', 
		'SaleTimeController@allowed'
	);
	Route::get('/sales/from-sale-time/{sale_time_id}/{inverted}/{only_one_date?}', 
		'SaleController@fromSaleTime'
	);
	Route::post('/sale-time', 
		'SaleTimeController@store'
	);
	Route::get('/sale-time/{id}', 
		'SaleTimeController@delete'
	);
	// Estadisticas
	Route::get('sales/statistics', 
		'SaleController@statistics'
	);

	// --------------------------------------------------------------------------------------

	// EMPLEADOS
	Route::get('/employees', 
		'UserController@getEmployees'
	);
	Route::get('/employees/{name}/{password}/{permissions}', 
		'UserController@saveEmployee'
	);
	Route::put('/employees/permissions/{employee_id}', 
		'UserController@updateEmployeePermissions'
	);
	Route::delete('/employees/{id}', 
		'UserController@deleteEmployee'
	);
	Route::get('/permissions', 
		'PermissionController@index'
	);
	Route::get('/permissions/sale-time', 
		'PermissionController@saleTime'
	);

	// --------------------------------------------------------------------------------------

	// ONLINE
	// Questions
	Route::get('/questions', 
		'QuestionController@index'
	);
	Route::delete('/questions/{id}', 
		'QuestionController@delete'
	);
	// Answers
	Route::post('/answers', 
		'AnswerController@store'
	);
	// Orders
	Route::get('/orders/unconfirmed', 
		'OrderController@unconfirmed'
	);
	Route::get('/orders/confirmed-finished', 
		'OrderController@confirmedFinished'
	);
	Route::get('/orders/confirm/{order_id}', 
		'OrderController@confirm'
	);
	Route::get('/orders/cancel/{order_id}', 
		'OrderController@cancel'
	);
	Route::get('/orders/finish/{order_id}', 
		'OrderController@finish'
	);
	Route::get('/orders/deliver/{order_id}', 
		'OrderController@deliver'
	);
	// Examine
	Route::get('/online/articles/most-view/{weeks_ago}', 
		'ArticleController@mostView'
	);
	Route::get('/online/categories/most-view/{weeks_ago}', 
		'CategoryController@mostView'
	);
	// Title
	Route::get('/online/titles', 
		'TitleController@index'
	);
	Route::put('/online/titles', 
		'TitleController@update'
	);
});




