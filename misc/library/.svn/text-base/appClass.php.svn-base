<?
class iro_appoinmnet extends dbconn
{
	function insApp($parti,$ext,$cat_from,$cat_end,$id=''){
			if($id!="")	{
				$qry = "update tbl_apppart set particular = '$parti', extension = '$ext', category_from = '$cat_from', category_end = '$cat_end' WHERE appid = '$id' ";
				$flag = $this->execQry($qry);
			} else {
				$qry = "insert into tbl_apppart (particular, extension, category_from, category_end) values ('$parti', '$ext', '$cat_from', '$cat_end')";
				$flag = $this->execQry($qry);
			}
			return $flag;
	}
	
	function getApp($extension){
		$arr = array();
		$qry	= "SELECT * FROM tbl_apppart where extension=".$extension."";	
		$result = $this->execQry($qry);
		
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			{
				$arr = $row['extension'];
			}
		}
		return $arr;
	
	}

	function viewApp($appid='')
	{
		global $page, $pp, $limit, $fltr;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;
		
		if($appid)
			$qry = "SELECT particular,extension,category_from,category_end FROM tbl_apppart where appid=".$appid;	
		else
			$qry = "SELECT appid,particular,extension,category_from,category_end from tbl_apppart ".$mdfyqry." ".$limit;		
		return $this->execQry($qry);
	}

	function getCatStNEnd($category_from,$category_end) {
		$qry	= "SELECT count(*) FROM tbl_apppart where category_from='".$category_from."' and category_end='".$category_end."'";	
		$result = $this->execQry($qry);
		$row = mysql_fetch_row($result);
		return $row[0];
	}
} ?>