<?php
class DealInfo_Model extends Model {
	function __construct() {
        parent::__construct(); 
    }
    public function getPublishDeals(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send data_city as data_city";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['module']) || (isset($paramsArr['module']) && $paramsArr['module'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send module as module";
            return json_encode($retArr);
        }
        // max is for pagination, start 
  //       if(!isset($paramsArr['filters']['start']) || (isset($paramsArr['filters']['start']) && $paramsArr['filters']['start'] == '')){
		// 	$retArr['errorCode'] = 1;
		// 	$retArr['errorStatus'] = "please send page number filter as start";
		// 	return json_encode($retArr);
		// }
		// if(!isset($paramsArr['filters']['max']) || (isset($paramsArr['filters']['max']) && $paramsArr['filters']['max'] == '')){
		// 	$retArr['errorCode'] = 1;
		// 	$retArr['errorStatus'] = "please send number of entries filter as max";
		// 	return json_encode($retArr);
		// }
		// if(!isset($paramsArr['filters']['coupon_storeid']) || (isset($paramsArr['filters']['coupon_storeid']) && $paramsArr['filters']['coupon_storeid'] == '')){
		// 	// coupon_storeid kept mandatory for temporary use
		// 	$retArr['errorCode'] = 1;
		// 	$retArr['errorStatus'] = "please send page number filter as coupon_storeid";
		// 	return json_encode($retArr);
		// }
        $mongo_inputs = array();
        $mongo_inputs['module'] = $paramsArr['module'];
        $mongo_inputs['action'] = 'getdata';
        $mongo_inputs['post_data'] = 1;

        $mongo_inputs['parentid'] = trim($paramsArr['parentid']);
        $mongo_inputs['data_city'] = trim($paramsArr['data_city']);
        $mongo_inputs['table'] = "tbl_business_temp_data";
        $mongo_inputs['fields'] = "nationalcatIds";
        /*CURL CALL TO GET DATA FROM MONGO*/
        $url =	DEALS_MONGO_API.'/services/mongoWrapper.php';
        $curlParams_temp = array();
        $curlParams_temp['url'] = $url;
        $curlParams_temp['method'] = 'post';
        $curlParams_temp['formate'] = 'basic';
        $curlParams_temp['postData'] = $mongo_inputs;
        $data_res = json_decode(Utility::curlCall($curlParams_temp),true);
        //~ $data_res = $this->mongo_obj->getData($mongo_inputs);
        $num = count($data_res);
        $respArr = array();
        if($num == 0){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = 'category not found';
            return json_encode($retArr);
        }
        $nation_catId_arr = array();
        $nation_catId_arr = explode("|P|",$data_res['nationalcatIds']);      
        if(isset($nation_catId_arr[1]) && trim($nation_catId_arr[1]) != "" ){
            $nation_catId_str = $nation_catId_arr[1];
            /*
                Getting Type_flag based on National Catids
                http://192.168.20.102:9001/web_services/categoryCount.php?ncatid=10983547&city=Kolkata  // API To get type_flag based on national CatIds
            */
            $curlParams_new = array();
            if (preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
                $url_new =	'http://avinashpanikar.jdsoftware.com/web_services/web_services/categoryCount.php?ncatid='.$nation_catId_str.'&city='.trim($paramsArr['data_city']).'';   

            else
                $url_new =	'http://192.168.20.102:9001/web_services/categoryCount.php?ncatid='.$nation_catId_str.'&city='.trim($paramsArr['data_city']).'';                        
            
            $curlParams_new['url'] = $url_new;
            $curlParams_new['method'] = 'get';
            $curlParams_new['formate'] = 'basic';
            $type_flag_data = json_decode(Utility::curlCall($curlParams_new),true);
            if(isset($type_flag_data['results'][0])){
            	
                $type_flag = $type_flag_data['results'][0]['display_product_flag'];
                
                $param_curl = array();
                $param_curl['coupon_storeid'] = trim($paramsArr['docid']);
                $param_curl['type_flag'] = $type_flag;
                $param_curl['max'] = 10;
                $param_curl['start'] = $paramsArr['filters']['start'];
                $docid = trim($paramsArr['docid']);
                
                
                $curlParams = array();
                $curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/getpublishdeals?docid='.$docid.'&active_flag='.$paramsArr['active_flag'].'';
                $curlParams['formate'] 		= 	'basic';
                $curlParams['method'] 		= 	'GET'; // POST
                //$curlParams['postData'] 		= 	$paramsArr; // POST
                $singleCheckTmp	=	Utility::curlCall($curlParams);
				$singleCheck    =   json_decode($singleCheckTmp,true);
				$singleCheck['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
				if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
					$retArr['result'] = $singleCheck['results'];
					$retArr['errorCode'] = 0;
					$retArr['errorStatus'] = "success";
				}
				else{
					$retArr['errorCode'] = 1;
					$retArr['errorStatus'] = "Error from Deal API ! ";
					$retArr['error'] = $singleCheck['errors'];          
				}
				return json_encode($retArr);     
            }else{
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = 'category not found';
                return json_encode($retArr);
            }
            //use this display_product_flag as  type_flag
        }else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = 'category not found';
            return json_encode($retArr);
        }        
    }
	public function updateJdDealDetails(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();
		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/updatejddealdetails';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
	public function getJdCouponTemplates(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
        if(!isset($paramsArr['entity_flag']) || (isset($paramsArr['entity_flag']) && $paramsArr['entity_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send entity_flag as entity_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send data_city as data_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['module']) || (isset($paramsArr['module']) && $paramsArr['module'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send module as module";
            return json_encode($retArr);
        }
        $CatIdApiNode_tmp = DealInfo_Model::getNatCatIdFrmParentId($paramsArr['parentid'],$paramsArr['data_city'],$paramsArr['module']);
        $CatIdApiNode = json_decode($CatIdApiNode_tmp,true);
        if($CatIdApiNode['errorCode'] != 0)
        {
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Category Issue... Category Not Found ! ";
            $retArr['tmpErrorStatus'] = $CatIdApiNode['tmpErrorStatus'];
            $retArr['mongo_curl']				= $CatIdApiNode['mongo_curl']; // for debugging test case
        	$retArr['categoryCount_curl']		= $CatIdApiNode['categoryCount_curl']; //  for debugging test case
        	$retArr['getparentcategory_curl']	= $CatIdApiNode['getparentcategory_curl']; //  for debugging test case
        	$retArr['CONST_DEALS_MONGO_API']	= $CatIdApiNode['CONST_DEALS_MONGO_API']; //  for debugging test case
            return json_encode($retArr);
        }
        else
        {
            $paramsArr['categoryid']     =   $CatIdApiNode['deals_categories']['catid'];
        }
        $retArr	=	array();
		$curlParams = array();
		$strCurlParamsArr = array();
		$strCurlParamsArr['categoryid']		=	trim($paramsArr['categoryid']);
		$strCurlParamsArr['entity_flag']	=	trim($paramsArr['entity_flag']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['server_city']	=	trim($paramsArr['server_city']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/getjdcoupontemplates?categoryid='.$strCurlParamsArr['categoryid'].'&entity_flag='.$strCurlParamsArr['entity_flag'].'&active_flag='.$strCurlParamsArr['active_flag'];
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'GET'; 
        // $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function getJdCustomTemplates(){
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['entity_flag']) || (isset($paramsArr['entity_flag']) && $paramsArr['entity_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send entity_flag as entity_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['bid']) || (isset($paramsArr['bid']) && $paramsArr['bid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send bid as bid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();
		$strCurlParamsArr = array();
		$strCurlParamsArr['parentid']		=	trim($paramsArr['parentid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['entity_flag']	=	trim($paramsArr['entity_flag']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['bid']			=	trim($paramsArr['bid']);
		$strCurlParamsArr['server_city']	=	trim($paramsArr['server_city']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/getjdcustomtemplates?docid='.$strCurlParamsArr["docid"].'&active_flag='.$strCurlParamsArr["active_flag"].'&entity_flag='.$strCurlParamsArr["entity_flag"].'&bid='.$strCurlParamsArr["bid"];
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'GET'; 
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);     
    }
    public function addJdDealInformation(){
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send title as title";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send description as description";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send startdate as startdate";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send expirydate as expirydate";
			return json_encode($retArr);
		}
        $paramsArr['saving']    =   '';
        $paramsArr['price'] =   '';
		if(!isset($paramsArr['createdby']) || (isset($paramsArr['createdby']) && $paramsArr['createdby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send createdby as createdby";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send data_city as data_city";
            return json_encode($retArr);
        }
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send applicable_on as applicable_on";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['city']) || (isset($paramsArr['city']) && $paramsArr['city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send city as city";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['source']) || (isset($paramsArr['source']) && $paramsArr['source'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send source as source";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['cityid']) || (isset($paramsArr['cityid']) && $paramsArr['cityid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send cityid as cityid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['storename']) || (isset($paramsArr['storename']) && $paramsArr['storename'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send storename as storename";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['storename']) || (isset($paramsArr['storename']) && $paramsArr['storename'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send storename as storename";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['module']) || (isset($paramsArr['module']) && $paramsArr['module'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send module as module";
            return json_encode($retArr);
        }
        else
        {
            if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
            {
                if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                    $retArr['errorCode'] = 1;
                    $retArr['errorStatus'] = "please send card_type as card_type";
                    return json_encode($retArr);
                }
                if(!isset($paramsArr['bank_name']) || (isset($paramsArr['bank_name']) && $paramsArr['bank_name'] == ''))
                {
		            $retArr['errorCode'] = 1;
		            $retArr['errorStatus'] = "please send bank_name as bank_name";
		            return json_encode($retArr);
		        }
            }
        }
        $CatIdApiNode_tmp = DealInfo_Model::getNatCatIdFrmParentId($paramsArr['parentid'],$paramsArr['data_city'],$paramsArr['module']);
        $CatIdApiNode = json_decode($CatIdApiNode_tmp,true);
        if($CatIdApiNode['errorCode'] != 0)
        {
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Category Issue... Category Not Found ! ";
            $retArr['tmpErrorStatus'] = $CatIdApiNode['tmpErrorStatus'];
            $retArr['mongo_curl']				= $CatIdApiNode['mongo_curl']; // for debugging test case
        	$retArr['categoryCount_curl']		= $CatIdApiNode['categoryCount_curl']; //  for debugging test case
        	$retArr['getparentcategory_curl']	= $CatIdApiNode['getparentcategory_curl']; //  for debugging test case
        	$retArr['CONST_DEALS_MONGO_API']	= $CatIdApiNode['CONST_DEALS_MONGO_API']; //  for debugging test case
            return json_encode($retArr);
        }
        else
        {
            $paramsArr['catid']     =   $CatIdApiNode['deals_categories']['catid'];
            $paramsArr['catname']   =   $CatIdApiNode['deals_categories']['catname'];
        }
        $retArr	=	array();
		$curlParams = array();
		$strCurlParamsArr = array();
		$strCurlParamsArr['title']			=	trim($paramsArr['title']);
		$strCurlParamsArr['description']	=	trim($paramsArr['description']);
		$strCurlParamsArr['startdate']		=	trim($paramsArr['startdate']);
		$strCurlParamsArr['expirydate']		=	trim($paramsArr['expirydate']);
		$strCurlParamsArr['saving']			=	trim($paramsArr['saving']);
		$strCurlParamsArr['price']			=	trim($paramsArr['price']);
		$strCurlParamsArr['createdby']		=	trim($paramsArr['createdby']);
		$strCurlParamsArr['catid']			=	trim($paramsArr['catid']);
		$strCurlParamsArr['catname']		=	trim($paramsArr['catname']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['applicable_on']	=	$paramsArr['applicable_on'];
		$strCurlParamsArr['city']			=	trim($paramsArr['city']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['source']			=	trim($paramsArr['source']);
		$strCurlParamsArr['cityid']			=	trim($paramsArr['cityid']);
        $strCurlParamsArr['identifier']     =   trim($paramsArr['identifier']);
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
        {
            $strCurlParamsArr['card_type']      =   trim($paramsArr['card_type']);
            if(isset($paramsArr['bank_name']) && $paramsArr['bank_name']!= ''){
        		$strCurlParamsArr['storename']      =   trim($paramsArr['bank_name']);
	        }else{
	        	$strCurlParamsArr['storename']      = 	"";
	        }
        }else{
        	$strCurlParamsArr['storename']      =   trim($paramsArr['storename']);
        }
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/addjddealinformation';
        $curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST		
		$singleCheck	=	json_decode(Utility::curlCall($curlParams),TRUE);
		if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){			
			$retArr['result'] = $singleCheck['results'];
			$retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
		}
		else{
			$retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];			
		}
		
		return json_encode($retArr);
    }
    public function addJdCustomTemplate(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['user_template']) || (isset($paramsArr['user_template']) && $paramsArr['user_template'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send user_template as user_template";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['entity_flag']) || (isset($paramsArr['entity_flag']) && $paramsArr['entity_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send entity_flag as entity_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['bid']) || (isset($paramsArr['bid']) && $paramsArr['bid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send bid as bid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['user_template']	=	urlencode(trim($paramsArr['user_template']));
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['entity_flag']	=	trim($paramsArr['entity_flag']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['bid']			=	trim($paramsArr['bid']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/addjdcustomtemplate';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
		$singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);     
    }
	public function editExistingHighlights(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['highlights']) || (isset($paramsArr['highlights']) && $paramsArr['highlights'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send highlights as highlights";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['highlights']		=	json_encode($paramsArr['highlights']);
		
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/editexistinghighlights';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);     
    }
    public function editExistingTerms(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['highlights']) || (isset($paramsArr['highlights']) && $paramsArr['highlights'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send terms as terms";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['terms']			=	json_encode($paramsArr['highlights']);
		
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/editexistingterms';
		
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
		
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);     
    }
	public function publishJdDeal(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}

		if(!isset($paramsArr['bid']) || (isset($paramsArr['bid']) && $paramsArr['bid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send bid as bid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
		$strCurlParamsArr['bid']			=	trim($paramsArr['bid']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/publishjddeal';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);

    }
    public function getJdDeals(){
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		// if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
		// 	$retArr['errorCode'] = 1;
		// 	$retArr['errorStatus'] = "please send offerid as offerid";
		// 	return json_encode($retArr);
		// }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/getjddeals?offerid='.$strCurlParamsArr['offerid'].'&docid='.$strCurlParamsArr['docid'];
        // $final = $curlParams['url'] . "?" . http_build_query($strCurlParamsArr);
        // print_r($final);die;
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'GET'; 
		$singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        
        $tmpres = Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($tmpres,true);
        
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){         
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        
        return json_encode($retArr);
        
    }
    public function updateJdDealInformation(){
		
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send title as title";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send description as description";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send startdate as startdate";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send expirydate as expirydate";
			return json_encode($retArr);
		}

        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send offerid as offerid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send updatedby as updatedby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send applicable_on as applicable_on";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['identifier']) || (isset($paramsArr['identifier']) && $paramsArr['identifier'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send identifier as identifier";
            return json_encode($retArr);
        }
        else
        {
            if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
            {
                if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                    $retArr['errorCode'] = 1;
                    $retArr['errorStatus'] = "please send card_type as card_type";
                    return json_encode($retArr);
                }
                if(!isset($paramsArr['bank_name']) || (isset($paramsArr['bank_name']) && $paramsArr['bank_name'] == ''))
                {
		            $retArr['errorCode'] = 1;
		            $retArr['errorStatus'] = "please send bank_name as bank_name";
		            return json_encode($retArr);
		        }
            }
        }
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['title']          =	trim($paramsArr['title']);
		$strCurlParamsArr['description']    =	trim($paramsArr['description']);
        $strCurlParamsArr['startdate']      =   trim($paramsArr['startdate']);
        $strCurlParamsArr['expirydate']     =   trim($paramsArr['expirydate']);
        $strCurlParamsArr['saving']         =   trim($paramsArr['saving']);
        $strCurlParamsArr['price']          =   trim($paramsArr['price']);
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['offerid']        =   trim($paramsArr['offerid']);
        $strCurlParamsArr['updatedby']      =   trim($paramsArr['updatedby']);
        $strCurlParamsArr['applicable_on']  =   trim($paramsArr['applicable_on']);
        $strCurlParamsArr['identifier']     =   trim($paramsArr['identifier']); //co
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
        {
            $strCurlParamsArr['card_type']      =   trim($paramsArr['card_type']); //co
            if(isset($paramsArr['bank_name']) && $paramsArr['bank_name']!= ''){
        		$strCurlParamsArr['storename']      =   trim($paramsArr['bank_name']);
	        }else{
	        	$strCurlParamsArr['storename']      = 	"";
	        }
        }else{
        	$strCurlParamsArr['storename']      =   trim($paramsArr['storename']);
        }
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/updatejddealinformation';

		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr; // POST
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function getNatCatIdFrmParentId($parentid,$data_city,$module)
    {
        // $parentid = "PXX22.XX22.180628163448.T8R4";
        // $data_city = "mumbai";
        $respArr = array();
        $respArr['mongo_curl']				= "Not Yet Reached Mongo call"; // for debugging test case
        $respArr['categoryCount_curl']		= "Not Yet Reached categoryCount call"; //  for debugging test case
        $respArr['getparentcategory_curl']	= "Not Yet Reached getparentcategory call"; //  for debugging test case
    	if(!isset($parentid) || (isset($parentid) && $parentid == '')){
			$respArr['errorCode'] = 1;
			$respArr['errorStatus'] = "please send parentid as parentid";
            $respArr['tmpErrorStatus'] = 'category not found stage params ==== 1 ======= ';
			return json_encode($respArr);
		}
        if(!isset($data_city) || (isset($data_city) && $data_city == '')){
			$respArr['errorCode'] = 1;
			$respArr['errorStatus'] = "please send data_city as data_city";
            $respArr['tmpErrorStatus'] = 'category not found stage params ==== 2 ======= ';
			return json_encode($respArr);
		}
        if(!isset($module) || (isset($module) && $module == '')){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = "please send module as module";
            $respArr['tmpErrorStatus'] = 'category not found stage params ==== 3 ======= ';
            return json_encode($respArr);
        }
        $mongo_inputs = array();
        $mongo_inputs['module'] = $module;
        
        $mongo_inputs['action'] = 'getalldata';

        $mongo_inputs['post_data'] = 1;

        $mongo_inputs['parentid'] = trim($parentid);
        $mongo_inputs['data_city'] = trim($data_city);
        $mongo_inputs['table'] = "tbl_business_temp_data";
        $mongo_inputs['fields'] = "nationalcatIds";
        $url =	DEALS_MONGO_API.'/services/mongoWrapper.php';
        $curlParams_temp = array();
        $curlParams_temp['url'] = $url;
        $curlParams_temp['method'] = 'post';
        $curlParams_temp['formate'] = 'basic';
        $curlParams_temp['postData'] = $mongo_inputs;
        $respArr['mongo_curl'] = '';
        $respArr['mongo_curl'] = $curlParams_temp; // for debugging test case
        $respArr['CONST_DEALS_MONGO_API'] = DEALS_MONGO_API; // for debugging test case
        
        $data_res = json_decode(Utility::curlCall($curlParams_temp),true);
        $num = count($data_res);
        if($num == 0){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'category not found';
            $respArr['tmpErrorStatus'] = 'category not found stage 1';
            return json_encode($respArr);
        }
        $nation_catId_arr = array();
        
        $nation_catId_arr = explode("|P|",$data_res['tbl_business_temp_data']['nationalcatIds']);        
        if(isset($nation_catId_arr[1]) && trim($nation_catId_arr[1]) != "" ){
            $nation_catId_str = $nation_catId_arr[1];
            $curlParams_new = array();
            if (preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
                 $url_new =    'http://avinashpanikar.jdsoftware.com/web_services/web_services/categoryCount.php?ncatid='.$nation_catId_str.'&city='.trim($data_city).'';
             }else{  
                $url_new =    'http://192.168.20.102:9001/web_services/categoryCount.php?ncatid='.$nation_catId_str.'&city='.trim($data_city).'';    
             }           
            // echo $url_new;
            $curlParams_new['url'] = $url_new;
            $curlParams_new['method'] = 'get';
            $curlParams_new['formate'] = 'basic';
            $type_flag_data = json_decode(Utility::curlCall($curlParams_new),true);
            $respArr['categoryCount_curl'] = '';
            $respArr['categoryCount_curl'] = $curlParams_new; //  for debugging test case
            $parentCat_url = array();
            $parentCat_param = array();
            if(isset($type_flag_data['results'][0])){
                $type_flag = $type_flag_data['results'][0]['display_product_flag'];
                // $type_flag = '9622925766410242';
                if($type_flag == 0)
                {
                    if (preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
                    {
                    	$parentCat_url = 'http://jigneshgore.jdsoftware.com/web_services/deals_services/getparentcategory?businessflag='.$type_flag.'&catname=others';
                    }
                    else
                    {
                        $parentCat_url = 'http://192.168.20.102:9001/deals_services/getparentcategory?businessflag='.$type_flag.'&catname=others';
                    }
                }
                else
                {
                    if (preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
                    {
                        $parentCat_url = 'http://jigneshgore.jdsoftware.com/web_services/deals_services/getparentcategory?businessflag='.$type_flag;
                    }
                    else
                    {
                        $parentCat_url = 'http://192.168.20.102:9001/deals_services/getparentcategory?businessflag='.$type_flag;
                    }
                }
                $parentCat_param['url'] 			= $parentCat_url;
                $parentCat_param['formate'] 		= 	'basic';
                $parentCat_param['method'] 		= 	'GET'; // POST
                //$curlParams['postData'] 		= 	$paramsArr; // POST
                $respArr['getparentcategory_curl'] = '';
                $respArr['getparentcategory_curl'] = $parentCat_param; //  for debugging test case
                $singleCheck	=	json_decode(Utility::curlCall($parentCat_param),true);
                $singleCheck['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
                if($singleCheck['results']['count'] != 0)
                {
                    $respArr['errorCode'] = 0;
                    $respArr['errorStatus'] = 'category found';
                    $respArr['tmpErrorStatus'] = 'success outer';
                    $respArr['deals_categories'] = $singleCheck['results']['categories'];
                    return json_encode($respArr);
                }
                else
                {
                    //Added as per Jignesh advise
                    if (preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST'])){
                        $parentCat_url_inner = "http://jigneshgore.jdsoftware.com/web_services/deals_services/getparentcategory?trace=1&catname=others";
                    }else{
                        $parentCat_url_inner = 'http://192.168.20.102:9001/deals_services/getparentcategory?trace=1&catname=others';
                    }
                    $parentCat_param_inner['url']             = $parentCat_url_inner;
                    $parentCat_param_inner['formate']         =   'basic';
                    $parentCat_param_inner['method']      =   'GET'; // POST
                    $respArr['getparentcategory_curl_inner'] = '';
                    $respArr['getparentcategory_curl_inner'] = $parentCat_param_inner; //  for debugging test case
                    $singleCheck_inner    =   json_decode(Utility::curlCall($parentCat_param_inner),true);
                    $singleCheck_inner['HTTP_HOST'] = $_SERVER['HTTP_HOST'];
                    if($singleCheck_inner['results']['count'] != 0){
                        $respArr['errorCode'] = 0;
                        $respArr['errorStatus'] = 'category found';
                        $respArr['tmpErrorStatus'] = 'success inner';
                        $respArr['deals_categories'] = $singleCheck_inner['results']['categories'];
                        return json_encode($respArr);
                    }else{
                        $respArr['errorCode'] = 1;
                        $respArr['errorStatus'] = 'category not found';
                        $respArr['tmpErrorStatus'] = 'failure inner';
                        $respArr['deals_categories'] = $singleCheck_inner['results']['categories'];
                        return json_encode($respArr);
                    }
                }
            }else{
                $respArr['errorCode'] = 1;
                $respArr['errorStatus'] = 'category not found';
                $respArr['tmpErrorStatus'] = 'category not found stage 2';
                return json_encode($respArr);
            }
        }else{
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'category not found';
            $respArr['tmpErrorStatus'] = 'category not found stage 3';
            return json_encode($respArr);
        }
    }
    public function getJdDealTemplates(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['bid']) || (isset($paramsArr['bid']) && $paramsArr['bid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send indentifier as bid";
            return json_encode($retArr);
        }
        // if(!isset($paramsArr['trace']) || (isset($paramsArr['trace']) && $paramsArr['trace'] == '')){
        //     $retArr['errorCode'] = 1;
        //     $retArr['errorStatus'] = "please send trace as trace";
        //     return json_encode($retArr);
        // }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send data_city as data_city";
            return json_encode($retArr);
        }
        $CatIdApiNode_tmp = DealInfo_Model::getNatCatIdFrmParentId($paramsArr['parentid'],$paramsArr['data_city'],$paramsArr['module']);
        $CatIdApiNode = json_decode($CatIdApiNode_tmp,true);
        if($CatIdApiNode['errorCode'] != 0)
        {
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Category Issue... Category Not Found ! ";
            $retArr['tmpErrorStatus'] = $CatIdApiNode['tmpErrorStatus'];
            $retArr['mongo_curl']				= $CatIdApiNode['mongo_curl']; // for debugging test case
        	$retArr['categoryCount_curl']		= $CatIdApiNode['categoryCount_curl']; //  for debugging test case
        	$retArr['getparentcategory_curl']	= $CatIdApiNode['getparentcategory_curl']; //  for debugging test case
        	$retArr['CONST_DEALS_MONGO_API']	= $CatIdApiNode['CONST_DEALS_MONGO_API']; //  for debugging test case
            return json_encode($retArr);
        }
        else
        {
            $paramsArr['catid']     =   $CatIdApiNode['deals_categories']['catid'];
            $paramsArr['catname']   =   $CatIdApiNode['deals_categories']['catname'];
        }
        $retArr =   array();
        $curlParams = array();

        $strCurlParamsArr = array();
        $strCurlParamsArr['active_flag']        =   trim($paramsArr['active_flag']);
        $strCurlParamsArr['categoryid']         =   trim($paramsArr['catid']);
        $strCurlParamsArr['bid']                =   trim($paramsArr['bid']);
        $strCurlParamsArr['trace']              =   trim($paramsArr['trace']);
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/getjddealtemplates?categoryid='.$strCurlParamsArr['categoryid'].'&active_flag='.$strCurlParamsArr['active_flag'].'&bid='.$strCurlParamsArr['bid'].'&trace='.$strCurlParamsArr['trace'];
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'GET'; 
        $tmpres = Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($tmpres,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){         
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        
        return json_encode($retArr);
    }
    public function updateJdCustomTemplate(){
        
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send updatedby as updatedby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['entity_flag']) || (isset($paramsArr['entity_flag']) && $paramsArr['entity_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send entity_flag as entity_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['templateid']) || (isset($paramsArr['templateid']) && $paramsArr['templateid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send templateid as templateid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['bid']) || (isset($paramsArr['bid']) && $paramsArr['bid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send bid as bid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }

        $retArr =   array();
        $curlParams = array();

        $strCurlParamsArr = array();
        $strCurlParamsArr['active_flag']	=	trim($paramsArr['active_flag']);
        $strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
        $strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
        $strCurlParamsArr['entity_flag']	=	trim($paramsArr['entity_flag']);
        $strCurlParamsArr['templateid']		=	trim($paramsArr['templateid']);
        $strCurlParamsArr['bid']			=	trim($paramsArr['bid']);
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/updatejdcustomtemplate';
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'POST'; 
        $curlParams['postData']      =   $strCurlParamsArr; // POST
        $tmpres = Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($tmpres,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){         
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function updateCmDealStatus(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send active_flag as active_flag";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['activeflag']	=	trim($paramsArr['active_flag']);
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/updatecmdealstatus';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr;
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function updateCmDealInformation(){
		header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr	=	array();
		if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send offerid as offerid";
			return json_encode($retArr);
		}
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send docid as docid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send updatedby as updatedby";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send title as title";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send description as description";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send startdate as startdate";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send expirydate as expirydate";
			return json_encode($retArr);
		}
		$paramsArr['saving']    =   '';
        $paramsArr['price'] =   '';
		if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send applicable_on as applicable_on";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['identifier']) || (isset($paramsArr['identifier']) && $paramsArr['identifier'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send identifier as identifier";
			return json_encode($retArr);
		}
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
        {
            if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send card_type as card_type";
                return json_encode($retArr);
            }
            if(!isset($paramsArr['bank_name']) || (isset($paramsArr['bank_name']) && $paramsArr['bank_name'] == ''))
            {
	            $retArr['errorCode'] = 1;
	            $retArr['errorStatus'] = "please send bank_name as bank_name";
	            return json_encode($retArr);
	        }
        }
		if(!isset($paramsArr['storename']) || (isset($paramsArr['storename']) && $paramsArr['storename'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send storename as storename";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send parentid as parentid";
			return json_encode($retArr);
		}
		if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
			$retArr['errorCode'] = 1;
			$retArr['errorStatus'] = "please send server_city as server_city";
			return json_encode($retArr);
		}
        $retArr	=	array();
		$curlParams = array();

		$strCurlParamsArr = array();
		$strCurlParamsArr['offerid']		=	trim($paramsArr['offerid']);
		$strCurlParamsArr['docid']			=	trim($paramsArr['docid']);
		$strCurlParamsArr['updatedby']		=	trim($paramsArr['updatedby']);
		$strCurlParamsArr['title']			=	trim($paramsArr['title']);
		$strCurlParamsArr['description']	=	trim($paramsArr['description']);
		$strCurlParamsArr['startdate']		=	trim($paramsArr['startdate']);
		$strCurlParamsArr['expirydate']		=	trim($paramsArr['expirydate']);
		$strCurlParamsArr['saving']			=	trim($paramsArr['saving']);
		$strCurlParamsArr['price']			=	trim($paramsArr['price']);
		$strCurlParamsArr['applicable_on']	=	trim($paramsArr['applicable_on']);
		$strCurlParamsArr['identifier']		=	trim($paramsArr['identifier']);
		if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7))
        {
            $strCurlParamsArr['card_type']      =   trim($paramsArr['card_type']);
            if(isset($paramsArr['bank_name']) && $paramsArr['bank_name']!= ''){
        		$strCurlParamsArr['storename']      =   trim($paramsArr['bank_name']);
	        }else{
	        	$strCurlParamsArr['storename']      = 	"";
	        }
        }else{
        	$strCurlParamsArr['storename']      =   trim($paramsArr['storename']);
        }
		$curlParams['url'] 			= 	DEALS_RATING_API.'deals_services/updatecmdealinformation';
		$curlParams['formate'] 		= 	'basic';
		$curlParams['method'] 		= 	'POST'; 
        $curlParams['postData'] 		= 	$strCurlParamsArr;
        $singleCheckTmp	=	Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function fetchDealAutoSuggest(){
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send data_city as data_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['module']) || (isset($paramsArr['module']) && $paramsArr['module'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send module as module";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['search']) || (isset($paramsArr['searchText']) && $paramsArr['search'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send search Text as search";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['entity_flag']) || (isset($paramsArr['entity_flag']) && $paramsArr['entity_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send entity_flag as entity_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['identifier']) || (isset($paramsArr['identifier']) && $paramsArr['identifier'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send identifier as identifier";
            return json_encode($retArr);
        } 
        
        $CatIdApiNode_tmp = DealInfo_Model::getNatCatIdFrmParentId($paramsArr['parentid'],$paramsArr['data_city'],$paramsArr['module']);
        $CatIdApiNode = json_decode($CatIdApiNode_tmp,true);
        if($CatIdApiNode['errorCode'] != 0){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Category Issue... Category Not Found ! ";
            $retArr['tmpErrorStatus'] = $CatIdApiNode['tmpErrorStatus'];
            $retArr['mongo_curl']               = $CatIdApiNode['mongo_curl']; // for debugging test case
            $retArr['categoryCount_curl']       = $CatIdApiNode['categoryCount_curl']; //  for debugging test case
            $retArr['getparentcategory_curl']   = $CatIdApiNode['getparentcategory_curl']; //  for debugging test case
            $retArr['CONST_DEALS_MONGO_API']    = $CatIdApiNode['CONST_DEALS_MONGO_API']; //  for debugging test case
            return json_encode($retArr);
        }else{
            $paramsArr['catid']     =   $CatIdApiNode['deals_categories']['catid'];
            $paramsArr['catname']   =   $CatIdApiNode['deals_categories']['catname'];
        }
        $retArr =   array();
        $curlParams = array();
        $strCurlParamsArr = array();
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['search']         =   trim($paramsArr['search']);
        $strCurlParamsArr['entity_flag']    =   trim($paramsArr['entity_flag']);
        $strCurlParamsArr['active_flag']    =   trim($paramsArr['active_flag']);
        $strCurlParamsArr['identifier']     =   trim($paramsArr['identifier']);
        $strCurlParamsArr['categoryid']     =   trim($paramsArr['catid']);

        $curlParams['url']          =   DEALS_RATING_API.'deals_services/coupontemplateautosuggest'. "?" . http_build_query($strCurlParamsArr);
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'GET';  
        $singleCheck    =   json_decode(Utility::curlCall($curlParams),TRUE);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){         
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        
        return json_encode($retArr);
    }
    public function addGenioDeal(){
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send title as title";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send description as description";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send startdate as startdate";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send expirydate as expirydate";
            return json_encode($retArr);
        }
        $paramsArr['saving']    =   '';
        $paramsArr['price'] =   '';
        if(!isset($paramsArr['createdby']) || (isset($paramsArr['createdby']) && $paramsArr['createdby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send createdby as createdby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['data_city']) || (isset($paramsArr['data_city']) && $paramsArr['data_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send data_city as data_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send applicable_on as applicable_on";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['city']) || (isset($paramsArr['city']) && $paramsArr['city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send city as city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['source']) || (isset($paramsArr['source']) && $paramsArr['source'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send source as source";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['cityid']) || (isset($paramsArr['cityid']) && $paramsArr['cityid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send cityid as cityid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['storename']) || (isset($paramsArr['storename']) && $paramsArr['storename'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send storename as storename";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['highlights']) || (isset($paramsArr['highlights']) && $paramsArr['highlights'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send highlights as highlights";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['terms']) || (isset($paramsArr['terms']) && $paramsArr['terms'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send terms as terms";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['identifier']) || (isset($paramsArr['identifier']) && $paramsArr['identifier'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send identifier as identifier";
            return json_encode($retArr);
        }
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send card_type as card_type";
                return json_encode($retArr);
            }
        }
        if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            if(!isset($paramsArr['products']) || (isset($paramsArr['products']) && $paramsArr['products'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send products as products";
                return json_encode($retArr);
            }
        }
        if(!isset($paramsArr['module']) || (isset($paramsArr['module']) && $paramsArr['module'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send module as module";
            return json_encode($retArr);
        }
        $CatIdApiNode_tmp = DealInfo_Model::getNatCatIdFrmParentId($paramsArr['parentid'],$paramsArr['data_city'],$paramsArr['module']);
        $CatIdApiNode = json_decode($CatIdApiNode_tmp,true);
        if($CatIdApiNode['errorCode'] != 0)
        {
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Category Issue... Category Not Found ! ";
            $retArr['tmpErrorStatus'] = $CatIdApiNode['tmpErrorStatus'];
            $retArr['mongo_curl']               = $CatIdApiNode['mongo_curl']; // for debugging test case
            $retArr['categoryCount_curl']       = $CatIdApiNode['categoryCount_curl']; //  for debugging test case
            $retArr['getparentcategory_curl']   = $CatIdApiNode['getparentcategory_curl']; //  for debugging test case
            $retArr['CONST_DEALS_MONGO_API']    = $CatIdApiNode['CONST_DEALS_MONGO_API']; //  for debugging test case
            return json_encode($retArr);
        }
        else
        {
            $paramsArr['catid']     =   $CatIdApiNode['deals_categories']['catid'];
            $paramsArr['catname']   =   $CatIdApiNode['deals_categories']['catname'];
        }
        $retArr =   array();
        $curlParams = array();
        $strCurlParamsArr = array();
        $strCurlParamsArr['title']          =   trim($paramsArr['title']);
        $strCurlParamsArr['description']    =   trim($paramsArr['description']);
        $strCurlParamsArr['startdate']      =   trim($paramsArr['startdate']);
        $strCurlParamsArr['expirydate']     =   trim($paramsArr['expirydate']);
        $strCurlParamsArr['saving']         =   trim($paramsArr['saving']);
        $strCurlParamsArr['price']          =   trim($paramsArr['price']);
        $strCurlParamsArr['createdby']      =   trim($paramsArr['createdby']);
        $strCurlParamsArr['catid']          =   trim($paramsArr['catid']);
        $strCurlParamsArr['catname']        =   trim($paramsArr['catname']);
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['applicable_on']  =   $paramsArr['applicable_on'];
        $strCurlParamsArr['city']           =   trim($paramsArr['city']);
        $strCurlParamsArr['active_flag']    =   trim($paramsArr['active_flag']);
        $strCurlParamsArr['source']         =   trim($paramsArr['source']);
        $strCurlParamsArr['cityid']         =   trim($paramsArr['cityid']);
        $strCurlParamsArr['storename']      =   trim($paramsArr['storename']);
        $strCurlParamsArr['identifier']     =   trim($paramsArr['identifier']);
        $strCurlParamsArr['highlights']     =   json_encode($paramsArr['highlights']);
        $strCurlParamsArr['terms']          =   json_encode($paramsArr['terms']);
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            $strCurlParamsArr['card_type']  =   json_encode($paramsArr['card_type']);
        }else if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            $strCurlParamsArr['products']  =   json_encode($paramsArr['products']);
        }
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/addgeniodeal';
        // $final = $curlParams['url'] . "?" . http_build_query($strCurlParamsArr);
        // print_r($final);die;
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'POST'; 
        $curlParams['postData']     =   $strCurlParamsArr; // POST      
        $singleCheck    =   json_decode(Utility::curlCall($curlParams),TRUE);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){         
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        
        return json_encode($retArr);
    }
    public function updateGenioDeal(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send offerid as offerid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send title as title";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send description as description";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send startdate as startdate";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send expirydate as expirydate";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send applicable_on as applicable_on";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send updatedby as updatedby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send card_type as card_type";
                return json_encode($retArr);
            }
        }
        if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            if(!isset($paramsArr['products']) || (isset($paramsArr['products']) && $paramsArr['products'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send products as products";
                return json_encode($retArr);
            }
        }
        $paramsArr['saving']    =   '';
        $paramsArr['price'] =   '';
        $retArr =   array();
        $curlParams = array();
        $strCurlParamsArr = array();
        $strCurlParamsArr['offerid']        =   trim($paramsArr['offerid']);
        $strCurlParamsArr['title']          =   trim($paramsArr['title']);
        $strCurlParamsArr['description']    =   trim($paramsArr['description']);
        $strCurlParamsArr['updatedby']      =   trim($paramsArr['updatedby']);
        $strCurlParamsArr['startdate']      =   trim($paramsArr['startdate']);
        $strCurlParamsArr['expirydate']     =   trim($paramsArr['expirydate']);
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['saving']         =   trim($paramsArr['saving']);
        $strCurlParamsArr['price']          =   trim($paramsArr['price']);
        $strCurlParamsArr['applicable_on']  =   $paramsArr['applicable_on'];
        $strCurlParamsArr['highlights']     =   json_encode($paramsArr['highlights']);
        $strCurlParamsArr['terms']          =   json_encode($paramsArr['terms']);
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            $strCurlParamsArr['card_type']          =   json_encode($paramsArr['card_type']);
        }else if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            $strCurlParamsArr['products']  =   json_encode($paramsArr['products']);
        }
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/updategeniodeal';
        // $final = $curlParams['url'] . "?" . http_build_query($strCurlParamsArr);
        // print_r($final);die;
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'POST'; 
        $curlParams['postData']         =   $strCurlParamsArr; // POST
        $singleCheckTmp =   Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function updatePublishedDeal(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['parentid']) || (isset($paramsArr['parentid']) && $paramsArr['parentid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send parentid as parentid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send offerid as offerid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['title']) || (isset($paramsArr['title']) && $paramsArr['title'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send title as title";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['description']) || (isset($paramsArr['description']) && $paramsArr['description'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send description as description";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['startdate']) || (isset($paramsArr['startdate']) && $paramsArr['startdate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send startdate as startdate";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['expirydate']) || (isset($paramsArr['expirydate']) && $paramsArr['expirydate'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send expirydate as expirydate";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['applicable_on']) || (isset($paramsArr['applicable_on']) && $paramsArr['applicable_on'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send applicable_on as applicable_on";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send updatedby as updatedby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            if(!isset($paramsArr['card_type']) || (isset($paramsArr['card_type']) && $paramsArr['card_type'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send card_type as card_type";
                return json_encode($retArr);
            }
        }
        if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            if(!isset($paramsArr['products']) || (isset($paramsArr['products']) && $paramsArr['products'] == '')){
                $retArr['errorCode'] = 1;
                $retArr['errorStatus'] = "please send products as products";
                return json_encode($retArr);
            }
        }
        $paramsArr['saving']    =   '';
        $paramsArr['price'] =   '';
        $retArr =   array();
        $curlParams = array();
        $strCurlParamsArr = array();
        $strCurlParamsArr['offerid']        =   trim($paramsArr['offerid']);
        $strCurlParamsArr['title']          =   trim($paramsArr['title']);
        $strCurlParamsArr['description']    =   trim($paramsArr['description']);
        $strCurlParamsArr['updatedby']      =   trim($paramsArr['updatedby']);
        $strCurlParamsArr['startdate']      =   trim($paramsArr['startdate']);
        $strCurlParamsArr['expirydate']     =   trim($paramsArr['expirydate']);
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['saving']         =   trim($paramsArr['saving']);
        $strCurlParamsArr['price']          =   trim($paramsArr['price']);
        $strCurlParamsArr['applicable_on']  =   $paramsArr['applicable_on'];
        $strCurlParamsArr['highlights']     =   json_encode($paramsArr['highlights']);
        $strCurlParamsArr['terms']          =   json_encode($paramsArr['terms']);
        if(($paramsArr['identifier'] == '7')||($paramsArr['identifier'] == 7)){
            $strCurlParamsArr['card_type']          =   json_encode($paramsArr['card_type']);
        }else if(($paramsArr['identifier'] == '8')||($paramsArr['identifier'] == 8)){
            $strCurlParamsArr['products']  =   json_encode($paramsArr['products']);
        }
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/updategeniopublisheddeal';
        // $final = $curlParams['url'] . "?" . http_build_query($strCurlParamsArr);
        // print_r($final);die;
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'POST'; 
        $curlParams['postData']         =   $strCurlParamsArr; // POST
        $singleCheckTmp =   Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);
    }
    public function updatePublishDealStatus(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        if(!isset($paramsArr['offerid']) || (isset($paramsArr['offerid']) && $paramsArr['offerid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send offerid as offerid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['docid']) || (isset($paramsArr['docid']) && $paramsArr['docid'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send docid as docid";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['updatedby']) || (isset($paramsArr['updatedby']) && $paramsArr['updatedby'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send updatedby as updatedby";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['active_flag']) || (isset($paramsArr['active_flag']) && $paramsArr['active_flag'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send active_flag as active_flag";
            return json_encode($retArr);
        }
        if(!isset($paramsArr['server_city']) || (isset($paramsArr['server_city']) && $paramsArr['server_city'] == '')){
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "please send server_city as server_city";
            return json_encode($retArr);
        }
        $retArr =   array();
        $curlParams = array();

        $strCurlParamsArr = array();
        $strCurlParamsArr['offerid']        =   trim($paramsArr['offerid']);
        $strCurlParamsArr['docid']          =   trim($paramsArr['docid']);
        $strCurlParamsArr['updatedby']      =   trim($paramsArr['updatedby']);
        $strCurlParamsArr['active_flag']    =   trim($paramsArr['active_flag']);
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/updategeniopublisheddealstatus';
        // $final = $curlParams['url'] . "?" . http_build_query($strCurlParamsArr);
        // print_r($final);die;
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'POST'; 
        $curlParams['postData']         =   $strCurlParamsArr; // POST
        $singleCheckTmp =   Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);

    }
    public function getDealOfferType(){
        header('Content-Type: application/json');
        $reqArr = array_merge($_GET,$_POST);
        $paramsArr = array();
        if(isset($reqArr['urlFlag'])){
            $paramsArr = $reqArr;
        }else{
            $paramsArr = json_decode(file_get_contents('php://input'),true);
        }
        $retArr =   array();
        $curlParams = array();

        $strCurlParamsArr = array();
        $curlParams['url']          =   DEALS_RATING_API.'deals_services/getoffertype';
        $curlParams['formate']      =   'basic';
        $curlParams['method']       =   'GET'; 
        // $curlParams['postData']         =   $strCurlParamsArr; // POST
        $singleCheckTmp =   Utility::curlCall($curlParams);
        $singleCheck    =   json_decode($singleCheckTmp,true);
        if(isset($singleCheck['errors']['code']) && ($singleCheck['errors']['code'] == 0)){ 
            $retArr['result'] = $singleCheck['results'];
            $retArr['errorCode'] = 0;
            $retArr['errorStatus'] = "success";
        }
        else{
            $retArr['errorCode'] = 1;
            $retArr['errorStatus'] = "Error from Deal API ! ";
            $retArr['error'] = $singleCheck['errors'];          
        }
        return json_encode($retArr);

    }
    public function generateAccesscode(){
        header('Content-Type: application/json');
        $params = array();
        $paramsArr = array_merge($_GET,$_POST);
        if( isset($paramsArr['urlFlag']) ){
            $params = $paramsArr;
        }else{
            $params = json_decode(file_get_contents('php://input'), true);
        }        
        $respArr = array();
        if( empty($params['ucode']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass employee-code as ucode';
            return json_encode($respArr);
        }
        if( empty($params['parentid']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass parentid';
            return json_encode($respArr);
        }
        if( empty($params['data_city']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass data_city';
            return json_encode($respArr);
        }
        if( empty($params['module']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass module';
            return json_encode($respArr);
        }
        if( empty($params['link_type']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass link_type';
            return json_encode($respArr);
        }
        $accessCode = $this->accessCodeGenNew(); // Function to generate Accesscode
        $checkAccesscode_params = array();
        $checkAccesscode_params['access_code'] = $accessCode;
        $checkAccesscode_resp = $this->checkAccesscode($checkAccesscode_params); // Function to check if the generated Accesscode already present in system
        $num_of_attempts_exhaus = false;
        if($checkAccesscode_resp['access_code_pres']){
            for($i = 1; $i <= 5; $i++){
                $accessCode_rechk = '';
                $accessCode_rechk = $this->accessCodeGenNew();
                $checkAccesscode_params = array();
                $checkAccesscode_params['access_code'] = $accessCode_rechk;
                $checkAccesscode_resp_new = $this->checkAccesscode($checkAccesscode_params);
                if(!$checkAccesscode_resp_new['access_code_pres']){
                    $insertAccesscode_params = array();
                    $insertAccesscode_params['parentid'] = $params['parentid'];
                    $insertAccesscode_params['data_city'] = $params['data_city'];
                    $insertAccesscode_params['ucode'] = $params['ucode'];
                    $insertAccesscode_params['module'] = $params['module'];
                    $insertAccesscode_params['link_type'] = $params['link_type'];
                    $insertAccesscode_params['access_code'] = $accessCode_rechk;
                    $insertAccesscode_resp = $this->insertAccesscode($insertAccesscode_params); // Function to insert the generated Accesscode in system
                    return json_encode($insertAccesscode_resp);
                    break;
                }
                if($i == 5){
                    $num_of_attempts_exhaus = true;
                }
            }
            if( $num_of_attempts_exhaus ){
                $respArr['errorCode'] = 1;
                $respArr['errorStatus'] = 'Duplicate accesscode found. Try again';
                $respArr['access_code'] = '';
                return json_encode($respArr);
            }
        }else{
            // insert function
            $insertAccesscode_params = array();
            $insertAccesscode_params['parentid'] = $params['parentid'];
            $insertAccesscode_params['data_city'] = $params['data_city'];
            $insertAccesscode_params['ucode'] = $params['ucode'];
            $insertAccesscode_params['module'] = $params['module'];
            $insertAccesscode_params['link_type'] = $params['link_type'];
            $insertAccesscode_params['access_code'] = $accessCode;
            $insertAccesscode_resp = $this->insertAccesscode($insertAccesscode_params); // Function to insert the generated Accesscode in system
            return json_encode($insertAccesscode_resp);
        }     
    }
    private function accessCodeGenNew() {
        $length = 16;
        $prefix = 'DEOF';

        $rnd_id = crypt(uniqid(rand(),1));

        //to remove any slashes that might have come
        $rnd_id = strip_tags(stripslashes($rnd_id));

        //Removing any . or / and reversing the string
        $rnd_id = str_replace(".","",$rnd_id);
        $rnd_id = strrev(str_replace("/","",$rnd_id));

        //finally take the first required length characters from the $rnd_id
        $accessCode = $prefix.substr($rnd_id,0,$length);
        return $accessCode;
    }
    
    // This function will return if the generated Accesscode already exists in the system
    // parameters paased are Accesscode    
    private function checkAccesscode( $params = array() ){
        $respArr = array();
        $dbObj_idc = new DB($this->db['db_idc_mumbai']);
        $get_access_code_rechk = "Select access_code from online_regis1.tbl_deals_offers_access_links where access_code = '".trim($params['access_code'])."' LIMIT 1";
        $con_get_access_code_rechk = $dbObj_idc->query($get_access_code_rechk);
        if( $con_get_access_code_rechk && $dbObj_idc->numRows($con_get_access_code_rechk) > 0 ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Accesscode already exists';
            $respArr['access_code'] = trim($params['access_code']);
            $respArr['access_code_pres'] = true;
        }else{
            $respArr['errorCode'] = 0;
            $respArr['errorStatus'] = 'Accesscode is unique';
            $respArr['access_code'] = trim($params['access_code']);
            $respArr['access_code_pres'] = false;
        }
        return $respArr;
    }
    // This function will insert the Accesscode generated in table. Post check if generated Accesscode is unique.
    private function insertAccesscode( $params = array() ){
        $respArr = array();
        $dbObj_idc = new DB($this->db['db_idc_mumbai']);
        $insrt_access_code_qr = "INSERT INTO online_regis1.tbl_deals_offers_access_links set access_code = '".trim($params['access_code'])."', parentid = '".$params['parentid']."', data_city = '".$params['data_city']."', empcode = '".$params['ucode']."', module = '".$params['module']."', link_type = '".$params['link_type']."', created_at = '".date("Y-m-d H:i:s")."' ";
        $con_insrt_access_code_qr = $dbObj_idc->query($insrt_access_code_qr);
        if( $con_insrt_access_code_qr ){
            $respArr['errorCode'] = 0;
            $respArr['errorStatus'] = 'Accesscode generated success.';
            $respArr['access_code'] = trim($params['access_code']);
        }else{
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Can not generate Accesscode please try again.';
            $respArr['access_code'] = '';
        }
        return $respArr;
    }
    public function getAccessCodeDetials(){
        header('Content-Type: application/json');
        $params = array();
        $paramsArr = array_merge($_GET,$_POST);
        if( isset($paramsArr['urlFlag']) ){
            $params = $paramsArr;
        }else{
            $params = json_decode(file_get_contents('php://input'), true);
        }  
        $responseArr = array();
        if( empty($params['accessCode']) ){
            $respArr['errorCode'] = 1;
            $respArr['errorStatus'] = 'Please pass accessCode as Access Code';
            return json_encode($responseArr);
        }
        $responseArr = array();
        $dbObj_idc   = new DB($this->db['db_idc_mumbai']);        
        $sel_qry     = "SELECT * FROM online_regis1.tbl_deals_offers_access_links WHERE access_code = '".$params['accessCode']."'";
        $sel_res     = $dbObj_idc->query($sel_qry);
        if($dbObj_idc->numRows($sel_res) > 0) {            
            $accessCodeData = $dbObj_idc->fetchData($sel_res);
            $responseArr['errorCode']   = '0';
            $responseArr['errorStatus'] = 'success';
            $responseArr['data']        = $accessCodeData;            
        }else{
            $responseArr['errorCode']   = '1';
            $responseArr['errorStatus'] = 'Invalid Data';       
        }
        return json_encode($responseArr);
    }

}