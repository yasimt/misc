<?php
class dealclosedashboardclass extends DB
{
	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');	
	
	var $ucode =null;
	var $parentid= null;
	var $version= null;
	var $data_city	= null;
	var $reqcity	= null;
	var $endtime	= null;
	var $starttime	= null;
	var $datebetween =null;
	
	function __construct($params)
	{		
		$this->params = $params;
	
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = strtolower($this->params['action']); 
			
		
		if(isset($this->params['ucode']) && trim($this->params['ucode']) != "")
		{
			$this->ucode = $this->params['ucode'];
		}
		
		if(isset($this->params['uname']) && trim($this->params['uname']) != "")
		{
			$this->uname = $this->params['uname'];
		}
		
		
		if(isset($this->params['parentid']) &&  trim($this->params['parentid']) != "")
		{
			$this->parentid = $this->params['parentid'];
		}
		
		if(isset($this->params['version']) &&  trim($this->params['version']) != "")
		{
			$this->version = $this->params['version'];
		}
		
		if(isset($this->params['data_city']) && trim($this->params['data_city']) != "")
		{
			$this->data_city = $this->params['data_city'];
		}
		
		
		if(isset($this->params['reqcity']) && trim($this->params['reqcity']) != "")
		{
			$this->reqcity = $this->params['reqcity'];
		}
		
		if($this->action=='updatecontactinfo' && ($this->parentid == null || $this->version== null || $this->data_city==null) )
		{
			$this->printerror(11," parentid or version or data_city is missing ");
		}

		if(isset($this->params['starttime']) && trim($this->params['starttime']) != "")
		{
			$this->starttime = $this->params['starttime'].' 00:00:00';
		}else
		{
			$this->starttime = date("Y-m-d").' 00:00:00';
		}
		
		if(isset($this->params['endtime']) && trim($this->params['endtime']) != "")
		{
			$this->endtime = $this->params['endtime'].' 23:59:59';
		}else
		{
			 $this->endtime = date("Y-m-d").' 23:59:59';
		}
		
		if(isset($this->params['limit']))
		{
			$this->limit = 'LIMIT '.$this->params['limit'];
		}else
		{
			$this->limit = 'LIMIT 0,10';
		}
		
		if(isset($this->params['campaignNameDisplay']))
		{
			$this->campaignNameDisplay = $this->params['campaignNameDisplay'];
		}else
		{
			$this->campaignNameDisplay = '';
		}
		
		if(isset($this->params['limit']))
		{
			$this->limit = 'LIMIT '.$this->params['limit'];
		}else
		{
			$this->limit = 'LIMIT 0,10';
		}
		
		if(isset($this->params['circle']) && trim($this->params['circle']) != "")
		{
			$this->circle = $this->params['circle'];
		}
		
		if(isset($this->params['companyname']) &&  trim($this->params['companyname']) != "")
		{
			$this->companyname = $this->params['companyname'];
		}
		
		$this->datebetween = " between '".$this->starttime."' and '".$this->endtime."' ";
		
		if($this->params['action'] == 'getCityWiseInstrumentAmount')
			$this->dealclosedonbetween = " dealclose_date between '".$this->starttime."' and '".$this->endtime."' ";
		elseif($this->params['action'] =='getAllLiteData')
			$this->dealclosedonbetween = " payment_done_on >= '".$this->starttime."' and  payment_done_on <= '".$this->endtime."' ";	
		else
			$this->dealclosedonbetween = " dealclosedon between '".$this->starttime."' and '".$this->endtime."' ";		
			
			
		$this->dealclosedonbetween_device = " entry_date >= '".$this->starttime."' and  entry_date <= '".$this->endtime."' ";	
		
		if($this->params['action'] =='getAllLiteData')
		{
			if(isset($this->params['status']) &&  trim($this->params['status']) != "")
			{
				$this->status = $this->params['status'];
			}
			if(isset($this->params['dealCloseType']) &&  trim($this->params['dealCloseType']) != "")
			{
				$this->dealCloseType = $this->params['dealCloseType'];
			}
			
			
			
		}
			
		$this->setServers();
		
		
		if($this->params['action'] =='getAllLiteData' )
		{
			$this-> all_mapped_main_cities = $this -> getAllMappedCities();
			
			if(isset($this->params['data_city']) &&  trim($this->params['data_city']) != "")
			{
				$this-> mapped_main_city   = $this->params['data_city'];
				
				$this-> mapped_data_cities = $this -> getMappedCities ();
			}
		}
		
		if(isset($this->params['module']) && trim($this->params['module']) != "")
		{
			$this->module = $this->params['module'];
		}
			
	}
	
	
		
	
	function setServers()
	{	
		global $db;		
		
		$data_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->global_db = $db;
		
		$this->conn_dcdash = $db['dcdash'];
		
		$this->dbConDjds  		= $db['mumbai']['d_jds']['master'];
		
		$this->dbConidc          = $db[$data_city]['idc']['master'];
		
		if($this->action == 'getalllitedata')
		{
			//print_r($db[$data_city]['idc']['master']);
		  $this->dbConidc  		=  $db['mumbai']['idc']['master'];	
		}
		
		if(DEBUG_MODE)
		{
			echo '$db dcdash ' ; print_r($db['dcdash']);
			
		}
	}
	
	
function campaignwisecount($empcode=null)
{

	$resultarray= array();
	
	if($this->campaignNameDisplay)
	$campaignNameCond = " WHERE campaignname='".$this->campaignNameDisplay."'";
		
	$campaignnameslist_query = " select  distinct campaignname from campaignnameslist ".$campaignNameCond;
	$res = parent::execQuery($campaignnameslist_query, $this->conn_dcdash);	
	
	
	$citycond=null;
	$empcodecond= null;
	if($this->reqcity)
	{
		$citycond = " mapped_maincity= '".$this->reqcity."' and ";	
	}
	
	if($empcode)
	{
		$empcodecond = " dealclosedby ='".$empcode."' and "; 
	}
	
	$dealclosedoncond = $this->dealclosedonbetween." and " ;
	
	while($arr = mysql_fetch_assoc($res))
	{
		$sqlarr[] = " select '".$arr['campaignname']."' as campaignname, count(1) as count  from contract_info_details where ".$citycond .$empcodecond .$dealclosedoncond." match (campnamedisplayed) against ('\"".$arr['campaignname']."\"' in boolean mode )";
	}
	
	
	$unionsql = "select campaignname,count from ( " . implode(' union ',$sqlarr). " )a order by count desc ";	
	$unionres = parent::execQuery($unionsql, $this->conn_dcdash);
	$ind=0;
	$displayorder_count=6;
	$totalcount=0;
	while($unionarr= mysql_fetch_assoc($unionres))
	{
		if($unionarr['count']>0)
		{
			$temparray[$ind++]= array("campaignname"=> $unionarr['campaignname'],'count'=>$unionarr['count'],'displayorder'=> ($ind<=$displayorder_count? "Default":"more") ,'campaignDesc'=>"",'data_city'=>$this->reqcity);
			$totalcount+=$unionarr['count'];
		}
	}
	
	$temparray['totalcount']=$totalcount;	
	$resultarray['data'] =$temparray;       
	$resultarray['errocode'] =0;
	$resultarray['erromsg'] ='No error';
	
	
	if(DEBUG_MODE)
	{
		echo '<br>$unionsql'.$unionsql ; 
		echo '<br>$temparray' ; print_r($temparray);
		echo '<br>$resultarray' ; print_r($resultarray);
	}


	return $resultarray;
        
}



function CampaignWiseDetails()
{
	
	if($this->campaignNameDisplay)
	$campaignNameCond = " WHERE campaignname ='".$this->campaignNameDisplay."'";
		
	$campaignnameslist_query = " select  distinct campaignname from campaignnameslist ".$campaignNameCond;
	$res = parent::execQuery($campaignnameslist_query, $this->conn_dcdash);	
	
	
	$citycond=null;
	$empcodecond= null;
	if($this->reqcity)
	{
		$citycond = " mapped_maincity= '".$this->reqcity."' and ";	
	}
	
	if($empcode)
	{
		$empcodecond = " dealclosedby ='".$empcode."' and "; 
	}
	
	$dealclosedoncond = $this->dealclosedonbetween." and " ;
	
	if(DEBUG_MODE)
	{
		echo '<br>$campaignnameslist_query'.$campaignnameslist_query ; 
		echo '<br>$res'.$res;
		echo '<br>mysql num rows :: '.mysql_num_rows($res);
	}
	
	if($res && mysql_num_rows($res))
	{
		while($arr = mysql_fetch_assoc($res))
		{
			$sqlarr = " SELECT companyname,parentid,data_city,mapped_maincity,AREA,pincode,latitude,longitude,latitude_actual,longitude_actual,
							   dealclosedby,dealclosedon, dealClosedByName,campnamebycampaignid,campnamedisplayed,campaignwisebudget,finbudget 
							   FROM contract_info_details 
							   WHERE ".$citycond .$empcodecond .$dealclosedoncond."
							   MATCH (campnamedisplayed) AGAINST ('\"".$arr['campaignname']."\"' IN BOOLEAN MODE )   
							   ORDER BY dealclosedon DESC ".$this->limit." ";
		   $resarr = parent::execQuery($sqlarr, $this->conn_dcdash);
		   
		   if(DEBUG_MODE)
			{
				echo '<br>$sqlarr'.$sqlarr ; 
				echo '<br>$resarr'.$resarr;
				echo '<br>mysql num rows :: '.mysql_num_rows($resarr);
			}
			
		   if($resarr && mysql_num_rows($resarr))
			{
				while($rowarr = mysql_fetch_assoc($resarr))
				{
					$rowarr['dealClosedByName'] = strtolower($rowarr['dealClosedByName']);
					if(stristr(trim(strtolower($rowarr['campnamedisplayed'])),'#'.strtolower($arr['campaignname']).'#'))
					$deal_close_campaign_data[$arr['campaignname']][] = $rowarr;
				}
			}	  
		}
	}
	
	
	if(count($deal_close_campaign_data)>0)
	{
		$data['errorCode']   = 0;
		$data['errorMsg']    = 'successful';
		$data['data_count']  = count($deal_close_campaign_data);
		$data['records']     = $deal_close_campaign_data;
	}
	else
	{
		$data['errorCode']   = 1;
		$data['errorMsg']    = 'No Data';
		$data['data_count']  = count($deal_close_campaign_data);
		$data['records']     = '';
	}
	
	return $data;
	
}

function getContractDealClosedDetails()
{
	if($this->parentid)
	{
		$sql_condition = "parentid='".$this->parentid."'";
	}
	
	if($this->companyname)
	{
		$sql_condition = " MATCH (companyname) AGAINST ('".$this->companyname."') ";
		$sql_column    = " , MATCH (companyname) AGAINST ('".$this->companyname."') as match_count ";
		$order_clause  = " ORDER BY match_count DESC LIMIT 100";
	} 
	
    $sql_get_contract_details ="SELECT companyname,parentid,data_city,mapped_maincity,AREA,pincode,latitude,longitude,latitude_actual,longitude_actual,
							   dealclosedby,dealclosedon, dealClosedByName,campnamebycampaignid,campnamedisplayed,campaignwisebudget,finbudget".$sql_column."  FROM contract_info_details WHERE ".$sql_condition." AND data_city='".$this->data_city."' ".$order_clause."";
    $res_get_contract_details = parent::execQuery($sql_get_contract_details, $this->conn_dcdash);
	if($res_get_contract_details && mysql_num_rows($res_get_contract_details))
	{
		while($row_get_contract_details = mysql_fetch_assoc($res_get_contract_details))
		{
			$deal_close_contract_data[$row_get_contract_details['parentid']][$row_get_contract_details['version']] = $row_get_contract_details;
			
		}
	}

   if(DEBUG_MODE)
	{
		echo '<br>$sqlarr'.$sql_get_contract_details ; 
		echo '<br>$resarr'.$res_get_contract_details;
		echo '<br>mysql num rows :: '.mysql_num_rows($res_get_contract_details);
	}
			
	if(count($deal_close_contract_data)>0)
	{
		$data['errorCode']   = 0;
		$data['errorMsg']    = 'successful';
		$data['data_count']  = count($deal_close_contract_data);
		$data['records']     = $deal_close_contract_data;
	}
	else
	{
		$data['errorCode']   = 1;
		$data['errorMsg']    = 'No Data';
		$data['data_count']  = count($deal_close_contract_data);
		$data['records']     = '';
	}
	
	return $data;
   
}
	
function GetHourlyCount()
{
	if($this->campaignNameDisplay)
	$campaignNameCond = " AND campnamedisplayed like '%##".strtolower($this->campaignNameDisplay)."##%'";
	
	$wherecond = " WHERE ";
	
	$date_cond = $this->dealclosedonbetween; 
	
	if($this->reqcity)
	{
		$citycond = "AND mapped_maincity= '".$this->reqcity."'";
	}
	
	$sql = "SELECT 
	mapped_maincity,dealclosedon,DATE(dealclosedon) AS deal_date,HOUR(dealclosedon) 'start_hr', HOUR(DATE_ADD(dealclosedon,INTERVAL 1 HOUR)) AS end_hr,COUNT(parentid) as deal_count 
	FROM contract_info_details ".$wherecond." ".$date_cond." ".$citycond." ".$campaignNameCond."
	GROUP BY mapped_maincity,deal_date,start_hr";
	$res = parent::execQuery($sql, $this->conn_dcdash);
	if($res && mysql_num_rows($res))
	{
		while($row = mysql_fetch_assoc($res))
		{
			$hourly_deal_close_arr[$row['mapped_maincity']][$row['deal_date']][$row['start_hr'].'-'.$row['end_hr']]['count'] = $row['deal_count'];
		}
	}
	
	if(DEBUG_MODE)
	{
		echo '<br>$sqlarr'.$sql ; 
		echo '<br>$resarr'.$res;
		echo '<br>mysql num rows :: '.mysql_num_rows($res);
	}
	
	
	
	if(count($hourly_deal_close_arr)>0)
	{
		
		foreach($hourly_deal_close_arr as $city => $date_arr)
		{
			$avg_divider = count($date_arr);
			foreach($date_arr as $date => $hourly_time_arr)
			{
				foreach($hourly_time_arr as $hourly_time => $hourly_time_arr)
				{
					//echo '<br> --->'.$hourly_time.' :: '.$hourly_time_arr['count'];
					$new_avg_arr[$hourly_time]['count'] += $hourly_time_arr['count'];
				}
			}
			 
		}
		
		//print_r($new_avg_arr);
		foreach($new_avg_arr as $hourly_time_new => $hourly_time_arr_range)
		{
			$new_avg_arr[$hourly_time_new]['no_of_days'] = $avg_divider;
			$new_avg_arr[$hourly_time_new]['avg'] = round($hourly_time_arr_range['count']/$avg_divider);
			
		}
		
		$data['errorCode']   = 0;
		$data['errorMsg']    = 'successful';
		$data['data_count']  = count($hourly_deal_close_arr);
		$data['records']     = $hourly_deal_close_arr;
		$data['records_avg']     = $new_avg_arr;
	}
	else
	{
		$data['errorCode']   = 1;
		$data['errorMsg']    = 'No Data';
	$data['data_count']  = count($hourly_deal_close_arr);
		$data['records']     = '';
	}
	
	return $data;
	
	
}
	
//second api :- total dealclose / city wise deal close count
function citywisecount()
{	
	$resultarray= array();	
	$paindiamaincity = $paindiaremotecity=0;	
	
	$wherecond= " Where ".$this->dealclosedonbetween; 
	
	
	if($this->campaignNameDisplay)	
	{
		$sql = "select count(cityidntifier)  as count,lower(mapped_maincity) as mapped_maincity,cityidntifier,campnamedisplayed,campnamebycampaignid from 
				(
				   SELECT IF(mapped_maincity = data_city, 'maincity', 'remotecity') as cityidntifier,mapped_maincity,campnamedisplayed,campnamebycampaignid from contract_info_details ".$wherecond."
				)a group by campnamedisplayed,mapped_maincity,cityidntifier";
	}
	else
	{
		$sql = "select count(cityidntifier)  as count,lower(mapped_maincity) as mapped_maincity,cityidntifier from 
				(
				   SELECT IF(mapped_maincity = data_city, 'maincity', 'remotecity') as cityidntifier,mapped_maincity from contract_info_details ".$wherecond."
				)a group by mapped_maincity,cityidntifier";
	}
	
	$res = parent::execQuery($sql, $this->conn_dcdash);
	
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$sql ; 		
	}
	
	while( $row= mysql_fetch_assoc($res))
	{
		if($this->campaignNameDisplay)	
		{
			if(!$row['campnamedisplayed'])
				$row['campnamedisplayed'] = '##Other Combo##';
			
		    $temparray[$row['campnamedisplayed']][$row['mapped_maincity']][$row['cityidntifier']]=  intval($row['count']);
		    
		}
		else
			$temparray[$row['mapped_maincity']][$row['cityidntifier']]=  intval($row['count']);
		
		if(DEBUG_MODE)
		{
			echo '<br>$row'; print_r($row); 		
		}
	}
	
	
	if(DEBUG_MODE)
	{
		echo '<br>$temparray'; print_r($temparray);
	}	
	
	if($this->campaignNameDisplay)	
	{
		foreach($temparray as $Key_campname=>$value_campname) 
		{
			foreach($value_campname as $Keymapped => $value)
			{
					$temparray[$Key_campname][$Keymapped]['total']= $value['maincity'] + $value['remotecity'];
					$paindiamaincity 	+= $value['maincity'];
					$paindiaremotecity 	+= $value['remotecity'];
			}
		}
		
	}
	else
	{
		foreach($temparray as $Key=>$value)
		{
			$temparray[$Key]['total']= $value['maincity'] + $value['remotecity'];
			$paindiamaincity 	+= $value['maincity'];
			$paindiaremotecity 	+= $value['remotecity'];
		}
	}
	
	$temparray['panindia'] =  array("maincity"=> $paindiamaincity,"remotecity"=> $paindiaremotecity,"total"=> $paindiamaincity + $paindiaremotecity);	
	
	if(DEBUG_MODE)
	{
		echo '<br>$temparray'; print_r($temparray);
	}
	
	
	$resultarray['data'] =$temparray;       
	$resultarray['errocode'] =0;
	$resultarray['erromsg'] ='No error';
	return $resultarray;
}

function cityWiseDetails()
{
	$wherecond = " WHERE ";
	
	$date_cond =" AND ".$this->dealclosedonbetween; 
	
	if(strtolower($this->circle) == 'main')
		$circle_cond =" AND mapped_maincity = data_city"; 
	else if(strtolower($this->circle) == 'remote')
		$circle_cond =" AND mapped_maincity != data_city"; 
	
	if($this->reqcity)
	{
		$cities_arr [] = $this->reqcity;
	}
	else
	{
			
		$sql_ct = "SELECT GROUP_CONCAT(DISTINCT mapped_maincity) as main_cities FROM contract_info_details";
		$res_ct = parent::execQuery($sql_ct, $this->conn_dcdash);
		if($res_ct && mysql_num_rows($res_ct))
		{
			$row_ct = mysql_fetch_assoc($res_ct);
			if($row_ct['main_cities'])
			{
				$cities_arr = explode(',',$row_ct['main_cities']);
			}
		}
	}
	
	if(count($cities_arr)>0)
	{
		foreach($cities_arr as $city)
		{
			$citycond = " mapped_maincity= '".$city."'";
			$sql = "SELECT companyname,parentid,data_city,mapped_maincity,IF(mapped_maincity = data_city, 'maincity', 'remotecity') AS   cityidntifier,
						   AREA,pincode,latitude,longitude,latitude_actual,longitude_actual, dealclosedby,dealclosedon, dealClosedByName,
						   campnamebycampaignid, campnamedisplayed, campaignwisebudget, finbudget 
						   FROM contract_info_details ".$wherecond." ".$citycond." ".$circle_cond." ".$date_cond."
						   ORDER BY dealclosedon DESC ".$this->limit." ";
			$res = parent::execQuery($sql, $this->conn_dcdash);
			if($res && mysql_num_rows($res))
			{
				while($row = mysql_fetch_assoc($res))
				{
					$deal_close_arr[$row['mapped_maincity']][] = $row;
				}
			}
			
			if(DEBUG_MODE)
			{
				echo '<br>$sqlarr'.$sql ; 
				echo '<br>$resarr'.$res;
				echo '<br>mysql num rows :: '.mysql_num_rows($res);
			}
			
		}
	}
	
	
	if(count($deal_close_arr)>0)
	{
		$data['errorCode']   = 0;
		$data['errorMsg']    = 'successful';
		$data['data_count']  = count($deal_close_arr);
		$data['records']     = $deal_close_arr;
	}
	else
	{
		$data['errorCode']   = 1;
		$data['errorMsg']    = 'No Data';
		$data['data_count']  = count($deal_close_arr);
		$data['records']     = '';
	}
	
	return $data;
	
	
}

function employeewisecount()
{
	
	$resultarray= array();	
	$temparray= array();
	
	$wherecond= " Where ".$this->dealclosedonbetween; 
	
	$dealclosedby_sql="select group_concat(distinct dealclosedby) as dealclosedby from contract_info_details ".$wherecond;	
	$dealclosedby_res = parent::execQuery($dealclosedby_sql, $this->conn_dcdash);
	
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$dealclosedby_sql ;
	}
	
	$dealclosedby_arr= mysql_fetch_assoc($dealclosedby_res);
	
	$employeearry = explode(',',$dealclosedby_arr['dealclosedby']);
	
	foreach($employeearry as $empcode)
	{
		$empcodearray= $this->campaignwisecount($empcode);
		$temparray[$empcode]['campaignwisecount']=$empcodearray['data'];		
	}
	
	$resultarray['data'] =$temparray;
	$resultarray['errocode'] =0;
	$resultarray['erromsg'] ='No error';	
	
	return $resultarray;
}

function getCitylevelgeocode()
{
$city= array();
$city['ahmedabad']['geocode']	= array('latitude'=>23.028792000000 , 'longitude'=>72.578400000000);
$city['bangalore']['geocode']	= array('latitude'=>12.966793871553 , 'longitude'=>77.598405772589);
$city['chennai']['geocode']		= array('latitude'=>13.069061897342 , 'longitude'=>80.243982476688);
$city['delhi']['geocode']		= array('latitude'=>28.664407557287 , 'longitude'=>77.090145924828);
$city['hyderabad']['geocode']	= array('latitude'=>17.400725280173 , 'longitude'=>78.437893489810);		
$city['kolkata']['geocode']		= array('latitude'=>22.562968864829 , 'longitude'=>88.389698473675);		
$city['mumbai']['geocode']		= array('latitude'=>19.090748276813 , 'longitude'=>72.876111853449);		
$city['pune']['geocode']		= array('latitude'=>18.523822174443 , 'longitude'=>73.872909291912);

return $city;	
}

private function getdealclosecontratwithgeocode()
{

	$city= array();	
	
	$wherecond= " Where ".$this->dealclosedonbetween; 
		
	$sql="select parentid,pincode,latitude,longitude,mapped_maincity,data_city,cityidntifier from 
					(
						SELECT parentid,pincode,latitude,longitude,IF(mapped_maincity = data_city, 'maincitycontractlist', 'remotecitycontractlist') as cityidntifier,mapped_maincity,data_city from contract_info_details ".$wherecond."
					)a";
	$res = parent::execQuery($sql, $this->conn_dcdash);
	
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$sql ;
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	
	if(mysql_num_rows($res)>0)
	{
		while($row= mysql_fetch_assoc($res))
		{
			$city[$row['mapped_maincity']][$row['cityidntifier']][] = array('parentid'=>$row['parentid'] ,'pincode'=>intval($row['pincode']) ,'latitude'=>floatval($row['latitude']) ,'longitude'=>floatval($row['longitude']),'data_city'=>$row['data_city']);
		}
	}


	if(DEBUG_MODE)
	{
		echo '<br> city' ; print_r($city);		
	}


	return $city;

}

function getCityWiseInstrumentAmount()
{
	
	$wherecond= " WHERE ".$this->dealclosedonbetween; 
	
	$sql_city = "SELECT cityname,mapped_cityname FROM tbl_city_master";
	$res_city = parent::execQuery($sql_city, $this->dbConDjds);
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$sql_city ;
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	
	if(mysql_num_rows($res_city)>0)
	{
		while($row_city= mysql_fetch_assoc($res_city))
		{
			$mapped_city[strtolower($row_city['cityname'])] = strtolower($row_city['mapped_cityname']);
		}
	}
	
	$sql = "SELECT SUM(instrumentAmount) as total_instrument_amount,instrumentType,data_city FROM contract_payment_details ".$wherecond." GROUP BY instrumentType,data_city";
	$res = parent::execQuery($sql, $this->conn_dcdash);
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$sql ;
		echo '<br>$rows'.mysql_num_rows($res) ;
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	
	if(mysql_num_rows($res)>0)
	{
		while($row= mysql_fetch_assoc($res))
		{
			$mapped_city[strtolower($row['data_city'])] = ($mapped_city[strtolower($row['data_city'])]) ? $mapped_city[strtolower($row['data_city'])] : 'Other Cities';
			
			$city[$mapped_city[strtolower($row['data_city'])]][$row['instrumentType']]['amount'] += $row['total_instrument_amount'];
			$city[$mapped_city[strtolower($row['data_city'])]]['city_instrument_amount']  += $row['total_instrument_amount'];
			$city['total_instrument_amount']  += $row['total_instrument_amount'];
		}
		return $city;
	}
}

function citywisecountwithgeocode()
{
	// in geo code wise count it will return city wise count 
	$resultarray= array();	
	$temparray= array();
	
	$Citylevelgeocode = $this->getCitylevelgeocode();
	$getdealclosecontrat = $this->getdealclosecontratwithgeocode();
		
	$citywisecountarr=  $this->citywisecount();
	$temparray		= $citywisecountarr['data'];	
	
	foreach($temparray as $city=>$cityarr)
	{
		if(isset($Citylevelgeocode[$city]) && isset($getdealclosecontrat[$city]))
		{
			$temparray[$city]['geocode']=$Citylevelgeocode[$city]['geocode'];
			$temparray[$city]['contractlist']=$getdealclosecontrat[$city];
		}
	}
	
	
	$resultarray['data'] =$temparray;
	$resultarray['errocode'] =0;
	$resultarray['erromsg'] ='No error';	
	
	if(DEBUG_MODE)
	{		
		echo '$resultarray' ; print_r($resultarray);
	}

	return $resultarray;
}

function recentdealclose()
{	
	$resultarray= array();	
	$temparray= array();
	
	if($this->reqcity)
	{
		$citycond = " WHERE mapped_maincity= '".$this->reqcity."' ";	
	}
	
	$sql="select parentid ,data_city ,mapped_maincity ,companyname ,city ,trim( ',' from replace(campnamedisplayed,'##',',')  ) as campaign, dealclosedby, dealclosedon, dealClosedByName,campnamebycampaignid, campaignwisebudget, finbudget from contract_info_details ".$citycond." order by dealclosedon desc limit 10 ";
	$res = parent::execQuery($sql, $this->conn_dcdash);
	
	if(DEBUG_MODE)
	{
		echo '<br>$sql'.$sql ;
	}
	$i=0;
	while($arr= mysql_fetch_assoc($res))
	{
		$temparray[$i++]= $arr;
	}	
	
	$resultarray['data'] =$temparray;
	$resultarray['errocode'] =0;
	$resultarray['erromsg'] ='No error';	
	
	return $resultarray;
	
}

function dealclosegraph()
{
	return $this->citywisecount();	
}

function mysql_real_escape_custom($string)
{
		
		$con = mysql_connect($this->conn_dcdash[0], $this->conn_dcdash[1], $this->conn_dcdash[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

//$this->conn_dcdash = $db['dcdash'];
}
	
function updatecontactinfo()
{	
	$query_prm = null;
	$query_sub =null;	
	$query_tbl = null;
	$fieldarry = array();
	
	$tablearray = array('tbl_companymaster_generalinfo_shadow','tbl_companymaster_extradetails_shadow','tbl_business_temp_data','tbl_temp_intermediate','tbl_catspon_temp','tbl_comp_banner_temp','tbl_companymaster_finance','tbl_companymaster_finance_shadow','tbl_companymaster_finance_temp','tbl_companymaster_finance_national','tbl_companymaster_finance_national_shadow','tbl_smsbid_temp','tbl_national_listing_temp');
	
		
	$contract_info_details = json_decode($this->params['contract_info_details'], true);
	
	
	$query_prm =" INSERT INTO contract_info_details SET 
					  parentid ='".$this->parentid."',
					  version  = '".$this->version."',
					  data_city ='".$this->data_city."' ";	
	
	if(isset($contract_info_details['tbl_companymaster_generalinfo_shadow']))
	{		
		$companyname	= $contract_info_details['tbl_companymaster_generalinfo_shadow']['companyname'];
		$sphinx_id			= $contract_info_details['tbl_companymaster_generalinfo_shadow']['sphinx_id'];
		$docid			= $contract_info_details['tbl_companymaster_generalinfo_shadow']['docid'];
		$state			= $contract_info_details['tbl_companymaster_generalinfo_shadow']['state'];
		$city			= $contract_info_details['tbl_companymaster_generalinfo_shadow']['city'];
		$area			= $contract_info_details['tbl_companymaster_generalinfo_shadow']['area'];
		$pincode		= $contract_info_details['tbl_companymaster_generalinfo_shadow']['pincode'];
		$latitude		= $contract_info_details['tbl_companymaster_generalinfo_shadow']['latitude'];
		$longitude		= $contract_info_details['tbl_companymaster_generalinfo_shadow']['longitude'];
		
		$query_sub = 	" companyname = '".addslashes(stripslashes($companyname))."' , 
						  sphinx_id = '".addslashes(stripslashes($sphinx_id))."' , 
						  docid = '".addslashes(stripslashes($docid))."' , 
						  state = '".addslashes(stripslashes($state))."' , 
						  city = '".addslashes(stripslashes($city))."' ,
						  area = '".addslashes(stripslashes($area))."' ,
						  pincode = '".$pincode."',
						  latitude = '".$latitude."' ,
						  longitude = '".$longitude."',";
		
	}
	
	
	if(isset($this->params['mapped_cityname']))
	{
		$query_sub .=" mapped_maincity ='".addslashes(stripslashes($this->params['mapped_cityname']))."',";

	}
	
	
	if($this->ucode)
	{
		
		$dealclosedby	= $this->ucode;
		$dealclosedon	= date('Y-m-d H:i:s');	
		
		$query_sub .=" dealclosedby 	='".$dealclosedby."',
					   dealClosedByName ='".addslashes($this->uname)."',
					   dealclosedon 	='".$dealclosedon."',";
		
	}elseif(isset($contract_info_details['tbl_companymaster_extradetails_shadow']))
	{
		$dealclosedby	= $contract_info_details['tbl_companymaster_extradetails_shadow']['updatedBy'];
		$dealclosedon	= $contract_info_details['tbl_companymaster_extradetails_shadow']['updatedOn'];	
		
		$query_sub .=" dealclosedby 	='".$dealclosedby."',
					   dealClosedByName ='".addslashes($this->uname)."',
					   dealclosedon 	='".$dealclosedon."',";
	}
	
	if(isset($contract_info_details['tbl_payment_type']))
	{
		$payment_type_str	= $contract_info_details['tbl_payment_type']['payment_type'];
		$payment_type_arr	= explode(',',$payment_type_str);		
		$payment_type_arr = array_filter($payment_type_arr);
		
		if(count($payment_type_arr))
		{
			$query_sub .=" campnamedisplayed ='##".implode('##',$payment_type_arr)."##',";			
			$campaignnameslist_query= "insert ignore into  campaignnameslist (campaignname) values (\"".implode('"),("',$payment_type_arr)."\") ";

			$res = parent::execQuery($campaignnameslist_query, $this->conn_dcdash);
						
			if(DEBUG_MODE)
			{
				echo '<br> payment_type_str-- '.$this->params['mapped_cityname'];
				
				echo '<br> payment_type_str-- '.$payment_type_str;
				echo '<br> $this->params ' ; print_r($this->params);				
				echo '<br> $payment_type_arr ' ; print_r($payment_type_arr);				
				echo '<br> final_query-- '.$campaignnameslist_query;
				echo '<br> $contract_info_details tbl_payment_type' ; print_r($contract_info_details['tbl_payment_type']);
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
		}
	}	
	
	if(isset($contract_info_details['tbl_companymaster_finance_temp']))
	{
		$companymaster_finance_temp	= $contract_info_details['tbl_companymaster_finance_temp'];		
		$campaignidlist='';
		$finbudget='';
		$campaignwisebudget='';
		$arrCampaign= array();		
		
		foreach ($companymaster_finance_temp as $key=>$value)
		{
			if($value['recalculate_flag']==1)
			{
				array_push($arrCampaign,$value['campaignid']);					
				$finbudget	= 	$finbudget+floatval($value['budget']);
				$campaignwisebudget	=	$campaignwisebudget.$value['campaignid'].'-'.floatval($value['budget']).',';
			}
		}
				
		if(count($arrCampaign))
		{		
			$campaignidlist = "##". implode('##',$arrCampaign) ."##";
			$campaignwisebudget = rtrim($campaignwisebudget,',');
			$campnamebycampaignid = $this->getCampname($arrCampaign);
			$campnamebycampaignid = '##'.str_replace(',','##',$campnamebycampaignid).'##';			
			
			$query_sub .=" campaignidlist ='".$campaignidlist."',
							campaignwisebudget ='".$campaignwisebudget."',
							finbudget ='".$finbudget."',
							campnamebycampaignid ='".$campnamebycampaignid."',";
		
		}			
						
		if(DEBUG_MODE)
		{
			
			echo '<br> $finbudget '.$finbudget;
			echo '<br> campaignidlist '.$campaignidlist;
			echo '<br> campnamebycampaignid '.$campnamebycampaignid;
			echo '<br> campaignwisebudget '.$campaignwisebudget;
			
		}
	}
	
	foreach($tablearray as $tblname)
	{
		
		if(isset($contract_info_details[$tblname]))
		{
			$tblval = $contract_info_details[$tblname];			
			$query_tbl .= " ".$tblname." = '".$this->mysql_real_escape_custom(json_encode($tblval)) ."',";
		}
	}
	
	
	$final_query = 	trim($query_prm,',') ." , ". trim($query_sub,',')." , ".trim($query_tbl ,','). "
					ON DUPLICATE KEY UPDATE 
					" .trim($query_sub,',')." , ".trim($query_tbl ,',');

	$res = parent::execQuery($final_query, $this->conn_dcdash);
	
		
	if(DEBUG_MODE)
	{
		echo '<br> final_query-- '.$final_query;
		echo '<br> resultset ' ; print_r($res);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	
	
}


function printerror($errorcode,$errormsg,$additionalinfo=null)
{
	$result['errocode'] = $errorcode;
	$result['erromsg'] = $errormsg;
	if($apiresult!=null)
	{
		$result['additionalinfo'] = $additionalinfo;
	}
	$resultstr= json_encode($result);
	print($resultstr);
	die;
}


function test()
{
	// testing 
	

}	

function getLiteInstrData()
{
	$sql_instrument = "SELECT instrument_type, instrument_number AS cheque_number, instrument_date AS cheque_date, instrument_deposit_location AS cheque_dep_loc,
							  bank_micr AS cheque_micr, bank_city AS cheque_bank_city, bank_name AS cheque_bank_name, bank_ifsc AS cheque_ifsc, 
							  bank_branch AS cheque_bank_branch, bank_acc_number AS cheque_acc_number, IF(LOWER(bank_acc_type) = 'savings',1,2) AS cheque_acc_type,  round(instrument_amount) AS instr_amt, instrument_image_path AS cheque_image, instrument_deposit_location AS deposit_location 
							  FROM tbl_geniolite_instrument_data
							  WHERE parentid ='".$this->parentid."' AND data_city='".$this->data_city."' AND VERSION = '".$this->version."'";
	$res_instrument = parent::execQuery($sql_instrument, $this->dbConidc);
	if($this->trace)
	{
		echo '<br> sql :: '.$sql_instrument;
		echo '<br> res :: '.$res_instrument;
		echo '<br> row :: '.parent::numRows($res_instrument);
	}
	if($res_instrument && parent::numRows($res_instrument)>0)
	{
		$instrument_details_arr = array();
		while($row_instrument = parent::fetchData($res_instrument))
		{
			if(strtolower($row_instrument['instrument_type']) == 'cash')
			{
				$instrument_details_arr['cash'][] = array('instr_amt'=> $row_instrument['instr_amt'], 'deposit_location'=> $row_instrument['deposit_location']);
			}
			
			if(strtolower($row_instrument['instrument_type']) == 'cheque')
			{
				$instrument_details_arr['cheque'][] = $row_instrument;
			}
			
		}
		return $instrument_details_arr;
	}else
	{
		$instrument_details_arr['error']['msg']  = 'No Data Found !';
		$instrument_details_arr['error']['code'] = 1;
	}
}

function getAllLiteData()
{
	if(trim($this->dealCloseType) !='')
	{
		if( trim($this->dealCloseType) == '1' )
			$dealCloseType_cond = " AND dealCloseType IN (1,2) ";
		else
			$dealCloseType_cond = " AND dealCloseType = ".$this->dealCloseType;
		
	}
	
	
	if(count($this-> mapped_data_cities) > 0)
	$data_city_cond = " AND data_city IN ('".implode("','",$this-> mapped_data_cities)."') ";
	
	
	if(trim($this->status) != '')
	$done_flag_cond = " AND done_flag = ".$this->status;
	
	if(trim($this->ucode) != '')
	$ucode_cond = "  AND user_code = ".$this->ucode;
	
	if(trim($this->module) != '')
	$module_cond = "  AND module = ".$this->module;
	
	
	
	$sql_byuser_gl = "SELECT user_code,user_name,COUNT(parentid) as gl_deal_cnt FROM online_regis1.tbl_selfsignup_contracts WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond.$module_cond." GROUP BY user_name";
	$res_byuser_gl = parent::execQuery($sql_byuser_gl, $this->dbConidc);
	if(DEBUG_MODE)
	{
		print_r($this->dbConidc);
		echo '<br> campaignnameslist_query-- '.$sql_byuser_gl;
		echo '<br> $res ' ; print_r($res_byuser_gl);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res_byuser_gl);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res_byuser_gl && mysql_num_rows($res_byuser_gl)>0)	
	{
		
		while($row_byuser_gl = mysql_fetch_assoc($res_byuser_gl))
		{
			$deal_close_user_mis [$row_byuser_gl['user_code']]['user_code']   = $row_byuser_gl['user_code'];
			$deal_close_user_mis [$row_byuser_gl['user_code']]['user_name']   = $row_byuser_gl['user_name'];
			$deal_close_user_mis [$row_byuser_gl['user_code']]['gl_deal_cnt'] = $row_byuser_gl['gl_deal_cnt'];
			$deal_close_user_mis [$row_byuser_gl['user_code']]['g_deal_cnt']  = 0;
		}
	}
	
	$sql_byuser_g = "SELECT user_code,user_name,COUNT(parentid) as g_deal_cnt FROM online_regis1.tbl_online_dealclose_contracts WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond." GROUP BY user_name";
	$res_byuser_g = parent::execQuery($sql_byuser_g, $this->dbConidc);
	if(DEBUG_MODE)
	{
		print_r($this->dbConidc);
		echo '<br> campaignnameslist_query-- '.$sql_byuser_g;
		echo '<br> $res ' ; print_r($res_byuser_g);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res_byuser_g);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res_byuser_g && mysql_num_rows($res_byuser_g)>0)	
	{
		
		while($row_byuser_g = mysql_fetch_assoc($res_byuser_g))
		{
			
			$deal_close_user_mis [$row_byuser_g['user_code']]['user_code']   = $row_byuser_g['user_code'];
			$deal_close_user_mis [$row_byuser_g['user_code']]['user_name']   = $row_byuser_g['user_name'];
			$deal_close_user_mis [$row_byuser_g['user_code']]['g_deal_cnt']  = $row_byuser_g['g_deal_cnt'];
			$deal_close_user_mis [$row_byuser_g['user_code']]['gl_deal_cnt'] = ($deal_close_user_mis [$row_byuser_g['user_code']]['gl_deal_cnt']) > 0 ? $deal_close_user_mis [$row_byuser_g['user_code']]['gl_deal_cnt'] : 0;
			
		}
		
	}
	
	if(count($deal_close_user_mis)>0)
	{
		$deal_close_user_mis_new = array();
		$index = 0;
		foreach($deal_close_user_mis as $user_key => $user_data)
		{
			$deal_close_user_mis_new[$index] = $user_data;
			$index ++;
		}
	}
	$return_data['user_data_mis']      = $deal_close_user_mis_new;
	
	
	$sql = "SELECT COUNT(parentid) as counts,done_flag,dealclosetype FROM online_regis1.tbl_selfsignup_contracts WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond.$done_flag_cond.$module_cond." GROUP BY dealclosetype,done_flag ";
	$res = parent::execQuery($sql, $this->dbConidc);
	if(DEBUG_MODE)
	{
		print_r($this->dbConidc);
		echo '<br> campaignnameslist_query-- '.$sql;
		echo '<br> $res ' ; print_r($res);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res && mysql_num_rows($res)>0)	
	{
		$dealclose_type_arr = array('1'=>'FP','2'=>'FP','3'=>'BA');
		$current_dealclose_status_arr = array('0'=>'Pending','1'=>'Done','2'=>'Failed');
		while($row = mysql_fetch_assoc($res))
		{
			$deal_close_data_mis[$dealclose_type_arr[$row['dealclosetype']]][$current_dealclose_status_arr[$row['done_flag']]] += $row['counts'];
		}
	}
	
	
	$sql_genio = "SELECT COUNT(parentid) as counts,done_flag,dealclosetype FROM online_regis1.tbl_online_dealclose_contracts WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond.$done_flag_cond." GROUP BY dealclosetype,done_flag ";
	$res_genio = parent::execQuery($sql_genio, $this->dbConidc);
	if(DEBUG_MODE)
	{
		print_r($this->dbConidc);
		echo '<br> campaignnameslist_query-- '.$sql_genio;
		echo '<br> $res ' ; print_r($res_genio);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res_genio);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res_genio && mysql_num_rows($res_genio)>0)	
	{
		$dealclose_type_arr = array('1'=>'FP','2'=>'PBA','3'=>'BA');
		$current_dealclose_status_arr = array('0'=>'Pending','1'=>'Done','2'=>'Failed');
		while($row_genio = mysql_fetch_assoc($res_genio))
		{
			$genio_deal_close_data_mis[$dealclose_type_arr[$row_genio['dealclosetype']]][$current_dealclose_status_arr[$row_genio['done_flag']]] = $row_genio['counts'];
		}
	}
	
	$sql_genio = "SELECT * FROM online_regis1.tbl_online_dealclose_contracts  WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond.$done_flag_cond.$ucode_cond."  ORDER BY requested_date DESC ";
	$res_genio = parent::execQuery($sql_genio, $this->dbConidc);
	if(DEBUG_MODE)
	{
		
		echo '<br> campaignnameslist_query-- '.$sql;
		echo '<br> $res ' ; print_r($res_genio);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res_genio);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res_genio && mysql_num_rows($res_genio)>0)	
	{
		while($row_genio = mysql_fetch_assoc($res_genio))
		{
			$genio_deal_close_data[] = $row_genio;
			$genio_mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row_genio['data_city']))]]['count'] += 1;
			
			if(strtolower(trim($row_genio['module'])))
			{
				$genio_mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row_genio['data_city']))]][strtolower(trim($row_genio['module'])).'_count'] += 1;
				
				$genio_mapped_city_count['Total'][strtolower(trim($row_genio['module'])).'_count'] += 1; 
			}
			else
				$genio_mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row_genio['data_city']))]]['other_count'] += 1;
		}
		
		
		$return_data['genio_data']      = $genio_deal_close_data;
		$return_data['genio_city_mis']  = $genio_mapped_city_count;
		$return_data['genio_mis']       = $genio_deal_close_data_mis;
		
	}
	
	$sql_device = " SELECT a.device_type,b.parentid,b.trans_id,b.data_city
					FROM online_regis1.tbl_deal_close_device_log a JOIN online_regis1.tbl_selfsignup_contracts b
					ON  a.parentid = b.parentid
					AND a.master_transaction_id = b.trans_id
					WHERE ".$this->dealclosedonbetween_device." ";
	$res_device = parent::execQuery($sql_device, $this->dbConidc);
	if(DEBUG_MODE)
	{
		
		echo '<br> qry_device-- '.$sql_device;
		echo '<br> $res_device ' ; print_r($res_device);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res_device);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res_device && mysql_num_rows($res_device)>0)	
	{
		while($row_device = mysql_fetch_assoc($res_device))
		{
			$device_city_data_count[$this-> all_mapped_main_cities[strtolower(trim($row_device['data_city']))]][strtolower(trim($row_device['device_type']))]['count'] += 1;
			
			$device_data_mis[strtolower(trim($row_device['device_type']))]['count'] += 1;	
			
			if(stristr(strtolower(trim($row_device['device_type'])),'ios'))
			{
				$device_os_mis['iOS']['count'] += 1;
			}
			else if(stristr(strtolower(trim($row_device['device_type'])),'android'))
			{
				$device_os_mis['Android']['count'] += 1;
			}
			else
			{
				$device_os_mis['Others']['count'] += 1;
			}
			
		}
		
		$return_data['device_os_mis']        = $device_os_mis;
		$return_data['device_data_mis']      = $device_data_mis;
		$return_data['device_city_data_mis'] = $device_city_data_count;
		
	}
	
	$sql = "SELECT * FROM online_regis1.tbl_selfsignup_contracts   WHERE ".$this->dealclosedonbetween.$data_city_cond.$dealCloseType_cond.$done_flag_cond.$ucode_cond.$module_cond."  ORDER BY requested_date DESC ";
	$res = parent::execQuery($sql, $this->dbConidc);
	if(DEBUG_MODE)
	{
		
		echo '<br> campaignnameslist_query-- '.$sql;
		echo '<br> $res ' ; print_r($res);			
		echo '<br><b>num  rows:</b>'.mysql_num_rows($res);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	if($res && mysql_num_rows($res)>0)	
	{
		while($row = mysql_fetch_assoc($res))
		{
			$deal_close_data[] = $row;
			
			$mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row['data_city']))]]['count'] += 1;
			
			if(strtolower(trim($row['module'])))
			{
				$mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row['data_city']))]][strtolower(trim($row['module'])).'_count'] += 1;
				
				$mapped_city_count['Total'][strtolower(trim($row['module'])).'_count'] += 1; 
			}
			else
				$mapped_city_count[$this-> all_mapped_main_cities[strtolower(trim($row['data_city']))]]['other_count'] += 1;
			
		}
		
		$return_data['error'] = 0;
		$return_data['data']      = $deal_close_data;
		$return_data['city_mis']  = $mapped_city_count;
		$return_data['mis']       = $deal_close_data_mis;
		
	}else {
		$return_data['error'] = 1;
		$return_data['message']   = 'no data found';
	}
	return $return_data;
	
		
}
	
function getActualCampaigNames()
{	
	$campaignnameslist_query= "SELECT * FROM campaignnameslist";
	$res = parent::execQuery($campaignnameslist_query, $this->conn_dcdash);	
	
	if(DEBUG_MODE)
	{
		
		echo '<br> campaignnameslist_query-- '.$campaignnameslist_query;
		echo '<br> $res ' ; print_r($res);			
		echo '<br><b>num rows:</b>'.mysql_num_rows($res);
		echo '<br><b>Error:</b>'.$this->mysql_error;
	}
	
	if($res && mysql_num_rows($res)>0)
	{
		while($row= mysql_fetch_assoc($res))
		{
			$campaignnames[$row['campaignname']]['display_name'] = ucwords($row['tagging_name']);
		}
	}

	return $campaignnames;
}

function getCampname($camparr)
{	
	$campaignnames=null;
	$camparr= array_filter($camparr);
	if(count($camparr))
	{
		$campaignnameslist_query= " select  group_concat(distinct campaignName) as campaignName,count(campaignId) as cnt from payment_campaign_master where campaignId in (".implode(',',$camparr).") ";
		$res = parent::execQuery($campaignnameslist_query, $this->conn_dcdash);	
		
		$arr= mysql_fetch_assoc($res);
		if($arr['cnt']>0)
		{
			$campaignnames= $arr['campaignName'];
		}
		
		if(DEBUG_MODE)
		{
			
			echo '<br> campaignnameslist_query-- '.$campaignnameslist_query;
			echo '<br> $res ' ; print_r($res);			
			echo '<br><b>$campaignnames:</b>'.$campaignnames;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
			
	}
	
	return $campaignnames;
}

function getMappedCities()
{
	$sql = "SELECT DISTINCT data_city FROM tbl_city_master WHERE dialer_mapped_cityname='".$this->mapped_main_city."' AND data_city !=''";
	$res = parent::execQuery($sql, $this->dbConidc);
	if(DEBUG_MODE)
		{
			
			echo '<br> campaignnameslist_query-- '.$sql;
			echo '<br> $res ' ; print_r($res);			
			echo '<br><b>rows :</b>'.mysql_num_rows($res);
		}
		
	if($res && mysql_num_rows($res))
	{
		$data_cities = array();
		while($row = mysql_fetch_assoc($res))
		{
			$data_cities [] = strtolower($row['data_city']);
		}
		
		return $data_cities;
	}

}

function getAllMappedCities()
{
	$sql = "SELECT dialer_mapped_cityname,data_city FROM tbl_city_master WHERE data_city !=''";
	$res = parent::execQuery($sql, $this->dbConidc);
	if(DEBUG_MODE)
		{
			
			echo '<br> campaignnameslist_query-- '.$sql;
			echo '<br> $res ' ; print_r($res);			
			echo '<br><b>rows :</b>'.mysql_num_rows($res);
		}
		
	if($res && mysql_num_rows($res))
	{
		$data_cities = array();
		while($row = mysql_fetch_assoc($res))
		{
			$data_cities [strtolower($row['data_city'])] = strtolower($row['dialer_mapped_cityname']);
		}
		
		return $data_cities;
	}

}



}
