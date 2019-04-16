<?php
@session_start();
@include_once("../common/config.php");//removed this code after entire implementation
### Set Common Used Variables Here.
$title				= "JUST DIAL";

if( isset($_SESSION['webApp']) && $_SESSION['webApp'] == true) {
	$svr =  "<FONT SIZE='1'>Connected to 80</FONT>";
	$dbpath = APP_PATH."common/dbconn_new.php";
	$dbpathDE = APP_PATH."common/dbconn_new.php";
}else {
	$svr = "<FONT SIZE='1'>Connected to 214</FONT>";
	$dbpath = APP_PATH."common/dbconn.php";
	$dbpathDE = APP_PATH."common/dbconnDE.php";
}

## Database Connection File for MultiCity Porting
$dbpathCity = APP_PATH."common/dbconnCity.php";

### Database Connection File FOR tme_np.
$dbpath_np = APP_PATH."common/dbconn_np.php";

### Database Connection File.
$dbRptPath = APP_PATH."common/dbconnRpt.php";
//$dbRptPathold= APP_PATH."common/dbconnRptold.php";  not in use  -22 april 2013   vinay desai
$dbconnTmeOnlinePath= APP_PATH."common/dbconnTmeOnline.php";
$dbDERptPath=APP_PATH."common/dbconnDERpt.php";
$dbCallPath = APP_PATH."common/dbconnCall.php";
$dbRptPath_d = APP_PATH."common/dbconnRpt_d.php";

//nonpaidreport
$dbRptPath_nonpaid = APP_PATH."common/dbconnRpt_nonpaid.php";


### JS Filepath.
$jspath = "/common/js/";

### CSS Filepath.
$csspath = "/common/css/";

### Images Filepath.
$imgpath = "/images/common/";

### HEADER Filepath.
$headpath = APP_PATH."common/header.php"; 
$headpath_new = APP_PATH."common/header_new.php"; 

### FOOTER Filepath.
$footpath = APP_PATH."common/footer.php"; 
$footpath_new = APP_PATH."common/footer_new.php"; 

### TOPMENU Filepath.
$topmenupath = APP_PATH."topmenu/top.php"; 
$topmenupath_new  = APP_PATH."topmenu/top_new.php"; 

### Common Pop Up Filepath for Div.
$showListpath	   = APP_PATH."common/divpopup/showList.php"; 
$showListlog	   = APP_PATH."common/divpopup/showlistlog.php"; 

$confMsgpath	   = APP_PATH."common/divpopup/confirmMsg.php";
$plainTemplatepath = APP_PATH."common/divpopup/plainTemplate.php";

### NonPaid Contract Class Filepath.
$nonPaidpath = APP_PATH."library/unpaidConClass.php";
$sourcepath  = APP_PATH."library/sourceClass.php";

/**************** important updates File ***************/
//class path
$impupdatepath = APP_PATH."/library/impUpdateClass.php";
//file created at this location
//$impupdatefile = "impupdate/impUpdate.htm";
$impupdatefile = "/spec/files/impUpdate.htm";
$impupdatefile = "/spec/files/impUpdate.htm";
//include main file or middle file
$impupdatemiddle = APP_PATH."impupdate/impupdate.php";


/********************* Customer Loyalty **************************/
//class path
$custloyal = APP_PATH."/library/custLoyalClass.php";

$mailContent = APP_PATH."loyalty/mailcontent.php";
$clppath   = APP_PATH;



/**************** Bidding Registration  File ***************/
// include for bidding
$bidding_regisMiddle = APP_PATH."bidding/bidding_registration_1.php";
//class path
$bidding_reg = APP_PATH."library/bidding_reg_Class.php";


/**************** department  ***************/
//class path
$deptPath = APP_PATH."library/departmentClass.php";

/**************** batch ***************/
//class path
$batchpath = APP_PATH."library/batchClass.php";

/*************** Zone files ***************/
$zonepath = APP_PATH."library/zoneClass.php";

/****************Source Path*****************/
$sourcepath = APP_PATH."library/sourceClass.php";

/****************Bulk Source Path*****************/
$bulksourcepath = APP_PATH."library/bulksourceClass.php";

/****************IRO Appoinment Path*****************/
$apppath = APP_PATH."library/appClass.php";

/****************Contract Additional Info Path*****************/
$con_addipath = APP_PATH."library/contract_addiClass.php";


/*************** contract type files ***************/
$cntractTypepath = APP_PATH."library/cont_typeClass.php";

/*************** Prompt files ***************/
$promptpath = APP_PATH."library/promptClass.php";

##############  Logout ##################3
$logoutpath = APP_PATH."library/logoutClass.php";
require_once(APP_PATH.'common/dbconnection/config.php');
require_once(APP_PATH.'common/dbconnection/db.class.php');
require_once(APP_PATH.'common/dbconnection/dbi.class.php');
require_once(APP_PATH.'00_Payment_Rework/common/helperFn.php');
require_once(APP_PATH.'00_Payment_Rework/payment_app/payment_app.php');
require_once(APP_PATH.'00_Payment_Rework/company_finance_class.php');
require_once(APP_PATH.'library/companymasterclass.php');
require_once(APP_PATH.'library/genio_flow.php');
require_once(APP_PATH.'library/class.Curl.php');

$MainInfo = "Mumbai_MainInfo";

$title	=TITLE;
$bidUrl = APP_URL . "/";
$jspath = COMMON_JS_URL;//Removed after entire implementation
$csspath = COMMON_CSS_URL;//Removed after entire implementation
$imgpath = IMG_COMMON_URL;//Removed after entire implementation

# Mail Header Footer For Mail
define("JD_MAIL_HEADER",'http://messaging.justdial.com/email_header.php');
define("JD_MAIL_FOOTER",'http://messaging.justdial.com/email_footer.php');
?>
