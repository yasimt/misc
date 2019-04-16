<?php

class Category_details_class extends DB {

    var $params         = null;
    var $dataservers    = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    function __construct($params) {
        $catid      = trim($params['catid']);
        $data_city  = trim($params['data_city']);

        if (trim($catid) == '') {
            $message = "Category Id is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }
        if (trim($data_city) == '') {
            $message = "Data City is blank.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        $this->catid        = $catid;
        $this->data_city    = $data_city;

        $this->setServers();
        $this->categoryClass_obj = new categoryClass();
    }

    // Function to set DB connection objects
    function setServers() {
        global $db;

        $conn_city = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');

        $this->conn_local       = $db[$conn_city]['d_jds']['master'];
        $this->db_budgeting     = $db[$conn_city]['db_budgeting']['master'];
        $this->fin              = $db[$conn_city]['fin']['master'];
        $this->conn_catmaster   = $this->conn_local;
    }

    function getCategoryDetails() {
        $CatinfoArr=array();
        //$sqlCategoryDetails = "SELECT catid,category_name,biddable_type FROM tbl_categorymaster_generalinfo WHERE catid ='$this->catid'";
       // $resCategoryDetails = parent::execQuery($sqlCategoryDetails, $this->conn_catmaster);
            $cat_params = array();
            $cat_params['page']= 'Category_details_class';
            $cat_params['data_city']    = $this->data_city;     
            $cat_params['return']       = 'catid,category_name,biddable_type';    

            $where_arr      =   array();            
            $where_arr['catid']    = $this->catid;                       
            $cat_params['where']   = json_encode($where_arr);
            
            $cat_res_arr = array();
            if($this->catid!=''){
                $cat_res    =   $this->categoryClass_obj->getCatRelatedInfo($cat_params);           
                if($cat_res!=''){
                    $cat_res_arr =  json_decode($cat_res,TRUE);
                }
            }

        if ($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results']) > 0) {
            foreach ($cat_res_arr['results'] as $key=>$row_catdetails) {
                $catid          = intval($row_catdetails['catid']);
                $category_name  = trim($row_catdetails['category_name']);
                $biddable_type  = intval($row_catdetails['biddable_type']);
                $CatinfoArr[$catid]['catname']      = $category_name;
                $CatinfoArr[$catid]['biddable_type']= $biddable_type;
            }
        }else{
            $message = "Category Id not found.";
            echo json_encode($this->sendDieMessage($message));
            die();
        }

        return $CatinfoArr;
    }

    private function sendDieMessage($msg) {
        $die_msg_arr['error']['code'] = 1;
        $die_msg_arr['error']['msg'] = $msg;
        return $die_msg_arr;
    }

}
