<?php

define("VERSION", "1.0");

error_reporting(E_ERROR | E_PARSE);

switch ($_GET['action'] ) {
	case "self-test":
		selfTest();
	break;
	case "downinstal":
		downloadAndUnpack();
	break;
	case "uninstall":
		uninstall();
	break;
	case "version":
		$return = array ('time'=> $time2-$time1, 'status' => '1', 'msg' => VERSION);
		print json_encode($return);
	break;
	default:
		$return = array ('time'=> $time2-$time1, 'status' => '-1', 'msg' => 'action for downloader not defined.');
		print json_encode($return);
}


function downloadAndUnpack() {

	$return = array();

	$pathinfo = pathinfo (base64_decode($_GET['product_url']));

	ini_set('max_execution_time', 300);

	$msg = 'downloader.php: download And Unpack - start';
	array_push( $return, getLoginResultArray(1, $msg));	

	$zipNameAndExt = $pathinfo['filename']. '.'.$pathinfo['extension'];

	// 1. download product zip file
	$a = downloadfile (base64_decode($_GET['product_url']), $zipNameAndExt);  // it works as CURL


	if ($a == true) {
		$msg = 'downloader.php: downloading big zip file - '.base64_decode($_GET['product_url']). ' - OK';
		array_push( $return, getLoginResultArray(1, $msg));
	}
	else {
		$msg = 'downloader.php: downloading big zip file - '.base64_decode($_GET['product_url']). ' - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	// 2. unzip zip file
	if (!class_exists('ZipArchive')) {
		$msg = 'this server does not support ZipArchive! Installation has to be done manually';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	$zip = new ZipArchive;
	$res = $zip->open($zipNameAndExt);
	if ($res == true) {
		$zip->extractTo('.');
		$zip->close();
		$msg = 'downloader.php: unzip big zip product file '.$zipNameAndExt.' - OK';
		array_push( $return, getLoginResultArray(1, $msg) );
		// delete zip fie
		unlink($zipNameAndExt);
	} else {
		// array_push( $return, getLoginResultArray(-1, $msg) );
		$msg = 'downloader.php: unzip big zip product file '.$zipNameAndExt.' - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}
	

	// 1. download product zip file
	$admin_zip_file = 'admin.zip';
	$a = downloadfile ('http://www.supersalesmachine.com/admin/files/SSM-admin.zip', $admin_zip_file );  // it works as CURL

	if ($a == true) {
		$msg = 'downloader.php: downloading admin zip file - http://www.supersalesmachine.com/admin/files/SSM-admin.zip - OK';
		array_push( $return, getLoginResultArray(1, $msg));
	}
	else {
		$msg = 'downloader.php: downloading admin zip file - http://www.supersalesmachine.com/admin/files/SSM-admin.zip - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	// 2. unzip admin file
	$zip = new ZipArchive;
 
	$res = $zip->open($admin_zip_file);
	if ($res == true) {
		$zip->extractTo('.');
		$zip->close();
		$msg = 'downloader.php: unzip admin zip product file '.$admin_zip_file.' - OK';
		array_push( $return, getLoginResultArray(1, $msg) );
		unlink('admin.zip');
	} else {
		// array_push( $return, getLoginResultArray(-1, $msg) );
		$msg = 'downloader.php: unzip admin zip product file '.$admin_zip_file.' - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}	


	// 3. change admin/user.php file
	$user_file = "admin/user.php";

	$config_file = @file_get_contents($user_file);

	if ($config_file === false ) {
		$msg = 'downloader.php: I can not read '.$user_file.' - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	$lines = explode("\n" , $config_file);

	$first_name = base64_decode($_GET['first_name']);
	$last_name = base64_decode($_GET['last_name']);
	$contact_email = base64_decode($_GET['contact_email']);
	$customer_support_email = base64_decode($_GET['customer_support_email']);
	$paypal_email = base64_decode($_GET['paypal_email']);
	$clickbank_id = base64_decode($_GET['clickbank_id']);
	$jvzoo_id = base64_decode($_GET['jvzoo_id']);

	$new_file = '';

	// create array to follow every replace
	$xchg = array (
		'rname' => false,
		'contact' => false,
		'fname' => false,
		'lname' => false,
		'email' => false,
		'pp' => false,
		'cb' => false,
		'jvz' => false,
	);

	foreach ($lines as $line) {
		if (preg_match ('/^(\$rname.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$first_name.$matches[3];
			$xchg['rname'] = true;
		}
		if (preg_match ('/^(\$contact.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$contact_email.$matches[3];
			$xchg['contact'] = true;
		}
		if (preg_match ('/^(\$fname.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$first_name.$matches[3];
			$xchg['fname'] = true;
		}
		if (preg_match ('/^(\$lname.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$last_name.$matches[3];
			$xchg['lname'] = true;
		}
		if (preg_match ('/^(\$email.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$customer_support_email.$matches[3];
			$xchg['email'] = true;
		}
		if (preg_match ('/^(\$pp.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$paypal_email.$matches[3];
			$xchg['pp'] = true;
		}
		if (preg_match ('/^(\$cb.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$clickbank_id.$matches[3];
			$xchg['cb'] = true;
		}
		if (preg_match ('/^(\$jvz.*=.*\')(.*)(\';.*)$/' , $line, $matches)) {
			$line = $matches[1].$jvzoo_id.$matches[3];
			$xchg['jvz'] = true;
		}

		$new_file .= $line."\n";
	}

	$error_counter = 0;
	foreach ($xchg as $key => $item) {
		if ($item === false) {
			$error_counter++;
			$msg = 'downloader.php: I did not replace '.$key.' variable in admin/user.php - FAILED';
			array_push( $return, getLoginResultArray(-1, $msg) );
		}
	}

	$save_status = @file_put_contents($user_file, $new_file);

	if ($save_status === false ) {
		$msg = 'downloader.php: problem with saving admin/user.php file - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
	}

	if ($error_counter>0) {
		$msg = 'downloader.php: Almost all jobs done except some variables in admin/user.php file. Please take a look to log file - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg));
		print json_encode($return);
		exit;
	}

	$msg = 'downloader.php: Download And Unpack and change admin/user.php file - OK';
	array_push( $return, getLoginResultArray(1, $msg));
	print json_encode($return);
}



/**
 * This function prepares array for log
 */

function getLoginResultArray($status, $msg) {
	$timestamp = date("Y-m-d H:i:s", round(microtime (true)));
	$return = array('timestamp'=>$timestamp,
		'status' => $status,
		'msg' => $msg
	);
	return $return;
}



function selfTest() {

	$return = array();

	$a = file_put_contents ('downloader_test_file.txt' , 'downloader test string' );

	if ($a) {
		$msg = 'downloader.php self-test: Writing test file on customer server - OK';
		array_push( $return, getLoginResultArray(1, $msg));
	}
	else {
		$msg = 'downloader.php self-test: Writing test file on customer server - FAILED. Test write permission for Apache/PHP';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	// 2. try to read just cteated file
	$a = file_get_contents ('downloader_test_file.txt' );
	if ($a) {
		$msg = 'downloader.php self-test: Reading test file on customer server - OK';
		array_push( $return, getLoginResultArray(1, $msg));
		unlink('downloader_test_file.txt');
	}
	else {
		$msg = 'downloader.php self-test: Reading test file on customer server - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		unlink('downloader_test_file.txt');
		exit;
	}


	// 3. download small zip file
	$small_zip_file_path = 'http://alltic.home.pl/aaron/example_product/downloadertestfile.zip';
	$a = downloadfile ($small_zip_file_path, 'downloader_test_zipfile.zip');
	if ($a) {
		$msg = 'downloader.php self-test: downloading small zip file from '.$small_zip_file_path. ' OK';
		array_push( $return, getLoginResultArray(1, $msg));
	}
	else {
		$msg = 'downloader.php self-test: downloading small zip file from '.$small_zip_file_path. ' FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

	// 4. try to open just downloader zip file
	$a = fopen("downloader_test_zipfile.zip", "r");
	if ($a == true) {
		$msg = 'downloader.php self-test: trying to fopen just test downloaded file - OK';
		array_push( $return, getLoginResultArray(1, $msg));
	}
	else {
		$msg = 'downloader.php self-test: trying to fopen just test downloaded file - FAILED';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
	}

    // 5. try to depack this zip file
    $zip = new ZipArchive;
    $res = $zip->open('downloader_test_zipfile.zip');
    if ($res == true) {
        $extractStatus = $zip->extractTo('tmp_downloader_test_zip_unpacked_file');
        $zip->close();

        // 6. if all done return TRUE
        if ($extractStatus == true ) {

            // delete files that I used to test ZIP functions
            unlink('downloader_test_file.txt');
            unlink('downloader_test_zipfile.zip');
            unlink('tmp_downloader_test_zip_unpacked_file/testfile.txt');
            rmdir('tmp_downloader_test_zip_unpacked_file/');
            unlink('downloader.php');

			$msg = 'downloader.php self-test: trying to unzip just downloaded test file - OK';
			array_push( $return, getLoginResultArray(1, $msg));
        }
        else {
			$msg = 'downloader.php self-test: trying to unzip just downloaded test file - FAILED. I believe customers server/apache/php can not unpack zip archives.';
			array_push( $return, getLoginResultArray(-1, $msg) );
			print json_encode($return);
			exit;
        }
    } else {
		$msg = 'downloader.php self-test: trying to unzip just downloaded file - FAILED. I believe customers server/apache/php can not unpack zip archives.';
		array_push( $return, getLoginResultArray(-1, $msg) );
		print json_encode($return);
		exit;
    }

    print json_encode($return);
}

// ==============================================================================================
function downloadfile($file_source, $file_target) {
	// to do: change this to CURL with timeout support

    $rh = fopen($file_source, 'rb');
    if ($rh == false ) return false;

    $wh = fopen($file_target, 'w+b');
    if (!$rh || !$wh) {
        return false;
    }

    while (!feof($rh)) {
        if (fwrite($wh, fread($rh, 4096)) === FALSE) {
            return false;
        }
        echo ' ';
        flush();
    }

    fclose($rh);
    fclose($wh);

    return true;
}
