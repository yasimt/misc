<?php

@session_start();
include_once("../common/Serverip.php");
include_once("../common/dbconnection/config.php");
include_once("../common/dbconnection/dbnames.php");
include_once("class_email_sms_send.php");
include_once("class.logs.php");

global $dbarr;
$data = array();

$sms	=	new email_sms_send($dbarr);

//~ echo '<pre>';print_r($_REQUEST);die();
$mobile = $_REQUEST['mobile'];
$email  = $_REQUEST['email'];
$comp_name = $_REQUEST['company'];
$parentid = $_REQUEST['parentid'];
$empcode = $_SESSION['empcode'];
$city 	 = $_REQUEST['city'];

$returnsms		=	'';
$returnemail	=	'';
$shortUrl		=	'';
 
	$shorturl	=	'';
	$sql="select concat(url_cityid,shorturl) as shorturl from db_iro.tbl_id_generator where parentid='".$parentid."'";
	$query=$sms->conn_iro->query_sql($sql);
	if($sms->conn_iro->numRows($query)>0){
		while($row=$sms->conn_iro->fetchData($query)){
			$shortUrl=$row['shorturl'];
		}
	}else{
		$shortUrl='';
	}
 
	if(!empty($mobile))
    {  
	   $sms_cont = "Dear ".$comp_name.",\nCongratulations! Your company rating page is now live. Send free SMS & Email to your customers to get ratings on Justdial- India's Leading Local  Search Engine.\nClick Now: http://jsdl.in/RT-".$shortUrl.".\n \nRegards,\nTeam Justdial";
		
	   foreach($mobile as $key => $val)
	   {
			$sms->sendSMS($val,$sms_cont,'CS_REVIEW');
			$data['ID']         =	$parentid;                 		    
			$data['PUBLISH']    =   'CS';         	  		
			$data['ROUTE']      = 	'RATING_SMS_AND_EMAIL';			
			$data['USER_ID']	= 	$empcode;  
			$data['CRITICAL_FLAG']  = 0 ;					
			$data['MESSAGE']        = 'SMS SENT';
			$data['DATA']['OTHER']	= $val;
			$data['DATA']['SMS MESSAGE']	= $sms_cont;
			
			$logsObj = new Logs();
			$sendLogs = $logsObj->sendLogs($data);
	   }
	   
		$returnsms = 1;
		
	}
	else
	{
		$returnsms = 0;
	}
	
	 $url	      =	"http://jsdl.in/RT-".$shortUrl;
	 $email_cont .= "Dear ".$comp_name.",<br><br>";
	 $email_cont .= "Congratulations! Your company rating page is now live.Send free SMS & Email to your customers to get ratings on Justdial- India's Leading Local Search Engine.<br><br>";
	 $email_cont .= "Click Now: <a href=".$url.">".$url."</a><br><br>";
	 $email_cont .= "Regards,<br>";
	 $email_cont .= "Team Justdial";
	
	if($email!=''){
		
		$from		= 	"feedback@justdial.com";
		$subject	=	"Get feedback from your customers";
		 foreach($email as $key => $val)
		 {
			$email_cont	=	trim($email_cont);
			$sms->sendEmail($val,$from,$subject, $email_cont, 'CS_REVIEW');	
			
			    $data['ID']         =	$parentid;                 		    
				$data['PUBLISH']    =   'CS';         	  		
				$data['ROUTE']      = 	'RATING_SMS_AND_EMAIL';   				
				$data['USER_ID']	= 	$empcode;  
				$data['CRITICAL_FLAG']  = 0 ;					
				$data['MESSAGE']        = 'EMAIL SENT';
				$data['DATA']['OTHER']	= $val;
				$data['DATA']['SMS MESSAGE']	= "Dear ".$comp_name.",\nCongratulations! Your company rating page is now live. Send free SMS & Email to your customers to get ratings on Justdial- India's Leading Local Search Engine.\nClick Now: <a href = '.$url.'>'.$url.'</a> \n \nRegards,\nTeam Justdial";
				
				$logsObj = new Logs();
				$sendLogs = $logsObj->sendLogs($data);
				
		 }
		$returnemail	=	1;
	}else{
		$returnemail	=	0;
	}
	
	if($returnsms	==	1 && $returnemail != 1)
	{
		echo 1;
	}
   else if($returnemail	==	1 && $returnsms != 1)
   {
		echo 2;
	}
	else if($returnsms	==	1 && $returnemail == 1){
		echo 3;
	}
	else
	{
		echo 0;
	}
	
?>
