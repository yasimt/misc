<?php
class campaignPaymentClass extends DB
{
    var  $conn_iro        = null;
    var  $conn_jds       = null;
    var  $conn_tme     = null;
    var  $conn_fnc        = null;
    var  $conn_idc        = null;
    var  $params      = null;
    var  $dataservers     = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    var  $module        = null;
    var  $data_city        = null;
    var $citywise_price = array();


    function __construct($params)
    {
		$this->params 	= $params;
        $data_city      = trim($params['data_city']);
        $payment_type  	= trim($params['payment_type']);
        $baladjust		= trim($params['baladjust']);
        $exclude_tax	= trim($params['exclude_tax']);
        $empcode		= trim($params['empcode']);
        
        if(trim($data_city)=='')
        {
            $message = "Data City is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        
        $this->data_city    	= $data_city;
        $this->payment_type    	= strtoupper($payment_type);
        $this->empcode			= $empcode;

        if(!isset($params['parentid']) || (isset($params['parentid']) && $params['parentid'] == "")){
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }else{
            $this->parentid     = trim($params['parentid']);
        }
        /*Changes Made here*/
        if(($exclude_tax == 1) && ($this->payment_type != 'ECS')){
			$this->taxper = 0;
		}else{
			$this->taxper = 18;
		}
        $this->setServers();
        
        $configcls_obj		= new configclass();
		$urldetails			= $configcls_obj->get_url($this->data_city);
		$this->jdbox_url	= $urldetails['jdbox_url'];
        
        
        if($this->payment_type == 'ECS'){
			$this->ecs_rules = $this->getECSRules();
		}else{
			$this->payment_type = "UPFRONT";
		}
		$this->debug = 0;
		if($params['trace'] == 1){
			$this->debug = 1;
		}
		$this->balance_adjust = 0;
		if($baladjust > 0){
			$this->balance_adjust = 1;
		}
		$this->skip_mindp = 0;
		
		if($this->debug){
			echo "<center><font style='color:red;font-size:20px;font-weight:bold;'> PAYMENT TYPE : ".$this->payment_type."</font></center><br>";
			echo "<br>";
			print"<pre>";print_r($this->ecs_rules);
		}
		
    }

    // Function to set DB connection objects
    function setServers()
    {
        global $db;

        $this->conn_city         = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');

        $this->conn_local          = $db[$this->conn_city]['d_jds']['master'];
        $this->conn_idc           = $db[$this->conn_city]['idc']['master'];
        
    }
    function getCampaignMinPayment($params){
		$this->campdata = array();
		
        if(trim($params['key'])=='')
        {
            $message = "Key is missing.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        else
        {
            $valid_key = $this->validateSecretKey($params);
            if($valid_key !=1)
            {
                $message = "Access Denied.";
                echo json_encode($this->sendDieMessage($message));
                die();
            }
        }
        $jsondata 	= trim($params['json']);
        $data_array = json_decode($jsondata,true);
        $this->campdata	= $data_array['campdata'];
        $this->addon = $data_array['addon'];
        
        if($this->debug){
			print"<pre> <b>Params</b> : ";print_r($this->params);
			echo "<hr>";
			print"<pre> <b>campdata</b> : ";print_r($this->campdata);
			echo "<hr>";
			print"<pre> <b>addon</b> : ";print_r($this->addon);
			echo "<hr>";
		}
        
        
        if(count($this->campdata)<=0){
			$message = "Campdata is not proper.";
            echo json_encode($this->sendDieMessage($message));
            die();
		}
		$this->uploadrates_data = $this->getUploadRatesData();
		
		if(count($this->uploadrates_data)<=0){
			$message = "No Entry Found In tbl_business_uploadrates for city : ".$this->data_city;
            echo json_encode($this->sendDieMessage($message));
            die();
		}
		$this->national_data = $this->nationalBudget();
		
        $campids_arr = array_keys($this->campdata);
        $this->requested_campids = $campids_arr;
        $this->campcnt = count($campids_arr);
        
        $campids_str = implode("','",$campids_arr);
        $selected_camp_arr = array();
        
        $this->campInfoArr 	= array();
        
        
        $this->getCampaignInfo($campids_arr);
        
        
        if($this->debug){
			print"<pre> <b>campInfoArr</b> : ";print_r($this->campInfoArr);
			echo "<hr>";
		}
        
        if(count($this->campInfoArr) >0 ){
			
			$rest_sel_campids_arr = array_keys($this->campInfoArr);
			
			
			
			
			$resultArr = array();
			// Primary
			
			if(count($rest_sel_campids_arr)>0){
				foreach($rest_sel_campids_arr as $selcampid){
					$addcampdata = $this->findMinPaymentValue($selcampid);
					
					if($this->debug){
						echo "<br><b>Processed Campaign  </b>".$this->campInfoArr[$selcampid]." [".$selcampid."]";
						print"<pre><b>Result </b> : ";print_r($addcampdata);
						echo "<hr>";
					}
					if(count($addcampdata)>0){
						$resultArr[$selcampid] = $addcampdata;
					}
				}
			}
		}
		#print"<pre>";print_r($resultArr);
		
        if(count($resultArr)>0){
            $response_arr['error']['code'] = 0;
            $response_arr['data'] = $resultArr;
            $total = 0;
            $maxinst = 0;
            $dealClsAmnt = 0;
            foreach($resultArr as $campname => $campamountinfo){
				$total += $campamountinfo['amount'];
				if(($campamountinfo['maxinst']>0) && ($campamountinfo['dealClsAmnt']>0)){
					$maxinst += $campamountinfo['maxinst'];
					$dealClsAmnt += $campamountinfo['dealClsAmnt'];
				}
			}
            $response_arr['total'] = round($total);
            if(($maxinst > 0) && ($dealClsAmnt > 0)){
				$response_arr['full_payment'] = 1;
				$response_arr['maxinst'] = $maxinst;
				$response_arr['dealClsAmnt'] = round($dealClsAmnt);
			}else{
				
				$response_arr['full_payment'] = 0;
				$response_arr['maxinst'] = -1;
				$response_arr['dealClsAmnt'] = round($total);
			}
            
        }else{
            $response_arr['error']['code'] = 1;
        }
        return $response_arr;
    }
    
    private function getCampaignInfo($campids_arr){
		$campids_str = implode("','",$campids_arr);
		$sqlCampaignInfo = "SELECT campid,campname,bitval FROM online_regis1.tbl_campaign_list WHERE active_flag = 1 AND campid IN ('".$campids_str."') ORDER BY bitval";
		$resCampaignInfo = parent::execQuery($sqlCampaignInfo, $this->conn_idc);
        if($resCampaignInfo && parent::numRows($resCampaignInfo)>0){
            while($row_campinfo = parent::fetchData($resCampaignInfo)){
				$this->campInfoArr[$row_campinfo['campid']] = $row_campinfo['campname'];
			}
		}
	}
    
    private function findMinPaymentValue($campval){
		if($this->payment_type == 'ECS'){
			return $this->getMinECSAmount($campval);
		}
		#print"<pre>";print_r($this->campdata);
		$payinfoArr = array();
		switch($campval){
			case 18 : // Package
				$pkg_default 	= $this->campdata[18]['budget'];
				if(($this->campdata[18]['duration'] == 30) || ($this->campdata[18]['duration'] == 90) || ($this->campdata[18]['duration'] >= 730)){
					$payinfoArr['amount'] = $this->campdata[18]['budget'];
				}else {
					if(($this->campdata[18]['duration'] == 365) && (in_array(25,$this->requested_campids))){
						$contract_value = $this->campdata[18]['budget'] + $this->campdata[25]['budget'];
					}else{
						$contract_value = $this->campdata[18]['budget'];
					}
					
					$budget_calc = 0;
					if(($this->campdata[18]['duration'] == 365) && (strtolower($this->data_city) == 'delhi')){
						$package_budget = intval($this->campdata[18]['budget']);
						$pkg_one_dp = round($contract_value / 12);
						if($package_budget >= 96000  && $package_budget < 120000){ // 3 dp min payment
							$budget_calc = 1;
							$pkg_amnt = $pkg_one_dp * 3;
							
						}else if($package_budget >= 120000){ // 2 dp min payment 
							$budget_calc = 1;
							$pkg_amnt = $pkg_one_dp * 2;
						}
						
					}
					if($budget_calc !=1){
					
						$pkg_ctminbdgt = $this->uploadrates_data['top_minbudget_package'] * 67 / 100;
						$pkg_1 			= $pkg_ctminbdgt;
						
						$contval_30per 	= $contract_value * 30 / 100;
						$pkg_2 			= $contval_30per;
						$pkg_amnt 		= max($pkg_1,$pkg_2);
						
						if($this->debug){
							print "<b>Rule : </b> 67/30";
							print"<br><b>Package contract_value : </b> : ".$contract_value;
							print"<br><b>Package contval_30per: </b> : ".$contval_30per;
							print"<br><b>Package ctminbdgt: </b> : ".$pkg_ctminbdgt;
							echo "<hr>";
						}
					}else {
						if($this->debug){
							print "<b>Rule : </b>DP Based on Budget";
							print"<br><b>Package Budget : </b> : ".$package_budget;
							print"<br><b>Package One DP: </b> : ".$pkg_one_dp;
							print"<br><b>Package Amount: </b> : ".$pkg_amnt;
							echo "<hr>";
						}
					}
					
					
					$payinfoArr['amount'] = $pkg_amnt;
				}
				if($payinfoArr['amount'] > $pkg_default){
					$payinfoArr['amount'] = $pkg_default + $pkg_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 16 : // Package Festive Combo - Website is free here
				$contract_value = $this->campdata[16]['budget'];
				$pkg_default 	= $this->campdata[16]['budget'];
				$pkg_ctminbdgt 	= $this->uploadrates_data['top_minbudget_package'] * 67 / 100;
				$pkg_1 = $pkg_ctminbdgt;
				
				$contval_30per = $contract_value * 30 / 100;
				$pkg_2 = $contval_30per;
				
				$pkg_amnt = max($pkg_1,$pkg_2);				
				
				if($this->debug){
					print"<b>Package Festive Combo contract_value : </b> : ".$contract_value;
					print"<br><b>Package Festive Combo contval_30per: </b> : ".$contval_30per;
					print"<br><b>Package Festive Combo ctminbdgt: </b> : ".$pkg_ctminbdgt;
					echo "<hr>";
				}
				
				/*if(intval($this->campdata[16]['setup']) > 0){ - No Setup fee as website is free
					$pkg_amnt = $pkg_amnt + intval($this->campdata[16]['setup']);
				}*/
				if(intval($this->addon['domain']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['domain']);
					$pkg_default = $pkg_default + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['ssl']);
					$pkg_default = $pkg_default + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['email']);
					$pkg_default = $pkg_default + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['sms']);
					$pkg_default = $pkg_default + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $pkg_amnt;
				
				if($payinfoArr['amount'] > $pkg_default){
					$payinfoArr['amount'] = $pkg_default + $pkg_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
			break;
			
			case 21 : // Package Festive Combo Q4
				$pkg_amnt 	= $this->campdata[21]['budget'];
				if(intval($this->addon['domain']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$pkg_amnt = $pkg_amnt + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $pkg_amnt + $pkg_amnt * $this->taxper / 100;
				
			break;
			case 1 : // Package Expiry
				$payinfoArr['amount'] = $this->campdata[1]['budget'] + $this->campdata[1]['budget'] * $this->taxper / 100;
				break;
			
			case 3 : // Adwords by Flexi Category
				$payinfoArr['amount'] = $this->campdata[3]['budget'] + $this->campdata[3]['budget'] * $this->taxper / 100;
				break;
			
			case 14 : //Flexi - Adwords by Budget 
				$contract_value = $this->campdata[14]['budget'];
				$flx_default 	= $this->campdata[14]['budget'];
				
				$pkg_ctminbdgt 	= $this->uploadrates_data['top_minbudget_package'] * 67 / 100;
				$pkg_1 = $pkg_ctminbdgt;
				
				$contval_30per = $contract_value * 30 / 100;
				$pkg_2 = $contval_30per;
				
				$pkg_amnt = max($pkg_1,$pkg_2);
				
				$payinfoArr['amount'] = $pkg_amnt;
				
				if($this->debug){
					print"<b>Flexi - Adwords by Budget contract_value : </b> : ".$contract_value;
					print"<br><b>Flexi - Adwords by Budget contval_30per: </b> : ".$contval_30per;
					print"<br><b>Flexi - Adwords by Budget ctminbdgt: </b> : ".$pkg_ctminbdgt;
					echo "<hr>";
				}
				
				if($payinfoArr['amount'] > $flx_default){
					$payinfoArr['amount'] = $flx_default + $flx_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			case 37: // VFL Low Price
				$payinfoArr['amount'] = $this->campdata[37]['budget'] + $this->campdata[37]['budget'] * $this->taxper / 100;
				break;
			case 34 : //Lifetime 10 Yrs
				$payinfoArr['amount'] = $this->campdata[34]['budget'] + $this->campdata[34]['budget'] * $this->taxper / 100;
				break;
			
			case 2 : // PDG
				$contract_value = $this->campdata[2]['budget']; 
				$pdg_default = $this->campdata[2]['budget'];
				if($this->campdata[2]['duration'] >= 730 ){ // PDG 2 Years
					
					if($this->campdata[2]['duration'] >= 3650){
						if($this->balance_adjust == 1){
							$payinfoArr['amount'] = $contract_value;
						}else{
							$paramsSend = array();
							$paramsSend['parentid'] 	= $this->parentid;
							$paramsSend['data_city'] 	= $this->data_city;
							$paramsSend['module'] 		= "ME";
							$paramsSend['action'] 		= "isEligible";
							
							
							$curlParams = array();
							$curlParams['url'] 			= $this->jdbox_url."services/chkMultiPaymtsEligibility.php";
							$curlParams['formate'] 		= 'basic';
							$curlParams['method'] 		= 'post';
							$curlParams['headerJson'] 	= 'json';
							$curlParams['postData'] 	= json_encode($paramsSend); 
							$multipay_info_res 			= json_decode($this->curlCall($curlParams),true);
							
							if($multipay_info_res['isEligible'] == '1'){
								$payinfoArr['amount'] = $contract_value;
							}else{
								$payinfoArr['amount'] = $contract_value * 50 / 100;
								$payinfoArr['maxinst'] = 2;
								$payinfoArr['dealClsAmnt'] =  $contract_value + $contract_value * $this->taxper / 100;
							}
						}
					}else{
						$payinfoArr['amount'] = $contract_value * 50 / 100;
						$payinfoArr['maxinst'] = 2;
						$payinfoArr['dealClsAmnt'] =  $contract_value + $contract_value * $this->taxper / 100;
					}
					
				}else{
					
					if(($this->campdata[2]['duration'] == 365) && (in_array(25,$this->requested_campids))){
						$contract_value = $this->campdata[2]['budget'] + $this->campdata[25]['budget'];
					}
					$pdg_ctminbdgt = $this->uploadrates_data['top_minbudget_fp'] * 67 / 100;
					$pdg_1 = $pdg_ctminbdgt;
					
					$contval_30per = $contract_value * 30 / 100;
					$pdg_2 = $contval_30per;
					
					$pdg_amnt = max($pdg_1,$pdg_2);
					
					if($this->debug){
						print"<b>PDG contract_value : </b> : ".$contract_value;
						print"<br><b>PDG contval_30per: </b> : ".$contval_30per;
						print"<br><b>PDG ctminbdgt: </b> : ".$pdg_ctminbdgt;
						echo "<hr>";
					}
					$payinfoArr['amount'] = $pdg_amnt;
				}
				if($payinfoArr['amount'] > $pdg_default){
					$payinfoArr['amount'] = $pdg_default + $pdg_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 19 : // PDG Festive Combo
				$contract_value = $this->campdata[19]['budget'];
				$pdg_festive_default 	= $this->campdata[19]['budget'];
				
				$pdg_ctminbdgt = $this->uploadrates_data['top_minbudget_fp'] * 67 / 100;
				$pdg_1 = $pdg_ctminbdgt;
				
				$contval_30per = $contract_value * 30 / 100;
				$pdg_2 = $contval_30per;
				
				$pdg_amnt = max($pdg_1,$pdg_2);
				
				if($pdg_amnt > $pdg_festive_default){
					$pdg_amnt = $pdg_festive_default; // to make sure , minimum payment should not exceed budget
				}
				
				
				if($this->debug){
					print"<b>PDG Festive Combo contract_value : </b> : ".$contract_value;
					print"<br><b>PDG Festive Combo contval_30per: </b> : ".$contval_30per;
					print"<br><b>PDG Festive Combo ctminbdgt: </b> : ".$pdg_ctminbdgt;
					echo "<hr>";
				}
				//~ if(intval($this->campdata[19]['setup']) > 0){ //- No Setup fee as website is free
					//~ $pdg_amnt 		= $pdg_amnt + intval($this->campdata[19]['setup']);
					
				//~ }
				if(intval($this->addon['domain']) > 0){
					$pdg_amnt 		= $pdg_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$pdg_amnt 		= $pdg_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$pdg_amnt 		= $pdg_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$pdg_amnt 		= $pdg_amnt + intval($this->addon['sms']);
				}
				
				$payinfoArr['amount'] = $pdg_amnt;
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				
				break;
				
			case 22 : // PDG Festive Combo Q4
				$pdg_amnt 	= $this->campdata[22]['budget'];
				if(intval($this->addon['domain']) > 0){
					$pdg_amnt = $pdg_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$pdg_amnt = $pdg_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$pdg_amnt = $pdg_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$pdg_amnt = $pdg_amnt + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $pdg_amnt + $pdg_amnt * $this->taxper / 100;
				break;			
			
			case 35 : // National Listing
				$contract_value = $this->campdata[35]['budget'];
				$nl_default 	= $this->campdata[35]['budget'];
				
				switch($this->campdata[35]['duration']){
					case 3650 : // National Ten Years
						$payinfoArr['amount'] = $contract_value * 50 / 100;
					break;
					case 730 : // National Two Years
						$payinfoArr['amount'] = $this->campdata[35]['budget'];
					break;
					default :
					
						if(($this->campdata[35]['duration'] == 365) && (in_array(25,$this->requested_campids))){
							$contract_value = $this->campdata[35]['budget'] + $this->campdata[25]['budget'];
						}
						$nl_ctminbdgt = $this->national_data['minimumbudget_national'] * 67 / 100;
						
						$nl_1 = $nl_ctminbdgt;
						
						$contval_30per = $contract_value * 30 / 100;
						$nl_2 = $contval_30per;
						
						$nl_amnt = max($nl_1,$nl_2);
						
						
						if($this->debug){
							print"<b>National Listing contract_value : </b> : ".$contract_value;
							print"<br><b>National Listing contval_30per: </b> : ".$contval_30per;
							print"<br><b>National Listing ctminbdgt: </b> : ".$nl_ctminbdgt;
							echo "<hr>";
						}
						$payinfoArr['amount'] = $nl_amnt;
				}
				if($payinfoArr['amount'] > $nl_default){
					$payinfoArr['amount'] = $nl_default + $nl_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 20 : // National Listing Festive Combo
				$contract_value 	= $this->campdata[20]['budget'];
				$nl_fetsive_default = $this->campdata[20]['budget'];
				
				$nl_ctminbdgt = $this->national_data['minimumbudget_national'] * 67 / 100;
						
				$nl_1 = $nl_ctminbdgt;
				
				$contval_30per = $contract_value * 30 / 100;
				$nl_2 = $contval_30per;
				
				$nl_amnt = max($nl_1,$nl_2);
				
				if($nl_amnt > $nl_fetsive_default){
					$nl_amnt = $nl_fetsive_default; // to make sure , minimum payment should not exceed budget
				}
				
				//~ if(intval($this->campdata[20]['setup']) > 0){ // - No Setup fee as website is free
					//~ $nl_amnt 		= $nl_amnt + intval($this->campdata[20]['setup']);
					
				//~ }
				if(intval($this->addon['domain']) > 0){
					$nl_amnt 		= $nl_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$nl_amnt 		= $nl_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$nl_amnt 		= $nl_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$nl_amnt 		= $nl_amnt + intval($this->addon['sms']);
				}
				
				if($this->debug){
					print"<b>National Listing Festive Combo contract_value : </b> : ".$contract_value;
					print"<br><b>National Listing Festive Combo contval_30per: </b> : ".$contval_30per;
					print"<br><b>National Listing Festive Combo ctminbdgt: </b> : ".$nl_ctminbdgt;
					echo "<hr>";
				}
				$payinfoArr['amount'] = $nl_amnt;
				
				if($payinfoArr['amount'] > $nl_fetsive_default){
					$payinfoArr['amount'] = $nl_fetsive_default + $nl_fetsive_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 4 : // JDRR
				$payinfoArr['amount'] = $this->campdata[4]['budget'] + $this->campdata[4]['budget'] * $this->taxper / 100;
				break;
			case 30 : // JDRR User Choice
				$payinfoArr['amount'] = $this->campdata[30]['budget'] + $this->campdata[30]['budget'] * $this->taxper / 100;
				break;	
			case 29 : // CRISIL
				$payinfoArr['amount'] = $this->campdata[29]['budget'] + $this->campdata[29]['budget'] * $this->taxper / 100;
				break;
			
			case 5 : // JDRR Plus
				$payinfoArr['amount'] = $this->campdata[5]['budget'] + $this->campdata[5]['budget'] * $this->taxper / 100;
				break;
				
			case 17 : // JDRR Super
			
				$jdrr_super_amnt 		= $this->campdata[17]['budget'];
				if(intval($this->addon['domain']) > 0){
					$jdrr_super_amnt 		= $jdrr_super_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$jdrr_super_amnt 		= $jdrr_super_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$jdrr_super_amnt 		= $jdrr_super_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$jdrr_super_amnt 		= $jdrr_super_amnt + intval($this->addon['sms']);
				}
			
				$payinfoArr['amount'] = $jdrr_super_amnt + $jdrr_super_amnt * $this->taxper / 100;
				break;
				
			case 25 : // Banner
				$payinfoArr['amount'] = $this->campdata[25]['budget'] + $this->campdata[25]['budget'] * $this->taxper / 100;
				if(((in_array(18,$this->requested_campids)) && (intval($this->campdata[18]['duration']) == 365 ) || (in_array(2,$this->requested_campids)) && (intval($this->campdata[2]['duration']) == 365 ) || (in_array(35,$this->requested_campids)) && (intval($this->campdata[35]['duration']) == 365 ))){
					$payinfoArr['amount'] = 0; // not asking payment for banner if Pkg 1 , PDG 1 or NL 1 Yr selected
				}
				break;
				
			case 6 : // Complete Suite - same as package
				$contract_value 	= $this->campdata[6]['budget'];
				$csuite_default 	= $this->campdata[6]['budget'];
				
				if($this->campdata[6]['duration'] > 365){
					$pkg_amnt = $contract_value;
				}else{
					$pkg_ctminbdgt 		= $this->uploadrates_data['top_minbudget_package'] * 67 / 100;
					$pkg_1 = $pkg_ctminbdgt;
					
					$contval_30per = $contract_value * 30 / 100;
					$pkg_2 = $contval_30per;
					
					$pkg_amnt = max($pkg_1,$pkg_2);
					
					if($pkg_amnt > $csuite_default){
						$pkg_amnt = $csuite_default; // to make sure , minimum payment should not exceed budget
					}
					if($this->debug){
						print"<b>Complete Suite contract_value : </b> : ".$contract_value;
						print"<br><b>Complete Suite contval_30per: </b> : ".$contval_30per;
						print"<br><b>Complete Suite ctminbdgt: </b> : ".$pkg_ctminbdgt;
						echo "<hr>";
					}
				}
				if(intval($this->campdata[6]['setup']) > 0){
					$pkg_amnt 		= $pkg_amnt + intval($this->campdata[6]['setup']);
					$csuite_default = $csuite_default + intval($this->campdata[6]['setup']);
				}
				if(intval($this->addon['domain']) > 0){
					$pkg_amnt 		= $pkg_amnt + intval($this->addon['domain']);
					$csuite_default = $csuite_default + intval($this->campdata[6]['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$pkg_amnt 		= $pkg_amnt + intval($this->addon['ssl']);
					$csuite_default = $csuite_default + intval($this->campdata[6]['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$pkg_amnt 		= $pkg_amnt + intval($this->addon['email']);
					$csuite_default = $csuite_default + intval($this->campdata[6]['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$pkg_amnt 		= $pkg_amnt + intval($this->addon['sms']);
					$csuite_default = $csuite_default + intval($this->campdata[6]['sms']);
				}
				$payinfoArr['amount'] = $pkg_amnt;
				
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 12 : // Your Own Website
				$web_amnt 	= $this->campdata[12]['budget']; // only maintenance fees expecting
				
				if(intval($this->campdata[12]['setup']) > 0){ 
					$web_amnt = $web_amnt + intval($this->campdata[12]['setup']);
				}
				if(intval($this->addon['domain']) > 0){
					$web_amnt = $web_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$web_amnt = $web_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$web_amnt = $web_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$web_amnt = $web_amnt + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $web_amnt;
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;	
				
			case 8 : // iPhone App
				$payinfoArr['amount'] = $this->campdata[8]['budget'] + $this->campdata[8]['budget'] * $this->taxper / 100;
				break;
				
			case 9 : // Android App
				$payinfoArr['amount'] = $this->campdata[9]['budget'] + $this->campdata[9]['budget'] * $this->taxper / 100;
				break;
				
			case 26 : // SMS Promo
				$contract_value 	= $this->campdata[26]['budget'];
				$sms_promo_default 	= $this->campdata[26]['budget'];
				
				$pkg_ctminbdgt 		= $this->uploadrates_data['top_minbudget_package'] * 67 / 100;
				$pkg_1 = $pkg_ctminbdgt;
				
				$contval_30per = $contract_value * 30 / 100;
				$pkg_2 = $contval_30per;
				
				$pkg_amnt = max($pkg_1,$pkg_2);
				
				if($this->debug){
					print"<b>SMS Promo contract_value : </b> : ".$contract_value;
					print"<br><b>SMS Promo contval_30per: </b> : ".$contval_30per;
					print"<br><b>SMS Promo ctminbdgt: </b> : ".$pkg_ctminbdgt;
					echo "<hr>";
				}
				if($pkg_amnt > $sms_promo_default){
					$pkg_amnt = $sms_promo_default; // to make sure , minimum payment should not exceed budget
				}
				$payinfoArr['amount'] = $pkg_amnt;
			
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 27 : // VIP
				$vip_amnt 	= $this->campdata[27]['budget'];
				
				if(intval($this->campdata[27]['setup']) > 0){ 
					$vip_amnt = $vip_amnt + intval($this->campdata[27]['setup']);
				}
				if(intval($this->addon['domain']) > 0){
					$vip_amnt = $vip_amnt + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$vip_amnt = $vip_amnt + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$vip_amnt = $vip_amnt + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$vip_amnt = $vip_amnt + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $vip_amnt;
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
			
			case 28 : // Banner National Listing
				$payinfoArr['amount'] = $this->campdata[28]['budget'] + $this->campdata[28]['budget'] * $this->taxper / 100; // didn't yet get rule
				break;	
		}
		return $payinfoArr;
	}
    private function getMinECSAmount($campval){
		
		
		$payinfoArr = array();
		switch($campval){
			
			case 18 : // Package
				if((intval($this->campdata[$campval]['duration']) == 365) && (strtolower($this->data_city) == 'mumbai')){
					$this->getJoiningDate();
				}
				$pkg_ecs_default = $this->campdata[$campval]['budget'];
				$pkgecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Package : </b> : ";print_r($pkgecs_data);
					echo "<hr>";
				}
				
				if($pkgecs_data['amount'] > 0){
					$payinfoArr['amount'] = $pkgecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $pkg_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $pkg_ecs_default + $pkg_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 16 : // Package Festive Combo
				$pkg_festiveecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Package Festive Combo : </b> : ";print_r($pkg_festiveecs_data);
					echo "<hr>";
				}
				
				if($pkg_festiveecs_data['amount'] > 0){
					$payinfoArr['amount'] = $pkg_festiveecs_data['amount'];
				}else{
					$payinfoArr['amount'] = $this->campdata[$campval]['budget'];
				}
				
				/*if(intval($this->campdata[$campval]['setup']) > 0){ - No Setup fee as website is free
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->campdata[$campval]['setup']);
				}*/

				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 21 : // Package Festive Combo Q4
				$pkg_festiveecs_q4_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Package Festive Combo Q4 : </b> : ";print_r($pkg_festiveecs_q4_data);
					echo "<hr>";
				}
				
				if($pkg_festiveecs_q4_data['amount'] > 0){
					$payinfoArr['amount'] = $pkg_festiveecs_q4_data['amount'];
				}else{
					$payinfoArr['amount'] = $this->campdata[$campval]['budget'];
				}
				
				/*if(intval($this->campdata[$campval]['setup']) > 0){ - No Setup fee as website is free
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->campdata[$campval]['setup']);
				}*/

				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;	
			
			case 1 : // Package Expiry
			
				$pkg_exp_default = $this->campdata[$campval]['budget']; 
				$pkg_exp_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Package Expiry : </b> : ";print_r($pkg_exp_data);
					echo "<hr>";
				}
				if($pkg_exp_data['amount'] > 0){
					$payinfoArr['amount'] = $pkg_exp_data['amount'];
				}
				if(($payinfoArr['amount'] > $pkg_exp_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $pkg_exp_default + $pkg_exp_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 3 : // Adwords by Flexi Category
				$pkg_flx_default = $this->campdata[$campval]['budget']; 
				$pkg_flx_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Adwords by Flexi Category : </b> : ";print_r($pkg_flx_data);
					echo "<hr>";
				}
				if($pkg_flx_data['amount'] > 0){
					$payinfoArr['amount'] = $pkg_flx_data['amount'];
				}else{
					$payinfoArr['amount'] = $pkg_flx_default;
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
			
			case 14 : //Flexi
				$flx_ecs_default = $this->campdata[$campval]['budget'];
				$flxecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Felxi : </b> : ";print_r($flxecs_data);
					echo "<hr>";
				}
				
				if($flxecs_data['amount'] > 0){
					$payinfoArr['amount'] = $flxecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $flx_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $flx_ecs_default + $flx_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 34 : //Lifetime 10 Yrs
			case 37 : //VFL Low Price
				$lt10_ecs_default = $this->campdata[$campval]['budget'];
				$lt10ecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Lifetime 10 Yrs : </b> : ";print_r($lt10ecs_data);
					echo "<hr>";
				}
				
				if($lt10ecs_data['amount'] > 0){
					$payinfoArr['amount'] = $lt10ecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $lt10_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $lt10_ecs_default + $lt10_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 2 : // PDG
				$pdg_ecs_default = $this->campdata[$campval]['budget'];
				$pdgecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>PDG : </b> : ";print_r($pdgecs_data);
					echo "<hr>";
				}
				
				if($pdgecs_data['amount'] > 0){
					$payinfoArr['amount'] = $pdgecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $pdg_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $pdg_ecs_default + $pdg_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 19 : // PDG Festive Combo
				$pdgfest_ecs_default = $this->campdata[$campval]['budget'];
				$pdgfestecs_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>PDG Festive Combo : </b> : ";print_r($pdgfestecs_data);
					echo "<hr>";
				}
				
				if($pdgfestecs_data['amount'] > 0){
					$payinfoArr['amount'] = $pdgfestecs_data['amount'];
				}
				
				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 22 : // PDG Festive Combo Q4
				
				$pdgfestecs_q4_data = $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>PDG Festive Combo Q4 : </b> : ";print_r($pdgfestecs_q4_data);
					echo "<hr>";
				}
				
				if($pdgfestecs_q4_data['amount'] > 0){
					$payinfoArr['amount'] = $pdgfestecs_q4_data['amount'];
				}else{
					$payinfoArr['amount'] = $this->campdata[$campval]['budget'];
				}
				
				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;	
			
			case 35 : // National Listing
			
				$nl_default = $this->campdata[$campval]['budget']; 
				$nl_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>National Listing : </b> : ";print_r($nl_data);
					echo "<hr>";
				}
				if($nl_data['amount'] > 0){
					$payinfoArr['amount'] = $nl_data['amount'];
				}
				if(($payinfoArr['amount'] > $nl_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $nl_default + $nl_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 20 : // National Listing Festive Combo
			
				$nl_festive_default = $this->campdata[$campval]['budget']; 
				$nl_festive_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>National Listing Festive Combo : </b> : ";print_r($nl_festive_data);
					echo "<hr>";
				}
				if($nl_festive_data['amount'] > 0){
					$payinfoArr['amount'] = $nl_festive_data['amount'];
				}
				
				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;	
				
			case 4 : // JDRR
			case 30 : // JDRR User Choice
				$jdrr_ecs_default 	= $this->campdata[$campval]['budget'];
				$jdrrecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>JDRR : </b> : ";print_r($jdrrecs_data);
					echo "<hr>";
				}
				
				if($jdrrecs_data['amount'] > 0){
					$payinfoArr['amount'] = $jdrrecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $jdrr_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $jdrr_ecs_default + $jdrr_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			case 29 : // CRISIL
				$payinfoArr['amount'] = $this->campdata[29]['budget'] + $this->campdata[29]['budget'] * $this->taxper / 100;
				break;
				
			case 5 : // JDRR Plus
				$jdrrplus_ecs_default 	= $this->campdata[$campval]['budget'];
				$jdrrplusecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>JDRR Plus : </b> : ";print_r($jdrrplusecs_data);
					echo "<hr>";
				}
				
				if($jdrrplusecs_data['amount'] > 0){
					$payinfoArr['amount'] = $jdrrplusecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $jdrrplus_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $jdrrplus_ecs_default + $jdrrplus_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 17 : // JDRR Super
			
				$jdrr_super_default 	= $this->campdata[$campval]['budget']; 
				$jdrr_super_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>JDRR Super : </b> : ";print_r($jdrr_super_data);
					echo "<hr>";
				}
				if($jdrr_super_data['amount'] > 0){
					$payinfoArr['amount'] = $jdrr_super_data['amount'];
				}
				
				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 25 : // Banner
				$banner_ecs_default 	= $this->campdata[$campval]['budget'];
				$bannerecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				
				if($this->debug){
					print"<pre><b>Banner : </b> : ";print_r($bannerecs_data);
					echo "<hr>";
				}
				
				if($bannerecs_data['amount'] > 0){
					$payinfoArr['amount'] = $bannerecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $banner_ecs_default) || (intval($payinfoArr['amount']) <=0 )){	
					$payinfoArr['amount'] = $banner_ecs_default + $banner_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 6 : // Complete Suite
				$compsuiteecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Complete Suite : </b> : ";print_r($compsuiteecs_data);
					echo "<hr>";
				}
				if($compsuiteecs_data['amount'] > 0){
					$payinfoArr['amount'] = $compsuiteecs_data['amount'];
				}else{
					$payinfoArr['amount'] = $this->campdata[$campval]['budget'];
				}
				
				if(intval($this->campdata[$campval]['setup']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->campdata[$campval]['setup']);
				}

				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 12 : // Your Own Website
				$websiteecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Your Own Website : </b> : ";print_r($websiteecs_data);
					echo "<hr>";
				}
				if($websiteecs_data['amount'] > 0){
					$payinfoArr['amount'] = $websiteecs_data['amount'];
				}
				if(intval($this->campdata[$campval]['setup']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->campdata[$campval]['setup']);
				}

				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 8 : // iPhone App
				$iphone_ecs_default 	= $this->campdata[$campval]['budget'];
				$iphoneecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>iPhone App : </b> : ";print_r($iphoneecs_data);
					echo "<hr>";
				}
				if($iphoneecs_data['amount'] > 0){
					$payinfoArr['amount'] = $iphoneecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $iphone_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $iphone_ecs_default + $iphone_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 9 : // Android App
				$android_ecs_default 	= $this->campdata[$campval]['budget'];
				$androidecs_data 		= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Android App : </b> : ";print_r($androidecs_data);
					echo "<hr>";
				}
				if($androidecs_data['amount'] > 0){
					$payinfoArr['amount'] = $androidecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $android_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $android_ecs_default + $android_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
			
			case 26 : // SMS Promo
				$sp_default = $this->campdata[$campval]['budget']; 
				$sp_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>SMS Promo : </b> : ";print_r($sp_data);
					echo "<hr>";
				}
				if($sp_data['amount'] > 0){
					$payinfoArr['amount'] = $sp_data['amount'];
				}
				if(($payinfoArr['amount'] > $sp_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $sp_default + $sp_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;
				
			case 27 : // VIP
				$vip_default = $this->campdata[$campval]['budget']; 
				$vip_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>VIP : </b> : ";print_r($vip_data);
					echo "<hr>";
				}
				if($vip_data['amount'] > 0){
					$payinfoArr['amount'] = $vip_data['amount'];
				}else{
					$payinfoArr['amount'] = $vip_default;
				}
				if(intval($this->addon['domain']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['domain']);
				}
				if(intval($this->addon['ssl']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['ssl']);
				}
				if(intval($this->addon['email']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['email']);
				}
				if(intval($this->addon['sms']) > 0){
					$payinfoArr['amount'] = $payinfoArr['amount'] + intval($this->addon['sms']);
				}
				$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				break;
				
			case 28 : // Banner National Listing
				$bnrnl_ecs_default 	= $this->campdata[$campval]['budget']; 
				$bnrnl_ecs_data 	= $this->applyECSRules($this->ecs_rules[$campval],$campval);
				if($this->debug){
					print"<pre><b>Banner National Listing : </b> : ";print_r($bnrnl_ecs_data);
					echo "<hr>";
				}
				if($bnrnl_ecs_data['amount'] > 0){
					$payinfoArr['amount'] = $bnrnl_ecs_data['amount'];
				}
				if(($payinfoArr['amount'] > $bnrnl_ecs_default) || (intval($payinfoArr['amount']) <=0 )){
					$payinfoArr['amount'] = $bnrnl_ecs_default + $bnrnl_ecs_default * $this->taxper / 100;
				}else{
					$payinfoArr['amount'] = $payinfoArr['amount'] + $payinfoArr['amount'] * $this->taxper / 100;
				}
				break;			
		}
		return $payinfoArr;
	}
	private function getECSRules(){
		$ecs_rules_arr = array();
		$sqlECSRules = "SELECT product_id,product_name,ecs_allowed,dp_count,solo_dp_count,check_dp,flat_amount,tenure_option,min_dp_amount FROM tbl_ecs_rules";
		$resECSRules = parent::execQuery($sqlECSRules, $this->conn_idc);
		if($resECSRules && parent::numRows($resECSRules)>0){
			while($row_ecs_rules = parent::fetchData($resECSRules)){
				$product_id 	= intval($row_ecs_rules['product_id']);
				$product_name 	= trim($row_ecs_rules['product_name']);
				$ecs_allowed 	= intval($row_ecs_rules['ecs_allowed']);
				$dp_count 		= intval($row_ecs_rules['dp_count']);
				$solo_dp_count 	= intval($row_ecs_rules['solo_dp_count']);
				$check_dp 		= intval($row_ecs_rules['check_dp']);
				$flat_amount	= trim($row_ecs_rules['flat_amount']);
				$flat_amount	= number_format((float)$flat_amount, 2, '.', '');
				$tenure_option 	= trim($row_ecs_rules['tenure_option']);
				$min_dp_amount	= trim($row_ecs_rules['min_dp_amount']);
				
				$ecs_rules_arr[$product_id]['product_name'] 	= $product_name;
				$ecs_rules_arr[$product_id]['ecs_allowed'] 		= $ecs_allowed;
				$ecs_rules_arr[$product_id]['dp_count'] 		= $dp_count;
				$ecs_rules_arr[$product_id]['solo_dp_count'] 	= $solo_dp_count;
				$ecs_rules_arr[$product_id]['check_dp'] 		= $check_dp;
				$ecs_rules_arr[$product_id]['flat_amount'] 		= $flat_amount;
				$ecs_rules_arr[$product_id]['tenure_option'] 	= $tenure_option;
				$ecs_rules_arr[$product_id]['min_dp_amount'] 	= $min_dp_amount;
			}
		}
		return $ecs_rules_arr;
	}	
	private function applyECSRules($data_array,$campid){
		
		$ecs_results = array();
		$ecs_results['rule'] = 0;
		if(count($data_array)>0){
			switch($data_array['ecs_allowed']){
				case 3 :
					$ecs_results['rule'] = 5; // refer dp_count / solo_dp_count from tenure_option json
					$ecs_results['desc'] = 'refer dp_count / solo_dp_count from tenure_option json';
					$tenure_option_arr = json_decode($data_array['tenure_option'],true);
					$tenure_months = 12;
					
					$new_data_arr = array();
					$new_data_arr = $tenure_option_arr[$data_array['required_tenure']];
					
					if($this->campcnt == 1){
						$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $new_data_arr['solo_dp_count'];
					}else{
						$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $new_data_arr['dp_count'];
					}
					$ecs_results['amount'] = max($tenure_option_arr[$data_array['required_tenure']]['min_dp_amount'],$ecs_results['amount']);
				break;	
				case 5 :
					$ecs_results['rule'] = 5; // apply formula (amount / 12 * dp_count) OR (amount / 12 * solo_dp_count) -- solo_dp_count will used in case of single product
					$ecs_results['desc'] = 'apply formula (amount / 12 * dp_count) OR (amount / 12 * solo_dp_count) -- solo_dp_count will used in case of single product';
					$tenure_months = 12; // it will always be 12 month as suggested by sumeshji
					
					
					
					$new_data_arr = array();
					$tenure_option_arr = json_decode($data_array['tenure_option'],true);
					$new_data_arr = $tenure_option_arr[$data_array['required_tenure']];
					
					if($new_data_arr['solo_dp_count'] && $new_data_arr['dp_count']){
						if($this->campcnt == 1){
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $new_data_arr['solo_dp_count'];
						}else{
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $new_data_arr['dp_count'];
						}
						
						$min_dp_amount = round($new_data_arr['min_dp_amount']);
						$ecs_results['amount'] = max($min_dp_amount,$ecs_results['amount']);
						
					}else{
						if($this->campcnt == 1){
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $data_array['solo_dp_count'];
						}else{
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $data_array['dp_count'];
						}
						$min_dp_amount = round($data_array['min_dp_amount']);
						$ecs_results['amount'] = max($min_dp_amount,$ecs_results['amount']);
					}					
					
				break;
				case 2 :
					$tenure_option_arr = json_decode($data_array['tenure_option'],true);
					$tenure_days = $this->campdata[$campid]['duration'];
					$tenure_months = $this->convertDaytoMonth($tenure_days);
					
					switch($tenure_option_arr[$tenure_months]['ecs_allowed']){
						case 1 :
							$data_array['ecs_allowed'] = 1;
							$recurdata = $this->applyECSRules($data_array,$campid);
							$recurdata['org_rule'] = 1; // get amount based on tenure - refer tenure_option column
							$recurdata['org_desc'] = 'amount based on tenure - refer tenure_option column';
							return $recurdata;
							
						break;
						case 0 :
							$data_array['ecs_allowed'] = 0;
							$recurdata = $this->applyECSRules($data_array,$campid);
							$recurdata['org_rule'] = 1; // get amount based on tenure - refer tenure_option column
							$recurdata['org_desc'] = 'amount based on tenure - refer tenure_option column';
							return $recurdata;
						break;
						case 3 :
							$data_array['required_tenure'] = $tenure_months;
							$data_array['ecs_allowed'] = 3;
							$recurdata = $this->applyECSRules($data_array,$campid);
							$recurdata['org_rule'] = 1; // get amount based on tenure - refer tenure_option column
							$recurdata['org_desc'] = 'amount based on tenure - refer tenure_option column';
							return $recurdata;
						break;
						case 4 :
							$ecs_results['rule'] = 6; // apply formula (amount / ecs_months * dp_count) OR (amount / ecs_months * solo_dp_count) -- solo_dp_count will used in case of single product
							$ecs_results['desc'] = 'apply formula (amount / ecs_months * dp_count) OR (amount / ecs_months * solo_dp_count) -- solo_dp_count will used in case of single product';
							$ecs_months		 	= $tenure_option_arr[$tenure_months]['ecs_months']; // getting tenure from json
							$dp_count		 	 = $tenure_option_arr[$tenure_months]['dp_count']; // getting dp_count from json
							$solo_dp_count		 = $tenure_option_arr[$tenure_months]['solo_dp_count']; // getting solo_dp_count from json
							
							$minimun_dpamnt 	= $tenure_option_arr[$tenure_months]['min_dp_amount'];
							
							if($this->campcnt == 1){
								$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $ecs_months * $solo_dp_count;
							}else{
								$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $ecs_months * $dp_count;
							}
							if($minimun_dpamnt){
								$ecs_results['amount'] = max($minimun_dpamnt,$ecs_results['amount']);
							}
						
						break;
						case 5 :
							$data_array['required_tenure'] = $tenure_months;
							$data_array['ecs_allowed'] = 5;
							$recurdata = $this->applyECSRules($data_array,$campid);
							$recurdata['org_rule'] = 1; // get amount based on tenure - refer tenure_option column
							$recurdata['org_desc'] = 'amount based on tenure - refer tenure_option column';
							return $recurdata;
						break;
					}
				break;
				case 1 :
					if($data_array['check_dp'] == 1){
						$ecs_results['rule'] = 2; // refer flat_amount column
						$ecs_results['desc'] = 'refer flat_amount column';
						$ecs_results['amount'] = $data_array['flat_amount'];
						
						$tenure_days 	= $this->campdata[$campid]['duration'];
						$tenure_months 	= $this->convertDaytoMonth($tenure_days);
						if($this->campcnt == 1){
							$ecs_results['amount'] = $ecs_results['amount'] + $this->campdata[$campid]['budget'] / $tenure_months * $data_array['solo_dp_count'];
						}else{
							$ecs_results['amount'] = $ecs_results['amount'] + $this->campdata[$campid]['budget'] / $tenure_months * $data_array['dp_count'];
						}
						
					}else{
						
						$ecs_results['rule'] = 3; // apply formula (amount / tenure * dp_count) OR (amount / tenure * solo_dp_count) -- solo_dp_count will used in case of single product
						$ecs_results['desc'] = 'apply formula (amount / tenure * dp_count) OR (amount / tenure * solo_dp_count) -- solo_dp_count will used in case of single product';
						
						$tenure_days = $this->campdata[$campid]['duration'];
						$tenure_months = $this->convertDaytoMonth($tenure_days);
						
						if($data_array['tenure_option']){
							$tenure_array = json_decode($data_array['tenure_option'],true);
							if(isset($tenure_array[$tenure_months]['min_dp_amount'])){
								$minimun_dp_amount = $tenure_array[$tenure_months]['min_dp_amount'];
							}else if($data_array['min_dp_amount'] > 0){
								$minimun_dp_amount = $data_array['min_dp_amount'];
							}
						}else if($data_array['min_dp_amount'] > 0){
							$minimun_dp_amount = $data_array['min_dp_amount'];
						}
						if($this->campcnt == 1){
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $data_array['solo_dp_count'];
						}else{
							$ecs_results['amount'] = $this->campdata[$campid]['budget'] / $tenure_months * $data_array['dp_count'];
						}
						
						if($minimun_dp_amount && !$this->skip_mindp){
							$ecs_results['amount'] = max($minimun_dp_amount,$ecs_results['amount']);
						}
					}
				break;
				case 0 :
					$ecs_results['rule'] = 4; // get total amount i.e 100 % like jdrr
					$ecs_results['amount'] = $this->campdata[$campid]['budget'];
					$ecs_results['desc'] = 'get total amount i.e 100 % like jdrr';
				break;
			}
		}
		return $ecs_results;
	}
	private function convertDaytoMonth($days){
		return round($days / (365/12));
	}	
    private function findAllCombinations($first,$remaining){
		#$suggest_camp = array();
		if(count($remaining)>0){
			
			for($i=1;$i <= count($remaining);$i++){
				$suggest_camp[$first + $remaining[$i]] = $remaining[$i];
			}
		}
		#print"<pre>";print_r($suggest_camp);
		if(count($suggest_camp)>1){
			$new_suggest_camp = array_values($suggest_camp);
			
			
			
			
			$first_element = current($new_suggest_camp);
			
			$rest_elem_arr = $new_suggest_camp;
			
			$key = array_search($first_element,$rest_elem_arr);
			unset($rest_elem_arr[$key]);
			
			$suggest_camp_new = $this->findAllCombinations($first_element,$rest_elem_arr);
			
			foreach($suggest_camp_new as $key => $val){
				$suggest_camp[$key] = $val;
			}
			
			#print"<pre>";print_r($suggest_camp_new);die;
			
			
		}
		#print"<pre>";print_r($suggest_camp);die;
		return $suggest_camp;
		//echo $first;
		//echo $remaining;
		//print"<pre>";print_r($suggest_camp);
		
		
		//die;
		
	}
    private function validateSecretKey($params){
        $action = trim($params['action']);
        $key = trim($params['key']);
        $validate_flag = 0;
        $sqlSecretKeyChk = "SELECT secret_key FROM online_regis1.tbl_budget_api_secret_key WHERE action = '".addslashes($action)."'";
        $resSecretKeyChk = parent::execQuery($sqlSecretKeyChk, $this->conn_idc);
        if($resSecretKeyChk && parent::numRows($resSecretKeyChk)>0){
            $row_secret_key = parent::fetchData($resSecretKeyChk);
            $secretkey        = $row_secret_key['secret_key'];
            $original_key     = hash_hmac('sha256', $action,($secretkey.strtolower($this->data_city)));
            $given_key         = $key;
            if((md5($original_key)===md5($given_key))){
                $validate_flag = 1;
            }
        }
        return $validate_flag;
    }	
    private function getUploadRatesData(){
        $sqlUploadRates = "SELECT top_minbudget_fp,top_minbudget_package FROM tbl_business_uploadrates  WHERE city = '".$this->data_city."'";
        $resUploadRates = parent::execQuery($sqlUploadRates, $this->conn_local);
        if($resUploadRates && parent::numRows($resUploadRates)>0){
            return $row_upload_rates = parent::fetchData($resUploadRates);
        }
    }
    private function nationalBudget(){
		$sqlUploadRates = "SELECT minimumbudget_national FROM tbl_business_uploadrates  WHERE city = '".$this->data_city."'"; // pointing to national listing
        $resUploadRates = parent::execQuery($sqlUploadRates, $this->conn_idc);
        if($resUploadRates && parent::numRows($resUploadRates)>0){
            return $row_upload_rates = parent::fetchData($resUploadRates);
        }
	}
	private function getJoiningDate(){
		if(intval($this->empcode) > 0){
			$empinfo_str =  $this->employeeInfo($this->empcode);
			if($empinfo_str){
				$empinfo_arr = json_decode($empinfo_str,true);
				if(($empinfo_arr['errorcode'] == 0) && (count($empinfo_arr['data']) > 0)){
					$date_of_joining = $empinfo_arr['data'][0]['date_of_joining'];
					if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_of_joining)){
						$cur_date 	= time();
						$join_date 	= strtotime($date_of_joining);
						$dt_diff 	= $cur_date - $join_date;
						$dt_diff = round($dt_diff / (60 * 60 * 24));
						if($dt_diff <=180){
							$this->skip_mindp = 1;
						}
					}
				}
			}
		}
	}
	private function employeeInfo($empcode){
		$retValemp					=	'';
		if(intval($empcode)>0){
			$paramsArr					=	array();
			$paramsArr['empcode']		=	$empcode;
			$paramsArr['textSearch']	=	4;
			$paramsArr['reseller_flag']	=	1;
			
			$curlParams = array();
			$curlParams['sso'] = 1;
			$curlParams['url'] = "http://".SSO_MODULE_IP.":8080/api/getEmployee_xhr.php";
			$curlParams['formate'] = 'basic';
			$curlParams['method'] = 'post';
			$curlParams['headerJson'] = 'json';
			$curlParams['postData'] = json_encode($paramsArr); 
			$retValemp 			= 	$this->curlCall($curlParams);
		}
		return $retValemp;
	}
	function curlCall($param)
	{	
		$retVal = '';
        $method = ((isset($param['method'])) && ($param['method'] != "")) ? strtolower($param['method']) : "get";
        $formate = ((isset($param['formate'])) && ($param['formate'] != "")) ? strtolower($param['formate']) : "array";
        
        $timeout = ((isset($param['timeout'])) && ($param['timeout'] >0 )) ? $param['timeout'] : 30;

        # Init Curl Call #
        $ch = curl_init();

        # Set Options #
        curl_setopt($ch, CURLOPT_URL, $param['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postData']);
        }
        $token = '';
        if($param['sso'] == 1){
			$token = 'HR-API-AUTH-TOKEN:'.md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
		}
        if(isset($param['headerJson']) && $param['headerJson'] != '')  {
			if($param['headerJson']	==	'json') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($param['postData']),
					$token
					)                                                                       
				); 
			} else if($param['headerJson']	==	'array') {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-type: multipart/form-data'
				));
			}
		}
        $retVal = curl_exec($ch);
        curl_close($ch);
        unset($method);
        if ($formate == "array") {
            return json_decode($retVal, TRUE);
        } else {
            return $retVal;
        }
	}
	
    private function sendDieMessage($msg)
    {
        $die_msg_arr['error']['code'] = 1;
        $die_msg_arr['error']['msg'] = $msg;
        return $die_msg_arr;
    }
}
?> 
