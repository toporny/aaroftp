<?php namespace App\Http\Controllers;

use App\Http\Requests;
use DB;
use Input;
use App\library\myFunctions;
use Illuminate\Support\Facades\Redirect;
use Aws\Laravel\AwsFacade as AWS;
use Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class BucketfileController extends Controller {

	private $bucket;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->bucket = new \App\library\myBuckets;
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
		if (Auth::user()->role != 'admin') return view('only_for_admin');

 		// $modal = array('message'=>'AA', 'class'=>'danger', 'label'=>'danger', 'title'=>'title');
//$modal = array('aa'=>'AA', 'bb'=>'BB');
		// print "<pre>";
		// print_r($modal);
		// exit;

// <!-- @if ($problem == 'true')
// 						<p style="background-color:red; color:white;padding: 10px; ">
// 							Problem with parsing json. I haven't saved any data. If you want to restore original template file click <a style="padding:0px 5px 0px 5px" href="{{ url() }}/bucketfile/restore_template_file" class="btn btn-danger" role="button"> HERE </a><br>
// 							If problem still exist show <input name="submit2" style="padding:0px 5px 0px 5px" class="btn btn-danger" type="submit" value="plain text"> and try to test this file here: <a style="padding:0px 5px 0px 5px" target="_new"  class="btn btn-danger" role="button" href="http://json.parser.online.fr/">http://json.parser.online.fr/</a>
// 						</p>
// @endif
//  -->



		$pass_variables = array(
			'modal' => isset($modal) ? $modal : '' ,
			'template_file'=> $this->bucket->getTemplateFile(),
			'auth_user' => Auth::user()->role,
			'bucket_names' => $this->bucket->getBucketFileNames(),
			'protected_domains'=> $this->bucket->getProtectedDomains()
		);
		return view('bucketfile_template_protected', $pass_variables );
	}


	/**
	 * get domains from all installed transaction
	 *
	 * @return Response
	 */

	// // todo: remove this from here. this is duplicated from myBuckets.php
	// private function getDomainsFromInstalledTransactions() {
	// 	$sql_query  = "SELECT DISTINCT LOWER(website) as website ";
	// 	$sql_query .= "FROM transactions ";
	// 	$sql_query .= "WHERE status = 'installed' ";
	// 	$sql_query .= 'AND FIND_IN_SET ("free_product", statuses_features) = 0 '; // show only NOT free_products
	// 	$results = DB::select($sql_query);
	// 	return $results;
	// }

	// /**
	//  * get domains from all installed + waiting transaction
	//  *
	//  * @return Response
	//  */

	// // todo: remove this from here. this is duplicated from myBuckets.php
	// private function getDomainsFromInstalledWaitingTransactions() {
	// 	$sql_query  = "SELECT DISTINCT LOWER(website) as website ";
	// 	$sql_query .= "FROM transactions ";
	// 	$sql_query .= "WHERE (status = 'installed' ";
	// 	$sql_query .= "OR status = 'waiting') ";
	// 	$sql_query .= 'AND FIND_IN_SET ("free_product", statuses_features) = 0 '; // show only NOT free_products
	// 	$results = DB::select($sql_query);
	// 	return $results;
	// }


	/**
	 * generate Pure Generated Domains As Array
	 *
	 * @return array
	 */
	// private function generatePureGeneratedDomainsAsArray($object) {
	// 	$aDomains = array();
	// 	foreach ($object as $domain) {
	// 		$domain = trim($domain->website);
	// 		$domain = trim($domain, '"');
	// 		$domain = rtrim($domain, '\*');
	// 		$domain = rtrim($domain, '\/');
	// 		$urlParts = parse_url($domain);
	// 		$domain = $urlParts['host'] ;
	// 		$domain = str_ireplace('www.','', $domain);
	// 		$aDomains[] = $domain;	
	// 	}
	// 	return $aDomains;
	// }

	/**
	 * generate Pure Protecte Unique Domains As Array
	 *
	 * @return array
	 */
	// private function generatePureProtectedUniqueDomainsAsArray() {
	// 	$sql_query  = "SELECT var_string ";
	// 	$sql_query .= "FROM settings ";
	// 	$sql_query .= "WHERE key_string = 'bucket_protected_domains' ";
	// 	$results = DB::select($sql_query);
	// 	$domains = explode(",", $results[0]->var_string);
	// 	array_pop($domains);

	// 	foreach ($domains as &$domain) {
	// 		$domain = trim($domain);
	// 		$domain = trim($domain, '"');
	// 		$domain = rtrim($domain, '\*');
	// 		$domain = rtrim($domain, '\/');
	// 		$urlParts = parse_url($domain);
	// 		$domain = $urlParts['host'] ;
	// 		$domain = str_ireplace('www.','', $domain);
	// 	}

	// 	$unique_protected_domains = array_unique ($domains);
 // 		return $unique_protected_domains;
	// }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function generatedDomains()
	{
		//if (Auth::user()->role != 'admin') return view('only_for_admin');

		$generated_domains = $this->bucket->getTransactionDomains();
// print "<pre>";
// print_r( $generated_domains  );
// print "</pre>";
// exit;


		// $pure_protected_domains_array = $this->generatePureProtectedUniqueDomainsAsArray();
		// $generated_domains_array = $this->generatePureGeneratedDomainsAsArray($generated_domains);

		// $double_domain = array_intersect ($pure_protected_domains_array, $generated_domains_array);
		// $double_domain = implode("<br>", $double_domain);

		// $pass_variables = array('generated_domains' => $generated_domains,
		// 						'additional_information' => '',
		// 						'auth_user' => Auth::user()->role,
		// 						'double_domain' => $double_domain );

		// foreach ($generated_domains as &$item) {
		// 	if (substr($item->website, 0, 5) == 'https') {
		// 		$item->website = '<font color="red">'.$item->website."</font>";
		// 		$pass_variables['additional_information'] = 'NOTE: <font color="red"> Some domains have <b>https</b>. Final file is generated only with <b>http</b> !</font></i>';
		// 	}
		// }
		$pass_variables = array(
			'transaction_domains' => $this->bucket->getTransactionDomains(),
			'auth_user' => Auth::user()->role
			// 'template_file'=> $this->bucket->getTemplateFile(),
			// 'auth_user' => Auth::user()->role,
			// 'protected_domains'=> $this->bucket->getProtectedDomains()
		);

		return view('bucketfile_generated_domains', $pass_variables );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function finalFiles()
	{
 
		$show_me_bucket_htacces_link = url('autoinstalator/showMeBucket');
		$show_me_bucket_htacces_link = str_replace('://','://Aaron123:Danker345@', $show_me_bucket_htacces_link);

		$tmp = Input::get('waiting_status', 1);
		$this->bucket->setWaitingStatusToo($tmp);

//print $this->bucket->getWaitingStatusToo();

		$pass_variables = array(
			'final_files'=> $this->bucket->getSplittedBucketFiles(),
			'last_s3_bucket_policy_udated_time' => $this->bucket->getLastS3BucketPolicyUdatedTime(),
			'last_known_bucket_policy' => $this->bucket->getLastKnownBucketPolicy(),
			'also_waiting_status' => $this->bucket->getWaitingStatusToo(),
			'show_me_bucket_htacces_link' => $show_me_bucket_htacces_link,
			'auth_user' => Auth::user()->role
			);

		return view('bucketfile_final_file', $pass_variables );
	}

// =================================================================



// private function getLastS3BucketPolicyUdatedTime() {

// 		$sql_query  = "SELECT key_string, var_string ";
// 		$sql_query .= "FROM settings ";
// 		$sql_query .= "WHERE key_string = 'last_s3_bucket_policy_udated_time' ";

// 		$results = DB::select($sql_query);
// 		if (count($results) == 1) return substr($results[0]->var_string, 0, 16);
// 		else return substr('0000-00-00 00:00:00', 0, 16);
//  	}
		

// remove this. this is done in class myBucket
// private function generateFinalFile($aGeneratedDomains) { 
	
// 	$sql_query  = "SELECT key_string, var_string ";
// 	$sql_query .= "FROM settings ";
// 	$sql_query .= "WHERE key_string = 'bucket_template_file' ";
// 	$sql_query .= "OR key_string = 'bucket_protected_domains' ";
// 	$sql_query .= "ORDER BY key_string ";

// 	$results = DB::select($sql_query);

// 	if (count($results) != 2) {
// 		$template_file = '';
// 		$protected_domains = '';
// 		print "has to have exactly two rows in [setting] table";
// 		return;
// 	}
// 	else {
// 		foreach ($results as $item) {
// 			if( $item->key_string == 'bucket_template_file') $template_file = $item->var_string;
// 			if( $item->key_string == 'bucket_protected_domains') $protected_domains = $item->var_string;
// 		}
// 	} 	

// 	$generatedDomains = '';
// 	foreach ($aGeneratedDomains as $domain) {
// 	$urlParts = parse_url($domain->website);
// 		$string = str_ireplace('www.','', $urlParts['host']);
// 		$string = trim($string);
// 		$string = rtrim ( $string, '/' );
// 		$generatedDomains .= '"'.'http://www.'.$string.'/*",' . "\n";
// 		$generatedDomains .= '"'.'http://'.$string.'/*",' . "\n";
// 	}

// 	// $constant_website  ='"http://www.setupmyproduct.com/*",'."\n";
// 	// $constant_website .='"http://setupmyproduct.com/*"'."\n";

// 	//$final_file = str_replace("[%current-date%]", date("Y-m-d"), $template_file);
// 	$final_file = str_replace("[%all-domains%]", $protected_domains, $final_file);
// 	//$final_file2 = str_replace("[%generated-domains%]", "\n\n" . $generatedDomains.$constant_website, $final_file);
// 	return $final_file2;
// }


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)
	{
		if (Auth::user()->role != 'admin') return view('only_for_admin');

		$modal = array();
 
		if(isset($_POST['add_domain'])) {
			$modal = $this->bucket->addDomainToProtectedDomainTable($_POST['domain']);
			//return redirect('dashboard')->with('status', 'Profile updated!');
		}

		if(isset($_POST['remove_domain'])) {
			$modal = $this->bucket->removeDomainToProtectedDomainTable($_POST['domain']);
		}

		// $aGeneratedDomains = $this->bucket->getDomainsFromInstalledTransactions();

 	// 	$generatedDomains = '';
 	// 	foreach ($aGeneratedDomains as $domain) {
 	// 		$generatedDomains .= '"'. $domain->website. '",' . "\n";
 	// 	}

		// $protected_domains = $request->input('protected_domains', false);
		// $template_file = $request->input('template_file', false);
		// if ($request->input('submit2', false) == 'plain text'){
		// 	print "<pre>".$this->bucket->prepareFinalBucketFile($template_file, $generatedDomains, $protected_domains )."</pre>";
		// 	exit;
		// }

		// $problem_with_json_decode = $this->bucket->checkCorrectJsonFinalBucketFile( $template_file, $generatedDomains, $protected_domains );
		// if ($problem_with_json_decode == 'false') {
		// 	// if ($this->saveToDatabase($template_file, $protected_domains) ) {
		// 	// 	$url =  url()."/bucketfile/final_file";
  //  //          	return Redirect::to($url);
  //  //          	exit;
		// 	// }
		// }

//		$protected_domains = $this->bucket->getProtectedDomains();

		$pass_variables = array(
	//		'problem'           => $problem_with_json_decode, // obsluzyc potem problem w formie komponentu 
			'modal'			    => $modal,
			'template_file'     => $this->bucket->getTemplateFile(),
			'auth_user'		    => Auth::user()->role,
			'bucket_names'      => $this->bucket->getBucketFileNames(),
			'protected_domains' => $this->bucket->getProtectedDomains()
			);
		return view('bucketfile_template_protected', $pass_variables );
	}


	/**
	 * saveToDatabase
	 *
	 * @param string $template_file
	 * @param string $generatedDomains
	 * @param string $protected_domains
	 * @return string
	 */
	// private function saveToDatabase($template_file, $protected_domains ) {
		
	// 	if (Auth::user()->role != 'admin') return view('only_for_admin');

	// 	$counter = 0;

	// 	$tmp_array = array('var_string' => $template_file);
	// 	$tmp = DB::table('settings')->where('key_string', 'bucket_template_file')->update($tmp_array);
	// 	if ($tmp) $counter++;

	// 	$sDomainsBeforeChanged = DB::table('settings')->select('var_string')->where('key_string', 'bucket_protected_domains')->first()->var_string;
	// 	$sDomainsAfterChanged = $protected_domains;
	// 	$aDomainsBeforeChanged = explode ("\n", $sDomainsBeforeChanged);
	// 	$aDomainsAfterChanged = explode ("\n", $sDomainsAfterChanged);

	// 	$result_added = array_diff ( $aDomainsAfterChanged,  $aDomainsBeforeChanged );
	// 	$result_removed = array_diff ( $aDomainsBeforeChanged,  $aDomainsAfterChanged );

	// 	$new_domains = implode(" ", $result_added);
	// 	$removed_domains = implode(" ", $result_removed);
	// 	myFunctions::addGlobalLog(Auth::user()->id, 1, 'Template & protected domain FILE changed. New domains: '.$new_domains.' Removed domains: '.$removed_domains);

	// 	$tmp_array = array('var_string' => $protected_domains);
	// 	$tmp = DB::table('settings')->where('key_string', 'bucket_protected_domains')->update($tmp_array);
	// 	if ($tmp) $counter++;

	// 	if ($counter == 2) return true;
	// 	return false;
	// }



	/**
	 * get domains from all installed transaction
	 *
	 * @return Response
	 */
	public function updateS3BucketByAjax() {

	$sharedConfig = [
		'driver' => 's3',
		'version' => 'latest'
	];

	$s3 =  AWS::createClient('s3');

	$splittedBucketFiles = $this->bucket->getSplittedBucketFiles();

	$tmp_array = array( 'var_string' => '' );
	$tmp = DB::table('settings')->where('key_string', 'last_known_bucket_policy')->update($tmp_array);


	foreach ($splittedBucketFiles as $key=>$str) {
		try {
			$arr = array(
				'Bucket' => env('AWS_BUCKET'.$key),
				'Policy' => $str
			);
			$qq = $s3->putBucketPolicy($arr);
		} catch (\Aws\S3\Exception\S3Exception $e) {
			// The AWS error code (e.g., )
			// echo $e->getAwsErrorCode() . "\n";
			// The bucket couldn't be created
			// echo $e->getMessage() . "\n";
			myFunctions::addGlobalLog(Auth::user()->id, -1, $e->getAwsErrorCode() . '. '.  $e->getMessage());
			$msg  = 'Amazon S3 Bucket updated - FAILED - please check global logs.';
			$return =  myFunctions::prepareResultArray(-1, $msg);
			print json_encode($return);
			return;
		}

		if ($qq['@metadata']['statusCode'] == 204) {
			$msg = 'Amazon S3 Bucket updated with success';
			$return = myFunctions::prepareResultArray(1, $msg) ;  
			myFunctions::addGlobalLog(Auth::user()->id, 1, 'Amazon S3 Bucket ('.env('AWS_BUCKET'.$key).') updated - OK. Length of json file = '.strlen($str).' bytes.');
			$tmp_array = array('var_string' => date("Y-m-d H:i:s"));
			$tmp = DB::table('settings')->where('key_string', 'last_s3_bucket_policy_udated_time')->update($tmp_array);
		} else {
			$msg  = 'Amazon S3 Bucket updated - FAILED';
			$return =  myFunctions::prepareResultArray(-1, $msg) ;  
			myFunctions::addGlobalLog(Auth::user()->id, 1, 'Amazon S3 Bucket updated - FAILED. Please login to amazon and check bucket policy manually, otherwise all sold product may not work.');
			print json_encode($return);
			return;		
		}


		try {
			$ww = $s3->getBucketPolicy(array('Bucket' => env('AWS_BUCKET'.$key)));
			$policy = $ww->get('Policy');
		} catch (\Aws\S3\Exception\S3Exception $e) {
			myFunctions::addGlobalLog(Auth::user()->id, -1, $e->getAwsErrorCode() . '. '.  $e->getMessage());
			$msg  = 'Amazon S3 Bucket reading - FAILED - please check global logs. Try to do this operation once again or check bucket policy on amazon server and update it manually!';
			$return =  myFunctions::prepareResultArray(-1, $msg);
			print json_encode($return);
			return;
		}

		$try_do_decode_policy = json_decode($policy, true);
		if ($try_do_decode_policy == NULL) {
			$msg = 'I can not decode bucket policy during bucket update operation. All customers websites do not work. This is very serious error. Please check bucket policy on amazon server and update it manually!';
			myFunctions::addGlobalLog(Auth::user()->id, -1, $msg);
			$return = myFunctions::prepareResultArray(1, $msg);
			print json_encode($return);
			exit;
		} else {
			$tmp = $try_do_decode_policy['Statement'][0]['Condition']['StringLike']['aws:Referer'];
			$counter = count($tmp);
			$result = "\n".'Bucket ('.($key+1).'/'.count($splittedBucketFiles).')   Name: '.env('AWS_BUCKET'.$key).'  Counter = '.$counter."\n\n";
			foreach ($tmp as $key1 => $item) {
				$result .= ($key1+1).'. '.$item." (".env('AWS_BUCKET'.$key).")\n";
			}
			
			DB::update("UPDATE settings SET var_string= CONCAT(var_string,'".$result."') WHERE key_string='last_known_bucket_policy'");
		}
	}
	print json_encode($return);
}


	/**
	 * restoreTemplateFile
	 *
	 * @return string
	 */
	public function restoreTemplateFile()
	{
		if (Auth::user()->role != 'admin') return view('only_for_admin');
		
		$sql_query  = "SELECT key_string, var_string ";   // move this to one privbate function
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'bucket_template_file' ";
		$sql_query .= "OR key_string = 'bucket_protected_domains' ";
		$sql_query .= "ORDER BY key_string ";
 
		$results = DB::select($sql_query);

		if (count($results) != 2) {
			$template_file = '';
			$protected_domains = '';
			print "has to have exactly two rows (bucket_template_file and bucket_protected_domains )in [setting] table";
			return;
		}
		else {
			foreach ($results as $item) {
				if( $item->key_string == 'bucket_protected_domains') $protected_domains = $item->var_string;
			}
		}

		$template_file = $this->getTemplateDefaultText();
	 

		$pass_variables = array('problem'=>'false',
							    'template_file'=>$template_file,
							    'auth_user' => Auth::user()->role,
							    'protected_domains'=>$protected_domains);

		return view('bucketfile_template_protected', $pass_variables );		
	}

/**
* getTemplateDefaultText
*
* @return string
*/
private function getTemplateDefaultText() {
$str = <<<EOF
{
  "Version": "2008-10-17",
  "Id": "preventHotLinking",
  "Statement": [
    {
      "Sid": "1",
      "Effect": "Allow",
      "Principal": {
        "AWS": "*"
      },
      "Action": "s3:GetObject",
EOF;
$str .= '      "Resource": "arn:aws:s3:::'.env('AWS_BUCKET').'/*",';
$str .= <<<EOF
      "Condition": {
        "StringLike": {
          "aws:Referer": [
            [%all-domains%]
          ]
        }
      }
    }
  ]
}
EOF;

return $str;
}



}
