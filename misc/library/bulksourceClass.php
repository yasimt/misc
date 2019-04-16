<?php
class bulk_source extends dbconn
{
	function selcompany($city,$cat,$source_comp)
	{
		if($_GET['fltr']=="num") {
  $fltr = " AND companyName REGEXP '^[[:alpha:]]' !=1";
} else if($_GET['fltr']!="num" && $_GET['fltr']) {
  $fltr = " AND companyName REGEXP '^";
  $fltr .= $_GET['fltr']."'";
} else if($_GET['fltr']=="ALL"){
  $fltr = "";
}
$fltr = "";
		$arr = array();
		$qry = "select catid from tbl_categorymaster_generalinfo WHERE category_name='".$cat."'";	
		$result = $this->execQry($qry);
		if( mysql_num_rows($result)!=0)	{
			$catid = mysql_fetch_array($result);
			/*
			$company = "select count(s.parentid) 
						from tbl_company_source s, tbl_company_master x 
						where 
						s.mainsource='".$source_comp."' and 
						x.parentid = s.parentid and 
						s.parentid in 
						(select y.parentid from tbl_compcatarea y where  match(y.catidlineage) AGAINST ('".$catid[0]."'))";
				*/
			$company = "SELECT count(DISTINCT b.parentid) 
						FROM tbl_company_source s
						INNER JOIN tbl_compcatarea b ON s.contactid = b.parentid
						WHERE 
						match(b.catidlineage) AGAINST ('".$catid[0]."') AND
						s.mainsource='".$source_comp."' ";
				 if($_GET['fltr'])
				{
				 $company.=$fltr;
				 }
				 if($_SERVER['REMOTE_ADDR'] == '172.29.5.38')
				 {
				 	echo "<hr>".$company;
				}
				$res_comp = $this->execQry($company);
				$cntResult = mysql_fetch_row($res_comp);
			
		//return $res_comp;
		return $cntResult[0];
		}
		return false;
	}
	/*function insBulkSource($source,$sub,$comp,$empDet,$srcdate,$adsize,$pageno)	{			
		$qry	= "insert into tbl_company_source (mainsource, subsource, contactID, datesource,emp_detail,adsize,pageno) values ('$source','$sub','$comp','$srcdate','$empDet','$adsize','$pageno');";
		
		$flag = $this->execQry($qry);
		return $flag;
	}*/
	function selcompanyNew($city,$cat,$source_comp,$fltr,$page,$pp,$strr,$strr1){
		$arr = array();
		$qry = "select catid from tbl_categorymaster_generalinfo WHERE category_name='".$cat."'";	
		$result = $this->execQry($qry);
			if(mysql_num_rows($result)!=0){
		$catid = mysql_fetch_array($result);
	
				$company = "select distinct  x.contactID,max(date(s.datesource)),s.emp_detail, x.companyName,x.paid,x.freez,x.displayType,x.contract_type,x.createdby,x.mask,x.curTime,x.tele_1,x.tele_2,x.tele_3,x.tele_4,x.mobile_1,x.mobile_2,x.parentid, x.area from tbl_company_source s INNER JOIN tbl_company_master x ON x.parentid = s.contactID 
INNER JOIN tbl_compcatarea AS y
ON s.contactID = y.parentid
where s.mainsource='".$source_comp."' and match(y.catidlineage) AGAINST ('".$catid[0]."') ".$fltr." group by x.contactID";
			 if($strr1 == asc ){
				 if($strr == 's.datesource' ){$company .= " Order by datesource  "; }
				 if($strr == 'x.companyName' ){$company .= " Order by companyName  "; }
				 if($strr == 'x.createdby' ){$company .= " Order by createdby  "; }
				 if($strr == 'x.displayType' ){$company .= " Order by displayType  "; }
			}else if($strr1 == desc){
				 if($strr == 's.datesource' ){$company .= " Order by datesource desc  "; }
				 if($strr == 'x.companyName' ){$company .= " Order by companyName desc  "; }
				 if($strr == 'x.createdby' ){$company .= " Order by createdby desc "; }
				 if($strr == 'x.displayType' ){$company .= " Order by displayType desc"; }
				}
				else {
					$company .= " Order by companyName";
				}
			 	$company .= " limit ".$page.", ".$pp;
				if($_SERVER['REMOTE_ADDR'] == '172.29.5.38')
				 {
				 	echo "<hr>".$company;
				}
				$res_comp = $this->execQry($company);
			
			return $res_comp;
			
		}
		return false;
	}
}
?>
