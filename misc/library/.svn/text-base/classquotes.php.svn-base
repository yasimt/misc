<?php
set_time_limit(0);
session_start();

class classquotes
{

function processQuotes($conn_iro,$compmaster_obj,$parentid,$ucode)
{



$temparr	= array();
$fieldstr	= '';
$fieldstr 	= "freeze,mask,closedown_flag,type_flag&32 as tf_flag";
$tablename	= "tbl_companymaster_extradetails";
$wherecond	= "parentid = '".$parentid."'";
$temparr	= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

if($temparr['numrows']>0)
{
	$freeze =0;
	$mask 	=0;
	$closedown_flag=0;

	$result_freeze_arr = $temparr['data']['0'];
	$freeze  = $result_freeze_arr['freeze'];
	$mask 	= $result_freeze_arr['mask'];
	$closedown_flag =  $result_freeze_arr['closedown_flag'];
	$tf_flag		= $result_freeze_arr['tf_flag'];
}



if($freeze || $mask || ($closedown_flag==1) || ($closedown_flag==13) || ($closedown_flag==14) ) // if contract is either freeze or masked or closedown
{
	$docidsql ="select docid from iro_cards where parentid='".$parentid."'";
	$docidrs = $conn_iro->query_sql ($docidsql);
	$docidarr= $conn_iro->fetchData($docidrs);
	
	if(APP_LIVE == 1)
	{
		$sfdeactivtionurl="http://192.168.20.105:1080/services/disable_quotes.php?docid=".$docidarr['docid']."&status=freeze&updated_by==web_api-".$ucode;
	}else
	{
		$sfdeactivtionurl="http://pankajpatil.jdsoftware.com/QUICK_SERVICES/services/disable_quotes.php?docid=".$docidarr['docid']."&status=freeze&updated_by=web_api-".$ucode;
	}
	
	$this->curl_call($sfdeactivtionurl);
	$this->logsquotes($parentid,$ucode,$freeze,$mask,$closedown_flag,$tf_flag);
}


}


function curl_call($url) 
{
	if(!empty($url))
	{
		$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resmsg = curl_exec($ch);
		//print_r($resmsg);
		curl_close($ch);
	}
}

function logsquotes($parentid,$ucode,$freeze,$mask,$closedown_flag,$tf_flag)
{
	return ; // stoping making log
	$log_msg='';
	$log_path = APP_PATH.'logs/quoteslog/';
	$sNamePrefix= $log_path;
	// fetch directory for the file
	$pathToLog = dirname($sNamePrefix); 
	//unlink($log_path);
	if(!file_exists($log_path)) {
		mkdir($log_path, 0755, true);;
	}
	/*$file_n=$sNamePrefix.$contractid.".txt"; */
	$file_n=$sNamePrefix.$parentid.".html";
	$command	= "chown apache:apache ". $file_n;
	$output = shell_exec($command);
	//echo $file_n;
	// Set this to whatever location the log file should reside at.
	$logFile = fopen($file_n, 'a+');

		
	//$userID= $_SESSION['ucode'];
	/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
	$pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
	$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
					<tr valign='top'>
						<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
						<td style='width:10%; border:1px solid #669966'>ucode :".$ucode."</td>
						<td style='width:15%; border:1px solid #669966'>freeze:".$freeze."</td>
						<td style='width:30%; border:1px solid #669966'>mask:".$mask."</td>
						<td style='width:30%; border:1px solid #669966'>closedown_flag:".$closedown_flag."</td>
						<td style='width:10%; border:1px solid #669966'>tf_flag :".$tf_flag."</td>
						
						</tr>
				</table>";
	fwrite($logFile, $log_msg);
	fclose($logFile);
}
	
}

?>
