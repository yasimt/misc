<?php
session_start();
set_time_limit(0);
# This process is used for search related data population when contract is rejected in approval 
if(!defined('APP_PATH'))
{
    require_once("../library/config.php");
}
include_once(APP_PATH."library/path.php");
require_once(APP_PATH."web_services/curl_client.php");
GLOBAL $dbarr;
$conn_iro	= new DB($dbarr['DB_IRO']);
$loop_flag=true;

$tablename = "unapproved_contract_data_population";
$archival_tablename = "unapproved_contract_data_population_archive";


while($loop_flag)	
{
	$fetch_parentid="SELECT * FROM ".$tablename." WHERE done_flag=0 order by priority_flag desc limit 200"; //LIMIT 10
	#echo $fetch_parentid;
	$result_fetch_parentid=$conn_iro -> query_sql($fetch_parentid);

	$rowcount = mysql_num_rows($result_fetch_parentid);

	IF ($rowcount<=0) {

		echo "<br>No more parentids left for processing. File EXITED.";
		$loop_flag = false;
	}
	ELSE
	{
		echo "<br> Total parentid to process".$rowcount;
		$today = date("Y-m-d H:i:s");
		echo "<br><b>PROCESS STARTED :-".$today."<br>";
		$counter=0;

		WHILE ($processrow = mysql_fetch_assoc($result_fetch_parentid))
		{
			$counter++;
			$var_parentid = $processrow['parentid'];
			$curtime = date("Y-m-d H:i:s");

			$update_doneflag_query="UPDATE ".$tablename." SET done_flag=9 WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_iro -> query_sql($update_doneflag_query);

			
			$valiationcode='REJUNAPR';
			web_api($var_parentid,"dbbackend","dbbackend",$valiationcode);
			
			// after web_api called we make entry into archival table
			
			$archiveinsert =" INSERT INTO ".$archival_tablename." SET  
			parentid ='".$processrow['parentid']."' ,
			done_flag = 1 ,
			priority_flag ='".$processrow['priority_flag']."' ,
			entry_time ='".$processrow['entry_time']."' ,
			entered_by ='".$processrow['entered_by']."' ,
			done_time ='".$curtime."' ";
			$result_archiveinsert = $conn_iro -> query_sql($archiveinsert);
			
			// after entry into archival table we remove entry form table
			$DELETE_query="DELETE FROM ".$tablename." WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_iro -> query_sql($DELETE_query);
						
			
		}
		$today = date("Y-m-d H:i:s");
		echo "<br><b>PROCESS FINISHED :-".$today."<br>";
	}
	sleep(2);
}


?>
