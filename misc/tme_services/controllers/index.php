<?php
class Index extends Controller {
	function __construct() {
        parent::__construct();
    }
    
    function index($flag,$brokenCypher='') {
		if($flag	==	1) {
			if(isset($_GET['authSess'])) {
				$cryptText	=	Utility::decrypt_data($_GET['authSess'],$_GET['ivText']);
				$brokenCypher = json_decode(json_encode((array)simplexml_load_string($cryptText)),1);
				$params	=	$brokenCypher;
				
				if(!isset($_SESSION['tokens'][$params['SERVICE']['SERVICE_PARAM']])) {
					if(isset($params['SERVICE'])) {
						$this->view->serviceParam	=	$params['SERVICE']['SERVICE_PARAM'];
						$this->view->serviceName	=	$params['SERVICE']['SERVICE_NAME'];
						$this->view->referUrl		=	$params['SSO_AUTH'];
						$this->view->logoutUrl		=	$params['SSO_LOGOUT'];
						$viewFinder	=	explode("_",$params['SERVICE']['SERVICE_NAME']);
						if(empty($viewFinder[1])) {
							$viewEscaped	=	$viewFinder[0];
						} else {
							$viewEscaped	=	$viewFinder[1];
						}
						if(isset($viewEscaped)) {
							switch($viewEscaped) {	
								case 'mum':
									$this->view->city	=	'Mumbai';
								break;
								case 'del':
									$this->view->city	=	'Delhi';
								break;
								case 'kol':
									$this->view->city	=	'Kolkata';
								break;
								case 'bang':
									$this->view->city	=	'Bangalore';
								break;
								case 'chn':
									$this->view->city	=	'Chennai';
								break;
								case 'pun':
									$this->view->city	=	'Pune';
								break;
								case 'hyd':
									$this->view->city	=	'Hyderabad';
								break;
								case 'ahd':
									$this->view->city	=	'Ahmedabad';
								break;
								default:
									$this->view->city	=	'Remote Cities';
								break;
							}
						}
						$this->view->render('login_'.$viewFinder[0],FALSE,TRUE);
					} else {
						$this->view->render('login_form',FALSE,TRUE);
					}
				} else {
					$xmlgen = new xmlgen();
					$array = array("EMPINFO"=>array("EMPCODE"=>$_SESSION['empcode'],"AUTHFLAG"=>1,"EMPNAME"=>$_SESSION['empname'],"CITY"=>$_SESSION['city']),"MODULEINFO"=>array("STATIONID"=>'NOT_APP'));
					
					$data = $xmlgen->generate('SSORESPONSE',$array);
					
					$text = $data;
					$cypherText	=	Utility::encrypt_data($text);
					$cypher	=	$cypherText['enc'];
					$iv	=	$cypherText['iv'];
					if(isset($_SESSION['loginSession'])) {
						$_SESSION['loginSession'][]	=	$params['SSO_LOGOUT'];
					} else {
						$_SESSION['loginSession']	=	array();
						$_SESSION['loginSession'][]	=	$params['SSO_LOGOUT'];
					}
					header('Location:http://'.$params['SSO_AUTH'].'?respText='.$cypher.'&iv='.$iv);
					die;
				}
			} else {
				$this->view->render('login_form',FALSE,TRUE);
			}
		} else {
			$this->view->render('home',FALSE,FALSE);
		}
	}
}
