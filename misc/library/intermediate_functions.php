<?php
require_once( APP_PATH."library/genio_flow.php");
/*------------THIS CONTRACT RETURNS TRUE WHEN IT IS PLATINUM, DIAMOND, GOLD OR PACKAGE------------*/
class web_display
{
    function __construct($dbarr)
    {
        $this -> parentid   = $_SESSION['parentid'][0]=='P' ? $_SESSION['parentid'] : 'P'.$_SESSION['parentid'];

        $this->sphinx_id =  getContractSphinxId ($this->parentid); 

        $genio_variables = get_company_data($this->sphinx_id);
        
        $this->financeObj= new company_master_finance($dbarr,$this->parentid,$this->sphinx_id);
    }
    
	function check_platinum_diamond_gold_package($conn_fnc, $conn_local)
	{
        $this->finance_main_budget = array();
        $this->finance_main_budget = $this->financeObj->getFinanceTempData(); 

        if($this->finance_main_budget['2']['budget']>0)
        {
            return  true;
        }
        else
        {
            if($this->finance_main_budget['1']['budget']>0)
            {
                return  true;
            }
            else
            {
                return false;
            }
        }

			/*$qry1 = " SELECT platinumBudget FROM tbl_bid_otherdetails WHERE contractid = '".$_SESSION['parentid']."'  ";
			$res1 = $conn_local -> query_sql($qry1);
			$row1 = mysql_fetch_assoc($res1);
			if($row1[platinumBudget] > '0')
			{
				return  true;
			}
			else
			{
				$qry2 = " SELECT Offerprice FROM tbl_supreme_flag WHERE parentid = '".$_SESSION['parentid']."' ";
				$res2 = $conn_fnc -> query_sql($qry2);
				$row2 = mysql_fetch_assoc($res2);
				if($row2[Offerprice] > '0')
					return true;
				else
					return false;	
			}*/
	}

	function set_web_display_on_approval($parentid, $conn_fnc, $conn_iro)
	{
			$true = '0';

            $finance_budget = array();
            $finance_budget = $this->financeObj->getFinanceMainData(); 

            if($finance_budget['2']['budget']>0 || $finance_budget['1']['budget']>0)
            {
                $true=1;
            }
			if($true == '1')
			{
				if(!isset($compmaster_obj)){
					$compmaster_obj = new companyMasterClass($conn_iro,"",$parentid);
				}
				$display_type=	'IRO~WEB~WIRELESS';
				$uparr['tbl_companymaster_generalinfo'] = array("parentid" => $parentid, "displayType" => $display_type);
				$compmaster_obj->UpdateRow($uparr);
			}
	}
}
?>
