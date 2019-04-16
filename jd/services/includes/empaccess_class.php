<?php
class empaccess_class extends DB
{
	var  $empcode		= null;
	var  $conn_idc   	= null;
	var  $params  		= null;
	
	function __construct()
	{
		global $db;
		$this->conn_idc = $db['remote']['idc']['master'];
	}
	function authorisedUsersCheck($params)
	{
		$empcode  = $params['empcode'];
		$access_flag = 0;
		$sqlUserAuthInfo = "SELECT empcode FROM online_regis1.tbl_budget_update_users WHERE empcode='".$empcode."' AND active_flag = 1 LIMIT 1";
		$resUserAuthInfo   = parent::execQuery($sqlUserAuthInfo, $this->conn_idc);
		if($resUserAuthInfo && parent::numRows($resUserAuthInfo)>0)
		{
			$access_flag = 1;
		}
		$result['access_flag'] = $access_flag;
		$result['errorcode'] = 0;
		return $result;
	}
	function getEmpAccessInfo($params)
	{
		$empcode  = $params['empcode'];
		$responseArr = array();
		$responseArr['limited_flg'] = 0;
		$sqlEmpAccessInfo =	"SELECT pkg_city,pkg_zone,pdg_city,pdg_zone FROM online_regis1.tbl_budget_update_users WHERE empcode = '".$empcode."' AND is_limited_access = '1'";
		$resEmpAccessInfo =	parent::execQuery($sqlEmpAccessInfo, $this->conn_idc);
		if($resEmpAccessInfo && parent::numRows($resEmpAccessInfo)>0)
		{
			$row_employees_info = parent::fetchData($resEmpAccessInfo);
			$pkg_city		= $row_employees_info['pkg_city'];
			$pkg_zone		= $row_employees_info['pkg_zone'];
			$pdg_city		= $row_employees_info['pdg_city'];
			$pdg_zone		= $row_employees_info['pdg_zone'];
			$responseArr['pkg_city'] = $pkg_city;
			$responseArr['pkg_zone'] = $pkg_zone;
			$responseArr['pdg_city'] = $pdg_city;
			$responseArr['pdg_zone'] = $pdg_zone;
			$responseArr['limited_flg'] = 1;
		}
		$result['errorcode'] = 0;
		return $responseArr;
	}
}
?>
