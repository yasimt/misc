<?php
class geoCodeClass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	var $omni_duration;	
	function __construct($params)
	{		
		$this->params = $params;		
		if($this->params['is_remote'] == 'REMOTE')
		{
			$this->is_split = FALSE;	 // when split table goes live then make it TRUE		
		}
		else
		{
			$this->is_split = FALSE;			
		}

		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = $this->params['action']; 
		
		if($this->action!=1){
		if(trim($this->params['parentid']) == "")
		{
			if( $this->action!=4 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Parentid Missing";
				echo json_encode($result_msg_arr);exit;
		}
		}
		else{

			$this->parentid  = $this->params['parentid']; 
		}
		
		
		if(trim($this->params['docid']) == "")
		{
				if($this->action!=3 && $this->action!=4 && $this->action!=5 ){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Doc Id Missing";
				echo json_encode($result_msg_arr);exit;
			}
		}
		else
			$this->docid  = $this->params['docid']; 
		}

		if(trim($this->params['pincode']) != "")
		{
				$this->pincode  = $this->params['pincode']; 
		}
		
		if(trim($this->params['org_latitude']) != "")
		{
				$this->org_latitude  = $this->params['org_latitude']; 
		}
		if(trim($this->params['org_longitude']) != "")
		{
				$this->org_longitude  = $this->params['org_longitude']; 
		}

		if(trim($this->params['pin_latitude']) != "")
		{
				$this->pin_latitude  = $this->params['pin_latitude']; 
		}
		if(trim($this->params['pin_longitude']) != "")
		{
				$this->pin_longitude  = $this->params['pin_longitude']; 
		}
		if(trim($this->params['radius']) != "")
		{
				$this->radius  = $this->params['radius']; 
		}
		if(trim($this->params['stage']) != "")
		{
				$this->stage  = $this->params['stage']; 
		}
		if(trim($this->params['version']) != "")
		{
				$this->version  = $this->params['version']; 
		}
		if(trim($this->params['usercode']) != "")
		{
				$this->usercode  = $this->params['usercode']; 
		}
		if(trim($this->params['checkval']) != "")
		{
				$this->usercode  = $this->params['checkval']; 
		}
		if(trim($this->params['newpincode']) != "")
		{
				$this->usercode  = $this->params['newpincode']; 
		}
		



		if(trim($this->params['module']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->module  = $this->params['module']; 


		if(trim($this->params['debug']) != ""){
			$this->debug  = $this->params['debug'];
		}
		

		if(trim($this->params['data_city']) == "")
			{
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Data City Missing";
				echo json_encode($result_msg_arr);exit;
			}
			else
				$this->data_city  = $this->params['data_city']; 
			
		$this->data_city_cm = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		
		$status=$this->setServers();
		$this->companyClass_obj  = new companyClass();
		if($status==-1)
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Module Missing";
			return $result_msg_arr;
		}
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;

		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		
		$this->conn_idc = $db[$data_city]['idc']['master'];
		$this->conn_jda = $db['jda']; 
		
		switch(strtolower($this->module))
		{
			case 'cs':
			$this->conn_temp = $db[$data_city]['d_jds']['master'];
			$this->conn_main = $db[$data_city]['d_jds']['master'];
			$this->conn_finance_temp = $db[$data_city]['fin']['master'];
			$this->conn_finance = $db[$data_city]['fin']['master'];
			
			$this->conn_iro = $db[$data_city]['iro']['master'];
			break;
			case 'tme':
		
			$this->conn_temp = $db[$data_city]['tme_jds']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_iro = $db[$data_city]['iro']['master'];
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){	
				$this->mongo_tme = 1;
			}
			break;
			case 'me':

			$this->conn_temp = $db[$data_city]['idc']['master'];
			$this->conn_main = $db[$data_city]['idc']['master'];
			$this->conn_finance_temp = $this->conn_temp;
			$this->conn_finance = $db[$data_city]['fin']['master'];
			$this->conn_iro = $db[$data_city]['iro']['master'];
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
			break;
			default:
			return -1;
			break;
		}

	}

	function checkDistanceWithPincode(){
		
		if(trim($this->org_latitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Latitude Missing";
			return $result_msg_arr;
		}

		if(trim($this->org_longitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Longtitude Missing";
			return $result_msg_arr;
		}
		
			$getradius = "SELECT latitude,longitude,new_radius,pincode FROM  d_jds.geocode_pincode_master WHERE pincode='".$this->pincode."'";
				$rdres = parent::execQuery($getradius, $this->dbConIro); 
			if($rdres && mysql_num_rows($rdres)>0){
				$rowrad=mysql_fetch_assoc($rdres);
				$this->radius = $rowrad['new_radius'];
				$lat = $rowrad['latitude'];
				$lon = $rowrad['longitude'];
			}
		

		$sql="SELECT d_jds.fn_distance_calc(".$this->org_latitude.",".$this->org_longitude.",".$lat.",".$lon.",".$this->radius.") as distance_in_km";
		
		$sqlres = parent::execQuery($sql, $this->dbConIro); 
		if($sqlres && mysql_num_rows($sqlres)>0){
			while($row=mysql_fetch_assoc($sqlres)){ 
				$distance=$row['distance_in_km'];

			} 
		}
		if($distance>$this->radius){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Reject GeoCode";
			$result_msg_arr['data']['distance_in_km'] = $distance;
			$result_msg_arr['data']['pincode'] = $this->pincode;
			return $result_msg_arr;
		}
		else{
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = "Update GeoCode";
			$result_msg_arr['data']['distance_in_km'] = $distance;
			$result_msg_arr['data']['pincode'] = $this->pincode;
			return $result_msg_arr;
		}

		
	}
	function getPincodeGeocode(){

		$pincodegeo=array();
		$geocode_accuracy_level=0;
		if($this->stage==0){
			$pincode='';
			
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "pincode,geocode_accuracy_level";
				$row = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				$sql="select pincode,geocode_accuracy_level from db_iro.tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";

				$sqlres = parent::execQuery($sql, $this->conn_temp); 
				if($sqlres && mysql_num_rows($sqlres)>0){
					$row=mysql_fetch_assoc($sqlres);
				}
			}
			$pincode=$row['pincode'];
			$geocode_accuracy_level=$row['geocode_accuracy_level'];
						
			$sql_rad="SELECT * FROM d_jds.geocode_pincode_master where pincode='".$pincode."'";

			$sqlres_rad = parent::execQuery($sql_rad, $this->conn_iro); 
			if($sqlres_rad && mysql_num_rows($sqlres_rad)>0){
				while($row_rad=mysql_fetch_assoc($sqlres_rad)){ 
						$this->radius=$row_rad['new_radius'];

				}
			}


			if($geocode_accuracy_level==1 ||$geocode_accuracy_level=='1'){

					
					$result_msg_arr['error']['code'] = 2;
					$result_msg_arr['error']['msg'] = "No Need For Updatiing - Already At Building Level"; 
					$this->reqparams['photo_capture']='Not Needed';
					$this->reqparams['geocode_capture']='Not Needed';
					//$this->createLog($this->reqparams,$result_msg_arr,0);
					return $result_msg_arr;
			}
			require_once('location_class.php');
			global $params;
			$params['rquest']='get_geocode';
			$params['type']='pincode';
			$params['pincode']=$pincode;
			$location_class_obj  	= new location_class($params);
			$location_info_arr 		= $location_class_obj->fetch_details();
			
			if(!is_array($location_info_arr)) 
			$location_info_arr=json_decode($location_info_arr,1); 

			if($location_info_arr['error']['code']!=1 &&$location_info_arr['error']['code']!='1' ){
				$pincodegeo['latitude']=$location_info_arr['result']['latitude'];
				$pincodegeo['longitude']=$location_info_arr['result']['longitude'];
				$pincodegeo['pincode']=$pincode;
				$pincodegeo['error']['code']=0;

			}
			else{
				
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Sorry Pincode Geo Code Not Found";
				return $result_msg_arr;
			}

		}else{

			
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Pincode Not Found";
			return $result_msg_arr;
		}
		return $pincodegeo;
		
	}

	function checkDistance(){
		if(trim($this->org_latitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Latitude Missing";
			return $result_msg_arr;
			
		}

		if(trim($this->org_longitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Longtitude Missing";
			return $result_msg_arr;
		}
		if(trim($this->pin_latitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Comparison Latitude Missing";
			return $result_msg_arr;
		}

		if(trim($this->pin_longitude)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Comparison Longtitude Missing";
			return $result_msg_arr;
		}
		if(trim($this->radius)==''){
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Radius Missing";
			return $result_msg_arr;
		}
		$sql="SELECT d_jds.fn_distance_calc(".$this->org_latitude.",".$this->org_longitude.",".$this->pin_latitude.",".$this->pin_longitude.",".$this->radius.") as pinarea";
		$sqlres = parent::execQuery($sql, $this->dbConIro); 
		$distance='';
		if($sqlres && mysql_num_rows($sqlres)>0){
			while($row=mysql_fetch_assoc($sqlres)){ 
				$distance=$row['pinarea'];

			} 
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['data']['distance'] = $distance;
			return $result_msg_arr;
		}
		$result_msg_arr['error']['code'] = 1;
		$result_msg_arr['error']['msg'] = 'Not Able To Calucate Distance';
		return $result_msg_arr;


	}

	function getBestLatLong(){
		$sqldocid="select * from  db_iro.tbl_id_generator where parentid='".$this->parentid."'";
		$docid='';
		$resdocid = parent::execQuery($sqldocid, $this->dbConIro); 
		if($resdocid && mysql_num_rows($resdocid)>0){

			while($rowdocid=mysql_fetch_assoc($resdocid)){
		
				$docid=$rowdocid['docid'];
			}
		}         
		else{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Docid Not Found';
			return $result_msg_arr;
		}
		$this->docid=$docid;
		$this->radius=10;
		$this->stage=0;
		$this->module="ME";
		$this->reqparams['geocode_capture']='';
		$this->reqparams['photo_capture']='';
		$sql="SELECT * FROM online_regis.tbl_capturegeocode_info   where parentid='".$this->parentid."' order by updated_on desc limit 1";
		$res = parent::execQuery($sql, $this->conn_idc);
		$lat='';
		$lon='';
		$checked=false;
		$reject_arr=array();
		if($res && mysql_num_rows($res)>0){
			while($row=mysql_fetch_assoc($res)){
				 $lat=$row['lat'];
				 $lon=$row['lng'];

				if($lat!='' && $lon!=''){
					$this->org_latitude=$lat;
					$this->org_longitude=$lon;
					$this->reqparams['geocode_capture']=json_encode(array('lat'=>$lat,'lon'=>$lon));
					$ret_param=array();
					$curl_res=$this->checkDistanceWithPincode();
					if($this->debug==1){
						echo 'Geo Code Capture Checked';
						echo "\nGeo Code Capture Distance Api Params Passed \nLat:".$this->org_latitude."\nLong:".$this->org_longitude."\nradius:".$this->radius."\nStage:".$this->stage."\nModule:".$this->module."\n";
						echo "\n Geo Code Capture Distance Api Result \n";
						print_r($curl_res);
					}
					if($curl_res['error']['code']=='0'){
						require_once('location_class.php');
						global $params;
						$params['data_city']=$this->data_city;
						$params['parentid']=$this->parentid;
						$params['latitude']=$this->org_latitude;
						$params['longitude']=$this->org_longitude;
						$params['geocode_accuracy_level']=1;
						$params['rquest']='map_pointer_flag';
						$location_class_obj  	= new location_class($params);
						$location_info_arr 		= $location_class_obj->fetch_details();
						$checked=true;
						$ret_param['latitude']					= $lat;
						$ret_param['longitude']					= $lon;
						$ret_param['distance']					= $curl_res['data']['distance_in_km'];
						$ret_param['pincode']					= $curl_res['data']['pincode'];
						$ret_param['accepted_from']				= 'Geo Code Capture';
						$ret_param['map_pointer_flags']			=$location_info_arr['result']['map_pointer_flags'];
						$ret_param['flags']					=$location_info_arr['result']['flags'];
						$this->reqparams['pincode']=$curl_res['data']['pincode'];
						$this->reqparams['photo_capture']='Not Required';
						$result_msg_arr['error']['code'] = 0;
						$result_msg_arr['error']['msg'] = 'Accept Geo Code';
						$result_msg_arr['data'] = $ret_param;
						if($this->debug==1){
							echo "\n"."Accepting from geo code capture\n";
						}
						$this->createLog($this->reqparams,$result_msg_arr,1);
						return $result_msg_arr;			

					}
					else if($curl_res['error']['code']=='2')
					{
						$result_msg_arr['error']['code'] = 2;
						$result_msg_arr['error']['msg'] = 'Already At Building Level';
						$this->reqparams=array();
						$this->createLog($this->reqparams,$result_msg_arr,2);
						return $result_msg_arr;
					}
					else{
							$reject_s_sarr['distance']=$curl_res['data']['distance_in_km'];
							$reject_s_sarr['pincode']=$curl_res['data']['pincode'];
							$reject_s_sarr['latitude']= $row['latitude'];
							$reject_s_sarr['longitude']= $row['longitude'];	
							$reject_s_sarr['reject_from']= 'Geo Code Capture';
							$reject_arr[]=$reject_s_sarr;
					}
					

				}
				else{
						$this->reqparams['geocode_capture']='Not Present';
				}
			}

		}
		if(!$checked){
			$sqlphoto="SELECT * FROM db_jda.tbl_catalogue_details  WHERE docid='".$docid."'";
			$res = parent::execQuery($sqlphoto, $this->conn_idc);
			$lat='';
			$lon='';
			$closest_arr=array();
			$photo_arr=array();
			$closest_arr['distance']=100;
			if($res && mysql_num_rows($res)>0){
				while($row=mysql_fetch_assoc($res)){
					$this->org_latitude='';
					$this->org_longitude='';
					if($row['latitude']!=0 && $row['longitude']!=0){
						$this->org_latitude=$row['latitude'];
						$this->org_longitude=$row['longitude'];	
						$photo_arr[]=array('lat'=>$row['latitude'],'lon'=>$row['longitude']); 
					}
					else{
						$photo_arr[]=array('lat'=>$row['latitude'],'lon'=>$row['longitude']); 
						if($this->debug==1){
							echo "Photo Capture Checked\n";
							echo "No Latitude and Long\n";
							
						}
						continue;

					}
					$curl_res=$this->checkDistanceWithPincode();
					if($this->debug==1){
						echo 'Photo Capture Checked';
						echo "\nPhoto Capture Distance Api Params Passed \nLat:".$this->org_latitude."\nLong:".$this->org_longitude."\nradius:".$this->radius."\nStage:".$this->stage."\nModule:".$this->module."\n";
						echo "\n Photo Capture Distance Api Result \n";
						print_r($curl_res);
					}

					if($curl_res['error']['code']=='0'){
						$checked=true;
							
						if($curl_res['data']['distance_in_km']<$closest_arr['distance']){

							$closest_arr['latitude']= $row['latitude'];
							$closest_arr['longitude']= $row['longitude'];	
							$closest_arr['distance']= $curl_res['data']['distance_in_km'];
							$closest_arr['pincode']= $curl_res['data']['pincode'];
						}


					}
					else if($curl_res['error']['code']=='2')
					{
						$result_msg_arr['error']['code'] = 2;
						$result_msg_arr['error']['msg'] = 'Already At Building Level';
						$this->reqparams=array();
						$this->createLog($this->reqparams,$result_msg_arr,2);
						return $result_msg_arr;
					}
					else{	
							
							$reject_s_sarr['distance']=$curl_res['data']['distance_in_km'];
							$reject_s_sarr['pincode']=$curl_res['data']['pincode'];
							$reject_s_sarr['latitude']= $row['latitude'];
							$reject_s_sarr['longitude']= $row['longitude'];	
							$reject_arr[]=$reject_s_sarr;
					}

				}
			
			}

		}
		if($checked){
			require_once('location_class.php');
			global $params;
			
			$params['latitude']=$closest_arr['latitude'];
			$params['longitude']=$closest_arr['longitude'];
			$params['geocode_accuracy_level']=1;
			$params['rquest']='map_pointer_flag';
			$location_class_obj  	= new location_class($params);
			$location_info_arr 		= $location_class_obj->fetch_details();
			$ret_param['latitude']					= $closest_arr['latitude'];
			$ret_param['longitude']					= $closest_arr['longitude'];
			$ret_param['distance']					= $closest_arr['distance'];
			$ret_param['pincode']					= $closest_arr['pincode'];
			$ret_param['map_pointer_flags']			=$location_info_arr['result']['map_pointer_flags'];
			$ret_param['flags']					    =$location_info_arr['result']['flags'];
			$ret_param['accepted_from']				= 'Photo Upload';
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['msg'] = 'Accept Geo Code';
			$result_msg_arr['data'] = $ret_param;
			$this->reqparams['photo_capture']=json_encode($photo_arr);
			$this->reqparams['pincode']=$closest_arr['pincode'];
			$this->createLog($this->reqparams,$result_msg_arr,1);
			return $result_msg_arr;
		}
		else{
			
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = 'Reject Geo Code';
			$result_msg_arr['data']['distance']	= ($reject_arr);
			$this->reqparams['photo_capture']=json_encode($photo_arr);

			$this->createLog($this->reqparams,$result_msg_arr,0);
			return $result_msg_arr;
		}	
	}
	function runProcess(){

		$sql="select * from tbl_geo_code_upt_log where accepted=0 ";
		$res = parent::execQuery($sql, $this->conn_idc); 
			$this->radius=10;
		if($res && mysql_num_rows($res)>0){
			while($row=mysql_fetch_assoc($res)){
				echo $this->parentid=$row['parentid'];
				echo "\n";
				if($row['geo_code_values']!=''){
					$geocode=json_decode($row['geo_code_values'],1);

					$this->org_latitude=$geocode['lat'];
					$this->org_longitude=$geocode['lon'];
					$curl_res=$this->checkDistanceWithPincode();
					if($curl_res['error']['code']=='1'){

						
						$reject_s_sarr['distance']=$curl_res['data']['distance_in_km'];
						$reject_s_sarr['pincode']=$curl_res['data']['pincode'];
						$reject_s_sarr['latitude']= $this->org_latitude;
						$reject_s_sarr['longitude']= $this->org_longitude;	
						$reject_arr=$reject_s_sarr;
						$result_msg_arr['error']['code'] = 1;
						$result_msg_arr['error']['msg'] = 'Reject Geo Code';
						$result_msg_arr['data']['distance']	= ($reject_arr);
						$upt="update tbl_geo_code_upt_log set accepted=0,result_sent='".json_encode($result_msg_arr)."' where parentid='".$this->parentid."' and request_time='".$row['request_time']."'";
						$res_up = parent::execQuery($upt, $this->conn_idc); 
					}
				}
				if($row['photo_upload_values']!='Not Required' && $row['photo_upload_values']!=''){
					$geocode=json_decode($row['photo_upload_values'],1);

					foreach ($geocode as $key => $value) {
						$this->org_latitude=$value['lat'];
						$this->org_longitude=$value['lon'];
						
						$curl_res=$this->checkDistanceWithPincode();
						print_r($curl_res);
						$closest_arr['distance']=100;
						$checked=false;
						if($curl_res['error']['code']=='0'){
							if($curl_res['data']['distance_in_km']<$closest_arr['distance']){
								$checked=true;
								$closest_arr['latitude']= $row['latitude'];
								$closest_arr['longitude']= $row['longitude'];	
								$closest_arr['distance']= $curl_res['data']['distance_in_km'];
								$closest_arr['pincode']= $curl_res['data']['pincode'];
							}


						}
						else if($curl_res['error']['code']=='2')
						{
							$result_msg_arr['error']['code'] = 2;
							$result_msg_arr['error']['msg'] = 'Already At Building Level';
							$upt="update tbl_geo_code_upt_log set accepted=2 where parentid='".$this->parentid."' and request_time='".$row['request_time']."'";
							$res_up = parent::execQuery($upt, $this->conn_idc); 
							continue;
						}
						else{
								$reject_s_sarr['distance']=$curl_res['data']['distance_in_km'];
								$reject_s_sarr['pincode']=$curl_res['data']['pincode'];
								$reject_s_sarr['latitude']= $this->org_latitude;
								$reject_s_sarr['longitude']=$this->org_longitude;
								$reject_arr[]=$reject_s_sarr;
								print_r($reject_arr);
						}
						if(!$checked){

							$result_msg_arr['error']['code'] = 1;
							$result_msg_arr['error']['msg'] = 'Reject Geo Code';
							$result_msg_arr['data']['distance']	= ($reject_arr);
							echo $upt="update tbl_geo_code_upt_log set accepted=0,result_sent='".json_encode($result_msg_arr)."',pincode_in_shadow='".$curl_res['data']['pincode']."' where parentid='".$this->parentid."' and request_time='".$row['request_time']."'";
							$res_up = parent::execQuery($upt, $this->conn_idc); 
						}
					}

				}
				

				if($curl_res['error']['code']=='1'){
					$upt="update tbl_geo_code_upt_log set accepted=0,pincode_in_shadow='".$curl_res['pincode']."' where parentid='".$this->parentid."' and request_time='".$row['request_time']."'";
					$res_up = parent::execQuery($upt, $this->conn_idc); 
				}
				if($curl_res['error']['code']=='2'){
					$upt="update tbl_geo_code_upt_log set accepted=2 where parentid='".$this->parentid."' and request_time='".$row['request_time']."'";
					$res_up = parent::execQuery($upt, $this->conn_idc); 
				}


			}
		}

	}
	function createLog($reqparams,$res,$status){
		if($this->action=='3'){
			$pincode='';
			$pinLat='';
			$pinLong='';

			 $sqlgetdata="SELECT * FROM tbl_uploadGeocodeDetails_log where parentid='".$this->parentid."' order by inserted_on DESC limit 1";
			$res_data = parent::execQuery($sqlgetdata, $this->conn_idc); 
			if($res_data && mysql_num_rows($res_data)>0){
				while($rowdata=mysql_fetch_assoc($res_data)){
					$pincode=$rowdata['pincode'];
					$pinLat=$rowdata['pinLat'];
					$pinLong=$rowdata['pinLong'];
					$pinRadius=$rowdata['pinRadius'];
					$upload_reason=$rowdata['upload_reason'];
				}

			}

			$accepted_from='-';
			$accepted=0;
			$distance='-';
			if($res['error']['code']=='0' || $res['error']['code']==0){
				$accepted_from=$res['data']['accepted_from'];
				$distance=$res['data']['distance'];
				$accepted=1;
			}
			$sql_ins_report_table = "INSERT INTO tbl_geocode_details set
			parentid ='".$this->parentid."',
			version ='".$this->version."',
			pincode ='".$pincode."',
			pin_lat='".$pinLat."',
			pin_lon ='".$pinLong."',
			pin_radius='".$pinRadius."',
			distance='".$distance."',
			geo_code_values='".$reqparams['geocode_capture']."',
			photo_upload_values='".$reqparams['photo_capture']."',
			not_uploaded_reason='".$upload_reason."',
			me_id='".$this->usercode."',
			accepted_from='".$accepted_from."',
			accepted='".$accepted."',
			inserted_on='".date('Y-m-d H:i:s')."'
			ON DUPLICATE KEY UPDATE
				pincode ='".$pincode."',
			pin_lat='".$pinLat."',
			pin_lon ='".$pinLong."',
			pin_radius='".$pinRadius."',
			distance='".$distance."',
			geo_code_values='".$reqparams['geocode_capture']."',
			photo_upload_values='".$reqparams['photo_capture']."',
			not_uploaded_reason='".$upload_reason."',
			me_id='".$this->usercode."',
			accepted_from='".$accepted_from."',
			accepted='".$accepted."',
			inserted_on='".date('Y-m-d H:i:s')."'
			";
			$res_idc = parent::execQuery($sql_ins_report_table, $this->conn_idc); 
		}
		$res=json_encode($res);
		if($this->action=='4')
			return;
			$sql_ins_log = "INSERT INTO tbl_geo_code_upt_log set
								parentid='".$this->parentid."',
								data_city='".$this->data_city."',
								geo_code_values='".$reqparams['geocode_capture']."',
								photo_upload_values='".$reqparams['photo_capture']."',
								radius_req='".$this->radius."',
								pincode_in_shadow='".$reqparams['pincode']."',
								result_sent='".$res."',
								request_time='".date('Y-m-d H:i:s')."',
								accepted='".$status."'";
			$res_idc = parent::execQuery($sql_ins_log, $this->conn_idc); 
	}
	function distanceWithTable(){
			if(trim($this->org_latitude)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Latitude Missing";
				return $result_msg_arr;
			}

			if(trim($this->org_longitude)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Longtitude Missing";
				return $result_msg_arr;
			}
			if(trim($this->pincode)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Pincode Missing";
				return $result_msg_arr;
			}
			
			$sql_rad="SELECT * FROM d_jds.geocode_pincode_master where pincode='".$this->pincode."'";
			$picode_geocode=array();
			$sqlres_rad = parent::execQuery($sql_rad, $this->conn_iro); 
			if($sqlres_rad && mysql_num_rows($sqlres_rad)>0){
				while($row_rad=mysql_fetch_assoc($sqlres_rad)){ 
						$this->radius=$row_rad['new_radius'];
						$picode_geocode['latitude']=$row_rad['latitude'];
						$picode_geocode['longitude']=$row_rad['longitude'];
				}
			}
 

			$sql="SELECT d_jds.fn_distance_calc(".$this->org_latitude.",".$this->org_longitude.",".$picode_geocode['latitude'].",".$picode_geocode['longitude'].",".$this->radius.") as distance_in_km";
			
			$sqlres = parent::execQuery($sql, $this->dbConIro); 
			if($sqlres && mysql_num_rows($sqlres)>0){
				while($row=mysql_fetch_assoc($sqlres)){ 
					$distance=$row['distance_in_km'];

				} 
			}
			if($distance>$this->radius){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Reject GeoCode";
				$result_msg_arr['data']['distance_in_km'] = $distance;
				$result_msg_arr['data']['pincode'] = $this->pincode;
				return $result_msg_arr;
			}
			else{
				$result_msg_arr['error']['code'] = 0;
				$result_msg_arr['error']['msg'] = "Allow GeoCode";
				$result_msg_arr['data']['distance_in_km'] = $distance;
				$result_msg_arr['data']['pincode'] = $this->pincode;
				return $result_msg_arr;
			}
	}
	
	
		function pincodeCheck(){
			if(trim($this->parentid)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Parentid Missing";
				return $result_msg_arr;
			}
			if(trim($this->data_city)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "City Missing";
				return $result_msg_arr;
			}
			
			if(trim($this->module)==''){
				$result_msg_arr['error']['code'] = 1;
				$result_msg_arr['error']['msg'] = "Module Missing";
				return $result_msg_arr;
			}
	
			if($this->mongo_flag == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_companymaster_generalinfo_shadow";
				$mongo_inputs['fields'] 	= "pincode,geocode_accuracy_level,latitude,longitude";
				$row = $this->mongo_obj->getData($mongo_inputs);
			}
			else
			{
				 $sql="select pincode,latitude,longitude,geocode_accuracy_level from tbl_companymaster_generalinfo_shadow where parentid='".$this->parentid."'";
				$sqlres = parent::execQuery($sql, $this->conn_temp); 
				if($sqlres && mysql_num_rows($sqlres)>0){
					$row=mysql_fetch_assoc($sqlres);
					
				}
			}
			$pincode_shadow=$row['pincode'];
			$shadowdata = $row;


			//$getpincode1="select pincode,latitude,longitude,geocode_accuracy_level from db_iro.tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
			//$respincode1 = parent::execQuery($getpincode1, $this->dbConIro); 
			$comp_params = array();
			$comp_params['data_city'] 	= $this->data_city;
			$comp_params['table'] 		= 'gen_info_id';		
			$comp_params['parentid'] 	= $this->parentid;
			$comp_params['fields']		= 'pincode,latitude,longitude,geocode_accuracy_level';
			$comp_params['action']		= 'fetchdata';
			$comp_params['page']		= 'geoCodeClass';
			$comp_params['skip_log']	= 1;

			$comp_api_arr	= array();
			$comp_api_res	=	$this->companyClass_obj->getCompanyInfo($comp_params);
			if($comp_api_res!=''){
				$comp_api_arr 	= json_decode($comp_api_res,TRUE);
			}
			if(!empty($comp_api_arr) && $comp_api_arr['errors']['code']=='0'){
				$checkgeo_main	= 	$comp_api_arr['results']['data'][$this->parentid];			
				$main_geo = $checkgeo_main['pincode'];
			}
			$maintbldata = $checkgeo_main;


			$getpincode="select pincode,latitude,longitude,geocode_accuracy_level from online_regis.tbl_checkGeocodes where parentid='".$this->parentid."'";
			$respincode = parent::execQuery($getpincode, $this->conn_temp); 
			if($respincode && mysql_num_rows($respincode)>0){
				$checkgeo_det=mysql_fetch_assoc($respincode);
			}

			$this->pincode = $shadowdata['pincode'];
			$this->org_latitude=$shadowdata['latitude'];
			$this->org_longitude=$shadowdata['longitude'];
			$curl_res_shadow=$this->checkDistanceWithPincode();

			
			
			$this->org_latitude=$maintbldata['latitude'];
			$this->org_longitude=$maintbldata['longitude'];
			$curl_res_main=$this->checkDistanceWithPincode();

			
			
			$this->org_latitude=$checkgeo_det['latitude'];
			$this->org_longitude=$checkgeo_det['longitude'];
			$curl_res_checkgeo=$this->checkDistanceWithPincode();


			if($main_geo != null && $main_geo != ''){
				if($main_geo != $pincode_shadow){
					if($curl_res_shadow['error']['code']=='0'){
						$result_msg_arr['shadow']['error']['code'] = 0;
						$result_msg_arr['shadow']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['shadow']['data']['pincode_shadow'] = $shadowdata;
						$result_msg_arr['shadow']['data']['pincode_geo'] = $main_geo;
					}else{
						$result_msg_arr['shadow']['error']['code'] = 1;
						$result_msg_arr['shadow']['error']['msg'] = "Reject GeoCode";
						$result_msg_arr['shadow']['data']['pincode_shadow'] = $shadowdata;
						$result_msg_arr['shadow']['data']['pincode_geo'] = $main_geo;
					}


					 if($curl_res_checkgeo['error']['code']=='0'){
						$result_msg_arr['checkgeo']['error']['code'] = 0;
						$result_msg_arr['checkgeo']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['checkgeo']['data']['pincode_chkgeo'] = $checkgeo_det;
						$result_msg_arr['checkgeo']['data']['pincode_geo'] = $main_geo;
					}else{
						$result_msg_arr['checkgeo']['error']['code'] = 1;
						$result_msg_arr['checkgeo']['error']['msg'] = "Reject GeoCode";
						$result_msg_arr['checkgeo']['data']['pincode_chkgeo'] = $checkgeo_det;
						$result_msg_arr['checkgeo']['data']['pincode_geo'] = $main_geo;
					}

				}else{
					if($curl_res_shadow['error']['code']=='0'){
						$result_msg_arr['shadow']['error']['code'] = 0;
						$result_msg_arr['shadow']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['shadow']['data']['pincode_shadow'] = $shadowdata;
						$result_msg_arr['shadow']['data']['pincode_geo'] = $main_geo;
					}else{
						$result_msg_arr['shadow']['error']['code'] = 1;
						$result_msg_arr['shadow']['error']['msg'] = "Reject GeoCode";
						$result_msg_arr['shadow']['data']['pincode_shadow'] = $shadowdata;
						$result_msg_arr['shadow']['data']['pincode_geo'] = $main_geo;
					}

					 if($curl_res_checkgeo['error']['code']=='0'){
						$result_msg_arr['checkgeo']['error']['code'] = 0;
						$result_msg_arr['checkgeo']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['checkgeo']['data']['pincode_chkgeo'] = $checkgeo_det;
						$result_msg_arr['checkgeo']['data']['pincode_geo'] = $main_geo;
					}else{
						$result_msg_arr['checkgeo']['error']['code'] = 1;
						$result_msg_arr['checkgeo']['error']['msg'] = "Reject GeoCode";
						$result_msg_arr['checkgeo']['data']['pincode_chkgeo'] = $checkgeo_det;
						$result_msg_arr['checkgeo']['data']['pincode_geo'] = $main_geo;
					}
						
				}
			}else{
						$result_msg_arr['shadow']['error']['code'] = 0;
						$result_msg_arr['shadow']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['shadow']['data']['pincode_shadow'] = $shadowdata;
						$result_msg_arr['shadow']['data']['pincode_geo'] = $main_geo;
						$result_msg_arr['checkgeo']['error']['code'] = 0;
						$result_msg_arr['checkgeo']['error']['msg'] = "Allow GeoCode";
						$result_msg_arr['checkgeo']['data']['pincode_chkgeo'] = $checkgeo_det;
						$result_msg_arr['checkgeo']['data']['pincode_geo'] = $main_geo;
			}	

			return $result_msg_arr;
				
		}
}
?>
