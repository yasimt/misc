<?
class batchUpdate extends dbconn
{

	function redirect($path)
	{
		ob_clean();
		header("Location:".$path);
	}

	function addData($bthTime)
	{		

		$qry	= "INSERT INTO batch_details(batch_time) VALUES('".$bthTime."')";	
		$flag = $this->execQry1($qry);
		return $flag;
	}

	function viewData()
	{
		$qry	= 		$qry	= "SELECT batch_id,batch_time FROM batch_details order by batch_time";				
		return $this->execQry1($qry);
	}
	function delData($batchId)
	{
		$qry	= "DELETE FROM batch_details where batch_id=$batchId ";		
		return $this->execQry1($qry);
	}

	function chkData($bTime)
	{
		$qry	= "SELECT count(*) FROM batch_details where batch_time='$bTime'";				
		return $this->execQry1($qry);
		
	}
	function updateData($bId,$bTime)
	{	
		$uqry = "UPDATE batch_details set  batch_time='$bTime' where batch_id =$bId";			
		return $this->execQry1($uqry);		
	}

}

?>