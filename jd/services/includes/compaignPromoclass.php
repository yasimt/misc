<?php

class compaignPromoclass extends DB
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
	var  $catsearch		= null;
	var  $data_city		= null;
	var  $campaignid 	= null;
	

	function __construct($params)
	{		
		$this->params = $params;		
		
		
		$this->setServers();
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->IDC    		= $db['online_regis'];
		
		

	}	

	
	function getemployeecontractdata()
	{
	
		$empcode= $this->params['empcode'];
		
		if($empcode==null)
		{
			$returnarr['errorcode']=1;
			$returnarr['msg'] ='empcode is blank';	
			return $returnarr;
		}
		
		$resultarr= array();
		
		#$accessempcodearray = array('013084','10013675','006492');
		
				
		
		$empcodesql= " where mic.empcode='".$empcode."'";
		
		if( isset($this->params['compname']) && strlen(trim($this->params['compname']))>0)
		{
			$empcodesql .= " AND ( mic.compname like '%".addslashes($this->params['compname'])."%' OR mic.hotcategory like '%".addslashes($this->params['compname'])."%') ";
		}
		
		if( isset($this->params['allocated_campaign_flag']) && $this->params['allocated_campaign_flag']>0)
		{
			$empcodesql .= " AND mic.allocated_campaign_flag = ".$this->params['allocated_campaign_flag'];
		}
		
		
		
		/*
		if(!in_array($empcode,$accessempcodearray))
		{
			$returnarr['data']=array();		
			$returnarr['errorcode']=1;
			$returnarr['msg'] ='No data found';
			return $returnarr;
			
		
		}else
		{
			$limit =' limit 10 ';
			$empcodesql='';
		}
		*/
		#$sql = "select parentid,compname,activecampaigns,reviews,rating,no_of_rating,hotcategory,area,pincode,city,data_city from marketingIntCampaign ";
		
		
		
		$maincityarry= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		
		if( in_array($this->params['data_city'],$maincityarry))
		{
			$datacitycond =" and mic.data_city='".$this->params['data_city']."' ";
			
		}else
		{
			$datacitycond =" and mic.data_city not in ('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata') ";
		}
		
		$allocation_master_sql="select  campaign_name,flag_value from tbl_allocation_master";
		$allocation_master_res = parent::execQuery($allocation_master_sql,$this->IDC);
		
				
		while($allocation_master_row= mysql_fetch_assoc($allocation_master_res))
		{
			$allocation_master_arr[intval($allocation_master_row['flag_value'])]=$allocation_master_row['campaign_name'];			
		}
		
		if (DEBUG_MODE)
		{
			echo '<br><b> Query:</b>' . $allocation_master_sql;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<br><b>$allocation_master_arr</b>' ; print_r($allocation_master_arr);
		}
		
		if( isset($this->params['whichModule']) && $this->params['whichModule'] == 'lite' ) {
			$empcodesql= " where (mic.empcode='".$empcode."' or mic.jda_code='".$empcode."')";
			//~ $isJDa = "";
			//~ if( !empty($this->params['type_of_employee']) ) {
				//~ if ( strpos(strtolower($this->params['type_of_employee']),"jda") !== false ) {
					//~ $isJDa = "isJDa";
				//~ }
			//~ }
			//~ if( $isJDa != "" ) {
				//~ $empcodesql = " where mic.jda_code='".$empcode."'";
			//~ }
			
		
			if( isset($this->params['compname']) && strlen(trim($this->params['compname']))>0)
			{
				$empcodesql .= " AND ( mic.compname like '%".addslashes($this->params['compname'])."%' OR mic.hotcategory like '%".addslashes($this->params['compname'])."%') ";
			}
			
			if( isset($this->params['allocated_campaign_flag']) && ($this->params['allocated_campaign_flag']>0))
			{
				$empcodesql .= " AND mic.allocated_campaign_flag = ".$this->params['allocated_campaign_flag'];
			}
			//~ elseif( $isJDa != "" ) {
				//~ $empcodesql .= " AND mic.allocated_campaign_flag = 64";				
			//~ }
			if( !empty($this->params['pincode'])) {
				$empcodesql .= " AND mic.pincode = ".$this->params['pincode'];
			}
			if( !empty($this->params['area'])) {
				$empcodesql .= " AND mic.area LIKE '".addslashes(stripslashes(trim($this->params["area"])))."%'";
			}
			$sql_total_cnt = "select COUNT(DISTINCT mic.parentid) AS cnt from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
			".$empcodesql.$datacitycond."";
			$res_total_cnt = parent::execQuery($sql_total_cnt,$this->IDC);
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $sql_total_cnt;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
			$totCnt = 0;
			if ( mysql_num_rows($res_total_cnt)>0 ) {
				$datacount=mysql_num_rows($res_total_cnt);
				$countArr = mysql_fetch_assoc($res_total_cnt);
				$totCnt = $countArr['cnt'];
			}
			$returnarr['counttot'] = $totCnt;
			if(isset($this->params['pageShow'])){
				$pageCount	=	$this->params['pageShow'];
			}else{
				$pageCount	=	1;
			}
			$multiplierFactor	=	10;
			if( isset($this->params['filter']) && $this->params['filter'] == 1 ) {
				$multiplierFactor	=	10;
				$limitStart	=	($pageCount*$multiplierFactor - $multiplierFactor);
				$limitEnd	=	$pageCount*$multiplierFactor;
				if( !empty($this->params['latitude']) && !empty($this->params['longitude']) ) {
					$sql = "select 3956 * 2 * ASIN(SQRT(
POWER(SIN((".$this->params['latitude']." - ABS(mic.latitude)) * PI()/180 / 2), 2) +  COS(".$this->params['latitude']." * PI()/180 ) * COS(ABS(mic.latitude) * PI()/180) *  POWER(SIN((".$this->params['longitude']." - mic.longitude) * PI()/180 / 2), 2) )) AS  distance ,mic.latitude,mic.longitude,mic.parentid,mic.compname,mic.mobile,mic.allocated_campaign_flag,mic.activecampaigns, group_concat(distinct ifnull(campaignname,'') ) as msgsentcampaign,  mic.reviews,mic.rating,mic.no_of_rating,mic.hotcategory,mic.area,mic.pincode,mic.city,mic.data_city from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
					".$empcodesql.$datacitycond." group by mic.parentid,mic.data_city HAVING distance < 10 LIMIT ".$limitStart.",".$multiplierFactor."";				
				}else{
					$sql = "select mic.latitude,mic.longitude,mic.parentid,mic.compname,mic.mobile,mic.allocated_campaign_flag,mic.activecampaigns, group_concat(distinct ifnull(campaignname,'') ) as msgsentcampaign,  mic.reviews,mic.rating,mic.no_of_rating,mic.hotcategory,mic.area,mic.pincode,mic.city,mic.data_city from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
					".$empcodesql.$datacitycond." group by mic.parentid,mic.data_city LIMIT ".$limitStart.",".$multiplierFactor."";
				}
				
				//
			}else{
				$limitStart	=	($pageCount*$multiplierFactor - $multiplierFactor);
				$limitEnd	=	$pageCount*$multiplierFactor;
				$sql = "select mic.latitude,mic.longitude,mic.parentid,mic.compname,mic.mobile,mic.allocated_campaign_flag,mic.activecampaigns, group_concat(distinct ifnull(campaignname,'') ) as msgsentcampaign,  mic.reviews,mic.rating,mic.no_of_rating,mic.hotcategory,mic.area,mic.pincode,mic.city,mic.data_city from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
				".$empcodesql.$datacitycond." group by mic.parentid,mic.data_city LIMIT ".$limitStart.",".$multiplierFactor."";
			}
			
		
			

			$res = parent::execQuery($sql,$this->IDC);
			
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $sql;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
			
			if(mysql_num_rows($res)>0)
			{	
				$datacount=mysql_num_rows($res);
				
				while($row=mysql_fetch_assoc($res))
				{
					$allocated_campaign_flag_array= array();
									
					$row['activecampaigns'] = str_replace('Phone Search - Package','Package',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Phone Search - Platinum/Diamond','fixedposition',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Competitors Banner','Banner',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Category Banner','Banner',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Website Creation Fee','Website',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Website Maintenance Fee','Website',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Registration Fee','',$row['activecampaigns']);
					$allocated_campaign_flag= intval($row['allocated_campaign_flag']);
					
					
					foreach($allocation_master_arr as $allocation_master_flag_value=>$allocation_master_campaign_name)
					{
						
						if( ($allocated_campaign_flag&$allocation_master_flag_value) == $allocation_master_flag_value)
						{
							array_push($allocated_campaign_flag_array,$allocation_master_campaign_name);
						}
					}
					$row['allocated_campaign']= implode(',',$allocated_campaign_flag_array);
					unset($row['allocated_campaign_flag']);
					
					if (DEBUG_MODE)
					{
						echo '<br><b> allocated_campaign_flag:</b>'.$allocated_campaign_flag;
						echo '<br><b> allocated_campaign_flag_array:</b>' ; print_r($allocated_campaign_flag_array);
						echo '<br>';
						echo '<br> implode --'.implode(',',$allocated_campaign_flag_array);
						echo '<br>$row allocated_campaign_flag -- '.$row['allocated_campaign_flag'];
											
					}
					
					/*
					if($row['allocated_campaign_flag']&1==1)
					{
						array_push($allocated_campaign_array,'Website')
					}
					if($row['allocated_campaign_flag']&2==2)
					{
						array_push($allocated_campaign_array,'JDRR')
					}
					if($row['allocated_campaign_flag']&4==4)
					{
						array_push($allocated_campaign_array,'Freelist Data')
					}
					if($row['allocated_campaign_flag']&8==8)
					{
						array_push($allocated_campaign_array,'Supercat Data')
					}
					*/
					
					$resultarr[$row['parentid']]= $row;
				}
				
				$returnarr['errorcode']=0;
				$returnarr['msg'] ='No Error';
				$returnarr['datacount'] = $datacount;
			}else
			{
				$returnarr['errorcode']=1;
				$returnarr['msg'] ='No data found';
			}			
		}else{
			$sql = "select mic.latitude,mic.longitude,mic.parentid,mic.compname,mic.mobile,mic.allocated_campaign_flag,mic.activecampaigns, group_concat(distinct ifnull(campaignname,'') ) as msgsentcampaign,  mic.reviews,mic.rating,mic.no_of_rating,mic.hotcategory,mic.area,mic.pincode,mic.city,mic.data_city from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
			".$empcodesql.$datacitycond." group by mic.parentid,mic.data_city ";

			$res = parent::execQuery($sql,$this->IDC);
			
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $sql;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
			
			if(mysql_num_rows($res)>0)
			{	
				$datacount=mysql_num_rows($res);
				
				while($row=mysql_fetch_assoc($res))
				{
					$allocated_campaign_flag_array= array();
									
					$row['activecampaigns'] = str_replace('Phone Search - Package','Package',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Phone Search - Platinum/Diamond','fixedposition',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Competitors Banner','Banner',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Category Banner','Banner',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Website Creation Fee','Website',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Website Maintenance Fee','Website',$row['activecampaigns']);
					$row['activecampaigns'] = str_replace('Registration Fee','',$row['activecampaigns']);
					$allocated_campaign_flag= intval($row['allocated_campaign_flag']);
					
					
					foreach($allocation_master_arr as $allocation_master_flag_value=>$allocation_master_campaign_name)
					{
						
						if( ($allocated_campaign_flag&$allocation_master_flag_value) == $allocation_master_flag_value)
						{
							array_push($allocated_campaign_flag_array,$allocation_master_campaign_name);
						}
					}
					$row['allocated_campaign']= implode(',',$allocated_campaign_flag_array);
					unset($row['allocated_campaign_flag']);
					
					if (DEBUG_MODE)
					{
						echo '<br><b> allocated_campaign_flag:</b>'.$allocated_campaign_flag;
						echo '<br><b> allocated_campaign_flag_array:</b>' ; print_r($allocated_campaign_flag_array);
						echo '<br>';
						echo '<br> implode --'.implode(',',$allocated_campaign_flag_array);
						echo '<br>$row allocated_campaign_flag -- '.$row['allocated_campaign_flag'];
											
					}
					
					/*
					if($row['allocated_campaign_flag']&1==1)
					{
						array_push($allocated_campaign_array,'Website')
					}
					if($row['allocated_campaign_flag']&2==2)
					{
						array_push($allocated_campaign_array,'JDRR')
					}
					if($row['allocated_campaign_flag']&4==4)
					{
						array_push($allocated_campaign_array,'Freelist Data')
					}
					if($row['allocated_campaign_flag']&8==8)
					{
						array_push($allocated_campaign_array,'Supercat Data')
					}
					*/
					
					$resultarr[$row['parentid']]= $row;
				}
				
				$returnarr['errorcode']=0;
				$returnarr['msg'] ='No Error';
				$returnarr['datacount'] = $datacount;
			}else
			{
				$returnarr['errorcode']=1;
				$returnarr['msg'] ='No data found';
			}
		}
		
		
	
		
		$returnarr['data']=$resultarr;
		return $returnarr;
			
	}
	
	function autoSuggestDetails(){		
		
		if(!isset($this->params['data_city']) || $this->params['data_city']==''){
			$message = "Please pass data_city.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		if(!isset($this->params['empcode']) || $this->params['empcode']==''){
			$message = "Please pass empcode.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		
		if(!isset($this->params['compname']) || $this->params['compname']==''){
			$message = "Please pass compname.";
			echo json_encode($this->send_die_message($message));
			die();
		}
	
		$data_city = trim($this->params['data_city']);
		$empcode   = trim($this->params['empcode']);
		$compname  = trim($this->params['compname']);
		
		$maincityarry= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');		
		if( in_array($this->params['data_city'],$maincityarry)){
			$datacitycond =" and mic.data_city='".$data_city."' ";
		}else{
			$datacitycond =" and mic.data_city not in ('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata') ";
		}		
		
		$compnameCon = ""; $limitCondition = "";
		if($compname!='' && $empcode!=''){					
			
			$compnameCon = " AND mic.compname LIKE '%".$compname."%' ";
			$empcodesql = " WHERE (mic.empcode='".$empcode."' or mic.jda_code='".$empcode."')";
			$limitCondition = " LIMIT 0,10";
			
			
			$getData  = "select mic.latitude,mic.longitude,mic.parentid,mic.compname,mic.mobile,mic.allocated_campaign_flag,mic.activecampaigns, group_concat(distinct ifnull(campaignname,'') ) as msgsentcampaign,  mic.reviews,mic.rating,mic.no_of_rating,mic.hotcategory,mic.area,mic.pincode,mic.city,mic.data_city from marketingIntCampaign mic left join marketingIntCampaign_messagesending mism on (mic.parentid=mism.parentid and  mic.data_city=mism.data_city) 
			".$empcodesql.$datacitycond.$compnameCon. " group by mic.parentid,mic.data_city ".$limitCondition." ";
			
			$resData = parent::execQuery($getData,$this->IDC);		
			if (DEBUG_MODE)
			{
				echo '<br><b> Query:</b>' . $getData;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}
			
			if(mysql_num_rows($resData)>0){	
				$index=0;
				while($row=mysql_fetch_assoc($resData)){
					$resultarr[$index++]= $row;
				}
				$returnarr['errorcode']=0;
				$returnarr['msg'] ='Success';
			}else{
				$returnarr['errorcode']=1;
				$returnarr['msg'] ='No data found';
			}
			$returnarr['data']=$resultarr;
			return $returnarr;			
		}else{
			if(!isset($this->params['compname']) || $this->params['compname']==''){
				$message = "compname & empcode missing.";
				echo json_encode($this->send_die_message($message));
				die();
			}
		}
	}
	
	private function send_die_message($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
	
	function getsentmsgdetails()
	{
	
		$empcode = $this->params['empcode'];
		$parentid = $this->params['parentid'];
		
		$wherecond = "";
		
		if($empcode==null)
		{
			$returnarr['errorcode']=1;
			$returnarr['msg'] ='empcode is blank';	
			return $returnarr;
		}else
		{
			$wherecond =" where empcode='".$empcode."' ";
		}
		
		
		$resultarr= array();
		
		$sql = "select parentid, compname, campaignname, sendtime,city,data_city from marketingIntCampaign_messagesending ";
		$res = parent::execQuery($sql,$this->IDC);
		
		if (DEBUG_MODE)
		{
			echo '<br><b> Query:</b>' . $sql;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		
		if(mysql_num_rows($res)>0)
		{	
			$index=1;
			while($row=mysql_fetch_assoc($res))
			{
				$resultarr[$index++]= $row;
			}
			
			$returnarr['errorcode']=0;
			$returnarr['msg'] ='No Error';
		}else
		{
			$returnarr['errorcode']=1;
			$returnarr['msg'] ='No data found';
		}
	
		
		$returnarr['data']=$resultarr;
		return $returnarr;
			
	}
	
	
	function sendmessage()
	{
		$returnarr= array();
		
		$parentid = $this->params['parentid'];
		$data_city= $this->params['data_city'];
		$campaignname  = $this->params['campaignname'];
		$empcode  = $this->params['empcode'];
		$mobile  = $this->params['mobile'];
		
		if($parentid==null ||$empcode==null || $campaignname == null  || $mobile ==null )
		{
			$returnarr['errorcode']=1;
			$returnarr['msg'] ='parentid,empcode,campaignname or mobile not passed';	
			return $returnarr;
		}
		
		$sql = "select parentid,compname,activecampaigns,reviews,rating,no_of_rating,hotcategory,area,pincode,city,data_city from marketingIntCampaign where parentid='".$parentid."' and data_city='".$data_city."' ";
		
		$res = parent::execQuery($sql,$this->IDC);
		$row=mysql_fetch_assoc($res);
		
		$sql= "insert into marketingIntCampaign_messagesending set 
				parentid= '".$parentid."',
				compname = '".addslashes(stripslashes($row['compname']))."',
				campaignname = '".addslashes(stripslashes($this->params['campaignname']))."',
				mobile  = '".$mobile."',
				tmecode  = '".$empcode."',
				empcode  = '".$empcode."',
				area	 = '".addslashes(stripslashes($row['area']))."',
				pincode = '".$row['pincode']."',
				city	 = '".addslashes(stripslashes($row['city']))."',
				data_city = '".addslashes(stripslashes($row['data_city']))."',
				sendtime = '".date('Y-m-d H:i:s')."'";
		parent::execQuery($sql,$this->IDC);
		
		if (DEBUG_MODE)
		{
			echo '<br><b> Query:</b>' . $sql;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
	
		$returnarr['errorcode']=0;
		$returnarr['msg'] ='successful';	
		return $returnarr;
		
	}
		
}
	
	




?>
