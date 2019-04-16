<?
class bidding_Update extends dbconn
{
	function redirect($path)
	{
		ob_clean();
		header("Location:".$path);
	}
	function addData($fname,$lname,$company,$email,$office_ph,$mobile,$intrested,$Time_frame,$discribes,$Segment,$contacted,$feedback,$uname,$password,$office_ph2,$date)
	{
		
		$password = md5($password);
		
		
		$chk_qry ="select count(*) as num from bid_user where uname= '$uname'";
		$chk_row = $this->execQry($chk_qry);
		$chk_array = mysql_fetch_array($chk_row);	
		if($chk_array[0] == 0)
		{
			$qry	= "INSERT INTO bid_user(fname,lname,company,email,office_ph,mobile,intrested,Time_frame,describes,Segment,contacted,feedback,uname,password,office_ph2,date) 	VALUES('$fname','$lname','$company','$email','$office_ph','$mobile','$intrested','$Time_frame','$discribes','$Segment','$contacted','$feedback','$uname','$password','$office_ph2','$date');";
		$flag = $this->execQry($qry);}
		return $flag;
	}
	function viewData(){
		$qry	= "SELECT  imp_id,imp_empid,imp_empname,imp_curdt,imp_showdt,imp_filenm FROM tbl_impUpdate order by imp_showdt desc";
		return $this->execQry($qry);
	}
	function get_bid_zone($cat,$city){
		if($city == '0'){
		$cat = stripslashes($cat);
		$qry = "select code,name from category_old  where code in (".$cat.")";
		$flag = $this->execQry($qry);}
		else{
			$qry = "select code,name from zone  where city = 'M'";
			$flag = $this->execQry($qry);}
			return $flag;
	}
	
	
	/* get bidding details */
	function get_bid_det($cat){
		$row_details = explode("M",$cat);
		$qry = "select max(bid_click),max(bid_lead),max(bid_call) from tbl_bid where bid_category =  $row_details[0] and bid_zone =  $row_details[1]";
		$flag = $this->execQry($qry);
		return $flag;
	}
   
	/* GET Bidder with perticular category and zone */


	function get_bidder($zone,$code){
	$qry = "select bid_bidder,bid_click,bid_lead,bid_call from tbl_bid where bid_category =  $code and bid_zone =  $zone";
	$flag = $this->execQry($qry);
	return $flag;
	}
	
	
	
	function get_auto($cat){
	$qry ="SELECT category_name as catname FROM tbl_categorymaster_generalinfo WHERE category_name like '$cat%' limit 15";
	$flag = $this->execQry($qry);			
	return $flag;
	}
}
?>