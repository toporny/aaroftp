<?php
namespace App\library
{
	use DB;

// class statusData {
// 	private $status;
// 	private $class;
// 	private $message;

// 	public function __CONSTRUCT($status = null, $class = null, $message = null) {
// 		$this->status = $status;
// 		$this->class = $class;
// 		$this->message = $message;
// 	}

// 	public function getStatus() {
// 		return $this->status;
// 	}

// 	public function getClass() {
// 		return $this->class;
// 	}

// 	public function getMessage() {
// 		return $this->message;
// 	}


// }


class myBuckets {

    // private $aReturn = array();
    // private $item = null;
    // private $piz_filepath = null;
    // private $zip_filepath = null;
   

    private $waitingStatusToo = 1;
    private $maxDomainsInOneBucket = 300;
    //private $bucketFileNames = array();
    //private $template_file = '';
    //private $sProtectedDomains = '';
    //private $oTransactionDomains = array();
    private $aCombinedDomains = array(); // generated + protected (only http)
    //private $aCombinedDomainsWithWWW = array(); // generated + protected + http + http://www


    public function __CONSTRUCT()
	{
		if (env('AWS_BUCKET0')) $this->bucketFileNames[0] = env('AWS_BUCKET0');
		if (env('AWS_BUCKET1')) $this->bucketFileNames[1] = env('AWS_BUCKET1');
		if (env('AWS_BUCKET2')) $this->bucketFileNames[2] = env('AWS_BUCKET2');
		if (env('AWS_BUCKET3')) $this->bucketFileNames[3] = env('AWS_BUCKET3');
		if (env('AWS_BUCKET4')) $this->bucketFileNames[4] = env('AWS_BUCKET4');
		if (env('AWS_BUCKET5')) $this->bucketFileNames[5] = env('AWS_BUCKET5');

		$sql_query  = "SELECT key_string, var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'bucket_template_file' ";
		//$sql_query .= "OR key_string = 'bucket_protected_domains' ";
		$sql_query .= "ORDER BY key_string ";
 
		$results = DB::select($sql_query);

		if (count($results) != 1) {
			$this->template_file = '';
			$this->protected_domains = '';
            myFunctions::addGlobalLog(0, -1, 'CRITICAL ERROR. key (bucket_template_file) missing in setting table.');
		}
		else {
			 $this->template_file = $results[0]->var_string;
		}

		$this->oTransactionDomains = $this->getDomainsFromTransactions();
		$this->oProtectedDomains = $this->getoProtectedDomains();

		// merge oProtectedDomains + oTransactionDomains
		$this->aCombinedDomains = array_merge ( $this->oProtectedDomains, $this->oTransactionDomains );

		// to nie jest potrzebne w konstruktorze!
		// make array two times longer but add "www" to all domain
		####################################################################################
		//$this->aCombinedDomainsWithWWW = $this->doubledomainsListAndADD_WWW( $this->aCombinedDomains );

    }



    // this function returns all doubled domains ( http + http://www )
	// public function doubledomainsListAndADD_WWW() {
	// 	$aCombinedDomainsWWW = array();

	// 	foreach ($this->aCombinedDomains as $item) {
	// 		 $aCombinedDomainsWWW[] = (object)array('domain' => str_ireplace ('http://','http://www.', $item->domain ),
	// 		 								'bucket_name_index' => $item->bucket_name_index
	// 		 								);
	// 	}
		
	// 	$merged = array_merge ( $this->aCombinedDomains, $aCombinedDomainsWWW);
	// 	return $merged;
	// }


    public function addDomainToProtectedDomainTable($domain) {
		$sql_query  = "SELECT count(*) as count ";
		$sql_query .= "FROM protected_domains ";
		$sql_query .= "WHERE domain = '".trim($domain)."'";

		$results = DB::select($sql_query);
		if ($results[0]->count == 0) {
			try
			{
				$tmp_array = array('domain'=>$domain, 'bucket_name_index' => 0);
				$id = DB::table('protected_domains')->insert($tmp_array);
				$modal = array('status'=>'1', 'message'=>'Done with success. Refresh page. now.', 'class'=>'success', 'label'=>'OK', 'title'=>'Success');
				return $modal;
			}
			catch(\Exception $ex)
			{
				dd("problem with inserting data into protected_domains table");
				$modal = array('status'=>'-1', 'message'=>'Problem with adding domain. Refresh page now.', 'class'=>'danger', 'label'=>'OK', 'title'=>'Error');
				return $modal;				
			}
		}
		else {
			$modal = array('status'=>'-1', 'message'=>'Seems domain already exist. Refresh page.', 'class'=>'danger', 'label'=>'OK', 'title'=>'Error');
			return $modal;	
		}
    }

    public function removeDomainToProtectedDomainTable($domain) {
		$sql_query  = "DELETE FROM protected_domains WHERE domain = '".trim($domain)."'";
		$id = DB::delete($sql_query);
		if ($id == 1) {
			$modal = array('status'=>'1', 'message'=>'Done with success.', 'class'=>'success', 'label'=>'OK', 'title'=>'Success');
			return $modal;
		} else {
			$modal = array('status'=>'-1', 'message'=>'Problem with deleting domain.', 'class'=>'danger', 'label'=>'OK', 'title'=>'Error');
			return $modal;	
		}
    }

    


    public function getTransactionDomains() {
    	return $this->oTransactionDomains;
    }


	public function getoProtectedDomains()
	{
		$sql_query  = "SELECT distinct(domain), bucket_name_index ";
		$sql_query .= "FROM protected_domains ";
		$sql_query .= "ORDER BY domain ";
 
		$oProtectedDomains = DB::select($sql_query);

		return $oProtectedDomains;
	}


	public function getCombinedDomains() {
		$combinedDomains = array();
		$aProtectedDomains = explode ("\n" , trim( $this->sProtectedDomains)) ;
		$aGeneratedDomains = array();
		foreach ($this->oGeneratedDomains as $item) {
			$aGeneratedDomains[] = $item->website;
		}
	    $combinedDomains = array_merge  ( $aProtectedDomains, $aGeneratedDomains) ;
	    return $combinedDomains;
	}

	public function getWaitingStatusToo() {
	  return $this->waitingStatusToo;
	}


	public function setWaitingStatusToo($status) {
	  $this->waitingStatusToo = $status;
	}


	public function getTemplateFile() {
	  return $this->template_file;
	}
	
	public function getProtectedDomains() {
 
	  return $this->oProtectedDomains;
	}

	// public function howManyBucketFiles() {
	//   return $this->bucketFiles;
	// }


	public function getBucketFileNames() {
	  return $this->bucketFileNames;
	}
 
	public function getLastS3BucketPolicyUdatedTime() {

		$sql_query  = "SELECT key_string, var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'last_s3_bucket_policy_udated_time' ";

		$results = DB::select($sql_query);
		if (count($results) == 1) return substr($results[0]->var_string, 0, 16);
		else return substr('0000-00-00 00:00:00', 0, 16);
 	}
		

	public function getLastKnownBucketPolicy () {
		$sql_query  = "SELECT var_string ";
		$sql_query .= "FROM settings ";
		$sql_query .= "WHERE key_string = 'last_known_bucket_policy' ";
		$results = DB::select($sql_query);
		$return = $results[0]->var_string;
		return $return;
	}


	// public function splitToPartition( $list, $p ) {
	//     $listlen = count( $list );
	//     $partlen = floor( $listlen / $p );
	//     $partrem = $listlen % $p;
	//     $partition = array();
	//     $mark = 0;
	//     for ($px = 0; $px < $p; $px++) {
	//         $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
	//         $partition[$px] = array_slice( $list, $mark, $incr );
	//         $mark += $incr;
	//     }
	//     return $partition;
	// }

	private function getPartitionedDomains() {  // divide files max 300 domains
		// $p = count($this->bucketFileNames);
		// $listlen = count( $this->aCombinedDomains );


	    $chunked = array_chunk($this->aCombinedDomains , $this->maxDomainsInOneBucket);
	    return $chunked;
	}

 
	//public function generateFinalFile($aGeneratedDomains) { 
	public function generateFinalFile($key, $list) { 
	
		$sDomains = ''; 
		$partitionedDomains = $this->getPartitionedDomains();


		foreach ($list as $item) {
			$urlParts = parse_url ($item->domain);
			$sDomains .= '"http://www.'.$urlParts['host'].'/*",' . "\n";
			$sDomains .= '"http://'.$urlParts['host'].'/*",' . "\n";

			//$sDomains .= '"'.$item->domain.'",' . "\n";
		}
		$sDomains = rtrim ( $sDomains, ",\n" );

		// $sDomains .= '"http://onet.pl",' . "\n";
		// $sDomains .= '"http://www.onet.pl",' . "\n";

		$final_file = str_replace("[%bucket-name%]", env('AWS_BUCKET'.($key)) , $this->template_file);
		$final_file2 = str_replace("[%all-domains%]", $sDomains , $final_file);

		return $final_file2;
	}

	/**
	 * prepareFinalBucketFile
	 *
	 * @param string $template_file
	 * @param string $generatedDomains
	 * @param string $protected_domains
	 * @return string
	 */
	public function prepareFinalBucketFile($template_file, $generatedDomains, $protected_domains )
	{
		$generatedDomains = rtrim($generatedDomains, ",\n");
		$generatedDomains = rtrim($generatedDomains, ",");
		$final_file = str_replace("[%all-domains%]", "\n\n" . $generatedDomains, $template_file);
		return $final_file;
	}




	public function getSplittedBucketFiles() {
		
		$partitionedDomains = $this->getPartitionedDomains();
		$files = array();
		foreach ($partitionedDomains as $key => $item) {
			$files[] = $this->generateFinalFile($key, $item);
		}

		return $files;
	}

	// public getBucketFile($index) // indexed from zero
	// 	$this->aGeneratedDomains = $this->getDomainsFromTransactions();
	// 	return $this->aGeneratedDomains;
	// 	//$final_file2 = $this->generateFinalFile($aGeneratedDomains);

	// }


	/**
	 * get domains from all installed transaction
	 *
	 * @return Response
	 */

	public function getDomainsFromInstalledTransactions() {
		$sql_query  = "SELECT DISTINCT LOWER(website) as website ";
		$sql_query .= "FROM transactions ";
		$sql_query .= "WHERE status = 'installed' ";
		$sql_query .= 'AND FIND_IN_SET ("free_product", statuses_features) = 0 '; // show only NOT free_products
		$results = DB::select($sql_query);
		return $results;
	}

	/**
	 * get domains from all installed + waiting transaction
	 *
	 * @return Response
	 */

	private function getDomainsFromTransactions() {

		// 'X' means "bucket_name_index" will be set up later.
		$sql_query  = "SELECT DISTINCT REPLACE(lower(website), 'http://www.', 'http://') as domain ";
		$sql_query .= " FROM transactions ";
		$sql_query .= " WHERE (status = 'installed' ";
		if ($this->getwaitingStatusToo() == 1) $sql_query .= "OR status = 'waiting' ";
		$sql_query .= ") ";
		$sql_query .= 'AND FIND_IN_SET ("free_product", statuses_features) = 0 '; // show only NOT free_products

		$results = DB::select($sql_query);
		return $results;
	}



	/**
	 * checkCorrectJsonFinalBucketFile
	 *
	 * @param string $template_file
	 * @param string $generatedDomains
	 * @param string $protected_domains
	 * @return boolean
	 */
// 	public function checkCorrectJsonFinalBucketFile($template_file, $generatedDomains, $protected_domains )
// 	{
// 		$joinedJson = $this->prepareFinalBucketFile($template_file, $generatedDomains, $protected_domains );
// // print "<pre>";
// // print_r($joinedJson);
// // exit;
// 		$try_do_decode = json_decode($joinedJson);
// 		if (is_null($try_do_decode)) $problem_with_json_decode = 'true';
// 		else $problem_with_json_decode = 'false';
// 		return $problem_with_json_decode;
// 	}




	// public setWaitingStatus($status)
 //      if ($status === 1) $this->waitingStatus = 1;
 //      if ($status === 0) $this->waitingStatus = 0;
	// }

	// public getWaitingStatus()
 //      return $this->waitingStatus;
	// }	

}






}
