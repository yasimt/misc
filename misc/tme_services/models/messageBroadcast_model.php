<?php
class MessageBroadcast_Model extends Model {
	function __construct() {
        parent::__construct(); 
    }
    
  public function getEmpMessageDetails() {
			header('Content-Type: application/json');
			$params								=	json_decode(file_get_contents('php://input'),true);
			$currant_date						=	date('Y-m-d');
			$paramsArr							=	array();
			$retValemp							=	array();				
			$postArrayempinfo					=	array();
			$paramsArr['url'] 					= 	SSOINFO.'/api/getEmployee_xhr.php';
			$paramsArr['formate'] 				= 	'basic';
			$paramsArr['headerJson'] 			= 	'json';
			$paramsArr['method'] 				= 	'post';
			$postArrayempinfo['empcode']		=	 trim($params['EmpCode']);
			$postArrayempinfo['textSearch']		=	4;
			$postArrayempinfo['reseller_flag']	=	1;
			$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
			$paramsArr['postData'] 				= 	json_encode($postArrayempinfo);
			$retValemp 							= 	json_decode(Utility::curlCall2($paramsArr),true);
			if((count($retValemp)>0) && ($retValemp['errorcode'] == 0)){
				$fetchCty['allocId']			= 	$retValemp['data'][0]['team_type'];
				$fetchCty['city']				= 	$retValemp['data'][0]['city'];
			}
			$teamtype  				= 	'ALL';
			if($fetchCty['allocId']!=null){
				 if($fetchCty['allocId']=='OTH'){
					 $teamtype = 'Others';
				 }
				  if($fetchCty['allocId']=='BD'){
					 $teamtype = 'Bounce';
				 }
				  if($fetchCty['allocId']=='HD'){
					 $teamtype = 'Hot Data';
				 }
				  if($fetchCty['allocId']=='O'){
					 $teamtype = 'Online';
				 }
				  if($fetchCty['allocId']=='RD'){
					 $teamtype = 'Retention';
				 }
				  if($fetchCty['allocId']=='BE'){
					 $teamtype = 'Revival';
				 }
				  if($fetchCty['allocId']=='S'){
					 $teamtype = 'Super';
				 }
				 if($fetchCty['allocId']=='SJ'){
					 $teamtype = 'Super Cats';
				 }
			}
			  $url				=	KNOWLEDGE_APICALL."knowledge/fetchBroadCastData?title=&limit=200&page=1&emp_id=".$params['EmpCode']."&emp_type=TME&team_type=$teamtype&tagged_city=".$fetchCty['city'];
			$curlParams					= 	array();
			$curlParams['url']			= 	$url;
			$curlParams['formate'] 		=  	'basic';
			$singleCheck				=	Utility::curlCall2($curlParams);	
			$res						=	json_decode($singleCheck,true);
			if(empty($res)){
				 $retArr['errorCode']    =   1;
            $retArr['errorMsg']     =   "Data Not Found";
            return json_encode($retArr);
			}
			$i=0;
        if($res['error']['code']==0) {
			foreach($res['result']['data'] as $key=>$index){
					$retArr['data'][$i]['title']= $index['title'];
					$retArr['data'][$i]['message']= $index['message'];
					if($index['mandatory']==1){
						if($index['flag']==0){
							$retArr['data'][$i]['flag']=1;
						}else if($index['flag']==1){
							$retArr['data'][$i]['flag']=0;
						}
					}else if($index['mandatory']==0){
						$retArr['data'][$i]['flag']=0;
					}
					$retArr['data'][$i]['mandatory']= $index['mandatory'];
					$retArr['data'][$i]['media_path']= $index['media_path'];
					if($index['media_show']==""){
						$retArr['data'][$i]['media_show']="nomedia";
					}
					else{
						$retArr['data'][$i]['media_show']= $index['media_show'];
					}
					if($index['media_type']==""){
						$retArr['data'][$i]['media_type']="nomedia";
					}
					else{
						$retArr['data'][$i]['media_type']= $index['media_type'];
					}
					if(isset($index['entry_date'])){
						$date=$index['entry_date'];
						$retArr['data'][$i]['msg_time']= date('d-m-Y | h:i A', strtotime($date));
					}
					if(is_array($index['_id'])) {
						$retArr['data'][$i]['media_id']= $index['_id']['$oid'];
					}
					else {
						$retArr['data'][$i]['media_id']= $index['_id'];
					}
					if(isset($index['broadcast_id'])){
						$data_all['data'][$i]['media_id']= $index['broadcast_id'];
					}
					$retArr['data'][$i]['senderId']="justdial";
					$i++;
				
			}
				$retArr['total'] = $i;
            $retArr['errorCode']    =   0;
            $retArr['errorMsg']     =   "Data Found Successfully";
        } else {
            $retArr['errorCode']    =   1;
            $retArr['errorMsg']     =   "Data Not Found";
        }
        return json_encode($retArr);
	}
	public function getEmpMessageUpdates() {
		header('Content-Type: application/json');
        $params     =   json_decode(file_get_contents('php://input'),true);

            $url				=	KNOWLEDGE_APICALL."knowledge/insertBMReadData?id=".urlencode($params['id'])."&read_emp=".urlencode(json_encode($params['emp']));
			$curlParams					= 	array();
			$curlParams['url']			= 	$url;
			$curlParams['formate'] 		=  	'basic';
			$singleCheck				=	Utility::curlCall2($curlParams);	
			$res						=	json_decode($singleCheck,true);
			if(empty($res)){
				 $retArr['errorCode']    =   1;
            $retArr['errorMsg']     =   "Data Not Found";
            return json_encode($retArr);
			}
			 return json_encode($res);
	}
	
}
?>
