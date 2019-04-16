<?php
function getSenderInfo($datacity)
{
	/*switch(strtolower(trim($datacity)))
	{
		case "mumbai"		: 	$senderEmailid = "yogitatandel@justdial.com";
								break;
		case "delhi"		: 	$senderEmailid = "delhiweb@justdial.com";
								break;
		case "kolkata" 		: 	$senderEmailid = "kolkataweb@justdial.com";
								break;
		case "bangalore" 	: 	$senderEmailid = "bangaloreweb@justdial.com";
								break;
		case "chennai" 		: 	$senderEmailid = "chennaiweb@justdial.com";
								break;
		case "pune" 		: 	$senderEmailid = "puneweb@justdial.com";
								break;
		case "hyderabad" 	: 	$senderEmailid = "hyderabadweb@justdial.com";
								break;
		case "ahmedabad" 	: 	$senderEmailid = "ahmedabadweb@justdial.com";
								break;
		case "jaipur" 		: 	$senderEmailid = "ankitskumar@justdial.com";
								break;
		case "chandigarh" 	: 	$senderEmailid = "delhiweb@justdial.com";
								break;
		case "coimbatore" 	: 	$senderEmailid = "chennaiweb@justdial.com";
								break;
		case ""				: 	$senderEmailid = "noreply@justdial.com";
								break;
	}*/
	$senderEmailid = "noreply@justdial.com";
	return $senderEmailid;
}

function getNewData($parentid,$dbCon,$tempval,$datacity,$compmaster_obj=null){
	if($parentid!=''){
		$callCompClass = false;
		/*if(intval($tempval)==1){
			$tableName = 'tbl_companymaster_generalinfo_shadow';
			
		}else{
			$tableName = 'tbl_companymaster_generalinfo';
		}*/
		switch(intval($tempval)){
			case 0 : 	$tableName = 'tbl_companymaster_generalinfo';
						$tablField = 'country,';
						$callCompClass = true;
						break;
			case 1 :	$tableName = 'tbl_companymaster_generalinfo_shadow';
						$tablField = 'country,';
						break;
			case 2 : 	$tableName = 'd_jds.tbl_data_correction_for_tme';
						$tablField = '';
						break;
			case 3 : 	$tableName = 'tbl_companymaster_generalinfo_shadow';
						$tablField = 'country,';
						break;
			default: 	$tableName = 'tbl_companymaster_generalinfo';
						$tablField = 'country,';
						$callCompClass = true;
						break;
			
		}
		if($callCompClass){
			if(!is_null($compmaster_obj)){
				$resgetdata = array();
				$fieldstr	= "parentid,".$tablField." companyname as company_name,contact_person,building_name,street,area,subarea,landmark,city,pincode,landline,mobile,mobile_display,mobile_feedback,fax,tollfree,email,email_display,email_feedback,website";
				$where 		= "parentid='".$parentid."' and data_city ='".$datacity."'";
				$resgetdata	= $compmaster_obj->getRow($fieldstr,$tableName,$where);
				if($resgetdata['numrows']>0){
					$rowgetdata = $resgetdata['data']['0'];
					$success = json_encode($rowgetdata);
				}else{
					$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
					$success = json_encode($error);
				}
			}else{
				$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
				$success = json_encode($successArr);
			}
		}else{
			$qrygetdata = "SELECT parentid,".$tablField." companyname as company_name,contact_person,building_name,street,area,subarea,landmark,city,pincode,landline,mobile,mobile_display,mobile_feedback,fax,tollfree,email,email_display,email_feedback,website FROM  ".$tableName." WHERE parentid='".$parentid."' and data_city ='".$datacity."'";
			$resgetdata = $dbCon->query_sql($qrygetdata);
			if($resgetdata && mysql_num_rows($resgetdata)>0){
				$rowgetdata = mysql_fetch_array($resgetdata,MYSQL_ASSOC);
				$success = json_encode($rowgetdata);
			}else{
				$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
				$success = json_encode($error);
			}
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getPaidStatus($parentid,$dbCon){
	$paid = false;
	$qryCheckPaid = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid ='".$parentid."'  and balance>0 and expired!=1 and campaignid not in (6,7,8,9,24) limit 1";
	$resCheckPaid = $dbCon->query_sql($qryCheckPaid);
	if($resCheckPaid && mysql_num_rows($resCheckPaid)>0){
		$paid = true;
	}
	return $paid;
}

function getOldData($parentid,$dbCon,$compmaster_obj){
	if($parentid!=''){
		$resGetNewData = array();
		$fiedsStr = "parentid,country,companyname as company_name,contact_person,building_name,street,area,subarea,landmark,city,pincode,landline,mobile,mobile_display,mobile_feedback,fax,tollfree,email,email_display,email_feedback,website";
		$whrcond = "parentid ='".$parentid."'";
		$resGetNewData = $compmaster_obj->getRow($fiedsStr,"tbl_companymaster_generalinfo",$whrcond);
		/*$qryGetNewData = "SELECT parentid,country,companyname as company_name,contact_person,building_name,street,area,subarea,landmark,city,pincode,landline,mobile,mobile_display,mobile_feedback,fax,tollfree,email,email_display,email_feedback,website FROM tbl_companymaster_generalinfo where parentid ='".$parentid."'";
		$resGetNewData = $dbCon->query_sql($qryGetNewData);*/
		if($resGetNewData['numrows']>0){
			$rowGetNewData = $resGetNewData['data']['0'];
			$success = json_encode($rowGetNewData);
		}else{
			$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
			$success = json_encode($error);
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getNewCatData($parentid,$dbCon,$tempval,$datacity,$compmaster_obj=null)
{
	if($parentid!=''){
		$catcallcomp = false;
		switch(intval($tempval)){
			case 0 :	$qryGetnewCatdata = "SELECT catidlineage,tag_catid FROM tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";
						$catcallcomp = true;
						break;
						
			case 1 :	$qryGetnewCatdata = "SELECT catIds FROM tbl_business_temp_data WHERE contractid ='".$parentid."'";
						break;
			
			case 2 :	$qryGetnewCatdata = "SELECT catidlineage,added_catids,removed_catids FROM d_jds.tbl_data_correction_for_tme WHERE parentid ='".$parentid."' and correct !='2' and paid!=0 and allocated=1";
						break;
						
			case 3 :	$qryGetnewCatdata = "SELECT catidlineage FROM tbl_companymaster_extradetails_shadow WHERE parentid ='".$parentid."' and data_city='".$datacity."'";
						break;
			
			default:	$qryGetnewCatdata = "SELECT catidlineage,tag_catid FROM tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";
						$catcallcomp = true;
						break;
		}
		
		/*if(intval($tempval)==1){
			$qryGetnewCatdata = "SELECT catIds FROM tbl_business_temp_data WHERE contractid ='".$parentid."'";
		}else{
			$qryGetnewCatdata = "SELECT catidlineage,tag_catid FROM tbl_companymaster_extradetails WHERE parentid ='".$parentid."'";
		}*/
		if($catcallcomp){
			if(!is_null($compmaster_obj)){
				$resGetnewCatdata = array();
				$fieldsStr = "catidlineage,tag_catid";
				$whrCnd = "parentid ='".$parentid."'";
				$resGetnewCatdata = $compmaster_obj->getRow($fieldsStr,"tbl_companymaster_extradetails",$whrCnd);
				if($resGetnewCatdata['numrows']>0){
					$rowGetnewCatdata = $resGetnewCatdata['data']['0'];
					$success = json_encode($rowGetnewCatdata);
				}else{
					$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
					$success = json_encode($error);
				}
			}else{
				$successArr = array('status' => "Failed", "msg" => "companymaster object not get.", "code"=>"0");
				$success = json_encode($successArr);
			}
		}else{
			$resGetnewCatdata = $dbCon->query_sql($qryGetnewCatdata);
			if($resGetnewCatdata && mysql_num_rows($resGetnewCatdata)>0){
			$rowGetnewCatdata = mysql_fetch_array($resGetnewCatdata,MYSQL_ASSOC);
			$success = json_encode($rowGetnewCatdata);
			}else{
				$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
				$success = json_encode($error);
			}
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getOldCatData($parentid,$dbCon,$compmaster_obj){
	if($parentid!=''){
		$resGetOldCatData = array();
		$fieldsstr = "catidlineage,tag_catid";
		$whrcond = "parentid = '".$parentid."'";
		$resGetOldCatData = $compmaster_obj->getRow($fieldsstr,"tbl_companymaster_extradetails",$whrcond);
		/*$qryGetOldCatData = "SELECT catidlineage,tag_catid FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
		$resGetOldCatData = $dbCon->query_sql($qryGetOldCatData);*/
		if($resGetOldCatData['numrows']>0){
			$rowGetOldCatData = $resGetOldCatData['data']['0'];
			$success = json_encode($rowGetOldCatData);
		}else{
			$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
			$success = json_encode($error);
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getOldNonpaidCatData($parentid,$dbCon,$compmaster_obj){
	if($parentid!=''){
		$resGetOldNonpaidCatdata = array();
		$fieldsStr = "catidlineage_nonpaid";
		$whercond = "parentid = '".$parentid."'";
		$resGetOldNonpaidCatdata = $compmaster_obj->getRow($fieldsStr,"tbl_companymaster_extradetails",$whercond);
		/*$qryGetOldNonpaidCatdata = "SELECT catidlineage_nonpaid FROM tbl_companymaster_extradetails WHERE parentid = '".$parentid."'";
		$resGetOldNonpaidCatdata = $dbCon->query_sql($qryGetOldNonpaidCatdata); */
		if($resGetOldNonpaidCatdata['numrows']>0){
			$rowGetOldNonpaidCatdata = $resGetOldNonpaidCatdata['data']['0'];
			$success = json_encode($rowGetOldNonpaidCatdata);
		}else{
			$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
			$success = json_encode($error);
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getNewNonpaidCatData($parentid,$dbCon,$tempval,$compmaster_obj=null){
	if($parentid!=''){
		$usecompObj = false;
		if(intval($tempval)==1){
			$nonCatTable  = "tbl_companymaster_extradetails_shadow";
		}else{
			$nonCatTable  = "tbl_companymaster_extradetails";
			if(!is_null($compmaster_obj)){
				$usecompObj = true;
			}
		}
		if($usecompObj){
			$resGetnewNonpaidCatdata = array();
			$fieldsStr = "catidlineage_nonpaid";
			$whercond = "parentid ='".$parentid."'";
			$resGetnewNonpaidCatdata = $compmaster_obj->getRow($fieldsStr,"tbl_companymaster_extradetails",$whercond);
			if($resGetnewNonpaidCatdata['numrows']>0){
				$rowGetnewNonpaidCatdata = $resGetnewNonpaidCatdata['data']['0'];
				$success = json_encode($rowGetnewNonpaidCatdata);
			}else{
				$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
				$success = json_encode($error);
			}
		}else{
			$qryGetnewNonpaidCatdata = "SELECT catidlineage_nonpaid FROM ".$nonCatTable." WHERE parentid ='".$parentid."'";
			$resGetnewNonpaidCatdata = $dbCon->query_sql($qryGetnewNonpaidCatdata);
			if($resGetnewNonpaidCatdata && mysql_num_rows($resGetnewNonpaidCatdata)>0){
				$rowGetnewNonpaidCatdata = mysql_fetch_array($resGetnewNonpaidCatdata,MYSQL_ASSOC);
				$success = json_encode($rowGetnewNonpaidCatdata);
			}else{
				$error = array('status' => "Failed", "msg" => "No record found", "code"=>"2");
				$success = json_encode($error);
			}
		}
	}else{
		$successArr = array('status' => "Failed", "msg" => "Parentid is blank.", "code"=>"0");
		$success = json_encode($successArr);
	}
	return $success;
}

function getSmsText($infoArr=array())
{
	/*$title_flag = false;
	$title_flag = getTitle($infoArr['contact_person']);
	if(intval($title_flag)==1)
	{
		$clientStrSms = "Dear ".$infoArr['contact_person'];
	}
	else
	{
		$clientStrSms = "Dear Mr./Madam ".$infoArr['contact_person'];
	}*/
	if ($infoArr['remotecityidentifier']==1) {
		$dcitySub = 'REM';
	}else{
		//$dcitySub = strtoupper(substr($infoArr['data_city'],0,3));
		$dcitySub = strtoupper($infoArr['data_city']);
	}
	//$smsUrl = "http://jsdl.in/".$infoArr['shortcode']."&ct=".$dcitySub; 
	//$smsUrl = "http://jsdl.in/".$infoArr['shortcode']; 
	$smsUrl = "http://jsdl.in/".$infoArr['shortcode']."-".$dcitySub; 
	
	/*$clientMainSmsBody = "Dear Customer, Changes done by you thru website edit listing option have been taken live on our phone service and same shall be visible on Justdial.com in 24-48 hours. Click here ".$smsUrl." to view the changes. - Thanks Justdial";*/

	if(trim($infoArr['module'])==3){
		$clientMainSmsBody = "Dear Customer, Requested Changes will be taken live on Justdial.com within 24-48 hours. Click here to view ".$smsUrl." -Thanks Justdial";
	}else{
		$clientMainSmsBody = "Dear Customer, Requested Changes will be taken live on Justdial.com within 24-48 hours. Click here to view ".$smsUrl." -Thanks Justdial";
	}
	return $clientMainSmsBody;
}

function GenerateShortcode($dbcon){
	$shortCode ='';
	$microTime = microtime(true);
	$microTime =  str_replace(".","",$microTime);
	$short_url_id = "select short_url($microTime);";
	$res_short_url_id= $dbcon->query_sql($short_url_id);
	$row_short_url_id	= mysql_fetch_array($res_short_url_id);
	$shortCodeArr = explode("-",$row_short_url_id[0]);
	$shortCode = "hi-".$shortCodeArr[1]; 
	return $shortCode;
}

function getEmailMsg($infoArr=array()){
	//echo "<hr><pre>";print_r($infoArr);die();
	$DifArr = array_diff_assoc($infoArr['old'], $infoArr['new']);
	//print_r($DifArr);
	//$tblCss= "style='border:1px solid black;'";
	$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				</head>
				<body>';
	if(trim($infoArr['module'])==3){
		$mainBody = "Dear Customer,                                             
				<p> Changes done by you in your contract thru website Edit listing option have been taken live on phone services.Same will go live on Justdial.com in 24-48 hours.</p>
				<p>Please refer the fields highlighted with color to view the changed details.</p>";
	}else{
	$mainBody = "Dear Customer,                                             
				<p> As per your request, we have done the changes in your contract \"".addslashes($infoArr['companyName']) ."-". $infoArr['parentid'] ."\".</p>
				<p>Please refer the fields highlighted with color to view the changed details.</p>";
	}
	$dataDiff = "<table width='1000' border='0' cellspacing='0' cellpadding='0' style='background:#eaebec;font-size:12px;font-family:arial;border:1px solid #B0B0B0;color:#666;'>
					<tr>
						<th width='164' style='background:#ededed;padding:18px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;text-align:center;'>Business Info</th>
						<th width='409' style='background:#ededed;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;text-align:center;'>Old details</th>
						<th width='425' style='background:#ededed;border-right:1px solid #efefef;border-bottom:1px solid #B0B0B0;text-align:center;'>New details</th>
					</tr>";
	foreach($infoArr['old'] as $key => $value){//echo "<br>".$key;
		$datachange = false;
		if(strtolower(trim($key))!='parentid' && $key!='country'){
			$dataName = ucwords(str_replace('_',' ',$key));
			if(array_key_exists($key,$DifArr)){
				if($key=='contact_person' && $infoArr['old']['contact_person']!='' && $infoArr['new']['contact_person']!=''){
					$rg="/(?:^|\W)(?:(?:Dr|Mr|Mrs|Ms|Sr|Jr)\.?|Miss|Phd|\+)(?:\W|$)/i";
					$oldContactP = trim(preg_replace($rg,",",preg_replace("/\([^)]+\)/","",trim($infoArr['old']['contact_person']))),",");
					$newContactP = trim(preg_replace($rg,",",preg_replace("/\([^)]+\)/","",trim($infoArr['new']['contact_person']))),",");
					$ConatactPOld = explode(",",$oldContactP);
					$ConatactPOld = array_merge(array_filter($ConatactPOld));
					$ConatactPnew = explode(",",$newContactP);
					$ConatactPnew = array_merge(array_filter($ConatactPnew));
					if(count($ConatactPOld)==count($ConatactPnew)){
						$oldContactP = preg_replace('/[\s]+/','',implode("",$ConatactPOld));
						$newContactP = preg_replace('/[\s]+/','',implode("",$ConatactPnew));
						if(strtolower(trim($oldContactP))!=strtolower(trim($newContactP))){
							$datachange = true;
						}
					}
				}elseif(in_array($key,array('company_name','building_name','street','area','subarea','landmark','city'))){
					if($infoArr['old'][$key]!='' && $infoArr['new'][$key]!=''){
						$oldCompanyNmArr = array();
						$newCompanyNmArr = array();
						$oldCompStr = '';
						$newCompStr = '';
						//$oldCompanyNmArr = explode(" ",preg_replace('/[.,]/', '', $infoArr['old'][$key]));
						$oldCompanyNmArr = explode(" ",preg_replace('/[^A-Za-z0-9]/', '', $infoArr['old'][$key]));
						$oldCompanyNmArr = array_merge(array_filter($oldCompanyNmArr));
						$newCompanyNmArr = explode(" ",preg_replace('/[^A-Za-z0-9]/', '', $infoArr['new'][$key]));
						$newCompanyNmArr = array_merge(array_filter($newCompanyNmArr));
						$oldCompStr = preg_replace('/[\s]+/','',implode($oldCompanyNmArr));
						$newCompStr = preg_replace('/[\s]+/','',implode($newCompanyNmArr));
						if(strtolower(trim($oldCompStr))!=strtolower(trim($newCompStr))){
							$datachange = true;
						}
					}
				}else{
					$datachange = true;
				}
			}else{
				$datachange = false;
			}
			if(array_key_exists($key,$DifArr) && $datachange == true){
				$dataDiff.= "<tr>
								<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$dataName."</td>
								<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".addslashes($infoArr['old'][$key])."</td>
								<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #B0B0B0;'>".addslashes($infoArr['new'][$key])."</td>
							</tr>";
			}else{ 
				$dataDiff.= "<tr>
								<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$dataName."</td>
								<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>&nbsp;".addslashes($infoArr['old'][$key])."</td>
								<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #B0B0B0;'>&nbsp;".addslashes($infoArr['new'][$key])."</td>
							</tr>";
			}
		}
	}
	$datachange = false;
	if(count($infoArr['cat_new'])>0){
		if(count($infoArr['cat_remove'])>0 || count($infoArr['cat_add'])>0){
			$datachange = true;
		}
		$paidCommonCat = '';
		$paidRmCat = '';
		$paidAddCat ='';
		foreach($infoArr['cat_commonNm'] as $catnewkey => $catnewname){
						$paidCommonCat .= $catnewname['category_name']."<br>";
		}
		if(count($infoArr['cat_remove'])>0){
			$paidRmCat.= "<br><font color='red'><b>Old Categories Removed :</b><br>";
			foreach($infoArr['cat_remove'] as $Rmkey => $Rmcatid){
				$paidRmCat.= $Rmcatid['category_name']."<br>";
			}
			$paidRmCat.= "</font>";
		}
		if(count($infoArr['cat_add'])>0){
			$paidAddCat.= "<br><font color='green'><b>New Categories Added :</b><br>";
			foreach($infoArr['cat_add'] as $Addkey => $Addcatid){
				$paidAddCat.= $Addcatid['category_name']."<br>";
			}
			$paidAddCat.= "</font>";
		}
		if($datachange){
			$dataDiff.= "<tr>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>Category Listing :</td>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$paidCommonCat.$paidRmCat."</td>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #B0B0B0;'>".$paidCommonCat.$paidAddCat."</td>
						</tr>";
		}else{
			$dataDiff.= "<tr>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>Category Listing :</td>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$paidCommonCat.$paidRmCat."</td>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #B0B0B0;'>".$paidCommonCat.$paidAddCat."</td>
						</tr>";
		}
	}
	
	$datachange = false;
	if(count($infoArr['cat_nonpaid_new'])>0){
		if(count($infoArr['cat_nonpaid_remove'])>0 || count($infoArr['cat_nonpaid_add'])>0){
			$datachange = true;
		}
		$NonpaidCommonCat = '';
		$NonpaidRmCat = '';
		$NonpaidAddCat ='';
		foreach($infoArr['cat_commonNonpaidNm'] as $Nonpcatnewkey => $Nonpcatnewname){
			$NonpaidCommonCat.= $Nonpcatnewname['category_name']."<br>";
		}
		if(count($infoArr['cat_nonpaid_remove'])>0){
			$NonpaidRmCat.= "<br><font color='red'><b>Old Categories Removed :</b><br>";
			foreach($infoArr['cat_nonpaid_remove'] as $NonpRmkey => $NonpRmcatid){
				$NonpaidRmCat.= $NonpRmcatid['category_name']."<br>";
			}
			$NonpaidRmCat.= "</font>";
		}
		if(count($infoArr['cat_nonpaid_add'])>0){
			$NonpaidAddCat.= "<br><font color='green'><b>New Categories Added :</b><br>";
			foreach($infoArr['cat_nonpaid_add'] as $NonpAddkey => $NonpAddcatid){
				$NonpaidAddCat.= $NonpAddcatid['category_name']."<br>";
			}
			$NonpaidAddCat.= "</font>";
		}
		if($datachange){
			$dataDiff.= "<tr>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>Category Listing :</td>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$NonpaidCommonCat.$NonpaidRmCat."</td>
							<td style='background:#FFFF99;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #efefef;'>".$NonpaidCommonCat.$NonpaidAddCat."</td>
						</tr>";
		}else{
			$dataDiff.= "<tr>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>Category Listing :</td>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #B0B0B0;border-bottom:1px solid #B0B0B0;'>".$NonpaidCommonCat.$NonpaidRmCat."</td>
							<td style='background:#fafafa;padding:20px;padding:20px;border-right:1px solid #efefef;border-bottom:1px solid #efefef;'>".$NonpaidCommonCat.$NonpaidAddCat."</td>
						</tr>";
		}
	}
	$dataDiff.= "</table>";
	$endMsg = "Thanks,<br> 
				Team Justdial</body></html>";
	$message = $mainBody.$dataDiff.$endMsg;
	return $message;
}

function getTitle($str)
{
	$title = false;
	if($str!=''){
		$pattern = "/^(MR|MRS|DR|MISS)\.?(\s*\w*)+$/i";
		$arr = explode(",",$str);
		$arr = array_merge(array_filter($arr));
		if(count($arr)>0){
			foreach($arr as $value){
				/*if(preg_match($pattern, $value)){echo "<br>".$value;
					$title = true;
				}*/
				$r = explode(" ",$value);
				$r[0] = $r[0].".";
				//echo "<br>line 79 --".preg_match($pattern, $r[0]);
				if(preg_match($pattern, $r[0])){
					$title = true;
					break;
				}
			}
		}
	}
	return $title;
}

function insertShortcode($infoArr=array(),$pid,$d_city,$module,$dbconOnlineregis1){
	if($infoArr['smsurl']!=''){
		$qryInsert = "INSERT INTO tbl_shortcode_details (transactionid,parentid,data_city,insert_date,source,smsurlcode) values ('".$infoArr['shortcode']."','".$pid."','".$d_city."','".date('Y-m-d H:i:s')."','".$module."','".$infoArr['smsurl']."')";
		$resInsert = $dbconOnlineregis1->query_sql($qryInsert);
	}
	return array($qryInsert,$resInsert);
}

function insertWebHistoryTable($pid,$dataArr,$dbcon){
	$DifArr = array_diff_assoc($dataArr['old'], $dataArr['new']);
	$insertField = "INSERT INTO tbl_attribute_change_history (parentid,transactionid,attribute,old_value,new_value,updatedOn) values";
	
	if(count($DifArr)>0){
		foreach($DifArr as $keys => $val){
			$insertFlag = true;
			if($keys=='contact_person'){ 
				if($dataArr['old']['contact_person']!='' && $dataArr['new']['contact_person']!=''){
					$rg="/(?:^|\W)(?:(?:Dr|Mr|Mrs|Ms|Sr|Jr)\.?|Miss|Phd|\+)(?:\W|$)/i";
					$oldContactP = trim(preg_replace($rg,",",preg_replace("/\([^)]+\)/","",$dataArr['old']['contact_person'])),",");
					$newContactP = trim(preg_replace($rg,",",preg_replace("/\([^)]+\)/","",$dataArr['new']['contact_person'])),",");
					$ConatactPOld = explode(",",$oldContactP);
					$ConatactPOld = array_merge(array_filter($ConatactPOld));
					$ConatactPnew = explode(",",$newContactP);
					$ConatactPnew = array_merge(array_filter($ConatactPnew));
					if(count($ConatactPOld)==count($ConatactPnew)){
						$oldContactP = preg_replace('/[\s]+/','',implode("",$ConatactPOld));
						$newContactP = preg_replace('/[\s]+/','',implode("",$ConatactPnew));
						/*$oldContactP = implode("",$ConatactPOld);
						$newContactP = implode("",$ConatactPnew);*/
						if(strtolower(trim($oldContactP))==strtolower(trim($newContactP))){
							$insertFlag = false;
						}
					}
				}
			}
			if(in_array($keys,array('company_name','building_name','street','area','subarea','landmark','city'))){
				if($dataArr['old'][$keys]!='' && $dataArr['new'][$keys]!=''){
					$oldCompanyNmArr = array();
					$newCompanyNmArr = array();
					$oldCompStr = '';
					$newCompStr = ''; 
					$oldCompanyNmArr = explode(" ",preg_replace('/[^A-Za-z0-9]/', '', $dataArr['old'][$keys]));
					$oldCompanyNmArr = array_merge(array_filter($oldCompanyNmArr));
					$newCompanyNmArr = explode(" ",preg_replace('/[^A-Za-z0-9]/', '', $dataArr['new'][$keys]));
					$newCompanyNmArr = array_merge(array_filter($newCompanyNmArr));
					$oldCompStr = implode($oldCompanyNmArr);
					$newCompStr = implode($newCompanyNmArr);
					if(strtolower(trim($oldCompStr))==strtolower(trim($newCompStr))){
						$insertFlag = false;
					}
				}
			}
			if($insertFlag){
				if($insertData!=''){
					$insertData .= "('".$pid."','".$dataArr['shortcode']."','".$keys."','".$dataArr['old'][$keys]."','".$dataArr['new'][$keys]."','".date('Y-m-d H:i:s')."'),";
				}else{
					$insertData = "('".$pid."','".$dataArr['shortcode']."','".$keys."','".$dataArr['old'][$keys]."','".$dataArr['new'][$keys]."','".date('Y-m-d H:i:s')."'),";
				}
			}
		}
		
	}
	
	if(count($dataArr['cat_new'])>0 && (count($dataArr['cat_remove'])>0 || count($dataArr['cat_add'])>0)){
		/*foreach($dataArr['cat_new'] as $catnewkey => $catnewname){
			if($catNames!=''){
				$catNames .= $catnewname['category_name']."|";
			}else{
				$catNames = $catnewname['category_name']."|";
			}
		}*/
		foreach($dataArr['cat_commonNm'] as $catnewkey => $catnewname){
			if($catNames!=''){
				$catNames .= $catnewname['category_name']."|";
			}else{
				$catNames = $catnewname['category_name']."|";
			}
		}
		if(count($dataArr['cat_remove'])>0){
			$removeCat = "|R|";
			foreach($dataArr['cat_remove'] as $Rmkey => $Rmcatid){
				$removeCat .= $Rmcatid['category_name']."|";
			}
		}
		if(count($dataArr['cat_add'])>0){
			$addCat = "|A|";
			foreach($dataArr['cat_add'] as $Addkey => $Addcatid){
				$addCat .= $Addcatid['category_name']."|";
			}
		}
		$oldcatvalues = rtrim($catNames,"|")."~".rtrim($removeCat,"|");
		$newcatvalues = rtrim($catNames,"|")."~".rtrim($addCat,"|");
		if($insertData!=''){
			$insertData .= "('".$pid."','".$dataArr['shortcode']."','category','".$oldcatvalues."','".$newcatvalues."','".date('Y-m-d H:i:s')."'),";
		}else{
			$insertData = "('".$pid."','".$dataArr['shortcode']."','category','".$oldcatvalues."','".$newcatvalues."','".date('Y-m-d H:i:s')."'),";
		}
	}
	
	if(count($dataArr['cat_nonpaid_new'])>0 && count($dataArr['cat_nonpaid_remove'])>0 || count($dataArr['cat_nonpaid_add'])>0){
		/*foreach($dataArr['cat_nonpaid_new'] as $Nonpcatnewkey => $Nonpcatnewname){
			if($NonPcatNames!=''){
				$NonPcatNames .= $Nonpcatnewname['category_name']."|";
			}else{
				$NonPcatNames = $Nonpcatnewname['category_name']."|";
			}
		}*/
		foreach($dataArr['cat_commonNonpaidNm'] as $Nonpcatnewkey => $Nonpcatnewname){
			if($NonPcatNames!=''){
				$NonPcatNames .= $Nonpcatnewname['category_name']."|";
			}else{
				$NonPcatNames = $Nonpcatnewname['category_name']."|";
			}
		}
		if(count($dataArr['cat_nonpaid_add'])>0){
			$addNonpCat = "|A|";
			foreach($dataArr['cat_nonpaid_add'] as $NonpAddkey => $NonpAddcatid){
				$addNonpCat.= $NonpAddcatid['category_name']."|";
			}
		}
		
		if(count($dataArr['cat_nonpaid_remove'])>0){
			$removeNonpCat = "|R|";
			foreach($dataArr['cat_nonpaid_remove'] as $nonpkey => $nonpval){
				$removeNonpCat.= $nonpval['category_name']."|";
			}
		}
		
		$oldNonpcatvalues = rtrim($NonPcatNames,"|")."~".rtrim($removeNonpCat,"|");
		$newNonpcatvalues = rtrim($NonPcatNames,"|")."~".rtrim($addNonpCat,"|");
		
		if($insertData!=''){
			$insertData .= "('".$pid."','".$dataArr['shortcode']."','nonpaid category','".addslashes($oldNonpcatvalues)."','".addslashes($newNonpcatvalues)."','".date('Y-m-d H:i:s')."'),";
		}else{
			$insertData = "('".$pid."','".$dataArr['shortcode']."','nonpaid category','".addslashes($oldNonpcatvalues)."','".addslashes($newNonpcatvalues)."','".date('Y-m-d H:i:s')."'),";
		}
	}
	//echo "<hr><pre>";print_r($dataArr);die();
	
	if($insertData!=''){
		$insertData = trim($insertData,",");
	}
	$qryInsert = $insertField.$insertData;
	$res_qryInsert = $dbcon->query_sql($qryInsert);
	return array($qryInsert,$res_qryInsert);
}
?>
