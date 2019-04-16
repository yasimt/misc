<?
class contracttp extends dbconn
{
	  function getId($gtPrefix)
	{
		
		$qry	= "SELECT typeid FROM contracttype where typename='".$gtPrefix."' ";	
		return $this->execQry($qry);
	}
	
	 function getPrefix($prefixval)
	{
		
		$qry	= "SELECT typename FROM contracttype where typename='".$prefixval."' ";	
		return $this->execQry($qry);
	}
	
	
	function addContType($prefixval,$descval,$priorityval,$leadval,$tenureval,$amtval)
	{
			$qry	= "insert into contracttype (typename, contractdesc, prioritynum, leadbased, tenurebased, amount) values ('".strtoupper($prefixval)."','".$descval."','".$priorityval."','".$leadval."','".$tenureval."','".$amtval."')";	
		   return $this->execQry($qry);	
	}

	function updtContType($prefixval,$descval,$priorityval,$leadval,$tenureval,$amtval,$idval)
	{
			 $qry = " UPDATE contracttype set typename='".strtoupper($prefixval)."',contractdesc='".$descval."',prioritynum='".$priorityval."',leadbased='".$leadval."',tenurebased='".$tenureval."',amount='".$amtval."' where typeid='".$idval."' "; 	
			
		   return $this->execQry($qry);	
	}

	
	function viewEntries()
	{
		global $page, $pp, $limit, $fltr;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		$qry	= "SELECT typename, ifnull(contractdesc,'-') as contractdesc, prioritynum, leadbased, tenurebased, amount, oldtype FROM contracttype ".$mdfyqry." ORDER BY typename ".$limit;	
		return $this->execQry($qry);
	}

	function getUpdtEntry($idval)
	{
		$qry = "SELECT typename, ifnull(contractdesc,'-') as contractdesc, prioritynum, leadbased, tenurebased, amount FROM contracttype where typeid='".$idval."'";	
		return $this->execQry($qry);
	}
	
}	
?>