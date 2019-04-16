<?php

class movie_timing_class extends DB{
	
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	
	function __construct($params)
	{
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim($params['data_city']);
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_die_message($message,1));
            die();
        }
        if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		if(trim($module)==''){
			$message = "Module is blank";
			echo json_encode($this->send_die_message($message,1));
			die();
		}
		
		$this->parentid  	= trim($parentid);
		$this->data_city 	= trim($data_city);
		$this->module  	  	= trim($module);
		
		$this->setServers();
	}
	
	function setServers(){
		
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->is_remote = 'REMOTE';
		}
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		
	}
	
	function get_today_timings(){
		
		$today 			 = date('Y-m-d');
		$array_fetch_res = array();
		
		$selTimings  = "SELECT GROUP_CONCAT(TIME_FORMAT(movie_timings,'%I:%i %p') SEPARATOR ', ') AS timing,movie_date,index_mv,category_name, catid FROM db_iro.tbl_movie_timings WHERE parentid='".$this->parentid."' AND movie_date >= '".$today." 00:00:00' AND movie_date <= '".$today." 23:59:59' GROUP BY index_mv,movie_date,category_name";
		$resDetails  = parent::execQuery($selTimings, $this->conn_iro);
		 $count      = mysql_num_rows($resDetails);
		if($resDetails && mysql_num_rows($resDetails) > 0){
			
			$i = 1;
			while($row_fetch = mysql_fetch_assoc($resDetails)){
				$array_fetch_res['data'][$row_fetch['category_name']]['category_name'] = $row_fetch['category_name'];
				 $array_fetch_res['data'][$row_fetch['category_name']]['movie_date'] = $row_fetch['movie_date'];
				 $array_fetch_res['data'][$row_fetch['category_name']]['moive_timings'] = $row_fetch['timing'];
				 $i++;	 
			}	  
			
			$array_fetch_res['error']['code']=0;
			$array_fetch_res['count']=$count;

			return $array_fetch_res;
			
		}else{
			$message = "No timings for today";
			echo json_encode($this->send_die_message($message,1));
            die();
		}	
		
	}
	
	private function send_die_message($msg,$errorCode)
	{
		$die_msg_arr['data'] = array();
		$die_msg_arr['error']['code'] = $errorCode;
		$die_msg_arr['error']['msg'] = $msg;
		return $die_msg_arr;
	}
}
?>
