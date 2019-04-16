<?php
set_time_limit(0);
session_start();

class companymasterSearch
{



//~ if(!defined('APP_PATH'))
//~ {
	//~ require_once("../library/config.php");
//~ }

//~ include_once(APP_PATH.'common/Serverip.php');
//~ include_once(APP_PATH.'library/path.php');
//~ require_once(APP_PATH.'common/dbconnection/config.php');
//~ require_once(APP_PATH.'common/dbconnection/db.class.php');
//~ global $dbarr;


	//$local_obj = new DB($dbarr['DB_IRO']);
	
	

	
###############		FUNCTION LIST		##################


	function updateCompanymasterSearch($parentid,$conn_iro,$ucodecrl,$compmaster_obj,$conn_rtSPHINX_1='',$conn_rtSPHINX_2='')
	{
		$local_obj=$conn_iro;
		GLOBAL $Stemmer;
		
		$this->parentid = $parentid;
		$this->conn_iro = $conn_iro;
		
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "nationalid,sphinx_id,regionid,docid,companyname,parentid,area,latitude,longitude,contact_person,state,city,full_address,pincode,email,website,paid,mobile,mobile_display,landline,landline_display,stdcode,tollfree,tollfree_display,fax,virtualNumber,data_city,TRIM(LOWER(full_address)) as address_search,company_callcnt";
		$tablename		= "tbl_companymaster_generalinfo";
		$wherecond		= "parentid='".$parentid."'";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		if ($temparr['numrows'])
		{
			$genralInfoArr=$temparr['data']['0'];
		}
		$this->geninfo_company = $genralInfoArr['companyname'];
		$this->geninfo_city = $genralInfoArr['city'];
		$address = 	$genralInfoArr['full_address'].",".$genralInfoArr['city'].",".$genralInfoArr['state'];
		$date = date("Y-m-d H:i:s");
		$this->company_callcnt = $genralInfoArr['company_callcnt'];
		
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "catidlineage_search,national_catidlineage_search,freeze,mask,catidlineage,averagerating";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid='".$parentid."'";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	
		if ($temparr['numrows'])
		{
			$extDetArr=$temparr['data']['0'];
		}
		if($extDetArr['freeze']== 1 || $extDetArr['mask'] == 1)
		{
			$display_flag = 0;
		}
		else
		{
			$display_flag = 1;
		}
		
		if($extDetArr['catidlineage']){
			$this->extradet_catidlineage  = $extDetArr['catidlineage'];
		}
		if($extDetArr['catidlineage_search'])
		{
			$this->extradet_catidlineage_search = $extDetArr['catidlineage_search'];
		}
		
	    $this->averagerating = $extDetArr['averagerating'];
		
					// new column population start
					//$parentid		= $row['parentid'];
					//echo '<br>'.$parentid;
					$compname = $genralInfoArr['companyname']; ## ORIGINAL COMPANY NAME WITH AREA
					$compname = str_ireplace('(customer care)','Customer Care',$compname);
					$compname = str_ireplace('(prepaid)','Prepaid',$compname);
					$compname = str_ireplace('(postpaicompname_area_search_processedd)','Postpaid',$compname);
					$compname = str_ireplace('(booking office)','Booking Office',$compname);
					
					//$sql_area = "select area as areaname,TRIM(LOWER(full_address)) as address_search from tbl_companymaster_generalinfo WHERE parentid='".$parentid."'";
					//$res_area = $local_obj->query_sql($sql_area);
					//$row_area = mysql_fetch_assoc($res_area);
					
					//$areaname	= sanitize($row_area['areaname'],1);
					$areaname	= $this->sanitize($genralInfoArr['area'],1);
					
					$areaname_processed = $this->concatsingle($this->getSingular($areaname));
					

					## COMPANY NAME WITH OUT AREA
					//$compname_search_witoutbracket_area = $compname_search_witoutbracket = trim(preg_replace('#\(.*\)#','',$this->sanitize($compname,1))); 
					$compname_search_witoutbracket_area = $compname_search_witoutbracket = $this->braces_content_removal($this->sanitize($compname,1)); 
					/*
					if($row['docid']!=$row['synid']) ## humne isliye kya hia jisse ki synonym se brakcet aur uske ander ka sub nikal jaye kya bole to string
					{
						$compname_area	= braces_content_removal($compname).(($areaname)?' ('.$areaname.')':'');
						$compname_search = braces_content_removal($this->sanitize($compname,1));

					}
					else
					{
						
						$compname_area	= $compname.(($areaname)?' ('.$areaname.')':'');
						$compname_search = braces_content_removal($this->sanitize($compname));
					}
					*/
					$compname_area	= $compname.(($areaname)?' ('.$areaname.')':'');
					$compname_search = $this->braces_content_removal($this->sanitize($compname));
						
					$compname_search_wo_space	= preg_replace('/\s*/m','',$compname_search);
					$compname_search_ignore		= $this->applyIgnore($compname_search_witoutbracket);
					
					$compname_area_search			= $this->companyWithArea($compname_search,$areaname);
					$compname_area_search_without	= $this->companyWithArea($compname_search_witoutbracket_area,$areaname);
					$compname_area_search_wo_space	= preg_replace('/\s*/m','',$compname_area_search);
					$compname_area_search_ignore    = $this->companyWithArea($compname_search_ignore,$areaname);
					$compname_area_search_wo_ignore=  preg_replace('/\s*/m','',$this->companyWithArea($compname_search_ignore,$areaname));
					$compname_search_processed = $this->concatsingle($this->getSingular($compname_search));
					$compname_search_processed_wo_space = preg_replace('/\s*/m','',$compname_search_processed);
					$compname_search_processed_ignore = $this->concatsingle($this->applyIgnore($this->getSingular($compname_search_witoutbracket)));
					$compname_search_processed_ignore_wo_space = preg_replace('/\s*/m','',$compname_search_processed_ignore);		

					$compname_area_search_processed = $this->concatsingle($this->getSingular($compname_area_search));
					$compname_area_search_processed_wo_space = preg_replace('/\s*/m','',$compname_area_search_processed);
					$compname_area_search_processed_ignore = $this->concatsingle($this->companyWithArea($compname_search_processed_ignore,$areaname));
					$compname_area_search_processed_ignore_wo_space =  preg_replace('/\s*/m','',$compname_area_search_processed_ignore);
					
					//$address_search			= trim(preg_replace('#\(.*\)#','',sanitize($row['address_search'],1)));
					$address_search			= $this->braces_content_removal($this->sanitize($genralInfoArr['address_search'],1)); 
					 
					//echo '<pre><br>'.
					/*
					$sql_updt = "update tbl_companymaster_search SET										
										area					= '".addslashes(stripslashes($areaname))."',	
										compname_search_processed_ignore		= '".$compname_search_processed_ignore."',
										compname_search_processed_ignore_wo_space = '".$compname_search_processed_ignore_wo_space."',
									    area_processed					='".$areaname_processed."',
									    compname_area_search_processed_ignore_wo_space	='".$compname_area_search_processed_ignore_wo_space."',
										compname_area_search_processed_ignore			='".$compname_area_search_processed_ignore."',
										address_search = '".$address_search."'										
								 where parentid ='".$parentid."'";
					$res_updat =$local_obj->query_sql($sql_updt);
					*/
					// new column population end
		
		//-------------- creating Array for Phone search display---------------------------//
		if(trim($genralInfoArr[mobile]))
			$phone_searchArr[]=trim($genralInfoArr[mobile]);
		if(trim($genralInfoArr[landline]))
			$phone_searchArr[]=trim($genralInfoArr[landline]);
		if(trim($genralInfoArr[tollfree]))
			$phone_searchArr[]=trim($genralInfoArr[tollfree]);
		if(trim($genralInfoArr[fax]))
			$phone_searchArr[]=trim($genralInfoArr[fax]);
		if(trim($genralInfoArr[virtualNumber]))
			$phone_searchArr[]=trim($genralInfoArr[virtualNumber]);
				
		$phone_search	= dataInString(',',$phone_searchArr);
		
		
		
		// Stemming of data (company name) //

		$nameWs = $genralInfoArr['companyname'];
		$wordWs = explode(" ",$nameWs);
		$search_wordWs = "";
		for($i=0; $i< sizeof($wordWs); $i++){
			$search_wordWs .="".($wordWs[$i]);
		}
					
		$companyname_Ws = trim($search_wordWs); //company name without spa//
		
		$stringtostem = addslashes($genralInfoArr['companyname']);

		
		$string = strtolower($stringtostem); 

		$wordstring = preg_replace( "/\$|,|@|#|~|`|\%|\*|\^|\&|\(|\)|\+|\=|\[|\-|\_|\]|\[|\}|\{|\;|\:|\'|\"|\<|\>|\?|\||\\|\!|\$|\./"," ", $string );

		$word = explode(" ",$wordstring);
		
		$search_stemed = "";
		for($i=0; $i< sizeof($word); $i++){
			$search_stemed .=" ".$Stemmer -> Stem($word[$i]);	//Stemmed word//
			$search_stemed_WS .="".$Stemmer -> Stem($word[$i]);  //stemmed name without space//
		}
		
		$compname_stemed = trim($search_stemed);
		$companyname_search_stem_WS = trim($search_stemed_WS);
		$companyname_search_area = addslashes($genralInfoArr['companyname'])."-".$genralInfoArr['area'];
		
		$singular = $this->applyIgnore($genralInfoArr['companyname']);
		$singularWOspace = str_replace(" ","",$singular);
		//echo "<pre>";echo "in curl_serverside.php: <br>";
		
		//companyname_search, companyname_search_area, compname_search_processed_ignore,compname_area_search_processed_ignore
		/***----------new logic of sphinx search starts-----------***/
		$comp_arr = array();
		$comp_arr = $this->get_area_compname_merged_fields($genralInfoArr['companyname'],$genralInfoArr['area']);
		$compname_search             = $comp_arr['compname_search']; 			  
		$compname_search_ignore 	 = $comp_arr['compname_search_ignore'];	  
		$compname_area_search 		 = $comp_arr['compname_area_search'];		  
		$compname_area_search_ignore = $comp_arr['compname_area_search_ignore'];
		
		/***----------new logic of sphinx search ends-----------***/
		
		
		$insarr['tbl_companymaster_search']	= array(
												"nationalid"									=>	$genralInfoArr['nationalid'],
												"sphinx_id"										=>	$genralInfoArr['sphinx_id'],
												"regionid"										=>	$genralInfoArr['regionid'],
												"docid"											=>	$genralInfoArr['docid'],
												"companyname"									=>	addslashes($genralInfoArr['companyname']),
												"parentid"										=>	$genralInfoArr['parentid'],
												"companyname_search"							=>	addslashes($compname_search),
												"companyname_search_area"						=>	addslashes($compname_area_search),
												"companyname_search_stem"						=>	addslashes($compname_stemed),
												"companyname_search_WS"	 						=>	addslashes($companyname_Ws),
												"companyname_search_stem_WS"					=>	addslashes($companyname_search_stem_WS),
												"latitude"										=>	$genralInfoArr['latitude'],
												"longitude"										=>	$genralInfoArr['longitude'],
												"contact_person"								=>	addslashes($genralInfoArr['contact_person']),
												"state"											=>	addslashes($genralInfoArr['state']),
												"city"											=>	addslashes($genralInfoArr['city']),
												"data_city"										=>	addslashes($genralInfoArr['data_city']),
												"pincode"										=>	$genralInfoArr['pincode'],
												"phone_search"									=>	$phone_search,
												"address"										=>	addslashes($address),
												"email"											=>	$genralInfoArr['email'],
												"website"										=>	addslashes($genralInfoArr['website']),
												"catidlineage_search"							=>	$extDetArr['catidlineage_search'],
												"national_catidlineage_search" 					=> 	$extDetArr['national_catidlineage_search'],
												"length"										=>	strlen($genralInfoArr['companyname']),
												"display_flag"									=>	$display_flag,
												"prompt_flag"									=>	'0',
												"paid"											=>	$genralInfoArr['paid'],
												"updatedBy"										=>	$ucodecrl,
												"updatedOn"										=>	$date,
												"compname_search_singular"						=>	addslashes($singular),
												"compname_search_singular_wo_space"				=>	addslashes($singularWOspace),
												"area"											=>	addslashes(stripslashes($areaname)),
												"compname_search_processed_ignore"				=>	$compname_search_processed_ignore,
												"compname_search_processed_ignore_wo_space"		=>	$compname_search_processed_ignore_wo_space,
												"area_processed"								=>	$areaname_processed,
												"compname_area_search_processed_ignore_wo_space"=>	$compname_area_search_processed_ignore_wo_space,
												"compname_area_search_processed_ignore"			=>	$compname_area_search_processed_ignore,
												"address_search" 								=>	$address_search
											);
				$compmaster_obj->UpdateRow($insarr);
				
				$sqlDelInactiveData = "DELETE FROM tbl_companymaster_search_inactive WHERE parentid = '".$genralInfoArr['parentid']."'";
				$resDelInactiveData = $conn_iro->query_sql($sqlDelInactiveData);
				
				$sp_url = IRO_APP_URL."/mvc/services/autosuggest/rt_index/com_instant?city=".urlencode($genralInfoArr['data_city'])."&mcity=".urlencode($genralInfoArr['data_city'])."&parentid=".$genralInfoArr['parentid']."&mt=0&debug=0";
				
				$ch = curl_init();        
				curl_setopt($ch, CURLOPT_URL, $sp_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 400);
				$output = curl_exec($ch);
				curl_close($ch); 
				
				 // Sphinx insertion start
				/* 
				$sql_sphinx_delete="delete from rt_comp_search where id = ".$genralInfoArr['sphinx_id']."";
				
				$Strcomparea= str_replace("'","",str_replace('-','',str_replace(')','', str_replace('(','',addslashes($companyname_Ws))))).str_replace(' ' ,'',addslashes(stripslashes($areaname)));	
						
				$comp_Ws = str_replace("'","",str_replace('-','',str_replace(')','', str_replace('(','',addslashes($companyname_Ws)))));
						
				$whtwhr_array = getWhatWhereFields($genralInfoArr['companyname'],$genralInfoArr['parentid'],$genralInfoArr['city'],$areaname);
						
				$insert_query="insert into rt_comp_search(id,c,ca,cs,cas,csi,casi,csw,casw,cl,pin,cls,cp,ads,ps,st,em,wb,jid,n,a,p,pop,f,updt,lc,lca,lcs,lcas,lcsi,lcasi,actf) values(".$genralInfoArr['sphinx_id'].",'".addslashes($genralInfoArr['companyname'])."','".addslashes($whtwhr_array['compname_area'])."','".addslashes($whtwhr_array['compname_search'])."','".addslashes($whtwhr_array['compname_area_search'])."','".$whtwhr_array['compname_search_ignore']."','".$whtwhr_array['compname_area_search_ignore']."','".addslashes($comp_Ws)."','".addslashes($compname_area_search_processed_ignore_wo_space)."','".addslashes($genralInfoArr['data_city'])."','".$genralInfoArr['pincode']."','".$extDetArr['catidlineage_search']."','".addslashes($genralInfoArr['contact_person'])."','".$address_search."','".$phone_search."','".addslashes($genralInfoArr['state'])."','".$genralInfoArr['email']."','".addslashes($genralInfoArr['website'])."','".$genralInfoArr['parentid']."','".addslashes($genralInfoArr['companyname'])."','".addslashes(stripslashes($areaname))."','".$genralInfoArr['paid']."',0,'".$display_flag."','".strtotime($date)."','".strlen($genralInfoArr['companyname'])."','".strlen(($genralInfoArr['companyname'])." ".stripslashes($areaname))."','".strlen($genralInfoArr['companyname'])."','".strlen($companyname_search_area)."','".strlen($compname_search_processed_ignore)."','".strlen($compname_area_search_processed_ignore_wo_space)."','1')";
				
				$sphinx_arr = array("0"=>$conn_rtSPHINX_1,"1"=>$conn_rtSPHINX_2);
				foreach($sphinx_arr AS $key=>$server)
				{
					$conn_rtSPHINX_conn = 	mysql_connect($server['0'],$server['1'],$server['2']);
					if(mysql_ping($conn_rtSPHINX_conn))
					{
						$res_del	=	mysql_query($sql_sphinx_delete,$conn_rtSPHINX_conn);		
						$fet		=	mysql_query($insert_query,$conn_rtSPHINX_conn);
						
						$this->logssphinxquery($parentid,$sql_sphinx_delete ."<br>". $insert_query ."<br> ".$server['0']."=>".$fet);	
					}
				}
				*/
				// Sphinx insertion End
				

			// companymaster serach sphinx search data population end
			
			
				
	}
	function updateContractAddInfo($compmaster_obj){
			$catid		  = preg_replace("/[^ 0-9 ]/", ' ', $this->extradet_catidlineage);
			$catidArray	  = explode(' ',$catid);
			$catidArray	  = array_merge(array_unique(array_filter($catidArray)));
			
			$popular_cat=""; // by default it is blank
			
			if(count($catidArray)>0)
			{
				$catids= implode(",",$catidArray);
				$insert_pop_cat	= "SELECT category_name,catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN(".$catids.") Order BY callcount DESC LIMIT 1";
				$res_pop_cat	= $this->conn_iro->query_sql ($insert_pop_cat);
				
				if(mysql_num_rows($res_pop_cat)>0)
				{
					$row_pop = mysql_fetch_assoc($res_pop_cat);
					$popular_cat = $row_pop['category_name'];
					// updateing hotcategory of contract 
					$dataArray['tbl_companymaster_extradetails']= array("hotcategory"=>"/".$row_pop['catid']."/");
					$wharecond = "parentid='".$this->parentid."'";
					$compmaster_obj->UpdateFields($dataArray,$wharecond);
				}
				
			}else // if there is no any category its hot category should be blanked 
			{
				$dataArray['tbl_companymaster_extradetails']= array("hotcategory"=>"");
				$wharecond = "parentid='".$this->parentid."'";
				$compmaster_obj->UpdateFields($dataArray,$wharecond);					
			}
			
			/**---------Hanling To Update Price Range Column In Extradetails Start-----------**/
					
				$catid_srch		= preg_replace("/[^ 0-9 ]/", ' ', $this->extradet_catidlineage_search);
				$catidSrchArr	= explode(' ',$catid_srch);
				$catidSrchArr	  	= array_merge(array_unique(array_filter($catidSrchArr)));
				
				$catid_list_str = '';
				$rest_cat_info = array();
				$rest_price_range_arr = array();
				$block_for_recommended = 0;
				#echo "<pre>catidSrchArr:-";print_r($catidSrchArr);
				if(count($catidSrchArr)>0)
				{
					$catid_list_str = implode(",",$catidSrchArr);
					if(!empty($catid_list_str))
					{
						$sqlFetchRestPriceRange = "SELECT display_product_flag,miscellaneous_flag,rest_price_range FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN (".$catid_list_str.")";
						$resFetchRestPriceRange = $this->conn_iro->query_sql($sqlFetchRestPriceRange);
						if($resFetchRestPriceRange && mysql_num_rows($resFetchRestPriceRange)>0)
						{
							while($row_price_range = mysql_fetch_assoc($resFetchRestPriceRange))
							{
								$display_product_val 	= trim($row_price_range['display_product_flag']);
								$miscellaneous_flag 	= trim($row_price_range['miscellaneous_flag']);
								
								if(((int)$display_product_val & 134217728) == 134217728){
									$rest_price_range_arr[] = trim($row_price_range['rest_price_range']);
								}
								
								if(((int)$miscellaneous_flag & 128) == 128){
									$block_for_recommended = 1;
								}
								
							}
						}
					}		
				}
				$price_range = 0;
				$rest_price_range_arr  = array_merge(array_unique(array_filter($rest_price_range_arr)));
				#echo "<pre>rest_price_range_arr:-";print_r($rest_price_range_arr);
				if(count($rest_price_range_arr)>0)
				{
					$rest_price_final_arr = array();
					foreach($rest_price_range_arr as $catname_display_str)
					{
						$catname_display_str = preg_replace('/\s+/', '', $catname_display_str); // Removing Space From categoryname_display Field
						$rest_price_final_arr[] = $catname_display_str;
					}
					$rest_price_final_arr = array_map('trim',$rest_price_final_arr);
					$rest_price_final_arr = array_filter($rest_price_final_arr);
					$rest_price_final_arr = array_map('strtolower', $rest_price_final_arr);
					$different_price_range_arr = array('inexpensive'=>'1','moderate'=>'2','expensive'=>'3','veryexpensive'=>'4');
					foreach($different_price_range_arr as $price_key => $price_val)
					{
						if(in_array($price_key,$rest_price_final_arr))
						{
							$price_range = $price_val;
							break;
						}
					}
				}
				#echo "<br> block_for_recommended : ".$block_for_recommended;
				if($block_for_recommended == 1){
					$misc_flag_val 	= 16;
					$misc_flag 		= "misc_flag + if(misc_flag&".$misc_flag_val."=".$misc_flag_val.",0,".$misc_flag_val.")" ;
				}else{
					$misc_flag_val 	= 16;
					$misc_flag 		= "misc_flag - if(misc_flag&".$misc_flag_val."=".$misc_flag_val.",".$misc_flag_val.",0)" ;
				}
				
				$sqlUpdateExtraAddInfo = "UPDATE tbl_companymaster_extradetails SET price_range = '".$price_range."', misc_flag = ".$misc_flag." WHERE parentid = '".$this->parentid."'";
				$resUpdateExtraAddInfo = $this->conn_iro->query_sql($sqlUpdateExtraAddInfo);
				$this->insertPriceRangeLog($this->parentid,$rest_price_range_arr,$price_range,$this->conn_iro);
			
			/**---------Handling To Update Price Range Column In Extradetails End-----------**/
		
		
	}
	
	function updatebusinesstags($compmaster_obj,$conn_iro,$parentid,$userID,$conn_data)
	{
		$fieldstr 		= "catidlineage_search,businesstags";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid='".$parentid."'";
		$resarr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		
		if ($resarr['numrows'])
		{
			$resarr_extDet=$resarr['data']['0'];
		}
		$businesstags_value = $resarr_extDet['businesstags'];
		$categorylist = $resarr_extDet['catidlineage_search'];
		
		//echo "<br>businesstags_value".$businesstags_value;
		$businesstags_value = intval($businesstags_value);
		
		$categorylist = str_replace("/","",$categorylist);		
		$categorylistArr = explode(",",$categorylist);
		$categorylistArr = array_unique($categorylistArr);
		
		$categorylistArr = array_filter($categorylistArr);
		
		$trending_flag = 0;
		if(count($categorylistArr)>0)
		{
			//businesstype,businesstags_value
		
			$categorylist = implode(",",$categorylistArr);
			$brandNameflg = 0;
		
			//check for brand_name in case if shopFront category, 
			$QryBrandName = "SELECT catid, brand_name FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN (".$categorylist.")  AND  brand_name !='' AND display_product_flag&2=2 LIMIT 1";
			$ResBrandname = $conn_iro->query_sql($QryBrandName);
			if(mysql_num_rows($ResBrandname)>0)
			{
				$brandNameflg =1;
			}
			$activeverticalslist =  "SELECT column_name,value,p.vertical_abbr as businesstype,businesstags_value FROM d_jds.tbl_display_product p join db_iro.tbl_businesstype_master b using(vertical_abbr) where p.vertical_abbr!='' and p.vertical_abbr is not null ";
			$activeverticalslist_rs = $conn_iro->query_sql($activeverticalslist);
		
			if(mysql_num_rows($activeverticalslist_rs))
			{
				$sql="select ";
				while($activeverticalslist_arr= mysql_fetch_assoc($activeverticalslist_rs))
				{
					$sql=$sql." max(".trim($activeverticalslist_arr['column_name'])."&".trim($activeverticalslist_arr['value'])."=".trim($activeverticalslist_arr['value']).") as ".trim($activeverticalslist_arr['businesstype'])." ,";
				
					$businesstagsarr[trim($activeverticalslist_arr['businesstype'])]=trim($activeverticalslist_arr['businesstags_value']);
				}
			
			$sql=trim($sql,",");
			$sql= $sql." from d_jds.tbl_categorymaster_generalinfo where catid in (".$categorylist.")";		
			$resultset = $conn_iro->query_sql($sql);
		
			$businesstagsarrlogingstr="";
				$business_abbr_flag='';
				$resultarray= array();
				
			if(mysql_num_rows($resultset) > 0)
			{
				$resultarray = mysql_fetch_assoc($resultset);	
				$businesstags_value	= 0;
				foreach ($businesstagsarr as $verticalname => $verticalbitval )
				{
					$businesstagsarrlogingstr.=$verticalname."=".intval($resultarray[$verticalname]).",";
						//if brand_name found, update businesstags else don't
				if($verticalbitval==2 && $brandNameflg==1)
						{
							$businesstags_value = ($businesstags_value | $verticalbitval);
							$business_abbr_flag .= "".$verticalname."|";
						}
						elseif(intval($resultarray[$verticalname])===1 && $verticalbitval !=2)
						{
							$businesstags_value = ($businesstags_value | $verticalbitval);
							$business_abbr_flag .= "".$verticalname."|";
						
						}
						else
						{	
							if(($businesstags_value & $verticalbitval) == $verticalbitval)
							{	
								$businesstags_value = ($businesstags_value ^ $verticalbitval);
								$business_abbr_flag .= "".$verticalname."|";
							}
					
					}			
				}
				if($business_abbr_flag!=''){
						$business_abbr_flag = "|".$business_abbr_flag;
					}else{
						$business_abbr_flag = '';
					}
			
			}
			else // we have no category so we will remove if all vertical tagging
			{
				$businesstags_value = 0;
				$business_abbr_flag = '';
			}
		
			}
			$trending_restaurant_catid = $this->getTrendingRestaurantsCatid($conn_iro);
			if(intval($trending_restaurant_catid)>0 && in_array($trending_restaurant_catid,$categorylistArr)){
				$trending_flag = 1;
			}
		}
		
		$insertarr['tbl_companymaster_extradetails'] = array("businesstags"=>$businesstags_value,"trending_flag"=>$trending_flag,"business_abbr_flag"=>$business_abbr_flag);				
		$insertarr['tbl_companymaster_search'] 		 = array("businesstags"=>$businesstags_value);
		$wherecond		= "parentid='".$parentid."'";
		$update_res  = $compmaster_obj->UpdateFields($insertarr,$wherecond);		
		$this->logsbusinesstags($parentid,$businesstags_value,$categorylist,$businesstagsarrlogingstr,$userID,$conn_iro,$brandNameflg,$business_abbr_flag,$conn_data);
		unset($business_abbr_flag,$businesstagsarr,$resultarray);
	}
	function UpdateExtradetailCompanySearch($conn_iro,$parentid){
		
		
		$sqlContractExtraDet = "SELECT catidlineage, catidlineage_nonpaid,tag_catid FROM tbl_companymaster_extradetails WHERE parentid ='".$parentid."' ";
		$resContrctExtraDet = $conn_iro->query_sql($sqlContractExtraDet);
		if($resContrctExtraDet && mysql_num_rows($resContrctExtraDet) > 0)
		{
			while($rowContrctExtraDet = mysql_fetch_assoc($resContrctExtraDet)) 
			{
				$catidlineage_originArr  = array();$catidlineage_nonpaidArr = array();$consolidatedCatid = array();$filterArr = array();$diffArr = array();
				$tag_catid               = $rowContrctExtraDet['tag_catid'];
				$catidlineage_originArr  = explode(",", $rowContrctExtraDet['catidlineage']);
				$catidlineage_originArr  = str_replace("/","",$catidlineage_originArr);
				$catidlineage_nonpaidArr = explode(",", $rowContrctExtraDet['catidlineage_nonpaid']);
				$catidlineage_nonpaidArr = str_replace("/","",$catidlineage_nonpaidArr);
				$consolidatedCatid       = array_merge((array)$catidlineage_originArr, (array)$catidlineage_nonpaidArr);
				foreach($consolidatedCatid as $key=>$value){
					if($value==''){
						unset($consolidatedCatid[$key]);
					}
				}
				$consolidatedCatidStr = implode(",",$consolidatedCatid);
				if($consolidatedCatidStr!="''" && count($consolidatedCatid)>0) {
					$sqlHfilter = "SELECT distinct a.catid FROM d_jds.tbl_categorymaster_generalinfo a join d_jds.tbl_categorymaster_generalinfo b on a.national_catid=b.associate_national_catid 
					WHERE b.catid IN (".$consolidatedCatidStr.") AND b.national_catid!=b.associate_national_catid ;";
					$resHfilter = $conn_iro->query_sql($sqlHfilter);
					while($rowHfilter = mysql_fetch_assoc($resHfilter)) {
						$filterArr[] = $rowHfilter['catid'];
					}
					unset($resHfilter, $sqlHfilter, $rowHfilter);
				}
				$diffArr = array_diff($filterArr, $consolidatedCatid);
				$finalCatlist = array_merge((array)$consolidatedCatid, (array)$diffArr);
				if(count($finalCatlist)>0){
					$Strcatidlineage_search = "/".implode("/,/", $finalCatlist)."/";
				}
				$catidLineage_arr_temp = explode("/,/", trim($rowContrctExtraDet['catidlineage'],"/"));
				$catidLineage_arr_temp = array_filter($catidLineage_arr_temp);
				$finalCatlistStr = implode(",",$finalCatlist);
				$arrNationalCatIds=array();	

				if($finalCatlistStr!='') {
					$get_cat_list = "select category_name as catname,catid,national_catid,if(category_verticals&8192=8192,1,0) as LifestyleTag,category_verticals as classtype, if(business_flag='1' or category_scope=1,1,0) as b2b_tag, if(auto_suggest_flag=1 or national_catid=associate_national_catid,1,0) AS dflag,promt_ratings_flag,business_tag from d_jds.tbl_categorymaster_generalinfo where catid in (".$finalCatlistStr.") and isdeleted=0 and (mask_status=0 or category_name like 'C2C%') AND active_flag > 0 AND biddable_type = 1 group by catid,category_name order by filter_callcount_rolling desc";
					$res_get_cat_list = $conn_iro->query_sql($get_cat_list);					
					if($res_get_cat_list)
					{
						$temparr = array();
						while($row_get_cat_list = mysql_fetch_assoc($res_get_cat_list)){
							$temparr[$row_get_cat_list['catid']] = $row_get_cat_list;
						}
						if(isset($temparr[$tag_catid])){
							$tagcatele = $temparr[$tag_catid];
							unset($temparr[$tag_catid]);
							array_unshift($temparr, $tagcatele);
						}
						$k =0;
						foreach($temparr as $key=>$value) {
							$row_get_cat_list = $value;
							$k++;
							if(trim($row_get_cat_list['national_catid'])!='')
								$arrNationalCatIds[$row_get_cat_list['catid']]=$row_get_cat_list['national_catid'];
						}
						unset($temparr, $tagcatele);
					}
				}	
				unset($catidlineage_originArr, $catidlineage_nonpaidArr, $consolidatedCatid, $consolidatedCatidStr, $filterArr, $diffArr, $finalCatlist, $finalCatlistStr);
				if(count($catidLineage_arr_temp)>0){
					foreach($catidLineage_arr_temp as $keyCat=>$valueCat) {
						$nationalCatid_catidLineage[$valueCat] = $arrNationalCatIds[$valueCat];
					}
				}
				$finalStrNationalCatid = "";
				if(count($nationalCatid_catidLineage)>0) {
					$finalStrNationalCatid = '/'.implode("/,/", $nationalCatid_catidLineage).'/';
				}
				if($arrNationalCatIds){
					$strNationalCatIds = "/".implode("/,/",array_unique($arrNationalCatIds))."/";
					$natCatids_Comma_Sep = implode(",",array_unique($arrNationalCatIds));
				}
				
			}	
			$updateExtraDtls="UPDATE 
			db_iro.tbl_companymaster_extradetails 
			  SET 
				national_catidlineage_search	=	'".$strNationalCatIds."' ,
				national_catidlineage 			=	'".$finalStrNationalCatid."' ,
				catidlineage_search 			= 	'".$Strcatidlineage_search."',
				db_update 						= 	'".date("Y-m-d H:i:s")."'
			  WHERE 
				parentid='".$parentid."';";
			$tempupdExDet = $conn_iro->query_sql($updateExtraDtls);
			
			$this->extra_national_catidlineage_search = $natCatids_Comma_Sep;

			$updateCompSearch="UPDATE 
				db_iro.tbl_companymaster_search 
			   SET 
				national_catidlineage_search	=	'".$strNationalCatIds."',
				catidlineage_search 			= 	'".$Strcatidlineage_search."'
			   WHERE 
				parentid='".$parentid."';";
			$tempupdCompSrch = $conn_iro->query_sql($updateCompSearch);
				
		}
	}
	function logsbusinesstags($parentid,$businesstags,$categorylist,$businesstagsarrlogingstr,$userID,$conn_iro,$brandNameflg ,$business_abbr_flag,$conn_data)
	{
		$this->insert_businesstags_log($parentid,$businesstags,$categorylist,$businesstagsarrlogingstr,$userID,$conn_iro,$brandNameflg,$business_abbr_flag,$conn_data);
		return ; // stoping making log
		$log_msg='';
		$log_path = APP_PATH.'logs/businesstagslog/';
		$sNamePrefix= $log_path;
		// fetch directory for the file
		$pathToLog = dirname($sNamePrefix); 
		//unlink($log_path);
		if(!file_exists($log_path)) {
			mkdir($log_path, 0755, true);;
		}
		/*$file_n=$sNamePrefix.$contractid.".txt"; */
		$file_n=$sNamePrefix.$parentid.".html";
		$command	= "chown apache:apache ". $file_n;
		$output = shell_exec($command);
		//echo $file_n;
		// Set this to whatever location the log file should reside at.
		$logFile = fopen($file_n, 'a+');

			
		//$userID= $_SESSION['ucode'];
		/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
		$pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
		$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
						<tr valign='top'>
							<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
							<td style='width:15%; border:1px solid #669966'>businesstags:".$businesstags."</td>
							<td style='width:30%; border:1px solid #669966'>categorylist:".$categorylist."</td>
							<td style='width:30%; border:1px solid #669966'>businesstagsarrlogingstr:".$businesstagsarrlogingstr."</td>
							<td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
							<td style='width:10%; border:1px solid #669966'>businesstags_abbr_flag :-:".$business_abbr_flag."</td>
							</tr>
					</table>";
		fwrite($logFile, $log_msg);
		fclose($logFile);
	}	
	
	function insert_businesstags_log($parentid,$businesstags,$categorylist,$cat_display_product_flag,$updatedby,$conn_iro,$brandNameflg,$business_abbr_flag,$conn_data)
	{
		$sqlInsertBusinessTagsLog = "INSERT INTO db_iro.tbl_businesstags_log SET
									 parentid 		= '".$parentid."',
									 businesstags 	= '".$businesstags."',
									 categorylist 	= '".$categorylist."',
									 cat_display_product_flag = '".$cat_display_product_flag."',
									 updatedby 		= '".$updatedby."',
									 updatedOn		= '".date("Y-m-d H:i:s")."',
									 brandName_flag = '".$brandNameflg."',
									 business_abbr_flag = '".$business_abbr_flag."'  ";
		$resInsertBusinessTagsLog = $conn_data->query_sql($sqlInsertBusinessTagsLog);
	}
	
	function insertPriceRangeLog($parentid,$price_range_arr,$price_range_val,$conn_iro)
	{
		$price_range_str = '';
		if(count($price_range_arr)>0){
			$price_range_str = implode(",",$price_range_arr);
		}
		$sqlInsertPriceRangeLog = "INSERT INTO tbl_pricerange_log SET
									parentid 		= '".$parentid."',
									price_range_str = '".$price_range_str."',
									price_range_val = '".$price_range_val."',
									updatedOn		= '".date("Y-m-d H:i:s")."'";
		$resInsertPriceRangeLog = $conn_iro->query_sql($sqlInsertPriceRangeLog);
	}
	function logssphinxquery($parentid,$query)
	{
		return ; // stoping making log
		$log_msg='';
		$log_path = APP_PATH.'logs/sphinxquery/';
		$sNamePrefix= $log_path;
		// fetch directory for the file
		$pathToLog = dirname($sNamePrefix); 
		//unlink($log_path);
		if(!file_exists($log_path)) {
			mkdir($log_path, 0755, true);;
		}
		/*$file_n=$sNamePrefix.$contractid.".txt"; */
		$file_n=$sNamePrefix.$parentid.".html";
		$command	= "chown apache:apache ". $file_n;
		$output = shell_exec($command);
		//echo $file_n;
		// Set this to whatever location the log file should reside at.
		$logFile = fopen($file_n, 'a+');

			
		//$userID= $_SESSION['ucode'];
		/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
		$pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
		$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
						<tr valign='top'>
							<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
							<td style='width:15%; border:1px solid #669966'>query:".$query."</td>							
							</tr>
					</table>";
		fwrite($logFile, $log_msg);
		fclose($logFile);
	}
	function getTrendingRestaurantsCatid($conn_iro)
	{
		$trending_rest_catid = 0;
		$sqlTrendingRestaurants = "SELECT catid FROM d_jds.tbl_categorymaster_generalinfo WHERE national_catid='11263749' LIMIT 1";
		$resTrendingRestaurants = $conn_iro->query_sql($sqlTrendingRestaurants);
		if($resTrendingRestaurants && mysql_num_rows($resTrendingRestaurants)>0)
		{
			$row_trending_rest = mysql_fetch_assoc($resTrendingRestaurants);
			$trending_rest_catid = trim($row_trending_rest['catid']);
			$trending_rest_catid = intval($trending_rest_catid);
		}
		return $trending_rest_catid;
	}
	function companyWithArea($c,$a)
	{
		$a = trim($a);
		$c = @preg_replace(array('/\bE\b/i','/\bW\b$/i'),array('East','West'),$c);
		if(!empty($a) && !preg_match("/\b$a/i",$c))
		{
			$c = $c.' '.$a;
		}
		return $c;
	}
	function getSingular($str='')
	{
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','glasses'=>'glass','mattresses'=>'mattress','mattress'=>'mattress','watches'=>'watch','access'=>'access');
		$r = array('ss'=>'ss','os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
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
		return (!empty($s)) ? implode(' ',$s) : $str;
	}
	function applyIgnore($str)
	{
		$ig_strt = array('/^\bthe\b/i','/^\bdr\.\s/i','/^\bdr\b/i','/^\bprof\.\s/i','/^\bprof\b/i','/^\band\b/i','/^\bbe\b/i');
		$ig_last = array('/\bpvt\.\s/i','/\bltd\.\s/i','/\bpvt\b/i','/\bltd\b/i','/\bprivate\b/i','/\blimited\b/i','/\brestaurants$\b/i','/\brestaurant\b$/i',
			  '/\bhotel\b$/i','/\bhotels\b$/i');
		$s = $str;
		$s = preg_replace($ig_strt,'',$s);
		$s = preg_replace($ig_last,'',trim($s));
		$s = preg_replace('/[\s+]+/',' ',trim($s));
		return (strlen($s)<=1) ? $str : $s;
	}
	function sanitize($str,$case='')
	{
		$str = preg_replace("/[@&\-\.,_]+/",' ',$str);
		if($case)
			$str = preg_replace("/[^a-zA-Z0-9\s\(\)]+/",'',$str);
		else
			$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);

		$str = preg_replace('/\\\+/i','',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim($str);
	}
	function braces_content_removal($str,$i=0)
	{
		//echo '<hr>';
		//echo '<br>str->'.$str;
		$sflag =$eflag = false;
		$start=$end=0;
		if(stristr($str,'(') || stristr($str,')'))
		{
			if(preg_match('/\(/',$str))
			{
				$sflag = true;
				//echo '<br>Start----->'.
				$start = strpos($str,'(');
			}
			
			if(preg_match('/\)/',$str))
			{
				$eflag = true;
				//echo '<br>End----->'.
				$end = strpos($str,')');
			}
			if(!$eflag)
			{
				//echo '<br>new end->'.
				$end =$start;
				//$end = strlen($str);
			}
			if(!$sflag)
			{
				//echo '<br>new start->'.
				$start = $end;
			}

			if($end < $start)
			{
				//echo '<br>new start(end < start)->'.
				$start = 0;
			}

			//echo '<br>Start->'.$start;
			//echo '<br>End->'.$end;
			//echo '<br>output->'.
			$str = substr_replace($str, '', $start, ($end-$start)+1);
			$str = $this->braces_content_removal($str,++$i);
			return trim($str);
		}
		else
		{
			$str = preg_replace('/\s\s+/',' ',trim($str));
			return trim($str);
		}
	}

	function concatsingle($str)
	{
		$new = '';
		$tmp = explode(' ',$str);
		for($i=0;$i<count($tmp);$i++)
		{
			if(strlen($tmp[$i])==1)
				$new .=$tmp[$i];
			else 
				$new .=' '.$tmp[$i].' ';
		}
		$new = trim(preg_replace('[\s\s+]',' ',$new));
		return $new;
	}
	function get_area_compname_merged_fields($companyname,$area){
		$result_arr			 = array();
		$company_filter_word = array('the', 'dr', 'dr.', 'prof', 'prof.','pvt', 'ltd','pvt.', 'ltd.','private','limited');
		$filter_word 	     = array('pvt', 'ltd');
		$pattern 			 = "/(\(.*\))/";
		
		preg_match($pattern, $companyname, $matches);
		
		$compname       = trim($companyname);
		$areaname 		= trim($area);
		$compname 		= str_replace($matches[0], "", $companyname);
		$compname 		= trim($companyname);
		
		$compname_filtered = preg_replace('#\(.*\)#', '', $companyname);
		$compname_filtered = trim($compname_filtered);
		
		$compname_search = $this->get_input_cleaned($this->get_compname_area_merged($compname_filtered, ''));		
		$compname_search_ignore = $this->get_input_cleaned($this->get_compname_area_merged($compname_filtered, '', false, $filter_word, false, $company_filter_word));
		$compname_area_search = $this->get_input_cleaned($this->get_compname_area_merged($compname_filtered, $areaname, false, false, true));		
		$compname_area_search_ignore = $this->get_input_cleaned($this->get_compname_area_merged($compname_filtered, $areaname, false, $filter_word, true, $company_filter_word));
		
		$result_arr['compname_search'] 			   = $compname_search;
		$result_arr['compname_search_ignore']	   = $compname_search_ignore;
		$result_arr['compname_area_search']		   = $compname_area_search;
		$result_arr['compname_area_search_ignore'] = $compname_area_search_ignore;
		return $result_arr;
	}
	function get_compname_area_merged($compname, $areaname, $remove_space = false, $filter_word = false, $east_west = false, $ignore_first_word = false){
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
		if($this->word_match($compname, $areaname) OR $this->word_match($compname, $this->word_replace($areaname, 'West', 'W')) OR $this->word_match($compname, $this->word_replace($areaname, 'East', 'E')))
		{
			$result = $compname;
		}
		else if($east_west === true)
		{
			$result = $compname;
			if($areaname != '')
			{
				if($this->ends_with($areaname, 'West') || $this->ends_with($areaname, 'East'))
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
				$result = $this->word_replace($result, $fw, '');
			}
		}
		
		if($remove_space === true)
		{
			$result = preg_replace('/\s*/m', '', $result);
		}
		return trim($result);
	}
	function word_match($haystack, $pattern){
		$pattern = str_replace('/', '\/', $pattern);
		$pattern = preg_quote($pattern);
		$res = @preg_match('/\b' . $pattern . '\b/i', $haystack) > 0;
		return $res;
	}
	function word_replace($haystack, $pattern, $replace){
		$pattern = str_replace('/', '\/', $pattern);
		$pattern = preg_quote($pattern);
		$res_word_replace = trim(preg_replace('/\b' . $pattern . '\b/i', $replace, $haystack));
		return $res_word_replace;
		
		
	}
	function ends_with($haystack, $needle){
		//echo "<br>ends_with--haystack--".$haystack."--needle--".$needle;
		$haystack = strtolower($haystack);
		$needle = strtolower($needle);
		$result_str = substr($haystack, strlen($haystack) - strlen($needle)) == $needle;
		//echo "<br>result_str:-".$result_str;
		return  $result_str;
		
	}
	function get_input_cleaned($input){
		$input = preg_replace("/\\$|,|@|#|~|`|\%|\*|\^|\&|\(|\)|\+|\=|\[|\-|\_|\]|\[|\}|\{|\;|\:|\"|\<|\>|\?|\||\\\|\\!|\/|\./", ' ', $input);
		$input = preg_replace('/\s\s+/', ' ', $input);
		$input = str_ireplace('\'', '', $input);
		$input = preg_replace('/\s\s+/', ' ', $input);
		$input = trim($input);
		return $input;
	}
	
}
	
?>
