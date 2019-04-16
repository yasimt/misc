<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);


class bformClass extends DB
{
	
	function __construct($datarrConstr=null)
	{
		if($datarrConstr)
		{
			if($datarrConstr['parentid'])
			{
				$this->parentid = $datarrConstr['parentid'];
				$this->ucode	= $datarrConstr['ucode'];
				$this->data_city	= $datarrConstr['data_city'];
			}
		}
	}	
	function dataInString($seperator,$numbarray)
	{
		
		if(count($numbarray) > 0)
		{
			$numbstring=implode($seperator,$numbarray);
			return $numbstring;
		}
		else
		{
			return false;
		}
	}
	
	function addslashesArray($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
	
	function trim_value($value) 
	{
		$value = trim($value); 
		return $value;
	}
	
	function stringToarray($seperator,$numbstring)
	{
			$numbarray=explode($seperator,$numbstring);
			return $numbarray;
	}

	function getGenInfoShadow($parentId,$compmaster_obj)
	{
		$temparr	 = array();
		$fieldstr	 = '';
		$fieldstr 	 = "*";
		$tablename	 = "tbl_companymaster_generalinfo_shadow";
		$wherecond	 = "parentid='".$parentId."'";
		$temparr  	 = $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		$genInfoArr	 = $temparr['data']['0'];

		return $genInfoArr;
	}

	function getextradetailsInfoShadow($parentId,$compmaster_obj)
	{

		$temparr	 = array();
		$fieldstr	 = '';
		$fieldstr 	 = "*";
		$tablename	 = "tbl_companymaster_extradetails_shadow";
		$wherecond	 = "parentid='".$parentId."'";
		$temparr  	 = $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		$extDetArr	 = $temparr['data']['0'];

		return $extDetArr;

	}

	function getGenInfoMain($parentId,$compmaster_obj)
    {
		global $conn_tme,$conn_idc;

		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 	 = "*";
		$tablename	 = "tbl_companymaster_generalinfo";
		$wherecond	 = "parentid='".$parentId."'";
		$temparr  	 = $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		$genInfoArr	 = $temparr['data']['0'];

        return $genInfoArr;
    }

	function getContactDetails($parentid,$conn_iro,$compmaster_obj)
	{
		$landline_arr 		= array();
		$mobile_arr   		= array();
		$tollfree_arr   	= array();
		$contact_arr 		= array();
		$landline_num_arr 	= array();
		$mobile_num_arr 	= array();

		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 	 ="landline,landline_display,mobile,mobile_display,tollfree";
		$tablename	 = "tbl_companymaster_generalinfo_shadow";
		$wherecond	 = "parentid='".$parentid."'";
		$temparr  	 = $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		$row_gen_info= $temparr['data']['0'];

		if($row_gen_info['landline_display'])
		$landline_arr = explode(',',$row_gen_info['landline_display']);

		if($row_gen_info['mobile_display'])
		$mobile_arr   = explode(',',$row_gen_info['mobile_display']);

		if($row_gen_info['tollfree'])
		$tollfree_arr   = explode(',',$row_gen_info['tollfree']);

		$contact_arr = array_merge($landline_arr,$mobile_arr,$tollfree_arr);
		$contact_arr = array_filter($contact_arr);
		$contact_arr = array_merge($contact_arr);

		if(COUNT($contact_arr)<=0)
            {
				unset($contact_arr);
				if(trim($row_gen_info['landline'])!='')
				{
					$landline_num_arr = explode(",",trim($row_gen_info['landline']));
				}

				if($row_gen_info['mobile'])
				{
					$mobile_num_arr = explode(",",trim($row_gen_info['mobile']));
				}

				$contact_arr = array_merge($landline_num_arr,$mobile_num_arr);
				$contact_arr = array_filter($contact_arr);
				$contact_arr = array_merge($contact_arr);
			}
		return $contact_arr;
	}

	function getextradetailsInfoMain($compmaster_obj,$parentId)
    {

		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 	= "*";
		$tablename	= "tbl_companymaster_extradetails";
		$wherecond	= "parentid= '".$parentId."'";
		$temparr  	= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		$extDetArr	= $temparr['data']['0'];
        return $extDetArr;
    }

	function populateShadowTable($postarr,$conn_iro,$conn_local) //populate shadow table
	{
		//~ echo "<pre>postarr-->>";print_r($postarr);
		//echo "populateShadowTable called";
		//print_r($conn_iro);
		//global $geocode_obj, $conn_local, $conn_tme, $conn_iro, $genio_variables;
		
		
		$postarr= $this->addslashesArray($postarr); /*--stripping and adding slashes in the array----*/
		$date = date('Y-m-d h:i:s');		
			
			$landmarkarr = explode(" ~ ",$postarr[landmark]);
			$postarr[landmark]= trim($landmarkarr[0]);
			
			$streetarr = explode(" ~ ",$postarr[street]);
			$postarr[street]= trim($streetarr[0]);

			/*TEXT COMMENT 'concatenated Address'*/ 
				//if(trim($postarr[bname]))
				//$addrarray[]=trim($postarr[bname]);
				if(trim($postarr[landmark]))
				$addrarray[]=trim($postarr[landmark]);
				if(trim($postarr[plot]))
				$addrarray[]=trim($postarr[plot]);
				if(trim($postarr[street]))
				$addrarray[]=trim($postarr[street]);
				if(trim($postarr[area]))
				$addrarray[]=trim($postarr[area]);
				
			$fulladdress = $this->dataInString(',',$addrarray);
			
			$fulladdress =	$fulladdress."-".$postarr[pincode];
			
			/*TEXT COMMENT 'concatenated landline numbers'*/ 
			if(trim($postarr[phone]))
				$telarray[]=trim($postarr[phone]);
			if(trim($postarr[phone2]))
				$telarray[]=trim($postarr[phone2]);
			if(trim($postarr[phone3]))
				$telarray[]=trim($postarr[phone3]);
			if(trim($postarr[phone4]))
				$telarray[]=trim($postarr[phone4]);
			/* concatinating additional phone numbers in the same landline strings
    [addMobile] => 
    [addtelemobile*/
			if(trim($postarr[addtele]))
				$telarray[]=trim($postarr[addtele]);
			
			$landline = $this->dataInString(',',$telarray);
			if($landline!=''){
				$landlineAddinfo = '';
				$tele_count = 1;
				foreach($telarray as $key =>$telnumber){
					if($telnumber!='' && $tele_count<=4){
						/*if($landlineAddinfo){
							$landlineAddinfo .= trim($telnumber).'|^|'.trim($postarr['tele'.$key.'_addinfo']).'|~|';
						}else{*/
							$landlineAddinfo .= trim($telnumber).'|^|'.trim($postarr['tele'.($key+1).'_addinfo']).'|~|';
						/*}*/
					}
					$tele_count +=1;
				}
				
				//echo "<br>".$landlineAddinfo = rtrim($landlineAddinfo,"|~|");exit;
				$landlineAddinfo = substr($landlineAddinfo,0,-3);//echo "<br>".$landlineAddinfo;exit;
				/*$landlineAddinfo =trim($postarr[phone]).'|^|'.trim($postarr[tele1_addinfo]).'|~|'.trim($postarr[phone2]).'|^|'.trim($postarr[tele2_addinfo]).'|~|'.trim($postarr[phone3]).'|^|'.trim($postarr[tele3_addinfo]).'|~|'.trim($postarr[phone4]).'|^|'.trim($postarr[tele4_addinfo]);*/
				$add_landlineAddinfo='';
				if($postarr[addtele_counter]>1){
					for($i=1; $i<$postarr[addtele_counter]; $i++){
						if($postarr['add_telenumber_'.$i]!=''){
							$add_landlineAddinfo .='|~|'.$postarr['add_telenumber_'.$i].'|^|'.$postarr['add_tele_addinfo'.$i];
						}
					}
				}
				$landlineAddinfo.= $add_landlineAddinfo;
			}
			
			
			/* TEXT COMMENT feedback landline numbers*/
			$landlineFeedback=" "; 

			/* TEXT  COMMENT 'concatenated mobile numbers'*/ 	
			if(trim($postarr[mobile]))
				$mobarray[]=trim($postarr[mobile]);
			if(trim($postarr[mobile2]))
				$mobarray[]=trim($postarr[mobile2]);
			if(trim($postarr[mobile3]))
				$mobarray[]=trim($postarr[mobile3]);
			if(trim($postarr[mobile4]))
				$mobarray[]=trim($postarr[mobile4]);
				
			/* concatinating additional mobile numbers in the same mobile strings */
			if(trim($postarr[addtelemobile]))
				$mobarray[]=trim($postarr[addtelemobile]);
			
			$mobile = $this->dataInString(',',$mobarray);
			
			if($mobile!=''){
				$mobileAddinfo = '';
				$mobil_count =1;//echo "<pre>";print_r($postarr); echo "</pre>";
				
				foreach($mobarray as $key1 => $mobNumber){
					if(trim($mobNumber)!='' && $mobil_count<=4){
						$mobileAddinfo .= trim($mobNumber).'|^|'.trim($postarr['mobile'.($key1+1).'_addinfo']).'|~|';
						$mobil_count++;
					}
				}
				$mobileAddinfo = substr($mobileAddinfo,0,-3);//echo "<br>".$mobileAddinfo;exit;
				/*$mobileAddinfo =trim($postarr[mobile]).'|^|'.trim($postarr[mobile1_addinfo]).'|~|'.trim($postarr[mobile2]).'|^|'.trim($postarr[mobile2_addinfo]).'|~|'.trim($postarr[mobile3]).'|^|'.trim($postarr[mobile3_addinfo]).'|~|'.trim($postarr[mobile4]).'|^|'.trim($postarr[mobile4_addinfo]);*/
				$add_mobileAddinfo='';
				if($postarr[addtele_counter_mobile]>1){
					for($i=1; $i<$postarr[addtele_counter_mobile]; $i++){
						if($postarr['add_telenumber_mobile_'.$i]!=''){
							$add_mobileAddinfo .='|~|'.$postarr['add_telenumber_mobile_'.$i].'|^|'.$postarr['add_mobile_addinfo'.$i];
						}
					}
				}
				$mobileAddinfo.= $add_mobileAddinfo;
			}			
			
			/* website handling starts here */
			//~ if(trim($postarr[site_1]))
				//~ $websiteArr[] = trim($postarr[site_1]);
			//~ if(trim($postarr[site_2]))
				//~ $websiteArr[] = trim($postarr[site_2]);
				/*concatinating additional websites*/
			if(trim($postarr[addwebsites]))
				$websiteArr[]=trim($postarr[addwebsites]);
			
				$website = $this->dataInString(',',$websiteArr);
				$website = rtrim($website,",");
			/* website handling ends here */
			
			/* OTHER CITY NUMBERS */
			$othercity_number = trim($postarr[OtherCityNumber]);
			/* TEXT COMMENT 'concatenated fax numbers'*/ 
			if(trim($postarr[fax]))
				$faxarray[]=trim($postarr[fax]);
			if(trim($postarr[fax2]))
				$faxarray[]=trim($postarr[fax2]);
			$fax = $this->dataInString(',',$faxarray);

			/* TEXT  COMMENT 'concatenated tollfree numbers' */
			if(trim($postarr[tollfree]))
				$tollfreearray[]=trim($postarr[tollfree]);
			if(trim($postarr[tollfree_1]))
				$tollfreearray[]=trim($postarr[tollfree_1]);
			
			$tollfree = $this->dataInString(',',$tollfreearray);
			if($tollfree){
				if(trim($postarr[tollfree])!=''){
				$tollfreeAddinfo = trim($postarr[tollfree]).'|^|'.trim($postarr[tollfree_addinfo]).'|~|';
				}
				if(trim($postarr[tollfree_1])!='' && $tollfreeAddinfo!=''){
					$tollfreeAddinfo .= trim($postarr[tollfree_1]).'|^|'.trim($postarr[tollfree1_addinfo]);
				}
			}
			//$tollfreeAddinfo=trim($postarr[tollfree]).'|^|'.trim($postarr[tollfree_addinfo]).'|~|'.trim($postarr[tollfree_1]).'|^|'.trim($postarr[tollfree1_addinfo]);

			/* TEXT COMMENT 'concatenated email ids' */
			if(trim($postarr[email]))
				$emailarray[]=trim($postarr[email]);
			if(trim($postarr[email2]))
				$emailarray[]=trim($postarr[email2]);
			if(trim($postarr[email3]))
				$emailarray[]=trim($postarr[email3]);
			if(trim($postarr[email4]))
				$emailarray[]=trim($postarr[email4]);
			$email = $this->dataInString(',',$emailarray);
			
			if($postarr[additional_email_counter]>4){
					for($i=5; $i<=$postarr[additional_email_counter]; $i++){
						if($postarr['email'.$i]!=''){
							$email.= ','.$postarr['email'.$i];
						}
					}
			}

			/* TEXT COMMENT 'concatenated displable phone no's'*/
			if($postarr[phone_display]=='1')
				$teldisparray[]=trim($postarr[phone]);
			if($postarr[phone_2_display]=='1')
				$teldisparray[]=trim($postarr[phone2]);
			if($postarr[phone_3_display]=='1')
				$teldisparray[]=trim($postarr[phone3]);
			if($postarr[phone_4_display]=='1')
				$teldisparray[]=trim($postarr[phone4]);
			$phoneDisplay = $this->dataInString(',',$teldisparray);
		
			/* TEXT COMMENT 'concatenated displable mobile nos'*/
			if($postarr[mobile_1_Display]=='1')
				$mobdisparray[]=trim($postarr[mobile]);
			if($postarr[mobile_2_Display]=='1')
				$mobdisparray[]=trim($postarr[mobile2]);
			if($postarr[mobile_3_Display]=='1')
				$mobdisparray[]=trim($postarr[mobile3]);
			if($postarr[mobile_4_Display]=='1')
				$mobdisparray[]=trim($postarr[mobile4]);
			$mobileDisplay = $this->dataInString(',',$mobdisparray);
            
			$additional_MobileDisplayArray = array();
            for($i=1; $i<$postarr[addtele_counter_mobile]; $i++){
				if($postarr['add_mobile_Display_'.$i] == '1')
				{
					if($postarr['add_telenumber_mobile_'.$i]) {
						array_push($additional_MobileDisplayArray, $postarr['add_telenumber_mobile_'.$i]);
					}
				}
            }
            
            $append_additional_Mobnumbers= $this -> dataInString(',', $additional_MobileDisplayArray);
            if($append_additional_Mobnumbers!=''){
                $mobileDisplay = $mobileDisplay.','.$append_additional_Mobnumbers;
            }
            
			 /* TEXT COMMENT 'concatenated displable email ids'*/
			if($postarr[email_1_Display]=='1')
				$emaildisparray[]=trim($postarr[email]);
			if($postarr[email_2_Display]=='1')
				$emaildisparray[]=trim($postarr[email2]);
			if($postarr[email_3_Display]=='1')
				$emaildisparray[]=trim($postarr[email3]);
			if($postarr[email_4_Display]=='1')
				$emaildisparray[]=trim($postarr[email4]);
			$email_display = $this->dataInString(',',$emaildisparray);
			
	
			if($postarr[additional_email_counter]>4){
					for($i=5; $i<=$postarr[additional_email_counter]; $i++){
						if($postarr['email_'.$i.'_Display']==1){
							$email_display.= ','.$postarr['email'.$i];
						}
					}
			}	
			
			
			
			/* TEXT COMMENT 'concatenated displable tollfree numbers'*/
			if($postarr[tollfree_display]=='on'){
				$tollfree_display.=$postarr[tollfree];
				}
			
			
			/* TEXT COMMENT 'concatenated contact person'*/
			if(trim($postarr[person]))
				$personarray[]=  trim($postarr[person]);
			if(trim($postarr[person2]))
				$personarray[]= trim($postarr[person2]);
			
			if(isset($postarr[additional_person_counter]) && $postarr[additional_person_counter]>2){
				for($i=3; $i<=$postarr['additional_person_counter']; $i++){
					if($postarr['person'.$i]!=''){
						$personarray[$i]= $postarr['person'.$i];
					}
				}
			}
				
			$contact_person = $this->dataInString(',',$personarray);
				
			$contact_person_display= " "; /* TEXT COMMENT 'concatenated displayable contact person'*/
			
			/******************FEEDBACK PROCESSING - ROHIT***********************/
			$femail=0;
			if($postarr['mobile_1_not']!='' && $postarr['mobile']!='')//1ST MOBILE
			{
				if(strlen(trim($postarr['mobile']))==8)// ADDED ON SUGGESTION OF MEETA - 24/5/2008
				{
					$fbmobile=$postarr['stdcode'].$postarr['mobile'];
				}
				else
				{
					$fbmobile=$postarr['mobile'];
				}
			}
			if($postarr['mobile_2_not']!='' && $postarr['mobile2']!='')//2ND MOBILE
			{
				if($fbmobile)
				{
					if(strlen(trim($postarr['mobile2']))==8)
					{
						$fbmobile.=",".$postarr['stdcode'].$postarr['mobile2'];
					}
					else
					{
						$fbmobile.=",".$postarr['mobile2'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile2']))==8)
					{
						$fbmobile=$postarr['stdcode'].$postarr['mobile2'];
					}
					else
					{
						$fbmobile=$postarr['mobile2'];
					}
				}
			}
			if($postarr['mobile_3_not']!='' && $postarr['mobile3']!='')//2ND MOBILE
			{
				if($fbmobile)
				{
					if(strlen(trim($postarr['mobile3']))==8)
					{
						$fbmobile.=",".$postarr['stdcode'].$postarr['mobile3'];
					}
					else
					{
						$fbmobile.=",".$postarr['mobile3'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile3']))==8)
					{
						$fbmobile=$postarr['stdcode'].$postarr['mobile3'];
					}
					else
					{
						$fbmobile=$postarr['mobile3'];
					}
				}
			}
			if($postarr['mobile_4_not']!='' && $postarr['mobile4']!='')//2ND MOBILE
			{
				if($fbmobile)
				{
					if(strlen(trim($postarr['mobile4']))==8)
					{
						$fbmobile.=",".$postarr['stdcode'].$postarr['mobile4'];
					}
					else
					{
						$fbmobile.=",".$postarr['mobile4'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile4']))==8)
					{
						$fbmobile=$postarr['stdcode'].$postarr['mobile4'];
					}
					else
					{
						$fbmobile=$postarr['mobile4'];
					}
				}
			}
			
			
			if($postarr['mobile_1_noti']!='' && $postarr['mobile']!='')//1ST MOBILE
			{
				if(strlen(trim($postarr['mobile']))==8)// ADDED ON SUGGESTION OF MEETA - 24/5/2008
				{
					$fbnotimobile=$postarr['stdcode'].$postarr['mobile'];
				}
				else
				{
					$fbnotimobile=$postarr['mobile'];
				}
			}
			if($postarr['mobile_2_noti']!='' && $postarr['mobile2']!='')//2ND MOBILE
			{
				if($fbnotimobile)
				{
					if(strlen(trim($postarr['mobile2']))==8)
					{
						$fbnotimobile.=",".$postarr['stdcode'].$postarr['mobile2'];
					}
					else
					{
						$fbnotimobile.=",".$postarr['mobile2'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile2']))==8)
					{
						$fbnotimobile=$postarr['stdcode'].$postarr['mobile2'];
					}
					else
					{
						$fbnotimobile=$postarr['mobile2'];
					}
				}
			}
			if($postarr['mobile_3_noti']!='' && $postarr['mobile3']!='')//2ND MOBILE
			{
				if($fbnotimobile)
				{
					if(strlen(trim($postarr['mobile3']))==8)
					{
						$fbnotimobile.=",".$postarr['stdcode'].$postarr['mobile3'];
					}
					else
					{
						$fbnotimobile.=",".$postarr['mobile3'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile3']))==8)
					{
						$fbnotimobile=$postarr['stdcode'].$postarr['mobile3'];
					}
					else
					{
						$fbnotimobile=$postarr['mobile3'];
					}
				}
			}
			if($postarr['mobile_4_noti']!='' && $postarr['mobile4']!='')//2ND MOBILE
			{
				if($fbnotimobile)
				{
					if(strlen(trim($postarr['mobile4']))==8)
					{
						$fbnotimobile.=",".$postarr['stdcode'].$postarr['mobile4'];
					}
					else
					{
						$fbnotimobile.=",".$postarr['mobile4'];
					}
				}
				else
				{
					if(strlen(trim($postarr['mobile4']))==8)
					{
						$fbnotimobile=$postarr['stdcode'].$postarr['mobile4'];
					}
					else
					{
						$fbnotimobile=$postarr['mobile4'];
					}
				}
			}
			
			
			if($postarr['email_1_not']!='' && $postarr['email']!='')//1ST EMAIL
			{
				$fbemail=$postarr['email'];
				$femail=1;
			}
			if($postarr['email_2_not']!='' && $postarr['email2']!='') //2ND EMAIL
			{
				if($fbemail)
				{
					$fbemail.=",".$postarr['email2'];
				}
				else
				{
					$fbemail=$postarr['email2'];
				}
				if($femail==1)
				{
					$femail=3;
				}
				else
				{
					$femail=2;
				}
			}
			/* TEXT COMMENT 'feedback mobile numbers'*/
			$mobileFeedback= $fbmobile;//	die;
			 /* TEXT COMMENT 'feedback email ids'*/
			$email_feedback= $fbemail;
			/*****************************************/
				/************************************************************/
				$dotcom = $postarr[flags];
				
			$sql_paid = "SELECT dotcom FROM tbl_temp_intermediate WHERE parentid = '".$this->parentid."'";
				
				$qry_paid=parent::execQuery($sql_paid, $conn_local);
				//$qry_paid = $conn_local->query_sql($sql_paid);
				//print_r($conn_local);
				if($qry_paid && mysql_num_rows($qry_paid)){
					$row_paid = mysql_fetch_assoc($qry_paid);
					
					$dotcom  = (isset($row_paid['dotcom'])?$row_paid['dotcom']:0);
				}
				
				
				if($dotcom == 1 && ($postarr[flags] &512) != 512 )
				{
					
					$postarr[flags]=$postarr[flags]+512; 
				}
				
				/****************************************************************************/			
				
				
				
				/*start for statement*/
				$sql ="select categories from tbl_business_temp_data where contractid='".$this->parentid."'";
				{
					//$res_sql=$conn_local->query_sql($sql);
					$res_sql=parent::execQuery($sql, $conn_local);
				}
				
				
				if($res_sql && mysql_num_rows($res_sql))
				{
					$row_sql=mysql_fetch_assoc($res_sql);
					$temp_categoris=$row_sql['categories'];
				}
				$cat_flag=0;
				if($temp_categoris!='')
				{
					$temp_categories_array=array();
					$temp_categories_array=explode("|P|",$temp_categoris); 
					if(sizeof($temp_categories_array)>0)
					{
						for($cat_count=1;$cat_count<=sizeof($temp_categories_array);$cat_count++)
						{ 	
							preg_match("/[a-zA-Z ]*hotel[a-zA-Z0-9 ]*([(]rs|star|[(][a-zA-Z0-9 ]*price)|([a-z0-9 ]*24 hours[a-z0-9 ]*)|([a-z0-9 ]*star[a-z0-9 ]*hotel[a-z0-9 ]*)/i", $temp_categories_array[$cat_count],$matches);
					
							if((preg_match("/[a-zA-Z ]*hotel[a-zA-Z0-9 ]*([(]rs|star|[(][a-zA-Z0-9 ]*price)|([a-z0-9 ]*24 hours[a-z0-9 ]*)|([a-z0-9 ]*star[a-z0-9 ]*hotel[a-z0-9 ]*)/i", $temp_categories_array[$cat_count],$matches)) && (!preg_match("/[a-zA-Z ]*reservation[a-zA-Z0-9 ]*/i", $temp_categories_array[$cat_count],$matches1)))
							{
								$cat_flag=1;
								break;
							}
						}
					}
				}	
				if($cat_flag=='1')
				{ 
					for($i = 0;$i<count($pieces);$i++)
					{
						$pieces[$i] = "00:00"; 
						$pieces1[$i]=  "23:59";
						$pieces2[$i] = "00:00";
						$pieces3[$i] = "23:59";
					} 
				}
					
				/*end*/
				$starttimeArray = array('timemon1','timetue1','timewed1','timethu1','timefri1','timesat1','timesun1');
				$endtimeArray   = array('timemon2','timetue2','timewed2','timethu2','timefri2','timesat2','timesun2');
				$starttimeArray1 = array('timemon3','timetue3','timewed3','timethu3','timefri3','timesat3','timesun3');
				$endtimeArray1   = array('timemon4','timetue4','timewed4','timethu4','timefri4','timesat4','timesun4');
				$starttimevalueArray = array();
				$endtimevalueArray   = array();
				$starttimevalueArray1 = array();
				$endtimevalueArray1   = array();
				foreach($postarr as $key=>$value)
				{
					if(in_array($key, $starttimeArray))
					{
						array_push($starttimevalueArray ,$_POST[$key]);
					}
					elseif(in_array($key,$endtimeArray))
					{
						array_push($endtimevalueArray ,$_POST[$key]);
					}
					if(in_array($key, $starttimeArray1))
					{
						array_push($starttimevalueArray1 ,$_POST[$key]);
					}
					elseif(in_array($key,$endtimeArray1))
					{
						array_push($endtimevalueArray1 ,$_POST[$key]);
					}
				}
				
				//$postarr['starttime'] = implode(',',$starttimevalueArray);
				//$postarr['endtime']   = implode(',',$endtimevalueArray);
				//$postarr['starttime1'] = implode(',',$starttimevalueArray1);
				//$postarr['endtime1']   = implode(',',$endtimevalueArray1);
				
								
				
				$pieces  = explode(",", $postarr['starttime']);
				$pieces1 = explode(",", $postarr['endtime']);
				$pieces2 = explode(",", $postarr['starttime1']);
				$pieces3 = explode(",", $postarr['endtime1']);
				
				for($i1=0;$i1<count($pieces);$i1++){
					if(in_array("00:00", $pieces2)) 
					{ //echo "01"; 
					      $sta1 .= $pieces[$i1].","; 
					}
					else
					{ 
					 //echo "11";
						if(isset($pieces2[$i1]) && $pieces2[$i1]!='')
							$sta1 .= $pieces[$i1]."-".$pieces2[$i1].",";
						else
							$sta1 .= $pieces[$i1].",";
					}
					
				}
				
				$sta1 =substr($sta1,0,-1);
				
				for($i11=0;$i11<count($pieces1);$i11++){
					  if(in_array("23:59", $pieces3)) 
					  {  //echo "02"; 
	 						$sta11 .= $pieces1[$i11].","; 
         			  } 
 					  else{
					 	//echo "22";
						if(isset($pieces3[$i11]) && $pieces3[$i11]!='')
							$sta11 .= $pieces1[$i11]."-".$pieces3[$i11].",";
						else
							$sta11 .= $pieces1[$i11].",";
					}
										
				}
				
				$sta11 =substr($sta11,0,-1);
				
				
				if($sta1=='-,') {$sta1="";}
				if($sta11=='-,') {$sta11="";}

				$arrpay = array('Cash-1','Master Card-1','Visa Card-1','Debit Cards-1','Discover-0','Money Orders-1','Cheques-1','Diners Club Card-1','Travelers Check-1','Credit Terms Available-0','Financing Available-1','American Express Card-1','Credit Card-1');

				$c=0;
				$str_final_cctype = '';
				if(count($arrpay)>0){
					foreach($arrpay as $key=>$value){
						$arrpaysub=explode("-",$value);
						$name_org = $arrpaysub[0];
						$repStr = str_replace(" ", "_", $arrpaysub[0]);
						if($postarr[$repStr] == 'on'){
							$str_final_cctype .=$name_org."~";
						}
					 }
					   $str_final_cctype  = substr($str_final_cctype ,0,-1);
				}	
				if($postarr['stmt1']=='on' || $postarr['stmt1']=='1' )
				{
					$postarr['stmt1']='1';
				}
				else{
					$postarr['stmt1']='0';
				}
				if($postarr['stmt2']=='on' || $postarr['stmt2']=='1' )
				{
							$postarr['stmt2']='1';
				}
				else{
							$postarr['stmt2']='0';
				}		
				if($postarr['stmt3']=='on' || $postarr['stmt3']=='1' )
				{
							$postarr['stmt3']='1';
				}
				else{
							$postarr['stmt3']='0';
				}
				if($postarr['stmt4']=='on' || $postarr['stmt4']=='1' )
				{
							$postarr['stmt4']='1';
				}
				else{
							$postarr['stmt4']='0';
				}
				if($postarr['stmt5']=='on' || $postarr['stmt5']=='1' )
				{
							$postarr['stmt5']='1';
				}
				else{
							$postarr['stmt5']='0';
				}
				if($postarr['stmt6']=='on' || $postarr['stmt6']=='1' )
				{
							$postarr['stmt6']='1';
				}
				else{
							$postarr['stmt6']='0';
				}
				if($postarr['stmt7']=='on' || $postarr['stmt7']=='1' )
				{
							$postarr['stmt7']='1';
				}
				else{
							$postarr['stmt7']='0';
				}
									
				//$stmt1=$postarr['stmt4'].$postarr['stmt3'].$postarr['stmt2'].$postarr['stmt1'];
				//$stmt1= bindec($stmt1);

				/*if($postarr['stmt2']=='on' || $postarr['stmt2']=='1' )
				{
							$postarr['stmt2']='1';
				}
				else{
							$postarr['stmt2']='0';
				}		
				if($postarr['stmt3']=='on' || $postarr['stmt3']=='1' )
				{
							$postarr['stmt3']='1';
				}
				else{
							$postarr['stmt3']='0';
				}
				if($postarr['stmt4']=='on' || $postarr['stmt4']=='1' )
				{
							$postarr['stmt4']='1';
				}
				else{
							$postarr['stmt4']='0';
				}*/
										
			$stmt1=$postarr['stmt7'].$postarr['stmt6'].$postarr['stmt5'].$postarr['stmt4'].$postarr['stmt3'].$postarr['stmt2'].$postarr['stmt1'];
			$stmt1= bindec($stmt1);
			/* Query to fetch state name from stateid */
			
			/* HIDE LANDLINE IN DISPLAY IF "CHECKED ON HIDE IN SEARCH" - STARTS */
			$val = $this->get_extradetails_shadow($conn_iro);
			$val = explode('#$#', $val);
			$prev_landline_display_arr	=  explode(',', $val[1]);
			/*$append_additional_numbers = '';
            if($postarr['addtele']!='')
            {
                $append_additional_numbers.= ','.$postarr[addtele];
            }*/
            $additional_landlineDisplayArray = array();
            for($i=1; $i<$postarr[addtele_counter]; $i++){
				if(!($postarr['add_hide_landline'.$i] == 'on' || $postarr['add_hide_landline'.$i] == 1))
				{
					if($postarr['add_telenumber_'.$i]) {
						array_push($additional_landlineDisplayArray, $postarr['add_telenumber_'.$i]);
					}
				}
            }
            
			$landlineDisplayArray = array();
			for($i = 1; $i <= 4; $i++)
			{				
				if(!($postarr[hide_landline.$i] == 'on' || $postarr[hide_landline.$i] == 1))
				{
					$j = ($i == 1 ? '' : $i);
					if($postarr[phone.$j]) {
						array_push($landlineDisplayArray, $postarr[phone.$j]);
					}
					unset($j);
				}
			}
			$landlineDisplay = $this -> dataInString(',', $landlineDisplayArray);
            $append_additional_numbers= $this -> dataInString(',', $additional_landlineDisplayArray);
            if($append_additional_numbers!=''){
                $landlineDisplay = $landlineDisplay.','.$append_additional_numbers;
            }
			$landlineDisplay = rtrim(ltrim($landlineDisplay, ','), ',');
			/* HIDE LANDLINE IN DISPLAY IF "CHECKED ON HIDE IN SEARCH" - ENDS  */
			/*---------------------HIDE ADDRESS IN SEARCH---------STARTS*/
			$postarr[hide_address] = $postarr[hide_address] == 'on' ? 1 : 0;
			/*---------------------HIDE ADDRESS IN SEARCH---------ENDS*/
			$this->checkNoChange($conn_iro, $landline,$mobile);
			$qry_state_name="SELECT DISTINCT st_name, state_id 
								FROM 
								state_master WHERE state_id = '".$postarr[state]."' ORDER by st_name";
			//$res_state_name=$conn_local->query_	sql($qry_state_name);
			$res_state_name=parent::execQuery($qry_state_name, $conn_local);
			$row_state_name=mysql_fetch_assoc($res_state_name);
				
			$select_sphinx_id	= "select sphinx_id from tbl_id_generator where parentid='".$this->parentid."' ";
			//$res_sphinx_id 		= $conn_iro->query_sql($select_sphinx_id);
			$res_sphinx_id			= parent::execQuery($select_sphinx_id, $conn_iro);
			$row_sphinx_id		= mysql_fetch_array($res_sphinx_id);
			
			//print_r($row_sphinx_id);
			//die();
			
			//if($postarr[feedback11] != '')
				$this->InsertFeedbackReason($postarr[feedback11], $postarr[mobile], $conn_local,1);
				$this->InsertFeedbackReason($postarr[feedback22], $postarr[mobile], $conn_local,2);
			/*----------------------------------ends-------------------------------------*/
			/*-------INSERTING PINCODE_WISE_GEOCODE IN GENERALINFO_SHADOW-----STARTS----*/
			/* need confirmation from sneha			
			$changedflag = $geocode_obj ->ISAddresschanged($postarr[area],$postarr[pincode]);
			
			if($changedflag )
			{
				$geocode_pincode_master = $geocode_obj -> select_pincode_wise_geocodes($postarr[pincode], $postarr[area]);
			//echo "<pre>"; print_r($geocode_pincode_master);			
				$Geocodes[0]	=	$geocode_pincode_master[0];
				$Geocodes[1]	=	$geocode_pincode_master[1];
			}
			else   // if area and pincode is not changed then there is no need to get those 
			{
					$Geocodes[0]=$postarr[latitude];
					$Geocodes[1]=$postarr[longitude];
			}
			*/
			
			$Geocodes[0]=$postarr[latitude];
			$Geocodes[1]=$postarr[longitude];
			
			/*-------INSERTING PINCODE_WISE_GEOCODE IN GENERALINFO_SHADOW------ENDS-----*/
			/*-------------------------STDCODE------------------------*/
			$stdcode = $this -> getStdCode($conn_local,$postarr[pincode], $postarr[area], $postarr[stdcode]);
			
			/* city should be changed as per our area selection -  Start*/
			// Chnaged by shital patil - 18-01-2016  
			if($postarr[pincode]!='')
			{
				$cityselect = "SELECT DISTINCT city,state FROM tbl_areamaster_consolidated_v3 WHERE pincode ='".$postarr[pincode]."' AND display_flag=1 AND type_flag=1" ;
				$cityselectrs=parent::execQuery($cityselect, $conn_local);
				
				if($cityselectrs && mysql_num_rows($cityselectrs) == 1)
				{
					$cityselectarr = mysql_fetch_assoc($cityselectrs);
					$cityofarea =   $cityselectarr[city];
					$row_state_name['st_name'] =   $cityselectarr[state];
				}
				else
				{
					$cityofarea = $postarr[city];
				}
			}			
			else 
			{
				$cityofarea = $postarr[city];
			}	
			/* city should be changed as per our area selection -  end */
			
			/* if company status is selected as closed down then add string (Closed down) to company name -- START
			$postarr[bname]=str_replace("(Opening Shortly)", "",str_replace("(Under Renovation)", "",str_replace("(Not in Business)", "", (str_replace("(Not Practicing)", "", (str_replace("(Closed down)", "", $postarr[bname])))))));*/
			//$postarr[bname]= preg_replace("/\([^)]+\)/", "",$postarr[bname]);
			
			$postarr[bname] = trim($postarr[bname]);
			//$postarr[bname]= str_replace("(Open)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Closed Down)", "",$postarr[bname]);
			
			//$postarr[bname]= str_replace("(Shifted)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Std Code)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Customer Care)", "",$postarr[bname]);
			//$postarr[bname]= str_ireplace("(Phone Banking)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Emergency Service)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Not Interested)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Not Practicing Any More)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Not in Business)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Mask)", "",$postarr[bname]);
			//$postarr[bname]= str_replace("(Admin Office)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Under Renovation)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Opening Shortly)", "",$postarr[bname]);
			$postarr[bname]= str_ireplace("(Temporary Closed Down)", "",$postarr[bname]);
			
			$postarr[bname] = trim($postarr[bname]);
			
			if($postarr[bname]!=''){
				switch ($postarr[compstatus]) {
					case 1:
						$pos = strpos($postarr[bname], '(Closed Down)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Closed Down)";
						}
						break;
					/*case 6:
						$pos = strpos($postarr[bname], '(Phone Banking)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Phone Banking)";
						}
						break;*/
					case 9:
						$pos = strpos($postarr[bname], '(Not Practicing Any More)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Not Practicing Any More)";
						}
						break;
					case 10:
						$pos = strpos($postarr[bname], '(Not in Business)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Not in Business)";
						}
						break;
					case 13:
						$pos = strpos($postarr[bname], '(Under Renovation)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Under Renovation)";
						}
						break;
					case 14:
						$pos = strpos($postarr[bname], '(Opening Shortly)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Opening Shortly)";
						}
						break;
					/*case 0:
						$pos = strpos($postarr[bname], '(Open)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Open)";
						}
						break;
					case 2:
						$pos = strpos($postarr[bname], '(Shifted)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Shifted)";
						}
						break;
					case 4:
						$pos = strpos($postarr[bname], '(Std Code)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Std Code)";
						}
						break;
					case 5:
						$pos = strpos($postarr[bname], '(Customer Care)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Customer Care)";
						}
						break;
					case 6:
						$pos = strpos($postarr[bname], '(Phone Banking)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Phone Banking)";
						}
						break;
					case 7:
						$pos = strpos($postarr[bname], '(Emergency Service)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Emergency Service)";
						}
						break;
					case 8:
						$pos = strpos($postarr[bname], '(Not Interested)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Not Interested)";
						}
						break;
					case 11:
						$pos = strpos($postarr[bname], '(Mask)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Mask)";
						}
						break;
					case 12:
						$pos = strpos($postarr[bname], '(Admin Office)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Admin Office)";
						}
						break;*/
					case 15:
						$pos = strpos($postarr[bname], '(Temporary Closed Down)');
						if($pos === false){
							//append string to company name.
							$postarr[bname]=trim($postarr[bname])." (Temporary Closed Down)";
						}
						break;
					default:
						break;
				}
			}
			
			// $_SESSION[compname]=$postarr[bname]; session variable to set 
			$mobile_admin = trim($postarr['mobile_admin']);
			$sql_generalinfo_Shadow = "INSERT INTO tbl_companymaster_generalinfo_shadow SET
				  nationalid				='',
				  sphinx_id					='".$row_sphinx_id[sphinx_id]."',
				  regionid					= '".$postarr[regionid]."',
				  companyname				='".$postarr[bname]."' ,
				  parentid					='".$this->parentid."',
				  country 					='98',
				  state						='".$row_state_name['st_name']."',
				  city						='".$cityofarea."',
				  display_city				='".$cityofarea."',
				  area 						='".$postarr[area]."',
				  subarea					='',
				  office_no 				='',
				  building_name				='".$postarr[plot]."',
				  street					='".$postarr[street]."',
				  street_direction			='',
				  street_suffix				='',
				  landmark					='".$postarr[landmark]."',
				  landmark_custom 			='',
				  pincode 					='".$postarr[pincode]."',
				  pincode_addinfo			='',
				  latitude					='".$Geocodes[0]."',
				  longitude					='".$Geocodes[1]."',
				  geocode_accuracy_level	='".$postarr[geocode_accuracy_level]."',
				  full_address				='".addslashes($fulladdress)."',
				  stdcode					='".$stdcode."',
				  landline 					='".$landline."',
				  landline_display			='".$landlineDisplay."',
				  landline_feedback			='".$landlineFeedback."',
				  mobile					='".$mobile."' ,
				  mobile_display			='".$mobileDisplay."',
				  mobile_feedback			='".$mobileFeedback."',
				  fax						='".$fax."' ,
				  tollfree					='".$tollfree."',
				  tollfree_display			='".$tollfree."',
				  email						='".$email."',
				  email_display				='".$email_display."',
				  email_feedback			='".$email_feedback."',
				  sms_scode					='".$postarr[sms]."',
				  website					='".$website."' ,
				  contact_person			='".$contact_person."',
				  contact_person_display	='".$contact_person."' ,
				  othercity_number			='".$othercity_number."',
				  hide_address				='".$postarr[hide_address]."',
				  paid			    		= 0, /* paid = postarr[paid] */
				  data_city					='".$this->data_city."',
				  mobile_feedback_nft       = '".$fbnotimobile."',
				  cc_status					='".$postarr[Optforcall_chkbx]."',
				  mobile_admin 				= '".$mobile_admin."'
				  
				  ON DUPLICATE KEY UPDATE			  
				  regionid					= '".$postarr[regionid]."', 	
				  companyname				='".$postarr[bname]."' ,
				  country 					='98',
				  state						='".$row_state_name['st_name']."',
				  city						='".$cityofarea."',
				  display_city				='".$cityofarea."',
				  area 						='".$postarr[area]."',
				  subarea					='',
				  office_no 				='',
				  building_name				='".$postarr[plot]."',
				  street					='".$postarr[street]."',
				  street_direction			='',
				  street_suffix				='',
				  landmark					='".$postarr[landmark]."',
				  landmark_custom 			='',
				  pincode 					='".$postarr[pincode]."',
				  pincode_addinfo			='',
				  latitude					='".$Geocodes[0]."',
				  longitude					='".$Geocodes[1]."',	
				  geocode_accuracy_level	='".$postarr[geocode_accuracy_level]."',			  
				  full_address				='".addslashes($fulladdress)."',
				  stdcode					='".$stdcode."',
				  landline 					='".$landline."',
				  landline_display			='".$landlineDisplay."',
				  landline_feedback			='".$landlineFeedback."',
				  mobile					='".$mobile."' ,
				  mobile_display			='".$mobileDisplay."',
				  mobile_feedback			='".$mobileFeedback."',
				  fax						='".$fax."' ,
				  tollfree					='".$tollfree."',
				  tollfree_display			='".$tollfree."',
				  email						='".$email."',
				  email_display				='".$email_display."',
				  email_feedback			='".$email_feedback."',
				  sms_scode					='".$postarr[sms]."',
				  website					='".$website."' ,
				  contact_person			='".$contact_person."',
				  contact_person_display	='".$contact_person."' ,
				  othercity_number			='".$othercity_number."',
				  hide_address				='".$postarr[hide_address]."',
				  data_city					='".$this->data_city."',
				  mobile_feedback_nft       = '".$fbnotimobile."',
				  cc_status					='".$postarr[Optforcall_chkbx]."',
				  mobile_admin 				= '".$mobile_admin."'  ";
		
		
		//$res_save = $conn_iro->query_sql($sql_generalinfo_Shadow);
		$res_save=parent::execQuery($sql_generalinfo_Shadow, $conn_iro);
		
		
		$sql_extradetails_shadow = "INSERT INTO tbl_companymaster_extradetails_shadow SET
				  nationalid='',/* BIGINT(20) DEFAULT NULL COMMENT pan india customer id*/
				  sphinx_id='".$row_sphinx_id[sphinx_id]."',
				  regionid='".$postarr[regionid]."',/* INT(11) NOT NULL DEFAULT '0' COMMENT 'Data city id'*/
				  companyname ='".$postarr[bname]."',/*VARCHAR(250) DEFAULT NULL COMMENT 'name of company'*/
				  parentid='".$this->parentid."',/* VARCHAR(100) DEFAULT NULL COMMENT 'parentid of company'*/
				  landline_addinfo='".$landlineAddinfo."',/* TEXT COMMENT 'additional info of landline nos ex: 28884060-number not in use'*/
				  mobile_addinfo='".$mobileAddinfo."',/* TEXT COMMENT 'additional info of mobile numbers'*/
				  tollfree_addinfo='".$tollfreeAddinfo."',/* TEXT COMMENT 'additional info tollfree numbers'*/ 
				  contact_person_addinfo='',/* TEXT COMMENT 'concatenated contact person format yxz-@#$%^,pqr-!@#$%'*/
				  turnover='".$postarr[turnover]."',/* VARCHAR(55) DEFAULT NULL */
				  working_time_start = '".$sta1."',
				  working_time_end = '".$sta11."',
				  payment_type='".$str_final_cctype."',/* VARCHAR(200) DEFAULT NULL  COMMENT 'mode of payment accepted (CC_type)'*/
				  year_establishment='".$postarr[comp_year_est]."',/* VARCHAR(5) DEFAULT NULL COMMENT 'year od establisment'*/
				  social_media_url='".$postarr[socialMedia]."',
				  accreditations='".$postarr[comp_ass]."',/* VARCHAR(255) DEFAULT NULL COMMENT 'professional associations'*/
				  certificates='".$postarr[comp_cert]."',/* VARCHAR(45) DEFAULT NULL  COMMENT 'certifications'*/
				  no_employee='".$postarr[comp_emp]."',/* VARCHAR(10) DEFAULT NULL COMMENT 'number of employees'*/
				  statement_flag='".$stmt1."',/* TINYINT(2) DEFAULT NULL COMMENT 'statement frequency'*/
				
				
				  fmobile='".$fbmobile."',
				  femail='".$fbemail."',
				  updatedBy='".$postarr[ucode]."',/* VARCHAR(25) DEFAULT NULL COMMENT updated by*/
				  map_pointer_flags='".$postarr[map_pointer_flags]."',
				  flags='".$postarr[flags]."',
				  data_city='".$this->data_city."', /* 'data city of parentid'*/
				  updatedOn='".$date."',/* DATETIME DEFAULT NULL COMMENT 'updated on'*/
				  tag_line='".$postarr[tagline]."',				  
				  tag_description='".$postarr[tagdesc]."',
				  tag_catid='".$postarr[tag_catid]."',
				  tag_catname='".$postarr[catsearchtxt]."',
				  CorporateDealers='".$postarr[CorporateDealers_chkbx]."',
				  helpline_flag='".$postarr['helpline_flag']."',
				  closedown_flag='".$postarr[compstatus]."',
				  createdby='".$postarr[ucode]."',
				  createdtime= NOW(),
				  original_creator='".$postarr[ucode]."',
				  fb_prefered_language='".$postarr[fb_prefered_language]."',
				  nocategory='".$postarr[nocategory]."',
				  original_date=NOW(),
				  company_trademark_flag='".$postarr[company_trademark_flag]."',
				  award = '".$postarr[award]."',
				  testimonial = '".$postarr[testimonial]."',
				  proof_establishment = '".$postarr[proof_establishment]."',
				  misc_flag= '".$postarr[misc_flag]."'
				  
				  
				  
				  ON DUPLICATE KEY UPDATE
				  
				  regionid='".$postarr[regionid]."',/* INT(11) NOT NULL DEFAULT '0' COMMENT 'Data city id'*/	
				  companyname ='".$postarr[bname]."',/*VARCHAR(250) DEFAULT NULL COMMENT 'name of company'*/
				  landline_addinfo='".$landlineAddinfo."',/* TEXT COMMENT 'additional info of landline nos ex: 28884060-number not in use'*/
				  mobile_addinfo='".$mobileAddinfo."',/* TEXT COMMENT 'additional info of mobile numbers'*/
				  tollfree_addinfo='".$tollfreeAddinfo."',/* TEXT COMMENT 'additional info tollfree numbers'*/ 
				  contact_person_addinfo='',/* TEXT COMMENT 'concatenated contact person format yxz-@#$%^,pqr-!@#$%'*/
				  turnover='".$postarr[turnover]."',/* VARCHAR(55) DEFAULT NULL*/
				  working_time_start = '".$sta1."',
				  working_time_end = '".$sta11."',
				  payment_type='".$str_final_cctype."',/* VARCHAR(200) DEFAULT NULL  COMMENT 'mode of payment accepted (CC_type)'*/
				  year_establishment='".$postarr[comp_year_est]."',/* VARCHAR(5) DEFAULT NULL COMMENT 'year od establisment'*/
				  social_media_url='".$postarr[socialMedia]."',
				  accreditations='".$postarr[comp_ass]."',/* VARCHAR(255) DEFAULT NULL COMMENT 'professional associations'*/
				  certificates='".$postarr[comp_cert]."',/* VARCHAR(45) DEFAULT NULL  COMMENT 'certifications'*/
				  no_employee='".$postarr[comp_emp]."',/* VARCHAR(10) DEFAULT NULL COMMENT 'number of employees'*/
				  statement_flag='".$stmt1."',/* TINYINT(2) DEFAULT NULL COMMENT 'statement frequency'*/
				  

				  fmobile='".$fbmobile."',
				  femail='".$fbemail."',
				  updatedBy='".$postarr[ucode]."',/* VARCHAR(25) DEFAULT NULL COMMENT updated by*/
				  map_pointer_flags='".$postarr[map_pointer_flags]."',
				  flags='".$postarr[flags]."',
				  data_city='".$this->data_city."', /* 'data city of parentid'*/
				  updatedOn=now(),/* DATETIME DEFAULT NULL COMMENT 'updated on'*/
				  tag_line='".$postarr[tagline]."',				  
				  tag_description='".$postarr[tagdesc]."',
				  tag_catid='".$postarr[tag_catid]."',
				  tag_catname='".$postarr[catsearchtxt]."',
				  CorporateDealers='".$postarr[CorporateDealers_chkbx]."',
				  helpline_flag='".$postarr['helpline_flag']."',
				  fb_prefered_language='".$postarr[fb_prefered_language]."',
				  nocategory='".$postarr[nocategory]."',
				  closedown_flag='".$postarr[compstatus]."',
				  company_trademark_flag='".$postarr[company_trademark_flag]."',
				  award = '".$postarr[award]."',
				  testimonial = '".$postarr[testimonial]."',
				  proof_establishment = '".$postarr[proof_establishment]."',
				  misc_flag= '".$postarr[misc_flag]."'";
				//$res_save = $conn_iro->query_sql($sql_extradetails_shadow);
				$res_save=parent::execQuery($sql_extradetails_shadow, $conn_iro);

		
				if($postarr[tag_catid])
				{
					$qryCatid 		= "SELECT categories,catIds,nationalcatIds,catSelected FROM tbl_business_temp_data WHERE contractid='".$this->parentid."'";
					$resultset 		= parent::execQuery($qryCatid,$conn_local);
					$resTag			= mysql_fetch_assoc($resultset);			
					$addedTagCat	= $resTag['catIds'];
					$addedTagName	= $resTag['categories'];
							
					$qryCatidmain 	= "SELECT tag_catid, tag_catname FROM tbl_companymaster_extradetails_shadow WHERE parentid='".$this->parentid."'";		   
					$resultsetmain 	= parent::execQuery($qryCatidmain,$conn_iro);
					$resTagmain		= mysql_fetch_assoc($resultsetmain);			
					$mainTagCat		= $resTagmain['tag_catid'];
					$mainTagName	= $resTagmain['tag_catname'];
					
					$addedTagCat	= $addedTagCat."|P|".$mainTagCat;
					//$addedTagCat	= $addedTagCat;
					$addedTagName	= $addedTagName."|P|".$mainTagName;
					//$addedTagName	= $addedTagName;
					
					$catidTagold 	= explode('|P|',$addedTagCat);
					$catidnameTagold= explode('|P|',$addedTagName);
					
					$catidTagold = array_filter($catidTagold);
					$catidTagold = array_unique($catidTagold);
					$catidTagold = array_merge($catidTagold);
					
					$catidnameTagold = array_filter($catidnameTagold);
					$catidnameTagold = array_unique($catidnameTagold);
					$catidnameTagold = array_merge($catidnameTagold);
					
					$temp_business_catids = array();
					$primary_catid = 0;
					$temp_business_catids = explode('|P|',$resTag['catIds']);
					
					$temp_business_catids = array_filter($temp_business_catids);
					$temp_business_catids = array_unique($temp_business_catids);
										
					
					if($mainTagCat != '' && in_array($mainTagCat,$temp_business_catids))
					{
						$primary_catid = 1;     // To check given Primary cate on bfor is already present in existing contract cateories 
					}
					
					//echo "<hr>";print_r($catidTagold);print_r($catidnameTagold);
					
					$catidnameTagold= implode('|P|',$catidnameTagold);
					$catidTagold	= implode('|P|',$catidTagold);
					$catidnameTagold= '|P|'.$catidnameTagold;
					$catidTagold	= '|P|'.$catidTagold;

					$resTagCtdM_sql= "Select tag_catid from tbl_companymaster_extradetails where parentid= '".$this->parentid."'";
					$resTagCtdM_rs = parent::execQuery($resTagCtdM_sql,$conn_iro);
					$resTagCtdM = $resTagCtdM_rs;
					$mnTagCat		= $resTagCtdM['tag_catid'];
					
					$qryCatidUpt 	= "UPDATE tbl_business_temp_data SET categories='".$catidnameTagold."',catIds='".$catidTagold."' WHERE contractid='".$this->parentid."'";
					$resultsetUpt 	= parent::execQuery($qryCatidUpt,$conn_local);
					
					if($resultsetUpt && ($mnTagCat != $mainTagCat) && ($primary_catid == 0)){
						$qryxmlflag = "UPDATE tbl_temp_intermediate 
									   SET nonpaid = '".$postarr[nonpaid]."', 
									   c2c = '".$postarr[c2c]."', 
									   hiddenCon = '".$postarr[hiddenCon]."', 
									   dotcom = '".$postarr[dotcom]."', 
									   exclusive = '".$postarr[exclusive]."', 
									   generatexml = '1' 
									   WHERE contractid='".$this->parentid."' 
									   OR parentid='".$this->parentid."'";
						$resultxmlUpt 	=parent::execQuery($qryxmlflag, $conn_local);
					}
					
				}
				if($postarr[excl_cat_flag]==1){
					$sql_excl_temp =   "INSERT INTO tbl_contract_bypass_exclusion_temp 
								 		SET 
								 			parentid='".$this->parentid."',
								 			reasonid =5,
								 			updatedby= '".$postarr[ucode]."',
								 			updatedon = NOW()
								 		ON DUPLICATE KEY UPDATE									 		
								 			updatedby= '".$postarr[ucode]."',
								 			updatedon = NOW() ";
					$res_excl_temp = parent::execQuery($sql_excl_temp,$conn_iro);
				}
		  return true;
	}

	function InsertFeedbackReason($Reason, $mob_no, $connection,$flagsms)
	{
		global $conn_local;		

		
		$sql_feedback ="INSERT INTO tbl_sms_feedback_deactive_log 
						SET 
						parentid='".$this->parentid."',
						feedback_flag='".$flagsms."',
						user_id='".$this->ucode."',
						deactive_date=now(),
						reason='".addslashes($Reason)."',
						mobilenumber='".$mob_no."'
						
						ON DUPLICATE KEY UPDATE
						
						feedback_flag='".$flagsms."',
						user_id='".$this->ucode."',
						deactive_date=now(),
						reason='".addslashes($Reason)."',
						mobilenumber='".$mob_no."'";
						
		parent::execQuery($sql_feedback,$connection);
	}

	function FetchFeedbackReason($pid, $connection)
	{ return 1;
		$sql_reason = " SELECT reason FROM tbl_sms_feedback_deactive_log WHERE parentid = '".$pid."' order by deactive_date desc limit 1";
			$res_reason = $connection->query_sql($sql_reason);
			$row_reason = mysql_fetch_assoc($res_reason);
			return $row_reason[reason];
	}
	
	function get_extradetails_shadow($conn_iro)
	{
		global $genio_variables;
		$sql_gen_info = "select landline, landline_display from tbl_companymaster_generalinfo_shadow where parentid= '".$genio_variables['parentid']."'";
		$res = parent::execQuery($sql_gen_info,$conn_iro);
		$row = mysql_fetch_assoc($res);
		return $row[landline].'#$#'.$row[landline_display];
	}
	
	function getStdCode($conn_local,$pincode, $area, $current_std)
	{
		$qry = "SELECT stdcode FROM tbl_area_master WHERE pincode = '".$pincode."' AND area = '".$area."' AND display_flag=1 AND type_flag=1 ";
		$res = parent::execQuery($qry,$conn_local);
		$row = mysql_fetch_assoc($res);
		$stdcode = $row['stdcode'];
		if($current_std == '' || $current_std !== $stdcode) {
			$current_std = $stdcode;
		}
		return $current_std;
	}
    /*function  is use to obtained compname_search_singular value in tbl_companymaster_search table*/
    function applyIgnore($str)
	{
		$ig_strt = array('/^\bthe\b/i','/^\bdr\.\s/i','/^\bdr\b/i','/^\bprof\.\s/i','/^\bprof\b/i','/^\band\b/i','/^\bbe\b/i');
		$ig_last = array('/\bpvt\.\s/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\blimited\b/i');
		$s = trim($str);
		$s = preg_replace($ig_strt,'',$s);
		$s = preg_replace($ig_last,'',trim($s));
		$s = trim(preg_replace('/\s\s+/',' ',$s));
		return (strlen($s)<=1) ? $str : $s;
	}
	
	function update_attributes_withcats($conn_local){
		global $genio_variables;
		$sql_data = "SELECT categories,catIds,pages,facility FROM tbl_business_temp_data WHERE contractid='".$this->parentid."'";
		$res_data = parent::execQuery($sql_data,$conn_local);
		$row_data=mysql_fetch_assoc($res_data);

		if($row_data['catIds'])
		{
			$categories_id_string = substr(str_replace("|P|",",",$row_data['catIds']),1);
			$categories_string = $row_data['categories'];
			
			// used the same code as in facility1.php
			
			$cat_types		= $catlist;
			$arr_facilties  = explode(",",$cat_types);
			$arr_facilties  = array_values(array_unique(array_filter($arr_facilties)));
			
			$get_attr_names = "SELECT * FROM tbl_category_attribute";
				$res_attr_names = parent::execQuery($get_attr_names,$conn_local);
				if($res_attr_names && mysql_num_rows($res_attr_names)>0){
					while($row_attr_names = mysql_fetch_assoc($res_attr_names)){
						$group_id 		= $row_attr_names['id'];
						$attribute_name = $row_attr_names['attribute_name'];
						$attrlist_arr[$row_attr_names['id']] = $row_attr_names['attribute_name'];
						
					}
				}
				
				if(count($attrlist_arr)>0){
					$attrlist_arr = array_unique($attrlist_arr);				
				}
				
				$attributes 	= "";	
				
			if($row_data[facility]!='')
			{	
				$temp1_arr=explode("***",$row_data[facility]);
				foreach($temp1_arr as $key=>$value){
						
					$temp2_arr=explode("@@@",$value);					
					$facility_group = $temp2_arr[0];					
					if($attrlist_arr[$facility_group]==''){
						unset($temp1_arr[$key]);
					}else{
						
						if($attributes){
							//$attributes.="###"."|$|".$temp2_arr[1];
							$attributes.="###".$temp2_arr[1];
						}else{
							$attributes=$temp2_arr[1];
						}
					}
				}
				$attributes=str_replace("~~~","-",$attributes);
				$attributes_list = implode("***",$temp1_arr);	
				$attributes= addslashes($attributes);
				$attributes_list=addslashes($attributes_list);
				$sql_save="INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','".$attributes."','".$attributes_list."')
				ON DUPLICATE KEY UPDATE
				mainattr='".$attributes."',facility='".$attributes_list."'";
				
				$res= parent::execQuery($sql_save,$conn_local);
			}
			
		}else
		{
			$sqlUpdateAttributes = "INSERT INTO tbl_business_temp_data(contractid,mainattr,facility) VALUES('".$this->parentid."','','') ON DUPLICATE KEY UPDATE mainattr='',facility=''";		
			$resUpdateAttributes= parent::execQuery($sqlUpdateAttributes,$conn_local);
		}
		
	}

	function checkNoChange($conn_iro,$landline,$mobile){
		
		$numberKeys = array();
		$numberArr	= array();
        $temp_landline = array();
        $temp_mobile = array();
        if($landline!='')
        {
            $temp_landline = explode(",",$landline);
        }

        if($mobile!='')
        {
            $temp_mobile = explode(",",$mobile);
        }
        $numberArr = array_merge(array_filter(array_merge($temp_landline,$temp_mobile)));
		/*if(count($postarr)>0){
			$keys = array_keys($postarr);
			foreach($keys as $values){
				if(strstr($values,'phone') || strstr($values,'mobile'))
					$numberKeys[] = $values;
			}
			if(count($numberKeys)>0){
				$numberKeys = array_unique($numberKeys);
				foreach($numberKeys as $values){
					$numberArr[] = $postarr[$value];
				}
				$numberArr = array_unique($numberArr);
			}
		}*/
		 $sql = "SELECT mobile,landline FROM tbl_companymaster_generalinfo_shadow WHERE parentid ='".$this->parentid."'";
		
		$res_save = parent::execQuery($sql,$conn_iro);
		
		$shadowNumber= array();
		if($res_save && mysql_num_rows($res_save)>0){
			$row_save		= mysql_fetch_assoc($res_save);
			$landlineArr 	= explode(',',$row_save['landline']);
			$mobileArr 		= explode(',',$row_save['mobile']);
			$shadowNumber	= array_merge($landlineArr,$mobileArr);
			$shadowNumber	= array_unique(array_merge(array_filter($shadowNumber)));
		}
		
		$diff = array_diff($numberArr,$shadowNumber);
		if(count($diff)>0){
			//$_SESSION['numberChange'] = 1;  session variable to set 
		}
	}
	
	
	
}
?>
