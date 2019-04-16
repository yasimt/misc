<?php
//http://vishalvinodrana.jdsoftware.com/jdbox/services/Complain_troubleshoot_api.php?parentid=PXX22.XX22.110906165241.S2Y2&company_Name=Malti%20Enterprise%20(Closed%20Down)&registeredBy=10026632&registeredby_name=vishal%20rana&data_city=mumbai&action=insert_data&complaint_type=176&complaint_type_name=Regular%20issue&Cs_ticket=123

//http://vishalvinodrana.jdsoftware.com/jdbox/services/Complain_troubleshoot_api.php?parentid=PXX22.XX22.110906165241.S2Y2&company_Name=Malti%20Enterprise%20(Closed%20Down)&resolvedby=10026632&resolvedby_name=vishal%20rana&data_city=mumbai&action=update_data&Cs_ticket=123

/**************************************************************************
FILENAME : Complain_troubleshoot_api.php
PURPOSE  : Create ticket via CS - helpdesk
CREATOR  : Vishal Rana(vishal.rana@justdial.com)
DATE     : 10 Nov ,2016
***************************************************************************/

$param = array_merge($_GET,$_POST);
$i=0;

require_once('../config.php'); 
global $db;



class Complain_class extends DB {
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	
	public function __construct($param){
		
		if(trim($param['parentid']) == '' && $param['action'] == 'insert_data')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "parentid Missing";
			$this->json($result_msg_arr);
			die;
		}
		if(trim($param['company_Name']) == '' && $param['action'] == 'insert_data')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Company Name Missing";
			$this->json($result_msg_arr);
			die;
		}
		
		if(trim($param['data_city']) == '')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "data city Missing";
			$this->json($result_msg_arr);
			die;
		}
		if(trim($param['Cs_ticket']) == '')
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Ticket Id Missing";
			$this->json($result_msg_arr);
			die;
		}
		
		
		$this->parentid = $param['parentid'];
		$this->company_Name = urldecode($param['company_Name']);
		$this->complaint_type = $param['complaint_type'];
		$this->complaint_type_name = urldecode($param['complaint_type_name']);
		$this->registeredBy = $param['registeredBy'];
		$this->registeredby_name = urldecode($param['registeredby_name']);
		$this->data_city = strtolower($param['data_city']);
		$this->action = $param['action'];
		$this->Cs_ticket = $param['Cs_ticket'];
		$this->resolvedby_name = urldecode($param['resolvedby_name']);
		$this->resolvedby = $param['resolvedby'];
		$this->complaint_id = $param['complaint_id'];
		$this->registered_date = $param['registered_date'];
		$this->description = $param['description'];
		
		//$this->dbConnect($dbarr);
		$this->setServers();
	}
	public function processApi(){
			$func = $_REQUEST['action']; 
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else{
				$error = array('status' => "Failed", "msg" => "action not found","error"=>"400");
				$this->json($error);
			}
		}
	function setServers()
	{	
		global $db;
		
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');	
		$this->conn_djds 		= $db[$conn_city]['d_jds']['master'];
		//echo '<pre>';print_r($db[$conn_city]['d_jds']['master']);die;
	}	
		
	private function insert_data(){
	
	if(!($this->complaint_id))
	{
	$sql = "INSERT INTO log_complain_main(`parentid`,`company_Name`,`resolutionflag`,`complain_registration_date`,`registeredBy`,`complain_type`,`complain_source`,`sub_source`,`registeredby_name`,`me_code`,`me_name`,`tme_code`,`tme_name`,`data_city`,`reason`,department_id,complain_resolved_date,resolvedby,resolvedby_name)VALUES('".$this->parentid."','".addslashes($this->company_Name)."','1','".$this->registered_date."','".$this->registeredBy."','".$this->complaint_type."','CS - Helpdesk','".$this->complaint_type_name."','".addslashes($this->registeredby_name)."','--','--','--','--','".$this->data_city."','','',now(),'".$this->resolvedby."','".addslashes($this->resolvedby_name)."')";		
	
	$res = parent::execQuery($sql, $this->conn_djds);
	$this->complaint_id = $this->mysql_insert_id;
	}
	//$this->conn_djds->mysql_insert_id;die
	
	$sql_details = "Insert into log_complain_details(complaintid,updated_date,updatedby,Description,Cs_ticketid) VALUES(".$this->complaint_id.",NOW(),'".addslashes($this->resolvedby_name)."','".addslashes($this->description)."',".$this->Cs_ticket.")";
	
	$res_datails = parent::execQuery($sql_details, $this->conn_djds);
	
		if($res_datails)
			{
				$error = array('status' => "Success", "msg" => "Successfully inserted","error"=>"200","complainid"=> $this->complaint_id);
			}
		else
			{
				$error = array('status' => "Failure", "msg" => "Unable to Insert","error"=>"400");	
			}
	
		$this->json($error);
	}
/*	private function update_data(){
		
	$sql = "update log_complain_main set resolutionflag='1',complain_resolved_date=now(),resolvedby='".$this->resolvedby."' ,resolvedby_name='".$this->resolvedby_name."' where Cs_ticketid='".$this->Cs_ticket."'";	
	
	$res = parent::execQuery($sql, $this->conn_djds); 
		
		if($res)
			{
				$error = array('status' => "Success", "msg" => "Successfully update","error"=>"200");
			}
		else
			{
				$error = array('status' => "Failure", "msg" => "Unable to update","error"=>"400");	
			}
	
		$this->json($error);	
		
	}*/
	private function json($data){
			if($data){				
				echo json_encode($data);
			}
		}		
} 

$api = new Complain_class($param);
//print_r($api);
$api->processApi();

?>
