<?php
//http://imteyazraja.jdsoftware.com/jdbox/services/doctest.php?parent_docid=022PXX22.XX22.130424143537.M4G5&data_city=mumbai&hosp_docid=022PXX22.XX22.131206144855.C8R2&doctor_name=Pratik&hospital_name=Paes+Hospital
require_once("../config.php");
require_once("../functions.php");
require_once('includes/create_doc_child_class.php');

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
	$doc_hosp_obj = new multilocation_doctor($params);
	$doc_hosp_obj->insertData();
}else{
	$die_msg_arr = array();
    $die_msg_arr['error']['code'] = 1;
    $die_msg_arr['error']['message'] = 'Kindly pass param as JSON data.';
    echo json_encode($die_msg_arr);
}
?>
