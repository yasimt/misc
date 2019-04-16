<?php

class downselldetailsClass extends DB {

    var $params         = null;
    var $dataservers    = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
		$this->params		= $params;
        $this->setServers();
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Function to set DB connection objects
     */
    function setServers() {
        global $db;
        $conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
        $this->conn_iro     = $db[$conn_city]['iro']['master'];             // 67.213 - db_iro
        $this->conn_tme_jds = $db[$conn_city]['tme_jds']['master'];         // 67.213 - tme_jds
        $this->conn_local   = $db[$conn_city]['d_jds']['master'];           // 67.213 - d_jds
        $this->conn_idc     = $db[$conn_city]['idc']['master'];             // 6.52 online_regis_xx
        $this->conn_fnc     = $db[$conn_city]['fin']['master'];             // 67.215 db_finance
        $this->conn_budget  = $db[$conn_city]['db_budgeting']['master'];    // 67.215 db_budget
        
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * to fetch the actual employee city
     */
    function getEmpCity($empid) {
        $url="http://".$this->sso_ip."/hrmodule/employee/fetch_employee_info/$empid";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resultString = curl_exec($ch);
        $data=  json_decode($resultString,TRUE);
        curl_close($ch);
        return $data['data']['city'];
    }
    
    public function ShowDownselNotData(){
		$data_arr       = 	array();
		$cond ='';
		$id   = '';
		if($this->params['parentid'] != '' && $this->params['version']!=''){
			$cond 			.= " AND parentid='".$this->params['parentid']."' AND version ='".$this->params['version']."'";
			 $query			=	"SELECT * FROM online_regis.downsell_trn WHERE delete_flag='0' AND status=0 ".$cond." ORDER BY id DESC";
			$con 			= 	parent::execQuery($query,$this->conn_idc);
			$num_Rows		=	parent::numRows($con);
			if($num_Rows > 0){
				while ($res = parent::fetchData($con)) {
					$response['data']	=	$res;
				}
				$id .=" AND id !='".$response['data']['id']."'";
				$response['errorcode']		=	'0';
				$response['errorStatus']	=	'Data Found To APPROVE/REJECT';
			}else{
				$getApproved	=	"SELECT * FROM online_regis.downsell_trn WHERE parentid='".$this->params['parentid']."' AND version ='".$this->params['version']."' ORDER BY id DESC LIMIT 1";
				$con1 			= 	parent::execQuery($getApproved,$this->conn_idc);
				$num_Rows1		=	parent::numRows($con1);
				if($num_Rows1 > 0){
					$j = 0;
					while ($res1 = parent::fetchData($con1)) {
						$response['data']	=	$res1;
						if($res1['downsel_type'] != 'tenure'){
							$calc		 		= round((($res1['proposed_amt']/$res1['actual_amt'])*100),2);
				        	$disc_perc	 		= round((100-$calc),2);
				        	$response['data'][$j]['disc_per'] = $disc_perc.'%';
						}else{
							$response['data'][$j]['disc_per'] = '-';
						}
						$j++;
					}
				}
				$response['errorcode']		=	'1';
				$response['errorStatus']	=	'No Data to APPROVE/REJECT';
			}
		}
		// Srch handling
		$srchCity	=	'';
		$srchmod	=	'';
		$srchuser	=	'';
		$srchdata	=	'';
		$srchDate	=	'';
		$srchFinal = '';
		if($this->params['srchArr']['city'] != "" && $this->params['srchArr']['city'] != "All")
			$srchCity .=" AND data_city ='".$this->params['srchArr']['city']."'";
		if($this->params['srchArr']['module'] != "" && $this->params['srchArr']['module'] != "All")
			$srchmod .=" AND source ='".$this->params['srchArr']['module']."'";
		if($this->params['srchArr']['users'] != "")
			$srchuser .=" AND updated_by ='".$this->params['srchArr']['users']."'";
		if($this->params['srchArr']['Srch'] != "")
			$srchdata .=" AND (parentid  Like '%".$this->params['srchArr']['Srch']."%' OR company_nm like '%".$this->params['srchArr']['Srch']."%' OR cust_id like  '%".$this->params['srchArr']['Srch']."%' OR cust_name like '%".$this->params['srchArr']['Srch']."%')";
		if($this->params['srchArr']['startDate'] != "" && $this->params['srchArr']['endDate'] != "")
			$srchDate .=" AND (DATE(created_at) >='".date("Y-m-d",strtotime($this->params['srchArr']['startDate']))."' AND DATE(created_at) <='".date("Y-m-d",strtotime($this->params['srchArr']['endDate']))."')";
		$srchFinal .= $srchCity.$srchmod.$srchuser.$srchdata.$srchDate;
		// Srch handling

		$condition ='';
		if($this->params['status'] != 0 && $this->params['status'] != 3)	
			$condition .= " status='".$this->params['status']."' AND delete_flag !=1";//approved and rejected --  AND updated_by ='".$this->params['empcode']."'
		else if($this->params['status'] == 3)
			$condition .= " delete_flag =1";//cancelled
		else
			$condition .= " status='".$this->params['status']."' AND delete_flag!=1";//pending


		$query1			=	"SELECT * FROM online_regis.downsell_trn Where ".$condition." ".$id." ".$srchFinal." ORDER BY id DESC LIMIT 100";
		$con1 			= 	parent::execQuery($query1,$this->conn_idc);
		$num_Rows1		=	parent::numRows($con1);
		// count Part
		$pending1			=	"SELECT * FROM online_regis.downsell_trn Where  status=0 AND delete_flag!=1 ".$id." ".$srchFinal;
		$pencon1 			= 	parent::execQuery($pending1,$this->conn_idc);
		$pencon1num			=	parent::numRows($pencon1);
		$approved1			=	"SELECT * FROM online_regis.downsell_trn Where  status=1 AND delete_flag !=1".$id." ".$srchFinal; // AND updated_by ='".$this->params['empcode']."'
		$appcon1 			= 	parent::execQuery($approved1,$this->conn_idc);
		$appcon1num			=	parent::numRows($appcon1);
		$rejroved1			=	"SELECT * FROM online_regis.downsell_trn Where  status=2 AND delete_flag !=1".$id." ".$srchFinal; // AND updated_by ='".$this->params['empcode']."'
		$rejcon1 			= 	parent::execQuery($rejroved1,$this->conn_idc);
		$rejcon1num			=	parent::numRows($rejcon1);
		$canoved1			=	"SELECT * FROM online_regis.downsell_trn Where delete_flag =1 ".$id." ".$srchFinal;
		$cancon1 			= 	parent::execQuery($canoved1,$this->conn_idc);
		$cancon1num			=	parent::numRows($cancon1);
		// count Part
		if($num_Rows1 > 0){
			$i=	0;
			while ($res1 = parent::fetchData($con1)) {
				$response['fulldata'][$i]	=	$res1;	
				if($res1['downsel_type'] != 'tenure'){
					$calc		 = round((($res1['proposed_amt']/$res1['actual_amt'])*100),2);
		        	$disc_perc	 = round((100-$calc),2);
		        	$response['fulldata'][$i]['disc_per'] = $disc_perc.'%';
		        }else{
					$response['fulldata'][$i]['disc_per'] = '-';
				}
				$i++;
			}
			$response['fullCode']		=	'0';
			$response['full_count']		=	$num_Rows1;
			$response['count']			=	$i;
			$response['pending']		=	$pencon1num;
			$response['approved']		=	$appcon1num;
			$response['rejected']		=	$rejcon1num;
			$response['cancelled']		=	$cancon1num;
			$response['fullStatus']		=	'Data Found';	
		}else{
			$response['fullCode']		=	'1';
			$response['fullStatus']		=	'Data Not Found';	
		}
		return json_encode($response);
	}

	public function acceptDownsell(){
		$data_arr       = 	array();
		$getData		=	"SELECT * FROM online_regis.downsell_trn WHERE id='".$this->params['id']."'";
		$con 			= 	parent::execQuery($getData,$this->conn_idc);
		$num_Rows		=	parent::numRows($con);
		$data 			=	array();
		if($num_Rows > 0){
			while ($res = parent::fetchData($con)) {
					$data	=	$res;
			}
			$data_city  = $data['data_city'];
	        $city       = strtoupper($data_city);
	        switch ($city) {
	            case 'MUMBAI':
	                $API=MUMBAI_API_FIN_SERVICE;
	                break;
	            case 'DELHI':
	                $API=DELHI_API_FIN_SERVICE;
	                break;
	            case 'KOLKATA':
	                $API=KOLKATA_API_FIN_SERVICE;
	                break;
	            case 'BANGALORE':
	                $API=BANGALORE_API_FIN_SERVICE;
	                break;
	            case 'CHENNAI':
	                $API=CHENNAI_API_FIN_SERVICE;
	                break;
	            case 'PUNE':
	                $API=PUNE_API_FIN_SERVICE;
	                break;
	            case 'HYDERABAD':
	                $API=HYDERABAD_API_FIN_SERVICE;
	                break;
	            case 'AHMEDABAD':
	                $API=AHMEDABAD_API_FIN_SERVICE;
	                break;
	            case 'REMOTE':
	                $API=REMOTE_API_FIN_SERVICE;
	                break;
	            default:
	                $API=REMOTE_API_FIN_SERVICE;
	                break;
	        }
	        $url= $API."discount_api.php";
	        $p_data['parentid'] = $data['parentid'];
	        $p_data['version']  = $data['version'];
	        $p_data['calc']= round((($data['proposed_amt']/$data['actual_amt'])*100),2);
	        $p_data['disc_perc'] = round((100-$p_data['calc']),2);
	        $p_data['module']   = $data['source'];
	        $p_data['row']      = $data['id'];
	        $p_data['setUpfree'] = $data['setUpfree'];
	        $p_data['downsel_type'] = $data['downsel_type'];
	        if($data['downsel_type'] == 'tenure'){
				$p_data['proposed_tenure'] = $data['proposed_tenure'];
				$p_data['action']   = 2; // 0 for percentage, 2 for tenure
			}else{
				$p_data['action']   = 0; // 0 for percentage, 2 for tenure.
			}
	        $chk    = curl_init();
	        curl_setopt($chk, CURLOPT_URL,$url);
	        curl_setopt($chk, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($chk,CURLOPT_POST, TRUE);
	        curl_setopt($chk,CURLOPT_POSTFIELDS,$p_data);
	        $response   =curl_exec($chk);
	        $param      =json_encode($p_data);
	        $this->logTableUpdates('Downsell Approve '.$city,$url,$param,$response,$data_city);
	        if (stripos($response, '"isError":0') !== false) { // success
	        	$Updstat 	 = "UPDATE online_regis.downsell_trn SET status= 1,updated_at=NOW(),updated_by='".$this->params['empcode']."' WHERE id='".$data['id']."'";
	        	$resUpdstat  = parent::execQuery($Updstat,$this->conn_idc);
				$this->finBudgetDownsel($p_data['parentid'],$p_data['version'],$data_city,$p_data['row'],$p_data['module'],$this->params['empcode']);// Updation of the finance budget
	            $sendTo     =$data['cust_id'];
	            $sendFrom   =$data['updated_by'];
	            $source     =$data['source'];
	            $data_city  =$data['data_city'];
	            $data['updated_by']  =$this->params['empcode'];
	            $this->emailSmsCurl($sendTo,$sendFrom,$source,$data_city,$p_data['parentid'],'smsmail',1);
	            $this->sendNotification($data,1);
	            $response 					= json_decode($response);
	            $data_arr['errorcode'] 		= 0;
				$data_arr['errorstatus'] 	= $response->data->error;
				
	        }else{
	        	$response 					= json_decode($response);
	        	$data_arr['errorcode'] 		= 1;
				$data_arr['errorstatus'] 	= $response->data->error;
	        }
		}else{
			$data_arr['errorcode'] 		= 1;
			$data_arr['errorstatus'] 	= 'No Data for the given ID';
		}
		return json_encode($data_arr);
	}
	
	public function finBudgetDownsel($parid,$ver,$data_city,$rowid,$module,$empcode) {
        $city       = strtoupper($data_city);
        switch ($city) {
            case 'MUMBAI':
                $API='online_regis_mumbai';
                break;
            case 'DELHI':
                $API='online_regis_delhi';
                break;
            case 'KOLKATA':
                $API='online_regis_kolkata';
                break;
            case 'BANGALORE':
                $API='online_regis_bangalore';
                break;
            case 'CHENNAI':
                $API='online_regis_chennai';
                break;
            case 'PUNE':
                $API='online_regis_pune';
                break;
            case 'HYDERABAD':
                $API='online_regis_hyderabad';
                break;
            case 'AHMEDABAD':
                $API='online_regis_ahmedabad';
                break;
            case 'REMOTE':
                $API='online_regis_remote_cities';
                break;
            default:
                $API='online_regis_remote_cities';
                break;
        }
        if(strtolower($module)	==	'me'){
			$query = "SELECT campaignid,budget,original_budget,version FROM $API.tbl_companymaster_finance_temp where parentid='".$parid."' AND version='".$ver."'";
	        $result = parent::execQuery($query,$this->conn_idc);
	        $fin_campaignBid	=	array();
			if(count($result)	>	0){
				while($dataRow = parent::fetchData($result)){
					$fin_campaignBid[$dataRow['campaignid']]	=	$dataRow['budget'];
				}
			}
			$fin_campaignBid	=	json_encode($fin_campaignBid);
	        $queryUpd = "UPDATE online_regis.downsell_trn SET finance_campaignBud= '$fin_campaignBid',updated_at=NOW(),updated_by='".$empcode."' WHERE id='$rowid'";
	        $result   = parent::execQuery($queryUpd,$this->conn_idc);
		}else{
			$jdboxApi				=	$this->getJdboxApiUrl($data_city);
			$url					= 	$jdboxApi."getBudgetService.php";
			$p_data['p_id'] 		=	$parid;
			$p_data['vrsn']      	=	$ver;
			$p_data['rowid']      	=	$rowid;
			$p_data['data_city']    =	$data_city;
			$p_data['act']      	=	'upTmedown';
			$chk 					=	curl_init();
			curl_setopt($chk, CURLOPT_URL,$url);
			curl_setopt($chk, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($chk,CURLOPT_POST, TRUE);
			curl_setopt($chk,CURLOPT_POSTFIELDS,$p_data);
			$result   =curl_exec($chk); 
			curl_close($chk);	
		}
	}
	
	
	public function RejectDiscount() {
		$data_arr       = 	array();
		if($this->params['chkPar'] == 1){
			$getData		=	"SELECT * FROM online_regis.downsell_trn WHERE parentid='".$this->params['parentid']."' and version ='".$this->params['version']."' AND delete_flag!=1 AND status!=2 AND dealclose_flag!=2";	
		}else{
			$getData		=	"SELECT * FROM online_regis.downsell_trn WHERE id='".$this->params['id']."'";	
		}
		$con 			= 	parent::execQuery($getData,$this->conn_idc);
		$num_Rows		=	parent::numRows($con);
		$data 			=	array();
		if($num_Rows > 0){
			while ($res = parent::fetchData($con)) {
					$data	=	$res;
			}
			$data_city  = $data['data_city'];
	        $city       = strtoupper($data_city);
	        switch ($city) {
	            case 'MUMBAI':
	                $API=MUMBAI_API_FIN_SERVICE;
	                break;
	            case 'DELHI':
	                $API=DELHI_API_FIN_SERVICE;
	                break;
	            case 'KOLKATA':
	                $API=KOLKATA_API_FIN_SERVICE;
	                break;
	            case 'BANGALORE':
	                $API=BANGALORE_API_FIN_SERVICE;
	                break;
	            case 'CHENNAI':
	                $API=CHENNAI_API_FIN_SERVICE;
	                break;
	            case 'PUNE':
	                $API=PUNE_API_FIN_SERVICE;
	                break;
	            case 'HYDERABAD':
	                $API=HYDERABAD_API_FIN_SERVICE;
	                break;
	            case 'AHMEDABAD':
	                $API=AHMEDABAD_API_FIN_SERVICE;
	                break;
	            case 'REMOTE':
	                $API=REMOTE_API_FIN_SERVICE;
	                break;
	            default:
	                $API=REMOTE_API_FIN_SERVICE;
	                break;
	        }
	        $updt_by 			= $this->params['empcode'];
	        $url     			= $API."discount_revert_api.php";
	        $p_data['parentid'] = $data['parentid'];
	        $p_data['version']  = $data['version'];
	        $p_data['city']     = $data['data_city'];
	        $p_data['requester']= $updt_by;
	        $p_data['row']      = $data['id'];
	        $chk    			= curl_init();
	        curl_setopt($chk, CURLOPT_URL,$url);
	        curl_setopt($chk, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($chk,CURLOPT_POST, TRUE);
	        curl_setopt($chk,CURLOPT_POSTFIELDS,$p_data);
	        $response   		= curl_exec($chk);
	        curl_close($chk);
	        $param      		= json_encode($p_data);
	        $this->logTableUpdates('Downsell Revert '.$city,$url,$param,$response,$data_city);
	        if (stripos($response, '"isError":0') !== false) {
	        	$Updstat 	 = "UPDATE online_regis.downsell_trn SET status= 2,updated_at=NOW(),updated_by='".$this->params['empcode']."' WHERE id='".$data['id']."'";
	        	$resUpdstat  = parent::execQuery($Updstat,$this->conn_idc);
	            $sendTo     =$data['cust_id'];
	            $sendFrom   =$data['updated_by'];
	            $source     =$data['source'];
	            $data_city  =$data['data_city'];
	            $data['updated_by']  =$this->params['empcode'];
	            if($this->params['chkPar'] == 1){
					$sval	=	4;
				}else{
					$sval	=	3;
				}
	            $this->emailSmsCurl($sendTo,$sendFrom,$source,$data_city,$p_data['parentid'],'mail',$sval);
	            $this->sendNotification($data,$sval);
	            $data_arr['errorcode'] 		= 0;
				$data_arr['errorstatus'] 	= 'Rejected Successfully!';
	        }else{
	        	$response 					= json_decode($response);
	        	$data_arr['errorcode'] 		= 2;
				$data_arr['errorstatus'] 	= $response->data->error;
	        }
		}else{
			$data_arr['errorcode'] 		= 1;
			$data_arr['errorstatus'] 	= 'No Discount found for this Contract';
		}
		return json_encode($data_arr);
    }
	
	public function RejectbeforeApproval() {
		$data_arr       = 	array();
		$getData		=	"SELECT * FROM online_regis.downsell_trn WHERE id='".$this->params['id']."'";
		$con 			= 	parent::execQuery($getData,$this->conn_idc);
		$num_Rows		=	parent::numRows($con);
		$data 			=	array();
		if($num_Rows > 0){
			while ($res = parent::fetchData($con)) {
					$data	=	$res;
			}
		}
		$Updstat 	 = "UPDATE online_regis.downsell_trn SET status= 2,updated_at=NOW(),updated_by='".$this->params['empcode']."' WHERE id='".$this->params['id']."'";
	    $resUpdstat  = parent::execQuery($Updstat,$this->conn_idc);
	    if($resUpdstat){
				$sendTo     =$data['cust_id'];
	            $sendFrom   =$data['updated_by'];
	            $source     =$data['source'];
	            $data_city  =$data['data_city'];
	            $this->emailSmsCurl($sendTo,$sendFrom,$source,$data_city,$data['parentid'],'mail',2);
	            $this->sendNotification($data,2);
	            $data_arr['errorcode'] 		= 0;
				$data_arr['errorstatus'] 	= 'Rejected Successfully!';
		}else{
				$data_arr['errorcode'] 		= 1;
				$data_arr['errorstatus'] 	= 'Request not Rejected!';
		}
		return json_encode($data_arr);
	}
	   
	   
    public function logTableUpdates($source,$api,$param,$response,$data_city) {
        $jdboxApi=$this->getJdboxApiUrl($data_city);
        $url= $jdboxApi."getBudgetService.php";
        $p_data['source']   =$source;
        $p_data['api']      =$api;
        $p_data['param']    =$param;
        $p_data['response'] =$response;
        $p_data['act']      ='logs';
        $chk = curl_init();
        curl_setopt($chk, CURLOPT_URL,$url);
        curl_setopt($chk, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chk,CURLOPT_POST, TRUE);
        curl_setopt($chk,CURLOPT_POSTFIELDS,$p_data);
        $result   =curl_exec($chk);
        curl_close($chk);
    }

	public function emailSmsCurl($sendTo,$sendFrom,$source,$data_city,$parentid,$act,$status) {
        $jdboxApi			=	$this->getJdboxApiUrl($data_city);
        $url 				= $jdboxApi."sendNotifications.php";
        $p_data['from']     =$sendFrom;
        $p_data['to']       =$sendTo;
        $p_data['pid']      =$parentid;
        $p_data['city']     =$data_city;
        $p_data['src']      =$source;
        $p_data['act']      =$act;
        $p_data['st']       =$status;
        $chk = curl_init();
        curl_setopt($chk, CURLOPT_URL,$url);
        curl_setopt($chk, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($chk,CURLOPT_POST, TRUE);
        curl_setopt($chk,CURLOPT_POSTFIELDS,$p_data);
        $result   =curl_exec($chk);
        curl_close($chk);
    }

    function sendNotification($sendNotArr,$status) {
		$curlParams 					=  array();
		$authToken  					=  "ZedA76A%'>j0~'z]&w7bR64{s";
		$parentid						=	$sendNotArr['parentid'];
		if($status == 1)
			$not_msg						=  "Your downsell request for contract : ".$sendNotArr['company_nm']." has been Approved by ".$sendNotArr['updated_by'].".";
		else if($status == 2)
			$not_msg						=  "Your downsell request for contract : ".$sendNotArr['company_nm']." has been Rejected by ".$sendNotArr['updated_by'].".";
		else if($status == 3 || $status == 4)
			$not_msg						=  "Your downsell request for contract : ".$sendNotArr['company_nm']." has been Reverted  by ".$sendNotArr['updated_by'].".";
        $curlParams['url'] 				=  "http://192.168.20.237:8080/api/sendNotification.php?auth_token=".urlencode($authToken)."&not_id=10&empcode=".$sendNotArr['cust_id']."&not_msg=".urlencode($not_msg);
		$curlParams['formate'] 			=  'basic';
		$curlParams['method'] 			=  'POST';
		$curlParams['postData'] 		=  json_encode($params);
		$curlParams['headerJson'] 		=  'json';
		$singleCheck					=	$this->curlCall($curlParams);
		return $singleCheck;
    }

    function getJdboxApiUrl($data_city) {
        $city=  strtoupper($data_city);
        switch ($city) {
            case 'MUMBAI':
                $api='http://'.MUMBAI_CS_JDBOX_URL.'services/';
                break;
            case 'DELHI':
                $api='http://'.DELHI_CS_JDBOX_URL.'services/';
                break;
            case 'KOLKATA':
                $api='http://'.KOLKATA_CS_JDBOX_URL.'services/';
                break;
            case 'BANGALORE':
                $api='http://'.BANGALORE_CS_JDBOX_URL.'services/';
                break;
            case 'CHENNAI':
                $api='http://'.CHENNAI_CS_JDBOX_URL.'services/';
                break;
            case 'PUNE':
                $api='http://'.PUNE_CS_JDBOX_URL.'services/';
                break;
            case 'HYDERABAD':
                $api='http://'.HYDERABAD_CS_JDBOX_URL.'services/';
                break;
            case 'AHMEDABAD':
                $api='http://'.AHMEDABAD_CS_JDBOX_URL.'services/';
                break;
            case 'REMOTE':
                $api='http://'.REMOTE_API_JDOX_SERVICE.'services/';
                break;

            default:
                $api='http://'.REMOTE_CS_JDBOX_URL.'services/';
                break;
        }
        return $api;
    }
    
    function curlCall($param) {
        $retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";

        # Init Curl Call #
        $ch = curl_init();
        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($param['postData']))                                                                       
				); 
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			} else if($param['headerJson']	==	'withHead') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data',
					'HR-API-AUTH-TOKEN:'.$param['authToken']
				));
			}
		}
		// echo '--retVCal=params==<pre>';print_r($param);
        $retVal = curl_exec($ch);
        // echo '--retVCal===<pre>';print_r($retVal);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
    }

}
