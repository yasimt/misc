<?php
	class searchDet_Model extends Model {
		private $limitVal	=	50;
		public $count = 0; 
		//~ public $resultArr	=	array();
		public	$temp = 0;
		//Test comment
		public function __construct() {
			parent::__construct();
		}

		public function getcompanydetails(){
			GLOBAL $parseConf;
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
			$resultArr	=	array();
			
			$debug = 0;
			if($parseConf['servicefinder']['remotecity'] == 1){
				$data_city = 'remote';
				
			}else{
				$data_city = $parseConf['servicefinder']['servicecity'];
			}
			$url						=	DECS_CITY.'/api_services/catsearchapi.php?rquest=categoryautosuggest&catnm='.urlencode($params['term']).'&module=cs&city='.strtolower($data_city).'&limit=20&sflag=2&stflag=2';
			
			if($debug){
				print"<pre>";print_r($parseConf);
				echo $data_city;
				echo "<br>".$url;
				die;
				
			}
			
			$curlParams					= 	array();
			$curlParams['url']			= 	$url;
			$curlParams['formate'] 		=  	'basic';
			$singleCheck				=	Utility::curlCall($curlParams);	
			$strnArr						=	json_decode($singleCheck,true);
			if(count($strnArr)>0){
					foreach($strnArr as $results => $value){
						foreach($value as $key1 =>$autosug_data){
							if($autosug_data['area'] !=''){
							   $areStr =  " [".$autosug_data['area']."]";
							}else{
							   $areStr =  "";
							}
							$resultArr['data'][$key1]['comp'] = trim(ucwords(strtolower(strip_tags($autosug_data['value'] .$areStr))));
							$resultArr['data'][$key1]['parid'] = $autosug_data['id'];
						}
					}
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Company Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'Company Not Found';
				}	
			return json_encode($resultArr);			
		}
		
		public function getallinfo(){
			header('Content-Type: application/json');
		
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
			$resultArr	=	array();
			$dbObj	=	new DB($this->db['db_local']);
			$query	=	"SELECT * FROM allocation.tbl_dataAnalysis_main WHERE parentid = '" . $params['parid'] . "'";
			$con	=	$dbObj->query($query);
			$numRows = $dbObj->numRows($con);
			$res		=	$dbObj->fetchData($con);
			
			$namequery1 = "select empName from d_jds.mktgEmpMaster where mktEmpCode = '" . $res['allocatedTo'] . "'";
			$namequery2 = "select empName from d_jds.mktgEmpMaster where mktEmpCode = '" . $res['wasallocatedTo'] . "'";
			$conname1	=	$dbObj->query($namequery1);
			$conname2	=	$dbObj->query($namequery2);
			$numRowsname1		=	$dbObj->numRows($conname1);
			$numRowsname2		=	$dbObj->numRows($conname2);
			if($numRowsname1 >0){
				$resname1		=	$dbObj->fetchData($conname1);
			}
			if($numRowsname2 >0){
				$resname2		=	$dbObj->fetchData($conname2);
			}
			$queryfin1 = "SELECT distinct disposition_value,disposition_name FROM d_jds.tbl_disposition_info";
			$confin1	=	$dbObj->query($queryfin1);
			$numRowsfin1 = $dbObj->numRows($confin1);
			while($row1		=	$dbObj->fetchData($confin1)){
				$disvals[] = $row1['disposition_value'];
				$disnames[] = $row1['disposition_name'];
				}
				$disarr = explode(",",$res['disposition']);
				foreach($disarr as $dis){
				if( in_array($dis, $disvals)){
					$disfound =  array_search($dis,$disvals);
					$disfinal[] =$disnames[$disfound];
					}
				}
				$queryfin = "SELECT distinct campaignId,campaignName FROM payment_campaign_master";
			$confin	=	$dbObj->query($queryfin);
			$numRowsfin = $dbObj->numRows($confin);
			while($row		=	$dbObj->fetchData($confin)){
				$campids[] = $row['campaignId'];
				$campNames[] = $row['campaignName'];
				}
				$camparr = explode(",",$res['campaigntypeflag']);
				foreach($camparr as $comp){
				if( in_array($comp, $campids)){
					$campfound =  array_search($comp,$campids);
					$campfinal[] =$campNames[$campfound];
					}
				}
				if(sizeof($campfinal) == 0){
						$campfinal[] = "--";
				}
				if(sizeof($disfinal) == 0){
						$disfinal[] = "--";
				}
			$resultArr['data']					=	$res;
			$resultArr['allocName']		=	$resname1['empName'];
			$resultArr['wasAllocName']	=	$resname2['empName'];
			$resultArr['campaigns']	=	$campfinal;
			$resultArr['dispositions']	=	$disfinal;



			if($numRows > 0){
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Company Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'Company Not Found';
				}	
			return json_encode($resultArr);			
		}
		
		public function getCampaignNames(){
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
			$resultArr	=	array();
			$dbFin	=	new DB($this->db['db_finance']);		
			$queryfin = "SELECT distinct campaignId,campaignName FROM payment_campaign_master";
			$confin	=	$dbFin->query($queryfin);
			$numRows = $dbFin->numRows($confin);
			while($res		=	$dbFin->fetchData($confin)){
				$resultArr['data'][] = $res;
			}
			if($numRows > 0){
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'campaignid Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'campaignid Not Found';
				}	
			return json_encode($resultArr);			
		}
		
		public function getDispositionNames(){
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
			$resultArr	=	array();
			$dbFin	=	new DB($this->db['db_local']);		
			$queryfin = "SELECT distinct disposition_value,disposition_name FROM d_jds.tbl_disposition_info";
			$confin	=	$dbFin->query($queryfin);
			$numRows = $dbFin->numRows($confin);
			while($res		=	$dbFin->fetchData($confin)){
				$resultArr['data'][] = $res;
			}
			if($numRows > 0){
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'dispotions Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'dispotions Not Found';
				}	
			return json_encode($resultArr);			
		}
		
		public function getInstrumentDet(){
			GLOBAL $parseConf;
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
            $resultArr	=	array();
            if($parseConf['servicefinder']['remotecity'] == 1){
				$params['deptCityVarGet'] = 'remote_cities';
				
			}else{
				$params['deptCityVarGet'] = $parseConf['servicefinder']['servicecity'];
			}
			$url= "http://genio.in/tme_services/services/dataAnalysis.php?deptCityVarGet=".$params['deptCityVarGet']."&insId=".$params['insID'];
			$curlParams					= 	array();
			$curlParams['url']			= 	$url;
			$curlParams['formate'] 		=  	'basic';
			$singleCheck				=	Utility::curlCall($curlParams);	
			$strnArr						=	json_decode($singleCheck,true);
			if($strnArr['errorCode'] == 0){
					$resultArr['data']			= 	$strnArr;
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'dispotions Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'dispotions Not Found';
				}	
			return json_encode($resultArr);			
		}
		
		public function getEmpAssignments($emp,$cond){
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
            if(isset($emp) && $emp != '' && $emp!=null){
				 $params['empcode'] = $emp;
				 $cond1 = $cond;
			}else{
            if( (($params['paidstatus'] == 0 )|| ($params['paidstatus'] == 1) || ($params['paidstatus'] == 2))&&  ($params['paidstatus'] != '' )){
				$cond.= "and paidstatus = '".$params['paidstatus']."'";
			}else{
			$cond.= "";
			}
			if($params['likelyExpIn'] != ''){
				$cond1 .= "AND expiry_days <= '".$params['likelyExpIn']."' and paidstatus = 1 ";
			}else{
				$cond1 .= "";
			}
			if(($params['expirydateFrom'] != '')  && ($params['expirydateTo'] != '') ){
				$cond2 .= "AND DATE(expirydate) BETWEEN '".$params['expirydateFrom']."' AND  '".$params['expirydateTo']."'";
			}else{
				$cond2 .= "";
			}
			}
			$resultArr	=	array();
			$dbObj	=	new DB($this->db['db_local']);
			$namequery = "select empName from d_jds.mktgEmpMaster where mktEmpCode = '" . $params['empcode'] . "'";	
			$queryemp = "SELECT * FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $params['empcode'] . "' ".$cond." ".$cond1." ".$cond2." ORDER BY (paidstatus = 1) DESC, paidstatus ASC";
			$conemp =	$dbObj->query($queryemp);
			$numRows = $dbObj->numRows($conemp);
			$queryfin1 = "SELECT distinct disposition_value,disposition_name FROM d_jds.tbl_disposition_info";
			$confin1	=	$dbObj->query($queryfin1);
			$numRowsfin1 = $dbObj->numRows($confin1);
			while($row1		=	$dbObj->fetchData($confin1)){
				$disvals[] = $row1['disposition_value'];
				$disnames[] = $row1['disposition_name'];
				}
				
				$queryfin = "SELECT distinct campaignId,campaignName FROM payment_campaign_master";
			$confin	=	$dbObj->query($queryfin);
			$numRowsfin = $dbObj->numRows($confin);
			while($row		=	$dbObj->fetchData($confin)){
				$campids[] = $row['campaignId'];
				$campNames[] = $row['campaignName'];
				}
				
				//~ if(sizeof($campfinal) == 0){
						//~ $campfinal[] = "--";
				//~ }
				//~ if(sizeof($disfinal) == 0){
						//~ $disfinal[] = "--";
				//~ }
			while($res		=	$dbObj->fetchData($conemp)){
				$campfinal = array();
				$disfinal = array();
				$disarr = explode(",",$res['disposition']);
				foreach($disarr as $dis){
				if( in_array($dis, $disvals)){
					$disfound =  array_search($dis,$disvals);
					$disfinal[] =$disnames[$disfound];
					}
				}
				$camparr = explode(",",$res['campaigntypeflag']);
				foreach($camparr as $comp){
				if( in_array($comp, $campids)){
					$campfound =  array_search($comp,$campids);
					$campfinal[] =$campNames[$campfound];
					}
				}
				$i = 0;
				if($res['instrumentDet'] != '' && $res['instrumentDet'] !=null){
					$instrumentdet	=	explode(",",$res['instrumentDet']);
							foreach($instrumentdet as $ins){
								$temp = explode("~",$ins);
								$instID = $temp[0];
								$instDate = $temp[1];
								$res['instrument'][$i]['insID'] = $instID;
								$res['instrument'][$i]['insDate'] = $instDate;
								$i++;
							}
						}else{
								$res['instrument'] = '';
							
							}
				$i = 0;
				if($res['recordings'] != '' && $res['recordings'] !=null){
					$recordings	=	explode(",",$res['recordings']);
					foreach($recordings as $recording){
						if($recording != '' && $recording != null){
							$res['record'][$i]= $recording;
							$i++;
						}
						
					}
				}else{
					$res['record'] = '';
							
				}
				$res['disposition'] = $disfinal;
				$res['campaigntypeflag'] = $campfinal;
				$resultArr['data'][] = $res;
			}
			$resultArr['total'] = $numRows;
			$conempname =	$dbObj->query($namequery);
			$empname = $dbObj->fetchData($conempname);
			$resultArr['empname'] = $empname['empName'];
			$querypd1 = "SELECT count(*) as pcount FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $params['empcode'] . "' and paidstatus = 1 ";//paid count
			$querypd2 = "SELECT count(*) as ecount FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $params['empcode'] . "' and paidstatus = 2 ";//expired count
			$querypd0 = "SELECT count(*) as fcount FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $params['empcode'] . "' and paidstatus = 0 ";//nonpaid count
			$conpd1 =	$dbObj->query($querypd1);
			$conpd2 =	$dbObj->query($querypd2);
			$conpd0 =	$dbObj->query($querypd0);
			$respd1		=	$dbObj->fetchData($conpd1);
			$respd2		=	$dbObj->fetchData($conpd2);
			$respd3		=	$dbObj->fetchData($conpd0);
			if($numRows > 0){
					$resultArr['paid']			=	$respd1['pcount'];
					$resultArr['expired']		=	$respd2['ecount'];
					$resultArr['nonpaid']		=	$respd3['fcount'];
					$resultArr['errorCode']		=	0;
					$resultArr['errorStatus']	=	'Success';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'Failed';
				}	
			return json_encode($resultArr);			
		}
		

		
		public function getManagerDetails(){
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
			$resultArr	=	array();
			$tempsum = 0;
			$tempapp = 0;
			$dbObj	=	new DB($this->db['db_local']);
			$queryemp = "SELECT employee_id,employee_name,data_city,reporting_head_code,reporting_head_name from d_jds.tbl_employee_lineage_new WHERE reporting_head_code='".$params['empcode']."'";
			$conemp =	$dbObj->query($queryemp);
			$numRows = $dbObj->numRows($conemp);
			$queryMandata = "SELECT count(*) as totalno, sum(contractValue) as value FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" .$params['empcode']. "' ";
			$conMandata =	$dbObj->query($queryMandata);
			$numMandata = $dbObj->numRows($conMandata);
			if($numMandata > 0){
				$Mandata = $dbObj->fetchData($conMandata);
				$tempsum= $Mandata['value'];
				$tempapp= $Mandata['totalno'];
			}else{
				$resultArr['totalofall']= 0; 
				$resultArr['sumofall'] = 0;
				}
			if($numRows > 0){
			while($res		=	$dbObj->fetchData($conemp)){
				$checkrep = "SELECT employee_id,employee_name,data_city,reporting_head_code,reporting_head_name from d_jds.tbl_employee_lineage_new WHERE reporting_head_code='".$res['employee_id']."'";
				$concheckrep =	$dbObj->query($checkrep);
				$numcheckrep = $dbObj->numRows($concheckrep);
				if($numcheckrep > 0){
					$res['repflag'] = 1;
				}else{
					$res['repflag'] = 0;
				}
				$queryemp1 = "SELECT count(*) as totalno, sum(contractValue) as value FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $res['employee_id'] . "' ";
				$conemp1 =	$dbObj->query($queryemp1);
				$numRows1 = $dbObj->numRows($conemp1);
				$res1 = $dbObj->fetchData($conemp1);
				$res['sum']= $res1['value'];
				$resultArr['sumofall'] = $tempsum + $res1['value']; 
				$tempsum = $resultArr['sumofall'];
				$res['totalno']= $res1['totalno'];
				$resultArr['totalofall'] = $tempapp + $res1['totalno']; 
				$tempapp = $resultArr['totalofall'];
				if($res['sum'] == null){
					$res['sum'] = 0;
				}
				$resultArr['data'][]= $res;
				}
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'reportees Found';
			}else{
					$empDet = "SELECT employee_id,employee_name,data_city,reporting_head_code,reporting_head_name from d_jds.tbl_employee_lineage_new WHERE employee_id='".$params['empcode']."'";
					$conempDet =	$dbObj->query($empDet);
					$numRowsDet = $dbObj->numRows($conempDet);
					if($numRowsDet > 0){
						while($row		=	$dbObj->fetchData($conempDet)){
							$queryemp1 = "SELECT count(*) as totalno, sum(contractValue) as value FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo = '" . $row['employee_id'] . "' ";
							$conemp1 =	$dbObj->query($queryemp1);
							$numRows1 = $dbObj->numRows($conemp1);
							$res1 = $dbObj->fetchData($conemp1);
							$row['sum']= $res1['value'];
							if($row['sum'] == null){
								$row['sum'] = 0;
								}
							$row['totalno']= $res1['totalno'];
							$row['repflag'] = 0;
							$resultArr['data'][]= $row;
						}
						$resultArr['totalofall']= 0; 
						$resultArr['sumofall'] = 0;
						$resultArr['errorCode']		=	0;
						$resultArr['errorStatus']	=	'employee Found';
					}else{
						$resultArr['errorCode']		=	1;
						$resultArr['errorStatus']	=	'reportees Not Found';
					}
			}	
			return json_encode($resultArr);			
		}
		
		public function empRecursiveFunc($empcode){
			$chckflag = array();
			$resultArr= array();
			$dbObj	=	new DB($this->db['db_local']);
			foreach($empcode as $employee){
			$checkrep = "SELECT employee_id  from d_jds.tbl_employee_lineage_new WHERE reporting_head_code='".$employee."'";
			$concheckrep =	$dbObj->query($checkrep);
			$numcheckrep = $dbObj->numRows($concheckrep);
				while($emp = $dbObj->fetchData($concheckrep)){
					$emparray[] = $emp['employee_id'];
					$checkrep1 = "SELECT employee_id  from d_jds.tbl_employee_lineage_new WHERE reporting_head_code='".$emp['employee_id']."'";
					$concheckrep1 =	$dbObj->query($checkrep1);
					$numcheckrep1 = $dbObj->numRows($concheckrep1);
					if($numcheckrep1 > 0){
							$chckflag[] = 1;
							$passbackarr[] = $emp['employee_id'];
					}else{
							$chckflag[] = 0;
					}
					
				}
			}
			if($emparray != null && $emparray !='' && sizeOf($emparray)!=0 ){
				$resultArr['empArr'] = $emparray;
			}
			if (in_array(1, $chckflag)){
				$resultArr['chckflag'] = 1;
				$resultArr['passbackarr'] = $passbackarr;
				}else{
					$resultArr['chckflag'] = 0;
				}
			return $resultArr;
			}
			

		public function getAllEmpAssignments(){ 
			ini_set('memory_limit', '500M');
			header('Content-Type: application/json');
			if(isset($_REQUEST['urlFlag']))
            {
                $params    =    $_REQUEST;
            }
            else
            {
                $params    = json_decode(file_get_contents('php://input'),true);
            }
            $cond1 = "";
			$resultArr = array();
			$retArr = array();
			$empArr = array();
			$empArrfinal =array();
			$tempArr	=array();
			if(isset($params['likelyExpIn']) && $params['likelyExpIn'] != ''){
				$cond1 .= "AND expiry_days <= '".$params['likelyExpIn']."' and paidstatus = 1 ";
			}else{
				$cond1 .= "";
			}
			$dbObj	=	new DB($this->db['db_local']);
			$queryfin1 = "SELECT distinct disposition_value,disposition_name FROM d_jds.tbl_disposition_info";
			$confin1	=	$dbObj->query($queryfin1);
			$numRowsfin1 = $dbObj->numRows($confin1);
			while($row1		=	$dbObj->fetchData($confin1)){
				$disvals[] = $row1['disposition_value'];
				$disnames[] = $row1['disposition_name'];
				}	
			$queryfin = "SELECT distinct campaignId,campaignName FROM payment_campaign_master";
			$confin	=	$dbObj->query($queryfin);
			$numRowsfin = $dbObj->numRows($confin);
			while($row		=	$dbObj->fetchData($confin)){
				$campids[] = $row['campaignId'];
				$campNames[] = $row['campaignName'];
				}
				
			$empArr[] = $params['empcode'];
			$result = $this->empRecursiveFunc($empArr);
			do{
				if($result['empArr'] != null && $result['empArr'] !='' && sizeOf($result['empArr'])!=0 ){
					$empArr[] = $result['empArr'];
				}
				if($result['chckflag'] == 1 ){
					$result = $this->empRecursiveFunc($result['passbackarr']);
				}
			}while($result['chckflag'] == 1);
			
			if($result['empArr'] != null && $result['empArr'] !='' && sizeOf($result['empArr'])!=0 ){
				$empArr[] = $result['empArr'];
			}
			foreach($empArr as $employeelist){
				if(is_array($employeelist)){
					$empArrfinal = array_merge($tempArr,$employeelist);
					$tempArr = $empArrfinal;
				}else{
					$makearr = array();
					$makearr[] = $employeelist;
					$empArrfinal = array_merge($tempArr,$makearr);
					$tempArr = $empArrfinal;
					}
				}
				
			if($empArrfinal != null && $empArrfinal !='' && sizeOf($empArrfinal)!=0 ){
			$imploded_emparr = implode(',',$empArrfinal);
			$queryemp = "SELECT * FROM allocation.tbl_dataAnalysis_main WHERE allocatedTo IN (".$imploded_emparr.")  ".$cond1." ORDER BY (paidstatus = 1) DESC, paidstatus ASC";
			$conemp =	$dbObj->query($queryemp);
			$numRows = $dbObj->numRows($conemp);	
			while($res		=	$dbObj->fetchData($conemp)){
				$this->count++;
				$campfinal = array();
				$disfinal = array();
				$disarr = explode(",",$res['disposition']);
				foreach($disarr as $dis){
				if( in_array($dis, $disvals)){
					$disfound =  array_search($dis,$disvals);
					$disfinal[] =$disnames[$disfound];
					}
				}
				$camparr = explode(",",$res['campaigntypeflag']);
				foreach($camparr as $comp){
				if( in_array($comp, $campids)){
					$campfound =  array_search($comp,$campids);
					$campfinal[] =$campNames[$campfound];
					}
				}
				$i = 0;
				if($res['instrumentDet'] != '' && $res['instrumentDet'] !=null){
					$instrumentdet	=	explode(",",$res['instrumentDet']);
							foreach($instrumentdet as $ins){
								$temp = explode("~",$ins);
								$instID = $temp[0];
								$instDate = $temp[1];
								$res['instrument'][$i]['insID'] = $instID;
								$res['instrument'][$i]['insDate'] = $instDate;
								$i++;
							}
						}else{
								$res['instrument'] = '';
							
							}		
				$i = 0;
				if($res['recordings'] != '' && $res['recordings'] !=null){
					$recordings	=	explode(",",$res['recordings']);
					foreach($recordings as $recording){
						if($recording != '' && $recording != null){
							$res['record'][$i]= $recording;
							$i++;
						}
					}
				}else{
					$res['record'] = '';
							
				}
				$from=date_create(date('Y-m-d'));
				$to=date_create($res['expirydate']);
				$diff=date_diff($from,$to);
				$res['expirydate'] = $diff->days;
				$res['expiry_days'] = floor($res['expiry_days']);
				$res['disposition'] = $disfinal;
				$res['campaigntypeflag'] = $campfinal;
				$resultArr['data'][] = $res;
				}
				}else{
					$this->count =0;
				}
				$resultArr['count'] = $this->count;
				if($this->count > 0){
				$resultArr['errorCode']		=	0;
				$resultArr['errorStatus']	=	'data Found';
				}else{
					$resultArr['errorCode']		=	1;
					$resultArr['errorStatus']	=	'data Not Found';
				}
					
				return json_encode($resultArr);			
			}
	
	}
?>
