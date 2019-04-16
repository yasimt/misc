<?php
// changes needed
require_once(APP_PATH."library/designerbanner.php");
class comp_banner{

	private $conn_main,$conn_temp,$conn_mid,$category_master;
	private $parentid, $data_city,$module,$conn_manual_set;
	private $versionval;

	function comp_banner($pid,$data_city='',$module='',$conn_arr=array(),$state,$dbarr,$version=0,$autoappr=0){
		$this->compbanner_log("Parameters passed  {".$pid.",".$data_city.",".$module.",".$state.",".$version."}",$pid,$dbarr,$module);
		
		//~ die("class.comp_banner");
		if(trim($pid)!=''){
			$this->parentid = trim($pid);
			$this->data_city= trim($data_city);
			$this->module	= strtolower(trim($module));
			$this->autoarr	= $autoarr;
			if($this->module == ''){

				if($version == 0 || trim($version)==''){
					if($autoappr == 0)
						die("No module or deal close version present for comp_banner");
					else
						$this->compbanner_log("Object creation failed -- No module or deal close version present for comp_banner");
				}else{
					if($version%10 == 1){
						$this->module = 'cs';
					}elseif($version%10 == 2){
						$this->module = 'tme';
					}elseif($version%10 == 3){
						$this->module = 'me';
					}
				}
			}
			
			$this->state	= $state;
			if(count($conn_arr)){
				$this->conn_main	= (isset($conn_arr['main']))?$conn_arr['main']:'';
				$this->conn_temp	= (isset($conn_arr['temp']))?$conn_arr['temp']:'';
				$this->conn_mid		= (isset($conn_arr['intermediate']))?$conn_arr['intermediate']:'';
				$this->conn_iro		= (isset($conn_arr['iro']))?$conn_arr['iro']:'';
				$this->conn_local	= (isset($conn_arr['local']))?$conn_arr['local']:'';
				$this->conn_appr	= $this->conn_main;
			}else{
				$this->setConnection($dbarr);
			}
			
			$this->conn_IDC	= new DB($dbarr['IDC']);
			$this->conn_fnc = new DB($dbarr['FINANCE']);
			$this->versionval = $version;
			
			if($this->data_city == ''){
				$this->data_city = $this->setDataCity();
			}
		}else{
			if($autoappr == 0)
				die("No parentid for comp_banner");
			else
				$this->compbanner_log("Object creation failed -- No parentid");
		}

		$this->categoryMaster	= new categoryMaster($dbarr,APP_MODULE,$autoappr);
		$this->compmaster_obj	= new companyMasterClass($this->conn_iro,$this->data_city,$this->parentid);
	}

	function setDataCity(){

		$data_city		= "";
		$sqlGetDataCity = "SELECT data_city FROM tbl_id_generator WHERE parentid ='".$this->parentid."'";
		$qryGetDataCity = $this->conn_iro->query_sql($sqlGetDataCity);

		if($qryGetDataCity && mysql_num_rows($qryGetDataCity)){
			$rowGetDataCity	= mysql_fetch_assoc($qryGetDataCity);
			$data_city = $rowGetDataCity['data_city'];
		}

		if(trim($data_city) == ''){
			if(trim($this->auto_appr) == '')
				die('Data City Problem in Competitor\'s Banner');
			else{
				$to = 'subroto.mahindar@justdial.com,yogitatandel@justdial.com';
				$subject = "Data City problem -- ".($this->cron == '1')?'CRON':'AUTO APPROVAL';
				$message = "Data City not found in parentid = ".$this->parentid;
				mail($to,$subject,$message);
			}
		}
		return $data_city;
	}

	function setConnection($dbarr){

		if(strtolower($this->module) == 'cs'){
			$this->conn_temp 	= new DB($dbarr['LOCAL']);
			$this->conn_main 	= $this->conn_temp;
			$this->conn_mid		= $this->conn_temp;
			$this->conn_local	= $this->conn_temp;
			$this->conn_iro		= new DB($dbarr['DB_IRO']);
			$this->conn_appr	= $this->conn_main;
		}elseif(strtolower($this->module) == 'tme'){
			$this->conn_temp 	= new DB($dbarr['DB_TME']);
			$this->conn_main 	= new DB($dbarr['IDC']);
			$this->conn_mid		= $this->conn_main;
			$this->conn_local	= new DB($dbarr['LOCAL']);
			$this->conn_iro		= new DB($dbarr['DB_IRO']);
			$this->conn_appr	= $this->conn_local;
		}elseif(strtolower($this->module) == 'me' || strtolower($this->module) == 'jda'){
			$this->conn_temp 	= new DB($dbarr['IDC']);
			$this->conn_main 	= new DB($dbarr['IDC']);
			$this->conn_mid		= $this->conn_temp;
			$this->conn_local	= $this->conn_temp;
			$this->conn_iro		= new DB($dbarr['DB_IRO']);
			$this->conn_appr	= new Db($dbarr['LOCAL']);
		}
		$this->conn_manual_set = 1;
	}

	function inserttotable($insertarr){
		/*
		 * state = 1 -- dealclose and contract flow
		 * state = 2 -- approval
		 * state = 3 -- balance readjustment
		*/
		$str_ins	= "";
		if(count($insertarr)){
			$this->compbanner_log("Insert update array -- ".json_encode($insertarr));
			$tablename_arr	= array_keys($insertarr);
			$tablename		= implode(" ",$tablename_arr);

			$conn = $this->returnConn($tablename);

			foreach($insertarr[$tablename] as $tablearr){
				$ins_arr= array();
				$uparr	= array();
				$keystr	= '';
				$valstr = '';

				foreach($tablearr as $key => $value){
					if($key!=$old_key){
						$valstr = "(".trim($valstr,",")."),";
					}
					$old_key 	= $key;
					$ins_arr[] 	= $key." = '".$value."'";
					if($key == 'parentid' || $key == 'catid' || $key == 'campaign_type'){
						continue;
					}else{
						$uparr[] = $key." = '".$value."'";
					}
				}
				$insstr	= implode(",",$ins_arr);
				$upstr	= implode(",",$uparr);
				if(trim($insstr)!='' && trim($upstr)!=''){

					$sql_insert = "INSERT INTO ".$tablename." SET parentid='".$this->parentid."', ".$insstr." ON DUPLICATE KEY UPDATE ".$upstr;
					$qry_insert	= $conn->query_sql($sql_insert);
					$this->compbanner_log("Insert query => ".$sql_insert. " Result == ".$qry_insert);
				}
			}
		}else{
			$this->compbanner_log("No insert array found");
		}
		return $qry_insert;
	}

	function removeCategory($uparr,$financeObj=null){
		$updateStr	= '';
		$whereCond	= '';
		if(count($uparr)){
			foreach($uparr as $tablename => $deleteCond){
				$conn		= $this->returnConn($tablename);
				if(trim($deleteCond)!='')
					$delCondStr	= $deleteCond;
			}

			if(trim($delCondStr)!=''){
				$sql_delete = "DELETE FROM ".$tablename." WHERE parentid = '".$this->parentid."' AND ".$delCondStr;
			}else{
				$sql_delete = "DELETE FROM ".$tablename." WHERE parentid = '".$this->parentid."'";
			}
			$qry_delete	= $conn->query_sql($sql_delete);

			$reqarr['tbl_comp_banner_temp'] = "catid";
			$catidarr	= $this->showSelected($reqarr);

			if(count($catidarr['tbl_comp_banner_temp']) == 0 && $financeObj!= null){
				$finIns['budget']	 		=  0;
				$finIns['duration']	 		=  0;
				$finIns['recalculate_flag'] =  0;

				$financeObj->financeInsertUpdateTemp('5',$finIns);
			}
			$this->compbanner_log("Delete query => ".$sql_delete. " Result == ".$qry_delete);

		}else{
			$this->compbanner_log("Delete not done");
		}
	}

	function showSelected($selected_arr, $whereCond_arr=array(),$approval_flag=0){
		$return_arr	= array();
		if(count($selected_arr)){
			foreach($selected_arr as $tablename => $fetchdata){
				$conn 		= $this->returnConn($tablename,$approval_flag);
				$fetchstr	= $fetchdata;
				if(count($whereCond_arr[$tablename])>0){
					$whereCondStr	= $whereCond_arr[$tablename];
				}else{
					$whereCondStr	= "parentid = '".$this->parentid."'";
				}
				if(trim($fetchstr)!='')
					$sql	= "SELECT ".$fetchstr." FROM ".$tablename." WHERE ".$whereCondStr;
					$qry	= $conn->query_sql($sql);

					if(mysql_num_rows($qry) > 0){
						while($row = mysql_fetch_assoc($qry)){
							$return_arr[$tablename][]	= $row;
						}
					}
			}
		}


		return $return_arr;
	}

	function GetCategories($word,$request){

		$word_arr = explode(" ",$word);
		$word_num=count($word_arr);
		unset($categories);

		$categories = $this->categoryMaster ->getCatSponFreeCat($word,'0',$request,$word_num);

		return $categories;
	}

	function getCatCompany($catid){
		$company_present = false;

		$joinfiedsname 	= "x1.sphinx_id, x1.parentid, y1.catidlineage, z1.data_city, y1.mask, y1.freeze";
		$jointablesname	= "tbl_companymaster_search x1, tbl_companymaster_extradetails y1, tbl_id_generator z1";
		$joincondon		= "";
		$wherecond		= "MATCH(x1.catidlineage_search) AGAINST('".$catid."' IN BOOLEAN MODE) AND x1.parentid = y1.parentid AND y1.parentid = z1.parentid AND x1.data_city='".$this->data_city."' and  y1.mask=0 and  y1.freeze =0";

		$temparr		= $this->compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond);
		$rowcount 		= $temparr['numrows'];

		$res_get_catcompany=$temparr['data']['0'];

		if($temparr['numrows']>0)
		{
			$company_present = true;
		}
		return $company_present;
	}

	function returnConn($tab_name,$approval_flag=0){

		$conn		= '';
		$tablearr	= array("tbl_comp_banner_temp","tbl_comp_banner_shadow","tbl_comp_banner");

		if(in_array($tab_name,$tablearr)){
			if($tab_name == 'tbl_comp_banner_temp'){
				$conn= $this->conn_temp;
			}else if($tab_name == 'tbl_comp_banner_shadow'  || ($tab_name == 'tbl_comp_banner' && $this->state == 1 && ($this->module == 'tme' || $this->module == 'me' || $this->module == 'jda'))){
				$conn= $this->conn_mid;
			}else if(($tab_name == 'tbl_comp_banner' && $this->state == 2)){
				$conn= $this->conn_appr;
			}else if($tab_name == 'tbl_comp_banner' && $this->state == 1 && $this->module=='cs'){
				$conn= $this->conn_main;
			}else if($tab_name == 'tbl_comp_banner_temp' && $this->state == 3){
				$conn= $this->conn_temp;
			}else if($tab_name == 'tbl_comp_banner' && $this->state == 3){
				$conn= $this->conn_appr;
			}else{
				if($this->autoarr == 0)
					die("Connection selection failed");
				else{
					$this->compbanner_log("Connection selection failed 	");
				}
			}
			if($approval_flag == 1){
				$conn = $this->conn_mid;
			}
			if($approval_flag == 3 && $tab_name == 'tbl_comp_banner'){
				$conn = $this->conn_local;
			}
		}else{
			$this->compbanner_log("No proper table found");
		}
		return $conn;
	}

	function getMinBudget(){
		$min = 0;
		$sql = "SELECT banner_budget FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
		$qry = $this->conn_local->query_sql($sql);
		if($qry && mysql_num_rows($qry)){
			$row	= mysql_fetch_assoc($qry);
			$min 	= $row['banner_budget'];
		}
		$this->compbanner_log("Minimum Budget	= ".$min);
		return $min;
	}

	function categorycheck($retVal=''){

		$delarr= array();
		$count = 0;
		if($retVal == 'catid')
			$field['tbl_comp_banner_temp'] = 'catid';
		else
			$field['tbl_comp_banner_temp'] = 'catid,cat_name';

		$get_arr	= $this->showSelected($field);
		foreach($get_arr['tbl_comp_banner_temp'] as $catarr){
			if($retVal == 'catid')
				$categoryarr[] = $catarr['catid'];
			else
				$categoryarr[$catarr['catid']] = $catarr['cat_name'];
		}

		if(count($categoryarr)){
			if($retVal == 'catid')
				$catidstr	= implode(",",$categoryarr);
			else
				$catidstr	= implode(",",array_keys($categoryarr));

			$sql	= "SELECT catid,paid_clients,nonpaid_clients,category_name FROM tbl_categorymaster_generalinfo WHERE catid in (".$catidstr.")";
			$qry	= $this->conn_local->query_sql($sql);

			if($qry && mysql_num_rows($qry)){
				while($row	= mysql_fetch_assoc($qry)){
					if($row['paid_clients'] == '0' && $row['nonpaid_clients'] == '0'){
						if($retVal == 'catid')
							$delarr[] = $row;
						else
							$delarr[$row['catid']] = $categoryarr[$row['catid']];
						++$count;
					}
				}
			}
		}
		$delarr['count'] = $count;

		return $delarr;
	}

	function budgetCalculation($financeobj,$request){/* Function for campaign submission*/

		if($request['total'] > 0 and $request['tenure'] >= 30){
			$ins_fin_arr	= array('budget' => $request['total'], 'duration'=>$request['tenure']);
			$financeMain	= $financeobj->getFinanceMainData('5');


			if($financeMain['5']['budget']!=$request['total'] || $request['calculate_attribute'] == 1){
				$ins_fin_arr['recalculate_flag'] = "1";
			}

			$selStr['tbl_comp_banner_temp'] = 'catid';
			$temparr	= $this->showSelected($selStr);
			$i=0;
			foreach($temparr['tbl_comp_banner_temp'] as $valarr){
				$inse_banner['tbl_comp_banner_temp'][$i]['tenure'] 			= $request['duration'];
				$inse_banner['tbl_comp_banner_temp'][$i]['campaign_type']  = 4;
				$inse_banner['tbl_comp_banner_temp'][$i++]['catid']  		= $valarr['catid'];
			}
			$this->inserttotable($inse_banner);


			$financeobj->financeInsertUpdateTemp('5',$ins_fin_arr);
		}
	}

	function temptoMain(){

		$temp_table 	= '';
		$main_table 	= '';
		$approval_tmeme	= 0;
		$result			= 0;
		switch($this->state){
			case '1':
					$temp_table = 'tbl_comp_banner_temp';
					if(strtolower($this->module) == 'cs')
						$main_table	= 'tbl_comp_banner_shadow';
					else
						$main_table	= 'tbl_comp_banner';
					break;
			case '2':
					if(strtolower($this->module) == 'cs')
						$temp_table = 'tbl_comp_banner_shadow';
					else
						$temp_table = 'tbl_comp_banner';
					$main_table	= 'tbl_comp_banner';
					break;
			case '3':
					$temp_table = 'tbl_comp_banner_temp';
					$main_table	= 'tbl_comp_banner';
					break;
			default:
					die("Wrong state");
					break;
		}
		
		if($temp_table == 'tbl_comp_banner' && ($this->module == 'me' || $this->module == 'tme' || $this->module == 'jda') && $this->state == 2){
			$approval_tmeme = 1;
		}
		
		$fetch_temp_arr[$temp_table] = 'MAX(banner_camp) AS banner_camp';
		$temparr	= $this->showSelected($fetch_temp_arr,array(),$approval_tmeme);
		$escape		= 0;
		if(count($temparr[$temp_table]) > 0){
			if($temparr[$temp_table][0]['banner_camp'] == '1' || $temparr[$temp_table][0]['banner_camp'] == '2'){
				$escape	= 1;
			}
		}
		
		if($escape == 0){
			
			$fetch_temp_arr[$temp_table] = 'cat_name,catid,national_catid,tenure,campaign_name,campaign_type,iscalculated,selectedCities';
			$temparr	= $this->showSelected($fetch_temp_arr,array(),$approval_tmeme);
			$insertarr	= array();
			$i			= 0;

			$insertarr[$main_table] = '';
			$this->removeCategory($insertarr);

			if(count($temparr[$temp_table]) > 0){
				foreach($temparr[$temp_table] as $key => $temparr){
					$key_arr	= array_keys($temparr);
					foreach($key_arr as $key_val){
						$insertarr[$main_table][$i][$key_val] = $temparr[$key_val];
					}
					$insertarr[$main_table][$i]['update_date'] = date("Y-m-d H:i:s");
					$i++;
				}

				$result = $this->inserttotable($insertarr);

			}

			if($this->state == 2 && strtolower($this->module) == 'cs'){
				$delarr['tbl_comp_banner_shadow'] = '';
				//$this->removeCategory($delarr);
			}
			
			if($this->state==2)// here we call designer banner function 
			{ 
				if($this->versionval%10 == 1)// cs
				{
					$companyname = $this ->getCompanyName($this ->parentid);
				}else
				{
					$companyname = $this ->getCompanyName_IDC($this ->parentid);
				}
				
				if($this->state==2)// here we call designer banner function 
				{ 
					if($this->versionval%10 == 1)// cs
					{
						$companyname = $this ->getCompanyName($this ->parentid);
					}else
					{
						$companyname = $this ->getCompanyName_IDC($this ->parentid);
					}
					
					$DesignerBanner = new DesignerBanner();
					$DesignerBanner->insertOnApproval($this->conn_fnc,$this ->parentid,$this->versionval,5,$companyname,$this->data_city);
					
				}
			}
		}
		return $result;
	}
	
	function getCompanyName_IDC($parentid)
	{
		$compnamesql		= "select companyname from tbl_companymaster_generalinfo where parentid='".$parentid."'";				
		$compnamers		= $this->conn_IDC->query_sql($compnamesql);
		
		if($compnamers && mysql_num_rows($compnamers))
		{
			$compnamearr= mysql_fetch_assoc($compnamers);
			return $compnamearr['companyname'];
		}
	}
			
	function getCompanyName($parentid){
			if(is_array($parentid)){
				$compname = array();
				$paridstr = implode("','",$parentid);

				$temparr		= array();
				$fieldstr		= '';
				$fieldstr 		= "parentid,companyname";
				$tablename		= "tbl_companymaster_generalinfo";
				$wherecond		= "parentid IN ('".$paridstr."')";
				$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
			}else{
				$compname = '';

				$temparr		= array();
				$fieldstr		= '';
				$fieldstr 		= "companyname";
				$tablename		= "tbl_companymaster_generalinfo";
				$wherecond		= "parentid='".$parentid."'";
				$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	}
	
	if($temparr['numrows'])
			{
				foreach($temparr['data'] as $row_comp){
					if(!is_array($parentid)){
						$compname = $row_comp['companyname'];
					}else{
						$compname[$row_comp['parentid']] = $row_comp['companyname'];
					}
				}
			}
			
			return $compname;
}
	
	function mainToTemp(){
		$select_arr['tbl_comp_banner_shadow'] = "cat_name,catid,national_catid,campaign_name,campaign_type,iscalculated,tenure,banner_camp,parentname";
		if(strtolower($this->module) == 'cs')
			$shadow_arr	= $this->showSelected($select_arr);
		if($shadow_arr['tbl_comp_banner_shadow'] == 0 || !isset($shadow_arr['tbl_comp_banner_shadow'])){
			$select_arr = array();
			$select_arr['tbl_comp_banner'] = "cat_name,catid,national_catid,campaign_name,campaign_type,iscalculated,tenure,banner_camp,parentname";
			if($this->module == 'tme' || $this->module == 'me' || $this->module == 'jda'){
				$main_arr	= $this->showSelected($select_arr,array(),3);
			}else{
				$main_arr	= $this->showSelected($select_arr);
			}
			$data_arr	= $main_arr['tbl_comp_banner'];
		}else{
			$data_arr	= $shadow_arr['tbl_comp_banner_shadow'];
		}

		$delarr['tbl_comp_banner_temp'] = '';
		$this->removeCategory($delarr);
		if(count($data_arr)>0){
			$insarr	= array();
			$i = 0;
			foreach($data_arr as $temparr){
				$insarr['tbl_comp_banner_temp'][$i]['catid'] 			= $temparr['catid'];
				$insarr['tbl_comp_banner_temp'][$i]['cat_name']	 		= $temparr['cat_name'];
				$insarr['tbl_comp_banner_temp'][$i]['national_catid'] 	= $temparr['national_catid'];
				$insarr['tbl_comp_banner_temp'][$i]['campaign_name'] 	= $temparr['campaign_name'];
				$insarr['tbl_comp_banner_temp'][$i]['campaign_type'] 	= $temparr['campaign_type'];
				$insarr['tbl_comp_banner_temp'][$i]['iscalculated'] 	= $temparr['iscalculated'];
				$insarr['tbl_comp_banner_temp'][$i]['tenure'] 			= $temparr['tenure'];
				$insarr['tbl_comp_banner_temp'][$i]['banner_camp'] 		= $temparr['banner_camp'];
				$insarr['tbl_comp_banner_temp'][$i]['update_date'] 		= date("Y-m-d H:i:s");
				$insarr['tbl_comp_banner_temp'][$i]['parentname'] 		= $temparr['parentname'];

				$this->inserttotable($insarr);
				if(!isset($_SESSSION['doctor_package'])){
					if($temparr['banner_camp'] == 2){
						$_SESSION['doctor_package'] = 1;
					}else if($temparr['banner_camp'] == 1 || $temparr['banner_camp'] == 0){
						$_SESSION['doctor_package'] = 2;
					}
				}
			}
		}
		if(strtolower($this->module) != 'cs'){
			return $_SESSION['doctor_package'];
		}

	}

	function compbanner_log($msg,$pid,$dbarr,$module){		
		if($pid!=''){
			$this->parentid = trim($pid);
			$this->module   = trim($module);
			$conn_idc  = new DB($dbarr['IDC']);
		}else{
			$conn_idc = $this->conn_IDC;
		}
		
		/* 
		$sNamePrefix	= APP_PATH . 'logs/compbanner/';
        $log_msg='';
        $pathToLog = dirname($sNamePrefix);
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        if(!file_exists($sNamePrefix))
		{
			mkdir($sNamePrefix, 0777, true);
		}

        $file_n=$sNamePrefix.$this->parentid.".txt";

        $logFile = fopen($file_n, 'a+');

		$txt = "";
		if($this->state == 1)
			$txt .= "Deal Close/Flow";
		else if($this->state == 2){
			$txt .= "Approval";
			if($this->autoarr == 1)
				$txt.= " - Auto Approval";

		}else if($this->state == 3)
			$txt.= "Balance Readjustment";
        $log_msg.= "[Date - ".date('Y-m-d H:i:s')."][Module - ".$this->module."][Process - ".$txt."] - ".$msg."\n\n";
		
		//parentid, updatedOn,uname,ucode,module,message,QUERY,php_script $_SERVER["SCRIPT_FILENAME"]; $_SERVER['PHP_SELF']

		$file = __FILE__;
		$qry_log = "insert into online_regis.tbl_compcat_log (parentid, updatedOn,uname,ucode,module,message,query,php_script,parent_php_script) values ('".$this->parentid."','".date('Y-m-d H:i:s')."','".$_SESSION['uname']."','".$_SESSION['ucode']."','".$this->module."','".mysql_real_escape_string(stripslashes($txt))."','".mysql_real_escape_string(stripslashes($msg))."','".mysql_real_escape_string(stripslashes($file))."','".mysql_real_escape_string(stripslashes($_SERVER['SCRIPT_FILENAME']))."')";
		$res_log = $conn_idc->query_sql($qry_log);
		
        fwrite($logFile, $log_msg);
        fclose($logFile);
        */
	}

	function __destruct(){
		if($this->conn_manual_set == 1 && $_SERVER['REMOTE_ADDR'] == '172.29.87.120'){
			$this->conn_temp->close();
			$this->conn_main->close();
			$this->conn_mid->close();
			$this->conn_local->close();
			$this->conn_iro->close();
			$this->conn_appr->close();
		}
	}
}
?>
