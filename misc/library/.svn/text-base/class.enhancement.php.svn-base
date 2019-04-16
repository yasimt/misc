<?php
if(!defined('APP_PATH'))
{
    die("APP PATH not define....");
}
class Enhancement 
{
    private $parentid, $paid_flag, $businessEligibilityEnhancement, $enhancemnet_flag, $powerlisting_flag,$min_enhancement_budget,$total_budget,$contract_perday,$package_flag,$smart_flag,$supremeSmartPackageAmount,$smart_regis,$version, $enhancement_budget , $old_servicename, $enhancement_entry_date, $query_execution, $min_enhancement_perday;

    private $city, $conn_iro, $conn_finance, $log_path, $campaigns_budget_values;
    public $changes_done, $direct_update,  $process_flag;

    function __construct($parentid, $dbarr, $city)
    {
        if(trim($parentid) == '')
		{
			 die("parentid found as blank");
		}
        $this->city             = $city;
        $this->conn_iro         = new DB($dbarr['DB_IRO']);
        $this->conn_finance     = new DB($dbarr['FINANCE']);
        $this->conn_decs        = new DB($dbarr['DB_DECS']);
        $this->conn_ecsbill     = new DB($dbarr['ECS_BILL']);
        $this->direct_update    =   false; 
        $this->initialized($parentid,$dbarr);
    }
    function initialized($parentid)
    {
        $this->parentid                 =   $parentid;
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
    }

    function process_update_enhancement_package($parentid)
    {
        $process_flag                 = true;
        $this->direct_update    =   true;
        if($parentid!=$this->parentid)
        {
            $this->initialized($parentid);
        }
        $process_flag = $this->business_eligibility();
        if(!$process_flag)
        {
            return false;
        }
        if($this->businessEligibilityEnhancement)
        {
            $process_flag = $this->getPackage();
            if(!$process_flag)
            {
                return false;
            }
            else
            {
                $process_flag = $this->get_enhancement_flag();
                if(!$process_flag)
                {
                    return false;
                }
                else
                {
                    $process_flag = $this->updateCompanyServicename();
                    if(!$process_flag)
                    {
                        return false;
                    }                    
                }
            }
        }
        return true;
    }

    function process_update_enhnacement_platinum($parentid)
    {
        $process_flag                 = true;
        $this->direct_update    =   true;
        if($parentid!=$this->parentid)
        {
            $this->initialized($parentid);
        }
        $process_flag = $this->business_eligibility(); 
        if(!$process_flag)
        {
            return false;
        }
        if($this->businessEligibilityEnhancement)
        {
            $process_flag = $this->get_contract_per_day();
            if(!$process_flag)
            {
                return false;
            }
            else
            {
                $process_flag = $this->get_minimum_enhancement_budget();
                if(!$process_flag)
                {
                    return false;
                }
                else
                {
                    //if($this->total_budget <$this->min_enhancement_budget)
                    //echo "<br>  119 contract per day =".$this->contract_perday;
                    if($this->contract_perday <$this->min_enhancement_perday)
                    {
                        $process_flag = $this->getEnhancementBudget();
                        if(!$process_flag)
                        {
                            return false;
                        }
                        else
                        {
                            if($this->enhancement_entry_date < '2011-02-16 00:00:00')
                            {
                                $process_flag = $this->old_enhancement();
                                if(!$process_flag)
                                {
                                    return false;
                                }
                                else
                                {
                                    $process_flag = $this->updateCompanyServicename();
                                    if(!$process_flag)
                                    {
                                        return false;
                                    } 
                                }
                            }
                            else
                            {
                                $process_flag = $this->new_enhancement();
                                if(!$process_flag)
                                {
                                    return false;
                                }
                                else
                                {
                                    $process_flag = $this->updateCompanyServicename();
                                    if(!$process_flag)
                                    {
                                        return false;
                                    } 
                                }
                            }
                        }
                    }
                    else
                    {
                        $process_flag = $this->premium_direct_vlc_flag();
                        if(!$process_flag)
                        {
                            return false;
                        }
                        else
                        {
                            $process_flag = $this->updateCompanyServicename();
                            if(!$process_flag)
                            {
                                return false;
                            } 
                        }
                    }
                }
            }
        }
    }
        function get_business_eligibility()
        {
            if($this->paid_flag<0)
            {
                $this->business_eligibility();
            }
            return $this->businessEligibilityEnhancement;
        }
    function business_eligibility()
    {
        if($this->paid_flag<0)
        {
            $qry_paid_contract = "SELECT DISTINCT parentid,version FROM payment_instrument_summary WHERE parentid='".trim($this->parentid)."' and approvalStatus='1' LIMIT 1";
            $res_paid_contract = $this->conn_finance->query_sql($qry_paid_contract);
            if(!$res_paid_contract)
            {
                echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_paid_contract; 
                return false;                
            }
            if(mysql_num_rows($res_paid_contract)>0)
            {
                $this->paid_flag = 1;
                $row_paid_contract = mysql_fetch_assoc($res_paid_contract);
                $this->version = $row_paid_contract['version'];
                mysql_free_result($res_paid_contract);
                unset($row_paid_contract);
            }
            else
            {
                mysql_free_result($res_paid_contract);
                $paymentQry="SELECT a.billnumber,a.version FROM " . DB_ECS_BILLING . ".ecs_bill_details a JOIN " . DB_ECS_BILLING . ".ecs_bill_clearance_details b ON a.billnumber = b.billnumber WHERE b.billResponseStatus in (1,3) AND b.billApportioningFlag=1 AND a.data_city = '" . $this->city . "' AND a.parentid = '" . trim($this->parentid) . "' ORDER BY b.billResponseDate DESC LIMIT 1";
                $paymentRes= $this->conn_finance->query_sql($paymentQry);
                if(!$paymentRes)
                {
                    echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $paymentQry; 
                    return false;                              
                }
                if(mysql_num_rows($paymentRes)>0)
                {
                    $this->paid_flag = 1;
                    $row_paymentQry = mysql_fetch_assoc($paymentRes);
                    $this->version = $row_paymentQry['version'];
                mysql_free_result($paymentRes);
                    unset($row_paymentQry);
                }
                else
                {
                    mysql_free_result($paymentRes);
                    $paymentQry_SI="SELECT a.billnumber,a.version FROM " . DB_SI_BILLING . ".si_ecs_bill_details a JOIN " . DB_SI_BILLING . ".si_ecs_bill_clearance_details b ON b.billnumber = b.billnumber WHERE b.billResponseStatus = 1 AND b.billApportioningFlag=1 AND a.data_city = '" . $this->city . "' AND a.parentid='" . trim($this->parentid) . "' ORDER BY b.billResponseDate DESC LIMIT 1";
                    $paymentRes_SI= $this->conn_finance->query_sql($paymentQry_SI);
                    if(!$paymentRes_SI)
                    {
                        echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $paymentQry_SI; 
                        return false;                              
                    }
                    if(mysql_num_rows($paymentRes_SI)>0)
                    {
                        $this->paid_flag = 1;
                        $row_paymentRes_SI = mysql_fetch_assoc($paymentRes_SI);
                        $this->version = $row_paymentRes_SI['version'];
                        mysql_free_result($paymentRes_SI);
                        unset($row_paymentRes_SI);
                    }         
                    else
                    {                    
                        $this->paid_flag = 0;
                    mysql_free_result($paymentRes_SI);
                    }                      
                }
            }
        }
        if(!$this->paid_flag)
        {
            $this->businessEligibilityEnhancement = false;
        }
        return true;
    }

    function get_campaigns_budget()
    {
        $sql_powerlisting   ="SELECT powerlisting,contractid FROM bid_details WHERE powerlisting=(SELECT MIN(powerlisting) FROM bid_details WHERE parentid='".$this->parentid."') AND parentid='".$this->parentid."'";
        $res_powerlisting   =  $this->conn_finance->query_sql($sql_powerlisting);
        if(!$res_powerlisting)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $sql_powerlisting;
            $this->query_execution = false;
            return false; 
        }
        if(mysql_num_rows($res_powerlisting)>0)
        {
            $row_powerlisting = mysql_fetch_assoc($res_powerlisting);
            $this->powerlisting_flag = $row_powerlisting['powerlisting'];
            if($this->powerlisting_flag==2 && stristr(trim($row_powerlisting['contractid']),'L'))
            {
                $this->getPackage(); 
                $this->get_enhancement_flag();
            }
            mysql_free_result($res_powerlisting);
            unset($row_powerlisting);
        }
    }

    function get_contract_per_day()
    {
        $selbudget = "SELECT SUM(bid_perday) AS budgetperday, SUM(campaign_value) AS total_budget  FROM tbl_clients_deduction_perday_master WHERE parentid='" . $this->parentid . "' AND campaignid IN (1,2) AND expired=0 AND activeflag=1 ";
        $selbudget_rs = $this->conn_finance->query_sql($selbudget);
        if(!$selbudget_rs)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $selbudget; 
            $this->query_execution = false;
            return false; 
        }
        if(mysql_num_rows($selbudget_rs)>0)
        {
            if($fetchbudgetperdays = mysql_fetch_array($selbudget_rs))
            {
                $this->contract_perday  =  $fetchbudgetperdays[budgetperday];
                $this->total_budget       =  $fetchbudgetperdays[total_budget];
                //$this->contract_perday= $budgetperday;
                //$this->total_budget =$total_budget;
            }
          mysql_free_result($selbudget_rs);
            unset($fetchbudgetperdays);
        }
        return true;
    }
    function get_enhancement_flag()
    {
        if($this->package_flag==1)
        {
            $qry_get_package_enhancement_flag = "SELECT annualcost, video, logo, catalogue FROM tbl_premium_listing_Justdialg WHERE city = '" . $this->city . "' AND JD_flag=1 HAVING (annualcost/365) <= '" . $this->contract_perday . "' 	ORDER BY annualcost DESC  LIMIT 1"; 
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
                $this->enhancement = $video_flag."~~".$logo_flag."~~".$catalog_flag;
                mysql_free_result($res_get_package_enhancement_flag);
                unset($row_get_package_enhancement_flag);
            }
        }
        else if($this->package_flag==2)
        {
            $qry_get_smart_enhancement_flag = "SELECT  video,logo,catalogue  FROM   tbl_smart_listing_Justdialg  WHERE   registrationfees ='".$this->smart_regis."'  AND annualcost='".$this->supremeSmartPackageAmount."'";
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
                if($row_get_smart_enhancement_flag['video'])
                {
                    $video_flag='video_shooting';
                }
                if($row_get_smart_enhancement_flag['logo'])
                {
                    $logo_flag ='logo';
                }
                if($row_get_smart_enhancement_flag['catalogue'])
                {
                    $catalog_flag ='catalog';
                }
                $this->enhancement = $video_flag."~~".$logo_flag."~~".$catalog_flag;
                mysql_free_result($res_get_smart_enhancement_flag);
                unset($row_get_smart_enhancement_flag);
            }
            else
            {
                $logo_flag ='logo';
                $catalog_flag ='catalog';
                $this->enhancement = $video_flag."~~".$logo_flag."~~".$catalog_flag;
                mysql_free_result($res_get_smart_enhancement_flag);
                unset($row_get_smart_enhancement_flag);
            }
        }
        return true;
    }

    function get_minimum_enhancement_budget()
    {
        $min_enhancement_budget_query = "SELECT MIN(annualcost) AS annualcost FROM tbl_premium_listing_Justdialg WHERE logo!=0 AND catalogue!=0 AND video!=0 AND city='".$this->city."' AND JD_flag=1";
        $min_enhancement_budget_result = $this->conn_decs->query_sql($min_enhancement_budget_query); 
        if(!$min_enhancement_budget_result)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $min_enhancement_budget_query; 
            $this->query_execution = false;
            return false;
        }
        if(mysql_num_rows($min_enhancement_budget_result)>0)
        {
            $min_enhancement_budget_row = mysql_fetch_assoc($min_enhancement_budget_result);
            $this->min_enhancement_budget = $min_enhancement_budget_row['annualcost'];
            $this->min_enhancement_perday = $min_enhancement_budget_row['annualcost']/365;
            mysql_free_result($min_enhancement_budget_result);
            unset($min_enhancement_budget_row);
        }
        return true;
    }

    function getPackage()
    {
        $qry_get_package_budget = "SELECT Offerprice, smart, smart_regis FROM tbl_supreme_main_flag WHERE parentid = '" . $this->parentid . "'";
        $res_get_package_budget = $this->conn_finance->query_sql($qry_get_package_budget);
        if(!$res_get_package_budget)
        {
            echo "<br>Error: " . mysql_error() . " when query was exectued: " . $qry_get_package_budget;
            $this->query_execution = false;
            return false;
        }
        if(mysql_num_rows($res_get_package_budget)>0)
        {
            $row_get_package_budget = mysql_fetch_assoc($res_get_package_budget);
            $this->supremeSmartPackageAmount = $row_get_package_budget['Offerprice'];
            $this->smart_flag = $row_get_package_budget['smart'];
            if(intval(trim($this->smart_flag))==1)
            {
                $this->package_flag = 2;
            }
            else
            {
                $execution_stopped = $this->get_contract_per_day();
                if(!$execution_stopped)
                {
                    return false;
                }
            }
            $this->smart_regis = $row_get_package_budget['smart_regis'];
          mysql_free_result($res_get_package_budget);
            unset($row_get_package_budget);
        }
        return true;
    }

    function getEnhancementBudget()
    {
        $qry_get_enhancement = "SELECT budget,entry_date FROM payment_apportioning WHERE parentid='".$this->parentid."'  AND version ='".$this->version."' AND campaignid = 8 ORDER BY entry_date,campaignid";
        $res_get_enhancement = $this->conn_finance->query_sql($qry_get_enhancement);
        if(!$res_get_enhancement)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_get_enhancement; 
            $this->query_execution = false;
            return false;
        }
        if(mysql_num_rows($res_get_enhancement)>0)
        {
            $row_get_enhancement = mysql_fetch_assoc($res_get_enhancement);
            echo "<br>Enhancement budget-->".$this->enhancement_budget= $row_get_enhancement['budget'];
            echo "<br>Entry date-->".$this->enhancement_entry_date = $row_get_enhancement['entry_date'];
            mysql_free_result($res_get_enhancement);
            unset($row_get_enhancement);
        }
        return true;
    }

    function updateCompanyServicename()
    {
        $qry_update_extradetail_servicename = "UPDATE tbl_companymaster_extradetails SET servicename = '" . $this->enhancement . "', db_update = '" . date('Y-m-d H:i:s') . "'  WHERE parentid = '" . $this->parentid . "'";
        $res_update_extradetail_servicename = $this->conn_iro->query_sql($qry_update_extradetail_servicename, $this->parentid, true);
        if(!$res_update_extradetail_servicename)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_update_extradetail_servicename; 
            return false;        
        }
        $this->changes_done = true;
        return true;        
    }

    function old_enhancement()
    {
        if($this->enhancement_budget>=15000 || $this->enhancement_budget>=13700)
        {
            $this->enhancement = "video_shooting~~logo~~catalog";
        }
        else if($this->enhancement_budget==12000 || $this->enhancement_budget==10700)
        {
            $this->enhancement = "video_shooting~~ ~~ ~~catalog";
        }
        else if($this->enhancement_budget==10000 || $this->enhancement_budget==8700)
        {
            $this->enhancement = "video_shooting~~logo~~ ~~";
        }
        else if($this->enhancement_budget==8000)
        {
            $this->enhancement = " ~~logo~~ ~~catalog";
        }
        else if($this->enhancement_budget==7000 || $this->enhancement_budget==5700)
        {
            $this->enhancement = "video_shooting~~ ~~ ~~ ";
        }
        else if($this->enhancement_budget==5000)
        {
            $this->enhancement = " ~~ ~~ ~~catalog";
        }
        else if($this->enhancement_budget==3000)
        {
            $this->enhancement = " ~~logo~~ ~~ ";
        }
        return true;
    }

    function new_enhancement()
    {
        if($this->enhancement_budget>=6500)
        {
            $this->enhancement = "video_shooting~~logo~~catalog";
        }
        elseif($this->enhancement_budget==5000)
        {
            /*if(stristr($this->old_servicename,'video_shooting') || stristr($this->old_servicename,'video'))
            {
            }
            else
            {
                $process_flag = false;
            }*/
            $process_flag = $this->insert_vlclog_table();
            if(!$process_flag)
            {
                return false;
            }
            return true;
        }
        elseif($this->enhancement_budget==3000)
        {
            $this->enhancement = " ~~logo~~ ~~catalog";
        }
        elseif($this->enhancement_budget==3500)
        {
            $this->enhancement = "video_shooting~~ ~~ ~~ ";
        }
        elseif($this->enhancement_budget==1500)
        {
            /*if(stristr($this->old_servicename,'video_shooting') || stristr($this->old_servicename,'video'))
            {

            }
            else
            {
                $process_flag = false;
            }*/
            $process_flag = $this->insert_vlclog_table();
            if(!$process_flag)
            {
                return false;
            }
            return true;
        }
        return true;
    }

    function getAdditionalEnhancement()
    {
        if($this->direct_update)
        {
            if(trim($this->enhancement_budget)=='3000' && trim($this->old_servicename)=='~~logo~~catalog')
            {
                $this->enhancement = "~~logo~~catalog";
            }
            else if(trim($this->enhancement_budget)=='3500')
            {
                $this->enhancement = "video_shooting~~ ~~ ~~ ";
            }
            else if(trim($this->enhancement_budget)>='6500')
            {
                $this->enhancement = "video_shooting~~logo~~catalog";
            }
        }
        else
        {
            $get_VLC_updaterates="select video_shooting_rate,logo_rate,catalog_rate from tbl_business_uploadrates where city='".$this->city."'";
            $res_VLC_updaterates=$this->conn_decs->query_sql($get_VLC_updaterates);
        }        
    }

    function get_old_servicename()
    {
        $qry_old_servicename="SELECT servicename FROM tbl_companymaster_extradetails WHERE parentid ='".$this->parentid."'";
        $res_old_servicename = $this->conn_iro->query_sql($qry_old_servicename);
        if(!$res_old_servicename)
        {
            echo "<br>Error:" . mysql_error() . " WHEN query was executed: " . $qry_old_servicename; 
            return false;
        }
        if(mysql_num_rows($res_old_servicename)>0)
        {
            $row_old_servicename =  mysql_fetch_assoc($res_old_servicename);
            $this->old_servicename = $row_old_servicename['servicename'];
            mysql_free_result($res_old_servicename);
            unset($row_old_servicename);
        }
    }
    
    function premium_direct_vlc_flag()
    {
        $video_flag='video_shooting';
        $logo_flag ='logo';
        $catalog_flag ='catalog';
        $this->enhancement = $video_flag."~~".$logo_flag."~~".$catalog_flag;
        return true;
    }

    function insert_vlclog_table()
    {
        $qry_insert_vlclog_tbl = "INSERT INTO tbl_enhancement_same_budget SET parentid='".$this->parentid."',enhancementbudget = '".$this->enhancement_budget."', server171 ='1', update_date ='".date('Y-m-d H:i:s')."'";
        $res_insert_vlclog_tbl = $this->conn_decs->query_sql($qry_insert_vlclog_tbl);
        /*run qry in 171*/
        return false;
    }
}
?>
