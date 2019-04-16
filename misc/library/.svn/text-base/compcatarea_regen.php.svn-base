<?php
@set_time_limit(0);
@ini_set("memory_limit", "-1");

# Requisites Check: #
#####################

#	CREATE TABLE tbl_compcatarea_regen_log(id int primary key auto_increment, parentid varchar(60), campaignid varchar(60), message TEXT, `backenduptdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
#	ALTER TABLE tbl_compcatarea_regen_log add index(parentid), add index(campaignid), add index(message(15));
require_once(APP_PATH."library/categoryMaster.php");

class compcatarea_regen
{

	private $parentid,$docid, $module, $userid, $conn_iro, $conn_local, $conn_finance;
	private $compcatarea_gen_called;
	private $nonpaidforcefully;
	private $nonpaidentryforpdg;
	private $purepack_epired;
	private $log_tbl;
	private $ContractsAllCategoryBefore;
	private $paidflag;
	private $Contractdata_city;
	private $compmaster_obj;
	private $compcataretableArr;
	private $catid_colarr;
	private $latitude_value;
	private $longitude_value;
	private $activephonesearch;
	private $companynameval;
	private $dup_groupid_value;
	private $Contractpincode_value;
	private $company_callcnt_rolling_value;
	private $duplicate_check_phonenos_value;
	private $pdgarraylist;
	
	
	//we found cases when wh have entry in companymaster finace against campaign but associated category table(bidcatdetails ) does not have category then we will
	// generate compcatarea nonpaid for that contract
	
	
	
	public function __construct($parentid,$dbarr,$userid){
		$this->parentid		= $parentid;
		$this->userid		= $userid;		
		$this->conn_iro		= new DB($dbarr['DB_IRO']);	
		$this->conn_local	= new DB($dbarr['LOCAL']);	
		$this->conn_finance	= new DB($dbarr['FINANCE']);
		$this->conn_national = new DB($dbarr['DB_NATIONAL']);	
		$this->nonpaidforcefully = 0;
		$this->nonpaidentryforpdg = 0;
		$this->activephonesearch = 0;
		$this->Contractdata_city = "";
		$this->purepack_epired=0; 
		if($userid=="dbbackend")
		{$this->log_tbl =" tbl_compcatarea_regen_log_dbbackend " ; } // backend process log
		else
		{$this->log_tbl =" tbl_compcatarea_regen_log " ; } // application log

		$this->init();
		$this->setdocId();
		
		if(!isset($this->compmaster_obj)){
			$this->compmaster_obj	= new companyMasterClass($this->conn_iro,"",$this->parentid);
		}
		
		
		/*check and update merge categories - start*/
			$this->UpdateCatidlineage();
		/*check and update merge categories - end*/
				
		$this->ContractsAllCategoryBefore =$this->getContractsAllCategory($parentid);

		
		
		$this->compcataretableArr = array('tbl_fp_search','tbl_package_search','tbl_nonpaid_search','tbl_compcatarea_ppc');
		$this->pdgarraylist  = array();
		
	}

	function init()
	{
		$this->catid_colarr['tbl_fp_search']='catid';
		$this->catid_colarr['tbl_package_search']='catid';
		$this->catid_colarr['tbl_nonpaid_search']='catid';
		$this->catid_colarr['tbl_compcatarea_ppc']='bid_catid';
		$this->catid_colarr['tbl_bidcatdetails_lead']='bid_catid';
		$this->catid_colarr['tbl_bidcatdetails_nonpaid']='bid_catid';
	}
		
	function __destruct(){
		$this->updateCategoryCount();
		unset($this->conn_iro);
		unset($this->conn_local);
		unset($this->conn_finance);
	}
	
	
	function UpdateCatidlineage()
	{ 
		
		$fieldstr 		= " replace(catidlineage_search,'/','') as catidlineage, replace(catidlineage_nonpaid,'/','') as catidlineage_nonpaid, tag_catid, tag_catname, data_city";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid = '".$this->parentid."'";
		$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		if($temparr['numrows']>0)
		{
			$result_arr 		 = $temparr['data']['0'];
			
			$catidlineage        = $result_arr[catidlineage];
			$catidlineage_nonpaid= $result_arr[catidlineage_nonpaid];
			
			$tag_catid			 = $result_arr[tag_catid];
			$tag_catname		 = $result_arr[tag_catname];
			
			$catidlineage_Arr = explode(",",$catidlineage);
			$catidlineage_Arr = array_unique($catidlineage_Arr);
			$catidlineage_Arr = array_filter($catidlineage_Arr);
			
			$catidlineage_nonpaid_Arr = explode(",",$catidlineage_nonpaid);
			$catidlineage_nonpaid_Arr = array_unique($catidlineage_nonpaid_Arr);
			$catidlineage_nonpaid_Arr = array_filter($catidlineage_nonpaid_Arr);
			
			$final_catidlineage_Arr = array_merge($catidlineage_Arr,$catidlineage_nonpaid_Arr);
			$final_catidlineage_Arr = array_unique(array_filter($final_catidlineage_Arr));
			
			
			
			/*checking for category merging - start*/
			$cat_param['module']	  = 'cs';
			$cat_param['action']	  = 'getMergeCategory';
			$cat_param['data_city']	  = urlencode($result_arr[data_city]);
			$cat_param['category']    = json_encode($final_catidlineage_Arr);
			$cat_url 	= JDBOX_SERVICES_API."/category_tag.php";
			$cat_res    = json_decode($this->call_curl_post($cat_url,($cat_param)),true);	
			
			
			
			if( $cat_res['error']['code'] == 0 && count($cat_res['error']['res'])>0 )
			{
				
				foreach($cat_res['error']['res'] as $source_catid => $destination_value)
				{
					
					if($tag_catid == $source_catid)
					{
						$tag_catid	    = $destination_value['dest_catid'];
						$tag_catname	= $destination_value['dest_name'];
					}
					
					if(count($catidlineage_Arr)>0)
					{
						foreach($catidlineage_Arr as $cat_idx => $src_catid)
						{
							if($src_catid == $source_catid)
							{
								unset($catidlineage_Arr[$cat_idx]);
								$catidlineage_Arr[] = $destination_value['dest_catid'];
							}
						}
					}
					
					if(count($catidlineage_nonpaid_Arr)>0)
					{
						foreach($catidlineage_nonpaid_Arr as $cat_idx => $src_catid)
						{
							if($src_catid == $source_catid)
							{
								unset($catidlineage_nonpaid_Arr[$cat_idx]);
								$catidlineage_nonpaid_Arr[] = $destination_value['dest_catid'];
							}
						}
					}
					
					if(count($final_catidlineage_Arr)>0)
					{
						foreach($final_catidlineage_Arr as $cat_idx => $src_catid)
						{
							if($src_catid == $source_catid)
							{
								unset($final_catidlineage_Arr[$cat_idx]);
								$final_catidlineage_Arr[] = $destination_value['dest_catid'];
							}
						}
					}
				}
				
				
				
				$catid_parent			=	$this->getRelevantCategories($catidlineage_Arr);
				
				if(count($catid_parent) > 0)
				{
					$catlineage_search_arr  	= array_merge($catid_parent,$catidlineage_Arr);
					$catlineage_search_arr		= array_unique($catlineage_search_arr);
				}
				else
				{
					$catlineage_search_arr 	= $catidlineage_Arr;
					$catlineage_search_arr 	= array_unique($catlineage_search_arr);
				}
				
				if(is_array($catidlineage_nonpaid_Arr) && count($catidlineage_nonpaid_Arr)>0)
				{					
					$final_catlin_srch = array_merge($catidlineage_nonpaid_Arr,$catlineage_search_arr);
					
					$final_nonpaid_catidlineage	= implode('/,/',$catidlineage_nonpaid_Arr);
					$final_nonpaid_catidlineage	= '/'.$final_nonpaid_catidlineage.'/';
				}
				else
				{
					$final_catlin_srch = array_unique(array_filter($catlineage_search_arr));
				}
			
				$catidlineage 			= implode('/,/',$catidlineage_Arr);
				$catidlineage_search  	= implode('/,/',$final_catlin_srch);
			
				
				if($catidlineage != '')
					$catidlineage 		= '/'.$catidlineage.'/';
				
						
				if($catidlineage_search != '')	
					$catidlineage_search = '/'.$catidlineage_search.'/';
					
			 
				$national_catidlineage 				= $this->getNationalCatlineage($catidlineage_Arr);
								
				if(count($final_catlin_srch))
				{
					$national_catidlineage_search 	= $this->getNationalCatlineage($final_catlin_srch);
				}
			
				if(count($catidlineage_Arr)>0)
				{
					$hot_category					= $this->getHotCategory($catidlineage_Arr);
				}
				
				if(count($final_catidlineage_Arr)>0)
				{
					$chain_nat_category					= $this->getChainNatCatid($final_catidlineage_Arr);
				}
				
				
				$sql_upt = "UPDATE tbl_companymaster_extradetails SET catidlineage= '".$catidlineage."',catidlineage_nonpaid= '".$final_nonpaid_catidlineage."',catidlineage_search= '".$catidlineage_search."',national_catidlineage= '".$national_catidlineage."',national_catidlineage_search= '".$national_catidlineage_search."',hotcategory= '".$hot_category."',tag_catid= '".$tag_catid."',tag_catname= '".$tag_catname."',chain_outlet_ncatid= '".$chain_nat_category."' WHERE parentid='".$this->parentid."'";
				$res_upt=$this -> conn_iro->query_sql($sql_upt);
				
				$sql_upt_search = "UPDATE tbl_companymaster_search SET catidlineage_search= '".$catidlineage_search."',national_catidlineage_search= '".$national_catidlineage_search."' WHERE parentid='".$this->parentid."'";
				$res_upt_search = $this -> conn_iro->query_sql($sql_upt_search);
				
			}
			else if(count($final_catidlineage_Arr)>0)
			{
				$chain_nat_category					= $this->getChainNatCatid($final_catidlineage_Arr);
				$sql_upt = "UPDATE tbl_companymaster_extradetails SET chain_outlet_ncatid= '".$chain_nat_category."' WHERE parentid='".$this->parentid."'";
				$res_upt = $this -> conn_iro->query_sql($sql_upt);
			}
			
			
			/*checking for category merging - end*/
			
		
			
		}
	}
	
	// Function to fetch parent categories
	function getRelevantCategories($catidlistarr)
	{	
		$parent_categories_arr = array();
		$catidarray		= null;
		
		if(count($catidlistarr)>0)
		{
			$catidliststr 	= implode(",",$catidlistarr);

			$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid > 0 AND category_name != '' ";
			//$res = parent::execQuery($sql, $this->dbConDjds_slave);
			$res = $this->conn_local->query_sql($sql);
			
			if($res && mysql_num_rows($res))
			{
				$row = mysql_fetch_assoc($res);
				if($row['associate_national_catid'])
				{
					
					$associate_national_catid_arr = explode(',',$row['associate_national_catid']);			
					
					$associate_national_catid_arr = array_unique($associate_national_catid_arr);
					$associate_national_catid_arr = array_filter($associate_national_catid_arr);
					$associate_national_catid_str = implode(",",$associate_national_catid_arr);
					
					// fetching the catid from national_catid and removing original catid
					$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid IN (".$associate_national_catid_str.") and catid NOT IN (".$catidliststr.") AND catid > 0 AND category_name != '' ";
				
					//$res = parent::execQuery($sql, $this->dbConDjds_slave);
					$res = $this->conn_local->query_sql($sql);
					
					if($res && mysql_num_rows($res))
					{
						$row = mysql_fetch_assoc($res);
						if($row['parent_categories'])
						{
							$parent_categories_arr = explode(',',$row['parent_categories']);
							
							$parent_categories_arr = array_unique($parent_categories_arr);
							$parent_categories_arr = array_filter($parent_categories_arr);					
						}
					}			
				}
			}
		}
		return $parent_categories_arr;
	}
	
	
	function getNationalCatlineage($catids_array)
	{
		$catids_array = $this->get_valid_categories($catids_array);
		if(count($catids_array))
		{
			$catid_list = implode("','",$catids_array);
			$sql_national_catids 	= "SELECT catid,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catid_list."')";
			$res_national_catids	= $this->conn_local->query_sql($sql_national_catids);
			
			if($res_national_catids && mysql_num_rows($res_national_catids))
			{
				while($row_national_catids = mysql_fetch_assoc($res_national_catids))
				{
					$arr_national_catids[] = $row_national_catids['national_catid']; 
				}
			}
			
			$national_catids = '';
			
			if (is_array($arr_national_catids) && count($arr_national_catids))
			{			
				$national_catids = implode('/,/', $arr_national_catids);
			
				if (trim($national_catids) != '')
				{
					$national_catids = '/'.$national_catids.'/';
				}
			}

			return $national_catids;
		}
	}
	
	// Function to fetch hotcategory
	function getHotCategory($catids_array)
	{
		$catids_array = $this->get_valid_categories($catids_array);
		if(count($catids_array))
		{
			$catid_list = implode("','",$catids_array);
			$sql_hot_catid 			= "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid IN('".$catid_list."') Order BY callcount DESC LIMIT 1";
						
			//$res_national_catids	=	parent::execQuery($sql_national_catids, $this->dbConDjds_slave);
			$res_hot_catid			=	$this->conn_local->query_sql($sql_hot_catid);
			
			$row_hot_catid			= mysql_fetch_assoc($res_hot_catid);
			$hot_category			= $row_hot_catid['catid'];
			return $hot_category;
		}
	}
	
	function getChainNatCatid($catids_array)
	{
		$catids_array = $this->get_valid_categories($catids_array);
		if(count($catids_array))
		{
			$catid_list 			    = implode("','",$catids_array);
			$sql_chain_catid 			= "SELECT national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN('".$catid_list."') AND miscellaneous_flag&16=16 LIMIT 1";
			$res_chain_catid			=	$this->conn_local->query_sql($sql_chain_catid);
			$row_chain_catid			= mysql_fetch_assoc($res_chain_catid);
			$chain_national_catid		= $row_chain_catid['national_catid'];
			return $chain_national_catid;
		}
	}
	
	
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if((!empty($final_catid)) && (intval($final_catid)>0))
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	
	function updateCategoryCount()
	{		
		$ContractsAllCatidAfter = array();
		$ContractsAllCatidBefore = array();	
				
		$ContractsAllCatidBefore =$this->ContractsAllCategoryBefore;	
		$ContractsAllCatidAfter = $this->getContractsAllCategory($this->parentid);
		
		
		$AllCatidBefore =  array_merge($ContractsAllCatidBefore['nonpaidcatid'],$ContractsAllCatidBefore['paidcatid']);		
		$AllCatidAfter =  array_merge($ContractsAllCatidAfter['nonpaidcatid'],$ContractsAllCatidAfter['paidcatid']);
		
		
		
		$AllCatidtouched = array_merge($AllCatidBefore,$AllCatidAfter);
		$AllCatidtouched = array_unique($AllCatidtouched); // All category touched added or removed
		
		if(count($AllCatidtouched) && defined("REMOTE_CITY_MODULE"))
		{
			#$this->updateCity($ContractsAllCatidBefore,$ContractsAllCatidAfter);
		}
		//$this->updatepaidnonpaidcount($AllCatidtouched); // removing function call 
		$this->incDecpaidnonpaidcount($ContractsAllCatidBefore,$ContractsAllCatidAfter);
		
		if($_SERVER['REMOTE_ADDR']=="172.29.87.117" && 0)
		{die(" die in compcatarea ");}
	}



	function updateCity($ContractsAllCatidBefore,$ContractsAllCatidAfter)
	{	
		return ; //As per pravin kucha's email stopping city update on tbl_category_freetext
		
		//echo "<pre>ContractsAllCatidBefore"; print_r($ContractsAllCatidBefore); echo "<br>ContractsAllCatidAfter";print_r($ContractsAllCatidAfter); echo "</pre>";
			
		if(!defined("REMOTE_CITY_MODULE")) 
		return ; //this process has to run only remote city
		
		$arrkey = array('paidcatid','nonpaidcatid');
		$totalcatidremovedarr 	= array();
		$totalcatidAddedarr 	= array();
		foreach($arrkey as $catidtype)
		{
			$updatecol="";
			$updatesql1="";
			$ContractsAllRemovedCatid = array();
			$ContractsAllAddedCatid = array(); 
			
			$ContractsAllRemovedCatid = array_diff($ContractsAllCatidBefore[$catidtype],$ContractsAllCatidAfter[$catidtype]);	
			$ContractsAllAddedCatid   = array_diff($ContractsAllCatidAfter[$catidtype],$ContractsAllCatidBefore[$catidtype]);
			
			$totalcatidremovedarr = array_merge($totalcatidremovedarr,$ContractsAllRemovedCatid);
			$totalcatidAddedarr  =  array_merge($totalcatidAddedarr , $ContractsAllAddedCatid );
		}
				
		
		if(count($totalcatidremovedarr)) // if category has been removed
		{
			$Catidstr= "";
			$existincatidwithcity = array();
			$Catidstr = implode(",",$totalcatidremovedarr);
			
			$sqlsel= "select group_concat(catid) as bidcatid from(
			select distinct catid from db_iro.tbl_fp_search where catid in (".$Catidstr.") AND data_city ='".$this->Contractdata_city."'
			union
			select distinct bid_catid  as catid from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.") AND data_city ='".$this->Contractdata_city."'
			union			
			select distinct catid from db_iro.tbl_package_search where catid in (".$Catidstr.") AND data_city ='".$this->Contractdata_city."'
			union
			select distinct catid from db_iro.tbl_nonpaid_search where catid in (".$Catidstr.") AND data_city ='".$this->Contractdata_city."' ) a " ;
			$sqlres = $this->conn_local->query_sql($sqlsel);
			
			if( $sqlres && mysql_num_rows($sqlres))
			{
				$rstarr = mysql_fetch_assoc($sqlres) ;
				
				if($rstarr['bidcatid'])
				{
					$existincatidwithcity = explode(",",$rstarr['bidcatid']);
				}				
			}
			
			// we have removed catid list and we have list where city is present . so we take difference and we will remove city from remainig remove catid
			$citytoremovearr= array();
			$citytoremovearr = array_diff($totalcatidremovedarr,$existincatidwithcity);
			$citytoremovearr = array_unique($citytoremovearr);
			$citytoremovearr = array_filter($citytoremovearr);
			
			
			if(count($citytoremovearr))
			{
				$arraytoremovecitystr = implode(",",$citytoremovearr);
				$update = "update tbl_category_freetext
				 SET city = trim(',' from REPLACE (concat(',',city,','), ',".$this->Contractdata_city.",' , ',' ))
				 WHERE catid in (".$arraytoremovecitystr.")";
				 $catidrs = $this->conn_local->query_sql($update);
			}
		}
		
		
		if(count($totalcatidAddedarr)) // if category has been added
		{
			$Catidstr= "";
			$Catidstr = implode(",",$totalcatidAddedarr);
			//$sql = "select group_concat(distinct catid) as catidlst from tbl_category_freetext where catid in (".$Catidstr.") AND match(city) against('".$this->Contractdata_city."');";
			
			$sql = "SELECT group_concat(distinct catid) as catidlst  FROM
			(
			SELECT distinct catid,city  from d_jds.tbl_category_freetext
			where catid in (".$Catidstr.") 
			) a
			WHERE MATCH(city) AGAINST ('\"".$this->Contractdata_city."\"' in boolean MODE);";
		
			$catidrs = $this->conn_local->query_sql($sql);
			
			$existincatidwithcity = array();			
			if($catidrs && mysql_num_rows($catidrs))
			{
				$catidarraylist = mysql_fetch_assoc($catidrs);
				if($catidarraylist['catidlst'])
				{
					$existincatidwithcity = explode(",",$catidarraylist['catidlst']);					
				}
				
			}
			// we have list of added catid as well as caid which has city present . so their difference is our catid on which we have to append city 
			
			$arraytoappendcity = array_diff($totalcatidAddedarr,$existincatidwithcity);
			$arraytoappendcity = array_unique($arraytoappendcity);
			$arraytoappendcity = array_filter($arraytoappendcity);
			
			if(count($arraytoappendcity))
			{
				$arraytoappendcitystr = implode(",",$arraytoappendcity);
				$update = "update tbl_category_freetext SET city = trim(',' from CONCAT( ifnull(city,''),',".$this->Contractdata_city."')) WHERE catid in (".$arraytoappendcitystr.")";
				$catidrs = $this->conn_local->query_sql($update);
			}
		}
		
		/*
		$updatecity = "update tbl_category_freetext a join (
		select bid_catid as catid,replace(trim(',' from GROUP_CONCAT(data_city ORDER BY data_city)),' ','') as city from (
		select distinct bid_catid, data_city from db_iro.tbl_fp_search where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_package_search where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_package_search where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid,data_city from db_iro.tbl_nonpaid_search where bid_catid in (".$Catidstr.")
		)a GROUP BY bid_catid ) b on a.catid=b.catid set a.city=b.city";
		$this -> conn_local->query_sql($updatecity);
		*/
	}


	function incDecpaidnonpaidcount($ContractsAllCatidBefore,$ContractsAllCatidAfter)
	{			
		//$ContractsAllCatidBefore,$ContractsAllCatidAfter
				 
		$arrkey = array('paidcatid','nonpaidcatid');
		$totalcatidremovedarr 	= array();
		$totalcatidAddedarr 	= array();
		foreach($arrkey as $catidtype)
		{
			$updatecol="";
			$updatesql1="";
			
			$ContractsAllRemovedCatid 	= array_diff($ContractsAllCatidBefore[$catidtype],$ContractsAllCatidAfter[$catidtype]);	
			$ContractsAllAddedCatid 	= array_diff($ContractsAllCatidAfter[$catidtype],$ContractsAllCatidBefore[$catidtype]);
			
			if(count($ContractsAllRemovedCatid))
			{
				if($catidtype=="paidcatid")
				{
					$updatecol = " paid_clients = if(paid_clients<1,0,paid_clients-1) ";
				}
				else
				{
					$updatecol = " nonpaid_clients = if(nonpaid_clients<1,0,nonpaid_clients-1) ";
				}
				
				$totalcatidremovedarr = array_merge($totalcatidremovedarr,$ContractsAllRemovedCatid);
				
				$updatesql="UPDATE tbl_categorymaster_generalinfo SET ".$updatecol." WHERE catid IN (".implode(',',$ContractsAllRemovedCatid).")";
				$this -> conn_local->query_sql($updatesql);				
				$updatesql1="UPDATE tbl_category_freetext SET ".$updatecol." WHERE catid IN (".implode(',',$ContractsAllRemovedCatid).")";
				$this -> conn_local->query_sql($updatesql1);
				
			}
			
			if(count($ContractsAllAddedCatid))
			{
				if($catidtype=="paidcatid")
				{
					$updatecol = "paid_clients = if(paid_clients<0,1,paid_clients+1)";
				}
				else
				{
					$updatecol = "nonpaid_clients = if(nonpaid_clients<0,1,nonpaid_clients+1)";
				}
				
				$totalcatidAddedarr = array_merge($totalcatidAddedarr,$ContractsAllAddedCatid);
				
				$updatesql="UPDATE tbl_categorymaster_generalinfo SET ".$updatecol." WHERE catid IN (".implode(',',$ContractsAllAddedCatid).")";
				$this -> conn_local->query_sql($updatesql);				
				$updatesql1="UPDATE tbl_category_freetext SET ".$updatecol." WHERE catid IN (".implode(',',$ContractsAllAddedCatid).")";
				$this -> conn_local->query_sql($updatesql1);
				
			}

	
		}
		
		if(count($totalcatidremovedarr))
		{
			$updatesql= " UPDATE tbl_categorymaster_generalinfo SET active_flag =0  where catid in (".implode(',',$totalcatidremovedarr).") AND (nonpaid_clients=0 AND  paid_clients=0 ) ";			
			$this -> conn_local->query_sql($updatesql);
			
			$update_freetext = "UPDATE tbl_category_freetext  SET active_flag =0 WHERE catid in (".implode(',',$totalcatidremovedarr).")  AND  (nonpaid_clients=0 AND  paid_clients=0 ) ";
			$this -> conn_local->query_sql($update_freetext);
			
		}
		/*
		if(count($totalcatidAddedarr))
		{
			$updatesql= " UPDATE tbl_categorymaster_generalinfo SET active_flag = 1  where catid in (".implode(',',$totalcatidremovedarr).") AND (nonpaid_clients>0 or  paid_clients>0 ) ";			
			$this -> conn_local->query_sql($updatesql);
			
			$update_freetext = "UPDATE tbl_category_freetext  SET active_flag = 1 WHERE catid in (".implode(',',$totalcatidremovedarr).")  AND  (nonpaid_clients=0 AND  paid_clients=0 ) ";
			$this -> conn_local->query_sql($update_freetext);
			
		}
		*/
		$totaltouchedcatid = array();
		$totaltouchedcatid = array_merge($totalcatidAddedarr,$totalcatidremovedarr);
		
		//echo "<pre>totaltouchedcatid"; print_r($totaltouchedcatid); echo "</pre>"; 
		
		if($totaltouchedcatid)
		{ 
		
		$Catidstr =implode(",",$totaltouchedcatid);
		 	
		$updateactive_flag = "UPDATE d_jds.tbl_categorymaster_generalinfo a join d_jds.tbl_category_freetext  b on a.catid=b.catid 
		set a.active_flag=if(a.paid_clients=0 and a.nonpaid_clients=0,0,if(a.mask_status=0 and a.isdeleted=0,1,a.active_flag)),
		b.active_flag=if(a.paid_clients=0 and a.nonpaid_clients=0,0,if(a.mask_status=0 and a.isdeleted=0,1,a.active_flag)),
		b.paid_clients=a.paid_clients,
		b.nonpaid_clients=a.nonpaid_clients
		where a.catid in (".$Catidstr.")";
		$this -> conn_local->query_sql($updateactive_flag);		
		$this ->updateCategoryCountlog($totaltouchedcatid);
		}
	}
	
	function updateCategoryCountlog($catidarr)
	{
		//echo "<br> updateCategoryCountlog called<pre>"; print_r($catidarr);
		if(count($catidarr))
		{
			$catidarr = array_unique($catidarr); 
			$catidarr = array_filter($catidarr);
			$catidarratr = implode(",",$catidarr);
			if(count($catidarr))
			{
					$insertlog = " INSERT INTO db_iro.category_count_log  (catid,catidinfo,parentid,updateby)  ( 
					select catid, concat('pc=',paid_clients,',nc=',nonpaid_clients) as catidinfo ,'".$this->parentid."' as parentid ,'".addslashes($this->userid)."' as updateby from d_jds.tbl_categorymaster_generalinfo where catid in 
					(".$catidarratr.") 	)";
					
					 $this->conn_iro->query_sql($insertlog);					
			}
		}
		
	}
	
	function updatepaidnonpaidcount($Catidarr)
	{
		/*
		return;
			
		if(count($Catidarr)==0)
		return; // if there is no category no need to process
		$Catidarr = array_unique($Catidarr);		
		$category_count="";
		$category_count_paid="";
		$category_count_nonpaid="";
		
		$allcatidArr = array();
		$Catidstr=implode(",",$Catidarr);
		$catidCountsarr = array();
		$paidcatidpresent= array();
		$nonpaidcatidpresent= array();
		
		// fetching paid_clinets count for logging 
		$sql = "select count(parentid) as paidcount,bid_catid from (
		select distinct parentid,bid_catid from db_iro.tbl_fp_search where bid_catid in (".$Catidstr.")   
		union
		select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
		union
		select distinct parentid,bid_catid from db_iro.tbl_package_search where bid_catid in (".$Catidstr.")
		union
		select distinct parentid,bid_catid from db_iro.tbl_package_search where bid_catid in (".$Catidstr.") )a group by bid_catid ";
		$paidcountrs = $this -> conn_local->query_sql($sql);
				
		if($paidcountrs)
		{
			while($paidcountArr = mysql_fetch_assoc($paidcountrs))
			{
				$catidCountsarr[$paidcountArr['bid_catid']]['paid_clients']=$paidcountArr['paidcount'];
				$paidcatidpresent[]=$paidcountArr['bid_catid'];
				$category_count_paid.=" ".$paidcountArr['bid_catid']."->".$paidcountArr['paidcount'].",";
			}

			// updating paid_clients
			$updatesql = "update tbl_categorymaster_generalinfo a join (
			select count(distinct parentid) as paidcount,bid_catid from (
			select distinct parentid,bid_catid from db_iro.tbl_fp_search where bid_catid in (".$Catidstr.")   
			union
			select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
			union
			select distinct parentid,bid_catid from db_iro.tbl_package_search where bid_catid in (".$Catidstr.")
			union
			select distinct parentid,bid_catid from db_iro.tbl_package_search where bid_catid in (".$Catidstr.") )inertbl group by bid_catid)b on a.catid=b.bid_catid  set paid_clients=paidcount";
			$paidcountrs = $this -> conn_local->query_sql($updatesql);
		}
		
		// finding removed categories and set their paid_clients =0		
		$removedpaidcatid=array();
		$removedpaidcatid = array_diff($Catidarr,$paidcatidpresent); // this are the removed categories from paid tables
		
		if(count($removedpaidcatid)>0)
		{
			$updatesql = "update tbl_categorymaster_generalinfo SET paid_clients=0 where catid in (".implode(',',$removedpaidcatid).")";
			$paidcountrs = $this -> conn_local->query_sql($updatesql);
			$category_count_paid.="(paid_clients=0 =>".implode(',',$removedpaidcatid).")";
		}
		
		
		// fetching nonpaid_clinets count for logging 
		$sql = "select count(distinct parentid) as nonpaidcount,bid_catid from db_iro.tbl_nonpaid_search where bid_catid in (".$Catidstr.") group by bid_catid ";
		$nonpaidcountrs = $this -> conn_local->query_sql($sql);
		
		if($nonpaidcountrs)
		{
			while($nonpaidcountArr = mysql_fetch_assoc($nonpaidcountrs))
			{
				$catidCountsarr[$nonpaidcountArr['bid_catid']]['nonpaid_clients']=$nonpaidcountArr['nonpaidcount'];
				$nonpaidcatidpresent[]=$nonpaidcountArr['bid_catid'];
				$category_count_nonpaid.=" ".$nonpaidcountArr['bid_catid']."->".$nonpaidcountArr['nonpaidcount'].",";
			}
			
			$sql = "update tbl_categorymaster_generalinfo a join (
			select count(distinct parentid) as nonpaidcount,bid_catid from db_iro.tbl_nonpaid_search where bid_catid in (".$Catidstr.") group by bid_catid)b on a.catid=b.bid_catid  set nonpaid_clients=nonpaidcount";
			$nonpaidcountrs = $this -> conn_local->query_sql($sql);
		}
		// finding removed categories and set their paid_clients =0

		$removednonpaidcatid=array();
		$removednonpaidcatid = array_diff($Catidarr,$nonpaidcatidpresent); // this are the removed categories from non paid tables
		
		if(count($removednonpaidcatid)>0)
		{
			$updatesql = "update tbl_categorymaster_generalinfo SET nonpaid_clients=0 where catid in (".implode(',',$removednonpaidcatid).")";
			$paidcountrs = $this -> conn_local->query_sql($updatesql);
			$category_count_nonpaid.="(nonpaid_clients=0 =>".implode(',',$removednonpaidcatid).")";
		}
		
		// finally update active flag and tbl_category_freetext fields  
		$updateactive_flag = "UPDATE d_jds.tbl_categorymaster_generalinfo a join d_jds.tbl_category_freetext  b on a.catid=b.catid 
		set a.active_flag=if(a.paid_clients=0 and a.nonpaid_clients=0,0,if(a.mask_status=0 and a.isdeleted=0,1,a.active_flag)),
		b.active_flag=if(a.paid_clients=0 and a.nonpaid_clients=0,0,if(a.mask_status=0 and a.isdeleted=0,1,a.active_flag)),
		b.paid_clients=a.paid_clients,
		b.nonpaid_clients=a.nonpaid_clients
		where a.catid in (".$Catidstr.")";
		$this -> conn_local->query_sql($updateactive_flag);

		
		$category_count_loginsert="";
		foreach($Catidarr as $catidval)
		{	
			$catidinfo ="";
			$catidinfo = "paid_clients = ".$catidCountsarr[$catidval]['paid_clients'] ." nonpaid_clients = ".$catidCountsarr[$catidval]['nonpaid_clients'];
			
			$category_count_loginsert.= "(".$catidval.",'".$this->parentid."','".$catidinfo."','".addslashes($this->userid)."'),";
			  
		}
		$category_count_loginsert = trim($category_count_loginsert,",");
		if($category_count_loginsert)
		{
			
			$category_count_loginsert = "INSERT INTO category_count_log (catid,parentid,catidinfo,updateby) VALUES ".$category_count_loginsert;
			$this -> conn_iro->query_sql($category_count_loginsert);
		}
	*/
	}
	
	function getContractsAllCategory($parentid)
	{
		$tableArr = array('tbl_fp_search','tbl_package_search','tbl_nonpaid_search','tbl_compcatarea_ppc');
		
		$categoryArr = array();
		$categoryArr['paidcatid'] = array();
		$categoryArr['nonpaidcatid'] = array();
		
		foreach($tableArr as $tablename)
		{
			$sql = "SELECT group_concat( DISTINCT ".$this->catid_colarr[$tablename].") as catids  FROM ".$tablename." where parentid='".$parentid."' group by parentid";
			$res = $this -> conn_iro->query_sql($sql);
			$row = mysql_fetch_assoc($res);
			if($row['catids'])
			{
				if($tablename=='tbl_nonpaid_search')
				{
					$categoryArr['nonpaidcatid'] = explode(",",$row['catids']);
				}
				else
				{
					$tempArr = explode(",",$row['catids']);				
					$categoryArr['paidcatid'] = array_merge($tempArr,$categoryArr['paidcatid']);	
				}
			}
		}

		return $categoryArr;
	}

	function fetch_categorytype($catids, $c2c=0)
	{
		$catid_array = array();
		if(trim($catids)!="")
		{
			$catidstemparr = explode(",",$catids);
			$catidstemparr = array_unique($catidstemparr);
			$catidstemparr = array_filter($catidstemparr);
			

			$catids = implode(",",$catidstemparr); 	
				
			if($catids)
			{
				$fetchcattype_query = "SELECT DISTINCT catid,CASE search_type WHEN 0 THEN 'L' WHEN 1 THEN 'A' WHEN 2 THEN 'Z' WHEN 3 THEN 'SZ' WHEN 4 THEN 'NM' WHEN 5 THEN 'VNM' END  AS search_type,national_catid,category_type & 16 as exclusive,total_results FROM tbl_categorymaster_generalinfo WHERE catid IN (".$catids.") AND biddable_type='1' AND isdeleted=0 ";
								
				if($c2c!=1)
				{
					$fetchcattype_query .="AND mask_status=0 ";
				}
				/* in case of c2c we do not check  display_flag and mask condition. C2C contract may contain mask and unmask both type of categories. In case of platinum/diamond contract compcatarea of masked categories can not be generated -athar sir*/
				
				$fetchcattype_query .=" group by catid";

				$result_fetchcattype_query = $this -> conn_local->query_sql ($fetchcattype_query);
			
				if($result_fetchcattype_query && mysql_num_rows($result_fetchcattype_query)>0)
				{
					while($row = mysql_fetch_assoc($result_fetchcattype_query))
					{
						$catid_array[$row['catid']]['national_catid']= $row['national_catid'];
						$catid_array[$row['catid']]['searchtype']	= $row['search_type'];
						$catid_array[$row['catid']]['exclusive']	= ($row['exclusive'] == 16)?1:0;
						$catid_array[$row['catid']]['totcompdisplay']	= $row['total_results'];
					}
				}
			}
		}
		return $catid_array;

	}
	
	
	function getContractsCategory($parentid,$tbl_bidcatdetails)
	{		
		$sql = "SELECT group_concat( DISTINCT ".$this->catid_colarr[$tbl_bidcatdetails].") as catids  FROM ".$tbl_bidcatdetails." where parentid='".$parentid."' group by parentid";
		$res = $this -> conn_finance->query_sql($sql);
		$row = mysql_fetch_assoc($res);
		if($row['catids'])
		{
			return $row['catids'];
		}
	}

	function getContractsCategoryNew($parentid,$tbl_bidding_details,$campid)
	{		
		$sql = "SELECT group_concat( DISTINCT catid) as catids  FROM ".$tbl_bidding_details." where parentid='".$parentid."' and campaignid='".$campid."' group by parentid";
		
		if($campid == 21)
			$res = $this -> conn_national -> query_sql($sql);
		else
			$res = $this -> conn_finance->query_sql($sql);
			
		$row = mysql_fetch_assoc($res);
		if($row['catids'])
		{
			return $row['catids'];
		}
	}
	
	
	function getContractPincode($parentid,$tbl_bidding_details,$campid)
	{
		$sql = "SELECT group_concat( DISTINCT pincode) as pincodelist  FROM ".$tbl_bidding_details." where parentid='".$parentid."' and campaignid='".$campid."' ";
		
		if($campid == 21)
			$res = $this -> conn_national -> query_sql($sql);
		else
			$res = $this -> conn_finance->query_sql($sql);
			
		$row = mysql_fetch_assoc($res);
		if($row['pincodelist'])
		{
			return $row['pincodelist'];
		}
	}
	
	
	
	function getParentCategories($category_catids)
	{	global $dbarr;
		$final_parent_category_arr = array();
		if(count($category_catids))
		{
			$categoryMasterobj = new categoryMaster($dbarr,'cs');
			$parent_category_arr = $categoryMasterobj->getParentCategories($category_catids);
			
			if(count($parent_category_arr))
			{
				$final_parent_category_arr = $this -> fetch_categorytype(implode(",",$parent_category_arr));
			}
		}		
				
		return $final_parent_category_arr;		
		
	}

function loggintable($loggingquery=null , $removefromquery = null,$log_campaignid=0)
{

if ($loggingquery== null)
{ return true; } // blank query so no need to do any thing

if(is_array($removefromquery))
{	
	$removefromquery[]=",,"; // removing unwanted comma
	// replacing all the parameters which are to remove
	foreach($removefromquery as $key=>$removingstr)
	{		
		$loggingquery = str_replace($removingstr,"",$loggingquery);		
	}
}

if($loggingquery)
{ 
	
	$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$this->parentid."','".$log_campaignid."','".addslashes($loggingquery)."')";	
	$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
}
unset($loggingquery);unset($removefromquery );
	
}
	
function deleteTable()
{
	$parentid = $this->parentid;
	$delete_ddg_query="DELETE FROM tbl_fp_search where parentid='".$parentid."'";
	$result_delete_ddg_query=$this -> conn_iro->query_sql($delete_ddg_query);	
	
	$delete_ddg_query_nat="DELETE FROM tbl_fp_search_national where parentid='".$parentid."'";
	$res_delete_ddg_query_nat=$this -> conn_national -> query_sql($delete_ddg_query_nat);	
	
	$delete_zone_query="DELETE FROM tbl_package_search where parentid='".$parentid."'";
	$result_delete_zone_query=$this -> conn_iro->query_sql($delete_zone_query);

	$delete_ppc_query="DELETE FROM tbl_compcatarea_ppc where parentid='".$parentid."'";
	$result_delete_ppc_query=$this -> conn_iro->query_sql($delete_ppc_query);
	
	$delete_nonpaid_query="DELETE FROM tbl_nonpaid_search where parentid='".$parentid."'";
	$result_delete_nonpaid_query=$this -> conn_iro->query_sql($delete_nonpaid_query);

	$delete_query ="DELETE Entries FROM compcatarea table";

	$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($delete_query)."')";
	$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
	}
	
	function freezemask()
	{
		$parentid = $this->parentid;
		$today = date("Y-m-d H:i:s");

		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "freeze,mask,closedown_flag,misc_flag&1 as misc_flag";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid = '".$this->parentid."'";
		$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		if($temparr['numrows']>0)
		{
			$freeze =0;
			$mask 	=0;
			$closedown_flag=0;

			$result_freeze_arr = $temparr['data']['0'];
			$freeze  = $result_freeze_arr[freeze];
			$mask 	= $result_freeze_arr[mask];
			$closedown_flag =  $result_freeze_arr[closedown_flag];
			$misc_flag =  $result_freeze_arr[misc_flag];

			$this->deleteTable(); // calling function to delete entry for all case
			
			$update_freezemask_query = "UPDATE tbl_companymaster_finance SET freeze=".$freeze.",mask=".$mask." WHERE parentid = '".$parentid."'"; //  to make freeze and mask field in sync with extrdetails
			$this -> conn_finance->query_sql($update_freezemask_query);
			
			if($freeze || $mask || ($closedown_flag==1) || ($closedown_flag==13) || ($closedown_flag==14) || ($closedown_flag==15) || ($misc_flag==1)) // if contract is either freeze or masked or closedown or tagged as testing 
			{			
			
				$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Masked/Frozen/Closed.freeze=".$freeze." ,mask=".$mask." , closedown_flag=".$closedown_flag.", misc_flag=".$misc_flag."')";
				$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);				
				
				$update_clients_package_query = "UPDATE tbl_clients_package_contribution SET updateddate=now() WHERE parentid = '".$parentid."'";
				$result_update_clients_package_query = $this -> conn_finance->query_sql($update_clients_package_query);
	
				$update_clients_perday_query = "UPDATE tbl_companymaster_finance SET active_flag=0, updatedOn='".$today."' WHERE parentid = '".$parentid."' AND campaignid not in (2,13,14,15)"; //  deduction of ddg and catspon contracts should be continue even it is masked or frozen -- by Rajeevkrisna nair 
				$result_update_clients_perday_query = $this -> conn_finance->query_sql($update_clients_perday_query);

				
				$update_national_listing_query = "UPDATE tbl_national_listing SET activeflag=0,update_flag=0 WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				
				$update_national_listing_query = "UPDATE tbl_companymaster_finance_national SET active_flag=0  WHERE parentid = '".$parentid."' AND campaignid not in (2,13,14,15) ";
				$this -> conn_national->query_sql($update_national_listing_query);


				return 0;
			}		
			else // when contract is neither freeze nor masked
			{
				
				$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','UnMasked/unFreeze/nonclosed..freeze=".$freeze." ,mask=".$mask.",closedown_flag=".$closedown_flag."')";
				$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
								
				$update_clients_package_query = "UPDATE tbl_clients_package_contribution SET updateddate=now() WHERE parentid = '".$parentid."'";
				$result_update_clients_package_query = $this -> conn_finance->query_sql($update_clients_package_query);

				$update_clients_perday_query = "UPDATE tbl_companymaster_finance SET active_flag=1, updatedOn='".$today."' WHERE parentid = '".$parentid."'  AND campaignid not in (13,15) "; // as per JK sir's request stop active_flag=1 for campaignid 13 and 15
				$result_update_clients_perday_query = $this -> conn_finance->query_sql($update_clients_perday_query);

				// nayional listing part start 				
				
				$check_national = "SELECT bid_perday, multiplier, balance FROM tbl_companymaster_finance_national WHERE parentid='".$this->parentid."' and campaignid = 10 /*and balance > 0 and multiplier > 1*/";
				$res_check_national = $this -> conn_national -> query_sql($check_national);
				if( $res_check_national && mysql_num_rows($res_check_national)>0 )
				{
					$row_check_national = mysql_fetch_assoc($res_check_national);
					
					if($row_check_national['balance']>0)
					{
						$national_update_column .=" , paid=1, expired=0 ";
					}
					else
					{
						$national_update_column .=" , paid=1, expired=1 ";
					}
					
					if(($row_check_national['bid_perday'] * $row_check_national['multiplier'])>0)
					{
						$national_update_column .=" , dailyContribution= (".$row_check_national['bid_perday']." * ".$row_check_national['multiplier'].")  ";
					}
					
					/*if(($row_check_national['bid_perday'] * $row_check_national['multiplier'])>0)
					{
						$update_national_data = "UPDATE tbl_national_listing SET dailyContribution = (".$row_check_national['bid_perday']." * ".$row_check_national['multiplier'].") WHERE parentid='".$this->parentid."' ";
						$res_national_data = $this -> conn_national -> query_sql($update_national_data);
						
					}*/
				}
				else
				{
					$national_update_column .=" , paid=0, expired=1 ";
				}
	
				
				$update_national_listing_query = "UPDATE tbl_national_listing SET activeflag=1,update_flag=0".$national_update_column." WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				$update_national_listing_query = "UPDATE tbl_companymaster_finance_national SET active_flag=1  WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				// nayional listing part end

				return 1;	
			
			}			
		}
	}
	
	function set_nonpaidforcefully( $val)
	{
		
		#echo "<br>set_nonpaidforcefully called with val ".$val;
		
		if($this->nonpaidforcefully<0) // if already set to do not generate non_paid then do not overwrite 
		{
			return;
		}
		
		$this->nonpaidforcefully = $val;
		
		
	}
	
	
	function update_shopfront_guarantee_flag($compcattblArr,$shopfront_guaranteeflag,$catidstr) // currently it is called with only one place with shopfront_guaranteeflag=1
	{
		$guarantee_col_name['tbl_fp_search'] 		='vertical_guarantee';
		$guarantee_col_name['tbl_nonpaid_search'] 	='vertical_guarantee';
		$guarantee_col_name['tbl_package_search'] 	='vertical_guarantee';
		$guarantee_col_name['tbl_compcatarea_ppc'] 	='shopfront_guarantee';
		
		if(count($compcattblArr))
		{
			foreach($compcattblArr as $tblname)
			{
				if($shopfront_guaranteeflag==1 || $shopfront_guaranteeflag==2)
				{
					$updatesfgflag = "update ".$tblname." set ".$guarantee_col_name[$tblname]."=".$shopfront_guaranteeflag." where parentid='".$this -> parentid."' AND ".$this->catid_colarr[$tblname]." in (".$catidstr.")";
					$this->conn_iro->query_sql($updatesfgflag);					
				}
			}
		}
		
	}
	
	
	function ProcessShopFrontGuarantee()
	{

		$shopfront_commission_sql = "select group_concat(distinct(catid)) catidlistr, count(catid) as catcount from tbl_categorywise_commission where parentid='".$this -> parentid."'";
		$shopfront_commission_rs = $this->conn_finance->query_sql($shopfront_commission_sql);
		$shopfront_commission_catidlistr = null;
		$guarantee_exclude_catid_sql = null;
		
		if($shopfront_commission_rs && mysql_num_rows($shopfront_commission_rs))
		{
			$shopfront_commission_arr = mysql_fetch_assoc($shopfront_commission_rs);			
			if($shopfront_commission_arr['catcount']>0)// category is present in table
			{
				$shopfront_commission_catidlistr = $shopfront_commission_arr['catidlistr'];
				$guarantee_exclude_catid_sql = " and catid not in (".$shopfront_commission_catidlistr.")";	
			}
		}
		
		$shopfront_guarantee_sql = "select group_concat(distinct(catid)) catidlistr, count(catid) as catcount from tbl_shopfront_guarantee where parentid='".$this -> parentid."' ".$guarantee_exclude_catid_sql;	
		$shopfront_guarantee_rs = $this->conn_local->query_sql($shopfront_guarantee_sql);
		$shopfront_guarantee_catidlistr =null;
		
		if($shopfront_guarantee_rs && mysql_num_rows($shopfront_guarantee_rs))
		{
			$shopfront_guarantee_arr = mysql_fetch_assoc($shopfront_guarantee_rs);			
			if($shopfront_guarantee_arr['catcount']>0)// category is present in table
			{
				$shopfront_guarantee_catidlistr = $shopfront_guarantee_arr['catidlistr'];
			}
		}

		if($shopfront_guarantee_catidlistr!=null)
		{
			$this->update_shopfront_guarantee_flag($this->compcataretableArr,1,$shopfront_guarantee_catidlistr);
		}

		if($shopfront_commission_catidlistr!=null )
		{
			$this->update_shopfront_guarantee_flag($this->compcataretableArr,2,$shopfront_commission_catidlistr);
		}
		
	}
	
	function update_primary_tag()
	{	
		
		if(defined("LIVE_APP") && LIVE_APP==0)
		{
			$url="http://172.29.0.197:800/mvc/services/company/update_tag_catinfo?parentid=".$this->parentid."&city=".$this->Contractdata_city."&debug=0";			
			
		}elseif(defined("LIVE_APP") && LIVE_APP==1)
		{
			$url=IRO_APP_URL."/mvc/services/company/update_tag_catinfo?parentid=".$this->parentid."&city=".$this->Contractdata_city."&debug=0";
		}
		
		$response    = json_decode($this->call_curl_get($url),true);
		#echo "<pre>url--".$url; print_r($response);
		
		$primary_tag_temparray = $response['results']['data']['selcat'];
		
		if(count($primary_tag_temparray))
		{
			
			foreach($primary_tag_temparray as $key =>$primary_tag_arrval)
			{
				$primary_tag_str.= $primary_tag_arrval['id'].',';
			}
			$primary_tag_str= trim($primary_tag_str,',');
			
			if(strlen($primary_tag_str))
			{
				$sql= "update tbl_package_search set primary_tag=1 where parentid='".$this->parentid."' and catid in (".$primary_tag_str.")";				
				$this -> conn_iro->query_sql($sql);
				
				#echo "<br>sql---".$sql;
				
				$sql= "update tbl_nonpaid_search set primary_tag=1 where parentid='".$this->parentid."' and catid in (".$primary_tag_str.")";				
				$this -> conn_iro->query_sql($sql);
				#echo "<br>sql---".$sql;
			}
			
		}
		
	}
	
	function insertinto_tbl_nonpaid_search($catidlineagevalue)
	{
		$today = date("Y-m-d H:i:s");
		
		if(strlen($catidlineagevalue)>0)
		{
		$catidlineagevalue_arr= explode(',',$catidlineagevalue);
		$catidlineagevalue_arr = array_unique($catidlineagevalue_arr);
		$catidlineagevalue_arr = array_filter($catidlineagevalue_arr);
		}
			
		if(count($catidlineagevalue_arr)>0)
		{
			$catidlineagevalue_arr = array_unique($catidlineagevalue_arr);
			$catidlineagevalue_arr = array_filter($catidlineagevalue_arr);
			$catidlineagevalue =implode(',',$catidlineagevalue_arr);
			$nationalId = $this->fetch_categorytype($catidlineagevalue);
			foreach($catidlineagevalue_arr as  $k=>$value)
			{								
				$insert_query_nonpaid .= "('".$this->parentid."','".$this->docid."','".$value."','".$nationalId[$value]['national_catid']."','".$this->Contractpincode_value."','".$this->Contractdata_city."','".$this->latitude_value."','".$this->longitude_value."','".$this->Contractaddress_value."','".$this->duplicate_check_phonenos_value."','".$this->dup_groupid_value."','".$this->company_callcnt_rolling_value."',1,'".$this->userid."','".$today."'),";				
			}

			$nonpaid_ins_sql="INSERT IGNORE INTO tbl_nonpaid_search (parentid,docid,catid,national_catid,pincode,data_city,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,callcount_rolling,activeflag,updatedby,updatedon) VALUES";
			$insert_query_nonpaid	= trim($insert_query_nonpaid, ',');
			$nonpaid_ins_sql_final	= $nonpaid_ins_sql.$insert_query_nonpaid;
			$this->conn_iro->query_sql($nonpaid_ins_sql_final);
		}
	}

	function setdocId()
	{
		$docidsql =  "select docid from tbl_id_generator where parentid='".$this->parentid."'";
		$docidres = $this -> conn_iro->query_sql($docidsql);
		$docidarr = mysql_fetch_assoc($docidres);
		$this->docid = $docidarr['docid'];
		//echo"<br>docid". $this->docid;
	}
	
	function call_curl_post($curlurl,$data)
	{	
		#echo $curlurl.'?'.$data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	
	
	function call_curl_get($curl_url)
	{		
		$ch = curl_init($curl_url);
		$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		curl_close($ch);
		return $resstr;
	}
	function getApprovalDate($pid,$ver)
	{
		$approval_date=null;
		$sql = "select approval_date  from payment_version_approval where parentid='".$pid."' and version='".$ver."' ";
		$res = $this->conn_finance->query_sql($sql);
		if(mysql_num_rows($res))
		{
			$arr= mysql_fetch_assoc($res);
			$approval_date =$arr['approval_date'];
		}
		return $approval_date;
	}
	
	/*function processTblBiddingDetailsforpackage($TblBiddingDetails_res)
	{// we will get result set of tbl_bidding_dettails and process the data 
		
		$CategoryPerdayarr = array();		
		
		if($TblBiddingDetails_res && mysql_num_rows($TblBiddingDetails_res)>0)
		{
			while($arr = mysql_fetch_assoc($TblBiddingDetails_res))
			{			
				$CategoryPerdayarr[$arr['bid_catid']] += $arr['bidperday'];
			}
		}		
		return $CategoryPerdayarr;		
	}*/
	
	function processTblBiddingDetails($companymaster_finance_arr)
	{// we will get result set of tbl_bidding_dettails and process the data 
		
		$campaignidstr=null;
		if( ($companymaster_finance_arr[1]['balance']>0 || $companymaster_finance_arr[1]['manual_override']==1 ) || $companymaster_finance_arr[2]['balance']>0  )
		{
			$campidarr = array();
			// package checking
			if($companymaster_finance_arr[1]['balance']>0 || $companymaster_finance_arr[1]['manual_override']==1 )
			{
				array_push($campidarr,1);
			}
			
			// fixed position checking			
			if($companymaster_finance_arr[2]['balance']>0 )
			{
				array_push($campidarr,2);
			}
			
			$campaignidstr =" and campaignid in ( ".implode(',',$campidarr)." ) " ;	
			
		}
		$catid_bidperday_sql= "select catid,sum(bidperday) as catid_bidperday,sum(actual_budget/duration) as category_perday_original from tbl_bidding_details where parentid='".$this->parentid."' ".$campaignidstr." group by catid";
		$catid_bidperday_res = $this -> conn_finance->query_sql($catid_bidperday_sql);
		#echo $catid_bidperday_sql;
		$CategoryPerdayarr = array();
		
		if($catid_bidperday_res && mysql_num_rows($catid_bidperday_res)>0)
		{
			while($catid_bidperday_temparr = mysql_fetch_assoc($catid_bidperday_res))
			{
				$CategoryPerdayarr[$catid_bidperday_temparr['catid']]['catid_bidperday']=$catid_bidperday_temparr['catid_bidperday'];
				$CategoryPerdayarr[$catid_bidperday_temparr['catid']]['category_perday_original']=$catid_bidperday_temparr['category_perday_original'];
			}
		}
		#echo '<pre>CategoryPerdayarr'; print_r($CategoryPerdayarr);
		return $CategoryPerdayarr;		
	}
	
	
	
	function processTblBiddingDetailsNational($companymaster_finance_arr)
	{// we will get result set of tbl_bidding_dettails and process the data 
		
		
		if( $companymaster_finance_arr[21]['balance']>0  )
		{
			$catid_bidperday_sql= "select catid,pincity,sum(bidperday) as catid_bidperday,sum(actual_budget/duration) as category_perday_original from db_national_listing.tbl_bidding_details_national where parentid='".$this->parentid."' GROUP BY pincity,catid";
			$catid_bidperday_res = $this -> conn_national -> query_sql($catid_bidperday_sql);
			#echo $catid_bidperday_sql;
			$CategoryPerdayarr = array();
			
			if($catid_bidperday_res && mysql_num_rows($catid_bidperday_res)>0)
			{
				while($catid_bidperday_temparr = mysql_fetch_assoc($catid_bidperday_res))
				{
					$CategoryPerdayarr[strtolower($catid_bidperday_temparr['pincity'])][$catid_bidperday_temparr['catid']]['catid_bidperday']=$catid_bidperday_temparr['catid_bidperday'];
					$CategoryPerdayarr[strtolower($catid_bidperday_temparr['pincity'])][$catid_bidperday_temparr['catid']]['category_perday_original']=$catid_bidperday_temparr['category_perday_original'];
				}
			}
		}
		#echo '<pre>CategoryPerdayarr'; print_r($CategoryPerdayarr);
		return $CategoryPerdayarr;		
		
	}
	
	
	/*function processcompanymasterfinanceforpackage($companymaster_finance_arr)
	{
		$packagereturnarr = array();
		
		$packagereturnarr['contract_bidperday']=0;
		// process if only it has packge campaign with balance
		
		if($companymaster_finance_arr[1]['balance']>0)
		{
			$packageversion	= intval($companymaster_finance_arr[1]['version']);
						
			foreach($companymaster_finance_arr as $campaignid=>$campaignidarr)
			{	
				if(intval($campaignidarr['version'])== $packageversion )
				{
					$curcampaignid = intval($campaignid);
										
					switch($curcampaignid)
					{						
						// campaigns that need to exclude						
						case 4:
						case 17:
						case 18:					
						break;						
						
						// campaigns where balance will get consider as budget/duration
						case 22:
						case 56:
						case 72:
						case 74:
						case 75:
						case 82:
						case 83:
						case 84:
						case 86:
						$packagereturnarr['contract_bidperday'] += ($campaignidarr['budget']/$campaignidarr['duration']);
						break;
						
						default:
						if($campaignidarr['balance']>0)
						{
							$packagereturnarr['contract_bidperday'] += $campaignidarr['bid_perday'];
						}						
					}
				}
			}
			
			$packagereturnarr['approval_date'] = $this->getApprovalDate($this->parentid,$packageversion);			
		}		
		return $packagereturnarr;		
	}*/

	function processcompanymasterfinance($companymaster_finance_arr)
	{		
		$versionwisvalue = array();
		$returnarr = array();		
		
		if( ($companymaster_finance_arr[1]['balance']>0 || $companymaster_finance_arr[1]['manual_override']==1 ) || $companymaster_finance_arr[2]['balance']>0  )
		{
			
			foreach($companymaster_finance_arr as $campaignid=>$campaignidarr)
			{
				$campaign_bidperday	=	0;
				$campaignversion	= intval($campaignidarr['version']);				
			
				$curcampaignid = intval($campaignid);
									
				switch($curcampaignid)
				{
					// campaigns that need to exclude						
					case 4:
					case 17:
					case 18:					
					break;						
					
					// campaigns where balance will get consider as budget/duration
					case 22:
					case 56:
					case 72:
					case 74:
					case 75:
					case 82:
					case 83:
					case 84:
					case 86:
					
					if($campaignidarr['budget'] != 0 && $campaignidarr['duration'] != 0 )
					{
						$campaign_bidperday = ($campaignidarr['budget']/$campaignidarr['duration']);
					}
					break;
					
					default:
					if($campaignidarr['balance']>0)
					{
						$campaign_bidperday = $campaignidarr['bid_perday'];
					}
				}

				$versionwisvalue[$campaignversion]['contract_bidperday'] +=$campaign_bidperday;
				
				if(!isset($versionwisvalue[$campaignversion]['approval_date']) && ($campaignid==1 || $campaignid==2))
				{					
					$versionwisvalue[$campaignversion]['approval_date'] = $this->getApprovalDate($this->parentid,$campaignversion);
				}				
			}
			// now array has been prepared , campaignwise data needs to prepare 
			
			// package data
			if($companymaster_finance_arr[1]['balance']>0  || $companymaster_finance_arr[1]['manual_override']==1 )
			{
				$returnarr[1]['contract_bidperday']	= $versionwisvalue[$companymaster_finance_arr[1]['version']]['contract_bidperday'];
				$returnarr[1]['approval_date']		= $versionwisvalue[$companymaster_finance_arr[1]['version']]['approval_date'];				
			}
			
			// fixed position data
			if($companymaster_finance_arr[2]['balance']>0 )
			{				
				$returnarr[2]['contract_bidperday']	= $versionwisvalue[$companymaster_finance_arr[2]['version']]['contract_bidperday'];
				$returnarr[2]['approval_date']		= $versionwisvalue[$companymaster_finance_arr[2]['version']]['approval_date'];
			}
		}
		
		return $returnarr;		
	}
	
	function paidFlagCampaignEligibility($companymaster_finance_arr)
	{
		$campaigneligibilityforcompcatarea= array(1,2,17,18,81,21);
		$CampaignEligibility=0;
		
		foreach($companymaster_finance_arr as $campaignid=>$campaignidarr)
		{
			if ( in_array($campaignid ,$campaigneligibilityforcompcatarea))
			{
				$CampaignEligibility=1;
				break;
			}
			 
		}
		return $CampaignEligibility;
		
	}
	
	function gettbl_companymaster_finance()
	{
		$resutlarr= array();
		
		$sql="SELECT *  FROM tbl_companymaster_finance WHERE parentid='".$this->parentid."' ";
		$res = $this -> conn_finance->query_sql($sql);
		
		if(mysql_num_rows($res))
		{
			while($row= mysql_fetch_assoc($res))
			{
				$resutlarr[intval($row['campaignid'])] = $row;
			}
		}
		
		$sql_national ="SELECT *  FROM tbl_companymaster_finance_national WHERE parentid='".$this->parentid."' AND campaignid = 21 ";
		$res_national = $this -> conn_national -> query_sql($sql_national);
		
		if(mysql_num_rows($res_national))
		{
			while($row_national= mysql_fetch_assoc($res_national))
			{
				$resutlarr[intval($row_national['campaignid'])] = $row_national;
			}
		}
		
		
		return $resutlarr;
	}
	
	function compcatarea_gen()
	{
		$function_status = -1;
		/* this variable is going to tell whether this has been completed sucessfully or not it by default it is -1 we change its value so that */
		# Query Companymaster Table for mask/freeze status #
		####################################################
		$parentid = $this -> parentid;

		$joinfiedsname 	= "a.companyname,a.sphinx_id, a.nationalid, a.parentid,a.docid, b.freeze, b.mask, a.paid,a.data_city, substring(a.parentid,2) as nonpaid_contractid, a.pincode, 1 as activeflag,a.company_callcnt,a.company_callcnt_rolling,a.landline_display, a.mobile_display, replace(b.catidlineage_search,'/','') as catidlineage, replace(b.catidlineage_nonpaid,'/','') as catidlineage_nonpaid,c.phone_search,c.address,a.area,c.regionid,a.latitude,a.longitude,c.email as email_search,low_ranking,b.dup_groupid";
		$jointablesname	= "tbl_companymaster_generalinfo a join tbl_companymaster_extradetails b join tbl_companymaster_search c";
		$joincondon		= "on a.parentid = b.parentid and b.parentid=c.parentid";
		$wherecond		= "a.parentid = '".$parentid."'";

		$temparr		= $this->compmaster_obj->joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond);
		$rowcount 		= $temparr['numrows'];

		IF ($rowcount<=0) {

			$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Parentid not found on companymaster table... Please check and enter again...File EXITED.')";
			$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
			//echo "<br>Process Exited. Check tbl_compcatarea_regen_log for details.";

			return 0;
		}

		$row	= $temparr['data']['0'];
		
		$this->companynameval = addslashes(stripslashes($row['companyname'])); 
		$freeze_value		= $row['freeze'];
		$mask_value			= $row['mask'];
		//$paid_value			= $row['paid'];
		//$contractid_value	= $row['nonpaid_contractid'];
		$pincode_value		= $row['pincode'];
		$this->Contractpincode_value	= $pincode_value;
		$activeflag_value	= $row['activeflag'];
		$catidlineage_value	= $row['catidlineage'];
		$catidlineage_nonpaid= $row['catidlineage_nonpaid'];
		$phone_search		= addslashes(stripslashes($row['phone_search']));;
		$address			= addslashes(stripslashes($row['address']));
		$this->Contractaddress_value=$address;
		$area				= addslashes(stripslashes($row['area']));
		$regionid			= $row['regionid'];
		$latitude			= $row['latitude'];
		$this->latitude_value = $row['latitude'];
		$longitude			= $row['longitude'];
		$this->longitude_value = $row['longitude'];
		
		$email_search		= addslashes(stripslashes($row['email_search']));
		$data_city 			= $row['data_city'];
		$this->Contractdata_city	= $row['data_city'];
		//$this->docid		= $row['docid'];
        $nonpaid_callcount  = $row['company_callcnt'];
        $company_callcnt_rolling  = $row['company_callcnt_rolling'];
        $this->company_callcnt_rolling_value =$company_callcnt_rolling;
        
        $low_ranking  		= $row['low_ranking'];
        $dup_groupid  		= $row['dup_groupid'];
        $this->dup_groupid_value=$dup_groupid;
        $land_display_arr = array();
        $land_display_arr = explode(",",$row['landline_display']);
        $mob_display_arr = array();
        $mob_display_arr = explode(",",$row['mobile_display']);
        $phonenos_arr = array();
        $phonenos_arr = array_merge($land_display_arr,$mob_display_arr);
        $phonenos_arr = array_filter(array_unique($phonenos_arr));
        if(count($phonenos_arr)){
			$phonenos_str = implode(",",$phonenos_arr);
		}
		$duplicate_check_phonenos = addslashes(stripslashes($phonenos_str));
		$this->duplicate_check_phonenos_value=$duplicate_check_phonenos;

		
		$today = date("Y-m-d H:i:s");

		
		$pack_parent_category_arr = array();
		$ddg_parent_category_arr = array ();		
		

		
		//$check_paid_entry="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' and campaignid in (1,2,17,18,81) order by campaignid";
		//$res_paid_entry=$this -> conn_finance->query_sql($check_paid_entry);
		
		//$rowpaidcount = mysql_num_rows($res_paid_entry);		
		//~ if($rowpaidcount>0)
		//~ {
			//~ $paid_value = 1;
		//~ }
		//~ else
		//~ {
			//~ $paid_value = 0;
		//~ }
		
		
		
		$companymaster_finance_arr =  $this->gettbl_companymaster_finance();
				
		$paid_value = $this->paidFlagCampaignEligibility($companymaster_finance_arr);
		$proscompfin =  $this->processcompanymasterfinance($companymaster_finance_arr);
		$CategoryPerdayarr =  $this->processTblBiddingDetails($companymaster_finance_arr);
		
		$CategoryPerdayarrNat =  $this->processTblBiddingDetailsNational($companymaster_finance_arr);
		
		
		// low_ranking will over write paid flag so that only compcatarea non will be generated 
		if($low_ranking==1)
		{
			$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Low ranking contract so removing all compcatarea entry and generate compcatarea_non_paid')";
			$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
			$this->deleteTable(); // calling function to delete entry from table
			$paid_value = 0;			
		}
		
		
		$this->paidflag = $paid_value;
		
		if($this->freezemask()==0) // contract is freeze or masked so no need to do further 
		{
			return 0;
		}
				
		//echo "<br>parentid".$parentid."paid_value--".$paid_value;
		if($paid_value == 1)		// paid contract
		{
		
		$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Deleting entries from COMPCATAREA Tables at parentid level')";
		$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

		$this->deleteTable(); // calling function to delete entry from table

		$check_contracts_query="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' order by campaignid";
		$result_check_contracts_query=$this -> conn_finance->query_sql($check_contracts_query);
		
		$rowcount = mysql_num_rows($result_check_contracts_query);

		IF ($rowcount<=0)
		{

			$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Parentid found in tbl_companymaster_finance.')";
			$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

			//return 0;
		}
		else
		{
		
			WHILE ($row = mysql_fetch_array($result_check_contracts_query)){
			$var_campaignid = $row['campaignid'];
			$searchcriteria = 0; // by default it is 0 it will be changed as per campaignid
			
			$today = date("Y-m-d H:i:s");
										
			IF ($var_campaignid == 2)
			{

				unset($myzone_ins);
				unset($zone_ins);
				unset($finalmyzonequery);
				unset($finalzonequery);
				
				$ddg_campaignid = $var_campaignid;
				// removing all delete queries
				
				//contract_bidperday
				$pdg_contract_bidperday   = $proscompfin[2]['contract_bidperday'];
				
				$check_balance_query="SELECT campaignid, expired, balance, manual_override, threshold_active_flag,searchcriteria,exclusivelisting_tag FROM tbl_companymaster_finance WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."'";
				$result_check_balance_query=$this -> conn_finance->query_sql($check_balance_query);

				$row = mysql_fetch_array($result_check_balance_query);

				$var_balance = $row['balance'];
				$var_expired = $row['expired'];
				$var_manualoverride = $row['manual_override'];
				$var_activeflag = $row['threshold_active_flag'];
				$searchcriteria = $row['searchcriteria'];
				$exclusive_flag = $row['exclusivelisting_tag'];

				# Fetch DDG Categories #
				########################
				$category_catids = $this -> getContractsCategoryNew($parentid,'tbl_bidding_details',$var_campaignid);
				//echo "<pre>category_catids--";print_r($category_catids);
				//echo '<br>category_catids--'.$category_catids;
				$this->pdgarraylist = explode(',',$category_catids);				
				$category_catids_arr = $this -> fetch_categorytype($category_catids); 
				$rowcount = count($category_catids_arr);
				if($rowcount<=0) {

							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Entries not found in tbl_bidding_details.')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
							$this->set_nonpaidforcefully(1);
				}
				else
				{	
					
					$pdg_category_count = count(array_filter(explode(',', $category_catids)));
					$ContractpdgPincode = $this->getContractPincode($parentid,'tbl_bidding_details',$var_campaignid);
					$pdg_pincode_count 	   = count(array_filter(explode(',', $ContractpdgPincode)));
										
					unset($insert_common_query_nonpaid);

					foreach($category_catids_arr as $cat_key =>$cat_value)
					{
						if(intval($cat_key)<=0)
						{
							continue;
						}
						$insert_common_query_ddg = '';
						$finalddgquery = '';
						$insert_common_query_nonpaid ='';
				
						$push_ddg_catquery="SELECT catid as bid_catid ,national_catid,pincode,position_flag,inventory,lcf,hcf,physical_pincode,latitude,longitude,bidperday,actual_budget/duration as bidperday_original FROM tbl_bidding_details WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."' and catid='".$cat_key."'";
					   
					   $result_push_ddg_catquery=$this -> conn_finance->query_sql($push_ddg_catquery);
					  
					   while($row = mysql_fetch_array($result_push_ddg_catquery))
					   {
						   $tbl_client_ddg_contres =null;
						   							
							$var_bid_catid				= $row['bid_catid'];
							$var_pincode				= $row['pincode'];							
							$var_inventory				= $row['inventory'];							
							$var_lcf					= $row['lcf'];							
							$var_hcf					= $row['hcf'];
							$var_physical_pincode		= $this->Contractpincode_value;
							$var_position_flag			= $row['position_flag'];
							$var_nationalcatid			= $row['national_catid'];;
							//$var_latitude				= $row['latitude'];;
							//$var_longitude				= $row['longitude'];;
							//echo "<br>var_position_flag-".$var_position_flag."<br>";
							$pdgcatbidperday 			= $row['bidperday'];
							$pdgcatbidperday_original 			= $row['bidperday_original'];
							$pdgcategory_perday			= $CategoryPerdayarr[$var_bid_catid]['catid_bidperday'];
							$pdgcategory_perday_original = $CategoryPerdayarr[$var_bid_catid]['category_perday_original'];
							$pdg_contract_bidperday 	= $proscompfin[2]['contract_bidperday'];
							$pdg_contract_active_date 	= $proscompfin[2]['approval_date'];
							
							$searchcol= "pos".$var_position_flag."_cum_inventory as search_cum_ddg_ratio , pos".$var_position_flag."_cum_callcount as search_cum_callcount ,pos".$var_position_flag."_contribution as search_contribution ";

							/*if($var_position_flag==3||$var_position_flag==4||$var_position_flag==5||$var_position_flag==6||$var_position_flag==7)
							{
								$ddg_contsqlcond="bronze_cumulative_callcount as cumulative_callcount, bronze_cumulative_ddg_ratio as cumulative_ddg_ratio, bronze_contribution as contribution ";
							}
							elseif($var_position_flag==2)
							{
								$ddg_contsqlcond="diamond_cumulative_callcount as cumulative_callcount, diamond_cumulative_ddg_ratio as cumulative_ddg_ratio, diamond_contribution as contribution ";
							}
							elseif($var_position_flag==1)
							{
								$ddg_contsqlcond="platinum_cumulative_callcount as cumulative_callcount, platinum_cumulative_ddg_ratio as cumulative_ddg_ratio, platinum_contribution as contribution ";
							}
							*/
							if($var_pincode && ($var_position_flag >=0 && $var_position_flag<100) )
							{
								$tbl_clients_fp_contributionsql= "SElECT ".$searchcol." from tbl_clients_fp_contribution where parentid='".$parentid."' AND catid=".$var_bid_catid." AND pincode=".$var_pincode;
							
								$tbl_client_ddg_contres = $this -> conn_finance->query_sql($tbl_clients_fp_contributionsql);													
							}
							
							if(mysql_num_rows($tbl_client_ddg_contres)>0)
							{
								$tbl_client_ddg_contarry= mysql_fetch_assoc($tbl_client_ddg_contres);

								$search_cum_callcount=$tbl_client_ddg_contarry['search_cum_callcount'];
								$search_cum_ddg_ratio=$tbl_client_ddg_contarry['search_cum_ddg_ratio'];
								$search_contribution=$tbl_client_ddg_contarry['search_contribution'];								
								
							}
							else
							{
								$search_cum_callcount= 1;
								$search_cum_ddg_ratio= '0.0000000';
								$search_contribution = '0.0000000';								
							}							

							//echo "catid= ".$var_bid_catid."-  pincode=".$var_pincode ."^^ search_cum_callcount=".$search_cum_callcount." ^^search_cum_ddg_ratio=".$search_cum_ddg_ratio."^^search_contribution=".$search_contribution;
								
							$insert_common_query_ddg .= "('".$this->parentid."','".$this->docid."','".$this->companynameval."','".$var_bid_catid."','".$var_nationalcatid."','".$var_pincode."','".$var_position_flag."','".$var_inventory."','".$var_lcf."','".$var_hcf."','".$pdgcatbidperday."','".$pdgcatbidperday_original."','".$pdgcategory_perday."','".$pdgcategory_perday_original."','".$pdg_contract_bidperday."','".$pdg_contract_active_date."','".$data_city."','".$var_physical_pincode."','".$area."','".$this->latitude_value."','".$this->longitude_value."','".$address."','".$duplicate_check_phonenos."','".$dup_groupid."','".$search_cum_callcount."','".$search_cum_ddg_ratio."','".$search_contribution."',1,'".$pdg_category_count."','".$pdg_pincode_count."','".$this->userid."','".$today."'),";

							
						}
						
						$ddg_ins="INSERT IGNORE INTO tbl_fp_search(parentid,docid,companyname,catid,national_catid,pincode,position_flag,inventory,lcf,hcf,bidperday,bidperday_original,category_perday,category_perday_original,contract_bidperday,active_date,data_city,physical_pincode,physical_area,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,search_cum_callcount,search_cum_ddg_ratio,search_contribution,activeflag,category_count,pincode_count,updatedby,updatedon) VALUES";
						
						if($insert_common_query_ddg)
						{
							$insert_common_query_ddg		= trim($insert_common_query_ddg, ',');
							$finalddgquery		=  $ddg_ins.$insert_common_query_ddg;
						}						
						
						if ( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{						# Balance Absent. Delete from CCA_DDG and push data into CCA_DDG
							if(strlen(trim($finalddgquery))!=0) // if query is created then only write into table
							{
							$insert_cca_ddgquery=$this -> conn_iro->query_sql($finalddgquery);
							//echo "<br>".$finalddgquery;
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
							$logginquery = $finalddgquery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
							//$this->set_nonpaidforcefully(-1); // no need to generate compcatarea non_paid
							$this->nonpaidentryforpdg = 1; // need to generate tbl_nonpaid_search for fixed position 
							$this->activephonesearch = 1; // need to generate tbl_nonpaid_search for fixed position 
							}
						}
						
						if(($var_balance<=0 && $var_manualoverride==0) && $rowcount>0 )
						{
							//$this->set_nonpaidforcefully(1); // to generate tbl_nonpaid_search 
						}

						}						
						/* Non paid categories to be inserted in tbl_nonpaid_search  STARTS*/				
						
						if($exclusive_flag)
						{
							$nonpaid_category_catids = $this -> getContractsCategory($parentid,'tbl_bidcatdetails_nonpaid');
							$nonpaid_category_catids_arr = $this -> fetch_categorytype($nonpaid_category_catids); 
							$rowcount = count($nonpaid_category_catids_arr);
							
							if($rowcount<=0)
							{

								$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Invalid campaignid. No entries found in tbl_bidcatdetails_nonpaid. Please check and enter again...File EXITED.')";
								//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
							}
							else
							{
								unset($insert_common_query_nonpaid);
								foreach($nonpaid_category_catids_arr as $cat_key =>$cat_value)
								{
									if(intval($cat_key)<=0)
									{
										continue;
									}
									$push_nonpaid_catquery="SELECT bid_catid,bid_zone,parentid,pincode,uptdate,nationalcatid FROM tbl_bidcatdetails_nonpaid WHERE parentid='".$parentid."' and bid_catid='".$cat_key."'";
									$res_push_nonpaid_catquery=$this -> conn_finance->query_sql($push_nonpaid_catquery);
									while($row_nonpaid = mysql_fetch_assoc($res_push_nonpaid_catquery))
									{
										//$var_campaignid				= $row_nonpaid['campaignid'];
										$var_campaignid				= 0 ;
										$var_bid_catid				= $row_nonpaid['bid_catid'];
										$var_bid_zone				= $row_nonpaid['bid_zone'];
										$var_parentid				= $row_nonpaid['parentid'];
										$var_pincode				= $row_nonpaid['pincode'];
										$var_uptdate				= $row_nonpaid['uptdate'];
										$var_nationalcatid			= $nonpaid_category_catids_arr[$row_nonpaid['bid_catid']]['national_catid'];
										$insert_common_query_nonpaid = $insert_common_query_nonpaid."('".$var_bid_catid."','".$var_parentid."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$this->latitude_value."','".$this->longitude_value."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."','".$dup_groupid."'),";
									}
								}
					
								$nonpaid_ins="INSERT IGNORE INTO tbl_nonpaid_search(bid_catid, parentid, activeflag,nationalcatid, phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos,dup_groupid) VALUES";
								
								if($insert_common_query_nonpaid)
								{
									$insert_common_query_nonpaid	= trim($insert_common_query_nonpaid, ',');
									$finalnonpaidquery	=  $nonpaid_ins.$insert_common_query_nonpaid;
									//$insert_cca_nonpaidquery=$this -> conn_iro->query_sql($finalnonpaidquery);
									
									$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
									$logginquery = $insert_cca_nonpaidquery;
									$this->loggintable($logginquery,$removeterms,$var_campaignid);
								}
							}
						}						
						
						/* Non paid categories to be inserted in tbl_nonpaid_search  STARTS*/
						# Actual inserts into the DDG table happen here #
						#################################################

						if( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{						# Balance Absent. Delete from CCA_DDG and push data into CCA_DDG
								$ddg_parent_category_arr = $this -> getParentCategories($category_catids);
								// removing flag updation queries

						}				
				}

				$this->set_nonpaidforcefully(1); // to generate tbl_nonpaid_search for spill entry
				
				} # END OF DDG CHECK

				//IF (substr($var_contractid,0,1) == "L"){
				if($var_campaignid == 1)
				{
					unset($myzone_ins);
					unset($zone_ins);
					unset($finalmyzonequery);
					unset($finalzonequery);
					unset($var_manualoverride);
					
					$pack_campaignid = $var_campaignid;
					// removed delete queries
					$check_balance_query="SELECT campaignid, expired, balance, manual_override, threshold_active_flag,searchcriteria,multiplier,daily_threshold_inc,daily_threshold ,weekly_threshold_inc,weekly_threshold , monthly_threshold_inc,monthly_threshold, leadtopackage_flag,bid_perday,radius_of_significance  FROM tbl_companymaster_finance WHERE parentid= '".$parentid."' AND campaignid='".$var_campaignid."'"; 
					
					$result_check_balance_query=$this -> conn_finance->query_sql($check_balance_query);

					$row = mysql_fetch_array($result_check_balance_query);
					$var_balance = $row['balance'];
					$var_expired = $row['expired'];
					$var_manualoverride = $row['manual_override'];
					$searchcriteria = $row['searchcriteria'];
					$var_activeflag = $row['threshold_active_flag'];
					$multiplier = $row['multiplier'];
					$radius_of_significance = $row['radius_of_significance'];
					
					
					
					$var_searchpara	  = 0.00000;	

					if(($row['daily_threshold_inc']>$row['daily_threshold'] || $row['weekly_threshold_inc']>$row['weekly_threshold'] || $row['monthly_threshold_inc']>$row['monthly_threshold']) && $row['leadtopackage_flag']==0 && $row['campaignid']==1)
					{
						$var_searchpara=1;
					}
					
					
					$purepack_numrows=0;						
					if($var_expired==1)
					{ // checking whether it is pure package or not
						$check_purepack_query="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid= '".$parentid."' AND campaignid in (2,17,18,81)";					
						$rs_purepack = $this -> conn_finance->query_sql($check_purepack_query);							
						$purepack_numrows = mysql_num_rows($rs_purepack);
					 
					}


							
					# Fetch MyZone / Zone Categories #
					##################################
					
					unset($insert_common_query_nonpaid);
					$pack_category_catids = $this -> getContractsCategoryNew($parentid,'tbl_bidding_details',$var_campaignid);
					$pack_category_catids_arr = $this -> fetch_categorytype($pack_category_catids);
			
						
					//contract_bidperday,active_date,physical_area,category_count,pincode_count
					$package_contract_bidperday   = $proscompfin[1]['contract_bidperday'];
					$package_contract_active_date = $proscompfin[1]['approval_date'];
					//echo '<pre>proscompfin'; print_r($proscompfin);
					//$packageCategoryPerdayarr =  $this->processTblBiddingDetails($result_push_myzone_catquery);
					//mysql_data_seek($result_push_myzone_catquery,0); // we need to reset the resource counter so that it should point to 0th row  
					
					$ContractPincode = $this->getContractPincode($parentid,'tbl_bidding_details',$var_campaignid);
					$package_pincode_count 	   = count(array_filter(explode(',', $ContractPincode)));						
					$package_category_count		  = count(array_filter(explode(',', $pack_category_catids)));
					
					
					
					$catid_bidperday_sql= "select catid,sum(bidperday) as catid_bidperday from tbl_bidding_details where parentid='".$this->parentid."'  group by catid";
					$catid_bidperday_res = $this -> conn_finance->query_sql($catid_bidperday_sql);
					
					while($catid_bidperday_temparr = mysql_fetch_assoc($catid_bidperday_res))
					{
						$catid_bidperday_arr[$catid_bidperday_temparr['catid']]=$catid_bidperday_temparr['catid_bidperday'];
					}
					
					foreach ($pack_category_catids_arr as $cat_key =>$cat_value)
					{
						if(intval($cat_key)<=0)
						{
								continue;
						}
						
						$insert_common_query_myzone = '';
						$insert_common_query_zone = '';
						$insert_common_query_nonpaid = '';
						$finalzonequery='';

						$push_myzone_catquery="SELECT catid as bid_catid ,national_catid,pincode,position_flag,inventory,lcf,hcf,physical_pincode,latitude,longitude,bidperday,actual_budget/duration as bidperday_original FROM tbl_bidding_details WHERE parentid='".$parentid."' AND campaignid='".$var_campaignid."' and catid=".$cat_key."";
																
						$result_push_myzone_catquery=$this -> conn_finance->query_sql($push_myzone_catquery);

						$rowcount = mysql_num_rows($result_push_myzone_catquery);

						if($rowcount<=0) {

							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Invalid campaignid. No entries found in tbl_bidding_details. Please check and enter again...File EXITED.')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
							$this->set_nonpaidforcefully(1);
						}
						else
						{
														
							
							if($var_expired==1 && $purepack_numrows==0)
							{
								$this->set_nonpaidforcefully(1);
								$this->purepack_epired = 1;							
							}
							else
							{
							
							
							
								while($row = mysql_fetch_array($result_push_myzone_catquery))
								{
								
									$var_bid_catid				= $row['bid_catid'];
									$var_pincode				= $row['pincode'];							
									$var_inventory				= $row['inventory'];							
									$var_lcf					= $row['lcf'];							
									$var_hcf					= $row['hcf'];
									$var_physical_pincode		= $this->Contractpincode_value;
									$var_position_flag			= $row['position_flag'];
									$var_nationalcatid			= $row['national_catid'];
									//$var_latitude				= $row['latitude'];;
									//$var_longitude				= $row['longitude'];;
									$var_bidperday				=$row['bidperday'];
									$var_bidperday_original		=$row['bidperday_original'];

									#$var_significance = $catid_bidperday_arr[$var_bid_catid];
									
									$search_contribution 	= $row['bidperday'];
									$category_perday		= $CategoryPerdayarr[$var_bid_catid]['catid_bidperday'];
									$category_perday_original	= $CategoryPerdayarr[$var_bid_catid]['category_perday_original'];
									
									if($multiplier>=1)
									{
										#$var_significance= $var_significance*$multiplier;
										$search_contribution= $search_contribution*$multiplier;
									}
									
									$var_significance   = $radius_of_significance;
										
									$insert_common_query_zone .= "('".$this->parentid."','".$this->docid."','".$this->companynameval."','".$var_bid_catid."','".$var_nationalcatid."','".$var_pincode."','".$var_bidperday."','".$var_bidperday_original."','".$category_perday."','".$category_perday_original."','".$package_contract_bidperday."','".$package_contract_active_date."','".$data_city."','".$var_physical_pincode."','".$area."','".$this->latitude_value."','".$this->longitude_value."','".$address."','".$duplicate_check_phonenos."','".$dup_groupid."','".$company_callcnt_rolling."','".$var_significance."','".$var_searchpara."','".$searchcriteria."','".$search_contribution."',1,'".$package_category_count."','".$package_pincode_count."','".$this->userid."','".$today."'),";

								}
								

								$zone_ins="INSERT IGNORE  INTO tbl_package_search (parentid,docid,companyname,catid,national_catid,pincode,bidperday,bidperday_original,category_perday,category_perday_original,contract_bidperday,active_date,data_city,physical_pincode,physical_area,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,callcount_rolling,significance,searchpara,searchcriteria,search_contribution,activeflag,category_count,pincode_count,updatedby,updatedon) VALUES";

								
								if($insert_common_query_zone)
								{
									$insert_common_query_zone		= trim($insert_common_query_zone, ',');
									$finalzonequery					=  $zone_ins.$insert_common_query_zone;
								}
								
								# Actual inserts into the MYZONE/ZONE table happen here #
								#########################################################
								//echo "<br>##".$var_balance."##".$var_manualoverride."##".$rowcount;
								// $var_manualoverride==1 && $var_expired==0 dependent campaign handling 
								if(( $var_balance>0 || ($var_manualoverride==1 && $var_expired==0) ) && $rowcount>0 )
								{
									
									$this->activephonesearch = 1; 
																	
								if( $finalzonequery != "" )
								{
						
									$insert_cca_zonequery=$this -> conn_iro->query_sql($finalzonequery);							
									
									$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
										
									$logginquery = $finalzonequery;
									$this->loggintable($logginquery,$removeterms,$var_campaignid);

								}
								
								}

								#echo "<br>var_balance--".$var_balance."## var_manualoverride-".$var_manualoverride."##rowcount--".$rowcount;
								if(( $var_balance<=0 && $var_manualoverride==0) && $rowcount>0 )
								{	# Balance Absent. 
								
									$this->set_nonpaidforcefully(1); // to generate tbl_nonpaid_search 

								}
							
							}
							
						}
						
					}// end of category foreach 	
					
					#echo "<br>count--".count($pack_category_catids_arr)."## var_expired-".$var_expired ;
					if( count($pack_category_catids_arr)==0 &&  $var_expired ==1 )
					{	# package expired and no category . 
					
						$this->set_nonpaidforcefully(1); // to generate tbl_nonpaid_search 
					}	

				} # END OF SUPREME CHECK HERE
				
				
					#getting parent of package categories
					$pack_parent_category_arr = $this -> getParentCategories($pack_category_catids);
					


					// parent category handling - now we will insert it into nonpaid table
					// as pre sunny now we will insert parent catgory into tbl_nonpaid_search instead of tbl_package_search					
					if(($var_campaignid==1||$var_campaignid==2) && $this->purepack_epired==0) 
					{						
						
						$contract_parent_category_arr = (!is_array($ddg_parent_category_arr)?array():$ddg_parent_category_arr) + (!is_array($pack_parent_category_arr)?array():$pack_parent_category_arr);

						//echo "<pre>contract_parent_category_arr"; print_r($contract_parent_category_arr);
						//echo "<br>var_campaignid:".$var_campaignid."--var_pincode:".$var_pincode;

						if(count($contract_parent_category_arr)>0)
						{
							
							$contract_parent_category_catid=array();
							foreach($contract_parent_category_arr as $catid => $catid_arr)
							{
								$contract_parent_category_catid[]=$catid;
							}

							if(count($contract_parent_category_catid))
							{
								$contract_parent_category_string = implode(',',$contract_parent_category_catid);								
								$this->insertinto_tbl_nonpaid_search($contract_parent_category_string);
							}
						}
						
						/*
						if(count($contract_parent_category_arr)>0)
						{	$exclusive=0;
							$totcompdisplay=0;
							foreach($contract_parent_category_arr as $catid => $catid_arr)
							{
								$var_campaignid		 = (count($ddg_parent_category_arr)>0) ? $ddg_campaignid : $pack_campaignid;
								//$var_searchpara1     = (count($ddg_parent_category_arr)>0) ? 1:0;
								
								if($searchcriteria == 1 && $var_campaignid == 2)
								{
									$var_searchpara = 1;
								}
								else
								{
									$var_searchpara = 0;	
								}
									
								$var_bid_catid		 = $catid;
								$var_parentid		 = $parentid;
								$var_bid_bidamt		 = '0';
								$var_searchtype      = $catid_arr['searchtype'];
								$var_nationalcatid   = $catid_arr['national_catid'];
																			
								$exclusive			=$catid_arr['exclusive'];
								$totcompdisplay		=$catid_arr['totcompdisplay'];
								
								if($totcompdisplay==1 || $exclusive==1)
								{// if it is exclusive category it should not enter into myzone table
											continue;
								}
								
								
								if((strtoupper($var_searchtype) == 'Z' ) OR  ( strtoupper($var_searchtype) == 'SZ' ) OR ( strtoupper($var_searchtype)== 'A' ) OR ( strtoupper($var_searchtype) == 'MZ' ) OR ( strtoupper($var_searchtype) == 'NM' ) OR ( strtoupper($var_searchtype) == 'VNM' ))
								{
									if($searchcriteria==1)
									$var_significance=2;
									else
									$var_significance=0;
								}
								else
								{									
									$var_significance =2.0;
								}

								//$ddg_insert_common_query_zone = $ddg_insert_common_query_zone."('".$var_campaignid."','".$var_bid_catid."','".$var_parentid."','".$var_bid_bidamt."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."',".$var_searchpara1.",".$var_significance.",'".$duplicate_check_phonenos."','".$dup_groupid."'),";

								$ddg_insert_common_query_zone = $ddg_insert_common_query_zone."('".$this->parentid."','".$this->docid."','".$var_bid_catid."','".$var_nationalcatid."','".$var_physical_pincode."','".$data_city."','".$var_physical_pincode."','".$this->latitude_value."','".$this->longitude_value."','".$address."','".$duplicate_check_phonenos."','".$dup_groupid."','".$company_callcnt_rolling."','".$var_significance."','".$var_searchpara."','".$searchcriteria."','".$var_bid_bidamt."',1,'".$this->userid."','".$today."'),";

							}
						}
						
						$ddg_zone_ins="INSERT IGNORE INTO tbl_package_search (parentid,docid,catid,national_catid,pincode,data_city,physical_pincode,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,callcount_rolling,significance,searchpara,searchcriteria,search_contribution,activeflag,updatedby,updatedon) VALUES";
						
					
						if($ddg_insert_common_query_zone)
						{
							$ddg_insert_common_query_zone		= trim($ddg_insert_common_query_zone, ',');
							$ddg_finalzonequery					= $ddg_zone_ins.$ddg_insert_common_query_zone;
							//echo $ddg_finalzonequery;
							$this -> conn_iro->query_sql($ddg_finalzonequery);
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
									
							$logginquery = $ddg_finalzonequery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);

							unset($ddg_insert_common_query_zone);
							$this->set_nonpaidforcefully(-1); // no need to generate compcatarea non_paid
						}
						*/

						#END - inserting (ddg and parent categories) of both (package and platinum) campaign into (zone and myzone) of compcatarea
						

						/* Non paid categories to be inserted in tbl_compcatarea_non_paid  STARTS*/
						if(trim($catidlineage_nonpaid,',')!='')
						{

							$this->insertinto_tbl_nonpaid_search($catidlineage_nonpaid);

							/*
							$nationalId = $this->fetch_categorytype($catidlineage_nonpaid);
							foreach(explode(',',$catidlineage_nonpaid) as $value)
							{								
								$sql_nonpaid_insert = "INSERT IGNORE INTO tbl_nonpaid_search (parentid,docid,catid,national_catid,pincode,data_city,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,callcount_rolling,activeflag,updatedby,updatedon) VALUES
								('".$this->parentid."','".$this->docid."','".$value."','".$nationalId[$value]['national_catid']."','".$pincode_value."','".$data_city."','".$this->latitude_value."','".$this->longitude_value."','".$address."','".$duplicate_check_phonenos."','".$dup_groupid."','".$company_callcnt_rolling."',1,'".$this->userid."','".$today."')";								
								$this->conn_iro->query_sql($sql_nonpaid_insert);
							}*/
						}
								
					/* Non paid categories to be inserted in tbl_compcatarea_non_paid  END*/
						
					}
			

					
					if(in_array($var_campaignid,array(17,18,81)))
					{

					
						// removing table deltion query
						//$check_balance_query="SELECT campaignid, expired, balance, manual_override, active_flag FROM tbl_companymaster_finance WHERE campaignid='".$var_campaignid."'";
						$check_balance_query="SELECT campaignid, expired, balance, manual_override, threshold_active_flag,searchcriteria FROM tbl_companymaster_finance WHERE parentid='".$parentid."' AND campaignid='".$var_campaignid."'";
						
						$result_check_balance_query=$this -> conn_finance->query_sql($check_balance_query);

						$row = mysql_fetch_array($result_check_balance_query);
						$var_balance = $row['balance'];
						$var_expired = $row['expired'];
						$var_manualoverride = $row['manual_override'];
						$var_activeflag = $row['threshold_active_flag'];
						$searchcriteriaval = $row['searchcriteria'];
						
						# Fetch PPC Categories #
						########################

						$push_ppc_catquery="SELECT count(parentid) as count FROM tbl_bidcatdetails_lead WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."'";  
						
						$result_push_ppc_catquery = $this -> conn_finance->query_sql($push_ppc_catquery);

						$result_push_ppc_arry= mysql_fetch_assoc($result_push_ppc_catquery);
						$rowcount = $result_push_ppc_arry['count'];
						unset($result_push_ppc_catquery);
						unset($push_ppc_catquery);

						IF ($rowcount<=0)
						{

							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Invalid campaignid. No entries found in tbl_bidcatdetails_ppc. Please check and enter again...File EXITED.')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

						}
						else
						{

							unset($insert_common_query_nonpaid);
							$lead_category_catids = $this -> getContractsCategory($parentid,'tbl_bidcatdetails_lead');
							
							if($var_campaignid == 18)
							{
								$lead_category_catids_arr = $this -> fetch_categorytype($lead_category_catids,1);
							}
							if($var_campaignid == 17 || $var_campaignid == 81)
							{
								$lead_category_catids_arr = $this -> fetch_categorytype($lead_category_catids);
							}
							
						
							$lead_category_catids_arr= array_filter($lead_category_catids_arr);
							if(count($lead_category_catids_arr)>0)
							{
								foreach($lead_category_catids_arr as $cat_key =>$cat_value)
								{
							   
								if(intval($cat_key)<=0)
								{
									continue;
								}
								
								$insert_common_query_ppc = '';
								$insert_common_query_nonpaid ='';
								$finalppcquery ='';
								
								$push_ppc_catquery="SELECT campaignid,bid_catid,bid_zone,parentid,pincode,bid_bidamt,grace_amt,uptdate,nationalcatid FROM tbl_bidcatdetails_lead WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."' and bid_catid='".$cat_key."'"; 
								
								$result_push_ppc_catquery = $this -> conn_finance->query_sql($push_ppc_catquery);
								
								
								if(mysql_num_rows($result_push_ppc_catquery)>0)
								{
								
									WHILE ($row = mysql_fetch_array($result_push_ppc_catquery))
									{
									$var_campaignid				= $row['campaignid'];
									$var_bid_catid				= $row['bid_catid'];
									$var_bid_zone				= $row['bid_zone'];
									$var_parentid				= $row['parentid'];
									$var_pincode				= $row['pincode'];
									$var_bid_bidamt				= $row['bid_bidamt'];
									$var_grace_amt				= $row['grace_amt'];
									$var_uptdate				= $row['uptdate'];
									$var_nationalcatid			= $lead_category_catids_arr[$row['bid_catid']]['national_catid'];

									$insert_common_query_ppc .= "('".$var_campaignid."','".$var_bid_catid."','".$var_bid_zone."','".$var_parentid."','".$var_pincode."','".$var_bid_bidamt."','".$var_grace_amt."','".$var_nationalcatid."','".$var_expired."','".$var_activeflag."','".$phone_search."','".$address."','".$area."','".$regionid."','".$this->latitude_value."','".$this->longitude_value."','".$email_search."','".$this->userid."','".$today."','".$data_city."','".$searchcriteriaval."','".$duplicate_check_phonenos."','".$pincode_value."','".$dup_groupid."'),";
									$insert_common_query_nonpaid .= "('".$var_bid_catid."','".$var_parentid."','".$pincode_value."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$this->latitude_value."','".$this->longitude_value."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."','".$dup_groupid."'),";
									}

									$ppc_ins="INSERT INTO tbl_compcatarea_ppc(campaignid,bid_catid,bid_zone,parentid,pincode,bid_bidamt,grace_lead_amt,nationalcatid,expired,activeflag,phonenos, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,searchcriteria,duplicate_check_phonenos,physical_pincode,dup_groupid) VALUES";								
									
									if($insert_common_query_ppc)
									{
										$insert_common_query_ppc		= trim($insert_common_query_ppc, ',');
										$finalppcquery					=  $ppc_ins.$insert_common_query_ppc;

									}
									
									
									# Actual inserts into the PPC table happen here #
									#################################################
									
									IF ( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 ){							# Balance Absent. Delete from CCA_PPC and push data into CCA_PPC

										$insert_cca_ppcquery=$this -> conn_iro->query_sql($finalppcquery);
										
										$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
								
										$logginquery = $finalppcquery;
										$this->loggintable($logginquery,$removeterms,$var_campaignid);
						

									}
								}
								
							}
								// end of foreach 
								// after loging of all contract we have to insert into tbl_clients_package_contribution tbl_companymaster_finance tbl_compcatarea_regen_log

								
						}
					}
				} # END OF C/H CHECK HERE
			
			} # WHILE Ends here

			
		$removetermsstr = implode(",",$removeterms);
		$companyDetails_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$this->parentid."','companyDetails1-" .addslashes($removetermsstr)."')";
		$result_insert_log_query=$this -> conn_finance->query_sql($companyDetails_log_query);

		} # ELSE Ends here
		
		
		
		$check_contracts_query_nat="SELECT campaignid FROM tbl_companymaster_finance_national WHERE parentid='".$parentid."' order by campaignid";
		$result_check_contracts_query_nat=$this -> conn_national->query_sql($check_contracts_query_nat);
		
		$rowcount_nat = mysql_num_rows($result_check_contracts_query_nat);

		IF ($rowcount_nat<=0)
		{

			$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Parentid found in tbl_companymaster_finance_national.')";
			$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

			return 0;
		}
		else
		{
			WHILE ($row_nat = mysql_fetch_array($result_check_contracts_query_nat)){
			$var_campaignid = $row_nat['campaignid'];
			$searchcriteria = 0; // by default it is 0 it will be changed as per campaignid
			
			$today = date("Y-m-d H:i:s");	
			IF ($var_campaignid == 21)
			{

				unset($myzone_ins);
				unset($zone_ins);
				unset($finalmyzonequery);
				unset($finalzonequery);
				
				$ddg_campaignid = $var_campaignid;
				// removing all delete queries
				
				//contract_bidperday
				$pdg_contract_bidperday   = $companymaster_finance_arr[21]['budget']/$companymaster_finance_arr[21]['duration'];

				$row = $companymaster_finance_arr[21];

				$var_balance = $row['balance'];
				$var_expired = $row['expired'];
				$var_manualoverride = $row['manual_override'];
				$var_activeflag = $row['threshold_active_flag'];
				$searchcriteria = $row['searchcriteria'];
				$exclusive_flag = $row['exclusivelisting_tag'];
				
				
				$national_pdg_contract_active_date = $this->getApprovalDate($this->parentid,$row['version']);

				# Fetch National DDG Categories #
				########################
				$category_catids = $this -> getContractsCategoryNew($parentid,'tbl_bidding_details_national',$var_campaignid);
				//echo "<pre>category_catids--";print_r($category_catids);
				//echo '<br>category_catids--'.$category_catids;
				//$this->pdgarraylist = explode(',',$category_catids);				
				$category_catids_arr = $this -> fetch_categorytype($category_catids); 
				$rowcount = count($category_catids_arr);
				if($rowcount<=0) {

							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Entries not found in tbl_bidding_details.')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
							$this->set_nonpaidforcefully(1);
				}
				else
				{	
					
					$pdg_category_count = count(array_filter(explode(',', $category_catids)));
					$ContractpdgPincode = $this->getContractPincode($parentid,'tbl_bidding_details_national',$var_campaignid);
					$pdg_pincode_count 	   = count(array_filter(explode(',', $ContractpdgPincode)));
										
					unset($insert_common_query_nonpaid);

					foreach($category_catids_arr as $cat_key =>$cat_value)
					{
						if(intval($cat_key)<=0)
						{
							continue;
						}
						$insert_common_query_ddg = '';
						$finalddgquery = '';
						$insert_common_query_nonpaid ='';
				
						$push_ddg_catquery="SELECT catid as bid_catid ,national_catid,pincode,position_flag,inventory,lcf,hcf,bidperday,actual_budget/duration as bidperday_original, pincity FROM tbl_bidding_details_national WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."' and catid='".$cat_key."'";
					   
					   $result_push_ddg_catquery= $this -> conn_national -> query_sql($push_ddg_catquery);
					   
					   while($row = mysql_fetch_array($result_push_ddg_catquery))
					   {
						   //echo '2000<pre>';print_r($row);die;
						   $tbl_client_ddg_contres =null;
						   							
							$var_bid_catid				= $row['bid_catid'];
							$var_pincode				= $row['pincode'];							
							$var_inventory				= $row['inventory'];							
							$var_lcf					= $row['lcf'];							
							$var_hcf					= $row['hcf'];
							$var_physical_pincode		= $this->Contractpincode_value;
							$var_position_flag			= $row['position_flag'];
							$var_nationalcatid			= $row['national_catid'];;
							//$var_latitude				= $row['latitude'];;
							//$var_longitude				= $row['longitude'];;
							//echo "<br>var_position_flag-".$var_position_flag."<br>";
							$pdgcatbidperday 			= $row['bidperday'];
							$pdgcatbidperday_original 			= $row['bidperday_original'];
							$pincity					= $row['pincity'];	
							$pdgcategory_perday			= $CategoryPerdayarrNat[strtolower($pincity)][$var_bid_catid]['catid_bidperday'];
							$pdgcategory_perday_original= $CategoryPerdayarrNat[strtolower($pincity)][$var_bid_catid]['category_perday_original'];
							
							$pdg_contract_bidperday 	= ( $companymaster_finance_arr[21]['balance'] > 0 &&  $companymaster_finance_arr[10]['balance'] > 0 ) ? ($companymaster_finance_arr[21]['bid_perday'] + $companymaster_finance_arr[10]['bid_perday']) : $companymaster_finance_arr[21]['bid_perday'];
							
							$pdg_contract_active_date 	= $national_pdg_contract_active_date;
							
							$searchcol= "pos".$var_position_flag."_cum_inventory as search_cum_ddg_ratio , pos".$var_position_flag."_cum_callcount as search_cum_callcount ,pos".$var_position_flag."_contribution as search_contribution ";

							/*if($var_position_flag==3||$var_position_flag==4||$var_position_flag==5||$var_position_flag==6||$var_position_flag==7)
							{
								$ddg_contsqlcond="bronze_cumulative_callcount as cumulative_callcount, bronze_cumulative_ddg_ratio as cumulative_ddg_ratio, bronze_contribution as contribution ";
							}
							elseif($var_position_flag==2)
							{
								$ddg_contsqlcond="diamond_cumulative_callcount as cumulative_callcount, diamond_cumulative_ddg_ratio as cumulative_ddg_ratio, diamond_contribution as contribution ";
							}
							elseif($var_position_flag==1)
							{
								$ddg_contsqlcond="platinum_cumulative_callcount as cumulative_callcount, platinum_cumulative_ddg_ratio as cumulative_ddg_ratio, platinum_contribution as contribution ";
							}
							*/
							$search_cum_callcount= 1;
							$search_cum_ddg_ratio= '0.0000000';
							$search_contribution = '0.0000000';	
							//echo "catid= ".$var_bid_catid."-  pincode=".$var_pincode ."^^ search_cum_callcount=".$search_cum_callcount." ^^search_cum_ddg_ratio=".$search_cum_ddg_ratio."^^search_contribution=".$search_contribution;
								
							$insert_common_query_ddg .= "('".$this->parentid."','".$this->docid."','".$this->companynameval."','".$var_bid_catid."','".$var_nationalcatid."','".$var_pincode."','".$var_position_flag."','".$var_inventory."','".$var_lcf."','".$var_hcf."','".$pdgcatbidperday."','".$pdgcatbidperday_original."','".$pdgcategory_perday."','".$pdgcategory_perday_original."','".$pdg_contract_bidperday."','".$pdg_contract_active_date."','".$data_city."','".$pincity."','".$var_physical_pincode."','".$area."','".$this->latitude_value."','".$this->longitude_value."','".$duplicate_check_phonenos."','".$dup_groupid."','".$search_cum_callcount."','".$search_cum_ddg_ratio."','".$search_contribution."','".$pdg_category_count."','".$pdg_pincode_count."','".$this->userid."','".$today."'),";

							
						}
						
						$ddg_ins="INSERT IGNORE INTO tbl_fp_search_national(parentid,docid,companyname,catid,national_catid,pincode,position_flag,inventory,lcf,hcf,bidperday,bidperday_original,category_perday,category_perday_original,contract_bidperday,active_date,data_city,pincity,physical_pincode,physical_area,latitude,longitude,duplicate_check_phonenos,groupid,search_cum_callcount,search_cum_ddg_ratio,search_contribution,category_count,pincode_count,updatedby,updatedon) VALUES";
						
						if($insert_common_query_ddg)
						{
							$insert_common_query_ddg		= trim($insert_common_query_ddg, ',');
							$finalddgquery		=  $ddg_ins.$insert_common_query_ddg;
						}						
						
						if ( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{						# Balance Absent. Delete from CCA_DDG and push data into CCA_DDG
							if(strlen(trim($finalddgquery))!=0) // if query is created then only write into table
							{
								$insert_cca_ddgquery=$this -> conn_national -> query_sql($finalddgquery);
								//echo "<br>".$finalddgquery;
								
								$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
								$logginquery = $finalddgquery;
								$this->loggintable($logginquery,$removeterms,$var_campaignid);
							}
						}
						
						if(($var_balance<=0 && $var_manualoverride==0) && $rowcount>0 )
						{
							//$this->set_nonpaidforcefully(1); // to generate tbl_nonpaid_search 
						}

						}						
						/* Non paid categories to be inserted in tbl_nonpaid_search  STARTS*/				
						
						/* Non paid categories to be inserted in tbl_nonpaid_search  STARTS*/
						# Actual inserts into the DDG table happen here #
						#################################################
			
				}
				
				} # END OF National DDG CHECK
			}
		}
		
		
			
	} # Paid =1 ENDS Here
	
	// instead of else we are checking two conditions 
	#echo "<br>paid_value-- - ".$paid_value ."---nonpaidforcefully---". $this->nonpaidforcefully;
	
	if($paid_value == 0 || $this->nonpaidforcefully>0)
	{ # NonPaid STARTS Here

		$low_rankingmsg="";
		if($low_ranking==1)
		{$low_rankingmsg=" low_ranking=".$low_ranking; }
		
		$purepack_epired_msg="";
		if($this->purepack_epired==1)
		{$purepack_epired_msg=" purepack_epired =".$this->purepack_epired; }
		
		
		$nonpaid_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','tbl_nonpaid_search generation, flag name and value, paid=".$paid_value.$low_rankingmsg.$purepack_epired_msg."  nonpaidforcefully_generation=".addslashes($this->nonpaidforcefully)."')";
			$result_insert_log_query=$this -> conn_finance->query_sql($nonpaid_log_query);
			
		// we are deleting in start of function call so this is not requred 	
		//$delete_nonpaid_query="DELETE FROM tbl_nonpaid_search where parentid='".$parentid."'";
		//$result_delete_nonpaid_query=$this -> conn_iro->query_sql($delete_nonpaid_query);

		//echo "NonPaid:".$delete_nonpaid_query;		
		$category_array = $this ->fetch_categorytype( $catidlineage_value);
		$today = date("Y-m-d H:i:s");

		$tbl_nonpaid_searchactiveflag=1;
		/*
		if($this->nonpaidentryforpdg==1)
		{
			$tbl_nonpaid_searchactiveflag=2;
		}
		*/
		$category_array= array_filter($category_array);
		$logcatid_array = array();
		
		if(count($category_array)>0)
		{
			
			foreach($category_array as $catid => $catid_value)
			{
				$national_catid = $catid_value['national_catid'];
				$logcatid_array[] = $catid;	
				$insert_common_query_purenonpaid = $insert_common_query_purenonpaid."('".$this->parentid."','".$this->docid."',".$catid.",".$national_catid.",'".$pincode_value."','".$data_city."','".$this->latitude_value."','".$this->longitude_value."','".$address."','".$duplicate_check_phonenos."','".$dup_groupid."','".$company_callcnt_rolling."',".$tbl_nonpaid_searchactiveflag.",'".$this->userid."','".$today."'),";
			}
		}
		if($insert_common_query_purenonpaid)
		{
			$purenonpaid_ins="INSERT IGNORE INTO tbl_nonpaid_search (parentid,docid,catid,national_catid,pincode,data_city,latitude,longitude,fulladdress,duplicate_check_phonenos,groupid,callcount_rolling,activeflag,updatedby,updatedon) VALUES";
			$insert_common_query_purenonpaid	= trim($insert_common_query_purenonpaid, ',');
			$finalpurenonpaidquery				= $purenonpaid_ins.$insert_common_query_purenonpaid;

			//echo "FinalQ".$finalpurenonpaidquery;
			$result_finalpurenonpaidquery=$this -> conn_iro->query_sql($finalpurenonpaidquery);
			 
			$removeterms=array("'".$this->parentid."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$this->latitude_value."'","'".$this->longitude_value."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."','".$dup_groupid."'");
								
			//$logginquery = $finalpurenonpaidquery;
			$logginquery = implode(",",$logcatid_array);
			$logginquery ="tbl_nonpaid_search catid ". addslashes($logginquery);
			
			$this->loggintable($logginquery,null,0);
			
			$removetermsstr = implode(",",$removeterms);
			$companyDetails_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$this->parentid."','companyDetails-" .addslashes($removetermsstr)."')";
			$result_insert_log_query=$this -> conn_finance->query_sql($companyDetails_log_query);			
		}
				

	} # Rowcount ELSE ENDS Here
	
	

	
	//echo '<br>activephonesearch--'.$this->activephonesearch;
	
	if($this->activephonesearch==1)
	{
		
		//echo '<pre> pdgarraylist'; print_r($this->pdgarraylist);		
		$this->pdgarraylist= array_filter($this->pdgarraylist);
		//echo '<pre> pdgarraylist'; print_r($this->pdgarraylist);		
		if(count($this->pdgarraylist))
		{
			// activeflag=5 :  Pseudo Package  (pdg intry inside nonpaid )
			$updateactivaflagsql= " update tbl_nonpaid_search set activeflag=5 where parentid='".$this->parentid."' and catid in (".implode(',',$this->pdgarraylist).") ";		
			$this->conn_iro->query_sql($updateactivaflagsql);
		}
		
		if(strlen($catidlineage_nonpaid)>1)
		{
			
			$catidlineage_nonpaid_arr= explode(',',$catidlineage_nonpaid);
			$catidlineage_nonpaid_arr = array_unique($catidlineage_nonpaid_arr);
			$catidlineage_nonpaid_arr = array_filter($catidlineage_nonpaid_arr);
			
			$catidlineage_nonpaid_arr_pdg = array_diff($catidlineage_nonpaid_arr,$this->pdgarraylist);
			
			//echo '<pre> catidlineage_nonpaid_arr_pdg'; print_r($catidlineage_nonpaid_arr_pdg);		
			// updateing  Activeflag=2: Nonpaid categories of paid active client (pdg + package )
			if(count($catidlineage_nonpaid_arr_pdg)>0)
			{
				$updateactivaflagsql= " update tbl_nonpaid_search set activeflag=2 where parentid='".$this->parentid."' and catid in (".implode(',',$catidlineage_nonpaid_arr_pdg).") ";		
				$this->conn_iro->query_sql($updateactivaflagsql);
				//echo $updateactivaflagsql;
			}
		}
		
	}
	
	$this->ProcessShopFrontGuarantee();
	$this->update_primary_tag();
//echo "<br> EXIT in compcatarea_regen.php"; exit;
	return;
	}

}

?>
