<?
class custLoyal extends dbconn
{
	function addData($clpid,$code,$mnths,$msg,$clppath,$clpflag)
	{		
		$msg  =  nl2br(htmlspecialchars($msg));

		
		if(!is_dir($clppath."loyalty"))
			mkdir($clppath."loyalty",0777);


		if(!is_dir($clppath."loyalty/".$mnths))
			mkdir($clppath."loyalty/".$mnths,0777);

		$dirpath = ($clppath."loyalty/".$mnths."/");
		//$out=`chmod -R 0777 $dirpath`;
		
		//New One File Making Starts
		if($clpflag=='M')
		{
			$fpath = ($clppath."loyalty/".$mnths."/".$mnths.".txt");
			$dbpath = ("loyalty/".$mnths."/".$mnths.".txt");
			file_put_contents($fpath,$msg);
		}
		if($clpflag=='S')
		{
			$fpath = ($clppath."loyalty/".$mnths."/S_".$mnths.".txt");
			$dbpath = ("loyalty/".$mnths."/S_".$mnths.".txt");
			file_put_contents($fpath,$msg);
		}
		//New One File Making Ends
		//check whether already exist or not
		$chkdata	= "SELECT count(*) FROM tbl_clp WHERE mnths='".$mnths."' and clpflag='".$clpflag."'";
		$rs       = $this->execQry($chkdata);
		$getCnt     = mysql_fetch_row($rs);		
		
		if($getCnt[0]==0)
			$qry = "INSERT INTO tbl_clp(mnths,clpfpath,updtby,updtdt,clpflag) VALUES('".$mnths."','".$dbpath."','".$code."',now(),'".$clpflag."')";
		else
			 $qry = "UPDATE tbl_clp set clpfpath='".$dbpath."', updtby='".$code."',clpflag='".$clpflag."', updtdt=now() where mnths=".$mnths."and clpid=".$clpid; 		
		//Old One Ends
		
		$flag=$this->execQry($qry);
		return $flag;
	}
	
	function viewCLPData($clpid='')
	{
		global $page, $pp, $limit, $fltr;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		if($clpid)
			$viewdata	= "SELECT mnths,clpfpath,updtby,updtdt,clpflag FROM tbl_clp where clpid=".$clpid;
		else
			$viewdata	= "SELECT clpid,mnths,clpfpath,updtby,updtdt,clpflag FROM tbl_clp ".$mdfyqry." ".$limit;
		$rs         = $this->execQry($viewdata);
		return $rs;
	}

	function showMnths($selected='')
	{
		for($i=1;$i<5;$i++)
		{
			if(($i*3)==$selected)
				$drpdown .= "<option value='".($i*3)."' selected>".($i*3)."</option>";
			else
				$drpdown .= "<option value='".($i*3)."'>".($i*3)."</option>";
		}

		return $drpdown;
	}

}

?>