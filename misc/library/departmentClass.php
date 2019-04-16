<?
class deptUpdate extends DB
{

	function redirect($path)
	{
		global $_GET;
		ob_clean();
		header("Location:".$path."?page=".$_GET['page']."&fltr=".$_GET['fltr']."&clkflg=1");
	}

	function addData($deptName,$deptPrefix)
	{
		if(!$this->CheckDup($deptName))
		{
			$qry	= "INSERT INTO department(dept_name,dept_prefix) VALUES('".$deptName."','".$deptPrefix."')";
			$flag = $this->execQry1($qry);
			return $flag;
		}
		else
		{
			$msg = "Department Already Exists";
		  return $msg;
		}
	}

	function CheckDup($deptName)
	{
		$qry = "SELECT dept_name FROM department WHERE dept_name = '".$deptName."'";
		$chkres = $this->execQry1($qry);
		$chkflag = mysql_num_rows($chkres);

	  return $chkflag;
	}

	function viewData()
	{
		global $page, $pp, $limit, $fltr;
		
		if(!isset($pp)){ $limit = ""; }
		else{ $limit = " limit ".$page.", ".$pp; }

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;
 
	 	$qry	= "SELECT dept_id,dept_name,dept_prefix FROM department ".$mdfyqry." order by dept_name ".$limit;
		return $this->execQry1($qry);
	}

	function viewData1($sort)
	{
		global $page, $pp, $limit, $fltr;
		
		if(!isset($pp)){ $limit = ""; }
		else{ $limit = " limit ".$page.", ".$pp; }

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		$qry	= "SELECT dept_id,dept_name,dept_prefix FROM department ".$mdfyqry;
			if($sort == 'deptAsc'){
			$qry.= " ORDER BY dept_name";
			}
			if($sort == 'deptDesc'){
			$qry.= " ORDER BY dept_name DESC";
			}
			
			if($sort == 'deptPreAsc'){
			$qry.= " ORDER BY dept_prefix";
			}
			if($sort == 'deptPreDesc'){
			$qry.= " ORDER BY dept_prefix DESC";
			}
			
				$qry .= $limit;
	
		return $this->execQry1($qry);
	}

	function delData($deptId)
	{
		$qry	= "DELETE FROM department where dept_id=$deptId";
		return $this->execQry1($qry);
	}
	function getPrefix($deptPrefix)
	{
		  //$qry="SELECT empCode FROM emplogin e where empCode like '".$deptPrefix."%' order by autoid desc limit 1";
		  $qry="select max(replace(empCode, '".$deptPrefix."', '') + 0) as empCode from emplogin where length(empCode) > 6 group by emptype order by max(replace(empCode, 'IRO', '') + 0) desc limit 1";
		return $this->execQry1($qry);
	}
}	 ?>
