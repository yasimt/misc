<?php
	class LineageInfo_Model extends Model {
		private $limitVal	=	50;
		//Test comment
		public function __construct() {
			parent::__construct();
		}
		// to get the lineage for prepopulating the popup
		public function getLineage(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);			
		}
		
		//autosuggest for city
		public function getcitylist() {
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);
		} 
		
		//inserting the lineage details
		public function insertlineageDetails() {
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);
		}
		
		public function send_sms($mobileNo, $smstext, $source){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);
		}
		
		public function sendEmail($emailid, $from, $subject, $emailtext, $source){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);
		}
		
		// fetch reportees for managers to accept or reject
		public function fetchreportees(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);			
		}
		
		//update tables on accept or reject
		public function accetRejectRequest(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);			
		}
		
		// if manager not present in list
		public function insertReportDetails(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);	
		}
		
		//on resending OTP
		public function sendOTP(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);	
		}
		
		// verifying the OTP
		public function checkOTP(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);	
		}
		
		// get the count of the number of requests the manager has
		public function countRequest(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);
		}
		
		//get last updated date of employee
		public function checkUpdatedOn(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);	
		}
		
		
		public function insertPenaltyUpdatedOn(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);		
		}
	
		public function getlineagealldata(){
			$resultArr	=	array();
			$resultArr['errorCode']		=	1;
			$resultArr['errorStatus']	=	'Not in use';
			return json_encode($resultArr);	
		}
	}
?>
