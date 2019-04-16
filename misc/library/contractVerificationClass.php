<?php
include_once('../library/class_email_sms_send.php');
$emailsms_obj = new email_sms_send($dbarr);

class contractVerificationClass
{
        public $parentid;
        public $logid;
        public $verification_code;
        public $MobileArray;

        function getVerificationcode(){
            $SecCode = mt_rand(100000,999999); // create random number and "md5" function converts it in to a hashed code
            $SecCode = substr($SecCode, 0, 6); // change all of the letters to lower case
            return $SecCode;
        }
        
        function getContractMobileNo($parentId, $conn_iro){
			$mobile_arr=  array();
			$this->compmaster_obj	= new companyMasterClass($conn_iro,"",$parentId);
			$temparr	= array();
			$fieldstr	= "mobile";
			$where 		= "parentid = '".$parentId."'";
			$temparr	= $this->compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);
			if($temparr['numrows']>0)
            {
				$Mobilerow = $temparr['data']['0'];
				$Mobile_arr= explode(",",(str_replace("/", ",", $Mobilerow['mobile'])));
			}
            $Mobile_arr= array_filter($Mobile_arr, 'strlen');
            return $Mobile_arr;
        }
        
        function sendVerificationCode($verification_code, $mobile_no, $logid, $conn_local){
        global $emailsms_obj;
           $smsmessage = "Thank you for calling justdial. Your verification code is : '".$verification_code."'. Please give this verification code to corresponding cs executive.";
             $output = $emailsms_obj->sendSMS($mobile_no, $smsmessage , 'cs');
                      
            /*$url="http://219.64.175.243/csms/fcgi/PushURL.fcgi?username=justnew2&password=jdp234&origin_addr=J_Dial&type=0&mobileno=91".$mobile_no."&message=".urlencode($smsmessage)."";
            $date	=date("Y-m-d  H:i:s");				
            $a		=curl_init();
            
          curl_setopt($a,CURLOPT_URL,$url);				
          curl_setopt($a, CURLOPT_RETURNTRANSFER, 1);
            $output=curl_exec($a);				
          curl_close($a);*/
            if($output==""){
                echo "<h1>Due to some technical issue verification code is not sent. Please try again.</h1>";
            }else{
                if($logid > 0){
                    $sqlqry= "UPDATE tbl_contract_verification_log SET 
                                verification_code   = '".$verification_code."',
                                verification_count  =verification_count+1,
                                date_time             = now(),
                                CLI_phoneNo         =".$mobile_no."
                                WHERE Id    =".$logid;
                       $result = $conn_local ->query_sql($sqlqry);       
                }
            }        
        }
        function getVerificationSentCount($parentId,$logid,$conn_local){
			$sqlcount="SELECT verification_count FROM tbl_contract_verification_log WHERE Id='".$logid."'  AND parentid='".$parentId."'";
            $rescount = $conn_local ->query_sql($sqlcount); 
            if(mysql_num_rows($rescount)>0){
                    $rowcount = mysql_fetch_assoc($rescount);
                    return $rowcount['verification_count'];
            }else{
                echo "<h1>There is error in log generation, no log entry found.</h1>";
                    return 0;
            }      
        }
        function logContractVerification($values, $conn_local){
            GLOBAL $conn_local;
            
			unset($values['newgenio']);
			unset($values['paid_flow']);
			unset($values['sent_flag']);
			unset($values['convert']);
			unset($values['nonpaid']);
			unset($values['nextLocation']);
			unset($values['topup']);
			
            $sql="INSERT IGNORE INTO tbl_contract_verification_log SET ";
            foreach($values as $field=>$value){
                $sql = $sql.$field."='".$value."', ";
            }
            $sql= $sql."date_time=now()";
            $sql=rtrim($sql,", ");      
			$res = $conn_local -> query_sql($sql);			
			if($res)
			{
				$sql1 = "SELECT id FROM d_jds.tbl_contract_verification_log WHERE  parentid='".$values['parentid']."' and usercode='".$values['usercode']."' and calltype=1 ORDER BY date_time DESC LIMIT 1";
				$res1 = $conn_local -> query_sql($sql1);
				$row1=mysql_fetch_assoc($res1);		   
				return $row1['id'];		   
			}
        }
}
?>
