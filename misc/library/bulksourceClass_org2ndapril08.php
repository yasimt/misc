<?php
class bulk_source extends dbconn
{
	function selcompany($city,$cat,$source_comp){
		$arr = array();
		$qry = "select catid from tbl_category_master WHERE catname='".$cat."'";	
		$result = $this->execQry($qry);

		if( mysql_num_rows($result)!=0)	{
			while($catid = mysql_fetch_array($result))	{
				//$company = "select contactID,date(datesource),emp_detail from tbl_company_source where mainsource='".$source_comp."' and contactID in (select contactid from tbl_compcatarea where catid='".$catid[0]."' and freez=0)";
			//	$company = "select contactID,date(datesource),emp_detail from tbl_company_source where mainsource='".$source_comp."' and contactID in (select contactid from tbl_compcatarea where catidlineage like '%/".$catid[0]."/%')";				
				 $company = "select count(s.contactID) from tbl_company_source s, tbl_company_master x where s.mainsource='".$source_comp."' and x.contactID = s.contactID and s.contactID in (select y.contactid from tbl_compcatarea y where y.catidlineage like '%/".$catid[0]."/%')";
	
				$res_comp = $this->execQry($company);
				$cntResult = mysql_fetch_row($res_comp);
			}
		//return $res_comp;
		return $cntResult[0];
		}
		return false;
	}

	function insBulkSource($source,$sub,$comp,$empDet,$srcdate,$adsize,$pageno)	{			
		$qry	= "insert into tbl_company_source (mainsource, subsource, contactID, datesource,emp_detail,adsize,pageno) values ('$source','$sub','$comp','$srcdate','$empDet','$adsize','$pageno');";
		$flag = $this->execQry($qry);
		return $flag;
	}

	function selcompanyNew($city,$cat,$source_comp,$fltr,$page,$pp,$strr,$strr1){
		$arr = array();
		$qry = "select catid from tbl_category_master WHERE catname='".$cat."'";	
		$result = $this->execQry($qry);

		if( mysql_num_rows($result)!=0){
			while($catid = mysql_fetch_array($result))	
			{
				
				//$company = "select contactID,date(datesource),emp_detail from tbl_company_source where mainsource='".$source_comp."' and contactID in (select contactid from tbl_compcatarea where catidlineage like '%/".$catid[0]."/%')";





		  $company = "select s.contactID,date(s.datesource),s.emp_detail, x.companyName,x.paid,x.freez,x.displayType,x.contract_type,x.createdby,x.mask,x.curTime,x.tele_1,x.tele_2,x.tele_3,x.tele_4,x.mobile_1,x.mobile_2  from tbl_company_source s, tbl_company_master x where s.mainsource='".$source_comp."' and x.parentid = s.contactID and s.contactID in (select y.parentid from tbl_compcatarea y where y.catidlineage like '%/".$catid[0]."/%')";

			 if($strr1 == asc ){
				 if($strr == 's.datesource' ){$company .= " Order by datesource  "; }
				 if($strr == 'x.companyName' ){$company .= " Order by companyName  "; }
				 if($strr == 'x.createdby' ){$company .= " Order by createdby  "; }
				 if($strr == 'x.displayType' ){$company .= " Order by displayType  "; }
			}else{
				 if($strr == 's.datesource' ){$company .= " Order by datesource desc  "; }
				 if($strr == 'x.companyName' ){$company .= " Order by companyName desc  "; }
				 if($strr == 'x.createdby' ){$company .= " Order by createdby desc "; }
				 if($strr == 'x.displayType' ){$company .= " Order by displayType desc"; }
				}
				$company .= " limit ".$page.", ".$pp;
				
				$res_comp = $this->execQry($company);
			}	
			return $res_comp;
		}
		return false;
	}
}
?>