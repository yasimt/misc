<?php

if(!defined('APP_PATH'))
{
    die("configuration file not included");
    exit;
}
include_once(APP_PATH."business/stemming.class.php"); 
$Stemmer = new  Stemmer();
function synonymAutosuggest($parentid)
{
	global $contact, $popular_cat, $getdata_general,$getdata_extra,$conn_local,$conn_auto;
		
	$Stemmer = new  Stemmer();
		
	$sql_syn="select synID, mainword,synname,paid from tbl_compsyn where parentid ='".$parentid."'";
	$syn_result = $conn_local->query_sql ($sql_syn);
		
	
	while($insyn=mysql_fetch_assoc($syn_result))
	{        
        $syn_update= "";
		if(trim($insyn['mainword'])!=trim($getdata_general['companyname']))
		{
			$synnumarr = explode('(see', $insyn['synname']);	
			$newsynnum = $synnumarr[0]."(see ".$getdata_general['companyname'].")";
			$mainword_stemed = $Stemmer -> stem($getdata_general['companyname']);
			$synname_stemed = $Stemmer -> stem($newsynnum);
			$newsynnum = addslashes($newsynnum);
          	$syn_update = " mainword ='".addslashes($getdata_general['companyname'])."', synname='".addslashes($newsynnum)."', mainword_stemed='".addslashes($mainword_stemed)."', synname_stemed='".addslashes($synname_stemed)."', parentid='".$parentid."' ";
        }
		if($insyn['paid'] != $getdata_general['paid'])
		{
			$syn_update .= (trim($syn_update)!='' ? ", " : "") . " paid='" . $getdata_general['paid'] . "'";
		}
		if($syn_update!="")
		{
			$syn_update = "UPDATE tbl_compsyn SET " . $syn_update . " WHERE synID = '".$insyn['synID']."'";
			$result_syn_update = $conn_local->query_sql ($syn_update);
		}		
	}


	if($getdata_extra['freeze']==0 && $getdata_extra['mask']==0 && $getdata_extra['hidden']==0) 
	{
		CompanyAutosuggestGenerationInsertion($parentid,1);
	}
}

function CompanyAutosuggestGeneration($parentid, $dbarr)
{
	logmsgautosuggest(" CompanyAutosuggestGeneration called for parentid='".$parentid."'",$parentid);
	if(trim($parentid)=='')
	{
		die('Invalid parentid for auto suggest regenerate');
		exit;
	}	
	if(!is_array($dbarr))
	{
		die('Expected parameter is invalid for db connection');
		exit;		
	}	
	
	
	global $contact, $popular_cat, $getdata_general,$getdata_extra, $conn_auto, $conn_local,$conn_iro,$getdata_autosuggest,$genio_variables;
	
	$conn_iro 	= new DB($dbarr['DB_IRO']);
	$conn_local = new DB($dbarr['LOCAL']);
	$conn_auto  = new DB($dbarr['DB_AUTOSUGGEST']);
	$conn_fin   = new DB($dbarr['FINANCE']);
	$finance_obj = new company_master_finance($dbarr, $parentid,0);
	$getdata_extra = array();
	$getdata_general= array();

    if(!isset($compmaster_obj)){
		$compmaster_obj = new companyMasterClass($conn_iro,"",$parentid);
	}
	$sqlpaid = "SELECT sum(balance)  as total FROM tbl_companymaster_finance WHERE parentid = '".$parentid."' ";
	$respaid = $conn_fin->query_sql($sqlpaid); 
	
	$paidstatus = 0;
	if ($respaid and mysql_num_rows($respaid)>0)
	{
		$arypaid = mysql_fetch_assoc($respaid);
	
		if($arypaid['total']>1)
		$paidstatus=1;
	}	
	
	//$updateautosuggestflag="UPDATE tbl_autosuggest_company SET display_flag=0 WHERE parentid ='".$parentid."'";
	$updateautosuggestflag="DELETE FROM tbl_autosuggest_company WHERE parentid ='".$parentid."'";
	$conn_auto->query_sql($updateautosuggestflag, $parentid, true);

	$fieldstr	= '';
	$temparr	= array();
	$fieldstr	= "freeze,mask,original_creator,catidlineage,data_city,closedown_flag";
	$tablename	= "tbl_companymaster_extradetails";
	$wherecond	= "parentid='".trim($parentid)."'";
	$temparr	= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

	if($temparr['numrows']>0)
	{

		$getdata_extra = $temparr['data']['0'];

		$fieldstr	= '';
		$temparr	= array();
		$fieldstr	= "companyname,area,street,landmark,paid,landline,mobile, landline_display, mobile_display, tollfree_display, displayType, company_callcnt_rolling,data_city,city,hide_address,othercity_number";
		$tablename	= "tbl_companymaster_generalinfo";
		$wherecond	= "parentid='".$parentid."'";
		$temparr	= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		if ($temparr['numrows']>0)
		{
			$getdata_general=$temparr['data']['0'];
			$getdata_general['paid']=$paidstatus;
		}
		//echo "<pre>110"; print_r($getdata_general);
 
		$str = $getdata_general['landline_display'].",".$getdata_general['mobile_display'].",".$getdata_general['tollfree_display']; 
		
		if($getdata_general['othercity_number']!='')
		{
			$new_str = '';
			$othr_city_arr = explode(",",$getdata_general['othercity_number']);
			$othr_city_arr = array_merge(array_filter($othr_city_arr));
			foreach($othr_city_arr as $key => $values)
			{
				$explo_arr = explode("##",$values);print_r($explo_arr);
				$new_str .= $explo_arr[2].",";
			}
			$othrcity_str = trim($new_str,",");
			if($othrcity_str!=''){
				$str .= ",".$othrcity_str;
			}
		}

		if(trim($str)!='')
		{
			$contact_array = explode(",",trim(trim($str),","));
			$contact_array = array_filter($contact_array);
			$contact_array = array_merge($contact_array);
			foreach($contact_array as $string)
			{
				$number = preg_replace("[^ 0-9 ]", '', $string);
				$exp_contact_arr=explode(" ",trim($number,' '));
				$exp_contact_arr=array_filter($exp_contact_arr);
				$exp_contact_arr=array_merge($exp_contact_arr);    
				foreach($exp_contact_arr as $num)
				{
					if(strlen(trim($num))>=1) // previously it was >=6 but we got a number 139 which is valid so removing restriction
					{
					$new_contact_arr[]=trim($num);
					break;
					}
				}
			}
		}

		if(!is_null($new_contact_arr))
		{
			$contact=1;
		}
		else
		{
			$contact=0;
		}
		

		$catid		  = preg_replace("/[^ 0-9 ]/", ' ', $getdata_extra['catidlineage']);
		$catidArray	  = explode(' ',$catid);
		$catidArray	  = array_merge(array_unique(array_filter($catidArray)));
		
		$popular_cat=""; // by default it is blank
		
		if(count($catidArray)>0)
		{
			$catids= implode(",",$catidArray);
			$insert_pop_cat	= "SELECT category_name FROM tbl_categorymaster_generalinfo WHERE catid IN(".$catids.") Order BY callcount DESC LIMIT 1";
			$res_pop_cat	= $conn_local->query_sql ($insert_pop_cat);

			if(mysql_num_rows($res_pop_cat)>0)
			{
				$row_pop = mysql_fetch_assoc($res_pop_cat);
				$popular_cat = $row_pop['category_name'];		
			}		
		}
		
		if($getdata_extra['closedown_flag']==1) // if it is a closed down company it will make contract and popular category filed blank
		{
			$contact=0;
			$popular_cat="";
		}		
				
		
		$hiddenflag = 0;
		
		$FinanceDataArr = $finance_obj->getFinanceMainData(17);
				
		if($FinanceDataArr[17][budget]>0)
		{
			$hiddenflag = 1;		
		}
		
		
		$getdata_extra['hidden']=$hiddenflag;
		$hideaddress = $getdata_general['hide_address'];
		if($hideaddress==1) 
		{
			$getdata_general['area']="";
			$getdata_general['landmark']="";
			$getdata_general['street']="";
		}
		
				
		if($getdata_extra['freeze']==0 && $getdata_extra['mask']==0 && $getdata_extra['hidden']==0) 
		{
			logmsgautosuggest("freeze==0 mask==0 hidden==0 parentid='".$parentid."'",$parentid);
			CompanyAutosuggestGenerationInsertion($parentid,0);	
			
		}
		else
		{		
			$autosuggest="UPDATE tbl_autosuggest_company SET display_flag=0 WHERE parentid ='".$parentid."'";
			$conn_auto->query_sql($autosuggest, $parentid, true);			
			logmsgautosuggest(" freeze/Mask/Hidden Contract . Freeze=".$getdata_extra[freeze]." mask=".$getdata_extra[mask]." hidden=".$getdata_extra[hidden]." for parentid='".$parentid."'",$parentid,$autosuggest);
		}		
		// inserting sunonym in autosuggest 
		synonymAutosuggest($parentid);	
	}
	else
	{
		$autosuggest="UPDATE tbl_autosuggest_company SET display_flag=0 WHERE parentid ='".$parentid."'";
		$conn_auto->query_sql($autosuggest, $parentid, true);		
		logmsgautosuggest("Entry not found in tbl_companymaster_extradetail so making display_flag=0 in tbl_autosuggest_company ",$parentid,$autosuggest);
		
	}
	
	if(!defined('REMOTE_CITY_MODULE')) // this is only for main city
	{
		UpdateTblAutosuggestCompanyWhatwhere($parentid,$compmaster_obj,0);
	}
	
	//echo "<br> Main function exit"; exit;
}

function CompanyAutosuggestGenerationInsertion($parentid,$synflag=0)
{
	global $contact, $popular_cat, $getdata_general,$getdata_extra, $conn_auto, $conn_local,$getdata_autosuggest,$dbarr;
	$getdata_autosuggest= array();
		
	if($getdata_extra['freeze']==0 && $getdata_extra['mask']==0 && $getdata_extra['hidden']==0) 
	{
		
		/* START : To get details before updation of autosuggest */
		$autosuggest_old="SELECT compname, compname_area, area  FROM tbl_autosuggest_company  WHERE parentid ='".$parentid."' and display_flag=1 and syn_flag=0 ";
		$getdata_autosuggest_old_res = $conn_auto->query_sql($autosuggest_old);
		
		if($getdata_autosuggest_old_res && mysql_num_rows($getdata_autosuggest_old_res) > 0)
		{
			$getdata_autosuggest_old = mysql_fetch_assoc($getdata_autosuggest_old_res); 
		}
		$compname_changes = 0;
		if (($getdata_autosuggest_old['compname'] != $getdata_general['companyname']) || ($getdata_autosuggest_old['area'] != $getdata_general['area']))
		{
			$compname_changes = 1;
		}
		if($compname_changes)
		{
			$autosuggest="UPDATE tbl_autosuggest_company SET display_flag = 0 WHERE parentid ='".$parentid."'";
			$conn_auto->query_sql($autosuggest, $parentid, true);
		}
		
		if($synflag==1)
		{		
			$sql_insyn="select synID,mainword,synname,mainword_stemed,synname_stemed,paid from tbl_compsyn where parentid='".$parentid."'"; 
			$sql_insyn1 = $conn_local->query_sql ($sql_insyn);
			while($insyn=mysql_fetch_assoc($sql_insyn1))
			{
				$mnword = $insyn['mainword'];
				$synnme = $insyn['synname'];
				$pd     = $insyn['paid'];
				
				$whtwhr_array = getWhatWhereFields($synnme,$parentid,$getdata_general['city'],$getdata_general['area']);
				
				$sql_upsyn="INSERT INTO tbl_autosuggest_company SET
							parentid = '".$parentid."',
							compname='".addslashes($synnme)."',
							area='".addslashes($getdata_general['area'])."',
							street='".addslashes($getdata_general['street'])."',
							display_flag = '1',
							compname_area = replace(replace(ucase('".$synnme."'),convert('(SEE' using utf8),concat(ucase('".addslashes($getdata_general['area'])."'),if(area!='',' ',''),convert('(SEE' using utf8))),'  ',' '),
							callcnt ='".$getdata_general['company_callcnt_rolling']."',
							paidstatus = '".$getdata_general['paid']."',
							data_city  = '".$getdata_general['data_city']."',
							syn_flag = '1',	
							company_search_without_space='".addslashes(str_replace(' ','',$getdata_general['companyname'].$getdata_general['area']))."',
							contact_flag = '".$contact."',
							popularCategory = '".$popular_cat."',
							landmark = '".addslashes($getdata_general['landmark'])."',
							compname_search='".$whtwhr_array['compname_search']."',
							compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
							compname_area_search='".$whtwhr_array['compname_area_search']."',
							compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
							compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."'
							
							ON DUPLICATE KEY UPDATE
																					
							compname='".addslashes($synnme)."',
							area='".addslashes($getdata_general['area'])."',
							street='".addslashes($getdata_general['street'])."',
							display_flag = '1',
							compname_area = replace(replace(ucase('".$synnme."'),convert('(SEE' using utf8),concat(ucase('".addslashes($getdata_general['area'])."'),if(area!='',' ',''),convert('(SEE' using utf8))),'  ',' '),
							callcnt ='".$getdata_general['company_callcnt_rolling']."',
							paidstatus = '".$getdata_general['paid']."',
							data_city  = '".$getdata_general['data_city']."',
							syn_flag = '1',	
							company_search_without_space='".addslashes(str_replace(' ','',$getdata_general['companyname'].$getdata_general['area']))."',
							contact_flag = '".$contact."',
							popularCategory = '".$popular_cat."',
							landmark = '".addslashes($getdata_general['landmark'])."',
							compname_search='".$whtwhr_array['compname_search']."',
							compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
							compname_area_search='".$whtwhr_array['compname_area_search']."',
							compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
							compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."'";	
				try{
					logmsgautosuggest(" Inserting synonames entry in table for parentid='".$parentid."'",$parentid,$sql_upsyn);	
				$sql_upsyn1= $conn_auto->query_sql($sql_upsyn, $parentid, true);
				}
				catch( Exception $e)
				{
					return;
				}
				if($printSQL) debug_helper($sql_upsyn,$sql_upsyn1,$conn);
			}		
		}
		else
		{			
			$whtwhr_array = getWhatWhereFields($getdata_general['companyname'],$parentid,$getdata_general['city'],$getdata_general['area']);
			
			$sql_include2 = "INSERT INTO tbl_autosuggest_company SET
							parentid = '".$parentid."',
							compname='".addslashes($getdata_general['companyname'])."',
							area='".addslashes($getdata_general['area'])."',
							street='".addslashes($getdata_general['street'])."',
							display_flag = '1',
							compname_area = '".addslashes($getdata_general['companyname']." ".$getdata_general['area'])."',
							callcnt ='".$getdata_general['company_callcnt_rolling']."',
							paidstatus = '".$getdata_general['paid']."',
							data_city  = '".$getdata_general['data_city']."',
							syn_flag = '0',	
							company_search_without_space='".addslashes(str_replace(' ','',$getdata_general['companyname'].$getdata_general['area']))."',
							contact_flag = '".$contact."',
							popularCategory = '".$popular_cat."',
							landmark = '".addslashes($getdata_general['landmark'])."',
							compname_search='".$whtwhr_array['compname_search']."',
							compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
							compname_area_search='".$whtwhr_array['compname_area_search']."',
							compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
							compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."'	
							
							ON DUPLICATE KEY UPDATE
							
							compname='".addslashes($getdata_general['companyname'])."',
							area='".addslashes($getdata_general['area'])."',
							street='".addslashes($getdata_general['street'])."',
							display_flag = '1',
							compname_area = '".addslashes($getdata_general['companyname']." ".$getdata_general['area'])."',
							callcnt ='".$getdata_general['company_callcnt_rolling']."',
							paidstatus = '".$getdata_general['paid']."',
							data_city  = '".$getdata_general['data_city']."',
							syn_flag = '0',	
							company_search_without_space='".addslashes(str_replace(' ','',$getdata_general['companyname'].$getdata_general['area']))."',
							contact_flag = '".$contact."',
							popularCategory = '".$popular_cat."',
							landmark = '".addslashes($getdata_general['landmark'])."',
							compname_search='".$whtwhr_array['compname_search']."',
							compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
							compname_area_search='".$whtwhr_array['compname_area_search']."',
							compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
							compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."'";	

			$inserintoautosuggest = $conn_auto -> query_sql($sql_include2, $parentid, true);  
			logmsgautosuggest(" Inserting tbl_autosuggest_company entry in table for parentid='".$parentid."'",$parentid,$sql_include2);	
		}
	}
	else
	{
		$autosuggest="UPDATE tbl_autosuggest_company SET display_flag=0 WHERE parentid ='".$parentid."'";
		$conn_auto->query_sql($autosuggest, $parentid, true);
	}
}

function get_compname_area_merged($compname, $areaname, $remove_space = false, $filter_word = false, $east_west = false, $ignore_first_word = false)
{
	$compname = trim($compname);
	$areaname = trim($areaname);		
	if($ignore_first_word !== false && is_array($ignore_first_word))
	{
		$compname_split = explode(' ', $compname);
		if(in_array(strtolower($compname_split[0]), $ignore_first_word))
		{
			array_shift($compname_split);
		}
		$compname = trim(implode(' ', $compname_split));
	}
	
	$result = '';
	if(word_match($compname, $areaname) OR word_match($compname, word_replace($areaname, 'West', 'W')) OR word_match($compname, word_replace($areaname, 'East', 'E')))
	{
		$result = $compname;
	}
	else if($east_west === true)
	{
		$result = $compname;
		if($areaname != '')
		{
			if(ends_with($areaname, 'West') || ends_with($areaname, 'East'))
			{
				$result .= ' (' . trim(substr($areaname, 0, strlen($areaname) - 4)) . ')';
			}
			else
			{
				$result .= ' (' . $areaname . ')';
			}
		}
	}
	else
	{
		$result = $compname;
		if($areaname != '')
		{
			$result .= ' (' . $areaname . ')';
		}
	}
	
	if($filter_word !== false)
	{
		if(!is_array($filter_word))
			$filter_word = explode(', ', $filter_word);
		foreach($filter_word as $fw)
		{
			$result = word_replace($result, $fw, '');
		}
	}
	
	if($remove_space === true)
	{
		$result = preg_replace('/\s*/m', '', $result);
	}
	return trim($result);
}

function ends_with($haystack, $needle)
{
	$haystack = strtolower($haystack);
	$needle = strtolower($needle);
	return  substr($haystack, strlen($haystack) - strlen($needle)) == $needle;
}

function word_match($haystack, $pattern)
{
	$pattern = str_replace('/', '\/', $pattern);
	$pattern = preg_quote($pattern);
	return @preg_match('/\b' . $pattern . '\b/i', $haystack) > 0;
}

function word_replace($haystack, $pattern, $replace)
{
	$pattern = str_replace('/', '\/', $pattern);
	$pattern = preg_quote($pattern);
	return trim(preg_replace('/\b' . $pattern . '\b/i', $replace, $haystack));
}

function get_input_cleaned($input)
{
	$input = preg_replace("/\\$|,|@|#|~|`|\%|\*|\^|\&|\(|\)|\+|\=|\[|\-|\_|\]|\[|\}|\{|\;|\:|\"|\<|\>|\?|\||\\\|\\!|\/|\./", ' ', $input);
	$input = preg_replace('/\s\s+/', ' ', $input);
	$input = str_ireplace('\'', '', $input);
	$input = preg_replace('/\s\s+/', ' ', $input);
	return trim($input);
}

function getSTDCode_WW()
{
	global $conn_local;
	$ippartarray= explode(".", $_SERVER[SERVER_ADDR]);
	$cityname="";
	$STDCode="";
	switch($ippartarray[2])
	{
		case '0':
		$cityname ="MUMBAI";
		break;
		case '8':
		$cityname="DELHI";
		break;
		case '16':
		$cityname="KOLKATA";
		break;
		case '26':
		$cityname="BANGALORE";
		break;
		case '32':
		$cityname="CHENNAI";
		break;
		case '40':
		$cityname="PUNE";
		break;
		case '50':
		$cityname="HYDERABAD";
		break;
		case '56':
		$cityname="AHMEDABAD";
		break;
		case '64':
		$cityname="MUMBAI";				
	}
	
	$STDcodesql = "select stdcode from city_master WHERE ct_name='".$cityname."' limit 1";
	$STDcoderes = $conn_local->query_sql($STDcodesql);
	
	if($STDcoderes and mysql_num_rows($STDcoderes)>0)
	{
		$STDcodearr = mysql_fetch_assoc($STDcoderes);
		$STDCode = $STDcodearr[stdcode];
		return $STDCode;
	}
	else
	{
		return false;
	}
	
	
}
	
function getWhatWhereFields($compname,$parentid,$city,$area)
{
	//echo "<br>getWhatWhereFields 1-".$compname."  2--".$parentid."  3--".$city."  3--".$area;
	$company_filter_word = array('the', 'dr', 'dr.', 'prof', 'prof.','pvt', 'ltd','pvt.', 'ltd.','private','limited');
	$filter_word = array('pvt', 'ltd');
	$STDCode = getSTDCode_WW();
	$contractid	= trim($STDCode.$parentid); 
	$compname	= trim($compname);

	$pattern = "/(\(.*\))/";
	preg_match($pattern, $compname, $matches);
	$compname = str_replace($matches[0], "", $compname);
	$compname = trim($compname);

	$city	  = trim($city);					
	$areaname = trim($area);
	
	$compname_filtered = preg_replace('#\(.*\)#', '', $compname);
	$compname_filtered = trim($compname_filtered);

	$compname_area = get_compname_area_merged($compname, $areaname);

	$compname_search = get_input_cleaned(get_compname_area_merged($compname_filtered, ''));
	// removed ignore words
	$compname_search_ignore = get_input_cleaned(get_compname_area_merged($compname_filtered, '', false, $filter_word, false, $company_filter_word));

	$compname_area_search = get_input_cleaned(get_compname_area_merged($compname_filtered, $areaname, false, false, true));
	//removed ignore words
	$compname_area_search_ignore = get_input_cleaned(get_compname_area_merged($compname_filtered, $areaname, false, $filter_word, true, $company_filter_word));

	$compname_search_wo_space = preg_replace('/\s*/m', '', $compname_search);
	$compname_area_search_wo_space = get_input_cleaned(get_compname_area_merged($compname_filtered, $areaname, true));
	$compname_area_search_wo_space = preg_replace('/\s*/m', '', $compname_area_search_wo_space);				

	$word_count_ignore = str_word_count($compname_area_search_ignore, 0);
	$whrwhr_fields= array();
	$whrwhr_fields['compname_area']= $compname_area;
	$whrwhr_fields['compname_search']= $compname_search;
	$whrwhr_fields['compname_search_ignore']= $compname_search_ignore;
	$whrwhr_fields['compname_area_search']= $compname_area_search;
	$whrwhr_fields['compname_area_search_ignore']= $compname_area_search_ignore;
	$whrwhr_fields['compname_search_wo_space']= $compname_search_wo_space;
	$whrwhr_fields['compname_area_search_wo_space']= $compname_area_search_wo_space;
	$whrwhr_fields['word_count_ignore']= $word_count_ignore;
	$whrwhr_fields['contractid']= $contractid;
	
	return $whrwhr_fields;
}


function UpdateTblAutosuggestCompanyWhatwhere($parentid,$compmaster_obj,$synflag=0)
{
	global $contact, $popular_cat, $getdata_general,$getdata_extra,$conn_local,$conn_iro,$conn_auto,$getdata_autosuggest_old,$autosuggest_old_cnt;
	
	$currenttime = date("Y-m-d H:i:s");
	
	$STDCode = getSTDCode_WW();
	if($STDCode)
	{	
	if(checkContracatConditions($getdata_extra, $getdata_general) ) 
	{			
		$getdata_whtwhr_Qry = "SELECT contractid,compname,compname_area,areaname, display_flag FROM tbl_autosuggest_company_whatwhere where contractid = '".$STDCode.$parentid."' and syn_flag=0";
		$Res_whtwhr = $conn_iro->query_sql($getdata_whtwhr_Qry);
		
		$whtwhr_numrows = mysql_num_rows($Res_whtwhr);
		
		if($Res_whtwhr && mysql_num_rows($Res_whtwhr)>0) //contract found in ww
		{
			$row_whtwhr = mysql_fetch_assoc($Res_whtwhr);			
			
			if( (trim($row_whtwhr['compname'])!=trim($getdata_general['companyname'])) || (trim($row_whtwhr['areaname'])!=trim($getdata_general['area'])) )
			{ /*company name or area has been changed */

				$temparr		= array();
				$joinfiedsname	= "parentid,gi.companyname,area";
				$jointablesname	= "tbl_companymaster_generalinfo gi join tbl_companymaster_extradetails";
				$joincondon		= "using(parentid)";
				$wherecond		= "gi.companyname='".addslashes(stripslashes($row_whtwhr['compname']))."' and area ='".addslashes(stripslashes($row_whtwhr['areaname']))."' and freeze=0 and mask=0";
				$temparr	= $compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond);

				if($temparr['numrows']>0)//
				{
					/* other company with old companyname and old area found in GI so we replace contractid only in ww */
					$giarry = $temparr['data']['0'];
					$giparentid = $giarry['parentid'];

                    /*valibhav start*/
                    $whtwhr_Qry = "SELECT contractid,compname,compname_area,areaname  FROM tbl_autosuggest_company_whatwhere where compname = '".addslashes(stripslashes($getdata_general['companyname']))."'  and areaname='".addslashes(stripslashes($getdata_general['area']))."'  and syn_flag=0 and display_flag = 1 ";
                    $whtwhr_Res = $conn_iro->query_sql($whtwhr_Qry);
                    if($whtwhr_Res && mysql_num_rows($whtwhr_Res)>0 ){
                        //do nothing or can be update active flag.
                    }else{
                        //insert new compname and area.
                        $whtwhr_array_inner = getWhatWhereFields($getdata_general['companyname'],$parentid,$getdata_general['city'],$getdata_general['area']);				
					InsertIntoAutosuggestCompanyWhatwhere($conn_iro,$getdata_general,$whtwhr_array_inner,0);
                    }
                    /*vaibhav end*/
                    
				}
				else
				{
					/* no company found with old compname and old area so we have to delete that record and insert new record*/
					$updatewwquery = "DELETE from  tbl_autosuggest_company_whatwhere where contractid = '".$STDCode.$parentid."' and syn_flag=0";
					$updatewwres = $conn_iro->query_sql($updatewwquery);
										
					$whtwhr_array = getWhatWhereFields($getdata_general['companyname'],$parentid,$getdata_general['city'],$getdata_general['area']);				
					InsertIntoAutosuggestCompanyWhatwhere($conn_iro,$getdata_general,$whtwhr_array,0);
				}
				
				
			}
			else
			{
					if(intval($row_whtwhr['display_flag'])!=1)
					{
						/*company name or area has not changed  and it is present in ww so we check only display_flag*/					
						$updatewwquery = "UPDATE tbl_autosuggest_company_whatwhere set display_flag=1 where contractid = '".$STDCode.$parentid."'";
						$conn_iro->query_sql($updatewwquery);						
					}
			}
			
			
			
		}
		else  /* contract not found in ww by contractid */
		{	
			$getdata_whtwhr_Qry = "SELECT contractid,compname,compname_area,areaname,display_flag FROM tbl_autosuggest_company_whatwhere where compname = '".$getdata_general['companyname']."' AND  areaname ='".$getdata_general['area']."'";
			
			$Res_whtwhr = $conn_iro->query_sql($getdata_whtwhr_Qry);
						
			if($Res_whtwhr && mysql_num_rows($Res_whtwhr)>0) 
			{
				$Res_whtwhrarr= mysql_fetch_assoc($Res_whtwhr);				
				if(intval($Res_whtwhrarr['display_flag'])!=1)
				{
					/*entry found in ww for that companyname and are so set display_flag=1 only*/
					$updatewwquerysql = "UPDATE tbl_autosuggest_company_whatwhere set display_flag=1  where contractid = '".$Res_whtwhrarr['contractid']."'";
					$conn_iro->query_sql($updatewwquerysql);									
				}
			}
			else
			{
				$whtwhr_array = getWhatWhereFields($getdata_general['companyname'],$parentid,$getdata_general['city'],$getdata_general['area']);				
				InsertIntoAutosuggestCompanyWhatwhere($conn_iro,$getdata_general,$whtwhr_array,0);
			}
			
		}	
			
	}
	else /* company has been freezed or mask */
	{
		// in InsertIntoAutosuggestCompanyWhatwhere we are deleting first so called this function
		// first we check whether any other contrcat with same companyname & areaname if we found then we update contractid with that new contractid
		$temparr		= array();
		$joinfiedsname	= "parentid,gi.companyname,area";
		$jointablesname	= "tbl_companymaster_generalinfo gi join tbl_companymaster_extradetails";
		$joincondon		= "using(parentid)";
		$wherecond		= "gi.companyname='".addslashes(stripslashes($getdata_general['companyname']))."' and area ='".addslashes(stripslashes($getdata_general['area']))."' and freeze=0 and mask=0";
		$compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond);
		//$gires = $conn_iro->query_sql($giquery);

		if($temparr['numrows']>0)
		{
			//$giarry = mysql_fetch_assoc($gires);
			//$giparentid = $giarry['parentid'];

			$whtwhr_array = getWhatWhereFields($getdata_general['companyname'],$parentid,$getdata_general['city'],$getdata_general['area']);
			InsertIntoAutosuggestCompanyWhatwhere($conn_iro,$getdata_general,$whtwhr_array,0);
						

		}
		else
		{
			$updatewwquery = "UPDATE tbl_autosuggest_company_whatwhere set display_flag=0 where contractid = '".$STDCode.$parentid."'";
			$conn_iro->query_sql($updatewwquery);		
		}
		
		
		
	}
	
	
	}

} /* End of UpdateTblAutosuggestCompanyWhatwhere() function */

function checkContracatConditions($getdata_extra, $getdata_general){
    $landlineArr = array_filter(explode(',',$getdata_general['landline_display']));
    $mobileArr = array_filter(explode(',',$getdata_general['mobile_display']));
    $tollfreeArr = array_filter(explode(',',$getdata_general['tollfree_display']));
    $contnumflag = false;
    if(count($landlineArr)>0){
        foreach($landlineArr as $key=> $landline){
            if(strlen($landline)>=5 && strlen($landline)<=10){
                $contnumflag=true; 
                break;
            }
        }
    }else if($contnumflag!=true && count($mobileArr)>0){
        foreach($mobileArr as $key=> $mobileNo){
            if(strlen($mobileNo)==10){
                $contnumflag=true; 
                break;
            }
        }
    }else if($contnumflag!=true && count($tollfreeArr)>0){
        foreach($tollfreeArr as $key=> $tollfreeNo){
            if(strlen($tollfreeNo)>=8){
                $contnumflag=true; 
                break;
            }
        }
    }
    /*
    echo "<br>number_array";
    print_r($landlineArr);
    print_r($tollfreeArr);
    print_r($mobileArr);
    echo "<br>contnumflag--".$contnumflag;*/
    if($contnumflag == false)
    {		
		return false;    
	}
    $testingIDArr = array('iro0002','007247','div001','004650','ADMIN','TESTMEERUT','9930730010','022364353','cs00001','mkt00001');
    //$testingIDArr = array('iro0002','007247','div001','004650','TESTMEERUT','9930730010','022364353','cs00001','mkt00001');

    
    if($getdata_extra['freeze']==1 || $getdata_extra['mask']==1 || $getdata_extra['hidden']==1)
    {
		return false;	
	}
	

	
	if( strpos( strtoupper($getdata_general['companyname']), ' CLOSED ') !== false  ||  strpos( strtoupper($getdata_general['companyname']), '(ACCOR)') !== false ||  strpos( strtoupper($getdata_general['companyname']), '(SODEXO)') !== false || strpos( strtoupper($getdata_general['companyname']), '(SEE ') !== false )
	{
		return false;		
	}
	if($getdata_general['companyname'] ==''  || $getdata_general['companyname'] == null) 
	{
		return false;		
	}
	
    if(in_array($getdata_extra['original_creator'] ,$testingIDArr))
    {		
		return false;	
	}    
    
    if(strpos( strtoupper($getdata_general['displayType']), 'WEB') === false)
    {		
		return false;
	}
     
     return true; // passed all eligibility cretiria
         
}

function InsertIntoAutosuggestCompanyWhatwhere($conn_iro,$getdata_general,$whtwhr_array,$synflag)
{
$currenttime = date("Y-m-d H:i:s");

$callcount_perday= 0.00000;
$popularity= 0;
$multiple= 0;
$group_flag = 1;
$group_callcount = 0.0000;


$fieldtosave ="SELECT callcount_perday,popularity,multiple ,group_flag, group_callcount from tbl_autosuggest_company_whatwhere WHERE compname_area='".addslashes($whtwhr_array['compname_area'])."'";
$fieldtosavers = $conn_iro -> query_sql($fieldtosave);
if(mysql_num_rows($fieldtosavers)>0)
{
$fieldtosavearr = mysql_fetch_assoc($fieldtosavers);

$callcount_perday=$fieldtosavearr[callcount_perday];
$popularity = $fieldtosavearr[popularity];
$multiple = $fieldtosavearr[multiple];
$group_flag = $fieldtosavearr[group_flag];
$group_callcount = $fieldtosavearr[group_callcount];

}


$deletewwquery = "DELETE from tbl_autosuggest_company_whatwhere WHERE compname_area='".addslashes($whtwhr_array['compname_area'])."'";
$conn_iro -> query_sql($deletewwquery);

$insertwwquery = "INSERT INTO tbl_autosuggest_company_whatwhere SET
contractid='".$whtwhr_array['contractid']."',
compname='".addslashes($getdata_general['companyname'])."',
compname_area='".addslashes($whtwhr_array['compname_area'])."',
compname_search='".$whtwhr_array['compname_search']."',
compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
compname_area_search='".$whtwhr_array['compname_area_search']."',
compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
compname_search_wo_space='".$whtwhr_array['compname_search_wo_space']."',
compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."',
areaname='".addslashes($getdata_general['area'])."',
city='".addslashes($getdata_general['city'])."',
word_count_ignore='".$whtwhr_array['word_count_ignore']."',
display_flag=1,
updated_date='".$currenttime."',
syn_flag='".$synflag."',
callcount_perday=".$callcount_perday.",
popularity = ".$popularity.",
multiple=".$multiple.",
group_flag=".$group_flag.",
group_callcount=".$group_callcount."

ON DUPLICATE KEY UPDATE
compname='".addslashes($getdata_general['companyname'])."',
compname_area='".addslashes($whtwhr_array['compname_area'])."',
compname_search='".$whtwhr_array['compname_search']."',
compname_search_ignore='".$whtwhr_array['compname_search_ignore']."',
compname_area_search='".$whtwhr_array['compname_area_search']."',
compname_area_search_ignore='".$whtwhr_array['compname_area_search_ignore']."',
compname_search_wo_space='".$whtwhr_array['compname_search_wo_space']."',
compname_area_search_wo_space='".$whtwhr_array['compname_area_search_wo_space']."',
areaname='".addslashes($getdata_general['area'])."',
city='".addslashes($getdata_general['city'])."',
word_count_ignore='".$whtwhr_array['word_count_ignore']."',
display_flag=1,
updated_date='".$currenttime."',
syn_flag='".$synflag."',			
updated_date='".$currenttime."',
syn_flag='".$synflag."',
callcount_perday=".$callcount_perday.",
popularity = ".$popularity.",
multiple=".$multiple.",
group_flag=".$group_flag.",
group_callcount=".$group_callcount;

try{
$conn_iro -> query_sql($insertwwquery);
//echo $insertwwquery;
}
catch( Exception $e)
{
	return;
}

}

function logmsgautosuggest($sMsg,$contractid,$extra_str='')
{
	return ; // stoping making log
	$log_msg='';
	$log_path = APP_PATH.'logs/autosuggestlog/';
	$sNamePrefix= $log_path;
	// fetch directory for the file
	$pathToLog = dirname($sNamePrefix); 
	if (!file_exists($pathToLog)) {
		mkdir($pathToLog, 0755, true);
	}
	/*$file_n=$sNamePrefix.$contractid.".txt"; */
	$file_n=$sNamePrefix.$contractid.".html";
	// Set this to whatever location the log file should reside at.
	$logFile = fopen($file_n, 'a+');

		
	$userID= $_SESSION['ucode'];
	/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
	$pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
	$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
					<tr valign='top'>
						<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
						<td style='width:15%; border:1px solid #669966'>File name:".$pageName."</td>
						<td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
						<td style='width:30%; border:1px solid #669966'>Query: ".$extra_str."</td>
						<td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
						</tr>
				</table>";
	fwrite($logFile, $log_msg);
	fclose($logFile);
}
?>
