<?php namespace App\Http\Controllers;

use DB;
use Auth;
use Illuminate\Http\Request;
use App\library\myFunctions;


class ProductController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Product Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->beforeFilter(function() {
			if (Auth::user()->role != 'admin')
				return view('only_for_admin');
		});
	}


	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$available_sorts = ['id', 'pname', 'uicode', 'txt_file_url', 'version'];

		$sort = $request->input('sort','pname');
		$ok = array_search($sort, $available_sorts);
		if ($ok == 0) $sort = 'pname';

		$sql_query  = "SELECT id, pname, uicode, begin_install, txt_file_url, version, ";
		$sql_query .= 'FIND_IN_SET ("zip_not_exist", statuses) as zip_not_exist ';
		$sql_query .= "FROM products ";
		$sql_query .= "ORDER BY ".$sort;

		$results = DB::select($sql_query);
		$pass_variables = array(
			'products' => $results,
			'url' => url(),
			);
		return view('products', $pass_variables );
	}


	public function action(Request $request)
	{
		$action = $request->input('action','nothing');
		$product_id = $request->input('product_id','0');

		if (!is_numeric($product_id)) {
			$msg = "get (product_id) not defined ";
			$return = array ('status' => '-1', 'msg' => $msg);
			print json_encode($return);
			exit;
		}

		switch ( $action )  {
			case "show_txt":
				$sql_query  = "SELECT txt_file_url ";
				$sql_query .= "FROM products ";
				$sql_query .= "WHERE id =  ".$product_id;
				$results = DB::select($sql_query);
				$content = file_get_contents( $results[0]->txt_file_url );
				$return = array ('status' => '1', 'msg' => "<pre><b>" . $results[0]->txt_file_url . '</b><br>'.  $content . "</pre>");
				print json_encode($return);
			break;

			case "remove_from_here": 
				$sql_query  = "SELECT txt_file_url ";
				$sql_query .= "FROM products ";
				$sql_query .= "WHERE id =  ".$product_id;
				$results = DB::select($sql_query);

				$sql_query  = "DELETE FROM products WHERE id = ".$product_id;

				DB::delete($sql_query);

				$msg = "Product removed from this list. Please consider to remove this file too: ";
				$msg .= "<p><b>".$results[0]->txt_file_url."</b></p>";
				$msg .= "Otherwise [refresh product list] button restores this product.";
				$return = array ('status' => '1', 'msg' => "<pre>" .  $msg . "</pre>");
				print json_encode($return);
			break;

			default: 
				$msg = "get (action) not defined ";
				$return = array ('status' => '-1', 'msg' => $msg);
				print json_encode($return);
				exit;		
			break;

		}

	}


	// this function operates with json file. TO DO: refreshProductsList() from route and from this file

	public function refreshProductsList()
	{

		$all_product_list = config('app.url_with_all_product_list');

		$ctx = stream_context_create(array('http' => array( 'timeout' => 4 )));
		try
		{
			$html_content = file_get_contents($all_product_list, 0, $ctx); 
		}
		catch(\Exception $ex)
		{
			$msg = "I can not retrive json file with products definition from: ".$all_product_list ;
			$return = array ('status' => '-1', 'msg' => $msg);
			myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
			print json_encode($return);
			exit;
		}


		$bigArrayProducts = json_decode($html_content, true);
		if ($bigArrayProducts === false ) {
			$msg = "I can not decode json file with products definition from: ".$all_product_list ;
			$return = array ('status' => '-1', 'msg' => $msg);
			myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
			print json_encode($return);
			exit;
		}

		if (count ($bigArrayProducts) == 0) {
			$msg = "I found zero products. Nothing found in products configuration file. ".$all_product_list ;
			$return = array ('status' => '-1', 'msg' => $msg);
			myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
			print json_encode($return);
			exit;
		}


		$arrayWithUicodeIndex = array();
		$tmp1_array = $bigArrayProducts; // it is needed to comparasion

		foreach ($bigArrayProducts as $key => $item) {
 
			$arrayWithUicodeIndex[$item['uicode']] = $item;
			$arrayCounters[$item['uicode']] = 0;
		}


		$fileArray = array();
		foreach ($bigArrayProducts as $key => $item) {
			$tmp = $item['uicode'];
			$fileArray[] = $tmp;
			$arrayCounters[$tmp]++;
			if ($arrayCounters[$tmp] > 1) {
				$msg = "Only one unique code for one product can be set. I found duplicated code: ".$tmp. 'in your product list '.config('app.url_with_all_product_list');
				$return = array ('status' => '-1', 'msg' => $msg);
				myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
				print json_encode($return);
				exit;
			}
		}

		$results = DB::table('products')->select('uicode')->get();
		$databaseArray = array();
		foreach ($results as $item) {
			$databaseArray[] = $item->uicode;
		}

		$diff = array_diff($databaseArray , $fileArray);
		if (count($diff)>0) {
			$msg = 'Now in local product database are more products that you have in '.config('app.url_with_all_product_list').' These product/s are no more exist in new update: <b>'.implode(", ", $diff).'</b> If you agree with that please delete those products manually first.';
			$return = array ('status' => '-1', 'msg' => $msg);
			myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
			print json_encode($return);
			exit;
		}

		$diff = array_diff($fileArray, $databaseArray);

		if (count($diff) >= 0) {
			if (count($diff) > 0) $msg = 'New product/s code/s added. uicode/s= '.implode(", ", $diff).'';
			if (count($diff) == 0) {
				$msg = 'Product table refreshed. Now number of products is the same as was before.';
			}
			$return = array ('status' => '1', 'msg' => $msg);

			foreach ($bigArrayProducts as $key => $item) {
				$product_decoded =  $item;
				$product_decoded['txt_file_url'] = env('URL_WITH_ALL_PRODUCT_LIST').$item['txt_file_url'];
				try
				{
					DB::table('products')->where('uicode', '=', $product_decoded['uicode'])->delete();
					$id = DB::table('products')->insertGetId($product_decoded);
				}
				catch(\Exception $ex)
				{
					$msg  = "Error. Product json file has more fields that predicted. Available configuration fields: ";
					$msg .= "id, pname, pcode, uicode, ";
					$msg .= "version, awlist1, grlist1, ";
					$msg .= "awlist2, grlist2, list3title, ";
					$msg .= "list1info, list2title, list2info, statuses, begin_install. ";
					$msg .= "I bet you have missed something or have something else in you json product txt configuration file.";
					$msg .= "Fix this error now - otherwise system may not work properly. Please see for global logs.";
					myFunctions::addGlobalLog(Auth::user()->id, -1, $msg.' '.$ex);
					$return = array ('status' => '-1', 'msg' => $msg);
					print json_encode($return);
					exit;
				}
			}
			myFunctions::addGlobalLog(Auth::user()->id, 1, $msg);
			myFunctions::addGlobalLog(Auth::user()->id, 1, 'products refreshed/updated with success. '.env('URL_WITH_ALL_PRODUCT_LIST'));
			print json_encode($return);
			exit;
		}
	
	}




	// public function refreshProductsList()
	// {

	// 	// nowa wersja http://Aaron123:Danker345@aaron2test.enbe.pl/delideli/

		
 //        $all_product_list = config('app.url_with_all_product_list');

 //        $html_content = file_get_contents($all_product_list);

 //        $array = (explode("\n", $html_content));
 //        $list_extracted = array();
 //        foreach ($array as $item) {
 //            if (substr($item, 0, 13) == '<li><a href="') {
 //                $pattern = '/^<li><a href="(.*\.txt)">\s(.*)<\/a>.*/';
 //                $result = preg_match($pattern, $item, $matches);
 //                if (($result) && ($matches[1] != "robots.txt")) $list_extracted[] = $matches[1];
 //            }
 //        }

 //        // now open every of these files..
 //        if (count($list_extracted)>0) {
 //            $json_files = array();
 //            foreach ($list_extracted as $item) {
 //                $url = config('app.url_with_all_product_list').$item;
	// 	        $json_files[$url] = file_get_contents($url);
 //            }
 //        }
 //        else {
	// 		$msg = "can't find any txt files with description on ". config('constans.URL_WITH_ALL_PRODUCT_LIST');
	// 		$return = array ('status' => '-1', 'msg' => $msg);
	// 		print json_encode($return);
	// 		exit;
 //        }

 //        // check every json format 
 //        foreach ($json_files as $key => $item) {
 //        	if (!json_decode($item)) {
	// 			$msg  = "Problem with json decode file: ".$key;
	// 			$msg .= "Are you sure it has proper json format product configuration ?";
	// 			$msg .= "please use for test: http://json.parser.online.fr/";
	// 			$return = array ('status' => '-1', 'msg' => $msg);
	// 			print json_encode($return);
	// 			exit;
 //            }
 //        }


	// 	// remove product database and put new product 
	// 	foreach ($json_files as $key => $item) {
	// 		$product_decoded = json_decode($item, true);
	// 		$product_decoded['txt_file_url'] = $key;
	// 			try
	// 		{
	// 			DB::table('products')->where('uicode', '=', $product_decoded['uicode'])->delete();
	// 			$id = DB::table('products')->insertGetId($product_decoded);
	// 		}
	// 		catch(\Exception $ex)
	// 		{
	// 			$msg  = "Available configuration fields: ";
	// 			$msg .= "id, pname, pcode, uicode, ";
	// 			$msg .= "version, awlist1, grlist1, ";
	// 			$msg .= "awlist2, grlist2, list3title, ";
	// 			$msg .= "list1info, list2title, list2info ";
	// 			$msg .= "I bet you have missed something or have something else in you json product txt configuration file.";
	// 			$msg .=  $key;
	// 			$msg .= " You have to fix this error now - otherwise system will not work.";
	// 			$return = array ('status' => '-1', 'msg' => $msg);
	// 			print json_encode($return);
	// 			exit;
	// 		}
	// 	}

	// 	$msg  = count($json_files). " products refreshed ";
	// 	$return = array ('status' => '1', 'msg' => $msg);
	// 	print json_encode($return);
	// 	exit;
	// }

}
