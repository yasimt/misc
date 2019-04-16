<?
	##############################################
	##	Class For Company details API - Pratik	##
	##############################################
//ini_set('display_errors',1); error_reporting(E_ALL);

class companyClass{
	var $module 	='';
	var $data_city 	='';
	var $parentid 	='';
	var $action 	='';
	var $die_msg_arr  = array();
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct()
	{
		//include_once('Paths.php');
	}

	public function getCompanyInfo($params){
		$this->validateMandateParam($params);
		if($this->die_msg_arr['error']['code'] == 1){
			return json_encode($this->die_msg_arr);			
		}

		$comp_res = '';
		switch($this->action){
			case 'fetchdata':				
				$comp_res =  $this->getData($params);
				break;
			case 'updatedata':
				$comp_res = $this->updateData($params);
				break;
			default:
				$msg = 'Please enter valid action';
				$this->sendErrorMsg($msg);
				//die;
		}
		if($this->die_msg_arr['error']['code'] == 1){
			return json_encode($this->die_msg_arr);
		}
		else{
			return $comp_res;
		}
	}
	public function getData($params){		
		$this->validateFetchParams($params);
		if($this->die_msg_arr['error']['code'] == 1){
			return json_encode($this->die_msg_arr);			
		}

		$input_arr 	= array();
	
		$curl_url 	=	COMPANY_DATA_URL."/get";

		$table			=	trim($params['table']);
		$page 			=   trim($params['page']);
		$skip_log 		=   trim($params['skip_log']);
		

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
			//die;
		}
		
		return $curl_res;
	}

	public function updateData($params){
		$api_invalid_flag = 0;
		$this->validateUpdateParams($params);
		if($this->die_msg_arr['error']['code'] == 1){
			$api_invalid_flag =1;		
		}
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
		
		$text_data['curl_url'] 	= $curl_url;
		$text_data['curl_data'] = $comp_params;
		$text_data['curl_res'] 	= $curl_res;
		//$text_data['page'] 		= $page;
		$text_data['parentid'] 	= trim($params['parentid']);
		
		return $curl_res;

	}

	private function validateFetchParams($params){
		$fields		=	trim($params['fields']);
		$table		=	trim($params['table']);
		if($fields ==''){
			$msg = 'Please enter fields';
			$this->sendErrorMsg($msg);
			//die;
		}
		if($table ==''){
			$msg = 'Please enter table name';
			$this->sendErrorMsg($msg);
			//die;
		}
		$this->fields = $fields;
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
	
	private function validateMandateParam($params){
		//echo "<pre>";print_r($params);
		$module		=	trim($params['module']);
		$data_city	=	trim($params['data_city']);
		$parentid	=	trim($params['parentid']);
		//$fields		=	trim($params['fields']);
		//$table		=	trim($params['table']);
		$action		=	trim($params['action']);

		if($module	==''){
			$module ='JDBOX';
		}
		if($data_city	==''){
			$msg = 'Data city is blank';
			$this->sendErrorMsg($msg);
			//die;
		}
		if($parentid ==''){
			$msg = 'Please enter parentid';
			$this->sendErrorMsg($msg);
			//die;
		}
		/* if($fields ==''){
			$msg = 'Please enter fields';
			$this->sendErrorMsg($msg);
			//die;
		} */
		/* if($table ==''){
			$msg = 'Please enter table name';
			$this->sendErrorMsg($msg);
			//die;
		} */
		if($action ==''){
			$msg = 'Please enter action name';
			$this->sendErrorMsg($msg);
			//die;
		}
		
		$this->module 		= $module;
		$this->data_city 	= $data_city;
		$this->parentid 	= $parentid;
		//$this->fields 		= $fields;		
		$this->action 		= strtolower($action);	
	}
			
	private function curlCallPost($curlurl,$data){
		/*echo $curlurl;
		echo "<pre>";print_r($data);*/
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
		$this->die_msg_arr['error']['code'] = 1;
		$this->die_msg_arr['error']['msg'][] = $msg." (Company Class)";
		//return $die_msg_arr;
	}

}




?>
