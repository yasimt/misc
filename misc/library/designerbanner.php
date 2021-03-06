<?php

class DesignerBanner{


public function insertOnApproval($conn_fin,$parentid,$version,$campaignid,$companyname,$datacity)
{	
	$checksql="select group_concat(version) as versions from tbl_banner_approval where parentid='".$parentid."' and campaignid='".$campaignid."'";
	
	$checksql=$conn_fin->query_sql($checksql); 
	$renewflag=0;
		while($row=$conn_fin->fetchData($checksql))
		{
			
			$versionsql= $row['versions'];
			if($row['versions'] != null && $row['versions'] != ''){
				$versionarr=explode(',', $versionsql);
				if (in_array($version, $versionarr)) {
				$renewflag=0;	
				  
				} else {
				 
				 $renewflag=1;
				}
			}

							
		}
		if($renewflag==0){

			$checksql="select parentid from tbl_banner_image_olddata where parentid='".$parentid."'";
			$checksql=$conn_fin->query_sql($checksql); 
			if($conn_fin->numRows($checksql)>0)
				 $renewflag=1;
		}
	
	$checksql_campaigntype = "SELECT duration from tbl_companymaster_finance WHERE parentid= '".$parentid."' AND campaignid IN ('5','13')";
	$checkres_campaigntype = $conn_fin->query_sql($checksql_campaigntype); 
	echo $conn_fin->numRows($checkres_campaigntype);
	
	if($conn_fin->numRows($checkres_campaigntype) > 0){
		$checkrow_campaigntype = mysql_fetch_assoc($checkres_campaigntype);
		if($checkrow_campaigntype['duration'] == 365){
			$banner_type = "Banner-Standard";
		}
		else if($checkrow_campaigntype['duration'] == 1095){
			$banner_type = "Banner-Classic";
		}
		else if($checkrow_campaigntype['duration'] == 3650){
			$banner_type = "Banner-Premium";
		}
	}
	else{
		$banner_type = "-";
	}
	if($renewflag==1){
		$sql = "Insert into tbl_banner_approval SET
		parentid	     = '".$parentid."',
		version 	     = '".$version."',
		campaignid 	     = '".$campaignid."',
		companyname      = '".addslashes(stripcslashes($companyname))."',
		campaign_name    = '".addslashes(stripcslashes($banner_type))."',
		entry_date 	     = '".date('Y-m-d H:i:s')."',
		data_city 	     = '".addslashes(stripcslashes($datacity))."',
		fin_approveddate = '".date('Y-m-d H:i:s')."',
		approval_status  = 15
		
		ON DUPLICATE KEY UPDATE
		
		companyname      = '".addslashes(stripcslashes($companyname))."',	
		campaign_name    = '".addslashes(stripcslashes($banner_type))."',
		data_city 	     = '".addslashes(stripcslashes($datacity))."'";
		$conn_fin->query_sql($sql);  
	}
	else
	{
		$sql = "Insert into tbl_banner_approval SET
		parentid	     = '".$parentid."',
		version 	     = '".$version."',
		campaignid 	     = '".$campaignid."',
		companyname      = '".addslashes(stripcslashes($companyname))."',
		campaign_name    = '".addslashes(stripcslashes($banner_type))."',
		entry_date 	     = '".date('Y-m-d H:i:s')."',
		data_city 	     = '".addslashes(stripcslashes($datacity))."',
		fin_approveddate = '".date('Y-m-d H:i:s')."',
		approval_status  = 0
		
		ON DUPLICATE KEY UPDATE
		
		companyname      = '".addslashes(stripcslashes($companyname))."',	
		campaign_name    = '".addslashes(stripcslashes($banner_type))."',
		data_city 	     = '".addslashes(stripcslashes($datacity))."'";
		$conn_fin->query_sql($sql);  
	}
	
}

function getContractsCategory($conn_finance,$parentid,$tbl_bidcatdetails, $campaignid)
{
	$sql = "SELECT GROUP_CONCAT(DISTINCT catid) AS catids FROM ".$tbl_bidcatdetails." WHERE  parentid='".$parentid."' AND campaignid='".$campaignid."' ";
	$res = $conn_finance->query_sql($sql);
	$row = mysql_fetch_assoc($res);
	if($row['catids'])
	{
		return $row['catids'];
	}
}

function getPhoneSearchCategories($conn_fin,$parentid)
{
	$resultarr = array();
	$phonesearch_fin = "select campaignid from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and  campaignid in (1,2) and balance>0 "; // checking package and pdg categories
	$phonesearch_fin_rs = $conn_fin->query_sql($phonesearch_fin);
	$ContractsCategory= "";
	if($conn_fin->numRows($phonesearch_fin_rs))
	{
		while($phonesearch_fin_arr=$conn_fin->fetchData($phonesearch_fin_rs))
		{
			if($phonesearch_fin_arr['campaignid']==1)
			{
				$ContractsCategorytemp = $this->getContractsCategory($conn_fin,$parentid,'tbl_bidding_details', 1);				
			}
			
			if($phonesearch_fin_arr['campaignid']==2)
			{
				$ContractsCategorytemp = $this->getContractsCategory($conn_fin,$parentid,'tbl_bidding_details', 2);
			}			

			$ContractsCategory = $ContractsCategory.",".$ContractsCategorytemp;
			//echo "<br>ContractsCategory--".$ContractsCategory;
		}
	}

	$ContractsCategoryArr = explode(",",$ContractsCategory);
	$ContractsCategoryArr = array_unique($ContractsCategoryArr);
	$ContractsCategoryArr = array_filter($ContractsCategoryArr);
	$ContractsCategory = implode(",",$ContractsCategoryArr);
	return $ContractsCategory;
}


function addBannerFromBackend($conn_local,$bannerObj,$catid_arr,$parentid,$finrsarray)
{
	//echo "<pre>"; print_r($finrsarray);
		if(count($catid_arr) > 0){
			$catarr_str	= implode(",",$catid_arr);				
			$sql_cat	= "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN($catarr_str)";
			$qry_cat	= $conn_local->query_sql($sql_cat);
			if($qry_cat && mysql_num_rows($qry_cat) > 0){
				$doctor_package = 1;
				while($row_cat	= mysql_fetch_assoc($qry_cat)){
					$catdetails_arr[$row_cat['catid']]['catname']	= $row_cat['category_name'];
					$catdetails_arr[$row_cat['catid']]['nat_cat']	= $row_cat['national_catid'];
				}
			}
		}
		
		if(count($catdetails_arr) > 0){
			
			$insert_arr = array();
			$i			= 0; 
			$count_cat	= count($catdetails_arr);
			$catids		= array_keys($catdetails_arr);
			$parents	= array_keys($bannerObj->find_top_parents(implode(",",$catids)));
			if(count($parents) == 2 && (in_array("Parentless",$parents) || in_array("parentless",$parents))){
				foreach($parents as $parnm){
					if($parnm!='Parentless'){
						$final_parent	= $parnm;
					}
				}
			}else if(count($parents) == 1 || count($parents) > 2 || (count($parents)  ==  2 && !in_array("Parentless",$parents) && !in_array("parentless",$parents))){
				$final_parent	= $parents['0'];
			}
			
			foreach($catdetails_arr as $catid => $catname){


				if( isset($finrsarray['5']['balance']) && $finrsarray['5']['balance']>0)
				{

				$tbl_comp_bannersql = "INSERT INTO tbl_comp_banner set
						parentid='".$parentid."',
						catid='".$catid."',
						campaign_type=4,						
						cat_name='".addslashes($catname['catname'])."',						
						banner_camp=2,
						national_catid='".$catname['nat_cat']."',
						update_date= now(),
						tenure = 365,						
						budget=".$finrsarray['5']['budget'].",
						start_date= '".$finrsarray['5']['start_date']."',
						end_date= '".$finrsarray['5']['end_date']."',
						variable_budget	= ".(4/$count_cat).",		
						campaign_name='cat_banner',
						iscalculated=1,
						inventory=0,				
						parentname='".addslashes($final_parent)."'
						ON DUPLICATE KEY UPDATE
						cat_name='".addslashes($catname['catname'])."',						
						banner_camp=2,
						national_catid='".$catname['nat_cat']."',
						update_date= now(),
						tenure = 365,						
						budget=".$finrsarray['5']['budget'].",
						start_date= '".$finrsarray['5']['start_date']."',
						end_date= '".$finrsarray['5']['end_date']."',
						variable_budget	= ".(4/$count_cat).",		
						campaign_name='cat_banner',
						iscalculated=1,
						inventory=0,				
						parentname='".addslashes($final_parent)."'";

						$conn_local->query_sql($tbl_comp_bannersql);
						//echo "<br>".$tbl_comp_bannersql;
				}


			if(isset($finrsarray['13']['balance']) && $finrsarray['13']['balance']>0)
			{
				
				$inv_arr	= $bannerObj->getAvail(array($catid));
				if($inv_arr[$catid] > 0)
				{

					$tbl_catsponsql = "INSERT INTO tbl_catspon set
						parentid='".$parentid."',
						catid = '".$catid."',
						campaign_type  	= 1,
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$finrsarray['13']['budget'].",
						variable_budget	= ".(4/$count_cat).",
						update_date= now(),
						start_date      = '".$finrsarray['13']['start_date']."',
						end_date		= '".$finrsarray['13']['end_date']."',						
						campaign_name  	= 'catspon',
						parentname 		= '".addslashes($final_parent)."'
						ON DUPLICATE KEY UPDATE
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$finrsarray['13']['budget'].",
						variable_budget	= ".(4/$count_cat).",
						update_date= now(),
						start_date      = '".$finrsarray['13']['start_date']."',
						end_date		= '".$finrsarray['13']['end_date']."',						
						campaign_name  	= 'catspon',
						parentname 		= '".addslashes($final_parent)."'";
					$conn_local->query_sql($tbl_catsponsql);
					//echo "<br>--".$tbl_catsponsql;
				}
			}
				$i++;
			}
		}
}

function addBannerFromBackend_multiparantage($conn_local,$bannerObj,$catid_arr,$parentid,$finrsarray,$parantagecatname)
{
	//echo "<pre>"; print_r($finrsarray);
		if(count($catid_arr) > 0){
			$catarr_str	= implode(",",$catid_arr);				
			$sql_cat	= "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN($catarr_str)";
			$qry_cat	= $conn_local->query_sql($sql_cat);
			if($qry_cat && mysql_num_rows($qry_cat) > 0){
				$doctor_package = 1;
				while($row_cat	= mysql_fetch_assoc($qry_cat)){
					$catdetails_arr[$row_cat['catid']]['catname']	= $row_cat['category_name'];
					$catdetails_arr[$row_cat['catid']]['nat_cat']	= $row_cat['national_catid'];
				}
			}
		}
		
		if(count($catdetails_arr) > 0){

			// since may be different parantage is already present so remove it before insert

			$del=  " delete from tbl_comp_banner where parentid='".$parentid."'";
			$conn_local->query_Sql($del);
			$del=  " delete from tbl_catspon where parentid='".$parentid."'";
			$conn_local->query_Sql($del);
			
			$insert_arr = array();
			$i			= 0; 
			$count_cat	= count($catdetails_arr);
			$catids		= array_keys($catdetails_arr);			
			
			foreach($catdetails_arr as $catid => $catname){				


				if( isset($finrsarray['5']['balance']) && $finrsarray['5']['balance']>0)
				{
					
					$tbl_comp_bannersql = "INSERT INTO tbl_comp_banner set
						parentid='".$parentid."',
						catid='".$catid."',
						campaign_type=4,						
						cat_name='".addslashes($catname['catname'])."',						
						banner_camp=2,
						national_catid='".$catname['nat_cat']."',
						update_date= now(),
						tenure = 365,						
						budget=".$finrsarray['5']['budget'].",
						start_date= '".$finrsarray['5']['start_date']."',
						end_date= '".$finrsarray['5']['end_date']."',
						variable_budget	= ".(4/$count_cat).",		
						campaign_name='cat_banner',
						iscalculated=1,
						inventory=0,				
						parentname='".addslashes($parantagecatname)."'
						ON DUPLICATE KEY UPDATE
						cat_name='".addslashes($catname['catname'])."',						
						banner_camp=2,
						national_catid='".$catname['nat_cat']."',
						update_date= now(),
						tenure = 365,						
						budget=".$finrsarray['5']['budget'].",
						start_date= '".$finrsarray['5']['start_date']."',
						end_date= '".$finrsarray['5']['end_date']."',
						variable_budget	= ".(4/$count_cat).",		
						campaign_name='cat_banner',
						iscalculated=1,
						inventory=0,				
						parentname='".addslashes($parantagecatname)."'";

				
					$conn_local->query_sql($tbl_comp_bannersql);
					//echo "<br>".$tbl_comp_bannersql;
				}
				

				if(isset($finrsarray['13']['balance']) && $finrsarray['13']['balance']>0)
				{
					
					$inv_arr	= $bannerObj->getAvail(array($catid));
					if($inv_arr[$catid] > 0){

						$tbl_catsponsql = "INSERT INTO tbl_catspon set
						parentid='".$parentid."',
						catid = '".$catid."',
						campaign_type  	= 1,
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$finrsarray['13']['budget'].",
						variable_budget	= ".(4/$count_cat).",
						update_date= now(),
						start_date      = '".$finrsarray['13']['start_date']."',
						end_date		= '".$finrsarray['13']['end_date']."',						
						campaign_name  	= 'catspon',
						parentname 		= '".addslashes($parantagecatname)."'
						ON DUPLICATE KEY UPDATE
						cat_name = '".addslashes($catname['catname'])."',
						national_catid	= ".$catname['nat_cat'].",
						iscalculated 	= 1,
						banner_camp  	= 2,
						tenure 		 	= 365,
						budget=".$finrsarray['13']['budget'].",
						variable_budget	= ".(4/$count_cat).",
						update_date= now(),
						start_date      = '".$finrsarray['13']['start_date']."',
						end_date		= '".$finrsarray['13']['end_date']."',						
						campaign_name  	= 'catspon',
						parentname 		= '".addslashes($parantagecatname)."'";

						$conn_local->query_sql($tbl_catsponsql);
					//echo "<br>--".$tbl_catsponsql;
					}

				}
				$i++;
			}
		}
}

function insertCategoryFromreport($conn_fin,$conn_local,$cat_bannerobj,$parentid)
{
	$tablename['5']  = 'tbl_comp_banner';
	$tablename['13'] = 'tbl_catspon';
	$finsql = "select * from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and (budget=4 or budget=8 or budget=12)  and balance>0 ";
	$finrs = $conn_fin->query_sql($finsql);
	if($conn_fin->numRows($finrs)==0)
	{
		return 0; // it is not a free banner
	}else
	{		
		
		$PhoneSearchCategories = $this->getPhoneSearchCategories($conn_fin,$parentid);
		// we have to check they categories are of same parantage or not if it is not of same parantage then we will show messagge of different parangtage

		if(strlen(trim($PhoneSearchCategories))<=1)
		return 1;// no phone search categories found for banner contract so can not add category on banner campaign 
		
		
		$parent_cat =  $cat_bannerobj->find_top_parents($PhoneSearchCategories);			
		$parent_cat	= array_filter($parent_cat);
		//echo"<pre>---cat_banner"; print_r($parent_cat);
		if(count($parent_cat)>1)
		{

					if(count($parent_cat) > 1){
						
		?>
						<form name='banner_form' id='form_div' action='bannerCategorystatus.php' method='post'>
						<div class='search_result'>							
													<b> This contract contains categories across multiple parentages </b></font></div>
							<div class='search_content'>								
								<div class='search_content_sub'>PARENT CATEGORY</div>
								<div class='search_content_sub select_catname'>CATEGORY NAME</div>
								<div id='clear'></div>
							</div>
		<?php
							foreach($parent_cat as $parentname => $parentname_arr){
								$catids 	= '';
								$catnames	= array();
								foreach($parentname_arr as $det_arr){
									$catids		.= $det_arr['catid'].",";
									$catnames[]	 = $det_arr['catname'];
								}
								$catnames	= array_unique($catnames);
		?><br>
								<div class='search_content <?php echo ($parentname!='Parentless')?'contectVis':'';?>' <?php echo $style;?>>
									<div class='search_content_text select_catid tcenter'>
		<?php							
									if($parentname != 'Parentless'){
		?>
										<input type='radio' name='parent' class='parent_check' value='<?php echo $parentname."~".trim($catids,",");?>'>
		<?php						
									}else{
										echo "*";
		?>
										<input type='hidden' name='parentless' value='<?php echo trim($catids,",")?>' />
		<?php
									}
		?>
									</div>
									<div class='search_content_text'><?php echo ($parentname!='Parentless')?$parentname:"Categories without parent"?></div>
									<div class='search_content_text select_catname'>
		<?php
										$i = 0;
										foreach($catnames as $values){
											echo ++$i.".  ".$values."<br>";
										}
										echo "<br>";
		?>
									</div>
									<div id='clear'></div>
								</div>
		<?php
							}		?>

							<div class='search_content sub_cat'>
							<input type='hidden' name='parentidvalnew' id='parentidvalnew' value='<?php echo $parentid?>' />								
							<input type='submit' name='submit_parent' id='submit_parent' value='SELECT PARENT'/>
							</div>
							
						</div>
		<?php	
					}
			echo "</font></div>";
			return 2;
		}

		$returnstatus=0;//
		// 18 - category present in both campaign we will sum campaignid in returnstatus
		$catid_arr= explode(",",$PhoneSearchCategories);

		while($fintemparr = $conn_fin->fetchData($finrs))
		{
			//print_r($fintemparr);
			$finrsarray[$fintemparr['campaignid']]=$fintemparr;
		}
		
		$this->addBannerFromBackend($conn_local,$cat_bannerobj,$catid_arr,$parentid,$finrsarray);
		
		// checking category is populated properly or not
		$tbl_comp_banner = "SELECT EXISTS(select parentid from tbl_comp_banner where parentid='".$parentid."' ) as result ";
		$tbl_comp_banner_rs = $conn_local->query_sql($tbl_comp_banner);
		$tbl_comp_banner_arr = $conn_local->fetchData($tbl_comp_banner_rs);
		if($tbl_comp_banner_arr['result']==0)
		return -5;

		// checking category is populated properly or not
		$tbl_catspon_sql = "SELECT EXISTS(select parentid from tbl_catspon where parentid='".$parentid."' ) as result ";
		$tbl_catspon_rs = $conn_local->query_sql($tbl_catspon_sql);
		$tbl_catspon_arr = $conn_local->fetchData($tbl_catspon_rs);
		if($tbl_catspon_arr['result']==0)
		return -13;

		// we reached at last so evry thing is populated porperly so we will return 18 (sum of both campaign)
		return 18;
	}	
}


function insertCategoryFromreportMultiparantage($conn_fin,$conn_local,$cat_bannerobj,$parentid)
{
	$tablename['5']  = 'tbl_comp_banner';
	$tablename['13'] = 'tbl_catspon';
	$finsql = "select * from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and (budget=4 or budget=8 or budget=12)  and balance>0 ";
	$finrs = $conn_fin->query_sql($finsql);
	if($conn_fin->numRows($finrs)==0)
	{
		return 0; // it is not a free banner
	}else
	{	
		//$_POST['parent']  Health & Beauty ~ 343269,116944
		$parantagearr= explode('~',$_POST['parent']);

		$parantagecatname = trim($parantagearr[0]);
		
		$catid_arr = explode(',',$parantagearr[1]);
		
		while($fintemparr = $conn_fin->fetchData($finrs))
		{			
			$finrsarray[$fintemparr['campaignid']]=$fintemparr;
		}
		
		$this->addBannerFromBackend_multiparantage($conn_local,$cat_bannerobj,$catid_arr,$parentid,$finrsarray,$parantagecatname);
		
		// checking category is populated properly or not
		$tbl_comp_banner = "SELECT EXISTS(select parentid from tbl_comp_banner where parentid='".$parentid."' ) as result ";
		$tbl_comp_banner_rs = $conn_local->query_sql($tbl_comp_banner);
		$tbl_comp_banner_arr = $conn_local->fetchData($tbl_comp_banner_rs);
		if($tbl_comp_banner_arr['result']==0)
		return -5;

		// checking category is populated properly or not
		$tbl_catspon_sql = "SELECT EXISTS(select parentid from tbl_catspon where parentid='".$parentid."' ) as result ";
		$tbl_catspon_rs = $conn_local->query_sql($tbl_catspon_sql);
		$tbl_catspon_arr = $conn_local->fetchData($tbl_catspon_rs);
		if($tbl_catspon_arr['result']==0)
		return -13;

		// we reached at last so evry thing is populated porperly so we will return 18 (sum of both campaign)
		return 18;
	}	
}

function IsValidFreeBannerContract($conn_fin,$parentid)
{
	
	$returnval= 0;	
	//$finsql = "select campaignid from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and (budget=4 or budget=8 or budget=12) and campaignid in (5,13) and balance>0 ";
	$finsql = "select sum(budget) as sumval,data_city from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (5,13) and  balance>0";
	$finrs = $conn_fin->query_sql($finsql);
	if($conn_fin->numRows($finrs))
	{
		$fin_arr = $conn_fin->fetchData($finrs);
		
		if($fin_arr['sumval']==8 )
		{
			$returnval= 1; // active banner contract
		}

		if($returnval==1 && defined("REMOTE_CITY_MODULE") && strtolower(DATA_CITY)!=strtolower($fin_arr['data_city']))
		{
			echo "<center><font color='blue' >Current city:-".DATA_CITY." Contract City:-".$fin_arr['data_city']."</font><br></center>";
			$returnval= -1;
		}
	}
	return $returnval;	

}
function IsValidComboBannerContract($conn_iro,$conn_fin,$parentid){
	$returnval= 0;	
	$returnarr=array();
	$version=0;
	$finsql = "select campaignid,balance,budget from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in (5,13) and /*(budget=4 or budget=8 or budget=12) and*/  balance>0";
	$finrs = $conn_fin->query_sql($finsql);
	if($conn_fin->numRows($finrs)>0)
	{
		while($row=$conn_fin->fetchData($finrs)){
			$version=$row['version'];
		}
		$returnval= 1; 
		$categories=$this->getCategoriesForContract($conn_iro,$conn_fin,$parentid,$version);

		if(is_array($categories)){
			$returnarr['catids']=implode(",",$categories);

		}
		else{
			$returnval= 3;
		}
	}
	else
		$returnval= 2; //version issue;
	$returnarr['reason']=$returnval;
	return $returnarr;	
}
function getCategoriesForContract($conn_iro,$conn_fin,$parentid,$version){
	$campaigns='';
	$selpayment_ap="select group_concat(campaignid) as all_cat from payment_apportioning where parentid='".$parentid."' and version='".$version."'";
	$finrs = $conn_fin->query_sql($selpayment_ap);
	if($conn_fin->numRows($finrs)>0)
	{
		while($row=$conn_fin->fetchData($finrs)){
			$campaigns=$row['all_cat'];

		}
	}
	$campaigns=explode(',',$campaigns);

	if(in_array('1', $campaigns) || in_array('2', $campaigns)){
		echo 'asf';
		$getcatdatabidding="SELECT GROUP_CONCAT(catid) AS catids FROM tbl_bidding_details WHERE parentid='".$parentid."' AND VERSION='".$version."'";
		$bidrs = $conn_fin->query_sql($getcatdatabidding);
		if($conn_fin->numRows($bidrs)>0)
		{
			while($bidrow=$conn_fin->fetchData($bidrs)){
				$catids=$bidrow['catids'];
			}
		}
		else{
			$getcatdatabidding="SELECT GROUP_CONCAT(catid) AS catids FROM tbl_bidding_details_expired WHERE parentid='".$parentid."' AND VERSION='".$version."'";
			$bidrs = $conn_fin->query_sql($getcatdatabidding);
			if($conn_fin->numRows($bidrs)>0)
			{
				while($bidrow=$conn_fin->fetchData($bidrs)){
					$catids=$bidrow['catids'];
				}
			}
		}
		$catids=explode(',', $catids);
		if(empty($catids)){
			return 1;
		}
		else{
			return $catids;
		}

	}
	else{
		$getcatextradetails="select catidlineage from tbl_companymaster_extradetails where parentid='".$parentid."'";
		$irors = $conn_iro->query_sql($getcatextradetails);
		if($conn_iro->numRows($irors)>0)
		{
			while($irorow=$conn_iro->fetchData($irors)){
				$catids=$irorow['catidlineage'];
			}
		}
		$catids=str_replace('/', '', $catids);
		$catids=explode(',',$catids);
		if(empty($catids)){
			return 1;
		}
		else{
			return $catids;
		}

	}


}
function insCategoryBackendForCombo($catids,$parentid,$conn_local,$conn_fin,$cat_bannerobj){
		$catid_arr=explode(',', $catids);

		$sql="select * from tbl_companymaster_finance where parentid='".$parentid."' and campaignid in ('13','5')";
		$finrs=$conn_fin->query_sql($sql);
		if($conn_fin->numRows($finrs)>0){
			
			while($fintemparr = $conn_fin->fetchData($finrs))
			{
				$finrsarray[$fintemparr['campaignid']]=$fintemparr;
			}
		}
		$this->addBannerFromBackend($conn_local,$cat_bannerobj,$catid_arr,$parentid,$finrsarray);
		
		// checking category is populated properly or not
		$tbl_comp_banner = "SELECT EXISTS(select parentid from tbl_comp_banner where parentid='".$parentid."' ) as result ";
		$tbl_comp_banner_rs = $conn_local->query_sql($tbl_comp_banner);
		$tbl_comp_banner_arr = $conn_local->fetchData($tbl_comp_banner_rs);
		if($tbl_comp_banner_arr['result']==0)
		return -5;

		// checking category is populated properly or not
		$tbl_catspon_sql = "SELECT EXISTS(select parentid from tbl_catspon where parentid='".$parentid."' ) as result ";
		$tbl_catspon_rs = $conn_local->query_sql($tbl_catspon_sql);
		$tbl_catspon_arr = $conn_local->fetchData($tbl_catspon_rs);
		if($tbl_catspon_arr['result']==0)
		return -13;

		$sqlqry="select companyname,data_city from db_iro.tbl_companymaster_generalinfo where parentid='".$parentid."'";
		$datares=$conn_local->query_sql($sqlqry);
		while($rowdata=$conn_local->fetchData($datares)){
			$companyname=$rowdata['companyname'];
			$datacity=$rowdata['data_city'];
		}
		if( isset($finrsarray['5']['balance']) && $finrsarray['5']['balance']>0)
		{
			$sql = "Insert ignore into tbl_banner_approval SET
			parentid	= '".$parentid."',
			version 	= '".$finrsarray['5']['version']."',
			campaignid 	= '5',
			companyname = '".addslashes(stripcslashes($companyname))."',
			entry_date 	= '".date('Y-m-d H:i:s')."',
			data_city 	= '".addslashes(stripcslashes($datacity))."',
			fin_approveddate = '".date('Y-m-d H:i:s')."',
			approval_status=0
			ON DUPLICATE KEY UPDATE
			companyname = '".addslashes(stripcslashes($companyname))."',	
			data_city 	= '".addslashes(stripcslashes($datacity))."'";
			$conn_fin->query_sql($sql);
		}
		if( isset($finrsarray['13']['balance']) && $finrsarray['13']['balance']>0)
		{
			$sql = "Insert ignore into tbl_banner_approval SET
			parentid	= '".$parentid."',
			version 	= '".$finrsarray['13']['version']."',
			campaignid 	= '13',
			companyname = '".addslashes(stripcslashes($companyname))."',
			entry_date 	= '".date('Y-m-d H:i:s')."',
			data_city 	= '".addslashes(stripcslashes($datacity))."',
			fin_approveddate = '".date('Y-m-d H:i:s')."',
			approval_status=0
			ON DUPLICATE KEY UPDATE
			companyname = '".addslashes(stripcslashes($companyname))."',	
			data_city 	= '".addslashes(stripcslashes($datacity))."'";
			$conn_fin->query_sql($sql);
		}

		// we reached at last so evry thing is populated porperly so we will return 18 (sum of both campaign)
		return 18;
}
function IsValidBannerContract($conn_fin,$parentid,$campaignid)
{
	
	// 0- not financial approved, 1 financial approved but not in tbl_banner_approval , 2 present in both 
	$Reason = 0;
	
	$finsql = "select parentid from db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid='".$campaignid."' and balance>0 ";
	$finrs = $conn_fin->query_sql($finsql);
	if(mysql_num_rows($finrs)==0)
	return 0;
	
	$banersql = "select parentid from db_finance.tbl_banner_approval where parentid='".$parentid."' and campaignid='".$campaignid."'";
	$bannerrs = $conn_fin->query_sql($banersql);
	
	if(mysql_num_rows($bannerrs)==0)
	{
		return 1;
	}else
	{
		return 2;
	}
	
}

 function insertFromreport($conn_fin,$parentid,$campaignid)
 {	
	$finsql ="insert ignore into db_finance.tbl_banner_approval (parentid,campaignid,version,companyname,entry_date,fin_approveddate,data_city,approval_status) 
			(select  parentid,campaignid,version,companyname,start_date as entry_date,start_date as fin_approveddate,data_city, 0 as approval_status from  db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid='".$campaignid."' and balance>0 );";
	$conn_fin->query_sql($finsql);
	$ucode=$_SESSION['ucode'];
	$logfinsql ="insert ignore into db_finance.banner_status_report_log(parentid,companyname,data_city,version,campaignid,usercode)(select  parentid,companyname,data_city,version,campaignid,'$ucode' from  db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid='".$campaignid."' and balance>0 );";
	$conn_fin->query_sql($logfinsql);
 }


function balanceReAdjustment($conn_fin,$parentid,$version)
{
	//echo "<br>parentid---".$parentid ."-------version==".$version;
	$finsql = "select campaignid from tbl_companymaster_finance where parentid='".$parentid."' and version ='".$version."' and balance>1 and campaignid in (5,13) ";
	//echo"<br>". $finsql
	$finrs = $conn_fin->query_sql($finsql);
	
	if(mysql_num_rows($finrs)==0)
	{
		//echo "<br>no balance readjustment";
		return 0; // this is not the case of balance readjustment
	}
	
	while($finArr =  mysql_fetch_assoc($finrs))
	{
		// checking same entry is present or not in tbl_banner_approval 
		$campaignid= $finArr['campaignid'];
		$bannersql = "select parentid from tbl_banner_approval where parentid='".$parentid."' and version ='".$version."' and campaignid='".$campaignid."' ";
		$bannerrs = $conn_fin->query_sql($bannersql);
		
		if(mysql_num_rows($bannerrs)) // the same entry is present in banner table so no need to do any thing 
		{
			//echo "the same entry is present in banner table so no need to do any thing";
			continue;
		
		}else
		{		
			$insertsql ="insert ignore into db_finance.tbl_banner_approval (parentid,campaignid,version,companyname,entry_date,fin_approveddate,data_city,approval_status) 
			(select  parentid,campaignid,version,companyname,start_date as entry_date,start_date as fin_approveddate,data_city, 0 as approval_status from  db_finance.tbl_companymaster_finance where parentid='".$parentid."' and campaignid='".$campaignid."' and balance>0 );";
			//echo"<br>". $insertsql
			$conn_fin->query_sql($insertsql);
		}
		
	}
}
 
	function setClientBannerSpecification($con_iro,$con_idc,$conn_fnc,$parentid,$version)
	{		
		
		if($version%10==1) // read data of cs
		{
			// currently we are not implemnting in cs
		}else
		{
			// fetch data from idc and insert into database		
			
			$selectsql = "select campaignid,specification from tbl_client_banner_specification where parentid= '".$parentid."' ";
			$selectrs =  $con_idc->query_sql($selectsql);
			if(mysql_num_rows($selectrs))
			{
				while($selectrs_arr= mysql_fetch_assoc($selectrs))
				{					
					$sql = "Insert into tbl_client_banner_specification SET
							parentid	= '".$parentid."',							
							campaignid 	= '".$selectrs_arr['campaignid']."',
							specification = '".addslashes(stripcslashes($selectrs_arr['specification']))."'
							ON DUPLICATE KEY UPDATE
							specification = '".addslashes(stripcslashes($selectrs_arr['specification']))."'";
					$con_iro->query_sql($sql);
					
					$updatesql="update tbl_banner_approval set ChangesbyClient='".addslashes(stripcslashes($selectrs_arr['specification']))."' where parentid	= '".$parentid."' and campaignid 	= '".$selectrs_arr['campaignid']."' and version ='".$version."'";
					$conn_fnc->query_sql($updatesql);
				}
			}
		}
	}
	
	function checkfreeBannerminamount($conn_local,$city)
	{
		/*$freebannerdatacity =strtoupper($city);
		if (in_array($freebannerdatacity, array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI','JAIPUR','CHANDIGARH','COIMBATORE')))
		{
			$DATA_CITYstring =  $city;
		}else
		{
			$DATA_CITYstring =  'Remote';
		}
		
		$sql = "select minannualpackval from d_jds.bannercharge where city ='".$DATA_CITYstring."'";
		$rs = $conn_local->query_sql($sql);
		
		$arr = mysql_fetch_assoc($rs);*/

		return BANNER_MIN_FEES;		
	}
	
	function checkEligibleforfreeBanner($conn_fnc,$minannualpackval,$tmpbudget_arr,$REQUESTARR)
	{
		$pdgcontract=0;
		$durationmultiplyingfactor=1;
		$returnflag =0;
		
		$existingsql = "select parentid from tbl_companymaster_finance where parentid='".$_SESSION['parentid']."'  and campaignid in(1,2,5,13)";
		$existingrs = $conn_fnc->query_sql($existingsql);
		if($existingrs && mysql_num_rows($existingrs)>0)
		{
			$returnflag = 0; // by default existing paid contract is not eligible for free banner we will check for package value  as per Raj/ malti -- 
		}
		
		if($tmpbudget_arr['2']['recalculate_flag']=1) // pdg contract 
		{
			$pdgcontract=1;
			
			switch($tmpbudget_arr['2']['duration'])
			{
				case 180:
				$durationmultiplyingfactor=2;
				break;
				case 90:
				$durationmultiplyingfactor=4;
				break;
				case 30:
				$durationmultiplyingfactor=12;
				break;
			}
						
			$totalbudget =  intval($REQUESTARR['package']) * $durationmultiplyingfactor * 1.1236;			
		
		}else
		{
			$totalbudget = intval($REQUESTARR['package']);
		}
				
		if($totalbudget>=$minannualpackval)
		{
			$returnflag = 1;	
		}
		else
		{
			$returnflag = 0;
		}
		
		return $returnflag;
	}
	
	function deleteSkipBannerCategory($conn, $parentid,$campaignid)
	{		
		if(strlen(trim($parentid))<2 ||  $campaignid==null || ($campaignid!=5 && $campaignid!=13))
		return;
		
		if($campaignid==5)
		$tablename='tbl_comp_banner_temp';
		
		if($campaignid==13)
		$tablename='tbl_catspon_temp';		
		
		$sql= "DELETE from ".$tablename." where parentid='".$parentid."' ";		
		$conn->query_sql($sql);		
	}

}



?>
