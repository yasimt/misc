<?
/*****

Created By: Nazma Khan
Creation Date: 2nd Jan, 2008
Purpose: Class For Creating or Updating New Dial Code for CTI System
NOTE: Update this file in library folder of both jds and dataentry module

*****/

class dialCodeClass extends dbconn
{
	function addDialCode($dialcode,$usercode)
	{
		$qry	= "INSERT INTO tbl_dialCode(dialCode,createdBy,createdDt) VALUES ('$dialcode','$usercode',now());";
		$flag	= $this->execQry2($qry);				
		return $flag;
 	}

	function editDialCode($dialcode,$usercode)
	{
		$qry	= "Update tbl_dialCode set dialCode='$dialcode',updatedBy='$usercode',updatedDt=now();";
		$flag	= $this->execQry2($qry);				
		return $flag;
 	}

	function editFlagon($flagval,$usercode)
	{
		$qry	= "Update tbl_dialCode set flagon='$flagval',updatedBy='$usercode',updatedDt=now();";
		$flag	= $this->execQry2($qry);				
		return $flag;
 	}

	function viewData()
	{
		$qry	= "SELECT dialCode, flagon FROM tbl_dialCode;";				
		return $this->execQry2($qry);
	}

	function checkCnt()
	{
		$qry	= "SELECT count(*) FROM tbl_dialCode;";	
		$res    = $this->execQry2($qry);
		$recd   = mysql_fetch_row($res);
		$cnt	= $recd[0];
		$res	= "";
		$recd	= "";
		return $cnt;
	}

	function getDialCode($falseCode='') {
		if($falseCode == '')
			return '';
		
		for($iNum=0; $iNum < 10; $iNum++) {
			if(md5($iNum) == $falseCode) {
				return $iNum;
			}
		}
	}
}
?>