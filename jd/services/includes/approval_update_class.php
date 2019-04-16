<?php

class Approval_update_class extends DB {

    var $params         = null;
    var $dataservers    = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
        $this->params=$params;
        if($params['act']==='fcv'):
            $this->ucode    = trim($params['ucode']);
            $this->amount   = trim($params['amount']);
            elseif ($params['act']==='refund'):
                $this->source   = $params['module'];
            elseif ($params['act']==='fundtrn'):
        endif;
        $this->data_city  = trim($params['data_city']);

        $this->setServers();
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Function to set DB connection objects
     */
    function setServers() {
        global $db;
        $conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');

        $this->conn_iro     = $db[$conn_city]['iro']['master'];             // 67.213 - db_iro
        $this->conn_local   = $db[$conn_city]['d_jds']['master'];           // 67.213 - d_jds
        $this->conn_idc     = $db[$conn_city]['idc']['master'];             // 6.52 online_regis_xx
        $this->conn_fnc     = $db[$conn_city]['fin']['master'];             // 67.215 db_finance
        $this->conn_budget  = $db[$conn_city]['db_budgeting']['master'];    // 67.215 db_budget
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to Update Finance DB tables after the-
    * FCV request gets approved from the Approval module.
    */
   function approveFcv() {
        $stmt   = parent::execQuery("SELECT * FROM tbl_fdv_account WHERE userid = '$this->ucode'", $this->conn_fnc);
        $row    = parent::numRows($stmt);
        if($row>0){
            $amountArray   = parent::fetchData($stmt);
            $fdvlimitavail = round($amountArray['fdvremaining']);
        }
        $now    = date('Y-m-d H:i:s');
        $status = 0;
        if ($this->ucode === '' || $this->amount === '' || $this->data_city === '' ):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                if($this->amount <= $fdvlimitavail){
                    /* Random String Generator */
                    for($i = 0; $i < 3; $i++){
                        $aChars = array('A', 'B', 'C', 'D', 'E','F','G','H', 'I', 'J', 'K', 'L','M','N','P', 'Q', 'R', 'S', 'T','U','V','W', 'X', 'Y', 'Z');
                        $iTotal = count($aChars) - 1;
                        $iIndex = rand(0, $iTotal);
                        $sCode .= $aChars[$iIndex];
                        $sCode .= chr(rand(49, 57));
                    }

                    $cCode1 = time().$sCode;
                    $cCode  = $this->ucode.".".$this->data_city.".".$cCode1;

                    $this->logMsg($this->ucode,'NEW Transactionid generated : '.$cCode.' <=> Create General FCV','fcv');
                    $this->logMsg($this->ucode,'Initial POST data : '.json_encode($this->params).' <=> Create General FCV','fcv');
                    $this->logMsg($this->ucode,'UPDATE tbl_fdv_account for Transactionid :'.$cCode.' <=> Create General FCV ::'.$cCode,'fcv');

                    $uptQuery   = 'UPDATE tbl_fdv_account SET fdvremaining = fdvremaining - "'.$this->amount.'", fdvused= fdvused + "'.$this->amount.'" WHERE userid = "'.$this->ucode.'"';
                    $result_update  = parent::execQuery($uptQuery, $this->conn_fnc);
                    $this->logMsg($this->ucode,'UPDATE tbl_fdv_account qry : '.$uptQuery.' <=> Create General FCV ::'.$cCode,'fcv');
                    $this->logMsg($this->ucode,'UPDATE tbl_fdv_account res : '.$result_update.' <=> Create General FCV ::'.$cCode,'fcv');

                    $transactiontype = 'GEN FCV';
                    $reason          = 'GENERAL FCV';

                    $this->logMsg($this->ucode,'INSERT INTO payment_fdvfcv_master for Transactionid :'.$cCode.' <=> Create General FCV ::'.$cCode,'fcv');
                    $insertfdvfcv_master = 'INSERT INTO payment_fdvfcv_master (transactionid,amount,doneby,doneon,donebyip,transactiontype,reason) VALUES ("'.$cCode.'","'.$this->amount.'","'.$this->ucode.'",NOW(),"'.$_SERVER['REMOTE_ADDR'].'","'.$transactiontype.'","'.$reason.'")';
                    $result_insqry  = parent::execQuery($insertfdvfcv_master, $this->conn_fnc);
                    $this->logMsg($this->ucode,'INSERT INTO payment_fdvfcv_master qry : '.$insertfdvfcv_master.' <=> Create General FCV ::'.$cCode,'fcv');
                    $this->logMsg($this->ucode,'INSERT payment_fdvfcv_master res : '.$result_insqry.' <=> Create General FCV ::'.$cCode,'fcv');

                    $this->logMsg($this->ucode,'INSERT INTO payment_campaign_fdvfcv for Transactionid :'.$cCode.' <=> Create General FCV ::'.$cCode,'fcv');
                    $insertcampaign_fdvfcv = 'INSERT INTO payment_campaign_fdvfcv (transactionid,transferAmount,campaignId) VALUES ("'.$cCode.'","'.$this->amount.'","0")';
                    $result_insery  = parent::execQuery($insertcampaign_fdvfcv, $this->conn_fnc);
                    $this->logMsg($this->ucode,'INSERT INTO payment_campaign_fdvfcv qry : '.$insertcampaign_fdvfcv.' <=> Create General FCV ::'.$cCode,'fcv');
                    $this->logMsg($this->ucode,'INSERT payment_fdvfcv_master res : '.$result_insery.' <=> Create General FCV ::'.$cCode,'fcv');

                    $result = json_encode(array('error' => 0, 'transaction_id'=> $cCode, 'msg' => 'success'));
                }
                $this->logMsg($this->ucode,'END of General FCV for  Transactionid : '.$cCode.' <=> Create General FCV ::'.$cCode,'fcv');

            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * To create logs to table "logs_trn"
     * @param String $ucode
     * @param String $msg
     * @param String $type
     */
    function logMsg($ucode, $msg, $type){
        $now    = date('Y-m-d H:i:s');
        try {
            if (parent::execQuery("INSERT INTO online_regis.logs_trn (custid,logs,type,datetime) "
                    . "VALUES ('$ucode','$msg','$type','$now')",$this->conn_idc)):
                $result = json_encode(array('error' => 0, 'msg' => 'Success'));
            else:
                $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
            endif;
        } catch (Exception $exc) {
            $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
        }
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------

}
