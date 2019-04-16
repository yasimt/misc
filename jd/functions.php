<?php
require_once("config.php");

function getSingular($str = '')
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

function checkCategory($compname, $conn_djds)
{		
	
	global $conn;
	$msg 	= '';
	if(!empty($compname))
	{
		$business_name 	= trim($compname);
		$business_name 	= preg_replace('/\s+/', ' ', $business_name); 
		$b1				= getSingular(strtolower($business_name));
		$msg			= '';
		
		if(!preg_match('/([a-zA-Z0-9])/', $b1))
		{
			$msg = INVALID;
		}
		else
		{
			/*$sql			= "SELECT category_name as catname FROM tbl_categorymaster_generalinfo WHERE category_name = '".addslashes(trim($business_name))."' OR category_name = '".addslashes($b1)."'";
			$res_catname 	= $conn->execQuery($sql, $conn_djds_slave);
			$num_rows		= mysql_num_rows($res_catname);
			if($res_catname && $num_rows > 0)
			{
				$msg = MATCHCAT;
			}*/
			$matched_flag	=	0;
			$sql	=	"SELECT category_name FROM tbl_categorymaster_generalinfo WHERE category_name = lower('".addslashes($business_name)."') or category_name like '".addslashes($business_name )."%' or category_name like '".addslashes($b1)."%' ";
		
			//$res_catname 	= $conn->execQuery($sql, $conn_djds_slave);
			$res_catname 	= $conn->execQuery($sql, $conn_djds);
			$num_rows		= mysql_num_rows($res_catname);
			if($res_catname  && $num_rows > 0)
			{
				while($bname_catname_arr =  mysql_fetch_assoc($res_catname))
				{
					$c1 = strtolower(getSingular(strtolower($bname_catname_arr['category_name'])));							
					
					if($b1 == $c1)
					{
						$matched_flag =	1;
						$msg = MATCHCAT;
						break;
					}
				}
			}
			else if($matched_flag == 0)
			{
				//$sql_cat	= "SELECT category_name as catname FROM d_jds.tbl_categorymaster_generalinfo WHERE replace(category_name,' ','') = '".addslashes(trim($business_name))."' OR replace(category_name,' ','') = '".addslashes($b1)."'";
				
				$catname_search_processed_ws  			= str_replace(' ','',$b1);
				
				/*$sql_cat ="SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name= '".addslashes(trim($business_name))."'
							OR REPLACE(category_name,' ','') = '".addslashes(trim($business_name))."'
							UNION
							SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name='".addslashes($b1)."'
							OR REPLACE(category_name,' ','') = '".addslashes($b1)."'";*/
				/*$sql_cat ="SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name= '".addslashes(trim($business_name))."'
							OR catname_search_processed_ws = '".addslashes(trim($catname_search_processed_ws))."'
							OR catname_search_processed = '".addslashes(trim($b1))."'";
				$res_cat 	= $conn->execQuery($sql_cat, $conn_djds); //$conn_djds_slave
				$num_rows	= mysql_num_rows($res_cat);*/
				
				$num_rows1 = 0;
				$num_rows2 = 0;
				$num_rows3 = 0;
				
				$sql_cat1 = "SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
							WHERE category_name= '".addslashes(trim($business_name))."'";
				$res_cat1 	= $conn->execQuery($sql_cat1, $conn_djds);
				$num_rows1	= mysql_num_rows($res_cat1);
				
				if($num_rows1 == 0)
				{
					$sql_cat2 = "SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
								WHERE catname_search_processed_ws = '".addslashes(trim($catname_search_processed_ws))."'";
					$res_cat2 	= $conn->execQuery($sql_cat2, $conn_djds);
					$num_rows2	= mysql_num_rows($res_cat2);
				}
				else if($num_rows2 == 0)
				{
					$sql_cat3 = "SELECT category_name AS catname FROM d_jds.tbl_categorymaster_generalinfo
								WHERE catname_search_processed = '".addslashes(trim($b1))."'";
					$res_cat3 	= $conn->execQuery($sql_cat3, $conn_djds);
					$num_rows3	= mysql_num_rows($res_cat3);
				}
				
				if($num_rows1 > 0 || $num_rows2 > 0 || $num_rows3 > 0)
				{
					$msg = MATCHCAT;
				}
			}
		}
	}
	return $msg;
}

function checkCatsynonym($compname, $conn_djds)
{
	global $conn;
	$msg 	= '';
	if(!empty($compname))
	{
		$business_name 		= trim($compname);
		$business_name 		= preg_replace('/\s+/', ' ', $business_name);
		$business_name_ws 	= str_replace(' ','',$business_name); 
		$sql_cat_syn	= "SELECT synonym_name FROM tbl_synonym WHERE synonym_name = lower('".addslashes($business_name)."') AND active_flag = 1
		 UNION SELECT synonym_name FROM tbl_synonym WHERE REPLACE(synonym_name, ' ','') = lower('".addslashes($business_name_ws)."') AND active_flag = 1";
		//$res_cat_syn	= $conn->execQuery($sql_cat_syn, $conn_djds_slave);
		$res_cat_syn	= $conn->execQuery($sql_cat_syn, $conn_djds);
		$num_rows		= mysql_num_rows($res_cat_syn);
		if($res_cat_syn && $num_rows > 0)
		{
			$msg = MATCHSYN;
		}
	}
	return $msg;
}

function checkBrandname($compname, $conn_iro)
{
	global $conn;
	$msg 	= '';
	if(!empty($compname))
	{
		$companystr 	= fn_stemming($compname);
		$sql_brand_name = "SELECT GROUP_CONCAT(brand_name separator '|~|') as brand_name, GROUP_CONCAT(source separator '|~|') as source FROM tbl_brand_names WHERE MATCH(brand_name) AGAINST('".$companystr."' IN BOOLEAN MODE) LIMIT 1";
		$res_brand	= $conn->execQuery($sql_brand_name, $conn_iro);
		$num_rows	= mysql_num_rows($res_brand);
		if($res_brand && $num_rows > 0)
		{
			$row 		= mysql_fetch_assoc($res_brand);
			$brand_name = trim($row['brand_name']);
			$brand_name = strtolower($brand_name);
			$source 	= trim($row['source']);
			$brand_name_arr = explode("|~|",$brand_name);
			$source_arr = explode("|~|",$source);
			$matched_brand = '';
			$matched_source = ''; 
			if(count($brand_name_arr)>0){
				foreach($brand_name_arr as $key => $value){
					if(strpos($companystr, $value) !== false) {
						$matched_brand = $value;
						$matched_source = $source_arr[$key];
						break;
					}
				}
			}
			if($matched_brand){
				$msg = MATCHBRAND;
			}
		}
	}
	return $msg;
}

function checkBlockednum($number, $conn_idc)
{
	global $conn;
	$msg	=	'';
	if(!empty($number))
	{
		$sql_block	= "SELECT reason FROM dnc.tbl_blockNumbers WHERE blocknumber ='".$number."' AND block_status = '1'";
		$res_block	= $conn->execQuery($sql_block, $conn_idc);
		$num_rows	= mysql_num_rows($res_block);
		if($res_block && $num_rows > 0)
		{
			$msg = "This number ".$number." cannot be added as it is Blocked For Entry";//BLOCKNUM;
		}
	}
	return $msg;
}

function isVirtualNumber($landline,$CITY,$conn_djds)
{//print_r($CITY);
	global $conn;
	if(intval($landline)>0)
	{
		$select_tbl_virtual_number_range = "SELECT * FROM tbl_virtual_number_range WHERE city = '". $CITY ."'  AND ". $landline ." BETWEEN start_number and end_number"; 	
		$tbl_virtual_number_range_query = $conn->execQuery($select_tbl_virtual_number_range,$conn_djds);
		$tbl_virtual_number_range_data  = mysql_num_rows($tbl_virtual_number_range_query);
		if($tbl_virtual_number_range_data )
		{
			return false;
		}
        return true;
    }
}

function checkVitualNumber($land_line,$cityName,$conn_djds)
{
    $total_arr		= array();
    $landline_arr	= array();
    $add_tele_arr	= array();
    $landline_arr	= explode("|",$land_line);
    $total_arr_wo 	= array_values(array_filter($landline_arr));
    $error			= 0;
    $str_display	= "";
    
    if(is_array($total_arr))
    {
        foreach($total_arr_wo as $number)
        {
            $dis_error = isVirtualNumber($number,strtoupper($cityName),$conn_djds);
            if(!$dis_error)
            {
                $str_display = "This landline number (".$number.") is not allowed since same number exist in our virtual number series, Please change number";
                $error = 1;
                break;
            }
        }
    }
    return $str_display;
}

function checkDuplicate($numbers, $compname = '', $pincode = '', $city, $source = '')
{
	global $conn;
	if(!empty($numbers) && !empty($city))
	{
		$json_parentids = json_decode(getData("http://".WEB_SERVICES_API."/web_services/PhoneSearch.php?phone_nos=$numbers&city=$city"),true);
		$str_docids 	= getDocids($json_parentids);	// get comma separated parentids
		
		if(!empty($str_docids))
		{
			$returned_content = getData("http://".WEB_SERVICES_API."/web_services/CompanyDetails.php?docid=$str_docids&json=1");
			if(!empty($returned_content))
			{
				/*$counter = 1;				
				html_maker($returned_content,$counter);
				*/
				//print_r($returned_content);
				$msg = 'Duplicate number found!';//$returned_content;
			}
		}
		/*if(!empty($pincode))
		{
			$pincode_where = " AND pincode = '".addslashes(stripslashes($pincode))."'";
		}
		$json_parentids = getData("http://192.168.1.121/web_services/PhoneSearch.php?phone_nos=$numbers&city=$city");
		
		if(!empty($json_parentids))
		{
			$msg = $json_parentids;
		}*/
		/*else  // need to check the logic
		{
			if($source != 'TME' && !empty($compname))
			{
				$sql = "SELECT a.compname,a.parentid,a.paidstatus,a.contact_details 
						FROM tbl_tmesearch a 
						WHERE 
						(MATCH(a.contact_details) AGAINST ('".$numbers."'  IN BOOLEAN MODE)) AND 
						a.compname = '".$compname."' AND
						freez != 1 AND
						mask != 1 ".$pincode_where."
						GROUP BY parentid
						ORDER BY paidstatus DESC";
			}
			elseif($source == 'TME' && !empty($compname)) //ME
			{
				$sql = "SELECT a.companyName as compname,a.parentid,a.paid as paidstatus,a.contact_details 
						FROM c2s_nonpaid a 
						WHERE 
						(MATCH(a.contact_details) AGAINST ('".$numbers."'  IN BOOLEAN MODE)) AND 
						companyName = '".$compname."' AND
						freez != 1 AND
						 mask != 1 ".$pincode_where."
						GROUP BY parentid";
			}
			$res		= $conn->execQuery($sql);
			$num_rows	= $conn->fetchNumRows($res);
			if($res && $num_rows > 0)
			{
				$msg = DUPNAME;
			}
		}*/
	}
	return $msg;
}

function getData($url)
{	
	$ch 		= curl_init();
	$timeout 	= 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function curlPostData($curlurl,$data_arr)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $curlurl);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_arr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$content  = curl_exec($ch);
	curl_close($ch);
	return $content;
}

function getDocids($json_parentids)
{
	if(is_array($json_parentids) && count($json_parentids) > 0)
	{
		foreach($json_parentids as $key => $arr_vals)
		{
			$arr_docid[] = $arr_vals['docid'];
		}
		$im_docid	= implode(",",$arr_docid);		
		return $im_docid;
	}
	else
	{
		return false;
	}
}

function checkBadWords($compname, $conn_iro,$data_city)
{
	/*global $conn;
	$arr_bad_words = array();
	$msg	= '';
	$flag_bad_word = 0;
	if(!empty($compname))
	{
		$sql_bad	=	"SELECT word1 FROM db_iro.CallerWord WHERE match(word1) AGAINST ('".addslashes($compname)."')";
		//$res_bad	=	$conn->execQuery($sql_bad, $conn_iro_slave);
		$res_bad	=	$conn->execQuery($sql_bad, $conn_iro);
		$num_rows	=	mysql_num_rows($res_bad);
		if($num_rows > 0)
		{
			while($row = mysql_fetch_assoc($res_bad))
			{
				$arr_bad_words[] = trim($row['word1']);
			}
			foreach($arr_bad_words as $key => $val)
			{
				$patt	= '/'.$val.'/i';
				if(preg_match($patt, trim($compname)))
				{
					$flag_bad_word	=	1;
					break;
				}
			}
			if($flag_bad_word == 1)
			{
				$msg = 'Companyname contains profain word';
			}
		}
		return $msg;
	}*/
	
	require_once('library/configclass.php');
	$configclassobj	=	new configclass();
	$urldetails		=	$configclassobj->get_url(urldecode($data_city));
	$badword_url	=	$urldetails['jdbox_service_url'].'location_api.php';
	
	$param_bw				   =   array();
	$param_bw['rquest']        =  'badword_check';
	$param_bw['companyname']   =  urlencode($compname);
	$param_bw['data_city']     =  urlencode($data_city);
	
	//echo $badword_url.'?'.http_build_query($param_bw);die;
	$response					=	curlPostData($badword_url,$param_bw);	
	return $response;
}
function checkNonUTFChar($compname){
	$flag=0;
	$msg='';
	if(preg_match('/[\x00-\x1F\x80-\xFF]/', $compname)){
		$flag=1;	
	}
	if($flag==1){
		$msg 		= "Companyname contains Non-UTF characters \r\nkindly re-enter companyname manually";
	}
	return $msg;
}
function checkBadWords_old($string)
{
	if(isset($string) && $string != '') 
	{
		$field_val 	= $string;

		$lines 		= file('profanity.txt');
		$all_words 	= array();
		$arr_all_words = array();
		$i			= 0;
		$err_msg 	= "Companyname contains profain word";

		foreach($lines as $line_num => $line)
		{
			$explode = explode(",",$line);
			foreach ($explode as $k => $v) 
			{
				$v1 = trim(strtolower($v));
				if(!empty($v1))
					$arr_all_words[$i] = $v1;
				$i++;
			}
		}

		$split_values = explode(" ", $field_val);
		$split_values_2 = explode("_", $field_val);
		$split_values_3 = explode(",", $field_val);
		$split_values_4 = explode(".", $field_val);
		$split_values_5 = explode("@", $field_val);
		
		foreach ($split_values as $kie => $val) 
		{
			$val = strtolower($val);
			
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_2 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);
		
		foreach ($split_values_3 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_4 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);

		foreach ($split_values_5 as $kie => $val) 
		{
			$val = strtolower($val);
			if(in_array($val,$arr_all_words))
			{
				$profanity_flag = 1;
			}
		}
		unset($kie,$val);
		
		if($profanity_flag == 1)
			return $err_msg;
	}
}

function html_maker($json)
{
	$arr_docid 			= array_keys($json);
	$i = 1;
	
	echo "<div style='font-family:arial;width:600px;overflow:auto;'>
			<center><b>The Following Contracts have similar information.</b> </center><br>
			<table style='border:solid 1px #6699FF;font-size:10px;'>
				<tr>
					<th style='background-color:#FFCC66;width:50px;' align='center'><font size=2> Sr. No </font></th>
					<th	style='background-color:#FFCC66;width:200px;' align='center'> <font size=2>Company Name </font></th>
					<th style='background-color:#FFCC66;width:200px;' align='center'><font size=2> Contractid </font></th>
					<th style='background-color:#FFCC66;width:200px;' align='center'><font size=2> Contact Person </font></th>
					<th style='background-color:#FFCC66;width:200px;' align='center'><font size=2> Address </font></th>
					<th style='background-color:#FFCC66;width:100px;' align='center'> <font size=2>Type </font></th>
				</tr>
				<tr>
					<td height='10px'></td>
				</tr>";
	
	echo "<tr>
			<th colspan='6' align='center' style='background-color:#99CCFF;'>
				<font size='2'>Matching Name & Numbers  entered in www.justdial.com</font>
			</th>
		</tr>
		<tr><td colspan='4'  height='10px'></td></tr>";

	foreach($arr_docid as $key => $val)
	{
		$compname		=	$json[$val]['companyname'];
		$parentid		=	$json[$val]['parentid'];
		$paidstatus		=	$json[$val]['paidstatus'];
		$contact_person	=	$json[$val]['contact_person'];
		$building_name	=	$json[$val]['building_name'];
		$street			=	$json[$val]['street'];
		$landmark		=	$json[$val]['landmark'];
		$area			=	$json[$val]['area'];
		
		//building_name+''+street+''+landmark+''+area;
		$full_address 	= $building_name.','.$street.','.$landmark.','.$area;
		if(substr($parentid,0,1) != 'P')
		{
			$parentid = "P".$parentid;
		}
		if($paidstatus != '0')
		{
			$status_type = "Paid";
		}
		elseif($paidstatus == '0')
		{
			$status_type = "Non-Paid";
		}					
	
		$link_page = "";
		
		echo "<tr><td align='center' class='tableTD'> ".$i." </td>";
			
		// if the contract is non paid contract then only.
		if($paidstatus=='0')
		{
			echo "<td class='tableTD'><a href=\"".$link_page."\">".$compname." </a></td>";
		}
		else
		{
			echo "<td class='tableTD'><font color='red'>".$compname."</font></td>";
		}
		
		echo "<td class='tableTD'> ".$parentid." </td>";
		echo "<td class='tableTD'>".$contact_person."</td>";
		echo "<td class='tableTD'>".$full_address."</td>";
		echo "<td class='tableTD'> ".$status_type." </td></tr>
					<tr>
						<td colspan='4' height='10px'></td>
					</tr>	
					<tr>
						<td colspan='4'  height='10px'></td>
					</tr>";
		$i++;
	}
	echo "</table></div>";
}

function checkPremimumCategory($catids, $conn_djds)
{
	global $conn;
	$msg	=	'';
	if(!empty($catids))
	{
		$arr_cat = explode(',',$catids);
		//print_r($arr_cat);
		$arr_cat	=	array_filter(array_values($arr_cat));
		
		$cat_str	=	implode("','",$arr_cat);
		
		$sql_pre_cat 	= "SELECT distinct(catid), category_name FROM d_jds.tbl_categorymaster_generalinfo where catid IN ('".$cat_str."') AND premium_flag = 1";
		//$res_pre_cat		=	$conn->execQuery($sql_pre_cat, $conn_djds_slave);
		$res_pre_cat		=	$conn->execQuery($sql_pre_cat, $conn_djds);
		
		if(mysql_num_rows($res_pre_cat) > 0)
		{
			$msg	=	'You have selected premium category, it may get rejected after moderation';
		}
	}
	echo $msg;
}

function getCityName($cityid, $conn_djds)
{
	global $conn;
	$sqlCity = "SELECT ct_name FROM city_master WHERE city_id = '".$cityid."' AND de_display=1 AND display_flag=1 LIMIT 1";
	$resCity = $conn->execQuery($sqlCity, $conn_djds);
	$row = mysql_fetch_assoc($resCity);
	return $row['ct_name'];
}

function getDatacity($city)
{
	$city		= strtolower($city);
	$datacity	= '';
	
	$arr_city['ahmedabad']	= array('ahmedabad','sanand');
	$arr_city['bangalore'] 	= array('bangalore');
	$arr_city['chennai'] 	= array('chennai');
	$arr_city['delhi'] 		= array('delhi', 'noida', 'greater noida', 'faridabad', 'ghaziabad', 'gurgaon');
	$arr_city['hyderabad'] 	= array('hyderabad', 'secunderabad');
	$arr_city['kolkata'] 	= array('howrah','hooghly','north 24 parganas','south 24 parganas');
	$arr_city['mumbai'] 	= array('mumbai', 'thane', 'navi mumbai', 'panvel', 'vashi', 'new panvel', 'raigad-maharashtra');
	$arr_city['pune'] 		= array('pune');
	$arr_city['chandigarh'] = array('chandigarh','mohali','zirakpur','panchkula');

	if(in_array($city,$arr_city))
	{
		$datacity = $city;
	}
	else
	{
		foreach($arr_city as $keycity => $subcity)
		{
			if(in_array($city,$subcity))
			{
				$data_city = $keycity;
				break;
			}
		}
	}
	
	if(!empty($data_city))
		return $data_city;
	else
		return $city;
}

function checkCompanyArea($compname, $city_name, $conn_djds)
{
	global $conn;
	$msg	=	'';
	$pos	=	'';
	if(!empty($compname))
	{
		$sql_area 		= "SELECT DISTINCT area FROM tbl_area_master WHERE city='".$city_name."' and display_flag = 1 and type_flag=1";
		$res_area		= $conn->execQuery($sql_area, $conn_djds);
		
		if(mysql_num_rows($res_area) > 0)
		{
			while($row = mysql_fetch_assoc($res_area))
			{
				$area[]	=	$row['area'];
			}
		}
		if(is_array($area) && !empty($area))
		{
			$cnt = count($area);
			
			for($i=0; $i<$cnt; $i++)
			{
				$pos1 = strripos(trim($compname), $area[$i]);
				$pos2 = strripos(trim($compname), str_replace('-', '', $area[$i]));
				$pos3 = strripos(trim($compname), str_replace('-', ' ', $area[$i]));
				
				if($pos1 !== false && $pos2 !== false && $pos3 !== false)
				{
					$msg = "Companyname contains areaname - " . $area[$i] . " is not allowed";
					return $msg;
				}
			}
		}
	}
	return $msg;
}

function checkCompanyCity($compname, $city_name, $conn_djds)
{
	global $conn;
	$msg	=	'';
	$pos	=	'';
	if(!empty($compname))
	{
		$compname	=	trim(strtolower($compname));
		$sql_ct 	= 	"SELECT ct_name FROM city_master WHERE de_display=1 and display_flag=1";
		$res_ct		= 	$conn->execQuery($sql_ct, $conn_djds);
		
		if(mysql_num_rows($res_ct) > 0)
		{
			while($row = mysql_fetch_assoc($res_ct))
			{
				$ct_name[]	=	$row['ct_name'];
			}
		}
		if(is_array($ct_name) && !empty($ct_name))
		{
			$cnt = count($ct_name);
			
			for($i=0; $i<$cnt; $i++)
			{				
				if($compname == strtolower($ct_name[$i]))
				{
					$msg = "Companyname contains city name - " . $ct_name[$i] . " is not allowed";
					return $msg;
				}
				else if($compname == strtolower(str_replace('-', '', $ct_name[$i])))
				{
					$msg = "Companyname contains city name - " . $ct_name[$i] . " is not allowed";
					return $msg;
				}
				else if($compname == strtolower(str_replace('-', ' ', $ct_name[$i])))
				{
					$msg = "Companyname contains city name - " . $ct_name[$i] . " is not allowed";
					return $msg;
				}
			}
		}
	}
	return $msg;
}

function fn_stemming($word)
{
	$string = strtolower($word); 
	$word = preg_replace("/[^A-Za-z0-9\s]/", " ", $string);
	return $word;
}

function get_cs_application_url($data_city)
{
	switch(strtoupper($data_city))
	{
		case 'MUMBAI' :
			$url = "http://".MUMBAI_CS_API."/";
		break;
		
		case 'DELHI' :
			$url = "http://".DELHI_CS_API."/";
		break;
		
		case 'KOLKATA' :
			$url = "http://".KOLKATA_CS_API."/";
		break;
		
		case 'BANGALORE' :
			$url = "http://".BANGALORE_CS_API."/";
		break;
		
		case 'CHENNAI' :
			$url = "http://".CHENNAI_CS_API."/";
		break;
		
		case 'PUNE' :
			$url = "http://".PUNE_CS_API."/";
		break;
		
		case 'HYDERABAD' :
			$url = "http://".HYDERABAD_CS_API."/";
		break;
		
		case 'AHMEDABAD' :
			$url = "http://".AHMEDABAD_CS_API."/";
		break;
		
		default: 
			$url = "http://".REMOTE_CITIES_CS_API."/";
	}
	return $url;
}
?>
