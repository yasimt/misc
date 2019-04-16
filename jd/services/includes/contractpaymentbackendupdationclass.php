<?php

class contractpaymentbackendupdationclass extends DB
{
var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	

	function __construct($params)
	{		
		$this->params = $params;
		$this->setServers();
	}
	
	function setServers()
	{	
		global $db;
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->fin_master_con = $db[$data_city]['fin']['master'];
	}

function process()
{
	
$sql = "DROP table IF EXISTS payment_instrument_allparentid ";
parent::execQuery($sql, $this->fin_master_con);

$allcontract_sql = "create table IF NOT EXISTS payment_instrument_allparentid  (UNIQUE (instrumentid)) (select instrumentid,approvalstatus,0 as done_flag  from payment_instrument_summary where  (entry_date >'2016-05-01 00:00:00'  or depositDate>'2016-05-01 00:00:00')  and approvalstatus!=2) ";
parent::execQuery($allcontract_sql, $this->fin_master_con);

// now we have to update the table 

$allcontract_sql = "select * from payment_instrument_allparentid where done_flag=0";
$allcontract_res = parent::execQuery($allcontract_sql, $this->fin_master_con);

$counter=1;
 
	while($allcontract_arr= mysql_fetch_assoc($allcontract_res))
	{
		$counter++;
		
		$instrumentid= $allcontract_arr['instrumentid'];
		$this->params['instrumentid']=$instrumentid;

		$this->update_done_flag($this->fin_master_con,$instrumentid,9);
		
		$cpsclass_obj = new contractpaymentserviceclass($this->params);

		$functio='';
		if($allcontract_arr['approvalstatus']==1)
		{
			$result = $cpsclass_obj->updatepaymentdetailsapproval();
			$functio='updatepaymentdetailsapproval';
		}else
		{
			$result = $cpsclass_obj->updatepaymentdetailsdealclose();
			$functio='updatepaymentdetailsdealclose';
		}

		//echo "<pre><br> instrumentid:-".$this->params['instrumentid']. "---functio:-".$functio."---approvalstatus:".$allcontract_arr['approvalstatus'];
		
		$this->update_done_flag($this->fin_master_con,$instrumentid,1);
		unset($instrumentid);
		unset($cpsclass_obj);
		
	}
}



function update_done_flag($conn_fin,$instrumentid,$done_flag)
{
	$update_sql= "update payment_instrument_allparentid set done_flag=".$done_flag." where instrumentid='".$instrumentid."' ";
	parent::execQuery($update_sql, $conn_fin);
} 

}
