<?php
require_once("dbnames.php");
require_once("db.class.php");
require_once("mongo.class.php");
require_once("category_api_class.php");
require_once("company_details_class.php");

define('MATCHCAT','Companyname matches with category name, Please enter the proper Company Name');
define('MATCHSYN','Companyname matches with category synonym');
define('MATCHBRAND',"Companyname matches with brand name. Click 'OK' to proceed ahead");
define('BLOCKNUM','Blocked number');
define('INVALID','Invalid companyname');
define('DUPNAME','Duplicate number');
define('WEB_SERVICES_API', '192.168.20.102:9001');
define("SMS_EMAIL_LB_IP", "192.168.20.116");

if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))
{
	define('LOG_PATH', $_SERVER['DOCUMENT_ROOT']);
	define("GNO_URL","192.168.20.17"); // defining genio IP
	define("LIVE_APP", 1);
	define("VIRTUAL_API","192.168.12.138:81/scripts/check_virtual.php");
	define("MUMBAI_API_FIN_SERVICE"     , "http://172.29.0.217:81/api/");
	define("DELHI_API_FIN_SERVICE"      , "http://172.29.8.217:81/api/");
	define("KOLKATA_API_FIN_SERVICE"    , "http://172.29.16.217:81/api/");
	define("BANGALORE_API_FIN_SERVICE"  , "http://172.29.26.217:81/api/");
	define("CHENNAI_API_FIN_SERVICE"    , "http://172.29.32.217:81/api/");
	define("PUNE_API_FIN_SERVICE"       , "http://172.29.40.217:81/api/");
	define("HYDERABAD_API_FIN_SERVICE"  , "http://172.29.50.217:81/api/");
	
	define("REMOTE_API_FIN_SERVICE"     , "http://192.168.17.217:81/api/");
	define("MUMBAI_CS_JDBOX_URL", "172.29.0.217:811/");
	define("DELHI_CS_JDBOX_URL", "172.29.8.217:811/");
	define("KOLKATA_CS_JDBOX_URL", "172.29.16.217:811/");
	define("BANGALORE_CS_JDBOX_URL", "172.29.26.217:811/");
	define("CHENNAI_CS_JDBOX_URL", "172.29.32.217:811/");
	define("PUNE_CS_JDBOX_URL", "172.29.40.217:811/");
	define("HYDERABAD_CS_JDBOX_URL", "172.29.50.217:811/");
	
	define("REMOTE_CS_JDBOX_URL", "192.168.20.135:811/");
	
	if(defined('AHMEDABAD_LCL_TO_IDC_MIGRATION') && AHMEDABAD_LCL_TO_IDC_MIGRATION==1 )
	{
		define("AHMEDABAD_API_FIN_SERVICE"  , "http://192.168.35.217:81/api/");
		define("AHMEDABAD_CS_JDBOX_URL", "192.168.35.217:811/");
		
	}else
	{
		define("AHMEDABAD_API_FIN_SERVICE"  , "http://172.29.56.217:81/api/");
		define("AHMEDABAD_CS_JDBOX_URL", "172.29.56.217:811/");
	}
}
else
{
	define('LOG_PATH', $_SERVER['DOCUMENT_ROOT'].'jdbox/');
	define("GNO_URL","hemavathiv.jdsoftware.com/MEGENIO"); // defining genio IP
	define("LIVE_APP", 0);
	define("VIRTUAL_API","http://ganeshsharma.jdsoftware.com/IND_BACKEND/scripts/check_virtual.php");
	define("MUMBAI_CS_JDBOX_URL", "hemavathiv.jdsoftware.com/JDBOX/");
	define("MUMBAI_API_FIN_SERVICE"     , "http://hemavathiv.jdsoftware.com/csgenio/api/");
}

define('APIDOMAIN',"http://192.168.20.102:9001/web_services/");

if(preg_match("/reseller.justdial.com/i", $_SERVER['HTTP_HOST']) || preg_match("/192.168.12.163/i", $_SERVER['HTTP_HOST']))
{
	define('SOURCE_TYPE','Reseller');
}

$techinfo_pri_series = array(
'MUMBAI' => array
    (
        array('MIN'=>61425500, 'MAX'=>61425999),
        array('MIN'=>61426200, 'MAX'=>61426699),
        array('MIN'=>61630000, 'MAX'=>61632499),
        array('MIN'=>61426700, 'MAX'=>61427199),
        array('MIN'=>61610000, 'MAX'=>61612499),
        array('MIN'=>61427200, 'MAX'=>61427699),
        array('MIN'=>61612500, 'MAX'=>61614999),
        array('MIN'=>61427700, 'MAX'=>61428199),
        array('MIN'=>61615000, 'MAX'=>61617499),
        array('MIN'=>61428200, 'MAX'=>61428699),
        array('MIN'=>61617500, 'MAX'=>61619999),
        array('MIN'=>61428700, 'MAX'=>61429199),
        array('MIN'=>61620000, 'MAX'=>61622499),
        array('MIN'=>61429200, 'MAX'=>61429699),
        array('MIN'=>61632500, 'MAX'=>61634999),
        array('MIN'=>61625000, 'MAX'=>61625499),
        array('MIN'=>61625500, 'MAX'=>61625999),
        array('MIN'=>61626000, 'MAX'=>61626499),
        array('MIN'=>61626500, 'MAX'=>61626999),
        array('MIN'=>61627000, 'MAX'=>61627499),
        array('MIN'=>61635000, 'MAX'=>61635499),
        array('MIN'=>61635500, 'MAX'=>61635999),
        array('MIN'=>61636000, 'MAX'=>61636499),
        array('MIN'=>61636500, 'MAX'=>61636999),
        array('MIN'=>61637000, 'MAX'=>61637499),
        array('MIN'=>61637500, 'MAX'=>61637999),
        array('MIN'=>61638000, 'MAX'=>61638499),
        array('MIN'=>61638500, 'MAX'=>61638999),
        array('MIN'=>61639000, 'MAX'=>61639499),
        array('MIN'=>66720400, 'MAX'=>66720899),
        array('MIN'=>66824000, 'MAX'=>66824499),
        array('MIN'=>67303500, 'MAX'=>67303999),
        array('MIN'=>67305000, 'MAX'=>67305499),
        array('MIN'=>67305500, 'MAX'=>67305999),
        array('MIN'=>67306000, 'MAX'=>67306499),
        array('MIN'=>67306500, 'MAX'=>67306999),
        array('MIN'=>67307000, 'MAX'=>67307499),
        array('MIN'=>67307500, 'MAX'=>67307999),
        array('MIN'=>67338000, 'MAX'=>67338299),
        array('MIN'=>67309500, 'MAX'=>67309699),
        array('MIN'=>67308500, 'MAX'=>67308999),
        array('MIN'=>67384000, 'MAX'=>67384499),
        array('MIN'=>67384500, 'MAX'=>67384999),
        array('MIN'=>67688400, 'MAX'=>67688499),
        array('MIN'=>67624300, 'MAX'=>67624699),
        array('MIN'=>67625000, 'MAX'=>67625399),
        array('MIN'=>67689000, 'MAX'=>67689999),
        array('MIN'=>67731000, 'MAX'=>67731999),
        array('MIN'=>67732000, 'MAX'=>67732999),
        array('MIN'=>67733000, 'MAX'=>67733499),
        array('MIN'=>49170000, 'MAX'=>49172999),
        array('MIN'=>49173000, 'MAX'=>49175999),
        array('MIN'=>49176000, 'MAX'=>49178999),
        array('MIN'=>49179000, 'MAX'=>49181999),
        array('MIN'=>49182000, 'MAX'=>49184999),
        array('MIN'=>49185000, 'MAX'=>49187999)
    ),
'DELHI' => array
    (
        array('MIN'=>66217000 , 'MAX'=>66217499),
        array('MIN'=>66242400 , 'MAX'=>66242899),
        array('MIN'=>66338500 , 'MAX'=>66338993),
        array('MIN'=>66264000 , 'MAX'=>66264499),
        array('MIN'=>66264500 , 'MAX'=>66264999),
        array('MIN'=>66265000 , 'MAX'=>66265499),
        array('MIN'=>66265500 , 'MAX'=>66265999),
        array('MIN'=>66266000 , 'MAX'=>66266499),
        array('MIN'=>66223200 , 'MAX'=>66223699),
        array('MIN'=>66223700 , 'MAX'=>66224199),
        array('MIN'=>66225200 , 'MAX'=>66225699),
        array('MIN'=>66225700 , 'MAX'=>66226199),
        array('MIN'=>66217500 , 'MAX'=>66217999),
        array('MIN'=>66218000 , 'MAX'=>66218499),
        array('MIN'=>66226200 , 'MAX'=>66226699),
        array('MIN'=>66355000 , 'MAX'=>66357499),
        array('MIN'=>66226700 , 'MAX'=>66227199),
        array('MIN'=>66357500 , 'MAX'=>66359999),
        array('MIN'=>66227200 , 'MAX'=>66227699),
        array('MIN'=>66360000 , 'MAX'=>66362499),
        array('MIN'=>66227700 , 'MAX'=>66228199),
        array('MIN'=>66362500 , 'MAX'=>66364999),
        array('MIN'=>66228200 , 'MAX'=>66228699),
        array('MIN'=>66266500 , 'MAX'=>66266999),
        array('MIN'=>66442001 , 'MAX'=>66444999),
        array('MIN'=>66430000 , 'MAX'=>66432999),
        array('MIN'=>66433000 , 'MAX'=>66435999),
        array('MIN'=>66440000 , 'MAX'=>66441999),
        array('MIN'=>66436000 , 'MAX'=>66438999),
        array('MIN'=>66261501 , 'MAX'=>66261799),
        array('MIN'=>66261801 , 'MAX'=>66261999),
        array('MIN'=>66262001 , 'MAX'=>66262421),
        array('MIN'=>66450140 , 'MAX'=>66451139),
        array('MIN'=>66777777 , 'MAX'=>66777777),
        array('MIN'=>67777700 , 'MAX'=>67778299),
        array('MIN'=>66531000 , 'MAX'=>66539699),
        array('MIN'=>66261500 , 'MAX'=>66261500)
    ),
'KOLKATA' => array
    (
        array('MIN'=>66341000, 'MAX'=>66341499),
        array('MIN'=>66341500, 'MAX'=>66341999),
        array('MIN'=>66342000, 'MAX'=>66342499),
        array('MIN'=>66342500, 'MAX'=>66342999),
        array('MIN'=>66343000, 'MAX'=>66343499),
        array('MIN'=>66343500, 'MAX'=>66343999),
        array('MIN'=>66346000, 'MAX'=>66346499),
        array('MIN'=>66346500, 'MAX'=>66346999),
        array('MIN'=>66347000, 'MAX'=>66347499),
        array('MIN'=>66347500, 'MAX'=>66347999),
        array('MIN'=>66348000, 'MAX'=>66348499),
        array('MIN'=>66245000, 'MAX'=>66247999),
        array('MIN'=>66037000, 'MAX'=>66039999),
        array('MIN'=>44501000, 'MAX'=>44503999),
        array('MIN'=>44504000, 'MAX'=>44507999)
    ),
'BANGALORE' => array
    (
        array('MIN'=>66379000 , 'MAX'=>66379499),
        array('MIN'=>66490000 , 'MAX'=>66492499),
        array('MIN'=>66379500 , 'MAX'=>66379999),
        array('MIN'=>66492500 , 'MAX'=>66494999),
        array('MIN'=>66366300 , 'MAX'=>66366799),
        array('MIN'=>66815000 , 'MAX'=>66817499),
        array('MIN'=>66388800 , 'MAX'=>66389299),
        array('MIN'=>66495000 , 'MAX'=>66497499),
        array('MIN'=>66389300 , 'MAX'=>66389799),
        array('MIN'=>66497500 , 'MAX'=>66499999),
        array('MIN'=>66367100 , 'MAX'=>66367599),
        array('MIN'=>66507000 , 'MAX'=>66507499),
        array('MIN'=>66507500 , 'MAX'=>66507999),
        array('MIN'=>66508000 , 'MAX'=>66508499),
        array('MIN'=>66534000 , 'MAX'=>66534499),
        array('MIN'=>66534500 , 'MAX'=>66534999),
        array('MIN'=>66536000 , 'MAX'=>66536499),
        array('MIN'=>66536500 , 'MAX'=>66536999),
        array('MIN'=>66537000 , 'MAX'=>66537499),
        array('MIN'=>49174000 , 'MAX'=>49174999),
        array('MIN'=>49175000 , 'MAX'=>49175999),
        array('MIN'=>49176000 , 'MAX'=>49176999),
        array('MIN'=>49177000 , 'MAX'=>49177999),
        array('MIN'=>49178000 , 'MAX'=>49178999),
        array('MIN'=>49179000 , 'MAX'=>49179999),
        array('MIN'=>49341000 , 'MAX'=>49341999),
        array('MIN'=>49342000 , 'MAX'=>49342999)
   ),
'CHENNAI' => array
    (
        array('MIN'=>66323000 , 'MAX'=>66323499),
        array('MIN'=>66420000 , 'MAX'=>66422499),
        array('MIN'=>66323500 , 'MAX'=>66323999),
        array('MIN'=>66423000 , 'MAX'=>66425499),
        array('MIN'=>66324200 , 'MAX'=>66324699),
        array('MIN'=>66426000 , 'MAX'=>66428499),
        array('MIN'=>66324700 , 'MAX'=>66325199),
        array('MIN'=>66320000 , 'MAX'=>66321999),
        array('MIN'=>66328800 , 'MAX'=>66329300),
        array('MIN'=>66325200 , 'MAX'=>66325699),
        array('MIN'=>66325700 , 'MAX'=>66326199),
        array('MIN'=>66368500 , 'MAX'=>66368999),
        array('MIN'=>66369000 , 'MAX'=>66369499),
        array('MIN'=>66369500 , 'MAX'=>66369999),
        array('MIN'=>66245000 , 'MAX'=>66245499),
        array('MIN'=>66245500 , 'MAX'=>66245999),
        array('MIN'=>66246000 , 'MAX'=>66246499),
        array('MIN'=>66246500 , 'MAX'=>66246999),
        array('MIN'=>66247000 , 'MAX'=>66247499),
        array('MIN'=>66247500 , 'MAX'=>66247999),
        array('MIN'=>66077000 , 'MAX'=>66079999),
        array('MIN'=>66598000 , 'MAX'=>66599999),
        array('MIN'=>66590001 , 'MAX'=>66593999),
        array('MIN'=>66751001 , 'MAX'=>66753999),
        array('MIN'=>66754001 , 'MAX'=>66756999),
        array('MIN'=>66757001 , 'MAX'=>66759999)
    ),
'PUNE' => array
    (
        array('MIN'=>66285500 , 'MAX'=>66285999),
        array('MIN'=>66820000 , 'MAX'=>66822499),
        array('MIN'=>66239000 , 'MAX'=>66239299),
        array('MIN'=>66822500 , 'MAX'=>66824999),
        array('MIN'=>66239300 , 'MAX'=>66239599),
        array('MIN'=>66825000 , 'MAX'=>66827499),
        array('MIN'=>66239600 , 'MAX'=>66239899),
        array('MIN'=>66827500 , 'MAX'=>66829999),
        array('MIN'=>66491000 , 'MAX'=>66491299),
        array('MIN'=>66491300 , 'MAX'=>66491599),
        array('MIN'=>66491600 , 'MAX'=>66491899),
        array('MIN'=>67281000 , 'MAX'=>67281999),
        array('MIN'=>67288000 , 'MAX'=>67289999),
        array('MIN'=>40014000 , 'MAX'=>40016999),
        array('MIN'=>40017000 , 'MAX'=>40019999)
    ),
'HYDERABAD' => array
    (
        array('MIN'=>66049000 , 'MAX'=>66049499),
        array('MIN'=>66049500 , 'MAX'=>66049999),
        array('MIN'=>66047800 , 'MAX'=>66048299),
        array('MIN'=>66048300 , 'MAX'=>66048799),
        array('MIN'=>66047300 , 'MAX'=>66047799),
        array('MIN'=>66045500 , 'MAX'=>66045999),
        array('MIN'=>66046000 , 'MAX'=>66046499),
        array('MIN'=>66046500 , 'MAX'=>66046999),
        array('MIN'=>67110000 , 'MAX'=>67113000),
        array('MIN'=>67119501 , 'MAX'=>67119999),
        array('MIN'=>67120000 , 'MAX'=>67120499),
        array('MIN'=>67113001 , 'MAX'=>67116000),
        array('MIN'=>67116001 , 'MAX'=>67119000),
        array('MIN'=>67119001 , 'MAX'=>67119500),
        array('MIN'=>67120500 , 'MAX'=>67120999),
        array('MIN'=>67239000 , 'MAX'=>67239999),
        array('MIN'=>67270000 , 'MAX'=>67271999),
        array('MIN'=>49640000 , 'MAX'=>49642999),
        array('MIN'=>49643000 , 'MAX'=>49645999),
        array('MIN'=>49646000 , 'MAX'=>49648999)
    ),
'AHMEDABAD' => array
    (
        array('MIN'=>66151000 , 'MAX'=>66151499),
        array('MIN'=>66154500 , 'MAX'=>66156999),
        array('MIN'=>66151500 , 'MAX'=>66151999),
        array('MIN'=>66152000 , 'MAX'=>66152499),
        array('MIN'=>66152500 , 'MAX'=>66152999),
        array('MIN'=>66153000 , 'MAX'=>66153499),
        array('MIN'=>66087000 , 'MAX'=>66087999),
        array('MIN'=>49010000 , 'MAX'=>49012999),
        array('MIN'=>49013000 , 'MAX'=>49015999)
    )
);

/* $field_arr	=	array("sphinx_id","parentid","regionid","companyname","country","state","city","display_city","data_city","area","building_name","street","landmark","pincode","latitude","longitude","geocode_accuracy_level","full_address","hide_address","stdcode","landline","landline_display","landline_feedback","landline_addinfo","mobile","mobile_display","mobile_feedback","mobile_addinfo","fax","tollfree","tollfree_display","tollfree_addinfo","email","email_display","email_feedback","sms_scode","website","contact_person","contact_person_display","contact_person_addinfo","callconnect","vitualNumber","virtual_mapped_number","paid","displayType","turnover","working_time_start","working_time_end","payment_type","year_establishment","accrediations","certificates","no_employee","catidlineage","catidlineage_search","national_catidlineage","national_catidlineage_search","hotcategory","tag_catid","tag_catname","tag_line","tag_image_path","tag_description","closedown_flag","audit_status","deactflg","display_flag","CorporateDealers","freeze","mask","original_creator","original_date","updatedBy","updatedOn","backendupdate","narration","mainsource","subsource","datesource","session_key","source"); 

if(preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) || preg_match("/beta.justdial.com/i", $_SERVER['HTTP_HOST']))
{
	$dbarr	=	array(
						'DB_IRO'                    => array($_SESSION['s_main_ip'],APP_USER, APP_PASS, DB_IRO),
						'DB_D_JDS'                  => array($_SESSION['s_main_ip'],APP_USER, APP_USER, DB_JDS),
						'DB_TME_JDS'                => array($_SESSION['s_main_ip'],APP_USER, APP_USER, DB_JDS),
						'DB_FINANCE'				=> array($_SESSION['finance_ip'],APP_USER, APP_USER, DB_FINANCE),
						'IDC'                       => array($_SESSION['internetip'],APP_USER, APP_USER, ONLINE_CITY_DB)
					);
}
else
{
	$dbarr	=	array(
						'DB_IRO'                    => array($_SESSION['s_main_ip'],APP_USER, APP_PASS, DB_IRO),
						'DB_D_JDS'                  => array($_SESSION['s_main_ip'],APP_USER, APP_USER, DB_JDS),
						'DB_TME_JDS'                => array($_SESSION['s_main_ip'],APP_USER, APP_USER, DB_JDS),
						'DB_FINANCE'				=> array($_SESSION['finance_ip'],APP_USER, APP_USER, DB_FINANCE),
						'IDC'                       => array($_SESSION['internetip'],APP_USER, APP_USER, ONLINE_CITY_DB)
					);
}
*/
?>
