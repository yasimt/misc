<?php
// changes needed
session_start();
set_time_limit(0);

## This process is to update area field where landmark is present in area field -- Pramesh chandra jha
if(!defined('APP_PATH'))
{    
require_once("../library/config.php");
require_once("../common/Serverip.php");
require_once(APP_PATH."library/path.php");
}

require_once(APP_PATH."web_services/curl_client.php");
GLOBAL $dbarr;
$conn_iro   = new DB($dbarr['DB_IRO']);
$conn_fin   = new DB($dbarr['DB_FNC']);
$conn_local	= new DB($dbarr['LOCAL']);
$loop_flag=true;

$tablename= "tbl_package_categoryaddition_backend";

while($loop_flag)
{
    $fetch_parentid="SELECT parentid FROM ".$tablename." WHERE done_flag=0 order by priority_flag desc limit 200"; //LIMIT 10
    #echo $fetch_parentid;
    $result_fetch_parentid=$conn_iro -> query_sql($fetch_parentid);
    $rowcount = mysql_num_rows($result_fetch_parentid);

    if($rowcount<=0) {
        echo "<br>No more parentids left for processing. File EXITED.";
        $loop_flag = false;
    }
    else
    {
        echo "<br> Total parentid to process".$rowcount;
        $today = date("Y-m-d H:i:s");
        echo "<br><b>PROCESS STARTED :-".$today."<br>";
        $counter=0;

        while($row = mysql_fetch_array($result_fetch_parentid))
        {
            $counter++;
            $parentid = $row['parentid'];
            $update_doneflag_query="UPDATE ".$tablename." SET done_flag=9 WHERE parentid='".$parentid."'";
            $result_update_doneflag_query=$conn_iro -> query_sql($update_doneflag_query);
         
		   insertCategoryFromreport($conn_fin,$conn_local,$parentid,$dbarr); 
		   web_api($parentid,'PACKCATADDBACKPROCESS','PACKCATADDBACKPROCESS','PACKCATADDBACKPROCESS');
           
           $update_doneflag_query="UPDATE ".$tablename." SET done_flag=1 WHERE parentid='".$parentid."'";
           $result_update_doneflag_query=$conn_iro -> query_sql($update_doneflag_query);
             
        }
        $today = date("Y-m-d H:i:s");
        echo "<br><b>PROCESS FINISHED :-".$today."<br>";
      
		if($counter%10==0)
		sleep(1);
    }
}

function insertCategoryFromreport($conn_fin,$conn_local,$parentid,$dbarr)
       {
	    $result= IsValidPackageContract($conn_fin,$parentid);
            if($result)
            {
                $finsql = "select version,bid_perday,data_city from tbl_companymaster_finance where parentid='".$parentid."' and campaignid=1 and balance>0";
                $finrs  = $conn_fin->query_sql($finsql);
                $finarr = $conn_fin->fetchData($finrs);

                if(intval($finarr['version'])>10)
                {
                    if(!is_object($curlobj)){
                        $curlobj = new CurlClass();
                    }
                     $postarr['parentid']    =   $parentid;
                     $postarr['version']     =   $finarr['version'];
                     $postarr['data_city']   =   $finarr['data_city'];
                     $postarr['bidperday']   =   $finarr['bid_perday'];
					 $postarr['astatus']     =	 2; 
					 $postarr['astate']		 =	 15;
			
			      $url = "http://".JDBOX_SERVICES_API."/invMgmt.php";
			      $ch = curl_init();
				  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				  curl_setopt($ch, CURLOPT_URL,$url);
				  curl_setopt($ch, CURLOPT_POSTFIELDS,$postarr);
				  $result=curl_exec($ch);
				  curl_close($ch);
                 }
			   }
		   }
		   
function IsValidPackageContract($conn_fin,$parentid)
{
	$returnval= 0;	
	//$finsql = "select campaignid from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and (budget=4 or budget=8 or budget=12) and campaignid in (5,13) and balance>0 ";
	$finsql = "select campaignid,balance,data_city from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (1,2) and  balance>0";
	$finrs = $conn_fin->query_sql($finsql);
	
	if($conn_fin->numRows($finrs))
	{	
		$fpbalance=0;
		$packbalance=0;

		$data_city=''; 
		
		while($fintemparr = $conn_fin->fetchData($finrs))
		{			
			if($fintemparr['campaignid']==1)
			{
				$packbalance=$fintemparr['balance'];
			}

			if($fintemparr['campaignid']==2)
			{
				$fpbalance=$fintemparr['balance'];
			}
			
			$data_city=$fintemparr['data_city'];
		}
		if($packbalance>1 && $fpbalance==0 )
		{
			$returnval= 1; 
		}
	}	
	
	return $returnval;
}
		  
?>

