<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', 'WelcomeController@index');
Route::get('/login', 'WelcomeController@login', array('menu_transactions'=> 'active'));
Route::get('contact', 'WelcomeController@contact');


Route::get('/dbg_do_to',  function () {
	print file_get_contents('../to_do.txt');
});


Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

Route::get('/helper/showProductsFileForWget', 'HelperController@showProductsFileForWget');


Route::get('/autoinstalator/upload',  'AutoInstalatorController@uploadFiles'); 
Route::get('/autoinstalator/showMeBucket',  'AutoInstalatorController@showMeBucket'); 

Route::get('/activities/install', ['middleware' => 'auth', 'uses' => 'ActivitiesController@index']);
Route::get('/activities/statistics', ['middleware' => 'auth', 'uses' => 'ActivitiesController@statistics']);
Route::get('/activities/user_logs', ['middleware' => 'auth', 'uses' => 'ActivitiesController@userLogs']);
Route::get('/activities/global_logs', ['middleware' => 'auth', 'uses' => 'ActivitiesController@globalLogs']);
Route::get('/activities/free_installations', ['middleware' => 'auth', 'uses' => 'ActivitiesController@free_installations']);

Route::get('/home', ['middleware' => 'auth', 'uses' => 'HomeController@index']);
Route::get('/manualMode', ['middleware' => 'auth', 'uses' => 'ManualModeController@index']);
Route::get('/manualMode/{id}', ['middleware' => 'auth', 'uses' => 'ManualModeController@index']);
Route::get('/ManualDownloaderAjaxRequest/{id}', ['middleware' => 'auth', 'uses' => 'ManualModeController@ManualDownloaderAjaxRequest']);
Route::get('/ManualDoFTPactionAjaxRequest/{id}', ['middleware' => 'auth', 'uses' => 'ManualModeController@ManualDoFTPactionAjaxRequest']);

Route::get('email_templates', ['middleware' => 'auth', 'uses' => 'EmailTemplatesController@index']);
Route::get('email_templates/form_submitted', ['middleware' => 'auth', 'uses' => 'EmailTemplatesController@formSubmitted']);
Route::get('email_templates/product_installed', ['middleware' => 'auth', 'uses' => 'EmailTemplatesController@productInstalled']);
Route::post('email_templates/update_submit_form_confirmation', ['middleware' => 'auth', 'uses' => 'EmailTemplatesController@update_submit_form_confirmation']);
Route::post('email_templates/update_instalation_confirmation', ['middleware' => 'auth', 'uses' => 'EmailTemplatesController@update_instalation_confirmation']);

Route::get('autonomous_mode', ['middleware' => 'auth', 'uses' => 'AutonomousController@index']);

Route::post('autonomous_mode/edit', ['middleware' => 'auth', 'AutonomousController@edit']);

//Route::get('/install/{id}/{options}',  'UserController@showUserForm2'); // this is needed on show_available_products: 'http://aaroftp.local/install/'+product_id+'/0';

Route::get('/do_action', ['middleware' => 'auth', 'uses' => 'TransactionsController@index']);
Route::get('/edit_transaction/{id}', ['middleware' => 'auth', 'uses' => 'TransactionsController@editTransaction']);
Route::get('/show_logs/{id}', ['middleware' => 'auth', 'uses' => 'TransactionsController@showLogs']);
Route::post('/save_transaction', ['middleware' => 'auth', 'uses' => 'TransactionsController@saveTransaction']);

Route::get('/',  'UserController@showUserForm');   // for everybody
Route::post('/', 'UserController@saveUserForm');   // for everybody 
Route::get('/testftp',  'UserController@testftp'); // for everybody

Route::get('/settings', ['middleware' => 'auth', 'uses' => 'SettingsController@index']);
Route::get('/bucketfile', ['middleware' => 'auth', 'uses' => 'BucketfileController@index']);

Route::get('/bucketfile/update_bucket_ajax', ['middleware' => 'auth', 'uses' => 'BucketfileController@updateS3BucketByAjax']);

Route::get('/bucketfile/generated_domains',  ['middleware' => 'auth', 'uses' => 'BucketfileController@generatedDomains']);
Route::get('/bucketfile/final_file',  ['middleware' => 'auth', 'uses' => 'BucketfileController@finalFiles']);
Route::post('/bucketfile/update',  ['middleware' => 'auth', 'uses' => 'BucketfileController@update']);
Route::get('/bucketfile/restore_template_file',  ['middleware' => 'auth', 'uses' => 'BucketfileController@restoreTemplateFile']);

Route::get('/launchdownloaderintestmode',  'UserController@launchDownloaderInTestMode');

Route::get('get_products_name', 'UserController@getProductName');

Route::get('show_available_products', ['middleware' => 'auth', 'as' => 'show_available_products', 'uses' => 'ProductController@index']);

Route::get('product_action', 'ProductController@action');

Route::get('refresh_products_list', ['middleware' => 'auth', 'uses' => 'ProductController@refreshProductsList']);
//Route::get('refresh_products_list2', ['middleware' => 'auth', 'uses' => 'ProductController@refreshProductsList2']);

//return view('home', array('test'=> 'test1', 'privateMessages' => $results));



