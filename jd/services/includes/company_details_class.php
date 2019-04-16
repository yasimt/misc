<?php

class company_details_class extends DB
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
		
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}		
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['action']) != "" && $this->params['action'] != null)
		{
			$this->action  = $this->params['action']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='action missing';
			echo json_encode($errorarray); exit;
		}
			
		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize datacity
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['remote']) != "" && $this->params['remote'] != null)
		{
			$this->remote  = $this->params['remote']; //initialize remote
		}
		
		if(trim($this->params['source']) != "" && $this->params['source'] != null)
		{
			$this->source  = strtolower(trim($this->params['source'])); //initialize remote
		}

		if($this->action == '2')
		{
			if(trim($this->params['catid']) != "" || trim($this->params['limit']) != "")
			{
				$this->catid  = $this->params['catid']; //initialize paretnid
				$this->limit  = $this->params['limit']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='catid or limit missing';
				echo json_encode($errorarray); exit;
			}
		}
		
		if($this->action == '4')
		{
			if(trim($this->params['report_type']) != "")
			{
				$this->report_type  = $this->params['report_type']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='report_type missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['limit']) != "")
			{
				$this->limit  = $this->params['limit']; //initialize limit
			}else
			{
				$errorarray['errormsg']='limit missing';
				echo json_encode($errorarray); exit;
			}
			
		}
		
		if($this->action == '5')
		{
			if(trim($this->params['txtReason']) != "")
			{
				$this->txtReason  = $this->params['txtReason']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='txtReason missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['block_flag']) != "")
			{
				$this->block_flag  = $this->params['block_flag']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='block_flag missing';
				echo json_encode($errorarray); exit;
			}
			
			$this->user_code  = $this->params['txtReason']; //initialize report_type
		}
		
		if($this->action == '6')
		{
			if(trim($this->params['from_date']) != "")
			{
				$this->from_date  = $this->params['from_date']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='from_date missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['end_date']) != "")
			{
				$this->end_date  = $this->params['end_date']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='end_date missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['llimit']) != "")
			{
				$this->llimit  = $this->params['llimit']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='llimit missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['ulimit']) != "")
			{
				$this->end_date  = $this->params['ulimit']; //initialize report_type
			}else
			{
				$errorarray['errormsg']='ulimit missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['unique']) != "")
			{
				$this->unique  = $this->params['unique']; //initialize unique
			}
			
			if(trim($this->params['misc_flag']) != "")
			{
				$this->misc_flag  = $this->params['misc_flag']; //initialize misc_flag
			}
			
			
		}
		
		if($this->action == '8')//Log complain and its details 
		{
			if(trim($this->params['companyname']) != "")
			{
				$this->companyname  = $this->params['companyname']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='companyname missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['complain_type']) != "")
			{
				$this->complain_type  = $this->params['complain_type']; //initialize paretnid
			}else
			{
				$errorarray['errormsg']='complain_type missing';
				echo json_encode($errorarray); exit;
			}
			
			
			if(trim(urldecode($this->params['complain_name']) != ""))
			{
				$this->complain_name  = trim(urldecode($this->params['complain_name'])); //initialize paretnid
			}
			
			if(trim($this->params['client_comment']) != "")
			{
				$this->client_comment  = urldecode($this->params['client_comment']); //initialize paretnid
			}else
			{
				$errorarray['errormsg']='client_comment missing';
				echo json_encode($errorarray); exit;
			}
			
			if(trim($this->params['ratings']) != "")
			{
				$this->cs_feedback_ratings  = urldecode($this->params['ratings']); //initialize cs rating by client
			}
			
			if(trim($this->params['registeredbyId']) != "")
			{
				$this->registeredbyId  = urldecode($this->params['registeredbyId']); //initialize cs rating by client
			}else
			{
				$this->registeredbyId  = 'Verified Owner-Web'; //initialize cs rating by client
			}
			
			if(trim($this->params['registeredbyName']) != "")
			{
				$this->registeredbyName  = urldecode($this->params['registeredbyName']); //initialize cs rating by client
			}else
			{
				$this->registeredbyName  = 'Verified Owner-Web'; //initialize cs rating by client
			}
			
			if(trim($this->params['complain_source']) != "")
			{
				$this->complain_source  = urldecode($this->params['complain_source']); //initialize cs rating by client
			}else
			{
				$this->complain_source  = 'JD Web - Complaint Through Website'; //initialize cs rating by client
			}
			
								
		}
		
		if($this->action == '45')//Update data coming from Edit listing audit module
		{
			
			
			//$this -> searchcriteria = strtolower(trim($this->params['type'])) == 'sp' ? 1 : strtolower(trim($this->params['type'])) == 'spp' ? 2 : 0;
			if(strtolower(trim($this->params['type'])) == 'sp')
			{
				$this -> searchcriteria = 1;
			}
			else if(strtolower(trim($this->params['type'])) == 'spp')
			{
				$this -> searchcriteria = 2;
			}
			else
			{
				$this -> searchcriteria = 0;
			}
			//echo $this -> searchcriteria;die;
			$this -> radius_of_sign = trim($this->params['dwr']);
			
			$this -> comment 		= trim(urldecode($this->params['cmt']));
		}
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
					
		if(DEBUG_MODE)
		{
			echo '<pre>db array :: ';
		print_r($db);
		}
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConTmeJds 		= $db[$data_city]['tme_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		//$this->db_budgeting		= $db[$data_city]['db_budgeting']['master'];
		if(DEBUG_MODE)
		{
			echo '<pre> IDc db array :: ';
			print_r($this->dbConIdc);
		}
		
		$this->jdbox_url = constant(strtoupper($data_city).'_CS_JDBOX_URL');
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
			break;
		}
	}
	
	function setsphinxid()
	{
			$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
			$res = parent::execQuery($sql, $this->dbConIro);

			if($res && mysql_num_rows($res) )
			{
					$row= mysql_fetch_assoc($res);
					$this->sphinx_id = $row['sphinx_id'];
					$this->docid = $row['docid'];
			}else
			{
					echo "sphinx_id not found in tbl_id_generator";
					exit;
			}
	}
	
	function getCompanyDetails()
	{
		$sql = "SELECT * FROM tbl_companymaster_generalinfo a join tbl_companymaster_extradetails b on a.parentid=b.parentid WHERE a.parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->dbConIro);
		if($res && mysql_num_rows($res) )
		{
				
				$row= mysql_fetch_assoc($res);
				$company_details_arr['company_details']['cp'] =$row['contact_person_display'];
				$company_details_arr['company_details']['cn'] =$row['companyname'];
				$company_details_arr['company_details']['bd'] =$row['building_name'];
				$company_details_arr['company_details']['str']=$row['street'];
				$company_details_arr['company_details']['ar'] =$row['area'];
				$company_details_arr['company_details']['lk'] =$row['landmark'];
				$company_details_arr['company_details']['st'] =$row['state'];
				$company_details_arr['company_details']['ct'] =$row['city'];
				$company_details_arr['company_details']['pc'] =$row['pincode'];
				$company_details_arr['company_details']['lt'] =$row['latitude'];
				$company_details_arr['company_details']['lo'] =$row['longitude'];
				$company_details_arr['company_details']['mb'] =$row['mobile_display'];
				$company_details_arr['company_details']['em'] =$row['email_display'];
				$company_details_arr['company_details']['fem']=$row['email_feedback'];
				$company_details_arr['company_details']['web']=$row['website'];
				$company_details_arr['company_details']['tel']=$row['landline_display'];
				$company_details_arr['company_details']['tol']=$row['tollfree'];
				$company_details_arr['company_details']['vn'] =$row['virtualNumber'];
				$company_details_arr['company_details']['bfv']=$row['blockforvirtual'];
				$company_details_arr['company_details']['yoe']=$row['year_establishment'];
				$company_details_arr['company_details']['noe']=$row['no_employee'];
				$company_details_arr['company_details']['wts']=$row['working_time_start'];
				$company_details_arr['company_details']['wte']=$row['working_time_end'];
				
				/*to get categories of contract from company master - start*/
				
				$getcatids			 = trim($row['catidlineage'],'/');
				
				if(stristr($getcatids,'/,/'))
				$getcatids 			 = str_replace('/,/',',',$getcatids);
								/*--------------------Making sure that even if there is blank catids we will still resolve the problem----------------------*/
				$arr_catids_lineage  = array();
				$CatidarrRemoveNull 		 = array();

				$getcatids 			 = str_replace('/','',$getcatids);
				$arr_catids_lineage  = explode(',',$getcatids);

				$CatidarrRemoveNull	 = array_filter($arr_catids_lineage);

				$company_details_arr['main_bus_cats'] = $this->getCategoryArr($CatidarrRemoveNull);
				
				$company_details_arr['bid_cats'] = $this->getBiddingCategories();
				
				/*to get categories from a contract from company master - end*/
				
				/*to get package categories - start*/
				
				/*to get package categories - end*/
				
				
				$payment_details_arr = $this->getPaymentDetails();
				
				
				
				if(count($payment_details_arr)>0)
				$company_details_arr = array_merge($company_details_arr,$payment_details_arr);
				
				return $company_details_arr;
				
		}else
		{
				echo "data not found in generalinfo table";
				exit;
		}
	}
	
	function getCategoryArr($catid_arr)
	{
		if(count($catid_arr)>0)
		{
			$getcatids = implode("','",$catid_arr);
			//$sql="SELECT catid,category_name as catname,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$getcatids."') AND isdeleted=0";
			//$res = parent::execQuery($sql, $this->dbConDjds);
			$cat_params = array();
			$cat_params['page'] ='company_details_class';
			$cat_params['data_city'] 	= $this->data_city;			
			$cat_params['return']		= 'catid,category_name';

			$catid_list 	=	implode(",",$catid_arr);
			$where_arr  	=	array();
			$where_arr['catid']		= $catid_list;
			$where_arr['isdeleted']	= '0';		
			$cat_params['where']	= json_encode($where_arr);
			if($catid_list!=''){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode'] == '0' && count($cat_res_arr['results'])>0)
			{
				$catid_details_arr = array();
				foreach($cat_res_arr['results'] as $key=>$row){
					$catid_details_arr[$row['catid']]['catname'] = $row['category_name'];
					$catid_details_arr[$row['catid']]['nid'] = $row['national_catid'];
				}
			
				return $catid_details_arr;
			}
		}
		
	}
	
	function getBiddingCategories()
	{
		$bidding_cats_arr = array();
		
		$sql_package_count = "SELECT count(1) as package_count FROM tbl_bidding_details  WHERE parentid='".$this->parentid."' AND position_flag=100 AND pincode!='999999'";
		$res_package_count = parent::execQuery($sql_package_count, $this->fin);
		if($res_package_count && mysql_num_rows($res_package_count))
		{
			$row_package_count = mysql_fetch_assoc($res_package_count);
			$bidding_cats_arr['pkg_counts'] = $row_package_count['package_count'];
		}
		
		$sql_package = "SELECT GROUP_CONCAT(catid) AS package_catids FROM tbl_bidding_details  WHERE parentid='".$this->parentid."' AND position_flag=100 AND pincode!='999999'";
		$res_package = parent::execQuery($sql_package, $this->fin);
		if($res_package && mysql_num_rows($res_package))
		{
			$row_package = mysql_fetch_assoc($res_package);
			
			$package_catid_arr = explode(',',$row_package['package_catids']);
			
			$bidding_cats_arr['pkg_cats'] = $this->getCategoryArr($package_catid_arr);
		}
		
		
		$sql_pdg_count = "SELECT count(1) as pdg_count FROM tbl_bidding_details  WHERE parentid='".$this->parentid."' AND position_flag<100 AND pincode!='999999'";
		$res_pdg_count = parent::execQuery($sql_pdg_count, $this->fin);
		if($res_pdg_count && mysql_num_rows($res_pdg_count))
		{
			$row_pdg_count = mysql_fetch_assoc($res_pdg_count);
			$bidding_cats_arr['pdg_counts'] = $row_pdg_count['pdg_count'];
		}
		
		$sql_pdg = "SELECT GROUP_CONCAT(catid) AS pdg_catids FROM tbl_bidding_details  WHERE parentid='".$this->parentid."' AND position_flag<100 AND pincode!='999999'";
		$res_pdg = parent::execQuery($sql_pdg, $this->fin);
		if($res_package && mysql_num_rows($res_package))
		{
			$row_pdg = mysql_fetch_assoc($res_pdg);
			
			$pdg_catid_arr	   = explode(',',$row_pdg['pdg_catids']);
			
			$bidding_cats_arr['pdg_cats'] = $this->getCategoryArr($pdg_catid_arr);
		}
			return 	$bidding_cats_arr;
	}
	
	
	function getPaymentDetails()
	{
		$sql="SELECT a.parentid,a.campaignid,a.version,b.campaignname,a.bid_perday,a.daily_threshold,a.total_app_amount AS total_amount,(a.total_app_amount - a.balance) AS used_amount,a.balance AS balance_amount,a.start_date
			  FROM tbl_companymaster_finance a JOIN payment_campaign_master b
			  ON a.campaignid=b.campaignid WHERE a.parentid='".$this->parentid."' AND a.campaignid<=22";
		$res = parent::execQuery($sql, $this->fin);
		
		$sql_apport = "SELECT version, campaignid, budget, duration FROM payment_apportioning WHERE parentid='".$this->parentid."'";
		$res_apport = parent::execQuery($sql_apport, $this->fin);
		if($res_apport && mysql_num_rows($res_apport) )
		{
			$deal_closed_data = array();
			while($row_apport= mysql_fetch_assoc($res_apport))
			{
				//print_r($row_apport);
				$deal_closed_data[$row_apport['version']][$row_apport['campaignid']]['budget']   = $row_apport['budget'];
				$deal_closed_data[$row_apport['version']][$row_apport['campaignid']]['duration'] = $row_apport['duration'];
			}
			
			
		}
		
		if($res && mysql_num_rows($res) )
		{
				while($row= mysql_fetch_assoc($res))
				{
					
				
				  if($deal_closed_data[$row['version']][$row['campaignid']]['duration'] < 3650  || $this->source == 'web_edit')
				  {
					
					$company_details_arr['payment_details'][$row['campaignname']]['Campaign_Id']     = $row['campaignid'];
					$company_details_arr['payment_details'][$row['campaignname']]['Campaign_Name']   = $row['campaignname'];
					$company_details_arr['payment_details'][$row['campaignname']]['Bid_Per_Day'] 	 = $row['bid_perday'];
					$company_details_arr['payment_details'][$row['campaignname']]['Day_Threshold'] 	 = floor($row['daily_threshold']);
					$company_details_arr['payment_details'][$row['campaignname']]['Total_Amount'] 	 = $row['total_amount'];
					$company_details_arr['payment_details'][$row['campaignname']]['Used_Amount'] 	 = $row['used_amount'];
					$company_details_arr['payment_details'][$row['campaignname']]['Balance_Amount']  = $row['balance_amount'];
					$company_details_arr['payment_details'][$row['campaignname']]['Start_Date'] 	 = $row['start_date'];
					if($row['bid_perday']>0)
					{
						$company_details_arr['payment_details'][$row['campaignname']]['Balance_Days'] 	 = floor($row['balance_amount']/$row['bid_perday']);
						$company_details_arr['payment_details'][$row['campaignname']]['End_Date'] 	     = date('Y-m-d H:i:s',strtotime(date('Y-m-d')." + ".floor($row['balance_amount']/$row['bid_perday'])." days"));
					}
					if($row['campaignid'] == 1)
					{
						$sql_payment_tp = "SELECT * FROM tbl_payment_type_dealclosed WHERE parentid='".$this->parentid."' AND VERSION='".$row['version']."' AND  payment_type LIKE '%flexi_selected_user%'";
						$res_payment_tp = parent::execQuery($sql_payment_tp, $this->fin);
						if($res_payment_tp && mysql_num_rows($res_payment_tp) )
						{
							$company_details_arr['payment_details'][$row['campaignname']]['Campaign_Type']   = 'Flexi';
						}/*else {
							
							$sql_payment_tp_count = "SELECT catid,COUNT(pincode) AS pincode_count FROM tbl_bidding_details WHERE parentid='".$this->parentid."' AND campaignid =1  GROUP BY catid HAVING pincode_count>1";
							$res_payment_tp_count = parent::execQuery($sql_payment_tp_count, $this->fin);
							if($res_payment_tp_count && mysql_num_rows($res_payment_tp_count) )
							{
								$company_details_arr['payment_details'][$row['campaignname']]['Campaign_Type']   = 'Flexi';
							}
							
						}*/
						
					}
				  }
					
				}

			}
			
			$sql_nt = "SELECT a.parentid, a.version, a.campaignid,a.bid_perday,a.daily_threshold,a.total_app_amount AS total_amount,(a.total_app_amount - a.balance) AS used_amount,a.balance AS balance_amount,a.start_date  FROM db_national_listing.tbl_companymaster_finance_national  a WHERE a.parentid='".$this->parentid."' AND a.campaignid<=22";
			$res_nt = parent::execQuery($sql_nt, $this->dbConIdc);
			if($res_nt && mysql_num_rows($res_nt) )
			{
					while($row_nt= mysql_fetch_assoc($res_nt)){
					 if($deal_closed_data[$row_nt['version']][$row_nt['campaignid']]['duration'] < 3650 || $this->source == 'web_edit')
					 {
						
						$company_details_arr['payment_details']['National Registration - Phone']['Campaign_Id']     = $row_nt['campaignid'];
						$company_details_arr['payment_details']['National Registration - Phone']['Campaign_Name']   = 'National Registration - Phone';
						$company_details_arr['payment_details']['National Registration - Phone']['Bid_Per_Day'] 	 = $row_nt['bid_perday'];
						$company_details_arr['payment_details']['National Registration - Phone']['Day_Threshold'] 	 = floor($row_nt['daily_threshold']);
						$company_details_arr['payment_details']['National Registration - Phone']['Total_Amount'] 	 = $row_nt['total_amount'];
						$company_details_arr['payment_details']['National Registration - Phone']['Used_Amount'] 	 = $row_nt['used_amount'];
						$company_details_arr['payment_details']['National Registration - Phone']['Balance_Amount']  = $row_nt['balance_amount'];
						$company_details_arr['payment_details']['National Registration - Phone']['Start_Date'] 	 = $row_nt['start_date'];
						if($row_nt['bid_perday']>0)
						{
							$company_details_arr['payment_details']['National Registration - Phone']['Balance_Days'] 	 = floor($row_nt['balance_amount']/$row_nt['bid_perday']);
							$company_details_arr['payment_details']['National Registration - Phone']['End_Date'] 	     = date('Y-m-d H:i:s',strtotime(date('Y-m-d')." + ".floor($row_nt['balance_amount']/$row_nt['bid_perday'])." days"));
						}
					}
				}
			}
		
		return $company_details_arr;
		
	}
	
	function getFixedPositionDetails()
	{
		$bidding_cats_arr = array();
		
		if($this->limit)
		$limit = 'limit '.$this->limit;
		
		if($this->catid)
		$catid_condition = "AND catid='".$this->catid."'";
		
		$sql_pdg = "SELECT catid,pincode,position_flag,inventory FROM tbl_bidding_details WHERE parentid='".$this->parentid."' ".$catid_condition." AND position_flag<100 AND pincode!='999999' ".$limit;
		$res_pdg = parent::execQuery($sql_pdg, $this->fin);
		if($res_pdg && mysql_num_rows($res_pdg))
		{
			if($this->catid)
			$catname = $this->getCategoryArr(array($this->catid));
			
			while($row_pdg = mysql_fetch_assoc($res_pdg))
			{
				$bidding_cats_arr[$row_pdg['catid']]['catname'] = $catname;
				$bidding_cats_arr[$row_pdg['catid']]['ddg_details'][$row_pdg['pincode']][$row_pdg['position_flag']]['inv'] = $row_pdg['inventory'];
			}
			if(!$this->catid && count($bidding_cats_arr)>0)
			{
				$catid_arr = array_keys($bidding_cats_arr);
				if(count($catid_arr)>0)
				{
					foreach($catid_arr as $catid)
					{
						$catname_arr = $this->getCategoryArr(array($catid));
						$bidding_cats_arr[$catid]['catname'] = $catname_arr;
					}
				}
				
			}
			
			return $bidding_cats_arr;
		}
	}
	
	function ECS_SI_Mandate_Details()
	{
		$ecs_si_mandate_details_arr = array();
		$sql_ecs = "SELECT billdeskId,mandate_type,vertical_flag,acname,acno,bankname,branch,city,micr,ifs,actType,mode,mandatestartdate,tmename,mename,cycleselected,CycleDays,capamt  FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."'  AND data_city='".$this->data_city."'  AND  activeflag=1 AND ecs_stop_flag=0 AND deactiveflag=0";
		$res_ecs = parent::execQuery($sql_ecs, $this->fin);
		if($res_ecs && mysql_num_rows($res_ecs))
		{
			$row_ecs = mysql_fetch_assoc($res_ecs);
			$ecs_si_mandate_details_arr['ecs']['bdid'] = $row_ecs['billdeskId'];
			$ecs_si_mandate_details_arr['ecs']['mt'] = $row_ecs['mandate_type'];
			$ecs_si_mandate_details_arr['ecs']['acname'] = $row_ecs['acname'];
			$ecs_si_mandate_details_arr['ecs']['acno'] = $row_ecs['acno'];
			$ecs_si_mandate_details_arr['ecs']['bkname'] = $row_ecs['bankname'];
			$ecs_si_mandate_details_arr['ecs']['branch'] = $row_ecs['branch'];
			$ecs_si_mandate_details_arr['ecs']['ct'] = $row_ecs['city'];
			$ecs_si_mandate_details_arr['ecs']['micr'] = $row_ecs['micr'];
			$ecs_si_mandate_details_arr['ecs']['ifsc'] = $row_ecs['ifs'];
			$ecs_si_mandate_details_arr['ecs']['bat'] = $row_ecs['actType'];
			$ecs_si_mandate_details_arr['ecs']['mode'] = $row_ecs['mode'];
			$ecs_si_mandate_details_arr['ecs']['stdt'] = $row_ecs['mandatestartdate'];
			$ecs_si_mandate_details_arr['ecs']['spls'] = '';
			$ecs_si_mandate_details_arr['ecs']['tme'] = $row_ecs['tmename'];
			$ecs_si_mandate_details_arr['ecs']['me'] = $row_ecs['mename'];
			
			switch($row_ecs['cycleselected'])
			{
				case 30:
				$ecs_si_mandate_details_arr['ecs']['df'] = 'Monthly';
				break;
				case 15:
				$ecs_si_mandate_details_arr['ecs']['df'] = 'Fortnightly';
				break;
				case 7:
				$ecs_si_mandate_details_arr['ecs']['df'] = 'Weekly';
				break;
				default:
				$ecs_si_mandate_details_arr['ecs']['df'] = 'Invalid Cycle';
				break;
			}
			
			$ecs_si_mandate_details_arr['ecs']['dd'] = $row_ecs['CycleDays'];
			
			$ecs_si_mandate_details_arr['ecs']['catamt'] = (($row_ecs['capamt'] == '9999999')?'NA':$row_ecs['capamt']);
			
			$sql_ecs_count="SELECT COUNT(1) as ecs_count
							FROM db_ecs_billing.ecs_bill_clearance_details a JOIN db_ecs_billing.ecs_bill_details b ON a.billNumber = b.billNumber
							WHERE b.parentid='".$this->parentid."'";
			$res_ecs_count = parent::execQuery($sql_ecs_count, $this->fin);
			if($res_ecs_count && mysql_num_rows($res_ecs_count))
			{
				$row_ecs_count = mysql_fetch_assoc($res_ecs_count);
				$ecs_si_mandate_details_arr['ecs']['report_count'] = $row_ecs_count['ecs_count'];
			}
			
		}
		
		$sql_si = "SELECT billdeskId,acname,cardtype,cardno,expdtyy,expdtmm,bankname,mandatestartdate,cycleselected,cycledays,capamt FROM db_si.si_mandate WHERE parentid='".$this->parentid."'  AND data_city='".$this->data_city."'  AND  activeflag=1 AND ecs_stop_flag=0 AND deactiveflag=0";
		$res_si = parent::execQuery($sql_si, $this->fin);
		
		if($res_si && mysql_num_rows($res_si))
		{
			$row_si = mysql_fetch_assoc($res_si);
			
			$ecs_si_mandate_details_arr['si']['bdid'] = $row_si['billdeskId'];
			$ecs_si_mandate_details_arr['si']['acname'] = $row_si['acname'];
			$ecs_si_mandate_details_arr['si']['cdtype'] = $row_si['cardtype'];
			$ecs_si_mandate_details_arr['si']['cdno'] = $row_si['cardno'];
			$ecs_si_mandate_details_arr['si']['expdt'] = $row_si['expdtmm'].'-'.$row_si['expdtyy'];
			$ecs_si_mandate_details_arr['si']['bkname'] = $row_si['bankname'];
			$ecs_si_mandate_details_arr['si']['stdt'] = $row_si['mandatestartdate'];
			switch($row_si['cycleselected'])
			{
				case 30:
				$ecs_si_mandate_details_arr['si']['df'] = 'Monthly';
				break;
				case 15:
				$ecs_si_mandate_details_arr['si']['df'] = 'Fortnightly';
				break;
				case 7:
				$ecs_si_mandate_details_arr['si']['df'] = 'Weekly';
				break;
				default:
				$ecs_si_mandate_details_arr['si']['df'] = 'Invalid Cycle';
				break;
			}
			
			$ecs_si_mandate_details_arr['si']['dd'] = $row_si['cycledays'];
			
			$ecs_si_mandate_details_arr['si']['capamt'] = (($row_si['capamt'] == '9999999')?'NA':$row_si['capamt']);
			
			$sql_si_count="SELECT COUNT(1) as si_count 
						   FROM db_si_billing.si_ecs_bill_clearance_details a JOIN db_si_billing.si_ecs_bill_details b ON a.billNumber = b.billNumber
						   WHERE b.parentid='".$this->parentid."' ";
			$res_si_count = parent::execQuery($sql_si_count, $this->fin);
			
			if($res_si_count && mysql_num_rows($res_si_count))
			{
				$row_si_count = mysql_fetch_assoc($res_si_count);
				$ecs_si_mandate_details_arr['si']['report_count'] = $row_si_count['si_count'];
			}
			
		}
		
		return $ecs_si_mandate_details_arr;
		
	}
	
	Function GetECS_SI_Billing_Report()
	{
			
		if($this->limit)
		$limit = 'limit '.$this->limit;
		
		if($this->params['report_type'] == 'ecs')
		{
			
			$sql_ecs_si_report ="SELECT b.billDeskId,DATE_FORMAT(b.billDate,'%d-%m-%Y') AS  billDate ,DATE_FORMAT(b.dueDate,'%d-%m-%Y') AS  dueDate ,
								  b.billAmount,DATE_FORMAT(a.billResponseDate,'%d-%m-%Y %H:%i:%s') AS  billResponseDate,
								  IF(a.billResponseStatus=0,'Pending',IF(a.billResponseStatus=1,'Paid',IF(a.billresponsestatus=2,'Failed',IF(a.billresponsestatus=4,'Rejected','Late Return')))) AS billResponseStatus,a.billResponseRemarks,b.parentid , b.bill_tds_amount
								  FROM db_ecs_billing.ecs_bill_clearance_details a JOIN db_ecs_billing.ecs_bill_details b ON a.billNumber = b.billNumber
								  WHERE b.parentid='".$this->parentid."'  GROUP BY b.billnumber ORDER BY b.billDate DESC ".$limit;
		}else{
			
			$sql_ecs_si_report ="SELECT b.billDeskId,DATE_FORMAT(b.billDate,'%d-%m-%Y') AS  billDate ,DATE_FORMAT(b.dueDate,'%d-%m-%Y') AS  dueDate ,
									b.billAmount,DATE_FORMAT(a.billResponseDate,'%d-%m-%Y %H:%i:%s') AS  billResponseDate,
									IF(a.billResponseStatus=0,'Pending',IF(a.billResponseStatus=1,'Paid',IF(a.billresponsestatus=2,'Failed',IF(a.billresponsestatus=4,'Rejected','Late Return')))) AS billResponseStatus,
									a.billResponseRemarks,b.parentid , b.bill_tds_amount
									FROM db_si_billing.si_ecs_bill_clearance_details a JOIN db_si_billing.si_ecs_bill_details b ON a.billNumber = b.billNumber
									WHERE b.parentid='".$this->parentid."'  GROUP BY b.billnumber ORDER BY b.billDate DESC ".$limit;
										
		}
		$res_ecs_si_report = parent::execQuery($sql_ecs_si_report, $this->fin);
		if($res_ecs_si_report && mysql_num_rows($res_ecs_si_report)>0)
		{
			$ecs_tracker_report['ecs_trac_report']['columns']=array('bdte','duedte','bamt','bresdte','bres_status','bresremarks','btdsamt');
			while($row_ecs_si_report = mysql_fetch_assoc($res_ecs_si_report))
			{
				$ecs_tracker_report['ecs_trac_report']['data'][]=array($row_ecs_si_report['billDate'],$row_ecs_si_report['dueDate'],$row_ecs_si_report['billAmount'],$row_ecs_si_report['billResponseDate'],$row_ecs_si_report['billResponseStatus'],$row_ecs_si_report['billResponseRemarks'],$row_ecs_si_report['bill_tds_amount']);
			}
			
			return $ecs_tracker_report;
		}
		
		
						  
	}
	
	function Block_Unblock_VN()
	{

		 if($this->params['cs_url'])
		 {
			 
			$curl_url   = $this->params['cs_url']."paid/editPaidblockvirtual.php?parentid=".$this->params['parentid']."&ucode=".$this->params['ucode']."&txtReason=".$this->params['txtReason']."&block_flag=".urlencode($this->params['block_flag'])."&web_request_flag=1&module=web&data_city=".$this->params['data_city']."";
			 return $res = $this->curl_call_get($curl_url);
		 }
		  
		
	}
	
	function getFeedBackReport()
	{
		 if($this->params['cs_url'])
		 {
			
			if($this->params['unmask']==1 && ($this->params['api_called_by']=='' || $this->params['key']=='')){
				$results_array['error']['code'] = 1;
				$results_array['error']['msg'] = "Access key & api_called_by parameters are missing!";
				$return_string = json_encode($results_array);
				echo $return_string;
				exit;
			}else{
				
				if($this->params['unmask']==1 && $this->params['api_called_by']!='' && $this->params['key']!=''){
					
					$api_called_by = $this->params['api_called_by'];
					$key = $this->params['key']; 
					$str = "&unmask=1&api_called_by=".$api_called_by."&key=".$key."";
				}
			}
			
			$curl_url   = $this->params['cs_url']."processes/companyFeedbackApi.php?parentid=".$this->params['parentid']."&from_date=".$this->params['from_date']."&end_date=".$this->params['end_date']."&misc_flag=".$this->params['misc_flag']."&unique=".$this->params['unique']."&llimit=".$this->params['llimit']."&ulimit=".$this->params['ulimit']."".$str."";
			
			 return $res = $this->curl_call_get($curl_url);
		 }
	}
	
	
	function getComplainTypes()
	{
		$sql = "SELECT complaintype_id, complaintype_name FROM tbl_complaintype WHERE eligible_source&2=2 AND display_flag=1";
		$res = parent::execQuery($sql, $this->dbConDjds);
		if($res && mysql_num_rows($res))
		{
			$i = 0;
			while($row = mysql_fetch_assoc($res))
			{
				$complaint_list_arr[$i]['cid']   = $row['complaintype_id'];
				$complaint_list_arr[$i]['cname'] = $row['complaintype_name'];
				$i++;
			}
			
			return $complaint_list_arr;
		}
	}
	
	function LogClientComplaint($sms_email_Obj)
	{
		
		
		if(strtolower(trim($this->params['src'])) == 'cc_web')
		{
			$sql_fetch = "select complaintype_id from tbl_complaintype where complaintype_name ='".trim(urldecode($this->params['complain_name']))."'";	
			$res_fetch = parent::execQuery($sql_fetch, $this->dbConDjds);
			if($res_fetch &&  mysql_num_rows($res_fetch))
			{
				$row_fetch = mysql_fetch_assoc($res_fetch);
				if(count($row_fetch) > 0)
				$this->complain_type = $row_fetch['complaintype_id'];
			}	
			
		}
		
		 $sql_main = "Insert into log_complain_main(`parentid`,`company_Name`,`resolutionflag`,`complain_registration_date`,`registeredBy`,`complain_type`,`complain_source`,`standard_complainID`,`sub_source`,`caller_name`,`caller_number`,`comp_category_id`,`comp_category_name`,`contract_created_by`,`contract_created_by_name`,`registeredby_name`,`me_code`,`me_name`,`tme_code`,`tme_name`,`data_city`,`reason`,department_id,`cs_feedback_rating`)values('".$this->parentid."','".$this->companyname."','0',now(),'".$this->registeredbyId."','".$this->complain_type."','".$this->complain_source."','','Regular Issues','','','','','','','".$this->registeredbyName."','--','--','--','--','".$this->data_city."','','','".$this->cs_feedback_ratings."')";
		 $res_main = parent::execQuery($sql_main, $this->dbConDjds);
		 $autoid = $this->mysql_insert_id;
		 
		 
		 $sql_details = "Insert into  	log_complain_details(`complaintid`,`updated_date`,`updatedby`,`Description`,`caller_name`,`caller_number`,`dept_id`,`cs_feedback_rating`)values('".$autoid."',now(),'".$this->registeredbyId."','".addslashes($this->client_comment)."','','','','".$this->cs_feedback_ratings."')";
		 $res_details = parent::execQuery($sql_details, $this->dbConDjds);
		
		 if($res_main && $res_details && $autoid)
		 {
			 
			 $sql_idc = "SELECT * FROM online_regis1.tbl_cs_department_email";
			 $res_idc = parent::execQuery($sql_idc, $this->dbConIdc);
			 if($res_idc &&  mysql_num_rows($res_idc))
			 {
				while($row_idc = mysql_fetch_assoc($res_idc))
				{
					$cs_email_arr[$row_idc['city']][$row_idc['department_name']][$row_idc['sub_department_name']]['emailid'] = $row_idc['email_id'];
				}  
				//echo '<pre>';print_r($cs_email_arr);
			 }
			 
			 $available_depts_ct = array_keys($cs_email_arr);
			 
			 if(!in_array($this->data_city,$available_depts_ct))
			 {
				$sql_map_ct  = "SELECT mapped_cityname FROM d_jds.tbl_city_master WHERE display_flag=1 AND DE_display=1 AND type_flag IN (0,1) AND ct_name='".$this->data_city."'";
				$res_map_ct = parent::execQuery($sql_map_ct, $this->dbConDjds);
				
				if($res_map_ct &&  mysql_num_rows($res_map_ct)) {
					$row_map_ct = mysql_fetch_assoc($res_map_ct);
					$city_name  = $row_map_ct['mapped_cityname'];
				}
			 }else{
				
					$city_name = $this->data_city;
			 }
			 
			 
			  
			 $city_name = strtolower(trim($city_name));
			 $complain_type_arr_web = $this->getComplainTypes();
			 $complaint_types_ecs   = array('ECS Related Clarification','ECS Mandate Change Request','Less Leads','No Leads','Irrelevant Leads','Out of Area Leads','Complaint regarding Masked number Lead');
			 $complaint_types_omni  = array('Jd Omni - Activation Related Clarification','Jd Omni - Domain Name Related','Jd Omni - Request For Addition Of New Features In Software.','Jd Omni - B-Form Changes','Jd Omni - Software Related Issue','Jd Omni - Hardware Related Issue','Jd Omni - Mobile App Changes Related','Jd Omni - Demo Request');
			 $complaint_types_omni = array_map('strtolower',$complaint_types_omni);
			 $complaint_types_ecs  = array_map('strtolower',$complaint_types_ecs);
			 //print_r($complaint_types_omni);
			 foreach($complain_type_arr_web as $complaint_types)
			 {
				 //print_r($complaint_types);
				 $complaint_types['cname'] = strtolower($complaint_types['cname']);
				 if(trim($complaint_types['cid']) == trim($this->complain_type) && !in_array(trim($complaint_types['cname']),$complaint_types_omni))
				 {
					 
					 if(in_array(trim($complaint_types['cname']),$complaint_types_ecs))
					 {
						 $emailid = $cs_email_arr[$city_name]['cs']['retention']['emailid'] ;
					 }else if( in_array(trim($complaint_types['cname']),$complaint_types_omni))
					 {
						 $emailid = $cs_email_arr[$city_name]['cs']['omni']['emailid'] ;
					 }
					 
					 //$emailid = "raj.mittal.yadav@gmail.com";
					 $email_id_cc = "meenali.chourasia@justdial.com";
					 $source    =  "contact us - web";
					 $subject   =  "Website Complaint | ".$this->data_city." | ".$this->companyname." | ".trim($complaint_types['cname'])."";
					 $emailtext = " ".$this->companyname." has registered complaint about ".ucwords(trim($complaint_types['cname']))." - ".$this->data_city." :
								    <br>Complaint : 	".$this->client_comment."
									<br>Contract Id : 	".$this->parentid."
									<br>City : 	".$this->data_city."";
					 $res_email = $sms_email_Obj -> sendEmail($emailid, $from, $subject, $emailtext, $source, $this->parentid,$email_id_cc);
					 break;
				 }
			 }
			 
			// $city_array_list = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'jaipur','chandigarh','coimbatore');
			
			 $lead_complaint_list   = array('less leads','no leads','irrelevant leads','out of area leads');
			 if(in_array(strtolower(trim($this->complain_name)),$lead_complaint_list))
			 {
				 
				  //$curl_url   = $this->params['cs_url']."api_services/retention_api.php?parentid=".$this->params['parentid']."&city=".$this->params['data_city']."&source=web&lead=1&complain_type=".$this->complain_name."";
				 $curl_url   = $this->params['cs_url']."api_services/retention_api.php?parentid=".$this->params['parentid']."&city=".urlencode($this->params['data_city'])."&source=web&lead=1&complain_type=".urlencode($this->complain_name)."";
				 $res = $this->curl_call_get($curl_url);
				 
			 }
			 
			 
			 $errorarray['msg']='complaint registered sucessfully';
			 $errorarray['complaint_no']=$autoid;
			 
		 }else
		 {
			$errorarray['errormsg']='complaint registration failed';
			$errorarray['errorcode']='-1';
		 }
		 return $errorarray;
		 
	}
	
	function SetRadiusOfSign()
	{
		$sql_upt_pkg = "UPDATE tbl_companymaster_finance SET searchcriteria = '".$this -> searchcriteria."', radius_of_significance= '".$this -> radius_of_sign."'
					WHERE parentid='".$this->parentid."' AND campaignid in ('1')";
		$res_upt_pkg = parent::execQuery($sql_upt_pkg, $this->fin);
		
		$sql_upt_pdg = "UPDATE tbl_companymaster_finance SET searchcriteria = '".$this -> searchcriteria."' 
					WHERE parentid='".$this->parentid."' AND campaignid in ('2')";
		$res_upt_pdg = parent::execQuery($sql_upt_pdg, $this->fin);
		
		$this -> comment = " Search Page - Distance Restriction \n".$this -> comment;
		
		$curl_url   = $this->params['cs_url']."api/fetch_update_narration.php?action=2&module=cs&parentid=".$this->parentid."&ucode=".$this->params['ucode']."&uname=".urlencode($this->params['uname'])."&narration=".urlencode($this -> comment)."&data_city=".urlencode($this->data_city)."";
	    $res = $this->curl_call_get($curl_url);
			 
		
		switch(strtoupper($this->data_city))
			{
				case 'MUMBAI' :
					$city_indicator 		= "main_city";
					break;

				case 'AHMEDABAD' :
					$city_indicator = "main_city";
					break;

				case 'BANGALORE' :
					$city_indicator 		= "main_city";
					break;

				case 'CHENNAI' :
					$city_indicator		    = "main_city";
					break;

				case 'DELHI' :
					$city_indicator 		= "main_city";
					break;

				case 'HYDERABAD' :
					$city_indicator 		= "main_city";
					break;

				case 'KOLKATA' :
					$city_indicator 		= "main_city";
					break;

				case 'PUNE' :
					$city_indicator 		= "main_city";
					break;

				default:
					$city_indicator 		= "remote_city";
					break;
			}
		
		
		$curl_new_url = $this->params['cs_url']."web_services/curl_serverside.php?parentid=".$this->parentid."&ucode=".$this->params['ucode']."&uname=".urlencode($this->params['uname'])."&validationcode=SRCHTPPL&city_indicator=".$city_indicator."&data_city=".urlencode($this->data_city)."";
	    $res_new = $this->curl_call_get($curl_new_url);
		
		
		
		if($res_upt_pkg && $res_upt_pdg)
		{
			$errorarray['error'] = '0';
			$errorarray['msg']	 = 'success';
		}else
		{
			$errorarray['error'] = '1';
			$errorarray['msg']	 = 'failed';
		}
		return $errorarray;
	}

	function getInstrumentDetails()
	{
		$sql_instrument = " SELECT
								a.instrumentId, a.parentid, a.instrumentType, a.instrumentAmount, a.tdsAmount, a.version, a.app_version,a.entry_date, a.paymentType ,a.campaign_type,

								b.accountsRecievedFlag, b.accountsRecievedDate, b.bankSentFlag, b.bankSentDate, b.bankClearanceFlag, b.bankClearanceDate, b.finalApprovalFlag, b.finalApprovalDate,

								c.chequeNo, c.chequeDate,  c.bankBranch, c.bankName, c.location, c.depositDate,

								e.approvalCode,

								f.companyname,(x1.discount_percent * 100) AS discount_percent,multicity

								FROM payment_instrument_summary a JOIN

									 payment_discount_factor x1 ON (a.parentid = x1.parentid AND x1.version=IF(app_version>0,app_version,a.version) ) JOIN 

									 payment_clearance_details b ON a.instrumentid=b.instrumentid LEFT JOIN

									 payment_cheque_details c ON a.instrumentid=c.instrumentid LEFT JOIN

									 payment_cash_details d ON a.instrumentid=d.instrumentid LEFT JOIN

									 payment_cc_details e ON a.instrumentid=e.instrumentid JOIN

									 payment_otherdetails f ON a.parentid=f.parentid AND a.version=f.version

								WHERE a.parentid='".$this->parentid."' ORDER BY a.entry_date DESC";
								//;var_dump($this->fin);
		$res_instrument = parent::execQuery($sql_instrument, $this->fin);
		if($res_instrument && mysql_num_rows($res_instrument)>0)
		{
			for ( $i = 0; $i < mysql_num_fields( $res_instrument ); $i++ ) {
				$field_names[] = mysql_field_name( $res_instrument, $i );
			}
			$instruments['instrument_header'] = array_values($field_names);
			
			
			$i=1;
			while($row_instrument = mysql_fetch_assoc($res_instrument))
			{
				$instruments['instrument_values'][$i] = array_values($row_instrument);
				$i++;
			}
			
			
			// $fields = mysql_field_array( $res_instrument );
		}
		if(count($instruments)) {
			return $instruments;
		}else {
			$error['message'] ='No instrument Data';
			$error['code']	  ='-1';
			return $error;
		}
	
	}
	
	
	function getJDRRStatus()
	{
		
		$sql = "SELECT acct_clearance_date,sent_to_art_work_date,sent_to_art_work,artwork_recvd_date ,artwork_recvd_date_flag,softcopy_sent_date,dispatch_artwork_date, version_no, uploaded_file_name FROM db_jdrr_tracking.jdrr_tracker WHERE parentid='".$this->parentid."' AND data_city='".$this->data_city."'";
		$res = parent::execQuery($sql, $this->dbConIdc);
		if($res && mysql_num_rows($res))
		{
				$row = mysql_fetch_assoc($res);
				$jdrr_data['error'] = 0;
				
				if($row['version_no'])
				{
					$sql_version = "SELECT entry_date FROM payment_apportioning WHERE parentid='".$this->parentid."' AND version='".$row['version_no']."' AND campaignid='22'";
					$res_version = parent::execQuery($sql_version, $this->fin);
					if($res_version && mysql_num_rows($res_version))
					{
						$row_version = mysql_fetch_assoc($res_version);
						$jdrr_data['data']['contract_booked']	   = 1;
						$jdrr_data['data']['contract_booked_date'] = $row_version['entry_date'];
					}
				}
				
				
				if($row['acct_clearance_date'])
				{
					$jdrr_data['data']['contract_activated']     = 1;
					$jdrr_data['data']['contract_activated_date']= $row['acct_clearance_date'];
				}
				
				$jdrr_data['data']['design_done_sent']       = $row[sent_to_art_work];
				$jdrr_data['data']['design_done_sent_date']  = $row['sent_to_art_work_date'];
				
				$jdrr_data['data']['details_confirmed']		 = $row[artwork_recvd_date_flag];
				$jdrr_data['data']['details_confirmed_date'] = $row['artwork_recvd_date'];
				
				if($row['softcopy_sent_date'])
				{
					$jdrr_data['data']['given_to_printer'] 		 = 1;
					$jdrr_data['data']['given_to_printer_date']  = $row['softcopy_sent_date'];
				}
				
				if($row['dispatch_artwork_date'])
				{
					$jdrr_data['data']['given_to_courier'] 		 = 1;
					$jdrr_data['data']['given_to_courier_date']  = $row['dispatch_artwork_date'];
					$jdrr_data['data']['in_transit']	 		 = 1;
					
				}else
					$jdrr_data['data']['in_transit']	 		 = 0;
				
					$jdrr_data['data']['is_delivered'] 			 = 0;
				
					$jdrr_data['data']['jdrr_file_name']  = $row['uploaded_file_name'];
				
				
		}else{
				$jdrr_data['data'] ='No Data Availabel For JDRR Certificate';
				$jdrr_data['error']	  ='-1';
				
		}
				return $jdrr_data;
	}
	
	function UpdateAuditedCompanyGeneralData()// Update edit listing audit data
	{
		
		$sql = "SELECT * FROM tbl_companymaster_generalinfo WHERE parentid='".$this->parentid."' AND data_city='".$this->data_city."'";
		$res = parent::execQuery($sql, $this->dbConIro);
		if($res && mysql_num_rows($res))
		{
			$row    = mysql_fetch_assoc($res);
			
			$post_arr['sphinx_id']             =    $row['sphinx_id'];
			$post_arr['docid']                 =    $this->params['docid'];
			$post_arr['parentid']              =    $this->params['parentid'];
			
			if($this->params['companyname'])
			$post_arr['companyname']           =    $this->params['companyname'];
			
			if($this->params['data_city'])
			$post_arr['data_city']             =    $this->params['data_city'];
			
			if($this->params['building_name'])
			$post_arr['building_name']         =    $this->params['building_name'];
			
			
			$post_arr['country']               =    '98';
			
			if($this->params['state'])
			$post_arr['state']                 =    $this->params['state'];
			
			if($this->params['city'])
			{
				$post_arr['city']                  =    $this->params['city'];
				$post_arr['display_city']          =    $this->params['city'];
			}
			
			if($this->params['pincode'])
			$post_arr['pincode']               =    $this->params['pincode'];
			
			if($this->params['area'])
			$post_arr['area']                  =    $this->params['area'];
			
			if($this->params['street'])
			$post_arr['street']                =    $this->params['street'];
			
			if($this->params['landmark'])
			$post_arr['landmark']              =    $this->params['landmark'];

			$sql_source = "SELECT scode FROM source WHERE UPPER(TRIM(sname))='".$this->params['sname']."'";
			$res 		= parent::execQuery($sql, $this->dbConDjds);
			$row_source = mysql_fetch_assoc($res_source);
		   
			$post_arr['mainsource']    = $row_source['scode'];
			$post_arr['subsource']     = $this->params['sname'];
			$post_arr['datesource']    = date('Y-m-d H:i:s');
		   
			$comments_str = date("l jS M, Y H:i:s")."\n";
			$comments_str .= $this->params['sname']."\n";

			$sql_upt_narr = "INSERT INTO tbl_paid_narration (contractid,narration,creationDt,createdBy,parentid,data_city) VALUES ('".$post_arr['parentid']."','".addslashes($comments_str)."','".date('Y-m-d H:i:s')."','".$this->params['sname']."','".$post_arr['parentid']."','".$post_arr['data_city']."')";
			$res_upt_narr = parent::execQuery($sql_upt_narr, $this->dbConDjds);
			
			//print_r($post_arr);
			//print_r($post_arr);
			$insert_api_url = "http://".$this->jdbox_url."insert_api.php";
			
			$ch                 = curl_init();
			curl_setopt($ch, CURLOPT_URL, $insert_api_url);
			curl_setopt($ch, CURLOPT_POST      ,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS ,$post_arr);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			$resmsg = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			
			if($code == '200')
			{
				$return_array['code'] = 0;
				$return_array['msg']  = 'success';
			}else{
				$return_array['code'] = 1;
				$return_array['msg']  = 'failed';
			}
			return $return_array;
		
		}
		
		
	}
	
	function curl_call_get($curl_url)
	{	
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );

		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
	}
	

}



?>

