<?
############################################################################################
/*******************************************************************************************
	Description	: Non Paid Contract related functions.
	Date		: 19th Jan, 2007.
	Author		: Nazma Khan.
*******************************************************************************************/
############################################################################################


class nonPaidCon extends dbconn
{
   	/*** 
		Function to get entry counts of DEO.
	 ***/
	function getCount($deo_code)
	{
		$qry	= " select count(*) from unpaidcont where deo_code = '".$deo_code."'";
		
		//$flag = $this->execQry($qry);
		//return $flag;		 
	}
	
   /*** 
		Function to get .
	***/
	function insCat($classIDs)
	{
		$qry	= "insert into where deo_code = '".$deo_code."'";
		
		//$flag = $this->execQry($qry);
		//return $flag;		 
	}
}?>