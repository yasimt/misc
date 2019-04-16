<?php
require_once 'class_send_sms_email.php';
class Tvcs_service_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= urldecode(trim($params['data_city']));
		$mobile 			= trim($params['mobile']);
		$email 				= trim($params['email']);
		$ucode 				= trim($params['ucode']);
		$uname 				= trim($params['uname']);
		$action				= trim($params['action']);
		$ad_id				= trim($params['ad_id']);
		
		//echo "<pre>params:-";print_r($params);die("class");
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message,1));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($module)=='' ){
			$message = "Module is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($mobile)=='' && $action==''){
			$message = "Mobile is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($ucode)==''){
			$message = "Ucode is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		
		
		$this->parentid  	= trim($parentid);
		$this->data_city 	= trim($data_city);
		$this->module  	  	= trim($module);
		$this->mobile  	  	= trim($mobile);
		$this->email  	  	= trim($email);
		$this->ucode		= trim($ucode);
		$this->uname		= trim($uname);
		$this->action		= trim($action);
		$this->adId			= trim($ad_id);
		
		$this->mobile    = ltrim($this->mobile,",");
		$this->mobile    = rtrim($this->mobile,",");
		
		$this->email    = ltrim($this->email,",");
		$this->email    = rtrim($this->email,",");
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
		
	}
	
	function setServers()
	{
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->is_remote = 'REMOTE';
		}
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->dnc  		= $db['dnc'];
		
		if(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}

		}
		elseif(strtoupper($this->module) =='ME' || strtoupper($this->module) =='JDA') {
			$this->conn_temp		= $this->conn_idc;
			$this->conn_catmaster 	= $this->conn_local;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}
		
	}
	
	function get_ad_id(){
		if($this->mongo_flag==1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "contractid";
			$mongo_alias = array();
			$mongo_alias['catIds']		= "catid";
			$mongo_inputs['aliaskey'] 	= $mongo_alias;
			$rowTempCategory = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sqlTempCategory	= "SELECT catIds as catid, contractid FROM tbl_business_temp_data WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	=  parent::execQuery($sqlTempCategory, $this->conn_temp);
			if(mysql_num_rows($resTempCategory)>0)
			{
				$rowTempCategory = mysql_fetch_assoc($resTempCategory);
			}
		}
		
		if(count($rowTempCategory)>0){
						
			if((isset($rowTempCategory['catid']) && $rowTempCategory['catid'] != '')){
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$rowTempCategory['catid']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				if(count($temp_catlin_arr) > 0){                     //fetch ad_id based on cat's
					$temp_catlin_arr_imp = implode("','", $temp_catlin_arr);
					//$fetchAdId = "SELECT GROUP_CONCAT(DISTINCT(ad_id)) AS ad_id  FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$temp_catlin_arr_imp."')";
					//$resAdId   = parent::execQuery($fetchAdId, $this->conn_catmaster);
					$cat_params = array();
					$cat_params['page']= 'Tvcs_service_class';
					$cat_params['data_city'] 	= $this->data_city;
					$cat_params['return']		= 'ad_id';

					$where_arr  	=	array();			
					$where_arr['catid']			= implode(",", $temp_catlin_arr);;
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);

					$cat_res_arr = array();
					if($cat_res!=''){
						$cat_res_arr =	json_decode($cat_res,TRUE);
					}
					
					$ad_id_arr ='';
					if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
						//$rowAdId = mysql_fetch_assoc($resAdId);
						foreach($cat_res_arr['results'] as $key =>$rowAdId){
							if($rowAdId['ad_id']!=''){
								$ad_id_arr[] =  $rowAdId['ad_id'];
							}
						}
						if(count($ad_id_arr)>0){
							$ad_id =	implode(",", $ad_id_arr);
						}
						//$ad_id   = $rowAdId['ad_id'];
						return $ad_id;
					}
					
				}else{
					return 0; // no cats
				}
			}else{
				return 0; // no cats
			}
		}else{
			return 0; // no cats
		}
		
	}
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
			$final_catids_arr = array_merge($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function returnTvAdLink(){
		$link = array();
		$this->ad_id = $this->get_ad_id();
		$fetchLinks		=	$this->fetchAdlink();
		if($this->ad_id!='' && $this->ad_id!=0){ 
			$ad_ids = str_replace(",","','",$this->ad_id);
			$ad_link = "SELECT ad_id, ad_link, sms_text,vertical_name,thumbnails FROM online_regis1.tbl_category_ad_link WHERE ad_id IN ('".$ad_ids."') AND (sms_text IS NOT NULL AND sms_text!='') AND (ad_link IS NOT NULL AND ad_link!='') ";
			$res_ad_link = parent::execQuery($ad_link, $this->conn_idc);
			if($res_ad_link && mysql_num_rows($res_ad_link) > 0){
				while($row_ad_link = mysql_fetch_assoc($res_ad_link)){
					$link_to_send = $row_ad_link['ad_link'];
					$link['vert_name']	=	$row_ad_link['vertical_name'];		
					$link['ad_id']		=	$row_ad_link['ad_id'];		
				}
				
				$link['code'] = 0;
				$link['link'] = $link_to_send;
			}else{
				$generic_link = "https://youtu.be/5gynJaWAGjE";
				$link['code'] = 0;
				$link['link'] = $generic_link;
			}
		}else{
			$generic_link = "https://youtu.be/5gynJaWAGjE";
			$link['code'] = 0;
			$link['link'] = $generic_link;
		}
		
		$link['fetchedData']	=	$fetchLinks;
		return $link;
	}
	function fetchAdlink(){
		$link = array();																																						
		$this->ad_id = $this->get_ad_id();
		$ad_ids = str_replace(",","','",$this->ad_id);							
		$ad_link = "SELECT * FROM online_regis1.tbl_category_ad_link order by ad_id";
		$res_ad_link = parent::execQuery($ad_link, $this->conn_idc);
		if($res_ad_link && mysql_num_rows($res_ad_link) > 0){
			$i	=	0;
			while($row_ad_link = mysql_fetch_assoc($res_ad_link)){
				$link['data'][]	= $row_ad_link;
			}
			$link['code'] = 0;
		}else{
			$generic_link = "https://youtu.be/5gynJaWAGjE";
			$link['code'] = 0;
			$link['data'] = $generic_link;
		}
		return $link;
	}
	function sendTvcAdLink(){
		GLOBAL $db;
		$emailsms_obj = new email_sms_send($db,$this->data_city);
		
		if($this->mobile!=''){
			$mobilenums = explode(',',$this->mobile);
		}
		if($this->email!=''){
			$emailid = explode(',',$this->email);
		}
		
		$emailid	 = array_unique($emailid);
		$mobilenums  = array_unique($mobilenums);
		if($this->adId == '')
			$this->ad_id = $this->get_ad_id();
		else
			$this->ad_id = $this->adId;
		$dnd=0;
		$dn_num='';
		if($this->ad_id!='' && $this->ad_id!=0){ 									//send sms text and link a/c to cat's
			$ad_ids = str_replace(",","','",$this->ad_id);
			$ad_link = "SELECT ad_id, ad_link, sms_text FROM online_regis1.tbl_category_ad_link WHERE ad_id IN ('".$ad_ids."') AND (sms_text IS NOT NULL AND sms_text!='') AND (ad_link IS NOT NULL AND ad_link!='') ";
			$res_ad_link = parent::execQuery($ad_link, $this->conn_idc);
			if($res_ad_link && mysql_num_rows($res_ad_link) > 0){
				while($row_ad_link = mysql_fetch_assoc($res_ad_link)){
					
					$link_to_send = $row_ad_link['ad_link'];
					$sms_text     = $row_ad_link['sms_text'];
					if($link_to_send!='' && $sms_text!=''){
						$smsflg = '';
						if(count($mobilenums)>0){  
							$sms_cont = $sms_text;
							foreach($mobilenums as $key => $val){
								 $checkfordnc="SELECT * FROM dnc.dndlist  WHERE dndnumber=".$val." AND (safe_till <= NOW()  OR is_safe=0) and is_deleted=0 "; 
				 				$res_dnc = parent::execQuery($checkfordnc, $this->dnc);
								if($res_dnc && mysql_num_rows($res_dnc)>0)
								 {
								 	$smsflg =0;
								 	$dnd=1;
								 	$dn_num=$val;
								 	break;
								 }
								 else{
									if($emailsms_obj->sendSMS($val,$sms_cont,'TV_AD_'.strtoupper($this->module).'')){
										$smsflg =1;
									}
								}
							} 
						}
						$emailflg = '';$email_cont='';$headers='';
						if(count($emailid)>0){
							 $email_cont = $sms_text;
							 $headers  = "From: noreply@justdial.com" . "\r\n" .
							 $headers .= "MIME-Version: 1.0\r\n";
							 $headers .= "Content-Type: text/html; charset=UTF-8\r\n";	
									 
							 
							 $subject	=	"Just Dial TV Ad Link";
							 $from  	= 	"noreply@justdial.com";
							foreach($emailid as $key => $val)
							{
								$email_cont	=	trim($email_cont);
								 
								$trueFlg=  mail($val, $subject, addslashes(stripslashes($email_cont)), $headers);
								 
								if($trueFlg){
									//~ echo "<br>success";
								}
								 
								//if(mail($val, $subject, addslashes(stripslashes($email_cont)), $headers)){
								if($emailsms_obj->sendInvoiceMails($val,$from,$subject, addslashes(stripslashes($email_cont)), 'TV_AD_'.strtoupper($this->module).'',$this->parentid)){
									$emailflg = 1;					
								}else{
									$emailflg = 0;
								}
								
							}
						}
					}
					//echo "<br>emailflg:-".$emailflg;
					$qry_log = "INSERT INTO addlink_message_request_log (parentid, email, mobile, email_sent_time, sms_sent_time, email_send_flag, sms_send_flag, usercode, username, sms_text, ad_link) VALUES
					('".$this->parentid."', '".$this->email."', '".$this->mobile."', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', '".$emailflg."', '".$smsflg."', '".$this->ucode."','".$this->uname."' , '".addslashes(stripslashes($sms_text))."' , '".$link_to_send."')";
					$res_log = parent::execQuery($qry_log, $this->conn_temp);
					
					if($emailflg==1 && $smsflg==1){
						$message = "Link sent on mobile and email Succesfully!";
						echo json_encode($this->send_die_message($message,0));
						die();
					}else if($smsflg==1){
						$message = "Link sent on mobile Succesfully!";
						echo json_encode($this->send_die_message($message,0));
						die();
					}else if($emailflg==1){
						$message = "Link sent on email Succesfully!";
						echo json_encode($this->send_die_message($message,0));
						die();
					}
					else if($dnd==1 && $smsflg==0){
						$message = "$dn_num Is a Dnd Number"; 
						echo json_encode($this->send_die_message($message,1)); 
						die();
					}
					else{
						$message = "Link not sent";
						echo json_encode($this->send_die_message($message,1));
						die();
					}
				}
							
			}else{
				$this->sendGenericSms();
			}
		}else{																		// send generic if no cat's						
			$this->sendGenericSms();
		}
	
	}
	
	function sendGenericSms(){
		GLOBAL $db;
		$emailsms_obj = new email_sms_send($db,$this->data_city);
		
		if($this->mobile!=''){
			$mobilenums = explode(',',$this->mobile);
		}
		if($this->email!=''){
			$emailid = explode(',',$this->email);
		}
		
		$emailid	 = array_unique($emailid);
		$mobilenums  = array_unique($mobilenums);
		$smsflg = '';
		$dnd=0;
		$dn_num='';
		if(count($mobilenums)>0){  
			
			
			$sms_cont = 'What\'s Trending - Justdial\'s';
			$sms_cont .= " New Ad Campaign with Mr. Amitabh Bachchan\nWatch Now: https://youtu.be/5gynJaWAGjE\n\nTeam Justdial";
			
			foreach($mobilenums as $key => $val){
				 $checkfordnc="SELECT * FROM dnc.dndlist  WHERE dndnumber=".$val." AND (safe_till <= NOW()  OR is_safe=0) and is_deleted=0 "; 
				 $res_dnc = parent::execQuery($checkfordnc, $this->dnc);

				 if($res_dnc && mysql_num_rows($res_dnc)>0)
				 {
				 	$smsflg =0;
				 	$dnd=1;
				 	$dn_num=$val;
				 	break;
				 }
				 else{
				 if($emailsms_obj->sendSMS($val,$sms_cont,'TV_AD_'.strtoupper($this->module).'')){
					$smsflg =1;
				 }
				}
			} 
		}
		
		$emailflg = '';$email_cont='';$headers='';
		/*if(count($emailid)>0){
			 $link1 = "https://youtu.be/5gynJaWAGjE";
			
			 $email_cont = 'What\'s Trending - Justdial\'s New Ad Campaign with Mr. Amitabh Bachchan<br>Watch Now: <br> <a href='.$link1.'>'.$link1.'</a><br><br>Team Justdial';
			 
			 $headers  = "From: noreply@justdial.com" . "\r\n" .
			 $headers .= "MIME-Version: 1.0\r\n";
			 $headers .= "Content-Type: text/html; charset=UTF-8\r\n";	
					 
			 
			 $subject	=	"Just Dial TV Ad Link";
			 $from  	= 	"noreply@justdial.com";
			foreach($emailid as $key => $val)
			{
				$email_cont	=	trim($email_cont);	
				if($emailsms_obj->sendInvoiceMails($val,$from,$subject, addslashes($email_cont), 'TV_AD_'.strtoupper($this->module).'',$this->parentid)){
					$emailflg = 1;					
				}else{
					$emailflg = 0;
				}
				
			}
		}*/


		$qry_log = "INSERT INTO addlink_message_request_log (parentid, email, mobile, email_sent_time, sms_sent_time, email_send_flag, sms_send_flag, usercode, username,sms_text, ad_link) VALUES
		('".$this->parentid."', '".$this->email."', '".$this->mobile."', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."', '".$emailflg."', '".$smsflg."', '".$this->ucode."','".$this->uname."', '".addslashes(stripslashes($sms_cont))."', 'https://youtu.be/5gynJaWAGjE')";
		$res_log = parent::execQuery($qry_log, $this->conn_temp);
		
		if($emailflg==1 && $smsflg==1){
			$message = "Link sent on mobile and email Succesfully!";
			echo json_encode($this->send_die_message($message,0));
			die();
		}else if($smsflg==1){
			$message = "Link sent on mobile Succesfully!";
			echo json_encode($this->send_die_message($message,0));
			die();
		}else if($emailflg==1){
			$message = "Link sent on email Succesfully!";
			echo json_encode($this->send_die_message($message,0));
			die();
		}
		else if($dnd==1 && $smsflg==0){
			$message = "$dn_num Is a Dnd Number"; 
			echo json_encode($this->send_die_message($message,1)); 
			die();
		}
		else{
			$message = "Link not sent";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
	
		
	}
	
	private function send_die_message($msg,$errorCode)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = $errorCode;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
