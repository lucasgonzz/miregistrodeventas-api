<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Route::get('/clients', 
// 	'ClientController@index'
// );

// Home
Route::get('/plans', 
	'PlanController@index'
);

Route::middleware('auth:sanctum')->group(function () {

	Route::get('/user', 'UserController@user');


	// -----------------------CONFIGURACION------------------------------------------
	Route::put('/user/password', 
		'UserController@updatePassword'
	);
	Route::put('/user', 
		'UserController@update'
	);
	Route::put('/users/image/{id}', 
		'UserController@updateImage'
	);
	Route::get('/user/trial/contratar-servicio', 
		'UserController@contratarServicio'
	);
	Route::post('/addresses', 
		'AddressController@store'
	);
	Route::delete('/addresses/{id}', 
		'AddressController@delete'
	);
	// Afip Information
	Route::put('/afip-information', 
		'AfipInformationController@update'
	);
	// Workdays
	Route::get('/workdays', 
		'WorkdayController@index'
	);
	Route::put('/workdays/remove-schedule', 
		'WorkdayController@removeSchedule'
	);
	Route::put('/workdays/add-schedule', 
		'WorkdayController@addSchedule'
	);
	// Schedules
	Route::get('/schedules', 
		'ScheduleController@index'
	);
	Route::post('/schedules', 
		'ScheduleController@store'
	);
	Route::put('/schedules', 
		'ScheduleController@update'
	);
	Route::delete('/schedules/{id}', 
		'ScheduleController@delete'
	);

	// PaymentMethods
	Route::resource('payment-methods', 'PaymentMethodController');

	// DeliveryZones
	Route::resource('delivery-zones', 'DeliveryZoneController');

	// -----------------------SUPER--------------------------------------------------
	Route::get('super/admins', 
		'SuperController@admins'
	);
	Route::get('super/commerces', 
		'SuperController@commerces'
	);
	Route::put('super/commerces', 
		'SuperController@updateCommerce'
	);
	Route::get('super/plans', 
		'SuperController@plans'
	);
	Route::put('super/plans', 
		'SuperController@updatePlan'
	);
	Route::get('super/permissions', 
		'SuperController@permissions'
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

	// Subscriptions
	Route::post('/subscriptions', 
		'SubscriptionController@store'
	);
	Route::get('/subscriptions/from-plan/{id}', 
		'SubscriptionController@subscriptionsFromPlan'
	);
	Route::delete('/subscriptions', 
		'SubscriptionController@delete'
	);
		
	// -----------------------PRODUCCION--------------------------------------------------

	// Budgets
	Route::get('/budgets', 
		'BudgetController@index'
	);
	Route::post('/budgets', 
		'BudgetController@store'
	);
	Route::put('/budgets', 
		'BudgetController@update'
	);
	Route::put('/budgets/confirm', 
		'BudgetController@confirm'
	);
	Route::delete('/budgets/{id}', 
		'BudgetController@delete'
	);

	// OrderProductions
	Route::get('/order-productions', 
		'OrderProductionController@index'
	);
	Route::post('/order-productions', 
		'OrderProductionController@store'
	);
	Route::put('/order-productions', 
		'OrderProductionController@update'
	);
	Route::post('/order-productions/pdf', 
		'OrderProductionController@setPdf'
	);
	Route::delete('/order-productions/{id}', 
		'OrderProductionController@delete'
	);
	Route::get('/order-productions/products-pdf/{id}', 
		'OrderProductionController@productsPdf'
	);

	// ProductDelivery
	Route::post('/budget-product-deliveries', 
		'BudgetProductDeliveryController@store'
	);
	Route::delete('/budget-product-deliveries/{id}', 
		'BudgetProductDeliveryController@delete'
	);

	// ProductArticleStock
	Route::post('/budget-product-article-stocks', 
		'BudgetProductArticleStockController@store'
	);
	Route::delete('/budget-product-article-stocks/{id}', 
		'BudgetProductArticleStockController@delete'
	);

	// OrderProductionStatuses
	Route::get('/order-production-statuses', 
		'OrderProductionStatusController@index'
	);

	// Locations
	Route::resource('locations', 'LocationController');

	// ProviderOrders
	Route::get('/provider-orders', 
		'ProviderOrderController@index'
	);
	Route::post('/provider-orders', 
		'ProviderOrderController@store'
	);
	Route::put('/provider-orders/{id}', 
		'ProviderOrderController@update'
	);
	Route::post('/provider-orders/received', 
		'ProviderOrderController@setReceived'
	);
	Route::delete('/provider-orders/{id}', 
		'ProviderOrderController@destroy'
	);


	// -----------------------VENDER--------------------------------------------------
	
	// vender
	Route::post('/sales', 
		'SaleController@store'
	);

	// Tarjeta
	Route::put('/users/set-percentage-card', 
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
		Route::post('/articles/new-article', 
			'ArticleController@newArticle'
		);
		Route::put('/articles/price', 
			'ArticleController@updatePrice'
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
		Route::post('/articles/excel/import', 
			'ArticleController@import'
		);


		// Provedores de comercios
		Route::resource('providers', 'ProviderController');
		Route::post('/providers/excel/import', 
			'ProviderController@import'
		);
		// Icons
		Route::get('/icons', 
			'IconController@index'
		);
		// Categorias
		Route::get('/categories', 
			'CategoryController@index'
		);
		Route::post('/categories', 
			'CategoryController@store'
		);
		Route::put('/categories', 
			'CategoryController@update'
		);
		Route::delete('/categories/{id}', 
			'CategoryController@delete'
		);
		// Sub Categorias
		Route::get('/sub-categories', 
			'SubCategoryController@index'
		);
		Route::post('/sub-categories', 
			'SubCategoryController@store'
		);
		Route::put('/sub-categories', 
			'SubCategoryController@update'
		);
		Route::delete('/sub-categories/{id}', 
			'SubCategoryController@delete'
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
		// Tags
		Route::get('tags', 
			'TagController@index'
		);
		Route::post('tags', 
			'TagController@store'
		);
		// Colors
		Route::get('colors', 
			'ColorController@index'
		);
		// Sizes
		Route::get('sizes', 
			'SizeController@index'
		);
		// Conditions
		Route::get('conditions', 
			'ConditionController@index'
		);
		Route::put('/conditions', 
			'ConditionController@update'
		);
		Route::post('/conditions', 
			'ConditionController@store'
		);
		Route::delete('/conditions/{id}', 
			'ConditionController@delete'
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
	Route::put('/articles/{prop}', 
		'ArticleController@updateProp'
	);
	Route::post('/articles/filter', 
		'ArticleController@filter'
	);
	Route::post('/articles/delete', 
		'ArticleController@delete'
	);

	// Ticket pdf
	Route::get('/articles/pdf/{ids}', 
		'ArticleController@pdf'
	);

	// Copiar descripciones
	Route::put('/articles/descriptions-copy', 
		'ArticleController@descriptionsCopy'
	);

	// Prices List
	Route::get('/prices-lists', 
		'PricesListController@index'
	);
	Route::post('/prices-lists', 
		'PricesListController@store'
	);
	Route::put('/prices-lists/{id}', 
		'PricesListController@update'
	);
	Route::delete('/prices-lists/{id}', 
		'PricesListController@delete'
	);

	// Combos
	Route::resource('combos', 'ComboController');

	// Variants
	Route::post('/articles/variants/{article_id}', 
		'ArticleController@setVariants'
	);
	Route::delete('/articles/variants/{article_id}', 
		'ArticleController@deleteVariants'
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
	Route::put('/images/set-color', 
		'ImageController@setColor'
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
	// Copiar imagenes
	Route::put('/articles/images-copy', 
		'ArticleController@imagesCopy'
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
	// Online
	Route::get('/articles/set-online/{article_id}',
		'ArticleController@setOnline'
	);

	// --------------------------------------------------------------------------------------

	// Ventas
	Route::get('/sales', 
		'SaleController@index'
	);
	Route::delete('/sales/{sales_id}', 
		'SaleController@deleteSales'
	);

	// Afip
	Route::get('/afip/importes/{sale_id}', 'AfipWsController@getImportes');
	Route::post('/afip/{sale_id}', 'AfipWsController@init');

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
	Route::post('/sellers', 
		'SellerController@store'
	);
	// Devuelve las comisiones de las ventas que le corresponden al vendedor
	Route::get('/commissions/from-commissioner/{commissioner_id}/{weeks_ago}', 
		'CommissionController@fromCommissioner'
	);
	Route::post('/commissions/update-percentage', 
		'CommissionController@updatePercentage'
	);

	// Comisionados
	Route::get('/commissioners', 
		'CommissionerController@index'
	);
	Route::post('/commissioners/pago', 
		'CommissionController@pagoForCommissioner'
	);
	Route::get('/commissioners/check-saldos/{commissioner_id}', 
		'CommissionerController@checkSaldos'
	);

	// Tipos de venta
	Route::get('/sale-types', 
		'SaleTypeController@index'
	);

	// Clientes
	Route::get('/clients', 
		'ClientController@index'
	);
	Route::get('/clients/{id}', 
		'ClientController@show'
	);
	Route::post('/clients', 
		'ClientController@store'
	);
	Route::put('/clients/{id}', 
		'ClientController@update'
	);
	Route::delete('/clients/{id}', 
		'ClientController@delete'
	);

	// CurrentAcounts
	Route::get('/clients/current-acounts/{id}/{months_ago}', 
		'ClientController@currentAcounts'
	);
	Route::post('/clients/excel/import', 
		'ClientController@import'
	);
	Route::post('/current-acounts/update-debe', 
		'CurrentAcountController@updateDebe'
	);
	Route::delete('/current-acounts/{id}', 
		'CurrentAcountController@delete'
	);
	Route::post('/current-acounts/excel/import/{client_id}', 
		'CurrentAcountController@import'
	);
	Route::get('/clients/check-saldos/{client_id}', 
		'ClientController@checkCurrentAcounts'
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

	// CurrentAcountsPaymentMethods
	Route::get('/current-acount-payment-methods', 
		'CurrentAcountPaymentMethodController@index'
	);


	// Ivas
	Route::get('/ivas', 
		'IvaController@index'
	);
	// Ivas Conditions
	Route::get('/iva-conditions', 
		'IvaConditionController@index'
	);
	// Ventas de un cliente
	Route::get('/sales/client/{client_id}', 
		'SaleController@saleClient'
	);
	Route::get('/sales/pagar-deuda/{sale_id}/{debt}', 
		'SaleController@pagarDeuda'
	);
	// PreviusDays
	Route::get('/sales/previus-days/{index}', 
		'SaleController@previusDays'
	);
	// Buscar por fecha
	Route::get('/sales/from-date/{date}', 
		'SaleController@fromDate'
	);
	// Route::get('/sales/only-one-date/{date}', 
	// 	'SaleController@onlyOneDate'
	// );
	// Horarios de ventas
	Route::get('/sale-time', 
		'SaleTimeController@index'
	);
	Route::get('/sale-time/allowed', 
		'SaleTimeController@allowed'
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
		'EmployeeController@index'
	);
	Route::post('/employees', 
		'EmployeeController@store'
	);
	Route::put('/employees', 
		'EmployeeController@update'
	);
	Route::delete('/employees/{id}', 
		'EmployeeController@delete'
	);
	Route::get('/permissions', 
		'PermissionController@index'
	);
	// Route::get('/permissions/extencions', 
	// 	'PermissionController@extencions'
	// );

	// --------------------------------------------------------------------------------------

	// ONLINE
	// Cupons
	Route::get('cupons', 
		'CuponController@index',
	);
	Route::post('cupons', 
		'CuponController@store',
	);
	Route::delete('cupons/{id}', 
		'CuponController@delete',
	);
	// Questions
	Route::get('/questions', 
		'QuestionController@index'
	);
	Route::delete('/questions/{id}', 
		'QuestionController@delete'
	);
	// Buyers
	Route::get('/buyers', 
		'BuyerController@index'
	);
	// Messages
	Route::get('/messages/{buyer_id}', 
		'MessageController@fromBuyer'
	);
	Route::get('/messages/set-read/{buyer_id}', 
		'MessageController@setRead'
	);
	Route::post('/messages', 
		'MessageController@store'
	);
	// Calls
	Route::get('/calls', 
		'CallController@index'
	);
	Route::put('/calls', 
		'CallController@realized'
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
	Route::put('/orders/cancel', 
		'OrderController@cancel'
	);
	Route::get('/orders/finish/{order_id}', 
		'OrderController@finish'
	);
	Route::get('/orders/deliver/{order_id}', 
		'OrderController@deliver'
	);
	// Examine
	// Route::get('/google-analytics', 
	// 	'GoogleAnalyticsController@getAnalyticsSummary'
	// );
	Route::get('/articles/most-viewed/{weeks_ago}', 
		'ArticleController@mostViewed'
	);
	Route::get('/sub-categories/most-viewed/{weeks_ago}', 
		'SubCategoryController@mostViewed'
	);
	// Title
	Route::get('/titles', 
		'TitleController@index'
	);
	Route::put('/titles/image/{id}', 
		'TitleController@updateImage'
	);
	Route::put('/titles', 
		'TitleController@update'
	);
	Route::post('/titles', 
		'TitleController@store'
	);
	Route::delete('/titles/{id}', 
		'TitleController@delete'
	);
	// Brands
	Route::get('/brands', 
		'BrandController@index'
	);
	Route::put('/brands', 
		'BrandController@update'
	);
	Route::post('/brands', 
		'BrandController@store'
	);
	Route::delete('/brands/{id}', 
		'BrandController@delete'
	);
	Route::post('/articles/brand', 
		'ArticleController@updateBrand'
	);
});




