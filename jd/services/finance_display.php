<?php
require_once('../config.php');
require_once('includes/financeDisplayClass.php');
require_once('includes/nationallistingclass.php');

//http://ganeshrj.jdsoftware.com/jdbox_cat/services/finance_display.php?parentid=&

if($_REQUEST["trace"] ==1)
{
	define("DEBUG_MODE",1);
}
else
{
	define("DEBUG_MODE",0);
}

if($_REQUEST)
{
	$params=$_REQUEST;
}
else
{
header('Content-Type: application/json');
$params	= json_decode(file_get_contents('php://input'),true);
}

if(DEBUG_MODE)
{
	echo '<pre>request data :: ';
	print_r($params);
}
//echo json_encode($params); exit;
$finance_display_obj = new financeDisplayClass($params);

if($params['action'] == 1)
{
	$diplay_arr = $finance_display_obj->financeDisplayUpfront();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 2)
{
	
	if(strtolower($params['module'] == 'tme')){
		$diplay_arr = $finance_display_obj->budgetDisplayUpfrontWithOffer();
	}else{
	$diplay_arr = $finance_display_obj->financeDisplayUpfrontWithOffer();
	}
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 3)
{
	
	$diplay_arr = $finance_display_obj->financeDisplayEcs();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 4)
{
	if(strtolower($params['module'] == 'tme')){
		$diplay_arr = $finance_display_obj->budgetDisplayEcsWithOffer();
	}else{
		$diplay_arr = $finance_display_obj->financeDisplayEcsWithOffer();
	}
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 5)
{
	$diplay_arr = $finance_display_obj->financeDisplayEcsAdv();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 6)
{
	$diplay_arr = $finance_display_obj->addJdCampaign();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 7)
{
	$diplay_arr = $finance_display_obj->deleteJdCampaign();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 8)
{
	$diplay_arr = $finance_display_obj->applyDiscount();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 9)
{
	$diplay_arr = $finance_display_obj->checkActiveEcs();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 10)
{
	$diplay_arr = $finance_display_obj->checkEcsEligibilty();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 11)
{
	$diplay_arr = $finance_display_obj->apiCalledAlwaysOmniFlow();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 12)
{
	$diplay_arr = $finance_display_obj->apiGetComboPriceForPackage();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 13)
{
	$diplay_arr = $finance_display_obj->saveCustComboDetails();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 14){
	
	$diplay_arr = $finance_display_obj->resetOmniCombo();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 15){
	
	$diplay_arr = $finance_display_obj->getOmniComboValue();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 16){
	
	$diplay_arr = $finance_display_obj->getOmniComboPrices();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 17){
	
	$diplay_arr = $finance_display_obj->getOmniMinComboPrices();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 18){
	
	$diplay_arr = $finance_display_obj->getPriceChart();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr;
}
if($params['action'] == 19){
	
	$diplay_arr = $finance_display_obj->deleteCampaignAll();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 20){
	
	$diplay_arr = $finance_display_obj->makeEcs();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 21){ 
	
	$diplay_arr = $finance_display_obj->makeUpfront();
	$result['results'] = array();
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 22){ 
	
	$diplay_arr = $finance_display_obj->tempToMainDependent();
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 23){ 
	
	$diplay_arr = $finance_display_obj->tempToMainPaymentType();
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 24){ 
	
	$diplay_arr = $finance_display_obj->mainToFinPaymentType(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 25){ 
	
	$diplay_arr = $finance_display_obj->mainToFinPaymentType(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 26){ 
	
	$diplay_arr = $finance_display_obj->displayLowValuePackage(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 27){ 
	
	$diplay_arr = $finance_display_obj->saveCustomValues(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 28){  
	
	$diplay_arr = $finance_display_obj->getCustomValues(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 29){  
	
	$diplay_arr = $finance_display_obj->deleteAsReq(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 30){  
	
	$diplay_arr = $finance_display_obj->deleteCombo(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 31){  
	// for proposal image path for jda
	$diplay_arr = $finance_display_obj->proposalImagePath(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 32){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->getPriceChartNew(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 33){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->saveEmailMobileDetails(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 34){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->tempToDealcloseBannerRotation(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}
if($params['action'] == 35){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->mainIDCToMainBannerRotation(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 36){  
	// for temp to shadow campaign mul new
	$diplay_arr = $finance_display_obj->tempToShadowCampaignMul(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 37){  
	// for shadow to main campaign mul new
	$diplay_arr = $finance_display_obj->shadowToMainCampaignMul(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 38){  
	// for new dependent insert
	$diplay_arr = $finance_display_obj->insertdependentinfo(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 39){  
	// for new multiplier insert
	$diplay_arr = $finance_display_obj->insertmultiplier(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] > 45){  
	$result['results'] = array();
	$result['error']['code'] = 1;	
	$result['error']['msg'] ='No Such Call';
}

if($params['action'] == 41){  
	$diplay_arr = $finance_display_obj->dependenthandling(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}


if($params['action'] == 34){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->deleteNationalcampaign(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 45){  
	// for pricechart new
	$diplay_arr = $finance_display_obj->newPriceChartAPI(); 
	$result['results'] = array(); 
	$result['error']['code'] = 0;	
	$result['error']['msg'] = $diplay_arr; 
}

if($params['action'] == 46){  
	$result = $finance_display_obj->updateFlexiBudget(); 
}


if($params['action'] == 47){  
	$diplay_arr = $finance_display_obj->GetExistingpackbudget(); 
	$result = $diplay_arr;
}

if($params['action'] == 48){
	$result = $finance_display_obj->gotopaymentPage(); 
}

if($params['action'] == 49){
	$result = $finance_display_obj->tempactualbudgetupdate(); 
}


$resultstr= json_encode($result);

echo ($resultstr);

