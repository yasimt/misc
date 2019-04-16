<?php
session_start();

if( !defined("APP_PATH"))
{
require_once("../library/config.php");
require_once("../common/Serverip.php");
include_once(APP_PATH."library/path.php");
}


class companyMasterClass
{
	private $parentid,$conn_iro,$tablename,$data_city,$tableno,$query_cond;
	Private $tbl_companymaster_extradetails_fieldArr,$tbl_companymaster_generalinfo_fieldArr,$tbl_companymaster_search_fieldArr;
	private $requestedtable,$requestedtableFieldArr;
	private $validtables;
	private $validmaintables;
	private $resultArr;
	private $remote;
	private $is_split;
	public $arr_company_tables = array();

	public function __construct($conn_iro,$data_city=null,$parentid=null,$tablename=null)
	{
		$this->validtables = array("tbl_companymaster_extradetails","tbl_companymaster_generalinfo","tbl_companymaster_search","tbl_companymaster_extradetails_shadow","tbl_companymaster_generalinfo_shadow");
		$this->validmaintables = array("tbl_companymaster_extradetails","tbl_companymaster_generalinfo","tbl_companymaster_search");
		
		$this->is_split = FALSE;
		if(defined("REMOTE_CITY_MODULE"))
		{
			$this->remote = 1;
			$this->is_split = FALSE;// when we will enable spliting we will make it TRUE			
		}
		else
		{
			$this->remote = 0;						
		}
		
		if(trim($parentid)!="")
		{
			$this -> parentid  = $parentid;/*initialize paretnid */
		}

		if(($conn_iro))
		{
			$this -> conn_iro = $conn_iro ;/*connection object to de/cs server*/
		}else
		{
			return  $this->errorMessage("Please provide connection object");
		}
		$this->setCompanyTables();

		if(trim($data_city)!="" && $data_city!=null)
		{
			$this -> data_city  = $data_city;/*initialize datacity*/
		}
		else
		{
			$this->set_datacity($parentid);
		}
		if(trim($data_city) == '' && trim($parentid) == ''){
			return  $this->errorMessage("Parentid and data city both cannot be blank");
		}
		if(trim($tablename)!="")
		{
			$val = $this -> settablename(trim($tablename));
			if($val!==1)
			{
				return $val;
			}
		}else
		{
			$this-> tablename = null;
		}

		$var = $this->setMapTableindex();
		if($var!==1)
		{
			return $var;
		}

		$this->resultArr= array();
	}

	public function setParentid($parentid)
	{
		$this -> parentid  = $parentid ;
	}

	public function set_datacity($parentid){

		if(trim($parentid)!=''){
			$sql 			 = "SELECT data_city FROM tbl_id_generator WHERE parentid='".$parentid."'";
			$qry			 = $this->conn_iro->query_sql($sql);
			$camparr 		 = mysql_fetch_assoc($qry);
			$this->data_city = $camparr['data_city'];
		}
		$this->logmsg("Data City Set ", $this->data_city);
	}

	public function setMapTableindex()
	{
		
		if($this->is_split === FALSE)
		{
			$this->tableno="";
			return 1; // integrating table mapping now so commenting it 
		}
		
		
		$sub ="\n\r_SERVER\n\r";
		foreach($_SERVER as $key=>$val)
		{
			$sub.= "\n\r".$key."=>".$val;
		}

		$sub .="\n\r_SESSION";
		foreach($_SESSION as $key=>$val)
		{
			$sub.= "\n\r".$key."=>".$val;
		}

		if($this -> data_city == null && $this -> parentid!=null)
		{
			$this -> set_datacity($this -> parentid);
		}

		$sql = "SELECT tableno from tbl_company_datacity_mapping where data_city='".$this -> data_city."'";
		$res = $this -> conn_iro->query_sql($sql);
		if(mysql_num_rows($res)>0)
		{
			$res_arr= mysql_fetch_assoc($res);
			if(trim($res_arr['tableno'])!="")
			{
				$this->tableno = $res_arr['tableno'];
				return 1;
			}
			else
			{
				mail("prameshjha@justdial.com","data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city,$Sub);
				return $his->errorMessage("data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city);
			}
		}
		else
		{
			mail("prameshjha@justdial.com","data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city, implode(",",array_merge($_SERVER, $_SESSION)));
			return $this->errorMessage("data_city is absent in tbl_company_datacity_mapping table data_city=".$this -> data_city);
		}
	}

	public function settablename($tablename)
	{
		if(trim($tablename)!="")
		{
			if(in_array($tablename,$this->validtables))
			{
				$this -> tablename = $tablename;
				return 1;
			}
			else
			{
				return  $this->errorMessage("Please provide valid table name");
			}
		}
	}

	private function errorMessage( $message)
	{
		return array("error" =>$message);
		//die("Error:".$message);
	}

	public function delete_shadow($parentid,$tblname){
		$valid_tables	= array("tbl_companymaster_generalinfo_shadow","tbl_companymaster_extradetails_shadow");
		if(trim($parentid)!='' && trim($tblname)!=''){
			if(in_array($tblname,$valid_tables)){
				$sql_del	= "DELETE FROM ".$tblname." WHERE parentid='".$parentid."'";
				$this->conn_iro->query_sql($sql_del);
			}else{
				$this->errorMessage($tblname." is not a valid table to delete from");
			}
		}
	}

	function populateFieldname($tblname)
	{
		return 1; // not needed now 
		if(in_array($tblname,$this->validtables))
		{
			$sql = "SELECT group_concat(column_name) as fields FROM information_schema.columns WHERE table_name='".$tblname."'";
			$rs  = $this->conn_iro->query_sql($sql);
			$temparr = mysql_fetch_assoc($rs);

			$temparr = explode(",",$temparr['fields']);
			$temparr[] = "*";

			$this->requestedtableFieldArr = $temparr;

			switch($tblname)
			{
				case "tbl_companymaster_extradetails":
				$tbl_companymaster_extradetails_fieldArr=$temparr;
				break;

				case "tbl_companymaster_generalinfo":
				$tbl_companymaster_generalinfo_fieldArr=$temparr;
				break;

				case "tbl_companymaster_search":
				$tbl_companymaster_search_fieldArr=$temparr;
				break;
			}
		}
	}

	public function getRow($fieldstr="*" , $tablename=null,$wherecond=null)
	{
		$fieldarr = explode(",",$fieldstr);
		$fieldarr =  array_filter($fieldarr);
		
		if(count($fieldarr))
		{
			foreach($fieldarr as $key=>$val)
			{
				$fieldarr[$key]=trim($val);
			}
		}

		if($tablename)
		{
			$val = $this->settablename($tablename);
			if($val!==1)
			return $val;
		}

		//$this->populateFieldname($this -> tablename);

		//$array_diff = array_diff($fieldarr,$this->requestedtableFieldArr);
		$array_diff	= array();

		if(count($array_diff))
		{
			$Field = count($array_diff)==1?"Field":"Fields";
			return $this->errorMessage( $Field." ".implode(',',$array_diff)." not present in table ".$this -> tablename);
		}

		if($this->tablename == null)
		{
			return $this->errorMessage("Table not selected");
		}

		$var = $this->setMapTableindex();
		if($var!==1)
		{
			return $var;
		}

		$finaltablename = $finaltablename = $this->getFinalTableName($this->tablename,$this->tableno);

		if($wherecond!=null)
		{
			$query_cond = $wherecond;
		}

		$this->select_query($fieldarr,$finaltablename,$query_cond);

		return $this->resultArr;
	}

	public function getmatchedtable($tblnmstring)
	{
		foreach ($this->validmaintables	as $maintablename)
		{
			if(strstr($tblnmstring,$maintablename))
			{ // we found table so replace the table with new table
				$var = $this->setMapTableindex();
				if($var!==1)
				{
					return $var;
				}
				$maintablenameFinal = $this->getFinalTableName($maintablename,$this->tableno);
				$tblnmstring = str_replace($maintablename,$maintablenameFinal,$tblnmstring);
			}
		}
		return $tblnmstring;
	}

	public function joinRow($joinfiedsname ,$jointablesname,$joincondon,$wherecond ,$orderbycond= null, $limit=null)
	{
		if($limit)
		{$limit= " limit ".$limit;}

		$this->resultArr = array();
		$jointablesnameArr = explode(",",$jointablesname);

		foreach($jointablesnameArr as $key=>$tblnm)
		{
			$jointablesnameArr[$key] = $this->getmatchedtable($tblnm);
		}

		$jointablesname = implode(",",$jointablesnameArr);

		$querystr= " select ".$joinfiedsname." FROM ".$jointablesname." ".$joincondon." WHERE ".$wherecond.$orderbycond.$limit;


		$res = $this->conn_iro->query_sql($querystr);
		$this->logmsg($querystr, $res);

		if($res && mysql_num_rows($res)>0)
		{
			$this->resultArr['numrows'] = mysql_num_rows($res);

			while( $arr = mysql_fetch_assoc($res))
			{
				$this->resultArr['data'][] = $arr;
			}
		}
		else
		{
			$this->resultArr['numrows'] = 0;
			$this->resultArr['data'] = null;
		}
		return $this->resultArr;
	}

	public function UpdateFields($dataarray,$wharecond,$without_quote_flag = 0)
	{
		$update_status = array();
		
		$tblnm ="";
		$tbldata= array();
		if(count($dataarray))
		{
			foreach($dataarray as $key=>$val)
			{
				$tblnm  = trim($key);
				$tbldataArr = $val;

				if($tblnm)
				{
					$val = $this->settablename($tblnm);
					if($val!==1)
					return $val;
				}
		
				//$array_diff = array_diff($fieldarr,$this->requestedtableFieldArr);
				$array_diff = array();

				if(count($array_diff))
				{
					$Field = count($array_diff)==1?"Field":"Fields";

					return $this->errorMessage( $Field." ".implode(',',$array_diff)." not present in table ".$this -> tablename);
				}

				if($this->tablename == null)
				{
					return $this->errorMessage("Table not selected");
				}

				$var = $this->setMapTableindex();
				if($var!==1)
				{
					return $var;
				}

				$finaltablename = $finaltablename = $this->getFinalTableName($this->tablename,$this->tableno);

				$querystr="";
				foreach($tbldataArr as $col=>$val)
				{
					if($without_quote_flag == 1)
					{
						$querystr.= $col."=".addslashes($val).",";
					}
					else
					{
						$querystr.= $col."='".addslashes($val)."',";
					}
				}
				$querystr = trim($querystr , ",");
				$querystr = " UPDATE  ".$finaltablename. " SET ".$querystr ." WHERE ".$wharecond;
				
				$res =  $this->conn_iro->query_sql($querystr);
				
				if($res)
				{$update_status[$tblnm]['status']= 1;}
				else 
				{$update_status[$tblnm]['status']= 0;}				
		}		
	}
	return $update_status;
}	
	
	public function UpdateRow($dataarray)
	{
		$tblnm ="";
		$tbldata= array();
		if(count($dataarray))
		{
			foreach($dataarray as $key=>$val)
			{
				$tblnm  = trim($key);
				$tbldataArr = $val;

				if($tblnm)
				{
					$val = $this->settablename($tblnm);
					if($val!==1)
					return $val;
				}
				//echo "<pre>dataarray "; print_r($tbldataArr);
				$parentid_str= "";
				$parentid = $tbldataArr['parentid'];

				if(trim($parentid)=="")
				{
					return $this->errorMessage("parentid not present in data array");
				}else
				{
					$this -> parentid  = $parentid;
					$parentid_str = "parentid = '".$parentid."', ";
					unset($tbldataArr['parentid']);
				}
				

				if(array_key_exists ("sphinx_id",$tbldataArr))
				{
					$sphinx_id = " sphinx_id = '".$tbldataArr['sphinx_id']."', ";
					unset($tbldataArr['sphinx_id']);
				}else // if there is no sphinx_id then get the sphinx_id
				{
					$querysphinx = "SELECT sphinx_id FROM tbl_id_generator where parentid='".$this -> parentid."' ";
					$rssphinx = $this->conn_iro->query_sql($querysphinx);
					$arr_sphinx = mysql_fetch_assoc($rssphinx);
					$sphinx_id = $arr_sphinx['sphinx_id'];
					$sphinx_id = " sphinx_id = '".$sphinx_id."', ";
				}



				//$this->populateFieldname($this -> tablename);
				$fieldarr = array_keys($tbldataArr);

				//$array_diff = array_diff($fieldarr,$this->requestedtableFieldArr);
				$array_diff	= array();// temp handling

				if(count($array_diff))
				{
					$Field = count($array_diff)==1?"Field":"Fields";

					return $this->errorMessage( $Field." ".implode(',',$array_diff)." not present in table ".$this -> tablename);
				}

				if($this->tablename == null)
				{
					return $this->errorMessage("Table not selected");
				}

				$var = $this->setMapTableindex();
				if($var!==1)
				{
					return $var;
				}

				$finaltablename = $finaltablename = $this->getFinalTableName($this->tablename,$this->tableno);

				$querystr="";
				$querystr_ondup="";
				foreach($tbldataArr as $col=>$val)
				{
					$querystr.= $col."='".addslashes(stripslashes($val))."',";
					
					if($col != 'sphinx_id'  && $col != 'parentid' && $col != 'createdby' && $col != 'createdtime' && $col != 'original_creator' && $col != 'original_date')
					{
						$querystr_ondup.= $col."='".addslashes(stripslashes($val))."',";
					}
				}

				$querystr = trim($querystr , ",");
				$querystr_ondup = trim($querystr_ondup , ",");
				
				$querystr = "INSERT INTO ".$finaltablename. " SET ".$sphinx_id.$parentid_str.$querystr ." ON DUPLICATE KEY UPDATE ".$querystr_ondup;
				$res = $this->conn_iro->query_sql($querystr);
				$this->logmsg($querystr, $res);
				if($finaltablename!='tbl_companymaster_generalinfo_shadow' && $finaltablename!='tbl_companymaster_extradetails_shadow')
				{
					//$this->removeunmatcheddata($finaltablename);
				}

			}
		}
	}

	private function removeunmatcheddata()
	{
		$tablenotoremove = array();

		$sql = "SELECT group_concat(distinct(tableno)) as alltableno from tbl_company_datacity_mapping ";
		$res = $this -> conn_iro->query_sql($sql);
		if(mysql_num_rows($res)>0)
		{
			$res_arr= mysql_fetch_assoc($res);
			if(trim($res_arr['alltableno'])!="")
			{
				$alltableno = $res_arr['alltableno'];
				$alltablenoarr = explode(",",$alltableno);
				$table_notoremove_Arr = array_diff($alltablenoarr,array($this->tableno));

			}
		}

		if(count($table_notoremove_Arr))
		{
			foreach($table_notoremove_Arr as $tabledelindex)
			{
				$finaltablename = $this->getFinalTableName($this->tablename,$tabledelindex);
				$delquerystr = " DELETE from ".$finaltablename." where parentid='".$this -> parentid ."'";
				$res = $this->conn_iro->query_sql($delquerystr);
				$this->logmsg($delquerystr, $res);
			}
		}

	}


	private function select_query($fieldarr,$ftablename,$query_cond)
	{
		$this->resultArr = array();
		$fielnamestr = implode("," ,$fieldarr);
		$sql = "select ".$fielnamestr." from " .$ftablename." where ".$query_cond;
		$res = $this -> conn_iro-> query_sql($sql);
		if($res && mysql_num_rows($res)>0)
		{
			$this->resultArr['numrows'] = mysql_num_rows($res);

			while( $arr = mysql_fetch_assoc($res))
			{
				$this->resultArr['data'][] = $arr;
			}
		}
		else
		{
			$this->resultArr['numrows'] = 0;
			$this->resultArr['data'] = null;
		}
		//$this->logmsg($sql, json_encode($this->resultArr)); no need of this log
	}


	function getFinalTableName($tablename,$tableno)
	{
		if($this->is_split === FALSE)
		{
			return $tablename; // when we integrate the tables mapping then we will use below logic
		}
		 
		
		$shadow_tables = array("tbl_companymaster_extradetails_shadow","tbl_companymaster_generalinfo_shadow");
		$returntblname=null;
		if(in_array($tablename,$shadow_tables))
		{
			$returntblname= $tablename;
		}
		else
		{
			//$returntblname = $tablename."_".$tableno; //when split logic will be implemented it will be removed
			$returntblname = $tablename;
		}
		return $returntblname;
	}

	function logmsg($msg, $result)
    {
		return ; // no loging now 
		
		$date = date("d");
		$year = date("y");
		$month= date("m");
		$sNamePrefix	= APP_PATH . 'logs/compmaster/'.$year."/".$month."/".$date."/";
        $log_msg='';
        $pathToLog = dirname($sNamePrefix);
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        if(!file_exists($sNamePrefix))
		{
			mkdir($sNamePrefix, 0777, true);
		}

        $file_n=$sNamePrefix.$this->parentid.".html";

        $logFile = fopen($file_n, 'a+');

        $pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
        $log_msg.= "<div style='width:100%;float:left;'>
							<div style='width:15%; border:1px solid #669966; float:left; word-wrap: break-word; padding-left:5px; height:70px;'><B>Date :</B>".date('Y-m-d H:i:s')."</div>
							<div style='width:40%; border:1px solid #669966; float:left; word-wrap: break-word; padding-left:5px; height:70px; overflow-y:auto;'>".$msg."</div>
							<div style='width:40%; border:1px solid #669966; float:left; word-wrap: break-word; padding-left:5px; height:70px; overflow-y:auto;'>".$result."</div>
					</div>";

        fwrite($logFile, $log_msg);
        fclose($logFile);
    }
    
	//------------------------------------------------ Function where we don't have data city -----------------------
    public function getTableNameArr($tablename)
    {	
		$tablename = strtolower($tablename);
		
		if($tablename=='tbl_companymaster_generalinfo' || $tablename=='tbl_companymaster_extradetails' || $tablename=='tbl_companymaster_search' )
		{
			return $this->arr_company_tables[$tablename];
		}
		else
		{
			die("Table is name is not proper");
		}
    }
    
    public function getRow_WDC($fieldstr="*" , $tablename=null,$wherecond=null) // get row without data city 
	{
		$fieldarr = explode(",",$fieldstr);
		$fieldarr =  array_filter($fieldarr);
		
		if(count($fieldarr))
		{
			foreach($fieldarr as $key=>$val)
			{
				$fieldarr[$key]=trim($val);
			}
		}		
		//echo "<pre>"; print_r($fieldarr);		
		if($tablename)
		{
			$TableNameArr = $this->getTableNameArr($tablename);
		}
		
		if(count($TableNameArr) == 0)
		{
			return $this->errorMessage("Table not selected");
		}
	
		if($wherecond!=null)
		{
			$query_cond = $wherecond;
		}

		$resultArr_WDC = $this->select_query_WDC($fieldarr,$TableNameArr,$query_cond);
		return $resultArr_WDC;
	}
	
	private function select_query_WDC($fieldarr,$ftablenameArray,$query_cond)
	{		
		$fielnamestr = implode("," ,$fieldarr);
		$returnresultArr = array();
		$returnresultArr['numrows']=0;	
		
		foreach ($ftablenameArray as $ind=>$ftablename)
		{
		
			$sql = "select ".$fielnamestr." from " .$ftablename." where ".$query_cond;			
			//echo "<br>query--".$sql;
			$res = $this -> conn_iro-> query_sql($sql);
			if($res && mysql_num_rows($res)>0)
			{
				$returnresultArr['numrows'] = $returnresultArr['numrows']+ mysql_num_rows($res);

				while( $arr = mysql_fetch_assoc($res))
				{
					$returnresultArr['data'][] = $arr;
				}
			}
		}
	
		if($returnresultArr['numrows']==0)
		{
			$returnresultArr['numrows'] = 0;
			$returnresultArr['data'] = null;
		}
		
		return $returnresultArr;
	}


	public function UpdateFields_WDC($dataarray,$wharecond,$without_quote_flag=0)
	{
		$update_status = array();
		
		$tblnm ="";
		$tbldata= array();
		if(count($dataarray))
		{
			foreach($dataarray as $key=>$val)
			{
				$tablename  = trim($key);
				$tbldataArr = $val;

				if($tablename)
				{
					$TableNameArr = $this->getTableNameArr($tablename);
				}
		
				//$array_diff = array_diff($fieldarr,$this->requestedtableFieldArr);
				
				$querystr="";
				foreach($tbldataArr as $col=>$val)
				{
					if($without_quote_flag == 1)
					{
						$querystr.= $col."=".addslashes($val).",";
					}
					else
					{
						$querystr.= $col."='".addslashes($val)."',";
					}
				}
				$updatequerystr = trim($querystr , ",");
				
				//echo "<pre>"; print_r($TableNameArr);
				
				$result =  $this->update_query_WDC($TableNameArr,$updatequerystr,$wharecond);
				
				if($result)
				{$update_status[$tablename]['status']= 1;}
				else 
				{$update_status[$tablename]['status']= 0;}
			}		
		}
		return $update_status;
	}
	
	private function update_query_WDC($tablenameArray,$querystr,$where_cond)
	{			
		$returnresultArr = array();
		$result=0;	
		
		foreach ($tablenameArray as $ind=>$tablename)
		{		
			$updsql = " UPDATE ".$tablename. " SET ".$querystr ." WHERE ".$where_cond;
			$res = $this -> conn_iro-> query_sql($updsql);
			if($res)
			{$result=1;}			
		}
		return $result;	
	}
	
	public function joinRow_WDC($joinfiedsname ,$jointablesname,$joincondon,$wherecond ,$orderbycond= null, $limit=null)
	{
		if($limit)
		{$limit= " limit ".$limit;}

		$this->resultArr = array();
		$jointablesnameArr = explode(",",$jointablesname);

		foreach($jointablesnameArr as $key=>$tblnm)
		{
			$jointablesnameArr[$key] = $this->getmatchedtable($tblnm);
		}

		$jointablesname = implode(",",$jointablesnameArr);

		$querystr= " select ".$joinfiedsname." FROM ".$jointablesname." ".$joincondon." WHERE ".$wherecond.$orderbycond.$limit;


		$res = $this->conn_iro->query_sql($querystr);
		$this->logmsg($querystr, $res);

		if($res && mysql_num_rows($res)>0)
		{
			$this->resultArr['numrows'] = mysql_num_rows($res);

			while( $arr = mysql_fetch_assoc($res))
			{
				$this->resultArr['data'][] = $arr;
			}
		}
		else
		{
			$this->resultArr['numrows'] = 0;
			$this->resultArr['data'] = null;
		}
		return $this->resultArr;
	}
	public function getSubQueryRows($cnt, $arr_query)
	{
		$this->resultArr = array();
		$q1_f = '';
		$q1_t = '';
		$q1_w = '';
		$q1_l = '';
		$q1_g = '';
		$q1_o = '';
		$str1 = '';
		
		for($i=0; $i<$cnt; $i++)
		{
			if($i == 0)
			{
				$q1_f	= $arr_query[0]['fields'];
				$q1_t 	= $this->getCleanTables($arr_query[0]['tablenames']);				
				$q1_w	= $this->getCleanCondition('where', $arr_query[0]['where']);
				$q1_j	= $this->getCleanCondition('on', $arr_query[0]['join_on']);
				$q1_g	= $this->getCleanCondition('group_by', $arr_query[0]['group_by']);
				$q1_o	= $this->getCleanCondition('order_by', $arr_query[0]['order_by']);
				$q1_l	= $this->getCleanCondition('limit', $arr_query[0]['limit']);
				
				$str_subq[$i]	= " SELECT " . $q1_f . " FROM " . $q1_t . $q1_j . $q1_w . $q1_g . $q1_o . $q1_l;
			}
			else
			{
				$q1_f	= $arr_query[$i]['fields'];

				$q1_t 	= $this->getCleanTables($arr_query[$i]['tablenames']);
				$q1_w	= $this->getCleanCondition('where', $arr_query[$i]['where']);
				$q1_j	= $this->getCleanCondition('on', $arr_query[$i]['join_on']);
				$q1_g	= $this->getCleanCondition('group_by', $arr_query[$i]['group_by']);
				$q1_o	= $this->getCleanCondition('order_by', $arr_query[$i]['order_by']);
				$q1_l	= $this->getCleanCondition('limit', $arr_query[$i]['limit']);
				
				$prev_t = $i-1;
				
				$str_subq[$i] = " SELECT " . $q1_f . " FROM (".$str_subq[$prev_t].") " . $q1_t .  $q1_j . $q1_w . $q1_g . $q1_o . $q1_l;
			}
		}
		$cn_minus 	= $cnt-1;
		$sql_final 	= $str_subq[$cn_minus];
		if($this->is_split === TRUE)
		{
			$arr_final_data = execQrySingle($sql_final);
		}
		else
		{
			$arr_final_data  = $this->commonQryExec($sql_final);
		}
		return $arr_final_data;
	}
	
	public function getCleanTables($str_table_names)
	{
		$arr_table_names = explode(",",$str_table_names);
		
		foreach($arr_table_names as $key=>$tblnm)
		{
			$arr_table_names1[$key] = $this->getmatchedtable($tblnm);
		}

		$str_table_names = implode(",",$arr_table_names1);
		return $str_table_names;
	}
	
	public function getCleanCondition($keynames, $str_val)
	{
		$str_val = trim($str_val);
		if(!empty($str_val))
			$str_val 	= " " . strtoupper(str_replace('_', ' ', $keynames)) . " " .$str_val;
		else
			$str_val 	= '';
		
		return $str_val;
	}
	
	public function commonQryExec($query)
	{
		$arr_no_querys = array();
		
		$res = $this->conn_iro->query_sql($query);
		if($res && mysql_num_rows($res)>0)
		{
			$numrows = mysql_num_rows($res);

			while( $arr = mysql_fetch_assoc($res))
			{
				$arr_data['data'][] = $arr;
			}
		}
		else
		{
			$numrows 			= 0;
			$arr_data['data']	= null;
		}
		$this->resultArr['numrows'] = $numrows;
		$this->resultArr['data'] 	= $arr_data['data'];
		return $this->resultArr;
	}
	
	public function replaceWithALLCMT($query)
	{
		
		$arr_return_queries = array();
		$arr_cmt 			= $this->arr_company_tables;
		$query1				= $query;
		$my_qry				= '';
		$arr_queries		= array();
		foreach($arr_cmt as $key_tables => $arr_subtables)
		{
			$i=0;
			foreach($arr_subtables as $subtables)
			{
				if(!empty($arr_queries[$i]))
					$my_qry = $arr_queries[$i];
				else
					$my_qry = $query1;
					
				$arr_queries[$i] = str_replace($key_tables, $subtables, $my_qry);
				$i++;
			}
		}
		return $arr_queries;
	}
	
	public function setCompanyTables()
	{
		if($this->is_split === TRUE)
		{
			$this->arr_company_tables['tbl_companymaster_generalinfo'] = array('tbl_companymaster_generalinfo_1','tbl_companymaster_generalinfo_2','tbl_companymaster_generalinfo_3','tbl_companymaster_generalinfo_4','tbl_companymaster_generalinfo_5');
			
			$this->arr_company_tables['tbl_companymaster_extradetails'] = array('tbl_companymaster_extradetails_1','tbl_companymaster_extradetails_2','tbl_companymaster_extradetails_3','tbl_companymaster_extradetails_4','tbl_companymaster_extradetails_5');
			
			$this->arr_company_tables['tbl_companymaster_search'] = array('tbl_companymaster_search_1','tbl_companymaster_search_2','tbl_companymaster_search_3','tbl_companymaster_search_4','tbl_companymaster_search_5');	
		}
		else
		{
			$this->arr_company_tables['tbl_companymaster_generalinfo']  = array('tbl_companymaster_generalinfo');
			$this->arr_company_tables['tbl_companymaster_extradetails']	=  array('tbl_companymaster_extradetails');
			$this->arr_company_tables['tbl_companymaster_search'] 		=  array('tbl_companymaster_search');
		}
	}
	
	public function execQrySingle($query)
	{
		$arr_queries 	= $this->replaceWithALLCMT($query);
		$arr_ret_data 	= array();
		$arr_final_data = array();
		$i = 0;
		foreach($arr_queries as $qrs)
		{
			$arr_ret_data[$i] = $this->commonQryExec($qrs);
			
			if(!isset($arr_final_data['numrows']))
				$arr_final_data['numrows']	= $arr_ret_data[$i]['numrows'];					
			else
				$arr_final_data['numrows']	+= $arr_ret_data[$i]['numrows'];
				
			if(!isset($arr_final_data['data']))
				$arr_final_data['data']	= $arr_ret_data[$i]['data'];
			else
				$arr_final_data['data']	= array_merge($arr_final_data['data'], (array) $arr_ret_data[$i]['data']);
			
			$i++;
		}		
		return $arr_final_data;
	}
}
