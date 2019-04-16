<?php
/*
 * @version : 0.0.1
 */
class Logs
{
	function __construct()
	{
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])) {
			define('LOGS_URL' ,'http://praveenchaudhary.blrsoftware.com/logsLive/logs.php');
			define('LOGS_FILE_INFO' ,'http://praveenchaudhary.blrsoftware.com/logsLive/process/Logsfile.php');
			define('GETLOGS' ,'http://praveenchaudhary.blrsoftware.com/logsLive/getlogsInfo.php');
			define('LOGS_LIST' ,'http://praveenchaudhary.blrsoftware.com/logsLive/process/getLogs.php');
			include('config.php');

		}
		else {
			define('LOGS_URL' ,'http://192.168.17.144/logs/logs.php');
			define('LOGS_FILE_INFO' ,'http://192.168.17.144/logs/process/Logsfile.php');
			define('GETLOGS' ,'http://192.168.17.144/logs/getlogsInfo.php');
			define('LOGS_LIST' ,'http://192.168.17.144/logs/process/getLogs.php');
			include('config.php');
		}
	}
	
	function CURLLogs($url_data)
	{
		echo 'Logs Inserted';
		print_r($url_data);

		  $ch = curl_init();
		  $timeout = 5;
		  curl_setopt($ch, CURLOPT_URL, $url_data['url']);
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  if(isset($url_data['data'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $url_data['data']);
		  }
		  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		  $data = curl_exec($ch);
		  curl_close($ch);
		  return $data;
	}
	
	function sendLogs($data)
	{
		
		$publish 			   = (isset($data['PUBLISH']) && $data['PUBLISH'] != '') ? $data['PUBLISH'] : 'BACKEND';
		if ($data['JDBOXAPI'] != 1)
		{
			$data['PUBLISH']   = $this->getPublish($publish);
		}
		$data['ROUTE']         = (isset($data['ROUTE']) && trim($data['ROUTE']) != '' ) ? trim($data['ROUTE']) : $data['PUBLISH'];
		$data['CRITICAL_FLAG'] = (isset($data['CRITICAL_FLAG']) && trim(is_numeric($data['CRITICAL_FLAG']))) ? $data['CRITICAL_FLAG'] : 0 ;
		
		$data['SERVER INFO']['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'];
		$data['SERVER INFO']['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
		$data['SERVER INFO']['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'];
		$data['SERVER INFO']['PHP_SELF']    = $_SERVER['PHP_SELF'];
		$data['SERVER INFO']['VERSION']     = '0.0.1';
		 
		$url['data'] = http_build_query($data);
		echo $url['url']  = LOGS_URL;

		return $this->CURLLogs($url);
	}
	function getLogsFile($publish, $id)
	{
		if($publish != '' && $id != '')	{
			$url['url'] = LOGS_FILE_INFO."?publish=$publish&id=$id";
			$response = $this->CURLLogs($url);
			return $response;
		}
		else {
			return 'Please Provide All parameter';
		}
	}
	function getPublish($publish)
	{
		$ipAddress = $_SERVER['SERVER_ADDR'];
		switch($publish)
		{
			case 'BACKEND' : 
					$city = array('MUMBAI','DELHI','KOLKATA','CHENNAI','PUNE','BANGALORE','HYDERABAD','AHMEDABAD');

						//if(in_array($city, strtoupper($_SESSION['s_deptCity'])) {
						if(defined('REMOTE_CITY_MODULE')){

							$publish = $publish.'_REMOTE';
						}
						else
						{
							$publish = $publish.'_'.$_SESSION['s_deptCity'];
						}

					
				break;
			default : 
					return $publish;
				break;
		}

		return $publish;
		
	}	
	function getLogsData($publish, $logid)
	{
		if($publish !='' && $logid != '') {
			$url['url'] = GETLOGS."?publish=$publish&logid=$logid";
			$response   = $this->CURLLogs($url);
			return $response;
		}
		else {
			return 'Please Provide logid and publish';
		}
	}
	function getLogs($publish, $date)
	{
		if($publish !='' && $date['fromDate'] != '' && $date['toDate'] !='') {
			$url['url'] = LOGS_LIST."?publish=$publish&from=".$date['fromDate']."&to=".$date['toDate']."&id=".((isset($date['logId']) && $date['logId'] !='') ? $date['logId'] : '');
			$response   = $this->CURLLogs($url);
			return $response;
		}
		else {
			return 'Please Provide Date and Publish';
		}
	}
}



?>
