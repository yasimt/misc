<?php
ini_set('max_execution_time', 300);
require_once('../config.php');
require_once('../library/configclass.php');
require_once('../mongo.class.php');

if($_REQUEST['post_data'])
{
	header('Content-Type: application/json');
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
}
else
{
	header('Content-Type: application/json');
	$params	= json_decode(file_get_contents('php://input'),true);
}
//print_r($params); die;
$action = $params['action'];
if(trim($action)==''){
	$message = "action is blank.";
	echo json_encode(array('error'=>1,'msg'=>$message));
	die();
}

$obj = new MongoClass();
if($params['action']=='getdata')
{
	if(count($params['aliaskey'])>0){
		$params['aliaskey'] = json_decode($params['aliaskey'],true);
	}
	$obj_arr = $obj->getData($params);
}
else if($params['action']=='getjoindata')
{
	$obj_arr = $obj->joinTables($params);
}
else if($params['action']=='getalldata')
{
	$obj_arr = $obj->getAllData($params);
}
else if($params['action']=='getmysqldata')
{
	$obj_arr = $obj->getMysqlData($params);
}
else if($params['action']=='updatedata')
{
	parse_str(str_replace(" = '","=",$params['table_data']),$params['table_data']);
	$res = $obj->updateData($params);
	if($res==1)
	{
		$obj_arr = array("error"=>0,"msg"=>"success");
	}
	else
	{
		$obj_arr = array("error"=>1,"msg"=>"query failed");
	}
}
else if($params['action']=='getdatamatch')
{
	$obj_arr = $obj->getDataMatch($params);
}
else if($params['action']=='getbulkdata')
{
	$parentid	=$params['parentid'];
	$data_city	=$params['data_city'];
	$table		=$params['table'];
	$module		=$params['module'];
	$obj_arr = $obj->setbulkdata($parentid,$data_city,$table,$module);
}
else if($params['action']=='getbulkdata_join')
{
	$obj_arr = $obj->setjoindata($params);
}
else if($params['action']=='gettabledata')
{
	$obj_arr = $obj->getMysqlTableData($params);
}
else if($params['action']=='getjointable')
{
	$obj_arr = $obj->getTableData($params);
}
else
{
	$message = "invalid action";
	echo json_encode(array('error'=>1,'msg'=>$message));
	die();
}

if($params['trace'] == 1)
{
	print"<pre>";print_r($obj_arr);
}
else
{
	$obj_str = json_encode($obj_arr);
	echo $obj_str;
}
?>
