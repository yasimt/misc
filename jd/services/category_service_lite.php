<?php



ini_set('display_errors',1);
ini_set('display_startup_errors',1);


require_once('../config.php');
require_once('includes/categoryServices_lite.php');


header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);

if($_REQUEST['print_flag'])
{
	foreach($_REQUEST as $key=>$value)
	{
		$params[$key] = $value;
	}
	//print"<pre>";print_r($params);
}
$catsaveobj  	= new categoryServices($params);
if($params['action'] == 1)
{
	$result = $catsaveobj->saveRelevantCategories();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
else if($params['action'] == 2)
{
	$result = $catsaveobj->getPopularAmongCompetitors();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
else if($params['action'] == 3)
{
	$result = $catsaveobj->getSiblingsAndChild();// child
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
else if($params['action'] == 4)
{
	$result = $catsaveobj->getSiblingsAndChild(); // sibling
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
else{
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = 'No Such Call';
	$result=json_encode($result); 
}
print($result);

?>



