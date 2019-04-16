<?
class impUpdate extends dbconn
{

	function redirect($path)
	{
		ob_clean();
		header("Location:".$path);
	}

	function addData($code,$name,$date,$msg,$impupdatefile,$docroot)
	{
		//$msg  =  nl2br(htmlspecialchars($msg));		
		$out = ("chmod -R 777".$impupdatefile);
		$filepath = $docroot.$impupdatefile;
		file_put_contents($filepath,$msg);
                  $impupdatefile = "/spec/files/impUpdate.htm";

 		$qry	= "INSERT INTO tbl_impUpdate(imp_id,imp_empid,imp_empname,imp_curdt,imp_showdt,imp_filenm) 	VALUES(1,'".$code."','".$name."',now(),'".$date."','".$impupdatefile."') ON DUPLICATE KEY UPDATE imp_empid='".$code."',imp_empname='".$name."',imp_curdt=now(),imp_showdt='".$date."'";
		$flag = $this->execQry_db_iro($qry);
				
		return $flag;
 	}

	function viewData()
	{
		$qry	= "SELECT  imp_id,imp_empid,imp_empname,imp_curdt,date(imp_showdt) as dt,imp_filenm FROM tbl_impUpdate order by imp_showdt desc";
				
		return $this->execQry_db_iro($qry);
	}

}  ?>
