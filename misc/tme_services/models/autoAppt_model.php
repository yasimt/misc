<?php 
/**
* Controller created to give the report of Auto Alloc Process
* Created by Apoorv Agrawal
* Date : 14-06-2017
*/

class AutoAppt_Model extends Model{	
	public function __construct(){
		# code...
		 parent::__construct();
	}
	public function get_view_exec_rec(){
		header('Content-Type: application/json');
		$params		=	array();
		$argsArr	=	array();
		$retArr		=	array();
		$params		=	array_merge($_REQUEST,$_POST);
		$whereConditn	=	'';
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		if(isset($argsArr['startDate']) && !isset($argsArr['endDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"END DATE ISSUE";
			return json_encode($retArr);
		}elseif(isset($argsArr['endDate']) && !isset($argsArr['startDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"START DATE ISSUE";
			return json_encode($retArr);
		}
		if(isset($argsArr['startDate']) && isset($argsArr['endDate']) && $argsArr['endDate'] !='' && $argsArr['startDate'] !=''){
			$params['startDate']	=	date("Y-m-d", strtotime($argsArr['startDate']));
			$params['endDate']	=	date("Y-m-d", strtotime($argsArr['endDate']));
			$whereConditn	=	" AND a.insertedOn >= '".$argsArr['startDate']." 00:00:00' AND a.insertedOn <= '".$argsArr['endDate']." 23:59:59'";
			$retArr['startDate']	=	$argsArr['startDate'];
			$retArr['endDate']	=	$argsArr['endDate'];
		}else{
			$currentTime	=	date("Y-m-d");
			//~ $currentTime	=	'2017-01-10'; // have to change this 15/02/2017
			$whereConditn	=	" AND a.insertedOn >= '".$currentTime." 00:00:00' AND a.insertedOn <= '".$currentTime." 23:59:59'";
			$retArr['startDate']	=	$currentTime;
			$retArr['endDate']	=	$currentTime;
		}
		$dbObjLocal	=	new DB($this->db['db_local']);
		$sel_qur_view_exec	=	"SELECT * FROM d_jds.tbl_apptLogs AS a JOIN d_jds.tblContractAllocation AS b ON a.parentid = b.contractCode AND CONCAT(a.appointmentDate , ' ' , a.actionTime) = b.actionTime WHERE a.appt_alloc = 1 AND a.all_me = 2 AND b.allocationType IN (25,99) ".$whereConditn." ORDER BY a.insertedOn DESC";
		$con_sel_qur_view_exec	=	$dbObjLocal->query($sel_qur_view_exec);
		$num_sel_qur_view_exec	=	$dbObjLocal->numRows($con_sel_qur_view_exec);
		if($num_sel_qur_view_exec > 0){
			while($row 		= 	$dbObjLocal->fetchData($con_sel_qur_view_exec)){
				$retArr['data'][] 	=	$row;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data Found";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data Not Found";
		}
		return json_encode($retArr);
	}
	public function get_click_alloc_fresh(){
		header('Content-Type: application/json');
		$params		=	array();
		$argsArr	=	array();
		$retArr		=	array();
		$params		=	array_merge($_REQUEST,$_POST);
		$whereConditn	=	'';
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		if(isset($argsArr['startDate']) && !isset($argsArr['endDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"END DATE ISSUE";
			return json_encode($retArr);
		}elseif(isset($argsArr['endDate']) && !isset($argsArr['startDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"START DATE ISSUE";
			return json_encode($retArr);
		}
		if(isset($argsArr['startDate']) && isset($argsArr['endDate']) && $argsArr['endDate'] !='' && $argsArr['startDate'] !=''){
			$params['startDate']	=	date("Y-m-d", strtotime($argsArr['startDate']));
			$params['endDate']	=	date("Y-m-d", strtotime($argsArr['endDate']));
			$whereConditn	=	" AND a.insertedOn >= '".$argsArr['startDate']." 00:00:00' AND a.insertedOn <= '".$argsArr['endDate']." 23:59:59'";
			$retArr['startDate']	=	$argsArr['startDate'];
			$retArr['endDate']	=	$argsArr['endDate'];
		}else{
			$currentTime	=	date("Y-m-d");
			//~ $currentTime	=	'2017-01-10'; // have to change this 15/02/2017
			$whereConditn	=	" AND a.insertedOn >= '".$currentTime." 00:00:00' AND a.insertedOn <= '".$currentTime." 23:59:59'";
			$retArr['startDate']	=	$currentTime;
			$retArr['endDate']	=	$currentTime;
		}
		$dbObjLocal	=	new DB($this->db['db_local']);
		$sel_click_alloc_fresh	=	"SELECT * FROM d_jds.tbl_apptLogs AS a JOIN d_jds.tblContractAllocation AS b ON a.parentid = b.contractCode AND CONCAT(a.appointmentDate , ' ' , a.actionTime) = b.actionTime WHERE a.appt_alloc = 1 AND a.cont_allocf = 2 AND a.all_me = 0 AND b.allocationType IN (25,99) ".$whereConditn." ORDER BY a.insertedOn DESC";
		$con_click_alloc_fresh	=	$dbObjLocal->query($sel_click_alloc_fresh);
		$num_click_alloc_fresh	=	$dbObjLocal->numRows($con_click_alloc_fresh);
		if($num_click_alloc_fresh > 0){
			while($row 		= 	$dbObjLocal->fetchData($con_click_alloc_fresh)){
				$retArr['data'][] 	=	$row;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data Found";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data Not Found";
		}
		return json_encode($retArr);
	}
	public function get_click_continue_followUp(){
		header('Content-Type: application/json');
		$params		=	array();
		$argsArr	=	array();
		$retArr		=	array();
		$params		=	array_merge($_REQUEST,$_POST);
		$whereConditn	=	'';
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		if(isset($argsArr['startDate']) && !isset($argsArr['endDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"END DATE ISSUE";
			return json_encode($retArr);
		}elseif(isset($argsArr['endDate']) && !isset($argsArr['startDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"START DATE ISSUE";
			return json_encode($retArr);
		}
		if(isset($argsArr['startDate']) && isset($argsArr['endDate']) && $argsArr['endDate'] !='' && $argsArr['startDate'] !=''){
			$params['startDate']	=	date("Y-m-d", strtotime($argsArr['startDate']));
			$params['endDate']	=	date("Y-m-d", strtotime($argsArr['endDate']));
			$whereConditn	=	" AND a.insertedOn >= '".$argsArr['startDate']." 00:00:00' AND a.insertedOn <= '".$argsArr['endDate']." 23:59:59'";
			$retArr['startDate']	=	$argsArr['startDate'];
			$retArr['endDate']	=	$argsArr['endDate'];
		}else{
			$currentTime	=	date("Y-m-d");
			//~ $currentTime	=	'2017-01-10'; // have to change this 15/02/2017
			$whereConditn	=	" AND a.insertedOn >= '".$currentTime." 00:00:00' AND a.insertedOn <= '".$currentTime." 23:59:59'";
			$retArr['startDate']	=	$currentTime;
			$retArr['endDate']	=	$currentTime;
		}
		$dbObjLocal	=	new DB($this->db['db_local']);
		$sel_click_continue_followUp	=	"SELECT * FROM d_jds.tbl_apptLogs AS a JOIN d_jds.tblContractAllocation AS b ON a.parentid = b.contractCode AND CONCAT(a.appointmentDate , ' ' , a.actionTime) = b.actionTime WHERE a.appt_alloc = 1 AND a.cont_allocf = 0 AND a.followUp = 1 AND a.all_me = 0 AND b.allocationType IN (25,99) ".$whereConditn." ORDER BY a.insertedOn DESC";
		$con_click_continue_followUp	=	$dbObjLocal->query($sel_click_continue_followUp);
		$num_click_continue_followUp	=	$dbObjLocal->numRows($con_click_continue_followUp);
		if($num_click_continue_followUp > 0){
			while($row 		= 	$dbObjLocal->fetchData($con_click_continue_followUp)){
				$retArr['data'][] 	=	$row;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data Found";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data Not Found";
		}
		return json_encode($retArr);
	}
	public function get_fersh_alloc(){
		header('Content-Type: application/json');
		$params		=	array();
		$argsArr	=	array();
		$retArr		=	array();
		$params		=	array_merge($_REQUEST,$_POST);
		$whereConditn	=	'';
		if(isset($params['urlFlag']) && $params['urlFlag'] == 1){
			$argsArr	=	$params;
		}else{
			$argsArr	=	json_decode(file_get_contents('php://input'),true);//GET PARAMETERS FROM curl CALL
		}
		if(isset($argsArr['startDate']) && !isset($argsArr['endDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"END DATE ISSUE";
			return json_encode($retArr);
		}elseif(isset($argsArr['endDate']) && !isset($argsArr['startDate'])){
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"START DATE ISSUE";
			return json_encode($retArr);
		}
		if(isset($argsArr['startDate']) && isset($argsArr['endDate']) && $argsArr['endDate'] !='' && $argsArr['startDate'] !=''){
			$params['startDate']	=	date("Y-m-d", strtotime($argsArr['startDate']));
			$params['endDate']	=	date("Y-m-d", strtotime($argsArr['endDate']));
			$whereConditn	=	" AND a.insertedOn >= '".$argsArr['startDate']." 00:00:00' AND a.insertedOn <= '".$argsArr['endDate']." 23:59:59'";
			$retArr['startDate']	=	$argsArr['startDate'];
			$retArr['endDate']	=	$argsArr['endDate'];
		}else{
			$currentTime	=	date("Y-m-d");
			//~ $currentTime	=	'2017-01-10'; // have to change this 15/02/2017
			$whereConditn	=	" AND a.insertedOn >= '".$currentTime." 00:00:00' AND a.insertedOn <= '".$currentTime." 23:59:59'";
			$retArr['startDate']	=	$currentTime;
			$retArr['endDate']	=	$currentTime;
		}
		$dbObjLocal	=	new DB($this->db['db_local']);
		$sel_fersh_alloc	=	"SELECT * FROM d_jds.tbl_apptLogs AS a JOIN d_jds.tblContractAllocation AS b ON a.parentid = b.contractCode AND CONCAT(a.appointmentDate , ' ' , a.actionTime) = b.actionTime WHERE a.appt_alloc = 1 AND a.cont_allocf = 0 AND followUp = 0 AND a.all_me = 0 AND b.allocationType IN (25,99) ".$whereConditn." ORDER BY a.insertedOn DESC";
		$con_fersh_alloc	=	$dbObjLocal->query($sel_fersh_alloc);
		$num_fersh_alloc	=	$dbObjLocal->numRows($con_fersh_alloc);
		if($num_fersh_alloc > 0){
			while($row 		= 	$dbObjLocal->fetchData($con_fersh_alloc)){
				$retArr['data'][] 	=	$row;
			}
			$retArr['errorCode']	=	0;
			$retArr['errorStatus']	=	"Data Found";
		}else{
			$retArr['errorCode']	=	1;
			$retArr['errorStatus']	=	"Data Not Found";
		}
		return json_encode($retArr);
	}
	
	public function insert_menu_link(){
		$dbObjLocal	=	new DB($this->db['db_local']);
		$inser_qur = "INSERT INTO `d_jds`.`tbl_menu_links` (`menu_name`, `menu_link`) VALUES ('DIY JDRR prospect DATA', '.jdrrPropectData');";
		$inser_qur_con = $dbObjLocal->query($inser_qur);
		if($inser_qur_con){
			echo "Link Inserted";
		}else{
			echo "Link NOT Inserted";
		}

	}
}
?>
