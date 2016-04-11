<?php namespace App\Http\Controllers;

use Session;
use App\Http\Requests;
use App\library\myFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use DB;
use Auth;

use Illuminate\Http\Request;

class EmailTemplatesController extends Controller {


	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// $this->beforeFilter(function() {
		// 	if (Auth::user()->role != 'admin')
		// 		return view('only_for_admin');
		// });
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $url =  url()."/email_templates/form_submitted";
        return Redirect::to($url);
	}



	/**
	 * Update update_submit_form_confirmation
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function update_submit_form_confirmation (Request $request)
	{
		if (Auth::user()->role != 'admin') return view('only_for_admin');

		$new_email_template	= $request->input('email_template', '');	
		if (strlen($new_email_template)>50) {
			$tmp_array = array('var_string' => $new_email_template);
			$tmp = DB::table('settings')->where('key_string', 'submit_form_notification_template')->update($tmp_array);
			Session::flash('message', array('btn-primary', 'UPDATED'));
		}
		else {
			Session::flash('message', array('btn-danger',' NOT UPDATED'));
		}
		$url = url()."/email_templates/form_submitted";
		return Redirect::to($url);
	}

	public function update_instalation_confirmation (Request $request)
	{
		if (Auth::user()->role != 'admin') return view('only_for_admin');

		$new_email_template	= $request->input('email_template', '');	
		if (strlen($new_email_template)>50) {
			$tmp_array = array('var_string' => $new_email_template);
			$tmp = DB::table('settings')->where('key_string', 'product_installed_notification_template')->update($tmp_array);
			Session::flash('message', array('btn-primary', 'UPDATED'));
		}
		else {
			Session::flash('message', array('btn-danger',' NOT UPDATED'));
		}
		$url =  url()."/email_templates/product_installed";
		return Redirect::to($url);
	}

	public function formSubmitted()
	{
		//if (Auth::user()->role != 'admin') return view('only_for_admin');

		$sql_query  = "SELECT var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'submit_form_notification_template' ";
		$results = DB::select($sql_query);
		// if $results is empty throw exception

		$email_template = $results[0]->var_string;
 
		$example_product_names = array( 'Lorem ipsum', 'Dolor sit amet', 'Consectetur adipiscing elit', 'Mauris suscipit aliquam nulla');

		$example_user_data1 = array(
			'first_name' => 'Lech',
			'last_name' => 'Walesa',
			'contact_email' => 'lech_walesa@gmail.com',
			'ftp_host' => '127.0.0.1',
			'ftp_username' => 'ftp_username',
			'ftp_password' => 'ftp_password',
			'ftp_dir' => 'ftp_dir',
			'website' => 'https://website',
		);

		$example_user_data2 = array(
			'first_name' => 'Lech',
			'last_name' => 'Walesa',
			'contact_email' => 'lech_walesa@gmail.com',
			'ftp_host' => '127.0.0.1',
			'ftp_username' => 'ftp_username',
			'ftp_password' => 'ftp_password',
			'ftp_dir' => 'ftp_dir',
			'website' => 'https://website',
			'paypal_api_username' => 'test pp_username',
			'paypal_api_password' => 'test ppp_password',
			'paypal_api_signature' => '1q2w3e4r5t6y7u8i9o0',
		);

		$example_template1 = $this->prepareExampleTemplateFormSubmitted($example_user_data2,  array_slice($example_product_names, 0, 1) ); // one product
		$example_template2 = $this->prepareExampleTemplateFormSubmitted($example_user_data1, $example_product_names );  // all products

		$variables = array (
			'plural' => 's',
			'example_product_names' => $example_product_names,
			'website' => 'http://example_website.com',
			'email_template' => $email_template,
			'example_template1' => $example_template1,
			'example_template2' => $example_template2,
			'auth_user' => Auth::user()->role,
			'flash_message' => Session::get('message', array('btn-primary', '')), 
		);
		return view('emailtemplates_submit_form_confirmation', $variables);
	}



	public function productInstalled()
	{
		
		$sql_query  = "SELECT var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'product_installed_notification_template' ";
		$results = DB::select($sql_query);
 		// if $results is empty throw exception

		$email_template = $results[0]->var_string;

		$example_product_names = array( 'Lorem ipsum', 'Dolor sit amet', 'Consectetur adipiscing elit', 'Mauris suscipit aliquam nulla');

		$user['admin_url'] = "http://exampleurl/admin";
		$user['first_name'] = "Lech";
		$user['last_name'] = "Walesa";

		$example_template1 = $this->prepareExampleTemplateProductInstalled($user, array_slice($example_product_names, 0, 1) ); // one product
		$example_template2 = $this->prepareExampleTemplateProductInstalled($user, $example_product_names );  // all products

		$variables = array (
			'email_template' => $email_template,
			'example_template1' => $example_template1,
			'example_template2' => $example_template2,
			'auth_user' => Auth::user()->role,
			'flash_message' => Session::get('message', array('btn-primary', '')), 
		);
		return view('emailtemplates_instalation_confirmation', $variables);
	}



	private function prepareExampleTemplateFormSubmitted ($user_data, $example_product_names) {
 
		$products_list = myFunctions::makeProductsList($example_product_names);
		$simpleTableWithProvidedData = myFunctions::generateEmailTableWithProvidedData($user_data);
		$user_data = $user_data + array('products_list' => $products_list);
		$user_data = $user_data + array('simpleTableWithProvidedData' => $simpleTableWithProvidedData);
		if (count($example_product_names) > 1) {
			$user_data['letter_s'] = 's';
			$user_data['is_are'] = 'are';
		} else {
			$user_data['letter_s'] = '';
			$user_data['is_are'] = 'is';
		}
		$all_parsed = myFunctions::parseEmailTemplateFormSubmitted($user_data);
		return $all_parsed;
	}



	public function prepareExampleTemplateProductInstalled ($user, $example_product_names) {

		$user_admin_url = $user['admin_url'];

		$products_list = myFunctions::makeProductsList($example_product_names);

		$data = array('products_list' => $products_list);
		$data = $data + array('user_admin_url' => $user['admin_url']);
		$data = $data + array('first_name' => $user['first_name']);
		$data = $data + array('last_name' => $user['last_name']);

		if (count($example_product_names) > 1) {
			$user_data['letter_s'] = 's';
			$user_data['is_are'] = 'are';
		} else {
			$user_data['letter_s'] = '';
			$user_data['is_are'] = 'is';
		}

		$all_parsed = myFunctions::parseEmailTemplateProductInstalled($data);
		return $all_parsed;
	}




}
