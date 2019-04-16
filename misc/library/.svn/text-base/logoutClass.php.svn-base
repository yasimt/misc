<?
class logout
{
	function showOption($conn_iro)
	{
		$qry	= "select id,reason from logoutmaster where status=1 order by id";		
		return $conn_iro->query_sql($qry);
	}

	function insertLogout($empid,$reasonid,$conn_iro)
	{
		echo $insertQry	= "insert into tbl_emplogoutinfo(empCode,curtime,reason) values('".$empid."',now(),'".$reasonid."')";	
		return $conn_iro->query_sql($insertQry);
	}

}
?>
