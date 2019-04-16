<?php

/**
 * Filename : catdetailsclass.php
 * Date		: 19/08/2013
 * Author	: pramesh
 
 * */
class budgetinitclass extends DB
{
	var $dbConIro = null;
	var $dbConDjds = null;
	var $dbConTmeJds = null;
	var $dbConFin = null;
	var $Idc = null;
	var $params = null;
	var $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var $arr_errors = array();
	var $is_split = null;
	var $parentid = null;

	var $username = null;
	var $module = null;
	var $data_city = null;
	var $ModuleVersion = null;
	
	
	//minpinbdgt - minimum category pincode budget for that catid and pincode for b2c category only 





	function __construct($params)
	{
		$this->params = $params;


		if (trim($this->params['parentid']) != "") {
			$this->parentid = $this->params['parentid']; //initialize paretnid
		} else {
			{
				echo json_encode('Please provide parentid');
				exit;
			}
		}

		if (trim($this->params['module']) != "" && $this->params['module'] != null) {
			$this->module = strtolower($this->params['module']); //initialize module
		} else {
			$errorarray['errormsg'] = 'module missing';
			echo json_encode($errorarray);
			exit;
		}

		if (trim($this->params['data_city']) != "" && $this->params['data_city'] != null) {
			$this->data_city = $this->params['data_city']; //initialize datacity
		} else {
			$errorarray['errormsg'] = 'data_city missing';
			echo json_encode($errorarray);
			exit;
		}

		if (trim($this->params['usercode']) != "" && $this->params['usercode'] != null) {
			$this->usercode = $this->params['usercode']; //initialize usercode
		} else {
			$errorarray['errormsg'] = 'usercode missing';
			echo json_encode($errorarray);
			exit;
		}

		if (trim($this->params['username']) != "" && $this->params['username'] != null) {
			$this->username = $this->params['username']; //initialize usercode
		}

		if (trim($this->params['passed_version']) != "" && $this->params['passed_version'] != null) {
			$this->passed_version = $this->params['passed_version']; //initialize usercode
		}
		
		//mongo
		$this->mongo_flag = 0;
		$this->mongo_tme = 0;
		$this->mongo_obj = new MongoClass();
		$this->setServers();
		$this->setversion();
		//echo json_encode('const'); exit;
	}
		
	// Function to set DB connection objects
	function setServers()
	{
		global $db;

		$data_city = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');

		$this->dbConIro = $db[$data_city]['iro']['master'];
		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		$this->Idc = $db[$data_city]['idc']['master'];
		$this->fin = $db[$data_city]['fin']['master'];
		$this->tme_jds = $db[$data_city]['tme_jds']['master'];
		$this->dbConbudget = $db[$data_city]['db_budgeting']['master'];


		
		//echo "<pre>"; print_r($this->Idc);

		switch ($this->module) {
			case 'cs':
				$this->tempconn = $this->fin;
				break;

			case 'tme':
				$this->tempconn = $this->tme_jds;
				if ((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))) {
					$this->mongo_tme = 1;
				}

				break;

			case 'me':
				$this->tempconn = $this->Idc;
				if ((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)) {
					$this->mongo_flag = 1;
				}
				break;
		}

	}


	function setversion()
	{
		if ($this->passed_version) {
			$module_version = $this->getModuleVersion();
			$this->version = $this->passed_version;
		} else {
			$version = $this->fetchVersion($this->parentid, 0);

			$version = $this->incrementVersion($thiis->parentid, $version);
			$this->version = $version;
		}
	}

	function getModuleVersion()
	{
		if (strtoupper($this->module) == 'CS') $version = 1;
		else if (strtoupper($this->module) == 'TME') $version = 2;
		else if (strtoupper($this->module) == 'ME') $version = 3;
		$this->ModuleVersion = $version;
		return $version;
	}


	function fetchVersion($stop_die = 0)
	{
		$module_version = $this->getModuleVersion();

		$qry = "SELECT version FROM payment_version WHERE parentid = '" . $this->parentid . "' AND module = '" . $module_version . "' ";

		$res = parent::execQuery($qry, $this->fin);

		if (mysql_num_rows($res)) {
			$row = mysql_fetch_array($res);
			$version = $row['version'];
			if (trim($version) == '') {
				$version = $this->getModuleVersion();
			}
		} else {
			$version = $this->getModuleVersion();
		}

		if ($this->versionSanity($version))
			return $version;
		else {
			if ($stop_die == 0) die("Error! Please contact S/W team- [budgetinitclass - jdbox services]");
		}
	}

	function versionSanity($version)
	{
		switch ($version % 10) {
			case 1:
				$sane = strtolower($this->module) == 'cs' ? true : false;
				break;
			case 2:
				$sane = strtolower($this->module) == 'tme' ? true : false;
				break;
			case 3:
				$sane = strtolower($this->module) == 'me' ? true : false;
				break;
			default:
				$sane = false;
		}
		return $sane;
	}

	function incrementVersion($parentid, $version)       //$version is current version. It has to be incremented by 10
	{
		$module_version = $this->getModuleVersion();
		$incremented_version = $version + 10;

		$checkVersion = "SELECT version FROM payment_apportioning WHERE parentid='" . $this->parentid . "'  AND version='" . $incremented_version . "' LIMIT 1";
		$resCheckVersion = parent::execQuery($checkVersion, $this->fin);
		$numCheckVersion = mysql_num_rows($resCheckVersion);

		if ($numCheckVersion) {
			$sqlFetchMaxVersion = "SELECT MAX(version*1) AS max_version FROM payment_apportioning WHERE parentid='" . $this->parentid . "' AND (version%10)='" . $module_version . "'";
			$resFetchMaxVersion = parent::execQuery($sqlFetchMaxVersion, $this->fin);
			$rowFetchMaxVersion = mysql_fetch_assoc($resFetchMaxVersion);
			$newIncrementedVersion = $rowFetchMaxVersion['max_version'] + 10;
		} else {
			$newIncrementedVersion = $incremented_version;
		}

		$Pparentid = $parentid[0] == 'P' ? $parentid : 'P' . $parentid;

		$qry = "INSERT INTO payment_version SET
				parentid = '$Pparentid',
				version  = '" . $newIncrementedVersion . "',
				module = $module_version
				ON DUPLICATE KEY UPDATE
				version  = '" . $newIncrementedVersion . "'";
		
		//parent::execQuery($qry, $this->tempconn);

		return ($newIncrementedVersion);
	}


	function initBudget()
	{
		$catid_sql = "select catIds from tbl_business_temp_data where contractid='" . $this->parentid . "'";
		$gi_sql = "select pincode,latitude,longitude,landline,mobile from tbl_companymaster_generalinfo_shadow where parentid='" . $this->parentid . "'";


		switch ($this->module) {
			case 'tme':
				if ($this->mongo_tme == 1) {
					$mongo_inputs = array();
					$mongo_inputs['parentid'] = $this->parentid;
					$mongo_inputs['data_city'] = $this->data_city;
					$mongo_inputs['module'] = $this->module;
					$mongo_inputs['table'] = "tbl_business_temp_data";
					$mongo_inputs['fields'] = "catIds";
					$catid_arr = $this->mongo_obj->getData($mongo_inputs);

					$mongo_inputs = array();
					$mongo_inputs['parentid'] = $this->parentid;
					$mongo_inputs['data_city'] = $this->data_city;
					$mongo_inputs['module'] = $this->module;
					$mongo_inputs['table'] = "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] = "pincode,latitude,longitude,landline,mobile";
					$gi_arr = $this->mongo_obj->getData($mongo_inputs);
				} else {
					$catid_res = parent::execQuery($catid_sql, $this->tme_jds);
					$gi_res = parent::execQuery($gi_sql, $this->tme_jds);
					if (mysql_num_rows($catid_res)) {
						$catid_arr = mysql_fetch_assoc($catid_res);
					}
					if (mysql_num_rows($gi_res)) {
						$gi_arr = mysql_fetch_assoc($gi_res);
					}
				}
				break;

			case 'cs':
				$catid_res = parent::execQuery($catid_sql, $this->dbConDjds);
				$gi_res = parent::execQuery($gi_sql, $this->dbConIro);
				if (mysql_num_rows($catid_res)) {
					$catid_arr = mysql_fetch_assoc($catid_res);
				}
				if (mysql_num_rows($gi_res)) {
					$gi_arr = mysql_fetch_assoc($gi_res);
				}
				break;

			case 'me':
				if ($this->mongo_flag == 1) {
					$mongo_inputs = array();
					$mongo_inputs['parentid'] = $this->parentid;
					$mongo_inputs['data_city'] = $this->data_city;
					$mongo_inputs['module'] = $this->module;
					$mongo_inputs['table'] = "tbl_business_temp_data";
					$mongo_inputs['fields'] = "catIds";
					$catid_arr = $this->mongo_obj->getData($mongo_inputs);

					if (isset($this->params['flexiFlag']) && $this->params['flexiFlag'] == 1) {
						$mongo_inputs = array();
						$mongo_inputs['parentid'] = $this->parentid;
						$mongo_inputs['data_city'] = $this->data_city;
						$mongo_inputs['module'] = $this->module;
						$mongo_inputs['table'] = "tbl_companymaster_extradetails_shadow";
						$mongo_inputs['fields'] = "catidlineage_nonpaid";
						$catid_arrNonpaid = $this->mongo_obj->getData($mongo_inputs);
					}

					$mongo_inputs = array();
					$mongo_inputs['parentid'] = $this->parentid;
					$mongo_inputs['data_city'] = $this->data_city;
					$mongo_inputs['module'] = $this->module;
					$mongo_inputs['table'] = "tbl_companymaster_generalinfo_shadow";
					$mongo_inputs['fields'] = "pincode,latitude,longitude,landline,mobile";
					$gi_arr = $this->mongo_obj->getData($mongo_inputs);
				} else {
					$catid_res = parent::execQuery($catid_sql, $this->Idc);
					$gi_res = parent::execQuery($gi_sql, $this->Idc);
					if (mysql_num_rows($catid_res)) {
						$catid_arr = mysql_fetch_assoc($catid_res);
					}
					if (mysql_num_rows($gi_res)) {
						$gi_arr = mysql_fetch_assoc($gi_res);
					}
				}
				break;
		}

		$catid_str = '';
		$catid_array = explode('|P|', $catid_arr['catIds']);
		$catNpExpArr = array();
		if (isset($this->params['flexiFlag']) && $this->params['flexiFlag'] == 1) {
			if ($catid_arrNonpaid['catidlineage_nonpaid'] != "" && $catid_arrNonpaid['catidlineage_nonpaid'] != null) {
				$catid_arrNonpaid = str_replace("/", "", $catid_arrNonpaid['catidlineage_nonpaid']);
				$catNpExpArr = explode(",", $catid_arrNonpaid);
			}
		}
		$catid_array = array_merge($catid_array, $catNpExpArr);
		if (count($catid_array)) {
			$catid_str = implode(',', $catid_array);
			$catid_str = trim($catid_str, ',');
		}

		$id_gen_sql = "select * from db_iro.tbl_id_generator where parentid='" . $this->parentid . "'";
		$id_gen_res = parent::execQuery($id_gen_sql, $this->dbConIro);
		if (mysql_num_rows($id_gen_res)) {
			$id_gen_arr = mysql_fetch_assoc($id_gen_res);
		} else {
			$errorarray['errormsg'] = 'entry missing in tbl_id_generator';
			echo json_encode($errorarray);
			exit;
		}

		$contact_details = $gi_arr['landline'] . "," . $gi_arr['mobile'];
		$contact_details_array = explode(',', $contact_details);
		$contact_details_array = array_filter($contact_details_array);

		$contact_details_str = '';
		if (count($contact_details_array)) {
			$contact_details_str = implode(',', $contact_details_array);
		}

		$pincode = $this->getPincode();
		$pincodejson = $pincode['pincodejson'];

		if ($pincode['pincodelist'] == '' || $pincode['pincodejson'] == '' || $pincode['pincodejson'] == 'null') {
			$pincodejson = ' concat(\'{"a_a_p":"\',pincode_list,\'","n_a_a_p":"\',pincode_list,\'"}\')';
		} else {
			$pincodejson = "'" . $pincodejson . "'";
		}

		if (isset($this->params['onlypackageprice']) && $this->params['onlypackageprice'] != 0) {
			$pincodeval = $gi_arr['pincode'];
		} else {
			$pincodeval = $pincode['pincodelist'];
		}
		//echo 'pincode:='.$pincode;

		if ($this->mongo_flag == 1 || $this->mongo_tme == 1) {
			$mongo_inputs = array();
			$mongo_inputs['parentid'] = $this->parentid;
			$mongo_inputs['data_city'] = $this->data_city;
			$mongo_inputs['module'] = $this->module;
			$mongo_data = array();
			$intermd_tbl = "tbl_temp_intermediate";
			$intermd_upt = array();
			$intermd_upt['version'] = $this->version;
			$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;

			$mongo_inputs['table_data'] = $mongo_data;
			$this->mongo_obj->updateData($mongo_inputs);
		}

		$tbl_temp_intermediate_update = "INSERT INTO tbl_temp_intermediate set										
										parentid='" . $this->parentid . "',
										version ='" . $this->version . "'
										ON DUPLICATE KEY UPDATE
										version ='" . $this->version . "' ";

		switch ($this->module) {
			case 'tme':
			//$tbl_temp_intermediate_update = $tbl_temp_intermediate_update."/* TMEMONGOQRY */";			
			//parent::execQuery($tbl_temp_intermediate_update, $this->tme_jds);			
				break;

			case 'cs':
				parent::execQuery($tbl_temp_intermediate_update, $this->dbConDjds);
				break;

			case 'me':
			//parent::execQuery($tbl_temp_intermediate_update, $this->Idc);			
				break;
		}

		$update_sql = " INSERT INTO tbl_bidding_details_summary set
						sphinx_id		='" . $id_gen_arr['sphinx_id'] . "',
						parentid		='" . $id_gen_arr['parentid'] . "',
						docid			='" . $id_gen_arr['docid'] . "',
						data_city		='" . $id_gen_arr['data_city'] . "',
						pincode			='" . $gi_arr['pincode'] . "',
						latitude		='" . $gi_arr['latitude'] . "',
						longitude		='" . $gi_arr['longitude'] . "',
						version			='" . $this->version . "',
						module			='" . $this->module . "',
						contact_details	='" . $contact_details_str . "',
						category_list	='" . $catid_str . "',
						dealclosed_flag	=0,
						pincode_list	='" . $pincodeval . "',
						pincodejson    =" . $pincodejson . ",
						updatedon			='" . date('Y-m-d H:i:s') . "',
						username			='" . addslashes(stripcslashes($this->username)) . "',
						updatedby			='" . addslashes(stripcslashes($this->usercode)) . "' 
						ON DUPLICATE KEY UPDATE
						sphinx_id		='" . $id_gen_arr['sphinx_id'] . "',						
						docid			='" . $id_gen_arr['docid'] . "',
						data_city		='" . $id_gen_arr['data_city'] . "',
						pincode			='" . $gi_arr['pincode'] . "',
						latitude		='" . $gi_arr['latitude'] . "',
						longitude		='" . $gi_arr['longitude'] . "',
						module			='" . $this->module . "',
						contact_details	='" . $contact_details_str . "',
						category_list	='" . $catid_str . "',
						dealclosed_flag	=0,
						pincode_list	='" . $pincodeval . "',
						pincodejson   	=" . $pincodejson . ",
						updatedon			='" . date('Y-m-d H:i:s') . "',
						username			='" . addslashes(stripcslashes($this->username)) . "',
						updatedby			='" . addslashes(stripcslashes($this->usercode)) . "'
						";

		parent::execQuery($update_sql, $this->dbConbudget);

		if (DEBUG_MODE) {
			echo '<br><b>DB Query:</b>' . $update_sql;
			echo '<br><b>dbConbudget:</b>';
			print_r($this->dbConbudget);
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		$array['data']['version'] = $this->version;
		$array['error_code'] = 0;
		$array['message'] = 'Sucess';
		return $array;
	}


	function getPincode()
	{
		$pincodelist = '';
		$sql = "select pincodelist,pincodejson from tbl_contract_pincodelist where parentid='" . $this->parentid . "' and module=" . $this->ModuleVersion;
		$res_pin = parent::execQuery($sql, $this->dbConbudget);
		$num_rows = mysql_num_rows($res_pin);

		if ($res_pin && $num_rows > 0) {
			$i = 0;
			while ($row = mysql_fetch_assoc($res_pin)) {
				$pincodelist['pincodelist'] = $row['pincodelist'];
				$pincodelist['pincodejson'] = $row['pincodejson'];
			}
		}
		return $pincodelist;
	}

	function getBudget()
	{	// this is skelton of 	

		$catidarr['126099']['cid'] = '126099';
		$catidarr['126099']['ncid'] = '10503428';
		$catidarr['126099']['cnm'] = 'TV Dealers-LG';

		$catidarr['297457']['cid'] = '297457';
		$catidarr['297457']['ncid'] = '10374369';
		$catidarr['297457']['cnm'] = 'Plasma TV Dealers-LG';

		$catidarr['1000404297']['cid'] = '1000404297';
		$catidarr['1000404297']['ncid'] = '10840369';
		$catidarr['1000404297']['cnm'] = 'LED TV Dealers-LG';

		$pincodearr = array('400092' => 'Borivali west', '400062' => 'Malad west', '400062' => 'chembur east', '400058' => 'bhandup west');

		$result = array();
		$positionarr = array(1, 2, 3, 4, 5, 6, 7, 100);

		$budgetvalrr['1'] = 2000;
		$budgetvalrr['2'] = 1700;
		$budgetvalrr['3'] = 1445;
		$budgetvalrr['4'] = 1300;
		$budgetvalrr['5'] = 1170;
		$budgetvalrr['6'] = 1053;
		$budgetvalrr['7'] = 948;
		$budgetvalrr['100'] = 400;

		$totbestbudget = 0;
		$catindex = 0;
		foreach ($catidarr as $catid => $catidarr) {
			$catbudgetarr = array();
			foreach ($pincodearr as $pincode => $pincodename) {
				//$catbudgetarr[$pincode]['pincode']=$pincode;
				$catbudgetarr[$pincode]['pinname'] = $pincodename;

				$positionbudgetarr = array();
				foreach ($positionarr as $key => $positionval) {
					$positionbudgetarr[$positionval]['budget'] = $budgetvalrr[$positionval];
					$positionbudgetarr[$positionval]['inv_booked'] = 0;
					$positionbudgetarr[$positionval]['bookedby'] = '';
					//$positionbudgetarr['bookedby'] = 'PXX22.XX22.120726083221.W3T7-18.59695-0.85000-0.85-0.85000,PXX22.XX22.120916225811.Y2P8-17.83941-0.15000-1-0.15000';
					$positionbudgetarr[$positionval]['inv_available'] = 1;
				}
				$catbudgetarr[$pincode]['pos'] = $positionbudgetarr;
				$catbudgetarr[$pincode]['bestposition'] = rand(1, 7);
				$catbudgetarr[$pincode]['minpinbdgt'] = 100;
				unset($positionbudgetarr);
			}

			$catbudgetarr['catbestbudget'] = '2000';
			$totbestbudget += $catbudgetarr['catbestbudget'];

			$result['catdata'][$catindex]['cid'] = $catid;
			$result['catdata'][$catindex]['ncid'] = $catidarr['ncid'];
			$result['catdata'][$catindex]['cnm'] = $catidarr['cnm'];

			$result['catdata'][$catindex++]['bdetls'] = $catbudgetarr;

			$result['positionarr'] = array(1 => 10, 2 => 15, 3 => 15, 4 => 10, 5 => 15, 6 => 15, 7 => 10, 100 => 10);
		}
		$result['totbestbudget'] = $totbestbudget;
		$resultArr['data'] = $result;
		$resultArr['error_code'] = '0';
		$resultArr['error_msg'] = 'No error';

		return $resultArr;
	}

}



?>
