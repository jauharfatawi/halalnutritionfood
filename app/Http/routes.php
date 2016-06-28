<?php

$s = 'public.';
Route::get('/',         ['as' => $s . 'home',   'uses' => 'PagesController@getHome']);
Route::get('/about',         ['as' => $s . 'about',   'uses' => 'PagesController@getAbout']);

$a = 'auth.';
Route::get('/login',            ['as' => $a . 'login',          'uses' => 'Auth\AuthController@getLogin']);
Route::post('/login',           ['as' => $a . 'login-post',     'uses' => 'Auth\AuthController@postLogin']);
Route::get('/register',         ['as' => $a . 'register',       'uses' => 'Auth\AuthController@getRegister']);
Route::post('/register',        ['as' => $a . 'register-post',  'uses' => 'Auth\AuthController@postRegister']);
Route::get('/password',         ['as' => $a . 'password',       'uses' => 'Auth\PasswordResetController@getPasswordReset']);
Route::post('/password',        ['as' => $a . 'password-post',  'uses' => 'Auth\PasswordResetController@postPasswordReset']);
Route::get('/password/{token}', ['as' => $a . 'reset',          'uses' => 'Auth\PasswordResetController@getPasswordResetForm']);
Route::post('/password/{token}',['as' => $a . 'reset-post',     'uses' => 'Auth\PasswordResetController@postPasswordResetForm']);

$s = 'social.';
Route::get('/social/redirect/{provider}',   ['as' => $s . 'redirect',   'uses' => 'Auth\AuthController@getSocialRedirect']);
Route::get('/social/handle/{provider}',     ['as' => $s . 'handle',     'uses' => 'Auth\AuthController@getSocialHandle']);

Route::group(['middleware' => 'auth:administrator'], function()
{
//    $a = 'admin.';
    Route::resource('additive','IngredientController', ['except' => ['index', 'show']]);
    Route::post('foodproduct/verify/{id}',['as' => 'foodproduct.verify', 'uses' => 'FoodProductController@verify']);
    Route::delete('halalSource/{id}',['as' => 'halalSource.destroy', 'uses' => 'IngredientController@halalSourceDestroy']);
    
});

Route::group(['prefix' => 'user', 'middleware' => 'auth:user'], function()
{
//    $a = 'user.';
});

Route::group(['middleware' => 'auth:all'], function()
{
    $a = 'authenticated.';
    Route::get('/logout', ['as' => $a . 'logout', 'uses' => 'Auth\AuthController@getLogout']);
    Route::resource('foodproduct','FoodProductController', ['except' => ['index', 'show']]);
    Route::delete('certificate/{id}',['as' => 'certificate.destroy', 'uses' => 'FoodProductController@certificateDestroy']);
});

Route::resource('foodproduct','FoodProductController', ['only' => ['index', 'show']]);
Route::resource('additive','IngredientController', ['only' => ['index', 'show']]);
Route::controller('api', 'ApiController',[
	'getFoodProductList' => 'api.foodproduct.list',
	'getAdditiveList' => 'api.additive.list',
	'getIngredientList' => 'api.ingredient.list',
	'getAdditiveData' => 'api.additive.data',
	'getFoodProductData' => 'api.foodproduct.data',
	'getManufactureList' => 'api.manufacture.list',
	'getHalalOrgList' => 'api.halalOrg.list',
	'getCertOrgList' => 'api.certOrg.list',
	'getWriteToTurtle' => 'api.writeTurtle',
	'getSparql' => 'api.sparql',
	'postSparql' => 'api.sparql',
]);

Route::controller('RDFBrowser', 'RDFBrowserController',[
	'getIndex' => 'rdf.browser'
]);
