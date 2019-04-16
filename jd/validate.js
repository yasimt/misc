var rgx_numeric 		    = /^[0-9]+$/,
	rgx_alpha 		        = /^[a-zA-Z]+$/i,
	rgx_alpha_numeric	    = /^[a-z0-9]+$/i,
	rgx_spl_char	 	    = /^[a-z0-9 .]+$/i,
	rgx_alpha_space	        = /^[a-zA-Z ]+$/i,
	rgx_url 			    = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/,
	//rgx_comp_invalid_words	= /www\.|http|https|mr\s|mrs\s|ms\s|dr\s|miss\s|mr\.\s|mrs\.\s|ms\.\s|dr\.\s/i;
	rgx_comp_invalid_words	= /www\.|www|https|http/i;
	rgx_comp_spl_char		= /^[a-z0-9 .!&@()'-\/]+$/i;
	rgx_email			    = /^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/i;
	
var jd_defaults = {

	compname : {
		MAXLENGTH 	 : 120,
		MINLENGTH	 : 2,
		MAXNUM		 : 4,
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
	
	std : {
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
	}
};

var messages = {
	msg_comp_required 		   : 'Please enter companyname',
	msg_state_required 		   : 'Please enter state',
	msg_city_required 		   : 'Please enter city',
	msg_area_required 		   : 'Please enter area',
	msg_std_required 		   : 'Please enter std code',
	msg_std_isnumeric		   : 'Stdcode can contain numbers only',
	msg_tele_required 		   : 'Please enter landline number',
	msg_mobile_required 	   : 'Please enter mobile number',
	msg_tollfree_required	   : 'Please enter tollfree number',
	msg_email_required		   : 'Please enter email',
	msg_website_required	   : 'Please enter website',
	msg_pincode_required	   : 'Please enter pincode',
	msg_contactperson_required : 'Please enter contact person',
	msg_smscode_required 	   : 'Please enter sms code',
	msg_yearestablish_required : 'Please enter Year of Establishment',
	msg_smscode_invalid		   : 'Please enter correct sms code',
	msg_smscode_isnumeric	   : 'Short SMS code should contain Numeric value.Please enter correct SMS Code',
	msg_comp_invalid 		   : 'Invalid companyname',
	msg_comp_salutation		   : 'Companyname cannot contain salutations',
	msg_comp_maxLength 		   : 'Companyname can not exceed ' + jd_defaults.compname.MAXLENGTH + ' characters',
	msg_comp_maxNum			   : 'Companyname can not contain more than ' + jd_defaults.compname.MAXNUM + ' numeric characters',
	msg_area_maxNum			   : 'Area can not contain more than ' + jd_defaults.area.MAXNUM + ' numeric characters',
	msg_comp_minLength 		   : 'Companyname should contain atleast ' + jd_defaults.compname.MINLENGTH + ' characters',
	msg_comp_splChar 		   : 'Companyname can not contain special characters',
	msg_comp_repChar		   : 'Companyname contains more than 4 repeated alphabets',
	msg_isnumeric 			   : 'Contact number can contain numbers only',
	msg_mobile_maxLength 	   : 'Mobile can not exceed ' + jd_defaults.mobile.MAXLENGTH + ' digits',
	msg_mobile_startchk 	   : 'Mobile can start with 7,8 or 9 only',
	msg_landline_startchk 	   : 'Landline can not start with 0 or 1',
	msg_tele_minLength		   : 'Landline number should contain atleast ' + jd_defaults.tele.MINLENGTH + ' digits',
	msg_tele_maxLength 		   : 'Landline number can not exceed ' + jd_defaults.tele.MAXLENGTH + ' digits',
	msg_tele_duplicate         : 'Two landline numbers found identical',
	msg_email_invalid		   : 'Invalid email',
	msg_pincode_invalid		   : 'Invalid pincode',
	msg_pincode_length		   : 'Pincode should contain 6 digits',
	msg_landline_invalid	   : 'Please enter valid landline',
	msg_tollfree_maxLength 	   : 'Tollfree can not exceed ' + jd_defaults.tollfree.MAXLENGTH + ' digits',
	msg_tollfree_minLength	   : 'Tollfree number should contain atleast ' + jd_defaults.tollfree.MINLENGTH + ' digits',
	msg_tollfree_startchk 	   : 'Toll Free Number should start with 1800 or 1860 or 0008',
	msg_tollfree_isnumeric     : 'Toll Free can contain numbers only',
	msg_yearestablish_invalid  : 'Please enter valid Year of Establishment',
	msg_website_invalid		   : 'Invalid website',
	msg_website_limit		   : 'You can not enter more than two website',
	msg_contactperson_repChar  : 'Contact Person contains more than 4 repeated alphabets',
	msg_contactperson_alpha	   : 'Contact Person should contain only alphabets',
	msg_contactperson_repText  : 'Contact Person contains repeated text',
	msg_mobile_minLength	   : 'Mobile number should contain atleast ' + jd_defaults.mobile.MINLENGTH + ' digits',
	msg_mobile_duplicate       : 'Two mobile numbers found identical',
	msg_tollfree_duplicate     : 'Two tollfree numbers found identical',
	msg_email_duplicate        : 'Two email address found identical',
	msg_fax_invalid			   : 'Enter valid fax number',
	msg_fax_length			   : 'Fax number length can be between 6 to 8',	
	msg_std_length			   : 'Stdcode length can be between 2 to 4',
	msg_area_length			   : 'Area length can be between 3 to 50 characters',
	msg_area_invalid		   : 'Invalid area',
	msg_fax_isnumeric		   : 'Fax number can contain numbers only',
	msg_contactperson_duplicate :  'Two contact person found identical',
	msg_area_repChar		   : 'Area can not contain more than 4 repeated alphabets',
};

$(document).ready (function(){
		
	$('.jd_rule').blur(function () {
		if($(this).attr('validation') != 'business') 
		validateContent(this, false);
	});
	
	$('#button').unbind('click').click(function () {
		validateContent(this, false);
	});
});

function validateData()
{
	var err_msg = new Array();
	var i       = 0;
	
	$('.jd_rule').each(function () {		
		err_msg[i] = validateContent(this, true);
		i += 1;		
	});
	
	err_msg = (uniqueArray(err_msg)).join("\r\n");
	
	return err_msg;	
}
	

function validateContent(fieldObj, validationmode)
{	
	var err_count 	    = 0;
	var err_msg 	    = '';	
	var value           = $.trim($(fieldObj).val());
	var city			= $.trim($('#jd_data_city').val());  // city from hidden value
	var sel_city		= $.trim($('#city').val());  // selected city on bform
	var validation_type = $(fieldObj).attr('validation');
		
	var std		  	=	$.trim($("input[validation='stdcode']").val());
	var mobile_val	=	$.trim($("input[validation='mobile']").val());			
	var tele_val	=	$.trim($("input[validation='tele']").val());
	var tollfree_val=	$.trim($("input[validation='tollfree']").val());
	
	switch (validation_type) 
	{
		case ('compname'):
		
			if (jd_defaults.compname.REQUIRED === true && value == '')
			{
				err_msg += "\r\n" + messages.msg_comp_required;
				if (!validationmode)  alert(messages.msg_comp_required);				
			}
			
			if(value != '')
			{
				var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type+ '&' + validation_type + '=' + value;
				
				err_msg += "\r\n" + callAjx(validation_type, pars, fieldObj, validationmode);
				
				// check for maxlength
				if(value.length > jd_defaults.compname.MAXLENGTH)
				{
					err_msg += "\r\n" + messages.msg_comp_maxLength;
					if (!validationmode) alert(messages.msg_comp_maxLength);
					err_count = err_count + 1;	
				}
				
				// check for minlength
				if(value.length < jd_defaults.compname.MINLENGTH)
				{
					err_msg += "\r\n" + messages.msg_comp_minLength;
					if (!validationmode) alert(messages.msg_comp_minLength);
					err_count = err_count + 1;
				}
				
				// check for special chars, allows alphanumeric and . , - , &
				if(jd_defaults.compname.SPLCHARREGEX.test(value) == false)
				{
					err_msg += "\r\n" + messages.msg_comp_splChar;
					if (!validationmode) alert(messages.msg_comp_splChar);
					err_count = err_count + 1;
				}
				
				// check for repeated chars
				if(jd_defaults.compname.REPEATCHAR === true && checkRepeatChar(value))
				{
					err_msg += "\r\n" + messages.msg_comp_repChar;
					if (!validationmode) alert(messages.msg_comp_repChar);
					err_count = err_count + 1;
				}
				
				// check for max numeric chars
				if(value.replace(/[^0-9]/g,"").length > jd_defaults.compname.MAXNUM)
				{					
					err_msg += "\r\n" + messages.msg_comp_maxNum;
					if (!validationmode) alert(messages.msg_comp_maxNum);
					err_count = err_count + 1;
				}
				
				// check for salutation and web related words
				if(jd_defaults.compname.INVALIDWORDS.test(value))
				{					
					err_msg += "\r\n" + messages.msg_comp_invalid;
					if (!validationmode) alert(messages.msg_comp_invalid);
					err_count = err_count + 1;
				}
				
				// check for salutation 
				if(checkSalutation(value))
				{
					err_msg += "\r\n" + messages.msg_comp_salutation;
					if (!validationmode) alert(messages.msg_comp_salutation);
					err_count = err_count + 1;
				}
			}			
			
		break;
		
		case ('state'):
			if (jd_defaults.state.REQUIRED === true && (value == '' || value == 'Select State'))
			{
				err_msg += "\r\n" + messages.msg_state_required;
				if (!validationmode)  alert(messages.msg_state_required);				
			}
		
		break;
		
		case ('city'):
			if (jd_defaults.city.REQUIRED === true && (value == '' || value == 'Select City')) 
			{
				err_msg += "\r\n" + messages.msg_city_required;
				if (!validationmode)  alert(messages.msg_city_required);				
			}
		
		break;
		
		case ('area'):
		
			if (jd_defaults.area.REQUIRED === true && value == '')
			{
				err_msg += "\r\n" + messages.msg_area_required;
				if (!validationmode)  alert(messages.msg_area_required);				
			}
			if(value != '')
			{
				// check for max numeric chars
				if(value.replace(/[^0-9]/g,"").length > jd_defaults.area.MAXNUM)
				{					
					err_msg += "\r\n" + messages.msg_area_maxNum;
					if (!validationmode) alert(messages.msg_area_maxNum);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.area.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_area_length;
					if (!validationmode) alert(messages.msg_area_length);
					err_count = err_count + 1;
				}
				
				// check for maxlength
				else if(value.length > jd_defaults.area.MAXLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_area_length;
					if (!validationmode) alert(messages.msg_area_length);
					err_count = err_count + 1;
				}
				// check for repeated chars
				if(jd_defaults.area.REPEATCHAR === true && checkRepeatChar(value))
				{
					err_msg += "\r\n" + messages.msg_area_repChar;
					if (!validationmode) alert(messages.msg_area_repChar);
					err_count = err_count + 1;
				}
				
				// check for numeric, do not allow only mumeric in area field
				if(!isNaN(value))
				{				
					err_msg += "\r\n" + messages.msg_area_invalid;
					if (!validationmode) alert(messages.msg_area_invalid);
					err_count = err_count + 1;
				}				
			}
			
		break;
		
		case ('std'):
			if (jd_defaults.std.REQUIRED === true && value == '') 
			{
				err_msg += "\r\n" + messages.msg_std_required;
				if (!validationmode)  alert(messages.msg_std_required);				
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.std.NUMERICREGEX.test(value) == false)
				{				
					err_msg += "\r\n" + messages.msg_std_isnumeric;
					if (!validationmode) alert(messages.msg_std_isnumeric);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.std.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_std_length;
					if (!validationmode) alert(messages.msg_std_length);
					err_count = err_count + 1;
				}
				
				// check for maxlength
				else if(value.length > jd_defaults.std.MAXLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_std_length;
					if (!validationmode) alert(messages.msg_std_length);
					err_count = err_count + 1;
				}
			}
		
		break;
		
		case ('tele'):		
		
			var chkValObj 	= 	checkMultiVals(validation_type);
			
			if (mobile_val == '' && tollfree_val == '' && jd_defaults.tele.REQUIRED === true && chkValObj.val_exists == false)
			{				
				err_msg += "\r\n" + messages.msg_tele_required;
				if (!validationmode) alert(messages.msg_tele_required);
				err_count = err_count + 1;
			}
			
			if (chkValObj.val_duplicate == true) 
			{ 				
				err_msg += "\r\n" + messages.msg_tele_duplicate;
				if (!validationmode) alert(messages.msg_tele_duplicate);
				err_count = err_count + 1;
			}			
			
			if(value != '')
			{
				var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type + '&' + validation_type + '=' + value;
				
				err_msg += "\r\n" + callAjx(validation_type, pars, fieldObj, validationmode);
				
				// check for numeric
				if(jd_defaults.tele.NUMERICREGEX.test(value) == false)
				{				
					err_msg += "\r\n" + messages.msg_isnumeric;
					if (!validationmode) alert(messages.msg_isnumeric);
					err_count = err_count + 1;
				}
				// mobile can not start with 0 or 1
				else if(value.slice(0,1) == 0 || value.slice(0,1) == 1)
				{					
					err_msg += "\r\n" + messages.msg_landline_startchk;
					if (!validationmode) alert(messages.msg_landline_startchk);
					err_count = err_count + 1;
				}
				
				// check for minlength
				else if(value.length < jd_defaults.tele.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_tele_minLength;
					if (!validationmode) alert(messages.msg_tele_minLength);
					err_count = err_count + 1;
				}
				
				// check for maxlength
				else if(value.length > jd_defaults.tele.MAXLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_tele_maxLength;
					if (!validationmode) alert(messages.msg_tele_maxLength);
					err_count = err_count + 1;
				}
				
				else if(std != "" && (std.length+value.length) != jd_defaults.tele.MAXLENGTH)
				{
					err_msg += "\r\n" + messages.msg_landline_invalid;
					if (!validationmode) alert(messages.msg_landline_invalid);
					err_count = err_count + 1;
				}
						
				var seqcheck = checkMultiSequence('tele');				
				if (seqcheck != '')
				{
					err_msg += "\r\n" + seqcheck;
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
				
			}	
						
		break;
		
		case ('mobile'):
			
			var chkValObj 	=	checkMultiVals(validation_type);
			
			if (tele_val == '' && tollfree_val == '' && jd_defaults.mobile.REQUIRED === true && chkValObj.val_exists == false) 
			{				
				err_msg += "\r\n" + messages.msg_mobile_required;
				if (!validationmode) alert(messages.msg_mobile_required);
				err_count = err_count + 1;
			}
			
			if (chkValObj.val_duplicate == true) 
			{
				alert(messages.msg_mobile_duplicate);
				err_count = err_count + 1;
			}
			
			if(value != '')
			{
				var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type+ '&' + validation_type + '=' + value;
				err_msg += "\r\n" + callAjx(validation_type, pars, fieldObj, validationmode);
				
				// check for numeric
				if(jd_defaults.mobile.NUMERICREGEX.test(value) == false)
				{					
					err_msg += "\r\n" + messages.msg_isnumeric;
					if (!validationmode) alert(messages.msg_isnumeric);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.mobile.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_mobile_minLength;
					if (!validationmode) alert(messages.msg_mobile_minLength);
					err_count = err_count + 1;
				}
				// check for maxlength
				else if(value.length > jd_defaults.mobile.MAXLENGTH)
				{				
					err_msg += "\r\n" + messages.msg_mobile_maxLength;
					if (!validationmode) alert(messages.msg_mobile_maxLength);
					err_count = err_count + 1;
				}
				
				// mobile should start with 7, 8 or 9
				else if(value.slice(0,1) != 7 && value.slice(0,1) != 8 && value.slice(0,1) != 9)
				{					
					err_msg += "\r\n" + messages.msg_mobile_startchk;
					if (!validationmode) alert(messages.msg_mobile_startchk);
					err_count = err_count + 1;
				}
				
				var seqcheck = checkMultiSequence('mobile');				
				if (seqcheck != '')
				{
					err_msg += "\r\n" + seqcheck;
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
			}			
		break;
		
		case ('tollfree'):
			var chkValObj = checkMultiVals(validation_type);
				
			if (tele_val == '' && mobile_val == '' && jd_defaults.tollfree.REQUIRED === true && chkValObj.val_exists == false) 
			{				
				err_msg += "\r\n" + messages.msg_tollfree_required;
				if (!validationmode) alert(messages.msg_tollfree_required);
				err_count = err_count + 1;
			}
			
			if (chkValObj.val_duplicate == true) 
			{
				err_msg += "\r\n" + messages.msg_tollfree_duplicate;
				if (!validationmode) alert(messages.msg_tollfree_duplicate);
				err_count = err_count + 1;
			}
				
			if(value != '')
			{
				// check for maxlength
				if(value.length > jd_defaults.tollfree.MAXLENGTH)
				{
					err_msg += "\r\n" + messages.msg_tollfree_maxLength;
					if (!validationmode) alert(messages.msg_tollfree_maxLength);
					err_count = err_count + 1;
				}
				// check for minlength
				else if(value.length < jd_defaults.tollfree.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_tollfree_minLength;
					if (!validationmode) alert(messages.msg_tollfree_minLength);
					err_count = err_count + 1;
				}
				// tollfree should start with 1800 or 1860
				else if(value.slice(0,4)!= '1800' && value.slice(0,4) != '1860' && value.slice(0,4) != '0008')
				{					
					err_msg += "\r\n" + messages.msg_tollfree_startchk;
					if (!validationmode) alert(messages.msg_tollfree_startchk);
					err_count = err_count + 1;
				}
				// check for numeric
				if(jd_defaults.tollfree.NUMERICREGEX.test(value) == false)
				{				
					err_msg += "\r\n" + messages.msg_tollfree_isnumeric;
					if (!validationmode) alert(messages.msg_tollfree_isnumeric);
					err_count = err_count + 1;
				}
				
				var seqcheck = checkMultiSequence('tollfree');				
				if (seqcheck != '')
				{
					err_msg += "\r\n" + seqcheck;
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
			}			
			
		break;
		
		case ('fax'):
			if(jd_defaults.fax.REQUIRED === true && value == '') 
			{
				err_msg += "\r\n" + messages.msg_fax_required;
				if (!validationmode) alert(messages.msg_fax_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.fax.NUMERICREGEX.test(value) == false)
				{				
					err_msg += "\r\n" + messages.msg_fax_isnumeric;
					if (!validationmode) alert(messages.msg_fax_isnumeric);
					err_count = err_count + 1;
				}
				
				// check for minlength
				else if(value.length < jd_defaults.fax.MINLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_fax_length;
					if (!validationmode) alert(messages.msg_fax_length);
					err_count = err_count + 1;
				}
				
				// check for maxlength
				else if(value.length > jd_defaults.fax.MAXLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_fax_length;
					if (!validationmode) alert(messages.msg_fax_length);
					err_count = err_count + 1;
				}
				else if(std != "" && (std.length+value.length) != jd_defaults.fax.VALIDLENGTH)
				{
					err_msg += "\r\n" + messages.msg_fax_invalid;
					if (!validationmode) alert(messages.msg_fax_invalid);
						err_count = err_count + 1;
				}
				
				var seqcheck = checkMultiSequence('fax');				
				if (seqcheck != '')
				{
					err_msg += "\r\n" + seqcheck;
					if (!validationmode) alert(seqcheck);
					err_count = err_count + 1;
				}
			}
		
		break;
			
		case ('email'):
			var chkValObj = checkMultiVals(validation_type);
				
			if(jd_defaults.email.REQUIRED === true && chkValObj.val_exists == false) 
			{				
				err_msg += "\r\n" + messages.msg_email_required;
				if (!validationmode) alert(messages.msg_email_required);
				err_count = err_count + 1;
			}
			
			if(chkValObj.val_duplicate == true) 
			{ 				
				err_msg += "\r\n" + messages.msg_email_duplicate;
				if (!validationmode) alert(messages.msg_email_duplicate);
				err_count = err_count + 1;
			}
			
			if(value != '')
			{
				// check for valid email
				if(jd_defaults.email.PATTERN.test(value) == false)
				{					
					err_msg += "\r\n" + messages.msg_email_invalid;
					if (!validationmode) alert(messages.msg_email_invalid);
					err_count = err_count + 1;
				}
			}
			
			var seqcheck = checkMultiSequence('email');				
			if (seqcheck != '')
			{
				err_msg += "\r\n" + seqcheck;
				if (!validationmode) alert(seqcheck);
				err_count = err_count + 1;
			}

			
		break;
		
		case ('contactperson'):
			
			var chkValObj = checkMultiVals(validation_type);
				
			if(jd_defaults.contactperson.REQUIRED === true && chkValObj.val_exists == false) 
			{				
				err_msg += "\r\n" + messages.msg_contactperson_required;
				if (!validationmode) alert(messages.msg_contactperson_required);
				err_count = err_count + 1;
			}
			
			if(chkValObj.val_duplicate == true) 
			{ 				
				err_msg += "\r\n" + messages.msg_contactperson_duplicate;
				if (!validationmode) alert(messages.msg_contactperson_duplicate);
				err_count = err_count + 1;
			}
			
			if(value != '')
			{
				// check for repeated chars
				if(jd_defaults.contactperson.REPEATCHAR === true && checkRepeatChar(value))
				{				
					err_msg += "\r\n" + messages.msg_contactperson_repChar;
					if (!validationmode) alert(messages.msg_contactperson_repChar);
					err_count = err_count + 1;
				}
				
				// check for repeated text
				if(jd_defaults.contactperson.REPEATTEXT === true && checkRepeatText(value))
				{					
					err_msg += "\r\n" + messages.msg_contactperson_repText;
					if (!validationmode) alert(messages.msg_contactperson_repText);
					err_count = err_count + 1;
				}
				
				// check for alphabets
				if(jd_defaults.contactperson.ALPHAREGEX.test(value) == false)
				{					
					err_msg += "\r\n" + messages.msg_contactperson_alpha;
					if (!validationmode) alert(messages.msg_contactperson_alpha);
					err_count = err_count + 1;
				}
			}
			
		break;
		
		case ('pincode'):
			if(jd_defaults.pincode.REQUIRED === true && value == '')
			{				
				err_msg += "\r\n" + messages.msg_pincode_required;
				if (!validationmode) alert(messages.msg_pincode_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.pincode.NUMERICREGEX.test(value) == false)
				{						
					err_msg += "\r\n" + messages.msg_pincode_invalid;
					if (!validationmode) alert(messages.msg_pincode_invalid);
					err_count = err_count + 1;
				}
				
				// check for maxlength
				if(value.length > jd_defaults.pincode.MAXLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_pincode_length;
					if (!validationmode) alert(messages.msg_pincode_length);
					err_count = err_count + 1;
				}
				
				// check for minlength
				if(value.length < jd_defaults.pincode.MINLENGTH)
				{						
					err_msg += "\r\n" + messages.msg_pincode_length;
					if (!validationmode) alert(messages.msg_pincode_length);
					err_count = err_count + 1;
				}
			}		
				
		break;
		
		case ('smscode') :
		
			if(jd_defaults.smscode.REQUIRED === true && value == '')
			{	
				err_msg += "\r\n" + messages.msg_smscode_required;
				if (!validationmode) alert(messages.msg_smscode_required);				
			}
			if(value != '')
			{
				// check for minlength
				if(value.length < jd_defaults.smscode.MINLENGTH)
				{						
					err_msg += "\r\n" + messages.msg_smscode_invalid;
					if (!validationmode) alert(messages.msg_smscode_invalid);
					err_count = err_count + 1;
				}
				
				// check for numeric
				if(jd_defaults.smscode.NUMERICREGEX.test(value) == false)
				{						
					err_msg += "\r\n" + messages.msg_smscode_isnumeric;
					if (!validationmode) alert(messages.msg_smscode_isnumeric);
					err_count = err_count + 1;
				}
			}
			
		break;
		
		case ('website'):
				
			if(jd_defaults.website.REQUIRED === true && value == '')
			{	
				err_msg += "\r\n" + messages.msg_website_required;
				if (!validationmode) alert(messages.msg_website_required);				
			}
			
			if(value != '')
			{
				var websitvalearr = value.split(',');
				if(websitvalearr.length > 2)
				{
					err_msg += "\r\n" + messages.msg_website_limit;
					if (!validationmode) alert(messages.msg_website_limit);
						err_count = err_count + 1;
				}
				// check for valid website					
				/*for (var i=0; i<websitvalearr.length; i++)
				{
					if(jd_defaults.website.URLREGEX.test(websitvalearr[i]) == false)
					{							
						err_msg += "\r\n" + messages.msg_website_invalid;
						if (!validationmode) alert(messages.msg_website_invalid);
						err_count = err_count + 1;
					}
					/*if(websitvalearr[i].toLowerCase().indexOf('www.') != '0')
					{
						err_msg += "\r\n" + messages.msg_website_invalid;
						if (!validationmode) alert(messages.msg_website_invalid);
						err_count = err_count + 1;
					}
				}*/
			}				
		break;
		
		case ('yearestablish'):
			if(jd_defaults.yearestablish.REQUIRED === true && value == '')
			{				
				err_msg += "\r\n" + messages.msg_yearestablish_required;
				if (!validationmode) alert(messages.msg_yearestablish_required);
				err_count = err_count + 1;
			}
			if(value != '')
			{
				// check for numeric
				if(jd_defaults.yearestablish.NUMERICREGEX.test(value) == false)
				{						
					err_msg += "\r\n" + messages.msg_yearestablish_invalid;
					if (!validationmode) alert(messages.msg_yearestablish_invalid);
					err_count = err_count + 1;
				}
				
				// check for length
				if(value.length != jd_defaults.yearestablish.VALIDLENGTH)
				{					
					err_msg += "\r\n" + messages.msg_yearestablish_invalid;
					if (!validationmode) alert(messages.msg_yearestablish_invalid);
					err_count = err_count + 1;
				}
			}
		break;
				
		case ('business'):
		
			var tele     = '';
			var mobile   = '';
			var tollfree = '';
			var compname = '';
			var pincode  = '';
			
			$('.jd_rule').each(function () {	
				if ($(this).attr('validation') == 'tele') { tele += ((tele != '') ? '|' + $.trim($(this).val()) : $.trim($(this).val())); }
				if ($(this).attr('validation') == 'mobile') { mobile += ((mobile != '') ? '|' + $.trim($(this).val()) : $.trim($(this).val()));  }
				if ($(this).attr('validation') == 'tollfree') { tollfree += ((tollfree != '') ? '|' + $.trim($(this).val()) : $.trim($(this).val()));  }
				if ($(this).attr('validation') == 'compname') compname = $.trim($(this).val());
				if ($(this).attr('validation') == 'pincode') pincode  = $.trim($(this).val());
			});
			
			// check for duplicate number
			var pars = 'city='+ city +'&sel_city=' + sel_city + '&type=' + validation_type + '&tele=' + tele + '&mobile=' + mobile + '&tollfree=' + tollfree + '&compname=' + compname + '&pincode=' + pincode;				
			err_msg += "\r\n" + callAjx(validation_type, pars, fieldObj, validationmode);
			
		break;
		
		default:			
		break;
	}
	
	if (err_count > 0)
	{
		$(fieldObj).val('');
		//if (validationmode == false) $(fieldObj).focus();
	}
	
	return err_msg;
}

function callAjx(validation_type, pars, fieldObj, validationmode)
{
	var xhr;
	var async_mode = ((validationmode == true) ? false : true);
	var msg = '';	
	
	if(xhr && xhr.readyState != 4) xhr.abort();	
	
	
	
	xhr = $.ajax({

		url	:	'http://' + window.location.host + '/jdbox.php',
		type  : 'POST',
		async : async_mode,
		data  : pars,
		success: function(response) {
			if (response != '') 
			{	
				msg = response;
				if (!validationmode)
				{
					if(response.indexOf("Companyname matches with brand name") != 0)
					{
						$(fieldObj).val('');
					}
					//$(fieldObj).focus('');	
					alert(response);				
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

function checkRepeatChar(str) 
{
	str = str.replace(/\s+/g,"_");
	if(/(\S)(\1{4,})/g.test(str)) {
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
	if(/^[a-zA-Z0-9- &()@',.\://\\]*$/.test(str)){
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
		if (!validationmode) alert('Building name contains more than 4 repeated alphabets');
		err_msg += "\r\n" + 'Building name contains more than 4 repeated alphabets'		
	}
	
	if(checkRepeatChar(street))
	{
		if (!validationmode) alert('Street contains more than 4 repeated alphabets');
		err_msg += "\r\n" + 'Street contains more than 4 repeated alphabets'		
	}
	
	if(checkRepeatChar(landmark))
	{
		if (!validationmode) alert('Landmark contains more than 4 repeated alphabets');
		err_msg += "\r\n" + 'Landmark contains more than 4 repeated alphabets'		
	}
	
	if(!checkAddressSplChar(building_name))
	{
		if (!validationmode) alert('Building Name contains special characters');
		err_msg += "\r\n" + 'Building Name contains special characters'	
	}
	
	if(!checkAddressSplChar(landmark))
	{
		if (!validationmode) alert('Landmark contains special characters');
		err_msg += "\r\n" + 'Landmark contains special characters'	
	}
	
	if(!checkAddressSplChar(street))
	{
		if (!validationmode) alert('Street contains special characters');
		err_msg += "\r\n" + 'Street contains special characters'
	}
							
	var addrArr   = Array(building_name, street, landmark, area);
	var dup_error = false;
	
	for (var i=0; i<addrArr.length; i++)
	{
		if (dup_error === true) break;
		
		for (var j=0; j<addrArr.length; j++)
		{
			if(addrArr[i] == addrArr[j] && i != j && addrArr[i] != '' && addrArr[j] != '')
			{
				if (!validationmode) alert('Address lines should not be duplicate');
				err_msg += "\r\n" + 'Address lines should not be duplicate';
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
			url	 : 'http://' + window.location.host + '/jdbox.php',			
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
