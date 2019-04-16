<?php

class financeDisplayClass extends DB
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
	var $activephonesearchcontractflag = 0;

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
		if(trim($this->params['version']) == "" && $this->action != 47)
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
			$this->data_city  = $this->params['data_city'];
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$status=$this->setServers();
		$this->categoryClass_obj = new categoryClass();
				
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}
		if(trim($this->params['discount']) == "")
		{
			if($this->action==8 && trim($this->params['discount']) == ""){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Discount is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}
		}
		else{
			if($this->params['discount']>10){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Discount Cant be more than 10";
				echo json_encode($result_msg_arr);
				exit;
			}
			$this->discount  = $this->params['discount'];
		}
		if(trim($this->params['usercode']) == "")
		{
			if($this->action==8 && trim($this->params['usercode']) == ""){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "usercode is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}
		}
		else{

			$this->usercode  = $this->params['usercode'];
		}
		if(trim($this->params['remote_flag']) == "")
		{
			if($this->action==8 && trim($this->params['remote_flag']) == ""){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "remote_flag is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}
		}
		else{

			$this->remote_flag  = $this->params['remote_flag'];
		}

		if(trim($this->params['combo']) != "")
		{
			$this->combo  = $this->params['combo'];
		}
		else
			$this->combo  = 0;

		if(trim($this->params['custom_setup_fees']) != "")
		{
			$this->custom_setup_fees  = $this->params['custom_setup_fees'];
		}
		else
			$this->custom_setup_fees  = -1;

		if(trim($this->params['campaignid']) != "")
		{
			$this->campaignid  = $this->params['campaignid'];
		}
		else
			$this->campaignid  = '';
		if(trim($this->params['custom_value']) != "")
		{
			$this->custom_value  = $this->params['custom_value'];
		}
		else
			$this->custom_value  = 0;




		if(trim($this->params['type']) != "")
		{
			$this->type  = $this->params['type'];
		}
		else
			$this->type  = 1;

		if(trim($this->params['domain_field_incl']) != "")
		{
			$this->domain_field_incl  = $this->params['domain_field_incl'];
		}
		else
			$this->domain_field_incl  = 0;

		if(trim($this->params['module_name']) != "")
		{
			$this->module_name  = $this->params['module_name'];
		}



		if(trim($this->params['combo_cust_price']) != "")
		{
			$this->combo_cust_price  = $this->params['combo_cust_price'];
		}
		else
			$this->combo_cust_price  = 0;

		if(trim($this->params['payment_type']) != ""){
			$this->payment_type  = $this->params['payment_type'];
			// 1 -upfront
			// 2- ecs
		}
		if(trim($this->params['mobile_show']) != ""){
			$this->mobile_show  = $this->params['mobile_show'];

		}
		if(trim($this->params['email_show']) != ""){
			$this->email_show  = $this->params['email_show'];

		}
		if(trim($this->params['invoice_email']) != ""){
			$this->invoice_email  = $this->params['invoice_email'];

		}
		if(trim($this->params['invoice_mobile']) != ""){
			$this->invoice_mobile  = $this->params['invoice_mobile'];

		}
		if(trim($this->params['invoice_contact']) != ""){
			$this->invoice_contact  = $this->params['invoice_contact'];

		}
		if(trim($this->params['skip_reg']) != ""){
			$this->skip_reg  = $this->params['skip_reg'];

		}
		else
			$this->skip_reg=0;

		if(trim($this->params['email_disp']) != ""){
			$this->email_disp  = $this->params['email_disp'];
		}
		else
			$this->email_disp  =0;



		if(trim($this->params['email_feed']) != ""){
			$this->email_feed  = $this->params['email_feed'];
		}
		else
			$this->email_feed =0;
		 if(trim($this->params['mob_disp']) != ""){
		 	$this->mob_disp  = $this->params['mob_disp'];
		 }
		 else
			$this->mob_disp  =0;
		 if(trim($this->params['mob_feed']) != ""){
		 	$this->mob_feed  = $this->params['mob_feed'];
		 }
		 else
			$this->mob_feed  =0;


		if($this->action==38){
			if($this->params['combotype'] == ''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "combotype is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->combotype = $this->params['combotype'];
			}
			
			if($this->params['pri_campaignid'] == '' || $this->params['pri_campaignid'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "primary campaignid is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->pri_campaignid = $this->params['pri_campaignid'];
			}
			
			if($this->params['depcamp'] == '' || $this->params['depcamp'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "dependent campaignid is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->depcamp = $this->params['depcamp'];
			}
			
			if($this->params['dep_budget'] == '' || $this->params['dep_budget'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "dependent budget is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->dep_budget = $this->params['dep_budget'];
			}
			
			if($this->params['dep_duration'] == '' || $this->params['dep_duration'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "dependent duration is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->dep_duration = $this->params['dep_duration'];
			}
			
		}
		
		if($this->action==39){
			if($this->params['campaignid'] == ''  || $this->params['campaignid'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "campaignid is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->campaignid = $this->params['campaignid']; 
			}
			
			if($this->params['actual_budget'] == ''  || $this->params['actual_budget'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "budget is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->actual_budget = $this->params['actual_budget']; 
			}
			
			if($this->params['multiplier'] == ''  || $this->params['multiplier'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "multiplier is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->multiplier = $this->params['multiplier'] ;
			}
			
			if($this->params['ucode'] == ''  || $this->params['ucode'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "ucode is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}else {
				$this->ucode = $this->params['ucode']; 
			}
		}
		if($this->action==46){
			if($this->params['price_arr'] == '' ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "price json is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}
			if($this->params['flexi_duration'] == '' || $this->params['flexi_duration'] == 0 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Flexi duration is  Missing";
				echo json_encode($result_msg_arr);
				exit;
			}
			
			
		}


		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');

		$this->omni_duration =1520;
		$this->service_tax=0.18;
		$this->omni_type_set=0;


		$this->nationallistingclass_obj = new nationallistingclass($params);
		$this->national_change_state  =  $this->nationallistingclass_obj-> isStateAdded();
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
		$this->conn_idc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		$this->db_budgeting		= $db[$data_city]['db_budgeting']['master'];
		if(DEBUG_MODE)
		{
			echo '<pre> IDc db array :: ';
			print_r($this->dbConIdc);
		}
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['iro']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_catmaster 	= $db[$data_city]['d_jds']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_temp_new = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_catmaster 	= $db[$data_city]['d_jds']['master'];
			if((in_array($this->params['usercode'], json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_temp_new = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_catmaster 	= $db[$data_city]['idc']['master'];
			if((in_array($this->params['usercode'], json_decode(MONGOUSER)) || ALLUSER == 1)){
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

	function financeDisplayUpfront(){
		$campaigns=$this->getCampaignForParentid();
		$sql="select *  from tbl_finance_omni_flow_display where campaignid in ($campaigns) ORDER BY FIELD(campaignid,$campaigns)";
		$res = parent::execQuery($sql, $this->conn_temp);
		$campDetails=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
			{
				$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
				if($row['campaignid']=='1' || $row['campaignid']=='2'){
			 		$sqlbudget="select budget  from tbl_companymaster_finance_temp where   parentid='".$this->parentid."'  and campaignid ='". $row['campaignid']."'";
					$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
			 		if($resbudget && mysql_num_rows($resbudget) )
			 		{
		 				while($rowbudget=mysql_fetch_assoc($resbudget)){
		 					$campDetails[$row['campaignid']]['price']=$rowbudget['budget'];
		 				}
			 		}
				}
				else
					$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];

			}
 		}
 		return $campDetails;


	}
	function checkCustom($type=1){
		if($type==1){
			$retarr=array();
			$sqlcustom="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."' and combo='".$this->type."'";
			$sqlcustomres = parent::execQuery($sqlcustom, $this->conn_temp);
			if($sqlcustomres && mysql_num_rows($sqlcustomres)>0){
				while($sqlcustomrow=mysql_fetch_assoc($sqlcustomres)){
					$retarr['flag']['val']=1;
					 $retarr['data']['val']=$sqlcustomrow['fees'];
					 $retarr['setup']['val']=$sqlcustomrow['setup_fees'];

				}
			}
			else{
				$retarr['flag']['val']=0;

			}
		}
		else if($type==2){
			$retarr=array();
			$sqlcustom="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid=72";
			$sqlcustomres = parent::execQuery($sqlcustom, $this->conn_temp);
			if($sqlcustomres && mysql_num_rows($sqlcustomres)>0){
				while($sqlcustomrow=mysql_fetch_assoc($sqlcustomres)){

					$retarr['flag']['val']=1;
					 $retarr['data']['val']=($sqlcustomrow['fees']/12);
					 $retarr['setup']['val']=$sqlcustomrow['setupfees'];

				}
			}
			else{
				$retarr['flag']['val']=0;

			}
		}
		if($this->type=='4' && $retarr['flag']['val']==0){
			$banner_sql="select combodiscount,comboextradetails  from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";

					$banner_res = parent::execQuery($banner_sql, $this->dbConIro);
					$banner_arr=array();
					$combodiscount=0;
					$banner_details='';
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
		if($type== 6 || $type== 7  || $type== 8 || $type== 9 || $type== 10) {
			$sql="select omniextradetails from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
			$omni_res = parent::execQuery($sql, $this->dbConDjds);
			if($omni_res && mysql_num_rows($omni_res)){
				while($res = mysql_fetch_assoc($omni_res)){
					$omni_rates = json_decode($res['omniextradetails'],1);
				}
				
				if($type== 6){
					$omni_camp = 740;
				}else if($type== 7){
					$omni_camp = 741;
				}else if($type== 8){
					$omni_camp = 742;
				}else if($type== 9){
					$omni_camp = 743;
				}else if($type== 10){
					$omni_camp = 744;
				}
				
				if(isset($omni_rates[$omni_camp])){
					
				}
					
			}
		}

			return $retarr;
	}
	
	function budgetDisplayUpfrontWithOffer(){
		
		$city_jdrr 	= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$temp		= $this->params['camp_selected'];
		$banner_rotation		= $this->params['banner_rotation'];
		$campaigns	=	array();
		$campaigns	=	explode(',', $temp);
		$campDetails = array();
		
		
		
		if(in_array("5",$campaigns,true) && in_array("22",$campaigns,true) && $banner_rotation<=1){
			if(($key = array_search('5', $campaigns)) !== false) {
				unset($campaigns[$key]);
			}
			if(($key = array_search('22', $campaigns)) !== false) {
				unset($campaigns[$key]);
			}
			array_push($campaigns,'225');
		}
		
		$sqlget="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sqlget, $this->conn_temp);
		$customarr=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
 			{
 				$customarr[$row['campaignid']]=$row['custom_value'];
			}
		}
		 		
		$sqlbudget="select campaignid,budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid IN (1,2,10,74,82,83,86,7) and recalculate_flag = 1";
		$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
	
		if($resbudget && mysql_num_rows($resbudget)){
			while($rowbudget=mysql_fetch_assoc($resbudget)){
				if($rowbudget['budget'] > 0){
				$sqldiscount="select *  from omni_flow_discount where parentid='".$this->parentid."' and version='".$this->version."'";
				$res_discount = parent::execQuery($sqldiscount, $this->conn_temp);
					if($res_discount && mysql_num_rows($res_discount) && $val1!='74')
					{
						while($row_discount=mysql_fetch_assoc($res_discount)){
							$discount=$row_discount['discount_percent'];
						}
						while($rowbudget=mysql_fetch_assoc($resbudget)){
							if($rowbudget['campaignid'] == '82'){
								$val1	=	'82';
							}else if($rowbudget['campaignid'] == '83'){
								$val1	=	'83';
							}else if($rowbudget['campaignid'] == '74'){
								$val1	=	'74';
							}else if($rowbudget['campaignid'] == '2'){
								$val1	=	'2';
							}else if($rowbudget['campaignid'] == '10'){
								$val1	=	'10';
							}else if($rowbudget['campaignid'] == '1'){
								$val1	=	'1';
							}else if($rowbudget['campaignid'] == '86'){
								$val1	=	'86';
							}else if($rowbudget['campaignid'] == '7'){
								$val1	=	'7';
							}
							$campDetails[$val1]['price']=ceil($rowbudget['original_actual_budget']);
							$campDetails[$val1]['offer_price']=ceil($rowbudget['budget']);
							$campDetails[$val1]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
							$campDetails[$val1]['discount_percent']=(($discount)."%");
						}
					}else{
						if($row['campaignid']=='74'){
							if($domain_field_incl==1)
							$websiteprice=ceil($rowbudget['budget']);
						}
						if($rowbudget['campaignid'] == '82'){
								$nval1	=	'82';
							}else if($rowbudget['campaignid'] == '83'){
								$nval1	=	'83';
							}else if($rowbudget['campaignid'] == '74'){
								$nval1	=	'74';
							}else if($rowbudget['campaignid'] == '2'){
								$nval1	=	'2';
							}else if($rowbudget['campaignid'] == '10'){
								$nval1	=	'10';
							}else if($rowbudget['campaignid'] == '1'){
								$nval1	=	'1';
							}else if($rowbudget['campaignid'] == '86'){
								$nval1	=	'86';
							}else if($rowbudget['campaignid'] == '7'){
								$nval1	=	'7';
							}
						$campDetails[$nval1]['price']=ceil($rowbudget['budget']);
						$campDetails[$nval1]['offer_price']=ceil($rowbudget['budget']);
						$campDetails[$nval1]['discount']=(0);
						$campDetails[$nval1]['discount_percent']=((0)."%");
						//print_r($campDetails);
						if(($this->combo=='1' || $this->combo==1 )&&  $val1=='1' ){
							unset($campDetails[1]);
							$rowbudget['budget']=$rowbudget['budget']+1;
							$campDetails[173]['price']=ceil($rowbudget['budget']);
							$campDetails[173]['offer_price']=ceil($rowbudget['budget']);
							$campDetails[173]['discount']=(0);
							$campDetails[173]['discount_percent']=((0)."%");
						}
						if(($this->combo=='2' || $this->combo==2 )&&  $val1=='1' ){
							unset($campDetails[1]);
							$rowbudget['budget']=$rowbudget['budget']+10;
							$campDetails[273]['price']=ceil($rowbudget['budget']);
							$campDetails[273]['offer_price']=ceil($rowbudget['budget']);
							$campDetails[273]['discount']=(0);
							$campDetails[273]['discount_percent']=((0)."%");
						}
					}
					if($rowbudget['campaignid'] ==	'2'){
						$campDetails['2']['campaign_name']	= 'Best option';
						$campDetails['2']['campaignid']		= '2';
					}
					if($rowbudget['campaignid'] ==	'10'){
						$campDetails['10']['campaign_name']	= 'National listing';
						$campDetails['10']['campaignid']	= '10';
					}
					if($rowbudget['campaignid'] ==	'74'){
						$campDetails['74']['campaign_name']	= 'Domain Registration Fee';
						$campDetails['74']['campaignid']	= '74';
					}
					if($rowbudget['campaignid'] ==	'82'){
						$campDetails['82']['campaign_name']	= 'Email';
						$campDetails['82']['campaignid']	= '82';
					}
					if($rowbudget['campaignid'] ==	'83'){
						$campDetails['83']['campaign_name']	= 'Sms';
						$campDetails['83']['campaignid']	= '83';
					}
					if($rowbudget['campaignid'] ==	'1'){
						$campDetails['1']['campaign_name']	= 'JD Campaign';
						$campDetails['1']['campaignid']	= '1';
					}
					if($rowbudget['campaignid'] ==	'86'){
						$campDetails['86']['campaign_name']	= 'SSL Certificate';
						$campDetails['86']['campaignid']	= '86';
					}
					if($rowbudget['campaignid'] ==	'7'){
						$campDetails['7']['campaign_name']	= 'Registration Fee';
						$campDetails['7']['campaignid']	= '7';
					}
								
				}
			}
		}
		//~ echo '<pre>';print_r($campDetails);			//~ }else{
					
		//~ if($campDetails[$val1] == ''){
			$query		=	"SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
			$conq		=	parent::execQuery($query, $this->dbConDjds );
			$data	=	array();
			if($conq && mysql_num_rows($conq)>0){
				$data												=	mysql_fetch_assoc($conq);
				$retArr['data']										=	json_decode($data['PriceList'],true);
				foreach($campaigns as $key1=>$val1){
					//~ if($val1 == '2' || $val1 == '10'){
					if($val1 == '1' || $val1 == '111' || $val1 == '114' || $val1 == '115' || $val1 == '116' || $val1 == '119'){
						$cust_val1	=	'1';
					}else{
						$cust_val1	=	$val1;
					}
					if($val1	==	'5'){
						if($banner_rotation == '1'){
							$val1	=	'51';
						}else if($banner_rotation == '2'){
							$val1	=	'52';
						}else if($banner_rotation == '3'){
							$val1	=	'53';
						}else if($banner_rotation == '4'){
							$val1	=	'54';
						}else {
							$val1	= '5'.$banner_rotation;
						}
					}
					$whichCam	=	'';
					foreach($retArr['data']['Package'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Package';
						}
					}
					foreach($retArr['data']['Omni'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Omni';
						}
					}
					
					foreach($retArr['data']['Normal'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Normal';
						}
					}
					foreach($retArr['data']['Banner'] as $key=>$value){
						if($key == $val1 || substr($val1, 0, 1) === '5'){
							$whichCam	=	'Banner';
						}
					}
							
					
					if($whichCam != '' && $campDetails[$cust_val1] == ''){
						if(substr($val1, 0, 2) === '73' || substr($val1, 0, 2) === '74'){
							if($customarr['73'] != '' && $customarr['72'] != ''){
								$campDetails[$val1]['price']=ceil($customarr['72'] + $customarr['73']);
								$campDetails[$val1]['offer_price']=ceil($customarr['72'] + $customarr['73']);
							}else{	
								$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront'] + $retArr['data'][$whichCam][$val1]['down_payment']);
								$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront'] + $retArr['data'][$whichCam][$val1]['down_payment']);
							}
						}else{
							if($customarr[$val1] != ''){
								$campDetails[$val1]['price']=ceil($customarr[$val1]);
								$campDetails[$val1]['offer_price']=ceil($customarr[$val1]);
							}else{
								/********************************Fetched from Table**************************************/
								if($whichCam == 'Package'){
									$sqlPay="select payment_type from tbl_payment_type where  parentid='".$this->parentid."' AND version = '".$this->version."'";
									$resPay = parent::execQuery($sqlPay, $this->conn_finance);	
									if($resPay && mysql_num_rows($resPay) > 0){
										$disp	=	mysql_fetch_assoc($resPay);
										if(strpos($disp['payment_type'],'pck_1yr_dis') !== false){
											$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront_discount']);
											$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront_discount']);
										}else if(strpos($disp['payment_type'],'pck_2yr_dis') !== false){
											$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront_two_years']);
											$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront_two_years']);
										}else{
											$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
											$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
										}
									}else{
										$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
										$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
									}
								}else if($whichCam == 'Banner' && $banner_rotation > 4){
									$campDetails[$val1]['price']= (($banner_rotation+1)*4000);
									$campDetails[$val1]['offer_price']=(($banner_rotation+1)*4000);
								}else{
									$campDetails[$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
									$campDetails[$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
								}
							}
						}
						if($whichCam == 'Banner' && $banner_rotation > 4){
							$campDetails[$val1]['campaign_name']='Banner';
							$campDetails[$val1]['campaignid']= $val1;
						}else{
						$campDetails[$val1]['campaign_name']=$retArr['data'][$whichCam][$val1]['name'];
						$campDetails[$val1]['campaignid']=ceil($retArr['data'][$whichCam][$val1]['campaignid']);
						}
		
						$campDetails[$val1]['discount']=0;
						$campDetails[$val1]['discount_percent']=0;
						
						if($row['campaignid'] == '10'){
							$campDetails[$val1]['nationallive']=ceil($retArr['data'][$whichCam][$val1]['live']);
							$campDetails[$val1]['national_state_change']=ceil($retArr['data'][$whichCam][$val1]['state_change']);
						}
					}
				}
					//~ $campDetails[$row['campaignid']]['offer_price']=ceil($retArr['data'][$whichCam][$row['campaignid']]['price_upfront']);##
					//~ $campDetails[$row['campaignid']]['discount']=ceil($retArr['data'][$whichCam][$row['campaignid']]['price_upfront']);
					//~ $campDetails[$row['campaignid']]['discount_percent']=(($discount)."%");
				}
			//~ }
		$sql="update campaigns_selected_status set ecs_flag='upfront' where  parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		return $campDetails;
	}
	
	
	function budgetDisplayEcsWithOffer(){
		
		$city_jdrr 	= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$temp		= $this->params['camp_selected'];
		$banner_rotation		= $this->params['banner_rotation'];
		$campaigns	=	array();
		//~ print_r($this->params['camp_selected']);die;
		$campaigns	=	explode(',', $temp);
		$campDetails = array();
		
		
		$sqlget="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sqlget, $this->conn_temp);
		$customarr=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
 			{
 				$customarr[$row['campaignid']]=$row['custom_value'];
			}
		}
		 
		 
		 
 		//Banner handling
 		
 		
 		
 		$sqlbudget="select campaignid,budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid IN (2,10,74,82,83,1,86,7) and recalculate_flag = 1";
		$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
		if($resbudget && mysql_num_rows($resbudget)>0){
			
			while($rowbudget=mysql_fetch_assoc($resbudget)){
				if($rowbudget['budget'] > 0){
					$budget_ecs = '';
					$rowbudget['budget'];
					$budget_ecs	=	ceil($rowbudget['budget']/12);
					if($rowbudget['campaignid'] == '82' || $rowbudget['campaignid'] == '83' || $rowbudget['campaignid'] == '86'){
							$campDetails['advance'][$rowbudget['campaignid']]['price']=ceil($rowbudget['budget']);
							$campDetails['advance'][$rowbudget['campaignid']]['offer_price']=ceil($rowbudget['budget']);
							$campDetails['advance'][$rowbudget['campaignid']]['discount']=0;
							$campDetails['advance'][$rowbudget['campaignid']]['discount_percent']=0;
							
							$campDetails['monthly'][$rowbudget['campaignid']]['price']				=0;
							$campDetails['monthly'][$rowbudget['campaignid']]['offer_price']		=0;
							$campDetails['monthly'][$rowbudget['campaignid']]['discount']			=0;
							$campDetails['monthly'][$rowbudget['campaignid']]['discount_percent']	=0;
					}else{
						if(in_array('119',$campaigns) && $rowbudget['campaignid'] == 1 && ceil($budget_ecs*2) < 5000){
							$campDetails['advance'][$rowbudget['campaignid']]['price']=5000;
							$campDetails['advance'][$rowbudget['campaignid']]['offer_price']=5000;
							$campDetails['advance'][$rowbudget['campaignid']]['discount']=5000;
							$campDetails['advance'][$rowbudget['campaignid']]['discount_percent']=(($discount)."%");
						}else if(in_array('119',$campaigns) && $rowbudget['campaignid'] == 1){
							$campDetails['advance'][$rowbudget['campaignid']]['price']=ceil($budget_ecs*2);
							$campDetails['advance'][$rowbudget['campaignid']]['offer_price']=ceil($budget_ecs*2);
							$campDetails['advance'][$rowbudget['campaignid']]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
							$campDetails['advance'][$rowbudget['campaignid']]['discount_percent']=(($discount)."%");
						}else{
							$campDetails['advance'][$rowbudget['campaignid']]['price']=ceil($budget_ecs*3);
							$campDetails['advance'][$rowbudget['campaignid']]['offer_price']=ceil($budget_ecs*3);
							$campDetails['advance'][$rowbudget['campaignid']]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
							$campDetails['advance'][$rowbudget['campaignid']]['discount_percent']=(($discount)."%");
						}
						
						$campDetails['monthly'][$rowbudget['campaignid']]['price']=ceil($budget_ecs);
						$campDetails['monthly'][$rowbudget['campaignid']]['offer_price']=ceil($budget_ecs);
						$campDetails['monthly'][$rowbudget['campaignid']]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
						$campDetails['monthly'][$rowbudget['campaignid']]['discount_percent']=(($discount)."%");
					}
					if($rowbudget['campaignid'] ==	'2'){
						$campDetails['advance']['2']['campaign_name']	= 'Best option';
						$campDetails['advance']['2']['campaignid']		= '2';
						
						$campDetails['monthly']['2']['campaign_name']	= 'Best option';
						$campDetails['monthly']['2']['campaignid']		= '2';
					}
					if($rowbudget['campaignid'] ==	'10'){
						$campDetails['advance']['10']['campaign_name']	= 'National listing';
						$campDetails['advance']['10']['campaignid']	= '10';
						
						$campDetails['monthly']['10']['campaign_name']	= 'National listing';
						$campDetails['monthly']['10']['campaignid']	= '10';
					}
					if($rowbudget['campaignid'] ==	'74'){
						$campDetails['advance']['74']['campaign_name']	= 'Domain Registration Fee';
						$campDetails['advance']['74']['campaignid']	= '74';
						
						$campDetails['monthly']['74']['campaign_name']	= 'Domain Registration Fee';
						$campDetails['monthly']['74']['campaignid']	= '74';
					}
					if($rowbudget['campaignid'] ==	'82'){
						$campDetails['advance']['82']['campaign_name']	= 'Email';
						$campDetails['advance']['82']['campaignid']	= '82';
						
						$campDetails['monthly']['82']['campaign_name']	= 'Email';
						$campDetails['monthly']['82']['campaignid']	= '82';
					}
					if($rowbudget['campaignid'] ==	'83'){
						$campDetails['advance']['83']['campaign_name']	= 'Sms';
						$campDetails['advance']['83']['campaignid']	= '83';
						
						$campDetails['monthly']['83']['campaign_name']	= 'Sms';
						$campDetails['monthly']['83']['campaignid']	= '83';
					}
					if($rowbudget['campaignid'] ==	'1'){
						$campDetails['advance']['1']['campaign_name']	= 'Jd campaign';
						$campDetails['advance']['1']['campaignid']	= '1';
						
						$campDetails['monthly']['1']['campaign_name']	= 'Jd campaign';
						$campDetails['monthly']['1']['campaignid']	= '1';
					}
					if($rowbudget['campaignid'] ==	'86'){
						$campDetails['advance']['86']['campaign_name']	= 'SSL Certificate';
						$campDetails['advance']['86']['campaignid']	= '86';
						
						$campDetails['monthly']['86']['campaign_name']	= 'SSL Certificate';
						$campDetails['monthly']['86']['campaignid']	= '86';
					}
					if($rowbudget['campaignid'] ==	'7'){
						$campDetails['advance']['7']['campaign_name']	= 'Registration Fee';
						$campDetails['advance']['7']['campaignid']	= '7';
						
						$campDetails['monthly']['7']['campaign_name']	= 'Registration Fee';
						$campDetails['monthly']['7']['campaignid']	= '7';
					}
				}
			}
		}
		
 		
			$query		=	"SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
			$conq		=	parent::execQuery($query, $this->dbConDjds);
			$data	=	array();
			if($conq && mysql_num_rows($conq)>0){
				$data												=	mysql_fetch_assoc($conq);
				$retArr['data']										=	json_decode($data['PriceList'],true);
				foreach($campaigns as $key1=>$val1){	
				if($val1 == '1' || $val1 == '111' || $val1 == '114' || $val1 == '115' || $val1 == '116' || $val1 == '119'){
					$cust_val1	=	'1';
				}else{
					$cust_val1	=	$val1;
				}
				if($campDetails['monthly'][$val1] == '' && $campDetails['advance'][$cust_val1] == ''){	
					if($val1	==	'5'){
						if($banner_rotation == '1'){
							$val1	=	'51';
						}else if($banner_rotation == '2'){
							$val1	=	'52';
						}else if($banner_rotation == '3'){
							$val1	=	'53';
						}else if($banner_rotation == '4'){
							$val1	=	'54';
						}else {
							$val1	= '5'.$banner_rotation;
						}
					}
					$whichCam	=	'';
					foreach($retArr['data']['Package'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Package';
						}
					}
					foreach($retArr['data']['Omni'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Omni';
						}
					}
					
					foreach($retArr['data']['Normal'] as $key=>$value){
						if($key == $val1){
							$whichCam	=	'Normal';
						}
					}
					foreach($retArr['data']['Banner'] as $key=>$value){
						if($key == $val1 || substr($val1, 0, 1) === '5'){
							$whichCam	=	'Banner';
						}
					}
					
					
					
					
					if($whichCam != ''){
						if(substr($val1, 0, 2) === '73' || substr($val1, 0, 2) === '74'){
							if($customarr['73'] != '' && $customarr['72'] != ''){
								$campDetails['monthly'][$val1]['price']=ceil($customarr['73']);
								$campDetails['monthly'][$val1]['offer_price']=ceil($customarr['73']);
								
								$campDetails['advance'][$val1]['price']=ceil($customarr['72']);
								$campDetails['advance'][$val1]['offer_price']=ceil($customarr['72']);
							}else{	
								$campDetails['monthly'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
								$campDetails['monthly'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
								
								$campDetails['advance'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['down_payment']);
								$campDetails['advance'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['down_payment']);
							}
						}else{
							if($customarr[$val1] != ''){
								if($val1 == '22' || $val1 == '225'){
									$campDetails['monthly'][$val1]['price']=ceil($customarr[$val1]);
									$campDetails['monthly'][$val1]['offer_price']=ceil($customarr[$val1]);
								
									$campDetails['advance'][$val1]['price']=ceil($customarr[$val1]*12);
									$campDetails['advance'][$val1]['offer_price']=ceil($customarr[$val1]*12);
								}else{
									
									$campDetails['monthly'][$val1]['price']=ceil($customarr[$val1]);
									$campDetails['monthly'][$val1]['offer_price']=ceil($customarr[$val1]);
								
									$campDetails['advance'][$val1]['price']=ceil($customarr[$val1]*3);
									$campDetails['advance'][$val1]['offer_price']=ceil($customarr[$val1]*3);
								}
							}else{
								if($val1 == '22' || $val1 == '225'){
									$campDetails['monthly'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
									$campDetails['monthly'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
									
									$campDetails['advance'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
									$campDetails['advance'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_upfront']);
								}else if($whichCam == 'Banner' && $banner_rotation > 4){
									$campDetails['monthly'][$val1]['price']= (($banner_rotation+1)*500);
									$campDetails['monthly'][$val1]['offer_price']=(($banner_rotation+1)*500);;
									
									$campDetails['advance'][$val1]['price']= $campDetails['monthly'][$val1]['price'] * 3;
									$campDetails['advance'][$val1]['offer_price']= $campDetails['monthly'][$val1]['price'] * 3;
								}else{	
									$campDetails['monthly'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
									$campDetails['monthly'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']);
									
									$campDetails['advance'][$val1]['price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']*3);
									$campDetails['advance'][$val1]['offer_price']=ceil($retArr['data'][$whichCam][$val1]['price_ecs']*3);
								}
							}
						}
						
						if($whichCam == 'Banner' && $banner_rotation > 4){
							$campDetails['monthly'][$val1]['campaign_name']='Banner';
							$campDetails['monthly'][$val1]['campaignid']=$val1;
							
							$campDetails['advance'][$val1]['campaign_name']='Banner';
							$campDetails['advance'][$val1]['campaignid']=$val1;
						}else {
						$campDetails['monthly'][$val1]['campaign_name']=$retArr['data'][$whichCam][$val1]['name'];
						$campDetails['monthly'][$val1]['campaignid']=ceil($retArr['data'][$whichCam][$val1]['campaignid']);
							
							$campDetails['advance'][$val1]['campaign_name']=$retArr['data'][$whichCam][$val1]['name'];
							$campDetails['advance'][$val1]['campaignid']=ceil($retArr['data'][$whichCam][$val1]['campaignid']);
						}
						
						$campDetails['monthly'][$val1]['discount']=0;
						$campDetails['monthly'][$val1]['discount_percent']=0;

						$campDetails['advance'][$val1]['discount']=0;
						$campDetails['advance'][$val1]['discount_percent']=0;
						
						if($row['campaignid'] == '10'){
							$campDetails['monthly'][$val1]['nationallive']=ceil($retArr['data'][$whichCam][$val1]['live']);
							$campDetails['monthly'][$val1]['national_state_change']=ceil($retArr['data'][$whichCam][$val1]['state_change']);
							
							$campDetails['advance'][$val1]['nationallive']=ceil($retArr['data'][$whichCam][$val1]['live']);
							$campDetails['advance'][$val1]['national_state_change']=ceil($retArr['data'][$whichCam][$val1]['state_change']);
							}
						}
					}
				}
			}
		$sql="update campaigns_selected_status set ecs_flag='ecs' where  parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		return $campDetails;
	}
	
	function gotopaymentPage(){
		$resArr = array();
		$checksql				= "select * from tme_jds.campaigns_selected_status where parentid='".$this->parentid."'"; 
		$con					= parent::execQuery($checksql, $this->conn_temp);
		if($con && mysql_num_rows($con)>0) {
			while($res	=	mysql_fetch_assoc($con)) {
				$resArr['data'][]	=	$res;
			}
			$resArr['errorCode'] 	= 0;
			$resArr['errorStatus'] = 'Data Found';
		}else{
			$resArr['errorCode'] 	= 1;
			$resArr['errorStatus'] 	= 'Data Not Found';
		}
		if($this->params['ecs_flag']==1){
			$checksql	=	"update campaigns_selected_status set ecs_flag='ecs' where parentid='".$this->parentid."'"; 
			$con		=	parent::execQuery($checksql, $this->conn_temp);	
			$resArr['Update'] = $con;
		}else{
			$checksql	=	"update campaigns_selected_status set ecs_flag='upfront' where parentid='".$this->parentid."'"; 
			$con		=	parent::execQuery($checksql, $this->conn_temp);
			$resArr['Update'] = $con;
		}
		return $resArr;
	}
	
	function tempactualbudgetupdate(){
		$resArr					=	array();
		$insertdummycampaigns	=	"insert into  campaigns_selected_status set parentid='".trim($this->params['parentid'])."',version='".trim($this->params['version'])."',campaignid='123',selected=0 ON DUPLICATE KEY UPDATE parentid='".trim($this->params['parentid'])."',version='".trim($this->params['version'])."',campaignid='123',selected=0"; 
		$con					=	parent::execQuery($insertdummycampaigns, $this->conn_temp); 
		$resetcampaigns			=	"update campaigns_selected_status set selected=0 where parentid='".trim($this->params['parentid'])."'";
		$con					=	parent::execQuery($resetcampaigns, $this->conn_temp);
		$resetcampaigns			=	"delete from  tbl_custom_omni_combo_budget where parentid='".trim($this->params['parentid'])."' and version='".trim($this->params['version'])."'"; 
		$con					=	parent::execQuery($resetcampaigns, $this->conn_temp);
		$resetcampaigns			=	"delete from  dependant_campaign_details_temp where parentid='".trim($this->params['parentid'])."'";
		$con					=	parent::execQuery($resetcampaigns, $this->conn_temp);
		$resetcampaigns			=	"delete from  tbl_custom_budget_campaign_wise where parentid='".trim($this->params['parentid'])."'";
		$con					=	parent::execQuery($resetcampaigns, $this->conn_temp);
		$resetcampaigns			=	"delete from  tbl_custom_omni_budget where parentid='".trim($this->params['parentid'])."'";
		$con					=	parent::execQuery($resetcampaigns, $this->conn_temp);
		$resetcampaigns			=	"delete from  tbl_lifetime_emi_option where parentid='".trim($this->params['parentid'])."' and version='".trim($this->params['version'])."'";
        $con    				=   parent::execQuery($resetcampaigns, $this->conn_temp);
		$sel					=	"select * from tbl_payment_type where parentid='".$this->params['parentid']."'  and version='".$this->params['version']."' and (payment_type in ('package_weekly','package_monthly') or find_in_set('r&w_mini',payment_type) <> 0)";
		$sel_res 				= 	parent::execQuery($sel, $this->conn_finance);
		if($sel_res && mysql_num_rows($sel_res) > 0){
				$this->deleteJdCampaign();
		}
		$this->apiCalledAlwaysOmniFlow();
	}
	
	
	function financeDisplayUpfrontWithOffer(){
		$city_jdrr 	= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');

		$campaigns=$this->getCampaignForParentid();


		$national_main = 0;
		$sql_nationallisting = "SELECT * FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid= '".$this->parentid."' and balance > 0";
		$res_nationallisting = parent::execQuery($sql_nationallisting, $this->conn_temp);
		if($res_nationallisting && mysql_num_rows($res_nationallisting))
		{
			$national_main = 1;
		}

		$combo_fees=0;
		$domain_field_incl=0;
		if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' )
		{
			$sql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sql, $this->conn_temp);
			if($res && mysql_num_rows($res) >0)
			{
				while($row=mysql_fetch_assoc($res)){
					$combo_fees=$row['fees'];
					$setup_fees_adv=$row['setup_fees'];
					$domain_field_incl=$row['domain_field_incl'];
				}
			}
 		}
		$sql="select *  from tbl_finance_omni_flow_display where campaignid in ($campaigns) ORDER BY FIELD(campaignid,$campaigns)";
		$res = parent::execQuery($sql, $this->conn_temp);
		$campDetails=array();
		$banner_combo=0;
		$websiteprice=0;
		
		if($res && mysql_num_rows($res))
 		{	
 			while($row=mysql_fetch_assoc($res))
			{ 
				$discount=0;
				if( (($row['campaignid']=='1'   || $row['campaignid']=='2') && $this->type != 4 && $this->type != 5 && $this->type != 2 ) || $row['campaignid']=='74' || $row['campaignid']=='82' || $row['campaignid']=='83' || $row['campaignid']=='10'  || $row['campaignid']=='86' || $row['campaignid']=='7'){ 
			 		$sqlbudget="select budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid ='". $row['campaignid']."'";
					$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);

			 		if($resbudget && mysql_num_rows($resbudget) )
			 		{

		 				while($rowbudget=mysql_fetch_assoc($resbudget)){

		 					$sqldiscount="select *  from omni_flow_discount where parentid='".$this->parentid."' and version='".$this->version."'";
		 						$res_discount = parent::execQuery($sqldiscount, $this->conn_temp);

		 						if($res_discount && mysql_num_rows($res_discount) && $row['campaignid']!='74')
		 						{
		 							while($row_discount=mysql_fetch_assoc($res_discount)){
		 								$discount=$row_discount['discount_percent'];
		 							}

		 						$campDetails[$row['campaignid']]['price']=ceil($rowbudget['original_actual_budget']);
			 					$campDetails[$row['campaignid']]['offer_price']=ceil($rowbudget['budget']);
			 					$campDetails[$row['campaignid']]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
			 					$campDetails[$row['campaignid']]['discount_percent']=(($discount)."%");

								}
		 					else{
		 						if($row['campaignid']=='74'){
		 							if($domain_field_incl==1)
		 							$websiteprice=ceil($rowbudget['budget']);
		 						}
		 						$campDetails[$row['campaignid']]['price']=ceil($rowbudget['budget']);
		 						$campDetails[$row['campaignid']]['offer_price']=ceil($rowbudget['budget']);
		 						$campDetails[$row['campaignid']]['discount']=(0);
		 						$campDetails[$row['campaignid']]['discount_percent']=((0)."%");
		 						//print_r($campDetails);
		 						if(($this->combo=='1' || $this->combo==1 )&&  $row['campaignid']=='1' ){
		 							unset($campDetails[1]);
		 							$rowbudget['budget']=$rowbudget['budget']+1;
		 							$campDetails[173]['price']=ceil($rowbudget['budget']);
		 							$campDetails[173]['offer_price']=ceil($rowbudget['budget']);
		 							$campDetails[173]['discount']=(0);
		 							$campDetails[173]['discount_percent']=((0)."%");
		 						}
		 						if(($this->combo=='2' || $this->combo==2 )&&  $row['campaignid']=='1' ){
		 							unset($campDetails[1]);
		 							$rowbudget['budget']=$rowbudget['budget']+10;
		 							$campDetails[273]['price']=ceil($rowbudget['budget']);
		 							$campDetails[273]['offer_price']=ceil($rowbudget['budget']);
		 							$campDetails[273]['discount']=(0);
		 							$campDetails[273]['discount_percent']=((0)."%");
		 						}
		 						
		 						if($this->type == 11 && ($row['campaignid']=='1' || $row['campaignid']=='2')){  
									$campDetails[1173]['campaign_name']='PDG festive Combo Offer ';
									$campDetails[1173]['campaignid']=1173;
									$campDetails[1173]['price'] += ceil($rowbudget['original_actual_budget']);
									$campDetails[1173]['offer_price']+=ceil($rowbudget['budget']);
									$campDetails[1173]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
									$campDetails[1173]['discount_percent']=(($discount)."%");
								}
								
								if($this->type == 12 && $row['campaignid']=='10'){ 
									$campDetails[1273]['campaign_name']='National listing festive Combo Offer ';
									$campDetails[1273]['campaignid']=1273;
									$campDetails[1273]['price'] = ceil($rowbudget['original_actual_budget']);
									$campDetails[1273]['offer_price']=ceil($rowbudget['budget']);
									$campDetails[1273]['discount']=ceil($rowbudget['original_actual_budget']-$rowbudget['budget']);
									$campDetails[1273]['discount_percent']=(($discount)."%");
									
								}
		 					}

		 				}
			
		 				$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 				$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
		 				if($campDetails[$row['campaignid']]['campaignid'] == '10' && $national_main==0)
		 				{
							$campDetails[$row['campaignid']]['nationallive'] = 1;
						}
						else if($campDetails[$row['campaignid']]['campaignid'] == '10' && $national_main==1)
						{
							$campDetails[$row['campaignid']]['nationallive'] = 0;
						}
		 				if($campDetails[$row['campaignid']]['campaignid'] && $this->national_change_state['state_change'] == 1)
		 				{
							$campDetails[$row['campaignid']]['national_change_state'] = 1;
						}
						else if($campDetails[$row['campaignid']]['campaignid'] && $this->national_change_state['state_change'] == 0)
						{
							$campDetails[$row['campaignid']]['national_change_state'] = 0;
						}



		 				if(($this->combo=='1' || $this->combo==1 )&&  $row['campaignid']=='1' ){
		 					unset($campDetails[1]);
		 					$campDetails[173]['campaign_name']='Combo';
		 					$campDetails[173]['campaignid']=173;
		 				}
		 				if(($this->combo=='2' || $this->combo==2 )&&  $row['campaignid']=='1' ){
		 					unset($campDetails[1]);
		 					$campDetails[273]['campaign_name']='Combo2';
		 					$campDetails[273]['campaignid']=273;
		 				}
		 				
		 				if($this->type == 11){
							unset($campDetails[1]);
							unset($campDetails[2]);
						}
					
						
						if($this->type == 12){
							unset($campDetails[10]);
						}
		 					
			 		}
				}else if($this->type >5 && ($row['campaignid']=='73' ||$row['campaignid']=='72' || $row['campaignid']=='75' || $row['campaignid']=='84') && ($this->type == 14 || $this->type == 15 || $this->type <10)){ 
					//~ $this->omni_type_set=1;
					
					$sql="select omniextradetails from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
					$omni_res = parent::execQuery($sql, $this->dbConDjds);
					if($omni_res && mysql_num_rows($omni_res)){
						while($omni_val = mysql_fetch_assoc($omni_res)){
							$omni_rates = json_decode($omni_val['omniextradetails'],1);
						}
						
						if($this->type== 6){
							$omni_camp = 740;
							$campain_name = "Website Maintenance Fee";
							$campaignid= "73";
						}
						if($this->type == 7){
							$omni_camp = 741;
							$campain_name = "Website Maintenance Fee";
							$campaignid= "73";
						}
						if($this->type == 10){
							$omni_camp = 744;
							$campain_name = "Website Maintenance Fee";
							$campaignid= "73";
						}
						if($this->type == 14){
							$omni_camp = 748;
							$campain_name = "Website Maintenance Fee";
							$campaignid= "73";
						}
						if($this->type == 15){
							$omni_camp = 749;
							$campain_name = "Website (3 years)";
							$campaignid= "73";
						}
						if($this->type == 8 || $row['campaignid']=='84'){
							$omni_camp = 742;
							$campain_name = "Android App";
							$campaignid= "84";
						}
						if($this->type == 9 || $row['campaignid']=='75'){
							$omni_camp = 743;
							$campain_name = "iOS App";
							$campaignid= "75";
						}
						
						if(isset($omni_rates[$omni_camp])){
							$campDetails[$campaignid]['campaign_name']=$campain_name;
							$campDetails[$campaignid]['campaignid']=$campaignid;
							if($this->type != 8 && $this->type != 9){
								$campDetails[$campaignid]['price']=$omni_rates[$omni_camp]['upfront'] + $omni_rates[$omni_camp]['down_payment'] ;
								$campDetails[$campaignid]['offer_price']=$omni_rates[$omni_camp]['upfront'] + $omni_rates[$omni_camp]['down_payment'] ;
							}else {
								$campDetails[$campaignid]['price']=$omni_rates[$omni_camp]['upfront'];
								$campDetails[$campaignid]['offer_price']=$omni_rates[$omni_camp]['upfront'];
							}
								
							
							$campDetails[$campaignid]['discount']=0;
							$campDetails[$campaignid]['discount_percent']="0%";
						}
						$customflag=$this->getCustomValues(1);
						if(isset($customflag['72'])){
							$campDetails[$campaignid]['price']=$customflag['72'] + $customflag['73'];
							$campDetails[$campaignid]['offer_price']=$customflag['72'] + $customflag['73'];
						}
					}
				}else if(($this->type == 16 || $this->type == 17 || $this->type == 18) && ($row['campaignid']=='73' || $row['campaignid']=='72')){
					$jdrrcombo ="SELECT omni_fees_upfront+omni_fees_plus_banner+omni_fees_plus_jdrr+omni_monthly_fees AS total_rate,omni_combo_name  FROM tbl_omni_pricing WHERE city='".$this->data_city_cm."' AND omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombo, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0){
						while($jdrrcomboresrow=mysql_fetch_assoc($jdrrcombores))
						{
							$campDetails['750']['price']=$jdrrcomboresrow['total_rate'];
							$campDetails['750']['price_upfront_actual']=$jdrrcomboresrow['total_rate'];
							$campDetails['750']['price_upfront_display']=$jdrrcomboresrow['total_rate'];
							$campDetails['750']['offer_price']=$jdrrcomboresrow['total_rate'];
							$campDetails['750']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
						}
					}
						
					$campDetails['750']['campaignid']='750';
					$campDetails['750']['discount']=0;
					$campDetails['750']['discount_percent']="0%";
						
				}else if($this->type>1 && $this->omni_type_set==0 && $this->type != 11 && $this->type != 12 && ($row['campaignid']=='73' || $row['campaignid']=='1' || $row['campaignid']=='72')){    
								$this->omni_type_set=1;
								if(($this->type == 5 || $this->type == 11 || $this->type == 12)  && $this->data_city_cm == 'remote'){
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
								
								$checkomnisql="select *,(omni_monthly_fees+omni_fees_upfront) as total_fees from tbl_omni_pricing where  city='".$omni_city."' and omni_type='".$this->type."' ";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['total_fees'];
			 						 	$adv=$checkomnisqlrow['omni_fees_upfront'];
			 						$row['price_upfront_actual']=$price_setup;
			 						$row['price_upfront_display']=$price_setup;
			 						if($this->type==2)
			 						 $customflag=$this->checkCustom(2);
			 						else
			 							$customflag=$this->checkCustom();

			 						if($customflag['flag']['val']==1 && $this->type != 2){
			 							$adv=$customflag['setup']['val'];
										$row['price_upfront_actual']=(($customflag['data']['val']*12)+$adv);
										$row['price_upfront_display']=(($customflag['data']['val']*12)+$adv);
									}

					 				$row['campaignid']=$this->type."73";
						 			$campDetails[$row['campaignid']]['campaign_name']=$checkomnisqlrow['omni_combo_name'];

									$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
									$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
									$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
									$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
									$campDetails[$row['campaignid']]['discount']=($dis_price);
									$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";


								}
							}
		 		}else if($this->type ==1  && ($row['campaignid']=='73'|| $row['campaignid']=='72') ){
					$checkomnisql="select *,(omni_monthly_fees+omni_fees_upfront) as total_fees from tbl_omni_pricing where  city='".$this->data_city_cm."' and omni_type='".$this->type."' ";
					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
					{
						while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
						{
							$price_setup=$checkomnisqlrow['total_fees'];
							$adv=$checkomnisqlrow['omni_fees_upfront'];
							$row['price_upfront_actual']=$price_setup;
							$row['price_upfront_display']=$price_setup;
							if($this->type==2)
							 $customflag=$this->checkCustom(2);
							else
								$customflag=$this->checkCustom();
							if($customflag['flag']['val']==1){

								$adv=$customflag['setup']['val'];
								$row['price_upfront_actual']=(($customflag['data']['val']*12)+$adv);
								$row['price_upfront_display']=(($customflag['data']['val']*12)+$adv);
							}

							$row['campaignid']="73";
							$campDetails[$row['campaignid']]['campaign_name']="complete suite for 5 years";

							$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
							$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
							$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
							$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
							$campDetails[$row['campaignid']]['discount']=($dis_price);
							$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";
							unset($campDetails['1']);
								}
							}
		 		}else{ 
					if(($row['campaignid']=='5' || $row['campaignid']=='22') && ($this->type!=5 && $this->type!=11 && $this->type!=12 && $this->type !=16 && $this->type !=17 && $this->type !=18 ) ){

						$sql_jdrr_budget="select budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid ='22'";
						$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->conn_finance_temp);
						if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget))
						{
							$row_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);
							$current_jdrr_budget = $row_jdrr_budget['budget'];
						}


						$sqlcombocheck="select * from campaigns_selected_status where selected=1 and parentid='".$this->parentid."' and campaignid in (5,22)";
						$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
						 $sqlget_check="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('22','5')";
						$res_combocheck = parent::execQuery($sqlget_check, $this->conn_temp);
						$flag225=true;
						if($res_combocheck && mysql_num_rows($res_combocheck)>0)
				 		{
				 			$flag225=false;
				 		}
				 		if($rescombo && mysql_num_rows($rescombo) ==2 && $flag225)
				 		{
				 			if($banner_combo!=1){

								$sql225="select *  from tbl_finance_omni_flow_display where campaignid='225'";
								$res225 = parent::execQuery($sql225, $this->conn_temp);
								if($res225 && mysql_num_rows($res225))
						 		{
						 			while($row225=mysql_fetch_assoc($res225))
									{
						 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
							 			//$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225' and city='".$city_jdrr."'";
							 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225' ";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['setupfees'];
						 						}
						 						$row225['price_upfront_actual']=$price_setup;
						 					}
				 						}
				 						if($this->combo=='2' || $this->combo==2){
								 			if($combo_fees>0)
				 							{

							 					$ratioamt=(1/7);
							 					$price_setup=ceil($combo_fees*$ratioamt);
								 				$row225['price_upfront_actual']=($price_setup*12);
				 							}else{
				 							$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
								 			$checkjdrrplussql="select * from tbl_combo_pricing  where campaignid='225' and combo='combo2' and city='".$scity."'";
						 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
				 					 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
				 								{
				 									$price_setup=$checkjdrrplussqlrow['ecs_upfront'];
				 								}
				 								$row225['price_upfront_actual']=$price_setup;
				 							}
				 						}
										$campDetails[$row225['campaignid']]['campaign_name']=$row225['campaign_name'];
	 									$campDetails[$row225['campaignid']]['campaignid']=$row225['campaignid'];
										$dis_price=$row225['price_upfront_display']-$row225['price_upfront_actual'];
										$campDetails[$row225['campaignid']]['price']=$row225['price_upfront_display'];
										$campDetails[$row225['campaignid']]['offer_price']=$row225['price_upfront_actual'];
										$campDetails[$row225['campaignid']]['discount']=($dis_price);
										$campDetails[$row225['campaignid']]['discount_percent']=ceil(($dis_price/$row225['price_upfront_display'])*100)."%";

									}
									if(count($campDetails[225])>0 && $campDetails[225]['offer_price'] < $current_jdrr_budget)
									{
										$campDetails[225]['offer_price'] = $current_jdrr_budget + 8;
									}
									
									$sql_paytype = "SELECT payment_type  FROM tbl_payment_type WHERE parentid='".$this->parentid."' and version='".$this->version."'";
									$res_paytype = parent::execQuery($sql_paytype, $this->fin);
									if ($res_paytype && mysql_num_rows($res_paytype) > 0) 
									{
										$row_paytype = mysql_fetch_assoc($res_paytype);
										if(strstr($row_paytype['payment_type'],"jdrr_web_dis") != '' && count($campDetails[225])>0)
										{
											$campDetails[225]['offer_price'] = 7500;
											if($campDetails[225]['offer_price'] < $current_jdrr_budget)
											{
												$campDetails[225]['offer_price'] = $current_jdrr_budget+8;
											}
										}
									}
								}
							}
							$banner_combo=1;
				 		}
				 		else{


					 			if($this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ) {
						 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
				 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
					 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
					 			 		{
					 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
					 						{
					 							$price_setup=$checkjdrrplussqlrow['setupfees'];
					 						}
					 						$row['price_upfront_actual']=$price_setup;
					 					}
			 						}
		 			 			$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 						$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
		 						$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
		 						$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
		 						$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
		 						$campDetails[$row['campaignid']]['discount']=($dis_price);
		 						$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";

				 		}
						if(count($campDetails[22])>0 && $campDetails[22]['offer_price'] < $current_jdrr_budget)
						{
							$campDetails[22]['offer_price'] = $current_jdrr_budget;
						}
						
						
						$sql_paytype = "SELECT payment_type  FROM tbl_payment_type WHERE parentid='".$this->parentid."' and version='".$this->version."'";
						$res_paytype = parent::execQuery($sql_paytype, $this->fin);
						if ($res_paytype && mysql_num_rows($res_paytype) > 0) 
						{
							$row_paytype = mysql_fetch_assoc($res_paytype);
							if(strstr($row_paytype['payment_type'],"jdrr_web_dis") != '' && count($campDetails[22])>0 )
							{
								$campDetails[22]['offer_price'] = 3000;
								if($campDetails[22]['offer_price'] < $current_jdrr_budget)
								{
									$campDetails[22]['offer_price'] = $current_jdrr_budget;
								}
							}
						}	
						

			 		}
			 		else if($row['campaignid']=='72'){
			 			if($this->type>1){
			 				continue;
			 			}
			 			if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1)
			 			{

			 				$row['price_upfront_actual']=20000;
			 			}
			 			$price_setup=0;
			 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
				 			$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
		 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
		 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
		 			 		{
		 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
		 						{
		 							$price_setup=$checkomnisqlrow['setupfees'];
		 						}
		 						$row['price_upfront_actual']=$price_setup;
		 					}
	 					}
	 					if($this->type==2){
				 			$checkomnisql="select * from tbl_omni_pricing where city='".$this->data_city_cm."' and omni_type='".$this->type."'";
		 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
		 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
		 			 		{
		 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
		 						{
		 							$price_setup=$checkomnisqlrow['omni_fees_upfront'];
		 						}
		 						$row['price_upfront_actual']=$price_setup;
		 					}

	 					}
			 			$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
						$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
						$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
						$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
						$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
						$campDetails[$row['campaignid']]['discount']=($dis_price);
						$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";



			 		}
			 		else if($row['campaignid']=='73'){
			 			if($this->type<2){

			 			$price_setup=0;
			 			if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1){
			 				continue;
			 				if($combo_fees>0)
				 			{
				 				if($this->combo=='1' || $this->combo==1 ){
				 					$ratioamt=(1/2);
				 				$price_setup=ceil($combo_fees*$ratioamt);
				 				}
				 				else{
				 					$ratioamt=(3/7);
				 					$price_setup=ceil($combo_fees*$ratioamt);
				 				}
				 				$row['price_upfront_actual']=($price_setup*12)-$websiteprice;
				 			}
				 			else{

				 				continue;
				 				$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
				 				$checkomniplussql="select * from tbl_combo_pricing  where campaignid='73' and combo='combo1' and city='".$scity."'";
		 					$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
 					 			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
 								{
 									$price_setup=$checkomniplussqlrow['ecs_upfront'];
 								}
 									$row['price_upfront_actual']=($price_setup)-$websiteprice;
				 			}

				 			}


				 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
					 			$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['fees'];
			 						}
			 						$row['price_upfront_actual']=$price_setup;
			 					}
		 					}
				 			$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
							$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
							$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
							$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
							$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
							$campDetails[$row['campaignid']]['discount']=($dis_price);
							$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";
				 		}



			 		}
			 		else{
						if($this->type>2 && $row['campaignid']!='75'  && $row['campaignid']!='84'){
			 				continue;
			 			}
			 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
			 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
	 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
		 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
		 			 		{
		 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
		 						{
		 							$price_setup=$checkjdrrplussqlrow['setupfees'];
		 						}
		 						$row['price_upfront_actual']=$price_setup;
		 					}
 						}

			 			$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
						$campDetails[$row['campaignid']]['campaignid']=$row['campaignid'];
						$dis_price=$row['price_upfront_display']-$row['price_upfront_actual'];
						$campDetails[$row['campaignid']]['price']=$row['price_upfront_display'];
						$campDetails[$row['campaignid']]['offer_price']=$row['price_upfront_actual'];
						$campDetails[$row['campaignid']]['discount']=($dis_price);
						@$campDetails[$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_upfront_display'])*100)."%";
					}


				}

			}
 		}
 		return $campDetails;


	}
	function financeDisplayEcs(){
		$campaigns=$this->getCampaignForParentid();
		$sql="select *  from tbl_finance_omni_flow_display where campaignid in ($campaigns) ORDER BY FIELD(campaignid,$campaigns)";
		$res = parent::execQuery($sql, $this->conn_temp);
		$campDetails=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
			{
				$campDetails[$row['campaignid']]['campaign_name']=$row['campaign_name'];
				if($row['campaignid']=='1' || $row['campaignid']=='2'){
			 		$sqlbudget="select budget  from tbl_companymaster_finance_temp where parentid='".$this->parentid."'  and recalculate_flag=1 and campaignid ='". $row['campaignid']."'";
					$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
			 		if($resbudget && mysql_num_rows($resbudget) )
			 		{
		 				while($rowbudget=mysql_fetch_assoc($resbudget)){
		 					$emimonthly=($rowbudget['budget']/12);

		 					$campDetails['advance'][$row['campaignid']]['price']=($emimonthly*3);
		 					$campDetails['monthly'][$row['campaignid']]['price']=$emimonthly;

		 				}
			 		}
				}
				else{

							$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*3);
		 					$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
				}

			}
 		}

 		return $campDetails;


	}
	function financeDisplayEcsWithOffer(){
		$city_jdrr 	= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$campaigns=$this->getCampaignForParentid();

		$combo_fees=0;
		$domain_field_incl=0;

		$national_main = 0;
		$sql_nationallisting = "SELECT * FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid= '".$this->parentid."' and balance > 0";
		$res_nationallisting = parent::execQuery($sql_nationallisting, $this->conn_temp);
		if($res_nationallisting && mysql_num_rows($res_nationallisting))
		{
			$national_main = 1;
		}


		if( $this->module=='me' || $this->module=='ME' ||$this->module=='tme' || $this->module=='TME' )
		{
			$sql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sql, $this->conn_temp);

			if($res && mysql_num_rows($res) >0)
			{
				while($row=mysql_fetch_assoc($res)){
					 $combo_fees=$row['fees'];
					$domain_field_incl=$row['domain_field_incl'];
				}
			}
		}
		$expired=0;
		$sqlpackweekly="select * from tbl_payment_type where parentid='".$this->parentid."' and find_in_set('package_expired',payment_type) <> 0 and find_in_set('omni',payment_type) = 0 and find_in_set('omni 3k',payment_type) = 0 and version='".$this->version."'";
		$respackweekly = parent::execQuery($sqlpackweekly, $this->conn_finance);
 		if($respackweekly && mysql_num_rows($respackweekly)>0)
 		{
 				$expired=1;
		}

		$payment_type_msg='';
		$getpackname="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
		$respackname = parent::execQuery($getpackname, $this->conn_finance);
 		if($respackname && mysql_num_rows($respackname)>0)
 		{
 				$rowpaymenttype=mysql_fetch_assoc($respackname);
 				$payment_type_msg=$rowpaymenttype['payment_type'];
		}

		$multiplier=0.25;
		$multiplier_banner=3;
		if($expired==1){
			$multiplier=0.5;
			$multiplier_banner=6;
		}


		if(strpos($payment_type_msg, 'mini_ecs') !== false){
				$multiplier=0.5;
		}
		$sql="select *  from tbl_finance_omni_flow_display where campaignid in ($campaigns) ORDER BY FIELD(campaignid,$campaigns)";
		$res = parent::execQuery($sql, $this->conn_temp);
		$campDetails=array();
		$banner_combo=0;
		$websiteprice=0;
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
			{

				if( (($row['campaignid']=='1' || $row['campaignid']=='2') && $this->type!= 4 && $this->type!= 5 && $this->type != 2) || $row['campaignid']=='74' || $row['campaignid']=='82' || $row['campaignid']=='83' || $row['campaignid']=='10' || $row['campaignid']=='86' || $row['campaignid']=='7'){
			 		$sqlbudget="select budget,duration  from tbl_companymaster_finance_temp where parentid='".$this->parentid."'  and recalculate_flag=1 and campaignid ='". $row['campaignid']."'";
					$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
			 		if($resbudget && mysql_num_rows($resbudget))
			 		{
		 				while($rowbudget=mysql_fetch_assoc($resbudget)){
		 					 if($row['campaignid']=='74' || $row['campaignid']=='82' || $row['campaignid']=='83' || $row['campaignid']=='86'){
		 					 	if($domain_field_incl=='1' || $domain_field_incl==1 )
		 					 	$websiteprice=ceil(($rowbudget['budget']));
		 					 	$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']));
		 					 	$campDetails['monthly'][$row['campaignid']]['price']=ceil(0);
		 					 	$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']));
		 					 	$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil(0);
		 					 	$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 					 	$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 					 	$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 					 	$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
	 					 	 	$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
	 					 	 	$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
	 					 		$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
	 					 		$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 					 }
		 					 else{
			 					$divconst=12;
			 					if($rowbudget['duration']=='365')
			 					$divconst=12;
			 					if($rowbudget['duration']=='180')
			 					$divconst=6;
			 					if($rowbudget['duration']=='90')
			 					$divconst=3;
			 					if($rowbudget['duration']=='3650' && $row['campaignid'] == 1){
									$multiplier = 0.16666;
								}
			 					if($rowbudget['duration']=='730'){
								$multiplier=0.25;
			 					$divconst=24;
								}
								if(strpos($payment_type_msg, 'mini_ecs') !== false && $row['campaignid']=='1'){

		 					 		$row['campaignid']='111';
		 					 		$divconst=12;
		 					 		$multiplier=1;
		 					 	}
			 					$emimonthly=($rowbudget['budget']/$divconst);
			 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);

								if($rowbudget['duration']=='3650' && ceil(($rowbudget['budget']*$multiplier)) < 5000) {
										$campDetails['advance'][$row['campaignid']]['price']=5000;
										$campDetails['advance'][$row['campaignid']]['offer_price']=5000;
								}else{
			 					$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*$multiplier));
			 					$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*$multiplier));
								}
			 					$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['discount']=(0);
			 					$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
			 					$campDetails['monthly'][$row['campaignid']]['discount']=(0);
			 					$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
			 					if($row['campaignid']=='10' ){

									//unset($campDetails['monthly'][1]);
		 							//unset($campDetails['advance'][1]);
		 							$row['campaignid']='10';
		 							$row['advance']['campaignid']='10';
		 							$row['monthly']['campaignid']='10';

		 							$rowbudget['budget']=$rowbudget['budget'];
		 							$row['campaign_name']='National Listing';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");

		 							if($campDetails['advance'][$row['campaignid']]['campaignid'] == '10' && $national_main==0)
									{
									$campDetails['advance'][$row['campaignid']]['nationallive'] = 1;
									}
									else if($campDetails['advance'][$row['campaignid']]['campaignid'] == '10' && $national_main==1)
									{
									$campDetails['advance'][$row['campaignid']]['nationallive'] = 0;
									}


		 							if($campDetails['advance'][$row['campaignid']]['campaignid'] == '10' && $this->national_change_state['state_change'] == 1)
									{
									$campDetails['advance'][$row['campaignid']]['national_change_state'] = 1;
									}
									else if($campDetails['advance'][$row['campaignid']]['campaignid'] == '10' && $this->national_change_state['state_change'] == 0)
									{
									$campDetails['advance'][$row['campaignid']]['national_change_state'] = 0;
									}





								}
			 					if(($this->combo=='1' || $this->combo==1 )&&  $row['campaignid']=='1' ){

		 							unset($campDetails['monthly'][1]);
		 							unset($campDetails['advance'][1]);
		 							$row['campaignid']=173;
		 							$row['advance']['campaignid']=173;
		 							$row['monthly']['campaignid']=173;
		 							$rowbudget['budget']=$rowbudget['budget']+1;
		 							$row['campaign_name']='Combo';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 						}
		 						if(($this->combo=='2' || $this->combo==2 )&&  $row['campaignid']=='1' ){

		 							unset($campDetails['monthly'][1]);
		 							unset($campDetails['advance'][1]);
		 							$row['campaignid']=273;
		 							$row['advance']['campaignid']=273;
		 							$row['monthly']['campaignid']=273;
		 							$rowbudget['budget']=$rowbudget['budget']+10;
		 							$row['campaign_name']='Combo';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']* $multiplier));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*$multiplier));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 						}
		 						if(($this->type=='11' ||  $this->type==11) && ($row['campaignid']=='2' || $row['campaignid']=='1' )){    
									unset($campDetails['monthly'][1]);
		 							unset($campDetails['advance'][1]);
		 							unset($campDetails['monthly'][2]);
		 							unset($campDetails['advance'][2]);
		 							$row['campaignid']=1173;
		 							$row['advance']['campaignid']=1173;
		 							$row['monthly']['campaignid']=1173;
		 							$rowbudget['budget']=$rowbudget['budget']+10;
		 							$row['campaign_name']='PDG festive Combo Offer';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']+=ceil(($rowbudget['budget']* $multiplier));
		 							$campDetails['monthly'][$row['campaignid']]['price']+=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']+=ceil(($rowbudget['budget']*$multiplier));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']+=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 						}
		 						if(($this->type=='12' ||  $this->type==12) && ($row['campaignid']=='10' || $row['campaignid']=='10' )){  
									unset($campDetails['monthly'][10]);
		 							unset($campDetails['advance'][10]);
		 							$row['campaignid']=1273;
		 							$row['advance']['campaignid']=1273;
		 							$row['monthly']['campaignid']=1273;
		 							$row['campaign_name']='National listing festive Combo Offer';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 						}
		 					}
		 				}
			 		}
				}else if($this->type >5 && ($row['campaignid']=='73' ||$row['campaignid']=='72') && ($this->type == 14 || $this->type == 15 || $this->type <10)){
					//~ $this->omni_type_set=1;
					$sql="select omniextradetails from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
					$omni_res = parent::execQuery($sql, $this->dbConDjds);
					if($omni_res && mysql_num_rows($omni_res)){
						while($omni_val = mysql_fetch_assoc($omni_res)){
							$omni_rates = json_decode($omni_val['omniextradetails'],1);
						}
						
						if($this->type== 6){
							$omni_camp = 740;
						}else if($this->type == 7){
							$omni_camp = 741;
						}else if($this->type == 8){
							$omni_camp = 742;
						}else if($this->type == 9){
							$omni_camp = 743;
						}else if($this->type == 10){
							$omni_camp = 744;
						}else if($this->type == 14){
							$omni_camp = 748;
						}else if($this->type == 15){
							$omni_camp = 749;
						}
						
						if(isset($omni_rates[$omni_camp])){
							$row['campaignid']= "273";
						 	$campDetails['advance'][$row['campaignid']]['campaign_name']="Website Creation Fee";
							$campDetails['monthly'][$row['campaignid']]['campaign_name']="Website Maintenance Fee";
							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
							$campDetails['advance'][$row['campaignid']]['price']=$omni_rates[$omni_camp]['down_payment'];
							$campDetails['monthly'][$row['campaignid']]['price']=$omni_rates[$omni_camp]['ecs'];
							$campDetails['advance'][$row['campaignid']]['offer_price']=$omni_rates[$omni_camp]['down_payment'];
							$campDetails['monthly'][$row['campaignid']]['offer_price']=$omni_rates[$omni_camp]['ecs'];
							$campDetails['advance'][$row['campaignid']]['discount']=(0);
							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 				}
		 				$customflag=$this->getCustomValues(1);
						if(isset($customflag['72'])){
							$campDetails['advance'][$row['campaignid']]['price']=$customflag['72'];
							$campDetails['advance'][$row['campaignid']]['offer_price']=$customflag['72'];
							$campDetails['monthly'][$row['campaignid']]['price']=$customflag['73'];
							$campDetails['monthly'][$row['campaignid']]['offer_price']=$customflag['73'];
						}
						
							
					}
				}else if(($this->type == 16 || $this->type == 17 ||  $this->type == 18) && ($row['campaignid']=='73' || $row['campaignid']=='72')){
					$jdrrcombo ="SELECT omni_fees_ecs+omni_fees_plus_banner+omni_fees_plus_jdrr+omni_monthly_fees_ecs AS total_rate,omni_combo_name  FROM tbl_omni_pricing WHERE city='".$this->data_city_cm."' AND omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombo, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0){
						while($jdrrcomboresrow=mysql_fetch_assoc($jdrrcombores))
						{
							$mon_val = ceil($jdrrcomboresrow['total_rate']/12);
							
							$campDetails['advance']['750']['price']=$mon_val*3;
							$campDetails['advance']['750']['offer_price']=$mon_val*3;
							$campDetails['monthly']['750']['price']=$mon_val;
							$campDetails['monthly']['750']['offer_price']=$mon_val;
							$campDetails['advance']['750']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$campDetails['monthly']['750']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
					
				}
					}
					$campDetails['advance']['750']['campaignid']=750;
					$campDetails['monthly']['750']['campaignid']=750;
					
					$campDetails['advance']['750']['discount']=0;
					$campDetails['advance']['750']['discount_percent']=0;
					$campDetails['monthly']['750']['discount']=0;
					$campDetails['monthly']['750']['discount_percent']=0;
								
				}else if($this->type == 19 && ($row['campaignid']=='73' || $row['campaignid']=='72')){  
					$jdrrcombo ="SELECT omni_fees_ecs+omni_fees_plus_banner+omni_fees_plus_jdrr+omni_monthly_fees_ecs AS total_rate,omni_combo_name  FROM tbl_omni_pricing WHERE city='".$this->data_city_cm."' AND omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombo, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0){
						while($jdrrcomboresrow=mysql_fetch_assoc($jdrrcombores))
						{
							$vip_adv = 9999;
							$mon_val = ceil(($jdrrcomboresrow['total_rate'] - $vip_adv)/12);
							$campDetails['advance']['753']['price']=$vip_adv;
							$campDetails['advance']['753']['offer_price']=$vip_adv;
							$campDetails['monthly']['753']['price']=$mon_val;
							$campDetails['monthly']['753']['offer_price']=$mon_val;
							$campDetails['advance']['753']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$campDetails['monthly']['753']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
						}
						$campDetails['advance']['753']['campaignid']=753;
						$campDetails['monthly']['753']['campaignid']=753;
						                           
						$campDetails['advance']['753']['discount']=0;
						$campDetails['advance']['753']['discount_percent']=0;
						$campDetails['monthly']['753']['discount']=0;
						$campDetails['monthly']['753']['discount_percent']=0;
					}
				}else if($this->type>1 && $this->omni_type_set==0 && $this->type!=11 && $this->type!=12 &&($row['campaignid']=='73' || $row['campaignid']=='1' || $row['campaignid']=='72')){

							$this->omni_type_set=1;
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
							
				 			$checkomnisql="select *  from tbl_omni_pricing where  city='".$omni_city."' and omni_type='".$this->type."'";
		 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
		 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
		 			 		{
		 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
		 						{
		 							$price_setup=$checkomnisqlrow['omni_monthly_fees'];
		 							$adv=$checkomnisqlrow['omni_fees_upfront'];
		 						 $price_setup=$price_setup/12;
		 						$row['price_ecs_display']=$price_setup;
		 						$row['price_ecs_actual']=$price_setup;
	 						 if($this->type==2)
			 						 $customflag=$this->checkCustom(2);
			 						else
			 							$customflag=$this->checkCustom();
	 						if($customflag['flag']['val']==1 && $this->type != 2){
								$row['price_ecs_actual']=(($customflag['data']['val']));
								$row['price_ecs_display']=(($customflag['data']['val']));
								$adv=$customflag['setup']['val'];
							}
			 				$row['campaignid']=$this->type."73";

			 				$row['campaignid']=$this->type."73";
					 		$campDetails['advance'][$row['campaignid']]['campaign_name']=$checkomnisqlrow['omni_combo_name'];
					 		$campDetails['monthly'][$row['campaignid']]['campaign_name']=$checkomnisqlrow['omni_combo_name'];

					 		$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
					 		$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
					 		$multiplier=3;
					 		if($this->type==2){
					 			$multiplier=0;
					 		}
				 			$dis_price_adv=($row['price_ecs_display']*$multiplier)-($row['price_ecs_actual']*$multiplier);
							$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier)+$adv;
							$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier)+$adv;
							$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
							$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier))*100)."%";


							$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
							$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
							$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
							$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
							$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
							}
						}
	 				}
				else{
							if($row['campaignid']=='75' ){

									$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']))*100)."%";


								$campDetails['monthly'][$row['campaignid']]['price']=0;
								$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
								$campDetails['monthly'][$row['campaignid']]['discount']=0;
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
								continue;
							}
							if($row['campaignid']=='72' ){
								if($this->type<2){
					 			$price_setup=0;
								if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1)
				 				{

				 					$row['price_ecs_actual']=20000;
				 				}
					 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
						 			$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
				 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
				 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
				 			 		{
				 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
				 						{
				 							$price_setup=$checkomnisqlrow['setupfees'];
				 						}
				 						$row['price_ecs_actual']=$price_setup;
				 					}
			 					}
			 					if($this->type==2){
					 			$checkomnisql="select * from tbl_omni_pricing where  city='".$this->data_city_cm."' and omni_type='".$this->type."'";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['omni_fees_upfront'];
			 						}
			 						$row['price_ecs_actual']=$price_setup;
			 					}

		 						}
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']))*100)."%";


								$campDetails['monthly'][$row['campaignid']]['price']=0;
								$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
								$campDetails['monthly'][$row['campaignid']]['discount']=0;
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
								}
							}else if($row['campaignid']=='73'){

								if($this->type<2){

								$price_setup=0;
								if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1){
				 				if($combo_fees>0){
				 					continue;
				 					if($this->combo=='1' || $this->combo==1 ){
					 					$ratioamt=(1/2);
					 					$price_setup=ceil($combo_fees*$ratioamt);
					 				}
					 				else{
					 					$ratioamt=(3/7);
					 					$price_setup=ceil($combo_fees*$ratioamt);
					 				}
					 				$row['price_ecs_actual']=($price_setup*12)-$websiteprice;
 								}
 								else{
 									continue;
					 			$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
				 				$checkomniplussql="select * from tbl_combo_pricing  where campaignid='73' and combo='combo1' and city='".$scity."'";
		 					$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
 					 			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
 								{
 									$price_setup=$checkomniplussqlrow['ecs_upfront'];
 								}
 								$row['price_ecs_actual']=($price_setup)-$websiteprice;
				 				}
				 			}
								if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
					 			$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
									$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
									if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
							 		{
							 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
										{
											$price_setup=$checkomnisqlrow['fees'];
										}
										$row['price_ecs_actual']=($price_setup);
									}
							}

							if($this->type>1){
					 			/*$checkomnisql="select * from tbl_omni_pricing where  city='".$this->data_city_cm."' and omni_type='".$this->type."'";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['omni_monthly_fees_ecs'];
			 						}
			 						$row['price_ecs_actual']=$price_setup;
			 					}

		 						}
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$row['price_ecs_display']=$row['price_ecs_display']/12;
								$row['price_ecs_actual']=$row['price_ecs_actual']/12;
								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['price']=0;
								if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1 ||$this->type>1){
									$campDetails['advance'][$row['campaignid']]['offer_price']=0;
									$campDetails['advance'][$row['campaignid']]['discount']=0;
									$campDetails['advance'][$row['campaignid']]['discount_percent']=0;
									$dis_price_adv=($row['price_ecs_display']*3)-($row['price_ecs_actual']*3);
									$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*3);
									$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*3);
									$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
									$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*3))*100)."%";*/


								}
								else{
									$row['price_ecs_display']=$row['price_ecs_display']/12;
									$row['price_ecs_actual']=$row['price_ecs_actual']/12;
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['advance'][$row['campaignid']]['offer_price']=0;
								$campDetails['advance'][$row['campaignid']]['price']=0;
								$campDetails['advance'][$row['campaignid']]['discount']=0;
								$campDetails['advance'][$row['campaignid']]['discount_percent']=0;
								}



								$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
								$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
								if(isset($this->type) && $this->type == 1){
									unset($campDetails['monthly'][1]);
									unset($campDetails['advance'][1]);
								}
								
								}
							}
							else if(($row['campaignid']=='5' || $row['campaignid']=='22') && ($this->type!=5 && $this->type!=11 && $this->type!=12 && $this->type != 16 && $this->type !=17 && $this->type !=18 && $this->type !=19)){ 
								$sqlcombocheck="select * from campaigns_selected_status where selected=1 and parentid='".$this->parentid."' and campaignid in (5,22)";
									$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
									 $sqlget_check="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('22','5')";
								$res_combocheck = parent::execQuery($sqlget_check, $this->conn_temp);
								$flag225=true;
								if($res_combocheck && mysql_num_rows($res_combocheck)>0)
						 		{
						 			$flag225=false;
						 		}
						 		
						 		$sql_jdrr_budget="select budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid ='22'";
								$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->conn_finance_temp);
								if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget))
								{
									$row_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);
									$current_jdrr_budget = $row_jdrr_budget['budget'];
								}
								
						 		if($rescombo && mysql_num_rows($rescombo)==2 && $flag225)
						 		{
						 			if($banner_combo!=1){

										$sql225="select *  from tbl_finance_omni_flow_display where campaignid='225'";
										$res225 = parent::execQuery($sql225, $this->conn_temp);
										if($res225 && mysql_num_rows($res225))
								 		{
								 			while($row225=mysql_fetch_assoc($res225))
											{

												if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
							 			//$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225' and city='".$city_jdrr."'";
							 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225'";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['fees'];
						 						}
						 						$row225['price_ecs_actual']=$price_setup;
						 					}
				 						}
				 						if($this->combo=='2' || $this->combo==2){
								 			if($combo_fees>0)
				 							{

							 					$ratioamt=(1/7);
							 					$price_setup=ceil($combo_fees*$ratioamt);
								 				$row225['price_ecs_actual']=($price_setup);
				 							}else{

				 							$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
								 			$checkjdrrplussql="select * from tbl_combo_pricing  where campaignid='225' and combo='combo2' and city='".$scity."'";
						 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
				 					 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
				 								{
				 									$price_setup=$checkjdrrplussqlrow['ecs_price'];
				 								}
				 								$row225['price_ecs_actual']=$price_setup;
				 							}
				 						}
												$campDetails['advance'][$row225['campaignid']]['campaign_name']=$row225['campaign_name'];
												$campDetails['monthly'][$row225['campaignid']]['campaign_name']=$row225['campaign_name'];
												$campDetails['advance'][$row225['campaignid']]['campaignid']=$row225['campaignid'];
												$campDetails['monthly'][$row225['campaignid']]['campaignid']=$row225['campaignid'];
												$row225['price_ecs_actual']=$row225['price_ecs_actual']+1;
												$dis_price_adv=($row225['price_ecs_display']*12)-($row225['price_ecs_actual']*12);
												$campDetails['advance'][$row225['campaignid']]['price']=($row225['price_ecs_display']*12);
												$campDetails['advance'][$row225['campaignid']]['offer_price']=($row225['price_ecs_actual']*12);
												$campDetails['advance'][$row225['campaignid']]['discount']=($dis_price_adv);
												$campDetails['advance'][$row225['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row225['price_ecs_display']*12))*100)."%";
												
												if(count($campDetails['advance'][225])>0 && $campDetails['advance'][225]['offer_price'] < $current_jdrr_budget)
												{
													$campDetails['advance'][225]['offer_price'] = $current_jdrr_budget + 8;
												}


											/*	$dis_price=$row225['price_ecs_display']-$row225['price_ecs_actual'];
												$campDetails['monthly'][$row225['campaignid']]['price']=$row225['price_ecs_display'];
												$campDetails['monthly'][$row225['campaignid']]['offer_price']=$row225['price_ecs_actual'];
												$campDetails['monthly'][$row225['campaignid']]['discount']=($dis_price);
												$campDetails['monthly'][$row225['campaignid']]['discount_percent']=ceil(($dis_price/$row225['price_ecs_display'])*100)."%";*/
												$dis_price=0;
												$campDetails['monthly'][$row225['campaignid']]['price']=0;
												$campDetails['monthly'][$row225['campaignid']]['offer_price']=0;
												$campDetails['monthly'][$row225['campaignid']]['discount']=0;
												$campDetails['monthly'][$row225['campaignid']]['discount_percent']=0;

											}
										}
									}
									$banner_combo=1;
						 		}
						 		else{  
				 			 			if($this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
				 			 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['fees'];
						 						}
						 						$row['price_ecs_actual']=$price_setup;
						 						$row['price_ecs_display']=$price_setup;
						 					}
				 						}
				 			 			$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
										$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
										if($row['campaignid']=='22'){
											$dis_price_adv=($row['price_ecs_display']*12)-($row['price_ecs_actual']*12);
											$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*12);
											$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*12);
											$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
											$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*12))*100)."%";
												$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
											$campDetails['monthly'][$row['campaignid']]['price']=0;
											$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
											$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
											$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
										}else{  
										$dis_price_adv=0;
										//~ $campDetails['advance'][$row['campaignid']]['price']="3000";
										//~ $campDetails['advance'][$row['campaignid']]['offer_price']="3000";
										$dis_price_adv=($row['price_ecs_display']*$multiplier_banner)-($row['price_ecs_actual']*$multiplier_banner); 
										$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier_banner); 
                                        $campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier_banner); 
                                        $campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
										//~ $campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier_banner))*100)."%";
										$campDetails['advance'][$row['campaignid']]['discount_percent']="0%";
										$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
										$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
										$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
										$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
										$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
									}
									
									if(count($campDetails['advance'][22])>0 && $campDetails['advance'][22]['offer_price'] < $current_jdrr_budget)
									{
										$campDetails['advance'][22]['offer_price'] = $current_jdrr_budget;
									}



						 		}
					 		}
							else{
								if($this->type>2){
				 				continue;
				 			}
								if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
								$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
				 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
					 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
					 			 		{
					 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
					 						{
					 							$price_setup=$checkjdrrplussqlrow['fees'];
					 						}
					 						$row['price_ecs_actual']=$price_setup;
					 					}
			 					}
						
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

								$dis_price_adv=($row['price_ecs_display']*$multiplier_banner)-($row['price_ecs_actual']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								@$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier_banner))*100)."%";


								$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
								$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
								@$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
							}
				}
				
			}
 		}

 		return $campDetails;


	}
	function financeDisplayEcsAdv(){
		$city_jdrr 	= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$campaigns=$this->getCampaignForParentid();
		$combo_fees=0;
		$domain_field_incl=0;

		if( $this->module=='me' || $this->module=='ME' ||$this->module=='tme' || $this->module=='TME' )
		{
			$sql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sql, $this->conn_temp);

			if($res && mysql_num_rows($res) >0)
			{
				while($row=mysql_fetch_assoc($res)){
					 $combo_fees=$row['fees'];
					$domain_field_incl=$row['domain_field_incl'];
				}
			}
		}
		$expired=0;
		$sqlpackweekly="select * from tbl_payment_type where parentid='".$this->parentid."' and find_in_set('package_expired',payment_type) <> 0 and find_in_set('omni',payment_type) = 0 and find_in_set('omni 3k',payment_type) = 0 and  version='".$this->version."'";
		$respackweekly = parent::execQuery($sqlpackweekly, $this->conn_finance);
 		if($respackweekly && mysql_num_rows($respackweekly)>0)
 		{
 			$expired=1;
		}
		$multiplier=0.25;
		$multiplier_banner=3;
		if($expired==1){
			$multiplier=0.5;
			$multiplier_banner=6;
		}
		$payment_type_msg='';
		$getpackname="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
		$respackname = parent::execQuery($getpackname, $this->conn_finance);
 		if($respackname && mysql_num_rows($respackname)>0)
 		{
 				$rowpaymenttype=mysql_fetch_assoc($respackname);
 				$payment_type_msg=$rowpaymenttype['payment_type'];
		}
		if(strpos($payment_type_msg, 'mini_ecs') !== false){
				$multiplier=0.5;
		}
		$sql="select *  from tbl_finance_omni_flow_display where campaignid in ($campaigns) ORDER BY FIELD(campaignid,$campaigns)";
		$res = parent::execQuery($sql, $this->conn_temp);
		$total_budget=array();
		$campDetails=array();
		$websiteprice=0;
		$payment_type_msg='';
				$getpackname="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
				$respackname = parent::execQuery($getpackname, $this->conn_finance);
		 		if($respackname && mysql_num_rows($respackname)>0)
		 		{
		 				$rowpaymenttype=mysql_fetch_assoc($respackname);
		 				$payment_type_msg=$rowpaymenttype['payment_type'];
				}
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
			{

				if( (($row['campaignid']=='1' || $row['campaignid']=='2') && $this->type!= 4 && $this->type!= 5 && $this->type!= 2) || $row['campaignid']=='74'  || $row['campaignid']=='82' || $row['campaignid']=='83' || $row['campaignid']=='10' || $row['campaignid']=='86' || $row['campaignid']=='7' ){
			 		$sqlbudget="select budget,duration  from tbl_companymaster_finance_temp where parentid='".$this->parentid."'  and recalculate_flag=1 and campaignid ='". $row['campaignid']."'";
					$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
			 		if($resbudget && mysql_num_rows($resbudget) )
			 		{
		 				while($rowbudget=mysql_fetch_assoc($resbudget)){
		 					 if($row['campaignid']=='74' || $row['campaignid']=='82' || $row['campaignid']=='83' || $row['campaignid']=='86'){
		 					 	if($domain_field_incl==1)
		 					 	$websiteprice=ceil(($rowbudget['budget']));
		 					 	$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']));
		 					 	$campDetails['monthly'][$row['campaignid']]['price']=ceil(0);
		 					 	$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']));
		 					 	$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil(0);
		 					 	$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 					 	$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 					 	$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 					 	$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
		 					 	$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 					 	$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 						$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 						$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 						$total_budget['advance']+=ceil(($rowbudget['budget']));
		 					 }
		 					 else{
			 					$divconst=12;
			 					if($rowbudget['duration']=='365')
			 					$divconst=12;
			 					if($rowbudget['duration']=='180')
			 					$divconst=6;
			 					if($rowbudget['duration']=='90')
			 					$divconst=3;
			 					if($rowbudget['duration']=='730'){
				 					$divconst=24;
				 					$multiplier=0.25;
			 					}
			 					if($rowbudget['duration']=='3650' && $row['campaignid'] == 1){
				 					$multiplier = 0.16666;
			 					}
								if(strpos($payment_type_msg, 'mini_ecs') !== false && $row['campaignid']=='1'){

									$row['campaignid']='111';
									$divconst=12;
		 					 		$multiplier=1;
								}

			 					$emimonthly=($rowbudget['budget']/$divconst);
			 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
								$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
								if($rowbudget['duration']=='3650' && $row['campaignid'] == 1 && ceil(($rowbudget['budget']*$multiplier)) < 5000){
									$campDetails['advance'][$row['campaignid']]['price']=5000;
									$campDetails['advance'][$row['campaignid']]['offer_price']=5000;
									$total_budget['advance']+=5000;
								}else{
			 						$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*$multiplier));
			 					$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*$multiplier));
			 						$total_budget['advance']+=ceil(($rowbudget['budget']*$multiplier));
								}
			 					
			 					$total_budget['monthly']+=($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['discount']=(0);
			 					$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
			 					$campDetails['monthly'][$row['campaignid']]['discount']=(0);
			 					$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
			 					if($row['campaignid']=='10' ){

									//unset($campDetails['monthly'][1]);
		 							//unset($campDetails['advance'][1]);
		 							$row['campaignid']='10';
		 							$row['advance']['campaignid']='10';
		 							$row['monthly']['campaignid']='10';

		 							$rowbudget['budget']=$rowbudget['budget'];
		 							$row['campaign_name']='National Listing';
		 							$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
								}
			 					if($this->combo=='1' || $this->combo==1){
			 						$total_budget['advance']-=ceil(($rowbudget['budget']*.25));
			 						$total_budget['monthly']-=($emimonthly);
			 						unset($campDetails['monthly'][1]);
			 						unset($campDetails['advance'][1]);
			 						$row['campaignid']=173;
			 						$row['advance']['campaignid']=173;
			 						$row['monthly']['campaignid']=173;
			 						$emimonthly=($rowbudget['budget']/$divconst);
			 						$row['campaign_name']='Combo';
			 						$rowbudget['budget']=$rowbudget['budget']+1;
			 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
			 					$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
			 					$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
			 					$total_budget['advance']+=ceil(($rowbudget['budget']*.25));
			 					$total_budget['monthly']+=($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['discount']=(0);
			 					$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
			 					$campDetails['monthly'][$row['campaignid']]['discount']=(0);
			 					$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
			 					}
			 					if($this->combo=='2' || $this->combo==2){
			 						$total_budget['advance']-=ceil(($rowbudget['budget']*.25));
			 						$total_budget['monthly']-=($emimonthly);
			 						unset($campDetails['monthly'][1]);
			 						unset($campDetails['advance'][1]);
			 						$row['campaignid']=273;
			 						$row['advance']['campaignid']=273;
			 						$row['monthly']['campaignid']=273;
			 						$emimonthly=($rowbudget['budget']/$divconst);
			 						$row['campaign_name']='Combo';
			 						$rowbudget['budget']=$rowbudget['budget']+10;
			 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
			 					$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
			 					$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
			 					$total_budget['advance']+=ceil(($rowbudget['budget']*.25));
			 					$total_budget['monthly']+=($emimonthly);
			 					$campDetails['advance'][$row['campaignid']]['discount']=(0);
			 					$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
			 					$campDetails['monthly'][$row['campaignid']]['discount']=(0);
			 					$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
			 					}
			 					
			 					
								if(($this->type=='11' || $this->type==11) && ($row['campaignid']=='2' || $row['campaignid']=='1' )){
									unset($campDetails['monthly'][1]);
									unset($campDetails['advance'][1]);
									unset($campDetails['advance'][2]);
									unset($campDetails['monthly'][2]);
									$row['campaignid'] = 1173	;
									$row['campaign_name']='PDG Festive Combo Offer';
									$emimonthly=($rowbudget['budget']/$divconst);
									$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
									$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
									$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
									$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

									$campDetails['advance'][$row['campaignid']]['price']+=ceil(($rowbudget['budget']*$multiplier));
									$campDetails['monthly'][$row['campaignid']]['price']+=ceil($emimonthly);

									$campDetails['advance'][$row['campaignid']]['offer_price']+=ceil(($rowbudget['budget']*$multiplier));
									$campDetails['monthly'][$row['campaignid']]['offer_price']+=ceil($emimonthly);
									$campDetails['advance'][$row['campaignid']]['discount']=(0);
									$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
									$campDetails['monthly'][$row['campaignid']]['discount']=(0);
									$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
								}
								
								if(($this->type=='12' || $this->type==12) && $row['campaignid']=='10'){
									unset($campDetails['monthly'][10]);
									unset($campDetails['advance'][10]);
									$row['campaignid'] = 1273	;
									$row['campaign_name']='National Listing festive Combo Offer';
									$emimonthly=($rowbudget['budget']/$divconst);
		 							$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
		 							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
		 							$campDetails['advance'][$row['campaignid']]['price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['offer_price']=ceil(($rowbudget['budget']*.25));
		 							$campDetails['monthly'][$row['campaignid']]['offer_price']=ceil($emimonthly);
		 							$campDetails['advance'][$row['campaignid']]['discount']=(0);
		 							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
		 							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
		 							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
								}
								
								
								
								
			 					
		 					}
		 				}
			 		}
				}else if($this->type >5 && ($row['campaignid']=='73' ||$row['campaignid']=='72') && ($this->type == 14 || $this->type == 15 || $this->type <10) && $this->omni_type_set==0){
					$this->omni_type_set=1;
					$sql="select omniextradetails from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";
					$omni_res = parent::execQuery($sql, $this->dbConDjds);
					if($omni_res && mysql_num_rows($omni_res)){
						while($omni_val = mysql_fetch_assoc($omni_res)){
							$omni_rates = json_decode($omni_val['omniextradetails'],1);
						}
						
						if($this->type== 6){
							$omni_camp = 740;
						}else if($this->type == 7){
							$omni_camp = 741;
						}else if($this->type == 8){
							$omni_camp = 742;
						}else if($this->type == 9){
							$omni_camp = 743;
						}else if($this->type == 10){
							$omni_camp = 744;
						}else if($this->type == 14){
							$omni_camp = 748;
						}else if($this->type == 15){
							$omni_camp = 749;
						}
						
						if(isset($omni_rates[$omni_camp])){
							$row['campaignid']= "73";
						 	$campDetails['advance'][$row['campaignid']]['campaign_name']="Website Creation Fee";
							$campDetails['monthly'][$row['campaignid']]['campaign_name']="Website Maintenance Fee";
							$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
							$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
							$campDetails['advance'][$row['campaignid']]['price']=$omni_rates[$omni_camp]['down_payment'];
							$campDetails['monthly'][$row['campaignid']]['price']=$omni_rates[$omni_camp]['ecs'];
							$campDetails['advance'][$row['campaignid']]['offer_price']=$omni_rates[$omni_camp]['down_payment'];
							$campDetails['monthly'][$row['campaignid']]['offer_price']=$omni_rates[$omni_camp]['ecs'];;
							$campDetails['advance'][$row['campaignid']]['discount']=(0);
							$campDetails['advance'][$row['campaignid']]['discount_percent']=("0%");
							$campDetails['monthly'][$row['campaignid']]['discount']=(0);
							$campDetails['monthly'][$row['campaignid']]['discount_percent']=("0%");
						}
		 				$customflag=$this->getCustomValues(1);
		 				if(isset($customflag['72'])){
							$campDetails['advance'][$row['campaignid']]['price']=$customflag['72'];
							$campDetails['advance'][$row['campaignid']]['offer_price']=$customflag['72'];
							$campDetails['monthly'][$row['campaignid']]['price']=$customflag['73'];
							$campDetails['monthly'][$row['campaignid']]['offer_price']=$customflag['73'];
							$total_budget['advance']+=$customflag['72'];
			 				$total_budget['monthly']+=$customflag['73'];
						}else {
							$total_budget['advance']+=$omni_rates[$omni_camp]['down_payment'];
			 				$total_budget['monthly']+=$omni_rates[$omni_camp]['ecs'];
			 			}
						
							
					}
				}else if(($this->type == 16 || $this->type == 17 ||  $this->type == 18) && $row['campaignid']=='73'){
					$jdrrcombo ="SELECT omni_fees_ecs+omni_fees_plus_banner+omni_fees_plus_jdrr+omni_monthly_fees_ecs AS total_rate,omni_combo_name  FROM tbl_omni_pricing WHERE city='".$this->data_city_cm."' AND omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombo, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0){
						while($jdrrcomboresrow=mysql_fetch_assoc($jdrrcombores))
						{
							$mon_val = ceil($jdrrcomboresrow['total_rate']/12);
							
							$campDetails['advance']['750']['price']=$mon_val*3;
							$campDetails['advance']['750']['offer_price']=$mon_val*3;
							$campDetails['monthly']['750']['price']=$mon_val;
							$campDetails['monthly']['750']['offer_price']=$mon_val;
							$campDetails['advance']['750']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$campDetails['monthly']['750']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$campDetails['advance']['750']['price']=$mon_val*3;
							$campDetails['advance']['750']['offer_price']=$mon_val*3;
							$campDetails['monthly']['750']['price']=$mon_val;
							$campDetails['monthly']['750']['offer_price']=$mon_val;
							$total_budget['advance']+=$mon_val*3;
							$total_budget['monthly']+=$mon_val;
					
					
						}
					}
					
					$campDetails['advance']['750']['discount']=0;
					$campDetails['advance']['750']['discount_percent']=0;
					$campDetails['monthly']['750']['discount']=0;
					$campDetails['monthly']['750']['discount_percent']=0;
					
				}else if($this->type == 19 && $row['campaignid']=='73'){
					$jdrrcombo ="SELECT omni_fees_ecs+omni_fees_plus_banner+omni_fees_plus_jdrr+omni_monthly_fees_ecs AS total_rate,omni_combo_name  FROM tbl_omni_pricing WHERE city='".$this->data_city_cm."' AND omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombo, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0){
						while($jdrrcomboresrow=mysql_fetch_assoc($jdrrcombores))
						{
							$vip_adv = 9999;
							$mon_val = ceil(($jdrrcomboresrow['total_rate']-$vip_adv)/12);
							
							$campDetails['advance']['753']['price']=$vip_adv;
							$campDetails['advance']['753']['offer_price']=$vip_adv;
							$campDetails['monthly']['753']['price']=$mon_val;
							$campDetails['monthly']['753']['offer_price']=$mon_val;
							$campDetails['advance']['753']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$campDetails['monthly']['753']['campaign_name']= $jdrrcomboresrow['omni_combo_name'];
							$total_budget['advance']+=$vip_adv;
							$total_budget['monthly']+=$mon_val;
					
					
						}
					}
					
					$campDetails['advance']['753']['discount']=0;
					$campDetails['advance']['753']['discount_percent']=0;
					$campDetails['monthly']['753']['discount']=0;
					$campDetails['monthly']['753']['discount_percent']=0;
					
				}else if($this->type>1 && $this->omni_type_set==0 && $this->type!= 11 && $this->type!= 12  && $this->type!= 16 && $this->type!= 17 && $this->type!= 18 && $this->type!= 19 && ($row['campaignid']=='73' || $row['campaignid']=='1' || $row['campaignid']=='72') ){
	 						$this->omni_type_set=1;
						$total_budget['advance'];
						
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
							
			 			$checkomnisql="select * from tbl_omni_pricing where  city='".$omni_city."' and omni_type='".$this->type."'";
	 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
	 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
	 			 		{
	 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
	 						{
	 							$price_setup=$checkomnisqlrow['omni_monthly_fees'];
	 							$adv=$checkomnisqlrow['omni_fees_upfront'];


	 						$row['price_ecs_display']=$price_setup/12;
		 					$row['price_ecs_actual']=$price_setup/12;
		 					 if($this->type==2)
			 						 $customflag=$this->checkCustom(2);
			 						else
			 							$customflag=$this->checkCustom();
	 						if($customflag['flag']['val']==1 && $this->type!= 2){
								$row['price_ecs_display']=(($customflag['data']['val']));
								$row['price_ecs_actual']=(($customflag['data']['val']));
								$adv=$customflag['setup']['val'];
							}
	 					$row['campaignid']=$this->type."73";
					 	$campDetails['advance'][$row['campaignid']]['campaign_name']=$checkomnisqlrow['omni_combo_name'];
					 		$campDetails['monthly'][$row['campaignid']]['campaign_name']=$checkomnisqlrow['omni_combo_name'];
					 	$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
					 		$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

					 	$multiplier=3;
					 		if($this->type==2){
					 			$multiplier=0;
					 		}

		 				$row['campaignid']=$this->type."73";
			 			$dis_price_adv=($row['price_ecs_display']*$multiplier)-($row['price_ecs_actual']*$multiplier)+$adv;
						$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier)+$adv;
						$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier)+$adv;
						$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
						$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier))*100)."%";


						$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
						$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
						$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
						$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
						$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
							$total_budget['advance']+=(($row['price_ecs_actual']*$multiplier)+$adv);
							$total_budget['monthly']+=$row['price_ecs_actual'];
						}
					}
 				}
				else{

							if($row['campaignid']=='75'){

								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								@$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']))*100)."%";


								$campDetails['monthly'][$row['campaignid']]['price']=0;
								$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
								$campDetails['monthly'][$row['campaignid']]['discount']=0;
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
								$total_budget['advance']+=($row['price_ecs_actual']);
								$total_budget['monthly']+=0;
								continue;
							}

							if($row['campaignid']=='72'){
								if($this->type<2){
								if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1){

									$row['price_ecs_actual']=20000;
								}
								if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
									$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
									$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
									if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
							 		{
							 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
										{
											$price_setup=$checkomnisqlrow['setupfees'];
										}
										$row['price_ecs_actual']=$price_setup;
									}
								}
		 					if($this->type>1 ){

					 			$checkomnisql="select * from tbl_omni_pricing where  city='".$this->data_city_cm."' and omni_type='".$this->type."'";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['omni_fees_upfront'];
			 						}
			 						$row['price_ecs_actual']=$price_setup;
			 					}

		 					}
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								@$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']))*100)."%";


								$campDetails['monthly'][$row['campaignid']]['price']=0;
								$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
								$campDetails['monthly'][$row['campaignid']]['discount']=0;
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
								$total_budget['advance']+=($row['price_ecs_actual']);
								$total_budget['monthly']+=0;
								}


							}
							else if($row['campaignid']=='73'){
								if($this->type<2){

								if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
									$checkomnisql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='72'";
									$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
									if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
							 		{
							 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
										{
											$price_setup=$checkomnisqlrow['fees'];
										}
										$row['price_ecs_actual']=($price_setup);
									}
								}
								if($this->combo=='2' || $this->combo==2 ||$this->combo=='1' || $this->combo==1){
					 			if($combo_fees>0){
					 				continue;
				 					if($this->combo=='1' || $this->combo==1 ){
					 					$ratioamt=(1/2);
					 					$price_setup=ceil($combo_fees*$ratioamt);
					 				}
					 				else{
					 					$ratioamt=(3/7);
					 					$price_setup=ceil($combo_fees*$ratioamt);
					 				}
					 				$row['price_ecs_actual']=($price_setup*12)-$websiteprice;
 								}else{
 									continue;
 									$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
					 			$checkomniplussql="select * from tbl_combo_pricing  where campaignid='73' and combo='combo1' and city='".$scity."'";
			 					$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
	 					 			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
	 								{
	 									$price_setup=$checkomniplussqlrow['ecs_upfront'];
	 								}
	 								$row['price_ecs_actual']=($price_setup)-$websiteprice;
	 							}
					 			}
					 			if($this->type>1){
					 			$checkomnisql="select * from tbl_omni_pricing where  city='".$this->data_city_cm."' and omni_type='".$this->type."'";
			 					$checkomnisqlres = parent::execQuery($checkomnisql, $this->conn_temp);
			 					if($checkomnisqlres && mysql_num_rows($checkomnisqlres)>0)
			 			 		{
			 			 			while($checkomnisqlrow=mysql_fetch_assoc($checkomnisqlres))
			 						{
			 							$price_setup=$checkomnisqlrow['omni_monthly_fees_ecs'];
			 						}
			 						$row['price_ecs_actual']=$price_setup;
			 					}

		 						}
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
								$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$row['price_ecs_display']=$row['price_ecs_display']/12;
								$row['price_ecs_actual']=$row['price_ecs_actual']/12;
								$dis_price_adv=($row['price_ecs_display'])-($row['price_ecs_actual']);
								if($this->combo=='2' || $this->combo==2 || $this->combo=='1' || $this->combo==1 || $this->type>1){
								/*	$campDetails['advance'][$row['campaignid']]['offer_price']=0;
									$campDetails['advance'][$row['campaignid']]['discount']=0;
									$campDetails['advance'][$row['campaignid']]['discount_percent']=0;
									$dis_price_adv=($row['price_ecs_display']*3)-($row['price_ecs_actual']*3);
									$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*3);
									$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*3);
									$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
									$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*3))*100)."%";
									$total_budget['advance']+=($row['price_ecs_actual']*3);*/

								}
								else{
								$campDetails['advance'][$row['campaignid']]['price']=0;
								$campDetails['advance'][$row['campaignid']]['offer_price']=0;
								$campDetails['advance'][$row['campaignid']]['discount']=0;
								$campDetails['advance'][$row['campaignid']]['discount_percent']=0;
								$total_budget['advance']+=(0);
								}

								$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
								$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";


								$total_budget['monthly']+=($row['price_ecs_actual']);
								
								$total_budget['advance']-=$campDetails['advance'][1]['offer_price'];
								$total_budget['monthly']-=$campDetails['monthly'][1]['offer_price'];
								unset($campDetails['monthly'][1]);
								unset($campDetails['advance'][1]);
				
								}
							}
							else if(($row['campaignid']=='5' || $row['campaignid']=='22') && ($this->type!=5 && $this->type!=11   && $this->type!=12 && $this->type != 16 && $this->type != 17 &&  $this->type != 18 &&  $this->type != 19) ){
								//print_r($row);
								$sql_jdrr_budget="select budget,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."'  and campaignid ='22'";
								$res_jdrr_budget = parent::execQuery($sql_jdrr_budget, $this->conn_finance_temp);
								if($res_jdrr_budget && mysql_num_rows($res_jdrr_budget))
								{
									$row_jdrr_budget = mysql_fetch_assoc($res_jdrr_budget);
									$current_jdrr_budget = $row_jdrr_budget['budget'];
								}
								
								
								$sqlcombocheck="select jdrr_banner_combo from campaigns_selected_status where selected=1 and parentid='".$this->parentid."' and campaignid='".$row['campaignid']."' and jdrr_banner_combo=1";

								$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
						 		if($rescombo && mysql_num_rows($rescombo) >0)
						 		{

						 			if($banner_combo!=1){

										$sql225="select *  from tbl_finance_omni_flow_display where campaignid='225'";
										$res225 = parent::execQuery($sql225, $this->conn_temp);
										if($res225 && mysql_num_rows($res225))
								 		{

								 			while($row225=mysql_fetch_assoc($res225))
											{
												if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
							 			//$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225' and city='".$city_jdrr."'";
							 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225' ";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['fees'];
						 						}
						 						$row225['price_ecs_actual']=$price_setup;
						 					}
				 						}
				 						if($this->combo=='2' || $this->combo==2){
						 			if($combo_fees>0)
				 							{

							 					$ratioamt=(1/7);
							 					$price_setup=ceil($combo_fees*$ratioamt);
								 				$row225['price_ecs_actual']=ceil($price_setup);
				 							}
				 					else{
				 						$scity 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
						 				$checkomniplussql="select * from tbl_combo_pricing  where campaignid='225' and combo='combo2' and city='".$city."'";

					 					$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
			 					 			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
			 								{
			 									$price_setup=$checkomniplussqlrow['ecs_upfront'];
			 								}
			 								$row225['price_ecs_actual']=($price_setup/12);
							 			}
						 			}
												$campDetails['advance'][$row225['campaignid']]['campaign_name']=$row225['campaign_name'];
												$campDetails['monthly'][$row225['campaignid']]['campaign_name']=$row225['campaign_name'];
												$campDetails['advance'][$row225['campaignid']]['campaignid']=$row225['campaignid'];
												$campDetails['monthly'][$row225['campaignid']]['campaignid']=$row225['campaignid'];

												/*$dis_price_adv=($row225['price_ecs_display']*3)-($row225['price_ecs_actual']*3);
												$campDetails['advance'][$row225['campaignid']]['price']=($row225['price_ecs_display']*3);
												$campDetails['advance'][$row225['campaignid']]['offer_price']=($row225['price_ecs_actual']*3);
												$campDetails['advance'][$row225['campaignid']]['discount']=($dis_price_adv);
												$campDetails['advance'][$row225['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row225['price_ecs_display']*3))*100)."%";*/
												$row225['price_ecs_actual']=$row225['price_ecs_actual']+1;
													$dis_price_adv=($row225['price_ecs_display']*12)-($row225['price_ecs_actual']*12);
												$campDetails['advance'][$row225['campaignid']]['price']=($row225['price_ecs_display']*12);
												$campDetails['advance'][$row225['campaignid']]['offer_price']=($row225['price_ecs_actual']*12);
												$campDetails['advance'][$row225['campaignid']]['discount']=($dis_price_adv);
												$campDetails['advance'][$row225['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row225['price_ecs_display']*12))*100)."%";
										
												
												/*$dis_price=$row225['price_ecs_display']-$row225['price_ecs_actual'];
												$campDetails['monthly'][$row225['campaignid']]['price']=$row225['price_ecs_display'];
												$campDetails['monthly'][$row225['campaignid']]['offer_price']=$row225['price_ecs_actual'];
												$campDetails['monthly'][$row225['campaignid']]['discount']=($dis_price);
												$campDetails['monthly'][$row225['campaignid']]['discount_percent']=ceil(($dis_price/$row225['price_ecs_display'])*100)."%";
												$total_budget['advance']+=($row225['price_ecs_actual']*3);
			 									$total_budget['monthly']+=($row225['price_ecs_actual']);*/
			 									$dis_price=$row225['price_ecs_display']-$row225['price_ecs_actual'];
												$campDetails['monthly'][$row225['campaignid']]['price']=0;
												$campDetails['monthly'][$row225['campaignid']]['offer_price']=0;
												$campDetails['monthly'][$row225['campaignid']]['discount']=($dis_price);
												$campDetails['monthly'][$row225['campaignid']]['discount_percent']=0;
												
												if(count($campDetails['advance'][225])>0 && $campDetails['advance'][225]['offer_price'] < $current_jdrr_budget)
												{
													$total_budget['advance'] += $current_jdrr_budget + 8;
													$campDetails['advance'][225]['offer_price'] = $current_jdrr_budget + 8;
												}else
													$total_budget['advance'] += ($row225['price_ecs_actual']*12);
												
			 									$total_budget['monthly']+=(0);
			 									

											}
										}
									}
									$banner_combo=1;
						 		}
						 		else{
				 			 			if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
				 			 			$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['fees'];
						 						}
						 						$row['price_ecs_actual']=$price_setup;
						 					}
				 						}

					 				if($row['campaignid']=='22'){
					 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
										$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

										$dis_price_adv=($row['price_ecs_display']*12)-($row['price_ecs_actual']*12);
										$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*12);
										$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*12);
										$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
										$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*12))*100)."%";


										$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
										$campDetails['monthly'][$row['campaignid']]['price']=0;
										$campDetails['monthly'][$row['campaignid']]['offer_price']=0;
										$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
										$campDetails['monthly'][$row['campaignid']]['discount_percent']=0;
										
										if(count($campDetails['advance'][22])>0 && $campDetails['advance'][22]['offer_price'] < $current_jdrr_budget)
											$total_budget['advance']+= $current_jdrr_budget;
										else
											$total_budget['advance']+=($row['price_ecs_actual']*12);
										
					 					$total_budget['monthly']+=(0);
					 					
					 				}else{
					 					$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
										$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
										$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];

										$dis_price_adv=($row['price_ecs_display']*$multiplier_banner)-($row['price_ecs_actual']*$multiplier_banner);
										$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier_banner);
										$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier_banner);
										$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
										$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier_banner))*100)."%";


										$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
										$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
										$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
										$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
										$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($dis_price/$row['price_ecs_display'])*100)."%";
										
										if(count($campDetails['advance'][22])>0 && $campDetails['advance'][22]['offer_price'] < $current_jdrr_budget)
										{
											$campDetails['advance'][22]['offer_price'] = $current_jdrr_budget;
											$total_budget['advance']+=($current_jdrr_budget);
										}else
										{
											$total_budget['advance']+=($row['price_ecs_actual']*$multiplier_banner);
										}
										
											$total_budget['monthly']+=($row['price_ecs_actual']);
					 				}
					 				
					 				if(count($campDetails['advance'][22])>0 && $campDetails['advance'][22]['offer_price'] < $current_jdrr_budget)
									{
										$campDetails['advance'][22]['offer_price'] = $current_jdrr_budget;
									}


						 		}
					 		}
							else{
								if($this->type>2){
				 				continue;
				 			}
								if( $this->module=='me' || $this->module=='ME' || $this->module=='tme' || $this->module=='TME' ){
								$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='".$row['campaignid']."'";
					 					$checkjdrrplussqlres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
						 					if($checkjdrrplussqlres && mysql_num_rows($checkjdrrplussqlres)>0)
						 			 		{
						 			 			while($checkjdrrplussqlrow=mysql_fetch_assoc($checkjdrrplussqlres))
						 						{
						 							$price_setup=$checkjdrrplussqlrow['fees'];
						 						}
						 						$row['price_ecs_actual']=$price_setup;
						 					}
				 						}
								$campDetails['advance'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['monthly'][$row['campaignid']]['campaign_name']=$row['campaign_name'];
			 					$campDetails['advance'][$row['campaignid']]['campaignid']=$row['campaignid'];
			 					$campDetails['monthly'][$row['campaignid']]['campaignid']=$row['campaignid'];
								$dis_price_adv=($row['price_ecs_display']*$multiplier_banner)-($row['price_ecs_actual']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['price']=($row['price_ecs_display']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['offer_price']=($row['price_ecs_actual']*$multiplier_banner);
								$campDetails['advance'][$row['campaignid']]['discount']=($dis_price_adv);
								$campDetails['advance'][$row['campaignid']]['discount_percent']=ceil(($dis_price_adv/($row['price_ecs_display']*$multiplier_banner))*100)."%";


								$dis_price=$row['price_ecs_display']-$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['price']=$row['price_ecs_display'];
								$campDetails['monthly'][$row['campaignid']]['offer_price']=$row['price_ecs_actual'];
								$campDetails['monthly'][$row['campaignid']]['discount']=($dis_price);
								//echo ($row['price_ecs_actual']."====".$row['price_upfront_display'])."<br>";
								$campDetails['monthly'][$row['campaignid']]['discount_percent']=ceil(($row['price_ecs_actual']/$row['price_ecs_display'])*100)."%";

								$total_budget['advance']+=($row['price_ecs_actual']*3);
			 					$total_budget['monthly']+=($row['price_ecs_actual']);
		 					}

				}

			}
		}
 		$campDetails['total']['advance']=ceil($total_budget['advance']);
 		$campDetails['total']['monthly']=ceil($total_budget['monthly']);
 		$total_adv_tax=($total_budget['advance']* $this->service_tax);
 		$total_emi_tax=($total_budget['monthly']* $this->service_tax);
 		$campDetails['tax']['advance']=ceil($total_adv_tax);
 		$campDetails['tax']['monthly']=ceil($total_emi_tax);
 		$campDetails['total_payable']['advance']=ceil($total_budget['advance'] + $total_adv_tax);
 		$campDetails['total_payable']['monthly']=ceil($total_budget['monthly'] + $total_emi_tax);

 		return $campDetails;


	}
	function getCampaignForParentid(){

 		$campids='';
 		$sql="select campaignid  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."' and recalculate_flag=1 and campaignid in (1,2,10,7) and budget>0";
		$res = parent::execQuery($sql, $this->conn_finance_temp);
 		if($res && mysql_num_rows($res) )
 		{
 				while($row=mysql_fetch_assoc($res)){
 					$campids.=",".$row['campaignid'];
 				}
 		}
 		$campids=ltrim($campids,',');

		$sql="select group_concat(\"'\",campaignid,\"'\" order by campaignid desc) as campaignids from campaigns_selected_status where selected=1 and parentid='".$this->parentid."' order by campaignid desc";
		$res = parent::execQuery($sql, $this->conn_temp);
 		if($res && mysql_num_rows($res) )
 		{
 				$row=mysql_fetch_assoc($res);
 				$campids.=",".$row['campaignids'];
 		}

 		$campids=trim($campids,',');
 		if(trim($campids)==''){
	 		$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Campaigns Selected";
			echo json_encode($result_msg_arr);
			exit;
 		}
 		return $campids;
	}

	function addJdCampaign(){

 		$sql="update tbl_companymaster_finance_temp set recalculate_flag=1 where  parentid='".$this->parentid."' and version='".$this->version."' and campaignid in (1,2)";
		$res = parent::execQuery($sql, $this->conn_finance_temp);
		return 'Sucess';
	}
	function deleteJdCampaign(){

		require_once('miscapijdaclass.php');
		require_once('versioninitclass.php');

		$miscapijdaclass_obj = new miscapijdaclass($this->params);

		$result = $miscapijdaclass_obj->updatefinancetempTable('1,2,7');

 		$sqlcheckfornational="select * from tbl_payment_type where (find_in_set('nl_1yr_discount',payment_type) <> 0 or  find_in_set('nl_2_yrs',payment_type) <> 0)  and parentid='".$this->parentid."' and version='".$this->version."' ";
		$resnational = parent::execQuery($sqlcheckfornational, $this->conn_finance);

 		if($resnational && mysql_num_rows($resnational))
 		{
	 		while($rownational=mysql_fetch_assoc($resnational))
	 		{

	 			$payment_type=explode(',', $rownational['payment_type']);
	 			foreach ($payment_type as $keynational => $valuenational) {
	 				if($valuenational!='nl_1yr_discount' && $valuenational!='nl_2_yrs'){
	 					unset($payment_type[$keynational]);

	 				}
	 			}

	 			 $payment_type=implode(',',$payment_type);

	 			$sql="update tbl_payment_type set payment_type='".$payment_type."' where  parentid='".$this->parentid."' and version='".$this->version."'";
				$res = parent::execQuery($sql, $this->conn_finance);
	 		}
	 	}
	 	else{

 			$sql="update tbl_payment_type set payment_type='' where  parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sql, $this->conn_finance);
		}
		$sqlban="DELETE FROM catspon_banner_rotation_temp WHERE  parentid='".$this->parentid."'";
		$resban = parent::execQuery($sqlban, $this->conn_temp_new);
 		//$sql="update tbl_companymaster_finance_temp set recalculate_flag=0 where  parentid='".$this->parentid."' and campaignid in (1,2)";
		//$res = parent::execQuery($sql, $this->conn_finance_temp);

		return 'Success';
	}

	function deleteNationalcampaign(){
		$sql="update tbl_companymaster_finance_temp set recalculate_flag=0  where parentid='".$this->parentid."' and campaignid in (10)";
		$res = parent::execQuery($sql, $this->conn_temp);

		//$sql="delete from tbl_national_listing_temp where parentid='".$this->parentid."'";
		//$res = parent::execQuery($sql, $this->conn_temp);
	}
	function deleteAsReq(){

		require_once('miscapijdaclass.php');
		require_once('versioninitclass.php');
		if($this->campaignid==''){
	 		$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Campaign Id Not found";
			echo json_encode($result_msg_arr);
			exit;
		}
		$miscapijdaclass_obj = new miscapijdaclass($this->params);

		$result = $miscapijdaclass_obj->updatefinancetempTable($this->campaignid);
 		//~ $sql="update tbl_payment_type set payment_type='' where  parentid='".$this->parentid."' and version='".$this->version."'";
		//~ $res = parent::execQuery($sql, $this->conn_finance);

 		//$sql="update tbl_companymaster_finance_temp set recalculate_flag=0 where  parentid='".$this->parentid."' and campaignid in (1,2)";
		//$res = parent::execQuery($sql, $this->conn_finance_temp);
		return 'Success';
	}

	function deleteCampaignAll(){

		require_once('miscapijdaclass.php');
		require_once('versioninitclass.php');

		$miscapijdaclass_obj = new miscapijdaclass($this->params);

		$result = $miscapijdaclass_obj->updatefinancetempTable();


 		$sql="update campaigns_selected_status set selected=0,jdrr_banner_combo=0 where  parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);

		$sql="delete from dependant_campaign_details_temp where  parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		
		$delpaytype = "delete from tbl_payment_type where  parentid='".$this->parentid."' and version='".$this->version."'";
		$respaytype = parent::execQuery($delpaytype, $this->conn_finance);

		$fields	= array('parentid'	=>urlencode($this->parentid),
						's_deptCity'=>urlencode($this->data_city),
						'module'	=>$this->module,
						'type'		=>'5',
						'state'		=> 1,
						'action'	=> 'mainToTemp');


			$city_arr_ipname = array (
									"ahmedabad" 	=> AHMEDABAD_CS_API,
									"bangalore" 	=> BANGALORE_CS_API,
									"chennai" 		=> CHENNAI_CS_API,
									"delhi"			=> DELHI_CS_API,
									"hyderabad" 	=> HYDERABAD_CS_API,
									"kolkata" 		=> KOLKATA_CS_API,
									"pune" 			=> PUNE_CS_API,
									"mumbai"  		=> MUMBAI_CS_API,
									"remote"  		=> REMOTE_CITIES_CS_API);

		if($_SERVER['SERVER_ADDR'] != '172.29.64.64'){

				 $url ='http://'.$city_arr_ipname[strtolower($this->data_city_cm)].'/business/bannerservice.php';

		}else{
			$url 	= "http://prameshjha.jdsoftware.com/CSGENIO/business/bannerservice.php" ;
		}

		$this->curlCall($url,$fields);



		return 'Success';


	}
	function curlCall($url,$params,$sso = 0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		
		if($sso == 1) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
							'Content-Type: application/json',                                                                                
							'Content-Length: ' . strlen($params),
							'HR-API-AUTH-TOKEN:'.md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s"))); 
		}
		
		$resultString = curl_exec($ch);
		curl_close($ch);
		$resultString= trim($resultString);
		$dicountallowed = $resultString;
		return $dicountallowed;
	}

	function applyDiscount(){

		$getmaxdiscount=$this->getDiscountPercentage();
 		if($this->discount>$getmaxdiscount && $getmaxdiscount!=null){
	 		$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Discount Cant be more than ".$getmaxdiscount;
			echo json_encode($result_msg_arr);
			exit;
		}
 		/*$sql="select *  from omni_flow_discount where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql, $this->conn_finance_temp);
 		if($res && mysql_num_rows($res) )
 		{
 				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Discount Already Applied";
				echo json_encode($result_msg_arr);
				exit;



 		}
 		else{*/


 		$sql="select budget,campaignid,original_actual_budget  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."' and recalculate_flag=1 and campaignid in (1,2) and budget>0";
		$res = parent::execQuery($sql, $this->conn_finance_temp);
 		if($res && mysql_num_rows($res))
 		{
	 		while($row=mysql_fetch_assoc($res))
	 		{

	 				if($this->discount=='0' || $this->discount==0){
	 					$sql_disc ="delete from omni_flow_discount where parentid='".$this->parentid."' and version='".$this->version."'";
	 					$res_disc = parent::execQuery($sql_disc, $this->conn_temp);
	 					$sqlupdt="update tbl_companymaster_finance_temp set budget=original_actual_budget,original_budget=original_actual_budget where  parentid='".$this->parentid."'  and campaignid='".$row['campaignid']."'";
					$resupdt = parent::execQuery($sqlupdt, $this->conn_finance_temp);

	 				}else{
	 				$budget=$row['original_actual_budget'];
	 			 	$sql_disc = "INSERT INTO omni_flow_discount set
										parentid='".$this->parentid."',
										version='".$this->version."',
										userid='".$this->usercode."',
										discount_percent='".$this->discount."'
										ON DUPLICATE KEY UPDATE
										userid='".$this->usercode."',
										discount_percent='".$this->discount."'";
					$res_disc = parent::execQuery($sql_disc, $this->conn_temp);
	 				$dis_amt=$budget*($this->discount /100 );
	 				$new_budget=ceil($budget-$dis_amt);
			 		$sqlupdt="update tbl_companymaster_finance_temp set budget='".$new_budget."', original_budget='".$new_budget."' where  parentid='".$this->parentid."'  and campaignid='".$row['campaignid']."'";
					$resupdt = parent::execQuery($sqlupdt, $this->conn_finance_temp);
			 		}
	 		}
 		}
 			return 'Sucess';
 		/*if($resupdt)
 		else
 			return 'failure';*/

	}

	function getDiscountPercentage(){
		$sql_finance = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' AND recalculate_flag ='1'";
		$res_finance = parent::execQuery($sql_finance, $this->conn_finance_temp);
		$city=$this->data_city;
		if($res_finance && mysql_num_rows($res_finance)>0){

		   $discount_arr = $this->getCampaignWiseDiscount($city,$this->remote_flag);
		   $total_budget = 0;
		   $durationFlag = 0;
		   while($row_finance = mysql_fetch_assoc($res_finance)){
				if($row_finance['budget']>0 && $row_finance['duration']<365){
					//$durationFlag = 1;
				}
				if($row_finance['campaignid'] == 1 && $row_finance['budget']>0){
					$package_budget = $row_finance['budget'];
				}
				if($row_finance['campaignid'] == 2 && $row_finance['budget']>0){
					$platinum_budget= $row_finance['budget'];
				}
				if($row_finance['campaignid'] == 29 && $row_finance['budget']>0){
	                $shop_front_budget= $row_finance['budget'];
	            }

				$total_budget += $row_finance['budget'];

			 }

				 if($durationFlag){/*discount to be given if tenure is one year*/
				 	return 0;
				}else if($platinum_budget>0 && $total_budget>=$discount_arr[2]['minbudget']){
					return  $discount_arr[2]['discount'];
				}else if($package_budget>0 && $total_budget >= $discount_arr[1]['minbudget']){
					return $discount_arr[1]['discount'];
				}
				else if($shop_front_budget>0 && $shop_front_budget == $total_budget && $total_budget >= $discount_arr[29]['minbudget']){
	                return $discount_arr[29]['discount'];
	            }
				else if($total_budget >= $discount_arr[1]['minbudget']){
	                return $discount_arr[1]['discount'];
	            }
	            return null;

		}
	}

	function getCampaignWiseDiscount($city,$remote_flg){
		if($remote_flg){
			if(!in_array($city,array('Coimbatore','Chandigarh','Jaipur'))){
				$city='other_cities';
			}
		}
		$sql = "select * from tbl_city_campaign_percentage_slabs where city='".$city."'";
		$res = parent::execQuery($sql, $this->conn_finance);
		if($res && mysql_num_rows($res)){
			$discount_arr = array();
			while($row = mysql_fetch_assoc($res)){
				$discount_arr[$row['campaignId']]['minbudget'] = $row['amount'];
				$discount_arr[$row['campaignId']]['discount']  = $row['discount'];
			}
			return $discount_arr;
		}
	}

	function checkOmniDependent($live=0,$type=2){
		if($live==1)
			$tbl="dependant_campaign_details";
		else
		$tbl="dependant_campaign_details_temp";
		$sqlcheck="select * from $tbl where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sqlcheck, $this->conn_temp);
		$cnt=0;
		if($res && mysql_num_rows($res)){
			$returnarr['msg']['dependent_present']=1;
			while($row = mysql_fetch_assoc($res)){
				$returnarr['data'][$cnt]['combo_type']=$row['combo_type'];
				$returnarr['data'][$cnt]['pri_campaignid']=$row['pri_campaignid'];
				$returnarr['data'][$cnt]['dep_campaignid']=$row['dep_campaignid'];
				$cnt++;

			}
			return $returnarr;
		}
		else{
			$returnarr['msg']['dependent_present']=0;
			return $returnarr;
		}



	}
	function checkActiveEcs(){
		if(trim($this->module_name)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Name Is Missing";
			echo json_encode($result_msg_arr);exit;
		}
		$ecs_edit  = true;
		$banner_details=json_decode($banner_details,1);
		$banner_arr=$banner_arr+$banner_details;
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$row_ex = $this->mongo_obj->getData($mongo_inputs);
			$catids_from_temp=$row_ex['catIds'];
		}
		else
		{
			$sqlgetcat="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
			$res_ex   = parent::execQuery($sqlgetcat, $this->conn_temp);
			$catids_from_temp='';

			if($res_ex && mysql_num_rows($res_ex)>0)
			{
				$row_ex = mysql_fetch_assoc($res_ex);
				$catids_from_temp=$row_ex['catIds'];
			}
		}

		$catids_from_temp=explode("|P|", $catids_from_temp);
		$catids_from_temp=array_filter($catids_from_temp);

		$arrcount=count($catids_from_temp);	
		$catids_from_temp_str=implode(',',$catids_from_temp);


		$gst_flag=0;

		//$sqlcheckgst="SELECT catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$catids_from_temp_str.") and misc_cat_flag&1024=1024"; 
			//$rescatflex   = parent::execQuery($sqlcheckgst, $this->dbConIro);
			$cat_params = array();
			$cat_params['page']= 'financeDisplayClass';
			$cat_params['skip_log']		= '1';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'catid';

			$where_arr  	=	array();
			if($catids_from_temp_str!=''){
				$where_arr['catid']			= $catids_from_temp_str;
				$where_arr['misc_cat_flag']	= '1024';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				$gst_flag=1;
								
			}

		$get_ecs_status = "SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."' AND deactiveflag = 0 AND ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1  UNION SELECT outlet_parentid,master_billdeskid from db_ecs.ecs_mandate_outlet WHERE outlet_parentid='".$this->parentid."' AND outlet_status IN (0,1) AND vertical_flag=0 LIMIT 1";
		$res_ecs_status = parent::execQuery($get_ecs_status, $this->conn_finance);

		if($res_ecs_status && mysql_num_rows($res_ecs_status))
		{
			$row_ecs_status = mysql_fetch_assoc($res_ecs_status);
			$ecs_edit = false;
		}
		else
		{
			$get_si_status = "SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='".$this->parentid."' and deactiveflag = 0 and ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1 ";
			$res_si_status = parent::execQuery($get_si_status, $this->conn_finance);
			if($res_si_status && mysql_num_rows($res_si_status))
			{
				$row_si_status = mysql_fetch_assoc($res_si_status);
				$ecs_edit = false;
			}
		}
		/*if(strtoupper(trim($this->module_name))=='ME' || strtoupper(trim($this->module_name))=='TME'){*/
		$jdrr_set = 0;
		$banner_set = 0;
		$check_campaign ="SELECT campaignid  FROM campaigns_selected_status WHERE parentid='".$this->parentid."' and selected = 1";
		$campaign_status_res = parent::execQuery($check_campaign, $this->conn_temp);
		//73 ,22
		if($campaign_status_res && mysql_num_rows($campaign_status_res)>0 && mysql_num_rows($campaign_status_res) <=2) {
			while($sel_campaign = mysql_fetch_assoc($campaign_status_res)){
					if($sel_campaign['campaignid'] == '22') {
						$jdrr_set = 1;
					}else if($sel_campaign['campaignid'] == '5') {
						$banner_set = 1;
					}
				}

				$check_finance ="SELECT campaignid FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND recalculate_flag=1 and campaignid in (1,2)";
				$check_finance_res = parent::execQuery($check_finance, $this->conn_finance_temp);
				if(mysql_num_rows($check_finance_res) == 0) {
					if($jdrr_set == 1 && $banner_set == 1) {
						$ecs_edit  = true;
					}

					if($jdrr_set == 1 && mysql_num_rows($campaign_status_res) == 1) {
						$ecs_edit  = true;
					}
				}
			}

		/*}*/


		if($ecs_edit){
				if($gst_flag==1){
				$result_msg_arr['error']['code'] = 6; 
				$result_msg_arr['error']['msg'] = "GST Category Present. Ecs Blocked!";  
				echo json_encode($result_msg_arr);exit; 
				}
				else{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Not On Going Ecs Contract";
				echo json_encode($result_msg_arr);exit;
				}

		}
		else{
			if($gst_flag==1){
				$result_msg_arr['error']['code'] = 7; 
				$result_msg_arr['error']['msg'] = "On Going Ecs Contract,GST Category Present.Ecs Blocked!";  
				echo json_encode($result_msg_arr);exit; 
			}
			else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "On Going Ecs Contract";
			echo json_encode($result_msg_arr);exit;
			}

		}
	}
	function checkEcsEligibilty(){
		/*$dept=$this->checkOmniDependent(0,2);
		as it comming from temp table reading only from type
		if($dept['msg']['dependent_present']=='1' ||$dept['msg']['dependent_present']==1 ){
			foreach ($dept['data'] as $key => $value) {
				if($value['combo_type']=='Omni Ultima' || $this->type=='3'){
					$result_msg_arr['error']['code'] = 5;
					$result_msg_arr['error']['msg'] = "Cant Allow ECS";
					echo json_encode($result_msg_arr);exit;
				}
			}
		}*/
		if($this->type=='3'){
					$result_msg_arr['error']['code'] = 5;
					$result_msg_arr['error']['msg'] = "Cant Allow ECS";
					echo json_encode($result_msg_arr);exit;
		}
		$onlyomniflg=0;
		$sqlcombocheck="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid not in (72,73) and recalculate_flag=1";
		$rescombo = parent::execQuery($sqlcombocheck, $this->conn_finance_temp);
 		if(mysql_num_rows($rescombo) >0)
 		{
 			$onlyomniflg=1;
 		}

		$sqlcombocheck="select * from campaigns_selected_status where selected=1 and parentid='".$this->parentid."'";
		$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
 		if($rescombo && mysql_num_rows($rescombo)>0)
 		{
 			while($rescomborow=mysql_fetch_assoc($rescombo))
			{
				if($rescomborow['campaignid']=='5' || $rescomborow['campaignid']=='22' )
					$onlyomniflg=1;
			}

			// put temp for upload purpose
		}


		$sqlbudget="select * from   tbl_companymaster_finance_temp  where parentid='".$this->parentid."' and campaignid not in('72','73','56','5','13')  and recalculate_flag=1 and duration > 0 group by duration";
			$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
	 		if($resbudget && mysql_num_rows($resbudget) >1)
	 		{
 							$result_msg_arr['error']['code'] = 1;
 							$result_msg_arr['error']['msg'] = "Tenure Mismatch";
 							echo json_encode($result_msg_arr);exit;

 			}

 			$sqlbudget="select * from   tbl_companymaster_finance_temp  where parentid='".$this->parentid."' and campaignid not in('72','73','56','5','13') and (duration<180 and duration > 0) and recalculate_flag=1";
			$resbudget = parent::execQuery($sqlbudget, $this->conn_finance_temp);
	 		if($resbudget && mysql_num_rows($resbudget) >0)
	 		{
 							$result_msg_arr['error']['code'] = 2;
 							$result_msg_arr['error']['msg'] = "Tenure Less Than 180 Days";
 							echo json_encode($result_msg_arr);exit;

 			}
			$jddrcheck=0;
			$sqlcombocheck="select * from campaigns_selected_status where selected=1 and parentid='".$this->parentid."' and campaignid in ('72','73','74','2','1','5','13','75','84')";
			$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
	 		if($rescombo && mysql_num_rows($rescombo)>0)
	 		{

	 			$jddrcheck=0;
			}
			else{

				$jddrcheck=1;
			}

			$sqlcombocheck="select * from tbl_companymaster_finance_temp where recalculate_flag=1 and parentid='".$this->parentid."' and campaignid in ('72','73','74','2','1','5','13','10','75','84')";
			$rescombo = parent::execQuery($sqlcombocheck, $this->conn_temp);
	 		if($rescombo && mysql_num_rows($rescombo)>0)
	 		{

	 			$jddrcheck=0;
			}
			if($jddrcheck==1){

				$result_msg_arr['error']['code'] = 3;
				$result_msg_arr['error']['msg'] = "Cant Allow ECS";
				echo json_encode($result_msg_arr);exit;
				// put temp for upload purpose
			}

			$sqlpackweekly="select * from tbl_payment_type where parentid='".$this->parentid."' and (payment_type='package_weekly' or payment_type='package_monthly') and version='".$this->version."'";
			$respackweekly = parent::execQuery($sqlpackweekly, $this->conn_finance);
	 		if($respackweekly && mysql_num_rows($respackweekly)>0)
	 		{
	 			$result_msg_arr['error']['code'] = 4;
				$result_msg_arr['error']['msg'] = "Cant Allow Upfront";
				echo json_encode($result_msg_arr);exit;
			}
 			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Upfront Eligible";
			echo json_encode($result_msg_arr);exit;
	}

	function apiCalledAlwaysOmniFlow(){
			//$sqlupdt="update tbl_companymaster_finance_temp set original_actual_budget=budget where  parentid='".$this->parentid."' and campaignid in (1,2) and original_actual_budget=0";
			$sqlupdt="update tbl_companymaster_finance_temp set original_actual_budget=budget where  parentid='".$this->parentid."' and original_actual_budget=0";
			$resupdt = parent::execQuery($sqlupdt, $this->conn_finance_temp);
			if($resupdt){
		 			$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['msg'] = "Success";
					echo json_encode($result_msg_arr);exit;
			}
			else{

		 			$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Failure";
					echo json_encode($result_msg_arr);exit;

			}
	}
	function apiGetComboPriceForPackage(){

		if($this->combo==2 || $this->combo=='2' || $this->combo==1 || $this->combo=='1'){
				$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
				$getsql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
				$resget = parent::execQuery($getsql, $this->conn_temp);
				if($resget && mysql_num_rows($resget) >0)
		 		{
		 			while($resrow=mysql_fetch_assoc($resget)){
		 				$fees=$resrow['fees'];
		 			}
		 			if($this->combo==2 || $this->combo=='2'){
		 				$ratioamt=(3/7);
		 				$price_list=ceil($fees*$ratioamt);
		 			}
		 			else if($this->combo==1 || $this->combo=='1'){
						$ratioamt=(1/2);
						$price_list=ceil($fees);
						$ful_fees=($fees*12)-1;
		 			}
		 			$result_msg_arr['error']['code'] = 0;
		 			$price_list_ar['1']=$price_list;
		 			$price_list_ar['1_full']=$ful_fees;
					$result_msg_arr['error']['msg'] = $price_list_ar;
					echo json_encode($result_msg_arr);exit;

			 	}
				else{
					$combo=$this->combo==2?'combo2':'combo1';
					$sql = "select * from tbl_combo_pricing where city='".$data_city."' and campaignid='1' and combo='".$combo."'";
					$res = parent::execQuery($sql, $this->conn_temp);
					$price_list=array();
					if($res && mysql_num_rows($res)>0){
						while($row = mysql_fetch_assoc($res)){

							$price_list[$row['campaignid']]= ceil($row['ecs_upfront']);
							if($combo=='combo2'){
								$price_list[$row['campaignid']."_full"]= ceil($row['ecs_upfront']*12);
							}
							else
							$price_list[$row['campaignid']."_full"]= ceil($row['ecs_upfront']*12);
						}
			 			$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = $price_list;
						echo json_encode($result_msg_arr);exit;
					}
					else{
					 			$result_msg_arr['error']['code'] = 1;
								$result_msg_arr['error']['msg'] = "Error!";
								echo json_encode($result_msg_arr);exit;
					}
 				}
	 	}
	 	else{
	 			$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Combo No Spl Price";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function saveCustComboDetails(){

		if( $this->type>1 && $this->custom_setup_fees!=-1)
		{

			$sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_combo_budget set
					 					parentid='".$this->parentid."',
					 					version='".$this->version."',
					 					combo='".$this->type."',
					 					domain_field_incl='".$this->domain_field_incl."',
					 					setup_fees='".$this->custom_setup_fees."',
					 					fees  	= '".$this->combo_cust_price."'
					 					ON DUPLICATE KEY UPDATE
					 					domain_field_incl='".$this->domain_field_incl."',
					 					setup_fees='".$this->custom_setup_fees."',
					 					combo='".$this->type."',
					 					fees  	= '".$this->combo_cust_price."'";
			$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
			$sql_ins_temp_omni_log = "INSERT INTO tbl_custom_omni_combo_budget_log set
					 					parentid='".$this->parentid."',
					 					version='".$this->version."',
					 					combo='".$this->type."',
					 					setup_fees='".$this->custom_setup_fees."',
					 					domain_field_incl='".$this->domain_field_incl."',
					 					fees  	= '".$this->combo_cust_price."'";
			$res_del_temp_omni_log = parent::execQuery($sql_ins_temp_omni_log, $this->conn_temp);
			if($res_del_temp_omni){
				 			$result_msg_arr['error']['code'] = 0;
							$result_msg_arr['error']['msg'] = "Success";
							echo json_encode($result_msg_arr);exit;
			}
			else{
		 			$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Error!";
					echo json_encode($result_msg_arr);exit;
			}
		}
		else{
	 			$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Combo price shld be non zero/combo flag is wrong/setup fees not passed";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function resetOmniCombo(){
		$sql="delete from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		if($sql){
	 			$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success!";
				echo json_encode($result_msg_arr);exit;
		}
		else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error!";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function getOmniComboValue(){
		$resarray=array();
		$sql="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql, $this->conn_temp);

		if($res && mysql_num_rows($res) >0)
 		{
 		  while($row=mysql_fetch_assoc($res)){
 		  	$fees=$row['fees'];
 		  	$domain_selected=$row['domain_field_incl'];
 		  	$combo=$row['combo'];
 		  }

 		  $resarray['combo_cost']=$fees;
 		  $resarray['domain_cost_incl']=$domain_selected;
 		  $resarray['combo']=$combo;
 		  	$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = $resarray;
			echo json_encode($result_msg_arr);exit;
 		}
 		else{
 				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Not Found";
				echo json_encode($result_msg_arr);exit;
 		}
	}
	function getOmniComboPrices(){
	$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
	$checkomniplussql="SELECT combo,SUM(ecs_price) as price_list FROM tbl_combo_pricing WHERE city='".$data_city."' AND campaignid IN ('73','225','1') group by combo";
	$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
		if($checkomniplussqlres && mysql_num_rows($checkomniplussqlres) >0){
			$returnarr=array();
			$count=0;
			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
			{

				$returnarr[$checkomniplussqlrow['combo']]['price']=floor($checkomniplussqlrow['price_list']);
				$returnarr[$checkomniplussqlrow['combo']]['combo']=$checkomniplussqlrow['combo'];
				$count++;
			}

				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = $returnarr;
				echo json_encode($result_msg_arr);exit;
		}
		else{
 				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Data Found";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function getOmniMinComboPrices(){
	$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
	$checkomniplussql="SELECT *  FROM min_budget_omni WHERE city='".$data_city."'";
	$checkomniplussqlres = parent::execQuery($checkomniplussql, $this->conn_temp);
		if($checkomniplussqlres && mysql_num_rows($checkomniplussqlres) >0){
			$returnarr=array();

			while($checkomniplussqlrow=mysql_fetch_assoc($checkomniplussqlres))
			{
				$returnarr['min_budget']=$checkomniplussqlrow['fees'];

			}

				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = $returnarr;
				echo json_encode($result_msg_arr);exit;
		}
		else{
 				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Data Found";
				echo json_encode($result_msg_arr);exit;
		}

	}
	function getPriceChart(){
		$where='';
		$desp='';
		if($this->payment_type=='1' || $this->payment_type==1 ){
			$where.=" where display_upfront=1";
			$desp='upfront_campaign_description';
		}
		if($this->payment_type=='2' || $this->payment_type==2 ){
			$where.=" where display_ecs=1";
			$desp='ecs_campaign_description';
		}
		$datarr=array();
		$sql="select *  from tbl_finance_omni_flow_display_new $where order by disp_order asc";
		$res = parent::execQuery($sql, $this->conn_temp);


		$sqlcheckfinance="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_finance_temp);
		$campaigns=array();
		if($resfinancetemp && mysql_num_rows($resfinancetemp))
			{
				while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
				{
					$campaigns[$rowfinancetemp['campaignid']]=$rowfinancetemp['campaignid'];
				}
			}
		if(isset($campaigns['73']) && !isset($campaigns['72']) ){
			unset($campaigns[73]);
			$campaigns['734']='734';
		}
		if(isset($campaigns['2']) && isset($campaigns['1'])){
			unset($campaigns[1]);
		}
		$selectcam="select * from campaigns_selected_status where parentid='".$this->parentid."' and jdrr_banner_combo=1";
		$rescam = parent::execQuery($selectcam, $this->conn_temp);
		if($rescam && mysql_num_rows($rescam)>0)
			{
				unset($campaigns[5]);
				unset($campaigns[13]);
				unset($campaigns[22]);
				$campaigns['225']='225';
			}
		$sqlcheckfinance="select * from dependant_campaign_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_temp);

		if($resfinancetemp && mysql_num_rows($resfinancetemp))
			{
				while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
				{
					if($rowfinancetemp['combo_type']=='Combo 2' ){
						unset($campaigns[1]);

						$campaigns['735']='735';
					}
					else if($rowfinancetemp['combo_type']=='Omni Supreme'){
						unset($campaigns[1]);
						$campaigns['731']='731';
					}
					else if($rowfinancetemp['combo_type']=='Omni Ultima'){
						unset($campaigns[73]);
						unset($campaigns[734]);
						$campaigns['732']='732';
					}
				}


		}

		$campDetails=array();
		if($res && mysql_num_rows($res))
			{
				while($row=mysql_fetch_assoc($res))
				{
						$type='';
						$money_saved=0;
						if($row['campaign_type']=='1')
							$type='Standard_Plans';
						if($row['campaign_type']=='2')
							$type='Recommended_Plans';

						$datarr[$type][$row['campaignid']]['name']=$row['campaign_name'];

						$datarr[$type][$row['campaignid']]['campaign_description']=$row[$desp];
						if($row['omni_type']!=null){
								$datarr[$type][$row['campaignid']]['omni_type']=$row['omni_type'];
							}
						if($this->payment_type=='1' || $this->payment_type==1){
							$datarr[$type][$row['campaignid']]['display_price']=$row['price_upfront_display'];
							$datarr[$type][$row['campaignid']]['actual_price']=$row['price_upfront_actual'];

							$datarr[$type][$row['campaignid']]['actual_price']=$row['price_upfront_actual'];
							$money_saved=$row['price_upfront_display']-$row['price_upfront_actual'];
							if($row['price_upfront_display']>0){
							$money_saved_percent=ceil((($money_saved/$row['price_upfront_display'])*100));
							}
							else{
								$money_saved_percent=0;
							}
							$datarr[$type][$row['campaignid']]['setup']=$row['setup_upfront'];
							$datarr[$type][$row['campaignid']]['money_saved']=$money_saved;
							$datarr[$type][$row['campaignid']]['money_saved_percent']=$money_saved_percent."%";

							if(filter_var($row['price_upfront_actual'], FILTER_VALIDATE_INT) !== false ){

							$datarr[$type][$row['campaignid']]['total']=($row['setup_upfront']+$row['price_upfront_actual']);
							}
							else
								$datarr[$type][$row['campaignid']]['total']=$row['price_upfront_actual'];


							$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);

						}
						else{
							$datarr[$type][$row['campaignid']]['setup']=$row['setup_ecs'];
							if(filter_var($row['price_ecs_actual'], FILTER_VALIDATE_FLOAT) !== false ){
							$datarr[$type][$row['campaignid']]['advance']=(int)$row['price_ecs_display'];
							$datarr[$type][$row['campaignid']]['emi']=(int)$row['price_ecs_actual'];
							}
							else{
								$datarr[$type][$row['campaignid']]['advance']=$row['price_ecs_display'];
								$datarr[$type][$row['campaignid']]['emi']=$row['price_ecs_actual'];
							}
							//$money_saved=$row['price_upfront_display']-$row['price_upfront_actual'];
							$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);
						}

				}
				$result_msg_arr['error']['code'] =0;
				$result_msg_arr['error']['msg'] = 'success';
				$result_msg_arr['data']['result'] =$datarr;
				header('Content-Type: application/json');
				echo json_encode($result_msg_arr);exit;
		}

	}
	function getBannerAvailibity($catidarr){
			//$catidarr is passed as array;
			$available	= array();
			if(count($catidarr)){
				$catidstr	= implode(",",$catidarr);
				if(trim($catidstr)!=''){
					$sql	= "SELECT catid,cat_sponbanner_inventory FROM tbl_cat_banner_bid WHERE catid IN (".$catidstr.") AND data_city='".$this->data_city."'";
					$qry= parent::execQuery($sql, $this->conn_finance);

					if($qry && mysql_num_rows($qry)){
						while($row = mysql_fetch_assoc($qry)){
							$available[$row['catid']] = (1 - $row['cat_sponbanner_inventory']);
						}
					}
				}

				$catid_frm_avail = array_keys($available);
				$diffarr		 = array_diff($catidarr,$catid_frm_avail);

				foreach($diffarr as $cat){
					$available[$cat]	= 1;
				}
			}
			$banner_avl=false;
			$arr_val=array_values($available);
			if(in_array(1, $arr_val)){
				$banner_avl=true;
			}
			return $banner_avl;
	}
	function checkCategoriesSanity($catid){
		$flag=false;
		$catid=implode(",", $catid);

		//$sql	= "SELECT catid,paid_clients,nonpaid_clients FROM tbl_categorymaster_generalinfo WHERE catid in (".$catid.")";
		//$qry= parent::execQuery($sql, $this->conn_catmaster);

		$cat_params = array();
		$cat_params['page']= 'financeDisplayClass';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid,paid_clients,nonpaid_clients';

		$where_arr  	=	array();
		if($catid!=''){
			$where_arr['catid']			= $catid;
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
			foreach($cat_res_arr['results'] as $key =>$row){
				if($row['paid_clients'] == '0' && $row['nonpaid_clients'] == '0'){
					$flag=true;
				}
			}
		}

		//$sql	= "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid in (".$catid.") and category_addon&8 = 8";
		//$qry= parent::execQuery($sql, $this->conn_catmaster);
		$cat_params = array();
		$cat_params['page']= 'financeDisplayClass';
		$cat_params['skip_log']		= '1';
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['return']		= 'catid';

		$where_arr  	=	array();
		if($catid!=''){
			$where_arr['catid']				= $catid;
			$where_arr['category_addon']	= '8';
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) >0){
			$flag=true;

		}
		return $flag;

	}
	function getExpired()
	{

		$balanceSum=0;
		$budgetSum=0;
		$packExpiredVal=0;
		$packExpiredDatediff=0;
		$packExpiredOnVal=null;
		$sql_compfin="select campaignid,balance,expired,expired_on,budget,bid_perday*365 as budget_1yr from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid in (1,2)";
		$res_compfin 	= parent::execQuery($sql_compfin, $this->conn_finance);
		$this->pdg_1yr_val = 0;
		if(mysql_num_rows($res_compfin)) // if it is already active phone seach campagn then do not allow
		{
			while($arr_compfin = mysql_fetch_assoc($res_compfin))
			{
				if($arr_compfin['balance']>0)
				{
					$balanceSum+=$arr_compfin['balance'];
					$this->pdg_1yr_val += $arr_compfin['budget_1yr'];
					$this->active_campaign=1;
					$budgetSum+=$arr_compfin['budget'];
				}
				if($arr_compfin['expired']==1 && ($arr_compfin['campaignid']==1 || $arr_compfin['campaignid']==2))
				{
					$packExpiredVal=1;
					$packExpiredOnVal=$arr_compfin['expired_on'];
					$todaydate  = date('Y-m-d H:i:s');
					if($packExpiredDatediff==0)
					{
						$packExpiredDatediff = round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400);
					}else
					{
						$packExpiredDatediff= min($packExpiredDatediff,round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400));
					}


				}
			}
			
			//~ if($balanceSum>0)
			//~ {
				//~ $this->activephonesearchcontractflag=1;
			//~ }
			
			//~ if(($this->data_city_cm == 'bangalore' && $budgetSum <= 15000) || ($this->data_city_cm == 'remote' && $budgetSum <= 8000 )){
				//~ $this->activephonesearchcontractflag=0; 
			//~ }

			if($balanceSum==0) // if there is no balance then checking for data in expired tables
			{
				$sql_bde="select count(1) as countval from tbl_bidding_details_expired where parentid='".$this->parentid."' and campaignid=2 and position_flag in (4,5,6,7)";
				$res_bde 	= parent::execQuery($sql_bde, $this->conn_finance);
				$num_bde		= mysql_num_rows($res_bde);

				if($res_bde && $num_bde > 0)
				{

					while($row_bde=mysql_fetch_assoc($res_bde))
					{
						if($row_bde['countval']>0)
						{
							$this->oldExpRemFxdPos=1;
						}
					}
				}

			}
			//if($balanceSum==0 && $packExpiredVal==1 && $packExpiredDatediff>=90) // previously it was 90 days condition
			if($balanceSum==0 && $packExpiredVal==1 && $packExpiredDatediff>=45) // expired package value setting
			{
				$this->expiredePackflg=1;


			}
		}

	}
	
	function newPriceChartAPI(){
		
		$retArr	=	array();
		$params	=	array();
		$national_min_budget_arr	=	array();
		
		$params['module']		=	$this->module;
		$params['data_city']	=	$this->data_city;
		$params['version']		=	$this->version;
		$params['parentid']		=	$this->parentid;
		/**************************************************************pre-select*********************************************************************/
		$campaigns=array();
		$banner_arr=array();
		$website_10years = 0;
		$sqlgetrcat="select * from catspon_banner_rotation_temp where parentid='".$this->parentid."'";
		$res_rex   = parent::execQuery($sqlgetrcat, $this->conn_temp);
		$catids_from_temp='';
		$banner_arr['no_of_rotation']=0;
		$banner_arr['rotation_avl'] = false;
		if($res_rex && mysql_num_rows($res_rex)>0)
		{
			$row_rex = mysql_fetch_assoc($res_rex);
			 $no_of_rotation=$row_rex['no_of_rotation'];
			 $banner_arr['no_of_rotation']= $no_of_rotation;
		}
		
		$sqlcheckfinance="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_finance_temp);
		$campaigns=array();
		if($resfinancetemp && mysql_num_rows($resfinancetemp))
		{
				while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
				{
					$campaigns[$rowfinancetemp['campaignid']]=$rowfinancetemp['campaignid'];
				}
		}
		
		
		if(isset($campaigns['o73']) && !isset($campaigns['72'])){
			unset($campaigns[73]);
			$campaigns['o734']='734';
		}
		if(isset($campaigns['2']) && isset($campaigns['1'])){
			unset($campaigns[1]);
		}
		$selectcam="select * from campaigns_selected_status where parentid='".$this->parentid."' and jdrr_banner_combo=1";
		$rescam = parent::execQuery($selectcam, $this->conn_temp);
		if($rescam && mysql_num_rows($rescam)>0)
		{
			unset($campaigns[51]);
			unset($campaigns[52]);
			unset($campaigns[53]);
			unset($campaigns[54]);
			unset($campaigns[5]);
			unset($campaigns[13]);
			unset($campaigns[22]);
			//~ $campaigns['225']='225';
		}
		
		
		
		$sqlcheckfinance="select * from dependant_campaign_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_temp);
		
		if($resfinancetemp && mysql_num_rows($resfinancetemp))
		{
			while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
			{
				if($rowfinancetemp['combo_type']=='Combo 2' ){  // changed combo 2
					unset($campaigns[1]);
		
					$campaigns['735']='735';
		
				}
				else if($rowfinancetemp['combo_type']=='Omni Supreme' || $rowfinancetemp['combo_type']=='Package'){
					unset($campaigns[1]);
					$campaigns['731']='731';
				}
				else if($rowfinancetemp['combo_type']=='Omni Ultima'){
					unset($campaigns[73]);
					unset($campaigns[734]);
					$campaigns['732']='732';
				}
			}
		}
		
		if(isset($campaigns['73'])) {
		$omni_sql="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
		$omni_temp_res = parent::execQuery($omni_sql, $this->conn_temp);
		if($omni_temp_res && mysql_num_rows($omni_temp_res)){
			while($omni_type=mysql_fetch_assoc($omni_temp_res))
			{
				//~ echo '=============='.$omni_type['omni_type'].(trim($this->params['module']));
				if((trim($this->params['module'])) == 'tme'){
					$campaigns[$omni_type['omni_type']]	=	$omni_type['omni_type'];
				}else{
					if($omni_type['omni_type'] == 6){
						$campaigns['740']='740';
					}else if($omni_type['omni_type'] == 7){
						$campaigns['741']='741';
					}else if($omni_type['omni_type'] == 10){
						$campaigns['744']='744';
					}else if($omni_type['omni_type'] == 2){
						$campaigns['734']='734';
					}
				}
			}
		}
		}
		if(isset($campaigns['75'])){
		$campaigns['743']='743';
		}
		if(isset($campaigns['84'])){
		$campaigns['742']='742';
		}
		
		$sqlfin="select * from tbl_payment_type where parentid='".$this->parentid."'  and version='".$this->version."'";
		$respack = parent::execQuery($sqlfin, $this->conn_finance);
		$payment_type='';
		
		if($respack && mysql_num_rows($respack))
		{
			while($rowpack=mysql_fetch_assoc($respack))
			{
				$payment_type=$rowpack['payment_type'];
			}
		}
		
		//~ if(isset($campaigns[1]) && strpos($payment_type, 'package_expired') === false){
			//~ $campaigns['116']='116';
			//~ unset($campaigns[1]);
		//~ }
		
		
		if($payment_type!=''){
		
			//~ echo $payment_type;
			if (strpos($payment_type, 'r&w(mini)') !== false || strpos($payment_type, 'Flexi Premium Ad Package') !== false || strpos($payment_type, 'flexi premium ad package') !== false || strpos($payment_type, 'pck_1yr_dis') !== false || strpos($payment_type, 'mini_ecs') !== false) {
				$campaigns['111']='111';
				unset($campaigns[1]);
				unset($campaigns[112]);
			}
		
			if (strpos($payment_type, 'r&w(premium)') !== false || strpos($payment_type, 'package_10dp_2yr') !== false || strpos($payment_type, 'Pack 2') !== false  || strpos($payment_type, 'Premium Package') !== false || strpos(strtolower($payment_type), 'ultra premium ad package') !== false ||  strpos(strtolower($payment_type), 'super premium ad package') !== false || strpos(strtolower($payment_type), 'premium ad package') !== false) {
		
				$campaigns['116']='116';
				unset($campaigns[1]);
			}
			if (strpos($payment_type, 'jd prime') !== false) {
				$campaigns['113']='113';
				unset($campaigns[1]);
			}
			if (strpos($payment_type, 'omni 1') !== false) {
				$campaigns['734']='734';
				unset($campaigns[73]);
			}
			
			
			
			if (strpos($payment_type, 'plus') !== FALSE) { //extra
				$campaigns['225']='225';
				//~ unset($campaigns[51]);
				//~ unset($campaigns[22]);
			}
			if ((strpos($payment_type, 'jdrr') !==  false || strpos($payment_type, 'JDRR') !==  false) && strpos($payment_type, 'plus') ===  false) { //extra
				$campaigns['22']='22';
				//~ unset($campaigns[51]);
				//~ unset($campaigns[22]);
			}
			if (strpos($payment_type, 'festive combo offer') !== false && strpos($payment_type, 'JDRR') ==  false) { //extra
				$campaigns['735']='735';
				unset($campaigns[51]);
				unset($campaigns[22]);
			}
			if (strpos($payment_type, 'pdg_combo') !== false && strpos($payment_type, 'JDRR') ==  false) { //extra
				$campaigns['746']='746';
				unset($campaigns[51]);
				unset($campaigns[22]);
			}
			if (strpos($payment_type, 'nl_original,nationallisting_combo') !== false && strpos($payment_type, 'JDRR') ===  false) { //extra
				$campaigns['747']='747';
				unset($campaigns[51]);
				unset($campaigns[22]);
			}
			if(strpos($payment_type, 'fixed position') !== false){
				$campaigns['2']='2';
			}
			if(strpos($payment_type, 'flexi_pincode_budget') !== false){
				$campaigns['119']='119';
				unset($campaigns[1]);
				unset($campaigns[2]);
			}
			if($banner_arr['no_of_rotation'] == 1 && (!isset($campaigns['225'])) && strpos($payment_type, 'banner') !== false){
			$campaigns['51']='51';
			}
			if($banner_arr['no_of_rotation'] == 4 && strpos($payment_type, 'banner') !== false){
				$campaigns['54']='54';
			}
			 if($banner_arr['no_of_rotation'] == 2 && strpos($payment_type, 'banner') !== false){
				$campaigns['52']='52';
			}
			 if($banner_arr['no_of_rotation'] == 3 && strpos($payment_type, 'banner') !== false){
				$campaigns['53']='53';
			}
		
		}
		
		
		
		
			
		
		//~ echo '<pre>';print_r($campaigns);die;	
		/**************************************************************pre-select*********************************************************************/
		if(trim($this->module) != "" && $this->module != null){
			$this -> national_list_obj = new nationallistingclass($params);
		}
		
		$sql="select * from tbl_business_uploadrates where city='".$this->data_city."' limit 1";
		$res 	= parent::execQuery($sql, $this->dbConDjds);
		$num_rows		= mysql_num_rows($res);
		
		if($res && $num_rows > 0)
		{		
			
			while($row=mysql_fetch_assoc($res)){
				$banner_arr['banner_single_unit']=$row['banner_single_unit'];
				$banner_arr['banner_upto_10']=$row['banner_upto_10'];
				$banner_arr['banner_above_10']=$row['banner_above_10'];
				$banner_arr['banner_ecs_per_rotation']=$row['banner_ecs_per_rotation'];
				$combodiscount	=	$row['combodiscount'];
				$disc=$combodiscount/100;
				$minimumbudget_national		= ($row['minimumbudget_national']);
				$maxbudget_national			= ($row['maxbudget_national']);
				$statebudget_national		= ($row['statebudget_national']);
				$minupfrontbudget_national	= ($row['minupfrontbudget_national']);
				$maxupfrontbudget_national	= ($row['maxupfrontbudget_national']);
				$stateupfrontbudget_national= ($row['stateupfrontbudget_national']);
			}
		}
		
		if($this -> national_list_obj){
			$national_min_budget_arr  =  $this -> national_list_obj->getNationalListingMinBudget($min_monthly_cost=($minimumbudget_national/12),$max_monthly_cost=($maxbudget_national/12),$minupfrontbudget_national,$maxupfrontbudget_national,$state_monthly_cost=($statebudget_national/12),$stateupfrontbudget_national);
		}
			
		$query		=	"SELECT PriceList FROM d_jds.pricing_citywise WHERE city='".$this->data_city."'";
		$conq		=	parent::execQuery($query, $this->dbConDjds);
		$data	=	array();
		if($conq && mysql_num_rows($conq)>0){
			$data															=	mysql_fetch_assoc($conq);
			$retArr['data']													=	json_decode($data['PriceList'],true);
			
			foreach($retArr['data']['Package'] as $key=>$value){
				if(in_array($key,$campaigns)){
					$retArr['data']['Package'][$key]['checked']					=	1;
				}else{
					$retArr['data']['Package'][$key]['checked']					=	0;
				}
			}
			foreach($retArr['data']['Omni'] as $key=>$value){
				if(in_array($key,$campaigns)){
					$retArr['data']['Omni'][$key]['checked']					=	1;
				}else{
					$retArr['data']['Omni'][$key]['checked']					=	0;
				}
			}
			
			foreach($retArr['data']['Normal'] as $key=>$value){
				if(in_array($key,$campaigns)){
					$retArr['data']['Normal'][$key]['checked']					=	1;
				}else{
					$retArr['data']['Normal'][$key]['checked']					=	0;
				}
			}
			
			foreach($retArr['data']['Banner'] as $key=>$value){
				if(in_array($key,$campaigns)){
					$retArr['data']['Banner'][$key]['checked']					=	1;
				}else{
					$retArr['data']['Banner'][$key]['checked']					=	0;
				}
			}
			
			$retArr['data']['Normal']['10']['price_ecs']					=	$national_min_budget_arr['monthly_budget'];
			$retArr['data']['Normal']['10']['down_payment']					=	$national_min_budget_arr['monthly_budget']*3;
			$retArr['data']['Normal']['10']['price_upfront']				=	$national_min_budget_arr['upfront_budget'];
			$retArr['data']['Normal']['10']['price_upfront_discount']		=	$national_min_budget_arr['upfront_budget']- ($national_min_budget_arr['upfront_budget'] * $disc);
			$retArr['data']['Normal']['10']['price_upfront_two_years']		=	$national_min_budget_arr['upfront_budget']+ ($national_min_budget_arr['upfront_budget'] * $disc);
			
			$instrument_type='';
			$instrument_type_sql="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
			$instrument_type_res = parent::execQuery($instrument_type_sql, $this->conn_finance);
			if($instrument_type_res && mysql_num_rows($instrument_type_res))
			{
				while($instrument_type_row=mysql_fetch_assoc($instrument_type_res))
				{
	
					$instrument_type									=	$instrument_type_row['instrument_type'];
					$retArr['data']['instrument_type']						=	$instrument_type;
				}
			}
		
		}
		//~ echo $retArr['data']['instrument_type'];die;	
		//~ echo '<pre>';print_r($retArr['data']);die;
	
		$this->expiredePackflg	=	0;
		$this->getExpired();
			
		if($this->expiredePackflg){
			$retArr['present']['isExpired']	=	1;
			$retArr['data']['Package']['1']['checked']=	0;
		}else{
			$retArr['present']['isExpired']	=	0;
			$retArr['data']['Package']['1']['checked']=	0;
		}
	
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$row_ex = $this->mongo_obj->getData($mongo_inputs);
			$catids_from_temp=$row_ex['catIds'];
		}
		else
		{
			$sqlgetcat="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
			$res_ex   = parent::execQuery($sqlgetcat, $this->conn_temp);
			$catids_from_temp='';
			if($res_ex && mysql_num_rows($res_ex)>0)
			{
				$row_ex = mysql_fetch_assoc($res_ex);
				$catids_from_temp=$row_ex['catIds'];
			}
		}
			
		$catids_from_temp=explode("|P|", $catids_from_temp);
		$catids_from_temp=array_filter($catids_from_temp);
		$arrcount=count($catids_from_temp);
		if($arrcount > 0){
			$catids_from_temp_str=implode(',',$catids_from_temp);
		}else{
		
			$catids_from_temp_str	=	0;
		}
		
		$banner_avl=$this->getBannerAvailibity($catids_from_temp);
		$banner_arr['rotation_avl']=$banner_avl;
		if($this->checkCategoriesSanity($catids_from_temp)){
			$banner_arr['rotation_avl']=false;
		}
		if($banner_arr['rotation_avl'] == false && $banner_arr['no_of_rotation'] > 1){
			$banner_arr['no_of_rotation'] = 1;
			$retArr['data']['Banner']['52']['checked'] = 0;
			$retArr['data']['Banner']['53']['checked'] = 0;
			$retArr['data']['Banner']['54']['checked'] = 0;
		}
		
		$retArr['data']['Banner']['51']['banner_rules'] = $banner_arr;
		$retArr['present']['flexflag']=0;
		$template_ids='';
		 //$sqlcatflex="SELECT count(catid) as countlow,group_concat(template_id) as template_ids FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$catids_from_temp_str.") and miscellaneous_flag&1=1 ";
			//$rescatflex   = parent::execQuery($sqlcatflex, $this->dbConIro);
		 	$cat_params = array();
			$cat_params['page']= 'financeDisplayClass';
			$cat_params['skip_log'] 	= '1';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'template_id';

			$where_arr  	=	array();
			if($catids_from_temp_str!=''){
				$where_arr['catid']					= $catids_from_temp_str;
				$where_arr['miscellaneous_flag']	= '1';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				//$rowcatflex = mysql_fetch_assoc($rescatflex);

				if($arrcount==count($cat_res_arr['results'])){
					$retArr['present']['flexflag']=1;//change to 1
					$retArr['data']['Package']['111']['checked']=	0;
				}else{
					$retArr['present']['flexflag']=0;
					$retArr['data']['Package']['111']['checked']=	0;
				}
		}
		//~ echo '==============<pre>';print_r($retArr['data']);
		
		/******************************************************National Listing special Nodes*****************************************************/
		
		$national_main = 0;
		$isnational = $this->isnationallisting();
		
		if($isnational['nationallisting'] == 1 && $this->national_change_state['state_change'] == 1)
		{
			 $retArr['data']['Normal']['10']['state_change']=	1;
		}
		else if($isnational['nationallisting'] == 1 && $this->national_change_state['state_change'] == 0)
		{
			$retArr['data']['Normal']['10']['state_change']=	0;
		}	
		
		
		$nat_present	=	0;
		$sql_nationallisting = "SELECT * FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid= '".$this->parentid."' and balance > 0";
		$res_nationallisting = parent::execQuery($sql_nationallisting, $this->conn_idc);
		if($res_nationallisting && mysql_num_rows($res_nationallisting))
		{
			
			$nat_present = 1;
		}
		
		
		if($isnational['nationallisting'] == 1 && $nat_present == 0)
		{
			 $retArr['data']['Normal']['10']['live']= false;
		}
		else if($isnational['nationallisting'] == 1 && $nat_present == 1)
		{
			$retArr['data']['Normal']['10']['live']= true;
		}
		
		

		if($isnational['nationallisting'] == 0 || $isnational['eligible_flag'] == 0)
		{
			 $retArr['present']['national_main'] = 0;
			  $retArr['data']['Normal']['10']['checked']=	0;
			 
		}else{
			$retArr['present']['national_main'] = 1;
			
			//For the First time
			if($nat_present == 1 && $isnational['nationallisting'] == 1){
				if(strpos($payment_type, 'national listing') !== false || strpos($payment_type, 'nl_') !== false){
					
					$retArr['data']['Normal']['10']['checked']=	1;
				}else{
					$retArr['data']['Normal']['10']['checked']=	0;
				}
			}else{
				$retArr['data']['Normal']['10']['checked']=	1;
			}
		}
		
		
		/******************************************************************Discount based on joining date********************************************/
		$pack_team=0;
		$pack_selected = 0;
		$sqlomni_1="select omni_1,omni_ultima,omni_normal,pack_mini,ultra_pack_oneplusone,joining_date,pack_selected,website_10years from online_regis.extra_prev_user where empcode='".$this->usercode."'";
		$res_omni = parent::execQuery($sqlomni_1, $this->conn_idc);
		if($res_omni && mysql_num_rows($res_omni))
		{
			while($row_omni=mysql_fetch_assoc($res_omni))
			{
				if($row_omni['joining_date'] != '' && $row_omni['joining_date'] != null && $row_omni['joining_date'] != '0000-00-00' ) {
					$pack_team=1;
					$joining_date = $row_omni['joining_date'];
				}
				$pack_selected = $row_omni['pack_selected'];
				$website_10years = $row_omni['website_10years'];
			}
		}
		
		$banner_sql="select banner_single_unit,banner_upto_10,banner_above_10,banner_ecs_per_rotation,banner_rotation_details,package_mini_display_flag,extra_package_details,combodiscount,comboextradetails,omniextradetails,ecssecyrinc,team_budget,team_minbudget_package   from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";

		$banner_res = parent::execQuery($banner_sql, $this->dbConIro);
		
		if($banner_res && mysql_num_rows($banner_res))
		{
			while($banner_row=mysql_fetch_assoc($banner_res))
			{
				$team_budget=$banner_row['team_budget'];
				$team_wise_budget=$banner_row['team_minbudget_package'];

			}
		}
		$team_budget = json_decode($team_budget,1);
		$team_wise_budget = json_decode($team_wise_budget,1);
		
		$retArr['data']['discount'] = array();
		if($pack_team == 1 && isset($team_budget['pck_dis'])) {
			$current_date  = date('Y-m-d'); 	
			$days_diff = date_diff(date_create($joining_date), date_create($current_date));
			$no_days = $days_diff->format("%a");
			if($no_days <= 60){
				$retArr['data']['discount']['team_dis'] = $team_budget['pck_dis']['2month'];
			}else if($no_days > 60 && $no_days <= 120 ){
				$retArr['data']['discount']['team_dis'] = $team_budget['pck_dis']['4month'];
			}else if($no_days > 120 && $no_days <= 180 ){
				$retArr['data']['discount']['team_dis'] = $team_budget['pck_dis']['6month'];
			}
		}
		$employee_arr =  json_decode($this->getEmployeeInfo($this->usercode),1);
		if($employee_arr['errorcode'] == 0 && isset($employee_arr['data']) && isset($employee_arr['data'][0]) && isset($employee_arr['data'][0]['team_type'])){
			$employee_allocid = strtolower($employee_arr['data'][0]['team_type']);
		}
		
		if(isset($team_wise_budget['C']) && $team_wise_budget['C'] > 0 && strtolower($this->data_city) == 'mumbai' && strtolower($employee_allocid) == 's'){
			if(isset($retArr['data']['discount']['team_dis']) && $retArr['data']['discount']['team_dis'] > 0){
				if($retArr['data']['discount']['team_dis'] > $team_wise_budget['C'])
					$retArr['data']['discount']['team_dis'] = $team_wise_budget['C'];
			}else {
				$retArr['data']['discount']['team_dis'] = $team_wise_budget['C'];
			}
		}
		
		
		
		/******************************************************************Discount based on joining date********************************************/
		
		if($pack_selected == 1 || $pack_selected == 2){
				$retArr['data']["Package"][118]['price_upfront_discount']='-';
				$retArr['data']["Package"][118]['price_upfront_two_years']='-';
				$retArr['data']["Package"][118]['name']="Highest to Lowest";
				$retArr['data']["Package"][118]['order']=7;
				$retArr['data']["Package"][118]['campaignid']=118;
				$retArr['data']["Package"][118]['price_upfront_display']='system budget';
				$retArr['data']["Package"][118]['price_ecs_display']='system budget';
				$retArr['data']["Package"][118]['price_upfront']='system budget';
				$retArr['data']["Package"][118]['price_ecs']='system budget';
				$retArr['data']["Package"][118]['down_payment']='system budget';
		}
				
		if($website_10years == 0){
			unset($retArr['data']["Omni"][748]);
		}
				
			
		if($this->activephonesearchcontractflag == 1  && strtolower($employee_allocid) == 'rd'){
			$this->check_retention();
		}
				
		$retArr['data']['activephonesearchcontractflag'] =$this->activephonesearchcontractflag;
		$retArr['data']['standard'] = array();  
		$retArr['data']['standard']['Package'] = $retArr['data']['Package'];
		$retArr['data']['standard']['Omni'] = $retArr['data']['Omni'];
		$retArr['data']['standard']['Banner'] = $retArr['data']['Banner'];
		$retArr['data']['standard']['Normal'] = $retArr['data']['Normal'];
		unset($retArr['data']['Package']);
		unset($retArr['data']['Omni']);
		unset($retArr['data']['Banner']);
		unset($retArr['data']['Normal']);
		echo json_encode($retArr);exit;
		
	}
	
	function check_retention(){
		$retention_sql ="SELECT count(1) as count FROM d_jds.tbl_new_retention WHERE parentid='".$this->parentid."'";
		$retention_res = parent::execQuery($retention_sql, $this->dbConIro);
		if($retention_res && mysql_num_rows($retention_res)){
			$retention_row=mysql_fetch_assoc($retention_res);
			if($retention_row['count'] == 1){
				$this->activephonesearchcontractflag = 0;
			}
		}
	}
	
	function getEmployeeInfo($empcode){
		$retValemp 			= '';
		if(intval($empcode)>0){
			$paramsArr			=	array();
			$empurl 					= 	'http://192.168.20.237:8080/api/getEmployee_xhr.php';
			$paramsArr['empcode']		=	 $empcode;
			$paramsArr['textSearch']		=	4;
			$paramsArr['reseller_flag']	=	1;
			$retValemp 							= 	$this->curlCall($empurl,json_encode($paramsArr),1);
		}
		return $retValemp;
	}
	
	function getPriceChartNew(){
		$where='';
		$desp='';
		$datarr=array();
		$sql="select *  from tbl_finance_omni_flow_display_new_new order by display_order_new asc";
		$res = parent::execQuery($sql, $this->conn_temp);
		$flex=0;
		$sqlomni_1="select omni_1,omni_ultima,omni_normal,pack_mini,ultra_pack_oneplusone,joining_date,pack_selected from online_regis.extra_prev_user where empcode='".$this->usercode."'";
		$res_omni = parent::execQuery($sqlomni_1, $this->conn_idc);
		$omni_1_emp=0;
		$omni_ultima_emp=0;
		$omni_normal_emp=0;
		$pack_mini=0;
		$pack_team=0;
		$ecssecyrinc=0;
		$pack_selected=0;
		$website_10years=0;
		$this->expiredePackflg=0;
		$banner_3years = 0;
		$banner_10years = 0;
		if($res_omni && mysql_num_rows($res_omni))
		{
			while($row_omni=mysql_fetch_assoc($res_omni))
			{
				$omni_1_emp=$row_omni['omni_1'];
				$omni_ultima_emp=$row_omni['omni_ultima'];
				$omni_normal_emp=$row_omni['omni_normal'];
				$pack_mini=$row_omni['pack_mini'];
				$pack_selected=$row_omni['pack_selected'];
				$ultra_pack_oneplusone=$row_omni['ultra_pack_oneplusone'];
				if($row_omni['joining_date'] != '' && $row_omni['joining_date'] != null && $row_omni['joining_date'] != '0000-00-00' ) {
					$pack_team=1;
					$joining_date = $row_omni['joining_date'];
				}
			}
		}
		
		$omni_1_emp=0;
		$omni_ultima_emp=0;
		$omni_normal_emp=0;
				
		$instrument_type='';
		$instrument_type_sql="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
		$instrument_type_res = parent::execQuery($instrument_type_sql, $this->conn_finance);
		if($instrument_type_res && mysql_num_rows($instrument_type_res))
		{
			while($instrument_type_row=mysql_fetch_assoc($instrument_type_res))
			{

				$instrument_type=$instrument_type_row['instrument_type'];
			}
		}

		
		$festivesql ="SELECT omni_monthly_fees FROM tbl_omni_pricing WHERE omni_type='5' AND city='".$this->data_city."'";
		$festres = parent::execQuery($festivesql, $this->conn_temp);
		if($festres && mysql_num_rows($festres))
		{
			while($festrow=mysql_fetch_assoc($festres))
			{

				$flexi_val=$festrow['omni_monthly_fees'];
			}
		}
		
		


		$this->getExpired();

		$banner_sql="select banner_single_unit,banner_upto_10,banner_above_10,banner_ecs_per_rotation,banner_rotation_details,package_mini_display_flag,extra_package_details,combodiscount,comboextradetails,omniextradetails,ecssecyrinc,team_budget   from d_jds.tbl_business_uploadrates  where city='".$this->data_city."'";

		$banner_res = parent::execQuery($banner_sql, $this->dbConIro);
		$banner_arr=array();
		$combodiscount=0;
		$banner_details='';
		if($banner_res && mysql_num_rows($banner_res))
		{
			while($banner_row=mysql_fetch_assoc($banner_res))
			{
				$banner_arr['banner_single_unit']=$banner_row['banner_single_unit'];
				$banner_arr['banner_upto_10']=$banner_row['banner_upto_10'];
				$banner_arr['banner_above_10']=$banner_row['banner_above_10'];
				$banner_arr['banner_ecs_per_rotation']=$banner_row['banner_ecs_per_rotation'];
				$banner_arr['rotation_avl']=false;
				$banner_arr['package_mini_display_flag']=$banner_row['package_mini_display_flag'];
				$banner_details=$banner_row['banner_rotation_details'];
				$extra_package_details=$banner_row['extra_package_details'];
				$comboextradetails=$banner_row['comboextradetails'];
				$combodiscount=$banner_row['combodiscount'];
				$ecssecyrinc= $banner_row['ecssecyrinc'];
				$omniextradetails=$banner_row['omniextradetails'];
				$team_budget=$banner_row['team_budget'];

			}
		}
		$team_budget = json_decode($team_budget,1);
		$comboextradetails=json_decode($comboextradetails,1);
		$banner_details=json_decode($banner_details,1);
		$banner_arr=$banner_arr+$banner_details;
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "catIds";
			$row_ex = $this->mongo_obj->getData($mongo_inputs);
			$catids_from_temp=$row_ex['catIds'];
		}
		else
		{
			$sqlgetcat="select catIds from tbl_business_temp_data where contractid='".$this->parentid."'";
			$res_ex   = parent::execQuery($sqlgetcat, $this->conn_temp);
			$catids_from_temp='';
			if($res_ex && mysql_num_rows($res_ex)>0)
			{
				$row_ex = mysql_fetch_assoc($res_ex);
				$catids_from_temp=$row_ex['catIds'];
			}
		}

		$catids_from_temp=explode("|P|", $catids_from_temp);
		$catids_from_temp=array_filter($catids_from_temp);

		$arrcount=count($catids_from_temp);
		$catids_from_temp_str=implode(',',$catids_from_temp);
		$flexflag=0;
		$template_ids='';
		//$sqlcatflex="SELECT count(catid) as countlow,group_concat(template_id) as template_ids FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$catids_from_temp_str.") and miscellaneous_flag&1=1 ";
			//$rescatflex   = parent::execQuery($sqlcatflex, $this->dbConIro);
			$cat_params = array();
			$cat_params['page']= 'financeDisplayClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'template_id';

			$where_arr  	=	array();
			if($catids_from_temp_str!=''){
				$where_arr['catid']					= $catids_from_temp_str;
				$where_arr['miscellaneous_flag']	= '1';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}

			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				//$rowcatflex = mysql_fetch_assoc($rescatflex);
				$countlow = count($cat_res_arr['results']);
				if($arrcount==$countlow){

					$flexflag=1;
				}
			}
			$allcatids='';
			//$sqlcatflex="SELECT count(catid) as countlow,group_concat(template_id) as template_ids,group_concat(catid) as allcatids FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in (".$catids_from_temp_str.") "; 
				//$rescatflex   = parent::execQuery($sqlcatflex, $this->dbConIro);
				$cat_params = array();
				$cat_params['page']= 'financeDisplayClass';
				$cat_params['data_city'] 	= $this->data_city;
				$cat_params['return']		= 'catid,template_id';

				$where_arr  	=	array();
				if($catids_from_temp_str!=''){
					$where_arr['catid']					= $catids_from_temp_str;
					$cat_params['where']		= json_encode($where_arr);
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				}
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
					//$rowcatflex = mysql_fetch_assoc($rescatflex);
					$catid_arr 			= array();
					$template_id_arr 	= array();
					foreach($cat_res_arr['results'] as $key => $cat_arr) {
						if($cat_arr['catid']!=''){
							$catid_arr[] =$cat_arr['catid'];
						}
						if($cat_arr['template_id']!=''){
							$template_id_arr[] =$cat_arr['template_id'];
						}
					}
					$template_ids	=implode(",",$template_id_arr);
					$allcatids		=implode(",",$catid_arr);

			}
		//if(strtolower($this->data_city) =='pune' || strtolower($this->data_city)=='ahmedabad' || strtolower($this->data_city) =='mumbai'){
			if($flexflag==1)
				$flex=1;
  		//}
  		$active_web=1; 
  		/*if($template_ids!=''){
  			$sqlgettem=" SELECT * FROM online_regis1.tbl_template_master WHERE id IN ($template_ids) and template_enable_flag=1";
  			$res_temp   = parent::execQuery($sqlgettem, $this->conn_idc);
  			if($rescatflex && mysql_num_rows($res_temp) >0)
  				$active_web=1;
  		}
*/
  		if($active_web==1){

  			$tempdets=$this->checkactiveTemplate();
  			if($tempdets['flag']!=1)
  				$active_web=0;
  		}
  		
			$insert_omni_log = "INSERT INTO tbl_omni_cat_log set
								parentid='".$this->parentid."',
								version='".$this->version."',
								catids  	= '".$allcatids."',
								template_id  	= '".$tempdets['template_id']."',
								template_name  = '".$tempdets['template_name']."',
								vertical_id  	= '".$tempdets['vertical_id']."',
								active_website ='".$active_web."',
								campaignid ='',
								campaign_names ='',
								theme_id  	= '".$tempdets['theme_id']."',
								selected=0
								ON DUPLICATE KEY UPDATE
								catids  	= '".$allcatids."',
								template_id  	= '".$tempdets['template_id']."',
								template_name  = '".$tempdets['template_name']."',
								vertical_id  	= '".$tempdets['vertical_id']."',
								theme_id  	= '".$tempdets['theme_id']."',
								campaignid ='',
								campaign_names ='',
								active_website ='".$active_web."',
								selected=0";
								
			$inst_log = parent::execQuery($insert_omni_log, $this->conn_temp);
  		//if($this->module=='tme' || $this->module=='TME' ){
  		//	$active_web=0;
  		//	$combodiscount=0;
  		//}
		$banner_avl=$this->getBannerAvailibity($catids_from_temp);
		$banner_arr['rotation_avl']=$banner_avl;
		if($this->checkCategoriesSanity($catids_from_temp)){
			$banner_arr['rotation_avl']=false;
		}


		$extra_package_details=json_decode($extra_package_details,1);


		$sqlgetrcat="select * from catspon_banner_rotation_temp where parentid='".$this->parentid."'";
		$res_rex   = parent::execQuery($sqlgetrcat, $this->conn_temp);
		$catids_from_temp='';
		$banner_arr['no_of_rotation']=0;
		if($res_rex && mysql_num_rows($res_rex)>0)
		{
			 $row_rex = mysql_fetch_assoc($res_rex);
			 $no_of_rotation=$row_rex['no_of_rotation'];
			 $banner_arr['no_of_rotation']= $no_of_rotation;
		}

		$national_main = 0;
		$sql_nationallisting = "SELECT * FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid= '".$this->parentid."' and balance > 0";

		$sql_budget_national ="select minbudget_national from tbl_business_uploadrates where city='".$this->data_city."' limit 1";
		$res_budget_national 	= parent::execQuery($sql_budget_national, $this->dbConDjds);
		$num_rows__budget_national		= mysql_num_rows($res_budget_national);
		$row_budget_national		=     mysql_fetch_assoc($res_budget_national);
		$national_budget = $row_budget_national['minbudget_national'];
		//print_r($row_budget_national['minbudget_national']);

		$res_nationallisting = parent::execQuery($sql_nationallisting, $this->conn_idc);
		if($res_nationallisting && mysql_num_rows($res_nationallisting))
		{
			$national_main = 1;
		}

		$sqlcheckfinance="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1 and budget>0";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_finance_temp);
		$campaigns=array();
		if($resfinancetemp && mysql_num_rows($resfinancetemp))
			{
				while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
				{
					$campaigns[$rowfinancetemp['campaignid']]=$rowfinancetemp['campaignid'];
					if(($rowfinancetemp['campaignid'] == 5 || $rowfinancetemp['campaignid'] == 13) && $rowfinancetemp['duration'] == 1095){
						$banner_3years = 1;
					}else if(($rowfinancetemp['campaignid'] == 5 || $rowfinancetemp['campaignid'] == 13) && $rowfinancetemp['duration'] == 3650){
						$banner_10years = 1;
					}
				}
			}
		if(isset($campaigns['o73']) && !isset($campaigns['72'])){
			unset($campaigns[73]);
			$campaigns['o734']='734';
		}
		if(isset($campaigns['2']) && isset($campaigns['1'])){
			unset($campaigns[1]);
		}
		
		$selectcam="select * from campaigns_selected_status where parentid='".$this->parentid."' and jdrr_banner_combo=1";
		$rescam = parent::execQuery($selectcam, $this->conn_temp);
		if($rescam && mysql_num_rows($rescam)>0)
			{
				unset($campaigns[51]);
				unset($campaigns[52]);
				unset($campaigns[53]);
				unset($campaigns[54]);
				unset($campaigns[5]);
				unset($campaigns[13]);
				unset($campaigns[22]);
				$campaigns['225']='225';
			}
		$sqlcheckfinance="select * from dependant_campaign_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
		$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_temp);

		if($resfinancetemp && mysql_num_rows($resfinancetemp))
			{
				while($rowfinancetemp=mysql_fetch_assoc($resfinancetemp))
				{
					if($rowfinancetemp['combo_type']=='Combo 2' ){
						unset($campaigns[1]);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[22]);
						unset($campaigns[225]);
						$campaigns['735']='735';
						$banner_arr['no_of_rotation']= 0;

					}
					else if($rowfinancetemp['combo_type']=='Omni Supreme' || $rowfinancetemp['combo_type']=='Package'){
						unset($campaigns[1]);
						$campaigns['731']='731';
					}
					else if($rowfinancetemp['combo_type']=='Omni Ultima'){
						unset($campaigns[73]);
						unset($campaigns[734]);
						$campaigns['732']='732';
					}
					else if($rowfinancetemp['combo_type']=='pdg festive combo'){
						unset($campaigns[1]);
						unset($campaigns[2]);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[22]);
						unset($campaigns[225]);
						$banner_arr['no_of_rotation']= 0;
						$campaigns['746']='746';
						
					}else if(strtolower($rowfinancetemp['combo_type'])=='national listing festive combo offer'){
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[22]);
						unset($campaigns[225]);
						$banner_arr['no_of_rotation']= 0;
					}else if(strtolower($rowfinancetemp['combo_type'])=='complete suite for 5 years'){
						$campaigns['73']='73';
						unset($campaigns['734']);
						unset($campaigns['1']);
						unset($campaigns['116']);
						unset($campaigns['111']);
					}
				}
		}
		
		
		if(isset($campaigns['73']) || isset($campaigns['72'])){
			$omni_sql="select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
			$omni_temp_res = parent::execQuery($omni_sql, $this->conn_temp);
			if($omni_temp_res && mysql_num_rows($omni_temp_res)){
				while($omni_type=mysql_fetch_assoc($omni_temp_res))
				{	if($omni_type['omni_type'] == 6){
						$campaigns['740']='740';
						unset($campaigns['73']);
					}else if($omni_type['omni_type'] == 7){
						$campaigns['741']='741';
						unset($campaigns['73']);
					}else if($omni_type['omni_type'] == 10){
						$campaigns['744']='744';
						unset($campaigns['73']);
					}else if($omni_type['omni_type'] == 14){
						$campaigns['748']='748';
						unset($campaigns['73']);
					}else if($omni_type['omni_type'] == 15){
						$campaigns['749']='749';
						unset($campaigns['73']);
						unset($campaigns['72']);
					}else if($omni_type['omni_type'] == 2){
						$campaigns['734']='734';
						unset($campaigns['73']);
						unset($campaigns['1']);
						unset($campaigns['116']);
						unset($campaigns['111']);
					}else if($omni_type['omni_type'] == 16){
						$campaigns['750']='750';
						unset($campaigns['73']);
						unset($campaigns['72']);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[13]);
						unset($campaigns[22]);
						unset($campaigns[225]);
					}else if($omni_type['omni_type'] == 17){
						$campaigns['751']='751';
						unset($campaigns['73']);
						unset($campaigns['72']);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[13]);
						unset($campaigns[22]);
						unset($campaigns[225]);
					}else if($omni_type['omni_type'] == 18){
						$campaigns['752']='752';
						unset($campaigns['73']);
						unset($campaigns['72']);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[13]);
						unset($campaigns[22]);
						unset($campaigns[225]);
					}else if($omni_type['omni_type'] == 19){   
						$campaigns['753']='753';
						unset($campaigns['73']);
						unset($campaigns['72']);
						unset($campaigns[51]);
						unset($campaigns[52]);
						unset($campaigns[53]);
						unset($campaigns[54]);
						unset($campaigns[5]);
						unset($campaigns[13]);
						unset($campaigns[22]);
						unset($campaigns[225]);
					}
				}
			}
		}
		if(isset($campaigns['75'])){
			$campaigns['743']='743';
		}
		if(isset($campaigns['84'])){
			$campaigns['742']='742';
		}
		
		$sqlfin="select * from tbl_payment_type where parentid='".$this->parentid."'  and version='".$this->version."'";
		$respack = parent::execQuery($sqlfin, $this->conn_finance);
		$payment_type='';

		if($respack && mysql_num_rows($respack))
			{
				while($rowpack=mysql_fetch_assoc($respack))
				{
					$payment_type=$rowpack['payment_type'];
				}
			}

			if(isset($campaigns[1]) && strpos($payment_type, 'package_expired') === false){
				$campaigns['116']='116';
				unset($campaigns[1]);
			}

			if($payment_type!=''){


				if (strpos($payment_type, 'r&w(mini)') !== false || strpos($payment_type, 'Flexi Premium Ad Package') !== false || strpos($payment_type, 'flexi premium ad package') !== false || strpos($payment_type, 'pck_1yr_dis') !== false || strpos($payment_type, 'mini_ecs') !== false) {
				    $campaigns['111']='111';
				    unset($campaigns[1]);
				    unset($campaigns[112]);
				    unset($campaigns[116]);
				}

				if (strpos($payment_type, 'r&w(premium)') !== false || strpos($payment_type, 'package_10dp_2yr') !== false || strpos($payment_type, 'Pack 2') !== false  || strpos($payment_type, 'Premium Package') !== false || strpos(strtolower($payment_type), 'ultra premium ad package') !== false ) {

				    $campaigns['112']='112';
				    unset($campaigns[1]);
				}
				if (strpos($payment_type, 'jd prime') !== false) {
				    $campaigns['113']='113';
				    unset($campaigns[1]);
				}
				if (strpos($payment_type, 'omni 1') !== false) {
				    $campaigns['734']='734';
				    unset($campaigns[73]);
				}
				if (strpos($payment_type, 'flexi_selected_user') !== false) {
				    $campaigns['118']='118';
				    unset($campaigns[2]);
				    unset($campaigns[112]);
				    unset($campaigns[1]);
				    unset($campaigns[116]);
				}

				if (strpos($payment_type, 'jdrrplus_3years') !== false) {
				    unset($campaigns[51]);
					unset($campaigns[52]);
					unset($campaigns[53]);
					unset($campaigns[54]);
					unset($campaigns[5]);
					unset($campaigns[13]);
					unset($campaigns[22]);
					unset($campaigns[225]);
					unset($campaigns[61]);
					unset($campaigns[62]);
					unset($campaigns[63]);
					unset($campaigns[64]);
					unset($campaigns[41]);
					unset($campaigns[42]);
					unset($campaigns[43]);
					unset($campaigns[44]);
					$campaigns['2252']='2252';
				}
				
				if (strpos($payment_type, 'jdrrplus_10year') !== false) {
				    unset($campaigns[51]);
					unset($campaigns[52]);
					unset($campaigns[53]);
					unset($campaigns[54]);
					unset($campaigns[5]);
					unset($campaigns[13]);
					unset($campaigns[22]);
					unset($campaigns[225]);
					unset($campaigns[61]);
					unset($campaigns[62]);
					unset($campaigns[63]);
					unset($campaigns[64]);
					unset($campaigns[41]);
					unset($campaigns[42]);
					unset($campaigns[43]);
					unset($campaigns[44]);
					$campaigns['2253']='2253';
				}
		
		}
		$campDetails=array();
		$omniextradetails = json_decode($omniextradetails,1);
		if($res && mysql_num_rows($res))
			{
				while($row=mysql_fetch_assoc($res))
				{
						$type='Normal';

						if($row['disp_order']=='1')
							$type='Package';
						if($row['disp_order']=='73')
							$type='Omni';
						if($row['disp_order']=='5')
							$type='Banner';
						if($row['disp_order']=='225')
							$type='JDRR_Plus';	

						if($row['disp_order']=='22')
							$type='Jdrr_Super';

						if($row['display_upfront']==0 && $row['display_ecs']==0 && $row['campaignid']=='734'){
							if($omni_1_emp==1 || ($active_web == 1 && strtolower($this->module) == 'me')){

								$row['display_upfront']=1;
								$row['display_ecs']=1;
							}

						}
						if($row['display_upfront']==0 && $row['display_ecs']==0 && $row['campaignid']=='732'){
							if($omni_ultima_emp==1){

								$row['display_upfront']=1;
								$row['display_ecs']=0;
							}

						}
						if($row['display_upfront']==0 && $row['display_ecs']==0 && $row['campaignid']=='73'){
							if($omni_normal_emp==1){

								$row['display_upfront']=1;
								$row['display_ecs']=1;
							}

						}

						if( $row['campaignid']=='111'){
							if($row['display_upfront']==0 && $row['display_ecs']==0){
								if($pack_mini==1 || $flex==1){

									$row['display_upfront']=1;
									$row['display_ecs']=1;
								}
								else{
									$row['display_upfront']=0;
									$row['display_ecs']=0;
								}

							}

						}
							if( $row['campaignid']=='112'){
							if($row['display_upfront']==0 && $row['display_ecs']==0){
								if($ultra_pack_oneplusone==1){

									$row['display_upfront']=1;
									$row['display_ecs']=1;
								}
								else{
									$row['display_upfront']=0;
									$row['display_ecs']=0;
								}

							}

						}



						if($this->expiredePackflg==1 && $row['campaignid']=='1'){
							$row['display_upfront']=1;
									$row['display_ecs']=1;
									$row['campaign_name']='Package Expired';
						}
						 
						if(substr($row['campaignid'], 0, 2) === '11' && $row['campaignid']!='112'){
							if(isset($extra_package_details[$row['campaignid']])) {
								$row['display_upfront']=$extra_package_details[$row['campaignid']]['display_upfront'];
								$row['display_ecs']=$extra_package_details[$row['campaignid']]['display_ecs'];
							}
						}
						if(substr($row['campaignid'], 0, 2) === '74'){
							if(isset($omniextradetails[$row['campaignid']])) {
								$row['display_upfront']=$omniextradetails[$row['campaignid']]['display_upfront'];
								$row['display_ecs']=$omniextradetails[$row['campaignid']]['display_ecs'];
							}
						}
						
						if($row['display_upfront']!=0 || $row['display_ecs']!=0 ){
						$datarr[$type][$row['campaignid']]['price_upfront_discount']='-';
						$datarr[$type][$row['campaignid']]['price_upfront_two_years']='-';
						$datarr[$type][$row['campaignid']]['name']=$row['campaign_name'];
						$datarr[$type][$row['campaignid']]['order']=$row['display_order_new'];
						$datarr[$type][$row['campaignid']]['campaignid']=$row['campaignid'];
						$datarr[$type][$row['campaignid']]['price_upfront_display']=$row['price_upfront_display'];
						$datarr[$type][$row['campaignid']]['price_ecs_display']=$row['price_ecs_display'];

						$datarr[$type][$row['campaignid']]['campaign_description']=$row['upfront_campaign_description'];
						if($row['omni_type']!=null){
								$datarr[$type][$row['campaignid']]['omni_type']=$row['omni_type'];
							}

							if($row['display_upfront']!=0){
								$datarr[$type][$row['campaignid']]['price_upfront']=($row['setup_upfront']+$row['price_upfront_actual']);
								if(isset($flexi_val) && $flexi_val != 0 && $row['campaignid'] == '735'){
									$datarr[$type][$row['campaignid']]['price_upfront']= $flexi_val;
								}else if(filter_var($row['price_upfront_actual'], FILTER_VALIDATE_INT) !== false && $row['campaignid'] != 734 ){

								$datarr[$type][$row['campaignid']]['price_upfront']=($row['setup_upfront']+$row['price_upfront_actual']);
								}
								else{
									$datarr[$type][$row['campaignid']]['price_upfront']=$row['price_upfront_actual'];
								}
								$datarr[$type][$row['campaignid']]['down_payment']='-';
							}
							else
								$datarr[$type][$row['campaignid']]['price_upfront']='-';

							if($row['display_ecs']!=0){
								$datarr[$type][$row['campaignid']]['price_ecs']=$row['price_ecs_actual'];
								if(isset($flexi_val) && $flexi_val != 0  && $row['campaignid'] == '735'){
									$datarr[$type][$row['campaignid']]['price_ecs']= $flexi_val/12;
								}


								$datarr[$type][$row['campaignid']]['down_payment']=$row['setup_ecs'];
							}
							else
								$datarr[$type][$row['campaignid']]['price_ecs']='-';




							if($row['campaignid']=='51'){
								$datarr[$type][$row['campaignid']]['banner_rules']=$banner_arr;
								$datarr[$type][$row['campaignid']]['price_upfront']=$banner_arr['banner_single_unit'];
								$datarr[$type][$row['campaignid']]['price_ecs']=$banner_arr['banner_ecs_per_rotation'] *2;
								$datarr[$type][$row['campaignid']]['down_payment']=$datarr[$type][$row['campaignid']]['price_ecs']*3;
								if($banner_arr['no_of_rotation']==1 && (!isset($campaigns['225']) && !isset($campaigns['750']) && !isset($campaigns['751']) && !isset($campaigns['752']) && !isset($campaigns['753']) && !isset($campaigns['2252']) && !isset($campaigns['2253']))){

									if($banner_3years == 1){
										$campaigns['61']='61';
									}else if($banner_10years == 1){
										$campaigns['41']='41';
									}else{
										$campaigns['51']='51';
								}
							}
							}

							if($row['campaignid']=='1'){
								$datarr[$type][$row['campaignid']]['setup_upfront']=$row['setup_upfront'];

							}
							if($row['setup_upfront']=='banner_rotatation'){

								$rot_price=$banner_arr['banner_upto_10'];
								if($row['setup_ecs']>2)
									$rot_price=$banner_arr['banner_above_10'];

									if(substr($row['campaignid'], 0, 1) === '5'){
										$banner_ecs = $banner_details['1']['ecs'];
										$banner_upfront = $banner_details['1']['upfront'];
									}else if(substr($row['campaignid'], 0, 1) === '6'){
										$banner_ecs = $banner_details['3']['ecs'];
										$banner_upfront = $banner_details['3']['upfront'];
									}else if(substr($row['campaignid'], 0, 1) === '4'){
										$banner_ecs = $banner_details['10']['ecs'];
										$banner_upfront = $banner_details['10']['upfront'];
									}

									if($banner_arr['no_of_rotation']>=4 && $banner_arr['rotation_avl'] != false){
										if($banner_3years == 1){
											$campaigns['64']= '64';
										}else if($banner_10years == 1){
											$campaigns['44']= '44';
										}else{
										$campaigns['54']='54';
									}
									}
									else if ($banner_arr['no_of_rotation']==2 && $banner_arr['rotation_avl'] != false){
										if($banner_3years == 1){
											$campaigns['62']= '62';
										}else if($banner_10years == 1){
											$campaigns['42']= '42';
										}else{
										$campaigns['52']='52';
									}
									}
									else if ($banner_arr['no_of_rotation']==3 && $banner_arr['rotation_avl'] != false){
										if($banner_3years == 1){
											$campaigns['63']= '63';
										}else if($banner_10years == 1){
											$campaigns['43']= '43';
										}else{
										$campaigns['53']='53';
									}
									}
								$ecs_price=round(($row['setup_ecs']*$rot_price)/3);
								$datarr[$type][$row['campaignid']]['price_upfront']=$row['setup_ecs']*$banner_upfront; 
								
								if($row['display_ecs']!=0){
									$datarr[$type][$row['campaignid']]['price_ecs']=$banner_ecs*$row['setup_ecs']; 
									$datarr[$type][$row['campaignid']]['down_payment']=$datarr[$type][$row['campaignid']]['price_ecs']*3;
								}else{
									$datarr[$type][$row['campaignid']]['price_ecs']='-';
								}
							}



							if($national_main == 0 && $datarr[$type]['10'])
							{
								$datarr[$type][$row['campaignid']]['checked']= true;
								$datarr[$type][$row['campaignid']]['live']= false;
								$datarr[$type][$row['campaignid']]['down_payment']= 'system budget';
								$datarr[$type][$row['campaignid']]['price_upfront']= 'system budget';
								$datarr[$type][$row['campaignid']]['price_ecs']= 'system budget';
							}
							else if($national_main == 1 && $datarr[$type]['10'])
							{
								$datarr[$type][$row['campaignid']]['live']= true;
								$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);
								$datarr[$type][$row['campaignid']]['down_payment']= 'system budget';
								$datarr[$type][$row['campaignid']]['price_upfront']= 'system budget';
								$datarr[$type][$row['campaignid']]['price_ecs']= 'system budget';
							}
							else
							{
								
								/*if(in_array($row['campaignid'], $campaigns) && $row['campaignid'] == '225')
								{
									$sqljdrrplus ="select duration from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1 and campaignid in (22)";
									$resjdrrplus = parent::execQuery($sqljdrrplus, $this->conn_finance_temp);
									//$campaigns=array();
									if($resjdrrplus && mysql_num_rows($resjdrrplus))
										{
											$rowjdrrplus=mysql_fetch_assoc($resjdrrplus);
										}
									if($rowjdrrplus['duration'] == 730)
									$datarr[$type]['2252']['checked'] = 1;
									else if($rowjdrrplus['duration'] == 3650)
									$datarr[$type]['2253']['checked'] = 1;
									else
									$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);			
									//print_r($rowjdrrplus);	
									//$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);	
									//print_r($row['campaignid']);
									//echo '<br>';	
								}
								else	
								{*/
									$datarr[$type][$row['campaignid']]['checked']=in_array($row['campaignid'], $campaigns);
								//}	

							}

							if($datarr[$type]['10'] && $this->national_change_state['state_change'] == 1)
							{
								$datarr[$type][$row['campaignid']]['checked'] = true;
								$datarr[$type][$row['campaignid']]['state_change'] = 1;
							}
							else if($datarr[$type]['10'] && $this->national_change_state['state_change'] == 0)
							{
								$datarr[$type][$row['campaignid']]['state_change'] = 0;
							}

							if($combodiscount>0){

								$disc=$combodiscount/100;
								if(substr($row['campaignid'], 0, 2) === '73'){
									if($row['campaignid']!='734' &&  $row['campaignid']!='73' && $row['campaignid']!='732'  && $row['campaignid']!='735' && $row['campaignid']!='736'){
										if(isset($comboextradetails[$row['campaignid']])) {
										$price=$comboextradetails[$row['campaignid']]['combo_upfront'];
										$datarr[$type][$row['campaignid']]['price_upfront_discount']=$price- ($price * $disc) ;
										$datarr[$type][$row['campaignid']]['price_upfront_two_years']=$price+ ($price * $disc) ;
										$datarr[$type][$row['campaignid']]['price_upfront']=$price;
										if($row['campaignid'] == 737 && $ecssecyrinc==1){
											$temp = ($comboextradetails[$row['campaignid']]['combo_ecs'] * 50)/100;
											$temp =$comboextradetails[$row['campaignid']]['combo_ecs'] + $temp;
											$datarr[$type][$row['campaignid']]['price_ecs']=floor($temp);
											$datarr[$type][$row['campaignid']]['down_payment']=(floor($temp)*3);
											$datarr[$type][$row['campaignid']]['price_upfront_two_years']=$datarr[$type][$row['campaignid']]['price_upfront_two_years']*1.5;
										}else{
											$datarr[$type][$row['campaignid']]['price_ecs']=$comboextradetails[$row['campaignid']]['combo_ecs'];
											$datarr[$type][$row['campaignid']]['down_payment']=($comboextradetails[$row['campaignid']]['combo_ecs']*3);
										}
									}
										//~ if(strtolower($this->module) == 'me'){
												$row['display_upfront']=0;
												$row['display_ecs']=0;
												unset($campaigns[$row['campaignid']]);
												unset($datarr[$type][$row['campaignid']]);
										//~ }
									}
								}
							}
							
							if(substr($row['campaignid'], 0, 2) === '74'){
								if(isset($omniextradetails[$row['campaignid']])) {
									$datarr[$type][$row['campaignid']]['down_payment']=$omniextradetails[$row['campaignid']]['down_payment'];  
									if($omniextradetails[$row['campaignid']]['display_ecs'] == 1){
										$datarr[$type][$row['campaignid']]['price_ecs']=$omniextradetails[$row['campaignid']]['ecs'];
									}
									if($omniextradetails[$row['campaignid']]['display_upfront'] == 1){
										$datarr[$type][$row['campaignid']]['price_upfront']=$omniextradetails[$row['campaignid']]['upfront'];  
									}
								}
								if(strtolower($this->module) == 'tme' && $row['campaignid'] != '741' ){
									$row['display_upfront']=0;
									$row['display_ecs']=0;
									unset($campaigns[$row['campaignid']]);
									unset($datarr[$type][$row['campaignid']]);
								}
										
							}


						}
						if(substr($row['campaignid'], 0, 2) === '11' && $row['campaignid']!='112'){
								//~ if($active_web==0){
									if(isset($extra_package_details[$row['campaignid']])) {
										$datarr[$type][$row['campaignid']]['price_upfront']=$extra_package_details[$row['campaignid']]['package_value'] *12 ;
										if($row['campaignid'] == 116 && $ecssecyrinc==1 ){
											$temp = ($extra_package_details[$row['campaignid']]['package_value'] * 50)/100;
											$temp = $extra_package_details[$row['campaignid']]['package_value'] + $temp;
											$datarr[$type][$row['campaignid']]['price_ecs']=floor($temp) ;
											$datarr[$type][$row['campaignid']]['down_payment']=floor($temp)*3;
										}else{
											$datarr[$type][$row['campaignid']]['price_ecs']=$extra_package_details[$row['campaignid']]['package_value'];
											$datarr[$type][$row['campaignid']]['down_payment']=$extra_package_details[$row['campaignid']]['package_value']*3;
										}
										
									if($combodiscount>0){
											$disc=$combodiscount/100;
											if($row['campaignid']=='116' && (strtolower($this->data_city) == 'delhi' || strtolower($this->data_city) == 'hyderabad' || strtolower($this->data_city) == 'kolkata' || strtolower($this->data_city) == 'chandigarh')){
												$disc = 0.375;
											}
											$price_pack =$extra_package_details[$row['campaignid']]['package_value'] *12;
											$datarr[$type][$row['campaignid']]['price_upfront_discount']=$price_pack - ($price_pack * $disc) ;
											
											if($row['campaignid'] == 116 && $ecssecyrinc==1 ){
												$pck_up = $datarr[$type][$row['campaignid']]['price_ecs'] * 12;
												$datarr[$type][$row['campaignid']]['price_upfront_two_years']=($pck_up + ($pck_up * 0.5));
											}else
											{
												$datarr[$type][$row['campaignid']]['price_upfront_two_years']=$price_pack + ($price_pack * 0.5) ;
											}
											
										}
										else{
											$datarr[$type][$row['campaignid']]['price_upfront_discount']='-';
											$datarr[$type][$row['campaignid']]['price_upfront_two_years']='-';
										}
									}
								//~ }
								else{
										if($row['campaignid']!='111' && $row['campaignid']!='118' && $row['campaignid']!='119'){
											unset($datarr[$type][$row['campaignid']]);
											unset($campaigns[$row['campaignid']]);
										}
								}


							}
						

				}

				$isnational = $this->isnationallisting();
				
				if($this->module=='me' || $this->module=='ME')
				{
				$datarr['Normal']['10L'] =  $datarr['Normal']['10'];
				$datarr['Normal']['10L']['campaignid'] = '10L';
				$datarr['Normal']['10L']['name'] = 'National Listing VFL';
				$datarr['Normal']['10L']['campaign_description'] = 'National Listing Life Time';
				$datarr['Normal']['10L']['checked'] = false;
				
				
				$sqlcheckfinance="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid = '10' and duration='3650' and recalculate_flag=1";
				
				$resfinancetemp = parent::execQuery($sqlcheckfinance, $this->conn_finance_temp);
				
				if($resfinancetemp && mysql_num_rows($resfinancetemp))
				{
					
					$datarr['Normal']['10']['checked'] = false;
					$datarr['Normal']['10L']['checked'] = true;
				}
				}
				
				
				
				
				
				if($isnational['nationallisting'] == 0 || $isnational['eligible_flag'] == 0)
				{
					unset($datarr['Normal']['10']);
					if($this->module=='me' || $this->module=='ME')
					{
						unset($datarr['Normal']['10L']);
					}
				}
				
				if(strtolower($this->module) == 'me') {
					$sql ="SELECT a.contractCode,a.compname,b.joining_date,b.pack_selected,b.website_10years,a.team_type FROM tblContractAllocation AS a LEFT JOIN online_regis.extra_prev_user AS b ON a.parentcode = b.empcode WHERE a.allocationType IN('25','99') AND a.contractcode='".$this->parentid."' ORDER BY a.allocationtime DESC LIMIT 1";
					$sql_res   = parent::execQuery($sql, $this->conn_temp);
					if($sql_res && mysql_num_rows($sql_res)>0)
					{	
						$row_sql = mysql_fetch_assoc($sql_res);
						if($row_sql['joining_date'] != '' && $row_sql['joining_date'] != null && $row_sql['joining_date'] != '0000-00-00' ) {
							$joining_date = $row_sql['joining_date'];
							$pack_team=1;
						}
						
						if($row_sql['pack_selected'] == 1){
							$pack_selected = 2;
						}
						
						if($row_sql['website_10years'] == 1){
							$website_10years = 1;
						}
						
						if($row_sql['team_type'] != null && $row_sql['team_type'] != '' && strtolower($row_sql['team_type']) == 'retention'){
							$this->check_retention();
						}
						
					}
				}
				
				$result_msg_arr['data']['discount'] = array();
				if($pack_team == 1 && isset($team_budget['pck_dis'])) {
					$current_date  = date('Y-m-d'); 	
					$days_diff = date_diff(date_create($joining_date), date_create($current_date));
					$no_days = $days_diff->format("%a");
					if($no_days <= 60){
						$result_msg_arr['data']['discount']['team_dis'] = $team_budget['pck_dis']['2month'];
					}else if($no_days > 60 && $no_days <= 120 ){
						$result_msg_arr['data']['discount']['team_dis'] = $team_budget['pck_dis']['4month'];
					}else if($no_days > 120 && $no_days <= 180 ){
						$result_msg_arr['data']['discount']['team_dis'] = $team_budget['pck_dis']['6month'];
					}
				}
				
				//~ if(($pack_selected == 1 || $pack_selected == 2) && strtolower($this->module) == 'me'){
						//~ $datarr["Package"][118]['price_upfront_discount']='-';
						//~ $datarr["Package"][118]['price_upfront_two_years']='-';
						//~ $datarr["Package"][118]['name']="Lifetime (10 year Package)";
						//~ $datarr["Package"][118]['order']=7;
						//~ $datarr["Package"][118]['campaignid']=118;
						//~ $datarr["Package"][118]['price_upfront_display']='system budget';
						//~ $datarr["Package"][118]['price_ecs_display']='system budget';
						//~ $datarr["Package"][118]['price_upfront']='system budget';
						//~ $datarr["Package"][118]['price_ecs']='system budget';
						//~ $datarr["Package"][118]['down_payment']='system budget';
				//~ }
				
				//~ if($website_10years != 1){
					//~ unset($datarr["Omni"][748]);
				//~ }
				
				$result_msg_arr['error']['code'] =0;
				$result_msg_arr['error']['msg'] = 'success';
				$result_msg_arr['data']['standard'] =$datarr;
				$result_msg_arr['data']['activephonesearchcontractflag'] =$this->activephonesearchcontractflag;
				$result_msg_arr['data']['instrument_type'] =$instrument_type;
				header('Content-Type: application/json');
				echo json_encode($result_msg_arr);exit;
		}

	}
	function checkactiveTemplate(){

		require_once('omniDetailsClass.php');
		$paramsnew=$this->params;
		$paramsnew['action']=26;
		
		$omniDetailsClassobj = new omniDetailsClass($paramsnew);
		$result = $omniDetailsClassobj->priceChartTemplate(); 
		
		$verticalid=$result['ts']['vid'];
		$template_id=$result['ts']['ttyp'];
		$template_name=$result['ts']['tnm'];

		$can_take=0;
		if($result['themeExist']==1 || $result['themeExist']=='1' ){


			$retarr['theme_id']='yes';
			$can_take=1;

		}
		else{
			$retarr['theme_id']='no';

		}
		$retarr['flag']=$can_take;
		$retarr['vertical_id']=$verticalid;
		$retarr['template_id']=$template_id;
		$retarr['template_name']=$template_name;
		return $retarr;


	}
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_idc[0], $this->conn_idc[1], $this->conn_idc[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}
	function isnationallisting()
	{
		$result['nationallisting'] = 0;

		$sql_national="SELECT * FROM tbl_national_listing_temp WHERE parentid='" . $this->parentid . "'";

		$qry_national 	= parent::execQuery($sql_national,$this->conn_temp);
		if($qry_national && mysql_num_rows($qry_national))
		{
			$row_national= mysql_fetch_assoc($qry_national);
			if(count($row_national)){
				$result['nationallisting'] = 1;
				$result['nationallisting_type'] = $row_national['state_zone'];
				$result['Category_nationalid']	= str_replace('|P|',',',trim($row_national['Category_nationalid'],'|P|'));
				$result['eligible_flag'] = 1;
			}
		}
		if($result['Category_nationalid'])
		{
			//$excl = "SELECT category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . $result['Category_nationalid'] . ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
			//$excl_national 	= parent::execQuery($excl,$this->conn_catmaster);
			$cat_params = array();
			$cat_params['page']= 'financeDisplayClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_scope';

			$where_arr  	=	array();
			if($result['Category_nationalid']!=''){
				$where_arr['national_catid'] = $result['Category_nationalid'];
				$where_arr['isdeleted'] 	= '0';
				$where_arr['mask_status'] 	= '0';
				$where_arr['category_scope'] 	= '1,2';
				$cat_params['where']		= json_encode($where_arr);
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				$cat_scope_arr = array();
				foreach($cat_res_arr['results'] as $key =>$row_excl)
				{
					if($row_excl['category_scope']!=''){
						$cat_scope_arr[] = $row_excl['category_scope'];
					}
				}
				$distr = min($cat_scope_arr);
				
				if($distr != '1' && $distr != '2')
				{
					$result['eligible_flag'] = 0;
				}
			}
			
			
		}
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_business_temp_data";
				$mongo_inputs['fields'] 	= "catIds,nationalcatIds";
				$row_temp_data = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sql_temp_data = "SELECT catIds,nationalcatIds FROM tbl_business_temp_data WHERE contractid='" . $this->parentid . "'";
				$res_temp_data = parent::execQuery($sql_temp_data,$this->conn_temp);
				if($this->trace)
				{
					echo "<br> sql :: ".$sql_temp_data;
					echo "<br> res :: ".$res_temp_data;
					echo "<br> rows:: ".mysql_num_rows($res_temp_data);
				}
				if($res_temp_data && mysql_num_rows($res_temp_data)>0){
					$row_temp_data = mysql_fetch_assoc($res_temp_data);
				}
			}
			
			
		if($row_temp_data['nationalcatIds']) {	
		//$excl = "SELECT category_name, min(category_scope) as distr, count(national_catid) FROM tbl_categorymaster_generalinfo WHERE national_catid in (" . str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|')). ") and isdeleted = 0 AND mask_status=0 AND (category_scope = 1 or category_scope = 2) GROUP BY category_name";
        
        //$excl_national 	= parent::execQuery($excl,$this->conn_catmaster);
			$cat_params = array();
			$cat_params['page']= 'financeDisplayClass';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_scope';

			$where_arr  	=	array();			
			$where_arr['national_catid'] = str_replace('|P|',',',trim($row_temp_data['nationalcatIds'],'|P|'));
			$where_arr['isdeleted'] 	= '0';
			$where_arr['mask_status'] 	= '0';
			$where_arr['category_scope'] 	= '1,2';
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
			{
				$cat_scope_arr = array();
				foreach($cat_res_arr['results'] as $key =>$cat_arr)
				{
					if($cat_arr['category_scope']!=''){
						$cat_scope_arr[] = $cat_arr['category_scope'];
					}					
				}
				$distr = min($cat_scope_arr);
				if($distr != '1' && $distr != '2')
				{
					$result['eligible_flag'] = 0;
					$result['Nonpaid_cat']   = 1;	
				}
				
			}
			else
			{
				$result['eligible_flag'] = 0;
				$result['Nonpaid_cat']   = 1;
			}
		}
		
			
		return $result;

	}


	function makeEcs(){

 		$sql="update campaigns_selected_status set ecs_flag='ecs' where  parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		$result_msg_arr['error']['code'] =0;
				$result_msg_arr['error']['msg'] = 'success';
				$result_msg_arr['data']['result'] =$datarr;
				echo json_encode($result_msg_arr);exit;
	}
	function makeUpfront(){
	 		$sql="update campaigns_selected_status set ecs_flag='upfront' where  parentid='".$this->parentid."'";
			$res = parent::execQuery($sql, $this->conn_temp);
			$result_msg_arr['error']['code'] =0;
				$result_msg_arr['error']['msg'] = 'success';
				$result_msg_arr['data']['result'] =$datarr;
				echo json_encode($result_msg_arr);exit;
	}
	function tempToMainDependent($genio_lite_campaign = null,$dependant_arr = null,$version = null){
	 	//~ $checktemp = "select * from tbl_omni_extradetails_temp where parentid='".$this->parentid."'";
		//~ $checktempres = parent::execQuery($checktemp, $this->conn_temp);
		//~ if($checktempres && mysql_num_rows($checktempres)>0){
			$sqladd = "";
			if($version != null)
			{
				$sqladd = "and version='".$version."'";
			}	
			$campaigndetails="select * from dependant_campaign_details_temp where parentid='".$this->parentid."' $sqladd";
			$campaigndetailsres = parent::execQuery($campaigndetails, $this->conn_temp);
			if($campaigndetailsres && mysql_num_rows($campaigndetailsres)>0)
	 		{
		 		while($campaigndetailsrow=mysql_fetch_assoc($campaigndetailsres))
					{
				 		  $dependentsql = "INSERT INTO dependant_campaign_details_dealclosed set
					 					parentid='".$campaigndetailsrow['parentid']."',
					 					pri_campaignid='".$campaigndetailsrow['pri_campaignid']."',
					 					version  	= '".$campaigndetailsrow['version']."',
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'
					 					ON DUPLICATE KEY UPDATE
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'";
				$dependentsqlres = parent::execQuery($dependentsql, $this->dbConIdc);

				 		   $dependentsql = "INSERT INTO dependant_campaign_details set
					 					parentid='".$campaigndetailsrow['parentid']."',
					 					pri_campaignid='".$campaigndetailsrow['pri_campaignid']."',
					 					version  	= '".$campaigndetailsrow['version']."',
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'
					 					ON DUPLICATE KEY UPDATE
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'";
				$dependentsqlres = parent::execQuery($dependentsql, $this->conn_finance);
				}
			}
			else if(count($dependant_arr) > 0)
			{
				foreach($dependant_arr as $key => $campaigndetailsrow)
				{
					
				 		  $dependentsql = "INSERT INTO dependant_campaign_details_dealclosed set
					 					parentid='".$campaigndetailsrow['parentid']."',
					 					pri_campaignid='".$campaigndetailsrow['pri_campaignid']."',
					 					version  	= '".$campaigndetailsrow['version']."',
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'
					 					ON DUPLICATE KEY UPDATE
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'";
				$dependentsqlres = parent::execQuery($dependentsql, $this->dbConIdc);

				 		  $dependentsql = "INSERT INTO dependant_campaign_details set
					 					parentid='".$campaigndetailsrow['parentid']."',
					 					pri_campaignid='".$campaigndetailsrow['pri_campaignid']."',
					 					version  	= '".$campaigndetailsrow['version']."',
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'
					 					ON DUPLICATE KEY UPDATE
					 					combo_type  	= '".$campaigndetailsrow['combo_type']."',
					 					dep_campaignid  = '".$campaigndetailsrow['dep_campaignid']."',
					 					dep_budget  	= '".$campaigndetailsrow['dep_budget']."',
					 					entry_date='".date('Y-m-d H:i:s')."',
					 					dep_duration  	= '".$campaigndetailsrow['dep_duration']."'";
				$dependentsqlres = parent::execQuery($dependentsql, $this->conn_finance);
				}
			}

			if($dependentsqlres){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				//echo json_encode($result_msg_arr);exit;
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				//echo json_encode($result_msg_arr);exit;
			}
			
			
			if(count($genio_lite_campaign)>0)
					return $result_msg_arr;
				else {
					echo json_encode($result_msg_arr);exit;
				}
			
		//~ }
		//~ else{
			//~ $result_msg_arr['error']['code'] = 2;
			//~ $result_msg_arr['error']['msg'] = "No Data For Combo";
			//~ echo json_encode($result_msg_arr);exit;
		//~ }
	}
	function tempToMainPaymentType(){
	 $combo_detailssql="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
			$combo_detailsres = parent::execQuery($combo_detailssql, $this->conn_finance);
			if($combo_detailsres && mysql_num_rows($combo_detailsres)>0)
	 		{
		 		while($combo_detailsrow=mysql_fetch_assoc($combo_detailsres))
					{
				 		 $sql_ins_combo = "INSERT INTO tbl_payment_type_dealclosed set
					 					parentid='".$combo_detailsrow['parentid']."',
					 					version='".$combo_detailsrow['version']."',
					 					payment_type = '".$combo_detailsrow['payment_type']."',
					 					inserted_time='".date('Y-m-d H:i:s')."',
					 					payment_type_flag='".$combo_detailsrow['payment_type_flag']."'
					 					ON DUPLICATE KEY UPDATE
					 					payment_type = '".$combo_detailsrow['payment_type']."',
					 					payment_type_flag='".$combo_detailsrow['payment_type_flag']."',
					 					inserted_time='".date('Y-m-d H:i:s')."'";
						$res_ins_combo = parent::execQuery($sql_ins_combo, $this->conn_finance);

						if($res_ins_combo){
							$ins_sql ="insert into payment_otherdetails set
										parentid='".$combo_detailsrow['parentid']."',
										version='".$combo_detailsrow['version']."',
										bitvalue='".$combo_detailsrow['payment_type_flag']."',
										payment_type = '".$combo_detailsrow['payment_type']."'
										on duplicate key update
										bitvalue='".$combo_detailsrow['payment_type_flag']."',
										payment_type = '".$combo_detailsrow['payment_type']."'";
							$res_ins =  parent::execQuery($ins_sql, $this->conn_finance);
						}
				}

				if($res_ins_combo){
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

	}
	function mainToFinPaymentType(){
	 $combo_detailssql="select * from tbl_payment_type_dealclosed where parentid='".$this->parentid."' and version='".$this->version."'";
			$combo_detailsres = parent::execQuery($combo_detailssql, $this->conn_finance);
			if($combo_detailsres && mysql_num_rows($combo_detailsres)>0)
	 		{
		 		while($combo_detailsrow=mysql_fetch_assoc($combo_detailsres))
					{
				 		 $sql_ins_combo = "INSERT INTO tbl_payment_type_approved set
					 					parentid='".$combo_detailsrow['parentid']."',
					 					version='".$combo_detailsrow['version']."',
					 					payment_type = '".$combo_detailsrow['payment_type']."',
					 					inserted_time='".date('Y-m-d H:i:s')."'
					 					ON DUPLICATE KEY UPDATE
					 					payment_type = '".$combo_detailsrow['payment_type']."',
					 					inserted_time='".date('Y-m-d H:i:s')."'";
						$res_ins_combo = parent::execQuery($sql_ins_combo, $this->conn_finance);
				}
				if($res_ins_combo){
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

	}

	function displayLowValuePackage(){
		if(trim($this->usercode)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "UserCode Missing";
			echo json_encode($result_msg_arr);exit;
		}
		if(trim($this->module_name)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Name Is Missing";
			echo json_encode($result_msg_arr);exit;
		}
		$wherecond='';
		switch(strtolower($this->module_name)){
			case 'me':$wherecond="and me=1";
			break;
			case 'jda':$wherecond="and jda=1";
			break;
			case 'tme':$wherecond="and tme=1";
			break;
			case 'cs':$wherecond="and cs=1";
			break;
			default:
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Wrong Module Name Passed";
			echo json_encode($result_msg_arr);exit;


		}
		$flagcheck=0;
		 $sqlcheckcity="SELECT * FROM online_regis.city_wise_control where city='".$this->data_city_cm."' and low_lvl_pack=1 $wherecond";

		$checkcityres = parent::execQuery($sqlcheckcity, $this->conn_idc);
		if($checkcityres && mysql_num_rows($checkcityres)>0)
 		{
	 		$flagcheck=1;
		}

    	 $sql="select * from online_regis.extra_prev_user where empcode='".$this->usercode."' and 100rsweekly=1";

    	$res = parent::execQuery($sql, $this->conn_idc);
    	if($res && mysql_num_rows($res)>0 )
    	{
	 		$flagcheck=1;
    	}
    	if($flagcheck==1){
    		$result_msg_arr['error']['code'] = 0;
    		$result_msg_arr['error']['msg'] = "Show Weekly";
    		echo json_encode($result_msg_arr);exit;
    	}
    	else{
    		$result_msg_arr['error']['code'] = 1;
    		$result_msg_arr['error']['msg'] = "Hide Weekly";
    		echo json_encode($result_msg_arr);exit;
    	}


	}

	function saveCustomValues(){
		if(trim($this->usercode)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "UserCode Missing";
			echo json_encode($result_msg_arr);exit;
		}
		if(trim($this->campaignid)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Campaignid Missing";
			echo json_encode($result_msg_arr);exit;
		}
		if((trim($this->custom_value)=='' || $this->custom_value==0) && $this->campaignid != 73 && $this->campaignid != 72 ){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Custom Value Missing";
			echo json_encode($result_msg_arr);exit;
		}
	 	$sql_custom = "INSERT INTO tbl_custom_budget_campaign_wise set
						parentid='".$this->parentid."',
						version='".$this->version."',
						campaignid='".$this->campaignid."',
						user_id='".$this->usercode."',
						time_inserted='".date('Y-m-d H:i:s')."',
						custom_value='".$this->custom_value."'
						ON DUPLICATE KEY UPDATE
						user_id='".$this->usercode."',
						time_inserted='".date('Y-m-d H:i:s')."',
						custom_value='".$this->custom_value."'";
		$res_cust = parent::execQuery($sql_custom, $this->conn_temp);
		 if($res_cust){
		 	$result_msg_arr['error']['code'] = 0;
		 	$result_msg_arr['error']['msg'] = "Success";
		 	echo json_encode($result_msg_arr);exit;
		 }
		 else{
		 	$result_msg_arr['error']['code'] = 1;
		 	$result_msg_arr['error']['msg'] = "Error!!";
		 	echo json_encode($result_msg_arr);exit;
		 }

	}
	function getCustomValues($from=0){
		 $sqlget="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' ";
		$res = parent::execQuery($sqlget, $this->conn_temp);
		$customarr=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
 			{
 				$customarr[$row['campaignid']]=$row['custom_value'];
			}
			if($from==1){
				return $customarr;
			}
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Custom Values Found";
			$result_msg_arr['error']['data'] = $customarr;
			echo json_encode($result_msg_arr);exit;
		}
		else{
			if($from==1){
				return false;
			}
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Custom Values";
			echo json_encode($result_msg_arr);exit;
		}
	}
	function deleteCombo(){
		$pri_campaignid="";
		$sql="select * from dependant_campaign_details_temp where  parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql, $this->conn_temp);
		$customarr=array();
		if($res && mysql_num_rows($res))
 		{
 			while($row=mysql_fetch_assoc($res))
 			{
 				$pri_campaignid=$row['pri_campaignid'];
 			}
 		}
 		if($pri_campaignid!=''){
	 		require_once('miscapijdaclass.php');
	 		require_once('versioninitclass.php');
			$miscapijdaclass_obj = new miscapijdaclass($this->params);
	 		$pri_campaignid.=",72,75,74,82,83,86,2,5,13,22";
	 		if($pri_campaignid== 10){
				$pri_campaignid ="5,13,22";
			}
			$result = $miscapijdaclass_obj->updatefinancetempTable($pri_campaignid);
			$sql="delete from dependant_campaign_details_temp where  parentid='".$this->parentid."' and version='".$this->version."'";
			$res = parent::execQuery($sql, $this->conn_temp);
			if($res){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				echo json_encode($result_msg_arr);exit;
			}
		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Combo To Delete";
			echo json_encode($result_msg_arr);exit;
		}


	}
	function proposalImagePath(){
		$cityname=$this->data_city;
        $cityname_img='mumbai';
        $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'chandigarh','coimbatore','jaipur');

        if(!in_array($cityname, $dataservers)){
        	$getdata_city = "SELECT mapped_cityname FROM d_jds.city_master WHERE ct_name='".$cityname."'";
			$resdatacity = parent::execQuery($getdata_city, $this->dbConIro);
			if($resdatacity && mysql_num_rows($resdatacity)){
				while($rowdatacity=mysql_fetch_assoc($resdatacity)){
					$cityname_img=$rowdatacity['mapped_cityname'];
				}
			}
        }
        else
        $cityname_img=$cityname;
        $imgname="images/rest_listing1_".ucwords(strtolower($cityname_img)).".jpg";
        $url="http://".GNO_URL."/business/";
    	if($cityname_img!=''){
    		$result_msg_arr['error']['code'] = 0;
    		$result_msg_arr['error']['msg'] = "Success";
    		$result_msg_arr['data']['proposal_img_path'] = $url.$imgname;
    		echo json_encode($result_msg_arr);exit;
    	}
        else{
        	$result_msg_arr['error']['code'] = 1;
        	$result_msg_arr['error']['msg'] = "No Data Found";
        	echo json_encode($result_msg_arr);exit;
        }

	}

	function saveEmailMobileDetails(){
		require_once('includes/regfeeclass.php');


		$upt=0;
		if(trim($this->invoice_mobile)!='' ){

			$sql="select campaignid  from tbl_companymaster_finance_temp where  parentid='".$this->parentid."' and recalculate_flag=1 and campaignid in (1,2) and budget>0";
			$res = parent::execQuery($sql, $this->conn_finance_temp);

	 		if(($res && mysql_num_rows($res)>0)  || $this->skip_reg==1)
	 		{


				$regfeeclassobj = new regfeeclass($this->params);
				$contactno=$this->invoice_mobile;
				$result = $regfeeclassobj->getRegfee($contactno);
				if($result>0)
				{
					$result_msg_arr['error']['code'] = 1;
		    		$result_msg_arr['error']['msg'] = "Reg Fees Present! Please go to bform to add the number";
		    		echo json_encode($result_msg_arr);exit;
				}

		}




			$mobile_upt='';
			if($this->mob_feed==1)
				$mobile_upt.=", mobile_feedback='".$this->invoice_mobile."'";
			if($this->mob_disp==1)
				$mobile_upt.=", mobile_display='".$this->invoice_mobile."'";
				
			//mongo query
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt = array();
				$geninfo_upt['mobile'] = $this->invoice_mobile;
				
				if($this->mob_feed==1)
					$geninfo_upt['mobile_feedback'] = $this->invoice_mobile;
				if($this->mob_disp==1)
					$geninfo_upt['mobile_display'] = $this->invoice_mobile;
				
				$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
				
				$mongo_inputs['table_data'] = $mongo_data;
				$uptsqlres = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{
				$uptsql="update tbl_companymaster_generalinfo_shadow set mobile='".$this->invoice_mobile."' $mobile_upt where parentid='".$this->parentid."'";
				$uptsql = $uptsql."/* TMEMONGOQRY */";
				$uptsqlres = parent::execQuery($uptsql, $this->conn_temp_new);
			}
		}
		if(trim($this->invoice_email)!='' ){

			$email_upt='';
			if($this->email_disp==1)
				$email_upt.=", email_display='".$this->invoice_email."'";
			if($this->email_feed==1)
				$email_upt.=", email_feedback='".$this->invoice_email."'";

			//mongo query
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt = array();
				$geninfo_upt['email'] = $this->invoice_email;
				
				if($this->email_disp==1)
					$geninfo_upt['email_display'] = $this->invoice_email;
				if($this->email_feed==1)
					$geninfo_upt['email_feedback'] = $this->invoice_email;
				
				$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
				
				$mongo_inputs['table_data'] = $mongo_data;
				$uptsqlres = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{
				$uptsql="update tbl_companymaster_generalinfo_shadow set email='".$this->invoice_email."' $email_upt where parentid='".$this->parentid."'";
				$uptsql = $uptsql."/* TMEMONGOQRY */";
				$uptsqlres = parent::execQuery($uptsql, $this->conn_temp_new);
			}
		}
		if(trim($this->invoice_contact)!='' ){
			
			//mongo query
			if($this->mongo_flag == 1 || $this->mongo_tme == 1)
			{
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$geninfo_tbl = "tbl_companymaster_generalinfo_shadow";
				$geninfo_upt = array();
				$geninfo_upt['contact_person'] = $this->invoice_contact;
				$geninfo_upt['contact_person_display'] = $this->invoice_contact;
				$mongo_data[$geninfo_tbl]['updatedata'] = $geninfo_upt;
				
				$mongo_inputs['table_data'] = $mongo_data;
				$uptsqlres = $this->mongo_obj->updateData($mongo_inputs);
			}
			else
			{
				$uptsql="update tbl_companymaster_generalinfo_shadow set contact_person='".$this->invoice_contact."',contact_person_display='".$this->invoice_contact."' where parentid='".$this->parentid."'";
				$uptsql = $uptsql."/* TMEMONGOQRY */";
				$uptsqlres = parent::execQuery($uptsql, $this->conn_temp_new);
			}
		}
	/*	else if(trim($this->invoice_email)!=''){
			$uptsql="update tbl_companymaster_extradetails_shadow set invoice_email='".$this->invoice_email."' where parentid='".$this->parentid."'";
		}
*/

		if($uptsqlres){
    		$result_msg_arr['error']['code'] = 0;
    		$result_msg_arr['error']['msg'] = "Success";
    		echo json_encode($result_msg_arr);exit;
    	}
        else{
        	$result_msg_arr['error']['code'] = 1;
        	$result_msg_arr['error']['query'] = $uptsql;
        	$result_msg_arr['error']['msg'] = 'failure';
        	echo json_encode($result_msg_arr);exit;
        }

	}

	function tempToDealcloseBannerRotation(){
			$rotation_detsql="select * from catspon_banner_rotation_temp where parentid='".$this->parentid."'";
			$rotation_detres = parent::execQuery($rotation_detsql, $this->conn_temp);
			if($rotation_detres && mysql_num_rows($rotation_detres)>0)
			{
				while($rotation_detrow=mysql_fetch_assoc($rotation_detres))
				{
					$tbl_catsponsql = "INSERT INTO catspon_banner_rotation set
					parentid='".$rotation_detrow['parentid']."',
					budget='".$rotation_detrow['budget']."',
					no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
					payment_type = '".$rotation_detrow['payment_type']."',
					categories_for_spon = '".$rotation_detrow['categories_for_spon']."'
					ON DUPLICATE KEY UPDATE
					budget='".$rotation_detrow['budget']."',
					no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
					payment_type = '".$rotation_detrow['payment_type']."',
					categories_for_spon = '".$rotation_detrow['categories_for_spon']."'";
					$res_ins_combo = parent::execQuery($tbl_catsponsql, $this->conn_idc);
					$tbl_catsponsql = "INSERT INTO catspon_banner_rotation_dealclose_archive set
					parentid='".$rotation_detrow['parentid']."',
					budget='".$rotation_detrow['budget']."',
					no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
					payment_type = '".$rotation_detrow['payment_type']."',
					categories_for_spon = '".$rotation_detrow['categories_for_spon']."'";
					$res_ins_combo = parent::execQuery($tbl_catsponsql, $this->conn_idc);


				}

				if($res_ins_combo){
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
			else{
					$result_msg_arr['error']['code'] = 2;
					$result_msg_arr['error']['msg'] = "No Data";
					echo json_encode($result_msg_arr);exit;
			}

	}

	function mainIDCToMainBannerRotation(){
		$rotation_detsql="select * from catspon_banner_rotation where parentid='".$this->parentid."'";
		$rotation_detres = parent::execQuery($rotation_detsql, $this->conn_idc);
		if($rotation_detres && mysql_num_rows($rotation_detres)>0)
		{
			while($rotation_detrow=mysql_fetch_assoc($rotation_detres))
			{
				$tbl_catsponsql = "INSERT INTO catspon_banner_rotation set
				parentid='".$rotation_detrow['parentid']."',
				budget='".$rotation_detrow['budget']."',
				no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
				payment_type = '".$rotation_detrow['payment_type']."',
				categories_for_spon = '".$rotation_detrow['categories_for_spon']."'
				ON DUPLICATE KEY UPDATE
				budget='".$rotation_detrow['budget']."',
				no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
				payment_type = '".$rotation_detrow['payment_type']."',
				categories_for_spon = '".$rotation_detrow['categories_for_spon']."'";
				$res_ins_combo = parent::execQuery($tbl_catsponsql, $this->conn_finance);
				$tbl_catsponsql = "INSERT INTO catspon_banner_rotation_archive set
				parentid='".$rotation_detrow['parentid']."',
				budget='".$rotation_detrow['budget']."',
				no_of_rotation = '".$rotation_detrow['no_of_rotation']."',
				payment_type = '".$rotation_detrow['payment_type']."',
				categories_for_spon = '".$rotation_detrow['categories_for_spon']."'";
				$res_ins_combo = parent::execQuery($tbl_catsponsql, $this->conn_finance);


			}

			if($res_ins_combo){
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
		else{
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "No Data";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function tempToShadowCampaignMul(){

		// changesd --- now writing to main as shadow is not req
		$camp_mul_sql="select * from campaign_multiplier_temp where parentid='".$this->parentid."'";
		$camp_mul_res = parent::execQuery($camp_mul_sql, $this->conn_temp);
		if($camp_mul_res && mysql_num_rows($camp_mul_res)>0)
		{
			while($camp_mul_row=mysql_fetch_assoc($camp_mul_res))
			{
				$tbl_cam_sha = "INSERT INTO campaign_multiplier set
				parentid='".$camp_mul_row['parentid']."',
				version='".$camp_mul_row['version']."',
				campaignid='".$camp_mul_row['campaignid']."',
				actual_budget='".$camp_mul_row['actual_budget']."',
				multiplier = '".$camp_mul_row['multiplier']."'
				ON DUPLICATE KEY UPDATE
				actual_budget='".$camp_mul_row['actual_budget']."',
				multiplier = '".$camp_mul_row['multiplier']."'";

				$res_ins_combo = parent::execQuery($tbl_cam_sha, $this->conn_finance);


			}

			if($res_ins_combo){
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
		else{
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "No Data";
				echo json_encode($result_msg_arr);exit;
		}
	}
	function shadowToMainCampaignMul(){
		$camp_mul_sql="select * from campaign_multiplier_shadow where parentid='".$this->parentid."'";
		$camp_mul_res = parent::execQuery($camp_mul_sql, $this->conn_idc);
		if($camp_mul_res && mysql_num_rows($camp_mul_res)>0)
		{
			while($camp_mul_row=mysql_fetch_assoc($camp_mul_res))
			{
				$tbl_cam_sha = "INSERT INTO campaign_multiplier set
				parentid='".$camp_mul_row['parentid']."',
				version='".$camp_mul_row['version']."',
				campaignid='".$camp_mul_row['campaignid']."',
				multiplier = '".$camp_mul_row['multiplier']."'
				ON DUPLICATE KEY UPDATE
				multiplier = '".$camp_mul_row['multiplier']."'";

				$res_ins_combo = parent::execQuery($tbl_cam_sha, $this->conn_finance);


			}

			if($res_ins_combo){
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
		else{
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "No Data";
				echo json_encode($result_msg_arr);exit;
		}
	}
	
	function insertdependentinfo(){
		$ins_sql ="insert into dependant_campaign_details_temp set parentid='".$this->parentid."',combo_type='".$this->combotype."',pri_campaignid='".$this->pri_campaignid."',version='".$this->version."',dep_campaignid='".$this->depcamp."',dep_budget='".$this->dep_budget."',dep_duration='".$this->dep_duration."' on duplicate key update	combo_type='".$this->combotype."',pri_campaignid='".$this->pri_campaignid."',version='".$this->version."',dep_budget='".$this->dep_budget."',dep_duration='".$this->dep_duration."'";
		$res_ins_combo = parent::execQuery($ins_sql,$this->conn_temp);
		if($res_ins_combo){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			echo json_encode($result_msg_arr);exit;
		}else {
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error";
			echo json_encode($result_msg_arr);exit;
		}
	}
	
	function insertmultiplier(){
		$inst_sql = "insert into campaign_multiplier_temp set parentid='".$this->parentid."',version='".$this->version."',campaignid='".$this->campaignid."',actual_budget='".$this->actual_budget."',multiplier='".$this->multiplier."',usercode='".$this->ucode."',insert_date=now() on duplicate key update campaignid='".$this->campaignid."',actual_budget='".$this->actual_budget."',multiplier='".$this->multiplier."',usercode='".$this->ucode."',insert_date=now()";
		$res_ins_combo = parent::execQuery($inst_sql,$this->conn_temp);
		if($res_ins_combo){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			echo json_encode($result_msg_arr);exit;
		}else {
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error";
			echo json_encode($result_msg_arr);exit;
		}
	}
	
	function dependenthandling(){
		$campaign = array();
		$check_sql ="select campaignid,budget,version from tbl_companymaster_finance_temp where parentid='".$this->parentid."' AND recalculate_flag=1 and budget!=balance and version='".$this->version."'";
		$res_check = parent::execQuery($check_sql,$this->conn_finance_temp);
		if($res_check && mysql_num_rows($res_check)>0){
			while($main_res_row=mysql_fetch_assoc($res_check)){
				$campaign[$main_res_row['campaignid']]['campaignid'] = $main_res_row['campaignid'];
				$campaign[$main_res_row['campaignid']]['budget'] = $main_res_row['budget'];
			}
			$check_min ="SELECT omni_monthly_fees FROM tbl_omni_pricing WHERE omni_type='5' AND city='".$this->data_city_cm."'";
			$res_min = parent::execQuery($check_min,$this->dbConIdc);
			if($res_min && mysql_num_rows($res_min)>0){
				$camp_res_row=mysql_fetch_assoc($res_min);
			}
			$pri_cam ='';
			if(isset($campaign['1']) && isset($campaign['2']) && isset($campaign['10'])){
				$pack_val = $campaign['1']['budget']+$campaign['2']['budget']+$campaign['10']['budget'];
				$primary_camp = '10';
			}else if(isset($campaign['2']) && isset($campaign['10'])){
				$pack_val = $campaign['2']['budget']+$campaign['10']['budget'];
				$primary_camp = '10';
			}else if(isset($campaign['1']) && isset($campaign['10'])){ 
				$pack_val = $campaign['1']['budget']+$campaign['10']['budget'];
				$primary_camp = '10';
			}else if(isset($campaign['1']) && isset($campaign['2'])){
				$pack_val = $campaign['1']['budget']+$campaign['2']['budget'];
			}else if(isset($campaign['2'])){
				$pack_val = $campaign['2']['budget'];
				$primary_camp = '2';
			}else if(isset($campaign['10'])){
				$pack_val = $campaign['10']['budget'];
			}else if(isset($campaign['1'])){
				$pack_val = $campaign['1']['budget'];
				$primary_camp = '1';
			}
			if($pack_val >=  $camp_res_row['omni_monthly_fees']){
				$sel_camp= array_keys($campaign);
				$fetch_dep ="SELECT * FROM dependant_campaign_details  WHERE parentid='".$this->parentid."'";
				$res_min = parent::execQuery($fetch_dep,$this->conn_finance);
				if($res_min && mysql_num_rows($res_min)>0){
					while($camp_res_row=mysql_fetch_assoc($res_min)){
						$check_active ="select parentid from tbl_companymaster_finance where parentid='".$this->parentid."' and campaignid='".$camp_res_row['dep_campaignid']."' and (balance >0 or manual_override = 1)";
						$res_check_active = parent::execQuery($check_active,$this->conn_finance);	
						if($res_check_active && mysql_num_rows($res_check_active)>0 && !in_array($camp_res_row['dep_campaignid'],$sel_camp)){
							if($primary_camp == 1){
								$combo_name ='Combo 2';
							}else if($primary_camp == 2){
								$combo_name ='pdg festive combo';
							}else if($primary_camp == 10){
								$combo_name ='National Listing Festive Combo Offer';
							}else{
								$combo_name =$camp_res_row['combo_type'];
								$primary_camp = $camp_res_row['pri_campaignid'];
							}
							
							$insert_sql ="insert into dependant_campaign_details_temp(parentid,combo_type,pri_campaignid,version,dep_campaignid,dep_budget,dep_duration,entry_date) values ('".$camp_res_row['parentid']."','".$combo_name."','".$primary_camp."','".$this->version."','".$camp_res_row['dep_campaignid']."','".$camp_res_row['dep_budget']."','".$camp_res_row['dep_duration']."',now())";
							$insret_res = parent::execQuery($insert_sql,$this->conn_temp);
							if($insret_res){
								$insert_log ="insert into dependant_renewval_log values('".$camp_res_row['parentid']."','".$this->version."',now(),'".$this->module."','".$camp_res_row['dep_campaignid']."')";
								$res_insert_log = parent::execQuery($insert_log,$this->conn_finance);	
							}
						}
					}
				}
			}
		
		}
	}

	function updateFlexiBudget(){ 
		$update_sql ="update tbl_bidding_details_summary set pincodebudgetjson='".$this->params['price_arr']."',duration='".$this->params['flexi_duration']."' where parentid='".$this->params['parentid']."' and version='".$this->params['version']."'";
		$update_res = parent::execQuery($update_sql,$this->db_budgeting);
		if($update_res){
			$result_msg_arr['error_code'] = 0;
			$result_msg_arr['error_msg'] = "updated successfully";
		}else{
			$result_msg_arr['error_code'] = 1;
			$result_msg_arr['error_msg'] = "updated failed";
}
		return $result_msg_arr;
	}
	
	Function GetExistingpackbudget(){
		
		$sql ="SELECT reseller_package_details FROM tbl_business_uploadrates WHERE city='".$this->data_city."'";
		$update_res = parent::execQuery($sql,$this->dbConDjds);
		if($update_res && mysql_num_rows($update_res) > 0){
			$rowdata = mysql_fetch_assoc($update_res);
			$pck_json = json_decode($rowdata['reseller_package_details'],1);
			if(isset($pck_json[2120]['package_value']))
				$vfl_val = $pck_json[2120]['package_value'] * 12;	
			else 
				$vfl_val ='';
		}else{
			$vfl_val ='';
		}
		$employee_allocid ='';
		$this->getExpired(); 
		if($this->pdg_1yr_val != '' && intval($this->pdg_1yr_val) > 0){
			/*if(strtolower($this->params['payment_type']) == 'ecs'){
				$resarr['budget'] = round($this->pdg_1yr_val*4);
			}else {
				$resarr['budget'] = round($this->pdg_1yr_val*4);
			}*/
			if(strtolower($this->data_city) == 'mumbai'){
				$resarr['budget'] = round($this->pdg_1yr_val*2);
			}else{
				$resarr['budget'] = round($this->pdg_1yr_val*4);
			}
			
			if(strtolower($this->module) == 'me') {
				$parentsql ="SELECT contractCode,compname,team_type,parentcode FROM tblContractAllocation WHERE allocationType IN('25','99') AND contractcode='".$this->parentid."' ORDER BY allocationtime DESC LIMIT 1";
				$parent_res   = parent::execQuery($parentsql, $this->conn_temp);
				if($parent_res && mysql_num_rows($parent_res)>0)
				{
					$parentdata = mysql_fetch_assoc($parent_res);
					$employee_arr =  json_decode($this->getEmployeeInfo($parentdata['parentcode']),1);
				}
			}else{	
				$employee_arr =  json_decode($this->getEmployeeInfo($this->params['usercode']),1);
			}
			
			if($employee_arr['errorcode'] == 0 && isset($employee_arr['data']) && isset($employee_arr['data'][0]) && isset($employee_arr['data'][0]['team_type'])){
				$employee_allocid = strtolower($employee_arr['data'][0]['team_type']);
			}
			
			if(strtolower($employee_allocid) == 'rd') {
				$resarr['budget'] =  round($vfl_val + ($vfl_val * 0.25));
			}
			
			$resarr['display_budget'] = round($vfl_val);
			$resarr['error_code'] = 0;
			$resarr['error_msg'] = "data found";
			$resarr['existing_contract'] = 1;
		}else if($this->expiredePackflg == 1 && strtolower($this->data_city) == 'kolkata'){
			$resarr['budget'] = 15000;
			$resarr['display_budget'] = 15000;
			$resarr['existing_contract'] = 2;
			$resarr['error_code'] = 0;
			$resarr['error_msg'] = "data found";
		}else if($this->expiredePackflg == 1 && strtolower($this->data_city) == 'mumbai'){
			$resarr['budget'] = 10000;
			$resarr['display_budget'] = 10000;
			$resarr['existing_contract'] = 2;
			$resarr['error_code'] = 0;
			$resarr['error_msg'] = "data found";
		}else if($vfl_val !='' && $vfl_val != 0){
			
			/*if(strtolower($this->data_city) == 'mumbai'){
				if(strtolower($this->module) == 'me') {
					$parentsql ="SELECT contractCode,compname,team_type,parentcode FROM tblContractAllocation WHERE allocationType IN('25','99') AND contractcode='".$this->parentid."' ORDER BY allocationtime DESC LIMIT 1";
					$parent_res   = parent::execQuery($parentsql, $this->conn_temp);
					if($parent_res && mysql_num_rows($parent_res)>0)
					{
						$parentdata = mysql_fetch_assoc($parent_res);
						$employee_arr =  json_decode($this->getEmployeeInfo($parentdata['parentcode']),1);
					}
				}else{	
					$employee_arr =  json_decode($this->getEmployeeInfo($this->params['usercode']),1);
				}
				
				if($employee_arr['errorcode'] == 0 && isset($employee_arr['data']) && isset($employee_arr['data'][0]) && isset($employee_arr['data'][0]['team_type'])){
					$emp_allocid = strtoupper($employee_arr['data'][0]['team_type']);
				}
				
				if($emp_allocid =='HD'){
					$date_of_joining = $employee_arr['data'][0]['date_of_joining'];
					if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_of_joining)){
						$cur_date 	= time();
						$join_date 	= strtotime($date_of_joining);
						$dt_diff 	= $cur_date - $join_date;
						$dt_diff = round($dt_diff / (60 * 60 * 24));
						if($dt_diff <=180){
							$vfl_specail_team = 1;
						}
					}
				}else if($emp_allocid =='S'){ // C Team confirmed by ITU
					$vfl_specail_team = 1;
				}
			}*/
			
			
			if(strtolower($this->data_city) == 'mumbai1' || strtolower($this->data_city) == 'delhi1'){
				
				$resarr['budget'] = round($vfl_val/2);
				$resarr['display_budget'] = round($vfl_val);
			}
			else{
				$resarr['budget'] = round($vfl_val);
				$resarr['display_budget'] = round($vfl_val);
			}
			
			
			$resarr['existing_contract'] = 2;
			$resarr['error_code'] = 0;
			$resarr['error_msg'] = "data found";
		}else{
			$resarr['error_code'] = 1;
			$resarr['error_msg'] = "data not found";
			$resarr['existing_contract'] = 0;
		}
		return $resarr;
	}
	
}




?>
