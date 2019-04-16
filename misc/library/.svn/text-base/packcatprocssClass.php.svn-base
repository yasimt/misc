<?php

class packcatprocssClass
{

function getContractsCategory($conn_finance,$parentid,$tbl_bidcatdetails)
{
	$sql = "SELECT group_concat( DISTINCT bid_catid) as catids  FROM ".$tbl_bidcatdetails." where parentid='".$parentid."' ";
	$res = $conn_finance->query_sql($sql);
	$row = mysql_fetch_assoc($res);
	if($row['catids'])
	{
		return $row['catids'];
	}
}

function getPhoneSearchCategories($conn_fin,$parentid)
{
	$resultarr = array();
	$phonesearch_fin = "select campaignid from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and  campaignid in (1,2) and balance>0 "; // checking package and pdg categories
	$phonesearch_fin_rs = $conn_fin->query_sql($phonesearch_fin);
	$ContractsCategory= "";
	if($conn_fin->numRows($phonesearch_fin_rs))
	{
		while($phonesearch_fin_arr=$conn_fin->fetchData($phonesearch_fin_rs))
		{
			if($phonesearch_fin_arr['campaignid']==1)
			{
				//$ContractsCategorytemp = $this->getContractsCategory($conn_fin,$parentid,'tbl_bidcatdetails_supreme');				
			}
			
			if($phonesearch_fin_arr['campaignid']==2)
			{
				//$ContractsCategorytemp = $this->getContractsCategory($conn_fin,$parentid,'tbl_bidcatdetails_ddg');
			}			

			$ContractsCategory = $ContractsCategory.",".$ContractsCategorytemp;
			//echo "<br>ContractsCategory--".$ContractsCategory;
		}
	}

	$ContractsCategoryArr = explode(",",$ContractsCategory);
	$ContractsCategoryArr = array_unique($ContractsCategoryArr);
	$ContractsCategoryArr = array_filter($ContractsCategoryArr);
	$ContractsCategory = implode(",",$ContractsCategoryArr);
	return $ContractsCategory;
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
			$returnval= 1; // active banner contract
		}

		if($returnval==1 && defined("REMOTE_CITY_MODULE") && strtolower(DATA_CITY)!=strtolower($data_city))
		{
			echo "<center><font color='blue' >Current city:-".DATA_CITY." Contract City:-".$data_city."</font><br></center>";
			$returnval= -1;
		}
	}	
	
	return $returnval;
}

function validate_json($str=NULL) {

        if (is_string($str)) {
            @json_decode($str);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
}
    
function insertCategoryFromreport($conn_fin,$conn_local,$parentid,$dbarr)
{
	$result= $this->IsValidPackageContract($conn_fin,$parentid);



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
						
			if(defined("REMOTE_CITY_MODULE") && strtolower(DATA_CITY)!=strtolower($finarr['data_city']))
			{
				echo "<center><font color='blue' >Current city:-".DATA_CITY." Contract City:-".$finarr['data_city']."</font><br></center>";
				$returnval= -1;
				exit;
			}

			$postarr['parentid']	=	$parentid;
			$postarr['version']		=	$finarr['version'];
			$postarr['data_city']	=	DATA_CITY;			
			$postarr['bidperday']	= $finarr['bid_perday'];


			// for financial approval 
			$postarr['astatus']		=	2; 
			$postarr['astate']		=	15;
			
			
			$curlurl = "http://".JDBOX_SERVICES_API."/invMgmt.php";
			$log_data['URL'] = $curlurl;
			$curlobj->setOpt(CURLOPT_CONNECTTIMEOUT, 30);
			$curlobj->setOpt(CURLOPT_TIMEOUT, 900);
			$output = $curlobj->post($curlurl,$postarr,1);
			$log_data['POSTDATA'] = json_encode($postarr);
			$log_data['OUTPUT'] = $output;			
			
			$result     = json_decode($output,true);

			//echo $curlurl;
			//echo print_r($postarr);
			//print_r($result);
			
			
			$fail_count = count($result['results']['fail']);
			$error_msg  = $result['error']['msg'];
					
			$valid_json = ($this->validate_json($output) ? 1 :2);
		
			if ($valid_json==2) {
				  echo "<br><center><div style='color: #9F6000;background-color: #FEEFB3;font-family: Georgia, Arial,Serif;font-size:14px;font-weight: bold;width:50%;text-align:center;padding:12px;vertical-align:middle;' >Something went wrong, please try after some time</div><br/><br/><br/>";
				  exit;
			}			

			if($result['error']['code']==1 && is_array($result['results']['fail']) && $fail_count>0)
			{
				$categoryMaster_obj= new categoryMaster($dbarr,APP_MODULE);
		 
				echo "<br><center><div style='color: #D8000C;background-color: #FFBABA;font-family: Georgia, Arial,Serif;font-size:14px;font-weight: bold;width:50%;text-align:center;padding:10px;vertical-align:middle;' >The desired positions of this contract might be lost for these below categories & pincode, <BR>please make fresh deal close once again</div><br/>";
				echo "<table align='center' width='50%'><tr bgcolor='#6F7175' style='height:40px;color:#FFF;' align='center'><th> Category Name </th><th> Pincode </th><th> Position </th></tr>";
				foreach($result['results']['fail'] as $failcatid=>$failcatidarr)
				{
				
					$failcatid_name = $categoryMaster_obj->getCatname($failcatid);

					foreach($failcatidarr as $failpincode =>$failpincodearr)	            {
					
						foreach($failpincodearr as $failpincodearr_key=>$failpincodearr_val)
						{
							 echo "<tr height=30>
								 <td align='center' style='width:45%;padding-left: 0.5%;border-left:1px solid #A5A4A4;color:#192E4D;border-right:1px solid #A5A4A4;border-bottom: 1px solid #A5A4A4;font-size:12px;'>".$failcatid_name[$failcatid]['category_name']."</td>
								 <td align='center' style='width:25%;padding-left: 0%;color:#192E4D;border-right:1px solid #A5A4A4;border-bottom: 1px solid #A5A4A4;font-size:12px;'>".$failpincode."</td>
								 <td align='center' style='width:30%;padding-left: 0%;color:#192E4D;border-right:1px solid #A5A4A4;border-bottom: 1px solid #A5A4A4;font-size:12px;' >".$failpincodearr_val."</td>
								 </tr>";
						}
					}

				}
				echo "</table>";

				if($stopexecution) { 
					exit;
				} 
			}
		
			if ($result['error']['code']==1)
			{
			
			   $error_msg = $result['error']['msg'];
			   if($stopexecution) {
				
					echo "<br><center><div style='color: #9F6000;background-color: #FEEFB3;font-family:Georgia, Arial,Serif;font-size:14px;font-weight: bold;width:50%;text-align:center;padding:10px;vertical-align:middle;' >".$error_msg."<BR><BR><a href='accInstrument.php?mode=3'>Click</a> to go back</div><br/><br/><br/>";
					exit;
			   } else { 
					 $error_array['error_code']= 2;
					 $error_array['error_msg'] = $error_msg;
					 return $error_array;
			   }
			}
			
		}
		web_api($parentid,$_SESSION['ucode'],$_SESSION['uname'],'PUREPACKPROCESS');
	}
	else
	{
		echo "<br> Not a pure Package Contract";
	}
	
}

}



?>
