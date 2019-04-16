<?php

require_once('../config.php');
require_once('includes/invoiceDataClass.php');


//echo "<pre>";print_r($_REQUEST);
$InvoiceDataObj = new  invoiceData($_REQUEST);
//echo "<pre>";print_r($InvoiceDataObj);
$invoice_data = $InvoiceDataObj->getInvoiceData();


?>
