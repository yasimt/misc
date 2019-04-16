<?php
// changes needed
session_start();
set_time_limit(0);
# Comment: The page acts as a wrapper for the main compcatarea_regen.php page.
		  # The page executes in a loop passing parentid as parameters whose
		  # CCA entries have to be regenerated.

# Requisites Check: #
#####################

#	CREATE TABLE tbl_backendprocess_bidperday(parentid varchar(60) PRIMARY KEY, cityid tinyint NOT NULL, done_flag tinyint DEFAULT '0');
#	ALTER  TABLE tbl_backendprocess_bidperday ADD INDEX(parentid), ADD INDEX(done_flag);
#	ALTER  TABLE tbl_backendprocess_bidperday ADD INDEX(parentid), ADD INDEX(done_flag);
require_once("config.php");
require_once("../common/Serverip.php");
include_once(APP_PATH."library/path.php");
include($dbpathDE);
GLOBAL $dbarr;

$conn_iro		= new DB($dbarr['DB_IRO']); 
$conn_local		= new DB($dbarr['LOCAL']);
$conn_finance	= new DB($dbarr['FINANCE']);

require_once("bidperday_regen_function.php");
include_once('bidperday_regen.php');

	# Process Started #

function fetch_contractids(){
GLOBAL $dbarr,$conn_iro,$conn_finance;

	$thru_wrapper=1;

	$fetch_contractid="SELECT parentid,cityid FROM tbl_backendprocess_bidperday WHERE done_flag=0 LIMIT 10";
	$result_fetch_contractid=$conn_finance -> query_sql($fetch_contractid);

	$rowcount = mysql_num_rows($result_fetch_contractid);
	$today = date("Y-m-d H:i:s");
	echo "<br><b>PROCESS STARTED</b>";
	IF ($rowcount<=0) {

		echo "<br>No more parentids left for processing. File EXITED.";
		die;
	}
	ELSE {
					WHILE ($row = mysql_fetch_array($result_fetch_contractid)){
						$var_parentid = $row['parentid'];
						$var_cityid 	= $row['cityid'];

						$obj=new bidperday_regen($var_cityid,$var_parentid,$dbarr,'dbbackend');
						$obj -> bidperday_gen();

						$update_doneflag_query="UPDATE tbl_backendprocess_bidperday SET done_flag=1 WHERE parentid='".$var_parentid."'";
						$result_update_doneflag_query=$conn_finance -> query_sql($update_doneflag_query);

					}

					//echo "<br>Sleeping for 5 secs...";
					sleep(5);

					fetch_contractids();
	}

} # Function Ends Here


fetch_contractids();

?>
