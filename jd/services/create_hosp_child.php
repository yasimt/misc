<?php
//http://imteyazraja.jdsoftware.com/jdbox/services/hosptest.php?parent_docid=022PXX22.XX22.170222054510.T3U9&data_city=mumbai&doctor_name=Pratik&hospital_name=Paes+Hospital&national_catid=10226406
require_once("../config.php");
require_once("../functions.php");
require_once('includes/create_hosp_child_class.php');

if(($_GET['insert_ignore'])||($_GET['key']=='key'))
{
	foreach($_GET as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}

if(count($params)>0){
	$doc_hosp_obj = new hosp_multiple_doctor($params);
	$doc_hosp_obj->insertData();
}else{
	$die_msg_arr = array();
    $die_msg_arr['error']['code'] = 1;
    $die_msg_arr['error']['message'] = 'Kindly pass param as JSON data.';
    echo json_encode($die_msg_arr);
}
?>
