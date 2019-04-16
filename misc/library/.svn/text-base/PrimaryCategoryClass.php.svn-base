<?php

class primaryCategory
{

function primaryCategoryInsertion($parent_id,$conn_iro,$conn_local,$override=0)
{
	$this->logmsgautosuggest(" primaryCategoryInsertion called with parent_id=".$parent_id." override=".$override,$parent_id);
	
	$sql = "SELECT parentid FROM mapcategory WHERE parentid = '".$parent_id."' "; // if contact is present in table then do not change it
	$res = $conn_local->query_sql($sql);
	
	if(mysql_num_rows($res)>0)
	{
		$this->logmsgautosuggest(" parentid present in mapcategory so exiting from function... ",$parent_id);
		return true;
	}
	
	
	
	if($override!=1)
	{
		$sql_nonpaid = "SELECT parentid FROM tbl_temp_intermediate WHERE parentid = '".$parent_id."' AND nonpaid!=1 LIMIT 1";
		$res_nopaid = $conn_local->query_sql($sql_nonpaid);
		
		if($res_nopaid && mysql_num_rows($res_nopaid)>0)
		{// this is paid contract so we will not insert into mapcategory table
			$this->logmsgautosuggest(" condition for paid satisfied exiting from function... ",$parent_id);
			return true;
			
		}
	}
	
	$sql = "SELECT parentid,companyname FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$parent_id."' LIMIT 1";
	$res = $conn_iro->query_sql($sql);
	//echo "<br>". $sql."<br>";
	
	// This  will result only nonpaid contract
		
	if($res && mysql_num_rows($res))
	{
		$row = mysql_fetch_assoc($res);
		$contract_id  = $row['parentid'];
		$company_name = addslashes(stripslashes($row['companyname']));

		if($contract_id)
		{
			// since we are inserting both primary and popular category so we are deleting from table
			$sql = "DELETE FROM mapcategory WHERE parentid = '".$parent_id."' and flag='C2L'  AND ifnull(editexclude,0) <> 1 "; // as per meeta we will delete only c2l not c2c 
			$res = $conn_local->query_sql($sql);
			//echo "<br>". $sql."<br>";	
			// fetching tag_category or primary category
			$tag_catidsql = "select tag_catid,tag_catname  from tbl_companymaster_extradetails_shadow where parentid= '".$parent_id."' and tag_catid!='' ";
			$tag_catidres = $conn_iro->query_sql($tag_catidsql);
			
			$tag_catid_flag=0;
			$tag_catid=0;
			
			if($tag_catidres && mysql_num_rows($tag_catidres)>0)
			{
				$tag_catidarr_temp= mysql_fetch_assoc($tag_catidres);
				
				$tag_catid_temp =$tag_catidarr_temp['tag_catid'];								
				
				$pmcat_sql="SELECT catid,category_name as catname,national_catid from tbl_categorymaster_generalinfo WHERE catid IN (".$tag_catid_temp.") and not (promt_ratings_flag & 1=1 OR promt_ratings_flag & 2=2  OR promt_ratings_flag & 32=32) AND (category_verticals & 8 != 8)";
				$pmcat_rs = $conn_local->query_sql($pmcat_sql);
				
				if($pmcat_rs  && mysql_num_rows($pmcat_rs)>0)
				{
					$tag_catidarr= mysql_fetch_assoc($pmcat_rs);
					$tag_catid=$tag_catidarr['catid'];
					$tag_national_catid=$tag_catidarr['national_catid'];
					$tag_catid_flag=1;
					$this->logmsgautosuggest("taged category found catid=".$tag_catid,$parent_id);
				}				
			}
			
			
			$qryCatid 		= "SELECT catIds FROM tbl_business_temp_data WHERE contractid='".$parent_id."'";
			$resultset 		= $conn_local->query_sql($qryCatid);
			$rescatid		= mysql_fetch_assoc($resultset);
			
			$catidstr = $rescatid['catIds'];
			
			$catidarr = explode("|P|" ,$catidstr);
						
			$catidarr = array_unique($catidarr);
			$catidarr = array_filter($catidarr);
			$catidarr = array_merge($catidarr);
			
			
			
			//echo "<pre>catidarr";print_r($catidarr);
			
			$catids_list = implode(",",$catidarr);
			
			$this->logmsgautosuggest(" catIds of tbl_business_temp_data=".$catids_list,$parent_id);
			
			//echo "<pre>catids_list".$catids_list;
			//echo "<br> tag_catid".$tag_catid;
			if(count($catidarr)>0) 
			{
				$sql="SELECT catid,category_name as catname,national_catid from tbl_categorymaster_generalinfo WHERE catid IN (".$catids_list.") and not (promt_ratings_flag & 1=1 OR promt_ratings_flag & 2=2  OR promt_ratings_flag & 32=32) AND (category_verticals & 8 != 8) ORDER BY callcount DESC LIMIT 1";
				$res_catms = $conn_local->query_sql($sql);

				if($res_catms && mysql_num_rows($res_catms)>0)
				{
					$row_catms = mysql_fetch_assoc($res_catms);
					
					$catid_catms = $row_catms['catid'];
					$national_catid_catms = $row_catms['national_catid'];
					
					//echo "catid_catms".$catid_catms."==tag_catid".$tag_catid;
					$this->logmsgautosuggest("Popular category".$catid_catms."---Primary category".$tag_catid,$parent_id);			
					if($catid_catms==$tag_catid)
					{// popular category is same as taged category so only on erecord will be inserted 
						$insertsql = "INSERT INTO mapcategory SET
						contactid 	= '".$parent_id."',
						parentid	=  '".$parent_id."',
						companyname ='".addslashes($row['companyname'])."',
						script      ='Shall I provide you numbers of few more ".addslashes($row_catms['catname'])." companies who may give you a better deal?.',
						catid       =".$catid_catms.",
						national_catid=".$national_catid_catms.",
						catlineage  ='".'/'.addslashes($row_catms['catname']).'/'."',
						flag        ='C2L',
						activeFlag  =2,
						empCode	 ='".$_SESSION['ucode']."',
						mapDate 	 ='".date('Y-m-d H:i:s')."'
						
						ON DUPLICATE KEY UPDATE
						
						companyname ='".addslashes($row['companyname'])."',
						script      ='Shall I provide you numbers of few more ".addslashes($row_catms['catname'])." companies who may give you a better deal?.',
						catid       =".$catid_catms.",
						national_catid=".$national_catid_catms.",
						catlineage  ='".'/'.addslashes($row_catms['catname']).'/'."',
						flag        ='C2L',
						activeFlag  =2,
						empCode	 	= '".$_SESSION['ucode']."',
						mapDate 	= '".date('Y-m-d H:i:s')."'";
						
						$conn_local->query_sql($insertsql);
						//echo "<br>1".$insertsql;
						$this->logmsgautosuggest("Popular category and primary category is same ",$parent_id,$insertsql);
						
						
					}
					else
					{ 
						//popular category is different from taged category so two erecord will be inserted 
						if($tag_catid_flag)
						{//taged category found in contract
							
							$insertsql = "INSERT INTO mapcategory SET
							contactid 	='".$parent_id."',
							parentid	='".$parent_id."',
							companyname ='".addslashes($row['companyname'])."',
							script      ='Shall I provide you numbers of few more ".addslashes($tag_catidarr['catname'])." companies who may give you a better deal?.',
							catid       =".$tag_catidarr['catid'].",
							national_catid=".$tag_catidarr['national_catid'].",
							catlineage  ='".'/'.addslashes($tag_catidarr['catname']).'/'."',
							flag        ='C2L',
							activeFlag  =2,
							empCode	 	='".$_SESSION['ucode']."',
							mapDate 	 ='".date('Y-m-d H:i:s')."'
							
							ON DUPLICATE KEY UPDATE
							
							companyname ='".addslashes($row['companyname'])."',
							script      ='Shall I provide you numbers of few more ".addslashes($tag_catidarr['catname'])." companies who may give you a better deal?.',
							catid       =".$tag_catidarr['catid'].",
							national_catid=".$tag_catidarr['national_catid'].",
							catlineage  ='".'/'.addslashes($tag_catidarr['catname']).'/'."',
							flag        ='C2L',
							activeFlag  =2,
							empCode	 	= '".$_SESSION['ucode']."',
							mapDate 	= '".date('Y-m-d H:i:s')."'";
							
							$conn_local->query_sql($insertsql);
							//echo "<br>2".$insertsql;
							$this->logmsgautosuggest("Popular category and primary category is diffrent primary category entry",$parent_id,$insertsql);
							
						}
						// processing popular category entry 
						
						$insertsql = "INSERT INTO mapcategory SET
						contactid 	='".$parent_id."',
						parentid	='".$parent_id."',
						companyname ='".addslashes($row['companyname'])."',
						script      ='Shall I provide you numbers of few more ".addslashes($row_catms['catname'])." companies who may give you a better deal?.',
						catid       =".$catid_catms.",
						national_catid=".$national_catid_catms.",
						catlineage  ='".'/'.addslashes($row_catms['catname']).'/'."',
						flag        ='C2L',
						activeFlag  =0,
						empCode	 	='".$_SESSION['ucode']."',
						mapDate 	 ='".date('Y-m-d H:i:s')."'
						
						ON DUPLICATE KEY UPDATE
						
						companyname ='".addslashes($row['companyname'])."',
						script      ='Shall I provide you numbers of few more ".addslashes($row_catms['catname'])." companies who may give you a better deal?.',
						catid       =".$catid_catms.",
						national_catid=".$national_catid_catms.",
						catlineage  ='".'/'.addslashes($row_catms['catname']).'/'."',
						flag        ='C2L',
						activeFlag  =0,
						empCode	 	= '".$_SESSION['ucode']."',
						mapDate 	= '".date('Y-m-d H:i:s')."'";
						
						$conn_local->query_sql($insertsql);
						$this->logmsgautosuggest("Popular category and primary category is diffrent popular category entry",$parent_id,$insertsql);
						//echo "<br>3".$insertsql;
						
					}						
				}
			}
			
		}
	}
//echo "<br>library/PrimaryCategoryClass.php".$parent_id; exit;
}

function logmsgautosuggest($sMsg,$contractid,$extra_str='')
{
	return ;// space issue so no need to make text log - As per Rohit Sir
	$log_msg='';
	$log_path = APP_PATH.'logs/primarycategory/';
	$sNamePrefix= $log_path;
	// fetch directory for the file
	$pathToLog = dirname($sNamePrefix); 
	if (!file_exists($pathToLog)) {
		mkdir($pathToLog, 0755, true);
	}
	/*$file_n=$sNamePrefix.$contractid.".txt"; */
	$file_n=$sNamePrefix.$contractid.".html";
	// Set this to whatever location the log file should reside at.
	$logFile = fopen($file_n, 'a+');

		
	$userID= $_SESSION['ucode'];
	/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
	$pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
	$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
					<tr valign='top'>
						<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
						<td style='width:15%; border:1px solid #669966'>File name:".$pageName."</td>
						<td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
						<td style='width:30%; border:1px solid #669966'>Query: ".$extra_str."</td>
						<td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
						</tr>
				</table>";
	fwrite($logFile, $log_msg);
	fclose($logFile);
}

}// end of class
?>
