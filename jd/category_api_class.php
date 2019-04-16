<?
	##############################################
	##	Class For Calling Category API - Pratik	##
	##############################################
require_once('config.php');
class categoryClass extends DB{
	var $module 	='';
	var $data_city 	='';
	var $catid 		='';
	var $catname 	='';
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct()
	{
		include_once('library/configclass.php');

		//$this->params = $params;			
	}
	public function getCatRelatedInfo($params){
		$validationRes = $this->validateMandateParam($params);
		if($validationRes['error']['code'] == 1){
			return json_encode($validationRes);
		}
		$config_obj	= new configclass();
		$url_arr 	= array();
		$input_arr 	= array();

		$url_arr 	=	$config_obj->get_url($this->data_city);
		$curl_url 	=	$url_arr['jdbox_service_url']."category_data_api.php";
		//$json_data  = json_encode($params);
	
		$return			=	trim($params['return']);
		$where_condn	=	trim($params['where']);
		$limit			=	trim($params['limit']);
		$orderby		=	trim($params['orderby']);
		$skip_log		=	trim($params['skip_log']);
		$page			=	trim($params['page']);
		$scase			=	trim($params['scase']);
		$q_type			=	trim($params['q_type']);
		$debug			=	trim($params['debug']);

		if($limit!=''){
			$input_arr['limit'] = $limit;
		}
		if($orderby!=''){
			$input_arr['orderby'] = $orderby;
		}

		$where_arr 		= 	array();
		$where_arr		=	json_decode($where_condn,TRUE);
		/*echo "<pre>dsad";print_r($where_arr);
		echo count($where_arr);
		echo is_array($where_arr);*/
		
		if(is_array($where_arr) && count($where_arr)>0){
			$input_arr['where'] = trim($params['where']);
		}
		else{
			$msg = 'Please enter proper where condition2';
			return json_encode($this->sendErrorMsg($msg));			
		}

		$input_arr['city']		=	$this->data_city;
		$input_arr['module']	=	$this->module;
		$input_arr['scase']		=	$scase;
		$input_arr['q_type']	=	$q_type;
		$input_arr['trace']		=	$debug;

		if($return!=''){
			$input_arr['return']=	$return;
		}
		//echo "<pre>";print_r($input_arr);
		$curl_res = $this->curlCallPost($curl_url,$input_arr);
		if($debug==1){
			return $curl_res;
		}		

		$curl_res_arr = array();
		$curl_error_flag = 0;
		if($curl_res!=''){
			$curl_res_arr = json_decode($curl_res,TRUE);
		}
		else{
			$curl_error_flag= 1;
		}
		if($curl_res_arr['errorcode']!='0'){
			$curl_error_flag= 1;	
		}
		if($skip_log == 1 && $curl_res_arr['errorcode']==2){
			$curl_error_flag	=	0;
		}
		if($curl_error_flag==1){
			$sql_err_log =	"INSERT INTO tbl_catapi_err_log SET 
						 curl_url 	= '".addslashes(stripslashes($curl_url))."',
						 curl_res_data 	= '".json_encode($curl_res)."',
						 catapi_request = '".json_encode($params)."',
						 source_page	= '".$page."',
						 updatedOn		= NOW()";
			//$res_err_log = parent::execQuery($sql_err_log,$this->conn_iro);
			$text_data = array();
			$text_data['curl_url'] 	= $curl_url;
			$text_data['curl_data'] = $input_arr;
			$text_data['curl_res'] 	= $curl_res;
			$text_data['page'] 	= $page;
			//$this->insertTextLog($text_data);
		}
		
		return $curl_res;
	}
	
	private function validateMandateParam($params){
		$module		=	trim($params['module']);
		$data_city	=	trim($params['data_city']);
		$where_str	=	trim($params['where']);
		if($module	==''){
			$module ='JDBOX';
		}
		if($data_city	==''){
			$msg = 'Data city is blank';
			return $this->sendErrorMsg($msg);			
		}
		if($where_str ==''){
			$msg = 'Please enter where condition';
			return $this->sendErrorMsg($msg);
		}
		
		$this->module 		= $module;
		$this->data_city 	= $data_city;
		
		$this->setServers();
	}
	private function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro = $db[$data_city]['iro']['master'];
	}
	private function insertTextLog($data_arr)
	{	//need to change 
		$post_data = array();
		$log_url = 'http://192.168.17.109/logs/logs.php';
		$post_data['ID']                = $data_arr['page'];
		$post_data['PUBLISH']           = 'ME';
		$post_data['ROUTE']             = 'CAT_API_ERROR';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= 'error while fetching data from category api';
		$post_data['DATA']['url']       = $data_arr['curl_url'];
		$post_data['DATA_JSON']['paramssubmited'] = $data_arr['curl_data'];
		$post_data['DATA_JSON']['response'] = $data_arr['curl_res'];
		$post_data = http_build_query($post_data);
		/*echo "<pre>";print_r($data_arr);
		echo "<pre>";print_r($post_data);*/
		$log_res = $this->curlCallPost($log_url,$post_data);
	}
	private function curlCallPost($curlurl,$data_str){
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
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['msg'] = $msg." (Category Class)";
		return $die_msg_arr;
	}
}

?>
