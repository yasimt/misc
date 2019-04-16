<?
/**(12:20) imteyaz raja: parent_docid : given 
hosp_docid : given
child_docid : created
child_pid : created
child_sphinxid : created
child_doctor_name : given
data_city : given
updatedOn : current time
done_flag
process_time
data
* 
*/
class multilocation_doctor  extends DB
{
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
	
	var $conn_iro   = '';
	var	$conn_local = '';
	
	function __construct($params){
		
        $parent_docid       = trim($params['parent_docid']);        
        $hosp_docid         = trim($params['hosp_docid']);
        $doctor_name        = trim($params['doctor_name']);
        $hospital_name      = trim($params['hospital_name']);    
        $data_city          = trim($params['data_city']);
        $insert_ignore_flag = trim($params['insert_ignore']);

        if(trim($data_city)=='')
        {
            $message = "data_city is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($parent_docid)=='')
        {
            $message = "parent_docid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($hosp_docid)=='')
        {
            $message = "hosp_docid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($doctor_name)=='')
        {
            $message = "doctor_name is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(trim($hospital_name)=='')
        {
            $message = "hospital_name is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        
        $this->insert_ignore_flag     = $insert_ignore_flag;
		$this->datetime         = date('Y-m-d H:i:s');
        $this->doctor_name 		= $doctor_name;
        $this->hospital_name 	= $hospital_name;
        $this->parent_docid		= $parent_docid;
        $this->hosp_docid  		= $hosp_docid;
        $this->data_city       	= strtolower($data_city);        
        $this->remote_flag 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? 0 : 1);
        $this->setServers();
        
        if(preg_match("/[^A-Z0-9\.]+/",$parent_docid))
        {
            $message = "Invalid Parent Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if(preg_match("/[^A-Z0-9\.]+/",$hosp_docid))
        {
            $message = "Invalid Hospital Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
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

        $this->parent_pid    =  strstr(strtoupper($parent_docid),'P');
        if(empty($this->parent_pid)){
            $message = "Invalid Parent Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->hosp_pid      =  strstr(strtoupper($hosp_docid),'P');

        if(empty($this->hosp_pid)){
            $message = "Invalid Hospital Docid.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $entryInMapping     = $this->entryInMapping($parent_docid);
        if($entryInMapping == 0)
        {
            $message = "No Such Doctor Exists - Entry Not Found In Mapping Table.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $childContract = $this->isExistingChidContract($this->hosp_pid);
        if(!empty($childContract))
        {
            $message = "Given Hospital contractid is a child contract of ".$childContract.". Hence, you are not allowed to select this contract as Hospital Name.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->contract_info_arr    = $this->fetchContractInfo($this->parent_pid,'doc');
        if(!empty($this->contract_info_arr) && $this->contract_info_arr['doc_valid_flag'] !=1)
        {
            $message = "Doctor Contract Id Does Not Exist.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $this->contract_info_arr    = $this->fetchContractInfo($this->hosp_pid,'hosp');
        if(!empty($this->contract_info_arr) && $this->contract_info_arr['hosp_valid_flag'] !=1)
        {
            $message = "Hospital Contract Id Does Not Exist.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        $this->checkDuplicacy();

	}
	private function setServers()
    {   
        GLOBAL $db;
        $conn_city      = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
        
        $this->conn_iro         = $db[$conn_city]['iro']['master'];
        $this->conn_local       = $db[$conn_city]['d_jds']['master']; 
        $this->conn_mapping     = $db['webedit_vertical'];         
    }
    private function checkDuplicacy(){
        $sqlCheck   =   "SELECT done_flag, hosp_docid FROM tbl_doc_child_contracts_request WHERE parent_docid ='".$this->parent_docid."' AND hosp_docid='".$this->hosp_docid."' ";
        $resCheck   =   parent::execQuery($sqlCheck,$this->conn_iro);
        if($resCheck && parent::numRows($resCheck)>0){
            $rowCheck   =   parent::fetchData($resCheck);
            $done_flag  =  trim($rowCheck['done_flag']);

            $res_arr    = array(); 
            $res_arr['error']['code']=  2;
            if($done_flag == 1 ){                
                $res_arr['error']['msg'] = "This Location already exists with this Doctor";
                $res_arr['error']['hosp_docid'] = $this->hosp_docid;
            }
            else{
             $res_arr['error']['msg'] = "Its Under Processing";
             $res_arr['error']['hosp_docid'] = $this->hosp_docid;                                 
            }
            echo json_encode($res_arr);
            die;
        }
    }

    public function insertData(){

            $child_parentid   = $this->generateParentid();
            $child_docid      = $this->docidCreator($child_parentid);
            $child_sphinx_id  = $this->getSphinxid($child_parentid);

            if($this->insert_ignore_flag ==1){
                    $results_array['contractinfo']['new_parentid']           = $child_parentid;
                    $results_array['contractinfo']['new_docid']              = $child_docid;                
                    $results_array['contractinfo']['loc_parentid']           = $this->hosp_pid;
                    $results_array['contractinfo']['loc_docid']              = $this->hosp_docid;
                    $results_array['contractinfo']['ref_parentid']           = $this->parent_docid;
                    $results_array['contractinfo']['entity_name'] 			 = $this->doctor_name.'('.$this->hospital_name.')';
                    $results_array['contractinfo']['entity_workplace']       = $this->hospital_name;
                    echo json_encode($results_array);
                    die;
                } 
            if(intval($child_sphinx_id) > 0){
                $sql_doc_hosp_data    =   "INSERT INTO tbl_doc_child_contracts_request SET 
											parent_docid    = '".$this->parent_docid."',
											hosp_docid		= '".$this->hosp_docid."',
											doctor_name 	= '".addslashes($this->doctor_name)."',
                                            hospital_name	= '".addslashes($this->hospital_name)."',
											child_docid		= '".$child_docid."',
											child_pid       = '".$child_parentid."',
                                            child_sphinxid  = '".$child_sphinx_id."',
                                            data_city       = '".$this->data_city."',
											done_flag       = '0',                                                
											updatedOn		= '".$this->datetime."',
											request_module	= 'vendor'";

                if($this->insert_ignore_flag !=1){
                    $res_doc_hosp_data  = parent::execQuery($sql_doc_hosp_data,$this->conn_iro);
                }               
            }
            if($res_doc_hosp_data){
                $results_array['error']['code'] = 0;
                $results_array['error']['msg'] = "Success";
                $results_array['contractinfo']['panindia_sphinxid']      = $child_sphinx_id;
                $results_array['contractinfo']['new_parentid'] 		     = $child_parentid;
                $results_array['contractinfo']['new_docid'] 		     = $child_docid;                
                $results_array['contractinfo']['loc_parentid']           = $this->hosp_pid;
                $results_array['contractinfo']['loc_docid']              = $this->hosp_docid;
                $results_array['contractinfo']['ref_parentid']           = $this->parent_docid;
                $results_array['contractinfo']['entity_name'] 			 = $this->doctor_name.'('.$this->hospital_name.')';
                $results_array['contractinfo']['entity_workplace']       = $this->hospital_name;               
            }
            else{
                $results_array['error']['code'] = 1;
                $results_array['error']['msg'] = "Unable to process";
				$results_array['error']['rsn'] = "There is a problem in creating new parentid.";
            }
            echo json_encode($results_array);
    }    

    private function getSphinxid($parentid){
        $selectSql  =   "SELECT sphinx_id FROM tbl_id_generator WHERE parentid = '".$parentid."'";
        $selectRes  =   parent::execQuery($selectSql,$this->conn_iro);
        $selectRow  =   parent::fetchData($selectRes);
        $sphinx_id  =   $selectRow['sphinx_id'];
        return $sphinx_id;
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
           $sqlMappingCheck =   "SELECT parent_docid FROM tbl_reservation_mapping WHERE parent_docid= '".$parent_docid."' AND sub_type_flag !=2 ";
           $resMappingCheck =    parent::execQuery($sqlMappingCheck,$this->conn_mapping);
           if(parent::numRows($resMappingCheck)>0){
                $entry_flag = 1;
           }
           return $entry_flag;
    }

    private function isExistingChidContract($parentid){
        $existing_child_pid = '';
        $sqlChildContractChk = "SELECT ref_parentid FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."' AND ref_parentid IS NOT NULL AND ref_parentid != '' AND ref_parentid != '0' AND parentid != ref_parentid";
        $resChildContractChk    = parent::execQuery($sqlChildContractChk,$this->conn_iro);
        $numberOfRows           = parent::numRows($resChildContractChk);
        if($resChildContractChk && $numberOfRows > 0){
            $row_child_info     = parent::fetchData($resChildContractChk);
            $existing_child_pid = trim($row_child_info['ref_parentid']);
        }
        return $existing_child_pid;
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
