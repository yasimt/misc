<?php
# Requisites Check: #
#####################
// changes needed
class bidperday_regen{

	private $cityid, $parentid, $module, $userid, $conn_iro, $conn_local, $conn_finance;
	private $compcatarea_gen_called;


	public function __construct($cityid,$parentid,$dbarr,$userid){

		$this->parentid	= $parentid;
		$this->cityid		= $cityid;
		$this->userid		= $userid;
		$this->conn_iro		= new DB($dbarr['DB_IRO']);
		$this->conn_local	= new DB($dbarr['LOCAL']);
		$this->conn_finance	= new DB($dbarr['FINANCE']);
	}

	function __destruct(){

		unset($this->conn_iro);
		unset($this->conn_local);
		unset($this->conn_finance);
	}

	function bidperday_gen(){
		
		return;

		$create_table_bidvalues_query="CREATE TABLE IF NOT EXISTS `tbl_bidvalues_calculated` (
		  `parentid` varchar(45) NOT NULL DEFAULT '',
		  `bid_catid` int(10) unsigned NOT NULL,
		  `pincode` varchar(15) NOT NULL DEFAULT '',
		  `position_flag` tinyint(4) unsigned NOT NULL DEFAULT '0',
		  `partial_ddg_ratio` double(16,5) DEFAULT '0.00000',
		  `bpd_bidcatdetails` double(16,5) DEFAULT NULL,
		  `bpd_cdpdm` double(16,5) DEFAULT '0.00000',
		  `platinum_value` double(16,5) DEFAULT '0.00000',
		  `callcnt` double(16,5) DEFAULT '0.00000',
		  `position_flagvalue_chosen` varchar(14) CHARACTER SET utf8 NOT NULL DEFAULT '',
		  `contribution` double(22,5) DEFAULT NULL,
		  `categorywise_bidvalue` decimal(16,6) DEFAULT NULL,
		  `latestuptdatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  KEY `parentid` (`parentid`),
		  KEY `bid_catid` (`bid_catid`),
		  KEY `pincode` (`pincode`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$result_create_table_bidvalues_query=$this ->conn_finance->query_sql($create_table_bidvalues_query);

		$create_table_ddgpin_query="CREATE TABLE IF NOT EXISTS `tbl_ddgpin_entries` (
		  `parentid` varchar(45) NOT NULL DEFAULT '',
		  `bid_catid` int(10) unsigned NOT NULL,
		  `pincode` varchar(15) NOT NULL DEFAULT '',
		  `bidperday` decimal(16,6) DEFAULT NULL,
		  `latestuptdatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  KEY `parentid` (`parentid`),
		  KEY `bid_catid` (`bid_catid`),
		  KEY `pincode` (`pincode`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$result_create_table_bcd_query=$this ->conn_finance->query_sql($create_table_ddgpin_query);

		$create_table_idc_query="CREATE TABLE IF NOT EXISTS `tbl_idc_entries` (
		  `parentid` varchar(45) NOT NULL DEFAULT '',
		  `bid_catid` int(10) unsigned NOT NULL,
		  `pincode` varchar(15) NOT NULL DEFAULT '',
		  `bidperday` decimal(16,6) DEFAULT NULL,
		  `latestuptdatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  KEY `parentid` (`parentid`),
		  KEY `bid_catid` (`bid_catid`),
		  KEY `pincode` (`pincode`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$result_create_table_idc_query=$this ->conn_finance->query_sql($create_table_idc_query);

		$create_table_bcd_query="CREATE TABLE IF NOT EXISTS `tbl_bcd_entries` (
		  `parentid` varchar(45) NOT NULL DEFAULT '',
		  `bid_catid` int(10) unsigned NOT NULL,
		  `pincode` varchar(15) NOT NULL DEFAULT '',
		  `bidperday` decimal(16,6) DEFAULT NULL,
		  `latestuptdatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  KEY `parentid` (`parentid`),
		  KEY `bid_catid` (`bid_catid`),
		  KEY `pincode` (`pincode`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1";
		$result_create_table_bcd_query=$this ->conn_finance->query_sql($create_table_bcd_query);

		$var_parentid	= $this->parentid;
		$var_cityid		= $this->cityid;

		$getcity_query="SELECT count(distinct pincode) as pincodecount FROM d_jds.tbl_area_master WHERE display_flag=1 AND deleted=0 AND data_city IN (SELECT cityname FROM tbl_data_city)";
		$result_getcity_query=$this -> conn_local->query_sql($getcity_query);

		$row				= mysql_fetch_array($result_getcity_query);
		$var_pincodecount	= $row[0];

		$delete_query="DELETE FROM tbl_bidvalues_calculated WHERE parentid='".$var_parentid."'";
		$result_drop_query=$this -> conn_finance->query_sql($delete_query);


		# Fetching data from 3 tables - pincodewisebid, bcd and perday master
		#######################################################################

		$select_query="SELECT x1.parentid, bid_catid, x1.pincode, position_flag, partial_ddg_ratio, bidperday as bpd_bidcatdetails,
			z1.bid_perday as bpd_cdpdm,
			platinum_value, y1.callcnt,
			if(position_flag=15,'platinum_value',if(position_flag=10,'diamond_value','bronze_value')) as position_flagvalue_chosen,
			if(position_flag=15,if((platinum_value=0.00000 and w1.imparent='B2B'),100,if((platinum_value=0.00000 and  (w1.imparent!='B2B' or imparent is null)),5,platinum_value)) ,
      		if(position_flag=10,if((diamond_value=0.00000 and w1.imparent='B2B'),(100*0.85),if((diamond_value=0.00000 and  (w1.imparent!='B2B' or imparent is null)),(5*0.85),diamond_value)),
			if((bronze_value=0.00000 and w1.imparent='B2B'),(100*0.70),if((bronze_value=0.00000 and (w1.imparent!='B2B' or imparent is null)),(5*0.70),bronze_value))))*if(y1.callcnt<=0,1/180,y1.callcnt)*partial_ddg_ratio as contribution
			FROM
			tbl_bidcatdetails_ddg x1 LEFT JOIN temp_tbl_category_master_copy w1 ON (x1.bid_catid = w1.catid), tbl_platinum_diamond_pincodewise_bid y1, db_finance.tbl_companymaster_finance z1
			WHERE x1.parentid='".$var_parentid."'
			AND x1.bid_catid = y1.catid
			AND x1.pincode = y1.pincode
			and x1.campaignid=2
			AND z1.parentid = x1.parentid
			AND z1.campaignid = x1.campaignid
			GROUP BY bid_catid,x1.pincode
			ORDER BY x1.parentid, bid_catid,x1.pincode";

		$result_select_query=$this -> conn_finance->query_sql($select_query);
		$var_num_rows = mysql_num_rows($result_select_query);

		# Check if everything is present locally if not fetch from IDC in else condition
		IF ($var_num_rows > 0){

			$insert_query="INSERT INTO tbl_bidvalues_calculated(parentid,bid_catid,pincode,position_flag,partial_ddg_ratio,bpd_bidcatdetails,bpd_cdpdm,platinum_value,callcnt,position_flagvalue_chosen,contribution)
			SELECT x1.parentid, bid_catid, x1.pincode, position_flag, partial_ddg_ratio, bidperday as bpd_bidcatdetails,
			z1.bid_perday as bpd_cdpdm,
			platinum_value, y1.callcnt,
			if(position_flag=15,'platinum_value',if(position_flag=10,'diamond_value','bronze_value')) as position_flagvalue_chosen,
			if(position_flag=15,if((platinum_value=0.00000 and w1.imparent='B2B'),100,if((platinum_value=0.00000 and  (w1.imparent!='B2B' or imparent is null)),5,platinum_value)) ,
      		if(position_flag=10,if((diamond_value=0.00000 and w1.imparent='B2B'),(100*0.85),if((diamond_value=0.00000 and  (w1.imparent!='B2B' or imparent is null)),(5*0.85),diamond_value)),
			if((bronze_value=0.00000 and w1.imparent='B2B'),(100*0.70),if((bronze_value=0.00000 and (w1.imparent!='B2B' or imparent is null)),(5*0.70),bronze_value))))*if(y1.callcnt<=0,1/180,y1.callcnt)*partial_ddg_ratio as contribution
			FROM
			tbl_bidcatdetails_ddg x1 LEFT JOIN temp_tbl_category_master_copy w1 ON (x1.bid_catid = w1.catid), tbl_platinum_diamond_pincodewise_bid y1, tbl_companymaster_finance z1
			WHERE x1.parentid='".$var_parentid."'
			AND x1.bid_catid = y1.catid
			AND x1.pincode = y1.pincode
			and x1.campaignid=2
			AND z1.parentid = x1.parentid
		    AND z1.campaignid = x1.campaignid
			GROUP BY bid_catid,x1.pincode
			ORDER BY x1.parentid, bid_catid,x1.pincode";
			$result_insert_query=$this -> conn_finance->query_sql($insert_query);

			$update_query="UPDATE tbl_bidvalues_calculated x1,(
																SELECT parentid, sum(contribution) as contribution_parentid
																FROM tbl_bidvalues_calculated
																GROUP BY parentid
															  )y1
			SET x1.categorywise_bidvalue = ( x1.contribution/y1.contribution_parentid ) * x1.bpd_cdpdm
			WHERE x1.parentid = y1.parentid
			AND x1.parentid='".$var_parentid."'";
			$result_update_query=$this -> conn_finance->query_sql($update_query);

			$update_bcd_query="UPDATE tbl_bidvalues_calculated x1, tbl_bidcatdetails_ddg y1
			SET y1.bidperday = x1.categorywise_bidvalue
			WHERE x1.parentid = y1.parentid
			AND x1.bid_catid = y1.bid_catid
			AND x1.pincode = y1.pincode
			and y1.campaignid=2
			AND x1.parentid='".$var_parentid."'";
			$result_update_bcd_query=$this -> conn_finance->query_sql($update_bcd_query);

			$update_d_dg_query="UPDATE tbl_bidvalues_calculated x1, tbl_d_dg_pin_dealclosed y1
			SET y1.bidperday = x1.categorywise_bidvalue
			WHERE x1.parentid = y1.parentid
			AND x1.bid_catid = y1.bid_catid
			AND x1.pincode = y1.pincode
			and y1.campaignid=2
			AND x1.parentid='".$var_parentid."'";
			$result_update_d_dg_query=$this -> conn_finance->query_sql($update_d_dg_query);

			$delete_ddg_pin_query="DELETE FROM tbl_ddgpin_entries WHERE parentid='".$var_parentid."'";
			$result_delete_ddg_pin_query=$this->conn_finance->query_sql($delete_ddg_pin_query);

			$ddg_pin_query="INSERT INTO tbl_ddgpin_entries(parentid,bid_catid,pincode,bidperday)SELECT parentid, bid_catid, pincode, bidperday FROM tbl_d_dg_pin_dealclosed WHERE  campaignid=2 and parentid='".$var_parentid."'";
			$result_ddg_pin_query=$this->conn_finance->query_sql($ddg_pin_query);

			$delete_bcd_query="DELETE FROM tbl_bcd_entries WHERE parentid='".$var_parentid."'";
			$result_delete_bcd_query=$this->conn_finance->query_sql($delete_bcd_query);

			$bcd_query="INSERT INTO tbl_bcd_entries(parentid,bid_catid,pincode,bidperday)SELECT parentid, bid_catid, pincode, bidperday FROM tbl_bidcatdetails_ddg WHERE  campaignid=2 and parentid='".$var_parentid."'" ;
			$result_bcd_query=$this->conn_finance->query_sql($bcd_query);

			$compare_query="SELECT x1.* FROM
			(SELECT parentid, bid_catid, pincode, bidperday FROM tbl_ddgpin_entries WHERE parentid='".$var_parentid."') x1 LEFT JOIN (SELECT parentid, bid_catid, pincode, bidperday FROM tbl_bcd_entries WHERE parentid='".$var_parentid."') y1 ON (x1.parentid = y1.parentid AND x1.bid_catid = y1.bid_catid AND x1.pincode = y1.pincode)
			WHERE y1.parentid IS NULL";
			$result_compare_query=$this->conn_finance->query_sql($compare_query);
			$numberofrows = mysql_num_rows($result_compare_query);

			IF ($numberofrows>0){ddgpindealclose_logic($var_cityid, $var_parentid);}
		}
		ELSE{

				ddgpindealclose_logic($var_cityid, $var_parentid);

		} # End of Else Condition

		return;

	}

}

?>
