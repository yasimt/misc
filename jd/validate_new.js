var rgx_numeric 		    = /^[0-9]+$/,
	rgx_alpha 		        = /^[a-zA-Z]+$/i,
	rgx_alpha_numeric	    = /^[a-z0-9]+$/i,
	rgx_spl_char	 	    = /^[a-z0-9 .]+$/i,
	rgx_alpha_space	        = /^[a-zA-Z ]+$/i,
	rgx_url 			    = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,15}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/,
	//rgx_comp_invalid_words	= /www\.|http|https|mr\s|mrs\s|ms\s|dr\s|miss\s|mr\.\s|mrs\.\s|ms\.\s|dr\.\s/i;
	//rgx_comp_invalid_words	= /www\.|www|https|http/i;
	rgx_comp_invalid_words	= /www\.|https|http/i;
	rgx_comp_spl_char		= /^[a-z0-9 .!&()'\\\-//]+$/i;
	rgx_email			    = /^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,15}$/i;

var patt 		= /jdsoftware.com/g;
if(patt.test(window.location.host) == true)
{
	var str1 = window.location.host;
	var str_split = document.referrer.split("/");
	var http_host = str1 + "/" + str_split[3];
	var http_host = "shitalpatil.jdsoftware.com";
}
else
{
	var http_host = window.location.host;
}

var jd_defaults = {

	compname : {
		MAXLENGTH 	 : 120,
		MINLENGTH	 : 2,
		MAXNUM		 : 6,
		REPEATCHAR	 : true,
		SPLCHAR		 : true,
		REQUIRED	 : true,
		SPLCHARREGEX : rgx_comp_spl_char,
		INVALIDWORDS : rgx_comp_invalid_words
	},

	state : {
		REQUIRED	 : true
	},

	city : {
		REQUIRED	 : true
	},

	area : {
		REQUIRED	 : true,
		MAXNUM		 : 6,
		MINLENGTH	 : 3,
		MAXNUM		 : 50,
		REPEATCHAR	 : true,
	},

	stdcode : {
		REQUIRED	 : true,
		MAXLENGTH 	 : 4,
		MINLENGTH 	 : 2,
		NUMERICREGEX : rgx_numeric
	},

	mobile : {
		MAXLENGTH 	 : 10,
		MINLENGTH	 : 10,
		MAXNUM		 : false,
		REPEATCHAR	 : false,
		REQUIRED	 : true,
		SPLCHAR		 : false,
		NUMERICREGEX : rgx_numeric
	},

	tele : {
		MAXLENGTH 	 : 10,
		MINLENGTH	 : 6,
		MAXNUM		 : false,
		REPEATCHAR	 : false,
		REQUIRED	 : true,
		SPLCHAR		 : false,
		NUMERICREGEX : rgx_numeric
	},

	fax : {
		REQUIRED	 : false,
		MAXLENGTH 	 : 8,
		MINLENGTH 	 : 6,
		VALIDLENGTH	 : 10,
		NUMERICREGEX : rgx_numeric
	},

	tollfree : {
		MAXLENGTH : 13,
		MINLENGTH :	8,
		REQUIRED  :	true,
		NUMERICREGEX: 	rgx_numeric,
	},

	email : {
		PATTERN		:	rgx_email,
		REQUIRED	: 	false
	},

	pincode : {
		MAXLENGTH 	: 	6,
		MINLENGTH	:	6,
		NUMERICREGEX: 	rgx_numeric,
		REQUIRED	: 	true
	},

	website : {
		REQUIRED	: 	false,
		URLREGEX	:	rgx_url
	},

	contactperson : {
		REQUIRED	:	false,
		REPEATCHAR	:	true,
		REPEATTEXT	:	true,
		ALPHAREGEX  :	rgx_alpha_space
	},

	smscode : {
		REQUIRED	:	false,
		MINLENGTH	:	5,
		NUMERICREGEX : rgx_numeric
	},

	yearestablish : {
		REQUIRED	 :	false,
		NUMERICREGEX : rgx_numeric,
		VALIDLENGTH  : 4
	},
	otherstd : {
		REQUIRED	 : false,
		MAXLENGTH 	 : 4,
		MINLENGTH 	 : 2,
		NUMERICREGEX : rgx_numeric
	},
	othertele : {
		MAXLENGTH 	 : 11,
		MINLENGTH	 : 6,
		MAXNUM		 : false,
		REPEATCHAR	 : false,
		REQUIRED	 : false,
		SPLCHAR		 : false,
		NUMERICREGEX : rgx_numeric
	},
	socialurl : {
		REQUIRED	: false
	},
	designation : {
		REQUIRED	:	false,
		REPEATCHAR	:	true,
		REPEATTEXT	:	true,
		ALPHAREGEX  :	rgx_alpha_space
	}
};

var messages = {
	msg_comp_required 		   : 'Please enter companyname',
	msg_comp_invalid 		   : 'Companyname cannot contain www., http or https',
	msg_comp_salutation		   : 'Companyname cannot contain salutations',
	msg_comp_maxLength 		   : 'Company Name should not exceed more than ' + jd_defaults.compname.MAXLENGTH + ' characters',
	msg_comp_maxNum			   : 'Companyname can not contain more than ' + jd_defaults.compname.MAXNUM + ' numeric characters',
	msg_comp_minLength 		   : 'Companyname should contain atleast ' + jd_defaults.compname.MINLENGTH + ' characters',
	msg_comp_splChar 		   : 'Companyname can not contain special characters',
	msg_comp_repChar		   : 'Companyname contains more than 4 repeated alphabets',
	msg_comp_repNum			   : 'Companyname contains more than 6 repeated numbers',
	msg_state_required 		   : 'Please enter state',
	msg_city_required 		   : 'Please enter city',
	msg_area_required 		   : 'Please enter area',
	msg_area_maxNum			   : 'Area can not contain more than ' + jd_defaults.area.MAXNUM + ' numeric characters',
	msg_area_length			   : 'Area length can be between 3 to 50 characters',
	msg_area_invalid		   : 'Invalid area',
	msg_area_repChar		   : 'Area can not contain more than 4 repeated alphabets',
	msg_pincode_required	   : 'Please enter pincode',
	msg_pincode_length		   : 'Pincode should contain 6 digits',	
	msg_pincode_invalid		   : 'Invalid pincode',
	msg_std_required 		   : 'Please enter std code',
	msg_std_isnumeric		   : 'Stdcode can contain numbers only',
	msg_std_length			   : 'Stdcode length can be between 2 to 4',
	msg_isnumeric 			   : 'Contact number can contain numbers only',
	msg_tele_required 		   : 'Please enter landline number',
	msg_tele_minLength		   : 'Landline number should contain atleast ' + jd_defaults.tele.MINLENGTH + ' digits',
	msg_tele_maxLength 		   : 'Landline number can not exceed ' + jd_defaults.tele.MAXLENGTH + ' digits',
	msg_tele_duplicate         : 'Two landline numbers found identical',
	msg_landline_startchk 	   : 'Landline can not start with 0 or 1',
	msg_landline_invalid	   : 'Please enter valid landline',
	msg_mobile_required 	   : 'Please enter mobile number',
	msg_mobile_maxLength 	   : 'Mobile can not exceed ' + jd_defaults.mobile.MAXLENGTH + ' digits',
	msg_mobile_startchk 	   : 'Mobile can start with 6, 7,8 or 9 only',
	msg_mobile_minLength	   : 'Mobile number should contain atleast ' + jd_defaults.mobile.MINLENGTH + ' digits',
	msg_mobile_duplicate       : 'Two mobile numbers found identical',
	msg_tollfree_required	   : 'Please enter tollfree number',
	msg_tollfree_maxLength 	   : 'Tollfree can not exceed ' + jd_defaults.tollfree.MAXLENGTH + ' digits',
	msg_tollfree_minLength	   : 'Tollfree number should contain atleast ' + jd_defaults.tollfree.MINLENGTH + ' digits',
	msg_tollfree_startchk 	   : 'Toll Free Number should start with 1800 or 1860 or 0008',
	msg_tollfree_isnumeric     : 'Toll Free can contain numbers only',
	msg_tollfree_duplicate     : 'Two tollfree numbers found identical',
	msg_fax_invalid			   : 'Enter valid fax number',
	msg_fax_length			   : 'Fax number length can be between 6 to 8',
	msg_fax_isnumeric		   : 'Fax number can contain numbers only',
	msg_email_required		   : 'Please enter email',
	msg_email_invalid		   : 'Invalid email',
	msg_email_duplicate        : 'Two email address found identical',
	msg_contactperson_required : 'Please enter contact person',
	msg_contactperson_repChar  : 'Contact Person contains more than 4 repeated alphabets',
	msg_contactperson_alpha	   : 'Contact Person should contain only alphabets',
	msg_contactperson_repText  : 'Contact Person contains repeated text',
	msg_contactperson_duplicate :  'Two contact person found identical',
	msg_contactperson_required  : 'Please enter Designation',
	msg_designation_repChar  	: 'Designation contains more than 4 repeated alphabets',
	msg_designation_alpha	   	: 'Designation should contain only alphabets',
	msg_designation_repText  	: 'Designation contains repeated text',
	msg_designation_duplicate 	: 'Two designations found identical',
	msg_website_required	   : 'Please enter website',
	msg_website_invalid		   : 'Invalid website',
	msg_website_limit		   : 'You can not enter more than two website',
	msg_website_repeat		   : 'Same URL repeatead more then once will not be allowed',
	msg_yearestablish_required : 'Please enter Year of Establishment',
	msg_yearestablish_invalid  : 'Please enter valid Year of Establishment',
	msg_yearestablish_curyear  : 'Year of establishment can not be greater than current year',
	msg_smscode_required 	   : 'Please enter sms code',
	msg_smscode_invalid		   : 'Please enter correct sms code',
	msg_smscode_isnumeric	   : 'Short SMS code should contain Numeric value.Please enter correct SMS Code',
	msg_socialurl_required		: 'Please enter Social Media URL',
	msg_socialurl_invalid		: 'Please enter valid Social Media URL',
	msg_otherstd_required		: 'Please enter othercity std code',
	msg_otherstd_isnumeric		: 'Othercity stdcode can contain numbers only',
	msg_otherstd_length			: 'Othercity stdcode length can be between 2 to 4',
	msg_othertele_required		: 'Please enter Othercity Number number',
	msg_othertele_duplicate		: 'Two Othercity numbers found identical',
	msg_othertele_minLength		: 'Othercity number should contain atleast ' + jd_defaults.othertele.MINLENGTH + ' digits',
	msg_othertele_maxLength		: 'Othercity number can not exceed ' + jd_defaults.othertele.MAXLENGTH + ' digits',
	msg_otherlandline_invalid	: 'Please enter valid Othercity number',
	msg_otherlandline_startchk	: 'Othercity number can not start with 0 or 1',
};
/*
$(document).ready (function(){

	$('.jd_rule').blur(function () {
		if($(this).attr('validation') != 'business')
		validateContent(this, false);
	});

	$('#button').unbind('click').click(function () {
		validateContent(this, false);
	});
});
*/
var OtherCityNumFlag = 0;
var params;
function validateData()
{
	params		= 	{};	
	//~ params['tele_arr']		=	{};
	if($('#cspaid').length){
		OtherCityNumFlag = setOtherCityFlag();
	}
	var err_msg = new Array();
	var i       = 0;
	var global_params = {};
	$('.jd_rule').each(function () {
		err_msg[i] = validateContent(this, true);
		i += 1;
	});
	
	var global_err ;
	global_err	=	validateGlobal();
	if(!global_err){
		return false;
	}
	err_msg = (uniqueArray(err_msg)).join("\r\n");
	if(err_msg==''){
		return true;
	}
	return err_msg;
}

var mobile_arr  = 	[];
var tele_arr 	=	[];

function validateGlobal(){
	var building_name	= $("#plot").val();
	if(building_name!=''){
		params['building_name'] 	= building_name;
	}
	var street	= $("#street").val();
	if(street!=''){
		params['street'] 	= street;
	}
	if($('#parentid').length)
	{
		params['parentid'] 	= $("#parentid").val();
	}
	var landmark	= $("#landmark").val();
	if(landmark!=''){
		params['landmark'] 	= landmark;
	}
	var area	= $("#area").val();
	if(area!=''){
		params['area'] 	= area;
	}
		
	var person_designation	=	$("#person_designation").val();
	if(person_designation!='' && person_designation!=undefined){
		params['designation'] 	= person_designation;
	}
	var person2_designation	=	$("#person2_designation").val();
	if(person2_designation!='' && person2_designation!=undefined){
		params['designation'] 	+= "|~|"+person2_designation;
	}
	
	var  data_city 	= 	'';
	if($('#data_city').length){
		data_city	= $('#data_city').val();
	}
	else{
		data_city = DATA_CITY;
	}	
	
	params['data_city'] 	= data_city;
	var paid_flow 	=	$("#paid_flow").val();
	var helpline_flag =0;
	if($("#helpline_flag").prop( "checked" ) || $("#helpline_flag").prop('disabled')){
		helpline_flag = 1;		
	}
	params['helpline_flag'] = helpline_flag;
	if(paid_flow ==1){
		params['module'] 		= "CS";
	}
	else{
		params['module'] 		= "DE";
	}
	var excl_cat_flag =0;
	if(paid_flow !=1){
		if($("#excl_cat_flag").prop("checked")){
			excl_cat_flag = 1;		
		}
	}
	params['excl_cat_flag'] = excl_cat_flag;
	var pagename = '';
	if($("#pagename").length){
		pagename =	$("#pagename").val();
	}
	if(pagename=='bform'){
		params['format_compname'] = 1;
	}
	
	var error_flag = ajaxCall(params);
	if(error_flag==1){
		return false;
	}
	else{
		return true;
	}
	//console.log(params);
}
function ajaxCall(param){
	var error_flag =0;
	var format_flag =0;
	var prompt_flag =0;
	xhr = $.ajax({
		url	:	'http://' +http_host+'/business/curl_request.php',
		type  : 'POST',
		async : false,
		data  : param,
		
		success: function(response) {
			if (response != '')
			{
				var res_arr = JSON.parse(response);
				//console.log("res",typeof(response));
				//var res = {};
					//res =	jQuery.parseJSON(response);				
				if(res_arr.error.code==1){
					var block_msg 	='';
					var prompt_msg 	= '';
					var format_msg	= '';					
									
					$.each(res_arr,function(key,value){
						if(key!='error'){
							$.each(value.msg,function(key1,msg1){								
								if(key=='block'){
									error_flag = 1;
									block_msg += msg1+"\n";
								}
								else if(key=='prompt'){
									prompt_msg += msg1+"\n";
									prompt_flag = 1;
								}
								else{
									format_msg = msg1; 
								}
								if(key=='format_action'){
									format_flag=1;
								}
							});
						}
					});					
					if(error_flag==1){
						alert(block_msg);
					}
					else if(format_flag==1){						
						formatCompMsg(prompt_msg,format_msg);
					}
					else{
						alert(prompt_msg);
					}
				}
			}
		}
	});
	if(format_flag==1){
		error_flag = 1;
	}
	return error_flag;
}
function formatCompMsg(prompt_msg,compname){
	if(prompt_msg){
		alert(prompt_msg);
	}
	var comp_chk ='';
	var capt_comp_name = compname;						
	comp_chk += '<span class="pp-title">Company name will be updated as "'+capt_comp_name+'" as per proper word case.</span>';
	comp_chk += '<div class="pp-btn-wrp">';
	comp_chk += '<a href="javascript:void(0);" class="syn_no" onclick="no_btn()">Skip</a>';
	comp_chk += '<a href="javascript:void(0);" class="syn_yes" onclick=\'prcd_btn("'+capt_comp_name+'")\'>Allow</a>';
	comp_chk += '</div>';
	$('#compsyn_div').html(comp_chk);
	$('#compsyn_div').show();
	$('#doc_helpdesk_cover').show();
}

function no_btn()
{
	$('#compsyn_div').hide();
	$('#doc_helpdesk_cover').hide();
	//alert("You have selected '"+$("#compstatus option:selected").text()+"' in Company Status");
	//set_regionid();
	proceedAfterCompname();
}
function prcd_btn(new_comp_name)
{	
	$('#compsyn_div').hide();    
	$('#doc_helpdesk_cover').hide();	
	document.getElementById("bname").innerHTML= new_comp_name;
	document.getElementById("bname").value = new_comp_name;	
	//alert("You have selected '"+$("#compstatus option:selected").text()+"' in Company Status");
	//set_regionid();
	proceedAfterCompname();
}
function proceedAfterCompname(){
	var $if_error = createbortxtbox();//returns tru if no error, if red marked return false;
				set_regionid();
				update_contacts();

				if(!$if_error){
					$("#dialog-confirm").dialog({
					  resizable: false,
					  height:180,
					  width:450,
					  modal: true,
					  buttons: [    {
						  text: "Proceed Further", 
						  width:150,     
						  click: function() {  
								bfromProceed()    
							  }    
						  } ,
						  {      
						   text: "Go Back to form",
						   width:150,      
						   click: function() {        
								   $(this).dialog("close");  
								   removebortxtbox() 
							   }   
						  } ],
						  close: function() {
									removebortxtbox()    
								   }
					});
				}
				else{
					bfromProceed();
				}
}
function validateContent(fieldObj, validationmode)
{
	var err_count 	    = 0;
	var err_msg 	    = '';
	var call_ajx_return = '';
	var value           = $.trim($(fieldObj).val());
	var data_city		= $.trim($('#data_city').val());  // city from hidden value
	var city			= $.trim($('#jd_data_city').val());  // city from hidden value
	var sel_city		= $.trim($('#city').val());  		// selected city on bform	
	var validation_type = $(fieldObj).attr('validation');
	
	var skip_brand ='';
	if($('#skip_brand').length){
		 skip_brand = $('#skip_brand').val();
	}

	var std		  	=	$.trim($("input[validation='stdcode']").val());
	var mobile_val	=	$.trim($("input[validation='mobile']").val());
	var tele_val	=	$.trim($("input[validation='tele']").val());
	var tollfree_val=	$.trim($("input[validation='tollfree']").val());
	var otherstd  	=	$.trim($("input[validation='otherstd']").val());
	
	
	if(std[0] == '0')
	{
		std1 = std.substring(1);
		std = std1;
	}
	
	switch (validation_type)
	{
		case ('compname'):
			if (jd_defaults.compname.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_comp_required + "\r\n";
				if (!validationmode)  alert(messages.msg_comp_required);
			}
			
			
			if(value != '')
			{
				var value1 = encodeURIComponent(value);
				/*
				var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type+ '&' + validation_type + '=' + value1 + '&skip_brand=' + skip_brand + '&data_city='+data_city;
				//~ call_ajx_return  = callAjx(validation_type, pars, fieldObj, validationmode);

				//~ if(call_ajx_return != '')
					//~ err_msg += call_ajx_return + "\r\n";
					
				var value_comp = value.replace(/\s+/g,"");
				
				// check for maxlength
				if(value_comp.length > jd_defaults.compname.MAXLENGTH)
				{
					err_msg += messages.msg_comp_maxLength + "\r\n";
					if (!validationmode) alert(messages.msg_comp_maxLength);
					err_count = err_count + 1;
				}

				// check for minlength
				if(value_comp.length < jd_defaults.compname.MINLENGTH)
				{
					err_msg += messages.msg_comp_minLength + "\r\n";
					if (!validationmode) alert(messages.msg_comp_minLength);
					err_count = err_count + 1;
				}

				// check for special chars, allows alphanumeric and . , - , &
				if(jd_defaults.compname.SPLCHARREGEX.test(value_comp) == false)
				{
					err_msg += messages.msg_comp_splChar + "\r\n";
					if (!validationmode) alert(messages.msg_comp_splChar);
					err_count = err_count + 1;
				}

				// check for repeated chars
				if(jd_defaults.compname.REPEATCHAR === true && checkRepeatChar(value_comp))
				{
					err_msg += messages.msg_comp_repChar + "\r\n";
					if (!validationmode) alert(messages.msg_comp_repChar);
					err_count = err_count + 1;
				}
				
				// check for max numeric chars
				if(value_comp.replace(/[^0-9]/g,"").length > jd_defaults.compname.MAXNUM)
				{
					// check for repeated numbers
					if(jd_defaults.compname.REPEATCHAR === true && checkRepeatNum(value_comp))
					{
						err_msg += messages.msg_comp_repNum + "\r\n";
						if (!validationmode) alert(messages.msg_comp_repNum);
						err_count = err_count + 1;
					}
					else
					{
						err_msg += messages.msg_comp_maxNum + "\r\n";
						if (!validationmode) alert(messages.msg_comp_maxNum);
						err_count = err_count + 1;
					}
				}

				// check for salutation and web related words
				if(jd_defaults.compname.INVALIDWORDS.test(value_comp))
				{
					err_msg += messages.msg_comp_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_comp_invalid);
					err_count = err_count + 1;
				}

				// check for salutation
				if(checkSalutation(value_comp))
				{
					err_msg += messages.msg_comp_salutation + "\r\n";
					if (!validationmode) alert(messages.msg_comp_salutation);
					err_count = err_count + 1;
				}
				* */
				params['compname']= value;	
			}

		break;

		case ('state'):
		
			if($("#paid_flow").val() != 1 && (COM == true || customer_care == 1))
			{
				jd_defaults.state.REQUIRED	=	false;
		
			}
			else if($("#paid_flow").val() != 1 && (COM != true && customer_care == 0))
			{
				jd_defaults.state.REQUIRED	=	true;	
			}
		
			if (jd_defaults.state.REQUIRED === true && (value == '' || value == 'Select State'))
			{
				err_msg += messages.msg_state_required + "\r\n";
				if (!validationmode)  alert(messages.msg_state_required);
			}
			if(value!=''){
				params['state']= value;	
			}

		break;

		case ('city'):
			if($("#paid_flow").val() != 1 && (COM == true || customer_care == 1))
				jd_defaults.city.REQUIRED	=	false;
			else if($("#paid_flow").val() != 1 && (COM != true && customer_care == 0))
				jd_defaults.city.REQUIRED	=	true;	
				
				
			if (jd_defaults.city.REQUIRED === true && (value == '' || value == 'Select City'))
			{
				err_msg += messages.msg_city_required + "\r\n";
				if (!validationmode)  alert(messages.msg_city_required);
			}
			if(value!=''){
				params['city']= value;	
			}
		break;

		case ('area'):

			if (jd_defaults.area.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_area_required + "\r\n";
				if (!validationmode)  alert(messages.msg_area_required);
			}
			if(value != '')
			{
				// check for max numeric chars
				if(value.replace(/[^0-9]/g,"").length > jd_defaults.area.MAXNUM)
				{
					err_msg += messages.msg_area_maxNum + "\r\n";
					if (!validationmode) alert(messages.msg_area_maxNum);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.area.MINLENGTH)
				{
					err_msg += messages.msg_area_length + "\r\n";
					if (!validationmode) alert(messages.msg_area_length);
					err_count = err_count + 1;
				}

				// check for maxlength
				else if(value.length > jd_defaults.area.MAXLENGTH)
				{
					err_msg += messages.msg_area_length + "\r\n";
					if (!validationmode) alert(messages.msg_area_length);
					err_count = err_count + 1;
				}
				// check for repeated chars
				if(jd_defaults.area.REPEATCHAR === true && checkRepeatChar(value))
				{
					err_msg += messages.msg_area_repChar + "\r\n";
					if (!validationmode) alert(messages.msg_area_repChar);
					err_count = err_count + 1;
				}

				// check for numeric, do not allow only mumeric in area field
				if(!isNaN(value))
				{
					err_msg += messages.msg_area_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_area_invalid);
					err_count = err_count + 1;
				}
			}

		break;

		case ('stdcode'):
			if (jd_defaults.stdcode.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_std_required + "\r\n";
				if (!validationmode)  alert(messages.msg_std_required);
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.stdcode.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_std_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_std_isnumeric);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.stdcode.MINLENGTH)
				{
					err_msg += messages.msg_std_length + "\r\n";
					if (!validationmode) alert(messages.msg_std_length);
					err_count = err_count + 1;
				}

				// check for maxlength
				else if(value.length > jd_defaults.stdcode.MAXLENGTH)
				{
					err_msg += messages.msg_std_length + "\r\n";
					if (!validationmode) alert(messages.msg_std_length);
					err_count = err_count + 1;
				}
				params['stdcode'] =	value;
			}

		break;

		case ('tele'):
		//alert(std);
			var chkValObj 	= 	checkMultiVals(validation_type);
				//console.log("tollfree_val--"+tollfree_val +"  mobile_val--"+mobile_val+" jd_defaults.tele.REQUIRED---"+jd_defaults.tele.REQUIRED+" chkValObj.val_exists "+chkValObj.val_exists+ " OtherCityNumFlag--"+OtherCityNumFlag);
				
			if ((mobile_val == '') && (tollfree_val == '') && (jd_defaults.tele.REQUIRED === true) && (chkValObj.val_exists == false) && (OtherCityNumFlag !=1))
			{
				err_msg += messages.msg_tele_required + "\r\n";
				if (!validationmode) alert(messages.msg_tele_required);
				err_count = err_count + 1;
			}
			if (chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_tele_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_tele_duplicate);
				err_count = err_count + 1;
			}
				
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.tele.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_isnumeric);
					err_count = err_count + 1;
				}
				
				// landline can not start with 0 or 1
				else if(value.slice(0,1) == 0 || value.slice(0,1) == 1)
				{
					if($("input[validation='helpline_flag']").is(":checked") == false)
					{
						err_msg += messages.msg_landline_startchk + "\r\n";
						if (!validationmode) alert(messages.msg_landline_startchk);
						err_count = err_count + 1;
					}
				}

				// check for minlength
				else if(value.length < jd_defaults.tele.MINLENGTH)
				{
					if($("input[validation='helpline_flag']").is(":checked") == false)
					{
						err_msg += messages.msg_tele_minLength + "\r\n";
						if (!validationmode) alert(messages.msg_tele_minLength);
						err_count = err_count + 1;
					}
				}

				// check for maxlength
				else if(value.length > jd_defaults.tele.MAXLENGTH)
				{
					if($("input[validation='helpline_flag']").is(":checked") == false)
					{
						err_msg += messages.msg_tele_maxLength + "\r\n";
						if (!validationmode) alert(messages.msg_tele_maxLength);
						err_count = err_count + 1;
					}
				}

				else if(std != "" && (std.length+value.length) != jd_defaults.tele.MAXLENGTH)
				{
					if($("input[validation='helpline_flag']").is(":checked") == false)
					{
						err_msg += messages.msg_landline_invalid + "\r\n";
						if (!validationmode) alert(messages.msg_landline_invalid);
						err_count = err_count + 1;
					}
				}
				
				
				var seqcheck = checkMultiSequence('tele');
				if (seqcheck != '')
				{
					err_msg += seqcheck + "\r\n";
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
				
				var pars 				= 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type + '&' + validation_type + '=' + value + '&skip_brand=' + skip_brand;
				//~ var call_ajx_return  	= callAjx(validation_type, pars, fieldObj, validationmode);
				//~ if(call_ajx_return != '')
					//~ err_msg += call_ajx_return + "\r\n";
				if(params['landline']!='' && params['landline']!=undefined){
					params['landline']+=	value+"|~|";
				}
				else{
					params['landline']	=	value+"|~|";
				}
			}

		break;

		case ('mobile'):
		
			var chkValObj 	=	checkMultiVals(validation_type);
			if ((mobile_val == '') && (tele_val == '') && (tollfree_val == '') && (jd_defaults.mobile.REQUIRED === true) && (chkValObj.val_exists == false) && (OtherCityNumFlag !=1))
			{
				err_msg += messages.msg_mobile_required + "\r\n";
				if (!validationmode) alert(messages.msg_mobile_required);
				err_count = err_count + 1;
			}

			if (chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_mobile_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_mobile_duplicate);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.mobile.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_isnumeric);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.mobile.MINLENGTH)
				{
					err_msg += messages.msg_mobile_minLength + "\r\n";
					if (!validationmode) alert(messages.msg_mobile_minLength);
					err_count = err_count + 1;
				}
				// check for maxlength
				else if(value.length > jd_defaults.mobile.MAXLENGTH)
				{
					err_msg += messages.msg_mobile_maxLength + "\r\n";
					if (!validationmode) alert(messages.msg_mobile_maxLength);
					err_count = err_count + 1;
				}

				// mobile should start with 7, 8 or 9
				else if(value.slice(0,1) != 6 && value.slice(0,1) != 7 && value.slice(0,1) != 8 && value.slice(0,1) != 9)
				{
					err_msg += messages.msg_mobile_startchk + "\r\n";
					if (!validationmode) alert(messages.msg_mobile_startchk);
					err_count = err_count + 1;
				}

				var seqcheck = checkMultiSequence('mobile');
				if (seqcheck != '')
				{
					err_msg += seqcheck + "\r\n";
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
				
				var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type+ '&' + validation_type + '=' + value;

				//~ var call_ajx_return  = callAjx(validation_type, pars, fieldObj, validationmode);
				
				//~ if(call_ajx_return != '')
					//~ err_msg += call_ajx_return + "\r\n";
					console.log(params['mobile']);
				if(params['mobile']!='' && params['mobile']!=undefined){
					params['mobile']	+=	value+"|~|";
				}
				else{
					params['mobile']	=	value+"|~|";
				}
			}
			
		break;

		case ('tollfree'):
			var chkValObj = checkMultiVals(validation_type);

			if ((tele_val == '') && (mobile_val == '') && (jd_defaults.tollfree.REQUIRED === true) && (chkValObj.val_exists == false) && (OtherCityNumFlag !=1))
			{
				err_msg += messages.msg_tollfree_required + "\r\n";
				if (!validationmode) alert(messages.msg_tollfree_required);
				err_count = err_count + 1;
			}

			if (chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_tollfree_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_tollfree_duplicate);
				err_count = err_count + 1;
			}

			if(value != '')
			{
				// check for maxlength
				if(value.length > jd_defaults.tollfree.MAXLENGTH)
				{
					err_msg += messages.msg_tollfree_maxLength + "\r\n";
					if (!validationmode) alert(messages.msg_tollfree_maxLength);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.tollfree.MINLENGTH)
				{
					err_msg += messages.msg_tollfree_minLength + "\r\n";
					if (!validationmode) alert(messages.msg_tollfree_minLength);
					err_count = err_count + 1;
				}
				// tollfree should start with 1800 or 1860
				else if(value.slice(0,4)!= '1800' && value.slice(0,4) != '1860' && value.slice(0,4) != '0008')
				{
					err_msg += messages.msg_tollfree_startchk + "\r\n";
					if (!validationmode) alert(messages.msg_tollfree_startchk);
					err_count = err_count + 1;
				}
				// check for numeric
				if(jd_defaults.tollfree.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_tollfree_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_tollfree_isnumeric);
					err_count = err_count + 1;
				}

				var seqcheck = checkMultiSequence('tollfree');
				if (seqcheck != '')
				{
					err_msg += seqcheck + "\r\n";
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
				if(params['tollfree']!='' && params['tollfree']!=undefined){
					params['tollfree']+=	value+"|~|";
				}
				else{
					params['tollfree']	=	value+"|~|";
				}
			}

		break;

		case ('fax'):
			if(jd_defaults.fax.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_fax_required + "\r\n";
				if (!validationmode) alert(messages.msg_fax_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.fax.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_fax_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_fax_isnumeric);
					err_count = err_count + 1;
				}

				// check for minlength
				else if(value.length < jd_defaults.fax.MINLENGTH)
				{
					err_msg += messages.msg_fax_length + "\r\n";
					if (!validationmode) alert(messages.msg_fax_length);
					err_count = err_count + 1;
				}

				// check for maxlength
				else if(value.length > jd_defaults.fax.MAXLENGTH)
				{
					err_msg += messages.msg_fax_length + "\r\n";
					if (!validationmode) alert(messages.msg_fax_length);
					err_count = err_count + 1;
				}
				else if(std != "" && (std.length+value.length) != jd_defaults.fax.VALIDLENGTH)
				{
					err_msg += messages.msg_fax_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_fax_invalid);
						err_count = err_count + 1;
				}

				var seqcheck = checkMultiSequence('fax');
				if (seqcheck != '')
				{
					err_msg += seqcheck + "\r\n";
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
				if(params['fax']!='' && params['fax']!=undefined){
					params['fax']+=	value+"|~|";
				}
				else{
					params['fax']	=	value+"|~|";
				}
			}

		break;

		case ('email'):
			var chkValObj = checkMultiVals(validation_type);

			if(jd_defaults.email.REQUIRED === true && chkValObj.val_exists == false)
			{
				err_msg += messages.msg_email_required + "\r\n";
				if (!validationmode) alert(messages.msg_email_required);
				err_count = err_count + 1;
			}

			if(chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_email_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_email_duplicate);
				err_count = err_count + 1;
			}

			if(value != '')
			{
				// check for valid email
				if(jd_defaults.email.PATTERN.test(value) == false)
				{
					err_msg += messages.msg_email_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_email_invalid);
					err_count = err_count + 1;
				}
				if(params['email']!='' && params['email']!=undefined){
					params['email']+=	value+"|~|";
				}
				else{
					params['email']	=	value+"|~|";
				}				
			}

			var seqcheck = checkMultiSequence('email');
			if (seqcheck != '')
			{
				err_msg += seqcheck + "\r\n";
				if (!validationmode) alert(seqcheck);
				err_count = err_count + 1;
			}


		break;

		case ('contactperson'):

			var chkValObj = checkMultiVals(validation_type);

			if(jd_defaults.contactperson.REQUIRED === true && chkValObj.val_exists == false)
			{
				err_msg += messages.msg_contactperson_required + "\r\n";
				if (!validationmode) alert(messages.msg_contactperson_required);
				err_count = err_count + 1;
			}

			if(chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_contactperson_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_contactperson_duplicate);
				err_count = err_count + 1;
			}

			if(value != '')
			{
				// check for repeated chars
				if(jd_defaults.contactperson.REPEATCHAR === true && checkRepeatChar(value))
				{
					err_msg += messages.msg_contactperson_repChar + "\r\n";
					if (!validationmode) alert(messages.msg_contactperson_repChar);
					err_count = err_count + 1;
				}

				// check for repeated text
				/*if(jd_defaults.contactperson.REPEATTEXT === true && checkRepeatText(value))
				{
					err_msg += messages.msg_contactperson_repText + "\r\n";
					if (!validationmode) alert(messages.msg_contactperson_repText);
					err_count = err_count + 1;
				}*/

				// check for alphabets
				if(jd_defaults.contactperson.ALPHAREGEX.test(value) == false)
				{
					err_msg += messages.msg_contactperson_alpha + "\r\n";
					if (!validationmode) alert(messages.msg_contactperson_alpha);
					err_count = err_count + 1;
				}
				if(params['contact_person']!='' && params['contact_person']!=undefined){
					params['contact_person']+=	value+"|~|";
				}
				else{
					params['contact_person']	=	value+"|~|";
				}
			}

		break;
		
		case ('designation'):

			var chkValObj = checkMultiVals(validation_type);

			if(jd_defaults.designation.REQUIRED === true && chkValObj.val_exists == false)
			{
				err_msg += messages.msg_designation_required + "\r\n";
				if (!validationmode) alert(messages.msg_designation_required);
				err_count = err_count + 1;
			}

			if(chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_designation_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_designation_duplicate);
				err_count = err_count + 1;
			}

			if(value != '')
			{
				// check for repeated chars
				if(jd_defaults.designation.REPEATCHAR === true && checkRepeatChar(value))
				{
					err_msg += messages.msg_designation_repChar + "\r\n";
					if (!validationmode) alert(messages.msg_designation_repChar);
					err_count = err_count + 1;
				}

				// check for repeated text
				/*if(jd_defaults.designation.REPEATTEXT === true && checkRepeatText(value))
				{
					err_msg += messages.msg_designation_repText + "\r\n";
					if (!validationmode) alert(messages.msg_designation_repText);
					err_count = err_count + 1;
				}*/

				// check for alphabets
				if(jd_defaults.designation.ALPHAREGEX.test(value) == false)
				{
					err_msg += messages.msg_designation_alpha + "\r\n";
					if (!validationmode) alert(messages.msg_designation_alpha);
					err_count = err_count + 1;
				}
			}

		break;

		case ('pincode'):
			if(jd_defaults.pincode.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_pincode_required + "\r\n";
				if (!validationmode) alert(messages.msg_pincode_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.pincode.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_pincode_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_pincode_invalid);
					err_count = err_count + 1;
				}

				// check for maxlength
				if(value.length > jd_defaults.pincode.MAXLENGTH)
				{
					err_msg += messages.msg_pincode_length + "\r\n";
					if (!validationmode) alert(messages.msg_pincode_length);
					err_count = err_count + 1;
				}

				// check for minlength
				if(value.length < jd_defaults.pincode.MINLENGTH)
				{
					err_msg += messages.msg_pincode_length + "\r\n";
					if (!validationmode) alert(messages.msg_pincode_length);
					err_count = err_count + 1;
				}
				params['pincode']= value;
			}

		break;

		case ('smscode') :

			if(jd_defaults.smscode.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_smscode_required + "\r\n";
				if (!validationmode) alert(messages.msg_smscode_required);
			}
			if(value != '')
			{
				// check for minlength
				if(value.length < jd_defaults.smscode.MINLENGTH)
				{
					err_msg += messages.msg_smscode_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_smscode_invalid);
					err_count = err_count + 1;
				}

				// check for numeric
				if(jd_defaults.smscode.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_smscode_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_smscode_isnumeric);
					err_count = err_count + 1;
				}
			}

		break;

		case ('website'):

			if(jd_defaults.website.REQUIRED === true && value == '')
			{
				err_msg +=  messages.msg_website_required + "\r\n";
				if (!validationmode) alert(messages.msg_website_required);
			}

			if(value != '')
			{	//value = value.replace(' ','');
				var websitvalearr = value.split(',');
				if(websitvalearr.length > 2)
				{
					err_msg += messages.msg_website_limit + "\r\n";
					if (!validationmode) alert(messages.msg_website_limit);
						err_count = err_count + 1;
				}
				
				// same URL repeated more than once is not allowed
				var webuniquearr	= uniqueArray(websitvalearr);
				if(webuniquearr.length != websitvalearr.length)
				{
					err_msg += messages.msg_website_repeat + "\r\n";
					if (!validationmode) alert(messages.msg_website_repeat);
						err_count = err_count + 1;
				}
				// check for valid website
				for (var i=0; i<websitvalearr.length; i++)
				{
					if(jd_defaults.website.URLREGEX.test($.trim(websitvalearr[i])) == false)
					{
						err_msg += messages.msg_website_invalid + "\r\n";
						if (!validationmode) alert(messages.msg_website_invalid);
						err_count = err_count + 1;
					}
					/*if(websitvalearr[i].toLowerCase().indexOf('www.') != '0')
					{
						err_msg += "\r\n" + messages.msg_website_invalid;
						if (!validationmode) alert(messages.msg_website_invalid);
						err_count = err_count + 1;
					}*/
				}
				if(params['website']!='' && params['website']!=undefined){
					params['website']+=	value+"|~|";
				}
				else{
					params['website']	=	value+"|~|";
				}
			}
		break;
		
		case ('otherstd'):
			if (jd_defaults.otherstd.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_otherstd_required + "\r\n";
				if (!validationmode)  alert(messages.msg_otherstd_required);
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.otherstd.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_otherstd_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_otherstd_isnumeric);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.otherstd.MINLENGTH)
				{
					err_msg += messages.msg_otherstd_length + "\r\n";
					if (!validationmode) alert(messages.msg_otherstd_length);
					err_count = err_count + 1;
				}

				// check for maxlength
				else if(value.length > jd_defaults.otherstd.MAXLENGTH)
				{
					err_msg += messages.msg_otherstd_length + "\r\n";
					if (!validationmode) alert(messages.msg_otherstd_length);
					err_count = err_count + 1;
				}
			}

		break;

		case ('othertele'):
		
			var chkValObj 	= 	checkMultiVals(validation_type);
				
			if (jd_defaults.othertele.REQUIRED === true && chkValObj.val_exists == false)
			{
				err_msg += messages.msg_othertele_required + "\r\n";
				if (!validationmode) alert(messages.msg_othertele_required);
				err_count = err_count + 1;
			}
			if (chkValObj.val_duplicate == true)
			{
				err_msg += messages.msg_othertele_duplicate + "\r\n";
				if (!validationmode) alert(messages.msg_othertele_duplicate);
				err_count = err_count + 1;
			}
				
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.othertele.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_isnumeric + "\r\n";
					if (!validationmode) alert(messages.msg_isnumeric);
					err_count = err_count + 1;
				}
				
				// landline can not start with 0 or 1
				else if(value.slice(0,1) == 0 || value.slice(0,1) == 1)
				{
					err_msg += messages.msg_otherlandline_startchk + "\r\n";
					if (!validationmode) alert(messages.msg_otherlandline_startchk);
					err_count = err_count + 1;
				}

				// check for minlength
				else if(value.length < jd_defaults.othertele.MINLENGTH)
				{
					err_msg += messages.msg_othertele_minLength + "\r\n";
					if (!validationmode) alert(messages.msg_othertele_minLength);
					err_count = err_count + 1;
				}

				// check for maxlength
				else if(value.length > jd_defaults.othertele.MAXLENGTH)
				{
					err_msg += messages.msg_othertele_maxLength + "\r\n";
					if (!validationmode) alert(messages.msg_othertele_maxLength);
					err_count = err_count + 1;
				}
				
				var seqcheck = checkMultiSequence('othertele');
				if (seqcheck != '')
				{
					err_msg += seqcheck + "\r\n";
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
			}

		break;

		case ('yearestablish'):
			if(jd_defaults.yearestablish.REQUIRED === true && value == '')
			{
				err_msg += messages.msg_yearestablish_required + "\r\n";
				if (!validationmode) alert(messages.msg_yearestablish_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.yearestablish.NUMERICREGEX.test(value) == false)
				{
					err_msg += messages.msg_yearestablish_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_yearestablish_invalid);
					err_count = err_count + 1;
				}

				// check for length
				if(value.length != jd_defaults.yearestablish.VALIDLENGTH)
				{
					err_msg += messages.msg_yearestablish_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_yearestablish_invalid);
					err_count = err_count + 1;
				}
				// check if value is less than current year
				var d1 			= new Date();
				var cur_year 	= d1.getFullYear();
				
				if(value > cur_year)
				{
					err_msg += messages.msg_yearestablish_curyear + "\r\n";
					if (!validationmode) alert(messages.msg_yearestablish_curyear);
					err_count = err_count + 1;
				}				
				params['year_of_est']= value;					
			}
		break;
		
		case ('socialurl'):
			if(jd_defaults.socialurl.REQUIRED === true && value == '')
			{				
				err_msg +=  messages.msg_socialurl_required + "\r\n";
				if (!validationmode) alert(messages.msg_socialurl_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				if((check_url(value) ==	false)	|| (value.search(",") >= 0 ))
				{
					err_msg += messages.msg_socialurl_invalid + "\r\n";
					if (!validationmode) alert(messages.msg_socialurl_invalid);
					err_count = err_count + 1;
				}
			}
		break;

		case ('business'):

			var tele     = '';
			var mobile   = '';
			var compname = '';
			var pincode  = '';

			$('.jd_rule').each(function () {
				if ($(this).attr('validation') == 'tele') { tele += ((tele != '') ? '|' + $.trim($(this).val()) : $.trim($(this).val())); }
				if ($(this).attr('validation') == 'mobile') { mobile += ((mobile != '') ? '|' + $.trim($(this).val()) : $.trim($(this).val()));  }
				if ($(this).attr('validation') == 'compname') compname = $.trim($(this).val());
				if ($(this).attr('validation') == 'pincode') pincode  = $.trim($(this).val());
			});

			// check for duplicate number
			var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type + '&tele=' + tele + '&mobile=' + mobile + '&compname=' + compname + '&pincode=' + pincode;

			//~ var call_ajx_return  = callAjx(validation_type, pars, fieldObj, validationmode);
			//~ if(call_ajx_return != '')
				//~ err_msg += call_ajx_return + "\r\n";

		break;

		default:
		break;
	}

	if (err_count > 0)
	{
		//$(fieldObj).val('');
		//if (validationmode == false) $(fieldObj).focus();
	}

	return err_msg;
}

function setOtherCityFlag()
{
	var otherCityChk = 0;
	var str_othercitynumber = '';
	if($('#new_other_city_number_loop').length && $('#OtherCityNumber').length){
		var other_city_num = $("#OtherCityNumber").val().trim();
		var other_city_num_arr = [];
		if(other_city_num !=''){
			other_city_num_arr = other_city_num.split(',');
			var othercityloopcnt = $("#new_other_city_number_loop").val().trim();
			if(othercityloopcnt != ""){
				for(var i = 1; i<othercityloopcnt; i++){
					if($('#other_std_value_'+i).length && $('#other_num_value_'+i).length){
						var other_std_value = $('#other_std_value_'+i).val().trim();
						var other_num_value = $('#other_num_value_'+i).val().trim();
						if(other_num_value!=''){
							var str_othercity = other_city_num_arr[i-1].split('##');
							str_othercitynumber +=str_othercity[0]+'##'+other_std_value+'##'+other_num_value+',';
							break;
						}
					}
				}
			}
		}
		if(str_othercitynumber !=''){
			otherCityChk = 1;
		}
	}
	return otherCityChk;
}

function callAjx(validation_type, pars, fieldObj, validationmode)
{
	var xhr;
	var async_mode = ((validationmode == true) ? false : true);
	var msg = '';

	if(xhr && xhr.readyState != 4) xhr.abort();

	xhr = $.ajax({

		url	:	'http://' + http_host + '/jdbox.php',
		type  : 'POST',
		async : async_mode,
		data  : pars,
		success: function(response) {
			if (response != '')
			{
				msg = response;
				if (!validationmode)
				{
					if(response.indexOf("Companyname matches with brand name") <= 0)
					{
						$(fieldObj).val('');
					}
					//$(fieldObj).focus('');
					//alert(response);
				}
			}
		}
	});
	return msg;
}

function checkMultiSequence(validation_type)
{
	var oldObj   = null;
	var itmcount = 0;
	var msg      = '';

	$('.jd_rule').each (function () {

		if($(this).attr('validation') == validation_type)
		{
			if (oldObj != null && $.trim($(this).val()) != '' && $.trim($(oldObj).val()) == '')
			{
				msg = 'You can not enter ' + validation_type + ' ' + (itmcount+1) + ' without entering ' +validation_type + ' ' + itmcount;
			}

			itmcount += 1;
			oldObj = this;
		}
	});

	return msg;
}


function checkMultiVals(validation_type)
{
	var val_exist_flag     = false;
	var val_duplicate_flag = false;
	var field_array        = new Array();
	
	$('.jd_rule').each(function () {
		if (validation_type == $(this).attr('validation') && $.trim($(this).val()) != '')
		{
			val_exist_flag = true;
			field_array.push($.trim($(this).val()));
		}
	});
	
	if(field_array.length > 0)
	{
		for(var i = 0; i < field_array.length; i++)
		{
			for(var j = 0; j < field_array.length; j++)
			{
				if (field_array[i] == field_array[j] && i != j)
				{
					val_duplicate_flag = true;

				}
			}
		}
	}

	return {'val_exists' : val_exist_flag,  'val_duplicate' : val_duplicate_flag };
}
// Function to check repeated characters in the string
function checkRepeatChar(str)
{
	//str = str.replace(/\s+/g,"_");
	str = str.replace(/\s+/g,"");
	str = str.toUpperCase();
	//if(/(\d)(\1{4,})/g.test(str))
	if(/([A-Za-z])(\1{4,})/g.test(str)) {
		return true;
	} else {
		return false;
	}
}

// Function to check repeated numeric in the string
function checkRepeatNum(num)
{
	//num = num.replace(/\s+/g,"_");
	num = num.replace(/\s+/g,"");
	num = num.toUpperCase();
	if(/(\d)(\1{6,})/g.test(num)) {
		return true;
	} else {
		return false;
	}
}

function checkSalutation(str)
{
	var split_duc = str.split(' ');
	for(var i=0; i<split_duc.length; i++)
	{
		if(split_duc[i] == 'mr')
		{
			return true;
		}
		else if(split_duc[i] == 'mrs')
		{
			return true;
		}
		else if(split_duc[i] == 'dr')
		{
			return true;
		}
		else if(split_duc[i] == 'miss')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
// Space, Ampersand, Single Quote, Open Bracket, Close Bracket, Comma, Hyphen, Colon, Slash, Back Slash are allowed in Address fields - building name, street, landmark
function checkAddressSplChar(str)
{
	if(/^[a-zA-Z0-9- &()',.\://\\]*$/.test(str)){
		return true;
	}
	else{
		return false;
	}
}

function validateAddress(building_name, street, landmark, area, validationmode)
{
	var fulladdress = building_name + street + landmark + area;
	var err_msg     = '';
	var address_regex = /^[a-zA-Z0-9- &().@',\://\\]*$/;

	/*if(fulladdress.length < 5)
	{
		if (!validationmode) alert('Address fields(Building + Street + Landmark + Area) should collectively atleast 5 characters');
		err_msg += "\r\n" + 'Address fields(Building + Street + Landmark + Area) should collectively atleast 5 characters'
	}
	*/
	if(checkRepeatChar(building_name))
	{
		if (!validationmode) alert('Building name contains more than 4 repeated characters');
		err_msg += 'Building name contains more than 4 repeated characters' + "\r\n";
	}

	if(checkRepeatChar(street))
	{
		if (!validationmode) alert('Street contains more than 4 repeated characters');
		err_msg += 'Street contains more than 4 repeated characters' + "\r\n";
	}

	if(checkRepeatChar(landmark))
	{
		if (!validationmode) alert('Landmark contains more than 4 repeated characters');
		err_msg += 'Landmark contains more than 4 repeated characters' + "\r\n";
	}
	
	if(checkRepeatNum(building_name))
	{
		if (!validationmode) alert('Building name contains more than 6 numerical characters');
		err_msg += 'Building name contains more than 6 numerical characters' + "\r\n";
	}

	if(checkRepeatNum(street))
	{
		if (!validationmode) alert('Street contains more than 6 numerical characters');
		err_msg += 'Street contains more than 6 numerical characters' + "\r\n";
	}

	if(checkRepeatNum(landmark))
	{
		if (!validationmode) alert('Landmark contains more than 6 numerical characters');
		err_msg += 'Landmark contains more than 6 numerical characters' + "\r\n";
	}

	if(!checkAddressSplChar(building_name))
	{
		if (!validationmode) alert('Building Name contains special characters');
		err_msg += 'Building Name contains special characters' + "\r\n";
	}

	if(!checkAddressSplChar(landmark))
	{
		if (!validationmode) alert('Landmark contains special characters');
		err_msg += 'Landmark contains special characters' + "\r\n";
	}

	if(!checkAddressSplChar(street))
	{
		if (!validationmode) alert('Street contains special characters');
		err_msg += 'Street contains special characters' + "\r\n";
	}

	var addrArr   = Array(building_name, street, landmark, area);
	var dup_error = false;

	for (var i=0; i<addrArr.length; i++)
	{
		if (dup_error === true) break;

		for (var j=0; j<addrArr.length; j++)
		{
			//str = str.replace(/\s+/g,"");
			addrArr[i] = addrArr[i].replace(/\s+/g,"");
			addrArr[j] = addrArr[j].replace(/\s+/g,"");
			if(addrArr[i].toUpperCase() == addrArr[j].toUpperCase() && i != j && addrArr[i].toUpperCase() != '' && addrArr[j].toUpperCase() != '')
			{
				if (!validationmode) alert('Address lines should not be duplicate');
				err_msg += 'Address lines should not be duplicate' + "\r\n";
				dup_error = true;
				break;
			}
		}
	}

	if (validationmode == true)
	{
		return err_msg;
	}
	else
	{
		return (err_msg != '') ? false : true;
	}
}

function validateFeedback(paid_status,validationmode)
{
	var err_msg 		= '';
	if(paid_status == 1 || paid_status == 'paid')
	{
		var fdbk_count 		= 0;
		var fdbk_uncheck 	= 0;
		var fdbk_check 		= 0;
		var fdbk_check_val_exists = 0;
		$("input[validation='feedback_check']" ).each(function() {
			if($(this).is('[name^="mobile"]')) {
				fdbk_count += 1;
	
				if($(this).is(":checked") == false)//if($(this).prop('checked') == false)
				{
					fdbk_uncheck += 1;
				}
				else 
				{
					fdbk_check += 1;
					var id_val = $.trim($("input[validation='mobile']").val());

					if(id_val == '')
					{
						if(!validationmode) alert("Please enter mobile number");
						err_msg = 'Please enter mobile number' + "\r\n";
					}
					else if(id_val != '')
					{
						fdbk_check_val_exists += 1;
					}
				}
			};
		});
		var mobile_val	=	$.trim($("input[validation='mobile']").val());

		// && $("input[validation='feedback_reason']").is(':visible')

		if((fdbk_uncheck == fdbk_count) && ($.trim($("input[validation='feedback_reason']").val()) == "") && ($.trim($("input[validation='mobile']").val()) != ''))
		{
			err_msg += 'Please enter the feedback';
			$('#shw_feed').show();//$("input[validation='feedback_reason']").show();
			$("input[validation='feedback_reason']").focus();			
		}
		else if( fdbk_uncheck == fdbk_count &&  $.trim($("input[validation='feedback_reason']").val().length) < 10 && $.trim($("input[validation='mobile']").val()) != '')
		{
			err_msg += 'Please enter feedback reason more than 10 characters';
			$('#shw_feed').show();
			$("input[validation='feedback_reason']").focus();
		}
		else if(fdbk_check_val_exists > 0 )
		{
			$("input[validation='feedback_reason']").val('');
			//$("input[validation='feedback_reason']").hide();
		}
	}
	return err_msg;
}

// Give unique array
function uniqueArray(a)
{
    var temp = new Array();
    for (var i = 0; i < a.length; i++)
    {
        temp[a[i]] = true;
	}
    var r = new Array();
    for (var k in temp)
    {
		if (k != '' && k != 'undefined' && typeof(k) != 'undefined' && k != 'contains')
		{
			r.push(k);
		}
	}
    return r;
}

function checkRepeatText(str)
{
	var str_arr 	   = str.split(' ');
	var unique_arr	   = uniqueArray(str_arr);

	return (str_arr.length != unique_arr.length) ? true : false;
}

//Validation for social URL
function check_url(url)
{
	var retval=false;
	
	if(url.search("http://www.facebook.com/")==0 || url.search("http://plus.google.com/")==0 || url.search("http://www.twitter.com/")==0 || url.search("https://www.facebook.com/")==0 || url.search("https://plus.google.com/")==0 || url.search("https://www.twitter.com/")==0 || url.search("www.facebook.com/")==0 || url.search("plus.google.com/")==0 || url.search("www.twitter.com/")==0) {
		
		retval=true;
	} 
	
	if(url.search(" ")>=0)
		retval=false;
		
	if(url=="http://www.facebook.com/" || url=="http://plus.google.com/" || url=="http://www.twitter.com/" || url=="https://www.facebook.com/" || url=="https://plus.google.com/" || url=="https://www.twitter.com/" || url=="www.facebook.com/" || url=="plus.google.com/" || url=="www.twitter.com/") {
		
		retval=false;
	} 	
	
	return retval;
}

/*
function checkCategory()
{
	var field_array = new Array();

	var city		= $.trim($("#jd_data_city").val());

	$('.jd_rule').each(function () {
		if ($(this).attr('validation') == 'category' && $(this).attr('checked') == false)
		{
			field_array.push($.trim($(this).val()));
		}
	});


	if(field_array.length > 0)
	{
		var data_str	= 'arr_cat='+field_array+'&type=category&city='+city;
		var xhr;

		if(xhr && xhr.readyState != 4) // abort any previous action
		{
			xhr.abort();
		}

		xhr = $.ajax({
			url	 : 'http://' + http_host + '/jdbox/jdbox.php',
			type : 'POST',
			data : data_str,
			success: function(response) {
				if (response != '') {
					alert(response);
				}
				else {
				}

			}
		});
	}
}
*/
