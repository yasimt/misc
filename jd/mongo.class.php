<?php

	##############################################
	##	Class For Calling Mongo API - Imteyaz	##
	##############################################

class MongoClass extends DB
{
	var  $conn_idc  = null;
	
	var $mongo_tables_arr 	= array("tbl_companymaster_generalinfo_shadow","tbl_companymaster_extradetails_shadow","tbl_business_temp_data","tbl_temp_intermediate");
	#var $main_cities_arr  	= array("mumbai", "delhi", "kolkata", "bangalore", "chennai", "pune", "hyderabad", "ahmedabad");
	var $main_cities_arr  	= array();
	var $dataservers 		= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	
	function __construct(){
		
		$this->invalid_data = 0;
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoft.com/i", $_SERVER['HTTP_HOST'])) {
			define("MONGOAPI", "http://172.29.0.186/api/"); #Development
			define("MONGOCITY", json_encode(array("mumbai")));
			define("ALLUSER", 1);
			$this->dev_mode = 1;
			$this->ignore_city_arr = array('mumbai');
		}
		else {
			define("MONGOAPI", "http://192.168.20.111/api/"); #Live
			define("MONGOCITY", json_encode(array("remote","mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad")));
			define("ALLUSER", 1);
			$this->dev_mode = 0;
			$this->ignore_city_arr = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
		}
       		define("MONGOUSER", json_encode(array("10022019","10000760")));
       		define("TME_MONGOUSER", json_encode(array("10022019","100007601")));
       		define("TME_ALLUSER_MONGO", 1);
       		$this->setDBServers();
	}
	function setDBServers(){
		global $db;
		//$this->conn_idc = $db['remote']['idc']['master'];
	}
	function setData($params){ // Insert or Update
		
		$this->validateParams($params);
		
		if($this->invalid_data !=1){
			
			$curl_url = MONGOAPI."setdata";
			
			$inputs_arr = array();
			$inputs_arr['parentid'] 	= trim($params['parentid']);
			$inputs_arr['data_city'] 	= trim($params['data_city']);
			$inputs_arr['table'] 		= trim($params['table']);
			
			
			if((count($params)>0) && (count($params['data'])>0)){
				$inputs_arr['data'] = http_build_query($params['data']);
				echo $curl_response 		= $this->sendRequest($curl_url,$inputs_arr);
				
			}else{
				$message = "Invalid Data Passed.";
				echo json_encode($this->sendResponseMsg($message));
				die();
			}
		}
		
	}
	function updateData($params){
		$this->validateParams($params,'update');
		if($this->invalid_data !=1){
			$curl_url = MONGOAPI."insertdata";
			
			$inputs_arr = array();
			$inputs_arr['parentid'] 	= trim($params['parentid']);
			$inputs_arr['data_city'] 	= trim($params['data_city']);
			$inputs_arr['module'] 		= trim($params['module']);
			
			if((count($params)>0) && (count($params['table_data'])>0)){
				
				/**/
				if((isset($params['table_data']['tbl_companymaster_extradetails_shadow']['updatedata']['companyname']) && $params['table_data']['tbl_companymaster_extradetails_shadow']['updatedata']['companyname']==='') || (isset($params['table_data']['tbl_companymaster_generalinfo_shadow']['updatedata']['companyname']) && $params['table_data']['tbl_companymaster_generalinfo_shadow']['updatedata']['companyname']==='')){
					$post_data = array();
					$log_url = 'http://192.168.17.109/logs/logs.php';
					$post_data['ID']                = $params['parentid'];
					$post_data['PUBLISH']           = 'ME';
					$post_data['ROUTE']             = 'MONGO_COMPANYNAME';
					$post_data['CRITICAL_FLAG'] 	= 1;
					$post_data['MESSAGE']       	= 'company name is missing - jdbox';
					$post_data['DATA']['url']       = '';
					$post_data['DATA_JSON']['paramssubmited'] = $params;
					$post_data['DATA_JSON']['response'] = '1';
					$post_data = http_build_query($post_data);
					$log_res = $this->sendRequest($log_url,$post_data);
				}
				/**/
				
				$inputs_arr['table_data'] 	= http_build_query($params['table_data']);
				$curl_response_str 			= $this->sendRequest($curl_url,$inputs_arr);
				if($curl_response_str){
					$curl_response_arr		= json_decode($curl_response_str,true);
					if($curl_response_arr['error'] ==  "0"){
						return $success_flag = 1;
					}
				}
				if(!$success_flag){
					
					/**/ 
					if(strtolower(trim($params['module'])) == 'me' || strtolower(trim($params['module'])) == 'jda') 
					{
						$post_data = array();
						$log_url = 'http://192.168.17.109/logs/logs.php';
						$post_data['ID']                = $params['parentid'];
						$post_data['PUBLISH']           = 'ME';
						$post_data['ROUTE']             = 'MONGO_UPDATE_ERROR';
						$post_data['CRITICAL_FLAG'] 	= 1;
						$post_data['MESSAGE']       	= 'error while updating data in mongo tables - jdbox';
						$post_data['DATA']['url']       = '';
						$post_data['DATA_JSON']['paramssubmited'] = $params;
						$post_data['DATA_JSON']['response'] = $curl_response_str;
						$post_data = http_build_query($post_data);
						$log_res = $this->sendRequest($log_url,$post_data);
					}
					/**/
					
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
			}
			if(!$success_flag){
				$dataArr = array();
				return $dataArr;
			}
		}
	}
	
	function getData($params){
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
			$curl_response_arr = array();
			$curl_response_str 			= $this->sendRequest($curl_url,$inputs_arr);
			if($curl_response_str){
				$curl_response_arr		= json_decode($curl_response_str,true);
				if($curl_response_arr['error'] ==  "0"){
					$success_flag = 1;
					return $curl_response_arr['data'];
				}
				else
				{
					/**/ 
					if(strtolower(trim($params['module'])) == 'me' || strtolower(trim($params['module'])) == 'jda') 
					{
						$post_data = array();
						$log_url = 'http://192.168.17.109/logs/logs.php';
						$post_data['ID']                = $params['parentid'];
						$post_data['PUBLISH']           = 'ME';
						$post_data['ROUTE']             = 'MONGOERROR';
						$post_data['CRITICAL_FLAG'] 	= 1;
						$post_data['MESSAGE']       	= 'error while fetching data from mongo tables';
						$post_data['DATA']['url']       = '';
						$post_data['DATA_JSON']['paramssubmited'] = $inputs_arr;
						$post_data['DATA_JSON']['response'] = $curl_response_str;
						$post_data = http_build_query($post_data);
						//$log_res = $this->sendRequest($log_url,$post_data);
					} 
					/**/ 
					
					//~ if((strtolower($inputs_arr['module']) == 'tme') && !(in_array(strtolower(trim($params['data_city'])),$this->ignore_city_arr)))
					//~ {
						//~ $res = $this->setbulkdata($inputs_arr['parentid'],$inputs_arr['data_city'],$inputs_arr['table'],$inputs_arr['module']);
						//~ if($res){
							//~ $success_flag = 1;
							//~ return $res;
						//~ }
					//~ }
				}
			}else{
				/**/
				if(strtolower(trim($params['module'])) == 'me' || strtolower(trim($params['module'])) == 'jda')
				{
					$post_data = array();
					$log_url = 'http://192.168.17.109/logs/logs.php';
					$post_data['ID']                = $params['parentid'];
					$post_data['PUBLISH']           = 'ME';
					$post_data['ROUTE']             = 'MONGO_GET_ERROR';
					$post_data['CRITICAL_FLAG'] 	= 1;
					$post_data['MESSAGE']       	= 'error while fetching data from mongo tables--';
					$post_data['DATA']['url']       = '';
					$post_data['DATA_JSON']['paramssubmited'] = $params;
					$post_data['DATA_JSON']['response'] = $curl_response_str;
					$post_data = http_build_query($post_data);
					//$log_res = $this->sendRequest($log_url,$post_data);
				} 
				/**/
				
				$dataArr = array();
				return $dataArr;
			}			
		}
	}
	
	function setbulkdata($parentid,$data_city,$table,$module)
	{
		global $db;
		$conn_city 		= ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
		
		if((strtolower($module) == 'me') || (strtolower($module) == 'jda')){
			$conn_temp = $db[$conn_city]['idc']['master'];
		}else if(strtolower($module) == 'tme'){
			$conn_temp = $db[$conn_city]['tme_jds']['master'];
		}
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
		$resFetchData 	= parent::execQuery($sqlFetchData, $conn_temp);
		if($resFetchData && parent::numRows($resFetchData)){
			$row_data	=	parent::fetchData($resFetchData);
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
				//~ if((strtolower($module) == 'tme') && !(in_array(strtolower(trim($params['data_city'])),$this->ignore_city_arr)))
				//~ {
					//~ $res = $this->setjoindata($params);
					//~ if($res){
						//~ $success_flag = 1;
						//~ return $res;
					//~ }
				//~ }
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
		
		global $db;
		$conn_city 		= ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
		
		if((strtolower($module) == 'me') || (strtolower($module) == 'jda')){
			$conn_temp = $db[$conn_city]['idc']['master'];
		}else if(strtolower($module) == 'tme'){
			$conn_temp = $db[$conn_city]['tme_jds']['master'];
		}
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $parentid;
		$mongo_inputs['data_city'] 	= $data_city;
		$mongo_inputs['module'] 	= $module;
		
		$sqlFetchExtraDetails = "SELECT * FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$parentid."'";
		$sqlFetchExtraDetails = $sqlFetchExtraDetails."/* TMEMONGOQRY */";
		$resFetchExtraDetails 	= parent::execQuery($sqlFetchExtraDetails, $conn_temp);
		if($resFetchExtraDetails && parent::numRows($resFetchExtraDetails)){
			
			$row_ext_details	=	parent::fetchData($resFetchExtraDetails);
			$extrdet_tbl = 'tbl_companymaster_extradetails_shadow';
			$mongo_data[$extrdet_tbl]['updatedata'] = $row_ext_details;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		
		$sql3 = "SELECT * FROM tbl_business_temp_data WHERE contractid = '".$parentid."'";
		$sql3 = $sql3."/* TMEMONGOQRY */";
		$res3 	= parent::execQuery($sql3, $conn_temp);
		if($res3 && parent::numRows($res3)){
			
			$row3	=	parent::fetchData($res3);
			$bustemp_tbl = 'tbl_business_temp_data';
			$mongo_data[$bustemp_tbl]['updatedata'] = $row3;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		if((count($mongo_inputs)>0) && (count($mongo_inputs['table_data'])>0)){
			$this->updateData($mongo_inputs);
		}
		
		$sql4 = "SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $parentid . "'";
		$sql4 = $sql4."/* TMEMONGOQRY */";
		$res4 = parent::execQuery($sql4, $conn_temp);
		if($res4 && parent::numRows($res4)){
			$row4 =	parent::fetchData($res4);
			return $row4;
		}
		else
			return 0;
	}
	
	function getAllData($params)
	{
		$resultArr=array();
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg($message));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		$curl_url = MONGOAPI."getalldata";
		
		$curl_response_str 			= $this->sendRequest($curl_url,$params);
		if($curl_response_str){
			$curl_response_arr	= json_decode($curl_response_str,true);
			if($curl_response_arr['error'] ==  "0"){
				$success_flag = 1;
				return $curl_response_arr['data'];
			}
		}
		if(!$success_flag){
			$dataArr = array();
			return $dataArr;
		}
	}
	
	function getMysqlData($params)
	{
		global $db;
		$resultArr=array();
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg($message));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}		
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		$mongo_inputs	= array();
		$conn_city 		= ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
		//$this->conn_idc = $db[$conn_city]['idc']['master'];
		
		if((strtolower($module) == 'me') || (strtolower($module) == 'jda')){
			$conn_temp = $db[$conn_city]['idc']['master'];
		}else if(strtolower($module) == 'tme'){
			$conn_temp = $db[$conn_city]['tme_jds']['master'];
		}
		
		$sqlFetchGenInfo = "SELECT * FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$parentid."'";
		$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
		if($resFetchGenInfo && parent::numRows($resFetchGenInfo)){
			
			$row_gen_info	=	parent::fetchData($resFetchGenInfo);
			$geninfo_tbl = 'tbl_companymaster_generalinfo_shadow';
			$mongo_data[$geninfo_tbl] = $row_gen_info;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sqlFetchExtraDetails = "SELECT * FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$parentid."'";
		$resFetchExtraDetails 	= parent::execQuery($sqlFetchExtraDetails, $conn_temp);
		if($resFetchExtraDetails && parent::numRows($resFetchExtraDetails)){
			
			$row_ext_details	=	parent::fetchData($resFetchExtraDetails);
			$extrdet_tbl = 'tbl_companymaster_extradetails_shadow';
			$mongo_data[$extrdet_tbl] = $row_ext_details;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sql2 = "SELECT * FROM tbl_temp_intermediate WHERE parentid = '".$parentid."'";
		$res2 	= parent::execQuery($sql2, $conn_temp);
		if($res2 && parent::numRows($res2)){
			
			$row2	=	parent::fetchData($res2);
			$intermd_tbl = 'tbl_temp_intermediate';
			$mongo_data[$intermd_tbl] = $row2;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sql3 = "SELECT * FROM tbl_business_temp_data WHERE contractid = '".$parentid."'";
		$res3 	= parent::execQuery($sql3, $conn_temp);
		if($res3 && parent::numRows($res3)){
			
			$row3	=	parent::fetchData($res3);
			$bustemp_tbl = 'tbl_business_temp_data';
			$mongo_data[$bustemp_tbl] = $row3;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		return $mongo_inputs['table_data'];
	}
	
	function getMysqlTableData($params)
	{
		//return $params;
		$parentid 	= trim($params['parentid']);
		$data_city 	= trim($params['data_city']);
		$module 	= trim($params['module']);
		
		global $db;
		$conn_city 		= ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
		//$conn_idc = $db[$conn_city]['idc']['master'];
		
		if((strtolower($module) == 'me') || (strtolower($module) == 'jda')){
			$conn_temp = $db[$conn_city]['idc']['master'];
		}else if(strtolower($module) == 'tme'){
			$conn_temp = $db[$conn_city]['tme_jds']['master'];
		}

		$tables = json_decode($params['table'],true);
		
		$result = array();
		foreach($tables as $table=>$fields)
		{
			$fields_arr 	= explode(",",$fields);
			$fields_arr 	= array_unique(array_filter($fields_arr));
			
			$all_filelds 	= $this->getColumnList($table,$conn_temp);
			
			$existfieldarr = array();
			foreach($fields_arr as $fieldval){
				if(in_array($fieldval,$all_filelds)){
					$existfieldarr[] = $fieldval;
				}
			}
			
			if(count($existfieldarr)>0){
				$existfieldstr = implode(",",$existfieldarr);
				
				if($table == 'tbl_business_temp_data'){
					$wherecondn = " WHERE contractid = '".$parentid."' ";
				}else{
					$wherecondn = " WHERE parentid = '".$parentid."' ";
				}
				
				$sqlFetchGenInfo = "SELECT ".$existfieldstr." FROM ".$table." ".$wherecondn;
				$sqlFetchGenInfo = $sqlFetchGenInfo."/* TMEMONGOQRY */";
				$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
				if($resFetchGenInfo && parent::numRows($resFetchGenInfo)){					
					$row_gen_info	=	parent::fetchData($resFetchGenInfo);
					foreach($row_gen_info as $key=>$val)
					{
						$result[$key] = $val;
					}
				}
			}
		}
		return $result;
	}
	
	function getColumnList($tablename,$conn_idc)
	{
		if(!empty($tablename))
		{
			$sql_column_list	=	"SELECT * FROM ".$tablename." LIMIT 1";
			$sql_column_list	=	$sql_column_list."/* TMEMONGOQRY2 */";
			$res_column_list	=	parent::execQuery($sql_column_list, $conn_idc);
			$field = mysql_num_fields($res_column_list);
			for ($i = 0; $i < $field; $i++) {
				$coulmn_names[] = mysql_field_name($res_column_list, $i);
			}
			return $coulmn_names;
		}
	}
	
	function getTableData($params)
	{
		//return $params;
		$resultArr=array();
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg($message));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		$curl_url = MONGOAPI."gettabledata";
		
		$curl_response_str 			= $this->sendRequest($curl_url,$params);
		if($curl_response_str){
			$curl_response_arr	= json_decode($curl_response_str,true);
			if($curl_response_arr['error'] ==  "0"){
				$success_flag = 1;
				return $curl_response_arr['data'];
			}
			else
			{
				if($this->dev_mode == 1 || strtolower($module) == 'tme')
				{
					$res = $this->getMysqlTableData($params);
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
	
	function getShadowData($params)
	{
		$resultArr=array();
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg($message));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		if(trim($module)==''){
			$this->invalid_data = 1;
			$message = "Module Name is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		
		$curl_url = MONGOAPI."getshadowdata";
		
		$curl_response_str 			= $this->sendRequest($curl_url,$params);
		if($curl_response_str){
			$curl_response_arr	= json_decode($curl_response_str,true);
			if($curl_response_arr['error'] == "0")
			{				
				if(!empty($curl_response_arr['notfound']))
				{
					if(strtolower($module) == 'tme')
					{
						$params['table'] = $curl_response_arr['notfound'];
						$res = $this->mysql_getShadowData($params);
						$result = array_merge($curl_response_arr['data'],$res);
						$success_flag = 1;
						return $result;
					}
				}
				$success_flag = 1;
				return $curl_response_arr['data'];
			}
		}
		if(!$success_flag){
			$dataArr = array();
			return $dataArr;
		}
	}
	
	function mysql_getShadowData($params)
	{
		$parentid 	= trim($params['parentid']);
		$data_city 	= trim($params['data_city']);
		$module 	= trim($params['module']);
		
		global $db;
		$conn_city 		= ((in_array(strtolower($data_city), $this->dataservers)) ? strtolower($data_city) : 'remote');
		
		if((strtolower($module) == 'me') || (strtolower($module) == 'jda')){
			$conn_temp = $db[$conn_city]['idc']['master'];
		}else if(strtolower($module) == 'tme'){
			$conn_temp = $db[$conn_city]['tme_jds']['master'];
		}
		$lookup_tables_arr = array("tbl_companymaster_generalinfo_shadow","tbl_companymaster_extradetails_shadow");
		$tables = $params['table'];
		
		$result = array();
		foreach($tables as $table=>$fields)
		{
			if(in_array($table,$lookup_tables_arr))
			{
				$fields_arr 	= explode(",",$fields);
				$fields_arr 	= array_unique(array_filter($fields_arr));
				
				$all_filelds 	= $this->getColumnList($table,$conn_temp);
				
				$existfieldarr = array();
				foreach($fields_arr as $fieldval){
					if(in_array($fieldval,$all_filelds)){
						$existfieldarr[] = $fieldval;
					}
				}
				
				if(count($existfieldarr)>0){
					$existfieldstr = implode(",",$existfieldarr);
					
					if($table == 'tbl_business_temp_data'){
						$wherecondn = " WHERE contractid = '".$parentid."' ";
					}else{
						$wherecondn = " WHERE parentid = '".$parentid."' ";
					}
					
					$sqlFetchGenInfo = "SELECT ".$existfieldstr." FROM ".$table." ".$wherecondn;
					$sqlFetchGenInfo = $sqlFetchGenInfo."/* TMEMONGOQRY */";
					$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
					if($resFetchGenInfo && parent::numRows($resFetchGenInfo)){					
						$row_gen_info	=	parent::fetchData($resFetchGenInfo);
						$result[$table] 	= $row_gen_info;
					}
				}
				else
				{
					if($table == 'tbl_business_temp_data'){
						$wherecondn = " WHERE contractid = '".$parentid."' ";
					}else{
						$wherecondn = " WHERE parentid = '".$parentid."' ";
					}
					
					$sqlFetchGenInfo = "SELECT * FROM ".$table." ".$wherecondn;
					$sqlFetchGenInfo = $sqlFetchGenInfo."/* TMEMONGOQRY */";
					$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
					if($resFetchGenInfo && parent::numRows($resFetchGenInfo)){					
						$row_gen_info	=	parent::fetchData($resFetchGenInfo);
						$result[$table] = $row_gen_info;
					}
				}
			}
		}
		return $result;
	}
	
	function validateParams($params,$action=''){
		
		$parentid 		= trim($params['parentid']);
		$data_city 		= trim($params['data_city']);
		$table			= trim($params['table']);
		$module			= trim($params['module']);
		
		if(trim($parentid)==''){
			$this->invalid_data = 1;
            $message = "Parentid is blank.";
            echo json_encode($this->sendResponseMsg($message));
            die();
        }
        if(trim($data_city)==''){
			$this->invalid_data = 1;
			$message = "Data City is blank.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		if(trim($table)=='' && $action !='update'){
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
		
		if(in_array(strtolower($data_city),$this->main_cities_arr)){
			$this->invalid_data = 1;
			$message = "Mongo API is only applicable in Remote Cities.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
		if((!in_array(strtolower($table),$this->mongo_tables_arr)) && ($action !='update')){
			$this->invalid_data = 1;
			$message = "Invalid Table.";
			echo json_encode($this->sendResponseMsg($message));
			die();
		}
	}
	function sendRequest($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
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
