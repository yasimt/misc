<?php
session_start();
require_once('config.php');
require_once('library/configclass.php');
require_once('library/class.Curl.php');
require_once('insertLiveClass.php');
require_once('insertLiveLogClass.php');
require_once('historyLog.php');
require_once('textlogs.php');

if (is_array($_REQUEST) && count($_REQUEST) && (!isset($_REQUEST['process']) || (isset($_REQUEST['process']) && $_REQUEST['process'] != 'savesummary')))
{	
	logDataToText($_REQUEST, 'D');
	
	$obj  		= new insertToLive($_REQUEST);
	$ucode		= ((!empty($_REQUEST['history_user'])) ? $_REQUEST['history_user'] : $_REQUEST['ucode']);
	// History only for nonpaid
	if(($_REQUEST['paid'] == 0 || $_REQUEST['source'] == 'TM DATA CORRECTION (F10)' || $_REQUEST['paidexpired'] == 1 || $_REQUEST['save_nonpaid_cat']== 1 || $_REQUEST['savenonpaidjda']== 1 || $_REQUEST['docvendor']== 1) && ($_REQUEST['log_created'] !=1))  // Added  "|| $_REQUEST['paidexpired'] == 1 " in the condition For paid Expired...
	{
		
		$obj_log	= new contractLog($_REQUEST['parentid'], $_REQUEST['source'], $ucode , $_REQUEST['data_city']);
	}
	
	$res  		= $obj->finalInsert();
    $res        = $obj->autoRejectDownsell();
	//$err 		= $obj->validateRules();
	// History only for nonpaid
	if($_REQUEST['paid'] == 0 || $_REQUEST['paidexpired'] == 1)   // Added " || $_REQUEST['paidexpired'] == 1 " in the condition For Paid Expired ... 
	{
		unset($obj_log);	
	}
}else if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'savesummary'){ //Added by subroto to capturing the contract details in the tbl_contract_details(db_contract_record -- IDC)
	
	logDataToText($_REQUEST, 'D');
	
	$obj  	= new insertToLive($_POST);
	$pid	= $_POST['parentid'];
	$ucode	= $_POST['ucode'];
	$dept	= $_POST['dept'];print "<pre>";print_r($_POST);print "</pre>";
	$finance_data	= json_decode($_POST['finance_data'],true);
	$obj->insertContractDetails($pid,$ucode,$dept,$finance_data);
    $res        = $obj->autoRejectDownsell();
}
