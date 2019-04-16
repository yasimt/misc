<?php
// changes needed
	class CategoryAuthorisationClass
	{
	//table tbl_temp_flow_status's  authorisation_flag =1 means restrict user to move forward
	// table tbl_temp_flow_status's authorisation_flag =0 means allow user to move forward

	//new rule
	//table tbl_temp_flow_status's  authorisation_flag =1 means ALLOW user to move forward
	// table tbl_temp_flow_status's authorisation_flag =0 means RESTRICT user to move forward
	var $parent_id;
	var $module;
	function __construct($parentidvalue="", $modulevalue="")
	{	
		if(trim($modulevalue)=="")
		{
			$this->module = strtolower(trim($_SESSION['module']));
		}
		else
		{
			$this->module=strtolower(trim($modulevalue));
		}
		
		if(trim($parentidvalue)=="")
		{
			$this->parent_id = $_SESSION['parentid'];
		}
		else
		{
			$this->parent_id=$parentidvalue;
		}
	}

	function setAuthorisationFlag($conn_local,$conn_tme,$authorisation_flag_value )
	{
		$Insert_tbl_temp_flow_status_sql= "INSERT INTO tbl_temp_flow_status SET
											parentid ='".$this->parent_id."',
											authorisation_flag='".$authorisation_flag_value."'
											ON DUPLICATE KEY UPDATE
											authorisation_flag='".$authorisation_flag_value."'";
				
		if($this->module=="tme")
		{ 
			$conn_tme->query_sql($Insert_tbl_temp_flow_status_sql);
		}
		elseif($this->module=="cs")
		{ 
			$conn_local->query_sql($Insert_tbl_temp_flow_status_sql);
		}
	}

	function getAuthorisationFlag($conn_local,$conn_tme)
	{
		$Select_tbl_temp_flow_status_sql= "SELECT authorisation_flag FROM tbl_temp_flow_status WHERE parentid = '" . $_SESSION['parentid'] . "'";
		if($this->module=="tme")
		{ 
			$res = $conn_tme->query_sql($Select_tbl_temp_flow_status_sql);
		}
		elseif($this->module=="cs")
		{ 
			$res = $conn_local->query_sql($Select_tbl_temp_flow_status_sql);
		}
		if(!$res)
		{
			return -2;/* indicates mysql error */
		}
		else
		{
			if(mysql_num_rows($res)>0)
			{
				$authoflag = mysql_fetch_assoc($res);
				return $authoflag['authorisation_flag']; /* indicates proper value form table */
			}
			else
			{
				return -1; /* indicates entry is not exist in the table */
			}
			
		}
	}

	/* Currently we are not using the next 3 functions*/
	function isAuthorizationLettersubmited($conn_local,$conn_tme)
	{
		//comp_cat_name_flag's default value is 1 so we consider it as not answered  
		$tbl_temp_flow_status_sql= "SELECT comp_cat_name_flag FROM tbl_temp_flow_status WHERE parentid ='".$this->parent_id."'";

		if($this->module=="tme")
		{
			$tbl_temp_flow_status_res = $conn_tme->query_sql($tbl_temp_flow_status_sql);
		}
		else if($this->module=="cs")
		{
			$tbl_temp_flow_status_res = $conn_local->query_sql($tbl_temp_flow_status_sql);
		}
		
		$tbl_temp_flow_status_res_array = mysql_fetch_assoc($tbl_temp_flow_status_res);
			
		if($tbl_temp_flow_status_res_array['comp_cat_name_flag']=='0')// we find authorised categories in contract
		{
			return 1;
		}
		else
		{
			//echo "FALSE";// it returns nothing
			return 0;
		}
	}

	function setAuthorizationLettersubmited($conn_local,$conn_tme)
	{ // this function gets called to set that user has accepted that he has received letter so we change comp_cat_name_flag=0
		//comp_cat_name_flag's default value is 1 so we consider it as not answered  
		$tbl_temp_flow_status_insert="INSERT INTO tbl_temp_flow_status SET
									parentid ='".$this->parent_id."',
									comp_cat_name_flag='0'
															
									ON DUPLICATE KEY UPDATE
									comp_cat_name_flag='0'";
		if($this->module=="tme")
		{
			$tbl_temp_flow_status_res = $conn_tme->query_sql($tbl_temp_flow_status_insert);
		}
		else if($this->module=="cs")
		{
			$tbl_temp_flow_status_res = $conn_local->query_sql($tbl_temp_flow_status_insert);
		}
		
		//echo "<br>connection obj"; print_r($connection_obj);
		
		//echo "<br>mysql_num_rows_".mysql_num_rows($select_tbl_business_temp_res);

		return $tbl_temp_flow_status_res ;
	}

	function resetAuthorizationLettersubmited($conn_local,$conn_tme)
	{ // this function gets called to set that user has accepted that he has received letter so we change comp_cat_name_flag=0
		//comp_cat_name_flag's default value is 1 so we consider it as not answered  
		$tbl_temp_flow_status_insert="INSERT INTO tbl_temp_flow_status SET
									parentid ='".$this->parent_id."',
									comp_cat_name_flag='1'
															
									ON DUPLICATE KEY UPDATE
									comp_cat_name_flag='1'";
		if($this->module=="tme")
		{
			$tbl_temp_flow_status_res = $conn_tme->query_sql($tbl_temp_flow_status_insert);
		}
		else if($this->module=="cs")
		{
			$tbl_temp_flow_status_res = $conn_local->query_sql($tbl_temp_flow_status_insert);
		}
		
		//echo "<br>connection obj"; print_r($connection_obj);
		
		//echo "<br>mysql_num_rows_".mysql_num_rows($select_tbl_business_temp_res);

		return $tbl_temp_flow_status_res ;
	}


	function IsAuthorisedCategoryExist($conn_local,$conn_tme)
	{
		$select_tbl_business_temp_sql= "SELECT categories FROM tbl_business_temp_data WHERE contractid ='".$this->parent_id."'";

		if($this->module=="tme")
		{
			$select_tbl_business_temp_res = $conn_tme->query_sql($select_tbl_business_temp_sql);
		}
		else if($this->module=="cs")
		{
			$select_tbl_business_temp_res = $conn_local->query_sql($select_tbl_business_temp_sql);
		}

		$select_tbl_business_temp_array = mysql_fetch_assoc($select_tbl_business_temp_res);
		$categories_names=$select_tbl_business_temp_array['categories'];
		
		if(stristr($categories_names,'(Authorised)') ||stristr($categories_names,'(Authorized)'))// we find authorised categories in contract
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}


	function IsAuthorisedCategoryChanged($conn_local,$conn_tme)
	{
	// this function tells whether there is a change in authorised categories of tbl_temp_data and tbl_category_authorisation
		$select_tbl_business_temp_sql ="SELECT catids FROM tbl_business_temp_data WHERE contractid = '".$this->parent_id."'";
		
		if($this->module=="tme")
		{
			$select_tbl_business_temp_res = $conn_tme->query_sql($select_tbl_business_temp_sql);
		}
		else if($this->module=="cs")
		{
			$select_tbl_business_temp_res = $conn_local->query_sql($select_tbl_business_temp_sql);
		}
		
		$select_tbl_business_temp_array = mysql_fetch_assoc($select_tbl_business_temp_res);
		$catidList = substr(str_replace('|P|',',',$select_tbl_business_temp_array['catids']),1);
		
		if(trim($catidList!="")){
			$select_categoty_authorization="select DISTINCT category_name as catname from tbl_categorymaster_generalinfo where catid in($catidList) AND mask_status='0'";
			
			if($this->module=="tme")
			{ 
				$categoty_authorization_res = $conn_local->query_sql($select_categoty_authorization);
			}
			elseif($this->module=="cs")
			{ 
				$categoty_authorization_res = $conn_local->query_sql($select_categoty_authorization);
			}
				
			$authorised_categories_string="";
				
			while($categoty_authorization_array= @mysql_fetch_assoc($categoty_authorization_res))
			{					
				if(stristr($categoty_authorization_array['catname'],'(Authorised)')||stristr($categoty_authorization_array['catname'],'(Authorized)'))
				{
					$authorised_categories_string=$authorised_categories_string.",".$categoty_authorization_array['catname'];
				}				
			}
			
			if(substr($authorised_categories_string,0,1)==',')
			{
				$authorised_categories_string= substr($authorised_categories_string,1);
			}
		}
		
		
		$select_tbl_categotyauthorization= "SELECT catnames FROM tbl_category_authorisation WHERE parentid ='".$this->parent_id."'";
		
		if($this->module=="tme")
		{
			$tbl_categotyauthorization_res = $conn_local->query_sql($select_tbl_categotyauthorization);
		}
		elseif($this->module=="cs")
		{
			$tbl_categotyauthorization_res = $conn_local->query_sql($select_tbl_categotyauthorization);
		}
		
		$categories_tbl_category_authorisation= mysql_fetch_assoc($tbl_categotyauthorization_res);

		$categories_array_tbl_business= explode(",",$authorised_categories_string);
		$categories_array_tbl_category_authorisation= explode(",",$categories_tbl_category_authorisation['catnames']);
		
		$categories_array_comparision1 = array_diff($categories_array_tbl_business,$categories_array_tbl_category_authorisation);
		$categories_array_comparision2 = array_diff($categories_array_tbl_category_authorisation,$categories_array_tbl_business);
		
		if(count($categories_array_comparision1)!=0 || count($categories_array_comparision2)!=0)
		{
			// it means there is change in categories
			return 1;
		}
		else
		{
			//echo "false";
			// it means user alredy said he has received letters
			return 0;
		}
		
		
	}


	function getFlowStatus($conn_local,$conn_tme, $mode="")
	{
		
		if($mode==1)//mode=1 means called from getcontract data here we set the value of authorisation_flag in other cases we dont have to do this
		{
			$authorisation_flag_value=1;
		
			if($this->IsAuthorisedCategoryExist($conn_local,$conn_tme))// we find authorised categories in contract
			{
				//echo "authorisation_category_changed_flag".
				$authorisation_category_changed_flag = $this->IsAuthorisedCategoryChanged($conn_local,$conn_tme);
				
					if ($authorisation_category_changed_flag)
					{
						// it means there is change in categories so we should ask for letter
						$authorisation_flag_value=0;
					}
					else
					{
						// it means user alredy said he has received letters so no need to ask
						$authorisation_flag_value=1;
					}
				
			}
			else  
			{
				// for those contracts which don't have Authorized Categories
				$authorisation_flag_value=1;
			}
			//echo "<br>authorisation_flag_value".$authorisation_flag_value;exit;
			$this->setAuthorisationFlag($conn_local,$conn_tme,$authorisation_flag_value);
		}
		return $this->getAuthorisationFlag($conn_local,$conn_tme);
	}


	/* NOT TO BE USED */
	function setFlowStatus($conn_local,$conn_tme,$authorisationFlag)
	{
		$this->setAuthorisationFlag($conn_local,$conn_tme,$authorisationFlag);
	}


	function insertAuthorisedCategoryIntoTable($conn_local,$conn_tme)
	{
		$select_tbl_business_temp_sql ="SELECT catids FROM tbl_business_temp_data WHERE contractid = '".$this->parent_id."'";
		
		if($this->module=="tme")
		{
			$select_tbl_business_temp_res = $conn_tme->query_sql($select_tbl_business_temp_sql);
		}
		else if($this->module=="cs")
		{
			$select_tbl_business_temp_res = $conn_local->query_sql($select_tbl_business_temp_sql);
		}
		
		$select_tbl_business_temp_array = mysql_fetch_assoc($select_tbl_business_temp_res);
			
		$catidList = substr(str_replace('|P|',',',$select_tbl_business_temp_array['catids']),1);
		if($catidList)
		{
		$select_categoty_authorization="select distinct catid,category_name as catname from tbl_categorymaster_generalinfo where catid in($catidList) AND mask_status='0'";
		
		if($this->module=="tme")
		{ 
			$categoty_authorization_res = $conn_local->query_sql($select_categoty_authorization);
		}
		elseif($this->module=="cs")
		{ 
			$categoty_authorization_res = $conn_local->query_sql($select_categoty_authorization);
		}
			
		$authorised_categories_string="";
		$authorised_categories_Ids_string="";
		
		while($categoty_authorization_array= mysql_fetch_assoc($categoty_authorization_res))
		{					
			if(stristr($categoty_authorization_array['catname'],'(Authorised)')||stristr($categoty_authorization_array['catname'],'(Authorized)'))
			{
				$authorised_categories_string=$authorised_categories_string.",".$categoty_authorization_array['catname'];
				$authorised_categories_Ids_string = $authorised_categories_Ids_string.",".$categoty_authorization_array['catid'];
			}				
		}	 
		
		if(substr($authorised_categories_string,0,1)==',')
		{
			$authorised_categories_string= substr($authorised_categories_string,1);
		}
		if(substr($authorised_categories_Ids_string,0,1)==',')
		{
			$authorised_categories_Ids_string= substr($authorised_categories_Ids_string,1);
		}
		
		if($_SESSION['compname']=="")
		{
			if($this->module=="tme")
			{ 	$select_categoty_authorization="select companyname from tbl_companymaster_generalinfo_shadow where parentid ='".$this->parent_id."'";
				$categoty_authorization_res = $conn_tme->query_sql($select_categoty_authorization);
				$categoty_authorization_array = mysql_fetch_assoc($categoty_authorization_res);
				$companyName_from_table = $categoty_authorization_array['companyname'];
			}
			elseif($this->module=="cs")
			{ 	$select_categoty_authorization="select companyname from db_iro.tbl_companymaster_generalinfo_shadow where parentid ='".$this->parent_id."'";
				$categoty_authorization_res = $conn_local->query_sql($select_categoty_authorization);
				$categoty_authorization_array = mysql_fetch_assoc($categoty_authorization_res);
				$companyName_from_table = $categoty_authorization_array['companyname'];
			}
		}
		else
		{
			$companyName_from_table= $_SESSION['compname'];
		}
		
		if(strlen($authorised_categories_string)>0)// means authorised category found
		{
			$Insert_tbl_category_authorisation_sql= "INSERT INTO tbl_category_authorisation SET
															parentid ='".$_SESSION['parentid']."',
															companyname ='".addslashes($companyName_from_table)."',
															catids ='".$authorised_categories_Ids_string."',
															catnames='".$authorised_categories_string."',
															username='".$_SESSION['uname']."',
															Userid='".$_SESSION['ucode']."',
															Dept='".$_SESSION['module']."',
															City='".$_SESSION['s_deptCity']."',
															updatetime=NOW()
															
															ON DUPLICATE KEY UPDATE
															companyname ='".addslashes($companyName_from_table)."',
															catids ='".$authorised_categories_Ids_string."',
															catnames='".$authorised_categories_string."',
															username='".$_SESSION['uname']."',
															Userid='".$_SESSION['ucode']."',
															Dept='".$_SESSION['module']."',
															City='".$_SESSION['s_deptCity']."',
															updatetime=NOW()";
						
			if($this->module=="tme")
			{ 
				$res = $conn_tme->query_sql($Insert_tbl_category_authorisation_sql);
			}
			elseif($this->module=="cs")
			{ 
				$res = $conn_local->query_sql($Insert_tbl_category_authorisation_sql);			
			}
		}
		else
		{//this is the case when a contract removes authorization check or removes authorised categories from his contract.

			$delete_tbl_category_authorisation= "DELETE FROM tbl_category_authorisation where parentid ='".$this->parent_id."'";
			
			if($this->module=="tme")
			{ 
				$conn_tme->query_sql($delete_tbl_category_authorisation);
			}
			elseif($this->module=="cs")
			{ 
				$conn_local->query_sql($delete_tbl_category_authorisation);
			}
		}
		}
	}

	function insertAfterApproval($conn_local,$conn_idc,$compmaster_obj)
	{
		$select_categoty_authorization_online_regis = "SELECT * FROM tbl_category_authorisation where parentid='".$this->parent_id."'";
		$select_categoty_authorization_online_regis_Res = $conn_idc->query_sql($select_categoty_authorization_online_regis);
		$fetched_array = mysql_fetch_assoc($select_categoty_authorization_online_regis_Res);
		/* if company name is blank*/
		if($fetched_array['companyname']=="")
		{
			$categoty_authorization_compname_res = array();
			$fieldstr							 = "companyname";
			$where 								 = "parentid ='".$this->parent_id."'";
			$categoty_authorization_compname_res = $compmaster_obj->getRow($fieldstr,"tbl_companymaster_generalinfo",$where);
			$categoty_authorization_compname_array = $categoty_authorization_compname_res['data']['0'];
			$fetched_array['companyname']= $categoty_authorization_compname_array['companyname'];
		}
		
		
		if($select_categoty_authorization_online_regis_Res and mysql_num_rows($select_categoty_authorization_online_regis_Res))
		{			
			$Insert_tbl_category_authorisation_sql= "INSERT INTO tbl_category_authorisation SET
														parentid ='".$fetched_array['parentid']."',
														companyname ='".addslashes($fetched_array['companyname'])."',
														catids ='".$fetched_array['catids']."',
														catnames='".$fetched_array['catnames']."',
														username='".$fetched_array['username']."',
														Userid='".$fetched_array['Userid']."',
														Dept='".$fetched_array['Dept']."',
														City='".$fetched_array['City']."',
														updatetime=NOW()
														
														ON DUPLICATE KEY UPDATE
														companyname ='".addslashes($fetched_array['companyname'])."',
														catids ='".$fetched_array['catids']."',
														catnames='".$fetched_array['catnames']."',
														username='".$fetched_array['username']."',
														Userid='".$fetched_array['Userid']."',
														Dept='".$fetched_array['Dept']."',
														City='".$fetched_array['City']."',
														updatetime=NOW()";
			
			$conn_local->query_sql($Insert_tbl_category_authorisation_sql);
		}
		else
		{
			//this is the case when a contract removes authorization check or removes authorised categories from his contract.

			$delete_tbl_category_authorisation= "DELETE FROM tbl_category_authorisation where parentid ='".$this->parent_id."'";
			$conn_local->query_sql($delete_tbl_category_authorisation);
			
		}
	}
	}
?>
