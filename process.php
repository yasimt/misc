<?php

echo "\n\r\n\r Process Start - ".date('Y-m-d H:i:s')."  \n\r";



//mysql_connect('localhost','application','s@myD#@mnl@sy',)
$db_server='172.29.67.213';
$db_user = 'pravink';
$db_pass = 'Pravin@123';
$db_database = 'dbteam_temp';
$process_table_name= ' tbl_db_table_production_update_02052017 ';
$searchlocation= '/home/justdial/Desktop/IRO_MODULE/';

$con=mysqli_connect($db_server,$db_user,$db_pass,$db_database);
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
die;
mysqli_select_db($con,$db_database);

$query ="select * from ".$process_table_name ." where done_flag=0";

//echo "".$query."\n\n";
$resource = mysqli_query($con,$query);


while($resource_array = mysqli_fetch_assoc($resource))
{	
	$tableToFind=$resource_array['Table_name'];
	$FoundFlag=0;
	updateDoneFlag($con,$process_table_name,$tableToFind,9);
	
	$output= grepTable_name($tableToFind,$searchlocation);
	
	if(count($output))
	{
		
		//echo "\n\r <br> table found ";	 print_r($output); echo "\n\r <br>";	
		
		$sqlupdate= " update ".$process_table_name." SET found_flag=1,pagename='".implode(',',$output)."' where Table_name='".$tableToFind."'";
		mysqli_query($con,$sqlupdate);
		
	}else
	{
		//echo "\n\r <br> table NOT found ";echo "\n\r <br>";
		$sqlupdate= " update ".$process_table_name." SET found_flag=0 where Table_name='".$tableToFind."'";
		mysqli_query($con,$sqlupdate);
	}
	updateDoneFlag($con,$process_table_name,$tableToFind,1);
	
}

mysqli_close($con);


echo "\n\r\n\r Process END - ".date('Y-m-d H:i:s')."  \n\r";

function updateDoneFlag($con,$process_table_name,$table_name,$done_flag)
{
	
	$sqlupdate= " update ".$process_table_name." SET done_flag=".$done_flag." where Table_name='".$table_name."'";
	$resource = mysqli_query($con,$sqlupdate);
	
}

function grepTable_name($Table_name,$searchlocation)
{
	//grep -nrli --exclude='*.svn-base' "tbl_movie_timings" /home/justdial/production/
	$output=array();
	$return_array= array();
	$command= " grep -nrli --include=*.php --exclude='*.svn-base' '".$Table_name."' ".$searchlocation;	
	
	exec ( $command , $output );
	
	if(count($output))
	{		
		$return_array = str_replace($searchlocation,'',$output);
		
	}
	
	return $return_array;
	
	 
	
}



?>
