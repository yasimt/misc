<?php
class categoryServices extends DB
{
    var  $conn_iro      = null;
    var  $conn_jds      = null;
    var  $conn_tme  = null;
    var  $conn_fnc      = null;
    var  $conn_idc      = null;
    var  $params    = null;
    var  $dataservers   = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

    var  $parentid      = null;
    var  $module        = null;
    var  $data_city     = null;
    
    
    function __construct($params)
    {
        $this->parentid     = trim($params['parentid']);
        $this->module   = strtoupper(trim($params['module']));
        $this->data_city    = trim($params['data_city']);
        $this->catlist  = trim($params['catlist']);
        $this->e_catlist    = trim($params['e_catlist']);
        $this->action   = trim($params['action']);
        $this->selectedCats     = trim($params['selectedCats']);
        $this->params   = $params;
        
        if(trim($this->parentid)=='')
        {
            $message = "Parentid is blank.";
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = $message;
            echo json_encode($result_msg_arr);exit; 
        }
        if(trim($this->data_city)=='')
        {
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = $message;
            echo json_encode($result_msg_arr);exit; 
        }
        if(trim($this->module)=='')
        {
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = $message;
            echo json_encode($result_msg_arr);exit; 
        }
        if(trim($this->selectedCats)=='')
        {
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = 'Selected catIds Missing';
            echo json_encode($result_msg_arr);exit; 
        }
        if(trim($this->module)!='')
        {
            $this->save_for     = trim($params['save_for']);
        }
        
        
        
        if(trim($this->params['stp']) != "" && $this->params['stp'] != null)
        {
            $this->stp  = intval($this->params['stp']); 
            if(trim($this->params['ntp']) != "" && $this->params['ntp'] != null)
            {
                $this->ntp  = intval($this->params['ntp']); 
            }else
            {
                $message='Please provide correct national type - zone,state or top'; 
                $result_msg_arr['error']['code'] = 1;
                $result_msg_arr['error']['msg'] = $message;
                echo json_encode($result_msg_arr);exit; 
            }
        }
        else
            $this->ntp=0;
        $this->setServers();
        
        $this->categoryClass_obj = new categoryClass();
        
        
    }
        
    // Function to set DB connection objects
    function setServers()
    {   
        global $db;
            
        $conn_city      = ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');

        $this->conn_iro         = $db[$conn_city]['iro']['slave'];
        $this->conn_local       = $db[$conn_city]['d_jds']['master'];
        $this->conn_tme         = $db[$conn_city]['tme_jds']['master'];
        $this->conn_idc         = $db[$conn_city]['idc']['master'];
        $this->conn_local_slave = $db[$conn_city]['d_jds']['slave'];
        
        if(($this->module =='DE') || ($this->module =='CS'))
        {
            $this->conn_temp        = $this->conn_local;
            $this->conn_catmaster   = $this->conn_local;
        }
        elseif($this->module =='TME')
        {
            $this->conn_temp        = $this->conn_tme;
            $this->conn_catmaster   = $this->conn_local;
        }
        elseif($this->module =='ME')
        {
            $this->conn_temp        = $this->conn_idc;
            $this->conn_catmaster   = $this->conn_local;
        }
        else
        {
            $message = "Invalid Module.";
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = $message;
            echo json_encode($result_msg_arr);exit; 
            die();
        }   
    }

    function saveRelevantCategories(){
        $get_pop_sql="select category_flow_info from tbl_business_temp_data where contractid='".$this->parentid."'";
        $res_temp = parent::execQuery($get_pop_sql, $this->conn_temp);
        
        if($res_temp && mysql_num_rows($res_temp)>0)
        {
            while($rowtemp=mysql_fetch_assoc($res_temp))
            {
                $temp_cat_list=$rowtemp['category_flow_info'];

            }
        }
        $catlist=array();
        $catlist=json_decode($temp_cat_list,1);
        $catids_arr = explode("|P|",$this->catlist);
        
        $e_catlist_arr = explode("|P|",$this->e_catlist);
        $catids_arr_all=

        $catids_arr_all=array_merge($catids_arr,$e_catlist_arr);
        
        $catids_arr_all=array_unique($catids_arr_all);
        $catids_arr_all=array_filter($catids_arr_all);
        //$catids_arr_all=array_map('trim',$catids_arr_all);
        
        $catlist_str=implode('|P|', $catids_arr_all);
        if(trim($this->save_for)==''){
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = "Save For Missing";
            echo json_encode($result_msg_arr);exit;

        }
        if(strtoupper($this->save_for)=='MRK'){
            
            $catlist['MRK']=$catlist_str; 

        }
        
        if(strtoupper($this->save_for)=='POP'){
            
            $catlist['POP']=$catlist_str; 

        }
        if(strtoupper($this->save_for)=='SIB'){
            
            $catlist['SIB']=$catlist_str; 

        }
        if(strtoupper($this->save_for)=='CHILD'){
            
            $catlist['CHILD']=$catlist_str; 

        }

        $sql_disc = "INSERT INTO tbl_business_temp_data set
                        contractid='".$this->parentid."',
                        category_flow_info='".json_encode($catlist)."'
                        ON DUPLICATE KEY UPDATE
                        contractid='".$this->parentid."',
                        category_flow_info='".json_encode($catlist)."'";
        $res_disc = parent::execQuery($sql_disc, $this->conn_temp);
        if($res_disc){
                    $result_msg_arr['error']['code'] = 0;
                    $result_msg_arr['error']['msg'] = "Sucess!!";
                    echo json_encode($result_msg_arr);exit;

        }
        else{
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = "Error!!";
            echo json_encode($result_msg_arr);exit;

        }

        
    }
    function getPopularAmongCompetitors(){

        //for local
        $temp_cat_list=$this->selectedCats;
        $bidcats_popular = '';
        $sqlPopularCatInfo = "call sp_similar_business_category('" . $temp_cat_list . "')";
        $resPopularCatInfo = parent::execQuery($sqlPopularCatInfo, $this->conn_local_slave);
        
        if ($resPopularCatInfo && parent::numRows($resPopularCatInfo) > 0) {
            $row_popular_cat = parent::fetchData($resPopularCatInfo);
        
            if ($row_popular_cat['bidcats']) {
                $bidcats_popular = $row_popular_cat['bidcats'];
            }
        }

        if ($bidcats_popular) {
            $this->getCatDetails($bidcats_popular, 0);
        } else {
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = "Error!!";
            echo json_encode($result_msg_arr);
            exit;
        }

    }

    function getSiblingsAndChild(){
        $flag=0;
        $temp_cat_list=$this->selectedCats;
        $temp_cat_list_arr=explode(",",$temp_cat_list); 
        if($this->action=='3' || $this->action==3){
            $flag=0;
        }
        else if($this->action=='4' || $this->action==4){
            $flag=1;
        }
        
        		$city_arr_ipname = array (
									"ahmedabad" 	=> AHMEDABAD_IRO_IP,
									"bangalore" 	=> BANGALORE_IRO_IP,
									"chennai" 		=> CHENNAI_IRO_IP,
									"delhi"			=> DELHI_IRO_IP,
									"hyderabad" 	=> HYDERABAD_IRO_IP,
									"kolkata" 		=> KOLKATA_IRO_IP,
									"pune" 			=> PUNE_IRO_IP,
									"mumbai"  		=> MUMBAI_IRO_IP,
									"remote"  		=> REMOTE_CITIES_IRO_IP);
									
		if($_SERVER['SERVER_ADDR'] != '172.29.64.64'){
			 $url ="http://".$city_arr_ipname[$this->conn_city]."/mvc/services/category/cat_sibling?mcity=".$this->data_city."&city=".$this->data_city."&catids=".$temp_cat_list."&mod=cs&debug=0&ntp=".$this->ntp."&start=0&total=1000&t=".rand();
		}else{
			$url="http://project01.pravinkucha.mum.jdsoftware.com/singlebox/mvc/services/category/cat_sibling?mcity=".$this->data_city."&city=".$this->data_city."&catids=".$temp_cat_list."&mod=cs&debug=0&ntp=".$this->ntp."&start=0&total=1000&t=".rand();
		}
		
		$child_result = json_decode($this->curlCall($url),1);
		$sib_str='';
  		
  		if($child_result['errors']['code'] == 0){
			if(!in_array($child_result['results']['data'],$temp_cat_list_arr))
				$sib_str=$child_result['results']['data'];
		}
		
        /*$sql = "call sp_child_sibling_category('" . $temp_cat_list . "'," . $flag . "," . $this->ntp . ")";


        $res_sib = parent::execQuery($sql, $this->conn_local_slave);

        $sib_str = '';

        if ($res_sib) {
            if (mysql_num_rows($res_sib) > 0) {

                while ($row_sib = mysql_fetch_assoc($res_sib)) {
                    if (!in_array($row_sib['var_result'], $temp_cat_list_arr))
                        $sib_str = $row_sib['var_result'];

                }
            }
        } */


        $this->getCatDetails($sib_str,1);
    }
    function getCatDetails($catids_search, $sameorder = 0)
    {

        $multicity_condition = '';
        $cat_params = array();
        $where_arr  = array(); 

        if ($this->stp) {
            if ($this->ntp != 2){
                $where_arr['category_scope']   = '1';
                $where_arr['city_count']       = '9';
                //$multicity_condition = " AND category_scope = 1 AND city_count=9  ";
            }
            else if ($this->ntp == 2){
                $where_arr['category_scope']   = '1,2';
                $where_arr['city_count']       = '9';
                //$multicity_condition = " AND (category_scope = 1 OR category_scope = 2) AND city_count=9  ";
            }
        }

        //$orderby = "order by callcount desc";
        $special_case =0;
        if ($sameorder == 1) {
            if ($catids_search != ''){
                $special_case =1;
                //$orderby = "order by field(catid," . $catids_search . ")";
            }
        }
        if($special_case ==1){
            $cat_params['scase']   = '1';
        }
        else{
            $cat_params['orderby'] = 'callcount desc'; 
        }
        $bfc_condn = '';
        if ($this->bfcignore == 1) {
            //$bfc_condn = " AND category_type&64=64 !=1 ";
            $where_arr['category_type']  = "!64";
        }

        if ($catids_search) {

            //$get_catd = "select catid, national_catid, category_name as catname, biddable_type as cat_type,  callcount, if(category_type&64=64,1,0) as block_for_contract, if(category_type&16=16,1,0) as exclusive, premium_flag, search_type, business_flag,bfc_bifurcation_flag as mrg,reach_count from tbl_categorymaster_generalinfo where catid in  (" . $catids_search . ") AND biddable_type=1 AND mask_status=0  AND bfc_bifurcation_flag NOT IN (4,5,6,7,8) " . $bfc_condn . $multicity_condition . "$orderby";
            //$get_catd_res = parent::execQuery($get_catd, $this->conn_catmaster);
            $cat_params['data_city']    = $this->data_city;
            $cat_params['page']         = 'categoryServices_lite.php';    
            $cat_params['parentid']     = $this->parentid; 
           
            $cat_params['return']       = 'catid,national_catid,category_name,biddable_type,callcount,category_type,premium_flag,search_type,business_flag,bfc_bifurcation_flag,reach_count';    

                      
            $where_arr['catid']                     = $catids_search;
            $where_arr['biddable_type']             = '1';
            $where_arr['mask_status']               = '0';
            $where_arr['bfc_bifurcation_flag']      = "!4,5,6,7,8";                       
            $cat_params['where']   = json_encode($where_arr);
            
            $cat_res_arr = array();
            if($catids_search!=''){
                $cat_res    =   $this->categoryClass_obj->getCatRelatedInfo($cat_params);           
                if($cat_res!=''){
                    $cat_res_arr =  json_decode($cat_res,TRUE);
                }
            }

            $temp_cat_list = '';
            if ($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results']) > 0) {
                foreach ($cat_res_arr['results'] as $key =>$row) {
                    
                    $category_type = $row['category_type']; 
                    $exclusive = 0; 
                    $block_for_contract = 0; 
                    if(((int)$category_type & 64) == 64){ 
                        $block_for_contract = 1; 
                    } 
                    if(((int)$category_type & 16) == 16){ 
                        $exclusive = 1; 
                    } 

                    if ($row['search_type'] == 1)
                        $cat_search_type = "A";
                    elseif ($row['search_type'] == 2)
                        $cat_search_type = "Z";
                    elseif ($row['search_type'] == 3)
                        $cat_search_type = "SZ";
                    elseif ($row['search_type'] == 4)
                        $cat_search_type = "NM";
                    elseif ($row['search_type'] == 5)
                        $cat_search_type = "VNM";
                    else
                        $cat_search_type = "L";

                    $business_flag = trim($row['business_flag']);
                    if ($business_flag == 1) {
                        $btype = "B2B";
                    } else if ($business_flag == 2) {
                        $btype = "B2C";
                    } else if ($business_flag == 3) {
                        $btype = "B2B,B2C";
                    } else {
                        $btype = "OTHER";
                    }   
                        
                    /*$PK_cat[$i]['cid'] = $row['catid'];
                    $PK_cat[$i]['nid'] = $row['national_catid'];
                    $PK_cat[$i]['cnm'] = $row['catname']." (".$cat_search_type.")";
                    $PK_cat[$i]['ctype'] = $row['cat_type'];
                    $PK_cat[$i]['bfc'] = $row['block_for_contract'];
                    $PK_cat[$i]['cst'] = $cat_search_type;
                    $PK_cat[$i]['ccnt'] = $row['callcount'];
                    $PK_cat[$i]['btype'] = $btype;
                    $PK_cat[$i]['excl'] = $row['exclusive'];
                    $PK_cat[$i]['premium'] = $row['premium_flag'];
                    $i++;*/
                    $merge_flag = intval($row['bfc_bifurcation_flag']);
                    $PK_cat['cid'] = $row['catid'];
                    $PK_cat['nid'] = $row['national_catid'];
                    $PK_cat['cnm'] = $row['category_name'];
                    $PK_cat['ctype'] = $row['biddable_type'];
                    $PK_cat['bfc'] = $block_for_contract;
                    $PK_cat['cst'] = $cat_search_type;
                    $PK_cat['ccnt'] = $row['callcount'];
                    $PK_cat['btype'] = $btype;
                    $PK_cat['excl'] = $exclusive;
                    $PK_cat['premium'] = $row['premium_flag'];
                    $PK_cat['mrg'] = $merge_flag;
                    $PK_cat['rcnt'] = number_format((float)$row['reach_count'], 2, '.', '');
                    if ($merge_flag == 6) {
                        $PK_cat['mrgwith'] = $this->mergeDestCat($row['catname']);
                    }

                    $searched_cat[] = $PK_cat;
                    $cid_array[] = $row['catid'];
                }

                $result_msg_arr['error']['code'] = 0;
                $result_msg_arr['error']['msg'] = "Data found!";
                $result_msg_arr['data']['cat_details'] = $searched_cat;
                echo json_encode($result_msg_arr);
                exit;
            } else {
                $result_msg_arr['error']['code'] = 1;
                $result_msg_arr['error']['msg'] = "Error!!";
                echo json_encode($result_msg_arr);
                exit;
            }
        } else {
            $result_msg_arr['error']['code'] = 1;
            $result_msg_arr['error']['msg'] = "Error!!";
            echo json_encode($result_msg_arr);
            exit;
        }

    }

    function getExistingCats(){

        return array();
        $sqltemp="select catIds as catids from tbl_business_temp_data  where contractid='".$this->parentid."'";
        
        $gettemp = parent::execQuery($sqltemp, $this->conn_temp);
        $temp_cat_list='';
        $catids=array();
        if($gettemp && mysql_num_rows($gettemp)>0)
        {   

            while($gettemprow=mysql_fetch_assoc($gettemp))
            {
                $catids=$gettemprow['catids'];
            }
            $catids=explode('|P|', $catids);
            $catids=array_filter($catids);
        
        }
        return $catids;



    }
    function mergeDestCat($catsrc)
    {
        $catname_des = '';
        $sqlDestCategory = "SELECT catname_des FROM tbl_category_merging_final WHERE  catname_src = '".addslashes(stripslashes($catsrc))."' LIMIT 1";
        $resDestCategory = parent::execQuery($sqlDestCategory, $this->conn_catmaster);
        if($resDestCategory && parent::numRows($resDestCategory)>0)
        {
            $row_des_cat = parent::fetchData($resDestCategory);
            $catname_des = trim($row_des_cat['catname_des']);
        }
        return $catname_des;
    }

    function curlCall($url,$params ='',$post=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($post == 1){
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		
		$resultString = curl_exec($ch);
		curl_close($ch);
		$resultString = trim($resultString);
		return  $resultString;
	}
}
?>
