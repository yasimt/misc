<?php
	session_start();
	
	require_once("../library/config_central.php");
	$arr_server = explode('.',$_SERVER["SERVER_ADDR"]);
	
	$getContents	=	1;
	GLOBAL $IPAddr;

	
	$pocPath = '../poc_'.$_SESSION['loginCity'].'/';
	$poc = 'SoftphoneLite/html/Softphone.html';

	$PageNo	=	$_GET['Pageno'];
	
	if($getContents	==	1) {
		$DisPage	=	"../newTme";
	} else {
		$DisPage	=	"mktgPage.php";
	}

	$username  = $_SESSION['mktgEmpCode'];
	$pwd	   = $_SESSION['pw'];
	$stationID = $_SESSION['Stationid'];
	
	# For AutoDialer Active/De-Active Flag#
	require_once( APP_PATH."common/clicktocall.php" );
	
	if($_SESSION['ucode'] == '100007601'){
		print"<pre>";print_r($_SESSION);
		echo "<br>".$working_server;
		echo "<br>".$_apilogin;
		echo "<br>".$_dialer_toolbar;
	}
	
	
?>
<html>
<head>
	<TITLE>Just Dial Pvt Ltd - India's No.1 Local Search Engine</TITLE>
	<script language="javascript" src="<?=$pocPath?>pocfn.js"></script>
	<script src="../newTme/js/autobahn.min.jgz"></script>
	<script language="javascript" src="<? echo COMMON_JS_URL;?>jquery-1.7.min.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<!-- <script language="javascript" src="<? echo COMMON_JS_URL;?>tmetransfercallpopup.js"></script> -->
	<link href="<? echo COMMON_CSS_URL;?>tmetransfercallpopup.css" rel="stylesheet" type="text/css">
	<!-- For Auto Dialer - S -->
	<script language="JavaScript" type="text/javascript" src="../AutoDial/js/AutoDial.js"></script>
	<link href="../common/jquery-ui-1.8.18.custom.css" rel="stylesheet" type="text/css" />
	<LINK rel="stylesheet" type="text/css" href="../AutoDial/css/AutoDial.css"/>
	<!-- For Auto Dialer - E -->

	<script src="./js/socket.io_2.0.4.js"></script>
	
</head>

<style type="text/css">

	.iroAppoverlay{
		z-index:1002;
		opacity:.5;
		position:fixed;
		z-index:999;
		top:-100px;
		left:0;
		bottom:0;
		right:0;
		height:125%;
		width:100%;
		background:none repeat scroll 0 0 #000
	}
	.modal{
		position:absolute;
		z-index:1002!important
	}
	.modal-box header h3{
		font-size:18px;
		color:#424242;
		padding:10px
	}
	.custom-modal .modal-header,.custom-modal .modal-content,.custom-modal .modal-footer{
		padding:10px;
		border-bottom:1px solid #ccc;background:#fff
	}
	.custom-modal .modal-header{
		font-size:18px;
		font-weight:400;
		color:#424242
	}
	.custom-modal .modal-header .close{
		position:absolute;
		right:18px;
		top:0;
		top:-5px
	}
	.custom-modal .modal-header .close a{
		color:#666;
		font-size:30px;
		font-weight:300
	}
	.custom-modal .modal-button{
		background:#fafcfc;
		border:1px solid #dce2e7;
		color:#4082c4;
		float:left;
		font-size:14px;
		height:36px;
		line-height:36px;
		margin:0 0 0 15px;
	}
	.custom-modal .border-bottom{
		border-bottom:1px solid #ccc
	}
	.iroApp{
		font-weight:bold;
		padding:10px;
		margin-left:25%;
	}
	.iroApp_details{
		font-weight:bold;
		padding:10px;
		margin-left:25%;
	}
	
</style>

<div id='bcast' class="divstyle1" style='visibility:hidden; position: absolute; top: 220px;left:0px;right:800px' ></div>

<?php if($ISAutoDialOn){?>
<script LANGUAGE="JavaScript">

function onloadpagecti(UserName,Pw,Station){
	//authenticateUser(UserName,Pw,Station);	
	AutoDialStart();
	CTILoginRequest(UserName,Pw,Station);
}

</script>
<?} else {?>

<script LANGUAGE="JavaScript">
var server_url = '<?=HOST_URL?>/jdboxNode/';
console.log(server_url);
var autoDialer=new Array();
var ext;
var i;

for(i=0,ext=6000;i<1000,ext<7000;i++,ext++){
	autoDialer[i]=ext;
}
	  	
function onloadpagecti(UserName,Pw,Station){
	//alert('login');
	CTILoginRequest(UserName,Pw,Station);
}


/*function IroTransfercall(pId, flgSrc, flgAfterCall, flgPaid, nonpaidFlag,irocode)
{
	var loc = "../tmAlloc/mktgGetContData.php?parentid="+pId+"&flgSrc="+flgSrc+"&flgAfterCall="+flgAfterCall+"&irocode="+irocode+"&hotdata=1";
	if(flgSrc == 1 && flgPaid != '*'){ loc += "&flgPaid="+flgPaid; }
	if(parseInt(nonpaidFlag) == 1){
		loc += "&convert=1";
	}
	window.location = loc;
}
*/
function in_array(what,where){
	var a=false;
	for(var i=0;i<where.length;i++){
		if(what == where[i]){
			a=true;
			break;
		}
	}
	return a;
}

function saveExit(iropid,irocity,iropaid,redir_city,ep_city){
	var USERID = '<?=$_SESSION['empcode']?>';
	var USERNAME = '<?=$_SESSION['uname']?>';
	
	var data = {
		irodata : JSON.stringify({parentid: iropid, city: irocity, paidflag: iropaid}),
		noparentid : 1,
		data_city: ep_city,
		ucode: USERID,
		uname: USERNAME,
		module: 'tme'
	};

	$.post(server_url+redir_city+'/bform/iroappsavenexit',data,function(saveExitData){

		if(Object.keys(saveExitData).length > 0 && saveExitData['error']['code'] == 0){
			alert('Data Save Sucessfully');
			$('.iroAppoverlay').hide();
			$('.transferPop').hide();
		}
	});
}

function proceedWithCompany(iropid,irocity,irouniquefield,source,businessname,redir_city,ep_city){
	
	console.log('inside proceedWithCompany -- ');

	var USERID = '<?=$_SESSION['empcode']?>';
	var USERNAME = '<?=$_SESSION['uname']?>';
	var REMOTE_ADDR = '<?=$IPAddr?>';
	var loc	=	'';

	var area = $('#area_sel99').val();
	
	if(pinSelectData == ''){
		var pincode= $('.online_pincode').val();
	}
	else{
		var pincode= pinSelectData;
	}

	var calldis=0;
	if ($('.linedis_2').is(":checked"))
	{
		alert('sad');
		calldis = 1;
	}

	if((pincode == null ||  pincode == '') || pincode == 'Select Pincode')
	{
		alert('Please enter pincode');
		return false;
	}
	
	var dataArr = {};
	dataArr['noparentid']	=	1;
	dataArr['data_city']	=	ep_city;
	dataArr['ucode']		=	USERID;
	dataArr['uname']		=	USERNAME;
	dataArr['module']		=	'tme';
	dataArr['irodata']		=	JSON.stringify({parentid : iropid, city : irocity, Uniquefield : irouniquefield, area : area, pincode : pincode, source : source, calldis : calldis, companyname : businessname, remote_addr : REMOTE_ADDR});

	console.log(dataArr);

	var irourl = redir_city+'/bform/iroappproceed';

	var proceedData = mktgApiCall(dataArr,irourl);

	console.log(proceedData);

	if(Object.keys(proceedData).length>0 && (proceedData['error']['code'] == 0)){

		var mktg_url = redir_city+'/bform/get-temp-data';
		var data = { data_city: ep_city, ucode: USERID, uname: USERNAME, module: 'tme', team_type: team_type, parentid: iropid };

		var mktg_res = mktgApiCall(data,mktg_url);

		if(Object.keys(mktg_res).length > 0){

			if(mktg_res['error']['code'] == 0)
			{
				loc = "../business/bform.php?parentid="+iropid+"&flgSrc=2&actMode=0&flgAfterCall=1&dialFlgDialer=1&ecs_flag=&extn=&web_dialer=0&newmktg=1";
				top.frame2.location.href = loc; 

				$('.iroAppoverlay').hide();
				$('.transferPop').hide();
			}else {
				alert(mktg_res['error']['msg']);
			}
		}
		else {
			console.log('tempdata api failed'+mktg_res);
		}
	}
}
  

function close_fn(){
	$('.iroAppoverlay').hide();
	$('.transferPop').hide();
}

function iroTransfer_popup(params, redir_city, ep_city)
{
	console.log('inside iro_popup --');

	var parantid = params['data'][0]['Parentid'];
	var Uniquefield = params['data'][0]['Uniquefield'];
	var type = params['data'][0]['type'];
	var f12_id = params['data'][0]['f12_id'];
	var paidflag = params['data']['paid'];
	var source = params['data']['source'];


	var paidHtml = '';			var OKHtml = '';			var popup_elements = '';		
	var CallerName = 'NA';		var CallerMobile = 'NA';	var CallerMobile = 'NA';	
	var CallerPhone = 'NA';		var IroCode = 'NA';			var IroName = 'NA';		
	var Category = 'NA';		var City = 'NA';			var ExtNo = 'NA';			
	var area = 'NA';			var pincode = 'NA';	
	
	var title = 'ONLINE TRANSFER FOR APPOINTMENT';

	if(params['data'][0]['CallerName'] != '')
	{	
		CallerName = params['data'][0]['CallerName'].replace(' ','-');
		CallerName_new = params['data'][0]['CallerName'];
	}

	if(params['data'][0]['CallerMobile'] != '')
	{	CallerMobile = params['data'][0]['CallerMobile'];	}

	if(params['data'][0]['CallerPhone'] != '')
	{	CallerPhone = params['data'][0]['CallerPhone'];	}
	
	if(params['data'][0]['IroCode'] != '')
	{	IroCode = params['data'][0]['IroCode'];	}
	
	if(params['data'][0]['IroName'] != '')
	{	IroName = params['data'][0]['IroName'];	}
		
	if(params['data'][0]['Category'] != '')
	{	Category = params['data'][0]['Category'];	}
	
	if(params['data'][0]['City'] != '')
	{	City = params['data'][0]['City'];	}
		
	if(params['data'][0]['ExtNo'] != '')
	{	ExtNo = params['data'][0]['ExtNo'];	}
		
	if(params['data']['area'] != '' || params['data']['area'] != null)
	{	area = params['data']['area'];	}
		
	if(params['data']['pincode'] != '' || params['data']['pincode'] != null )
	{	pincode = params['data']['pincode'];	}		
		
	if(params['data']['paid'] == 1)
	{
		paidHtml += '<span style="font-weight:bold;text-align:center;font-size:25px" class="heading wrapper ng-scope">This is Paid Contract Its Cant be editable.</span>';
		OKHtml 	+= '<div class="modal-footer wrapper" style="padding-bottom: 50px;"><div class="f_right" style="margin-left:25%"><button class="modal-button" onclick="close_fn()">OK</button></div></div>';
	}
	 
	if(type == 1)
	{
		title = 'ONLINE TRANSFER FOR APPOINTMENT';
	}
	if(type == 1 && f12_id == '23232323')
	{
		title = 'ONLINE TRANSFER FOR JD Omni Product Enquiry';
	}
	if(type == 1 && f12_id == 'JDRRPromo')
	{
		title = 'ONLINE TRANSFER FOR JDRR Sales QUERY';
	}
	if(type == 3)
	{
		title = 'Expired contract - SMS campaign';
	}

	popup_elements	+=	'<div class="modal custom-modal add-business popTopBarBroad" id="modal2" style="margin-left: 20%; width: 70%;"><div class="modal-header wrapper"><span class="heading wrapper" style="font-weight:bold">'+title+'</span>'+paidHtml+'</div><div class="modal-content wrapper" style="padding-bottom:10px;color: #424242;height:auto">';

		popup_elements += '<table style="margin-left:30%">';
			popup_elements += '<tr><td class="iroApp" >CALLER NAME:</td><td class="iroApp_details" >'+CallerName_new+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >CALLER MOBILE:</td><td class="iroApp_details" >'+CallerMobile+'</td></tr>';	
			popup_elements += '<tr><td class="iroApp" >CALLER PHONE:</td> <td class="iroApp_details" >'+CallerPhone+'</td></tr>';		
			popup_elements += '<tr><td class="iroApp" >BUSINESS NAME:</td><td class="iroApp_details" >'+CallerName_new+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >AREA :</td><td class="iroApp_details" ><input type="text" id="area_sel99" name="area" class="add_all_1 loc_loop_1"   value="'+area+'" prentid="'+parantid+'" /></input></td></tr>';
			popup_elements	+= '<tr><td class="iroApp" >PINCODE:</td><td class="iroApp_details pincode abc" id="parentDiv" ><select class="online_pincode" name="pincode" id="sel_online_pincode"><option value='+pincode+' std="22">'+pincode+'</option></select></td></tr>';						
			popup_elements += '<tr><td class="iroApp" >IRO CODE:</td><td class="iroApp_details" >'+IroCode+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >IRO NAME:</td><td class="iroApp_details" >'+IroName+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >CATEGORY:</td><td class="iroApp_details" >'+Category+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >CITY:</td><td class="iroApp_details" >'+City+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >EXTN:</td><td class="iroApp_details" >'+ExtNo+'</td></tr>';
			popup_elements += '<tr><td class="iroApp" >Line Disconnected:</td><td class="iroApp_details" ><input type="checkbox" id="linedis_1" name="linedis" class="linedis_2" ></input></td></tr>';
		popup_elements += '</table>';

	popup_elements += '</div>';

	popup_elements += '<div class="modal-footer wrapper" style="padding-bottom: 50px;">';
	popup_elements += '<div class="f_right" style="margin-left:25%">';
		popup_elements += '<button class="modal-button" onclick= "saveExit(\''+parantid+'\', \''+City+'\', \''+paidflag+'\', \''+redir_city+'\', \''+ep_city+'\')">Save And Exit Flow</button>';
		popup_elements += '<button class="modal-button" onclick= "proceedWithCompany(\''+parantid+'\', \''+City+'\', \''+Uniquefield+'\', \''+source+'\', \''+CallerName+'\', \''+redir_city+'\', \''+ep_city+'\')">Proceed With this Company</button>';
	popup_elements += '</div>';
	popup_elements += '</div>'+OKHtml+'</div>';
				
	return 	popup_elements;				
}

</script>

<?}?>

<script language="JavaScript" type="text/javascript">
var pinSelectData = "";
var socket_url = '<?=SOCKET_API?>';
var USERID = '<?=$_SESSION['empcode']?>';
var USERNAME = '<?=$_SESSION['uname']?>';
var LOGINCITYVAL = '<?=$_SESSION['loginCity']?>';
var team_type = '<?php echo $_SESSION['allocid']; ?>';

var mainCity_Arr = ["mumbai","delhi","kolkata","bangalore","chennai","pune","hyderabad","ahmedabad"];
//Ankit Testing for Socket event pull starts...

function callEventPullSocket(){

	if(USERID !="") {

		const LOGINUCODE = USERID.toString();
		const dialer_socket = io(socket_url+'?ucode='+LOGINUCODE);

		const connstring = 'socketconnected_'+LOGINUCODE;

		dialer_socket.emit(connstring, {'ucode': LOGINUCODE , 'msg' : 'User Connected'});

		const recev = 'dialerdata_'+LOGINUCODE;
		const disconnect_str = 'disconnected_'+LOGINUCODE;

		dialer_socket.on(recev, (data) => {
			
			console.log(data);
			var ep_city		=  data["data_city"];

			if(ep_city == undefined || ep_city == null || ep_city.length <= 0){
				alert('Eventpull is called without city, Please contact admin');
				return false;
			}

			ep_city = ep_city.trim().toLowerCase();

			if($.inArray(ep_city, mainCity_Arr) != -1 ){
				var redir_city = ep_city;
			}
			else {
				var redir_city = 'remote';
			}
			
			var loc	=	'';
			var id			=  data.contractid;
			var reseller 	=  id.split('-');

			switch(reseller[1]) {

				case 'inbound':
					loc = "../business/bfrom_inbound.php?id="+reseller[0]+"&data_city="+ep_city+"&ucode="+USERID+"&uname="+USERNAME;
					top.frame2.location.href = loc;
					break;
				case 'newstrategy':
					loc = "../business/bform_showcategories.php?id="+reseller[0]+"&data_city="+ep_city+"&ucode="+USERID+"&uname="+USERNAME;
					top.frame2.location.href = loc;
					break;
				case 'ECS':

					var url = redir_city+'/bform/ecstransferinfo';
					var data = { module:'tme', ecs_flag:reseller[1], extn:reseller[0], ucode:USERID, uname:USERNAME, data_city:ep_city, login_city:LOGINCITYVAL, server_city:redir_city };

					var mktg_res = mktgApiCall(data,url);

					console.log(mktg_res);

					if(Object.keys(mktg_res).length > 0){

						if(mktg_res['error']['code'] == 0){
							var pid = mktg_res.data.parentid;
							if(pid!=undefined && pid.length > 0){
								var param = { data_city : ep_city, parentid : pid, requestFrom : "dialer" };

								var sessionApi_res = SetSessionAPI(param);

								if(Object.keys(sessionApi_res).length > 0 && sessionApi_res['error'] == 0){

									loc = "../business/bform.php?parentid="+pid+"&flgSrc=2&actMode=0&ecs_flag="+reseller[1]+"&extn="+reseller[0]+"&web_dialer=0&newmktg=1";
									top.frame2.location.href = loc; 

								}else {
									alert(sessionApi_res['error']['msg']+' session Api failed');
									return false;
								}
							}
							//~ else{
								//~ alert('parentid id is blank, please contact admin');
								//~ return false;
							//~ }
						}
						//~ else{
							//~ alert(mktg_res['error']['msg']+' Please contact admin');
							//~ return false;
						//~ }						
					}
					else {
						alert('ecstransferinfo api failed');
						return false;
					}

					break;
				case 'web_dialer' :

					var url = redir_city+'/bform/get-temp-data';
					var data = { data_city: ep_city, ucode: USERID, uname: USERNAME, module: 'tme', team_type: team_type, parentid: reseller[0] };

					var mktg_res = mktgApiCall(data,url);

					if(Object.keys(mktg_res).length > 0){

						if(mktg_res['error']['code'] == 0){
							var param = { data_city : ep_city, parentid : reseller[0], requestFrom : "dialer" };

							var sessionApi_res = SetSessionAPI(param);

							if(Object.keys(sessionApi_res).length > 0 && sessionApi_res['error'] == 0){

								loc = "../business/bform.php?parentid="+reseller[0]+"&flgSrc=2&actMode=0&ecs_flag=&extn=&web_dialer=1&newmktg=1";
								top.frame2.location.href = loc;

							}else {
								alert(sessionApi_res['error']['msg']+' Please contact admin');
								return false;
							}
						}else {
							alert(mktg_res['error']['msg']);
							return false;
						}
							
					}
					else {
						alert('get-temp-data api failed in web-dialer');
						return false;
					}

					break;
				case 'IROAPP':

					var url = redir_city+'/bform/iroapptransfer';
					var data = { module:'tme', iroApp_flag:reseller[1], extn:reseller[0], ucode:USERID, uname:USERNAME, data_city:ep_city, noparentid:1};
					var transerData = mktgApiCall(data,url);

					console.log(transerData);

					if(Object.keys(transerData).length > 0 ){

						if(transerData['error']['code'] == 0){

							var pid = transerData['data'][0]['Parentid'];
							var City = transerData['data'][0]['City'].trim().toLowerCase();

							if(pid!=undefined && pid.length>0){
								var param = { data_city : ep_city, parentid : pid, requestFrom : "dialer" };

								var sessionApi_res = SetSessionAPI(param);

								if(Object.keys(sessionApi_res).length > 0 && sessionApi_res['error'] == 0){

									var response = iroTransfer_popup(transerData, redir_city, ep_city);


									$('.iroAppoverlay').show();
									$('.transferPop').show();
									$('.transferPop').html(response);

									if($('#sel_online_pincode').val() == '')
									{
										var parentid = $('#area_sel99').attr('prentid');

										$.post(server_url+City+'/bform/pincodeinfo', {pincode:'',ucode:USERID,uname:USERNAME,data_city:City,parentid:pid,city:City,get_flg:1}, function(data_pincode) {

											var pincodesuggestions = [];
											console.log(data_pincode);
											var str_pincode = '';
											str_pincode +='<option value="Select Pincode" std="22">Select Pincode</option>'; 
											if(data_pincode.error.code == 0) {
												$.each(data_pincode.data,function(i,val) {
													
													str_pincode +='<option value='+i+' std="22">'+i+'</option>'; 
													
												});
												$('.online_pincode').html(str_pincode);
											}  
											$('.online_pincode').change(function() {
												pinSelectData = $(this).val();
											});
										});
									}
									
									setTimeout(function() {
										$('.loc_loop_1').autocomplete({
											source	:	function(request, response) 
											{
												var prev_area = $('#area_sel99').val();
												var parentid = $('#area_sel99').attr('prentid');

												var url = City+'/bform/areainfo';
												var data = { module:'tme', search:request.term, autosuggest:1, ucode:USERID, uname:USERNAME, data_city:City, parentid:pid, city:City};
												var areaAuto_res = mktgApiCall(data,url);
												var areasuggestions = [];

												if(Object.keys(areaAuto_res).length>0) {

													if(areaAuto_res['error']['code'] == 0) {
														data = 	areaAuto_res['data'];

														$.each(data,function(i,val)
														{
															areasuggestions.push({'label':val['areaname']});
														});
														
														console.log(areasuggestions);
														response(areasuggestions);
													}
												}

											},
											focus:function (event, ui) {
												if(ui.item.label != 'No Matches') {
													$('.loc_loop_1').val(ui.item.label);
												}
												return false;
											},
											select: function( event, ui ) {

												var parentid = $('#area_sel99').attr('prentid');

												var url = City+'/bform/pincodeinfo';
												var data = { module:'tme', area:ui.item.label,ucode:USERID,uname:USERNAME,data_city:City,parentid:pid,city:City,get_flg:1};
												var data_pincode = mktgApiCall(data,url);
												var pincodesuggestions = [];

												if(Object.keys(data_pincode).length>0) {
													console.log(data_pincode);
													var str_pincode = '';
													str_pincode +='<option value="Select Pincode" std="22">Select Pincode</option>'; 
													if(data_pincode.error.code == 0) {

														$.each(data_pincode.data,function(i,val) {
															str_pincode +='<option value='+i+' std="22">'+i+'</option>'; 
														});

														$('.online_pincode').html(str_pincode);

													} else if(data.error.code == 1) {
														pincodesuggestions.push({'label':'No Matches'});
													} 
													$('.online_pincode').change(function() {
														pinSelectData = $(this).val();
													});
													return false;
												}
										
											}
										}).data( "autocomplete" )._renderItem = function( ul, item ) {
											return $( "<li></li>" )
												.data( "item.autocomplete", item )
												.append( "<a>"+ui.item.valuel+"</a>" )
												.appendTo( ul );
										};
									},1000);

								}else {
									alert(sessionApi_res['error']['msg']+' Please contact admin');
									return false;
								}
							}
							else {
								alert('something went wrong, please contact admin');
								console.log('iroapptransfer api is not returning parentid');
								return false;
							}
						}
						
					}else {
						alert('iroapptransfer api failed in iroapp case');
						return false;
					}

					break;
				default:
					
					var url = redir_city+'/bform/get-temp-data';
					var data = { data_city: ep_city, ucode: USERID, uname: USERNAME, module: 'tme', team_type: team_type, parentid: id };

					var mktg_res = mktgApiCall(data,url);

					console.log(mktg_res);

					if(Object.keys(mktg_res).length > 0 ){

						if(mktg_res['error']['code'] == 0){
							var param = { data_city : ep_city, parentid : id, requestFrom : "dialer" };

							var sessionApi_res = SetSessionAPI(param);

							if(Object.keys(sessionApi_res).length > 0 && sessionApi_res['error'] == 0){
								loc = "../business/bform.php?parentid="+id+"&flgSrc=2&actMode=0&ecs_flag=&extn=&web_dialer=0&newmktg=1";
								top.frame2.location.href = loc; 
							}else {
								alert(sessionApi_res['error']['msg']+' Please contact admin');
								return false;
							}
						}
						else {
							alert(mktg_res['error']['msg']);
							return false;
						}
					}
					else {
						alert('something went wrong, please contact admin');
						console.log('there are some error with default case mktg api');
						return false;
					}
			}
			
		});				

		dialer_socket.on(disconnect_str, (reason) => {
			console.log("Disconnected : - ",reason);
		});

	}
}

function SetSessionAPI(param){
	var result = {};
	var api_url = '../sso/setSessionAPI.php';

	$.ajax({
		type     : "POST",  
		url      : api_url,
		async	 : false,
		data 	 : param,
		success	 : function(response) {
			var resobj = JSON.parse(response);
			result = resobj;
		},
		error 	 : function(err2){
			console.log("mktg condata api execution failed -- "+err2.message);
		} 
	});

	return result;
}

function mktgApiCall(param,redir_city){

	var result = {};
	var api_url = server_url+redir_city;

	$.ajax({
		type     : "POST",  
		url      : api_url,
		async	 : false,
		data 	 : param,
		success	 : function(resobj) {
			result = resobj;
		},
		error 	 : function(err2){
			console.log("mktg condata api execution failed -- ");
		} 
	});

	return result;
}

//Ankit Testing for Socket event pull ends...


function ShowCallerbanner()
{
	//alert('hhhhhhhhh');
}
function hideDisplay(empid,id)
{
	doWork123(empid,id)
}

function getHTTPObject() 
{ 
	if (typeof XMLHttpRequest != 'undefined') 
	{ return new XMLHttpRequest(); } 
	try { return new ActiveXObject("Msxml2.XMLHTTP"); } 
	catch (e) { try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
	catch (e) {} } return false; 
}

function doWork123(empid,id)
{
	var httpObject = getHTTPObject();
	if (httpObject != null) {
		httpObject.open("GET","../tmAlloc/test/testsession.php?employeeid="+empid+"&id="+id, true);
		httpObject.send(null);
		httpObject.onreadystatechange = setOutput123;
	}
}

function setOutput123(){
	if(httpObject.readyState == 4) {
		document.getElementById('bcast').style.visibility='hidden';
	}
}

var t;
function timestamp()
{
   	// startclock(); 
	t = setTimeout("ajax_nav()",3000);
}

function ajax_nav()
{ 
	httpObject = getHTTPObject();
	if (httpObject != null) {
    	httpObject.open("GET","../tmAlloc/test/helpTest.php", true);
    	httpObject.send(null);
		timestamp();
		httpObject.onreadystatechange = setOutputres;
	}
}

function setOutputres(){ 
	if(httpObject.readyState == 4){ 
		if(httpObject.responseText != ''){
			document.getElementById('bcast').innerHTML=httpObject.responseText;
			document.getElementById('bcast').style.visibility='visible';
		}else{
			document.getElementById('bcast').style.visibility='hidden';
		}
	}
}

function openToolBar(){
	if(document.getElementById('dialerToolbar').style.display	==	"table-row") {
		document.getElementById('apiLogin').style.display	=	"none";
		document.getElementById('dialerToolbar').style.display	=	"none";
	} else {
		document.getElementById('apiLogin').style.display	=	"table-row";
		document.getElementById('dialerToolbar').style.display	=	"table-row";
	}
}
</script>
<?
$autoDialer=array();         
for($ext=6000;$ext<7000;$ext++){
	$autoDialer[]=$ext;
}
if($_SESSION['Stationid'] and in_array($_SESSION['Stationid'],$autoDialer) )
{
	// if($parseConf['servicefinder']['allowneweventpull'] == 2) {
?>
	<script>callEventPullSocket();</script>
<?		
	// }
}
?>

<body style='margin:0;padding:0' onLoad="javascript:onloadpagecti('<?=$username?>','<?=md5($pwd)?>','<?=$stationID?>');">

	<div class="iroAppoverlay" style='display:none'></div>
	<div class='transferPop' ></div>
<!-- <BODY style='margin:0;padding:0' onLoad="javascript:onloadpagecti('<?=$username?>','<?=md5($pwd)?>','<?=$stationID?>');"> -->
	<form name='frmmainpg' method='post' style='margin:0;padding:0'>

		
		<table cellspacing=0 cellpadding=0 border=0 width='100%' align='center' height='100%' style='margin:0;padding:0'>
			<?
	if($WHICH_VENDOR == "TECHINFO")
	{
		if($POC_HTML === TRUE)
		{
?>			
			<TR>
				<TD>
					<IFRAME src="<?=$pocPath;?><?=$poc;?>" name='frame1' width="100%" height="0" scrolling="no" frameborder="0"></IFRAME>
				</TD>
			</TR>
			<tr>
				<td height='100%'>
					<IFRAME src=<?=$DisPage;?> name='frame2' width="100%" height="100%" scrolling="auto" frameborder="0"></IFRAME>
				</td>
			</tr>
<?
		}
		else
		{
?>
			<TR style="display:none" id="apiLogin">
				<TD>
					<IFRAME src="<?=$_apilogin;?>" name='iframecti' width="100%" height="0" scrolling="no" frameborder="0"></IFRAME>
				</TD>
			</TR>
			<TR style="display:none" id="dialerToolbar">
				<TD height='5%'>
					<IFRAME src="<?=$_dialer_toolbar;?>" name='iframecti' id= 'frameCTI' width="100%" height="100" scrolling="auto" frameborder="0"></IFRAME>
				</TD>
			</TR>
			<div style="height: 25px;left: 0;position: absolute;top: 16px;width: 40px;"  onclick="openToolBar()"><img src="images/ic_view_headline_black_24dp.png" /></div>
			<tr>
				<td height='95%'>
					<IFRAME src=<?=$DisPage;?> name='frame2' width="100%" height="100%" scrolling="auto" frameborder="0"></IFRAME>
				</td>
			</tr>
<?		}
	}	
	else
	{
?>
		<TR>
			<TD>
				<IFRAME src="<?=$pocPath;?><?=$poc;?>" name='frame1' width="100%" height="0" scrolling="no" frameborder="0"></IFRAME>
			</TD>
		</TR>
		<tr>
			<td height='100%'>
				<IFRAME src=<?=$DisPage;?> name='frame2' width="100%" height="100%" scrolling="auto" frameborder="0"></IFRAME>
			</td>
		</tr>
<?
	}
?>
		</table>
		<input type='hidden' name='clinum' value=''>
		<input type='hidden' name='workitemid' value=''>
		<input type='hidden' name='disConFlg' value=''>
		<input type="hidden" name="ctiFlag" value='0'>
		<input type="hidden" name="waitInQ" value=''>
		<input type="hidden" name="ctiLoginStatus" readonly>
		<input type="hidden" name="stationID" value='<?=$stationID?>'>
		<div id='overlay_backcalltransfer' style="display:none"></div>
		<div id='popup_confirmtransfer' style="display:none">
		<span id='feedbacktoptransfer' style="display:none">
		<span id='feedbacktoptransfer_paidcont' style="display:none">
		</div>
	<DIV ID="DialNumber" CLASS='dialNum' style='margin:0;padding:0'></DIV>
	</form>
</body>
</html>
