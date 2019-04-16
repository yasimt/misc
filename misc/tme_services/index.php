<?php

	require 'libs/Bootstrap.php';
	require 'libs/Controller.php';
	require 'libs/Model.php';
	require 'libs/View.php';
	$serverAddr	=	$_SERVER['SERVER_ADDR'];
	$serverPointer	=	explode('.',$serverAddr);
	define('ADDR_CONST',$serverPointer[2]);
	GLOBAL $parseConf;
	if(ADDR_CONST	==	'64') {
		$parseConf	=	parse_ini_file('public/files/developmentip.conf',1);
	} else {
		$parseConf	=	parse_ini_file('public/files/productionip.conf',1);
	}
	define('SERVER_PARAM',$parseConf['servicefinder']['serviceparam']);
	define('SERVER_CITY',$parseConf['servicefinder']['servicecity']);
	require 'libs/utility.php';
	require 'libs/Paths.php';
	require 'libs/db.class.php';
	require 'libs/Session.php';
	require 'libs/Cookie.php';
	require 'libs/Mongo.php';
	require 'libs/company_details_class.php';
	$app = new Bootstrap();

?>
