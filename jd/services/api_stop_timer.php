<?
require_once("../config.php");

class stopTimer extends DB{
    var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
    var $remote     = 0;
    function __construct($params)
	{
        $empcode    =   trim($params['empcode']);
        $data_city  =   trim($params['data_city']);

        $parentid       =   trim($params['parentid']);
        $empname        =   trim($params['empname']);
        $disposition    =   trim($params['disposition']);

        if(!isset($empcode) || (isset($empcode) && $empcode == "")) {          
            $msg = "Please send employee code.";
            echo json_encode($this->sendDieMsg($msg));
            die;
        }
        else if($data_city==''){
            $msg = "data city is blank.";
            echo json_encode($this->sendDieMsg($msg));
            die;
        }
        $this->empcode      = $empcode;
        $this->data_city    = $data_city;
        $this->parentid     = $parentid;
        $this->empname      = $empname;
        $this->disposition  = $disposition;

        $this->setServers();
    }
    public function processTime(){
        $retVal     = '';
        $retVal     =   $this->getEmpData();
        $resData    =   array();
        $resData    =   json_decode($retVal,true);

        if($this->remote == 1 ){
            $time	=	date('Y/m/d H:i:s');	

            $url = "http://192.168.55.105:8082/publish";
            $data = '{"topic":"call.disconnect.timeroff","args":[{"empcode" : "'.$this->empcode.'"}]}';          
            $result	=	$this->curLogs($url, $data);

            if($result['curl_errno'] == 0){                   
                $sel_query	=	"SELECT timerStart FROM tme_jds.tbl_auto_wrapup_log WHERE empcode 	=	'".$this->empcode."' ORDER BY updated_on DESC LIMIT 1";
                $con_sel	=	parent::execQuery($sel_query,$this->tme_jds);

                $start_time	=	parent::fetchData($con_sel);
                if(strtotime($start_time['timerStart']) > 0){                    
                    $warpup_time    =	strtotime($time) - strtotime($start_time['timerStart']);
                    
                    $log_query	=	"INSERT INTO tbl_auto_wrapup_log SET parentid 	=	'".$this->parentid."', 
                                                                        empcode 	=	'".$this->empcode."',
                                                                        empname 	=	'".$this->empname."',
                                                                        timerEnd	=	'".$time."',
                                                                        Disposition=	'".$this->disposition."',
                                                                        wrapupTime	=	'".$warpup_time."',
                                                                        updated_on	= 	'".$time."'";
                    $con_log	=	parent::execQuery($log_query,$this->tme_jds);
                }
                    
                $insert1	=	"INSERT INTO tbl_timer_status 
                                    SET 
                                empcode		=	'".$this->empcode."',
                                isConnected	=	'1'
                                    ON DUPLICATE KEY UPDATE
                                isConnected	=	'1'";

                $result_con	=	parent::execQuery($insert1,$this->tme_jds);
                if($result_con == 1){
                    return json_encode("1");
                }
            }
            else  if($result['curl_errno'] == 28){
                $insert2	=	"INSERT INTO tbl_timer_status
                                    SET 
                                    empcode		=	'".$this->empcode."',
                                    isConnected	=	'0'
                                        ON DUPLICATE KEY UPDATE
                                    isConnected	=	'0'";
                
                $result_con1	=	parent::execQuery($insert2,$this->tme_jds);
                if($result_con1 == 1){
                    return json_encode("2");
                }
            }
            else{
                $insert3	=	"INSERT INTO tbl_timer_status 
                                    SET 	
                                    empcode		=	'".$this->empcode."',
                                    isConnected	=	'0'
                                    ON DUPLICATE KEY UPDATE
                                    isConnected	=	'0'";
                $result_con2	=	parent::execQuery($insert3,$this->tme_jds);
                if($result_con2 == 1){
                    return json_encode("0");
                }
            }
        }
        else{
           //echo "in main";
           //echo "<pre>";print_r($this->tme_jds);
            //For Main city data
            $time	=	date('Y/m/d H:i:s');

            $url = "http://192.168.55.105:8082/publish";
            $data = '{"topic":"call.disconnect.timeroff","args":[{"empcode" : "'.$this->empcode.'"}]}';

            if($resData['data'][0]['team_type']=='S' && strtolower($resData['data'][0]['status'])=='active' &&  ($resData['data'][0]['city_type']==1 || ($resData['data'][0]['city_type']==2 &&  strtolower($resData['data'][0]['city'])=='pune'))) {
                $result	=	$this->curLogs($url, $data);

                if($result['curl_errno'] == 0){                                         
                    $sel_query	=	"SELECT timerStart FROM tbl_auto_wrapup_log WHERE empcode 	=	'".$this->empcode."' ORDER BY updated_on DESC LIMIT 1";
                    $con_sel	=	parent::execQuery($sel_query,$this->tme_jds);

                    $start_time	=	parent::fetchData($con_sel);

                    if(strtotime($start_time['timerStart']) > 0){                        
                        $warpup_time    =	strtotime($time) - strtotime($start_time['timerStart']);
                        
                        $log_query	=	"INSERT INTO tbl_auto_wrapup_log SET parentid 	=	'".$this->parentid."', 
                                                                            empcode 	=	'".$this->empcode."',
                                                                            empname 	=	'".$this->empname."',
                                                                            timerEnd	=	'".$time."',
                                                                            Disposition=	'".$this->disposition."',
                                                                            wrapupTime	=	'".$warpup_time."',
                                                                            updated_on	= 	'".$time."'";
                        $con_log	=	parent::execQuery($log_query,$this->tme_jds);
                    }
                        
                    $insert1	=	"INSERT INTO tbl_timer_status 
                                        SET empcode		=	'".$this->empcode."',
                                            isConnected	=	'1'
                                        ON DUPLICATE KEY UPDATE 
                                            isConnected	=	'1'";
                    $result_con	=	parent::execQuery($insert1,$this->tme_jds);
                    if($result_con == 1){
                        return json_encode("1");
                    }
                }
                else if($result['curl_errno'] == 28){
                    $insert2	=	"INSERT INTO tbl_timer_status 
                                        SET 	empcode		=	'".$this->empcode."',
                                                isConnected	=	'0'
                                        ON DUPLICATE KEY UPDATE 
                                                isConnected	=	'0'";
                    
                    $result_con1	=	parent::execQuery($insert2,$this->tme_jds);
                    if($result_con1 == 1){
                        return json_encode("2");
                    }
                }
                else{
                    $insert3	=	"INSERT INTO tbl_timer_status 
                                        SET empcode		=	'".$this->empcode."',
                                            isConnected	=	'0'
                                        ON DUPLICATE KEY UPDATE 
                                            isConnected	=	'0'";
                    $result_con2	=	parent::execQuery($insert3,$this->tme_jds);
                    if($result_con2 == 1){
                        return json_encode("0");
                    }
                }                
            }
        }
    }

    private function getEmpData(){
        define("HR_SERVICE_PATH","http://192.168.20.237:8080/");
        
        $postArray                  =   array();
        $postArray['empcode']   	= $this->empcode;
        $postArray['textSearch'] 	= 4;
        $auth_token                 =  md5("Q-ZedAP^I76A%'>j0~'z]&w7bR64{s");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,HR_SERVICE_PATH.'api/getEmployee_xhr.php');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,  CURLOPT_POSTFIELDS , json_encode($postArray));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Content-Length: '.strlen(json_encode($postArray)),'HR-API-AUTH-TOKEN:'.$auth_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $retVal = curl_exec($ch);
        //$resData	=	json_decode($retVal,true);
        return $retVal;
    }

    private function curLogs($url,$data)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_TIMEOUT,3);
        $info['data'] = curl_exec($ch);
        $info['curl_errno'] = curl_errno($ch);
        $info['curl_error'] = curl_error($ch);
        curl_close($ch);
        return $info;
    }
    private function setServers(){
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->tme_jds    		= $db[$conn_city]['tme_jds']['master'];
              
        if($conn_city == 'remote'){
            $this->remote = 1;
        }
        /* 
		$this->conn_local_slave  		= $db[$conn_city]['d_jds']['slave'];		
		$this->conn_idc 		= $db[$conn_city]['idc']['master'];
		$this->conn_dnc   		= $db['dnc']; */
	}
    private function sendDieMsg($msg){
		$res_arr = array();
		$res_arr['errorCode']	=	1;
		$res_arr['errorMsg']	    =   $msg;
		return $res_arr;
	}
}

$obj_timer = new stopTimer($_REQUEST);
echo $obj_timer->processTime();

?>
