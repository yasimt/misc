<?php
class campaignDetailsClass extends DB
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
    var  $empaccess_info = array();
	var $omni_pricing = array();
	var $jdrr_pricing_arr = array();

    function __construct($params)
    {
        $data_city         = trim($params['data_city']);
        $emptype         	= trim($params['emptype']);
        $empcode         	= trim($params['empcode']);
        if(trim($data_city)=='')
        {
            $message = "Data City is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        
        $this->data_city    	= $data_city;
        $this->emptype 			= "";
        if($emptype){
			$this->emptype		= strtolower($emptype);
		}
		
		if($empcode){
			$this->empcode		= trim($empcode);
		}else{
			$this->empcode		= '';
		}
		

        if(!isset($params['parentid']) || (isset($params['parentid']) && $params['parentid'] == "")){
            $message = "Parentid is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }else{
            $this->parentid     = trim($params['parentid']);
        }
        /*Changes Made here*/
        
        if((preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST'])) || (preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']))){
			$this->devmode = 1;
		}else{
			$this->devmode = 0;
		}
		$this->bnr_eligible_catarr = array();
		$this->bnr_inv_avail 	= 1;
        
        $this->mongo_obj     = new MongoClass();
        $this->categoryClass_obj = new categoryClass();
        $this->setServers();
        $this->limited_access = 0;
        if($this->empcode){
			$this->empaccess_info = $this->empAcccessInfo();
		}
		$this->remote_zone = $this->getRemoteCityZone();
		$this->parent_column = "parent_flag";
		$this->position_column = "position_flag";
		if($this->limited_access == 1){
			$this->parent_column = "reseller_parent";
			$this->position_column = "reseller_position";
		}
		$this->vfl_specail_team = 0;
		
    }

    // Function to set DB connection objects
    function setServers()
    {
        global $db;

        $this->conn_city    = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
        $this->conn_local 	= $db[$this->conn_city]['d_jds']['master'];
        $this->conn_idc		= $db[$this->conn_city]['idc']['master'];
        $this->conn_fin  	= $db[$this->conn_city]['fin']['master'];
        
    }
    function getCampaignMinBudget($params){


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
		$this->finance_data 		= $this->financeInfo();
		if($params['trace'] == 1){
			print"<pre>";print_r($this->finance_data);
		}
		$this->restricted_catinfo 	= $this->validateContractCategories();
		
		
		$this->bannerInventoryCheck();
		
		
		$this->uploadrates_data = $this->getUploadRatesData();
		
		$this->keyword_info = $this->getCampaignKeywordInfo();
		
		#print"<pre>";print_r($this->keyword_info);
		
		$omni_types_array = array(1 => "CS 5yrs", 2 => "CS 1yr", 5 => "PKG Festive Combo", 16 => "JDRR Super – Standard", 17 => "JDRR Super – Classic", 18 => "JDRR Super – Premium", 12 => "National Listing Festive Combo", 19 => "VIP" , 11 => "PDG Festive Combo");
		$this->omni_pricing = $this->getOMNIPrice($omni_types_array);
		
		$this->jdrr_pricing_arr = $this->getJDRRPricing();
		$this->allocID = $this->allocationInfo();
		
		#print"<pre>";print_r($this->jdrr_pricing_arr);
		
        $campdata_arr = array();
        $campdata_arr = $this->getCampaignList();
        
        if(count($campdata_arr)>0){
            $response_arr['error']['code'] = 0;
            $response_arr['data'] = $campdata_arr;
        }else{
            $response_arr['error']['code'] = 1;
        }
        return $response_arr;
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
    
    private function getCampaignList(){
        $camplist_arr = array();
		$sqlParentCampInfo = "SELECT * FROM online_regis1.tbl_campaign_list WHERE ".$this->parent_column." = 0  AND active_flag IN (1,2) ORDER BY ".$this->position_column."";
        $resParentCampInfo = parent::execQuery($sqlParentCampInfo, $this->conn_idc);
        if($resParentCampInfo && parent::numRows($resParentCampInfo)>0){
			while($row_parent_camp = parent::fetchData($resParentCampInfo)){
				
				$row_parent_camp['position_flag'] = $row_parent_camp[$this->position_column];
					
				$parent_active		= intval($row_parent_camp['active_flag']);
				$parent_campid 		= intval($row_parent_camp['campid']);
				$parent_campname 	= trim($row_parent_camp['campname']);
				$parent_addinfo 	= trim($row_parent_camp['pricechart_display']);
				$parent_actcamp 	= trim($row_parent_camp['actual_campaign']);
				$parent_blkcamp 	= trim($row_parent_camp['block_campaign']);
				$parent_dptcamp 	= trim($row_parent_camp['dependent_campaign']);
				$parent_mandcamp 	= trim($row_parent_camp['mandatory_campaign']);
				$parent_combocamp 	= trim($row_parent_camp['combo_campaign']);
				$parent_sgstcamp 	= trim($row_parent_camp['suggested_campaign']);
				$parent_big_lite 	= trim($row_parent_camp['back_big_lite']);
				$parent_small_lite 	= trim($row_parent_camp['back_small_lite']);
				$parent_isCombo 	= trim($row_parent_camp['isCombo']);
				$parent_rdt_url		= trim($row_parent_camp['redirection_url']);
				$parent_pos_flag	= trim($row_parent_camp['position_flag']);
				$parent_img_header	= trim($row_parent_camp['image_header']);
				$parent_order_flag	= trim($row_parent_camp['order_flag']);
				
				if($parent_active == 1){
					$child_camp_data = array();
					$child_camp_data = $this->getChildCampInfo($parent_campid);
					if(count($child_camp_data)>0){ 
						$camplist_arr[$parent_campid] 				= $child_camp_data;
						$camplist_arr[$parent_campid]['child_data'] = $child_camp_data;
						$camplist_arr[$parent_campid]['name'] 		= $parent_campname;
						$camplist_arr[$parent_campid]['actflag'] 	= 1;
						$camplist_arr[$parent_campid]['allowed'] 	= 1;
						$camplist_arr[$parent_campid]['parent'] 	= 1;
						$camplist_arr[$parent_campid]['back_big'] 	= $parent_big_lite;
						$camplist_arr[$parent_campid]['back_small'] = $parent_small_lite;
						$camplist_arr[$parent_campid]['posflag'] 	= $parent_pos_flag;
						$camplist_arr[$parent_campid]['imghdr'] 	= $parent_img_header;
						$camplist_arr[$parent_campid]['ordflag'] 	= $parent_order_flag;
					}else{
						// parent with no child - national listing
						$parent_data = array();
						$parent_data = $this->getCampData($parent_campid,$row_parent_camp);
						if(count($parent_data)>0){
							$camplist_arr += $parent_data;
						}
						
					}
				}else{ // active flag 2
					
					$parent_data = array();
					$parent_data = $this->getCampData($parent_campid,$row_parent_camp);
					if(count($parent_data)>0){
						$camplist_arr += $parent_data;
					}
					
				}
			}
		}
		return $camplist_arr;
        
    }
    private function getChildCampInfo($campid){
		$subchild_arr = array();
		
		$dataArray = array();
		$sqlChildCampInfo = "SELECT * FROM online_regis1.tbl_campaign_list WHERE ".$this->parent_column." = '".$campid."' AND active_flag = 1 ORDER BY ".$this->position_column."";
		$resChildCampInfo = parent::execQuery($sqlChildCampInfo, $this->conn_idc);
		if($resChildCampInfo && parent::numRows($resChildCampInfo)>0){
			while($row_child_camp = parent::fetchData($resChildCampInfo)){
				
				$row_child_camp['position_flag'] = $row_child_camp[$this->position_column];
					
				if($row_child_camp['child'] == 'NO'){
					
					$campaign_data = array();
					$campaignid = intval($row_child_camp['campid']);
					$campaign_data = $this->getCampData($campaignid,$row_child_camp);
					if(count($campaign_data)>0){
						$dataArray += $campaign_data;
					}
				}else if($row_child_camp['child'] == 'YES'){
					$subchild_arr[$row_child_camp['campid']] = $row_child_camp;
				}
			}
		}
		if(count($subchild_arr)>0){
			foreach($subchild_arr as $subchild => $childdata){
				$childarr = array();
				$childarr = $this->getChildCampInfo($subchild);
				if(count($childarr)>0){
					$dataArray[$subchild] 				= $childarr;
					$dataArray[$subchild]['child_data']	= $childarr;
					$dataArray[$subchild]['name'] 		= $childdata['campname'];
					$dataArray[$subchild]['actflag'] 		= 1;
					$dataArray[$subchild]['allowed'] 		= 1;
					$dataArray[$subchild]['parent'] 		= 1;
					$dataArray[$subchild]['back_big'] 	= $childdata['back_big_lite'];
					$dataArray[$subchild]['back_small'] 	= $childdata['back_small_lite'];
					$dataArray[$subchild]['posflag']		= $childdata['position_flag'];
					$dataArray[$subchild]['imghdr']			= $childdata['image_header'];
					$dataArray[$subchild]['ordflag']		= $childdata['order_flag'];
					
				}
			}
		}		
		return $dataArray;
	}
	private function getCampData($campaignid,$row_child_camp){
		$dataArr = array();
		switch($campaignid){
			case 1 : // Package Expiry
				$exp_idx = -1;
				
				if($this->finance_data['pack_exp_45_days'] == 1){
					
					if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
						
						$proceed_flag = 1;
						$jpr_zone_city_arr = $this->zoneWiseCityList("jaipur");
						if(in_array(strtolower($this->data_city),$jpr_zone_city_arr) || strtolower($this->data_city) == 'jaipur'){
							$proceed_flag = 0;
							if(intval($this->finance_data['expire_since_days']) >= 90){
								$proceed_flag = 1;
							}
						}
						
						if($proceed_flag == 1){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						$dataArr[$campaignid]['multi_keyword']	= 1;
						$pkg_exp_1yr_bdgt = intval($this->uploadrates_data['exppackval']);
						
						
						
						if(intval($this->finance_data['expire_since_days']) >= 730){
							// checking if city belongs to pune or their remote
							if(strtolower($this->data_city) == 'pune'){
								$pkg_exp_1yr_bdgt = 6000;
							}else{
								$pune_zone_city_arr = $this->zoneWiseCityList("pune");
								if(in_array(strtolower($this->data_city),$pune_zone_city_arr)){
									$pkg_exp_1yr_bdgt = 6000;
								}
							}
						}
						$pkg_1yr_ecs_budget = $pkg_exp_1yr_bdgt;
						
						if(strtolower($this->data_city) == 'mumbai'){
							$pkg_1yr_ecs_budget = 12000;
						}
						
						if($this->uploadrates_data['exppackval']){ // expiry one year
							$exp_idx ++;
							$dataArr[$campaignid]['tenure_options'][$exp_idx]	= array("tenure_days"=>"365","tenure_name"=>"1 Year",'payment_mode_available'=>'ECS/NON ECS',"tenure_months"=>12,"upfront"=>$pkg_exp_1yr_bdgt,"ecs"=>$pkg_1yr_ecs_budget,"keyword"=>$this->keyword_info['1.1']['name'],"bitval"=>$this->keyword_info['1.1']['bitval']);
							$dataArr[$campaignid]['price_disp']	= $pkg_exp_1yr_bdgt;
						}
						if($this->uploadrates_data['exppackval_2']){ // expiry one year
							$exp_idx ++;
							$dataArr[$campaignid]['tenure_options'][$exp_idx]	= array("tenure_days"=>"730","tenure_name"=>"2 Years",'payment_mode_available'=>'NON ECS',"tenure_months"=>24,"upfront"=>intval($this->uploadrates_data['exppackval_2']),"keyword"=>$this->keyword_info['1.2']['name'],"bitval"=>$this->keyword_info['1.2']['bitval']);
						}
						}
					}
				}
			break;
			case 2 	: // By Position - PDG
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= 1;
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->keyword_info[2]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[2]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[2]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
					$pdg_disc_per	= floatval($this->uploadrates_data['pdg_disc_per']);
					$pdg_disc_per	= number_format($pdg_disc_per, 2);
					$dataArr[$campaignid]['disc_per']		= $pdg_disc_per;
					$dataArr[$campaignid]['disc_amnt']		= intval($this->uploadrates_data['pdg_disc_eligib']);
					$dataArr[$campaignid]['ct_min_bdgt']	= intval($this->uploadrates_data['top_minbudget_fp']);
					
					
					$pdg_tenure_arr[] = array("tenure_name"=> "90 Days", "days"=> 90, "tenure_days"=> 90, "tenure_months"=> 3, "exclusive"=> 1); 
					
					if($this->remote_zone == "coimbatore" || $this->data_city == "coimbatore"){
						$pdg_tenure_arr[] = array("tenure_name"=> "120 Days", "days"=> 120, "tenure_days"=> 120, "tenure_months"=> 4, "exclusive"=> 1);
					}
					$pdg_tenure_arr[] = array("tenure_name"=> "180 Days", "days"=> 180, "tenure_days"=> 180, "tenure_months"=> 6, "exclusive"=> 1);
					$pdg_tenure_arr[] = array("tenure_name"=> "1 Year", "days"=> 365, "tenure_days"=> 365, "tenure_months"=> 12, "exclusive"=> 1);
					$pdg_tenure_arr[] = array("tenure_name"=> "2 Year", "days"=> 730, "tenure_days"=> 730, "tenure_months"=> 24, "exclusive"=> 0);
					$pdg_tenure_arr[] = array("tenure_name"=> "3 Years", "days"=> 1095, "tenure_days"=> 1095, "tenure_months"=> 36, "exclusive"=> 0);
					$pdg_tenure_arr[] = array("tenure_name"=> "5 years", "days"=> 1825, "tenure_days"=> 1825, "tenure_months"=> 60, "exclusive"=> 0);
					$pdg_tenure_arr[] = array("tenure_name"=> "VFL", "days"=> 3650, "tenure_days"=> 3650, "tenure_months"=> 120, "exclusive"=> 0);
					
					$dataArr[$campaignid]['tenure_options'] = (object)$pdg_tenure_arr;					
					
				}
			break;
			case 3 : // Flexi Premium Ad Package
				if($this->restricted_catinfo['flx_prem_ad_pkg'] == 1){
					
					if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						
						
						
						$dataArr[$campaignid]['upfront'] 	= intval($this->uploadrates_data['package_mini']);
						$dataArr[$campaignid]['ecs'] 		= intval($this->uploadrates_data['package_mini_ecs']) * 12 * 2; // Down Payment + ECS
						
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						$dataArr[$campaignid]['multi_keyword']	= 1;
						
						$flx_prem_upfront_1yr 	= $dataArr[$campaignid]['upfront'];
						$flx_prem_ecs_1yr 		= $dataArr[$campaignid]['ecs'];
						
						$dataArr[$campaignid]['tenure_options'][0]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$flx_prem_upfront_1yr,"ecs"=>$dataArr[$campaignid]['ecs']);
						
						if($this->keyword_info['3.1']){
							$dataArr[$campaignid]['tenure_options'][0]['keyword'] 	= $this->keyword_info['3.1']['name'];
							$dataArr[$campaignid]['tenure_options'][0]['bitval'] 	= $this->keyword_info['3.1']['bitval'];
							$dataArr[$campaignid]['tenure_options'][0]['keyword_ecs'] 	= "mini_ecs";
							
							$dataArr[$campaignid]['price_disp']	= intval($this->uploadrates_data['package_mini']);
							
						}
						$flx_prem_upfront_2yr 	= $dataArr[$campaignid]['upfront'] * 1.5;
						$flx_prem_ecs_2yr 		= $dataArr[$campaignid]['ecs'] * 1.5;
						
						$dataArr[$campaignid]['tenure_options'][1]	= array("tenure_days"=>"730","tenure_name"=>"2 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'2nd year at 50% premium only',"tenure_months"=>24,"upfront"=>$flx_prem_upfront_2yr,"ecs"=>$flx_prem_ecs_2yr);
						
						if($this->keyword_info['3.2']){
							$dataArr[$campaignid]['tenure_options'][1]['keyword'] 	= $this->keyword_info['3.2']['name'];
							$dataArr[$campaignid]['tenure_options'][1]['bitval'] 	= $this->keyword_info['3.2']['bitval'];
							$dataArr[$campaignid]['tenure_options'][1]['keyword_ecs'] 	= "mini_ecs";
						}
					}
				}
			break;
			case 14 : // By Budget - Flexi
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->keyword_info[14]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[14]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[14]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
					if($this->restricted_catinfo['pkg_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					$dataArr[$campaignid]['ct_min_bdgt']	= intval($this->uploadrates_data['top_minbudget_package']);
				}
			break;
			case 18 : // Adwords By Package
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$extra_package_details_str = trim($this->uploadrates_data['extra_package_details']);
					$extra_package_details_arr    = json_decode($extra_package_details_str,true);
					if(count($extra_package_details_arr)>0){
						
						$pkg_upfront_budget = intval($extra_package_details_arr['116']['package_value']) * 12;
						$pkg_orig_budget = $pkg_upfront_budget;
						
						$pkg_specail_bdgt_arr = $this->getPkgSpecialBudget();
						
						if(count($pkg_specail_bdgt_arr)>0){
							
							$pkg_upfront_budget = $pkg_specail_bdgt_arr['upfront'];
							$pkg_ecs_budget 	= $pkg_specail_bdgt_arr['ecs'];
							
						}else{
							$pkg_upfront_budget = $pkg_orig_budget;
							if(intval($this->uploadrates_data['ecssecyrinc']) == 1){
								$pkg_ecs_budget = ($extra_package_details_arr['116']['package_value'] + ($extra_package_details_arr['116']['package_value'] * 50) / 100) * 12;
							}else{
								$pkg_ecs_budget = $extra_package_details_arr['116']['package_value'] * 12;
							}
						}
						
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= $pkg_upfront_budget;
						$dataArr[$campaignid]['ecs'] 		= $pkg_ecs_budget;
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						$dataArr[$campaignid]['multi_keyword']	= 1;
						
						$pkg_idx = 0;
						$block_2yr_ecs = 0;
						$package_oneyr_upfront = $pkg_upfront_budget;
						$package_oneyr_ecs = $pkg_ecs_budget;
						if(!empty($this->empcode)){
							$empminbdgt = $this->getEmpMinBudget();
							if(($empminbdgt >0) && ($empminbdgt < intval($this->uploadrates_data['top_minbudget_package']) )){
								$package_oneyr_upfront = intval($empminbdgt);
								$package_oneyr_ecs = intval($empminbdgt);
								$block_2yr_ecs = 1;
							}
						}
						
						$dataArr[$campaignid]['tenure_options'][$pkg_idx]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","multiplyFactor"=>1,"fixed_price"=>0,"payment_mode_available"=>"ECS/NON ECS","offer_text"=>"","tenure_months"=>12,"upfront"=>$package_oneyr_upfront,"ecs"=>$package_oneyr_ecs);
						if($this->keyword_info['18.1']){
							$dataArr[$campaignid]['tenure_options'][$pkg_idx]['keyword'] 	= $this->keyword_info['18.1']['name'];
							$dataArr[$campaignid]['tenure_options'][$pkg_idx]['bitval'] 	= $this->keyword_info['18.1']['bitval'];
							
							$dataArr[$campaignid]['price_disp']	= intval($extra_package_details_arr['116']['package_value']) * 12;
						}
						
						
						if($block_2yr_ecs !=1){
							
							$pune_contract = 0;
							if(strtolower($this->data_city) == 'pune'){
								$pune_contract = 1;
							}else{
								
								if($this->remote_zone == "pune"){
									$pune_contract = 1;
								}
							}
							
							$chennai_contract = 0;
							if($this->remote_zone == "chennai"){
								$chennai_contract = 1;
							}
							
							$coimbatore_contract = 0;
							if($this->remote_zone == "coimbatore"){
								$coimbatore_contract = 1;
							}
							
							
							$multiplyFactor = 1.5;
							$multiplyPer = "50%";
							if($pune_contract == 1){
								$multiplyFactor = 1.25;
								$multiplyPer = "25%";
							}else if($chennai_contract == 1 || $coimbatore_contract == 1){
								$multiplyFactor = 2;
								$multiplyPer = "100%";
							}
							
							
							$pkg_idx++;	
							$pkg2_upfront = $pkg_upfront_budget * $multiplyFactor;
							$dataArr[$campaignid]['tenure_options'][$pkg_idx]	= array("tenure_days"=>"730","tenure_name"=>"2 Years","multiplyFactor"=>$multiplyFactor,"fixed_price"=>0,"payment_mode_available"=>"NON ECS","offer_text"=>"2nd year at ".$multiplyPer." premium only","tenure_months"=>24,"upfront"=>$pkg2_upfront,"ecs"=>0);
							
							if($this->keyword_info['18.2']){
								$dataArr[$campaignid]['tenure_options'][$pkg_idx]['keyword'] 	= $this->keyword_info['18.2']['name'];
								$dataArr[$campaignid]['tenure_options'][$pkg_idx]['bitval'] 	= $this->keyword_info['18.2']['bitval'];
							}
						}
						$reseller_package_details_str = trim($this->uploadrates_data['reseller_package_details']);
						$reseller_package_details_arr    = json_decode($reseller_package_details_str,true);
						if(($this->finance_data['data_found'] != 1) || (($this->finance_data['pack_exp_45_days'] == 1) && (strtolower($this->data_city) == 'delhi'))){
							
							//~ $pkg_idx++;
							//~ $pkg_one_month_upfront = round($extra_package_details_arr['21']['package_value'] * 12);
							//~ $dataArr[$campaignid]['tenure_options'][$pkg_idx]	= array("tenure_days"=>"30","tenure_name"=>"1 Month","fixed_price"=>1,"payment_mode_available"=>"NON ECS","upfront"=> $pkg_one_month_upfront,"tenure_months"=>1);
							//~ if($this->keyword_info['18.08']){
								//~ $dataArr[$campaignid]['tenure_options'][$pkg_idx]['keyword'] 	= $this->keyword_info['18.08']['name'];
								//~ $dataArr[$campaignid]['tenure_options'][$pkg_idx]['bitval'] 	= $this->keyword_info['18.08']['bitval'];
							//~ }
							
							$pkg_idx++;
							$pkg_three_months_upfront = round($reseller_package_details_arr['23']['package_value'] * 12);
							if($pkg_three_months_upfront){
								$dataArr[$campaignid]['tenure_options'][$pkg_idx]	= array("tenure_days"=>"90","tenure_name"=>"3 Months","fixed_price"=>1,"payment_mode_available"=>"NON ECS","upfront"=> $pkg_three_months_upfront,"tenure_months"=>3);
								if($this->keyword_info['18.25']){
									$dataArr[$campaignid]['tenure_options'][$pkg_idx]['keyword'] 	= $this->keyword_info['18.25']['name'];
									$dataArr[$campaignid]['tenure_options'][$pkg_idx]['bitval'] 	= $this->keyword_info['18.25']['bitval'];
								}
							}
						}
						
						$dataArr[$campaignid]['ct_min_bdgt']	= intval($this->uploadrates_data['top_minbudget_package']);
						if(!empty($this->empcode)){
							$empminbdgt = $this->getEmpMinBudget();
							if(($empminbdgt >0) && ($empminbdgt < $dataArr[$campaignid]['ct_min_bdgt'] )){
								$dataArr[$campaignid]['ct_min_bdgt']	= $empminbdgt;
							}
						}
						$pkg_disc_per	= floatval($this->uploadrates_data['pkg_disc_per']);
						$pkg_disc_per	= number_format($pkg_disc_per, 2);
						$dataArr[$campaignid]['disc_per']		= $pkg_disc_per;
						$dataArr[$campaignid]['disc_amnt']		= intval($this->uploadrates_data['pkg_disc_eligib']);
					}
				}
			break;
			case 34 : // Lifetime (10 year Package)
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					
					$reseller_package_details_str = trim($this->uploadrates_data['reseller_package_details']);
					$reseller_package_details_arr    = json_decode($reseller_package_details_str,true);
					if(count($reseller_package_details_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						
						if(($this->finance_data['phonesearch_flag'] == 1) && ($this->finance_data['total_budget'] > 0) && ($this->finance_data['existing_vfl'] !=1)){
							
							
							
							if($this->allocID == 'RD'){
								$vfl_budget = round($reseller_package_details_arr['2120']['package_value'] * 12);
								
								$dataArr[$campaignid]['upfront']		= round($vfl_budget + ($vfl_budget * 0.25));
								$dataArr[$campaignid]['ecs']			= round($vfl_budget + ($vfl_budget * 0.25));
								$dataArr[$campaignid]['existing']		= 1;
							}else{
							
								if(strtolower($this->data_city) == 'chandigarh'){
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*2.5);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*2.5);
									$dataArr[$campaignid]['existing']		= 1;
								}else if(strtolower($this->data_city) == 'mumbai'){
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*2);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*2);
									$dataArr[$campaignid]['existing']		= 1;
								}else{
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*4);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*4);
									$dataArr[$campaignid]['existing']		= 1;
								}
								$vfl_budget = $dataArr[$campaignid]['upfront'];
							}
						}else if((strtolower($this->data_city) == 'kolkata') && ($this->finance_data['pack_exp_45_days'] == 1)){
							$vfl_budget = 15000;
							$dataArr[$campaignid]['upfront']		= $vfl_budget;
							$dataArr[$campaignid]['ecs']			= $vfl_budget;
							$dataArr[$campaignid]['existing']		= 0;
						}else if((strtolower($this->data_city) == 'mumbai') && ($this->finance_data['pack_exp_45_days'] == 1)){
							$vfl_budget = 10000; 
							$dataArr[$campaignid]['upfront']		= $vfl_budget;
							$dataArr[$campaignid]['ecs']			= $vfl_budget;
							$dataArr[$campaignid]['existing']		= 0;
						}else{
							
							
							$vfl_budget = round($reseller_package_details_arr['2120']['package_value'] * 12);
							
							if(strtolower($this->data_city) == 'mumbai1' || strtolower($this->data_city) == 'delhi1'){
								
								$dataArr[$campaignid]['upfront'] 	= $vfl_budget / 2;
								$dataArr[$campaignid]['ecs']		= $vfl_budget / 2;								
								
							}else{
								$dataArr[$campaignid]['upfront'] 	= $vfl_budget;
								$dataArr[$campaignid]['ecs']		= $vfl_budget;
							}
							$dataArr[$campaignid]['existing']		= 0;
						}
						$dataArr[$campaignid]['pricetab']	= $vfl_budget;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[34]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[34]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[34]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						
						$dataArr[$campaignid]['disc_amnt']		= intval($reseller_package_details_arr['2000']['discount']);
						
					}
				}
			break;
			
			case 37 : // VFL Low Price
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					
					$reseller_package_details_str = trim($this->uploadrates_data['reseller_package_details']);
					$reseller_package_details_arr    = json_decode($reseller_package_details_str,true);
					if(count($reseller_package_details_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						
						if(($this->finance_data['phonesearch_flag'] == 1) && ($this->finance_data['total_budget'] > 0) && ($this->finance_data['existing_vfl'] !=1)){
							
							if($this->allocID == 'RD'){
								$vfl_budget = round($reseller_package_details_arr['3120']['package_value'] * 12);
								
								$dataArr[$campaignid]['upfront']		= round($vfl_budget + ($vfl_budget * 0.25));
								$dataArr[$campaignid]['ecs']			= round($vfl_budget + ($vfl_budget * 0.25));
								$dataArr[$campaignid]['existing']		= 1;
							}else{
							
								if(strtolower($this->data_city) == 'chandigarh'){
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*2.5);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*2.5);
									$dataArr[$campaignid]['existing']		= 1;
								}else if(strtolower($this->data_city) == 'mumbai'){
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*2);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*2);
									$dataArr[$campaignid]['existing']		= 1;
								}else{
									$dataArr[$campaignid]['upfront']		= round($this->finance_data['total_budget']*4);
									$dataArr[$campaignid]['ecs']			= round($this->finance_data['total_budget']*4);
									$dataArr[$campaignid]['existing']		= 1;
								}
								$vfl_budget = $dataArr[$campaignid]['upfront'];
							}
						}else if((strtolower($this->data_city) == 'kolkata') && ($this->finance_data['pack_exp_45_days'] == 1)){
							$vfl_budget = 15000;
							$dataArr[$campaignid]['upfront']		= $vfl_budget;
							$dataArr[$campaignid]['ecs']			= $vfl_budget;
							$dataArr[$campaignid]['existing']		= 0;
						}else if((strtolower($this->data_city) == 'mumbai') && ($this->finance_data['pack_exp_45_days'] == 1)){
							$vfl_budget = 10000; 
							$dataArr[$campaignid]['upfront']		= $vfl_budget;
							$dataArr[$campaignid]['ecs']			= $vfl_budget;
							$dataArr[$campaignid]['existing']		= 0;
						}else{
							
							$vfl_budget = round($reseller_package_details_arr['3120']['package_value'] * 12);
							
							$dataArr[$campaignid]['upfront'] 	= $vfl_budget;
							$dataArr[$campaignid]['ecs']		= $vfl_budget;
							
							$dataArr[$campaignid]['existing']		= 0;
						}
						$dataArr[$campaignid]['pricetab']	= $vfl_budget;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[37]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[37]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[37]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						$dataArr[$campaignid]['disc_amnt']		= intval($reseller_package_details_arr['3000']['discount']);
						
					}
				}
			break;
			
			case 6 : // Complete Suite
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$cs_1yr_pricing_arr = $this->omni_pricing[2]; // Complete Suite For 1 Year
					$cs_5yr_pricing_arr = $this->omni_pricing[1]; // Complete Suite For 5 Years
					if(count($cs_1yr_pricing_arr)>0 || count($cs_5yr_pricing_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['multi_keyword']	= 1;
						
						
						$cs_idx = -1;
						if(count($cs_1yr_pricing_arr)>0){
							
							$cs_1yr_upfront 		= $cs_1yr_pricing_arr['upfront'] + $cs_1yr_pricing_arr['setup_upfront'];
							$cs_1yr_ecs 			= $cs_1yr_pricing_arr['upfront'] + $cs_1yr_pricing_arr['setup_upfront'];
							$cs_1yr_setup_upfront 	= $cs_1yr_pricing_arr['setup_upfront'];
							$cs_1yr_setup_ecs 		= $cs_1yr_pricing_arr['setup_ecs'];
							
							$cs_idx ++;
							$dataArr[$campaignid]['tenure_options'][$cs_idx]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$cs_1yr_upfront,"ecs"=>$cs_1yr_ecs,"setup_upfront"=>$cs_1yr_setup_upfront,"setup_ecs"=>$cs_1yr_setup_ecs);
							
							if($this->keyword_info['6.1']){
								$dataArr[$campaignid]['tenure_options'][$cs_idx]['keyword'] 	= $this->keyword_info['6.1']['name'];
								$dataArr[$campaignid]['tenure_options'][$cs_idx]['bitval'] 		= $this->keyword_info['6.1']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']	= $cs_1yr_upfront;
						}
						if(count($cs_5yr_pricing_arr)>0){
							$cs_5yr_upfront 		= $cs_5yr_pricing_arr['upfront'] + $cs_5yr_pricing_arr['setup_upfront'];
							$cs_5yr_setup_upfront 	= $cs_5yr_pricing_arr['setup_upfront'];
							
							$cs_idx ++;
							$dataArr[$campaignid]['tenure_options'][$cs_idx]	= array("tenure_days"=>"1825","tenure_name"=>"5 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>60,"upfront"=>$cs_5yr_upfront,"setup_upfront"=>$cs_5yr_setup_upfront);
							
							if($this->keyword_info['6.5']){
								$dataArr[$campaignid]['tenure_options'][$cs_idx]['keyword'] 	= $this->keyword_info['6.5']['name'];
								$dataArr[$campaignid]['tenure_options'][$cs_idx]['bitval'] 		= $this->keyword_info['6.5']['bitval'];
							}
						}
					}
				}
			break;
			
			case 16 : // Package Festive Combo Offer
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$pkg_festive_combo_arr = $this->omni_pricing[5]; // Package Festive Combo Offer 
					if(count($pkg_festive_combo_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= $pkg_festive_combo_arr['upfront'] + $pkg_festive_combo_arr['setup_upfront'];
						$dataArr[$campaignid]['ecs'] 		= $pkg_festive_combo_arr['ecs'] + $pkg_festive_combo_arr['setup_ecs'];
						$dataArr[$campaignid]['setup_upfront'] = $pkg_festive_combo_arr['setup_upfront'];
						$dataArr[$campaignid]['setup_ecs'] = $pkg_festive_combo_arr['setup_ecs'];
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_allowed'];
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[16]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[16]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[16]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						if($this->restricted_catinfo['pkg_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						$dataArr[$campaignid]['price_disp']	= $pkg_festive_combo_arr['upfront'] + $pkg_festive_combo_arr['setup_upfront'];
						$dataArr[$campaignid]['ct_min_bdgt'] = min($dataArr[$campaignid]['upfront'],$dataArr[$campaignid]['ecs']);
					}
				}
			break;
			
			
			case 21 : // Package Festive Combo Offer Q4
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$pkg_festive_combo_arr = $this->festiveComboBudget(1);
					
					if(count($pkg_festive_combo_arr)>0){
						
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$bnr_campinfo_arr 					= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
						$dataArr[$campaignid]['actcamp'] 	= $bnr_campinfo_arr['actcampid'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['pkg_bnr_allowed'];
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						if($this->keyword_info[16]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[16]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[16]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						if($this->restricted_catinfo['pkg_bnr_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['pkg_bnr_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						
						$pkgcombo_idx = -1;
						if(count($pkg_festive_combo_arr['365']) > 0){
							
							$pkgcombo_idx ++;
							$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$pkg_festive_combo_arr['365']['upfront'],"ecs"=>$pkg_festive_combo_arr['365']['ecs']);
							
							if($this->keyword_info['16']){
								$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]['keyword'] 	= $this->keyword_info['16']['name'];
								$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]['bitval'] 		= $this->keyword_info['16']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']	= $pkg_festive_combo_arr['365']['upfront'];
						}
						if(count($pkg_festive_combo_arr['3650']) > 0){
						
							$pkgcombo_idx ++;
							$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]	= array("tenure_days"=>"3650","tenure_name"=>"10 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$pkg_festive_combo_arr['3650']['upfront'],"ecs"=>$pkg_festive_combo_arr['3650']['ecs']);
							if($this->keyword_info['16']){
								$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]['keyword'] = $this->keyword_info['16']['name'];
								$dataArr[$campaignid]['tenure_options'][$pkgcombo_idx]['bitval'] 	= $this->keyword_info['16']['bitval'];
							}
						}
						
					}
				}
			break;
			
			case 25 : // Banner
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
				$banner_rotation_details_arr = json_decode($this->uploadrates_data['banner_rotation_details'],true);
				if(count($banner_rotation_details_arr)>0){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					$dataArr[$campaignid]['rot'] 		= 1;
					$bnr_campinfo_arr 					= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['actcamp'] 	= $bnr_campinfo_arr['actcampid'];
					$dataArr[$campaignid]['roteligib'] 	= $bnr_campinfo_arr['roteligib'];
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['bnr_allowed'];
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->restricted_catinfo['bnr_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					$dataArr[$campaignid]['multi_keyword']	= 1;								
					
					
					
					$banner_idx = -1;
					
					$banner_1yr_budget_arr = $banner_rotation_details_arr[1];
					if(count($banner_1yr_budget_arr) > 0){
						
						$banner_idx ++;
						$factor = 1 + 1; // n + 1 - n denotes rotation
						$bnr_1yr_upfront 		= $banner_1yr_budget_arr['upfront'];
						$bnr_1yr_ecs 			= $banner_1yr_budget_arr['ecs'] * 12;
						$dataArr[$campaignid]['tenure_options'][$banner_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - Rotation Banner - 1 Year","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$bnr_1yr_upfront,"ecs"=>$bnr_1yr_ecs);
						if($this->keyword_info['25']){
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['keyword'] = $this->keyword_info['25']['name'];
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['bitval'] 	= $this->keyword_info['25']['bitval'];
						}
						$dataArr[$campaignid]['price_disp']	= $bnr_1yr_upfront * 2;
					}
					
					$banner_3yrs_budget_arr = $banner_rotation_details_arr[3];
					if(count($banner_3yrs_budget_arr) > 0){
						
						$banner_idx ++;
						$factor = 1 + 1; // n + 1 - n denotes rotation
						$bnr_3yrs_upfront 		= $banner_3yrs_budget_arr['upfront'];
						$bnr_3yrs_ecs 			= $banner_3yrs_budget_arr['ecs'] * 12;
						$dataArr[$campaignid]['tenure_options'][$banner_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - Rotation Banner - 3 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>36,"upfront"=>$bnr_3yrs_upfront);
						if($this->keyword_info['25']){
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['keyword'] = $this->keyword_info['25']['name'];
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['bitval'] 	= $this->keyword_info['25']['bitval'];
						}
					}
					
					$banner_10yrs_budget_arr = $banner_rotation_details_arr[10];
					if(count($banner_10yrs_budget_arr) > 0){
						
						$banner_idx ++;
						$factor = 1 + 1; // n + 1 - n denotes rotation
						$bnr_10yrs_upfront 		= $banner_10yrs_budget_arr['upfront'];
						$bnr_10yrs_ecs 			= $banner_10yrs_budget_arr['ecs'] * 12;
						$dataArr[$campaignid]['tenure_options'][$banner_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - Rotation Banner - Forever","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$bnr_10yrs_upfront);
						if($this->keyword_info['25']){
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['keyword'] = $this->keyword_info['25']['name'];
							$dataArr[$campaignid]['tenure_options'][$banner_idx]['bitval'] 	= $this->keyword_info['25']['bitval'];
						}
					}
					
					
					
				}
				}
			break;
			
			case 26 : // SMS Promo
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= 1;
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->keyword_info[26]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[26]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[26]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
				}
			break;
			
			case 29 : // CRISIL
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					
					if(intval($this->uploadrates_data['crisil_price']) >0){
						
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= intval($this->uploadrates_data['crisil_price']);
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[29]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[29]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[29]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						$dataArr[$campaignid]['price_disp']	= intval($this->uploadrates_data['crisil_price']);
					}
				}
			break;
			
			case 4 : // JDRR 2019
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					if(count($this->jdrr_pricing_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= intval($this->jdrr_pricing_arr['365']['upfront_payment']);
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['16*12']['init_per']	= 0;
						$dataArr[$campaignid]['24*18']['init_per']	= 30;
						$dataArr[$campaignid]['16*12']['qty_per']	= 50;
						$dataArr[$campaignid]['24*18']['qty_per']	= 50;
						
						$dataArr[$campaignid]['multi_keyword']		= 1;
						
						$jdrr_idx = -1;
						$jdrr_1yr_price = intval($this->jdrr_pricing_arr['365']['upfront_payment']);
						if($jdrr_1yr_price > 0){
							$jdrr_idx ++;
							
							$dataArr[$campaignid]['tenure_options'][$jdrr_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - JDRR 1 Year","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$jdrr_1yr_price);
							
							if($this->keyword_info['4.1']){
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['keyword'] 	= $this->keyword_info['4.1']['name'];
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['bitval'] 	= $this->keyword_info['4.1']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']	= $jdrr_1yr_price;
						}
						
						$jdrr_3yr_price = intval($this->jdrr_pricing_arr['1095']['upfront_payment']);
						if($jdrr_3yr_price > 0){
							$jdrr_idx ++;
							
							$dataArr[$campaignid]['tenure_options'][$jdrr_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - JDRR 3 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>36,"upfront"=>$jdrr_3yr_price);
							
							if($this->keyword_info['4.3']){
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['keyword'] 	= $this->keyword_info['4.3']['name'];
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['bitval'] 	= $this->keyword_info['4.3']['bitval'];
							}
						}
						
						$jdrr_10yr_price = intval($this->jdrr_pricing_arr['3650']['upfront_payment']);
						if($jdrr_10yr_price > 0){
							$jdrr_idx ++;
							
							$dataArr[$campaignid]['tenure_options'][$jdrr_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - JDRR 10 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$jdrr_10yr_price);
							
							if($this->keyword_info['4.5']){
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['keyword'] 	= $this->keyword_info['4.5']['name'];
								$dataArr[$campaignid]['tenure_options'][$jdrr_idx]['bitval'] 	= $this->keyword_info['4.5']['bitval'];
							}
						}
						
						
					}
				}
			break;
			
			case 30 : // JDRR 2019
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					if(count($this->jdrr_pricing_arr)>0){
						$jdrr_userchoice_price = 9999;
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= $jdrr_userchoice_price;
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['16*12']['init_per']	= 0;
						$dataArr[$campaignid]['24*18']['init_per']	= 30;
						$dataArr[$campaignid]['16*12']['qty_per']	= 50;
						$dataArr[$campaignid]['24*18']['qty_per']	= 50;
						
						
						if($this->keyword_info[30]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[30]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[30]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						
						$dataArr[$campaignid]['price_disp']			= $jdrr_userchoice_price;
					}
				}
			break;
			
			
			case 5 : // JDRR Plus
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
				$jdrrplus_cids_arr = array("225" => "Jdrr Plus - Standard", "2252" => "JDRR Plus - Classic", "2253" => "JDRR Plus - Premium");
				$jdrrplus_pricing_arr = $this->OthersCampaignData($jdrrplus_cids_arr);
				//print"<pre>";print_r($jdrrplus_pricing_arr);
			
				if(count($jdrrplus_pricing_arr)>0){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					
					$bnr_campinfo_arr 					= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['actcamp'] 	= $bnr_campinfo_arr['actcampid'];
					$dataArr[$campaignid]['roteligib'] 	= $bnr_campinfo_arr['roteligib'];
					
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['bnr_allowed'];;
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->restricted_catinfo['bnr_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					$dataArr[$campaignid]['16*12']['init_per']	= 0;
					$dataArr[$campaignid]['24*18']['init_per']	= 30;
					$dataArr[$campaignid]['16*12']['qty_per']	= 50;
					$dataArr[$campaignid]['24*18']['qty_per']	= 50;
					$dataArr[$campaignid]['jdrr_price']			= intval($this->jdrr_pricing_arr['365']['upfront_payment']);
					$dataArr[$campaignid]['multi_keyword']		= 1;
					
					
					
					$jdplus_idx = -1;
					$jdrrplus_standard_pricing_arr = $jdrrplus_pricing_arr[225];
					if(count($jdrrplus_standard_pricing_arr) > 0){
						$jdplus_idx ++;
						$jdrrplus_standard_upfront 	= $jdrrplus_standard_pricing_arr['upfront'];
						$jdrrplus_standard_ecs	 	= round($jdrrplus_standard_pricing_arr['ecs'] * 12);
						
						$dataArr[$campaignid]['tenure_options'][$jdplus_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - JDRR + Banner 1 Year","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$jdrrplus_standard_upfront,"ecs"=>$jdrrplus_standard_ecs);
						
						if($this->keyword_info['5.1']){
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['keyword'] 	= $this->keyword_info['5.1']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['bitval'] 	= $this->keyword_info['5.1']['bitval'];
						}
						$dataArr[$campaignid]['price_disp']	= $jdrrplus_standard_pricing_arr['upfront'];
					}
					
					$jdrrplus_classic_pricing_arr = $jdrrplus_pricing_arr[2252];
					if(count($jdrrplus_classic_pricing_arr) > 0){
						$jdplus_idx ++;
						$jdrrplus_classic_upfront 	= $jdrrplus_classic_pricing_arr['upfront'];
						$jdrrplus_classic_ecs	 	= round($jdrrplus_classic_pricing_arr['ecs'] * 12);
						
						$dataArr[$campaignid]['tenure_options'][$jdplus_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - JDRR + Banner 3 Years","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>36,"upfront"=>$jdrrplus_classic_upfront,"ecs"=>$jdrrplus_classic_ecs);
						
						if($this->keyword_info['5.3']){
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['keyword'] 	= $this->keyword_info['5.3']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['bitval'] 	= $this->keyword_info['5.3']['bitval'];
						}
					}
					
					$jdrrplus_premium_pricing_arr = $jdrrplus_pricing_arr[2253];
					if(count($jdrrplus_premium_pricing_arr) > 0){
						$jdplus_idx ++;
						$jdrrplus_premium_upfront 	= $jdrrplus_premium_pricing_arr['upfront'];
						$jdrrplus_premium_ecs	 	= round($jdrrplus_premium_pricing_arr['ecs'] * 12);
						
						$dataArr[$campaignid]['tenure_options'][$jdplus_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - JDRR + Banner Forever","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$jdrrplus_premium_upfront,"ecs"=>$jdrrplus_premium_ecs);
						
						if($this->keyword_info['5.5']){
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['keyword'] 	= $this->keyword_info['5.5']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdplus_idx]['bitval'] 	= $this->keyword_info['5.5']['bitval'];
						}
					}
					
				}
				}
			break;
			
			case 17 : // JDRR Super
			if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
				$jddr_super_standard_arr 	= $this->omni_pricing[16]; // JDRR Super – Standard
				$jddr_super_classic_arr 	= $this->omni_pricing[17]; // JDRR Super – Classic
				$jddr_super_premium_arr 	= $this->omni_pricing[18]; // JDRR Super – Premium
				if(count($jddr_super_standard_arr) || count($jddr_super_classic_arr) || count($jddr_super_premium_arr)){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					
					$bnr_campinfo_arr 						= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['actcamp'] 		= $bnr_campinfo_arr['actcampid'];
					$dataArr[$campaignid]['roteligib'] 		= $bnr_campinfo_arr['roteligib'];
					
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['bnr_allowed'];;
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					if($this->restricted_catinfo['bnr_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					$dataArr[$campaignid]['16*12']['init_per']	= 0;
					$dataArr[$campaignid]['24*18']['init_per']	= 30;
					$dataArr[$campaignid]['16*12']['qty_per']	= 50;
					$dataArr[$campaignid]['24*18']['qty_per']	= 50;
					$dataArr[$campaignid]['jdrr_price']			= intval($this->jdrr_pricing_arr['365']['upfront_payment']);
					$dataArr[$campaignid]['multi_keyword']		= 1;
					
					$jdsuper_idx = -1;
					if(count($jddr_super_standard_arr) > 0){
						$jdsuper_idx ++;
						$jdstandard_upfront 		= $jddr_super_standard_arr['upfront'] + $jddr_super_standard_arr['setup_upfront'] + $jddr_super_standard_arr['plus_banner'] + $jddr_super_standard_arr['plus_jdrr'];
						$jdstandard_ecs	 			= $jddr_super_standard_arr['ecs'] + $jddr_super_standard_arr['setup_ecs'] + $jddr_super_standard_arr['plus_banner'] + $jddr_super_standard_arr['plus_jdrr'];
						$jdstandard_setup_upfront 	= $jddr_super_standard_arr['setup_upfront'];
						$jdstandard_setup_ecs 		= $jddr_super_standard_arr['setup_ecs'];
						
						$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - JDRR + Banner + Website (1 Year)","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$jdstandard_upfront,"ecs"=>$jdstandard_ecs,"setup_upfront"=>$jdstandard_setup_upfront,"setup_ecs"=>$jdstandard_setup_ecs);
						
						if($this->keyword_info['17.1']){
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['keyword'] 	= $this->keyword_info['17.1']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['bitval'] 	= $this->keyword_info['17.1']['bitval'];
						}
					}
					
					if(count($jddr_super_classic_arr) > 0){
						$jdsuper_idx ++;
						$jdclassic_upfront 		= $jddr_super_classic_arr['upfront'] + $jddr_super_classic_arr['setup_upfront'] + $jddr_super_classic_arr['plus_banner'] + $jddr_super_classic_arr['plus_jdrr'];
						$jdclassic_ecs	 			= $jddr_super_classic_arr['ecs'] + $jddr_super_classic_arr['setup_ecs'] + $jddr_super_classic_arr['plus_banner'] + $jddr_super_classic_arr['plus_jdrr'];
						$jdclassic_setup_upfront 	= $jddr_super_classic_arr['setup_upfront'];
						$jdclassic_setup_ecs 		= $jddr_super_classic_arr['setup_ecs'];
						
						$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - JDRR + Banner + Website (3 Years)","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>36,"upfront"=>$jdclassic_upfront,"ecs"=>$jdclassic_ecs,"setup_upfront"=>$jdclassic_setup_upfront,"setup_ecs"=>$jdclassic_setup_ecs);
						
						if($this->keyword_info['17.3']){
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['keyword'] 	= $this->keyword_info['17.3']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['bitval'] 	= $this->keyword_info['17.3']['bitval'];
						}
					}
					
					if(count($jddr_super_premium_arr) > 0){
						$jdsuper_idx ++;
						$jdpremium_upfront 		= $jddr_super_premium_arr['upfront'] + $jddr_super_premium_arr['setup_upfront'] + $jddr_super_premium_arr['plus_banner'] + $jddr_super_premium_arr['plus_jdrr'];
						$jdpremium_ecs	 			= $jddr_super_premium_arr['ecs'] + $jddr_super_premium_arr['setup_ecs'] + $jddr_super_premium_arr['plus_banner'] + $jddr_super_premium_arr['plus_jdrr'];
						$jdpremium_setup_upfront 	= $jddr_super_premium_arr['setup_upfront'];
						$jdpremium_setup_ecs 		= $jddr_super_premium_arr['setup_ecs'];
						
						$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - JDRR + Banner + Website (Forever)","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$jdpremium_upfront,"ecs"=>$jdpremium_ecs,"setup_upfront"=>$jdpremium_setup_upfront,"setup_ecs"=>$jdpremium_setup_ecs);
						
						if($this->keyword_info['17.5']){
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['keyword'] 	= $this->keyword_info['17.5']['name'];
							$dataArr[$campaignid]['tenure_options'][$jdsuper_idx]['bitval'] 	= $this->keyword_info['17.5']['bitval'];
						}
					}
				}
				}
			break;
			
			case 12 : // Own Dynamic Website & Mobile Site With Transaction Capability
				
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$omniextradetails_str = trim($this->uploadrates_data['omniextradetails']);
					$omniextradetails_arr    = json_decode($omniextradetails_str,true);
					
					if(count($omniextradetails_arr)>0){
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['multi_keyword']	= 1;
						$dataArr[$campaignid]['omni_contract']	= $this->findOMNIContract();
						
						$web_idx = -1;
						/*if(count($omniextradetails_arr['741']['upfront']) > 0){
							$web_idx ++;
							$web_1yr_upfront 		= $omniextradetails_arr['741']['upfront'] + $omniextradetails_arr['741']['down_payment'];
							$web_1yr_ecs	 		= ($omniextradetails_arr['741']['ecs'] * 12) + $omniextradetails_arr['741']['down_payment'];
							$web_1yr_ecs			= round($web_1yr_ecs);
							$web_1yr_setup_upfront 	= $omniextradetails_arr['741']['down_payment'];
							$web_1yr_setup_ecs 		= $omniextradetails_arr['741']['down_payment'];
							
							$dataArr[$campaignid]['tenure_options'][$web_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - Website With 1 Year Hosting","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$web_1yr_upfront,"ecs"=>$web_1yr_ecs,"setup_upfront"=>$web_1yr_setup_upfront,"setup_ecs"=>$web_1yr_setup_ecs);
							
							if($this->keyword_info['12.1']){
								$dataArr[$campaignid]['tenure_options'][$web_idx]['keyword'] 	= $this->keyword_info['12.1']['name'];
								$dataArr[$campaignid]['tenure_options'][$web_idx]['bitval'] 	= $this->keyword_info['12.1']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']	= $omniextradetails_arr['741']['upfront'] + $omniextradetails_arr['741']['down_payment'];
						}
						
						
						if(count($omniextradetails_arr['749']['upfront']) > 0){
							$web_idx ++;
							$web_3yr_upfront 		= $omniextradetails_arr['749']['upfront'] + $omniextradetails_arr['749']['down_payment'];
							$web_3yr_ecs 			= ($omniextradetails_arr['749']['ecs'] * 12) + $omniextradetails_arr['749']['down_payment'];
							$web_3yr_setup_upfront 	= $omniextradetails_arr['749']['down_payment'];
							$web_3yr_setup_ecs 		= $omniextradetails_arr['749']['down_payment'];
							
							$dataArr[$campaignid]['tenure_options'][$web_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - Website With 3 Years Hosting","fixed_price"=>1,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>36,"upfront"=>$web_3yr_upfront,"ecs"=>$web_3yr_ecs,"setup_upfront"=>$web_3yr_setup_upfront,"setup_ecs"=>$web_3yr_setup_ecs);
							
							if($this->keyword_info['12.3']){
								$dataArr[$campaignid]['tenure_options'][$web_idx]['keyword'] 	= $this->keyword_info['12.3']['name'];
								$dataArr[$campaignid]['tenure_options'][$web_idx]['bitval'] 	= $this->keyword_info['12.3']['bitval'];
							}
						}*/
						if(count($omniextradetails_arr['748']['upfront']) > 0){
							$web_idx ++;
							//$web_10yr_upfront 		= $omniextradetails_arr['748']['upfront'] + $omniextradetails_arr['748']['down_payment'];
							$web_10yr_upfront 		= 15000;
							//$web_10yr_setup_upfront = $omniextradetails_arr['748']['down_payment'];
							$web_10yr_setup_upfront = 1500;
							
							$dataArr[$campaignid]['tenure_options'][$web_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - Website With Forever Hosting","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$web_10yr_upfront,"setup_upfront"=>$web_10yr_setup_upfront);
							
							if($this->keyword_info['12.5']){
								$dataArr[$campaignid]['tenure_options'][$web_idx]['keyword'] 	= $this->keyword_info['12.5']['name'];
								$dataArr[$campaignid]['tenure_options'][$web_idx]['bitval'] 	= $this->keyword_info['12.5']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']	= $web_10yr_upfront;
						}
					}
				}
				
			break;
			
			case 8 : // Basic iPhone App
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$omniextradetails_str = trim($this->uploadrates_data['omniextradetails']);
					$omniextradetails_arr    = json_decode($omniextradetails_str,true);
					if(count($omniextradetails_arr)>0){
						
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= $omniextradetails_arr['743']['upfront'];
						//$dataArr[$campaignid]['ecs'] 		= $omniextradetails_arr['743']['ecs'] * 12; // only upfront for Mobile Application (IOS)
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[8]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[8]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[8]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						$dataArr[$campaignid]['price_disp']	= $omniextradetails_arr['743']['upfront'];
					}
				}
			break;
			
			case 9 : // Basic Android App
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$omniextradetails_str = trim($this->uploadrates_data['omniextradetails']);
					$omniextradetails_arr    = json_decode($omniextradetails_str,true);
					if(count($omniextradetails_arr)>0){
						
						$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
						$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
						$dataArr[$campaignid]['upfront'] 	= $omniextradetails_arr['742']['upfront'];
						//$dataArr[$campaignid]['ecs'] 		= $omniextradetails_arr['742']['ecs'] * 12; // only upfront for Mobile Application (IOS)
						$dataArr[$campaignid]['actcamp'] 	= $row_child_camp['actual_campaign'];
						$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
						$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
						$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
						$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
						$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
						$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
						$dataArr[$campaignid]['allowed'] 	= 1;
						$dataArr[$campaignid]['parent'] 	= 0;
						$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
						$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
						$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
						$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
						$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
						$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
						$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
						if($this->keyword_info[9]){
							$dataArr[$campaignid]['keyword']	= $this->keyword_info[9]['name'];
							$dataArr[$campaignid]['bitval']		= $this->keyword_info[9]['bitval'];
							$dataArr[$campaignid]['multi_keyword']	= 0;
						}
						$dataArr[$campaignid]['price_disp']	= $omniextradetails_arr['742']['upfront'];
					}
				}
			break;
			
			case 31 : // Email
				$emailprice_val = $this->getEmailPrice();
				if($emailprice_val > 0){
					if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					
						$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
						$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
						$dataArr[$campaignid]['price'] 			= $emailprice_val;
						$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
						$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
						$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
						$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
						$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
						$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
						$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
						$dataArr[$campaignid]['allowed'] 		= 1;
						$dataArr[$campaignid]['parent'] 		= 0;
						$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
						$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
						$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
						$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
						$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
						$dataArr[$campaignid]['imghdr']			= $row_child_camp['image_header'];
					}
				}
			break;
			
			case 32 : // SMS
				$smsprice_arr = $this->getSMSPrice(83);
				if(count($smsprice_arr) > 0){
					if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
						
						$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
						$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
						$dataArr[$campaignid]['upfront'] 		= $smsprice_arr['price_upfront'];
						$dataArr[$campaignid]['ecs'] 			= $smsprice_arr['price_ecs'];
						$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
						$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
						$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
						$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
						$dataArr[$campaignid]['combocamp'] 	= trim($row_child_camp['combo_campaign']);
						$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
						$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
						$dataArr[$campaignid]['allowed'] 		= 1;
						$dataArr[$campaignid]['parent'] 		= 0;
						$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
						$dataArr[$campaignid]['back_small']	= trim($row_child_camp['back_small_lite']);
						$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
						$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
						$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
						$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
						$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
						$dataArr[$campaignid]['imghdr']			= $row_child_camp['image_header'];
					}
				}
			break;
			
			case 33 : // SSL Certificate
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$dataArr[$campaignid]['upfront'] 		= intval($this->uploadrates_data['ssl_upfront']);
					$dataArr[$campaignid]['ecs'] 			= intval($this->uploadrates_data['ssl_ecs']);
					$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 	= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= 1;
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']	= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= $row_child_camp['image_header'];
				}
			break;
			
			case 35 : // National Listing
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
				
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= 1;
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= $row_child_camp['image_header'];
					$dataArr[$campaignid]['multi_keyword']	= 1;	
					
					$dataArr[$campaignid]['tenure_options'][0]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","fixed_price"=>0,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>12);
					if($this->keyword_info['35.1']){
						$dataArr[$campaignid]['tenure_options'][0]['keyword'] 	= $this->keyword_info['35.1']['name'];
						$dataArr[$campaignid]['tenure_options'][0]['bitval'] 	= $this->keyword_info['35.1']['bitval'];
					}
					
					$dataArr[$campaignid]['tenure_options'][1]	= array("tenure_days"=>"730","tenure_name"=>"2 Years","fixed_price"=>0,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>24);
					if($this->keyword_info['35.2']){
						$dataArr[$campaignid]['tenure_options'][1]['keyword'] 	= $this->keyword_info['35.2']['name'];
						$dataArr[$campaignid]['tenure_options'][1]['bitval'] 	= $this->keyword_info['35.2']['bitval'];
					}
					
					//if(($this->finance_data['phonesearch_flag'] != 1) && ($this->finance_data['national_active'] != 1)){
						$dataArr[$campaignid]['tenure_options'][2]	= array("tenure_days"=>"3650","tenure_name"=>"VFL","fixed_price"=>0,'payment_mode_available'=>'ECS/NON ECS','offer_text'=>'',"tenure_months"=>120);
						if($this->keyword_info['35.5']){ // 35.10 is not able to update as its taking 35.1 only
							$dataArr[$campaignid]['tenure_options'][2]['keyword'] 	= $this->keyword_info['35.5']['name'];
							$dataArr[$campaignid]['tenure_options'][2]['bitval'] 	= $this->keyword_info['35.5']['bitval'];
						}
					//}
				}	
					
			break;
			
			case 15 : // Domain Registration Fees
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 	= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= 1;
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']	= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= $row_child_camp['image_header'];
				}
			break;
			
			case 19 : // PDG Festive Combo
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= 1;
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= trim($row_child_camp['image_header']);
					
					$pdgfestive_combo_bdgt_arr 				= $this->omni_pricing[11]; // PDG Festive Combo					
					$dataArr[$campaignid]['minbdgt']		= trim($pdgfestive_combo_bdgt_arr['upfront']);
					
					if($this->keyword_info[19]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[19]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[19]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
					$dataArr[$campaignid]['ct_min_bdgt'] = trim($pdgfestive_combo_bdgt_arr['upfront']);
					
					
					$pdgfest_tenure_arr[] = array("tenure_name"=> "1 Year", "days"=> 365, "tenure_days"=> 365, "tenure_months"=> 12, "exclusive"=> 1);
					$pdgfest_tenure_arr[] = array("tenure_name"=> "2 Year", "days"=> 730, "tenure_days"=> 730, "tenure_months"=> 24, "exclusive"=> 0);
					$pdgfest_tenure_arr[] = array("tenure_name"=> "3 Years", "days"=> 1095, "tenure_days"=> 1095, "tenure_months"=> 36, "exclusive"=> 0);
					$pdgfest_tenure_arr[] = array("tenure_name"=> "5 years", "days"=> 1825, "tenure_days"=> 1825, "tenure_months"=> 60, "exclusive"=> 0);
					$pdgfest_tenure_arr[] = array("tenure_name"=> "VFL", "days"=> 3650, "tenure_days"=> 3650, "tenure_months"=> 120, "exclusive"=> 0);
					
					$dataArr[$campaignid]['tenure_options'] = (object) $pdgfest_tenure_arr;
					
					
				}
			break;
			
			
			case 22 : // PDG Festive Combo Q4
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					
					$pdg_festive_combo_arr = $this->festiveComboBudget(2);
					
					if(count($pdg_festive_combo_arr)>0){
						$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
						$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
						$bnr_campinfo_arr 						= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
						$dataArr[$campaignid]['actcamp'] 		= $bnr_campinfo_arr['actcampid'];
						$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
						$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
						$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
						$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
						$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
						$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
						$dataArr[$campaignid]['allowed'] 		= $this->restricted_catinfo['bnr_allowed'];
						$dataArr[$campaignid]['parent'] 		= 0;
						$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
						$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
						$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
						$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
						$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
						$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
						$dataArr[$campaignid]['imghdr']			= trim($row_child_camp['image_header']);
						$dataArr[$campaignid]['multi_keyword']	= 1;
						if($this->restricted_catinfo['bnr_allowed'] === 0){
							$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
							$dataArr[$campaignid]['blktype']= 'category';
						}
						
						$pdgcombo_idx = -1;
						if(count($pdg_festive_combo_arr['365']) > 0){
							
							$pdgcombo_idx ++;
							$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]	= array("tenure_days"=>"365","tenure_name"=>"1 Year","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>12,"upfront"=>$pdg_festive_combo_arr['365']['upfront'],"ecs"=>$pdg_festive_combo_arr['365']['ecs']);
							
							if($this->keyword_info['19']){
								$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]['keyword'] 	= $this->keyword_info['19']['name'];
								$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]['bitval'] 		= $this->keyword_info['19']['bitval'];
							}
							$dataArr[$campaignid]['price_disp']		= $pdg_festive_combo_arr['365']['upfront'];
							$dataArr[$campaignid]['minbdgt']		= $pdg_festive_combo_arr['365']['upfront'];
						}
						if(count($pdg_festive_combo_arr['3650']) > 0){
						
							$pdgcombo_idx ++;
							$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]	= array("tenure_days"=>"3650","tenure_name"=>"10 Years","fixed_price"=>1,'payment_mode_available'=>'NON ECS','offer_text'=>'',"tenure_months"=>120,"upfront"=>$pdg_festive_combo_arr['3650']['upfront'],"ecs"=>$pdg_festive_combo_arr['3650']['ecs']);
							if($this->keyword_info['19']){
								$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]['keyword'] 	= $this->keyword_info['19']['name'];
								$dataArr[$campaignid]['tenure_options'][$pdgcombo_idx]['bitval'] 	= $this->keyword_info['19']['bitval'];
							}
						}
					}
				}
			break;
			
			case 20 : // National Listing Festive Combo
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$dataArr[$campaignid]['actcamp'] 		= trim($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= 1;
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= trim($row_child_camp['image_header']);

					$nlfestive_combo_bdgt_arr 				= $this->omni_pricing[12]; // National Listing Festive Combo					
					$dataArr[$campaignid]['minbdgt']		= trim($nlfestive_combo_bdgt_arr['upfront']);
					
					if($this->keyword_info[20]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[20]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[20]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
					$dataArr[$campaignid]['ct_min_bdgt'] 	= trim($nlfestive_combo_bdgt_arr['upfront']);
				}
			break;
			
			case 28 : // Banner National Listing
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 			= trim($row_child_camp['campname']);
					$dataArr[$campaignid]['info'] 			= trim($row_child_camp['pricechart_display']);
					$bnr_campinfo_arr 						= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['actcamp'] 		= $bnr_campinfo_arr['actcampid'];
					$dataArr[$campaignid]['roteligib'] 		= $bnr_campinfo_arr['roteligib'];
					$dataArr[$campaignid]['blkcamp'] 		= trim($row_child_camp['block_campaign']);
					$dataArr[$campaignid]['dptcamp'] 		= trim($row_child_camp['dependent_campaign']);
					$dataArr[$campaignid]['mandcamp'] 		= trim($row_child_camp['mandatory_campaign']);
					$dataArr[$campaignid]['combocamp'] 		= trim($row_child_camp['combo_campaign']);
					$dataArr[$campaignid]['sgstcamp'] 		= trim($row_child_camp['suggested_campaign']);
					$dataArr[$campaignid]['actflag'] 		= intval($row_child_camp['active_flag']);
					$dataArr[$campaignid]['allowed'] 		= $this->restricted_catinfo['bnr_allowed'];
					$dataArr[$campaignid]['parent'] 		= 0;
					$dataArr[$campaignid]['back_big'] 		= trim($row_child_camp['back_big_lite']);
					$dataArr[$campaignid]['back_small']		= trim($row_child_camp['back_small_lite']);
					$dataArr[$campaignid]['posflag']		= trim($row_child_camp['position_flag']);
					$dataArr[$campaignid]['imgdisp']		= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']		= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']		= trim($row_child_camp['bal_adjst_allowed_campaign']);
					$dataArr[$campaignid]['isCombo']		= trim($row_child_camp['isCombo']);
					$dataArr[$campaignid]['rdt_url']		= trim($row_child_camp['redirection_url']);
					$dataArr[$campaignid]['imghdr']			= trim($row_child_camp['image_header']);
					
					if($this->restricted_catinfo['bnr_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					
					$bnr_nl_idx = 0;
					$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]	= array("tenure_days"=>"365","tenure_name"=>"Standard - 1 Year","payment_mode_available"=>"ECS/NON ECS","offer_text"=>"","tenure_months"=>12,"upfront" => 0, "ecs" => 0);
					if($this->keyword_info['28.1']){
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['keyword'] 	= $this->keyword_info['28.1']['name'];
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['bitval'] 	= $this->keyword_info['28.1']['bitval'];
					}
					
					$bnr_nl_idx  ++;
					$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]	= array("tenure_days"=>"1095","tenure_name"=>"Classic - 3 Years","payment_mode_available"=>"ECS/NON ECS","offer_text"=>"","tenure_months"=>36,"upfront" => 0, "ecs" => 0);
					if($this->keyword_info['28.3']){
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['keyword'] 	= $this->keyword_info['28.3']['name'];
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['bitval'] 	= $this->keyword_info['28.3']['bitval'];
					}
					
					$bnr_nl_idx  ++;
					$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]	= array("tenure_days"=>"3650","tenure_name"=>"Premium - Forever","payment_mode_available"=>"ECS/NON ECS","offer_text"=>"","tenure_months"=>120,"upfront" => 0, "ecs" => 0);
					if($this->keyword_info['28.5']){
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['keyword'] 	= $this->keyword_info['28.5']['name'];
						$dataArr[$campaignid]['tenure_options'][$bnr_nl_idx]['bitval'] 	= $this->keyword_info['28.5']['bitval'];
					}
					
					$dataArr[$campaignid]['multi_keyword']	= 1;
				}
			break;
			
			case 27 : // VIP
				if((($this->limited_access  === 1) && (in_array($campaignid, $this->empaccess_info))) || ($this->limited_access === 0)){
					$dataArr[$campaignid]['name'] 		= $row_child_camp['campname'];
					$dataArr[$campaignid]['info'] 		= $row_child_camp['pricechart_display'];
					$bnr_campinfo_arr 						= $this->getBannerActualCampid($row_child_camp['actual_campaign']);
					$dataArr[$campaignid]['actcamp'] 		= $bnr_campinfo_arr['actcampid'];
					$dataArr[$campaignid]['roteligib'] 		= $bnr_campinfo_arr['roteligib'];
					$dataArr[$campaignid]['blkcamp'] 	= $row_child_camp['block_campaign'];
					$dataArr[$campaignid]['dptcamp'] 	= $row_child_camp['dependent_campaign'];
					$dataArr[$campaignid]['mandcamp'] 	= $row_child_camp['mandatory_campaign'];
					$dataArr[$campaignid]['combocamp'] 	= $row_child_camp['combo_campaign'];
					$dataArr[$campaignid]['sgstcamp'] 	= $row_child_camp['suggested_campaign'];
					$dataArr[$campaignid]['actflag'] 	= $row_child_camp['active_flag'];
					$dataArr[$campaignid]['allowed'] 	= $this->restricted_catinfo['bnr_allowed'];
					$dataArr[$campaignid]['parent'] 	= 0;
					$dataArr[$campaignid]['isCombo'] 	= $row_child_camp['isCombo'];
					$dataArr[$campaignid]['rdt_url'] 	= $row_child_camp['redirection_url'];
					$dataArr[$campaignid]['imghdr']		= $row_child_camp['image_header'];
					$dataArr[$campaignid]['back_big'] 	= $row_child_camp['back_big_lite'];
					$dataArr[$campaignid]['back_small']	= $row_child_camp['back_small_lite'];
					$dataArr[$campaignid]['posflag']	= $row_child_camp['position_flag'];
					$dataArr[$campaignid]['imgdisp']	= $row_child_camp['img_display_campaigns'];
					$dataArr[$campaignid]['ordflag']	= trim($row_child_camp['order_flag']);
					$dataArr[$campaignid]['balcamp']	= trim($row_child_camp['bal_adjst_allowed_campaign']);
					
					if($this->restricted_catinfo['bnr_allowed'] === 0){
						$dataArr[$campaignid]['reason']	= $this->restricted_catinfo['bnr_reason'];
						$dataArr[$campaignid]['blktype']= 'category';
					}
					
					$vip_budget_arr 	= $this->omni_pricing[19]; // VIP
					if(count($vip_budget_arr)>0){
						$dataArr[$campaignid]['ecs'] = $vip_budget_arr['ecs'] + $vip_budget_arr['setup_ecs'] + $vip_budget_arr['plus_banner'] + $vip_budget_arr['plus_jdrr'];
						$dataArr[$campaignid]['ecs'] = 59880;
						$dataArr[$campaignid]['upfront'] = 70000;
						
					}
					if($this->keyword_info[27]){
						$dataArr[$campaignid]['keyword']	= $this->keyword_info[27]['name'];
						$dataArr[$campaignid]['bitval']		= $this->keyword_info[27]['bitval'];
						$dataArr[$campaignid]['multi_keyword']	= 0;
					}
					
					$dataArr[$campaignid]['tenure_months']	= 120;
					$dataArr[$campaignid]['tenure_days']	= 3650;
				}
			break;
		}
		return $dataArr;
	}
	
	private function bannerInventoryCheck(){
		
		if(count($this->bnr_eligible_catarr)>0){
			$bnr_catids_str = implode("','",$this->bnr_eligible_catarr);
			$bnr_booked_inv_catarr = array();
			$bnr_free_inv_catarr = array();
			$sqlBannerInventory = "SELECT catid,cat_sponbanner_inventory,cat_sponbanner_bidder FROM tbl_cat_banner_bid WHERE catid IN ('".$bnr_catids_str."') AND data_city = '".$this->data_city."'";
			$resBannerInventory = parent::execQuery($sqlBannerInventory, $this->conn_fin);
			if($resBannerInventory && parent::numRows($resBannerInventory)>0){
				while($row_bnr_inventory = parent::fetchData($resBannerInventory)){
					$catid						= trim($row_bnr_inventory['catid']);
					$cat_sponbanner_inventory 	= trim($row_bnr_inventory['cat_sponbanner_inventory']);
					$cat_sponbanner_bidder 		= trim($row_bnr_inventory['cat_sponbanner_bidder']);
					
					if((stripos($cat_sponbanner_bidder, $this->parentid) !== false) || ((1 - $cat_sponbanner_inventory) > 0)){
						// inventory available
					}else{
						$bnr_booked_inv_catarr[] = $catid;
					}
				}
			}
			foreach($this->bnr_eligible_catarr as $bnrcat){
				if(!in_array($bnrcat,$bnr_booked_inv_catarr)){
					$bnr_free_inv_catarr[] = $bnrcat;
				}
			}
			if(count($bnr_free_inv_catarr) <= 0 ){
				
				$this->bnr_inv_avail = 0;
			}
		}
	}
	
	private function festiveComboBudget($combo_type){ // 1 - Package , 2 - PDG
		$festive_budget_arr = array();
		$combo_cities_arr = array("mumbai","bangalore","chennai","coimbatore");
		if(in_array(strtolower($this->data_city), $combo_cities_arr)){
			// get budget
			
			$sqlComboBudget = "SELECT * FROM online_regis1.tbl_festive_combo_budget WHERE city = '".$this->data_city."' AND combo_type = '".$combo_type."'";
			$resComboBudget = parent::execQuery($sqlComboBudget, $this->conn_idc);
			if($resComboBudget && parent::numRows($resComboBudget)>0){
				while($row_combo_budget = parent::fetchData($resComboBudget)){
					$duration 	= intval($row_combo_budget['duration']);
					$upfront 	= intval($row_combo_budget['upfront']);
					$ecs 		= intval($row_combo_budget['ecs']);
					if($duration && $upfront && $ecs){
						$festive_budget_arr[$duration]['upfront'] 	= $upfront;
						$festive_budget_arr[$duration]['ecs'] 		= $ecs;
					}
				}
			}	
			
		}else{
			if($this->conn_city == 'remote'){
				// find zone
				if(in_array($this->remote_zone, $combo_cities_arr)){
					
					$cityval = $this->remote_zone." remote";
					$sqlComboBudget = "SELECT * FROM online_regis1.tbl_festive_combo_budget WHERE city = '".$cityval."' AND combo_type = '".$combo_type."'";
					$resComboBudget = parent::execQuery($sqlComboBudget, $this->conn_idc);
					if($resComboBudget && parent::numRows($resComboBudget)>0){
						while($row_combo_budget = parent::fetchData($resComboBudget)){
							$duration 	= intval($row_combo_budget['duration']);
							$upfront 	= intval($row_combo_budget['upfront']);
							$ecs 		= intval($row_combo_budget['ecs']);
							if($duration && $upfront && $ecs){
								$festive_budget_arr[$duration]['upfront'] 	= $upfront;
								$festive_budget_arr[$duration]['ecs'] 		= $ecs;
							}
						}
					}
					
				}
			}
		}
		return $festive_budget_arr;
	}
	private function getBannerActualCampid($actual_campid){
		$banner_info_arr = array();
		$banner_info_arr['actcampid'] = $actual_campid;
		$banner_info_arr['roteligib'] = 1;
		
		if($this->bnr_inv_avail === 0){
			
			$actual_campid_arr = explode(",",$actual_campid);
			$actual_campid_arr = array_merge(array_unique(array_filter($actual_campid_arr)));
			$comp_bnr_cid = 13; // if inventory availabe then 13 will be added in banner campaign
			if(in_array($comp_bnr_cid,$actual_campid_arr)){
				$key = array_search($comp_bnr_cid,$actual_campid_arr);
				unset($actual_campid_arr[$key]);
			}
			$banner_info_arr['actcampid'] = implode(",",$actual_campid_arr);
			$banner_info_arr['roteligib'] = 0;
		}
		return $banner_info_arr;
	}
	private function getEmpMinBudget(){
		$empbudget = 0;
		$sqlEmpMinBudget = "SELECT empcode,joining_date FROM online_regis.extra_prev_user WHERE empcode = '".$this->empcode."' AND  joining_date IS NOT NULL";
		$resEmpMinBudget = parent::execQuery($sqlEmpMinBudget, $this->conn_idc);
		if($resEmpMinBudget && parent::numRows($resEmpMinBudget)>0){
			$row_empmin_budget = parent::fetchData($resEmpMinBudget);
			$emp_joining_date = trim($row_empmin_budget['joining_date']);
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$emp_joining_date)){
				
				$current_date = time(); // or your date as well
				$joining_date = strtotime($emp_joining_date);
				$date_diff = $current_date - $joining_date;

				$datediff = round($date_diff / (60 * 60 * 24));
				
				$team_budget = trim($this->uploadrates_data['team_budget']);
				$team_budget_arr = json_decode($team_budget,true);
				if(count($team_budget_arr['pck_dis'])>0){
					$empbdgt_range_arr = array();
					foreach($team_budget_arr['pck_dis'] as $month => $budget){
						switch(trim($month)){
							case '2month' :
								$empbdgt_range_arr['60'] = $budget;
							break;
							case '4month' :
								$empbdgt_range_arr['120'] = $budget;
							break;
							case '6month' :
								$empbdgt_range_arr['180'] = $budget;
							break;
						}
						
						
					}
				}
				
				if(($datediff > 0) && ($datediff <=60)){
					$empbudget = $empbdgt_range_arr[60];
					
				}else if (($datediff > 60) && ($datediff <=120)){
					$empbudget = $empbdgt_range_arr[120];
				}else if(($datediff > 120) && ($datediff <=180)){
					$empbudget = $empbdgt_range_arr[180];
				}
				
			}
		}
		return $empbudget;
	}
	private function findOMNIContract(){
		
		$omni_contract = 0;
		$sqlOMNIContract = "SELECT parentid FROM tbl_companymaster_finance WHERE parentid ='".$this->parentid."' AND campaignid in (72,73)";
		$resOMNIContract = parent::execQuery($sqlOMNIContract, $this->conn_fin);
		if($resOMNIContract && parent::numRows($resOMNIContract)>0){
			$omni_contract = 1;
		}
		return $omni_contract;
		
	}
	private function getCampaignKeywordInfo(){
		$keyword_data_arr = array();
		$sqlKeywordInfo = "SELECT group_concat(key_words) as keyword_name ,group_concat(bitvalue) as bitvalue,geniolite_cid FROM tbl_payment_type_master where geniolite_cid > 0 GROUP BY geniolite_cid";
		$resKeywordInfo = parent::execQuery($sqlKeywordInfo, $this->conn_fin);
		if($resKeywordInfo && parent::numRows($resKeywordInfo)>0){
			while($row_keyword = parent::fetchData($resKeywordInfo)){
				$keyword_data_arr[$row_keyword['geniolite_cid']]['name'] 	= trim($row_keyword['keyword_name']);
				$keyword_data_arr[$row_keyword['geniolite_cid']]['bitval'] 	= trim($row_keyword['bitvalue']);
			}
		}
		return $keyword_data_arr;
	}
	
	private function getPkgSpecialBudget(){
		$spl_bdgt_arr = array();
		/*if(strtolower($this->data_city) == 'pune'){
			$spl_bdgt_arr['upfront'] 	= 12000;
			$spl_bdgt_arr['ecs'] 		= 24000;
			return $spl_bdgt_arr;
		}else if($this->remote_zone == 'pune'){
			$spl_bdgt_arr['upfront'] 	= 6000;
			$spl_bdgt_arr['ecs'] 		= 24000;
			return $spl_bdgt_arr;
		}*/
		$sqlPkgSpecialBudget = "SELECT upfront,ecs FROM online_regis1.tbl_package_budget WHERE city = '".$this->data_city."' AND active_flag = 1";
		$resPkgSpecialBudget = parent::execQuery($sqlPkgSpecialBudget, $this->conn_idc);
		if($resPkgSpecialBudget && parent::numRows($resPkgSpecialBudget)>0){
			$row_pkgbdgt = parent::fetchData($resPkgSpecialBudget);
			$pkg_upfront 	= intval($row_pkgbdgt['upfront']);
			$pkg_ecs 		= intval($row_pkgbdgt['ecs']);
			if($pkg_upfront && $pkg_ecs){
				$spl_bdgt_arr['upfront'] = $pkg_upfront;
				$spl_bdgt_arr['ecs'] = $pkg_ecs;
			}
		}
		return $spl_bdgt_arr;
	}
	
	
	private function financeInfo(){
		$finData = array();
		$phonesearch_flag 	 = 0;
		$packExpiredVal		 = 0;
		$packExpiredDatediff = 0;
		$pack_exp_45_days 	 = 0;
		$national_active	 = 0;
		$total_budget		 = 0;
		$data_found	 	 	 = 0; // expire or active contract - entry in finance table or not
		$existing_vfl		 = 0;
		$existing_nl_vfl	 = 0;
		$national_budget	 = 0;
		$expire_since_days 	 = 0;
		$sqlPhoneSearchInfo  = "SELECT campaignid,balance,expired,expired_on,manual_override,bid_perday,duration FROM tbl_companymaster_finance WHERE parentid = '".$this->parentid."' AND campaignid IN (1,2)";
		$resPhoneSearchInfo  = parent::execQuery($sqlPhoneSearchInfo, $this->conn_fin);
		if($resPhoneSearchInfo && parent::numRows($resPhoneSearchInfo)>0){
			while($row_fin_info = parent::fetchData($resPhoneSearchInfo)){
				
				$data_found		= 1;
				$campaignid			= intval($row_fin_info['campaignid']);
				$balance 			= trim($row_fin_info['balance']);
				$expired 			= intval($row_fin_info['expired']);
				$manual_override 	= intval($row_fin_info['manual_override']);
				$duration			= intval($row_fin_info['duration']);
				$bid_perday 		= trim($row_fin_info['bid_perday']);
				$budget_1yr			= $bid_perday * 365;
				
				
				if(($campaignid==1) && ($duration > 3600)){
					$existing_vfl = 1;
				}
				
				if(($balance >0 ) || (($balance <=0) && ($manual_override == 1) && ($expired == 0))){
					$phonesearch_flag = 1;
					
					$total_budget += $budget_1yr;
				}
				if(($expired == 1) && ($campaignid==1 || $campaignid==2)){
					$packExpiredVal		=	1;
					$packExpiredOnVal	= 	$row_fin_info['expired_on'];
					$todaydate  		= 	date('Y-m-d H:i:s');
					if($packExpiredDatediff == 0){
						$packExpiredDatediff = round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400); // 24 hours into sec 60*60*24
					}else{
						$packExpiredDatediff = min($packExpiredDatediff,round(abs(strtotime($todaydate)-strtotime($packExpiredOnVal))/86400));
					}
				}
			}
			if(($phonesearch_flag == 0) && ($packExpiredVal==1) && ($packExpiredDatediff>=45)){
				$pack_exp_45_days = 1;
				$expire_since_days = $packExpiredDatediff;
			}
			
		}
		$sqlNationalActiveInfo = "SELECT campaignid,balance,expired,manual_override,duration,bid_perday FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid = '".$this->parentid."' AND campaignid = 10 LIMIT 1";
		$resNationalActiveInfo = parent::execQuery($sqlNationalActiveInfo, $this->conn_idc);
		if($resNationalActiveInfo && parent::numRows($resNationalActiveInfo)>0){
			$row_nat_info = parent::fetchData($resNationalActiveInfo);
			$data_found		= 1;
			$balance 			= trim($row_nat_info['balance']);
			$expired 			= intval($row_nat_info['expired']);
			$manual_override 	= intval($row_nat_info['manual_override']);
			$duration			= intval($row_nat_info['duration']);
			$bid_perday 		= trim($row_nat_info['bid_perday']);
			
			
			if(($balance >0 ) || (($balance <=0) && ($manual_override == 1) && ($expired == 0))){
				$national_active = 1;
				$national_budget = $bid_perday * 365;
			}
			if($duration > 3600){
				$existing_nl_vfl = 1;
			}
		}
		$finData['phonesearch_flag']	= $phonesearch_flag;
		$finData['pack_exp_45_days'] 	= $pack_exp_45_days;
		$finData['expire_since_days'] 	= $expire_since_days;
		$finData['total_budget']		= $total_budget;
		$finData['existing_vfl']		= $existing_vfl;
		$finData['national_active'] 	= $national_active;
		$finData['existing_nl_vfl']		= $existing_nl_vfl;
		$finData['national_budget']		= $national_budget;
		$finData['data_found']			= $data_found;
		return $finData;
	}
    private function getOMNIPrice($omni_types_arr){
		$data_cities_arr     = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		if(in_array(strtolower($this->data_city),$data_cities_arr)){
			$omni_city = $this->data_city;
		}else{
			$omni_city = "remote";
		}
		$omni_types_str = implode(",",array_keys($omni_types_arr));
		$omni_price_arr = array();
		$sqlOMNIPrice = "SELECT omni_type,omni_monthly_fees as upfront_fees,omni_monthly_fees_ecs as ecs_fees,omni_fees_upfront as setup_fee_upfront,omni_fees_ecs as setup_fee_ecs,omni_fees_plus_banner,omni_fees_plus_jdrr FROM tbl_omni_pricing WHERE omni_type IN (".$omni_types_str.") AND city = '".$omni_city."'";
		$resOMNIPrice = parent::execQuery($sqlOMNIPrice, $this->conn_idc);
		if($resOMNIPrice && parent::numRows($resOMNIPrice)>0){
			while($row_omni_price = parent::fetchData($resOMNIPrice)){
				$omni_type										= trim($row_omni_price['omni_type']);
				$omni_price_arr[$omni_type]['upfront'] 			= intval($row_omni_price['upfront_fees']);
				$omni_price_arr[$omni_type]['ecs'] 				= intval($row_omni_price['ecs_fees']);
				$omni_price_arr[$omni_type]['setup_upfront']	= intval($row_omni_price['setup_fee_upfront']);
				$omni_price_arr[$omni_type]['setup_ecs'] 		= intval($row_omni_price['setup_fee_ecs']);
				
				$omni_price_arr[$omni_type]['plus_banner'] 		= intval($row_omni_price['omni_fees_plus_banner']);
				$omni_price_arr[$omni_type]['plus_jdrr'] 		= intval($row_omni_price['omni_fees_plus_jdrr']);
			}
		}
		return $omni_price_arr;
	}
	private function getSMSPrice($campaignid){
		$data_cities_arr     = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		if(in_array(strtolower($this->data_city),$data_cities_arr)){
			$sms_city = $this->data_city;
		}else{
			$sms_city = "remote";
		}
		$sqlSMSPrice = "SELECT price_upfront,price_ecs,city FROM online_regis1.omni_add_ons_pricing WHERE campaignid='".$campaignid."' AND camp_type='1' AND city = '".$sms_city."'";
		$resSMSPrice = parent::execQuery($sqlSMSPrice, $this->conn_idc);
		if($resSMSPrice && parent::numRows($resSMSPrice)>0){
			return $row_sms_price = parent::fetchData($resSMSPrice);
		}
	}
	private function getEmailPrice(){
		$email_res = 41.66;
		$sqlEmailPrice = "SELECT result FROM online_regis1.tbl_business_data WHERE action = 'email_price'"; // Its updating using cron
		$resEmailPrice = parent::execQuery($sqlEmailPrice, $this->conn_idc);
		if($resEmailPrice && parent::numRows($resEmailPrice)>0){
			$row_email_price = parent::fetchData($resEmailPrice);
			$email_price = trim($row_email_price['result']);
			if($email_price > 0){
				$email_res = $email_price;
			}
		}
		return $email_res;
	}
    private function getUploadRatesData(){
        $sqlUploadRates = "SELECT extra_package_details,ecssecyrinc,banner_single_unit,banner_ecs_per_rotation,omniextradetails,reseller_package_details,top_minbudget_fp,top_minbudget_package,top_minbudget_fp,team_budget,ssl_ecs,ssl_upfront,exppackval,exppackval_2,package_mini,package_mini_ecs,banner_rotation_details,maxdiscount_package as pkg_disc_per,discount_eligibility_package as pkg_disc_eligib, maxdiscount as pdg_disc_per, discount_eligibility as pdg_disc_eligib,crisil_price FROM tbl_business_uploadrates  WHERE city = '".$this->data_city."'";
        $resUploadRates = parent::execQuery($sqlUploadRates, $this->conn_local);
        if($resUploadRates && parent::numRows($resUploadRates)>0){
            return $row_upload_rates = parent::fetchData($resUploadRates);
        }
    }
    
    private function OthersCampaignData($campaignids_arr){
		
		$others_camp_arr = array();
		$campaignids_str = implode("','",array_keys($campaignids_arr));
        $sqlOthersCampBudget = "SELECT campaignid,campaign_name,price_upfront_actual,price_ecs_actual FROM tbl_finance_omni_flow_display_new_new  WHERE campaignid in ('".$campaignids_str."')";
        $resOthersCampBudget = parent::execQuery($sqlOthersCampBudget, $this->conn_idc);
        if($resOthersCampBudget && parent::numRows($resOthersCampBudget)>0){
            while($row_others_camp = parent::fetchData($resOthersCampBudget)){
				$campaignid 								= trim($row_others_camp['campaignid']);
				$others_camp_arr[$campaignid]['upfront'] 	= trim($row_others_camp['price_upfront_actual']);
				$others_camp_arr[$campaignid]['ecs'] 		= trim($row_others_camp['price_ecs_actual']);
			}
        }
        return $others_camp_arr;
    }
    
    private function getJDRRPricing(){
		$jdrr_price_arr 	 = array();
		$data_cities_arr     = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata');
		if(in_array(strtolower($this->data_city),$data_cities_arr)){
			$city = $this->data_city;
		}else{
			$city = "remote";
		}
		$sqlJDRRPricing = "SELECT city,upfront_payment,tenure FROM online_regis_mumbai.tbl_jdrr_pricing_gl WHERE city = '".$city."'";
		$resJDRRPricing = parent::execQuery($sqlJDRRPricing, $this->conn_idc);
		if($resJDRRPricing && parent::numRows($resJDRRPricing)>0){
			while($row_jdrr_price = parent::fetchData($resJDRRPricing)){
				$tenure			= 	trim($row_jdrr_price['tenure']);
				$jdrr_price_arr[$tenure]['upfront_payment'] = $row_jdrr_price['upfront_payment'];
			}

		}
		return $jdrr_price_arr;
	}
    
    private function validateContractCategories(){
		
		$respArr = array();
		$mongo_inputs = array();
		$mongo_inputs['parentid'] 	= $this->parentid;
		$mongo_inputs['data_city'] 	= $this->data_city;
		$mongo_inputs['module']		= 'ME';
		$mongo_inputs['table'] 		= "tbl_business_temp_data";
		$mongo_inputs['fields'] 	= "catIds";
		$row_temp_category = $this->mongo_obj->getData($mongo_inputs);
		$banner_block = 0;
		$package_block = 0;
		$flx_tagged_cat_cnt = 0;
		$flx_prem_ad_pkg = 0;
		$temp_catlin_arr 	= 	array();
		$banner_block_catnm = array();
		$banner_block_catid = array();
		
		$package_block_catnm = array();
		$package_block_catid = array();
		
		if(count($row_temp_category) > 0){
			$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catIds']);
			$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
			$temp_catlin_arr 	= 	$this->getValidCategories($temp_catlin_arr);
			$temp_catlin_cnt	=	count($temp_catlin_arr);
			if($temp_catlin_cnt > 0){
				$cat_name_arr 	= array();
				$catids_str = implode("','",$temp_catlin_arr);
				//$sqlCatInfo	= "SELECT catid,paid_clients,nonpaid_clients,misc_cat_flag,category_name,miscellaneous_flag FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
				//$resCatInfo 	= parent::execQuery($sqlCatInfo, $this->conn_local);
				$cat_params = array();
				$cat_params['page'] ='campaign_budget_class';
				$cat_params['data_city'] 	= $this->data_city;			
				$cat_params['return']		= 'catid,paid_clients,nonpaid_clients,misc_cat_flag,category_name,miscellaneous_flag,category_type';

				$where_arr  	=	array();
				$where_arr['catid']		= implode(",",$temp_catlin_arr);
				$cat_params['where']	= json_encode($where_arr);

				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
				$cat_res_arr = array();
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}

				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
					foreach($cat_res_arr['results'] as $key => $row_catinfo){
						$miscellaneous_flag = trim($row_catinfo['miscellaneous_flag']);
						$category_type	= trim($row_catinfo['category_type']);
						
						if(((int)$category_type & 131072) == 131072){
							$package_block = 1;
							$package_block_catnm[] = trim($row_catinfo['category_name']);
							$package_block_catid[] = trim($row_catinfo['catid']);
						}
						
						if(((int)$miscellaneous_flag & 1) == 1){
							$flx_tagged_cat_cnt ++;
						}
						
						if($row_catinfo['paid_clients'] == '0' && $row_catinfo['nonpaid_clients'] == '0'){ // ONLY FOR BANNER
							$banner_block = 1;
							if(!in_array(trim($row_catinfo['category_name']),$banner_block_catnm)){
								$banner_block_catnm[] = trim($row_catinfo['category_name']);
								$banner_block_catid[] = trim($row_catinfo['catid']);
							}
						}
						if(((int)$row_catinfo['misc_cat_flag'] & 64) == 64){ // JDRR + BANNER
							$banner_block = 1;
							if(!in_array(trim($row_catinfo['category_name']),$banner_block_catnm)){
								$banner_block_catnm[] = trim($row_catinfo['category_name']);
								$banner_block_catid[] = trim($row_catinfo['catid']);
							}
						}
					}
					if($temp_catlin_cnt == $flx_tagged_cat_cnt){
						$flx_prem_ad_pkg = 1;
					}
				}
			}
		}
		$this->bnr_eligible_catarr = array_diff($temp_catlin_arr,$banner_block_catid);
		if(($banner_block == 1) && (count($temp_catlin_arr) == count($banner_block_catid))){
			$respArr['bnr_allowed']		= 0;
			$respArr['bnr_reason'] 		= implode(",",array_unique($banner_block_catnm));
		}else{
			$respArr['bnr_allowed']		= 1;
		}
		
		if($package_block == 1){
			$respArr['pkg_allowed']		= 0;
			$respArr['pkg_reason'] 		= implode(",",array_unique($package_block_catnm));
		}else{
			$respArr['pkg_allowed']		= 1;
		}
		
		if($package_block == 1 || $banner_block == 1){
			$respArr['pkg_bnr_allowed']		= 0;
			$respArr['pkg_bnr_reason'] 		= implode(",",array_unique(array_merge($package_block_catnm,$banner_block_catnm)));
		}else{
			$respArr['pkg_bnr_allowed']		= 1;
		}
		$respArr['flx_prem_ad_pkg']	= $flx_prem_ad_pkg; // Flexi Premium Ad Package Identifier
		return $respArr;
	}
	private function allocationInfo(){
		$emp_allocid = '';
		$sqlAllocationInfo = "SELECT contractCode,compname,parentcode FROM tblContractAllocation WHERE allocationType IN('25','99') AND contractcode= '".$this->parentid."' ORDER BY allocationtime DESC LIMIT 1";
		$resAllocationInfo = parent::execQuery($sqlAllocationInfo, $this->conn_idc);
		if($resAllocationInfo && parent::numRows($resAllocationInfo)>0){
			$row_alloc = parent::fetchData($resAllocationInfo);
			$parentcode = trim($row_alloc['parentcode']);
			if($parentcode > 0){
				
				$empinfo_str =  $this->employeeInfo($parentcode);
				if($empinfo_str){
					$empinfo_arr = json_decode($empinfo_str,true);
					if(($empinfo_arr['errorcode'] == 0) && (count($empinfo_arr['data']) > 0)){
						$emp_allocid = strtoupper($empinfo_arr['data'][0]['team_type']);
						if($emp_allocid =='HD'){
							$date_of_joining = $empinfo_arr['data'][0]['date_of_joining'];
							if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_of_joining)){
								$cur_date 	= time();
								$join_date 	= strtotime($date_of_joining);
								$dt_diff 	= $cur_date - $join_date;
								$dt_diff = round($dt_diff / (60 * 60 * 24));
								if($dt_diff <=180){
									$this->vfl_specail_team = 1;
								}
							}
						}else if($emp_allocid =='S'){ // C Team confirmed by ITU
							$this->vfl_specail_team = 1;
						}
					}
				}
			}
		}
		return $emp_allocid;	
	}
	private function employeeInfo($empcode){
		$retValemp					=	'';
		if(intval($empcode)>0){
			$paramsArr					=	array();
			$paramsArr['empcode']		=	$empcode;
			$paramsArr['textSearch']	=	4;
			$paramsArr['reseller_flag']	=	1;
			
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
	private function empAcccessInfo(){
		$allowed_campids_arr = array();
		$sqlEmpAccessInfo = "SELECT empcode,allowed_campids FROM online_regis1.tbl_users_campaign_mapping WHERE empcode = '".$this->empcode."' AND is_limited_access = 1";
		$resEmpAccessInfo = parent::execQuery($sqlEmpAccessInfo, $this->conn_idc);
		if($resEmpAccessInfo && parent::numRows($resEmpAccessInfo)>0){
			$row_empinfo = parent::fetchData($resEmpAccessInfo);
			$allowed_campids = trim($row_empinfo['allowed_campids']);
			$allowed_campids_arr = explode(",",$allowed_campids);
			$allowed_campids_arr = array_merge(array_unique(array_filter($allowed_campids_arr)));
			$this->limited_access = 1;
		}
		return $allowed_campids_arr;
	}
	private function getValidCategories($total_catlin_arr){
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0)){
			foreach($total_catlin_arr as $catid){
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0){
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_merge(array_unique(array_filter($final_catids_arr)));
		}
		return $final_catids_arr;	
	}
	function zoneWiseCityList($main_zone)
	{
		$zone_cities_arr = array();
		$sqlZoneCityList = "SELECT GROUP_CONCAT(Cities SEPARATOR '|') as zonecity FROM tbl_zone_cities WHERE main_zone = '".$main_zone."' AND Cities NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur') LIMIT 1";
		$resZoneCityList = parent::execQuery($sqlZoneCityList, $this->conn_local);
		if($resZoneCityList && parent::numRows($resZoneCityList)>0)
		{
			$row_zone_citylist = parent::fetchData($resZoneCityList);
			$zonecity 			= trim($row_zone_citylist['zonecity']);
			$zone_cities_arr = explode("|",$zonecity);
			$zone_cities_arr = array_map('strtolower',$zone_cities_arr);
		}
		return $zone_cities_arr;
	}
	function getRemoteCityZone(){
		$remote_city_zone = "";
		$sqlFindRemoteCityZone = "SELECT main_zone,Cities FROM tbl_zone_cities WHERE Cities = '".$this->data_city."'  and Cities NOT IN ('mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahmedabad','chandigarh','coimbatore','jaipur')";
		$resFindRemoteCityZone = parent::execQuery($sqlFindRemoteCityZone, $this->conn_local);
		if($resFindRemoteCityZone && parent::numRows($resFindRemoteCityZone)>0)
		{
			$row_remotezone = parent::fetchData($resFindRemoteCityZone);
			$main_zone 		= trim($row_remotezone['main_zone']);
			$main_zone		= strtolower($main_zone);
			if(!empty($main_zone)){
				$remote_city_zone = $main_zone;
			}
		}
		return $remote_city_zone;
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
