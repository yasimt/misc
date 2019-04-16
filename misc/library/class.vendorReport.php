<?php
if(!defined('APP_PATH'))
{
	require_once("config.php");
}
include_once(APP_PATH."library/path.php");
include_once(APP_PATH."library/common_api.php");
class videoVendor
{
	function __construct($dbarr)
	{
		$this->conn_finance     = new DB($dbarr['FINANCE']);
		$this->conn_finance_slave     = new DB($dbarr['FINANCE_SLAVE']);
		$this->conn_decs     	= new DB($dbarr['DB_DECS']);
		$this->conn_iro	= new DB($dbarr['DB_IRO']);
		$this->conn_idc	= new DB($dbarr['IDC']);
		$this->log_path = APP_PATH . 'logs/videoShootLogs/';
		$this->data_city_arr = array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI','JAIPUR','CHANDIGARH','COIMBATORE');
		$getIp = explode(".",$dbarr['FINANCE'][0]);
		$city_ip_array = array("0"=>'MUMBAI',"8"=>'DELHI',"16"=>'KOLKATA',"26"=>'BANGALORE',"32"=>'CHENNAI',"40"=>'PUNE',"50"=>'HYDERABAD',"56"=>'AHMEDABAD',"17"=>'REMOTE_CITIES',"17"=>'REMOTE_CITIES',"192.168.17.171"=>'REMOTE_CITIES',"64"=>'MUMBAI',"6"=>'REMOTE_CITIES');
        if(array_key_exists($getIp[2], $city_ip_array))
        {
            $this->server_city = $city_ip_array[$getIp[2]];
        }
	}

	public function getVendorList($datacity,$vendorid,$zoneid,$vstatus=0,$start,$end)
	{
		$condition= '';
		if($vendorid!=''){
			$condition .= " AND vendor_id= '".$vendorid."'";
		}
		if($zoneid!=''){
			$condition .= " AND (FIND_IN_SET('".$zoneid."',service_area) OR FIND_IN_SET('".$zoneid."',retention_area) OR FIND_IN_SET('".$zoneid."',doctor_area))";
		}
		
		if(intval($vstatus)>0){
			if(intval($vstatus)==1){
				$condition .= " AND active_vendor = 1 ";
			}elseif(intval($vstatus)==2){
				$condition .= " AND active_vendor = 0 ";
			}
		}
		
		if(intval($start)>0 && intval($end)>0){
			$limitCondition = "limit ".$start.",".$end;
		}else{
			$limitCondition = "";
		}
		$qryGetVendorList = "select vendor_name,vendor_id,contact_person,address,full_address,landline,mobile,stdcode,email,website,service_area,camera_person,editor_capacity,min_allocate_cap,temp_allocate_cap,retention_area,doctor_area,accountNo,IFSC,bankBranch,data_city,active_vendor,vendor_type from tbl_vendor_information where data_city ='".$datacity."' ".$condition." order by data_city ".$limitCondition;
		$resGetVendorList = $this->conn_finance_slave->query_sql($qryGetVendorList);
		if($resGetVendorList){
			if(mysql_num_rows($resGetVendorList)>0){
				$i=0;
				while($rowGetVendorList = mysql_fetch_array($resGetVendorList,MYSQL_ASSOC)){
					$result['vendor_list'][$i] = $rowGetVendorList;
					$i++;
				}
				return $this->json($result);
			}else{
				$error = array('status' => "Failed", "msg" => "No record found");
				return $this->json($error);
			}
		}else{
			$error = array('status' => "Failed", "msg" => "Query failed");
			return $this->json($error);
		}
	}

	public function vendorInfo($vendorid){
		if(!empty($vendorid)){
			$qryGetVendorInfo = "select vendor_name,vendor_id,contact_person,address,state,city,area,landline,mobile,stdcode,email,website,service_area,camera_person,editor_capacity,primary_emailid,min_allocate_cap,retention_area,temp_allocate_cap,accountNo,IFSC,bankBranch,data_city,vendor_type from tbl_vendor_information where vendor_id='".$vendorid."'";
			$resGetVendorInfo = $this->conn_finance_slave->query_sql($qryGetVendorInfo);
			if($resGetVendorInfo && mysql_num_rows($resGetVendorInfo)>0){
				$rowGetVendorInfo = mysql_fetch_array($resGetVendorInfo,MYSQL_ASSOC);
				$result['vendor_info'][0]= $rowGetVendorInfo;
				return $this->json($result);
			}else{
				$error = array('status' => "Failed", "msg" => "No record");
				return $this->json($error);
			}
		}else{
			$error = array('status' => "Failed", "msg" => "Invalid Vendor id");
			return $this->json($error);
		}
	}

	public function getStateid($country_id,$stateName){
		if(!empty($stateName)){
			$qryGetStateid = "select st_name,state_id from state_master where country_id=".$country_id." and st_name='".$stateName."' order by st_name limit 1";
			$resGetStateid = $this->conn_decs->query_sql($qryGetStateid);
			if($resGetStateid && mysql_num_rows($resGetStateid)>0){
				$rowGetStateid = mysql_fetch_array($resGetStateid,MYSQL_ASSOC);
				$result['state_info'][0]= $rowGetStateid;
				return $this->json($result);
			}else{
				$error = array('status' => "Failed", "msg" => "No record");
				return $this->json($error);
			}
		}else{
			$error = array('status' => "Failed", "msg" => "Invalid state name");
			return $this->json($error);
		}
	}

	public function getCityid($country_id,$stateId,$cityNm){
		if(!empty($stateId) && !empty($cityNm)){
			$qryGetCityid = "select ct_name,city_id from city_master where state_id=".$stateId." and country_id=".$country_id." order by ct_name";
			$resGetCityid = $this->conn_decs->query_sql($qryGetCityid);
			if($resGetCityid && mysql_num_rows($resGetCityid)>0){
				$rowGetCityid = mysql_fetch_array($resGetCityid,MYSQL_ASSOC);
				$result['city_info'][0]= $rowGetCityid;
				return $this->json($result);
			}else{
				$error = array('status' => "Failed", "msg" => "No record");
				return $this->json($error);
			}
		}else{
			$error = array('status' => "Failed", "msg" => "Invalid state or city name");
			return $this->json($error);
		}
	}

	function GetCitywiseVendor($datacity,$maincity){
		if(!empty($maincity)){
			if(trim($datacity)!=''){
				$qryGetCitywiseVendor = "SELECT vendor_name,vendor_id FROM tbl_vendor_information WHERE data_city ='".$datacity."' AND active_vendor = 1 /*AND temp_allocate_cap>0*/ ORDER BY min_allocate_cap DESC ";
				$resGetCitywiseVendor = $this->conn_finance_slave->query_sql($qryGetCitywiseVendor);
				if($resGetCitywiseVendor){
					if(mysql_num_rows($resGetCitywiseVendor)>0){
						$j=0;
						while($rowGetCitywiseVendor = mysql_fetch_array($resGetCitywiseVendor,MYSQL_ASSOC)){
							$result['list_vendor'][$j] = $rowGetCitywiseVendor;
							$j++;
						}
						return $this->json($result);
					}else{
						$error = array('status' => "Failed", "msg" => "No record");
						return $this->json($error);
					}
				}else{
					$error = array('status' => "Failed", "msg" => "Query failed");
					return $this->json($error);
				}
			}
		}else{
			$error = array('status' => "Failed", "msg" => "Not found datacity");
			return $this->json($error);
		}
	}

	function checkSameVendorAssign($datacity,$maincity,$pid,$assignV,$version){
		$assignSame = 0;
		if(!empty($maincity) && !empty($assignV) && !empty($pid)){
			if(trim($datacity)!=''){
				$qryCheckVn = "select vendor_id from tbl_vendor_mapped where parentid='".$pid."' and vendor_id ='".$assignV."' and version ='".$version."'";
				$resCheckVn = $this->conn_finance_slave->query_sql($qryCheckVn);
				if($resCheckVn && mysql_num_rows($resCheckVn)>0){
					$assignSame = 1;
				}
			}
		}
	}

	function assignVendorApproval($parentid,$version,$usercode,$processName,$compmaster_obj){
		$allocatedVendorID = '';
		$sendemail = false;
		$this->processName = $processName;
		$this->usercode = $usercode;
		$videoshootDatacity = '';
		$module ='';
		$firstApproval = false;
		$videoShootValidCity = false;
		$videoshootCampaign =  false;
		$remoteCityIdentifier = 0;
		if(!defined('REMOTE_CITY_MODULE')){
			$remoteCityIdentifier = 0;
		}else{
			$remoteCityIdentifier = 1;
		}/* $remoteCityIdentifier == 0 its maincity and $remoteCityIdentifier ==1 its remotecity module*/
		$extra_str= "[Parentid : ".$parentid."][usercode :".$this->usercode."][remotecity identyfier : ".$remoteCityIdentifier."]";
		$this->logmsgvideoshootmail("Assign video vendor process start. ",$this->log_path,$parentid,$extra_str,$this->processName);

		$module = $this->getModule($parentid,$version);

		$extra_str= "[Parentid : ".$parentid."][version : ".$version."][module : ".$module."]";
		$this->logmsgvideoshootmail("Get Module information. ",$this->log_path,$parentid,$extra_str,$this->processName);

		$liquorCatFlag = $this->checkLiquorCategory($parentid,$compmaster_obj);
		
		if(!$liquorCatFlag){
			$extra_str= "[block for videoshoot category flag :".intval($liquorCatFlag)."][Contract not have block for videoshoot category(liquor category)]";
			$this->logmsgvideoshootmail("Check category block for Video shoot.",$this->log_path,$parentid,$extra_str,$this->processName);

			$firstApproval = $this->checkFirstTimeApproval($parentid,$version);
			if($firstApproval){
				$extra_str= "[Approval flag :".intval($firstApproval)."][Video vendor is not already assign]";
				$this->logmsgvideoshootmail("Check video vendor already assign. ",$this->log_path,$parentid,$extra_str,$this->processName);

				$videoShootValidCityjson = $this->getDatacityValidation($parentid,$compmaster_obj);
				$videoShootValidCityjsonArr = json_decode($videoShootValidCityjson,true);
				if($videoShootValidCityjsonArr['datacity_list'][0]['datacityFlag']==1){
					$videoShootValidCity = true;
					$videoshootDatacity = $videoShootValidCityjsonArr['datacity_list'][0]['datacityName'];
				}
				$extra_str= "[Data city Information :".$videoShootValidCityjson."][Data city :".$videoShootValidCityjsonArr['datacity_list'][0]['datacityName']."][Data city Flag :".$videoShootValidCityjsonArr['datacity_list'][0]['datacityFlag']."][videoShootValidCity : ".intval($videoShootValidCity)."]";
				$this->logmsgvideoshootmail("Datacity Information.",$this->log_path,$parentid,$extra_str,$this->processName);
				//$videoShootValidCity = true;

				if($videoShootValidCity){
					$extra_str= "[valid datacity flag :".intval($videoShootValidCity)."][Datacity is valid]";
					$this->logmsgvideoshootmail("Check valid data city.",$this->log_path,$parentid,$extra_str,$this->processName);

					$videoshootCampaign = $this->checkVideoshootCampaign($parentid,$version);
					//$videoshootCampaign = true;
					if($videoshootCampaign){
						$assignVid = '';
						$extra_str= "[videoshoot campaign flag :".intval($videoshootCampaign)."][Contract have videoshoot campaign]";
						$this->logmsgvideoshootmail("Check contract have videoshoot campaign.",$this->log_path,$parentid,$extra_str,$this->processName);
						if($videoshootDatacity!=''){
							$checkVideoUploaded = $this->CheckVideoUploaded($parentid,$videoshootDatacity);
							if($checkVideoUploaded){

								$extra_str= "[Video is not uploaded so send mail to video vendor.][send mail flag : ".$checkVideoUploaded."]";
								$this->logmsgvideoshootmail("Check video is uploaded.",$this->log_path,$parentid,$extra_str,$this->processName);

								$previousVendor = $this->checkPreviousVendor($parentid);
								$extra_str= "[previous video vendor :".$previousVendor."]";
								$this->logmsgvideoshootmail("Check previouse assign video vendor.",$this->log_path,$parentid,$extra_str,$this->processName);
								
								$checkretentionVendor = $this->checkRentntion($parentid);
								
								if($checkretentionVendor){
									$extra_str= "[Retention vendor flag :".$checkretentionVendor."][Vendor is assign through retention module][Retention Vendor : ".$this->vendorUploadDetail['assigned_to']."][Approved Video Flag : ".intval($this->vendorUploadDetail['approve_video'])."][Multiple Vendor flag : ".intval($this->multipleAllocateVendor)."]";
									$this->logmsgvideoshootmail("Check retention assign video vendor.",$this->log_path,$parentid,$extra_str,$this->processName);
									
									if($this->vendorUploadDetail['approve_video']=='1'){
										$sendemail = true;
										if($previousVendor!='' && $this->vendorUploadDetail['assigned_to']!='' && trim($previousVendor)==trim($this->vendorUploadDetail['assigned_to'])){
											$allocatedVendorID = $previousVendor;
											
											$extra_str= "[Retention Vendor : ".$this->vendorUploadDetail['assigned_to']."][Mapped previous Vendor : ".$previousVendor."][Allocated Vendor Id : ".$allocatedVendorID."][Both vendor are same]";
											$this->logmsgvideoshootmail("Check retention vendor and mapped vendor for approved video.",$this->log_path,$parentid,$extra_str,$this->processName);
										}else{
											if(trim($this->vendorUploadDetail['assigned_to'])!=''){
												$allocatedVendorID = trim($this->vendorUploadDetail['assigned_to']);
											}elseif(trim($previousVendor)!=''){
												$allocatedVendorID = $previousVendor;
											}
											
											$extra_str= "[Retention Vendor : ".$this->vendorUploadDetail['assigned_to']."][Mapped previous Vendor : ".$previousVendor."][Allocated Vendor Id : ".$allocatedVendorID."][Both vendor are different ,so allocate vendor shoudle laste video uploaded vendor]";
											$this->logmsgvideoshootmail("Check retention vendor and mapped vendor for approved video.",$this->log_path,$parentid,$extra_str,$this->processName);
										}
									}else{
										if(trim($this->vendorUploadDetail['assigned_to'])!='' && intval($this->vendorUploadDetail['approve_video'])=='0'){
											$allocatedVendorID = trim($this->vendorUploadDetail['assigned_to']);
											$sendemail = true;
											$extra_str= "[Retention vendor assign but video not approved so assign same vendor to retention vendor][Allocated Vendor Id : ".$allocatedVendorID."]";
											$this->logmsgvideoshootmail("Retention vendor assign but video not approved",$this->log_path,$parentid,$extra_str,$this->processName);
										}else{
											$extra_str= "[Multiple vendor assign Flag :".$this->multipleAllocateVendor."]";
											$this->logmsgvideoshootmail("Vendor is not allocate because multiple vendor already assign.",$this->log_path,$parentid,$extra_str,$this->processName);
										}
									}
								}else{
									$sendemail = true;
									$extra_str= "[Retention vendor flag :".$checkretentionVendor."][Vendor is not assign through retention module]";
									$this->logmsgvideoshootmail("Check retention assign video vendor.",$this->log_path,$parentid,$extra_str,$this->processName);
								}
								
								/*if($previousVendor!=''){
									$checkPincodeVid = $this->checkPincode($parentid,$videoshootDatacity,$previousVendor,$compmaster_obj);
									$extra_str= "[pincode wise video vendor id :".$checkPincodeVid."]";
									$this->logmsgvideoshootmail("Check pincode wise vendor id.",$this->log_path,$parentid,$extra_str,$this->processName);
								}else{
									$extra_str= "[previous vendor assign flag :".$previousVendor."]";
									$this->logmsgvideoshootmail("Previous vendor is npt assign.",$this->log_path,$parentid,$extra_str,$this->processName);
								}

								if(trim($previousVendor)==trim($checkPincodeVid) && trim($previousVendor!='')){
									$assignVid = trim($previousVendor);
									$extra_str= "[vendor id :".$assignVid."]";
									$this->logmsgvideoshootmail("Function call with videovendorid.",$this->log_path,$parentid,$extra_str,$this->processName);
									$api_responce = $this->callVendorassignFn($parentid,$version,$videoshootDatacity,$remoteCityIdentifier,$this->processName,$module,$assignVid);
								}else{
									$assignVid = '';
									$extra_str= "[vendor id :".$assignVid."]";
									$this->logmsgvideoshootmail("Function call without videovendorid.",$this->log_path,$parentid,$extra_str,$this->processName);
									$api_responce = $this->callVendorassignFn($parentid,$version,$videoshootDatacity,$remoteCityIdentifier,$this->processName,$module);
								}*/

								if($sendemail){
									if($allocatedVendorID!=''){
										$extra_str= "[vendor id :".$allocatedVendorID."]";
										$this->logmsgvideoshootmail("Function call with videovendorid.",$this->log_path,$parentid,$extra_str,$this->processName);
										$api_responce = $this->callVendorassignFn($parentid,$version,$videoshootDatacity,$remoteCityIdentifier,$this->processName,$module,$allocatedVendorID);
									}else{
										$extra_str= "[vendor id :".$allocatedVendorID."]";
										$this->logmsgvideoshootmail("Function call without videovendorid.",$this->log_path,$parentid,$extra_str,$this->processName);
										$api_responce = $this->callVendorassignFn($parentid,$version,$videoshootDatacity,$remoteCityIdentifier,$this->processName,$module);
									}
									$api_responceArr = json_decode($api_responce,true);
									if($api_responceArr['vendor']['code']==1){
										$extra_str= "[Mail send code: ".$api_responceArr['vendor']['code']."][Mail send status : ".$api_responceArr['vendor']['status']."][Mail end Msg :".$api_responceArr['vendor']['vendor msg']."][Mail send suceessfully]";
										$this->logmsgvideoshootmail("Check video vendor mail send.",$this->log_path,$parentid,$extra_str,$this->processName);
										if (array_key_exists('vendor', $api_responceArr)) {
											if($api_responceArr['vendor']['vendor Id']!='' && $api_responceArr['vendor']['code']==1){
												$qryInsertVendorMapp = "INSERT INTO tbl_vendor_mapped (parentid,vendor_id,data_city,version,insert_date) VALUES ('".$parentid."','".$api_responceArr['vendor']['vendor Id']."','".DATA_CITY."','".$version."','".date('Y-m-d H:i:s')."')";
												$resInsertVendorMapp = $this->conn_finance->query_sql($qryInsertVendorMapp);

												$extra_str= "[Query : ".$qryInsertVendorMapp."][Result : ".$resInsertVendorMapp."][Data successfuly insert in Mapped table]";
												$this->logmsgvideoshootmail("Insert data in mapped table.",$this->log_path,$parentid,$extra_str,$this->processName);
											/* INSERT INTO tbl_vendor_upload_details -- STARTS*/
												$sqldatacheck	= "SELECT parentid,assigned_to FROM login_details.tbl_vendor_upload_details WHERE parentid='".$parentid."' AND data_type IN (1,2)";
												$qrydatacheck	= $this->conn_idc->query_sql($sqldatacheck);
												$extra_str	=	"[Checking count :".mysql_num_rows($qrydatacheck)."][Result : ".$resInsertVendorMapp."][Data successfuly insert in Mapped table]";
												$this->logmsgvideoshootmail("Vendor_upload details check",$this->log_path,$parentid,$extra_str,$this->processName);
												if($qrydatacheck){
													$rowdatacheck	= mysql_fetch_assoc($qrydatacheck);
													if($rowdatacheck['parentid'] == $parentid && $rowdatacheck['assigned_to'] != $api_responceArr['vendor']['vendor Id'] || mysql_num_rows($qrydatacheck) == 0){
														$qry_details = array();
														$field		 = "parentid,companyname,full_address,area,landline,mobile,email,contact_person, data_city";
														$where		 = "parentid = '".$parentid."'";
														$qry_details = $compmaster_obj->getRow($field,"tbl_companymaster_generalinfo",$where);

														if($qry_details['numrows']){
															$row_details	= $qry_details['data']['0'];
															$sql_insert	= "INSERT INTO login_details.tbl_vendor_upload_details SET
																			parentid		= '".$row_details['parentid']."',
																			companyname		= '".addslashes($row_details['companyname'])."',
																			landline		= '".$row_details['landline']."',
																			mobile			= '".$row_details['mobile']."',
																			full_address	= '".addslashes($row_details['full_address'])."',
																			area			= '".addslashes($row_details['area'])."',
																			email			= '".$row_details['email']."',
																			contact_person	= '".$row_details['contact_person']."',
																			city			= '".$row_details['data_city']."',
																			module			= '".$module."',
																			updatedate		= '".date("y-m-d H:i:s")."',
																			createdby		= '".$this->usercode."',
																			assigned_to		= '".$api_responceArr['vendor']['vendor Id']."',
																			data_type		= '1'";
															$qry_insert	= $this->conn_idc->query_sql($sql_insert);

															$extra_str= "[Query : ".$sql_insert."][Result : ".$qry_insert."][Data successfuly insert in tbl_vendor_upload_details table]";
															$this->logmsgvideoshootmail("Insert data in tbl_vendor_upload_details table.",$this->log_path,$parentid,$extra_str,$this->processName);
														/* Saving history for Vendor -- STARTS*/
															$processname_arr	= array('Manual Approval','single cheque approval','ECS-CLEARANCE','Auto Approval');
															if(in_array($this->processName,$processname_arr)){
																$sql_history = "INSERT INTO vlc_history SET
																				parentid	= '".$row_details['parentid']."',
																				vendor		= '".$api_responceArr['vendor']['vendor Id']."',
																				userid		= '0000',
																				data_city	= '".$row_details['data_city']."',
																				dept		= 'CS',
																				date_alloc	= '".date("Y-m-d H:i:s")."',
																				remainder	= '0'";
																$qry_history = $this->conn_decs->query_sql($sql_history);
																$extra_str= "[Query : ".$sql_history."][Result : ".$qry_history."][Data successfuly insert in vlc_history table]";
																$this->logmsgvideoshootmail("Insert data in vlc_history table.",$this->log_path,$parentid,$extra_str,$this->processName);
															}
														/* Saving history for Vendor -- ENDS*/
														}
													}else{
														$extra_str= "[Query : ".$sql_insert."][Result : ".$qry_insert."][Data insert not done in tbl_vendor_upload_details as already assigned to same vendor]";
														$this->logmsgvideoshootmail("Insert data in tbl_vendor_upload_details table.",$this->log_path,$parentid,$extra_str,$this->processName);
													}
												}
											/* INSERT INTO tbl_vendor_upload_details -- ENDS*/
											}else{
												$extra_str= "[Query : ".$qryInsertVendorMapp."][Result : ".$resInsertVendorMapp."][Data is not successfuly insert in Mapped table]";
												$this->logmsgvideoshootmail("Insert data in mapped table.",$this->log_path,$parentid,$extra_str,$this->processName);
											}
										}
									}else{
										$extra_str= "[Mail send code: ".$api_responceArr['vendor']['code']."][Mail send status : ".$api_responceArr['vendor']['status']."][Mail end Msg :".$api_responceArr['vendor']['vendor msg']."][Mail not send suceessfully]";
										$this->logmsgvideoshootmail("Check video vendor mail send.",$this->log_path,$parentid,$extra_str,$this->processName);
									}
								}else{
									$extra_str= "[send email flag : ".intval($sendemail)."]";
									$this->logmsgvideoshootmail("email not not because of some reason.",$this->log_path,$parentid,$extra_str,$this->processName);
								}
							}else{
								$extra_str= "[Video is uploaded so mail is not send to video vendor][send mail flag : ".$checkVideoUploaded."]";
								$this->logmsgvideoshootmail("Check video is uploaded.",$this->log_path,$parentid,$extra_str,$this->processName);
							}
						}else{
							$extra_str= "[videoshoot campaign flag :".intval($videoshootCampaign)."][Contract have videoshoot campaign]";
							$this->logmsgvideoshootmail("only datacity condiation is not satisfied.",$this->log_path,$parentid,$extra_str,$this->processName);
						}

					}else{
						$extra_str= "[videoshoot campaign flag :".intval($videoshootCampaign)."][Contract dont have videoshoot campaign]";
						$this->logmsgvideoshootmail("Check contract have videoshoot campaign.",$this->log_path,$parentid,$extra_str,$this->processName);
					}

				}else{
					$extra_str= "[valid datacity flag :".intval($videoShootValidCity)."][Datacity is not valid]";
					$this->logmsgvideoshootmail("Check valid data city.",$this->log_path,$parentid,$extra_str,$this->processName);
				}

			}else{
				$extra_str= "[Approval flag :".intval($firstApproval)."][Video vendor is already assign]";
				$this->logmsgvideoshootmail("Check video vendor is already assign.",$this->log_path,$parentid,$extra_str,$this->processName);
			}
		}else{
			$extra_str= "[block for videoshoot category flag :".intval($liquorCatFlag)."][Contract have block for videoshoot category(liquor category)]";
			$this->logmsgvideoshootmail("Check category block for Video shoot.",$this->log_path,$parentid,$extra_str,$this->processName);
		}
	}
	
	function checkLiquorCategory($pid,$compmaster_obj){
		$liquarCategoryFlag = false;
		$resGetCategory	 = array();
		$field					 = "catidlineage";
		$where					 = "parentid ='".$pid."'";
		$resGetCategory  = $compmaster_obj->getRow($field,"tbl_companymaster_extradetails",$where);
		if($resGetCategory['numrows']>0){
			$rowGetCategory =  $resGetCategory['data']['0'];
			$catidlineageStr = $rowGetCategory['catidlineage'];
			$catidlineageArr  = explode("/",trim($catidlineageStr,"/"));
			if(count($catidlineageArr)){
				foreach($catidlineageArr as $key => $val){
					$output = '';
					$output = preg_replace('/[^0-9]/', '', $val);
					$catidlineageArr[$key]=$output;
				}
				$catidlineageArr = array_merge(array_filter($catidlineageArr));
				if(count($catidlineageArr)>0){
					$qryCheckliquarCat = "SELECT catid FROM tbl_categorymaster_generalinfo WHERE catid in('".implode("','",$catidlineageArr)."') AND display_product_flag&16=16";
					$resCheckliquarCat = $this->conn_decs->query_sql($qryCheckliquarCat);
					if($resCheckliquarCat && mysql_num_rows($resCheckliquarCat)>0){
						$liquarCategoryFlag = true;
					}
				}
			}
		}
		return $liquarCategoryFlag;
	}

	function checkFirstTimeApproval($pid,$version){
		$approveFlag = false;
		if(trim($pid)!='' && trim($version)!='' && intval(trim($version))>0){
			$qryGetcontractApprove = "SELECT parentid,version FROM tbl_vendor_mapped WHERE parentid ='".$pid."' AND version = '".$version."'";
			$resGetcontractApprove = $this->conn_finance->query_sql($qryGetcontractApprove);
			if($resGetcontractApprove && mysql_num_rows($resGetcontractApprove)<=0){
				$approveFlag = true;
				$extra_str= "[Query: ".$qryGetcontractApprove."] [Query result : ".$resGetcontractApprove."][Rows:".mysql_num_rows($resGetcontractApprove)."]";
				$this->logmsgvideoshootmail("Check video vendor is already assign query.",$this->log_path,$parentid,$extra_str,$this->processName);
			}
		}
		return $approveFlag;
	}

	function getDatacityValidation($pid,$compmaster_obj){
		$datacityFlag = false;
		$datacity_name = '';

		$resGetContractDatacity	 = array();
		$field					 = "data_city";
		$where					 = "parentid ='".$pid."'";
		$resGetContractDatacity  = $compmaster_obj->getRow($field,"tbl_companymaster_generalinfo",$where);

		if($resGetContractDatacity['numrows']>0){
			$rowGetContractDatacity = $resGetContractDatacity['data']['0'];
			if(trim($rowGetContractDatacity['data_city'])!=''){
				if(in_array(strtoupper(trim($rowGetContractDatacity['data_city'])),$this->data_city_arr)){
					$datacityFlag = true;
					$datacity_name = strtoupper(trim($rowGetContractDatacity['data_city']));
					$extra_str= "[Query: ".$qryGetContractDatacity."] [Query result : ".$resGetContractDatacity."][Rows:".($resGetContractDatacity['numrows'])."][DB data city :".trim($rowGetContractDatacity['data_city'])."][Data city is eligible][Flag :".intval($datacityFlag)."]";
					$this->logmsgvideoshootmail("Check contract data city.",$this->log_path,$parentid,$extra_str,$this->processName);
				}else{
					$extra_str= "[Query: ".$qryGetContractDatacity."] [Query result : ".$resGetContractDatacity."][Rows:".($resGetContractDatacity['numrows'])."][DB data city :".trim($rowGetContractDatacity['data_city'])."][Data city is not eligible][Flag :".intval($datacityFlag)."]";
					$this->logmsgvideoshootmail("Check contract data city.",$this->log_path,$parentid,$extra_str,$this->processName);
				}
			}else{
				$extra_str= "[Query: ".$qryGetContractDatacity."] [Query result : ".$resGetContractDatacity."][Rows:".($resGetContractDatacity['numrows'])."][DB data city :".trim($rowGetContractDatacity['data_city'])."][Data city is blank in company master generalinfo][Flag :".intval($datacityFlag)."]";
				$this->logmsgvideoshootmail("Check contract data city.",$this->log_path,$parentid,$extra_str,$this->processName);

				unset($rowGetContractDatacity);

				$qryCheckIdgenerator = "select data_city from tbl_id_generator where parentid = '".$pid."'";
				$resCheckIdgenerator  = $this->conn_iro->query_sql($qryCheckIdgenerator);
				if($resCheckIdgenerator && mysql_num_rows($resCheckIdgenerator)>0){
					$rowCheckIdgenerator = mysql_fetch_assoc($resCheckIdgenerator);
					if(trim($rowCheckIdgenerator['data_city'])!=''){
						if(in_array(strtoupper(trim($rowCheckIdgenerator['data_city'])),$this->data_city_arr)){
							$datacityFlag = true;
							$datacity_name = strtoupper(trim($rowCheckIdgenerator['data_city']));
							$extra_str= "[Query: ".$qryCheckIdgenerator."] [Query result : ".$resCheckIdgenerator."][Rows:".mysql_num_rows($resCheckIdgenerator)."][DB data city :".trim($rowCheckIdgenerator['data_city'])."][Data city is eligible][Flag :".intval($datacityFlag)."]";
							$this->logmsgvideoshootmail("Check contract data city in id_generator table.",$this->log_path,$parentid,$extra_str,$this->processName);
						}else{
							$extra_str= "[Query: ".$qryCheckIdgenerator."] [Query result : ".$resCheckIdgenerator."][Rows:".mysql_num_rows($resCheckIdgenerator)."][DB data city :".trim($rowCheckIdgenerator['data_city'])."][Data city is not eligible][Flag :".intval($datacityFlag)."]";
							$this->logmsgvideoshootmail("Check contract data city in id_generator table.",$this->log_path,$parentid,$extra_str,$this->processName);
						}
					}else{
						$extra_str= "[Query: ".$qryCheckIdgenerator."] [Query result : ".$resCheckIdgenerator."][Rows:".mysql_num_rows($resCheckIdgenerator)."][DB data city :".trim($rowCheckIdgenerator['data_city'])."][Data city is blank in id_generator][Flag :".intval($datacityFlag)."]";
						$this->logmsgvideoshootmail("Check contract data city in id_generator table.",$this->log_path,$parentid,$extra_str,$this->processName);
					}
				}else{
					$extra_str= "[Query: ".$qryCheckIdgenerator."] [Query result : ".$resCheckIdgenerator."][Rows:".mysql_num_rows($resCheckIdgenerator)."][DB data city :".trim($rowCheckIdgenerator['data_city'])."][record not found in id_generator table][Flag :".intval($datacityFlag)."]";
					$this->logmsgvideoshootmail("Check contract data city in id_generator table.",$this->log_path,$parentid,$extra_str,$this->processName);
				}
			}
		}else{
			$extra_str= "[Query: ".$qryGetContractDatacity."] [Query result : ".$resGetContractDatacity."][Rows:".mysql_num_rows($resGetContractDatacity)."][DB data city :".trim($rowGetContractDatacity['data_city'])."][record not found in company master generalinfo][Flag :".intval($datacityFlag)."]";
			$this->logmsgvideoshootmail("Check contract data city.",$this->log_path,$parentid,$extra_str,$this->processName);
		}
		$datacityArr['datacity_list'][0]['datacityName']=$datacity_name;
		$datacityArr['datacity_list'][0]['datacityFlag']=$datacityFlag;
		return $this->json($datacityArr);
	}
	
	function checkRentntion($pid){
		$rententionAlloc =  false;
		$this->multipleAllocateVendor = false;
		$this->vendorUploadDetail = array();
		if($pid!=''){
			$qryCheckRetention = "select parentid,assigned_to from login_details.tbl_vendor_upload_details where parentid = '".$pid."' and data_type = 2 and upload_video_flag=1 and approval_video_flag = 1";
			$resCheckRetention = $this->conn_idc->query_sql($qryCheckRetention);
			if($resCheckRetention && mysql_num_rows($resCheckRetention)>0){
				$rententionAlloc = true;
				$rowCheckRetention = mysql_fetch_assoc($resCheckRetention);
				$this->vendorUploadDetail['assigned_to'] = $rowCheckRetention['assigned_to'];
				$this->vendorUploadDetail['approve_video'] = '1';
			}elseif($resCheckRetention && mysql_num_rows($resCheckRetention)<=0){
				$qryCheckrent = "select parentid,assigned_to from login_details.tbl_vendor_upload_details where parentid = '".$pid."'";
				$resCheckrent = $this->conn_idc->query_sql($qryCheckrent);
				if($resCheckrent && mysql_num_rows($resCheckrent)>0){
					if(mysql_num_rows($resCheckrent)==1){
						$rententionAlloc = true;
						$rowCheckrent = mysql_fetch_assoc($resCheckrent);
						$this->vendorUploadDetail['assigned_to'] = $rowCheckrent['assigned_to'];
						$this->vendorUploadDetail['approve_video'] = '0';
					}else{
						$rententionAlloc = true;
						$this->multipleAllocateVendor = true;
					}
				}
			}
		}
		return $rententionAlloc;
	}

	function checkVideoshootCampaign($pid,$version){
		$videoshootcampaign= false;
		$qryGetvideoShootCampaign = "SELECT campaignid FROM tbl_companymaster_finance WHERE parentid = '".$pid."' AND version ='".$version."' AND campaignid =24 AND active_campaign =1";
		$resGetvideoShootCampaign = $this->conn_finance->query_sql($qryGetvideoShootCampaign);
		if($resGetvideoShootCampaign && mysql_num_rows($resGetvideoShootCampaign)>0){
			$videoshootcampaign = true;
			$extra_str= "[Query: ".$qryGetvideoShootCampaign."] [Query result : ".$resGetvideoShootCampaign."][Rows:".mysql_num_rows($resGetvideoShootCampaign)."][CONTRACT HAVE VIDEO SHOOT CAMPAIGN][Flag : ".intval($videoshootcampaign)."]";
			$this->logmsgvideoshootmail("Check video shoot campaign.",$this->log_path,$parentid,$extra_str,$this->processName);
		}else{
			$extra_str= "[Query: ".$qryGetvideoShootCampaign."] [Query result : ".$resGetvideoShootCampaign."][Rows:".mysql_num_rows($resGetvideoShootCampaign)."][CONTRACT DON'T HAVE VIDEO SHOOT CAMPAIGN][Flag : ".intval($videoshootcampaign)."]";
			$this->logmsgvideoshootmail("Check video shoot campaign.",$this->log_path,$parentid,$extra_str,$this->processName);
		}
		return $videoshootcampaign;
	}

	function callVendorassignFn($parentid,$version,$videoshootDatacity,$remoteCityIdentifier,$processname,$module,$videovendorId=''){
		if(APP_LIVE == 1){
			$url ='http://'.constant($this->server_city."_API").'/api/videoVendorClientMailApi.php';
		}else{
			$url ='http://yogitatandel.jdsoftware.com/csgenio/api/videoVendorClientMailApi.php';
		}
		$fields = array('parentid'	=>urlencode($parentid),
							's_deptCity'=>urlencode($videoshootDatacity),
							'datacity' => urlencode($videoshootDatacity),
							'module'	=> $module,
							'action' => 'vendorclientmail',
							'ucode' => $this->usercode,
							'processname' => $processname,
							'remotecityidentifier' => $remoteCityIdentifier);

		$extra_str= "[Url : ".$url."] [Parameters : ".json_encode($fields)."]";
		$this->logmsgvideoshootmail("Assign videoshoot vendor api called.",$this->log_path,$parentid,$extra_str,$this->processName);

		$resultString = curlCall($url,$fields);
		$curlResult = json_decode($resultString,true);
		$extra_str= "[Curl response : ".$resultString."]";
		$this->logmsgvideoshootmail("Check Api result.",$this->log_path,$parentid,$extra_str,$this->processName);
		return $resultString;
	}

	function getModule($pid,$version){
		$Vmodule = '';
		$isdeleted = '';
		switch(($version%10))
		{
			case 1: $isdeleted = $this->GetJda($pid,$version);
					if($isdeleted>0 && $isdeleted==1){
						$Vmodule = 'jda';
					}else{
						$Vmodule = 'cs';
					}
					break;

			case 2: $Vmodule = 'tme';
					break;

			case 3: $Vmodule = 'me';
					break;

			case '': $Vmodule = '';
						break;
		}
		return $Vmodule;
	}

	function GetJda($pid,$version){
		$is_deleted = -1;
		$QrygetJdaFlag = "select isdeleted from payment_apportioning where parentid ='".$pid."' and version ='".$version."' limit 1";
		$ResgetJdaFlag = $this->conn_finance->query_sql($QrygetJdaFlag);
		if($ResgetJdaFlag && mysql_num_rows($ResgetJdaFlag)>0){
			$RowgetJdaFlag = mysql_fetch_assoc($ResgetJdaFlag);
			$is_deleted = $RowgetJdaFlag['isdeleted'];
			$extra_str= "[Query: ".$QrygetJdaFlag."] [Query result : ".$ResgetJdaFlag."][Rows:".mysql_num_rows($ResgetJdaFlag)."][payment apportioning entry present][Flag : ".$is_deleted."]";
			$this->logmsgvideoshootmail("Get JDA flag.",$this->log_path,$pid,$extra_str,$this->processName);
		}else{
			$extra_str= "[Query: ".$QrygetJdaFlag."] [Query result : ".$ResgetJdaFlag."][Rows:".mysql_num_rows($ResgetJdaFlag)."][payment apportioning entry is not present][Flag : ".$is_deleted."]";
			$this->logmsgvideoshootmail("Get JDA flag.",$this->log_path,$pid,$extra_str,$this->processName);
		}
		return $is_deleted;
	}

	function checkPreviousVendor($pid){
		$vendorId = '';
		$qryGetPreviousVendor = "SELECT parentid,version,vendor_id FROM tbl_vendor_mapped WHERE parentid ='".$pid."' ORDER BY insert_date DESC LIMIT 1";
		$resGetPreviousVendor = $this->conn_finance->query_sql($qryGetPreviousVendor);
		if($resGetPreviousVendor && mysql_num_rows($resGetPreviousVendor)>0){
			$rowGetPreviousVendor = mysql_fetch_assoc($resGetPreviousVendor);
			$vendorId = $rowGetPreviousVendor['vendor_id'];
		}
		$extra_str= "[Query: ".$qryGetPreviousVendor."] [Query result : ".$resGetPreviousVendor."][Rows:".mysql_num_rows($resGetPreviousVendor)."][previous video vendor :".$vendorId."]";
		$this->logmsgvideoshootmail("Get previously assign video vendor.",$this->log_path,$pid,$extra_str,$this->processName);
		return $vendorId;
	}

	function CheckVideoUploaded($pid,$city){
		$sendVendorMailFlag = false;
		$older = false;
		$stdcode = $this->docid($city);
		$docid = $stdcode.$pid;
		$todayDate = date('Y-m-d H:i:s');
		//$docid = '022PXX22.XX22.131014125221.T9A8';
		if($pid!='' && $city!=''){
			if(APP_LIVE == 1){
				//$Videourl = 'http://192.168.1.121/web_services/vlc_details.php?docid='.$docid.'&city='.$city.'&mode=vdov&media=v';
				$Videourl = 'http://'.WEB_SERVICES_API.'/web_services/vlc.php?docid='.$docid.'&city='.$city.'&mode=vdov&media=v';
			}else{
				$Videourl = 'http://nileshkude.jdsoftware.com/svn/web_services_live/web_services/vlc.php?docid='.$docid.'&city='.$city.'&mode=vdov&media=v';
				//$Videourl = 'http://nileshkude.jdsoftware.com/svn/web_services/web_services/vlc_details.php?docid=022PXX22.XX22.131014125221.T9A8&city='.$city.'&mode=vdov&media=v';
			}//http://nileshkude.jdsoftware.com/svn/web_services/web_services/vlc_details.php?docid=022PXX22.XX22.131014125221.T9A8&city=Mumbai&mode=vdov

			$curlResultString = curlCall($Videourl);
			$VideocurlResult = json_decode($curlResultString,true);
			$extra_str= "[url :".$Videourl."][curlresponce : ".$curlResultString."]";
			$this->logmsgvideoshootmail("Check video uploaded on webiste.",$this->log_path,$pid,$extra_str,$this->processName);
			$diff = abs(strtotime($todayDate) - strtotime($VideocurlResult[$docid]['v'][0]['create_date']));
			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

			if(intval($VideocurlResult[$docid]['v'][0]['approved'])== '1' && intval($VideocurlResult[$docid]['v'][0]['delete_flag'])== '0'&& ($VideocurlResult[$docid]['v'][0]['display_flag']== '1'  || $VideocurlResult[$docid]['v'][0]['display_flag'] == '0') && intval($VideocurlResult[$docid]['v'][0]['process_flag'])== '0'){
				$extra_str= "[Uploaded  video is  approved][Approve flag :".$VideocurlResult[$docid]['v'][0]['approved']."][deleteed flag :".$VideocurlResult[$docid]['v'][0]['delete_flag']."][Display flag :".$VideocurlResult[$docid]['v'][0]['display_flag']."][Process flag : ".$VideocurlResult[$docid]['v'][0]['process_flag']."]";
				$this->logmsgvideoshootmail("check Uploaded video is approve",$this->log_path,$pid,$extra_str,$this->processName);
				if($years>0){
					$older = true;
				}else{
					if($months>12){
						$older = true;
					}else{
						if($days>365){
							$older = true;
						}
					}
				}
				if($older==true && $VideocurlResult[$docid]['v'][0]['create_date']!=''){
					$sendVendorMailFlag = true;
					$extra_str= "[Uploaded video is older.][days :".$days."][Uploaded Date :".$VideocurlResult[$docid]['v'][0]['create_date']."][Uploeded BY  :".$VideocurlResult[$docid]['v'][0]['upload_by']."][Module code :".$VideocurlResult[$docid]['v'][0]['module_type']."]";
					$this->logmsgvideoshootmail("Check uploaded video is not older then 1 year and uploaded by vendor",$this->log_path,$pid,$extra_str,$this->processName);
				}else{
					$extra_str= "[Uploaded video is latest one][days :".$days."][Uploaded Date :".$VideocurlResult[$docid]['v'][0]['create_date']."][Uploeded BY  :".$VideocurlResult[$docid]['v'][0]['upload_by']."][Module code :".$VideocurlResult[$docid]['v'][0]['module_type']."]";
					$this->logmsgvideoshootmail("Check uploaded video is not older then 1 year and  uploaded by vendor",$this->log_path,$pid,$extra_str,$this->processName);
				}
			}else{
				$sendVendorMailFlag = true;
				$extra_str= "[Uploaded  video is not approved][Approve flag :".$VideocurlResult[$docid]['v'][0]['approved']."][deleted flag :".$VideocurlResult[$docid]['v'][0]['delete_flag']."][Display flag :".$VideocurlResult[$docid]['v'][0]['display_flag']."][Process flag : ".$VideocurlResult[$docid]['v'][0]['process_flag']."]";
				$this->logmsgvideoshootmail("check Uploaded video is approve",$this->log_path,$pid,$extra_str,$this->processName);
			}
		}
		return $sendVendorMailFlag;
	}


	function checkPincode($pid,$vdatacity,$prvVendorId,$compmaster_obj){
		$vid ='';

		$resGetPincode = array();
		$field		   = "pincode";
		$where 		   = "parentid='".$pid."'";
		$resGetPincode = $compmaster_obj->getRow($field,"tbl_companymaster_generalinfo",$where);

		if($resGetPincode['numrows']>0){
			$rowGetPincode = $resGetPincode['data']['0'];
			$pincode = $rowGetPincode['pincode'];
			$extra_str= "[Query: ".$qryGetPincode."] [Query result : ".$resGetPincode."][Rows:".mysql_num_rows($resGetPincode)."][Pincode:".$pincode."]";
			$this->logmsgvideoshootmail("Get pincode.",$this->log_path,$pid,$extra_str,$this->processName);
			if($pincode!='' && intval($pincode)>0){
				$qryGetZoneId = "select zoneid from tbl_area_master where pincode='".$pincode."' and display_flag = 1 and deleted=0 and zoneid!='' limit 1";
				$resGetZoneId  = $this->conn_decs->query_sql($qryGetZoneId);
				if($resGetZoneId && mysql_num_rows($resGetZoneId)>0){
					$rowGetZoneId = mysql_fetch_assoc($resGetZoneId);
					$zoneid  = $rowGetZoneId['zoneid'];
					$extra_str= "[Query: ".$qryGetZoneId."] [Query result : ".$resGetZoneId."][Rows:".mysql_num_rows($resGetZoneId)."][Zoneid:".$zoneid."]";
					$this->logmsgvideoshootmail("Get zoneid.",$this->log_path,$pid,$extra_str,$this->processName);
					$vid = $this->getZonewiseVendorInfo($vdatacity,$zoneid,$prvVendorId);
					$extra_str= "[datacity:".$vdatacity."][zoneid:".$zoneid."][vendorid:".$vid."]";
					$this->logmsgvideoshootmail("Get zoneid wise videovendor.",$this->log_path,$pid,$extra_str,$this->processName);
				}else{
					$extra_str= "[Query: ".$qryGetZoneId."] [Query result : ".$resGetZoneId."][Rows:".mysql_num_rows($resGetZoneId)."]";
					$this->logmsgvideoshootmail("Get zoneid.",$this->log_path,$pid,$extra_str,$this->processName);
				}
			}
		}else{
			$extra_str= "[Query: ".$qryGetPincode."] [Query result : ".$resGetPincode."][Rows:".mysql_num_rows($resGetPincode)."]";
			$this->logmsgvideoshootmail("Get pincode.",$this->log_path,$pid,$extra_str,$this->processName);
		}
		return $vid;
	}

	function getZonewiseVendorInfo($vdatacity,$zoneid =0,$prvVendorId=''){
		$vid = '';
		$zoneidCondition = '';
		
		if($prvVendorId!='' && strpos(strtoupper($prvVendorId),'VEN_') !== false){
			$preVid = " and vendor_id='".$prvVendorId."' ";
		}
		if(intval(trim($zoneid))>0){
			$zoneidCondition = " AND FIND_IN_SET('".$zoneid."',service_area) ";

			$qryCheckZonewiseVendor  = "SELECT vendor_id FROM tbl_vendor_information WHERE data_city = '".$vdatacity."' ".$zoneidCondition. $preVid ." AND min_allocate_cap>0 AND active_vendor = 1 ";
			$resCheckZonewiseVendor = $this->conn_finance->query_sql($qryCheckZonewiseVendor);
			if($resCheckZonewiseVendor && mysql_num_rows($resCheckZonewiseVendor)>0){
				$rowCheckZonewiseVendor = mysql_fetch_assoc($resCheckZonewiseVendor);
				$vid = $rowCheckZonewiseVendor['vendor_id'];
			}elseif($resCheckZonewiseVendor && mysql_num_rows($resCheckZonewiseVendor)<=0){
				$qryCheckZonewiseVendor  = "SELECT vendor_id FROM tbl_vendor_information WHERE data_city = '".$vdatacity."' ".$zoneidCondition." AND min_allocate_cap>0 AND active_vendor = 1 ";
				$resCheckZonewiseVendor = $this->conn_finance->query_sql($qryCheckZonewiseVendor);
				if($resCheckZonewiseVendor && mysql_num_rows($resCheckZonewiseVendor)>0){
					$rowCheckZonewiseVendor = mysql_fetch_assoc($resCheckZonewiseVendor);
					$vid = $rowCheckZonewiseVendor['vendor_id'];
				}
			}
		}
		return $vid;
	}

	function docid($cityarg)
	{
		global $conn_local;
		//$ippartarray= explode(".", $_SERVER[SERVER_ADDR]);
		if($_SERVER[SERVER_ADDR]!=''){
			$ippartarray= explode(".", $_SERVER[SERVER_ADDR]);
		}else{
			if(count($_SERVER['argv'])>0){
				$ippartarray[2] = $_SERVER['argv'][1];
			}
		}
		$cityname="";
		$STDCode="";
		if(!defined('REMOTE_CITY_MODULE')) // main city module
		{
			switch($ippartarray[2])
			{
				case '0':	$cityname ="MUMBAI";
							break;
				case '8':	$cityname="DELHI";
							break;
				case '16':	$cityname="KOLKATA";
							break;
				case '26':	$cityname="BANGALORE";
							break;
				case '32':	$cityname="CHENNAI";
							break;
				case '40':	$cityname="PUNE";
							break;
				case '50':	$cityname="HYDERABAD";
							break;
				case '56':	$cityname="AHMEDABAD";
							break;
				case '64':	$cityname="MUMBAI";
							break;
			}

			$STDcodesql = "select stdcode from city_master WHERE ct_name='".$cityname."' limit 1";
			$STDcoderes = $this->conn_decs->query_sql($STDcodesql);

			if($STDcoderes and mysql_num_rows($STDcoderes)>0)
			{
				$STDcodearr = mysql_fetch_assoc($STDcoderes);
				$STDCode = $STDcodearr[stdcode];
			}
		}
		else  // remote city module
		{
			$cityarg= strtolower($cityarg);

			 $remotearray = array('agra', 'alappuzha', 'allahabad', 'amritsar', 'bhavnagar', 'bhopal', 'bhubaneshwar', 'chandigarh', 'coimbatore', 'cuttack', 'dharwad', 'ernakulam', 'goa', 'hubli', 'indore', 'jaipur', 'jalandhar', 'jamnagar', 'jamshedpur', 'jodhpur', 'kanpur', 'kolhapur', 'kozhikode', 'lucknow', 'ludhiana', 'madurai', 'mangalore', 'mysore', 'nagpur', 'nashik', 'patna', 'pondicherry', 'rajkot', 'ranchi', 'salem', 'shimla', 'surat', 'thiruvananthapuram', 'tirunelveli', 'trichy', 'udupi', 'vadodara', 'varanasi', 'vijayawada', 'vizag', 'visakhapatnam');

			if(in_array($cityarg,$remotearray))
			{
				$STDcodesql = "select stdcode from city_master WHERE ct_name='".$cityarg."' limit 1";
				$STDcoderes = $this->conn_decs->query_sql($STDcodesql);

				if($STDcoderes and mysql_num_rows($STDcoderes)>0)
				{
					$STDcodearr = mysql_fetch_assoc($STDcoderes);
					$STDCode = $STDcodearr[stdcode];
				}
			}
			else
			{
				// non famous remote city module
				$STDCode="9999";
			}

		}
		return $STDCode;
	}

	function getDistinctVendorDatacity(){
		$qryGetDistinctDcity = "SELECT DISTINCT data_city FROM tbl_vendor_information order by data_city";
		$resGetDistinctDcity = $this->conn_finance_slave->query_sql($qryGetDistinctDcity);
		if($resGetDistinctDcity && mysql_num_rows($resGetDistinctDcity)>0){
			$i=0;
			while($rowGetDistinctDcity = mysql_fetch_array($resGetDistinctDcity,MYSQL_ASSOC)){
				//$result['vendor_datacitylist'][$i] = $rowGetDistinctDcity;
				$result[$i] = $rowGetDistinctDcity;
				$i++;
			}
			return $this->json($result);
			//return $result;
		}else{
			$error = array('status' => "Failed", "msg" => "no data found");
			return $this->json($error);
		}
	}

	function getZoneList($Citynm,$commsep=0){
		$qryFetchZoneid = "SELECT zoneid FROM tbl_area_master WHERE data_city='".$Citynm."' GROUP BY zoneid";
		$resFetchZoneid = $this->conn_decs->query_sql($qryFetchZoneid);
		if($resFetchZoneid && mysql_num_rows($resFetchZoneid)>0){
			$i=0;
			while($rowFetchZoneid = mysql_fetch_array($resFetchZoneid,MYSQL_ASSOC)){
				//$result['vendor_datacitylist'][$i] = $rowGetDistinctDcity;
				if($commsep==1){
					$result[$i] = $rowFetchZoneid['zoneid'];
				}else{
					$result[$i] = $rowFetchZoneid;
				}
				$i++;
			}
			return $this->json($result);
		}else{
			$error = array('status' => "Failed", "msg" => "no data found");
			return $this->json($error);
		}
	}
	function getServiceRetentionArea($Citynm){
		if($Citynm!=''){
			$qryGetserviceArea = "SELECT GROUP_CONCAT(service_area) AS all_service_area,GROUP_CONCAT(retention_area) AS all_retention_area FROM tbl_vendor_information WHERE data_city='".$Citynm."' AND active_vendor=1";
			$resGetserviceArea = $this->conn_finance_slave->query_sql($qryGetserviceArea);
			if($resGetserviceArea && mysql_num_rows($resGetserviceArea)>0){
				$rowGetserviceArea = mysql_fetch_assoc($resGetserviceArea);
				return $this->json($rowGetserviceArea);
			}else{
				$error = array('status' => "Failed", "msg" => "no data found");
				return $this->json($error);
			}
		}
	}
	
	function getVendorIsNotpresent($dataCt){
		$serviceAreaDiff= array();
		$rententionAreaDiff = array();
		$serviceAreaArr = array();
		$rententionAreaArr = array();
		$zoneidListArr = array();
		$zoneidList = $this->getZoneList($dataCt,1);
		$zoneidListArr = json_decode($zoneidList,true);
		$getAllArea = $this->getServiceRetentionArea($dataCt);
		$getAllAreaArr = json_decode($getAllArea,true);
		if(count($zoneidListArr)>0){
			if($zoneidListArr['status']!= "Failed"){
				if($getAllAreaArr['status']!= "Failed"){
					$serviceArea = $getAllAreaArr['all_service_area'];
					if($serviceArea!=''){
						$serviceAreaArr = explode(",",$serviceArea);
						$serviceAreaArr = array_merge(array_filter(array_unique($serviceAreaArr)));
						asort($serviceAreaArr);
						$serviceAreaArr = array_merge(array_filter($serviceAreaArr));
						$serviceAreaDiff = array_diff($zoneidListArr,$serviceAreaArr);
					}
					$rententionArea = $getAllAreaArr['all_retention_area'];
					if($rententionArea!=''){
						$rententionAreaArr = explode(",",$rententionArea);
						$rententionAreaArr = array_merge(array_filter(array_unique($rententionAreaArr)));
						asort($rententionAreaArr);
						$rententionAreaArr = array_merge(array_filter($rententionAreaArr));
						$rententionAreaDiff = array_diff($zoneidListArr,$rententionAreaArr);
					}
					$result['notserviceArea']= implode(",",$serviceAreaDiff);
					$result['notretentionArea']= implode(",",$rententionAreaDiff);
				}else{
					$result = array('status' => "Failed", "msg" => "vendor data not found");
				}
			}else{
				$result = array('status' => "Failed", "msg" => "Zone data not found");
			}
		}
		return $this->json($result);
		//return $serviceAreaDiff;
	}

	private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
	}

	function logmsgvideoshootmail($sMsg,$sNamePrefix,$contractid,$extra_str='',$process='')
    {
        $log_msg='';


        // fetch directory for the file
        $pathToLog = dirname($sNamePrefix);
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        if(!file_exists($sNamePrefix))
		{
			mkdir($sNamePrefix, 0777, true);
		}
        /*$file_n=$sNamePrefix.$contractid.".txt"; */
        $file_n=$sNamePrefix.$contractid.".html";

        //chmod($file_n,0777);
        // Set this to whatever location the log file should reside at.
        $logFile = fopen($file_n, 'a+');

        // Change this to point to the User ID variable in session.
        if (isset($this->usercode) || isset($_SESSION['mktgEmpCode'])) {
            $userID = isset($this->usercode) ? $this->usercode : $_SESSION['mktgEmpCode']; //  Switches between TME_Live Session ID and DATAENTRY Session ID
        } else {
            $userID = 'unknown'; // stands for "default"  or "unknown"
        }
        /*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
        $pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
        $log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
                        <tr valign='top'>
                            <td style='width:10%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
                            <td style='width:10%; border:1px solid #669966'>File name:".$pageName."</td>
                            <td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
                            <td style='width:30%; border:1px solid #669966'>Extra Message: ".$extra_str."</td>
                            <td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
                            <td style='width:10%; border:1px solid #669966'>Action :".$process."</td>
                        </tr>
                    </table>";

        fwrite($logFile, $log_msg);
        fclose($logFile);
    }
}
?>
