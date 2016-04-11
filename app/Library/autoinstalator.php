<?php
namespace App\library
{
	use DB;
    use App\library\myFunctions;

    class autoinstalator {

        private $aReturn = array();
        private $item = null;
        private $piz_filepath = null;
        private $zip_filepath = null;

    	public function __CONSTRUCT($item)
    	{
            if (isset($item->id) == false) {
                return false;
            }
            $this->item = $item;

            $tmp = pathinfo($this->item->piz_file);
            $this->piz_filepath = "../products_local_storage/". $tmp['filename'].'.'.$tmp['extension'];

            $tmp = pathinfo($this->item->zip_file);
            $this->zip_filepath = "../products_local_storage/". $tmp['filename'].'.'.$tmp['extension'];

            $this->test_local_copy_of_piz_product();
            $this->test_local_copy_of_zip_product();
        }




        public function putPizFileByFtp()
        {

            $conn_id = @ftp_connect($this->item->ftp_host);

            if ($conn_id) {
                myFunctions::addRecordToLogInstallations(1, 'connect in to FTP '.$this->item->ftp_host.'- SUCCESS.', $this->item->id);
            }
            else {
                myFunctions::addRecordToLogInstallations(-1, 'There was a problem with connect to '.$this->item->ftp_host.' FTP server. '.$this->item->ftp_host.'- OK.', $this->item->id);
                return false;
            }

            $login_result = @ftp_login($conn_id, $this->item->ftp_username, $this->item->ftp_password);
            {
                if ($login_result) {
                    myFunctions::addRecordToLogInstallations(1, 'log in to FTP - OK.', $this->item->id);
                }
                else {
                    myFunctions::addRecordToLogInstallations(-1, 'There was a problem with login or password in FTP server. Login:'. $this->item->ftp_username .' Password:' . $this->item->ftp_password, $this->item->id);
                    return false;
                }
            }

            // turn passive mode on
            ftp_pasv($conn_id, true);

            // change FTP directory

            if (@ftp_chdir ($conn_id, $this->item->ftp_dir )) {
                myFunctions::addRecordToLogInstallations(1, 'FTP directory changed successfully. '.$this->item->ftp_dir, $this->item->id);
            } else {
                myFunctions::addRecordToLogInstallations(-1, 'There was a problem with changing directory on FTP. '.$this->item->ftp_dir, $this->item->id);
                return false;
            }

            $pathResult = pathinfo($this->piz_filepath);

            if (ftp_put($conn_id, $pathResult['basename'], $this->piz_filepath, FTP_BINARY)) {
                $msg = $pathResult['basename'].' successfully uploaded';
                myFunctions::addRecordToLogInstallations(1, $msg, $this->item->id);
            } else {
                $msg  = 'There is a problem with uploading '. $pathResult['basename'].' to FTP server.';
                $msg .= ' server_ip = '. $this->item->ftp_host;
                $msg .= ' ftp_username = '.$this->item->ftp_username;
                $msg .= ' ftp_password = '.$this->item->ftp_password;
                $msg .= ' ftp_dir = '. $this->item->ftp_dir;
                myFunctions::addRecordToLogInstallations(-1, $msg, $this->item->id);
                ftp_close($conn_id);
                return false;
            }
            ftp_close($conn_id);
            return true;
        }





        // 1. test local copy of piz product
        private function test_local_copy_of_piz_product() {
            if (file_exists($this->piz_filepath)) {
                //myFunctions::addRecordToLogInstallations(1, 'piz local file exist', $this->item->id);
                return true;
            } else {
                $this->make_piz_file();
            }
        }

        private function test_local_copy_of_zip_product() {
            if (file_exists($this->zip_filepath)) {
                //myFunctions::addRecordToLogInstallations(1, 'zip local file exist', $this->item->id);
                return true;
            } else {
                $this->download_zip_file();
            }
        }

        private function make_piz_file() {
            if (file_exists($this->zip_filepath)) {
                $this->revert_zip();
            } else {
                $this->download_zip_file();
                $this->revert_zip();
            }
        }

        private function revert_zip() {
            $tmp = file_get_contents($this->zip_filepath);
            $reversed = strrev($tmp); 
            $tmp = file_put_contents($this->piz_filepath, $reversed);
            myFunctions::addRecordToLogInstallations(1, 'making piz file from zip: DONE', $this->item->id);
        }


        private function download_zip_file() {
            $zip_file = file_get_contents($this->item->zip_file);
            if ($zip_file === false) {
                myFunctions::addRecordToLogInstallations(1, 'download zip file: FAILED', $this->item->id);
                return false;
            }
            $tmp = file_put_contents($this->zip_filepath, $zip_file);
            if ($tmp === false) {
                myFunctions::addRecordToLogInstallations(1, 'download zip file: FAILED', $this->item->id);
                return false;
            }

            myFunctions::addRecordToLogInstallations(1, 'download zip file: DONE', $this->item->id);
            return true;
        }

	}
}
?>