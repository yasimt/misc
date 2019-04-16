<?php
class jdrrPlusCampaignClass extends DB
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
		
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 

		
			if(trim($this->params['parentid']) == "")
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Parentid Missing";
					echo json_encode($result_msg_arr);exit;
			}
			else
				$this->parentid  = $this->params['parentid']; 

			if(trim($this->params['version']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "version Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->version  = $this->params['version']; 

		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 

		
		
		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 

		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->data_city  = $this->params['data_city']; 
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
		/*if(trim($this->params['campaignidselected']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Campaign Selected Is Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->campaignidselected  = $this->params['campaignidselected']; */
		

		$status=$this->setServers();
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}

		$this->omni_duration =1520;
		
		if($this->action=='2'){

			if(trim($this->params['user_price']) != "")
			{
				$this->user_price  = $this->params['user_price']; 
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Price Missing";
				return $result_msg_arr;
			}
			if(trim($this->params['user_price_monthly']) != "")
			{
				$this->user_price_monthly  = $this->params['user_price_monthly']; 
			}
			else
				$this->user_price_monthly  = 0;

		}
		
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->data_city_main	= $data_city;
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];

			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			break;
			case 'jda':
			//$this->conn_temp = 
			break;
			default:
			return -1;
			break;
		}

	}
	function customJdrrPlus(){
		 
		if($this->module=='me' || $this->module=='ME'){
			if($this->user_price >0 || $this->user_price_monthly>0){
				if($this->user_price_monthly==0){
					$this->user_price_monthly=ceil($this->user_price/12);
				}
				if($this->user_price==0){
					$this->user_price=($this->user_price_monthly*12);
				}
			 $sql_ins_temp_omni = "INSERT INTO tbl_custom_omni_budget set
				 					parentid='".$this->parentid."',
				 					campaignid='225',
				 					setupfees  	= '".$this->user_price."',
				 					fees  	= '".$this->user_price_monthly."'
				 					ON DUPLICATE KEY UPDATE
				 					campaignid='225',
				 					setupfees  	= '".$this->user_price."',
				 					fees  	= '".$this->user_price_monthly."'";
			$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
			}
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
	}

	function deleteJdrrPlusCustom(){

		 if($this->module=='me' || $this->module=='ME'){
		 $querydel="delete from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225'";
		$ressql = parent::execQuery($querydel, $this->conn_temp);
		$result_msg_arr['error']['code'] = 0;
		$result_msg_arr['error']['msg'] = "Success";
		return $result_msg_arr;
		}
	}
	function checkJdrrPlusCampaignEligibility(){
		
		$jdrramt=11991; 
		$combo_duration ='365';
		
		if(strtolower($this->module) == 'tme'){
			$sqlgetjdrrplusprice="select * FROM d_jds.pricing_citywise  WHERE city='".$this->data_city."'" ;
			$res_price = parent::execQuery($sqlgetjdrrplusprice, $this->conn_temp);
			$customarr=array();
			if($res_price && mysql_num_rows($res_price)>0)
	 		{
	 			$rowprice	=	mysql_fetch_assoc($res_price);
	 			$jdrrprice	=	json_decode($rowprice['PriceList'],true);
				$jdrramt	=	$jdrrprice['Normal']['225']['price_upfront']- 8;  
	 		}
		}else{
		$sqlgetjdrrplusprice="select *,jdrrplus_bnr_upfront+jdrrplus_upfront as jdrr_price from  d_jds.tbl_business_uploadrates  WHERE city='".$this->data_city."'" ;
		$res_price = parent::execQuery($sqlgetjdrrplusprice, $this->dbConIro);
		$customarr=array();
		if($res_price && mysql_num_rows($res_price)>0)
 		{
 			while($rowprice=mysql_fetch_assoc($res_price)){
 				$jdrramt=$rowprice['jdrr_price']-$rowprice['jdrrplus_bnr_upfront'];  

 			}
 		}
		}
		

		 $sqlget="select * from tbl_custom_budget_campaign_wise where parentid='".$this->parentid."' and version='".$this->version."' and campaignid in ('22','5')";
		$res = parent::execQuery($sqlget, $this->conn_temp);
		$customarr=array();
		if($res && mysql_num_rows($res)>0)
 		{
 			return; 
 		}
 		
		if($this->type==5 || $this->type=='5')
			return;
		if($this->combo==0){
			$sqlcombo="select * from tbl_payment_type where parentid='".$this->parentid."' and version='".$this->version."'";
			$sqlcombores = parent::execQuery($sqlcombo, $this->conn_finance);
			if($sqlcombores && mysql_num_rows($sqlcombores)>0)
	 		{
	 			while($sqlcomborow=mysql_fetch_assoc($sqlcombores))
				{
					$combo_name=$sqlcomborow['payment_type'];
				}
			}
			
			if (strpos($combo_name, 'combo2') !== false) {
			    $this->combo=2;
			}

		}
		$campaignid=0;
		$campaignidupdt=0;
		if($this->campaignidselected=='5'){
			$campaignid=22;
			
		}
		if($this->campaignidselected=='22'){
			$campaignid=5;
		}
		$cust_bugdt=0;

		
		
	/*	$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		if(strtolower($data_city)=='remote') 
			$jdrramt=11991; 
	*/

		if($this->module=='me' || $this->module=='ME'){
		$checkjdrrplussql="select * from tbl_custom_omni_budget where parentid='".$this->parentid."' and campaignid='225'";
		$checkjdrrplusres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
			if($checkjdrrplusres && mysql_num_rows($checkjdrrplusres)>0)
	 		{
	 			while($checkjdrrplusrow=mysql_fetch_assoc($checkjdrrplusres))
				{
					$price_setup=$checkjdrrplusrow['setupfees'];
					$price_setup_monthly=$checkjdrrplusrow['fees'];
				}
				$jdrramt=$price_setup;
				$cust_bugdt=1;
			}
		}
		/*if($campaignid!=0){*/
			$ecs_flag=0;
			
			$checkfinance="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1  and campaignid in (5,22)";
			$res = parent::execQuery($checkfinance, $this->conn_finance_temp);
			$count=0;
			$getecsinfo="select * from campaigns_selected_status  where parentid='".$this->parentid."' and campaignid in (5,22)";
			$ecsinfores = parent::execQuery($getecsinfo, $this->conn_temp);
			$count=mysql_num_rows($ecsinfores);
			if($ecsinfores && mysql_num_rows($ecsinfores)>0){
				while($rowecsinfores=mysql_fetch_assoc($ecsinfores)){
					if($rowecsinfores['ecs_flag']=='ecs')
						$ecs_flag=1;
					}
				
			}
			if($ecs_flag==0 && $cust_bugdt == 1){
				$jdrramt=$jdrramt-8;
			}
			/*if($ecs_flag==1 && $cust_bugdt == 0){
				$jdrramt=((998*12)-8);
			}
*/
			if($ecs_flag==1 && $cust_bugdt == 1){
				$jdrramt=(($price_setup_monthly*12)-8);
			}
			if($this->combo=='2' || $this->combo==2 ){
					$sqlcustom="select * from tbl_custom_omni_combo_budget where parentid='".$this->parentid."' and version='".$this->version."'";
					$rescustom = parent::execQuery($sqlcustom, $this->conn_temp);

					if($rescustom && mysql_num_rows($rescustom) >0)
			 		{
			 			while($rescustomrow=mysql_fetch_assoc($rescustom)){
			 				$fees=$rescustomrow['fees'];
			 			}
			 			$ratioamt=(1/7);
			 			$jdrramt=ceil($fees*$ratioamt*12)-8;
			 		}
			 		else{
		 				$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote'); 

		 				$checkjdrrplussql="SELECT * FROM tbl_combo_pricing WHERE city='".$data_city."' and campaignid=22 and combo='combo2'";
		 				$checkjdrrplusres = parent::execQuery($checkjdrrplussql, $this->conn_temp);
		 					if($checkjdrrplusres && mysql_num_rows($checkjdrrplusres)>0)
		 			 		{
		 			 			while($checkjdrrplusrow=mysql_fetch_assoc($checkjdrrplusres))
		 						{
		 							  $jdrramt = $checkjdrrplusrow['ecs_upfront'];
		 						}
		 						
		 					}
			 		}
				
			}
			
			
			$jdrr_web_dis_flag = 0;
			$sql_paytype = "SELECT payment_type  FROM tbl_payment_type WHERE parentid='".$this->parentid."' and version='".$this->version."'";
			$res_paytype = parent::execQuery($sql_paytype,  $this->conn_finance);
			if ($res_paytype && mysql_num_rows($res_paytype) > 0) 
			{
				$row_paytype = mysql_fetch_assoc($res_paytype);
				
				if(strstr($row_paytype['payment_type'],"jdrr_web_dis") != '')
				{
					//print_r($row_paytype);
					$jdrr_web_dis_flag = 1;
					//echo 'ddss=='.$jdrr_web_dis_budget =  $row_jdrr_existing_budget['budget'];
				}
			}
			
			$budget_data = array();
			if($res && mysql_num_rows($res)==2 && $count==2){
				
				while($row_jdrr_existing_budget = mysql_fetch_assoc($res))
				{
					
					$budget_data[$row_jdrr_existing_budget['campaignid']] = $row_jdrr_existing_budget;
					if($row_jdrr_existing_budget['campaignid'] == '22')
					$existing_jdrr_budget = $row_jdrr_existing_budget['original_actual_budget'];
					//echo $existing_jdrr_budget;
				}
				
				if($this->type==16 || $this->type=='16' || $this->type==17 || $this->type=='17' || $this->type==18 || $this->type=='18' || $this->type==19 || $this->type=='19'){
					$jdrrcombosql="SELECT omni_fees_plus_jdrr,omni_fees_plus_banner,tenure_upfront,tenure_ecs FROM tbl_omni_pricing WHERE city='".$this->data_city_main."' and omni_type='".$this->type."'";
					$jdrrcombores = parent::execQuery($jdrrcombosql, $this->conn_temp);
					if($jdrrcombores && mysql_num_rows($jdrrcombores)>0)
					{
						while($jdrrcomborow=mysql_fetch_assoc($jdrrcombores))
						{
							$existing_jdrr_budget=$jdrrcomborow['omni_fees_plus_jdrr'];
							$jdrr_web_dis_flag = 1;
							$combo_duration = $jdrrcomborow['tenure_upfront'];
						}
					}
				}
			
				$checkcatban="select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and recalculate_flag=1 and campaignid='13' and budget>0";
				$rescat = parent::execQuery($checkcatban, $this->conn_finance_temp);
				$row_budget_banner = mysql_fetch_assoc($rescat);
				
				if($rescat && mysql_num_rows($rescat)>0)
				{
					$updatefinance="update tbl_companymaster_finance_temp set duration ='".$budget_data['22']['duration']."', budget='4',original_budget='4' where parentid='".$this->parentid."' and campaignid=5";
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
					$updatefinance="update tbl_companymaster_finance_temp set duration ='".$budget_data['22']['duration']."',budget='4',original_budget='4' where parentid='".$this->parentid."' and campaignid=13";
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
					$updatefinance="update catspon_banner_rotation_temp set budget='4',no_of_rotation=1 where parentid='".$this->parentid."'";
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
					$updatefinance="update tbl_companymaster_finance_temp set budget='".max($jdrramt,($existing_jdrr_budget))."',original_budget='".max($jdrramt,$existing_jdrr_budget)."' where parentid='".$this->parentid."' and campaignid=22";
					
				
				
					if($jdrr_web_dis_flag == 1) 
					{
					//$existing_jdrr_budget = $existing_jdrr_budget - 8;
					$updatefinance="update tbl_companymaster_finance_temp set budget='".$existing_jdrr_budget."',original_budget='".$existing_jdrr_budget."' where parentid='".$this->parentid."' and campaignid=22";
					$jdrramt = $existing_jdrr_budget;
					}
					
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
				}
				else{
					$updatefinance="update tbl_companymaster_finance_temp set duration ='".$budget_data['22']['duration']."', budget='8',original_budget='8' where parentid='".$this->parentid."' and campaignid=5";
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
					$updatefinance="update catspon_banner_rotation_temp set budget='8',no_of_rotation=1 where parentid='".$this->parentid."'";
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
					$updatefinance="update tbl_companymaster_finance_temp set budget='".max($jdrramt,($existing_jdrr_budget))."',original_budget='".max($jdrramt,$existing_jdrr_budget)."' where parentid='".$this->parentid."' and campaignid=22";
					if ($jdrr_web_dis_flag == 1) 
					{
					//$existing_jdrr_budget = $existing_jdrr_budget - 8;	
					$updatefinance="update tbl_companymaster_finance_temp set budget='".$existing_jdrr_budget."',original_budget='".$existing_jdrr_budget."' where parentid='".$this->parentid."' and campaignid=22";
					$jdrramt = $existing_jdrr_budget;
					}
					
					$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
				}
				if($resupt){
					$updatejdrrcombo="update campaigns_selected_status set jdrr_banner_combo=1 where parentid='".$this->parentid."'";
					$resupt = parent::execQuery($updatejdrrcombo, $this->conn_temp);
					

					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['combo']['type'] = $this->combo;
					$result_msg_arr['combo']['price'] = $jdrramt;
					$result_msg_arr['error']['msg'] = "Success";
					echo json_encode($result_msg_arr);exit;
				}
				else{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['combo']['type'] = $this->combo;
					$result_msg_arr['combo']['price'] = $jdrramt;
					$result_msg_arr['error']['msg'] = "Failure";
					echo json_encode($result_msg_arr);exit;
				}

			}
			else{
				
				$updatefinance="update tbl_companymaster_finance_temp set budget=original_actual_budget where parentid='".$this->parentid."' and campaignid in (5,13,22)";
				$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
				$updatejdrrcombo="update campaigns_selected_status set jdrr_banner_combo=0 where parentid='".$this->parentid."' ";
				$resupt = parent::execQuery($updatejdrrcombo, $this->conn_temp);
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				echo json_encode($result_msg_arr);exit;
			}

		/*}
		else{
			$updatefinance="update tbl_companymaster_finance_temp set budget=original_actual_budget where parentid='".$this->parentid."' and campaignid=(5,13,22)";
			$resupt = parent::execQuery($updatefinance, $this->conn_finance_temp);
			$updatejdrrcombo="update tbl_finance_omni_flow_display set jdrr_banner_combo=0 where parentid='".$this->parentid."' ";
			$resupt = parent::execQuery($updatejdrrcombo, $this->conn_temp);
		}*/
	}
}
?>
