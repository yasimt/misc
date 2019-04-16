<?php
/* 
To check profile pic  - shital patil 16-06-5016
*/
//require_once('class_send_sms_email.php');
class vlc_emailers_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
	var  $conn_messaging 	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	function __construct($params)
	{	
		global $params;
 		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']); 	
		$rquest 			= trim($params['rquest']); 
 		 
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_die_message($message));
			die();
		}		 
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->setServers();	

		$this->gen_arr			= $this->get_general_info();		
		$this->data_city		= $this->gen_arr['data_city'];
		if(count($this->gen_arr)>0)
			$this->edit_contract =	1;
		else	
			$this->edit_contract =	0;
		$this->paid_flag			= 	$this->paid_status();	
		
		$this->docid				=	$this->get_docid('docid');
		$this->shortUrl				=	$this->get_docid('shorturl');
		$this->mappedEmailid		=	$this->getEmailId();	
		
	}
	public function checkImageProPic()
	{
		
		$sent_date = date('Y-m-d');	
		if($this->edit_contract == 1 && $this->paid_flag == 1)
		{
			$select = "SELECT * FROM d_jds.tbl_profile_pic_message_log WHERE parentid = '".$this->parentid."' AND (sent_date > ADDDATE(NOW(),-7) OR restrct_flag = 1) ";
			$res_sel 	= parent::execQuery($select, $this->data_correction);
			
			if(mysql_num_rows($res_sel)== 0)
			{
				$mobile			=	"";
				$email			=	"";
				$contact_person	=	"";
				if(!empty($this->gen_arr['mobile'])){
					$mobile_arr = explode(",",$this->gen_arr['mobile']);
					if(count($mobile_arr)>0)
						$mobile = $mobile_arr['0'];
				}
				if(!empty($this->gen_arr['email'])){
					$email_arr = explode(",",$this->gen_arr['email']);
					if(count($email_arr)>0)
						$email = $email_arr['0'];
				}	
				if(!empty($this->gen_arr['contact_person'])){
					$contact_person_arr = explode(",",$this->gen_arr['contact_person']);
					if(count($contact_person_arr)>0)
						$contact_person = $contact_person_arr['0'];
				}
				if($contact_person == '')
					$contact_person = "Customer";	

 				if(!empty($email))
				{
					$email_id  		= $email;
					$email_subject  = "Just Dial - Contract without photo";
					
					$email_text='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

					<style>
					*{margin:0;padding:0;}
					</style>
					</head>
					<body>
					<!--[if (gte mso 9)|(IE)]>
					<table width="570" align="center" cellpadding="0" cellspacing="0" border="0">
					<tr>
					<td>
					<![endif]-->
					<div align="center">
						<table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width:540px;border: 2px solid #f3f3f3; text-align:left; backgroung:#ffffff;">
						
						<tr>
						<td width="100%" style="vertical-align:top;text-align:left;padding:10px 0 10px 20px;border-bottom:1px solid #f2efe6">
						<a href="https://www.justdial.com">
						<img src="http://akam.cdn.jdmagicbox.com/images/email_banner/jd_logo_bw.png" border="0">
						</a>
						</td>
						</tr>
						
						<tr>
						<td style="font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:16px;color:#6a6969;text-align:center;"> 
						<img src="http://akam.cdn.jdmagicbox.com/images/email_banner/uploadphoto.jpg" style="border:0;" />
						</td>
						</tr>
						
						<tr>
						<td style="padding:20px 20px 0;font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:16px;color:#6a6969;line-height:20px;letter-spacing: 0.01em;"> Hi '.$contact_person.',
						</td>
						</tr>
						<tr>
						<td style="padding:20px;font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:15px;color:#6a6969;line-height:20px;letter-spacing: 0.01em; text-align:justify;"> Your company profile has been activated on Justdial.com We noticed that you might have missed filling up some important fields as your profile is not 100% complete. Providing more information about your company will get you higher response and help you to get optimum business.
						</td>
						</tr>
						
						<tr>
						<td style="font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:16px;color:#6a6969;"> 
						<img src="http://akam.cdn.jdmagicbox.com/images/email_banner/pointsnew.jpg" style="border:0;" />
						</td>
						</tr>	
						
						<tr>
						<td style="text-align:center;padding:20px 10px">   
						<a href="http://jsdl.in/EL-'.$this->shortUrl.'"  style="background:#1193C5;font-size:13px;font-family:Roboto-Light, Helvetica, Arial, sans-serif;border-radius:8px;padding:7px 10px;color:#fff;text-decoration:none;text-align:center">Complete Your Business Now</a>
						</td>
						</tr>
						
						<tr>
						<td style="padding:20px 10px 0;font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:16px;color:#6a6969;line-height:20px;letter-spacing: 0.01em; text-align:center;">We\'re Here For You
						</td>
						</tr>
						
						<tr>
						<td style="padding:5px 20px;font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:14px;color:#6a6969;line-height:20px;text-align:center;">Feel free to contact us. We\'ll be guiding you on every step of your journey <br/>by giving you additional resources and important updates to grow in business.
						</td>
						</tr>
						<tr>
						<td style="padding:20px 10px;font-family:Roboto-Light,Arial, Helvetica, sans-serif;font-size:16px;color:#6a6969;text-align:center;">For any query, please visit <a href="https://www.justdial.com/cms/customer-care" target="_blank" style="color:#1274C0; text-decoration:none;letter-spacing:-0.3px;">customer-care </a>
						</td>
						</tr>
						
						<tr>
						<td>
						<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
						<td width="100%" style="color: #626262;font-size: 12px;padding: 10px 0 10px;font-weight: none;-webkit-text-size-adjust: none;font-family: Roboto-Light, Helvetica, Arial, sans-serif;text-align:center;border-top:1px solid #e7e7e7;background-color:#f9f9f9;">
						<a href="http://www.justdial.com/shop-online" target="_blank" style="display:inline-block;padding: 0 5px;color:#626262;text-decoration:none;">Shop Now</a>
						<span style="display:inline-block;padding: 0 5px;color:#626262;text-decoration:none;">|</span>					
						<a href="http://jsdl.in/litegdl" target="_blank" style="display:inline-block;padding: 0 5px;color:#626262;text-decoration:none;">Download JD APP</a>
						<span style="display:inline-block;padding: 0 5px;color:#626262;text-decoration:none;">|</span>
						
						<a href="tel:8888888888" target="_blank" style="display:inline-block;padding: 0 5px;color:#626262;text-decoration:none;">88888 88888</a>
						</td>
						</tr>
						<tr>
						<td width="100%" style="color: #626262;font-size: 12px;padding: 10px 0 7px 5px;font-weight: none;-webkit-text-size-adjust: none;font-family: Roboto-Light, Helvetica, Arial, sans-serif;text-align:center;border-top:1px solid #e7e7e7;background-color:#f9f9f9;">
						<!--[if mso]>
						<v:rect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://www.justdial.com/functions/facebook_log.php?type=201&page=1" style="padding:0 5px 0 5px; display:inline-block;background-color: transparent  !important;" fill="false" stroke="f" strokecolor="none">
						<w:anchorlock/>
						<center>
						<![endif]-->
						<a href="http://www.justdial.com/functions/facebook_log.php?type=201&page=1">
						<img src="http://images.jdmagicbox.com/email_banners/Follow_facebook.png" border="0" style="padding:0 5px 0 5px;margin:0 5px 0 5px;display:inline-block"></a>
						<!--[if mso]>
						</center>
						</v:rect>
						<![endif]-->
						
						<span style="font-size:20px;color:#cbcbcb;">|</span>		
						<!--[if mso]>
						<v:rect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://plus.google.com/+justdial" style="padding:0 5px 0 5px; display:inline-block;background-color: transparent  !important;"  fill="false" stroke="f" strokecolor="none">
						<w:anchorlock/>
						<center>
						<![endif]-->
						<a href="https://plus.google.com/+justdial">
						<img src="http://images.jdmagicbox.com/email_banners/Follow_google.png" border="0" style="padding:0 5px 0 5px;margin:0 5px 0 5px;display:inline-block"></a>
						<!--[if mso]>
						</center>
						</v:rect>
						<![endif]-->
						</td>
						</tr>
						
						<tr>
						<td style="padding:10px 0 10px;text-align:center;background-color:#f9f9f9;" align="center">
						<a href="http://www.justdial.com"><img src="http://img.jdmagicbox.com/email_banners/justdial_logo1.png" border="0" ></a>
						</td>
						</tr>	
						<tr>
						<td style="color: #626262;font-size: 12px;padding: 5px 6px 5px 6px;font-weight: none;-webkit-text-size-adjust: none;font-family: Roboto-Light, Helvetica, Arial, sans-serif;text-align:center;background-color:#f9f9f9;">
						India\'s No. 1 local search engine
						</td>
						</tr>			
						<tr>
						<td style="color: #626262;font-size: 12px;padding: 5px 6px 10px 6px;font-weight: none;-webkit-text-size-adjust: none;font-family: Roboto-Light, Helvetica, Arial, sans-serif;text-align:center;background-color:#f9f9f9;">
						We respect your feedback, follow <a href="http://www.justdial.com/contest/unsubscribe.php?cs=@CHECKSUM&id=@EMAIL" target="_blank" style="color:#1274c0;font-family:Roboto-Light, Helvetica, Arial, sans-serif">this link</a> to opt-out
						
						</td>
						</tr>
						</table>
						
					   </td>
						</tr>
						</table>		
					</div>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->


					</body>
					</html>';
				}
				$sms_script = 'Dear Customer,~We observed that Photos/Logo/Video is missing in your Listing.~Pls click the below Link to upload & attract more customers.~jsdl.in/EL-'.$this->shortUrl .'';
			 
			 	if(!empty($mobile) || !empty($email))
				{
					$data_source 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
					
					$params_SE = Array();
					$params_SE['city_name'] 	 	= $data_source;
					$params_SE['email_id'] 	 		= $email;
					$params_SE['email_subject']  	= $email_subject;
					$params_SE['email_text']  		=	$email_text;
					$params_SE['email_id_cc'] 		= $email_id_cc;
					$params_SE['sender_email'] 		= addslashes($this->mappedEmailid);
					$params_SE['parent_id'] 		= $this->parentid;
					$params_SE['source'] 	 		= 'Profile Pic';
					if(strtolower($data_source) == 'remote')
						$params_SE['mod'] 		 		= 'common_idc';
					else	
						$params_SE['mod'] 		 		= 'common_panindia';
						
					$params_SE['mobile'] 	= $mobile;
					$params_SE['sms_text'] = $sms_script;
					
					$result_api  = $this->callSMSEmailAPI($params_SE);
				}
			 	if(0)//strtoupper($this->module) == 'CRON')
				{	
					$user_details = $this->get_user_details();	
					foreach($user_details AS $key=>$val)
					{	
						if(!empty($val))
						{
							$emp_details = $this->get_empinfo_data($val);	
							$jd_ucode .= $emp_details['empcode'].",";
							$jd_user_mobile .= $emp_details['mobile_num'].",";
							$jd_user_email 	.= $emp_details['email_id'].",";
							if(!empty($jd_user_mobile) || !empty($jd_user_email))
							{
								$sql_common_mail_user = "INSERT INTO sms_email_sending.tbl_common_intimations SET
									parent_id		= '".$this->parentid."', 
									email_id		= '".$emp_details['email_id']."', 
									email_subject	= '".addslashes($email_subject)."', 
									email_text		= '".addslashes($email_text)."',  
									sender_email	= '".addslashes($this->mappedEmailid)."',  
									mobile			= '".$emp_details['mobile_num']."',  
									sms_text		= '".addslashes($sms_script)."',  
									source			= 'Profile Pic',  
									sender_name		= 'Just Dial',  
									city_name		= '".$this->data_city."'"; 
									 
								$res_common_mail 	= parent::execQuery($sql_common_mail_user, $this->messaging);	
							}
						}
					}
				}	
				if($result_api == '1')
				{
					$InsertLog = "INSERT INTO d_jds.tbl_profile_pic_message_log
					SET
						parentid 		=	'".$this->parentid."', 
						companyname		=	'".addslashes($this->gen_arr['companyname'])."', 
						jd_ucode		=	'".trim($jd_ucode,",")."',  
						mobile			=	'".$mobile."', 
						jd_user_mobile	=	'".trim($jd_user_mobile,",")."', 
						email			=	'".$email."', 
						jd_user_email	=	'".trim($jd_user_email,",")."', 
						contact_person	=	'".addslashes($contact_person)."', 
						sent_date		=	now(), 
						module			=	'".$this->module."', 
						data_city		=	'".addslashes($this->data_city)."'";
					$resInsertLog 	= 	parent::execQuery($InsertLog, $this->data_correction);
					
					$output['result'] 				=   "SMS/Email sent";
					$output['error']['message'] 	=  	"success";	
					echo json_encode($this->send_die_message($output));	
				}		
				else
				{
					$output['result'] 				=   "SMS/Email Not sent";
					$output['error']['message'] 	=  	"Error";	
					echo json_encode($this->send_die_message($output));	
				}				 
			} 	
			else
			{
				$output['result'] 				=   "SMS/Email already sent today";
				$output['error']['message'] 	=  	"success";	
				echo  json_encode($this->send_die_message($output));
			}				
		}
		else
		{
			if($this->edit_contract == 0){	
				$output['result'] 	=	"New Contract";				
			}
			if($this->paid_flag == 0){
				$output['result'] 	=	"Non Paid Contract";				
			}
			$output['error']['message'] 	=  	"success";	
			echo  json_encode($this->send_die_message($output));
		}
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->messaging    	= $db[$conn_city]['messaging']['master'];		
		$this->conn_fnc  	  	= $db[$conn_city]['fin']['master'];		
		$this->data_correction	= $db[$conn_city]['data_correction']['master'];		
	}

	public function get_general_info() 
	{
		global $params;
		$query_get_gen_info="SELECT * FROM tbl_companymaster_generalinfo  WHERE parentid= '".$params['parentid']."'";
		$res_gen_info 	= parent::execQuery($query_get_gen_info, $this->conn_iro);
		$row_gen_info=mysql_fetch_assoc($res_gen_info);
		return $row_gen_info;
	}	
	public function paid_status()
	{
		switch(strtolower($this->data_city))
		{
			case "mumbai" 	 : $jdboxurl = MUMBAI_JDBOX_API; break;
			case "delhi"  	 : $jdboxurl = DELHI_JDBOX_API; break;
			case "kolkata" 	 : $jdboxurl = KOLKATA_JDBOX_API; break;
			case "bangalore" : $jdboxurl = BANGALORE_JDBOX_API; break;
			case "chennai" 	 : $jdboxurl = CHENNAI_JDBOX_API; break;
			case "pune" 	 : $jdboxurl = PUNE_JDBOX_API; break;
			case "hyderabad" : $jdboxurl = HYDERABAD_JDBOX_API; break;
			case "ahmedabad" : $jdboxurl = AHMEDABAD_JDBOX_API; break;
			default 		 : $jdboxurl = REMOTE_CITIES_JDBOX_API; break;
		}
		$url	= "http://".$jdboxurl."/services/contract_type.php?parentid=".$this->parentid."&data_city=".$this->data_city."&rquest=get_contract_type";
		$ret = $this->get_data($url);		
 		return $ret['result']['paid'];
	}
	public function get_docid($type) 
	{
		$sql	=	"SELECT *,concat(url_cityid,shorturl) as shorturl FROM tbl_id_generator WHERE parentid= '".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->conn_iro);
		$row	=	mysql_fetch_assoc($res);
		if($type == 'docid')
			return $row['docid'];
		else if($type == 'shorturl')
			return $row['shorturl'];
	}
	public function getEmailId()
	{
		$array_mapped_city  = array('port blair'=>'hyderabad','achampet'=>'hyderabad','addanki'=>'hyderabad','adilabad'=>'hyderabad','adoni'=>'hyderabad','akividu'=>'hyderabad','alampur'=>'hyderabad','allagadda'=>'hyderabad','alur'=>'hyderabad','amalapuram'=>'hyderabad','amangallu'=>'hyderabad','amudalavalasa '=>'hyderabad','anakapalle'=>'hyderabad','anakpalli'=>'hyderabad','anantapur'=>'hyderabad','anaparthi'=>'hyderabad','andole'=>'hyderabad','araku'=>'hyderabad','armoor'=>'hyderabad','arundelpet'=>'hyderabad','asifabad'=>'hyderabad','aswaraopet'=>'hyderabad','atmakur'=>'hyderabad','attili'=>'hyderabad','avanigadda'=>'hyderabad','b. kothakota'=>'hyderabad','badvel'=>'hyderabad','bandar'=>'hyderabad','banganapalle'=>'hyderabad','bangarupalem'=>'hyderabad','banswada'=>'hyderabad','bapatla'=>'hyderabad','bellampalli'=>'hyderabad','bellampally'=>'hyderabad','betamcherla'=>'hyderabad','bhadrachalam'=>'hyderabad','bhainsa'=>'hyderabad','bhimadole '=>'hyderabad','bhimavaram'=>'hyderabad','bhimunipatnam '=>'hyderabad','bhongir'=>'hyderabad','boath'=>'hyderabad','bobbili'=>'hyderabad','bodhan'=>'hyderabad','burgampahad'=>'hyderabad','chandoor'=>'hyderabad','chandragiri'=>'hyderabad','chavitidibbalu'=>'hyderabad','chejerla '=>'hyderabad','cherial '=>'hyderabad','chilakaluripeta'=>'hyderabad','chilakalurupet '=>'hyderabad','chinnor '=>'hyderabad','chintalapudi'=>'hyderabad','chintapalle '=>'hyderabad','chipurupalli'=>'hyderabad','chirala'=>'hyderabad','chittoor'=>'hyderabad','chodavaram'=>'hyderabad','cuddapah '=>'hyderabad','darsi'=>'hyderabad','devarapalli'=>'hyderabad','devarkonda '=>'hyderabad','dharmavaram'=>'hyderabad','dhone '=>'hyderabad','east godavari'=>'hyderabad','eluru'=>'hyderabad','gadwal'=>'hyderabad','gajapathinagaram '=>'hyderabad','gajwel '=>'hyderabad','garladinne'=>'hyderabad','giddalur'=>'hyderabad','godavarikhani'=>'hyderabad','gooty '=>'hyderabad','gudivada'=>'hyderabad','gudur'=>'hyderabad','guntakal'=>'hyderabad','guntur'=>'hyderabad','hanamkonda'=>'hyderabad','hindupur'=>'hyderabad','husnabad'=>'hyderabad','huzurabad'=>'hyderabad','huzurnagar '=>'hyderabad','hyderabad'=>'hyderabad','ichapuram '=>'hyderabad','isntur'=>'hyderabad','jaggayyapeta'=>'hyderabad','jagtial'=>'hyderabad','jammalamadugu '=>'hyderabad','jammikunta'=>'hyderabad','jangaon'=>'hyderabad','jangareddygudem'=>'hyderabad','jannaram'=>'hyderabad','kadapa'=>'hyderabad','kadiri'=>'hyderabad','kaikalur'=>'hyderabad','kakati'=>'hyderabad','kakinada'=>'hyderabad','kalwakurthy '=>'hyderabad','kalyandurg'=>'hyderabad','kamalapuram'=>'hyderabad','kamareddy'=>'hyderabad','kambadur '=>'hyderabad','kanaganapalle '=>'hyderabad','kandukur'=>'hyderabad','kanigiri '=>'hyderabad','karim nagar'=>'hyderabad','karimnagar'=>'hyderabad','kasibugga'=>'hyderabad','kavali'=>'hyderabad','khammam'=>'hyderabad','khanapur'=>'hyderabad','kodad'=>'hyderabad','kodangal '=>'hyderabad','koilakuntla'=>'hyderabad','kollapur'=>'hyderabad','kondapally'=>'hyderabad','koratla'=>'hyderabad','kothagudem'=>'hyderabad','kothavalasa'=>'hyderabad','kovur'=>'hyderabad','koyyalagudem'=>'hyderabad','krishna'=>'hyderabad','krosuru'=>'hyderabad','kuppam'=>'hyderabad','kurnool'=>'hyderabad','lakkireddipalli'=>'hyderabad','lakshettipet'=>'hyderabad','macherla'=>'hyderabad','machilipatnam'=>'hyderabad','madakasira'=>'hyderabad','madanapalle'=>'hyderabad','madanpalli'=>'hyderabad','madhira'=>'hyderabad','madnoor'=>'hyderabad','mahabubabad'=>'hyderabad','mahabubnagar'=>'hyderabad','mahadevapur'=>'hyderabad','mahbubabad'=>'hyderabad','mancherial'=>'hyderabad','mandamarri'=>'hyderabad','mandapeta'=>'hyderabad','mangalagiri'=>'hyderabad','manthani'=>'hyderabad','manuguru'=>'hyderabad','markapur'=>'hyderabad','martur-andhra pradesh'=>'hyderabad','medak'=>'hyderabad','medarmetla'=>'hyderabad','metpalli'=>'hyderabad','miryalaguda'=>'hyderabad','mudigubba'=>'hyderabad','mulug'=>'hyderabad','mylavaram'=>'hyderabad','nagari'=>'hyderabad','nagarkurnool'=>'hyderabad','nalgonda'=>'hyderabad','nallacheruvu'=>'hyderabad','nandigama'=>'hyderabad','nandikotkur'=>'hyderabad','nandyal'=>'hyderabad','nandyala'=>'hyderabad','narasampet'=>'hyderabad','narasannapeta'=>'hyderabad','narasapuram'=>'hyderabad','narasaraopet'=>'hyderabad','narayanakhed'=>'hyderabad','narayanpet'=>'hyderabad','narsampet'=>'hyderabad','narsaraopet'=>'hyderabad','narsipatnam'=>'hyderabad','nayudupeta'=>'hyderabad','nellore'=>'hyderabad','nidadavole'=>'hyderabad','nidamanur '=>'hyderabad','nirmal'=>'hyderabad','nizamabad'=>'hyderabad','nuzvid'=>'hyderabad','ongole'=>'hyderabad','outsarangapalle'=>'hyderabad','paderu'=>'hyderabad','pakala'=>'hyderabad','palakollu'=>'hyderabad','palakonda'=>'hyderabad','palamaner'=>'hyderabad','paland'=>'hyderabad','palasa'=>'hyderabad','palvancha'=>'hyderabad','pamuru'=>'hyderabad','pargi'=>'hyderabad','parkal'=>'hyderabad','parvatipuram'=>'hyderabad','pathapatnam'=>'hyderabad','pattikonda'=>'hyderabad','peapully'=>'hyderabad','pedana'=>'hyderabad','peddapalli'=>'hyderabad','peddapuram'=>'hyderabad','penugonda'=>'hyderabad','piduguralla'=>'hyderabad','piler'=>'hyderabad','pithapuram'=>'hyderabad','podili'=>'hyderabad','poduru'=>'hyderabad','polavaram'=>'hyderabad','prakasam'=>'hyderabad','prasanthi nilayam'=>'hyderabad','proddatur'=>'hyderabad','pulivendla'=>'hyderabad','pulivendula'=>'hyderabad','punganuru'=>'hyderabad','puttaparthi'=>'hyderabad','puttaparthy'=>'hyderabad','rajahmundry'=>'hyderabad','rajam'=>'hyderabad','rajampet'=>'hyderabad','rajamumdry'=>'hyderabad','ramachandrapuram'=>'hyderabad','ramagundam'=>'hyderabad','ramannapet'=>'hyderabad','ramapuram'=>'hyderabad','rampachodavaram'=>'hyderabad','rangareddy'=>'hyderabad','rapur'=>'hyderabad','ravulapalem'=>'hyderabad','rayachoti'=>'hyderabad','rayadrug'=>'hyderabad','razam'=>'hyderabad','razole'=>'hyderabad','renigunta'=>'hyderabad','repalle'=>'hyderabad','sadasivpet'=>'hyderabad','salur'=>'hyderabad','samalkot'=>'hyderabad','sangareddy'=>'hyderabad','sathupalli'=>'hyderabad','sattenapalli'=>'hyderabad','satyavedu'=>'hyderabad','secunderabad'=>'hyderabad','shadnagar'=>'hyderabad','siddavattam'=>'hyderabad','siddipet'=>'hyderabad','sileru'=>'hyderabad','sirpur kagaznagar '=>'hyderabad','sirsilla'=>'hyderabad','sitarampuram'=>'hyderabad','sivarao peta'=>'hyderabad','sodam'=>'hyderabad','sompeta'=>'hyderabad','srikakulam'=>'hyderabad','srikalahasti'=>'hyderabad','srisailam'=>'hyderabad','srungavarapukota'=>'hyderabad','subbaraopeta'=>'hyderabad','sudhimalla'=>'hyderabad','sullurpet'=>'hyderabad','suryapet'=>'hyderabad','tadepalle'=>'hyderabad','tadepalligudem'=>'hyderabad','tadipatri'=>'hyderabad','tandur'=>'hyderabad','tanuku'=>'hyderabad','tekkali'=>'hyderabad','tenali'=>'hyderabad','tezu'=>'hyderabad','thungaturthy'=>'hyderabad','tirupathi'=>'hyderabad','tirupati'=>'hyderabad','tiruvuru'=>'hyderabad','tuni'=>'hyderabad','udaygiri'=>'hyderabad','ulvapadu'=>'hyderabad','uravakonda'=>'hyderabad','utnor'=>'hyderabad','v r puram'=>'hyderabad','vaimpalli'=>'hyderabad','vayalpad'=>'hyderabad','vemulavada'=>'hyderabad','vemulawada'=>'hyderabad','venkatagiri'=>'hyderabad','venkatgirikota'=>'hyderabad','vetapalem'=>'hyderabad','vijayawada'=>'hyderabad','vikarabad'=>'hyderabad','vinjamuru'=>'hyderabad','vinukonda'=>'hyderabad','visakhapatnam'=>'hyderabad','vizag'=>'hyderabad','vizayanagaram'=>'hyderabad','vizianagaram'=>'hyderabad','vuyyuru'=>'hyderabad','wanaparthy'=>'hyderabad','warangal'=>'hyderabad','wardhannapet'=>'hyderabad','west godavari'=>'hyderabad','yanam'=>'hyderabad','yelamanchili'=>'hyderabad','yelavaram'=>'hyderabad','yeleswaram'=>'hyderabad','yellandu'=>'hyderabad','yellanuru'=>'hyderabad','yellareddy'=>'hyderabad','yemmiganur'=>'hyderabad','yerragondapalem'=>'hyderabad','yerraguntla'=>'hyderabad','zahirabad'=>'hyderabad','along'=>'kolkata','east kameng'=>'kolkata','east siang'=>'kolkata','itanagar'=>'kolkata','itanagar ziro'=>'kolkata','lohit'=>'kolkata','rani'=>'kolkata','tawang'=>'kolkata','tirap'=>'kolkata','upper subansiri'=>'kolkata','west kameng'=>'kolkata','west siang'=>'kolkata','abhayapuri'=>'kolkata','amerigog'=>'kolkata','badarpur'=>'kolkata','baihata'=>'kolkata','barpeta'=>'kolkata','barpeta road'=>'kolkata','bharalupar'=>'kolkata','bijni'=>'kolkata','bilashipara'=>'kolkata','bongaigaon'=>'kolkata','changsari'=>'kolkata','darrang'=>'kolkata','dhemaji'=>'kolkata','dhubri h o'=>'kolkata','dibrugarh'=>'kolkata','digboi'=>'kolkata','diphu'=>'kolkata','dispur'=>'kolkata','doomdooma'=>'kolkata','goalpara'=>'kolkata','golaghat'=>'kolkata','gotanagar'=>'kolkata','guwahati'=>'kolkata','haflong'=>'kolkata','haiborgaon'=>'kolkata','hailakandi'=>'kolkata','hijiguri'=>'kolkata','hojai'=>'kolkata','jalan nagar'=>'kolkata','jorhat'=>'kolkata','kalain'=>'kolkata','kamrup'=>'kolkata','karimganj'=>'kolkata','karimganj h o'=>'kolkata','kaziranga'=>'kolkata','kharupetia'=>'kolkata','kokrajhar'=>'kolkata','lakhimpur'=>'kolkata','lala'=>'kolkata','mangaldai h o'=>'kolkata','mariana'=>'kolkata','meherpur'=>'kolkata','morigaon'=>'kolkata','nagaon'=>'kolkata','nalbari'=>'kolkata','nazira'=>'kolkata','north cachar hills'=>'kolkata','north lakhimpur'=>'kolkata','nowgong'=>'kolkata','panchgram'=>'kolkata','parbatpur'=>'kolkata','ramkrishna'=>'kolkata','rangia'=>'kolkata','sarthebari'=>'kolkata','sibsagar'=>'kolkata','silchar'=>'kolkata','sivsagar'=>'kolkata','sonitpur'=>'kolkata','sualkuchi'=>'kolkata','tezpur'=>'kolkata','tinsukia'=>'kolkata','titabor'=>'kolkata','araria'=>'kolkata','arrah'=>'kolkata','arwal'=>'kolkata','aurangabad-bihar'=>'kolkata','bagana'=>'kolkata','banka'=>'kolkata','begusarai'=>'kolkata','benwalia'=>'kolkata','bettiah'=>'kolkata','bhagalpur'=>'kolkata','bhatta bazar'=>'kolkata','bhawanathpur'=>'kolkata','bhojpur'=>'kolkata','bijaynagar-uttar pradesh'=>'kolkata','bodhgaya'=>'kolkata','buniyad ganj'=>'kolkata','buxar'=>'kolkata','chapra h o'=>'kolkata','darbhanga'=>'kolkata','eastchamparan'=>'kolkata','fatehpur'=>'kolkata','gaya'=>'kolkata','gopalganj'=>'kolkata','hajipur'=>'kolkata','jamui'=>'kolkata','jehanabad'=>'kolkata','jhanjharpur'=>'kolkata','kadirganj'=>'kolkata','kaimur'=>'kolkata','katihar'=>'kolkata','khagaria'=>'kolkata','kishanganj'=>'kolkata','koilwar'=>'kolkata','kurhani'=>'kolkata','laheriasarai'=>'kolkata','lakhisarai'=>'kolkata','madhepura'=>'kolkata','madhubani'=>'kolkata','motihari'=>'kolkata','motipur'=>'kolkata','munger'=>'kolkata','muzaffarpur'=>'mumbai','nalanda'=>'kolkata','nathnagar'=>'kolkata','nawada'=>'kolkata','patna'=>'kolkata','purnia'=>'kolkata','pusa'=>'kolkata','rajgir'=>'kolkata','rohtas'=>'kolkata','sabour'=>'kolkata','saharsa'=>'kolkata','samastipur'=>'kolkata','saraidhela'=>'kolkata','saran'=>'kolkata','sasaram'=>'kolkata','sheikhpura'=>'kolkata','sheohar'=>'kolkata','sherghati'=>'kolkata','sitamarhi'=>'kolkata','siwan'=>'kolkata','supaul'=>'kolkata','teghra'=>'kolkata','vaishali'=>'kolkata','west champaran'=>'kolkata','chandigarh'=>'chandigarh','other / not listed##chandigarh'=>'chandigarh','ambikapur'=>'mumbai','baikunthpur'=>'mumbai','bastar'=>'mumbai','bhilai'=>'mumbai','bijapur-chhattisgarh'=>'mumbai','bilaspur'=>'mumbai','bilaspur-chhattisgarh'=>'kolkata','dallirajhara'=>'mumbai','dantewada'=>'mumbai','dhamtari'=>'kolkata','durg'=>'mumbai','jagdalpur'=>'mumbai','janjgir champa'=>'mumbai','jashpur'=>'mumbai','kanker'=>'mumbai','kawardha'=>'mumbai','korba'=>'kolkata','korea '=>'mumbai','kota-chhattisgarh'=>'mumbai','mahasamund'=>'mumbai','manendragarh'=>'mumbai','murra'=>'mumbai','narayanpur'=>'mumbai','pali-chhattisgarh'=>'mumbai','patan-chhattisgarh'=>'mumbai','raigarh-chhattisgarh'=>'kolkata','raipur-chhattisgarh'=>'kolkata','rajnandgaon'=>'mumbai','sakti'=>'mumbai','sankari'=>'mumbai','surguja'=>'mumbai','udaipur-chhattisgarh'=>'mumbai','silvassa'=>'ahmedabad','daman'=>'ahmedabad','daman and diu'=>'ahmedabad','diu'=>'ahmedabad','delhi'=>'delhi','goa'=>'pune','adipur'=>'ahmedabad','ahmedabad'=>'ahmedabad','ahwa'=>'ahmedabad','amreli'=>'ahmedabad','anand'=>'ahmedabad','ankleshwar'=>'ahmedabad','banaskantha'=>'ahmedabad','baroda'=>'ahmedabad','barwala'=>'ahmedabad','bavla'=>'ahmedabad','bhachau'=>'ahmedabad','bhakti nagar'=>'ahmedabad','bharuch'=>'ahmedabad','bhavnagar'=>'ahmedabad','bhuj'=>'ahmedabad','chhatral'=>'ahmedabad','daang'=>'ahmedabad','dahod'=>'ahmedabad','dwarka'=>'ahmedabad','dwarka-gujarat'=>'ahmedabad','gandhidham'=>'ahmedabad','gandhinagar-gujarat'=>'ahmedabad','gopalpuri'=>'ahmedabad','himatnagar'=>'ahmedabad','jamnagar'=>'ahmedabad','junagadh'=>'ahmedabad','kandla'=>'ahmedabad','khambhat'=>'ahmedabad','kheda'=>'ahmedabad','kutch'=>'ahmedabad','lakadia'=>'ahmedabad','mahemdavad'=>'ahmedabad','mandvi'=>'ahmedabad','mankuva'=>'ahmedabad','mehsana'=>'ahmedabad','morbi'=>'ahmedabad','mundra'=>'ahmedabad','nadiad'=>'ahmedabad','naliya'=>'ahmedabad','narmada'=>'ahmedabad','navagarh'=>'ahmedabad','navsari'=>'ahmedabad','palanpur'=>'ahmedabad','panchmahal'=>'ahmedabad','patan-gujarat'=>'ahmedabad','porbandar'=>'ahmedabad','rajkot'=>'ahmedabad','rajpipla'=>'ahmedabad','sabarkantha'=>'ahmedabad','sanand'=>'ahmedabad','saputara'=>'ahmedabad','sukhpar'=>'ahmedabad','surat'=>'ahmedabad','surendra nagar-gujarat'=>'ahmedabad','talaja'=>'ahmedabad','tapi'=>'ahmedabad','thangadh'=>'ahmedabad','umargam'=>'ahmedabad','vadodara'=>'ahmedabad','valsad'=>'ahmedabad','vapi'=>'ahmedabad','veraval'=>'ahmedabad','vijapur'=>'ahmedabad','visnagar'=>'ahmedabad','vyara'=>'ahmedabad','wankaner'=>'ahmedabad','adampur'=>'delhi','ambala'=>'delhi','bahadurgarh'=>'delhi','bhiwani'=>'delhi','dharuhera'=>'delhi','faridabad'=>'delhi','fatehabad'=>'delhi','gurgaon'=>'delhi','hansi'=>'delhi','hissar'=>'delhi','jhajjar'=>'delhi','jind'=>'delhi','julana'=>'delhi','kaithal'=>'delhi','kalka'=>'delhi','karnal'=>'delhi','kundli'=>'delhi','kurukshetra'=>'delhi','ladwa'=>'delhi','mahendergarh'=>'delhi','mahendernagar'=>'delhi','narnaul'=>'delhi','palwal'=>'delhi','panchkula'=>'chandigarh','panipat'=>'delhi','pehowa'=>'delhi','radaur'=>'delhi','rania'=>'delhi','rewari'=>'delhi','rohtak'=>'delhi','sirsa punjab'=>'delhi','sirsa-haryana'=>'delhi','sirsa-punjab'=>'delhi','sonepat'=>'delhi','taraori'=>'delhi','tohana'=>'delhi','yamunanagar'=>'delhi','arki'=>'chandigarh','baijnath'=>'chandigarh','bharmour'=>'chandigarh','bilaspur-himachal pradesh'=>'chandigarh','chail'=>'chandigarh','chamba'=>'chandigarh','dalhousie'=>'chandigarh','dharampur'=>'chandigarh','dharamshala'=>'chandigarh','ghumarwin'=>'chandigarh','hamirpur-himachal pradesh'=>'chandigarh','hatkoti'=>'chandigarh','kala amb'=>'chandigarh','kangra'=>'chandigarh','kinnaur'=>'chandigarh','kufri'=>'chandigarh','kullu'=>'chandigarh','manali'=>'chandigarh','mandi'=>'chandigarh','nadaun'=>'chandigarh','nahan'=>'chandigarh','nalagarh'=>'chandigarh','nurpur'=>'chandigarh','paonta sahib'=>'chandigarh','parwanoo'=>'chandigarh','renuka ji'=>'chandigarh','rewalsar'=>'chandigarh','sarkaghat'=>'chandigarh','shimla'=>'chandigarh','sirmaur'=>'chandigarh','solan'=>'chandigarh','spiti'=>'chandigarh','tabo'=>'chandigarh','theog'=>'chandigarh','una'=>'chandigarh','akhnoor'=>'delhi','anantnag'=>'delhi','baramulla'=>'delhi','budgam'=>'delhi','jammu'=>'delhi','kargil'=>'delhi','kashmir'=>'delhi','kathua'=>'delhi','katra'=>'delhi','kupwara'=>'delhi','ladakh '=>'delhi','leh ladakh'=>'delhi','nagrota'=>'delhi','pulwama'=>'delhi','rajouri'=>'delhi','sopore'=>'delhi','srinagar'=>'delhi','udhampur'=>'delhi','vaishnodevi'=>'delhi','barhi'=>'kolkata','bermo'=>'kolkata','bokaro'=>'kolkata','bokaro steel city'=>'kolkata','chaibasa'=>'kolkata','chakradharpur'=>'kolkata','chas'=>'kolkata','chatra'=>'kolkata','chirkunda'=>'kolkata','daltanganj'=>'kolkata','deoghar'=>'kolkata','dhanbad'=>'mumbai','dumka'=>'kolkata','east singhbhum'=>'kolkata','garhwa'=>'kolkata','ghatshila'=>'kolkata','giridih'=>'kolkata','godda'=>'kolkata','gumla'=>'kolkata','hazaribagh'=>'kolkata','jamshedpur'=>'kolkata','jamtara'=>'kolkata','jhumritelaiya'=>'kolkata','koderma'=>'kolkata','latehar'=>'kolkata','lohardaga'=>'kolkata','mihijam'=>'kolkata','pakaur'=>'kolkata','palamu'=>'kolkata','phusro bazar'=>'kolkata','ramgarh'=>'kolkata','ranchi'=>'kolkata','sahibganj'=>'kolkata','seraikela kharsawan'=>'kolkata','simdega'=>'kolkata','west singhbhum'=>'kolkata','aigali'=>'bangalore','amingad'=>'bangalore','ammasandra'=>'bangalore','anavatti'=>'bangalore','ankola'=>'bangalore','bagalkot'=>'bangalore','balkur'=>'bangalore','bangalore'=>'bangalore','bangarpet'=>'bangalore','bankal'=>'bangalore','bankikodla'=>'bangalore','bannur'=>'bangalore','belekeri'=>'bangalore','belgaum'=>'bangalore','bellad bagewadi'=>'bangalore','bellary'=>'bangalore','belthangady'=>'bangalore','bengaluru'=>'bangalore','bhadravathi'=>'bangalore','bhatkal'=>'bangalore','bidar'=>'bangalore','bijapur-karnataka'=>'bangalore','chamarajanagar'=>'bangalore','chamrajnagar'=>'bangalore','channapatna'=>'bangalore','chickballapur'=>'bangalore','chikamagalur'=>'bangalore','chikmagalur'=>'bangalore','chintamani'=>'bangalore','chitradurga'=>'bangalore','coorg'=>'bangalore','dakshina kannada'=>'bangalore','dandeli'=>'bangalore','davangere'=>'bangalore','dharwad'=>'bangalore','gadag'=>'bangalore','gandhinagar-karnataka'=>'bangalore','ghataprabha'=>'bangalore','ginigera'=>'bangalore','gulbarga'=>'bangalore','haliyal'=>'bangalore','hassan'=>'bangalore','haveri'=>'bangalore','heggarni'=>'bangalore','herur'=>'bangalore','hirehally'=>'bangalore','holealur'=>'bangalore','honavar'=>'bangalore','honnali'=>'bangalore','horanadu'=>'bangalore','hosaritti'=>'bangalore','hospet'=>'bangalore','hubli'=>'bangalore','itagi'=>'bangalore','janmane'=>'bangalore','jawali'=>'bangalore','jnanaganga'=>'bangalore','jodumarga'=>'bangalore','jog falls'=>'bangalore','kaiga'=>'bangalore','kaimara'=>'bangalore','kankanwadi'=>'bangalore','karwar'=>'bangalore','kavalagi'=>'bangalore','kawalgundi'=>'bangalore','kembalalu'=>'bangalore','kemmanagundi'=>'bangalore','kittur'=>'bangalore','kodagu'=>'bangalore','kolar'=>'bangalore','kolar gold fields (kgf)'=>'bangalore','koppa'=>'bangalore','koppal'=>'bangalore','koratagere'=>'bangalore','kumta'=>'bangalore','m k hubli'=>'bangalore','madanbhavi'=>'bangalore','madangeri'=>'bangalore','madekeri'=>'bangalore','madikeri'=>'bangalore','malgi'=>'bangalore','mandya'=>'bangalore','mangalore'=>'bangalore','manur'=>'bangalore','maralur'=>'bangalore','melkote'=>'bangalore','mudalgi'=>'bangalore','mulki'=>'bangalore','mundgod'=>'bangalore','mysore'=>'bangalore','nandangadda'=>'bangalore','navalgund'=>'bangalore','nerli'=>'bangalore','nidasoshi'=>'bangalore','pandaravally'=>'bangalore','puttur'=>'bangalore','raichur'=>'bangalore','ramanagara'=>'bangalore','saligrama'=>'bangalore','sambargi'=>'bangalore','saundalga'=>'bangalore','saundatti'=>'bangalore','seegur'=>'bangalore','shimoga'=>'bangalore','shirali'=>'bangalore','shirguppi'=>'bangalore','shivaganga'=>'bangalore','shravanabelagola'=>'bangalore','shriwada'=>'bangalore','siddapur'=>'bangalore','sirsi'=>'bangalore','station road chitradurga'=>'bangalore','thirthahalli'=>'bangalore','tigadi-belgaum'=>'bangalore','tiptur'=>'bangalore','toikotta'=>'bangalore','tumkur'=>'bangalore','udupi'=>'bangalore','virajpet'=>'bangalore','yadgir'=>'bangalore','yelandur'=>'bangalore','yeliyur'=>'bangalore','yellapur'=>'bangalore','adoor'=>'coimbatore','alappuzha'=>'coimbatore','alleppey'=>'coimbatore','ayur'=>'coimbatore','calicut'=>'coimbatore','cannanore'=>'coimbatore','cochi'=>'coimbatore','cochin'=>'coimbatore','ernakulam'=>'coimbatore','idukki'=>'coimbatore','kannur'=>'coimbatore','kasaragod'=>'coimbatore','kasargod'=>'coimbatore','kochi'=>'coimbatore','kollam'=>'coimbatore','kottayam'=>'coimbatore','kovalam'=>'coimbatore','kozhikode'=>'coimbatore','kumarakom'=>'coimbatore','kumbanadu'=>'coimbatore','malappuram'=>'coimbatore','munnar'=>'coimbatore','nazareth'=>'coimbatore','palakkad'=>'coimbatore','palghat'=>'coimbatore','pathanamthitta'=>'coimbatore','periyar'=>'coimbatore','quilon'=>'coimbatore','thalassery'=>'coimbatore','thiruvalla'=>'coimbatore','thiruvananthapuram'=>'coimbatore','thrissur'=>'coimbatore','tiruvananthapuram'=>'coimbatore','trichur'=>'coimbatore','trissur'=>'coimbatore','trivandrum'=>'coimbatore','vadakara'=>'coimbatore','wayanad'=>'coimbatore','agathy '=>'coimbatore','lakshadweep'=>'coimbatore','agar'=>'mumbai','ajaigarh'=>'mumbai','alirajpur'=>'mumbai','ambah'=>'mumbai','amla'=>'mumbai','anjad'=>'mumbai','anuppur'=>'mumbai','aron'=>'mumbai','arone'=>'mumbai','ashoknagar'=>'mumbai','ashta'=>'mumbai','atner'=>'mumbai','babaichichli'=>'mumbai','badamalhera'=>'mumbai','badarwsas'=>'mumbai','balaghat'=>'mumbai','bandhavgarh'=>'mumbai','barwani'=>'mumbai','betul'=>'mumbai','bhind'=>'mumbai','bhopal'=>'mumbai','biaora'=>'mumbai','burhanpur'=>'mumbai','chhatarpur'=>'mumbai','chhindwara'=>'mumbai','chitrakoot-mp'=>'mumbai','damoh'=>'mumbai','datia'=>'mumbai','dewas'=>'mumbai','dhar'=>'mumbai','dharampuri'=>'mumbai','dindori'=>'mumbai','gandhinagar-madhya pradesh'=>'mumbai','guna'=>'mumbai','gwalior'=>'mumbai','harda'=>'mumbai','hoshangabad'=>'mumbai','indore'=>'mumbai','itarsi'=>'mumbai','jabalpur'=>'mumbai','jaora'=>'mumbai','jhabua '=>'mumbai','katni'=>'mumbai','khajuraho'=>'mumbai','khandwa'=>'mumbai','khargone'=>'mumbai','mandla'=>'mumbai','mandsaur'=>'mumbai','morena'=>'mumbai','narsinghpur'=>'mumbai','neemuch'=>'mumbai','omkareshwar'=>'mumbai','pachmarhi'=>'mumbai','panna'=>'mumbai','raipur-madhya pradesh'=>'mumbai','raisen'=>'mumbai','rajgarh'=>'mumbai','ratlam'=>'mumbai','rewa'=>'mumbai','sagar'=>'mumbai','sanchi'=>'mumbai','satna'=>'mumbai','sehore'=>'mumbai','sendhwa'=>'mumbai','seoni'=>'mumbai','shahdol'=>'mumbai','shajapur'=>'mumbai','sheopur'=>'mumbai','shivpuri'=>'mumbai','sidhi'=>'mumbai','singrauli'=>'mumbai','sironj'=>'mumbai','tikamgarh'=>'mumbai','ujjain'=>'mumbai','umariya'=>'mumbai','vidisha'=>'mumbai','aheri'=>'pune','ahmednagar'=>'pune','ahmedpur'=>'pune','ajara'=>'pune','akkalkot'=>'pune','akola'=>'pune','alandi'=>'pune','alibaug'=>'pune','ambad'=>'pune','amboli'=>'pune','amravati'=>'pune','aurangabad'=>'pune','aurangabad-maharashtra'=>'pune','baramati'=>'pune','beed'=>'pune','bhandara'=>'pune','bhigwan'=>'pune','bhir'=>'pune','bhiwapur'=>'pune','bhokardan'=>'pune','bhor'=>'pune','bhudargad '=>'pune','bhusawal'=>'pune','biloli'=>'pune','buldhana'=>'pune','chacher'=>'pune','chamorshi '=>'pune','chandrapur'=>'pune','chikhaldara'=>'pune','dapoli'=>'pune','daund'=>'pune','deoli'=>'pune','desaiganj'=>'pune','dhule'=>'pune','diveagar'=>'pune','gadchiroli'=>'pune','gangakhed'=>'pune','ganpatipule'=>'pune','ghatanji'=>'pune','ghoti'=>'pune','gondia'=>'pune','guhagar'=>'pune','harihareshwar'=>'pune','hingoli'=>'pune','ichalkaranji'=>'pune','indapur'=>'pune','jalgaon'=>'pune','jalna'=>'pune','jejuri'=>'pune','kashid'=>'pune','khandala'=>'pune','khed shivapur'=>'pune','kolhapur'=>'pune','kopargaon'=>'pune','latur'=>'pune','lonavala'=>'pune','mahabaleshwar'=>'pune','malegaon'=>'pune','malshej'=>'pune','malvan'=>'pune','matheran'=>'pune','mumbai'=>'mumbai','murud'=>'pune','nagpur'=>'pune','nanded'=>'pune','nandurbar'=>'pune','narayangaon'=>'pune','nashik'=>'pune','navi mumbai'=>'mumbai','niphad'=>'pune','nira'=>'pune','osmanabad'=>'pune','paithan'=>'pune','pali-maharashtra'=>'pune','panchgani'=>'pune','pandharpur'=>'pune','panhala'=>'pune','panvel'=>'mumbai','parbhani'=>'pune','parli vaijnath'=>'pune','pune'=>'pune','raigad'=>'pune','raigad-maharashtra'=>'mumbai','raigarh'=>'pune','ranjhangaon'=>'pune','ratnagiri'=>'pune','sangli'=>'pune','saswad'=>'pune','satara'=>'pune','shirdi'=>'pune','shrirampur'=>'pune','shrivardhan'=>'pune','sindhudurg'=>'pune','solapur'=>'pune','thane'=>'mumbai','toranmal'=>'pune','udgir'=>'pune','vaduj'=>'pune','varvand'=>'pune','vasai'=>'mumbai','velhe'=>'pune','walchandnagar'=>'pune','wardha'=>'pune','warora'=>'pune','washim'=>'pune','yavatmal'=>'pune','yeola'=>'pune','bishnupur'=>'kolkata','churachandpur'=>'kolkata','imphal'=>'kolkata','mantripukhri'=>'kolkata','porompat'=>'kolkata','senapati'=>'kolkata','tamenglong'=>'kolkata','thoubal'=>'kolkata','assam rifles'=>'kolkata','burnihat'=>'kolkata','east khasi hills'=>'kolkata','jowai'=>'kolkata','lower chandmari'=>'kolkata','nongpoh'=>'kolkata','shillong'=>'kolkata','tura'=>'kolkata','west khasi hills'=>'kolkata','aizawl'=>'kolkata','champhai'=>'kolkata','khawzawl'=>'kolkata','kolasib'=>'kolkata','lunglei'=>'kolkata','mizoram'=>'kolkata','ramhlun'=>'kolkata','sairang'=>'kolkata','serchhip'=>'kolkata','zemabawk'=>'kolkata','dimapur'=>'kolkata','kiphire'=>'kolkata','kohima'=>'kolkata','longleng'=>'kolkata','mokokchung'=>'kolkata','mon'=>'kolkata','peren'=>'kolkata','phek'=>'kolkata','tuensang'=>'kolkata','wokha'=>'kolkata','zunheboto'=>'kolkata','angul'=>'kolkata','aska'=>'kolkata','athamallick'=>'kolkata','bairoi'=>'kolkata','balangir'=>'kolkata','balasore'=>'kolkata','baleshwar'=>'kolkata','baliapal'=>'kolkata','balikuda'=>'kolkata','bandrak'=>'kolkata','barbil'=>'kolkata','bargarh'=>'kolkata','baripada'=>'kolkata','barkot'=>'kolkata','barpali'=>'kolkata','barungadai'=>'kolkata','belapadapatna'=>'kolkata','belda'=>'kolkata','belpahar'=>'kolkata','berhampur-orrisa'=>'kolkata','bhadrak'=>'kolkata','bhanjpur'=>'kolkata','bhawanipatna'=>'kolkata','bhuban'=>'kolkata','bhubaneshwar'=>'kolkata','binjharpur'=>'kolkata','biramaharajpur'=>'kolkata','bolangir'=>'kolkata','boudh'=>'kolkata','burla-orrisa'=>'kolkata','cuttack'=>'kolkata','damanjori'=>'kolkata','debagarh'=>'kolkata','dhanmandal'=>'kolkata','dhenkanal'=>'kolkata','dunguripali'=>'kolkata','gajapati'=>'kolkata','ganjam'=>'kolkata','garabandha'=>'kolkata','golanthara'=>'kolkata','gopalpur'=>'kolkata','gosaninuagaon'=>'kolkata','gunupur'=>'kolkata','jagatsinghapur'=>'kolkata','jajpur'=>'kolkata','jaleswarpur'=>'kolkata','jayantipur'=>'kolkata','jeypore'=>'kolkata','jharsuguda'=>'kolkata','kalahandi'=>'kolkata','kandhamal'=>'kolkata','kansbahal'=>'kolkata','karanjia'=>'kolkata','kendrapada'=>'kolkata','kendrapara'=>'kolkata','keonjhar'=>'kolkata','keonjhargarh'=>'kolkata','khurda'=>'kolkata','kishoreganj-jharkhand'=>'kolkata','konark'=>'kolkata','koraput'=>'kolkata','malkangiri'=>'kolkata','manamunda'=>'kolkata','mayurbhanj'=>'kolkata','melchhamunda'=>'kolkata','murusundhi'=>'kolkata','nabarangpur'=>'kolkata','nalco nagar'=>'kolkata','narayanpatna'=>'kolkata','nayagarh'=>'kolkata','niali'=>'kolkata','nuapada'=>'kolkata','pallahara'=>'kolkata','paradeep port'=>'kolkata','paradip'=>'kolkata','pattamundai'=>'kolkata','phulbani bazar'=>'kolkata','puri'=>'kolkata','rajgangpur'=>'kolkata','ramgarh-jharkhand'=>'kolkata','rayagada'=>'kolkata','rengali'=>'kolkata','rourkela'=>'kolkata','sambalpur'=>'kolkata','seranga'=>'kolkata','sonepur'=>'kolkata','sonepur bazari'=>'kolkata','sonepur rampur-orissa'=>'kolkata','sudaragarh'=>'kolkata','sundargarh'=>'kolkata','talcher'=>'kolkata','udit nagar'=>'kolkata','ulunda'=>'kolkata','karaikal'=>'chennai','mahe'=>'chennai','pondicherry'=>'chennai','thatcher-pondicherry'=>'chennai','abohar'=>'chandigarh','amritsar'=>'chandigarh','banga'=>'chandigarh','barnala'=>'chandigarh','batala'=>'chandigarh','bathinda'=>'chandigarh','bhatinda'=>'chandigarh','chhabewal'=>'chandigarh','dhanaula'=>'chandigarh','dhuri'=>'chandigarh','dina nagar'=>'chandigarh','faridkot'=>'chandigarh','fatehgarh sahib'=>'chandigarh','ferozepur'=>'chandigarh','gidderbaha'=>'chandigarh','gurdaspur'=>'chandigarh','hoshiarpur'=>'chandigarh','jagraon'=>'chandigarh','jalandhar'=>'chandigarh','kapurthala'=>'chandigarh','khanna'=>'chandigarh','kharar'=>'chandigarh','kotkapura'=>'chandigarh','ludhiana'=>'chandigarh','malerkotla'=>'chandigarh','malout'=>'chandigarh','mansa'=>'chandigarh','moga'=>'chandigarh','mohali'=>'chandigarh','morinda'=>'chandigarh','muktsar'=>'chandigarh','nabha'=>'chandigarh','nangal'=>'chandigarh','nawanshahr'=>'chandigarh','pathankot'=>'chandigarh','patiala'=>'chandigarh','phagwara'=>'chandigarh','rajpura'=>'chandigarh','rayya'=>'chandigarh','ropar'=>'chandigarh','samadh bhai'=>'chandigarh','samana'=>'chandigarh','samrala'=>'chandigarh','sangrur'=>'chandigarh','sheron'=>'chandigarh','sunam'=>'chandigarh','talwandi bhai'=>'chandigarh','talwandi sabo'=>'chandigarh','talwara'=>'chandigarh','tarn taran'=>'chandigarh','tibri'=>'chandigarh','urmar tanda'=>'chandigarh','zira'=>'chandigarh','zirakpur'=>'chandigarh','abu road'=>'jaipur','ajmer'=>'jaipur','alwar'=>'jaipur','arain'=>'jaipur','balotra'=>'jaipur','banswara'=>'jaipur','baran'=>'jaipur','barmer'=>'jaipur','beawar'=>'jaipur','bharatpur'=>'jaipur','bhilwara'=>'jaipur','bhiwadi'=>'jaipur','bikaner'=>'jaipur','bilara'=>'jaipur','brahmabad'=>'jaipur','bundi'=>'jaipur','chittorgarh'=>'jaipur','chomu'=>'jaipur','churu'=>'jaipur','dausa'=>'jaipur','degana'=>'jaipur','dholpur'=>'jaipur','dungarpur'=>'jaipur','falna'=>'jaipur','gadepan'=>'jaipur','gaumukh'=>'jaipur','gulabpura'=>'jaipur','hanumangarh'=>'jaipur','hindaun'=>'jaipur','jaipur'=>'jaipur','jaisalmer'=>'jaipur','jalore'=>'jaipur','jhalawar'=>'jaipur','jhujhunu'=>'jaipur','jhunjhunu'=>'jaipur','jodhpur'=>'jaipur','kankroli'=>'jaipur','karauli'=>'jaipur','kota-rajasthan'=>'jaipur','kuchaman'=>'jaipur','magarra'=>'jaipur','malpura'=>'jaipur','merta'=>'jaipur','mount abu'=>'jaipur','nagaur'=>'jaipur','nathdwara'=>'jaipur','nawalgarh'=>'jaipur','pali marwar'=>'jaipur','pali-rajasthan'=>'jaipur','pilani'=>'jaipur','pratapgarh'=>'jaipur','pushkar'=>'jaipur','raisinghnagar'=>'jaipur','raj bhawan'=>'jaipur','rajsamand'=>'jaipur','ramganj mandi'=>'jaipur','ratangarh'=>'jaipur','sanchore'=>'jaipur','sawai madhopur'=>'jaipur','shankhwali'=>'jaipur','sheoganj'=>'jaipur','sikar'=>'jaipur','sirohi'=>'jaipur','sojat'=>'jaipur','sojat city'=>'jaipur','sri ganganagar-rajasthan'=>'jaipur','suratgarh'=>'jaipur','tonk'=>'jaipur','udaipur-rajasthan'=>'jaipur','gangtok'=>'kolkata','mangan'=>'kolkata','rangpo'=>'kolkata','ravangla'=>'kolkata','todong'=>'kolkata','acharapakkam'=>'chennai','alangudi'=>'chennai','ambasamudram'=>'chennai','ambur'=>'chennai','anaicut'=>'chennai','arakkonam'=>'chennai','arani'=>'chennai','aravakurichi'=>'chennai','archalur'=>'chennai','arcot'=>'chennai','ariyalur'=>'chennai','arruppukottia'=>'chennai','aruppukottai'=>'chennai','attur salem'=>'coimbatore','aundipatti'=>'chennai','avanashi'=>'coimbatore','ayyampet'=>'chennai','bagayam'=>'chennai','bhavani'=>'chennai','bhawni'=>'chennai','bhoothapandy'=>'chennai','bitherkad'=>'chennai','chengalpattu'=>'chennai','chengam'=>'chennai','chennai'=>'chennai','chidambaram'=>'chennai','coimbatore'=>'coimbatore','coonoor'=>'coimbatore','cuddalore'=>'chennai','cumbum'=>'chennai','devakottai'=>'chennai','devarkulam'=>'chennai','dharampuram'=>'coimbatore','dharapuram'=>'coimbatore','dharmapuri'=>'coimbatore','dindigul'=>'chennai','erode'=>'coimbatore','gingee'=>'chennai','gobichettipalayam'=>'coimbatore','gobichettipalayam erode'=>'coimbatore','gudalur'=>'coimbatore','gudiyattam'=>'chennai','harur'=>'coimbatore','hosur'=>'coimbatore','jayamkondacholapuram'=>'chennai','jolarpettai'=>'chennai','kadambur'=>'chennai','kadayanallur'=>'chennai','kalambur'=>'chennai','kalavai'=>'chennai','kallakurichi'=>'chennai','kallimandayam'=>'chennai','kanchipuram'=>'chennai','kanchipurram'=>'chennai','kangayam'=>'coimbatore','kaniyambadi'=>'chennai','kannivadi'=>'chennai','kanyakumari'=>'chennai','karaikudi'=>'chennai','karur'=>'chennai','katpadi'=>'chennai','kaveripakkam'=>'chennai','keeranur'=>'chennai','kilpennathur'=>'chennai','kodaikanal'=>'coimbatore','kodumudi'=>'chennai','komarapalayam'=>'chennai','kovai'=>'coimbatore','kovilpatti'=>'chennai','krishnagiri'=>'coimbatore','kulasekharam'=>'chennai','kumbakonam'=>'chennai','kuzhithurai'=>'chennai','madanur'=>'chennai','madras'=>'chennai','madurai'=>'chennai','madurantakam'=>'chennai','mahabalipuram'=>'chennai','manamadurai'=>'chennai','mannargudi'=>'chennai','mayiladuthurai'=>'chennai','megamalai'=>'chennai','melmaruvathur'=>'chennai','melur toothukudi-tamil nadu'=>'chennai','melur-tamil nadu'=>'chennai','mettupalayam coimbatore'=>'coimbatore','mettur'=>'coimbatore','nagapattinam'=>'chennai','nagarcoil'=>'chennai','nagercoil'=>'chennai','namakkal'=>'coimbatore','nambiyur'=>'chennai','nannilam'=>'chennai','neyveli'=>'chennai','neyyoor'=>'chennai','nilakottai'=>'chennai','nilgiri'=>'coimbatore','ooty'=>'coimbatore','padukkotia'=>'chennai','palani'=>'coimbatore','palladam'=>'coimbatore','pallikonda'=>'chennai','panruti'=>'chennai','papanasam'=>'chennai','paramakudi'=>'chennai','paramathi'=>'coimbatore','patteeswaram'=>'chennai','pattukottai'=>'chennai','perambalur'=>'chennai','pernambut'=>'chennai','perumbalai'=>'chennai','perundurai'=>'coimbatore','pollachi'=>'coimbatore','polur'=>'chennai','ponbethi'=>'chennai','ponnamaravathy'=>'chennai','poovalur'=>'chennai','pudukkottai'=>'chennai','rajapalayam'=>'chennai','ramanathapuram'=>'chennai','rameshwaram'=>'chennai','rameswaram'=>'chennai','ranipet'=>'chennai','rasipuram'=>'chennai','salem'=>'coimbatore','sathyamangalam'=>'coimbatore','sendurai'=>'chennai','shencottah'=>'chennai','sholinghur'=>'chennai','sholingur'=>'chennai','shoolagiri'=>'chennai','sirugudi'=>'chennai','sivaganga'=>'chennai','sivagangai'=>'chennai','sivakasi'=>'chennai','srirangam'=>'chennai','srivilliputtur'=>'chennai','tenkasi'=>'chennai','thanjavur'=>'chennai','theni'=>'chennai','thiruchengode'=>'coimbatore','thirumayam'=>'chennai','thiruvarur'=>'chennai','thoothukudi'=>'chennai','thovalai'=>'chennai','thuckalay'=>'chennai','tindinivam'=>'chennai','tindivanam'=>'chennai','tiruchendur'=>'chennai','tiruchengode'=>'coimbatore','tiruchengodu'=>'coimbatore','tirunelveli'=>'chennai','tirupur'=>'coimbatore','tiruttani'=>'chennai','tiruvallur'=>'chennai','tiruvanmalai'=>'chennai','tiruvanmalaia'=>'chennai','tiruvannamalai'=>'chennai','tiruvarur'=>'chennai','toothukudi-tamil nadu'=>'chennai','trichy'=>'coimbatore','triuvannamalai'=>'chennai','tuticorin'=>'chennai','udumalpet'=>'coimbatore','ulundurpet'=>'chennai','uthiyur'=>'chennai','uthukuli'=>'chennai','vallam'=>'chennai','vaniyambadi'=>'chennai','velankanni'=>'chennai','vellore'=>'chennai','vijayapuram'=>'chennai','vilathikulam'=>'chennai','villupuram'=>'chennai','virudhunagar'=>'chennai','vriddhachalam'=>'chennai','yelagiri hills'=>'chennai','yercaud'=>'coimbatore','agartala'=>'kolkata','ambasa'=>'kolkata','belonia'=>'kolkata','dhaleswar'=>'kolkata','dharma nagar h o'=>'kolkata','kumarghat'=>'kolkata','north tripura'=>'kolkata','radhakishorepur'=>'kolkata','south tripura'=>'kolkata','tripura'=>'kolkata','west tripura'=>'kolkata','agra'=>'delhi','aligarh'=>'delhi','allahabad'=>'delhi','ambedkar nagar'=>'delhi','ambedkar nagar-uttar pradesh'=>'delhi','amila'=>'delhi','amroha'=>'delhi','atarra'=>'delhi','auraiya'=>'delhi','ayodhya'=>'delhi','azamgarh'=>'delhi','badaun'=>'delhi','baghpat'=>'delhi','bahraich'=>'delhi','ballia'=>'delhi','balrampur'=>'delhi','banaras'=>'delhi','banda'=>'delhi','barabanki'=>'delhi','baraut'=>'delhi','bareilly'=>'delhi','basti'=>'delhi','bhadohi'=>'delhi','bijnor'=>'delhi','bithoor '=>'delhi','budaun'=>'delhi','bulandshahr'=>'delhi','chandauli'=>'delhi','chitrakoot-up'=>'delhi','chitrakoot-up '=>'delhi','chopan'=>'delhi','chunar'=>'delhi','deoria'=>'delhi','dhampur'=>'delhi','etah'=>'delhi','etawah'=>'delhi','faizabad'=>'delhi','farrukhabad'=>'delhi','fatehabad - uttar pradesh'=>'delhi','fatehabad-uttar pradesh'=>'delhi','fatehpur sikri'=>'delhi','fatehpur-uttar pradesh'=>'delhi','firozabad'=>'delhi','gajraula'=>'delhi','garhmukteshwar'=>'delhi','ghaziabad'=>'delhi','ghazipur'=>'delhi','gonda'=>'delhi','gorakhpur'=>'delhi','hamirpur'=>'delhi','hapur'=>'delhi','hardoi'=>'delhi','hathras'=>'delhi','jagdishpur'=>'delhi','jainpur'=>'delhi','jalaun'=>'delhi','jaunpur'=>'delhi','jhansi'=>'delhi','jharaka'=>'delhi','jyotiba phoolay'=>'delhi','kannauj'=>'delhi','kanpur'=>'delhi','kasganj'=>'delhi','kashi'=>'delhi','kaushambi'=>'delhi','khatauli'=>'delhi','kosi kalan'=>'delhi','kushinagar'=>'delhi','lakhimpur kheri'=>'delhi','lalitpur'=>'delhi','lucknow'=>'delhi','maharajganj'=>'delhi','mahoba'=>'delhi','mainpuri'=>'delhi','mariahu'=>'delhi','mathura'=>'delhi','mau'=>'delhi','meerut'=>'delhi','mirzapur'=>'delhi','mirzapur bandura'=>'delhi','moradabad'=>'delhi','muzaffarnagar'=>'delhi','neoli'=>'delhi','noida'=>'delhi','orai'=>'delhi','padrauna'=>'delhi','pilibhit'=>'delhi','prayag'=>'delhi','prem nagar-rohtak'=>'delhi','raebareli'=>'delhi','rampur'=>'delhi','rasra'=>'delhi','saharanpur'=>'delhi','sambhal'=>'delhi','sant kabir nagar'=>'delhi','sant ravidas nagar bhadohi'=>'delhi','sarnath'=>'delhi','shahjahanpur'=>'delhi','shamli'=>'delhi','shravasti'=>'delhi','sidhart nagar'=>'delhi','sirsa-uttar pradesh'=>'delhi','sitapur'=>'delhi','sonbhadra'=>'delhi','sravasti'=>'delhi','sultanpur'=>'delhi','tehri garhwal'=>'delhi','unnao'=>'delhi','varanasi'=>'delhi','vrindavan'=>'delhi','almora'=>'delhi','anjni sain'=>'delhi','badrinath'=>'delhi','bageshwar'=>'delhi','bangapani'=>'delhi','bazpur'=>'delhi','bhagwanpur'=>'delhi','chamoli'=>'delhi','champawat'=>'delhi','dehradun'=>'delhi','gangotri'=>'delhi','gopeshwar'=>'delhi','guptkashi'=>'delhi','haldwani'=>'delhi','haridwar'=>'delhi','joshimath'=>'delhi','karnaprayag'=>'delhi','kashipur'=>'delhi','kedarnath'=>'delhi','kedernath'=>'delhi','khatima'=>'delhi','mussoorie'=>'delhi','nainital'=>'delhi','pauri'=>'delhi','pithoragarh'=>'delhi','purola'=>'delhi','ranikhet'=>'delhi','rishikesh'=>'delhi','roorkee'=>'delhi','rudraprayag'=>'delhi','rudrapur'=>'delhi','udham singh nagar'=>'delhi','uttarkashi'=>'delhi','yamunotri'=>'delhi','abinashpur'=>'kolkata','adra'=>'kolkata','alipurduar court'=>'kolkata','andal'=>'kolkata','arambagh'=>'kolkata','asansol'=>'kolkata','bagdogra'=>'kolkata','bagdogra air port'=>'kolkata','bagdora'=>'kolkata','bagnapara'=>'kolkata','bahula'=>'kolkata','baidyabati'=>'kolkata','baidyanathpur'=>'kolkata','baidyapur'=>'kolkata','bakshirhat'=>'kolkata','bally'=>'kolkata','banarhat'=>'kolkata','bandel'=>'kolkata','bankura'=>'kolkata','bara bazar'=>'kolkata','bara jaguli'=>'kolkata','barakar'=>'kolkata','bardhaman'=>'kolkata','basirhat'=>'kolkata','beldanga'=>'kolkata','beraballavpara'=>'kolkata','berhampore station road'=>'kolkata','berhampore-west bengal'=>'kolkata','bethuadahari'=>'kolkata','bhatjangla'=>'kolkata','bijanbari'=>'kolkata','birbhum'=>'kolkata','birpara'=>'kolkata','bolpur'=>'kolkata','bongaon'=>'kolkata','buinchi'=>'kolkata','burnpur'=>'kolkata','calcutta'=>'kolkata','chakdah'=>'kolkata','chinsurah'=>'kolkata','chittaranjan'=>'kolkata','contai'=>'kolkata','cooch behar'=>'kolkata','darjeeling'=>'kolkata','darjiling'=>'kolkata','debhog'=>'kolkata','dhupguri'=>'kolkata','diamond harbour'=>'kolkata','digha'=>'kolkata','dinajpur'=>'kolkata','dinhata'=>'kolkata','dubrajpur'=>'kolkata','durgapur'=>'kolkata','egra'=>'kolkata','falakata'=>'kolkata','falta'=>'kolkata','farakka barrage'=>'kolkata','farrakka'=>'kolkata','garbeta'=>'kolkata','ghatal'=>'kolkata','gorabari'=>'kolkata','gorifa'=>'kolkata','guskara'=>'kolkata','habra'=>'kolkata','haldia'=>'kolkata','haripur'=>'kolkata','hindmotor'=>'kolkata','hooghly'=>'kolkata','howrah'=>'kolkata','ilambazar'=>'kolkata','islampur'=>'kolkata','jalapahar'=>'kolkata','jalpaiguri'=>'kolkata','jamuria'=>'kolkata','jangipur'=>'kolkata','kadamtala'=>'kolkata','kalimpong'=>'kolkata','kalindi'=>'kolkata','kalna'=>'kolkata','kalyani'=>'kolkata','kandi'=>'kolkata','katwa'=>'kolkata','kenduadihi'=>'kolkata','keshiary'=>'kolkata','khakurda'=>'kolkata','khantura'=>'kolkata','kharagpur'=>'kolkata','kolaghat'=>'kolkata','kolkata'=>'kolkata','konnogar'=>'kolkata','ktpp township'=>'kolkata','kurseong'=>'kolkata','lakshmi pabartak'=>'kolkata','lalgola'=>'kolkata','lataguri'=>'kolkata','liluah'=>'kolkata','malda'=>'kolkata','mangalbari'=>'kolkata','manikpara'=>'kolkata','mankar'=>'kolkata','mankundu'=>'kolkata','matigara'=>'kolkata','md.bazar'=>'kolkata','mecheda'=>'kolkata','medinipur'=>'kolkata','memari'=>'kolkata','midnapore'=>'kolkata','mirik'=>'kolkata','murshidabad'=>'kolkata','nabadwip'=>'kolkata','nadia'=>'kolkata','nalhati'=>'kolkata','namopara'=>'kolkata','neamatpur'=>'kolkata','nimtauri'=>'kolkata','noorpur'=>'kolkata','north 24 parganas'=>'kolkata','nutandanga'=>'kolkata','panagarh'=>'kolkata','panskura'=>'kolkata','paraj'=>'kolkata','parulia'=>'kolkata','phuguri'=>'kolkata','plassey'=>'kolkata','pundibari'=>'kolkata','purba'=>'kolkata','purba medinipur'=>'kolkata','purulia'=>'kolkata','rabindra sarani'=>'kolkata','raghunathganj'=>'kolkata','raghunathpur'=>'kolkata','raidighi'=>'kolkata','raiganj'=>'kolkata','rampurhat'=>'kolkata','ranaghat'=>'kolkata','rangli rangliot'=>'kolkata','raniganj'=>'kolkata','rishra'=>'kolkata','rupanarayan'=>'kolkata','sagarbhanga'=>'kolkata','sainthia'=>'kolkata','samsi'=>'kolkata','sangamner'=>'kolkata','santipur'=>'kolkata','shaktinagar'=>'kolkata','shantiniketan'=>'kolkata','siliguri'=>'kolkata','simlapal'=>'kolkata','sitbankura'=>'kolkata','sonada'=>'kolkata','sonamukhi'=>'kolkata','south 24 parganas'=>'kolkata','sreerampur'=>'kolkata','sriniketan'=>'kolkata','sripally'=>'kolkata','sukna'=>'kolkata','sushruta nagar'=>'kolkata','sutahata'=>'kolkata','tamluk'=>'kolkata','tamluk medinipur'=>'kolkata','tarakeshwar'=>'kolkata','tarapith'=>'kolkata','tehatta'=>'kolkata','thakur nagar-west bengal'=>'kolkata','tufanganj'=>'kolkata','ukhra'=>'kolkata','uttarpara'=>'kolkata');
		$mapped_data_city = $array_mapped_city[strtolower($this->data_city)];
		switch($mapped_data_city) 
		{
			case  "mumbai" 		: $emailid =  "mumbaiweb@justdial.com"; break;
			case  "delhi" 		: $emailid =  "delhiweb@justdial.com"; break;
			case  "kolkata" 	: $emailid =  "kolkataweb@justdial.com"; break;
			case  "bangalore" 	: $emailid =  "bangaloreweb@justdial.com"; break;
			case  "chennai" 	: $emailid =  "chennaiweb@justdial.com"; break;
			case  "pune" 		: $emailid =  "puneweb@justdial.com"; break;
			case  "hyderabad" 	: $emailid =  "hyderabadweb@justdial.com"; break;
			case  "ahmedabad" 	: $emailid =  "ahmedabadweb@justdial.com"; break;
			case  "coimbatore" 	: $emailid =  "coimbatore@justdial.com"; break;
			case  "chandigarh" 	: $emailid =  "chdweb@justdial.com"; break;
			case  "jaipur" 		: $emailid =  "jaipurweb@justdial.com"; break;
		}
		return $emailid;
	}
	function get_empinfo_data($empcode)
	{		
		$curlurl 	= "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$empcode;
		$emp_detail = $this->get_data($curlurl);
		return $emp_detail['data'];
	}	
	function get_user_details()
	{		
		$usercode=	Array(); 
		$sql = "SELECT parentid,version FROM payment_apportioning WHERE parentid='".$this->parentid."' and  (MOD (version,2) OR MOD (version,3))  ORDER BY entry_date DESC LIMIT 1";
		$res 	= 	parent::execQuery($sql, $this->conn_fnc);
		if($res && mysql_num_rows($res)>0)
		{
			$row	=	mysql_fetch_assoc($res);
			
			$sql1 = "SELECT parentid,tmecode,mecode FROM payment_otherdetails WHERE parentid='".$this->parentid."' and   version='".$row['version']."'";
			$res1 	= 	parent::execQuery($sql1, $this->conn_fnc);
			if($res1 && mysql_num_rows($res1)>0)
			{
				$row1	=	mysql_fetch_assoc($res1);
				if(!empty($row1['tmecode']))
					$usercode[]	=	$row1['tmecode'];
				if(!empty($row1['mecode']))
					$usercode[]	=	$row1['mecode'];
			}	
		}	
		return $usercode;
	}
	
	public function get_data($url)
	{
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		$retArr = json_decode($data,true);
		return $retArr;
	}
	function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}
	function callSMSEmailAPI($params)
	{
		$curl_url = "http://".SMS_EMAIL_LB_IP."/insert.php"; 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
		if(strtolower($response) == 'success'){
			return 1;
		}else{			 
			return 0;
		}		
	}
} 

?>
