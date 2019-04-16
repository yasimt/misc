<?
class bform_validation_class extends DB {
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	public function __construct($params){
		$parentid 	= trim($params['parentid']);
		$data_city 	= trim($params['data_city']);
		$module 	= trim($params['module']);
		$city 		= trim($params['city']);		
		$ucode 		= trim($params['ucode']);
		$nonpaid 	= trim($params['nonpaid']);
		
		if($parentid==''){
			$message = "Parentid is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
		}
		if($data_city==''){
			$message = "data_city is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
		}
		if($module==''){
			$message = "module is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
		}
		$this->parentid = $parentid;
		$this->module 	= strtolower($module);
		$this->city 	= $city;
		$this->nonpaid	= $nonpaid;
		$this->data_city = strtolower($data_city);
		
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();	// Initiate Database connection
		switch($this->module)
		{
			case 'tme':	$this->tmeCode = $ucode;
						break;
						
			case 'me':	$this->meCode = $ucode;
						break;
			default:		
					$message = "Invalid module.";
					echo json_encode($this->sendDieMsg($message,1));
					die();					
				
		}
		$this->ucode = $ucode;
	}
	private function setServers()
	{	
		GLOBAL $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		
		$this->remote_flag = ((in_array(strtolower($this->data_city), $this->dataservers)) ? 0 : 1);	
	}
	public function checkFields(){		
			$genaralinfoArr = array();
			$extradetailsArr = array();
			$blankArr 	= array();
			$erroMsg 	= array();
			
			if(($this->module == 'me' || $this->module == 'tme')){
				$mongo_inputs 					= array();
				$mongo_inputs['module']       	= $this->module;
				$mongo_inputs['parentid']       = $this->parentid;
				$mongo_inputs['data_city']      = $this->data_city;
				$mongo_inputs['table']          = json_encode(array(
					"tbl_companymaster_generalinfo_shadow"=>"parentid,companyname,country,state,city,data_city,pincode,latitude,longitude,stdcode,website,landline,mobile",
					"tbl_companymaster_extradetails_shadow"=>"flags,landline_addinfo,mobile_addinfo,companyname,data_city"
				));
				$shadow_arr = array();
				if(!empty($this->parentid)){
					$shadow_arr = $this->mongo_obj->getShadowData($mongo_inputs);
					//echo "<pre>";print_r($shadow_arr);		
				}
				$genaralinfoArr 	= $shadow_arr['tbl_companymaster_generalinfo_shadow'];
				$extradetailsArr 	= $shadow_arr['tbl_companymaster_extradetails_shadow'];				
			}
			
			//echo "<pre>";print_r($genaralinfoArr);
			//echo "<pre>";print_r($extradetailsArr);
			
			if(is_array($genaralinfoArr) && is_array($extradetailsArr))
			{
				$blankArr = array_merge($genaralinfoArr,$extradetailsArr);
				$blankArr['Excompanyname'] 	= 	$extradetailsArr['companyname'];		
				$blankArr['ExDatacity'] 	= 	$extradetailsArr['data_city'];
			}
			
			//echo "<pre>";print_r($blankArr);
			
			if(count($blankArr)>0){
				if((array_key_exists('companyname', $blankArr) && $blankArr['companyname']=='')|| (array_key_exists('Excompanyname', $blankArr) && $blankArr['Excompanyname']=='')){
					$erroMsg['error']['message'][]=  "Company name not found";
				}
				if(array_key_exists('country', $blankArr) && $blankArr['country']==''){
					$erroMsg['error']['message'][]=  "Country name not found";
				}
				if(array_key_exists('state', $blankArr) && $blankArr['state']==''){
					$erroMsg['error']['message'][]=  "State name not found";
				}
				
				if(array_key_exists('data_city', $blankArr) && $blankArr['data_city']==''){
					$erroMsg['error']['message'][]=  "Data City is blank in General Info Table";
				}
				
				if(array_key_exists('ExDatacity', $blankArr) && $blankArr['ExDatacity']==''){
					$erroMsg['error']['message'][]=  "Data City is blank in Extra Details Table";
				}
				
				if($this->nonpaid!=1){
					
					if(array_key_exists('city', $blankArr) && $blankArr['city']==''){
						$erroMsg['error']['message'][]=  "City not found";
					}
					if(array_key_exists('pincode', $blankArr) && $blankArr['pincode']==''){
						$erroMsg['error']['message'][]=  "Pincode not found";
					}
					if(array_key_exists('latitude', $blankArr) && ($blankArr['latitude']=='' || intval($blankArr['latitude'])==0) && $this->module!='tme'){
						$erroMsg['error']['message'][]=  "Latitude not found";
					}
					if(array_key_exists('longitude', $blankArr) && ($blankArr['longitude']=='' || intval($blankArr['longitude'])==0) && $this->module!='tme'){
						$erroMsg['error']['message'][]=  "Longitude not found";
					}
					if(array_key_exists('stdcode', $blankArr) && $blankArr['stdcode']==''){
						//$erroMsg['error']['message'][]=  "Stdcode not found";
					}					
				}
				
				if(array_key_exists('pincode', $blankArr) && $blankArr['pincode']!='' && (strtolower($this->module)=='tme' ||  strtolower($this->module)=='me') ){
						$sqlQryIdgenerator = "select data_city from tbl_id_generator  where parentid = '".$this->parentid."'";
						$sqlResIdgenerator = parent::execQuery($sqlQryIdgenerator,$this->conn_iro);
						if($sqlResIdgenerator && parent::numRows($sqlResIdgenerator)>0){
							$sqlRowIdgenerator = parent::fetchData($sqlResIdgenerator);
							$this->idgenartorDatacity = $sqlRowIdgenerator['data_city'];
						}//&& !='') && (array_key_exists('ExDatacity', $blankArr) && $blankArr['ExDatacity']==''))
						if(array_key_exists('data_city', $blankArr)){
							$this->GeneralinfoDatacity = $blankArr['data_city'];
						}
						if(array_key_exists('ExDatacity', $blankArr)){
							$this->extradetailDatacity = $blankArr['ExDatacity'];
						}
						if(array_key_exists('city', $blankArr)){
							$this->generalInfoCity = $blankArr['city'];
						}//echo $GeneralinfoDatacity."==".$extradetailDatacity."==".$generalInfoCity."==".$idgenartorDatacity;
						$datacityDiffr = false;
						if((!empty($this->GeneralinfoDatacity)) && (strtolower($this->GeneralinfoDatacity) != strtolower($this->idgenartorDatacity))){
								$erroMsg['error']['message'][] = "Datacity is different in general info[".$this->GeneralinfoDatacity."] and in id_generator[".$this->idgenartorDatacity."].";
								$datacityDiffr = true;
							}elseif((!empty($this->extradetailDatacity)) && (strtolower($this->extradetailDatacity) != strtolower($this->idgenartorDatacity))){
								$erroMsg['error']['message'][] = "Datacity is different in extra details [".$this->extradetailDatacity."] and in id_generator[".$this->idgenartorDatacity."].";
								$datacityDiffr = true;
						}
						if($this->remote_flag ==1){
							if(!empty($this->generalInfoCity)){
								$citymismatch = $this->getDataCityInfo();
								if($citymismatch){
									$erroMsg['error']['message'][] = "Datacity and city are different.";
									$datacityDiffr = true;
								}
							}
						}
						if($datacityDiffr ==true){
							$this->datacityErroMsgLog();
						}
					}

			}
			else{
				$erroMsg['error']['code'] = 1;
				$erroMsg['error']['message'][] 	  = "No data found in shadow table";
				return $erroMsg;			
			}
			
			if(count($erroMsg['error']['message'])>0){
				$erroMsg['error']['code'] = 1;	
			}
			else{
				$erroMsg['error']['code'] = 0;
				$erroMsg['error']['message'][] 	  = 'Valid data';
			}
			//~ $erroMsg['error']['code'] = 1;
			//~ $erroMsg['error']['message'][] 	  = "Datacity are different";
			//~ $erroMsg['error']['message'][] 	  = "Datacity are 33";
		unset($blankArr);
		unset($genaralinfoArr);
		unset($extradetailsArr);
		return $erroMsg;
	}
	private function getDataCityInfo(){
		$mismatch_flag = 0;
		$sqlDataCityInfo = "SELECT data_city FROM  d_jds.tbl_city_master WHERE cityname ='".$this->generalInfoCity."' AND display_flag = 1 LIMIT 1";
		$resDataCityInfo = parent::execQuery($sqlDataCityInfo,$this->conn_iro);
		if($resDataCityInfo && parent::numRows($resDataCityInfo)>0){
			$row_data_city 		= parent::fetchData($resDataCityInfo);
			$exact_data_city 	= trim($row_data_city['data_city']);
			if(strtolower($exact_data_city) != strtolower($this->idgenartorDatacity)){
				$mismatch_flag = 1;
			}
		}
		return $mismatch_flag;
	}
	private function datacityErroMsgLog()
	{
		$dt = date('Y-m-d H:i:s');
		$sqlInsertLog = "insert into d_jds.tbl_datacity_api_log (parentid,generalinfo_data_city,extradetail_data_city,ganeralinfo_city,idgenertor_data_city,module_nm,tmeCode,meCode,insertdate) values ('".$this->parentid."','".$this->GeneralinfoDatacity."','".$this->extradetailDatacity."','".$this->generalInfoCity."','".$this->idgenartorDatacity."','".$this->module."','".$this->tmeCode."','".$this->meCode."','".$dt."')";
		$sqlResInsertLog = parent::execQuery($sqlInsertLog,$this->conn_iro);
	}
	private function sendDieMsg($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}	
}
?>
