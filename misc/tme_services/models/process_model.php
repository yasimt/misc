<?php
class Process_Model extends Model {
	public function __construct() {
		parent::__construct();
	}
	
	public function incentiveProcess() {
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		
		$dbObjSSO		=	new DB($this->db['db_sso']);
		
		$drpTab			=	"DROP TABLE IF EXISTS tbl_emp_incentive_map";
		//$conDrp			=	$dbObjSSO->query($drpTab);
	
		$drpTab2		=	"DROP TABLE IF EXISTS tbl_incentives_emp_compdetails";
		//$conDrp2		=	$dbObjSSO->query($drpTab2);
		
		//~ $delData		=	"DELETE FROM tbl_emp_incentive_map WHERE incentive_month = '2016-11'";
		//~ $conDel			=	$dbObjSSO->query($delData);
		
		//~ $delData2		=	"DELETE FROM tbl_incentives_emp_compdetails WHERE incentive_month = '2016-11'";
		//~ $conDel2			=	$dbObjSSO->query($delData2); die;
		
		$crTable		=	"CREATE TABLE IF NOT EXISTS tbl_emp_incentive_map (empcode varchar(25) default null,empName varchar(100) default null,empType tinyint(4) default null,team_name varchar(10) default null,down_payment double not null default '0',down_payment_incen double not null default '0',cold_call_dp_amt double not null default '0',cold_call_dp_incen double not null default '0',jdrr_amount_normal double not null default '0',jdrr_incentive_normal double not null default '0', jdrr_amount_coldcall double not null default '0', jdrr_incentive_coldcall double not null default '0', ecs_clearence_amount double not null default '0',ecs_clearence_inc double not null default '0',ecs_clearence_cold_call_amt double not null default '0', ecs_clearence_cold_call_inc double not null default '0', incentive_month varchar(30) default '0000-00',emp_city varchar(30) default null, contract_city varchar(30) default null, active_flag tinyint(1), KEY idx_empcode(empcode), KEY idx_empType(empType), KEY idx_team(team_name), KEY idx_incentive_month(incentive_month), KEY idx_emp_city(emp_city), KEY idx_cont_city(contract_city))";
		$conCrTable		=	$dbObjSSO->query($crTable);
		
		$crTable2		=	"CREATE TABLE IF NOT EXISTS tbl_incentives_emp_compdetails (empcode varchar(25) default null,parentid varchar(100) default null,empname  varchar(100) default null,emptype  tinyint(4) default null,team_name varchar(10) default null,compname varchar(100) default null,amount double not null default '0',entry_date datetime default '0000-00-00 00:00:00',appr_date datetime default '0000-00-00 00:00:00',inc_type varchar(25) default null, incentive_month varchar(30) default '0000-00',emp_city varchar(30) default null, contract_city varchar(30) default null, onlineflag tinyint(1),KEY idx_empcode(empcode), KEY idx_parentid(parentid), KEY idx_appr_date(appr_date), KEY idx_incentive_month(incentive_month), KEY idx_inc_type(inc_type), KEY idx_emp_city(emp_city), KEY idx_cont_city(contract_city))";
		$conCrTable		=	$dbObjSSO->query($crTable2);
		
		$monthArr	=	array("2016-11");
		foreach($monthArr as $value) {
			$month			=	$value;
			$dbObjLocal		=	new DB($this->db['db_local']);
			$insStrEmp		=	"";
			$empStr			=	"";
			$empArr			=	array();
			$empcodeArr		=	array();
			
			$dbObjFin		=	new DB($this->db['db_finance']);
			
			$selEmpDate		=	"SELECT instrumentType,instrumentAmount,version,campaignidlist,campaignwisebudget,dealclosebudget,entry_date,tmecode,mecode,entry_doneby,ecsflag,parentid,companyname,finalApprovalDate,service_tax,tdsAmount,data_city FROM contract_payment_details WHERE finalApprovalDate >= '".$month."-01 00:00:00' AND finalApprovalDate <= '".$month."-31 23:59:59' AND finalApprovalDate != '' AND approvalStatus = 1";
			$conEmpDate		=	$dbObjFin->query($selEmpDate);
			$k = 0;
			while($conGetInc	=	$dbObjFin->fetchData($conEmpDate)) {
				$instruAmount	=	(((float)$conGetInc['instrumentAmount']+(float)$conGetInc['tdsAmount']) / (1+$conGetInc['service_tax']));
				if(!isset($empArr[$conGetInc['tmecode']]) && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
					$empArr[$conGetInc['tmecode']]	=	array();
					$empArr[$conGetInc['tmecode']]['normal']	=	array();
					$empArr[$conGetInc['tmecode']]['normal']['downPay']	=	array();
					$empArr[$conGetInc['tmecode']]['normal']['contracts']	=	array();
					$empArr[$conGetInc['tmecode']]['coldcall']	=	array();
					$empArr[$conGetInc['tmecode']]['coldcall']['downPay']	=	array();
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts']	=	array();
					$empArr[$conGetInc['tmecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$conGetInc['tmecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$conGetInc['tmecode']]['jdrr']	=	array();
					$empArr[$conGetInc['tmecode']]['jdrr']['amount']	=	array();
					$empArr[$conGetInc['tmecode']]['jdrr']['tmecode']	=	array();
					$empArr[$conGetInc['tmecode']]['jdrr']['mecode']	=	array();
					$empArr[$conGetInc['tmecode']]['jdrr']['contracts']	=	array();
					$empArr[$conGetInc['tmecode']]['jdrr']['incentive']	=	0;
					$empArr[$conGetInc['tmecode']]['jdrr']['totAmount']	=	0;
					$empArr[$conGetInc['tmecode']]['ecsclear']	=	array();
					$empArr[$conGetInc['tmecode']]['ecsclear']['normal']	=	array();
					$empArr[$conGetInc['tmecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$conGetInc['tmecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$conGetInc['tmecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$conGetInc['tmecode']]['ecsclear']['contracts']	=	array();
					$empArr[$conGetInc['tmecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$conGetInc['tmecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$conGetInc['tmecode']."','";
				}
				if(!isset($empArr[$conGetInc['mecode']]) && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
					$empArr[$conGetInc['mecode']]	=	array();
					$empArr[$conGetInc['mecode']]['normal']	=	array();
					$empArr[$conGetInc['mecode']]['normal']['downPay']	=	array();
					$empArr[$conGetInc['mecode']]['normal']['contracts']	=	array();
					$empArr[$conGetInc['mecode']]['coldcall']	=	array();
					$empArr[$conGetInc['mecode']]['coldcall']['downPay']	=	array();
					$empArr[$conGetInc['mecode']]['coldcall']['contracts']	=	array();
					$empArr[$conGetInc['mecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$conGetInc['mecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$conGetInc['mecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$conGetInc['mecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$conGetInc['mecode']]['jdrr']	=	array();
					$empArr[$conGetInc['mecode']]['jdrr']['amount']	=	array();
					$empArr[$conGetInc['mecode']]['jdrr']['tmecode']	=	array();
					$empArr[$conGetInc['mecode']]['jdrr']['mecode']	=	array();
					$empArr[$conGetInc['mecode']]['jdrr']['contracts']	=	array();
					$empArr[$conGetInc['mecode']]['jdrr']['incentive']	=	0;
					$empArr[$conGetInc['mecode']]['jdrr']['totAmount']	=	0;
					$empArr[$conGetInc['mecode']]['ecsclear']	=	array();
					$empArr[$conGetInc['mecode']]['ecsclear']['normal']	=	array();
					$empArr[$conGetInc['mecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$conGetInc['mecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$conGetInc['mecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$conGetInc['mecode']]['ecsclear']['contracts']	=	array();
					$empArr[$conGetInc['mecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$conGetInc['mecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$conGetInc['mecode']."','";
				}
				if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
					$empArr[$conGetInc['tmecode']]['normal']['downPay']['amount']	=	$empArr[$conGetInc['tmecode']]['normal']['downPay']['amount']+(float)round($instruAmount);
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['onlineFlag']		=	0;
					if($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu') {
						$empArr[$conGetInc['tmecode']]['normal']['downPay']['onlineamount']	=	$empArr[$conGetInc['tmecode']]['normal']['downPay']['onlineamount']+(float)round($instruAmount);
						$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['onlineFlag']	=	1;
					}
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['parentid']		=	$conGetInc['parentid'];
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['compname']		=	$conGetInc['companyname'];
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['amount']		=	(float)round($instruAmount);
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['entry_date']	=	$conGetInc['entry_date'];
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['appr_date']		=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['tmecode']]['normal']['contracts'][$k]['data_city']		=	$conGetInc['data_city'];
				}
				if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
					$empArr[$conGetInc['mecode']]['normal']['downPay']['amount']	=	$empArr[$conGetInc['mecode']]['normal']['downPay']['amount']+(float)round($instruAmount);
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['onlineFlag']		=	0;
					if($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu') {
						$empArr[$conGetInc['mecode']]['normal']['downPay']['onlineamount']	=	$empArr[$conGetInc['mecode']]['normal']['downPay']['onlineamount']+(float)round($instruAmount);
						$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['onlineFlag']	=	1;
					}
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['parentid']		=	$conGetInc['parentid'];
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['compname']		=	$conGetInc['companyname'];
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['amount']		=	(float)round($instruAmount);
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['entry_date']	=	$conGetInc['entry_date'];
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['appr_date']		=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['mecode']]['normal']['contracts'][$k]['data_city']		=	$conGetInc['data_city'];
				}
				if(($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
					$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['amount']	=	$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['amount']+(float)round($instruAmount);
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['onlineFlag']		=	0;
					if($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu') {
						$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['onlineamount']	=	$empArr[$conGetInc['tmecode']]['coldcall']['downPay']['onlineamount']+(float)round($instruAmount);
						$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['onlineFlag']		=	1;
					}
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['parentid']		=	$conGetInc['parentid'];
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['compname']		=	$conGetInc['companyname'];
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['amount']			=	(float)round($instruAmount);
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['entry_date']		=	$conGetInc['entry_date'];
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['appr_date']		=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['tmecode']]['coldcall']['contracts'][$k]['data_city']		=	$conGetInc['data_city'];
				}
				if(($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) && $conGetInc['mecode'] != null && $conGetInc['mecode'] != "") {
					$empArr[$conGetInc['mecode']]['coldcall']['downPay']['amount']	=	$empArr[$conGetInc['mecode']]['coldcall']['downPay']['amount']+(float)round($instruAmount);
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['onlineFlag']		=	0;
					if($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu') {
						$empArr[$conGetInc['mecode']]['coldcall']['downPay']['onlineamount']	=	$empArr[$conGetInc['mecode']]['coldcall']['downPay']['onlineamount']+(float)round($instruAmount);
						$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['onlineFlag']		=	1;
					}
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['parentid']		=	$conGetInc['parentid'];
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['compname']		=	$conGetInc['companyname'];
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['amount']		=	(float)round($instruAmount);
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['entry_date']	=	$conGetInc['entry_date'];
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['appr_date']		=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['mecode']]['coldcall']['contracts'][$k]['data_city']		=	$conGetInc['data_city'];
				}
				
				$expCampaignList	=	explode(",",$conGetInc['campaignidlist']);
				$expCampigBudgList	=	explode(",",$conGetInc['campaignwisebudget']);
				$arrSendData	=	array();
				foreach($expCampigBudgList as $key=>$value) {
					$budgetVal	=	explode("-",$value);
					$arrSendData[$budgetVal[0]]	=	$budgetVal[1];
				}
				
				if(in_array(22,$expCampaignList)) {
					$empArr[$conGetInc['tmecode']]['jdrr']['amount'][$k]	=	(float)round($arrSendData[22]);
					$empArr[$conGetInc['tmecode']]['jdrr']['contracts'][$k]['parentid']	=	$conGetInc['parentid'];
					$empArr[$conGetInc['tmecode']]['jdrr']['contracts'][$k]['compname']	=	$conGetInc['companyname'];
					$empArr[$conGetInc['tmecode']]['jdrr']['tmecode'][$k]	=	$conGetInc['tmecode'];
					$empArr[$conGetInc['tmecode']]['jdrr']['mecode'][$k]	=	$conGetInc['mecode'];
					$empArr[$conGetInc['tmecode']]['jdrr']['entry_date'][$k]	=	$conGetInc['entry_date'];
					$empArr[$conGetInc['tmecode']]['jdrr']['appr_date'][$k]	=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['tmecode']]['jdrr']['data_city'][$k]	=	$conGetInc['data_city'];
					$empArr[$conGetInc['tmecode']]['jdrrSet']	=	1;
					
					$empArr[$conGetInc['mecode']]['jdrr']['amount'][$k]	=	(float)round($arrSendData[22]);
					$empArr[$conGetInc['mecode']]['jdrr']['contracts'][$k]['parentid']	=	$conGetInc['parentid'];
					$empArr[$conGetInc['mecode']]['jdrr']['contracts'][$k]['compname']	=	$conGetInc['companyname'];
					$empArr[$conGetInc['mecode']]['jdrr']['tmecode'][$k]	=	$conGetInc['tmecode'];
					$empArr[$conGetInc['mecode']]['jdrr']['mecode'][$k]	=	$conGetInc['mecode'];
					$empArr[$conGetInc['mecode']]['jdrr']['entry_date'][$k]	=	$conGetInc['entry_date'];
					$empArr[$conGetInc['mecode']]['jdrr']['appr_date'][$k]	=	$conGetInc['finalApprovalDate'];
					$empArr[$conGetInc['mecode']]['jdrr']['data_city'][$k]	=	$conGetInc['data_city'];
					$empArr[$conGetInc['mecode']]['jdrrSet']	=	1;
				}
				$k++;
			}
			
			/******** ECS Clearance Calculation Start *****************/
			$ecsClearData	=	"SELECT a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_ecs.ecs_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59'";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			$l = 0;
			while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
				$servTax	=	$resClearData['service_tax'];
				$amount		=	$resClearData['billAmount'];
				$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
				if(!isset($empArr[$resClearData['tmecode']]) && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
					$empArr[$resClearData['tmecode']]	=	array();
					$empArr[$resClearData['tmecode']]['normal']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['downPay']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['tmecode']]['jdrr']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['amount']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['tmecode']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['mecode']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['incentive']	=	0;
					$empArr[$resClearData['tmecode']]['jdrr']['totAmount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$resClearData['tmecode']."','";
				}
				if(!isset($empArr[$resClearData['mecode']]) && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
					$empArr[$resClearData['mecode']]	=	array();
					$empArr[$resClearData['mecode']]['normal']	=	array();
					$empArr[$resClearData['mecode']]['normal']['downPay']	=	array();
					$empArr[$resClearData['mecode']]['normal']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']['downPay']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$resClearData['mecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['mecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$resClearData['mecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['mecode']]['jdrr']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['amount']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['tmecode']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['mecode']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['incentive']	=	0;
					$empArr[$resClearData['mecode']]['jdrr']['totAmount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$resClearData['mecode']."','";
				}
				if($resClearData['tmecode'] != null && $resClearData['tmecode'] != "" && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount']	=	$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount'] + $actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['amount']		=	$actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['entry_date']	=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if($resClearData['mecode'] != null && $resClearData['mecode'] != "" && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount']	=	$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount'] + $actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['amount']		=	$actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['entry_date']	=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if(($resClearData['mecode'] == "" || $resClearData['mecode'] == null) && $resClearData['tmecode'] != null && $resClearData['tmecode'] != "") {
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount']	=	$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount'] + $actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['amount']	=	$actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['entry_date']=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if(($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) && $resClearData['mecode'] != null && $resClearData['mecode'] != "") {
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount']	=	$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount'] + $actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['amount']	=	$actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['entry_date']=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['data_city']	=	$resClearData['data_city'];
				}
				$l++;
			}
			
			$ecsClearData	=	"SELECT a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_si.si_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59'";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
				$servTax	=	$resClearData['service_tax'];
				$amount		=	$resClearData['billAmount'];
				$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
				if(!isset($empArr[$resClearData['tmecode']]) && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
					$empArr[$resClearData['tmecode']]	=	array();
					$empArr[$resClearData['tmecode']]['normal']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['downPay']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']	=	array();
					$empArr[$resClearData['tmecode']]['coldcall']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['tmecode']]['jdrr']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['amount']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['tmecode']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['mecode']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['jdrr']['incentive']	=	0;
					$empArr[$resClearData['tmecode']]['jdrr']['totAmount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$resClearData['tmecode']."','";
				}
				if(!isset($empArr[$resClearData['mecode']]) && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
					$empArr[$resClearData['mecode']]	=	array();
					$empArr[$resClearData['mecode']]['normal']	=	array();
					$empArr[$resClearData['mecode']]['normal']['downPay']	=	array();
					$empArr[$resClearData['mecode']]['normal']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']['downPay']	=	array();
					$empArr[$resClearData['mecode']]['coldcall']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['normal']['downPay']['amount']	=	0;
					$empArr[$resClearData['mecode']]['normal']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['mecode']]['coldcall']['downPay']['amount']	=	0;
					$empArr[$resClearData['mecode']]['coldcall']['downPay']['onlineamount']	=	0;
					$empArr[$resClearData['mecode']]['jdrr']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['amount']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['tmecode']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['mecode']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['jdrr']['incentive']	=	0;
					$empArr[$resClearData['mecode']]['jdrr']['totAmount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount']	=	0;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal']	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall']	=	array();
					$empStr	.=	$resClearData['mecode']."','";
				}
				if($resClearData['tmecode'] != null && $resClearData['tmecode'] != "" && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]	=	array();
					$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount']	=	$empArr[$resClearData['tmecode']]['ecsclear']['normal']['amount'] + $actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['amount']		=	$actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['entry_date']	=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['normal'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if($resClearData['mecode'] != null && $resClearData['mecode'] != "" && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]	=	array();
					$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount']	=	$empArr[$resClearData['mecode']]['ecsclear']['normal']['amount'] + $actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['amount']		=	$actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['entry_date']	=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['normal'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if(($resClearData['mecode'] == "" || $resClearData['mecode'] == null) && $resClearData['tmecode'] != null && $resClearData['tmecode'] != "") {
					$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount']	=	$empArr[$resClearData['tmecode']]['ecsclear']['coldcall']['amount'] + $actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['amount']	=	$actualAmount;
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['entry_date']=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['tmecode']]['ecsclear']['contracts']['coldcall'][$l]['data_city']	=	$resClearData['data_city'];
				}
				if(($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) && $resClearData['mecode'] != null && $resClearData['mecode'] != "") {
					$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount']	=	$empArr[$resClearData['mecode']]['ecsclear']['coldcall']['amount'] + $actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['parentid']	=	$resClearData['parentid'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['compname']	=	$resClearData['companyname'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['amount']	=	$actualAmount;
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['entry_date']=	$resClearData['billGenerateDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['appr_date']	=	$resClearData['billResponseDate'];
					$empArr[$resClearData['mecode']]['ecsclear']['contracts']['coldcall'][$l]['data_city']	=	$resClearData['data_city'];
				}
				$l++;
			}
			
			$dbObjIDC		=	new DB($this->db['db_idc_login']);
			$selEmpInfo	=	"SELECT mktEmpCode,empType,city,allocId FROM tbl_loginDetails WHERE mktEmpCode IN ('".substr($empStr,0,-3)."')";
			$conSelEmpInfo	=	$dbObjIDC->query($selEmpInfo);
			while($resEmpInfo	=	$dbObjIDC->fetchData($conSelEmpInfo)) {
				$empArr[$resEmpInfo['mktEmpCode']]['emptype']	=	$resEmpInfo['empType'];
				$empArr[$resEmpInfo['mktEmpCode']]['city']		=	$resEmpInfo['city'];
				$empArr[$resEmpInfo['mktEmpCode']]['team_name']	=	$resEmpInfo['allocId'];
			}
			
			$selEmpInfoSSO	=	"SELECT empname,empcode,city,resign_flag,delete_flag,city FROM tbl_employee_info WHERE empcode IN ('".substr($empStr,0,-3)."')";
			$conSelEmpInfoSSO	=	$dbObjSSO->query($selEmpInfoSSO);
			while($resEmpInfoSSO	=	$dbObjSSO->fetchData($conSelEmpInfoSSO)) {
				$empArr[$resEmpInfoSSO['empcode']]['empname']	=	$resEmpInfoSSO['empname'];
				if($resEmpInfoSSO['resign_flag'] == 1 && $resEmpInfoSSO['delete_flag'] == 0) {
					$empArr[$resEmpInfoSSO['empcode']]['active']	=	1;
				} else {
					$empArr[$resEmpInfoSSO['empcode']]['active']	=		0;
				}
			}
			
			foreach($empArr as $key=>$value) {
				if($value['emptype']	==	'13') {
					$empArr[$key]['normal']['downPay']['incentive']	=	$empArr[$key]['normal']['downPay']['amount']*0.08;
					$empArr[$key]['ecsclear']['normal']['incentive']	=	$empArr[$key]['ecsclear']['normal']['amount']*0.08;
				} else {
					if($value['team_name'] == "SJ") {
						$empArr[$key]['normal']['downPay']['incentive']	=	$empArr[$key]['normal']['downPay']['amount']*0.025;
						$empArr[$key]['ecsclear']['normal']['incentive']	=	$empArr[$key]['ecsclear']['normal']['amount']*0.03;
					} else if($value['team_name'] == "RD"){
						$empArr[$key]['normal']['downPay']['incentive']	=	$empArr[$key]['normal']['downPay']['amount']*0.01;
						$empArr[$key]['ecsclear']['normal']['incentive']	=	$empArr[$key]['ecsclear']['normal']['amount']*0.01;
					} else {
						$empArr[$key]['normal']['downPay']['incentive']	=	$empArr[$key]['normal']['downPay']['amount']*0.04;
						$empArr[$key]['ecsclear']['normal']['incentive']	=	$empArr[$key]['ecsclear']['normal']['amount']*0.05;
					}
				}
				if($value['emptype']	==	'3' || $value['emptype']	==	'13') {
					$empArr[$key]['normal']['downPay']['incentive']	=	$empArr[$key]['normal']['downPay']['incentive']+($empArr[$key]['normal']['downPay']['onlineamount']*0.01);
				}
				
				/******************** Cold Call Calculation *************************/
				if($value['emptype']	==	'5') {
					if($value['team_name'] == "SJ") {
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.04;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.045;
					} else if($value['team_name'] == "RD"){
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.01;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.01;
					} else {
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.055;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.065;
					}
				} else if($value['emptype']	==	'3') {
					if($value['team_name'] == "SJ") {
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.04;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.045;
					} else if($value['team_name'] == "RD"){
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.04;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.045;
					} else {
						$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.04;
						$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.045;
					}
				} else if($value['emptype']	==	'13') {
					$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['amount']*0.08;
					$empArr[$key]['ecsclear']['coldcall']['incentive']	=	$empArr[$key]['ecsclear']['coldcall']['amount']*0.08;
				}
				if($value['emptype']	==	'3' || $value['emptype']	==	'13') {
					$empArr[$key]['coldcall']['downPay']['incentive']	=	$empArr[$key]['coldcall']['downPay']['incentive']+($empArr[$key]['coldcall']['downPay']['onlineamount']*0.01);
				}
				/******************** JDRR Calculation *****************************/
				foreach($empArr[$key]['jdrr']['amount'] as $key2=>$value2) {
					if($empArr[$key]['jdrr']['tmecode'][$key2] != "" && $empArr[$key]['jdrr']['mecode'][$key2] != "") {
						$empArr[$key]['jdrrnormal']['incentive']	=	$empArr[$key]['jdrrnormal']['incentive']+($value2*0.04);
						$empArr[$key]['jdrrnormal']['totAmount']		=	$empArr[$key]['jdrrnormal']['totAmount']+($value2);
					} else if(($empArr[$key]['jdrr']['tmecode'][$key2] == "" && $empArr[$key]['jdrr']['mecode'][$key2] != "") || ($empArr[$key]['jdrr']['mecode'][$key2] == "" && $empArr[$key]['jdrr']['tmecode'][$key2] != "")) {
						$empArr[$key]['jdrrcoldcall']['incentive']	=	$empArr[$key]['jdrrcoldcall']['incentive']+($value2*0.08);
						$empArr[$key]['jdrrcoldcall']['totAmount']		=	$empArr[$key]['jdrrcoldcall']['totAmount']+($value2);
					}
				}
			}
			
			$insStr		=	"";
			$compInsStr	=	"";
			foreach($empArr as $keyEmp=>$valueInc) {
				//~ //$finalArr['main'][$i]['data']	=	$valueInc;
				//~ //$finalArr['main'][$i]['empcode']	=	$keyEmp;
				$insStr		.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueInc['normal']['downPay']['amount']."','".$valueInc['normal']['downPay']['incentive']."','".$valueInc['coldcall']['downPay']['amount']."','".$valueInc['coldcall']['downPay']['incentive']."','".$valueInc['jdrrnormal']['totAmount']."','".$valueInc['jdrrnormal']['incentive']."','".$valueInc['jdrrcoldcall']['totAmount']."','".$valueInc['jdrrcoldcall']['incentive']."','".$valueInc['ecsclear']['normal']['amount']."','".$valueInc['ecsclear']['normal']['incentive']."','".$valueInc['ecsclear']['coldcall']['amount']."','".$valueInc['ecsclear']['coldcall']['incentive']."','".$month."','".$valueInc['city']."','".SERVER_CITY."','".$valueInc['active']."'),";
				
				if(count($valueInc['normal']['contracts']) > 0) {
					foreach($valueInc['normal']['contracts'] as $keyCont=>$valueCont) {
						if(count($valueCont) > 0) {
							$compInsStr	.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueCont['parentid']."','".addslashes(stripslashes($valueCont['compname']))."','".$valueCont['amount']."','".$valueCont['entry_date']."','".$valueCont['appr_date']."','downpayment','".$month."','".$valueInc['city']."','".$valueCont['data_city']."','".$valueCont['onlineFlag']."'),";
						}
					}
				}
				if(count($valueInc['coldcall']['contracts']) > 0) {
					foreach($valueInc['coldcall']['contracts'] as $keyCont=>$valueCont) {
						if(count($valueCont) > 0) {
							$compInsStr	.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueCont['parentid']."','".addslashes(stripslashes($valueCont['compname']))."','".$valueCont['amount']."','".$valueCont['entry_date']."','".$valueCont['appr_date']."','coldcall','".$month."','".$valueInc['city']."','".$valueCont['data_city']."','".$valueCont['onlineFlag']."'),";
						}
					}
				}
				if(count($valueInc['jdrr']['contracts']) > 0) {
					foreach($valueInc['jdrr']['contracts'] as $keyCont=>$valueCont) {
						if(count($valueCont) > 0) {
							if($valueInc['jdrr']['tmecode'][$keyCont] != "" && $valueInc['jdrr']['mecode'][$keyCont] != "") {
								$tagValue	=	"jdrrnormal";
							} else if(($valueInc['jdrr']['tmecode'][$keyCont] != "" && $valueInc['jdrr']['mecode'][$keyCont] == "") || ($valueInc['jdrr']['tmecode'][$keyCont] == "" && $valueInc['jdrr']['mecode'][$keyCont] != "")){
								$tagValue	=	"jdrrcoldcall";
							}
							$compInsStr	.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueCont['parentid']."','".addslashes(stripslashes($valueCont['compname']))."','".$valueInc['jdrr']['amount'][$keyCont]."','".$valueInc['jdrr']['entry_date'][$keyCont]."','".$valueInc['jdrr']['appr_date'][$keyCont]."','".$tagValue."','".$month."','".$valueInc['city']."','".$valueCont['data_city']."','".$valueCont['onlineFlag']."'),";
						}
					}
				}
				if(count($valueInc['ecsclear']['contracts']['normal']) > 0) {
					foreach($valueInc['ecsclear']['contracts']['normal'] as $keyCont=>$valueCont) {
						if(count($valueCont) > 0) {
							$compInsStr	.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueCont['parentid']."','".addslashes(stripslashes($valueCont['compname']))."','".$valueCont['amount']."','".$valueCont['entry_date']."','".$valueCont['appr_date']."','ecscleardp','".$month."','".$valueInc['city']."','".$valueCont['data_city']."','".$valueCont['onlineFlag']."'),";
						}
					}
				}
				if(count($valueInc['ecsclear']['contracts']['coldcall']) > 0) {
					foreach($valueInc['ecsclear']['contracts']['coldcall'] as $keyCont=>$valueCont) {
						if(count($valueCont) > 0) {
							$compInsStr	.=	"('".$keyEmp."','".addslashes(stripslashes($valueInc['empname']))."','".$valueInc['emptype']."','".$valueInc['team_name']."','".$valueCont['parentid']."','".addslashes(stripslashes($valueCont['compname']))."','".$valueCont['amount']."','".$valueCont['entry_date']."','".$valueCont['appr_date']."','ecsclearcoldcall','".$month."','".$valueInc['city']."','".$valueCont['data_city']."','".$valueCont['onlineFlag']."'),";
						}
					}
				}
				$i++;
			}
			
			$insertTab	=	"INSERT INTO tbl_emp_incentive_map (empcode,empName,empType,team_name,down_payment,down_payment_incen,cold_call_dp_amt,cold_call_dp_incen,jdrr_amount_normal,jdrr_incentive_normal,jdrr_amount_coldcall,jdrr_incentive_coldcall,ecs_clearence_amount,ecs_clearence_inc,ecs_clearence_cold_call_amt,ecs_clearence_cold_call_inc,incentive_month,emp_city,contract_city,active_flag) VALUES ".substr($insStr,0,-1);
			
			$conDrp2			=	$dbObjSSO->query($insertTab);
			
			$insertTab2	=	"INSERT INTO tbl_incentives_emp_compdetails (empcode,empname,emptype,team_name,parentid,compname,amount,entry_date,appr_date,inc_type,incentive_month,emp_city,contract_city,onlineflag) VALUES ".substr($compInsStr,0,-1);
			$conDrp2			=	$dbObjSSO->query($insertTab2);
			echo $month." Done Successfully<hr>";
		}
	}
	
	public function processDataNew() {
		ini_set('memory_limit', '-1');
		//die("Stopped");
		$dbObjSSO		=	new DB($this->db['db_sso']);
		
		$dr	=	"DROP TABLE tbl_contract_incentive_back";
		//$con	=	$dbObjSSO->query($dr);
		
		//~ $ins	=	"INSERT INTO db_accounts.tbl_clash_lock SET moduleid = '11',modulename = 'Me', added_on = '2017-02-09 17:28:09',active_flag='0'";
		//~ $con	=	$dbObjSSO->query($ins); die;
		
		$updateFinalCity	=	"UPDATE tbl_contract_incentive_back SET final_city=IF(emp_city<>'',emp_city,IF(module_city<>'',module_city,IF(data_city<>'',data_city,''))) WHERE payment_type IN ('dp','jdrr','omni') AND incentive_month IN ('2016-12','2017-01');";
		//$conUpd	=	$dbObjSSO->query($updateFinalCity); die('Update Over');
		
		$alterTable	=	"ALTER TABLE tbl_contract_incentive ADD COLUMN approval_date datetime default '0000-00-00 00:00:00', ADD INDEX idx_appr_date(approval_date)";
		//$conAlterDate	=	$dbObjSSO->query($alterTable); die('Update Over');
		
		$delEntry	=	"DELETE FROM tbl_contract_incentive WHERE payment_type= 'ecs'";
		//$conDelEntry	=	$dbObjSSO->query($delEntry); die('Delete Over');
		
		$processCity	=	"remote";
		
		//~ $crTabLike		=	"CREATE TABLE tbl_contract_incentive_back LIKE tbl_contract_incentive";
		//~ $conCrTabLike	=	$dbObjSSO->query($crTabLike);
		
		//~ $insTabLike		=	"INSERT INTO tbl_contract_incentive_back SELECT * FROM tbl_contract_incentive";
		//~ $conInsTabLike	=	$dbObjSSO->query($insTabLike); die('done');
		
		//~ $alterTab	=	"ALTER TABLE tbl_contract_incentive_back ADD COLUMN clashNew tinyint(1) DEFAULT 0";
		//~ $conAlterTab	=	$dbObjSSO->query($alterTab); die("Done");
		
		$crTable		=	"CREATE TABLE IF NOT EXISTS `tbl_contract_incentive_back` (`parentid` VARCHAR(100) DEFAULT NULL,`payment_type` VARCHAR(10) DEFAULT NULL,`payment_id` VARCHAR(100) DEFAULT NULL,`payment_source` VARCHAR(10) DEFAULT NULL,`version` VARCHAR(10) DEFAULT NULL,`amount` DOUBLE NOT NULL DEFAULT '0',`incentive_amount` DOUBLE NOT NULL DEFAULT '0',`incentive_amount_online` DOUBLE NOT NULL DEFAULT '0',`entry_date` DATETIME DEFAULT NULL,`approval_date` DATETIME DEFAULT NULL,`empcode` VARCHAR(25) DEFAULT NULL,`emptype` VARCHAR(10) DEFAULT NULL,`empname` VARCHAR(100) DEFAULT NULL,`team_name` VARCHAR(10) DEFAULT NULL,`data_city` VARCHAR(25) DEFAULT NULL,`module_city` VARCHAR(25) DEFAULT NULL,`emp_city` VARCHAR(25) DEFAULT NULL,`incentive_month` VARCHAR(30) DEFAULT '0000-00',`active_flag` TINYINT(1) DEFAULT NULL,`compname` VARCHAR(255) DEFAULT NULL,`coldcall_tag` TINYINT(1) DEFAULT '0',`final_city` VARCHAR(255) NOT NULL DEFAULT '',`process_city` VARCHAR(255) NOT NULL DEFAULT '',`foundIn` VARCHAR(255) NOT NULL DEFAULT '',`server_emp_city` VARCHAR(100) NOT NULL DEFAULT '',`clash_flag` TINYINT(1) DEFAULT '0',`clash_perc` INT(11) DEFAULT '100',`clashed_on` DATETIME DEFAULT '0000-00-00 00:00:00',`clashid` VARCHAR(100) DEFAULT NULL,`clash_emp_type` VARCHAR(25) DEFAULT NULL,`clashNew` TINYINT(1) DEFAULT '0', `main_remTag` TINYINT(1) DEFAULT '0',KEY `idx_parid` (`parentid`),KEY `idx_payid` (`payment_id`),KEY `idx_empcode` (`empcode`),KEY `idx_team` (`team_name`),KEY `idx_incentive_month` (`incentive_month`),KEY `idx_emp_city` (`emp_city`),KEY `idx_module_city` (`module_city`),KEY `idx_pay_type` (`payment_type`),KEY `idx_data_city` (`data_city`),KEY `idx_final_city` (`final_city`),KEY `idx_appr_date` (`approval_date`),KEY `idx_foundIn` (`foundIn`),KEY `idx_server_emp_city` (`server_emp_city`),KEY `clash_perc` (`clash_perc`),KEY `clashed_on` (`clashed_on`),KEY `clashid` (`clashid`),KEY `clash_emp_type` (`clash_emp_type`)) ENGINE=MYISAM DEFAULT CHARSET=latin1";
		$conCrTable		=	$dbObjSSO->query($crTable);
		
		$empArr			=	array();
		$empStr			=	"";
		
		$selEmpInfoSSO	=	"SELECT empname as empname,empcode,city,section,resign_flag,delete_flag FROM db_hr.tbl_employee_info";
		$conSelEmpInfoSSO	=	$dbObjSSO->query($selEmpInfoSSO);
		while($resEmpInfoSSO	=	$dbObjSSO->fetchData($conSelEmpInfoSSO)) {
			$empArr[trim($resEmpInfoSSO['empcode'])]['empname']	=	$resEmpInfoSSO['empname'];
			$empArr[trim($resEmpInfoSSO['empcode'])]['maincity']	=	$resEmpInfoSSO['city'];
			if($resEmpInfoSSO['resign_flag'] == 1 && $resEmpInfoSSO['delete_flag'] == 0) {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	1;
			} else {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	0;
			}
			$tmeOpArr		=	array("tme support","tme operations");
			$meOpArr		=	array("bde support","bde operations");
			$jdaOpArr		=	array("jda support","jda operations");
			if(strtolower($resEmpInfoSSO['section'])	==	'tme support' || strtolower($resEmpInfoSSO['section'])	==	'tme operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	5;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'bde support' || strtolower($resEmpInfoSSO['section'])	==	'bde operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	3;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'jda support' || strtolower($resEmpInfoSSO['section'])	==	'jda operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	13;
			}
			$empArr[trim($resEmpInfoSSO['empcode'])]['team_name']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['city']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['empcode']	=	trim($resEmpInfoSSO['empcode']);
			$empStr	.=	trim($resEmpInfoSSO['empcode'])."','";
		}
		
		$cityFinderArr	=	array("remote","mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
		foreach($cityFinderArr as $key=>$value) {
			$dbObjLocLoop		=	new DB($this->db['db_local_'.$value]);
			$selEmpInfo	=	"SELECT mktEmpCode,empType,city,allocId,city_type FROM mktgEmpMaster WHERE mktEmpCode != '' AND mktEmpCode IS NOT NULL and mktEmpCode IN ('".substr($empStr,0,-3)."')";
			$conSelEmpInfo	=	$dbObjLocLoop->query($selEmpInfo);
			while($resEmpInfo	=	$dbObjLocLoop->fetchData($conSelEmpInfo)) {
				if(!isset($empArr[trim($resEmpInfo['mktEmpCode'])]) || (isset($empArr[trim($resEmpInfo['mktEmpCode'])]) && $empArr[trim($resEmpInfo['mktEmpCode'])]['city_type'] == 0)) {
					$empArr[trim($resEmpInfo['mktEmpCode'])]['city']		=	$resEmpInfo['city'];
					$empArr[trim($resEmpInfo['mktEmpCode'])]['team_name']	=	$resEmpInfo['allocId'];
					$empArr[trim($resEmpInfo['mktEmpCode'])]['server_city']	=	$value;
					$empArr[trim($resEmpInfo['mktEmpCode'])]['city_type']	=	$resEmpInfo['city_type'];
				}
			}
		}
		//~ echo "<pre>";
		//~ print_r($empArr);
		//~ die;
		//$monthArr	=	array("2016-09","2016-10");
		$monthArr	=	array("2016-07");
		foreach($monthArr as $value) {
			$dbObjLocal		=	new DB($this->db['db_local']);
			$month			=	$value;
			$insStrEmp		=	"";
			$contArr		=	array();
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			
			$maincityArr	=	array("mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
			
			$selEmpDate		=	"SELECT instrumentType,instrumentAmount,version,campaignidlist,campaignwisebudget,dealclosebudget,entry_date,tmecode,mecode,entry_doneby,ecsflag,parentid,companyname,finalApprovalDate,service_tax,tdsAmount,data_city,instrumentid FROM contract_payment_details WHERE finalApprovalDate >= '".$month."-01 00:00:00' AND finalApprovalDate <= '".$month."-31 23:59:59' AND finalApprovalDate != '' AND approvalStatus = 1 AND parentid != ''";
			$conEmpDate		=	$dbObjFin->query($selEmpDate);
			$k = 0;
			while($conGetInc	=	$dbObjFin->fetchData($conEmpDate)) {
				$instruAmount	=	(((float)$conGetInc['instrumentAmount']+(float)$conGetInc['tdsAmount']) / (1+$conGetInc['service_tax']));
				if(!isset($contArr[$conGetInc['parentid']])) {
					$contArr[$conGetInc['parentid']]	=	array();
				}
				if(!isset($contArr[$conGetInc['parentid']]['dp'])) {
					$contArr[$conGetInc['parentid']]['dp']	=	array();
				}
				$contArr[$conGetInc['parentid']]['dp'][$k]	=	array();
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumentamount']	=	$instruAmount;
				$contArr[$conGetInc['parentid']]['dp'][$k]['version']	=	$conGetInc['version'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['entry_date']	=	$conGetInc['entry_date'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['meGotCode']		=	$conGetInc['mecode'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['companyname']	=	$conGetInc['companyname'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['data_city']	=	$conGetInc['data_city'];
				$taggedEmpArr	=	array();
				
				$taggedEmpArr['tmecode']	=	$conGetInc['tmecode'];
				$taggedEmpArr['mecode']		=	$conGetInc['mecode'];
				
				$m = 0;
				foreach($taggedEmpArr as $key=>$value) {
					if(isset($empArr[$value])) {
						if(isset($empArr[$value]) && $empArr[$value]['emptype'] == 5) {
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
							if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.025;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.025;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.035;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.035;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.01;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.01;
								} else {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.04;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.04;
								}
							} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.04;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.04;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.05;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.05;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.01;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.01;
								} else {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.055;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.055;
								}
							}
							if($month == '2016-11') {
								if($empArr[$value]['maincity'] != "" && (strtolower(trim($empArr[$value]['maincity'])) ==	'delhi' || strtolower(trim($empArr[$value]['maincity'])) ==	'noida') && ($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']	=	$instruAmount*0.01;
								}
							} else if($month == '2016-12' || $month == '2017-01') {
								$cityArrExt	=	array("chandigarh","jaipur","hyderabad","delhi","kolkata","noida");
								if($empArr[$value]['maincity'] != "" && in_array(strtolower(trim($empArr[$value]['maincity'])),$cityArrExt) && ($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']	=	$instruAmount*0.01;
								}
							}
						} 
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
							$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
							if($empArr[$value]['emptype'] == '13') {
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.08;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.08;
								} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.08;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.08;
								}
							} else {
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									if($empArr[$conGetInc['tmecode']]['team_name']	==	"SJ") {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										if($empArr[$conGetInc['tmecode']]['city_type'] == 1) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.025;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.025;
										} else if($empArr[$conGetInc['tmecode']]['city_type'] == 2) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.035;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.035;
										}
									} else if($empArr[$conGetInc['tmecode']]['team_name']	==	"RD") {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.01;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.01;
									} else {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
									}
								} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.04;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
								}
							}
							if(($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']	=	$instruAmount*0.01;
							}
						}
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
							if($key == 'tmecode') {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
								$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	0;
								} else if($conGetInc['tmecode'] != '' && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
								$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
								$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	0;
								} else if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	0;
								}
							}
						}
					} else {
						if($key == 'tmecode') {
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	0;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]	=	0;
							if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	0;
							} else if($conGetInc['tmecode'] != '' && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	0;
							}
						} else if($key == 'mecode') {
							$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	0;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]=	0;
							if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	0;
							} else if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	0;
							}
						}
					}
					$m++;
				}
				
				$expCampaignList	=	explode(",",$conGetInc['campaignidlist']);
				$expCampigBudgList	=	explode(",",$conGetInc['campaignwisebudget']);
				$arrSendData	=	array();
				foreach($expCampigBudgList as $key=>$value) {
					$budgetVal	=	explode("-",$value);
					$arrSendData[$budgetVal[0]]	=	$budgetVal[1];
				}
				//For JDRR
				if(in_array(22,$expCampaignList)) {
					$selInstruJdrrApportion	=	"SELECT app_amount FROM payment_snapshot WHERE instrumentid = '".$conGetInc['instrumentid']."' AND campaignId = '22'";
					$conInstJdrrApp			=	$dbObjFin->query($selInstruJdrrApportion);
					$numRowsJdrr			=	$dbObjFin->numRows($conInstJdrrApp);
					if($numRowsJdrr > 0) {
						$resInstJdrrApp		=	$dbObjFin->fetchData($conInstJdrrApp);
						if(!isset($contArr[$conGetInc['parentid']]['jdrr'])) {
							$contArr[$conGetInc['parentid']]['jdrr']	=	array();
						}
						$contArr[$conGetInc['parentid']]['jdrr'][$k]	=	array();
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumentamount']	=	$instruAmount;
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['version']	=	$conGetInc['version'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['entry_date']	=	$conGetInc['entry_date'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['meGotCode']	=	$conGetInc['mecode'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['companyname']	=	$conGetInc['companyname'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['data_city']	=	$conGetInc['data_city'];
						$jdrrAmount	=	$resInstJdrrApp['app_amount'];
						if($resInstJdrrApp['app_amount'] > $instruAmount) {
							$jdrrAmount	=	$instruAmount;
						}
						$m = 0;
						foreach($taggedEmpArr as $key=>$value) {
							if(isset($empArr[$value])) {
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInTme'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	(float)round($jdrrAmount) * 0.04;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme']['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	(float)round($jdrrAmount) * 0.08;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										}
									}
								} 
							
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInMe'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	(float)round($jdrrAmount) * 0.04;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']."--";
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	(float)round($jdrrAmount) * 0.08;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
											}	
										}
									}
								} 
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
									if($key == 'tmecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
											$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
												}	
											}
										}
									} else if($key == 'mecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
											$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
											$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
											if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	0;
												}
											}
										}
									}
								}
							} else {
								if($key == 'tmecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmename'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecity'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeTeamName'][$m]=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmemaincity'][$m]=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInTme'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityTme'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeTme'][$m]	=	0;
										if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
											}	
										}
									}
								} else if($key == 'mecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mename'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecity'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meTeamName'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInMe'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityMe'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeMe'][$m]	=	0;
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	0;
											}
										}
									}
								}
							}
							$m++;
						}
					}
				}
				//~ //For Omni
				if(in_array(72,$expCampaignList) || in_array(73,$expCampaignList)) {
					$selInstruOmniApportion	=	"SELECT SUM(app_amount) as app_amount FROM payment_snapshot WHERE instrumentid = '".$conGetInc['instrumentid']."' AND campaignId IN (72,73) GROUP BY instrumentid";
					$conInstOmniApp			=	$dbObjFin->query($selInstruOmniApportion);
					$numRowsOmni			=	$dbObjFin->numRows($conInstOmniApp);
					if($numRowsOmni > 0) {
						$resInstOmniApp		=	$dbObjFin->fetchData($conInstOmniApp);
						if(!isset($contArr[$conGetInc['parentid']]['omni'])) {
							$contArr[$conGetInc['parentid']]['omni']	=	array();
						}
						$contArr[$conGetInc['parentid']]['omni'][$k]	=	array();
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumentamount']	=	$instruAmount;
						$contArr[$conGetInc['parentid']]['omni'][$k]['version']	=	$conGetInc['version'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['entry_date']	=	$conGetInc['entry_date'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['meGotCode']	=	$conGetInc['mecode'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['companyname']	=	$conGetInc['companyname'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['data_city']	=	$conGetInc['data_city'];
						$m = 0;
						foreach($taggedEmpArr as $key=>$value) {
							if(isset($empArr[$value])) {
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	(float)round($omniAmount) * 0.05;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {	
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	(float)round($omniAmount) * 0.10;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										}
									}
								}
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['tmecode'] != "" && $conGetInc['mecode'] != null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
											if($empArr[$value]['emptype'] == 13) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	(float)round($omniAmount) * 0.08;	
											} else {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	(float)round($omniAmount) * 0.05;	
											}
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);	
											if($empArr[$value]['emptype'] == 13) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	(float)round($omniAmount) * 0.08;	
											} else {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	(float)round($omniAmount) * 0.10;	
											}
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
											}	
										}
									}
								}
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
									if($key == 'tmecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]		=	$key;
											$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
												$omniAmount	=	$resInstOmniApp['app_amount'];
												if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']) {
													$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
												$omniAmount	=	$resInstOmniApp['app_amount'];
												if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']) {
													$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
												}	
											}
										}
									} else if($key == 'mecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
											$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]		=	$key;
											$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$omniAmount	=	$resInstOmniApp['app_amount'];
												if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']) {
													$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
												$omniAmount	=	$resInstOmniApp['app_amount'];
												if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']) {
													$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me']['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me']['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
												}
											}
										}
									}
								}
							} else {
								if($key == 'tmecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityTme'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincity'][$m]=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]=	0;
										if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
											}	
										}
									}
								} else if($key == 'mecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]	=	0;
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me']['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me']['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
											}
										}
									}
								}
							}
							$m++;
						}
					}
				}
				$k++;
			}
			
			$ecsClearData	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_si.si_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59' AND a.parentid != '' AND a.parentid = ''";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			if($dbObjFin->numRows($conClearData) > 0) {
				$j = 0;
				while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
					if(!isset($contArr[$resClearData['parentid']])) {
						$contArr[$resClearData['parentid']]	=	array();
					}
					if(!isset($contArr[$resClearData['parentid']]['ecs'])) {
						$contArr[$resClearData['parentid']]['ecs']	=	array();
					}
					$servTax	=	$resClearData['service_tax'];
					$amount		=	$resClearData['billAmount'];
					$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
					$contArr[$resClearData['parentid']]['ecs'][$j]	=	array();
					$contArr[$resClearData['parentid']]['ecs'][$j]['billdeskid']	=	$resClearData['billDeskId'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billAmount']	=	$actualAmount;
					$contArr[$resClearData['parentid']]['ecs'][$j]['tmeGotCode']	=	$resClearData['tmecode'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['meGotCode']		=	$resClearData['mecode'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['companyname']	=	$resClearData['companyname'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['version']	=	$resClearData['version'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billResponseDate']	=	$resClearData['billResponseDate'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billGenerateDate']	=	$resClearData['billGenerateDate'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['data_city']	=	$resClearData['data_city'];
					$taggedEmpArr	=	array();
					$taggedEmpArr['tmecode']	=	$resClearData['tmecode'];
					$taggedEmpArr['mecode']		=	$resClearData['mecode'];
					$m = 0;
					foreach($taggedEmpArr as $key=>$value) {
						if(isset($empArr[$value])) {
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									if($empArr[$value]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										if($empArr[$value]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.03;
										} else if($empArr[$value]['city_type'] == 2) {
											$contArr[$actualAmount['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.04;
										}
									} else if($empArr[$value]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.01;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
									}
								} else if($resClearData['mecode'] == "" || $resClearData['mecode'] == null) {
									if($empArr[$value]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										if($empArr[$value]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.045;
										} else if($empArr[$value]['city_type'] == 2) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.055;
										}
									} else if($empArr[$value]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.01;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
									}
								}
							} 
							
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									if($empArr[$value]['emptype'] == '3') {
										if($empArr[$resClearData['tmecode']]['team_name']	==	"SJ") {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											if($empArr[$resClearData['tmecode']]['city_type'] == 1) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.03;
											} else if($empArr[$resClearData['tmecode']]['city_type'] == 2) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
											}
										} else if($empArr[$resClearData['tmecode']]['team_name']	==	"RD") {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.01;
										} else {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
										}
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.08;
									}
								} else if($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) {
									if($empArr[$value]['emptype'] == '3') {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.045;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
									}
								}
								
							}
							
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
								if($key == 'tmecode') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
									$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
									if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
									} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
									}
								} else if($key == 'mecode') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
									$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
									$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
									$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
									if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
									} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
									}
								}
							}
						} else {
							if($key == 'tmecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	0;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]	=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]	=	0;
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
								} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	0;
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]	=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	0;
								if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
								} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
								}
							}
						}
						$m++;
					}
					$j++;
				}
			}
			
			$ecsClearData	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_ecs.ecs_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59' AND a.parentid != ''";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			$j = 0;
			while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
				if(!isset($contArr[$resClearData['parentid']])) {
					$contArr[$resClearData['parentid']]	=	array();
				}
				if(!isset($contArr[$resClearData['parentid']]['ecs'])) {
					$contArr[$resClearData['parentid']]['ecs']	=	array();
				}
				$servTax	=	$resClearData['service_tax'];
				$amount		=	$resClearData['billAmount'];
				$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
				$contArr[$resClearData['parentid']]['ecs'][$j]	=	array();
				$contArr[$resClearData['parentid']]['ecs'][$j]['billdeskid']	=	$resClearData['billDeskId'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billAmount']	=	$actualAmount;
				$contArr[$resClearData['parentid']]['ecs'][$j]['companyname']	=	$resClearData['companyname'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['tmeGotCode']	=	$resClearData['tmecode'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['meGotCode']	=	$resClearData['mecode'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['version']	=	$resClearData['version'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billGenerateDate']	=	$resClearData['billGenerateDate'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billResponseDate']	=	$resClearData['billResponseDate'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['data_city']	=	$resClearData['data_city'];
				$taggedEmpArr	=	array();
				$taggedEmpArr['tmecode']	=	$resClearData['tmecode'];
				$taggedEmpArr['mecode']		=	$resClearData['mecode'];
				$m = 0;
				foreach($taggedEmpArr as $key=>$value) {
					if(isset($empArr[$value])) {
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]		=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]		=	$empArr[$value]['city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]		=	$empArr[$value]['active'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
							if($resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.03;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.04;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.01;
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
								}
							} else if($resClearData['mecode'] == "" || $resClearData['mecode'] == null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.045;
									} else if($empArr[$value]['city_type'] == 2){
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.055;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.01;
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
								}
							}
						}
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
							$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	$empArr[$value]['active'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
							if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
								if($empArr[$value]['emptype'] == '3') {
									if($empArr[$resClearData['tmecode']]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										if($empArr[$resClearData['tmecode']]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.03;
										} else if($empArr[$resClearData['tmecode']]['city_type'] == 2) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
										}
									} else if($empArr[$resClearData['tmecode']]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.01;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
									}
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.08;
								}
							} else if($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) {
								if($empArr[$value]['emptype'] == '3') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.045;
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
								}
							}
							
						}
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
							if($key == 'tmecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
								} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
								} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
								}
							}
						} 
					} else {
						if($key == 'tmecode') {
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	0;
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]	=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]	=	0;
							if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
							} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
							}
						} else if($key == 'mecode') {
							$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	0;
							$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]	=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	0;
							if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
							} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']		=	0;
							}
						}
					}
					$m++;
				}
				$j++;
			}
			
			//~ echo "<pre>";
			//~ print_r($contArr);
			//~ die;
			$insStr	=	"";
			foreach($contArr as $keyPar=>$valuePar) {
				if($keyPar != "") {
					foreach($valuePar as $keyType=>$valType) {
						foreach($valType as $keyInt=>$valInt) {
							if($keyType == 'dp' || $keyType == 'jdrr' || $keyType == 'omni') {
								if(isset($valInt['tme'])) {
									foreach($valInt['tme'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."'),";
										} else {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."'),";
										}
									}
								}
								if(isset($valInt['me'])){
									foreach($valInt['me'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."'),";
										} else {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."'),";
										}
									}
								}
							} else if($keyType == 'ecs') {
								if(isset($valInt['tme'])) {
									foreach($valInt['tme'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."'),";
										} else {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."'),";
										}
									}
								}
								if(isset($valInt['me'])){
									foreach($valInt['me'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."'),";
										} else {
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."'),";
										}
									}
								}
							}
						}
					}
				}
			}
			
			$insert		=	"INSERT INTO tbl_contract_incentive_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname ,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,process_city,foundIn,server_emp_city,main_remTag) VALUES ".substr($insStr,0,-1);
			$conInsert	=	$dbObjSSO->query($insert);
			echo "Done for ".$month."<hr>";
		}
		
		//~ $updateFinalCity	=	"UPDATE tbl_contract_incentive SET final_city=IF(emp_city<>'',emp_city,IF(module_city<>'',module_city,IF(data_city<>'',data_city,'')));";
		//~ $conUpd	=	$dbObjSSO->query($updateFinalCity);
		//~ echo "completely finished<hr>";
	}
	
	public function processDataNewRules() {
		ini_set('memory_limit', '-1');
		
		$dbObjSSO		=	new DB($this->db['db_sso']);
		
		$dr	=	"DROP TABLE IF EXISTS tbl_contract_incentive_back";
		//~ $con	=	$dbObjSSO->query($dr);
		
		$updateFinalCity	=	"UPDATE tbl_contract_incentive_back SET final_city=IF(emp_city<>'',emp_city,IF(module_city<>'',module_city,IF(data_city<>'',data_city,''))) WHERE incentive_month = '2017-03'";
		//$conUpd	=	$dbObjSSO->query($updateFinalCity); die('Update Over');
		
		$alterTable	=	"ALTER TABLE tbl_contract_incentive_back ADD COLUMN approval_date datetime default '0000-00-00 00:00:00', ADD INDEX idx_appr_date(approval_date)";
		//$conAlterDate	=	$dbObjSSO->query($alterTable); die('Update Over');
		
		$delEntry	=	"DELETE FROM tbl_contract_incentive_back WHERE process_city = 'ahmedabad'";
		//$conDelEntry	=	$dbObjSSO->query($delEntry); die('Delete Over');
		
		$processCity	=	"ahmedabad";
		
		//~ $crTabLike		=	"CREATE TABLE tbl_contract_incentive_back LIKE tbl_contract_incentive_back";
		//~ $conCrTabLike	=	$dbObjSSO->query($crTabLike);
		
		//~ $insTabLike		=	"INSERT INTO tbl_contract_incentive_back SELECT * FROM tbl_contract_incentive_back";
		//~ $conInsTabLike	=	$dbObjSSO->query($insTabLike); die('done');
		
		//~ $alterTab	=	"ALTER TABLE tbl_contract_incentive_back ADD COLUMN clashNew tinyint(1) DEFAULT 0";
		//~ $conAlterTab	=	$dbObjSSO->query($alterTab); die("Done");
		
		$crTable		=	"CREATE TABLE `tbl_contract_incentive_back` (
							  `parentid` VARCHAR(100) DEFAULT NULL,
							  `payment_type` VARCHAR(10) DEFAULT NULL,
							  `payment_id` VARCHAR(100) DEFAULT NULL,
							  `payment_source` VARCHAR(10) DEFAULT NULL,
							  `version` VARCHAR(10) DEFAULT NULL,
							  `amount` DOUBLE NOT NULL DEFAULT '0',
							  `incentive_amount` DOUBLE NOT NULL DEFAULT '0',
							  `incentive_amount_online` DOUBLE NOT NULL DEFAULT '0',
							  `entry_date` DATETIME DEFAULT NULL,
							  `approval_date` DATETIME DEFAULT NULL,
							  `empcode` VARCHAR(25) DEFAULT NULL,
							  `emptype` VARCHAR(10) DEFAULT NULL,
							  `empname` VARCHAR(100) DEFAULT NULL,
							  `team_name` VARCHAR(10) DEFAULT NULL,
							  `data_city` VARCHAR(25) DEFAULT NULL,
							  `module_city` VARCHAR(25) DEFAULT NULL,
							  `emp_city` VARCHAR(25) DEFAULT NULL,
							  `incentive_month` VARCHAR(30) DEFAULT '0000-00',
							  `active_flag` TINYINT(1) DEFAULT NULL,
							  `compname` VARCHAR(255) DEFAULT NULL,
							  `coldcall_tag` TINYINT(1) DEFAULT '0',
							  `final_city` VARCHAR(255) NOT NULL DEFAULT '',
							  `process_city` VARCHAR(255) NOT NULL DEFAULT '',
							  `foundIn` VARCHAR(255) NOT NULL DEFAULT '',
							  `server_emp_city` VARCHAR(100) NOT NULL DEFAULT '',
							  `clash_flag` TINYINT(1) DEFAULT '0',
							  `clash_perc` INT(11) DEFAULT '100',
							  `clashed_on` DATETIME DEFAULT '0000-00-00 00:00:00',
							  `clashid` VARCHAR(100) DEFAULT NULL,
							  `clash_emp_type` VARCHAR(25) DEFAULT NULL,
							  `clashNew` TINYINT(1) DEFAULT '0',
							  `main_remTag` TINYINT(1) DEFAULT '0',
							  `reten_flag` TINYINT(1) DEFAULT '0',
							  `reten_on` DATETIME DEFAULT '0000-00-00 00:00:00',
							  `clash_me_div` TINYINT(1) DEFAULT '0',
							  `calc_flag` TINYINT(1) DEFAULT '1',
							  `late_ret_flag` TINYINT(1) DEFAULT '0',
							  `return_on` DATETIME DEFAULT '0000-00-00 00:00:00',
							  KEY `idx_parid` (`parentid`),
							  KEY `idx_payid` (`payment_id`),
							  KEY `idx_empcode` (`empcode`),
							  KEY `idx_team` (`team_name`),
							  KEY `idx_incentive_month` (`incentive_month`),
							  KEY `idx_emp_city` (`emp_city`),
							  KEY `idx_module_city` (`module_city`),
							  KEY `idx_pay_type` (`payment_type`),
							  KEY `idx_data_city` (`data_city`),
							  KEY `idx_final_city` (`final_city`),
							  KEY `idx_appr_date` (`approval_date`),
							  KEY `idx_foundIn` (`foundIn`),
							  KEY `idx_server_emp_city` (`server_emp_city`),
							  KEY `clash_perc` (`clash_perc`),
							  KEY `clashed_on` (`clashed_on`),
							  KEY `clashid` (`clashid`),
							  KEY `clash_emp_type` (`clash_emp_type`),
							  KEY `idx_empname` (`empname`)
							) ENGINE=INNODB";
		//~ $conCrTable		=	$dbObjSSO->query($crTable);
		
		$empArr			=	array();
		$empStr			=	"";
		
		$selEmpInfoSSO	=	"SELECT empname as empname,empcode,city,section,resign_flag,delete_flag,grade,team_type,city_type FROM db_hr.tbl_employee_info";
		$conSelEmpInfoSSO	=	$dbObjSSO->query($selEmpInfoSSO);
		while($resEmpInfoSSO	=	$dbObjSSO->fetchData($conSelEmpInfoSSO)) {
			$empArr[trim($resEmpInfoSSO['empcode'])]['empname']	=	$resEmpInfoSSO['empname'];
			$empArr[trim($resEmpInfoSSO['empcode'])]['maincity']	=	$resEmpInfoSSO['city'];
			if($resEmpInfoSSO['resign_flag'] == 1 && $resEmpInfoSSO['delete_flag'] == 0) {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	1;
			} else {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	0;
			}
			$tmeOpArr		=	array("tme support","tme operations");
			$meOpArr		=	array("bde support","bde operations");
			$jdaOpArr		=	array("jda support","jda operations");
			if(strtolower($resEmpInfoSSO['section'])	==	'tme support' || strtolower($resEmpInfoSSO['section'])	==	'tme operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	5;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'bde support' || strtolower($resEmpInfoSSO['section'])	==	'bde operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	3;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'jda support' || strtolower($resEmpInfoSSO['section'])	==	'jda operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	13;
			}
			$empArr[trim($resEmpInfoSSO['empcode'])]['section']	=	strtolower($resEmpInfoSSO['section']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['grade']	=	strtolower($resEmpInfoSSO['grade']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['team_name']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['city']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['empcode']	=	trim($resEmpInfoSSO['empcode']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['team_name']	=	trim($resEmpInfoSSO['team_type']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['city_type']	=	trim($resEmpInfoSSO['city_type']);
			$empStr	.=	trim($resEmpInfoSSO['empcode'])."','";
		}
		
		$cityFinderArr	=	array("remote","mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
		foreach($cityFinderArr as $key=>$value) {
			$dbObjLocLoop		=	new DB($this->db['db_local_'.$value]);
			$selEmpInfo	=	"SELECT mktEmpCode,empType,city,allocId,city_type FROM mktgEmpMaster WHERE mktEmpCode != '' AND mktEmpCode IS NOT NULL and mktEmpCode IN ('".substr($empStr,0,-3)."')";
			$conSelEmpInfo	=	$dbObjLocLoop->query($selEmpInfo);
			while($resEmpInfo	=	$dbObjLocLoop->fetchData($conSelEmpInfo)) {
				if(!isset($empArr[trim($resEmpInfo['mktEmpCode'])]) || (isset($empArr[trim($resEmpInfo['mktEmpCode'])]) && $empArr[trim($resEmpInfo['mktEmpCode'])]['city_type'] == 0)) {
					$empArr[trim($resEmpInfo['mktEmpCode'])]['city']		=	$resEmpInfo['city'];
					$empArr[trim($resEmpInfo['mktEmpCode'])]['server_city']	=	$value;
				}
			}
		}
		//~ echo "<pre>";
		//~ print_r($empArr);
		//~ die;
		//$monthArr	=	array("2016-09","2016-10");
		$monthArr	=	array("2017-01","2017-02","2017-03","2017-04");
		foreach($monthArr as $value) {
			$dbObjLocal		=	new DB($this->db['db_local']);
			$month			=	$value;
			$insStrEmp		=	"";
			$contArr		=	array();
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			
			$maincityArr	=	array("mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
			
			$selEmpDate		=	"SELECT instrumentType,instrumentAmount,version,campaignidlist,campaignwisebudget,dealclosebudget,entry_date,tmecode,mecode,entry_doneby,ecsflag,parentid,companyname,finalApprovalDate,service_tax,tdsAmount,data_city,instrumentid FROM contract_payment_details WHERE finalApprovalDate >= '".$month."-01 00:00:00' AND finalApprovalDate <= '".$month."-31 23:59:59' AND finalApprovalDate != '' AND approvalStatus = 1 AND parentid != ''";
			$conEmpDate		=	$dbObjFin->query($selEmpDate);
			$k = 0;
			while($conGetInc	=	$dbObjFin->fetchData($conEmpDate)) {
				$instruAmount	=	(((float)$conGetInc['instrumentAmount']+(float)$conGetInc['tdsAmount']) / (1+$conGetInc['service_tax']));
				if(!isset($contArr[$conGetInc['parentid']])) {
					$contArr[$conGetInc['parentid']]	=	array();
				}
				if(!isset($contArr[$conGetInc['parentid']]['dp'])) {
					$contArr[$conGetInc['parentid']]['dp']	=	array();
				}
				$ecsMandateParId	=	"";
				if($conGetInc['ecsflag']	==	1) {
					$contArr[$conGetInc['parentid']]['calc_flag']	=	0;
				}
				$contArr[$conGetInc['parentid']]['dp'][$k]	=	array();
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['instrumentamount']	=	$instruAmount;
				$contArr[$conGetInc['parentid']]['dp'][$k]['version']	=	$conGetInc['version'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['entry_date']	=	$conGetInc['entry_date'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['meGotCode']		=	$conGetInc['mecode'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['companyname']	=	$conGetInc['companyname'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
				$contArr[$conGetInc['parentid']]['dp'][$k]['data_city']	=	$conGetInc['data_city'];
				$taggedEmpArr	=	array();
				
				$taggedEmpArr['tmecode']	=	$conGetInc['tmecode'];
				$taggedEmpArr['mecode']		=	$conGetInc['mecode'];
				
				$m = 0;
				foreach($taggedEmpArr as $key=>$value) {
					if(isset($empArr[$value])) {
						if(isset($empArr[$value]) && $empArr[$value]['emptype'] == 5) {
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
							if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.025;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.025;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.035;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.035;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.04;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.04;
								} else {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$instruAmount*0.04;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.04;
								}
							} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.04;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.04;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.05;
										$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.05;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.055;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.055;
								} else {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$instruAmount*0.055;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen']	=	0.055;
								}
							}
							//~ if($month == '2016-11') {
								//~ if($empArr[$value]['maincity'] != "" && (strtolower(trim($empArr[$value]['maincity'])) ==	'delhi' || strtolower(trim($empArr[$value]['maincity'])) ==	'noida') && ($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
									//~ $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$instruAmount;
									//~ $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']	=	$instruAmount*0.01;
								//~ }
							//~ } else if($month == '2016-12' || $month == '2017-01' || $month == '2017-02') {
								$cityArrExt	=	array("chandigarh","jaipur","hyderabad","delhi","kolkata","noida");
								if($empArr[$value]['maincity'] != "" && in_array(strtolower(trim($empArr[$value]['maincity'])),$cityArrExt) && ($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']	=	$instruAmount*0.01;
								}
							//~ }
						} 
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
							$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
							if($empArr[$value]['emptype'] == '13') {
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									$gradeFinder	=	$empArr[$value]['grade'];
									if(substr($empArr[$value]['grade'],0,1) == 'g') {
										$gradeFinder	=	substr($empArr[$value]['grade'],1);
									}
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	0;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0;
									if($gradeFinder != '' && $gradeFinder != null) {
										$gradeFinder	=	(int)$gradeFinder;
										if($gradeFinder <= 10) {
											if($empArr[$value]['section']	==	'jda support') {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
											}
										} else if($gradeFinder >= 11) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
										}
									} else if($gradeFinder == '' || $gradeFinder == null) {
										if($empArr[$value]['section']	==	'jda operations') {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
										}
									}
								} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
									$gradeFinder	=	$empArr[$value]['grade'];
									if(substr($empArr[$value]['grade'],0,1) == 'g') {
										$gradeFinder	=	substr($empArr[$value]['grade'],1);
									}
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	0;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0;
									if($gradeFinder != '' && $gradeFinder != null) {
										$gradeFinder	=	(int)$gradeFinder;
										if($gradeFinder <= 10) {
											if($empArr[$value]['section']	==	'jda support') {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.04;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
											}
										} else if($gradeFinder >= 11) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.08;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.08;
										}
									} else if($gradeFinder == '' || $gradeFinder == null) {
										if($empArr[$value]['section']	==	'jda operations') {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.08;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.08;
										}
									}
								}
							} else {
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									if($empArr[$conGetInc['tmecode']]['team_name']	==	"SJ") {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										if($empArr[$conGetInc['tmecode']]['city_type'] == 1) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.025;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.025;
										} else if($empArr[$conGetInc['tmecode']]['city_type'] == 2) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.035;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.035;
										}
									} else if($empArr[$conGetInc['tmecode']]['team_name']	==	"RD") {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
									} else {
										$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
										$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
									}
								} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me']	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.04;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen']	=	0.04;
								}
							}
							if(($conGetInc['instrumentType'] == 'neft' || $conGetInc['instrumentType'] == 'creditcard' || $conGetInc['instrumentType'] == 'payu')) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']	=	$instruAmount*0.01;
							}
						}
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
							if($key == 'tmecode') {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
								$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	0;
								} else if($conGetInc['tmecode'] != '' && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
								$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
								$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	0;
								} else if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
									$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	0;
								}
							}
						}
					} else {
						if($key == 'tmecode') {
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	0;
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]	=	0;
							if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	0;
							} else if($conGetInc['tmecode'] != '' && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	0;
							}
						} else if($key == 'mecode') {
							$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	0;
							$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]=	$key;
							$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]=	"";
							$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]=	0;
							if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	0;
							} else if($conGetInc['mecode'] != '' && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]	=	array();
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount;
								$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	0;
							}
						}
					}
					$m++;
				}
				
				$expCampaignList	=	explode(",",$conGetInc['campaignidlist']);
				$expCampigBudgList	=	explode(",",$conGetInc['campaignwisebudget']);
				$arrSendData	=	array();
				foreach($expCampigBudgList as $key=>$value) {
					$budgetVal	=	explode("-",$value);
					$arrSendData[$budgetVal[0]]	=	$budgetVal[1];
				}
				//For JDRR
				if(in_array(22,$expCampaignList)) {
					$selInstruJdrrApportion	=	"SELECT app_amount FROM payment_snapshot WHERE instrumentid = '".$conGetInc['instrumentid']."' AND campaignId = '22'";
					$conInstJdrrApp			=	$dbObjFin->query($selInstruJdrrApportion);
					$numRowsJdrr			=	$dbObjFin->numRows($conInstJdrrApp);
					if($numRowsJdrr > 0) {
						$resInstJdrrApp		=	$dbObjFin->fetchData($conInstJdrrApp);
						if(!isset($contArr[$conGetInc['parentid']]['jdrr'])) {
							$contArr[$conGetInc['parentid']]['jdrr']	=	array();
						}
						$contArr[$conGetInc['parentid']]['jdrr'][$k]	=	array();
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['instrumentamount']	=	$instruAmount;
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['version']	=	$conGetInc['version'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['entry_date']	=	$conGetInc['entry_date'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['meGotCode']	=	$conGetInc['mecode'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['companyname']	=	$conGetInc['companyname'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
						$contArr[$conGetInc['parentid']]['jdrr'][$k]['data_city']	=	$conGetInc['data_city'];
						$jdrrAmount	=	0;
						$jdrrAmount	=	$resInstJdrrApp['app_amount'];
						if($resInstJdrrApp['app_amount'] > $instruAmount) {
							$jdrrAmount	=	$instruAmount;
						}
						$m = 0;
						foreach($taggedEmpArr as $key=>$value) {
							if(isset($empArr[$value])) {
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInTme'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	(float)round($jdrrAmount) * 0.04;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme']['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	(float)round($jdrrAmount) * 0.08;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										}
									}
								} 
							
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInMe'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
										if($empArr[$value]['emptype'] == '13') {
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$gradeFinder	=	$empArr[$value]['grade'];
												if(substr($empArr[$value]['grade'],0,1) == 'g') {
													$gradeFinder	=	substr($empArr[$value]['grade'],1);
												}
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']	=	$jdrrAmount;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	0;
												if($gradeFinder != '' && $gradeFinder != null) {
													$gradeFinder	=	(int)$gradeFinder;
													if($gradeFinder <= 10) {
														if($empArr[$value]['section']	==	'jda support') {
															$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	$jdrrAmount*0.04;
															$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.04;
														}
													} else if($gradeFinder >= 11) {
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	$jdrrAmount*0.04;
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.04;
													}
												} else if($gradeFinder == '' || $gradeFinder == null) {
													if($empArr[$value]['section']	==	'jda operations') {
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.04;
													}
												}
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
												}
											} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
												$gradeFinder	=	$empArr[$value]['grade'];
												if(substr($empArr[$value]['grade'],0,1) == 'g') {
													$gradeFinder	=	substr($empArr[$value]['grade'],1);
												}
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	$jdrrAmount;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	0;
												if($gradeFinder != '' && $gradeFinder != null) {
													$gradeFinder	=	(int)$gradeFinder;
													if($gradeFinder <= 10) {
														if($empArr[$value]['section']	==	'jda support') {
															$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	$jdrrAmount*0.04;
															$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.04;
														}
													} else if($gradeFinder >= 11) {
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	$jdrrAmount*0.08;
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.08;
													}
												} else if($gradeFinder == '' || $gradeFinder == null) {
													if($empArr[$value]['section']	==	'jda operations') {
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.08;
														$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['percen']	=	0.08;
													}
												}
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
												}
											}
										} else {
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']=	(float)round($jdrrAmount);	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	(float)round($jdrrAmount) * 0.04;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
												}
											} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me']	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	(float)round($jdrrAmount) * 0.08;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount'] * 0.01;
												}	
											}
										}
									}
								} 
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
									if($key == 'tmecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmecity'][$m]	=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmecode'][$m]	=	$value;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeact'][$m]	=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['foundInTme'][$m]	=	$key;
											$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityTme'][$m]	=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
												}	
											}
										}
									} else if($key == 'mecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
											$contArr[$conGetInc['parentid']]['dp'][$k]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['dp'][$k]['mecity'][$m]	=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['mecode'][$m]	=	$value;
											$contArr[$conGetInc['parentid']]['dp'][$k]['meact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['meTeamName'][$m]=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['memaincity'][$m]=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['foundInMe'][$m]	=	$key;
											$contArr[$conGetInc['parentid']]['dp'][$k]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['dp'][$k]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
											if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	0;
												}
											}
										}
									}
								}
							} else {
								if($key == 'tmecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmename'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecity'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmeTeamName'][$m]=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['tmemaincity'][$m]=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInTme'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityTme'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeTme'][$m]	=	0;
										if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['incen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccincen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['tme'][$m]['oninc']	=	0;
											}	
										}
									}
								} else if($key == 'mecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mename'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecity'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['meTeamName'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['foundInMe'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['server_cityMe'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['jdrr'][$k]['city_typeMe'][$m]	=	0;
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['incen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount']	=	(float)round($jdrrAmount);
											$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccincen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$jdrrAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['jdrr'][$k]['me'][$m]['oninc']		=	0;
											}
										}
									}
								}
							}
							$m++;
						}
					}
				}
				//~ //For Omni
				if(in_array(72,$expCampaignList) || in_array(73,$expCampaignList)) {
					$selInstruOmniApportion	=	"SELECT SUM(app_amount) as app_amount FROM payment_snapshot WHERE instrumentid = '".$conGetInc['instrumentid']."' AND campaignId IN (72,73) GROUP BY instrumentid";
					$conInstOmniApp			=	$dbObjFin->query($selInstruOmniApportion);
					$numRowsOmni			=	$dbObjFin->numRows($conInstOmniApp);
					if($numRowsOmni > 0) {
						$resInstOmniApp		=	$dbObjFin->fetchData($conInstOmniApp);
						if(!isset($contArr[$conGetInc['parentid']]['omni'])) {
							$contArr[$conGetInc['parentid']]['omni']	=	array();
						}
						$contArr[$conGetInc['parentid']]['omni'][$k]	=	array();
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumentid']	=	$conGetInc['instrumentid'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumenttype']	=	$conGetInc['instrumentType'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['instrumentamount']	=	$instruAmount;
						$contArr[$conGetInc['parentid']]['omni'][$k]['version']	=	$conGetInc['version'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['campaignidlist']	=	$conGetInc['campaignidlist'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['campaignwisebudget']	=	$conGetInc['campaignwisebudget'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['dealclosebudget']	=	$conGetInc['dealclosebudget'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['entry_date']	=	$conGetInc['entry_date'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['tmeGotCode']	=	$conGetInc['tmecode'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['meGotCode']	=	$conGetInc['mecode'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['entry_doneby']	=	$conGetInc['entry_doneby'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['ecsflag']	=	$conGetInc['ecsflag'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['companyname']	=	$conGetInc['companyname'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['finalApprovalDate']	=	$conGetInc['finalApprovalDate'];
						$contArr[$conGetInc['parentid']]['omni'][$k]['data_city']	=	$conGetInc['data_city'];
						$omniAmount	=	0;
						$omniAmount	=	$resInstOmniApp['app_amount'];
						if($resInstOmniApp['app_amount'] > $instruAmount) {
							$omniAmount	=	$instruAmount;
						}
						
						$m = 0;
						foreach($taggedEmpArr as $key=>$value) {
							if(isset($empArr[$value])) {
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	(float)round($omniAmount) * 0.05;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										} else if($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null) {	
											$omniAmount	=	$resInstOmniApp['app_amount'];
											if($resInstOmniApp['app_amount'] > $contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']) {
												$omniAmount	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
											}
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme']	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	(float)round($omniAmount) * 0.10;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount'] * 0.01;
											}
										}
									}
								}
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	$empArr[$value]['active'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]		=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
										
										if($empArr[$value]['emptype'] == '13') {
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$gradeFinder	=	$empArr[$value]['grade'];
												if(substr($empArr[$value]['grade'],0,1) == 'g') {
													$gradeFinder	=	substr($empArr[$value]['grade'],1);
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	$omniAmount;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	0;
												if($gradeFinder != '' && $gradeFinder != null) {
													$gradeFinder	=	(int)$gradeFinder;
													if($gradeFinder <= 10) {
														if($empArr[$value]['section']	==	'jda support') {
															$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	$omniAmount*0.04;
															$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.04;
														}
													} else if($gradeFinder >= 11) {
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	$omniAmount*0.04;
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.04;
													}
												} else if($gradeFinder == '' || $gradeFinder == null) {
													if($empArr[$value]['section']	==	'jda operations') {
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	$instruAmount*0.04;
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.04;
													}
												}
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
												}
											} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
												$gradeFinder	=	$empArr[$value]['grade'];
												if(substr($empArr[$value]['grade'],0,1) == 'g') {
													$gradeFinder	=	substr($empArr[$value]['grade'],1);
												}
												$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	$omniAmount;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	0;
												if($gradeFinder != '' && $gradeFinder != null) {
													$gradeFinder	=	(int)$gradeFinder;
													if($gradeFinder <= 10) {
														if($empArr[$value]['section']	==	'jda support') {
															$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	$omniAmount*0.04;
															$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.04;
														}
													} else if($gradeFinder >= 11) {
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	$omniAmount*0.08;
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.08;
													}
												} else if($gradeFinder == '' || $gradeFinder == null) {
													if($empArr[$value]['section']	==	'jda operations') {
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	$instruAmount*0.08;
														$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['percen']	=	0.08;
													}
												}
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
												}
											}
										} else {
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	(float)round($omniAmount) * 0.05;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
												}
											} else if($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me']	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	(float)round($omniAmount) * 0.10;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount'] * 0.01;
												}	
											}
										}
									}
								}
								
								if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
									if($key == 'tmecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]		=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]		=	$key;
											$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	0;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
												}	
											}
										}
									} else if($key == 'mecode') {
										if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
											$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	$empArr[$value]['city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
											$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	$empArr[$value]['active'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	$empArr[$value]['team_name'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['memaincity'][$m]	=	$empArr[$value]['maincity'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]		=	$key;
											$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]		=	$empArr[$value]['server_city'];
											$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]		=	$empArr[$value]['city_type'];
											if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
												}	
											} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	0;	
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$omniAmount;
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me']['percen'];
												if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount'];
													$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount']*0.01;
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me']['ccamount'];
													$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
												}
											}
										}
									}
								}
							} else {
								if($key == 'tmecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmename'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecity'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmeTeamName'][$m]=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInTme'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_city'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['tmemaincityTme'][$m]=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeTme'][$m]=	0;
										if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && $conGetInc['mecode'] != "" && $conGetInc['mecode'] != null) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['incen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null && ($conGetInc['mecode'] == "" || $conGetInc['mecode'] == null)) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccincen']	=	0;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['tme'][$m]['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['tme'][$m]['oninc']	=	0;
											}	
										}
									}
								} else if($key == 'mecode') {
									if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m])) {
										$contArr[$conGetInc['parentid']]['omni'][$k]['mename'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecity'][$m]		=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['mecode'][$m]		=	$value;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meact'][$m]		=	0;
										$contArr[$conGetInc['parentid']]['omni'][$k]['meEmpType'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['meTeamName'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['foundInMe'][$m]	=	$key;
										$contArr[$conGetInc['parentid']]['omni'][$k]['server_cityMe'][$m]	=	"";
										$contArr[$conGetInc['parentid']]['omni'][$k]['city_typeMe'][$m]	=	0;
										if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && $conGetInc['tmecode'] != "" && $conGetInc['tmecode'] != null) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['incen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['incen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['percen'];	
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['amount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['amount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
											}	
										} else if($conGetInc['mecode'] != "" && $conGetInc['mecode'] != null && ($conGetInc['tmecode'] == "" || $conGetInc['tmecode'] == null)) {
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]	=	array();	
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccamount']	=	(float)round($omniAmount);
											$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['ccincen']	=	0;	
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']	=	$instruAmount-$omniAmount;
											$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccincen']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['ccamount']*$contArr[$conGetInc['parentid']]['dp'][$k]['me']['percen'];
											if(isset($contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount'])) {
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount'];
												$contArr[$conGetInc['parentid']]['dp'][$k]['me'][$m]['oninc']		=	$contArr[$conGetInc['parentid']]['dp'][$k]['me']['ccamount']*0.01;
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['onamount']	=	$contArr[$conGetInc['parentid']]['omni'][$k]['me']['ccamount'];
												$contArr[$conGetInc['parentid']]['omni'][$k]['me'][$m]['oninc']	=	0;
											}
										}
									}
								}
							}
							$m++;
						}
					}
				}
				$k++;
			}
			
			//~ echo "<pre>";
			//~ print_r($contArr); die;
			
			$ecsParentid	=	"";
			$ecsClearData	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city,c.entry_date FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_si.si_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59' AND a.parentid != '' AND a.parentid = ''";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			if($dbObjFin->numRows($conClearData) > 0) {
				$j = 0;
				while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
					$ecsParentid	.=	$resClearData['parentid']."','";
					if(!isset($contArr[$resClearData['parentid']])) {
						$contArr[$resClearData['parentid']]	=	array();
					}
					if(!isset($contArr[$resClearData['parentid']]['ecs'])) {
						$contArr[$resClearData['parentid']]['ecs']	=	array();
					}
					
					$contArr[$resClearData['parentid']]['calc_flag']	=	0;
					$servTax	=	$resClearData['service_tax'];
					$amount		=	$resClearData['billAmount'];
					$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
					$contArr[$resClearData['parentid']]['ecs'][$j]	=	array();
					$contArr[$resClearData['parentid']]['ecs'][$j]['billdeskid']	=	$resClearData['billDeskId'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billAmount']	=	$actualAmount;
					$contArr[$resClearData['parentid']]['ecs'][$j]['tmeGotCode']	=	$resClearData['tmecode'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['meGotCode']		=	$resClearData['mecode'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['companyname']	=	$resClearData['companyname'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['version']	=	$resClearData['version'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billResponseDate']	=	$resClearData['billResponseDate'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['billGenerateDate']	=	$resClearData['billGenerateDate'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['entry_date']	=	$resClearData['entry_date'];
					$contArr[$resClearData['parentid']]['ecs'][$j]['data_city']	=	$resClearData['data_city'];
					$taggedEmpArr	=	array();
					$taggedEmpArr['tmecode']	=	$resClearData['tmecode'];
					$taggedEmpArr['mecode']		=	$resClearData['mecode'];
					$m = 0;
					foreach($taggedEmpArr as $key=>$value) {
						if(isset($empArr[$value])) {
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									if($empArr[$value]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										if($empArr[$value]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.03;
										} else if($empArr[$value]['city_type'] == 2) {
											$contArr[$actualAmount['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.04;
										}
									} else if($empArr[$value]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
									}
								} else if($resClearData['mecode'] == "" || $resClearData['mecode'] == null) {
									if($empArr[$value]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										if($empArr[$value]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.045;
										} else if($empArr[$value]['city_type'] == 2) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.055;
										}
									} else if($empArr[$value]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
									}
								}
							} 
							
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									if($empArr[$value]['emptype'] == '3') {
										if($empArr[$resClearData['tmecode']]['team_name']	==	"SJ") {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											if($empArr[$resClearData['tmecode']]['city_type'] == 1) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.03;
											} else if($empArr[$resClearData['tmecode']]['city_type'] == 2) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
											}
										} else if($empArr[$resClearData['tmecode']]['team_name']	==	"RD") {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
										} else {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
										}
									} else {
										$gradeFinder	=	$empArr[$value]['grade'];
										if(substr($empArr[$value]['grade'],0,1) == 'g') {
											$gradeFinder	=	substr($empArr[$value]['grade'],1);
										}
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0;
										if($gradeFinder != '' && $gradeFinder != null) {
											$gradeFinder	=	(int)$gradeFinder;
											if($gradeFinder <= 10) {
												if($empArr[$value]['section']	==	'jda support') {
													$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
													$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
												}
											} else if($gradeFinder >= 11) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
											}
										} else if($gradeFinder == '' || $gradeFinder == null) {
											if($empArr[$value]['section']	==	'jda operations') {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
											}
										}
									}
								} else if($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) {
									if($empArr[$value]['emptype'] == '3') {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0;
									} else {
										$gradeFinder	=	$empArr[$value]['grade'];
										if(substr($empArr[$value]['grade'],0,1) == 'g') {
											$gradeFinder	=	substr($empArr[$value]['grade'],1);
										}
										if($gradeFinder != '' && $gradeFinder != null) {
											$gradeFinder	=	(int)$gradeFinder;
											if($gradeFinder <= 10) {
												if($empArr[$value]['section']	==	'jda support') {
													$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.04;
													$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
												}
											} else if($gradeFinder >= 11) {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.08;
											}
										} else if($gradeFinder == '' || $gradeFinder == null) {
											if($empArr[$value]['section']	==	'jda operations') {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.08;
											}
										}
									}
								}
								
							} 
							if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
								if($key == 'tmecode') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
									$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
									if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
									} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
									}
								} else if($key == 'mecode') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
									$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
									$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
									$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
									$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
									if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
									} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
									}
								}
							}
						} else {
							if($key == 'tmecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	0;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]	=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]	=	0;
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
								} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	0;
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]	=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	"";
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	0;
								if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
								} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
								}
							}
						}
						$m++;
					}
					$j++;
				}
			}
			
			$ecsClearData	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city,c.entry_date FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_ecs.ecs_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 1 AND b.billResponseDate >= '".$month."-01 00:00:00' AND b.billResponseDate <= '".$month."-31 23:59:59' AND a.parentid != ''";
			$conClearData	=	$dbObjFin->query($ecsClearData);
			$j = 0;
			while($resClearData	=	$dbObjFin->fetchData($conClearData)) {
				$ecsParentid	.=	$resClearData['parentid']."','";
				if(!isset($contArr[$resClearData['parentid']])) {
					$contArr[$resClearData['parentid']]	=	array();
				}
				if(!isset($contArr[$resClearData['parentid']]['ecs'])) {
					$contArr[$resClearData['parentid']]['ecs']	=	array();
				}
				$contArr[$resClearData['parentid']]['calc_flag']	=	0;
				$servTax	=	$resClearData['service_tax'];
				$amount		=	$resClearData['billAmount'];
				$actualAmount	=	$amount / (1+($resClearData['service_tax']/100));
				$contArr[$resClearData['parentid']]['ecs'][$j]	=	array();
				$contArr[$resClearData['parentid']]['ecs'][$j]['billdeskid']	=	$resClearData['billDeskId'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billAmount']	=	$actualAmount;
				$contArr[$resClearData['parentid']]['ecs'][$j]['companyname']	=	$resClearData['companyname'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['tmeGotCode']	=	$resClearData['tmecode'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['meGotCode']	=	$resClearData['mecode'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['version']	=	$resClearData['version'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billGenerateDate']	=	$resClearData['billGenerateDate'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['billResponseDate']	=	$resClearData['billResponseDate'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['entry_date']	=	$resClearData['entry_date'];
				$contArr[$resClearData['parentid']]['ecs'][$j]['data_city']	=	$resClearData['data_city'];
				$taggedEmpArr	=	array();
				$taggedEmpArr['tmecode']	=	$resClearData['tmecode'];
				$taggedEmpArr['mecode']		=	$resClearData['mecode'];
				$m = 0;
				foreach($taggedEmpArr as $key=>$value) {
					if(isset($empArr[$value])) {
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 5)) {
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]		=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]		=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]		=	$empArr[$value]['city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]		=	$empArr[$value]['active'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]	=	$empArr[$value]['team_name'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]	=	$empArr[$value]['maincity'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
							if($resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.03;
									} else if($empArr[$value]['city_type'] == 2) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.04;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	$actualAmount*0.05;
								}
							} else if($resClearData['mecode'] == "" || $resClearData['mecode'] == null) {
								if($empArr[$value]['team_name']	==	"SJ") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									if($empArr[$value]['city_type'] == 1) {
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.045;
									} else if($empArr[$value]['city_type'] == 2){
										$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.055;
									}
								} else if($empArr[$value]['team_name']	==	"RD") {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
								} else {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	$actualAmount*0.065;
								}
							}
						}
						
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] == 3 || $empArr[$value]['emptype'] == 13)) {
							$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	$empArr[$value]['active'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
							if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
								if($empArr[$value]['emptype'] == '3') {
									if($empArr[$resClearData['tmecode']]['team_name']	==	"SJ") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										if($empArr[$resClearData['tmecode']]['city_type'] == 1) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.03;
										} else if($empArr[$resClearData['tmecode']]['city_type'] == 2) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
										}
									} else if($empArr[$resClearData['tmecode']]['team_name']	==	"RD") {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
									} else {
										$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
										$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.05;
									}
								} else {
									$gradeFinder	=	$empArr[$value]['grade'];
									if(substr($empArr[$value]['grade'],0,1) == 'g') {
										$gradeFinder	=	substr($empArr[$value]['grade'],1);
									}
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
									if($gradeFinder != '' && $gradeFinder != null) {
										$gradeFinder	=	(int)$gradeFinder;
										if($gradeFinder <= 10) {
											if($empArr[$value]['section']	==	'jda support') {		
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
											}
										} else if($gradeFinder >= 11) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
										}
									} else if($gradeFinder == '' || $gradeFinder == null) {
										if($empArr[$value]['section']	==	'jda operations') {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	$actualAmount*0.04;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
										}
									}
								}
							} else if($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null) {
								if($empArr[$value]['emptype'] == '3') {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.045;
								} else {
									$gradeFinder	=	$empArr[$value]['grade'];
									if(substr($empArr[$value]['grade'],0,1) == 'g') {
										$gradeFinder	=	substr($empArr[$value]['grade'],1);
									}
									$contArr[$resClearData['parentid']]['ecs'][$j]['me']	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
									if($gradeFinder != '' && $gradeFinder != null) {
										$gradeFinder	=	(int)$gradeFinder;
										if($gradeFinder <= 10) {
											if($empArr[$value]['section']	==	'jda support') {
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.04;
												$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.04;
											}
										} else if($gradeFinder >= 11) {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.08;
										}
									} else if($gradeFinder == '' || $gradeFinder == null) {
										if($empArr[$value]['section']	==	'jda operations') {
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	$actualAmount*0.08;
											$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['percen']	=	0.08;
										}
									}
								}
							}
						}
						if(isset($empArr[$value]) && ($empArr[$value]['emptype'] != 3 && $empArr[$value]['emptype'] != 13 && $empArr[$value]['emptype'] != 5)) {
							if($key == 'tmecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]		=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]		=	$empArr[$value]['city_type'];
								if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
								} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
								}
							} else if($key == 'mecode') {
								$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	addslashes(stripslashes($empArr[$value]['empname']));
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
								$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	$empArr[$value]['city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]		=	$empArr[$value]['active'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	$empArr[$value]['emptype'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	$empArr[$value]['team_name'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	$empArr[$value]['maincity'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]		=	$key;
								$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	$empArr[$value]['server_city'];
								$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	$empArr[$value]['city_type'];
								if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
								} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
									$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']	=	0;
								}
							}
						}
					} else {
						if($key == 'tmecode') {
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmename'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecity'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeact'][$m]	=	0;
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeEmpType'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmeTeamName'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmemaincity'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['tmecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInTme'][$m]	=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityTme'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeTme'][$m]	=	0;
							if($resClearData['tmecode'] != "" && $resClearData['tmecode'] != null && $resClearData['mecode'] != "" && $resClearData['mecode'] != null) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['amount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['incen']	=	0;
							} else if($resClearData['tmecode'] != '' && $resClearData['tmecode'] != null && ($resClearData['mecode'] == "" || $resClearData['mecode'] == null)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccamount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['tme'][$m]['ccincen']	=	0;
							}
						} else if($key == 'mecode') {
							$contArr[$resClearData['parentid']]['ecs'][$j]['mename'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecity'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['meact'][$m]	=	0;
							$contArr[$resClearData['parentid']]['ecs'][$j]['meEmpType'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['meTeamName'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['memaincity'][$m]=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['mecode'][$m]	=	$value;
							$contArr[$resClearData['parentid']]['ecs'][$j]['foundInMe'][$m]	=	$key;
							$contArr[$resClearData['parentid']]['ecs'][$j]['server_cityMe'][$m]	=	"";
							$contArr[$resClearData['parentid']]['ecs'][$j]['city_typeMe'][$m]	=	0;
							if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && $resClearData['tmecode'] != "" && $resClearData['tmecode'] != null) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['amount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['incen']	=	0;
							} else if($resClearData['mecode'] != '' && $resClearData['mecode'] != null && ($resClearData['tmecode'] == "" || $resClearData['tmecode'] == null)) {
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]	=	array();
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccamount']	=	$actualAmount;
								$contArr[$resClearData['parentid']]['ecs'][$j]['me'][$m]['ccincen']		=	0;
							}
						}
					}
					$m++;
				}
				$j++;
			}
			//~ echo "<pre>";
			//~ print_r($contArr);die;
			$dbObjLoc		=	new DB($this->db['db_local']);
			$selRetenData	=	"SELECT tmecode,tmename,mecode,mename,parentid,update_date FROM tbl_new_retention WHERE parentid IN ('".substr($ecsParentid,0,-3)."') AND action_flag = 5";
			$conRetenData	=	$dbObjLoc->query($selRetenData);
			while($resRetenData	=	$dbObjLoc->fetchData($conRetenData)) {
				foreach($contArr[$resRetenData['parentid']]['ecs'] as $key=>$value) {
					if($resRetenData['update_date'] > $value['entry_date'] && $resRetenData['update_date'] < $value['billResponseDate']) {
						$contArr[$resRetenData['parentid']]['ecs'][$key]['retenFlag']	=	1;
						$contArr[$resRetenData['parentid']]['ecs'][$key]['retenOn']	=	$resRetenData['update_date'];
						if(isset($empArr[$resRetenData['tmecode']])) {
							if(isset($empArr[$resRetenData['tmecode']]) && ($empArr[$resRetenData['tmecode']]['emptype'] == 5)) {
								$billAmount	=	$value['billAmount'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmename']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmecode']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmecity']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeact']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeEmpType']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeTeamName']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmemaincity']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['foundInTme']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['server_cityTme']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['city_typeTme']	=	array();
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
								
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmename'][0]		=	addslashes(stripslashes($empArr[$resRetenData['tmecode']]['empname']));
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmecode'][0]		=	$resRetenData['tmecode'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmecity'][0]		=	$empArr[$resRetenData['tmecode']]['city'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeact'][0]		=	$empArr[$resRetenData['tmecode']]['active'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeEmpType'][0]	=	$empArr[$resRetenData['tmecode']]['emptype'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmeTeamName'][0]	=	$empArr[$resRetenData['tmecode']]['team_name'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['tmemaincity'][0]	=	$empArr[$resRetenData['tmecode']]['maincity'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['foundInTme'][0]	=	"";
								$contArr[$resRetenData['parentid']]['ecs'][$key]['server_cityTme'][0]	=	$empArr[$resRetenData['tmecode']]['server_city'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['city_typeTme'][0]	=	$empArr[$resRetenData['tmecode']]['city_type'];
								
								if($resRetenData['mecode'] != "" && $resRetenData['mecode'] != null) {
									if($empArr[$resRetenData['tmecode']]['team_name']	==	"SJ") {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['amount']	=	$billAmount;
										if($empArr[$resRetenData['tmecode']]['city_type'] == 1) {
											$contArr[$resRetenData['parentid']]['ecs'][$j]['tme'][0]['incen']	=	$billAmount*0.03;
										} else if($empArr[$resRetenData['tmecode']]['city_type'] == 2) {
											$contArr[$resRetenData['parentid']]['ecs'][$j]['tme'][0]['incen']	=	$billAmount*0.04;
										}
									} else if($empArr[$resRetenData['tmecode']]['team_name']	==	"RD") {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['amount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['incen']	=	$billAmount*0.015;
									} else {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['amount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['incen']	=	$billAmount*0.05;
									}
								} else if($resRetenData['mecode'] == "" || $resRetenData['mecode'] == null) {
									if($empArr[$resRetenData['tmecode']]['team_name']	==	"SJ") {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccamount']	=	$billAmount;
										if($empArr[$resRetenData['tmecode']]['city_type'] == 1) {
											$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccincen']	=	$billAmount*0.045;
										} else if($empArr[$resRetenData['tmecode']]['city_type'] == 2){
											$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccincen']	=	$billAmount*0.055;
										}
									} else if($empArr[$resRetenData['tmecode']]['team_name']	==	"RD") {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccamount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccincen']	=	$billAmount*0.015;
									} else {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccamount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['tme'][0]['ccincen']	=	$billAmount*0.065;
									}
								}
							}
						}
						$contArr[$resRetenData['parentid']]['ecs'][$key]['mename']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['mecode']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['mecity']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['meact']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['meEmpType']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['meTeamName']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['memaincity']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['foundInMe']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['server_cityMe']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['city_typeMe']	=	array();
						$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
						if($resRetenData['mecode'] != '' && $resRetenData['mecode'] != null) {
							$billAmount	=	$value['billAmount'];
							if(isset($empArr[$resRetenData['mecode']]) && ($empArr[$resRetenData['mecode']]['emptype'] == 3)) {
								$contArr[$resRetenData['parentid']]['ecs'][$key]['mename'][0]	=	addslashes(stripslashes($empArr[$resRetenData['mecode']]['empname']));
								$contArr[$resRetenData['parentid']]['ecs'][$key]['mecode'][0]	=	$resRetenData['mecode'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['mecity'][0]	=	$empArr[$resRetenData['mecode']]['city'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['meact'][0]	=	$empArr[$resRetenData['mecode']]['active'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['meEmpType'][0]	=	$empArr[$resRetenData['mecode']]['emptype'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['meTeamName'][0]=	$empArr[$resRetenData['mecode']]['team_name'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['memaincity'][0]=	$empArr[$resRetenData['mecode']]['maincity'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['foundInMe'][0]		=	"";
								$contArr[$resRetenData['parentid']]['ecs'][$key]['server_cityMe'][0]	=	$empArr[$resRetenData['mecode']]['server_city'];
								$contArr[$resRetenData['parentid']]['ecs'][$key]['city_typeMe'][0]	=	$empArr[$resRetenData['mecode']]['city_type'];
								if($resRetenData['mecode'] != "" && $resRetenData['mecode'] != null) {
									if($empArr[$resRetenData['mecode']]['emptype'] == '3') {
										if($empArr[$resRetenData['tmecode']]['team_name']	==	"SJ") {
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['amount']	=	$billAmount;
											if($empArr[$resRetenData['tmecode']]['city_type'] == 1) {
												$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['incen']	=	$billAmount*0.03;
											} else if($empArr[$resRetenData['tmecode']]['city_type'] == 2) {
												$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['incen']	=	$billAmount*0.04;
											}
										} else if($empArr[$resRetenData['tmecode']]['team_name']	==	"RD") {
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['amount']	=	$billAmount;
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['incen']	=	$billAmount*0.01;
										} else {
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['amount']	=	$billAmount;
											$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['incen']	=	$billAmount*0.05;
										}
									} else {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['amount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['incen']	=	$billAmount*0.04;
									}
								} else if($resRetenData['tmecode'] == "" || $resRetenData['tmecode'] == null) {
									if($empArr[$value]['emptype'] == '3') {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['ccamount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['ccincen']	=	$billAmount*0.045;
									} else {
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me']	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]	=	array();
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['ccamount']	=	$billAmount;
										$contArr[$resRetenData['parentid']]['ecs'][$key]['me'][0]['ccincen']	=	$billAmount*0.08;
									}
								}
							}
						}
					}
				}
			}
			
			$insStr	=	"";
			foreach($contArr as $keyPar=>$valuePar) {
				if($keyPar != "") {
					foreach($valuePar as $keyType=>$valType) {
						foreach($valType as $keyInt=>$valInt) {
							if($keyType == 'dp' || $keyType == 'jdrr' || $keyType == 'omni') {
								if(isset($valInt['tme'])) {
									foreach($valInt['tme'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										} else {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										}
									}
								}
								if(isset($valInt['me'])){
									foreach($valInt['me'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										} else {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['instrumentid']."','".$valInt['instrumenttype']."','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['entry_date']."','".$valInt['finalApprovalDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										}
									}
								}
							} else if($keyType == 'ecs') {
								if(isset($valInt['tme'])) {
									foreach($valInt['tme'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										} else {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['tmecode'][$key]."','".$valInt['tmeEmpType'][$key]."','".$valInt['tmename'][$key]."','".$valInt['tmeTeamName'][$key]."','".$valInt['data_city']."','".$valInt['tmecity'][$key]."','".$valInt['tmemaincity'][$key]."','".$month."','".$valInt['tmeact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInTme'][$key]."','".$valInt['server_cityTme'][$key]."','".$valInt['city_typeTme'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										}
									}
								}
								if(isset($valInt['me'])){
									foreach($valInt['me'] as $key=>$value) {
										if(isset($value['ccamount'])) {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['ccamount']."','".$value['ccincen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','1','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										} else {
											if(!isset($valInt['retenFlag'])) {$retenValue	=	0;$retenOn = "";} else {$retenValue	=	1;$retenOn = $valInt['retenOn'];}
											$insStr	.=	"('".$keyPar."','".$keyType."','".$valInt['billdeskid']."','ecs','".$valInt['version']."','".$value['amount']."','".$value['incen']."','".$value['oninc']."','".$valInt['billGenerateDate']."','".$valInt['billResponseDate']."','".$valInt['mecode'][$key]."','".$valInt['meEmpType'][$key]."','".$valInt['mename'][$key]."','".$valInt['meTeamName'][$key]."','".$valInt['data_city']."','".$valInt['mecity'][$key]."','".$valInt['memaincity'][$key]."','".$month."','".$valInt['meact'][$key]."','".addslashes(stripslashes($valInt['companyname']))."','0','".$processCity."','".$valInt['foundInMe'][$key]."','".$valInt['server_cityMe'][$key]."','".$valInt['city_typeMe'][$key]."','".$retenValue."','".$retenOn."','".$valuePar['calc_flag']."'),";
										}
									}
								}
							}
						}
					}
				}
			}
			
			$insert		=	"INSERT INTO tbl_contract_incentive_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname ,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,process_city,foundIn,server_emp_city,main_remTag,reten_flag,reten_on,calc_flag) VALUES ".substr($insStr,0,-1);
			$conInsert	=	$dbObjSSO->query($insert);
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$checkMandateQuer	=	"SELECT a.billdeskid,b.parentid,b.version FROM db_ecs.ecs_HO_clearance_details a JOIN db_ecs.ecs_mandate b ON a.billdeskid = b.billdeskid WHERE a.billdeskVerificationResponseFlag IN (1,3,8) AND a.billdeskVerificationResponseDate >= '".$month."-01 00:00:00' AND a.billdeskVerificationResponseDate <= '".$month."-31 23:59:59'";
			$conChkMandateOver	=	$dbObjFin->query($checkMandateQuer);
			if($dbObjFin->numRows($conChkMandateOver) > 0) {
				$dbObjSSO		=	new DB($this->db['db_sso']);
				while($fetMandateData	=	$dbObjFin->fetchData($conChkMandateOver)) {
					$updCalcFlag	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 1 WHERE parentid = '".$fetMandateData['parentid']."' AND version = '".$fetMandateData['version']."' AND calc_flag = 0";
					$conUPdCalcFlg	=	$dbObjSSO->query($updCalcFlag);
				}
			}
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$checkMandateQuer	=	"SELECT a.billdeskid,b.parentid,b.version FROM db_si.si_HO_clearance_details a JOIN db_si.si_mandate b ON a.billdeskid = b.billdeskid WHERE a.billdeskVerificationResponseFlag IN (1,3,8) AND a.billdeskVerificationResponseDate >= '".$month."-01 00:00:00' AND a.billdeskVerificationResponseDate <= '".$month."-31 23:59:59'";
			$conChkMandateOver	=	$dbObjFin->query($checkMandateQuer);
			if($dbObjFin->numRows($conChkMandateOver) > 0) {
				$dbObjSSO		=	new DB($this->db['db_sso']);
				while($fetMandateData	=	$dbObjFin->fetchData($conChkMandateOver)) {
					$updCalcFlag	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 1 WHERE parentid = '".$fetMandateData['parentid']."' AND version = '".$fetMandateData['version']."' AND calc_flag = 0";
					$conUPdCalcFlg	=	$dbObjSSO->query($updCalcFlag);
				}
			}
			
			echo "Done for ".$month."<hr>";
		}
		
		//~ $updateFinalCity	=	"UPDATE tbl_contract_incentive_back SET final_city=IF(emp_city<>'',emp_city,IF(module_city<>'',module_city,IF(data_city<>'',data_city,'')));";
		//~ $conUpd	=	$dbObjSSO->query($updateFinalCity);
		echo "completely finished for ".$processCity."<hr>";
	}
	
	public function setCalcFlag() {
		$dbObjSSO		=	new DB($this->db['db_sso']);
		$upd	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 1";
		//~ $conUpd	=	$dbObjSSO->query($upd);
		
		$updBills	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 0 WHERE payment_type = 'ecs'";
		//~ $conUpdBill	=	$dbObjSSO->query($updBills);
		
		$monthArr	=	array("2017-01","2017-02","2017-03","2017-04");
		foreach($monthArr as $value) {
			$month			=	$value;
		
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$selECSCont	=	"SELECT instrumentid FROM contract_payment_details WHERE ecsflag > 0 AND finalApprovalDate >= '".$month."-01 00:00:00' AND finalApprovalDate <= '".$month."-31 23:59:59'";
			$conSel		=	$dbObjFin->query($selECSCont);
			$dbObjSSO		=	new DB($this->db['db_sso']);
			while($resUpd		=	$dbObjFin->fetchData($conSel)) {
				$upd	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 0 WHERE payment_id = '".$resUpd['instrumentid']."'";
				$conUpd	=	$dbObjSSO->query($upd);
			}
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$checkMandateQuer	=	"SELECT a.billdeskid,b.parentid,b.version FROM db_ecs.ecs_HO_clearance_details a JOIN db_ecs.ecs_mandate b ON a.billdeskid = b.billdeskid WHERE a.billdeskVerificationResponseFlag IN (1,3,8) AND a.billdeskVerificationResponseDate >= '".$month."-01 00:00:00' AND a.billdeskVerificationResponseDate <= '".$month."-31 23:59:59'";
			$conChkMandateOver	=	$dbObjFin->query($checkMandateQuer);
			if($dbObjFin->numRows($conChkMandateOver) > 0) {
				$dbObjSSO		=	new DB($this->db['db_sso']);
				while($fetMandateData	=	$dbObjFin->fetchData($conChkMandateOver)) {
					
					$updCalcFlag	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 1 WHERE parentid = '".$fetMandateData['parentid']."' AND version = '".$fetMandateData['version']."' AND calc_flag = 0";
					$conUPdCalcFlg	=	$dbObjSSO->query($updCalcFlag);
				}
			}
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$checkMandateQuer	=	"SELECT a.billdeskid,b.parentid,b.version FROM db_si.si_HO_clearance_details a JOIN db_si.si_mandate b ON a.billdeskid = b.billdeskid WHERE a.billdeskVerificationResponseFlag IN (1,3,8) AND a.billdeskVerificationResponseDate >= '".$month."-01 00:00:00' AND a.billdeskVerificationResponseDate <= '".$month."-31 23:59:59'";
			$conChkMandateOver	=	$dbObjFin->query($checkMandateQuer);
			if($dbObjFin->numRows($conChkMandateOver) > 0) {
				$dbObjSSO		=	new DB($this->db['db_sso']);
				while($fetMandateData	=	$dbObjFin->fetchData($conChkMandateOver)) {
					
					$updCalcFlag	=	"UPDATE tbl_contract_incentive_back SET calc_flag = 1 WHERE parentid = '".$fetMandateData['parentid']."' AND version = '".$fetMandateData['version']."' AND calc_flag = 0";
					$conUPdCalcFlg	=	$dbObjSSO->query($updCalcFlag);
				}
			}
			echo "Done for remote ".$month."<hr>";
		}
	}
	
	public function processDataNewClash() {
		ini_set('memory_limit', '-1');
		$dbObjSSO		=	new DB($this->db['db_sso']);
		
		//~ $dbObjFin		=	new DB($this->db['db_finance']);
		//~ $checkMandateQuer	=	"SELECT a.billdeskid,b.parentid FROM db_ecs.ecs_HO_clearance_details a JOIN db_ecs.ecs_mandate b ON a.billdeskid = b.billdeskid WHERE a.billdeskVerificationResponseFlag IN (1,3,5) AND a.billdeskVerificationResponseDate >= '2017-01-01 00:00:00' AND a.billdeskVerificationResponseDate <= '2017-01-31 23:59:59'";
		//~ $conChkMandateOver	=	$dbObjFin->query($checkMandateQuer,1);
		//~ if($dbObjFin->numRows($conChkMandateOver) > 0) {
			//~ while($fetMandateData	=	$dbObjFin->fetchData($conChkMandateOver)) {
				//~ $dbObjSSO		=	new DB($this->db['db_sso']);
				//~ $updCalcFlag	=	"UPDATE tbl_contract_incentive_back_back SET calc_flag = 1 WHERE parentid = '".$fetMandateData['parentid']."' AND calc_flag = 0";
				//~ $conUPdCalcFlg	=	$dbObjSSO->query($updCalcFlag);
			//~ }
		//~ }
		
		//~ $upod = "DELETE FROM tbl_clash_admin WHERE sr_no = 0 and empCode = '013253'";
		//~ $con	=	$dbObjSSO->query($upod); die;
		
		//~ $crTable	=	"INSERT INTO tbl_clash_admin SET sr_no = '4',empCode='006492',added_on='2017-02-06 00:00:00';";
		//~ $con	=	$dbObjSSO->query($crTable); die;
		
		//~ $empArr			=	array();
		//~ $empStr			=	"";
		
		//~ $altTabCont	=	"ALTER TABLE tbl_contract_incentive_back ADD COLUMN clash_me_div TINYINT(1) default 0;";
		//~ $conUpd	=	$dbObjSSO->query($altTabCont); die;
		
		//~ $cityFinderArr	=	array("delhi");
		//~ foreach($cityFinderArr as $key=>$value) {
			//~ $selDataFinder	=	"SELECT clashid FROM tbl_contract_incentive_back WHERE clash_flag = 1 AND process_city = '".$value."'";
			//~ $conSelDataFind	=	$dbObjSSO->query($selDataFinder);
			
			//~ $clashidStr	=	"";
			//~ while($resSelDataFind	=	$dbObjSSO->fetchData($conSelDataFind)) {
				//~ $clashidStr	.=	$resSelDataFind['clashid']."','";
			//~ }
			//~ $dbObjLocLoop		=	new DB($this->db['db_fin_'.$value]);
			//~ $selClashDesc	=	"SELECT decision_date,clashid FROM clash_temp WHERE clashid IN ('".substr($clashidStr,0,-3)."')";
			//~ $conClashDesc	=	$dbObjLocLoop->query($selClashDesc);
			//~ while($resClashDesc	=	$dbObjLocLoop->fetchData($conClashDesc)) {
				//~ $updClashDesc	=	"UPDATE tbl_contract_incentive_back SET desc_date = '".$resClashDesc['decision_date']."' WHERE clashid = '".$resClashDesc['clashid']."'";
				//~ $conUpdClashDesc	=	$dbObjSSO->query($updClashDesc);
			//~ }
		//~ }
		//~ die("DFone");
		
		//die("Stopped");
		$updateFinalCity	=	"UPDATE tbl_contract_incentive_back SET final_city=IF(emp_city<>'',emp_city,IF(module_city<>'',module_city,IF(data_city<>'',data_city,'')));";
		//$conUpd	=	$dbObjSSO->query($updateFinalCity); die("update done");
		
		$delTable	=	"DROP TABLE tbl_contract_incentive_back_04_04_2017";
		//$conDelTable	=	$dbObjSSO->query($delTable); 
		
		//~ $renameTab	=	"RENAME TABLE tbl_contract_incentive TO tbl_contract_incentive_back_20_04_2017";
		//~ $conUpd	=	$dbObjSSO->query($renameTab);
		
		//~ $renameTab2	=	"RENAME TABLE tbl_contract_incentive_back TO tbl_contract_incentive";
		//~ $conUpd	=	$dbObjSSO->query($renameTab2);
		
		//~ $renameTab	=	"RENAME TABLE tbl_contract_incentive_debit TO tbl_contract_incentive_debit_20_04_2017";
		//~ $conUpd	=	$dbObjSSO->query($renameTab);
		
		//~ $renameTab	=	"RENAME TABLE tbl_contract_incentive_debit_back TO tbl_contract_incentive_debit";
		//~ $conUpd	=	$dbObjSSO->query($renameTab);
		
		//~ die('Update Over');
		
		
		//~ $upd = "update tbl_contract_incentive_back set clash_flag = '0',clashed_on = '0000-00-00 00:00:00',clashid='' WHERE clash_flag = 1";
		//~ $conUpd	=	$dbObjSSO->query($upd);
		
		//~ $del	=	"DELETE FROM tbl_contract_incentive_back WHERE clashNew = '1'";
		//~ $conDel	=	$dbObjSSO->query($del);
		//~ die("Stop");
		
		//~ $crTab	=	"CREATE TABLE tbl_temp_inst2 (KEY(payment_id)) SELECT payment_id FROM tbl_contract_incentive_back WHERE emptype = '5' GROUP BY payment_id";
		//~ $conTab	=	$dbObjSSO->query($crTab); die;
		
		$selEmpInfoSSO	=	"SELECT empname as empname,empcode,city,section,resign_flag,delete_flag,grade,team_type,city_type FROM db_hr.tbl_employee_info";
		$conSelEmpInfoSSO	=	$dbObjSSO->query($selEmpInfoSSO);
		while($resEmpInfoSSO	=	$dbObjSSO->fetchData($conSelEmpInfoSSO)) {
			$empArr[trim($resEmpInfoSSO['empcode'])]['empname']	=	$resEmpInfoSSO['empname'];
			$empArr[trim($resEmpInfoSSO['empcode'])]['maincity']	=	$resEmpInfoSSO['city'];
			if($resEmpInfoSSO['resign_flag'] == 1 && $resEmpInfoSSO['delete_flag'] == 0) {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	1;
			} else {
				$empArr[trim($resEmpInfoSSO['empcode'])]['active']	=	0;
			}
			$tmeOpArr		=	array("tme support","tme operations");
			$meOpArr		=	array("bde support","bde operations");
			$jdaOpArr		=	array("jda support","jda operations");
			if(strtolower($resEmpInfoSSO['section'])	==	'tme support' || strtolower($resEmpInfoSSO['section'])	==	'tme operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	5;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'bde support' || strtolower($resEmpInfoSSO['section'])	==	'bde operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	3;
			} else if(strtolower($resEmpInfoSSO['section'])	==	'jda support' || strtolower($resEmpInfoSSO['section'])	==	'jda operations') {
				$empArr[trim($resEmpInfoSSO['empcode'])]['emptype']	=	13;
			}
			$empArr[trim($resEmpInfoSSO['empcode'])]['section']	=	strtolower($resEmpInfoSSO['section']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['grade']	=	strtolower($resEmpInfoSSO['grade']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['team_name']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['city']	=	"";
			$empArr[trim($resEmpInfoSSO['empcode'])]['empcode']	=	trim($resEmpInfoSSO['empcode']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['team_name']	=	trim($resEmpInfoSSO['team_type']);
			$empArr[trim($resEmpInfoSSO['empcode'])]['city_type']	=	trim($resEmpInfoSSO['city_type']);
			$empStr	.=	trim($resEmpInfoSSO['empcode'])."','";
		}
		
		$cityFinderArr	=	array("remote","mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
		foreach($cityFinderArr as $key=>$value) {
			$dbObjLocLoop		=	new DB($this->db['db_local_'.$value]);
			$selEmpInfo	=	"SELECT mktEmpCode,empType,city,allocId,city_type FROM mktgEmpMaster WHERE mktEmpCode != '' AND mktEmpCode IS NOT NULL and mktEmpCode IN ('".substr($empStr,0,-3)."')";
			$conSelEmpInfo	=	$dbObjLocLoop->query($selEmpInfo);
			while($resEmpInfo	=	$dbObjLocLoop->fetchData($conSelEmpInfo)) {
				if(!isset($empArr[trim($resEmpInfo['mktEmpCode'])]) || (isset($empArr[trim($resEmpInfo['mktEmpCode'])]) && $empArr[trim($resEmpInfo['mktEmpCode'])]['city_type'] == 0)) {
					$empArr[trim($resEmpInfo['mktEmpCode'])]['city']		=	$resEmpInfo['city'];
					$empArr[trim($resEmpInfo['mktEmpCode'])]['server_city']	=	$value;
				}
			}
		}
		
		$processCity = "ahmedabad";
		
		$monthArr	=	array("2017-01","2017-02","2017-03","2017-04");
		foreach($monthArr as $value) {
			$dbObjLocal		=	new DB($this->db['db_local']);
			$month			=	$value;
			$month10		=	date("Y-m",strtotime("+1 month",strtotime($month)));
			$insStrEmp		=	"";
			$contArr		=	array();
			
			$dbObjFin		=	new DB($this->db['db_finance']);
			
			$maincityArr	=	array("mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
			
			$selectIncData	=	"SELECT * FROM tbl_contract_incentive_back WHERE incentive_month = '".$value."' AND clash_flag = 0 AND process_city = '".$processCity."'";
			$conSelIncData	=	$dbObjSSO->query($selectIncData);
			$payIdStrIns	=	"";
			$payIdStrECS	=	"";
			$i = 0;
			$paymentIdArr	=	array();
			while($resSelIncData	=	$dbObjSSO->fetchData($conSelIncData)) {
				if(!isset($contArr[$resSelIncData['payment_id']])) {
					$contArr[$resSelIncData['payment_id']]	=	array();
					$contArr[$resSelIncData['payment_id']]['data']	=	array();
				}
				if(!isset($paymentIdArr[$resSelIncData['payment_id']])) {
					$paymentIdArr[$resSelIncData['payment_id']]	=	array();
				}
				$paymentIdArr[$resSelIncData['payment_id']][$i]	=	$resSelIncData;
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]	=	array();
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]['empcode']	=	$resSelIncData['empcode'];
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]['emptype']	=	$resSelIncData['emptype'];
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]['owner']		=	1;
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]['reten_flag']		=	$resSelIncData['reten_flag'];
				$contArr[$resSelIncData['payment_id']]['data'][$resSelIncData['empcode']]['reten_on']		=	$resSelIncData['reten_on'];
				$contArr[$resSelIncData['payment_id']]['reten_flag']	=	$resSelIncData['reten_flag'];
				$contArr[$resSelIncData['payment_id']]['reten_on']	=	$resSelIncData['reten_on'];
				$contArr[$resSelIncData['payment_id']]['clashFlg']	=	0;
				$contArr[$resSelIncData['payment_id']]['calc_flag']	=	$resSelIncData['calc_flag'];
				if($resSelIncData['payment_type']	==	'ecs') {
					$payIdStrECS	.=	$resSelIncData['payment_id']."','";
				} else {
					$payIdStrIns	.=	$resSelIncData['payment_id']."','";
				}				
				$i++;
			}
			
			$selClashData	=	"SELECT clashed_on,parentid,instrumentid,claimentptg,ownerptg,claimed_by,owner_cd,status_clash,clashid,emp_type FROM clash_temp WHERE instrumentid IN ('".substr($payIdStrIns,0,-3)."') AND status_clash = 'Approved' AND clashed_on >= '".$value."-01 00:00:00' AND clashed_on <= '".$month10."-10 23:59:59'";
			$conSelClashData	=	$dbObjSSO->query($selClashData);
			$i = 0;
			while($resSelClashData	=	$dbObjSSO->fetchData($conSelClashData)) {
				$ownerCd	=	strtoupper($resSelClashData['owner_cd']);
				if((strlen($resSelClashData['owner_cd']) < 6) && ($resSelClashData['owner_cd'] != 'TME1' && $resSelClashData['owner_cd'] != 'ME1')) {
					$ownerCd	=	str_pad($resSelClashData['owner_cd'],6,"0",STR_PAD_LEFT);
				}
				$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['perc']	=	$resSelClashData['ownerptg'];
				$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['empcode']	=	$ownerCd;
				$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['clashedOn']	=	$resSelClashData['clashed_on'];
				$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['clashid']	=	$resSelClashData['clashid'];
				if($contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['owner'] != 1) {
					if($resSelClashData['emp_type']	==	'TME') {
						$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['emptype']	=	5;
					} else if($resSelClashData['emp_type']	==	'ME') {
						$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['emptype']	=	3;
					} else if($resSelClashData['emp_type']	==	'JDA') {
						$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['emptype']	=	13;
					}
				}
				$contArr[$resSelClashData['instrumentid']]['clashFor'][$i]	=	$contArr[$resSelClashData['instrumentid']]['data'][$ownerCd]['emptype'];
				
				$claimedBy	=	strtoupper($resSelClashData['claimed_by']);
				if((strlen($resSelClashData['claimed_by']) < 6) && ($resSelClashData['claimed_by'] != 'TME1' && $resSelClashData['claimed_by'] != 'ME1')) {
					$claimedBy	=	str_pad($resSelClashData['claimed_by'],6,"0",STR_PAD_LEFT);
				}
				
				$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['perc']	=	$resSelClashData['claimentptg'];
				$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['empcode']	=	$claimedBy;
				$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['clashedOn']	=	$resSelClashData['clashed_on'];
				$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['clashid']	=	$resSelClashData['clashid'];
				if($contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['owner'] != 1) {
					if($resSelClashData['emp_type']	==	'TME') {
						$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['emptype']	=	5;
					} else if($resSelClashData['emp_type']	==	'ME') {
						$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['emptype']	=	3;
					} else if($resSelClashData['emp_type']	==	'JDA') {
						$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['emptype']	=	13;
					}
				}
				$contArr[$resSelClashData['instrumentid']]['clashFor'][$i]	=	$contArr[$resSelClashData['instrumentid']]['data'][$claimedBy]['emptype'];
				$contArr[$resSelClashData['instrumentid']]['clashFlg']	=	1;
				$i++;
			}
			
			$j = 0;
			$selClashData	=	"SELECT clashed_on,parentid,billdeskid,claimentptg,ownerptg,claimed_by,owner_cd,status_clash,clashid,emp_type FROM clash_temp WHERE billdeskid IN ('".substr($payIdStrECS,0,-3)."') AND status_clash = 'Approved' AND clashed_on <= '".$month10."-10 23:59:59'";
			$conSelClashData	=	$dbObjSSO->query($selClashData);
			while($resSelClashData	=	$dbObjSSO->fetchData($conSelClashData)) {
				if($contArr[$resSelClashData['billdeskid']]['reten_flag'] == 0 || ($contArr[$resSelClashData['billdeskid']]['reten_flag'] == 1 && $contArr[$resSelClashData['billdeskid']]['reten_on'] < $resSelClashData['clashed_on'])) {
					$ownerCd	=	strtoupper($resSelClashData['owner_cd']);
					if((strlen($resSelClashData['owner_cd']) < 6) && ($resSelClashData['owner_cd'] != 'TME1' && $resSelClashData['owner_cd'] != 'ME1')) {
						$ownerCd	=	str_pad($resSelClashData['owner_cd'],6,"0",STR_PAD_LEFT);
					}
					
					$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['perc']		=	$resSelClashData['ownerptg'];
					$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['empcode']	=	$ownerCd;
					$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['clashedOn']	=	$resSelClashData['clashed_on'];
					$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['clashid']	=	$resSelClashData['clashid'];
					if($resSelClashData['emp_type']	==	'TME') {
						$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['emptype']	=	5;
					} else if($resSelClashData['emp_type']	==	'ME') {
						$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['emptype']	=	3;
					} else if($resSelClashData['emp_type']	==	'JDA') {
						$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['emptype']	=	13;
					}
					$contArr[$resSelClashData['billdeskid']]['clashFor'][$j]	=	$contArr[$resSelClashData['billdeskid']]['data'][$ownerCd]['emptype'];
					
					$claimedBy	=	strtoupper($resSelClashData['claimed_by']);
					if((strlen($resSelClashData['claimed_by']) < 6) && ($resSelClashData['claimed_by'] != 'TME1' && $resSelClashData['claimed_by'] != 'ME1')) {
						$claimedBy	=	str_pad($resSelClashData['claimed_by'],6,"0",STR_PAD_LEFT);
					}
					
					$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['perc']	=	$resSelClashData['claimentptg'];
					$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['empcode']	=	$claimedBy;
					$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['clashedOn']	=	$resSelClashData['clashed_on'];
					$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['clashid']	=	$resSelClashData['clashid'];
					if($resSelClashData['emp_type']	==	'TME') {
						$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['emptype']	=	5;
					} else if($resSelClashData['emp_type']	==	'ME') {
						$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['emptype']	=	3;
					} else if($resSelClashData['emp_type']	==	'JDA') {
						$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['emptype']	=	13;
					}
					$contArr[$resSelClashData['billdeskid']]['clashFor'][$j]	=	$contArr[$resSelClashData['billdeskid']]['data'][$claimedBy]['emptype'];
					$contArr[$resSelClashData['billdeskid']]['clashFlg']	=	1;
					$j++;
				}
			}
			
			//~ echo "<pre>";
			//~ print_r($contArr); die;
			
			$insStrArr	=	array();
			foreach($contArr as $keyClash=>$valueClash) {
				if($valueClash['clashFlg']	==	1) {
					foreach($valueClash['data'] as $keyData=>$valueDataClash) {
						if($valueDataClash['owner'] == 1 && in_array($valueDataClash['emptype'],$valueClash['clashFor'])) {
							if(!isset($valueDataClash['perc'])) {
								$updQuery	=	"UPDATE tbl_contract_incentive_back SET clash_flag = 1,clash_perc = 0,clashed_on = '".$valueDataClash['clashedOn']."',clashid='".$valueDataClash['clashid']."' WHERE payment_id = '".$keyClash."' AND empcode = '".$keyData."'";
								$conUpdate	=	$dbObjSSO->query($updQuery);
							} else {
								$updQuery	=	"UPDATE tbl_contract_incentive_back SET clash_flag = 1,clash_perc = '".$valueDataClash['perc']."',clashed_on = '".$valueDataClash['clashedOn']."',clashid='".$valueDataClash['clashid']."' WHERE payment_id = '".$keyClash."' AND empcode = '".$keyData."'";
								$conUpdate	=	$dbObjSSO->query($updQuery);
							}
						} else {
							if(in_array($valueDataClash['emptype'],$valueClash['clashFor'])) {
								if(!isset($insStrArr[$keyClash])) {
									$insStrArr[$keyClash]	=	array();
								}
								$insStrArr[$keyClash][$keyData]	=	array();
								$insStrArr[$keyClash][$keyData]['data']	=	array();
								$insStrArr[$keyClash][$keyData]['data']	=	$paymentIdArr[$keyClash];
								$insStrArr[$keyClash][$keyData]['assoc']	=	array();
								foreach($paymentIdArr[$keyClash] as $valueCl) {
									if($valueCl['emptype']	==	5) {
										$insStrArr[$keyClash][$keyData]['assoc']['tme']['empcode']		=	$valueCl['empcode'];
										$insStrArr[$keyClash][$keyData]['assoc']['tme']['team_name']	=	$valueCl['team_name'];
										$insStrArr[$keyClash][$keyData]['assoc']['tme']['server_city']	=	$valueCl['server_emp_city'];
										$insStrArr[$keyClash][$keyData]['assoc']['tme']['emp_city']	=	$valueCl['emp_city'];
										$insStrArr[$keyClash][$keyData]['assoc']['tme']['city_type']	=	$valueCl['main_remTag'];
									}
								}
								$insStrArr[$keyClash][$keyData]['perc']	=	$valueDataClash['perc'];
								$insStrArr[$keyClash][$keyData]['clashedOn']	=	$valueDataClash['clashedOn'];
								$insStrArr[$keyClash][$keyData]['clashid']	=	$valueDataClash['clashid'];
								$insStrArr[$keyClash][$keyData]['emptype']	=	$valueDataClash['emptype'];
								$insStrArr[$keyClash][$keyData]['calc_flag']	=	$valueClash['calc_flag'];
							}
						}
					}
				}
			}
			//~ echo "<pre>";
			//~ print_r($insStrArr);
			//~ die;
			
			$insStr	=	"";
			foreach($insStrArr as $keyIns=>$valueIns) {
				foreach($valueIns as $keyEmp=>$valEmp) {
					if($keyEmp != '') {
					foreach($valEmp['data'] as $valpayInfo) {
						if(isset($empArr[$keyEmp])) {
							$incAmountOnline	=	0;
							$incAmounnt = 0;
							if(isset($empArr[$keyEmp]) && ($empArr[$keyEmp]['emptype'] == 5)) {
								if($valpayInfo['payment_type'] == 'dp') {
									if($empArr[$keyEmp]['team_name']	==	"SJ") {
										if($empArr[$keyEmp]['city_type'] == 1) {
											$incAmounnt	=	$valpayInfo['amount']*0.025;
										} else if($empArr[$keyEmp]['city_type'] == 2){
											$incAmounnt	=	$valpayInfo['amount']*0.035;
										}
									} else if($empArr[$keyEmp]['team_name']	==	"RD") {
										$incAmounnt	=	$valpayInfo['amount']*0.04;
									} else {
										$incAmounnt	=	$valpayInfo['amount']*0.04;
									}
									
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'jdrr') {
									$incAmounnt	=	$valpayInfo['amount']*0.04;
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'omni') {
									$incAmounnt	=	$valpayInfo['amount']*0.05;
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'ecs') {
									if($empArr[$keyEmp]['team_name']	==	"SJ") {
										if($empArr[$keyEmp]['city_type'] == 1) {
											$incAmounnt	=	$valpayInfo['amount']*0.03;
										} else if($empArr[$keyEmp]['city_type'] == 2){
											$incAmounnt	=	$valpayInfo['amount']*0.04;
										}
									} else if($empArr[$keyEmp]['team_name']	==	"RD") {
										$incAmounnt	=	$valpayInfo['amount']*0.05;
									} else {
										$incAmounnt	=	$valpayInfo['amount']*0.05;
									}
								}
							} else if(isset($empArr[$keyEmp]) && ($empArr[$keyEmp]['emptype'] == 3 || $empArr[$keyEmp]['emptype'] == 13)){
								if($valpayInfo['payment_type'] == 'dp') {
									if($empArr[$keyEmp]['emptype'] == 13) {
										$gradeFinder	=	$empArr[$value]['grade'];
										if(substr($empArr[$value]['grade'],0,1) == 'g') {
											$gradeFinder	=	substr($empArr[$value]['grade'],1);
										}
										if($gradeFinder != '' && $gradeFinder != null) {
											$gradeFinder	=	(int)$gradeFinder;
											if($gradeFinder <= 10) {
												if($empArr[$value]['section']	==	'jda support') {
													$incAmounnt	=	$valpayInfo['amount']*0.04;
												}
											} else if($gradeFinder >= 11) {
												$incAmounnt	=	$valpayInfo['amount']*0.04;
											}
										}
									} else if($empArr[$keyEmp]['emptype'] == 3) {
										if(isset($valpayInfo['assoc']['tme'])) {
											if($valpayInfo['assoc']['tme']['team_name']	==	"SJ") {
												if($valpayInfo['assoc']['tme']['city_type'] == 1) {
													$incAmounnt	=	$valpayInfo['amount']*0.025;
												} else if($valpayInfo['assoc']['tme']['city_type'] == 2){
													$incAmounnt	=	$valpayInfo['amount']*0.035;
												}
											} else if($valpayInfo['assoc']['tme']['team_name']	==	"RD") {
												$incAmounnt	=	$valpayInfo['amount']*0.04;
											} else {
												$incAmounnt	=	$valpayInfo['amount']*0.04;
											}
										} else {
											$incAmounnt	=	$valpayInfo['amount']*0.04;
										}
									}
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'jdrr') {
									$incAmounnt	=	$valpayInfo['amount']*0.04;
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'omni') {
									if($empArr[$keyEmp]['emptype'] == 13) {
										$incAmounnt	=	$valpayInfo['amount']*0.08;
									} else {
										$incAmounnt	=	$valpayInfo['amount']*0.05;
									}
									if($valpayInfo['incentive_amount_online'] > 0) {
										$incAmountOnline	=	$valpayInfo['amount'] * 0.01;
									}
								} else if($valpayInfo['payment_type'] == 'ecs') {
									if($empArr[$keyEmp]['emptype'] == 13) {
										$gradeFinder	=	$empArr[$value]['grade'];
										if(substr($empArr[$value]['grade'],0,1) == 'g') {
											$gradeFinder	=	substr($empArr[$value]['grade'],1);
										}
										if($gradeFinder != '' && $gradeFinder != null) {
											$gradeFinder	=	(int)$gradeFinder;
											if($gradeFinder <= 10) {
												if($empArr[$value]['section']	==	'jda support') {
													$incAmounnt	=	$valpayInfo['amount']*0.04;
												}
											} else if($gradeFinder >= 11) {
												$incAmounnt	=	$valpayInfo['amount']*0.04;
											}
										}
									} else if($empArr[$keyEmp]['emptype'] == 3) {
										if(isset($valpayInfo['assoc']['tme'])) {
											if($valpayInfo['assoc']['tme']['team_name']	==	"SJ") {
												if($valpayInfo['assoc']['tme']['city_type'] == 1) {
													$incAmounnt	=	$valpayInfo['amount']*0.03;
												} else if($valpayInfo['assoc']['tme']['city_type'] == 2){
													$incAmounnt	=	$valpayInfo['amount']*0.04;
												}
											} else if($valpayInfo['assoc']['tme']['team_name']	==	"RD") {
												$incAmounnt	=	$valpayInfo['amount']*0.05;
											} else {
												$incAmounnt	=	$valpayInfo['amount']*0.05;
											}
										} else {
											$incAmounnt	=	$valpayInfo['amount']*0.05;
										}
									}
								}
							}
							if($valpayInfo['calc_flag']	==	'1') {
								$calc_flag = 1;
							} else {
								$calc_flag = 0;
							}
							if($valpayInfo['coldcall_tag']	==	1) {
								$insStr	.=	"('".$valpayInfo['parentid']."','".$valpayInfo['payment_type']."','".$keyIns."','".$valpayInfo['payment_source']."','".$valpayInfo['version']."','".$valpayInfo['amount']."','".$incAmounnt."','".$incAmountOnline."','".$valpayInfo['entry_date']."','".$valpayInfo['approval_date']."','".$keyEmp."','".$empArr[$keyEmp]['emptype']."','".addslashes(stripslashes($empArr[$keyEmp]['empname']))."','".$empArr[$keyEmp]['team_name']."','".$valpayInfo['data_city']."','".$empArr[$keyEmp]['city']."','".$empArr[$keyEmp]['maincity']."','".$value."','".$empArr[$keyEmp]['active']."','".addslashes(stripslashes($valpayInfo['compname']))."','0','','".$processCity."','','".$empArr[$keyEmp]['server_city']."','1','".$valEmp['perc']."','".$valEmp['clashedOn']."','".$valEmp['clashid']."','".$valEmp['emptype']."','1','".$empArr[$keyEmp]['city_type']."','0','".$calc_flag."'),";
							} else if($valpayInfo['coldcall_tag']	==	0) {
								if($empArr[$keyEmp]['emptype']	==	$valpayInfo['emptype']) {
									$insStr	.=	"('".$valpayInfo['parentid']."','".$valpayInfo['payment_type']."','".$keyIns."','".$valpayInfo['payment_source']."','".$valpayInfo['version']."','".$valpayInfo['amount']."','".$incAmounnt."','".$incAmountOnline."','".$valpayInfo['entry_date']."','".$valpayInfo['approval_date']."','".$keyEmp."','".$empArr[$keyEmp]['emptype']."','".addslashes(stripslashes($empArr[$keyEmp]['empname']))."','".$empArr[$keyEmp]['team_name']."','".$valpayInfo['data_city']."','".$empArr[$keyEmp]['city']."','".$empArr[$keyEmp]['maincity']."','".$value."','".$empArr[$keyEmp]['active']."','".addslashes(stripslashes($valpayInfo['compname']))."','0','','".$processCity."','','".$empArr[$keyEmp]['server_city']."','1','".$valEmp['perc']."','".$valEmp['clashedOn']."','".$valEmp['clashid']."','".$valEmp['emptype']."','1','".$empArr[$keyEmp]['city_type']."','0','".$calc_flag."'),";
								}
							}
						}
					}
					}
				}
			}
			
			$insert		=	"INSERT INTO tbl_contract_incentive_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname ,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,final_city,process_city,foundIn,server_emp_city,clash_flag,clash_perc,clashed_on,clashid,clash_emp_type,clashNew,main_remTag,reten_flag,calc_flag) VALUES ".substr($insStr,0,-1);
			$conInsert	=	$dbObjSSO->query($insert);
			
			
			$resTmeClashArr	=	array();
			$contArr		=	array();
			$n = 0;
			$selectTMEClash	=	"SELECT * FROM tbl_contract_incentive_back WHERE clash_flag = 1 AND emptype = 5 AND incentive_month = '".$value."' AND process_city = '".$processCity."'";
			$conSelectTMEClash	=	$dbObjSSO->query($selectTMEClash);
			while($resSelectClash	=	$dbObjSSO->fetchData($conSelectTMEClash)) {
				if(!isset($resTmeClashArr[$resSelectClash['payment_id']])) {
					$resTmeClashArr[$resSelectClash['payment_id']]	=	array();
				}
				$resTmeClashArr[$resSelectClash['payment_id']][$n]	=	array();
				$resTmeClashArr[$resSelectClash['payment_id']][$n]['data']	=	array();
				$resTmeClashArr[$resSelectClash['payment_id']][$n]['data']	=	$resSelectClash;
				$n++;
			}
			
			$m = 0;
			foreach($resTmeClashArr as $keyData => $valueData) {
				$selectMe	=	"SELECT * FROM tbl_contract_incentive_back WHERE (emptype = 3 OR emptype=13)  AND payment_id = '".$keyData."' AND incentive_month = '".$value."' AND process_city = '".$processCity."' AND clash_me_div = 0";
				$conSelectMe	=	$dbObjSSO->query($selectMe);
				
				while($resSelectME	=	$dbObjSSO->fetchData($conSelectMe)) {
					foreach($valueData as $keyTME=>$valueTME) {
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['payment_id'][$m]	=	$resSelectME['payment_id'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['payment_source'][$m]=	$resSelectME['payment_source'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['version'][$m]		=	$resSelectME['version'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['entry_date'][$m]	=	$resSelectME['entry_date'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['companyname'][$m]	=	$resSelectME['compname'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['approval_date'][$m]	=	$resSelectME['approval_date'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['data_city'][$m]		=	$resSelectME['data_city'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['clash_perc'][$m]	=	$valueTME['data']['clash_perc'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['clash_id'][$m]		=	$valueTME['data']['clashid'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['clash_flag'][$m]	=	1;
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['clashed_on'][$m]		=	$valueTME['data']['clashed_on'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['calc_flag'][$m]		=	$resSelectME['calc_flag'];
						
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['mename'][$m]		=	addslashes(stripslashes($resSelectME['empname']));
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['mecity'][$m]		=	$resSelectME['module_city'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['mecode'][$m]		=	$resSelectME['empcode'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['meact'][$m]			=	$resSelectME['active_flag'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['meEmpType'][$m]		=	$resSelectME['emptype'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['meTeamName'][$m]	=	$resSelectME['team_name'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['memaincity'][$m]	=	$resSelectME['emp_city'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['foundInMe'][$m]		=	$resSelectME['foundIn'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['server_cityMe'][$m]	=	$resSelectME['server_emp_city'];
						$contArr[$valueTME['data']['parentid']][$valueTME['data']['payment_type']]['city_typeMe'][$m]	=	$resSelectME['main_remTag'];
						if($valueTME['data']['payment_type'] == 'dp') {
							if($resSelectME['emptype'] == 3) {
								if($valueTME['data']['team_name']	==	"SJ") {
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]	=	array();
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['amount']	=	$valueTME['data']['amount'];
									if($valueTME['data']['main_remTag'] == 1) {
										$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.025;
										$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['percen']	=	0.025;
									} else if($valueTME['data']['main_remTag'] == 2) {
										$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.035;
										$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['percen']	=	0.035;
									}
								} else if($valueTME['data']['team_name']	==	"RD") {
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]=	array();
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['amount']	=	$valueTME['data']['amount'];
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.04;
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['percen']	=	0.04;
								} else {
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]	=	array();
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['amount']	=	$valueTME['data']['amount'];
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.04;
									$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['percen']	=	0.04;
								}
							} else {
								$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['amount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.04;
								$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['percen']	=	0.04;
							}
							if($valueTME['data']['incentive_amount_online'] > 0) {
								$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['onamount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['dp']['me'][$m]['oninc']	=	$valueTME['data']['amount']*0.01;
							}
						} else if($valueTME['data']['payment_type'] == 'jdrr') {
							$contArr[$valueTME['data']['parentid']]['jdrr']['me'][$m]	=	array();
							$contArr[$valueTME['data']['parentid']]['jdrr']['me'][$m]['amount']	=	$valueTME['data']['amount'];
							$contArr[$valueTME['data']['parentid']]['jdrr']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.04;
							if($valueTME['data']['incentive_amount_online'] > 0) {
								$contArr[$valueTME['data']['parentid']]['jdrr']['me'][$m]['onamount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['jdrr']['me'][$m]['oninc']	=	$valueTME['data']['amount']*0.01;
							}
						} else if($valueTME['data']['payment_type'] == 'omni') {
							$contArr[$valueTME['data']['parentid']]['omni']['me'][$m]	=	array();
							$contArr[$valueTME['data']['parentid']]['omni']['me'][$m]['amount']	=	$valueTME['data']['amount'];
							$contArr[$valueTME['data']['parentid']]['omni']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.05;
							if($valueTME['data']['incentive_amount_online'] > 0) {
								$contArr[$valueTME['data']['parentid']]['omni']['me'][$m]['onamount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['omni']['me'][$m]['oninc']	=	$valueTME['data']['amount']*0.01;
							}
						} else if($valueTME['data']['payment_type'] == 'ecs') {
							if($valueTME['data']['team_name']	==	"SJ") {
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]	=	array();
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['amount']	=	$valueTME['data']['amount'];
								if($valueTME['data']['main_remTag'] == 1) {
									$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.03;
									$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['percen']	=	0.03;
								} else if($valueTME['data']['main_remTag'] == 2) {
									$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.04;
									$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['percen']	=	0.04;
								}
							} else if($valueTME['data']['team_name']	==	"RD") {
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]=	array();
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['amount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.05;
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['percen']	=	0.05;
							} else {
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]	=	array();
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['amount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['incen']	=	$valueTME['data']['amount']*0.05;
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['percen']	=	0.05;
							}
							if($valueTME['data']['incentive_amount_online'] > 0) {
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['onamount']	=	$valueTME['data']['amount'];
								$contArr[$valueTME['data']['parentid']]['ecs']['me'][$m]['oninc']	=	$valueTME['data']['amount']*0.01;
							}
						}
						$m++;
					}
				}
				$deleteMe	=	"DELETE FROM tbl_contract_incentive_back WHERE (emptype = 3 OR emptype = 13) AND payment_id = '".$keyData."' AND incentive_month = '".$value."'";
				$conSelectMe	=	$dbObjSSO->query($deleteMe);
			}
			$insStr	=	"";
			foreach($contArr as $keyData=>$valueData) {
				foreach($valueData as $keyType=>$valueType) {
					if(isset($valueType['me'])) {
						foreach($valueType['me'] as $keyInc=>$valueInc) {
							$insStr	.=	"('".$keyData."','".$keyType."','".$valueType['payment_id'][$keyInc]."','".$valueType['payment_source'][$keyInc]."','".$valueType['version'][$keyInc]."','".$valueInc['amount']."','".$valueInc['incen']."','".$valueInc['oninc']."','".$valueType['entry_date'][$keyInc]."','".$valueType['approval_date'][$keyInc]."','".$valueType['mecode'][$keyInc]."','".$valueType['meEmpType'][$keyInc]."','".addslashes(stripslashes($valueType['mename'][$keyInc]))."','".$valueType['meTeamName'][$keyInc]."','".$valueType['data_city'][$keyInc]."','".$valueType['mecity'][$keyInc]."','".$valueType['memaincity'][$keyInc]."','".$value."','".$valueType['meact'][$keyInc]."','".addslashes(stripslashes($valueType['companyname'][$keyInc]))."','0','','".$processCity."','','".$valueType['server_cityMe'][$keyInc]."','".$valueType['clash_flag'][$keyInc]."','".$valueType['clash_perc'][$keyInc]."','".$valueType['clashed_on'][$keyInc]."','".$valueType['clash_id'][$keyInc]."','3','1','".$valueType['city_typeMe'][$keyInc]."','0','1','".$valueType['calc_flag'][$keyInc]."'),";
						}
					}
				}
			}
			echo "<pre>";
			//echo "<hr><hr><hr><hr><hr><hr>";
			//print_r($contArr);
			echo "<hr><hr><hr><hr><hr><hr>";
			if($insStr != '') {
				$insertNew		=	"INSERT INTO tbl_contract_incentive_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname ,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,final_city,process_city,foundIn,server_emp_city,clash_flag,clash_perc,clashed_on,clashid,clash_emp_type,clashNew,main_remTag,reten_flag,clash_me_div,calc_flag) VALUES ".substr($insStr,0,-1);
				$conInsert	=	$dbObjSSO->query($insertNew);
			} else {
				echo "Nothing to insert";
			}
			echo "Done for ".$value."<hr>";
			echo "Process City: ".$processCity."<hr>";
		}
	}
	
	public function processLateReturn() {
		ini_set('memory_limit', '-1');
		
		$dbObjSSO		=	new DB($this->db['db_sso']);
		//~ $drpTab	=	"DROP TABLE tbl_contract_incentive_debit_back";
		//~ $conDrp	=	$dbObjSSO->query($drpTab);
		
		$crTable		=	"CREATE TABLE IF NOT EXISTS `tbl_contract_incentive_debit_back` (`parentid` VARCHAR(100) DEFAULT NULL,`payment_type` VARCHAR(10) DEFAULT NULL,`payment_id` VARCHAR(100) DEFAULT NULL,`payment_source` VARCHAR(10) DEFAULT NULL,`version` VARCHAR(10) DEFAULT NULL,`amount` DOUBLE NOT NULL DEFAULT '0',`incentive_amount` DOUBLE NOT NULL DEFAULT '0',`incentive_amount_online` DOUBLE NOT NULL DEFAULT '0',`entry_date` DATETIME DEFAULT NULL,`approval_date` DATETIME DEFAULT NULL,`empcode` VARCHAR(25) DEFAULT NULL,`emptype` VARCHAR(10) DEFAULT NULL,`empname` VARCHAR(100) DEFAULT NULL,`team_name` VARCHAR(10) DEFAULT NULL,`data_city` VARCHAR(25) DEFAULT NULL,`module_city` VARCHAR(25) DEFAULT NULL,`emp_city` VARCHAR(25) DEFAULT NULL,`incentive_month` VARCHAR(30) DEFAULT '0000-00',`active_flag` TINYINT(1) DEFAULT NULL,`compname` VARCHAR(255) DEFAULT NULL,`coldcall_tag` TINYINT(1) DEFAULT '0',`final_city` VARCHAR(255) NOT NULL DEFAULT '',`process_city` VARCHAR(255) NOT NULL DEFAULT '',`foundIn` VARCHAR(255) NOT NULL DEFAULT '',`server_emp_city` VARCHAR(100) NOT NULL DEFAULT '',`clash_flag` TINYINT(1) DEFAULT '0',`clash_perc` INT(11) DEFAULT '100',`clashed_on` DATETIME DEFAULT '0000-00-00 00:00:00',`clashid` VARCHAR(100) DEFAULT NULL,`clash_emp_type` VARCHAR(25) DEFAULT NULL,`clashNew` TINYINT(1) DEFAULT '0', `main_remTag` TINYINT(1) DEFAULT '0', `reten_flag` TINYINT(1) DEFAULT '0', reten_on DATETIME DEFAULT '0000-00-00 00:00:00',`clash_me_div` TINYINT(1) DEFAULT '0', calc_flag TINYINT(1) DEFAULT 0,debit_flag varchar(10) DEFAULT NULL,debit_month VARCHAR(30) DEFAULT '0000-00',debit_flag_date DATETIME DEFAULT '0000-00-00 00:00:00',KEY `idx_parid` (`parentid`),KEY `idx_payid` (`payment_id`),KEY `idx_empcode` (`empcode`),KEY `idx_team` (`team_name`),KEY `idx_incentive_month` (`incentive_month`),KEY `idx_emp_city` (`emp_city`),KEY `idx_module_city` (`module_city`),KEY `idx_pay_type` (`payment_type`),KEY `idx_data_city` (`data_city`),KEY `idx_final_city` (`final_city`),KEY `idx_appr_date` (`approval_date`),KEY `idx_foundIn` (`foundIn`),KEY `idx_server_emp_city` (`server_emp_city`),KEY `clash_perc` (`clash_perc`),KEY `clashed_on` (`clashed_on`),KEY `clashid` (`clashid`),KEY `clash_emp_type` (`clash_emp_type`),KEY `debit_month` (`debit_month`),KEY `debit_flag`(debit_flag)) ENGINE=InnoDb DEFAULT CHARSET=latin1";
		//~ $conCrTable		=	$dbObjSSO->query($crTable);
		
		//~ $alterTab	=	"UPDATE tbl_contract_incentive SET late_ret_flag = 0,return_on = '0000-00-00 00:00:00'";
		//~ $cinAkterTab	=	$dbObjSSO->query($alterTab); die;
		
		$monthArr	=	array("2017-01","2017-02","2017-03","2017-04");
		foreach($monthArr as $value) {
			$month			=	$value;
			$month10		=	date("Y-m",strtotime("+1 month",strtotime($month)));
			$insStrEmp		=	"";
			$contArr		=	array();
			
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$maincityArr	=	array("mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad");
			$instrArr		=	array();
			$query			=	"SELECT instrumentids,campaigndetails,refund_amt,doneon,parentid FROM payment_refund_details WHERE refund_type ='Late Return' AND doneon >='".$value."-01 00:00:00' AND doneon <='".$month10."-10 23:59:59'";
			$con			=	$dbObjFin->query($query);
			$numData		=	$dbObjFin->numRows($con);
			if($numData > 0) {
				$instrumentStr	=	"";
				while($fetchData	=	$dbObjFin->fetchData($con)) {
					$instrArr[$fetchData['instrumentids']]['campaign']	=	$fetchData['campaigndetails'];
					$instrArr[$fetchData['instrumentids']]['refund_amt']	=	$fetchData['refund_amt'];
					$instrArr[$fetchData['instrumentids']]['doneon']	=	$fetchData['doneon'];
					$instrArr[$fetchData['instrumentids']]['parentid']	=	$fetchData['parentid'];
					$instrumentStr	.=	$fetchData['instrumentids']."','";
				}
				$instrumentStr	=	substr($instrumentStr,0,-2);
				$dbObjSSO		=	new DB($this->db['db_sso']);
				$insertStr		=	"";
				$selectInc	=	"SELECT * FROM tbl_contract_incentive_back WHERE payment_id IN ('".$instrumentStr.") AND late_ret_flag = 0";
				$conSelectInc	=	$dbObjSSO->query($selectInc);
				while($fetchIncData	=	$dbObjSSO->fetchData($conSelectInc)) {
					$monthPlus		=	date("Y-m",strtotime("+1 month",strtotime($fetchIncData['incentive_month'])));
					if(($instrArr[$fetchIncData['payment_id']]['doneon'] >= $fetchIncData['incentive_month']."-01 00:00:00" && $instrArr[$fetchIncData['payment_id']]['doneon'] <= $monthPlus."-10 23:59:59")) {
						$update	=	"UPDATE tbl_contract_incentive_back SET late_ret_flag = 1,return_on = '".$instrArr[$fetchIncData['payment_id']]['doneon']."' WHERE payment_id = '".$fetchIncData['payment_id']."'";
						$conUpd	=	$dbObjSSO->query($update);
					} else if((date("Y-m",strtotime($instrArr[$fetchIncData['payment_id']]['doneon'])) >= $fetchIncData['incentive_month'])) {
						$insertStr		.=	"('".$fetchIncData['parentid']."','".$fetchIncData['payment_type']."','".$fetchIncData['payment_id']."','".$fetchIncData['payment_source']."','".$fetchIncData['version']."','".$fetchIncData['amount']."','".$fetchIncData['incentive_amount']."','".$fetchIncData['incentive_amount_online']."','".$fetchIncData['entry_date']."','".$fetchIncData['approval_date']."','".$fetchIncData['empcode']."','".$fetchIncData['emptype']."','".$fetchIncData['empname']."','".$fetchIncData['team_name']."','".$fetchIncData['data_city']."','".$fetchIncData['module_city']."','".$fetchIncData['emp_city']."','".$fetchIncData['incentive_month']."','".$fetchIncData['active_flag']."','".$fetchIncData['compname']."','".$fetchIncData['coldcall_tag']."','".$fetchIncData['final_city']."','".$fetchIncData['process_city']."','".$fetchIncData['foundIn']."','".$fetchIncData['server_emp_city']."','".$fetchIncData['clash_flag']."','".$fetchIncData['clash_perc']."','".$fetchIncData['clashed_on']."','".$fetchIncData['clashid']."','".$fetchIncData['clash_emp_type']."','".$fetchIncData['clashNew']."','".$fetchIncData['main_remTag']."','".$fetchIncData['reten_flag']."','".$fetchIncData['reten_on']."','".$fetchIncData['clash_me_div']."','".$fetchIncData['calc_flag']."','1','".$value."','".$instrArr[$fetchIncData['payment_id']]['doneon']."'),";	
					}
				}
				
				if($insertStr != "") {
					$insertStr		=	substr($insertStr,0,-1);
					$insertValue	=	"INSERT INTO tbl_contract_incentive_debit_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,final_city,process_city,foundIn,server_emp_city,clash_flag,clash_perc,clashed_on,clashid,clash_emp_type,clashNew,main_remTag,reten_flag,reten_on,clash_me_div,calc_flag,debit_flag,debit_month,debit_flag_date) VALUES ".$insertStr;
					$conIns	=	$dbObjSSO->query($insertValue);
				}
			}
			$instrArr	=	array();
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$siQuery	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city,c.entry_date FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_si.si_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 3 AND b.billResponseDate >= '".$value."-01 00:00:00' AND b.billResponseDate <= '".$month10."-10 23:59:59' AND a.parentid != '' AND a.parentid = ''";
			$conSi		=	$dbObjFin->query($siQuery);
			if($dbObjFin->numRows($conSi) > 0) {
				$paymentIdStr	=	"";
				while($fetchDataSi	=	$dbObjFin->fetchData($conSi)) {
					$instrArr[$fetchDataSi['billDeskId']]['doneon']	=	$fetchDataSi['billResponseDate'];
					$instrArr[$fetchDataSi['billDeskId']]['parentid']	=	$fetchData['parentid'];
					$paymentIdStr	.=	$fetchDataSi['billDeskId']."','";
				}
				$paymentIdStr	=	substr($paymentIdStr,0,-2);
				$dbObjSSO		=	new DB($this->db['db_sso']);
				$insertStr		=	"";
				$selectInc	=	"SELECT * FROM tbl_contract_incentive_back WHERE payment_id IN ('".$paymentIdStr.") AND late_ret_flag = 0";
				$conSelectInc	=	$dbObjSSO->query($selectInc);
				while($fetchIncData	=	$dbObjSSO->fetchData($conSelectInc)) {
					$monthPlus		=	date("Y-m",strtotime("+1 month",strtotime($fetchIncData['incentive_month'])));
					if(($instrArr[$fetchIncData['payment_id']]['doneon'] >= $fetchIncData['incentive_month']."-01 00:00:00" && $instrArr[$fetchIncData['payment_id']]['doneon'] <= $monthPlus."-10 23:59:59")) {
						$update	=	"UPDATE tbl_contract_incentive_back SET late_ret_flag = 1,return_on = '".$instrArr[$fetchIncData['payment_id']]['doneon']."' WHERE payment_id = '".$fetchIncData['payment_id']."'";
						$conUpd	=	$dbObjSSO->query($update);
					} else if((date("Y-m",strtotime($instrArr[$fetchIncData['payment_id']]['doneon'])) >= $fetchIncData['incentive_month'])) {
						$insertStr		.=	"('".$fetchIncData['parentid']."','".$fetchIncData['payment_type']."','".$fetchIncData['payment_id']."','".$fetchIncData['payment_source']."','".$fetchIncData['version']."','".$fetchIncData['amount']."','".$fetchIncData['incentive_amount']."','".$fetchIncData['incentive_amount_online']."','".$fetchIncData['entry_date']."','".$fetchIncData['approval_date']."','".$fetchIncData['empcode']."','".$fetchIncData['emptype']."','".$fetchIncData['empname']."','".$fetchIncData['team_name']."','".$fetchIncData['data_city']."','".$fetchIncData['module_city']."','".$fetchIncData['emp_city']."','".$fetchIncData['incentive_month']."','".$fetchIncData['active_flag']."','".$fetchIncData['compname']."','".$fetchIncData['coldcall_tag']."','".$fetchIncData['final_city']."','".$fetchIncData['process_city']."','".$fetchIncData['foundIn']."','".$fetchIncData['server_emp_city']."','".$fetchIncData['clash_flag']."','".$fetchIncData['clash_perc']."','".$fetchIncData['clashed_on']."','".$fetchIncData['clashid']."','".$fetchIncData['clash_emp_type']."','".$fetchIncData['clashNew']."','".$fetchIncData['main_remTag']."','".$fetchIncData['reten_flag']."','".$fetchIncData['reten_on']."','".$fetchIncData['clash_me_div']."','".$fetchIncData['calc_flag']."','1','".$value."','".$instrArr[$fetchIncData['payment_id']]['doneon']."'),";	
					}
				}
				
				if($insertStr != "") {
					$insertStr		=	substr($insertStr,0,-1);
					$insertValue	=	"INSERT INTO tbl_contract_incentive_debit_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,final_city,process_city,foundIn,server_emp_city,clash_flag,clash_perc,clashed_on,clashid,clash_emp_type,clashNew,main_remTag,reten_flag,reten_on,clash_me_div,calc_flag,debit_flag,debit_month,debit_flag_date) VALUES ".$insertStr;
					$conIns	=	$dbObjSSO->query($insertValue);
				}
			}
			
			$instrArr	=	array();
			$dbObjFin		=	new DB($this->db['db_finance_slave']);
			$ecsQuery	=	"SELECT a.billDeskId,a.parentid,a.billAmount,a.service_tax,c.companyname,c.tmecode,c.tmename,c.mecode,c.mename,a.version,b.billGenerateDate,b.billResponseDate,a.data_city,c.entry_date FROM db_ecs_billing.ecs_bill_details a JOIN db_ecs_billing.ecs_bill_clearance_details b ON a.billnumber = b.billNumber JOIN  db_ecs.ecs_mandate c ON (a.billdeskId = c.billdeskId ) WHERE b.billresponsestatus = 3 AND b.billResponseDate >= '".$value."-01 00:00:00' AND b.billResponseDate <= '".$month10."-10 23:59:59' AND a.parentid != '' AND a.parentid = ''";
			$conEcs		=	$dbObjFin->query($ecsQuery);
			if($dbObjFin->numRows($conEcs) > 0) {
				$paymentIdStr	=	"";
				while($fetchDataEcs	=	$dbObjFin->fetchData($conEcs)) {
					$instrArr[$fetchDataEcs['billDeskId']]['doneon']	=	$fetchDataSi['billResponseDate'];
					$instrArr[$fetchDataEcs['billDeskId']]['parentid']	=	$fetchData['parentid'];
					$paymentIdStr	.=	$fetchDataEcs['billDeskId']."','";
				}
				$paymentIdStr	=	substr($paymentIdStr,0,-2);
				$dbObjSSO		=	new DB($this->db['db_sso']);
				$insertStr		=	"";
				$selectInc	=	"SELECT * FROM tbl_contract_incentive_back WHERE payment_id IN ('".$paymentIdStr.") AND late_ret_flag = 0";
				$conSelectInc	=	$dbObjSSO->query($selectInc);
				while($fetchIncData	=	$dbObjSSO->fetchData($conSelectInc)) {
					$monthPlus		=	date("Y-m",strtotime("+1 month",strtotime($fetchIncData['incentive_month'])));
					if(($instrArr[$fetchIncData['payment_id']]['doneon'] >= $fetchIncData['incentive_month']."-01 00:00:00" && $instrArr[$fetchIncData['payment_id']]['doneon'] <= $monthPlus."-10 23:59:59")) {
						$update	=	"UPDATE tbl_contract_incentive_back SET late_ret_flag = 1,return_on = '".$instrArr[$fetchIncData['payment_id']]['doneon']."' WHERE payment_id = '".$fetchIncData['payment_id']."'";
						$conUpd	=	$dbObjSSO->query($update);
					} else if((date("Y-m",strtotime($instrArr[$fetchIncData['payment_id']]['doneon'])) >= $fetchIncData['incentive_month'])) {
						$insertStr		.=	"('".$fetchIncData['parentid']."','".$fetchIncData['payment_type']."','".$fetchIncData['payment_id']."','".$fetchIncData['payment_source']."','".$fetchIncData['version']."','".$fetchIncData['amount']."','".$fetchIncData['incentive_amount']."','".$fetchIncData['incentive_amount_online']."','".$fetchIncData['entry_date']."','".$fetchIncData['approval_date']."','".$fetchIncData['empcode']."','".$fetchIncData['emptype']."','".$fetchIncData['empname']."','".$fetchIncData['team_name']."','".$fetchIncData['data_city']."','".$fetchIncData['module_city']."','".$fetchIncData['emp_city']."','".$fetchIncData['incentive_month']."','".$fetchIncData['active_flag']."','".$fetchIncData['compname']."','".$fetchIncData['coldcall_tag']."','".$fetchIncData['final_city']."','".$fetchIncData['process_city']."','".$fetchIncData['foundIn']."','".$fetchIncData['server_emp_city']."','".$fetchIncData['clash_flag']."','".$fetchIncData['clash_perc']."','".$fetchIncData['clashed_on']."','".$fetchIncData['clashid']."','".$fetchIncData['clash_emp_type']."','".$fetchIncData['clashNew']."','".$fetchIncData['main_remTag']."','".$fetchIncData['reten_flag']."','".$fetchIncData['reten_on']."','".$fetchIncData['clash_me_div']."','".$fetchIncData['calc_flag']."','1','".$value."','".$instrArr[$fetchIncData['payment_id']]['doneon']."'),";	
					}
				}
				
				if($insertStr != "") {
					$insertStr		=	substr($insertStr,0,-1);
					$insertValue	=	"INSERT INTO tbl_contract_incentive_debit_back (parentid,payment_type,payment_id,payment_source,version,amount,incentive_amount,incentive_amount_online,entry_date,approval_date,empcode,emptype,empname,team_name,data_city,module_city,emp_city,incentive_month,active_flag,compname,coldcall_tag,final_city,process_city,foundIn,server_emp_city,clash_flag,clash_perc,clashed_on,clashid,clash_emp_type,clashNew,main_remTag,reten_flag,reten_on,clash_me_div,calc_flag,debit_flag,debit_month,debit_flag_date) VALUES ".$insertStr;
					$conIns	=	$dbObjSSO->query($insertValue);
				}
			}
			
			echo "Done For ==".$value."<hr>";
		}
	}
	public function updateTable() {
		$dbObj	=	new DB($this->db['db_local']);		
		$dbObjFin		=	new DB($this->db['db_finance_slave']);		
		$query	=	"SELECT parentid FROM allocation.tbl_dataAnalysis_main WHERE paidstatus ='1'";
		$con	=	$dbObj->query($query);
		while($res		=	$dbObj->fetchData($con)) {
			$strParId	.=	$res['parentid']."','";			
		}
		$strParId	=	substr($strParId,0,-2);
		$selInsId	=	"SELECT instrumentid,finalApprovalDate,parentid,instrumentAmount FROM db_finance.contract_payment_details WHERE parentid IN ('".$strParId.")";
		$insID	=	$dbObjFin->query($selInsId);
		$instrIdArr	=	array();
		while($resINS	=	$dbObjFin->fetchData($insID)) {
			//$instrIdArr[$resINS['parentid']]['data']	.=	$resINS['instrumentid']."~".$resINS['finalApprovalDate'].",";
			$instrIdArr[$resINS['parentid']]['sum']		=	$instrIdArr[$resINS['parentid']]['sum']+$resINS['instrumentAmount'];
		}
		foreach($instrIdArr as $key=>$value) {	
			$value['sum']		=	substr($value['sum'],0,-1);
			$updData	=	"UPDATE allocation.tbl_dataAnalysis_main SET contractValue = '".$value['sum']."' WHERE parentid = '".$key."'";
			$conUpd		=	$dbObj->query($updData);
		}
	}	
}
