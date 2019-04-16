<?php
define("DOMAIN", "http://" . $_SERVER['HTTP_HOST'] . "/");
define("DOMAIN_MODULE", "http://" . $_SERVER['HTTP_HOST']);
define("DOMAIN_MODULE_PUBLIC", "http://115.112.246.34:100/sso");
define("DOMAIN_MODULE_HARD","http://192.168.20.237");	//Floating IP for SSO master is 192.168.6.222, standby is 192.168.6.221
define("HRMODULE","http://192.168.20.237/hrmodule");
define("DOC_ROOT", dirname(__FILE__) . '/../');
define("LOGS", DOC_ROOT . 'log/');
define("FILES", DOC_ROOT . 'public/files/');
define("FILE_PATH",DOMAIN.'public/files');
//define("USER_AGENT", $_SERVER['HTTP_USER_AGENT']);
define("PUBLIC_PATH",  DOMAIN.'public');
define("IMG_PATH",  DOMAIN.'public/images');
define("JS_PATH",  DOMAIN.'public/js');
define("CSS_PATH",  DOMAIN.'public/css');
define('WEB_SERVICES','http://192.168.20.102:9001/web_services/');
define('MODULE',$parseConf['servicefinder']['module']);
define("GNO_URL","192.168.20.17");
define("SSOINFO","http://192.168.20.237:8080");
if($parseConf['servicefinder']['live'] == 0) {
	# DEVELOPMENT PATHS
	define("SERVICE_IP","http://" . $_SERVER['HTTP_HOST'] . "/tmegenio/tme_services");
	define("DECS_CITY","http://" . $_SERVER['HTTP_HOST'] . "/csgenio");
	define("IRO_CITY","http://172.29.0.227");
	define("DECS_TME","http://" . $_SERVER['HTTP_HOST'] . "/tmegenio");
	define("DC_URL","http://nareshbhati.jdsoftware.com/tmegenio");
	define("COMPARE_URL","http://hemavathiv.jdsoftware.com/tmegenio/tme_services/");
	define("JDBOX_API","http://hirenmistry.jdsoftware.com/jdbox/");
	define("JDBOX_API2","http://hirenmistry.jdsoftware.com/jdbox/");
    define('LOG_URL','http://praveenchaudhary.blrsoftware.com/logsLive/');
    define("CS_REMOTE_APP_IP","http://imteyazraja.jdsoftware.com/csgenio");
    define("TME_REMOTE_APP_IP","http://imteyazraja.jdsoftware.com/tmegenio");
    define("KNOWLEDGE_API","http://192.168.22.103:810/SSO/uimage/");
    define("DIALER_DASH_API","http://192.168.22.103:810/");
    define("MONGO_URL","172.29.0.186");
    define("KNOWLEDGE_APICALL","http://192.168.22.103:810/SSO/uimage/");
    define("COMPANY_DATA_URL","http://172.29.86.26:4000/api/comp");
} else {
	# PRODUCTION PATHS
	define("MONGO_URL","192.168.6.238");
	define("KNOWLEDGE_APICALL","http://192.168.20.237/uimage/");
	define("KNOWLEDGE_API","http://192.168.20.237/uimage/");
	//define("DIALER_DASH_API","http://192.168.22.103/");
	define("DIALER_DASH_API","http://192.168.20.17/");
	define("CS_REMOTE_APP_IP", "192.168.17.217:81");
	if(strpos(trim($_SERVER['SERVER_ADDR']), '.227')){
		define("TME_REMOTE_APP_IP","192.168.17.227:197");
	}else if(strpos(trim($_SERVER['SERVER_ADDR']), '.217')){
		define("TME_REMOTE_APP_IP","192.168.17.217:197");
	}else{
		define("TME_REMOTE_APP_IP","192.168.17.237:197");
	}
	define('LOG_URL','http://192.168.17.109/logs/');
	if($parseConf['servicefinder']['remotecity'] == 1) 
	{
		define("SERVICE_IP","http://" . $_SERVER['HTTP_HOST'] . "/tme_services");
		define("DECS_CITY","http://" . "192.168.17.217:81");
		define("DECS_TME","http://" . $_SERVER['HTTP_HOST']);
		define("IRO_CITY","http://" . "192.168.17.217");
		define("DC_API","http://" . $_SERVER['HTTP_HOST']);
		define("DC_URL","http://". $_SERVER['HTTP_HOST']);
		define("COMPARE_URL","http://". $_SERVER['HTTP_HOST']."/tme_services");
		define("JDBOX_API","http://192.168.20.135:811");
		define("SAVEASFREELISTING","http://192.168.17.117:811/");
		define("REMOTEZONEFLAG",1);
	}
	else 
	{
 		define("SERVICE_IP","http://" . $_SERVER['HTTP_HOST'] . "/tme_services");
		define("DECS_TME","http://" . $_SERVER['HTTP_HOST']);
		
		define("DC_API","http://" . $_SERVER['HTTP_HOST']);
		define("DC_URL","http://". $_SERVER['HTTP_HOST']);
		define("COMPARE_URL","http://". $_SERVER['HTTP_HOST']."/tme_services");

		
		if($parseConf['servicefinder']['serviceparam'] == 35){
			
			define("IRO_CITY","http://" . "192.168.35.227");
			define("DECS_CITY","http://" . "192.168.35.217:81");
			define("JDBOX_API","http://192.168.35.237:977/");
			define("SAVEASFREELISTING","http://192.168.35.237:977/");
			
		}else{
			define("IRO_CITY","http://" . "172.29.".$parseConf['servicefinder']['serviceparam'].".227");
			define("DECS_CITY","http://" . "172.29.".$parseConf['servicefinder']['serviceparam'].".217:81");
			define("JDBOX_API","http://172.29.".$parseConf['servicefinder']['serviceparam'].".237:977/");
			define("SAVEASFREELISTING","http://172.29.".$parseConf['servicefinder']['serviceparam'].".237:977/");
		}
		
		
		define("REMOTEZONEFLAG",0);
	}
	define("COMPANY_DATA_URL","http://192.168.20.133/api/comp");
}
define("DC_API_NEW","192.168.1.141:100/");
define("JDOMNIDEMO","http://www.jdomni.com/marketplace/static/php/web/service_api.php");
define("JDOMNIYOW","http://www.jdomni.com/marketplace/static/php/web/common_api.php");
