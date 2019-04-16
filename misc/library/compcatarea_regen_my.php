<?php

# Requisites Check: #
#####################

#	CREATE TABLE tbl_compcatarea_regen_log(id int primary key auto_increment, parentid varchar(60), campaignid varchar(60), message TEXT, `backenduptdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);
#	ALTER TABLE tbl_compcatarea_regen_log add index(parentid), add index(campaignid), add index(message(15));
require_once(APP_PATH."library/categoryMaster.php");

class compcatarea_regen
{

	private $parentid, $module, $userid, $conn_iro, $conn_local, $conn_finance;
	private $compcatarea_gen_called;
	private $nonpaidforcefully;
	private $purepack_epired;
	private $log_tbl;
	private $ContractsAllCategoryBefore;
	private $paidflag;
	
	
	//we found cases when wh have entry in companymaster finace against campaign but associated category table(bidcatdetails ) does not have category then we will 
	// generate compcatarea nonpaid for that contract
	
	
	
	public function __construct($parentid,$dbarr,$userid){
		$this->parentid		= $parentid;
		$this->userid		= $userid;		
		$this->conn_iro		= new DB($dbarr['DB_IRO']);	
		$this->conn_local	= new DB($dbarr['LOCAL']);	
		$this->conn_finance	= new DB($dbarr['FINANCE']);
		$this->conn_national = new DB($dbarr['DB_NATIONAL']);	
		$this->nonpaidforcefully=0;
		$this->purepack_epired=0; 
		if($userid=="dbbackend")
		{$this->log_tbl =" tbl_compcatarea_regen_log_dbbackend " ; } // backend process log
		else
		{$this->log_tbl =" tbl_compcatarea_regen_log " ; } // application log
	
		$this->ContractsAllCategoryBefore =$this->getContractsAllCategory($parentid);	
	}
		
	function __destruct(){
		$this->updateCategoryCount();
		unset($this->conn_iro);
		unset($this->conn_local);
		unset($this->conn_finance);
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
			$this->updateCity($AllCatidtouched);
		}
		$this->updatepaidnonpaidcount($AllCatidtouched);
		
		if($_SERVER['REMOTE_ADDR']=="172.29.5.22" && 0)
		{die("die in compcatarea");}
	}



	function updateCity($Catidarr)
	{		
		if(!defined("REMOTE_CITY_MODULE")) 
		return ; //this process has to run only remote city
		
		if(count($Catidarr)==0)
		return; // if there is no category no need to process
		
		$Catidstr=implode(",",$Catidarr);
		
		$updatecity = "update tbl_category_freetext a join (
		select bid_catid as catid,replace(trim(',' from GROUP_CONCAT(data_city ORDER BY data_city)),' ','') as city from (
		select distinct bid_catid, data_city from db_iro.tbl_compcatarea_ddg where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_compcatarea_zone where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid, data_city from db_iro.tbl_compcatarea_myzone where bid_catid in (".$Catidstr.")
		union
		select distinct bid_catid,data_city from db_iro.tbl_compcatarea_non_paid where bid_catid in (".$Catidstr.")
		)a GROUP BY bid_catid ) b on a.catid=b.catid set a.city=b.city";
		$this -> conn_local->query_sql($updatecity);
	}


	function updatepaidnonpaidcount($Catidarr)
	{	
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
		select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ddg where bid_catid in (".$Catidstr.")   
		union
		select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
		union
		select distinct parentid,bid_catid from db_iro.tbl_compcatarea_zone where bid_catid in (".$Catidstr.")
		union
		select distinct parentid,bid_catid from db_iro.tbl_compcatarea_myzone where bid_catid in (".$Catidstr.") )a group by bid_catid ";
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
			select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ddg where bid_catid in (".$Catidstr.")   
			union
			select distinct parentid,bid_catid from db_iro.tbl_compcatarea_ppc where bid_catid in (".$Catidstr.")
			union
			select distinct parentid,bid_catid from db_iro.tbl_compcatarea_zone where bid_catid in (".$Catidstr.")
			union
			select distinct parentid,bid_catid from db_iro.tbl_compcatarea_myzone where bid_catid in (".$Catidstr.") )inertbl group by bid_catid)b on a.catid=b.bid_catid  set paid_clients=paidcount";
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
		$sql = "select count(distinct parentid) as nonpaidcount,bid_catid from db_iro.tbl_compcatarea_non_paid where bid_catid in (".$Catidstr.") group by bid_catid ";
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
			select count(distinct parentid) as nonpaidcount,bid_catid from db_iro.tbl_compcatarea_non_paid where bid_catid in (".$Catidstr.") group by bid_catid)b on a.catid=b.bid_catid  set nonpaid_clients=nonpaidcount";
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
		
		//logging into another table
		//$category_count =" nonpaid categories->".$category_count_nonpaid." paid catgories ->".$category_count_paid;
		//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$this->parentid."','".addslashes($category_count)."')";
		//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
		
		
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
	}
	
	function getContractsAllCategory($parentid)
	{
		$tableArr = array('tbl_compcatarea_ddg','tbl_compcatarea_zone','tbl_compcatarea_myzone','tbl_compcatarea_non_paid','tbl_compcatarea_ppc');
		
		$categoryArr = array();
		$categoryArr['paidcatid'] = array();
		$categoryArr['nonpaidcatid'] = array();
		
		foreach($tableArr as $tablename)
		{
			$sql = "SELECT group_concat( DISTINCT bid_catid) as catids  FROM ".$tablename." where parentid='".$parentid."' group by parentid";
			$res = $this -> conn_iro->query_sql($sql);
			$row = mysql_fetch_assoc($res);
			if($row['catids'])
			{
				if($tablename=='tbl_compcatarea_non_paid')
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
		//echo "<pre>categoryArr"; print_r($categoryArr);
		return $categoryArr;
	}

	function fetch_categorytype($catids, $c2c=0)
	{
		$catid_array = array();
		if(trim($catids)!="")
		{
			$catidstemparr=explode(",",$catids);
			$catidstemparr=array_unique($catidstemparr);
			$catidstemparr=array_filter($catidstemparr);
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
		$sql = "SELECT group_concat( DISTINCT bid_catid) as catids  FROM ".$tbl_bidcatdetails." where parentid='".$parentid."' group by parentid";
		$res = $this -> conn_finance->query_sql($sql);
		$row = mysql_fetch_assoc($res);
		if($row['catids'])
		{
			return $row['catids'];
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
		
		/*$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_catfilters where fcatid in (".$category_catids.") and catid>0 and catname!='' and catname!=fcatname ";
		$res = $this -> conn_local->query_sql($sql);
		$final_parent_category_arr = array();
		if($res && mysql_num_rows($res))
		{
			$row = mysql_fetch_assoc($res);
			if($row['parent_categories'])
			{
				$passed_category_arr = explode(',',$category_catids);
				$mix_category_arr	 = explode(',',$row['parent_categories']);
				$parent_category_arr = array_diff($mix_category_arr,$passed_category_arr);
				$parent_category	 = implode(',',$parent_category_arr);
				$final_parent_category_arr = $this -> fetch_categorytype($parent_category);

				return $final_parent_category_arr;
			}
		}
		*/
	}

function loggintable($loggingquery=null , $removefromquery = null,$log_campaignid=0)
{
//echo "<br>removefromquery";
//print_r($removefromquery);

if ($loggingquery== null)
{ return true; } // blank query so no need to do any thing

if(is_array($removefromquery))
{	
	$removefromquery[]=",,"; // removing unwanted comma
	// replacing all the parameters which are to remove
	foreach($removefromquery as $key=>$removingstr)
	{
		//echo "<br>removingstr--".$removingstr."<br>";
		$loggingquery = str_replace($removingstr,"",$loggingquery);		
	}
	//echo $loggingquery;exit;
	if($loggingquery)
	{ 
		//$this->conn_finance->query_sql(addslashes($loggingquery)); 
		
		$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$this->parentid."','".$log_campaignid."','".addslashes($loggingquery)."')";
		$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
	}
	
}

unset($loggingquery);unset($removefromquery );
	
}
	
function deleteTable()
{
	$parentid = $this->parentid;
	$delete_ddg_query="DELETE FROM tbl_compcatarea_ddg where parentid='".$parentid."'";
	$result_delete_ddg_query=$this -> conn_iro->query_sql($delete_ddg_query);
	
	$delete_myzone_query="DELETE FROM tbl_compcatarea_zone where parentid='".$parentid."'";
	$result_delete_myzone_query=$this -> conn_iro->query_sql($delete_myzone_query);

	$delete_zone_query="DELETE FROM tbl_compcatarea_myzone where parentid='".$parentid."'";
	$result_delete_zone_query=$this -> conn_iro->query_sql($delete_zone_query);

	$delete_ppc_query="DELETE FROM tbl_compcatarea_ppc where parentid='".$parentid."'";
	$result_delete_ppc_query=$this -> conn_iro->query_sql($delete_ppc_query);
	$delete_nonpaid_query="DELETE FROM tbl_compcatarea_non_paid where parentid='".$parentid."'";
	$result_delete_nonpaid_query=$this -> conn_iro->query_sql($delete_nonpaid_query);

	$delete_query ="DELETE Entries FROM compcatarea table";

	$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($delete_query)."')";
	$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
	}
	
	function freezemask()
	{
		$parentid = $this->parentid;
		$today = date("Y-m-d H:i:s");
		$freeze_sql ="SELECT freeze,mask,closedown_flag from tbl_companymaster_extradetails WHERE  parentid = '".$this->parentid."'";
		$result_freeze_sql=$this -> conn_iro->query_sql($freeze_sql);
		
		if(mysql_num_rows($result_freeze_sql)>0)
		{
			$freeze =0;
			$mask 	=0;
			$closedown_flag=0;
			
			$result_freeze_arr = mysql_fetch_assoc($result_freeze_sql);
			$freeze  = $result_freeze_arr[freeze];
			$mask 	= $result_freeze_arr[mask];
			$closedown_flag =  $result_freeze_arr[closedown_flag];
			
			$update_freezemask_query = "UPDATE tbl_companymaster_finance SET freeze=".$freeze.",mask=".$mask." WHERE parentid = '".$parentid."'"; //  to make freeze and mask field in sync with extrdetails
			$this -> conn_finance->query_sql($update_freezemask_query);
			
			//$insert_freezemask_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes(" Synchronizing freeze mask of tbl_companymaster_finance with tbl_companymaster_extradetails ".$update_freezemask_query)."')";
			//$this -> conn_finance->query_sql($insert_freezemask_query);
			
			if($freeze || $mask || ($closedown_flag==1)) // if contract is either freeze or masked or closedown
			{			
			
				$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Masked/Frozen/Closed.freeze=".$freeze." ,mask=".$mask." , closedown_flag=".$closedown_flag."')";
				$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				$this->deleteTable(); // calling function to delete entry from table
				
				// now updating all relevant flags
				
				$update_clients_package_query = "UPDATE tbl_clients_package_contribution SET updateddate=now() WHERE parentid = '".$parentid."'";
				$result_update_clients_package_query = $this -> conn_finance->query_sql($update_clients_package_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_clients_package_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

				$update_clients_perday_query = "UPDATE tbl_companymaster_finance SET active_flag=0, updatedOn='".$today."' WHERE parentid = '".$parentid."' AND campaignid not in (2,13,14,15)"; //  deduction of ddg and catspon contracts should be continue even it is masked or frozen -- by Rajeevkrisna nair 
				$result_update_clients_perday_query = $this -> conn_finance->query_sql($update_clients_perday_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_clients_perday_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				// nayional listing part 
				
				$update_national_listing_query = "UPDATE tbl_national_listing SET activeflag=0,update_flag=0 WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_national_listing_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				$update_national_listing_query = "UPDATE tbl_companymaster_finance_national SET active_flag=0  WHERE parentid = '".$parentid."' AND campaignid not in (2,13,14,15) ";
				$this -> conn_national->query_sql($update_national_listing_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_national_listing_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				// nayional listing part end
				
				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Entries successfully deleted from COMPCATAREA Tables. File EXITED.')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

				return 0;
			}		
			else // when contract is neither freeze nor masked
			{
				
				$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','UnMasked/unFreeze/nonclosed..freeze=".$freeze." ,mask=".$mask.",closedown_flag=".$closedown_flag."')";
				$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
								
				$update_clients_package_query = "UPDATE tbl_clients_package_contribution SET updateddate=now() WHERE parentid = '".$parentid."'";
				$result_update_clients_package_query = $this -> conn_finance->query_sql($update_clients_package_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_clients_package_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

				$update_clients_perday_query = "UPDATE tbl_companymaster_finance SET active_flag=1, updatedOn='".$today."' WHERE parentid = '".$parentid."' ";
				$result_update_clients_perday_query = $this -> conn_finance->query_sql($update_clients_perday_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_clients_perday_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				// nayional listing part start 				
				$update_national_listing_query = "UPDATE tbl_national_listing SET activeflag=1,update_flag=0 WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_national_listing_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				$update_national_listing_query = "UPDATE tbl_companymaster_finance_national SET active_flag=1  WHERE parentid = '".$parentid."' ";
				$this -> conn_national->query_sql($update_national_listing_query);

				//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($update_national_listing_query)."')";
				//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
				
				// nayional listing part end

				return 1;	
			
			}			
		}
	}
	
	function compcatarea_gen()
	{
		$function_status = -1;
		/* this variable is going to tell whether this has been completed sucessfully or not it by default it is -1 we change its value so that */
		# Query Companymaster Table for mask/freeze status #
		####################################################
		$parentid = $this -> parentid;
		$freeze_query="SELECT a.sphinx_id, a.nationalid, a.parentid, b.freeze, b.mask, a.paid,a.data_city, substring(a.parentid,2) as nonpaid_contractid, a.pincode, 1 as activeflag,a.company_callcnt,a.landline_display, replace(b.catidlineage_search,'/','') as catidlineage, replace(b.catidlineage_nonpaid,'/','') as catidlineage_nonpaid,c.phone_search,c.address,a.area,c.regionid,c.latitude,c.longitude,c.email as email_search,low_ranking FROM tbl_companymaster_generalinfo a join tbl_companymaster_extradetails b join tbl_companymaster_search c ON a.parentid = b.parentid and b.parentid=c.parentid WHERE  a.parentid = '".$parentid."'";
		$result_freeze_query=$this -> conn_iro->query_sql($freeze_query);

		$rowcount = mysql_num_rows($result_freeze_query);

		IF ($rowcount<=0) {

			$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','Parentid not found on companymaster table... Please check and enter again...File EXITED.')";
			$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
			//echo "<br>Process Exited. Check tbl_compcatarea_regen_log for details.";

			return 0;
		}
		
		$row	= mysql_fetch_assoc($result_freeze_query);

		$freeze_value		= $row['freeze'];
		$mask_value			= $row['mask'];
		//$paid_value			= $row['paid'];
		//$contractid_value	= $row['nonpaid_contractid'];
		$pincode_value		= $row['pincode'];
		$activeflag_value	= $row['activeflag'];
		$catidlineage_value	= $row['catidlineage'];
		$catidlineage_nonpaid= $row['catidlineage_nonpaid'];
		$phone_search		= addslashes(stripslashes($row['phone_search']));;
		$address			= addslashes(stripslashes($row['address']));
		$area				= addslashes(stripslashes($row['area']));
		$regionid			= $row['regionid'];
		$latitude			= $row['latitude'];
		$longitude			= $row['longitude'];
		$email_search		= addslashes(stripslashes($row['email_search']));
		$data_city 			= $row['data_city'];
        $nonpaid_callcount  = $row['company_callcnt'];
        $low_ranking  		= $row['low_ranking'];
		$duplicate_check_phonenos = addslashes(stripslashes($row['landline_display']));
		
		$today = date("Y-m-d H:i:s");

		
		$pack_parent_category_arr = array();
		$ddg_parent_category_arr = array ();		
		

		
		$check_paid_entry="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' and campaignid in (1,2,17,18) order by campaignid";
		$res_paid_entry=$this -> conn_finance->query_sql($check_paid_entry);
		
		$rowpaidcount = mysql_num_rows($res_paid_entry);
		
		if($rowpaidcount>0)
		{
			$paid_value = 1;
		}
		else
		{
			$paid_value = 0;
		}
		
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

			return 0;
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
				$category_catids = $this -> getContractsCategory($parentid,'tbl_bidcatdetails_ddg');
				$category_catids_arr = $this -> fetch_categorytype($category_catids); 
				$rowcount = count($category_catids_arr);
				if($rowcount<=0) {

							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Entries not found in tbl_bidcatdetails_ddg.')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
							$this->nonpaidforcefully=1;
				}
				else
				{
					unset($insert_common_query_nonpaid);

					foreach($category_catids_arr as $cat_key =>$cat_value)
					{
						if(intval($cat_key)<=0)
						{
							continue;
						}
						$insert_common_query_ddg = '';
						$insert_common_query_nonpaid ='';
					  
						$push_ddg_catquery="SELECT campaignid,bid_catid,bid_zone,parentid,pincode,bid_bidamt,position_flag,uptdate,partial_ddg_ratio,partial_ddg_ratio_cum,nationalcatid FROM tbl_bidcatdetails_ddg WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."' and bid_catid='".$cat_key."'";
					   
					   $result_push_ddg_catquery=$this -> conn_finance->query_sql($push_ddg_catquery);
					  
					   while($row = mysql_fetch_array($result_push_ddg_catquery))
					   {
							$var_campaignid				= $row['campaignid'];
							$var_bid_catid				= $row['bid_catid'];
							$var_bid_zone				= $row['bid_zone'];
							$var_parentid				= $row['parentid'];
							$var_pincode				= $row['pincode'];
							$var_bid_bidamt				= $row['bid_bidamt'];
							$var_position_flag			= $row['position_flag'];
							$var_uptdate				= $row['uptdate'];
							$var_partial_ddg_ratio		= $row['partial_ddg_ratio'];
							$var_partial_ddg_ratio_cum	= $row['partial_ddg_ratio_cum'];
							$var_nationalcatid			= $category_catids_arr[$row['bid_catid']]['national_catid'];

							if($var_position_flag==8)
							{
								$ddg_contsqlcond="bronze_cumulative_callcount as cumulative_callcount, bronze_cumulative_ddg_ratio as cumulative_ddg_ratio, bronze_contribution as contribution ";
							}
							elseif($var_position_flag==10)
							{
								$ddg_contsqlcond="diamond_cumulative_callcount as cumulative_callcount, diamond_cumulative_ddg_ratio as cumulative_ddg_ratio, diamond_contribution as contribution ";
							}
							elseif($var_position_flag==15)
							{
								$ddg_contsqlcond="platinum_cumulative_callcount as cumulative_callcount, platinum_cumulative_ddg_ratio as cumulative_ddg_ratio, platinum_contribution as contribution ";
							}

							$tbl_client_ddg_contsql= "SElECT ".$ddg_contsqlcond. " from tbl_clients_ddg_contribution where parentid='".$var_parentid."' AND bid_catid=".$var_bid_catid." AND pincode=".$var_pincode;
							
							$tbl_client_ddg_contres = $this -> conn_finance->query_sql($tbl_client_ddg_contsql);													
							
							if(mysql_num_rows($tbl_client_ddg_contres)>0)
							{
								$tbl_client_ddg_contarry= mysql_fetch_assoc($tbl_client_ddg_contres);
								$search_cum_callcount=$tbl_client_ddg_contarry[cumulative_callcount];
								$search_cum_ddg_ratio=$tbl_client_ddg_contarry[cumulative_ddg_ratio];
								$search_contribution=$tbl_client_ddg_contarry[contribution];
							}
							else
							{
								$search_cum_callcount= 1;
								$search_cum_ddg_ratio= $var_partial_ddg_ratio;
								$search_contribution = $var_partial_ddg_ratio;
							}
							$insert_common_query_ddg = $insert_common_query_ddg."('".$var_campaignid."','".$var_bid_catid."','".$var_bid_zone."','".$var_parentid."','".$var_pincode."','".$var_bid_bidamt."','".$var_position_flag."','".$var_partial_ddg_ratio."','".$var_partial_ddg_ratio_cum."','".$var_nationalcatid."','".$var_expired."','".$var_activeflag."','".$search_cum_callcount."','".$search_cum_ddg_ratio."','".$search_contribution."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."','".$pincode_value."'),";
							
							$insert_common_query_nonpaid = $insert_common_query_nonpaid."('".$var_bid_catid."','".$var_parentid."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
							
						}
								
						$ddg_ins="INSERT INTO tbl_compcatarea_ddg(campaignid,bid_catid,bid_zone,parentid,pincode,bid_bidamt,position_flag,partial_ddg_ratio,partial_ddg_ratio_cum,nationalcatid,expired,activeflag,search_cum_callcount,search_cum_ddg_ratio,search_contribution, phonenos, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,duplicate_check_phonenos,physical_pincode) VALUES";
						$nonpaid_ins="INSERT IGNORE INTO tbl_compcatarea_non_paid(bid_catid, parentid, activeflag,nationalcatid, phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
						
						if($insert_common_query_ddg)
						{
							$insert_common_query_ddg		= trim($insert_common_query_ddg, ',');
							$finalddgquery		=  $ddg_ins.$insert_common_query_ddg;
						}
						if($insert_common_query_nonpaid)
						{
							$insert_common_query_nonpaid	= trim($insert_common_query_nonpaid, ',');
							$finalnonpaidquery	=  $nonpaid_ins.$insert_common_query_nonpaid;
						}
						
						if ( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{						# Balance Absent. Delete from CCA_DDG and push data into CCA_DDG
							if(strlen(trim($finalddgquery))!=0) // if query is created then only write into table
							{
							$insert_cca_ddgquery=$this -> conn_iro->query_sql($finalddgquery);
							
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
							$logginquery = $finalddgquery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
/*
							$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalddgquery)."')";
							$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
*/
							}
						}
						
						if(($var_balance<=0 && $var_manualoverride==0) && $rowcount>0 )
						{

							$insert_cca_nonpaidquery=$this -> conn_iro->query_sql($finalnonpaidquery);
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
							$logginquery = $finalddgquery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
							
							//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalnonpaidquery)."')";
							//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

						}

						}
				
						/* Non paid categories to be inserted in tbl_compcatarea_non_paid  STARTS*/				
						
						if($exclusive_flag)
						{
							$nonpaid_category_catids = $this -> getContractsCategory($parentid,'tbl_bidcatdetails_nonpaid');
							$nonpaid_category_catids_arr = $this -> fetch_categorytype($nonpaid_category_catids); 
							$rowcount = count($nonpaid_category_catids_arr);
							
							if($rowcount<=0)
							{

								$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Invalid campaignid. No entries found in tbl_bidcatdetails_nonpaid. Please check and enter again...File EXITED.')";
								$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
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
										$insert_common_query_nonpaid = $insert_common_query_nonpaid."('".$var_bid_catid."','".$var_parentid."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
									}
								}
					
								$nonpaid_ins="INSERT IGNORE INTO tbl_compcatarea_non_paid(bid_catid, parentid, activeflag,nationalcatid, phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
								
								if($insert_common_query_nonpaid)
								{
									$insert_common_query_nonpaid	= trim($insert_common_query_nonpaid, ',');
									$finalnonpaidquery	=  $nonpaid_ins.$insert_common_query_nonpaid;
									$insert_cca_nonpaidquery=$this -> conn_iro->query_sql($finalnonpaidquery);
									
									$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
									$logginquery = $insert_cca_nonpaidquery;
									$this->loggintable($logginquery,$removeterms,$var_campaignid);
									//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalnonpaidquery)."')";
									//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
								}
							}
						}						
						
						/* Non paid categories to be inserted in tbl_compcatarea_non_paid  STARTS*/
						# Actual inserts into the DDG table happen here #
						#################################################

						if( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{						# Balance Absent. Delete from CCA_DDG and push data into CCA_DDG
								$ddg_parent_category_arr = $this -> getParentCategories($category_catids);
								// removing flag updation queries

						}				
				}
				
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
					$check_balance_query="SELECT campaignid, expired, balance, manual_override, threshold_active_flag,searchcriteria FROM tbl_companymaster_finance WHERE parentid= '".$parentid."' AND campaignid='".$var_campaignid."'"; 
					
					$result_check_balance_query=$this -> conn_finance->query_sql($check_balance_query);

					$row = mysql_fetch_array($result_check_balance_query);
					$var_balance = $row['balance'];
					$var_expired = $row['expired'];
					$var_manualoverride = $row['manual_override'];
					$searchcriteria = $row['searchcriteria'];
					$var_activeflag = $row['threshold_active_flag'];
					
					# Fetch MyZone / Zone Categories #
					##################################

					$push_myzone_catquery="SELECT campaignid,bid_catid,bid_zone,parentid,pincode,uptdate,bidperday FROM tbl_bidcatdetails_supreme WHERE parentid='".$parentid."' AND campaignid='".$var_campaignid."'";
															
					$result_push_myzone_catquery=$this -> conn_finance->query_sql($push_myzone_catquery);

					$rowcount = mysql_num_rows($result_push_myzone_catquery);

					if($rowcount<=0) {

						$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','Invalid campaignid. No entries found in tbl_bidcatdetails_supreme(myzone). Please check and enter again...File EXITED.')";
						$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						$this->nonpaidforcefully=1;
					}
					else
					{
						$purepack_numrows=0;
						
						if($var_expired==1)
						{ // checking whether it is pure package or not
							$check_purepack_query="SELECT campaignid FROM tbl_companymaster_finance WHERE parentid= '".$parentid."' AND campaignid in (2,17,18)";					
							$rs_purepack = $this -> conn_finance->query_sql($check_purepack_query);							
							$purepack_numrows = mysql_num_rows($rs_purepack);
						 
						}
						
						
						if($var_expired==1 && $purepack_numrows==0)
						{
							$this->nonpaidforcefully=1;
							$this->purepack_epired = 1;
							
						}
						else
						{							
						unset($insert_common_query_nonpaid);
						$pack_category_catids = $this -> getContractsCategory($parentid,'tbl_bidcatdetails_supreme');
						$pack_category_catids_arr = $this -> fetch_categorytype($pack_category_catids);
						$insert_common_query_myzone = '';
						$insert_common_query_zone = '';
						$insert_common_query_nonpaid = '';
						
						while($row = mysql_fetch_array($result_push_myzone_catquery))
						{
							$var_campaignid	= $row['campaignid'];
							$var_bid_catid	= $row['bid_catid'];
							$var_bid_zone	= $row['bid_zone'];
							$var_parentid	= $row['parentid'];
							$var_pincode	= $row['pincode'];
							$var_bidperday	= $row['bidperday'];

							$var_searchtype =$pack_category_catids_arr[$row['bid_catid']]['searchtype'];
							$var_nationalcatid   = $pack_category_catids_arr[$row['bid_catid']]['national_catid'];

							if(( strtoupper($var_searchtype) == 'Z' ) OR  ( strtoupper($var_searchtype) == 'SZ' ) OR ( strtoupper($var_searchtype)== 'A' ) OR ( strtoupper($var_searchtype) == 'MZ' ) OR ( strtoupper($var_searchtype) == 'NM' ) OR ( strtoupper($var_searchtype) == 'VNM' ) ){
								$insert_common_query_myzone = $insert_common_query_myzone."('".$var_campaignid."','".$var_bid_catid."','".$var_bid_zone."','".$var_parentid."','".$var_pincode."','".$var_bidperday."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
							}
							else
							{
								$insert_common_query_zone = $insert_common_query_zone."('".$var_campaignid."','".$var_bid_catid."','".$var_bid_zone."','".$var_parentid."','".$var_pincode."','".$var_bidperday."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
							}

							
							$insert_common_query_nonpaid = $insert_common_query_nonpaid."('".$var_bid_catid."','".$var_parentid."','".$var_pincode."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";

							}
							
							$myzone_ins="INSERT INTO tbl_compcatarea_myzone(campaignid, bid_catid, bid_zone, parentid, pincode,perdaycontribution,activeflag,nationalcatid,phonenos, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
							$zone_ins="INSERT INTO tbl_compcatarea_zone(campaignid, bid_catid, bid_zone, parentid, pincode,perdaycontribution,activeflag,nationalcatid,phonenos, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
							$nonpaid_ins="INSERT IGNORE INTO tbl_compcatarea_non_paid(bid_catid, parentid, pincode, activeflag,nationalcatid, phonenos, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
							if($insert_common_query_myzone)
							{
								$insert_common_query_myzone		= trim($insert_common_query_myzone, ',');
								$finalmyzonequery				=  $myzone_ins.$insert_common_query_myzone;
							}
							if($insert_common_query_zone)
							{
								$insert_common_query_zone		= trim($insert_common_query_zone, ',');
								$finalzonequery					=  $zone_ins.$insert_common_query_zone;
							}

							if($insert_common_query_nonpaid)
							{
								$insert_common_query_nonpaid	= trim($insert_common_query_nonpaid, ',');
								$finalnonpaidquery				=  $nonpaid_ins.$insert_common_query_nonpaid;
							}
						
						}
						
						}
						
						

						# Actual inserts into the MYZONE/ZONE table happen here #
						#########################################################

						if(( $var_balance>0 || $var_manualoverride==1) && $rowcount>0 )
						{							# Balance Absent. Delete from CCA_MYZONE and push data into CCA_MYZONE

						if( $finalmyzonequery != "" )
						{
							$insert_cca_myzonequery=$this -> conn_iro->query_sql($finalmyzonequery);
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
							$logginquery = $finalmyzonequery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
							//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalmyzonequery)."')";
							//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						}

						if( $finalzonequery != "" )
						{
				
							$insert_cca_zonequery=$this -> conn_iro->query_sql($finalzonequery);
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
							$logginquery = $finalzonequery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
														
							//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalzonequery)."')";
							//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						}
									
									#getting parent of package categories
						$pack_parent_category_arr = $this -> getParentCategories($pack_category_catids);
						// removing table deltion query

						}

						if(( $var_balance<=0 && $var_manualoverride==0) && $rowcount>0 && $finalnonpaidquery)
						{	# Balance Absent. Delete from CCA_MYZONE and push data into non_paid							
							$insert_cca_nonpaidquery=$this -> conn_iro->query_sql($finalnonpaidquery);
							
							$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
							
							$logginquery = $finalnonpaidquery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
							
							//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalnonpaidquery)."')";
							//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						}

					} # END OF SUPREME CHECK HERE


					if(($var_campaignid==1||$var_campaignid==2) && $this->purepack_epired==0) 
					{
						
					#START - inserting (ddg and parent categories) of both (package and platinum) campaign into (zone and myzone) of compcatarea

					# This part is for inserting DDG contracts into MyZONE and ZONE #
					#################################################################

					$push_ddgentries_inpkgtablecatquery = "SELECT campaignid, bid_catid, parentid, max(bid_bidamt) as bid_bidamt FROM  tbl_compcatarea_ddg  WHERE parentid = '".$parentid."' AND pincode !='999999' GROUP BY campaignid, bid_catid";
					$result_push_ddgentries_inpkgtablecatquery=$this -> conn_iro->query_sql($push_ddgentries_inpkgtablecatquery);
					
					$push_ddgentries_perdaycontributionquery = "SELECT SUM(bidperday) as perdaycontribution,bid_catid FROM  tbl_bidcatdetails_ddg  WHERE parentid = '".$parentid."' AND pincode !='999999'  /* AND physicallocation!=1 */ GROUP BY bid_catid";
					$push_ddgentries_perdaycontributionrs =$this -> conn_finance->query_sql($push_ddgentries_perdaycontributionquery);
					
					$ddgpPDCArray =array();
					if ($push_ddgentries_perdaycontributionrs && mysql_num_rows($push_ddgentries_perdaycontributionrs)>0)
					while($ddg_pdcarr = mysql_fetch_assoc($push_ddgentries_perdaycontributionrs))
					{
						$ddgpPDCArray[$ddg_pdcarr['bid_catid']]= $ddg_pdcarr['perdaycontribution'];
					}
														
					$insert_common_query_myzone = '';
					$insert_common_query_zone = '';
					$insert_common_query_nonpaid = '';

					WHILE ($row = mysql_fetch_array($result_push_ddgentries_inpkgtablecatquery))
					{ 
						$var_campaignid				= $row['campaignid'];
						$var_bid_catid				= $row['bid_catid'];
						$var_parentid				= $row['parentid'];
						//$var_bid_bidamt				= $row['bid_bidamt']; perdaycontribution
						$var_bid_pdc				= $ddgpPDCArray[$row['bid_catid']]; //perdaycontribution
						
						$var_searchtype      =$category_catids_arr[$row['bid_catid']]['searchtype'];
						$var_nationalcatid   = $category_catids_arr[$row['bid_catid']]['national_catid'];
						
						$totcompdisplay   = $category_catids_arr[$row['bid_catid']]['totcompdisplay'];
						$exclusive   = $category_catids_arr[$row['bid_catid']]['exclusive'];
																		
						if ($totcompdisplay==1 || $exclusive==1)
						{// if it is exclusive category it should not enter into myzone table
								continue;
						}

						if(( strtoupper($var_searchtype) == 'Z' ) OR  ( strtoupper($var_searchtype) == 'SZ' ) OR ( strtoupper($var_searchtype)== 'A' ) OR ( strtoupper($var_searchtype) == 'MZ' ) OR ( strtoupper($var_searchtype) == 'NM' ) OR ( strtoupper($var_searchtype) == 'VNM' )  )
						{
								//echo "<br>InMYZONE";
							if($searchcriteria==1)
							$var_significance=2;
							else
							$var_significance=0;
							
							$ddg_insert_common_query_myzone = $ddg_insert_common_query_myzone."('".$var_campaignid."','".$var_bid_catid."','".$var_parentid."','".$var_bid_pdc."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."',1,".$var_significance.",'".$duplicate_check_phonenos."'),";
						}
						else
						{ //ELSE in else part we are updating tbl_compcatarea_zone
									//echo "<br>InZONE";
						
							$var_significance=2.0;
							
							$ddg_insert_common_query_zone = $ddg_insert_common_query_zone."('".$var_campaignid."','".$var_bid_catid."','".$var_parentid."','".$var_bid_pdc."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."',1,".$var_significance.",'".$duplicate_check_phonenos."'),";

						}
					}

					$contract_parent_category_arr = (!is_array($ddg_parent_category_arr)?array():$ddg_parent_category_arr) + (!is_array($pack_parent_category_arr)?array():$pack_parent_category_arr);
				
					if(count($contract_parent_category_arr)>0)
					{	$exclusive=0;
						$totcompdisplay=0;
						foreach($contract_parent_category_arr as $catid => $catid_arr)
						{
							$var_campaignid		 = (count($ddg_parent_category_arr)>0) ? $ddg_campaignid : $pack_campaignid;
							//$var_searchpara1     = (count($ddg_parent_category_arr)>0) ? 1:0;
							
							if($searchcriteria == 1 && $var_campaignid == 2)
							{
								$var_searchpara1 = 1;
							}
							else
							{
								$var_searchpara1 = 0;	
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

								//echo "<br>InMYZONE parentcategory";
								if($searchcriteria==1)
								$var_significance=2;
								else
								$var_significance=0;
									
								$ddg_insert_common_query_myzone = $ddg_insert_common_query_myzone."('".$var_campaignid."','".$var_bid_catid."','".$var_parentid."','".$var_bid_bidamt."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."',".$var_searchpara1.",".$var_significance.",'".$duplicate_check_phonenos."'),";
							}
							else
							{
								//echo "<br>InZONE";//ELSE in else part we are updating tbl_compcatarea_zone
								$var_significance =2.0;
								$ddg_insert_common_query_zone = $ddg_insert_common_query_zone."('".$var_campaignid."','".$var_bid_catid."','".$var_parentid."','".$var_bid_bidamt."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$pincode_value."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."',".$var_searchpara1.",".$var_significance.",'".$duplicate_check_phonenos."'),";

							}				

						}
					}

					$ddg_myzone_ins="INSERT IGNORE INTO tbl_compcatarea_myzone(campaignid, bid_catid, parentid, perdaycontribution,activeflag,nationalcatid,phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,searchpara1,significance,duplicate_check_phonenos) VALUES";

					$ddg_zone_ins="INSERT IGNORE INTO tbl_compcatarea_zone(campaignid, bid_catid, parentid, perdaycontribution,activeflag,nationalcatid,phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,searchpara1,significance,duplicate_check_phonenos) VALUES";
				
					if($ddg_insert_common_query_myzone)
					{
						$ddg_insert_common_query_myzone		= trim($ddg_insert_common_query_myzone, ',');
						$ddg_finalmyzonequery				= $ddg_myzone_ins.$ddg_insert_common_query_myzone;
						$ddg_insert_cca_myzonequery         =$this -> conn_iro->query_sql($ddg_finalmyzonequery);
						
						$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
							$logginquery = $ddg_finalmyzonequery;
							$this->loggintable($logginquery,$removeterms,$var_campaignid);
						
						//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($ddg_finalmyzonequery)."')";
						//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						unset($ddg_insert_common_query_myzone);
					}
					
					if($ddg_insert_common_query_zone)
					{
						$ddg_insert_common_query_zone		= trim($ddg_insert_common_query_zone, ',');
						$ddg_finalzonequery					= $ddg_zone_ins.$ddg_insert_common_query_zone;
						$ddg_insert_cca_zonequery			=$this -> conn_iro->query_sql($ddg_finalzonequery);
						
						$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
						$logginquery = $ddg_finalzonequery;
						$this->loggintable($logginquery,$removeterms,$var_campaignid);
						
						//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($ddg_finalzonequery)."')";
						//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
						unset($ddg_insert_common_query_zone);
					}

					#END - inserting (ddg and parent categories) of both (package and platinum) campaign into (zone and myzone) of compcatarea
					
					
					/* Non paid categories to be inserted in tbl_compcatarea_non_paid  STARTS*/
						if(trim($catidlineage_nonpaid,',')!='')
						{
							foreach(explode(',',$catidlineage_nonpaid) as $value)
							{
								$nationalId = $this->fetch_categorytype($catidlineage_nonpaid);
								$sql_nonpaid_insert = "INSERT IGNORE INTO tbl_compcatarea_non_paid( bid_catid, parentid, activeflag,nationalcatid, phonenos, pincode, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES('".$value."','".$this -> parentid."','".$activeflag_value."','".$nationalId[$value]['national_catid']."','".$phone_search."','".$pincode_value."','".addslashes($address)."','".addslashes($area)."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".addslashes($this->userid)."','".$today."','".$data_city."','".$duplicate_check_phonenos."')";
								$this->conn_iro->query_sql($sql_nonpaid_insert);
								
								$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
								$logginquery = $sql_nonpaid_insert;
								$this->loggintable($logginquery,$removeterms,$var_campaignid);
								
								//$sqlLog = "INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$this -> parentid."','".$var_campaignid."','".addslashes($sql_nonpaid_insert)."')";
								//$qryLog = $this -> conn_finance->query_sql($sqlLog);
							}
							//echo "<br>catidlineage_nonpaid".$catidlineage_nonpaid."<br>".$sql_nonpaid_insert; exit;
						}
								
					/* Non paid categories to be inserted in tbl_compcatarea_non_paid  STARTS*/
					
					}
					
					
					if($var_campaignid == 17 || $var_campaignid == 18)
					{
					
						// removing table deltion query
						//$check_balance_query="SELECT campaignid, expired, balance, manual_override, active_flag FROM tbl_companymaster_finance WHERE campaignid='".$var_campaignid."'";
						$check_balance_query="SELECT campaignid, expired, balance, manual_override, threshold_active_flag FROM tbl_companymaster_finance WHERE parentid='".$parentid."' AND campaignid='".$var_campaignid."'";
						
						$result_check_balance_query=$this -> conn_finance->query_sql($check_balance_query);

						$row = mysql_fetch_array($result_check_balance_query);
						$var_balance = $row['balance'];
						$var_expired = $row['expired'];
						$var_manualoverride = $row['manual_override'];
						$var_activeflag = $row['threshold_active_flag'];
						
						# Fetch PPC Categories #
						########################

						$push_ppc_catquery="SELECT count(parentid) as count FROM tbl_bidcatdetails_lead WHERE parentid = '".$parentid."' AND campaignid='".$var_campaignid."'";  
						
						$result_push_ppc_catquery = $this -> conn_finance->query_sql($push_ppc_catquery);

						$result_push_ppc_arry= mysql_fetch_assoc($result_push_ppc_catquery);
						$rowcount = $result_push_ppc_arry['count'];
						unset($result_push_ppc_catquery);
						unset($push_ppc_catquery);
						//echo "<br>rowcount--------".$rowcount; exit;
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
							if($var_campaignid == 17)
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

									$insert_common_query_ppc = $insert_common_query_ppc."('".$var_campaignid."','".$var_bid_catid."','".$var_bid_zone."','".$var_parentid."','".$var_pincode."','".$var_bid_bidamt."','".$var_grace_amt."','".$var_nationalcatid."','".$var_expired."','".$var_activeflag."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."','".$pincode_value."'),";
									$insert_common_query_nonpaid = $insert_common_query_nonpaid."('".$var_bid_catid."','".$var_parentid."','".$pincode_value."','".$var_activeflag."','".$var_nationalcatid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
									}

									$ppc_ins="INSERT INTO tbl_compcatarea_ppc(campaignid,bid_catid,bid_zone,parentid,pincode,bid_bidamt,grace_lead_amt,nationalcatid,expired,activeflag,phonenos, fulladdress, area, regionid,latitude,longitude,email_search,updatedby,updateddate,data_city,duplicate_check_phonenos,physical_pincode) VALUES";
									
									$nonpaid_ins="INSERT IGNORE INTO tbl_compcatarea_non_paid( bid_catid, parentid, pincode, activeflag,nationalcatid, phonenos, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
									if($insert_common_query_ppc)
									{
										$insert_common_query_ppc		= trim($insert_common_query_ppc, ',');
										$finalppcquery					=  $ppc_ins.$insert_common_query_ppc;

									}
									if($insert_common_query_nonpaid)
									{
										$insert_common_query_nonpaid	= trim($insert_common_query_nonpaid, ',');
										$finalnonpaidquery				=  $nonpaid_ins.$insert_common_query_nonpaid;
									}
									
									# Actual inserts into the PPC table happen here #
									#################################################
									
									IF ( ($var_balance>0 || $var_manualoverride==1) && $rowcount>0 ){							# Balance Absent. Delete from CCA_PPC and push data into CCA_PPC

										$insert_cca_ppcquery=$this -> conn_iro->query_sql($finalppcquery);
										
										$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
										$logginquery = $finalppcquery;
										$this->loggintable($logginquery,$removeterms,$var_campaignid);
										
										//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalppcquery)."')";
										//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);

									}

									IF ( ( $var_balance<=0 && $var_manualoverride==0) && $rowcount>0 ){
										// Balance Absent. Delete from CCA_PPC and push data into non_paid

										$insert_cca_nonpaidquery=$this -> conn_iro->query_sql($finalnonpaidquery);
																				
										
										$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$var_nationalcatid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
										$logginquery = $finalnonpaidquery;
										$this->loggintable($logginquery,$removeterms,$var_campaignid);
										
										//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,campaignid,message) VALUES('".$parentid."','".$var_campaignid."','".addslashes($finalnonpaidquery)."')";
										//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
										
									}
										
									mysql_free_result($result_push_ppc_catquery);
								
								}
								
							}
								// end of foreach 
								// after loging of all contract we have to insert into tbl_clients_package_contribution tbl_companymaster_finance tbl_compcatarea_regen_log

								
						}
					}
				} # END OF C/H CHECK HERE
			
			} # WHILE Ends here
			
		$removetermsstr = implode(",",$removeterms);
		$companyDetails_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$this->parentid."','companyDetails-" .addslashes($removetermsstr)."')";
		$result_insert_log_query=$this -> conn_finance->query_sql($companyDetails_log_query);

		} # ELSE Ends here
			
	} # Paid =1 ENDS Here
	
	// instead of else we are checking two conditions 
	if($paid_value == 0 || $this->nonpaidforcefully)
	{ # NonPaid STARTS Here
		
		$low_rankingmsg="";
		if($low_ranking==1)
		{$low_rankingmsg=" low_ranking=".$low_ranking; }
		
		$purepack_epired_msg="";
		if($this->purepack_epired==1)
		{$purepack_epired_msg=" purepack_epired =".$this->purepack_epired; }
		
		
		$nonpaid_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','tbl_compcatarea_non_paid generation, flag name and value, paid=".$paid_value.$low_rankingmsg.$purepack_epired_msg."  nonpaidforcefully_generation=".addslashes($this->nonpaidforcefully)."')";
			$result_insert_log_query=$this -> conn_finance->query_sql($nonpaid_log_query);
			
			
		$delete_nonpaid_query="DELETE FROM tbl_compcatarea_non_paid where parentid='".$parentid."'";
		$result_delete_nonpaid_query=$this -> conn_iro->query_sql($delete_nonpaid_query);

		//echo "NonPaid:".$delete_nonpaid_query;

		$category_array = $this ->fetch_categorytype( $catidlineage_value);
		$today = date("Y-m-d H:i:s");

		//$var_catids = explode(",", $catidlineage_value);
		$category_array= array_filter($category_array);
		
		if(count($category_array)>0)
		{
			foreach($category_array as $catid => $catid_value)
			{
				$national_catid = $catid_value['national_catid'];
				$insert_common_query_purenonpaid = $insert_common_query_purenonpaid."('".$catid."','".$parentid."','".$pincode_value."','".$activeflag_value."','".$national_catid."','".$phone_search."','".$address."','".$area."','".$regionid."','".$latitude."','".$longitude."','".$email_search."','".$nonpaid_callcount."','".$this->userid."','".$today."','".$data_city."','".$duplicate_check_phonenos."'),";
			}
		}
		if($insert_common_query_purenonpaid)
		{
			$purenonpaid_ins="INSERT INTO tbl_compcatarea_non_paid( bid_catid, parentid, pincode, activeflag, nationalcatid, phonenos, fulladdress, area, regionid,latitude,longitude,email_search,callcount,updatedby,updateddate,data_city,duplicate_check_phonenos) VALUES";
			$insert_common_query_purenonpaid	= trim($insert_common_query_purenonpaid, ',');
			$finalpurenonpaidquery				= $purenonpaid_ins.$insert_common_query_purenonpaid;

			//echo "FinalQ".$finalpurenonpaidquery;
			$result_finalpurenonpaidquery=$this -> conn_iro->query_sql($finalpurenonpaidquery);
			 
			$removeterms=array("'".$var_campaignid."'","'".$var_parentid."'","'".$national_catid."'","'".$var_position_flag."'","'".$search_cum_callcount."'","'".$search_cum_ddg_ratio."'","'".$search_contribution."'","'".$phone_search."'","'".$pincode_value."'","'".$address."'","'".$area."'","'".$regionid."'","'".$latitude."'","'".$longitude."'","'".$email_search."'","'".$nonpaid_callcount."'","'".$this->userid."'","'".$today."'","'".$data_city."'","'".$duplicate_check_phonenos."'");
								
			$logginquery = $finalpurenonpaidquery;
			$this->loggintable($logginquery,$removeterms,0);
			
			$removetermsstr = implode(",",$removeterms);
			$companyDetails_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$this->parentid."','companyDetails-" .addslashes($removetermsstr)."')";
			$result_insert_log_query=$this -> conn_finance->query_sql($companyDetails_log_query);
			//$insert_log_query="INSERT INTO ".$this->log_tbl."(parentid,message) VALUES('".$parentid."','".addslashes($finalpurenonpaidquery)."')";
			//$result_insert_log_query=$this -> conn_finance->query_sql($insert_log_query);
		}
				

	} # Rowcount ELSE ENDS Here
//echo "<br> EXIT in compcatarea_regen.php"; exit;
	return;
	}

}

?>
