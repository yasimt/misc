<?php


class googleEmail extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	function __construct($params)
	{		
		$this->params = $params;		
		

		
		/* Code for companymasterclass logic starts */
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}
		$result_msg_arr=array();
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 
		if($this->action!='4'){
		if(trim($this->params['parentid']) == "")
		{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Parentid Missing";
				echo json_encode($result_msg_arr);exit;
		}
		else
			$this->parentid  = $this->params['parentid']; 
		}
		
		
		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 

		if(trim($this->params['data_city']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Data City Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->data_city  = $this->params['data_city']; 
		if($this->action!='4'){
		if(trim($this->params['version']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "version Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->version  = $this->params['version']; 
		}
		if(trim($this->params['tlds']) != "")
		{
			$this->tlds  = $this->params['tlds']; 
		}
		if(trim($this->params['no_of_emails']) != "")
		{
			$this->no_of_emails  = $this->params['no_of_emails']; 
		}
		else 
			$this->no_of_emails  = '';
		if(trim($this->params['email_type']) != "")
		{
			$this->email_type  = $this->params['email_type'];  
		} 
		else 
			$this->email_type  = ''; 
		
		
			

		if($this->action!='3' && $this->action!='4' && $this->action!='9'){ 

			

			if(trim($this->params['usercode']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Usercode Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->usercode  = $this->params['usercode']; 
			/*if($this->action!='4' && $this->action!='3'  && $this->action!='5' && $this->action!='6' && $this->action!='7' && $this->action!='9' && $this->action!='8'){ 
				if(trim($this->params['domainname']) == "")
				{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "domainname Missing";
					echo json_encode($result_msg_arr);exit;
				}
				else
					$this->domainname  = $this->params['domainname']; 
			}
*/
		}
		
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}
		$status=$this->setServers();
		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$this->meurl="http://ganeshrj.jdsoftware.com/megenio";
			$this->meurl="http://192.168.22.103:810";
			//$this->meurl="1192.168.11.237:810";
		}
		else{
			$this->meurl="http://192.168.22.103";  
			//$this->meurl="1192.168.11.237:810";   
		}
		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->conn_idc = $db[$data_city]['idc']['master'];
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			
			$this->conn_iro = $db[$data_city]['iro']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_iro = $db[$data_city]['iro']['master'];
			break;
			case 'me':
			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_iro = $db[$data_city]['iro']['master'];
			break;
			default:
			return -1;
			break;
		}

	}
	
	function curlCall($url,$data=null,$method='get'){
		global $genio_variables;
		global $dbarr;

		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$data=$data."&development=1";
				if($method=='get')
				$url=$url."&development=1";
		}
		else{
			$data=$data."&development=0";	
				if($method=='get')
			$url=$url."&development=0";
		}
		
			$ch = curl_init();        
	        curl_setopt($ch, CURLOPT_URL, $url);
	        if($method=='post'){
	        	
	        	curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	        }
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resultString = curl_exec($ch);

	        if(curl_error($ch)){
	        	$resultString=array();
	        	$resultString['status']='error';
	        	$resultString['msg']= curl_error($ch);
	        	$resultString=json_encode($resultString);  
	        }
	        curl_close($ch); 
		return $resultString;
	}
	function curlCallLive($url,$data=null,$method='get'){
		global $genio_variables;
		global $dbarr;

		if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
		{
			$data=$data."&development=0";
				if($method=='get')
				$url=$url."&development=0";
		}
		else{
			$data=$data."&development=0";	
				if($method=='get')
			$url=$url."&development=0";
		}
		
			$ch = curl_init();        
	        curl_setopt($ch, CURLOPT_URL, $url);
	        if($method=='post'){
	        	
	        	curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	        }
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$resultString = curl_exec($ch);
	        curl_close($ch); 
		return $resultString;
	}
	function mysql_real_escape_custom($string){
		
		$con = mysql_connect($this->conn_idc[0], $this->conn_idc[1], $this->conn_idc[2]) ;
		if(!$con){
			return $string;
		}
		$escapedstring=mysql_real_escape_string($string);
		return $escapedstring;

	}

	function insertIntoFailureLog($step,$response){
		 	$ins_log_sql = "INSERT INTO online_regis1.omni_website_creation_failure set
			 					parentid='".$this->parentid."',
			 					data_city   ='".$this->data_city_cm."',
			 					version  	= '".$this->version."',
			 					step  		= '".$step."',
			 					status_flag  	= '0',
			 					count_flag  		= '0',
			 					api_response  	= '".$this->mysql_real_escape_custom($response)."'
			 					ON DUPLICATE KEY UPDATE
			 					step  		= '".$step."',
			 					status_flag  	= '0',
			 					count_flag  		= '0',
			 					api_response  	= '".$this->mysql_real_escape_custom($response)."'";

			$res_log_sql = parent::execQuery($ins_log_sql, $this->conn_idc);
			if($step<7){
			$updatecons="update online_regis1.tbl_omni_details_consolidated set website_creation_status='fail'
			where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'"; 
			$updateconsres = parent::execQuery($updatecons, $this->conn_idc);
			}
			else if($step==7){
				$updatecons="update online_regis1.tbl_omni_details_consolidated set email_creation_status='fail'
				where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'"; 
				$updateconsres = parent::execQuery($updatecons, $this->conn_idc);
			}
 

		 	$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Some Error Occured";
			if($step!=7) 
			echo  json_encode($result_msg_arr);

	}
 
	function registerGoogleSuite(){

		$selectsql="select companyname,city,pincode,state,email,mobile_feedback,mobile,full_address,contact_person from  db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		$selectsqlres = parent::execQuery($selectsql, $this->conn_main);
		$domainname='';
		$companyname='';
		$full_address='';
		$city='';
		$pincode='';
		$contact_person='';
		
		if($selectsqlres && mysql_num_rows($selectsqlres)>0)
 		{
	 		while($selectsqlrow=mysql_fetch_assoc($selectsqlres))
				{
					
					$mobile=$selectsqlrow['mobile'];
					$email=$selectsqlrow['email'];
					$companyname=$selectsqlrow['companyname'];
					$contact_person=$selectsqlrow['contact_person'];
					$city=$selectsqlrow['city'];
					$pincode=$selectsqlrow['pincode'];
					$full_address=substr($selectsqlrow['full_address'],0,60);
					$state=$selectsqlrow['state'];
				}
		}

		$full_address = strip_tags( $full_address);
		$full_address = html_entity_decode($full_address);
		$full_address = urldecode($full_address);
		$full_address = preg_replace('/[^A-Za-z0-9]/', ' ', $full_address);
		$full_address = preg_replace('/ +/', ' ', $full_address);
		$full_address = trim($full_address);

		$companyname = strip_tags( $companyname);
		$companyname = html_entity_decode($companyname);
		$companyname = urldecode($companyname);
		$companyname = preg_replace('/[^A-Za-z0-9]/', ' ', $companyname);
		$companyname = preg_replace('/ +/', ' ', $companyname);
		$companyname = trim($companyname);

		$websiteDetails="select * from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";

		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_idc);
		$custid='';
		if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
 		{
	 		
	 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
				{
			 		$website= $websiteDetailsrow['website'];
			 		$custid= $websiteDetailsrow['website_customerid']; 
			 		$own_cust= $websiteDetailsrow['own_cust_website']; 
				}

		}
		//$website="goog-test.ganeshrjnew.jdomni.in";        
		//$username="ganeshrj"  ; 
		

		$mobile=explode(',',$mobile); 
		$email=explode(',',$email);
		$mobile=empty($mobile[0])?$mobile[1]:$mobile[0];
		$email=empty($email[0])?$email[1]:$email[0];
		$websiteDetails="select * from tbl_omni_email_details where parentid='".$this->parentid."' and email_type='google' and approved=0 and version='".$this->version."'";

		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_idc);
		$custid='';
		

		if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
 		{
	 		
				while($detailsrow=mysql_fetch_assoc($websiteDetailsres)){
					$no_of_seats=$detailsrow['num_of_emails'];
					$username=$detailsrow['admin_username'];
				}

				$app_amount=0;
				$sqlfinance="select  IFNULL(SUM(app_amount) ,0) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='82' and version='".$this->version."' group by parentid";
					$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
					$app_amount=0;
					if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0)
			 		{
				 		while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres))
							{
								$app_amount=$sqlfinancerow['app_amount'];
							}
					}

				$pricesql="select * from online_regis1.omni_add_ons_pricing where campaignid='82' and camp_type='1'";
				$price=0;
				$priceres = parent::execQuery($pricesql, $this->conn_idc);
				if($priceres && mysql_num_rows($priceres)>0){ 
					while($rowprice=mysql_fetch_assoc($priceres)){
						$gprice=$rowprice['price_upfront'];
					}	
					$price=($gprice*$no_of_seats);    

				} 

				if($price>$app_amount || $price==0 || $app_amount==0){ 
					$this->sendMail('Gsuite WithoutMoney Case'.$this->parentid); 
					$this->insertIntoFailureLog('1','Gsuite WithoutMoney Case'.$this->parentid);
					exit;

				}
				 
				if($custid==''){ 
					$maxexecute=3;
					$parameter = "username=".$email."&company=".urlencode($companyname)."&address-line-1=".urlencode($full_address)."&address-line-2=".$addressline2."&city=".urlencode($city)."&state=".$state."&zipcode=".urlencode($pincode)."&phone=".$mobile."&name=".urlencode($contact_person)."&action=createcustomer"."&website=".$website;  
						  $url =$this->meurl.'/email_omni/google_services.php?action=createcustomer&'.$parameter;

						 
					do{
						$custid='';

						$custid=$this->curlCall($url,$parameter,'post');
						
						$checkcustid=trim($custid); 
						$checkcustid=json_decode($checkcustid,1);
						
						if($checkcustid['error']['code']=='0'){
							$maxexecute=0;
							break;
						}
						
						$maxexecute--;
					}while($maxexecute>1);
			 	}
			 	
			 	$custid_arr=json_decode($custid,1);

				if($custid_arr['error']['code']=='0'){ 
					$this->createWebsiteLog($url,$parameter,$custid,'10','Pass'); 
					$parameter = "action=subscribe"."&website=".$website."&no_of_seats=".$no_of_seats;  
				    $url =$this->meurl.'/email_omni/google_services.php?action=subscribe&'.$parameter;

				    $subsribe=$this->curlCall($url,$parameter,'post');
				    $subsribe_arr=json_decode($subsribe,1);
			    	if($subsribe_arr['error']['code']=='0'){ 
			    		$this->createWebsiteLog($url,$parameter,$subsribe,'11','Pass'); 
			    		$redirecturl=$subsribe_arr['data']['redirecturl'];
			    		sleep(2);
		    			$parameter = "action=firstuser"."&website=".$website."&name=".$contact_person."&username=".$username;  
		    		    $url =$this->meurl.'/email_omni/google_services.php?action=firstuser&'.$parameter;
		    		    $firstuser=$this->curlCall($url,$parameter,'post');
		    		    $firstuser_arr=json_decode($firstuser,1);
		    		    if($firstuser_arr['error']['code']=='0'){
		    		    	
		    		    	$this->createWebsiteLog($url,$parameter,$firstuser,'12','Pass'); 
		    		    	$primary_email=$firstuser_arr['data']['primary_email'];
	    		    		$parameter = "action=makeadmin"."&website=".$website."&name=".$contact_person."&username=".$username;  
	    		    	    $url =$this->meurl.'/email_omni/google_services.php?action=makeadmin&'.$parameter;
	    		    	    $makeadmin=$this->curlCall($url,$parameter,'post');
	    		    	    $makeadmin_arr=json_decode($makeadmin,1);
	    		    	     if($makeadmin_arr['error']['code']=='0'){
	    		    	     	$this->createWebsiteLog($url,$parameter,$makeadmin,'13','Pass');  
	    		    	     	$deeplink="https://www.google.com/accounts/AccountChooser?Email=".$primary_email."&continue=".$redirecturl;
	    		    	     	$sqlupt="update tbl_omni_email_details set approved=1,approved_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'";   
	    		    	     	$updtqry = parent::execQuery($sqlupt, $this->conn_idc);
	    		    	     	$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
	    		    	     		 					parentid        = '".$this->parentid."',
	    		    	     		 					data_city       = '".$this->data_city_cm."',
	    		    	     		 					email_taken='yes',
	    		    	     		 					email_type='google',
	    		    	     		 					email_no_taken='".$no_of_seats."',
	    		    	     		 					email_approveddate='".date('Y-m-d H:i"s')."',
	    		    	     		 					email_creation_status='pass'
	    		    	     		 					ON DUPLICATE KEY UPDATE 
	    		    	     		 					email_taken='yes',
	    		    	     		 					email_type='google',
	    		    	     		 					email_no_taken='".$no_of_seats."',
	    		    	     		 					email_approveddate='".date('Y-m-d H:i"s')."',
	    		    	     		 					email_creation_status='pass'";  

	    		    	     	
	    		    	     	if($own_cust=='no'){
		    		    	     	$this->sendWelcomeEmail($deeplink,$username."@".$website,$email); 
	 								$parameter = "action=siteverification"."&website=".$website;  
			    		    	    $url =$this->meurl.'/email_omni/google_services.php?action=makeadmin&'.$parameter;
			    		    	    $sitetocken=$this->curlCall($url,$parameter,'post');
			    		    	    $sitetocken_arr=json_decode($sitetocken,1);
			    		    	    if($sitetocken_arr['error']['code']=='0'){
			    		    	    	$tocken=$sitetocken_arr['data']['token'];
			    		    	         $parameter = "website=".$website."&action=addtextrecord&txt=".$tocken; 
				    		    	    echo $url =$this->meurl.'/business/domainServices.php?action=addtextrecord&txt='.$tocken; 
				    		    	    $res_email_mx_id=$this->curlCall($url,$parameter,'post');
				    		    	    $this->createWebsiteLog($url,$parameter,$res_email_mx_id,'15','Pass');  


			    		    	    }
			    		    	    else{
			    		    	    	$this->sendMail($sitetocken);  
			    		    	    	$this->createWebsiteLog($url,$parameter,$sitetocken,'14','Fail'); 
			    		    	    	$this->insertIntoFailureLog(14,$sitetocken); 
			    		    	    	die;
			    		    	    }
		    		    	    }
		    		    	    else{
		    		    	    	
		    		    	    	$this->createWebsiteLog('Not Needed As Customer Own Website','Not Needed As Customer Own Website','Not Needed As Customer Own Website','14','Pass');  
		    		    	    }
	    		    	     	//google-site-verification=yzcDUNgRYrB9UTaI1v0-HG4O5hsKjA37lZYp0h9vuTA -- site verification
			    		    	$resarray['error']['code']='0';
			    		    	$resarray['error']['msg']='success';
			    		    	echo json_encode($resarray); 
			    		    	exit;  

		    		    	}
		    		    	else{
		    		    		$this->sendMail($makeadmin);  
		    		    		$this->createWebsiteLog($url,$parameter,$makeadmin,'13','Fail'); 
		    		    		$this->insertIntoFailureLog(13,$makeadmin); 
		    		    		die;
		    		    	} 
		    		    }
		    		    else{
		    		    	$this->sendMail($firstuser);  
		    		    	$this->createWebsiteLog($url,$parameter,$firstuser,'12','Fail'); 
		    		    	$this->insertIntoFailureLog(12,$firstuser); 
		    		    	die;  
		    		    }

			    		 
			     	} 
			     	else{
			     		 
			     		$this->sendMail($subsribe);  
			     		$this->createWebsiteLog($url,$parameter,$subsribe,'11','Fail'); 
			     		$this->insertIntoFailureLog(11,$subsribe);
			     		die;  
			     	}

				}
				else{	
						//$custid=json_encode($custid);
						$this->sendMail($custid);  
						$this->createWebsiteLog($url,$parameter,$custid,'10','Fail'); 
						$this->insertIntoFailureLog(10,$custid);
						die;   
				}

			
			
		}
				

	}
	
	function createWebsiteLog($url,$parameter,$response,$step,$result){
		//tbl_omni_website_details_log revert after prcoess
		$sql_ins_website_log = "INSERT INTO tbl_omni_website_details_log set
				 					parentid='".$this->parentid."',
				 					api_call='".stripslashes(addslashes($url.$parameter))."',
				 					api_result = '".stripslashes(addslashes($response))."',
				 					step ='".$step."',
				 					result = '".$result."',
				 					call_time  	= '".date('Y-m-d H:i:s')."'";
				 					//print_r($this->conn_idc);
		$res_ins_website_log = parent::execQuery($sql_ins_website_log, $this->conn_idc);

	}
	function clean_http($url)
    {
        $purl= parse_url($url);
        //echo "<pre>";print_r($purl);echo "</pre>";

        if(stristr($url, "jdomni.com/marketplace/"))
        {
            if(!(empty($purl['host'])))
                $final_domain = $purl['host'].$purl['path'].'?'.$purl['query'];
            else
                $final_domain = trim($purl['path'],"/");
            return $final_domain;
        }

        if(!(empty($purl['host'])))
        {
            //echo '<br>got host';
            $purl['host'] = str_replace("www.","",$purl['host']);
            //print_r($purl);
            if(stristr($purl['host'],".jdomni.com") && !empty($purl['path']))
            {
                //echo '<br>Internla IP:';
                $purl['path'] = trim($purl['path'],"/");
                $pos          = stripos($purl['path'], "/");
                //echo '<br>Pos:'.$pos;
                if($pos===false)
                {
                    $final_domain = $purl['host']."/".$purl['path'];
                }
                else
                {
                    $final_domain = $purl['host']."/".substr($purl['path'],0,$pos);
                }
                //print_r($purl);
            }
            else
            {
                $final_domain = $purl['host'];
            }
        }
        else
        {
            $purl['path'] = trim($purl['path'],"/");
            $purl_array   = explode("/",$purl['path']);
            //print_r($purl_array);
            $purl['host']  = str_replace("www.","",($purl_array[0]));
            $purl['path']  = $purl_array[1];

            if(stristr($purl['host'],".jdomni.com") && !empty($purl['path']))
            {
                $purl['path'] = trim($purl['path'],"/");
                $pos          = stripos($purl['path'], "/");
                //echo '<br>Pos:'.$pos;
                if($pos===false)
                {
                    $final_domain = $purl['host']."/".$purl['path'];
                }
                else
                {
                    $final_domain = $purl['host']."/".substr($purl['path'],0,$pos);
                }
                //print_r($purl);
            }
            else
            {
                $final_domain = $purl['host'];
            }
        }
        return $final_domain;
    } 


  function setsphinxid()
    {
    		$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
    		$res = parent::execQuery($sql, $this->dbConIro);

    		if($res && mysql_num_rows($res) )
    		{
    				$row= mysql_fetch_assoc($res);
    				$this->sphinx_id = $row['sphinx_id'];
    				$this->docid = $row['docid'];
    		}else
    		{
    				echo "sphinx_id not found in tbl_id_generator";
    				exit;
    		}
    }
	function financeInsertUpdateTemp($campaignid,$camp_data) {
        $this -> setsphinxid();
        if ($campaignid>0 && is_array($camp_data)) {

            $insert_str = '';
            foreach($camp_data as $column_key => $column_value) {

                $temp_str    = $column_key ."='".$column_value . "'";
                $insert_str .= (($insert_str=='') ? $temp_str : ','.$temp_str) ;
            }

                        $compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                            ". $insert_str.",
                                            sphinx_id   = '".$this->sphinx_id."',
                                            campaignid  = '".$campaignid."',
                                            parentid    = '" . $this->parentid . "'
                                            ON DUPLICATE KEY UPDATE
                                            " . $insert_str . "";//exit;
                        //echo $compmaster_fin_temp_insert;
            $res_compmaster_fin_temp_insert = parent::execQuery($compmaster_fin_temp_insert, $this->conn_finance_temp);
			
			if(DEBUG_MODE)
			{
				echo '<br>sql_omni_budget :: '.$compmaster_fin_temp_insert;
				echo '<br>res :: '.$res_compmaster_fin_temp_insert;
			}
			
			return $res_compmaster_fin_temp_insert;

        }
	 
    }


	
	
 	function sendMail($subject){
 			foreach($_SERVER as $key=>$val)
 			$serverdetiail.="<br>".$key.'=>'.$val;
 			$headers  = 'MIME-Version: 1.0' . "\r\n";
 			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 			$headers .= 'From: apache@justdial.com';
 			mail ('rajakkal.ganesh@justdial.com' , $subject.$this->parentid,$subject);
 	}
 	function sendWelcomeEmail($link,$username,$email){
 		global $db;
		require_once('class_send_sms_email.php');
		
		$smsObj	 = new email_sms_send($db,$this->data_city_cm); 
		$here="<a target='_BLANK' href='https://www.justdial.com/Terms-of-Use/Service-for-Advertiser/JD-Omni'>here</a>"  ;
		$deeplink="<a target='_BLANK' href='$link'>Open Google Admin Panel</a>"  ;
		$body.="Dear Customer,<br><br>";
		$body.="As per your request for a professional Google mail ID, please find below the required details.";
		$body.="<br><br>E-mail ID: ".$username;
		$body.="<br>Password: justdial@123"; 
		$body.="<br><br>Click on the link below to proceed. <br>";
		$body.="Link: ".$deeplink;  
		$body.="<br><br>Regards,<br>";
		$body.="Team Justdial"; 
		$body=$this->mysql_real_escape_custom($body);
		$emailsend=$smsObj->sendInvoiceMails($email, 'noreply@justdial.com', 'Thank you for your registration with Just Dial for Google Suite.', $body,'ME',$this->parentid);      
 		
 	} 

	
}
?>
