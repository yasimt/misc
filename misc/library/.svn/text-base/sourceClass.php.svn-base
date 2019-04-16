<?
class source
{
   	/*** 
		Function to insert Source and Contract info.
	***/
	function linkSource($contcode,$scode,$adType)
	{
		$crDate	= date("Y-m-d");
		$qry	= "INSERT INTO sourceinfo (cCode,sCode,crDate,adTypeCode) VALUES (".$contcode.",".$scode.",'".$crDate."',$adType)";
		
		$flag = $this->execQry($qry);
		return $flag;		 
	}

	/*** 
		Function to insert source detail.
	***/
	function insSource($sname,$shrtname,$gmktdet,$stype,$subsource,$sCity,$id='',$parentsource)
	{
		global $conn_decs;
		$crDate	= date("Y-m-d");
		//echo "<br>";echo "<br>";echo "id".$id;
		
	  
		if($id!='')
		{	//echo "<br>";echo $snames = (preg_match("/\askme\b/i", $sname);
			if($this->fn_exists($sname,$id)==0){
				if((stristr($sname, 'askme') === FALSE) && (stristr($sname, 'ask me') === FALSE) && (stristr($sname, 'tata press') === FALSE) && (stristr($sname, 'tatapress') === FALSE) && (stristr($sname, 'infomedia') === FALSE) &&  (stristr($sname, 'tpyp') === FALSE))
	 		 {		//echo "<br>";echo "<br>";echo "update".
					$qry= "update source set sName = '$sname' , shortName = '$shrtname', getMktDet = '$gmktdet' , sNewsppr = '$stype', sCrDate = '$crDate', subsource = '$subsource', sCity = '$sCity',parent = '".$parentsource."' WHERE `sCode`='$id';";
					//$flag = $this->execQry8($qry);
                    $flag = $conn_decs->query_sql($qry);
				
				}else{
					 $ermsg='This '.$sname.' source cannot be created2.';$flag = 0;
					header('Location: innerSource.php?clkflg=1');
				}
			}
			//else
			//{  header('Location: innerSource.php?clkflg=1');}
		}
		else
		{	//echo "<br>";echo "<br>";echo "insert".
			//if((strtolower($sname)!='askme') && (strtolower($sname)!='ask me') && (strtolower($sname)!='tata press') && (strtolower($sname)!='tatapress') && (strtolower($sname)!='infomedia') &&  (strtolower($sname)!='tpyp') )
			if((stristr($sname, 'askme') === FALSE) && (stristr($sname, 'ask me') === FALSE) && (stristr($sname, 'tata press') === FALSE) && (stristr($sname, 'tatapress') === FALSE) && (stristr($sname, 'infomedia') === FALSE) &&  (stristr($sname, 'tpyp') === FALSE))
	 		 {
			$qry	= "insert into source (sName, shortName, getMktDet, sNewsppr, sCrDate, subsource, sCity,parent) values ('$sname', '$shrtname', '$gmktdet', '$stype', '$crDate', '$subsource', '$sCity','".$parentsource."');";			
        	             //$flag = $this->execQry8($qry);
                         $flag = $conn_decs->query_sql($qry);
			}
			//else
			//{  $ermsg='This '.$sname.' source cannot be created33.';header('Location: innerSource.php?clkflg=1'); }
			
			 
		}//exit;
		//echo "flag".$flag;
		if($flag=='')
		  {	//echo "<br>";echo "sha";echo "<br>";echo "inserso";echo "innerSource.php?clkflg=1&source=$sname";
			header('Location: innerSource.php?clkflg=1&sname='.$sname.'');exit;
		  }
		  else
		 {	//echo "<br>";echo "shasss";echo "<br>";exit;
			return $flag;
		  }
	   }
	
	function getSource($sourceName)
	{
	    global $conn_decs;	
		$arr=array();
		$qry	= "SELECT sName,subSource FROM source where sName='".$sourceName."'";	
		
        //$result = $this->execQry($qry);
        $result =$conn_decs->query_sql($qry);
	
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
			{
				$arr = $row['sName'];
			}
		}
		return $arr;
	}

	function viewSource($sCode='')
	{
		global $page, $pp, $limit, $fltr,$conn_decs;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		if($sCode!='')
			$qry	= "SELECT * from source where sCode=".$sCode;	
		else
		    $qry	= "SELECT sCode,sName,subSource,parent from source ".$mdfyqry." ".$limit;
		//return $this->execQry($qry);
        return $conn_decs->query_sql($qry);
	}
	
	function viewSource1($sCode='',$sort)
	{
		global $page, $pp, $limit, $fltr,$conn_decs;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		if($sCode!='')
			$qry	= "SELECT * from source where sCode=".$sCode;	
		else
		    $qry	= "SELECT sCode,sName,subSource,parent from source ".$mdfyqry;

		if($sort == 'srcNameAsc'){
			$qry	.= " ORDER BY sName";
		}
		
		if($sort == 'srcNameDesc'){
			$qry	.= " ORDER BY sName DESC";
		}

		$qry .= $limit;
		//return $this->execQry($qry);
        return $conn_decs->query_sql($qry);
	}

	function fn_exists($sName='',$sCode=''){
        global $conn_decs;
		$selSrc = "SELECT COUNT(*) as recCnt FROM source WHERE sname = '".$sName."' AND scode <> '".$sCode."' ";
		//$resSrc = $this->execQry($selSrc);
        $resSrc = $conn_decs->query_sql($selSrc);
		$objSrc = mysql_fetch_object($resSrc);
	  return ($objSrc->recCnt);
	}

}?>
