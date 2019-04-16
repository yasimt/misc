<?php
// changes needed
session_start();
set_time_limit(0);
## This process is to update area field where landmark is present in area field -- Pramesh chandra jha
if(!defined('APP_PATH'))
{
    require_once("../library/config.php");
}

include_once(APP_PATH."library/path.php");
require_once(APP_PATH."library/CompanyAutosuggestGeneration.php");
GLOBAL $dbarr;
$conn_finance	= new DB($dbarr['DB_IRO']);
$loop_flag=true;

$tablename= "tbl_autosuggest_generation_parentid";


while($loop_flag)	
{
	$fetch_parentid="SELECT parentid FROM ".$tablename." WHERE done_flag=0 order by priority_flag desc limit 200"; //LIMIT 10
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
			
			CompanyAutosuggestGeneration($var_parentid,$dbarr);

			$update_doneflag_query="UPDATE ".$tablename." SET done_flag=1 WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_finance -> query_sql($update_doneflag_query);
			
			$obj = null;
			
		}
		$today = date("Y-m-d H:i:s");
		echo "<br><b>PROCESS FINISHED :-".$today."<br>";
	}
	sleep(2);
}


?>
