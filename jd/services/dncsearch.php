<?php
require_once('../config.php');

class dncsearch extends DB{
    var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
    public function __construct($params){
        if((isset($params['phonenum'])) && (!empty($params['phonenum'])))
        {   
            $data_city = $params['data_city'];
            // if($data_city==''){
            //     echo json_encode($this->sendResponse(1,"data city is blank"));
            //     die;
            // }            
            $this->data_city = $data_city;
            $this->setServers();
            $searchkey = trim(urldecode($params['phonenum']));
            $searchkey = str_replace(",","|",$searchkey);

            ## Call the Search Function
            #############################
            if(!preg_match("/jdsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoftware.com/i", $_SERVER['HTTP_HOST']) && !preg_match("/blrsoft.com/i", $_SERVER['HTTP_HOST'])){
                $searchresult = $this->findDNDNumbersLive($searchkey);
            }else{
                $searchresult = $this->findDNDNumbersDev($searchkey);
            }
            ## Results
            ###########

            $output = json_encode($searchresult);
            echo $output;
        }else{
            echo json_encode($this->sendResponse(1,"Invalid Param Passed"));
        }
    }
    private function setServers()
	{	
		GLOBAL $db;		
		// $conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		$this->conn_idc   		= $db['mumbai']['idc']['master'];	
	}
    private function findDNDNumbersLive($searchkey)
    {
        $request_num_arr = array();
        $request_num_arr = explode("|",$searchkey);
        
        if(count($request_num_arr)>0)
        {

            $final_value = array();
            include_once('../library/sphinxapi.php');

            $cl = new SphinxClient();
            $cl->SetServer('192.168.12.14',3312);

            $cl->SetMatchMode(SPH_MATCH_EXTENDED);
            $cl->SetArrayResult(true);
            assert_options(ASSERT_WARNING, 0);
            $cl->SetLimits(0, 750);

            $cl->SetRankingMode(SPH_RANK_PROXIMITY);

            # Exact Match OR Exac Ignore Match OR without space match
            $cl->SetMatchMode(SPH_MATCH_EXTENDED2);

            #### DNDNumber Starts Here

            $sphinxQry = '@dndnumber ^'.$searchkey.'$';
            $cl->AddQuery($sphinxQry, 'main');


            $res = $cl->RunQueries();
            //print"<pre>";print_r($res);
            
            
            if($res===false )
            {
                $msg_arr['error']['code'] 	= 1;
                $msg_arr['error']['msg'] 	= "Query Failed : ".$cl->GetLastError();
                return $msg_arr;
            }
            else
            {
                /*if($cl->GetLastWarning())
                        print "WARNING: " . $cl->GetLastWarning() . "\n\n";*/
                $matched_num_arr = array();
                $currentdate = date("Y-m-d H:i:s");
                if (count($res)>0)
                {
                    foreach($res as $key => $rest)
                    {
                        if(count($rest["matches"]) )
                        {
                            foreach($rest["matches"] as $docinfo )
                            {
                                foreach ($rest["attrs"] as $attrname => $attrtype )
                                {
                                    $dndvalue = $docinfo["attrs"]['dndnumber'];
                                    if(in_array($dndvalue,$request_num_arr))
                                    {
                                        $dndnumber	= trim($docinfo['attrs']['dndnumber']);
                                        $safe_till	= trim($docinfo['attrs']['safe_till']);
                                        $is_safe 	= intval($docinfo['attrs']['is_safe']);
                                        $is_deleted	= intval($docinfo['attrs']['is_deleted']);
                                        
                                        if(($is_safe == 1) && ($is_deleted == 0) && ($safe_till >= $currentdate)){
                                            $status = "NonDND";
                                        }else if(($is_safe == 1) && ($is_deleted == 0) && ($safe_till < $currentdate)){
                                            $status = "DND";
                                        }else if(($is_safe == 0) && ($is_deleted == 1)){
                                            $status = "NonDND";
                                        }else if(($is_safe == 0) && ($is_deleted == 0)){
                                            $status = "DND";
                                        }else{
                                            $status = "NonDND";
                                        }
                                        $final_value[$dndvalue]['status'] 		= $status;
                                        $final_value[$dndvalue]['safe_till']  	= $safe_till;
                                        $final_value[$dndvalue]['is_safe']    	= $is_safe;
                                        $final_value[$dndvalue]['is_deleted'] 	= $is_deleted;
                                        $final_value[$dndvalue]['found'] 	  	= 1;
                                        
                                        $matched_num_arr[] = $dndvalue;
                                    }
                                }
                            }
                        }
                    }
                }
                if(count($matched_num_arr)>0){
                    $matched_num_arr = array_unique($matched_num_arr);
                }
                $no_data_arr = array();
                $no_data_arr = array_diff($request_num_arr,$matched_num_arr);
                if(count($no_data_arr)>0){
                    $no_data_arr = array_unique($no_data_arr);
                    foreach($no_data_arr as $mobilenum){
                        $final_value[$mobilenum]['status'] = "NonDND";
                        $final_value[$mobilenum]['found']   = 0;
                    }
                }
                $final_value['error']['code'] 	= 0;
                return $final_value;
            }
            
        }
        else
        {
            $msg_arr['error']['code'] 	= 1;
            $msg_arr['error']['msg'] 	= "Invalid Param Passed";
            return $msg_arr;
        }

    }

    private function findDNDNumbersDev($searchkey){
        session_start();
        ob_start();
        // require_once("../library/config.php" );
        // require_once("../common/Serverip.php" );
        // include_once(APP_PATH."library/path.php");
        
       
        $request_num_arr = array();
        $request_num_arr = explode("|",$searchkey);
        if(count($request_num_arr)>0)
        {
            $matched_num_arr = array();
            $currentdate = date("Y-m-d H:i:s");
            foreach($request_num_arr as $contact_number) {
                $sqlFetchDNDNumber	= "SELECT dndnumber, is_safe, safe_till, is_deleted FROM dnc.dndlist WHERE dndnumber = '".$contact_number."'";
                $resFetchDNDNumber	= parent::execQuery($sqlFetchDNDNumber,$this->conn_idc);
                if($resFetchDNDNumber && parent::numRows($resFetchDNDNumber)){
                    $row_dndnumber 		= parent::fetchData($resFetchDNDNumber);
                    
                    $dndvalue	= trim($row_dndnumber['dndnumber']);
                    $safe_till	= trim($row_dndnumber['safe_till']);
                    $is_safe 	= intval($row_dndnumber['is_safe']);
                    $is_deleted	= intval($row_dndnumber['is_deleted']);
                    
                    if(($is_safe == 1) && ($is_deleted == 0) && ($safe_till >= $currentdate)){
                        $status = "NonDND";
                    }else if(($is_safe == 1) && ($is_deleted == 0) && ($safe_till < $currentdate)){
                        $status = "DND";
                    }else if(($is_safe == 0) && ($is_deleted == 1)){
                        $status = "NonDND";
                    }else if(($is_safe == 0) && ($is_deleted == 0)){
                        $status = "DND";
                    }else{
                        $status = "NonDND";
                    }
                    $final_value[$dndvalue]['status'] 		= $status;
                    $final_value[$dndvalue]['safe_till']  	= $safe_till;
                    $final_value[$dndvalue]['is_safe']    	= $is_safe;
                    $final_value[$dndvalue]['is_deleted'] 	= $is_deleted;
                    $final_value[$dndvalue]['found'] 	  	= 1;
                    
                    $matched_num_arr[] = $dndvalue;
                }
            }
            if(count($matched_num_arr)>0){
                $matched_num_arr = array_unique($matched_num_arr);
            }
            $no_data_arr = array();
            $no_data_arr = array_diff($request_num_arr,$matched_num_arr);
            if(count($no_data_arr)>0){
                $no_data_arr = array_unique($no_data_arr);
                foreach($no_data_arr as $mobilenum){
                    $final_value[$mobilenum]['status'] = "NonDND";
                    $final_value[$mobilenum]['found']   = 0;
                }
            }
            $final_value['error']['code'] 	= 0;
            return $final_value;
        }
        else
        {
            $msg_arr['error']['code'] 	= 1;
            $msg_arr['error']['msg'] 	= "Invalid Param Passed";
            return $msg_arr;
        }
    }

    private function sendResponse($err,$msg){
        $msg_arr['error']['code'] 	= $err;
        $msg_arr['error']['msg'] 	= $msg;
        return $msg_arr;
    }
}

$dncsearch_obj =  new dncsearch($_REQUEST);

?>
