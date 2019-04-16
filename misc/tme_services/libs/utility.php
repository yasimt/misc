<?php

class Utility{
	protected $db;
    public static function curlCall($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";
        
        $timeout = ((isset($param['timeout'])) && ($param['timeout'] >0 )) ? $param['timeout'] : 300;

        # Init Curl Call #
        $ch = curl_init();

        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($param['postData']))                                                                       
				); 
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			}
		}
        $retVal = curl_exec($ch);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }
	public static function curlCall2($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";

        # Init Curl Call #
        $ch = curl_init();

        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				if(isset($param['auth_token']) && $param['auth_token']!= ''){
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']),
						'HR-API-AUTH-TOKEN:'.$param['auth_token'])); 
				}else{
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
						'Content-Type: application/json',                                                                                
						'Content-Length: ' . strlen($param['postData']))); 
				}
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			}
		}
        $retVal = curl_exec($ch);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }
// MRK
  public static function update_return($con) {
        if ($con) {
            $errorMsg = "data Updated successfully";
            $errorCode = "0";
            $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
            $response = array("results" => $resArr);
        } else {
            $errorMsg = "data Updation fail";
            $errorCode = "1";
            $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
            $response = array("results" => $resArr);
        }
        return $response;
    }

    public static function insert_return($con) {
        if ($con) {
            $errorMsg = "data inserted successfully";
            $errorCode = "0";
            $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
            $response = array("results" => $resArr);
        } else {
            $errorMsg = "data insertion fail";
            $errorCode = "1";
            $resArr = array("errorMsg" => $errorMsg, "errorCode" => $errorCode);
            $response = array("results" => $resArr);
        }
        return $response;
    }
    
    public static function insert_update_fail_logs($query,$part) {
		
	}
	
	public static function get_std_code($city,$data_city,$db){
		$cityname	=	"";
		$STDCode	=	"";
		
		$retArr	=	array();
		GLOBAL $parseConf;
		if($parseConf['servicefinder']['remotecity'] == 0){ // main city module
			$cityname	= strtoupper($city);
			$STDcodesql 	= "SELECT stdcode FROM city_master WHERE ct_name='".$city."' AND DE_display =1 and display_flag=1 LIMIT 1";
			$dbObjLocal  	= new DB($db['db_local']);
			$STDcoderes  	= $dbObjLocal->query($STDcodesql);
			if($STDcoderes and $dbObjLocal->numRows($STDcoderes) > 0){
				$STDcodearr =	$dbObjLocal->fetchData($STDcoderes); 
				$STDCode 	= 	$STDcodearr['stdcode'];		
				$retArr['data']	=	$STDCode;
				$retArr['errorCode']	=	0;	
				$retArr['errorStatus']	=	'Data Found';	
			} else {
				$retArr['errorCode']	=	1;	
				$retArr['errorStatus']	=	'Data Not Found';	
			}	
		} else {	// remote city module
			$cityarg= strtolower($data_city);
			
			$remotearray = array('agra', 'alappuzha', 'allahabad', 'amritsar', 'bhavnagar', 'bhopal', 'bhubaneshwar', 'chandigarh', 'coimbatore', 'cuttack', 'dharwad', 'ernakulam', 'goa', 'hubli', 'indore', 'jaipur', 'jalandhar', 'jamnagar', 'jamshedpur', 'jodhpur', 'kanpur', 'kolhapur', 'kozhikode', 'lucknow', 'ludhiana', 'madurai', 'mangalore', 'mysore', 'nagpur', 'nashik', 'patna', 'pondicherry', 'rajkot', 'ranchi', 'salem', 'shimla', 'surat', 'thiruvananthapuram', 'tirunelveli', 'trichy', 'udupi', 'vadodara', 'varanasi', 'vijayawada', 'vizag', 'visakhapatnam');
			
			if(in_array($cityarg,$remotearray)){
				$STDcodesql = "SELECT stdcode FROM city_master WHERE ct_name='".$cityarg."' AND DE_display =1 and display_flag=1 LIMIT 1";
				$STDcoderes  	= $dbObjLocal->query($STDcodesql);
				
				if($STDcoderes and $dbObjLocal->numRows($STDcoderes)>0) {
					$STDcodearr = $dbObjLocal->fetchData($STDcoderes);
					$STDCode 	= $STDcodearr['stdcode'];
					$retArr['data']	=	$STDCode;
					$retArr['errorCode']	=	0;	
					$retArr['errorStatus']	=	'Data Found';	
				} else {
					$retArr['errorCode']	=	1;	
					$retArr['errorStatus']	=	'Data Not Found';	
				}
			}else{
				// non famous remote city module
				$retArr['data']	=	'9999';
				$retArr['errorCode']	=	0;	
				$retArr['errorStatus']	=	'Data Found';	
			}	
		}
		return json_encode($retArr);
	}
	

    public static function week_mon_fri_date() {
        $d = new DateTime();
        $weekday = $d->format('w');
        $diff = 7 + ($weekday == 0 ? 6 : $weekday - 1); // Monday=0, Sunday=6
        $d->modify("-$diff day");
        $mon_date = $d->format('Y-m-d');
        $d->modify('+6 day');
        $friday_date = $d->format('Y-m-d');
        $arr_ret_date['monday'] = $mon_date;
        $arr_ret_date['sunday'] = $friday_date;

        return $arr_ret_date;
    }

    public static function encode5t($str) {
        for ($i = 0; $i < 5; $i++) {
            $str = strrev(base64_encode($str)); //apply base64 first and then reverse the string
        }
        return $str;
    }

    public function decode5t($str) {
        for ($i = 0; $i < 5; $i++) {
            $str = base64_decode(strrev($str)); //apply base64 first and then reverse the string}
        }
        return $str;
    }
	
	public static function encrypt_data($text){
		$key = "SADFo92jzVnzSj39IUYGvi6eL8v6RvJH8Cytuiouh547vCytdyUFl76R";

		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		$enc = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $text, MCRYPT_MODE_ECB, $iv);

		$iv = rawurlencode(base64_encode($iv));
		$enc = rawurlencode(base64_encode($enc));
		$mcryptParams	=	array();
		$mcryptParams['iv']	=	$iv;
		$mcryptParams['enc']	=	$enc;
		return $mcryptParams;
	}

	public static function decrypt_data($text,$iv){
		$key = "SADFo92jzVnzSj39IUYGvi6eL8v6RvJH8Cytuiouh547vCytdyUFl76R";

		$iv = base64_decode(rawurldecode($iv));
		$enc = base64_decode(rawurldecode($text));

		$decrypted_text = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $enc, MCRYPT_MODE_ECB, $iv);
		return $decrypted_text;
	}
	
	public function connectDB() {
		require "../config/database.php";
		require "../config/config.php";
		return $this->db = $db;
	}
}

?>
