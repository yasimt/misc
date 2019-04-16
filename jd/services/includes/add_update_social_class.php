<?php
class socialClass extends DB
{
	var  $conn_iro    	= null;
	var  $params  		= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	var  $parentid		= null;
	var  $data_city		= null;	
	
	function __construct($params)
	{	
		global $params;
		$data_city 		= trim($params['data_city']); 	
		$city 			= trim($params['city']); 
		$companyname 	= trim($params['companyname']);
		$mobile 		= trim($params['mobile']);
		$parentid 		= trim($params['parentid']); 
		
		$this->city 		= $city;
		$this->data_city 	= $data_city;
		$this->companyname 	= $companyname;	
		$this->mobile 		= $mobile;	
		$this->parentid 	= $parentid;	

		$this->setServers();
		$docid	=	$this->getDocid();
		$this->docid = $docid;
	}		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
		
		$conn_city 				= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');	
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$url_arr 	=	array();
		$configobj 	= 	new configclass();		
		$url_arr	=	$configobj->get_url($params['city']);
		$this->social_new_url=	$url_arr['social_new_url'];
		$this->omni_url		 =	$url_arr['omni_url'];
	}
	function getDocid(){
		$sql_get_docid =	"SELECT docid FROM tbl_id_generator WHERE parentid='".$this->parentid."'";
		$res_get_docid =	parent::execQuery($sql_get_docid,$this->conn_iro);
		if(parent::numRows($res_get_docid)){
			$row_get_docid 	=	parent::fetchData($res_get_docid);
			$docid 			=	$row_get_docid['docid'];
		}
		return $docid;
	}

	function addSocial(){	
		if($this->docid!='' && $this->companyname!=''){
			$curl_url  = $this->social_new_url."?case=business&docid=".$this->docid."&isdcode=0091&mob=".$this->mobile."&compName=".urlencode($this->companyname)."&city=".$this->city."&type=business";
			$curl_res 		=	$this->curlCall($curl_url);
			//echo "<br>res---".$curl_res;			

			$curl_omni_url 	= $this->omni_url."?docid=".$this->docid;
			$curl_omni_res 	=	$this->curlCall($curl_omni_url);
			$sql_omni_log =	"INSERT INTO tbl_social_api_log SET 
								docid = '".$this->docid."',
								social_url = '".addslashes(stripslashes($curl_url))."',
								social_res = '".addslashes(stripslashes($curl_res))."',
								omni_url = '".addslashes(stripslashes($curl_omni_url))."',
								omni_res = '".addslashes(stripslashes($curl_omni_res))."',
								updatedOn  = NOW() ";
			$res_omni_log = parent::execQuery($sql_omni_log,$this->conn_iro);
		}
	}

	function curlCall($url)
	{
		if(!empty($url))
		{
			$ch 		= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			$resmsg = curl_exec($ch);
			//print_r($resmsg);
			curl_close($ch);
		}
		return $resmsg;
	}
}
