<?php
namespace App\library
{

use DB;

use Auth;

class myFunctions {


	public static function prepareResultArray($status, $msg, $timestamp = false) {

		if ($timestamp == false) $timestamp = date("Y-m-d H:i:s", round(microtime (true)));

 		$return = array('timestamp'=>$timestamp,
 						'status' => $status,
 						'msg' => $msg
 						);
 		return $return;
	}


	public static function getCurrentBucketName() {
		$sql_query = "SELECT var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'current_bucketname_index'";
		$getCurrentBucketNameIndex = DB::select($sql_query);
		if (count($getCurrentBucketNameIndex)>0) {
			return $getCurrentBucketNameIndex[0]->var_string;
		} else {
			return false;
		}
	}




	public static function getAllPnameWebstiteForDateOrdered($date_ordered) {
		$sql_query = "SELECT pname, LOWER(website) as website, user_firstname, user_lastname ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE date_ordered = '".$date_ordered."' ";
		$pnamesWebsites = DB::select($sql_query);

		return $pnamesWebsites;
	}


	public static function getOneTransactionRecrod($id) {
		$sql_query  = "SELECT id, user_firstname, pname, user_lastname, user_email, LOWER(website) as website, ";
		$sql_query .= "paypal_email, customer_support_email, date_ordered ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE id = ".$id;

		$resultsData = DB::select($sql_query);
		return $resultsData;    	
	}

	public static function getOneTransactionRecordFullData($id) {
		$sql_query  = "SELECT id, user_firstname, user_lastname, user_email, ";
		$sql_query  .= "paypal_email, paypal_api_username, paypal_api_password, paypal_api_signature,  "; // paypal_api_id,
		$sql_query  .= "customer_support_email, pname, pcode, uicode, version, txt_file_url, date_ordered, ";
		$sql_query  .= "date_updated, date_installed, date_expired, ";
		$sql_query  .= "ftp_host, ftp_username, ftp_password, ftp_dir, website, ";
		$sql_query  .= "transaction_id, clickbank_id, jvzoo_id, ";
		$sql_query  .= "awlist1, awlist2, grlist1, grlist2, list1title, list1info, list2title, list2info, ";
		$sql_query  .= "status, statuses_features, ip, http_referer, comments ";

		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE id = ".$id;

		$resultsData = DB::select($sql_query);
		return $resultsData;    	
	}


	public static function getAllIdForDateOrderedUserEmail($date_ordered, $user_email) {
		$sql_query  = "SELECT id ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE date_ordered = '".$date_ordered."' ";
		$sql_query .= "AND date_ordered = '".$user_email."'"; 
		$id_lists = DB::select($sql_query);
		$array_id = array(); 
		foreach ($id_lists as $item) {
			$array_id[] = $item->id;
		}

		return $array_id;
	}


	public static function checkEveryTransactionForUserInstalled($id) {

		$sql_query = "SELECT date_ordered, user_email FROM transactions WHERE id =".$id;
		$res1 = DB::select($sql_query);

		$sql_query  = "SELECT count(*) AS count_all, user_email, date_ordered, ";
		$sql_query  .= "(SELECT count(*) from transactions ";
		$sql_query  .= "WHERE date_ordered ='".$res1[0]->date_ordered."' AND user_email ='".$res1[0]->user_email."' AND status='installed' ) AS count_installed ";
		$sql_query  .= "FROM transactions ";
		$sql_query  .= "WHERE date_ordered ='".$res1[0]->date_ordered."' AND user_email ='".$res1[0]->user_email."' ";

		$results = DB::select($sql_query);
		return $results[0];
	}


	public static function getIP() {
		$ip = '0.0.0.0';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		    $ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}



	public static function changeStatusesFeatures($id, $marker, $status) {
		if (($status == true) || ($status == 1)) {
			$sql_query  = "UPDATE transactions SET statuses_features = ";
			$sql_query .= "CONCAT_WS(',', IF(statuses_features = '', NULL, statuses_features), '".$marker."') ";
			$sql_query .= "WHERE id = ".$id;
			DB::update($sql_query);
		} else {
			$sql_query  = "UPDATE transactions SET statuses_features = ";
			$sql_query  .= "TRIM(BOTH ',' FROM REPLACE(CONCAT(',', statuses_features, ','), ";
			$sql_query  .= "CONCAT(',', '".$marker."', ','), ',')) ";
			$sql_query  .= "WHERE id = ".$id;
			DB::update($sql_query);
		}
	}


	public static function addGlobalLog($id, $status, $msg) {
		$timestamp = date("Y-m-d H:i:s", round(microtime (true)));

		$tmp_array = array(
			'timestamp' => $timestamp,
			'product_id' => (isset($id)) ? $id : NULL,
			'status' => $status,
			'msg' => $msg
		);

		try
		{
			$id = DB::table('log_global')->insert($tmp_array);
		}
		catch(\Exception $ex)
		{
			dd("problem with inserting into log_global table");
		}
	}




	public static function addRecordToLogInstallations($status, $msg, $id, $timestamp = false) {
		if ($timestamp == false) $timestamp = date("Y-m-d H:i:s", round(microtime (true)));

		$tmp_array = array(
			'user_id' => (Auth::user()) ?  Auth::user()->id : 0,
			'user_name' => (Auth::user()) ? Auth::user()->name : 'autoinstalator' ,
			'ip' =>  myFunctions::getIP(),
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







		/**
		* changeTimestampAndTransactionStatus
		*
		* @return Response
		*/
    	
		public static function changeTimestampAndTransactionStatus($id, $status) { 
			$timestamp = date("Y-m-d H:i:s", round(microtime (true)));
			$sql = "UPDATE transactions SET date_installed = '".$timestamp."', ";
			$sql .= "status = '".$status."' WHERE id = ".$id;

			if ( $status == -1 ) { print "error"; exit; }
			return DB::update($sql);		
		}

		// ======================================================================

		public static function change_transaction_status( $id, $status ) {

			$old_query = "SELECT status FROM transactions WHERE id =".$id;
			$old = DB::select($old_query);

			$sql = "UPDATE transactions SET status = '".$status."' WHERE id = ".$id;			
			$update_result = DB::update($sql);

			$return = array();

			if ($update_result) {
				$msg = "changed status from: ". strtoupper ($old[0]->status) . ' to ' . strtoupper($status);
				array_push( $return, myFunctions::addRecordToLogInstallations(1, $msg, $id));	
			} else {
				$msg = "problem with changing status to: ".$status;
				array_push( $return, myFunctions::addRecordToLogInstallations(-1, $msg, $id));	
			}

			return $update_result;
		}


		// ======================================================================

	    public static function generateEmailTableWithProvidedData ($postData) {

	        $string  ='<table border="1" style="background-color:FFFFCC;border-collapse:collapse;border:1px solid FFCC00;color:000000;" cellpadding="3" cellspacing="3">'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">Name</td> <td style="margin:2px; padding:2px">'.$postData['first_name'].' '.$postData['last_name'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">Contact email</td> <td style="margin:2px; padding:2px">'.$postData['contact_email'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">FTP host:</td> <td style="margin:2px; padding:2px">'.$postData['ftp_host'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">FTP username</td> <td style="margin:2px; padding:2px">'.$postData['ftp_username'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">FTP password</td> <td style="margin:2px; padding:2px">'.$postData['ftp_password'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">FTP install folder</td> <td style="margin:2px; padding:2px">'.$postData['ftp_dir'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr>'."\n";
	        $string .='        <td style="margin:2px; padding:2px">Your website</td> <td style="margin:2px; padding:2px">'.$postData['website'].'</td>'."\n";
	        $string .='    </tr>'."\n";


			if ( (isset($postData['statuses_features'])) && ( strpos($postData['statuses_features'], 'aweber') !== FALSE) ) {

	            $string .='    <tr>'."\n";
	            $string .='        <td style="margin:2px; padding:2px">Aweber username</td><td style="margin:2px; padding:2px">'.$postData['aweber_username'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	            $string .='    <tr>'."\n";
	            $string .='        <td style="margin:2px; padding:2px">Aweber password</td><td style="margin:2px; padding:2px">'.$postData['aweber_password'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	        }


			if ( (isset($postData['statuses_features'])) && ( strpos($postData['statuses_features'], 'paypal') !== FALSE) )
			{

	            $string .='    <tr>'."\n";
	            $string .='        <td style="margin:2px; padding:2px">Paypal API username</td><td style="margin:2px; padding:2px">'.$postData['paypal_api_username'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	            $string .='    <tr>'."\n";
	            $string .='        <td style="margin:2px; padding:2px">Paypal API password</td><td style="margin:2px; padding:2px">'.$postData['paypal_api_password'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	            $string .='    <tr>'."\n";
	            $string .='        <td style="margin:2px; padding:2px">Paypal API signature</td><td style="margin:2px; padding:2px">'.$postData['paypal_api_signature'].'</td>'."\n";
	            $string .='    </tr>'."\n";
				// $string .='    <tr>'."\n";
				// $string .='        <td style="margin:2px; padding:2px">Paypal API id</td><td style="margin:2px; padding:2px">'.$postData['paypal_api_id'].'</td>'."\n";
				// $string .='    </tr>'."\n";
	        }        
	        $string .='</table>'."\n";

	        return $string;
	    }

	   	// ===========================================================

	    public static function generateNiceTableWithProvidedData ($postData) {

	        $string  ='<table class="table table-bordered">'."\n";
	        $string .='    <tr class="warning">'."\n";
	        $string .='        <td>Name</td><td>'.$postData['first_name'].' '.$postData['last_name'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="active">'."\n";
	        $string .='        <td>Contact email</td><td>'.$postData['contact_email'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="warning">'."\n";
	        $string .='        <td>FTP host:</td><td>'.$postData['ftp_host'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="active">'."\n";
	        $string .='        <td>FTP username</td><td>'.$postData['ftp_username'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="warning">'."\n";
	        $string .='        <td>FTP password</td><td>'.$postData['ftp_password'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="active">'."\n";
	        $string .='        <td>FTP install folder</td><td>'.$postData['ftp_dir'].'</td>'."\n";
	        $string .='    </tr>'."\n";
	        $string .='    <tr class="warning">'."\n";
	        $string .='        <td>Your website</td><td>'.$postData['website'].'</td>'."\n";
	        $string .='    </tr>'."\n";

			if ( (isset($postData['statuses_features'])) && ( strpos($postData['statuses_features'], 'aweber') !== FALSE) ) {
				$string .='    <tr class="active">'."\n";
				$string .='        <td>Aweber username</td><td>'.$postData['aweber_username'].'</td>'."\n";
				$string .='    </tr>'."\n";
				$string .='    <tr class="warning">'."\n";
				$string .='        <td>Aweber password</td><td>'.$postData['aweber_password'].'</td>'."\n";
				$string .='    </tr>'."\n";
			}

	        if ($postData['paypal_api_username']) {

	            $string .='    <tr class="active">'."\n";
	            $string .='        <td>Paypal API username</td><td>'.$postData['paypal_api_username'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	            $string .='    <tr class="warning">'."\n";
	            $string .='        <td>Paypal API password</td><td>'.$postData['paypal_api_password'].'</td>'."\n";
	            $string .='    </tr>'."\n";
	            $string .='    <tr class="active">'."\n";
	            $string .='        <td>Paypal API signature</td><td>'.$postData['paypal_api_signature'].'</td>'."\n";
	            $string .='    </tr>'."\n";
				// $string .='    <tr class="warning">'."\n";
				// $string .='        <td>Paypal API id</td><td>'.$postData['paypal_api_id'].'</td>'."\n";
				// $string .='    </tr>'."\n";
	        }

	        $string .='</table>'."\n";
	        return $string;
	    }

	    // ======================================================================
	    public static function makeProductsList ($products_list) {
	        $return = "<ul>\n";
	        foreach ($products_list as $product) {
	            $return .= "<li>". $product ."</li>";
	        }
	        $return .= "</ul>\n";
	        return $return;
	    }

	    // ===========================================================
	    public static function parseEmailTemplateFormSubmitted ($data) {

			$sql_query  = "SELECT var_string ";
			$sql_query .= "FROM settings ";
			$sql_query .= "WHERE key_string = 'submit_form_notification_template' ";
			$resultsData = DB::select($sql_query);

			$parsed = $resultsData[0]->var_string;

			$parsed = str_replace("[%first_name%]", $data['first_name'], $parsed);
			$parsed = str_replace("[%last_name%]", $data['last_name'], $parsed);
			$parsed = str_replace("[%products_list%]", $data['products_list'], $parsed);
			$parsed = str_replace("[%table_with_customer_details%]", $data['simpleTableWithProvidedData'], $parsed);
			$parsed = str_replace("[%letter_s%]", $data['letter_s'], $parsed);
			$parsed = str_replace("[%is_are%]", $data['is_are'], $parsed);

	        return $parsed;
	    }
    // ===========================================================
	    public static function parseEmailTemplateProductInstalled ($data) {

			$sql_query  = "SELECT var_string ";
			$sql_query .= "FROM settings ";
			$sql_query .= "WHERE key_string = 'product_installed_notification_template' ";
			$resultsData = DB::select($sql_query);

			$parsed = $resultsData[0]->var_string;

			$parsed = str_replace("[%first_name%]", $data['first_name'], $parsed);
			$parsed = str_replace("[%last_name%]", $data['last_name'], $parsed);
			$parsed = str_replace("[%products_list%]", $data['products_list'], $parsed);
			$parsed = str_replace("[%user_admin_url%]", $data['user_admin_url'], $parsed);

	        return $parsed;
	    }
    // ===========================================================
		

	}
}
?>