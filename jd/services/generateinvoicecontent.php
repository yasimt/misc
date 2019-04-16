<?php

require_once('../config.php');
//~ ini_set('display_errors',1);
//~ ini_set('display_startup_errors',1);
//~ error_reporting(-1);


//~ http://sandeepk.jdsoftware.com/jd_box/services/generateinvoicecontent.php?action=1&parentid=PXX22.XX22.170810115801.Y1R2&module=me&data_city=mumbai&version=13


if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}
require_once('includes/generateinvoicecontentclass.php');
require_once('includes/contractdetailsclass.php');

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
	
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

}

if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
print_r($params);
}

$generate_invoice_fn = new generate_invoice_class($params);
$params_contract_details = $params;
$params_contract_details['action'] ='GetPlatDiamCategories';

if($params['action'] == 1)
{
	$generate_invoice_fn_obj = new contractdetailsclass($params_contract_details);
	$result = $generate_invoice_fn -> getInvoiceContent($generate_invoice_fn_obj);
}
if($params['action'] == 2)
{
	$result = $generate_invoice_fn -> get_all_instrument();
}
if($params['action'] == 3)
{
	$generate_invoice_fn_obj = new contractdetailsclass($params_contract_details);
	$result = $generate_invoice_fn -> getInvoiceContent($generate_invoice_fn_obj);
}
if($params['action'] == 5)
{
	$generate_invoice_fn_obj = new contractdetailsclass($params_contract_details);
	$result = $generate_invoice_fn -> htmlpdfgen_new($generate_invoice_fn_obj);
}
if($params['action'] == 6)
{
	$generate_invoice_fn_obj = new contractdetailsclass($params_contract_details);
	$result = $generate_invoice_fn -> getInvoiceContent($generate_invoice_fn_obj);
}
if($params['action'] == 7)
{
	$generate_invoice_fn_obj = new contractdetailsclass($params_contract_details);
	$result = $generate_invoice_fn -> get_all_instrument_approved($generate_invoice_fn_obj);
} 
echo $result;














?>
