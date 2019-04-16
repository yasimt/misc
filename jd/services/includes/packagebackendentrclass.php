<?php

class packagebackendentrclass extends DB
{
var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

var  $data_city	= null;	
	
	

	function __construct($params)
	{		
		$this->params = $params;
		$this->setServers();
	}
	
	function setServers()
	{	
		global $db;
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->fin_master_con = $db[$data_city]['fin']['master'];
		$this->ConIro    		= $db[$data_city]['iro']['master'];
	}

function process()
{

$allcontract_sql = "SELECT * FROM dbteam_temp.temp_malti_parentids_1 where done_flag=0 limit 1";
$allcontract_res = parent::execQuery($allcontract_sql, $this->fin_master_con);

$counter=1;
 
	while($allcontract_arr= mysql_fetch_assoc($allcontract_res))
	{
		$counter++;
		
		$parentid = $allcontract_arr['parentid'];		
		$parentid = 'P1236425901D1A8Z8';		

		$this->update_done_flag($this->fin_master_con,$parentid,9);
		
		$donval=$this->callinvapi($parentid);
		
		$this->update_done_flag($this->fin_master_con,$parentid,$donval);
		unset($instrumentid);
		unset($cpsclass_obj);
		
	}
}



function update_done_flag($conn_fin,$parentid,$done_flag)
{
	$update_sql= "update dbteam_temp.temp_malti_parentids_1 set done_flag=".$done_flag." where parentid='".$parentid."' ";	
	parent::execQuery($update_sql, $conn_fin);
}


function callinvapi($parentid)
{
	$retval=0;
	$checkpurepackage="select campaignid,balance,bid_perday,version from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (1,2) ";
	$getdetails = parent::execQuery($checkpurepackage, $this->fin_master_con);
	$bidperday='';
	if(mysql_num_rows($getdetails)>0){
		
			while($resrow=mysql_fetch_assoc($getdetails))
			{
				if($resrow['campaignid']==2 && $resrow['balance']>0){
					$fpbalance=$resrow['balance'];
				}

				if($resrow['campaignid']==1 && $resrow['balance']>0)
				{
						$bidperday	=$resrow['bid_perday'];
						$packbalance=$resrow['balance'];
						$version	=$resrow['version'];
				}
			}
		
			if($fpbalance>0)
			{
				return 1; // pdg contract
				
			}

			if($packbalance<=0)
			{
				return 2;  // no balance
			}
							
	}
	else{
		return 3; // not active package contract
	}

		

		$data_city='';

		$getdatacitysql="select data_city from tbl_companymaster_generalinfo where parentid='".$parentid."'";
		$getdatacityres = parent::execQuery($getdatacitysql, $this->ConIro);

		if(mysql_num_rows($getdatacityres)>0){
			while($resrow=mysql_fetch_assoc($getdatacityres))
			{
				$data_city=$resrow['data_city'];
			}
	}

	if($packbalance>0 && $fpbalance<=0)
	{

	if( preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) ){
		$url="http://sunnyshende.jdsoftware.com/jdbox/services/invMgmt.php?parentid=$parentid&astatus=2&astate=15&bidperday=".$bidperday."&data_city=".$data_city."&version=".$version;
	}
	else{		
		
			switch(strtoupper($data_city))
			{
				case 'MUMBAI' :
					$url 					= "http://172.29.0.217:81/";
					$jdbox_url 				= "http://172.29.0.217:811/";
					$city_indicator 		= "main_city";
					break;

				case 'AHMEDABAD' :
					$url 					= "http://172.29.56.217:81/";
					$jdbox_url 				= "http://172.29.56.217:811/";
					$city_indicator = "main_city";
					break;

				case 'BANGALORE' :
					$url 					= "http://172.29.26.217:81/";
					$jdbox_url 				= "http://172.29.26.217:811/";
					$city_indicator 		= "main_city";
					break;

				case 'CHENNAI' :
					$url 					= "http://172.29.32.217:81/";
					$jdbox_url 				= "http://172.29.32.217:811/";
					$city_indicator		    = "main_city";
					break;

				case 'DELHI' :
					$url 					= "http://172.29.8.217:81/";
					$jdbox_url 				= "http://172.29.8.217:811/";
					$city_indicator 		= "main_city";
					break;

				case 'HYDERABAD' :
					$url 					= "http://172.29.50.217:81/";
					$jdbox_url 				= "http://172.29.50.217:811/";
					$city_indicator 		= "main_city";
					break;

				case 'KOLKATA' :
					$url 					= "http://172.29.16.217:81/";
					$jdbox_url 				= "http://172.29.16.217:811/";
					$city_indicator 		= "main_city";
					break;

				case 'PUNE' :
					$url 					= "http://172.29.40.217:81/";
					$jdbox_url 				= "http://172.29.40.217:811/";
					$city_indicator 		= "main_city";
					break;

				default:
					$url 					= "http://192.168.17.217:81/";
					$jdbox_url 				= "http://192.168.20.135:811/";
					$city_indicator 		= "remote_city";
					break;
			}
			
			$url="http://".$jdbox_url."/invMgmt.php?parentid=$parentid&astatus=2&astate=15&bidperday=".$bidperday."&data_city=".$data_city."&version=".$version;
	}

	$ch = curl_init();        
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$resultString = curl_exec($ch);
	curl_close($ch); 
	  $resultString=json_decode($resultString,true);
	}
	
	$this->callwebapi($parentid,$data_city);
	return 100;

}


function callwebapi($parentid,$data_city)
{
	
		switch($data_city)
			{
				case 'MUMBAI' :
					$url 			= "http://172.29.0.217:81/";
					$city_indicator = "main_city";
					break;

				case 'AHMEDABAD' :
					$url 			= "http://172.29.56.217:81/";
					$city_indicator = "main_city";
					break;

				case 'BANGALORE' :
					$url 			= "http://172.29.26.217:81/";
					$city_indicator = "main_city";
					break;

				case 'CHENNAI' :
					$url 			= "http://172.29.32.217:81/";
					$city_indicator = "main_city";
					break;

				case 'DELHI' :
					$url 			= "http://172.29.8.217:81/";
					$city_indicator = "main_city";
					break;

				case 'HYDERABAD' :
					$url 			= "http://172.29.50.217:81/";
					$city_indicator = "main_city";
					break;

				case 'KOLKATA' :
					$url 			= "http://172.29.16.217:81/";
					$city_indicator = "main_city";
					break;

				case 'PUNE' :
					$url 			= "http://172.29.40.217:81/";
					$city_indicator = "main_city";
					break;

				default:
					$url 			= "http://192.168.17.217:81/";
					$city_indicator = "remote_city";
					break;
			}

			if(preg_match("/\bjdsoftware.com\b/i", $_SERVER['HTTP_HOST']))
			{
				$curl_url	= "http://prameshjha.jdsoftware.com/csgenio/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=DBBackend&validationcode=DBBKND&uname=DBBackend";
			}
			else
			{
				$curl_url	= $url."/web_services/curl_serverside.php?city_indicator=".$city_indicator."&data_city=".urlencode($data_city)."&parentid=".$parentid."&ucode=DBBackend&validationcode=DBBKND&uname=DBBackend";
			}
			echo $curl_url;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resjson = curl_exec($ch);
			curl_close($ch);
		}
	
}
