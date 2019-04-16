<?php
//namespace etc\tmemodel;
class CompareInfo_Model extends Model {
	public function __construct() {
		parent::__construct();
		GLOBAL $parseConf;
		$this->mongo_obj = new MongoClass();
		$this->mongo_city = ($parseConf['servicefinder']['remotecity'] == 1) ? $_SESSION['remote_city'] : $_SESSION['s_deptCity'];
	}
	
	private function getMainTabData($parentid,$data_city) {
		# FUNCTION USED TO GET MAIN TABLES DATA
		$retData	=	array();
		$curlParams = array();
		$curlParams2 = array();
		
		$curlParams['url'] = SERVICE_IP.'/contractInfo/getMainTabGeneralData/'.$parentid;
		$curlParams['formate'] = 'basic';
		$retDataGeneral	=	json_decode(Utility::curlCall($curlParams),true);
		
		$curlParams2['url'] = SERVICE_IP.'/contractInfo/getMainTabExtraData/'.$parentid;
		$curlParams2['formate'] = 'basic';
		$retDataExtra	=	json_decode(Utility::curlCall($curlParams2),true);
		
		if($retDataGeneral['errorCode']	==	0 || $retDataExtra['errorCode']	==	0) {
			$retData['data']	=	array_merge($retDataGeneral['data'],$retDataExtra['data']);
			$retData['errorCode']	=	0;
		} else {
			$retData['errorCode']	=	1;
		}
		return $retData;
	}
	
	private function getShadowTabData($parentid,$data_city) { // same function defined inside contract info model as well, its getting called from this file only
		# FUNCTION USED TO GET SHADOW TABLES DATA
		$retData	=	array();
		$curlParams = array();
		$curlParams2 = array();
		
		$curlParams['url'] = SERVICE_IP.'/contractInfo/getShadowTabGeneralData/'.$parentid;
		$curlParams['formate'] = 'basic';
		$retDataGeneral	=	json_decode(Utility::curlCall($curlParams),true);
		
		$curlParams2['url'] = SERVICE_IP.'/contractInfo/getShadowTabExtraData/'.$parentid;
		$curlParams2['formate'] = 'basic';
		$retDataExtra	=	json_decode(Utility::curlCall($curlParams2),true);
		
		if($retDataGeneral['errorCode']	==	0 || $retDataExtra['errorCode']	==	0) {
			$retData['data']	=	array_merge($retDataGeneral['data'],$retDataExtra['data']);
			$retData['errorCode']	=	0;
		} else {
			$retData['errorCode']	=	1;
		}
		return $retData;
	}
	
	private function getTempBusinessData($parentid,$data_city) {
		# FUNCTION USED TO GET TEMP BUSINESS DATA FOR CATEGORY CHANGES
		$retData	=	array();
		$curlParams = 	array();
		
		$curlParams['url'] = SERVICE_IP.'/contractInfo/tempContract/'.$parentid;
		$curlParams['formate'] = 'basic';
		$retDataTempData	=	json_decode(Utility::curlCall($curlParams),true);
		return $retDataTempData;
	}
	
	public function compareBform($parentid) {
		
		header('Content-Type: application/json');
		$params		=	json_decode(file_get_contents('php://input'),true);
		if(count($params) <=0){
			$params	=	array_merge($_GET,$_POST);
		}
		
		$data_city 	= trim($params['data_city']);
		
		# MAIN FUNCTION CALLED WHEN API GETS CALLED
		$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		
		$retArr	=	array();
		$locCompArr		=	json_decode($this->compareLocation($parentid,$mainTabData,$shadowTabData,$data_city),true);
		$contCompArr	=	json_decode($this->compareContact($parentid,$mainTabData,$shadowTabData,$data_city),true);
		$timeCompArr	=	json_decode($this->compareTiming($parentid,$mainTabData,$shadowTabData,$data_city),true);
		//$paymentCompArr	=	json_decode($this->comparePayment($parentid,$mainTabData,$shadowTabData,$data_city),true); #COMMENTED BECAUSE DC MODULE DOESN'T ENTERTAINS PAYMENT TYPE
		$categoryCompArr=	json_decode($this->compareCategory($parentid,$mainTabData,$data_city),true);
		$miscCompArr	=	json_decode($this->compareMisc($parentid,$mainTabData,$shadowTabData,$data_city),true);
		
		$retArr['data']	=	array_merge($locCompArr,$contCompArr,$timeCompArr,/*$paymentCompArr,*/ $categoryCompArr,$miscCompArr);
		
		if($locCompArr['locationComp']['errorCode']	==	2 || $contCompArr['contactComp']['errorCode'] == 2 || $timeCompArr['timingComp']['errorCode']	==	2 || $categoryCompArr['categoryComp']['errorCode']	==	2  || $miscCompArr['miscComp']['errorCode']	==	2) {
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	'Data found different in any of the form';
		} else {
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	'Everything matched';
		}
		return json_encode($retArr);
	}
	
	public function compareLocation($parentid,$mainTabData='',$shadowTabData='',$data_city) {
		# FUNCTION USED TO COMPARE LOCATION INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		if($shadowTabData	==	'') {
			$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		}
		
		$locationColArr	=	array();
		$locationColArr	=	array("companyname","building_name","street","landmark","country","state","city","area","pincode");
		
		$retData	=	array();
		if($mainTabData['errorCode']	==	0) {
			$countNotMatch	=	0;
			foreach($locationColArr as $key) {
				if((isset($mainTabData['data'][$key]) && isset($shadowTabData['data'][$key])) || ($mainTabData['data'][$key] == null || $shadowTabData['data'][$key] == null)) {
					if(strtolower($shadowTabData['data'][$key])	!=	strtolower($mainTabData['data'][$key])) {
						$retData['locationComp']['data'][ucwords($key)]['errorCode']	=	1;
						$retData['locationComp']['data'][ucwords($key)]['mainTabVal']	=	($mainTabData['data'][$key]	==	'98') ? 'India' : $mainTabData['data'][$key];
						$retData['locationComp']['data'][ucwords($key)]['newVal']	=	($shadowTabData['data'][$key] == '98') ? 'India' : $shadowTabData['data'][$key];
						$retData['locationComp']['data'][ucwords($key)]['keyName']	=	$key;
						$countNotMatch++;
					} else {
						$retData['locationComp']['data'][ucwords($key)]['errorCode']	=	0;
					}
				} else {
					$retData['locationComp']['data'][ucwords($key)]['errorCode']	=	0;
				}
			}
			if($countNotMatch > 0) {
				$retData['locationComp']['errorCode']	=	2;
				$retData['locationComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
				$retData['locationComp']['errorContain']	=	'Location Information';
			} else {
				$retData['locationComp']['errorCode']	=	0;
				$retData['locationComp']['errorStatus']	=	'Data Compared. All values are matching';
			}
		} else {
			$retData['locationComp']['errorCode']	=	1;
			$retData['locationComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		return json_encode($retData);		
	}
	
	public function compareMisc($parentid,$mainTabData='',$shadowTabData='',$data_city) {
		# FUNCTION USED TO COMPARE MISCELLANOUS INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		if($shadowTabData	==	'') {
			$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		}
		$preferedLanguage   =   json_decode($this->getPreferedLanguage($data_city),true);

		$locationColArr	=	array();
		$locationColArr	=	array("fb_prefered_language");
		
		$retData	=	array();
		if($mainTabData['errorCode']	==	0) {
			$countNotMatch	=	0;
			foreach($locationColArr as $key) {
				if((isset($mainTabData['data'][$key]) && isset($shadowTabData['data'][$key])) || ($mainTabData['data'][$key] == null || $shadowTabData['data'][$key] == null)) {
					if(strtolower($shadowTabData['data'][$key])	!=	strtolower($mainTabData['data'][$key])) {
						$retData['miscComp']['data'][ucwords($key)]['errorCode']	=	1;
						$retData['miscComp']['data'][ucwords($key)]['mainTabVal']	=	$mainTabData['data'][$key];	
						$retData['miscComp']['data'][ucwords($key)]['mainTabValDisplay']	=	$preferedLanguage[$mainTabData['data'][$key]];	
						$retData['miscComp']['data'][ucwords($key)]['newVal']		=	$shadowTabData['data'][$key];
						$retData['miscComp']['data'][ucwords($key)]['newValDisplay']		=	$preferedLanguage[$shadowTabData['data'][$key]];
						$retData['miscComp']['data'][ucwords($key)]['keyName']		=	$key;						
						$countNotMatch++;
					} else {
						$retData['miscComp']['data'][ucwords($key)]['errorCode']	=	0;
					}
				} else {
					$retData['miscComp']['data'][ucwords($key)]['errorCode']	=	0;
				}
			}
			if($countNotMatch > 0) {
				$retData['miscComp']['errorCode']	=	2;
				$retData['miscComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
				$retData['miscComp']['errorContain']	=	'Miscellaneous Information';
			} else {
				$retData['miscComp']['errorCode']	=	0;
				$retData['miscComp']['errorStatus']	=	'Data Compared. All values are matching';
			}
		} else {
			$retData['miscComp']['errorCode']	=	1;
			$retData['miscComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		return json_encode($retData);		
	}
	
	public function getPreferedLanguage($data_city) {
		$dbObjIro		=	new DB($this->db['db_iro']);
		$languageQry	=   "SELECT * FROM tbl_language_master WHERE active_flag = '1'";
		$conLanguage    =   $dbObjIro->query($languageQry);
		$num		    =	$dbObjIro->numRows($conLanguage);
        $responseArr	=	array();
        if ($num > 0 ) {
			while ($resLanguage = $dbObjIro->fetchData($conLanguage)) {
				$responseArr[$resLanguage['language_id']]	=	$resLanguage['language'];
			}
        }
        return json_encode($responseArr);
    }
	
	public function compareContact($parentid,$mainTabData='',$shadowTabData='',$data_city) {
		# FUNCTION USED TO COMPARE CONTACT INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		if($shadowTabData	==	'') {
			$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		}
		
		$contactColArr	=	array();
		$contactColArr	=	array("contact_person","landline","mobile","email","website","fax","tollfree");
		
		$retData	=	array();
		if($mainTabData['errorCode']	==	0) {
			$countNotMatch	=	0;
			foreach($contactColArr as $key) {
				if((isset($mainTabData['data'][$key]) && isset($shadowTabData['data'][$key])) || ($mainTabData['data'][$key] == null || $shadowTabData['data'][$key] == null)) { #NOT ABLE TO CHECK NULL VALUES WITH ISSET
					$retData['contactComp']['countComp'][$key]	=	0;
					$retData['contactComp']['diffVals'][$key]['shadTab']	=	'';
					$retData['contactComp']['diffVals'][$key]['mainTab']	=	'';
					
					$expArrShad	=	explode(',',trim($shadowTabData['data'][$key]));
					$expArrMain	=	explode(',',trim($mainTabData['data'][$key]));
					
					$diffArr	=	array();
					$diffArrTemp	=	array();
					
					$diffArrTemp	=	count(array_diff($expArrShad,$expArrMain));
					$diffArr	=	array_merge(array_diff($expArrShad,$expArrMain),array_diff($expArrMain,$expArrShad));
					
					if(count($diffArr) > 0) {
						$retData['contactComp']['countComp'][$key]++;
						$k=0;
						foreach($diffArr as $value) {
							if($k	<	$diffArrTemp) {
								$retData['contactComp']['diffVals'][$key]['shadTab']	.=	$value.',';
							} else {
								$retData['contactComp']['diffVals'][$key]['mainTab']	.=	$value.',';
							}
							$k++;
						}
					}
					
					if($retData['contactComp']['countComp'][$key] > 0) {
						$retData['contactComp']['data'][$key]['errorCode']	=	1;
						$retData['contactComp']['data'][$key]['mainTabVal']	=	$mainTabData['data'][$key];
						$retData['contactComp']['data'][$key]['newVal']	=	$shadowTabData['data'][$key];
						$retData['contactComp']['data'][$key]['keyName']	=	$key;
						$countNotMatch++;
					} else {
						$retData['contactComp']['data'][$key]['errorCode']	=	0;
					}
				} else {
					$retData['contactComp']['data'][$key]['errorCode']	=	0;
				}
			}
			if($countNotMatch > 0) {
				$retData['contactComp']['errorCode']	=	2;
				$retData['contactComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
				$retData['contactComp']['errorContain']	=	'Contact Information';
			} else {
				$retData['contactComp']['errorCode']	=	0;
				$retData['contactComp']['errorStatus']	=	'Data Compared. All values are matching';
			}
		} else {
			$retData['contactComp']['errorCode']	=	1;
			$retData['contactComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		
		return json_encode($retData);
	}
	
	public function compareTiming($parentid,$mainTabData='',$shadowTabData='',$data_city) {
		# FUNCTION USED TO COMPARE TIMING INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		if($shadowTabData	==	'') {
			$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		}
		
		$timingColArr	=	array();
		$timingColArr	=	array("working_time_start","working_time_end");
		
		if($mainTabData['errorCode']	==	0) {
			$countNotMatch	=	0;
			foreach($timingColArr as $key) {
				if((isset($mainTabData['data'][$key]) && isset($shadowTabData['data'][$key])) || ($mainTabData['data'][$key] == null || $shadowTabData['data'][$key] == null)) {
					$timeShadStr	=	'';
					$subStrTimeShad	=	'';
					if($shadowTabData['data'][$key] != null && $shadowTabData['data'][$key] != '') {
						$expShadowTabData	=	explode(',',$shadowTabData['data'][$key]);
						foreach($expShadowTabData as $valsTime) {
							$valsTimeExp	=	explode('-',$valsTime);
							$timeShadStr	.=	$valsTimeExp[0]/*.((!isset($valsTimeExp[1]) || $valsTimeExp[1] == '')  ? '' : '-'.$valsTimeExp[1])*/.','; # COMMENTED EVENING TIME IN, BECAUSE IT IS NOT INCLUDED IN DC MODULE
						}
						$subStrTimeShad	=	substr($timeShadStr,0,-1);
					}
					$timeMainStr	=	'';
					$subStrTimeMain	=	'';
					if($mainTabData['data'][$key] != null && $mainTabData['data'][$key] != '') {
						$expMainTabData	=	explode(',',$mainTabData['data'][$key]);
						foreach($expMainTabData as $valsTime) {
							$valsTimeExp	=	explode('-',$valsTime);
							$timeMainStr	.=	$valsTimeExp[0]/*.((!isset($valsTimeExp[1]) || $valsTimeExp[1] == '')  ? '' : '-'.$valsTimeExp[1])*/.','; # COMMENTED EVENING TIME OUT BECAUSE IT IS NOT INCLUDED IN DC MODULE
						}
						$subStrTimeMain	=	substr($timeMainStr,0,-1);
					}
					if($key	==	'working_time_start') {
						$valArrKey	=	'Hours of Operation Start Time';
					} else {
						$valArrKey	=	'Hours of Operation End Time';
					}
					if(trim($subStrTimeShad,',')	!=	trim($subStrTimeMain,',')) {
						$retData['timingComp']['data'][$valArrKey]['errorCode']	=	1;
						$retData['timingComp']['data'][$valArrKey]['mainTabVal']	=	$subStrTimeMain;
						$retData['timingComp']['data'][$valArrKey]['newVal']	=	$subStrTimeShad;
						$retData['timingComp']['data'][$valArrKey]['keyName']	=	$key;
						$countNotMatch++;
					} else {
						$retData['timingComp']['data'][$valArrKey]['errorCode']	=	0;
					}
				}
				if($countNotMatch > 0) {
					$retData['timingComp']['errorCode']	=	2;
					$retData['timingComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
					$retData['timingComp']['errorContain']	=	'Timing Information';
				} else {
					$retData['timingComp']['errorCode']	=	0;
					$retData['timingComp']['errorStatus']	=	'Data Compared. All values are matching';
				}
			}
		} else {
			$retData['timingComp']['errorCode']	=	1;
			$retData['timingComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		return json_encode($retData);
	}
	
	public function comparePayment($parentid,$mainTabData='',$shadowTabData='',$data_city) {
		# FUNCTION USED TO COMPARE PAYMENT INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		if($shadowTabData	==	'') {
			$shadowTabData	=	$this->getShadowTabData($parentid,$data_city);
		}
		if($mainTabData['errorCode']	==	0) {
			$countNotMatch	=	0;
			$retData	=	array();
			$retData['countPayment']['payment_type']	=	0;
			if(isset($mainTabData['data']['payment_type']) && isset($shadowTabData['data']['payment_type'])) {
				$expArrShad	=	explode('~~',$shadowTabData['data']['payment_type']);
				$expArrMain	=	explode('~',$mainTabData['data']['payment_type']);
				$diffArr	=	array();
				$diffArr	=	array_merge(array_diff($expArrShad,$expArrMain),array_diff($expArrMain,$expArrShad));
				if(count($diffArr) > 0) {
					foreach($diffArr as $value) {
						if($value != '') {
							$retData['countPayment']['payment_type']++;
						}
					}
				}
				if($retData['countPayment']['payment_type'] > 0) {
					$retData['paymentComp']['data']['Payment Type']['errorCode']	=	1;
					$retData['paymentComp']['data']['Payment Type']['mainTabVal']	=	$mainTabData['data']['payment_type'];
					$retData['paymentComp']['data']['Payment Type']['newVal']	=	$shadowTabData['data']['payment_type'];
					$retData['paymentComp']['data']['Payment Type']['keyName']	=	'payment_type';
					$countNotMatch++;
				} else {
					$retData['paymentComp']['data']['Payment Type']['errorCode']	=	0;
				}
			}
			if($countNotMatch > 0) {
				$retData['paymentComp']['errorCode']	=	2;
				$retData['paymentComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
				$retData['paymentComp']['errorContain']	=	'Payment Options';
			} else {
				$retData['paymentComp']['errorCode']	=	0;
				$retData['paymentComp']['errorStatus']	=	'Data Compared. All values are matching';
			}
		} else {
			$retData['paymentComp']['errorCode']	=	1;
			$retData['paymentComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		return json_encode($retData);
	}
	
	public function compareCategory($parentid,$mainTabData='',$data_city) {
		# FUNCTION USED TO COMPARE CATEGORY INFORMATION
		$params	=	array_merge($_GET,$_POST);
		if($mainTabData	==	'') {
			$mainTabData	=	$this->getMainTabData($parentid,$data_city);
		}
		
		$retData	=	array();
		$tempDataArr	=	$this->getTempBusinessData($parentid,$data_city);
		$countNotMatch	=	0;
		if($mainTabData['errorCode']	==	0) {
			if(((isset($tempDataArr['data']) && (isset($tempDataArr['data']['catIds']))) && ((isset($mainTabData['data'])) && (isset($mainTabData['data']['catidlineage'])))) || ($tempDataArr['data']['catIds']	==	null || $mainTabData['data']['catidlineage']	==	null)) {
				$expTempCatidArr	=	explode('|P|',$tempDataArr['data']['catIds']);
				$expMainCatidArr	=	explode(',',str_replace('/','',$mainTabData['data']['catidlineage']));
				$diffArr	=	array();
				$diffArr	=	array_merge(array_diff($expTempCatidArr,$expMainCatidArr),array_diff($expMainCatidArr,$expTempCatidArr));
				if(count($diffArr) > 0) {
					foreach($diffArr as $value) {
						if($value != '') {
							$countNotMatch++;
						}
					}
					
					$catIdMain	=	'';
					if($mainTabData['data']['catidlineage'] != '' && $mainTabData['data']['catidlineage'] != null) {
						$paramArr	=	array();
						$paramArr['catParam']	=	str_replace('/','',$mainTabData['data']['catidlineage']);
						$paramArr['catFlag']	=	2;
						$paramArr['data_city']	=	$data_city;
						$curlParams = array();
						$curlParams['url'] = SERVICE_IP.'/categoryInfo/getCategoryInfo';
						$curlParams['formate'] = 'basic';
						$curlParams['method'] = 'post';
						$curlParams['headerJson'] = 'json';
						$curlParams['postData'] = json_encode($paramArr); 
						$categoryInfoMain	=	json_decode(Utility::curlCall($curlParams),true);
						if($categoryInfoMain['errorCode']	==	0) {
							foreach($categoryInfoMain['data'] as $key=>$value) {
								$catIdMain	.=	$value['category_name'].',';
							}
						}
					}
					
					$retData['categoryComp']['data']['Category']['errorCode']	=	1;
					$retData['categoryComp']['data']['Category']['mainTabValCatid']	=	$mainTabData['data']['catidlineage'];
					$retData['categoryComp']['data']['Category']['newValCatid']		=	($tempDataArr['count'] > 0 ? substr(str_replace('|P|','/,/',$tempDataArr['data']['catIds']),2) : '');
					$retData['categoryComp']['data']['Category']['mainTabVal']	=	trim($catIdMain,',');
					$retData['categoryComp']['data']['Category']['newVal']	=	($tempDataArr['count'] > 0 ? ltrim(str_replace('|P|',',',$tempDataArr['data']['categories']),',') : '');
					$retData['categoryComp']['data']['Category']['keyName']	=	'catidlineage';
				}
			}
			if($countNotMatch > 0) {
				$retData['categoryComp']['errorCode']	=	2;
				$retData['categoryComp']['errorStatus']	=	'Data Compared. Some values are not matching.';
				$retData['categoryComp']['errorContain']	=	'Category Information';
			} else {
				$retData['categoryComp']['errorCode']	=	0;
				$retData['categoryComp']['errorStatus']	=	'Data Compared. All values are matching';
			}
		} else {
			$retData['categoryComp']['errorCode']	=	1;
			$retData['categoryComp']['errorStatus']	=	'Data Not Compared. No data in main Tables';
		}
		return json_encode($retData);
	}
	
	public function insertLogBformDC() {
		# FUNCTION USED TO SEND FOR DATA CORRECTION AND SAVING LOGS
		if(isset($_POST['parentid'])) {
			$params	=	$_POST;
		}else{
			header('Content-Type: application/json');
			$params		=	json_decode(file_get_contents('php://input'),true);
		}
		$data_city = trim($params['data_city']);
		//echo '<pre>'; print_r($params);
		$dbObjLocal	=	new DB($this->db['db_local']);		
		$dbObj_datacorrection	=	new DB($this->db['db_data_correction']);
		$drpTab	=	"drop table tbl_bform_dc_logs";
		//$condrptab	=	$dbObj_datacorrection->query($drpTab);
		//echo 'here in comparemodel--------<pre>'; print_r($params); 
		$crTab	=	"CREATE TABLE IF NOT EXISTS d_jds.tbl_bform_dc_logs (parentid varchar(100) not null, bformChanges text not null, bformChangesSel text not null, empcode varchar(20) not null, added_on datetime default '0000-00-00 00:00:00', sent_for_dc tinyint(1) default 0, which_flow tinyint(1) default 0, KEY idx_parid(parentid), KEY idx_empcode(empcode), KEY idx_added_on(added_on))";
		$conTab			=	$dbObj_datacorrection->query($crTab);
		$mainTabData	=	$this->getMainTabData($params['parentid'],$data_city); 	//echo 'main tab data...<pre>'; print_r($mainTabData); 
		
		$shadowTabData	=	$this->getShadowTabData($params['parentid'],$data_city); 	//echo 'shadow table data...<pre>'; print_r($shadowTabData); //die;
		$flagCheck	=	0;
		
		// If shadow table data city is blank	
		if(empty($shadowTabData['data']['data_city']) || $shadowTabData['data']['data_city']==''){
			$sql_dc = "SELECT data_city FROM db_iro.tbl_id_generator WHERE parentid = '".$params['parentid']."'";				
			$res_dc	=	$dbObjLocal->query($sql_dc);			
			if($res_dc && mysql_num_rows($res_dc)>0){
				$row_dc = mysql_fetch_assoc($res_dc);
				$shadowTabData['data']['data_city'] = $row_dc['data_city'];
			}
		}// End
		if(empty($params['empcode']))
			$params['empcode'] = $shadowTabData['data']['updatedBy'];
		########################### KEEPING LAST MOD DATE ########################################
		$last_mod 	= 	"INSERT INTO tbl_me_tme_sink SET parentid = '".$params['parentid']."',	empId	 = '".$params['empcode']."',
		mod_flag = 0,	approval_flag = 0,allocationType='".$params['disposeVal']."'  ON DUPLICATE KEY UPDATE
		parentid = '".$params['parentid']."',	empId	 = '".$params['empcode']."',	mod_flag = 0,	approval_flag = 0, allocationType='".$params['disposeVal']."'";
		$conLastMod	=	$dbObjLocal->query($last_mod);
		########################### KEEPING LAST MOD DATE ########################################
		
		if($params['flag']	==	1) { 
			if($mainTabData['errorCode'] == 0) {
				$changedJSON	=	json_encode($params['diffData']);
				$changedJSONSel	=	json_encode($params['checkedData']);
				$sendArr	=	array();
				$sendArr['mandatory_field']['sphinx_id']	=	$mainTabData['data']['sphinx_id'];
				$sendArr['mandatory_field']['parentid']		=	$mainTabData['data']['parentid'];
				$sendArr['mandatory_field']['data_city']	=	$shadowTabData['data']['data_city'];
				$sendArr['mandatory_field']['stdcode']		=	$shadowTabData['data']['stdcode'];
				$sendArr['mandatory_field']['module_type']	=	'TME';
				$sendArr['mandatory_field']['updatedBy']	=	$shadowTabData['data']['updatedBy'];
				$sendArr['mandatory_field']['updatedOn']	=	$shadowTabData['data']['updatedOn'];
				$sendArr['mandatory_field']['paid']			=	$mainTabData['data']['paid'];
				$sendArr['mandatory_field']['freeze']		=	$mainTabData['data']['freeze'];
				$sendArr['mandatory_field']['mask']			=	$mainTabData['data']['mask'];
				$sendArr['mandatory_field']['companyname']	=	(isset($params['checkedData']['companyname'])) ? addslashes(stripslashes($shadowTabData['data']['companyname'])) : addslashes(stripslashes($mainTabData['data']['companyname']));
				$sendArr['mandatory_field']['pincode']	=	(isset($params['checkedData']['pincode'])) ? $shadowTabData['data']['pincode'] : $mainTabData['data']['pincode'];
				$sendArr['mandatory_field']['mobile']	=	(isset($params['checkedData']['mobile'])) ? $shadowTabData['data']['mobile'] : ($mainTabData['data']['mobile'] == '') ? $shadowTabData['data']['mobile'] : $mainTabData['data']['mobile'];
				$sendArr['mandatory_field']['landline']	=	(isset($params['checkedData']['landline'])) ? $shadowTabData['data']['landline'] : ($mainTabData['data']['landline'] == '') ? $shadowTabData['data']['landline'] : $mainTabData['data']['landline'];
				$sendArr['mandatory_field']['modified_date']	=	date('Y-m-d H:i:s');
				$new_val	=	'';
				foreach($params['checkedData'] as $key=>$value) {
					$sendArr['changed_field'][$key]['original']	=	addslashes(stripslashes($value['oldVal']));
					$sendArr['changed_field'][$key]['user']		=	addslashes(stripslashes($value['newVal']));
					if($key=="catidlineage"){
						$sendArr['changed_field'][$key]['original']	=	addslashes(stripslashes($value['newVal']));
						$sendArr['changed_field'][$key]['user']	=	addslashes(stripslashes($value['oldVal']));
					}
				}
				$extraFieldArr	=	array();
				$extraFieldArr	=	array('sphinx_id','freeze','mask','paid','companyname','state','city','pincode','area','subarea','building_name','street','landmark','latitude','longitude','geocode_accuracy_level','tollfree','mobile','landline','fax','email','website','mobile_display','email_display','catidlineage','contact_person','working_time_start','working_time_end','mobile_addinfo','landline_display','landline_addinfo','contact_person_addinfo','tollfree_addinfo','email_feedback','mobile_feedback','landline_feedback','turnover','payment_type','year_establishment','accreditations','certificates','no_employee','statement_flag','data_city','stdcode','fb_prefered_language');
				
				if(MONGOUSER==1)
				{
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $params['parentid'];
					$mongo_inputs['data_city'] 	= SERVER_CITY;
					$mongo_inputs['module']		= 'tme';
					$mongo_inputs['table'] 		= "tbl_business_temp_data";
					$mongo_inputs['fields'] 	= "catIds";
					$busi_catlineage = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$busi_cat_lineage		=	"SELECT catIds FROM tme_jds.tbl_business_temp_data where contractid='".$params['parentid']."'";
					$con_busi_cat_lineage	=	$dbObjLocal->query($busi_cat_lineage);
					$busi_catlineage		=	$dbObjLocal->fetchData($con_busi_cat_lineage);
				}
				$busi_cat_lineage	=	str_replace("|P|","/,/",$busi_catlineage['catIds']);
				foreach($extraFieldArr as $valueExtraArr) {
					$sendArr['extra_field'][$valueExtraArr]['original']	=	$mainTabData['data'][$valueExtraArr];
					$sendArr['extra_field'][$valueExtraArr]['user']		=	$shadowTabData['data'][$valueExtraArr];
					if($valueExtraArr=="catidlineage")
						//$sendArr['extra_field'][$valueExtraArr]['original']	=	$mainTabData['data'][$valueExtraArr];
						//$new_val_exp	=	explode(",",$new_val);
						//$newval_fin	=	'';
						//foreach($new_val_exp as $key1=>$val1)
							//$newval_fin	.= '/'.$val1.'/,';
						$sendArr['extra_field'][$valueExtraArr]['user']	=	trim(trim(str_replace("|P|",",",$busi_catlineage['catIds']),"|P|"),',');
					//}*/
				}
			} else {
				$sendArr	=	array();
				$changedJSONSel	=	'';
				$changedJSON	=	'';
			}
			$curlParams	=	array();
			//echo 'send_Arr----<pre>';  print_r($sendArr); 
			//echo $mainTabData['errorCode']; die;
			if($mainTabData['errorCode'] == 0) {
				
				$sendArr_paid	=	array();
				$sendArr_paid['parentid'] 	=	$mainTabData['data']['parentid'];
				$sendArr_paid['data_city'] 	=	$mainTabData['data']['data_city'];
				$sendArr_paid['rquest'] 	=	'get_contract_type';				

				$curlParams_p	=	array();
				$curlParams_p['url']			= 	JDBOX_API.'services/contract_type.php';
				$curlParams_p['formate'] 		= 	'basic';
				$curlParams_p['headerJson'] 	= 	'json';
				$curlParams_p['postData'] 		= 	json_encode($sendArr_paid);
				$curlParams_p['method'] 		= 	'post';
				$retData_p						=	json_decode(Utility::curlCall($curlParams_p),true);
				
				//if($mainTabData['data']['paid'] == 1)
				if($retData_p['result']['paid'] == 1)
				{
					//$curlParams['url'] = 'http://neelamrasal.jdsoftware.com/tmegenio_live/business/includes/dump_into_data_correction_api.php';
					$curlParams['url']			= 	DECS_TME.'/business/includes/dump_into_data_correction_api.php';
					$curlParams['formate'] 		= 	'basic';
					$curlParams['headerJson'] 	= 	'json';
					$curlParams['postData'] 	= 	json_encode($sendArr);
					$curlParams['method'] 		= 	'post';
					$retData					=	json_decode(Utility::curlCall($curlParams),true);
					$whichFlow	=	'1';
				} else {
					$mod_type = (!empty($params['mod_type'])) ? $params['mod_type'] : "TME";				
					$arr_dc_api_param = array("parentid"=>$params['parentid'],"mod_type"=>$mod_type,"data_city"=>$shadowTabData['data']['data_city'],"userid"=>$params['empcode'],"edited_date"=>date("Y-m-d H:i:s"), "dc_array"=>$sendArr);
					 
					//$curlParams['url'] = 'http://snehasanghadia.jdsoftware.com/tmelive/api_dc/datacorrection_api.php';
					//$curlParams['url'] = DECS_TME.'/api_dc/datacorrection_api.php';
					$curlParams['url'] = DC_URL.'/api_dc/datacorrection_api.php';
					$curlParams['formate'] 		= 	'basic';
					$curlParams['headerJson'] 	= 	'json';
					$curlParams['postData'] 	= 	json_encode($arr_dc_api_param);
					$curlParams['method'] 		= 	'post';
					$retData					=	json_decode(Utility::curlCall($curlParams),true);
					//print_r($curlParams);
					//print_r($retData);
					//echo $retData;
					//die;
					$whichFlow	=	'0';
				}
			} else {
				$mod_type = (!empty($params['mod_type'])) ? $params['mod_type'] : "TME";
				$arr_dc_api_param = array("parentid"=>$params['parentid'],"mod_type"=>$mod_type,"data_city"=>$shadowTabData['data']['data_city'],"userid"=>$params['empcode'],"edited_date"=>date("Y-m-d H:i:s"));
				//$curlParams['url'] = 'http://snehasanghadia.jdsoftware.com/tmelive/api_dc/datacorrection_api.php';
				$curlParams['url'] = DC_URL.'/api_dc/datacorrection_api.php';
				//$curlParams['url'] = DECS_TME.'/api_dc/datacorrection_api.php';
								
				$curlParams['formate'] 		= 	'basic';
				$curlParams['headerJson'] 	= 	'json';
				$curlParams['postData'] 	= 	json_encode($arr_dc_api_param);
				$curlParams['method'] 		= 	'post';
				$retData					=	json_decode(Utility::curlCall($curlParams),true);
				$whichFlow	=	'0';
				//print_r($curlParams);
				//print_r($retData);
				//echo $retData;
				//die;
			}
			if($retData['erroCode']	==	1) {
				$flagCheck	=	1;
			} else {
				$flagCheck	=	2;
			}
		} else {
			$mod_type = (!empty($params['mod_type'])) ? $params['mod_type'] : "TME";
			$arr_dc_api_param = array("parentid"=>$params['parentid'],"mod_type"=>$mod_type,"data_city"=>$shadowTabData['data']['data_city'],"userid"=>$params['empcode'],"edited_date"=>date("Y-m-d H:i:s"));
			//$curlParams['url'] = 'http://snehasanghadia.jdsoftware.com/tmelive/api_dc/datacorrection_api.php';
			$curlParams['url'] = DC_URL.'/api_dc/datacorrection_api.php';
			//$curlParams['url'] = DECS_TME.'/api_dc/datacorrection_api.php';
			
			$curlParams['formate'] 		= 	'basic';
			$curlParams['headerJson'] 	= 	'json';
			$curlParams['postData'] 	= 	json_encode($arr_dc_api_param);
			$curlParams['method'] 		= 	'post';
			$retData					=	json_decode(Utility::curlCall($curlParams),true);
			$whichFlow	=	'0';
			//print_r($curlParams);
			//print_r($retData);
			//echo $retData;
			//die;
			$changedJSON	=	json_encode($params['diffData']);
			$changedJSONSel	=	'';
		}
		$insertLog	=	"INSERT INTO d_jds.tbl_bform_dc_logs (parentid,bformChanges,bformChangesSel,empcode,added_on,sent_for_dc,which_flow) VALUES ('".$params['parentid']."','".addslashes(stripslashes($changedJSON))."','".addslashes(stripslashes($changedJSONSel))."','".$params['empcode']."','".date('Y-m-d H:i:s')."','".$flagCheck."','".$whichFlow."')"; 
		$conInsLog	=	$dbObj_datacorrection->query($insertLog);
		return json_encode(Utility::insert_return($conInsLog));
	}
}
