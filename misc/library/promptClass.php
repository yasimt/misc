<?
class prompt extends dbconn
{
	function addScript($scriptnm,$company)
	{
		$qry ="INSERT INTO script (script,compid) VALUES('".strtoupper($scriptnm)."','".$company."')";
		
		$msg = "Script inserted successfully";
		$flg=$this->execQry($qry);
		return $msg;
	}
	/*function viewScript($scriptid='')
	{
		if($scriptid)
			$qry	= "SELECT compid,script FROM script where scriptid ='".$scriptid."' ";	
		else
			$qry	= "SELECT scriptid,compid,script FROM script order by `compid`";	
			
		return $this->execQry($qry);
	}
	
	
	
	function chkScript($scriptnm,$company)
	{
		$qry	= "SELECT count(*) FROM script WHERE  script='".strtoupper($scriptnm)."' and compid='".$company."';	
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}
	

	function addScript($scriptnm,$company)
	{
		$flg = $this->chkScript($scriptnm,$company);
		
		if($flg ==0)
		{	
			$qryZone= "SELECT compid,script FROM script WHERE name='".strtoupper($scriptnm)."'";
			$result	= $this->execQry($qryZone);
			$getZone= mysql_fetch_row($result);
		
			if($getZone[0]=="")
			{
				$qryMaxCode= "SELECT max(code) FROM script";
				$rscode    = $this->execQry($qryMaxCode);
				$maxCode   = mysql_fetch_row($rscode);
				$scriptcode  = $maxCode[0]+1;
			}
			else
				$scriptcode = $getZone[1];
			
			if($getZone[0]==$sp1 || $getZone[0]=="")
			{
				$qry ="INSERT INTO script (script) VALUES('".strtoupper($scriptnm)."')";
				
				$msg = "Script inserted successfully";
				
				$flg=$this->execQry($qry);
			}
			else
				$msg="Existing script map with different zone";
		}		
		else
			$msg="Script already exist";	
		return $msg;

	}*/
}

?>