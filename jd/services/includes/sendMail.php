<?php
require_once 'class_send_sms_email.php';
class sendMailClass extends DB{

	function setServers($data_city,$dataservers)
	{	
		global $db;
		
		$data_city 		= ((in_array(strtolower($data_city), $dataservers)) ? strtolower($data_city) : 'remote');
		$this->dbConIro	= $db[$data_city]['iro']['master'];
		$this->dbConFin    		= $db[$data_city]['fin']['master'];
		
	}
	function getEmailids($data_city){
		$dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'coimbatore', 'chandigarh', 'jaipur');
		$checkremote=((in_array(strtolower($data_city), $dataservers)) ? strtolower($data_city) : 'remote');
		$emailids='';
		if($checkremote!='remote'){
			$sql="select group_concat(emailid) as group_email from d_jds.tbl_mail_ids_for_inventory_change where data_city='".$data_city."'";
			$res_email 	= parent::execQuery($sql, $this->dbConIro);
			$num_rows		= mysql_num_rows($res_email);
			
			if($res_email && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_email))
				{	
					$emailids = $row['group_email'];
				}
			}
		}
		else{	
			$zone='';
			$sql="SELECT de_remotezone FROM d_jds.city_master where ct_name='".$data_city."'";
			$res_zone 	= parent::execQuery($sql, $this->dbConIro);
			$num_rows		= mysql_num_rows($res_zone);
			
			if($res_zone && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_zone))
				{	
					$zone = $row['de_remotezone'];
				}
			}

			switch($zone)
			{
				case '1' 	 : 	
				case '11' 	 : 	
					$data_city ='Mumbai';break;
					
				case '2' 	 : 	
				case '22' 	 : 	
					$data_city ='Kolkata';break;

				
				case '3' 	 : 	
				case '33' 	 : 	
					$data_city ='Hyderabad';break;

				case '4' 	 : 	
				case '44' 	 : 	
					$data_city ='Delhi';break;
				
				case '5' 	 : 	
				case '55' 	 : 	
					$data_city ='Chennai';break;

				case '6' 	 : 	
				case '66' 	 : 	
					$data_city ='Bangalore';break;
					
				case '7' 	 : 	
				case '77' 	 : 	
					$data_city ='Ahmedabad';break;
					
				
				case '8' 	 : 	
				case '88' 	 : 	
					$data_city ='Pune';break;
				

				 
			}
			$sql="select group_concat(emailid) as group_email from d_jds.tbl_mail_ids_for_inventory_change where data_city='".$data_city."'";
			$res_email 	= parent::execQuery($sql, $this->dbConIro);
			$num_rows		= mysql_num_rows($res_email);
			
			if($res_email && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_email))
				{	
					$emailids = $row['group_email'];
				}
			}	
		}
		return $emailids;
	}

function sendmailToCS($paramarr,$againtparentid,$data_city){
	$html='';

	global $db;
	$emailsms_obj = new email_sms_send($db,$data_city);
	  $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	$this->setServers($data_city,$dataservers);
	$contentforcs='';
	$contentfortme='';
	$parentidarray=array();
	$rowarray=array();
	$header="";
	$header.='<table border="1" cellpadding="0" cellspacing="10" width="800px" style="border-collapse:collapse;font-size:14px">';
	$header.='<tr>';
	$header.="<th >Contract Id</th>";
	$header.="<th >Category</th>";
	$header.="<th >Pincode</th>";
	$header.="<th >Position</th>";
	$header.="<th >Removed Partial Inventory %</th>";
	$header.="<th >New Position assigned</th>";
	$header.='</tr>';	
	$namearray=array();
	$insertquery=array();
	foreach ($paramarr as $parentid =>$categoriesarr) {
			$sql="select version from tbl_companymaster_finance where parentid='".$parentid."' and campaignid=2";
			$res_version 	= parent::execQuery($sql, $this->dbConFin);
			$num_rows		= mysql_num_rows($res_version);
			$version=0;
			if($res_version && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_version))
				{	
					$version= $row['version'];
					
				}
			}

				$version_condition='';
			if($version!=0){
				$version_condition=" and version='".$version."'";
			}


			$sql="select tmecode,mecode from payment_otherdetails where parentid='".$parentid."' $version_condition";
			$res_tme 	= parent::execQuery($sql, $this->dbConFin);
			$num_rows		= mysql_num_rows($res_tme);
			$tmecode='';
			$mecode='';
			if($res_tme && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_tme))
				{	
					$tmecode = $row['tmecode'];
					$mecode = $row['mecode'];				
				}
			}
			$sql="select companyname from tbl_companymaster_generalinfo where parentid='".$parentid."'";
			$res_cn 	= parent::execQuery($sql, $this->dbConIro);
			$num_rows		= mysql_num_rows($res_cn);
			$companyname='';
			if($res_cn && $num_rows > 0)
			{
				while($row=mysql_fetch_assoc($res_cn))
				{	
					$companyname = $row['companyname'];
				}
			}
			$namearray[$parentid]=$companyname;
			$html='';
			foreach ($categoriesarr as $catid =>$categories_val) {
			$catname=$categories_val['cnm'];
				
				foreach ($categories_val['res'] as $bypincode) {
				$html.="<tr>";
				$html.="<td >".$parentid."</td>";
				$html.="<td >".$catname."</td>";
				$html.="<td >".$bypincode['pin']."</td>";
				$html.="<td >".$bypincode['pos']."</td>";
				$html.="<td >".($bypincode['inv']*100)."</td>";
				$html.="<td >Package</td>";
				$html.="</tr>";
				$insertquery[]= "('".($categories_val['cid'])."', '".$catname."', '".$bypincode['pin']."', '".$parentid."', '".$companyname."', '".$data_city."', '".$bypincode['pos']."', '".$bypincode['inv']."', 'JBOX-InvtMgmt-SendMail-PartialInventory-Release')";
				
			}
		}

		$rowarray[$parentid]['content']=$html;
		$rowarray[$parentid]['name']=$companyname;
		$rowarray[$parentid]['tme']=$tmecode;
		$rowarray[$parentid]['me']=$mecode;
	}
	/* tme and me mail part */
	$tmecontentmail='';
	$mecontentmail='';
	$cscontentmail='';

	$send_header = '';
	$send_header .= 'MIME-Version: 1.0' . "\r\n";
	$send_header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$send_header.="from:noreply@justdial.com";
	$Qry = "insert ignore into db_finance.tbl_partial_inventory_consolidated (Catid, Category_Name, Pincode,Parentid, Companyname, Data_city, Position_Flag, Inventory, updatedby) VALUES ".implode(",",$insertquery)." ";
		$res_ins 	= parent::execQuery($Qry, $this->dbConFin);
	foreach ($rowarray as $key => $maildet) {
		$tme_mail='';
		$me_mail='';
		$url="https://192.168.20.237/hrmodule/employee/fetch_employee_info/".$maildet['tme'];
		$resultString=$this->curlcall($url);
		foreach ($resultString as $object) {
		    $tme_mail=$object->email_id;
		    break;
		}
		$url="https://192.168.20.237/hrmodule/employee/fetch_employee_info/".$maildet['me'];
		$resultString=$this->curlcall($url);
		foreach ($resultString as $object) {
		    $me_mail=$object->email_id;
		    break;
		}
		$message="Partial Inventory is removed from following contract as 100% is sold to other client <br><br>".$header.$maildet['content']."</table>";
		/*$mail=mail($tme_mail.",".$me_mail, "Partial Inventory removed from  ".$namearray[$key]."-".$key." & allotted to ".$againtparentid, $message, $send_header); */
		$emailsms_obj->sendEmailAdv($tme_mail.",".$me_mail, 'noreply@justdial.com',"Partial Inventory removed from  ".$namearray[$key]."-".$key." & allotted to ".$againtparentid, $message, "Partial Inventory- JDBOX-TMEMAIL", $parentid,$email_id_cc,$email_id_bcc,$reply_to);	
		 $cscontentmail.=$maildet['content'];
	}
	

	
	$csmail='';
	$stdcode=trim($stdcode);
	$data_city=strtolower($data_city);
	
	$email_to=$this->getEmailids($data_city);
	$email_message="Partial Inventory is removed from following contracts as 100% is sold to other client $againtparentid <br><br>".$header.$cscontentmail."</table>";

	/*$mail=mail($email_to, "Partial Inventory removed & allotted to ".$againtparentid."- ".ucwords($data_city), $email_message, $send_header);  */
	$emailsms_obj->sendEmailAdv($email_to, 'noreply@justdial.com',"Partial Inventory removed & allotted to ".$againtparentid."- ".ucwords($data_city), $email_message, "Partial Inventory- JDBOX-CSMAIL", $parentid,$email_id_cc,$email_id_bcc,$reply_to);
	
	}
	function curlcall($url){
				$ch = curl_init();        
		        curl_setopt($ch, CURLOPT_URL, $url);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				$resultString = curl_exec($ch);
		        curl_close($ch); 
		        $resultString=json_decode($resultString);
		        return $resultString;
	}
}

?>
