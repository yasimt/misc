<?
include_once("../library/config.php");
include_once(APP_PATH."library/path.php");
include_once("functions.php");

$conn_iro = new DB($dbarr['DB_IRO']);


$response_msg = "";
$id = trim($_REQUEST['id']);
if(intval($id)>0)
{
	$sqlHistoryData = "SELECT parentid,docid,companyname,ucode,uname,vertical_name,insertdate,parent_pid,business_details_old,business_details_new FROM tbl_vertical_bform_details WHERE id = '".$id."'";
	$resHistoryData = $conn_iro->query_sql($sqlHistoryData);
	if($resHistoryData && mysql_num_rows($resHistoryData)>0)
	{
		$row_history_data = mysql_fetch_assoc($resHistoryData);
		$parentid = $row_history_data['parentid'];
		$parent_pid = $row_history_data['parent_pid'];
		$parent_pid = $parent_pid ? $parent_pid : '----------';
		$updatedtime = $row_history_data['insertdate'];
		$ucode  = trim($row_history_data['ucode']);
		$uname  = trim($row_history_data['uname']);
		$vertical_name = trim($row_history_data['vertical_name']);
		$updatedby = $uname."(".$ucode.")";
		$business_details_old = trim($row_history_data['business_details_old']);
		$business_details_new = trim($row_history_data['business_details_new']);
		
		$compmaster_obj 	= new companyMasterClass($conn_iro,'',$parentid);
		$comp_details_arr 	= getCompanyDetails($parentid,$compmaster_obj);
		$companyname 		= $comp_details_arr['companyname'];	
		$record_flag = 0;
		if($business_details_old != $business_details_new)
		{
			$history_flag = 1;
			parse_str(str_replace(" = '","=",$business_details_old),$business_details_old_arr);
			parse_str(str_replace(" = '","=",$business_details_new),$business_details_new_arr);
		}
		if($history_flag)
		{
			$fields_to_ignore=	hideKey($vertical_name);
			$fields_abstration = verticalKeyDetails($conn_iro);
			$i = 0;
			$keys = array_merge(array_keys($business_details_old_arr),array_keys($business_details_new_arr));
			$keys = array_unique($keys);
			if(count($business_details_old_arr))
			{
				foreach($keys as $value)
				{
					$vertical_data_arr[$value] = $business_details_old_arr[$value];
				}	
			}
			else
			{
				foreach($keys as $value)
				{
					$vertical_data_arr[$value] = $business_details_new_arr[$value];
				}

			}
			//print"<pre>";print_r($vertical_data_arr);
			if(count($vertical_data_arr)>0)
			{
				$response_msg .="<a class='closeButton' onclick='close_changes_done_div();'></a>
								<br>
								<table class='tbl_view_changes_header_cls' border='0'>
									<tr>
										<th>Parentid</th>
										<td width='5%' align='center'>:</td>
										<td>".$parentid."</td>
									</tr>
									<tr><td colspan='3' height='5px;'></td></tr>
									<tr>
										<th>Company</th>
										<td width='5%' align='center'>:</td>
										<td>".$companyname."</td>
									</tr>
									<tr><td colspan='3' height='5px;'></td></tr>
									<tr>
										<th>Parent Pid</th>
										<td width='5%' align='center'>:</td>
										<td>".$parent_pid."</td>
									</tr>
									<tr><td colspan='3' height='5px;'></td></tr>
									<tr>
										<th>Updated By</th>
										<td width='5%' align='center'>:</td>
										<td>".$updatedby."</td>
									</tr>
									<tr><td colspan='3' height='5px;'></td></tr>
									<tr>
										<th>Update Date</th>
										<td width='5%' align='center'>:</td>
										<td>".$updatedtime."</td>
									</tr>
									<tr><td colspan='3' height='10px;'></td></tr>
								</table>";
							
							
				
				foreach($vertical_data_arr as $key=>$value)
				{
					if(!in_array($key,$fields_to_ignore))
					{
						if($i==0)
						{
							$record_flag = 1;
							$response_msg .= "<br>
											 <table align='center' style='border:0px solid #405366' width='90%'>
												<tr bgcolor='#003040' height='40px;'>
													<td colspan='4' align='center' style='font-size:16px;color:white'>Contract Vertical Details</td>
												</tr>
												<tr>
													<td bgcolor='#DAF0E6' align='center' height='30px' colspan='4'><span style='font-weight:bold;color:#003040'>::  ".$vertical_name."  ::</span></td>
												</tr>
												<tr bgcolor='#003040' height='40px;'>
													<td class='color-white' style='width:7%' align='center'> Sr.No </td>
													<td class='color-white' align='center' style='width:23%'> Info Changed </td>
													<td class='color-white' align='center' style='width:35%'> Old Value </td>
													<td class='color-white' align='center' style='width:35%'> New Value </td>
												 </tr>
											 </table>
											 <div class='scroll_top_cls' style='overflow:auto;width:100%;height:50%;'>
											 <table class='tbl_data_cls' align='center'>
											";
							$i++;
						}
						
						if(strtolower(trim($business_details_old_arr[$key])) != strtolower(trim($business_details_new_arr[$key])))
						{
							$column = $fields_abstration[$key];
							$column = ucwords(strtolower($column));
							
							$response_msg .="<tr style='color:#003040'>
												<td class='border_td' align='center' style='width:7%'>".$i++."."."</td>
												<td class='border_td' style='padding-left:5px;width:23%;'>".$column."</td>";
								  
							if($business_details_old_arr[$key]!=""){										
							$response_msg .=	"<td class='border_td' style='padding-left:5px;width:35%;'>".$business_details_old_arr[$key]."</td>";
							}else{
							$response_msg .=	"<td class='border_td' style='text-align:center;width:35%;'>-</td>";
							}
							
							if($business_details_new_arr[$key]!="") {
							$response_msg .=	"<td class='border_td' style='padding-left:5px;width:35%;'>".$business_details_new_arr[$key]."</td>";
							}else{
							$response_msg .=	"<td class='border_td' style='text-align:center;width:35%;'>-</td>";
							} 
							$response_msg .="</tr>";
						}
					}
					$response_msg .="</div>";
				}
			}
		$response_msg .="</table>";
		}	
	}
}
if($response_msg =='')
{
	$extra_dot = "<span style='color:#003545;font-weight:normal;'>.........</span>";
	$msg_content = $extra_dot." No Changes Made !!! ".$extra_dot;
	$response_msg = "<a class='closeButton' onclick='close_changes_done_div();'></a><div style='margin-top:5%;color:red;font-weight:bold;font-size:20px;'>".$msg_content."</div>";
}
if($record_flag !=1)
{
	$extra_dot = "<span style='color:#003545;font-weight:normal;'>.........</span>";
	$msg_content = $extra_dot." No Changes Made !!! ".$extra_dot;
	$response_msg = "<a class='closeButton' onclick='close_changes_done_div();'></a><div style='margin-top:5%;color:red;font-weight:bold;font-size:20px;'>".$msg_content."</div>";
}
echo $response_msg;
?>
