<?php
include_once("../library/config.php");
include_once(APP_PATH."library/path.php");
include_once("functions.php");

$random_number= rand();

$conn_iro  = new DB($dbarr['DB_IRO']);

$parentid = trim($_REQUEST['parentid']);

if(!empty($parentid))
{
	$associated_verticals_arr = array();
	$associated_verticals_arr = fetch_associated_verticals($parentid,$conn_iro);
	$compmaster_obj 	= new companyMasterClass($conn_iro,'',$parentid);
	$comp_details_arr 	= getCompanyDetails($parentid,$compmaster_obj);
	$companyname 		= $comp_details_arr['companyname'];	
}
else
{
	die('Parentid Is Blank');
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Search Plus History</title>
    <script src="<?=APP_URL?>/common/js/jquery-1.7.min.js"></script>
	<script src="<?=APP_URL?>/reports/js/jquery-ui-1.8.18.custom.min.js"></script>
	<script src="js/validation.js?ver=12345"></script>
    <link href="css/styles.css" rel="stylesheet" type="text/css" />
   
</head>

<div id="accordian">
	<ul>
		<!-- we will keep this LI open by default -->
		<li class="active">
			<h3><span></span>Contact Details</h3>
			<ul>
				<!--li><a href="#">Parentid</a></li-->
				<!--li><a href="#">Company Name</a></li--->
				<table class='tbl_contact_details_cls' border='0'>
				  <tr>
					<th>Parentid</th>
					<td>:</td>
					<td><?=$parentid?></td>
				  </tr>
				  <tr>
					<th>Company </th>
					<td>:</td>
					<td><?=$companyname?></td>
				  </tr>
				</table>
			</ul>
		</li>
		<? if((!empty($associated_verticals_arr)) && (count($associated_verticals_arr)>0)){
			foreach($associated_verticals_arr as $vertical_name){
		?>
		<li>
			<h3><span></span><?=$vertical_name?></h3>
			<?
				$updateDetailsArr = fetchUpdateDetails($parentid,$vertical_name,$conn_iro);
				
				if(count($updateDetailsArr)>0){
					?>
					<ul>
						<table class='tbl_contact_details_cls' id ='vertical_data_td' border='0' style='width:90%;margin-right:5%;margin-left:5%'>
							 <tr>
								<th style="width:5%;text-align: left;">Sr No</th>
								<th>Updated By</th>
								<th>Updated Date</th>
								<th>View Report</th>
							 </tr>
							 <tr style='height:5px;'><td colspan='4'></td></tr>
					<?
					$srno =1;
					foreach($updateDetailsArr as $key => $value){
						$valueArr = explode("|",$value);
						$updatedby = $valueArr[0];
						$updatedtime = $valueArr[1];
						?>
							 <tr class='tr_show_bg'>
								<td><?=$srno?>.</td>
								<td><?=$updatedby?></td>
								<td><?=$updatedtime?></td>
								<td align='center'><a style='color:#C1F0C1;cursor:pointer;' onclick='showVerticalHistory(<?=$key?>);'>View Changes</a></td>
							 </tr>
							 <tr style='height:5px;'><td colspan='4'></td></tr>
						<?
						$srno++;
					}
					?>
						</table>
					</ul>	
					<?
				}
			?>
			
		</li>
		<?}}?>
		<!--li>
			<h3><span></span>Calendar</h3>
			<ul>
				<li><a href="#">Current Month</a></li>
				<li><a href="#">Current Week</a></li>
				<li><a href="#">Previous Month</a></li>
				<li><a href="#">Previous Week</a></li>
				<li><a href="#">Next Month</a></li>
				<li><a href="#">Next Week</a></li>
				<li><a href="#">Team Calendar</a></li>
				<li><a href="#">Private Calendar</a></li>
				<li><a href="#">Settings</a></li>
			</ul>
		</li>
		<li>
			<h3><span></span>Favourites</h3>
			<ul>
				<li><a href="#">Global favs</a></li>
				<li><a href="#">My favs</a></li>
				<li><a href="#">Team favs</a></li>
				<li><a href="#">Settings</a></li>
			</ul>
		</li-->
	</ul>
</div>
<div id='changes-done-cover'></div>
<div id='changes-done-div' align='center'>
</div>
