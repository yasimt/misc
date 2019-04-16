<?php

// This class contains all category master related function we are going to implement this class to remove all direct query from other pages
// we will enrich this class gradually
//function are of generic nature which work as per arguments
//function will return array in general but return other data type also as per requirement
//Person to contact- pramesh,faizan,amit patil 

class categoryMaster{

public $conn_finance,$conn_local,$conn_iro,$parentid,$sphinx_id;
private $dbarr,$module;

//d_jds.tbl_category_master

function __construct ($dbarr,$module='',$curl=0)
{
	$this->dbarr=$dbarr;
	$this->module=strtolower($module); // on the base of module we have to take decision on display contract
	//$display_bitwise        = array('display_cs'=>1, 'display_de'=>2,'display_tme'=>4,'display_iro'=>8,'display_web'=>16,'display_wap'=>32);
	$this->conn_local = new DB($dbarr['LOCAL']);
	//echo "<pre>"; print_r($dbarr['LOCAL']);
}

// we accespt single catid or catidlist comma seprated. it will return all catid and associated catname in associative array
// when we want deactive categories we will pass second argument as 1 which will give deactive categories 
function getCatname($catidlist,$deactive=0)
{
	// if catidlist is blank then return null
	if(trim($catidlist)=="")
	{
		return null;
	}
	
	$catidarray = null;
	$catidlistarr = explode(",",$catidlist);
	
	$catidlistarr = array_unique($catidlistarr);
	$catidlistarr = array_filter($catidlistarr);
	$catidliststr = implode(",",$catidlistarr);
		
	// if catidlist is blank then return null
	if(count($catidlistarr)==0)
	{
		return null;
	}
	
	$display_cond="";
//$display_bitwise        = array('display_cs'=>1, 'display_de'=>2,'display_tme'=>4,'display_iro'=>8,'display_web'=>16,'display_wap'=>32);	
	
	$display_cond =" AND active_flag = 1";
	
	if($deactive)
	{ 
		$display_cond="";
	}
	else
	{
		$display_cond.= " AND isdeleted=0 AND mask_status=0 ";

	}
	
	$sqlsel="SELECT catid,category_name,category_type,national_catid from tbl_categorymaster_generalinfo where catid in (".$catidliststr.") ".$display_cond; 
	//echo  $sqlsel;
	$catidrs = $this->conn_local->query_sql($sqlsel);	
	if($catidrs && mysql_num_rows($catidrs))
	{				
		while($catiarr = mysql_fetch_assoc($catidrs))
		{
			$catidarray[$catiarr[catid]]=array(catid=>$catiarr[catid],category_name=>$catiarr[category_name],catname=>$catiarr[category_name],category_type=>$catiarr[category_type],national_catid=>$catiarr[national_catid],nationalcatid=>$catiarr[national_catid]);
		}		
	}
	
	return $catidarray; // either it will return all catid array or null
	
}

// this function takes category name and return it catid and catname array 
function getCatId($catname,$multiple=0,$deactive=0)
{
	
	// if catname is blank then return null
	if(trim($catname)=="")
	{
		return null;
	}
	
	$display_cond="";
//$display_bitwise        = array('display_cs'=>1, 'display_de'=>2,'display_tme'=>4,'display_iro'=>8,'display_web'=>16,'display_wap'=>32);	
	
	if($this->module=="cs")
	{
		$display_cond =" AND (display_flag & 1 =1) ";
	}
	elseif($this->module=="de")
	{
		$display_cond =" AND (display_flag & 2 =2) ";
	}
	elseif($this->module=="tme" || $this->module=="me" )
	{
		$display_cond =" AND (display_flag & 4 =4) ";
	}
	
	if($deactive)
	{ 
		$display_cond="";
	}
	else
	{
		$display_cond.= " AND isdeleted=0 AND mask_status=0 ";
	}
	
	if($multiple)
	{
		$catnamearr = explode(",",$catname);	
		$catnamearr = array_unique($catnamearr);
		$catnamearr = array_filter($catnamearr);
		$catnamearrstr = implode("','",$catnamearr);	
		
		$catnamearrstr= "'".$catnamearrstr."'";
		
		$multiplesql= " category_name in (".$catnamearrstr.") ";
		
	}
	else
	{
		$multiplesql= " category_name = ('".$catname."') ";
	}
	
	$sqlsel="SELECT catid,category_name,category_type from tbl_categorymaster_generalinfo where ".$multiplesql." ".$display_cond; 
	//echo  $sqlsel;
	$catidrs = $this->conn_local->query_sql($sqlsel);	
	if($catidrs && mysql_num_rows($catidrs))
	{	
		//echo "<br>inside<br>"			;
		while($catiarr = mysql_fetch_assoc($catidrs))
		{
			$catidarray[$catiarr[catid]]=array(catid=>$catiarr[catid],category_name=>$catiarr[category_name],catname=>$catiarr[category_name],category_type=>$catiarr[category_type]);
		}
	}
	//echo "<pre>"; print_r($catidarray);
	return $catidarray; // either it will return all catid array or null

}

// we accespt single catid or catidlist comma seprated.
// when we want deactive categories we will pass second argument as 1 which will give deactive categories 
// as per amit patil associate_national_catid 
function getParentCategories($catidlist,$deactive=0)
{	
	$parent_categories_arr = array();
	$catidlistarr = explode(",",$catidlist);
	
	$catidlistarr = array_unique($catidlistarr);
	$catidlistarr = array_filter($catidlistarr);
	$catidliststr = implode(",",$catidlistarr);
	
	if($deactive)
	{ 
		$display_cond="";
	}
	else
	{
		$display_cond.= " AND isdeleted=0 AND mask_status=0 ";
	}
	
	
	$sql = "SELECT group_concat( DISTINCT associate_national_catid) as associate_national_catid FROM tbl_categorymaster_generalinfo where catid in (".$catidliststr.") AND catid>0 AND category_name !='' ".$display_cond;
	$res = $this -> conn_local->query_sql($sql);
	$final_parent_category_arr = array();
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
			$sql = "SELECT group_concat( DISTINCT catid) as parent_categories FROM tbl_categorymaster_generalinfo where national_catid in (".$associate_national_catid_str.") and catid not in (".$catidliststr.") AND catid>0 AND category_name !='' ".$display_cond;
		
			$res = $this -> conn_local->query_sql($sql);
			$final_parent_category_arr = array();
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
	return $parent_categories_arr;
}

// this function takes category name and return it catid and catname array 
function getCatIdMatch($catnamelist,$deactive=0)
{
	//$catname;
	$catnamearray = null;
	$catnamearray = explode(",",$catnamelist);
	
	$catnamearray = array_unique($catnamearray);
	$catnamearray = array_filter($catnamearray);
	$catnameliststr = implode(",",$catnamearray);
	
	$catnameliststr="'".$catnameliststr."'";
	
	
	// if catidlist is blank then return null
	if(count($catnamearray)==0)
	{
		return null;
	}
	
	$display_cond="";
//$display_bitwise        = array('display_cs'=>1, 'display_de'=>2,'display_tme'=>4,'display_iro'=>8,'display_web'=>16,'display_wap'=>32);	
	
	if($this->module=="cs")
	{
		$display_cond =" AND (display_flag & 1 =1) ";
	}
	elseif($this->module=="de")
	{
		$display_cond =" AND (display_flag & 2 =2) ";
	}
	elseif($this->module=="tme" || $this->module=="me" )
	{
		$display_cond =" AND (display_flag & 4 =4) ";
	}
	
	if($deactive)
	{ 
		$display_cond="";
	}
	else
	{
		$display_cond.= " AND isdeleted=0 AND mask_status=0 ";

	}
	
	$orderby= " ORDER BY callcnt DESC ";
	
	$sqlsel="SELECT catid,category_name,category_type from tbl_categorymaster_generalinfo where MATCH(category_name) AGAINST (".$catnameliststr.") ".$display_cond.$orderby; 
	//echo  $sqlsel;
	$catidrs = $this->conn_local->query_sql($sqlsel);	
	if($catidrs && mysql_num_rows($catidrs))
	{	
		//echo "<br>inside<br>"			;
		while($catiarr = mysql_fetch_assoc($catidrs))
		{
			$catidarray[$catiarr[catid]]=array(catid=>$catiarr[catid],category_name=>$catiarr[category_name],catname=>$catiarr[category_name],category_type=>$catiarr[category_type]);
		}
	}
	//echo "<pre>"; print_r($catidarray);
	return $catidarray; // either it will return all catid array or null

}

// this will give details of all catid even mask, non active
function getCatIdAll($catname)
{	
	//$catname
}

function catSponAutosuggest($catStr)
{
	if(strlen($catStr)){
		$sqlQryCatSpon = "select DISTINCT catGen.category_name as catname,catGen.catid from tbl_categorymaster_generalinfo as catGen join tbl_categorymaster_parentinfo as catPar on catGen.catid = catPar.catid where catGen.category_name LIKE('".trim($catStr)."%') AND (catGen.mask_status = 0 AND  catGen.display_flag&1=1)AND catGen.category_name NOT LIKE 'c2s%'  and catGen.category_name NOT LIKE 'c2c%' AND (catGen.paid_clients>0 OR catGen.nonpaid_clients>0) AND (catGen.biddable_type = 1 OR (catGen.biddable_type= 0 AND catPar.parent_flag=1)) AND  catGen.isdeleted=0 ORDER BY catGen.callcount DESC LIMIT 10 ";
		$sqlResCatSpon = $this->conn_local->query_sql($sqlQryCatSpon);
		if($sqlResCatSpon && mysql_num_rows($sqlResCatSpon)>0){
			$return_str = "<table border =\"0\" width=\"100%\">\n";
			if(mysql_num_rows($sqlResCatSpon))
			{
				echo "<script language=\"javascript\">box('1');</script>";
				while($sqlRowCatSpon = mysql_fetch_assoc($sqlResCatSpon)){
						$country = str_ireplace($catStr,"<b>".$catStr."</b>",($sqlRowCatSpon['catname']));
						$return_str.= "<tr id=\"word".$sqlRowCatSpon['catname']."\" onmouseover=\"highlight(1,'".$sqlRowCatSpon['catname']."');\" onmouseout=\"highlight(0,'".$sqlRowCatSpon['catname']."');\" onClick=\"display('".$sqlRowCatSpon['catname']."');\" >\n<td>".$country."</td>\n</tr>\n";
					
				}
			}
			$return_str.= "</table>";
		}
	}else{
		$return_str = "<script language=\"javascript\">box('0');</script>";
	}
	return $return_str;
}

function getCatSponFreeCat($word,$banner_id,$request,$word_num)
{
	$rowReturn = array();
	/*if (!defined('REMOTE_CITY_MODULE'))
	{
		$condition = " AND company_count>0 ";
	}
	$sql1='SELECT catid,catname,parent_flag,final_catname,cat_type,mask,parentid,parent_callcnt,callcnt,
				   GROUP_CONCAT(catlineage SEPARATOR "|P|") as P_lineage,
				   ((match(catname_stem) against("'.$word.'"))*0.25+callcnt/100*0.75) as score, 
				   ((match(catname_stem) against("'.$word.'" IN BOOLEAN MODE))) as score2,if(catname="'.$request['text_content'].'",1,0) as exact_match
				   FROM tbl_category_master 
				   WHERE 
					match(catname_stem) against("'.$word.'") AND
					catname NOT LIKE "c2s%"  and catname NOT LIKE "c2c%" AND
					(paid>0 OR nonpaid>0) AND 
					((display_flag=1 AND 
					mask=0) ) AND
					(cat_type="B" OR cat_type="BT" OR (cat_type="BT" AND parent_flag=1)) AND
					deleted = 0 '.$condition.'
					GROUP BY catid';*/
	$sql1= "SELECT DISTINCT catGen.category_name as catname,catGen.catid , catGen.biddable_type,
			((match(catGen.catname_search_processed) against('".$word."'))*0.25+catGen.callcount/100*0.75) AS score,
			((match(catGen.catname_search_processed) against('".$word."' IN BOOLEAN MODE))) AS score2,
			IF(catGen.category_name='".$request['text_content']."',1,0) AS exact_match 
			FROM tbl_categorymaster_generalinfo AS catGen 
			JOIN tbl_categorymaster_parentinfo AS catPar 
			ON catGen.catid = catPar.catid 
			WHERE match(catGen.catname_search_processed) against('".$word."') AND 
			catGen.category_name NOT LIKE 'c2s%'  and catGen.category_name NOT LIKE 'c2c%' AND 
			(catGen.paid_clients>0 OR catGen.nonpaid_clients>0) AND
			(catGen.biddable_type = 1 OR (catGen.biddable_type= 0 AND catPar.parent_flag=1))
			AND catGen.isdeleted=0
			group by catGen.catid ";
	$sql2= " HAVING score2=".$word_num;
	$sql3= " ORDER BY exact_match desc,score2 desc, score DESC LIMIT 52";
	$sqlQryFreeCatSpon = $sql1.$sql2." ".$sql3;
	$sqlResFreeCatSpon = $this->conn_local->query_sql($sqlQryFreeCatSpon);
	if($sqlResFreeCatSpon && mysql_num_rows($sqlResFreeCatSpon)>0)
	{
		while($sqlRowFreeCatSpon =  mysql_fetch_assoc($sqlResFreeCatSpon)){
			$rowReturn [$sqlRowFreeCatSpon['catid']]['cat_name'] = $sqlRowFreeCatSpon['catname'];
			$rowReturn [$sqlRowFreeCatSpon['catid']]['cat_type'] = $sqlRowFreeCatSpon['biddable_type'];
		}
	}
	return $rowReturn;
}

function depluralize($word){
    // Here is the list of rules. To add a scenario,
    // Add the plural ending as the key and the singular
    // ending as the value for that key. This could be
    // turned into a preg_replace and probably will be
    // eventually, but for now, this is what it is.
    //
    // Note: The first rule has a value of false since
    // we don't want to mess with words that end with
    // double 's'. We normally wouldn't have to create
    // rules for words we don't want to mess with, but
    // the last rule (s) would catch double (ss) words
    // if we didn't stop before it got to that rule. 
    $rules = array( 
        'ss' => false, 
        'os' => 'o', 
        'ies' => 'y', 
        'xes' => 'x', 
        'oes' => 'o', 
        'ies' => 'y', 
        'ves' => 'f', 
        's' => '');
    // Loop through all the rules and do the replacement. 
    foreach(array_keys($rules) as $key){
        // If the end of the word doesn't match the key,
        // it's not a candidate for replacement. Move on
        // to the next plural ending. 
        if(substr($word, (strlen($key) * -1)) != $key) 
            continue;
        // If the value of the key is false, stop looping
        // and return the original version of the word. 
        if($key === false) 
            return $word;
        // We've made it this far, so we can do the
        // replacement. 
        return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key]; 
    }
    return $word;
}

//FnSelect($whereArray,$columns,$sortColumns,$sortAscending, $limit,$jointable,$joincond) 
function FnSelect( $whereArray = null, $columns = null,$sortColumns = null, $sortAscending = true, $limit = null,$jointable= null,$joincond=null) 
{
	$sortAscending=trim($sortAscending);
	//echo "<br><br>sortAscending".$sortAscending;
	//$sortoption = ($sortAscending ? "ASC" : "DESC");	
	if( strtoupper($sortAscending)=="ASC")
	$sortoption="ASC";
	elseif( strtoupper($sortAscending)=="DESC")
	$sortoption="DESC";
	elseif($sortAscending===false)
	$sortoption="DESC";
	elseif($sortAscending===true || $sortAscending==null )
	$sortoption="ASC";
	
	
	
	$tableName= "tbl_categorymaster_generalinfo";
	
	if (! is_null($columns)) {
		$sql = self::BuildSQLColumns($columns);
	} else {
		$sql = "*";
	}
	$sql = "SELECT " . $sql . " FROM " . $tableName . "";
	if (is_array($whereArray)) {
		$sql .= self::BuildSQLWhereClause($whereArray);
	}
	if (! is_null($sortColumns)) {
		$sql .= " ORDER BY " .
				self::BuildSQLColumns($sortColumns, true, false) .
				" " . $sortoption;
	}
	if (! is_null($limit)) {
		$sql .= " LIMIT " . $limit;
	}
	//return $sql;
	//echo  "<br> INside library/CategoryMaster.php <br><br>--".$sql;
	$catidrs = $this->conn_local->query_sql($sql);
	
	return $catidrs;
}

private function BuildSQLWhereClause($whereArray) {
	$where = "";
	//echo "whereArray"$whereArray
	foreach ($whereArray as $key => $value) {
		if (strlen($where) == 0) {
			if (is_string($key)) {
				$where = " WHERE `" . $key . "` = " . $value;
			} else {
				$where = " WHERE " . $value;
			}
		} else {
			if (is_string($key)) {
				$where .= " AND `" . $key . "` = " . $value;
			} else {
				$where .= " AND " . $value;
			}
		}
	}
	return $where;
}

static private function BuildSQLColumns($columns, $addQuotes = true, $showAlias = true) {
	if ($addQuotes) {
		//$quote = "`";
		$quote = "";
	} else {
		$quote = "";
	}
	switch (gettype($columns)) {
		case "array":
			$sql = "";
			foreach ($columns as $key => $value) {
				// Build the columns
				if (strlen($sql) == 0) {
					$sql = $quote . $value . $quote;
				} else {
					$sql .= ", " . $quote . $value . $quote;
				}
				if ($showAlias && is_string($key) && (! empty($key))) {
					$sql .= ' AS "' . $key . '"';
				}
			}
			return $sql;
			break;
		case "string":
			return $quote . $columns . $quote;
			break;
		default:
			return false;
			break;
	}
}


	function sanitize($str)
	{
		$str = preg_replace('/[@&-.,_)(\s+]+/',' ',$str);
		$str = preg_replace('/\\\+/i',' ',$str);
		$str = preg_replace("/[^a-zA-Z0-9\s]+/",'',$str);
		$str = preg_replace('/\s\s+/',' ',$str);
		return trim($str);
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
	
	function catFilter($categoryname)
	{
	 $filter = '/\b(about|after|all|also|an|and|another|any|are|as|at|be|because|been|before|being|between|both|but|by|came|can|come|could|did|do|does|each|else|for|from|get|got|has|had|he|have|her|here|him|himself|his|how|if|in|into|is|it|its|like|many|me|might|more|most|much|must|my|never|now|of|on|only|or|other|our|out|over|said|same|see|should|since|so|some|still|such|take|than|that|their|them|then|there|these|they|this|those|through|to|too|under|up|use|upto|until|very|want|was|way|we|well|were|what|when|which|while|who|will|with|would|you|your|the)\b/i';
	
	 $categoryname = trim(preg_replace($filter,'',$categoryname));
	 return preg_replace('/[\s\s+]+/',' ',$categoryname);
	}
	
}


?>
