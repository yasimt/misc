<?php
if(!defined('APP_PATH'))
{
   require_once("config.php");
}
include_once(APP_PATH."library/path.php");
global $dbarr;

	class email_sms_send
	{
        private $conn_messaging;
            
        function __construct( $dbarr)
        {
            $this->conn_messaging= 	new DB($dbarr['DB_MESSAGING']);
        }
       function sendSMS($mobileNo, $smstext, $source){
             $smstext= addslashes($smstext);
             if($mobileNo!='' && $smstext!=''){
                 $sql_sms="INSERT INTO tbl_common_intimations SET 
                    mobile = '".$mobileNo."', 
                    sms_text ='".$smstext."',
                    source='".$source."'";
                $result = $this->conn_messaging->query_sql($sql_sms);
                return $result;
            }else{
                return 0;
            }
       }
    }
?>
