<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Input;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ManualModeController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($id = false)
	{
		if ($id > 0) {
			$sql_query  = "SELECT id, pname, user_firstname, user_lastname, website,";
			$sql_query .= 'FIND_IN_SET ("ftp_path_ok", statuses_features) as ftp_path_ok, ';
			$sql_query .= "CONCAT(TRIM(TRAILING '.txt' FROM txt_file_url), '.zip') as zip_file, ";
			$sql_query .= "ftp_host, ftp_username, ftp_password, ftp_dir, user_email, date_ordered  ";
			$sql_query .= "FROM transactions ";
			$sql_query .= "WHERE id = ".$id;

			$resultsData = DB::select($sql_query);

			$sql_query2  = "SELECT id, pname, ";
			$sql_query2 .= "CONCAT(TRIM(TRAILING '.txt' FROM txt_file_url), '.zip') as zip_file, ";
			$sql_query2 .= "status, user_email ";
			$sql_query2 .= "FROM transactions ";
			$sql_query2 .= "WHERE user_email='".$resultsData[0]->user_email."' ";
			$sql_query2 .= "AND date_ordered='".$resultsData[0]->date_ordered."' ";

			$transactionsBelong = DB::select($sql_query2);


			foreach ($transactionsBelong as &$item) {
				if ($item->status == 'installed') $item->class = 'success';
				if ($item->status == 'waiting') $item->class = 'bg-info';
				if ($item->status == 'problem') $item->class = 'btn-danger';
				if ($item->status == 'cancelled') $item->class = 'btn-primary';
			}

			$result = array(
				'admin_path' => env('URL_WITH_ALL_PRODUCT_LIST'). 'SSM-admin.zip',
				'id' => $resultsData[0]->id,
				'pname' => $resultsData[0]->pname,
				'website' => $resultsData[0]->website,
				'ftp_path_ok' => $resultsData[0]->ftp_path_ok,
				'user_firstname' => $resultsData[0]->user_firstname,
				'user_lastname' => $resultsData[0]->user_lastname,
				'zip_file' => $resultsData[0]->zip_file,
				'ftpserver' => $resultsData[0]->ftp_host,
				'username' => $resultsData[0]->ftp_username,
				'password' => $resultsData[0]->ftp_password,
				'directory' => $resultsData[0]->ftp_dir,
				'transactionsBelong' => $transactionsBelong 
				);
	        return view('checkfiles_by_net2ftp', $result);
    	}
    	else 
    		return new RedirectResponse(url('/home'));
	}



	/**
	 * put File By FTP
	 *
	 * @return Response
	 */

	private function putFileByFTP($id, $action='upload_product') { // $type='admin' or $type='product'


		$results = $this->getOneTransaction($id);

		$return = array();


		// get the login and password for FTP server

		$txt_file_url = $results[0]->txt_file_url.'.zip';
		$server_ip = $results[0]->ftp_host;
		$ftp_username = $results[0]->ftp_username;
		$ftp_password = $results[0]->ftp_password;
		$ftp_dir = $results[0]->ftp_dir;
		if ($ftp_dir == '') $ftp_dir = '/';
		$website = $results[0]->website;


        // set up basic FTP onnection
        
        $conn_id = @ftp_connect($server_ip);
        $timestamp = date("Y-m-d H:i:s", round(microtime (true)));

		if ($conn_id) {
			$msg = 'connect in to FTP - OK.';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
		}
		else {
			$msg = 'There is a problem with connect to FTP server. <br>';
			$msg = 'Please check FTP details and/or update FTP details in this transaction record.<br> ';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			exit;
		}


        // login with username and password
        
        $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
        {
        	$timestamp = date("Y-m-d H:i:s", round(microtime (true)));
            if ($login_result) {
				$msg = 'log in to FTP - OK.';
				array_push( $return, $this->getLoginResultArray(1, $msg, $id));
            }
            else {
                $msg =  'There is a problem with login or password in FTP server.<br> ';
                $msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br> ';
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
				exit;
            }
        }

        // turn passive mode on
        ftp_pasv($conn_id, true);

        // change FTP directory

        if (@ftp_chdir ($conn_id,  $ftp_dir )) {
			$msg = 'FTP directory changed successfully';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));            
        } else {
            $msg  = 'There is a problem with changing directory on FTP.<br>';
            $msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br>';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			exit;
            return;
        }

        // IMPORTANT TO DO!
		// TODO: please check is prodacon local storage first

        switch ($action) {
        	case 'upload_product' :
        		$pathResult = pathinfo($txt_file_url);
		        $path_local_file = "../products_local_storage/".$pathResult['filename'].'.'.$pathResult['extension'];
        	break;
        	case 'upload_admin' :
        		$txt_file_url = 'http://www.supersalesmachine.com/admin/files/SSM-admin.zip';
        		$pathResult = pathinfo($txt_file_url);
				$path_local_file = "../products_local_storage/SSM-admin.zip";
        	break;
        	case 'upload_downloader' :
        		$txt_file_url = './downloader.php';
        		$pathResult = pathinfo('./downloader.php');
				$path_local_file = "../products_local_storage/downloader.php";
        	break;
        	default : 
	            $msg  = 'private function ManualModeController->putFileByFTP(...';
	            $msg .= 'action not defined';
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
				exit;
        	break;
        }


		$file_name = $pathResult['filename'].'.'.$pathResult['extension'];

		// this is without sense everytime it makes local copy of products !!!
		// should check first is local copy exist or not.
		
		$tmp = file_put_contents($path_local_file, file_get_contents($txt_file_url));
		if ($tmp == false) {
			$msg =  'There is a problem with making local copy of product.';
			$msg .= $txt_file_url;
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			ftp_close($conn_id);
			exit;
		} else {
			$msg =  'I made local copy of '.$txt_file_url;
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
		}

		// put $path_local_file (ZIP) to customer FTP server

   		if (ftp_put($conn_id, $file_name, $path_local_file, FTP_BINARY)) {
   			$msg = $file_name.' successfully uploaded';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));     
		} else {
			$msg  = 'There is a problem with uploading '. $file_name.' to FTP server.';
			$msg .= ' txt_file_url = '.$txt_file_url;
			$msg .= ' server_ip = '.$server_ip;
			$msg .= ' ftp_username = '.$ftp_username;
			$msg .= ' ftp_password = '.$ftp_password;
			$msg .= ' ftp_dir = '.$ftp_dir;
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			ftp_close($conn_id);
			return;
		}
		ftp_close($conn_id);
		return $return;
	}


	
	private function removeFileByFTP($id) {
		$results = $this->getOneTransaction($id);

		$return = array();


		// get the login and password for FTP server

		$txt_file_url = $results[0]->txt_file_url.'.zip';
		$server_ip = $results[0]->ftp_host;
		$ftp_username = $results[0]->ftp_username;
		$ftp_password = $results[0]->ftp_password;
		$ftp_dir = $results[0]->ftp_dir;
		if ($ftp_dir == '') $ftp_dir = '/';
		$website = $results[0]->website;


        // set up basic FTP onnection
        
        $conn_id = @ftp_connect($server_ip);
        $timestamp = date("Y-m-d H:i:s", round(microtime (true)));

        if ($conn_id) {
			$msg = 'connect in to FTP - OK.';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
        }
        else {
			$msg = 'There is a problem with connect to FTP server. <br>';
			$msg = 'Please check FTP details and/or update FTP details in this transaction record.<br> ';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			exit;
            return;
        }


        // login with username and password
        
        $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
        {
        	$timestamp = date("Y-m-d H:i:s", round(microtime (true)));
            if ($login_result) {
				$msg = 'log in to FTP - OK.';
				array_push( $return, $this->getLoginResultArray(1, $msg, $id));
            }
            else {
                $msg =  'There is a problem with login or password in FTP server.<br> ';
                $msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br> ';
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
				ftp_close($conn_id);
                exit;
            }
        }

        // turn passive mode on
        ftp_pasv($conn_id, true);

        // change FTP directory and delete

        if (@ftp_chdir ($conn_id,  $ftp_dir )) {
			$msg = 'FTP directory changed successfully';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
        } else {
            $msg  = 'There is a problem with changing directory on FTP.<br>';
            $msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br>';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			ftp_close($conn_id);
            exit;
        }

        if (@ftp_delete($conn_id, 'downloader.php')) {
			$msg = ' "downloader.php" successfully deleted';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
        } else {
            $msg  = 'There was a problem with removing "downloader.php" from remote server. Is "downloader.php" there? ';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			ftp_close($conn_id);
            exit;
        }
		
		ftp_close($conn_id);
		print json_encode($return);
		exit;

	}

	/**
	 * Manual Do Action Ajax Request.
	 *
	 * @return Response
	 */
	public function ManualDoFTPactionAjaxRequest($id) {
		
		ini_set('max_execution_time', 300);

		$action = Input::get('action', '');
 		$return = array();

		switch ($action) {
			case 'upload_product' :
				$return = $this->putFileByFTP($id, 'upload_product');
				break;
			case 'upload_admin' :
				$return = $this->putFileByFTP($id, 'upload_admin');
				break;
			case 'upload_downloader' :
				$return = $this->putFileByFTP($id, 'upload_downloader');
				break;
			case 'remove_downloader' :
				$return = $this->removeFileByFTP($id, 'remove_downloader');
				break;
			case 'send_email' :
				$msg = 'send_email action not yet ready!';
				$msg .= 'send_email action not yet ready!';
				$msg .= 'send_email action not yet ready!';
				$msg .= 'send_email action not yet ready!';

				array_push( $return, $this->getLoginResultArray(1, $msg, $id)); 
				break;

			default:
				$msg = 'Unknown action = '.$action;
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id)); 
				break;
		}


		print json_encode($return);
		exit;
	}		


	/**
	 * get One Transaction.
	 * id
	 * @return Response
	 */
	private function getOneTransaction($id)
	{
		// from this moment this query is very similar to transaction. Move it to something else.
		$sql_query  = "SELECT id, user_firstname, user_lastname, pname, user_email, ";
		$sql_query .= "paypal_email, customer_support_email, clickbank_id, jvzoo_id, ";
		$sql_query .= "SUBSTRING_INDEX(txt_file_url, '.txt', 1) as txt_file_url, ";
		$sql_query .= "ftp_host, ftp_username, ftp_password, ftp_dir, website, statuses_features, version ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE id = ".$id;

		$results = DB::select($sql_query);
		return $results;
	}


	/**
	 * Manual Downloader Ajax Request.
	 *
	 * @return Response
	 */
	public function ManualDownloaderAjaxRequest($id) {

		set_time_limit(120);
		

		$action = Input::get('action', '');

 		$return = array();

		if ($action == '') {
			$msg = 'Ajax ERROR. Action for ManualModeDownloaderAjaxRequest not spesified.';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id) );
			print json_encode($return);
			exit;
		}
		
		$results = $this->getOneTransaction($id);

		if(!$results) {
			$msg = 'Problem with getting FTP credentials from database.';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
            print json_encode($return);
            return;
		}

		$txt_file_url = $results[0]->txt_file_url.'.zip';
		$server_ip = $results[0]->ftp_host;
		$ftp_username = $results[0]->ftp_username;
		$ftp_password = $results[0]->ftp_password;
		$ftp_dir = $results[0]->ftp_dir;
		if ($ftp_dir == '') $ftp_dir = '/';
		$website = $results[0]->website;

		$add_url  = '&first_name='.base64_encode($results[0]->user_firstname);
		$add_url .= '&last_name='.base64_encode($results[0]->user_lastname);
		$add_url .= '&contact_email='.base64_encode($results[0]->user_email);
		$add_url .= '&customer_support_email='.base64_encode($results[0]->customer_support_email);
		$add_url .= '&paypal_email='.base64_encode($results[0]->paypal_email);
		$add_url .= '&clickbank_id='.base64_encode($results[0]->clickbank_id);
		$add_url .= '&jvzoo_id='.base64_encode($results[0]->jvzoo_id);

		switch ($action) {

			case 'unzip_product':
				$downloader_action = 'justUnpackProduct';
				break;
			case 'unzip_admin':
				$downloader_action = 'justUnpackAdmin';
				break;
			case 'change_user_file':
				$downloader_action = 'justConfigure';
				break;
			default:
				$msg = 'public function ManualDownloaderAjaxRequest action not defined.';
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
				exit;
				break;
		}

		$url = $website.'/downloader.php?action='.$downloader_action.'&product_url='.base64_encode($txt_file_url).$add_url;

		$msg = 'Try to decode json from this file '.$url;
		array_push( $return, $this->getLoginResultArray(1, $msg, $id)); // zahardkodowane ze zawsz ok? 

		$file_headers = @get_headers($url);
		if(strpos($file_headers[0],'200')===false) {
			$msg = 'I can not to reach downloader file. Is downloader php exists on destination server?';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id)); // zahardkodowane ze zawsz ok? 
			print json_encode($return);
			exit;
		}

		$a = file_get_contents ($url);
		if ($a) {
			$jsonResultsFromDownloader = json_decode($a, true);
			if ($jsonResultsFromDownloader === false ) {
				$msg = 'I received data from downloader.php but this is not json format. Part of data = '.$a;
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
				exit;
			}

			// check every log from downloader each by each
			foreach (
			$jsonResultsFromDownloader as $item) {
				$msg = $item['msg'];
				$status = $item['status'];
				$timestamp = $item['timestamp'];
				array_push( $return, $this->getLoginResultArray($status, $msg, $id, $timestamp));
				if ($status == -1) {
					print json_encode($return);
					exit;
				}
			}

			$tmp_array = json_decode($a, true);

			if (json_last_error() == JSON_ERROR_NONE) {
				if (count($tmp_array)>0) {
					foreach ($tmp_array as $item) {
						array_push( $return, $this->getLoginResultArray($item['status'], $item['msg'], $id,  $item['timestamp']));
					}
				}
			} else {
				array_push( $return, $this->getLoginResultArray(-1, 'problem with decoding downloader.php JSON data', $id));
				print json_encode($return);
				exit;
			}

			$return = array_merge ($return, $tmp_array);
		}
		else {
			$msg = 'do action: '.$website.'/downloader.php?action=justConfigure&product_url='.$txt_file_url.' - FAILED';
			print json_encode($return);
			exit;
		}

		array_push( $return, $this->getLoginResultArray(1, 'Manual mode just did justConfigure changed admin/user.php configuration file.', $id));
		print json_encode($return);
	}

	/**
	 * This function prepares array for log TODO :the same function is in TransactionController.
	 * TODO: move it into one place
	 */
	private function getLoginResultArray($status, $msg, $id, $timestamp = false) {

		if ($timestamp == false) $timestamp = date("Y-m-d H:i:s", round(microtime (true)));


		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}		

		$tmp_array = array(
			'user_id' => Auth::user()->id,
			'user_name' => Auth::user()->name,
			'ip' =>  $ip,
			'timestamp' => $timestamp,
			'transaction_id' => $id,
			'status' => $status,
			'msg' => $msg
		);

		try
		{
			$id = DB::table('log_installations')->insert($tmp_array);
		}
		catch(\Exception $ex)
		{
			dd("problem with inserting into log_installations table");
		}

 		$return = array('timestamp'=>$timestamp,
 						'status' => $status,
 						'msg' => $msg
 						);
 		return $return;
	}	

}
