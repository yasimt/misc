<?php 

class JdominiInfo_Model extends Model {
	public function __construct(){
		parent::__construct();
	}
	public function showTimingSlots(){
		header('Content-Type: application/json');
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		//~ $create_table_qur 		=	"Create table `d_jds`.`tbl_jd_omini_time_slots`(`autoId` int(11) NOT NULL AUTO_INCREMENT,`jdominiCode` int(11) NOT NULL COMMENT 'tmeCode',`allocationDate` 		datetime NOT NULL,`time_slots` text COMMENT 'comma seperated',`allocation_type` int(11),`activeFlag` tinyint(11) NOT NULL DEFAULT 1,`createdOn` datetime NOT NULL COMMENT 'time of entry', Key(`autoId`), primary key (`jdominiCode`)) ENGINE=InnoDB;";
		//~ $conn_create_table_qur 	=	$con_local->query($create_table_qur);
		//~ if($conn_create_table_qur){
			//~ $retArr['errorCode']	=	0;
			//~ $retArr['errorStatus']	=	'Table Created';
		//~ }else{
			//~ $retArr['errorCode']	=	1;
			//~ $retArr['errorStatus']	=	'Data Not Found';
		//~ }
		$followUp_Callback_arr 		=	array("08:00","08:30","09:00","09:30","10:00","10:30","11:00","11:30","12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30","16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30","20:00","20:30","21:00","21:30","22:00","22:30","23:00","23:30");
		$params		=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		$urlFlag	=	$_REQUEST['urlFlag'];
		if(!$urlFlag){
			$this->dateSend		=	$params['date'];
			$this->stVal		=	$params['stVal'];
			$this->JdOminiCode	=	$params['JdOminiCode'];
		}else{
			$this->dateSend		=	$_REQUEST['date'];
			$this->stVal		=	$_REQUEST['stVal'];
			$this->JdOminiCode	=	$_REQUEST['JdOminiCode'];
		}
		if($this->stVal == "" || empty($this->stVal)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"Disposition value not present";
			return json_encode($retArr); die;
		}
		if($this->JdOminiCode == "" || empty($this->JdOminiCode)){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARAMETER ISUUE";
			return json_encode($retArr); die;
		}
		
		if($this->dateSend == "" || empty($this->dateSend)) {
			$actiondate		=	date('Y-m-d',strtotime('+1 day'));
			$this->dateSend	=	$actiondate;
		}
		$givenDate     		= 	date("Y-m-d", strtotime($this->dateSend));
		$future_Date 		=	date('Y/m/d',strtotime('+1 day'));
		$currentTime 		=	date('H:i');
		$Currentdate 		= 	date('Y-m-d');
		if($givenDate>$Currentdate){
			//enabled
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'0');
			}
		}elseif($givenDate<$Currentdate){
			//disabled
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'1');
				
			}
		}else{
			for ($i=0; $i <count($followUp_Callback_arr) ; $i++) { 
				if( $followUp_Callback_arr[$i] <	$currentTime){
					//disabled time
					$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'1');
				}else{
					//enabled time
					$retArrGrab[$followUp_Callback_arr[$i]]	=	array("flag"=>'0');
				}
			}
		}
		$retArr['data'] 		=	$retArrGrab;
		$retArr['errorCode'] 	=	0;
		$retArr['errorStatus'] 	=	"Data Found";
		$retArr['future_Date'] 	=	$future_Date;
		$retArr['givenDate']	=	$givenDate;
		return json_encode($retArr);
	}
	/*
	 * This Function is responsible for the download xls file of jdomini invite Report
	 * Created by Apoorv
	 * Date: 02/06/2016
	*/
	public function showJDOMINIAppts(){
		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$repoData				=	'';
		if(isset($_REQUEST['urlFlag']) && !empty($_REQUEST['urlFlag'])){
			$urlFlag 	=	$_REQUEST['urlFlag'];
		}
		if(!$urlFlag){
			$this->startDate		=	$params['startDate'];
			$this->endDate			=	$params['endDate'];
		}else{
			$this->startDate		=	$_REQUEST['startDate'];
			$this->endDate			=	$_REQUEST['endDate'];
		}
		if($this->startDate == '' || $this->endDate == ''){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARIS";
			return json_encode($retArr); die;
		}
		$startTime 				=	date("Y-m-d", strtotime($this->startDate))." 00:00:00";
		$EndTime 				=	date("Y-m-d", strtotime($this->endDate))." 23:59:59";
		$getJdOMINIRepo_qry 	=	"Select contractCode,allocationType,allocationTime,actionTime,compname,tmename,empcode from tblContractAllocation where (allocationType= '317' AND parentCode != '' AND flgAllocStatus = 1) AND (allocationTime >='".$startTime."' AND allocationTime <='".$EndTime."')";
		$getJdOMINIRepo_qry_con 	=	$con_local->query($getJdOMINIRepo_qry);
		$num_getJdOMINIRepo_qry		=	$con_local->numRows($getJdOMINIRepo_qry_con);
		if($num_getJdOMINIRepo_qry>0){
			$table= "<table style='font-size:50px'>";
				$table .= "<tr>";
				$table .= "<td>Sr</td>";
				$table .= "<td>ParentId</td>";
				$table .= "<td>Allocation Type</td>";
				$table .= "<td>Allocation Time</td>";
				$table .= "<td>Action Time</td>";
				$table .= "<td>Company Name</td>";
				$table .= "<td>EMPLOYEE CODE</td>";
				$table .= "<td>EMPLOYEE NAME</td>";
				$table .= "</tr>";
				$table .= "<td></td>";
				$i=1;
			while($JDominiData = $con_local->fetchData($getJdOMINIRepo_qry_con)) {
				$retArr['data'][] 		= 	$JDominiData;
				$table .= '<tr>';
				$table .= '<td>'.$i.'</td>';
				$table .= '<td>'.$JDominiData['contractCode'].'</td>';
				$table .= '<td>'.$JDominiData['allocationType'].'</td>';
				$table .= '<td>'.$JDominiData['allocationTime'].'</td>';
				$table .= '<td>'.$JDominiData['actionTime'].'</td>';
				$table .= '<td>'.$JDominiData['compname'].'</td>';
				$table .= '<td>'.$JDominiData['empcode'].'</td>';
				$table .= '<td>'.$JDominiData['tmename'].'</td>';
				$table .= '</tr>';
				$i++;				
			}
			$table .= "</table>";
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"DF";
			$retArr['qurExecuted']  =	$getJdOMINIRepo_qry;
		}else{
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"DNF";
		}
		$ext		=	'.xls';
		$filename 	=	'JdominReport'.$this->startDate.'to'.$this->endDate;
		header("Content-type: application/ms-excel"); 
		header("Content-Disposition: attachment; filename=\"".$filename.$ext."\"");
		header("Content-Transfer-Encoding: binary");
		return ($table);die;
	}
	public function showGroupByJDOMINIData(){
		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$urlFlag				=	$_REQUEST['urlFlag'];
		if(!$urlFlag){
			$this->startDate		=	$params['startDate'];
			$this->endDate			=	$params['endDate'];
		}else{
			$this->startDate		=	$_REQUEST['startDate'];
			$this->endDate			=	$_REQUEST['endDate'];
		}
		if($this->startDate == '' || $this->endDate == ''){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARIS";
			return json_encode($retArr); die;
		}
		$startTime 				=	date("Y-m-d", strtotime($this->startDate))." 00:00:00";
		$EndTime 				=	date("Y-m-d", strtotime($this->endDate))." 23:59:59";
		$getJdOMINIRepo_qry 	=	"Select count(1) as jdOminiCnt,tmename,empCode as tmeCode from tblContractAllocation where (allocationType= '317' AND parentCode != '' AND flgAllocStatus = 1) AND (allocationTime >='".$startTime."' AND allocationTime <='".$EndTime."')  GROUP BY empCode";
		$getJdOMINIRepo_qry_con 	=	$con_local->query($getJdOMINIRepo_qry);
		$num_getJdOMINIRepo_qry		=	$con_local->numRows($getJdOMINIRepo_qry_con);
		if($num_getJdOMINIRepo_qry>0){
			while($JDominiData = $con_local->fetchData($getJdOMINIRepo_qry_con)) {
				$retArr['data'][] 		= 	$JDominiData;
			}
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"DF";
			$retArr['qurExecuted']  =	$getJdOMINIRepo_qry;
			$retArr['page']			=	1;
		}else{
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"DNF";
			$retArr['page']			=	1;
		}
		return json_encode($retArr);die;
	}
	
	public function getDetailsJDOmini(){
		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		$urlFlag				=	$_REQUEST['urlFlag'];
		if(!$urlFlag){
			$this->startDate		=	$params['startDate'];
			$this->endDate			=	$params['endDate'];
			$tmeCode				=	$params['tmeCode'];
		}else{
			$this->startDate		=	$_REQUEST['startDate'];
			$this->endDate			=	$_REQUEST['endDate'];
			$tmeCode				=	$_REQUEST['tmeCode'];
		}
		if($this->startDate == '' || $this->endDate == '' || $this->tmeCode=''){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARIS";
			return json_encode($retArr); die;
		}
		$startTime 				=	date("Y-m-d", strtotime($this->startDate))." 00:00:00";
		$EndTime 				=	date("Y-m-d", strtotime($this->endDate))." 23:59:59";
		$getJdOMINIRepo_qry 	=	"Select empCode as tmeCode,tmename,actionTime as timeSlots, COUNT(1) AS timeCnt from tblContractAllocation where (allocationType= '317' AND parentCode != '' AND flgAllocStatus = 1) AND (allocationTime >='".$startTime."' AND allocationTime <='".$EndTime."') AND (empCode = '".$tmeCode."' || parentCode = '".$tmeCode."') group by actionTime";
		$getJdOMINIRepo_qry_con 	=	$con_local->query($getJdOMINIRepo_qry);
		$num_getJdOMINIRepo_qry		=	$con_local->numRows($getJdOMINIRepo_qry_con);
		if($num_getJdOMINIRepo_qry>0){
			while($JDominiData = $con_local->fetchData($getJdOMINIRepo_qry_con)) {
				$retArr['data'][] 		= 	$JDominiData;
			}
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"DF";
			$retArr['qurExecuted']  =	$getJdOMINIRepo_qry;
			$retArr['page']			=	2;
		}else{
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"DNF";
			$retArr['page']			=	2;
		}
		//$retArr['qry']	=	$getJdOMINIRepo_qry;
		return json_encode($retArr);die;
	}
	public function TmeCodeWiseData(){
		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);
		$con_local 				=	new DB($this->db['db_local']);
		$retArr 				=	array();
		if(!$urlFlag){
			$this->startDate		=	$params['startDate'];
			$this->endDate			=	$params['endDate'];
			$tmeCode				=	$params['tmeCode'];
			$actionTime				=	$params['actionTime'];
		}else{
			$this->startDate		=	$_REQUEST['startDate'];
			$this->endDate			=	$_REQUEST['endDate'];
			$tmeCode				=	$_REQUEST['tmeCode'];
			$actionTime				=	$_REQUEST['actionTime'];
		}
		if($this->startDate == '' || $this->endDate == '' || $this->tmeCode=''){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARIS";
			return json_encode($retArr); die;
		}
		$startTime 				=	date("Y-m-d", strtotime($this->startDate))." 00:00:00";
		$EndTime 				=	date("Y-m-d", strtotime($this->endDate))." 23:59:59";
		$getJdOMINIRepo_qry 	=	"Select contractCode,allocationType,allocationTime,actionTime,compname,tmename,empCode as tmeCode from tblContractAllocation where (allocationType= '317' AND parentCode != '' AND flgAllocStatus = 1) AND (allocationTime >='".$startTime."' AND allocationTime <='".$EndTime."') AND (empCode = '".$tmeCode."' || parentCode = '".$tmeCode."') AND actionTime='".$actionTime."'";
		$getJdOMINIRepo_qry_con 	=	$con_local->query($getJdOMINIRepo_qry);
		$num_getJdOMINIRepo_qry		=	$con_local->numRows($getJdOMINIRepo_qry_con);
		if($num_getJdOMINIRepo_qry>0){
			while($JDominiData = $con_local->fetchData($getJdOMINIRepo_qry_con)) {
				$retArr['data'][] 		= 	$JDominiData;
			}
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"DF";
			$retArr['qurExecuted']  =	$getJdOMINIRepo_qry;
			$retArr['page']			=	3;
		}else{
			$retArr['data_count']	= 	$num_getJdOMINIRepo_qry;
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"DNF";
			$retArr['page']			=	3;
		}
		//$retArr['qry']	=	$getJdOMINIRepo_qry;
		return json_encode($retArr);die;
	}
	public function getTodayDate(){
		$urlFlag				=	$_REQUEST['urlFlag'];
		header('Content-Type: application/json');
		$params					=	json_decode(file_get_contents('php://input'),true);
		$retArr 				=	array();
		if(!$urlFlag){
			$jdominiFlg		=	$params['jdominiFlg'];
		}else{
			$jdominiFlg		=	$_REQUEST['jdominiFlg'];
		}
		if($jdominiFlg == '' || $jdominiFlg ==0){
			$retArr['errorCode'] 	=	1;
			$retArr['errorStatus'] 	=	"PARIS";
		}else{
			$retArr['startDate']	=	date('Y/m/d');
			$retArr['endDate']		=	date('Y/m/d');
			$retArr['errorCode'] 	=	0;
			$retArr['errorStatus'] 	=	"NO PARIS";
		}
		echo json_encode($retArr);die;
	}
}
?>
