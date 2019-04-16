<?
//paren_docid - hosp_docid
//hospital_name
//loc_docid - Doctor Docid from Autosuggest
//doctor_name
//ref_parentid - Hospital Parentid
class hosp_multiple_doctor  extends DB
{
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
	
	var $conn_iro   = '';
	var	$conn_local = '';

	function __construct($params){		
        //echo "<pre>";print_r($params);
        $parent_docid       = trim($params['parent_docid']);
        $hospital_name      = trim($params['hospital_name']);    
        $doctor_name        = trim($params['doctor_name']);
        $data_city          = trim($params['data_city']);
        $national_catid     = trim($params['national_catid']);
        $insert_ignore_flag = trim($params['insert_ignore_flag']);
        if(isset($params['loc_docid'])){
			$loc_docid = trim($params['loc_docid']);
			$loc_parentid  =  strstr(strtoupper($loc_docid),'P');
		}else{
			$loc_docid 		= '';
			$loc_parentid 	= '';
		}
      
        if(trim($data_city)=='')
        {
            $message = "data_city is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($parent_docid)=='')
        {
            $message = "Parent docid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($hospital_name)=='')
        {
            $message = "Hospital Name is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($doctor_name)=='')
        {
            $message = "Doctor Name is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($doctor_name)=='')
        {
            $message = "Doctor Name is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($national_catid)=='')
        {
            $message = "National Catid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        
        $national_catid_Arr     =   explode(",",$national_catid);
        $this->national_catid   =   implode("','",$national_catid_Arr);

        $this->update_parent_hosp_flag = 0;
        $this->insert_ignore_flag   = $insert_ignore_flag;
        $this->datetime         = date('Y-m-d H:i:s');
        $this->parent_docid		= $parent_docid;
        $this->hospital_name 	= $hospital_name;
        $this->doctor_name 		= $doctor_name;
        $this->data_city       	= strtolower($data_city);        
        $this->remote_flag 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? 0 : 1);
        $this->setServers();

        if(preg_match("/[^A-Z0-9\.]+/",$this->parent_docid))
        {
            $message = "Invalid Parent Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->parent_pid    =  strstr(strtoupper($this->parent_docid),'P');
        if(empty($this->parent_pid)){
            $message = "Invalid Parent Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }else{
			$this->ref_parentid = $this->parent_pid;
		}
		$this->loc_docid 		= $loc_docid;
		$this->loc_parentid 	= $loc_parentid;
        if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
        {
            $this->cs_api      =   get_cs_application_url($this->data_city);
        }
        else
        {
            $this->cs_api = "http://imteyazraja.jdsoftware.com/csgenio/";
        }

        if($insert_ignore_flag == 1){
            echo "<b>CS_URL :</b>".$this->cs_api;
            echo "<br><b>Conn Mapping :</b><br>";
            echo "<pre>";print_r($this->conn_mapping);
        }

        $this->contract_info_arr    = $this->fetchContractInfo($this->parent_pid,'hosp');
        if(!empty($this->contract_info_arr) && $this->contract_info_arr['hosp_valid_flag'] !=1)
        {
            $message = "Hospital Contract Id Does Not Exist.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
       
        $entryInMapping     = $this->entryInMapping($this->parent_docid);
        if($entryInMapping == 0)
        {
           /* $message = "No Such Hospital Exists - Entry Not Found In Mapping Table.";
            echo json_encode($this->sendDieMessage($message));
            die();*/
            $this->checkExistingParentData();
        }
        

    }
    private function checkExistingParentData(){            
        $sql_child_contract    =   "SELECT parent_docid FROM tbl_reservation_mapping WHERE MATCH(child_docid) AGAINST ('\"".$this->parent_docid."\"' IN BOOLEAN MODE) LIMIT 1";
        $res_child_contract    =   parent::execQuery($sql_child_contract,$this->conn_mapping);
        if(parent::numRows($res_child_contract)>0){
			$message = "Given Parent docid ".$this->parent_docid." is associated with some other contract.";
			echo json_encode($this->sendDieMessage($message));
			die();
        }
        else{          
			$this->update_parent_hosp_flag =1;
        }
    }

    public function insertData(){
		$child_parentid   = $this->generateParentid();
        $child_docid      = $this->docidCreator($child_parentid);
        $child_sphinx_id  = $this->getSphinxid($child_parentid);


        if($this->insert_ignore_flag ==1){
            $results_array['contractinfo']['new_parentid']           = $child_parentid;
            $results_array['contractinfo']['new_docid']              = $child_docid;
            $results_array['contractinfo']['loc_docid']              = $this->loc_docid;
            $results_array['contractinfo']['loc_parentid']           = $this->loc_parentid;
            $results_array['contractinfo']['ref_parentid']           = $this->ref_parentid;
            $results_array['contractinfo']['entity_name']            = $this->doctor_name;
            $results_array['contractinfo']['entity_workplace']       = $this->hospital_name;
            $results_array['contractinfo']['national_catid']         = $this->national_catid;
            echo json_encode($results_array);
            die;
        } 

        if(intval($child_sphinx_id) > 0){
                $sql_doc_hosp_data    =   "INSERT INTO tbl_hosp_child_contracts_request SET 
                                            parent_docid    = '".$this->parent_docid."',
                                            hospital_name   = '".addslashes($this->hospital_name)."',
                                            child_sphinxid  = '".$child_sphinx_id."',
                                            child_docid     = '".$child_docid."',
                                            child_pid       = '".$child_parentid."',
                                            loc_docid       = '".$this->loc_docid."',
                                            doctor_name     = '".addslashes($this->doctor_name)."',
                                            data_city       = '".$this->data_city."',
                                            national_catid  = '".$this->national_catid."',
                                            update_parent_hosp_flag = $this->update_parent_hosp_flag,
											done_flag       = '0',                                              
											updatedOn		= '".$this->datetime."',
											request_module	= 'vendor'";
                
                $res_doc_hosp_data  = parent::execQuery($sql_doc_hosp_data,$this->conn_iro);		
            }
            if($res_doc_hosp_data){
                $results_array['error']['code'] = 0;
                $results_array['error']['msg'] = "Success";
                $results_array['contractinfo']['panindia_sphinxid']      = $child_sphinx_id;
                $results_array['contractinfo']['new_parentid'] 		     = $child_parentid;
                $results_array['contractinfo']['new_docid'] 		     = $child_docid;
                $results_array['contractinfo']['loc_docid']              = $this->loc_docid;
                $results_array['contractinfo']['loc_parentid']           = $this->loc_parentid;
                $results_array['contractinfo']['ref_parentid']           = $this->ref_parentid;
                $results_array['contractinfo']['entity_name'] 			 = $this->doctor_name;
                $results_array['contractinfo']['entity_workplace']       = $this->hospital_name;
                $results_array['contractinfo']['national_catid']         = $this->national_catid;
            }
            else{
                $results_array['error']['code'] = 1;
                $results_array['error']['msg'] = "Unable to process";
				$results_array['error']['rsn'] = "There is a problem in creating new parentid.";
            }
            echo json_encode($results_array);
    }

    private function setServers()
    {   
        GLOBAL $db;
        $conn_city      = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
        
        $this->conn_iro         = $db[$conn_city]['iro']['master'];
        $this->conn_local       = $db[$conn_city]['d_jds']['master']; 
        $this->conn_mapping     = $db['webedit_vertical'];         
    }

    private function getValidCategories($total_catlin_arr){
        $final_catids_arr = array();
        if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
        {
            foreach($total_catlin_arr as $catid)
            {
                $final_catid = 0;
                $final_catid = preg_replace('/[^0-9]/', '', $catid);
                if((!empty($final_catid)) && (intval($final_catid)>0))
                {
                    $final_catids_arr[] = $final_catid;
                }
            }
            $final_catids_arr = array_filter($final_catids_arr);
            $final_catids_arr = array_unique($final_catids_arr);
        }
        return $final_catids_arr;   
    }

     private function generateParentid(){
        
        for($i = 0; $i < 3; $i++){  //Random String Generator
             $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
             $iTotal = count($aChars) - 1;
             $iIndex = rand(0, $iTotal);
             $sCode .= $aChars[$iIndex];
             $sCode .= chr(rand(49, 57));
        }
        $stdcode = "XXXX";
        if($this->data_city){
            $sqlFetchStdCode = "SELECT stdcode FROM tbl_stdcode_master WHERE city = '".$this->data_city."' and stdcode!='' LIMIT 1";
            $resFetchStdCode = parent::execQuery($sqlFetchStdCode,$this->conn_local);
            $numberOfRows    = parent::numRows($resFetchStdCode);
            if($resFetchStdCode && $numberOfRows > 0){
                $row_std_code   =   parent::fetchData($resFetchStdCode);
                $stdcode = $row_std_code['stdcode'];
            }
        }
        $stdcode = substr($stdcode,1);
        $stdcode = str_pad($stdcode,4,"X",STR_PAD_LEFT);

        if($stdcode=="XXXX"){
            $message = "STD code for given data city ".$this->data_city." does not exist.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $stdcode_destination_component = $stdcode; // 4 digit
        $time_component = substr(date("YmdHis",time()),2); // 12 digit
        $random_number_component = substr($sCode,2); // 4 digit

        $cCode = $stdcode_destination_component.".".$stdcode_destination_component.".".$time_component.".".$random_number_component; //24 + 3 = 27 digits
        /*Genrating Sphinx id*/
        if($cCode && $this->insert_ignore_flag != 1){
			$PCode="P".$cCode;
			$id_generator_url= $this->cs_api."/api_services/api_idgeneration.php?source=docvendor&rquest=idgenerator&module=cs&datacity=".urlencode($this->data_city)."&parentid=".$PCode."&rflag=".$this->remote_flag;
            $strNewsphinxId = json_decode($this->curlCallGet($id_generator_url),true);
        }
        /*--------------------*/
        return ('P'.$cCode);    
    }

    private function docidCreator($parentid)
    {   
        switch(strtoupper(trim($this->data_city))){
            case 'MUMBAI':
                $docid = "022".$parentid;
                break;
            case 'DELHI':
                $docid = "011".$parentid;
                break;
            case 'KOLKATA':
                $docid = "033".$parentid;
                break;
            case 'BANGALORE':
                $docid = "080".$parentid;
                break;
            case 'CHENNAI':
                $docid = "044".$parentid;
                break;
            case 'PUNE':
                $docid = "020".$parentid;
                break;
            case 'HYDERABAD':
                $docid = "040".$parentid;
                break;
            case 'AHMEDABAD':
                $docid = "079".$parentid;
                break;      
            default:
                $docid_stdcode  = $this->stdcodeMaster();
                if($docid_stdcode){
                    $temp_stdcode = ltrim($docid_stdcode,0);
                }
                $ArrCity = array('AGRA','ALAPPUZHA','ALLAHABAD','AMRITSAR','BHAVNAGAR','BHOPAL','BHUBANESHWAR','CHANDIGARH','COIMBATORE','CUTTACK','DHARWAD','ERNAKULAM','GOA','HUBLI','INDORE','JAIPUR','JALANDHAR','JAMNAGAR','JAMSHEDPUR','JODHPUR','KANPUR','KOLHAPUR','KOZHIKODE','LUCKNOW','LUDHIANA','MADURAI','MANGALORE','MYSORE','NAGPUR','NASHIK','PATNA','PONDICHERRY','RAJKOT','RANCHI','SALEM','SHIMLA','SURAT','THIRUVANANTHAPURAM','TIRUNELVELI','TRICHY','UDUPI','VADODARA','VARANASI','VIJAYAWADA','VISAKHAPATNAM','VIZAG');
                if(in_array(strtoupper($this->data_city),$ArrCity)){
                    $sqlStd     = "SELECT stdcode FROM tbl_data_city WHERE cityname = '".$this->data_city."'";
                    $resStd     = parent::execQuery($sqlStd,$this->conn_local);
                    $rowStd     =  parent:: fetchData($resStd);
                    $cityStdCode    =  $rowStd['stdcode'];
                    if($temp_stdcode == ""){
                        $stdcode = ltrim($cityStdCode,0);
                        $stdcode = "0".$stdcode;                
                    }else{
                        $stdcode = "0".$temp_stdcode;               
                    }
                    
                }else{
                    $stdcode = "9999";
                }   
                $docid = $stdcode.$parentid;
                break;          
        }
        
        return $docid;
    }

    private function getSphinxid($parentid){
        $selectSql  =   "SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$parentid."'";
        $selectRes  =   parent::execQuery($selectSql,$this->conn_iro);
        $selectRow  =   parent::fetchData($selectRes);
        $sphinx_id  =   $selectRow['sphinx_id'];
        return $sphinx_id;
    }

    private function fetchContractInfo($parentid,$flag)
    {
        $contract_arr = array();
        if($flag == 'doc'){
            $contract_arr['doc_valid_flag'] = 0;
            $contract_arr['doctor_name'] = '';
            
        }else if($flag == 'hosp'){
            $contract_arr['hosp_valid_flag'] = 0;
            $contract_arr['hospital_name'] = '';
        }
        $sqlParentidCheck   = "SELECT parentid,companyname FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
        $resParentidCheck   = parent::execQuery($sqlParentidCheck,$this->conn_iro);
        $numberOfRows       = parent::numRows($resParentidCheck);
        if($resParentidCheck && $numberOfRows >0){
            $row_contract_info  =   parent::fetchData($resParentidCheck);
            if($flag == 'doc'){
            $contract_arr['doc_valid_flag'] = 1;
            $contract_arr['doctor_name'] = $row_contract_info['companyname'];
            }else if($flag == 'hosp'){
                $contract_arr['hosp_valid_flag'] = 1;
                $contract_arr['hospital_name'] = $row_contract_info['companyname'];
            }
        }
        return $contract_arr;
    }

    private function stdcodeMaster()
    {
        $sql_stdcode    =   "SELECT stdcode FROM city_master WHERE data_city = '".$this->data_city."'";
        $res_stdcode    =   parent::execQuery($sql_stdcode,$this->conn_local);
        if($res_stdcode){
            $row_stdcode    =  parent::fetchData($res_stdcode);
            $stdcode        =   $row_stdcode['stdcode'];    
            if($stdcode[0]=='0'){
                $stdcode = $stdcode;
            }else{
                $stdcode = '0'.$stdcode;
            }
        }
        return $stdcode;
    } 

    private function entryInMapping($parent_docid){
           $entry_flag = 0;
           $sqlMappingCheck =   "SELECT parent_docid FROM tbl_reservation_mapping WHERE parent_docid= '".$parent_docid."' AND sub_type_flag =2 ";
           $resMappingCheck =    parent::execQuery($sqlMappingCheck,$this->conn_mapping);
           if(parent::numRows($resMappingCheck)>0){
                $entry_flag = 1;
           }
           return $entry_flag;
    }

    public function curlCallGet($curl_url){
        $ch = curl_init($curl_url);
        $ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $resstr = curl_exec($ch);
        //print "curl result : ".$resstr ;exit;
        curl_close($ch);
        return $resstr;
    }

    private function sendDieMessage($reason)
    {
        $die_msg_arr = array();
        $die_msg_arr['error']['code'] = 1;
        $die_msg_arr['error']['msg'] = 'Unable to process';
		$die_msg_arr['error']['rsn'] = $reason;
        return $die_msg_arr;
    }

}

?>
