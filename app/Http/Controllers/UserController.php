<?php namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\library\myFunctions;
use Illuminate\Routing\Controller as BaseController;
use Mail;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Response;
use Input;
use View;
use Illuminate\Support\Facades\Redirect;
 
 

class UserController extends BaseController
{

    // public function showUserForm2 ($id, $options)
    // {
    //     $sql_query  = "SELECT uicode FROM products WHERE id = '".$id."'";
    //     $results = DB::select($sql_query);
    //     if($results[0]->uicode ) {

    //         parse_str($options, $output);
    //         $extrastring = '';

    //         if (isset($output['email'])) $extrastring .= '&email='.$output['email'];
    //         if (isset($output['paypal'])) $extrastring .= '&paypal='.$output['paypal'];
    //         $url =  url()."/?uicode=".$results[0]->uicode.$extrastring;
    //         // print $url;
    //         // exit;
    //         return Redirect::to($url);
    //     }
    //     return;
    // }


    public function showUserForm(Request $request)
    {
        $uicode = $request->input('uicode', false);
        $email_mode = $request->input('email', 1);
        $paypal_mode = $request->input('paypal', 0);
        $aweber_mode = $request->input('aweber', 0);

        $free_product = $request->input('free_product', 0);
        $autoinstall_mode = $request->input('autoinstall', 0);
        $ftp_path_ok = 0; // never show.

        $statuses_features = '';
        $statuses_features .= ($paypal_mode == 1) ? 'paypal,' : '';
        $statuses_features .= ($email_mode == 1) ? 'email,' : '';
        $statuses_features .= ($aweber_mode == 1) ? 'aweber,' : '';
        $statuses_features .= ($ftp_path_ok == 1) ? 'ftp_path_ok,' : '';
        $statuses_features .= ($free_product == 1) ? 'free_product,' : '';
        $statuses_features .= ($autoinstall_mode == 1) ? 'autoinstall,' : '';
        $statuses_features = rtrim($statuses_features,',');
        
        $counter = substr_count ( $uicode, ',') + 1;
        $in_string = "'".str_replace (',', "','", $uicode)."'";

        if ($uicode) {
            $sql_query  = "SELECT count(uicode) AS count FROM products WHERE uicode in (".$in_string.")";
            $results = DB::select($sql_query);

            if($results[0]->count == $counter ) {
                $pass_data = array(
                    'uicode' => $uicode,
                    'paypal_mode' => ($paypal_mode == 0) ? 0 : 1,
                    'paypal_mode' => ($paypal_mode == 0) ? 0 : 1,
                    'aweber_mode' => ($aweber_mode == 0) ? 0 : 1,
                    'email_mode' => ($email_mode == 0) ? 0 : 1,
                    'statuses_features' => $statuses_features,
                    'http_referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
                );
                return view('appuserform', $pass_data);
            }
        }
    
        if (env('APP_DEBUG') == true ) {
            print "something wrong with GET query<br>";
            print "should be something like: ".url()."/?uicode=xxxx<br><br>";
            print "or login <a href=".url()."/login>here</a>";
        }
        exit;
    }



    public function launchDownloaderInTestMode(Request $request)
    {
        $server_ip = $request->input('customer_url','');
        $server_ip = trim ($server_ip, '/' );
        $downloader_url = $server_ip . '/downloader.php?action=testmode';
        
        print $downloader_url;
        $homepage = file_get_contents($downloader_url);
        
        echo $homepage;
        exit;
    }




    public function testftp(Request $request)
    {
        // cale to sprawdzanie to powinno sie wyrzucic do osobnej klasy!

        $server_ip = $request->input('server_ip','');
        $ftp_username = $request->input('ftp_username','');
        $ftp_password = $request->input('ftp_password','');
        $ftp_dir = $request->input('ftp_dir','');
        $website = $request->input('url_customers_website','/');

        $current_time = date("Y-m-d-H-i-s", round(microtime (true)));
        
        $test_tmp_file = 'tmp_'.$current_time.'.html';
        
        $tmp_file_content = '<html><body>'.$current_time.'</body></html>';

        // create $test_tmp_file on local machine
        if (!@file_put_contents($test_tmp_file, $tmp_file_content)) {
            $return = array ('status' => '-1', 'msg' => 'I can not create test /'.$test_tmp_file.'/ at '.url().' Are write permissions set right? ');
            print json_encode($return);
            return;
        }

        $return = array ('status'=>'1', 'msg'=>'successfully uploaded');

        // set up basic connection
        $conn_id = @ftp_connect($server_ip);
        
        if ($conn_id) {
            $return = array ('status'=>'1', 'msg'=>'connect in to FTP - OK.');
        }
        else {
            $msg =  'There is a problem with connect into your FTP server.<br>';
            $msg .= 'Are you sure that you gave us correct data ?<br>';
            $msg .= '<pre>server/host : ' . $server_ip . "<br>";
            $msg .= 'ftp_username : ' . $ftp_username . "<br>";
            $msg .= 'ftp_password : ' . $ftp_password . "<br>";
            $msg .= 'ftp_dir : ' . $ftp_dir . "<br></pre>";
            $return = array ('status' => '-1', 'msg' => $msg);
            print json_encode($return);
            return;
        }

        // login with username and password
        $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
        {
            if ($login_result) {
                $return = array ('status'=>'1', 'msg'=>'log in to FTP - OK.');
            }
            else {
                $msg =  'There is a problem with login or password in your FTP server.<br>';
                $msg .= 'Are you sure that you gave us correct data ?<br>';
                $msg .= '<pre><u>DATA THAT YOU PROVIDED:</u><br><br>';
                $msg .= 'server/host : ' . $server_ip . "<br>";
                $msg .= 'ftp_username : ' . $ftp_username . "<br>";
                $msg .= 'ftp_password : ' . $ftp_password . "<br>";
                $msg .= 'ftp_dir : ' . $ftp_dir . "<br>";
                $msg .= 'URL to customer\'s website : ' . $website . "<br></pre>";
                $return = array ('status' => '-1', 'msg' => $msg);
                print json_encode($return);
                return;
            }
        }

        // turn passive mode on
        ftp_pasv($conn_id, true);


        // =================================================================
        // print $ftp_dir;
        // exit;
        if( $ftp_dir == '') {
            $ftp_dir = $this->try_to_guess_the_web_path($conn_id, $ftp_dir, $website, $test_tmp_file, $tmp_file_content);
            if ($ftp_dir == false) {
                $msg = "I was not able not find proper FTP_PATH";
                $return = array ('status' => '-5', 'msg' => $msg);
                print json_encode($return);
                return;
            }
        }

        // reset FTP dir//
        ftp_chdir ($conn_id,  '/' );

        // =================================================================

        // change FTP directory..
        // czy to jest nadal potrzebne? TAK ale TYLKO WTEDY kiedy nie udalo sie automatycznie ustalic path
        // ano tak skoro user moze dac FTP_PATH to trzeba sprawdzic czy dobra dal
        // powinno to byc przeniesione do osobnej klasy! 
        if (@ftp_chdir ($conn_id,  $ftp_dir )) {
            $return = array ('status'=>'1', 'msg'=>'successfully ftp directory changed');
        } else {

            $msg  =  'There is a problem with changing directory on FTP.<br>';
            $msg .= 'Are you sure that you gave us correct data ?<br>';
            $msg .= 'Especially <b>FTP DIR (install folder)</b><br>';
            $msg .= 'or <b>URL to website</b> ?<br>';

            $msg .= '<pre><u>DATA THAT YOU PROVIDED:</u><br><br>';
            $msg .= 'server/host : ' . $server_ip . "<br>";
            $msg .= 'ftp_username : ' . $ftp_username . "<br>";
            $msg .= 'ftp_password : ' . $ftp_password . "<br>";
            $msg .= 'ftp_dir : ' . $ftp_dir . "<br>";
            $msg .= 'URL to customer\'s website : ' . $website . "<br></pre>";
            $msg .= '<p style="font-weight:bold">Note:</p>';
            $msg .= 'You can also skip this step and continue anyway, ';
            $msg .= 'and let our team set up everything but';
            $msg .= 'it is strongly recomended to fill all fields in the right way.';
            $return = array ('status'=>'-6', 'msg'=> $msg);
            print json_encode($return);
            return;
        }

        // put testing file on server
        // czy to jest nadal potrzebne? TAK ale TYLKO WTEDY kiedy nie udalo sie automatycznie ustalic path
        // powinno to byc przeniesione do osobnej klasy! 
        if (@ftp_put($conn_id, $test_tmp_file, $test_tmp_file, FTP_ASCII)) {
            $return = array ('status'=>'1', 'msg'=>'successfully uploaded');
            unlink ($test_tmp_file);
        } else {
            $msg =  'There is a problem with uploading file to FTP server.<br>';
            $msg .= 'Are you sure that you gave us correct data ?<br>';
            $msg .= 'Please check FTP permission rights too ?<br>';
            $msg .= '<pre><u>DATA THAT YOU PROVIDED:</u><br><br>';
            $msg .= 'server/host : ' . $server_ip . "<br>";
            $msg .= 'ftp_username : ' . $ftp_username . "<br>";
            $msg .= 'ftp_password : ' . $ftp_password . "<br>";
            $msg .= 'ftp_dir : ' . $ftp_dir . "<br>";
            $msg .= 'URL to customer\'s website : ' . $website . "<br></pre>";
            $return = array ('status'=>'-1', 'msg'=> $msg );
            ftp_close($conn_id);
            print json_encode($return);
            return;
        }

        // try to get test file via WWW
        //$website = str_replace("/", "", $website);

        if ($remote_content = @file_get_contents($website."/".$test_tmp_file)) {
            if(!@ftp_delete($conn_id, $test_tmp_file)) {
                $return = array ('status'=>'-1', 'msg' => 'System can not remove this file by FTP. ['. $test_tmp_file.'] Are you sure FTP user is properly configured? (check permision to DELETE command)');
                print json_encode($return);
                return;
            }
            if ($remote_content == $tmp_file_content ) {
                $return = array ('status'=>'1', 'ftp_dir' => $ftp_dir, 'msg' => $website."/".$test_tmp_file.' file successfully reached.');
                print json_encode($return);
                return;
            }
            else {
                $return = array ('status'=>'-1', 'msg' => $website."/".$test_tmp_file.' file successfully reached but content is different.');
                print json_encode($return);
                return;
            }
        } else {

            $msg =  'I can not reach testing file '.$website.'/'.$test_tmp_file.' via '.$website.'.<br>';
            $msg .= 'Are you sure that <b>URL to customer\'s website</b><br>';
            $msg .= 'is connected with your FTP server and <b>'.$ftp_dir.'</b> directory?<br>';
            $msg .= '<pre><u>DATA THAT YOU PROVIDED:</u><br><br>';
            $msg .= 'server/host : ' . $server_ip . "<br>";
            $msg .= 'ftp_username : ' . $ftp_username . "<br>";
            $msg .= 'ftp_password : ' . $ftp_password . "<br>";
            $msg .= 'ftp_dir : ' . $ftp_dir . "<br>";
            $msg .= 'URL to customer\'s website : ' . $website . "<br></pre>";
            $return = array ('status' => '-1', 'msg' => $msg);
            print json_encode($return);
            return;
        }

        print json_encode($return);
        ftp_close($conn_id);
        return;
    }

    // put testing file on server for:  www.domain.com
    // 1. public_html
    // 2. domain.com
    // 3. www.domain.com
    // 4. domain.com/public_html
    // 5. www.domain.com/public_html

    // try to guess the path
    // $ return is reference
    private function try_to_guess_the_web_path ($conn_id, $ftp_dir, $website, $test_tmp_file, $tmp_file_content ) {

        $path = pathinfo($website);

        if (substr($path['basename'],0,4) == 'www.') $path['basename'] = substr($path['basename'], 4);
        $possible_directories = array(
            '/',
            'public_html',
            'public_html/'.$path['basename'],
            'public_html/www.'.$path['basename'],
            $path['basename'],
            'www.'.$path['basename'],
            'www',
        );

        foreach ($possible_directories as $possible_dir) {

            @ftp_chdir ($conn_id,  '/' );

            if (@ftp_chdir ($conn_id,  $possible_dir )) {
                // check is this really web folder
                // 1. put temp file
                // 2. try to get http request for this file

                // 1. put temp file
                if (@ftp_put($conn_id, $test_tmp_file, $test_tmp_file, FTP_ASCII)) {
                    // 2. try to get http request for this file
                    if ($remote_content = @file_get_contents($website."/".$test_tmp_file)) {
                        if(!@ftp_delete($conn_id, $test_tmp_file)) { // log these information: 'System can not remove this file by FTP. ['. $test_tmp_file.'] Are you sure FTP user is properly configured? (check permision to FTP/DELETE command)');
                        }
                        if ($remote_content == $tmp_file_content ) {
                            return $possible_dir;
                        }
                    }

                } else {
                    // I was not able to put $possible_dir / $test_tmp_file on destination server.
                    // male log fot this in future
                }

            } else {
                // problem with changing - Means this dir is not exist.
            }
        }

        return false;
    }



    public function saveUserForm(UserFormRequest $request)
    {
        // check uicode and email
        $uicode = $request->input('uicode', false);
        $statuses_features = $request->input('statuses_features', 1);
        $http_referer = $request->input('http_referer', '');

        $uicode = str_replace(' ', '', $uicode);

        $counter = substr_count ( $uicode, ',') + 1;
        $in_string = "'".str_replace (',', "','", $uicode)."'";
        if ($uicode) {
            $sql_query  = "SELECT count(uicode) AS count FROM products WHERE uicode in (".$in_string.")";
            $results = DB::select($sql_query);
            if($results[0]->count != $counter ) {
                print "something wrong with uicode/s";
                exit;
            }
        }

        $date_ordered = date("Y-m-d H:i:s", round(microtime (true)));

        $uicodes = explode (',', $uicode);
        $products_list = array();

        foreach ($uicodes as $one_code ) {

            $sql_query  = "SELECT id, pname, pcode, uicode, version, txt_file_url, awlist1, grlist1, awlist2, ";
            $sql_query .= "grlist2, list1title, list1info, list2title, list2info ";
            $sql_query .= "FROM products ";
            $sql_query .= "WHERE uicode = '".$one_code."'";

            $results = DB::select($sql_query);

            $products_list[] = $results[0]->pname;

            $current_bucketname_index = myFunctions::getCurrentBucketNameIndex();
            if ($current_bucketname_index == false) {
                myFunctions::addGlobalLog(0, -1, 'CRITICAL ERROR. There is no current_bucketname_index in settings table!');
            }

            $current_bucketname = env('AWS_BUCKET'.($current_bucketname_index));
// ??? dokonczyc
            $post = array(
                'pname'                 => $results[0]->pname,
                'pcode'                 => $results[0]->pcode,
                'uicode'                => $one_code,
                'version'               => $results[0]->version,
                'contact_email'         => trim(Input::get('contact_email')),
                'first_name'            => trim(Input::get('first_name')),
                'last_name'             => trim(Input::get('last_name')),
                'ftp_host'              => trim(Input::get('ftp_host')),
                'ftp_username'          => trim(Input::get('ftp_username')),
                'ftp_password'          => trim(Input::get('ftp_password')),
                'ftp_dir'               => trim(Input::get('ftp_dir')),
                'date_ordered'          => $date_ordered,
                'txt_file_url'          => $results[0]->txt_file_url,
                'website'               => trim(Input::get('url_customers_website')),

                'paypal_email'          => trim(Input::get('paypal_email')),
                
                'paypal_api_username'   => trim(Input::get('paypal_api_username')),
                'paypal_api_password'   => trim(Input::get('paypal_api_password')),
                'paypal_api_signature'  => trim(Input::get('paypal_api_signature')),

                'customer_support_email'=> trim(Input::get('customer_support_email')),
                'clickbank_id'          => trim(Input::get('clickbank_id')),
                'jvzoo_id'              => trim(Input::get('jvzoo_id')),

                'aweber_username'       => trim(Input::get('aweber_username')),
                'aweber_password'       => trim(Input::get('aweber_password')),

                'awlist1'               => $results[0]->awlist1,
                'awlist2'               => $results[0]->awlist2,
                'grlist1'               => $results[0]->grlist1,
                'grlist2'               => $results[0]->grlist2,
                'list1title'            => $results[0]->list1title,   
                'list1info'             => $results[0]->list1info,  
                'list2title'            => $results[0]->list2title,   
                'list2info'             => $results[0]->list2info,
                'statuses_features'     => $statuses_features, // ?? to jest kluczowe ustawuienie
                'http_referer'          => $http_referer
            );

            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }       

            // SAVE TO DATABASE
            $status = DB::table('transactions')->insert(array(
                array(

                    'user_firstname'        => $post['first_name'],
                    'user_lastname'         => $post['last_name'],
                    'user_email'            => $post['contact_email'],
                    'pname'                 => $post['pname'],
                    'pcode'                 => $post['pcode'],
                    'uicode'                => $post['uicode'],
                    'version'               => $post['version'],
                    'txt_file_url'          => $post['txt_file_url'],
                    'date_ordered'          => $post['date_ordered'],
                    'ftp_host'              => $post['ftp_host'],
                    'ftp_username'          => $post['ftp_username'],
                    'ftp_password'          => $post['ftp_password'],
                    'ftp_dir'               => $post['ftp_dir'],
                    'website'               => $post['website'],
                    'date_installed'        => 'NULL',
                    'paypal_email'          => $post['paypal_email'],

                    'paypal_api_username'   => $post['paypal_api_username'],
                    'paypal_api_password'   => $post['paypal_api_password'],
                    'paypal_api_signature'  => $post['paypal_api_signature'],

                    'customer_support_email'=> $post['customer_support_email'],
                    'clickbank_id'          => $post['clickbank_id'],
                    'jvzoo_id'              => $post['jvzoo_id'],

                    'aweber_username'       => $post['aweber_username'],
                    'aweber_password'       => $post['aweber_password'],

                    'awlist1'               => $post['awlist1'],
                    'awlist2'               => $post['awlist2'],  
                    'grlist1'               => $post['grlist1'], 
                    'grlist2'               => $post['grlist2'], 
                    'list1title'            => $post['list1title'], 
                    'list1info'             => $post['list1info'], 
                    'list2title'            => $post['list2title'], 
                    'list2info'             => $post['list2info'],
                    'status'                => 'waiting',
                    // 'transaction_id' => 'qweqwe',
                    'ip'                    => $ip,
                    'http_referer'          => $post['http_referer'],
                    'statuses_features'     => $statuses_features
                )
            ));

            if ($status === false ) { print "problem with insert product into DB"; exit; }    
        }

        if ($counter > 1 ) {
            $post['letter_s'] = 's';
            $post['is_are'] = 'are';
        } else {
            $post['letter_s'] = '';
            $post['is_are'] = 'is';
        }

        // I use this to show on the screen
        $niceTableWithProvidedData = myFunctions::generateNiceTableWithProvidedData($post);
        $post = $post + array('niceTableWithProvidedData' => $niceTableWithProvidedData);

        // I use this to send email
        $simpleTableWithProvidedData = myFunctions::generateEmailTableWithProvidedData($post);
        $post = $post + array('simpleTableWithProvidedData' => $simpleTableWithProvidedData);

        $products_list = myFunctions::makeProductsList($products_list); // <ul><li> products.. etc
        $post = $post + array('products_list' => $products_list);

        $parsed_email = myFunctions::parseEmailTemplateFormSubmitted($post);
        $post = $post + array('parsed_email_template' => $parsed_email);

        // $parsed_one_product_example = $this->parseEmailTemplate($post);
        // $post = $post + array('parsed_one_product_example' => $parsed_one_product_example);

        try {
            Mail::send('emails.submission_confirmation_db', $post, function ($message) use ($post)
            {
                $message->from('supersalesmachine@gmail.com', 'Super Sales Machine')
                ->to(Input::get('contact_email'), Input::get('first_name') . ' ' .Input::get('last_name'))
                ->subject('Thank You ' . $post['first_name'] . ', Your Install Request Has Been Submitted!');
            });

        } catch(\Exception $e) {
            // TODO 1: change status in transaction table to "problem"
            // TODO 2: save log with information that email was not sent            
            $status = DB::table('log_global')->insert(array(
                array(
                    'status' => '-1',
                    'msg' => 'problem with sending submit form notification email. CONTACT_EMAIL = '.$post['contact_email'].' DATE_ORDERED = '.$post['date_ordered'].". DETAILS = ". $e
                )
            ));
        }


        $finally_view = view('appuserform_submitted')->with($post);

        return $finally_view;
    }





}