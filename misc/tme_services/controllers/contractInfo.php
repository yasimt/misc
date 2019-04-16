<?php
class contractInfo extends Controller {
    function __construct() {
        parent::__construct();
    }
    
    function getContractCatLive($contractid) { // NO changes 
		echo $this->view->contractInfo	=	$this->model->getContractCatLive($contractid);
	}
	
	function checkTrackerRep($contractid) { // done
		echo $this->view->contractInfo	=	$this->model->checkTrackerRep($contractid);
	}
	
	function tempContract($contractid) { // done
		echo $this->view->contractInfo	=	$this->model->tempContract($contractid);
	}
	
	function actEcsRetention() { // done
		echo $this->view->contractInfo	=	$this->model->actEcsRetention();
	}
	
	function fetchEcsRetentionData($number,$fullData='') { // not in use
		echo $this->view->contractInfo	=	$this->model->fetchEcsRetentionData($number,$fullData);
	}
	
	function searchCompanyByNum($phone='') { // Not in use
		echo $this->view->contractInfo	=	$this->model->searchCompanyByNum($phone);
	}
	
	function fetchMulticityTagging($parentid='') { // done
		echo $this->view->contractInfo	=	$this->model->fetchMulticityTagging($parentid);
	}
	
	function insertDisposeVal() {
		echo $this->view->contractInfo	=	$this->model->insertDisposeVal();
	}
	
	function insertECSStatus() { // no use
		echo $this->view->contractInfo	=	$this->model->insertECSStatus();
	}
	
	function fetchTmeRetentionComments($empcode = '') { // in use 
		echo $this->view->contractInfo	=	$this->model->fetchTmeRetentionComments($empcode);	
	}
	
	function SendVLC($parentid='',$data_city='',$reminder=''){ // done
		echo $this->view->contractInfo       =       $this->model->SendVLC($parentid,$data_city,$reminder);
	}
	
	function StoreCommentECS(){ // done
		echo $this->view->contractInfo       =       $this->model->StoreCommentECS();
	}
	
	function StoreCommentretention(){ // done
		echo $this->view->contractInfo       =       $this->model->StoreCommentretention();
	}
	
	function getMainTabGeneralData($parentid) {
		echo $this->view->contractInfo       =       $this->model->getMainTabGeneralData($parentid);
	}
	
	function getMainTabExtraData($parentid) {
		echo $this->view->contractInfo       =       $this->model->getMainTabExtraData($parentid);
	}
	
	function getShadowTabGeneralData($parentid) {
		echo $this->view->contractInfo       =       $this->model->getShadowTabGeneralData($parentid);
	}
	
	function getShadowTabExtraData($parentid) {
		echo $this->view->contractInfo       =       $this->model->getShadowTabExtraData($parentid);
	}
	
	function checkNewTmeCall() { // no use 
		echo $this->view->contractInfo       =       $this->model->checkNewTmeCall();
	}
	
	function insertAutoWrapUP() {
		echo	$this->view->insertAutoWrapUP   =       $this->model->insertAutoWrapUP();
	}
	
	function getAutoWrapupInfo() {
		echo	$this->view->getAutoWrapupInfo   =       $this->model->getAutoWrapupInfo();
	}
	
	function removeAllCallBack() {
		echo $this->view->contractInfo       =       $this->model->removeAllCallBack();
	}
	
	function remindLaterCallBack() {
		echo $this->view->contractInfo       =       $this->model->remindLaterCallBack();
	}
	
	function showContractBalance() {
		echo $this->view->contractInfo       =       $this->model->showContractBalance();
	}
	
	function fetchcities() {
		echo $this->view->contractInfo	=	$this->model->fetchcities();
	}
	
	function sendJdrrMail() {
		echo $this->view->contractInfo       =       $this->model->sendJdrrMail();
	}
	
	function getDispositionList() {
		echo $this->view->getDispositionList       =       $this->model->getDispositionList();
	}
	
	function getModuleType() {
		echo $this->view->contractInfo	=	$this->model->getModuleType();
	}
	
	function getEcsEmpcode($empcode) {
		echo $this->view->contractInfo	=	$this->model->getEcsEmpcode($empcode);
	}
	
	function getContractDataInfo() {
		echo $this->view->contractInfo	=	$this->model->getContractDataInfo();
	}
	
	function fetchLiveData() {
        echo $this->view->contractInfo  =   $this->model->fetchLiveData();
    }
	
    function getJdrrPath() {
		echo $this->view->contractInfo       =       $this->model->getJdrrPath();
	}  
	
	function addjdrr() {
		echo $this->view->contractInfo       =       $this->model->addjdrr();
	}

	function addbanner() {
		echo $this->view->contractInfo       =       $this->model->addbanner();
	}

	function bannerlog() {
		echo $this->view->contractInfo       =       $this->model->bannerlog();
	}

	function checkbanner() {
		echo $this->view->contractInfo       =       $this->model->checkbanner();
	}

	function deletebanner() {
		echo $this->view->contractInfo       =       $this->model->deletebanner();
	}

	function deletejdrr() {
		echo $this->view->contractInfo       =       $this->model->deletejdrr();
	}

	function jdrrlog() {
		echo $this->view->contractInfo       =       $this->model->jdrrlog();
	}

	function checkjdrr() {
		echo $this->view->contractInfo       =       $this->model->checkjdrr();
	}

	function get_banner_spec() {
		echo $this->view->contractInfo       =       $this->model->get_banner_spec();
	}
	
	function addjdomni() {
		echo $this->view->contractInfo       =       $this->model->addjdomni();
	}
	
	function deletejdomni() {
		echo $this->view->contractInfo       =       $this->model->deletejdomni();
	}
	
	function payment_type() {
		echo $this->view->contractInfo       =       $this->model->payment_type();
	}
	
	function campaignpricelist() {
		echo $this->view->contractInfo       =       $this->model->campaignpricelist();
	}
	
	function ecspricelist() {
		echo $this->view->contractInfo       =       $this->model->ecspricelist();
	}
	
	
	function go_to_payment_page() {
		echo	$this->view->contractInfo       =       $this->model->go_to_payment_page();
	}
	
	function payment_summary_list() {
		echo	$this->view->contractInfo       =       $this->model->payment_summary_list();
	}
	
	function delete_unchecked() {
		echo	$this->view->contractInfo       =       $this->model->delete_unchecked();
	}
	
	
	
	function deletecampaign() {
		echo	$this->view->contractInfo       =       $this->model->deletecampaign();
	}
	
	function call_disc_api() {
		echo	$this->view->contractInfo       =       $this->model->call_disc_api();
	}
	
	function check_ecs() {
		echo	$this->view->contractInfo       =       $this->model->check_ecs();
	} 
	
	function get_bankdetials() {
		echo	$this->view->contractInfo       =       $this->model->get_bankdetials();
	}
	
	function save_bankdetials() {
		echo	$this->view->contractInfo       =       $this->model->save_bankdetials();
	}
	
	function get_accountdetials() {
		echo	$this->view->contractInfo       =       $this->model->get_accountdetials();
	}
	
	function check_upfront() {
		echo	$this->view->contractInfo       =       $this->model->check_upfront();
	} 
	
	function customjdrrhandling() {
		echo	$this->view->customjdrrhandling       =       $this->model->customjdrrhandling();
	} 
	
	
	function jdrrplusdiscount() {
		echo	$this->view->customjdrrhandling       =       $this->model->jdrrplusdiscount();
	} 
	
	
	function addjdrrLive($params = '') {
		echo	$this->view->customjdrrhandling       =       $this->model->addjdrrLive($params);
	} 
	
	
	function addbannerlive($params = '') {
		echo	$this->view->customjdrrhandling       =       $this->model->addbannerlive($params);
	} 
	      
	function addjdomniLive($params = '') {
		echo	$this->view->customjdrrhandling       =       $this->model->addjdomniLive($params);
	} 
	
	function tempactualbudgetupdate() {
		echo	$this->view->customjdrrhandling       =       $this->model->tempactualbudgetupdate();
	} 
	
	function checkdomainavailibilty() {
		echo	$this->view->checkdomainavailibilty   =       $this->model->checkdomainavailibilty();
	}
	
	
	function saveomnidomains() {
		echo	$this->view->saveomnidomains   =       $this->model->saveomnidomains();
	} 
	
	
	function getowndomainname() {
		echo	$this->view->saveomnidomains   =       $this->model->getowndomainname();
	} 
	
	function deletedomainname() {
		echo	$this->view->deletedomainname   =       $this->model->deletedomainname();
	} 
	
	function checkemail() {
		echo	$this->view->checkemail   =       $this->model->checkemail();
	} 
	
	
	
	function getpricelist() {
		echo	$this->view->getpricelist   =       $this->model->getpricelist();
	}
	
	
	function addjdrrplus() {
		echo	$this->view->addjdrrplus   =       $this->model->addjdrrplus();
	}
	
	function deletejdrrplus() {
		echo	$this->view->deletejdrrplus   =       $this->model->deletejdrrplus();
	}
	
	function combopackageprice() {
		echo	$this->view->combopackageprice   =       $this->model->combopackageprice();
	}
	
	function combocustomprice() {
		echo	$this->view->combocustomprice   =       $this->model->combocustomprice();
	}
	
	function combopricereset() {
		echo	$this->view->combopricereset   =       $this->model->combopricereset();
	}
	
	function comboprice() {
		echo	$this->view->comboprice   =       $this->model->comboprice();
	}
	
	function combopricelist() {
		echo	$this->view->combopricelist   =       $this->model->combopricelist();
	}
	
	
	function combopricemin() {
		echo	$this->view->combopricemin   =       $this->model->combopricemin();
	}
	
	function setTemplateId() {
		echo	$this->view->setTemplateId   =       $this->model->setTemplateId();
	}
	
	function sendomnidemo() {
		echo	$this->view->sendomnidemo   =       $this->model->sendomnidemo();
	}
	function sendYOWlink() {
		echo	$this->view->sendYOWlink   =       $this->model->sendYOWlink();
	}
	
	function checkCategoryType() {
		echo	$this->view->checkCategoryType   =       $this->model->checkCategoryType();
	}
	
	function insertDemoLinkDetails() {
		echo	$this->view->insertDemoLinkDetails   =       $this->model->insertDemoLinkDetails();
	}
	
	function fetchDemoLinkDetails() {
		echo	$this->view->fetchDemoLinkDetails   =       $this->model->fetchDemoLinkDetails();
	}
	
	
	function fetchdemocategories() {
		echo	$this->view->fetchdemocategories   =       $this->model->fetchdemocategories();
	}

	function transferaccdetailstomain() {
		echo	$this->view->transferaccdetailstomain   =       $this->model->transferaccdetailstomain();
	}

	function sendjdpaylink() {
		echo	$this->view->sendjdpaylink   =       $this->model->sendjdpaylink();
	}
	function check_one_plus_block() {
		echo	$this->view->check_one_plus_block   =       $this->model->check_one_plus_block();
	}
	function checkemployeeeligible() {
		echo	$this->view->checkemployeeeligible   =       $this->model->checkemployeeeligible();
	}
	
	function fetchpaymentype() {
		echo	$this->view->fetchpaymentype   =       $this->model->fetchpaymentype();
	}
	function deleteallcampaigns() {
		echo	$this->view->deleteallcampaigns   =       $this->model->deleteallcampaigns();
	}
	function setecs() {
		echo	$this->view->setecs   =       $this->model->setecs();
	}
	function sendratinglink() {
		echo	$this->view->sendratinglink   =       $this->model->sendratinglink();
	}
	
	function checklive() {
		echo	$this->view->checklive   =       $this->model->checklive();
	}
	
	function chkRatingCat() {
		echo	$this->view->chkRatingCat   =       $this->model->chkRatingCat();
	}
	
	function gettemplateurl() {
		echo	$this->view->gettemplateurl   =       $this->model->gettemplateurl();
	}
	
	function storeomnitemplateinfo() {
		echo	$this->view->gettemplateurl   =       $this->model->storeomnitemplateinfo();
	}
	
	function addomnitemplatetemp() {
		echo	$this->view->gettemplateurl   =       $this->model->addomnitemplatetemp();
	}
	
	function deleteomnitemplatetemp() {
		echo	$this->view->gettemplateurl   =       $this->model->deleteomnitemplatetemp();
	}
	
	function addomnitemplatelive() {
		echo	$this->view->gettemplateurl   =       $this->model->addomnitemplatelive();
	}
	
	function deleteomnitemplatelive() {
		echo	$this->view->gettemplateurl   =       $this->model->deleteomnitemplatelive();
	}
	
	function checkpackagedepend() {
		echo	$this->view->checkpackagedepend   =       $this->model->checkpackagedepend();
	}
	
	function  checkaccess() {
		echo	$this->view->checkaccess   =       $this->model->checkaccess();
	}
	
	function  fetchpricechatprice() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->fetchpricechatprice();
	}
	
	function  insert_discount() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->insert_discount();
	}
	
	function get_discount_info() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->get_discount_info();
	}
	
	function deletejdrrLive() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->deletejdrrLive();
	}
	
	function deletejdomniLive() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->deletejdomniLive();
	}
	
	function deletebannerLive() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->deletebannerLive();
	}
	
	function deletecombolive() {
		echo	$this->view->fetchpricechatupfront   =       $this->model->deletecombolive();
	}

	function saveemailids() {
		echo	$this->view->saveemailids   =       $this->model->saveemailids();
	}
	
	function emailpackageprice() {
		echo	$this->view->emailpackageprice   =       $this->model->emailpackageprice();
	}
	
	function emailpackagerequired() {
		echo	$this->view->emailpackagerequired   =       $this->model->emailpackagerequired();
	}
	
	function smspackagerequired() {
		echo	$this->view->smspackagerequired   =       $this->model->smspackagerequired();
	}
	
	function smsprice() {
		echo	$this->view->smsprice   =       $this->model->smsprice();
	}

	function newpricechatval() {
		echo	$this->view->newpricechatval   =       $this->model->newpricechatval();
	}
	function getAppointLogInfo() {
		echo	$this->view->contractInfo   =       $this->model->getAppointLogInfo();
	}
	function insertTimerStatus() {
		echo	$this->view->insertTimerStatus   =       $this->model->insertTimerStatus();
	}
	
	function getTimerStatus() {
		echo	$this->view->getTimerStatus   =       $this->model->getTimerStatus();
	}
	
	function getalldata() {
		echo	$this->view->contractInfo   =       $this->model->getalldata();
	}
	
	function getAutoWrapupInfoDetail() {
		echo	$this->view->contractInfo   =       $this->model->getAutoWrapupInfoDetail();
	}
	/*function Added for updating contact person if missing in bform from Allocate Appt*/
	function update_generalinfo_shadow() {
		echo	$this->view->contractInfo   =       $this->model->update_generalinfo_shadow();
	}
	
	public function getStateListings() {
        echo $this->view->contractInfo      =   $this->model->getStateListings();
    }
	
	public function getShadowTabData($contractid) {
        echo    $this->view->contractInfo       =       $this->model->getShadowTabData($contractid);
    }
	public function checkmulticity() {
        echo $this->view->contractInfo      =   $this->model->checkmulticity();
    }
    
    public function saveNationallistingData() {
        echo $this->view->contractInfo      =   $this->model->saveNationallistingData();
    }
    public function insertLocalListingval() {
        echo $this->view->contractInfo      =   $this->model->insertLocalListingval();
    }
    public function bformvalidation() {
        echo    $this->view->bformvalidation   =       $this->model->bformvalidation();
    }

    function savedetails() {
	echo	$this->view->savedetails   =       $this->model->savedetails();
    }
    
	function omnicatlog() {
		echo $this->view->categoryInfo	=	$this->model->omnicatlog();
	}

	function fetchCorIncorAccuracy() {
		echo $this->view->contractInfo  = $this->model->fetchCorIncorAccuracy();
	}
	
	function fetchCorIncorAccuracyDetail() {
		echo $this->view->contractInfo  = $this->model->fetchCorIncorAccuracyDetail();
	}
	function checkDiscount() {
		echo	$this->view->checkdiscount   =       $this->model->checkDiscount();
	}
	
	function freeWebsiteStatus() {
		echo $this->view->contractInfo  = $this->model->freeWebsiteStatus();
	}

	function fetchDcInfo($parentid = '') {
		echo $this->view->fetchDcInfo  = $this->model->fetchDcInfo($parentid);
	}
	
	function getmaincampaignid() {
		echo	$this->view->contractInfo   =       $this->model->getmaincampaignid();
	}
	
	function GetContractData() {
		echo $this->view->GetContractData  = $this->model->GetContractData();
	}
	function SSLpackagerequired() {
		echo	$this->view->contractInfo   =       $this->model->SSLpackagerequired();
	}
	function deleteSSLPackage() {
		echo	$this->view->contractInfo   =       $this->model->deletesslpack();
	}
	function getforgetLink() {
        echo    $this->view->contractInfo   =       $this->model->getforgetLink();
    }
	
    function domainregisterauto() {
        echo $this->view->contractInfo  =   $this->model->domainregisterauto();
    }
     function get_bankdetialsmicr() {
        echo $this->view->contractInfo  =   $this->model->get_bankdetialsmicr();
    }
	 function set_pack_emi() {
        echo $this->view->contractInfo  =   $this->model->set_pack_emi();
    }
    function get_pack_emi() {
        echo $this->view->contractInfo  =   $this->model->get_pack_emi();
    }
    function check_existing_budget() {
        echo $this->view->contractInfo  =   $this->model->check_existing_budget();
    }
	function view_tv_ad(){
		echo $this->view->contractInfo  =   $this->model->view_tv_ad();
	}
	function udateusereditdata(){
		echo $this->view->contractInfo  =   $this->model->udateusereditdata();
	}
}
