<?php
/*/////////////////////////////////////////////////////////////////
Organization		:	Just Dial Pvt. Ltd., Mumbai
Script Name		:	solution.php
Description		:	To make a file in which we put our solutions related to system
 				and it will be updated day by day so that once we solve a problem in one place we just use this file
 				no need to do repetitive work
Author			:	Pramesh Chandra Jha
Creation Date		:	Oct 30 2010

/////////////////////////////////////////////////////////////////*/

//This is the function which solve E- East and W- west problem
require_once(APP_PATH."library/categoryMaster.php");

function WEareasolution ($areaString)
{
$patternE = " E$";
$patternW = " W$";

	if (eregi($patternE,$areaString))
	{
		$areaString=str_ireplace(' E', ' East',$areaString);
	}

	if (eregi($patternW,$areaString))
	{
		$areaString=str_ireplace(' W', ' West',$areaString);
	}
return $areaString;
}
function movieDeletion()
{
    $day= date(D);  //Three char day
    $hour= date(G); // hour in 24 Hour format
    $minute= date(i); //minute

    $timesum= 60*$hour+$minute;  //7:30=450

    $deletemovie_flag=1;

    if (($timesum>450 ) && (strtolower(trim($day)) == "fri"))
    {
        $deletemovie_flag=0;
    }
    else
    {
        $deletemovie_flag=1;
    }

    return $deletemovie_flag;

}

function str_replace_mine($searchingstring,$replacingstring,$sentance,$minwordlength)
{

	$sentancenew="";
	$sentancearray=explode($searchingstring,$sentance);


	foreach($sentancearray as $valueee)
	{
		if(strlen($valueee) > $minwordlength)
		{
			if(strlen($sentancenew)==0)
			{
				$sentancenew= $valueee;
			}
			else
			{
				$sentancenew=$sentancenew.$replacingstring.$valueee;
			}
		}
		else
		{
			if(strlen($sentancenew)==0)
			{
				$sentancenew= $valueee;
			}
			else
			{
				$sentancenew=$sentancenew.$searchingstring.$valueee;
			}
		}
	}
	return $sentancenew;
}

function gerCatidlineageSearch($catids_list,$conn_local)
{
	//this function takes comma seperated catlist and return catid with their parent catid
	//global $conn_local;
	/* This PART is copied from setcontract data/insertdatapaid and verified from raj kumar yadav
	 * This Part gathers all parents of selected categories form below given table
	 * and the merges two arrays of existing categories(selected ones) and parents selected form below query
	 * creates a string to populate catidlineage_search in tbl_companymaster_extradetails -------[ROHIT KAUL]
	 */
	 //echo "<br>catids_list".$catids_list;
	$catids_list= trim($catids_list,",");
	$catidArrExisting= explode(",",$catids_list);
	$catidArrExisting= array_filter($catidArrExisting);
	$catLinSrch="";
	$row_catid_parent= array();
	global $dbarr;
	$categoryMasterobj = new categoryMaster($dbarr,APP_MODULE);

	//echo "<br>count". count($catidArrExisting);
	if(count($catidArrExisting)>0)
	{
		/*
		$selectFilter = "SELECT DISTINCT catid FROM tbl_catfilters where fcatid in(".$catids_list.")";
		$resultFilter = $conn_local->query_sql($selectFilter);
		while($rowCatids	 = mysql_fetch_assoc($resultFilter))
		{
			$row_catid_parent[] = $rowCatids[catid];
		}
		*/
		$row_catid_parent = $categoryMasterobj->getParentCategories($catids_list);
		if(count($row_catid_parent)>0)
		{
			$arrayCatLinSrch_inter  = array_merge($row_catid_parent,$catidArrExisting);
			$arrayCatLinSrch 		= array_unique($arrayCatLinSrch_inter);
			$arrayCatLinSrch 		= array_merge($arrayCatLinSrch);
		}
		else
		{
			$arrayCatLinSrch 		= $catidArrExisting;
		}

		/*--------Creating String simalar to catlineage with parent categories-------*/
		$catLinSrch  = implode('/,/',$arrayCatLinSrch);
		$catLinSrch = '/'.$catLinSrch.'/';
		/*--------------------------------END---------------------------------------*/
	}
	return $catLinSrch;
}

function isAllowedForDataEntry($cityname,$conn_local)
{

	$allow_flag=0;

	$sql_allow = "SELECT allow_data FROM city_master where ct_name ='".$cityname."'";
	$result_allow  = $conn_local -> query_sql($sql_allow);

	if ($result_allow and mysql_num_rows($result_allow)>0)
	{
		$result_allow_arr = mysql_fetch_assoc($result_allow);
		if($result_allow_arr[allow_data]>0)
		{
			$allow_flag=1;
		}
	}
	return $allow_flag;
}

function getSingular($str='')
{
$s = array();
$t = explode(' ',$str);
$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','watches'=>'watch');
$r = array('ss'=>false,'os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
foreach($t as $v){
	if(strlen($v)>=4){
		$f = false;
		foreach(array_keys($r) as $k){
			if(substr($v,(strlen($k)*-1))!=$k){
				continue;
			}
			else{
				$f = true;
				if(array_key_exists($v,$e))
					$s[] = $e[$v];
				else
					$s[] = substr($v,0,strlen($v)-strlen($k)).$r[$k];

				break;
			}
		}
		if(!$f){
			$s[] = $v;
		}
	}
	else{
		$s[] = $v;
	}
}
return implode(' ',$s);
}

function get_singular($word)
{
$rules = array('ss'=>false,'os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
foreach(array_keys($rules) as $key)
{
	if(substr($word,(strlen($key) * -1))!=$key)
	{
		continue;
	}
	if($rules[$key]===false)
	{
		return $word;
	}
	return substr($word,0,strlen($word)-strlen($key)).$rules[$key];
}
return $word;
}

function appendNonPaidMovieCat($extDetArr_catidlineage_nonpaid,$temp_catid_arr,$conn_local,$parentid)
{
	$res_arr= array();
	echo "<pre>";print_r($temp_catid_arr);
	$catidlineage_nonpaid_arr = array();
	$catidlineage_nonpaid_arr = explode("/,/", trim($extDetArr_catidlineage_nonpaid,"/"));
	$catidlineage_nonpaid_arr = array_filter($catidlineage_nonpaid_arr);
	
	$catidlineage_arr = array_filter($temp_catid_arr);
	$catid_nonpaid_final=array_diff($catidlineage_nonpaid_arr,$catidlineage_arr);
	
	if(count($catid_nonpaid_final)>0)
	{
		$sqlCheckPaidExist = "SELECT slogan_np,slogan FROM tbl_business_temp_data WHERE contractid = '".$parentid."' ";
		$resCheckPaidExist = $conn_local->query_sql($sqlCheckPaidExist);

		//echo "<pre>";print_r($conn_local);
		//echo $sqlCheckPaidExist;
		if($conn_local->numrows($resCheckPaidExist) > 0)
		{
			$row_slogan 	= $conn_local->fetchData($resCheckPaidExist);
			$sloganstr 		= trim($row_slogan['slogan']);
			$sloganstr_np	= trim($row_slogan['slogan_np']);
		}
		if($sloganstr !='')
		{
			$sloganstr_arr = array();
			$sloganstr_arr = explode("|$|", $sloganstr);
			$sloganstr_arr  = array_filter($sloganstr_arr);

			$slogan_paid_arr = array();
			foreach ($sloganstr_arr  as $key => $str)
			{
				$details_arr = array();
				$details_arr = explode("~~~", $str);
				$details_arr = array_filter($details_arr);
				$slogan_paid_arr[$details_arr[2]]['name'] = $details_arr[0];
				$slogan_paid_arr[$details_arr[2]]['timing'] = $details_arr[1];
				$slogan_paid_arr[$details_arr[2]]['catid'] = $details_arr[2];
			}
		}
		if($sloganstr_np !='')
		{
			$sloganstr_np_arr = array();
			$sloganstr_np_arr = explode("|$|", $sloganstr_np);
			$sloganstr_np_arr  = array_filter($sloganstr_np_arr);

			$slogan_nonpaid_arr = array();
			foreach ($sloganstr_np_arr  as $key => $str)
			{
				$details_arr = array();
				$details_arr = explode("~~~", $str);
				$details_arr = array_filter($details_arr);
				$slogan_nonpaid_arr[$details_arr[2]]['name'] = $details_arr[0];
				$slogan_nonpaid_arr[$details_arr[2]]['timing'] = $details_arr[1];
				$slogan_nonpaid_arr[$details_arr[2]]['catid'] = $details_arr[2];
			}
		}
		$slogan_nonpaid_catid_arr =array();
		$slogan_nonpaid_catid_arr = array_keys($slogan_nonpaid_arr);

		$slogan_paid_catid_arr =array();
		$slogan_paid_catid_arr = array_keys($slogan_paid_arr);

		$slogan_nonpaid_catid_array =array();
		$slogan_nonpaid_catid_array = array_diff($slogan_nonpaid_catid_arr,$slogan_paid_catid_arr);

		$sloganstr ='';
		$htmldump = '';

		//echo "<pre>";print_r($slogan_paid_arr);
		//echo "<pre>";print_r($slogan_nonpaid_catid_array);
		$count = 0;
		if(count($slogan_paid_arr)>0)
		{
			foreach ($slogan_paid_arr as $key => $value_arr)
			{
				$count++;
				$catid 				= $slogan_paid_arr[$key]['catid'];
				$movie_timing_val 	= $slogan_paid_arr[$key]['timing'];
				$catname 			= $slogan_paid_arr[$key]['name'];

				$sloganstr.= $catname."~~~".$movie_timing_val."~~~".$catid."|$|";

				if($count%2 == 1)
				{
					$htmldump .= "<tr><td width='15'></td><td width='326' class='fontA14'>".$catname;
					if($movie_timing_val)
						$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td>";
					else
						$htmldump .= "</td>";
				}
				else
				{
					$htmldump .= "<td width='30'></td><td width='326' class='fontA14'>".$catname;
					if($movie_timing_val)
						$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
					else
						$htmldump .= "</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
				}							
			}
		}
		if(count($slogan_nonpaid_catid_array)>0)
		{
			foreach ($slogan_nonpaid_catid_array as $key => $nonpaid_catid) 
			{
				$count++;
				$catid 				= $slogan_nonpaid_arr[$nonpaid_catid]['catid'];
				$movie_timing_val 	= $slogan_nonpaid_arr[$nonpaid_catid]['timing'];
				$catname 			= $slogan_nonpaid_arr[$nonpaid_catid]['name'];
				
				$sloganstr.= $catname."~~~".$movie_timing_val."~~~".$catid."|$|";
				
				if($count%2 == 1)
				{
					$htmldump .= "<tr><td width='15'></td><td width='326' class='fontA14'>".$catname;
					if($movie_timing_val)
						$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td>";
					else
						$htmldump .= "</td>";
				}
				else
				{
					$htmldump .= "<td width='30'></td><td width='326' class='fontA14'>".$catname;
					if($movie_timing_val)
						$htmldump .= "<br><FONT SIZE='2' COLOR='#0066FF'>[".$movie_timing_val."]</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
					else
						$htmldump .= "</td><td width='15'></td></tr><tr><td height='4' colspan=2></td></tr>";
				}	
			}
		}
		if($htmldump)
		{
			$htmldump = "<table cellspacing='0' cellpadding='0' border='1' width='100%' align='center' class='bgcolfee0'>".$htmldump."</table>";
		}
		if($sloganstr && substr($sloganstr,0,3)!='|$|')
		{
			$sloganstr="|$|".$sloganstr;
		}
		$res_arr['htmldump'] = $htmldump;
		$res_arr['sloganstr'] = $sloganstr;
		/*echo "<b>html</b><br>".$htmldump;
		echo "<b>str</b><br>".$sloganstr;die;	
*/
		$sqlUpdBusFacility	= "UPDATE bus_facility_dump SET sloganstr_np='',htmldump_np='' WHERE refno = '".$parentid."' ";
		$resUpdBusFacility = $conn_local->query_sql($sqlUpdBusFacility);
	}
	return $res_arr;
}

function updateMovieTimeLog($parentid,$sloganstr,$catidsarr,$conn_decs,$compmaster_obj)
{
	echo "sloganstr ".$sloganstr."<br/>";
	echo "<pre>";print_r($catidsarr);
	$logArr = array();
	$finalLogData = array();
	$temp_catids_arr = array();
	$new_logData_arr = array();
	$old_logData_arr = array();

	$final_log_values = array();
//echo "slogan ".$sloganstr;die;
	$slogan_arr = explode("|$|",$sloganstr);
	$slogan_arr = array_filter($slogan_arr);
	echo "<pre>f";print_r($slogan_arr);

// To get Original catids & Slogan From LIVE ---- starts here
	$original_arr = array();
	$extra_moives_catid_arr = array();

	//$sql_original = "SELECT sloganstr FROM bus_facility_dump WHERE refno='".$parentid."'";
	$sql_original = "SELECT REPLACE(CONCAT(IFNULL(sloganstr,''),IFNULL(sloganstr_np,'')),'|$||$|','|$|') AS sloganstr  FROM  bus_facility_dump WHERE refno='".$parentid."'";
	$res_original = $conn_decs -> query_sql($sql_original);
	if($res_original && mysql_num_rows($res_original)>0)
	{
		$row_original = mysql_fetch_assoc($res_original);
		$original_slogan_arr = explode("|$|",$row_original['sloganstr']);
		$original_slogan_arr = array_filter($original_slogan_arr);
	}

	$sql_old_cat = "SELECT IFNULL(catidlineage,'') AS catidlineage FROM db_iro.tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";

/*	else
	{
		$sql_old_cat = "SELECT CONCAT(IFNULL(catidlineage,''),',',IFNULL(catidlineage_nonpaid,'')) AS catidlineage FROM db_iro.tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";
	}*/
	$res_old_cat = $conn_decs->query_sql($sql_old_cat);
	if($res_old_cat && mysql_num_rows($res_old_cat)>0)
	{
		$row_old_cat = mysql_fetch_assoc($res_old_cat);
		$extra_catids = str_replace('/','',$row_old_cat['catidlineage']);
		$extra_catids_arr = explode(',',$extra_catids);
	}
// To get Original catids & Slogan From LIVE ---- Ends here

	if(COUNT($extra_catids_arr)>0)
	{
		$catidsarr = array_merge($extra_catids_arr,$catidsarr);
		$catidsarr = array_filter($catidsarr);
		$catidsarr = array_unique($catidsarr);
	}

	$catids = implode("','",$catidsarr);

// To find only movies related catis  -- Starts here
	$sql_qry = "SELECT DISTINCT(catid) as catid FROM tbl_categorymaster_generalinfo WHERE catid in ('".$catids."') AND (category_verticals & 8 = 8)";
	$res_qry  = $conn_decs->query_sql($sql_qry);
	$moives_catid_arr = array();
	if($res_qry && mysql_num_rows($res_qry)>0)
	{
		while($row_qry = mysql_fetch_assoc($res_qry))
		{
			foreach ($slogan_arr as $key=>$value)
			{
				if(strstr($value,$row_qry['catid']))
				{
					$moives_catid_arr[$row_qry['catid']] = $value;
				}
			}
			if(count($extra_catids_arr)>0)
			{
				foreach ($original_slogan_arr as $key=>$value)
				{
					if(strstr($value,$row_qry['catid']))
					{
						$extra_moives_catid_arr[$row_qry['catid']] = $value;
					}
				}
			}
			$movie_catids[] = $row_qry['catid'];
		}
	}
// To find only movies related catis  -- Ends here
	echo "moives_catid_arr<pre>";print_r($moives_catid_arr);
	echo "<pre>extra_moives_catid_arr";print_r($extra_moives_catid_arr);
	if(COUNT($moives_catid_arr)>0)
	{
		foreach($moives_catid_arr as $key => $value)
		{
			if(strcmp(trim($value),trim($extra_moives_catid_arr[$key])) != 0)
			{
				$new_logData_arr[$key] = $moives_catid_arr[$key];
				$old_logData_arr[$key] = $extra_moives_catid_arr[$key];
			}
		}
	}
	print "<pre>";print_r($new_logData_arr);
	foreach($new_logData_arr as $movie_key => $movie_value)
	{
		$new_temp = array();
		$new_temp = explode('~~~',$movie_value);
		$new_logArr[$new_temp[2]] = $new_temp;
	}
print "<pre>";print_r($new_logArr);//exit;
	foreach($old_logData_arr as $movie_key => $movie_value)
	{
		$old_temp = array();
		$old_temp = explode('~~~',$movie_value);
		$old_logArr[$old_temp[2]] = $old_temp;
	}

	if(COUNT($new_logArr)>0)
	{
		foreach($new_logArr as $log_key => $log_value)
		{
			if($log_value[1] !='' || $old_logArr[$log_key][1] !='')
			{
				$final_log_values[$log_key][catid] = $log_key;
				$final_log_values[$log_key][catname] = $log_value[0];
				$final_log_values[$log_key][oldvlaues] = $old_logArr[$log_key][1];
				$final_log_values[$log_key][newvlaues] = $log_value[1];
			}
		}
	}

	$insert_log_new_values = '';
	$city = '';
	if(defined("REMOTE_CITY_MODULE"))
	{
		$city = DATA_CITY;
	}
	else
	{
		$city = $_SESSION['s_deptCity'];
	}
	echo "<pre>";print_r($final_log_values);//die;
	if(COUNT($final_log_values)>0)
	{
		foreach($final_log_values as $log_key => $log_value)
		{
			// inserting new & old values
			if($insert_log_new_values == '')
			{
				$insert_log_new_values = "('".$parentid."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d h:i:s')."','".$_SESSION['ucode']."','".$city."','".$_SESSION['module']."')";
			}
			else
			{
				$insert_log_new_values .= ",('".$parentid."','".$log_value[catid]."','".$log_value[catname]."','".$log_value[oldvlaues]."','".$log_value[newvlaues]."','".date('Y-m-d h:i:s')."','".$_SESSION['ucode']."','".$city."','".$_SESSION['module']."')";
			}
		}
		$sql_log = "INSERT INTO tbl_movietimes_log (parentid,catid,catname,oldtimings,newtimings,updatedOn,updatedBy,city,dept) VALUES ".$insert_log_old_values.$insert_log_new_values;
		//echo $sql_log;die;
		$res_log = $conn_decs->query_sql($sql_log);
	}
/*
	print "<pre>";
	print $sql_log;
	print "<br>insert_log_new_values : ".$insert_log_new_values;
	print "<br>new_logArr : ";print_r($new_logArr);
	print "<br>old_logArr : ";print_r($old_logArr);
	print "<br>final_log_values : ";print_r($final_log_values);
	print "<pre>extra_catids_arr";print_r($extra_catids_arr);
	print "<pre>temp_catids_arr";print_r($temp_catids_arr);
	print "<pre>moives_catid_arr";print_r($moives_catid_arr);
	print "<pre>extra_moives_catid_arr";print_r($extra_moives_catid_arr);
	print "<pre>logArr";print_r($logArr);
	exit;
*/
}


?>
