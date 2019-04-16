<?php

// This class behave similar to history process

class dealcloseCatDel
{
// we will take snapshot of table at two times one before any update and one after all update like history 
//13	Category Sponsorship
//14	Category Filter Banner
//15	Category Text Banner

private $oldCategoryDetails,$newcategoryDetails;
private  $oldCatSpon,$oldCatText, $newCatSpon,$newCatText;
private $snapsstatus; // 0 -old , 1- new 
private $module;
private $conn_iro,$conn_local,$conn_iro_main; //,$conn_finance
private $dbarr;

private $companyname,$userid,$username;
public function __construct($parentid,$dbarr,$userid,$username,$module){
		
		$this->parentid	= $parentid;
		$this->userid	= $userid;
		$this->username	= $username;
		
		$this->module	= strtolower($module);
		$this->dbarr	= $dbarr;
		
		$this->setsnapsstatus(0); // taking old detail on constructor
		$this->oldCategoryDetails = array();
		$this->newcategoryDetails = array();		
		$this->oldCatSpon = array();
		$this->oldCatText = array();
		$this->newCatSpon = array();
		$this->newCatText = array();
		$this->oldCompBanner = array();
	    $this->newCompBanner = array();		
		$this->populateDeletedCategory(); // taking snapshot of old data		
}

function __destruct()
{
	$this->setsnapsstatus(1); // taking new detail on distructor
	$this->populateDeletedCategory();  // taking snapshot of new data
	
	$this->insertIntoTable();
	$this->insertIntoCompBann();

	unset($this->oldCategoryDetails);
	unset($this->newcategoryDetails);
	unset($this->oldCatSpon);
	unset($this->oldCatText);
	unset($this->newCatSpon);
	unset($this->newCatText);
	unset($this->oldCompBanner);
	unset($this->newCompBanner);
	$this->conn_iro->close();
	$this->conn_local->close();
	//$this->conn_finance->close();
	unset($this->conn_iro);
	unset($this->conn_local);
	//unset($this->conn_finance);
	//die("on distructor of dealcategory");


}


function setsnapsstatus($val)
{
	$this->snapsstatus=$val;
	
	$this->conn_iro		= null;	
	$this->conn_local	= null;
	$this->conn_iro_main	= null;	
	//$this->conn_finance	= null;

	//echo "<pre>".$this->module." ^^^ ".$val."<BR>"; //print_r($this->dbarr);
	//die("die on setsnapsstatus");
	
	if($this->module=="cs")
	{
		//echo $this->module." ^cs^ ".$val;
		$this->conn_iro		= new DB($this->dbarr['DB_IRO']);	
		$this->conn_local	= new DB($this->dbarr['LOCAL']);			
		$this->conn_iro_main	= new DB($this->dbarr['DB_IRO']);	
	}
	elseif($this->module=="tme")
	{
		//echo $this->module." ^tme^ ".$val; 
		if($val==1) // new tme data 
		{
			$this->conn_iro		= new DB($this->dbarr['IDC']);	
			$this->conn_local	= new DB($this->dbarr['IDC']);		
		}
		elseif($val==0) // old tme data 
		{
			$this->conn_iro		= new DB($this->dbarr['DB_IRO']);	
			$this->conn_local	= new DB($this->dbarr['LOCAL']);		
		}
		$this->conn_iro_main	= new DB($this->dbarr['DB_IRO']);
		
	}
	elseif($this->module=="me")
	{
		//echo $this->module." ^me^ ".$val; 
		if($val==1) // new me data 
		{
			$this->conn_iro		= new DB($this->dbarr['DB_IRO']);	
			$this->conn_local	= new DB($this->dbarr['DB_IRO']);		
		}
		elseif($val==0) // old me data 
		{
			$this->conn_iro		= new DB($this->dbarr['DB_SERVER_IRO']);	
			$this->conn_local	= new DB($this->dbarr['DB_SERVER_DECS']);		
		}
		$this->conn_iro_main	= new DB($this->dbarr['DB_SERVER_IRO']);
	}	
}


function getCategory()
{
	$compmaster_obj	= new companyMasterClass($this->conn_iro,DATA_CITY,$this->parentid);

	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "catidlineage,companyname";
	$tablename		= "tbl_companymaster_extradetails";
	$wherecond		= "parentid ='".$this->parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

$select_categoty_ed_array = $temparr['data']['0'];
$catidarr_main = $select_categoty_ed_array['catidlineage'];
$catidarr_main = str_replace("/","", $catidarr_main); // removing all / from string
$catidarr_main_arr = explode(",",$catidarr_main);

$catidarr_main_arr = array_unique($catidarr_main_arr);
$catidarr_main_arr = array_filter($catidarr_main_arr);
$catidarr_main_arr = array_merge($catidarr_main_arr);

if($this->snapsstatus==0)
{
	$this->oldCategoryDetails=$catidarr_main_arr;
}
elseif($this->snapsstatus==1)
{
	$this->newcategoryDetails=$catidarr_main_arr;
}
$this->companyname = $select_categoty_ed_array[companyname];

}

function getCompBanner()
{
$select_comp_banner = "SELECT GROUP_CONCAT(catid) catlist, campaign_type FROM tbl_comp_banner WHERE parentid = '".$this->parentid."' group by campaign_type";
$select_comp_banner_res = $this->conn_local->query_sql($select_comp_banner);
$comp_banner_type_arr = array();
if(mysql_num_rows($select_comp_banner_res)>0)
{
	while($compBanner_arr = mysql_fetch_assoc($select_comp_banner_res))
	{
		$comp_banner_type_arr = explode(",",$compBanner_arr[catlist]);
		if(count($comp_banner_type_arr))
		{
		$comp_banner_type_arr = array_unique($comp_banner_type_arr);
		$comp_banner_type_arr = array_filter($comp_banner_type_arr);
		$comp_banner_type_arr = array_merge($comp_banner_type_arr);
		}
	}
}
if($this->snapsstatus==0)
{
	$this->oldCompBanner = $comp_banner_type_arr;
}
elseif($this->snapsstatus==1)
{
	$this->newCompBanner = $comp_banner_type_arr;
}

}

function getCatsponCategory()
{

$select_categoty_ed = "select GROUP_CONCAT(catid) catlist,campaign_type  FROM tbl_catspon where parentid='".$this->parentid."' group by campaign_type";
$select_categoty_ed_res = $this->conn_local->query_sql($select_categoty_ed);
$campaign_type1_main_arr = array(); 
$campaign_type2_main_arr = array();
if(mysql_num_rows($select_categoty_ed_res)>0)
{
	
	
	while($catspooncategoryarray = mysql_fetch_assoc($select_categoty_ed_res))
	{
			
		if($catspooncategoryarray[campaign_type]==1)
		{
			$campaign_type1_main_arr = explode(",",$catspooncategoryarray[catlist]);
			
			if(count($campaign_type1_main_arr))
			{			
			$campaign_type1_main_arr = array_unique($campaign_type1_main_arr);
			$campaign_type1_main_arr = array_filter($campaign_type1_main_arr);
			$campaign_type1_main_arr = array_merge($campaign_type1_main_arr);
			}
		}
		
		if($catspooncategoryarray[campaign_type]==3)
		{
			$campaign_type2_main_arr = explode(",",$catspooncategoryarray[catlist]);
			
			if(count($campaign_type2_main_arr))
			{
			$campaign_type2_main_arr = array_unique($campaign_type2_main_arr);
			$campaign_type2_main_arr = array_filter($campaign_type2_main_arr);
			$campaign_type2_main_arr = array_merge($campaign_type2_main_arr);
			}
			
		}
		
		
	}
	
}

//$oldCatSpon,$oldCatText, $newCatSpon,$newCatText;
if($this->snapsstatus==0)
{
	if(count($campaign_type1_main_arr))
	{
		$this->oldCatSpon=$campaign_type1_main_arr;
    }
	
	if(count($campaign_type2_main_arr))
	{
		$this->oldCatText=$campaign_type2_main_arr;
	}
	
}
elseif($this->snapsstatus==1) // new
{
	if(count($campaign_type1_main_arr))
	{
		$this->newCatSpon=$campaign_type1_main_arr;
		
	}
	
	if(count($campaign_type2_main_arr))
	{
		$this->newCatText=$campaign_type2_main_arr;
	}
}
}

function getCatname($catidarry)
{
	$catarray = null;
	if(count($catidarry))
	{
		$catidarry =  array_filter($catidarry);
		$catidarry =  array_unique($catidarry);
		
		$catidlst= implode(",",$catidarry);
		$sql="SELECT catid,category_name as catname from d_jds.tbl_categorymaster_generalinfo where catid in  (".$catidlst.") group  by catid ";
		$catrs = $this->conn_iro_main->query_sql($sql);
		if($catrs && mysql_num_rows($catrs))
		{
			while($catarr = mysql_fetch_assoc($catrs))
			{
				$catarray[$catarr[catid]]= $catarr[catname];
				//echo "<br>".$catarr[catid]."=".$catarr[catname];
			}
			
			
		}
	}
	//echo "<pre>" ; print_r($catarray);
	return $catarray;
	
}


function populateDeletedCategory()
{
$this->getCategory();
$this->getCatsponCategory();
$this->getCompBanner();
}

function sendEmail()
{


	if($this->module=="tme" || $this->module=="me")  // we send email only for TME /ME
	{

	$mailfailed=0;
	$to="";
	$removedcatidarry1 = array();
	$removedcatidarry2 = array();
	$removedcatidarry3 = array();

	$removedcatidarry1 = array_diff($this->oldCategoryDetails,$this->newcategoryDetails);// general category entry
	$removedcatidarry2 = array_diff($this->oldCatSpon,$this->newCatSpon); // catspon entry
	$removedcatidarry3 = array_diff($this->oldCatText,$this->newCatText); // catspon entry 
	
	if(count($removedcatidarry1) || count($removedcatidarry2)|| count($removedcatidarry3))
	{
		
		$empd= "select empParent,empName,emailId from d_jds.mktgEmpMaster where mktEmpCode ='".$this->userid."' ";
		$empdrs = $this->conn_iro_main->query_sql($empd);
		$empdetarr= array();
		if($empdrs && mysql_num_rows($empdrs))
		{
			$empdetarr = mysql_fetch_assoc($empdrs);
		}
		
		if(count($empdetarr))
		{
			$empd= "select empName,emailId from d_jds.mktgEmpMaster where mktEmpCode ='".$empdetarr[empParent]."' ";
			$empdrs = $this->conn_iro_main->query_sql($empd);
			$empparentdetarr= array();
			if($empdrs && mysql_num_rows($empdrs))
			{
				$empparentdetarr = mysql_fetch_assoc($empdrs);		
							
				$subject = 'Category deletion on deal close by '.$empdetarr[empName].'('.$this->userid.')';
				$message = 'Dear <b>'.$empparentdetarr[empName].'</b>';
				
				$message.= '<br>Following existing categories are deleted by<b> '.$empdetarr[empName].'('.$this->userid.')'.' </b>From Contract <b>'.$this->companyname.'('.$this->parentid.') </b> at the time of  deal close.
							<br>This may affect client \'s lead response.
							<br>pls check with concerned employee';
				
				
				$catdetail="";
				if(count($removedcatidarry1))
				{
					$catarry = $this->getCatname($removedcatidarry1);
					if(count($catarry))
					{
						$catdetail= "<br><br>Details of contract Category removed  <br>No. - Category Name ";
						$i=1;
						foreach($catarry as $catis=>$catname)
						{
							$catdetail.="<br>".$i++."&nbsp;&nbsp;".$catname;
						}						
					}	
				}
				
				if(count($removedcatidarry2))
				{
					$catarry = $this->getCatname($removedcatidarry2);
					if(count($catarry))
					{$catdetail.= "<br><br>Details Category Sponsership category removed <br>No. - Category Name ";				
						$i=1;
						foreach($catarry as $catis=>$catname)
						{
							$catdetail.="<br>".$i++."&nbsp;&nbsp;".$catname;
						}						
					}	
				}				  
				
				if(count($removedcatidarry3))
				{
					$catarry = $this->getCatname($removedcatidarry3);
					if(count($catarry))
					{
						$catdetail.= "<br><br>Details of  Category text category removed  <br>No. - Category Name ";
						$i=1;
						foreach($catarry as $catis=>$catname)
						{
							$catdetail.="<br>".$i++."&nbsp;&nbsp;".$catname;
						}						
					}	
				}
				$message.=$catdetail;
				
				
				
				$to      = $empparentdetarr[emailId];
				if($to)
				{
				$headers = 'From: justdial@justdial.com' . "\r\n" .
				'Cc:'.$empdetarr[emailId] . "\r\n" ;				
				$headers.= "MIME-Version: 1.0\r\n";
				$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				$flag=mail($to, $subject, $message, $headers);
				}
				else
				{
					$mailfailed=1;
				}
/*
				if($flag)
				echo "email send sucessfully";
				else
				echo "email send fail";
*/
			}
			else
			{  // seniors email id not found
			
				$mailfailed=1;
				
			}
			
				
		}
		
	//echo "<br>mailfailed".$mailfailed;
	if($mailfailed) // email failed so sending intimation to their seniors
	{
		
		$loop=1;
		$userid_loop = $this->userid;
		$userid_loop_prnt[]=array();
		$loopcounter=1;
		while($loop)
		{
			if($loopcounter>=4) //we will not go more then 3 level  
			{
				break;				
			}
			else
			{
				$loopcounter++;
			}
			$empdrs = null;
			$finalemailid="";
			$finalempName="";
			$empd= "select empParent,empName,emailId from d_jds.mktgEmpMaster where mktEmpCode ='".$userid_loop."' ";
			$empdrs = $this->conn_iro_main->query_sql($empd);
			$empdetarr= array();
			
			if($empdrs && mysql_num_rows($empdrs))
			{
				$empdetarr = mysql_fetch_assoc($empdrs);
				
				if($empdetarr[empParent])
				{
					$empdparent= "select empName,emailId from d_jds.mktgEmpMaster where mktEmpCode ='".$empdetarr[empParent]."' ";
					$empdparentrs = $this->conn_iro_main->query_sql($empdparent);
					$empparentdetarr= array();
					if($empdparentrs && mysql_num_rows($empdparentrs))
					{
						$empdparentarr = mysql_fetch_assoc($empdparentrs);
						
						if(trim($empdparentarr[emailId])=="")
						{
							$userid_loop_prnt[$empdetarr[empParent]]=$empdparentarr[empName]; // emailid missed for this user;
							$userid_loop= $empdetarr[empParent];							
						}
						else
						{
							$finalemailid =$empdparentarr[emailId];
							$finalempName =$empdparentarr[empName];
							$loop=0; // termination loop
						}						
					  }					
					}			
				}
		}
		
		if(count($userid_loop_prnt) && $finalemailid)
		{
				
				$to      = $finalemailid;
				if($to)
				{
				$headers = 'From: justdial@justdial.com' . "\r\n" .
				'Reply-To:'.$empdetarr[emailId] . "\r\n" ;				
				$headers.= "MIME-Version: 1.0\r\n";
				$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				
				$subject ="PLEASE UPADTE EMAIL INFORMATION OF BELOW EMPLOYEE";
				$userid_loop_prnt = array_filter($userid_loop_prnt);
				$userid_loop_prnt = array_unique($userid_loop_prnt);
				
				if(count($userid_loop_prnt))
				{
					foreach($userid_loop_prnt as $empcode=>$empname)
					{
						$userlist.="<br>".$empname."(".$empcode.")";
					}
					
					$message ="DEAR ".$finalempName."<br> Please update the email detail of following employees<br>".$userlist;		
					$flag=mail($to, $subject, $message, $headers);
				}
				
				
				}
		}
	
	}	
	
	}
	}
}


function insertIntoTable()
{
$sql="";
$flg1=0;
$flg2=0;
$flg3=0;
$removedcatidarry= array_diff($this->oldCategoryDetails,$this->newcategoryDetails);// general category entry
if(count($removedcatidarry))	
{
	$i=0;
	foreach($removedcatidarry as $catids)
	{
		if($i)
		{
		$sql.=",";
		}
		$sql.="('".$this->parentid."','".$catids."','".$this->userid."','".$this->username."', now(),99,'".$this->module."')";
		$i++;
		$flg1=1;
	}
}

$removedcatidarry = array_diff($this->oldCatSpon,$this->newCatSpon); // catspon entry 
if(count($removedcatidarry))	
{
	$i=0;
	foreach($removedcatidarry as $catids)
	{
		if($i || $flg1)
		{
		$sql.=",";
		}
		$sql.="('".$this->parentid."','".$catids."','".$this->userid."','".$this->username."', now(),13,'".$this->module."')";
		$i++;
		$flg2=1;
		
	}
	
}


$removedcatidarry = array_diff($this->oldCatText,$this->newCatText); // catspon entry 
if(count($removedcatidarry))	
{
	$i=0;
	foreach($removedcatidarry as $catids)
	{
		if($i|| $flg1 || $flg2)
		{
		$sql.=",";
		}
		$sql.="('".$this->parentid."','".$catids."','".$this->userid."','".$this->username."', now(),15,'".$this->module."')";
		$i++;
	}
}
	
	
if($sql)
{
	$insertqry="INSERT INTO db_iro.tbl_dealclose_category_removed (parentid,catid,deletionby,uname,deletiontime,campaignid,module) VALUES ".$sql;
	
	$this->conn_iro_main->query_sql($insertqry);	
	//$this->sendEmail();
}

//$this->sendEmail();
}

function insertIntoCompBann()
{
	$parentid = $this->parentid;
	$catspon_record_found = 0;
	$compban_record_found = 0;
	$action =0;
	
	$removeCompBanner_Arr = array_diff($this->oldCompBanner,$this->newCompBanner);

	if(count($removeCompBanner_Arr))
	{
		$action = 2;
		$compban_record_found = 1;
	}

	$removeCompBanner_Arr1 = array_diff($this->newCompBanner,$this->oldCompBanner);
	if(count($removeCompBanner_Arr1))
	{
		$action = 1;
		$compban_record_found = 1;
	}
	if($compban_record_found)
	{
		$this->insertCategoryChangedData(5,$action);
	}
	$removedcatidarry12 = array_diff($this->oldCatSpon,$this->newCatSpon);
	if(count($removedcatidarry12))
	{
		$action = 2;
		$catspon_record_found = 1;
	}
	$removedcatidarry_new = array_diff($this->newCatSpon,$this->oldCatSpon);
	if(count($removedcatidarry_new))
	{
		$action = 1;
		$catspon_record_found = 1;
	}
	if($catspon_record_found)
	{
		$this->insertCategoryChangedData(13,$action);
	}
	
	
}
function insertCategoryChangedData($campaignid,$action)
{
	if(intval($campaignid)>0)
	{
		$insertCatInfo =  "INSERT INTO tbl_dealclose_bannercontract_updated 
							SET 
							parentid		= '".$this->parentid."',
							updateby		= '".$this->userid."',
							updatetime		= '".date("Y-m-d H:i:s")."',
							updateaction	= '".$action."',
							campaignid 		= '".$campaignid."',
							done_flag		= '0'
							ON DUPLICATE KEY UPDATE
							updateby		= '".$this->userid."',
							updatetime		= '".date("Y-m-d H:i:s")."',
							updateaction	= '".$action."',
							campaignid 		= '".$campaignid."',
							done_flag		= '0'";
		$respCatInfo = $this->conn_iro_main->query_sql($insertCatInfo);
	}
}

}
?>
