<?php

ini_set("memory_limit", "-1");
class onlinesignupclass extends DB
{
	var  $conn_default  = null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $configobj		= null;
	
	function __construct($params)
	{
		
		$this->params		= $params;
		$this->parentid     = trim($params['parentid']);
		$this->data_city    = trim($params['data_city']);
		$this->trace	    = trim($params['trace']);
		$this->pass_transid = trim($params['trans_id']);
		
		$this->configclassobj = new configclass();
		
		
		
		
		$this->setCommonInfo();
		
		$this->sms_email_Obj = new email_sms_send($db,$params['data_city']);
		
		$this->setServers();
		
		
		//echo '<pre>';print_r($this->db);die;
		
	}
	// Function to set Common Information
	function setCommonInfo()
	{
		$this->configobj = new configclass();		
		
	}
	
	function setServers()
	{	global $db;
		
		$data_city = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');		
		
		$this->conn_idc 	= $db[strtolower($data_city)]['idc']['master'];
		$this->conn_log 	= $db['db_log'];//reference to 17.103 server
		
		$this->fin			= $db[strtolower($data_city)]['fin']['master'];
		
		#echo " -- fin master---"; print_r($db[strtolower($data_city)]['fin']['master']);
		
	}
	
	
	function verifyKey()
	{
		$sqlgetup="select * from online_regis1.tbl_genio_apis_access where access_to='".$this->params['api_called_by']."'";
		$res_acc =parent::execQuery($sqlgetup, $this->conn_idc); 
		$secretkey='';
		if($res_acc && mysql_num_rows($res_acc) > 0){
				
				$row_acc = mysql_fetch_assoc($res_acc);
				$secretkey=$row_acc['secret_key'];				
		}
		
		
		$api_key = hash_hmac('sha256', trim($this->params['api_called_by']),(date('Y-m-d') . $secretkey ));
		
		if(DEBUG_MODE)
		{

			echo '<pre> sql :: '.$sqlgetup;
			
			if($_SERVER['REMOTE_ADDR']=='172.29.87.117')
			{
				echo "<br>api_key--".$api_key."<br>";
			}
		
			
		}
		
		if( $api_key != $this->params['key'] || ( trim($this->params['key'])==null || trim($this->params['api_called_by'])==null ) )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Invalid key ";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
	}
	
	
	// Function to generate parentid
	function generateParentid($data_city,$source,$remote_flag){
		
		for($i = 0; $i < 3; $i++){	//Random String Generator
			 $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
			 $iTotal = count($aChars) - 1;
			 $iIndex = rand(0, $iTotal);
			 $sCode .= $aChars[$iIndex];
			 $sCode .= chr(rand(49, 57));
		}
		$stdcode = "XXXX";
		if($data_city){
			$sql = "SELECT stdcode FROM online_regis1.city_master WHERE ct_name = '".$data_city."' and stdcode!='' LIMIT 1";
			$res = parent::execQuery($sql, $this->conn_idc);
			if($res && mysql_num_rows($res)){
				$row = mysql_fetch_assoc($res);
				$stdcode = $row['stdcode'];
			}
		}
		$stdcode = substr($stdcode,1);
		$stdcode = str_pad($stdcode,4,"X",STR_PAD_LEFT);

		if($stdcode=="XXXX"){
			echo '<h1>STD code for city '.$data_city.' found to be blank.</h1>';
			echo "<h2>Please contact to software team immediately</h2>";
			die;
		}

		$stdcode_destination_component = $stdcode; // 4 digit
    	$time_component = substr(date("YmdHis",time()),2); // 12 digit
		$random_number_component = substr($sCode,2); // 4 digit

		$cCode = $stdcode_destination_component.".".$stdcode_destination_component.".".$time_component.".".$random_number_component; //24 + 3 = 27 digits
		/*Genrating Sphinx id*/
		
		
		if($cCode){
			if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
				
				$this->urldetails	=	$this->configclassobj->get_url(urldecode($this->params['data_city']));
				
				$cs_app_url = $this->urldetails['url'];
			}else{
				$cs_app_url = "http://prameshjha.jdsoftware.com/csgenio/";
			}
			$PCode="P".$cCode;
			$url=$cs_app_url."api_services/api_idgeneration.php?source=".$source."&rquest=idgenerator&module=CS&datacity=".urlencode($data_city)."&parentid=".$PCode."&rflag=".$remote_flag;
			$strNewsphinxId = json_decode($this->MakeGetCurlCall($url),true);
		}
		/*--------------------*/
		
		if(DEBUG_MODE)
		{
			echo "<br>cCode-- ".$cCode;
			echo "<br>".$url;
		}
		
		$this->centraliselogging(array(),' cs api_services api_idgeneration.php ',$this->params['action'],$url,$strNewsphinxId);
		
		return ('P'.$cCode);
	}
	
	function createNewContract()
	{
		$post_arr = array();
		
		$post_arr['parentid'] 	 	= $this->params['parentid'];
		$post_arr['companyname'] 	= $this->params['callername'];
		$post_arr['contact_person'] = $this->params['callername'];
		$post_arr['mobile'] 	 	= trim($this->params['callermobile']);
		$post_arr['city'] 	 	 	= $this->params['city'];
		$post_arr['data_city'] 	 	= $this->params['data_city'];
		$post_arr['source'] 	 	= 'onlineSignup';
		
		
		$maincityarry= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		
		if( in_array($this->params['data_city'],$maincityarry))
		{
			$post_arr['is_remote'] = 'REMOTE';			
		}
		
		
		
		$this->urldetails	=	$this->configclassobj->get_url(urldecode($this->params['data_city']));
		
		$jdbox_ip_url	=	$this->urldetails['jdbox_url'];
		//print_r($urldetails);
			
		// Calling new API to insert into new table for Nonpaid contracts
		if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$curl_url_insert 	= $jdbox_ip_url."insert_api.php";
		}
		else
		{
			//$curl_url 	= "http://". $_SERVER['HTTP_HOST']."/jdbox/insert_api.php";
			$curl_url_insert 	= "http://prameshjha.jdsoftware.com/jdbox/insert_api.php";
		}
		
		if($this->trace) 
		echo '<br><br> curl url :: '.$curl_url_insert;
		//die('heree');
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url_insert);
		curl_setopt($ch, CURLOPT_POST      ,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS ,$post_arr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$resmsg_insert = curl_exec($ch);		
		
		$this->centraliselogging($post_arr,'jdbox insert_api.php call ',$this->params['action'],$curl_url_insert,$resmsg_insert);
		
	}
	
	function signupinitiationmobile()
	{
		$result = array();
		
		if( trim($this->params['callermobile'])==null )  
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "callermobile is missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if( trim($this->params['trans_id'])==null )  
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "trans_id is missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		$source='onlineSignup';
		
		$maincityarry= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		
		if( in_array(strtolower($this->params['data_city']),$maincityarry))
		{
			$remote_flag=0;
			
		}else
		{
			$remote_flag=1;
		}
		
		# parentid has been generated not we have to create contract 
		$this->params['parentid'] = $this->generateParentid($this->params['data_city'],$source,$remote_flag);
		
		$this->centraliselogging($this->params,'API Params',$this->params['action']);
		
		$this->params['companyname'] = $this->params['callername'];		
		$this->createNewContract();
		


		$insert_sql= "INSERT INTO online_regis1.onlineSignup set
						parentid			='".$this->params['parentid']."',
						trans_id			='".$this->params['trans_id']."',						
						companyname			='".addslashes(stripslashes($this->params['companyname']))."',
						city				='".addslashes(stripslashes($this->params['city']))."',
						data_city			='".addslashes(stripslashes($this->params['data_city']))."',
						callername			='".addslashes(stripslashes($this->params['callername']))."',
						callermobile		='".addslashes(stripslashes($this->params['callermobile']))."',						
						api_called_by		='".addslashes(stripslashes($this->params['api_called_by']))."',
						api_called_on		='".date('Y-m-d H:i:s')."',						
						action				= '".addslashes(stripslashes($this->params['action']))."',
						source				='".addslashes(stripslashes($this->params['source']))."',
						payment_id			='".addslashes(stripslashes($this->params['payment_id']))."',
						payment_type		='".addslashes(stripslashes($this->params['payment_type']))."',
						paymode				='".addslashes(stripslashes($this->params['paymode']))."',
						payment_done_flag	='".addslashes(stripslashes($this->params['payment_done_flag']))."',
						payment_done_on		='".addslashes(stripslashes($this->params['payment_done_on']))."',
						requested_date		='".addslashes(stripslashes($this->params['requested_date']))."',
						bank_ref_no			='".addslashes(stripslashes($this->params['bank_ref_no']))."',
						amount_paid			='".addslashes(stripslashes($this->params['amount_paid']))."',
						tax					='".addslashes(stripslashes($this->params['tax']))."',
						selPGMode			='".addslashes(stripslashes($this->params['selPGMode']))."',
						user_code			='".addslashes(stripslashes($this->params['user_code']))."',
						user_name			='".addslashes(stripslashes($this->params['user_name']))."',
						campaign			='".addslashes(stripslashes($this->params['campaign']))."',
						campaigninfo 		= '".addslashes(stripslashes($this->params['campaigninfo']))."',
						department 			= '".addslashes(stripslashes($this->params['department']))."',
						sent_source 		= '".addslashes(stripslashes($this->params['sent_source']))."',
						emp_city 			= '".addslashes(stripslashes($this->params['emp_city']))."'	
						
						ON DUPLICATE KEY UPDATE						
						
						companyname			='".addslashes(stripslashes($this->params['companyname']))."',
						city				='".addslashes(stripslashes($this->params['city']))."',
						data_city			='".addslashes(stripslashes($this->params['data_city']))."',
						callername			='".addslashes(stripslashes($this->params['callername']))."',						
						api_called_by		='".addslashes(stripslashes($this->params['api_called_by']))."',
						api_called_on		='".date('Y-m-d H:i:s')."',						
						action				= '".addslashes(stripslashes($this->params['action']))."',
						source				='".addslashes(stripslashes($this->params['source']))."',
						payment_id			='".addslashes(stripslashes($this->params['payment_id']))."',
						payment_type		='".addslashes(stripslashes($this->params['payment_type']))."',
						paymode				='".addslashes(stripslashes($this->params['paymode']))."',
						payment_done_flag	='".addslashes(stripslashes($this->params['payment_done_flag']))."',
						payment_done_on		='".addslashes(stripslashes($this->params['payment_done_on']))."',
						requested_date		='".addslashes(stripslashes($this->params['requested_date']))."',
						bank_ref_no			='".addslashes(stripslashes($this->params['bank_ref_no']))."',
						amount_paid			='".addslashes(stripslashes($this->params['amount_paid']))."',
						tax					='".addslashes(stripslashes($this->params['tax']))."',
						selPGMode			='".addslashes(stripslashes($this->params['selPGMode']))."',
						user_code			='".addslashes(stripslashes($this->params['user_code']))."',
						user_name			='".addslashes(stripslashes($this->params['user_name']))."',
						campaign			='".addslashes(stripslashes($this->params['campaign']))."',
						campaigninfo 		= '".addslashes(stripslashes($this->params['campaigninfo']))."',
						department 			= '".addslashes(stripslashes($this->params['department']))."',
						sent_source 		= '".addslashes(stripslashes($this->params['sent_source']))."',
						emp_city 			= '".addslashes(stripslashes($this->params['emp_city']))."'	";

					
					$res_ins = parent::execQuery($insert_sql, $this->conn_idc);
					
					if (DEBUG_MODE) 
					{
						echo '<pre> insert_sql :: '.$insert_sql;							
						echo '<br><b>Error:</b>' . $this->mysql_error;
					}
			
					
			$result['error']['code'] = 0;
			$result['result'] = "data updated sucessfully";
			
			if (DEBUG_MODE) 
			{
				echo "<pre> return array "; print_r($result);
			}
			return $result;
			
	}
	
	function signupinitiation()
	{
		$result = array();
		
		$this->centraliselogging($this->params,'API Params',$this->params['action']);
		
		if( ( trim($this->params['parentid'])==null ) &&  ( trim($this->params['mobile'])==null )  )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Parentid / mobile is missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if( trim($this->params['trans_id'])==null )  
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "trans_id is missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}			
		
		

		$insert_sql= "INSERT INTO online_regis1.onlineSignup set
						parentid			='".$this->params['parentid']."',
						trans_id			='".$this->params['trans_id']."',						
						companyname			='".addslashes(stripslashes($this->params['companyname']))."',
						city				='".addslashes(stripslashes($this->params['city']))."',
						data_city			='".addslashes(stripslashes($this->params['data_city']))."',
						callername			='".addslashes(stripslashes($this->params['callername']))."',
						callermobile		='".addslashes(stripslashes($this->params['callermobile']))."',						
						api_called_by		='".addslashes(stripslashes($this->params['api_called_by']))."',
						api_called_on		='".date('Y-m-d H:i:s')."',						
						action				= '".addslashes(stripslashes($this->params['action']))."',
						source				='".addslashes(stripslashes($this->params['source']))."',
						payment_id			='".addslashes(stripslashes($this->params['payment_id']))."',
						payment_type		='".addslashes(stripslashes($this->params['payment_type']))."',
						paymode				='".addslashes(stripslashes($this->params['paymode']))."',
						payment_done_flag	='".addslashes(stripslashes($this->params['payment_done_flag']))."',
						payment_done_on		='".addslashes(stripslashes($this->params['payment_done_on']))."',
						requested_date		='".addslashes(stripslashes($this->params['requested_date']))."',
						bank_ref_no			='".addslashes(stripslashes($this->params['bank_ref_no']))."',
						amount_paid			='".addslashes(stripslashes($this->params['amount_paid']))."',
						tax					='".addslashes(stripslashes($this->params['tax']))."',
						selPGMode			='".addslashes(stripslashes($this->params['selPGMode']))."',
						user_code			='".addslashes(stripslashes($this->params['user_code']))."',
						user_name			='".addslashes(stripslashes($this->params['user_name']))."',
						campaign			='".addslashes(stripslashes($this->params['campaign']))."',
						campaigninfo 		= '".addslashes(stripslashes($this->params['campaigninfo']))."',
						department 			= '".addslashes(stripslashes($this->params['department']))."',
						sent_source 		= '".addslashes(stripslashes($this->params['sent_source']))."',
						emp_city 			= '".addslashes(stripslashes($this->params['emp_city']))."'
						
						
						ON DUPLICATE KEY UPDATE						
						
						companyname			='".addslashes(stripslashes($this->params['companyname']))."',
						city				='".addslashes(stripslashes($this->params['city']))."',
						data_city			='".addslashes(stripslashes($this->params['data_city']))."',
						callername			='".addslashes(stripslashes($this->params['callername']))."',						
						api_called_by		='".addslashes(stripslashes($this->params['api_called_by']))."',
						api_called_on		='".date('Y-m-d H:i:s')."',						
						action				= '".addslashes(stripslashes($this->params['action']))."',
						source				='".addslashes(stripslashes($this->params['source']))."',
						payment_id			='".addslashes(stripslashes($this->params['payment_id']))."',
						payment_type		='".addslashes(stripslashes($this->params['payment_type']))."',
						paymode				='".addslashes(stripslashes($this->params['paymode']))."',
						payment_done_flag	='".addslashes(stripslashes($this->params['payment_done_flag']))."',
						payment_done_on		='".addslashes(stripslashes($this->params['payment_done_on']))."',
						requested_date		='".addslashes(stripslashes($this->params['requested_date']))."',
						bank_ref_no			='".addslashes(stripslashes($this->params['bank_ref_no']))."',
						amount_paid			='".addslashes(stripslashes($this->params['amount_paid']))."',
						tax					='".addslashes(stripslashes($this->params['tax']))."',
						selPGMode			='".addslashes(stripslashes($this->params['selPGMode']))."',
						user_code			='".addslashes(stripslashes($this->params['user_code']))."',
						user_name			='".addslashes(stripslashes($this->params['user_name']))."',
						campaign			='".addslashes(stripslashes($this->params['campaign']))."',
						campaigninfo 		= '".addslashes(stripslashes($this->params['campaigninfo']))."',
						department 			= '".addslashes(stripslashes($this->params['department']))."',
						sent_source 		= '".addslashes(stripslashes($this->params['sent_source']))."',
						emp_city 			= '".addslashes(stripslashes($this->params['emp_city']))."'	";

					
					$res_ins = parent::execQuery($insert_sql, $this->conn_idc);
					
					if (DEBUG_MODE) 
					{
						echo '<pre> insert_sql :: '.$insert_sql;							
						echo '<br><b>Error:</b>' . $this->mysql_error;
					}
			
			
			#$this->callselfSignupAPI($this->params);
					
			$result['error']['code'] = 0;
			$result['result'] = "data updated sucessfully";
			
			if (DEBUG_MODE) 
			{
				echo "<pre> return array "; print_r($result);
			}
			return $result;
			
	}

	function callselfSignupAPI($selfSignupAPIparams)
	{
		$selfSignupParamsarr = $this->prepareParamsforselfSignupAPI($selfSignupAPIparams);
		
		$curlobj = new CurlClass();	
		
		$urldetails= $this->configclassobj->get_url(urldecode($this->data_city));

		$curlurl=$urldetails['jdbox_service_url'].'insSelfSignUp.php';
		
		$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
		$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
		#$output = $curlobj->post($curlurl,$selfSignupParamsarr);
		#$output = $curlobj->post($curlurl,json_encode($selfSignupParamsarr),1);
		$output = $curlobj->get($curlurl,$selfSignupParamsarr);
		$output_arr= json_decode($output,true);
		
		if(DEBUG_MODE) 
		{
			echo "<br>curlurl".$curlurl;print_r($selfSignupParamsarr);
			echo '<br>output_arr -- '; print_r($output_arr);
			echo '<br>output_arr-- ';
		}
		
		$this->centraliselogging($selfSignupParamsarr,'insSelfSignUp.php call ',$this->params['action'],$curlurl,$output);
		
		return $output_arr;
		
		
	}	

	function prepareParamsforselfSignupAPI($ssAPIparams)
	{
		$selfSignupAPIParams= array();
		
		$selfSignupAPIParams['parentid']			= $ssAPIparams['parentid'];
		$selfSignupAPIParams['companyname']			= $ssAPIparams['companyname'] ;
		$selfSignupAPIParams['data_city']			= $ssAPIparams['data_city'];
		$selfSignupAPIParams['campaigninfo'] 		= $ssAPIparams['campaigninfo'];
		$selfSignupAPIParams['source']				= $ssAPIparams['source'];
		$selfSignupAPIParams['payment_done_flag']	= $ssAPIparams['payment_done_flag'];
		$selfSignupAPIParams['payment_done_on'] 	= $ssAPIparams['payment_done_on'];
		$selfSignupAPIParams['requested_date'] 		= $ssAPIparams['requested_date'];
		$selfSignupAPIParams['trans_id'] 			= $ssAPIparams['trans_id'];
		$selfSignupAPIParams['payment_type'] 		= $ssAPIparams['payment_type'];
		$selfSignupAPIParams['paymode']		 		= $ssAPIparams['paymode'];
		$selfSignupAPIParams['amount_paid'] 		= $ssAPIparams['amount_paid'];		
		$selfSignupAPIParams['payment_id'] 			= $ssAPIparams['payment_id'];
		$selfSignupAPIParams['bank_ref_no'] 		= $ssAPIparams['bank_ref_no'];
		$selfSignupAPIParams['tax'] 				= $ssAPIparams['tax'];
		$selfSignupAPIParams['selPGMode'] 			= $ssAPIparams['selPGMode'];		
		$selfSignupAPIParams['user_code']			= $ssAPIparams['user_code'];
		
		if(trim($selfSignupAPIParams['user_code'])==null)
		{
			$selfSignupAPIParams['user_code']='6665656565665';
		}
		
		$selfSignupAPIParams['user_name']			= $ssAPIparams['user_name'];
		
		
		if(trim($selfSignupAPIParams['user_name'])==null)
		{
			$selfSignupAPIParams['user_name']='online_self_signup';
		}
		
		#only tme and me are expected in api so passing module only as tme/me, for JDA we have to pass me
		if(strtolower(trim($ssAPIparams['department']))=='tme')
		{
			$selfSignupAPIParams['module']	= 'tme';
		
		}elseif(strtolower(trim($ssAPIparams['department']))=='jda' || strtolower(trim($ssAPIparams['department']))=='me')
		{
			$selfSignupAPIParams['module']	= 'me';
		}
		
				
		#as per Raj we have to keep version handling as it is, as raj make necessary handling for version we will remove this 
		if(! isset($ssAPIparams['version']) || $ssAPIparams['version'] == null)
		{
			$versionval = $this->getNewVersion();
			$selfSignupAPIParams['version']= $versionval;
		}
		
		$selfSignupAPIParams['balance']				= 0;		
		
		
		return $selfSignupAPIParams;
	}
	
	function getNewVersion()
	{
		#$sql="SELECT ifnull( MAX(version*1) +10 ,13) AS newversion FROM payment_apportioning WHERE parentid='".$this->parentid."' AND (version%10)='3'";
		
		$sql="SELECT ifnull( MAX(version*1) +10 ,11) AS newversion FROM payment_apportioning WHERE parentid='".$this->parentid."' AND (version%10)='1'";
		$res= parent::execQuery($sql,$this->fin);
		$row = mysql_fetch_assoc($res);
		
		if(DEBUG_MODE) 
		{
			echo "<br>sql".$sql;
			echo '<br>row -- '; print_r($row);
			echo '<br>$row newversion'.$row['newversion'];
		}
		
		return $row['newversion'];
	}
	
	function gettrans_id()
	{
        $dataArr['length']=15;
        $dataArr['prefix'] = '';
        
		$length  =  empty($dataArr['length']) ? 6 : $dataArr['length'];
		$prefix  =  empty($dataArr['prefix']) ? '' : $dataArr['prefix'];
                        
        //generate a random id encrypt it and store it in $rnd_id 
		$rnd_id = crypt(uniqid(rand(),1)); 

		//to remove any slashes that might have come 
		$rnd_id = strip_tags(stripslashes($rnd_id)); 

		//Removing any . or / and reversing the string 
		$rnd_id = str_replace(".","",$rnd_id); 
		$rnd_id = strrev(str_replace("/","",$rnd_id)); 

		//finally take the first required length characters from the $rnd_id 
		$rnd_id = $prefix.substr($rnd_id,0,$length); 
                
        return $rnd_id;
    
	}
	
	function genioDealClosePendingStatus()
	{		
		
		
		if( trim($this->params['parentid'])==null )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "parentid missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if( trim($this->params['data_city'])==null )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "data_city missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
				
		$result      = array();
		
		$sql = "SELECT parentid FROM online_regis1.onlineSignup WHERE parentid='".$this->params['parentid']."' AND data_city='".urldecode($this->params['data_city'])."' AND genio_dealclose_flag=0";	$res   = parent::execQuery($sql, $this->conn_idc);
		
		if($res && parent::numRows($res)>0){			
			$result['error']['code'] = 0;
			$result['status']  = 'pending';			
		}else
		{
			$result['error']['code'] = 1;
			$result['status']  = 'not pending';			
		}
		
		if (DEBUG_MODE) 
		{
			echo '<pre> sql :: '.$sql;
			echo '<pre> $res :: '.$res;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			
			echo '<pre> $result :: '; print_r($result);
			
		}
		
		return $result;
	}
	
	function updategeniodealcloseflag($parentid,$datacity,$trans_id,$genio_dealclose_by)
	{
		$sql = "UPDATE online_regis1.onlineSignup SET
				genio_dealclose_flag =1,
				genio_dealclose_by	 = '".$genio_dealclose_by."',
				genio_dealclose_on	 = '".date('Y-m-d H:i:s')."'
				
				where parentid='".$parentid."' and data_city='".addslashes($datacity)."' and trans_id='".$trans_id."'";
		$res = parent::execQuery($sql, $this->conn_idc);
		
		if (DEBUG_MODE) 
		{
			echo '<pre> sql :: '.$sql;
			echo '<pre> $res :: '.$res;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
	}
	
	function geniodealclose()
	{
		$this->centraliselogging($this->params,'API Params',$this->params['action']);
		
		if( trim($this->params['parentid'])==null )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "parentid missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if( trim($this->params['data_city'])==null )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "data_city missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		if( trim($this->params['genio_dealclose_by'])==null )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "genio_dealclose_by missing";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		
		$sql = "select * from online_regis1.onlineSignup where parentid='".$this->params['parentid']."' and data_city='".urldecode($this->params['data_city'])."' and genio_dealclose_flag=0 ";
		$res = parent::execQuery($sql, $this->conn_idc); 
		
		if (DEBUG_MODE) 
		{
			echo '<pre>sql :: '.$sql;
			echo '<pre> mysql_num_rows '.mysql_num_rows($res);
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		
		$Num_rows = mysql_num_rows($res);
		
		if( $Num_rows == 0 )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Please contact Software Team, No pending deal close entry found for Parentid : ".$this->params['parentid']." , data_city : ".$this->params['data_city'];
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		
		if( $Num_rows > 1 )
		{
			$result['error']['code'] = 1;
			$result['error']['msg'] = "Please contact Software Team, Multiple pending deal close entry found for Parentid : ".$this->params['parentid']." , data_city : ".$this->params['data_city']."";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		
		if($res && $Num_rows == 1)
		{			
			$row = mysql_fetch_assoc($res);
			
			#Calling selfsignup 
			
			$response_arr = $this->callselfSignupAPI($row);
			
			$response_arr['error_code']= intval($response_arr['error_code']);
			
			if($response_arr['error_code']==0)
			{
				
				$this->updategeniodealcloseflag($row['parentid'],$row['data_city'],$row['trans_id'],$this->params['genio_dealclose_by']);
				
				$result['error']['code'] = 0;
				$result['result'] = "genio deal close flags updated sucessfully";
				
			}else
			{
				$result['error']['code'] = 1;
				$result['error']['msg'] = "Please contact Software Team, insSelfSignUp.php API failed for Parentid : ".$this->params['parentid']." , data_city : ".$this->params['data_city'].". API Response :- ".$response_arr['status'];
				$resultstr= json_encode($result);
				print($resultstr);
				die;
			}
			
		}
		
		return $result;
	}
	
	
		
	function centraliselogging($dataarray,$message,$source,$apiurl=null,$apiurlresponse=null)
	{		
		$post_data = array();
		
		$log_url = 'http://192.168.17.109/logs/logs.php';
		
		if(trim($this->params['parentid'])!=null)
		{
			$post_data['ID']                = $this->params['parentid'];
		}elseif(trim($this->params['callermobile'])!=null)
		{
			$post_data['ID']                = $this->params['callermobile'];
		}
		
		$post_data['PUBLISH']           = 'ONLINESIGNUP';
		$post_data['ROUTE']             = $source;
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= $message;
		$post_data['DATA']['url']       = $apiurl;
		$post_data['DATA_JSON']['DataArray'] = json_encode($dataarray);
		$post_data['DATA_JSON']['response'] = $apiurlresponse;
		$post_data['DATA']['user'] = 	$this->usercode;
		$post_data['DATA']['source'] = 	$source;
		
		$post_data = http_build_query($post_data);
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $log_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		
		if(DEBUG_MODE) 
		{
			echo '<br>centraliselogging<br><b>post_data</b> <br>'; print_r($post_data); // uncomment
			echo '<br> content <br>'; print($content); // uncomment
		}		
		
	}
	
	public static function getDataCityFromCity($city)
	{	
		$result_data_city = null;
		
		$configobj= new configclass();
		$urldetails		=	$configobj->get_url(urldecode($data_city));				
		$url 	= $urldetails['jdbox_service_url']."/location_api.php?rquest=get_city&city=".$city;
		
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result_json = curl_exec($ch);
		
		$result 	= json_decode($result_json,true);
		
		
		curl_close($ch);
		
		if($result['numRows']==1)
		{
			$result_data_city=$result['result'][0]['data_city'];
		}		
		
		if(DEBUG_MODE) 
		{
			echo '<br><br> url :: '.$url;
			echo '<br><br> result_json :: '.$result_json;
			echo '<br><br> result  :: '; print_r($result);
			echo '<br><br> result_data_city  :: '.$result_data_city;
		}
		
		return $result_data_city;
			
	}
	
	
	public function MakeGetCurlCall($url)
	{	
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		
		
		
		if(DEBUG_MODE) 
		{
			echo '<br><br> url :: '.$url;		
			echo '<br><br> result  :: '; print($result);
		}
		
		curl_close($ch);	
		
		return $result;
			
	}
		
}
?>
