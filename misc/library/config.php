<?php
error_reporting(0);
ini_set("display_errors", 0);
define("INCLUDE_COPY_FORM",1);

if(!defined("DEVLP_APP_IP"))
{define("DEVLP_APP_IP", "172.29.64.64");}

@session_start();
if(!defined('APP_PATH')) 
{
    $new_jailservers = array('192.168.17.217','172.29.0.217', '172.29.8.217', '172.29.16.217', '172.29.26.217', '172.29.32.217', '172.29.40.217', '172.29.50.217', '172.29.56.217');
    $new_jailservers_ip = array(1,0, 8, 16, 26, 32, 40, 50, 56);
    /*if($_SERVER['REMOTE_ADDR'] != "172.29.87.127")
    {
		die("<center><h1>Kindly use csgenionew port. Doing some development on this port.</h1></center>");
	}*/
	if($_SERVER['SERVER_ADDR'] == DEVLP_APP_IP)
	{
		if(stristr(strtolower($_SERVER['SCRIPT_FILENAME']),"/csgenio/"))				/* means vikastuteja.jdsoftware.com/tme */
		{
			if($_SERVER['HTTP_HOST']=='csgenio.jdsoftware.com')						
			{
				include_once($_SERVER['DOCUMENT_ROOT'].'common/config.local.php');		/* INCLUDING LOCAL FILE ONLY IN CS DEVELOPMENT PORT */
			}
			else
			{				
				include_once($_SERVER['DOCUMENT_ROOT'].'/csgenio/common/config.local.php');		/* INCLUDING LOCAL FILE ONLY IN CS DEVELOPMENT PORT */
			}
			
			define("MAIN_CITY_MODULE", "cs");
			//define("REMOTE_CITY_MODULE", "remote_city");
			if($_SERVER['REMOTE_ADDR'] == '172.29.87.1271'|| $_SERVER['REMOTE_ADDR'] == '172.29.106.1426'||$_SERVER['REMOTE_ADDR'] == '172.29.87.721'|| $_SERVER['REMOTE_ADDR'] == '172.29.106.2391' || $_SERVER['REMOTE_ADDR'] == '172.29.87.941')
			{ 
			//|| $_SERVER['REMOTE_ADDR'] == '172.29.64.64' for curl based remote application used in brand name only for development
				define("REMOTE_CITY_MODULE", "remote_city");			
			}
	
			//define("REMOTE_CITY_MODULE", "remote_city");			
		}
		
		define("APP_MODULE", 'cs');
		define("APP_PATH", $_SERVER['DOCUMENT_ROOT']);	
        define("LIVE_APP", 0);
		define("APP_LIVE",0);    /* Use this APP_LIVE instead of LIVE_APP. */
		define("VERTICAL_API","http://niharranjan.jdsoftware.com/web_services/web_services/");
		define("VERTICAL_API_HOTEL","http://karnishmaster.jdsoftware.com/web_services/web_services/");
        define("SRVPOPULATE","srvPrePopData.php");
        define("COURIER_VERTICAL_PARAM","8192");
        define("PROPIC_API","http://jayendrapatel.jdsoftware.com/development/web_services");
	}
	else if( in_array($_SERVER['SERVER_ADDR'], array('192.168.17.117','192.168.17.217','192.168.17.185','192.168.17.227', '192.168.17.237', '192.168.1.86','115.112.246.24','103.20.126.63'))) /* possible ips for remote cities modules */
	{
		define("PROPIC_API","http://192.168.20.102:9001/");
		define("VERTICAL_API","http://192.168.1.97:9001/web_services/");	// Web Services URL
		define("VERTICAL_API_HOTEL","http://192.168.20.102:9001/web_services/");	// Web Services URL
		define("SRVPOPULATE","srvPrePopData.php");
		define("COURIER_VERTICAL_PARAM","8192");
        define("LIVE_APP", 1);
		define("APP_LIVE", 1); 
        switch($_SERVER['SERVER_PORT'])
		{
			case '81' :  
			case '810' :
			if(strpos(trim($_SERVER['PATH_TRANSLATED']), 'production/dataentry_pincodewise/') !== false)
			{
				define("APP_PATH","/var/www/production/dataentry_pincodewise/");/* nginx CS LIVE */

			}else if(stristr($_SERVER['DOCUMENT_ROOT'],'/var/www/production/dataentry_pincodewise/'))
			{
				define("APP_PATH","/var/www/production/dataentry_pincodewise/");/* nginx CS LIVE */

			}else if($_SERVER['SERVER_ADDR'])
			{
				define("APP_PATH", "/var/www/html/dataentry_pincodewise/");            
			}
                                        
            define("REMOTE_CITY_MODULE", "remote_city");
            break;
		}
	}else if( in_array($_SERVER['SERVER_ADDR'], array('192.168.35.217'))) /* possible ips for LOCAL TO IDC MIgration */
	{
		define("PROPIC_API","http://192.168.20.102:9001/");
		define("VERTICAL_API","http://192.168.20.102:9001/web_services/");	// Web Services URL
		define("VERTICAL_API_HOTEL","http://192.168.20.102:9001/web_services/");	// Web Services URL
		define("SRVPOPULATE","srvPrePopData.php");
		define("COURIER_VERTICAL_PARAM","8192");
        define("LIVE_APP", 1);
		define("APP_LIVE", 1); 
        switch($_SERVER['SERVER_PORT'])
		{
			case '81' :  
			case '810' :
			if(strpos(trim($_SERVER['PATH_TRANSLATED']), 'production/dataentry_pincodewise/') !== false)
			{
				define("APP_PATH","/var/www/production/dataentry_pincodewise/");/* nginx CS LIVE */

			}else if(stristr($_SERVER['DOCUMENT_ROOT'],'/var/www/production/dataentry_pincodewise/'))
			{
				define("APP_PATH","/var/www/production/dataentry_pincodewise/");/* nginx CS LIVE */

			}else if($_SERVER['SERVER_ADDR'])
			{
				define("APP_PATH", "/var/www/html/dataentry_pincodewise/");            
			}
            
		}
	}
	else
	{    
		define("PROPIC_API","http://192.168.20.102:9001/");  
		define("VERTICAL_API","http://192.168.1.97:9001/web_services/");	// Web Services URL
		define("VERTICAL_API_HOTEL","http://192.168.20.102:9001/web_services/");	// Web Services URL
		define("SRVPOPULATE","srvPrePopData.php");
		define("COURIER_VERTICAL_PARAM","8192");
        $ip_mid_add = array('0', '8', '16', '26', '32', '40', '50', '56');
        $valid_server = false;
        foreach($ip_mid_add as $each_ip)
        {
            if($_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.217' || $_SERVER['SERVER_ADDR']=='172.29.32.151' ||  $_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.227' || $_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.237')
            {
                $valid_server = true;
			}
        }
        if(!$valid_server)
        {
            $ip_mid_add = array('3');
            foreach($ip_mid_add as $each_ip)
            {
                if($_SERVER['SERVER_ADDR']=='192.168.' . $each_ip . '.217' || $_SERVER['SERVER_ADDR']=='192.168.' . $each_ip . '.227' || $_SERVER['SERVER_ADDR']=='192.168.' . $each_ip . '.237')
                {
                    $valid_server = true;
                    break;
                }
            }        
        }

        if(!$valid_server)
        {
            if(!isset($_SERVER['HTTP_HOST']))
            {
                if(isset($_SERVER['PATH_TRANSLATED']) && strpos(trim($_SERVER['PATH_TRANSLATED']), 'cron/') !== false && count($_SERVER['argv'])>0)
                {
                    /*if(strpos(trim($_SERVER['PATH_TRANSLATED']), '/dataentry_pincodewise/') !== false)
                    {
						define("APP_PATH","/httpdjail/var/www/html/dataentry_pincodewise/");/* CS LIVE */                    
					/*}*/
					define("APP_PATH","/home/imteyazraja/sandbox/csgenio/");
                    define("LIVE_APP", 0);
                    define("APP_LIVE", 0);
                    if($_SERVER['argv'][1]==1 || $_SERVER['argv'][1]==17){
						define("REMOTE_CITY_MODULE", "remote_city");
					}
                }
				else if(isset($_SERVER['PATH_TRANSLATED']) && (strpos(trim($_SERVER['PATH_TRANSLATED']), 'auto_approval_curl.php') !== false || strpos(trim($_SERVER['PATH_TRANSLATED']), 'online_ecs_stop_cron.php') !== false || strpos(trim($_SERVER['PATH_TRANSLATED']), 'single_ecs_clearance_upload.php') !== false) && count($_SERVER['argv'])>0)
				{
					/*if(strpos(trim($_SERVER['PATH_TRANSLATED']), '/dataentry_pincodewise/') !== false)
                    {
						define("APP_PATH","/httpdjail/var/www/html/dataentry_pincodewise/");/* CS LIVE */
					/*}
                    define("LIVE_APP", 1);
                    define("APP_LIVE", 1);*/
					define("APP_PATH","/home/imteyazraja/sandbox/csgenio/");
					define("LIVE_APP", 0);
                    define("APP_LIVE", 0);
				}
				
            }
            if(!defined('APP_PATH'))
            {
                die('Unauthorized server. Please contact software team.');
            }
        }
        else
        {
          define("LIVE_APP", 1);
	      define("APP_LIVE", 1);
      	  if($_SERVER['SERVER_PORT']){
			switch($_SERVER['SERVER_PORT'])
			{
			 case '81':
			 if(stristr($_SERVER['DOCUMENT_ROOT'],'/var/www/production/dataentry_pincodewise/'))
			  define("APP_PATH","/var/www/production/dataentry_pincodewise/"); /* CS LIVE */
			 else
			  define("APP_PATH","/var/www/html/dataentry_pincodewise/"); /* CS LIVE */
			 break;
			 case '810':
			  define("APP_PATH","/var/www/html/dataentry_pincodewise_staging/"); /* CS STAGING 1*/
			 break;
			 case '8100':
			  define("APP_PATH","/var/www/html/dataentry_pincodewise_8100/"); /* CS New Budget Port*/
			 break;
			 case '1810':
			  define("APP_PATH","/var/www/html/dataentry_pincodewise_dev_staging/"); /* CS STAGING 1*/
			 break;
			 case '1811':
			  define("APP_PATH","/var/www/html/dataentry_pincodewise_uat_staging//"); /* CS STAGING 1*/
			 break;
			 default:
			 die('Unauthorized server. Please contact software team.');
			}
	     }
	   }
    }
           
	if(!defined('APP_PATH'))
	{
	  die('Unauthorized server. Please contact software team.');
	 }
	define("APP_URL",  'http://' . $_SERVER['HTTP_HOST']);
	
	define("IMG_URL", APP_URL);
	define("CSS_URL", APP_URL);
	define("JS_URL", APP_URL);
	
	define("COMMON_JS_URL", APP_URL . "/common/js/");
	define("COMMON_CSS_URL", APP_URL . "/common/css/");
	define("IMG_COMMON_URL", APP_URL . "/images/common/");
	
	if(!defined("REMOTE_CITY_MODULE"))
	{			
		define("DATA_CITY",$_SESSION['s_deptCity']);
	}
	else
	{
		if(isset($_SESSION['remote_city']))
		{
			define("DATA_CITY",$_SESSION['remote_city']);
		}
	}
	
	define("LOGIN_CITY", $_SESSION['s_deptCity']);
	
	define("TITLE","JUST DIAL");
	define("APP_MODULE", 'cs');
    define("MAIN_CITY_MODULE", "cs");
	
    /*VIRTUAL TAG URL ADDRESS*/
    define("MUMBAI_TAG_IP", "172.29.66.208:81");
	define("HYDERABAD_TAG_IP", "172.29.50.208:81");
	define("KOLKATA_TAG_IP", "172.29.16.208:81");
	define("BANGALORE_TAG_IP", "172.29.26.208:81");
	define("CHENNAI_TAG_IP", "172.29.32.208:81");
	//define("DELHI_TAG_IP", "121.247.181.155:81"); //(not working date 7 march 2012)
    define("DELHI_TAG_IP", "122.176.103.145:81");
	define("PUNE_TAG_IP", "172.29.40.208:81");
	define("AHMEDABAD_TAG_IP", "172.29.56.208:81");

    /*TECHINFO APPLY CITY ARRAY*/
    $techinfo_app_arr= array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI');
    
    define("SSO_URL","accounts.justdial.com");
	define("SSO_LOGOUT","http://".SSO_URL."/logout/logoutServiceAuth");
	//define("GNO_URL","genio.in");
	define("GNO_URL","imteyazraja.jdsoftware.com/megenio");

	$serverAddr	=	$_SERVER['SERVER_ADDR'];
	$expServerAddr	=	explode('.',$serverAddr);
	switch($expServerAddr[2]) {
		case 0:
		define("SERVICE_NAME","decs_mum");
		define("SERVICE_PARAM","3");
		define("CARDDET_API","http://172.29.0.217:8001/");
		break;
		case 8:
		define("SERVICE_NAME","decs_del");
		define("SERVICE_PARAM","16");
		define("CARDDET_API","http://172.29.8.217:8001/");
		break;
		case 16:
		define("SERVICE_NAME","decs_kol");
		define("SERVICE_PARAM","17");
		define("CARDDET_API","http://172.29.16.217:8001/");
		break;
		case 26:
		define("SERVICE_NAME","decs_bang");
		define("SERVICE_PARAM","18");
		define("CARDDET_API","http://172.29.26.217:8001/");
		break;
		case 32:
		define("SERVICE_NAME","decs_chn");
		define("SERVICE_PARAM","19");
		define("CARDDET_API","http://172.29.32.217:8001/");
		break;
		case 40:
		define("SERVICE_NAME","decs_pun");
		define("SERVICE_PARAM","20");
		define("CARDDET_API","http://172.29.40.217:8001/");
		break;
		case 50:
		define("SERVICE_NAME","decs_hyd");
		define("SERVICE_PARAM","21");
		define("CARDDET_API","http://172.29.50.217:8001/");
		break;
		case 56:
		define("SERVICE_NAME","decs_ahd");
		define("SERVICE_PARAM","22");
		define("CARDDET_API","http://172.29.56.217:8001/");
		break;
		case 35:
		define("SERVICE_NAME","decs_ahd");
		define("SERVICE_PARAM","22");
		define("CARDDET_API","http://192.168.35.217:8001/");
		break;
		case 1:
		define("SERVICE_NAME","decs_rem");
		define("SERVICE_PARAM","32");
		define("CARDDET_API","http://172.29.0.217:8001/");
		break;
		case 17:
		define("SERVICE_NAME","decs_rem");
		define("SERVICE_PARAM","32");
		define("CARDDET_API","http://172.29.0.217:8001/");
		break;
		case 64:
		if(defined('REMOTE_CITY_MODULE'))
		{
			define("SERVICE_PARAM","32");
		}else
		{
			define("SERVICE_PARAM","3");	
		}
		define("SERVICE_NAME","decs_mum");

		define("CARDDET_API","http://meetabilimoria.jdsoftware.com/services/");
		break;
	}
	define("IRO_JD_SRCH_IP_MAP","http://192.168.17.227:84/");
}
?>
