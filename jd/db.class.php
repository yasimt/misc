<?php

class DB
{	
	/*
	protected $con = null;		
	protected $val = null;	
	
	function __construct($dbarr)
	{					
		$this->val= $val_1;
		
		$this->con = mysql_connect($dbarr[0], $dbarr[1], $dbarr[2])  or die("couldn't connect to " . $dbarr[0]);
		
		if(!empty($dbarr[3]))
			mysql_select_db($dbarr[3], $this->con);
		else
			mysql_select_db('d_jds', $this->con);
	}
	*/
	public $mysql_error;
	public $lastResult;
	public function execQuery($query, $db)
	{
		$this->lastResult = NULL;
		$scrFilename= basename($_SERVER['SCRIPT_FILENAME']);
		$this->mysql_error	='';
		$con = mysql_connect($db[0], $db[1], $db[2]) or $this->log_errors("couldn't connect to $db[0]"."\r\n at ".date('Y-m-d H:i:s')." ".mysql_error()."\n Base file ".$scrFilename);
		
		mysql_select_db($db[3], $con);
		
		//$query = "/*".$_SERVER['REMOTE_ADDR']."|~|".$_SERVER['PHP_SELF']."|~|".$db[3]."*/".$query;
		$result = mysql_query($query, $con) or $this->log_errors("error found while executing query $db[0]-$db[3] at ".date('Y-m-d H:i:s')." , QUERY: $query"." @@@ ".mysql_error()."\n Base file ".$scrFilename);
		
		
		
		if((($db[0] == '192.168.6.52') || ($db[0] == '192.168.17.233')) && ((stripos($query, 'tbl_companymaster_generalinfo_shadow') !== false) || (stripos($query, 'tbl_companymaster_extradetails_shadow') !== false) || (stripos($query, 'tbl_business_temp_data') !== false) || (stripos($query, 'tbl_temp_intermediate') !== false))){
			
			$query_new		= trim(addslashes($query));
			$query_new		= preg_replace("/[\r\n]{2,}/", " ", $query_new);
			$query_new		= preg_replace("/[\t]{2,}/", " ", $query_new);
			
			$query_new = "/*".$_SERVER['REQUEST_URI']."*/".$query_new;
			
			$query_new = mysql_real_escape_string($query_new,$con);
			
			$sqlMongoTableData = "INSERT INTO online_regis1.tbl_mongo_data SET query = '".$query_new."' ,flow = 'JDBOX' , insertdate = '".date("Y-m-d H:i:s")."'";
			//$resMongoTableData = mysql_query($sqlMongoTableData,$con);
		}
		
		$local_server_arr = array("172.29.67.213","192.168.17.171");
		if((in_array($db[0],$local_server_arr)) && ((stripos($query, 'tbl_companymaster_generalinfo_shadow') !== false) || (stripos($query, 'tbl_companymaster_extradetails_shadow') !== false) || (stripos($query, 'tbl_business_temp_data') !== false) || (stripos($query, 'tbl_temp_intermediate') !== false)) && (($db[3] == 'tme_jds') || (stripos($query, 'tme_jds') !== false)) && (stripos($query, 'TMEMONGOQRY2') === false)){

			$query_new		= trim(addslashes($query));
			$query_new		= preg_replace("/[\r\n]{2,}/", " ", $query_new);
			$query_new		= preg_replace("/[\t]{2,}/", " ", $query_new);
			
			$query_new = "/*".$_SERVER['REQUEST_URI']."*/".$query_new;
			
			$query_new = mysql_real_escape_string($query_new,$con);
			
			$sqlMongoTableData = "INSERT INTO tme_jds.tbl_mongo_data SET query = '".$query_new."' ,flow = 'JDBOX-TME' , insertdate = '".date("Y-m-d H:i:s")."'";
			//$resMongoTableData = mysql_query($sqlMongoTableData,$con);
		}
		if((in_array($db[0],$local_server_arr)) && ((stripos($query, 'tbl_categorymaster_generalinfo') !== false) && (stripos($query, 'CATSQL') === false))){

			$query_new		= trim(addslashes($query));
			$query_new		= preg_replace("/[\r\n]{2,}/", " ", $query_new);
			$query_new		= preg_replace("/[\t]{2,}/", " ", $query_new);
			
			$query_new = "/*".$_SERVER['REQUEST_URI']."*/".$query_new;
			
			$query_new = mysql_real_escape_string($query_new,$con);
			
			$sqlMongoTableData = "INSERT INTO db_iro.tbl_cat_api SET sql_log = '".$query_new."' ,source = 'JDBOX' , updatetime = '".date("Y-m-d H:i:s")."'";
			//$resMongoTableData = mysql_query($sqlMongoTableData,$con);
		}
		
		$this->lastResult = $result;

		if($result == 1)
		{
			$this->mysql_insert_id	= mysql_insert_id($con);
		}
		
		mysql_close($con);
		
		return $result;
		
	}
	
	public function log_errors($str_log)
	{
		$this->mysql_error=$str_log;
		$content	= "";
		$content2	= "";
		$filename 	= LOG_PATH."/logs/mysql_errors/jdbox_".date('Y-m-d').".txt";
		
		$handle		= fopen($filename, 'a+') or die("Couldn't create new file :".$filename." <br>error:".$str_log);
		$content 	= htmlentities($str_log);
		$content2 	= strip_tags($content);
		$content2 	.= "\r\n===============================================================================================================\r\n";
		fwrite($handle, $content2);
		fclose($handle);
		
		
		$email_text	= '';
		$email_text .= '<br>';
		$email_text .= "<br>Error on ".$_SESSION['SERVER_ADDR']." : " . $str_log;
		$email_text .= '<br>';
		$email_text .= '<br>';
		
	}
	function numRows($result = NULL) {
		if($result == NULL)
			return mysql_num_rows($this->lastResult);
		else
			return mysql_num_rows($result);
	}
	function fetchData($result = NULL) {
		if($result == NULL)
		   $result = $this->lastResult;

		if($result == NULL || mysql_num_rows($result) < 1)
		   return NULL;
		else
		   return mysql_fetch_assoc($result);
	}
	/*	
	function __destruct()
	{
		mysql_close($this->con);
	}
	*/	
}
