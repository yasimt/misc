<?php
require_once('../config.php');
require_once('includes/class_send_sms_email.php');

header('Content-Type: application/json');
if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        $params[$key] = $value;
    }
} else {
    header('Content-Type: application/json');
    $params = json_decode(file_get_contents('php://input'), true);
}
//pranlin.jdsoftware.com/jdbox/services/sendNotifications.php?from=10041420&to=10033648&pid=PXX22.XX22.170523152947.T8A4&city=Mumbai&src=ME&act=sms&st=1
//pranlin.jdsoftware.com/jdbox/services/sendNotifications.php?from=10041420&to=10033648&pid=PXX22.XX22.170523152947.T8A4&city=Mumbai&src=ME&act=mail&st=1
//pranlin.jdsoftware.com/jdbox/services/sendNotifications.php?from=10033648&to=10041420&pid=PXX22.XX22.170523152947.T8A4&city=Mumbai&src=ME&act=smsmail&st=1
GLOBAL $db;
$emailsms_obj   = new email_sms_send($db,strtolower($params['city']));
$fromEmp        =$params['from'];
$toEmp          =$params['to'];
$source         =$params['src'];
$parentid       =$params['pid'];
$status         =$params['st'];
$fromEmpDetails =fetchEmpDetails($fromEmp);
$toEmpDetails   =fetchEmpDetails($toEmp);
$fromEmail      =$fromEmpDetails['email'];
$fromName       =$fromEmpDetails['name'];
$fromMobile     =$fromEmpDetails['mobile'];

$toEmail        =$toEmpDetails['email'];
$toName         =$toEmpDetails['name'];
$toMobile       =$toEmpDetails['mobile'];

$email_subject  ="Downsell request status update";
if($status==1){
    $smstext        ="Hello $toName your downsell request for contract $parentid has been approved. Please Deal close";
    $email_text     ="Hello ".$toName.",</br></br></br>Your downsell request for the contract <b>$parentid</b> has been approved. </br>Please Deal close through <b>Discount Report page</b>. </br></br></br></br>Regards,</br>Team Justdial</br>";
}else if($status==2){
    $smstext        ="Hello $toName your downsell request for contract $parentid has been rejected.";
    $email_text     ="Hello ".$toName.",</br></br></br>Your downsell request for the contract <b>$parentid</b> has been rejected.</br></br></br></br>Regards,</br>Team Justdial</br>";
}else if($status==3){
    $email_text     ="Hello ".$toName.",</br></br></br>Your downsell request (which was approved previously) for the contract <b>$parentid</b> has been rejected due to backend data lose.</br> Please recalculate the budget through <b>BForm</b> and do a fresh downsell requset.</br></br></br> <b>Note:</b> This issue could have been avoided if you deal close it as fast as you can when you got the <b>Approval</b>.</br>Please try to avoid delay.</br></br></br></br></br>Regards,</br>Team Justdial</br>";
}else if($status == 4){
	$smstext        ="Hello $toName your downsell request for contract $parentid has been rejected due to the categories present in the contract got merged.";
	$email_text		=  "Hello ".$toName.",</br></br></br>As categories selected for contract : $parentid has been merged. Downsell Request for this contract is rejected. Kindly give Fresh downsell request and proceed.</br></br></br>Regards,</br>Team Justdial</br>";
}

switch ($params['act']) {
    case 'mail':
        $email_response = $emailsms_obj->sendEmail($toEmail, $fromEmail, $email_subject, $email_text, $source, $parentid,$fromEmail);
        if($email_response==1){
            $sms_email_response = json_encode(array('error' => 0, 'msg' => 'Email send'));
        }else{
            $sms_email_response = json_encode(array('error' => 1, 'msg' => 'Email sending failed.'));
        }
        break;
    case 'sms':
        $sms_response = $emailsms_obj->sendSMS($toMobile, $smstext, $source);
        if($sms_response==1){
            $sms_email_response = json_encode(array('error' => 0, 'msg' => 'SMS send'));
        }else{
            $sms_email_response = json_encode(array('error' => 1, 'msg' => 'SMS sending failed'));
        }
        break;
    case 'smsmail':
        $sms_response = $emailsms_obj->sendSMS($toMobile, $smstext, $source);
        $email_response = $emailsms_obj->sendEmail($toEmail, $fromEmail, $email_subject, $email_text, $source, $parentid,$fromEmail);
        if($sms_response==1 && $email_response==1){
            $sms_email_response = json_encode(array('error' => 0, 'msg' => 'Both Email and SMS are send'));
        }else if($sms_response==1 && $email_response==0){
            $sms_email_response = json_encode(array('error' => 1, 'msg' => 'SMS sent, but Email failed'));
        }else if($sms_response==0 && $email_response==1){
            $sms_email_response = json_encode(array('error' => 2, 'msg' => 'Email sent, but SMS failed'));
        }else if($sms_response==0 && $email_response==0){
            $sms_email_response = json_encode(array('error' => 3, 'msg' => 'Both Email and SMS are failed'));
        }
        break;

    default:
        $sms_email_response = json_encode(array('error' => 99, 'msg' => 'unknown request'));
        break;
}
print($sms_email_response);

function fetchEmpDetails($empid){
    $curl_url = "http://".SSO_IP."/hrmodule/employee/fetch_employee_info/".$empid;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curl_url);
    curl_setopt($ch, CURLOPT_POST, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    $resArr=json_decode($response,TRUE);
    $empArray['empcode']=$resArr['data']['empcode'];
    $empArray['name']=$resArr['data']['empname'];
    $empArray['city']=$resArr['data']['city'];
    $empArray['mobile']=$resArr['data']['mobile_num'];
    $empArray['email']=$resArr['data']['email_id'];
    curl_close($ch);
    return $empArray;
}
