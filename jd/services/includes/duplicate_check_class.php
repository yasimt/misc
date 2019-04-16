<?php
class duplicate_check_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_jds   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $conn_fnc    	= null;
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
 		/*if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }*/
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
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_iro_slave	= $db[$conn_city]['iro']['slave'];
		$this->conn_fnc    		= $db[$conn_city]['fin']['master'];		
	}	
	function duplicate_check() 
	{
		global $params;
		$stm = microtime(true);
		
		$datacity=trim(urldecode($this->data_city));
		 
		$main_city_array = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad');   
		if(!in_array(strtolower($datacity),$main_city_array))
		{
			$strcityIP	=	REMOTE_CITIES_DULICATE_IP;
			$rflag = '1';	
		}	
		else
		{
			$strcityIP=constant(strtoupper($datacity)."_DULICATE_IP");
			$rflag = '0';	
		}	
		
		
		if($params['trace']==1)
		{
			echo "<pre>";
			echo "\n---------------------------------------------------------------------------------\n";
			echo "parameters";
			echo "\n---------------------------------------------------------------------------------\n";
			print_r($params);
			echo "\n---------------------------------------------------------------------------------\n";
		
			echo "\nExecution Started @".$stm." sec\n";
		}	
		
		if($params['n'] == '1')
		{
			$ret_arr  					= $this->get_duplicate_data_sp($strcityIP);		
			$ret_arr['params']  		= $this->get_api_param($strcityIP);
		}
		else 
		{
			$ret_arr['company_search']  = $this->getData($strcityIP);
			$ret_arr['phone_search']    = $this->getDataSearch();
		}
		
		if($params['trace']==1)
		{
			echo "\n---------------------------------------------------------------------------------\n";
			echo "Total Execution Time: ".round(((microtime(true)-$stm)/60*60),3)." sec";
			echo "\n---------------------------------------------------------------------------------\n";
			echo "\n---------------------------------------------------------------------------------\n";
			echo "Result=>";
			echo "\n---------------------------------------------------------------------------------\n\n";
		}
		
		echo json_encode($ret_arr);
		
	}
	function get_duplicate_data_sp($strcityIP)
	{		
		global $params,$dup_array_sp;
		$dup_array_sp = array();
		$st = 0;
		$lm = 999;
		/*
		$csi		=	$params['companyname'];
		$csi1 		=	$this->braces_content_removal($this->sanitize($csi,1));	
		$csi2       =	$this->getIgnored($csi1);
		$compname_search_ignore =	$this->getSingular($csi2);
		$params['companyname'] = $compname_search_ignore;*/		
		
		if(trim($params['landmark']))
			$addrarray[]=trim($params['landmark']);
		if(trim($params['street']))
			$addrarray[]=trim($params['street']);
		if(trim($params['area']))
			$addrarray[]=trim($params['area']);		
	
		if(count($addrarray) > 0)
			$fulladdress = implode(',',$addrarray);
		else
			$fulladdress ="";
		if(count($addrarray) > 0)
			$params['address'] = trim($params['landmark'].",".$params['building'].",".$params['street'],",");	
		
		$phone_search_arr['perfect_match'] = $this->get_perfect_match_data($strcityIP);
		$phone_search_arr['probable_match'] = $this->get_probable_match_data($strcityIP);
	 
		if($params['trace']==1) {
			echo "\n----------------------------------------------------------------------------\n";
			echo "Perfect & Probable Match";
			echo "\n----------------------------------------------------------------------------\n";
			print_r($phone_search_arr);				 
			echo "\n----------------------------------------------------------------------------\n";
			echo "Match details";
			echo "\n----------------------------------------------------------------------------\n";
		}		
		return $phone_search_arr;	
		
	}
	function get_perfect_match_data($strcityIP)
	{
		global $params,$dup_array_sp;
		
		$perfect_match_data = array();
		$compdata = array();
		if(isset($params['minibform']) && $params['minibform']==1)
		{
		
		}
		else
		{	
			$match_arr = Array(
				'1'=>Array('companyname'=>'85','address'=>'60','phone'=>'1','pincode'=>'0','bracket'=>'No Bracket','distance'=>'0','tagging'=>'Matched','rule'=>'1'),
				
				'2'=>Array('companyname'=>'85','address'=>'65','phone'=>'0','pincode'=>'1','bracket'=>'No Bracket','distance'=>'0','tagging'=>'Matched','rule'=>'2'),
				
				'3'=>Array('companyname'=>'75','address'=>'40','phone'=>'1','pincode'=>'1','bracket'=>'No Bracket','distance'=>'0','tagging'=>'Matched','rule'=>'3') 
			);
			foreach($match_arr AS $key=>$match_logic)
			{
				if(($match_logic['companyname'] > 0 && empty($params['companyname'])) || ($match_logic['address'] > 0 && empty($params['address'])) || ($match_logic['phone'] > 0 && empty($params['phone'])) || ($match_logic['pincode'] > 0 && empty($params['pincode'])))
				{
					
				}
				else
				{
					$cmd = '';$cmp = '';$adr = '';$pin = '';$phn = '';
					if($match_logic['companyname'] > 0)
					{
						$cmp = $this->quoram_new($params['companyname'],$match_logic['companyname'],'companyname');
						$cmp = '@c '.$cmp;
					}
					if($match_logic['address'] > 0)
					{
						$adr = ' @ads '.$this->quoram_new(trim(str_replace('  ',' ',$params['address'])),$match_logic['address'],'address');
					}
					if($match_logic['phone'] == 1)
					{
						$phn = ' @ps '.str_replace(',','|',$params['phone']); 
					}		
					if($match_logic['pincode'] == 1)
					{
						$pin = ' @pin "'.trim($params['pincode']).'"';
					}
					$query[] = $cmd = $cmp.$adr.$pin.$phn;					
					 
					$spx = new SphinxClient();
					$spx->SetServer($strcityIP,3382);
					$spx->SetMatchMode(SPH_MATCH_EXTENDED2);
					$spx->SetRankingMode(SPH_RANK_EXPR,'SUM(word_count)');
					$spx->SetSortMode(SPH_SORT_EXTENDED,'@relevance DESC');
					$spx->SetGroupBy("jid", SPH_GROUPBY_ATTR, '@weight DESC, pop DESC');
					$spx->SetRankingMode(SPH_RANK_EXPR, "doc_word_count");
					$spx->SetLimits(0,999);
					
					//$spx->AddQuery($cmd,'comp_search,rt_comp_search');
					$spx->AddQuery($cmd,'comp_search,comp_search_instant');
					$res='';
					$res = $spx->RunQueries();
					$xx=0;
					if($params['trace'] == 1)	
					{
						echo "<hr>";
						echo "<br>".$cmd;
						//print_r($res);
						echo "<hr>"; 
					}	 
					
					$parentid_temp_array = Array();
					for($i=0;$i<count($res);$i++)
					{
						if(count($res[$i]['matches'])>0)
						{		
							foreach($res[$i]['matches'] as $idx=>$val)
							{							
								if(!in_array($res[$i]['matches'][$idx]['attrs']['jid'],$dup_array_sp))
								{
									$parentid_temp_array[]=$res[$i]['matches'][$idx]['attrs']['jid'];
									$dup_array_sp[] = $res[$i]['matches'][$idx]['attrs']['jid']; 
								}	
								
							}
						}	
					}
					$parentid_temp_array = array_unique($parentid_temp_array);
					$compdata = $this->get_customise_data_sp($parentid_temp_array,$match_logic);
					$perfect_match_data = array_merge($perfect_match_data,$compdata);
				}			
			}
		}	
		return $perfect_match_data;	
	}
	function get_probable_match_data($strcityIP)
	{
		global $params,$dup_array_sp;
		if(isset($params['minibform']) && $params['minibform']==1)
		{
			$match_arr = Array( 
						'15'=>Array('companyname'=>'0','address'=>'0','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'number match','rule'=>'15'),
						
						'4'=>Array('companyname'=>'80','address'=>'0','phone'=>'1','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 1','rule'=>'4'),
						
						'7'=>Array('companyname'=>'80','address'=>'0','phone'=>'0','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 7','rule'=>'7'),
						
						'9'=>Array('companyname'=>'80','address'=>'0','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'probable Match 4','rule'=>'9'),
						
						'11'=>Array('companyname'=>'0','address'=>'0','phone'=>'1','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 3','rule'=>'11')						
						 
					);	
			
		}
		else
		{	
			$match_arr = Array( 
						'15'=>Array('companyname'=>'0','address'=>'0','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'number match','rule'=>'15'),
						
						'4'=>Array('companyname'=>'80','address'=>'0','phone'=>'1','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 1','rule'=>'4'),
						
						'5'=>Array('companyname'=>'40','address'=>'40','phone'=>'1','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 2','rule'=>'5'),
						
						'6'=>Array('companyname'=>'50','address'=>'50','phone'=>'0','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'probable Match 5','rule'=>'6'),
						
						'7'=>Array('companyname'=>'80','address'=>'0','phone'=>'0','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 7','rule'=>'7'),
						
						'8'=>Array('companyname'=>'20','address'=>'20','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'probable Match 10','rule'=>'8'),
						
						'9'=>Array('companyname'=>'80','address'=>'0','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'probable Match 4','rule'=>'9'),
						
						'10'=>Array('companyname'=>'0','address'=>'50','phone'=>'1','pincode'=>'0','bracket'=>'','distance'=>'0','tagging'=>'probable Match 6','rule'=>'10'),
						
						'11'=>Array('companyname'=>'0','address'=>'0','phone'=>'1','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 3','rule'=>'11'),
						
						'12'=>Array('companyname'=>'50','address'=>'40','phone'=>'0','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'probable Match 8','rule'=>'12'),
						
						'13'=>Array('companyname'=>'75','address'=>'0','phone'=>'0','pincode'=>'0','bracket'=>'','distance'=>'10','tagging'=>'probable Match 11','rule'=>'13'),
						
						'14'=>Array('companyname'=>'50','address'=>'30','phone'=>'0','pincode'=>'1','bracket'=>'','distance'=>'0','tagging'=>'50_30 Probable','rule'=>'14')					
						 
					);	
		}			
		$dup_array = array();
		$dup_array_new = array();
		$probale_match_data = array();
		$compdata = array();
		
		foreach($match_arr AS $key=>$match_logic)
		{
			if(($match_logic['companyname'] > 0 && empty($params['companyname'])) || ($match_logic['address'] > 0 && empty($params['address'])) || ($match_logic['phone'] > 0 && empty($params['phone'])) || ($match_logic['pincode'] > 0 && empty($params['pincode'])))
			{
				
			}
			else
			{
				$cmd = '';$cmp = '';$adr = '';$pin = '';$phn = '';				
				if($match_logic['companyname'] > 0)
				{
					$cmp = $this->quoram_new($params['companyname'],$match_logic['companyname'],'companyname');
					$cmp = '@c '.$cmp;
				}
				if($match_logic['address'] > 0)
				{
					$adr = ' @ads '.$this->quoram_new(trim(str_replace('  ',' ',$params['address'])),$match_logic['address'],'address');
				}
				if($match_logic['phone'] == 1)
				{
					$phn = ' @ps '.str_replace(',','|',$params['phone']); 
				}		
				if($match_logic['pincode'] == 1)
				{
					$pin = ' @pin "'.trim($params['pincode']).'"';
				}
				
				$query[] = $cmd = $cmp.$adr.$pin.$phn;			
				 
				$spx = new SphinxClient();
				$spx->SetServer($strcityIP,3382);
				$spx->SetMatchMode(SPH_MATCH_EXTENDED2);
				$spx->SetRankingMode(SPH_RANK_EXPR,'SUM(word_count)');
				$spx->SetSortMode(SPH_SORT_EXTENDED,'@relevance DESC');
				$spx->SetGroupBy("jid", SPH_GROUPBY_ATTR, '@weight DESC, pop DESC');
				$spx->SetRankingMode(SPH_RANK_EXPR, "doc_word_count");
				$spx->SetLimits(0,999);
				
				//$spx->AddQuery($cmd,'comp_search,rt_comp_search');
				$spx->AddQuery($cmd,'comp_search,comp_search_instant');
				$res='';
				$res = $spx->RunQueries();
				$xx=0;
				if($params['trace'] == 1)	
				{
					echo "<hr>".$match_logic['rule'];
					echo "===>  ".$cmd;
					//echo "===>  ".$res;
					echo "<hr>";
				}	 
				$parentid_temp_array = Array();
				for($i=0;$i<count($res);$i++)
				{
					if(count($res[$i]['matches'])>0)
					{		
						foreach($res[$i]['matches'] as $idx=>$val)
						{		
							if($match_logic['rule'] =='13' && !empty($params['parentid']))
							{
								$dataparentid = $res[$i]['matches'][$idx]['attrs']['jid'];
								$compdata_inp = $this->get_compdata($params['parentid']);
								$compdata_res = $this->get_compdata($dataparentid);
									
								if($compdata_res[$dataparentid]['latitude'] > 0 && $compdata_res[$dataparentid]['longitude'] > 0 && $compdata_inp[$params['parentid']]['latitude'] > 0 && $compdata_inp[$params['parentid']]['longitude'] > 0)
								{
									$sql_dist = "SELECT ACOS(COS(RADIANS(90-".$compdata_inp[$params['parentid']]['latitude'].")) *COS(RADIANS(90-".$compdata_res[$dataparentid]['latitude'].")) +SIN(RADIANS(90-".$compdata_inp[$params['parentid']]['latitude'].")) *SIN(RADIANS(90-".$compdata_res[$dataparentid]['latitude'].")) *COS(RADIANS(".$compdata_inp[$params['parentid']]['longitude']."-".$compdata_res[$dataparentid]['longitude']."))) *6371 as distance";	
									$res_dist = parent::execQuery($sql_dist, $this->conn_iro);			
									$row_dist = mysql_fetch_assoc($res_dist);
									$distance = round($row_dist['distance'],2);
									if($distance <= 10 && $distance >= 0)
									{
										if(!in_array($res[$i]['matches'][$idx]['attrs']['jid'],$dup_array_sp))
										{
											$parentid_temp_array[]=$res[$i]['matches'][$idx]['attrs']['jid'];
											$dup_array_sp[] = $res[$i]['matches'][$idx]['attrs']['jid']; 
										}		
									}	
								}
							}
							else
							{
								if(!in_array($res[$i]['matches'][$idx]['attrs']['jid'],$dup_array_sp))
								{
									$parentid_temp_array[]=$res[$i]['matches'][$idx]['attrs']['jid'];
									$dup_array_sp[] = $res[$i]['matches'][$idx]['attrs']['jid']; 
								}	
							}	
						}
					}	
				}
				$parentid_temp_array = array_unique($parentid_temp_array);
				$compdata = $this->get_customise_data_sp($parentid_temp_array,$match_logic);
				$probale_match_data = array_merge($probale_match_data,$compdata);
			}			
		}
		return $probale_match_data;	
	}
	function get_customise_data_sp($dup_array,$match_logic)
	{
		$parentid_arr = array();
		$ret_arr = array();
		$parentid_list = implode("','",$dup_array);
		if(count($dup_array)>0)
		{
			//echo "\n".$sql = "SELECT a.parentid,a.companyname,a.area,a.pincode,a.city,a.building_name as building,a.landmark,b.phone_search,b.display_flag,b.paid as paidstatus,a.full_address as address,b.data_city,a.latitude,a.longitude FROM db_iro.tbl_companymaster_generalinfo a JOIN db_iro.tbl_companymaster_search b on a.parentid=b.parentid WHERE a.parentid IN ('".$parentid_list."')";
			$sql_search = "SELECT parentid,paid as paidstatus,phone_search,display_flag  FROM db_iro.tbl_companymaster_search WHERE parentid  IN ('".$parentid_list."')";
			$res_search = parent::execQuery($sql_search, $this->conn_iro);			
			$search_arr =array();
			if($res_search && mysql_num_rows($res_search)>0)
			{
				while($row_search = mysql_fetch_assoc($res_search))
				{
					$search_arr[$row_search['parentid']]['paidstatus'] = $row_search['paidstatus'];
					$search_arr[$row_search['parentid']]['phone_search'] = trim($row_search['phone_search'],",");
					$search_arr[$row_search['parentid']]['display_flag'] = $row_search['display_flag'];
				}
			}	
			$sql = "SELECT parentid,companyname,area,pincode,city,building_name as building,landmark, concat(mobile,\",\",landline) as phone_search,full_address as address,data_city,latitude,longitude FROM db_iro.tbl_companymaster_generalinfo WHERE parentid  IN ('".$parentid_list."')";			
			$res = parent::execQuery($sql, $this->conn_iro);			
			if($res && mysql_num_rows($res)>0){
				while($row = mysql_fetch_assoc($res)){
					$eligible_arr[] = $row['parentid'];
					
					$parentid_arr[$row['parentid']]['compname'] 	=	$this->ascii_char($row['companyname']);
					$parentid_arr[$row['parentid']]['data_city']	=	$this->ascii_char($row['data_city']);
					$parentid_arr[$row['parentid']]['area']			=	$this->ascii_char($row['area']);
					$parentid_arr[$row['parentid']]['pincode']	 	=	$this->ascii_char($row['pincode']);
					$parentid_arr[$row['parentid']]['city']	 		=	$this->ascii_char($row['city']);
					$parentid_arr[$row['parentid']]['phone_search'] =	$this->ascii_char($row['phone_search']);
					$parentid_arr[$row['parentid']]['phone_search'] =	$this->ascii_char($search_arr[$row['parentid']['phone_search']]);
					$parentid_arr[$row['parentid']]['phone_search'] =	$this->ascii_char(($search_arr[$row['parentid']]['phone_search']) ? $search_arr[$row['parentid']]['phone_search'] : '');
					$parentid_arr[$row['parentid']]['building'] 	=	$this->ascii_char($row['building']);
					$parentid_arr[$row['parentid']]['landmark'] 	=	$this->ascii_char($row['landmark']);
					
					$parentid_arr[$row['parentid']]['display_flag'] =	$this->ascii_char(($search_arr[$row['parentid']]['display_flag']) ? $search_arr[$row['parentid']]['display_flag'] : 0);
					
					$parentid_arr[$row['parentid']]['paidstatus'] 	=	$this->ascii_char(($search_arr[$row['parentid']]['paidstatus']) ? $search_arr[$row['parentid']]['paidstatus'] : '0');
					$parentid_arr[$row['parentid']]['address'] 		=	$this->ascii_char($row['address']);
					$parentid_arr[$row['parentid']]['expired_flag'] = 	'';
					$parentid_arr[$row['parentid']]['expired_on']   =   '';
					$parentid_arr[$row['parentid']]['latitude']  	=   $row['latitude'];
					$parentid_arr[$row['parentid']]['longitude']    =   $row['longitude'];					
					$parentid_arr[$row['parentid']]['rule'] 		=	$match_logic[$row['parentid']]['rule'];
					$parentid_arr[$row['parentid']]['tagging'] 		=	$match_logic[$row['parentid']]['tagging'];
				}
			}
			if(count($parentid_arr)>0)
			{
				//$sql_expired_chk= "SELECT a.*,b.paid_flag,b.expired_on FROM db_iro.tbl_id_generator a LEFT JOIN dbteam_temp.tbl_active_parentid b on a.parentid=b.parentid WHERE  a.parentid in ('".$parentid_list."')";
				$sql_expired_chk= "SELECT parentid,paid_flag,expired_on FROM dbteam_temp.tbl_active_parentid  WHERE  parentid in ('".$parentid_list."')";
				$res_ex_chk = parent::execQuery($sql_expired_chk, $this->conn_iro_slave);			
				if($res_ex_chk && mysql_num_rows($res_ex_chk)>0){
					while($row_ex_chk = mysql_fetch_assoc($res_ex_chk)){	
						if($row_ex_chk['paid_flag'] == 2){
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = $row_ex_chk['paid_flag'];
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = $row_ex_chk['expired_on'];
						}	
						else{ 			
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = '0';
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = '';
						}	
					}
				}
				foreach($dup_array AS $key=>$val){
					if(in_array($val,$eligible_arr))
					{
						$row_arr['parentid']		=	$val; 
						$row_arr['companyname'] 	=	$parentid_arr[$val]['compname']; 
						$row_arr['data_city'] 		=	$parentid_arr[$val]['data_city']; 
						$row_arr['area'] 			=	$parentid_arr[$val]['area'];
						$row_arr['pincode'] 		=	$parentid_arr[$val]['pincode'];						
						$row_arr['city'] 			=	$parentid_arr[$val]['city'];						
						$row_arr['phone'] 			=	$parentid_arr[$val]['phone_search']; 
						$row_arr['building'] 		=	$parentid_arr[$val]['building']; 
						$row_arr['landmark'] 		=	$parentid_arr[$val]['landmark']; 
						$row_arr['display_flag'] 	=	$parentid_arr[$val]['display_flag']; 
						$row_arr['paidstatus'] 		=	$parentid_arr[$val]['paidstatus']; 
						$row_arr['address'] 		=	$parentid_arr[$val]['address']; 
						$row_arr['expired_flag'] 	=	($parentid_arr[$val]['expired_flag'] ? $parentid_arr[$val]['expired_flag'] : 0); 
						$row_arr['expired_on'] 		=	$parentid_arr[$val]['expired_on']; 
						$row_arr['tagging_type'] 	=	$match_logic['tagging'];
						$row_arr['rule'] 			=	$match_logic['rule'];						 
						$row_arr['latitude'] 		=	$parentid_arr[$val]['latitude'];
						$row_arr['longitude'] 		=	$parentid_arr[$val]['longitude'];
						$ret_arr[] = $row_arr;			
					} 					
				} 
			}	
		}
		return $ret_arr;	
	}
	function get_duplicate_data($strcityIP)
	{
		GLOBAL $params,$con;
		$parentid_arr = Array();
		$phone_search_arr = Array();
		
		$params['companyname'] = $params['companyname'];
		
		if(trim($params['building']))
			$addrarray[]=trim($params['building']);		
		if(trim($params['landmark']))
			$addrarray[]=trim($params['landmark']);
		if(trim($params['street']))
			$addrarray[]=trim($params['street']);
		if(trim($params['area']))
			$addrarray[]=trim($params['area']);
		
	
		if(count($addrarray) > 0)
			$fulladdress = implode(',',$addrarray);
		else
			$fulladdress ="";
		if(!empty($params['pincode']))
		{
			$fulladdress .=	"-".$params['pincode'];
		} 	
		//$fulladdress .=	",".$params['data_city'];
		$params['address']		=	trim($fulladdress,",");	
		if($params['trace']==1)
		{
			echo "\nSphinx Server IP inside getData fn. @".$strcityIP." sec\n";
			echo "<hr>"; 
			echo "".$params['address']; 
			echo "<hr>"; 
		}
		 
		$data_comp 		= $this->get_sphinx_data($strcityIP);
		$dup_array_perfect = array();
		$dup_array_probable = array();
		$perfect_match = array();
		$probable_match = array();
		foreach($data_comp as $key=>$data)
		{
			$cmp_match = $data['match_percentage_comp'];
			$add_match = $data['match_percentage_address'];
			$phn_match = $data['match_percentage_phone'];
			$pin_match = $data['match_percentage_pincode'];
			$insert_flag=1;
			$distance=0;
			if(isset($params['minibform']) && $params['minibform']==1)
			{
				if($cmp_match >= 80 && $phn_match == 1 && $pin_match == 1)
				{
					$type = 'probable';
					//4 - probable Match 1
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '4';
					$dup_array_probable[$key]['tagging'] = 'probable Match 1';					    	
				}
				else if($cmp_match>=80  && $pin_match==1)  
				{
					$type = 'probable';
					//7 - probable Match 7			
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '7';
					$dup_array_probable[$key]['tagging'] = 'probable Match 7';	
				}		
				else if($cmp_match>=80  && $phn_match==1)  
				{
					$type = 'probable';
					//9 - probable Match 4	
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '9';
					$dup_array_probable[$key]['tagging'] = 'probable Match 4';		
				}	
				else if($phn_match==1 && $pin_match==1)  
				{
					$type = 'probable';
					//11 - probable Match 3
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '11';
					$dup_array_probable[$key]['tagging'] = 'probable Match 3';		
				}
				else if($phn_match == 1) 
				{
					$type = 'probable';
					//15 - phone match
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '15';
					$dup_array_probable[$key]['tagging'] = 'Number Match';	
				}
				else
				{
					$insert_flag=0;
				}				
			}
			else
			{
				if($cmp_match >= 75 && $add_match >= 60 && $phn_match == 1)
				{	
					$type = 'perfect';
					$perfect_match[] = $key;
					$dup_array_perfect[$key]['parentid'] = $key;
					$dup_array_perfect[$key]['rule'] = '1';
					$dup_array_perfect[$key]['tagging'] = 'matched';
				}	
				else if($cmp_match >= 85 && $add_match >= 65 && $pin_match == 1) 	
				{
					$type = 'perfect';
					$perfect_match[] = $key;
					$dup_array_perfect[$key]['parentid'] = $key;
					$dup_array_perfect[$key]['rule'] = '2';
					$dup_array_perfect[$key]['tagging'] = 'matched';
				}	
				else if($cmp_match >= 75 && $add_match >= 40 && $phn_match == 1 && $pin_match == 1)  
				{
					$type = 'perfect';
					$perfect_match[] = $key;
					$dup_array_perfect[$key]['parentid'] = $key;
					$dup_array_perfect[$key]['rule'] = '3';
					$dup_array_perfect[$key]['tagging'] = 'matched';				
				}  	
				else if($cmp_match >= 80 && $phn_match == 1 && $pin_match == 1)
				{
					$type = 'probable';
					//4 - probable Match 1
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '4';
					$dup_array_probable[$key]['tagging'] = 'probable Match 1';					    	
				}	
				else if($cmp_match>=40 && $add_match>=40 && $phn_match==1 && $pin_match==1)  
				{
					$type = 'probable';
					//5 - probable Match 2	
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '5';
					$dup_array_probable[$key]['tagging'] = 'probable Match 2';				
				}	
				else if($cmp_match>=50 && $add_match>=50 )  
				{
					$type = 'probable';
					//6 - probable Match 5
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '6';
					$dup_array_probable[$key]['tagging'] = 'probable Match 5';
				}	
				else if($cmp_match>=80  && $pin_match==1)  
				{
					$type = 'probable';
					//7 - probable Match 7			
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '7';
					$dup_array_probable[$key]['tagging'] = 'probable Match 7';	
				}	
				else if($cmp_match>=20  && $add_match>=20 && $phn_match==1)  
				{
					$type = 'probable';
					//8 - probable Match 10
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '8';
					$dup_array_probable[$key]['tagging'] = 'probable Match 10';		
				}	
				else if($cmp_match>=80  && $phn_match==1)  
				{
					$type = 'probable';
					//9 - probable Match 4	
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '9';
					$dup_array_probable[$key]['tagging'] = 'probable Match 4';		
				}				
				else if($add_match>=50  && $phn_match==1) 
				{	
					$type = 'probable';
					//10 - probable Match 6
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '10';
					$dup_array_probable[$key]['tagging'] = 'probable Match 6';		
				}
				else if($phn_match==1 && $pin_match==1)  
				{
					$type = 'probable';
					//11 - probable Match 3
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '11';
					$dup_array_probable[$key]['tagging'] = 'probable Match 3';		
				}				
				else if($cmp_match>=50 && $add_match>=40 && $pin_match==1)  
				{
					$type = 'probable';
					//12 - probable Match 8	
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '12';
					$dup_array_probable[$key]['tagging'] = 'probable Match 8';		
				}				
				else if($cmp_match>=75 && $distance<=10 )  
				{
				
					//ACOS(COS(RADIANS(90-Lat1)) *COS(RADIANS(90-Lat2)) +SIN(RADIANS(90-Lat1)) *SIN(RADIANS(90-Lat2)) *COS(RADIANS(Long1-Long2))) *6371
					if(!empty($params['parentid']))
					{
						$compdata_inp = $this->get_compdata($params['parentid']);
						$compdata_res = $this->get_compdata($data['parentid']);
						
						if($compdata_res[$data['parentid']]['latitude'] > 0 && $compdata_res[$data['parentid']]['longitude'] > 0 && $compdata_inp[$params['parentid']]['latitude'] > 0 && $compdata_inp[$params['parentid']]['longitude'] > 0)
						{
							$sql_dist = "SELECT ACOS(COS(RADIANS(90-".$compdata_inp[$params['parentid']]['latitude'].")) *COS(RADIANS(90-".$compdata_res[$data['parentid']]['latitude'].")) +SIN(RADIANS(90-".$compdata_inp[$params['parentid']]['latitude'].")) *SIN(RADIANS(90-".$compdata_res[$data['parentid']]['latitude'].")) *COS(RADIANS(".$compdata_inp[$params['parentid']]['longitude']."-".$compdata_res[$data['parentid']]['longitude']."))) *6371 as distance";	
							$res_dist = parent::execQuery($sql_dist, $this->conn_iro);			
							$row_dist = mysql_fetch_assoc($res_dist);
							$distance = round($row_dist['distance'],2);
							if($distance <= 10 && $distance >= 0 && $distance <= 10)
							{
								$type = 'probable';
								//13 - probable Match 11		
								$probable_match[] = $key;
								$dup_array_probable[$key]['parentid'] = $key;
								$dup_array_probable[$key]['rule'] = '13 - '.$distance;
								$dup_array_probable[$key]['tagging'] = 'probable Match 11';	
							}	
						}
					}
				} 
				else if($cmp_match>50 && $add_match>30) 
				{	
					$type = 'probable';
					//14 - 50_30 Probable
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '14';
					$dup_array_probable[$key]['tagging'] = '50_30 Probable';		
				} 
				else if($phn_match == 1) 
				{
					$type = 'probable';
					//15 - phone match
					$probable_match[] = $key;
					$dup_array_probable[$key]['parentid'] = $key;
					$dup_array_probable[$key]['rule'] = '15';
					$dup_array_probable[$key]['tagging'] = 'Number Match';	
				}
				else
				{
					$insert_flag=0;
				}
			}
			if($insert_flag==1)
			{
				if($type == 'perfect')
				{	
					$dup_array_perfect[$key]['match_percentage_comp'] = $data['match_percentage_comp'];
					$dup_array_perfect[$key]['match_percentage_address'] = $data['match_percentage_address'];
					$dup_array_perfect[$key]['match_percentage_phone'] = $data['match_percentage_phone'];
					$dup_array_perfect[$key]['match_percentage_pincode'] = $data['match_percentage_pincode'];
				}
				if($type == 'probable')
				{	
					$dup_array_probable[$key]['match_percentage_comp'] = $data['match_percentage_comp'];
					$dup_array_probable[$key]['match_percentage_address'] = $data['match_percentage_address'];
					$dup_array_probable[$key]['match_percentage_phone'] = $data['match_percentage_phone'];
					$dup_array_probable[$key]['match_percentage_pincode'] = $data['match_percentage_pincode'];
				}	
			}
		}
		$phone_search_arr['perfect_match'] = $this->get_customise_data($perfect_match,$dup_array_perfect);
		$phone_search_arr['probable_match'] = $this->get_customise_data($probable_match,$dup_array_probable);
	 
		if($params['trace']==1) {
			echo "\n----------------------------------------------------------------------------\n";
			echo "Perfect & Probable Match";
			echo "\n----------------------------------------------------------------------------\n";
				print_r($phone_search_arr);				 
			echo "\n----------------------------------------------------------------------------\n";
			echo "Match details";
			echo "\n----------------------------------------------------------------------------\n";	
				print_r($data_comp);				 
		}		
		return $phone_search_arr;		
	}
	private function get_sphinx_data($strcityIP)
	{
		global $params,$output_arr;
		$dup_array = array();
	 	if(isset($params['minibform']) && $params['minibform'] == 1)
		{
			$field_array = array('companyname'=>$params['companyname'],'phone'=>$params['phone'],'pincode'=>$params['pincode']);
		}	
	 	else
		{
			$field_array = array('companyname'=>$params['companyname'],'address'=>$params['address'],'phone'=>$params['phone'],'pincode'=>$params['pincode']);
		}	
	 	foreach($field_array AS $field=>$str)
		{	
			$str = trim($str,"-");
			$str = trim($str," ");
			$str = str_replace("/"," ",$str);
			$str = str_replace("-"," ",$str);
			$str = str_replace(","," ",$str);
			$str = str_replace("&"," ",$str);
		
			$arr_data = explode(" ",$str);
			$arr_data = array_filter($arr_data);
			if($params['trace'] == 1)	
			{
				print_r($arr_data);
			}	
			
			if($field == 'companyname')
				$cmd = ' @c '.implode("|",$arr_data).' ';
			else if($field == 'address')
				$cmd = ' @ads '.implode("|",$arr_data).' ';
			else if($field == 'pincode')
				$cmd = ' @pin '.$str.' ';
			else if($field == 'phone')
				$cmd = ' @ps '.str_replace(' ',' | ',$str);
			if($params['trace'] == 1)	
			{
				echo "<hr>";
				echo $cmd;
				echo "<hr>";
			}
			$spx = new SphinxClient();
			$spx->SetServer($strcityIP,3382);
			$spx->SetMatchMode(SPH_MATCH_EXTENDED2);
			$spx->SetRankingMode(SPH_RANK_EXPR,'SUM(word_count)');
			$spx->SetSortMode(SPH_SORT_EXTENDED,'@relevance DESC');
			$spx->SetGroupBy("jid", SPH_GROUPBY_ATTR, '@weight DESC, pop DESC');
			$spx->SetRankingMode(SPH_RANK_EXPR, "doc_word_count");
			$spx->SetLimits(0,999);
			
			//$spx->AddQuery($cmd,'comp_search,rt_comp_search');
			$spx->AddQuery($cmd,'comp_search,comp_search_instant');
			$res='';
			$res = $spx->RunQueries();
			$xx=0;
			if($params['trace'] == 1)	
			{
				echo "<hr>";
				//print_r($res);
				echo "<hr>"; 
			}	
			$parentid_temp_array = Array();
			for($i=0;$i<count($res);$i++)
			{
				if(count($res[$i]['matches'])>0)
				{		
					foreach($res[$i]['matches'] as $idx=>$val)
					{			
						$parentid_temp = $res[$i]['matches'][$idx]['attrs']['jid'];
						$parentid_temp_array[] = $res[$i]['matches'][$idx]['attrs']['jid'];
						$output_arr[$parentid_temp]['parentid']=$parentid_temp;
						$output_arr[$parentid_temp]['companyname']=$res[$i]['matches'][$idx]['attrs']['n'];
						$output_arr[$parentid_temp]['weight_'.$field]=$res[$i]['matches'][$idx]['weight'];						
					}
				}	
			}
			
			$parentid_str = implode("','",$parentid_temp_array);			
			$compdata = $this->get_compdata($parentid_str);
			
			//print_r($compdata);exit;			
			foreach($output_arr AS $key=>$data)
			{	 
				//$compdata = $this->get_compdata($data['parentid']);
				$word_cnt_in = 0;
				$word_cnt_out = 0;
				if($field == 'companyname')
				{	
					$output_arr[$data['parentid']]['match_percentage_comp'] = '0';
					$word_cnt_in = count(array_filter(explode(" ",trim(str_replace("-"," ",$str)))));	
					$word_cnt_out = count(array_filter(explode(" ",trim(str_replace("-"," ",$data['companyname'])))));
										 
					if(substr_count($data[$field], '&')>0)
					{
						$data['weight_'.$field] +=substr_count($data[$field], '&');
						
					}
					if(substr_count($field_array[$field], '&')>0)
					{
						$word_cnt_in +=substr_count($field_array[$field], '&');
						
					} 
					$match_percentage_comp   = ROUND($data['weight_'.$field]*2/($word_cnt_in +$word_cnt_out)*100,2);					
					$output_arr[$data['parentid']]['match_percentage_comp']=  $match_percentage_comp; ;
				}
				else if($field == 'address')	
				{
					$compdata['full_address'] = trim($compdata[$data['parentid']]['full_address'],"-");
					$compdata['full_address'] = trim($compdata[$data['parentid']]['full_address']," ");
					$compdata['full_address'] = str_replace("/"," ",$compdata[$data['parentid']]['full_address']);
					$compdata['full_address'] = str_replace("-"," ",$compdata[$data['parentid']]['full_address']);
					$compdata['full_address'] = str_replace(","," ",$compdata[$data['parentid']]['full_address']);
					$compdata['full_address'] = str_replace("&"," ",$compdata[$data['parentid']]['full_address']);
				
					$output_arr[$data['parentid']]['match_percentage_address']= '0';
					if($str == '' && !empty($compdata[$data['parentid']]['full_address']))
					{
						$output_arr[$data['parentid']]['match_percentage_address']= '0';
					}
					else if(!empty($compdata[$data['parentid']]['full_address']))
					{
						$word_cnt_in = count(array_filter(explode(" ",trim(str_replace("-"," ",$str)))));
						$word_cnt_out = count(array_filter(explode(" ",trim(str_replace("-"," ",$compdata[$data['parentid']]['full_address'])))));
						
						if(substr_count($data[$field], '&')>0)
						{
							$data['weight_'.$field] +=substr_count($data[$field], '&');
							
						}
						if(substr_count($field_array[$field], '&')>0)
						{
							$word_cnt_in +=substr_count($field_array[$field], '&');
							
						}
						
						$match_percentage_add   = ROUND($data['weight_'.$field]*2/($word_cnt_in +$word_cnt_out)*100,2);
						
						$output_arr[$data['parentid']]['match_percentage_address']=  $match_percentage_add;			 
						
					}
				}
				else if($field == 'pincode')
				{	
					$output_arr[$data['parentid']]['match_percentage_pincode'] = 0;
					if(trim($compdata[$data['parentid']]['pincode']) == trim($str) && trim($str)!='' && trim($compdata[$data['parentid']]['pincode'])!='')
					{
						$output_arr[$data['parentid']]['match_percentage_pincode']=   '1';
					}
				}	
				else if($field == 'phone')	
				{
					$output_arr[$data['parentid']]['match_percentage_phone'] = '0'; 					
					$uniq_arr = Array();
					$contact_arr = explode(",",$compdata[$data['parentid']]['phone_search']);
					
					$str_arr = explode(" ",$str);
					$uniq_arr = array_filter(array_intersect($str_arr,$contact_arr));
					if(count($uniq_arr)>0)
					{
						$output_arr[$data['parentid']]['match_percentage_phone'] = '1';
					}				
				}
			} 
		}
		return $output_arr;
	}
	function get_customise_data($dup_array,$data_arr)
	{
		$parentid_arr = array();
		$ret_arr = array();
		$parentid_list = implode("','",$dup_array);
		if(count($dup_array)>0)
		{
			$sql = "SELECT a.parentid,a.companyname,a.area,a.pincode,a.city,a.building_name as building,a.landmark,b.phone_search,b.display_flag,b.paid as paidstatus,a.full_address as address,b.data_city,a.latitude,a.longitude FROM db_iro.tbl_companymaster_generalinfo a JOIN db_iro.tbl_companymaster_search b on a.parentid=b.parentid WHERE a.parentid IN ('".$parentid_list."')";
			$res = parent::execQuery($sql, $this->conn_iro);			
			if($res && mysql_num_rows($res)>0){
				while($row = mysql_fetch_assoc($res)){
					$eligible_arr[] = $row['parentid'];
					$parentid_arr[$row['parentid']]['compname'] 	=	$row['companyname'];
					$parentid_arr[$row['parentid']]['data_city']	=	$row['data_city'];
					$parentid_arr[$row['parentid']]['area']			=	$row['area'];
					$parentid_arr[$row['parentid']]['pincode']	 	=	$row['pincode'];
					$parentid_arr[$row['parentid']]['city']	 		=	$row['city'];
					$parentid_arr[$row['parentid']]['phone_search'] =	$row['phone_search'];
					$parentid_arr[$row['parentid']]['building'] 	=	$row['building'];
					$parentid_arr[$row['parentid']]['landmark'] 	=	$row['landmark'];
					$parentid_arr[$row['parentid']]['display_flag'] =	$row['display_flag'];
					$parentid_arr[$row['parentid']]['paidstatus'] 	=	$row['paidstatus'];
					$parentid_arr[$row['parentid']]['address'] 		=	$row['address'];
					$parentid_arr[$row['parentid']]['expired_flag'] = 	'';
					$parentid_arr[$row['parentid']]['expired_on']   =   '';
					$parentid_arr[$row['parentid']]['latitude']  	=   $row['latitude'];
					$parentid_arr[$row['parentid']]['longitude']    =   $row['longitude'];
					
					$parentid_arr[$row['parentid']]['rule'] 		=	$data_arr[$row['parentid']]['rule'];
					$parentid_arr[$row['parentid']]['tagging'] 		=	$data_arr[$row['parentid']]['tagging'];
				}
			}
			if(count($parentid_arr)>0)
			{
				$sql_expired_chk= "SELECT a.*,b.paid_flag,b.expired_on FROM db_iro.tbl_id_generator a LEFT JOIN dbteam_temp.tbl_active_parentid b on a.parentid=b.parentid WHERE  a.parentid in ('".$parentid_list."')";
					
				$res_ex_chk = parent::execQuery($sql_expired_chk, $this->conn_iro_slave);			
				if($res_ex_chk && mysql_num_rows($res_ex_chk)>0){
					while($row_ex_chk = mysql_fetch_assoc($res_ex_chk)){	
						if($row_ex_chk['paid_flag'] == 2){
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = $row_ex_chk['paid_flag'];
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = $row_ex_chk['expired_on'];
						}	
						else{ 			
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = '0';
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = '';
						}	
					}
				}
				foreach($dup_array AS $key=>$val){
					if(in_array($val,$eligible_arr))
					{
						$row_arr['parentid']		=	$val; 
						$row_arr['companyname'] 	=	$parentid_arr[$val]['compname']; 
						$row_arr['data_city'] 		=	$parentid_arr[$val]['data_city']; 
						$row_arr['area'] 			=	$parentid_arr[$val]['area'];
						$row_arr['pincode'] 		=	$parentid_arr[$val]['pincode'];						
						$row_arr['city'] 			=	$parentid_arr[$val]['city'];						
						$row_arr['phone'] 			=	$parentid_arr[$val]['phone_search']; 
						$row_arr['building'] 		=	$parentid_arr[$val]['building']; 
						$row_arr['landmark'] 		=	$parentid_arr[$val]['landmark']; 
						$row_arr['display_flag'] 	=	$parentid_arr[$val]['display_flag']; 
						$row_arr['paidstatus'] 		=	$parentid_arr[$val]['paidstatus']; 
						$row_arr['address'] 		=	$parentid_arr[$val]['address']; 
						$row_arr['expired_flag'] 	=	$parentid_arr[$val]['expired_flag']; 
						$row_arr['expired_on'] 		=	$parentid_arr[$val]['expired_on']; 
						$row_arr['tagging_type'] 	=	$data_arr[$val]['tagging'];
						$row_arr['rule'] 			=	$data_arr[$val]['rule'];
						$row_arr['match_percentage_comp'] 			=	$data_arr[$val]['match_percentage_comp'];
						$row_arr['match_percentage_address'] 			=	$data_arr[$val]['match_percentage_address'];
						$row_arr['match_percentage_phone'] 			=	$data_arr[$val]['match_percentage_phone'];
						$row_arr['match_percentage_pincode'] 			=	$data_arr[$val]['match_percentage_pincode'];
						$row_arr['latitude'] 		=	$parentid_arr[$val]['latitude'];
						$row_arr['longitude'] 		=	$parentid_arr[$val]['longitude'];
						$ret_arr[] = $row_arr;			
					} 					
				} 
			}	
		}
		return $ret_arr;	
	}
	function getDataSearch()
	{
		global $params,$con;
		$arr_numbers = explode(",",$params['phone']);
		
		if(count($arr_numbers)>0)
		{	 
			/*$param_arr['phone'] 	= 	$params['phone'];
			$param_arr['dcity'] 	= 	$this->data_city;
			$param_arr['scity'] 	= 	$this->data_city;
			$param_arr['mod'] 		=	'cs';
			$param_arr['limit'] 	=	'500';
			$param_arr['stpos'] 	=	'0';
			$param_arr['act'] 		=	'3';
			$param_arr['debug'] 	=	'0';
			$param_arr['t'] 		=	mt_rand();
			$search_arr = json_decode($this->get_data($param_arr),true);
			
		 	$pid_arr = Array();
			
			foreach($search_arr['results']['data'] AS $key=>$val){
				$pid_arr[] = $val['parentid'];
			}
			$parentid_list = implode("','",$pid_arr); 
			*/
			$parentid_arr = Array();
			$phone_search_arr = Array();
			$phone_search = str_replace(","," ",$params['phone']);
			if(1)//count($pid_arr)>0)			
			{
				//$sql = "SELECT a.parentid,a.pincode,a.phone_search ,b.freeze,b.mask FROM tbl_companymaster_search a join tbl_companymaster_extradetails b  on a.parentid=b.parentid WHERE a.parentid in ('".$parentid_list."')";
				$sql = "SELECT DISTINCT a.parentid,a.companyname,a.pincode,a.phone_search,a.paid ,b.freeze,b.mask,b.data_city FROM db_iro.tbl_companymaster_search a JOIN db_iro.tbl_companymaster_extradetails b  on a.parentid=b.parentid WHERE MATCH(a.phone_search) AGAINST ('".$phone_search."' IN BOOLEAN MODE) order BY paid DESC";
				$res = parent::execQuery($sql, $this->conn_iro);			
				if($res && mysql_num_rows($res)>0){
					while($row = mysql_fetch_assoc($res)){
						$parentid_list_arr[] = $row['parentid'];
						$parentid_arr[$row['parentid']]['parentid'] = $row['parentid'] ? $row['parentid'] : "";;
						$parentid_arr[$row['parentid']]['companyname'] = $row['companyname'] ? $row['companyname'] : "";;
						$parentid_arr[$row['parentid']]['paid'] = $row['paid'];
						$parentid_arr[$row['parentid']]['pincode'] = $row['pincode'] ? $row['pincode'] : "";;
						$parentid_arr[$row['parentid']]['phone_search'] = $row['phone_search'] ? $row['phone_search'] : "";
						$parentid_arr[$row['parentid']]['data_city'] = $row['data_city'] ? $row['data_city'] : "";
						if($row['freeze'] == '0' and $row['mask'] == '0' )
							$parentid_arr[$row['parentid']]['display_flag'] = '1';
						else	
							$parentid_arr[$row['parentid']]['display_flag'] = '0';
					}
				}
				
				$parentid_list = implode("','",$parentid_list_arr); 
				$sql_expired_chk= "SELECT a.*,b.paid_flag,b.expired_on FROM db_iro.tbl_id_generator a  left join dbteam_temp.tbl_active_parentid b on a.parentid=b.parentid WHERE  a.parentid in ('".$parentid_list."')";
				
				$res_ex_chk = parent::execQuery($sql_expired_chk, $this->conn_iro_slave);			
				if($res_ex_chk && mysql_num_rows($res_ex_chk)>0){
					while($row_ex_chk = mysql_fetch_assoc($res_ex_chk)){	
						if($row_ex_chk['paid_flag'] == 2){
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = $row_ex_chk['paid_flag'];
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = $row_ex_chk['expired_on'];
						}	
						else{ 			
							$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = '0';
							$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = '';
						}	
					}
				}

				foreach($parentid_arr AS $key=>$val){
					$row_arr['parentid']		=	$val['parentid']; 
					$row_arr['companyname'] 	=	$val['companyname']; 
					$row_arr['data_city'] 		=	$val['data_city']; 
					$row_arr['pincode'] 		=	$val['pincode'];
					$row_arr['phone'] 			=	$val['phone_search']; 
					$row_arr['display_flag'] 	=	$val['display_flag']; 
					$row_arr['paidstatus'] 		=	$val['paid']; 
					$row_arr['expired_flag'] 	=	$val['expired_flag']; 
					$row_arr['expired_on'] 		=	$val['expired_on']; 				
					$phone_search_arr[] 		= $row_arr;			
				} 
			}
			if($params['trace']==1) 
			{
				echo "\n---------------------------------------------------------------------------------\n";
				echo "Phone Search";
				echo "\n---------------------------------------------------------------------------------\n";
				print_r($phone_search_arr);
			}	
			return $phone_search_arr;			
		}
	}
	function get_data($param_arr)
	{
		switch(strtolower($this->data_city))
		{	
			case 'mumbai' 		: $url = "http://".MUMBAI_IRO_IP;break;
			case 'delhi' 		: $url = "http://".DELHI_IRO_IP;break;
			case 'kolkata' 		: $url = "http://".KOLKATA_IRO_IP;break;
			case 'bangalore' 	: $url = "http://".BANGALORE_IRO_IP;break;
			case 'chennai' 		: $url = "http://".CHENNAI_IRO_IP;break;
			case 'pune' 		: $url = "http://".PUNE_IRO_IP;break;
			case 'hyderabad' 	: $url = "http://".HYDERABAD_IRO_IP;break;
			case 'ahmedabad' 	: $url = "http://".AHMEDABAD_IRO_IP;break;
			default 			: $url = "http://".REMOTE_CITIES_IRO_IP;break;					
		}
		$curl_url = $url . "/mvc/autosuggest/Adv_search?".http_build_query($param_arr);	

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curl_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);		 
		curl_close($ch);
		return $data;	
	}
	function getData($strcityIP)
	{
		GLOBAL $params,$con;
		$parentid_arr = Array();
		$phone_search_arr = Array();
		$st = 0;
		$lm = 999;
		
		$csi		=	$params['companyname'];
		$csi1 		=	$this->braces_content_removal($this->sanitize($csi,1));	
		$csi2       =	$this->getIgnored($csi1);
		$compname_search_ignore =	$this->getSingular($csi2);
		$params['companyname'] = $compname_search_ignore;
		
		
		if(trim($params['landmark']))
			$addrarray[]=trim($params['landmark']);
		if(trim($params['street']))
			$addrarray[]=trim($params['street']);
		if(trim($params['area']))
			$addrarray[]=trim($params['area']);
		
	
		if(count($addrarray) > 0)
			$fulladdress = implode(',',$addrarray);
		else
			$fulladdress ="";
		if(count($addrarray) > 0)
			$params['address']		=	trim($params['landmark'].",".$params['building'].",".$params['street'],",");
		 
		
		$spx = new SphinxClient();
		if($params['trace']==1)
		{
			echo "\nSphinx Server IP inside getData fn. @".$strcityIP." sec\n";
		}
		
		//$spx->SetServer($strcityIP,3380);
		$spx->SetServer($strcityIP,3382);
		$spx->SetMatchMode(SPH_MATCH_EXTENDED2);
		$spx->SetRankingMode(SPH_RANK_EXPR,'SUM(word_count)');
		$spx->SetSortMode(SPH_SORT_EXTENDED,'@relevance DESC');


		$cmp = strstr($params['companyname'],' ') ? $this->quoram($params['companyname']) : '"'.$params['companyname'].'"';
		if(!empty($params['address'])){
			$adr = ' @ads '.$this->quoram(trim(str_replace('  ',' ',$params['address'])));
		}
		$query[] = $cmd = '@csi '.$cmp.$adr.' @pin "'.trim($params['pincode']).'" @ps '.str_replace(',','|',$params['phone']);
		//$spx->AddQuery($cmd,'comp_search,rt_comp_search');
		$spx->AddQuery($cmd,'comp_search,comp_search_instant');
		 
		$res = $spx->RunQueries();
		
		
		$cnt=0;
		$display_str = '<table border="0" cellspacing="3" cellpadding="3" style="font:12px verdana;">';
		for($i=0;$i<count($res);$i++)
		{
			$display_str .= '<tr><td><h4>'.$i.'.'.$query[$i].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total = '.$res[$i]['total'].'</h4> </td></tr>';
			$display_str .= '<tr><td><table border="1" cellspacing="3" cellpadding="3" style="font:12px verdana;">';
			if(count($res[$i]['matches'])>0)
			{
				foreach($res[$i]['matches'] as $idx=>$val)
				{
					$dup_array[] 	 = $res[$i]['matches'][$idx]['attrs']['jid'];			
					
					$cnt++;
				}
			}
			$display_str .= '</table></td></tr>';
		}
		$display_str .= '</table>';
		$parentid_list = implode("','",$dup_array);
		if(count($dup_array)>0)
		{
			$sql = "SELECT a.parentid,a.companyname,a.pincode,a.building_name as building,a.landmark,b.phone_search,b.display_flag,b.paid as paidstatus,b.address,b.data_city FROM db_iro.tbl_companymaster_generalinfo a JOIN db_iro.tbl_companymaster_search b on a.parentid=b.parentid WHERE a.parentid IN ('".$parentid_list."')";
			$res = parent::execQuery($sql, $this->conn_iro);			
			if($res && mysql_num_rows($res)>0){
				while($row = mysql_fetch_assoc($res)){
					$parentid_arr[$row['parentid']]['compname'] 	=	$row['companyname'];
					$parentid_arr[$row['parentid']]['data_city']	=	$row['data_city'];
					$parentid_arr[$row['parentid']]['pincode']	 	=	$row['pincode'];
					$parentid_arr[$row['parentid']]['phone_search'] =	$row['phone_search'];
					$parentid_arr[$row['parentid']]['building'] 	=	$row['building'];
					$parentid_arr[$row['parentid']]['landmark'] 	=	$row['landmark'];
					$parentid_arr[$row['parentid']]['display_flag'] =	$row['display_flag'];
					$parentid_arr[$row['parentid']]['paidstatus'] 	=	$row['paidstatus'];
					$parentid_arr[$row['parentid']]['address'] 		=	$row['address'];
				}
			}
			$sql_expired_chk= "SELECT a.*,b.paid_flag,b.expired_on FROM db_iro.tbl_id_generator a  left join dbteam_temp.tbl_active_parentid b on a.parentid=b.parentid WHERE  a.parentid in ('".$parentid_list."')";
				
			$res_ex_chk = parent::execQuery($sql_expired_chk, $this->conn_iro_slave);			
			if($res_ex_chk && mysql_num_rows($res_ex_chk)>0){
				while($row_ex_chk = mysql_fetch_assoc($res_ex_chk)){	
					if($row_ex_chk['paid_flag'] == 2){
						$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = $row_ex_chk['paid_flag'];
						$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = $row_ex_chk['expired_on'];
					}	
					else{ 			
						$parentid_arr[$row_ex_chk['parentid']]['expired_flag'] = '0';
						$parentid_arr[$row_ex_chk['parentid']]['expired_on']   = '';
					}	
				}
			}
			foreach($dup_array AS $key=>$val){
				$row_arr['parentid']		=	$val; 
				$row_arr['companyname'] 	=	$parentid_arr[$val]['compname']; 
				$row_arr['data_city'] 	=	$parentid_arr[$val]['data_city']; 
				$row_arr['pincode'] 		=	$parentid_arr[$val]['pincode'];
				$row_arr['phone'] 			=	$parentid_arr[$val]['phone_search']; 
				$row_arr['building'] 		=	$parentid_arr[$val]['building']; 
				$row_arr['landmark'] 		=	$parentid_arr[$val]['landmark']; 
				$row_arr['display_flag'] 	=	$parentid_arr[$val]['display_flag']; 
				$row_arr['paidstatus'] 		=	$parentid_arr[$val]['paidstatus']; 
				$row_arr['address'] 		=	$parentid_arr[$val]['address']; 
				$row_arr['expired_flag'] 	=	$parentid_arr[$val]['expired_flag']; 
				$row_arr['expired_on'] 		=	$parentid_arr[$val]['expired_on']; 
				
				$phone_search_arr[] = $row_arr;			
			} 
		}
		
		if($params['trace']==1) {
				echo "\n---------------------------------------------------------------------------------\n";
				echo "Company Search";
				echo "\n---------------------------------------------------------------------------------\n";
				print_r($phone_search_arr);
		}		
		return $phone_search_arr;
		
	}
	function quoram($str)
	{
		$bits = preg_split('/\s+/',trim($str));
		if(count($bits)<4){
			$quorum = ceil(count($bits)*0.6);
		}
		else{
			$quorum = ceil(count($bits)*0.75);
		}
		$str2 = '='.implode(' =',$bits);
		$str = '"'.$str.'"/'.$quorum.' | "'.$str2.'"/'.$quorum;
		return $str;
	}
	function quoram_new($str,$percentage)
	{
		$bits = preg_split('/\s+/',trim($str));
		$quorum = ceil(count($bits)*($percentage/100));		
		$str2 = '='.implode(' =',$bits);
		$str = '"'.$str.'"/'.$quorum.' | "'.$str2.'"/'.$quorum;
		return $str;
	}
	
	function update($d,$p)
	{
		GLOBAL $con;
		$dids = '';
		$upd = "UPDATE tbl_duplicate_marking_data SET doneflag=1 WHERE docid='".$p."'";
		mysql_query($upd);
		foreach($d as $i=>$v)
		{
			if($d[$i]['attrs']['did']!=$p)
			{
				$dids .= $d[$i]['attrs']['did'].',';
				$upd = "UPDATE tbl_duplicate_marking_data SET doneflag=1 WHERE docid='".$d[$i]['attrs']['did']."'";
				mysql_query($upd);
			}
		}
		echo "\nFound Duplicate For $p.\n";
		$ins = "INSERT INTO duplicate_data_marked VALUES('".$p."','".trim($dids,',')."')";
		mysql_query($ins);
	}
	function braces_content_removal($str,$i=0)
  	{	
		
		$sflag = $eflag = false;
		$start=$end=0;
		if(stristr($str,'(') || stristr($str,')'))
		{
			if(preg_match('/\(/',$str))
			{
				$sflag = true;	
				$start = strpos($str,'(');
			}
			
			if(preg_match('/\)/',$str))
			{
				$eflag = true;				
				$end = strpos($str,')');
			}
			if(!$eflag)
			{
				$end =$start;	
			}
			if(!$sflag)
			{				
				$start = $end;
			}

			if($end < $start)
			{			
				$start = 0;
			}			
			$str = substr_replace($str, '', $start, ($end-$start)+1);
		
			$str = $this->braces_content_removal($str,++$i);
			
			return trim($str);
		}
		else
		{
			$str = preg_replace('/\s\s+/',' ',trim($str));
			return trim($str);
		}
	}	
	public function sanitize($str,$case='')
	{
		$str = preg_replace("/[@&\-\.,_]+/",' ',$str);
		if($case)
			$str = preg_replace("/[^a-zA-Z0-9\s\(\)]+/",'',$str);
		else
			$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);

		$str = preg_replace('/\\\+/i','',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim($str);
	}
 	function getIgnored($str)
	{
		$ign = '';
		$filter['icmp'] = array('/\bthe\b/i','/\bdr\b/i','/^\bprof\b/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\band\b/i','/\blimited\b/i','/\bbe\b/i','/\brestaurant[s]*\b$/i');
		$tmp = explode(' ',$str);
		$c = count($tmp);
		if($c>1){
			$ign = $str;
			foreach($filter['icmp'] as $w)
			{
				if(!preg_match('/^\bdr\b/',$str) && !preg_match('/^\bthe\b/',$str))
					$ign = preg_replace($w,'',trim($ign));
				}
		}
		$ign = preg_replace('/[@&-.,_)(\s+]+/',' ',$ign);
		return (strlen($ign)<=1) ? $str : $ign;
	}
	function getSingular($str='')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','watches'=>'watch','access'=>'access');
		$r = array('ss'=>'ss','os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
		foreach($t as $v){
			if(strlen($v)>=4){
				$f = false;
				foreach(array_keys($r) as $k){
					if(substr($v,(strlen($k)*-1))!=$k){
						continue;
					}
					else{
						$f = true;
						if(array_key_exists($v,$e))
							$s[] = $e[$v];
						else
							$s[] = substr($v,0,strlen($v)-strlen($k)).$r[$k];

						break;
					}
				}
				if(!$f){
					$s[] = $v;
				}
			}
			else{
				$s[] = $v;
			}
		}
		return (!empty($s)) ? implode(' ',$s) : $str;
	}
	function get_compdata($parentid)
	{
		$sql = "SELECT a.parentid,a.companyname,a.pincode,a.area,a.full_address,a.pincode,b.phone_search,a.latitude,a.longitude FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_search  b ON a.parentid=b.parentid WHERE a.parentid in ('".$parentid."') LIMIT 2000";
		//$sql = "SELECT * FROM tbl_companymaster_generalinfo a JOIN tbl_companymaster_search  b ON a.parentid=b.parentid WHERE a.parentid='".$parentid."' LIMIT 1";
		
		$res = parent::execQuery($sql, $this->conn_iro); 
		$numRows = mysql_num_rows($res);
		
		$rows	=	array();
		$compdata	=	array();
		if($numRows > 0)
		{	
			while($rows = mysql_fetch_assoc($res))
				$compdata[$rows['parentid']] = $rows;			
		}	
		return $compdata;	 
			
	}
	function get_api_param()
	{
 		GLOBAL $params,$con;
 		 		 
		if(trim($params['building']))
			$addrarray[]=trim($params['building']);		
		if(trim($params['landmark']))
			$addrarray[]=trim($params['landmark']);
		if(trim($params['street']))
			$addrarray[]=trim($params['street']);
		if(trim($params['area']))
			$addrarray[]=trim($params['area']);
		
	
		if(count($addrarray) > 0)
			$fulladdress = implode(',',$addrarray);
		else
			$fulladdress ="";
		if(!empty($params['pincode']))
		{
			$fulladdress .=	"-".$params['pincode'];
		} 	
		
		$params['address']	=	trim($fulladdress,",");	
		$ret = "http://172.29.0.217:811/services/duplicate_check.php?".http_build_query($params);	
		if($params['trace']==1) {
			echo "\n---------------------------------------------------------------------------------\n";
			echo "Url = >";
			echo "\n---------------------------------------------------------------------------------\n";
			echo $ret;
		}	
		return $ret;
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
	}	
	private function ascii_char($string)
	{
		return htmlentities($string, ENT_QUOTES);
	}	
}
?>
