<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use Aws\Laravel\AwsFacade as AWS;
use Aws\Laravel\AwsServiceProvider;
use App\library\myFunctions;
use App\library\autoinstalator;
use Illuminate\Http\Request;

class AutoInstalatorController extends Controller {




public function getDomainfromS3Bucket() {




	// http://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.S3.S3Client.html#_putBucketPolicy
	// http://alltic.home.pl/s3_deli/aws-sdk-php/


	// http://Aaron123:Danker345@aaroftp.local/autoinstalator/showMeBucket
	// http://Aaron123:Danker345@aarondev.enbe.pl/autoinstalator/showMeBucket

	// AWS_ACCESS_KEY_ID
	// AWS_SECRET_ACCESS_KEY
	// AWS_SESSION_TOKEN

	// Use Temporary Security Credentials (IAM Roles) Instead of Long-Term Access Keys
	// User Name,Password,Direct Signin Link
	// "przemyslaw",xXtIx5UiTf3b,https://598218725386.signin.aws.amazon.com/console

	// User Name,Access Key Id,Secret Access Key
	// "przemyslaw",AKIAJVUCRETIX6V5JUWQ,iQqZV2Y9GMexGM7JmaihkcNWnDvqqiCaWV5wZlHl

	// jak zainstalowac kommand line i wygenerowac session token
	// http://docs.aws.amazon.com/cli/latest/userguide/installing.html

	// https://aws.amazon.com/s3/
	// user email: tiptopmarketer@gmail.com
	// pass: d045313882

	// php SDK do amazona
	//http://aws.amazon.com/sdk-for-php/


}



	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function showMeBucket() { 

		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="My Realm"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Text to send if user hits Cancel button';
			exit;
		} else {

			if ($_SERVER['PHP_AUTH_USER'] != 'Aaron123') exit;
			if ($_SERVER['PHP_AUTH_PW'] != 'Danker345') exit;

			$sql_query  = "SELECT key_string, var_string ";
			$sql_query .= "FROM settings ";
			$sql_query .= "WHERE key_string = 'bucket_template_file' ";
			$sql_query .= "OR key_string = 'bucket_protected_domains' ";
			$sql_query .= "ORDER BY key_string ";
	 
			$results = DB::select($sql_query);

			if (count($results) != 2) {
				$template_file = '';
				$protected_domains = '';
				print "has to have exactly two rows in [setting] table";
				exit;
			}
			else {
				foreach ($results as $item) {
					if( $item->key_string == 'bucket_template_file') $template_file = $item->var_string;
					if( $item->key_string == 'bucket_protected_domains') $protected_domains = $item->var_string;
				}
			} 	

			$sql_query  = "SELECT DISTINCT website ";
			$sql_query .= "FROM transactions ";
			$sql_query .= "WHERE status = 'installed' ";
			$sql_query .= "OR status = 'waiting' ";
			$aGeneratedDomains = DB::select($sql_query);
	 		
	 		$generatedDomains = '';
	 		foreach ($aGeneratedDomains as $domain) {
				$urlParts = parse_url($domain->website);
	 			$string = str_ireplace('www.','', $urlParts['host']);
	 			$string = trim($string);
	 			$string = rtrim ( $string, '/' );
	 			$generatedDomains .= '"'.'http://www.'.$string.'/*",' . "\n";
	 			$generatedDomains .= '"'.'http://'.$string.'/*",' . "\n";
	 		}

			$constant_website  ='"http://www.setupmyproduct.com/*",'."\n";
			$constant_website .='"http://setupmyproduct.com/*"'."\n";

			$final_file = str_replace("[%current-date%]", date("Y-m-d"), $template_file);
			$final_file = str_replace("[%protected-domains%]", $protected_domains, $final_file);
			$final_file2 = str_replace("[%generated-domains%]", "\n\n" . $generatedDomains.$constant_website, $final_file);
			header("Content-Type: text/plain");
			print $final_file2;
		}
	}

	public function index()
	{
		ini_set('max_execution_time', 300);
		$this->beforeFilter(function() {
			// check timeline +/-5 seconds
		});
	}


	public function uploadfiles()
	{
		ini_set('max_execution_time', 300);

		$sql_query  = "SELECT id, ftp_host,ftp_username,ftp_password,ftp_dir, statuses_features, ";
		$sql_query .= "txt_file_url as txt_file, ";
		$sql_query .= "CONCAT(TRIM(TRAILING '.txt' FROM txt_file_url), '.zip') as zip_file, ";
		$sql_query .= "CONCAT(TRIM(TRAILING '.txt' FROM txt_file_url), '.piz') as piz_file ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE status = 'waiting' ";
		$sql_query .= "AND FIND_IN_SET ('ftp_path_ok', statuses_features) ";
		$sql_query .= "AND FIND_IN_SET ('autoinstall_started', statuses_features) = 0 ";
		$sql_query .= "AND FIND_IN_SET ('autoinstall_pizfile_uploaded', statuses_features) = 0 ";
		$sql_query .= "AND FIND_IN_SET ('autoinstall_error', statuses_features) = 0 ";
		$sql_query .= "AND FIND_IN_SET ('autoinstall_done', statuses_features) = 0 ";

		$results = DB::select($sql_query);

		if (count($results)>0) {
			foreach ($results as $item) {
				$autoinstalator = new autoinstalator( $item );
				myFunctions::changeStatusesFeatures($item->id, 'autoinstall_started', 1);
				myFunctions::addGlobalLog($item->id, 1, 'autoinstall_started '. $item->txt_file);

				if ($autoinstalator->putPizFileByFtp() ) {
					myFunctions::changeStatusesFeatures($item->id, 'autoinstall_pizfile_uploaded', 1);
					myFunctions::addGlobalLog($item->id, 1, 'autoinstall_pizfile_uploaded '. $item->txt_file);
				}
				else {
					myFunctions::addGlobalLog($item->id, -1, 'autoinstall_error '. $item->txt_file);
					myFunctions::changeStatusesFeatures($item->id, 'autoinstall_error', 1);
				}
			}
		}
		else {
			$status = DB::table('log_global')->insert(array(
				array(
					'status' => '1',
					'msg' => 'cron start with upload files but was nothing to do. '
				)
			));
		}

	}

	private function make_local_copy_of_zip () {
		return false;
	}

	private function make_local_copy_of_piz () {
		return false;
	}

	private function make_local_copy () {
		return false;
	}

	public function installfiles()
	{
		// installfiles
	}


}
