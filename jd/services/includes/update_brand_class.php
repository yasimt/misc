<?
class brandUpdateClass extends DB {
    var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
    public function __construct($params){
		$data_city 	= trim($params['data_city']);
		$parentid 	= trim($params['parentid']);
        $ucode 		= trim($params['ucode']);
        $uname 		= trim($params['uname']);
        $ct_name 	= trim($params['ct_name']);
		$dept   	= trim($params['dept']);
        $paid_flag 	    = trim($params['paid']);
    
        $new_company_name 	= trim($params['new_company']);
        $old_company_name 	= trim($params['old_company']);
        		
		if($parentid==''){
			$message = "Parentid is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
		}
		if($data_city==''){
			$message = "data_city is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
		}
		if($dept==''){
			$message = "dept is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
        if($ucode==''){
			$message = "ucode is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
        if($uname==''){
			$message = "uname is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
        if($ct_name==''){
			$message = "city is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
        if($new_company_name==''){
			$message = "new_company name is blank.";
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
        if(preg_match('/[\x00-\x1F\x80-\xFF]/', $new_company_name)){           
            $message =  "Non-UTF Character Found";            
            echo json_encode($this->sendDieMsg($message,1));
            die();
        }
		$this->parentid     = $parentid;
		$this->dept 	    = strtolower($dept);
		$this->ct_name 	    = $ct_name;
		$this->paid_flag    = $paid_flag;
		$this->data_city    = strtolower($data_city);
        $this->ucode        = $ucode;
        $this->uname        = $uname;
        
        $this->brand_name       = $new_company_name;
        $this->old_company_name = $old_company_name;
		$this->setServers();	// Initiate Database connection
		
	}
    private function setServers()
	{	
		GLOBAL $db;			
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		
		$this->remote_flag = ((in_array(strtolower($this->data_city), $this->dataservers)) ? 0 : 1);	
    }
    private function fn_stemming($word)
    {
        $string = strtolower($word); 
        $word = preg_replace("/[^A-Za-z0-9\s]/", " ", $string);
        return $word;
    }
    private function sendDieMsg($msg)
	{
		$die_msg_arr = array();
		$die_msg_arr['error']['code'] = 1;
		$die_msg_arr['error']['message'] = $msg;
		return $die_msg_arr;
    }
    public function updateBrandName(){
        $de_remotezone	= 0;
		$de_tourist		= 0;
		$mapped_cityname	= '';
		if($this->remote_flag ==1){				
			$sql_get_zones  = "SELECT de_remotezone,de_tourist,mapped_cityname FROM tbl_city_master WHERE ct_name ='".$this->ct_name."' ";
			$res_get_zones =   parent::execQuery($sql_get_zones,$this->conn_local);
			if(parent::numRows($res_get_zones)>0){
				$row_get_zones 	=	parent::fetchData($res_get_zones);
				$de_remotezone 	= trim($row_get_zones['de_remotezone']);
				$de_tourist 	= trim($row_get_zones['de_tourist']);
				$mapped_cityname 	= trim($row_get_zones['mapped_cityname']);
			}
        }
        if(trim($this->paid_flag) != 1 && trim($this->parentid) !='')
		{
			$update_status = 0;
			$date= date("Y-m-d H:i:s");
            $compname_flag = 0;
            
            // echo $this->old_company_name;
            // echo "<br>new naem";echo $this->brand_name;
            // echo "<br>";
			if(trim($this->old_company_name) !='')
			{
				if(trim($this->old_company_name) == trim($this->brand_name))
				{
					$compname_flag = 1;
				}
            }
            $brand_name_stemmed = $this->fn_stemming($this->brand_name);

			$companystr 	= $brand_name_stemmed;
			$sqlBrandMatch 	= "SELECT GROUP_CONCAT(brand_name separator '|~|') as brand_name, GROUP_CONCAT(source separator '|~|') as source FROM tbl_brand_names WHERE MATCH(brand_name) AGAINST('".$companystr."' IN BOOLEAN MODE) LIMIT 1";
			$resBrandMatch = parent::execQuery($sqlBrandMatch,$this->conn_iro);
			if($resBrandMatch && parent::numRows($resBrandMatch)>0)
			{
				$row_matched_brand = parent::fetchData($resBrandMatch);
				$bnameval 		= trim($row_matched_brand['brand_name']);
				$bnameval 		= strtolower($bnameval);
				$bsource 		= trim($row_matched_brand['source']);
				$brand_name_arr = explode("|~|",$bnameval);
				$source_arr 	= explode("|~|",$bsource);
				$matchedname 	= '';
				$brand_source 	= ''; 
				if(count($brand_name_arr)>0){
					foreach($brand_name_arr as $key => $value){
						if(strpos($companystr, $value) !== false) {
							$matchedname = $value;
							$brand_source = $source_arr[$key];
							break;
						}
					}
				}
				#echo "Matched Brand : ".$matchedname."<br>Brand Source : ".$brand_source;die;			
				$exist_contract_flag = 0;
				if($matchedname){				
					if(trim($this->old_company_name) !=''){
						$sqlContractInfo = "SELECT parentid,companyname,done_flag FROM tbl_company_brandname_audit WHERE parentid='".$this->parentid."' AND companyname = '".trim($this->old_company_name)."'";
						$resContractInfo = parent::execQuery($sqlContractInfo,$this->conn_iro);
						if($resContractInfo && parent::numRows($resContractInfo)>0){
							$exist_contract_flag = 1;
							$row_brand_name = parent::fetchData($resContractInfo);
							$done_flag 		= trim($row_brand_name['done_flag']);
						}
					}
				}
            }
            // echo "exist---".$exist_contract_flag;
            // echo "compname_flag---".$compname_flag;
            // echo "done_flag---".$done_flag;
            
			$reason= 'Company Name Matches With Brand Name';
			if($matchedname && $brand_source)  
			{
				if(trim($exist_contract_flag) == 0 && trim($compname_flag) != 1)  // For New Contract Matching With Brand Name
				{
					$update_status = 1;
					$comment = "New Contract Matching With Brand Name";
					$sql_insrt_brand_name="INSERT INTO tbl_company_brandname_audit(parentid, companyname, createdby, creator_name, creationdate, department, done_flag, city, brand_source,matched_brand_name,de_remotezone,de_tourist,mapped_cityname) VALUES ('".$this->parentid."', '".addslashes($this->brand_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$this->dept."', '1', '".addslashes($this->ct_name)."', '".$brand_source."','".addslashes($matchedname)."','".$de_remotezone."','".$de_tourist."','".addslashes($mapped_cityname)."')";
					$res_insrt_brand_name = parent::execQuery($sql_insrt_brand_name,$this->conn_iro);

					
					$sql_brand_name_log = "INSERT INTO tbl_company_brandname_audit_log(parentid, companyname, modifiedby, modifier_name, modifieddate, department, done_flag, paid_status, data_city, comment,matched_brand_name,brand_source,old_company_name,received_date) VALUE('".$this->parentid."', '".addslashes($this->brand_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$dept."', '1', '".$this->paid_flag."', '".addslashes($this->ct_name)."','".$comment."','".addslashes($matchedname)."','".addslashes($brand_source)."','".addslashes($this->old_company_name)."','".$date."')";
					$res_brand_name_log = parent::execQuery($sql_brand_name_log,$this->conn_iro);
				}
				else if((trim($exist_contract_flag) == 1) && (trim($done_flag) !=0) ) // For NonApproved Contract Exists In Aud.Module
				{
					$update_status =1;
					$comment = "Non-Approved Contract Edited";

					$sql_get_createdate =	"SELECT creationdate FROM tbl_company_brandname_audit where parentid='".$this->parentid."' AND companyname = '".trim(addslashes($this->old_company_name))."'";
					$res_get_createdate =  parent::execQuery($sql_get_createdate,$this->conn_iro);

					if(parent::numRows($res_get_createdate)>0){
						$row_getdate =	parent::fetchData($res_get_createdate);
						$creationdate	=	trim($row_getdate['creationdate']);
					}

					$sql_updt_brand_name = "UPDATE tbl_company_brandname_audit SET modifiedby='".$this->ucode."', modifier_name='".$this->uname."', donedate='".$date."', companyname = '".addslashes($this->brand_name)."', city = '".addslashes($this->ct_name)."', brand_source = '".$brand_source."',matched_brand_name ='".addslashes($matchedname)."',de_remotezone='".$de_remotezone."',de_tourist='".$de_tourist."',mapped_cityname='".addslashes($mapped_cityname)."' where parentid='".$this->parentid."' AND companyname = '".trim(addslashes($this->old_company_name))."'";
					$res_updt_brand_name = parent::execQuery($sql_updt_brand_name,$this->conn_iro);

					
					$sql_brand_name_log = "INSERT INTO tbl_company_brandname_audit_log(parentid, companyname, modifiedby, modifier_name, modifieddate, department, done_flag, paid_status, data_city, comment,matched_brand_name,brand_source,old_company_name,received_date) VALUE('".$this->parentid."', '".addslashes($this->brand_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$this->dept."', '".$done_flag."', '".$this->paid_flag."', '".addslashes($this->ct_name)."', '".$comment."','".addslashes($matchedname)."','".addslashes($brand_source)."','".addslashes($this->old_company_name)."','".$creationdate."')";
					$res_brand_name_log = parent::execQuery($sql_brand_name_log,$this->conn_iro);
				}
				else if((trim($exist_contract_flag) == 1) && (trim($done_flag) == 0) && (trim($compname_flag) != 1)) // Approved 
				{
					$update_status = 1;
					$comment = "Approved Contract Again Matching With Brand Name";
					$sql_insrt_brand_name="INSERT INTO tbl_company_brandname_audit(parentid, companyname, createdby, creator_name, creationdate, department, done_flag, city, modifiedby, modifier_name, donedate, brand_source,matched_brand_name,de_remotezone,de_tourist,mapped_cityname) VALUES ('".$this->parentid."', '".addslashes($this->brand_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$this->dept."', '1', '".addslashes($this->ct_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$brand_source."','".addslashes($matchedname)."','".$de_remotezone."','".$de_tourist."','".addslashes($mapped_cityname)."')";
					$res_insrt_brand_name = parent::execQuery($sql_insrt_brand_name,$this->conn_iro);

					
					$sql_brand_name_log = "INSERT INTO tbl_company_brandname_audit_log(parentid, companyname, modifiedby, modifier_name, modifieddate, department, done_flag, paid_status, data_city, comment,matched_brand_name,brand_source,old_company_name,received_date) VALUE('".$this->parentid."', '".addslashes($this->brand_name)."', '".$this->ucode."', '".addslashes($this->uname)."', '".$date."', '".$this->dept."', '1', '".$this->paid_flag."' , '".addslashes($this->ct_name)."', '".$comment."','".addslashes($matchedname)."','".addslashes($brand_source)."','".addslashes($this->old_company_name)."','".$date."')";
					$res_brand_name_log = parent::execQuery($sql_brand_name_log,$this->conn_iro);
				}
            }           	
            $res_arr['update_status'] = $update_status;	
        }
        else{
            $res_arr['error']['code'] = 1;
            $res_arr['update_status'] = 0;	
        }
        echo json_encode($res_arr);
    }
    
}

?>