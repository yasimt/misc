<?

$boolQ = str_replace(" "," +", $txtCompany);
$boolQ = "+".$boolQ; 

$sqlInQry = "SELECT contactid FROM tbl_company_master WHERE MATCH (companyName) AGAINST ('".$txtCompany."') and contract_type<>'prompt'";


$sql = "SELECT contactid,companyName,paid,parentid,landmark,MATCH (companyName) AGAINST ('".$txtCompany."')*1.5 as score1 ,((match(companyName) against('\"".$boolQ."\"' IN BOOLEAN MODE))) as score2
FROM tbl_company_master WHERE MATCH (companyName) AGAINST ('".$txtCompany."') and contract_type<>'prompt'"; 

$sql .= " order by score2 desc,score1 desc ";
@$rs_syn_main = $conn->execQry($sql);
$arySrch = explode(' ', $txtCompany);
while($rows = mysql_fetch_array($rs_syn_main)){
	$src = 1;
	$found = 0;
	$expld = explode(' ',$rows['companyName']);
	 for($x= 0; $x < count($arySrch); $x++){
		$p1 =  strripos($rows['companyName'],trim($arySrch[$x]));
		if($p1 === false){$src = $src;}
		else{
			$found = true;
		}
		if($found != false){
			if($arySrch[0] == $expld[0])
			{ 
				$src =  $src*6;
			} else {
				$src =  $src*2;
			}
			$found = false;
		}
	 }
	if(strtolower($rows['companyName']) == strtolower(trim($txtCompany))){$src =  $src*10;}	
	if(strtolower($rows['companyName']) == strtolower(trim($txtCompany))){$src =  $src*10;} else{$src = $src;}
	
	$sr = $src * ($rows['score2']+1);
	$cnt = $cnt + 1;
	$arrysr[$cnt][0] = $sr;	
	$arrysr[$cnt][1] = $rows['companyName'];
	$arrysr[$cnt][2] = $rows['parentid'];
	$arrysr[$cnt][3] = $rows['landmark'];
	$arrysr[$cnt][4] = $rows['contractid'];

	$k = 0;
	
	if(($rows['score2'] == 1)){
		$foundcmop = "T";
	}
}

if(($foundcmop != 'T')){
	print $nores =  "<FONT SIZE='2' COLOR='red'>No result for '<B>$searchstr</B>' in/around '<B>$searcharea</B>' so all mumbai results</FONT>";
}

print "<pre>";
//print_r($arrysr);
print "</pre>";

/*@rsort($arrysr);
for($i = 0; $i < $cnt; $i++){
	if($i == ($cnt-1)){
		$oput .=  @implode('|~|',$arrysr[$i]);}
	else{
		$oput .=  @implode('|~|',$arrysr[$i])."|$|";
	}
}
$oput = str_replace('"','`',$oput);
$recd[0] = $oput;*/
//unset($oput,$arrysr,$sql_main,$sql_sum);
?>