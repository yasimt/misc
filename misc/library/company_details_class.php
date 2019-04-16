<?
	##############################################
	##	Class For Company details API - Pratik	##
	##############################################

class companyClass{
	var $module 	='';
	var $data_city 	='';
	var $parentid 	='';
	var $action 	='';
	var $die_msg_arr  = array();
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct()
	{
		include_once('config.php');		
		include_once('path.php');					
	}

	public function getCompanyInfo($params){
		$this->validateMandateParam($params);
		//echo "<pre>";print_r($this->die_msg_arr);
		
		if($this->die_msg_arr['error']['code'] == 1){
			return json_encode($this->die_msg_arr);			
		}
		$comp_res = '';
		switch($this->action){
			case 'fetchdata':
				$comp_res = $this->fetchData($params);
				break;
			case 'updatedata':
				$comp_res = $this->updateData($params);
				break;
			default:
				$msg = 'Please enter valid action';
				$this->sendErrorMsg($msg);
		}
		if($this->die_msg_arr['error']['code'] == 1){
			echo json_encode($this->die_msg_arr);
		}
		else{
			return $comp_res;
		}
	}
	public function fetchData($params){
		$this->validateFetchParams($params);
		//echo "<pre>";print_r($this->die_msg_arr);
		if($this->die_msg_arr['error']['code'] == 1){
			return json_encode($this->die_msg_arr);			
		}		
		$input_arr 	= array();
		$curl_url 	=	COMPANY_DATA_URL."/get";
		$table		=	trim($params['table']);

				
		$input_arr['city']		=	$this->data_city;
		$input_arr['rsrc']		=	$this->module;
		$input_arr['pid']		=	$this->parentid;
		$input_arr['fields']	=	$this->fields;
		$input_arr['type']		=	$table;

		//echo "<pre>";print_r($input_arr);
		if($table!=''){
			$curl_res = $this->curlCallPost($curl_url,$input_arr);
		}
		else{
			$msg = 'Please enter valid table name';
			$this->sendErrorMsg($msg);
		}
		$curl_res_arr = array();
		if($curl_res!=''){
			$curl_res_arr =	json_decode($curl_res,TRUE);
		}
		return $curl_res;
	}

	public function updateData($params){		
		$api_invalid_flag = 0;
		$this->validateUpdateParams($params);
		if($this->die_msg_arr['error']['code'] == 1){
			$api_invalid_flag =1;		
		}
		$page = $params['page'];
		if($api_invalid_flag!=1){		
			$curl_url 	=	COMPANY_DATA_URL."/set";

			$comp_params = array();		
			$comp_params['pid'] 		= $params['parentid'];
			$comp_params['city'] 		= $params['data_city'];
			$comp_params['usrid'] 		= $params['usrid'];
			$comp_params['usrnm'] 		= $params['usrnm'];
			$comp_params['rsrc'] 		= $params['rsrc'];
			$comp_params['update_data'] = $params['update_data'];		
			$post_data 					= http_build_query($comp_params);
			$curl_res = $this->curlCallPostLog($curl_url,$post_data);
		}
		$curl_res_arr = array();
		$curl_err_flag = 0;
		if($curl_res!=''){
			$curl_res_arr =	json_decode($curl_res,TRUE);
		}
		else{
			$curl_err_flag = 0;
		}
		
		if($curl_res_arr['errors']['code'] !='0'){
			$curl_err_flag  = 1;
		}
		$text_data['curl_url'] 	= $curl_url;
		$text_data['curl_data'] = $comp_params;
		$text_data['curl_res'] 	= $curl_res;
		$text_data['page'] 		= $page;
		$text_data['parentid'] 	= trim($params['parentid']);
		$this->insertTextLog($text_data,2);		
		
		if($curl_err_flag == 1){
			$sql_upd_log =	"INSERT INTO tbl_comp_update_log SET 
							 curl_url 	= '".addslashes(stripslashes($curl_url))."',
							 curl_res 	= '".$curl_res."',
							 curl_params = '".json_encode($comp_params)."',						
							 updatedOn	= NOW(),
							 parentid 	= '".$params['parentid']."',
							 source		= '".$page."' ";
			//$res_upd_log = parent::execQuery($sql_upd_log,$this->conn_iro);
		}
		return $curl_res;

	}
	private function validateFetchParams($params){
		$fields		=	trim($params['fields']);
		$table		=	trim($params['table']);
		if($fields ==''){
			$msg = 'Please enter fields';
			$this->sendErrorMsg($msg);			
		}
		if($table ==''){
			$msg = 'Please enter table name';
			$this->sendErrorMsg($msg);			
		}
		$this->fields = $fields;
		
		//echo "<pre>";print_r($this->die_msg_arr);
	}
	private function validateMandateParam($params){		
		$module		=	trim($params['module']);
		$data_city	=	trim($params['data_city']);
		$parentid	=	trim($params['parentid']);
		$fields		=	trim($params['fields']);
		$table		=	trim($params['table']);
		$action		=	trim($params['action']);

		if($module	==''){
			$module ='CS';
		}
		if($data_city	==''){
			$msg = 'Data city is blank';
			$this->sendErrorMsg($msg);			
		}
		if($parentid ==''){
			$msg = 'Please enter parentid';
			$this->sendErrorMsg($msg);			
		}
		//~ if($fields ==''){
			//~ $msg = 'Please enter fields';
			//~ echo json_encode($this->sendErrorMsg($msg));
			
		//~ }
		//~ if($table ==''){
			//~ $msg = 'Please enter table name';
			//~ echo json_encode($this->sendErrorMsg($msg));
			//~ die;
		//~ }
		if($action ==''){
			$msg = 'Please enter action name';
			$this->sendErrorMsg($msg);
		}		
		$this->module 		= $module;
		$this->data_city 	= $data_city;
		$this->parentid 	= $parentid;			
		$this->action 		= strtolower($action);		
	}
	private function validateUpdateParams($params){			
		$usrid				=	trim($params['usrid']);
		$usrnm				=	trim($params['usrnm']);
		$rsrc				=	trim($params['rsrc']);
		$update_data		=	trim($params['update_data']);
		if($usrid ==''){
			$msg = 'Please enter usrid';
			$this->sendErrorMsg($msg);
		}
		if($usrnm ==''){
			$msg = 'Please enter usrnm';
			$this->sendErrorMsg($msg);
		}
		if($rsrc ==''){
			$msg = 'Please enter source';
			$this->sendErrorMsg($msg);
		}
		if($update_data ==''){
			$msg = 'Please enter update_data';
			$this->sendErrorMsg($msg);
		}
		if($update_data!=''){
			$update_data_arr  =	json_decode($update_data,TRUE);
			if(count($update_data_arr)<1){
				$msg = 'Please enter update_data in json format';
				$this->sendErrorMsg($msg);
			}
		}
	}
	
	private function insertTextLog($data_arr,$flag)
	{
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $data_arr['parentid'];
		$post_data['PUBLISH']           = 'CS';
		$post_data['ROUTE']             = 'COMP_API_ERROR';
		$post_data['CRITICAL_FLAG'] 	= 1;
		if($flag == 1){
			$post_data['MESSAGE']       	= 'error while fetching data from company api';
		}
		else{
			$post_data['MESSAGE']       	= 'error while update data from company api';
		}
		$post_data['DATA']['url']       = $data_arr['curl_url'];
		$post_data['DATA_JSON']['paramssubmited'] = $data_arr['curl_data'];
		$post_data['DATA_JSON']['response'] = $data_arr['curl_res'];
		$post_data = http_build_query($post_data);
		/*echo "<pre>";print_r($data_arr);
		echo "<pre>";print_r($post_data);*/
		$log_res = $this->curlCallPostLog($log_url,$post_data);
	}

	private function curlCallPost($curlurl,$data){
		//~ echo $curlurl;
		//~ echo "<pre>";print_r($data);
		$data_str = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                         
			'Content-Type: application/json',                                                                                
			'Content-Length: ' . strlen($data_str))                                                                       
		);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function curlCallPostLog($curlurl,$data_str){
		/*echo $curlurl;
		echo "<pre>";print_r($data_str);*/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_str);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function sendErrorMsg($msg){
		$this->die_msg_arr['error']['code'] 	= 1;
		$this->die_msg_arr['error']['msg'][] 	= $msg." (Company Class)";		
	}

}

?>
