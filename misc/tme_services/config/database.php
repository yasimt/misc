<?php
define('DB_TYPE', 'mysqli');
GLOBAL $parseConf;

# PART ADDED BECAUSE THE SAME FILE IS USED FOR CONSTANT CONFIG OF OTHER MODULES
$serverAddr	=	$_SERVER['SERVER_ADDR'];
$serverPointer	=	explode('.',$serverAddr);
if($serverPointer[2]	==	'64') {
	$parseConf	=	parse_ini_file(dirname(__FILE__) . '/../public/files/developmentip.conf',1);
} else {
	$parseConf	=	parse_ini_file(dirname(__FILE__) . '/../public/files/productionip.conf',1);
}
$mongo_user_ips_arr = array('172.29.87.1271','172.29.87.122');
if($parseConf['servicefinder']['live'] == 0) { 
	# DEVELOPMENT ENVIORONMENT SETTINGS
	# LOCAL SERVER SETTINGS
	if($parseConf['servicefinder']['remotecity'] == 0) {
		define('DB_HOST_LOC', '172.29.67.213');
		define('DB_HOST_LOC_SLAVE', '172.29.67.213');
	} else {
		define('DB_HOST_LOC', '192.168.6.96');
		define('DB_HOST_LOC_SLAVE', '192.168.6.96');
	}
	define('DB_NAME_LOC', 'd_jds');
	define('DB_NAME_TME', 'tme_jds');
	define('DB_NAME_IRO', 'db_iro');
	define('DB_NAME_ALLOC', 'allocation');
	define('DB_USER_LOCAL', 'application');
	define('DB_PASS_LOCAL', 's@myD#@mnl@sy');
	
	# FINANCE SERVER SETTINGS
	define('DB_HOST_FIN', '172.29.67.215');
	define('DB_HOST_FIN_SLAVE', '172.29.67.215');
	define('DB_HOST_FIN_BUDGET', '172.29.67.215');
	define('DB_NAME_FIN', 'db_finance');
	define('DB_NAME_FIN_BUDGET', 'db_budgeting');
	define('DB_USER_FIN', 'application');
	define('DB_PASS_FIN', 's@myD#@mnl@sy');
	
	# IDC 233/6.52 SERVER SETTINGS
	define('DB_HOST_IDC', '192.168.6.52');
	define('DB_NAME_IDC_LOCAL', 'online_regis_mumbai');
	define('DB_NAME_IDC_DIALER', 'db_dialer');
	define('DB_NAME_IDC_LOGIN', 'login_details');
	define('DB_NAME_IDC_ONLINE', 'online_regis');
	define('DB_NAME_NATIONAL','db_national_listing');
	define('DB_USER_IDC', 'jddb1');
	define('DB_PASS_IDC', 'w1sem@n0ut');
	define('DB_HOST_SMS', '172.29.0.33');
	
	define('DB_HOST_DATA_CORRECTION', '172.29.67.213');
	define('DB_NAME_DATA_CORRECTION','db_data_correction');
	define("MONGOUSER", 1);
	
	## City Wise Constant - Development
	
	
	define('DB_HOST_LOC_MUMBAI', '172.29.67.213');
	define('DB_HOST_DATA_CORRECTION_MUMBAI', '172.29.67.213');
	define('DB_HOST_FIN_MUMBAI', '172.29.67.215');
	define('DB_HOST_FIN_BUDGET_MUMBAI', '172.29.67.215');
	define('DB_HOST_SMS_MUMBAI', '172.29.0.33');
	define('DB_NAME_IDC_MUMBAI', 'online_regis_mumbai');
	define('DB_HOST_LOC_SLAVE_MUMBAI', '172.29.67.213');
	define('DB_HOST_FIN_SLAVE_MUMBAI', '172.29.67.215');
	
	
	
} else { 
	# PRODUCTION ENVIORONMENT SETTINGS
	# LOCAL SERVER SETTINGS
	if(!defined('SERVER_PARAM')) {
		define('SERVER_PARAM',$parseConf['servicefinder']['serviceparam']);
	}
	
	if(!defined('SERVER_CITY')) {
		define('SERVER_CITY',$parseConf['servicefinder']['servicecity']);
	}
	if($parseConf['servicefinder']['remotecity'] == 0) {
		
		
		
		if(SERVER_PARAM==35)
		{
			define('DB_HOST_LOC', '192.168.35.171');
			define('DB_HOST_LOC_SLAVE', '192.168.35.213');
			define('DB_HOST_DATA_CORRECTION', '192.168.35.171');
			define('DB_HOST_DATA_CORRECTION_SLAVE', '192.168.35.213');
			define('DB_HOST_FIN_SLAVE', '192.168.35.215');
			define('DB_HOST_FIN', '192.168.35.161');
			define('DB_HOST_FIN_BUDGET', '192.168.35.124');
			define('DB_HOST_SMS', '192.168.35.33');
		}
		else
		{
			if(SERVER_PARAM==0){
				define('DB_HOST_LOC_SLAVE', '172.29.'.SERVER_PARAM.'.171');
				define('DB_HOST_DATA_CORRECTION_SLAVE', '172.29.'.SERVER_PARAM.'.171');
			}else{
				define('DB_HOST_LOC_SLAVE', '172.29.'.SERVER_PARAM.'.213');
				define('DB_HOST_DATA_CORRECTION_SLAVE', '172.29.'.SERVER_PARAM.'.213');
			}
			define('DB_HOST_LOC', '172.29.'.SERVER_PARAM.'.171');
			define('DB_HOST_DATA_CORRECTION', '172.29.'.SERVER_PARAM.'.171');
			define('DB_HOST_FIN_SLAVE', '172.29.'.SERVER_PARAM.'.215');
			define('DB_HOST_FIN', '172.29.'.SERVER_PARAM.'.161');
			define('DB_HOST_FIN_BUDGET', '172.29.'.SERVER_PARAM.'.124');
			define('DB_HOST_SMS', '172.29.'.SERVER_PARAM.'.33');
		}
		
		
		
	} else {
		define('DB_HOST_LOC', '192.168.17.171');
		define('DB_HOST_LOC_SLAVE', '192.168.17.213');
		define('DB_HOST_FIN', '192.168.17.161');
		define('DB_HOST_FIN_SLAVE', '192.168.17.215');
		define('DB_HOST_FIN_BUDGET', '192.168.17.124');
		define('DB_HOST_SMS', '192.168.6.133');
		define('DB_HOST_DATA_CORRECTION', '192.168.17.103');
		define('DB_HOST_DATA_CORRECTION_SLAVE', '192.168.17.104');
		
	}
	define("MONGOUSER", 1);
	define('DB_NAME_LOC', 'd_jds');
	define('DB_NAME_TME', 'tme_jds');
	define('DB_NAME_IRO', 'db_iro');
	define('DB_NAME_ALLOC', 'allocation');
	define('DB_USER_LOCAL', 'application');
	define('DB_PASS_LOCAL', 's@myD#@mnl@sy');
	
	# FINANCE SERVER SETTINGS
	define('DB_NAME_FIN', 'db_finance');
	define('DB_USER_FIN', 'application');
	define('DB_NAME_FIN_BUDGET', 'db_budgeting');
	define('DB_PASS_FIN', 's@myD#@mnl@sy');
	
	# IDC 233/6.52 SERVER SETTINGS
	define('DB_HOST_IDC', '192.168.17.233');
	define('DB_NAME_IDC_LOCAL', 'online_regis_'.SERVER_CITY);
	define('DB_NAME_IDC_DIALER', 'db_dialer');
	define('DB_NAME_IDC_LOGIN', 'login_details');
	define('DB_NAME_IDC_ONLINE', 'online_regis');
	define('DB_NAME_NATIONAL','db_national_listing');
	define('DB_NAME_DATA_CORRECTION','db_data_correction');
	define('DB_USER_IDC', 'meappuser');
	define('DB_PASS_IDC', 's@myD#@mnl@sy');
	
	
	
	## City Wise Constant - Live
	
	
	define('DB_HOST_LOC_MUMBAI', '172.29.0.171');
	define('DB_HOST_LOC_DELHI', '172.29.8.171');
	define('DB_HOST_LOC_KOLKATA', '172.29.16.171');
	define('DB_HOST_LOC_BANGALORE', '172.29.26.171');
	define('DB_HOST_LOC_CHENNAI', '172.29.32.171');
	define('DB_HOST_LOC_PUNE', '172.29.40.171');
	define('DB_HOST_LOC_HYDERABAD', '172.29.50.171');
	define('DB_HOST_LOC_AHMEDABAD', '172.29.56.171');
	define('DB_HOST_LOC_REMOTE', '192.168.17.171');
	
	
	
	define('DB_HOST_DATA_CORRECTION_MUMBAI', '172.29.0.171');
	define('DB_HOST_DATA_CORRECTION_DELHI', '172.29.8.171');
	define('DB_HOST_DATA_CORRECTION_KOLKATA', '172.29.16.171');
	define('DB_HOST_DATA_CORRECTION_BANGALORE', '172.29.26.171');
	define('DB_HOST_DATA_CORRECTION_CHENNAI', '172.29.32.171');
	define('DB_HOST_DATA_CORRECTION_PUNE', '172.29.40.171');
	define('DB_HOST_DATA_CORRECTION_HYDERABAD', '172.29.50.171');
	define('DB_HOST_DATA_CORRECTION_AHMEDABAD', '172.29.56.171');
	define('DB_HOST_DATA_CORRECTION_REMOTE', '192.168.17.103');
	
	
	
	
	define('DB_HOST_FIN_MUMBAI', '172.29.0.161');
	define('DB_HOST_FIN_DELHI', '172.29.8.161');
	define('DB_HOST_FIN_KOLKATA', '172.29.16.161');
	define('DB_HOST_FIN_BANGALORE', '172.29.26.161');
	define('DB_HOST_FIN_CHENNAI', '172.29.32.161');
	define('DB_HOST_FIN_PUNE', '172.29.40.161');
	define('DB_HOST_FIN_HYDERABAD', '172.29.50.161');
	define('DB_HOST_FIN_AHMEDABAD', '172.29.56.161');
	define('DB_HOST_FIN_REMOTE', '192.168.17.161');
	
	define('DB_HOST_FIN_BUDGET_MUMBAI', '172.29.0.124');
	define('DB_HOST_FIN_BUDGET_DELHI', '172.29.8.124');
	define('DB_HOST_FIN_BUDGET_KOLKATA', '172.29.16.124');
	define('DB_HOST_FIN_BUDGET_BANGALORE', '172.29.26.124');
	define('DB_HOST_FIN_BUDGET_CHENNAI', '172.29.32.124');
	define('DB_HOST_FIN_BUDGET_PUNE', '172.29.40.124');
	define('DB_HOST_FIN_BUDGET_HYDERABAD', '172.29.50.124');
	define('DB_HOST_FIN_BUDGET_AHMEDABAD', '172.29.56.124');
	define('DB_HOST_FIN_BUDGET_REMOTE', '192.168.17.124');
	
	
	define('DB_HOST_SMS_MUMBAI', '172.29.0.33');
	define('DB_HOST_SMS_DELHI', '172.29.8.33');
	define('DB_HOST_SMS_KOLKATA', '172.29.16.33');
	define('DB_HOST_SMS_BANGALORE', '172.29.26.33');
	define('DB_HOST_SMS_CHENNAI', '172.29.32.33');
	define('DB_HOST_SMS_PUNE', '172.29.40.33');
	define('DB_HOST_SMS_HYDERABAD', '172.29.50.33');
	define('DB_HOST_SMS_AHMEDABAD', '172.29.56.33');
	define('DB_HOST_SMS_REMOTE', '192.168.6.133');
	
	
	
	define('DB_NAME_IDC_MUMBAI', 'online_regis_mumbai');
	define('DB_NAME_IDC_DELHI', 'online_regis_delhi');
	define('DB_NAME_IDC_KOLKATA', 'online_regis_kolkata');
	define('DB_NAME_IDC_BANGALORE', 'online_regis_bangalore');
	define('DB_NAME_IDC_CHENNAI', 'online_regis_chennai');
	define('DB_NAME_IDC_PUNE', 'online_regis_pune');
	define('DB_NAME_IDC_HYDERABAD', 'online_regis_hyderabad');
	define('DB_NAME_IDC_AHMEDABAD', 'online_regis_ahmedabad');
	define('DB_NAME_IDC_REMOTE', 'online_regis_remote_cities');
	
	
	
	define('DB_HOST_LOC_SLAVE_MUMBAI', '172.29.0.213');
	define('DB_HOST_LOC_SLAVE_DELHI', '172.29.8.213');
	define('DB_HOST_LOC_SLAVE_KOLKATA', '172.29.16.213');
	define('DB_HOST_LOC_SLAVE_BANGALORE', '172.29.26.213');
	define('DB_HOST_LOC_SLAVE_CHENNAI', '172.29.32.213');
	define('DB_HOST_LOC_SLAVE_PUNE', '172.29.40.213');
	define('DB_HOST_LOC_SLAVE_HYDERABAD', '172.29.50.213');
	define('DB_HOST_LOC_SLAVE_AHMEDABAD', '172.29.56.171');
	define('DB_HOST_LOC_SLAVE_REMOTE', '192.168.17.213');
	
	
	define('DB_HOST_FIN_SLAVE_MUMBAI', '172.29.0.215');
	define('DB_HOST_FIN_SLAVE_DELHI', '172.29.8.215');
	define('DB_HOST_FIN_SLAVE_KOLKATA', '172.29.16.215');
	define('DB_HOST_FIN_SLAVE_BANGALORE', '172.29.26.215');
	define('DB_HOST_FIN_SLAVE_CHENNAI', '172.29.32.161');
	define('DB_HOST_FIN_SLAVE_PUNE', '172.29.40.215');
	define('DB_HOST_FIN_SLAVE_HYDERABAD', '172.29.50.215');
	define('DB_HOST_FIN_SLAVE_AHMEDABAD', '172.29.56.161');
	define('DB_HOST_FIN_SLAVE_REMOTE', '192.168.17.215');
	
	
}

// SMS Server
//define('DB_HOST_SMS', '192.168.6.131');
define('DB_NAME_SMS', 'sms_email_sending');
define('DB_USER_SMS', 'application');
define('DB_PASS_SMS', 's@myD#@mnl@sy');
define('TBL_SMSEMAILSETTINGS', 'SmsEmailSettings');
