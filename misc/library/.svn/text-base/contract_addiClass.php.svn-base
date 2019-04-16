<?
class contract_addi extends dbconn
{
	function getAddi()
	{
		$arr = array();
		$qry	= "SELECT a.category_name FROM tbl_categorymaster_generalinfo a join tbl_categorymaster_parentinfo b on a.catid=b.catid WHERE b.parent_catid = 0 limit 15";	
		$result = $this->execQry($qry);
		
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			{
				$arr[] = $row['category_name'];
			}
		}
		return $arr;
	
	}

	function viewContract_addi($id='')
	{
		global $page, $pp, $limit, $fltr;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		if($id)
			$qry = "SELECT Bussiness_type,Feed FROM tbl_additionalinfo where auto=".$id;	
		else
		   $qry	 = "SELECT auto,Bussiness_type,Feed from tbl_additionalinfo ".$mdfyqry." ".$limit;		
		return $this->execQry($qry);
	}

	function insAddi($bussiname,$addi,$id='')
	{
			$crDate	= date("Y-m-d");
			if($id!='')
			{
				$qry= "update tbl_additionalinfo set Bussiness_type = '$bussiname' , Feed = '$addi' WHERE `auto`='$id';";
				$flag = $this->execQry($qry);
			}
			else
			{
				$qry	= "insert into tbl_additionalinfo (Bussiness_type, Feed)values('$bussiname','$addi');";
				$flag	= $this->execQry($qry);
			}
			return $flag;
		
	}

	function getCat($BusiCat)
	{
		
		$arr=array();
		$qry	= "SELECT Bussiness_type FROM tbl_additionalinfo where Bussiness_type='".$BusiCat."'";	
		$result = $this->execQry($qry);
	
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			{
				$arr = $row['Bussiness_type'];
			}
		}
		return $arr;
	}

	function updateAddi($bussi_cat,$arrDel,$id)
	{
		
		$qry= "update tbl_additionalinfo set Bussiness_type = '$bussi_cat' , Feed = '$arrDel' WHERE `auto`='$id'";
		$flag_up = $this->execQry($qry);
		return $flag_up;
	}

	function delId($delete_busi,$id)
	{
		$arr = array();
		$qry	= "select Feed from tbl_additionalinfo where auto='".$id."'";	
	
		$result = $this->execQry($qry);
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			{
				$arr = $row['Feed'];
			}
			
		}
		
		$strlen = strlen($delete_busi);
		$startpos = strpos($arr, $delete_busi);
		$endpos = ($strlen+1);

		$str = substr_replace($arr,"",$startpos,$endpos);
		
		return $str;
	}

	
	

}
?>