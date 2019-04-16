<?php

class budgetsubmitclass extends DB
{
	var  $dbConIro    	= null;
	var  $dbConDjds   	= null;
	var  $dbConTmeJds 	= null;
	var  $dbConFin    	= null;
	var  $dbConIdc    	= null;
	var  $intermediate 	= null;
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');
	var	 $arr_errors	= array();
	var  $is_split		= null;
	var  $parentid		= null;
	var  $version		= null;
	var  $sys_regfee_budget	= null;
	

	var  $catsearch	= null;
	var  $data_city	= null;
	var  $opt 		= 'ALL'; 	// area selection option 
	
	
	
	var	 $optvalset = array('ALL','ZONE','NAME','PIN','DIST');
	

	function __construct($params)
	{		
		$this->params = $params;		
				
		if(trim($this->params['parentid']) != "")
		{
			$this->parentid  = $this->params['parentid']; //initialize paretnid
		}else
		{
			$errorarray['errormsg']='parentid missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['module']) != "" && $this->params['module'] != null)
		{
			$this->module  = strtolower($this->params['module']); //initialize module
		}else
		{
			$errorarray['errormsg']='module missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['duration']) != "" && $this->params['duration'] != null)
		{
			$this->duration  = strtolower($this->params['duration']); //initialize duration
		}else
		{
			$errorarray['errormsg']='duration missing';
			echo json_encode($errorarray); exit;
		}
		
		if(trim($this->params['data_city']) != "" && $this->params['data_city'] != null)
		{
			$this->data_city  = $this->params['data_city']; //initialize datacity
		}else
		{
			$errorarray['errormsg']='data_city missing';
			echo json_encode($errorarray); exit;
		}

		if(trim($this->params['usercode']) != "" && $this->params['usercode'] != null)
		{
			$this->usercode  = $this->params['usercode']; //initialize usercode
		}else
		{
			$errorarray['errormsg']='usercode missing';
			echo json_encode($errorarray); exit;
		}
		if(trim($this->params['package_10dp_2yr']) != "" && $this->params['package_10dp_2yr'] != null)
		{
			$this->package_10dp_2yr  = $this->params['package_10dp_2yr']; 
		}else
		{
			$this->package_10dp_2yr  =0;
		}

		if(trim($this->params['genio_lite_daemon']) != "" && $this->params['genio_lite_daemon'] != null)
		{
			$this->genio_lite_daemon  = $this->params['genio_lite_daemon']; 
		}else
		{
			$this->genio_lite_daemon  =0;
		}

		if(trim($this->params['actual_duration']) != "" && $this->params['actual_duration'] != null)
		{
			$this->actual_duration  = $this->params['actual_duration']; 
		}
		
		$this->p_bgt = 0;
		$this->fp_bgt= 0;
		$this->exactrenewal =0;
		$this->mongo_flag 	= 0;
		$this->mongo_tme 	= 0;
		$this->mongo_obj 	= new MongoClass();
		$this->categoryClass_obj =	new categoryClass();
		$this->setServers();
		$this->setsphinxid(); // set the sphinxid variable
		$this->setversion(); 
		//$this->locationinit();
		//echo json_encode('const'); exit;
		
	    $this-> minimum_reg_fee = 999; 
	}
		
	// Function to set DB connection objects
	function setServers()
	{	
		global $db;
			
		$data_city 		= ((in_array(strtolower($this->params['data_city']), $this->dataservers)) ? strtolower($this->params['data_city']) : 'remote');
		
		$this->dbConIro    		= $db[$data_city]['iro']['master'];
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		$this->dbConDjds_slave	= $db[$data_city]['d_jds']['slave'];
		$this->dbConIro_slave	= $db[$data_city]['iro']['slave'];
		$this->dbConIdc   		= $db[$data_city]['idc']['master'];
		$this->fin   			= $db[$data_city]['fin']['master'];
		$this->tme_jds   		= $db[$data_city]['tme_jds']['master'];
		$this->db_budgeting   	= $db[$data_city]['db_budgeting']['master'];
		
		//print_r($this->fin);
		switch($this->module)
		{
			case 'cs':		
			$this->intermediate = $this->dbConDjds;
			$this->finance_temp = $this->fin;
			$this->conn_temp	 	= $this->dbConIro;
			break;

			case 'tme':
			$this->finance_temp = $this->tme_jds;
			$this->intermediate = $this->tme_jds;
			$this->conn_temp	 	= $this->tme_jds;
			if((in_array($this->usercode, json_decode(TME_MONGOUSER)) || TME_ALLUSER_MONGO == 1) && in_array(strtolower($data_city), json_decode(MONGOCITY))){
				$this->mongo_tme = 1;
			}
			break;

			case 'me':
			$this->finance_temp = $this->dbConIdc;
			$this->intermediate = $this->dbConIdc;
			$this->conn_temp	= $this->dbConIdc;
			if((in_array($this->usercode, json_decode(MONGOUSER)) || ALLUSER == 1)){	
				$this->mongo_flag = 1;
			}
			break;
		}

	}

	function setversion()
	{
		if((trim($this->params['version']) != "" && $this->params['version'] != null) && ($this->params['action'] == 'updateActualBudget' || ($this->params['action'] == 'submitbudget' && $this->genio_lite_daemon) ) )
		{
			$this->version  = $this->params['version']; 
		}else
		{
			
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_inputs['table'] 		= "tbl_temp_intermediate";
				$mongo_inputs['fields'] 	= "version";
				$summary_version_arr 		= $this->mongo_obj->getData($mongo_inputs);
			}else{
			
				$summary_version_sql 	="select version from tbl_temp_intermediate where parentid='".$this->parentid."'";
				$summary_version_rs 	=  parent::execQuery($summary_version_sql, $this->intermediate);
				$summary_version_arr 	= mysql_fetch_assoc($summary_version_rs);
			}
			$this->version = $summary_version_arr['version'];
		}
	}
		
	function setsphinxid()
	{
		$sql= "select sphinx_id,docid from tbl_id_generator where parentid='".$this->parentid."'";
		$res = parent::execQuery($sql, $this->dbConIro);

		if($res && mysql_num_rows($res) )
		{
			$row= mysql_fetch_assoc($res);
			$this->sphinx_id = $row['sphinx_id'];
			$this->docid = $row['docid'];
		}else
		{
			echo "sphinx_id not found in tbl_id_generator";
			exit;
		}
	}
	
	function submitbudget()
	{		

		$this->params['budgetjson'] = ($this->genio_lite_daemon) ? json_decode($this->params['budgetjson'],true) : $this->params['budgetjson'];

		$budgetjson = $this->params['budgetjson'];

		// we will delete first then we will insert data for that version 
		$delsql= " delete from tbl_bidding_details_intermediate where parentid ='".$this->parentid."' and version ='".$this->version."'";
		parent::execQuery($delsql, $this->db_budgeting);

		
		if(!$this->genio_lite_daemon)
		{
			$insjsonsql= " REPLACE INTO tbl_bidding_details_budgetjson(parentid, version, budgetjson, updatedby, updatedon) 
						   VALUES 
						   ('".$this->parentid."','".$this->version."','".addslashes(stripslashes(json_encode($this->params['budgetjson'])))."','".$this->usercode."',now()) ";
			$res_insjsonsql = parent::execQuery($insjsonsql, $this->db_budgeting);
		}
		//echo $insjsonsql;print_r($this->params['budgetjson']);
		//$budgetjson = $this->params['dataArr'];
		//$budgetjson = json_decode($budgetjson_str,true);

		//return $budgetjson;
		//return $budgetjson_str;

		//$this->parentid
		$inv_seeked = array();
		$city_min_bgt	= $budgetjson['city_bgt'];
		//$city_bgt       = $budgetjson['totBudget'] - $budgetjson['reg_bgt'];
		//$budgetjson['totBudget']= 356;
		//$actual_bgt       = $budgetjson['actual_bgt']; // actual budget of contract we are getting
		$totBudget       = $budgetjson['totBudget']; // total budget 

		$system_fp_budget	=$budgetjson['pdgBudget']; // total budget 
		$system_pack_budget	= $budgetjson['packageBudget']; // total budget 

		$actual_bgt = $system_fp_budget+$system_pack_budget;

		if($system_fp_budget==0 && $system_pack_budget==0)				
		{
			$ret_array['error_code']=1;
			$ret_array['message']='Budget is not proper';
			
			mail('prameshjha@justdial.com,sumesh.dubey@justdial.com','Zero budget for PDG and Package '.'--'.$this->usercode.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],json_encode($budgetjson));
			return $ret_array;
			exit;
		}
		
		$customBudget=0;
		//$customBudget=$budgetjson['totBudget'];
		
		if(isset($budgetjson['customBudget']))
		{
			$customBudget	 = $budgetjson['customBudget'];
		}
		##city_min_bgt==5000.00###city_bgt==89892##

		#cityfactor--1
		if($customBudget!=0) // custom budget is set 
		{
			$city_factor = $customBudget/$actual_bgt;
		}
		elseif($actual_bgt<$city_min_bgt)
		{
			$city_factor = 1+(($city_min_bgt-$actual_bgt)/$actual_bgt);
		}
		else
		{
			$city_factor = 1;
		}

		// for negative budget hadling start
		/*
		foreach($budgetjson['c_data'] as $catid=>$catidarr)
		{
				$bgtvalue 	= $catidarr['c_bgt'];
				
				if($bgtvalue<=0)				
				{
					$ret_array['error_code']=1;
					$ret_array['message']='Negative budget Found';
					
					mail('prameshjha@justdial.com,sumesh.dubey@justdial.com','Negative budget or Zero budget of c_bgt '.'--'.$this->usercode.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],json_encode($budgetjson));
					return $ret_array;
					exit;
				}
		}
		*/
		// for negative budget hadling end
		
		/*gettig existing signed up category pincode inventory data - start*/
			$existing_signed_up = $this->get_live_inventory();
		/*gettig existing signed up category pincode inventory data - end*/

		//echo "city_min_bgt==".$city_min_bgt."###actual_bgt==".$actual_bgt."###totBudget==".$budgetjson['totBudget']."##cityfactor=".$city_factor	;
		//$cnt_row = ceil(count($budgetjson['c_data'])/10);
		$numItems = count($budgetjson['c_data']);
		$i = 0;
		foreach($budgetjson['c_data'] as $catid=>$catidarr)
		{
				$c_bgt 	= $catidarr['c_bgt'];
				$bflg 	= $catidarr['bflg'];
				$bm_bgt	= $catidarr['bm_bgt'];

				
				# bm_bgt is not relevant now so removing this comparision
				/*
				if($bflg==1)
					$f_bgt = max($c_bgt, $bm_bgt);
				else
					$f_bgt = $c_bgt;
				*/	
				
				$f_bgt = $c_bgt;

				//echo "--f_bgt=".$f_bgt;
			
			$cat_pincode_budget_sum=0;		
			foreach($catidarr['pin_data'] as $pincode =>$pincodearr)
			{
				$inv_seeked[$catid][$pincode]['cnt_f']   = $pincodearr['cnt_f'];
				foreach($pincodearr['pos'] as $positionval =>$positionarr)
				{	
					
					$inv_seeked[$catid][$pincode]['pin']   = $pincode;
					$inv_seeked[$catid][$pincode]['pos']   = $positionval;
					$inv_seeked[$catid][$pincode]['bidvalue']   = $positionarr['bidvalue'];
					$inv_seeked[$catid][$pincode]['budget'] = $positionarr['budget'];
					$inv_seeked[$catid][$pincode]['inv'] 	 = $positionarr['inventory'];
					$budget[$catid] += $positionarr['budget'];
					$cat_pincode_budget_sum+= $positionarr['budget'];
					
					$calculated_data[$catid][$pincode][$positionval]['i'] = $positionarr['inventory'];
				}
			}

			//echo "\n--budget catid--".$budget[$catid]."##f_bgt=".$f_bgt;
			/*
			if($budget[$catid]<$f_bgt)
			{
				if($city_factor<1)
				{
					$factor = $city_factor;
				}else
				{
					$factor = 1 + (($f_bgt - $budget[$catid])/$budget[$catid]);
				}
			}
			elseif($city_factor<1)// custom budget
			{				
				$factor = $city_factor;
			}else
			{
				$factor = 1;
			}
			*/

			// we have to apportionate budget for pincode
			//echo '\n catid'.$catid;
			//echo ' cat_pincode_budget_sum'.$cat_pincode_budget_sum;
			//echo ' c_bgt'.$c_bgt;

			
			if($cat_pincode_budget_sum!=0 && $c_bgt!=0)
			{
				$Pincode_multiplying_factor= $c_bgt/$cat_pincode_budget_sum ;
			

				foreach($inv_seeked[$catid] as $pincode=>$val_array)
				{
					$inv_seeked[$catid][$pincode]['budget'] = $inv_seeked[$catid][$pincode]['budget']*$Pincode_multiplying_factor;

				// now we dont require to sum because we will get it from since we already get this in submit 
				/*
				if($inv_seeked[$catid][$pincode]['pos']==100)
					$this->p_bgt += $inv_seeked[$catid][$pincode]['budget'];
				else
					$this->fp_bgt += $inv_seeked[$catid][$pincode]['budget'];
					*/
				}
			}

			//echo "\n--fp_bgt--".$this->fp_bgt."---p_bgt=".$this->p_bgt."##factor=".$factor;
			
			$x .= "('".$this->parentid."','".$catid."','".$catidarr['ncid']."','".$f_bgt."','".$this->version."','".json_encode($inv_seeked[$catid])."','".$this->usercode."',NOW()),";
			
			if(++$i === $numItems) 
			{
				//echo "last index!";
				$x = rtrim($x,',');
				$sql = "insert into tbl_bidding_details_intermediate
				(parentid,catid,national_catid,cat_budget,version,pincode_list,updatedby,updatedon) values ".$x;
				parent::execQuery($sql, $this->db_budgeting);	
			}
			else
			{
				//	
				if($i % 15 == 0)
				{
					$x = rtrim($x,',');	
					$sql = "insert into tbl_bidding_details_intermediate
					(parentid,catid,national_catid,cat_budget,version,pincode_list,updatedby,updatedon) values ".$x;	
					$x='';
					parent::execQuery($sql, $this->db_budgeting);	
				}
			}
			
			/*$sql = "insert into tbl_bidding_details_intermediate set
					parentid		='".$this->parentid."',
					catid			='".$catid."',
					national_catid	='".$catidarr['ncid']."',
					cat_budget		='".$f_bgt."',
					version			='".$this->version."',
					pincode_list 	='".json_encode($inv_seeked[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()
					ON DUPLICATE KEY UPDATE							
					catid			='".$catid."',
					national_catid	='".$catidarr['ncid']."',
					cat_budget		='".$f_bgt."',
					pincode_list 	='".json_encode($inv_seeked[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()	";
					parent::execQuery($sql, $this->db_budgeting);
			//return $sql;*/
		}


		/*
		if($budgetjson['tenure']==12)
		{
			$duration=365;
			
		}elseif($budgetjson['tenure']==6)
		{
			$duration=180;
			
		}elseif($budgetjson['tenure']==3)
		{
			$duration=90;
			
		}elseif($budgetjson['tenure']==1)
		{
			$duration=30;
		}
		*/
		
		if($this->genio_lite_daemon)
		{
			if($budgetjson['tenure']> 0 )
			{
					$duration = ($budgetjson['tenure']/12) * 365;
			}else
			{
				$array['error_code']=1;
				$array['message']='Invalid Duration Passed';
				return $array;
			}
		}
		else
		{
			if($budgetjson['tenure']==12)
			{
					$duration=365;

			}elseif($budgetjson['tenure']==24)
			{
					$duration=730;

			}elseif($budgetjson['tenure']==60)
			{
					$duration=1825;
			}elseif($budgetjson['tenure']==120)
			{
				$duration=3650;
			}else{
					//$duration = ($budgetjson['tenure']/12) * 365;
					
					if($budgetjson['tenure']> 0 )
					{
							if($budgetjson['tenure']> 12 )
								$duration = ($budgetjson['tenure']/12) * 365;
							else
								$duration = $budgetjson['tenure'] * 30;
					}
					else
					{
						$array['error_code']=1;
						$array['message']='Invalid Duration Passed';
						return $array;
					}

			}

		}
		
		
		$this->sys_regfee_budget = ($budgetjson['reg_bgt'] > $this-> minimum_reg_fee ) ? $budgetjson['reg_bgt'] : 0;
		$this->sys_total_budget  = $budgetjson['totBudget'];


		$campaign_budget_ratio = $this->sys_total_budget/($system_fp_budget+$system_pack_budget) ;
		
		

		$this->p_bgt = $system_pack_budget * $campaign_budget_ratio ;
		$this->fp_bgt = $system_fp_budget * $campaign_budget_ratio ;
		
		if(isset($budgetjson['exactrenewal']))
		{
			$this->exactrenewal = $budgetjson['exactrenewal'];
		}
		
		if(is_array($existing_signed_up) && count($existing_signed_up)>0 && is_array($calculated_data) && count($calculated_data)>0)
		{
			$this->exactrenewal = 1;
			
			foreach($existing_signed_up as $existing_catid => $existing_catid_data)
			{
				foreach($existing_catid_data as $existing_pincode => $existing_pincode_data)
				{
					foreach($existing_pincode_data as $existing_position => $existing_position_data)
					{
						if(!$calculated_data[$existing_catid][$existing_pincode][$existing_position] )
						{
							$this->exactrenewal = 0;
						}
						else if($calculated_data[$existing_catid][$existing_pincode][$existing_position])
						{
							if($existing_position < 100 && $calculated_data[$existing_catid][$existing_pincode][$existing_position]['i'] != $existing_position_data['i'])
							{
							  $this->exactrenewal = 0;
							}
						}
					}
				}
			}
			
			foreach($calculated_data as $calculated_catid => $calculated_catid_data)
			{
				foreach($calculated_catid_data as $calculated_pincode => $calculated_pincode_data)
				{
					foreach($calculated_pincode_data as $calculated_position => $calculated_position_data)
					{
						if(!$existing_signed_up[$calculated_catid][$calculated_pincode][$calculated_position])
						{
							$this->exactrenewal = 0;
						}else if ($existing_signed_up[$calculated_catid][$calculated_pincode][$calculated_position])
						{
							if($calculated_position < 100  && $existing_signed_up[$calculated_catid][$calculated_pincode][$calculated_position]['i'] != $calculated_position_data['i'])
							{
								$this->exactrenewal = 0;
							}
						}
					}
				}
			}
			
			
		}
		else
		{
			$this->exactrenewal = 0;
		}
		
		

		$this->updatebudget($duration); // we have to update finance temp table budget 
		$array['error_code']=0;
		$array['message']='Sucess';
		$array['exact_renewal']=$this->exactrenewal;


		if(isset($budgetjson['removeCatStr']))
		{
			$remove_catidlist_arr = explode(',',$budgetjson['removeCatStr']);
			$remove_catidlist_arr= array_unique($remove_catidlist_arr);
			$remove_catidlist_arr= array_filter($remove_catidlist_arr);
			
			if(count($remove_catidlist_arr))
			{
				$this->populate_category_temp_data($remove_catidlist_arr);// remove category handling
			}
		}

		

		if(isset($budgetjson['nonpaidStr']))
		{
			$this->updateNonpaidCategory($budgetjson['nonpaidStr']);
		}
		
		return $array;
	}

	
	function submitBudgetDataHidden()
	{		

		$budgetjson = $this->params['budgetjson'];
		//print_r($this->params);
		// we will delete first then we will insert data for that version 
		$delsql= " delete from tbl_bidding_details_hidden_intermediate where parentid ='".$this->parentid."' and version ='".$this->version."'";
		
		parent::execQuery($delsql, $this->dbConDjds);
		
		
		/*$insjsonsql= " INSERT INTO tbl_bidding_details_budgetjson SET 		
		parentid ='".$this->parentid."',
		version  ='".$this->version."',
		budgetjson ='".stripslashes(json_encode($this->params['budgetjson']))."',
		updatedby		='".$this->usercode."',
		updatedon=now()
		ON DUPLICATE KEY UPDATE
		budgetjson ='".stripslashes(json_encode($this->params['budgetjson']))."',
		updatedby ='".$this->usercode."',
		updatedon=now() ";
		parent::execQuery($insjsonsql, $this->db_budgeting);
		*/
		//echo $insjsonsql;print_r($this->params['budgetjson']);
		//$budgetjson = $this->params['dataArr'];
		//$budgetjson = json_decode($budgetjson_str,true);

		//return $budgetjson;
		//return $budgetjson_str;

		//$this->parentid
		$inv_seeked = array();
		$city_min_bgt	= $budgetjson['city_bgt'];
		//$city_bgt       = $budgetjson['totBudget'] - $budgetjson['reg_bgt'];
		//$budgetjson['totBudget']= 356;
		//$actual_bgt       = $budgetjson['actual_bgt']; // actual budget of contract we are getting
		$totBudget       = $budgetjson['totBudget']; // total budget 

		$system_fp_budget	=$budgetjson['pdgBudget']; // total budget 
		$system_pack_budget	= $budgetjson['packageBudget']; // total budget 

		$actual_bgt = $system_fp_budget+$system_pack_budget;

		
		
		$customBudget=0;
		//$customBudget=$budgetjson['totBudget'];
		
		if(isset($budgetjson['customBudget']))
		{
			$customBudget	 = $budgetjson['customBudget'];
		}
		##city_min_bgt==5000.00###city_bgt==89892##

		#cityfactor--1
		if($customBudget!=0) // custom budget is set 
		{
			$city_factor = $customBudget/$actual_bgt;
		}
		elseif($actual_bgt<$city_min_bgt)
		{
			$city_factor = 1+(($city_min_bgt-$actual_bgt)/$actual_bgt);
		}
		else
		{
			$city_factor = 1;
		}

		// for negative budget hadling start
		foreach($budgetjson['c_data'] as $catid=>$catidarr)
		{
				$bgtvalue 	= $catidarr['c_bgt'];
				
				if($bgtvalue<=0)				
				{
					$ret_array['error_code']=1;
					$ret_array['message']='Negative budget Found';
					
					//mail('prameshjhatest@gmail.com','Negative budget '.'--'.$this->usercode.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],json_encode($budgetjson));
					//return $ret_array;
					//exit;
				}
		}
		// for negative budget hadling end
		
		
		//echo "city_min_bgt==".$city_min_bgt."###actual_bgt==".$actual_bgt."###totBudget==".$budgetjson['totBudget']."##cityfactor=".$city_factor	;
		//$cnt_row = ceil(count($budgetjson['c_data'])/10);
		$numItems = count($budgetjson['c_data']);
		$i = 0;
		foreach($budgetjson['c_data'] as $catid=>$catidarr)
		{
				$c_bgt 	= $catidarr['c_bgt'];
				$bflg 	= $catidarr['bflg'];
				$bm_bgt	= $catidarr['bm_bgt'];

				
				
				if($bflg==1)
					$f_bgt = max($c_bgt, $bm_bgt);
				else
					$f_bgt = $c_bgt;

				//echo "--f_bgt=".$f_bgt;

			$cat_pincode_budget_sum=0;		
			foreach($catidarr['pin_data'] as $pincode =>$pincodearr)
			{
				$inv_seeked[$catid][$pincode]['cnt_f']   = $pincodearr['cnt_f'];
				foreach($pincodearr['pos'] as $positionval =>$positionarr)
				{	
					$inv_seeked[$catid][$pincode]['pin']   = $pincode;
					$inv_seeked[$catid][$pincode]['pos']   = $positionval;
					$inv_seeked[$catid][$pincode]['bidvalue']   = $positionarr['bidvalue'];
					$inv_seeked[$catid][$pincode]['budget'] = $positionarr['budget'];
					$inv_seeked[$catid][$pincode]['inv'] 	 = $positionarr['inventory'];
					$budget[$catid] += $positionarr['budget'];
					$cat_pincode_budget_sum+= $positionarr['budget'];
				}
			}

			//echo "\n--budget catid--".$budget[$catid]."##f_bgt=".$f_bgt;
			/*
			if($budget[$catid]<$f_bgt)
			{
				if($city_factor<1)
				{
					$factor = $city_factor;
				}else
				{
					$factor = 1 + (($f_bgt - $budget[$catid])/$budget[$catid]);
				}
			}
			elseif($city_factor<1)// custom budget
			{				
				$factor = $city_factor;
			}else
			{
				$factor = 1;
			}
			*/

			// we have to apportionate budget for pincode
			//echo '\n catid'.$catid;
			//echo ' cat_pincode_budget_sum'.$cat_pincode_budget_sum;
			//echo ' c_bgt'.$c_bgt;

			
			if($cat_pincode_budget_sum!=0 && $c_bgt!=0)
			{
				$Pincode_multiplying_factor= $c_bgt/$cat_pincode_budget_sum ;
			

				foreach($inv_seeked[$catid] as $pincode=>$val_array)
				{
					$inv_seeked[$catid][$pincode]['budget'] = $inv_seeked[$catid][$pincode]['budget']*$Pincode_multiplying_factor;

				// now we dont require to sum because we will get it from since we already get this in submit 
				/*
				if($inv_seeked[$catid][$pincode]['pos']==100)
					$this->p_bgt += $inv_seeked[$catid][$pincode]['budget'];
				else
					$this->fp_bgt += $inv_seeked[$catid][$pincode]['budget'];
					*/
				}
			}

			//echo "\n--fp_bgt--".$this->fp_bgt."---p_bgt=".$this->p_bgt."##factor=".$factor;
			
			$x .= "('".$this->parentid."','".$catid."','".$catidarr['ncid']."','".$f_bgt."','".$this->version."','".json_encode($inv_seeked[$catid])."','".$this->usercode."',NOW()),";
			//echo (++$i === $numItems);
			if(++$i === $numItems) 
			{
				
				$x = rtrim($x,',');
				$sql = "insert into tbl_bidding_details_hidden_intermediate
				(parentid,catid,national_catid,cat_budget,version,pincode_list,updatedby,updatedon) values ".$x;
				parent::execQuery($sql, $this->dbConDjds);	
			}
			else
			{
				//	
				if($i % 15 == 0)
				{
					
					$x = rtrim($x,',');	
					$sql = "insert into tbl_bidding_details_hidden_intermediate
					(parentid,catid,national_catid,cat_budget,version,pincode_list,updatedby,updatedon) values ".$x;	
					$x='';
					parent::execQuery($sql, $this->dbConDjds);	
				}
			}
			
			/*$sql = "insert into tbl_bidding_details_intermediate set
					parentid		='".$this->parentid."',
					catid			='".$catid."',
					national_catid	='".$catidarr['ncid']."',
					cat_budget		='".$f_bgt."',
					version			='".$this->version."',
					pincode_list 	='".json_encode($inv_seeked[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()
					ON DUPLICATE KEY UPDATE							
					catid			='".$catid."',
					national_catid	='".$catidarr['ncid']."',
					cat_budget		='".$f_bgt."',
					pincode_list 	='".json_encode($inv_seeked[$catid])."',							
					updatedby		='".$this->usercode."',
					updatedon=now()	";
					parent::execQuery($sql, $this->db_budgeting);
			//return $sql;*/
		}


		/*
		if($budgetjson['tenure']==12)
		{
			$duration=365;
			
		}elseif($budgetjson['tenure']==6)
		{
			$duration=180;
			
		}elseif($budgetjson['tenure']==3)
		{
			$duration=90;
			
		}elseif($budgetjson['tenure']==1)
		{
			$duration=30;
		}
		*/

		if($budgetjson['tenure']==12)
		{
			$duration=365;
			
		}elseif($budgetjson['tenure']==24)
		{
			$duration=730;
			
		}else
		{
			$duration = $budgetjson['tenure'] * 30;
			
		}
		
		$this->sys_regfee_budget = ($budgetjson['reg_bgt'] > $this-> minimum_reg_fee ) ? $budgetjson['reg_bgt'] : 0;
		
		$this->sys_total_budget  = $budgetjson['totBudget'];


		$campaign_budget_ratio = $this->sys_total_budget/($system_fp_budget+$system_pack_budget) ;
		
		

		$this->p_bgt = $system_pack_budget * $campaign_budget_ratio ;
		$this->fp_bgt = $system_fp_budget * $campaign_budget_ratio ;

		

		
		
		$this->updatebudgetHidden($duration); // we have to update finance temp table budget 
		$array['error_code']=0;
		$array['message']='Sucess';


		if(isset($budgetjson['removeCatStr']))
		{
			$remove_catidlist_arr = explode(',',$budgetjson['removeCatStr']);
			$remove_catidlist_arr= array_unique($remove_catidlist_arr);
			$remove_catidlist_arr= array_filter($remove_catidlist_arr);
			
			if(count($remove_catidlist_arr))
			{
				$this->populate_category_temp_data($remove_catidlist_arr);// remove category handling
			}
		}

		

		if(isset($budgetjson['nonpaidStr']))
		{
			$this->updateNonpaidCategory($budgetjson['nonpaidStr']);
		}
		
		return $array;
	}
	
	function updatebudgetHidden($duration)
	{
		if($this->sys_total_budget>0)
		{	
			$sys_fp_budget=$this->sys_total_budget;				
			$Budgetarray[17]["budget"] =$this->sys_total_budget;
			$Budgetarray[17]["original_budget"] =$this->sys_total_budget;
			$Budgetarray[17]["original_actual_budget"] =$this->sys_total_budget;
			$Budgetarray[17]["recalculate_flag"]	=1;
		}
		else
		{
			$sys_fp_budget=0;				
			$Budgetarray[17]["budget"] =0;
			$Budgetarray[17]["original_budget"] =0;
			$Budgetarray[17]["original_actual_budget"] =0;
			$Budgetarray[17]["recalculate_flag"]	=0;
		}
		
		foreach($Budgetarray as $campaignid =>$campaignarr)
		{			
			$this->financeInsertUpdateTemp($campaignid,array("budget"=>$campaignarr["budget"],"original_budget"=>$campaignarr['original_budget'],"original_actual_budget"=>$campaignarr['original_actual_budget'],"duration"=>$duration,"recalculate_flag"=>$campaignarr['recalculate_flag'],"version" =>$this->version));
		}
		
		
	}
	function updatebudget($duration)
	{
		$Budgetarray=array(); // this array we will take as parameter 

		if($this->fp_bgt>0)
		{
			$this->GetSetBudgetLog($campaignid=2,$this->fp_bgt,$duration,$this->sys_regfee_budget,$action=2,$this->usercode,$this->remote_city_flag,$this->module,$this>appserver_cs);
			$sys_fp_budget=$this->fp_bgt;				
			$Budgetarray[2]["budget"] =$this->fp_bgt;
			$Budgetarray[2]["original_budget"] =$this->fp_bgt;
			$Budgetarray[2]["original_actual_budget"] =$this->fp_bgt;
			$Budgetarray[2]["recalculate_flag"]	=1;
		}else
		{			
			$Budgetarray[2]["budget"] =0;
			$Budgetarray[2]["original_budget"] =0;
			$Budgetarray[2]["original_actual_budget"] =0;
			$Budgetarray[2]["recalculate_flag"]	=0;
		}

		if($this->p_bgt)
		{
			$this->GetSetBudgetLog($campaignid=2,$this->fp_bgt,$duration,$this->sys_regfee_budget,$action=2,$this->usercode,$this->remote_city_flag,$this->module,$this>appserver_cs);
			$sys_package_budget=$this->p_bgt;
			$Budgetarray[1]["budget"]	=$this->p_bgt;
			$Budgetarray[1]["original_budget"]	=$this->p_bgt;
			$Budgetarray[1]["original_actual_budget"]	=$this->p_bgt;
			$Budgetarray[1]["recalculate_flag"]	=1;
		}else
		{			
			$Budgetarray[1]["budget"] =0;
			$Budgetarray[1]["original_budget"] =0;
			$Budgetarray[1]["original_actual_budget"] =0;
			$Budgetarray[1]["recalculate_flag"]	=0;
		}

		if($this->sys_regfee_budget>0)
		{
			$sys_regfee_budget=$this->sys_regfee_budget;
			$Budgetarray[7]["budget"]	=$this->sys_regfee_budget;
			$Budgetarray[7]["original_budget"]	=$this->sys_regfee_budget;
			$Budgetarray[7]["recalculate_flag"]	=1;
		}else
		{			
			$Budgetarray[7]["budget"] =0;
			$Budgetarray[7]["original_budget"] =0;
			$Budgetarray[7]["recalculate_flag"]	=0;
		}		

		$tbl_bidding_details_summary_insert=" INSERT INTO tbl_bidding_details_summary set 
		parentid 			='".$this->parentid."',
		version 			='".$this->version."',
		sys_fp_budget		='".$sys_fp_budget."',
		sys_package_budget	='".$sys_package_budget."',
		sys_regfee_budget	='".$sys_regfee_budget."',
		sys_total_budget	='".$this->sys_total_budget."',
		exactrenewal		='".$this->exactrenewal."',
		duration			=".$duration.",
		updatedon			='".date('Y-m-d H:i:s')."',
		updatedby			='".addslashes(stripcslashes($this->usercode))."'
		ON DUPLICATE KEY UPDATE
		sys_fp_budget		='".$sys_fp_budget."',
		sys_package_budget	='".$sys_package_budget."',
		sys_regfee_budget	='".$sys_regfee_budget."',
		sys_total_budget	='".$this->sys_total_budget."',
		exactrenewal		='".$this->exactrenewal."',
		duration			=".$duration.",
		updatedon			='".date('Y-m-d H:i:s')."',
		updatedby			='".addslashes(stripcslashes($this->usercode))."'";
		
		 //echo $tbl_bidding_details_summary_insert		;
		parent::execQuery($tbl_bidding_details_summary_insert, $this->db_budgeting);
				
		foreach($Budgetarray as $campaignid =>$campaignarr)
		{			
			$this->financeInsertUpdateTemp($campaignid,array("budget"=>$campaignarr["budget"],"original_budget"=>$campaignarr['original_budget'],"original_actual_budget"=>$campaignarr['original_actual_budget'],"duration"=>$duration,"recalculate_flag"=>$campaignarr['recalculate_flag'],"version" =>$this->version));
		}
		

			$getpayment_type="select * from tbl_payment_type where parentid='".$this->parentid."' and (find_in_set('combo1_1yr_dis',payment_type) <> 0 or  find_in_set('combo1_2yr_dis',payment_type) <> 0 or find_in_set('pck_1yr_dis',payment_type) <> 0 or find_in_set('pck_2yr_dis',payment_type) <> 0 ) and version='".$this->version."'";
				$res_payment 	=  parent::execQuery($getpayment_type, $this->fin);
				$multiplier_per=0;
				$multiplier_name='';
				$flag_mul=0;
				if(mysql_num_rows($res_payment)>0)
				{
					while($row_payment = mysql_fetch_assoc($res_payment))
					{
						$multiplier_name=$row_payment['payment_type'];
						$flag_mul=1;

					}
				}
			$budget_got=$Budgetarray[1]["budget"];
			$multiplier_name=explode(',', $multiplier_name);
			if($this->package_10dp_2yr=='1' || $this->package_10dp_2yr==1 ||  $flag_mul==1){
			
				$sqlmul="select campaign_multiplier from d_jds.tbl_business_uploadrates where city='".$this->data_city."'";
				$sqlmul_res 	=  parent::execQuery($sqlmul, $this->dbConIro);
				$multiplier=0;
				if(mysql_num_rows($sqlmul_res))
				{
					$sqlmul_row = mysql_fetch_assoc($sqlmul_res);
					$multiplier=$sqlmul_row['campaign_multiplier']; 
				}
				
				
				if($multiplier_name!=''){
					

					if(in_array('pck_1yr_dis',$multiplier_name) || in_array('combo1_1yr_dis',$multiplier_name)){
						$budget_got=$Budgetarray[1]["budget"];
						$service=(($budget_got*100)/85);
						$multiplier=$service/$budget_got;
					}
					if(in_array('combo1_2yr_dis',$multiplier_name) || in_array('pck_2yr_dis',$multiplier_name)){
						$budget_got=($Budgetarray[1]["budget"]);
						$budget_oneyr=($Budgetarray[1]["budget"]/1.5);
						$budget_2yr=$budget_oneyr*2;
						$service=$budget_2yr;
						$multiplier=$service/$budget_got;
					}

				}
				if($multiplier>0){
					 $campaign_multiplier_temp_insert = "INSERT INTO campaign_multiplier_temp SET
		                                            parentid   = '".$this->parentid."',
		                                            version  = '".$this->version."',
		                                            campaignid  = 1,
		                                            actual_budget  = '".$budget_got."',
		                                            multiplier    = '" .$multiplier. "',
		                                            usercode    = '" .$this->usercode. "',
		                                            insert_date    = '" .date('Y-m-d H:i:s') . "'
		                                            ON DUPLICATE KEY UPDATE
		                                            multiplier    = '" .$multiplier. "',
		                                            actual_budget  = '".$budget_got."',
		                                            usercode    = '" .$this->usercode. "',
		                                            insert_date    = '" .date('Y-m-d H:i:s'). "'";
					//echo $compmaster_fin_temp_insert;
		            parent::execQuery($campaign_multiplier_temp_insert, $this->conn_temp);
				}
			}
			else{
				if( !isset($this->params['budgetjson']['pck_dis']) ||  $this->params['budgetjson']['pck_dis'] != 2) {
					$del_camp_mul 	="DELETE FROM campaign_multiplier_temp WHERE parentid='".$this->parentid."' and version='".$this->version."' AND campaignid = 1 ";
					$del_camp_mul_res 	=  parent::execQuery($del_camp_mul, $this->conn_temp);
				}
			}

		/*Deleting entry from temp table of upselling details - Raj*/
		$del_upsell_temp_sql 	="DELETE FROM tbl_system_budget_temp WHERE parentid='".$this->parentid."'";
		$del_upsell_temp_res 	=  parent::execQuery($del_upsell_temp_sql, $this->intermediate);
		
		/*Deleting entry from temp table of upselling details - Raj*/
		
	}

	function financeInsertUpdateTemp($campaignid,$camp_data) {

        if ($campaignid>0 && is_array($camp_data)) {

            $insert_str = '';
            foreach($camp_data as $column_key => $column_value) {

                $temp_str    = $column_key ."='".$column_value . "'";
                $insert_str .= (($insert_str=='') ? $temp_str : ','.$temp_str) ;
            }

			$compmaster_fin_temp_insert = "INSERT INTO tbl_companymaster_finance_temp SET
                                            ". $insert_str.",
                                            sphinx_id   = '".$this->sphinx_id."',
                                            campaignid  = '".$campaignid."',
                                            parentid    = '" . $this->parentid . "'
                                            ON DUPLICATE KEY UPDATE
                                            " . $insert_str . "";//exit;
			//echo $compmaster_fin_temp_insert;
            parent::execQuery($compmaster_fin_temp_insert, $this->finance_temp);


        }
        return 0;

    }

	function getbudget()
	{
		$res = null;
		$catidArray = array();
		$dataarry = array();
		$sql =" select catid,pincode_list,cat_budget from tbl_bidding_details_intermediate where parentid	='".$this->parentid."' and version ='".$this->version."'";
		$res = parent::execQuery($sql, $this->db_budgeting);

		if(mysql_num_rows($res)==0)
		{
			$sql =" select catid,pincode_list,cat_budget from tbl_bidding_details_intermediate_archive where parentid	='".$this->parentid."' and version ='".$this->version."'";
			$res = parent::execQuery($sql, $this->db_budgeting);
		}
		
		if(mysql_num_rows($res))
		{
			while($temparry = mysql_fetch_assoc($res))
			{
				$pincode_jsonarr = json_decode($temparry['pincode_list'],true);

				array_push($catidArray,$temparry['catid']);
				$dataarry['c_data'][$temparry['catid']]['c_bgt']=$temparry['cat_budget'];
				
				foreach($pincode_jsonarr as $pincodekey => $pincodeposarr)
				{
					$pincode= $pincodeposarr['pin'] ;
					$positionval= $pincodeposarr['pos'] ;
										
					$dataarry['c_data'][$temparry['catid']]['pin_data'][$pincode][$positionval]= array('pin'=>$pincode,'bidvalue'=>$pincodeposarr['bidvalue'],'budget'=>$pincodeposarr['budget'],'inventory'=>$pincodeposarr['inv']);
				}
			}
		}

		// we have to give name of all categories
		if(count($catidArray))
		{
			$catidresArray= $this->getCategoryName(implode(',',$catidArray));
			foreach($catidresArray as $catid=>$catname)
			{
				$dataarry['c_data'][$catid]['cnm']=$catname;
			}
		}

		$BiddingDetailsSummary = $this->getBiddingDetailsSummary();

		$dataarry['packageBudget']	= $BiddingDetailsSummary['sys_package_budget'];
		$dataarry['pdgBudget']		= $BiddingDetailsSummary['sys_fp_budget'];
		$dataarry['reg_bgt']		= $BiddingDetailsSummary['sys_regfee_budget'];
		$dataarry['tenure']			=$BiddingDetailsSummary['duration'];

		//$dataarry['totBudget']		=$BiddingDetailsSummary['sys_total_budget'];
		$dataarry['totBudget']		= round($dataarry['reg_bgt']+$dataarry['pdgBudget']+$dataarry['packageBudget']);
		
		return $dataarry;
	}
	
	
	function getActbudget()
	{
		$res = null;
		$catidArray = array();
		$dataarry = array();
		$sql =" select catid,pincode_list,cat_budget from tbl_bidding_details_intermediate where parentid	='".$this->parentid."' and version ='".$this->version."'";
		$res = parent::execQuery($sql, $this->db_budgeting);

		if(mysql_num_rows($res)==0)
		{
			$sql =" select catid,pincode_list,cat_budget from tbl_bidding_details_intermediate_archive where parentid	='".$this->parentid."' and version ='".$this->version."'";
			$res = parent::execQuery($sql, $this->db_budgeting);
		}
		
		if(mysql_num_rows($res))
		{
			while($temparry = mysql_fetch_assoc($res))
			{
				$pincode_jsonarr = json_decode($temparry['pincode_list'],true);

				array_push($catidArray,$temparry['catid']);
				$dataarry['c_data'][$temparry['catid']]['c_bgt']=$temparry['cat_budget'];
				
				foreach($pincode_jsonarr as $pincodekey => $pincodeposarr)
				{
					$pincode= $pincodeposarr['pin'] ;
					$positionval= $pincodeposarr['pos'] ;
										
					$dataarry['c_data'][$temparry['catid']]['pin_data'][$pincode][$positionval]= array('pin'=>$pincode,'bidvalue'=>$pincodeposarr['bidvalue'],'budget'=>$pincodeposarr['budget'],'inventory'=>$pincodeposarr['inv']);
				}
			}
		}

		// we have to give name of all categories
		if(count($catidArray))
		{
			$catidresArray= $this->getCategoryName(implode(',',$catidArray));
			foreach($catidresArray as $catid=>$catname)
			{
				$dataarry['c_data'][$catid]['cnm']=$catname;
			}
		}

		$BiddingDetailsSummary = $this->getBiddingDetailsSummary();

		$dataarry['packageBudget']	= $BiddingDetailsSummary['actual_package_budget'];
		$dataarry['pdgBudget']		= $BiddingDetailsSummary['actual_fp_budget'];
		$dataarry['reg_bgt']		= $BiddingDetailsSummary['actual_regfee_budget'];
		$dataarry['tenure']			=$BiddingDetailsSummary['duration'];
		$dataarry['updatedby']		=$BiddingDetailsSummary['updatedby'];
		//$dataarry['totBudget']		=$BiddingDetailsSummary['sys_total_budget'];
		$dataarry['totBudget']		= round($dataarry['reg_bgt']+$dataarry['pdgBudget']+$dataarry['packageBudget']);
		
		return $dataarry;
	}
	

	function getbudgetCompletejson()
	{
		$insjsonsql= " select budgetjson from  tbl_bidding_details_budgetjson where parentid ='".$this->parentid."' AND	version  ='".$this->version."'";
		$res= parent::execQuery($insjsonsql, $this->db_budgeting);
		$resultarr= array();
		if($res && mysql_num_rows($res))
		{
			while($arr= mysql_fetch_assoc($res))
			{
				$resultarr['budgetjson']=json_decode($arr['budgetjson'],true);
			}
		}
		return $resultarr;
		
	}

	function updateActualBudget()
	{
		
		if($this->params['actual_total_budget']==0)		
		{
			$array['error_code']=1;
			$array['message']='Invalid actual_total_budget :-'.$this->params['actual_total_budget'];
			
			#mail('prameshjha@justdial.com,srinivasgajangi@justdial.com','updateActualBudget issue '.'--'.$this->usercode.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],json_encode($this->params));
			return $array;			
		}
		
		
		
		if((intval($this->params['actual_fp_budget']) + intval($this->params['actual_package_budget']) ) == 0) 
		{
			$array['error_code']=1;
			$erromsg ='Invalid Budget found, ';
			
			if($this->params['actual_fp_budget']==0 && $this->params['actual_package_budget']==0)
			{
				$erromsg .=' actual_fp_budget : '.$this->params['actual_package_budget'].', actual_package_budget : '.$this->params['actual_package_budget'];
				
			}elseif($this->params['actual_fp_budget']==0)
			{
				$erromsg ='actual_fp_budget : '.$this->params['actual_fp_budget'];
			}
			elseif($this->params['actual_package_budget']==0)
			{
				$erromsg .=' actual_package_budget : '.$this->params['actual_package_budget'];
			}
									
			$array['message']=$erromsg;
			
			
			#mail('prameshjha@justdial.com,srinivasgajangi@justdial.com','updateActualBudget issue '.'--'.$this->usercode.'--'.$this->parentid.' - '.$this->version.'-'.$this->params['data_city'],json_encode($this->params));			
			return $array;
			
		}
		
		if($this->genio_lite_daemon)
		{
			$deal_close_flag = "dealclosed_flag = 0 ,";
		}
		
		if($this->actual_duration)
		{
			$actual_duration_update = "duration = '".$this->actual_duration."' ,";
		}
		
		$this->params['actual_regfee_budget'] = ( $this->params['actual_regfee_budget'] > $this-> minimum_reg_fee ) ? $this->params['actual_regfee_budget'] : 0;
		
		
		$sqlUpdtActualBudget = "INSERT INTO tbl_bidding_details_summary SET 
								parentid 				= '".$this->parentid."',
								version 				= '".$this->version."',
								actual_fp_budget		= '".$this->params['actual_fp_budget']."',				
								actual_package_budget	= '".$this->params['actual_package_budget']."',				
								actual_regfee_budget	= '".$this->params['actual_regfee_budget']."',				
								actual_total_budget		= '".$this->params['actual_total_budget']."',
								".$deal_close_flag."
								".$actual_duration_update."
								updatedon				= '".date('Y-m-d H:i:s')."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'
								ON DUPLICATE KEY UPDATE
								actual_fp_budget		= '".$this->params['actual_fp_budget']."',				
								actual_package_budget	= '".$this->params['actual_package_budget']."',				
								actual_regfee_budget	= '".$this->params['actual_regfee_budget']."',				
								actual_total_budget		= '".$this->params['actual_total_budget']."',
								".$deal_close_flag."
								".$actual_duration_update."
								updatedon				= '".date('Y-m-d H:i:s')."',
								updatedby				= '".addslashes(stripcslashes($this->usercode))."'";
		$resUpdtActualBudget = parent::execQuery($sqlUpdtActualBudget, $this->db_budgeting);
		if($resUpdtActualBudget)
		{
			$array['error_code']=0;
			$array['message']='Sucess';
		}
		else
		{
			$array['error_code']=0;
			$array['message']='Unsucess';
		}
		return $array;
	}

	function updateActualBudgetNEW()
	{
		if($this->params['trace'])
		{
			echo '<pre>';print_r($this->params);
		}
		if(trim($this->params['version']) != "" && $this->params['version'] != null)
		{
			$this->version  = $this->params['version']; //initialize datacity
		}
		else
		{
			$return['error_code']= 1;
			$return['message']   = 'Version is missing';
			return $return;		
		}
		
		if($this->params['readjust_actfp_budget'] <= 0 && $this->params['readjust_actpackage_budget'] <= 0)
		{
			$return['error_code']=1;
			$return['message']='Invalid actual budget found : PDG  - '.$this->params['readjust_actfp_budget'].' :: PK - '.$this->params['readjust_actpackage_budget'];
			return $return;		
		}
		
		if($this->params['readjust_duration'] <= 0)		
		{
			$return['error_code']=1;
			$return['message']='Invalid actual duration :-'.$this->params['readjust_duration'];
			return $return;			
		}
		
		if((intval($this->params['readjust_actfp_budget']) + intval($this->params['readjust_actpackage_budget']) ) <= 0) 
		{
			$return['error_code']=1;
			$return['message']='Invalid actual total budget found : PDG  - '.$this->params['readjust_actfp_budget'].' :: PK - '.$this->params['readjust_actpackage_budget'];
			return $return;			
		}
		
		
		$sqlUpdtReadjustActualBudget = "UPDATE tbl_bidding_details_summary 
										SET
									 readjust_actfp_budget		= '".$this->params['readjust_actfp_budget']."',
									 readjust_actpackage_budget	= '".$this->params['readjust_actpackage_budget']."', 
									 readjust_actregfee_budget	= '".$this->params['readjust_actregfee_budget']."', 
									 readjust_total_budget		= '".(intval($this->params['readjust_actfp_budget']) + intval($this->params['readjust_actpackage_budget']))."', 
									 readjust_duration			= '".$this->params['readjust_duration']."', 
									 updatedon					= '".date('Y-m-d H:i:s')."',
									 updatedby					= '".addslashes(stripcslashes($this->usercode))."'
										WHERE  parentid = '".$this->parentid."' AND version	= '".$this->version."'";
		$resUpdtReadjustActualBudget = parent::execQuery($sqlUpdtReadjustActualBudget, $this->db_budgeting);
		if($this->params['trace'])
		{
			echo '<br> sql => '.$sqlUpdtReadjustActualBudget;
			echo '<br> res => '.$resUpdtReadjustActualBudget;					
		}
		if($resUpdtReadjustActualBudget)
		{
			$return['error_code']=0;
			$return['message']='Sucess';
		}
		else
		{
			$return['error_code']=0;
			$return['message']='Unsucess';
		}
		return $return;
	}
	
	function getCategoryName($catlist)
	{
		$resultarr= array();
		//$sql ="select catid,category_name from tbl_categorymaster_generalinfo where catid in (".$catlist.") ";

		$cat_params = array();
		$cat_params['page'] 	= 'budgetsubmitclass';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name';

		$where_arr  	=	array();
		if($catlist!=''){
			$where_arr['catid']			= $catlist;		
			$cat_params['where']		= json_encode($where_arr);

			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}
		//$res = parent::execQuery($sql,$this->dbConDjds);

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key=>$cat_arr)
			{
				$resultarr[$cat_arr['catid']]=$cat_arr['category_name'];
			}
		}
		return $resultarr;
	}


	function getBiddingDetailsSummary()
	{		
		$resultarr= array();
		$sql ="select * from tbl_bidding_details_summary where parentid	='".$this->parentid."' and version='".$this->version."'";
		$res = parent::execQuery($sql,$this->db_budgeting);

		if($res && mysql_num_rows($res))
		{
			$resultarr= mysql_fetch_assoc($res);
			
		}
		return $resultarr;
	}


	function curlCallLog($fields)
	{	
	
		if($_SERVER["SERVER_ADDR"]==DEVLP_APP_IP)
		{
			$url ="http://prameshjha.jdsoftware.com/csgenio/api/api.CampaignBudgetLog.php";
		}
		else
		{
		  $url ='http://'.$fields['strcityIP'].'/api/api.CampaignBudgetLog.php';
		}
		
		//echo "<pre> inside fields -"; print_r($fields);
		$ch = curl_init();
		# Set Options #
		
		$ch = curl_init($url);	
		
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if($fields!=''){
			curl_setopt($ch,CURLOPT_POST, TRUE);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
		}
		# Execute Call #
		try {
		$resultString = curl_exec($ch);
		
		}catch (Exception $e) {
			echo curl_error($ch);
		}
		
		curl_close($ch);
		
		return $resultString;
	}


	function GetSetBudgetLog($campaignid,$budget,$duration,$regfee,$action,$usercode,$rflag,$module,$strcityIP)
	{
		return 1;// currently we are not using it 
		$request_array = array(
					 'parentid'	  			=> urlencode($this -> parentid), 
					 'data_city'  			=> urlencode($this->data_city),
					 'module'	  			=> $this->module,
					 'usercode'				=> urlencode($this->usercode),
					 'action'    			=> urlencode($action),
					 'remote'				=> $rflag,
					 'campaignid'			=> urlencode($campaignid),
					 'budget'				=> urlencode($budget),
					 'duration'				=> urlencode($duration),
					 'regfee'				=> urlencode($regfee),
					 'strcityIP'			=> $strcityIP
					 
					 );
		$result = $this->curlCallLog($request_array);
		//$result = $this ->decode_json($result);
		return $result;
	}


	function updateNonpaidCategory($nonpaidCatStr)
	{
		//echo "updateNonpaidCategory";

		$nonpaid_catidlist =$nonpaidCatStr;
		$catidlineage_nonpaid_edshadow_arr = array();
		$nonpaid_catidlist_arr = array();
		if($nonpaid_catidlist)
		{
			$nonpaid_catidlist_arr = explode(',',$nonpaid_catidlist);
			$nonpaid_catidlist_arr = array_unique(array_filter($nonpaid_catidlist_arr));
		}

		
		
		
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			$mongo_inputs = array();
			$mongo_inputs['parentid'] 	= $this->parentid;
			$mongo_inputs['data_city'] 	= $this->data_city;
			$mongo_inputs['module']		= $this->module;
			$mongo_inputs['table'] 		= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['fields'] 	= "catidlineage_nonpaid";
			$arrselectShadow = $this->mongo_obj->getData($mongo_inputs);
		}else{
			$sqlselectShadow = "select catidlineage_nonpaid from tbl_companymaster_extradetails_shadow where parentid = '".$this->parentid."'";
			$resselectShadow   = parent::execQuery($sqlselectShadow, $this->conn_temp);
			$num_rows 			= parent::numRows($resselectShadow);
			if($num_rows > 0 ){
				$arrselectShadow = parent::fetchData($resselectShadow);
			}
			
		}

		if(count($arrselectShadow)>0)
		{
			$catidlineage_nonpaid_edshadow_str = $arrselectShadow['catidlineage_nonpaid'];

			if(trim($catidlineage_nonpaid_edshadow_str))
			{
				$catidlineage_nonpaid_edshadow_str = str_replace('/','',$catidlineage_nonpaid_edshadow_str);
				$catidlineage_nonpaid_edshadow_arr= explode(',',$catidlineage_nonpaid_edshadow_str);
				$catidlineage_nonpaid_edshadow_arr = array_unique($catidlineage_nonpaid_edshadow_arr);
				
			}
		}

		if(count($nonpaid_catidlist_arr)>0 ||  count($catidlineage_nonpaid_edshadow_arr)>0)
		{
			$catidlineage_nonpaid_edshadow_arr = array_merge($catidlineage_nonpaid_edshadow_arr,$nonpaid_catidlist_arr);
			$catidlineage_nonpaid_edshadow_arr = array_unique($catidlineage_nonpaid_edshadow_arr);
		}
		

		if(count($catidlineage_nonpaid_edshadow_arr))
		{
			$catidlineage_nonpaid_str = "/".implode('/,/',$catidlineage_nonpaid_edshadow_arr)."/";
			if($this->mongo_flag == 1 || $this->mongo_tme == 1){
				$mongo_inputs = array();
				$mongo_inputs['parentid'] 	= $this->parentid;
				$mongo_inputs['data_city'] 	= $this->data_city;
				$mongo_inputs['module']		= $this->module;
				$mongo_data = array();
				$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
				$extrdet_upt = array();
				$extrdet_upt['catidlineage_nonpaid'] 	= $catidlineage_nonpaid_str;
				$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
				$mongo_inputs['table_data'] 			= $mongo_data;
				$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
			}else{
				$sqlUpdateExtraShadow = "UPDATE tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '".$catidlineage_nonpaid_str."' WHERE parentid = '".$this->parentid."'";
				$sqlUpdateExtraShadow = $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
				$resUpdateExtraShadow   = parent::execQuery($sqlUpdateExtraShadow, $this->conn_temp);	
			}
		}
		
		return $result_msg_arr;
}

	function populate_category_temp_data($remove_catidlist_arr)
	{
		if(count($remove_catidlist_arr)>0)
		{
			$this->add_catlin_nonpaid_db = 0;
			if(($this->module == 'de') || ($this->module == 'cs'))
			{
				$this->add_catlin_nonpaid_db = 1;
			}	
			$contract_existing_temp_cat_arr = $this->fetch_contract_temp_categories();
			$this->remove_catidlist_arr 		= $remove_catidlist_arr;
			if(count($this->temp_paid_cat_arr) >0)
			{
				if(count($this->remove_catidlist_arr)>0)	
				{
					$final_temp_paid_cat_arr = array();
					$matched_paid_cat_arr = array();
					$matched_paid_cat_arr = array_intersect($this->temp_paid_cat_arr,$this->remove_catidlist_arr);
					if(count($matched_paid_cat_arr)>0)
					{
						$final_temp_paid_cat_arr = array_diff($this->temp_paid_cat_arr,$matched_paid_cat_arr);
					}
					else
					{
						$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
					}
				}
				else
				{
					$final_temp_paid_cat_arr = $this->temp_paid_cat_arr;
				}
			}
			if(count($final_temp_paid_cat_arr)>0)
			{
				$all_temp_paid_cat_arr = $this->getCategoryDetails($final_temp_paid_cat_arr);
				
				$catids_arr = array();
				$catnames_arr = array();
				$national_catids_arr = array();
				$i = 1;
				if(count($all_temp_paid_cat_arr)>0)
				{
					foreach($all_temp_paid_cat_arr as $catid => $catinfo_arr)
					{
						$catname 		= trim($catinfo_arr['catname']);
						$national_catid = trim($catinfo_arr['national_catid']);					
						$catids_arr[] 			= $catid;
						$catnames_arr[] 		= $catname;
						$national_catids_arr[] 	= $national_catid;
					}
				}
				$catnames_str = "|P|";
				$catnames_str .= implode("|P|",$catnames_arr);
				
				$catids_str = "|P|";
				$catids_str .= implode("|P|",$catids_arr);
				
				$national_catids_str = "|P|";
				$national_catids_str .= implode("|P|",$national_catids_arr);
				
				$catname_sel = str_ireplace("|P|","|~|",$catnames_str);
				 
				 
				$categories 	= $this->slasher($catnames_str);
				$catSelected 	= $this->slasher($catname_sel);
							 
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){				
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
					
					$bustemp_tbl 		= "tbl_business_temp_data";
					$bustemp_upt = array();
					$bustemp_upt['categories'] 				= $categories;
					$bustemp_upt['catIds'] 					= $catids_str;
					$bustemp_upt['nationalcatIds'] 			= $national_catids_str;
					$bustemp_upt['catSelected'] 			= $catSelected;
					$bustemp_upt['categories_list'] 		= '';
					$mongo_data[$bustemp_tbl]['updatedata'] = $bustemp_upt;
					
					$mongo_inputs['table_data'] 			= $mongo_data;
					$resUpdtBusinessTempData = $this->mongo_obj->updateData($mongo_inputs);
				}
				else
				{
					// Update tbl_business_temp_data
					$sqlUpdtBusinessTempData = "UPDATE tbl_business_temp_data SET 
											categories 		= '".$categories."',
											catIds 			= '".$catids_str."',
											nationalcatIds 	= '".$national_catids_str."',
											catSelected 	= '".$catSelected."', 
											categories_list	= ''
											WHERE contractid='".$this->parentid."'";
					$sqlUpdtBusinessTempData 	= $sqlUpdtBusinessTempData."/* TMEMONGOQRY */";						
					$resUpdtBusinessTempData 	= parent::execQuery($sqlUpdtBusinessTempData, $this->intermediate);
				}
			}
			
			if(count($this->temp_nonpaid_cat_arr) >0)
			{
				if(count($this->remove_catidlist_arr)>0)	
				{
					$final_temp_nonpaid_cat_arr 	= 	array();
					$matched_nonpaid_cat_arr 		= 	array();				
					$matched_nonpaid_cat_arr 		= 	array_intersect($this->temp_nonpaid_cat_arr,$this->remove_catidlist_arr);
					
					if(count($matched_nonpaid_cat_arr)>0)
					{
						$final_temp_nonpaid_cat_arr = array_diff($this->temp_nonpaid_cat_arr,$matched_nonpaid_cat_arr);
					}
					else
					{
						$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
					}
				}
				else
				{
					$final_temp_nonpaid_cat_arr = $this->temp_nonpaid_cat_arr;
				}
			}
			if(count($final_temp_nonpaid_cat_arr)>0)
			{
				$all_temp_nonpaid_cat_arr = $this->getCategoryDetails($final_temp_nonpaid_cat_arr);
				
				$catids_nonpaid_arr = array();
				$national_catids_nonpaid_arr = array();
				$i = 1;
				if(count($all_temp_nonpaid_cat_arr)>0)
				{
					foreach($all_temp_nonpaid_cat_arr as $catid => $catinfo_arr)
					{
						$catname 		= trim($catinfo_arr['catname']);
						$national_catid = trim($catinfo_arr['national_catid']);
						
						$catids_nonpaid_arr[] 			= $catid;
						$national_catids_nonpaid_arr[] 	= $national_catid;
					}
				}
				
				$catidlineage_nonpaid 			= "/".implode('/,/',$catids_nonpaid_arr)."/";
				$national_catidlineage_nonpaid 	= "/".implode('/,/',$national_catids_nonpaid_arr)."/";
				
				$catlin_nonpaid_db = '';
				if($this->add_catlin_nonpaid_db == 1)
				{
					$catlin_nonpaid_db = 'db_iro.';
				}
				if($this->mongo_flag == 1 || $this->mongo_tme == 1){
					$mongo_inputs = array();
					$mongo_inputs['parentid'] 	= $this->parentid;
					$mongo_inputs['data_city'] 	= $this->data_city;
					$mongo_inputs['module']		= $this->module;
					$mongo_data = array();
					$extrdet_tbl 		= "tbl_companymaster_extradetails_shadow";
					$extrdet_upt = array();
					$extrdet_upt['catidlineage_nonpaid'] 			= $catidlineage_nonpaid;
					$extrdet_upt['national_catidlineage_nonpaid'] 	= $national_catidlineage_nonpaid;
					$mongo_data[$extrdet_tbl]['updatedata'] = $extrdet_upt;
					$mongo_inputs['table_data'] 			= $mongo_data;
					$resUpdateExtraShadow = $this->mongo_obj->updateData($mongo_inputs);
				}else{
					$sqlUpdateExtraShadow = "UPDATE ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow SET catidlineage_nonpaid = '".$catidlineage_nonpaid."', national_catidlineage_nonpaid = '".$national_catidlineage_nonpaid."'  WHERE parentid = '".$this->parentid."'";
					$sqlUpdateExtraShadow 	= $sqlUpdateExtraShadow."/* TMEMONGOQRY */";
					$resUpdateExtraShadow 	= parent::execQuery($sqlUpdateExtraShadow, $this->intermediate);
				}
			}
			$result_msg_arr['error']['code'] = 0;
			$result_msg_arr['error']['message'] = "Sucess";
		}
		else
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['message'] = "Remove Category Count is 0";
		}
		return $result_msg_arr;		
	}
 	function fetch_contract_temp_categories()
	{
		$temp_category_arr = array();
		$catlin_nonpaid_db = '';
		if($this->add_catlin_nonpaid_db == 1)
		{
			$catlin_nonpaid_db = 'db_iro.';
		}
		if($this->mongo_flag == 1 || $this->mongo_tme == 1){
			
			$mongo_inputs = array();
			$mongo_inputs['data_city'] 		= $this->data_city;
			$mongo_inputs['module']			= $this->module;
			$mongo_inputs['t1'] 			= "tbl_business_temp_data";
			$mongo_inputs['t2'] 			= "tbl_companymaster_extradetails_shadow";
			$mongo_inputs['t1_on'] 			= "contractid";
			$mongo_inputs['t2_on'] 			= "parentid";
			$mongo_inputs['t1_fld'] 		= "";
			$mongo_inputs['t2_fld'] 		= "catidlineage_nonpaid";
			$mongo_inputs['t1_mtch'] 		= json_encode(array("contractid"=>$this->parentid));
			$mongo_inputs['t2_mtch']		= "";
			$mongo_inputs['t1_alias'] 		= json_encode(array("catIds"=>"catidlineage"));
			$mongo_inputs['t2_alias'] 		= "";
			$mongo_join_data 	= $this->mongo_obj->joinTables($mongo_inputs);
			$row_temp_category 	= $mongo_join_data[0];
			
		}else{
			$sqlTempCategory	=	"SELECT catids as catidlineage,catidlineage_nonpaid FROM tbl_business_temp_data as A LEFT JOIN ".$catlin_nonpaid_db."tbl_companymaster_extradetails_shadow as B on A.contractid=B.parentid WHERE contractid = '" . $this->parentid . "'";
			$resTempCategory 	= parent::execQuery($sqlTempCategory, $this->intermediate);
			$num_rows 			= parent::numRows($resTempCategory);
			if($num_rows > 0 ){
				$row_temp_category = parent::fetchData($resTempCategory);
			}
		}

		if(count($row_temp_category)>0)
		{
			if((isset($row_temp_category['catidlineage']) && $row_temp_category['catidlineage'] != '') || (isset($row_temp_category['catidlineage_nonpaid']) && $row_temp_category['catidlineage_nonpaid'] != ''))
			{
				$temp_catlin_arr 	= 	array();
				$temp_catlin_arr  	=   explode('|P|',$row_temp_category['catidlineage']);
				$temp_catlin_arr 	= 	array_filter($temp_catlin_arr);
				$temp_catlin_arr 	= 	$this->get_valid_categories($temp_catlin_arr);
				
				$temp_catlin_np_arr =  array();
				$temp_catlin_np_arr =  explode("/,/",trim($row_temp_category['catidlineage_nonpaid'],"/"));
				$temp_catlin_np_arr =  array_filter($temp_catlin_np_arr);
				$temp_catlin_np_arr =  $this->get_valid_categories($temp_catlin_np_arr);
				
				
				$total_catlin_arr = array();
				$total_catlin_arr =  array_merge($temp_catlin_arr,$temp_catlin_np_arr);
				$total_catlin_arr = array_merge(array_filter($total_catlin_arr));
				$temp_category_arr = $this->get_valid_categories($total_catlin_arr);
				
				$this->temp_paid_cat_arr = $temp_catlin_arr;
				$this->temp_nonpaid_cat_arr = $temp_catlin_np_arr;
			}
		}
 		return $temp_category_arr; 
	}
	function getCategoryDetails($catids_arr)
	{
		$CatinfoArr = array();
		$catids_str = implode("','",$catids_arr);
		//$sqlCategoryDetails = "SELECT catid,category_name,national_catid FROM tbl_categorymaster_generalinfo WHERE catid IN ('".$catids_str."')";
		//$resCategoryDetails 	= parent::execQuery($sqlCategoryDetails, $this->dbConDjds);
		$cat_params = array();
		$cat_params['page'] 	= 'budgetsubmitclass';
		$cat_params['data_city'] 	= $this->data_city;			
		$cat_params['return']		= 'catid,category_name,national_catid';

		$where_arr  	=	array();
		if(count($catids_arr)>0){
			$where_arr['catid']			= implode(",",$catids_arr);		
			$cat_params['where']		= json_encode($where_arr);
			$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);
		}
		$cat_res_arr = array();
		if($cat_res!=''){
			$cat_res_arr =	json_decode($cat_res,TRUE);
		}

		if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0)
		{
			foreach($cat_res_arr['results'] as $key=>$cat_arr)
			{
				$catid 			= trim($cat_arr['catid']);
				$category_name	= trim($cat_arr['category_name']);
				$national_catid	= trim($cat_arr['national_catid']);
				$CatinfoArr[$catid]['catname'] = $category_name;
				$CatinfoArr[$catid]['national_catid'] = $national_catid;
			}
		}
		return $CatinfoArr;
	}
	function get_valid_categories($total_catlin_arr)
	{
		$final_catids_arr = array();
		if((!empty($total_catlin_arr)) && (count($total_catlin_arr) >0))
		{
			foreach($total_catlin_arr as $catid)
			{
				$final_catid = 0;
				$final_catid = preg_replace('/[^0-9]/', '', $catid);
				if(intval($final_catid)>0)
				{
					$final_catids_arr[]	= $final_catid;
				}
			}
			$final_catids_arr = array_filter($final_catids_arr);
			$final_catids_arr = array_unique($final_catids_arr);
		}
		return $final_catids_arr;	
	}
	function slasher($arr)
	{
		if(is_array($arr))
		{
			foreach($arr as $key=>$value)
			{
				$arr[$key] = addslashes(stripslashes($value));
			}
		}
		else
		{
			$arr = addslashes(stripslashes($arr));
		}
		return $arr;		
	}
	
	function get_live_inventory()
	{
		
		$sql	="SELECT * FROM tbl_bidding_details WHERE parentid ='".$this->parentid."' ORDER BY catid, pincode";
		$res 	= parent::execQuery($sql, $this->fin);
		$num	= mysql_num_rows($res);
		
		if($res && $num > 0)
		{
			while($row=mysql_fetch_assoc($res))
			{
				$inv_seeked[$row['catid']][$row['pincode']][$row['position_flag']]['i'] 	  = $row['inventory'];	
			}
		} 
		else
		{
			$sql_bde	="SELECT * FROM tbl_bidding_details_expired WHERE parentid ='".$this->parentid."' ORDER BY catid, pincode";
			$res_bde 	= parent::execQuery($sql_bde, $this->fin);
			$num_bde	= mysql_num_rows($res_bde);
			
			if($res_bde && $num_bde > 0)
			{
				while($row_bde=mysql_fetch_assoc($res_bde))
				{
					$inv_seeked[$row_bde['catid']][$row_bde['pincode']][$row_bde['position_flag']]['i'] 	  = $row_bde['inventory'];	
				}
			}
		} 
		
		return($inv_seeked);
	}
	
	
}



?>
