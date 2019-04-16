<?
class deptUpdate extends dbconn
{

	function redirect($path)
	{
		ob_clean();
		header("Location:".$path);
	}

	function addData($deptName,$deptPrefix)
	{		

		$qry	= "INSERT INTO department(dept_name,dept_prefix) VALUES('".$deptName."','".$deptPrefix."')";	
		$flag = $this->execQry1($qry);
		return $flag;
	}

	function viewData()
	{
		$qry	= "SELECT dept_id,dept_name,dept_prefix FROM department order by dept_name";		
		return $this->execQry1($qry);
	}
	function delData($deptId)
	{
		$qry	= "DELETE FROM department where dept_id=$deptId ";		
		return $this->execQry1($qry);
	}
	function getPrefix($deptPrefix)
	{
		 $qry="SELECT empCode FROM JDIN.emplogin e where empCode like '".$deptPrefix."%' order by empCode desc limit 1 ";
		return $this->execQry1($qry);
	}

}
?>