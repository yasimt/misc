<?

class DialableNumberClass
{

private $existing_virtualnumber_cities,$Vstdcode_arr;

public function __construct($compmaster_obj)
{
$this->existing_virtualnumber_cities = array('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','jaipur','chandigarh','coimbatore');
$this->Vstdcode_arr = array('22','11','33','80','44','20','40','79','422','141','172'); //Coimbatore --422  , jaipur -141 ,,Chandigarh
$this->compmaster_obj = $compmaster_obj;
}

function upDateDialableNumber($parentid,$conn_iro)
{
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "sphinx_id,mobile,mobile_display,landline,landline_display,stdcode,virtualNumber,data_city";
		$tablename		= "tbl_companymaster_generalinfo";
		$wherecond		= "parentid='".$parentid."'";
		$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		if ($temparr['numrows']>0)
		{
			$ArggenralInfoArr = $temparr['data']['0'];
		}
		
	//dialable_landline,dialable_landline_display,dialable_mobile,dialable_mobile_display 
	$DialableNumberArr = array();
	$Vstdcode = $stdcode = ltrim($ArggenralInfoArr['stdcode'],'0');	
	$stdcode = "91".$stdcode;
	$DialableNumberArr['dialable_VirtualNumber']="";
	if(strlen(trim($ArggenralInfoArr['virtualNumber'])>4))
	{	
		if(in_array($Vstdcode,$this->Vstdcode_arr))
		{
			$DialableNumberArr['dialable_VirtualNumber'] = "91".$Vstdcode.$ArggenralInfoArr['virtualNumber'];
		}
		elseif($this->in_array_cinsensitive_chk($ArggenralInfoArr['data_city'],$this->existing_virtualnumber_cities))
		{
			//echo $sql_STDcode = "SELECT stdcode FROM d_jds.tbl_area_master WHERE data_city='".$ArggenralInfoArr['data_city']."' AND deleted=0  AND display_flag=1 LIMIT 1";
			$sql_STDcode = "SELECT stdcode FROM d_jds.city_master WHERE data_city='".$ArggenralInfoArr['data_city']."' AND display_flag=1 LIMIT 1";
			$rs_STDcode = $conn_iro->query_sql($sql_STDcode);
			if($rs_STDcode && mysql_num_rows($rs_STDcode)>0)
			{
				$arr_STDcode = mysql_fetch_assoc($rs_STDcode);
				$Vstdcode = $arr_STDcode['stdcode'];
				$Vstdcode = ltrim($Vstdcode,'0');
				$DialableNumberArr['dialable_VirtualNumber'] = "91".$Vstdcode.$ArggenralInfoArr['virtualNumber'];
			}else
			{
				$DialableNumberArr['dialable_VirtualNumber'] =$stdcode.$ArggenralInfoArr['virtualNumber'];
			}
		}
	}
	
	$DialableNumberArr['dialable_VirtualNumber'] = "91".$ArggenralInfoArr['virtualNumber'];//VN will now be mobile number hence putting only country code

	$landlinearr = explode(",",$ArggenralInfoArr['landline']);
	$landlinearr= array_filter($landlinearr);
	$dialable_landlineArr = array();
	foreach($landlinearr as $landline)
	{
		$dialable_landlineArr[]= $stdcode.$landline;
	}

		$landlinearr = explode(",",$ArggenralInfoArr['landline']);
		$landlinearr= array_filter($landlinearr);
		$dialable_landlineArr = array();
		foreach($landlinearr as $landline)
		{
			$dialable_landlineArr[]= $stdcode.$landline;
		}

		$landline_displayarr = explode(",",$ArggenralInfoArr['landline_display']);
		$landline_displayarr= array_filter($landline_displayarr);

		$dialable_landline_displayArr = array();
		foreach($landline_displayarr as $landline)
		{
			$dialable_landline_displayArr[]= $stdcode.$landline;
		}


		$mobilearr = explode(",",$ArggenralInfoArr['mobile']);
		$mobilearr= array_filter($mobilearr);
		$dialable_mobileArr = array();
		foreach($mobilearr as $mobile)
		{
			$dialable_mobileArr[]= "91".$mobile;
		}


		$mobile_displayarr = explode(",",$ArggenralInfoArr['mobile_display']);
		$mobile_displayarr = array_filter($mobile_displayarr);

		$dialable_mobile_displayArr = array();
		foreach($mobile_displayarr as $mobile)
		{
			$dialable_mobile_displayArr[]= "91".$mobile;
		}

		$DialableNumberArr['dialable_landline'] = implode(",",$dialable_landlineArr);
		$DialableNumberArr['dialable_landline_display'] = implode(",",$dialable_landline_displayArr);
		$DialableNumberArr['dialable_mobile'] = implode(",",$dialable_mobileArr);
		$DialableNumberArr['dialable_mobile_display'] = implode(",",$dialable_mobile_displayArr);

		$insarr			= array();
		$insarr['tbl_companymaster_generalinfo']	= array(
															"dialable_landline" 		=> $DialableNumberArr['dialable_landline'],
															"dialable_landline_display" => $DialableNumberArr['dialable_landline_display'],
															"dialable_mobile" 			=> $DialableNumberArr['dialable_mobile'],
															"dialable_mobile_display"	=> $DialableNumberArr['dialable_mobile_display'],
															"dialable_VirtualNumber" 	=> $DialableNumberArr['dialable_VirtualNumber'],
															"sphinx_id"					=> $ArggenralInfoArr['sphinx_id'],
															"parentid"					=> $parentid
														);
		$this->compmaster_obj->UpdateRow($insarr);
		//return $DialableNumberArr;
}
function in_array_cinsensitive_chk($needle, $haystack)
{
    foreach ($haystack as $value)
    {
        if (strtolower($value) == strtolower($needle))
        return true;
    }
    return false;
}

}


?>
