<?


$cat_params = array();
$cat_params['data_city'] 	= $_REQUEST['data_city'];
$cat_params['table'] 		= $_REQUEST['table'];
$cat_params['module'] 		= $_REQUEST['module'];
$cat_params['parentid'] 	= $_REQUEST['parentid'];
$cat_params['fields']		= $_REQUEST['fields'];
$cat_params['action']		=$_REQUEST['action'];
$curl_call					=$_REQUEST['curl_call'];



if($curl_call == 1){
	//echo "curlcall";
$url = "http://pratikjain.jdsoftware.com/jdbox/company_info_api.php";
$res = curlCallPost($url,$cat_params);
//echo $res;
}
else{
	require_once('company_details_class.php');
	$categoryClass_obj = new companyClass();
	//echo "includes";
	$cat_api_res		= array();
	$res	=	$categoryClass_obj->getCompanyInfo($cat_params);
	$cat_res_arr = json_decode($cat_api_res_str,TRUE);
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
