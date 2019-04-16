<?php
// changes needed
require_once(APP_PATH."library/categoryMaster.php");
require_once(APP_PATH."library/designerbanner.php");

	class spontextbanner_mgmt{
		private $parentid,$campType,$data_city,$auto_appr,$cron,$compmaster_obj;
		private $conn_fnc,$conn_temp,$conn_main,$conn_server,$conn_local,$conn_server_decs;

		public  $bool_city,$categoryMaster;
		
		const MAX_INVENTORY = '1';		//Max inventory to be available
		const MIN_INVENTORY = '0.05';	//Min inventory to be available
		//const MULTIPLIER 	= '0.25';	//Min inventory to be available
        const MULTIPLIER 	= '5';		//Min inventory to be available
		const BUDGET_MUL	= '1.33';	
		const SPON_VALUE	= '0.50';	//%age of Platinum budget
		const TEXT_VALUE	= '0.075';
		const PLAT_INCREMENT_FACTOR = '1.3';/*incrementing platinum budget from 10% to 30% - platinum factor */	
		const NONB2B_MIN_BID_VAL = '5';/*minimum bid value for non b2b category*/
		const D_B2B_MIN_BID_VAL = '100';/*minimum platinum bid value for b2b category*/
		 
		function __construct($pid,$dbarr,$dataCity,$campType,$module,$version=0,$autoappr='',$cron=''){
			
			$this->parentid = $pid;
			$this->data_city= $dataCity;
			$this->campType	= $campType;
			$this->module	= $module;
			$this->auto_appr= $autoappr;
			$this->cron		= $cron;
			$this->bool_city= 1;

			$this->setConnection($dbarr,$version);
			$this->compmaster_obj = new companyMasterClass($this->conn_server,'',$this->parentid);
			if(trim($this->data_city) == '' || $this->data_city == 'DATA_CITY'){
				$this->setDataCity();
			}
			if(trim($this->data_city) == '' || $this->data_city == 'DATA_CITY'){
				if(trim($this->auto_appr) == '' && trim($this->cron) == '')
				{
					die('Data City is blank : Category Banner');
				}
			}
			if($this->parentid == ''){
				die('Parentid is blank : Category Banner');
			}
			$this->categoryMaster = new categoryMaster($dbarr,$this->module);
			$this->min_budget	  = $this->getMinMonthlyBudget();
			
			if($campType == '13'){
				$this->banner = 1 ;
			}
		}

		function setConnection($dbarr,$version){
			if($this->module=='' || $this->module==0){
				$this->setVersionModule($version,'1');}
			if(strtolower($this->module) == 'cs'){
				$this->conn_temp	= new DB($dbarr['LOCAL']);
				$this->conn_main	= new DB($dbarr['DB_IRO']);
				$this->conn_iro_slave = new DB($dbarr['DB_IRO_SLAVE']);
				$this->conn_local	= $this->conn_temp;
				$this->conn_server	= $this->conn_main;
				$this->conn_server_decs= $this->conn_temp;
			}elseif(strtolower($this->module) == 'tme'){
				$this->conn_local	= new DB($dbarr['LOCAL']);
				$this->conn_temp	= new DB($dbarr['DB_TME']);
				$this->conn_main	= new DB($dbarr['IDC']);
				$this->conn_server	= new DB($dbarr['DB_IRO']);
				$this->conn_decs	= $this->conn_local;
				$this->conn_server_decs= $this->conn_local;
			}elseif(strtolower($this->module) == 'me'){

				$this->conn_main	= new DB($dbarr['IDC']);
				$this->conn_temp	= $this->conn_main;
				$this->conn_server	= new DB($dbarr['DB_IRO']);
				$this->conn_local	= $this->conn_main;
				$this->conn_decs	= new DB($dbarr['LOCAL']);
				$this->conn_server_decs= new DB($dbarr['LOCAL']);
			}
			$this->conn_IDC	= new DB($dbarr['IDC']);
			$this->conn_fnc	= new DB($dbarr['FINANCE']);

		}

		function setDataCity(){

			$sqlGetDataCity = "SELECT data_city FROM tbl_companymaster_generalinfo WHERE parentid ='".$this->parentid."'";
			$qryGetDataCity = $this->conn_main->query_sql($sqlGetDataCity);
			if($qryGetDataCity){
				$rowGetDataCity		= mysql_fetch_assoc($qryGetDataCity);
				if(trim($rowGetDataCity['data_city']) == ''){
					$temparr		= array();
					$fieldstr		= '';
					$fieldstr 		= "data_city";
					$tablename		= "tbl_companymaster_generalinfo";
					$wherecond		= "parentid ='".$this->parentid."'";
					$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
					$rowGetDataCity	= $temparr['data']['0'];
				}
				$this->data_city 	= $rowGetDataCity['data_city'];
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Data City Set = >".$this->data_city);
			}
			if(trim($rowGetDataCity['data_city']) == ''){
				if(trim($this->auto_appr) == '' && trim($this->cron)=='')
					die('Data City Problem in Category Sponsership/text banner/filter banner');
				else{
					$this->bool_city = 0;
					$to = 'subroto.mahindar@justdial.com,yogitatandel@justdial.com';
					$subject = "Data City problem -- ".($this->cron == '1')?'CRON':'AUTO APPROVAL';
					$message = "Data City not found in parentid = ".$this->parentid;
					mail($to,$subject,$message);
				}
			}
		}
		
		function bidder_explode_array($bidders_string){/* Function to get the bidder array */
			$bidders_array		= array();
			$bid_parentid_array = array();
			if(trim($bidders_string)!=''){
				$bidders_array 		= explode(",",$bidders_string);
			}
			if(count($bidders_array)>0)
			{
				for($i=0;$i<count($bidders_array);$i++)
				{
					unset($temp_bid);
					$temp_bid = explode("-",$bidders_array[$i]);
					$temp_bid[0] = strtoupper($temp_bid[0]);
					$bid_parentid_array[$temp_bid[0]]['inv']     = $temp_bid[1];
					$bid_parentid_array[$temp_bid[0]]['act_inv'] = ($temp_bid[2]?$temp_bid[2]:0);
					
					if($this->parentid == $temp_bid[0])
					{
						$bid_parentid_array[$temp_bid[0]]['existing'] = 1;
						
					}
					else
					{
						$bid_parentid_array[$temp_bid[0]]['existing'] = 0;
					}
				}
			}
			
			return($bid_parentid_array);
		} 
		
		function bidder_implode_array($bidderArr){
			$tempArr	= array();
			$finalStr	= '';
			if(count($bidderArr)>0){
				foreach($bidderArr as $key =>$value){
					if($value['inv'] == 0){
						unset($bidderArr[$key]);
						continue;
					}
					$tempArr[] = $key."-".$value['inv']."-".$value['act_inv'];
				}
				$finalStr	= implode(',',$tempArr);
			}
			return $finalStr;
		}
		
		function getAvailableInventory($catid,$inventory_asked,$typeFlg="-1"){/*function for getting the free inventory */
			
            $inventory_asked = $inventory_asked/100;
			$availInv = 0;
			if($this->campType == '13')
			{	
				$field = " cat_sponbanner_bidder AS bidder, cat_sponbanner_inventory AS inventory ";
				$where = " catid = '".$catid."' AND data_city ='".$this->data_city."'";
				$table = "tbl_cat_banner_bid";
			}
			elseif($this->campType == '14')
			{
				$field= " hotkey_banner AS bidder, hotkey_banner_inventory AS inventory ";
				$where = " vertical_id = '".$catid."' AND data_city ='".$this->data_city."' AND type_flag = '".$typeFlg."'";
				$table = "tbl_hotkey_banner_bid";
			}
			elseif($this->campType == '15')
			{
				$field = " cat_textbanner_bidder AS bidder, cat_textbanner_inventory AS inventory ";
				$where = " catid = '".$catid."' AND data_city ='".$this->data_city."'";
				$table = "tbl_cat_banner_bid";
			}
			$sqlGet = "SELECT ".$field." FROM ".$table." WHERE ".$where;
			$qryGet = $this->conn_fnc->query_sql($sqlGet);
			if($qryGet){
			
				$rowGet		= mysql_fetch_assoc($qryGet);
		
				$total_inventory = ($rowGet['inventory']!='')?$rowGet['inventory']:'0';
				
				$bidderArr	= $this->bidder_explode_array($rowGet['bidder']);

				if($bidderArr[$this->parentid]['existing'] == 1)
				{
					$avail_inventory['inv_avail'] = (self :: MAX_INVENTORY - $total_inventory) + $bidderArr[$this->parentid]['inv'];
				}
				else
				{
					$avail_inventory['inv_avail'] = (self :: MAX_INVENTORY - $total_inventory);
				}
                if($avail_inventory['inv_avail'] >= self :: MIN_INVENTORY)/*if minimum of 25%  is available*/
                {
                    if($inventory_asked <= $avail_inventory['inv_avail'])
                    {
                        //return $avail_inventory['availiability'] = $inventory_asked;
                       // $avail_inventory['availiability'] = (self :: MAX_INVENTORY - $total_inventory);
					    $cattype_arr = $this->set_top_flg();
						if(isset($cattype_arr[$catid]) && $cattype_arr[$catid] == 0){
							$avail_inventory['inv_avail']	= $avail_inventory['inv_avail'];
						}else{
							$avail_inventory['inv_avail']	= $inventory_asked;
						}
                    }
                    else
                    {
                        $multiplier_int=(int)(self :: MULTIPLIER);
                        $inventory_percentage= trim($avail_inventory['inv_avail']) * 100;
                        $average_percentage=(int)($inventory_percentage/(int)(self :: MULTIPLIER));
                        $avail_inventory['inv_avail']= ($multiplier_int * $average_percentage)/100;
                    }
                }
				
				$avail_inventory['availiability'] = (self :: MAX_INVENTORY - $total_inventory)+(($bidderArr[$this->parentid]['existing'] == 1)? $bidderArr[$this->parentid]['inv'] : 0);
			}
			return $avail_inventory;
		}
	
		function getCategoryDetails($catid,$flag){/* Get the snapshot of the actual present before and after booking */
			$msg 	='';
			if($this->campType == '15'){
				$field = " cat_textbanner_bidder AS bidder, cat_textbanner_inventory AS inventory, cat_textbanner_actual_inventory AS actual_inventory ";
				$where = " catid = '".$catid."'";
				$msgTxt= "Cat Spon";
				$table = "tbl_cat_banner_bid";
			}else if($this->campType == '13'){
				$field = " cat_sponbanner_bidder AS bidder, cat_sponbanner_inventory AS inventory, cat_sponbanner_actual_inventory AS actual_inventory ";
				$where = " catid = '".$catid."'";
				$msgTxt= "Cat Text";
				$table = "tbl_cat_banner_bid";
			}else if($this->campType == '14'){
				$field= " hotkey_banner AS bidder, hotkey_banner_inventory AS inventory,hotkey_banner_actual_inventory AS actual_inventory,type_flag ";
				$where = " vertical_id = '".$catid."'";
				$msgTxt= "Cat Banner";
				$table = "tbl_hotkey_banner_bid";
			}
			$qrySel	= "SELECT ".$field." FROM ".$table." WHERE ".$where." AND data_city = '".$this->data_city."'";
			$resSel	= $this->conn_fnc->query_sql($qrySel);
			 if($resSel && mysql_num_rows($resSel)>0)
			{
				$rowSel	 = mysql_fetch_assoc($resSel);
				if($flag == '1')
					$msg	.= " SnapShot Before Booking ";
				else
					$msg	.= " SnapShot After Booking ";
				$msg 	.= "[".$msgTxt." Bidder: ".$rowSel['bidder']."] ";
				$msg 	.= "[".$msgTxt." Inventory: ".$rowSel['inventory']."] ";
				$msg 	.= "[".$msgTxt." Act INVT: ".$rowSel['actual_inventory']."]";
				if($this->campType == '14'){
					$msg 		.= "[Cat Banner Type Flag: ".$rowSel['type_flag']."]";
				}
				if($rowSel)
				{
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,$msg);
				}            
			} 
		}
		
		function sponTextInvMgmt($catid,$status,$askedInventory,$campID,$national_catid=null){
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Inventory Management == >  Catid -->".$catid." Status --> ".$status." Invntory ASked --> ".$askedInventory." Camp ID -- >".$campID);
			if($campID == '13' || $campID == '15'|| $campID == '14')
			{
				if($campID == '15'){
					$field = " catid,cat_textbanner_bidder AS bidder, cat_textbanner_inventory AS inventory, cat_textbanner_actual_inventory AS actual_inventory ";
					$where = " catid = '".$catid."'";
					$table	= "tbl_cat_banner_bid";
					$this->campType='15';
				}else if($campID == '13'){
					$field = " catid,cat_sponbanner_bidder AS bidder, cat_sponbanner_inventory AS inventory, cat_sponbanner_actual_inventory AS actual_inventory ";
					$where = " catid = '".$catid."'";
					$table	 	= "tbl_cat_banner_bid";
					$this->campType='13';
				}else if($campID == '14'){
					$type_flag	= $this->getTypeFlag($catid);
					$field 		= " vertical_id AS catid,hotkey_banner AS bidder, hotkey_banner_inventory AS inventory, hotkey_banner_actual_inventory AS actual_inventory ";
					$where 		= " vertical_id = '".$catid."' AND type_flag ='".$type_flag."'";
					$table	 	= "tbl_hotkey_banner_bid";
					$this->campType='14';
				}
				$this->getCategoryDetails($catid,'1');
				$qrySel	= "SELECT ".$field." FROM ".$table." WHERE ".$where." AND data_city = '".$this->data_city."'";
				$resSel	= $this->conn_fnc->query_sql($qrySel);
				
				if($resSel && mysql_num_rows($resSel)){
					$rowSel = mysql_fetch_assoc($resSel);
					$category	= $rowSel['catid'];
					$bidder		= $rowSel['bidder'];
					$inventory	= $rowSel['inventory'];
					$actualInv	= $rowSel['actual_inventory'];
					
					if($bidder=='' || $bidder == null){ /* entry present for the category asked but not any bidder*/
						unset($bidder_array);
						if($askedInventory<=1){
							if($status == 2){
								$bidderString = $this->parentid."-".$askedInventory."-".$askedInventory;
							}elseif($status == 1){
								$bidderString = $this->parentid."-".$askedInventory."-0";
							}
							if($campID == '13'){
								$upField = "cat_sponbanner_bidder 			= '".$bidderString."',
											cat_sponbanner_inventory		= '".$askedInventory."',";
								if($status	== 2){
									$upField.= "cat_sponbanner_actual_inventory	= '".$askedInventory."',";
								}else{
									$upField.= "cat_sponbanner_actual_inventory	= 0,";
								}
								$upField.=" updated_on						= now()";
								$where	 = "catid = '".$category."'";
								$table	 = "tbl_cat_banner_bid";
							}else if($campID == '15'){
								$upField = "cat_textbanner_bidder 			= '".$bidderString."',
											cat_textbanner_inventory		= '".$askedInventory."',";
								if($status	== 2){
									$upField.= "cat_textbanner_actual_inventory	= '".$askedInventory."',";
								}else{
									$upField.= "cat_textbanner_actual_inventory	= 0,";
								}
								$upField.="updated_on						= now()";
								$where	 = "catid = '".$category."'";
								$table	 = "tbl_cat_banner_bid";
							}else if($campID == '14'){
								$upField = "hotkey_banner 					= '".$bidderString."',
											hotkey_banner_inventory			= '".$askedInventory."',";
								if($status	== 2){
									$upField.= "hotkey_banner_actual_inventory	= '".$askedInventory."',";
								}else{
									$upField.= "hotkey_banner_actual_inventory	= 0,";
								}
								$upField="updated_on						= now()";
								$where	 = "vertical_id = '".$category."' AND type_flag = '".$type_flag."'";
								$table	 = "tbl_hotkey_banner_bid";
							}
							
							$upSql	= "UPDATE ".$table." SET ".$upField." WHERE ".$where." AND data_city = '".$this->data_city."'";
							$upQry	= $this->conn_fnc->query_sql($upSql,$this->parentid);
							$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Query == >".$upSql." Result=>".$upQry);
							$this->getCategoryDetails($catid,'2');
						}else{
							$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Failed : Overbooking - ".$askedInventory );
						}
					}else{	
						$bidder_array = $this -> bidder_explode_array($bidder);/*getting bidders in an array with parentid,inventory and actual inventory*/
						if($bidder_array[$this->parentid]['existing'] == 1){
							$delta_inventory 		= $askedInventory-$bidder_array[$this->parentid]['inv'];
							$actual_delta_inventory = $askedInventory-$bidder_array[$this->parentid]['act_inv'];
							if(($inventory+$delta_inventory) <= 1 || ($delta_inventory<=0))
							{
								if($status==2){
								
									/*existing user inventory downgrade/upgrade during approval/balance readjustment';*/
									$inventory 								 = $inventory + $delta_inventory;
									$actualInv								 = $actualInv + $actual_delta_inventory;
									$bidder_array[$this->parentid]['inv'] 	 = $askedInventory;
									$bidder_array[$this->parentid]['act_inv']= $askedInventory;
								}
								elseif($delta_inventory < 0 )
								{
									// during degradation booking cant release inventory unless financial transaction happens
									$inventory 								 = $inventory;
									$actualInv								 = $actualInv;
									//$bidder_array[$this->parentid]['inv'] 	 = $bidder_array[$this->parentid]['inv']+$delta_inventory;
									$bidder_array[$this->parentid]['inv'] 	 = $bidder_array[$this->parentid]['inv'];
									$bidder_array[$this->parentid]['act_inv']= $bidder_array[$this->parentid]['act_inv'];
								}
								elseif($delta_inventory > 0 )
								{
									// during upgration booking cant release inventory unless financial transaction happens
									$inventory 								 = $inventory + $delta_inventory;
									$actualInv								 = $actualInv;
									$bidder_array[$this->parentid]['inv'] 	 = $askedInventory;
									$bidder_array[$this->parentid]['act_inv']= $bidder_array[$this->parentid]['act_inv'];
								}
							}
							else
							{
								$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Failed : Overbooking - ".$inventory ."+". $askedInventory );
							}
						}else{
							if($inventory + $askedInventory <=1)
							{
								if($status == 2)					
								{
									$inventory 								 = $inventory + $askedInventory;
									$actualInv								 = $actualInv + $askedInventory;
									$bidder_array[$this->parentid]['inv']    = $askedInventory;
									$bidder_array[$this->parentid]['act_inv']= $askedInventory;
								}
								else
								{
									$inventory 							     = $inventory + $askedInventory;
									$actualInv							     = $actualInv;
									$bidder_array[$this->parentid]['inv'] 	 = $askedInventory;
									$bidder_array[$this->parentid]['act_inv']= 0;
								}
							}
							else
							{
								$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Failed : Overbooking - ".$inventory ."+". $askedInventory );
							}
						}
						
						if(($delta_inventory!=0 && $bidder_array[$this->parentid]['existing']) || ($delta_inventory == '0' || $delta_inventory == '')){
							$bidderString	= $this->bidder_implode_array($bidder_array);
							if($campID == '13'){
								$upField	= "cat_sponbanner_bidder 			= '".$bidderString."',
											   cat_sponbanner_inventory			= '".$inventory."',
											   cat_sponbanner_actual_inventory  = '".$actualInv."',
											   updated_on						= now()";
								$table	 	= "tbl_cat_banner_bid";
								$where		= "catid = '".$catid."' AND data_city ='".$this->data_city."'";
							}
							elseif($campID == '15'){
								$upField	= "cat_textbanner_bidder 			= '".$bidderString."',
											   cat_textbanner_inventory			= '".$inventory."',
											   cat_textbanner_actual_inventory  = '".$actualInv."',
											   updated_on						= now()";
								$table		= "tbl_cat_banner_bid";
								$where		= "catid = '".$catid."' AND data_city ='".$this->data_city."'";
							}elseif($campID == '14'){
								$upField	= "hotkey_banner 				 = '".$bidderString."',
											   hotkey_banner_inventory		 = '".$inventory."',
											   hotkey_banner_actual_inventory= '".$actualInv."',
											   updated_on					 = now()";
								$table	 	= "tbl_hotkey_banner_bid";
								$where		= "vertical_id = '".$catid."' AND data_city ='".$this->data_city."' AND type_flag='".$type_flag."'";
							}
							$sqlUp			= "UPDATE ".$table." SET ".$upField." WHERE ".$where; 	
							$qryUp			= $this->conn_fnc->query_sql($sqlUp);
							$this->insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Query == >".$sqlUp." Result =>".$qryUp);
						}
						$this->getCategoryDetails($catid,'2');
					}
				}
				else
				{ /* New entry for the catid */
					unset($bidder_array);
					if($askedInventory<=1 && $askedInventory>0){	
						if($status	== 2){
							$bidderString = $this->parentid."-".$askedInventory."-".$askedInventory;
						}elseif($status	== 1){
							$bidderString = $this->parentid."-".$askedInventory."-0";
						}
						if($campID == '13'){
							$insField ="catid							= '".$catid."',
										national_catid					= '".$national_catid."',
										cat_sponbanner_bidder 			= '".$bidderString."',
										cat_sponbanner_inventory		= '".$askedInventory."',";
							if($status	== 2){
								$insField.= "cat_sponbanner_actual_inventory	= '".$askedInventory."',";
							}else{
								$insField.= "cat_sponbanner_actual_inventory	= 0,";
							}
							$insField.="data_city						= '".$this->data_city."',
										updated_on						= now()";
							$table	 	= "tbl_cat_banner_bid";
						}else if($campID == '15'){
							$insField ="catid							= '".$catid."',
										national_catid					= '".$national_catid."',
										cat_textbanner_bidder 			= '".$bidderString."',";
							if($status	== 2){
								$insField.= "cat_sponbanner_actual_inventory	= '".$askedInventory."',";
							}else{
								$insField.= "cat_sponbanner_actual_inventory	= 0,";
							}
							$insField.="cat_textbanner_actual_inventory	= 0,
										data_city						= '".$this->data_city."',
										updated_on						= now()";
							$table	 	= "tbl_cat_banner_bid";
						}else if($campID == '14'){
							
							$insField ="vertical_id						= '".$catid."',
										hotkey_banner					= '".$bidderString."',
										hotkey_banner_inventory			= '".$askedInventory."',";
							if($status	== 2){
								$insField.= "hotkey_banner_actual_inventory	= '".$askedInventory."',";
							}else{
								$insField.= "hotkey_banner_actual_inventory	= 0,";
							}			
							$insField.="type_flag						= '".$type_flag."',
										data_city						= '".$this->data_city."',
										updated_on						= now()";
							$table	  = "tbl_hotkey_banner_bid";
						}
						$upSql	= "INSERT INTO ".$table." SET ".$insField;
						$upQry	= $this->conn_fnc->query_sql($upSql);
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Query == >".$upSql." Result=>".$upQry);
						$this->getCategoryDetails($catid,'2');
					}
				}
			}
			else
			{
				if($status == 1){
					echo '<h1>FATAL ERROR [CATSPONINV001]: Wrong Campaign Type.'.$this -> parentid .'</h1>';
					echo '<h2>Campaign Type is either not set or wrong</h2>';
					echo '<h3> Click <a href="../business/category_filter_banner.php">here</a> to fix it.</h3>';
					die;
				}else{
					$msg	= "Invalid campaignid. Booking exited";
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,$msg);
				}
			}
		}
	
		function GetTitles($word,$typeFlag,$data_source){/*function to all the titles */
			$sql_title= "SELECT vertical_displayname,vertical_id FROM tbl_verticalid_generator WHERE vertical_displayname LIKE '".trim($word)."%' AND data_city ='".$this->data_city."' AND type_flag='".$typeFlag."' AND data_source='".$data_source."' AND active_flag=1 ORDER BY vertical_displayname";
			$res_title = $conn_server ->query_sql($sql_title);
			if($res_title && mysql_num_rows($res_title))
			{
				while($row_title = mysql_fetch_assoc($res_title))
				{
					$categories[$row_title['vertical_id']][$row_title['vertical_displayname']]['budget'] = $this -> getTitleBannerBudget($row_title['vertical_id'],$typeFlag);
				}
			}
			
			return $categories;
			
		}
		
		function getTitleBannerBudget($banner_id,$b2b,$inv_take){
			if(defined("REMOTE_CITY_MODULE"))
			{
				$all_main_cities=array("mumbai","delhi","hyderabad","kolkata","bangalore","chennai","pune","ahmedabad","jaipur","chandigarh","coimbatore");
				if(in_array(strtolower($this -> data_city), $all_main_cities)){
					$cityname = $this -> data_city;
				}else{
					$cityname = 'other_city';
				}
				$sql_banner_price = "SELECT * FROM tbl_category_banner_price where data_city = '".$cityname."'";
			}else{
				$sql_banner_price = "SELECT * FROM tbl_category_banner_price where data_city = '".$this ->data_city."'";
			}
			$res_banner_price = $this->conn_local->query_sql($sql_banner_price);
			if($res_banner_price && mysql_num_rows($res_banner_price))
			{
				$row_banner_price = mysql_fetch_assoc($res_banner_price);
				if($b2b)
				{
					$totalBudget = $row_banner_price['b2b_price'];
				}
				else
				{
					$totalBudget = $row_banner_price['non_b2b_price'];
				}
				
                $free = $this -> getAvailableInventory($banner_id,$inv_take,$b2b);
				
                $totalBudget = $totalBudget * $free['inv_avail'];
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Vertical Id ".$banner_id." budget = ".$totalBudget);
				return $totalBudget;
			}	
		}

        function getActualTitleBannerBudget($banner_id,$b2b){
			if(defined("REMOTE_CITY_MODULE"))
			{
				$all_main_cities=array("mumbai","delhi","hyderabad","kolkata","bangalore","chennai","pune","ahmedabad","jaipur","chandigarh","coimbatore");
				if(in_array(strtolower($this -> data_city), $all_main_cities)){
					$cityname = $this -> data_city;
				}else{
					$cityname = 'other_city';
				}
				$sql_banner_price = "SELECT b2b_price,non_b2b_price FROM tbl_category_banner_price WHERE data_city = '".$cityname."'";
			}else{
				$sql_banner_price = "SELECT b2b_price,non_b2b_price FROM tbl_category_banner_price WHERE data_city = '".$this ->data_city."'";
			}
			$res_banner_price = $this->conn_local->query_sql($sql_banner_price);
			if($res_banner_price && mysql_num_rows($res_banner_price))
			{
				$row_banner_price = mysql_fetch_assoc($res_banner_price);
				if($b2b)
				{
					$totalBudget = $row_banner_price['b2b_price'];
				}
				else
				{
					$totalBudget = $row_banner_price['non_b2b_price'];
				}
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Actual budget for Vertical Id ".$banner_id." = ".$totalBudget);
				return $totalBudget;
			}	
		}
	
		function GetFreeCategories($word,$banner_id,$request){
			
            if($request['inventory']!='')
            {
                $inv = $request['inventory'];
            }
            else
            {
                $inv = 100;
            }
			if($this->campType == '13' || $this->campType == '15'){
				
				//$word = preg_replace('/\s+/', ' ', $word);
				//$word = str_replace("."," ",$word);
				
				$word_arr = explode(" ",$word);
				$word_num=count($word_arr);
				
				/*if (!defined('REMOTE_CITY_MODULE'))
				{
					//$condition = " AND company_count>0 ";
				}
				$sql1='SELECT catid,catname,parent_flag,final_catname,cat_type,mask,parentid,parent_callcnt,callcnt,
							   GROUP_CONCAT(catlineage SEPARATOR "|P|") as P_lineage,
							   ((match(catname_stem) against("'.$word.'"))*0.25+callcnt/100*0.75) as score, 
							   ((match(catname_stem) against("'.$word.'" IN BOOLEAN MODE))) as score2,if(catname="'.$request['text_content'].'",1,0) as exact_match
							   FROM tbl_category_master 
							   WHERE 
								match(catname_stem) against("'.$word.'") AND
								catname NOT LIKE "c2s%"  and catname NOT LIKE "c2c%" AND
								(paid>0 OR nonpaid>0) AND 
								((display_flag=1 AND 
								mask=0) ) AND
								(cat_type="B" OR cat_type="BT" OR (cat_type="BT" AND parent_flag=1)) AND
								deleted = 0 '.$condition.'
								GROUP BY catid';
				$sql2=' HAVING score2='.$word_num;
				$sql3=' ORDER BY exact_match desc,score2 desc, score DESC 
				   LIMIT 52';
				$query=$sql1.$sql2." ".$sql3;
				$res=$this->conn_local->query_sql($query);
				//if($_SERVER['REMOTE_ADDR']""){echo "<br>category_banner.php".$query;}
				if($res && mysql_num_rows($res)==0)
				{
					$query=$sql1." ".$sql3;
                    $res=$this->conn_local->query_sql($query);
				}
			
				unset($categories);
				
				if($res && mysql_num_rows($res))
				{
					
					while($row=mysql_fetch_assoc($res))
					{
						if($row['cat_type']=='B' || $row['cat_type']=='BT' || $row['parent_flag']=='0')
						{
							$categories[$row['catid']]['cat_name'] = $row['catname'];
							
							$categories[$row['catid']]['platinum_budget']= $this -> getCategoryBannerBudget($row['catid'],$banner_id,$inv);
						}
					 }	
				
					return $categories;
				}*/
				unset($categories); 
				$categories = $this->categoryMaster ->getCatSponFreeCat($word,$banner_id,$request,$word_num);
				foreach ($categories as $catid => $catval){
					$categories[$catid]['platinum_budget']= $this -> getCategoryBannerBudget($catid,$banner_id,$inv);
				}
				//echo "<br>in class file--<pre>";print_r($categories);echo "</pre>";
				return $categories;
			}
			else
			{
				$word = str_replace("(B2B & B2C)","",$word);
				$word = str_replace("(B2B)","",$word);
				$word = str_replace("(B2C)","",$word);
				$sql_title="SELECT vertical_id, 
							IF(type_flag=1,CONCAT(vertical_name, ' ','(B2B)'),'') AS b2b_title,
							IF(type_flag=0,CONCAT(vertical_name, ' ','(B2C)'),'') AS b2c_title
							FROM tbl_verticalid_generator WHERE vertical_name LIKE '".trim($word)."%' AND type_flag = '".$request['typeFlg']."' AND  data_source='".$request['dataSrc']."' and active_flag=1";
				$res_title = $this->conn_server->query_sql($sql_title);
				if($res_title && mysql_num_rows($res_title))
				{
					while($row_title = mysql_fetch_assoc($res_title))
					{
						if(trim($row_title['b2b_title'])!='')
                        {
							//$categories[$row_title['vertical_id']][$row_title['b2b_title']]['budget'] = $this -> getTitleBannerBudget($row_title['vertical_id'],$type);
                            $categories[$row_title['vertical_id']][$row_title['b2b_title']]['budget'] = $this -> getTitleBannerBudget($row_title['vertical_id'],$request['typeFlg'],$inv);
                        }
						elseif(trim($row_title['b2c_title'])!='')
                        {
							//$categories[$row_title['vertical_id']][$row_title['b2c_title']]['budget'] = $this -> getTitleBannerBudget($row_title['vertical_id'],$type,$inv);
                            $categories[$row_title['vertical_id']][$row_title['b2c_title']]['budget'] = $this -> getTitleBannerBudget($row_title['vertical_id'],$request['typeFlg'],$inv);
                        }
					}
				}
				return $categories;
			}
		}
		
		function getCategoryBannerBudget($catId,$category_banner_id,$inv_take){		
			if($category_banner_id != 2)
			{
				$multiPlier	= self::BUDGET_MUL;
				$tenure 	= 365;
				if($category_banner_id == 1)
				{	
					$catSpon= self::SPON_VALUE;
				}
				elseif($category_banner_id == 3)
				{
					$catSpon= self::TEXT_VALUE;
				}
				
				$platDiaPincode = array();
				$pincodeArr = array();
				$platDiapresent = false;
				
				$qryGetTotalPincode = "SELECT DISTINCT(pincode) as pincode FROM tbl_area_master WHERE data_city = '".$this ->data_city."' AND type_flag=1 AND display_flag=1 AND deleted=0 order by pincode";
				$resGetTotalPincode 	= $this->conn_local->query_sql($qryGetTotalPincode);
				if($resGetTotalPincode && mysql_num_rows($resGetTotalPincode)>0){
					while($rowGetTotalPincode = mysql_fetch_assoc($resGetTotalPincode)){
						$pincodeArr[] = $rowGetTotalPincode['pincode'];
					}
				}
				
				/*get b2b, nonb2b callcount*/
				$qryGetcallcount = "select catid,business_flag,callcount from tbl_categorymaster_generalinfo where catid ='".$catId."'";
				$resGetcallcount = $this->conn_local->query_sql($qryGetcallcount);
				if($resGetcallcount && mysql_num_rows($resGetcallcount)>0){
					$rowGetcallcount = mysql_fetch_assoc($resGetcallcount);
					$b2bFlag = ($rowGetcallcount['business_flag'] == '1') ? '1' : '0';
					$catCount = $rowGetcallcount['callcount'];
				}
				
				/* Call count grouth rate */
				$sqlCallPer 	= "SELECT callcnt_per,addon_premium FROM tbl_business_uploadrates WHERE city = '".$this ->data_city ."'";
				$resultCallPer 	= $this->conn_local->query_sql($sqlCallPer);
				$rowCallPer 	= mysql_fetch_assoc($resultCallPer);
				$callPer 		= ($rowCallPer['callcnt_per'] / 100 + 1)*($rowCallPer['addon_premium'] / 100 + 1);
				
				/* Selecting call count and bid value  */
                if(defined("REMOTE_CITY_MODULE")){
                   $qryArea 	= "SELECT pincode,catid, callcnt, platinum_value FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '". $catId."' AND data_city='".$this ->data_city."'";	
                }
                else{
                    $qryArea 	= "SELECT pincode,catid, callcnt, platinum_value FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '". $catId."'";	
                }
				$resArea 	= $this -> conn_fnc->query_sql($qryArea);
				$numRows = mysql_num_rows($resArea);
				
				if($numRows > 0)
				{
					$platDiapresent = true;
					while($rowArea = mysql_fetch_assoc($resArea))
					{	
						$platDiaPincode[$rowArea['pincode']]['platinum_value'] = $rowArea['platinum_value'];
						$platDiaPincode[$rowArea['pincode']]['callcnt'] = $rowArea['callcnt'];
						/* If call count not found or call count is zero then assuming 1 call count for all pincodes  */
						/*if($rowArea['callcnt'] == 0)
							$callCnt	= (1/$numRows)/365;
						else
							$callCnt	= $rowArea['callcnt']/365;*/
							
						/* If Bid value is  not found or bid value is less than 5 then taking minimum bid value as 5 for all pincodes  */
						
						/*$bidValue = ($b2bFlag) ? (($catCount>100)? max($rowArea['platinum_value'],self :: NONB2B_MIN_BID_VAL):max($rowArea['platinum_value'],self :: D_B2B_MIN_BID_VAL)) : max($rowArea['platinum_value'],self :: NONB2B_MIN_BID_VAL);*/
						/*echo "<br>pincode=>".$rowArea['pincode']."==".$bidValue."*".$callCnt."*".$multiPlier ."*". $callPer ."*". $tenure ."*".$catSpon;
						echo "<br>pincode=>".$rowArea['pincode']."==".($bidValue*$callCnt*$multiPlier * $callPer * $tenure *$catSpon*self::PLAT_INCREMENT_FACTOR);*/
						
						/*if($rowArea['platinum_value'] == 0)
							$bidValue =  5;
						else
							$bidValue = max($rowArea['platinum_value'],5);*/
						
						/*$budgetPlat = $callCnt * $bidValue;
						$totbudgetPlat += $budgetPlat ;*/
					}
				}
				else
				{		
					/* If no recodes found in bid tables then we will assign with defaults value for all pincodes  */
					$qryArea 	= "SELECT DISTINCT(pincode) FROM tbl_area_master WHERE data_city = '".$this ->data_city."' AND type_flag=1 AND display_flag=1 AND deleted=0";	
					$resArea 	= $this->conn_local->query_sql($qryArea);
					$numRows = mysql_num_rows($resArea);
				
					if($numRows > 0)
					{
						$callCnt	= (1/$numRows)/365 ;
					}
					else
					{
						/*echo "No areas found!";
						die;*/
						$callCnt	= 1/365; /*as per discuss with Sk sir and raj (if no single pincode is found im particular area pass 1 for single pincode)*/
					}
						
					$bidValue =  5;
					$budgetPlat = $callCnt * $bidValue;
					$totbudgetPlat += $budgetPlat ;
				}
				/* Calculating budget  */
				if(count($pincodeArr)>0){
					$callCnt ='';
					$bidValue ='';
					foreach($pincodeArr as $pincodekey => $pincodevalue){
						if($pincodevalue!='' && $pincodevalue>0){
							if (array_key_exists($pincodevalue, $platDiaPincode)) {
								if($platDiaPincode[$pincodevalue]['callcnt'] == 0)
									$callCnt	= 1/count($pincodeArr)/365;
								else
									$callCnt	= $platDiaPincode[$pincodevalue]['callcnt']/365;
							}else{
								$callCnt	= 1/count($pincodeArr)/365;
							}
							$bidValue = ($b2bFlag) ? (($catCount>100)? max($platDiaPincode[$pincodevalue]['platinum_value'],self :: NONB2B_MIN_BID_VAL):max($platDiaPincode[$pincodevalue]['platinum_value'],self :: D_B2B_MIN_BID_VAL)) : max($platDiaPincode[$pincodevalue]['platinum_value'],self :: NONB2B_MIN_BID_VAL);
							
							//echo "<br>pincode=>".$pincodevalue."==".$bidValue."*".$callCnt."*".$multiPlier ."*". $callPer ."*". $tenure ."*".$catSpon."*".self::PLAT_INCREMENT_FACTOR;
							//echo "<br>pincode=>".$pincodevalue."==".($bidValue*$callCnt*$multiPlier * $callPer * $tenure *$catSpon*self::PLAT_INCREMENT_FACTOR);
							
							$budgetPlat = $callCnt * $bidValue;
							$totbudgetPlat += $budgetPlat ;
						}
					}
				}
				//echo "<br>line 740-->".$multiPlier." *". $callPer." *". $tenure." *".$catSpon ."*".self::PLAT_INCREMENT_FACTOR;echo "<br>". $totbudgetPlat;	
				
				$budgetFactor 	= $multiPlier * $callPer * $tenure *$catSpon *self::PLAT_INCREMENT_FACTOR;//* self::PLAT_INCREMENT_FACTOR;	
				$totalBudget 	= $totbudgetPlat * $budgetFactor;
                $free = $this -> getAvailableInventory($catId,$inv_take); 
                $totalBudget = $totalBudget * $free['inv_avail'];
			}
            
			return ceil($totalBudget);
		}
		
        function getActualCategoryBannerBudget($catId,$category_banner_id){		
			if($category_banner_id != 2)
			{
				$multiPlier	= self::BUDGET_MUL;
				$tenure 	= 365;
				if($category_banner_id == 1)
				{	
					$catSpon= self::SPON_VALUE;
				}
				elseif($category_banner_id == 3)
				{
					$catSpon= self::TEXT_VALUE;
				}
				
				$platDiaPincode = array();
				$pincodeArr = array();
				$platDiapresent = false;
				
				$qryGetTotalPincode = "SELECT DISTINCT(pincode) as pincode FROM tbl_area_master WHERE data_city = '".$this ->data_city."' AND type_flag=1 AND display_flag=1 AND deleted=0 order by pincode";
				$resGetTotalPincode 	= $this->conn_local->query_sql($qryGetTotalPincode);
				if($resGetTotalPincode && mysql_num_rows($resGetTotalPincode)>0){
					while($rowGetTotalPincode = mysql_fetch_assoc($resGetTotalPincode)){
						$pincodeArr[] = $rowGetTotalPincode['pincode'];
					}
				}
				
				/*get b2b, nonb2b callcount*/
				$qryGetcallcount = "select catid,business_flag,callcount from tbl_categorymaster_generalinfo where catid ='".$catId."'";
				$resGetcallcount = $this->conn_local->query_sql($qryGetcallcount);
				if($resGetcallcount && mysql_num_rows($resGetcallcount)>0){
					$rowGetcallcount = mysql_fetch_assoc($resGetcallcount);
					$b2bFlag = ($rowGetcallcount['business_flag'] == '1') ? '1' : '0';
					$catCount = $rowGetcallcount['callcount'];
				}
				
				/* Call count grouth rate */
				$sqlCallPer 	= "SELECT callcnt_per,addon_premium FROM tbl_business_uploadrates WHERE city = '".$this ->data_city ."'";
				$resultCallPer 	= $this->conn_local->query_sql($sqlCallPer);
				$rowCallPer 	= mysql_fetch_assoc($resultCallPer);
				$callPer 		= ($rowCallPer['callcnt_per'] / 100 + 1)*($rowCallPer['addon_premium'] / 100 + 1);
				
				/* Selecting call count and bid value  */
                if(defined("REMOTE_CITY_MODULE")){
                    $qryArea 	= "SELECT pincode,catid, callcnt, platinum_value FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '". $catId."' AND data_city='".$this ->data_city."'";	
                }
                else{
                    $qryArea 	= "SELECT pincode,catid, callcnt, platinum_value FROM tbl_platinum_diamond_pincodewise_bid WHERE catid = '". $catId."'";	
                }
				$resArea 	= $this -> conn_fnc->query_sql($qryArea);
				$numRows = mysql_num_rows($resArea);
				
				if($numRows > 0)
				{
					$platDiapresent = true;
					while($rowArea = mysql_fetch_assoc($resArea))
					{	
						$platDiaPincode[$rowArea['pincode']]['platinum_value'] = $rowArea['platinum_value'];
						$platDiaPincode[$rowArea['pincode']]['callcnt'] = $rowArea['callcnt'];
						
						/*$platDiaPincode[$catId]['pincode'] = $rowArea['pincode'];
						$platDiaPincode[$catId]['platinum_value'] = $rowArea['platinum_value'];
						$platDiaPincode[$catId]['callcnt'] = $rowArea['callcnt'];*/
						/* If call count not found or call count is zero then assuming 1 call count for all pincodes  */
						/*if($rowArea['callcnt'] == 0)
							$callCnt	= 1/$numRows;
						else
							$callCnt	= $rowArea['callcnt']/365;*/
							
						/* If Bid value is  not found or bid value is less than 5 then taking minimum bid value as 5 for all pincodes  */
						/*if($rowArea['platinum_value'] == 0)
							$bidValue =  5;
						else
							$bidValue = max($rowArea['platinum_value'],5);*/
							
						/*$bidValue = ($b2bFlag) ? (($catCount>100)? max($rowArea['platinum_value'],self :: NONB2B_MIN_BID_VAL):max($rowArea['platinum_value'],self :: D_B2B_MIN_BID_VAL)) : max($rowArea['platinum_value'],self :: NONB2B_MIN_BID_VAL);
						echo "<br>pincode=>".$rowArea['pincode']."==".$bidValue."*".$callCnt."*".$multiPlier ."*". $callPer ."*". $tenure ."*".$catSpon."*".self::PLAT_INCREMENT_FACTOR;
						echo "<br>pincode=>".$rowArea['pincode']."==".($bidValue*$callCnt*$multiPlier * $callPer * $tenure *$catSpon*self::PLAT_INCREMENT_FACTOR);
						
						$budgetPlat = $callCnt * $bidValue;
						$totbudgetPlat += $budgetPlat ;*/
					}
				}
				else
				{		
					/* If no recodes found in bid tables then we will assign with defaults value for all pincodes  */
					$qryArea 	= "SELECT DISTINCT(pincode) FROM tbl_area_master WHERE data_city = '".$this ->data_city."' AND type_flag=1 AND display_flag=1 AND deleted=0 order by pincode";	
					$resArea 	= $this->conn_local->query_sql($qryArea);
					$numRows = mysql_num_rows($resArea);
				
					if($numRows > 0)
					{
						$callCnt	= 1/$numRows/365 ;
					}
					else
					{
						/*echo "No areas found!";
						die;*/
						$callCnt	= 1; /*as per discuss with Sk sir and raj (if no single pincode is found im particular area pass 1 for single pincode)*/
					}
						
					$bidValue =  5;
					$budgetPlat = $callCnt * $bidValue;
					$totbudgetPlat += $budgetPlat ;
				}
				
				if(count($pincodeArr)>0){
					$callCnt ='';
					$bidValue ='';
					foreach($pincodeArr as $pincodekey => $pincodevalue){
						if($pincodevalue!='' && $pincodevalue>0){
							if (array_key_exists($pincodevalue, $platDiaPincode)) {
								if($platDiaPincode[$pincodevalue]['callcnt'] == 0)
									$callCnt	= 1/count($pincodeArr)/365;
								else
									$callCnt	= $platDiaPincode[$pincodevalue]['callcnt']/365;
							}else{
								$callCnt	= 1/count($pincodeArr)/365;
							}
							$bidValue = ($b2bFlag) ? (($catCount>100)? max($platDiaPincode[$pincodevalue]['platinum_value'],self :: NONB2B_MIN_BID_VAL):max($platDiaPincode[$pincodevalue]['platinum_value'],self :: D_B2B_MIN_BID_VAL)) : max($platDiaPincode[$pincodevalue]['platinum_value'],self :: NONB2B_MIN_BID_VAL);
							
							//echo "<br>pincode=>".$pincodevalue."==".$bidValue."*".$callCnt."*".$multiPlier ."*". $callPer ."*". $tenure ."*".$catSpon."*".self::PLAT_INCREMENT_FACTOR;
							//echo "<br>pincode=>".$pincodevalue."==".($bidValue*$callCnt*$multiPlier * $callPer * $tenure *$catSpon*self::PLAT_INCREMENT_FACTOR);
							
							$budgetPlat = $callCnt * $bidValue;
							$totbudgetPlat += $budgetPlat ;
						}
					}
				}
				
				/* Calculating budget  */
				//echo "<br>".$multiPlier." *". $callPer." *". $tenure." *".$catSpon;echo "<br>". $totbudgetPlat;	
				
				$budgetFactor 	= $multiPlier * $callPer * $tenure *$catSpon*self::PLAT_INCREMENT_FACTOR;	
				$totalBudget 	= $totbudgetPlat * $budgetFactor;
			}
            $this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Actual budget for catid ".$catId." = ".$totalBudget);
			return ceil($totalBudget);
		}

		function isSmartPackage($financeObj,$curlFlag=0){
			$powerlisting = $financeObj->checkIsPackageContract(1);
			$errFlg 	  = 0;
			if($powerlisting==3)
			{
				$errFlg	= 1;
				if($curlFlag == 1){
					$msg = "<br><div align='center'><font color='red' size='5'>This contract is already having a smart package in it.</font></div><br>";
					return '1|^|^|^|'.$msg;
				}else{
					echo "<br><div align='center'><font color='red' size='5'>This contract is already having a smart package in it.</font></div><br>";
					die;
				}
			}else{
				if($curlFlag == 1)
					return '0|^|^|^|';
			}
		}
	
		function updateBudget($banner_id,$banner_name,$inv_take){
			$catid_title_field = ($banner_id == 2)?'title_name':'cat_name';
            $catid_titleid_field = ($banner_id == 2)?'title_id':'catid';
			$temp_table 	   = ($banner_id == 2)?'tbl_catfilter_temp':'tbl_catspon_temp'; 

			$sel_sql = "SELECT ".$catid_title_field.",".$catid_titleid_field." FROM ".$temp_table." WHERE parentid='".$this->parentid."' AND campaign_type='".$banner_id."' AND campaign_name='".$banner_name."'";
			$sel_res = $this->conn_temp->query_sql($sel_sql,$this->module,$temp_flg=1);
			if($sel_res && mysql_num_rows($sel_res))
			{
				$budgetsum=0;
				while($sel_row = mysql_fetch_assoc($sel_res))
				{
					
					$b2b_flag = ($banner_id == 2)?((stristr($sel_row[$catid_title_field],'B2B')) ? 1 : 0):0;			   
					//$budget   = ($banner_id == 2)?$this->getTitleBannerBudget($b2b_flag,$conn):$this->getCategoryBannerBudget($sel_row[$catid_title_field],$banner_id,$conn);
					$budget   = ($banner_id == 2)?$this->getTitleBannerBudget($sel_row[$catid_titleid_field],$b2b_flag,$inv_take):$this->getCategoryBannerBudget($sel_row[$catid_titleid_field],$banner_id,$inv_take);
					if($this->module != 'cs'){
						$budget = 0;
					}
					$upt_sql = "UPDATE ".$temp_table." SET budget='".$budget."',iscalculated='1' WHERE parentid='".$this->parentid."' AND ".$catid_title_field."='".$sel_row[$catid_title_field]."' AND campaign_type='".$banner_id."' and campaign_name='".$banner_name."'";
					$upt_res = $this->conn_temp->query_sql($upt_sql,$this->parentid);
									
					$budgetsum +=$budget; 
				}
			}
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Budget Updated for campaignid ".$this->campType." = ".$budgetsum);
		}

        function getActTotalBudget($banner_id,$banner_name){
			$catid_title_field = ($banner_id == 2)?'title_name':'catid';
			$temp_table 	   = ($banner_id == 2)?'tbl_catfilter_temp':'tbl_catspon_temp'; 

			$sel_sql = "SELECT ".$catid_title_field." FROM ".$temp_table." WHERE parentid='".$this->parentid."' AND campaign_type='".$banner_id."' and campaign_name='".$banner_name."'";
			$sel_res = $this->conn_temp->query_sql($sel_sql,$this->module,$temp_flg=1);
			if($sel_res && mysql_num_rows($sel_res))
			{
				$budgetsum=0;
				while($sel_row = mysql_fetch_assoc($sel_res))
				{
					
					$b2b_flag = ($banner_id == 2)?((stristr($sel_row[$catid_title_field],'B2B')) ? 1 : 0):0;			   
					//$budget   = ($banner_id == 2)?$this->getTitleBannerBudget($b2b_flag,$conn):$this->getCategoryBannerBudget($sel_row[$catid_title_field],$banner_id,$conn);
                    $budget   = ($banner_id == 2)?$this->getActualTitleBannerBudget($banner_id,$b2b_flag):$this->getActualCategoryBannerBudget($sel_row[$catid_title_field],$banner_id);
					$budgetsum +=$budget; 
				}
			}
            return $budgetsum;
		} 
		
		function adjustBudget($removed_cat_arr,$category_banner_id,$category_banner_name,$financeObj){
			
			if(count($removed_cat_arr))
			{
				$cat_title_field = ($category_banner_id == 2)?'title_name':'cat_name';
				$temp_table      = ($category_banner_id == 2)?'tbl_catfilter_temp':'tbl_catspon_temp';
				$main_table      = ($category_banner_id == 2)?'tbl_catfilter':'tbl_catspon';
				$category_names = implode("','",$removed_cat_arr);
				$sql_sum = "SELECT SUM(variable_budget) as budget FROM ".$temp_table." where parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
				
				$res_sum = $this->conn_temp->query_sql($sql_sum);
				if($res_sum && mysql_num_rows($res_sum))
				{
					$row_sum = mysql_fetch_assoc($res_sum);
				}
				
				$sql_main_count = "SELECT COUNT(*) as main_count FROM ".$main_table." WHERE parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
				$res_main_count = $this->conn_local->query_sql($sql_main_count);
				if($res_main_count && mysql_num_rows($res_main_count))
				{
					$row_main_count = mysql_fetch_assoc($res_main_count);
				}
				
				if($this->campType =='13' || $this->campType =='15')
					$field ='catid AS catid';
				elseif($this->campType =='14'){
					$field ='title_id AS catid';
				}
				$sqlGetCatid = "SELECT ".$field." FROM ".$temp_table." WHERE parentid ='".$this->parentid."' AND ".$cat_title_field." IN ('".$category_names."') AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
				$qryGetCatid = $this->conn_temp->query_sql($sqlGetCatid);
				if($qryGetCatid){
					while($rowGetCatid = mysql_fetch_assoc($qryGetCatid)){
						$catIDs[] = $rowGetCatid['catid'];
					}
				}
				$sql_del="DELETE FROM ".$temp_table." WHERE  parentid='".$this->parentid."' AND ".$cat_title_field." IN ('".$category_names."') AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
				//echo"<br>". $sql_del;
				$res_del = $this->conn_temp->query_sql($sql_del,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete Query=>".$sql_del);
				if($res_del && $row_main_count['main_count']>0 && $row_sum['budget']>0)
				{
					
					$sql_budget = "SELECT SUM(variable_budget) AS budget FROM ".$temp_table." WHERE parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
					//echo"<br>". $sql_budget;
					$res_budget = $this->conn_temp->query_sql($sql_budget);
					$row_budget = mysql_fetch_assoc($res_budget);
					if($row_budget['budget']>0)
					{
						$sql_upt = "UPDATE ".$temp_table." SET variable_budget = variable_budget*(".$row_sum['budget']/$row_budget['budget'].") WHERE parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
						$res_upt = $this->conn_temp->query_sql($sql_upt,$this->parentid);
						//echo"<br>". $sql_upt;
						// here we are contributing budget into other so no need to do update on finance_temp				
						//exit;
					}				
					
				}
				// if all category has been deleted then we have to make budget=0 for company_master_finance_temp 
				
				$campaignid = $this->campType;
				$finArr = $financeObj->getFinanceMainData($campaignid);
				$editmode = $finArr[$campaignid]['balance']>0?1:0;
				$fieldname="";
				switch($campaignid)
				{
					case 13: $fieldname ="catspon"; break;
					case 14: $fieldname ="catfilter"; break;
					case 15: $fieldname ="cattext"; break;
				}
				
				
				if(!$editmode)
				{
					$iscalculatedquiery = " UPDATE  ".$temp_table."  set iscalculated=0 where parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."' ";
					$updateQry = $this->conn_temp->query_sql($iscalculatedquiery,$this->parentid);
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete Update  Query=>".$iscalculatedquiery." Result=>".$updateQry);
					$tempflowquiery = " INSERT INTO tbl_temp_flow_status set parentid='".$this->parentid."', ".$fieldname."=".$campaignid."
							ON DUPLICATE KEY UPDATE ".$fieldname."=".$campaignid;
					$InsertQry = $this->conn_temp ->query_sql($tempflowquiery,$this->parentid);
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Update  Query=>".$iscalculatedquiery." Result=>".$InsertQry);
				}
							
				//echo"<br>editmode". $editmode; exit;
				$sql_sel="SELECT parentid FROM ".$temp_table." WHERE  parentid='".$this->parentid."' AND campaign_type='".$category_banner_id."'";
				$res_sel_rs = $this->conn_temp->query_sql($sql_sel,$module,$temp_flg=1);
				
							
				//if($res_sel_rs && mysql_num_rows($res_sel_rs)==0 && !$editmode)
				if($res_sel_rs && mysql_num_rows($res_sel_rs)==0)
				{	// this is new contract for catspon and remove all categories			
					$camp_data = array("budget"=>0,"recalculate_flag"=>0);
					$financeObj->financeInsertUpdateTemp($this->campType,$camp_data);
					
					$tempflowquiery = " INSERT INTO tbl_temp_flow_status set parentid='".$this->parentid."', ".$fieldname."=0
							ON DUPLICATE KEY UPDATE ".$fieldname."=0";
					$this->conn_temp->query_sql($tempflowquiery,$this->parentid);
					
				}
			}
			return 1;
		}
	
		function showSelected($conn,$field,$table,$camp_type=''){/* Function for showing the category selected */
			$retArr = array();
			if(count($field) && trim($table)!=''){
				foreach($field as $value){
					if(substr_count(strtolower($value)," as ")){
						$valArr = explode(" as ",strtolower($value));
						$value  = $valArr['1'];
					}
					$sendArr[] = trim($value," DISTINCT ");
				}
				$fieldStr = implode(',',$field);
                if($camp_type!='')
                {
                    if($camp_type == '13'){
                        $where =" AND campaign_type = 1";
                    }else if($camp_type == '14'){
                        $where =" AND campaign_type = 2";
                    }else if($camp_type == '15'){
                        $where =" AND campaign_type = 3";
                    }
                }
                else
                {
                    if(strstr($table, 'filter'))
                    {
                        $this->campType = 14;
                    }
                    else
                    {
                        $this->campType = 13;
                    }
                    if($this->campType == '13'){
                        $where =" AND campaign_type = 1";
                    }else if($this->campType == '14'){
                        $where =" AND campaign_type = 2";
                    }else if($this->campType == '15'){
                        $where =" AND campaign_type = 3";
                    }
                }
				$sql = "SELECT ".$fieldStr." FROM ".$table." WHERE parentid ='".$this->parentid."'".$where;
				$qry = $conn->query_sql($sql);
				if($qry){
					$i = 0;
					while($row = mysql_fetch_assoc($qry)){
						foreach($sendArr as $value)
							$retArr[$i][$value] = $row[$value];
						$i++;
					}
				}
			}
			return $retArr;	
		}

		function budgetCalculation($category_banner_id,$temp_table,$main_table,$catid_title_field,$category_banner_name,$financeobj,$request){/* Function for campaign submission*/
			$tenure = $request['tenure'];
			$count	= $request['count'];
            switch($category_banner_id)
            {
                case 1: $inventory_table = "tbl_cat_banner_bid";
                        break;
                case 2: $inventory_table = "tbl_hotkey_banner_bid";
                        break;
                case 3: $inventory_table = "tbl_cat_banner_bid";
                        break;
            }
			
			/*****************************************************   Calculation for call connect    *************************************************/
			$sum=($request['sum']/$tenure);								// per day budget according tenure
			$call_connect=$request['callconnect']/365;						// per day call connect fee

			$cost_call_connect=$sum+$call_connect;							// toatal per day 

			$call_connect_bud=($call_connect*$tenure)/$count;				// call connect fee for each category
			
			$remote_city_arry = array('chandigarh','jaipur','coimbatore');
			/*********************************************************************************************************************/

			$sql_temp="SELECT SUM(variable_budget) AS variable_budget,SUM(budget) AS budget FROM ".$temp_table." WHERE parentid ='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
			$res_temp=$this->conn_temp->query_sql($sql_temp);
			$row_temp=mysql_fetch_assoc($res_temp);
			
			$sql_main="SELECT SUM(variable_budget) AS variable_budget,SUM(budget) AS budget FROM ".$main_table." WHERE parentid ='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
			if(strtolower($this->module) == 'cs'){
				$res_main=$this->conn_local->query_sql($sql_main);
			}else{
				$res_main=$this->conn_main->query_sql($sql_main);
			}
			$res_main=$this->conn_local->query_sql($sql_main);
			$row_main=mysql_fetch_assoc($res_main);

			$select_bid="SELECT budget,".$catid_title_field.",variable_budget,iscalculated FROM ".$temp_table." WHERE parentid ='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'"; 
			$bid_res=$this->conn_temp->query_sql($select_bid);
			
			$j=0;

			while($row_bid=mysql_fetch_assoc($bid_res))
			{
				if($row_bid['iscalculated'] && $request['calculate_attribute'] == 1)
				{
					$row_bid['budget']=(($row_bid['budget']/365) * $tenure)+$call_connect_bud; // adding call connect fee in budget
					if($category_banner_id == 1)			
					{
						switch($request['tenure']){
							case '90': 
									$ten_mul	= 3;
									break;
							case '180': 
									$ten_mul	= 6;
									break;
							case '365': 
									$ten_mul	= 12;
									break;
						}
						if(defined("REMOTE_CITY_MODULE") )
						{
							if(in_array(strtolower($this->data_city),$remote_city_arry) )
							{
								//$row_bid['budget']=7500/$count;
								$row_bid['budget']=15000/$count;
							}
							else
							{
								//$row_bid['budget']=5000/$count;
								$row_bid['budget']=10000/$count;
							}
						}
						elseif((($row_temp['budget']/365) * 30)< $this->min_budget) // The self::MONTHLY budget should be greater than 2000 per month
						{
							$row_bid['budget']=($this->min_budget * $ten_mul)/$count;
						}
					}
					elseif(($category_banner_id == 3))			
					{
						/*if(defined("REMOTE_CITY_MODULE") && !in_array(strtolower($this->data_city),$remote_city_arry) && (($row_temp['budget']/365) * $tenure)>10000)
						{
							$row_bid['budget']=10000/$count;
						}
						elseif(defined("REMOTE_CITY_MODULE") && !in_array(strtolower($this->data_city),$remote_city_arry) && (($row_temp['budget']/365) * $tenure)<10000)
						{
							$row_bid['budget']=10000/$count;
						}*/
						if(defined("REMOTE_CITY_MODULE") )
						{
							if(in_array(strtolower($this->data_city),$remote_city_arry) )
							{
								$row_bid['budget']=7500/$count;
							}
							else
							{
								$row_bid['budget']=5000/$count;
							}
						}
						elseif((($row_temp['budget']/365) * $tenure)<12000)
						{
							$row_bid['budget']=12000/$count;
						}
					}
					elseif(($category_banner_id == 2) && (($row_temp['budget']/365) * $tenure)<12000)			
					{
						$row_bid['budget']=12000/$count;
					}
					if($tenure < 30)
					{
						die('Not Allowed');
					}
					
				}
				elseif($row_temp['variable_budget'])
				{
					$row_bid['budget'] = $row_bid['variable_budget'];
				}
				/*if($request['inv']!=''){
                    $free = $this -> getAvailableInventory($row_bid['catid'],$request['inventory']);
					//$row_bid['budget'] = ($row_bid['budget']*$request['inv'])/100;
                    $row_bid['budget'] = ($row_bid['budget']*$free['availiability'])/100;
				}*/
				if($category_banner_id == 2){
					$rowFlag['type_flag'] = $this->getTypeFlag($row_bid['title_id']);
				}
                $free = $this -> getAvailableInventory($row_bid[$catid_title_field],$request['inventory'],$rowFlag['type_flag']);

				$bid_per_day[budget]=($row_bid[budget]/$request['tenure']);	
		
				$insert_into="UPDATE ".$temp_table." SET 
								tenure			='".$request['tenure']."',
								bid_per_day		='".$bid_per_day['budget']."',
								variable_budget	='".$row_bid['budget']."',
								update_date		=now(),
								inventory		= '".$free['inv_avail']."'
							 WHERE parentid ='".$this->parentid."'
								AND campaign_type='".$category_banner_id."' 
								AND campaign_name='".$category_banner_name."' 
								AND ".$catid_title_field."='".$row_bid[$catid_title_field]."'";
				
				$res_insert = $this->conn_temp->query_sql($insert_into,$this->parentid);
				$totabudget+=$row_bid['budget'];
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Query=>".$insert_into." Result =>".$res_insert);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Total Budget=>".$totabudget);
			}
			if($this->campType){
				$camp_data = array("budget"=>round($totabudget),"original_budget"=>round($totabudget), "duration"=>$request['tenure'],"version"=>$request['version']);
				$finArrTemp= $financeobj->getFinanceMainData($this->campType);
				
				if($totabudget!= $finArrTemp[$this->campType]['budget']){
					$camp_data['recalculate_flag'] = 1;
				}else if($request['calculate_attribute']==1){
					$camp_data['recalculate_flag'] = 1;
				}
				$financeobj->financeInsertUpdateTemp($this->campType,$camp_data);
				$fieldname="";
				switch($this->campType)
				{
				case 13: $fieldname ="catspon"; break;
				case 14: $fieldname ="catfilter"; break;
				case 15: $fieldname ="cattext"; break;
				}
				$tempflowquiery = " INSERT INTO tbl_temp_flow_status set parentid='".$this->parentid."', ".$fieldname."=0
				ON DUPLICATE KEY UPDATE ".$fieldname."=0";
				$this->conn_temp->query_sql($tempflowquiery,$this->parentid);						
			}
		}
		
		function getCategories($temp_table,$category_banner_name,$category_banner_id){
			if($this->campType == '13'){
				$field = "catid";
			}elseif($this->campType == '14'){
				$field = "title_id";
			}elseif($this->campType == '15'){
				$field = "catid";
			}
			
			$sqlGetCat	="SELECT  ".$field." FROM ".$temp_table." WHERE parentid ='".$this->parentid."' AND campaign_type='".$category_banner_id."' AND campaign_name='".$category_banner_name."'";
			$qryGetCat	= $this->conn_temp->query_sql($sqlGetCat);
			if($qryGetCat){
				while($rowGetCat = mysql_fetch_assoc($qryGetCat)){
					$catidArr[] = $rowGetCat[$field];
				}
			}
			
			return $catidArr;
		}
		
		function tempTableUpdate($conn,$insertField,$upField,$table){
		
			if(count($insertField)){
				foreach($insertField as $key => $value){
					$insString.= $key."='".$value."'," ;
				}
				foreach($upField as $key => $value){
					$upString.= $key."='".$value."'," ;
				}
				$insString= trim($insString,",");
				$upString = trim($upString,",");
				
				 $insert= "insert into ".$table." set 
								parentid = '".$this->parentid."',
                                ".$insString."  
                            ON DUPLICATE KEY UPDATE
                                ".$upString; 
				$inseQry = $conn->query_sql($insert,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Query=>".$insert." Result=>".$inseQry);
			}
		}
	
		function getCompanyName_IDC($parentid)
		{					
			$compnamesql		= "select companyname from tbl_companymaster_generalinfo where parentid='".$parentid."'";				
			$compnamers		= $this->conn_IDC->query_sql($compnamesql);
			
			if($compnamers && mysql_num_rows($compnamers))
			{
				$compnamearr= mysql_fetch_assoc($compnamers);
				return $compnamearr['companyname'];
			}
		}

		function getCompanyName($parentid){
			if(is_array($parentid)){
				$compname = array();
				$paridstr = implode("','",$parentid);

				$temparr		= array();
				$fieldstr		= '';
				$fieldstr 		= "parentid,companyname";
				$tablename		= "tbl_companymaster_generalinfo";
				$wherecond		= "parentid IN ('".$paridstr."')";
				$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
			}else{
				$compname = '';

				$temparr		= array();
				$fieldstr		= '';
				$fieldstr 		= "companyname";
				$tablename		= "tbl_companymaster_generalinfo";
				$wherecond		= "parentid='".$parentid."'";
				$temparr		= $this->compmaster_obj->getRow($fieldstr,$tablename,$wherecond);
			}


			if($temparr['numrows'])
			{
				foreach($temparr['data'] as $row_comp){
					if(!is_array($parentid)){
						$compname = $row_comp['companyname'];
					}else{
						$compname[$row_comp['parentid']] = $row_comp['companyname'];
					}
				}
			}
			
			return $compname;
		}
	
		function getBookedBy($catid,$type='-1'){
			if($catid){
				$bidderArr	= array();
				if($this->campType == '15'){
					$field = " cat_textbanner_bidder AS bidder";
					$where = " catid = '".$catid."'";
					$table = "tbl_cat_banner_bid";
				}else if($this->campType == '13'){
					$field = " cat_sponbanner_bidder AS bidder ";
					$where = " catid = '".$catid."'";
					$table	 	= "tbl_cat_banner_bid";
				}else if($this->campType == '14'){
					$field = " hotkey_banner AS bidder ";
					$where = " vertical_id = '".$catid."' AND type_flag ='".$type."'";
					$table = "tbl_hotkey_banner_bid";
				}
				$sqlGet = "SELECT ".$field." FROM ".$table." WHERE ".$where." AND data_city='".$this->data_city."'";
				$qryGet = $this->conn_fnc->query_sql($sqlGet);
				if($qryGet){
					$rowGet 	= mysql_fetch_assoc($qryGet);
					$bidderArr	= $this->bidder_explode_array($rowGet['bidder']);
					foreach($bidderArr as $key =>$value){
						$bidderArr[$key]['compName'] = $this->getCompanyName($key);
					}
				}
			}
			
			return $bidderArr;
		}

		function temptoMain($version,$financeObj,$transState=0){ 
			/* 
				$transState = 1 --- Deal Close
				$transState = 2 --- Approval
				$transState = 3 --- Balance Readjustment
			*/
			
			$escape		= 0;
			
			$sponTemp	= $financeObj->getFinanceTempData('13');
			$filterTemp	= $financeObj->getFinanceTempData('14');
			$textTemp	= $financeObj->getFinanceTempData('15');
			
			
			$bannerTypeArr = array('1','2','3');
			$originalState = $transState;
			foreach($bannerTypeArr as $banner_type){
				if($banner_type == 1){
					$data_fin		= $financeObj->getFinanceMainData('13');
					$financemain	= $data_fin['13'];
					$temp			= $sponTemp['13'];
					$campaignid		= '13';
				}else if($banner_type == 2){
					$data_fin		= $financeObj->getFinanceMainData('14');
					$financemain	= $data_fin['14'];
					$temp			= $filterTemp['14'];
					$campaignid		= '14';
				}else if($banner_type == 3){
					$data_fin		= $financeObj->getFinanceMainData('15');
					$financemain	= $data_fin['15'];
					$temp			= $textTemp['15'];
					$campaignid		= '15';
				}
				
				$eligiblecontract=0;
							
				if(($financemain['expired'] == 0) || ($financemain['expired'] == 1 && $temp['recalculate_flag'] == 1 && $temp['budget']>0) || $transState == '2'){
					
					if((($sponTemp['13']['recalculate_flag'] != 1 && $banner_type == 1)|| ($filterTemp['14']['recalculate_flag'] != 1 && $banner_type == 2) || ($textTemp['15']['recalculate_flag'] != 1 && $banner_type == 3))&& (($version%10) == 1)){
							$transState = 3;
					}else{
						$transState = $originalState;
					}
					if($transState == '1'){
						if(strtolower($this->module) == 'cs' && (($version%10) == 1)){
							if($banner_type == 1 || $banner_type == 3){
								$tbl_arr = array('tbl_catspon_shadow' => 'tbl_catspon_temp');
							}else if($banner_type == 2){
								$tbl_arr = array('tbl_catfilter_shadow' => 'tbl_catfilter_temp');
							}
						}else{
							if($banner_type == 1 || $banner_type == 3){
								$tbl_arr = array('tbl_catspon'   => 'tbl_catspon_temp');
							}else if($banner_type == 2){
								$tbl_arr = array('tbl_catfilter' => 'tbl_catfilter_temp');
							}
						}
					}else if($transState == '2'){
						if(strtolower($this->module) == 'cs' && (($version%10) == 1)){
							if($banner_type == 1 || $banner_type == 3){
								$tbl_arr = array('tbl_catspon'   => 'tbl_catspon_shadow');
							}else if($banner_type == 2){
								$tbl_arr = array('tbl_catfilter' => 'tbl_catfilter_shadow');
							}
						}else{
							if($banner_type == 1 || $banner_type == 3){
								$tbl_arr = array('tbl_catspon'   => 'tbl_catspon');
							}else if($banner_type == 2){
								$tbl_arr = array('tbl_catfilter' => 'tbl_catfilter');
							}
						}
					}else if($transState == '3' && strtolower($this->module) == 'cs' && (($version%10) == 1)){
						if($banner_type == 1 || $banner_type == 3){
							$tbl_arr = array('tbl_catspon'   => 'tbl_catspon_temp');
						}else if($banner_type == 2){
							$tbl_arr = array('tbl_catfilter' => 'tbl_catfilter_temp');
						}
					}
					if($banner_type == '1'){
						if($transState == 1 || $transState == 3)
							$tabname = 'tbl_catspon_temp';
						else if($transState == 2)
							if($this->module == 'tme' || $this->module == 'me'){
								$tabname = 'tbl_catspon';
							}else{
								$tabname = 'tbl_catspon_shadow';
							}
						$sql	= "SELECT MIN(banner_camp) AS banner_camp  FROM ".$tabname." WHERE parentid='".$this->parentid."' AND campaign_type=1";
						
						if(($this->module == 'tme' || $this->module == 'me') && $transState==2){
							$qry = $this->conn_main->query_sql($sql);
						}else{
							$qry = $this->conn_temp->query_sql($sql);
						}
						$row	= mysql_fetch_assoc($qry);
						if($row['banner_camp'] == 1 || $row['banner_camp'] == 2){
							$escape = 1;
							continue;
						}
					}
					foreach($tbl_arr as $main_table => $temp_table)
					{
						
						if($transState == '1'){
							if(strtolower($this->module) == 'cs' && (($version%10) == 1)){
								$entity_id   = ($main_table == 'tbl_catspon_shadow') ? 'catid'   :'title_id';
								$entity_name = ($main_table == 'tbl_catspon_shadow') ? 'cat_name':'title_name';
								$entity_national = ($main_table == 'tbl_catspon_shadow')?'national_catid':'';
							}else{
								$entity_id   = ($main_table == 'tbl_catspon') ? 'catid'   :'title_id';
								$entity_name = ($main_table == 'tbl_catspon') ? 'cat_name':'title_name';
								$entity_national = ($main_table == 'tbl_catspon')?'national_catid':'';
							}
						}else if($transState == '2' || $transState == '3'){
							$entity_id   = ($main_table == 'tbl_catspon') ? 'catid'   :'title_id';
							$entity_name = ($main_table == 'tbl_catspon') ? 'cat_name':'title_name';
							$entity_national = ($main_table == 'tbl_catspon')?'national_catid':'';
						}

						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Main Table = ".$main_table);
						if($transState == 1){
							if($banner_type == 1 || $banner_type == 3){
								if(strtolower($this->module) == 'cs')
									$table_name = "tbl_catspon_shadow";
								else
									$table_name = "tbl_catspon";
							}else{
								if(strtolower($this->module) == 'cs')
									$table_name = "tbl_catfilter_shadow";
								else
									$table_name = "tbl_catfilter";
							}
						}else if($transState == 2 ){ 
							if($banner_type == 1 || $banner_type == 3){
								$table_name = "tbl_catspon";
							}else{
								$table_name = "tbl_catfilter";
							}
						}else if($transState == 3 && strtolower($this->module) == 'cs'){
							if($banner_type == 1 || $banner_type == 3){
								$table_name = "tbl_catspon";
							}else{
								$table_name = "tbl_catfilter";
							}
						}
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Before Calling Release inventory");
						$this -> ReleaseInventory($banner_type,$transState,$table_name,$version,$sponTemp['13']['recalculate_flag']); //Release Inventory of deleted categories
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"After Calling Release inventory");
						$sql_del = "DELETE FROM ".$main_table." WHERE parentid = '".$this->parentid."' AND campaign_type = ".$banner_type;
						
						if($transState == 1){
							if(strtolower($this->module) == 'cs'){
								$this->conn_local->query_sql($sql_del,$this->parentid);
							}else{
								$this->conn_main->query_sql($sql_del,$this->parentid);
							}
						}else if($transState == 2) {
							if(strtolower($this->module) == 'me'){
								$this->conn_decs->query_sql($sql_del,$this->parentid);
							}else{
								$this->conn_local->query_sql($sql_del,$this->parentid);
							}
						}else if($transState == 3 && strtolower($this->module) == 'cs'){
							$this->conn_local->query_sql($sql_del);
						}
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete from table called =>".$sql_del);
						$row_naltional='';
						if($entity_national){
							$entity_national = ",".$entity_national;
						}
						$sql_temp = "SELECT budget,update_date,".$entity_name.",".$entity_id.",tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,iscalculated,inventory,selectedCities".$entity_national." FROM ".$temp_table." where parentid='".$this->parentid."' and campaign_type =".$banner_type;
						
						//$qry_temp = $this->conn_temp->query_sql($sql_temp); exit;
						if(($this->module == 'tme' || $this->module == 'me') && $transState==2){
							$qry_temp = $this->conn_main->query_sql($sql_temp); 
						}else{
							$qry_temp = $this->conn_temp->query_sql($sql_temp); 
						}	
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"select query for insert =>".$sql_temp." Result =>".$qry_temp);
						
						if($qry_temp && mysql_num_rows($qry_temp))
						{
							$eligiblecontract = 1;
							$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"entry present in temp table =>".$temp_table);
							$sql_main = "INSERT INTO ".$main_table."  (parentid,budget,update_date,".$entity_name.",".$entity_id.",tenure,start_date,end_date,bid_per_day,variable_budget,campaign_name,campaign_type,iscalculated,inventory,selectedCities".$entity_national.")VALUES";
							$sql_value = "";
							$catid_banner_arr = array();
							
							while($row_temp = mysql_fetch_assoc($qry_temp))
							{	
								$row_naltional ='';
								if($transState == '1'){
									$start_date = $row_temp['start_date'];
									$end_date	= $row_temp['end_date'];
								}elseif($transState == '2' || $transState == '3'){
									$start_date = 'now()';
									$end_date	= 'now()';
								}
								$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"calling booking function for entity id =>".$row_temp[$entity_id]." entity name=>".$row_temp[$entity_name]);
								if($transState == 1 || $transState == 3){
									$this -> insertDealClose($row_temp[$entity_id],$row_temp[campaign_type],$version,$row_temp['inventory'],$row_temp['national_catid']);/*function to book banner*/
								}
								$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"done calling of booking function for entity id =>".$row_temp[$entity_id]."entity name=>".$row_temp[$entity_name]);					
								$catid_banner_arr[$row_temp[campaign_type]][] = $row_temp[$entity_id];/*setting catids into array for booking against respective banner*/
								if($entity_national){
									$row_naltional = ",'".$row_temp['national_catid']."'";
								}
								$sql_value.="('".$this->parentid."','".$row_temp['budget']."','now()','".$row_temp[$entity_name]."','".$row_temp[$entity_id]."','".$row_temp['tenure']."','".$start_date."','".$end_date."','".$row_temp['bid_per_day']."','".$row_temp['variable_budget']."','".$row_temp['campaign_name']."','".$row_temp['campaign_type']."','".$row_temp['iscalculated']."','".$row_temp['inventory']."','".$row_temp['selectedCities']."'".$row_naltional."),";
								
								$askedInv	= ($row_temp['inventory']);
								if($row_temp['campaign_type'] == '1')
									$this->campType = '13';
								else if($row_temp['campaign_type'] == '2')
									$this->campType = '14';
								else if($row_temp['campaign_type'] == '3')
									$this->campType = '15';
								
								if($row_temp['inventory'] > 0){
									if($transState == 1 || $transState==3){
										if($transState==3){
											$status = 2;
										}else{
											$status = 1;
										}
										$this->sponTextInvMgmt($row_temp[$entity_id],$status,$askedInv,$this->campType,$row_temp['national_catid']);
									}elseif($transState == 2){
										$this->sponTextInvMgmt($row_temp[$entity_id],2,$askedInv,$this->campType,$row_temp['national_catid']);
										//$this->approvalInventory();
									}
								}
							}
							
							if($sql_value)
							{
								$sql_main = $sql_main.rtrim($sql_value,","); 
								
								if((strtolower($this->module) == 'tme' || strtolower($this->module) == 'me') && $transState == 2){
									$res_main = $this->conn_decs->query_sql($sql_main,$this->parentid);							
								}else{
									if(strtolower($this->module) == 'cs'){
										$res_main = $this->conn_local->query_sql($sql_main,$this->parentid);
									}else{
										$res_main = $this -> conn_main->query_sql($sql_main,$this->parentid);
									}
								}
							}
							$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert query =>".$sql_main);
							if(strtolower($this->module) == 'cs' && $transState == 2 && ($version%10 == 1)){
								$sqlDelShadow ="DELETE FROM ".$temp_table." WHERE parentid = '".$this->parentid."' AND campaign_type = ".$banner_type;
								//$this->conn_temp->query_sql($sqlDelShadow,$this->parentid);
								$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete from temp table =>".$sqlDelShadow);
							}
							
						}
						$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Next Loop - main table => ".$main_table."  temp table =>".$temp_table);
					}

				if($transState==2 && $eligiblecontract)// here we call designer banner function 
				{ 
					die;
					if($version%10 == 1)// cs
					{
						$companyname = $this ->getCompanyName($this ->parentid);
					}else
					{
						$companyname = $this ->getCompanyName_IDC($this ->parentid);
					}
					$DesignerBanner = new DesignerBanner();
					echo "DesignerBanner:-------";print_r($DesignerBanner);die;
					$DesignerBanner->insertOnApproval($this->conn_fnc,$this ->parentid,$version,$campaignid,$companyname,$this->data_city);
				}
				//die("inside banner management class");
				}else{
					if($banner_type == 1)
						$ctype = 'Cat Spon';
					else if ($banner_type == 2)
						$ctype = 'filter banner';
					else if ($banner_type == 3)
						$ctype = 'text filter';
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Contract expired and not recalculated --- ".$ctype);
				}
			}
	
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Finishing Loop - main table => ".$main_table."  temp table =>".$temp_table);
			
			return (!$escape);
		
		}
	
		function insertDealClose($entity_id,$banner_type,$version,$inventory,$national_id=null){/*function to book requested banner for category*/
			$inventory_table = ($banner_type == 2)?'tbl_categoryfilterbanner_bid':'tbl_categorybanner_bid';
			$field_id   	 = ($banner_type == 2)?'titleid':'catid';
			$field_name 	 = ($banner_type == 2)?'titlename':'cat_name';
			$dealcolse_table = ($banner_type == 2)?'tbl_d_dg_pin_dealclosed_catfilter':'tbl_d_dg_pin_dealclosed_catspon';
			$extra_nationalField = ($banner_type!= 2)?",national_catid='".$national_id."'":"";

			$mode = ($version % 10) == 1 ? 'OFFLINE' : 'ONLINE';

			$ucode 			= $_SESSION['ucode'];
			
			if($banner_type!=2){
				$sql_do_entry_dealclose = "INSERT INTO ".$dealcolse_table." SET 
											parentid        = '".$this -> parentid."',
											bid_catid       = '".$entity_id."',
											contract_type   = '".$banner_type."',
											source          = '".$mode."',
											data_city       = '".$this -> data_city."',
											version         = '".$version."',
											booked_date     = '".date('Y-m-d H:i:s')."',
											booked_by       = '".$ucode."',
											inventory		= '".$inventory."'
											".$extra_nationalField ."
											ON DUPLICATE KEY UPDATE
											version         = '".$version."',
											contract_type   = '".$banner_type."',
											".$bookedD."
											booked_by       = '".$ucode."',
											inventory		= '".$inventory."'";
				$res_do_entry_dealclose = $this -> conn_fnc->query_sql($sql_do_entry_dealclose,$this->parentid); 

				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Insert query for inventory in dealclosed table =>".$sql_do_entry_dealclose." Result=>".$res_do_entry_dealclose);
			}else{
				$rowFlag['type_flag'] = $this->getTypeFlag($entity_id);
				$b2bflag = intval($rowFlag['type_flag']);

				if($b2bFlag!='' || intval($b2bFlag)===0)
				{
					$sql_do_entry_dealclose_catfilter = "INSERT INTO ".$dealcolse_table." SET 
											parentid        = '".$this -> parentid."',
											bid_catid       = '".$entity_id."',
											contract_type   = '".$banner_type."',
											source          = '".$mode."',
											data_city       = '".$this -> data_city."',
											version         = '".$version."',
											booked_date     = '".date('Y-m-d H:i:s')."',
											booked_by       = '".$ucode."',
											b2bflag         = ".intval($b2bFlag).",
											inventory		= '".$inventory."'
											ON DUPLICATE KEY UPDATE
											version         = '".$version."',
											".$bookedD."
											booked_by       = '".$ucode."',
											inventory		= '".$inventory."'";
					$res_do_entry_dealclose_catfilter = $this -> conn_fnc->query_sql($sql_do_entry_dealclose_catfilter,$this->parentid); 
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," dealclosed dooking query ->".$sql_do_entry_dealclose_catfilter." resutl set =>".$res_do_entry_dealclose_catfilter);
				}
			}			
		}
		
		function setVersionModule($version,$flag){

			if($flag == 1 && $this->module == ''){

				if($version%10 ==1){
					$this->module ='cs';
				}else if($version%10 ==2){
					$this->module ='tme';
				}else if($version%10 ==3){
					$this->module ='me';
				}
				
			}
		}
	
		function askedBudget($field,$table,$where, $catid){
			$bidderArr	= array();
			$fieldArr	= explode(',',$field);
			$sql = "SELECT ".$field." FROM ".$table." WHERE ".$where." = '".$catid."'";
			$qry = $this->conn_fnc->query_sql($sql);
			if($qry){
				$row		= mysql_fetch_assoc($qry);
				$bidderArr	= $this->bidder_explode_array($row[$fieldArr['0']]);
				foreach($bidderArr as $key => $value){
					if($bidderArr[$key]['act_inv']!=$bidderArr[$key]['inv']){
						$bidderArr[$key]['act_catInv'] = $row[$fieldArr['1']];
                        $bidderArr[$key]['total_bookInv']=$row[$fieldArr['2']];
					}
				}
			}
			
			return $bidderArr;
		}
		
		function approvalInventory(){ 
			$campaingnArr = array('13','14','15');
			foreach($campaingnArr as $campaign){

				if($campaign == '13'){
					if(strtolower($this->module) == 'cs'){
						$str = '_shadow';
					}
					$sql = "SELECT catid FROM tbl_catspon{$str} where parentid = '".$this->parentid."' and campaign_type = '1' and iscalculated = '1'";
					
					if(strtolower($this->module) == 'cs'){
						$qry = $this->conn_local->query_sql($sql);
					}else{
						$qry = $this->conn_main->query_sql($sql);
					}
					
					if($qry && mysql_num_rows($qry)){
						while($row = mysql_fetch_assoc($qry)){
                            $finalCatActInv = 0;
							$field	= "cat_sponbanner_bidder,cat_sponbanner_actual_inventory,cat_sponbanner_inventory";
							$table	= "tbl_cat_banner_bid";
							$where	= "catid";
							$invArr = $this->askedBudget($field,$table,$where, $row['catid']);
							if(count($invArr)){
								foreach($invArr as $key => $value){
									if($key == $this->parentid){
                                        $delta_actual_inventory = $invArr[$key]['inv'] - $invArr[$key]['act_inv'];
                                        $total_inventory = $invArr[$key]['total_bookInv'];
                                        if($total_inventory<=1)
                                        {
                                            $invArr[$key]['act_inv']  = $invArr[$key]['act_inv'] + $delta_actual_inventory;
                                        }
										if($invArr[$key]['act_inv'] > 0 || $finalCatActInv > 1){
											$checkFlg = 1;
										}
									}
                                    if($finalCatActInv<=1)
                                    {
                                        $finalCatActInv += $invArr[$key]['act_inv'];
                                    }
								}

								if($checkFlg == 1){
									$finalBidderStr = $this->bidder_implode_array($invArr);
									
									$update = "update ".$table." set 
												cat_sponbanner_bidder 			= '".$finalBidderStr."',
												cat_sponbanner_actual_inventory = '".$finalCatActInv."'
											   where catid = '".$row['catid']."' and data_city = '".$this->data_city."'"; 
									$this->conn_fnc->query_sql($update,$this->parentid);
									$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid," Update Approval =>".$update);
								}
							}
						}
					}
				}else if($campaign == '15'){
					if(strtolower($this->module) == 'cs'){
						$str = '_shadow';
					}
					$sql = "SELECT catid FROM tbl_catspon{$str} where parentid = '".$this->parentid."' and campaign_type = '3' and iscalculated = 1";
					if(strtolower($this->module) == 'cs'){
						$qry = $this->conn_local->query_sql($sql);
					}else{
						$qry = $this->conn_main->query_sql($sql);
					}
					if($qry && mysql_num_rows($qry)){
						while($row = mysql_fetch_assoc($qry)){
                            $finalCatActInv = 0;
							$field	= "cat_textbanner_bidder,cat_textbanner_actual_inventory,cat_textbanner_inventory";
							$table	= "tbl_cat_banner_bid";
							$where	= "catid";
							$invArr = $this->askedBudget($field,$table,$where, $row['catid']);
							
							if(count($invArr)){
								foreach($invArr as $key => $value){
									if($key == $this->parentid){
										/*$invArr[$key]['act_inv']  = $invArr[$key]['act_inv'] + $invArr[$key]['inv'];
										$finalCatActInv		= $invArr['act_catInv']+$invArr['inv'];*/
                                        $delta_actual_inventory = $invArr[$key]['inv'] - $invArr[$key]['act_inv'];
                                        $total_inventory = $invArr[$key]['total_bookInv'];
                                        if($total_inventory<=1)
                                        {
                                            $invArr[$key]['act_inv']  = $invArr[$key]['act_inv'] + $delta_actual_inventory;
                                        }
										if($invArr[$key]['act_inv'] > 0 || $finalCatActInv > 1){
											$checkFlg = 1;
										}
									}
                                    if($finalCatActInv<=1)
                                    {
                                        $finalCatActInv += $invArr[$key]['act_inv'];
                                    }
								}
								
								if($checkFlg == 1){
									$finalBidderStr = $this->bidder_implode_array($invArr);
									
									$update = "update ".$table." set 
												cat_textbanner_bidder 			= '".$finalBidderStr."',
												cat_textbanner_actual_inventory = '".$finalCatActInv."'
											   where catid = '".$row['catid']."' and data_city = '".$this->data_city."'";
									$this->conn_fnc->query_sql($update,$this->parentid);
								}
							}
						}
					}
				}else if($campaign == '14'){
					if(strtolower($this->module) == 'cs'){
						$str = '_shadow';
					}
					 $sql = "SELECT title_id FROM tbl_catfilter{$str} where parentid = '".$this->parentid."' and campaign_type = '2' and iscalculated = 1";
					if(strtolower($this->module) == 'cs'){
						$qry = $this->conn_local->query_sql($sql);
					}else{
						$qry = $this->conn_main->query_sql($sql);
					}
					if($qry && mysql_num_rows($qry)){
						while($row = mysql_fetch_assoc($qry)){
                            $finalCatActInv = 0;
							$field	= "hotkey_banner,hotkey_banner_actual_inventory,hotkey_banner_inventory";
							$table	= "tbl_hotkey_banner_bid";
							$where	= "vertical_id";
							$invArr = $this->askedBudget($field,$table,$where, $row['title_id']);
							if(count($invArr)){
								foreach($invArr as $key => $value){
									if($key == $this->parentid){
										/*$invArr[$key]['act_inv']  = $invArr[$key]['act_inv'] + $invArr[$key]['inv'];
										$finalCatActInv		= $invArr['act_catInv']+$invArr['inv'];*/
                                        $delta_actual_inventory = $invArr[$key]['inv'] - $invArr[$key]['act_inv'];
                                        $total_inventory = $invArr[$key]['total_bookInv'];
                                        if($total_inventory<=1)
                                        {
                                            $invArr[$key]['act_inv']  = $invArr[$key]['act_inv'] + $delta_actual_inventory;
                                        }
										if($invArr[$key]['act_inv'] > 0 || $finalCatActInv > 1){
											$checkFlg = 1;
										}
									}
                                    if($finalCatActInv<=1)
                                    {
                                        $finalCatActInv += $invArr[$key]['act_inv'];
                                    }
								}
								if($checkFlg == 1){
									$finalBidderStr = $this->bidder_implode_array($invArr);
									
									$update = "update ".$table." set 
												hotkey_banner 					= '".$finalBidderStr."',
												hotkey_banner_actual_inventory  = '".$finalCatActInv."'
											   where vertical_id = '".$row['title_id']."' and data_city = '".$this->data_city."'"; 
									$this->conn_fnc->query_sql($update,$this->parentid);
								}
							}
						}
					}
				}
			}
		}

		function dealclose_catspon_checking($curlflag=0){
			$campaignArr = array('13','14','15');
            $invErr =array();
            $error_title ='';
			foreach($campaignArr as $campaign){
				if($campaign == 13  || $campaign == 15){
					if($campaign == 13){
						$field_banner	= array("MIN(banner_camp) AS banner_camp");
						$getArr_banner	= $this->showSelected($this->conn_temp,$field_banner,"tbl_catspon_temp",$campaign);
						if($getArr_banner['0']['banner_camp'] == 1 || $getArr_banner['0']['banner_camp'] == 2){
							continue;
						}
					}
				}
				$field 		= array('catid', 'cat_name','inventory');
				$tempTable 	= "tbl_catspon_temp";
				$invTable 	= "tbl_cat_banner_bid";
				$where		= "catid";
				$field_name = "catid";
				if($campaign == 13){
					$this->campType = 13;
					$invField = "cat_sponbanner_bidder AS bidder, cat_sponbanner_inventory AS inventory";
					$category_banner_name = "Category Sponsorship";
					$link	  = "category_sponsership.php";
				}elseif($campaign == 15){
					$this->campType = 15;
					$invField = "cat_textbanner_bidder AS bidder, cat_textbanner_inventory AS inventory";
					$category_banner_name = "Category Text Banner";
					$link	  = "category_text_banner.php";
				}
				elseif($campaign == 14){
					$this->campType = 14;
					$field 		= array('title_id', 'title_name', 'inventory');
					$tempTable 	= "tbl_catfilter_temp";
					$invTable  	= "tbl_hotkey_banner_bid";
					$invField  	= "hotkey_banner AS bidder, hotkey_banner_inventory AS inventory";
					$where		= "vertical_id";
					$field_name = "title_id";
					$category_banner_name = "Category Filter Banner";
					$link	  	= "category_filter_banner.php";
				}
				$getArr = $this->showSelected($this->conn_temp,$field,$tempTable,$campaign);
				$fieldArr	= $field;
				unset($invErr);
				foreach($getArr as $rowGet){
					$askedInventory = $rowGet['inventory'];
					$typeFlg  = '-1';
					if($campaign == '14')
						$typeFlg	= $this->getTypeFlag($rowGet[$field_name]);
						$free = $this->getAvailableInventory($rowGet[$field_name],($askedInventory*100),$typeFlg);

                    if($free['inv_avail']!=0)
                    {
                        if($askedInventory>$free['inv_avail'])
                        {
                            $invErr[$rowGet[$fieldArr[0]]] = $rowGet[$fieldArr[1]];
                        }
                    }
                    else
                    {
                        $invErr[$rowGet[$fieldArr[0]]] = $rowGet[$fieldArr[1]];
                    }

				}
				if($campaign == '13')
					$errorSponFlag = 0;
				else if($campaign == '14')
					$errorTextFlag = 0;
				else if($campaign == '15')
					$errorFilterFlag = 0;
				$errorFlag = 0;
				$errMsg		= '';
 				if(count($invErr)){
					if($campaign == '13')
						$errorSponFlag = 0;
					else if($campaign == '14')
						$errorTextFlag = 0;
					else if($campaign == '15')
						$errorFilterFlag = 1;
					$start_msg = "<div align='center'><B><font color='red' size='5'>Category Inventory Overlapping</font></B>";

					$error_title .= "<br><B>Booking error in following categories for <font color='red' size='3'>".$category_banner_name."</font> campaign</B>";
					$error_title .='<table border="1" cellpadding="1" cellspacing="0" width="45%">
                        <tr>
                      <td width="5%">&nbsp;<b>Sr No.</b></td>
                      <td width="10%">&nbsp;<b>Catid</b></td>
                      <td width="10%">&nbsp;<b>Cat name<b/></td>
                     </tr>';
					 $counter	= 0;

					 foreach($invErr as $key => $value){
						$error_title .= "<tr><td>&nbsp;" . ++$counter . "</td>
                                        <td>&nbsp;". $key . "</td>
                                        <td>&nbsp;". $value . "</td></tr>";
					}
				}

				if($curlflag == '1'){
					$errMsg = $start_msg.$error_title.$error_title_end;
				}else if(count($invErr)){
					echo $start_msg.$error_title.$error_title_end;
					exit;
				}
				if($curlflag == '1'){
					if($errorSponFlag == 1 || $errorTextFlag == 1 || $errorFilterFlag == 1){
						$errorFlag = 1;
					}
					return $errorFlag."|^|^|^|^|^|^|".$errMsg;
				}
			}
		}

		function getTypeFlag($catid){
			$type_flag		='-1';
			if(trim($catid)!=''){
				$sqlGetType = "SELECT type_flag FROM tbl_verticalid_generator WHERE vertical_id='".$catid."'";
				$qryGetType = $this->conn_server->query_sql($sqlGetType);
				$rowGetType = mysql_fetch_assoc($qryGetType);
				$type_flag	= $rowGetType['type_flag'];
			}
			return $type_flag;
		}
	
		function catbannerInventoryStatus($input_status,$approvalState=0){
			/*
				approvalState == 1 -------- auto approval
				approvalState == 0 -------- normal approval
			*/
			$campaignArr = array('13','14','15');
			$error_title ='';
			foreach($campaignArr as $campaign){
				
				$invErr= array();
				if($campaign == 13  || $campaign == 15){
					$whereAnd = '';
					$field 		= array('catid', 'cat_name', 'inventory');	
					if(strtolower($this->module) == 'cs'){
						$tempTable 	= "tbl_catspon_shadow";
						$connection = $this->conn_temp;
					}else{
						$tempTable 	= "tbl_catspon";
						$connection = $this->conn_main;
					}
					$invTable 	= "tbl_cat_banner_bid";
					$where		= "catid";
					$field_name = "catid";
					if($campaign == 13){
						$typeFlg		= -1;
						$this->campType = 13;
						$invField = "cat_sponbanner_bidder AS bidder, cat_sponbanner_actual_inventory AS inventory";
						$category_banner_name = "Category Sponsorship";
					}elseif($campaign == 15){
						$typeFlg		= -1;
						$this->campType = 15;
						$invField = "cat_textbanner_bidder AS bidder, cat_textbanner_actual_inventory AS inventory";
						$category_banner_name = "Category Text Banner";
					}
					
					if($campaign == 13){
						$ban_check_arr = $this->showSelected($connection,array("DISTINCT banner_camp"),$tempTable,$campaign);	
						if(count($ban_check_arr) > 1){
							die("Error in campaign data. Please contract software team");
						}else{
							$bann_det	= $ban_check_arr['0']['banner_camp'];
							if($bann_det == 1 || $bann_det == 2){
								continue;
							}
						}
					}
				}
				elseif($campaign == 14){
					$this->campType = 14;
					$field 		= array('title_id', 'title_name', 'inventory');
					if(strtolower($this->module) == 'cs'){
						$tempTable 	= "tbl_catfilter_shadow";
						$connection = $this->conn_temp;
					}else{
						$tempTable 	= "tbl_catfilter";
						$connection = $this->conn_main;
					}
					$invTable  	= "tbl_hotkey_banner_bid";
					$invField  	= "hotkey_banner AS bidder,hotkey_banner_actual_inventory AS inventory";
					$where		= "vertical_id";
					$field_name = "title_id";
					$category_banner_name = "Category Filter Banner";
				}
				
				$getArr = $this->showSelected($connection,$field,$tempTable,$campaign);
				
				$fieldArr	= $field;
				
				foreach($getArr as $rowGet){
					$askedInventory = $rowGet['inventory'];
					if($askedInventory == 0) continue;
					$typeFlg  = '-1';
					if($campaign == '14')
						$typeFlg	= $this->getTypeFlag($rowGet[$field_name]);
                    
					$free = $this->getAvailableInventory($rowGet[$field_name],($askedInventory*100),$typeFlg);
					
                    if($free['inv_avail']!=0)
                    {
                        if($askedInventory>$free['inv_avail'])
                        {
                            $invErr[$rowGet[$fieldArr[0]]] = $rowGet[$fieldArr[1]];
                        }
                    }
                    else
                    {
                        $invErr[$rowGet[$fieldArr[0]]] = $rowGet[$fieldArr[1]];
                    }
					
				} 
				if(count($invErr)){
					
					$error_title .= "<br><B>Booking error in following categories for <font color='red' size='3'>".$category_banner_name."</font> campaign</B>";
					$error_title .='<table border="1" cellpadding="1" cellspacing="0" width="45%">
						<tr>
					  <td width="5%">&nbsp;<b>Sr No.</b></td>
					  <td width="10%">&nbsp;<b>Catid</b></td>
					  <td width="10%">&nbsp;<b>Cat name<b/></td>
					 </tr>';
					 $counter	= 0;
					 foreach($invErr as $key => $value){
						$error_title .= "<tr><td>&nbsp;" . ++$counter . "</td>
										<td>&nbsp;". $key . "</td>
										<td>&nbsp;". $value . "</td></tr>";
					}
					$error_title .= "</table><BR></div>";
					//$error_title_end = "</table><h3>click <a href ='00_Payment_Rework/accInstrument.php?mode=3'> here</a> to set it</h3><BR>";
				}
			}
			
			$start_msg = "<div align='center'><B><font color='red' size='5'>Category Inventory Overlapping</font></B>";
			
			if($input_status==3 && trim($error_title)!='')
			{
				if($approvalState == 0){
					echo $start_msg.$error_title.$error_title_end;
					exit;
				}else{
					return false;
				}
			}else if($input_status==3 && count($invErr)==0 && $approvalState == 1){
				return true;
			}
		}

		function ReleaseInventory($banner_type,$tras_state,$table_name,$version){
			if($tras_state==1 || $tras_state==3)
			{
				$table_flag = 1;
			}
			elseif($tras_state==2)
			{
				$table_flag = 3;
			}
			//echo "<br>in inventory release:<br>trans_state=>".$tras_state."  banner_type=>".$banner_type."  table_name=>".$table_name;
			$fields=(($banner_type==2)?'title_id':'catid');
			$catbanner_array = $this->getBannerCatid($tras_state,$banner_type,$table_flag);

			$sql_bidcat = "SELECT ".$fields.",inventory FROM ".$table_name." WHERE parentid='".$this -> parentid."' AND campaign_type= '".$banner_type."'";
			if(strtolower($this->module) == 'cs')
			{
				$res_bidcat = $this -> conn_temp -> query_sql($sql_bidcat);
			}
			else
			{
				if($tras_state==2 || $tras_state==3){
					$res_bidcat = $this->conn_server_decs->query_sql($sql_bidcat);
				}else{
					$res_bidcat = $this->conn_main->query_sql($sql_bidcat);
				}
			}
			if($res_bidcat && mysql_num_rows($res_bidcat))
			{
				$main_table=(($banner_type==2)?'tbl_catfilter':'tbl_catspon');
				$where_field =(($banner_type==2)?'title_id':'catid');
				$ip_status='2';//passing input status
				while ($row_bidcat = mysql_fetch_assoc($res_bidcat))
				{
					$qry_main_banner_table ="SELECT * FROM ".$main_table." WHERE parentid ='".$this->parentid."' AND ".$where_field ."= '".$row_bidcat[$fields]."' AND campaign_type='".$banner_type."'";
					if(strtolower($this->module) == 'cs'){
						$res_main_banner_table = $this->conn_local->query_sql($qry_main_banner_table);
					}else{
						$res_main_banner_table = $this->conn_server_decs->query_sql($qry_main_banner_table);
					}
					
					if((($table_name == 'tbl_catspon_shadow' || $table_name == 'tbl_catfilter_shadow')&& mysql_num_rows($res_main_banner_table) == 0) || ($table_name == 'tbl_catspon' || $table_name == 'tbl_catfilter') && strtolower($this->module)=='cs')
					{	
						
						if(!in_array($row_bidcat[$fields],$catbanner_array))
						{
							switch($banner_type){
								case '1':
											$campID = '13';
											break;
								case '2':
											$campID = '14';
											break;
								case '3':
											$campID = '15';
											break;
							}
							$this->sponTextInvMgmt($row_bidcat[$fields],$ip_status,'0',$campID);
						}
					}else if(mysql_num_rows($res_main_banner_table)>0 && $tras_state == 3 || strtolower($this->module) =='cs'){
						if(!in_array($row_bidcat[$fields],$catbanner_array))/*if pincode is not present in category array*/
						{
							
							switch($banner_type){
								case '1':
											$campID = '13';
											break;
								case '2':
											$campID = '14';
											break;
								case '3':
											$campID = '15';
											break;
							}
							$this->sponTextInvMgmt($row_bidcat[$fields],$ip_status,'0',$campID);
						}
					}else if(strtolower($this->module) !='cs' && (mysql_num_rows($res_main_banner_table) == 0 || $tras_state == 2)){
						if(!in_array($row_bidcat[$fields],$catbanner_array))/*if pincode is not present in category array*/
						{
							switch($banner_type){
								case '1':
											$campID = '13';
											break;
								case '2':
											$campID = '14';
											break;
								case '3':
											$campID = '15';
											break;
							}
							$this->sponTextInvMgmt($row_bidcat[$fields],$ip_status,'0',$campID);
						}
					}
				}
			}
		}
		
		function getBannerCatid($tras_state,$banner_type,$table_flag=''){
			if($banner_type==1 || $banner_type==3)
			{
				$fieldname = 'catid';
				if($table_flag==1)
				{
					$table_name='tbl_catspon_temp';
					$connect = $this->conn_temp;
				}
				elseif($table_flag==3)
				{
					if($this->module =='cs'){
						$table_name='tbl_catspon_shadow';
						$connect = $this->conn_temp;
					}
					else{
						$table_name='tbl_catspon';
						$connect = $this->conn_main;
					}
				}
				else
				{
					$table_name='tbl_catspon';
					if(strtolower($this->module) == 'cs')
					{
						$connect = $this->conn_temp;
					}
					else
					{
						$connect = $this->conn_main;
					}
				}
				if($tras_state==1 && $table_flag==1){
					$main_fieldname = 'catid';
					$main_connect= $this->conn_server_decs;
					$main_table_name='tbl_catspon';
				}
			}
			elseif($banner_type==2)
			{
				$fieldname = 'title_id';
				if($table_flag==1)
				{
					$table_name='tbl_catfilter_temp';
					$connect = $this->conn_temp;
				}
				elseif($table_flag==3)
				{
					if($this->module =='cs'){
						$table_name='tbl_catfilter_shadow';
						$connect = $this->conn_temp;
					}
					else{
						$table_name='tbl_catfilter';
						$connect = $this->conn_main;
					}
					
				}
				else
				{
					$table_name='tbl_catfilter';
					if(strtolower($this->module) == 'cs')
					{
						$connect = $this->conn_temp;
					}
					else
					{
						$connect = $this->conn_main;
					}
				}
				if($tras_state==1 && $table_flag==1){
					$main_fieldname = 'title_id';
					$main_connect= $this->conn_server_decs;
					$main_table_name='tbl_catfilter';
				}
			}
			$catidArr	   = array();
			$qry_get_catid = "select ".$fieldname." from ".$table_name." where parentid='".$this->parentid."' AND campaign_type = '".$banner_type."'";
			$res_get_catid = $connect->query_sql($qry_get_catid);
			if($res_get_catid && mysql_num_rows($res_get_catid)>0)
			{
				while($row_get_catid = mysql_fetch_assoc($res_get_catid)){
					$catidArr[]  = $row_get_catid[$fieldname];
				}
			}
			
			//added by vaibhav start
			$maincatidArr = array();
			if($tras_state==1 && $table_flag==1){
				$qry_get_maincatid = "select ".$main_fieldname." from ".$main_table_name." where parentid='".$this->parentid."' AND campaign_type = '".$banner_type."'";
				$res_get_maincatid = $main_connect->query_sql($qry_get_maincatid);
				if($res_get_maincatid && mysql_num_rows($res_get_maincatid)>0)
				{
					while($row_get_maincatid = mysql_fetch_assoc($res_get_maincatid)){
						$maincatidArr[]  = $row_get_maincatid[$main_fieldname];
					}
				}
			}
			//added by vaibhav end
			
			$catidArr_final = array_merge($catidArr,$maincatidArr);
			$catidArr_final = array_unique($catidArr_final);
			return $catidArr_final;
		}
		
		function mainToTemp($finance_obj){
			$cat_spon_balance =0;
			$cat_filt_balance =0;
			$cat_text_balance =0;	


			$catspon_finance_arr = $finance_obj->getFinanceTempData(13); 
			$cat_spon_balance=$catspon_finance_arr[13]['balance'];

			$catfilter_finance_arr = $finance_obj->getFinanceTempData(14); 
			$cat_filt_balance=$catfilter_finance_arr[14]['balance'];

			$cattext_finance_arr = $finance_obj->getFinanceTempData(15); 
			$cat_text_balance=$cattext_finance_arr[15]['balance'];
			
			
			$sqlDelCatspon = "UPDATE tbl_temp_flow_status set catspon=0,catfilter=0,cattext=0  where  parentid = '".$this->parentid."'";
			$resRetcatspon = $this->conn_temp->query_sql($sqlDelCatspon,$this->parentid);

			$sqlDelCatspon = "DELETE FROM tbl_catspon_temp WHERE parentid = '".$this->parentid."'";
			$resRetcatspon = $this->conn_temp->query_sql($sqlDelCatspon,$this->parentid);
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete Query Main To temp==>".$sqlDelCatspon);
			$sqlDelCatfilt = "DELETE FROM tbl_catfilter_temp WHERE parentid = '".$this->parentid."'";
			$resDelCatfilt = $this->conn_temp->query_sql($sqlDelCatfilt,$this->parentid);	
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Delete Query Main To temp==>".$sqlDelCatfilt);
			
			/*checking for the entry in shadow table -- starts*/
			if(strtolower($this->module) == 'cs'){
				$sqlGetSponCount = "SELECT COUNT(parentid) AS count_parent FROM tbl_catspon_shadow WHERE parentid = '".$this->parentid."' AND campaign_type=1";
				$qryGetSponCount = $this->conn_temp->query_sql($sqlGetSponCount);
				if($qryGetSponCount){
					$rowGetSponCount = mysql_fetch_assoc($qryGetSponCount);
					if($rowGetSponCount['count_parent']>0){
						$strSpon = "_shadow";
					}
				}
				$sqlGetTextCount = "SELECT COUNT(parentid) AS count_parent FROM tbl_catspon_shadow WHERE parentid = '".$this->parentid."' AND campaign_type=3";
				$qryGetTextCount = $this->conn_temp->query_sql($sqlGetTextCount);
				if($qryGetTextCount){
					$rowGetTextCount = mysql_fetch_assoc($qryGetTextCount);
					if($rowGetTextCount['count_parent']>0){
						$strText = "_shadow";
					}
				}
				
				$sqlGetFilterCount = "SELECT COUNT(parentid) AS count_parent FROM tbl_catfilter_shadow WHERE parentid = '".$this->parentid."'";
				$qryGetFilterCount = $this->conn_temp->query_sql($sqlGetFilterCount);
				if($qryGetFilterCount){
					$rowGetFilterCount = mysql_fetch_assoc($qryGetFilterCount);
					if($rowGetFilterCount['count_parent']>0){
						$strFilter = "_shadow";
					}
				}
			}
			/*checking for the entry in shadow table -- ends*/
			$sqlCatSponGet =  "SELECT * FROM tbl_catspon{$strSpon} WHERE parentid = '".$this->parentid."' AND campaign_type = 1";
			if(strtolower($this->module) == 'me'){
				$qryCatSponGet 	=  $this->conn_decs->query_sql($sqlCatSponGet);
			}else{
				$qryCatSponGet 	=  $this->conn_local->query_sql($sqlCatSponGet);
			}
			if($qryCatSponGet  && mysql_num_rows($qryCatSponGet)){
				$insertIntotemp ="INSERT  INTO  tbl_catspon_temp(parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_type,iscalculated,campaign_name,inventory,national_catid,banner_camp,parentname) VALUES ";
				while($rowCatSponGet = mysql_fetch_array($qryCatSponGet)){
					$insertIntotemp.= "('".$rowCatSponGet['parentid']."','".$rowCatSponGet['budget']."','".$rowCatSponGet['update_date']."','".$rowCatSponGet['cat_name']."','".$rowCatSponGet['catid']."','".$rowCatSponGet['tenure']."','".$rowCatSponGet['start_date']."','".$rowCatSponGet['end_date']."','".$rowCatSponGet['bid_per_day']."','".$rowCatSponGet['variable_budget']."','".$rowCatSponGet['campaign_type']."','".$rowCatSponGet['iscalculated']."','".$rowCatSponGet['campaign_name']."','".$rowCatSponGet['inventory']."','".$rowCatSponGet['national_catid']."','".$rowCatSponGet['banner_camp']."','".$rowCatSponGet['parentname']."'),";
					
					if(!isset($_SESSSION['doctor_package'])){
						if($rowCatSponGet['banner_camp'] == 2){
							$_SESSION['doctor_package'] = 1;
						}else if($rowCatSponGet['banner_camp'] == 1 || $rowCatSponGet['banner_camp'] == 0){
							$_SESSION['doctor_package'] = 2;
						}
					}
				}
				$insertIntotemp = trim($insertIntotemp,",");
				$this->conn_temp->query_sql($insertIntotemp,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Query Main To temp==>".$insertIntotemp);
			}
			
			$sqlCatTextGet =  "SELECT * FROM tbl_catspon{$strText} WHERE parentid = '".$this->parentid."' AND campaign_type = 3";
			if(strtolower($this->module) == 'me'){
				$qryCatTextGet 	=  $this->conn_decs->query_sql($sqlCatTextGet);
			}else{
				$qryCatTextGet 	=  $this->conn_local->query_sql($sqlCatTextGet);
			}
			if($qryCatTextGet  && mysql_num_rows($qryCatTextGet) ){
				$insertIntotemp ="INSERT  INTO  tbl_catspon_temp(parentid,budget,update_date,cat_name,catid,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_type,iscalculated,campaign_name,inventory,national_catid) VALUES ";
				while($rowCatTextGet = mysql_fetch_array($qryCatTextGet)){
					$insertIntotemp.= "('".$rowCatTextGet['parentid']."','".$rowCatTextGet['budget']."','".$rowCatTextGet['update_date']."','".$rowCatTextGet['cat_name']."','".$rowCatTextGet['catid']."','".$rowCatTextGet['tenure']."','".$rowCatTextGet['start_date']."','".$rowCatTextGet['end_date']."','".$rowCatTextGet['bid_per_day']."','".$rowCatTextGet['variable_budget']."','".$rowCatTextGet['campaign_type']."','".$rowCatTextGet['iscalculated']."','".$rowCatTextGet['campaign_name']."','".$rowCatTextGet['inventory']."','".$rowCatTextGet['national_catid']."'),";
				}
				$insertIntotemp = trim($insertIntotemp,",");
				$this->conn_temp->query_sql($insertIntotemp,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Query Main To temp==>".$insertIntotemp);
			}
			
			$sqlFilterGet =  "SELECT * FROM tbl_catfilter{$strFilter} WHERE parentid = '".$this->parentid."' AND campaign_type = 2";
			if(strtolower($this->module) == 'me'){
				$qryFilterGet 	=  $this->conn_decs->query_sql($sqlFilterGet);
			}else{
				$qryFilterGet 	=  $this->conn_local->query_sql($sqlFilterGet);
			}
			if($qryFilterGet && mysql_num_rows($qryFilterGet)){
				$insertIntotemp ="INSERT  INTO  tbl_catfilter_temp(parentid,budget,update_date,title_name,title_id,tenure,start_date,end_date,bid_per_day,variable_budget,campaign_type,iscalculated,campaign_name,inventory) VALUES ";
				while($rowFilterGet = mysql_fetch_array($qryFilterGet)){
					$insertIntotemp.= "('".$rowFilterGet['parentid']."','".$rowFilterGet['budget']."','".$rowFilterGet['update_date']."','".$rowFilterGet['title_name']."','".$rowFilterGet['title_id']."','".$rowFilterGet['tenure']."','".$rowFilterGet['start_date']."','".$rowFilterGet['end_date']."','".$rowFilterGet['bid_per_day']."','".$rowFilterGet['variable_budget']."','".$rowFilterGet['campaign_type']."','".$rowFilterGet['iscalculated']."','".$rowFilterGet['campaign_name']."','".$rowFilterGet['inventory']."'),";
				}
				$insertIntotemp = trim($insertIntotemp,",");
				$this->conn_temp->query_sql($insertIntotemp,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Insert Query Main To temp==>".$insertIntotemp);
			}
			
			$sql_sum = "SELECT SUM(variable_budget) AS budget,MAX(tenure) AS tenure, campaign_type FROM tbl_catspon_temp WHERE parentid='".$this->parentid."' GROUP BY campaign_type";
			$res_sum = $this->conn_temp->query_sql($sql_sum);
			if($res_sum && mysql_num_rows($res_sum))
			{
			  $banner_budget_balance_arr = array();
			  while($row_sum = mysql_fetch_assoc($res_sum))
			  {
				$banner_budget_balance_arr[$row_sum['campaign_type']]['budget'] = $row_sum['budget'];
				 switch($row_sum['campaign_type'])
				 {
					case 1:
					if(intval($cat_spon_balance)>0){
						$banner_budget_balance_arr[$row_sum['campaign_type']]['balance'] = $cat_spon_balance;
						$banner_budget_balance_arr[$row_sum['campaign_type']]['duration'] = floor($cat_spon_balance/$catspon_finance_arr[13]['daily_threshold']);
					}else{
						$banner_budget_balance_arr[$row_sum['campaign_type']]['balance'] = $row_sum['budget'];
						$banner_budget_balance_arr[$row_sum['campaign_type']]['duration'] = $row_sum['tenure'];
					}
					break;
					case 3:
					if($cat_text_balance>0){
						$banner_budget_balance_arr[$row_sum['campaign_type']]['balance'] = $cat_text_balance;
						$banner_budget_balance_arr[$row_sum['campaign_type']]['duration'] = floor($cat_text_balance/$cattext_finance_arr[15]['daily_threshold']);
					}else{
						$banner_budget_balance_arr[$row_sum['campaign_type']]['balance'] = $row_sum['budget'];
						$banner_budget_balance_arr[$row_sum['campaign_type']]['duration'] = $row_sum['tenure'];
					}
					break;
					default:
					die('Invalid banner type - getcontractdata');
				 } 
			  }
			}	

			########################################Added by dev#########################
		
			if (count($banner_budget_balance_arr)>0)
			{
				foreach($banner_budget_balance_arr as $banner_type => $banner_budget_balance)
				{

					$sql_upt = "UPDATE tbl_catspon_temp set variable_budget = '".$banner_budget_balance['balance']."' * (variable_budget/'".$banner_budget_balance['budget']."'),tenure='".floor($banner_budget_balance['duration'])."' WHERE parentid='".$this->parentid."' AND campaign_type='".$banner_type."'";
					$res_upt = $this->conn_temp->query_sql($sql_upt,$this->parentid);
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Query Main To temp==>".$sql_upt);
				}
			}
			
			$sql_sum = "SELECT SUM(variable_budget) AS budget,MAX(tenure) AS tenure,campaign_type FROM tbl_catfilter_temp WHERE parentid='".$this->parentid."' GROUP BY campaign_type"; //exit;
			$res_sum = $this->conn_temp->query_sql($sql_sum);
			if($res_sum && mysql_num_rows($res_sum))
			{
				$row_sum = mysql_fetch_assoc($res_sum);
				if($cat_filt_balance<=0){
					$cat_filt_balance=$row_sum['budget'];
					$cat_filt_tanure=$row_sum['tenure'];
				}else{
					$cat_filt_tanure=floor($cat_filt_balance/$catfilter_finance_arr[14]['daily_threshold']);
				}
				
				$sql_upt = "UPDATE tbl_catfilter_temp SET variable_budget = '".$cat_filt_balance."' * (variable_budget/".$row_sum['budget']."),tenure='".$cat_filt_tanure."' WHERE parentid='".$this->parentid."' AND campaign_type='".$row_sum['campaign_type']."'";
				
				$res_upt = $this->conn_temp->query_sql($sql_upt,$this->parentid);
				$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Update Query Main To temp==>".$sql_upt);
			}
			
			if(strtolower($this->module) != 'cs'){
				return $_SESSION['doctor_package'];
			}
		}
		
		function insertTimeLog_temp ($time, $date, $lineno, $parentid ,$message){
		
			if(!defined("REMOTE_CITY_MODULE"))
			{
			 $sNamePrefix = '../logs/log_flow/categorybanner'.$parentid.'.txt';
			 $pathToLog = dirname($sNamePrefix);
				if (!file_exists($pathToLog)) 
				{
					mkdir($pathToLog, 0755, true);
				}
			 $fp = fopen('../logs/log_flow/categorybanner'.$parentid.'.txt', 'a');
			 $string="For Parentid :".$parentid." [  Line No :- ".$lineno." Date Time".$date."--".$time." -- ".$message."]\n";
			 fwrite($fp,$string );
			 fclose($fp);        
			}
		}

        function getCatCompany($catid)
        {
            $company_present = false;

	
			$sql_company_count = "SELECT x1.sphinx_id, x1.parentid, y1.catidlineage, x1.data_city, y1.mask, y1.freeze 
								  FROM   tbl_companymaster_search x1, tbl_companymaster_extradetails y1
								  WHERE  MATCH(x1.catidlineage_search) AGAINST('".$catid."' IN BOOLEAN MODE)
								  AND x1.parentid = y1.parentid 
								  AND x1.data_city='".$this->data_city."'
								  AND  y1.mask=0 AND  y1.freeze =0
								  LIMIT 10 ";
			$res_company_count = $this->conn_iro_slave->query_sql($sql_company_count);

            if($res_company_count && mysql_num_rows($res_company_count)>0)
            {
                $company_present = true;
            }
            return $company_present;
        }
		
		function setNewParent($pid){
			if($this->parentid != $pid){
				$this->parentid = $pid;
				$this->setDataCity();
			}
		}
		
		function getApprDataCity()
		{
			if($this->data_city == '')
				return false;
			else
				return true;
		}
		
		function categorycheck($conn_catspon,$conn_category,$retVal=''){
			
			$delarr= array();
			$count = 0;
			if($retVal == 'catid')
				$field = array('catid');
			else
				$field = array('catid','cat_name');
				
			$table = 'tbl_catspon_temp';
			
			$get_arr	= $this->showSelected($conn_catspon,$field,$table,$this->campType);
			
			foreach($get_arr as $catarr){
				if($retVal == 'catid')
					$categoryarr[] = $catarr['catid'];
				else
					$categoryarr[$catarr['catid']] = $catarr['cat_name'];
			}
			
			if(count($categoryarr)){
				if($retVal == 'catid')
					$catidstr	= implode(",",$categoryarr);
				else
					$catidstr	= implode(",",array_keys($categoryarr));
			
				$sql	= "SELECT catid,paid_clients,nonpaid_clients,category_name FROM tbl_categorymaster_generalinfo WHERE catid in (".$catidstr.")";
				$qry	= $conn_category->query_sql($sql);
				
				if($qry && mysql_num_rows($qry)){
					while($row	= mysql_fetch_assoc($qry)){
						if($row['paid_clients'] == '0' && $row['nonpaid_clients'] == '0'){
							if($retVal == 'catid')
								$delarr[] = $row;
							else
								$delarr[$row['catid']] = $categoryarr[$row['catid']];
							++$count;
						}
					}
				}
			}
			$delarr['count'] = $count;
			return $delarr;
		}
		
		function set_top_flg(){
			
			$field		= array("catid");
			$table		= "tbl_catspon_temp";
			$camp_type	= '1';
			
			$catarr	 = $this->showSelected($this->conn_temp,$field,$table,$camp_type);
			if(count($catarr) > 0){
				foreach($catarr as $catidarr){
					$temparr[] = $catidarr['catid'];
				}
				
				$sql = "SELECT catid,top_category_flag,1  AS partial_inventory FROM tbl_categorymaster_generalinfo WHERE catid IN (".implode(",",$temparr).")";
				$qry = $this->conn_local->query_sql($sql);
				if($qry){
					while($row = mysql_fetch_assoc($qry)){
						$cattype_arr[$row['catid']] = $row['partial_inventory'];
					}
				}
			}
			return $cattype_arr;
		}
		
		function getMinMonthlyBudget(){
			$min_budget = '';
			$sql = "SELECT banner_budget FROM tbl_business_uploadrates WHERE city = '".$this->data_city."'";
			$qry = $this->conn_local->query_sql($sql);
			if($qry && mysql_num_rows($qry)){
				$row		= mysql_fetch_assoc($qry);
				$min_budget	= $row['banner_budget'];
			}
			return $min_budget;
		}
		
		function getMinBudget(){
			return $this->min_budget;
		}
		
		function getClients($catid){
			$pidarr	= array();
			if(trim($catid)!=''){
				$sql = "SELECT parentid FROM tbl_catspon WHERE catid = '".$catid."'";
				$qry = $this->conn_server_decs->query_sql($sql);
				if($qry && mysql_num_rows($qry)){
					while($row 	= mysql_fetch_assoc($qry)){
						$pidarr[]	= $row['parentid'];
					}
				}
				
				if(count($pidarr)>0){
					$getarr	= $this->getCompanyName($pidarr);
				}
			}
			return $getarr;
		}
		
		function sameVersionInstApp(){
			
			$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Approval for same version of the instrument started");
			$campaignarr	= array("13","14","15");
			foreach($campaignarr as $campid){
				$this->campType	= $campid;
				$getArr	= array("catid","inventory","national_catid");
				$catid = $this->showSelected($this->conn_local,$getArr,"tbl_catspon",'1');
				foreach($catid as $details){
					$this -> insertTimeLog_temp(date('H:i:s'),date('Y-m-d'),__LINE__,$this -> parentid,"Catid = ".$details['catid']." Inventory = ".$details['inventory']);
					if($details['inventory'] > 0 && $details['catid']!=''){
						$this->sponTextInvMgmt($details['catid'],2,$details['inventory'],$this->campType,$details['national_catid']);
					}
				}
			}
		}
		
		function __destruct(){/* Destructor Closing connection and releasing all variables */
			
			$this->parentid 	= null;
			$this->module		= null;
			$this->data_city	= null;
			$this->conn_temp	= null;
			$this->conn_main	= null;
			$this->conn_server	= null;
			$this->conn_decs	= null;
			$this->conn_fnc		= null;
		}
	}
?>
