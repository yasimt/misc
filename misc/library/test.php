<?php

if(!defined('APP_PATH'))
{
    require_once("../library/config.php");
}
include_once(APP_PATH."library/path.php");
require_once(APP_PATH."library/define_virtualnumbers.php");
require_once(APP_PATH.'library/class.virtualnumber.php');
require_once(APP_PATH."library/define_virtualnumbers.php");

Global $dbarr;
$conn_iro	=  new DB($dbarr['DB_IRO']); 
$conn_decs	=  new DB($dbarr['DB_DECS']);
$conn_fnc 	=  new DB($dbarr['DB_FNC']);
$conn_local =  new DB($dbarr['LOCAL']);

$parentid = 'PXX22.XX22.121016170136.F9K6';
$virtualnoObj= new Virtualnumber($parentid,$dbarr,'mumbai','vn_cron');	

$vn_return = $virtualnoObj->genio_update_virtual_number($parentid);

?>