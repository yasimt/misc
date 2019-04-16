<?php
function ddgpindealclose_logic($param_var_cityid,$param_var_parentid){
	
	return ;

GLOBAL $conn_iro,$conn_local,$conn_finance;

$var_cityid  = $param_var_cityid;
$var_parentid = $param_var_parentid;

# Connect to 1.233. Fetch bid_perday from bidcatdetails_ddg and update tbl_d_dg_pin_dealclosed locally.
			########################################################################################################

			IF ($var_cityid == 0)$databasename	=	"online_regis_mumbai";
			IF ($var_cityid == 8)$databasename	=	"online_regis_delhi";
			IF ($var_cityid == 16)$databasename	=	"online_regis_kolkata";
			IF ($var_cityid == 26)$databasename	=	"online_regis_bangalore";
			IF ($var_cityid == 32)$databasename	=	"online_regis_chennai";
			IF ($var_cityid == 40)$databasename	=	"online_regis_pune";
			IF ($var_cityid == 50)$databasename	=	"online_regis_hyderabad";
			IF ($var_cityid == 56)$databasename	=	"online_regis_ahmedabad";
			IF ($var_cityid == 99)$databasename	=	"online_regis_remote_cities";

			$link = mysql_connect(IDC_DB_APP_IP.':3306', 'rajeev', 'r@@j33vK');

			IF (!mysql_select_db($databasename, $link)) {
				echo "Cannot connect to 1.233 db";
				die;
			}
			ELSE{

				$delete_ddg_pin_query="DELETE FROM tbl_ddgpin_entries WHERE parentid='".$var_parentid."'";
				$result_delete_ddg_pin_query=$conn_finance->query_sql($delete_ddg_pin_query);

				$ddg_pin_query="INSERT INTO tbl_ddgpin_entries(parentid,bid_catid,pincode,bidperday)SELECT parentid, bid_catid, pincode, bidperday FROM tbl_d_dg_pin_dealclosed WHERE campaignid=2 and  parentid='".$var_parentid."'";
				$result_ddg_pin_query=$conn_finance->query_sql($ddg_pin_query);

				$delete_idc_bcd_query="DELETE FROM tbl_idc_entries WHERE parentid='".$var_parentid."'";
				$result_delete_idc_bcd_query=$conn_finance->query_sql($delete_idc_bcd_query);

				# Fetching Data from IDC
				#########################

				$idc_query="SELECT IF(LENGTH(GROUP_CONCAT(CONCAT(bid_catid,'~',bidperday,'~',pincode) SEPARATOR '##'))IS NULL,0,LENGTH(GROUP_CONCAT(CONCAT(bid_catid,'~',bidperday,'~',pincode) SEPARATOR '##')))  AS valuecount, GROUP_CONCAT(CONCAT(bid_catid,'~',bidperday,'~',pincode) SEPARATOR '##') AS catandbid FROM ".$databasename.".tbl_bidcatdetails_ddg WHERE  campaignid=2 and  parentid='".$var_parentid."'";
				$result_idc_query=mysql_query($idc_query);
				$rowcount=mysql_fetch_array($result_idc_query);

				#echo "Rowcount: ".$rowcount[0];
				#echo "<br>";
				#echo "NumRows: ".$numrows_idc_query;
				#echo "<br>";

				IF ($rowcount[0] > 0){
					#$rows=mysql_fetch_array($result_idc_query);
					#$concatedvalue = $rows[`catandbid`];

					$concatedvalue = $rowcount[1];

					unset($explodedvalues);
					#echo "Concated Value ".$concatedvalue;
					#echo "<br>";

					$explodedvalues = explode("##",$concatedvalue);

					#echo "Count:".count($explodedvalues);
					#echo "<br>";
					#$cnt=0;

					FOR ($i=0; $i<count($explodedvalues); $i++){

						$explodedvalues_again = explode("~",$explodedvalues[$i]);
						#echo "Exploded Values:".$explodedvalues_again[1];
						#echo "<br><br>";

						$idc_bcd_query="INSERT INTO tbl_idc_entries(parentid,bid_catid,pincode,bidperday)VALUES('".$var_parentid."','".$explodedvalues_again[0]."','".$explodedvalues_again[2]."','".$explodedvalues_again[1]."')";
						$result_idc_bcd_query=$conn_finance->query_sql($idc_bcd_query);

						# If the fetched Catid from IDC doesnt have a bidperday,it updates from shadow
						################################################################################

						IF ( $explodedvalues_again[1]<=0  OR $explodedvalues_again[1]==(NULL) OR $explodedvalues_again[1]=="" ){
							$update_idc_d_dg_query = "UPDATE tbl_bidcatdetails_ddg_shadow x1, tbl_d_dg_pin_dealclosed y1
							SET y1.bidperday = x1.bidperday
							WHERE x1.parentid = y1.parentid
							AND x1.bid_catid = y1.bid_catid
							AND x1.pincode = y1.pincode
							AND x1.parentid='".$var_parentid."'
							and x1.campaignid=y1.campaignid
							AND x1.bid_catid='".$explodedvalues_again[0]."'
							AND x1.pincode='".$explodedvalues_again[2]."'";
							$result_update_idc_d_dg_query=$conn_finance->query_sql($update_idc_d_dg_query);
							#echo "<br>";
						}
						ELSE{

						# Updating deal close table with the fetched catids from IDC
						#############################################################

							$update_idc_d_dg_query = "UPDATE tbl_d_dg_pin_dealclosed
							SET bidperday = ".$explodedvalues_again[1]."
							WHERE parentid='".$var_parentid."'
							and campaignid=2
							AND bid_catid=".$explodedvalues_again[0]."
							AND pincode=".$explodedvalues_again[2];
							$result_update_idc_d_dg_query=$conn_finance->query_sql($update_idc_d_dg_query);
							#echo "<br>";
						}

						unset($explodedvalues_again);

					} # End of For Loop

					# The below qry fires for the delta of categories between IDC and Local
					########################################################################

					$update_idc_d_dg_query_new = "UPDATE tbl_bidcatdetails_ddg_shadow m1,(
											SELECT x1.* FROM
											(SELECT * FROM tbl_ddgpin_entries WHERE parentid='".$var_parentid."')x1 LEFT JOIN (SELECT * FROM tbl_idc_entries WHERE parentid='".$var_parentid."')y1
											ON (x1.parentid = y1.parentid AND x1.bid_catid = y1.bid_catid AND x1.pincode = y1.pincode)
											WHERE y1.parentid IS NULL
										   )z1,
						tbl_d_dg_pin_dealclosed n1
						SET n1.bidperday = m1.bidperday
						WHERE m1.parentid = z1.parentid
						AND m1.bid_catid = z1.bid_catid
						AND m1.pincode = z1.pincode
						AND m1.parentid = n1.parentid
						and m1.campaignid=2
						and m1.campaignid=n1.campaignid
						AND m1.bid_catid = n1.bid_catid
						AND m1.pincode = n1.pincode
						AND n1.parentid='".$var_parentid."'";
					$result_update_idc_d_dg_query_new=$conn_finance->query_sql($update_idc_d_dg_query_new);

					mysql_close($link);
				}
				ELSE{

					#echo "No record Found for parentid ".$var_parentid." on IDC ";

					# If no recd found on IDC the below qry fetches everything from BCD Shadow
					###########################################################################

					$update_idc_d_dg_query_final = "UPDATE tbl_bidcatdetails_ddg_shadow x1, tbl_d_dg_pin_dealclosed y1
					SET y1.bidperday = x1.bidperday
					WHERE x1.parentid = y1.parentid
					and x1.campaignid=y1.campaignid
					AND x1.bid_catid = y1.bid_catid
					AND x1.pincode = y1.pincode
					AND x1.parentid='".$var_parentid."'";
					$result_update_idc_d_dg_query_final=$conn_finance->query_sql($update_idc_d_dg_query_final);

					echo "<br>";
				}
			}

		return;

} # Function Ends here
