<?
	##############################################
	##	Class For Calling Category API - Pratik	##
	##############################################
class categoryClass {
	var $module 	='';
	var $data_city 	='';
	var $catid 		='';
	var $catname 	='';
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct()
	{
		include_once('Paths.php');
		//$this->params = $params;			
	}
	public function getCatRelatedInfo($params){
		$validationRes = $this->validateMandateParam($params);
		if($validationRes['error']['code'] == 1){
			return json_encode($validationRes);
		}

		$input_arr 	= array();

		$curl_url 	=	JDBOX_API."services/category_data_api.php";
		//$json_data  = json_encode($params);
	
		$return			=	trim($params['return']);
		$where_condn	=	trim($params['where']);
		$limit			=	trim($params['limit']);
		$orderby		=	trim($params['orderby']);
		$skip_log		=	trim($params['skip_log']);
		$page			=	trim($params['page']);
		$scase			=	trim($params['scase']);
		$q_type			=	trim($params['q_type']);
		

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
		
		if($return!=''){
			$input_arr['return']=	$return;
		}
		//echo "<pre>";print_r($input_arr);
		$curl_res = $this->curlCallPost($curl_url,$input_arr);
		
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
