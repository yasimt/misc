<?php
class domainClass extends DB
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
		if($this->params['is_remote'] == 'REMOTE'){
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else{
			$this->is_split = FALSE;			
		}

		$result_msg_arr=array();
	
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->action  = $this->params['action']; 
		}
			
			
		if($this->params['email_acc_price']!=''){
			$this->email_acc_price = $this->params['email_acc_price'];
		}
		if($this->params['email_acc_count']!=''){
			$this->email_acc_count = $this->params['email_acc_count'];
		}
		
	 	if($this->action!='4'){
			if(trim($this->params['parentid']) == "")
			{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "Parentid Missing";
					echo json_encode($result_msg_arr);exit;
			}
			else{
				$this->parentid  = $this->params['parentid']; 
			}
		}

		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->module  = $this->params['module']; 
		}

		if(trim($this->params['data_city']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Data City Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else{
			$this->data_city  = $this->params['data_city']; 
		}
		if($this->action!='4'){
			if(trim($this->params['version']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Data City Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else{
				$this->version  = $this->params['version']; 
			}
		}
		if(trim($this->params['tlds']) != "")
		{
			$this->tlds  = $this->params['tlds']; 
		}
		if(trim($this->params['no_of_emails']) != "")
		{
			$this->no_of_emails  = $this->params['no_of_emails']; 
		}
		else {
			$this->no_of_emails  = '';
		}
		if(trim($this->params['email_type']) != "")
		{
			$this->email_type  = $this->params['email_type'];  
		} 
		else {
			$this->email_type  = ''; 
		}
		if(trim($this->params['admin_username']) != "")
		{
			$this->admin_username  = $this->params['admin_username'];  
		} 
		else{
			$this->admin_username  = ''; 
		}
		
		
		if($this->action!='3' && $this->action!='4' && $this->action!='9'){ 

			if(trim($this->params['usercode']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Usercode Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else{
				$this->usercode  = $this->params['usercode']; 
			}

			if($this->action!='4' && $this->action!='3'  && $this->action!='5' && $this->action!='6' && $this->action!='7' && $this->action!='9' && $this->action!='8' && $this->action!='9' && $this->action!='10' && $this->action!='populateomnidata'){  
				if(trim($this->params['domainname']) == "")
				{
					$result_msg_arr['error']['code'] = 1;
					$result_msg_arr['error']['msg'] = "domainname Missing";
					echo json_encode($result_msg_arr);exit;
				}
				else{
					$this->domainname  = $this->params['domainname']; 
				}
			}
		}
		
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}

		$this->companyClass_obj = new companyClass();
		$status=$this->setServers();
		$this->meurl="http://".GNO_URL;
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

	function checkAvailibity(){
		$urlparams['action']='checkavailability';
		/*if (filter_var($this->domainname, FILTER_VALIDATE_URL) === FALSE) {
		   $result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Not A Valid Url";
			return json_encode($result_msg_arr);
		}
		
		$domainname_arr=explode('.',  strtolower($this->domainname));
		if(strtolower($domainname_arr[0])!='http://www'){
			  $result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Not A Valid Url";
			return json_encode($result_msg_arr);

		}*/
		/*$domainname=explode('.', strtolower($this->domainname));
		unset($domainname_arr[0]);
		unset($domainname_arr[1]);
		$tlds=implode('.', $domainname_arr);*/


		$urlparams['domainname']=$this->domainname;
		$urlparams['tlds']=$this->tlds;
		
		$tldstxt.='&tlds='.$this->tlds;
		
		$url =$this->meurl.'/business/domainServices.php?action='.$urlparams['action'].'&domainname='.urlencode($urlparams['domainname']).$tldstxt;
		$res=$this->curlCallLive($url);
		$json_de=json_decode($res,true);
		
		$price=$this->getAllPrice();
		$json_price=json_decode($price,true);
		$tlds_arr=explode(',', $this->tlds);

		foreach ($tlds_arr as $keytlds => $tldsvalue) {
			
			if(strtolower(trim($json_de[$this->domainname.".".$tldsvalue]['status']))=='available'){
				$price=$json_price['result']['price'][".".$tldsvalue];
				$result_msg_arr[$this->domainname.".".$tldsvalue]['error']['code'] = 0;
				$result_msg_arr[$this->domainname.".".$tldsvalue]['error']['msg'] = "Domain Available";
				$result_msg_arr[$this->domainname.".".$tldsvalue]['result']['price'] = $price;
				$result_msg_arr[$this->domainname.".".$tldsvalue]['result']['domainname'] =$this->domainname.".".$tldsvalue;
			}
			else{
			$result_msg_arr[$this->domainname.".".$tldsvalue]['error']['code'] = 1;
			$result_msg_arr[$this->domainname.".".$tldsvalue]['error']['msg'] = "Domain Not Available";
			
			}
		}
		return json_encode($result_msg_arr);
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

	function getPrice($domainname=null){

		$domarray = array("org", "biz", "us", "cno", "info");
		$standardcomarray = array("us", "eu", "de", "qc", "kr", "gr");
		$premiumcomarray = array("uk", "gb", "br", "hu", "jpn", "no", "ru", "sa", "se", "uy", "za");
		$tldmorearray = array('eu', 'asia', 'name', 'tel', 'tv', 'me', 'ws', 'bz',
		    'cc', 'org.uk', 'me.uk', 'net.in', 'org.in', 'ind.in', 'firm.in',
		    'gen.in', 'mn', 'us.com', 'eu.com', 'uk.com', 'uk.net', 'gb.com',
		    'gb.net', 'de.com', 'cn.com', 'qc.com', 'kr.com', 'ae.org', 'br.com',
		    'hu.com', 'jpn.com', 'no.com', 'ru.com', 'sa.com', 'se.com', 'se.net',
		    'uy.com', 'za.com', 'co', 'gr.com', 'co.nz', 'net.nz', 'org.nz', 'com.co',
		    'net.co', 'nom.co', 'ca', 'de', 'es', 'xxx', 'ru', 'com.ru', 'net.ru',
		    'org.ru', 'pro', 'nl', 'sx', 'cn', 'com.cn', 'net.cn', 'org.cn', 'com.de');
		if($this->params['action']=='2')
			$domainname = $this->domainname;

		$domainname=explode('.', $domainname);
		unset($domainname[0]);
		$domainname=implode('.',$domainname);
		if ($domainname != "") {

		    $selectedtld = $domainname;
		    $count = substr_count($selectedtld, '.');
		    if ($count == 1) {

		        $split = explode(".", $selectedtld, 2);
		        $selectedtld = $split[1];

		        if (in_array($selectedtld, $domarray)) {
		            $selectedtld = "dom" . $selectedtld;
		        } elseif ($split[1] == "com") {
		            $selectedtld = "domcno";
		        } else {
		            $selectedtld = "dot" . $selectedtld;
		        }
		    }
		    if ($count == 2) {
		        $split = explode(".", $selectedtld, 3);
		        if ($split[2] == "com" || $split[2] == "net") {

		            if (in_array($split[1], $standardcomarray)) {
		                $selectedtld = "centralnicstandard";
		            } elseif (in_array($split[1], $premiumcomarray)) {
		                $selectedtld = "centralnicpremium";
		            } else if ($split[1] == "cn" && $split[2] == "com") {
		                $selectedtld = "centralniccncom";
		            }
		        } else if ($split[2] == "de" && $split[1] == "com") {
		            $selectedtld = "centralniccomde";
		        } else if ($split[2] == "org" && $split[1] == "ae") {
		            $selectedtld = "centralnicstandard";
		        } else {
		            $selectedtld = "thirdleveldot" . $split[2];
		        }
		    }
		} else {

		    $selectedtld = "domcno";
		}

		$url =$this->meurl.'/business/domainServices.php?action=getprice';
		$res_price=$this->curlCallLive($url);
		$json_price=json_decode($res_price,true);
		$price=0;
		
		if(is_array($json_price)){
			 return $price=$json_price[$selectedtld]['addnewdomain'][1];

		}
		if($price==0){
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "Price Not Found";
				return json_encode($result_msg_arr);
		}
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

	function registerWebsite(){
		return;
		// stop domain booking 
		$domainname='';
		$companyname='';
		$full_address='';
		$city='';
		$pincode='';
		$contact_person='';

		$selectsqlrow = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'companyname,city,pincode,state,email,mobile_feedback,mobile,full_address,contact_person';
		$cat_params['page']			= 'domainClass';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);

		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){

			$selectsqlrow 		=	$res_gen_info1['results']['data'][$this->parentid];
			$mobile=$selectsqlrow['mobile'];
			$email=$selectsqlrow['email'];
			$companyname=$selectsqlrow['companyname'];
			$contact_person=$selectsqlrow['contact_person'];
			$city=$selectsqlrow['city'];
			$pincode=$selectsqlrow['pincode'];
			$full_address=substr($selectsqlrow['full_address'],0,60);
			$state=$selectsqlrow['state'];
		}

		/*$selectsql="select companyname,city,pincode,state,email,mobile_feedback,mobile,full_address,contact_person from  db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
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
		}*/

		$getomniextradetailsql="select *  from tbl_omni_extradetails where parentid='".$this->parentid."'";
		$getomniextradetailres = parent::execQuery($getomniextradetailsql, $this->conn_idc);
		if($getomniextradetailres && mysql_num_rows($getomniextradetailres)>0)
 		{
	 		while($getomniextradetailrow=mysql_fetch_assoc($getomniextradetailres))
				{
					
					$template_name=$getomniextradetailrow['template_name'];
					$template_type=$getomniextradetailrow['template_type'];
					$vertical_id=$getomniextradetailrow['vertical_id'];
					$vertical_name=$getomniextradetailrow['vertical_name'];
					$omni_type=$getomniextradetailrow['omni_type'];
					$arecord=$getomniextradetailrow['arecord'];
				}
		}
		else{
		//	mail ('prameshjha@justdial.com' , 'Fetching Data No Data - Service Template Api - domain page'.$this->parentid,'Fetching Data No Data - Service Template Api - domain page'.$this->parentid);
				include('omniDetailsClass.php');
				$omniDetailsClassobj = new omniDetailsClass($this->params);
				$templatedetails=$omniDetailsClassobj->getTemplateMapping();
				$template_name=$templatedetails['ts']['tnm'];
				$template_type=$templatedetails['ts']['ttyp'];
				$vertical_id=$templatedetails['ts']['vid'];
				$vertical_name=$templatedetails['ts']['vnm'];
				$omni_type=$templatedetails['ts']['omnityp'];
				$arecord=$templatedetails['ts']['pip'];
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

		
		
		$mobile=explode(',',$mobile);
		$email=explode(',',$email);
		$mobile=empty($mobile[0])?$mobile[1]:$mobile[0];
		$email=empty($email[0])?$email[1]:$email[0];

		$websiteDetails="select * from tbl_omni_website_details where parentid='".$this->parentid."' and website_own=0 and approved=0 and version='".$this->version."'";

		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_idc);
		$custid='';
		if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
 		{
	 		
	 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
				{
			 		$domainnames= $websiteDetailsrow['website_requests'];
			 		$custid= $websiteDetailsrow['website_userid'];
				}

				$sqlfinance="select sum(app_amount) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='74' and version='".$this->version."' group by parentid";
					$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
					$app_amount=0;
					if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0)
			 		{
				 		while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres))
							{
								$app_amount=$sqlfinancerow['app_amount'];
							}
					}
					else{
						$this->insertIntoFailureLog(1,'no money case'); 
						$sub="without money come in approval".$this->parentid;
						$this->sendMail($sub); 
						exit;
					}
				

				if($custid==''){
					$maxexecute=3;
					$parameter = "username=".$email."&company=".urlencode($companyname)."&address-line-1=".urlencode($full_address)."&address-line-2=".$addressline2."&city=".urlencode($city)."&state=".$state."&zipcode=".urlencode($pincode)."&phone=".$mobile."&name=".urlencode($contact_person)."&action=createcustomer";
					$url =$this->meurl.'/business/domainServices.php?action=createcustomer&';
					do{
						$custid='';
						$custid=$this->curlCall($url,$parameter,'post');
						$checkcustid=trim($custid); 
						$checkcustid=json_decode($checkcustid,1);
						 
						if($checkcustid['status']!='error' && @trim($checkcustid)!=''){
							$maxexecute=0;
							break;
						}
						
						$maxexecute--;
					}while($maxexecute>1);
			 	}
				$custid=trim($custid);
				$custid=json_decode($custid,1);
				
				//echo "<pre>"; print_r($custid);
				
				if(strtolower($custid['status'])=='error')
				{
					$cusromerdetailsarry = $this->getCustomerDetailsbyEmail($email);
					
					if(strtolower($cusromerdetailsarry['status'])=="customer details found" && intval($cusromerdetailsarry['error_code'])==0)
					{
						$custid=$cusromerdetailsarry['customerid'];
						$this->createWebsiteLog($this->meurl.'/business/domainServices.php','?action=getCustomerdetails&username='.$email,json_encode($cusromerdetailsarry),'2','pass');
					}
				}
				
				/*
				echo 'cusromerdetailsarry'; print_r($cusromerdetailsarry);
				echo "<br>custid--int ".$custid;
				echo "<br>custid--arr "; print_r($custid);
				die("<br>".__FILE__ ."--" . __LINE__ );
				*/

				if(strtolower($custid['status'])!='error'){
				//if(true){
					$apiDetails="update tbl_omni_website_details set website_userid=$custid where parentid='".$this->parentid."'";
					$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);

					$this->createWebsiteLog($url,$parameter,$custid,'2','Pass');
					//$parameter = "customerid=".$custid."&action=createcustomercontact";
					$parameter = "customerid=".$custid."&email=".$email."&company=".urlencode($companyname)."&address-line-1=".urlencode($full_address)."&address-line-2=".$addressline2."&city=".urlencode($city)."&state=".$state."&zipcode=".urlencode($pincode)."&phone=".$mobile."&name=".urlencode($contact_person)."&action=createcustomercontact";
					
					$url =$this->meurl.'/business/domainServices.php?action=createcustomercontact&';
					$customercontactid='';
					do{
					
					$customercontactid=$this->curlCall($url,$parameter,'post');
					$checkcustomercontactid=trim($customercontactid);
					$checkcustomercontactid=json_decode($checkcustomercontactid,1);
					if(($checkcustomercontactid['status'])!='error' && trim($checkcustomercontactid)!=''){
						$maxexecute=0; 
						break;
					}
					$maxexecute--;
					}while($maxexecute>1);

					$customercontactid_arr=json_decode($customercontactid,1);
					
					if(strtolower($customercontactid_arr['status'])!='error'){
						$this->createWebsiteLog($url,$parameter,$customercontactid,'3','Pass');
						$domainnames=explode(',',$domainnames);
						$tot=count($domainnames);
						$count=0;
						$price=0;
							foreach ($domainnames as $key => $domainname) {
								$domainname=explode('.', $domainname);
								unset($domainname[0]);
								$domainname=implode('.', $domainname);
								$parameter['domainname']	  = $domainname;
								$price=$this->getPrice($domainname);
								if($price<=round($app_amount)){								
								$count++;
								$addressline2='';
								$customercontactid=trim($customercontactid);
								$parameter = "custid=".urlencode($custid)."&domainname=".urlencode($domainname)."&contactid=".$customercontactid."&action=register";
								$url =$this->meurl.'/business/domainServices.php?action=register&';
								 $res_price=$this->curlCall($url,$parameter,'post');
								 $res_price_arr=json_decode($res_price,1);

								if(strtolower($res_price_arr['status'])!='error' && strtolower($res_price_arr['status'])=='success'){ 

						 		$this->createWebsiteLog($url,$parameter,$res_price,'4','Pass');
								$apiDetails="update tbl_omni_website_details set approved_status='".addslashes(stripslashes($res_price))."', approved_api='".addslashes(stripslashes($parameter))."' ,website_userid=$custid,website_approved='".addslashes(stripslashes($domainname))."',approved_time='".date('Y-m-d H:i:s')."',approved=1 where parentid='".$this->parentid."'";
									$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);

								$apiDetails="update tbl_companymaster_extradetails set redirection_url='".addslashes(stripslashes($domainname))."' where parentid='".$this->parentid."'";
									$apiDetailsres = parent::execQuery($apiDetails, $this->conn_iro);
								$apiDetails="update tbl_omni_mapping set omni_website='".addslashes(stripslashes($domainname))."' where parentid='".$this->parentid."' and version='".$this->version."'";
									$apiDetailsres = parent::execQuery($apiDetails, $this->conn_iro);
								
								$updatecons="update online_regis1.tbl_omni_details_consolidated set website='".$this->clean_http($domainname)."',
								website_customerid='".$custid."',
								website_approved_date='".date('Y-m-d H:i:s')."',
								website_arecord='".$arecord."',
								website_booked_by='GENIO',
								website_booked_by_uid='000000',
								emailid='".$email."',
								mobile='".$mobile."',
								contact_person='".$contact_person."',
								website_creation_status='pass',
								own_cust_website='no'
								where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
								$updateconsres = parent::execQuery($updatecons, $this->conn_idc);

								$ins_audit = "INSERT INTO online_regis1.tbl_omni_website_audit_details set
								 		 					parentid='".$this->parentid."',
								 		 					data_city='".$this->data_city."',
								 		 					website='".$this->clean_http($domainname)."',
								 		 					price='".$price."',
								 		 					booked_date='".date("Y-m-d H:i:s")."',
								 		 					userid='00000', 
								 		 					booked_by='GENIO',
															action='book', 
			 		 										action_by='GENIO'
								 		 					ON DUPLICATE KEY UPDATE
								 		 					parentid='".$this->parentid."',
								 		 					data_city='".$this->data_city."',
								 		 					price='".$price."',
								 		 					booked_date='".date("Y-m-d H:i:s")."',
								 		 					userid='00000', 
								 		 					booked_by='GENIO',
															action='book', 
			 		 										action_by='GENIO'
								 		 					";  
								$res_ins_audit = parent::execQuery($ins_audit, $this->conn_idc);   

									$parameter = "entityid=".urlencode($res_price_arr['entityid'])."&action=createzone";
									include('omniDetailsClass.php');
									
									$params=$this->params;
									if(strtolower($omni_type)=='products'){
									$params['action']='7';
									$omniDetailsClassobj = new omniDetailsClass($params);
									$result = $omniDetailsClassobj->setOmniDomain();
									}
									else{
										$params['action']='14';
										$omniDetailsClassobj = new omniDetailsClass($params);
										$result = $omniDetailsClassobj->domainMappingService('',$domainname);
										$params['action']='7';
										$omniDetailsClassobj = new omniDetailsClass($params);
										$result = $omniDetailsClassobj->setOmniDomain();
									}


									$url =$this->meurl.'/business/domainServices.php?action=createzone&';
									$zoneres=$this->curlCall($url,$parameter,'post');
									$zoneres_arr=$this->curlCall($url,$parameter,'post');

									if(strtolower($zoneres_arr['status'])!='error'){
										$this->createWebsiteLog($url,$parameter,$zoneres,'5','Pass');
										
										$parameter = "domainname=".urlencode($domainname)."&action=createcustomarecord&ip=".$arecord;
										$url =$this->meurl.'/business/domainServices.php?action=createcustomarecord&';
										$arecordcreation=$this->curlCall($url,$parameter,'post');
										$arecordcreation_arr=$this->curlCall($url,$parameter,'post');
										if(strtolower($zoneres_arr['status'])!='error'){
											$this->createWebsiteLog($url,$parameter,$zoneres,'6','Pass');
										}
										else{ 
											$this->createWebsiteLog($url,$parameter,$zoneres,'6','Fail');
										}
										 	$result_msg_arr['error']['code'] = 0;
											$result_msg_arr['error']['msg'] = "success";
											echo  json_encode($result_msg_arr); 

									}
									else{
										$this->createWebsiteLog($url,$parameter,$zoneres,'5','Fail');
										foreach($_SERVER as $key=>$val)
										$serverdetiail.="<br>".$key.'=>'.$val;
										$headers  = 'MIME-Version: 1.0' . "\r\n";
										$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
										$headers .= 'From: apache@justdial.com';
										mail ('prameshjha@justdial.com' , 'Zone Creation '.$this->parentid, $zoneres);
										$this->insertIntoFailureLog(5,$zoneres);
									}
									break;
								}
								else{
									if($tot==$count){
										foreach($_SERVER as $key=>$val)
										$serverdetiail.="<br>".$key.'=>'.$val;
										$headers  = 'MIME-Version: 1.0' . "\r\n";
										$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
										$headers .= 'From: apache@justdial.com';
										mail ('prameshjha@justdial.com' , 'Domain Booking Failure '.$this->parentid, $res_price);
									}
									$this->createWebsiteLog($url,$parameter,$res_price,'4','Fail');
									$this->insertIntoFailureLog(4,$res_price);
								}
							}
							else{
								$apiDetails="update tbl_omni_website_details set approved_status='appamt-$app_amount price-$price No Full Money', approved_api='".addslashes(stripslashes($parameter))."' where parentid='".$this->parentid."'";
								$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);
								$response="appamt-$app_amount price-$price No Full Money";
								$this->createWebsiteLog('App AMount check',$parameter,$response,'2','Fail');
								$res_price="appamt-$app_amount price-$price No Full Money";
								foreach($_SERVER as $key=>$val)
											$serverdetiail.="<br>".$key.'=>'.$val;
											$headers  = 'MIME-Version: 1.0' . "\r\n";
											$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
											$headers .= 'From: apache@justdial.com';
											mail ('prameshjha@justdial.com' , 'Domain Booking Failure- App Amount Mistch'.$this->parentid, $res_price);
											$this->insertIntoFailureLog(4,$res_price);

								die;
							}	
						}// for 
					}
					else{
						$this->createWebsiteLog($url,$parameter,$customercontactid,'3','Fail');
							foreach($_SERVER as $key=>$val)
						$serverdetiail.="<br>".$key.'=>'.$val;
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers .= 'From: apache@justdial.com';
						mail ('prameshjha@justdial.com' , 'Customer Creation Failed '.$this->parentid, 'Customer Creation Issue');
						
						$apiDetails="update tbl_omni_website_details set approved_status='Customer Contact Creation Issue', approved_api='".addslashes(stripslashes($parameter))."' where parentid='".$this->parentid."'";
							$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);
							$this->insertIntoFailureLog(3,$customercontactid);
						die;
					}
				}
				else{
						foreach($_SERVER as $key=>$val)
					$serverdetiail.="<br>".$key.'=>'.$val;
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
					$headers .= 'From: apache@justdial.com';
					mail ('prameshjha@justdial.com' , 'Customer Creation Failed '.$this->parentid, 'Customer Creation Issue');
					
					$apiDetails="update tbl_omni_website_details set approved_status='Customer Creation Issue', approved_api='".addslashes(stripslashes($parameter))."' where parentid='".$this->parentid."'";
						$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);
						$custid=json_encode($custid);
						$this->createWebsiteLog($url,$parameter,$custid,'2','Fail');
						$this->insertIntoFailureLog(1,$custid);
						die;
				}

			
			
		}

		$websiteDetails="select * from tbl_omni_website_details where parentid='".$this->parentid."' and website_own=1 and approved=0 and version='".$this->version."'";
		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_idc);

		if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
 		{
 			$website='';
	 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
			{
		 		$domainnames= $websiteDetailsrow['website_requests'];
		 		$apiDetails="update tbl_omni_website_details set approved_status='own website', approved_api='own website' ,approved=0 where parentid='".$this->parentid."'";
		 			$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);

		 		$apiDetails="update tbl_companymaster_extradetails set redirection_url='".addslashes(stripslashes($domainnames))."' where parentid='".$this->parentid."'";
		 			$apiDetailsres = parent::execQuery($apiDetails, $this->conn_iro);
				$website=$websiteDetailsrow['website_requests'];
				$apiDetails="update tbl_omni_mapping set omni_website='".$websiteDetailsrow['website_requests']."' where parentid='".$this->parentid."' and version='".$this->version."'";
				$apiDetailsres = parent::execQuery($apiDetails, $this->conn_iro); 

				$updatecons="update online_regis1.tbl_omni_details_consolidated set website='".$this->clean_http($websiteDetailsrow['website_requests'])."',website_creation_status='pass',
				own_cust_website='yes'
				where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
				$updateconsres = parent::execQuery($updatecons, $this->conn_idc);
			}
				
			include('omniDetailsClass.php');
			$params=$this->params;
							
			if(strtolower($omni_type)=='products'){
				$params['action']='7';
				$omniDetailsClassobj = new omniDetailsClass($params);
				$result = $omniDetailsClassobj->setOmniDomain();
			}
			else{
				$params['action']='14';
				$omniDetailsClassobj = new omniDetailsClass($params);
				$result = $omniDetailsClassobj->domainMappingService('',$website);
				$params['action']='7';
				$omniDetailsClassobj = new omniDetailsClass($params);
				$result = $omniDetailsClassobj->setOmniDomain();
			}

		 	$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			$result_msg_arr['result']['price'] = array();
			echo  json_encode($result_msg_arr);
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

	function getAllPrice(){

		$domarray = array("org", "biz", "us","info");
		$standardcomarray = array("us", "eu", "de", "qc", "kr", "gr");
		$premiumcomarray = array("uk", "gb", "br", "hu", "jpn", "no", "ru", "sa", "se", "uy", "za");
		$tldmorearray = array('eu', 'asia', 'name', 'tel', 'tv', 'me', 'ws', 'bz',
		    'cc', 'org.uk', 'me.uk', 'net.in', 'org.in', 'ind.in', 'firm.in',
		    'gen.in', 'mn', 'us.com', 'eu.com', 'uk.com', 'uk.net', 'gb.com',
		    'gb.net', 'de.com', 'cn.com', 'qc.com', 'kr.com', 'ae.org', 'br.com',
		    'hu.com', 'jpn.com', 'no.com', 'ru.com', 'sa.com', 'se.com', 'se.net',
		    'uy.com', 'za.com', 'co', 'gr.com', 'co.nz', 'net.nz', 'org.nz', 'com.co',
		    'net.co', 'nom.co', 'ca', 'de', 'es', 'xxx', 'ru', 'com.ru', 'net.ru',
		    'org.ru', 'pro', 'nl', 'sx', 'cn', 'com.cn', 'net.cn', 'org.cn', 'com.de');
		
		$url =$this->meurl.'/business/domainServices.php?action=getprice';
		$res_price=$this->curlCallLive($url);
		$json_price=json_decode($res_price,true);
		$price_array=array();
		
		//$requireddomains=array('.com','.in','.net','.co.in','.net.in');
		$requireddomains=array('.com','.in');

		foreach ($requireddomains as $key => $value) {
			
		    $selectedtld = $value;
		    $count = substr_count($selectedtld, '.');
		    if ($count == 1) {
		    	
		        $split = explode(".", $selectedtld, 2);
		        $selectedtld = $split[1];

		        if (in_array($selectedtld, $domarray)) {
		            $selectedtld = "dom" . $selectedtld;
		        } elseif ($split[1] == "com") {
		            $selectedtld = "domcno";
		        } else {
		            $selectedtld = "dot" . $selectedtld;
		        }
		        
		    }
		    if ($count == 2) {
		        $split = explode(".", $selectedtld, 3);
		        if ($split[2] == "com" || $split[2] == "net") {

		            if (in_array($split[1], $standardcomarray)) {
		                $selectedtld = "centralnicstandard";
		            } elseif (in_array($split[1], $premiumcomarray)) {
		                $selectedtld = "centralnicpremium";
		            } else if ($split[1] == "cn" && $split[2] == "com") {
		                $selectedtld = "centralniccncom";
		            }
		        } else if ($split[2] == "de" && $split[1] == "com") {
		            $selectedtld = "centralniccomde";
		        } else if ($split[2] == "org" && $split[1] == "ae") {
		            $selectedtld = "centralnicstandard";
		        } else {
		            $selectedtld = "thirdleveldot" . $split[2];
		        }
		    }
			    	
			$price_array[$value]=$json_price[$selectedtld]['addnewdomain'][1];
				
		}

		if(is_array($price_array)){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "";
			$result_msg_arr['result']['price'] = $price_array;
			return json_encode($result_msg_arr);

		}
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "No Price Available";
			$result_msg_arr['result']['price'] = array();
			return json_encode($result_msg_arr);
		}
	}

	function addEmailTemp(){

		if(intval($this->no_of_emails)==0 || trim($this->no_of_emails)==''){
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "No Of Email Needed";
				echo json_encode($result_msg_arr);exit;
		}
		if(intval($this->email_type)==0 || trim($this->email_type)==''){
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Email Type Needed" ; 
				echo json_encode($result_msg_arr);exit; 
		}
		if($this->email_type>2 || $this->email_type<0){
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Wrong Email Type Passed" ; 
				echo json_encode($result_msg_arr);exit;  
		}

		$email_type_arr=array(1=>"direct-i",2=>"google");
		$email_type=$email_type_arr[$this->email_type];
		$adminusr='';

		if($this->email_type==1){
			$getprice=$this->getEmailPricingDirecti(); 
			$getprice=json_decode($getprice,1);
			$email_price=$getprice['data']['direct-i']['price']; 
			$price=($email_price*($this->no_of_emails)*12);  
		}
		else if($this->email_type==2){

			if(trim($this->admin_username)==''){
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Admin Username Required" ; 
				echo json_encode($result_msg_arr);exit;  
			}

			$adminusr="admin_username='".$this->mysql_real_escape_custom($this->admin_username)."',";
	      	$pricesql="select * from online_regis1.omni_add_ons_pricing where campaignid='82' and camp_type='1'";
			$gprice=0;
			$priceres = parent::execQuery($pricesql, $this->conn_idc);

	      	if($priceres && mysql_num_rows($priceres)>0){ 
		      	while($rowprice=mysql_fetch_assoc($priceres)){
		      		$gprice=$rowprice['price_upfront'];
		      	}	
		      	$price=($gprice*$this->no_of_emails);    
		 	}
			else{
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Wrong Email Type Passed" ; 
				echo json_encode($result_msg_arr);exit;  
			}
		}
		else{
		 	$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Wrong Email Type Passed" ; 
			echo json_encode($result_msg_arr);exit;  
		}

		$sql_ext_temp = "INSERT INTO tbl_omni_email_details_temp set
		 		 					parentid='".$this->parentid."',
		 		 					version='".$this->version."',
		 		 					num_of_emails='".$this->no_of_emails."',
		 		 					email_type='".$email_type."',
		 		 					added_by='".$this->usercode."',
		 		 					$adminusr
		 		 					added_time='".date("Y-m-d H:i:s")."' ,
		 		 					email_acc_count = '".$this->email_acc_count."',
		 		 					email_acc_price = '".$this->email_acc_price."'
		 		 					ON DUPLICATE KEY UPDATE
		 		 					num_of_emails='".$this->no_of_emails."',
		 		 					email_type='".$email_type."',
		 		 					added_by='".$this->usercode."',
		 		 					$adminusr
		 		 					added_time='".date("Y-m-d H:i:s")."',
		 		 					email_acc_count = '".$this->email_acc_count."',
		 		 					email_acc_price = '".$this->email_acc_price."' ";  
		
		$sql_ext_res = parent::execQuery($sql_ext_temp, $this->conn_temp); 

		$res_compmaster_fin_temp_insert = $this->financeInsertUpdateTemp($campaignid=82,array("budget"=>$price,"original_budget"=>$price,"original_actual_budget"=>$price,"duration"=>'365',"recalculate_flag"=>1,"version" =>$this->version));
		$this->addEmailCampaignTemp();
		if($sql_ext_res){ 
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			return json_encode($result_msg_arr);
		}
		else{
		 	$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error!";
			return json_encode($result_msg_arr);
		}
	}

	function deleteEmailCampaign(){
    	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
	    						parentid='".$this->parentid."',
	    						campaignid='82',
	    						selected  	= 0
	    						ON DUPLICATE KEY UPDATE
	    						campaignid='82',
	    						selected  	= 0";
    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
    	$sql_del_temp_fnc = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid in ('82')";
    	$res_del_temp_fnc = parent::execQuery($sql_del_temp_fnc, $this->conn_finance_temp); 

    	$delemailsql="delete from tbl_omni_email_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
    	$res_del_temp_omni = parent::execQuery($delemailsql, $this->conn_temp); 
    	if($res_del_temp_omni){
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Success";
			echo json_encode($result_msg_arr);
			exit;
    	}
    	else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Error";
			echo json_encode($result_msg_arr);
			exit;
    	}
	}
	
	function addEmailCampaignTemp(){
	 	$sql_ins_temp_omni = "INSERT INTO campaigns_selected_status set
    						parentid='".$this->parentid."',
    						campaignid='82',
    						selected  	= 1
    						ON DUPLICATE KEY UPDATE
    						campaignid='82',
    						selected  	= 1";
    	$res_del_temp_omni = parent::execQuery($sql_ins_temp_omni, $this->conn_temp);
    	if($res_del_temp_omni){
    			$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				return $result_msg_arr;
				
    	}
    	else{
	    			$result_msg_arr['error']['code'] = 1;
    				$result_msg_arr['error']['msg'] = "Error";
    				return $result_msg_arr;
    				
	    	}
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


	function createCustomer(){
		 
		$domainname='';
		$companyname='';
		$full_address='';
		$city='';
		$pincode='';
		$contact_person='';

		$selectsqlrow = array();
		$cat_params = array();
		$cat_params['data_city'] 	= $this->data_city;
		$cat_params['table'] 		= 'gen_info_id';
		$cat_params['module'] 		= $this->module;
		$cat_params['parentid'] 	= $this->parentid;
		$cat_params['action'] 		= 'fetchdata';
		$cat_params['fields']		= 'companyname,city,pincode,state,email,mobile_feedback,mobile,full_address,contact_person';
		$cat_params['page']			= 'domainClass';

		$res_gen_info1		= 	array();
		$res_gen_info1		=	json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
		
		
		if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){

			$selectsqlrow 		=	$res_gen_info1['results']['data'][$this->parentid];
			echo "<pre>";print_r($selectsqlrow);
			$mobile=$selectsqlrow['mobile'];
			$email=$selectsqlrow['email'];
			$companyname=$selectsqlrow['companyname'];
			$contact_person=$selectsqlrow['contact_person'];
			$city=$selectsqlrow['city'];
			$pincode=$selectsqlrow['pincode'];
			$full_address=substr($selectsqlrow['full_address'],0,60);
			$state=$selectsqlrow['state'];
		}


		/*$selectsql="select companyname,city,pincode,state,email,mobile_feedback,mobile,full_address,contact_person from  db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
		$selectsqlres = parent::execQuery($selectsql, $this->dbConIro);

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

		}*/
		
		$mobile=explode(',',$mobile);
		$email=explode(',',$email);
		$mobile=empty($mobile[0])?$mobile[1]:$mobile[0];
		$email=empty($email[0])?$email[1]:$email[0];
		
		$maxexecute=3;
		/*$email='ganeshrj2010'.rand().'@gmail.com';// for dev
		$full_address="asfasgsagsag";// for dev*/
		$parameter = "username=".$email."&company=".urlencode($companyname)."&address-line-1=".urlencode($full_address)."&address-line-2=".$addressline2."&city=".urlencode($city)."&state=".$state."&zipcode=".urlencode($pincode)."&phone=".$mobile."&name=".urlencode($contact_person)."&action=createcustomer";
		$url =$this->meurl.'/business/domainServices.php?action=createcustomer&';
		do{
			$custid='';
			$custid=$this->curlCall($url,$parameter,'post');
			$checkcustid=trim($custid); 
			$checkcustid=json_decode($checkcustid,1);
			 
			if($checkcustid['status']!='error' && @trim($checkcustid)!=''){
				$maxexecute=0;
				break;
			}
			
			$maxexecute--;
		}while($maxexecute>1);
		$custid=trim($custid);
		$custid=json_decode($custid,1);
		/*$custid=14478153;
		$app_amount=11111;*/
		if(strtolower($custid['status'])!='error'){ 
			$this->createWebsiteLog($url,$parameter,$custid,'2','Pass'); 
			$updtcons="update online_regis1.tbl_omni_details_consolidated set website_customerid='".$custid."' where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";
			$updtres = parent::execQuery($updtcons, $this->conn_idc);
			return $custid;
		}
		else{
			$custid=json_encode($custid);
			$this->createWebsiteLog($url,$parameter,$custid,'2','Fail'); 
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Customer Creation Issue';
			echo json_encode($result_msg_arr); exit ;
		}

	}

	/*function deleteEmailCampaign(){ 
		$sql_del_temp_fnc_new = "DELETE FROM tbl_companymaster_finance_temp WHERE parentid='".$this->parentid."' AND campaignid='82'";
		$res_del_temp_fnc_new = parent::execQuery($sql_del_temp_fnc_new, $this->conn_finance_temp);

		$delemailsql="delete from tbl_omni_email_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
		$res_del_temp_omni = parent::execQuery($delemailsql, $this->conn_temp); 
		if($res_del_temp_fnc_new){ 
	  	 		$result_msg_arr['error']['code'] = 0;
	  			$result_msg_arr['error']['msg'] = "Success";
	  			echo json_encode($result_msg_arr);
	  			exit;
	  		}
	      else{
	      	 		$result_msg_arr['error']['code'] = 1;
	      			$result_msg_arr['error']['msg'] = "Error";
	      			echo json_encode($result_msg_arr);
	      			exit;
	      } 
	}*/

	function addNoOfEmails(){

		$getprice=$this->getEmailPricingDirecti(); 
		$getprice=json_decode($getprice,1);
		$email_price=$getprice['data']['direct-i']['price'];
		$websiteDetails="select * from online_regis1.tbl_omni_details_consolidated where parentid='".$this->parentid."' and data_city='".$this->data_city_cm."'";

		$websiteDetailsres = parent::execQuery($websiteDetails, $this->conn_idc);
		$custid='';
		if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
 		{
	 		
	 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
				{
			 		$domain_name= $websiteDetailsrow['website'];
			 		$custid= $websiteDetailsrow['website_customerid']; 
			 		$own_cust_website= $websiteDetailsrow['own_cust_website']; 
				}

		}
		if($custid==''){
			$custid=$this->createCustomer();


		} 
		//$domain_name='ganeshrj1.in';// for dev
		if($custid=='' || $domain_name==''){ 
			 	$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Website Customerid Or Domain Name Not Found";
				return json_encode($result_msg_arr);
		}
		$emailDetails="select * from tbl_omni_email_details where parentid='".$this->parentid."' and approved=0 and version='".$this->version."'";
  
		$emailDetailsres = parent::execQuery($emailDetails, $this->conn_idc);
		if($emailDetailsres && mysql_num_rows($emailDetailsres)>0)
 		{
			$no_of_emails=0;
 			while($emailDetailsrow = mysql_fetch_assoc($emailDetailsres)){ 
 				$no_of_emails=$emailDetailsrow['num_of_emails']; 
 			}
 			$sqlfinance="select sum(app_amount) as app_amount from payment_snapshot where parentid='".$this->parentid."' and campaignid='82' and version='".$this->version."' group by parentid";
				$sqlfinanceres = parent::execQuery($sqlfinance, $this->conn_finance);
				$app_amount=0;
				$total_price=$no_of_emails*$email_price; //total email price
				if($sqlfinanceres && mysql_num_rows($sqlfinanceres)>0)
		 		{
			 		while($sqlfinancerow=mysql_fetch_assoc($sqlfinanceres))
						{
							$app_amount=intval(round($sqlfinancerow['app_amount'])); 
						}
				}
				//$app_amount=10000;// for dev
				if($total_price>$app_amount){
					//exiting if price is more than money recieved.
					$sub="Email Price Mismatch appamt=".$app_amount." need price=".$total_price;
					$this->sendMail($sub); 
					$this->insertIntoFailureLog(7,$sub); 
 					$result_msg_arr['error']['code'] = 1;
		  			$result_msg_arr['error']['msg'] = 'Error - No Money In Finance';  
		  			echo json_encode($result_msg_arr); exit ; 

				} 

 					//$no_of_emails=1;// for dev
					$domain_name=$this->clean_http($domain_name); 
					 $parameter = "website=".$domain_name."&custid=".urlencode($custid)."&noofaccounts=".$no_of_emails."&action=addforemail"; 
					$url =$this->meurl.'/business/domainServices.php?action=addforemail&';
					$res_email_id=$this->curlCall($url,$parameter,'post'); 
					 $res_email_id_arr=json_decode($res_email_id,1);
					if(strtolower($res_email_id_arr['status'])!='error'){
			 			
			 			$sqlupt="update tbl_omni_email_details set approved=1,approved_time='".date('Y-m-d H:i:s')."' where parentid='".$this->parentid."' and version='".$this->version."'";   
			 			$updtqry = parent::execQuery($sqlupt, $this->conn_idc);
			 			$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
			 				 					parentid        = '".$this->parentid."',
			 				 					data_city       = '".$this->data_city_cm."',
			 				 					email_taken='yes',
			 				 					email_type='direct-i',
			 				 					email_no_taken='".$no_of_emails."',
			 				 					email_approveddate='".date('Y-m-d H:i"s')."',
			 				 					email_creation_status='pass'
			 				 					ON DUPLICATE KEY UPDATE 
			 				 					email_taken='yes',
			 				 					email_type='direct-i',
			 				 					email_no_taken='".$no_of_emails."',
			 				 					email_approveddate='".date('Y-m-d H:i"s')."',
			 				 					email_creation_status='pass'"; 
			 			$res_ins_website = parent::execQuery($sql_omni_mapping, $this->conn_idc);

			 			$this->createWebsiteLog($url,$parameter,$res_email_id,'7','Pass'); 
			 			
			 			if($own_cust_website=='no'){  
			 			 $parameter = "website=".$domain_name."&action=addmxrecord"; 
			 			$url =$this->meurl.'/business/domainServices.php?action=addmxrecord&';
			 			$res_email_mx_id=$this->curlCall($url,$parameter,'post');
			 			$this->createWebsiteLog($url,$parameter,$res_email_mx_id,'8','Pass'); 
			 			}
			 			else
			 				$this->createWebsiteLog('Not Needed As Customer Own Website','Not Needed As Customer Own Website','Not Needed As Customer Own Website','8','Pass'); 
 
		 			  	$result_msg_arr['error']['code'] = 0;
		 			  	$result_msg_arr['error']['msg'] = 'Success!!';
		 			  	echo json_encode($result_msg_arr); exit ;
					  }
			  	 		else{
			  	 				
			  	 				foreach($_SERVER as $key=>$val)
			  	 				$serverdetiail.="<br>".$key.'=>'.$val;
			  	 				$headers  = 'MIME-Version: 1.0' . "\r\n";
			  	 				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			  	 				$headers .= 'From: apache@justdial.com';
			  	 				mail ('prameshjha@justdial.com' , 'Email Creation Failed '.$this->parentid, 'Email Creation Issue');
			  	 				
			  	 				/*$apiDetails="update tbl_omni_email_details set approved_status='".$this->mysql_real_escape_custom($res_email_id)."' where parentid='".$this->parentid."'";
			  	 					$apiDetailsres = parent::execQuery($apiDetails, $this->conn_idc);*/
			  	 					
			  	 					$this->createWebsiteLog($url,$parameter,$res_email_id,'7','Fail'); 
			  	 					$this->insertIntoFailureLog(7,$res_email_id); 
			  	 					$result_msg_arr['error']['code'] = 1;
			   			  			$result_msg_arr['error']['msg'] = 'Error'; 
			   			  			echo json_encode($result_msg_arr); exit ;  
			  	 					

			  		}
				
	 	}
	 	else{
 				$result_msg_arr['error']['code'] = 1;
	  			$result_msg_arr['error']['msg'] = 'No Data To Process';  
	  			echo json_encode($result_msg_arr); exit ; 
	 	}
 	} 

 	function sendMail($subject){
 			foreach($_SERVER as $key=>$val)
 			$serverdetiail.="<br>".$key.'=>'.$val;
 			$headers  = 'MIME-Version: 1.0' . "\r\n";
 			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 			$headers .= 'From: apache@justdial.com';
 			mail ('rohitkaul@justdial.com,neelakandan@justdial.com,prameshjha@justdial.com' , $subject.$this->parentid,$subject);
 	}

	function getEmailPricingDirecti(){
		$url =$this->meurl.'/business/domainServices.php?action=getprice';
		$res_price=$this->curlCallLive($url);
		$json_price=json_decode($res_price,true);
		$price=0;
		
		
		$price=$json_price['eeliteus']['email_account_ranges'];
		$price_arr=array();
		
		foreach ($price as $key => $value) {
			
			//$price_arr[$key]['price']=$value['add']['12']; for based on no of mails
			$price_arr=$value['add']['12']; 
			break; 
		}
		
      $pricesql="select * from online_regis1.omni_add_ons_pricing where campaignid='82' and camp_type='1'";
      $gprice=0;
      $priceres = parent::execQuery($pricesql, $this->conn_idc);
      if($priceres && mysql_num_rows($priceres)>0){ 
	      	while($rowprice=mysql_fetch_assoc($priceres)){
	      		$gprice=$rowprice['price_upfront'];
	      	}	
		 }
		if($price==0){
				$result_msg_arr['error']['code'] = 2;
				$result_msg_arr['error']['msg'] = "Price Not Found";
				return json_encode($result_msg_arr);
		}
		else{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Success';
			$result_msg_arr['data']['direct-i']['price'] = $price_arr; 
			$result_msg_arr['data']['google']['price'] =  ($gprice/12);    
			return json_encode($result_msg_arr);  
		}
	}

	function checkOmniDependent($live=0,$type=2){
		$conn=$this->conn_finance;
		if($live==1){
			$tbl="dependant_campaign_details";
			 $conn=$this->conn_finance;
		}
		else{
		$tbl="dependant_campaign_details_temp";
		 	$conn=$this->conn_temp;	
		}
		$sqlcheck="select * from $tbl where parentid='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sqlcheck, $conn);
		$cnt=0;
		if($res && mysql_num_rows($res)){
			$returnarr['msg']['dependent_present']=1;
			while($row = mysql_fetch_assoc($res)){
				$returnarr['data'][$cnt]['combo_type']=$row['combo_type'];
				$returnarr['data'][$cnt]['pri_campaignid']=$row['pri_campaignid'];
				$returnarr['data'][$cnt]['dep_campaignid']=$row['dep_campaignid'];
				$cnt++;
				
			}
			return $returnarr;
		}
		else{
			$returnarr['msg']['dependent_present']=0;
			return $returnarr;
		}	
	}

	function tempTomain($genio_lite_campaign = null){
		/*$dependend=false;
		$checkdept=$this->checkOmniDependent(0,2);
		if($checkdept['msg']['dependent_present']=='1' || $checkdept['msg']['dependent_present']==1){

			$dependend=true;
		}*/

		$sqlcheck="select * from tbl_omni_email_details where parentid='".$this->parentid."' and version='".$this->version."'";
		$checkmain_res = parent::execQuery($sqlcheck, $this->conn_idc);
		if($checkmain_res && mysql_num_rows($checkmain_res)>0){
			$result_msg_arr['error']['code'] = 0;// as richie is going forward in jda
			$result_msg_arr['error']['msg'] = "Data Already Present For The Version"; 
			
			if(count($genio_lite_campaign)>0)
				return $result_msg_arr;
			else
			{
				echo json_encode($result_msg_arr);exit;
			}
		} 
	 	
			$res_ins_email=true;
			$emailDetails="select * from tbl_omni_email_details_temp where parentid='".$this->parentid."' and version='".$this->version."'";
			$emailDetailsres = parent::execQuery($emailDetails, $this->conn_temp);
			if($emailDetailsres && mysql_num_rows($emailDetailsres)>0)
	 		{
		 		 	$checktemp = "select * from tbl_companymaster_finance_temp where parentid='".$this->parentid."' and campaignid='82' and recalculate_flag=1";
		 			$checktempres = parent::execQuery($checktemp, $this->conn_temp);
		 			if(!mysql_num_rows($checktempres)>0 && !array_key_exists("82",$genio_lite_campaign)){
		 				
		 				$result_msg_arr['error']['code'] = 1;
		 				$result_msg_arr['error']['msg'] = "No Finance Details";
		 				echo json_encode($result_msg_arr);exit;
		 			}
		 			
		 		while($emailDetailsrow=mysql_fetch_assoc($emailDetailsres))
					{
				 		$sql_ins_email = "INSERT INTO tbl_omni_email_details set
					 					parentid='".$emailDetailsrow['parentid']."',
					 					version  	= '".$this->version."',
					 					num_of_emails='".$emailDetailsrow['num_of_emails']."',
					 					email_type  	= '".$emailDetailsrow['email_type']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					admin_username  	= '".$emailDetailsrow['admin_username']."',
					 					added_by  	= '".$emailDetailsrow['added_by']."',
					 					email_acc_count = '".$emailDetailsrow['email_acc_count']."',
										email_acc_price = '".$emailDetailsrow['email_acc_price']."'
					 					ON DUPLICATE KEY UPDATE
					 					num_of_emails='".$emailDetailsrow['num_of_emails']."',
					 					email_type  	= '".$emailDetailsrow['email_type']."',
					 					admin_username  	= '".$emailDetailsrow['admin_username']."',
					 					added_time  	= '".date('Y-m-d H:i:s')."',
					 					added_by  	= '".$emailDetailsrow['added_by']."',
					 					email_acc_count = '".$emailDetailsrow['email_acc_count']."',
										email_acc_price = '".$emailDetailsrow['email_acc_price']."'";
						$res_ins_email = parent::execQuery($sql_ins_email, $this->conn_idc);
					
					$sql_omni_mapping = "INSERT INTO online_regis1.tbl_omni_details_consolidated set
					 					parentid        = '".$this->parentid."',
					 					data_city       = '".$this->data_city_cm."',
					 					email_taken  	= 'yes',
					 					email_type	  	= '".$emailDetailsrow['email_type']."',
					 					email_no_taken  	= '".$emailDetailsrow['num_of_emails']."'
					 					ON DUPLICATE KEY UPDATE
					 					email_taken  	= 'yes',
					 					email_type	  	= '".$emailDetailsrow['email_type']."',
					 					email_no_taken  	= '".$emailDetailsrow['num_of_emails']."'";
					$res_ins_email = parent::execQuery($sql_omni_mapping, $this->conn_idc);
 
				}
			}
			if($res_ins_email){
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Success";
				//echo json_encode($result_msg_arr);exit;
			}
			else{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Error";
				//echo json_encode($result_msg_arr);exit;
			}
		
			if(count($genio_lite_campaign)>0)
				return $result_msg_arr;
			else
			{
				echo json_encode($result_msg_arr);exit;
			}
	}

	function switchBasedSelection(){
		return ;
		// stop email booking 
			$emailDetails="select * from tbl_omni_email_details where parentid='".$this->parentid."' and approved=0 and version='".$this->version."'";
					$websiteDetailsres = parent::execQuery($emailDetails, $this->conn_idc);

			if($websiteDetailsres && mysql_num_rows($websiteDetailsres)>0)
	 		{
	 			$website='';

		 		while($websiteDetailsrow=mysql_fetch_assoc($websiteDetailsres))
					{
						if($websiteDetailsrow['email_type']=='direct-i'){
							
							echo $this->addNoOfEmails();

						}
						else if($websiteDetailsrow['email_type']=='google'){
							require_once('includes/googleEmail.php');
							$this->params['action']=1;
							$googleEmailObj = new googleEmail($this->params);
							echo $result = $googleEmailObj->registerGoogleSuite(); 
						}
						else{
							echo "Type Issue";
						}
					}
			}
	}
	
	function getCustomerDetailsbyEmail($email)
	{
		$url =$this->meurl.'/business/domainServices.php?action=getCustomerdetails&username='.$email;
		$custdetailsarrystr=$this->curlCall($url);		
		$custdetailsarry=json_decode($custdetailsarrystr,1);		
		return $custdetailsarry;		
	}
}
?>
