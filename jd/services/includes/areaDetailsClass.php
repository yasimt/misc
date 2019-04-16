<?php

class areaDetailsClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $ucode		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST','BAND');
	

	function __construct($params)
	{		
		$this->params = $params;	
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
			
		$this->setServers();
		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}

		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}
		if(trim($this->params['module']) != "")
		{
			$this->module  = strtoupper(trim($this->params['module'])); //initialize paretnid
		}
		if(trim($this->params['latitude']) != "")
		{
			$this->area_latitude  = $this->params['latitude']; //initialize latitude
		}
		
		if(trim($this->params['longitude']) != "")
		{
			$this->area_longitude  = $this->params['longitude']; //initialize longitude
		}
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}

		if(trim($this->params['opt']) != "" && $this->params['opt'] != null)
		{
			$this->opt  = strtoupper($this->params['opt']); //initialize order of results			
			if(!in_array($this->opt,$this->optvalset))
			{echo json_encode('Please provide correct area selectio option '); exit; }
		}
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['master'];
		$this->dbContme			= $db[$data_city]['tme_jds']['master'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];		
		$this->dbConbudget  	= $db[$data_city]['db_budgeting']['master'];
		if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){
			$this->mongo_flag = 1;
		}
		if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
			$this->mongo_tme = 1;
		}
	}
	
	function getArea()
	{		
		$resultarr = array();
		switch($this->opt)
		{
			case 'ALL':
			$resultarr= $this->getArea_ALL();
			break;
			case 'ZONE':
			$resultarr= $this->getArea_ZONE();
			break;
			case 'NAME':
			$resultarr= $this->getArea_NAME();
			break;
			case 'PIN':
			$resultarr= $this->getArea_PIN();
			break;
			case 'DIST':
			$resultarr= $this->getArea_DIST();
			break;
			case 'BAND':
			$resultarr= $this->getArea_BAND();
			break;
		}
		
		return $resultarr;
	}
	
	
	function getArea_ALL()
	{
		$areaArr =array();		
		$sql="select pincode,group_concat(areaname ORDER BY callcnt_perday DESC) as areaname from tbl_areamaster_consolidated_v3  where data_city='".$this->data_city."' and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1  group by pincode  ORDER BY pincode";

		
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area All Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res_area && $num_rows > 0)
		{
			$i=0;
			while($row=mysql_fetch_assoc($res_area))
			{
				//$areaArr[$i]['pin'] = $row['pincode'];
				//$areaArr[$i]['area'] = $row['areaname'];				
				$areaArr[$row['pincode']]['pin'] = $row['pincode'];
				$areaArr[$row['pincode']]['area'] = $row['areaname'];				
				$i++;
			}
		}
		
		$pincodejson = $this->getContractPincode();
		if($pincodejson['error']['code'] == 0){
			$return_array['pincodejson'] = json_decode($pincodejson['results'],1);
		}
		
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;
	}

	function getContractPincode()
	{
		$pincodejson = '';
		$return_array = array();
		$sql="select pincodejson from tbl_contract_pincodelist where  parentid='".$this->parentid."'";
		$res 	= parent::execQuery($sql, $this->dbConbudget);
		$num_rows		= mysql_num_rows($res);
		if(DEBUG_MODE)
		{
			echo '<br><b>getContractPincode:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res && $num_rows > 0){
			while($row=mysql_fetch_assoc($res))
			{
				$pincodejson = $row['pincodejson'];
			}
			$return_array['results'] = $pincodejson;
			$return_array['error']['code'] = "0";
			$return_array['error']['msg'] = "data found";
		}else {
			$return_array['results'] = '';
			$return_array['error']['code'] = "1";
			$return_array['error']['msg'] = "data not found";
		}
		return $return_array;
	}

	
	function getArea_BAND()
	{

		$datacity = $this->params['data_city'];
		$parentid = $this->params['parentid'];
		$sql="select count(distinct(pincode)) as citytotalcount from d_jds.tbl_areamaster_consolidated_v3 where data_city='".$datacity."' AND display_flag=1 and type_flag=1";
		$city_count	= parent::execQuery($sql, $this->dbConDjds_slave);
		$totalpincodecount=0;
		if($city_count)
		{
			while($row=mysql_fetch_assoc($city_count))
			{	
				$totalpincodecount = $row['citytotalcount'];
			}
		}
		$pincodearray=array();
		if(intval($this->params['pincode'])==0){
			if($this->module=='TME'){				
				if($this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] 	= "pincode";
					$row = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$pincodesql ="SELECT pincode from tme_jds.tbl_companymaster_generalinfo_shadow where parentid='$parentid'";
					$res_pincode 	= parent::execQuery($pincodesql, $this->dbContme);
					$num_rows		= mysql_num_rows($res_pincode);
					if($res_pincode && $num_rows > 0){
						$row=mysql_fetch_assoc($res_pincode);		
					}
				}
				$pincode = $row['pincode'];
				$pincode =intval($pincode);
			}
			if($this->module=='CS'){
				$pincodesql ="SELECT pincode from db_iro.tbl_companymaster_generalinfo_shadow where parentid='$parentid'";
				$res_pincode 	= parent::execQuery($pincodesql, $this->dbConIro);
				$num_rows		= mysql_num_rows($res_pincode);
				if($res_pincode && $num_rows > 0){
					$row=mysql_fetch_assoc($res_pincode);	
					$pincode = $row['pincode'];
				}
				$pincode =intval($pincode);
			}
			if($this->module=='ME'){
				if($this->mongo_flag == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] 	= "pincode";
					$row = $this->mongo_obj->getData($mongo_inputs);
				}
				else
				{
					$pincodesql ="SELECT pincode from tbl_companymaster_generalinfo_shadow where parentid='$parentid'";
					$res_pincode 	= parent::execQuery($pincodesql, $this->dbConIdc);
					$num_rows		= mysql_num_rows($res_pincode);
					if($res_pincode && $num_rows > 0)
					{
						$row=mysql_fetch_assoc($res_pincode);
					}	
				}
				$pincode = $row['pincode'];
				$pincode =intval($pincode);
			}
		}
		else
			$pincode=intval($this->params['pincode']);

			
		$radius=3.5;
		$radiusincrement=3.5;
		$zonecount=1;
		$zonepincode=array();
		$pincodeinserted=0;
		$count=0;

		while($pincodeinserted!=$totalpincodecount){

				$zonepincode[$zonecount]['pincode']=array();
				$zonepincode[$zonecount]['area']=array();
				$pincodesql ="SELECT fn_city_nearby_pincode('".$pincode."','".$radius."','".$datacity."') as pinarea";
				$res_pincode 	= parent::execQuery($pincodesql, $this->dbConDjds_slave);
				$num_rows	= mysql_num_rows($res_pincode);
				if($num_rows)
				{	

					$row=mysql_fetch_assoc($res_pincode);
					$dataarray = explode('|P|',$row['pinarea']);
					foreach ($dataarray as  $gtpincode) {
						$gtpincode= explode('~',$gtpincode);

						$gtareaname=$gtpincode[1];
						$gtpincode=$gtpincode[0];

						if(!in_array($gtpincode, $pincodearray)){
							array_push($pincodearray,$gtpincode);
							array_push($zonepincode[$zonecount]['pincode'],$gtpincode);
							array_push($zonepincode[$zonecount]['area'],$gtareaname);
							$pincodeinserted++;
						}
					}
					
				}
				$radius+=$radiusincrement;
				$zonecount++;
				if($radius>200)
					break;

				
		}		
		$l=0;
		foreach ($zonepincode as $key => $pincodedetails) {
				$areaArr[$l]['zid']=$key;
				$areaArr[$l]['znm']="By Kms ".($key *3.5);
				
					$j=0;
				foreach ($pincodedetails['pincode'] as $arraydet => $pincodedets) {
					$areaArr[$l]['areapin'][$j]['area'] = $pincodedetails['area'][$arraydet];
					$areaArr[$l]['areapin'][$j]['pincode'] =  $pincodedets;	
					$j++;
				}
				if($j>0){
					$l++;
				} else {
					unset($areaArr[$l]);
				}
			
		}
		
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;
		
	}	
	function getArea_ZONE()
	{
		$areaArr =array();	
		//$sql="select region_id as zoneid,region_name as zonename,areaname as area,pincode from tbl_areamaster_consolidated_v3 where data_city= '".$this->data_city."' and display_flag=1  group by region_id,pincode ORDER BY region_id";
		$sql="select zoneid,concat('ZONE',zoneid) as zonename,areaname as area,pincode from tbl_areamaster_consolidated_v3 where data_city='".$this->data_city."' and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1 group by zoneid,pincode
				ORDER BY zoneid+0";

		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area ZONE Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		if($res_area && $num_rows > 0)
		{		
			$i=-1;	$j=0;
			while($row=mysql_fetch_assoc($res_area))
			{
				
				if($old_zone!=$row['zoneid'])
				{	$i++;  $j=0;}
				$j++;	
				$areaArr[$i]['zid'] = $row['zoneid'];
				$areaArr[$i]['znm'] = $row['zonename'];	
				$areaArr[$i]['areapin'][$j]['area'] = $row['area'];
				$areaArr[$i]['areapin'][$j]['pincode'] = $row['pincode'];
				
				$old_zone = $row['zoneid'];
			}
		}
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;

	}	
	function getArea_NAME()
	{
		$areaArr =array();
		$sql="select areaname,pincode from tbl_areamaster_consolidated_v3 where data_city= '".$this->data_city."' and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1  group by areaname ORDER BY areaname";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area Name Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.mysql_error();
		}
		
		if($res_area && $num_rows > 0)
		{
			$i=0;
			while($row=mysql_fetch_assoc($res_area))
			{
				$areaArr[$i]['area'] = $row['areaname'];
				$areaArr[$i]['pincode'] = $row['pincode'];
				$i++;
			}
		}
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;

	}
	function getArea_PIN()
	{
		$areaArr =array();		
		$sql="select pincode,areaname,group_concat(areaname ORDER BY callcnt_perday DESC) as areadetails from tbl_areamaster_consolidated_v3 where data_city= '".$this->data_city."' and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1  group by pincode  ORDER BY pincode";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area Pin Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		if($res_area && $num_rows > 0)
		{
			$i=0;
			while($row=mysql_fetch_assoc($res_area))
			{
				$areaArr[$i]['pin'] = $row['pincode'];
				$areaArr[$i]['area'] = $row['areaname'];				
				$areaArr[$i]['areadetails'] = $row['areadetails'];				
				$i++;
			}
		}
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;
	}
	
	function getArea_DIST()
	{		
		$areaArr 		= array();
		$return_array	= array();

		$return_array['error']['code'] = "1";
		$return_array['error']['msg'] = "Data Not Found";
		
		$radius='200';
		$datacity = $this->params['data_city'];
		
		if( isset($this->params['rds']) && ($this->params['rds']!=null) && intval($this->params['rds'])>0){
			$radius=$this->params['rds'];
		}
		$pincode=intval($this->params['pincode']);

		//select d_jds.fn_city_nearby_pincode(400064,10,'mumbai');
		
		$pincodesql ="SELECT fn_city_nearby_pincode_v2('".$pincode."','".$radius."','".$datacity."','".$this->params['lat']."','".$this->params['long']."') as pinarea";
		$res_pincode 	= parent::execQuery($pincodesql, $this->dbConDjds_slave);
		$num_rows	= mysql_num_rows($res_pincode);
		if($num_rows)
		{	
			$row=mysql_fetch_assoc($res_pincode);
			
			$pinareafromsp = $row['pinarea'];
			$passedpincode = (string) $pincode;
			
				
			# if physical pincode is not there then append that on start 
			
			if( strpos($pinareafromsp,$passedpincode) === FALSE )
			{				
				$areaname = $this->getPhysicalPincodeArea($pincode);				
				$row['pinarea']= $pincode.'~'.$areaname.'-0'.'|P|'.$row['pinarea'];
			}
			
			
			$dataarray = explode('|P|',$row['pinarea']);
					
			if(DEBUG_MODE)
			{
				echo '<br><b>dbConDjds_slave</b>'.print_r($this->dbConDjds_slave);
				echo '<br><b>row:</b>'.print($pincodesql);
				echo '<br><b>row:</b>'.print_r($row);				
				echo '<br><b>Num Rows:</b>'.$num_rows;
				echo '<br><b>count dataarray- :</b>'.count($dataarray);
				
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			$index=0;
			foreach($dataarray as $pinareaval)
			{
				$pinareavalarr= explode('~',$pinareaval);
				$areaArr[$index]['pin'] = $pinareavalarr[0];
				$areaArr[$index]['area'] = $pinareavalarr[1];
				$index++;
			}
			
			
			$pincodejson = $this->getContractPincode();
			if($pincodejson['error']['code'] == 0){
				$return_array['pincodejson'] = json_decode($pincodejson['results'],1);
			}

			
			$return_array['results'] = $areaArr;
			$return_array['error']['code'] = "0";
			$return_array['error']['msg'] = "Sucessful";

			if(DEBUG_MODE)
			{
				echo '<br><b>dataarray:</b>'.print_r($dataarray);				
				echo '<br><b>--index=:</b>';echo $index.'<br>';
				echo '<br><b>areaArr:</b>'.print_r($areaArr);
				echo '<br><b>return_array:</b>'.print_r($return_array);
			}
		}
		
		return $return_array;
	}

	
	function getcityArea()
	{
		
		$areaArr =array();		
		$sql="select areaname,pincode from tbl_areamaster_consolidated_v3 where data_city='".$this->data_city."' and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1  group by areaname,pincode  ORDER BY areaname";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area City Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.mysql_error();
		}	
		if($res_area && $num_rows > 0)
		{
			$i=0;
			while($row=mysql_fetch_assoc($res_area))
			{
				$areaArr[$i]['area'] = $row['areaname'];
				$areaArr[$i]['pincode'] = $row['pincode'];				
				$i++;
			}
		}
		$return_array['results'] = $areaArr;
		$return_array['error']['code'] = "0";
		$return_array['error']['msg'] = "";
		return $return_array;

	}
	
	
	function getPhysicalPincodeArea($pincode)
	{			
		$returnval = '';
		
		$sql="select areaname from tbl_areamaster_consolidated_v3 where pincode='".$pincode."'and type_flag=1 AND display_flag=1 AND broader_area_flag=0 AND de_display=1 ORDER BY callcnt_perday DESC limit 1 ";
		$res_area 	= parent::execQuery($sql, $this->dbConDjds_slave);
		$num_rows		= mysql_num_rows($res_area);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Area City Query:</b>'.$sql;
			echo '<br><b>Result Set:</b>'.$res_area;
			echo '<br><b>Num Rows:</b>'.$num_rows;
			echo '<br><b>Error:</b>'.mysql_error();
		}	
		
		$row=mysql_fetch_assoc($res_area);
		
		$returnval = $row['areaname'];
			
		return $returnval;
	}
	
}



?>
