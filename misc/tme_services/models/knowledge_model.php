<?php 
 class knowledge_Model extends Model {
		function __construct() {
			parent::__construct(); 
		}
 
		  
		public function getalldata() {
				$params	=json_decode(file_get_contents('php://input'),true);
				$location='';
				$i=0;
				if(isset($params['location'])){
					$location=$params['location'];
					if($location=='Ahmedabad'){
						$location='Ahemdabad';
					}
				}
				$page=$params['page'];
				$searchpara=$params['searchpara'];
				$emptype=$params['emptype'];
                                $emptype = 'TME';
				$search_title='';
				if(isset($params['search_title'])==true){
					$search_title=urlencode($params['search_title']);
				}
				$userid=$params['userid'];
				$team=$params['teamtype'];
				
				$todayDate	=	date("Y-m-d");
				$sevenDaysPriorDate	= date("Y-m-d",strtotime("-7 days",strtotime($todayDate)));
				
				
				$url	=	KNOWLEDGE_API."knowledge/fetchEmpWiseData?team_type=$team&media_type=$searchpara&search_title=$search_title&location=$location&emptype=TME&userid=$userid&page=$page&limit=20";
				$curlParams					= 	array();
					$curlParams['url']			= 	$url;
					$curlParams['formate'] 		=  	'basic';
					$singleCheck				=	Utility::curlCall2($curlParams);	
					$res						=	json_decode($singleCheck,true);
					if(empty($res)){
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						return json_encode($retArr);
				}
					 if($res['error']['code'] == 0){
						 foreach($res['result']['data'] as $key=>$index){
							$data_all['data'][$i]['title']= $index['title'];
							$data_all['data'][$i]['description']= $index['description'];
							$data_all['data'][$i]['length_desc']= 100;
							$data_all['data'][$i]['display_path']= $index['display_pic_path'];
							$data_all['data'][$i]['media_type']= $index['media_type'];
							
							
							 $uploaded_date = str_replace('/', '-', $index['uploadedon_show']);
							 $uploaded_date	=	date("Y-m-d",strtotime($uploaded_date));
							
							
							if(isset($index['isNew'])==true){
								$data_all['data'][$i]['isNew']	=$index['isNew'];
									}
									else if(isset($index['isNew'])==false){
										$data_all['data'][$i]['isNew']	=0;
									}
							if(isset($index['record_of'])==true && $index['record_of']!=''){
								$data_all['data'][$i]['record_of']= $index['record_of'];
							}
							else if(isset($index['record_of'])==true && $index['record_of']==''){
								$data_all['data'][$i]['record_of']= 'NA';
							}
							else if(isset($index['record_of'])==false){
								$data_all['data'][$i]['record_of']= 'NA';
							}
							if(isset($index['record_city'])==true && $index['record_city']!=''){
								$data_all['data'][$i]['record_city']= $index['record_city'];
							}
							else if(isset($index['record_city'])==true && $index['record_city']==''){
								$data_all['data'][$i]['record_city']= 'NA';
							}
							else if(isset($index['record_city'])==false){
								$data_all['data'][$i]['record_city']= 'NA';
							}
							if(isset($index['uploadedon_show'])){
								$data_all['data'][$i]['postedon']= $index['uploadedon_show'];
							}
							if(isset($index['display_pic'])){
								$data_all['data'][$i]['display_pic']= $index['display_pic'];
							}
							$data_all['data'][$i]['media_path']= $index['media_path'];
							$data_all['data'][$i]['postedby']= $index['postedby'];
							if(isset($index['admin_city'])){
								$data_all['data'][$i]['city']= $index['admin_city'];
							}
							if(isset($index['media_show'])){
								$data_all['data'][$i]['media_show']= $index['media_show'];
							}
                                                        if(is_array($index['media_id'])) {
                                                            $data_all['data'][$i]['media_id']= $index['media_id']['$oid'];
                                                        }
                                                        else {
                                                            $data_all['data'][$i]['media_id']= $index['media_id'];
                                                        }
							$i++;
						}
						$retArr = $data_all;
						$retArr['errorCode'] = 0;
						$retArr['errorMsg'] = "data found";
						$retArr['total'] = $res['result']['total_count'];
					
						}else{
					
						$retArr = $res;	
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						}
						return json_encode($retArr);
				
			}
		
			public function fetchall_autosuggest_megenio() {
				$params	=json_decode(file_get_contents('php://input'),true);
				$location='';
				$y=0;
				if(isset($params['location'])){
					$location=$params['location'];
					if($location=='Ahmedabad'){
						$location='Ahemdabad';
					}
				}
				$searchpara=$params['searchpara'];
				$term=urlencode($params['term']);
				$emptype=$params['emptype'];
				$userid=$params['userid'];
				$team=$params['teamtype'];
				$url				=	KNOWLEDGE_API."knowledge/fetchallAutoSuggestMEGenio?team_type=$team&location=$location&media_type=$searchpara&emptype=TME&userid=$userid&term=$term";
					$curlParams					= 	array();
					$curlParams['url']			= 	$url;
					$curlParams['formate'] 		=  	'basic';
					$singleCheck				=	Utility::curlCall2($curlParams);	
					$res						=	json_decode($singleCheck,true);
					if(empty($res)){
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						return json_encode($retArr);
				}
					 if($res['error']['code'] == 0){
						 foreach($res['result']['data'] as $key=>$index){
							$retArr['data'][$y]['title']	=	$index['title'];
							$y++;
						}
						$retArr = $retArr;
						$retArr['errorCode'] = 0;
						$retArr['errorMsg'] = "data found";
						}else{
						$retArr['APIresp'] = $res;	
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						}
						return json_encode($retArr);
			}
			public function getalldata_mandatory() {
				$params	=json_decode(file_get_contents('php://input'),true);
				$location='';
				$i=0;
				if(isset($params['location'])){
					$location=$params['location'];
					if($location=='Ahmedabad'){
						$location='Ahemdabad';
					}
				}
				//print_r($params);
				$searchpara=$params['searchpara'];
				$emptype=$params['emptype'];
				$search_title='';
				$team=$params['teamtype'];
				if(isset($params['search_title'])==true){
					$search_title=urlencode($params['search_title']);
				}
				$userid=$params['userid'];
				$mediaid=$params['mediaid'];
				$url	=	KNOWLEDGE_API."knowledge/fetchEmpWiseData?team_type=$team&media_type=$searchpara&search_title=$search_title&emptype=TME&userid=$userid&location=$location&page=1&limit=999";
				$curlParams					= 	array();
					$curlParams['url']			= 	$url;
					$curlParams['formate'] 		=  	'basic';
					$singleCheck				=	Utility::curlCall2($curlParams);	
					$res						=	json_decode($singleCheck,true);
					if(empty($res)){
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						return json_encode($retArr);
				}
					
				$todayDate	=	date("Y-m-d");
				$sevenDaysPriorDate	= date("Y-m-d",strtotime("-7 days",strtotime($todayDate)));

					 if($res['error']['code'] == 0){
						 $mandatorycount=0;
						 foreach($res['result']['data'] as $key=>$index){
							  if(is_array($index['media_id'])) {
									$media_array= $index['media_id']['$oid'];
								}
								else {
									$media__array= $index['media_id'];
								}
							 if($index['mandatory']==1 && in_array($media__array, $mediaid)){
								$data_all['data'][$i]['title']= $index['title'];
								$data_all['data'][$i]['description']= $index['description'];
								$data_all['data'][$i]['length_desc']= 100;
								$data_all['data'][$i]['display_path']= $index['display_pic_path'];
								$data_all['data'][$i]['media_type']= $index['media_type'];
								$data_all['data'][$i]['mandatory']= $index['mandatory'];
									
								$uploaded_date = str_replace('/', '-', $index['uploadedon_show']);
								 $uploaded_date	=	date("Y-m-d",strtotime($uploaded_date));
								
								
								if(isset($index['isNew'])==true){
								$data_all['data'][$i]['isNew']	=$index['isNew'];
									}
									else if(isset($index['isNew'])==false){
										$data_all['data'][$i]['isNew']	=0;
									}
								
								if(isset($index['record_of'])==true && $index['record_of']!=''){
									$data_all['data'][$i]['record_of']= $index['record_of'];
								}
								else if(isset($index['record_of'])==true && $index['record_of']==''){
									$data_all['data'][$i]['record_of']= 'NA';
								}
								else if(isset($index['record_of'])==false){
									$data_all['data'][$i]['record_of']= 'NA';
								}
								if(isset($index['record_city'])==true && $index['record_city']!=''){
									$data_all['data'][$i]['record_city']= $index['record_city'];
								}
								else if(isset($index['record_city'])==true && $index['record_city']==''){
									$data_all['data'][$i]['record_city']= 'NA';
								}
								else if(isset($index['record_city'])==false){
									$data_all['data'][$i]['record_city']= 'NA';
								}
								
								if(isset($index['uploadedon_show'])){
									$data_all['data'][$i]['postedon']= $index['uploadedon_show'];
								}
								if(isset($index['display_pic'])){
									$data_all['data'][$i]['display_pic']= $index['display_pic'];
								}
								$data_all['data'][$i]['media_path']= $index['media_path'];
								$data_all['data'][$i]['postedby']= $index['postedby'];
								if(isset($index['admin_city'])){
									$data_all['data'][$i]['city']= $index['admin_city'];
								}
								if(isset($index['media_show'])){
									$data_all['data'][$i]['media_show']= $index['media_show'];
								}
								if(is_array($index['media_id'])) {
									$data_all['data'][$i]['media_id']= $index['media_id']['$oid'];
								}
								else {
									$data_all['data'][$i]['media_id']= $index['media_id'];
								}
								$i++;
								$mandatorycount++;
							}
						}
						if($mandatorycount==0){
							$retArr['errorCode'] = 1;
							$retArr['errorMsg'] = "data not found";
							$retArr['mandatorycount']= $mandatorycount;
							}
							else{
								$retArr = $data_all;
								$retArr['errorCode'] = 0;
								$retArr['errorMsg'] = "data found";
								$retArr['total'] = $res['result']['total_count'];
								$retArr['mandatorycount']= $mandatorycount;
							}
						}else{
					
						$retArr = $res;	
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						$retArr['mandatorycount']= $mandatorycount;
						}
						return json_encode($retArr);
			}
			public function getalldata_mandatory_popup() {
				$date = new DateTime();
				$a = $date->getTimestamp();
				$uploadedon=date("Y-m-d", $a);
				$params	=json_decode(file_get_contents('php://input'),true);
				$userid=$params['userid'];
				$team=$params['teamtype'];
				  $url				=	KNOWLEDGE_API."knowledge/fetchTMEMandatoryLogs?team_type=$team&employee_id=$userid&log_entry_date=".urlencode($uploadedon)."&module_type=TME";
					$curlParams					= 	array();
					$curlParams['url']			= 	$url;
					$curlParams['formate'] 		=  	'basic';
					$singleCheck				=	Utility::curlCall2($curlParams);	
					$res						=	json_decode($singleCheck,true);
					if(empty($res)){
						$retArr['errorCode'] = 1;
						$retArr['errorMsg'] = "data not found";
						return json_encode($retArr);
				}
					return json_encode($res);
			}
			
			public function teamtype() {
				header('Content-Type: application/json');
				$params			=	json_decode(file_get_contents('php://input'),true);
				$getSSOData		=	$this->getSSOInfo($params['empcode']);
				if((count($getSSOData)>0) && ($getSSOData['errorcode'] == 0)){
					$res_arr['team']['allocid'] 		= 	$getSSOData['data'][0]['team_type'];
					$res_arr['errorCode']				=	0;
					$res_arr['errorStatus']				=	'SSO Data FOUND';
				}else{
					$res_arr['errorCode']	= $getSSOData['errorcode'];	
					$res_arr['errorStatus']	= $getSSOData['reportStatus'];
				}
				return json_encode($res_arr);
			}
			
			public function getSSOInfo($empcode){
				$paramsArr							=	array();
				$retValemp							=	array();				
				$postArrayempinfo					=	array();
				$paramsArr['url'] 					= 	SSOINFO.'/api/getEmployee_xhr.php';
				$paramsArr['formate'] 				= 	'basic';
				$paramsArr['headerJson'] 			= 	'json';
				$paramsArr['method'] 				= 	'post';
				$postArrayempinfo['empcode']		=	 trim($empcode);
				$postArrayempinfo['textSearch']		=	4;
				$postArrayempinfo['reseller_flag']	=	1;
				$paramsArr['auth_token']			= 	md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
				$paramsArr['postData'] 				= 	json_encode($postArrayempinfo);
				$retValemp 							= 	json_decode(Utility::curlCall2($paramsArr),true);
				return $retValemp;
			}
			
	}

   ?>
   
