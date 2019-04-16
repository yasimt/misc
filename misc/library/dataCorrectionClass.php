<?php
class dataCorrection 
{
   	/******************* SHOW CATEGORY LIST & REMOVED CATEGORY OF THIS CONTRACT **********************/
	function listCatNRmCat($parentid, $rmCatIdList){
		global $conn_local,$conn_iro;
		$selCatIdQry = "select catidlineage from tbl_companymaster_extradetails where parentid='".$parentid."' ";
		$parentid=substr($contractID,1);		
		$resCatIds	 = $conn_iro->query_sql($selCatIdQry);
		
		$catIdNm = Array();

		if($resCatIds) {

			$recdCatIds = mysql_fetch_row($resCatIds);	
		}
		unset($selCatIdQry, $resCatIds);

		if(trim($recdCatIds[0])) {
			
			$recdCatIds[0] = str_replace("/","",$recdCatIds[0]);
			$recdCatIds[0] = "'".str_replace(",","','",$recdCatIds[0])."'";
			
			$selCatNm = "select catid, category_name as catname from tbl_categorymaster_generalinfo where catid in (".$recdCatIds[0].") ";

			if(trim($rmCatIdList)) {
				$rrmCatIds = substr($rmCatIdList,0,strlen($rmCatIdList)-1);			
				$rrmCatIds  = explode(",",$rrmCatIds);
			}
			$selCatNm .= " order by catid;";
			//echo $selCatNm;
			//die;
			$resCatNm	 = $conn_local->query_sql($selCatNm);

			while($recdCatNm=mysql_fetch_assoc($resCatNm)) {
				$catIdNm[$recdCatNm[catid]] = $recdCatNm[catname];
			}
			unset($selCatNm, $resCatNm, $recdCatNm);
		}

		if(count($catIdNm) > 0) {
			$cont .= "<table border='0' cellpadding='0' cellspacing='0' width='100%' class='fontA14'>";
			$cnt = 0;
			foreach($catIdNm as $key=>$value) {
				$match_flag = false;
				for($jCtr=0; $jCtr < count($rrmCatIds); $jCtr++) {
					if($rrmCatIds[$jCtr] == $key) {
						if($rmCatNmList == "") {
							$rmCatNmList = $value;
						} else {
							$rmCatNmList .= ", ".$value;
						}
						$match_flag = true;
					}
				}

				if(!$match_flag) {
					if($cnt % 2 == 0) {
						 $cont .= "<tr><td></td><td>".$value."</td>";
						 $cnt = 1;
					} else {
						$cont .= "<td>".$value."</td></tr>";
						$cnt = 0;
					}	
				}
			}
			if($cnt % 2 != 0) {
				 $cont .= "<td></td></tr>";
			}	
			$cont .= "</table>";
			$arrlist[] = $cont;
			$arrlist[] = $rmCatNmList;

			$cont = "";
			$catIdNm = "";
			return $arrlist;
		}
	}
	/******************* SHOW CATEGORY LIST & REMOVED CATEGORY OF THIS CONTRACT **********************/
}?>
