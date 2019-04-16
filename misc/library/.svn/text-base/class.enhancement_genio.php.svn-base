<?php
// changes needed
if(!defined('APP_PATH'))
{
    die("APP PATH not defined....");
}
require_once(APP_PATH."00_Payment_Rework/company_finance_class.php");
class Enhancement 
{
    private $parentid, $paid_flag, $businessEligibilityEnhancement, $enhancemnet_flag, $powerlisting_flag,$min_enhancement_budget,$total_budget,$contract_perday,$smart_flag,$supremeSmartPackageAmount,$smart_regis,$version, $enhancement_budget , $old_servicename, $enhancement_entry_date, $query_execution, $sphinx_id,$without_packagebudget,$servicename_ratio;

    private $city, $conn_iro, $conn_finance, $log_path, $campaigns_budget_values,$budgetArr;
    public $changes_done, $direct_update,  $process_flag, $financeObj,$min_enhancement_perday,$package_flag,$newbudgetArr;

    function __construct($parentid, $dbarr, $city,$sphinx_id)
    {
        if(trim($parentid) == '')
		{
			 die("parentid found as blank");
		}
        $this->city             = $this->getDataCity($city);
        $this->main_city        = $city;
        $this->log_path			= APP_PATH . 'logs/';
        $this->conn_iro         = new DB($dbarr['DB_IRO']);
        $this->conn_finance     = new DB($dbarr['FINANCE']);
        $this->conn_decs        = new DB($dbarr['DB_DECS']);
        $this->conn_ecsbill     = new DB($dbarr['ECS_BILL']);
		$this->conn_tme_temp	= new DB($dbarr['DB_TME']);
        $this->direct_update    = false; 
        
        $this->financeObj 		= new company_master_finance($dbarr,$parentid,$sphinx_id) ;
        $this->initialized($parentid,$sphinx_id);
        
    }
    
    function initialized($parentid,$sphinx_id)
    {
        $this->parentid                 =   $parentid;
        $this->sphinx_id				= 	$sphinx_id;
        $this->changes_done             =   false;
        $this->enhancemnet_flag         =   '';
        $this->campaigns_budget_values  = array();
        $this->powerlisting_flag        =   '';
        $this->enhancement              = '';
        $this->package_flag             = 1;
        $this->smart_flag               ='';
        $this->supremeSmartPackageAmount='';
        $this->smart_regis              ='';
        $this->paid_flag                = -1;
        $this->businessEligibilityEnhancement      = true;
        $this->version                  = '';
        $this->enhancement_budget       = '';
        $this->old_servicename          = '';
        $this->enhancement_entry_date   = ''; 
        $this->query_execution          = true;
        $this->budgetArr				= $this->financeObj->getFinanceTempData();
        $this->without_packagebudget    = 0;
        $this->servicename_ratio        = '';
        $this->temp_data_present         = 0;
        $this->get_temp_flag            = array();
        //$this->iscalculated_flag        = -1;
    }

    function enhancement_validation()
    {
        $temp_eligibleFlg = $this->eligibility(1);
        if(intval(trim($temp_eligibleFlg))==0)
        {
            $this->get_temp_enhancement_flag();
        }
        return $this->temp_data_present;
    }

    function get_temp_enhancement_flag()
    {
        $qry_get_temp_ehn = "select * from tbl_business_temp_enhancements where contractid='".$this->parentid."'";
        if(strtoupper(trim($this->module))=='ME')
        {
            $res_get_temp_ehn = $this->conn_idc->query_sql($qry_get_temp_ehn); 
        }
        elseif(strtoupper(trim($this->module))=='TME')
        {
            $res_get_temp_ehn = $this->conn_tme_temp->query_sql($qry_get_temp_ehn); 
        }
        else
        {
            $res_get_temp_ehn = $this->conn_decs->query_sql($qry_get_temp_ehn); 
        }
        if(!$res_get_temp_ehn)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_get_temp_ehn; 
            exit;
        }
        else
        {
            if(mysql_num_rows($res_get_temp_ehn)>0)
            {
                $this->get_temp_flag = array();
                $row_get_temp_ehn = mysql_fetch_assoc($res_get_temp_ehn);
                if(intval($row_get_temp_ehn['video_facility'])==1)
                {
                    $this->temp_data_present = 1;
                }
                elseif($row_get_temp_ehn['logo_facility']==1)
                {
                    $this->temp_data_present = 1;
                }
                elseif($row_get_temp_ehn['catalog_facility']==1)
                {
                    $this->temp_data_present = 1;
                }
                $this->get_temp_flag=$row_get_temp_ehn;
            }
        }
    }

    function get_minimum_enhancement_budget()
    {
        $min_enhancement_budget_query = "SELECT MIN(annualcost) AS annualcost FROM tbl_premium_listing_Justdialg WHERE logo!=0 AND catalogue!=0 AND video!=0 AND city='".$this->city."' AND JD_flag=1";
		$min_enhancement_budget_result = $this->conn_decs->query_sql($min_enhancement_budget_query); 
        if(!$min_enhancement_budget_result)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $min_enhancement_budget_query; 
            exit;
        }
        if(mysql_num_rows($min_enhancement_budget_result)>0)
        {
            $min_enhancement_budget_row = mysql_fetch_assoc($min_enhancement_budget_result);
            $this->min_enhancement_budget = $min_enhancement_budget_row['annualcost'];
            $this->min_enhancement_perday = $min_enhancement_budget_row['annualcost']/365;
            mysql_free_result($min_enhancement_budget_result);
            unset($min_enhancement_budget_row);
			$upArr['eligible_budget_enhancement']=$this->min_enhancement_budget;
			$this->financeObj->metaTempUpdate($upArr);
        }
    }
    
    function get_minimum_enhancement_budget_national(){
            $this->min_enhancement_budget = 30000;
            $this->min_enhancement_perday = 30000/365; 
			$upArr['eligible_budget_enhancement']=$this->min_enhancement_budget;
			$this->financeObj->metaTempUpdate($upArr);
    }
    
    function getContractBudget()
    {
		$totalBudget=0;
		$budgetperday=0.0;
		$total_perday = 0;
        
        if($this->without_packagebudget>0)
        {
            for($i=1;$i<18;$i++)
            {
                /*if($i!=8){
                    $totalBudget+= $this->budgetArr[$i]['budget'];
                    $duration 	 = ($this->budgetArr[$i]['duration']>0)?$this->budget[$i]['duration']:'365';
                }*/
                if(in_array($i,array(1,2))){
                    $totalBudget+= $this->budgetArr[$i]['budget'];
                    if(intval($this->budgetArr[$i]['budget'])>0)
                    {
                        $duration 	 = ($this->budgetArr[$i]['duration']>0)?intval($this->budgetArr[$i]['duration']):'365';
                        $total_perday += $this->budgetArr[$i]['budget']/$duration;
                    }
                }
                elseif(in_array($i,array(10)) && intval($this->budgetArr[$i]['budget'])>0)
                {
                    $totalBudget+= $this->budgetArr[$i]['budget'];
                    //echo "hre".$this->budgetArr[$i]['duration'].;
                    $total_perday ='';
                    $duration ='';
                    $duration 	 = ($this->budgetArr[$i]['duration']>0)?intval($this->budgetArr[$i]['duration']):'365';
                    $total_perday += $this->budgetArr[$i]['budget']/$duration;
                    break;
                }
            }
        }
        else
        {
            if($this->budgetArr[1]['smartlisting_flag'] ==1)
            {
                $samrt_package_budget = $this->budgetArr[16]['budget'];
            }
            else
            {
                $package_budget = $this->budgetArr[1]['budget'];
                $totalBudget= $this->budgetArr[1]['budget'];
                $duration = $this->budgetArr[1]['duration'];
                $table_perday = $this->budgetArr[1]['daily_threshold'];
                if($duration>0){
                    $total_perday = $totalBudget/$duration;
                    if($table_perday>0){
                        if(abs(($table_perday-$total_perday))<1){
                            $total_perday=$table_perday;
                        }
                    }
                }
            }
        }
		if($totalBudget>0 && $duration>0)
		{
			//$budgetperday = $totalBudget/$duration;
			$budgetperday = $total_perday;
		}
	
		return $budgetperday;
	}

    function check_pure_package_contract()
    {
        $pure_package = -1;
        $this->without_packagebudget = 0;
        for($j=2;$j<=20;$j++)
        {
            if($j!=16)
            {
                $this->without_packagebudget += $this->budgetArr[$j]['budget'];
            }
        }
        if(($this->budgetArr[10]['budget']>0 && $this->budgetArr[10]['budget']<30000) && $this->budgetArr[1]['budget']>0){
            $this->without_packagebudget=0;
            $pure_package=1;
            return $pure_package;
        }
        if($this->without_packagebudget>0)
        {
            $pure_package =0;
        }
        else
        {
            $pure_package = 1;
        }
        return $pure_package;
    }

	function eligibility($temp='')
	{
		//$eligibleFree	= -1;
		$minEnBudget 	= 0;
		$contractPerDay	= 0;
        if($this->budgetArr[10]['budget']>=30000 || ($this->budgetArr[1]['budget']==0 && $this->budgetArr[10]['budget']>0)){
            $this->get_minimum_enhancement_budget_national();
        }else{
            $this->get_minimum_enhancement_budget();
        }
		$minEnBudget 	= $this->min_enhancement_perday; 
        if(intval($temp)==1)
        {
            $pure_packge = $this->check_pure_package_contract();
            $contractPerDay	= $this->getContractBudget();
        }
        else
        {
		    $contractPerDay	= $this->getContractBudget();
        }
		
		if($contractPerDay > 0.0)
		{
			if($contractPerDay >= $minEnBudget)
			{
				$eligibleFree = 1;
			}
			else
			{
				$eligibleFree = 0;
			}
		}
        else
        {
            $eligibleFree = 0;
        }
		return $eligibleFree;
	}

	function setValuePackage()
	{
		$budget = $this->financeObj->getFinanceTempData();
		$nonWebSearch = false;
		if($budget['17']['budget']>0){
			$nonWebSearch = true;
		}
		/*if($budget['1']['budget'] > 0)
		{
            $pure_packge = $this->check_pure_package_contract(); 
            if(intval($pure_packge)==1)
            {
                if($budget['8']['budget'] <= 0)
                {
                    $this->get_package_vlc(); 
                }
            }
            else
            {
                $eligibility = $this->eligibility(); 
                if($eligibility == 1)
                {*/
                if(!$nonWebSearch){ 
					$tot_budget = 0;
                    $serviceName = '~video_shooting~logo~catalog~';
                    $vlcBudget	 = $this->getUploadRates($this->city);
                    $duration = 0;
                    $totBud = ($vlcBudget['video_shooting_rate']+$vlcBudget['catalog_rate']+$vlcBudget['logo_rate']);
                    $vidPercent = sprintf ("%.2f",($vlcBudget['video_shooting_rate']/$totBud)*100);
                    $catPercent = sprintf ("%.2f",($vlcBudget['catalog_rate']/$totBud)*100);
                    $logPercent = sprintf ("%.2f",($vlcBudget['logo_rate']/$totBud)*100);
					foreach($budget as $campkey => $campval){
						$tot_budget += $budget[$campkey]['budget'];
						if(intval($duration)<=0 )
						{
							if($budget[$campkey]['recalculate_flag']==1){
								$duration = $budget[$campkey]['duration'];
							}
						}
					}
					if($duration<=0){
						$duration = 365;
					}
					$updateMeta['primary_campaign_budget'] = $tot_budget;
					$updateMeta['primary_campaign_duration'] = $duration;
                    $updateMeta['servicename_ratio'] = $vidPercent."-".$catPercent."-".$logPercent;
                    $updateMeta['servicename'] 		 = $serviceName;
                    $this->financeObj -> metaTempUpdate($updateMeta);
				}
                /*}
            }
		}*/
		
	}

    function get_package_vlc()
    {
        $totBud =0; //echo "<pre>";print_r($this->budgetArr);exit;
        if($this->budgetArr[1]['budget']>0 && $this->budgetArr[1]['smartlisting_flag']==1 && $this->budgetArr[16]['smartlisting_flag']==1)
        {
            /*it samrt contract*/
           $sql_get_smart_vlc="select  video,logo,catalogue  from   tbl_smart_listing_Justdialg  where   registrationfees ='".$this->budgetArr[16]['budget']."'  and annualcost='".$this->budgetArr[1]['budget']."'";
            $res_sql_get_smart_vlc = $this->conn_decs->query_sql($sql_get_smart_vlc);
            echo "<br> line 284-->".mysql_num_rows($res_sql_get_smart_vlc);
            if(!$res_sql_get_smart_vlc)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_get_smart_vlc; 
                exit;
            }
            else
            {
                if(mysql_num_rows($res_sql_get_smart_vlc)>0)
                {
                    $row_sql_get_smart_vlc = mysql_fetch_assoc($res_sql_get_smart_vlc);
                    $video_facility = '';
                    $logo_facility = '';
                    $catalog_facility = '';

                    if($row_sql_get_smart_vlc['video'])			{	$video_facility     = 'video_shooting';	}
                    if($row_sql_get_smart_vlc['logo'])			{	$logo_facility      = 'logo';		    }
                    if($row_sql_get_smart_vlc['catalogue'])	    {	$catalog_facility   = 'catalog';		}
                    
                    $serviceName = '~'.$video_facility.'~'.$logo_facility.'~'.$catalog_facility.'~';
                    if(strtolower($this->city)=='other_cities')
                    {
                        $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));
                    }
                    else
                    {
                        $vlcBudget	 = $this->getUploadRates($this->city);
                    }
                    if($row_sql_get_smart_vlc['video'])
                    {
                        $totBud += $vlcBudget['video_shooting_rate'];
                    }
                    if($row_sql_get_smart_vlc['logo'])
                    {
                        $totBud += $vlcBudget['logo_rate'];
                    }
                    if($row_sql_get_smart_vlc['catalogue'])
                    {
                        $totBud += $vlcBudget['catalog_rate'];
                    }
                    if(intval($totBud)>0)
                    {
                        $vidPercent =($vlcBudget['video_shooting_rate']*100)/$totBud;
                        $catPercent = ($vlcBudget['catalog_rate']*100)/$totBud;
                        $logPercent = ($vlcBudget['logo_rate']*100)/$totBud;
                    }
                    $updateMeta['servicename_ratio'] = (($row_sql_get_smart_vlc['video'])?$vidPercent:"0")."-".(($row_sql_get_smart_vlc['logo'])?$logPercent:"0")."-".(($row_sql_get_smart_vlc['catalogue'])?$catPercent:"0");
                    $updateMeta['servicename'] 		 = $serviceName;
                    $updateMeta['primary_campaign_budget'] = $this->budgetArr[1]['budget'];
                    $updateMeta['primary_campaign_duration'] = $this->budgetArr[1]['duration'];
                    $this->financeObj -> metaTempUpdate($updateMeta);
                }
                else
                {
                    $sql_get_smart_minregfee="select  video,logo,catalogue,min(registrationfees) as minregisfees from d_jds.tbl_smart_listing_Justdialg where  video='1' and logo='1' and catalogue='1' having minregisfees>0";
                    $res_sql_get_smart_minregfee = $this->conn_decs->query_sql($sql_get_smart_minregfee);
                    if(!$res_sql_get_smart_minregfee)
                    {
                        echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_get_smart_minregfee; 
                        exit;
                    }
                    else
                    {
                        if(mysql_num_rows($res_sql_get_smart_minregfee)>0)
                        {
                            $row_sql_get_smart_minregfee = mysql_fetch_assoc($res_sql_get_smart_minregfee);
                            $min_registration_fees = $row_sql_get_smart_minregfee['minregisfees'];
                            if(($min_registration_fees<$this->budgetArr[16]['budget']||$min_registration_fees==$this->budgetArr[16]['budget']) && (strtolower($this->city)!='other_cities'))
					        {
                                $serviceName = '~video_shooting~logo~catalog~';
                                if(strtolower($this->city)=='other_cities')
                                {
                                    $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));
                                }
                                else
                                {
                                    $vlcBudget	 = $this->getUploadRates($this->city);
                                }
                                if($row_sql_get_smart_minregfee['video'])
                                {
                                    $totBud += $vlcBudget['video_shooting_rate'];
                                }
                                if($row_sql_get_smart_minregfee['logo'])
                                {
                                    $totBud += $vlcBudget['logo_rate'];
                                }
                                if($row_sql_get_smart_minregfee['catalogue'])
                                {
                                    $totBud += $vlcBudget['catalog_rate'];
                                }
                                if(intval($totBud)>0)
                                {
                                    $vidPercent =sprintf ("%.2f",($vlcBudget['video_shooting_rate']*100)/$totBud);
                                    $catPercent = sprintf ("%.2f",($vlcBudget['catalog_rate']*100)/$totBud);
                                    $logPercent = sprintf ("%.2f",($vlcBudget['logo_rate']*100)/$totBud);
                                }
                                $updateMeta['servicename_ratio'] = (($row_sql_get_smart_vlc['video'])?$vidPercent:"0")."-".(($row_sql_get_smart_vlc['logo'])?$logPercent:"0")."-".(($row_sql_get_smart_vlc['catalogue'])?$catPercent:"0");
                                $updateMeta['servicename'] 		 = $serviceName;
                                $updateMeta['primary_campaign_budget'] = $this->budgetArr[1]['budget'];
                                $updateMeta['primary_campaign_duration'] = $this->budgetArr[1]['duration'];
                                $this->financeObj -> metaTempUpdate($updateMeta);
                            }
                            else
                            {
                                $smart_videoflag = $this->get_smart_videoflag($this->budgetArr[16]['budget']);
                                /*$this->get_minimum_enhancement_budget();
                                $minEnBudget 	= $this->min_enhancement_perday;*/
                                if( $smart_videoflag && intval($this->budgetArr[1]['duration'])>0)
                                {
                                    $serviceName = '~video_shooting~logo~catalog~'; 
                                    $totBud = ($vlcBudget['video_shooting_rate']+$vlcBudget['catalog_rate']+$vlcBudget['logo_rate']);
                                }
                                else
                                {
                                    $serviceName = '~~logo~catalog~';
                                    $totBud = ($vlcBudget['catalog_rate']+$vlcBudget['logo_rate']);
                                }
                                if(strtolower($this->city)=='other_cities')
                                {
                                    $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));
                                }
                                else
                                {
                                    $vlcBudget	 = $this->getUploadRates($this->city);
                                }
                                
                                $vidPercent = sprintf ("%.2f",($vlcBudget['video_shooting_rate']*100)/$totBud);
                                $catPercent = sprintf ("%.2f",($vlcBudget['catalog_rate']*100)/$totBud);
                                $logPercent = sprintf ("%.2f",($vlcBudget['logo_rate']*100)/$totBud);
                                $updateMeta['servicename_ratio'] = "0"."-".$catPercent."-".$logPercent;
                                $updateMeta['servicename'] 		 = $serviceName;
                                $updateMeta['primary_campaign_budget'] = $this->budgetArr[1]['budget'];
                                $updateMeta['primary_campaign_duration'] = $this->budgetArr[1]['duration'];
                                $this->financeObj -> metaTempUpdate($updateMeta);
                            }
                        }
                        else
                        {
                            $serviceName = '~~logo~catalog~';
                            if(strtolower($this->city)=='other_cities')
                            {
                                $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));
                            }
                            else
                            {
                                $vlcBudget	 = $this->getUploadRates($this->city);
                            }
                            $totBud = ($vlcBudget['catalog_rate']+$vlcBudget['logo_rate']);
                            $vidPercent = ($vlcBudget['video_shooting_rate']*100)/$totBud;
                            $catPercent = ($vlcBudget['catalog_rate']*100)/$totBud;
                            $logPercent = ($vlcBudget['logo_rate']*100)/$totBud;
                            $updateMeta['servicename_ratio'] = "0"."-".$catPercent."-".$logPercent;
                            $updateMeta['servicename'] 		 = $serviceName;
                            $updateMeta['primary_campaign_budget'] = $this->budgetArr[1]['budget'];
                            $updateMeta['primary_campaign_duration'] = $this->budgetArr[1]['duration'];
                            $this->financeObj -> metaTempUpdate($updateMeta);
                        }
                    }
                }
            }
        }
        else
        {
            $contract_per_day = $this->budgetArr[1]['budget']/$this->budgetArr[1]['duration']; 
            $sql_get_supreme_vlc = "SELECT annualcost,video,logo,catalogue FROM tbl_premium_listing_Justdialg WHERE city = '".$this->city."'	AND JD_flag=1 having (annualcost/365) <= '".$contract_per_day."' ORDER BY annualcost DESC  LIMIT 1";
            $res_sql_get_supreme_vlc = $this->conn_decs->query_sql($sql_get_supreme_vlc);
            if(!$res_sql_get_supreme_vlc)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_get_supreme_vlc; 
                exit;
            }
            else
            {
                if(mysql_num_rows($res_sql_get_supreme_vlc)>0)
                {
                    $row_sql_get_supreme_vlc = mysql_fetch_assoc($res_sql_get_supreme_vlc);
                    $video_facility = '';
                    $logo_facility = '';
                    $catalog_facility = '';

                    if($row_sql_get_supreme_vlc['video'])			{	$video_facility     = 'video_shooting';	}
                    if($row_sql_get_supreme_vlc['logo'])			{	$logo_facility      = 'logo';		    }
                    if($row_sql_get_supreme_vlc['catalogue'])	    {	$catalog_facility   = 'catalog';		}
                    
                    $serviceName = '~'.$video_facility.'~'.$logo_facility.'~'.$catalog_facility.'~';
                    $vlcBudget	 = $this->getUploadRates($this->city);
                    if($row_sql_get_supreme_vlc['video'])
                    {
                        $totBud += $vlcBudget['video_shooting_rate'];
                    }
                    if($row_sql_get_supreme_vlc['logo'])
                    {
                        $totBud += $vlcBudget['logo_rate'];
                    }
                    if($row_sql_get_supreme_vlc['catalogue'])
                    {
                        $totBud += $vlcBudget['catalog_rate'];
                    }
                    if(intval($totBud)>0)
                    {
                        $vidPercent =($vlcBudget['video_shooting_rate']*100)/$totBud;
                        $catPercent = ($vlcBudget['catalog_rate']*100)/$totBud;
                        $logPercent = ($vlcBudget['logo_rate']*100)/$totBud;
                    }
                    $updateMeta['servicename_ratio'] = (($row_sql_get_smart_vlc['video'])?$vidPercent:"0")."-".(($row_sql_get_smart_vlc['logo'])?$logPercent:"0")."-".(($row_sql_get_smart_vlc['catalogue'])?$catPercent:"0");
                    $updateMeta['servicename'] 		 = $serviceName;
                    $updateMeta['primary_campaign_budget'] = $this->budgetArr[1]['budget'];
                    $updateMeta['primary_campaign_duration'] = $this->budgetArr[1]['duration'];
                    $this->financeObj -> metaTempUpdate($updateMeta);
                }
            }
        }
    }
	
	function setValues($postArr)
	{
		$upArr				= array();
		$upArr['budget']	= 0;
		$serviceName		= null;
		$videoPercent	= 0;
		$logoPercent 	= 0;
		$catalogPercent	= 0;

		$eligibleFlg = $this->eligibility('1');

		if($eligibleFlg == 0)
		{
             $video_val=$logo_val=$cat_val=0;
             if($this->budgetArr[1]['budget']>0){
                $sqlvlc="SELECT * FROM tbl_premium_listing_Justdialg WHERE  city='".$this->city."'  AND annualcost<='".$this->budgetArr[1]['budget']."' AND JD_flag=1 limit 1";
                $resvlc =$this->conn_decs->query_sql($sqlvlc);
                if(mysql_num_rows($resvlc)>0){
                    $rowvlc = mysql_fetch_assoc($resvlc);
                    $video_val= $rowvlc['video'];
                    $logo_val= $rowvlc['logo'];
                    $cat_val= $rowvlc['catalogue'];
                }
            }
			if($postArr['video_up']=='on')
			{
                if($video_val==0){
                    $upArr['budget']+=$postArr['videoRate'];
                }
				$serviceName.='~video_shooting~';
			}
			if($postArr['catalog_up']=='on')
			{
                if($cat_val==0){
                    $upArr['budget']+=$postArr['catalogRate'];
                }
				$serviceName.='~catalog~';
			}
			if($postArr['logo_up']=='on')
			{
                if($logo_val==0){
                    $upArr['budget']+=$postArr['logoRate'];
                }
				$serviceName.='~logo~';
			}
			$ratioBudget=$upArr['budget'];
			if($ratioBudget>0)
			{
                if($postArr['video_up']=='on')
			    {
				    $videoPercent	= ($postArr['videoRate']*100)/$ratioBudget;
                }
                if($postArr['catalog_up']=='on')
			    {
                    $catalogPercent	= ($postArr['catalogRate']*100)/$ratioBudget;
                }
                if($postArr['logo_up']=='on')
			    {
				    $logoPercent 	= ($postArr['logoRate']*100)/$ratioBudget;
                }
			}
			$serviceRatio	= $videoPercent."-".$logoPercent ."-".$catalogPercent;
			$updateMeta= array();
			$updateMeta['servicename_ratio'] = $serviceRatio;
            $updateMeta['servicename'] 		 = $serviceName;
            $duartion_array =array();
			foreach($this->budgetArr as $key => $value)
			{
				$updateMeta['primary_campaign_budget']+= $this->budgetArr[$key]['budget'];
				//$updateMeta['primary_campaign_duration'] = $this->budgetArr[$key]['duration'];
                if($key!=8)
                {
                    $duartion_array[]   = $this->budgetArr[$key]['duration'];
                }
				//$upArr['duration']  					 = $this->budgetArr[$key]['duration'];
			}
            $updateMeta['primary_campaign_duration']  					 = max($duartion_array);
            $upArr['duration']  					 = max($duartion_array);
			$this->financeObj -> metaTempUpdate($updateMeta);
            $this->update_temp_enhancement($postArr);
			
			if($this->budgetArr['2']['exclusivelisting_tag']==1)
			{
				$upArr['exclusivelisting_tag']=1;
			}
			if(intval($this->budgetArr['8']['budget'])!= $upArr['budget'])
			{
				$upArr['recalculate_flag'] = 1;
			}
			if(intval($upArr['budget'])>0)
			{
				$this->financeObj->financeInsertUpdateTemp(8,$upArr);
				//$this->updateExtradetails($serviceName);
			}
            else
            {
                $upArr['recalculate_flag'] = 0;
                $this->financeObj->financeInsertUpdateTemp(8,$upArr);
            }
			
		}
		else if($eligibleFlg == 1)
		{
			$serviceName = '~video_shooting~logo~catalog~';
			$totBud = ($postArr['videoRate']+$postArr['catalogRate']+$postArr['logoRate']);
			$vidPercent = sprintf ("%.2f",($postArr['videoRate']/$totBud)*100);
			$catPercent = sprintf ("%.2f",($postArr['catalogRate']/$totBud)*100);
			$logPercent = sprintf ("%.2f",($postArr['logoRate']/$totBud)*100);

			$updateMeta['servicename_ratio'] = $vidPercent."-".$catPercent."-".$logPercent;
            $updateMeta['servicename'] 		 = $serviceName;
            foreach($this->budgetArr as $key => $value)
			{
				$updateMeta['primary_campaign_budget']+= $this->budgetArr[$key]['budget'];
				//$updateMeta['primary_campaign_duration'] = $this->budgetArr[$key]['duration'];
                $duartion_array[]   = $this->budgetArr[$key]['duration'];
				//$upArr['duration']  					 = $this->budgetArr[$key]['duration'];
			}
            $updateMeta['primary_campaign_duration']  					 = max($duartion_array);
			$this->financeObj -> metaTempUpdate($updateMeta);
			if($this->budgetArr['8']['budget']>0)
			{
				$update['budget'] 	= 0;
				$update['duration']	= 0;
				$this->financeObj->financeInsertUpdateTemp('8',$update);
				//$this->updateExtradetails($serviceName);
			}
		}
		if(($this->budgetArr['7']['budget']!=''|| $this->budgetArr['7']['budget']!='0') && intval($postArr['regfee'])!=0)
		{
			$insArr['budget'] 	= $postArr['regfee'];
			foreach($this->budgetArr as $value){
				if($value['duration']!= ''){
					$duration = $value['duration'];
				}
			}
			$insArr['duration']	= ($duration>0)?$duration:'365';
			$this->financeObj->financeInsertUpdateTemp('7',$insArr);
		}
	}
	
	function updateExtradetails($serviceName)
	{
		$update="UPDATE tbl_companymaster_extradetails_shadow SET servicename='".$serviceName."' WHERE sphinx_id = ".$this->sphinx_id;
		$this->conn_iro->query_sql($update);
	}
	
	function getUploadRates($city)
	{
		$video_qry 	= 'SELECT video_shooting_rate, logo_rate, catalog_rate FROM tbl_business_uploadrates WHERE city = "'.$city.'"'; 
		$res_qry 	= $this->conn_decs->query_sql($video_qry);

		if($res_qry &&  mysql_num_rows($res_qry)>0)
		{
			$row_qry =  mysql_fetch_assoc($res_qry);
		}		
		return $row_qry;
	}
    function getDataCity($city)
    {
        $all_main_cities=array("mumbai","delhi","hyderabad","kolkata","bangalore","chennai","pune","ahmedabad","jaipur","chandigarh","coimbatore");
        if(in_array(strtolower($city),$all_main_cities))
        {
            $return_city_name = $city;
        }
        else
        {
            $return_city_name = "other_cities";
        }
        return $return_city_name;
    }

    function get_enhancement_flag()
    {
        $this->newbudgetArr= $this->financeObj->getFinanceTempData(); 
        if($this->package_flag==2)
        {
            if($this->newbudgetArr['16']['budget'] ==0)
            {
                $oldbudgetArr= $this->financeObj->getFinanceMainData(16);
            }
            else
            {
                $oldbudgetArr[16]= $this->newbudgetArr[16];
            }
            
            $qry_get_smart_enhancement_flag = "SELECT  video,logo,catalogue  FROM   tbl_smart_listing_Justdialg  WHERE   registrationfees ='".$oldbudgetArr['16']['budget']."'  AND annualcost='".$this->newbudgetArr['1']['budget']."'"; //exit;
			$res_get_smart_enhancement_flag = $this->conn_decs->query_sql($qry_get_smart_enhancement_flag); 
            if(!$res_get_smart_enhancement_flag)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_get_smart_enhancement_flag;
                $this->query_execution = false;
                return false;
            }
            if(mysql_num_rows($res_get_smart_enhancement_flag)>0)
            {
                $row_get_smart_enhancement_flag = mysql_fetch_assoc($res_get_smart_enhancement_flag);
                $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));
                if($row_get_smart_enhancement_flag['video'])
                {
                    $totBud += $vlcBudget['video_shooting_rate'];
                    $video_flag='video_shooting';
                }
                if($row_get_smart_enhancement_flag['logo'])
                {
                    $totBud += $vlcBudget['logo_rate'];
                    $logo_flag ='logo';
                }
                if($row_get_smart_enhancement_flag['catalogue'])
                {
                    $totBud += $vlcBudget['catalog_rate'];
                    $catalog_flag ='catalog';
                }
                if(intval($totBud)>0)
                {
                    $vidPercent =($vlcBudget['video_shooting_rate']*100)/$totBud;
                    $catPercent = ($vlcBudget['catalog_rate']*100)/$totBud;
                    $logPercent = ($vlcBudget['logo_rate']*100)/$totBud;
                }
                $this->servicename_ratio = (($row_get_smart_enhancement_flag['video'])?$vidPercent:"0")."-".(($row_get_smart_enhancement_flag['logo'])?$logPercent:"0")."-".(($row_get_smart_enhancement_flag['catalogue'])?$catPercent:"0");
                $this->enhancement = "~".$video_flag."~~".$logo_flag."~~".$catalog_flag."~";
                mysql_free_result($res_get_smart_enhancement_flag);
                unset($row_get_smart_enhancement_flag);
            }
            else
            {
                $smart_video_flag = $this->get_smart_videoflag($oldbudgetArr['16']['budget']);
                /*$sql_v="select video from tbl_smart_listing_Justdialg where registrationfees <=".$oldbudgetArr['16']['budget']." and video>0";		
		        $res_v = $this->conn_decs->query_sql($sql_v);
                if(!$res_v)
                {
                    echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_v;
                    $this->query_execution = false;
                    return false;
                }*/
                if($smart_video_flag)
                {
                    $video_flag='video_shooting';
                    $logo_flag ='logo';
                    $catalog_flag ='catalog';
                }
                else
                {
                    $logo_flag ='logo';
                    $catalog_flag ='catalog';
                }
                $vlcBudget	 = $this->getUploadRates(strtolower($this->main_city));print_r($vlcBudget);
                if($video_flag)
                {
                    $totBud += $vlcBudget['video_shooting_rate'];
                    //$video_flag='video_shooting';
                }
                if($logo_flag)
                {
                    $totBud += $vlcBudget['logo_rate'];
                    //$logo_flag ='logo';
                }
                if($catalog_flag)
                {
                    $totBud += $vlcBudget['catalog_rate'];
                    //$catalog_flag ='catalog';
                }
                if(intval($totBud)>0)
                {
                    if($video_flag){$vidPercent =($vlcBudget['video_shooting_rate']*100)/$totBud;}
                    if($logo_flag) {$catPercent = ($vlcBudget['catalog_rate']*100)/$totBud;}
                    if($catalog_flag){$logPercent = ($vlcBudget['logo_rate']*100)/$totBud;}
                }
                $this->servicename_ratio = $vidPercent."-".$logPercent."-".$catPercent;
                $this->enhancement = "~".$video_flag."~~".$logo_flag."~~".$catalog_flag."~";
                mysql_free_result($res_get_smart_enhancement_flag);
                unset($row_get_smart_enhancement_flag);
            }
        }
        else
        {
            $qry_get_package_enhancement_flag = "SELECT annualcost, video, logo, catalogue FROM tbl_premium_listing_Justdialg WHERE city = '" . $this->city . "' AND JD_flag=1 HAVING (annualcost/365) <= '" . ($this->newbudgetArr['1']['budget']/$this->newbudgetArr['1']['duration']). "' 	ORDER BY annualcost DESC  LIMIT 1"; 
            $res_get_package_enhancement_flag = $this->conn_decs->query_sql($qry_get_package_enhancement_flag);
            if(!$res_get_package_enhancement_flag)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_get_package_enhancement_flag; 
                $this->query_execution = false;
                return false; 
            }

            if(mysql_num_rows($res_get_package_enhancement_flag)>0)
            {
                $row_get_package_enhancement_flag = mysql_fetch_assoc($res_get_package_enhancement_flag);
                if($row_get_package_enhancement_flag['video'])
                {
                    $video_flag='video_shooting';
                }
                if($row_get_package_enhancement_flag['logo'])
                {
                    $logo_flag ='logo';
                }
                if($row_get_package_enhancement_flag['catalogue'])
                {
                    $catalog_flag ='catalog';
                }
                $this->enhancement = "~".$video_flag."~~".$logo_flag."~~".$catalog_flag."~";
                mysql_free_result($res_get_package_enhancement_flag);
                unset($row_get_package_enhancement_flag);
            }
        }
    }
    
    function get_finance_meta_array()
    {
        $this->get_enhancement_flag();
        $enhancement_array= array();
        $enhancement_array['servicename']=$this->enhancement; 
        $enhancement_array['servicename_ratio']=$this->servicename_ratio;
        $enhancement_array['primary_campaign_budget'] = $this->newbudgetArr['1']['budget'];
        $enhancement_array['primary_campaign_duration'] = $this->newbudgetArr['1']['duration'];
        $this->get_minimum_enhancement_budget();
        return $enhancement_array;
    }

    function updateCompanyServicename()
    {
        //echo "INSERT INTO UPDATE FUNCTION";
        $meta_array = $this->financeObj->getMainMeta();
        $extra_str="[return array from main meta table :".implode("#",$meta_array)."]";
        $this->logmsgvirtualno("service name form meta table",$this->log_path,'Approval process',$this->parentid,$extra_str);
        if(is_array($meta_array)&& count($meta_array)>0)
        {
            $return_flag = $this->updateCompanymasterServicename($meta_array);
        }
        return $return_flag;
    }

    function get_iscalculated_flag()
    {
        $other_budget_flag =0;
        $temp_budget_array = $this->financeObj->getFinanceTempData();
        foreach($temp_budget_array as $campid =>$value)
        {
            if($temp_budget_array[$campid]['budget']>0 && ($campid==1 || $campid== 2 || $campid==10 || $campid ==16))
            {
                if($temp_budget_array[$campid]['recalculate_flag']==1 )
                {
                    $this->iscalculated_flag = $temp_budget_array[$campid]['recalculate_flag'];
                    break;
                }
            }
        }
    }

    function updateCompanymasterServicename($meta_array)
    {
        $qry_update_extradetail_servicename = "UPDATE tbl_companymaster_extradetails SET servicename = '" . $meta_array['servicename'] . "'  WHERE parentid = '" . $this->parentid . "'";
        $res_update_extradetail_servicename = $this->conn_iro->query_sql($qry_update_extradetail_servicename, $this->parentid, true);
        if(!$res_update_extradetail_servicename)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_update_extradetail_servicename; 
            $extra_str="[Qry :".$qry_update_extradetail_servicename."][Qry result: ".$res_update_extradetail_servicename."]";
            $this->logmsgvirtualno("Update service name in live table failed",$this->log_path,'Approval process',$this->parentid,$extra_str);
            return false;        
        }
        $extra_str="[Qry :".$qry_update_extradetail_servicename."][Qry result: ".$res_update_extradetail_servicename."]";
        $this->logmsgvirtualno("Update service name in live table",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return true;        
    }
    
    function update_temp_enhancement($postArr)
    {
        $add_str = '';
        if($postArr['video_up']=='on')
        {
            $add_str .="video_facility = '2',";
        }
        else
        {
            $add_str .="video_facility = '0',";
        }
        if($postArr['catalog_up']=='on')
        {
            $add_str .="catalog_facility = '1',";
        }
        else
        {
            $add_str .="catalog_facility = '0',";
        }
        if($postArr['logo_up']=='on')
        {
            $add_str .="logo_facility = '1'";
        }
        else
        {
            $add_str .="logo_facility = '0'";
        }
        $qry_update_temp_ehn = "update tbl_business_temp_enhancements set ".$add_str." where contractid='".$this->parentid."'";
        if(strtoupper(trim($this->module))=='ME')
        {
            $res_qry_update_temp_ehn = $this->conn_idc->query_sql($qry_update_temp_ehn); 
        }
        elseif(strtoupper(trim($this->module))=='TME')
        {
            $res_qry_update_temp_ehn = $this->conn_tme_temp->query_sql($qry_update_temp_ehn); 
        }
        else
        {
            $res_qry_update_temp_ehn = $this->conn_decs->query_sql($qry_update_temp_ehn); 
        }
    }

    function get_smart_videoflag($reg_fee)
    {
        $v_flag= false;
        $sql_v="select video from tbl_smart_listing_Justdialg where registrationfees <='".$reg_fee."' and video>0";		
        $res_v = $this->conn_decs->query_sql($sql_v);
        if(!$res_v)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_v;
            $this->query_execution = false;
            return false;
        }
        if(mysql_num_rows($res_v)>0)
        {
            $v_flag = true;
        }
        return $v_flag;
    }

    function upsell_downsell($selling_flag,$city,$module='')
    {
        $upArr				= array();
		$upArr['budget']	= 0;
        $update_rate = array();
        $updateMeta = array();
        $temp_eligibleFlg = $this->eligibility(1);
        $update_rate = $this->getUploadRates($city);
        $totBud = ($update_rate['video_shooting_rate']+$update_rate['catalog_rate']+$update_rate['logo_rate']);
        $vidPercent = sprintf ("%.2f",($update_rate['video_shooting_rate']/$totBud)*100);
        $catPercent = sprintf ("%.2f",($update_rate['catalog_rate']/$totBud)*100);
        $logPercent = sprintf ("%.2f",($update_rate['logo_rate']/$totBud)*100);

        if(intval(trim($temp_eligibleFlg))==0)
        {
            $qry_get_temp = "select * from tbl_business_temp_enhancements where contractid='".$this->parentid."'";
            if(strtoupper(trim($module))=='ME')
            {
                $res_qry_get_temp = $this->conn_idc->query_sql($qry_get_temp); 
            }
            elseif(strtoupper(trim($module))=='TME')
            {
                $res_qry_get_temp = $this->conn_tme_temp->query_sql($qry_get_temp); 
            }
            else
            {
                $res_qry_get_temp = $this->conn_decs->query_sql($qry_get_temp); 
            }
            if(!$res_qry_get_temp)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_get_temp; 
                exit;
            }
            else
            {
                $row_qry_get_temp = mysql_fetch_assoc($res_qry_get_temp);
                if($row_qry_get_temp['video_facility']==2 || $row_qry_get_temp['video_facility']==1)
                {
                    $upArr['budget']+=$update_rate['video_shooting_rate'];
				    $serviceName.='~video_shooting~';
                }
                if($row_qry_get_temp['catalog_facility']==1)
                {
                    $upArr['budget']+=$update_rate['catalog_rate'];
				    $serviceName.='~catalog~';
                }
                if($row_qry_get_temp['logo_facility']==1)
                {
                    $upArr['budget']+=$update_rate['logo_rate'];
				    $serviceName.='~logo~';
                }
    			$ratioBudget=$upArr['budget'];
                if($ratioBudget>0)
                {
                    if($row_qry_get_temp['video_facility']==2 || $row_qry_get_temp['video_facility']==1)
                    {
                        $videoPercent	= ($update_rate['video_shooting_rate']*100)/$ratioBudget;
                    }
                    if($row_qry_get_temp['catalog_facility']==1)
                    {
                        $catalogPercent	= ($update_rate['catalog_rate']*100)/$ratioBudget;
                    }
                    if($row_qry_get_temp['logo_facility']==1)
                    {
                        $logoPercent 	= ($update_rate['logo_rate']*100)/$ratioBudget;
                    }
                }
                $serviceRatio	= $videoPercent."-".$logoPercent ."-".$catalogPercent;
                $updateMeta= array();
                $updateMeta['servicename_ratio'] = $serviceRatio;
                $updateMeta['servicename'] 		 = $serviceName; 
                foreach($this->budgetArr as $key => $value)
                {
                    $updateMeta['primary_campaign_budget']+= $this->budgetArr[$key]['budget'];
                    //$updateMeta['primary_campaign_duration'] = $this->budgetArr[$key]['duration'];
                    $duartion_array[]   = $this->budgetArr[$key]['duration'];
                    //$upArr['duration']  					 = $this->budgetArr[$key]['duration'];
                }
                $updateMeta['primary_campaign_duration']  					 = max($duartion_array);
                $upArr['duration']  					 = max($duartion_array);
                $this->financeObj -> metaTempUpdate($updateMeta);
                //$this->update_temp_enhancement($postArr);
                if(intval($upArr['budget'])>0)
                {
					$upArr['recalculate_flag'] = 1; 
                    $this->financeObj->financeInsertUpdateTemp(8,$upArr);
                    //$this->updateExtradetails($serviceName);
                }
                else
                {
                    $upArr['recalculate_flag'] = 0;
                    $this->financeObj->financeInsertUpdateTemp(8,$upArr);
                }
            }
        }
        else
        {
            $serviceName = '~video_shooting~logo~catalog~';
            $updateMeta['servicename_ratio'] = $vidPercent."-".$catPercent."-".$logPercent;
            $updateMeta['servicename'] 		 = $serviceName;
            $this->financeObj -> metaTempUpdate($updateMeta);
            if($this->budgetArr['8']['budget']>0)
            {
                $update['budget'] 	= 0;
                $update['duration']	= 0;
                $this->financeObj->financeInsertUpdateTemp('8',$update);
                //$this->updateExtradetails($serviceName);
            }
        }
    }
    function logmsgvirtualno($sMsg, $sNamePrefix,$process,$contractid,$extra_str='')
    {
		return;
        $log_msg='';
        // fetch directory for the file
        $pathToLog = dirname($sNamePrefix); 
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        /*$file_n=$sNamePrefix.$contractid.".txt"; */
        $file_n=$sNamePrefix.'enhancement_'.$contractid.".html";
        // Set this to whatever location the log file should reside at.
        $logFile = fopen($file_n, 'a+');

        // Change this to point to the User ID variable in session.
        if (isset($this->usercode) || isset($_SESSION['mktgEmpCode'])) {
            $userID = isset($this->usercode) ? $this->usercode : $_SESSION['mktgEmpCode']; //  Switches between TME_Live Session ID and DATAENTRY Session ID
        } else {
            $userID = 'unknown'; // stands for "default"  or "unknown"
        }
        /*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
        $pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
        $log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
                        <tr valign='top'>
                            <td style='width:10%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
                            <td style='width:10%; border:1px solid #669966'>File name:".$pageName."</td>
                            <td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
                            <td style='width:30%; border:1px solid #669966'>Extra Message: ".$extra_str."</td>
                            <td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
                            <td style='width:10%; border:1px solid #669966'>Action :".$process."</td>
                        </tr>
                    </table>";
        fwrite($logFile, $log_msg);
        fclose($logFile);
    }
}
?>
