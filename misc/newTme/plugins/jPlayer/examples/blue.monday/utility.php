<?php
	class utility {
		public static function __highlight($srch,$str) {
			$srch = str_replace("%","",$srch);
			//$singular = $this->__get_singular_string($srch);
			$srch = str_replace('(','',$srch);
			$srch = str_replace(')','',$srch);
			$srch = preg_replace('/\s+/',' ',trim($srch));
			$tmp = explode(' ',trim($srch));

			//$singular = str_replace('(','',$singular);
			//$singular = str_replace(')','',$singular);
			//$singular = preg_replace('/\s+/',' ',trim($singular));
			//$tmp1 = explode(' ',trim($singular));

			$str = preg_replace('/\\\/',' ',$str);
			$str = preg_replace('/\s+/',' ',trim($str));
			$str = strip_tags($str);
			for($i=0;$i<count($tmp);$i++)
			{
				$str = preg_replace("|($tmp[$i])|Ui",'<#>$1</#>',$str);
				//$str = preg_replace("|($tmp1[$i])|Ui",'<#>$1</#>',$str);
			}
			$str = str_replace('#','b',$str);

			return $str;
		} 

		public static function curlCall($param){
                    
			# Define Return Value #
			$retVal = '';
                        
                       // $logResp    =   utility::logCurlCalls($param);
			
			# Type Of Request Call (Get/Post) #
			$method		= ((isset($param['method'])) && ($param['method']!="")) ? strtolower($param['method']) : "get";
			$formate	= ((isset($param['formate'])) && ($param['formate']!="")) ? strtolower($param['formate']) : "array";

			# Init Curl Call #
			$ch			= curl_init();

			# Set Options #
			curl_setopt($ch, CURLOPT_URL, $param['url']);
			curl_setopt($ch, CURLOPT_ENCODING, '');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($ch, CURLOPT_USERPWD, "foodadmin:500d1dm!n");
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
                        //curl_setopt($ch, CURLOPT_TIMEOUT, 120); //The maximum number of seconds to allow cURL functions to execute.
			if($method=='post'){
				curl_setopt($ch,CURLOPT_POST, TRUE);
				curl_setopt($ch,CURLOPT_POSTFIELDS,$param['postData']);
			}
			
			# Execute Call #
			$retVal		= curl_exec($ch);     
             
			# Close Request #
			curl_close($ch); unset($method);
                        
//                        if($logResp['error']['code'] == 0) {
//                            
//                            $updtResp['id']   =   $logResp['result']['id'];
//                            $updtResp['response']   =   $retVal;
//                            
//                            utility::updateAPIResponse($updtResp);
//                        }
                        
			if($formate=="array"){
				return json_decode($retVal,TRUE);
			} else {
				return $retVal;
			}
		}
                
                public function logCurlCalls($dataArr) {
                    
                    GLOBAL $db,$session;
                    $dbArr   = $db;   
                    $sessObj = $session;
                                                            
                    $dbObj  = new DB($dbArr['ocm']);
                    
                    $ip_addr=   utility::get_client_ip();
                    
                    $qry    = "INSERT INTO tbl_curl_call_logs (call_start_time,curl_call,data_arr,type,source,ip_address) VALUES (NOW(),'". addslashes(stripslashes($dataArr['url']))."','". mysql_real_escape_string(json_encode($dataArr),$dbObj->links)."','".addslashes(stripslashes($dataArr['method']))."','OCM','".addslashes(stripslashes($ip_addr))."')";
                    $res    = $dbObj->query($qry);
                    
                                
                    if($res && (mysql_affected_rows() >0)){				
                        
                        $retVal['result']['id'] =    $dbObj->lastInsertedId();
                        $errorMsg           =   "Data Inserted Successfully ";
                        $errorCode	    =    0; 
                     }
                     else if($res && (mysql_affected_rows() == 0)) {
                        $errorMsg           =    "Data Already Inserted";
                        $errorCode		=    0; 
                     }
                     else {
                        $errorMsg           =    "Query Failed ".  mysql_error();
                        $errorCode		=    1; 
                     }
                     
                     $dbObj->close();
                     
                     $retVal['error']    =   array("code"=>$errorCode,"msg"=>$errorMsg);
                     return $retVal;

                }
                
                public function updateAPIResponse($dataArr){
                    
                    if(empty($dataArr['id'])){
                        
                        $retVal['error']    =   array("code"=>"1","msg"=>"Empty ID");
                        return $retVal;
                    }
                    
                    GLOBAL $db;
                    $dbArr  = $db;                    
                                                            
                    $dbObj  = new DB($dbArr['ocm']);
                    
                    $qry    = "UPDATE tbl_curl_call_logs SET curl_response = '".  mysql_real_escape_string($dataArr['response'],$dbObj->links)."' , response_time = NOW() WHERE id = ".$dataArr['id'];
                    $res    = $dbObj->query($qry);

                    
                                
                    if($res && (mysql_affected_rows() >0)){				
                                                
                        $errorMsg           =   "Data Updated Successfully ";
                        $errorCode	    =    0; 
                     }
                     else if($res && (mysql_affected_rows() == 0)) {
                        $errorMsg           =    "Data Already Inserted";
                        $errorCode		=    0; 
                     }
                     else {
                        $errorMsg           =    "Query Failed ".  mysql_error();
                        $errorCode		=    1; 
                     }
                     
                     $dbObj->close();
                     
                     $retVal['error']    =   array("code"=>$errorCode,"msg"=>$errorMsg);
                     return $retVal;
                }

        /**
         * Prepares and sends out JSON response
         * @param object $data
         */
        public static function sendJSONResponse($data) {
            $results = array("results" => $data);
            header('Content-type: application/json');
            echo json_encode($results);
        }
		
    public static function sanitize($str) {
            if(isset($str) && $str!="") {
                $str = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $str);
                /*$str = preg_replace('/[@&-.,_)(\s+]+/',' ',$str);
                $str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
                $str = preg_replace('/\\\+/i','',$str);
                $str = preg_replace('/\s\s+/',' ',$str);*/

                $str = trim($str);
                return $str;
            }
        }
        
        public static function logs($dataArr) {
            
        }
        
        public static function ArraytoUrl($dataArr) {
            
            $url_part   =   '';
            if(is_array($dataArr)){
                foreach($dataArr as $filtr => $val){
                    $filter_arr[] =   $filtr."=".urlencode($val);
                } 

                if(is_array($filter_arr) && count($filter_arr)>0){

                  $url_part     =  "&".implode("&",$filter_arr);
                }
            }
            
            return $url_part;
        }
        
        // Function to get the client IP address
        public static function get_client_ip() {
            $ipaddress = '';
            if (getenv('HTTP_CLIENT_IP'))
                $ipaddress = getenv('HTTP_CLIENT_IP');
            else if(getenv('HTTP_X_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            else if(getenv('HTTP_X_FORWARDED'))
                $ipaddress = getenv('HTTP_X_FORWARDED');
            else if(getenv('HTTP_FORWARDED_FOR'))
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
            else if(getenv('HTTP_FORWARDED'))
               $ipaddress = getenv('HTTP_FORWARDED');
            else if(getenv('REMOTE_ADDR'))
                $ipaddress = getenv('REMOTE_ADDR');
            else
                $ipaddress = 'UNKNOWN';
            return $ipaddress;
        }
}
?>
