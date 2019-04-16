<?php

//Sample URL : http://172.29.0.237:1010/services/searchplus_eligibility_check.php?parentid=PXX22.XX22.150809095726.E9D9&data_city=Mumbai&module=TME
require_once('../config.php');
require_once('../functions.php');
require_once('includes/searchplus_eligibility_class.php');


if($_REQUEST['post_data'])
{
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



$searchplus_eligible_class_obj 	= new searchplus_eligibility_class($params);
$searchplus_eligible_camp_arr 	= $searchplus_eligible_class_obj->getEligibleSearchplusCampaign();
$searchplus_eligible_camp_str 	= json_encode($searchplus_eligible_camp_arr);
print($searchplus_eligible_camp_str);

?>



