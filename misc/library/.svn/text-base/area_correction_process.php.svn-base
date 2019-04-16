<?php
session_start();
set_time_limit(0);

	$detail= "\n code - ".$_SESSION[ucode]."\n name - ".$_SESSION[uname];
	$ipAddress = $_SERVER['REMOTE_ADDR'];
	$ipAddress.=$detail;
	
	mail('prameshjha@justdial.com','UnAuthorised access attempt run library/area_correction_process.php  ', $ipAddress);
	echo "<br><h3> <br>You Are not authorized to run this process <br>Mail send successfully  Your Details -> ".$ipAddress." </h3>";
	exit;
	// changes needed
## This process is to update area field where landmark is present in area field -- Pramesh chandra jha
if(!defined('APP_PATH'))
{
    require_once("../library/config.php");
}
include_once(APP_PATH."library/path.php");

//require_once("config.php");
//require_once("../common/Serverip.php");
require_once(APP_PATH."web_services/curl_client.php");
require_once(APP_PATH."library/historyLog.php");

GLOBAL $dbarr;

$conn_iro		= new DB($dbarr['DB_IRO']); 
$conn_local		= new DB($dbarr['LOCAL']);
$conn_finance	= new DB($dbarr['FINANCE']);
$conn_idc   = new DB($dbarr['IDC']);

# Process Started #

$sql_ch = "show tables like 'tbl_areachange_process'";
$sql_ch_rs = $conn_iro->query_sql($sql_ch);

if(mysql_num_rows($sql_ch_rs)>0)
{
	$currenttime = date("Y-m-d H:i:s");
	$currenttime = str_replace("-","",$currenttime);
	$currenttime = str_replace(" ","_",$currenttime);
	$currenttime = str_replace(":","_",$currenttime);
	$renametable="tbl_areachange_process_temp".$currenttime;
	$sql = "ALTER TABLE tbl_areachange_process  RENAME ".$renametable;
	$conn_iro->query_sql($sql);
	
}

$sql = " CREATE TABLE `tbl_areachange_process` (
	parentid varchar(60) NOT NULL,
	companyname varchar(250) DEFAULT NULL COMMENT 'name of company',
	building_name varchar(100) DEFAULT NULL,
	landmark_gi varchar(100) DEFAULT NULL,
	landmark_corrected varchar(100) DEFAULT NULL,
	street varchar(100) DEFAULT NULL,
	area_gi varchar(100) DEFAULT NULL,
	subarea_am varchar(100) DEFAULT NULL,
	city varchar(100) DEFAULT NULL,
	data_city varchar(100) DEFAULT NULL,
	full_address_gi text,
	full_address_processed text,
	pincode_gi varchar(15)  DEFAULT NULL,
	pincode_am varchar(15)  DEFAULT NULL,
	paid  tinyint(1),
	paid_fin  tinyint(1),
	done_flag tinyint(4) DEFAULT '0',
	PRIMARY KEY (`parentid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1";
$conn_iro->query_sql($sql);


$sql = "insert into tbl_areachange_process (parentid, companyname, building_name, landmark_gi, street, area_gi, subarea_am, city, data_city, full_address_gi, pincode_gi,pincode_am , paid ) select parentid, companyname,building_name,gi.landmark,street,gi.area,am.subarea,gi.city,am.data_city,gi.full_address, gi.pincode,am.pincode,paid from (select area,pincode,subarea,type_flag,data_city from d_jds.tbl_area_master where ( subarea is not null and subarea<>'')and type_flag=2) am   join  tbl_companymaster_generalinfo  gi on ( gi.area=am.area and gi.pincode=am.pincode) ";

$conn_iro->query_sql($sql);

//and process_flag=0 
$corectlanmarksql="Select corrected_landmark,area,pincode from online_regis1.tbl_area_master_updated WHERE type_flag=2 AND corrected_landmark!='' AND display_flag>0";
 
$rs = $conn_idc->query_sql($corectlanmarksql);
$arr= array();
while($row_idc=mysql_fetch_assoc($rs))
{
		$arrayParentid=Array();
		$sql_ap="SELECT parentid FROM tbl_areachange_process WHERE landmark_gi='' AND  area_gi='".$row_idc['area']."' AND pincode_gi='".$row_idc['pincode']."'";
		$rs_ap = $conn_iro->query_sql($sql_ap);
		while($row_ap=mysql_fetch_assoc($rs_ap))
		{
				array_push($arrayParentid,$row_ap['parentid']);
		}
		$parentid_list=implode("','",$arrayParentid);
		$upt_ap="UPDATE tbl_areachange_process SET landmark_corrected='".$row_idc['corrected_landmark']."' WHERE parentid IN ('".$parentid_list."')";
		$rs_upt_ap = $conn_iro->query_sql($upt_ap);	
		
		
		$key=$row_idc['area'];
		
		$sum +=count($arrayParentid);
		$arr[$key]= count($arrayParentid);
		
}



$loop_flag=true;
$thru_wrapper=1;
$counter=0;
$valiationcode='AREACR';


$today = date("Y-m-d H:i:s");
echo "<br><b>PROCESS STARTED :-".$today."<br>";

while($loop_flag)	
{
	$fetch_parentid="SELECT * FROM tbl_areachange_process WHERE done_flag=0 limit 200"; //LIMIT 10
	#echo $fetch_parentid;
	$result_fetch_parentid=$conn_iro -> query_sql($fetch_parentid);

	$rowcount = mysql_num_rows($result_fetch_parentid);

	IF ($rowcount<=0) {

		echo "<br>No more parentids left for processing.";
		$loop_flag = false;
		$today = date("Y-m-d H:i:s");
		echo "<br><b>PROCESS FINISHED :-".$today."<br>";
	}
	ELSE
	{
		

		WHILE ($row = mysql_fetch_array($result_fetch_parentid))
		{
			$counter++;
			$var_parentid = $row['parentid'];
			
			echo "<BR>counter=".$counter.",parentid=".$var_parentid; 
			
			 $fetch_paid = "SELECT * FROM tbl_companymaster_finance WHERE parentid='".$var_parentid."' ";
			 $result_fetch_paid = $conn_finance -> query_sql($fetch_paid);
			 if($result_fetch_paid && mysql_num_rows($result_fetch_paid)>0)
			 {
				 //Entry found in tbl_companymaster_finance so do not do any thing
				$update_donepaidflag_query="UPDATE tbl_areachange_process SET done_flag=5,paid_fin=1 WHERE parentid='".$var_parentid."'";
				$conn_iro -> query_sql($update_donepaidflag_query);
				//continue;
				
			 }
			 			
			if($objLog == null)
			{$objLog= new contractLog($var_parentid,APP_MODULE,"Area correction process",$dbarr);}
			else
			{$objLog->newContractLog($var_parentid);}
			
			
			
			if(trim($row[landmark_gi])=="") // if landmark is blank we are replacing with exising area which is actually landmark
			{
				if(trim($row[landmark_corrected])!="")
				{					
					$row[landmark_gi] 	= trim($row[landmark_corrected]);
				}
				else
				{
					$row[landmark_gi] 	= trim($row[area_gi]);
				}
				
			}
			
			$landmark_gi = ",".$row[landmark_gi];
			
			$row[area_gi]		= trim($row[subarea_am]); // changing area with main area of area_master
			
			if(strlen(trim($row[area_gi]))>0)
			{
				$area_gi = ",".trim($row[area_gi]);
			}
			
			
			$row[companyname]	= trim($row[companyname]);
			
			if(strlen(trim($row[building_name]))>0)
			{
				$row[building_name] = ",".trim($row[building_name]);
			}
			
			if(strlen(trim($row[street]))>0)
			{
				$row[street] = ",".trim($row[street]);
			}
			
			//companyname,landmark_gi,building_name,street,area_gi,pincode_gi
			
			$full_address = $row[companyname].$landmark_gi.$row[building_name].$row[street].$area_gi."-".$row[pincode_gi];
			
			$updategeninfo= "UPDATE tbl_companymaster_generalinfo SET area='".addslashes($row[subarea_am])."',landmark='".addslashes($row[landmark_gi])."', full_address='".addslashes($full_address)."' WHERE parentid='".$var_parentid."'";
			$conn_iro -> query_sql($updategeninfo);
			
			$updaextradetail="UPDATE tbl_companymaster_extradetails SET db_update=now() WHERE parentid='".$var_parentid."'";
			$conn_iro -> query_sql($updaextradetail);
			
			
			
//			web_api($var_parentid,$_SESSION['ucode'],$_SESSION['uname'],$valiationcode);
			web_api($var_parentid,"888888","temp user",$valiationcode);

			$update_doneflag_query="UPDATE tbl_areachange_process SET done_flag=9,full_address_processed='".addslashes($full_address)."' WHERE parentid='".$var_parentid."'";
			$result_update_doneflag_query=$conn_iro -> query_sql($update_doneflag_query);
			
			
		}
	}
	// after 200 contract sleep for 2 sec
	sleep(2);	

}


?>
