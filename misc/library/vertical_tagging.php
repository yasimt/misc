<?
/* Tmegenio/businesss/vertical_popup_msg*/

session_start();
ob_start();

class VerticalTagging
{

private $gi_data_cityval;
function __construct($ucode='',$uname=''){
	if($ucode!=''){
		$this->usercode =$ucode;
	}else{
		$this->usercode = 'CRON';
	}
	
	if($uname!=''){
		$this->username = $uname;
	}else{
		$this->username = 'CRON';
	}
	
	$this->updatedby 	= $this->username." [".$this->usercode."]";
	
	$this->live_auto_tagged_verticals 	  = array(1024,2048,262144,8,16,524288,1048576,2097152,1,2147483648,256,268435456,134217728,8796093022208,549755813888,17592186044416,16777216,281474976710656,1125899906842624,128,4503599627370496);//verticals for which auto tagging logic is implemented/*,1099511627776,2199023255552,32768,64,512,4096,8192,16384,33554432,131072*/
	
	$this->skip_auto_tagged_verticals 	  = array(2,67108864);//verticals for which auto tagging method will be skipped
	
	$this->skip_exclusion_entry_verticals = array(1,8,128,32768,8796093022208,549755813888,17592186044416,16777216,281474976710656,1125899906842624,4503599627370496);//these verticals will not be cheked in exclusion - entry will be checked for last deactivated status
	
	$this->skip_sub_type_flags 			  = array(4194304, 67108864);//skip auto tagging for contracts having any of these sub type flags
	
	$this->verticals_with_sp_bform		  = array(8,16,524288,1048576,1,256,17592186044416,128);//verticals for which sp bform will be populated/*1099511627776,2199023255552,32768,64,512,4096,16384,8192,33554432,131072*/
	
	
}
function updateVerticalTagging($parentid,$vertical_list_arr,$deactiveVerticalArr,$deactiveShopfront,$conn_finance,$conn_local,$server_city,$compmaster_obj,$conn_iro)
{
	//echo "<pre>"; print_r($vertical_list_arr);print_r($deactiveVerticalArr);die();
	$resstr = '';
	/*if($_SESSION['ucode']!=''){
		$this->usercode = $_SESSION['ucode'];
	}else{
		$this->usercode = 'CRON';
	}
	if($_SESSION['uname']!=''){
		$this->username = $_SESSION['uname'];
	}else{
		$this->username = 'CRON';
	}*/
	
	$pidFinanceArr = array();
	$pidFinanceArr = $this->checkFinance($parentid,$conn_finance);
	
	$companyclosedown = false;
	$companyclosedown = $this->checkClosedownFlag($parentid,$compmaster_obj);
	
	$shopfrontapi = '';
	$verticalCounter = "1";
	
	$mobileFeedbackFlag = false;
	$mobileFeedbackFlag =  $this->checkMobileFeedback($parentid,$compmaster_obj);
	
	//echo 'flag ::  <pre>'.$deactiveShopfront;
	//print_r($vertical_list_arr);
		
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Deactive vertical :: ".json_encode($deactiveVerticalArr) ." User Code ::".$this->usercode." Datacity ::".$this->datacity);
	foreach($vertical_list_arr as $vertical_value => $verticalArr)
	{

		
		if(in_array($verticalArr, $this->skip_auto_tagged_verticals) ||  (!in_array($verticalArr,$this->skip_exclusion_entry_verticals) && $this->isInExclusionList($parentid,$verticalArr,$conn_iro) && $deactiveShopfront) || (in_array($verticalArr,$this->skip_exclusion_entry_verticals) && $this->isInDeactivatedList($parentid,$verticalArr,$conn_iro) && $deactiveShopfront))
		{
			continue;
		}
		
		//echo "<br>-21---".$vertical_value."==".$verticalArr;|| (!$deactiveShopfront && count($this->getPreviousVertical($parentid,$conn_iro,$compmaster_obj))>0)
		$iro_active_flag_api = 0;
		$web_active_flag_api = 0;
		$testdriveAPI ='';
		$testdriveapi = '';
		$deactivate_flag = 0;
		$TTcurlOutput ='';
		$rsvn_web_data = '';
		$rsvn_reason = 'GENIOAUTO';
		$rsvn_url = '';
		$url 	   ='';
		$rsvnWebcurlOutput ='';
		$manual_deactive = false;
		/*$vertical_info_arr = array();
		$vertical_info_arr = $this->getVerticalDetails($vertical_value);*/
		
		if(APP_LIVE == 1)
		{
			$rsvn_web_url =  "http://".WEB_SERVICES_API."/web_services/rsvnActivate.php";
		}else
		{
			$rsvn_web_url =  "http://sunnyshende.jdsoftware.com/web_services/web_services/rsvnActivate.php";
		}
		
		//echo"<pre>verticalArr--"; print($verticalArr);
		//if(!empty($vertical_value) && !empty($verticalArr) && ((in_array($verticalArr,array(32,64,128,512,1024,2048,4096,16384,32768,131072,262144,8192,33554432,8,16,524288,1048576)) && $deactiveShopfront==1) || $deactiveShopfront==0))
		// removing shopfront or vertical 32 from if and putting into else part so it will be handle indipendently 
		if(!empty($vertical_value) && !empty($verticalArr) && ((in_array($verticalArr,$this->live_auto_tagged_verticals) && $deactiveShopfront==1) || $deactiveShopfront==0))
		{
			
			//$vertical_type_flag = $vertical_info_arr['vertical_flag'];
			$vertical_type_flag = $verticalArr;
			$docid = $this->docid_creator($conn_local,$parentid,$compmaster_obj);
			
			$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," DOC id :: ".$docid);

			if(trim($docid)==null)
			{
				return; // docid not found return
			}
			
			/*if($verticalArr=='32'){
				$sf_deactive_quey="SELECT docid from tbl_sf_deactive where docid='".$docid."' and data_city='".$this->gi_data_cityval."'";
				$sf_res = $conn_sf->query_sql($sf_deactive_quey);
				
				if($sf_res && mysql_num_rows($sf_res)>0)
				{
					array_push($deactiveVerticalArr,$verticalArr);
				}
				//return;// it is deactive contract so no need to call api for activation
			}*/
			
			$shopMsg = false;
			
			
		
			if($deactiveShopfront && !in_array($verticalArr,$deactiveVerticalArr)){
				$iro_active_flag_api = 1; $web_active_flag_api =1; $process_flag = 1;
				$flagStr = "&active_flag=1&web_active_flag=1&iro_active_flag=1&updatedby=".urlencode($this->updatedby);
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," vertical is active . Vertical Flag :: ".$verticalArr);
				if($verticalArr==32){
					$shopfrontapi = 'docid='.$docid.'&active=1&updatedby='.$this->usercode.'&data_city='.urlencode($this->datacity);
				}elseif(in_array($verticalArr,$this->verticals_with_sp_bform)){
					$testdriveapi = 'parentid='.$parentid.'&data_city='.urlencode($this->datacity).'&module=CS&vertical='.$verticalArr.'&usercode='.$this->usercode;
				}
					$rsvn_web_data = 'docid='.$docid.'&active_flag=1&iro_active_flag=1&web_active_flag=1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
			}else{
				$deactivate_flag = 1;
				if(in_array($verticalArr,array('4096','16384','8192','33554432'))){
					if($pidFinanceArr['paid_flag']=='1' && $pidFinanceArr['expired_flag']=='1' && !$companyclosedown){
						$flagStr = "&active_flag=1&web_active_flag=1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
						$rsvn_web_data = 'docid='.$docid.'&active_flag=1&iro_active_flag=-1&web_active_flag=1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
					}else{
						$flagStr = "&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
						$rsvn_web_data = 'docid='.$docid.'&active_flag=-1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
					}
				}else{
					$flagStr = "&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
					$rsvn_web_data = 'docid='.$docid.'&active_flag=-1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
				}
				
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," vertical is deactive . Vertical Flag :: ".$verticalArr);
				
			}
			
			if($verticalArr==32){
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," SHOPFRONT API CALLED IN FLOW. API OUTPUT :: ".intval($Active_flag_shopfront));
				if(intval($Active_flag_shopfront)!==1){
					$shopMsg = true;
				}
			}
			
			$temparr_check	= array();
			$fieldstr_check	= " parentid,companyname,IF(type_flag&".$verticalArr."=".$verticalArr.",1,-1) as active_flag,data_city ";
			$tablename_check= "tbl_companymaster_extradetails";
			$wherecond_check= "parentid='".$parentid."'";
			$temparr_check		= $compmaster_obj->getRow($fieldstr_check,$tablename_check,$wherecond_check);
			
			$narration_url = '';
			$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," TAGGING URL :: ".DE_CS_APP_URL." APP URL ::".APP_URL." server city : ".$server_city);
			
			if(defined('APP_URL') && APP_URL!='' && APP_URL!='APP_URL' && strstr(APP_URL, '.')){
					if($testdriveapi!='' && in_array($verticalArr,$this->verticals_with_sp_bform)){
						$testdriveAPI = APP_URL."/api/update_vertical_bform.php?".$testdriveapi;
						
						$TTcurlOutput = $this->curlcall($testdriveAPI);
						if($iro_active_flag_api)
						{
							$iro_active_flag_api = $TTcurlOutput; $web_active_flag_api =$TTcurlOutput; $process_flag = $TTcurlOutput;
						}
						if($TTcurlOutput==1 || $TTcurlOutput==2 || $deactivate_flag == 1){
							if($TTcurlOutput == 1)
							{
								$log_url     = APP_URL."/api/update_searchplus_tagging.php?parentid=".$parentid."&doc_id=".$docid."&usercode=".$this->usercode."&data_city=".$this->datacity."&module=cs&auto=1&vertical=".$verticalArr;
								$log_url_res = $this->curlcall($log_url);
								$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call log url for paid verticals :: ".$log_url ." Curl output ::".$TTcurlOutput."(success)");
							}
							$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call test drive bform url :: ".$testdriveAPI ." Curl output ::".$TTcurlOutput."(success)");
							$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
							$url = APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
						}else{
							if(($verticalArr == '8' || $verticalArr == '17592186044416')  && $TTcurlOutput == '-1')
							{
								$rsvn_reason ='GENIO AUTO - Menu Not Active';
								$flagStr = "&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
								$rsvn_web_data = 'docid='.$docid.'&active_flag=-1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
								$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
								$url = APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
							
							}		
							$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call test drive bform url :: ".$testdriveAPI ." Curl output ::".$TTcurlOutput."(unsuccess)");
						}
					}else{
						
						if(!$deactivate_flag && $temparr_check['numrows']>0 && trim($temparr_check['data']['0']['active_flag']) != '1')
						{
							$log_url     = APP_URL."/api/update_searchplus_tagging.php?parentid=".$parentid."&doc_id=".$docid."&usercode=".$this->usercode."&data_city=".$this->datacity."&module=cs&auto=1&vertical=".$verticalArr;
							$log_url_res = $this->curlcall($log_url);
								$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call log url for onpaid verticals :: ".$log_url ." Curl output ::".$TTcurlOutput."(success)");
						}
						
						
						$url = APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
						$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
					}
					$narration_url = APP_URL."/api/fetch_update_narration.php";
			}else{ 
				if($testdriveapi!='' && in_array($verticalArr,$this->verticals_with_sp_bform) && !$manual_deactive){
					$testdriveAPI = "http://".DE_CS_APP_URL."/api/update_vertical_bform.php?".$testdriveapi;
					$TTcurlOutput = $this->curlcall($testdriveAPI);
					if($iro_active_flag_api)
					{
						$iro_active_flag_api = $TTcurlOutput; $web_active_flag_api =$TTcurlOutput; $process_flag = $TTcurlOutput;
					}
					if($TTcurlOutput==1 || $TTcurlOutput==2 || $deactivate_flag == 1){
						
						if($TTcurlOutput == 1)
						{
							$log_url     = DE_CS_APP_URL."/api/update_searchplus_tagging.php?parentid=".$parentid."&doc_id=".$docid."&usercode=".$this->usercode."&data_city=".$this->datacity."&module=cs&auto=1&vertical=".$verticalArr;
							$log_url_res = $this->curlcall($log_url);
							$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call log url for paid verticals :: ".$log_url ." Curl output ::".$TTcurlOutput."(success)");
						}
						
						$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call test drive bfor url :: ".$testdriveAPI ." Curl output ::".$TTcurlOutput."(success)");
						$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
						$url = "http://".DE_CS_APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
					}else{
						if(($verticalArr == '8' || $verticalArr == '17592186044416') && $TTcurlOutput == '-1')
							{
								$rsvn_reason ='GENIO AUTO - Menu Not Active';
								$flagStr = "&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
								$rsvn_web_data = 'docid='.$docid.'&active_flag=-1&iro_active_flag=-1&web_active_flag=-1&vertical_type_flag='. $verticalArr.'&updatedby='.$this->usercode.'&remarks='.$rsvn_reason.'';
								$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
								$url = "http://".DE_CS_APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
								
							}	
							
						$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call test drive bfor url :: ".$testdriveAPI ." Curl output ::".$TTcurlOutput."(unsuccess)");
					}
				}else{
					
					if(!$deactivate_flag && $temparr_check['numrows']>0 && trim($temparr_check['data']['0']['active_flag']) != '1')
					{
						$log_url     = APP_URL."/api/update_searchplus_tagging.php?parentid=".$parentid."&doc_id=".$docid."&usercode=".$this->usercode."&data_city=".$this->datacity."&module=cs&auto=1&vertical=".$verticalArr;
						$log_url_res = $this->curlcall($log_url);
							$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call log url for onpaid verticals :: ".$log_url ." Curl output ::".$TTcurlOutput."(success)");
					}
					
					$rsvn_url = $rsvn_web_url."?".$rsvn_web_data;
					$url = "http://".DE_CS_APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=".$verticalArr.$flagStr;
				}
				$narration_url = "http://".DE_CS_APP_URL."/api/fetch_update_narration.php";
			}
			
			$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," url:: ".$url);
	
			if(!$shopMsg){
				$curlOutput = $this->curlcall($url);
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," VerticalCounter:".$verticalCounter." vertical : ".$verticalArr." Curl active/deactive url run. ".$curlOutput ." shopmsg flag :".intval($shopMsg));
				if(!$manual_deactive){
					$rsvnWebcurlOutput = $this->curlcall($rsvn_url);
					//$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," VerticalCounter:".$verticalCounter." vertical : ".$verticalArr." Rsvn Web Curl active/deactive url run. ".$rsvn_url ." Curl Output : ".$rsvnWebcurlOutput." shopmsg flag :".intval($shopMsg))."";
					$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," VerticalCounter:".$verticalCounter." vertical : ".$verticalArr." Rsvn Web Curl active/deactive url run. ".$rsvn_url ." shopmsg flag :".intval($shopMsg))."";
				}else{
					$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," VerticalCounter:".$verticalCounter." vertical : ".$verticalArr." Rsvn Web Curl active/deactive url not called because Its MANUALLY DEACTIVE VERTICAL. Manual deactive :".intval($manual_deactive) ." shopmsg flag :".intval($shopMsg))."";
				}
			}else{
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," VerticalCounter:".$verticalCounter." vertical : ".$verticalArr." Curl active/deactive url not run. shopmsg flag :".intval($shopMsg));
			}
			
			$vertical_info_arr = array();
			$vertical_info_arr = $this->fn_fetch_vertical_info($vertical_type_flag,$conn_local); 
			if($vertical_info_arr['vertical_name'] !='' && $narration_url!='')
			{
				$process_msg = '';
				$iro_narration_msg = '';
				$web_narration_msg = '';
				//$iro_narration_msg = ($iro_active_flag_api == 1) ? 'Activated' : (($iro_active_flag_api == -1) ? $resaon_txt='Not Eligible (Mobile feedback not present)':(($iro_active_flag_api == -2)?$resaon_txt='Not Eligible (Hours of operation not present)':'De-Activated'));
				
				$iro_narration_msg = ($iro_active_flag_api == 2) ? 'Already Activated': (($iro_active_flag_api == 1) ? 'Activated' : (($iro_active_flag_api == -1) ? $resaon_txt='Not Eligible (Mobile feedback not present)':(($iro_active_flag_api == -2)?$resaon_txt='Not Eligible (Hours of operation not present)':'De-Activated')));
				
				$web_narration_msg = ($web_active_flag_api == 2)? 'Already Activated' : (($web_active_flag_api == 1) ? 'Activated' : (($web_active_flag_api == -1) ? $resaon_txt='Not Eligible (Mobile feedback not present)':(($web_active_flag_api == -2)?$resaon_txt='Not Eligible (Hours of operation not present)':'De-Activated')));
				
				//$process_msg = ($process_flag == 1) ? 'Auto Activation' : 'Auto De-Activation';
				
				$process_msg = 'Genio : Auto Tagging';
				//($process_flag == 1) ? 'Activated' : (($process_flag == -1) ? $resaon_txt=$resaon_txt.'(Mobile feedback not present)':(($process_flag == -2)?$resaon_txt=$resaon_txt.'(Hours of operation not present)':'De-Activated'));
				
				//$narration  = "Vertical Name : ".$vertical_info_arr['vertical_name']." IRO : ".$iro_narration_msg." WEB : ".$web_narration_msg;
				$narration  = "\n"."Process : Deal Close ".$process_msg."\n"."Vertical Name : ".$vertical_info_arr['vertical_name']."\n"."IRO  : ".$iro_narration_msg."\n"."WEB : ".$web_narration_msg;
				//$narration	= nl2br($narration);
				$insert_narration_API = $narration_url.'?parentid='.$parentid.'&data_city='.urlencode($this->datacity).'&narration='.urlencode($narration).'&campaignid='.$vertical_info_arr['campaignid'].'&module=CS&action=2&ucode='.$this->usercode.'&uname='.urlencode($this->username);
				$call_narration_api		=	$this->call_vertical_status_api($insert_narration_API);
				//if($vertical_type_flag==512){die("Vertical->".$vertical_type_flag."<br>narration url=".$narration_url."<br>arr=".json_encode($vertical_info_arr));}
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Narration log Vertical :".$vertical_type_flag." Narrationa Url:".$insert_narration_API." fetch Array:".json_encode($vertical_info_arr)."Curl Output :".$call_narration_api."");
			}else{
				$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Narration log Vertical :".$vertical_type_flag." Narrationa Url:".$insert_narration_API." fetch Array:".json_encode($vertical_info_arr)." Curl Output :".$call_narration_api." (narration url is not run)");
			}
			$verticalCounter++;
		}elseif($verticalArr==32) // only for shopfront tagging and untagging pramesh
		{
			$tbl_sf_deactive_flag=0;
			$shopfront_finance_autotaggineligible=0;
			$nonpaid_allowedcontract_flag=0;
			
			$docid = $this->docid_creator($conn_local,$parentid,$compmaster_obj);
			//$this->activateShopfront($parentid,$docid,1);return;			
			$finance_flag = -1;
			$nonpaid_flag = -1;
			$brandname_flag = -1;
			if(in_array(32,$deactiveVerticalArr))
			{
				
				$active_flag_chk = $this->getExisitingActiveFlag($parentid);
				if($active_flag_chk != -1)				
				{
					$this->deactiveShopfront($parentid,$docid);
					$this->narrationInsertionforSF('SF vertical deactivated' ,$parentid);
					$this->autotagging_shopfront_logs($parentid,'SF vertical deactivated as category not found',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
				}
				else
				{
					$this->autotagging_shopfront_logs($parentid,'SF vertical already deactivated',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
				}
				continue;
			}
				
				if(APP_LIVE == 1)
				{
					$sf_info_url     =  "http://192.168.20.105:1080/services/opt/sf_ApiResponse.php?docid=".$docid."&parentid=".$parentid."&data_city=".$this->gi_data_cityval;
					$sf_info_res = $this->curlcall_timeout_chk($sf_info_url);
				}
				else
				{
					$sf_info_url     = "http://rohnyshende.jdsoftware.com/QUICK_SERVICES/services/opt/sf_ApiResponse.php?docid=".$docid."&parentid=".$parentid."&data_city=".$this->gi_data_cityval."&development=1";
					$sf_info_res = $this->curlcall_timeout_chk($sf_info_url);
				}
				
				$CurlData 	= json_decode($sf_info_res['data'], true);
				$error_code = $sf_info_res['curl_error'];
				
				$resul = $CurlData['result'];
				$sf_proceed_flag = 1;
				
				if($error_code == 28)
				{
					$sf_proceed_flag = 0;
				}
				else if($CurlData['result'] != 'Data Fetched Successfully')
				{
					$sf_proceed_flag = 0;
				}

				if($sf_proceed_flag == 0)
				{
					$this->insertDataToLogTbl($parentid,$docid,$this->gi_data_cityval,$CurlData['result'],$error_code,$conn_local);					
					continue ;
				}
				
				if($sf_proceed_flag == 1)
				{
					$sf_deactive_flag = $CurlData['sf_deactive_flag'];
					$sf_opted_Flag 	  = $CurlData['sf_opted_Flag'];
					
					if($sf_deactive_flag)
					{
						
						$active_flag_chk = $this->getExisitingActiveFlag($parentid);
						if($active_flag_chk != -1)
						{
							$this->narrationInsertionforSF('contract present in deactive table' ,$parentid);
							$this->deactiveShopfront($parentid,$docid);
							$this->autotagging_shopfront_logs($parentid,'contract present in deactive table',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
						}
						else
						{
							$this->autotagging_shopfront_logs($parentid,'contract already present in deactive table',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
						}
						continue;
					}
					
					/*if($sf_opted_Flag)
					{
						$nonpaid_allowedcontract_flag=1;
					}*/
							
					$Financearray = $this->checkFinance($parentid,$conn_finance);			
					if($Financearray['paid_flag']==1 && $Financearray['expired_flag'] == '0')
					{
						$shopfront_finance_autotaggineligible=1;	
					}		
					
					/*(if($nonpaid_allowedcontract_flag==0)
					{
						
						if(APP_LIVE == 1)
						{
							$params_sf_url = VERTICAL_API."sf_info.php?docid=".$docid."&type_flag=32&formate=basic";
						}else
						{
							$params_sf_url = "http://rohnyshende.jdsoftware.com/web_services/web_services/web_services/sf_info.php?docid=".$docid."&type_flag=32&formate=basic";
						}
										
						$response = $this->curlcall($params_sf_url);
						$responsearr = json_decode($response,true);
						$responsedocid= $responsearr['results']['compdetails']['docid'];
						if($responsedocid!=null && strlen($responsedocid)>3)
						{
							$nonpaid_allowedcontract_flag=1;
						}
						
					}*/
					
					if($shopfront_finance_autotaggineligible)
					{
						$finance_flag = 1;
					}
					if(strlen($responsedocid)>3)
					{
						$nonpaid_flag ='response from API - 1';
					}
					elseif($sf_opted_Flag)
					{
						$nonpaid_flag = 'Sf_opted_flag - 1';
					}
					else
					{
						$nonpaid_flag = -1;
					}
					//checking for brand_name
					$brand_flg_chk = $this->isShopFrontCategoryPresent($parentid,$compmaster_obj,$conn_local);
					if($brand_flg_chk)
					{
						$brandname_flag = 1;
					}
					
					// either it is elligible nopaid contract or paid contract with category for SF so we will activate 
					if(($shopfront_finance_autotaggineligible==1)  && ($brandname_flag == 1))
					{
						$firsttimeautotagging=null;
						$temparr_check	= array();
						$fieldstr_check	= " parentid,companyname,IF(type_flag&".$verticalArr."=".$verticalArr.",1,-1) as active_flag,data_city ";
						$tablename_check= "tbl_companymaster_extradetails";
						$wherecond_check= "parentid='".$parentid."'";
						$temparr_check		= $compmaster_obj->getRow($fieldstr_check,$tablename_check,$wherecond_check);
						
						if($temparr_check['numrows']>0 && trim($temparr_check['data']['0']['active_flag']) != '1')
						{
							$firsttimeautotagging=1;
						}				
						
						$active_flag_chk = $this->getExisitingActiveFlag($parentid);
						if($active_flag_chk != 1)
						{
							$this->activateShopfront($parentid,$docid,$firsttimeautotagging);
							 $this->narrationInsertionforSF('SF vertical activated' ,$parentid);
							$this->autotagging_shopfront_logs($parentid,'SF vertical activated',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
						}
						else
						{
							$this->autotagging_shopfront_logs($parentid,'SF vertical already activated',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local);
						}
						unset($temparr_check);
					}else // not satisfy any activation condition so making is deactive
					{ 
						 $active_flag_chk = $this->getExisitingActiveFlag($parentid);
						 if($active_flag_chk != -1)
						 {
							$this->narrationInsertionforSF('SF vertical deactivated' ,$parentid);
						    $this->deactiveShopfront($parentid,$docid);
							$this->autotagging_shopfront_logs($parentid,'SF vertical deactivated',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local); 
						 }
						 else
						 {
							 $this->autotagging_shopfront_logs($parentid,'SF vertical already deactivated - not eligible',$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local); 
						 }
					}
				}	
				
			}
		}
			
}

function narrationInsertionforSF($message, $parentid)
{
$narration_url = "http://".DE_CS_APP_URL."/api/fetch_update_narration.php";
$process_msg = 'Genio : Auto Tagging';
//($process_flag == 1) ? 'Activated' : (($process_flag == -1) ? $resaon_txt=$resaon_txt.'(Mobile feedback not present)':(($process_flag == -2)?$resaon_txt=$resaon_txt.'(Hours of operation not present)':'De-Activated'));

//$narration  = "Vertical Name : ".$vertical_info_arr['vertical_name']." IRO : ".$iro_narration_msg." WEB : ".$web_narration_msg;
$narration  = "\n"."Process : Deal Close ".$process_msg."\n"."Vertical Name : SF \n Message:-".$message;
//$narration	= nl2br($narration);
$insert_narration_API = $narration_url.'?parentid='.$parentid.'&data_city='.urlencode($this->datacity).'&narration='.urlencode($narration).'&campaignid=29&module=CS&action=2&ucode='.$this->usercode.'&uname='.urlencode($this->username);
$call_narration_api		=	$this->call_vertical_status_api($insert_narration_API);	
}

function deactiveShopfront($parentid,$docid)
{
	$rsvn_reason = 'GENIOAUTO';
	$flagStr = "&active_flag=-1&web_active_flag=-1&iro_active_flag=-1&updatedby=".urlencode($this->updatedby);
	$url = APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=32".$flagStr;
	$this->curlcall($url);


	$shopfrontapi = 'docid='.$docid.'&active=-1&updatedby='.$this->usercode.'&data_city='.urlencode($this->datacity);
	//$Active_flag_shopfront =	$this->Active_flag_shopfront($shopfrontapi);
	//$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," SHOPFRONT API CALLED IN FLOW. API OUTPUT :: ".intval($Active_flag_shopfront));
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," vertical is deactive . Vertical Flag :: 32");
}

function activateShopfront($parentid,$docid,$firsttimeautotagging=null)
{	
	$rsvn_reason = 'GENIOAUTO';	
	$flagStr = "&active_flag=1&web_active_flag=1&iro_active_flag=1&updatedby=".urlencode($this->updatedby);
	$url = APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=2&request=AutoTag&dept=CS&vertical_type_flag=32".$flagStr;
	$this->curlcall($url);
	
	$shopfrontapi = 'docid='.$docid.'&active=1&updatedby='.$this->usercode.'&data_city='.urlencode($this->datacity);
	$Active_flag_shopfront =	$this->Active_flag_shopfront($shopfrontapi);
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," SHOPFRONT API CALLED IN FLOW. API OUTPUT :: ".intval($Active_flag_shopfront));
	
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," vertical is Active . Vertical Flag :: 32");
	
	if($firsttimeautotagging)	
	{
		$log_url     = APP_URL."/api/update_searchplus_tagging.php?parentid=".$parentid."&doc_id=".$docid."&usercode=".$this->usercode."&data_city=".$this->datacity."&module=cs&auto=1&vertical=32";
		$log_url_res = $this->curlcall($log_url);
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Call log url for paid verticals :: ".$log_url ." Curl output ::".$TTcurlOutput."(success)");
	}
	
}

function getVerticalDetails($vertical_val)
{
	$vertical_arr = array();
	switch($vertical_val)
	{
		case 1:
			$vertical_arr['vertical_name'] = 'Food Ordering Service';
			$vertical_arr['vertical_flag'] = '8';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 2 :
			$vertical_arr['vertical_name'] = 'Wine Ordering Service';
			$vertical_arr['vertical_flag'] = '16';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 3 :
			$vertical_arr['vertical_name'] = 'Shop Front Service';
			$vertical_arr['vertical_flag'] = '32';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 4 :
			$vertical_arr['vertical_name'] = 'Vehicle service';
			$vertical_arr['vertical_flag'] = '512';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;	
		case 5 : 
			$vertical_arr['vertical_name'] = 'Pharmacy Ordering Service';
			$vertical_arr['vertical_flag'] = '32768';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 7 :
			$vertical_arr['vertical_name'] = 'Grocery Ordering Service';
			$vertical_arr['vertical_flag'] = '128';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 9 :
			$vertical_arr['vertical_name'] = 'Laundry Ordering Service';
			$vertical_arr['vertical_flag'] = '64';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 11 :
			$vertical_arr['vertical_name'] = 'Doctor Service';
			$vertical_arr['vertical_flag'] = '2';
			$vertical_arr['doc_hosp_flag'] = '1';				// Used to differentiate Doctor and Hospital 1-Doctor 2-Hospital
			break;
		case 20 :
			$vertical_arr['vertical_name'] = 'Courier Service';
			$vertical_arr['vertical_flag'] = '8192';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
		case 19 :
			$vertical_arr['vertical_name'] = 'Flower Service';
			$vertical_arr['vertical_flag'] = '262144';
			$vertical_arr['doc_hosp_flag'] = '0';
			break;
	}
	return $vertical_arr;
}
function call_vertical_status_api($curl_url)
{	
	$ch = curl_init($curl_url);
	$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 10);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$resstr = curl_exec($ch);
	curl_close($ch);
	return $resstr;
}

function getCompanymasterData($parentid,$compmaster_obj){
	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "parentid,data_city,landline,mobile,tollfree,pincode,companyname ";
	$tablename		= "tbl_companymaster_generalinfo";
	$wherecond		= "parentid = '".$parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	if($temparr['numrows']>0)
	{
		$companymasterDataRow= $temparr['data']['0'];
		$this->datacity    = $companymasterDataRow['data_city'];
		
		if($companymasterDataRow['landline'] || $companymasterDataRow['mobile'])
		$this->contact_number_present  = 1;
		
		if($companymasterDataRow['mobile'])
		$this->mobile_number_present  = 1;
		
		
		if($companymasterDataRow['pincode'])
		$this->pincode_present  = 1;
		
		if($companymasterDataRow['companyname'])
		$this->company_name  = $companymasterDataRow['companyname'];
		
	}
}

function eligibleSpaVertical()
{
	if($this->company_name)
	{
		$company_name_array = explode(" ",strtolower($this->company_name));
		$spa_string_array = array("spa","salon","beauty");
		if(count(array_intersect($company_name_array,$spa_string_array))>0){
			return true;
		}else{
			return false;
		}
		
	}
}

function isInExclusionList($parentid,$vertical_type_flag,$conn_iro)
{
	$vertical_type_flag_condn = '';
	if(intval($vertical_type_flag) > 0)
	{
		$vertical_type_flag_condn = " AND vertical_type_flag = '".$vertical_type_flag."'";
	}
	$sql = "SELECT parentid FROM tbl_exclusion_list_verticals WHERE parentid='".$parentid."' ".$vertical_type_flag_condn."";
	$res = $conn_iro ->query_sql($sql);
	if($res && mysql_num_rows($res)>0)
	{
		return true;
	}
	else
	{
		return false;
	}
	
}

function isInDeactivatedList($parentid,$vertical_type_flag,$conn_iro)
{
	$vertical_type_flag_condn = '';
	if(intval($vertical_type_flag) > 0)
	{
		$vertical_type_flag_condn = " AND business_type_flag = '".$vertical_type_flag."'";
	}
	$sql = "SELECT parentid,iro_active_flag,web_active_flag FROM tbl_business_activation_instant_log WHERE parentid='".$parentid."' ".$vertical_type_flag_condn." ORDER BY updatedOn DESC LIMIT 1";
	$res = $conn_iro ->query_sql($sql);
	if($res && $conn_iro->numRows($res)>0)
	{
		$row = $conn_iro->fetchData($res);
		if($row['iro_active_flag'] == '-1' && $row['web_active_flag'] == '-1')
		return true;
		else
		return false;
	}
	else
	{
		return false;
	}
	
}

function isShopFrontCategoryPresent($parentid,$compmaster_obj,$conn_local)
{
	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "catidlineage,catidlineage_nonpaid";
	$tablename		= "tbl_companymaster_extradetails";
	$wherecond		= "parentid = '".$parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

	if($temparr['numrows'])
	{

		$selectCheckCatRow = $temparr['data']['0'];

		if((isset($selectCheckCatRow['catidlineage']) && $selectCheckCatRow['catidlineage'] != '' ) || (isset($selectCheckCatRow['catidlineage_nonpaid']) && $selectCheckCatRow['catidlineage_nonpaid'] != ''))
		{
			$nonpaidCatlist = array();
			$catidsList 	= 	str_replace("/","",$selectCheckCatRow['catidlineage']);
			$catidsList     =   explode(',',$catidsList);
			$paidCatarr = array();
			$paidCatarr = array_merge(array_filter($catidsList));
			$selectCheckCatRow['catidlineage_nonpaid'] = str_replace("/","",$selectCheckCatRow['catidlineage_nonpaid']);
			
			$nonpaidCatlist = explode(",",$selectCheckCatRow['catidlineage_nonpaid']);
			$nonpaidCatlist = array_merge(array_filter($nonpaidCatlist));
			$totalCatarr =  array_merge($paidCatarr,$nonpaidCatlist);
			$totalCatarr = array_merge(array_filter($totalCatarr));
			$selectCatBrand_flag	= "SELECT catid, brand_name FROM d_jds.tbl_categorymaster_generalinfo WHERE catid IN ('".implode("','",$totalCatarr)."')  AND  brand_name !='' AND display_product_flag&2=2 LIMIT 1";	
			$ResBrand_flag	=	$conn_local->query_sql($selectCatBrand_flag);
			$cn             =   mysql_num_rows($ResBrand_flag);			
			if($cn)
			{
				$brand_flg_chk = 1;
			}
			else
			{
				$brand_flg_chk = 0;
			}
		}
	}
	return $brand_flg_chk;
}

function isFlowersCategoryPresent($parentid,$compmaster_obj,$conn_iro){

	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "catidlineage,catidlineage_nonpaid";
	$tablename		= "tbl_companymaster_extradetails";
	$wherecond		= "parentid = '".$parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

	if($temparr['numrows'] > 0)
	{
		$selectCheckCatRow = $temparr['data']['0'];
		if((isset($selectCheckCatRow['catidlineage']) && $selectCheckCatRow['catidlineage'] != '' ) || (isset($selectCheckCatRow['catidlineage_nonpaid']) && $selectCheckCatRow['catidlineage_nonpaid'] != ''))
		{
			$nonpaidCatlist = array();
			$catidsList = array();
			$catidsList = explode("/",$selectCheckCatRow['catidlineage']);
			$catidsList = array_merge(array_filter($catidsList));
			if(count($catidsList)>0){
				foreach($catidsList as $key => $value){
					$output = '';
					$output = preg_replace('/[^0-9]/', '', $value);
					$catidsList[$key]=$output;
				}
			}
			$catidsList =  array_merge(array_filter($catidsList));
			
			$nonpaidCatlist = explode("/",$selectCheckCatRow['catidlineage_nonpaid']);
			$nonpaidCatlist = array_merge(array_filter($nonpaidCatlist));
			if(count($nonpaidCatlist)>0){
				foreach($nonpaidCatlist as $catkey => $catval){
					$output = '';
					$output = preg_replace('/[^0-9]/', '', $catval);
					$nonpaidCatlist[$catkey]=$output;
				}
			}
			$nonpaidCatlist = array_merge(array_filter($nonpaidCatlist));
			
			$totalCatarr =  array_merge($catidsList,$nonpaidCatlist);
			$totalCatarr = array_merge(array_filter($totalCatarr));
			
			$selectCatSql	=	"SELECT count(catid) as cnt from d_jds.tbl_categorymaster_generalinfo WHERE active_flag=1 and display_product_flag&1048576=1048576 AND catid IN ('".implode("','",$totalCatarr)."') GROUP BY catid";
			$selectCatRes	=	$conn_iro->query_sql($selectCatSql);
			$selectCatRow	=	mysql_fetch_assoc($selectCatRes);
			if($selectCatRow['cnt'] > 0){
				return true;
			}else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	else
	{
		return false;
	}
}
function checkClosedownFlag($parentid,$compmaster_obj){
	if($parentid!=''){
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "parentid,closedown_flag,freeze,mask";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid = '".$parentid."' AND ((closedown_flag>0 AND closedown_flag!=6) OR (freeze=1 OR mask=1))";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		if($temparr['numrows']>0){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function getSubTypeFlag($parentid,$compmaster_obj)
{
	$this->sub_type_flag =0;
	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "sub_type_flag";
	$tablename		= "tbl_companymaster_extradetails";
	$wherecond		= "parentid = '".$parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	if($temparr['numrows'])
	{
		$row = $temparr['data']['0'];
		$this->sub_type_flag = $row['sub_type_flag'];
	}
	
}

function checkMobileFeedback($parentid,$compmaster_obj){
	$mobileFeedbackArr = array();
	if($parentid!=''){
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "parentid,mobile_feedback";
		$tablename		= "tbl_companymaster_generalinfo";
		$wherecond		= "parentid = '".$parentid."' AND mobile_feedback!=''";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
			if($temparr['numrows']>0){
				$rowmobileFeedback  = $temparr['data']['0'];
				$mobileFeedbackstr = $rowmobileFeedback['mobile_feedback'];
				$mobileFeedbackArr = explode(",",trim($mobileFeedbackstr,","));
				$mobileFeedbackArr = array_merge(array_filter($mobileFeedbackArr));
				if(count($mobileFeedbackArr)>0){
					foreach($mobileFeedbackArr as $key => $mobileval){
						$output = '';
						$output = preg_replace('/[^0-9]/', '', $mobileval);
						$mobileFeedbackArr[$key]=$output;
					}
					$mobileFeedbackArr = array_merge(array_filter($mobileFeedbackArr));
					if(count($mobileFeedbackArr)>0){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}		
	}else{
		return false;
	}
}

function check_Inexpensive_Hotel_Tag($conn_local,$catid_arr,$category_condition)
{
		$query_vertical = "SELECT category_name,catid,add_bform_flag,display_product_flag,category_verticals,rest_price_range 
                                            FROM tbl_categorymaster_generalinfo 
                                             WHERE 
                                            catid IN (" .implode(",",$catid_arr). ") 
                                             ".$category_condition." 
                                            ORDER BY category_name";
		$res_query_vertical = $conn_local->query_sql($query_vertical);
		if($res_query_vertical && mysql_num_rows($res_query_vertical)>0)
		{
			return true;
		}else{
			return false;
		}
	
}


function isLegalCategoryThere($conn_local,$catid_arr,$category_condition)
{
		$query_vertical = "SELECT category_name,catid,add_bform_flag,display_product_flag,category_verticals,rest_price_range 
                                            FROM tbl_categorymaster_generalinfo 
                                            WHERE 
                                            catid IN (" .implode(",",$catid_arr). ") 
                                            AND category_name='Karnataka Banks'";
		$res_query_vertical = $conn_local->query_sql($query_vertical);
		if($res_query_vertical && mysql_num_rows($res_query_vertical)>0)
		{
			return true;
		}else{
			return false;
		}
	
}


function checkExclusionCategories($conn_local,$catid_arr,$category_condition)
{
		$query_vertical = " SELECT * FROM tbl_urbanclap_ignore_category WHERE catid IN ( '" .implode("','",$catid_arr). "' ) ";
		$res_query_vertical = $conn_local->query_sql($query_vertical);
		if($res_query_vertical && mysql_num_rows($res_query_vertical)>0)
		{
			return true;
		}else{
			return false;
		}
	
}


function isInCompetitor($conn_local,$parentid,$category_condition)
{
	$sql_isCompt = "SELECT * FROM db_iro.tbl_vertical_competitor WHERE parentid='".$parentid."' AND active_flag = 1 ".$category_condition." ";
	$res_isCompt = $conn_local->query_sql($sql_isCompt);
	if($res_isCompt && mysql_num_rows($res_isCompt)>0)
	{
		   $this->isInCompetitor = 1;
	}else{
		   $this->isInCompetitor = 0;
	}
}

function IsNumberInSPBform($vertical_type_flag,$parentid,$docid)
{
	if($vertical_type_flag == '8'){//food ordering service
		$url    = REST_WEBSITE."/restDet/".trim($parentid);
		$result = $this->curlcall($url);
		$result_existing_arr = json_decode($result,true);
		
		//echo '<br><pre>res 1 ::';
		//print_r($result_existing_arr);
		
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"FOS URL :: ".$url);
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"FOS Existing Data :: ".$result_existing_arr['results']['gen_info'][0]['mobile_no_trc']." :: ".$result_existing_arr['results']['gen_info'][0]['call_back_number']);
		
		if(trim($result_existing_arr['results']['gen_info'][0]['mobile_no_trc']) || trim($result_existing_arr['results']['gen_info'][0]['call_back_number']))
		$this->sp_jdfos_bform_number = 1;
		
		
		//$result_exising_arr['results']['gen_info'][0]
		
	}else if($vertical_type_flag == '1'){ //table reservation
		$url    = VERTICAL_API."rsvnInfo.php?docid=".$docid."&type_flag=".$vertical_type_flag;
		$result = $this->curlcall($url);
		$result_existing_arr = json_decode($result,true);
		//$result_exising_arr['results']['compdetails']
		
		
		
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"TR URL :: ".$url);
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"TR Existing Data :: ".$result_existing_arr['results']['compdetails']['entity_phone']." :: ".$result_existing_arr['results']['compdetails']['entity_mobile']);
		
		
		if(trim($result_existing_arr['results']['compdetails']['entity_phone']) || trim($result_existing_arr['results']['compdetails']['entity_mobile']))
		$this->sp_tr_bform_number = 1;	
	}
	
}

function IsInChainRest($parentid,$categories_arr,$conn_local)
{
	  $is_in_chain_query  = "SELECT * FROM tbl_vertical_redirection_master WHERE catid IN (" .implode(",",$categories_arr). ")";
	  $is_in_chain_result = $conn_local->query_sql($is_in_chain_query);
	  if($is_in_chain_result && mysql_num_rows($is_in_chain_result)>0) {
		  $this->is_in_chain_rest = 1;	
	  }else {
		  $this->is_in_chain_rest = 0;	
	  }
}

function getVerticalTagging($parentid,$conn_iro,$conn_local,$conn_finance,$compmaster_obj,$conn_finance_national){
	$cat_tag_type_flag = array();
	$check_cat_exist_arr = array();
	$comp_type_flag = array();
	$paidFlag = false;
	
	$this->getCompanymasterData($parentid,$compmaster_obj);
	
	$docid = $this->docid_creator($conn_local,$parentid,$compmaster_obj);
	
	$pidFinanceArr = array();
	$pidFinanceArr = $this->checkFinance($parentid,$conn_finance);
	if($conn_finance_national)
	{
	$qryCheckBalance_national = "select parentid,balance from tbl_companymaster_finance_national where parentid='".$parentid."' and balance>0";
	$resCheckBalance_national = $conn_finance_national->query_sql($qryCheckBalance_national);
	}
	$qryCheckBalance = "select parentid,balance from tbl_companymaster_finance where parentid='".$parentid."' and balance>0";
	$resCheckBalance = $conn_finance->query_sql($qryCheckBalance);
	
	if(($resCheckBalance && mysql_num_rows($resCheckBalance)>0) || ($resCheckBalance_national && mysql_num_rows($resCheckBalance_national)>0)){
		$paidFlag = true;
	}

	$temparr		= array();
	$fieldstr		= '';
	$fieldstr 		= "catidlineage,catidlineage_nonpaid,type_flag";
	$tablename		= "tbl_companymaster_extradetails";
	$wherecond		= "parentid = '".$parentid."'";
	$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
	//echo "<pre>"; print_r($temparr); echo "</pre>";
	if($temparr['numrows'])
	{
		$selectCheckCatRow = $temparr['data']['0'];
		if((isset($selectCheckCatRow['catidlineage']) && $selectCheckCatRow['catidlineage'] != '' ) || (isset($selectCheckCatRow['catidlineage_nonpaid']) && $selectCheckCatRow['catidlineage_nonpaid'] != ''))
		{
			$nonpaidCatlist = array();
			$catidsList = array();
			$catidsList = explode("/",$selectCheckCatRow['catidlineage']);
			$catidsList = array_merge(array_filter($catidsList));
			if(count($catidsList)>0){
				foreach($catidsList as $key => $value){
					$output = '';
					$output = preg_replace('/[^0-9]/', '', $value);
					$catidsList[$key]=$output;
				}
			}
			$catidsList =  array_merge(array_filter($catidsList));
			
			$nonpaidCatlist = explode("/",$selectCheckCatRow['catidlineage_nonpaid']);
			$nonpaidCatlist = array_merge(array_filter($nonpaidCatlist));
			if(count($nonpaidCatlist)>0){
				foreach($nonpaidCatlist as $catkey => $catval){
					$output = '';
					$output = preg_replace('/[^0-9]/', '', $catval);
					$nonpaidCatlist[$catkey]=$output;
				}
			}
			$nonpaidCatlist = array_merge(array_filter($nonpaidCatlist));
			
			$totalCatarr =  array_merge($catidsList,$nonpaidCatlist);
			$totalCatarr = array_merge(array_filter($totalCatarr));
			
			$sql = "SELECT product,value,column_name FROM  tbl_display_product";
			$res = $conn_local->query_sql($sql);  
			while ($row = mysql_fetch_assoc($res)) {//echo "<br>--".$row['value']."==".$row['column_name'];
				/*$vertical_arr[$row['value']]['product']		=	$row['product'];
				$vertical_arr[$row['value']]['column_name']	=	trim($row['column_name']);*/
				$vertical_arr[$row['product']]['value']		=	$row['value'];
				$vertical_arr[$row['product']]['column_name']	=	trim($row['column_name']);
			}
			unset($sql,$res,$row);
			if(count($totalCatarr)>0){
				//$table_reservation_eligibility = 0;
				//$bit_value_tr                  = 0;
				$query_vertical = "SELECT category_name,catid,add_bform_flag,display_product_flag,category_verticals,rest_price_range 
                                            FROM tbl_categorymaster_generalinfo 
                                             WHERE 
                                            catid IN (" .implode(",",$totalCatarr). ") 
                                            and display_product_flag>1
                                            GROUP BY catid 
                                            ORDER BY category_name";
				$con_get_vertical = $conn_local->query_sql($query_vertical);
				while ($res_get_vertical = mysql_fetch_assoc($con_get_vertical)) {
					
					//$bit_value_tr = $res_get_vertical['display_product_flag'] & 134217728;
					foreach($vertical_arr AS $type_flag_value=>$type_flag_name){
						
						//echo"<br>----->". $type_flag_name['column_name']."==".$res_get_vertical[$type_flag_name['column_name']]."==".$type_flag_name['value']."==".($res_get_vertical[$type_flag_name['column_name']]&$type_flag_name['value'])."=====".$type_flag_name['value']."++".$type_flag_value;
						
						if(((int)$res_get_vertical[$type_flag_name['column_name']]&$type_flag_name['value']) == $type_flag_name['value']){
							$cat_tag_type_flag[$type_flag_name['column_name']][$type_flag_name['value']] = $type_flag_value;
						}
						//echo '<br>'.$res_get_vertical['display_product_flag'] & 134217728;
						/*if(($bit_value_tr == '134217728') && in_array(strtolower($res_get_vertical['rest_price_range']),array("moderate","expensive","very expensive"))) {
							$table_reservation_eligibility = 1;
						}*/
						
					}
				}
				
				$this->IsInChainRest($parentid,$totalCatarr,$conn_local);
				
			}
			
			/*product flag as key and type flag as value*/
			$map_catvertical_to_typeflag	=	array("display_product_flag"=>array('2'=>array('32'),'4'=>array('2'),'8'=>array('2'),'16'=>array('16'),'32'=>array('128'),'64'=>array('32768'),'128'=>array('64'),'256'=>array('256'),'1024'=>array('4'),'2048'=>array('512'),'4096'=>array('1024'),'8192'=>array('2048'),'16384'=>array('16384'),'32768'=>array('4096'),'65536'=>array('8192'),'131072'=>array('65536'),'262144'=>array('131072'),'1048576'=>array('262144'),'8388608'=>array('33554432'),'8796093022208'=>array('8'),'4398046511104'=>array('1'),'2097152'=>array('1048576'),'4194304'=>array('524288'),'34359738368'=>array('2097152'),'17179869184'=>array('2147483648'),'256'=>array('256'),'33554432'=>array('268435456'),'67108864'=>array('134217728'),'17592186044416'=>array('1099511627776'),'35184372088832'=>array('2199023255552'),'140737488355328'=>array('8796093022208'),'281474976710656'=>array('549755813888'),'134217728'=>array('17592186044416'),'1073741824'=>array('16777216'),'1125899906842624'=>array('281474976710656'),'4503599627370496'=>array('1125899906842624'),'9007199254740992'=>array('4503599627370496')),"category_verticals"=>array('2'=>array('1')));
				
			if(count($cat_tag_type_flag)>0){
				foreach ($cat_tag_type_flag AS $column_name=>$val){					
					foreach($val AS $product_val=>$product_name){						
						if(isset($map_catvertical_to_typeflag[$column_name][$product_val])){
							/*here check category flag*/
							//echo "<br>-->product name =>".$product_name."= column name==>".$column_name."=product_val".$product_val."===".$map_catvertical_to_typeflag[$column_name][$product_val][0];
							switch($product_val)
							{
								case '1':
								break;
								case '2':
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
									$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '16'://Liquor 
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag && $this->mobile_number_present && $this->pincode_present)//same condition as jdfos has
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '32'://Grocery
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag && $this->pincode_present)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								/*case '64'://Pharmacy
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_n	ame][$product_val][0];
								if($paidFlag && $this->pincode_present)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;*/
								case '128'://Laundry
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '256'://Spa
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								$category_condition ="AND display_product_flag&4=4 ";//checking for doctor categories
								
								if($paidFlag && (!$this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition) || ($this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition) && $this->eligibleSpaVertical())))
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '2048'://Vehicle service
								
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '4096'://bus booking
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '8192'://cab booking
$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag && in_array(strtoupper(trim($this->datacity)),array('DELHI','CHANDIGARH','AMRITSAR','AHMEDABAD','BANGALORE','HYDERABAD','KOLKATA','LUDHIANA','MUMBAI','PUNE')))
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '16384'://AC Service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '32768'://water purifier service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '65536'://courier service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '262144'://Test Drive
								/*$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '1048576'://flower service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '2097152'://sweet service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '4194304'://cake service
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '8388608'://Mineral Water
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '34359738368'://flight booking
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '17179869184'://recharge
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '4398046511104'://table reservation
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								$category_condition ="AND TRIM(LOWER(rest_price_range)) = 'inexpensive' ";
								$this->IsNumberInSPBform($map_catvertical_to_typeflag[$column_name][$product_val][0],$parentid,$docid);
								
								$comp_condition ="AND website_type_flag&1=1";
								$this->isInCompetitor($conn_local,$parentid,$comp_condition);
								
								//$flag = $this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition);
								//echo '<br>'.$this->contact_number_present."==".$this->pincode_present.'=='.$flag;  ||  $this->isInCompetitor
								//if( (($this->contact_number_present || $this->sp_tr_bform_number) && $this->pincode_present ) && !($this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition)))
								if( ($this->isInCompetitor ) && !($this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition)) )
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '8796093022208'://food ordering service
								//if($paidFlag || $pidFinanceArr['paid_flag']=='1' || ($selectCheckCatRow['type_flag'] && (((int)$selectCheckCatRow['type_flag']&$map_catvertical_to_typeflag[$column_name][$product_val][0]) == $map_catvertical_to_typeflag[$column_name][$product_val][0])))
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								$category_condition ="AND category_type&32768=32768 ";
								$comp_condition ="AND website_type_flag&8=8";
								$this->IsNumberInSPBform($map_catvertical_to_typeflag[$column_name][$product_val][0],$parentid,$docid);
								
								$this->isInCompetitor($conn_local,$parentid,$comp_condition);
								
								if(/*($paidFlag  && ($this->mobile_number_present || $this->sp_jdfos_bform_number) && $this->pincode_present && !($this->check_Inexpensive_Hotel_Tag($conn_local,$totalCatarr,$category_condition))) ||*/ $this->isInCompetitor || $this->is_in_chain_rest)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '134217728'://MENU Vertical - checking Restaurant flag  only
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '33554432'://loan - only nonpaid
								//if(!$this->isLegalCategoryThere($conn_local,$totalCatarr,$category_condition))
									//$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
									
								if(!$paidFlag /*&& $pidFinanceArr['paid_flag']== '0' && $pidFinanceArr['expired_flag'] == '0'*/ && !$this->isLegalCategoryThere($conn_local,$totalCatarr,$category_condition))
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '67108864'://insurance service - only nonpaid
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag && $pidFinanceArr['paid_flag']== '0' && $pidFinanceArr['expired_flag'] == '0')
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								/*case '17592186044416'://repair vertical - all contracts
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag && $this->contact_number_present && $this->pincode_present)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '35184372088832'://service vertical - all contracts
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag && $this->contact_number_present && $this->pincode_present)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;*/
								case '140737488355328'://sim card vertical - all contracts
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '281474976710656'://train booking vertical - all contracts
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '1073741824'://movie Vertical - checking movie display flag  only
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag || $this->IsAggregator($map_catvertical_to_typeflag[$column_name][$product_val][0],$parentid,$docid))
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '1125899906842624'://forex vertical - all nonpaid and paid expired contracts
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								/*if(!$paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];*/
								break;
								case '4503599627370496'://real estate Vertical - checking real estate display flag  only
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if($paidFlag)
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								case '9007199254740992'://urban clap Vertical - checking urban clap display flag  only
								//checkExclusionCategories($conn_local,$catid_arr,$category_condition)
								$check_cat_exist_arr[]    =  $map_catvertical_to_typeflag[$column_name][$product_val][0];
								if( stristr(strtolower($this->company_name),'urbanclap') || (  strtolower($this->datacity) == 'delhi' && !$paidFlag && ($this->checkExclusionCategories($conn_local,$totalCatarr,$category_condition)) ) )
								$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								break;
								default:
								break;
							}
							//$comp_type_flag[$product_name]	=	$map_catvertical_to_typeflag[$column_name][$product_val];
							//if(($paidFlag && $product_val!='1048576') || !$paidFlag){ 
							/*if(($paidFlag && !in_array($product_val,array('4096','8192','1048576','34359738368','17179869184'))) || (!$paidFlag && !in_array($product_val,array('32','64','128','2048','16384','32768','262144','65536','8388608','8796093022208','16','2097152','4194304','256')))  || ($pidFinanceArr['paid_flag']=='1' && in_array($product_val,array('8796093022208','16','2097152','4194304'))) || (!$paidFlag && in_array($product_val,array('32','64','128','65536','8388608','8796093022208','16','2097152','4194304')) && $selectCheckCatRow['type_flag'] && (((int)$selectCheckCatRow['type_flag'] & $map_catvertical_to_typeflag[$column_name][$product_val][0]) == $map_catvertical_to_typeflag[$column_name][$product_val][0])) || (in_array($product_val,array('4398046511104')) && $this->contact_number_present)){
								
								//if((in_array($product_val,array('4398046511104')) && $this->contact_number_present))
								echo '<br> product value :: '.$product_val. '==='.$map_catvertical_to_typeflag[$column_name][$product_val][0];
								
							    if(!$paidFlag && $product_val==8192 && in_array(strtoupper(trim($this->datacity)),array('DELHI','CHANDIGARH','AMRITSAR','AHMEDABAD','BANGALORE','HYDERABAD','KOLKATA','LUDHIANA','MUMBAI','PUNE'))){
									$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								}elseif(!$paidFlag && $product_val!=8192){
									$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								}elseif($paidFlag){
									$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								}
								/* commenting below code as cab booking is now open for some cities*/
								
								//$comp_type_flag[]	=	$map_catvertical_to_typeflag[$column_name][$product_val][0];
								
								/*Exclusive and dirty handling for tabel reservation vertical in absent of its product flag - start*
								 if($table_reservation_eligibility)
								 {
									$comp_type_flag[]	=	1;
								 }
								/*Exclusive and dirty handling for tabel reservation vertical in absent of its product flag - end *
								
								
							}*/
						}
					}
				}
			}
			
		}
	}
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," Present vertical :: ".json_encode($comp_type_flag));	
	$this->check_cat_exist_arr = $check_cat_exist_arr;
	return $comp_type_flag;
}

function IsAggregator($vertical_type_flag,$parentid,$docid)
{
	if($vertical_type_flag == '16777216'){//food ordering service
				
		
		
		if(APP_LIVE == 1)
		{
			$url =  "http://".WEB_SERVICES_API."/movie_services/movie_api.php?case=checkTheatre&docid=".$docid;
		}else
		{
			$url = "http://anirudhmathur.jdsoftware.com/web_services/movie_services/movie_api.php?case=checkTheatre&docid=".$docid;
		}
		
		$result = $this->curlcall($url);
		$result_existing_arr = json_decode($result,true);
		
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"Agg URL :: ".$url);
		$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid,"Movie Existing Data :: ".json_encode($result_existing_arr));
		
		if($result_existing_arr['status'] && strtolower(trim($result_existing_arr['message'])) == 'success' && $result_existing_arr['results'][$docid]['is_present'] == 1)
		return true;
		
		
		//$result_exising_arr['results']['gen_info'][0]
		
	}
	
}

function getPreviousVertical($parentid,$conn_iro,$compmaster_obj){
	$previousVr = array();
	if($parentid!=''){
		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "type_flag";
		$tablename		= "tbl_companymaster_extradetails";
		$wherecond		= "parentid = '".$parentid."' AND type_flag!='' AND sub_type_flag&33554432!=33554432";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);

		if($temparr['numrows']>0){
			$rowgettypeflag = $temparr['data']['0'];
			$prvTypeflag = $rowgettypeflag['type_flag'];
		}
		
		$qrygetVertical = "select type_flag_bit,type_flag_value,vertical_name from tbl_vertical_master";
		$resgetVertical = $conn_iro->query_sql($qrygetVertical);
		if($resgetVertical && mysql_num_rows($resgetVertical)>0){
			while($rowgetVertical = mysql_fetch_assoc($resgetVertical)){
				if(intval(trim($prvTypeflag)) & (1 << intval($rowgetVertical['type_flag_bit']))){
					//$previousVr[$rowgetVertical['vertical_name']] = $rowgetVertical['type_flag_value'];
					//$previousVr[$rowgetVertical['type_flag_value']] = $rowgetVertical['vertical_name'];
					$previousVr[] = $rowgetVertical['type_flag_value'];
				}
			}
		}
		unset($resgetVertical);
	}
	$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$parentid," previous vertical :: ".json_encode($previousVr));	
	return $previousVr;
}

function getVerticalValue($verticalArr,$conn_iro){
	$verticalDetailsArr = array();
	if(count($verticalArr)>0){
		$qryGetVertical =  "select type_flag_bit,type_flag_value,vertical_name from tbl_vertical_master where type_flag_value in (".implode(",",$verticalArr).")";
		$resGetVertical = $conn_iro->query_sql($qryGetVertical);
		if($resGetVertical &&  mysql_num_rows($resGetVertical)>0){
			while($rowGetVertical = mysql_fetch_assoc($resGetVertical)){
				$verticalDetailsArr[$rowGetVertical['vertical_name']]= $rowGetVertical['type_flag_value'];
			}
		}
	}
	return $verticalDetailsArr;
}

function fnDeactiveShopfront($parentid,$conn_finance,$conn_iro,$conn_local,$compmaster_obj=null,$server_city){
	$paidFlag = false;
	$deactiveVerticalTag =1;
	if($parentid!=''){
		$qryCheckActivePaid  = "select parentid,balance from tbl_companymaster_finance where parentid='".$parentid."' and balance>0";
		$resCheckActivePaid = $conn_finance->query_sql($qryCheckActivePaid);
		if($resCheckActivePaid && mysql_num_rows($resCheckActivePaid)>0){
			$paidFlag = true;
		}
		
		$sql_get_data_city = "SELECT data_city FROM tbl_companymaster_generalinfo WHERE parentid='".$parentid."'";
		$res_get_data_city = $conn_iro->query_sql($sql_get_data_city);
		if($res_get_data_city && mysql_num_rows($res_get_data_city))
		{
			$row_get_data_city = mysql_fetch_assoc($res_get_data_city);
			
		}
		
		$default_type_flg = 0;
		$is_in_exclusion_list = $this->isInExclusionList($parentid,$default_type_flg,$conn_iro);
		
		
		if(!$paidFlag && !$is_in_exclusion_list){
			$array_vertical = $this->getVerticalTagging($parentid,$conn_iro,$conn_local,$conn_finance,$compmaster_obj);//echo "cat vertical->";print_r($array_vertical);
			$previousVertical = $this->getPreviousVertical($parentid,$conn_iro,$compmaster_obj);//echo "type flag vertical->"; print_r($previousVertical);
			$newVertical = array_diff($array_vertical,$previousVertical);//echo "new vertical->"; print_r($newVertical);
			$removeVertical = array_diff($previousVertical,$array_vertical);//echo "remove vertical->"; print_r($removeVertical);
			$allVertical = array_merge(array_unique(array_merge($array_vertical,$previousVertical)));//echo "vertical->"; print_r($allVertical);
			$allVerticalValue = $this->getVerticalValue($allVertical,$conn_iro);//echo "all vertical->"; print_r($allVerticalValue);
			//echo "<br>\n\n TAGGING URL :: ".DE_CS_APP_URL." APP URL ::".APP_URL." server city : ".$server_city;
			fileperms(APP_PATH.'logs/log_flow/type_flag_logs/'.$parentid.'.txt') ." :: ".substr(sprintf('%o', fileperms(APP_PATH.'logs/log_flow/type_flag_logs/'.$parentid.'.txt')), -4);
			$this->updateVerticalTagging($parentid,$allVerticalValue,$removeVertical,$deactiveVerticalTag,$conn_finance,$conn_local,$server_city,$compmaster_obj,$conn_iro);
			return 1;
		}else{
			return 2;
		}
	}else{
		return 3;
	}
}
	 public function stdcode_master($conn_local,$gi_data_city) {
		
			$sql_stdcode = "SELECT stdcode FROM d_jds.city_master WHERE data_city = '".addslashes($gi_data_city)."' ";
			$res_stdcode = $conn_local->query_sql($sql_stdcode);
			if ($res_stdcode && mysql_num_rows($res_stdcode) ) 
			{
				$row_stdcode = mysql_fetch_assoc($res_stdcode);
				$stdcode = $row_stdcode['stdcode'];
				if ($stdcode[0] == '0') {
					$stdcode = $stdcode;
				} else {
					$stdcode = '0' . $stdcode;
				}
			}
			return $stdcode;
	}

	public function docid_creator($conn_local,$parentid,$compmaster_obj) {

		$temparr		= array();
		$fieldstr		= '';
		$fieldstr 		= "data_city";
		$tablename		= "tbl_companymaster_generalinfo";
		$wherecond		= "parentid='".$parentid."'";
		$temparr		= $compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
		
		$row_data_city = $temparr['data']['0'];
		$gi_data_city = $row_data_city['data_city'];
		
		$this->gi_data_cityval = $gi_data_city;
		$docid_stdcode = $this->stdcode_master($conn_local,$gi_data_city);
		
		if($_SERVER['SERVER_ADDR']!=''){
			$serverIP = $_SERVER['SERVER_ADDR'];
			$serverIpArray = explode(".", $serverIP);
			$cityip = $serverIpArray[2];
		}else{
			if($_SERVER['argv'][1]!=''){
				$cityip = $_SERVER['argv'][1];
			}
		}
		
		if($cityip!=''){
			switch ($cityip) {
				case 0:
					$docid = "022" . $parentid;
					break;
				case 64:
					$docid = "022" . $parentid;
					break;
				case 1:
				case 17:
					if ($docid_stdcode) {
						$temp_stdcode = ltrim($docid_stdcode, 0);
					}
					$ArrCity = array('AGRA', 'ALAPPUZHA', 'ALLAHABAD', 'AMRITSAR', 'BHAVNAGAR', 'BHOPAL', 'BHUBANESHWAR', 'CHANDIGARH', 'COIMBATORE', 'CUTTACK', 'DHARWAD', 'ERNAKULAM', 'GOA', 'HUBLI', 'INDORE', 'JAIPUR', 'JALANDHAR', 'JAMNAGAR', 'JAMSHEDPUR', 'JODHPUR', 'KANPUR', 'KOLHAPUR', 'KOZHIKODE', 'LUCKNOW', 'LUDHIANA', 'MADURAI', 'MANGALORE', 'MYSORE', 'NAGPUR', 'NASHIK', 'PATNA', 'PONDICHERRY', 'RAJKOT', 'RANCHI', 'SALEM', 'SHIMLA', 'SURAT', 'THIRUVANANTHAPURAM', 'TIRUNELVELI', 'TRICHY', 'UDUPI', 'VADODARA', 'VARANASI', 'VIJAYAWADA', 'VISAKHAPATNAM', 'VIZAG');
					if (in_array(strtoupper($gi_data_city), $ArrCity)) {
						$sqlStd = "SELECT stdcode FROM tbl_data_city WHERE cityname = '" . $gi_data_city . "'";
						$resStd = $conn_local->query_sql($sqlStd);
						$rowStd = mysql_fetch_array($resStd);
						$cityStdCode = $rowStd['stdcode'];
						if ($temp_stdcode == "") {
							$stdcode = ltrim($cityStdCode, 0);
							$stdcode = "0" . $stdcode;
						} else {
							$stdcode = "0" . $temp_stdcode;
						}
					} else {
						$stdcode = "9999";
					}
					$docid = $stdcode . $parentid;
					break;
				case 8:
					$docid = "011" . $parentid;
					break;
				case 16:
					$docid = "033" . $parentid;
					break;
				case 26:
					$docid = "080" . $parentid;
					break;
				case 32:
					$docid = "044" . $parentid;
					break;
				case 40:
					$docid = "020" . $parentid;
					break;
				case 50:
					$docid = "040" . $parentid;
					break;
				case 35:
					$docid = "079" . $parentid;
					break;	
				case 56:
					$docid = "079" . $parentid;
					break;            
			}
		}
		/*
		  $docid_stdcode 					= $this->stdcode_master();
		  $doc_id							= $this->stdcode_master().$this->parentid;
		 */
		#echo $docid; die;
		return $docid;
		
	}

	function insertTimeLog_temp ($time, $date, $lineno, $parentid ,$message)
	{
		//$sNamePrefix = '../logs/log_flow/type_flag_logs/'.$parentid.'.txt';
		if(!defined('REMOTE_CITY_MODULE'))
		{
		$baseDir = APP_PATH. 'logs/log_flow/type_flag_logs';
		$year = date("Y");   
		$month = date("m");
		$day = date("d");
		$diryr = $baseDir. "/". $year;
		$dirmnth = $diryr. "/". $month;
		$dirday = $dirmnth. "/". $day;
		if(trim('logs/log_flow/type_flag_logs')!=''){
			$directodyArr = explode("/",'logs/log_flow/type_flag_logs');
			$directodyArr = array_merge(array_filter($directodyArr));
			$filePath = '';
			foreach($directodyArr as $key => $directoryName){
				if($filePath!=''){
					$filePath .= "/".$directoryName;
				}else{
					$filePath = APP_PATH.$directoryName;
				}
				if(!is_dir($filePath)){
					mkdir($filePath, 0755);
				}
			}
		}
		if(!is_dir($diryr))
		{
			 mkdir($baseDir."/". $year, 0755);						 
		}

		if(!is_dir($dirmnth))
		{
			 mkdir($diryr."/". $month, 0755);						 
		}
		if(!is_dir($dirday))
		{
			 mkdir($dirmnth."/". $day, 0755);						 
		}
		$basefile = basename('logs/log_flow/type_flag_logs');
		$FinalPath["result"]["path"] = explode(",",$dirday);
		
		
		$sNamePrefix = $FinalPath["result"]["path"][0]."/".$parentid.'.txt';
		$pathToLog = dirname($sNamePrefix);
		if (!file_exists($pathToLog)) 
		{
			mkdir($pathToLog, 0755, true);
		}
		$fp = fopen($FinalPath["result"]["path"][0]."/".$parentid.'.txt', 'a');
		$string="For Parentid :".$parentid." [  Line No :- ".$lineno." Date Time".$date."--".$time." -- "." Insert sucessfull in Table : ".$message."]\n";
		fwrite($fp,$string );
		fclose($fp);
		
		}        
	}
	
	function fn_fetch_vertical_info($type_flag_value,$conn_local)
	{
		$vertical_arr = array();
		$sqlFetchCampaignId = "SELECT campaign_id,vertical_name FROM db_iro.tbl_vertical_master where type_flag_value = '".$type_flag_value."'";
		$resFetchCampaignId = $conn_local->query_sql($sqlFetchCampaignId);
		if($resFetchCampaignId && mysql_num_rows($resFetchCampaignId)>0)
		{
			$row_campaign_id = mysql_fetch_assoc($resFetchCampaignId);
			$vertical_arr['campaignid'] = $row_campaign_id['campaign_id'];
			$vertical_arr['vertical_name'] = ucwords(strtolower(trim($row_campaign_id['vertical_name'])));
			if(strtolower(trim($vertical_arr['vertical_name']) == 'doctor reservation'))
			{
				$vertical_arr['vertical_name'] = 'Doctor/Hospital Reservation';
			}
		}
		return $vertical_arr;
	}
	public function Active_flag_shopfront($parameters){
		if($_SERVER['SERVER_ADDR'] == '172.29.64.64')
		{
			 $rec_url =  "http://rohnyshende.jdsoftware.com/QUICK_SERVICES/services/opt/deactivate_quotes.php?".$parameters."&development=1";
		}else
		{
			$rec_url =  "http://192.168.20.105:1080/services/opt/deactivate_quotes.php?".$parameters;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $rec_url);
		/*curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);*/
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch); 
		$response = curl_getinfo($ch);
		curl_close($ch);
		return $content;
	}
	
	public function curlcall($url,$postval=''){
		$ch = curl_init($url);
		$ans=curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 10);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		//print "curl result : ".$resstr ;exit;
		curl_close($ch);
		return $resstr;
	}
	
	public function checkFinance($parentid,$conn_finance){
		$pidFinArr = array('paid_flag' => '0','expired_flag' => '0');
		$qryCheckFinance = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid='".$parentid."' LIMIT 1";
		$resCheckFinance = $conn_finance->query_sql($qryCheckFinance);
		if($resCheckFinance && mysql_num_rows($resCheckFinance)>0){
			$pidFinArr['paid_flag'] = '1';
			$qryCheckExpired = "select parentid,balance from tbl_companymaster_finance where parentid='".$parentid."' and balance>0";
			$resCheckExpired = $conn_finance->query_sql($qryCheckExpired);
			if($resCheckExpired && mysql_num_rows($resCheckExpired)<=0){
				$pidFinArr['expired_flag'] = '1';
			}
		}
		return $pidFinArr;
	}
	
	function getMutuallyExclusiveVertical($verticalArr=array(),$removeArray=array()){//print_r($verticalArr);print_r($removeArray);die();
		$extraRemoveVertical = array();
		/*commenting preference rule set function logic - if(count($verticalArr)>0){
			foreach($verticalArr as $verKey => $varVal){
				if($varVal!=''){
					if(!in_array($varVal,$removeArray)){
						if($varVal=='32768'){
							if(in_array('2',$verticalArr) && !in_array('2',$removeArray)){
								$extraRemoveVertical[]=$varVal;
							}
						}elseif($varVal=='128'){
							if(in_array('32768',$verticalArr) && !in_array('32768',$removeArray)){
								$extraRemoveVertical[]=$varVal;
							}
						}elseif($varVal=='8'){
							if(in_array('128',$verticalArr) && !in_array('128',$removeArray)){
									$extraRemoveVertical[]=$varVal;
							}
	                    }elseif($varVal=='33554432'){
							if(in_array('128',$verticalArr) && !in_array('128',$removeArray)){
									$extraRemoveVertical[]=$varVal;
							}elseif(in_array('32768',$verticalArr) && !in_array('32768',$removeArray)){
								$extraRemoveVertical[]=$varVal;
							}
	                    }elseif($varVal=='2147483648'){
							if(in_array('32',$verticalArr) && !in_array('32',$removeArray)){
								$extraRemoveVertical[]=$varVal;
							}
						}
					}
				}
			}
		}*/
		return $extraRemoveVertical;
	}
	public function curlcall_timeout_chk($url,$postval=''){
		$ch = curl_init($url);
		$ans=curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 10);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		$resstr['data'] = curl_exec($ch);
		$curl_errorno = curl_errno($ch);
		$resstr['curl_error'] = $curl_errorno;
		curl_close($ch);
		return $resstr;
	}
	function insertDataToLogTbl($parentid,$docid,$data_city,$api_response,$error_code,$conn_local)
	{
		$insertQry = "INSERT INTO db_iro.tbl_sf_api_response_log
					   SET 
					   parentid	 	= '".$parentid."',
					   docid	 	= '".$docid."',
					   data_city	= '".addslashes($data_city)."',
					   entry_time	= '".date("Y-m-d H:i:s")."',
					   entered_by   = '".$this->usercode."',
					   api_response	= '".$api_response."',
					   error_code	= '".$error_code."'";
									  
		$resInsertQry = $conn_local->query_sql($insertQry);
		
		$insertQrytoMain = "INSERT INTO db_iro.unapproved_contract_data_population
					   SET 
					   parentid	 	= '".$parentid."',
					   done_flag	= '0',
					   priority_flag = '0',
					   entry_time	= '".date("Y-m-d H:i:s")."',
					   entered_by    = '".$this->usercode."'
					   ON DUPLICATE KEY UPDATE
					   done_flag	= '0',
					   priority_flag = '0',
					   entry_time	= '".date("Y-m-d H:i:s")."',
					   entered_by    = '".$this->usercode."' ";
									  
		$resInsertQryMain = $conn_local->query_sql($insertQrytoMain);
		
	}
	
	function autotagging_shopfront_logs($parentid,$process,$nonpaid_flag,$finance_flag,$brandname_flag,$conn_local)
	{ 
	
		$InsSFLogs = "INSERT INTO db_iro.tbl_shopfront_autotagging_logs
					   SET 
					   parentid	 		= '".$parentid."',
					   process			= '".$process."',
					   updatedby		= '".$this->usercode."',
					   updatedOn		= '".date("Y-m-d H:i:s")."',
					   nonpaid_flag		= '".$nonpaid_flag."',
					   finance_flag		= '".$finance_flag."',
					   brandname_flag	= '".$brandname_flag."'
					   ON DUPLICATE KEY UPDATE
					   process			= '".$process."',
					   updatedby		= '".$this->usercode."',
					   updatedOn		= '".date("Y-m-d H:i:s")."',
					   nonpaid_flag		= '".$nonpaid_flag."',
					   finance_flag		= '".$finance_flag."',
					   brandname_flag	= '".$brandname_flag."' ";
		
		$resInsSFLogs = $conn_local->query_sql($InsSFLogs);
	}

	function getExisitingActiveFlag($parentid)
	{
		$url = "http://".DE_CS_APP_URL."/api/update_iro_web_listing_flag.php?parentid=".$parentid."&action=1&vertical_type_flag=32";
		$info_url = $this->curlcall_timeout_chk($url);
		$active_flag_data 	= json_decode($info_url['data'], true);
		$active_flag_chk = $active_flag_data['active_flag'];
		
		return $active_flag_chk;
	}
}
?>
