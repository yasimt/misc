<?php
require_once('class_send_sms_email.php');
class setAllData extends DB
{

	var  $conn_idc    	= null;
	var  $params  		= null;
	var  $parentid		= null;
	var  $data_city		= null;
	var  $module		= null;
	//var  $from_date	= null;
	var  $to_date		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
		
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$data_city 			= trim($params['data_city']);
		$module 			= trim($params['module']);
		$this->parentid  	= $parentid;
		$this->data_city  	= $data_city;
		$this->module  		= $module;
		//$this->from_date 	= trim($params['fdate']);
		$this->to_date  	= trim($params['tdate']);
				
		/*mongo*/
		$this->mongo_flag 	= 0;
		$this->mongo_obj 	= new MongoClass();
	}
	
	
	function setbulkdata()
	{
		if(trim($this->parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode(array("error"=>1,"message"=>$message));
            die();
        }
        
        if(trim($this->data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode(array("error"=>1,"message"=>$message));
			die();
		}
		
		if(trim($this->module)=='')
		{
			$message = "Module is blank.";
			echo json_encode(array("error"=>1,"message"=>$message));
			die();
		}
        
        global $db;
        if((strtolower($this->module) == 'me') || (strtolower($this->module) == 'jda')){
			$conn_temp = $db[$this->data_city]['idc']['master'];
		}else if(strtolower($this->module) == 'tme'){
			$conn_temp = $db[$this->data_city]['tme_jds']['master'];
		}
        
		
		$mongo_inputs = array();
		$mongo_inputs['parentid'] = $this->parentid;
		$mongo_inputs['data_city'] = $this->data_city;
		$mongo_inputs['module'] = $this->module;
		
		$sqlFetchGenInfo = "SELECT * FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$this->parentid."'";
		$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
		if($resFetchGenInfo && parent::numRows($resFetchGenInfo)){
			
			$row_gen_info	=	parent::fetchData($resFetchGenInfo);
			unset($row_gen_info['parentid']);
			$geninfo_tbl = 'tbl_companymaster_generalinfo_shadow';
			$mongo_data[$geninfo_tbl]['updatedata'] = $row_gen_info;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sqlFetchExtraDetails = "SELECT * FROM tbl_companymaster_extradetails_shadow WHERE parentid = '".$this->parentid."'";
		$resFetchExtraDetails 	= parent::execQuery($sqlFetchExtraDetails, $conn_temp);
		if($resFetchExtraDetails && parent::numRows($resFetchExtraDetails)){
			
			$row_ext_details	=	parent::fetchData($resFetchExtraDetails);
			unset($row_ext_details['parentid']);
			$extrdet_tbl = 'tbl_companymaster_extradetails_shadow';
			$mongo_data[$extrdet_tbl]['updatedata'] = $row_ext_details;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sql2 = "SELECT * FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."'";
		$res2 	= parent::execQuery($sql2, $conn_temp);
		if($res2 && parent::numRows($res2)){
			
			$row2	=	parent::fetchData($res2);
			unset($row2['parentid']);
			$intermd_tbl = 'tbl_temp_intermediate';
			$mongo_data[$intermd_tbl]['updatedata'] = $row2;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		
		$sql3 = "SELECT * FROM tbl_business_temp_data WHERE contractid = '".$this->parentid."'";
		$res3 	= parent::execQuery($sql3, $conn_temp);
		if($res3 && parent::numRows($res3)){
			
			$row3	=	parent::fetchData($res3);
			unset($row3['contractid']);
			$bustemp_tbl = 'tbl_business_temp_data';
			$mongo_data[$bustemp_tbl]['updatedata'] = $row3;
			$mongo_inputs['table_data'] = $mongo_data;
		}
		if((count($mongo_inputs)>0) && (count($mongo_inputs['table_data'])>0)){
			$res = $this->mongo_obj->updateData($mongo_inputs);
		}
		return $res;
	}
	
	
	function setallbulkdata()
	{
		for($day = 5;$day <=18; $day ++)
		{
			$this->setDataDayWise($day);
		}
	}
	
	
	function setDataDayWise($day)
	{
        if(trim($this->data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode(array("error"=>1,"message"=>$message));
			die();
		}
		
		if(trim($this->module)=='')
		{
			$message = "Module is blank.";
			echo json_encode(array("error"=>1,"message"=>$message));
			die();
		}
        $this->data_city = strtolower($this->data_city);
        global $db;
        if((strtolower($this->module) == 'me') || (strtolower($this->module) == 'jda')){
			$conn_temp = $db[$this->data_city]['idc']['master'];
		}else if(strtolower($this->module) == 'tme'){
			$conn_temp = $db[$this->data_city]['tme_jds']['master'];
		}
		
		$qrydate = "2018-01"."-".$day;
		$sql = 'SELECT * FROM tbl_companymaster_extradetails_shadow WHERE updatedOn BETWEEN "'.$qrydate.' 00:00:00" AND "'.$qrydate.' 23:59:59"';
		$res = parent::execQuery($sql, $conn_temp);
		if($res && parent::numRows($res))
		{
			$num_row = parent::numRows($res);
			$arr = array();
			while($row = parent::fetchData($res))
			{
				if($row['parentid'])
				{
					$parentid = $row['parentid'];
					$mongo_inputs = array();
					$mongo_data = array();
					$mongo_inputs['parentid'] = $parentid;
					$mongo_inputs['data_city'] = $this->data_city;
					$mongo_inputs['module'] = $this->module;
					
					unset($row['parentid']);
					$extrdet_tbl = 'tbl_companymaster_extradetails_shadow';
					$mongo_data[$extrdet_tbl]['updatedata'] = $row;
					$mongo_inputs['table_data'] = $mongo_data;
					
					$sqlFetchGenInfo = "SELECT * FROM tbl_companymaster_generalinfo_shadow WHERE parentid = '".$parentid."'";
					$resFetchGenInfo 	= parent::execQuery($sqlFetchGenInfo, $conn_temp);
					if($resFetchGenInfo && parent::numRows($resFetchGenInfo))
					{
						$row_gen_info	=	parent::fetchData($resFetchGenInfo);
						unset($row_gen_info['parentid']);
						$geninfo_tbl = 'tbl_companymaster_generalinfo_shadow';
						$mongo_data[$geninfo_tbl]['updatedata'] = $row_gen_info;
						$mongo_inputs['table_data'] = $mongo_data;
					}
					
					$sql2 = "SELECT * FROM tbl_temp_intermediate WHERE parentid = '".$parentid."'";
					$res2 	= parent::execQuery($sql2, $conn_temp);
					if($res2 && parent::numRows($res2))
					{
						$row2	=	parent::fetchData($res2);
						unset($row2['parentid']);
						$intermd_tbl = 'tbl_temp_intermediate';
						$mongo_data[$intermd_tbl]['updatedata'] = $row2;
						$mongo_inputs['table_data'] = $mongo_data;
					}
					
					$sql3 = "SELECT * FROM tbl_business_temp_data WHERE contractid = '".$parentid."'";
					$res3 	= parent::execQuery($sql3, $conn_temp);
					if($res3 && parent::numRows($res3))
					{
						$row3	=	parent::fetchData($res3);
						unset($row3['contractid']);
						$bustemp_tbl = 'tbl_business_temp_data';
						$mongo_data[$bustemp_tbl]['updatedata'] = $row3;
						$mongo_inputs['table_data'] = $mongo_data;
					}
					if((count($mongo_inputs)>0) && (count($mongo_inputs['table_data'])>0)){
						$this->mongo_obj->updateData($mongo_inputs);
					}
					unset($mongo_inputs);
					unset($mongo_inputs['table_data']);
					unset($mongo_data);
					unset($row);
					unset($row_gen_info);
					unset($row2);
					unset($row3);
				}
			}
			
			//$mailto 	= "manjeet.singh1@justdial.com";
			$mailto 	= "imteyaz.raja@justdial.com,manjeet.singh1@justdial.com";
			$subject 	= "mongo data migration";
			$message 	= "data migration is done for ".$this->data_city." - date - ".$qrydate;
			$from 		= "noreply@justdial.com";
			
			global $db;
			$emailsms_obj = new email_sms_send($db,'mumbai');
			if($emailsms_obj){
				$mailing = $emailsms_obj->sendEmail($mailto, $from, $subject, $message , 'docbform');
			}
			return "success";
		}
	}
	
	function getMatchData($params){
		//return $params;
		$mainarr = array();
		$mob_arr = array();
		if(array_key_exists('mobile',$params))
		{
			if(!empty($params['mobile']))
			{
				$mobiles = explode(",",$params['mobile']);
				foreach($mobiles as $m)
				{
					$get_match = array(
						"table"         =>"tbl_companymaster_generalinfo_shadow",
						"data_city"     =>strtolower($params['data_city']),
						"module"        =>"ME",
						"fields"        =>"parentid,mobile,landline,full_address,building_name,companyname,data_city,display_flag,expired_flag,expired_on,landmark,paidstatus,pincode,phone",
						"like"          =>json_encode(array("mobile"=>$m)),
						"limit"         =>100
					);
					$resdata = $this->mongo_obj->getDataMatch($get_match);
					if(!empty($resdata))
						$mob_arr[$m] = $resdata;
				}
			}
		}

		$landline_arr = array();
		if(array_key_exists('landline',$params))
		{
			if(!empty($params['landline']))
			{
				$landlines = explode(",",$params['landline']);
				foreach($landlines as $l)
				{
					$get_match_l = array(
						"table"         =>"tbl_companymaster_generalinfo_shadow",
						"data_city"     =>strtolower($params['data_city']),
						"module"        =>"ME",
						"fields"        =>"parentid,mobile,landline,full_address,building_name,companyname,data_city,display_flag,expired_flag,expired_on,landmark,paidstatus,pincode,phone",
						"like"          =>json_encode(array("landline"=>$l)),
						"limit"         =>100
					);
					$resdata_l = $this->mongo_obj->getDataMatch($get_match_l);
					if(!empty($resdata_l))
						$landline_arr[$l] = $resdata_l;
				}
			}
		}
		
		$mob_data = array();
		foreach($mob_arr as $val)
		{
			foreach($val as $ineval)
			{
				array_push($mob_data,$ineval);
			}
		}
		
		$landline_data = array();
		foreach($landline_arr as $val_l)
		{
			foreach($val_l as $ineval_l)
			{
				array_push($landline_data,$ineval_l);
			}
		}
		
		$mainarr = array_merge($landline_data,$mob_data);
		$input = array_map("unserialize", array_unique(array_map("serialize", $mainarr)));
		return array_values($input);
	}

}

?>
