<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\EditTransactionFormRequest;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EmailTemplatesController;
use App\library\myFunctions;
use Mail;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TransactionsController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		$do_action = $request->input('action', false);
		$id = $request->input('id');
		$var = $request->input('var');
		$email = $request->input('email');

		switch ($do_action) {
			// ==================================================================================

			case 'getDataForExpressInstall': 
				// $result = myFunctions::get_data_for_express_install($id);
				// // TODO: catch the status
				// $msg = "Status changed";
				// $return = array ('status' => '1', 'msg' => $msg);
				// ?????????????????????????
				// print json_encode($return);
				print '{"name_surname":"bedzie bedzie","product_list":[{"id": "113", "name": "erwer11"},{"id": "114", "name": "erwer22"},{"id": "115", "name": "erwer33"}]}';
				// ?????????????????????????
			break;


			case 'change_transaction_status': 
				$result = myFunctions::change_transaction_status($id, $var);

				$msg = "Status changed";
				$return = array ('status' => '1', 'msg' => $msg);
				print json_encode($return);
				break;

			// ==================================================================================
			case 'remove_from_here': 
				if (Auth::user()->role == 'admin') {
					$result = $this->remove_transaction($id);
					if ($result == 1) {
						$msg = "Transaction record removed from database";
						$return = array ('status' => '1', 'msg' => $msg);
					}
					else {
						$msg = "Problem with removing transaction record";
						$return = array ('status' => '-1', 'msg' => $msg);
					}
					print json_encode($return);
				}
				break;

			// ==================================================================================
			case "only_send_email":
				$this->onlySendEmail($id);
				break;

			// ==================================================================================

			case "only_show_email_preview":

				$result = myFunctions::checkEveryTransactionForUserInstalled($id);

				if ($result->count_installed != $result->count_all) {
					$msg = 'Every products for this date ordered <b>('.$result->date_ordered.')</b> and</br>user_email = <b>('.$result->user_email.')</b> has to be installed first !' ;
					$return = array ('status' => '-2', 'msg' => $msg);
					print json_encode($return);
					return;
				}

				$sql_query  = "SELECT pname, website, user_firstname, user_lastname ";
				$sql_query .= "FROM transactions ";
				$sql_query .= "WHERE date_ordered = '".$result->date_ordered."'";
				$sql_query .= "AND status = 'installed' ";
				$results = DB::select($sql_query);

				$product_names = array();
				foreach ($results as $item) {
					$product_names[] = $item->pname;
				}

				$user['admin_url'] = rtrim($results[0]->website,'/').'/admin';
				$user['first_name'] = $results[0]->user_firstname;
				$user['last_name'] =$results[0]->user_lastname;
				
				$a = new EmailTemplatesController();
				$example_template1 = $a->prepareExampleTemplateProductInstalled($user, $product_names );
				$return = array ('status' => '1', 'msg' => $example_template1 );
				print json_encode($return);

				break;

			// ==================================================================================
			case "do_big_install":
				$this->doBigInstall($id, $email);
				break;
			// ==================================================================================

			case "uninstall":
			// TODO: add log every thing into DB

				break;

			// ==================================================================================
			case 'show_transaction_details': 
				//if (Auth::user()->role == 'admin') {
					$sql_query = "SELECT id, user_firstname, user_lastname, user_email, pname, pcode, uicode, ";
					$sql_query .= "version, txt_file_url, date_ordered, date_updated, date_installed, ftp_host, ftp_username, ";
					$sql_query .= "paypal_api_username, paypal_api_password, paypal_api_signature,  aweber_username, aweber_password, "; 
					$sql_query .= "ftp_password, ftp_dir, website, transaction_id, awlist1, awlist2, grlist1, ";
					$sql_query .= "grlist2, list1title, list1info, list2title, list2info, comments, status ";
					$sql_query .= "FROM " ;
					$sql_query .= "transactions ";
					$sql_query .= "WHERE " ;
					$sql_query .= "id =".$id;

					$results = DB::select($sql_query);
					if($results) {
						$return = array ('status' => '1', 'msg' => "<pre>".print_r($results[0],true)."</pre>");
					} else {
						$return = array ('status' => '-1', 'msg' => "problem with getting transaction details.");
					}
					print json_encode($return);
				//}
				break;
			// ==================================================================================
			default: 
				$return = array ('status' => '-1', 'msg' => "action and id not defined");
				print json_encode($return);
				break;
		}
	}



	private function remove_transaction( $id ) {
		$old_data = myFunctions::getOneTransactionRecordFullData($id);
		if (count($old_data) == 0) return -1;
		$msg = json_encode(array('RECORD'=>'REMOVED') + (array)$old_data[0]);
		DB::table('transactions')->where('id', '=', $id)->delete();
		DB::table('log_installations')->where('transaction_id', '=', $id)->delete();
		$id_global = -1;
		try
		{
			$id_global = MyFunctions::addGlobalLog($id, 1, $msg);
			$id_global = 1;
		}
		catch(\Exception $ex)
		{
			dd("problem with inserting into log_installations table");
			exit;
		}
		return $id_global;
	}



	/**
	 * This function prepares array for log TODO :the same function is in ManualModeController.
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
			exit;
		}

 		$return = array('timestamp'=>$timestamp,
 						'status' => $status,
 						'msg' => $msg
 						);
 		return $return;
	}


	/**
	 * only Send Email  //
	 */
	private function onlySendEmail($id)
	{

		$one_record = myFunctions::checkEveryTransactionForUserInstalled($id);

		if ($one_record->count_installed != $one_record->count_all) {
			$msg = 'Every products for this date ordered <b>('.$one_record->date_ordered.')</b> and</br>user_email = <b>('.$one_record->user_email.')</b> has to be installed first !' ;
			$return = array ('status' => '-2', 'msg' => $msg);
			print json_encode($return);
			return;
		}

		$id_list = myFunctions::getAllIdForDateOrderedUserEmail($one_record->date_ordered, $one_record->user_email );

		$resultsData = myFunctions::getOneTransactionRecrod($id);
		
		$return = array();
		if(!$resultsData) {
			$msg = 'Problem with getting transaction from database (only Send Email part 1).';
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
            print json_encode($return);
            return;
		}

		$result = myFunctions::checkEveryTransactionForUserInstalled($id);

		if ($result->count_installed != $result->count_all) {
			$msg = 'Every products for this date ordered <b>('.$result->date_ordered.')</b> and</br>user_email = <b>('.$result->user_email.')</b> has to be installed first !' ;
			$return = array ('status' => '-2', 'msg' => $msg);
			print json_encode($return);
			return;
		}

		$pnameWebstites = myFunctions::getAllPnameWebstiteForDateOrdered($result->date_ordered);
		
		$product_names = array();
		foreach ($pnameWebstites as $item) {
			$product_names[] = $item->pname;
		}

		//	$product_names = array( 'Lorem ipsum', 'Dolor sit amet', 'Consectetur adipiscing elit', 'Mauris suscipit aliquam nulla');
		$user['admin_url'] = rtrim($pnameWebstites[0]->website,'/').'/admin'; //"http://exampleurl/admin";
		$user['first_name'] = $pnameWebstites[0]->user_firstname;
		$user['last_name'] = $pnameWebstites[0]->user_lastname;

		$a = new EmailTemplatesController();
		$example_template1 = $a->prepareExampleTemplateProductInstalled($user, $product_names );

		// send email
		$mail_arr = array(
			'parsed_email_template' => $example_template1,
			'contact_email' => $resultsData[0]->user_email,
			'first_name' => $resultsData[0]->user_firstname,
			'last_name' => $resultsData[0]->user_lastname,
		);

		try
		{
			$tmp = Mail::send('emails.instalation_confirmation_db', $mail_arr, function ($message) use ($mail_arr)
			{
				$message->
				from('supersalesmachine@gmail.com', 'Super Sales Machine')->
				to  ( $mail_arr['contact_email'], $mail_arr['first_name'] . ' ' . $mail_arr['last_name'] )->
				subject('Congratulations '. $mail_arr['first_name'].'. Your Installation is Complete!');
			});

			foreach($id_list as $tmp_id){
				myFunctions::changeStatusesFeatures($tmp_id, 'email_confirmation_sent', 1);
			}

			$msg = 'I have sent INSTALATION_CONFIRMATION EMAIL to customer '.$mail_arr['contact_email'];
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
			print json_encode($return);
			return;

		}
		catch(\Exception $e)
		{
			$msg = 'problem with sending instalation_confirmation email to customer '.$mail_arr['contact_email'].' Transaction id = '.$id;
			//myFunctions::addRecordToLogInstallations(-1, $msg, $id);
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			myFunctions::addGlobalLog($id, -1, $msg);
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			return;
		}
	}




	/**
	 * Do a big install.
	 */
	private function doBigInstall($id, $email = 0)
	{
		set_time_limit(600);

		$sql_query  = "SELECT id, user_firstname, user_lastname, pname, user_email, ";
		$sql_query .= "paypal_email, customer_support_email, clickbank_id, jvzoo_id, ";
		$sql_query .= "SUBSTRING_INDEX(txt_file_url, '.txt', 1) as txt_file_url, date_ordered, ";
		$sql_query .= "FIND_IN_SET('email',statuses_features)>0 as email_mode, ";
		$sql_query .= "ftp_host, ftp_username, ftp_password, ftp_dir, website, statuses_features, version ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE id = ".$id;

		$return = array();

		$results = DB::select($sql_query);
		if(!$results) {
			$msg = 'Problem with getting FTP credentials from database.';
			// change database status
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
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


        // set up basic connection
        
        $conn_id = @ftp_connect($server_ip);
        $timestamp = date("Y-m-d H:i:s", round(microtime (true)));

        if ($conn_id) {
			$msg = 'connect in to FTP - OK.';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
        }
        else {
			$msg = 'There is a problem with connect to FTP server. <br>';
			$msg = 'Please check FTP details and/or update FTP details in this transaction record.<br> ';
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
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
				myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				print json_encode($return);
                return;
            }
        }


        // turn passive mode on
        
        ftp_pasv($conn_id, true);


        // change FTP directory

        if (@ftp_chdir ($conn_id,  $ftp_dir )) {
			$msg = 'FTP directory changed successfully';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));            
        } else {
            $msg  =  'There is a problem with changing directory on FTP.<br>';
            $msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br>';
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
            return;
        }


		// put download.php to customer FTP server

		if (@ftp_put($conn_id, 'downloader.php', 'downloader.php', FTP_ASCII)) {
			$msg = 'downloader.php successfully uploaded';
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));     
		} else {
			$msg =  'There is a problem with uploading downloader.php to FTP server.<br>';
			$msg .= 'Please check FTP details and/or update FTP details in this transaction record ?<br>';
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			@ftp_delete($conn_id, 'downloader.php');
			ftp_close($conn_id);
			exit;
		}

		// launch downloader.php in self-test mode

	$msg = 'Try to downloader selftest mode '.$website.'/downloader.php?action=self-test';
	array_push( $return, $this->getLoginResultArray(1, $msg, $id)); // zahardkodowane ze zawsz ok? 

	
	if ($this->checkHttpFileExist($website.'/downloader.php?action=self-test') ) {
		$msg = 'try to get downloader.php by HTTP: OK';
		array_push( $return, $this->getLoginResultArray(1, $msg, $id)); 
	} else {
		$msg = 'try to get downloader.php by HTTP: FAILED '.$website.'/downloader.php?action=self-test';
		myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
		array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
		print json_encode($return);
		//@ftp_delete($conn_id, 'downloader.php');
		ftp_close($conn_id);
		exit;
	}

	$ctx = stream_context_create(array( 
	    'http' => array( 
	        'timeout' => 440 
	        ) 
	    ) 
	); 
	
	$a = file_get_contents ($website.'/downloader.php?action=self-test', 0, $ctx);

	//$a = file_get_contents ( );
		if ($a) {
			$msg = 'Reading downloader.php self-test: OK';
			$tmp_array = json_decode($a, true);
			$return = array_merge ($return, $tmp_array);
			array_push( $return, $this->getLoginResultArray(1, $msg, $id));
		 }
		else {

			$msg = 'Reading downloader.php self-test: FAILED';
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
			print json_encode($return);
			@ftp_delete($conn_id, 'downloader.php');
			ftp_close($conn_id);
			exit;
		}

		$add_url  = '&first_name='.base64_encode($results[0]->user_firstname);
		$add_url .= '&last_name='.base64_encode($results[0]->user_lastname);
		$add_url .= '&contact_email='.base64_encode($results[0]->user_email);
		$add_url .= '&customer_support_email='.base64_encode($results[0]->customer_support_email);
		$add_url .= '&paypal_email='.base64_encode($results[0]->paypal_email);
		$add_url .= '&clickbank_id='.base64_encode($results[0]->clickbank_id);
		$add_url .= '&jvzoo_id='.base64_encode($results[0]->jvzoo_id);

		$url = $website.'/downloader.php?action=downinstal&product_url='.base64_encode($txt_file_url).$add_url;

		$msg = 'Try to decode json from this file '.$url;

		array_push( $return, $this->getLoginResultArray(1, $msg, $id)); // zahardkodowane ze zawsz ok? 
		
		$ctx = stream_context_create(array( 
			'http' => array( 
				'timeout' => 440 
				) 
			) 
		); 
		
		$a = $this->file_get_contents_curl ($url, 0, $ctx);
		if ($a) {

			$jsonResultsFromDownloader = json_decode($a, true);
			if ($jsonResultsFromDownloader === false ) {
				$msg = 'I received data from downloader.php but this is not json format. Part of data = '.$a;
				array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
				myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
				print json_encode($return);
				@ftp_delete($conn_id, 'downloader.php');
				ftp_close($conn_id);
				exit;
			}

			// check every log from downloader each by each
			foreach ($jsonResultsFromDownloader as $item) {
				$msg = $item['msg'];
				$status = $item['status'];
				$timestamp = $item['timestamp'];
				array_push( $return, $this->getLoginResultArray($status, $msg, $id, $timestamp));
				if ($status == -1) {
					array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
					myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
					print json_encode($return);
					//@ftp_delete($conn_id, 'downloader.php');
					ftp_close($conn_id);
					exit;
				}
			}

			// change database status
			myFunctions::changeTimestampAndTransactionStatus($id, 'installed');

			// send email
			$mail_arr = array(
				'product_name' => $results[0]->pname,
				'contact_email' => $results[0]->user_email,
				'first_name' => $results[0]->user_firstname,
				'last_name' => $results[0]->user_lastname,
				'website' => $results[0]->website
				);
/*
			if ($results[0]->email_mode > 0) {

				try {
					$tmp = Mail::send('emails.instalation_confirmation', $mail_arr, function ($message) use ($mail_arr)
					{
						$message->
						from('supersalesmachine@gmail.com', 'Super Sales Machine')->
						to  ( $mail_arr['contact_email'],
							$mail_arr['first_name'] . ' ' . $mail_arr['last_name']
							)->
						subject('Congratulations '. $mail_arr['first_name'].'. Your Installation Of '.$mail_arr['product_name'].' Is Complete!');
					});
				} catch(\Exception $e) {

					array_push( $return, $this->getLoginResultArray(-1, 'problem with sending email', $id));
					myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
					// TODO 1: change transaction_status to "problem"
					// TODO 2: save log with information that email was not sent
					$status = DB::table('log_global')->insert(array(
						array(
							'status' => '-1',
							'msg' => 'problem with sending instalation confirmation email. CONTACT_EMAIL = '.$results[0]->user_email.' DATE_ORDERED = '.$results[0]->date_ordered.". DETAILS = ". $e
						)
					));
				}
			}
*/
			$tmp_array = json_decode($a, true);

			if (json_last_error() == JSON_ERROR_NONE) {
				if (count($tmp_array)>0) {
					foreach ($tmp_array as $item) {
						array_push( $return, $this->getLoginResultArray($item['status'], $item['msg'], $id,  $item['timestamp']));
					}
				}
			} else {
				array_push( $return, $this->getLoginResultArray(-1, 'problem with decoding downloader.php JSON data', $id));
				myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
				print json_encode($return);
				@ftp_delete($conn_id, 'downloader.php');
				ftp_close($conn_id);
				exit;
			}

			$return = array_merge ($return, $tmp_array);
		}
		else {
			$msg = 'do action: '.$website.'/downloader.php?action=downinstal&product_url='.$txt_file_url.' - FAILED';
			myFunctions::changeTimestampAndTransactionStatus($id, 'problem');
			print json_encode($return);
			exit;
		}

		@ftp_delete($conn_id, 'downloader.php');
		ftp_close($conn_id);
		print json_encode($return);
	}



	/**
	 * Show the form for editing transacton record.
	 */
	public function editTransaction($id)
	{

		$check_box_query = "SELECT REPLACE( REPLACE(COLUMN_TYPE, 'set(', '') , ')','') as strings ";
		$check_box_query .= "FROM information_schema.COLUMNS ";
		$check_box_query .= "WHERE TABLE_SCHEMA = '".env('DB_DATABASE')."' ";
		$check_box_query .= "AND TABLE_NAME = 'transactions' ";
		$check_box_query .= "AND COLUMN_NAME = 'statuses_features' ";
		
		$results = DB::select($check_box_query);

		// build query for checkbox
		$build_query = "SELECT ";
		$exploded = explode(",", $results[0]->strings);
		foreach ($exploded as &$item) {
			$item = trim($item, "'");
			$build_query .= "(FIND_IN_SET('" . $item . "',statuses_features)>0) AS " . $item . ", ";
		}
		$build_query = rtrim($build_query, ", ");
		$build_query .=" FROM transactions WHERE id = ".$id;
		$checkboxes = DB::select($build_query);


		$sql_query  = "SELECT id, user_firstname, user_lastname, user_email, pname, pcode, uicode, ";
		$sql_query .= "paypal_api_username, paypal_api_password, paypal_api_signature, "; 
		$sql_query .= "version, txt_file_url, date_ordered, date_installed, ftp_host, ftp_username, ";
		$sql_query .= "ftp_password, ftp_dir, website, transaction_id, awlist1, awlist2, grlist1, ";
		$sql_query .= "grlist2, list1title, list1info, list2title, list2info, comments, status ";
		$sql_query .= "FROM " ;
		$sql_query .= "transactions ";
		$sql_query .= "WHERE " ;
		$sql_query .= "id =".$id;

		$results = DB::table('transactions') ->where('id', '=', $id)->get() ;
		$result = json_decode(json_encode($results[0]), true);

		$result['updated'] = false;
		$return = array(
						'form' => $result,
						'checkboxes' => $checkboxes[0]
						);

		return view('edit_transaction', $return);
	}



	/**
	 * Show the transaction instalation logs
	 *
	 */
	public function showLogs($id)
	{

		$sql_query = "SELECT id, user_id, user_name, timestamp, msg, status" ;
		$sql_query .= " FROM log_installations" ;
		$sql_query .= " WHERE transaction_id = ". $id;
		$sql_query .= " ORDER BY id " ;
		$sql_query .= " DESC" ;

		$results = DB::select($sql_query);

		$str = '<table class="table table-bordered">';
		$str .= "<tr>\n";
		$str .= "<th>Username </td>\n";
		$str .= "<th>Timestamp</td>\n";
		$str .= "<th>message</td>\n";
		$str .= "<th>status</td>\n";
		$str .= "</tr>\n";

		if ($results>0)
			foreach ($results as $item)  {
				$str .= "<tr ";
				if ($item->status != 1) $str .= 'class="danger" ';
				$str .= ">\n";
				$str .= "<td>".$item->user_name."</td>\n";
				$str .= "<td>".$item->timestamp."</td>\n";
				$str .= "<td>".$item->msg."</td>\n";
				$str .= "<td>".$item->status."</td>\n";
				$str .= "</tr>\n";
			}
		$str .= '<table>';

		$result = array('text'=>$str);
		return view('show_transaction_logs', $result);
	}



	
	public function giveMeDifferencesBeetweanOldRecordAndNewRecord($id, $new_data) {
		$old_data = myFunctions::getOneTransactionRecordFullData($id);

		$return = array( );

  		if ($old_data[0]->user_firstname != $new_data['user_firstname']) $return['user_firstname'] = $old_data[0]->user_firstname. ' => ' .$new_data['user_firstname'];
		if ($old_data[0]->user_lastname != $new_data['user_lastname'])   $return['user_lastname'] =  $old_data[0]->user_lastname.' => '.$new_data['user_lastname'];
		if ($old_data[0]->user_email != $new_data['user_email']) $return['user_email'] = $old_data[0]->user_email.' => '.$new_data['user_email'];
		if ($old_data[0]->customer_support_email != $new_data['customer_support_email']) $return['customer_support_email'] = $old_data[0]->customer_support_email.' => '.$new_data['customer_support_email'];
		if ($old_data[0]->paypal_email != $new_data['paypal_email']) $return['paypal_email'] = $old_data[0]->paypal_email.' => '.$new_data['paypal_email'];
		if ($old_data[0]->paypal_api_username != $new_data['paypal_api_username']) $return['paypal_api_username'] = $old_data[0]->paypal_api_username.' => '.$new_data['paypal_api_username'];
		if ($old_data[0]->paypal_api_password != $new_data['paypal_api_password']) $return['paypal_api_password'] = $old_data[0]->paypal_api_password.' => '.$new_data['paypal_api_password'];
		if ($old_data[0]->paypal_api_signature != $new_data['paypal_api_signature']) $return['paypal_api_signature'] = $old_data[0]->paypal_api_signature.' => '.$new_data['paypal_api_signature'];
		if ($old_data[0]->clickbank_id != $new_data['clickbank_id']) $return['clickbank_id'] = $old_data[0]->clickbank_id.' => '.$new_data['clickbank_id'];
		if ($old_data[0]->jvzoo_id != $new_data['jvzoo_id']) $return['jvzoo_id'] = $old_data[0]->jvzoo_id.' => '.$new_data['jvzoo_id'];
		if ($old_data[0]->transaction_id != $new_data['transaction_id']) $return['transaction_id'] = $old_data[0]->transaction_id.' => '.$new_data['transaction_id'];
		if ($old_data[0]->pname != $new_data['pname']) $return['pname'] = $old_data[0]->pname.' => '.$new_data['pname'];
		if ($old_data[0]->pcode != $new_data['pcode']) $return['pcode'] = $old_data[0]->pcode.' => '.$new_data['pcode'];
		if ($old_data[0]->uicode != $new_data['uicode']) $return['uicode'] = $old_data[0]->uicode.' => '.$new_data['uicode'];
		if ($old_data[0]->version != $new_data['version']) $return['version'] = $old_data[0]->version.' => '.$new_data['version'];
		if ($old_data[0]->txt_file_url != $new_data['txt_file_url']) $return['txt_file_url'] = $old_data[0]->txt_file_url.' => '.$new_data['txt_file_url'];
		if ($old_data[0]->ftp_host != $new_data['ftp_host']) $return['ftp_host'] = $old_data[0]->ftp_host.' => '.$new_data['ftp_host'];
		if ($old_data[0]->ftp_username != $new_data['ftp_username']) $return['ftp_username'] = $old_data[0]->ftp_username.' => '.$new_data['ftp_username'];
		if ($old_data[0]->ftp_password != $new_data['ftp_password']) $return['ftp_password'] = $old_data[0]->ftp_password.' => '.$new_data['ftp_password'];
		if ($old_data[0]->ftp_dir != $new_data['ftp_dir']) $return['ftp_dir'] = $old_data[0]->ftp_dir.' => '.$new_data['ftp_dir'];
		if ($old_data[0]->website != $new_data['website']) $return['website'] = $old_data[0]->website.' => '.$new_data['website'];
		if ($old_data[0]->awlist1 != $new_data['awlist1']) $return['awlist1'] = $old_data[0]->awlist1.' => '.$new_data['awlist1'];
		if ($old_data[0]->awlist2 != $new_data['awlist2']) $return['awlist2'] = $old_data[0]->awlist2.' => '.$new_data['awlist2'];
		if ($old_data[0]->grlist1 != $new_data['grlist1']) $return['grlist1'] = $old_data[0]->grlist1.' => '.$new_data['grlist1'];
		if ($old_data[0]->grlist2 != $new_data['grlist2']) $return['grlist2'] = $old_data[0]->grlist2.' => '.$new_data['grlist2'];
		if ($old_data[0]->list1title != $new_data['list1title']) $return['list1title'] = $old_data[0]->list1title.' => '.$new_data['list1title'];
		if ($old_data[0]->list1info != $new_data['list1info']) $return['list1info'] = $old_data[0]->list1info.' => '.$new_data['list1info'];
		if ($old_data[0]->list2title != $new_data['list2title']) $return['list2title'] = $old_data[0]->list2title.' => '.$new_data['list2title'];
		if ($old_data[0]->list2info != $new_data['list2info']) $return['list2info'] = $old_data[0]->list2info.' => '.$new_data['list2info'];
		if ($old_data[0]->comments != $new_data['comments']) $return['comments'] = $old_data[0]->comments.' => '.$new_data['comments'];
		if ($old_data[0]->statuses_features != $new_data['statuses_features']) $return['statuses_features'] = $old_data[0]->statuses_features.' => '.$new_data['statuses_features'];

		if (count($return)>0) $return = array('RECORD_'.$id.'_EDITED'=> '(OLD_VALUE => VEW_WALUE)' ) + $return;
			else $return = array('RECORD_'.$id.'_EDITED'=> 'no changes' ) + $return;

		$string = json_encode($return);
		return $string; 
	}


	/**
	 * Show the form for editing transacton record.
	 *
	 */
	public function saveTransaction(EditTransactionFormRequest $request)
	{
		$id = $request->input('id', false);

		$statuses_features_string = '';
		$statuses_features = $request->input('statuses_features');
		if (count($statuses_features)>0) {
			foreach ($statuses_features as $key => $item) {
				$statuses_features_string .= $key.',';
			}
			$statuses_features_string = rtrim($statuses_features_string,',');
		}

		$tmp_array = array(
			'user_firstname' => $request->input('user_firstname', ''),
			'user_lastname' => $request->input('user_lastname', ''),
			'user_email' => $request->input('user_email', ''),
			'customer_support_email'=> $request->input('customer_support_email'),
			'paypal_email'=> $request->input('paypal_email'),

			'paypal_api_username'=> $request->input('paypal_api_username'),
			'paypal_api_password'=> $request->input('paypal_api_password'),
			'paypal_api_signature'=> $request->input('paypal_api_signature'),

			'clickbank_id'=> $request->input('clickbank_id'),
			'jvzoo_id'=> $request->input('jvzoo_id'),
			'aweber_username'=> $request->input('aweber_username'),
			'aweber_password'=> $request->input('aweber_password'),

			'transaction_id'=> $request->input('transaction_id'),
			'pname' => $request->input('pname', ''),
			'pcode' => $request->input('pcode', ''),
			'uicode' => $request->input('uicode', ''),
			'version' => $request->input('version', ''),
			'txt_file_url' => $request->input('txt_file_url', ''),
			'ftp_host' => $request->input('ftp_host', ''),
			'ftp_username' => $request->input('ftp_username', ''),
			'ftp_password' => $request->input('ftp_password', ''),
			'ftp_dir' => $request->input('ftp_dir', ''),
			'website' => $request->input('website', ''),
			'awlist1' => $request->input('awlist1', ''),
			'awlist2' => $request->input('awlist2', ''),
			'grlist1' => $request->input('grlist1', ''),
			'grlist2' => $request->input('grlist2', ''),
			'list1title' => $request->input('list1title', ''),
			'list1info' => $request->input('list1info', ''),
			'list2title' => $request->input('list2title', ''),
			'list2info' => $request->input('list2info', ''),
			'comments' => $request->input('comments', ''),
			'statuses_features' => $statuses_features_string
			//'status' => $request->input('status', '')
		);

		$return = array();
		
		$differenceString = $this->giveMeDifferencesBeetweanOldRecordAndNewRecord($id, $tmp_array);

		array_push( $return, $this->getLoginResultArray(1, $differenceString, $id));

		DB::table('transactions')
		->where('id', $id)
		->update($tmp_array);

		$check_box_query = "SELECT REPLACE( REPLACE(COLUMN_TYPE, 'set(', '') , ')','') as strings ";
		$check_box_query .= "FROM information_schema.COLUMNS ";
		$check_box_query .= "WHERE TABLE_SCHEMA = '".env('DB_DATABASE')."' ";
		$check_box_query .= "AND TABLE_NAME = 'transactions' ";
		$check_box_query .= "AND COLUMN_NAME = 'statuses_features' ";
		$results = DB::select($check_box_query);
		// build query for checkbox
		$build_query = "SELECT ";
		$exploded = explode(",", $results[0]->strings);
		foreach ($exploded as &$item) {
			$item = trim($item, "'");
			$build_query .= "(FIND_IN_SET('" . $item . "',statuses_features)>0) AS " . $item . ", ";
		}
		$build_query = rtrim($build_query, ", ");
		$build_query .=" FROM transactions WHERE id = ".$id;
		$checkboxes = DB::select($build_query);



		// $sql_query = "SELECT id, user_firstname, user_lastname, user_email, pname, pcode, uicode, ";
		// $sql_query .= "version, txt_file_url, date_ordered, date_installed, ftp_host, ftp_username, ";
		// $sql_query .= "ftp_password, ftp_dir, website, transaction_id, awlist1, awlist2, grlist1, ";
		// $sql_query .= "grlist2, list1title, list1info, list2title, list2info, status ";
		// $sql_query .= "FROM " ;
		// $sql_query .= "transactions ";
		// $sql_query .= "WHERE " ;
		// $sql_query .= "id =".$id;

		$results = DB::table('transactions') ->where('id', '=', $id)->get() ;
		$result = json_decode(json_encode($results[0]), true);

		$result['updated'] = true;
		$return = array(
						'form' => $result,
						'checkboxes' => $checkboxes[0]
						);
		return view('edit_transaction', $return);
	}


private function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


	public function checkHttpFileExist($url)
	{

		try {
			$result = @file_get_contents($url);
		}
		catch (Exception $e) {
			$msg = 'Problem with getting url='.$url.' '.$e->getMessage();
			array_push( $return, $this->getLoginResultArray(-1, $msg, $id));
		}
		
		$decoded = json_decode ($result, true);
		if (is_array($decoded)) {
			$element = array_pop($decoded);
			if (isset($element['status'])) {
				if ($element['status'] == 1) return true;
				else return false;
			}
		}
		return false;


		//		[{"timestamp":"2015-10-25 17:09:18","status":1,"msg":"downloader.php self-test: Writing test file on customer server - OK"},{"timestamp":"2015-10-25 17:09:18","status":1,"msg":"downloader.php self-test: Reading test file on customer server - OK"}]



		// 		$ch = curl_init($url);
		// //		curl_setopt($ch, CURLOPT_NOBODY, true);
		// 		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,"; 
		// 		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5"; 
		// 		$header[] = "Cache-Control: max-age=0"; 
		// 		$header[] = "Connection: keep-alive"; 
		// 		$header[] = "Keep-Alive: 300"; 
		// 		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7"; 
		// 		$header[] = "Accept-Language: en-us,en;q=0.5"; 
		// 		$header[] = "Pragma: "; // browsers keep this blank. 

		// 		curl_setopt($ch, CURLOPT_URL, $url); 
		// 		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
		// 		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		// 		//curl_setopt($ch, CURLOPT_ENCODING , "");
		// 		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate'); 
		// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 		 
		// 		curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com'); 
		// 		curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
		// 		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// 		curl_setopt($ch, CURLOPT_TIMEOUT, 10); 

		// 		curl_exec($ch);
		// 		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// print "<pre>";
		// print_r($retcode);
		// print "</pre>";
		// 		// $retcode >= 400 -> not found, $retcode = 200, found.
		// 		curl_close($ch);
		// 		if ($retcode == 200) return true;
		// 		else return false;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
