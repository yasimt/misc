<?php

class Budget_cal_class extends DB {

    var $params         = null;
    var $dataservers    = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
        $this->params=$params;
        $this->data_city  = trim($params['data_city']);
        if($params['act']=='add'){
            $this->ucode    = $params['ucode'];
            $this->pincode  = $params['pincode'];
            $this->catids   = $params['catids'];
            $this->pinlist  = $params['pinlist'];
            $this->city     = $params['city'];
        }
        if($params['act']=='bid'){
            $this->catid    = $params['catid'];
        }
        $this->categoryClass_obj = new categoryClass();
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
    * function to fetch all cities
    */
    function fetchAllCity() {
        $stmt   = parent::execQuery("select distinct(a.mapped_cityname) as city, b.city_id as id from city_master a join city_master b on a.mapped_cityname=b.ct_name;", $this->conn_local);
        $row    = parent::numRows($stmt);
        if($row>0):
            while ($cityArray   = parent::fetchData($stmt)) {
                $data[$cityArray['id']]=$cityArray['city'];
            }
            $result=json_encode(array('error' => 0, 'data' => $data));
            else:
                $result=json_encode(array('error' => 1, 'msg' => 'No data'));
        endif;

        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to check if the category is biddable or not
    */
    function checkBiddable() {
        if($this->catid==''){
            $result=json_encode(array('error' => 1, 'msg' => 'Category Id required'));
        }else{
            //$stmt   = parent::execQuery("select biddable_type,category_name,catid from d_jds.tbl_categorymaster_generalinfo where catid='$this->catid' and  category_type&64 !=64", $this->conn_local);
            //$row    = parent::numRows($stmt);
            $cat_params = array();
            $cat_params['page'] ='Budget_cal_class';
            $cat_params['data_city']    = $this->data_city;         
            $cat_params['return']       = 'biddable_type,category_name,catid';

            $where_arr      =   array();
            $where_arr['catid']         = $this->catid;
            $where_arr['category_type'] = "!64";     
            $cat_params['where']    = json_encode($where_arr);
			if($this->catid!=''){
				$cat_res    =   $this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
            $cat_res_arr = array();
            if($cat_res!=''){
                $cat_res_arr =  json_decode($cat_res,TRUE);
            }

            if(count($cat_res_arr['results'])>0):
                foreach($cat_res_arr['results'] as $key=>$cityArray) {
                    $data['biddable']=$cityArray['biddable_type'];
                    $data['name']=$cityArray['category_name'];
                    $data['catid']=$cityArray['catid'];
                }
                $result=json_encode(array('error' => 0, 'data' => $data));
                else:
                    $result=json_encode(array('error' => 1, 'msg' => 'No data'));
            endif;
        }
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    /**
    * function to fetch all Remote cities
    */
    function fetchRemoteCity() {
        $stmt   = parent::execQuery("select distinct(a.mapped_cityname) as city, b.city_id as id from city_master a join city_master b on a.mapped_cityname=b.ct_name;", $this->conn_local);
        $row    = parent::numRows($stmt);
        if($row>0):
            while ($cityArray   = parent::fetchData($stmt)) {
                $data[]=$cityArray['id'];
            }
            $ids=  implode(',', $data);
            $stmt2   = parent::execQuery("select distinct(ct_name) as city, city_id as id from city_master where city_id not in($ids);", $this->conn_local);
            $row2    = parent::numRows($stmt2);
            if($row2>0):
                while ($cityArray2   = parent::fetchData($stmt2)) {
                    $dataArr[$cityArray2['city']]['id']=$cityArray2['id'];
                    $dataArr[$cityArray2['city']]['city']=$cityArray2['city'];
                }
            endif;

            $result=json_encode(array('error' => 0, 'data' => $dataArr));
            else:
                $result=json_encode(array('error' => 1, 'msg' => 'No data'));
        endif;
        return $result;
    }
    //-----------------------------------------------------------------------------------------------------------------------------------------
    function addCalculationRqst() {
        if ($this->ucode=== '' || $this->city === '' || $this->pincode === '' || $this->catids === '' || $this->pinlist === ''):
            $result = json_encode(array('error' => 1, 'msg' => 'All parameters are required'));
        else:
            $now    = date('Y-m-d H:i:s');
        try {
            if (parent::execQuery("INSERT INTO tbl_bidding_details_summary (parentid,data_city,pincode,category_list,pincode_list,updatedon,updatedby,version) "
                        . "VALUES ('$this->ucode','$this->city','$this->pincode','$this->catids','$this->pinlist','$now','$this->ucode','13') ON DUPLICATE KEY UPDATE"
                    . " data_city='$this->city', pincode='$this->pincode', category_list='$this->catids', pincode_list='$this->pinlist', updatedon='$now'",$this->conn_budget)):
                    $result = json_encode(array('error' => 0, 'msg' => 'Success'));
                else:
                    $result = json_encode(array('error' => 3, 'msg' => 'Sever error: unable to insert new record.'));
                endif;
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
            if (parent::execQuery("INSERT INTO online_regis.logs_trn (custid,logs,type,datetime) VALUES ('$ucode','$msg','$type','$now')",$this->conn_idc)):
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
