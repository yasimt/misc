<?php
class omniBudgetClass extends DB
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
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	function __construct($params)
	{		
		$this->params = $params;		
		

		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		if(trim($this->params['parentid']) == "")
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Parentid Missing";
				echo json_encode($result_msg_arr);exit;
		}
		else
			$this->parentid  = $this->params['parentid']; 
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 
		if(trim($this->params['version']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "version Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->version  = $this->params['version']; 
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = urldecode($this->params['data_city']); 

		if(trim($this->params['ecs_flag']) != "")
		{
			$this->ecs_flag  = $this->params['ecs_flag']; 
		}
		else
			$this->ecs_flag  = 0; 


		if(trim($this->params['user_price']) != "")
		{
			$this->user_price  = $this->params['user_price']; 
		}
		if(trim($this->params['no_of_sms']) != "")
		{
			$this->no_of_sms  = $this->params['no_of_sms']; 
		}
		else
			$this->no_of_sms=0;
		if(trim($this->params['user_price_monthly']) != "")
		{
			$this->user_price_monthly  = $this->params['user_price_monthly']; 
		}
		else
			$this->user_price_monthly  = 0;

		if(trim($this->params['user_price_setup']) != "")
		{
			$this->user_price_setup  = $this->params['user_price_setup']; 
		}
		else
			$this->user_price_setup  = -1;

		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$status=$this->setServers();
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}

		if(trim($this->params['other_parameter']) != "")
		{
			$this->other_parameter  = $this->params['other_parameter']; 
		}
		else
			$this->other_parameter  = 0;
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
			$this->type  = 1;

		if(trim($this->params['tenure']) != "")
		{
			$this->tenure  = $this->params['tenure']; 
		}
		else
			$this->tenure  = 1;


		if(trim($this->params['usercode']) != "")
		{
			$this->usercode  = $this->params['usercode']; 
		}
		else
			$this->usercode  = '';

		if(trim($this->params['domain_incl']) != "")
		{
			$this->domain_incl  = $this->params['domain_incl']; 
		}
		else
			$this->domain_incl  = 0;
		if(trim($this->params['setup_exclude']) != "")
		{
			$this->setup_exclude  = $this->params['setup_exclude']; 
		}
		else
			$this->setup_exclude  = 0;
		if($this->type==1 && $this->combo==0) 
			$this->omni_duration =1520;
		else
			$this->omni_duration =365;

			$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
			
			
		if($this->params['total_sms_email_price']!=''){
			$this->total_sms_email_price = $this->params['total_sms_email_price'];
		}
		if(trim($this->params['ssl_payment_type']) != ""){
			$this->ssl_payment_type  = $this->params['ssl_payment_type']; 
		}else{
			$this->ssl_payment_type  = ""; 
		}
		if(trim($this->params['ssl_val']) != ""){
			$this->ssl_val  = $this->params['ssl_val']; 
		}else{
			$this->ssl_val  = ""; 
		}
		
		/*
			type 1 normal
			2 - 3k omni
			3- omni ultima
			4- omni supreme
		*/
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->conn_idc = $db[$data_city]['idc']['master']; 
		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['iro']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}


			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_temp_new = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			return -1;
			break;
		}

	}
	
	function getOmniPricing(){
		require_once('miscapijdaclass.php');
		require_once('versioninitclass.php');

		$miscapijdaclass_obj = new miscapijdaclass($this->params);
		
		$result = $miscapijdaclass_obj->updatefinancetempTable('72,73');    


		$dependent_campaign=0;
		$omni_combo_name=''; 
		$city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		if($this->combo=='2' || $this->combo==2 ||$this->combo=='1' || $this->combo==1 ){
			if(strtolower($this->params['module']) == 'tme'){
				$jdrr	=	array();	
				$sql_json = "SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
				$res_json = parent::execQuery($sql_json, $this->conn_temp);
				if($res_json && mysql_num_rows($res_json)>0)
				{
					$temp_jdrr_budget = mysql_fetch_assoc($res_json);
					$jdrr			  =	json_decode($temp_jdrr_budget['PriceList'],true);
					$row_jdrr_budget  =	$jdrr['Omni'][$this->params['type']];
					if($this->ecs_flag==0){
						if($this->params['type'] == '742' || $this->params['type'] == '743') {
							$omni_setup =  0;
						}else{
							$omni_setup = $row_jdrr_budget['down_payment']; 
						}
						
						$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_upfront'];
						$omni_pricing['omni_price'] = $omni_setup;
					}else{
						$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_ecs']*12;
						$omni_pricing['omni_price'] = $row_jdrr_budget['down_payment'];
					}
					
				}
					
			}else{
				$websiteprice=0;
				/*if($this->domain_incl==1 ||$this->domain_incl=='1' )
				{
					$sql_omni_budget = "SELECT budget FROM tbl_companymaster_finance_temp WHERE campaignid=74 and recalculate_flag=1 and parentid='".$this->parentid."'";
					$res_omni_budget = parent::execQuery($sql_omni_budget, $this->conn_temp);
					if($res_omni_budget && mysql_num_rows($res_omni_budget)>0)
					{
						$res_omni_budget = mysql_fetch_assoc($res_omni_budget);
						$websiteprice=$res_omni_budget['budget'];
					}
				}*/
				$sql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
				$res = parent::execQuery($sql, $this->conn_temp_new);
	
				if($res && mysql_num_rows($res) >0)
		 		{
		 			while($row=mysql_fetch_assoc($res)){
		 				$fees=$row['fees'];
		 				$domain_field_incl=$row['domain_field_incl'];
		 				$setupfees=$row['setup_fees'];
		 			}	
		 			if($this->combo==2 || $this->combo=='2'){
		 				$ratioamt=(3/7);
		 				 $price_list=ceil($fees*$ratioamt);
		 			}
		 			else if($this->combo==1 || $this->combo=='1'){
						$ratioamt=(1/2);
						//$price_list=ceil($fees*$ratioamt);
						$price_list=ceil(1);
		 			}
		 			$websiteprice=0;
		 			if($domain_field_incl>0){
			 			$sql_omni_budget = "SELECT budget FROM tbl_companymaster_finance_temp WHERE campaignid=74 and recalculate_flag=1 and parentid='".$this->parentid."'";
			 			$res_omni_budget = parent::execQuery($sql_omni_budget, $this->conn_temp);
			 			if($res_omni_budget && mysql_num_rows($res_omni_budget)>0)
			 			{
			 				$res_omni_budget = mysql_fetch_assoc($res_omni_budget);
			 				$websiteprice=$res_omni_budget['budget'];
			 			}
		 			}
		 			/*if($this->setup_exclude=='0' || $this->setup_exclude==0)
		 			$omni_pricing['omni_price'] = 20000;
		 			else
		 				$omni_pricing['omni_price'] = 0;*/
		 			$omni_pricing['omni_price'] = $setupfees; 
					//$omni_pricing['omni_price_month'] = ($price_list*12)-$websiteprice;
					$omni_pricing['omni_price_month'] = (1)-$websiteprice;
					
					if($websiteprice>0)
					{
					$sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
						 					parentid='".$this->parentid."',
						 					campaignid='72',
						 					setupfees  	= '".$omni_pricing['omni_price']."',
						 					domain_field_incl=1,
						 					fees  	= '".$omni_pricing['omni_price_month']."'
						 					ON DUPLICATE KEY UPDATE
						 					campaignid='72',
						 					setupfees  	= '".$omni_pricing['omni_price']."',
						 					domain_field_incl=1,
						 					fees  	= '".$omni_pricing['omni_price_month']."'";
					$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
					}
					else{
						$sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
							 					parentid='".$this->parentid."',
							 					campaignid='72',
							 					setupfees  	= '".$omni_pricing['omni_price']."',
							 					fees  	= '".$omni_pricing['omni_price_month']."'
							 					ON DUPLICATE KEY UPDATE
							 					campaignid='72',
							 					setupfees  	= '".$omni_pricing['omni_price']."',
							 					fees  	= '".$omni_pricing['omni_price_month']."'";
						$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
					}
	
				}else{
				
					if(strtolower($this->params['module']) == 'tme'){
						$jdrr	=	array();	
						$sql_json = "SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
						$res_json = parent::execQuery($sql_json, $this->conn_temp);
						if($res_json && mysql_num_rows($res_json)>0)
						{
							$temp_jdrr_budget = mysql_fetch_assoc($res_json);
							$jdrr			  =	json_decode($temp_jdrr_budget['PriceList'],true);
							$row_jdrr_budget  =	$jdrr['Omni'][$this->params['type']];
							//~ if($this->params['type'] == '72'){
								$omni_pricing['omni_price'] = $row_jdrr_budget['down_payment'];
							//~ }
							//~ if($this->params['type'] == '72'){
								$omni_pricing['omni_price'] = $row_jdrr_budget['price_ecs'];
							//~ }
							if($this->setup_exclude=='1' || $this->setup_exclude==1){
									$omni_pricing['omni_price'] = 0; 
							}
							
						}
					}else{
						$sql_omni_budget = "SELECT * FROM tbl_combo_pricing WHERE city='".$city."' and campaignid in (73,72) and combo='combo1'";
						$res_omni_budget = parent::execQuery($sql_omni_budget, $this->conn_temp);
						
							if(DEBUG_MODE)
							{
								echo '<br>sql_omni_budget :: '.$sql_omni_budget;
								echo '<br>res :: '.$res_omni_budget;
								echo '<br>res rows :: '.mysql_num_rows($res_omni_budget);
							}
						if($res_omni_budget && mysql_num_rows($res_omni_budget)>0)
						{	
		
							while($row_omni_budget = mysql_fetch_assoc($res_omni_budget))
							{
									
									if($row_omni_budget['campaignid']=='72')
									$omni_pricing['omni_price'] = $row_omni_budget['ecs_upfront'];
									if($row_omni_budget['campaignid']=='73')
									$omni_pricing['omni_price_month'] = ($row_omni_budget['ecs_price'])-$websiteprice;
									
							}
							if($this->setup_exclude=='1' || $this->setup_exclude==1){
								$omni_pricing['omni_price'] = 0; 
							}
						}
					}
				}
			}
		}else if($this->type == 6 || $this->type=='740' || $this->type == 7 || $this->type=='741' || $this->type == 8 || $this->type=='742' || $this->type == 9 || $this->type=='743' || $this->type == 10 || $this->type=='744' || $this->type == 14 || $this->type=='748' || $this->type == 15 || $this->type=='749'){
			
			
				if(strtolower($this->params['module']) == 'tme'){
					
					$jdrr	=	array();	
					$sql_json = "SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
					$res_json = parent::execQuery($sql_json, $this->conn_temp);
					if($res_json && mysql_num_rows($res_json)>0)
					{
						$temp_jdrr_budget = mysql_fetch_assoc($res_json);
						$jdrr			  =	json_decode($temp_jdrr_budget['PriceList'],true);
						$row_jdrr_budget  =	$jdrr['Omni'][$this->params['type']];
						if($this->ecs_flag==0){
							if($this->params['type'] == '742' || $this->params['type'] == '743') {
								$omni_setup =  0;
							}else{
								$omni_setup = $row_jdrr_budget['down_payment']; 
							}
							
							$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_upfront'];
							$omni_pricing['omni_price'] = $omni_setup;
						}else{
							$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_ecs']*12;
							$omni_pricing['omni_price'] = $row_jdrr_budget['down_payment'];
						}
						
					}
					
				}else{
					$sql="select omniextradetails from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
					$omni_res = parent::execQuery($sql, $this->dbConDjds);
					if($omni_res && mysql_num_rows($omni_res)){
						while($res = mysql_fetch_assoc($omni_res)){
							$omni_rates = json_decode($res['omniextradetails'],1);
						}
						if($this->type== 6 || $this->type=='740'){
							$omni_camp = 740;
						}else if($this->type == 7 || $this->type=='741'){
							$omni_camp = 741;
						}else if($this->type == 8 || $this->type=='742'){
							$omni_camp = 742;
						}else if($this->type == 9 || $this->type=='743'){
							$omni_camp = 743;
						}else if($this->type == 10 ||$this->type=='744'){
							$omni_camp = 744;
						}else if($this->type == 14 ||$this->type=='748'){
							$omni_camp = 748;
						}else if($this->type == 15 ||$this->type=='749'){
							$omni_camp = 749;
						}
						
						if(isset($omni_rates[$omni_camp])){
							if($this->ecs_flag==0){
								if($omni_camp == 742 || $omni_camp == 743) {
									$omni_setup =  0;
								}else{
									$omni_setup = $omni_rates[$omni_camp]['down_payment']; 
								}
								
								$omni_pricing['omni_price_month'] = $omni_rates[$omni_camp]['upfront'];
								$omni_pricing['omni_price'] = $omni_setup;
							}else{
								$omni_pricing['omni_price_month'] = $omni_rates[$omni_camp]['ecs']*12;
								$omni_pricing['omni_price'] = $omni_rates[$omni_camp]['down_payment'];
							}
							
						}
					}
				}
		}else{
			
			if(strtolower($this->params['module']) == 'tme'){
				$jdrr	=	array();	
				$sql_json = "SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
					$res_json = parent::execQuery($sql_json, $this->conn_temp);
					if($res_json && mysql_num_rows($res_json)>0)
					{
						$temp_jdrr_budget = mysql_fetch_assoc($res_json);
						$jdrr			  =	json_decode($temp_jdrr_budget['PriceList'],true);
						//~ echo '<pre>';print_r($jdrr);
						if($this->params['type']	==	'747'){
							$row_jdrr_budget  =	$jdrr['Normal'][$this->params['type']];
						}else{
							$row_jdrr_budget  =	$jdrr['Omni'][$this->params['type']];
						}
						if($this->ecs_flag==0){
							$omni_pricing['omni_price'] = $row_jdrr_budget['down_payment'];
							$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_upfront'];
							if($this->type== '734'){
								if($this->user_price_monthly>0){
									$omni_pricing['omni_price_month'] =($this->user_price_monthly);
									$omni_pricing_monthly=($this->user_price_monthly);  
								}
							}
	
							//~ $this->omni_duration= $res_omni_budget['tenure_upfront'];
						}
						else{
								
								$omni_pricing['omni_price'] = $row_jdrr_budget['down_payment'];
								$omni_pricing['omni_price_month'] = $row_jdrr_budget['price_ecs']*12;
								if($this->type== '734'){
									if($this->user_price_monthly>0){
											$omni_pricing['omni_price_month'] =($this->user_price_monthly*12); 
											$omni_pricing_monthly=($this->user_price_monthly*12); 
									}
								}
								//~ $this->omni_duration= $res_omni_budget['tenure_ecs'];
						}
						if($this->params['type']	==	'747'){ // Handling for National Listing
							
							$dependent		=		$jdrr['Normal'][10]['id_dependant'];
							$dependent_id	=	$jdrr['Normal'][10]['combo_id'];
							$dependent_campaign	=	$jdrr['Normal'][10]['dependant_campaign'];
						}else{
							$dependent		=		$jdrr['Omni'][$this->params['type']]['id_dependant'];
							$dependent_id	=	$jdrr['Omni'][$this->params['type']]['combo_id'];
							$dependent_campaign	=	$jdrr['Omni'][$this->params['type']]['dependant_campaign'];
						}
					}
			}else{
				$sql_omni_budget = "SELECT * FROM tbl_omni_pricing WHERE city='".$city."' and omni_type='".$this->type."'";
				$res_omni_budget = parent::execQuery($sql_omni_budget, $this->conn_temp);
				
					if(DEBUG_MODE)
					{
						echo '<br>sql_omni_budget :: '.$sql_omni_budget;
						echo '<br>res :: '.$res_omni_budget;
						echo '<br>res rows :: '.mysql_num_rows($res_omni_budget);
					}
					$dependent_campaign=0;
				if($res_omni_budget && mysql_num_rows($res_omni_budget)>0)
				{
					$res_omni_budget = mysql_fetch_assoc($res_omni_budget);
	
					if($this->ecs_flag==0){
					$omni_pricing['omni_price'] = $res_omni_budget['omni_fees_upfront'];
					$omni_pricing['omni_price_month'] = $res_omni_budget['omni_monthly_fees'];
						if($this->type==2){
							if($this->user_price_monthly>0){
									$omni_pricing['omni_price_month'] =($this->user_price_monthly);
									$omni_pricing_monthly=($this->user_price_monthly);  
							}
						}
	
						$this->omni_duration= $res_omni_budget['tenure_upfront'];
					}
					else{
							$omni_pricing['omni_price'] = $res_omni_budget['omni_fees_ecs'];
							$omni_pricing['omni_price_month'] = $res_omni_budget['omni_monthly_fees_ecs'];
							if($this->type==2){
								if($this->user_price_monthly>0){
										$omni_pricing['omni_price_month'] =($this->user_price_monthly*12); 
										$omni_pricing_monthly=($this->user_price_monthly*12); 
								}
							}
							$this->omni_duration= $res_omni_budget['tenure_ecs'];
					}
	
					$dependent_campaign=$res_omni_budget['dependent_campaign'];
					$dependent=$res_omni_budget['dependent'];
					$dependent_id=$res_omni_budget['dependent_id'];
					$omni_combo_name=$res_omni_budget['omni_combo_name'];
					
				}
			}
		}
			if(($this->type==2 || $this->type=='734')&& $this->user_price_monthly>0){
				$omni_pricing_monthly=$omni_pricing_monthly/12;
			$sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_combo_budget set
						 					parentid='".$this->parentid."',
						 					version='".$this->version."',
						 					combo='2',
						 					setup_fees='0',
						 					fees  	= '".$omni_pricing_monthly."'
						 					ON DUPLICATE KEY UPDATE
						 					combo='2',
						 					setup_fees='0',
						 					fees  	= '".$omni_pricing_monthly."'";
			$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		}
		if($dependent==1 || $dependent=='1' ){
			
 			$sqldel="delete from dependant_campaign_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
 			$resdeldep = parent::execQuery($sqldel, $this->conn_temp);
 			
 			
 			
 			if($dependent_campaign=='73' || $dependent_campaign==73){
 				
 				//~ $omni_pricing['omni_price'] = 0; 
 				$omni_pricing['omni_price_month'] =0; 
 				if($this->type==1)
					$omni_pricing['omni_price'] =0; 
 			}

 			$twoyrs=0;
 			$sqlgetfinancetype="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."' and find_in_set('combo1_2yr_dis',payment_type) <> 0" ;
 			$getfinancetype = parent::execQuery($sqlgetfinancetype, $this->conn_finance);

 				if($getfinancetype && mysql_num_rows($getfinancetype)>0){
 					$twoyrs=1;
 				}

			$selsql="select * from  online_regis.dependent_campaigns_master where combo_id='".$dependent_id."'and city='".$city."'";
			$res_dept = parent::execQuery($selsql, $this->conn_idc);
			if($res_dept && mysql_num_rows($res_dept)>0)
			{
				while($row_dept=mysql_fetch_assoc($res_dept)){ 
				
				 $customflag=$this->checkCustom();
				 if($customflag['flag']['val']==1 && ($row_dept['dependent_campaign'] !='13' && $row_dept['dependent_campaign'] !='5' && $row_dept['dependent_campaign'] !='22' )){   
				 	$row_dept['dependent_campaign_upfront_value']=($customflag['data']['val']*12);
				 	$row_dept['dependent_campaign_ecs_value']=($customflag['data']['val']*12);  
				 }
				 if($customflag['flag']['val']==1){
 					$omni_pricing['omni_price'] = $customflag['data']['setup']; 
 				}
				 $dept_cost=0;
				 $dept_duration=0;
				
				 if($this->ecs_flag==0){
				 	$dept_cost=$row_dept['dependent_campaign_upfront_value'];
				 	$dept_duration=$row_dept['dependent_campaign_upfront_tenure'];
				 	$this->omni_duration=$row_dept['primary_campaign_upfront_tenure'];
				 }
				 else{
				 	$dept_cost=$row_dept['dependent_campaign_ecs_value'];
				 	$dept_duration=$row_dept['dependent_campaign_ecs_tenure'];
				 	$this->omni_duration=$row_dept['primary_campaign_ecs_tenure']; 
				 }
				 if($twoyrs==1){
				 	 $dept_duration=$row_dept['dependent_campaign_upfront_tenure'] *2; 
				 }	

				 $sql_ins_temp_omni = "INSERT INTO dependant_campaign_details_temp set
					 					parentid='".$this->parentid."',
					 					version  	= '".$this->version."',
					 					pri_campaignid='".$row_dept['primary_campaign']."',
					 					combo_type  	= '".$row_dept['combo_name']."',
					 					dep_campaignid  = '".$row_dept['dependent_campaign']."',
					 					dep_budget  	= '".$dept_cost."',
					 					dep_duration  	= '".$dept_duration."'
					 					ON DUPLICATE KEY UPDATE
					 					version  	= '".$this->version."',
					 					pri_campaignid='".$row_dept['primary_campaign']."',
					 					combo_type  	= '".$row_dept['combo_name']."',
					 					dep_campaignid  = '".$row_dept['dependent_campaign']."',
					 					dep_budget  	= '".$dept_cost."',
					 					dep_duration  	= '".$dept_duration."'";
				$res_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);


				}
			}

		}
		else{
			if(!isset($this->params['dependent']) || $this->params['dependent'] != 1){ 
			$delsql="delete from dependant_campaign_details_temp where parentid='".$this->parentid."'";
			$del_omnni = parent::execQuery($delsql, $this->conn_temp);
			} 
		}
		$sql_ext_temp = "INSERT INTO tbl_omni_extradetails_temp set
		 		 					parentid='".$this->parentid."',
		 		 					omni_type='".$this->type."'
		 		 					ON DUPLICATE KEY UPDATE
		 		 					omni_type='".$this->type."'";
		$sql_ext_res = parent::execQuery($sql_ext_temp, $this->conn_temp);
		
		return $omni_pricing; 
	}
	
	
	
	function checkCustom(){
			
			$retarr=array();
			$sqlcustom="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."' and combo='".$this->type."'";
			$sqlcustomres = parent::execQuery($sqlcustom, $this->conn_temp);
			if($sqlcustomres && mysql_num_rows($sqlcustomres)>0){
				while($sqlcustomrow=mysql_fetch_assoc($sqlcustomres)){
					$retarr['flag']['val']=1;
					 $retarr['data']['val']=$sqlcustomrow['fees']; 
					 $retarr['data']['setup']=$sqlcustomrow['setup_fees']; 

					  
				}
			}
			else{
				$retarr['flag']['val']=0; 

			}

			if($this->type=='4' && $retarr['flag']['val']==0){

			$banner_sql="select combodiscount,comboextradetails  from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'"; 

						$banner_res = parent::execQuery($banner_sql, $this->dbConIro);
						
						if($banner_res && mysql_num_rows($banner_res))
						{
							
							while($banner_row=mysql_fetch_assoc($banner_res))
							{
								$comboextradetails=$banner_row['comboextradetails']; 
								$combodiscount=$banner_row['combodiscount']; 
								
							}
							
							$comboextradetails=json_decode($comboextradetails,1);
							
								$roww='731';
								if(isset($comboextradetails[$roww])) {

								$price=$comboextradetails[$roww]['combo_upfront'];
								//$datarr[$type][$roww]['price_ecs']=$comboextradetails[$roww]['combo_ecs'];
								$retarr['flag']['val']=1;
								 $retarr['data']['val']=$price/12; 
								 $retarr['data']['setup']=0; 
								return $retarr;   
							}
					}
			}

			
			return $retarr;  
	}
	function addOmni(){

		$omni_pricing=0;
		$ecs_flag=0;

		//$ecs_flag=$this->ecs_flag ;
		$getecsinfo="select * from campaigns_selected_status  where parentid='".$this->parentid."' and ecs_flag='ecs'";
		$ecsinfores = parent::execQuery($getecsinfo, $this->conn_temp);
		if($ecsinfores && mysql_num_rows($ecsinfores)>0){
			$ecs_flag=1;
		}
		if($ecs_flag==1){
			$this->omni_duration=365;
		}else if($this->type == 14 || $this->type=='748'){
			$this->omni_duration=3650;
		}else if($this->type == 15 || $this->type=='749'){
			$this->omni_duration=1095;
		}
		if($this->user_price!='' && trim($this->user_price!='' )  && $this->combo==0){

		/*
			 if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
			
				 
				 $omni_pricing_arr_org=$this->getOmniPricing();
				 $omni_pricing_org=$omni_pricing_arr_org['omni_price'];
				 $omni_pricing_monthly_org=$omni_pricing_arr_org['omni_price_month'];
				 
				 if($ecs_flag==0){
				 	 $omni_check=$this->user_price;
				 	 $omni_pricing_org=$omni_pricing_arr_org['omni_price'] + $omni_pricing_arr_org['omni_price_month'];
				 	 $omni_user_monthly=$omni_pricing_monthly_org;
				 }
				 else{
				 	$omni_check=$this->user_price;
				 	$omni_user_monthly=($this->user_price_monthly  * 12);
				 }
				 
				 if($omni_pricing_org !=  $omni_check || $omni_pricing_monthly_org != $omni_user_monthly){
					 if($ecs_flag==0){
					 	if(intval($this->user_price)<1000){
						 	 $result_msg_arr['error']['code'] = 1;
						 	$result_msg_arr['error']['msg'] = "Omni Amount Less Than 1000";
						 	return $result_msg_arr;
					 	}
					 	if($this->user_price>20000){
						 	$omni_pricing=$this->user_price;
						 	$omni_pricing_monthly=$omni_pricing-20000;
						 	$omni_pricing=20000;
					 	}
					 	else{
					 		$omni_pricing=$this->user_price;
					 		$omni_pricing_monthly=0;
					 	}
					 }
					 else{
					 	if(intval($this->user_price_monthly)==0){
						 	 $result_msg_arr['error']['code'] = 1;
						 	$result_msg_arr['error']['msg'] = "Monthly Amount Not Found";
						 	return $result_msg_arr;
					 	}
					 	if(intval($this->user_price)<100){
						 	 $result_msg_arr['error']['code'] = 1;
						 	$result_msg_arr['error']['msg'] = "Omni Amount Less Than 100";
						 	return $result_msg_arr;
					 	}

					 	$omni_pricing=$this->user_price;
					 	$omni_pricing_monthly=($this->user_price_monthly *12);
					 }
					 $sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
						 					parentid='".$this->parentid."',
						 					campaignid='72',
						 					setupfees  	= '".$omni_pricing."',
						 					fees  	= '".$omni_pricing_monthly."'
						 					ON DUPLICATE KEY UPDATE
						 					campaignid='72',
						 					setupfees  	= '".$omni_pricing."',
						 					fees  	= '".$omni_pricing_monthly."'";
					$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
				}
				else{
					 $omni_pricing_arr=$this->getOmniPricing();
					 $omni_pricing=$omni_pricing_arr['omni_price'];
					 $omni_pricing_monthly=$omni_pricing_arr['omni_price_month'];
					if($this->params['module']=='me'){
					 $querydel="delete from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
						$ressql = parent::execQuery($querydel, $this->conn_temp);
					}
				}
			}

			else{
				 $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Custom Budget Offer";
				return $result_msg_arr;
			}
		*/
			//$omni_pricing_arr=$this->getOmniPricing();
			 $omni_pricing_arr=$this->getOmniPricing();
			 if($this->user_price_setup==-1 || $this->user_price_setup=='-1'){
			 	$this->user_price_setup=$omni_pricing_arr['omni_price'];
			 }
			 if($this->user_price_setup==0){
			 	$this->user_price_setup=0; 
			 } 
			if($ecs_flag==0){ 

			$omni_pricing=$this->user_price_setup;
			//~ if($this->user_price<=0){
				 //~ $result_msg_arr['error']['code'] = 1;
				//~ $result_msg_arr['error']['msg'] = "Price Cant be zero";
				//~ return $result_msg_arr;
			//~ }	
				
				$omni_pricing_monthly=($this->user_price ); 
			} 
			else{
				$omni_pricing=$this->user_price_setup; 
				//~ if($this->user_price_monthly<=0){
				 //~ $result_msg_arr['error']['code'] = 1;
				//~ $result_msg_arr['error']['msg'] = "Monthly Fees Cant be zero"; 
				//~ return $result_msg_arr;
				//~ } 
				$omni_pricing_monthly=($this->user_price_monthly*12); 
			}
			$sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
				 					parentid='".$this->parentid."',
				 					campaignid='72',
				 					setupfees  	= '".$omni_pricing."',
				 					fees  	= '".$omni_pricing_monthly."'
				 					ON DUPLICATE KEY UPDATE
				 					campaignid='72',
				 					setupfees  	= '".$omni_pricing."',
				 					fees  	= '".$omni_pricing_monthly."'";
			$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);    


		}
		else{

			 $omni_pricing_arr=$this->getOmniPricing();
			 $omni_pricing=$omni_pricing_arr['omni_price'];
			 $omni_pricing_monthly=$omni_pricing_arr['omni_price_month'];
		}
		$addcamp=1;
		/*
		$getprevamt="select * from payment_snapshot where parentid='".$this->parentid."' and campaignId in (72,73) and app_amount>0";
		$getpreres = parent::execQuery($getprevamt, $this->conn_finance);

		if($getpreres && mysql_num_rows($getpreres)>0){
			$addcamp=0;
		}
		*/
		
		
		if($addcamp==1 && $omni_pricing>0){
		
		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=72,array("budget"=>$omni_pricing,"original_actual_budget"=>$omni_pricing,"original_budget"=>$omni_pricing,"duration"=>$this->omni_duration,"recalculate_flag"=>1,"version" =>$this->version));
		
 
		}
		else{
			/*$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='72'";
			$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);*/  
		}
		if($omni_pricing_monthly>0){ 
		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=73,array("budget"=>$omni_pricing_monthly,"original_budget"=>$omni_pricing_monthly,"original_actual_budget"=>$omni_pricing_monthly,"duration"=>$this->omni_duration,"recalculate_flag"=>1,"version" =>$this->version));
		}
		else
			$res_compmaster_fin_temp_insert=true;

		 if($res_compmaster_fin_temp_insert )
		 {
			 $result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
		   }else{
				 $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Not successful";
				return $result_msg_arr;
		 }
		
	}
	function deleteOmni(){
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='72',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='72',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='73',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='73',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='74',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='74',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='75',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='75',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='82',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='82',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='83',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='83',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='84',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='84',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='86',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='86',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		require_once('miscapijdaclass.php');
		require_once('versioninitclass.php');

		$miscapijdaclass_obj = new miscapijdaclass($this->params);
		 $result = $miscapijdaclass_obj->updatefinancetempTable('72,73,74,75,82,83,84,86');  
		$financetype='';
	$sqlgetfinancetype="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'" ;
	$getfinancetype = parent::execQuery($sqlgetfinancetype, $this->conn_finance);

		if($getfinancetype && mysql_num_rows($getfinancetype)>0){
			while($getfinancetyperow=mysql_fetch_assoc($getfinancetype)){
				$financetype=$getfinancetyperow['payment_type'];
			}
		}
		
		$financetype=explode(',', $financetype);
		$key=array_search("omni",$financetype);
		unset($financetype[$key]);
		$financetype=implode(',', $financetype);
		$updtfintype="update tbl_payment_type set payment_type ='".$financetype."' where parentid='".$this->parentid."' and version='".$this->version."'" ;
		$resss = parent::execQuery($updtfintype, $this->conn_finance); 
	/*	$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='72'";
		$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp);
		$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='73'";
		$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp);
*/
		$sql_del_temp_fnc = "DELETE FROM dependant_campaign_details_temp WHERE parentid='".$this->parentid."' and version='".$this->version."'"; 

		$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_temp);

		if(DEBUG_MODE)
		{
			echo '<br>sql_omni_budget :: '.$sql_del_temp_fnc;
			echo '<br>res :: '.$res_del_temp_fnc;
		}
		if($res_del_temp_fnc )
		 {
			 $result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
		   }else{
				 $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Not successful";
				return $result_msg_arr;
		 }
	}
	function emailIdCheckForWebsite(){
		$email='';
		$contact_person='';
		$mobile='';
		$area='';
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
			$mongo_inputs['fields'] 	= "email,contact_person,mobile,area";
			$sqlgetemailrow 			= $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlgetemail="select email,contact_person,mobile,area from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
			$sqlgetemailres = parent::execQuery($sqlgetemail, $this->conn_temp_new);
			$sqlgetemailrow=mysql_fetch_assoc($sqlgetemailres);
		}
		
	 	//$sqlgetemail="select email,contact_person,mobile,area from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
		//$sqlgetemailres = parent::execQuery($sqlgetemail, $this->conn_temp_new);
		//if($sqlgetemailres && mysql_num_rows($sqlgetemailres)>0)
		if(count($sqlgetemailrow)>0)
 		{
	 		//while($sqlgetemailrow=mysql_fetch_assoc($sqlgetemailres))
			//	{
					$email=trim($sqlgetemailrow['email']);
					$contact_person=trim($sqlgetemailrow['contact_person']);
					$mobile=trim($sqlgetemailrow['mobile']);
					$area=trim($sqlgetemailrow['area']);
			//	}
			$str='';
			$flag=0;
			if(trim($email)==''){
				$str='Email';
				$flag=1;
			}
			if(trim($contact_person)==''){
				$str.=', Contact Person';
				$flag=1;
			}
			if(trim($mobile)==''){
				$str.=', Mobile';
				$flag=1;
			}
			/*if(trim($area)==''){
				$str.=', area';
				$flag=1;
			}*/

			if($flag==0){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Email ID Found!";
				
				$result_msg_arr['data']['email']=$email;
				$result_msg_arr['data']['contact_person']=$contact_person;
				$result_msg_arr['data']['mobile']=$mobile;
				$result_msg_arr['data']['area']=$area;
				echo json_encode($result_msg_arr);exit;
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = $str." Not Found!";
				$result_msg_arr['data']['email']=$email;
				$result_msg_arr['data']['contact_person']=$contact_person;
				$result_msg_arr['data']['mobile']=$mobile;
				$result_msg_arr['data']['area']=$area;
				echo json_encode($result_msg_arr);exit;
			}
		}
		else{
			
			$result_msg_arr['error']['code'] = 3;
			$result_msg_arr['error']['msg'] = "Error occurred!";
			$result_msg_arr['data']['email']=$email;
			$result_msg_arr['data']['contact_person']=$contact_person;
			$result_msg_arr['data']['mobile']=$mobile;
			$result_msg_arr['data']['area']=$area;
			echo json_encode($result_msg_arr);exit;
		}
	}
	 function PopulateTempCampaign(){
	 	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='72',
							selected  	= 1
							ON DUPLICATE KEY UPDATE
							campaignid='72',
							selected  	= 1";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='73',
							selected  	= 1
							ON DUPLICATE KEY UPDATE
							campaignid='73',
							selected  	= 1";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
			
			if((($this->user_price!='' && $this->user_price!=0) || ($this->user_price_monthly!='' && $this->user_price_monthly!=0))  && $this->combo==0){
				$omni_pricing_arr=$this->getOmniPricing();
				$omni_pricing=$this->user_price_setup;
				if($this->user_price_setup==-1 || $this->user_price_setup=='-1'){
					$omni_pricing=$omni_pricing_arr['omni_price'];
				}
				if($this->user_price_setup==0){
					$omni_pricing=0;  
				} 

				if($this->ecs_flag==0){
					$omni_pricing_monthly=($this->user_price );
				}
				else{
					$omni_pricing_monthly=($this->user_price_monthly * 12);
				}
 
				 $sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
					 					parentid='".$this->parentid."',
					 					campaignid='72',
					 					setupfees  	= '".$omni_pricing."',
					 					fees  	= '".$omni_pricing_monthly."'
					 					ON DUPLICATE KEY UPDATE
					 					campaignid='72',
					 					setupfees  	= '".$omni_pricing."',
					 					fees  	= '".$omni_pricing_monthly."'";
				$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);  


			}
			else{
				$querydel="delete from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
				$ressql = parent::execQuery($querydel, $this->conn_temp); 
			}
			

		}
		
 		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		
		return $result_msg_arr;
	 }
	 function getDependentPackageDetails(){
		if(($this->type == 5 || $this->type == 11 || $this->type == 12) && $this->data_city_cm == 'remote'){
			$checkcitysql ="SELECT (omni_monthly_fees+omni_fees_upfront) as total_fees  FROM tbl_omni_pricing WHERE omni_type='".$this->type."' and city='".$this->data_city."'";
			$checkcitysqlres = parent::execQuery($checkcitysql, $this->conn_temp);
			if($checkcitysqlres && mysql_num_rows($checkcitysqlres)>0){
				$omni_city = $this->data_city;
			}else {
				$omni_city = $this->data_city_cm;
			}
		}else {
			$omni_city = $this->data_city_cm;
		}
		
		$sql_omni_budget = "SELECT * FROM tbl_omni_pricing WHERE city='".$omni_city."' and omni_type='".$this->type."'";
		$res_omni_budget = parent::execQuery($sql_omni_budget, $this->conn_temp);
		
		$dependent_campaign=0;
		if($res_omni_budget && mysql_num_rows($res_omni_budget)>0)
		{
			$res_omni_budget = mysql_fetch_assoc($res_omni_budget);
			$dependent_campaign=$res_omni_budget['dependent']; 
			$dependent_id=$res_omni_budget['dependent_id']; 
			
			
		}
		
		$pack_flag=0;
		$pack_val=0;
		$tenure=0;
		if($dependent_campaign==1 || $dependent_campaign=='1' ){
		
			$selsql="select * from  online_regis.dependent_campaigns_master where combo_id='".$dependent_id."'and city='".$omni_city."'";
			$res_dept = parent::execQuery($selsql, $this->conn_idc);
			if($res_dept && mysql_num_rows($res_dept)>0)
			{
				while($row_dept=mysql_fetch_assoc($res_dept)){
					$customflag=$this->checkCustom();

					$pack_flag=$row_dept['primary_campaign']=='1'?1:0;
					if($pack_flag){
						$pack_val=$row_dept['primary_campaign_upfront_value'];
						$tenure=(($row_dept['primary_campaign_upfront_tenure']/365) * 12);
						if($customflag['flag']['val']==1){
							$pack_val=($customflag['data']['val']*12); 
						}
					}
				}

			}
		}
		$twoyrs=0;
		$sqlgetfinancetype="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."' and find_in_set('combo1_2yr_dis',payment_type) <> 0" ;
		$getfinancetype = parent::execQuery($sqlgetfinancetype, $this->conn_finance);
		if($getfinancetype && mysql_num_rows($getfinancetype)>0){
			$twoyrs=1;
		}
		if($twoyrs==1)
			$tenure*=2;
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		$pack_array['package_needed']=$pack_flag;
		$pack_array['package_cost']=$pack_val;
		$pack_array['package_tenure']=$tenure;
		$result_msg_arr['package'] = $pack_array;
		return $result_msg_arr;
	 }
	 function delTempCampaign(){
	 	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='72',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='72',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='73',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='73',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='74',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='74',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='75',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='75',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='82',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='82',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
							parentid='".$this->parentid."',
							campaignid='83',
							selected  	= 0
							ON DUPLICATE KEY UPDATE
							campaignid='83',
							selected  	= 0";
		$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
		$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid in ('74','75,'82','83')";
		$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp); 

		/*$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='75'";
		$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp); */

		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
	 }
		function financeInsertUpdateTemp($campaignid,$camp_data) {

	        $this -> setsphinxid();
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
					echo '<br>sql_omni_budget :: '.$compmaster_fin_temp_insert;
					echo '<br>res :: '.$res_compmaster_fin_temp_insert;
				}
				
				return $res_compmaster_fin_temp_insert;

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
	    function checkUserPrv(){

	    	if(trim($this->usercode)==''){																			$result_msg_arr['error']['code'] = 1;
    				$result_msg_arr['error']['msg'] = "Usercode Required";
    				return $result_msg_arr;
    				exit;

	    	}
	    	$sql="select * from online_regis.extra_prev_user where empcode='".$this->usercode."' and remove72=1";

	    	$res = parent::execQuery($sql, $this->conn_idc);
	    	if($res && mysql_num_rows($res)>0 )
	    	{
		 		$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Show Flag";
				return $result_msg_arr;
				exit;
	    	}
	    	else{
    		 		$result_msg_arr['error']['code'] = 1;
    				$result_msg_arr['error']['msg'] = "Dont Show Flag";
    				return $result_msg_arr;
    				exit;
	    	}

	    }
	    function addIosCampaignTemp(){
	    	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
	    						parentid='".$this->parentid."',
	    						campaignid='75',
	    						selected  	= 1
	    						ON DUPLICATE KEY UPDATE
	    						campaignid='75',
	    						selected  	= 1";
	    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
	    	if($res_del_temp_omni){
	    			$result_msg_arr['error']['code'] = 0;
    				$result_msg_arr['error']['msg'] = "Success";
    				return $result_msg_arr;
    				exit;
	    	}
	    	else{
		    			$result_msg_arr['error']['code'] = 1;
	    				$result_msg_arr['error']['msg'] = "Error";
	    				return $result_msg_arr;
	    				exit;
	    	}
	    }
	    function addAndroidCampaignTemp(){
	    	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
	    						parentid='".$this->parentid."',
	    						campaignid='84',
	    						selected  	= 1
	    						ON DUPLICATE KEY UPDATE
	    						campaignid='84',
	    						selected  	= 1";
	    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
	    	if($res_del_temp_omni){
	    			$result_msg_arr['error']['code'] = 0;
    				$result_msg_arr['error']['msg'] = "Success";
    				return $result_msg_arr;
    				exit;
	    	}
	    	else{
		    			$result_msg_arr['error']['code'] = 1;
	    				$result_msg_arr['error']['msg'] = "Error";
	    				return $result_msg_arr;
	    				exit;
	    	}
	    }
	    function deleteIosCampaignTemp(){
	    	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
	    						parentid='".$this->parentid."',
	    						campaignid='75',
	    						selected  	= 0
	    						ON DUPLICATE KEY UPDATE
	    						campaignid='75',
	    						selected  	= 0";
	    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
	    	if($res_del_temp_omni){
	    			$result_msg_arr['error']['code'] = 0;
    				$result_msg_arr['error']['msg'] = "Success";
    				return $result_msg_arr;
    				exit;
	    	}
	    	else{
		    			$result_msg_arr['error']['code'] = 1;
	    				$result_msg_arr['error']['msg'] = "Error";
	    				return $result_msg_arr;
	    				exit;
	    	}
	    }
	    function deleteAndroidTemp(){
	    	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
	    						parentid='".$this->parentid."',
	    						campaignid='84',
	    						selected  	= 0
	    						ON DUPLICATE KEY UPDATE
	    						campaignid='84',
	    						selected  	= 0";
	    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
	    	if($res_del_temp_omni){
	    			$result_msg_arr['error']['code'] = 0;
    				$result_msg_arr['error']['msg'] = "Success";
    				return $result_msg_arr;
    				exit;
	    	}
	    	else{
		    			$result_msg_arr['error']['code'] = 1;
	    				$result_msg_arr['error']['msg'] = "Error";
	    				return $result_msg_arr;
	    				exit;
	    	}
	    }
	    function addIosCampaign(){
		$rates_arr = json_decode($this->uploadrates(),1);
		 
		  if($rates_arr['error']['code'] == 1) {
			    $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "price not found";
				echo json_encode($result_msg_arr);
				exit;
		  }else {
				$rates_arr = json_decode($rates_arr['data']['price']['omniextradetails'],1);
				if(isset($rates_arr['743'])){
					$price=$rates_arr['743']['upfront'];
		      		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=75,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));
				}
				
				if($res_compmaster_fin_temp_insert){
	      	 		$result_msg_arr['error']['code'] = 0;
	      			$result_msg_arr['error']['msg'] = "Success";
	      			echo json_encode($result_msg_arr);
					exit;
				}
				else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error";
					echo json_encode($result_msg_arr);
					exit;
				}
	      }
		  
	     
	     
	    }
	    function addandroidtemplate(){
			$rates_arr = json_decode($this->uploadrates(),1);
		 
		  if($rates_arr['error']['code'] == 1) {
			    $result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "price not found";
				echo json_encode($result_msg_arr);
				exit;
		  }else {
				$rates_arr = json_decode($rates_arr['data']['price']['omniextradetails'],1);
				if(isset($rates_arr['742'])){
					$price=$rates_arr['742']['upfront'];
		      		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=84,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));
				}
				
				if($res_compmaster_fin_temp_insert){
	      	 		$result_msg_arr['error']['code'] = 0;
	      			$result_msg_arr['error']['msg'] = "Success";
	      			echo json_encode($result_msg_arr);
					exit;
				}
				else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error";
					echo json_encode($result_msg_arr);
					exit;
				}
	      }
	
	    }
	    
	    function deleteIosCampaign(){
	    	$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='75'";
	    	$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);
	    	if($res_del_temp_fnc_new){
	      	 		$result_msg_arr['error']['code'] = 0;
	      			$result_msg_arr['error']['msg'] = "Success";
	      			echo json_encode($result_msg_arr);
	      			exit;
	      		}
		      else{
		      	 		$result_msg_arr['error']['code'] = 1;
		      			$result_msg_arr['error']['msg'] = "Error";
		      			echo json_encode($result_msg_arr);
		      			exit;
		      } 
	    }
	    
	     function deleteAndroidCampaign(){
	    	$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='84'";
	    	$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);
	    	if($res_del_temp_fnc_new){
	      	 		$result_msg_arr['error']['code'] = 0;
	      			$result_msg_arr['error']['msg'] = "Success";
	      			echo json_encode($result_msg_arr);
	      			exit;
	      		}
		      else{
		      	 		$result_msg_arr['error']['code'] = 1;
		      			$result_msg_arr['error']['msg'] = "Error";
		      			echo json_encode($result_msg_arr);
		      			exit;
		      } 
	    }

	    function addSmsCampaign(){
	    	if(intval($this->no_of_sms)==0 || trim($this->no_of_sms)=='' ||intval($this->no_of_sms)<1000 ){ 
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Of SMS Needed";
				echo json_encode($result_msg_arr);exit;
			} 
			if(trim($this->usercode)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Usercode Required";
				echo json_encode($result_msg_arr); 
				exit;
		    } 
			$sms_type_arr=array(1=>"jd");
			$sms_type=$sms_type_arr[1];
			
			$sql_ext_temp = "INSERT INTO tbl_omni_sms_details_temp set
			 		 					parentid='".$this->parentid."',
			 		 					version='".$this->version."',
			 		 					num_of_sms='".$this->no_of_sms."',
			 		 					sms_type='".$sms_type."',
			 		 					added_by='".$this->usercode."',
			 		 					added_time='".date("Y-m-d H:i:s")."', 
			 		 					sms_email_price='".$this->total_sms_email_price."' 
			 		 					ON DUPLICATE KEY UPDATE
			 		 					num_of_sms='".$this->no_of_sms."',
			 		 					sms_type='".$sms_type."',
			 		 					added_by='".$this->usercode."',
			 		 					added_time='".date("Y-m-d H:i:s")."',			 		 					
			 		 					sms_email_price='".$this->total_sms_email_price."' ";  
			$sql_ext_res = parent::execQuery($sql_ext_temp, $this->conn_temp); 
 			
 			$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
 								parentid='".$this->parentid."',
 								campaignid='83',
 								selected  	= 1
 								ON DUPLICATE KEY UPDATE
 								campaignid='83',
 								selected  	= 1";
 			$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		      $pricesql="select * from online_regis1.omni_add_ons_pricing where campaignid='83' and camp_type='1'";
		      $priceres = parent::execQuery($pricesql, $this->conn_idc);
		      if($priceres && mysql_num_rows($priceres)>0){ 
			      	while($rowprice=mysql_fetch_assoc($priceres)){
			      		$price=$rowprice['price_upfront'];
			      		$price*=$this->no_of_sms;
			      		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=83,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));
			      	}	
		      }
		      if($res_compmaster_fin_temp_insert){
		      	 		$result_msg_arr['error']['code'] = 0;
		      			$result_msg_arr['error']['msg'] = "Success";
		      			echo json_encode($result_msg_arr);
		      			exit;
		      }
		      else{
		      	 		$result_msg_arr['error']['code'] = 1;
		      			$result_msg_arr['error']['msg'] = "Error";
		      			echo json_encode($result_msg_arr);
		      			exit;
		      }
	      
	  }
	  function deleteSmsCampaign(){ 
	    	$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='83'";
	    	$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);

	    	$delemailsql="delete from tbl_omni_sms_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
	    	$res_del_temp_omni = parent::execQuery($delemailsql, $this->conn_temp); 
	    	if($res_del_temp_fnc_new){
	      	 		$result_msg_arr['error']['code'] = 0;
	      			$result_msg_arr['error']['msg'] = "Success";
	      			echo json_encode($result_msg_arr);
	      			exit;
	      		}
		      else{
		      	 		$result_msg_arr['error']['code'] = 1;
		      			$result_msg_arr['error']['msg'] = "Error";
		      			echo json_encode($result_msg_arr);
		      			exit;
		      } 
	  }
	  function tempToMainSms(){
		$dependend=false;
		/*
		$checkdept=$this->checkOmniDependent(0,2);
		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){

			$dependend=true;
		}*/

		$sqlcheck="select * from tbl_omni_sms_details where parentid='".$this->parentid."' and version='".$this->version."'";
		$checkmain_res = parent::execQuery($sqlcheck, $this->conn_idc);
		if($checkmain_res && mysql_num_rows($checkmain_res)>0){
			$result_msg_arr['error']['code'] = 0;// as richie is going forward in jda
			$result_msg_arr['error']['msg'] = "Success";  
			$result_msg_arr['error']['msg_err'] = "Data Already Present for this version";   //as richie needs this.   
			echo json_encode($result_msg_arr);exit; 
		} 
	 		$res_ins_email=true;
			$emailDetails="select * from tbl_omni_sms_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
			$emailDetailsres = parent::execQuery($emailDetails, $this->conn_temp);
			if($emailDetailsres && mysql_num_rows($emailDetailsres)>0)
	 		{
	 		 	$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid='83' and recalculate_flag=1";
	 			$checktempres = parent::execQuery($checktemp, $this->conn_temp);
	 			if(!mysql_num_rows($checktempres)>0){
	 				$result_msg_arr['error']['code'] = 1;
	 				$result_msg_arr['error']['msg'] = "No Finance Details";
	 				echo json_encode($result_msg_arr);exit;
	 			}
		 		while($emailDetailsrow=mysql_fetch_assoc($emailDetailsres))
					{
				 		 $sql_ins_email = "INSERT INTO tbl_omni_sms_details set
					 					parentid='".$emailDetailsrow['parentid']."',
					 					version  	= '".$this->version."',
					 					num_of_sms='".$emailDetailsrow['num_of_sms']."',
					 					sms_type  	= '".$emailDetailsrow['sms_type']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					added_by  	= '".$emailDetailsrow['added_by']."',
					 					sms_email_price ='".$emailDetailsrow['sms_email_price']."'
					 					ON DUPLICATE KEY UPDATE
					 					num_of_sms='".$emailDetailsrow['num_of_sms']."',
					 					sms_type  	= '".$emailDetailsrow['sms_type']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					added_by  	= '".$emailDetailsrow['added_by']."',
					 					sms_email_price ='".$emailDetailsrow['sms_email_price']."'";
						$res_ins_email = parent::execQuery($sql_ins_email, $this->conn_idc);
					
					$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					sms_taken  	= 'yes',
					 					sms_type	  	= '".$emailDetailsrow['sms_type']."',
					 					sms_no_taken  	= '".$emailDetailsrow['num_of_sms']."'
					 					ON DUPLICATE KEY UPDATE
					 					sms_taken  	= 'yes',
					 					sms_type	  	= '".$emailDetailsrow['sms_type']."',
					 					sms_no_taken  	= '".$emailDetailsrow['num_of_sms']."'";
					$res_ins_email = parent::execQuery($sql_omni_mapping, $this->conn_idc);
 
				}
			}
			if($res_ins_email){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				echo json_encode($result_msg_arr);exit;
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				echo json_encode($result_msg_arr);exit;
			}
		
		

	}
	function getSmsPrice(){
	      $pricesql="select * from online_regis1.omni_add_ons_pricing where campaignid='83' and camp_type='1'";
	      $priceres = parent::execQuery($pricesql, $this->conn_idc);
	      if($priceres && mysql_num_rows($priceres)>0){ 
		      	while($rowprice=mysql_fetch_assoc($priceres)){
		      		$price=$rowprice['price_upfront'];
		      	
		      	}
		      $result_msg_arr['error']['code'] = 0;
		      $result_msg_arr['error']['msg'] = "Price Found";
		      $result_msg_arr['data']['price'] = $price; 
		      echo json_encode($result_msg_arr);exit;
	      }
	      else{ 
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Price Details Found";
			echo json_encode($result_msg_arr);exit;
		}


	}
	function uploadrates(){ 
		$sql="select * from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
		$rate_res = parent::execQuery($sql, $this->dbConDjds);
		if($rate_res && mysql_num_rows($rate_res)){
			while($val = mysql_fetch_assoc($rate_res)){
				$rates[] = $val;
			}
			$result_msg_arr['error']['code'] = 0;
		    $result_msg_arr['error']['msg'] = "Price Found";
		    $result_msg_arr['data']['price'] = $rates[0]; 
		}else {
			$result_msg_arr['error']['code'] = 1;
		    $result_msg_arr['error']['msg'] = "Price Not Found"; 
		}
		return json_encode($result_msg_arr);
	}
	/////SSL/////
	function addSSLCampaign(){
	    	if(trim($this->ssl_val) == ''){ 
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "SSL value is needed";
				echo json_encode($result_msg_arr);exit;
			}
			if(trim($this->usercode)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Usercode Required";
				echo json_encode($result_msg_arr); 
				exit;
		    } 
			$sql_ext_temp = "INSERT INTO tbl_omni_ssl_details_temp set
			 		 					parentid='".$this->parentid."',
			 		 					version='".$this->version."',
			 		 					payment_type='".$this->ssl_payment_type."',
			 		 					payment_amount='".$this->ssl_val."',
			 		 					added_by='".$this->usercode."',
			 		 					added_time='".date("Y-m-d H:i:s")."' ,
			 		 					module    = '".$this->module."'
			 		 					ON DUPLICATE KEY UPDATE
			 		 					payment_type='".$this->ssl_payment_type."',
			 		 					payment_amount='".$this->ssl_val."',
			 		 					added_by='".$this->usercode."',
			 		 					added_time='".date("Y-m-d H:i:s")."',
			 		 					module    = '".$this->module."'";  
			$sql_ext_res = parent::execQuery($sql_ext_temp, $this->conn_temp); 
 			
 			$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
 								parentid='".$this->parentid."',
 								campaignid='86',
 								selected  	= 1
 								ON DUPLICATE KEY UPDATE
 								campaignid='86',
 								selected  	= 1";
     		  $res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);

		      $rates_arr = json_decode($this->uploadrates(),1);
			  if($rates_arr['error']['code'] == 1) {
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "price not found";
					echo json_encode($result_msg_arr);
					exit;
			  }else {
				    $rates_arr = json_decode($rates_arr['data']['price']['ecs_upfront'],1);
					if(isset($rates_arr['86'])){
						if(strtolower($this->ssl_payment_type)	==	"ecs"){
							$price= $rates_arr['86']['ecs'] * 12;  
 					    }else{
							$price=$rates_arr['86']['upfront'];
						}
						$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=86,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));
					}
					if($res_compmaster_fin_temp_insert){
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = "Success";
						echo json_encode($result_msg_arr);
						exit;
					}
					else{
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg'] = "Error";
						echo json_encode($result_msg_arr);
						exit;
					}
			  }
	  }
	  
	   function deleteSSLCampaign(){ 
	    	$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='86'";
	    	$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);

	    	$delemailsql="delete from tbl_omni_ssl_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
	    	$res_del_temp_omni = parent::execQuery($delemailsql, $this->conn_temp); 
	    	if($res_del_temp_fnc_new){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				echo json_encode($result_msg_arr);
				exit;
			}else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				echo json_encode($result_msg_arr);
				exit;
			} 
	  }
	  
	  function tempToMainSSL($genio_lite_campaign = null){
			$dependend=false;
			$sqlcheck="select * from tbl_omni_ssl_details where parentid='".$this->parentid."' and version='".$this->version."'";
			$checkmain_res = parent::execQuery($sqlcheck, $this->conn_idc);
			if($checkmain_res && mysql_num_rows($checkmain_res)>0){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";  
				$result_msg_arr['error']['msg_err'] = "Data Already Present for this version";
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else{	
					echo json_encode($result_msg_arr);exit; 
				}	
			} 
			$res_ins_email=true;
			$emailDetails="select * from tbl_omni_ssl_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
			$emailDetailsres = parent::execQuery($emailDetails, $this->conn_temp);
			if($emailDetailsres && mysql_num_rows($emailDetailsres)>0){
				
				$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid='86' and recalculate_flag=1";
				$checktempres = parent::execQuery($checktemp, $this->conn_temp);
				
				
				if(!mysql_num_rows($checktempres)>0){
					
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "No Finance Details";
					echo json_encode($result_msg_arr);exit;
				}
				
				if(count($genio_lite_campaign)>0 && !array_key_exists("86",$genio_lite_campaign))
				{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "No Finance Details";
					return $result_msg_arr;
				}
				
				while($emailDetailsrow=mysql_fetch_assoc($emailDetailsres)){
						 $sql_ins_email = "INSERT INTO tbl_omni_ssl_details set
												parentid='".$emailDetailsrow['parentid']."',
												version  	= '".$this->version."',
												payment_type='".$emailDetailsrow['payment_type']."',
												payment_amount='".$emailDetailsrow['payment_amount']."',
												added_by  	= '".$emailDetailsrow['added_by']."',
												added_time  	= '".date('Y-m-d H:i:s')."',
												module			= '".$this->module."'
										 ON DUPLICATE KEY UPDATE
												payment_type='".$emailDetailsrow['payment_type']."',
												payment_amount='".$emailDetailsrow['payment_amount']."',
												added_by='".$emailDetailsrow['added_by']."',
												added_time='".date("Y-m-d H:i:s")."',
												module			= '".$this->module."'";
						$res_ins_email = parent::execQuery($sql_ins_email, $this->conn_idc);
						$sql_omni_mapping1 = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
												parentid        	= '".$this->parentid."',
												data_city       	= '".$this->data_city_cm."',
												ssl_taken  			= 'yes',
												ssl_payment_type	= '".$emailDetailsrow['payment_type']."',
												ssl_val  			= '".$emailDetailsrow['payment_amount']."'
											ON DUPLICATE KEY UPDATE
												ssl_taken  			= 'yes',
												ssl_payment_type	= '".$emailDetailsrow['payment_type']."',
												ssl_val  			= '".$emailDetailsrow['payment_amount']."'";
						$res_ins_email1 = parent::execQuery($sql_omni_mapping1, $this->conn_idc);
				}
			}
			
			if($res_ins_email){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else {
					echo json_encode($result_msg_arr);exit;
				}
				
			}else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else {
					echo json_encode($result_msg_arr);exit;
				}
			}
	}
	
	function getSSLPrice(){
		$result_msg_arr	=	array();
		  $rates_arr = json_decode($rates_arr['data']['price']['ecs_upfront'],1);
		  if(isset($rates_arr['86'])){
				$result_msg_arr['data']['upfront_price']		=	$rates_arr['86']['upfront'];
				$result_msg_arr['data']['ecs_price']			=	$rates_arr['86']['ecs'];
				$result_msg_arr['data']['actual_ecs_price']		= 	$rates_arr['86']['ecs'] * 12; 
				$result_msg_arr['data']['actual_upfront_price']	= 	$rates_arr['86']['upfront'];
				$result_msg_arr['error']['code'] 				=	 0;
			    $result_msg_arr['error']['msg'] 				= "Price Found";
			    echo json_encode($result_msg_arr);exit;
		  }else{
			  $result_msg_arr['error']['code'] = 1;
			  $result_msg_arr['error']['msg'] = "No Price Details Found";
			  echo json_encode($result_msg_arr);exit;
		  }
	}
	/////SSL/////
}
?>
