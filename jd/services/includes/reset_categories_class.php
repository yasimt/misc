<?php
class reset_categories_class extends DB
{
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	
	function __construct($params)
	{
		$parentid 	= trim($params['parentid']);
		$module 	= trim($params['module']);
		$data_city 	= trim($params['data_city']);
		$ucode		= trim($params['ucode']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message));
            die();
        }
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
		$valid_module_arr = array("DE","CS","TME","ME");
		if(!in_array(strtoupper($module),$valid_module_arr))
		{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}
		$this->parentid  	= $parentid;
		$this->data_city 	= $data_city;
		$this->module  	  	= strtoupper($module);
		$this->ucode  	  	= $ucode;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->setServers();
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
			
		if(($this->module =='DE') || ($this->module =='CS'))
		{
			$this->conn_temp	 	= $this->conn_local;
		}
		elseif($this->module =='TME')
		{
			$this->conn_temp		= $this->conn_tme;
			if((in_array($this->ucode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}
		elseif($this->module =='ME')
		{
			$this->conn_temp		= $this->conn_idc;
			if((in_array($this->ucode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
		}
		else
		{
			$message = "Invalid Module.";
			echo json_encode($this->send_die_message($message));
			die();
		}	
	}
	function reset_categories()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			
			$bustemp_tbl 		= "tbl_business_temp_data";
			$bustemp_upt = array();
			$bustemp_upt['catIds'] 					= '';
			$bustemp_upt['mainattr'] 				= '';
			$bustemp_upt['facility'] 				= '';
			$bustemp_upt['categories'] 				= '';
			$bustemp_upt['nationalcatIds'] 			= '';
			$bustemp_upt['catSelected'] 			= '';
			$bustemp_upt['htmldump'] 				= '';
			$bustemp_upt['slogan'] 					= '';
			$bustemp_upt['category_flow_info'] 		= '';
			$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
			
			$mongo_inputs['table_data'] 			= $mongo_data;
			$resResetPaidCategories = $this->mongo_obj->updateData($mongo_inputs);
			
		}
		else
		{
			if(($this->module =='DE') || ($this->module =='CS')) {
				$sqlResetPaidCategories = "UPDATE tbl_business_temp_data SET catIds = '', mainattr='', facility='', categories = '', nationalcatIds = '', catSelected = '', htmldump='', slogan='' WHERE contractid ='".$this->parentid."'";
			}
			else{
				$sqlResetPaidCategories = "UPDATE tbl_business_temp_data SET catIds = '', mainattr='', facility='',  categories = '', nationalcatIds = '', catSelected = '', htmldump='', slogan='',category_flow_info='' WHERE contractid ='".$this->parentid."'";
			}
			$sqlResetPaidCategories = $sqlResetPaidCategories."/* TMEMONGOQRY */";
			$resResetPaidCategories 	= parent::execQuery($sqlResetPaidCategories, $this->conn_temp);
		}
		
		$sqlUpdtNationalListing ="UPDATE tbl_national_listing_temp SET Category_nationalid = '' , TotalCategoryWeight = '' WHERE parentid='".$this->parentid."'";
		$resUpdtNationalListing   = parent::execQuery($sqlUpdtNationalListing, $this->conn_temp); 
		
		//when categories, delete attributes from temp table
		$delAttr = "DELETE FROM tbl_companymaster_attributes_temp WHERE parentid='".$this->parentid."'";
		$resAttr = parent::execQuery($delAttr, $this->conn_temp); 
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			
			$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
			$extrdet_upt = array();
			$extrdet_upt['catidlineage_nonpaid'] 			= '';
			$extrdet_upt['attribute_search'] 				= '';
			$extrdet_upt['attributes_edit'] 				= '';
			$extrdet_upt['attributes'] 						= '';
			$extrdet_upt['catidlineage_nonpaid'] 			= '';
			$extrdet_upt['national_catidlineage_nonpaid'] 	= '';
			$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
			$mongo_inputs['table_data'] 			= $mongo_data;
			$resResetNonpaidCategories = $this->mongo_obj->updateData($mongo_inputs);
		}
		else
		{
			$sqlResetNonpaidCategories = "UPDATE tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '',attribute_search='',attributes_edit='', attributes='', national_catidlineage_nonpaid = '' WHERE parentid ='".$this->parentid."'";
			
			if(($this->module =='DE') || ($this->module =='CS'))
			{
				$resResetNonpaidCategories 	= parent::execQuery($sqlResetNonpaidCategories, $this->conn_iro);
				
				//delete all the categories from tbl_movie_timings_shadow table when all the categories are deleted.
				$delCatids = "DELETE FROM db_iro.tbl_movie_timings_shadow WHERE parentid='".$this->parentid."' ";
				$resCatids = parent::execQuery($delCatids, $this->conn_iro);
			}
			else
			{
				$sqlResetNonpaidCategories = $sqlResetNonpaidCategories."/* TMEMONGOQRY */";
				$resResetNonpaidCategories 	= parent::execQuery($sqlResetNonpaidCategories, $this->conn_temp);
			}
		}
		
		
		if($resResetPaidCategories && $resResetNonpaidCategories)
		{
			$this->updateTempResetFlag();
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return $result_msg_arr;
		}
		else
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error in updating temp table";
			return $result_msg_arr;
		}
	}
	public function updateTempResetFlag()
	{
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_data = array();
			
			$intermd_tbl 		= "tbl_temp_intermediate";
			$intermd_upt 	= array();
			
			$intermd_upt = array();
			$intermd_upt['cat_reset_flag'] 	= 1;
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
			$mongo_inputs['table_data'] 			= $mongo_data;
			$resUpdateTempResetFlag = $this->mongo_obj->updateData($mongo_inputs);
		}
		else
		{
			$sqlUpdateTempResetFlag = "UPDATE tbl_temp_intermediate SET cat_reset_flag = 1 WHERE parentid ='".$this->parentid."'";
			$sqlUpdateTempResetFlag = $sqlUpdateTempResetFlag."/* TMEMONGOQRY */";
			$resUpdateTempResetFlag 	= parent::execQuery($sqlUpdateTempResetFlag, $this->conn_temp);
		}
	}
	private function send_die_message($msg)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}



?>
