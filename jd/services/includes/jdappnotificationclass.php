<?php 
ini_set("memory_limit", "-1");
set_time_limit(0);
class jdappnotificationclass extends DB {
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct($params)
	{
		$this->params = $params;
		
		
		$errorarray = array();	
		if(!$this->params['action'])
		{
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['empcode']) || $this->params['empcode'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='empcode missing';
			echo json_encode($errorarray); exit;
		}
		if(!isset($this->params['empname']) || $this->params['empname'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='empname missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['mobile']) || $this->params['mobile'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='mobile missing';
			echo json_encode($errorarray); exit;
		}
		
		if(!isset($this->params['text']) || $this->params['text'] == ''){
			$errorarray['errorcode']='1';
			$errorarray['errormsg']='text missing';
			echo json_encode($errorarray); exit;
		}
		
		//~ if(!isset($this->params['empname']) || $this->params['empname'] == ''){
			//~ $errorarray['errorcode']='1';
			//~ $errorarray['errormsg']='module missing';
			//~ echo json_encode($errorarray); exit;
		//~ }
		
		//~ if(!isset($this->params['data_city']) || $this->params['data_city'] == ''){
			//~ $errorarray['errorcode']='1';
			//~ $errorarray['errormsg']='data city missing';
			//~ echo json_encode($errorarray); exit;
		//~ }
		
		
		$this->usercode = null;
		$this->setServers();
		$this->data_city =	$this->params['data_city'];
		$this->empcode =	$this->params['empcode'];
		$this->mobile =	$this->params['mobile'];
		$this->empname =	$this->params['empname'];
		$this->text =	$this->params['text'];

	}
function curl_call($url,$post_array_data){
	
		
		$finalip	=	array();
		$ch = curl_init();        
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		echo $finalip = curl_exec($ch);
		curl_close($ch);
		return $finalip ;
	}
	function setServers()
	{	
		global $db;
					
		if(DEBUG_MODE)
		{
			echo '<pre>db array :: ';
			print_r($db);
		}
		
		
		$data_city 				= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->local		= $db[strtolower($data_city)]['d_jds']['master'];
        $this->idc_con			= $db[strtolower($data_city)]['idc']['master'];
		$this->messaging	= $db[strtolower($data_city)]['messaging']['master'];
		
	}
	
	 function appNotification(){
				
				$notification=array();
				$url 	= 	'http://192.168.20.116/insert_whatsapp.php';
				$notification['source'] = 'app_notification_jdapp';
				$notification['mobile'] = $this->mobile;
				$notification['uname'] = $this->empname;
				$notification['sms_text'] = $this->text;
				$notification['wa_flag'] = 1;
				
				$Curl_data = $this->curl_call($url,$notification);
				$con_messges= json_decode($Curl_data,true);
				
				if($con_messges['results']['error_code']==0)
				{
					   $message_sent_log = "INSERT INTO online_regis.tbl_jdapp_notification_logs SET data_city='".$this->data_city."',entry_date=NOW(),mobile='".$this->mobile."',sms_text='".addslashes($this->text)."',empcode='".$this->empcode."',empname='".addslashes($this->empname)."'";
					  $con_message = parent::execQuery($message_sent_log, $this->idc_con);
					  
					  $JDAPPmessage['errorCode']		   = 0;	
					  $JDAPPmessage['message']		   = 'Sent SMS Properly';	
				  
			   }else{
					 $JDAPPmessage['errorCode']		   = 1;	
				} 
		
		return json_encode($JDAPPmessage);
		
	}
}


?>
