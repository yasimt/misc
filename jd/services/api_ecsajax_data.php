<?
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once("../config.php");
require_once('includes/class_send_sms_email.php');

class ecsAjaxData extends DB{

    var $dataservers  = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
    var $remote     = 0;

    function __construct($params)
  {
        GLOBAL $db;

        $ucode       = trim($params['empcode']);
        $tmecode     = trim($params['ucode']);
        $data_city   = trim($params['data_city']);
        $parentid    = trim($params['parentid']);
        $action_flag = trim($params['action_flag']);
        $flag        = trim($params['flag']);
        $compname    = trim($params['compname']);
        $tmename     = trim($params['tmename']);
        $req_source  = trim($params['req_source']);
        $ecs_skip    = trim($params['ecs_skip']);
        $st          = trim($params['st']);

        if(!isset($ucode) || (isset($ucode) && $ucode == "")) {          
            $msg = "Please send user code.";
            echo json_encode($this->sendDieMsg($msg));
            die;
        }
        else if($data_city==''){
            $msg = "data city is blank.";
            echo json_encode($this->sendDieMsg($msg));
            die;
        }

        $this->ucode       = $ucode;
        $this->tmecode     = $tmecode;
        $this->data_city   = $data_city;
        $this->parentid    = $parentid;
        $this->action_flag = $action_flag;
        $this->flag        = $flag;
        $this->compname    = $compname;
        $this->tmename     = $tmename;
        $this->req_source  = $req_source;
        $this->ecs_skip    = $ecs_skip;
        $this->st          = $st;

        $this->emailsms_obj = new email_sms_send($db,strtolower($data_city));

        $this->setServers();

        $this->apiurl_city      = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
    }


    public function processAjaxData(){
        $resData    =   array();
        $ret = 'failed';

        if($this->action_flag != '25' && $this->action_flag != '26' && $this->action_flag != '35' && $this->action_flag != '38') {

            $getEcsStatusQry = "SELECT parentid from db_ecs.ecs_mandate where parentid = '".$this->parentid."' and deactiveFlag=0 and ecs_stop_flag = 0";
            $con_sel = parent::execQuery($getEcsStatusQry,$this->conn_fin);
            $getEcsStatusres = parent::numRows($con_sel);

            if($getEcsStatusres > 0){                    
                $active_mandate = 'ECS';
            }
            else { // ECS not active , check SI
                $getSIStatusQry = "SELECT parentid from db_si.si_mandate where parentid = '".$this->parentid."' and deactiveFlag=0 and ecs_stop_flag = 0";
                $getSIStatusres = parent::execQuery($getSIStatusQry,$this->conn_fin);

                if ($getSIStatusres && parent::numRows($getSIStatusres) > 0 ) {
                    $active_mandate = 'CCSI';
                }else { // SI not active. Nothing is active
                    $getSIStatusQry = "SELECT parentid from db_ecs.ecs_mandate_stopped WHERE bill_flag=0 AND ignore_flag=0 AND parentid='".$this->parentid."'";
                    $getSIStatusres = parent::execQuery($getSIStatusQry,$this->conn_fin);

                    if ($getSIStatusres && parent::numRows($getSIStatusres) > 0 ) {
                        $active_mandate = 'ECS';
                    }else{
                        $active_mandate = 'NONE';
                    }
                }
            }

            if($active_mandate == 'NONE') {
                return  $active_mandate;
            }
        }

        if($this->flag == 'old') {
            $insertSql = "INSERT INTO tbl_ecs_retention_action SET 
                      parentid = '".$this->parentid."',
                      tmecode = '".$this->ucode."',
                      action_flag = '".$this->action_flag."',
                      insertdate = now(),
                      updatedate = now()
                      ON DUPLICATE KEY UPDATE 
                      tmecode = '".$this->ucode."',
                      action_flag = '".$this->action_flag."',
                      updatedate = now()";
            
            $insertRes = parent::execQuery($insertSql,$this->conn_local);
            
            if ($insertRes)
            {
                
                $insertLogSql = "INSERT INTO tbl_ecs_retention_action_log
                                  SET
                                  parentid = '".$this->parentid."',
                                  tmecode = '".$this->ucode."',
                                  action_flag = '".$this->action_flag."',
                                  insertdate = now(),
                                  updatedate = now()";
                $insertLogRes = parent::execQuery($insertLogSql,$this->conn_local);
            
                $ret = '1';
            } 
            else
            {
                $ret = '2';
            }
            
        }else if($this->flag == 'new'){
            $alloc_date_condition = '';
            $select_current_tmedetails = "SELECT * FROM d_jds.tbl_new_retention WHERE parentid='".$this->parentid."'";
            $select_current_tmedetails_Res = parent::execQuery($select_current_tmedetails,$this->conn_local);
            $select_current_tmedetails_Data = parent::fetchData($select_current_tmedetails_Res);
            $current_tmecode = $select_current_tmedetails_Data['tmecode'];
            $current_tmename = $select_current_tmedetails_Data['tmename'];
            
            if($current_tmecode == $this->tmecode){
                $alloc_date_condition = '';
            }else{
                $alloc_date_condition = 'allocated_date = NOW(),';
            }
            
            $ringing_cnt =  0;
            if($this->action_flag == 21) {
                $ringing_cnt =  1;
                $check_sql ="select action_flag,date(update_date) as update_date,ringing_count from tbl_new_retention where parentid='".$this->parentid."'";
                $check_obj = parent::execQuery($check_sql,$this->conn_local);

                while($val = parent::fetchData($check_obj)) {
                    if($val['action_flag'] == 21) {
                        if(date('Y-m-d') != $val['update_date']) {
                            $ringing_cnt = ++$val['ringing_count'];
                        }else {
                            $ringing_cnt = $val['ringing_count'];
                        }
                    }    
                }
            }

            if($this->action_flag !=9 && $this->action_flag !=24 && $this->action_flag !=35 && $this->action_flag !=38) {
                
                $insertSql = "INSERT INTO tbl_new_retention
                              SET
                              parentid = '".$this->parentid."',
                              tmecode = '".$this->tmecode."',
                              action_flag = '".$this->action_flag."',
                              companyname = '".addslashes(stripslashes($this->compname))."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              insert_date = now(),
                              repeat_call = 0,
                              stop_request_datetime ='',
                              state ='2',
                              data_city = '".$this->data_city."',
                              request_source = '".$this->req_source."',
                              ".$alloc_date_condition."
                              ringing_count = '".$ringing_cnt."',
                              update_date = now()
                              ON DUPLICATE KEY UPDATE 
                              stop_request_datetime ='',
                              ringing_count = '".$ringing_cnt."',
                              repeat_call = 0,
                              tmecode = '".$this->tmecode."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              action_flag = '".$this->action_flag."',
                              mecode='',
                              mename ='',
                              request_source = '".$this->req_source."',
                              ".$alloc_date_condition."
                              update_date = now()";

                $insertRes = parent::execQuery($insertSql,$this->conn_local);
                
                if ($insertRes)
                {
                    $insertLogSql = "INSERT INTO tbl_new_retention_log
                                      SET
                                      companyname = '".addslashes(stripslashes($this->compname))."',
                                      tmename = '".addslashes(stripslashes($this->tmename))."',
                                      parentid = '".$this->parentid."',
                                      tmecode = '".$this->tmecode."',
                                      action_flag = '".$this->action_flag."',
                                      request_source = '".$this->req_source."',
                                      insert_date = now()";

                    $insertLogRes = parent::execQuery($insertLogSql,$this->conn_local);
                    
                    if($this->action_flag == 5 || $this->action_flag == 23) {
                 
                        $sel_phone ="SELECT mobile,email,contact_person FROM tbl_companymaster_generalinfo where parentid='".$this->parentid."'";  

                        $mob_obj = parent::execQuery($sel_phone,$this->conn_iro);

                        while($val = parent::fetchData($mob_obj)) {
                             $mob = trim($val['mobile']);
                             $email = trim($val['email']);
                             $contract_person = trim($val['contract_person']);
                        }
                        
                        $mob_arr = explode(',', $mob);
                        $email_arr = explode(',',$email);
                        
                        if(count($mob_arr)>0){
                            $mobile_no =  $mob_arr[0];
                        } 
                        
                        if(count($email_arr)>0){
                            $email =  $email_arr[0];
                        }
                        
                        if($mobile_no != '') {

                            if($this->action_flag == 5) {
                            $message ="Dear Customer,
                                    As per our recent discussion & agreement terms, your ECS stands continued. We look forward to having a strong business relationship in days to come.

                                    Warm Regards,
                                    Team Justdial";
                            }else if($this->action_flag == 23) {
                                $message ="Dear Customer,
                                            With reference to your ECS Stop request, we tried to contact you multiple times but were unable to connect.

                                            Your ECS currently stands continued. Please contact us on 8888888888 to help us address your concerns.

                                            Regards,
                                            Team Justdial";
                            }
                            $sms_flag = $this->emailsms_obj->sendSMS($mobile_no, $message , 'tme');
                        }
                        
                        if($email != '') {
                            $dataCityArr = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','jaipur','coimbatore');
                            $dataCitymap = array('mumbai'=>'mumbai@justdial.com','delhi'=>'delhi@justdial.com','kolkata'=>'kolkata@justdial.com','bangalore'=>'bangalore@justdial.com','chennai'=>'chennai@justdial.com','pune'=>'pune@justdial.com','hyderabad'=>'hyderabad@justdial.com','ahmedabad'=>'ahmedabad@justdial.com','chandigarh'=>'chd@justdial.com','jaipur'=>'Jaipur@justdial.com','coimbatore'=>'coimbatore@justdial.com');     
                            $contractCity = $this->getContractDataCity($this->parentid, $this->conn_iro, $this->conn_local);                     
                            $contractCity = strtolower($contractCity);
                            if($contractCity==''){
                                $contractCity = DATA_CITY;
                            }   
                            if(in_array($contractCity,$dataCityArr)){
                                $sender_email = $dataCitymap[$contractCity];
                            }else{
                                $sender_email = 'customersuppport@justdial.com';
                            }                       
                            if($this->action_flag == 5) {
                                $message = "Dear Customer,<br><br>
                                As per our recent discussion & agreement terms, your ECS stands continued. We look forward to having a strong business relationship in days to come.<br><br>

                                Warm Regards,<br>
                                Team Justdial";
                            }else if($this->action_flag == 23) {
                                $message ="Dear Customer,<br><br>
                                With reference to your ECS Stop request, we tried to contact you multiple times but were unable to connect.<br><br>
            
                                Your ECS currently stands continued. Please contact us on 8888888888 to help us address your concerns.<br><br>

                                Regards,<br>
                                Team Justdial";

                            }

                            $email_flag = $this->emailsms_obj->sendEmail($email, $sender_email , "Response to ECS Stop Request" , $message , 'tme');                            
                        }
                    }
                    $ret = '1';
                } 
                else
                {
                    $ret = '2';
                }
            }else if($this->action_flag ==24) {
                $start_date = date("d-m-Y"); 
                $still_date = date("d-m-Y",strtotime($start_date." +".$this->ecs_skip." month"));
                
                if($this->apiurl_city!='remote'){
                    $pause_api ="http://".constant(strtoupper($this->apiurl_city)."_CS_API")."/api_services/ecs_si_stop_api.php?parentid=".$parentid."&flag=pause&pause_start=".$start_date."&pause_end=".$still_date."&done_by=".$this->ucode;
                }
                else {
                    $pause_api ="http://".constant("REMOTE_CITIES_CS_API")."/api_services/ecs_si_stop_api.php?parentid=".$parentid."&flag=pause&pause_start=".$start_date."&pause_end=".$still_date."&done_by=".$this->ucode;
                }

                $res = $this->call_api_curl('',$pause_api);

                if(strtolower($res) == 'updated successfully'){
                    
                    $insertSql = "INSERT INTO tbl_new_retention
                              SET
                              parentid = '".$this->parentid."',
                              tmecode = '".$this->tmecode."',
                              action_flag = '".$this->action_flag."',
                              companyname = '".addslashes(stripslashes($this->compname))."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              insert_date = now(),
                              repeat_call = 0,
                              stop_request_datetime ='',
                              state ='2',
                              data_city = '".$this->data_city."',
                              ".$alloc_date_condition."
                              request_source = '".$this->req_source."',
                              ringing_count = '".$ringing_cnt."',
                              update_date = now()
                              ON DUPLICATE KEY UPDATE 
                              stop_request_datetime ='',
                              ringing_count = '".$ringing_cnt."',
                              ".$alloc_date_condition."
                              repeat_call = 0,
                              tmecode = '".$this->tmecode."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              action_flag = '".$this->action_flag."',
                              mecode='',
                              mename ='',
                              ecs_skip = '".$start_date." - ".$still_date."',
                              request_source = '".$this->req_source."',
                              update_date = now()";

                    $insertRes = parent::execQuery($insertSql,$this->conn_local);
                    
                    if ($insertRes)
                    {
                        $insertLogSql = "INSERT INTO tbl_new_retention_log
                                          SET
                                          companyname = '".addslashes(stripslashes($this->compname))."',
                                          tmename = '".addslashes(stripslashes($this->tmename))."',
                                          parentid = '".$this->parentid."',
                                          tmecode = '".$this->tmecode."',
                                          action_flag = '".$this->action_flag."',
                                          ecs_skip = '".$start_date." - ".$still_date."',
                                          request_source = '".$this->req_source."',
                                          insert_date = now()";
                        // $insertLogRes = $conn_local->query_sql($insertLogSql);
                        $insertLogRes = parent::execQuery($insertLogSql,$this->conn_local);
                        
                        $ret ='1';
                    }
            
                    /*CODE FOR MSG ********************************/

                    $ecs_dt = date("F Y",strtotime($start_date." +".$this->ecs_skip." month"));
        
                    $sel_phone ="SELECT mobile,email,contact_person FROM tbl_companymaster_generalinfo where parentid='".$this->parentid."'";  
                    $mob_obj = parent::execQuery($sel_phone,$this->conn_iro);

                    while($val = parent::fetchData($mob_obj)) {
                         $mob = trim($val['mobile']);
                         $email = trim($val['email']);
                         $contract_person = trim($val['contract_person']);
                    }
                    
                    $mob_arr = explode(',', $mob);
                    $email_arr = explode(',',$email);
                    
                    if(count($mob_arr)>0){
                        $mobile_no =  $mob_arr[0];
                    } 
                    
                    if(count($email_arr)>0){
                        $email =  $email_arr[0];
                    }
                    
                    if($mobile_no != '') {
                        
                        $message ="Dear Customer, As per your request, your next ECS will be processed on ".$ecs_dt.". Pls ensure your account is adequately funded,
                                    Warm Regards,
                                    Team Justdial";
                
                        $sms_flag = $this->emailsms_obj->sendSMS($mobile_no, $message , 'tme');
                    }
                        
                    if($email != '') {
                    
                        $message = "Dear Customer,<br><br>
                        As per your request, your next ECS will be processed on ".$ecs_dt.". Pls ensure your account is adequately funded,<br><br>

                        Warm Regards,<br>
                        Team Justdial";
                                                                    
                        $email_flag = $this->emailsms_obj->sendEmail($email, "customersuppport@justdial.com" , "confirmation" , $message , 'tme');
                    }
                    
                    /* ------------------------------- */
                }else {
                    $ret ='3';
                }
            }else if($this->action_flag == 35 || $this->action_flag == 38){
                $postData = array();
                $postData['edited_date'] = date("Y-m-d H:i:s"); 
                $postData['parentid'] = $this->parentid;
                $postData['mod_type'] = "TME_FBK"; 
                $postData['userid'] = $this->ucode; 
                $postData['data_city'] = $this->data_city; 
                
                if($this->action_flag == 35){
                    
                    // $rec_url =APP_URL."/api_dc/datacorrection_api.php";

                    if($this->apiurl_city!='remote'){
                        $rec_url =constant(strtoupper($this->apiurl_city)."_TME_URL")."/api_dc/datacorrection_api.php";
                    }
                    else {
                        $rec_url =constant("REMOTE_CITIES_TME_URL")."/api_dc/datacorrection_api.php";
                    }

                    $ch = curl_init($rec_url);
                    curl_setopt ($ch, CURLOPT_POST, true);
                    curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                    $content = curl_exec($ch);
                    $response = curl_getinfo($ch);
                    curl_close($ch); 
                    $result = json_decode($content,true); 

                }
                else if($this->action_flag == 38){
                    
                    $select_details = "SELECT companyname,city,full_address,mobile,email,website,contact_person FROM db_iro.tbl_companymaster_generalinfo where parentid = '".$this->parentid."'";

                    $select_details_Res = parent::execQuery($select_details,$this->conn_iro);
                    $select_details_Data = parent::fetchData($select_details_Res);

                    $email = "support.jdomni.com";
                    // $email = "ankit.kumar10@justdial.com";
                    $message ="Dear Team,<br><br>
                    
                                Parentid : ".$this->parentid."<br>
                                CompanyName : ".$select_details_Data['companyname']."<br>
                                Address : ".$select_details_Data['full_address']."<br>
                                Contact Person : ".$select_details_Data['contact_person']."<br>
                                Mobile Number : ".$select_details_Data['mobile']."<br><br>
                                This Customer has a Complaint Regarding Website Contract.<br>
                                Please contact the customer and resolve.<br><br>
                                Regards,<br>
                                Retention Team";

                    $email_flag = $this->emailsms_obj->sendEmail($email, "noreply@justdial.com" , "Complaint Regarding Website Contract." , $message , 'tme');
                    // $email_flag = 1;

                    if($email_flag == 1){
                        $result['erroCode'] = "1";
                    }else{
                        $result['erroCode'] = "0";
                    }
                }
                
                if($result['erroCode'] == "1"){
                    
                    $insertSql = "INSERT INTO tbl_new_retention
                              SET
                              parentid = '".$this->parentid."',
                              tmecode = '".$this->tmecode."',
                              action_flag = '".$this->action_flag."',
                              companyname = '".addslashes(stripslashes($this->compname))."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              insert_date = now(),
                              repeat_call = 0,
                              stop_request_datetime ='',
                              state ='2',
                              data_city = '".$this->data_city."',
                              request_source = '".$this->req_source."',
                              ".$alloc_date_condition."
                              update_date = now()
                              ON DUPLICATE KEY UPDATE 
                              stop_request_datetime ='',
                              repeat_call = 0,
                              insert_date = now(),
                              ".$alloc_date_condition."
                              tmecode = '".$this->tmecode."',
                              tmename = '".addslashes(stripslashes($this->tmename))."',
                              request_source = '".$this->req_source."',
                              action_flag = '".$this->action_flag."',
                              mecode='',
                              mename ='',
                              update_date = now()";

                    $insertRes = parent::execQuery($insertSql,$this->conn_local);
                    
                    if ($insertRes){
                            $insertLogSql = "INSERT INTO tbl_new_retention_log
                                              SET
                                              companyname = '".addslashes(stripslashes($this->compname))."',
                                              tmename = '".addslashes(stripslashes($this->tmename))."',
                                              parentid = '".$this->parentid."',
                                              tmecode = '".$this->tmecode."',
                                              action_flag = '".$this->action_flag."',
                                              request_source = '".$this->req_source."',
                                              insert_date = now()";

                            $insertLogRes = parent::execQuery($insertLogSql,$this->conn_local);
                            
                            if($this->action_flag == 35){
                                $insert_into_bcvtable = "INSERT INTO d_jds.tbl_business_closedown_validation SET 
                                                          companyname = '".addslashes(stripslashes($this->compname))."',
                                                          tmename = '".addslashes(stripslashes($this->tmename))."',
                                                          parentid = '".$this->parentid."',
                                                          tmecode = '".$this->tmecode."',
                                                          requested_on = NOW(),
                                                          action_flag = '".$this->action_flag."',
                                                          city = '".$this->data_city."'
                                                          ON DUPLICATE KEY UPDATE 
                                                          tmename = '".addslashes(stripslashes($this->tmename))."',
                                                          parentid = '".$this->parentid."',
                                                          tmecode = '".$this->tmecode."',
                                                          requested_on = NOW(),
                                                          action_flag = '".$this->action_flag."',
                                                          city = '".$this->data_city."'";
                                // $insert_into_bcvtable_Res = $conn_local->query_sql($insert_into_bcvtable);
                                $insert_into_bcvtable_Res = parent::execQuery($insert_into_bcvtable,$this->conn_local);
                            }
                            
                            if($this->action_flag == 35){
                                $ret = 'REQUEST_SENT';
                            }else{
                                $ret = 'WEBTEAM_REQ_SENT';
                            }
                    }
                }
                else{
                    if($this->action_flag == 35){
                        $ret = 'REQUEST_NOT_SENT';
                    }else{
                        $ret = 'WEBTEAM_REQ_NOT_SENT';
                    }
                }
            }
        }
        
        
        // Handling done by Ishan to determine wheter to stop ECS or CCSI Starts
        if ($this->st == 1) {
        
            $getEcsStatusQry = "SELECT parentid from db_ecs.ecs_mandate where parentid = '".$this->parentid."' and deactiveFlag=0 and ecs_stop_flag = 0";

            $getEcsStatusres = parent::execQuery($getEcsStatusQry,$this->conn_fin);

            if ($getEcsStatusres && parent::numRows($getEcsStatusres) > 0 ) {
                $active_mandate = 'ECS';
            }
            else { // ECS not active , check SI
                $getSIStatusQry = "SELECT parentid from db_si.si_mandate where parentid = '".$this->parentid."' and deactiveFlag=0 and ecs_stop_flag = 0";
                $getSIStatusres = parent::execQuery($getSIStatusQry,$this->conn_fin);

                if ($getSIStatusres && parent::numRows($getSIStatusres) > 0 ) {
                    $active_mandate = 'CCSI';
                }else{
                    $getSIStatusQry = "SELECT parentid from db_ecs.ecs_mandate_stopped WHERE bill_flag=0 AND ignore_flag=0 AND parentid='".$this->parentid."'";
                    $getSIStatusres = parent::execQuery($getSIStatusQry,$this->conn_fin);

                    if ($getSIStatusres && parent::numRows($getSIStatusres) > 0 ) {
                        $active_mandate = 'ECS';
                    }else{
                        $active_mandate = 'NONE';
                    }
                }
            }

            return $active_mandate;
        }
        else { // Handling done by Ishan to determine wheter to stop ECS or CCSI Ends

            return $ret;
        }
    }

    function getContractDataCity($parentid){
        $city_final    = '';
        $datacity_query = "SELECT data_city FROM db_iro.tbl_companymaster_extradetails WHERE parentid='".$parentid."'"; 
        $city_res = parent::execQuery($datacity_query,$this->conn_iro);
        $city_temp = parent::fetchData($city_res);

        $city      = strtolower(trim($city_temp['data_city']));     
        if(defined('REMOTE_CITY_MODULE')) { 
            $zone_query = "select main_zone from tbl_zone_cities where cities='".$city."'";
            $zone_obj = parent::execQuery($zone_query,$this->conn_local);
            $zone_res = parent::fetchData($zone_obj);

            $zone_temp = strtolower(trim($zone_res['main_zone']));
            
            if($zone_temp == 'coimbatore2') {
                $city_final = 'coimbatore'; 
            }else if($zone_temp == 'mumbai2') {
                $city_final = 'mumbai'; 
            }else{
                $city_final = $zone_temp; 
            }                       
        }else {
            if(strtolower($city) == 'delhi'){
                $city_final = "delhi";
            }else if(strtolower($city) == 'noida'){
                $city_final = "noida";
            }else{
                $city_final = strtolower(trim($city));
            }   
        }       
        return $city_final;
    }

    public function call_api_curl($postdata,$url){
        $data_string = $postdata;
        $ch = curl_init($url);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $response = curl_getinfo($ch);
        curl_close($ch);  
        return $content;
    }   

    private function setServers(){
        GLOBAL $db;
        $conn_city    = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
    
        $this->conn_iro         = $db[$conn_city]['iro']['master'];
        $this->conn_local       = $db[$conn_city]['d_jds']['master'];
        $this->conn_fin         = $db[$conn_city]['fin']['master'];
              
        if($conn_city == 'remote'){
            $this->remote = 1;
        }
  }

    private function sendDieMsg($msg){
		$res_arr = array();
		$res_arr['errorCode']	=	1;
		$res_arr['errorMsg']	    =   $msg;
		return $res_arr;
	}
}

if(!empty($_REQUEST))
{
    $req_params = $_REQUEST;
}
else
{
    $req_params = json_decode(file_get_contents('php://input'),true);
}


$obj_ecsAjaxData = new ecsAjaxData($req_params);

$returnval = $obj_ecsAjaxData->processAjaxData();

echo json_encode($returnval);

exit;

?>
