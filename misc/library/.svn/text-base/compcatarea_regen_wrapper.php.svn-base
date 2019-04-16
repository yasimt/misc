<?php
session_start();
set_time_limit(0);
# Comment: The page acts as a wrapper for the main compcatarea_regen.php page.
		  # The page executes in a loop passing parentid as parameters whose
		  # CCA entries have to be regenerated.

# Requisites Check: #
#####################

#	CREATE TABLE tbl_backendprocess_parentid(parentid varchar(60) PRIMARY KEY, done_flag tinyint DEFAULT '0');
#	ALTER  TABLE tbl_backendprocess_parentid ADD INDEX(parentid), ADD INDEX(done_flag);
#	ALTER  TABLE tbl_backendprocess_parentid ADD INDEX(parentid), ADD INDEX(done_flag);

require_once("../library/config.php");
include_once('../common/Serverip.php');
include_once(APP_PATH."library/path.php");

GLOBAL $dbarr;

$conn_iro	= new DB($dbarr['DB_IRO']);
$conn_local	= new DB($dbarr['LOCAL']);
$conn_finance	= new DB($dbarr['FINANCE']);

include_once('compcatarea_regen.php');
$loop_flag=true;

if(trim($_GET['categorymerging'])=="categorymerging")
$tablename= "tbl_backendprocess_parentid_category_merging";
else
$tablename= "tbl_backendprocess_parentid";


while($loop_flag)	
{
	$fetch_parentid="SELECT parentid FROM ".$tablename." WHERE done_flag=0 order by priority_flag desc limit 1000"; //LIMIT 10
	#echo $fetch_parentid;
	$result_fetch_parentid=$conn_finance -> query_sql($fetch_parentid);

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

		WHILE ($row = mysql_fetch_array($result_fetch_parentid))
		{
			$counter++;
			$var_parentid = $row['parentid'];
			
			$update_doneflag_query="UPDATE ".$tablename." SET done_flag=9 WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_finance -> query_sql($update_doneflag_query);
			
			$obj=new compcatarea_regen($var_parentid,$dbarr,'dbbackend');
			$obj -> compcatarea_gen();

			$update_doneflag_query="UPDATE ".$tablename." SET done_flag=1 WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_finance -> query_sql($update_doneflag_query);
			
			$obj = null;
			
		}
		$today = date("Y-m-d H:i:s");
		echo "<br><b>PROCESS FINISHED :-".$today."<br>";
	}
	sleep(5);
}


?>
