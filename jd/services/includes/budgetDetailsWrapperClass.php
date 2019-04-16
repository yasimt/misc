<?php

class budgetDetailsWrapperClass extends DB
{
	var $dbConIro = null;
	var $dbConDjds = null;
	var $dbConTmeJds = null;
	var $dbConFin = null;
	var $dbConIdc = null;
	var $params = null;
	var $dataservers = array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var $arr_errors = array();
	var $is_split = null;
	var $parentid = null;
	var $Tot_flexi_budget_arr_catwise = array();
	var $manage_campaign_removedlivecategories = array();
	var $manage_campaign_addeded_or_shadowcatidlineagenonpaid_categories = array();
	
		var $phonesearchwraperdistribution = array();

	function __construct($params)
	{
		$this->params = $params;
		
		if(trim($this->params['parentid']) != "") {
			$this->parentid = strtoupper($this->params['parentid']); //initialize paretnid
		}
				
		if (trim($this->params['version']) != "") {
			$this->version = $this->params['version']; //initialize version
		}

		if ( isset($this->params['mode']) &&  trim($this->params['mode']) != "") {
			$this->mode = $this->params['mode']; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive 6-renewal2
		}	

		if (trim($this->params['option']) != "") {
			$this->option = $this->params['option']; // default 1, max 7
		}
		
		$this->setServers();
		$this->mongo_obj = new MongoClass();
		$this->categoryClass_obj = new categoryClass();
		
		
		#$this->centraliselogging($params,'API Params',$apiurl=null,$apiurlresponse=null);	
	}

	function setServers()
	{
		global $db;

		$data_city = ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		$this->dbConDjds = $db[$data_city]['d_jds']['master'];
		$this->finance = $db[$data_city]['fin']['master'];
		$this->dbConbudget = $db[$data_city]['db_budgeting']['master'];
		
		
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];		
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		
		
		if (DEBUG_MODE) {
			echo '<br>dbConDjds:';
			print_r($this->dbConDjds);
			echo '<br>dbConbudget:';
			print_r($this->dbConbudget);
			echo '<br>db:';
			print_r($db);

		}
	}

	function escapeJsonString($value){
		$escapers = array('\\', '/', '\'');
		$replacements = array('\\\\', '\\/', '\\\'');
		$result = str_replace($escapers, $replacements, $value);
		return $result;
	}
	
	
	function getactualapiresponse()
	{
		$bdgtDtlsClsobj = new budgetDetailsClass($this->params);
		$result = $bdgtDtlsClsobj->getBudget();
		return $result;
	}
	
	function setBudgetWrapperSummary($bdgtapires)
	{
		
		$sql = " INSERT INTO tbl_budget_wrapper_summary SET 
				parentid='".$this->parentid."',
				version='".$this->version."',
				budgetapiresponse= '".addslashes(stripslashes(json_encode($bdgtapires)))."'
				
				ON DUPLICATE KEY UPDATE
				budgetapiresponse= '".addslashes(stripslashes(json_encode($bdgtapires)))."'
							
				";
				
		parent::execQuery($sql, $this->dbConbudget);		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
				
	}
	
	function updatetbflexibgtBdgetWrperSry($tbflexibgt,$pid,$vrsn)
	{
		
		$sql = "update tbl_budget_wrapper_summary SET tb_flexi_bgt=".$tbflexibgt."
				WHERE 
				parentid='".$pid."' and version='".$vrsn."' ";
				
		parent::execQuery($sql, $this->dbConbudget);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>sql:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
				
	}
	
	function getCatPinPosProcessedDataphonesearch($catid,$catidarray,$package_array=array(),$requestmode=null)
	{
		
		$ReturnArr= array();
		
		array_push($this->phonesearchwraperdistribution['catlist'],$catid);
		
		
		if(is_array($package_array) && count($package_array)>0)
		{
			$package_bidders = $package_array['bidder'];
			$package_bid = $package_array['bid'];
			$package_sc = $package_array['sc'];
		}		
		
		$CatPinPosarray	= array ();
		$PinPosarray	= array();
		
		$CatPinPosarray['cid']		 = $catid;
		#$CatPinPosarray['c_bgt']	 = $catidarray['c_bgt']	;
		$CatPinPosarray['c_bgt']	 = $catidarray['f_bgt']	;
		
		$CatPinPosarray['bflg']		 = $catidarray['bflg'];
		$CatPinPosarray['bm_bgt']	 = $catidarray['bm_bgt'];
		$CatPinPosarray['cnm']		 = $catidarray['cnm'];
		$CatPinPosarray['ncid']		 = $catidarray['ncid'];
		
		$category_fixedpositionbudget	= 0 ;
		$category_packagepositionbudget= 0 ;
		
		$pin_data_val = $catidarray['pin_data'];
		
		$managecampaign_category_flexi_budget=0;
		//print_r( $pin_data_val);
		$srchCntTotal	=	0;
		foreach($pin_data_val  as $pincode=>$pincodeArr)
		{
			
			$best_flg_val =  $pincodeArr['best_flg'];
			
			$PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] 		= $pincodeArr['pos'][$best_flg_val]['budget'];
			$PinPosarray[$pincode]['pos'][$best_flg_val]['bidvalue'] 	= $pincodeArr['pos'][$best_flg_val]['bidvalue'];
			$PinPosarray[$pincode]['pos'][$best_flg_val]['inventory'] 	= $pincodeArr['pos'][$best_flg_val]['inv_avail'];
			
			$PinPosarray[$pincode]['cnt']	= $pincodeArr['cnt'];
			$PinPosarray[$pincode]['cnt_f']	= $pincodeArr['cnt_f'];
			
			
			array_push($this->phonesearchwraperdistribution['pinlist'],$pincode);
			
			if($best_flg_val==100)
			{
				$this->phonesearchwraperdistribution['packagepositionbudget']	+= $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] ;
				$category_packagepositionbudget					+= $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] ;
				
			}else
			{	
				$this->phonesearchwraperdistribution['fixedpositionbudget']		+= $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] ;
				$category_fixedpositionbudget						+= $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] ;
			}

			$srchCntTotalF	=	$srchCntTotalF+ $pincodeArr['srch_cnt_f'];
			$srchCntTotal	=	$srchCntTotal+ $pincodeArr['cnt_f'];
			$this->phonesearchwraperdistribution['positionwise_pincode_number'][$best_flg_val]+=1;
			$this->phonesearchwraperdistribution['cat_pincode_wise_pos'][$catid][$pincode]=$best_flg_val;
		
			if(DEBUG_MODE)
			{	
				
				echo "<br>pincodeArr pos---".$pincodeArr['pos'];
				echo "<br>best_flg_val".$best_flg_val;
				echo '<br><b>phonesearchwraperdistribution:</b>';print_r($this->phonesearchwraperdistribution);
				echo "<br>budget---".$pincodeArr['pos'][$best_flg_val]['budget'];
				#echo '<br><b>pincodeArr:</b>';print_r($pincodeArr);
				
				#echo '<br><b>best_flg_val:</b>'.$best_flg_val;
			}
			
		}
		
		$CatPinPosarray['pin_data'] = $PinPosarray;
		
		if(DEBUG_MODE)
		{			
			
			#echo '<br><b>pin_data_val:</b>';print_r($pin_data_val);
			#echo '<br><b>catidarray:</b>';print_r($catidarray);
			echo '<br><b>CatPinPosarray:</b>';print_r($CatPinPosarray);
			
		}
		
		$ReturnArr['pin_data_processed']= $CatPinPosarray;
		
		$ReturnArr['cat_packagepositionbudget'] = $category_packagepositionbudget ;
		$ReturnArr['cat_fixedpositionbudget']   = $category_fixedpositionbudget ;
		$ReturnArr[ 'srch_cnt_f']   = $srchCntTotalF;
		$ReturnArr[ 'cnt_f']   = $srchCntTotal;
		
		return $ReturnArr;
		
		#{"cid":305,"ncid":"10076456","cnm":"Car Hire","cst":"Z","bval":492.418388,"bflg":"0","c_bgt":22653.764,"bm_bgt":0,"f_bgt":22653.764,"xflg":"0"}
		
		#"c_bgt":35643.486700073,"flexi_bgt":35643.486700073,"bflg":"0","bm_bgt":0,"cnm":"Gold Jewellery Showrooms","ncid":"10234859","pin_data":
	}	
	
	#this function will retrun the array with whatever the best cat pin pos rerurned by budget api
	
	function getCatPinPosProcessedData($catid,$catidarray,$package_array=array(),$requestmode=null)
	{
		$ReturnArr= array();
		
		if(is_array($package_array) && count($package_array)>0)
		{
			$package_bidders = $package_array['bidder'];
			$package_bid = $package_array['bid'];
			$package_sc = $package_array['sc'];
		}		
		
		$CatPinPosarray	= array ();
		$PinPosarray	= array();
		
		$CatPinPosarray['cid']		 = $catid;
		#$CatPinPosarray['c_bgt']	 = $catidarray['c_bgt']	;
		$CatPinPosarray['c_bgt']	 = $catidarray['f_bgt']	;
		
		if(isset($catidarray['flexi_bgt']))
		{
			$CatPinPosarray['flexi_bgt'] = $catidarray['flexi_bgt'] ;
			$CatPinPosarray['c_bgt']	 = $catidarray['flexi_bgt']	; // if it is flexi then use flxi_bdgt
		}
		
		$CatPinPosarray['bflg']		 = $catidarray['bflg'];
		$CatPinPosarray['bm_bgt']	 = $catidarray['bm_bgt'];
		$CatPinPosarray['cnm']		 = $catidarray['cnm'];
		$CatPinPosarray['ncid']		 = $catidarray['ncid'];
		
		$pin_data_val = $catidarray['pin_data'];
		
		$managecampaign_category_flexi_budget=0;
		
		foreach($pin_data_val  as $pincode=>$pincodeArr)
		{
			
			$renew_flg	  =  $pincodeArr['renew_flg'];
			if($renew_flg==1)
			{
				$best_flg_val =  $pincodeArr['best_flg'];
			}else
			{
				$best_flg_val=100;
			}
			
			
			
			// if it is a flexi contract then conside flexi budget
			
			if(isset($pincodeArr['flexi_bgt']) && floatval($pincodeArr['flexi_bgt'])!=0)
			{
				$PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] 		= $pincodeArr['flexi_bgt'];
			
			
			}elseif($requestmode=='managecampaign')
			{
				if($renew_flg==1 && !in_array($catid,$this->manage_campaign_addeded_or_shadowcatidlineagenonpaid_categories))
				{
					#$PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] 		= $pincodeArr['pos'][$best_flg_val]['a_bpd']*$this->tbl_companymaster_finance_duration;
					$PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] 		= $pincodeArr['a_clbgt'];
					
					#$managecampaign_category_flexi_budget += $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'];
					
					//
					$catidarray['pin_data'][$pincode]['flexi_bgt']		= $PinPosarray[$pincode]['pos'][$best_flg_val]['budget'];
					$catidarray['pin_data'][$pincode]['flexi_pos']		= $best_flg_val;
					$catidarray['pin_data'][$pincode]['flexi_bpd']		= $pincodeArr['a_clbpd'];				
				
				}else // these will be those case where category or pincode is added 
				{
					$catidarray['pin_data'][$pincode]['flexi_bgt']		= 0;
					$catidarray['pin_data'][$pincode]['flexi_pos']		= 101;
					$catidarray['pin_data'][$pincode]['flexi_bpd']		= 0;				
				}
				
					$managecampaign_category_flexi_budget += $catidarray['pin_data'][$pincode]['flexi_bgt'];
					
				# now we have to stuffing flexi_bidder flexi_bid flexi_sc at pincode level
				
				
				$catidarray['pin_data'][$pincode]['flexi_bidder'] = $package_bidders[$catid][$pincode];
				$catidarray['pin_data'][$pincode]['flexi_bid'] = $package_bid[$catid][$pincode];
				$catidarray['pin_data'][$pincode]['flexi_sc'] = $package_sc[$catid][$pincode];
				

			}else
			{
				$PinPosarray[$pincode]['pos'][$best_flg_val]['budget'] 		= $pincodeArr['pos'][$best_flg_val]['budget'];
			}
			
						
			$PinPosarray[$pincode]['pos'][$best_flg_val]['bidvalue'] 	= $pincodeArr['pos'][$best_flg_val]['bidvalue'];
			$PinPosarray[$pincode]['pos'][$best_flg_val]['inventory'] 	= $pincodeArr['pos'][$best_flg_val]['inv_avail'];
			
			$PinPosarray[$pincode]['cnt']	= $pincodeArr['cnt'];
			$PinPosarray[$pincode]['cnt_f']	= $pincodeArr['cnt_f'];
			
			
			if(DEBUG_MODE)
			{	
				#echo '<br><b>pincodeArr:</b>';print_r($pincodeArr);
				#echo '<br><b>best_flg_val:</b>'.$best_flg_val;
			}
			
		}
		
		$CatPinPosarray['pin_data'] = $PinPosarray;
		
		if(DEBUG_MODE)
		{			
			
			#echo '<br><b>pin_data_val:</b>';print_r($pin_data_val);
			#echo '<br><b>catidarray:</b>';print_r($catidarray);
			#echo '<br><b>CatPinPosarray:</b>';print_r($CatPinPosarray);
			
		}
		
		
		if($requestmode=='managecampaign')
		{
			$ReturnArr['pin_data_managecampaign'] = $catidarray['pin_data'];
			$ReturnArr['managecampaign_category_flexi_budget'] = $managecampaign_category_flexi_budget;
			
			$CatPinPosarray['flexi_bgt'] = $managecampaign_category_flexi_budget ;
			$CatPinPosarray['c_bgt']	 = $managecampaign_category_flexi_budget ; 
		}
		
		$ReturnArr['pin_data_processed']= $CatPinPosarray;
		
		
		
		
		
		return $ReturnArr;
		
		#{"cid":305,"ncid":"10076456","cnm":"Car Hire","cst":"Z","bval":492.418388,"bflg":"0","c_bgt":22653.764,"bm_bgt":0,"f_bgt":22653.764,"xflg":"0"}
		
		#"c_bgt":35643.486700073,"flexi_bgt":35643.486700073,"bflg":"0","bm_bgt":0,"cnm":"Gold Jewellery Showrooms","ncid":"10234859","pin_data":
	}
	
	function getFlexiPinPosMlbdgtforZeroBudget($pin_data,$catBudget,$whichFlow)
	{
		
		 $pin_data_pincode= array();
		 $pin_data_pincode_count=0;
		foreach($pin_data  as $pincode=>$pincodeArr)
		{						
			 $pin_data_pincode_count++;
		}
		
		$Newbdgt=$catBudget/$pin_data_pincode_count;
		if($whichFlow == 0) {//condition for flexi
			foreach($pin_data  as $pincode=>$pincodeArr){						
				$pin_data[$pincode]['flexi_bgt']	= $Newbdgt;
			}
		} else if($whichFlow == 1) {//condition for pdg
			foreach ($pin_data as $pincode => $pincodeArr) {
				$pin_data[$pincode]['best_bgt'] = $Newbdgt;
			}
		}
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>Newbdgt</b>---'.$Newbdgt;
			echo '<br><b>pin_data_pincode_count</b>---'.$pin_data_pincode_count;
			echo '<br><b>$pin_data_pincode</b>---'; count($pin_data_pincode);
			echo '<br><b>pin_data count</b>---'; count($pin_data);
			echo '<br><b>pin_data</b>  '; print_r($pin_data);
		}
		
		return $pin_data;
	}
	function getFlexiPinPosMlbdgt($pin_data,$flexi_ml_fact, $whichFlow)
	{
		if($whichFlow == 0) {//condition for flexi
			foreach($pin_data  as $pincode=>$pincodeArr)
			{						
				$pin_data[$pincode]['flexi_bgt']	= $pin_data[$pincode]['flexi_bgt']*$flexi_ml_fact;
			}
		} else if($whichFlow == 1) {//condition for pdg
			foreach ($pin_data as $pincode => $pincodeArr) {
				$pin_data[$pincode]['best_bgt'] = $pin_data[$pincode]['best_bgt'] * $flexi_ml_fact;
			}
		}
		
		return $pin_data;				
	}
	
	function setBudgetWrapperIntermediatephonesearch($bdgtDtlsResp_result_c_data,$package_array=array())
	{
		$returnarr = array();
		
		
		if(DEBUG_MODE)
		{
			echo '<br><b>setBudgetWrapperIntermediate</b>---';
			echo '<br><b>bdgtDtlsResp_result_c_data</b>  '; print_r($bdgtDtlsResp_result_c_data);
		}
		
		
		$sql = " DELETE from tbl_budget_wrapper_intermediate where parentid='".$this->parentid."' and version='".$this->version."'";
		parent::execQuery($sql, $this->dbConbudget);		
			
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		
				
		foreach($bdgtDtlsResp_result_c_data as $catid =>$catidarray)
		{	
				$CatPinPosarr = array();
				
				$pin_data = $catidarray['pin_data'];				
				
				$temparr = $this->getCatPinPosProcessedDataphonesearch($catid , $catidarray,$package_array);
				if(DEBUG_MODE) {
					echo "tempdataarr";
					print_r( $catidarray);
				}
				$CatPinPosarr = $temparr['pin_data_processed'];
				$sumFixPackBud	=	$temparr['cat_fixedpositionbudget']+ $temparr['cat_packagepositionbudget'];
				
				if( $sumFixPackBud < $catidarray['f_bgt']) {
					$updRatioFP		=	$temparr['cat_fixedpositionbudget']/ $sumFixPackBud;
					$updRatioPck	=	$temparr[ 'cat_packagepositionbudget']/ $sumFixPackBud;
					
					$temparr['cat_fixedpositionbudget']	=	$catidarray['f_bgt']* $updRatioFP;
					$temparr['cat_packagepositionbudget']	=	$catidarray['f_bgt']* $updRatioPck;
				}
				$catidarray['cat_fixedpositionbudget']		=	$temparr['cat_fixedpositionbudget'];
				$catidarray['cat_packagepositionbudget']	=	$temparr['cat_packagepositionbudget'];
				$catidarray['srch_cnt_f']	=	$temparr['srch_cnt_f'];
				$catidarray['cnt_f']	=	$temparr['cnt_f'];
				//$catidarray['cat_packagepositionbudget']	=	$temparr['cat_packagepositionbudget'];
				unset($catidarray['pin_data']);
				
				
				
				
				
				$sql = " INSERT INTO tbl_budget_wrapper_intermediate SET 
				parentid='".$this->parentid."',
				version='".$this->version."',
				catid='".$catid."',
				cat_data = '".addslashes(stripslashes(json_encode($catidarray)))."',
				pin_data = '".addslashes(stripslashes(json_encode($pin_data)))."',
				pin_data_processed ='".addslashes(stripslashes(json_encode($CatPinPosarr)))."',
				pin_data_final= '".addslashes(stripslashes(json_encode($CatPinPosarr)))."'
								
				ON DUPLICATE KEY UPDATE
				cat_data = '".addslashes(stripslashes(json_encode($catidarray)))."',
				pin_data = '".addslashes(stripslashes(json_encode($pin_data)))."',
				pin_data_processed ='".addslashes(stripslashes(json_encode($CatPinPosarr)))."',
				pin_data_final= '".addslashes(stripslashes(json_encode($CatPinPosarr)))."'
							
				";
				
				parent::execQuery($sql, $this->dbConbudget);		
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB Query:</b>---'.$sql;			
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				$returnarr[$catid]= $catidarray;
		}
		
		return $returnarr;
				
	}
	
	function setBudgetWrapperIntermediate($bdgtDtlsResp_result_c_data,$package_array=array(),$requestmode=null)
	{
		$returnarr = array();
		$this->Tot_flexi_budget_arr_catwise['sum']= 0;
		
		if(DEBUG_MODE)
		{
			echo '<br><b>setBudgetWrapperIntermediate</b>---';
			echo '<br><b>bdgtDtlsResp_result_c_data</b>  '; print_r($bdgtDtlsResp_result_c_data);
		}
		
		
		$sql = " DELETE from tbl_budget_wrapper_intermediate where parentid='".$this->parentid."' and version='".$this->version."'";
		parent::execQuery($sql, $this->dbConbudget);		
			
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		
				
		foreach($bdgtDtlsResp_result_c_data as $catid =>$catidarray)
		{
			
				if($requestmode=='managecampaign')
				{
					if(in_array($catid,$this->manage_campaign_removedlivecategories))
					continue;
				
				}
				
				$CatPinPosarr = array();
				
				$pin_data = $catidarray['pin_data'];				
				
				$temparr = $this->getCatPinPosProcessedData($catid , $catidarray,$package_array,$requestmode);
				
				$CatPinPosarr = $temparr['pin_data_processed'];
				
				#if request is managecampaing then revised pindata needs to store in table 
				if($requestmode=='managecampaign')
				{
					$pin_data = $temparr['pin_data_managecampaign'];
					$catidarray['flexi_bgt']= $temparr['managecampaign_category_flexi_budget'];
					
					$this->Tot_flexi_budget_arr_catwise['sum']+=$temparr['managecampaign_category_flexi_budget'];
					$this->Tot_flexi_budget_arr_catwise[$catid]=$temparr['managecampaign_category_flexi_budget'];
				}
				
				
				unset($catidarray['pin_data']);
				
				
				
				
				
				$sql = " INSERT INTO tbl_budget_wrapper_intermediate SET 
				parentid='".$this->parentid."',
				version='".$this->version."',
				catid='".$catid."',
				cat_data = '".addslashes(stripslashes(json_encode($catidarray)))."',
				pin_data = '".addslashes(stripslashes(json_encode($pin_data)))."',
				pin_data_processed ='".addslashes(stripslashes(json_encode($CatPinPosarr)))."',
				pin_data_final= '".addslashes(stripslashes(json_encode($CatPinPosarr)))."'
								
				ON DUPLICATE KEY UPDATE
				cat_data = '".addslashes(stripslashes(json_encode($catidarray)))."',
				pin_data = '".addslashes(stripslashes(json_encode($pin_data)))."',
				pin_data_processed ='".addslashes(stripslashes(json_encode($CatPinPosarr)))."',
				pin_data_final= '".addslashes(stripslashes(json_encode($CatPinPosarr)))."'
							
				";
				
				parent::execQuery($sql, $this->dbConbudget);		
				
				if(DEBUG_MODE)
				{
					echo '<br><b>DB Query:</b>---'.$sql;			
					echo '<br><b>Error:</b>'.$this->mysql_error;
				}
				
				$returnarr[$catid]= $catidarray;
		}
		
		return $returnarr;
				
	}
	
	function remove_pin_data_from_c_data($bdgtDtlsResp_result_c_data)
	{
		
		
	}
	
	
	function getbudgetinitial()
	{
		$returnarr = array();
		$bdgtDtlsClsobj = new budgetDetailsClass($this->params);
		$bdgtDtlsResp = $bdgtDtlsClsobj->getBudget();
		
		$this->centraliselogging( $this->params,'Sunny Sir Budget Data Flexi',null,json_encode( $bdgtDtlsResp));	
		
		if($bdgtDtlsResp['error']['code']!=0)
		{
			return	$bdgtDtlsResp['error'];
		
		}else
		{
			# now we have to process the entire data of bdgtDtlsResp
			
			$bdgtDtlsResp_result = $bdgtDtlsResp['result'];
			$bdgtDtlsResp_result_c_data = $bdgtDtlsResp_result['c_data'];
			
			#unset c_data and store the rest of response 
			unset($bdgtDtlsResp_result['c_data']);
			
			$this->setBudgetWrapperSummary($bdgtDtlsResp_result);
			
			$c_data_without_pin_data = $this->setBudgetWrapperIntermediate($bdgtDtlsResp_result_c_data);
			
			$bdgtDtlsResp_result['c_data']=$c_data_without_pin_data;
			
			$returnarr['result']=$bdgtDtlsResp_result;
			
			return $returnarr;
			#return $bdgtDtlsResp;
		}		
		
	}
	
	
	function getbudgetinitialphonesearch()
	{
		$returnarr = array();
		$bdgtDtlsClsobj = new budgetDetailsClass($this->params);
		$bdgtDtlsResp = $bdgtDtlsClsobj->getBudget();

		$this->centraliselogging( $this->params,'Sunny Sir Budget Data PDG',null,json_encode( $bdgtDtlsResp));	
		
		$this->phonesearchwraperdistribution['catlist']= array();
		$this->phonesearchwraperdistribution['pinlist']= array();
		$this->phonesearchwraperdistribution['fixedpositionbudget']= 0;
		$this->phonesearchwraperdistribution['packagepositionbudget']= 0;		
		$this->phonesearchwraperdistribution['positionwise_pincode_number']= array();
		$this->phonesearchwraperdistribution['cat_pincode_wise_pos']= array();
		
		
		if($bdgtDtlsResp['error']['code']!=0)
		{
			return	$bdgtDtlsResp['error'];
		
		}else
		{
			# now we have to process the entire data of bdgtDtlsResp
			
			$bdgtDtlsResp_result = $bdgtDtlsResp['result'];
			$bdgtDtlsResp_result_c_data = $bdgtDtlsResp_result['c_data'];
			
			#unset c_data and store the rest of response 
			unset($bdgtDtlsResp_result['c_data']);
			
			$this->setBudgetWrapperSummary($bdgtDtlsResp_result);
			
			$c_data_without_pin_data = $this->setBudgetWrapperIntermediatephonesearch($bdgtDtlsResp_result_c_data);
			
			
			$bdgtDtlsResp_result['c_data']=$c_data_without_pin_data;
			
			
			if(DEBUG_MODE)
			{
				echo "<pre>phonesearchwraperdistribution---"; print_r($this->phonesearchwraperdistribution);
			}			
			
			
			$returnarr['result']=$bdgtDtlsResp_result;
			
			$this->phonesearchwraperdistribution['catlist'] = array_unique($this->phonesearchwraperdistribution['catlist']);
			$this->phonesearchwraperdistribution['pinlist'] = array_unique($this->phonesearchwraperdistribution['pinlist']);
			
			$returnarr['result']['wraperdistribution'] = $this->phonesearchwraperdistribution;
			
			return $returnarr;
			#return $bdgtDtlsResp;
		}
	}
	
	
	function updateBudgetWrapperTables($bdgtDtlAPIResult,$package_array=array(),$requestmode)
	{
		
		if($bdgtDtlAPIResult['error']['code']!=0)
		{
			return	$bdgtDtlAPIResult['error'];
		
		}else
		{
			# now we have to process the entire data of bdgtDtlAPIResult
			
			$bdgtDtlAPIResult_result = $bdgtDtlAPIResult['result'];
			$bdgtDtlAPIResult_result_c_data = $bdgtDtlAPIResult_result['c_data'];
			
			#unset c_data and store the rest of response 
			unset($bdgtDtlAPIResult_result['c_data']);
			
			$this->setBudgetWrapperSummary($bdgtDtlAPIResult_result);
			
			$c_data_without_pin_data = $this->setBudgetWrapperIntermediate($bdgtDtlAPIResult_result_c_data, $package_array,$requestmode);
			
			$bdgtDtlAPIResult_result['c_data']=$c_data_without_pin_data;
			
			$returnarr['result']=$bdgtDtlAPIResult_result;
			
			if(DEBUG_MODE)
			{
					echo '<br><b>updateBudgetWrapperTables :</b>-';			
					echo '<br><b>package_array</b>'; print_r($package_array);
					
					echo '<br><b>updateBudgetWrapperTables :</b>-';			
					echo '<br><b>returnarr</b>'; print_r($returnarr);
			}
			return $returnarr;
			#return $bdgtDtlAPIResult;
		}		
		
	}
	
	function getpindata()
	{
		
		if(DEBUG_MODE)
				{
					echo '<br><b>getpindata :</b>---';			
					echo '<br><b>$this->params</b>'; print_r($this->params);
				}
				
		$returnarr = array();
		
		#$catBudget 
		#$catid
		
		
		 
		
		if(trim($this->params['catlist'])== null)
		{
			$result['results'] = array();
			$result['error']['code'] = 1;	
			$result['error']['msg'] = "catlist needed for pin_data";
			
			return $result;
			
		}else
		{
			$catlist = $this->params['catlist'];
			
			
				
			$sql = " SELECT catid,cat_data,pin_data from tbl_budget_wrapper_intermediate where parentid='".$this->parentid."' and version='".$this->version."' and catid = '".$catlist."' ";
			
			$res = parent::execQuery($sql, $this->dbConbudget);
			
			if(DEBUG_MODE)
			{
				echo '<br><b>DB Query:</b>---'.$sql;			
				echo '<br><b>Error:</b>'.$this->mysql_error;
			}
			
			if( mysql_num_rows($res) )
			{
				$row = mysql_fetch_assoc($res);
				if (DEBUG_MODE) {
					print_r($row);
					echo $cat_data['flexi_bgt'];
				}
				$cat_data = json_decode($row['cat_data'],true);
				$pin_data = json_decode($row['pin_data'],true);
				
				
				$flexi_ml_fact=1;
				
				if( isset($this->params['catBudget']) &&  $this->params['catBudget'] > 0)
				{	
					if(isset($cat_data['flexi_bgt'])) {
						if(floor($cat_data['flexi_bgt'])==0)
						{
							$pin_data = $this->getFlexiPinPosMlbdgtforZeroBudget($pin_data,$this->params['catBudget'],0);
							
						}elseif( floor($this->params['catBudget']) != floor($cat_data['flexi_bgt']))
						{ 
							$flexi_ml_fact = ($this->params['catBudget'])/$cat_data['flexi_bgt'];
							$pin_data = $this->getFlexiPinPosMlbdgt($pin_data,$flexi_ml_fact,0);
						}
					} else {
						if (floor($cat_data['c_bgt']) == 0) {
							$pin_data = $this->getFlexiPinPosMlbdgtforZeroBudget($pin_data, $this->params['catBudget'],1);

						} elseif (floor($this->params['catBudget']) != floor($cat_data['c_bgt'])) {
							$flexi_ml_fact = ($this->params['catBudget']) / $cat_data['c_bgt'];
							
							$pin_data = $this->getFlexiPinPosMlbdgt($pin_data, $flexi_ml_fact,1);
						}
					}
				}				
				
				$returnarr[$row['catid']]['pin_data']= $pin_data;
				
			
			}else
			{				
				$result['error']['code'] = 1;	
				$result['error']['msg'] = "Data not found for catid - ".$catlist;
			}
			
			return $returnarr;
			
		}
	}
	
	function prepare_budgetjson()
	{
		$temparr = array();
		$returnarr= array();
		
		$completecatarry= array();
		
		$changedjson = $this->params['changedjson'];
		$changedjsoncatids= array();
		
		if(count($changedjson['c_data']))
		{
			$changedjsoncatids  = array_keys($changedjson['c_data']);
			
			#echo "<pre>changedjsoncatids--"; print_r($changedjsoncatids);
		}
		
		$budgetjsonPDGBudget=0;
		$budgetjsonPKGBudget=0;
		
		$sql = "select catid,cat_data,pin_data_final from tbl_budget_wrapper_intermediate where parentid= '".$this->parentid."' and version='".$this->version."' " ;
		$res = parent::execQuery($sql, $this->dbConbudget);
			
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		while($row = mysql_fetch_assoc($res))
		{
			
			array_push($completecatarry,$row['catid']);
			
			if(in_array($row['catid'],$changedjsoncatids))
			{
				$pin_data_final = array();
				$pin_data_final = json_decode($this->escapeJsonString( $row['pin_data_final']),true);


				
				
				#$cat_data = json_decode($row['cat_data'],true);
							
				#echo '<br> c_c_bgt--'.$changedjson['c_data'][$row['catid']]['c_c_bgt'];
				
				#echo '<br>pindata count--- '.count($changedjson['c_data'][$row['catid']]['pin_data']);
				
									
				if( isset($changedjson['c_data'][$row['catid']]['c_c_bgt']) && floatval($changedjson['c_data'][$row['catid']]['c_c_bgt'])>0 )
				{
					// budget has been changed , now we have to identify its pin_data is present or not 
					
					if( !isset($changedjson['c_data'][$row['catid']]['pin_data']) || count($changedjson['c_data'][$row['catid']]['pin_data'])==0) // if pin_data has not been passed
					{
							if( floor($changedjson['c_data'][$row['catid']]['c_c_bgt']) != floor($pin_data_final['c_bgt']))
							{
								$ml_fact = ($changedjson['c_data'][$row['catid']]['c_c_bgt'])/$pin_data_final['c_bgt'];								
									
									#echo "<br>-- catid--".$row['catid']."## ml_fact--".$ml_fact;
									foreach($pin_data_final['pin_data']  as $pincode=>$pincodeArr)
									{	
										
										foreach($pincodeArr['pos']  as $positionval =>$positionarr)
										{					
											
											if(floor($pin_data_final['c_bgt'])) // if category budget is set 
											{
												$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $positionarr['budget'] * $ml_fact;
												
											}											
											
										}
									}
							}
						
					
					}elseif(isset($changedjson['c_data'][$row['catid']]['pin_data']) && count($changedjson['c_data'][$row['catid']]['pin_data'])>=0) 
					{
						// if pin_data has been passed then we have to keep pin_data as it is for the pincodes passed , and rest of the pincode will be retained from table
						
						foreach($pin_data_final['pin_data']  as $pincode=>$pincodeArr)
						{	
							foreach($pincodeArr['pos']  as $positionval =>$positionarr)
							{	
								
								if(isset($changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget']))
								{
									$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget'];
								
								}else
								{
									#nothing to do for flexi , but may be handling require in pdg 
								}
									
																			
								
							}
						}
						
						
					}else
					{
						// default case , if none of the condition satisfy
						$pin_data_final = json_decode($this->escapeJsonString( $row['pin_data_final']),true); 
					}
					
					if(isset($changedjson['c_data'][$row['catid']]['c_c_bgt']) && ($changedjson['c_data'][$row['catid']]['c_c_bgt']>0))
					{
						$pin_data_final['c_bgt']= $changedjson['c_data'][$row['catid']]['c_c_bgt']; # upating c_bdgt
						
						#if flexi_bgt is set then it will be also get updated 
						
						if(isset($pin_data_final['flexi_bgt']) && floatval($pin_data_final['flexi_bgt'])>0)
						{
							$pin_data_final['flexi_bgt']= $changedjson['c_data'][$row['catid']]['c_c_bgt']; # upating c_bdgt
						}
						
						
					}
					
				}
							
				$temparr[$row['catid']] = $pin_data_final;
				
				
			}else
			{
				$temparr[$row['catid']] = json_decode( $this->escapeJsonString($row['pin_data_final']),true);
			}
			
			#if removedPin has been passed then remove that pincode
			if(isset($changedjson['c_data'][$row['catid']]['removedPin']) && count($changedjson['c_data'][$row['catid']]['removedPin'])>0) 
			{
				$removedPinArr =  $changedjson['c_data'][$row['catid']]['removedPin'];
				
				foreach($removedPinArr as $indx=>$removedpin)
				{
					unset($temparr[$row['catid']]['pin_data'][$removedpin]);
				}
			}
			
			
			
		}
		
		
		// Traverse $temparr to get total pdg and pacakge budget 
		
		# collecting non zero budget category 
		
		$nonzerobdgtcatarry = array();
		
		
		foreach ($temparr as $catid=>$catidarr)
		{
			foreach($catidarr['pin_data']  as $pincode=>$pincodeArr)
			{	
				
				foreach($pincodeArr['pos']  as $positionval =>$positionarr)
				{					
					
					if(intval($positionval)==100)
					{
						$budgetjsonPKGBudget = $budgetjsonPKGBudget + $positionarr['budget'] ;
						
					}else
					{
						$budgetjsonPDGBudget = $budgetjsonPDGBudget + $positionarr['budget'];
					}
					
					
					if($positionarr['budget']>0)
					{
						array_push($nonzerobdgtcatarry,$catid);
					}
					
					
				}
			}
		}	
		
		
		
		$nonzerobdgtcatarry = array_unique($nonzerobdgtcatarry);
		
		$zerobdgtcatarry = array();
		
		$zerobdgtcatarry = array_diff($completecatarry,$nonzerobdgtcatarry);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>completecatarry</b>---'; print_r($completecatarry);
			echo '<br><b>nonzerobdgtcatarry</b>---'; print_r($nonzerobdgtcatarry);
			echo '<br><b>zerobdgtcatarry</b>---'; print_r($zerobdgtcatarry);
		
		}
		
		#unseting all the categories where budget is 0
		foreach($zerobdgtcatarry as $ind=> $zerobdgtcatval) 
		{
			unset($temparr[$zerobdgtcatval]);
		}
		
		
		
		$returnarr['c_data'] = $temparr;
		$returnarr['pdgBudget']		= $budgetjsonPDGBudget;
		$returnarr['packageBudget'] = $budgetjsonPKGBudget;
		
		#echo '<br><b>$returnarr</b>---'; print_r($returnarr);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>$returnarr</b>---'; print_r($returnarr);
		
		}
			
		return $returnarr;	
		
	}
	
	
	
	function prepare_budgetjsonphonesearch()
	{
		$temparr = array();
		$returnarr= array();
		
		$completecatarry= array();
		
		$changedjson = $this->params['changedjson'];
		
		$changedjsoncatids= array();
		
		if(count($changedjson['c_data']))
		{
			$changedjsoncatids  = array_keys($changedjson['c_data']);
			
			#echo "<pre>changedjsoncatids--"; print_r($changedjsoncatids);
		}
		
		$budgetjsonPDGBudget=0;
		$budgetjsonPKGBudget=0;
		
		$sql = "select catid,cat_data,pin_data_final from tbl_budget_wrapper_intermediate where parentid= '".$this->parentid."' and version='".$this->version."' " ;
		$res = parent::execQuery($sql, $this->dbConbudget);
			
		if(DEBUG_MODE)
		{
			echo '<br><b>DB Query:</b>---'.$sql;			
			echo '<br><b>Error:</b>'.$this->mysql_error;
		}
		
		while($row = mysql_fetch_assoc($res))
		{
			
			array_push($completecatarry,$row['catid']);
			
			if(in_array($row['catid'],$changedjsoncatids))
			{
				$pin_data_final = array();
				$pin_data_final = json_decode( $this->escapeJsonString($row['pin_data_final']),true);
				
				if( isset($changedjson['c_data'][$row['catid']]['c_c_bgt']) && floatval($changedjson['c_data'][$row['catid']]['c_c_bgt'])>0 )
				{
					// budget has been changed , now we have to identify its pin_data is present or not 
					
					if( !isset($changedjson['c_data'][$row['catid']]['pin_data']) || count($changedjson['c_data'][$row['catid']]['pin_data'])==0) // if pin_data has not been passed
					{
							if( floor($changedjson['c_data'][$row['catid']]['c_c_bgt']) != floor($pin_data_final['c_bgt']))
							{	
								if($changedjson['c_data'][$row['catid']]['cat_old_pdg_budget'] > 0) {
									$changedPDG_fact = ($changedjson['c_data'][$row['catid']]['cat_pdg_budget'])/ $changedjson['c_data'][$row['catid']]['cat_old_pdg_budget'];
								} else {
									$changedPDG_fact = 1;
								}
								
								if($changedjson['c_data'][$row['catid']]['cat_old_package_budget'] > 0){
									$changedPack_fact = ($changedjson['c_data'][$row['catid']]['cat_package_budget'])/ $changedjson['c_data'][$row['catid']]['cat_old_package_budget'];
								}else{
									$changedPack_fact = 1;
								}
									
									#echo "<br>-- catid--".$row['catid']."## ml_fact--".$ml_fact;
									foreach($pin_data_final['pin_data']  as $pincode=>$pincodeArr)
									{	
										
										foreach($pincodeArr['pos']  as $positionval =>$positionarr)
										{					
											
											if(floor($pin_data_final['c_bgt'])) // if category budget is set 
											{
												if( $positionval == 100) {
													$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $positionarr['budget'] * $changedPack_fact;
												} else {
													$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $positionarr['budget'] * $changedPDG_fact;
												}
												
											}											
											
										}
									}
							}
						
					
					}elseif(isset($changedjson['c_data'][$row['catid']]['pin_data']) && count($changedjson['c_data'][$row['catid']]['pin_data'])>=0) 
					{
						// if pin_data has been passed then we have to keep pin_data as it is for the pincodes passed , and rest of the pincode will be retained from table
						foreach($pin_data_final['pin_data']  as $pincode=>$pincodeArr)
						{	
							foreach($pincodeArr['pos']  as $positionval =>$positionarr)
							{	
								/* if(isset($changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget']))
								{
									$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget'];
								
								}else
								{
									echo "dataset--".$row['catid']."--". $pincode."--". $positionval."<br>";
									#nothing to do for flexi , but may be handling require in pdg 
									//$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget'];
								} */
								if (isset($changedjson['c_data'][$row['catid']]['pin_data'][$pincode])) {
									$posKey = array_keys($changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos']);
									if ($positionval	!= $posKey[0]) {
										unset($pin_data_final['pin_data'][$pincode]['pos'][$positionval]);
										$pin_data_final['pin_data'][$pincode]['pos'][$posKey[0]]['budget'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$posKey[0]]['budget'];
										$pin_data_final['pin_data'][$pincode]['pos'][$posKey[0]]['bidvalue'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$posKey[0]]['bidvalue'];
										$pin_data_final['pin_data'][$pincode]['pos'][$posKey[0]]['inventory'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$posKey[0]]['inventory'];
									} else {
										$pin_data_final['pin_data'][$pincode]['pos'][$positionval]['budget'] = $changedjson['c_data'][$row['catid']]['pin_data'][$pincode]['pos'][$positionval]['budget'];
									}
								}
							}
						}
						
						
					}else
					{
						// default case , if none of the condition satisfy
						$pin_data_final = json_decode( $this->escapeJsonString($row['pin_data_final']),true); 
					}
					
					if(isset($changedjson['c_data'][$row['catid']]['c_c_bgt']) && ($changedjson['c_data'][$row['catid']]['c_c_bgt']>0))
					{
						$pin_data_final['c_bgt']= $changedjson['c_data'][$row['catid']]['c_c_bgt']; # upating c_bdgt
						
						#if flexi_bgt is set then it will be also get updated 
						
						if(isset($pin_data_final['flexi_bgt']) && floatval($pin_data_final['flexi_bgt'])>0)
						{
							$pin_data_final['flexi_bgt']= $changedjson['c_data'][$row['catid']]['c_c_bgt']; # upating c_bdgt
						}
						
						
					}
					
				}
							
				$temparr[$row['catid']] = $pin_data_final;
				
				
			}else
			{
				$temparr[$row['catid']] = json_decode( $this->escapeJsonString($row['pin_data_final']),true);
			}
			#if removedPin has been passed then remove that pincode
			if(isset($changedjson['c_data'][$row['catid']]['removedPin']) && count($changedjson['c_data'][$row['catid']]['removedPin'])>0) 
			{
				$removedPinArr =  $changedjson['c_data'][$row['catid']]['removedPin'];
				
				foreach($removedPinArr as $indx=>$removedpin)
				{
					unset($temparr[$row['catid']]['pin_data'][$removedpin]);
				}
			}
        }
        if(isset($this->params['removeCatStr']) && $this->params['removeCatStr'] != "") {
            $removedCatArr    =    explode(",",$this->params['removeCatStr']);
            foreach($removedCatArr as $indx=>$removedCat) {
				unset($temparr[$removedCat]);
				
            }
		} 
		# skippackage handling , we will remove all package position 		
		if (isset($this->params['skippackage']) && $this->params['skippackage'] == 1) {
			
			foreach ($temparr as $catid => $catidarr) {
				foreach ($catidarr['pin_data'] as $pincode => $pincodeArr) {

					foreach ($pincodeArr['pos'] as $positionval => $positionarr) {
						if (intval($positionval) == 100) {
							//$temparr[$catid]['c_bgt'] = $temparr[$catid]['c_bgt'] - $positionarr['budget'];
							unset($temparr[$catid]['pin_data'][$pincode]);
						}
					}
				}
			}
		} else {
			# oldpackageBudget handling for custom budget -- start
			if (DEBUG_MODE) {
				echo '<pre>temparr';
				print_r($temparr);
			}
			# oldpackageBudget handling for custom budget -- end
		}
		
		# skippackage handling end
		
		// Traverse $temparr to get total pdg and pacakge budget 		
		
		# collecting non zero budget category 
		
		$nonzerobdgtcatarry = array();
		
		
		foreach ($temparr as $catid=>$catidarr)
		{
			foreach($catidarr['pin_data']  as $pincode=>$pincodeArr)
			{	
				
				foreach($pincodeArr['pos']  as $positionval =>$positionarr)
				{					
					
					if(intval($positionval)==100)
					{
						$budgetjsonPKGBudget = $budgetjsonPKGBudget + $positionarr['budget'] ;
						
					}else
					{
						$budgetjsonPDGBudget = $budgetjsonPDGBudget + $positionarr['budget'];
					}
					
					
					if($positionarr['budget']>0)
					{
						array_push($nonzerobdgtcatarry,$catid);
					}
					
					
				}
			}
		}	
		
		
		
		$nonzerobdgtcatarry = array_unique($nonzerobdgtcatarry);
		
		$zerobdgtcatarry = array();
		
		$zerobdgtcatarry = array_diff($completecatarry,$nonzerobdgtcatarry);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>completecatarry</b>---'; print_r($completecatarry);
			echo '<br><b>nonzerobdgtcatarry</b>---'; print_r($nonzerobdgtcatarry);
			echo '<br><b>zerobdgtcatarry</b>---'; print_r($zerobdgtcatarry);
		
		}
		
		#unseting all the categories where budget is 0
		foreach($zerobdgtcatarry as $ind=> $zerobdgtcatval) 
		{
			unset($temparr[$zerobdgtcatval]);
		}
		
		
		
		$returnarr['c_data'] = $temparr;
		$returnarr['pdgBudget']		= $budgetjsonPDGBudget;
		$returnarr['packageBudget'] = $budgetjsonPKGBudget;
		
		#echo '<br><b>$returnarr</b>---'; print_r($returnarr);
		
		if(DEBUG_MODE)
		{
			echo '<br><b>$returnarr</b>---'; print_r($returnarr);
		
		}
			
		return $returnarr;	
		
	}
	
	function submitbudgetphonesearch()
	{	
		
		#echo "<pre>";
		#print_r($this->params);
		
		# ( (!isset($this->params['packageBudget']) || $this->params['packageBudget']==null) || (!isset($this->params['pdgBudget'] ) || $this->params['pdgBudget'] ==null) || !isset($this->params['actual_bgt']) || $this->params['actual_bgt']==null)   
		#print_r($this->params)	;
		
		if( ( (!isset($this->params['totBudget']) || $this->params['totBudget'] ==null) || (!isset($this->params['city_bgt'])) ||  empty($this->params['tenure']) ))
		{
			$result['results'] = array();
			$result['error']['code'] = 1;	
			$result['error']['msg'] = "Budget parameters missing";
			
			return $result;
		}
		/*else
		{
			$result['results'] = array();
			$result['error']['code'] = 0;	
			$result['error']['msg'] = "submitbudget successful";
		}
		
		*/
		$budgetjson_val = $this->prepare_budgetjsonphonesearch();
		
		$this->params['duration'] = $this->params['tenure'];
		$this->params['usercode'] = $this->params['usercode'];
		
		#totBudget packageBudget and pdgBudget will be present on geniolite so we are accepting these 3 nodes 
		$budgetjson_val['totBudget'] 	 = $this->params['totBudget'];
		$budgetjson_val['packageBudget'] = $this->params['packageBudget']; 
		$budgetjson_val['pdgBudget'] 	 = $this->params['pdgBudget'];		
		$budgetjson_val['actual_bgt'] 	 = $this->params['actual_bgt'];		
		$budgetjson_val['city_bgt'] 	= $this->params['city_bgt'];
		$budgetjson_val['tenure']		= $this->params['tenure'];
		

		if(isset($this->params['reg_bgt']))
		{
			$budgetjson_val['reg_bgt']= $this->params['reg_bgt'];
		}
		
		
		if(isset($this->params['removeCatStr']))
		{
			$budgetjson_val['removeCatStr']= $this->params['removeCatStr'];
		}

		if(isset($this->params['nonpaidStr']))
		{
			$budgetjson_val['nonpaidStr']= $this->params['nonpaidStr'];
		}
		
		$this->params['budgetjson']= $budgetjson_val;
		
		
		
		$budgetsubmitobj = new budgetsubmitclass($this->params);
		$dataSubRet =	$budgetsubmitobj->submitbudget();

		if(DEBUG_MODE){
			echo "exact_ren_check";
			print_r($dataSubRet);
		}
		
		#$result['error']['code'] = 0;
		#$result['results']['msg'] = "submitbudget successful";
	
		return $dataSubRet;
		
	}
	
	function submitbudget()
	{	
		
		#echo "<pre>";
		#print_r($this->params);
		
		# ( (!isset($this->params['packageBudget']) || $this->params['packageBudget']==null) || (!isset($this->params['pdgBudget'] ) || $this->params['pdgBudget'] ==null) || !isset($this->params['actual_bgt']) || $this->params['actual_bgt']==null)   
		#print_r($this->params)	;
		
		if( ( (!isset($this->params['totBudget']) || $this->params['totBudget'] ==null) || (!isset($this->params['city_bgt'])) ||  empty($this->params['tenure']) ))
		{
			$result['results'] = array();
			$result['error']['code'] = 1;	
			$result['error']['msg'] = "Budget parameters missing";
			
			return $result;
		}
		/*else
		{
			$result['results'] = array();
			$result['error']['code'] = 0;	
			$result['error']['msg'] = "submitbudget successful";
		}
		
		*/
		$budgetjson_val = $this->prepare_budgetjson();
		$this->params['duration'] = $this->params['tenure'];
		$this->params['usercode'] = $this->params['usercode'];
		
		#$budgetjson_val['packageBudget'] = $this->params['packageBudget'];
		#$budgetjson_val['pdgBudget'] 	 = $this->params['pdgBudget'];
		
		$budgetjson_val['actual_bgt'] 	 = $this->params['actual_bgt'];
		$budgetjson_val['totBudget'] 	 = $this->params['totBudget'];
		$budgetjson_val['city_bgt'] 	= $this->params['city_bgt'];
		$budgetjson_val['tenure']		= $this->params['tenure'];
		
		
		#packageBudget=12000&pdgBudget=24000&totBudget=36000&city_bgt=36000&actual_bgt=36000
		
		
		if(isset($this->params['customBudget']))
		{
			$budgetjson_val['customBudget']= $this->params['customBudget'];
		}
		

		if(isset($this->params['customBudget']))
		{
			$budgetjson_val['customBudget']= $this->params['customBudget'];
		}
		
		if(isset($this->params['reg_bgt']))
		{
			$budgetjson_val['reg_bgt']= $this->params['reg_bgt'];
		}
		
		
		if(isset($this->params['removeCatStr']))
		{
			$budgetjson_val['removeCatStr']= $this->params['removeCatStr'];
		}

		if(isset($this->params['nonpaidStr']))
		{
			$budgetjson_val['nonpaidStr']= $this->params['nonpaidStr'];
		}
		
		$this->params['budgetjson']= $budgetjson_val;
		
		
		
		$budgetsubmitobj = new budgetsubmitclass($this->params);
		$bdgtresparr = $budgetsubmitobj->submitbudget();	
		
		
		#$result['error']['code'] = 0;
		#$result['results']['msg'] = "submitbudget successful";
		
		if($bdgtresparr['error_code']!=0)
		{
			$result['error_code']=$bdgtresparr['error_code'];
			$result['message']=$bdgtresparr['message'];
			
		}else
		{	
			$this->updatepaidnonpaidcategories();
			$result['error_code']=0;
			$result['message']='Sucess';
			$result['exact_renewal']=0;
		}
		
		return $result;
		
	}
	

	
	function get_category_details($catids)
	{
		$sql = "select category_name, national_catid, catid, if((business_flag&1)=1,1,0) as b2b_flag,  if((category_type&64)=64,1,0) as block_for_contract 
	from tbl_categorymaster_generalinfo where catid in (" . $catids . ") AND biddable_type=1";
		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>Category Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
			//print_r($row);
				$catid = $row['catid'];
				$ret_array[$catid]['cnm'] = $row['category_name'];
				$ret_array[$catid]['cid'] = $row['catid'];
				$ret_array[$catid]['nid'] = $row['national_catid'];
				$ret_array[$catid]['b2b_flag'] = $row['b2b_flag'];
				$ret_array[$catid]['bfc'] = $row['block_for_contract'];
			}
		}
		return ($ret_array);
	}
	
	function getcurrenttempcategories()
	{
		$rerutnarr = array();
		$mongo_inputs = array();
		$mongo_inputs['parentid'] = $this->parentid;
		$mongo_inputs['data_city'] = $this->params['data_city'] ;
		$mongo_inputs['module'] = 'ME'; // currently it is for website manage campaing so we can are using ME
		$mongo_inputs['table'] = "tbl_business_temp_data";
		$mongo_inputs['fields'] = "catIds";
		$catid_arr = $this->mongo_obj->getData($mongo_inputs);

		$catid_str = '';
		$rerutnarr = explode('|P|', trim($catid_arr['catIds'], '|P|'));



		if (DEBUG_MODE) {
			echo '<br>inside getcurrenttempcategories <br> mongo_inputs';
			print_r($mongo_inputs);
			echo '<br> mongo output ';
			print_r($catid_arr);
			echo '<br> rerutnarr ';
			print_r($rerutnarr);
		}

		return $rerutnarr;
	}
	
	 
	function gettblcompanymasterextradetailsshadow($fields='*')
	{
		$rerutnarr = array();
		$mongo_inputs = array();
		$mongo_inputs['parentid'] = $this->parentid;
		$mongo_inputs['data_city'] = $this->params['data_city'] ;
		$mongo_inputs['module'] = $this->params['module'];
		$mongo_inputs['table'] = "tbl_companymaster_extradetails_shadow";
		$mongo_inputs['fields'] = $fields;
		$rerutnarr = $this->mongo_obj->getData($mongo_inputs);

		
		if (DEBUG_MODE) {
			echo '<br>inside gettblcompanymasterextradetailsshadow <br> mongo_inputs';
			print_r($mongo_inputs);
			echo '<br> mongo output ';
			print_r($rerutnarr);
		}

		return $rerutnarr;
	}
	
	function updatepaidnonpaidcategories()
	{	
		
		# updating tbl_business_temp_data data 
		$mongo_inputs = array();
		$intermd_upt = array();
			
		$mongo_inputs['module']       	= $this->params['module'];
		$mongo_inputs['parentid']       = $this->params['parentid'] ;
		$mongo_inputs['data_city']      = $this->params['data_city'] ;
		$intermd_tbl 					= "tbl_business_temp_data";
		$intermd_upt['contractid'] 		= $this->parentid;
		$intermd_upt['catIds'] 			= str_replace(',','|P|',$this->params['pcatid']);
		$intermd_upt['categories'] 		=  str_replace(',','|P|',$this->params['pcatname']);
		
		$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
		$mongo_inputs['table_data'] 	= $mongo_data;
		$res = $this->mongo_obj->updateData($mongo_inputs);
		
		
		# updating tbl_business_temp_data data 
		$intermd_tbl 					= "tbl_companymaster_extradetails_shadow";
		$intermd_upt = array();		
		$intermd_upt['catidlineage_nonpaid'] 			= '/'.str_replace(',','/,/',$this->params['npcatid']).'/';
		
		$mongo_data[$intermd_tbl]['updatedata'] = $intermd_upt;
		$mongo_inputs['table_data'] 	= $mongo_data;
		$res = $this->mongo_obj->updateData($mongo_inputs);		
	}
	
		
	function getbudgetinitialmanagecampaign()
	{
		
		$MGCResultarray= array();
		
		
		$this->currenttempcategories = $this->getcurrenttempcategories();
		$extradetailsshadow_catidlineage_nonpaid = $this->gettblcompanymasterextradetailsshadow('catidlineage_nonpaid');
		$catidlineage_nonpaid_str= str_replace('/','',$extradetailsshadow_catidlineage_nonpaid['catidlineage_nonpaid']);
		
		$shadow_catidlineage_nonpaid_arr =  explode(',', str_replace('/','',$catidlineage_nonpaid_str));
		
		if (DEBUG_MODE) {
			echo '<br><b>catidlineage_nonpaid_str</b>' . $catidlineage_nonpaid_str;
			echo '<br><b>shadow_catidlineage_nonpaid_arr</b>' ;print_r($shadow_catidlineage_nonpaid_arr);
			
		}
		$this->tbl_companymaster_finance_duration = 0;
		#finance details 
		
		$sql = "select campaignid,budget,duration,balance,version,bid_perday,expired,(balance/bid_perday) as remaining_tenure  from tbl_companymaster_finance where parentid ='" . $this->parentid . "' and campaignid in (1,2) and balance>0 ORDER BY campaignid";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		$finance_live_total_budget = 0;
		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;

		}

		if ($res && $num > 0) 
		{
			$ecs_edit = true;
			$get_ecs_status = "SELECT parentid,billdeskid FROM db_ecs.ecs_mandate WHERE parentid='" . $this->parentid . "' AND deactiveflag = 0 AND ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1  UNION SELECT outlet_parentid,master_billdeskid from db_ecs.ecs_mandate_outlet WHERE outlet_parentid='" . $this->parentid . "' AND outlet_status IN (0,1) AND vertical_flag=0 LIMIT 1";
			$res_ecs_status = parent::execQuery($get_ecs_status, $this->finance);
			if ($res_ecs_status && mysql_num_rows($res_ecs_status)) {
				$row_ecs_status = mysql_fetch_assoc($res_ecs_status);
				$ecs_edit = false;
			} else {
				$get_si_status = "SELECT parentid,billdeskid FROM db_si.si_mandate WHERE parentid='" . $this->parentid . "' and deactiveflag = 0 and ecs_stop_flag = 0 and vertical_flag=0 LIMIT 1 ";
				$res_si_status = parent::execQuery($get_si_status, $this->finance);
				if ($res_si_status && mysql_num_rows($res_si_status)) {
					$row_si_status = mysql_fetch_assoc($res_si_status);
					$ecs_edit = false;
				}
			}
			$ecs_flag = 0;
			if ($ecs_edit) {
				$ecs_flag = 0;
			} else {
				$ecs_flag = 1;
			}
			
			while ($row = mysql_fetch_assoc($res)) 
			{			
				$campaignid = (int)$row['campaignid'];
				$MGCResultarray['result']['finance']['data'][$campaignid]['campaignid'] = $campaignid;
				$MGCResultarray['result']['finance']['data'][$campaignid]['budget'] = floatval($row['budget']);
				$MGCResultarray['result']['finance']['data'][$campaignid]['tenure'] = intval($row['duration']);				
				$MGCResultarray['result']['finance']['data'][$campaignid]['balance'] = floatval($row['balance']);
				$MGCResultarray['result']['finance']['data'][$campaignid]['version'] = floatval($row['version']);
				$MGCResultarray['result']['finance']['data'][$campaignid]['bid_perday'] = floatval($row['bid_perday']);
				$MGCResultarray['result']['finance']['data'][$campaignid]['remaining_tenure'] = floatval($row['remaining_tenure']);
				$MGCResultarray['result']['finance']['data'][$campaignid]['expired'] = $row['expired'];
				$MGCResultarray['result']['finance']['data'][$campaignid]['ecs_flag'] = $ecs_flag;
				
				if($this->tbl_companymaster_finance_duration < intval($row['duration']))
				{
					$this->tbl_companymaster_finance_duration = intval($row['duration']);
				}
				
				$finance_live_total_budget += floatval($row['budget']);		
				
			}
		}
		
		#Budget details 
		$datasource= null;
		
	
			#Fetching category and pincode from shadow and live tables to find out list of added/removed categories/pincodes 
		
		$catid_array	= array();
		$pincode_array	= array();
		
		$sql = "select group_concat(distinct catid) as catidlist,count(distinct catid) as catcount,group_concat(distinct pincode) as pincodelist,version from tbl_bidding_details_shadow where parentid='" . $this->parentid . "' ";
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}
		
		$row = mysql_fetch_assoc($res);
		if ($res && $row['catcount'] > 0) 
		{	
			$datasource = "shadow";
			$cnt = 0;
			$pendingversion = 0;
			$paduration = 0;
			$pabudget = 0;
			$shadowtotalbudget = 0;
			
			
			$version = $pendingversion = $row['version'] ;
			$catid_array = explode(',',$row['catidlist']);
			$pincode_array = explode(',',$row['pincodelist']);
		
		}
		
		// if there is no data on shadow 
		if($datasource==null) 
		{
			$sql = "select group_concat(distinct catid) as catidlist,count(distinct catid) as catcount,group_concat(distinct pincode) as pincodelist,version from tbl_bidding_details where parentid='" . $this->parentid . "' ";
			$res = parent::execQuery($sql, $this->finance);
			$num = mysql_num_rows($res);

			if (DEBUG_MODE) {
				echo '<hr><b>Live DB Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
				echo '<hr>';
			}
			
			$row = mysql_fetch_assoc($res);
			if ($res && $row['catcount'] > 0) 
			{	
				$datasource = "live";
				$cnt = 0;
				$pendingversion = 0;
				$paduration = 0;
				$pabudget = 0;
				$shadowtotalbudget = 0;
				
				
				$version = $pendingversion = $row['version'] ;
				$catid_array = explode(',',$row['catidlist']);
				$pincode_array = explode(',',$row['pincodelist']);
			
			}
			
		}
		
		if($datasource == null){
			$result_array['error']['code'] = "1";
			$result_array['error']['msg'] = "data not found in bidding details tables campaign ";
			return $result_array;
			die();
		}else
		{
			
			if (count($catid_array) > 0) 
			{
				$pincode_array = array_unique($pincode_array);
				$pincode_list = implode(",", $pincode_array);

				$catid_array = array_unique($catid_array);
				$catid_list = implode(",", $catid_array);

				$cat_array = $this->get_category_details($catid_list);
				$pin_array = $this->get_pincode_details($pincode_list);

				#$catid_added = array_diff($this->currenttempcategories, $catid_array);
				#$catid_removed = array_diff($catid_array,$this->currenttempcategories);
				
				 
				
				$catid_added = array_diff(array_merge($shadow_catidlineage_nonpaid_arr,$this->currenttempcategories), $catid_array);
				$catid_removed = array_diff($catid_array,array_merge($shadow_catidlineage_nonpaid_arr,$this->currenttempcategories));
				
				$newcatidList = "";

				if (count($catid_added) > 0) {
					$newcatidList = implode(",", $catid_added);
					$newcatidList= trim($newcatidList,',');
				}
				
				if (count($catid_removed) > 0) {
					$this->manage_campaign_removedlivecategories = $catid_removed;
				}
				
				if (count($catid_added) > 0 || count($shadow_catidlineage_nonpaid_arr) ) {
					
					$this->manage_campaign_addeded_or_shadowcatidlineagenonpaid_categories = array_filter(array_unique(array_merge($catid_added,$shadow_catidlineage_nonpaid_arr)));
				}


				if (DEBUG_MODE) {
					
					echo '<br><b>this->currenttempcategories</b>' ;print_r($this->currenttempcategories);
					echo '<br><b>shadow_catidlineage_nonpaid_arr</b>' ;print_r($shadow_catidlineage_nonpaid_arr);
					echo '<br><b>catid_array</b>' ;print_r($catid_array);
					echo '<br><b>catid_added</b>' ;print_r($catid_added);
					echo '<br><b>catid_removed</b>' ;print_r($catid_removed);
					
					echo '<br><b>manage_campaign_addeded_or_shadowcatidlineagenonpaid_categories</b>' ;print_r($this->manage_campaign_addeded_or_shadowcatidlineagenonpaid_categories);
					
					echo '<br><b>newcatidList--</b>'.$newcatidList;
					
				}
				
				
				$package_array = $this->fn_package_bidders(implode(",", $this->currenttempcategories), $pincode_list);
				
				$version= $this->version; # budget init got started to call now before manage campaing so we will use passed version instead of old version Jan 25 
				$BudgetDetailsManageCampaignArr =   $this->ManageCampaignBudgetDetails($version, $newcatidList);
				
				$budgetdetailprocessedres =  $this->updateBudgetWrapperTables($BudgetDetailsManageCampaignArr,$package_array,'managecampaign');
				
				$MGCResultarray['result']['c_data'] = $budgetdetailprocessedres['result']['c_data'];
				
				
				
			}
			
			
			$MGCResultarray['result']['datasource'] = $datasource;
			$MGCResultarray['result']['currenttempcategories'] = implode(',', $this->currenttempcategories);
			$MGCResultarray['result']['catidlineage_nonpaid'] = implode(',', $shadow_catidlineage_nonpaid_arr);
			
			$MGCResultarray['result']['currentliveshadowcategories'] = $catid_list;
			
			if (count($catid_added))
				$MGCResultarray['result']['addedtempcategories'] = implode(',', $catid_added);
			else
				$MGCResultarray['result']['addedtempcategories'] = '';

			if (count($catid_removed))
				$MGCResultarray['result']['removedlivecategories'] = implode(',', $catid_removed);
			else
				$MGCResultarray['result']['removedlivecategories'] = '';

			$payment_type_dealclosed = $this->fn_tbl_payment_type_dealclosed($version, $MGCResultarray['result']['finance']['data']);
			$MGCResultarray['result']['tbl_payment_type_dealclosed'] = $payment_type_dealclosed;
			
			
			$MGCResultarray['result']['tb_flexi_bgt']			=$this->Tot_flexi_budget_arr_catwise['sum'];
			
			$this->updatetbflexibgtBdgetWrperSry($this->Tot_flexi_budget_arr_catwise['sum'],$this->parentid,$this->params['version']);
			
			
			
			$MGCResultarray['result']['city_bgt']=0;

			$MGCResultarray['error']['code'] = "0";
			$MGCResultarray['error']['msg'] = "No error";

			
			
		}
		return $MGCResultarray;
			
		

	}
	
	# This function is for actual buget details api call 
	function callActualBudgetDetailsAPI($budgetDetailsParams,$newCatidList=null)
	{
		if( !is_array($budgetDetailsParams) || count($budgetDetailsParams)==0 )
		{
			$result['result'] = array();
			$result['error']['code'] = 1;
			$result['error']['msg'] = "budgetDetailsParams not passed on callActualBudgetDetailsAPI ";
			$resultstr= json_encode($result);
			print($resultstr);
			die;
		}
		
		$budgetDetailsClass_obj = new budgetDetailsClass($budgetDetailsParams);
		$result = $budgetDetailsClass_obj->getBudget($newCatidList);
		
		return $result;
	}
	
	function getBudgetCalculationParams($version,$requestmode)
	{
		$BdgtCallparams['data_city'] = $this->params['data_city'];
		$BdgtCallparams['parentid'] = $this->params['parentid'];
		$BdgtCallparams['version'] = $version;
		$BdgtCallparams['tenure'] = 12;
		#$BdgtCallparams['mode'] = 4; // initialize mode 1-best positon 2-fixed position 3-package 4-renewal 5-exclusive
		#$BdgtCallparams['option'] = 1; // default 1, max 7


		if($requestmode=='managecampaign')
		{
			$BdgtCallparams['mode'] = 6; 
			#$BdgtCallparams['mode'] = 1; 
			$BdgtCallparams['option'] = 1;
			$BdgtCallparams['glcpb'] = 1;
		}

		if (DEBUG_MODE) {
			echo '<br><b>getBudgetCalculationParams</b> <br>';
			print_r($BdgtCallparams); // uncomment
		}
		return $BdgtCallparams;

	}
	
	function ManageCampaignBudgetDetails($version, $newCatidList)
	{
		$BdgtCallparams = $this->getBudgetCalculationParams($version,'managecampaign');
		
		$result = $this->callActualBudgetDetailsAPI($BdgtCallparams,$newCatidList);

		if (DEBUG_MODE) {
			echo '<br><b>new catid list</b> <br>';
			print_r($newCatidList); // uncomment		
			echo '<br><b>budgetDetailsAPIresult</b> <br>';
			print_r($result); // uncomment		
		}	
	
		return $result;

	}
	
	

	function fn_package_bidders($catids, $pincodes)
	{
		if (DEBUG_MODE) {
			echo '<br>fn: fn_package_bidders($cat_array,$pin_array)';
			echo '<br>fn: category list :' . $catids;
			echo '<br>fn: pincode list :' . $pincodes;
		}
		$package_bidder = array();
		$package_bid_array = array();
		$sql = "select parentid, companyname, catid, pincode, round(search_contribution,2) as search_contribution, round(bidperday,2) as bidperday, 
		round(contract_bidperday,2) as contract_bidperday, physical_pincode, physical_area as area, active_date, category_count, pincode_count
		from db_iro.tbl_package_search where catid in (" . $catids . ")  and data_city='" . $this->params['data_city'] . "'  and pincode in (" . $pincodes . ") order by catid, pincode, bidperday desc";
		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>PS Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
				//print_r($row);
				$catid = $row['catid'];
				$pincode = $row['pincode'];
				$parentid = $row['parentid'];

				$data['p'] = $row['parentid'];
				$data['c'] = $row['companyname'];
				$data['sc'] = $row['search_contribution'];
				$data['bpd'] = $row['bidperday'];
				$data['c_bpd'] = $row['contract_bidperday'];
				$data['p_p'] = $row['physical_pincode'];
				$data['p_a'] = $row['area'];
				$data['c_c'] = $row['category_count'];
				$data['p_c'] = $row['pincode_count'];
				$data['a_d'] = $row['active_date'];

				$package_bidder[$catid][$pincode][] = $data;
				$package_bid_array[$catid][$pincode][] = $row['bidperday'];
				$package_sc_array[$catid][$pincode][] = $row['search_contribution'];
			}
		}

		$return_array['bidder'] = $package_bidder;
		$return_array['bid'] = $package_bid_array;
		$return_array['sc'] = $package_sc_array;
		#$return_array['cat_bidder'] = $category_bidder;
		#$return_array['cat_bid'] = $category_bid_array;

		return ($return_array);
	}


	function fn_tbl_payment_type_dealclosed($version, $financedata,$paymenttypename=0)
	{
		$result = array();
		
		$sql = 	"select ifnull(payment_type ,'') as payment_type ,ifnull(payment_type_flag,'') as payment_type_flag from (
			select group_concat(payment_type separator ',') as payment_type , group_concat(payment_type_flag separator ',') as payment_type_flag from tbl_payment_type_dealclosed where parentid='" . $this->parentid . "' 
			)a;";

		#$sql = "select group_concat(payment_type separator ',') as payment_type , group_concat(payment_type_flag separator ',') as payment_type_flag from tbl_payment_type_dealclosed where parentid='" . $this->parentid . "' ";

		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($num > 0) {
			$resarr= mysql_fetch_assoc($res);			
			$result['payment_type'] = $resarr['payment_type'];
			$result['payment_type_flag'] = $resarr['payment_type_flag'];
			
			if($paymenttypename)
			{
				$tblpaymenttypemaster = $this->tbl_payment_type_master($resarr['payment_type']);
				$result['campaign_name'] = $tblpaymenttypemaster['campaign_name'];				
			}
			
			
		}else {
				foreach ($financedata as $campaignidval => $campaignidarray) {
					if ($campaignidval == 2) {
						$result['payment_type'] = 'PDG';
						$result['payment_type_flag'] = '1';
					} elseif ($campaignidval == 1 && count($result) == 0) {
						$result['payment_type'] = 'package';
						$result['payment_type_flag'] = '1';
					}
				}

			}
		
		return $result;

	}
	
	function tbl_payment_type_master($key_words)
	{
		$key_wordsstr = str_replace(",","','",$key_words);
		
		$sql= "select group_concat(campaign_name) as campaign_name from tbl_payment_type_master where key_words in ('".$key_wordsstr."') ";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<hr><b>Live DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<hr>';
		}

		if ($num > 0) {
			$resarr= mysql_fetch_assoc($res);			
			$result['campaign_name'] = $resarr['campaign_name'];
		}
		
		return $result;
	}

	function get_pincode_details($pincodes)
	{
		$sql = "select pincode, substring_index(group_concat(main_area order by callcnt_perday desc SEPARATOR '#'),'#',1) as areaname
	from tbl_areamaster_consolidated_v3 where pincode in (" . $pincodes . ") group by pincode";
		$res_area = parent::execQuery($sql, $this->dbConDjds);
		$num_rows = mysql_num_rows($res_area);

		if (DEBUG_MODE) {
			echo '<br><b>Area Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res_area;
			echo '<br><b>Num Rows:</b>' . $num_rows;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}

		if ($res_area && $num_rows > 0) {

			while ($row = mysql_fetch_assoc($res_area)) {
			//print_r($row);
				$pincode = $row['pincode'];
				$ret_array[$pincode]['pincode'] = $row['pincode'];
				$ret_array[$pincode]['anm'] = $row['areaname'];
			}
		}
		return ($ret_array);
	}
	
	function tbflexibgttbws()
	{
		$sql= "select tb_flexi_bgt from tbl_budget_wrapper_summary where parentid='" . $this->params['parentid'] . "' and version ='".$this->params['version']."'";
		
		$res = parent::execQuery($sql, $this->dbConbudget);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		
		if($num)
		{
			while ($row = mysql_fetch_assoc($res)) 
			{
				$resultarr['tb_flexi_bgt']= $row['tb_flexi_bgt'];
			}			
			
			$resultarr['error']['code'] = 0;
		
		}else
		{
			$resultarr['error']['code'] = 1;
			$resultarr['error']['msg'] = 'data not found on tbl_budget_wrapper_summary';
		}		
		
		$finrr = $this->getfinancemaindata();
		$resultarr['finance']=$finrr['data'];
		
		return $resultarr;
		
	}
	
	function getfinancemaindata()
	{
		$resultarr= array();
		$sql = "select parentid,campaignid,campaign_value,budget,balance,bid_perday,start_date,end_date,duration,version,total_app_duration,total_app_amount,last_credit_date,expired,expired_on,active_flag,active_campaign,multiplier from tbl_companymaster_finance where parentid ='" . $this->parentid . "' ";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
		}
		
		if($num)
		{
			while ($row = mysql_fetch_assoc($res)) 
			{
				$resultarr['data'][$row['campaignid']]= $row;
			}			
			
			$resultarr['error']['code'] = 0;
		
		}else
		{
			$resultarr['error']['code'] = 1;
			$resultarr['error']['msg'] = 'data not found on tbl_companymaster_finance';
		}		
		
		return $resultarr;
	}	
	
	
	function getfinancetempdata()
	{
		
		if(trim($this->params['module']) == null)
		{
			$result['results'] = array();
			$result['error']['code'] = 1;	
			$result['error']['msg'] = "module missing";
			
			return $result;			
		}
		
		if( strtolower($params['module'])=='tme')
		{
			$confintemp = $this->tme_jds;
			
		}elseif( strtolower($params['module'])=='me')
		{
			$confintemp   = $this->dbConIdc;
		}
		
		
		
		$resultarr= array();
		$sql = "select parentid,campaignid,budget,duration,version,exclusivelisting_tag,recalculate_flag,original_budget,original_actual_budget,actual_price,discount_percent from tbl_companymaster_finance_temp where parentid ='" . $this->parentid . "' ";
		$res = parent::execQuery($sql, $this->dbConIdc);
		$num = mysql_num_rows($res);

		$finance_live_total_budget = 0;
		if (DEBUG_MODE) {
			
			
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;
			echo '<br><b>$confintemp:</b>' ; print_r($$confintemp);
			
			
		}
		
		if($num)
		{
			while ($row = mysql_fetch_assoc($res)) 
			{
				$resultarr['data'][$row['campaignid']]= $row;
			}			
			
			$resultarr['error']['code'] = 0;
		
		}else
		{
			$resultarr['error']['code'] = 1;
			$resultarr['error']['msg'] = 'data not found on tbl_companymaster_finance_temp';
		}
		
		
		return $resultarr;

	}
		
	function getCompanymasterFinancedata()
	{
		$sql = "select campaignid,budget,duration,balance,version,bid_perday,expired,(balance/bid_perday) as remaining_tenure  from tbl_companymaster_finance where parentid ='" . $this->parentid . "' and campaignid in (1,2) and balance>0 ORDER BY campaignid";
		$res = parent::execQuery($sql, $this->finance);
		$num = mysql_num_rows($res);

		$finance_live_total_budget = 0;
		if (DEBUG_MODE) {
			echo '<br><b>Finance DB Query:</b>' . $sql;
			echo '<br><b>Result Set:</b>' . $res;
			echo '<br><b>Num Rows:</b>' . $num;
			echo '<br><b>Error:</b>' . $this->mysql_error;

		}

		if ($num > 0) {
			while ($row = mysql_fetch_assoc($res)) {

				$campaignid = (int)$row['campaignid'];
				$compmasterfinanceversion = intval($row['version']);

				$finance_array['result']['finance']['data'][$campaignid]['campaignid'] = $campaignid;
				$finance_array['result']['finance']['data'][$campaignid]['budget'] = floatval($row['budget']);
				$finance_array['result']['finance']['data'][$campaignid]['tenure'] = intval($row['duration']);
				$finance_array['result']['finance']['data'][$campaignid]['balance'] = floatval($row['balance']);
				$finance_array['result']['finance']['data'][$campaignid]['version'] = intval($row['version']);
				$finance_array['result']['finance']['data'][$campaignid]['bid_perday'] = floatval($row['bid_perday']);
				$finance_array['result']['finance']['data'][$campaignid]['remaining_tenure'] = floatval($row['remaining_tenure']);
				$finance_array['result']['finance']['data'][$campaignid]['expired'] = $row['expired'];

				$finance_array['result']['finance']['version'] = $compmasterfinanceversion;

			}

		} else {
			$sql = "select version from tbl_bidding_details_shadow where parentid='" . $this->parentid . "' order by booked_date desc limit 1";
			$res = parent::execQuery($sql, $this->dbConbudget);
			$num = mysql_num_rows($res);
			$row = mysql_fetch_assoc($res);

			if (DEBUG_MODE) {
				echo '<br><b>Finance DB Query:</b>' . $sql;
				echo '<br><b>Result Set:</b>' . $res;
				echo '<br><b>Num Rows:</b>' . $num;
				echo '<br><b>Error:</b>' . $this->mysql_error;
			}

			$finance_array['result']['finance']['version'] = $row['version'];

		}

		return $finance_array;

	}


	function paymenttypedealclosed()
	{
		$findata = $this->getCompanymasterFinancedata();
		$version = $findata['result']['finance']['version'];

		$returnarray = $this->fn_tbl_payment_type_dealclosed($version, $findata);
		return $returnarray;

	}
	
	function centraliselogging($dataarray,$message,$apiurl=null,$apiurlresponse=null)
	{		
		$post_data = array();
		
		$log_url = 'http://192.168.17.109/logs/logs.php';
		
		if(trim($this->params['parentid'])!=null)
		{
			$post_data['ID']                = $this->params['parentid'];
		}elseif(trim($this->params['callermobile'])!=null)
		{
			$post_data['ID']                = $this->params['callermobile'];
		}
		
			
		$post_data['PUBLISH']           = 'ME';
		$post_data['ROUTE']             = 'BUDGETDETAILSWRAPER';
		$post_data['CRITICAL_FLAG'] 	= 1;
		$post_data['MESSAGE']       	= $message;
		$post_data['DATA']['url']       = $apiurl;
		$post_data['DATA_JSON']['DataArray'] = json_encode($dataarray);
		$post_data['DATA_JSON']['response'] = $apiurlresponse;
		$post_data['DATA']['user'] = 	$this->usercode;
		$post_data['DATA']['source'] = 	$source;
		
		$post_data = http_build_query($post_data);
				
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $log_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content  = curl_exec($ch);
		curl_close($ch);
		
		if(DEBUG_MODE) 
		{
			echo '<br>centraliselogging<br><b>post_data</b> <br>'; print_r($post_data); // uncomment
			echo '<br> content <br>'; print($content); // uncomment
		}		
		
	}					

}	
