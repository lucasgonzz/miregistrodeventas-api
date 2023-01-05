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

// Update Features
Route::get('update-feature', 'UpdateFeatureController@index');

Route::middleware('auth:sanctum')->group(function () {

	Route::get('/auth-user', 'UserController@user');

	// Generals
	Route::post('search/{model_name}', 'SearchController@search');
	Route::post('set-comercio-city-user', 'GeneralController@setComercioCityUser');


	// -----------------------CONFIGURACION------------------------------------------
	Route::put('/user/password', 
		'UserController@updatePassword'
	);
	Route::put('/user', 
		'UserController@update'
	);
	Route::put('/user/image/{id}', 
		'UserController@updateImage'
	);
	Route::put('/user/default_article_image_url', 
		'UserController@defautlArticleImage'
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

	// UserPayments
	Route::get('/user-payment/{model_id}/{from_date}/{until_date?}', 
		'UserPaymentController@index'
	);
	Route::resource('user-payment', 'UserPaymentController');

	// PaymentMethods
	Route::resource('payment-method', 'PaymentMethodController');

	// PaymentMethodTypes
	Route::get('payment-method-type', 'PaymentMethodTypeController@index');

	// DeliveryZones
	Route::resource('delivery-zone', 'DeliveryZoneController');

	// Platelets
	Route::resource('platelet', 'PlateletController');

	// CreditCards
	Route::resource('credit-card', 'CreditCardController');

	// CreditCardPaymentPlans
	Route::resource('credit-card-payment-plan', 'CreditCardPaymentPlanController');

	// -----------------------SUPER--------------------------------------------------
	Route::resource('super-user', 'SuperUserController');

	// Extencions
	Route::get('extencion', 'ExtencionController@index');


	Route::get('plans', 
		'SuperController@plans'
	);

	// SuperBudget
	Route::resource('super-budget', 'SuperBudgetController');

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
	Route::get('/budget/{from_date}/{until_date?}', 
		'BudgetController@index'
	);
	Route::get('/budget-previus-days/{index}', 
		'BudgetController@previusDays'
	);
	Route::post('/budget', 
		'BudgetController@store'
	);
	Route::put('/budget/{id}', 
		'BudgetController@update'
	);
	Route::put('/budget/confirm', 
		'BudgetController@confirm'
	);
	Route::delete('/budget/{id}', 
		'BudgetController@delete'
	);

	// Budget Status
	Route::get('/budget-status', 
		'BudgetStatusController@index'
	);

	// OrderProductions
	Route::get('/order-production/{from_date}/{until_date?}', 
		'OrderProductionController@index'
	);
	Route::get('/order-production-previus-days/{index}', 
		'OrderProductionController@previusDays'
	);
	Route::post('/order-production', 
		'OrderProductionController@store'
	);
	Route::put('/order-production/{id}', 
		'OrderProductionController@update'
	);
	Route::post('/order-production/pdf', 
		'OrderProductionController@setPdf'
	);
	Route::delete('/order-production/{id}', 
		'OrderProductionController@delete'
	);
	Route::put('/order-production/finish/{id}', 
		'OrderProductionController@finish'
	);
		

	// Recetas - recipe
	Route::resource('recipe', 'RecipeController');

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
	Route::resource('/order-production-status', 'OrderProductionStatusController');

	// Locations
	Route::resource('location', 'LocationController');

	// ProviderOrders
	Route::get('/provider-order/{from_date}/{until_date?}', 
		'ProviderOrderController@index'
	);
	Route::get('/provider-order-previus-days/{index}', 
		'ProviderOrderController@previusDays'
	);
	Route::post('/provider-order', 
		'ProviderOrderController@store'
	);
	Route::put('/provider-order/{id}', 
		'ProviderOrderController@update'
	);
	Route::post('/provider-order/received', 
		'ProviderOrderController@setReceived'
	);
	Route::post('/provider-order/excel/import', 
		'ProviderOrderController@import'
	);
	Route::delete('/provider-order/{id}', 
		'ProviderOrderController@destroy'
	);

	// ProviderOrderAfipTickets
	Route::resource('provider-order-afip-ticket', 'ProviderOrderAfipTicketController');

	// ProviderOrderStatuses
	Route::get('/provider-order-status', 
		'ProviderOrderStatusController@index'
	);


	// -----------------------VENDER--------------------------------------------------
	
	// vender
	Route::post('/sales', 
		'SaleController@store'
	);
	
	// Actualizar venta
	Route::get('/sales/previus-next/{index}', 
		'SaleController@previusNext'
	);
	Route::put('/sales/{id}', 
		'SaleController@update'
	);
	Route::put('/sale/update-prices/{id}', 
		'SaleController@updatePrices'
	);
	Route::get('/sale/get-previus-next-index/{created_at}', 
		'SaleController@getIndexPreviusNext'
	);

	// --------------------------------------------------------------------------------------
	// ARTICLE
		Route::get('/article/index/{status}', 
			'ArticleController@index'
		);
		Route::get('/article/{id}', 
			'ArticleController@show'
		);
		Route::put('/article', 
			'ArticleController@update'
		);
		Route::put('/article/update-prop/{prop}', 
			'ArticleController@updateProp'
		);
		Route::put('/article/update-props', 
			'ArticleController@updateProps'
		);
		Route::post('/article/delete', 
			'ArticleController@delete'
		);
		Route::post('/article', 
			'ArticleController@store'
		);
		Route::post('/article/new-article', 
			'ArticleController@newArticle'
		);
		Route::post('/article/excel/import', 
			'ArticleController@import'
		);

		// Deposits
		Route::resource('deposit', 'DepositController');

		// Provedores 
		Route::resource('provider', 'ProviderController');
		Route::post('/provider/excel/import', 
			'ProviderController@import'
		);
		// Provider-prices-list
		Route::resource('provider-price-list', 'ProviderPriceListController');

		// Categorias
		Route::resource('/category', 'CategoryController');
		// Sub Categorias
		Route::resource('/sub-category', 'SubCategoryController');
		Route::get('/sub-categories/for-vender/{ids}', 
			'SubCategoryController@forVender'
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
		Route::get('condition', 
			'ConditionController@index'
		);
		Route::put('/condition/{id}', 
			'ConditionController@update'
		);
		Route::post('/condition', 
			'ConditionController@store'
		);
		Route::delete('/condition/{id}', 
			'ConditionController@delete'
		);

	// --------------------------------------------------------------------------------------

	// VENDER
	Route::post('/sales', 
		'SaleController@store'
	);

	// --------------------------------------------------------------------------------------

	// Ticket pdf
	Route::get('/article/pdf/{ids}', 
		'ArticleController@pdf'
	);

	// Copiar descripciones
	Route::put('/articles/descriptions-copy', 
		'ArticleController@descriptionsCopy'
	);

	// Prices List
	Route::resource('/prices-list', 'PricesListController');

	// PriceType
	Route::resource('price-type', 'PriceTypeController');

	// Combos
	Route::resource('combo', 'ComboController');

	// Services
	Route::post('services', 'ServiceController@store');

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
	Route::get('/article/set-first-image/{image_id}', 
		'ArticleController@setFirstImage'
	);
	Route::put('/article/image/{article_id}', 
		'ArticleController@addImage'
	);
	Route::get('/articles/delete-image/{image_id}', 
		'ArticleController@deleteImage'
	);
	// Copiar imagenes
	Route::put('/article/images-copy', 
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
	Route::get('/article/set-featured/{article_id}',
		'ArticleController@setFeatured'
	);
	// Online
	Route::get('/article/set-online/{article_id}',
		'ArticleController@setOnline'
	);

	// --------------------------------------------------------------------------------------

	// Ventas
	Route::get('/sale/{from_date?}/{until_date?}', 
		'SaleController@index'
	);
	Route::get('/sale-show/{id}', 
		'SaleController@show'
	);
	Route::put('/sale/save-current-acount/{id}', 
		'SaleController@saveCurrentAcount'
	);
	Route::delete('/sale/{sales_id}', 
		'SaleController@deleteSales'
	);

	// Afip
	Route::get('/afip/importes/{sale_id}', 'AfipWsController@getImportes');
	Route::post('/sale/make-afip-ticket/{sale_id}', 'AfipWsController@init');

	// Descuentos
	Route::resource('/discount', 'DiscountController');

	// Recargos
	Route::resource('/surchage', 'SurchageController');

	// Vendedores
	Route::get('/seller', 
		'SellerController@index'
	);
	Route::post('/seller', 
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
	Route::resource('/client', 'ClientController');
	Route::get('/client-show/{id}', 'ClientController@show');

	// CurrentAcounts
	Route::get('/current-acount/{model_name}/{model_id}/{months_ago}', 
		'CurrentAcountController@index'
	);
	Route::post('/current-acount/pago', 
		'CurrentAcountController@pago'
	);
	Route::post('/current-acount/nota-credito', 
		'CurrentAcountController@notaCredito'
	);
	Route::post('/current-acount/nota-debito', 
		'CurrentAcountController@notaDebito'
	);
	Route::post('/current-acount/saldo-inicial', 
		'CurrentAcountController@saldoInicial'
	);
	Route::delete('/current-acount/{model_name}/{id}', 
		'CurrentAcountController@delete'
	);
	
	Route::post('/clients/excel/import', 
		'ClientController@import'
	);
	Route::post('/current-acount/update-debe', 
		'CurrentAcountController@updateDebe'
	);
	Route::post('/current-acount/excel/import/{client_id}', 
		'CurrentAcountController@import'
	);
	Route::get('/clients/check-saldos/{client_id}', 
		'ClientController@checkCurrentAcounts'
	);

	// CurrentAcountsPaymentMethods
	Route::get('/current-acount-payment-methods', 
		'CurrentAcountPaymentMethodController@index'
	);


	// Ivas
	Route::get('/iva', 
		'IvaController@index'
	);
	// Ivas Conditions
	Route::get('/iva-condition', 
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
	Route::get('/sale-previus-days/{index}', 
		'SaleController@previusDays'
	);
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
	Route::resource('/employee', 'EmployeeController');
	Route::get('/permissions', 
		'PermissionController@index'
	);
	// Route::get('/permissions/extencions', 
	// 	'PermissionController@extencions'
	// );

	// --------------------------------------------------------------------------------------

	// ONLINE
	// Cupons
	Route::resource('cupon', 'CuponController',);
	// Questions
	Route::get('/questions', 
		'QuestionController@index'
	);
	Route::delete('/questions/{id}', 
		'QuestionController@delete'
	);
	// Buyers
	Route::resource('/buyer', 'BuyerController');
	
	// Messages
	Route::get('/message/{buyer_id}', 
		'MessageController@fromBuyer'
	);
	Route::get('/message/set-read/{buyer_id}', 
		'MessageController@setRead'
	);
	Route::post('/message', 
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
	Route::get('/order/{from_date}/{until_date?}', 
		'OrderController@index'
	);
	Route::get('/order-unconfirmed', 
		'OrderController@indexUnconfirmed'
	);
	Route::get('/order-previus-days/{index}', 
		'OrderController@previusDays'
	);
	Route::get('/order-show/{id}', 
		'OrderController@show'
	);
	Route::put('/order/{id}', 
		'OrderController@update'
	);
	Route::put('/order/update-status/{order_id}', 
		'OrderController@updateStatus'
	);
	Route::put('/order/cancel/{order_id}', 
		'OrderController@cancel'
	);
	// OrderStats
	Route::get('/order-status', 
		'OrderStatusController@index'
	);

	// MercadoPago
	Route::get('/mercado-pago/payment/{payment_id}',
		'MercadoPagoController@payment'
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
	Route::resource('/title', 'TitleController');
	Route::put('/title/image/{id}', 
		'TitleController@updateImage'
	);
	// Brands
	Route::get('/brand', 
		'BrandController@index'
	);
	Route::put('/brand/{id}', 
		'BrandController@update'
	);
	Route::post('/brand', 
		'BrandController@store'
	);
	Route::delete('/brand/{id}', 
		'BrandController@delete'
	);
	Route::post('/articles/brand', 
		'ArticleController@updateBrand'
	);
});




