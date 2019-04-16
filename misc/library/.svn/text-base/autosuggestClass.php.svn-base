<?

Class Autosuggest {

	function companySuggest($suggestStr='',$start,$end) {
		
		if(trim($suggestStr) == '')
			return '';
		$suggestStr = strtolower($suggestStr);
		$var_first_alpha = substr(trim($suggestStr),0,1);
		$var_autosuggest_tbl = "tbl_autosuggest_comp_".$var_first_alpha;
		$var_autosuggest_comp_qry="select compname_area,contractid from ".$var_autosuggest_tbl ." where compname_area like '".$suggestStr."%' and display_flag=1 order by compname,area limit $start,".($end+1).";";

		return $var_autosuggest_comp_qry;
	}

	function categorySuggest($suggestStr='',$start,$end) {

		if(trim($suggestStr) == '')
			return '';
		$suggestStr = strtolower($suggestStr);
		$var_first_alpha = substr(trim($suggestStr),0,1);
		$var_autosuggest_tbl = "tbl_autosuggest_cat_".$var_first_alpha;
		$var_autosuggest_cat_qry="select distinct categoryname,catid,cat_type from ".$var_autosuggest_tbl ." where categoryname like '".$suggestStr."%' order by categoryname limit $start,".($end+1).";";

		return $var_autosuggest_cat_qry;
	}
}
?>