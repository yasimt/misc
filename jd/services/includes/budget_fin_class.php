<?php

class Budget_fin_class extends DB {

    var $params = null;
    var $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
        $this->version = trim($params['vrsn']);
        $this->parant_id = trim($params['p_id']);
        $this->data_city = trim($params['data_city']);
        $this->campaignid= trim($params['campid']);

        $this->setServers();
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * Function to set DB connection objects
     */
    function setServers() {
        global $db;
        $conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');

        $this->conn_iro = $db[$conn_city]['iro']['master'];             // 67.213 - db_iro
        $this->conn_local = $db[$conn_city]['d_jds']['master'];           // 67.213 - d_jds
        $this->conn_idc = $db[$conn_city]['idc']['master'];             // 6.52 online_regis_xx
        $this->conn_fnc = $db[$conn_city]['fin']['master'];             // 67.215 db_finance
        $this->conn_budget = $db[$conn_city]['db_budgeting']['master'];    // 67.215 db_budget
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * function to check id the down sell request is valid or already existing
     * @return json
     */
    function getTBDIADetails() {
        if ($this->parant_id === '' || $this->version ===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt = parent::execQuery("SELECT * FROM tbl_bidding_details_intermediate_approvalmodule WHERE parentid='$this->parant_id' AND version='$this->version'", $this->conn_budget);
                $row = parent::numRows($stmt);
                if ($row > 0):
                    $i = 0;
                    while ($rows = parent::fetchData($stmt)) {
                        $data[$i] = $rows;
                        $arrVal['catid'] = $rows['catid'];
                        $arrVal['pincode_list'] = $rows['pincode_list'];
                        $arrVal['version'] = $rows['version'];
                        $data[$i]['cat_budget'] = $rows['cat_budget'];
                        $data[$i]['callcount'] = $this->getCallCount($arrVal);
                        $i++;
                    }
                    $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                else:
                    $data = array();
                    $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * function to check id the down sell request is valid or already existing
     * @return json
     */
    function getPADetails() {
        if ($this->parant_id === '' || $this->version === ''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt = parent::execQuery("SELECT group_concat(campaignid) as campaignid,entry_date, start_date,version, sum(budget) as budget FROM payment_apportioning WHERE parentid='$this->parant_id' AND budget!=balance group by version ORDER BY entry_date,campaignid", $this->conn_fnc);
                $row = parent::numRows($stmt);
                if ($row > 0):
                    $i = 0;
                    while ($rows = parent::fetchData($stmt)) {
                        $data[$i] = $rows;
                        $i++;
                    }
                    $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                else:
                    $data = array();
                    $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    function getCallCount($dataArry) {
        $catid = $dataArry['catid'];
        foreach (json_decode($dataArry['pincode_list']) as $key => $value) {
            $pinArr[] = $key;
        }
        $inPinCodes = implode(',', $pinArr);
        try {
            $stmt = parent::execQuery("select SUM(callcount) as callcount from db_budgeting.tbl_fixedposition_pincodewise_bid where catid in ($catid) and pincode in($inPinCodes)", $this->conn_budget);
            $row = parent::numRows($stmt);
            if ($row > 0):
                $i = 0;
                while ($rows = parent::fetchData($stmt)) {
                    $callcount[$i] = $rows;
                    $i++;
                }
            endif;
        } catch (Exception $exc) { echo $exc->getTraceAsString(); }
        $numCalls=$callcount[0]['callcount'];
        return $numCalls;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * to fetch the old Version
     * @return JSON
     */
    function getLastVersion() {
        if ($this->parant_id === '' || $this->version ===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $stmt = parent::execQuery("select max(version) as version from db_finance.payment_apportioning WHERE parentid='$this->parant_id' AND version < '$this->version'", $this->conn_fnc);
                $row = parent::numRows($stmt);
                if ($row > 0):
                    while ($rows = parent::fetchData($stmt)) {
                        $data['version'] = $rows['version'];
                    }
                    $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                else:
                    $data = array();
                    $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * To fetch the old data for the particular contract and last version
     * @return JSON
     */
    function getOldDataList() {
        if ($this->parant_id === '' || $this->version ===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $data = array();
                $stmt1 = parent::execQuery("SELECT distinct(catid) as catid FROM tbl_bidding_details WHERE parentid='$this->parant_id' and version='$this->version'", $this->conn_fnc);
                $row1 = parent::numRows($stmt1);
                if ($row1 > 0):
                    $i = 0;
                    while ($rows1 = parent::fetchData($stmt1)) {
                        $data[$rows1['catid']]= $this->getFullPinCodeDetails('tbl_bidding_details','pincode as pin, position_flag as pos, bidvalue as bid, callcount as callcnt, sys_budget as budget ',$this->conn_fnc,$this->parant_id,$this->version,$rows1['catid'],$this->conn_budget);
                        $i++;
                    }
                    $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                else:
                    $stmt1 = parent::execQuery("SELECT distinct(catid) as catid FROM tbl_bidding_details_shadow WHERE parentid='$this->parant_id' and version='$this->version'",$this->conn_budget);
                    $row1 = parent::numRows($stmt1);
                    if($row1>0){
                        $i = 0;
                        while ($rows1 = parent::fetchData($stmt1)) {
                            $data[$rows1['catid']]= $this->getFullPinCodeDetails('tbl_bidding_details','pincode as pin, position_flag as pos, bidvalue as bid, callcount as callcnt, sys_budget as budget ',$this->conn_fnc,$this->parant_id,$this->version,$rows1['catid'],$this->conn_budget);
                            $i++;
                        }
                        $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                    }else{
                        $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
                    }
                endif;
            } catch (Exception $exc) {
                $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString()));
            }
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
     * to fetct details of catid each
     * @param string $table
     * @param string $fields
     * @param object $dbconn
     * @param string $pid
     * @param int $version
     * @param int $catid
     * @return JSON
     */
    function getFullPinCodeDetails($table,$fields,$dbconn,$pid,$version,$catid,$dbbdgetconn) {
        try {
            $data = array();
            $stmt1 = parent::execQuery("SELECT $fields FROM $table WHERE parentid='$pid' and version='$version' AND catid='$catid'", $dbconn);
            $row1 = parent::numRows($stmt1);
            if ($row1 > 0):
                $i = 0;
                while ($rows = parent::fetchData($stmt1)) {
                    $data[$i]['catid'] = $catid;
                    $data[$i]['pin'] = $rows['pin'];
                    $data[$i]['pos'] = $rows['pos'];
                    $data[$i]['bid'] = $rows['bid'];
                    $data[$i]['call'] = $rows['callcnt'];
                    $data[$i]['budget'] = $rows['budget'];
                    $data[$i]['version'] = $version;
                    $i++;
                }
                $result = $data;
            else:
                $stmt2 = parent::execQuery("SELECT $fields FROM tbl_bidding_details_expired WHERE parentid='$pid' and version='$version' AND catid='$catid'", $dbconn);
                $row2 = parent::numRows($stmt2);
                if($row2 > 0){
                    $i = 0;
                    while ($rows = parent::fetchData($stmt2)) {
                        $data[$i]['catid'] = $catid;
                        $data[$i]['pin'] = $rows['pin'];
                        $data[$i]['pos'] = $rows['pos'];
                        $data[$i]['bid'] = $rows['bid'];
                        $data[$i]['call'] = $rows['callcnt'];
                        $data[$i]['budget'] = $rows['budget'];
                        $data[$i]['version'] = $version;
                        $i++;
                    }
                    $result = $data;
                }else{
                    $stmt3 = parent::execQuery("SELECT $fields FROM tbl_bidding_details_shadow_archive WHERE parentid='$pid' and version='$version' AND catid='$catid'", $dbbdgetconn);
                    $row3 = parent::numRows($stmt3);
                    if($row3 > 0){
                        $i = 0;
                        while ($rows = parent::fetchData($stmt3)) {
                            $data[$i]['catid'] = $catid;
                            $data[$i]['pin'] = $rows['pin'];
                            $data[$i]['pos'] = $rows['pos'];
                            $data[$i]['bid'] = $rows['bid'];
                            $data[$i]['call'] = $rows['callcnt'];
                            $data[$i]['budget'] = $rows['budget'];
                            $data[$i]['version'] = $version;
                            $i++;
                        }
                        $result = $data;
                    }else{
                        $stmt4 = parent::execQuery("SELECT $fields FROM tbl_bidding_details_shadow WHERE parentid='$pid' and version='$version' AND catid='$catid'", $dbbdgetconn);
                        $row4 = parent::numRows($stmt4);
                        if($row4 > 0){
                            $i = 0;
                            while ($rows = parent::fetchData($stmt4)) {
                                $data[$i]['catid'] = $catid;
                                $data[$i]['pin'] = $rows['pin'];
                                $data[$i]['pos'] = $rows['pos'];
                                $data[$i]['bid'] = $rows['bid'];
                                $data[$i]['call'] = $rows['callcnt'];
                                $data[$i]['budget'] = $rows['budget'];
                                $data[$i]['version'] = $version;
                                $i++;
                            }
                            $result = $data;
                        }else{
                            $result='';
                        }
                    }
                }
                //$result = '';
            endif;
        } catch (Exception $exc) { $result = ''; }
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    function getCampaignName() {
        try {
            $data = array();
            $stmt1 = parent::execQuery("SELECT campaignName,campaignId FROM payment_campaign_master WHERE campaignId='$this->campaignid'", $this->conn_fnc);
            $row1 = parent::numRows($stmt1);
            if ($row1 > 0):
                $i = 0;
                while ($rows = parent::fetchData($stmt1)) {
                    $data['id'] = $rows['campaignId'];
                    $data['name'] = $rows['campaignName'];
                    $i++;
                }
                $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
            else:
                $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
            endif;
        } catch (Exception $exc) { $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString())); }
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    function getCampaignIds() {
        if ($this->parant_id === '' || $this->version ===''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            try {
                $data = array();
                $stmt1 = parent::execQuery("SELECT group_concat(campaignid SEPARATOR '-') as campaignids from tbl_companymaster_finance_temp where parentid='$this->parant_id' and version='$this->version' and recalculate_flag=1", $this->conn_idc);
                $row1 = parent::numRows($stmt1);
                while ($rows = parent::fetchData($stmt1)) {
                    $data = $rows['campaignids'];
                }
                if($data=='' || $data==NULL){
                    $stmt1 = parent::execQuery("SELECT group_concat(campaignid SEPARATOR '-') as campaignids from payment_apportioning where parentid='$this->parant_id' and version='$this->version'", $this->conn_fnc);
                    $row1 = parent::numRows($stmt1);
                    while ($rows = parent::fetchData($stmt1)) {
                        $data = $rows['campaignids'];
                    }
                }
                if($data=='' || $data==NULL){
                    $result = json_encode(array('data' => $data, 'error' => 1, 'msg' => 'No records found'));
                }else{
                    $result = json_encode(array('data' => $data, 'error' => 0, 'msg' => 'success'));
                }
            } catch (Exception $exc) { $result = json_encode(array('error' => 2, 'msg' => $exc->getTraceAsString())); }
            return $result;
        endif;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
}
