<?php 
class bformmulticity_class extends DB {
	
	var  $conn_iro    	= null;
	var  $conn_local   	= null;
	var  $conn_tme 		= null;
	var  $conn_idc    	= null;
	var  $params  		= null;
	
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var  $parentid		= null;
	var  $module		= null;
	var  $data_city		= null;
	var  $usercode		= null;
	
	function __construct($params)
	{
		$data_arr = array();
		$parentid 			= trim($params['parentid']);
		$module 			= trim($params['module']);
		$data_city 			= trim(urldecode($params['data_city']));
		
		if(trim($parentid)=='' || $parentid == null )
        {
			$data_arr['data'] = array();
			$data_arr['errorCode'] = 1;
			$data_arr['errorMsg']  = "Parentid is blank.";
			echo json_encode($data_arr);
			die();
        }else if(trim($data_city)=='' || $data_city == null)
		{	
			$data_arr['data'] = array();
			$data_arr['errorCode'] = 1;
			$data_arr['errorMsg']  = "Data city is blank.";
			echo json_encode($data_arr);
			die();
		}else if(trim($module)=='' || $module == null){
			$data_arr['data'] = array();
			$data_arr['errorCode'] = 1;
			$data_arr['errorMsg']  = "Module is blank.";
			echo json_encode($data_arr);
			die();
		}
		
		$this->usercode	=  $params['usercode'];
		
		$this->parentid  	= trim($parentid);
		$this->data_city 	= trim($data_city);
		$this->module  	  	= trim($module);
		
		$this -> national_list_obj = new nationallistingclass($params);
		
		//mongo
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		$this->setServers();
	}
	
	function setServers()
	{
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->is_remote = '';
		if($conn_city == 'remote'){
			$this->is_remote = 'REMOTE';
		}
		
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_jds    		= $db[$conn_city]['d_jds']['master'];
		$this->conn_tme  		= $db[$conn_city]['tme_jds']['master'];
		$this->conn_idc   		= $db[$conn_city]['idc']['master'];
		$this->fin   			= $db[$conn_city]['fin']['master'];
		$this->national   			= $db['db_national'];
		//echo "<pre>"; print_r($this->fin );
		if(strtoupper($this->module) =='TME'){
			$this->conn_temp		= $this->conn_tme;
			$this->conn_local       = $this->conn_jds; 
			$this->conn_fin       = $this->conn_tme; 
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($conn_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}

		}else if(strtoupper($this->module) =='ME' || strtoupper($this->module) =='JDA') {
			$this->conn_temp		= $this->conn_idc;
			$this->conn_local       = $this->conn_idc;
			$this->conn_fin         = $this->conn_idc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){
				$this->mongo_flag = 1;
			}
		}else if(strtoupper($this->module) =='CS') {
			$this->conn_temp		= $this->conn_jds;
			$this->conn_local       = $this->conn_jds;
			$this->conn_fin         = $this->fin; 
		}
		
	}
	
	public function initialBalance() {
		$data_arr = array();
		$campaign_row='';
		$qry = "SELECT campaignid,balance FROM tbl_companymaster_finance WHERE parentid = '" . $this->parentid . "' AND campaignid NOT IN (22,72,73,74,75,82,83)";
		$res = parent::execQuery($qry, $this->fin );
		
		if($res && mysql_num_rows($res) > 0)	{
			while($row = mysql_fetch_assoc($res)){
				$campaignid  = $row['campaignid'];
				$campaign_row[$campaignid] = $row['balance'];
			}
		}
		
		$qry_idc = "SELECT campaignid,balance FROM db_national_listing.tbl_companymaster_finance_national WHERE parentid = '" . $this->parentid . "'";
		$res_idc = parent::execQuery($qry_idc, $this->national);
		
		if ($res_idc && mysql_num_rows($res_idc) > 0){
		  $idc_row = mysql_fetch_assoc($res_idc);
		  $campaign_row[$idc_row['campaignid']] = $idc_row['balance'];
		}
		
		if($campaign_row == "") {
			$data_arr["errorCode"] = 1;
			$data_arr["errorMsg"] = "Data Not Found";
		}else {
			$data_arr["errorCode"] = 0;
			$data_arr["errorMsg"] = "Data Found";
			$data_arr["data"] = $campaign_row;
		}
		
		return json_encode($data_arr);
	}


	public function getFinanceMainData($campaignid=0,$version=0) {
		$data_arr = array();
		$initialBalanceRes = json_decode($this->initialBalance(),1);
		if($initialBalanceRes['errorCode'] == 1)	{
			$data_arr["errorCode"] = 1;
			$data_arr["errorMsg"] = "initial Balance Missing" ;
		}else{
			$campaign_balance = $initialBalanceRes['data'];
			$shadow_str = '';
			$where = ' AND active_campaign=1';
			$camp_main_array = array();
			if ($campaignid>0) $where = " AND campaignid = " . $campaignid . " ";

			if ($version) {
				$where = " AND version = " .$version. " ";
				$shadow_str = "_shadow";
			}
			
			$company_finance_temp_sql  = "SELECT * FROM tbl_companymaster_finance{$shadow_str} WHERE parentid = '" .$this->parentid."'". $where . " ";
			$company_finance_temp_res  = parent::execQuery($company_finance_temp_sql, $this->fin);
			$company_finance_temp_rows =  mysql_num_rows($company_finance_temp_res);

			if ($company_finance_temp_rows) {
				while ( $temp_values = mysql_fetch_assoc($company_finance_temp_res)){
					$temp_values['balance'] = $campaign_balance[$temp_values['campaignid']];
					$camp_main_array[$temp_values['campaignid']] = $temp_values;
				}
			}else if ($version==0 && $company_finance_temp_rows==0) {
				$company_finance_temp_sql  = "SELECT * FROM tbl_companymaster_finance WHERE parentid = '" .$this->parentid. "'";
				$company_finance_temp_res  = parent::execQuery($company_finance_temp_sql, $this->fin);
				$company_finance_temp_rows = mysql_num_rows($company_finance_temp_res);
				while ($temp_values =  mysql_fetch_assoc($company_finance_temp_res)) {
					$temp_values['balance'] = $campaign_balance[$temp_values['campaignid']];
					$camp_main_array[$temp_values['campaignid']] = $temp_values;
				}
			}
			
			if ($version==0) {
				$comp_fin_national_sql  = "SELECT * FROM tbl_companymaster_finance_national{$shadow_str} WHERE parentid = '" .$this->parentid. "' AND campaignid = 10 AND active_campaign=1";
				$comp_fin_national_res  = parent::execQuery($comp_fin_national_sql, $this->national);
				$comp_fin_national_rows = mysql_num_rows($comp_fin_national_res);
				
				if ($comp_fin_national_rows) {
					while ($national_values =  mysql_fetch_assoc($comp_fin_national_res)) {
						$national_values['balance'] = $campaign_balance[$national_values['campaignid']];
						$camp_main_array[$national_values['campaignid']] = $national_values;
					}
				}
			}
			
			//echo "<pre>"; print_r($camp_main_array);
			if(count($camp_main_array) == 0) {
				$data_arr["errorCode"] = 1;
				$data_arr["errorMsg"] = "Data Not Found" ;
			}else {
				$data_arr["errorCode"] = 0;
				$data_arr["errorMsg"] = "Data Found" ;
				$data_arr["data"] =  $camp_main_array;
				
			}
		}
		return json_encode($data_arr);
	}

	public function getNationalListingTemp($fieldArr){
		if($fieldArr == '' || $fieldArr ==null) {
			$fieldArr=' * ';
		}
		$res_arr = array();
		$sql = "select ".$fieldArr." from tbl_national_listing_temp where parentid = '".$this->parentid."'";   
		$qry  = parent::execQuery($sql, $this->conn_temp);
		if($qry && mysql_num_rows($qry))
		{
			$res_arr['data']	= mysql_fetch_assoc($qry);
			$res_arr['errorCode']	= 0;
			$res_arr['errorMsg']	= "data found";
		}else {
			$res_arr['errorCode']	= 1;
			$res_arr['errorMsg']	= "data not found";
		}
		return json_encode($res_arr);
	}
		
	public function checkMultiCity($catidlineage_nonpaid){
		
		$res_arr = array();
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1)
		{
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_business_temp_data";
			$mongo_inputs['fields'] 	= "";
			$mongo_alias = array();
			$mongo_alias['catIds']		= "catids";
			$mongo_inputs['aliaskey'] 	= $mongo_alias;
			$rowsB2b = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$qryC 			= "select catIds as catids from tbl_business_temp_data where  contractid = '" . $this->parentid. "'";
			$conn_national  = parent::execQuery($qryC, $this->conn_temp);
			$rowsB2b 		=  mysql_fetch_assoc($conn_national);
		}
		
		$b2bcat 	= $rowsB2b['catids'];
		$catstr = trim(str_replace("|P|",',',$b2bcat), ',');
		
		$hidden = 0;
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_temp_intermediate";
			$mongo_inputs['fields'] 	= "nonpaid,actMode,bronze,exclusive,hiddenCon,version";
			$row_nonpaid = $this->mongo_obj->getData($mongo_inputs);
		}
		else
		{
			$sql_nonpaid = "SELECT nonpaid,actMode, bronze, exclusive, hiddenCon,version FROM tbl_temp_intermediate WHERE parentid='".$this->parentid."'";
			$resCat  = parent::execQuery($sql_nonpaid, $this->conn_temp);
			$num_rows = mysql_num_rows($resCat);
			if($num_rows > 0 ){
				$row_nonpaid =  mysql_fetch_assoc($resCat);
			}
		}
		
		$actMode	= $row_nonpaid['actMode'];
		$hidden	    = $row_nonpaid['hiddenCon'];
		$version 	= $row_nonpaid['version'];
		
		if($version == '' || $version == null) {
			$res_arr['errorCode']	= 1;
			$res_arr['errorMsg']	= "Version Not Found";
			return json_encode($res_arr);
		}
		 
		$balanceValShadow_res =	json_decode($this->getFinanceMainData(0,$version),1);
		if($balanceValShadow_res['errorCode'] == 0){
			$balanceValShadow =	$balanceValShadow_res['data'];
		}else{
			//~ $balanceValShadow = '';
		}
		
		$balanceVal_res = json_decode($this->getFinanceMainData(),1);
		if($balanceVal_res['errorCode'] == 0){
			$balanceVal =	$balanceVal_res['data'];
		}else{
			//~ $balanceVal = '';
		}
		
		$getnationalistingdata = json_decode($this->getNationalListingTemp("Category_city,state_zone"),1);
		
		if($getnationalistingdata['errorCode'] == 0){
			$getnationalisting['Category_city'] = strtolower(trim($getnationalistingdata['data']['Category_city'],'|#|'));
			$getnationalisting['Category_city'] = str_replace('|#|',',',$getnationalisting['Category_city']);
			if($getnationalistingdata['data']['state_zone'] == 3) {
				$getnationalisting['state_zone'] = "city";
			}else if($getnationalistingdata['data']['state_zone'] == 2) {
				$getnationalisting['state_zone'] = "state";
			}else if($getnationalistingdata['data']['state_zone'] == 1) {
				$getnationalisting['state_zone'] = "zone";
			}
			
		}
		
		
		if(!count($balanceValShadow)){
			$balanceValShadow = $balanceVal;
		}
		
		if(strlen(trim($catstr,','))!=0)
		{

			/*$qryCat 	= "select if(category_type&128=128,1,0) AS mfrs, 
					CASE business_flag
					WHEN 2 THEN 'B2C'
					WHEN 1 THEN 'B2B'
					WHEN 3 THEN 'B2B,B2C' END  AS imparent,min(category_scope) AS natCat   from 
					tbl_categorymaster_generalinfo where catid in (" . $catstr. ") AND isdeleted = 0 AND mask_status=0";
			*/					
			//$resCat  = parent::execQuery($qryCat, $this->conn_local);
			$cat_params = array();
			$cat_params['page'] ='bformmulticity_class';
			$cat_params['data_city'] 	= $this->data_city;
			$cat_params['return']		= 'category_scope';			

			$where_arr  	=	array();			
			$where_arr['catid']			= $catstr;
			$where_arr['isdeleted']		= '0';
			$where_arr['mask_status']	= '0';
			$cat_params['where']		= json_encode($where_arr);
			if($catstr!=''){
				$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
			}
			$cat_res_arr = array();
			if($cat_res!=''){
				$cat_res_arr =	json_decode($cat_res,TRUE);
			}
					
			$natCat_arr = array();
			if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0){
				
				foreach ($cat_res_arr['results'] as $key => $cat_arr) {					
					if($cat_arr['category_scope']!=''){
						$natCat_arr[] = $cat_arr['category_scope'];
					}
				}

			}	
				$nationalavail= min($natCat_arr);
		}
		
		
		//~ catidlineage_nonpaid   get this in paramater   
		if($catidlineage_nonpaid!='')
			$nonpaidCat = 0;
		else
			$nonpaidCat = 0;
		
			
		$check=0;
		if(count($balanceValShadow)>0){
			foreach($balanceValShadow as $key => $value){
				if($key == 1 || $key == 10 || $key ==16 || $key ==7|| $key ==8 || $key==22 || $key==24  || $key ==56){
					continue;
				}else
				{
					if($balanceValShadow[$key]['expired'] == 0)
					{
					//	$check=1;
						break;
					}	
				}
			}			
		}
		$parentidType = substr_count($this->parentid,".");
		if($balanceVal['10']['balance']<=0){
			if(count($getnationalisting)) {
				$res_arr['city_data'] = "allow City contract creation";
				$res_arr['city_errorCode'] = "0";
			}else {
				$res_arr['city_data'] = "City contract selected";
				$res_arr['city_errorCode'] = "2";
			}
		}else {
			$res_arr['city_data'] = "City contract creation is not allowed";
			$res_arr['city_errorCode'] = "1";
		}
		
		if($parentidType == 0){
			$res_arr['mul_data'] = "Multicity Option is Not Available for this type of parentid.You have to create a new contract for the national listing sign up";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}else if($nonpaidCat){
			$res_arr['mul_data'] = "Multicity Option is Not Available,As Contract contains non-paid categories";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}else if($check){
			$res_arr['mul_data'] = "Multicity Option is Not Available,As no campaigns are not allowed with national listing other than package.";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}else if($nonpaidCat){
			$res_arr['mul_data'] = "Multicity Option is Not Available,As Contract contains non-paid categories.";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}else if($hidden == 1){
			//~ if($hidden == 1) $cont = 'hidden';
			//~ else if($c2c == 1) $cont = 'c2c';
			//~ else if($excl == 1) $cont = 'exclusive';
			//~ echo "6";
			$res_arr['mul_data'] = "Multicity Option is Not Available for hidden Contracts.";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}else if(((/*($nationalavail == 1 || $nationalavail ==2) && */strlen(trim($catstr,','))!=0)|| strlen(trim($catstr,','))==0) && $hidden == 0) {
			if(count($getnationalisting)>0) {
				$res_arr['mul_data'] = "multiCity contract selected";
				$res_arr['mul_errorCode'] = "3";
				$res_arr['selected_data'] = $getnationalisting;
				$res_arr['errorMsg']  = "success";
			}else {
				$res_arr['mul_data'] = "allow  multiCity contract";
				$res_arr['mul_errorCode'] = "0";
				$res_arr['errorMsg']  = "success";
			}
		}else{
			$res_arr['mul_data'] = "Multicity Option is Not Available,since this Company Contains a Non B2B Category.";
			$res_arr['mul_errorCode'] = "1";
			$res_arr['errorMsg']  = "failed";
		}
		
		$ecs_qry="SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='".$this->parentid."' AND activeflag = 1 AND deactiveflag = 0 AND ecs_stop_flag = 0 AND ( mandate_type IS NULL  OR mandate_type='' OR mandate_type='JDA' ) LIMIT 1 ";
		$resecs  = parent::execQuery($ecs_qry, $this->fin);
		
		$si_qry="SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='".$this->parentid."' AND activeflag = 1 and deactiveflag = 0 and ecs_stop_flag = 0 AND ( mandate_type IS NULL  OR mandate_type='' OR mandate_type='JDA' ) LIMIT 1";
		$ressi  = parent::execQuery($si_qry, $this->fin);
		
		
		$res_arr['block_edit'] = 0;
		
		if(($balanceVal_res['data'][1]['balance'] > 0 || $balanceVal_res['data'][2]['balance'] > 0 || ($balanceVal_res['data'][2]['manual_override'] == 1 && $balanceVal_res['data'][2]['expired'] == 0) || ($balanceVal_res['data'][1]['manual_override'] == 1 && $balanceVal_res['data'][1]['expired'] == 0) ) && (mysql_num_rows($resecs) > 0 || mysql_num_rows($ressi) > 0))
		{
			if(in_array(strtolower($this->data_city),array("mumbai","bangalore","chennai","ahmedabad","pune","coimbatore")))
			{
				$res_arr['block_edit'] = 1;
			}
		}
		
		if(mysql_num_rows($resecs) > 0) {
			$res_arr['ecs_status'] = 1;
			$res_arr['ecs_type'] = "ecs";
		}else if(mysql_num_rows($ressi) > 0) {
			$res_arr['ecs_status'] = 1;
			$res_arr['ecs_type'] = "si";
		}else{
			$res_arr['ecs_status'] = 0;
			$res_arr['ecs_type'] = "";
		}
			
			
		return json_encode($res_arr);
	}
	
	public function getCountryZones(){
		$res_arr = array();
		$sql ="SELECT countryzone,countryzone_id FROM countryzone_master ORDER BY countryzone_id";
		$reszone  = parent::execQuery($sql, $this->conn_local);
		
		if(mysql_num_rows($reszone) > 0) {
			while($row = mysql_fetch_assoc($reszone)){
				$res_arr['data'][] = $row ;
			}
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['data'] =  '';
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data Not found";
		}
		return json_encode($res_arr);
	}
	
	public function getZoneListings(){
		$res_arr = array();
		$sql="SELECT DISTINCT(ct_name) AS city,countryzone as zone,countryzone_id as zoneid FROM city_master WHERE /*ct_name != '".$this->data_city."' AND*/ multicity_display = '1' AND display_flag=1  ORDER BY countryzone,ct_name";
		$reszone  = parent::execQuery($sql, $this->conn_local);
		$val = array();
		if(mysql_num_rows($reszone) > 0){
			$i =0;
			while($row = mysql_fetch_assoc($reszone)) {
				if(strtolower($row['zone']) != $res_arr['data'][strtolower($row['zone'])][$i-1]['zone']){
					$i=0;
				}
				$res_arr['data'][strtolower($row['zone'])][$i]['city'] 	 = strtolower($row['city']);
				$res_arr['data'][strtolower($row['zone'])][$i]['zone'] 	 = strtolower($row['zone']);
				$res_arr['data'][strtolower($row['zone'])][$i]['zoneid'] = strtolower($row['zoneid']);
				$i++;
			}
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data Not found";
		}
		return json_encode($res_arr);
	}
	
	public function getTopCities() {
		$res_arr = array();
		$sql="SELECT ct_name as city ,city_id FROM city_master WHERE top_city_flag = 1 AND /*ct_name != '".$this->data_city."' AND*/ multicity_display=1 AND display_flag=1 ORDER BY totalcnt DESC";
		$reszone  = parent::execQuery($sql, $this->conn_local);
		$val = array();
		if(mysql_num_rows($reszone) > 0){
			$i =0;
			while($row = mysql_fetch_assoc($reszone)) {
				$res_arr['data']['Top Cities'][$i]['city'] = strtolower($row['city']);
				$res_arr['data']['Top Cities'][$i]['city_id'] = strtolower($row['city_id']);
				$i++;
			}
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data Not found";
		}
		return json_encode($res_arr);
	}
	
	public function getStateListings(){
		$res_arr = array();
		$sql="SELECT DISTINCT(ct_name) as city,city_id,state_id,state_name FROM city_master WHERE /*ct_name != '".$this->data_city."' AND*/ multicity_display = '1' AND display_flag=1  ORDER BY state_name,ct_name";
		$reszone  = parent::execQuery($sql, $this->conn_local);
		if(mysql_num_rows($reszone) > 0){
			$i =0;
			while($row = mysql_fetch_assoc($reszone)) {
				if(strtolower($row['state_name']) != $res_arr['data'][strtolower($row['state_name'])][$i-1]['state_name']){
					$i=0;
				}
				$res_arr['data'][strtolower($row['state_name'])][$i]['city'] 		= strtolower($row['city']);
				$res_arr['data'][strtolower($row['state_name'])][$i]['city_id'] 	= strtolower($row['city_id']);
				$res_arr['data'][strtolower($row['state_name'])][$i]['state_id']	= strtolower($row['state_id']);
				$res_arr['data'][strtolower($row['state_name'])][$i]['state_name']	= strtolower($row['state_name']);
				$i++;
			}
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data Not found";
		}
		return json_encode($res_arr);
	}
	
	public function getStateListings2(){
		$res_arr = array();
		$sql="SELECT DISTINCT(ct_name) as city,city_id,state_id,state_name,countryzone FROM city_master WHERE /*ct_name != '".$this->data_city."' AND */multicity_display = '1' AND display_flag=1  ORDER BY state_name,ct_name";
		$reszone  = parent::execQuery($sql, $this->conn_local);
		if(mysql_num_rows($reszone) > 0){
			$i =0;
			while($row = mysql_fetch_assoc($reszone)) {
			
				if(!$state_last_index[strtolower($row['state_name'])]['index'])
				{
					$state_last_index[strtolower($row['state_name'])]['index'] = 0;
				}
				
				switch(strtolower($row['countryzone']))
				{
				case  'south zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
				case  'north zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
				case  'west zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
				case  'east zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
				case  'central zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);
				case  'north east zone':
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city'] 		= strtolower($row['city']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['city_id'] 	= strtolower($row['city_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_id']	= strtolower($row['state_id']);
					$res_arr['data'][strtolower($row['countryzone'])][strtolower($row['state_name'])][$state_last_index[strtolower($row['state_name'])]['index']]['state_name']	= strtolower($row['state_name']);		
				}
				
				$state_last_index[strtolower($row['state_name'])]['index'] = $state_last_index[strtolower($row['state_name'])]['index'] + 1;
				
			}
			
		$sql_min_national = "SELECT minupfrontbudget_national,maxupfrontbudget_national,stateupfrontbudget_national FROM tbl_business_uploadrates WHERE city = '" . $this->data_city . "'";
		$res_min_national = parent::execQuery($sql_min_national,$this->conn_jds);
		//echo mysql_num_rows($res_min_national);
	
	
		if($res_min_national && mysql_num_rows($res_min_national)>0)
		{
			$row_arr = mysql_fetch_assoc($res_min_national);
			$res_arr['minupfrontbudget_national']	= intval($row_arr['minupfrontbudget_national']);
			$res_arr['maxupfrontbudget_national']	= intval($row_arr['maxupfrontbudget_national']);
			$res_arr['stateupfrontbudget_national']	= intval($row_arr['stateupfrontbudget_national']);
		}
		else
		{
			$res_arr['minupfrontbudget_national'] = 0;
			$res_arr['maxupfrontbudget_national'] = 0;
			$res_arr['stateupfrontbudget_national'] = 0;
		}
			
			
			//echo '<pre>';print_r($res_arr);
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data found";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data Not found";
		}
		return json_encode($res_arr);
	}
	
	public function insertNationalListingval($citystr,$latitude,$longitude,$type){
		$res_arr = array();
		$cityList = "|#|";//.$this->data_city."|#|";
		$city_arr = explode(',',$citystr);
		
		if(count($city_arr) > 0 && $citystr!='' && $citystr!=null){
			foreach($city_arr as $key=>$value)		
			{		
				$cityList .= $value . "|#|";
			}
		}
		
		if($type == 'zone') {
			$statezone = 1;
		}else if($type == 'state'){
			$statezone = 2;
		}else if($type == 'city'){
			$statezone = 3;
		}
		
		$shortUrlSql = "SELECT sphinx_id, parentid, CONCAT(url_cityid, shorturl) AS url FROM tbl_id_generator WHERE shorturl IS NOT NULL AND parentid='".$this->parentid."'";
		$reszone  = parent::execQuery($shortUrlSql, $this->conn_iro);
		$shortUrlRow = mysql_fetch_assoc($reszone);
		
		$sqlIns = "INSERT INTO tbl_national_listing_temp SET
							parentid 			= '".$this->parentid."',					   
							Category_city 		= '".$cityList."',										
							contractCity 		= '".$this->data_city."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							short_url		 	= '".$shortUrlRow['url']."'
						ON DUPLICATE KEY UPDATE
							Category_city 		= '".$cityList."',										
							contractCity 		= '".$this->data_city."',
							latitude			= '".$latitude."',
							longitude			= '".$longitude."',
							lastupdate			= '".date('Y-m-d H:i:s')."',
							state_zone			= '".$statezone."',
							short_url		 	= '".$shortUrlRow['url']."'";
		$resIns = parent::execQuery($sqlIns, $this->conn_temp);
		$insert_debug_log = "INSERT INTO tbl_national_listing_temp_debug SET parentid='".$this->parentid."',page='jd_box/services/includes/bformmulticity_class.php',line_no= '468',query= '".addslashes(stripslashes($sqlIns))."',date_time= '".date('Y-m-d H:i:s')."',ucode= '".$_SESSION['ucode']."',uname='".$_SESSION['uname']."'";
		$res_insert_debug_log =  parent::execQuery($insert_debug_log, $this->conn_temp);
		
		$national_listing_arr = $this -> national_list_obj -> isStateAdded();
		
		if($national_listing_arr['state_change'] || $national_listing_arr['new_contract'])
		{
			$sql_finance_temp = "UPDATE tbl_companymaster_finance_temp SET recalculate_flag  = 0 WHERE parentid='".$this->parentid."' AND campaignid='10'";
			$re_finance_temp  = parent::execQuery($sql_finance_temp, $this->conn_temp);
		}
		
		
		if($resIns) {
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data inserted";
		}else {
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data not inserted";
		}
		return json_encode($res_arr);
	}
	
	public function insertLocalListingval($sphinxid){
		$res_arr = array();
		$national_temp = json_decode($this->getNationalListingTemp('parentid'),1);
		
		if($national_temp['errorCode'] == 0){
			
			$inst_qry ="INSERT INTO tbl_companymaster_finance_temp SET
                                            sphinx_id   = '".$sphinxid."',
                                            campaignid  = 10,
                                            parentid    = '" .$this->parentid. "',
                                            budget		= 0,
                                            duration    = 0,
                                            recalculate_flag =0
                                            ON DUPLICATE KEY UPDATE
                                            budget		= 0,
                                            duration    = 0,
                                            recalculate_flag =0";
            $res_insert_query =  parent::execQuery($inst_qry, $this->conn_fin);
		}
		
		$qrySelDel = "DELETE FROM tbl_national_listing_temp WHERE parentid='".$this->parentid."'";
		$resSelDel = parent::execQuery($qrySelDel, $this->conn_temp);
		if($resSelDel) {
			$res_arr['errorCode'] = "0";
			$res_arr['errorMsg']  = "Data inserted";
		}else{
			$res_arr['errorCode'] = "1";
			$res_arr['errorMsg']  = "Data not inserted";
		}
		return json_encode($res_arr);
	}
	
}



?>
