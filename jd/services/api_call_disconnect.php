<?
require_once("../config.php");

class callDisconnect extends DB{
    var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
    var $remote     = 0;
    function __construct($params)
	{
        $empcode    =   trim($params['empcode']);
        $data_city  =   trim($params['data_city']);

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
        $this->setServers();
    }
    public function processTime(){
        $retVal     = '';
        $retVal     =   $this->getEmpData();
        $resData    =   array();
        $resData    =   json_decode($retVal,true);

        if($this->remote != 1 ){
            if($resData['data'][0]['team_type']=='S' && strtolower($resData['data'][0]['status'])=='active' && $resData['data'][0]['city_type']==1){
                $time	=	date('Y/m/d H:i:s');	

                $url = "http://192.168.55.105:8082/publish";
                $data = '{"topic":"disposition_mandate.call.disconnect","args":[{"empcode":"'.$this->empcode.'","timer":"120"}]}';         
                //$result	=	$this->curLogs($url, $data);

                if($value['isConnected'] == 0){
                    $stop_data	=	'{"topic":"call.disconnect.timeroff","args":[{"empcode":"'.$this->empcode.'"}]}';
                    $stop_result	=	$this->curLogs($url, $stop_data);
                    if($stop_result['curl_errno'] == 0){
                        $upd_query		=	"UPDATE tbl_timer_status SET isConnected=1 WHERE empcode = '".$this->empcode."'";
                        $con			=	parent::execQuery($upd_query,$this->tme_jds);

                        $result			=	$this->curLogs($url, $data);
                        
                        if($result['curl_errno'] == 0){                           
                            $log_query	=	"INSERT INTO tbl_auto_wrapup_log SET empcode 	=	'".$this->empcode."',
                                                                                 timerStart	=	'".$time."',
                                                                                 updated_on	= 	'".$time."'";
                            $con_log	=	parent::execQuery($log_query,$this->tme_jds);
                            
                            echo json_encode($result);                            
                        }
                        else{
                            echo json_encode("0");
                        }
                        
                    } else {
                        echo json_encode("0");
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
	}
    private function sendDieMsg($msg){
		$res_arr = array();
		$res_arr['errorCode']	=	1;
		$res_arr['errorMsg']	    =   $msg;
		return $res_arr;
	}
}

$obj_timer = new callDisconnect($_REQUEST);
echo $obj_timer->processTime();

?>
