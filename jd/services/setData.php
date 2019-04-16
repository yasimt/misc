<?php


ini_set('max_execution_time', 1000);
require_once('../config.php');
require_once('../library/configclass.php');
require_once('includes/setalldata.php');

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
        $params = json_decode(file_get_contents('php://input'),true);
}
$obj = new setAllData($params);
if($params['bulkall']==1)
{
        $obj_arr = $obj->setallbulkdata();
}
else if($params['bulkdaywise']==1)
{
        $day = $params['day'];
        $obj_arr = $obj->setDataDayWise($day);
}
else if($params['getmatch']==1)
{
        $obj_arr = $obj->getMatchData($params);
}
else
{
        $res = $obj->setbulkdata();
        if($res==1)
        {
                $obj_arr = array("msg"=>"success");
        }
}

if($params['trace'] == 1)
{
        print"<pre>";print_r($obj_arr);
}
else
{
        $obj_str        = json_encode($obj_arr);
        print($obj_str);
}

?>
