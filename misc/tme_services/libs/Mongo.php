<?php

	##############################################
	##	Class For Calling Mongo API - Imteyaz	##
	##############################################

class MongoClass extends Model
{
	var  $conn_idc  = null;
	var $mongo_tables_arr 	= array("tbl_companymaster_generalinfo_shadow","tbl_companymaster_extradetails_shadow","tbl_business_temp_data","tbl_temp_intermediate");
	var $dataservers 		= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	function __construct($dbarr = array()){
		parent::__construct();
		$this->invalid_data = 0;
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])) {
			define("MONGOAPI", "http://172.29.0.186/api/"); #Development
			$this->ignore_city_arr = array('mumbai');
		}
		else {
			define("MONGOAPI", "http://192.168.20.111/api/"); #Live
			$this->ignore_city_arr = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
		}
	}
	function updateData($params){
		//print_r($params);
		$this->validateParams($params,'update');
		if($this->invalid_data !=1){
			$curl_url = MONGOAPI."insertdata_test";
			
			$inputs_arr = array();
			$inputs_arr['parentid'] 	= trim($params['parentid']);
			$inputs_arr['data_city'] 	= trim($params['data_city']);
			$inputs_arr['module'] 		= trim($params['module']);
			
			if((count($params)>0) && (count($params['table_data'])>0)){
				$inputs_arr['table_data'] 	= http_build_query($params['table_data']);
				$curl_response_str 			= $this->sendRequest($curl_url,$inputs_arr);
				if($curl_response_str){
					$curl_response_arr		= json_decode($curl_response_str,true);
					if($curl_response_arr['error'] ==  "0"){
						return $success_flag = 1;
					}
				}
				if(!$success_flag){
					$message = "Data Insertion API Fail. Params Passed : ".json_encode($params)." Response : ".$curl_response_str;
					echo $message;
				}
				
			}else{
				$message = "Invalid Data Passed.";
				echo json_encode($this->sendResponseMsg($message));
				die();
			}
			
		}
	}
	
	function getDataMatch($params){
		//print_r($params);
		$data_city 		= trim($params['data_city']);
		$table			= trim($params['table']);
		$module			= trim($params['module']);
		
		if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		if(trim($table)==''){
			$this->invalid_data = 1;
			$message = "Table Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		if($this->invalid_data !=1){
			$curl_url = MONGOAPI."getdatamatch";
			$inputs_arr = array();
			
			$curl_response_str 			= $this->sendRequest($curl_url,$params);
			if($curl_response_str){
				$curl_response_arr		= json_decode($curl_response_str,true);
				if($curl_response_arr['error'] ==  "0"){
					$success_flag = 1;
					return $curl_response_arr['data'];
				}
				else
					return json_decode($curl_response_str,true);
			}
			if(!$success_flag){
				$dataArr = array();
				return $dataArr;
			}
		}
	}
	
	function getData($params){
		//print_r($params);
		$this->validateParams($params);
		if($this->invalid_data !=1){
			$curl_url = MONGOAPI."getdata";
			$inputs_arr = array();
			$inputs_arr['parentid'] 	= trim($params['parentid']);
			$inputs_arr['data_city'] 	= trim($params['data_city']);
			$inputs_arr['table'] 		= trim($params['table']);
			$inputs_arr['module'] 		= trim($params['module']);
			if(isset($params['fields'])){
				$inputs_arr['fields'] 		= trim($params['fields']);
			}
			if(count($params['aliaskey']) >0 ){
				$inputs_arr['aliaskey'] 	= json_encode($params['aliaskey']);
			}
			$curl_response_str 			= $this->sendRequest($curl_url,$inputs_arr);
			if($curl_response_str){
				$curl_response_arr		= json_decode($curl_response_str,true);
				if($curl_response_arr['error'] == "0"){
					$success_flag = 1;
					return $curl_response_arr['data'];
				}
				else
				{
					if(!(in_array(strtolower(trim($params['data_city'])),$this->ignore_city_arr)))
					{
						$res = $this->setbulkdata($inputs_arr['parentid'],$inputs_arr['data_city'],$inputs_arr['table'],$inputs_arr['module']);
						if($res){
							$success_flag = 1;
							return $res;
						}
					}
				}
			}
			if(!$success_flag){
				$dataArr = array();
				return $dataArr;
			}
		}
	}
	
	function setbulkdata($parentid,$data_city,$table,$module)
	{
		$conn_temp = new DB($this->db['db_tme']);
		
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $parentid;
		$mongo_inputs['data_city'] 	= $data_city;
		$mongo_inputs['module'] 	= $module;
		
		if($table == 'tbl_business_temp_data'){
			$wherecondn = " WHERE contractid = '".$parentid."' ";
		}else{
			$wherecondn = " WHERE parentid = '".$parentid."' ";
		}
		$sqlFetchData = "SELECT * FROM ".$table." ".$wherecondn;
		$sqlFetchData = $sqlFetchData."/* TMEMONGOQRY */";
		$resFetchData 	= $conn_temp->query($sqlFetchData);
		if($resFetchData && $conn_temp->numRows($resFetchData)){
			$row_data	=	$conn_temp->fetchData($resFetchData);
			$mongo_data[$table]['updatedata'] = $row_data;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		//print_r($mongo_inputs);
		if((count($mongo_inputs)>0) && (count($mongo_inputs['table_data'])>0)){
			$this->updateData($mongo_inputs);
		}
		$result = isset($mongo_inputs['table_data'][$table]) ? $mongo_inputs['table_data'][$table]['updatedata'] : 0;
		return $result;
	}
		
	function joinTables($params){
		//print_r($params);
		$data_city 		= trim($params['data_city']);
		$module			= trim($params['module']);
		if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank. Function - joinTables. ";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		$curl_url = MONGOAPI."getjoin";
		
		$curl_response_str 			= $this->sendRequest($curl_url,$params);
		if($curl_response_str){
			$curl_response_arr	= json_decode($curl_response_str,true);
			if($curl_response_arr['error'] ==  "0"){
				$success_flag = 1;
				return $curl_response_arr['data'];
			}
			else
			{
				if(!(in_array(strtolower(trim($params['data_city'])),$this->ignore_city_arr)))
				{
					$res = $this->setjoindata($params);
					if($res){
						$success_flag = 1;
						return $res;
					}
				}
			}
		}
		if(!$success_flag){
			$dataArr = array();
			return $dataArr;
		}
	}
	
	function setjoindata($params)
	{
		if(isset($params['t1_mtch']))
		{
			$arr = json_decode($params['t1_mtch'],true);
		}
		$parentid 	= $arr['contractid'];
		$data_city 	= $params['data_city'];
		$module 	= $params['module'];
		
		$conn_temp = new DB($this->db['db_tme']);
		
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $parentid;
		$mongo_inputs['data_city'] 	= $data_city;
		$mongo_inputs['module'] 	= $module;
		
		$sqlFetchExtraDetails = "SELECT * FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$parentid."'";
		$sqlFetchExtraDetails = $sqlFetchExtraDetails."/* TMEMONGOQRY */";
		$resFetchExtraDetails 	= $conn_temp->query($sqlFetchExtraDetails);
		if($resFetchExtraDetails && $conn_temp->numRows($resFetchExtraDetails)){
			
			$row_ext_details	=	$conn_temp->fetchData($resFetchExtraDetails);
			$extrdet_tbl = 'tbl_companymaster_extradetails_shadow';
			$mongo_data[$extrdet_tbl]['updatedata'] = $row_ext_details;
			$mongo_inputs['table_data'] = $mongo_data;
		}
				
		$sql3 = "SELECT * FROM tbl_business_temp_data WHERE contractid = '".$parentid."'";
		$sql3 = $sql3."/* TMEMONGOQRY */";
		$res3 	= $conn_temp->query($sql3);
		if($res3 && $conn_temp->numRows($res3)){
			
			$row3	=	$conn_temp->fetchData($res3);
			$bustemp_tbl = 'tbl_business_temp_data';
			$mongo_data[$bustemp_tbl]['updatedata'] = $row3;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		//print_r(json_encode($mongo_inputs));
		if((count($mongo_inputs)>0) && (count($mongo_inputs['table_data'])>0)){
			$this->updateData($mongo_inputs);
		}
		
		$sql4 = "SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $parentid . "'";
		$sql4 = $sql4."/* TMEMONGOQRY */";
		$res4 = $conn_temp->query($sql4);
		if($res4 && $conn_temp->numRows($res4)){
			$row4 =	$conn_temp->fetchData($res4);
			return $row4;
		}
		else
			return 0;
	}
	
	function validateParams($params,$action=''){
		
		$parentid 		= $params['parentid'];
		$data_city 		= trim($params['data_city']);
		$table			= trim($params['table']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg(json_encode($message)));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.".json_encode($params);
			echo json_encode($this->sendResponseMsg(json_encode($message)));
			die();
		}
		if(trim($table)=='' && $action !='update'){
			$this->invalid_data = 1;
			$message = "Table Name is blank.";
			echo json_encode($this->sendResponseMsg(json_encode($message)));
			die();
		}
		
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg(json_encode($message)));
			die();
		}
		
		if((!in_array(strtolower($table),$this->mongo_tables_arr)) && ($action !='update')){
			$this->invalid_data = 1;
			$message = "Invalid Table.";
			echo json_encode($this->sendResponseMsg(json_encode($message)));
			die();
		}
	}
	
	function sendRequest($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function sendResponseMsg($msg){
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg." (Mongo Class)";
		return $die_msg_arr;
	}
}
?>
