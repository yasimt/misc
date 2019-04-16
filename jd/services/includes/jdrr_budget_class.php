<?php

class jdrr_budget_class extends DB
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
	var  $usercode 	= null;
	

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
		if(trim($this->params['ecs_flag']) != "" && $this->params['ecs_flag'] != null)
		{
			$this->ecs_flag  = $this->params['ecs_flag']; //initialize datacity
		}
		else{
			$this->ecs_flag=0;
		}
		if(trim($this->params['user_price']) != "")
		{
			$this->user_price  = $this->params['user_price']; 
		}
		else 
			$this->user_price  =0;
		if(trim($this->params['user_price_monthly']) != "")
		{
			$this->user_price_monthly  = $this->params['user_price_monthly']; 
		}
		else
			$this->user_price_monthly  = 0;

		if(trim($this->params['combo']) != "")
		{
			$this->combo  = $this->params['combo']; 
		}
		else
			$this->combo  = 0;

		if(trim($this->params['type']) != "")
		{
			$this->type  = $this->params['type']; 
		}
		else
			$this->type  = 0;
			
		if(trim($this->params['searchText']) != "")
		{
			$this->searchText  = $this->params['searchText']; 
		}
		else
			$this->searchText  = '';
			
		if(trim($this->params['ver']) != "")
		{
			$this->ver  = $this->params['ver']; 
		}
		else
			$this->ver  = '';
		
		
			
		if($this->action == 8)
		{
			$this->version	 	= $this->params['version'];
			$this->jdrr_budget	= $this->params['jdrr_budget'];
			$this->no_certs	 	= $this->params['no_certs'];
			$this->avg_rats		= $this->params['avg_rats'];
			$this->no_rats	 	= $this->params['no_rats'];
			$this->cert_size	= $this->params['cert_size'];
			$this->outlets		= $this->params['outlets'];
			$this->address	 	= $this->params['address'];
			$this->email	    = $this->params['email'];
		}
			
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
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
		$this->data_city_main = $data_city;
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConTmeJds 		= $db[$data_city]['tme_jds']['master'];
		//$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		//$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		//$this->db_budgeting		= $db[$data_city]['db_budgeting']['master'];
		
		$this->jdrr_data_city 	= $data_city;
		
		if($data_city == 'remote')
		{
			$this->remote = 1;
		}
		
		if(DEBUG_MODE)
		{
			echo '<pre> IDc db array :: ';
			print_r($this->dbConIdc);
		}
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
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
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
	
	function get_JDRR_newBud(){
		
		$sql_jdrr_budget = "SELECT * FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
		$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->conn_temp);
			
			if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget)>0)
			{
					
					$res_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);
					$jdrr_price		=	json_decode($res_jdrr_budget['PriceList'],true);
					$jdrr_pricing[jdrr_budget] = $jdrr_price['Normal']['22']['price_upfront'];
					$jdrr_pricing[certificate_size] = '16X12';
					$jdrr_pricing[jdrr_certificates] = '1';
					$jdrr_pricing[jdrr_budget_monthly]=($jdrr_price['Normal']['22']['price_upfront']/12);
			
			}
			return $jdrr_pricing;
	}
	
	function get_JDRR_minBudget()
	{
		$city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		if($this->combo=='2' || $this->combo==2 ){
			$sql_jdrr_budget = "SELECT * FROM tbl_combo_pricing WHERE city='".$city."' and campaignid=22 and combo='combo2'";
			$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->conn_temp);
			
				if(DEBUG_MODE)
				{
					echo '<br>sql_jdrr_budget :: '.$sql_jdrr_budget;
					echo '<br>res :: '.$res_jdrr_budget;
					echo '<br>res rows :: '.mysql_num_rows($res_jdrr_budget);
				}
			if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget)>0)
			{
				$res_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);

					$jdrr_pricing[jdrr_budget] = $res_jdrr_budget['ecs_upfront'];
					$jdrr_pricing[certificate_size] = '16X12';
					$jdrr_pricing[jdrr_certificates] = '1';
					$jdrr_pricing[jdrr_budget_monthly]=($res_jdrr_budget['ecs_upfront']/12);
			
			}
		}
		else{
			if($this->remote)
			{
				$city = "remote";
				$sql_city = "SELECT city FROM online_regis_mumbai.tbl_jdrr_pricing WHERE city='".$this->data_city."'";
				$res_city = parent::execQuery($sql_city, $this->dbConIdc);
				if($res_city && mysql_num_rows($res_city)>0)
				{
					$city = $this->data_city;
				}
			}
			$sql_jdrr_budget = "SELECT * FROM online_regis_mumbai.tbl_jdrr_pricing WHERE city='".$city."'";
			$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->dbConIdc);
			
				if(DEBUG_MODE)
				{
					echo '<br>sql_jdrr_budget :: '.$sql_jdrr_budget;
					echo '<br>res :: '.$res_jdrr_budget;
					echo '<br>res rows :: '.mysql_num_rows($res_jdrr_budget);
				}
			if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget)>0)
			{
				$row_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);
			
				$jdrr_pricing[jdrr_budget] = $row_jdrr_budget['upfront_payment'];
				$jdrr_pricing[jdrr_budget_monthly] = intval($row_jdrr_budget['monthlyPayment']*12);
				$jdrr_pricing[starting_down_budget] = $row_jdrr_budget['advance_amt'];
				$jdrr_pricing[additional_down_budget] = $row_jdrr_budget['additional_amt'];
				$jdrr_pricing[certificate_size] = '16X12';
				$jdrr_pricing[jdrr_certificates] = '1';
			}
		}	

		return $jdrr_pricing;
		
	}
	
	 function getRatingDetails()
	 {
			if($this->remote)
			{
				$city = " AND city='".$this->data_city."'";
			}
			
			$sql_sel = "SELECT * FROM tbl_jdratings_sales WHERE parentid='".$this->parentid."' ".$city."";
			$res_sel = parent::execQuery($sql_sel, $this->dbConDjds);
			if($res_sel && mysql_num_rows($res_sel))
			{
				$row_sel = mysql_fetch_assoc($res_sel);
				
			}else
			{
				$row_sel['errCode'] = '-2';
				$row_sel['errMsg']  = 'Data not found';
			}
			
			$sel_banner_temp = "SELECT * FROM catspon_banner_rotation_temp WHERE parentid='" . $this->parentid . "' AND no_of_rotation > 1";		
			$res_banner_temp = parent::execQuery($sel_banner_temp, $this->conn_temp);
			
			$row_sel['banner'] = 1;
			
			if($res_banner_temp && mysql_num_rows($res_banner_temp)>0)
			$row_sel['banner'] = 0;
			
			
			$sel_jdrr_temp = "SELECT * FROM tbl_jd_reviewrating_budget_temp WHERE parentid='" . $this->parentid . "'";		
			$resjdrr_query = parent::execQuery($sel_jdrr_temp, $this->conn_temp);
			if(DEBUG_MODE)
				{
					echo '<br>sel_jdrr_temp :: '.$sel_jdrr_temp;
					echo '<br>resjdrr_query :: '.$resjdrr_query;
					echo '<br>res rows :: '.mysql_num_rows($resjdrr_query);
				}
		
			 if($resjdrr_query && mysql_num_rows($resjdrr_query)>0)
			 {
				 $rowjdrr_query = mysql_fetch_assoc($resjdrr_query);
				 
				 if($rowjdrr_query['Linked_branch_Contractid'])
				 {
					 $linked_contractiid_arr = explode(",",$rowjdrr_query['Linked_branch_Contractid']);
					 $sql_linked_sel = "SELECT * FROM tbl_jdratings_sales WHERE parentid IN ('".str_replace(",","','",$rowjdrr_query['Linked_branch_Contractid'])."') ".$city."";
					 $res_linked_sel = parent::execQuery($sql_linked_sel, $this->dbConDjds);
					 if($res_linked_sel && mysql_num_rows($res_linked_sel))
					 {
						while($row_linked_sel = mysql_fetch_assoc($res_linked_sel))
						{
							$linked_contractid_arr[$row_linked_sel['parentid']]['no_of_rating'] = $row_linked_sel['no_of_rating'];
							$linked_contractid_arr[$row_linked_sel['parentid']]['avg_rating']   = $row_linked_sel['rating'];
						}
						
					 } 
					 $sql_gen_sel = "SELECT parentid,companyname,full_address FROM db_iro.tbl_companymaster_generalinfo WHERE parentid IN ('".str_replace(",","','",$rowjdrr_query['Linked_branch_Contractid'])."') ";
					 $res_gen_sel = parent::execQuery($sql_gen_sel, $this->dbConDjds);
					 if($res_gen_sel && mysql_num_rows($res_gen_sel))
					 {
						while($row_gen_sel = mysql_fetch_assoc($res_gen_sel))
						{
							$address_arr[$row_gen_sel['parentid']]['companyname']  = $row_gen_sel['companyname'];
							$address_arr[$row_gen_sel['parentid']]['full_address'] = $row_gen_sel['full_address'];
						}
					 }
						 
					if(count($linked_contractiid_arr)>0)
					{
						$i=1;
						foreach($linked_contractiid_arr as $linked_parentid)
						{
							$final_arr[$i]['key'] 		 = $i;
							$final_arr[$i]['parentid'] = $linked_parentid;
							$final_arr[$i]['compname'] = $address_arr[$linked_parentid]['companyname'];
							$final_arr[$i]['fulladdr'] = $address_arr[$linked_parentid]['full_address'];
							$final_arr[$i]['nofrating'] = $linked_contractid_arr[$linked_parentid]['no_of_rating'];
							$final_arr[$i]['avgrating'] = $linked_contractid_arr[$linked_parentid]['avg_rating'];
							$i++;
						}
						
							$row_sel['outlets'] = $final_arr;
					}
						
				 }
			 }
			
			$row_sel['total_no_certs'] = $rowjdrr_query['no_of_certificate'];
			$row_sel['cert_size'] 	   = $rowjdrr_query['certificate_size'];
			
			
			
			
			$sql_jdrr_budget = "SELECT * FROM online_regis_mumbai.tbl_jdrr_pricing WHERE city='" . $this->jdrr_data_city . "'";
			$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->dbConIdc);
			if ($res_jdrr_budget && mysql_num_rows($res_jdrr_budget) > 0) {
				$row_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);

				$row_sel[jdrr_budget]            = $row_jdrr_budget['upfront_payment'];
				$row_sel[jdrr_budget_monthly]    = intval($row_jdrr_budget['monthlyPayment']);
				$row_sel[starting_down_budget]   = $row_jdrr_budget['advance_amt'];
				$row_sel[additional_down_budget] = $row_jdrr_budget['additional_amt'];
				$row_sel[jdrr_web_dis] = 0;
			}
			
			$row_sel['additional_banner_value'] 	   =  3999;
			$sql225="SELECT *  FROM tbl_finance_omni_flow_display WHERE campaignid='225'";
			$res225 = parent::execQuery($sql225, $this->conn_temp);
			if ($res225 && mysql_num_rows($res225) > 0) 
			{
				$row225 = mysql_fetch_assoc($res225);
				$row_sel['additional_banner_value'] 	   =  ($row225['price_upfront_actual'] - $row_sel[jdrr_budget])>0 ? ($row225['price_upfront_actual'] - $row_sel[jdrr_budget]) : 3999;
			}
			
			$bud_data = array();
			$sql225_new="SELECT *  FROM tbl_finance_omni_flow_display_new_new WHERE campaignid in ('2252','2253',225,22)";
			$res225_new = parent::execQuery($sql225_new, $this->conn_temp);
			if ($res225_new && mysql_num_rows($res225_new) > 0) 
			{
				while($row225_new = mysql_fetch_assoc($res225_new))
				{
					$bud_data[$row225_new['campaignid']] = $row225_new;
				}	
				
			}
			//print_r($bud_data);
			
			
			$sql_paytype = "SELECT payment_type,instrument_type  FROM tbl_payment_type WHERE parentid='".$this->parentid."' and version='".$this->ver."'";
			$res_paytype = parent::execQuery($sql_paytype, $this->fin);
			if ($res_paytype && mysql_num_rows($res_paytype) > 0) 
			{
				$row_paytype = mysql_fetch_assoc($res_paytype);
				if(strstr($row_paytype['payment_type'],"jdrr_web_dis") != '')
				{
					
					$row_sel['jdrr_budget'] = 3000;
					$row_sel['jdrr_web_dis'] = 1;
				}
				
				if(strstr($row_paytype['payment_type'],"jdrrplus_3years") != '')
				{
					
					$row_sel['jdrr_budget'] = $row_jdrr_budget['upfront_payment'];
					
					if($row_paytype['instrument_type'] == 'upfront')
					{
						$row_sel['additional_banner_value'] = $bud_data['2252']['price_upfront_actual'] - $row_jdrr_budget['upfront_payment'];
					}
					else if($row_paytype['instrument_type'] == 'ecs')
					{
						$row_sel['additional_banner_value'] = ($bud_data['2252']['price_ecs_actual'] * 12) - $row_jdrr_budget['upfront_payment'];
					}	
					
					//$row_sel['jdrr_OGbudget'] = $row_jdrr_budget['upfront_payment'];
					//$row_sel['additional_banner_value'] = $bud_data['2252']['price_upfront_actual'] - $row_jdrr_budget['upfront_payment'];
					//print_r($row_sel);
				}
				
				else if(strstr($row_paytype['payment_type'],"jdrrplus_10year") != '')
				{
					$row_sel['jdrr_budget'] = $row_jdrr_budget['upfront_payment'];
					
					if($row_paytype['instrument_type'] == 'upfront')
					{
						$row_sel['additional_banner_value'] = $bud_data['2253']['price_upfront_actual'] - $row_jdrr_budget['upfront_payment'];
					}
					else if($row_paytype['instrument_type'] == 'ecs')
					{
						$row_sel['additional_banner_value'] = ($bud_data['2253']['price_ecs_actual'] * 12) - $row_jdrr_budget['upfront_payment'];
					}
				}
				
				
				else if(strstr($row_paytype['payment_type'],"jdrrplus_1years") != '')
				{
					$row_sel['jdrr_budget'] = $row_jdrr_budget['upfront_payment'];
					
					if($row_paytype['instrument_type'] == 'upfront')
					{
						$row_sel['additional_banner_value'] = $bud_data['225']['price_upfront_actual'] - $row_jdrr_budget['upfront_payment'];
					}
					else if($row_paytype['instrument_type'] == 'ecs')
					{
						$row_sel['additional_banner_value'] = ($bud_data['225']['price_ecs_actual'] * 12) - $row_jdrr_budget['upfront_payment'];
					}
				}
				else if(stristr($row_paytype['payment_type'],"jdrr") != '')
				{
					$row_sel['jdrr_budget'] = $row_jdrr_budget['upfront_payment'];
					
					if($row_paytype['instrument_type'] == 'upfront')
					{
						$row_sel['additional_banner_value'] = 0;
					}
					else if($row_paytype['instrument_type'] == 'ecs')
					{
						$row_sel['additional_banner_value'] = 0;
					}
				}
					
				
			}
			return $row_sel;
		}
		
	
	function populate_JDRR_Budget()
	{
		
		$omniflag=0;
		$checkflow = "select * from campaigns_selected_status where parentid='".$this->parentid."' and campaignid=72 and selected=1";
		$checkflow_res = parent::execQuery($checkflow, $this->conn_temp);


		if(mysql_num_rows($checkflow_res)>0){
			$omniflag=1;
		}
		/*$sql_tenure = "SELECT * FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid IN (1,2,72,73) AND recalculate_flag=1 AND budget>0";
		$res_tenure = parent::execQuery($sql_tenure, $this->conn_finance_temp);
		
		if((!$res_tenure || !mysql_num_rows($res_tenure)) && $omniflag==0)
		{
			$result_msg_arr['error']['code'] = -1;
			$result_msg_arr['error']['msg'] = "No package or fixed position campaign";
			return $result_msg_arr;
		}elseif(mysql_num_rows($res_tenure)>0 || $omniflag==1){
			
			
			
			if(DEBUG_MODE)
			{
				echo '<pre>pricing details array :: ';
				print_r($jdrr_pricing);
			}
			
			$sql_total_budget = "SELECT sum(budget) as total_budget FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid IN (1,2) AND recalculate_flag=1 AND budget>0";
			$res_total_budget = parent::execQuery($sql_total_budget, $this->conn_finance_temp);
			if( ($res_total_budget && mysql_num_rows($res_total_budget) ) || $omniflag==1) 
			{
				$row_total_budget = mysql_fetch_assoc($res_total_budget);
				//$jdrr_budget = min(($row_total_budget['total_budget'] * 0.7),6000);	
								

				//if($this->remote)
				//$jdrr_pricing['jdrr_budget'] =  min(($row_total_budget['total_budget'] * 0.7),4000);
				//else
				//$jdrr_pricing['jdrr_budget'] =  min(($row_total_budget['total_budget'] * 0.7),6000);
			
			}
		}*/
		
		$cust_bugdt=0;

		if($this->module=='me' || $this->module=='ME'){
			/*$checkjdrrsql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='22'";
			$checkjdrrres = parent::execQuery($checkjdrrsql, $this->conn_temp);
			if($checkjdrrres && mysql_num_rows($checkjdrrres)>0)
	 		{
	 			while($checkjdrrrow=mysql_fetch_assoc($checkjdrrres))
				{
					
				}
				$jdrramt=$price_setup;
				$cust_bugdt=1;
			}*/
			if($this->user_price>0 && $this->ecs_flag==0){
				$price_setup=$this->user_price;
				$price_setup_monthly=ceil($this->user_price/12);
				$cust_bugdt=1;
			}
			if($this->user_price_monthly>0 && $this->ecs_flag==1){
				$price_setup_monthly=$this->user_price_monthly;
				$price_setup=$this->user_price_monthly * 12;
				$cust_bugdt=1;
			}
			if($cust_bugdt==1){
				 $sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
					 					parentid='".$this->parentid."',
					 					campaignid='22',
					 					setupfees  	= '".$price_setup."',
					 					fees  	= '".$price_setup_monthly."'
					 					ON DUPLICATE KEY UPDATE
					 					campaignid='22',
					 					setupfees  	= '".$price_setup."',
					 					fees  	= '".$price_setup_monthly."'";
				$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
			}
			
			
			$jdrr_pricing['certificate_size'] = '16X12';
			$jdrr_pricing['jdrr_certificates'] = '1';
			
		}else if($this->module=='tme' || $this->module=='TME'){
			$jdrr_pricing = $this -> get_JDRR_newBud();
		}else{
			$jdrr_pricing = $this -> get_JDRR_minBudget();
		}
		
		
		if($this->ecs_flag==1){
			if($cust_bugdt==1)
				$jdrr_pricing['jdrr_budget'] = ($price_setup_monthly * 12);			
			else if($this->combo=='2' || $this->combo==2 )
				$jdrr_pricing['jdrr_budget'] = $jdrr_pricing['jdrr_budget'];
			else
				$jdrr_pricing['jdrr_budget'] = $jdrr_pricing['jdrr_budget_monthly'];			
		}
		else{
			if($cust_bugdt==1)
				$jdrr_pricing['jdrr_budget'] = $price_setup;
			else
				$jdrr_pricing['jdrr_budget'] = $jdrr_pricing['jdrr_budget'];
		}


		
		
		$jdrr_rating_details = $this -> getRatingDetails();
		
		if(DEBUG_MODE)
		{
			echo '<pre>rating details array :: ';
				print_r($jdrr_rating_details);
			echo '<pre>pricing details array :: ';
				print_r($jdrr_pricing);
		}

		if(count($jdrr_pricing) /* && $res_tenure && mysql_num_rows($res_tenure)>0 && count($jdrr_rating_details)*/){
			
			$down_payment = $jdrr_pricing[starting_down_budget];
			
			if(strtolower($this->module) == 'cs')
			{
				$dbcondition = "db_iro.";
				$sql_shadow = "select full_address,email from  ".$dbcondition."tbl_companymaster_generalinfo_shadow where parentid ='".$this->parentid."'";
				$res_shadow = parent::execQuery($sql_shadow, $this->conn_temp);
				$row_shadow = mysql_fetch_assoc($res_shadow);
			}
			else
			{
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] 	= "full_address,email";
					$row_shadow 				= $this->mongo_obj->getData($mongo_inputs);
				}
				else{
					$dbcondition = "";
					$sql_shadow = "select full_address,email from  ".$dbcondition."tbl_companymaster_generalinfo_shadow where parentid ='".$this->parentid."'";
					$res_shadow = parent::execQuery($sql_shadow, $this->conn_temp);
					$row_shadow = mysql_fetch_assoc($res_shadow);
				}
			}			
			
			if(count($row_shadow)>0)
			{
				//$row_shadow = mysql_fetch_assoc($res_shadow);
				$full_address = $row_shadow['full_address'];
				$email 		  = $row_shadow['email'];
			}
			
			$this -> setsphinxid();
			$sql_tenure = "SELECT max(duration) as tenure,version FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid IN (1,2) AND recalculate_flag=1";
			$res_tenure = parent::execQuery($sql_tenure, $this->conn_finance_temp);
			if($res_tenure && mysql_num_rows($res_tenure)>0)
			{
				$row_tenure = mysql_fetch_assoc($res_tenure);
				if($row_tenure['tenure'])
				$tenure =$row_tenure['tenure'];
				else
				$tenure =365;
				
				$version =$row_tenure['version'];
				
			}
			$version=$this->setversion();
			//added here for proposal jdrr plus handling
			$dependant_Check	=	"SELECT * FROM tbl_payment_type WHERE parentid='".$this->parentid."' AND version='".$version."' AND (FIND_IN_SET('jdrr plus',payment_type) <> 0)";
			$Resdependant_Check = parent::execQuery($dependant_Check, $this->conn_finance_temp);
			if($Resdependant_Check && mysql_num_rows($Resdependant_Check)>0){
				$tenure	=	365;
			}
			if($version=='')
			{
				 $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Version Not Found!"; 
				return $result_msg_arr;
			}
			
			if($this->type==16 || $this->type=='16' || $this->type==17 || $this->type=='17' || $this->type==18 || $this->type=='18' || $this->type==19 || $this->type=='19'){
				$jdrrcombosql="SELECT omni_fees_plus_jdrr,omni_fees_plus_banner,tenure_upfront,tenure_ecs FROM tbl_omni_pricing WHERE city='".$this->data_city_main."' and omni_type='".$this->type."'";
				$jdrrcombores = parent::execQuery($jdrrcombosql, $this->conn_temp);
				if($jdrrcombores && mysql_num_rows($jdrrcombores)>0)
				{
					while($jdrrcomborow=mysql_fetch_assoc($jdrrcombores))
					{
						$jdrr_pricing[jdrr_budget]=$jdrrcomborow['omni_fees_plus_jdrr'];
						$tenure = $jdrrcomborow['tenure_upfront'];
					}
				}
			}
				
				
			$sql_del = "DELETE FROM tbl_jd_reviewrating_budget_temp WHERE parentid='".$this->parentid."'";
			$res_del = parent::execQuery($sql_del, $this->conn_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_del :: '.$sql_del;
				echo '<br>res_del :: '.$res_del;
			}
			
			$sql_insert = "INSERT INTO tbl_jd_reviewrating_budget_temp(parentid,budget,tenure,avg_rating,no_of_rating,certificate_size,uptDate,data_city,no_of_certificate,monthlyPayment,downPayment, multiple_branch_flag, Linked_branch_Contractid,address,email)values('".$this->parentid."','".$jdrr_pricing['jdrr_budget']."','".$tenure."','".$jdrr_rating_details['rating']."','".$jdrr_rating_details['no_of_rating']."','".$jdrr_pricing['certificate_size']."',now(),'".$this->data_city."','".$jdrr_pricing['jdrr_certificates']."','".$jdrr_pricing['jdrr_budget_monthly']."','".ceil($down_payment)."','".$branch_flag."', '".$branch_ids."','".addslashes(stripslashes($full_address))."','".$email."')";

			$res_insert = parent::execQuery($sql_insert, $this->conn_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_jdrr_budget :: '.$sql_insert;
				echo '<br>res :: '.$res_insert;
			}
			
			if($this->type!=5 && $this->type!='735' && $this->type!=11 && $this->type!='746' && $this->type!=12 && $this->type!='747'){
			$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=22,array("budget"=>$jdrr_pricing[jdrr_budget],"original_budget"=>$jdrr_pricing[jdrr_budget],"original_actual_budget"=>$jdrr_pricing[jdrr_budget],"duration"=>$tenure,"recalculate_flag"=>1,"version" =>$version));
			}
			else{
				$res_compmaster_fin_temp_insert=true;
				$res_insert=true; 

			}
			 if($res_compmaster_fin_temp_insert && $res_insert)
			 {
			 $result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			 }else{
				 $result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "Not successful";
				return $result_msg_arr;
			 }
			
		}else{
			 $result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "pricing not found for city";
				return $result_msg_arr;
		}
	}
	
	function populate_JDRR_Budget_NEW()
	{
			
			$sql_del = "DELETE FROM tbl_jd_reviewrating_budget_temp WHERE parentid='".$this->parentid."'";
			$res_del = parent::execQuery($sql_del, $this->conn_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_del :: '.$sql_del;
				echo '<br>res_del :: '.$res_del;
			}
			
			$this->tenure = $this->params['tenure'];
			
			if($this->outlets)
			$this->branch_flag =1;
			
			if($this->cert_size == 24)
			$this->cert_size = '24X18';
			else
			$this->cert_size = '16X12';
			
			
			$sql_insert = "INSERT INTO tbl_jd_reviewrating_budget_temp(parentid,budget,tenure,avg_rating,no_of_rating,certificate_size,uptDate,data_city,no_of_certificate,monthlyPayment,downPayment, multiple_branch_flag, Linked_branch_Contractid,address,email)values('".$this->parentid."','".$this->jdrr_budget."','".$this->tenure."','".$this->avg_rats."','".$this->no_rats."','".$this->cert_size."',now(),'".$this->data_city."','".$this->no_certs."','".$this->jdrr_budget_monthly."','".ceil($this->down_payment)."','".$this->branch_flag."', '".$this->outlets."','".addslashes(stripslashes($this->address))."','".addslashes($this->email)."')";

			$res_insert = parent::execQuery($sql_insert, $this->conn_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_jdrr_budget :: '.$sql_insert;
				echo '<br>res :: '.$res_insert;
			}

			
			$this -> setsphinxid();
			
			/*$sql_tenure = "SELECT max(duration) as tenure,version FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid IN (1,2)";
			$res_tenure = parent::execQuery($sql_tenure, $this->conn_finance_temp);
			if($res_tenure && mysql_num_rows($res_tenure)>0)
			{
				$row_tenure = mysql_fetch_assoc($res_tenure);
				if($row_tenure['tenure'])
				$tenure = min('365',$row_tenure['tenure']);
				else
				$tenure =365;
				
				$version =$row_tenure['version'];
				
			}*/
			$version=$this->setversion();
			if($version=='')
			{
				 $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Version Not Found!"; 
				return $result_msg_arr;
			}
			
			
			$sql_ins_temp_jdrr = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='22',
							selected  	= 1
							ON DUPLICATE KEY UPDATE
							campaignid='22',
							selected  	= 1";
			$res_del_temp_jdrr = parent::execQuery($sql_ins_temp_jdrr, $this->conn_temp);
		
			
			if($this->type!=5 && $this->type!='735'){
			$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=22,array("budget"=>$this->jdrr_budget,"original_budget"=>$this->jdrr_budget,"original_actual_budget"=>$this->jdrr_budget,"duration"=>$this->tenure,"recalculate_flag"=>1,"version" =>$version));
			}
			else{
				$res_compmaster_fin_temp_insert=true;
				$res_insert=true; 

			}
			 if($res_compmaster_fin_temp_insert && $res_insert)
			 {
			 $result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			 }else{
				 $result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "Not successful";
				return $result_msg_arr;
			 }
			
		
	}
	
	
	 function setversion()
	 {
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "version";
			$summary_version_arr = $this->mongo_obj->getData($mongo_inputs);
		}
		else{
			$summary_version_sql    ="select version from tbl_temp_intermediate where parentid='".$this->parentid."'";
			$summary_version_rs     =  parent::execQuery($summary_version_sql, $this->conn_temp);

			$summary_version_arr = mysql_fetch_assoc($summary_version_rs);
		}
		$this->version = $summary_version_arr['version'];
		return $this->version;
	 }
	 function PopulateTempCampaign(){
	 	$sql_ins_temp_jdrr = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='22',
							selected  	= 1
							ON DUPLICATE KEY UPDATE
							campaignid='22',
							selected  	= 1";
		$res_del_temp_jdrr = parent::execQuery($sql_ins_temp_jdrr, $this->conn_temp);
 		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		if($this->module=='me' || $this->module=='ME'){
		$querydel="delete from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='22'";
		$ressql = parent::execQuery($querydel, $this->conn_temp);
		}

		return $result_msg_arr;
	 }
	 function delTempCampaign(){
	 	$sql_ins_temp_jdrr = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='22',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='22',
							selected  	= 0";
		$res_del_temp_jdrr = parent::execQuery($sql_ins_temp_jdrr, $this->conn_temp);
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
	 }
	 function checkTempCampaign(){
	 		$sql= "select * from campaigns_selected_status where parentid='".$this->parentid."' and campaignid='22'";
			$res = parent::execQuery($sql, $this->conn_temp);

			if($res && mysql_num_rows($res) >0)
			{
					$status=1;
			}
			else
				$status=0;
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = $status;
			return $result_msg_arr;
	 }
	 
	 function removeJDRRCampaign()
	 {
		 $sql_ins_temp_jdrr = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='22',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='22',
							selected  	= 0";
		$res_del_temp_jdrr = parent::execQuery($sql_ins_temp_jdrr, $this->conn_temp);
		 $sql_del_temp_jdrr = "DELETE FROM tbl_jd_reviewrating_budget_temp WHERE parentid='".$this->parentid."'";
		 $res_del_temp_jdrr = parent::execQuery($sql_del_temp_jdrr, $this->conn_temp);
		 
		 if(DEBUG_MODE)
			{
				echo '<br>sql_del_temp_jdrr :: '.$sql_del_temp_jdrr;
				echo '<br>res_del_temp_jdrr :: '.$res_del_temp_jdrr;
			}
		 require_once('miscapijdaclass.php');
		 require_once('versioninitclass.php');

		 $miscapijdaclass_obj = new miscapijdaclass($this->params);
		 
		 $result = $miscapijdaclass_obj->updatefinancetempTable('22'); /*
		 $sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='22'";
		 $res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp); making it main to temp*/
		 
		 	 
		 if(DEBUG_MODE)
			{
				echo '<br>sql_del_temp_jdrr :: '.$sql_del_temp_fnc;
				echo '<br>res_del_temp_jdrr :: '.$res_del_temp_fnc;
			}
		 
		 $this->delTempCampaign();
		 if($res_del_temp_jdrr)
		 {
		 $result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		 }else{
			 $result_msg_arr['error']['code'] = -1;
			$result_msg_arr['error']['msg'] = "Not successful";
			return $result_msg_arr;
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


	function financeInsertUpdateTemp($campaignid,$camp_data) {

        if ($campaignid>0 && is_array($camp_data)) {

            $insert_str = '';
            foreach($camp_data as $column_key => $column_value) {

                $temp_str    = $column_key ."='".$column_value . "'";
                $insert_str .= (($insert_str=='') ? $temp_str : ','.$temp_str) ;
            }

                        $compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                            ". $insert_str.",
                                            sphinx_id   = '".$this->sphinx_id."',
                                            campaignid  = '".$campaignid."',
                                            parentid    = '" . $this->parentid . "'
                                            ON DUPLICATE KEY UPDATE
                                            " . $insert_str . "";//exit;
                        //echo $compmaster_fin_temp_insert;
            $res_compmaster_fin_temp_insert = parent::execQuery($compmaster_fin_temp_insert, $this->conn_finance_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_jdrr_budget :: '.$compmaster_fin_temp_insert;
				echo '<br>res :: '.$res_compmaster_fin_temp_insert;
			}
			
			return $res_compmaster_fin_temp_insert;

        }
	 
    }
    
    function PopulateMainTable()
    {		
		$sel_jdrr_temp = "SELECT * FROM tbl_jd_reviewrating_budget_temp WHERE parentid='" . $this->parentid . "'";
		
		$resjdrr_query = parent::execQuery($sel_jdrr_temp, $this->conn_temp);
		
		if(DEBUG_MODE)
			{
				echo '<br>sel_jdrr_temp :: '.$sel_jdrr_temp;
				echo '<br>resjdrr_query :: '.$resjdrr_query;
				echo '<br>res rows :: '.mysql_num_rows($resjdrr_query);
			}
	
		 if($resjdrr_query && mysql_num_rows($resjdrr_query)>0){
				$row_count_jdrr = 1;	
				//delete previous entries
				$del_jdrr = "DELETE FROM tbl_jd_reviewrating_budget WHERE parentid='" . $this->parentid . "'";
				$res_del_jdrr = parent::execQuery($del_jdrr, $this->conn_main);
				
				if(DEBUG_MODE)
				{
					echo '<br>sel_jdrr_temp :: '.$del_jdrr;
					echo '<br>resjdrr_query :: '.$res_del_jdrr;
				}
				
				$rowjdrr_query = mysql_fetch_assoc($resjdrr_query);
				
				if(DEBUG_MODE)
				{
					echo '<pre>record set :: ';
					print_r($rowjdrr_query);
				}
						
				$ins_jdrr_main = "INSERT INTO tbl_jd_reviewrating_budget SET
								 parentid     	   = '" . $rowjdrr_query['parentid'] . "',
								 budget       	   = '" . $rowjdrr_query['budget'] . "',
								 tenure       	   = '" . $rowjdrr_query['tenure'] . "',
								 avg_rating  	   = '" . $rowjdrr_query['avg_rating'] . "',
								 no_of_rating 	   = '" . $rowjdrr_query['no_of_rating'] . "',
								 no_of_certificate = '" . $rowjdrr_query['no_of_certificate'] . "',
								 monthlyPayment 	   = '" . $rowjdrr_query['monthlyPayment'] . "',
								 downPayment 	   = '" . $rowjdrr_query['downPayment'] . "',
								 certificate_size  = '" . $rowjdrr_query['certificate_size'] . "',
								 multiple_branch_flag  = '" . $rowjdrr_query['multiple_branch_flag'] . "',
								 Linked_branch_Contractid  = '" . $rowjdrr_query['Linked_branch_Contractid'] . "',
								 uptDate      	   = '" . $rowjdrr_query['uptDate'] . "',
								 data_city   	   = '" . $rowjdrr_query['data_city'] . "',
								 address            = '".addslashes(stripslashes($rowjdrr_query['address']))."',
								 email              = '".$rowjdrr_query['email']."'";
								 

			 $res_ins_jdrr_main = parent::execQuery($ins_jdrr_main, $this->conn_main);
			 
			 
		if(DEBUG_MODE)
			{
				echo '<br>ins_jdrr_main :: '.$ins_jdrr_main;
				echo '<br>res_ins_jdrr_main :: '.$res_ins_jdrr_main;
			}
			
			 if($res_ins_jdrr_main)
			 {
			 $result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			 }else{
				 $result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "Not successful";
				return $result_msg_arr;
			 }
			
		}
	 }
	 
	function populate_JDRR_Budget_FREE()
    {		
		$sel_comp = "SELECT companyname, full_address, email FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='" . $this->parentid . "'";
		
		$res_comp = parent::execQuery($sel_comp, $this->conn_main);
		
		if(DEBUG_MODE)
			{
				echo '<br>sel_jdrr_temp :: '.$sel_comp;
				echo '<br>resjdrr_query :: '.$res_comp;
				echo '<br>res rows :: '.mysql_num_rows($res_comp);
			}
	
		$sel_comp_ext = "SELECT averageRating FROM db_iro.tbl_companymaster_extradetails WHERE parentid='" . $this->parentid . "'";
		
		$res_comp_ext = parent::execQuery($sel_comp_ext, $this->conn_main);
		
		if(DEBUG_MODE)
			{
				echo '<br>sel_jdrr_temp :: '.$sel_comp_ext;
				echo '<br>resjdrr_query :: '.$res_comp_ext;
				echo '<br>res rows :: '.mysql_num_rows($res_comp_ext);
			}
		
		if($res_comp_ext && mysql_num_rows($res_comp_ext)>0)
		$row_comp_ext = mysql_fetch_assoc($res_comp_ext);
		
		$row_comp_ext = explode("~",$row_comp_ext['averageRating']);
			
		 if($res_comp && mysql_num_rows($res_comp)>0){
				
				$row_comp = mysql_fetch_assoc($res_comp);
				if(DEBUG_MODE)
				{
					echo '<pre>record set :: ';
					print_r($row_comp);
				}
				
				$del_jdrr = "DELETE FROM tbl_jd_reviewrating_budget WHERE parentid='" . $this->parentid . "'";
				$res_del_jdrr = parent::execQuery($del_jdrr, $this->conn_main);
				
				$ins_jdrr_main = "INSERT INTO tbl_jd_reviewrating_budget SET
								 parentid     	   = '" . $this->parentid . "',
								 budget       	   = '1',
								 tenure       	   = '365',
								 avg_rating  	   = '".$row_comp_ext[0]."',
								 no_of_rating 	   = '".$row_comp_ext[1]."',
								 no_of_certificate = '1',
								 monthlyPayment 	   = '0',
								 downPayment 	   = '0',
								 certificate_size  = '16X12',
								 multiple_branch_flag  = '0',
								 Linked_branch_Contractid  = '',
								 uptDate      	   = NOW(),
								 data_city   	   = '" . $this->data_city . "',
								 address            = '".addslashes(stripslashes($row_comp['full_address']))."',
								 email              = '".$row_comp['email']."'";
								 

			 $res_ins_jdrr_main = parent::execQuery($ins_jdrr_main, $this->conn_main);
			 
			 
		if(DEBUG_MODE)
			{
				echo '<br>ins_jdrr_main :: '.$ins_jdrr_main;
				echo '<br>res_ins_jdrr_main :: '.$res_ins_jdrr_main;
			}
			
			 if($res_ins_jdrr_main)
			 {
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
			 }
			 else
			 {
				 $result_msg_arr['error']['code'] = -1;
				 $result_msg_arr['error']['msg'] = "Not successful";
				 return $result_msg_arr;
			 }
			
		}
		else
		{
				$result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "No Company Data Found !";
				return $result_msg_arr;
		}
	 }
	 
	 function checkCategoriesSanity(){
	 	$catidsql = "SELECT catIds FROM tme_jds.tbl_business_temp_data WHERE contractid='".$parentid."'";
	 	$catidsqlres = $conn_local->query_sql($catidsql);
	 	
	 	$row = mysql_fetch_assoc($catidsqlres);
	 	if($row['catIds'])
	 	{
	 		$PhoneSearchCategories=$row['catIds'];
	 	}
	 	$catid = str_replace('|P|', ',', $PhoneSearchCategories);
	 	
	 	$flag=false;
		 $sql	= "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid in (".$catid.") and category_addon&8 = 8";
		$qry	= $conn_local->query_sql($sql);
		if($qry && mysql_num_rows($qry) >0){
			$flag=true;
		}
		return $flag;

	}
	
    function getJdrrAutoSuggest()
    {
		
		if($this->remote)
		{
			 $city = " AND city='".$this->data_city."'";
		}
		
		
		$sql_sel = "SELECT a.companyname,a.parentid FROM tbl_companymaster_generalinfo AS a
					JOIN
					tbl_companymaster_extradetails AS b
					ON 
					a.parentid = b.parentid
					WHERE a.companyname LIKE '".$this->searchText."%' and b.freeze=0 and b.mask=0 and a.parentid!='".$this->parentid."'  ".$city." group by a.parentid  order by companyname limit 20";
		$res_sel = parent::execQuery($sql_sel, $this->dbConIro);
		if($res_sel && mysql_num_rows($res_sel))
		{
			$i=0;
			while($row_sel = mysql_fetch_assoc($res_sel))
			{
				$autoarr[$i]['name'] = $row_sel['companyname'];
				$autoarr[$i]['id']   = $row_sel['parentid'];
				//$autoarr[$row_sel['parentid']] = $row_sel['compname'];
				$i++;
			}
			return  $autoarr;
		}
		
	}
	
}



?>
