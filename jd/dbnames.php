<?php
@session_start();

define("APP_USER","decs_app");
define("APP_PASS","s@myD#@mnl@sy");

define("WEB_INSTANT_USER", "web_instant");
define("WEB_INSTANT_PASS", "!5@uGuST1($7FrEe1Ndi@");
define("WEB_INSTANT_DB", "db_company");	

define("WEB_SPLIT_USER", "web_instant");
define("WEB_SPLIT_PASS", "3nqJ3DVmj;m*$");
define("WEB_SPLIT_DB", "db_split");

define("APP_USER_LIVE","jdbox");
define("APP_PASS_LIVE","jDb0X@#@!");

define("RESELLER_DEV_USER","application");
define("RESELLER_LIVE_USER","reseller");
define("DB_ONLINE1","online_regis1");

define("UAT_USER","uattesting");
define("UAT_PASS",'U@#t3$T!nG');
#define("WEB_SERVICES_API", "192.168.20.102:9001"); already defined in config.php

define("WEB_SERVER", "192.168.20.163");
define("WEB_USER", "decs_app");
define("WEB_PASS", "s@myD#@mnl@sy");
define("WEB_DB", "cms");

define("AHMEDABAD_LCL_TO_IDC_MIGRATION","1");

define("SSO_MODULE_IP", "192.168.20.237");


if(defined('AHMEDABAD_LCL_TO_IDC_MIGRATION') && AHMEDABAD_LCL_TO_IDC_MIGRATION==1 )
{
	define("AHMEDABAD_IRO_API_IP", "192.168.35.227");
	define("AHMEDABAD_DULICATE_IP", "192.168.35.141");
	define("AHMEDABAD_CS_API", "192.168.35.217:81");
	define("AHMEDABAD_JDBOX_API", "192.168.35.237:977");
	define("AHMEDABAD_IRO_IP", "192.168.35.227");
	
}else
{
	define("AHMEDABAD_IRO_API_IP", "172.29.56.227");
	define("AHMEDABAD_DULICATE_IP", "172.29.56.141");
	define("AHMEDABAD_CS_API", "172.29.56.217:81");
	define("AHMEDABAD_JDBOX_API", "172.29.56.237:977");
	define("AHMEDABAD_IRO_IP", "172.29.56.227");
}

define("MUMBAI_IRO_API_IP", "172.29.0.227");
define("DELHI_IRO_API_IP", "172.29.8.227");
define("KOLKATA_IRO_API_IP", "172.29.16.227");
define("BANGALORE_IRO_API_IP", "172.29.26.227");
define("CHENNAI_IRO_API_IP", "172.29.32.227");
define("PUNE_IRO_API_IP", "172.29.40.227");
define("HYDERABAD_IRO_API_IP", "172.29.50.227");

define("REMOTE_CITIES_IRO_API_IP", "192.168.17.227");


define("MUMBAI_DULICATE_IP", "172.29.0.131"); // used for sphinx
define("DELHI_DULICATE_IP", "172.29.8.131");
define("KOLKATA_DULICATE_IP", "172.29.16.141");
define("BANGALORE_DULICATE_IP", "172.29.26.131");
define("CHENNAI_DULICATE_IP", "172.29.32.141");
define("PUNE_DULICATE_IP", "172.29.40.141");
define("HYDERABAD_DULICATE_IP", "172.29.50.141");

define("REMOTE_CITIES_DULICATE_IP", "192.168.17.141");
define("REMOTE_DULICATE_IP",REMOTE_CITIES_DULICATE_IP);


define("MUMBAI_CS_API", "172.29.0.217:81");
define("DELHI_CS_API", "172.29.8.217:81");
define("KOLKATA_CS_API", "172.29.16.217:81");
define("BANGALORE_CS_API", "172.29.26.217:81");
define("CHENNAI_CS_API", "172.29.32.217:81");
define("PUNE_CS_API", "172.29.40.217:81");
define("HYDERABAD_CS_API", "172.29.50.217:81");
define("REMOTE_CITIES_CS_API", "192.168.17.217:81");

define("MUMBAI_TME_URL", "http://172.29.0.237:97");
define("DELHI_TME_URL", "http://172.29.8.237:97");
define("KOLKATA_TME_URL", "http://172.29.16.237:97");
define("BANGALORE_TME_URL", "http://172.29.26.237:97");
define("CHENNAI_TME_URL", "http://172.29.32.237:97");
define("PUNE_TME_URL", "http://172.29.40.237:97");
define("HYDERABAD_TME_URL", "http://172.29.50.237:97");
define("AHMEDABAD_TME_URL", "http://192.168.35.237:97");
define("REMOTE_CITIES_TME_URL","http://192.168.17.237:197");

define("MUMBAI_JDBOX_API", "172.29.0.217:811");
define("DELHI_JDBOX_API", "172.29.8.217:811");
define("KOLKATA_JDBOX_API", "172.29.16.217:811");
define("BANGALORE_JDBOX_API", "172.29.26.217:811");
define("CHENNAI_JDBOX_API", "172.29.32.217:811");
define("PUNE_JDBOX_API", "172.29.40.217:811");
define("HYDERABAD_JDBOX_API", "172.29.50.217:811");

define("REMOTE_CITIES_JDBOX_API", "192.168.20.135:811");



define("MUMBAI_IRO_IP", "172.29.0.227");
define("DELHI_IRO_IP", "172.29.8.227");
define("KOLKATA_IRO_IP", "172.29.16.217");
define("BANGALORE_IRO_IP", "172.29.26.217");
define("CHENNAI_IRO_IP", "172.29.32.217");
define("PUNE_IRO_IP", "172.29.40.217");
define("HYDERABAD_IRO_IP", "172.29.50.217");

define("REMOTE_CITIES_IRO_IP", "192.168.17.217");
define("DB_HOST_JDA",'192.168.6.60');
define("DB_HOST_JDA_SLAVE",'192.168.6.59');
define("DB_USER_JDA",'web_app');
define("DB_PASS_JDA",'!5@uGuST1($7FrEe1Ndi@');
define("DB_NAME_JDA",'db_jda');



define("SSO_IP","192.168.20.237");	

if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || $_REQUEST['UAT_PORT'] == "1811")
{
	
	/*****************************   db_data_correction SERVERS START ***********************************/
	
	$db['mumbai']['data_correction']['master'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['hyderabad']['data_correction']['master'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['kolkata']['data_correction']['master'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['bangalore']['data_correction']['master'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['chennai']['data_correction']['master'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['delhi']['data_correction']['master'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['pune']['data_correction']['master'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['ahmedabad']['data_correction']['master'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['remote']['data_correction']['master'] 		= array('172.29.6.96', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');

	$db['mumbai']['data_correction']['slave'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['hyderabad']['data_correction']['slave'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['kolkata']['data_correction']['slave'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['bangalore']['data_correction']['slave'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['chennai']['data_correction']['slave'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['delhi']['data_correction']['slave'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['pune']['data_correction']['slave'] 		= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['ahmedabad']['data_correction']['slave'] 	= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['remote']['data_correction']['slave'] 		= array('172.29.6.96', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	/*****************************   db_data_correction SERVERS END ***********************************/
	
	
	/*****************************   db_finance SERVERS START ***********************************/
	
	
	/*****************************   db_finance SERVERS START ********************************/
	
	if($_REQUEST['UAT_PORT'] == "1811")
	{
		$db['mumbai']['fin']['master'] 		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['hyderabad']['fin']['master'] 	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['kolkata']['fin']['master'] 	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['bangalore']['fin']['master'] 	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['chennai']['fin']['master'] 	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['delhi']['fin']['master'] 		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['pune']['fin']['master'] 		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['ahmedabad']['fin']['master'] 	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['remote']['fin']['master'] 		= array('172.29.67.215', UAT_USER, UAT_PASS, 'db_finance');

		$db['mumbai']['fin']['slave']  		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['hyderabad']['fin']['slave']  	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['kolkata']['fin']['slave']  	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['bangalore']['fin']['slave']  	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['chennai']['fin']['slave']  	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['delhi']['fin']['slave']  		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['pune']['fin']['slave']  		= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['ahmedabad']['fin']['slave']  	= array('172.29.67.161', UAT_USER, UAT_PASS, 'db_finance');
		$db['remote']['fin']['slave']  		= array('172.29.67.215', UAT_USER, UAT_PASS, 'db_finance');

		/*****************************   db_finance SERVERS END **********************************/
		
		/*****************************   db_iro SERVERS START ***********************************/

		$db['mumbai']['iro']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['hyderabad']['iro']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['kolkata']['iro']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['bangalore']['iro']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['chennai']['iro']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['delhi']['iro']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['pune']['iro']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['ahmedabad']['iro']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['remote']['iro']['master'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'db_iro');

		$db['mumbai']['iro']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['hyderabad']['iro']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['kolkata']['iro']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['bangalore']['iro']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['chennai']['iro']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['delhi']['iro']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['pune']['iro']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['ahmedabad']['iro']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'db_iro');
		$db['remote']['iro']['slave'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'db_iro');

		/*****************************   db_iro SERVERS END ************************************/
		
		
		/*****************************   d_jds SERVERS START ***********************************/

		$db['mumbai']['d_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['hyderabad']['d_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['kolkata']['d_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['bangalore']['d_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['chennai']['d_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['delhi']['d_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['pune']['d_jds']['master'] 			= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['ahmedabad']['d_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['remote']['d_jds']['master'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'd_jds');

		$db['mumbai']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['hyderabad']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['kolkata']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['bangalore']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['chennai']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['delhi']['d_jds']['slave'] 			= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['pune']['d_jds']['slave'] 			= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['ahmedabad']['d_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'd_jds');
		$db['remote']['d_jds']['slave'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'd_jds');

		/*****************************   d_jds SERVERS END *************************************/
		
		/*****************************   tme_jds SERVERS START ***********************************/

		$db['mumbai']['tme_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['hyderabad']['tme_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['kolkata']['tme_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['bangalore']['tme_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['chennai']['tme_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['delhi']['tme_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['pune']['tme_jds']['master'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['ahmedabad']['tme_jds']['master'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['remote']['tme_jds']['master'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'tme_jds');

		$db['mumbai']['tme_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['hyderabad']['tme_jds']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['kolkata']['tme_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['bangalore']['tme_jds']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['chennai']['tme_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['delhi']['tme_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['pune']['tme_jds']['slave'] 		= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['ahmedabad']['tme_jds']['slave'] 	= array('172.29.67.171', UAT_USER, UAT_PASS, 'tme_jds');
		$db['remote']['tme_jds']['slave'] 		= array('192.168.6.96', UAT_USER, UAT_PASS, 'tme_jds');

		/*****************************   tme_jds SERVERS END ***********************************/
		
		/*****************************   idc SERVERS START ***********************************/
	
		$db['mumbai']['idc']['master'] 		= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_mumbai');
		$db['hyderabad']['idc']['master'] 	= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_hyderabad');
		$db['kolkata']['idc']['master'] 	= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_kolkata');
		$db['bangalore']['idc']['master'] 	= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_bangalore');
		$db['chennai']['idc']['master'] 	= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_chennai');
		$db['delhi']['idc']['master'] 		= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_delhi');
		$db['pune']['idc']['master'] 		= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_pune');
		$db['ahmedabad']['idc']['master'] 	= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_ahmedabad');
		$db['remote']['idc']['master'] 		= array('172.29.67.171:3307', UAT_USER, UAT_PASS, 'online_regis_remote_cities');
		/*****************************   idc SERVERS END ***********************************/
	
	}
	else
	{
		$db['mumbai']['fin']['master'] 		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['hyderabad']['fin']['master'] 	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['kolkata']['fin']['master'] 	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['bangalore']['fin']['master'] 	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['chennai']['fin']['master'] 	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['delhi']['fin']['master'] 		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['pune']['fin']['master'] 		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['ahmedabad']['fin']['master'] 	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['remote']['fin']['master'] 		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');

		$db['mumbai']['fin']['slave']  		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['hyderabad']['fin']['slave']  	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['kolkata']['fin']['slave']  	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['bangalore']['fin']['slave']  	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['chennai']['fin']['slave']  	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['delhi']['fin']['slave']  		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['pune']['fin']['slave']  		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['ahmedabad']['fin']['slave']  	= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');
		$db['remote']['fin']['slave']  		= array('172.29.67.215', APP_USER, APP_PASS, 'db_finance');

		$db['mumbai']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['hyderabad']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['kolkata']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['bangalore']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['chennai']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['delhi']['db_budgeting']['master'] 		= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['pune']['db_budgeting']['master'] 		= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['ahmedabad']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['remote']['db_budgeting']['master'] 	= array('172.29.67.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
			
		/*****************************   db_finance SERVERS END **********************************/
		
		/*****************************   db_iro SERVERS START ***********************************/

		$db['mumbai']['iro']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['hyderabad']['iro']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['kolkata']['iro']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['bangalore']['iro']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['chennai']['iro']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['delhi']['iro']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['pune']['iro']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['ahmedabad']['iro']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['remote']['iro']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');

		$db['mumbai']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['hyderabad']['iro']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['kolkata']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['bangalore']['iro']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['chennai']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['delhi']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['pune']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['ahmedabad']['iro']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');
		$db['remote']['iro']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'db_iro');

		/*****************************   db_iro SERVERS END ************************************/
		
		
		/*****************************   d_jds SERVERS START ***********************************/

		$db['mumbai']['d_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['hyderabad']['d_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['kolkata']['d_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['bangalore']['d_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['chennai']['d_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['delhi']['d_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['pune']['d_jds']['master'] 			= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['ahmedabad']['d_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['remote']['d_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');

		$db['mumbai']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['hyderabad']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['kolkata']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['bangalore']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['chennai']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['delhi']['d_jds']['slave'] 			= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['pune']['d_jds']['slave'] 			= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['ahmedabad']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');
		$db['remote']['d_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'd_jds');

		/*****************************   d_jds SERVERS END *************************************/
		
		/*****************************   tme_jds SERVERS START ***********************************/

		$db['mumbai']['tme_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['hyderabad']['tme_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['kolkata']['tme_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['bangalore']['tme_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['chennai']['tme_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['delhi']['tme_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['pune']['tme_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['ahmedabad']['tme_jds']['master'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['remote']['tme_jds']['master'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');

		$db['mumbai']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['hyderabad']['tme_jds']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['kolkata']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['bangalore']['tme_jds']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['chennai']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['delhi']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['pune']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['ahmedabad']['tme_jds']['slave'] 	= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');
		$db['remote']['tme_jds']['slave'] 		= array('172.29.67.213', APP_USER, APP_PASS, 'tme_jds');

		/*****************************   tme_jds SERVERS END ***********************************/
		
		/*****************************   idc SERVERS START ***********************************/
		$db['mumbai']['idc']['master'] 		= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_mumbai');
		$db['hyderabad']['idc']['master'] 	= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_hyderabad');
		$db['kolkata']['idc']['master'] 	= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_kolkata');
		$db['bangalore']['idc']['master'] 	= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_bangalore');
		$db['chennai']['idc']['master'] 	= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_chennai');
		$db['delhi']['idc']['master'] 		= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_delhi');
		$db['pune']['idc']['master'] 		= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_pune');
		$db['ahmedabad']['idc']['master'] 	= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_ahmedabad');
		$db['remote']['idc']['master'] 		= array('192.168.6.52', APP_USER, APP_PASS, 'online_regis_remote_cities');
		/*****************************   idc SERVERS END ***********************************/
	}

	$db['dnc'] 							= array('192.168.6.52', APP_USER_LIVE, APP_PASS_LIVE, 'dnc');
	$db['dcdash'] 						= array('172.29.67.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_dealclosedashboard');
	$db['db_log'] 							= array('192.168.6.52', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis1');
	$db['webedit_vertical']				= array('192.168.6.180', 'application', 's@myD#@mnl@sy', 'db_reservation');
	$db['db_payment'] 					= array('192.168.6.52', APP_USER_LIVE, APP_PASS_LIVE, 'db_payment');
	$db['online_regis'] 				= array('192.168.6.52', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis');
	$db['db_national'] 					= array('192.168.6.52', APP_USER_LIVE, APP_PASS_LIVE, 'db_national_listing');
	$db['web_edit'] 					= array('192.168.6.180', WEB_INSTANT_USER, WEB_INSTANT_PASS, WEB_INSTANT_DB);
	$db['web_split'] 					= array('192.168.6.180', WEB_INSTANT_USER, WEB_INSTANT_PASS, WEB_INSTANT_DB);
	$db['web_split_standby'] 			= array('192.168.6.180', WEB_INSTANT_USER, WEB_INSTANT_PASS, WEB_INSTANT_DB);
	$db['web_split_standby_2'] 			= array('192.168.6.180', WEB_INSTANT_USER, WEB_INSTANT_PASS, WEB_INSTANT_DB);
	
	/*****************************   Contact Us - Web SERVERS START ***********************************/
	$db['website']['master']		=	array(WEB_SERVER, WEB_USER, WEB_PASS, WEB_DB);
	/*****************************   Contact Us - Web SERVERS END ***********************************/

	/*****************************   reseller SERVERS START ***********************************/
	$db['reseller']['master']			= array('192.168.12.163', RESELLER_DEV_USER, APP_PASS, 'reseller_db');
	$db['reseller']['slave']			= array('192.168.12.163', RESELLER_DEV_USER, APP_PASS, 'reseller_db');

	$db['mumbai']['messaging']['master']			= array('172.29.0.33', APP_USER, APP_PASS, 'sms_email_sending');
	
	/*****************************   reseller SERVERS END ***********************************/
	
	/*****************************   corporate contract SERVER START ***********************************/
	$db['corporate']		=	array('172.29.81.34', APP_USER, APP_PASS, 'db_jd_deductioninfo');
	$db['jda']				=	array(DB_HOST_JDA, DB_USER_JDA, DB_PASS_JDA, 'db_jda');
	/*****************************   corporate contract SERVER END ***********************************/
}
else
{
	/*****************************   db_data_correction SERVERS START ***********************************/
	
	if(defined('AHMEDABAD_LCL_TO_IDC_MIGRATION') && AHMEDABAD_LCL_TO_IDC_MIGRATION==1 )
	{
		$db['ahmedabad']['data_correction']['master'] 	= array('192.168.35.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
		$db['ahmedabad']['data_correction']['slave'] 	= array('192.168.35.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
		$db['ahmedabad']['fin']['master'] 	= array('192.168.35.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
		$db['ahmedabad']['fin']['slave']  	= array('192.168.35.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
		$db['ahmedabad']['iro']['master'] 	= array('192.168.35.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
		$db['ahmedabad']['iro']['slave'] 	= array('192.168.35.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
		$db['ahmedabad']['d_jds']['master'] 	= array('192.168.35.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
		$db['ahmedabad']['d_jds']['slave'] 		= array('192.168.35.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
		$db['ahmedabad']['tme_jds']['master'] 	= array('192.168.35.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
		$db['ahmedabad']['tme_jds']['slave'] 	= array('192.168.35.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
		$db['ahmedabad']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_ahmedabad');
		$db['ahmedabad']['db_budgeting']['master'] 	= array('192.168.35.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['ahmedabad']['messaging']['master']			= array('192.168.35.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	}else
	{
		$db['ahmedabad']['data_correction']['master'] 	= array('172.29.56.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
		$db['ahmedabad']['data_correction']['slave'] 	= array('172.29.56.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
		$db['ahmedabad']['fin']['master'] 	= array('172.29.56.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
		$db['ahmedabad']['fin']['slave']  	= array('172.29.56.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
		$db['ahmedabad']['iro']['master'] 	= array('172.29.56.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
		$db['ahmedabad']['iro']['slave'] 	= array('172.29.56.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
		$db['ahmedabad']['d_jds']['master'] 	= array('172.29.56.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
		$db['ahmedabad']['d_jds']['slave'] 		= array('172.29.56.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
		$db['ahmedabad']['tme_jds']['master'] 	= array('172.29.56.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
		$db['ahmedabad']['tme_jds']['slave'] 	= array('172.29.56.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
		$db['ahmedabad']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_ahmedabad');
		$db['ahmedabad']['db_budgeting']['master'] 	= array('172.29.56.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
		$db['ahmedabad']['messaging']['master']			= array('172.29.56.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	}
	
	
	
	
	$db['mumbai']['data_correction']['master'] 		= array('172.29.0.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['hyderabad']['data_correction']['master'] 	= array('172.29.50.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['kolkata']['data_correction']['master'] 	= array('172.29.16.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['bangalore']['data_correction']['master'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['chennai']['data_correction']['master'] 	= array('172.29.32.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['delhi']['data_correction']['master'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['pune']['data_correction']['master'] 		= array('172.29.40.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	
	$db['remote']['data_correction']['master'] 		= array('192.168.17.103', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');

	$db['mumbai']['data_correction']['slave'] 		= array('172.29.0.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['hyderabad']['data_correction']['slave'] 	= array('172.29.50.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['kolkata']['data_correction']['slave'] 		= array('172.29.16.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['bangalore']['data_correction']['slave'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['chennai']['data_correction']['slave'] 		= array('172.29.32.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['delhi']['data_correction']['slave'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	$db['pune']['data_correction']['slave'] 		= array('172.29.40.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	
	$db['remote']['data_correction']['slave'] 		= array('192.168.17.104', APP_USER_LIVE, APP_PASS_LIVE, 'db_data_correction');
	/*****************************   db_data_correction SERVERS END ***********************************/
	
	
	/*****************************   db_finance SERVERS START ***********************************/
	
	$db['mumbai']['fin']['master'] 		= array('172.29.0.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['hyderabad']['fin']['master'] 	= array('172.29.50.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['kolkata']['fin']['master'] 	= array('172.29.16.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['bangalore']['fin']['master'] 	= array('172.29.26.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['chennai']['fin']['master'] 	= array('172.29.32.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['delhi']['fin']['master'] 		= array('172.29.8.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['pune']['fin']['master'] 		= array('172.29.40.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	
	$db['remote']['fin']['master'] 		= array('192.168.17.161', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');

	$db['mumbai']['fin']['slave']  		= array('172.29.0.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['hyderabad']['fin']['slave']  	= array('172.29.50.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['kolkata']['fin']['slave']  	= array('172.29.16.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['bangalore']['fin']['slave']  	= array('172.29.26.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['chennai']['fin']['slave']  	= array('172.29.32.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['delhi']['fin']['slave']  		= array('172.29.8.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	$db['pune']['fin']['slave']  		= array('172.29.40.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	
	$db['remote']['fin']['slave']  		= array('192.168.17.215', APP_USER_LIVE, APP_PASS_LIVE, 'db_finance');
	
	/*****************************   db_finance SERVERS END ***********************************/

	/*****************************   db_iro SERVERS START ***********************************/

	$db['mumbai']['iro']['master'] 		= array('172.29.0.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['hyderabad']['iro']['master'] 	= array('172.29.50.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['kolkata']['iro']['master'] 	= array('172.29.16.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['bangalore']['iro']['master'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['chennai']['iro']['master'] 	= array('172.29.32.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['delhi']['iro']['master'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['pune']['iro']['master'] 		= array('172.29.40.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	
	$db['remote']['iro']['master'] 		= array('192.168.17.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');

	$db['mumbai']['iro']['slave'] 		= array('172.29.0.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['hyderabad']['iro']['slave'] 	= array('172.29.50.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['kolkata']['iro']['slave'] 		= array('172.29.16.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['bangalore']['iro']['slave'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['chennai']['iro']['slave'] 		= array('172.29.32.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['delhi']['iro']['slave'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	$db['pune']['iro']['slave'] 		= array('172.29.40.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	
	$db['remote']['iro']['slave'] 		= array('192.168.17.213', APP_USER_LIVE, APP_PASS_LIVE, 'db_iro');
	
	/*****************************   db_iro SERVERS END ***********************************/

	/*****************************   d_jds SERVERS START ***********************************/

	$db['mumbai']['d_jds']['master'] 		= array('172.29.0.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['hyderabad']['d_jds']['master'] 	= array('172.29.50.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['kolkata']['d_jds']['master'] 		= array('172.29.16.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['bangalore']['d_jds']['master'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['chennai']['d_jds']['master'] 		= array('172.29.32.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['delhi']['d_jds']['master'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['pune']['d_jds']['master'] 			= array('172.29.40.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	
	$db['remote']['d_jds']['master'] 		= array('192.168.17.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');

	$db['mumbai']['d_jds']['slave'] 		= array('172.29.0.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['hyderabad']['d_jds']['slave'] 		= array('172.29.50.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['kolkata']['d_jds']['slave'] 		= array('172.29.16.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['bangalore']['d_jds']['slave'] 		= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['chennai']['d_jds']['slave'] 		= array('172.29.32.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['delhi']['d_jds']['slave'] 			= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	$db['pune']['d_jds']['slave'] 			= array('172.29.40.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	
	$db['remote']['d_jds']['slave'] 		= array('192.168.17.213', APP_USER_LIVE, APP_PASS_LIVE, 'd_jds');
	
	/*****************************   d_jds SERVERS END ***********************************/

	/*****************************   tme_jds SERVERS START ***********************************/

	$db['mumbai']['tme_jds']['master'] 		= array('172.29.0.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['hyderabad']['tme_jds']['master'] 	= array('172.29.50.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['kolkata']['tme_jds']['master'] 	= array('172.29.16.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['bangalore']['tme_jds']['master'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['chennai']['tme_jds']['master'] 	= array('172.29.32.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['delhi']['tme_jds']['master'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['pune']['tme_jds']['master'] 		= array('172.29.40.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	
	$db['remote']['tme_jds']['master'] 		= array('192.168.17.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');

	$db['mumbai']['tme_jds']['slave'] 		= array('172.29.0.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['hyderabad']['tme_jds']['slave'] 	= array('172.29.50.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['kolkata']['tme_jds']['slave'] 		= array('172.29.16.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['bangalore']['tme_jds']['slave'] 	= array('172.29.26.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['chennai']['tme_jds']['slave'] 		= array('172.29.32.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['delhi']['tme_jds']['slave'] 		= array('172.29.8.171', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	$db['pune']['tme_jds']['slave'] 		= array('172.29.40.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');
	
	$db['remote']['tme_jds']['slave'] 		= array('192.168.17.213', APP_USER_LIVE, APP_PASS_LIVE, 'tme_jds');

	/*****************************   tme_jds SERVERS END ***********************************/
	
	/*****************************   idc SERVERS START ***********************************/
	
	$db['mumbai']['idc']['master'] 		= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_mumbai');
	$db['hyderabad']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_hyderabad');
	$db['kolkata']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_kolkata');
	$db['bangalore']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_bangalore');
	$db['chennai']['idc']['master'] 	= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_chennai');
	$db['delhi']['idc']['master'] 		= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_delhi');
	$db['pune']['idc']['master'] 		= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_pune');
	
	$db['remote']['idc']['master'] 		= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis_remote_cities');

	$db['dnc'] 							= array('192.168.1.233', APP_USER_LIVE, APP_PASS_LIVE, 'dnc');
	$db['dcdash'] 						= array('192.168.17.103', APP_USER_LIVE, APP_PASS_LIVE, 'db_dealclosedashboard');
	$db['db_log'] 						= array('192.168.17.103', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis1');
	$db['webedit_vertical']				= array('192.168.20.226', 'application', 's@myD#@mnl@sy', 'db_reservation');
	$db['db_payment'] 					= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'db_payment');
	$db['online_regis'] 				= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'online_regis');
	$db['db_national'] 					= array('192.168.17.233', APP_USER_LIVE, APP_PASS_LIVE, 'db_national_listing');
	$db['web_edit'] 					= array('192.168.6.75', WEB_INSTANT_USER, WEB_INSTANT_PASS, WEB_INSTANT_DB);
	$db['web_split'] 					= array('192.168.12.103', WEB_SPLIT_USER, WEB_SPLIT_PASS, WEB_SPLIT_DB);
	$db['web_split_standby'] 			= array('192.168.12.104', WEB_SPLIT_USER, WEB_SPLIT_PASS, WEB_SPLIT_DB);
	$db['web_split_standby_2'] 			= array('192.168.12.143', WEB_SPLIT_USER, WEB_SPLIT_PASS, WEB_SPLIT_DB);
	/*****************************   idc SERVERS START ***********************************/
	
	
	$db['mumbai']['db_budgeting']['master'] 	= array('172.29.0.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['hyderabad']['db_budgeting']['master'] 	= array('172.29.50.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['kolkata']['db_budgeting']['master'] 	= array('172.29.16.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['bangalore']['db_budgeting']['master'] 	= array('172.29.26.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['chennai']['db_budgeting']['master'] 	= array('172.29.32.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['delhi']['db_budgeting']['master'] 		= array('172.29.8.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	$db['pune']['db_budgeting']['master'] 		= array('172.29.40.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');
	
	$db['remote']['db_budgeting']['master'] 	= array('192.168.17.124', APP_USER_LIVE, APP_PASS_LIVE, 'db_budgeting');

	/*****************************   reseller SERVERS START ***********************************/
	$db['reseller']['master']		=	array('192.168.12.162', RESELLER_LIVE_USER, APP_PASS, 'reseller');
	$db['reseller']['slave']		=	array('192.168.12.163', RESELLER_LIVE_USER, APP_PASS, 'reseller');
	/*****************************   reseller SERVERS END ***********************************/

	/*****************************   corporate contract SERVER START ***********************************/
	$db['corporate']		=	array('172.29.81.34', APP_USER, APP_PASS, 'db_jd_deductioninfo');
	/*****************************   corporate contract SERVER END ***********************************/
	
	/*****************************   Messaging SERVERS START ***********************************/
	$db['mumbai']['messaging']['master']			= array('172.29.0.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['hyderabad']['messaging']['master']			= array('172.29.50.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['kolkata']['messaging']['master']			= array('172.29.16.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['bangalore']['messaging']['master']			= array('172.29.26.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['chennai']['messaging']['master']			= array('172.29.32.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['delhi']['messaging']['master']			    = array('172.29.8.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	$db['pune']['messaging']['master']			    = array('172.29.40.33', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	
	$db['remote']['messaging']['master']			= array('192.168.6.133', RESELLER_DEV_USER, APP_PASS, 'sms_email_sending');
	/*****************************   Messaging SERVERS START ***********************************/
	
	/*****************************   Contact Us - Web SERVERS START ***********************************/
	$db['website']['master']		=	array(WEB_SERVER, WEB_USER, WEB_PASS, WEB_DB);
	/*****************************   Contact Us - Web SERVERS END ***********************************/
		$db['jda']				=	array(DB_HOST_JDA, DB_USER_JDA, DB_PASS_JDA, 'db_jda');
}
