<?
	##############################################
	##	Class For Calling Category API - Pratik	##
	##############################################

class categoryClass{
	var $module 	='';
	var $data_city 	='';
	var $catid 		='';
	var $catname 	='';
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	function __construct()
	{
		include_once('config.php');		
		include_once('path.php');		
		//$this->params = $params;			
	}
	public function getCatRelatedInfo($params){
		$this->validateMandateParam($params);
		
		$url_arr 	= array();
		$input_arr 	= array();

		$curl_url 	=	JDBOX_SERVICES_API."/category_data_api.php";
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
			$msg = 'Please enter proper where condition';
			echo json_encode($this->sendErrorMsg($msg));
			die;
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
	
	// fetch parent categories
	function getParentCategories($catidlist,$data_city)
	{	
		$parent_categories_arr = array();
		$catidlistarr 	= explode(",",$catidlist);	
		$catidlistarr 	= array_filter(array_unique($catidlistarr));
		if(count($catidlistarr)>0)
		{
			$cat_params = array();
			$cat_params['page'] 		= 'category_api_class';
			$cat_params['data_city'] 	= $data_city;
			$cat_params['return']		= 'associate_national_catid,national_catid';			
			$where_arr  				= array();
			$where_arr['catid']			= implode(",",$catidlistarr);
			$cat_params['where']		= json_encode($where_arr);
			$cat_res					= $this->getCatRelatedInfo($cat_params);
			
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,1);
				if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
					$associate_national_catid_arr = array();
					foreach ($cat_res_arr['results'] as $key => $cat_arr) {
						$associate_national_catid =  $cat_arr['associate_national_catid'];
						if($associate_national_catid!=''){
							$associate_national_catid_arr[]= $associate_national_catid;
						}
					}

					if(count($associate_national_catid_arr)>0){
						$associate_national_catid_arr = array_filter(array_unique($associate_national_catid_arr));
						$associate_national_catid_str = implode(",",$associate_national_catid_arr);
						$cat_params = array();
						$cat_params['page'] 		= 'category_api_class';
						$cat_params['data_city'] 	= $this->data_city;
						$cat_params['return']		= 'catid';			

						$where_arr  	=	array();
						if($associate_national_catid_str!=''){
							$where_arr['national_catid']	   = $associate_national_catid_str;
							$where_arr['biddable_type']		   = '1';
							$where_arr['mask_status']		   = '0';
							$where_arr['premimum_flag']		   = '0';
							$where_arr['bfc_bifurcation_flag'] = '!4,5,6,7';
							$cat_params['where']			   = json_encode($where_arr);
							$nat_cat_res	=	$this->getCatRelatedInfo($cat_params);
						}

						$nat_cat_res_arr = array();
						if($nat_cat_res!=''){
							$nat_cat_res_arr =	json_decode($nat_cat_res,1);
							if($nat_cat_res_arr['errorcode'] =='0' && count($nat_cat_res_arr['results'])>0){
								
								foreach ($nat_cat_res_arr['results'] as $key => $cat_arr) {
									if($cat_arr['catid']!=''){
										$parent_categories_arr[] = $cat_arr['catid'];
										$parent_categories_arr[] = $cat_arr['national_catid'];
									}
								}
								if(count($parent_categories_arr)>0){
									$parent_categories_arr = array_filter(array_unique($parent_categories_arr));
								}
							}		
						}
					}
				}
			}
		}
		//echo "<pre>parent_categories_arr:----"; print_r($parent_categories_arr);
		return $parent_categories_arr;
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
			echo json_encode($this->sendErrorMsg($msg));
			die;
		}
		if($where_str ==''){
			$msg = 'Please enter where condition';
			echo json_encode($this->sendErrorMsg($msg));
			die;
		}
		
		$this->module 		= $module;
		$this->data_city 	= $data_city;
		
		//$this->setServers();
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
