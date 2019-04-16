<?php
class DNDNumber
{
	var $allowed_expiry_period = 180; /* As per email converation & Mr. Tushar's instrumction expired period has been exceeded by 180 days on 11th Feb 2011 */
	var $restricted_year = 2;
	
	
	private $conn_finance;
	private $conn_iro ;
	private $conn_idc_dnc ;
	private $conn_local ;
    private $conn_iro_slave;
	
		
		
	function __construct($dbarr){
		
		if (!is_array($dbarr)){echo "<br> dbarr array not found at DNDNumber class Instantiation "; exit;}
		$this-> conn_finance  = new DB($dbarr['FINANCE']);
		$this->conn_iro      = new DB($dbarr['DB_IRO']);
		$this->conn_idc_dnc  = new DB($dbarr['DNC']);
		$this->conn_local	  = new DB($dbarr['LOCAL']);
        $this->conn_iro_slave  = new DB($dbarr['DB_IRO_SLAVE']);
	}
	
	function possibleNumbers($number, $stdCodes='')
	{
		$numberStr = array();
		if(is_array($stdCodes))
		{
			foreach($stdCodes as $std)
			{
				$numberStr[] = trim($std.$number);
			}
		}
		else
		{
			if(trim($stdCodes)!='')
			{
				$numberStr[] = trim($stdCodes.$number);
			}
			else
			{
				$numberStr[] = trim($number);
			}
		}
		return $numberStr;
	}
	

	function IsInDNClist($Numbers=array()) 
	{           
		$Reason = 0;
		$currentDate = date('Y-m-d')." 23:59:59";
        if(!is_array($Numbers) && trim($Numbers)!='')
        {
            $Numbers = array($Numbers);
        }
		if(is_array($Numbers) && count($Numbers)>0)
		{
			$Selednd = "SELECT dndnumber, is_safe, safe_till FROM dnc.dndlist WHERE dndnumber in ('" . implode("', '", $Numbers) . "') and is_deleted =0";
            $resultset = $this->conn_iro_slave ->query_sql($Selednd);

            //$resultset = mysql_query($Selednd);   //$resultset = $conn_local->query_sql($Selednd);
            return mysql_num_rows($resultset);
		}        
		return $Reason;
	}
	
	function getStdCodes($stdcode='', $city='')
	{		
		if(trim($stdcode)=='')
		{
			if(trim($city)!='')
			{
				$get_std_sql = "SELECT stdcode FROM tbl_stdcode_master WHERE city = '".$city."' and stdcode!='' LIMIT 1";
				$get_std_res = $this->conn_local->query_sql($get_std_sql);
				if($get_std_res && $get_std_row = mysql_fetch_assoc($get_std_res))
				{
					$stdcode = $get_std_row['stdcode'];
					$stdcode = ltrim($stdcode, '0');
				}
			}
		}
		if(trim($stdcode)=='')
		{
			$addrPart = explode(".", $_SERVER['SERVER_ADDR']);
			$stdGroup = array();
			if($addrPart[0]=='172' && $addrPart[1]=='29')
			{
				switch($addrPart[2])
				{
					case 0:
						$stdGroup = array('22','251','215');
					break;
					case 8:
						$stdGroup = array('11','120','129','124');
					break;
					case 16:
						$stdGroup = array('33');
					break;
					case 26:
						$stdGroup = array('80');
					break;
					case 32:
						$stdGroup = array('44');
					break;
					case 40:
						$stdGroup = array('20');
					break;
					case 50:
						$stdGroup = array('40');
					break;
					case 56:
						$stdGroup = array('79');
					break;
					default:
						$stdGroup = array('22','251','215');
					break;
				}				
			}			
		}
		else
		{
			$stdGroup  = array(trim($stdcode));
		}
		return $stdGroup;		
	}
	/*
	$Reason=0 not fount in DND list
	$Reason=1 Found in DND list And restrict call 
	$Reason=2 Found and allowed call (means associated with paid contracts.)
	*/
	function IsDNDNumber( $Numbers=array()) 
	{		
		$Reason = 0;
		$currentDate = date('Y-m-d')." 23:59:59";
		/*if(is_array($Numbers) && count($Numbers)>0)
		{
			$Selednd = "SELECT dndnumber, is_safe, safe_till FROM ".DB_DNC_LIVE.".dndlist WHERE dndnumber in ('" . implode("', '", $Numbers) . "') and is_deleted =0";
			$resultset = $this->conn_iro_slave->query_sql($Selednd);
			$rowCat = mysql_fetch_array($resultset);   			   
			if ($rowCat)
			{
				$Reason=1;
				if($rowCat[1] == 1 && $rowCat[2] >= $currentDate)
				{
					$Reason=2;
				}
			}					
		}*/
		return $Reason;
	}
	function IsLinkWithValidContracts( $Number, $compmaster_obj, $stdcode='', $city='')
	{
		//GLOBAL $dbarr;
		//$dbconn_fnc   = new DB($dbarr['DB_FNC']);
		//$conn_iro  	  = new DB($dbarr['DB_IRO']);
		
		$Number = trim($Number);
        $chkNumber = ltrim($Number, '0');
		$DNDNumber = $chkNumber;
        if(strlen($chkNumber)==10)
        {
        	$stdcode = ltrim($stdcode, '0');
			$possibleStdCodes = $this->getStdCodes($stdcode, $city );
			foreach($possibleStdCodes as $value_std)
			{
				$length_value_std = strlen($value_std);
				if($value_std == substr($chkNumber,'0',$length_value_std))
				{
					$DNDNumber = substr($chkNumber,$length_value_std);
					break;
				}
			}
        }
		if($DNDNumber!='')
		{
			$temparr	= array();
			$fieldstr	= "group_concat(DISTINCT parentid SEPARATOR '\", \"') as parentids";
			$where		= "MATCH(phone_search) AGAINST('" . $DNDNumber . "')";
			$check_expiry_rs	= $compmaster_obj->getRow($fieldstr,"tbl_companymaster_search",$where);

			if($check_expiry_rs['numrows'])
			{
				$check_expiry_row	= $check_expiry_rs['data']['0'];
				if(trim($check_expiry_row['parentids'])!='')
				{
					//echo $check_comp_mast_expiry = "SELECT expired, parentid, expired_on FROM tbl_companymaster_search WHERE parentid IN (\"" . trim($check_expiry_row['parentids']) . "\") AND paid='1' AND (expired='0' OR (expired_on <= '" . date("Y-m-d") . "' AND expired_on > SUBDATE('".date("Y-m-d")."', INTERVAL " . $this->allowed_expiry_period . " DAY)))";

					$temparr	= array();
					$fieldstr	= "expired, parentid";
					$where		= "parentid IN (\"" . trim($check_expiry_row['parentids']) . "\") AND paid='1' AND expired='0'";
					$check_comp_mast_expiry_rs	= $compmaster_obj->getRow($fieldstr,"tbl_companymaster_search",$where);

					if($check_comp_mast_expiry_rs['numrows'] > 0)
					{
						return true;/* Allow to make call */
					}                       
				} 
			}
			
		}
		return false; /* Not allow to make call */
	}

	function IsValidDNDNumber( $Number, $compmaster_obj, $stdcode='', $city='')  // as name suggest it will tell whether a number is valid DND number or not // this function is used as public function
	{
		$Number = trim($Number);
		$mobil_number = ltrim($Number, '0'); /* remove all advance zero */		
		if(strlen($mobil_number) < 10)
		{
			$possibleStdCodes = $this->getStdCodes($stdcode, $city);
			$possibleDNDNumbers = $this->possibleNumbers($mobil_number, $possibleStdCodes);
		}
		else 
		{
			$possibleDNDNumbers[] = trim($mobil_number);
		}
		
		  
		$Reason = 0;   
		$Reason = $this->IsDNDNumber($possibleDNDNumbers);
		if ($Reason == 1)
		{
			if($this->IsLinkWithValidContracts($mobil_number,$compmaster_obj, $stdcode, $city))
			{
				$Reason = 2;
			}
		}
		return $Reason;
	}

	function getDNDColors($stdcode,$compmaster_obj, $Numbers)// this function returns two array green_list and red_list
	{
		
		$red_list = array();
		$green_list = array();
		$currentDate = date('Y-m-d')." 23:59:59";
		if(is_array($Numbers) && count($Numbers)>0)
		{
			$Selednd = "SELECT dndnumber, is_safe, safe_till FROM ".DB_DNC_LIVE.".dndlist WHERE dndnumber in ('" . implode("', '", $Numbers) . "') and is_deleted =0";
			$resultset = $this->conn_iro_slave->query_sql($Selednd);
			while($rowCat = mysql_fetch_array($resultset))      
			{
				$len_std		=	strlen($stdcode);							//Fetching stdcode length to compare with Landline numbers//
				$std_number		=	substr($rowCat['dndnumber'],0,$len_std);		//removing numbers equal to std code length//				
				if($std_number==$stdcode)										//Checking wether std code and removed numbers are equal //
				{
					$final_number =	substr($rowCat['dndnumber'],$len_std);			//Removing stdcode from the number  //
				}
				else
				{
					$final_number 	= 	$rowCat['dndnumber'];						//no check if number's digit doesnot match stdcode i.e mobile number//
				}		
				$Reason=1;
				if($rowCat[1] == 1 && $rowCat[2] > $currentDate)
				{
					$Reason=2;	// Allow to make call //
				}
				else
				{
					if($this->IsLinkWithValidContracts($final_number,$compmaster_obj,$stdcode))
					{
						$Reason = 2;
					}
				}
				
				if ($Reason==1)
				{
					$red_list[] 	= 	$final_number;	
				}
				elseif ($Reason==2)
				{
					$green_list[] = $final_number;
				}
				
			}			
			
		}
		return array("red_list"=>$red_list, "green_list"=>$green_list);
	}

	function updateDNDList($parentid,$compmaster_obj)
	{
		/*
		GLOBAL $dbarr;
		$conn_finance  = new DB($dbarr['FINANCE']);
		$conn_iro      = new DB($dbarr['DB_IRO']);
		$conn_idc_dnc  = new DB($dbarr['DNC']);
		$conn_local	  = new DB($dbarr['LOCAL']);
		*/
		$returnstr = '';
		if($parentid != '')
		{			
			/* REMOVED FROM PREVIOUS QUERY GROUP BY parentid AND ORDER BY ADDED */
			/*$sel_comp_phone_num = "SELECT mobile_1, mobile_2, mobile_3, mobile_4, stdcode, city, tele_1, tele_2, tele_3, tele_4, addtele,addmobile,tollfree, GREATEST(IF(date(end_date)='0000-00-00' OR date(end_date) IS NULL,CURRENT_DATE,end_date),DATE_ADD( IF(date(end_date)='0000-00-00' OR date(end_date) IS NULL,CURRENT_DATE,DATE(end_date)),INTERVAL " . $this->allowed_expiry_period . " DAY)) AS safe_till_end_date  FROM tbl_company_master WHERE parentid = '" . $parentid . "' ORDER BY end_date DESC LIMIT 1";
			$res_comp_phone_num = $conn_local->query_sql($sel_comp_phone_num);*/

			$temparr	= array();

			$joinfiedsname 	= "mobile,landline,tollfree,stdcode,city,landline_addinfo,mobile_addinfo";
			$jointablesname	= "tbl_companymaster_generalinfo AS a JOIN tbl_companymaster_extradetails AS b";
			$joincondon		= "on a.parentid=b.parentid";
			$wherecond		= "a.parentid= '" . $parentid . "'";

			$res_comp_phone_num		= $compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond);


			$contact_numbers = array();
			$possibleStdCodes = array();
			$all_numbers = array();

			if($res_comp_phone_num['numrows'])
			{
				$row_comp_phone_num	= $res_comp_phone_num['data']['0'];
				$stdcode = trim($row_comp_phone_num['stdcode']);
				$city	 = trim($row_comp_phone_num['city']);
				$stdcode = ltrim($stdcode, 0);
				if($stdcode=='')
				{
					$possibleStdCodes = $this->getStdCodes($stdcode, $city);					
				}
				else
				{
					$possibleStdCodes = array($stdcode);
				}
				if(is_array($possibleStdCodes) && count($possibleStdCodes)>0)
				{
					/*if(trim($row_comp_phone_num['tele_1'])!='')
					{
						$all_numbers[] = trim($row_comp_phone_num['tele_1']);
					}
					if(trim($row_comp_phone_num['tele_2'])!='')
					{
						$all_numbers[] = $row_comp_phone_num['tele_2'];
					}
					if(trim($row_comp_phone_num['tele_3'])!='')
					{
						$all_numbers[] = $row_comp_phone_num['tele_3'];
					}
					if(trim($row_comp_phone_num['tele_4'])!='')
					{
						$all_numbers[] = $row_comp_phone_num['tele_4'];
					}*/
					if(trim($row_comp_phone_num['landline']) != '')
					{
						$add_numbers = explode(',', $row_comp_phone_num['landline']);
						$all_numbers = array_merge($all_numbers, $add_numbers);
					}
					if(trim($row_comp_phone_num['landline_addinfo']) != '')
					{
						//$add_numbers = explode(',', $row_comp_phone_num['landline_addinfo']);
						$add_numbers = $this->getAllPhoneNumbers(trim($row_comp_phone_num['landline_addinfo']));
						if(is_array($add_numbers))
						$all_numbers = array_merge($all_numbers, $add_numbers);
					}
				}
				else
				{
					$possibleStdCodes = array();
				}
				/*if(trim($row_comp_phone_num['mobile_1'])!='')
				{
					$all_numbers[] = $row_comp_phone_num['mobile_1'];
				}
				if(trim($row_comp_phone_num['mobile_2'])!='')
				{
					$all_numbers[] = $row_comp_phone_num['mobile_2'];
				}
				if(trim($row_comp_phone_num['mobile_3'])!='')
				{
					$all_numbers[] = $row_comp_phone_num['mobile_3'];
				}
				if(trim($row_comp_phone_num['mobile_4'])!='')
				{
					$all_numbers[] = $row_comp_phone_num['mobile_4'];
				}*/
				if(trim($row_comp_phone_num['mobile']) != '')
				{
					$add_mob_numbers = explode(',', trim($row_comp_phone_num['mobile']));
					$all_numbers = array_merge($all_numbers, $add_mob_numbers);
				}
				if(trim($row_comp_phone_num['mobile_addinfo']) != '')
				{
					//$add_mob_numbers = explode(',', $row_comp_phone_num['addmobile']);
					$add_mob_numbers = $this->getAllPhoneNumbers(trim($row_comp_phone_num['mobile_addinfo']));
					if(is_array($add_mob_numbers))
					$all_numbers = array_merge($all_numbers, $add_mob_numbers);
				}
				if(strlen(trim($row_comp_phone_num['tollfree'])==10))
				{
					$all_numbers[] = trim($row_comp_phone_num['tollfree']);
				}
				foreach($all_numbers as $number)
				{
					$number = trim($number);
					if(strlen($number)<10)
					{
						foreach($possibleStdCodes as $stdcd)
						{
							$contact_numbers[] = $this->getValidNumDNC($number,  $stdcd);
						}
					}
					elseif(strlen($number)==10)
					{
						$contact_numbers[] = $this->getValidNumDNC($number);
					}
				}
				
				$get_end_date = "SELECT GREATEST(IF(date(end_date)='0000-00-00' OR date(end_date) IS NULL,CURRENT_DATE,end_date),DATE_ADD( IF(date(end_date)='0000-00-00' OR date(end_date) IS NULL,CURRENT_DATE,DATE(end_date)),INTERVAL " . $this->allowed_expiry_period . " DAY)) AS safe_till_end_date FROM tbl_companymaster_finance WHERE   parentid = '" . $parentid . "' ORDER BY end_date DESC LIMIT 1";
				$res_end_date = $this->conn_finance->query_sql($get_end_date);
				
				if($res_end_date && $row_end_date = mysql_fetch_array($res_end_date))
				{
					$end_date = $row_end_date['safe_till_end_date'];

					$not_validdate = strtotime(date('Y') + $this->restricted_year);
					$yearofendate  = strtotime(substr($end_date,0,4));

					if($yearofendate >= $not_validdate)
					{
						$returnstr = 'dnc.dndlist not updated as enddate > '.$this->restricted_year.' yrs ';
					}
					else 
					{

						if(count($contact_numbers)>0) /*$contact_details != ''*/
						{
							$update_dnc_contact_details = "UPDATE dnc.dndlist SET is_safe =1 ,safe_till = '" . $end_date . "',done_flag=2 WHERE dndnumber IN ('".implode("', '", array_unique($contact_numbers)) ."')"; 
							//$res_dnc_contact_details = $conn_local->query_sql($update_dnc_contact_details);
							$res_dnc_contact_details = $this->conn_idc_dnc->query_sql($update_dnc_contact_details);
							$returnstr = 'dnc.dndlist  updated  with  safe_till set to:'.$end_date;
						}
						else
						{
							$returnstr = 'dnc.dndlist not updated dndnumber blank ';
						}
					}
				}
			}
		}
		else
		{
			$returnstr = 'Parentid not found in updateDNDList ';
		}
		return $returnstr;
	}


/* THIS FUNCTION WILL UPDATE is_deleted flag of number in table
 last argument wil used for which city we have to update by default it is 0 means update all city */
	function updateis_deletedflag($DNDnumber)
	{				
		$ipthirdpos="#";		
		$updatednd = "UPDATE ".DB_DNC_LIVE.".dndlist  SET is_deleted=1,done_flag=2 where dndnumber=".$DNDnumber."";
		$this->conn_idc_dnc->query_sql($updatednd);
		return IDC_DB_APP_IP;
		
	}
	
	
	function getValidNumDNC($phone,$stdcode='')
	{
		$phone = trim($phone);
		$phone = ltrim($phone,0);

		if(strlen($phone)==10)
		{
			return $phone;
		}
		else
		{
			if(trim($stdcode)!='')
			{
				return ltrim(trim($stdcode), '0').$phone;
			}
		}
		return '';
	}
    function getAllPhoneNumbers($phone_nos) 
    {
        $phone_info_arr = explode('|~|', $phone_nos);
        $phone_arr = array();
        foreach ($phone_info_arr as $phone_info) 
        {
            if (trim($phone_info)!='') 
            {
                $phoneinfo_sub_arr = explode('|^|',$phone_info);                                
                $phone_no = $phoneinfo_sub_arr[count($phoneinfo_sub_arr)-2] ;
                if (isset($phone_no) && trim($phone_no)!='')
                {
                    $phone_arr[] = $phone_no;
                }
            }		
        }
        return $phone_arr;	
    }

}
?>
