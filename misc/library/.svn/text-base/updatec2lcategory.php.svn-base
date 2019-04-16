<?php
class updatec2lCategories {
	
	function updateMapCategory($parentid, $conn_iro, $conn_local, $finarr, $ucodecrl)
	{
		$resarr   = array();
		$tag_catid = array();
		$catimp = '';$bankCatEntry = 0;
		$getcatid = "select catidlineage, tag_catid, c2l_categories from tbl_companymaster_extradetails where parentid='".$parentid."' and ( (catidlineage!='' AND catidlineage is not null ) OR  (tag_catid!='' AND tag_catid is not null)) ";		
		$rescatid = $conn_iro->query_sql($getcatid);
		
		if($rescatid && mysql_num_rows($rescatid)> 0){
			$row = $conn_iro->fetchData($rescatid);
			$catidlineage = $row['catidlineage'];
			$tag_catid[] 	  = $row['tag_catid'];
			$c2l_categories 	= $row['c2l_categories'];
			$misc_cat = '';
			if($c2l_categories != ''){
				$c2l_categories = json_decode($c2l_categories,true);
				$misc_cat 		=	(isset($c2l_categories['misc']) && $c2l_categories['misc'] != '') ? $c2l_categories['misc'] : '';
			}
					
			$catidarr = str_replace("/", "", $catidlineage);
			
			$catidarr = array_merge(array_filter(explode("," ,$catidarr)));
			if(count($catidarr)>0 || count($tag_catid) > 0){
				$catidarr = array_filter(array_unique(array_merge($catidarr, $tag_catid)));
				$catimp = implode("','",$catidarr);
				$catidsTopass = implode(",",$catidarr);
			}
			$bankCatEntry = 0;
			// 10077195 nat_catid of carloans,10359572 personal loans, 10302729 loan against gold,  10250496 home loans.
			
			$c2lcatid = "select catid, category_name from d_jds.tbl_categorymaster_generalinfo where catid in ('".$catimp."') and not (promt_ratings_flag & 1=1 OR promt_ratings_flag & 2=2  OR promt_ratings_flag & 32=32) AND (category_verticals & 8 != 8) AND  category_conversion_flag&1=1 ORDER BY callcount DESC LIMIT 5";
			$res 	  = $conn_local->query_sql($c2lcatid);
			
			if($res && mysql_num_rows($res)>0){
				
				while($rowCat = $conn_local->fetchData($res)){
					if(strtolower($rowCat['category_name'])=='banks'){
						$bankCatEntry =1;
					}
					array_push($resarr, $rowCat['catid']);
				}
			}
			
			if($bankCatEntry==1){
				//$getDefaultCats = "SELECT GROUP_CONCAT(QUOTE(catid)) AS catlist FROM d_jds.tbl_categorymaster_generalinfo WHERE national_catid IN ('10077195', 		'10359572','10302729','10250496')";
				$getDefaultCats  = "SELECT GROUP_CONCAT(QUOTE(catid)) AS catlist FROM d_jds.tbl_categorymaster_generalinfo WHERE category_name in ('Car Loans','Personal Loans','Loan Against Gold','Home Loans') and category_conversion_flag&8=8  and mask_status=0";
				$resDefaultCats = $conn_local->query_sql($getDefaultCats);
				if($resDefaultCats && $conn_local->numRows($resDefaultCats)){
					$rowDefaultCats = $conn_local->fetchData($resDefaultCats);
					
					$bankCats = $rowDefaultCats['catlist'];		
				}
				
			}
			
			$bankCats = str_replace("'",'',$bankCats);
			if($bankCats){
				$finalRes['c2l'] = $bankCats;
			}
				
			if(count($resarr)>0 && $finalRes['c2l']!=''){
				$c2lcatid   = implode(',', $resarr);
				$finalRes['c2l']   =  $finalRes['c2l'].','.$c2lcatid;
			
			}
			else{
				$c2lcatid   	   = implode(',', $resarr);
				$finalRes['c2l']   =  $c2lcatid;
			}
			
			
			$c2fcatid 	= $this->getC2Fcatids($catidsTopass, $conn_local);
			//echo "<pre>c2fcatid:-";print_r($c2fcatid);
			$c2fcatids  = $c2fcatid['c2fcatids'];
			if($c2fcatid['count'] >0){
				$finalRes['c2f']   =  $c2fcatids;
			}
			
			$c2hcatid   = $this->getC2Hcatids($catidsTopass, $conn_local);
			//echo "<pre>c2hcatid:-";print_r($c2hcatid);
			$c2hcatids  = $c2hcatid['c2hcatids'];	
			
			if($c2hcatid['count'] >0){
				$finalRes['c2h']   =  $c2hcatids;
				//$finalRes['c2l']   =  '';
				
			}

			if($misc_cat){
				$finalRes['misc']   =  $misc_cat;
			}
			
			$finalRes 		   = json_encode($finalRes);
			
			$sum = '';$paid='';
			foreach($finarr as $key=>$value){
				 $sum += $value['balance'];
			}
			if($sum > 0){
				$paid =1;
				//$updateC2l = "update tbl_companymaster_extradetails set c2l_categories ='' where parentid='".$parentid."'";
				
			}else{
				$paid=0;
				
			}


$updateC2l = "update tbl_companymaster_extradetails set c2l_categories ='".$finalRes."' where parentid='".$parentid."'";
			$resC2L = $conn_iro->query_sql($updateC2l);			
			$insertLog = "insert into db_iro.tbl_c2lcategory_log(parentid, c2l_categories, update_time,updated_by, paid) values ('".$parentid."' , '".$finalRes."', '".date('Y-m-d H:i:s')."', '".$ucodecrl."', '".$paid."')";
			$resLog = $conn_iro->query_sql($insertLog);

			
		}else{
			$c2l_categories = "";
			$updateC2l = "update tbl_companymaster_extradetails set c2l_categories ='".$c2l_categories."' where parentid='".$parentid."'";
			$resC2L = $conn_iro->query_sql($updateC2l);			
			$insertLog = "insert into db_iro.tbl_c2lcategory_log(parentid, c2l_categories, update_time,updated_by, paid) values ('".$parentid."' , '".$c2l_categories."', '".date('Y-m-d H:i:s')."', '".$ucodecrl."', '')";
			$resLog = $conn_iro->query_sql($insertLog);
		}
		
	}
	
	function getC2Fcatids($catids, $conn_local)
	{
		$result    = array();
		$restaurantsArr   = array(); $fiveStarArr = array();
		$fivestarcatid = 0;
		$catids_str = str_replace(",","','",$catids);
		
		//1-c2l, 2-c2f, 4-c2h, 8-banks cats
		//$getQry = "SELECT catid, category_name, national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE national_catid in ('10890984','11274373')";
		
			$getQry = "SELECT catid, category_name, national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid in ('".$catids_str."') and category_conversion_flag&2=2   and mask_status=0";
			$resQry = $conn_local->query_sql($getQry);
			if($resQry && $conn_local->numRows($resQry) > 0)
			{
				
				while($rowQry = $conn_local->fetchData($resQry)) {
					
					array_push($restaurantsArr,$rowQry['catid']);
					if(trim($rowQry['national_catid']) == 10890984){
						$fivestarcatid = trim($rowQry['catid']);
					}
				}
			}

			$catEx = explode(',',$catids);
			$catEx = array_unique($catEx);
			$restaurantCatPresent = 0;
			if(count($catEx) >0){
				foreach($catEx as $key=>$value){
					if(trim($value)==trim($fivestarcatid)){
						$restaurantCatPresent=1;
						break;
					}
					
				}
			}
		
			
			if($restaurantCatPresent==1){	
				$result['count'] 	 = count($restaurantsArr);	
				$res1 = implode(',',$restaurantsArr);
				$result['c2fcatids'] = $res1;
				
			}
		

		return $result;
	}

	function getC2Hcatids($catids, $conn_local)
	{
		
		//10156727,10253670
		$catidArr = array();
		$exisitngCat = array(); $existFlag = 0;	$result = array();	
		
		$catids_str = str_replace(",","','",$catids);
									//'Hospitals','Departmental Stores' //1-c2l, 2-c2f, 4-c2h
		//$qry = "select catid, category_name from tbl_categorymaster_generalinfo where national_catid in ('10156727', '10253670')";
		$qry = "select catid, category_name from tbl_categorymaster_generalinfo where catid in ('".$catids_str."') and category_conversion_flag&4=4 and mask_status=0";
		$res = $conn_local->query_sql($qry);
		if($res && $conn_local->numRows($res)>0)
		{
			while($row = $conn_local->fetchData($res)) {
				array_push($catidArr,$row['catid']);
			}
		}
		
		$catEx = explode(',',$catids);
		$catEx = array_unique($catEx);
		foreach($catEx as $key=>$value){
			if(in_array($value,$catidArr)){
				$existFlag = 1;
				break;	
			}
			
		}
		
		if($existFlag==1){
			$resArr = array();
			$catids = str_replace(",", "','", $catids);
			$highestcallcnt = "SELECT catid, category_name, callcount, national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN ('".$catids."') GROUP BY catid ORDER BY callcount DESC LIMIT 5 ";
			$reshighestcallcnt = $conn_local->query_sql($highestcallcnt);
			if($reshighestcallcnt && $conn_local->numRows($reshighestcallcnt) > 0){
				while($rowhighestcallcnt = $conn_local->fetchData($reshighestcallcnt)){
					array_push($resArr,$rowhighestcallcnt['catid']);
				}
			}
			
		}
		
		if(count($resArr) > 0){
			$result['count'] 	 = count($resArr);
			$exisitngCat 		 = implode(',',$resArr);			
			$result['c2hcatids'] = $exisitngCat;
			
		}
		
		return $result;

	}


}

?>
