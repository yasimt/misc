<?

//ini_set('display_errors',1); error_reporting(E_ALL);

$cat_params = array();
$cat_params['data_city'] 	= $_REQUEST['data_city'];
$cat_params['table'] 		= $_REQUEST['table'];
$cat_params['module'] 		= $_REQUEST['module'];
$cat_params['parentid'] 	= $_REQUEST['parentid'];
$cat_params['fields']		= $_REQUEST['fields'];
$cat_params['action']		=$_REQUEST['action'];
$curl_call					=$_REQUEST['curl_call'];
$cat_params['skip_log']		=$_REQUEST['skip_log'];



if($curl_call == 1){
	//echo "curlcall";
//~ $url = "http://pratikjain.jdsoftware.com/jdbox/company_info_api.php";
//~ $res = curlCallPost($url,$cat_params);
//echo $res;
}
else{
	require_once('company_details_class.php');
	$categoryClass_obj = new companyClass();
	//echo "includes";
	$cat_api_res		= array();
	if($_REQUEST['action'] =='fetchdata'){
		$res	=	$categoryClass_obj->getCompanyInfo($cat_params);
	}
	else if($_REQUEST['action'] =='updatedata') {	
		$cat_params = array();
		$cat_params['usrid']		='12389';
		$cat_params['usrnm'] 		='Mani';
		$cat_params['data_city'] 	='Mumbai';
		$cat_params['parentid'] 	='P1000';
		$cat_params['rsrc'] 	 	='cs';

		$update_data = array();
		$update_data['extra_det_id']['companyname'] 			='test99';
		$update_data['extra_det_id']['original_date'] 			='1996-02-31';
		$update_data['extra_det_id']['type_flag']['set9'] 		="2";
		$update_data['extra_det_id']['iro_type_flag']['set'] 	="2";
		$update_data['gen_info_id']['companyname'] 				='test123';
		$cat_params['update_data'] = json_encode($update_data);
		//{"usrid":"012345","usrnm":"abhi","city":"mumbai","rsrc":"cs","pid":"P1000","update_data":{"extra_det_id":{"companyname":"test","original_date":"1996-02-31","type_flag":{"set":"2,4,8"},"iro_type_flag":{"set":"2,4,8"}},"gen_info_id":{"companyname":"test123"}}};

		$res	=	$categoryClass_obj->updateData($cat_params);
	}
	else{
		$res	=	$categoryClass_obj->getCompanyInfo($cat_params);
	}
	
}
echo $res;

//echo "<pre>";print_r($cat_res_arr);	
function curlCallPost($curlurl,$data){
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
	
?>
