define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('areaSelectionController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$log,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		
		$rootScope.setNoMenu	=	1;
		$scope.checkAreas	=	[];
		$scope.selected = [];
		$scope.checkDisabled	=	[];
		$scope.lengthData	=	0;
		$scope.limiter	=	15;
		$scope.limiterBand	=	1;
		$scope.highest_to_lowest = 0;
		$scope.flexiSel = 0;
		$scope.checkAreas_new   =   [];
		$scope.checkAreas_all_area_new   =   [];
		$scope.pincode_selected_new = {a_a_p:"", n_a_a_p:"",g_p_s:""};
		$scope.disable_htl = 1;
		$scope.pincode_selected_all_area_pincode = [];
		$scope.pincode_selected_non_all_area_pincode = [];
		$scope.all_area_cat = '';
		$scope.non_all_area_cat = '';		
		$scope.sel_payment_type = $cookieStore.get('payment_type'); 
	$scope.all_area_cat_arr = [];
        $scope.non_all_area_cat_arr = [];
        $rootScope.life_time_arr = {};
        $rootScope.life_time_arr['flexi_bud'] = '';
        $rootScope.life_time_arr['life_time_emi'] = '';
        $rootScope.life_time_arr['flexi_emi_3'] = 10;
        $rootScope.life_time_arr['flexi_emi_6'] = 15;
        $rootScope.life_time_arr['flexi_emi_9'] = 20;
        
        $rootScope.vfl = 0;
        
        $scope.sel_version = $cookieStore.get('version');
        var selected_opt=$cookieStore.get('campaign_str');
        if(typeof selected_opt !== 'undefined' && selected_opt!=''){
            $scope.selected_arr = selected_opt.split(',');
             if($scope.selected_arr[0] != 2 && $scope.selected_arr[0] != 746 ) {
                $scope.flexiSel = 1;                
            }else {
                $scope.flexiSel = 0;            
            }
            
            if($scope.selected_arr.indexOf("119") != -1){
			$rootScope.vfl = 1;
		}
			//~ else{
				//~ $scope.highest_to_lowest = 0;
            //~ }
        }
        $scope.allAreaCt = 0;
        $scope.nonAllAreaCt = 0;
        
        $scope.already_disp_flg = 0;
        $scope.show_cat_click = 0;
        $rootScope.existing_contract = 0;
        $scope.which_page = '';
        
        $rootScope.display_budget_temp = 0; 
		
		// || DATACITY.toLowerCase() == 'chennai'
		
		$rootScope.disp_budgt_str = '';
		
		//~ if(DATACITY.toLowerCase() == 'delhi' || DATACITY.toLowerCase() == 'kolkata' || DATACITY.toLowerCase() == 'bangalore' || DATACITY.toLowerCase() == 'pune' || DATACITY.toLowerCase() == 'hyderabad' || DATACITY.toLowerCase() == 'ahmedabad' || DATACITY.toLowerCase() == 'coimbatore' || DATACITY.toLowerCase() == 'chennai' || DATACITY.toLowerCase() == 'chandigarh' || DATACITY.toLowerCase() == 'mumbai'){
			//~ $rootScope.flexi_min = 18000;
			//~ $rootScope.display_budget_temp = 18000;
		//~ }else{
			//~ $rootScope.flexi_min = 12000;
			//~ $rootScope.display_budget_temp = 12000;
		//~ }
		
		//~ if(DATACITY.toLowerCase() == 'delhi' || DATACITY.toLowerCase() == 'kolkata' || DATACITY.toLowerCase() == 'hyderabad' || DATACITY.toLowerCase() == 'chandigarh'){
			//~ $rootScope.flexi_min = 15000;
		//~ }else{
			//~ $rootScope.flexi_min = 10000;
		//~ }

		APIServices.check_existing_budget(returnState.paridInfo,$scope.sel_payment_type).success(function(response) {
			if(response.error_code == 0){
				if(response.existing_contract == 2){
					$rootScope.flexi_min = response.budget;
					$rootScope.display_budget_temp = response.display_budget;
				}else {
					$rootScope.flexi_min = response.budget;
					$rootScope.display_budget_temp = response.display_budget;
					$rootScope.existing_contract = 1;
				}
			}
		});
		
		//Check Downsell Request
		APIServices.getversion($stateParams.parid,DATACITY).success(function(response) {
			if(response.version) {
				$rootScope.budgetVersion    =   response.version;
				APIServices.checkDiscount(returnState.paridInfo,response.version).success(function(response) {
					if(response.error == 0 || response.error == 4 ||response.error == '0' ) {
						$scope.stop_nxt = true;
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "Down Sell Request Present! Cant Proceed";
						return false;
					}
				});
			}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = response.error.msg;
					return false;
			}
		});
		
		
		//Calling Service for contractInformation
		APIServices.getContractData(returnState.paridInfo).success(function(response) {
			$rootScope.companyTempInfo	=	response;
			$scope.checkAreas[$rootScope.companyTempInfo.data.pincode]	=	true;
			$scope.checkDisabled[$rootScope.companyTempInfo.data.pincode]	=	true;
            $scope.checkAreas_new[$rootScope.companyTempInfo.data.pincode]   =   true;
           	$scope.checkAreas_all_area_new[$rootScope.companyTempInfo.data.pincode]   =   true;
			$scope.selected.push($rootScope.companyTempInfo.data.pincode);
            $scope.previous_selected_zonal_pins = [];
            $scope.skipData	=	0;
            $scope.showCatDistSel = 1;
            $scope.show_all_area_prev = 0;
            APIServices.getCatPreviewData($rootScope.parentid,DATACITY,'TME').success(function(responseCats) {
                if(responseCats['error']['code'] != 0) {
                    alert('There are no categories associated'); return false;
                }
				$scope.allAreaCt = 0;
                $scope.nonAllAreaCt = 0;
                if($scope.flexiSel == 1) {
                    angular.forEach(responseCats['data'],function(value,key) {
						if(value['type'] == 'All Area' || value['type'] == 'Zonal' || value['type'] =='Superzone') {                        
                            $scope.allAreaCt++;
                            $scope.all_area_cat += value['cnm']+', ';
                            $scope.all_area_cat_arr.push(value['cnm']);
                        } else {
                            $scope.nonAllAreaCt++;
                            $scope.non_all_area_cat += value['cnm']+', ';
                            $scope.non_all_area_cat_arr.push(value['cnm']);
                        }
                    });
                    $scope.non_all_area_cat = $scope.non_all_area_cat.replace(/,\s*$/, "");
                    $scope.all_area_cat = $scope.all_area_cat.replace(/,\s*$/, "");
                }
				var sendAttr	=	{};
                if($scope.allAreaCt >= $scope.nonAllAreaCt) {
					sendAttr.sendAttr	=	'DIST';
					sendAttr.rds	=	'5';
                    $scope.showCatDistSel   =   1; //setting distance tabs according to all area categories
                    $scope.selRadio =   5;
                    $scope.mainSelRadio =   5;
                    $scope.which_page = 'first';
                } else {
                    sendAttr.sendAttr   =   'DIST';
                    sendAttr.rds    =   '2.5';    
                    $scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
                    $scope.selRadio =   2.5;
                    $scope.mainSelRadio =   2.5;
                    $scope.already_disp_flg = 1;
                    $scope.which_page = 'first';
                }
                if($scope.flexiSel == 1 && ($scope.allAreaCt == 0 && $scope.nonAllAreaCt > 0)){
					$scope.topMsg	=	"Please Select Desired Radius for Non All Area Categories";
				}
                var my_flg = 0;
                if($scope.flexiSel == 1 && $scope.allAreaCt > 0){
					$scope.topMsg	=	"Please Select Desired Radius for All Area Categories";
					sendAttr.sendAttr   =   'DIST';
                    sendAttr.rds    =   '5';
                    $scope.showCatDistSel   =   1; //setting distance tabs according to all area categories
                    $scope.selRadio =   5;
                    $scope.mainSelRadio =   5;
                    my_flg = 1;
                    $scope.already_disp_flg = 0;
                    $scope.show_all_area_prev = 1;
                    $scope.which_page = 'first';
				}
				sendAttr.pincode	=	$rootScope.companyTempInfo.data.pincode;
				$scope.showLoader	=	0;
				$scope.allPinValsCity	=	[];
				$scope.new_pincodejson_arr = [];
				APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(response) {
					$scope.areaDataDist	=	response;
					$scope.areaDataSelPin	=	response;
                   	$scope.new_pincodejson_arr = response;
					APIServices.getAllPincodes(returnState.paridInfo).success(function(response2) {
						$scope.showLoader	=	1;
						var dataSet	=	response2.pincodelist.split(',');
						$scope.lengthData	=	dataSet.length;
						angular.forEach(response.results,function(value,index) {
							$scope.allPinValsCity.push(index);
							if(dataSet[0] == "") {
								$scope.selected.push(value.pin);
								$scope.checkAreas[value.pin]	=	true;
							}
						});                        
						if(dataSet[0] != "") {
							angular.forEach(dataSet,function(value,key) {
								$scope.checkAreas[value]	=	true;
								$scope.selected.push(value);
							});
						}
						if($scope.allAreaCt == 0 && $scope.flexiSel == 1){
							$scope.selected = [];
						}
						if($scope.non_all_area_cat == 0 && $scope.flexiSel == 1){
							$scope.selected = [];
						}
						if($scope.new_pincodejson_arr.pincodejson != undefined && $scope.flexiSel == 1){
							if($scope.new_pincodejson_arr.pincodejson.n_a_a_p != undefined && $scope.new_pincodejson_arr.pincodejson.n_a_a_p != '' && $scope.nonAllAreaCt > 0){
								$scope.new_pincodejson_arr.pincodejson.n_a_a_p = $scope.new_pincodejson_arr.pincodejson.n_a_a_p.replace(/,\s*$/, "");
								var non_all_area_pins = $scope.new_pincodejson_arr.pincodejson.n_a_a_p.split(',');
								angular.forEach(non_all_area_pins,function(v,i) {
									$scope.checkAreas_new[v]    =   true;
									$scope.pincode_selected_non_all_area_pincode.push(v);
									$scope.previous_selected_zonal_pins.push(v);
									$scope.selected.push(v);
								});
								if(dataSet[0] != "") {									
									angular.forEach(dataSet,function(value,key) {
										$scope.checkAreas[value]    =   false;
									});
								}
							}
							if($scope.new_pincodejson_arr.pincodejson.a_a_p != undefined && $scope.new_pincodejson_arr.pincodejson.a_a_p != '' && $scope.allAreaCt > 0){
								$scope.new_pincodejson_arr.pincodejson.a_a_p = $scope.new_pincodejson_arr.pincodejson.a_a_p.replace(/,\s*$/, "");
								var all_area_pins = $scope.new_pincodejson_arr.pincodejson.a_a_p.split(',');
								angular.forEach(all_area_pins,function(v,i) {
									$scope.checkAreas_all_area_new[v]    =   true;
									$scope.pincode_selected_all_area_pincode.push(v);
									$scope.selected.push(v);
								});
								if(dataSet[0] != "") {
									angular.forEach(dataSet,function(value,key) {
										$scope.checkAreas[value]    =   false;										
									});
								}
							}else{
								angular.forEach(response.results,function(value,index) {
									if(dataSet[0] == "") {
										if($scope.allAreaCt > 0){
											$scope.checkAreas_all_area_new[value.pin] = true;
											$scope.pincode_selected_all_area_pincode.push(value.pin);
										}
									}
								});
								if(dataSet[0] != "") {
									angular.forEach(dataSet,function(value,key) {
										if($scope.allAreaCt > 0){
											$scope.checkAreas_all_area_new[value] = true;
											$scope.pincode_selected_all_area_pincode.push(value);
										}
									});
								}
							}
						}else if($scope.flexiSel == 1){
							angular.forEach(response.results,function(value,index) {
								if(dataSet[0] == "" && $scope.allAreaCt > 0) {
									$scope.checkAreas_all_area_new[value.pin] = true;
									$scope.pincode_selected_all_area_pincode.push(value.pin);
								}
							});							
							if(dataSet[0] != "" && $scope.allAreaCt > 0) {
								angular.forEach(dataSet,function(value,key) {
									$scope.checkAreas_all_area_new[value] = true;
									$scope.pincode_selected_all_area_pincode.push(value);
								});
							}
						}
						
						if($scope.flexiSel == 1 && ($scope.pincode_selected_all_area_pincode == '' && $scope.allAreaCt > 0)){
							angular.forEach(response.results,function(value,index) {
								if(dataSet[0] == "" && $scope.allAreaCt > 0) {
									$scope.checkAreas_all_area_new[value.pin] = true;
									$scope.pincode_selected_all_area_pincode.push(value.pin);
								}
							});							
							if(dataSet[0] != "" && $scope.allAreaCt > 0) {
								angular.forEach(dataSet,function(value,key) {
									$scope.checkAreas_all_area_new[value] = true;
									$scope.pincode_selected_all_area_pincode.push(value);
								});
							}
						}
						
						$scope.checkAreas_new[response2.physical_pincode]   =   true;
						$scope.pincode_selected_non_all_area_pincode.push(response2.physical_pincode);
						$scope.checkAreas_all_area_new[response2.physical_pincode] = true;
						$scope.pincode_selected_all_area_pincode.push(response2.physical_pincode);
						
						if(response2.is_datacity_pincode == 0) {
							$scope.selected.splice($scope.selected.indexOf(response2.physical_pincode),1);
							$scope.checkAreas[response2.physical_pincode]	=	false;
							if($scope.showCatDistSel == 2 && $scope.nonAllAreaCt > 0){
								$scope.checkAreas_new[response2.physical_pincode]   =   false;
								$scope.pincode_selected_non_all_area_pincode.splice($scope.selected.indexOf(response2.physical_pincode),1);
							}
							if($scope.showCatDistSel == 1 && $scope.allAreaCt > 0){
								$scope.checkAreas_all_area_new[response2.physical_pincode]   =   false;
								$scope.pincode_selected_all_area_pincode.splice($scope.selected.indexOf(response2.physical_pincode),1);
							}
						}
						if($scope.allAreaCt == 0){
							$scope.pincode_selected_all_area_pincode = [];
						}
						if($scope.nonAllAreaCt == 0){
							$scope.pincode_selected_all_area_pincode.splice($scope.selected.indexOf(response2.physical_pincode),1);
							$scope.pincode_selected_non_all_area_pincode = [];
						}
						var sendAttr	=	{};
						sendAttr.sendAttr	=	'ALL';
						sendAttr.rds	=	'';
						sendAttr.pincode	=	'';
						$scope.showLoader	=	0;
						$scope.allPinValsCity	=	[];
						APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(response) {
							$scope.areaDataSelPin	=	response;
							$scope.showLoader	=	1;
							angular.forEach(response.results,function(value,index) {
								$scope.allPinValsCity.push(index);
							});
						});
						if($scope.moduleType	==	'TME' || $scope.moduleType	==	'ME') {
							APIServices.findGetContractData(returnState.paridInfo).success(function(response2) {
								if(response2.error.code == 0) {
									$rootScope.getcon_loader = 1;
									APIServices.fetchLiveData(returnState.paridInfo).success(function(respmsg) { 
										if(respmsg.error.code == 0){ 
											$rootScope.getcon_loader = 0; 
											window.location = '../business/bform.php?navbar=yes'; 
										}else if(respmsg.error.code == 2){
											$rootScope.getcon_loader = 0;
											$rootScope.downselVal	=	respmsg.error.msg;
											$rootScope.showCommonPop	=	'downsel_check';
										}
									});
								}else if(response2.error.code == 2){
									$rootScope.downselVal	=	response2.error.msg;
									$rootScope.showCommonPop	=	'downsel_check';
								}
							});			
						}
						
				});
			});
		});
	});

        
		$scope.showCatsClick_toggle = function(ev,show_cat_click){
			if(show_cat_click == 0){
				$scope.show_cat_click = 1;
			}
			if(show_cat_click == 1){
				$scope.show_cat_click = 0;
			}			
		};
		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
		
		$rootScope.parentid = returnState.paridInfo;
		$rootScope.extraHandler	=	$stateParams.page;
		$rootScope.businessUrl	=	'../business/bform.php?navbar=yes';
		//Handling for JDA
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://rakeshkotian.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$rootScope.businessUrl	=	'http://rakeshkotian.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://'+CONSTANTS.ServerUrl+'/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$rootScope.businessUrl	=	'http://'+CONSTANTS.ServerUrl+'/include/redirect_doc.php?redirect_path=location_info';
			}
		}
		
		//Function to stop auto sorting by ng-repeat in angular js
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
		
		var selected = null;
		$scope.selectedIndex = 2;
		$scope.topMsg	=	"Please select desired areas";
		$scope.doSecondaryAction	=	function(event) {
			if($(event.target).closest('div').find('.dataAreas').hasClass('none')) {
				$(event.target).closest('div').find('.dataAreas').removeClass('none');
			} else {
				$(event.target).closest('div').find('.dataAreas').addClass('none');
			}
		};
		
		
		$scope.toggle = function (item, list,index) {
			if($scope.checkAreas[index]) {
				if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
					var idx = list.indexOf(item);
					list.splice(idx, 1);
					if(list.length > 0) {
						$scope.skipData	=	0;
					} else {
						$scope.skipData	=	1;
					}
				}
			} else {
				if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
					list.push(item);
					if(list.length > 0) {
						$scope.skipData	=	0;
					} else {
						$scope.skipData	=	1;
					}
				}
			}
		};
		
		$scope.toggle_new = function (item, list,index) {
            if($scope.checkAreas_new[index]) {
                if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                    var idx = list.indexOf(item);
                    list.splice(idx, 1);
                    if(list.length > 0) {
                        $scope.skipData =   0;
                    } else {
                        $scope.skipData =   1;
                    }
					var idx = $scope.selected.indexOf(item);
					$scope.selected.splice(idx,1);
					if($scope.showCatDistSel == 2 && $scope.flexiSel == 1 && $scope.nonAllAreaCt > 0){
						var idx = $scope.pincode_selected_non_all_area_pincode.indexOf(item);
						$scope.pincode_selected_non_all_area_pincode.splice(idx,1);
					}
                }
            } else {
                if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                    list.push(item);
					$scope.selected.push(item);
					if($scope.showCatDistSel == 2 && $scope.flexiSel == 1 && $scope.nonAllAreaCt > 0){
						$scope.pincode_selected_non_all_area_pincode.push(item);
					}
                    if(list.length > 0) {
                        $scope.skipData =   0;
                    } else {
                        $scope.skipData =   1;
                    }
                }
            }
        };
        $scope.toggle_all_area_new = function (item, list,index) {
            if($scope.checkAreas_all_area_new[index]) {
                if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                    var idx = list.indexOf(item);
                    list.splice(idx, 1);
                    if(list.length > 0) {
                        $scope.skipData =   0;
                    } else {
                        $scope.skipData =   1;
                    }
                    var idx = $scope.selected.indexOf(item);
					$scope.selected.splice(idx,1);
                    if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
						var idx = $scope.pincode_selected_all_area_pincode.indexOf(item);
						$scope.pincode_selected_all_area_pincode.splice(idx,1);
					}
                }
            } else {
                if(parseInt(item) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                    list.push(item);
                    if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
						$scope.pincode_selected_all_area_pincode.push(item);
					}
					$scope.selected.push(item);
                    if(list.length > 0) {
                        $scope.skipData =   0;
                    } else {
                        $scope.skipData =   1;
                    }
                }
            }
        };
        
		$scope.selAllCheck	=	[];
		$scope.selectAll	=	function(length,id,list) {
			if($scope.selAllCheck[id]) {
				angular.forEach($scope.areaData.results,function(value,key) {
					if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pin]	=	false;
						var idx = list.indexOf(value.pin);
					}
				});
				$scope.selected = [];
				$scope.selected.push($rootScope.companyTempInfo.data.pincode);
                if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
					$scope.pincode_selected_all_area_pincode = [];
					$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode)
				}
				if($scope.showCatDistSel == 2 && $scope.flexiSel == 1 && $scope.nonAllAreaCt > 0){
					$scope.pincode_selected_non_all_area_pincode = [];
					$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode)
				}
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			} else {
				angular.forEach($scope.areaData.results,function(value,key) {
					if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {					
						$scope.checkAreas[value.pin]	=	true;
						var idx = list.indexOf(value.pin);
						list.push(value.pin);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.pincode_selected_all_area_pincode.push(value.pin);
						}
						if($scope.showCatDistSel == 2 && $scope.flexiSel == 1 && $scope.nonAllAreaCt > 0){
							$scope.pincode_selected_non_all_area_pincode.push(value.pin);
						}
					}
				});
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			}
		};
		
		$scope.checkZones	=	[];
		$scope.selAllCheckZone	=	[];
		$scope.selectAllZone	=	function(length,id,list) {
			if($scope.selAllCheckZone[id]) {
				var i=0;
				angular.forEach($scope.areaDataZone.results,function(value,key) {
					$scope.checkZones[i]	=	false;
					angular.forEach(value.areapin,function(value2,key2) {
						if(parseInt(value2.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
							$scope.checkAreas[value2.pincode]	=	false;
							var idx = list.indexOf(value2.pincode);
						}
					});
					i++;
				});
				$scope.selected = [];
				$scope.selected.push($rootScope.companyTempInfo.data.pincode);
                if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
					$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode)
				}
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			} else {
				var i=0;
				angular.forEach($scope.areaDataZone.results,function(value,key) {
					$scope.checkZones[i]	=	true;
					angular.forEach(value.areapin,function(value2,key2) {
						if(parseInt(value2.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
							$scope.checkAreas[value2.pincode]	=	true;
							var idx = list.indexOf(value2.pincode);
							list.push(value2.pincode);
                            if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
								$scope.pincode_selected_all_area_pincode.push(value2.pincode);
							}
						}
					});
					i++;
				});
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			}
		};
		
		$scope.selAllCheckName	=	[];
		$scope.selectAllName	=	function(length,id,list) {
			if($scope.selAllCheckName[id]) {
				angular.forEach($scope.areaDataName.results,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	false;
						var idx = list.indexOf(value.pincode);
					}
				});
				$scope.selected = [];
				$scope.selected.push($rootScope.companyTempInfo.data.pincode);
                if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
					$scope.checkAreas_all_area_new[value.pincode]    =   false;
					$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode)
				}
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			} else {
				angular.forEach($scope.areaDataName.results,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	true;
						var idx = list.indexOf(value.pincode);
						list.push(value.pincode);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   true;
							$scope.pincode_selected_all_area_pincode.push(value.pincode);
						}
					}
				});
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			}
		};
		
		$scope.selAllCheckPin	=	[];
		$scope.selectAllPin	=	function(length,id,list) {
			if($scope.selAllCheckPin[id]) {
				angular.forEach($scope.areaDataPin.results,function(value,key) {
					if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pin]	=	false;
						var idx = list.indexOf(value.pin);
					}
				});
				$scope.selected = [];
				$scope.selected.push($rootScope.companyTempInfo.data.pincode);
                if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
					$scope.checkAreas_all_area_new[value.pincode]    =   false;
					$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode)
				}
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			} else {
				angular.forEach($scope.areaDataPin.results,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pin]	=	true;
						var idx = list.indexOf(value.pin);
						list.push(value.pin);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   true;
							$scope.pincode_selected_all_area_pincode.push(value.pin);
						}
					}
				});
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			}
		};
		
		$scope.selAllCheckDist	=	[];
        $scope.selAllCheckDist_new = [];
        $scope.selAllCheckDist_all_area_new = [];
		$scope.selectAllDist	=	function(length,id,list) {
			if($scope.selAllCheckDist[id]) {
				angular.forEach($scope.areaDataDist.results,function(value,key) {
					if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pin]	=	false;
						var idx = list.indexOf(value.pin);
					}
				});
				$scope.selected = [];
				$scope.selected.push($rootScope.companyTempInfo.data.pincode);
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			} else {
				angular.forEach($scope.areaDataDist.results,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pin]	=	true;
						var idx = list.indexOf(value.pin);
						list.push(value.pin);
					}
				});
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
			}
		};
        //~ $scope.checkAreas_all_area_new;
        $scope.selectAllDist_all_area_new =   function(length,id,list) {			
            if($scope.selAllCheckDist_all_area_new[id] != undefined && $scope.selAllCheckDist_all_area_new[id] == true) {
                angular.forEach($scope.areaDataDist.results,function(value,key) {
                    if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                        $scope.checkAreas_all_area_new[value.pin] = false;
                        var idx = list.indexOf(value.pin);
                    }
                });
                $scope.pincode_selected_all_area_pincode = [];
                $scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);	
                //~ $scope.selected = [];
                //~ $scope.selected.push($rootScope.companyTempInfo.data.pincode);
                if(list.length > 0) {
                    $scope.skipData =   0;
                } else {
                    $scope.skipData =   1;
                }
            } else {
				$scope.selectedVals = [];
				$scope.pincode_selected_all_area_pincode = [];
                angular.forEach($scope.areaDataDist.results,function(value,key) {
                    if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                        $scope.checkAreas_all_area_new[value.pin]    =   true;
                        var idx = list.indexOf(value.pin);
                        list.push(value.pin);
                        $scope.pincode_selected_all_area_pincode.push(value.pin);
                        //~ $scope.selected.push(value.pin);
                    }
                });
                if(list.length > 0) {
                    $scope.skipData =   0;
                } else {
                    $scope.skipData =   1;
                }
            }
            if($scope.flexiSel == 1){
				$scope.pincode_selected_all_area_pincode = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
				$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
			}
        };
        
        $scope.selectAllDist_new =   function(length,id,list) {			
            if($scope.selAllCheckDist_new[id] != undefined && $scope.selAllCheckDist_new[id] == true) {
                angular.forEach($scope.areaDataDist.results,function(value,key) {
                    if(parseInt(value.pin) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                        $scope.checkAreas_new[value.pin] = false;
                        var idx = list.indexOf(value.pin);
                    }
                });
                $scope.previous_selected_zonal_pins = [];
                $scope.pincode_selected_non_all_area_pincode = [];
                $scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
                //~ $scope.previous_selected_zonal_pins.push($rootScope.companyTempInfo.data.pincode);
                if(list.length > 0) {
                    $scope.skipData =   0;
                } else {
                    $scope.skipData =   1;
                }
            } else {
				$scope.pincode_selected_non_all_area_pincode = [];
				//~ $scope.previous_selected_zonal_pins = [];
                angular.forEach($scope.areaDataDist.results,function(value,key) {
                    if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
                        $scope.checkAreas_new[value.pin]    =   true;
                        var idx = list.indexOf(value.pin);
                        list.push(value.pin);
						$scope.pincode_selected_non_all_area_pincode.push(value.pin);
						//~ $scope.previous_selected_zonal_pins.push(value.pin);
						 //~ $scope.selected.push(value.pin);
                    }
                });
                if(list.length > 0) {
                    $scope.skipData =   0;
                } else {
                    $scope.skipData =   1;
                }
            }
            if($scope.flexiSel == 1){
				$scope.pincode_selected_all_area_pincode = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
				$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
			}
        };
		
		$scope.stateChangedZone	=	function(zoneid,list) {
			if($scope.checkZones[zoneid]) {
				angular.forEach($scope.areaDataZone.results[zoneid].areapin,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	false;
						var idx = list.indexOf(value.pincode);
						list.splice(idx, 1);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1  && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   false;
							var idx = $scope.pincode_selected_all_area_pincode.indexOf(value.pincode);
							$scope.pincode_selected_all_area_pincode.splice(idx, 1);
						}
					}
				});
			} else {
				angular.forEach($scope.areaDataZone.results[zoneid].areapin,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	true;
						list.push(value.pincode);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   true;
							$scope.pincode_selected_all_area_pincode.push(value.pincode);
						}
					}
				});
			}
		};

		$scope.checkBand	=	[];
		$scope.stateChangedBand	=	function(zoneid,list) {
			if($scope.checkBand[zoneid]) {
				angular.forEach($scope.areaDataBand.results[zoneid].areapin,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	false;
						var idx = list.indexOf(value.pincode);
						list.splice(idx, 1);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   false;
							var idx = $scope.pincode_selected_all_area_pincode.indexOf(value.pincode);
							$scope.pincode_selected_all_area_pincode.splice(idx, 1);
						}
					}
				});
			} else {
				angular.forEach($scope.areaDataBand.results[zoneid].areapin,function(value,key) {
					if(parseInt(value.pincode) != parseInt($rootScope.companyTempInfo.data.pincode)) {
						$scope.checkAreas[value.pincode]	=	true;
						list.push(value.pincode);
                        if($scope.showCatDistSel == 1 && $scope.flexiSel == 1 && $scope.allAreaCt > 0){
							$scope.checkAreas_all_area_new[value.pincode]    =   true;
							$scope.pincode_selected_all_area_pincode.push(value.pincode);
						}
					}
				});
			}
		};
		
		$scope.dataFilter	=	'all';
		$scope.searchAreasAlpha	=	function(letter) {
			$scope.dataFilter	=	letter.toLowerCase();
		};
       		$scope.selectedAreaIdx  =   0;
       	
       	$scope.bestPositionShow =   {};
		$scope.bestBudgetShow   =   {};
		$scope.bidderValue      =   {};
		$scope.cattotalBudget   =   {};
		$scope.bidValue         =   {};
		$scope.inventory        =   {};
		$scope.callcount        =   {};
		$scope.callcountTotal   =   {};
		$scope.callcountTotalFix=   {};
		$scope.searchcount      =   {};
		$scope.searchcountTotal =   {};
		$scope.searchcountTotalFix= {};
		$scope.totalBudgetShowMain  =   "";
		var catKeyOld   =   "";
		$scope.callCountTotalTop    =   0;
		$scope.callCountTotalTopExtra   =   0;
		$scope.searchCountTotalTop  =   0;
		$scope.searchCountTotalTopExtra =   0;
		$scope.loadInit =   function(catKey,pin,bestBudg,index,index2) {
			$scope.setArrowLimiter  =   1;
            if((catKeyOld   !=  catKey) ||  ($scope.bestPositionShow[catKey] === undefined)) {
                $scope.bestPositionShow[catKey] =   {};
                $scope.bestBudgetShow[catKey]   =   {};

                $scope.bidderValue[catKey]      =   {};
                $scope.bidValue[catKey]     =   {};
                $scope.inventory[catKey]        =   {};
                $scope.callcount[catKey]    =   {};
            }
            if(index == 0) {
                $scope.callcountTotal[catKey]   =   0;
                $scope.callcountTotalFix[catKey]    =   0;
            }
            if(index2 == 0) {
                $scope.callCountTotalTop    =   0;
                $scope.callCountTotalTopExtra   =   0;
            }
            $scope.bestPositionShow[catKey][pin]=   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].best_flg;
            $scope.bestBudgetShow[catKey][pin]  =   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].budget;
            $scope.bidderValue[catKey][pin]     =   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidder;
            $scope.bidValue[catKey][pin]        =   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidvalue;
            $scope.inventory[catKey][pin]       =   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].inv_avail;
            $scope.callcount[catKey][pin]       =   $scope.pckbestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
            $scope.callcountTotal[catKey]       =   $scope.callcountTotal[catKey]+$scope.pckbestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
            $scope.callcountTotalFix[catKey]    =   $scope.callcountTotalFix[catKey]+$scope.pckbestBudget.result.c_data[catKey].pin_data[pin].cnt_f;

            $scope.cattotalBudget[catKey]   =    parseFloat($scope.pckbestBudget.result.c_data[catKey].flexi_bgt);
			$scope.totalBudgetShowMain  =   parseFloat($scope.pckbestBudget.result.tb_flexi_bgt);
            
            $scope.callCountTotalTop        =   $scope.callCountTotalTop+$scope.pckbestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
            $scope.callCountTotalTopExtra   =   $scope.callCountTotalTopExtra+$scope.pckbestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
            catKeyOld   =   catKey;
        };
        
        $scope.submit_pck_budget =   function(customBudget,tenure) { 
            $scope.pck_dis = $cookieStore.get('pck_dis');
            $scope.submitArr    =   {};
            if($scope.pck_dis != undefined && $scope.pck_dis !=0 )
				$scope.submitArr['pck_dis']  =  $scope.pck_dis;
				
            $scope.submitArr['c_data']  =   {};
            $scope.submitArr['packageBudget']   =   0;
            $scope.submitArr['pdgBudget']   =   0;
            $scope.submitArr['tenure']  =  tenure;
            $scope.submitArr['actual_bgt']  =   0;
            $scope.submitArr['removeCatStr']    =   '';
            $scope.submitArr['nonpaidStr']  =   "";
            angular.forEach($scope.bestBudgetShow,function(value,key) {
                $scope.submitArr['totBudget']   =   {};
                if(customBudget == 0) {
                    $scope.submitArr['totBudget']   =   $scope.totalBudgetShowMain;
                } else {
                    $scope.submitArr['totBudget']   =   customBudget*12;
                }
                $scope.submitArr['customBudget']    =   customBudget*12;
				  
                $scope.submitArr['reg_bgt']     =   $scope.pckbestBudget.result.reg_bgt;
                $scope.submitArr['city_bgt']    =   $scope.pckbestBudget.result.city_bgt;
                if($scope.cattotalBudget[key]) {
					$scope.submitArr['actual_bgt']  =   $scope.submitArr['actual_bgt'] + $scope.cattotalBudget[key];
                    $scope.submitArr['c_data'][key] =   {};
                    $scope.submitArr['c_data'][key]['c_bgt']    =   $scope.cattotalBudget[key];
                    $scope.submitArr['c_data'][key]['bflg']     =   $scope.pckbestBudget.result.c_data[key]['bflg'];
                    $scope.submitArr['c_data'][key]['bm_bgt']   =   $scope.pckbestBudget.result.c_data[key]['bm_bgt'];
                    $scope.submitArr['c_data'][key]['cnm']      =   $scope.pckbestBudget.result.c_data[key]['cnm'];
                    $scope.submitArr['c_data'][key]['ncid']     =   $scope.pckbestBudget.result.c_data[key]['ncid'];
                    $scope.submitArr['c_data'][key]['flexi_bgt']     =   $scope.cattotalBudget[key];
                    $scope.submitArr['c_data'][key]['pin_data'] =   {};
                    angular.forEach(value,function(value2,key2) {
							$scope.submitArr['c_data'][key]['pin_data'][key2]   =   {};
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['pos']    =   {};
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['anm']    =   $scope.pckbestBudget.result.c_data[key]['pin_data'][key2]['anm'];
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']    =   {};
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']    =   $scope.pckbestBudget.result.c_data[key]['pin_data'][key2].cnt;
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['cnt_f']  =   $scope.pckbestBudget.result.c_data[key]['pin_data'][key2].cnt_f;
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]    =   {};
                            var bidderCt = 0;
							if(typeof $scope.pckbestBudget.result.c_data[key]['pin_data'][key2]['pos'] !== 'undefined') {
								angular.forEach($scope.pckbestBudget.result.c_data[key]['pin_data'][key2]['pos'],function(valuePos,keyPos) {
									if(keyPos != 100) {
										if(typeof valuePos['bidder'] !== 'undefined') {
											if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
												bidderCt++;
											}
										}
									}
								});
							}
							$scope.submitArr['c_data'][key]['pin_data'][key2]['flexi_pos']  =   $scope.pckbestBudget.result.c_data[key]['pin_data'][key2].flexi_pos+bidderCt;
							$scope.submitArr['c_data'][key]['pin_data'][key2]['flexi_bgt']  =   parseFloat($scope.pckbestBudget.result.c_data[key]['pin_data'][key2].flexi_bgt);
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['budget']  =    parseFloat($scope.pckbestBudget.result.c_data[key]['pin_data'][key2].flexi_bgt);
                            if($scope.bestPositionShow[key][key2] == 100) {
                                $scope.submitArr['packageBudget']       =   $scope.submitArr['packageBudget']+value2;
                            } else {
                                $scope.submitArr['pdgBudget']       =   $scope.submitArr['pdgBudget']+value2;
                            }
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['bidvalue']    =   $scope.bidValue[key][key2];
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['inventory']   =   $scope.inventory[key][key2];
                    });
				}else {
					$scope.submitArr['nonpaidStr']  +=  key+',';
					$scope.submitArr['removeCatStr']  +=  key+',';
				}
            });
			if($scope.submitArr['actual_bgt'] == 0) {
                $mdToast.show(
                    $mdToast.simple()
                    .content('Please select categories to proceed.')
                    .position('top right')
                    .hideDelay(3000)
                );
                return false;
            }
            $scope.showOptionLoader =   1;
            $scope.submitArr['nonpaidStr'] = $scope.submitArr['nonpaidStr'].slice(0,-1);
            $scope.submitArr['removeCatStr'] = $scope.submitArr['removeCatStr'].slice(0,-1);
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,0).success(function(response) {
                $scope.showOptionLoader =   0;
                if(response.error_code == 0) {
					$state.go('appHome.showBudgetPageSub',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
                }else
                {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Some error found while submiting budget. Please contact software team';  
                }
            });
        };
        
        $scope.get_pack_details = function(){
			
			$scope.flexi_budget = $cookieStore.get('flexi_bud');
			$scope.pck_tenure = $cookieStore.get('flexi_tenure');
			
			if($scope.flexi_budget == undefined || parseInt($scope.flexi_budget) == 0){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Budget Missing contact software team";
				return false;
			}
			
			if($scope.pck_tenure == undefined || parseInt($scope.pck_tenure) == 0){
				$scope.pck_tenure = 12;
			}
			APIServices.getBudgetData(3,1,$scope.pck_tenure,returnState.paridInfo,DATACITY,$rootScope.employees.hrInfo.data.empname,$rootScope.companyTempInfo.data.pincode,0,0,$scope.flexi_budget,0).success(function(response) {
				$scope.showOptionLoader =   0;
				$scope.catLengthWise    =   {};
				if(typeof response === 'object') {
					$scope.pckbestBudget   =   response;
					var l=0;
					angular.forEach(response.result.c_data,function(value,key) {
						var i=0;
						$scope.catLengthWise[key]   =   0;
						angular.forEach(value.pin_data,function(value2,key2) {
							$scope.loadInit(key,key2,value2.best_flg,i,l);
							i++;
							$scope.catLengthWise[key]++;
							$scope.pinLength++;
							$scope.oldPinLength++;
							l++
						});
						$scope.catLength++;
						$scope.oldCatLength++;
					});
					$scope.submit_pck_budget($scope.flexi_budget/12,$scope.pck_tenure);
				} else {
					$scope.pckbestBudget   =   {};
					$scope.pckbestBudget['error_code'] =   2;
				}
			});
		}
		
		$scope.submitPins	=	function(ev) {
			if($scope.nonAllAreaCt == 0 && $scope.flexiSel == 1){
				$scope.pincode_selected_non_all_area_pincode.splice($rootScope.companyTempInfo.data.pincode,1);
			}
			if($scope.allAreaCt == 0 && $scope.flexiSel == 1){
				$scope.pincode_selected_all_area_pincode.splice($rootScope.companyTempInfo.data.pincode,1);
			}
			if($scope.flexiSel == 1){
				if($scope.nonAllAreaCt > 0){
					$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
					$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
					});
					angular.forEach($scope.pincode_selected_non_all_area_pincode,function(value,key) {											
						if($scope.selected.indexOf(value) == -1) {
							$scope.selected.push(value);

						}
					});
				}
				if($scope.allAreaCt > 0){
					$scope.pincode_selected_all_area_pincode = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
						   return inputArray.indexOf(item) == index;
					});
					
					angular.forEach($scope.pincode_selected_all_area_pincode,function(value,key) {											
						if($scope.selected.indexOf(value) == -1) {
							$scope.selected.push(value);
						}
					});
				}
				
			}
			var sendArr	=	[];
			var strSendPincode	=	"";
			if($scope.tabValue == 6) {
				$scope.which_page = 'last';
				APIServices.getExistingCats(returnState.paridInfo,DATACITY).success(function(response) {
					if(response.error.code == 0) {
						if(response.data.tmperror.code == 2) {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "There is/are non biddable category/s associated.";
								$rootScope.commonShowContent = 'You can not proceed further with following non biddable category/s:\n'+response.data.TEMP.BLOCK;
						} else if(response.data.tmperror.code == 1){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "There are no paid categories";
								$rootScope.commonShowContent = 'You can not proceed further because there are no paid categories in this contract';
						} else {
							var counter=0;
							var list_of_pin_to_remove = '';
							angular.forEach($scope.selected,function(value,key) {
								if($scope.allPinValsCity.indexOf(value) > -1) {
									counter++;
								}
							});
							if($scope.previous_selected_zonal_pins != undefined && $scope.previous_selected_zonal_pins != '' && $scope.nonAllAreaCt !=0){
								angular.forEach($scope.previous_selected_zonal_pins,function(value,key) {
									if(sendArr.indexOf(value) == -1) {
										sendArr.push(value);
										strSendPincode	+=	value+",";
										list_of_pin_to_remove  +=  value+",";
									}
								});
							}
							if($scope.flexiSel == 0){
								$scope.pincode_selected_new.g_p_s =  strSendPincode;
							}
							if($scope.flexiSel == 1) {
								var sendAttr = {};
								sendAttr.sendAttr   =   'DIST';
								sendAttr.rds    =   '2.5'; 
								sendAttr.pincode    =   $rootScope.companyTempInfo.data.pincode;
								$scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
								APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(responseDist) {									
									//~ $scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
									$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
										   return inputArray.indexOf(item) == index;
									});
									angular.forEach($scope.pincode_selected_non_all_area_pincode,function(value,key) {
										if($scope.allPinValsCity.indexOf(value) > -1) {
											counter++;
										}
									});
									var substrSendPincode   =   strSendPincode.slice(0,-1);
									var expSubPin   =   substrSendPincode.split(",");
									var twokmpin    =   [];
									angular.forEach(responseDist['results'],function(value,key) {
										twokmpin.push(value['pin']);
									});
									var notwokmpin = 0;
									var keepgoing = true;
						
									angular.forEach(expSubPin,function(value,key) {
										if(keepgoing) {
											if(twokmpin.indexOf(value) == -1) {
												notwokmpin = 1;
												keepgoing = false;
											}
										}
									});
									list_of_pin_to_remove = list_of_pin_to_remove.slice(0,-1);                                   
									if(notwokmpin == 1 && list_of_pin_to_remove != '' && $scope.nonAllAreaCt !=0) {
										var confirm = $mdDialog.confirm()
										  .title('Out of Range Pincodes')
										  .content('Remove pincodes Non All Area Category as they are out of 2.5 km range '+list_of_pin_to_remove)
										  .ariaLabel('')
										  .clickOutsideToClose(false)
										  .targetEvent(ev)
										  .ok('Okay')

										$mdDialog.show(confirm).then(function() {
											$scope.selectedVals  =   [];
											angular.forEach($scope.selected,function(value,key) {
												if(value != '' && value != null) {
													if($scope.selectedVals.indexOf(value) == -1) {
														$scope.selectedVals.push(value);
													}
												}
											});
											$scope.tabValue =   6;
											$scope.selectedAreaIdx  =   6;
											$scope.show_all_area_prev = 1;
											$scope.which_page = 'last';
											$scope.disable_htl =0;
											$scope.merge_pincode();
										}, function() {
											$scope.selectedVals  =   [];
											angular.forEach($scope.selected,function(value,key) {
												if(value != '' && value != null) {
													if($scope.selectedVals.indexOf(value) == -1) {
														$scope.selectedVals.push(value);
													}
												}
											});
											$scope.tabValue =   6;
											$scope.selectedAreaIdx  =   6;
											$scope.show_all_area_prev = 1;
											$scope.which_page = 'last';
											$scope.disable_htl =0;
											$scope.merge_pincode();
										});
										return false;
									}
									
									angular.forEach($scope.selected,function(value,key) {											
										if(sendArr.indexOf(value) == -1) {
											sendArr.push(value);
											strSendPincode  +=  value+",";
										}
									});
									var substrSendPincode	=	strSendPincode.slice(0,-1);
									var all_area_pin_str = '';
									var non_all_area_pin_str = '';
									var all_area_p_arr = [];
									var non_all_area_p_arr = [];
									$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
									$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
									all_area_p_arr = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
										   return inputArray.indexOf(item) == index;
									});
									angular.forEach(all_area_p_arr,function(val,key) {
											all_area_pin_str  +=  val+",";
									});										
									non_all_area_p_arr = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
										   return inputArray.indexOf(item) == index;
									});
									angular.forEach(non_all_area_p_arr,function(val1,key) {
										non_all_area_pin_str  +=  val1+",";
									});
									$scope.pincode_selected_new.n_a_a_p = '';
									$scope.pincode_selected_new.a_a_p = '';
									$scope.pincode_selected_new.n_a_a_p = non_all_area_pin_str;
									$scope.pincode_selected_new.a_a_p = all_area_pin_str;
									APIServices.setAreaPincodeData(substrSendPincode,returnState.paridInfo,DATACITY,'TME',$scope.pincode_selected_new).success(function(response) {
										if($scope.selected_arr[0] == '119'){
											if($scope.sel_payment_type.toLowerCase() == 'ecs'){
												$rootScope.showCommonPop = 'life_time_emi'
											}else{
												$rootScope.showCommonPop = 'life_time_amount';
											}
										}else if($scope.selected_arr[0] != '118' && ($scope.selected_arr[0] == '734' || $scope.selected_arr[0] == '73' || $scope.selected_arr[0] == '735' || $scope.selected_arr[0].substr(0,2) == '11' || $scope.selected_arr[0] == '1') || ($scope.selected_arr.length >1 && ($scope.selected_arr[1] == '734' || $scope.selected_arr[1] == '73' || $scope.selected_arr[1] == '735' || $scope.selected_arr[1] == '1'))){  
											$scope.get_pack_details();
										}else{ 
											$state.go('appHome.showExistInventory',{parid:returnState.paridInfo,flow:'fixed',page:$rootScope.extraHandler});
										}
									});
										
								});
							} else {
								angular.forEach($scope.selected,function(value,key) {											
									if(sendArr.indexOf(value) == -1) {
										sendArr.push(value);
										strSendPincode  +=  value+",";
									}
								});
								var substrSendPincode = strSendPincode.slice(0,-1);
								var all_area_pin_str = '';
								var non_all_area_pin_str = '';
								var all_area_p_arr = [];
								var non_all_area_p_arr = [];
								$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
								$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
								all_area_p_arr = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
									   return inputArray.indexOf(item) == index;
								});
								angular.forEach(all_area_p_arr,function(val,key) {
										all_area_pin_str  +=  val+",";
								});										
								non_all_area_p_arr = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
									   return inputArray.indexOf(item) == index;
								});
								angular.forEach(non_all_area_p_arr,function(val1,key) {
										non_all_area_pin_str  +=  val1+",";
								});
								$scope.pincode_selected_new.n_a_a_p = '';
								$scope.pincode_selected_new.a_a_p = '';
								$scope.pincode_selected_new.n_a_a_p = non_all_area_pin_str;
								$scope.pincode_selected_new.a_a_p = all_area_pin_str;
								APIServices.setAreaPincodeData(substrSendPincode,returnState.paridInfo,DATACITY,'TME',$scope.pincode_selected_new).success(function(response) {
									if($scope.selected_arr[0] == '119'){
										if($scope.sel_payment_type.toLowerCase() == 'ecs'){
											$rootScope.showCommonPop = 'life_time_emi'
										}else{
											$rootScope.showCommonPop = 'life_time_amount';
										}		
									}else if(($scope.selected_arr[0] != '118' && $scope.selected_arr[0] == '734' || $scope.selected_arr[0] == '73' || $scope.selected_arr[0] == '735' || $scope.selected_arr[0].substr(0,2) == '11' || $scope.selected_arr[0] == '1') || ($scope.selected_arr.length >1 && ($scope.selected_arr[1] == '734' || $scope.selected_arr[1] == '73' || $scope.selected_arr[1] == '735' || $scope.selected_arr[1] == '1'))){
										$scope.get_pack_details();
									}else{ 
									 $state.go('appHome.showExistInventory',{parid:returnState.paridInfo,flow:'fixed',page:$rootScope.extraHandler});
									}
								});
							}
						}
					}else {
						$mdToast.show(
							$mdToast.simple()
							.content('No categories associated, please select categories to proceed.')
							.position('top right')
							.hideDelay(3000)
						);	
						return false;
					}
				});
            } else {
				if($scope.nonAllAreaCt == 0 && $scope.flexiSel == 1){
					$scope.tabValue = 1; // only all area
					$scope.which_page = 'last';
				}
				if($scope.tabValue !=   1 && $scope.nonAllAreaCt != 0 && $scope.flexiSel == 1 && $scope.already_disp_flg == 0){
					$scope.show_cat_click = 0;
					$scope.tabValue = '';
					$scope.selectedAreaIdx  =   ''; 
					var sendAttr = {};
					sendAttr.sendAttr   =   'DIST';
					sendAttr.rds    =   '2.5';    
					$scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
					$scope.selRadio =   2.5;
					$scope.mainSelRadio =   2.5;
					sendAttr.pincode    =   $rootScope.companyTempInfo.data.pincode;
					$scope.showLoader   =   0;
					$scope.allPinValsCity   =   [];
					$scope.my_var_pin = [];
					$scope.topMsg	=	"Please Select Desired Radius for Non All Area Categories";
					$scope.show_all_area_prev = 2;
					$scope.which_page = 'second';
					APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(response) {
						$scope.areaDataDist =   response;
						$scope.areaDataSelPin   =   response;
						$scope.my_var_pin   =   response;
						APIServices.getAllPincodes(returnState.paridInfo).success(function(response2) {
							$scope.showLoader   =   1;
							var dataSet =   response2.pincodelist.split(',');
							$scope.lengthData   =   dataSet.length;
							angular.forEach(response.results,function(value,index) {
								$scope.allPinValsCity.push(index);
								if(dataSet[0] == "") {
									$scope.selected.push(value.pin);
									$scope.checkAreas[value.pin]    =   true;
								}
							});
							if(dataSet[0] != "") {
								angular.forEach(dataSet,function(value,key) {
									$scope.checkAreas[value]    =   true;
								});
							}
							if(response2.is_datacity_pincode == 0) {
								$scope.selected.splice($scope.selected.indexOf(response2.physical_pincode),1);
								$scope.checkAreas[response2.physical_pincode]   =   false;
							}
							var sendAttr    =   {};
							sendAttr.sendAttr   =   'ALL';
							sendAttr.rds    =   '';
							sendAttr.pincode    =   '';
							APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(response) {
								$scope.areaDataSelPin   =   response;
								angular.forEach(response.results,function(value,index) {
									$scope.allPinValsCity.push(index);
								});
							});
						});
					});
					$scope.tabValue = 1;
				}else if($scope.tabValue == 1 || $scope.already_disp_flg == 1 || $scope.flexiSel == 0){					
					$scope.selectedVals  =   [];
					if($scope.flexiSel == 0){
						angular.forEach($scope.selected,function(value,key) {
							if(value != '' && value != null) {
								if($scope.selectedVals.indexOf(value) == -1) {
									$scope.selectedVals.push(value);
								}
							}
						});
					}
					if($scope.flexiSel == 1 && ($scope.allAreaCt == 0 || $scope.nonAllAreaCt == 0)){
						angular.forEach($scope.selected,function(value,key) {
							if(value != '' && value != null) {
								if($scope.selectedVals.indexOf(value) == -1) {
									$scope.selectedVals.push(value);
								}
							}
						});
					}					
					if($scope.checkAreas_new != undefined && $scope.checkAreas_new != '' && $scope.nonAllAreaCt > 0){
						angular.forEach($scope.checkAreas_new,function(value,key) {							
							if((value != '' && value != null) && value == true) {
								if($scope.selectedVals.indexOf(key) == -1) {
									$scope.selectedVals.push(key.toString());
									$scope.selected.splice(key.toString());
								}
							}
						});
					}
					if($scope.checkAreas_all_area_new != undefined && $scope.checkAreas_all_area_new != '' && $scope.allAreaCt > 0){
						angular.forEach($scope.checkAreas_all_area_new,function(value,key) {
							if((value != '' && value != null) && value == true) {
								if($scope.selectedVals.indexOf(key) == -1) {
									$scope.selectedVals.push(key.toString());
									$scope.selected.splice(key.toString());
								}
							}
						});
					}
					$scope.selectedVals = $scope.selected;
					$scope.selectedVals = $scope.selectedVals.filter( function( item, index, inputArray ) {
						   return inputArray.indexOf(item) == index;
					});
					$scope.selected = $scope.selected.filter( function( item, index, inputArray ) {
						   return inputArray.indexOf(item) == index;
					});
					
					/*Code Added Here*/
					if($scope.flexiSel == 1 && $scope.nonAllAreaCt > 0){				
						angular.forEach($scope.previous_selected_zonal_pins,function(value,key) {
							if(sendArr.indexOf(value) == -1) {
								sendArr.push(value);
								strSendPincode  +=  value+",";
							}
						});
						var sendAttr = {};
						sendAttr.sendAttr   =   'DIST';
						sendAttr.rds    =   '2.5'; 
						sendAttr.pincode    =   $rootScope.companyTempInfo.data.pincode;
						$scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
						APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(responseDist) {
							$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
							$scope.selectedVals.push($rootScope.companyTempInfo.data.pincode);
							$scope.selected.push($rootScope.companyTempInfo.data.pincode);
							$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
								   return inputArray.indexOf(item) == index;
							});
							//~ angular.forEach($scope.pincode_selected_non_all_area_pincode,function(value,key) {
								//~ if($scope.allPinValsCity.indexOf(value) > -1) {
								//~ }
							//~ });
							var substrSendPincode   =   strSendPincode.slice(0,-1);
							var expSubPin   =   substrSendPincode.split(",");
							var twokmpin    =   [];
							angular.forEach(responseDist['results'],function(value,key) {
								twokmpin.push(value['pin']);
							});
							var notwokmpin = 0;
							var keepgoing = true;
							var list_of_pin_to_remove = '';
							angular.forEach(expSubPin,function(value,key) {
								if(twokmpin.indexOf(value) == -1) {
									list_of_pin_to_remove += value+',';
								}
							}); 							
							angular.forEach(expSubPin,function(value,key) {
								if(keepgoing) {
									if(twokmpin.indexOf(value) == -1) {
										notwokmpin = 1;
										keepgoing = false;
									}
								}
							});
							list_of_pin_to_remove = list_of_pin_to_remove.slice(0,-1);							
							if(notwokmpin == 1 && list_of_pin_to_remove != '' && $scope.nonAllAreaCt !=0) {
									 var confirm = $mdDialog.confirm()
										  .title('Out of Range Pincodes')
										  .content('Remove pincodes from Non All Area Category as they are out of 2.5 km range '+list_of_pin_to_remove)
										  .ariaLabel('')
										  .clickOutsideToClose(false)
										  .targetEvent(ev)
										  .ok('Okay')

									$mdDialog.show(confirm).then(function() {
										$scope.selectedVals  =   [];
										angular.forEach($scope.selected,function(value,key) {
											if(value != '' && value != null) {
												if($scope.selectedVals.indexOf(value) == -1) {
													$scope.selectedVals.push(value);
												}
											}
										});
										$scope.tabValue =   6;
										$scope.selectedAreaIdx  =   6;
										$scope.show_all_area_prev = 1;
										$scope.which_page = 'last';
										$scope.disable_htl =0;
										$scope.merge_pincode();
									}, function() {
										$scope.selectedVals  =   [];
										angular.forEach($scope.selected,function(value,key) {
											if(value != '' && value != null) {
												if($scope.selectedVals.indexOf(value) == -1) {
													$scope.selectedVals.push(value);
												}
											}
										});
										$scope.tabValue =   6;
										$scope.selectedAreaIdx  =   6;
										$scope.show_all_area_prev = 1;
										$scope.which_page = 'last';
										$scope.disable_htl =0;
										$scope.merge_pincode();
									});
								return false;
							}else{
								$scope.tabValue =   6;
								$scope.selectedAreaIdx  =   6;
								$scope.show_all_area_prev = 1;
								$scope.which_page = 'last';
								$scope.disable_htl =0;
								$scope.merge_pincode();
							}
						});
					}else{
						$scope.tabValue =   6;
						$scope.selectedAreaIdx  =   6;
						$scope.show_all_area_prev = 1;
						$scope.which_page = 'last';
						$scope.disable_htl =0;
						$scope.merge_pincode();
					}
				}
            }
		};
		
		if($rootScope.vfl == 1) {
			if($scope.sel_payment_type.toLowerCase() == 'ecs'){
				$rootScope.showCommonPop = 'life_time_emi'
			}else{
				$rootScope.showCommonPop = 'life_time_amount';
			}
			//~ $scope.merge_pincode();
		}
		
		$rootScope.lifetime_ecs = function(){
			if($rootScope.life_time_arr['life_time_emi'] == ''){
				$mdToast.show(
					$mdToast.simple()
					.content('Please select a EMI option')
					.position('top right')
					.parent(angular.element(document.querySelector('.display_emi_error')))
					.hideDelay(4000)
				);
			}else{
				if($rootScope.life_time_arr['life_time_emi'] == 3 && $rootScope.life_time_arr['flexi_emi_'+$rootScope.life_time_arr['life_time_emi']] < 10){
					$mdToast.show(
						$mdToast.simple()
						.content('3 Months EMI Premium% should be 10% or more')
						.position('top right')
						.parent(angular.element(document.querySelector('.display_emi_error')))
						.hideDelay(4000)
					);
					return false;
				}else if($rootScope.life_time_arr['life_time_emi'] == 6 && $rootScope.life_time_arr['flexi_emi_'+$rootScope.life_time_arr['life_time_emi']] < 15){
					$mdToast.show(
						$mdToast.simple()
						.content('6 Months EMI Premium% should be 15% or more')
						.position('top right')
						.parent(angular.element(document.querySelector('.display_emi_error')))
						.hideDelay(4000)
					);
					return false;
				}else if($rootScope.life_time_arr['life_time_emi'] == 9 && $rootScope.life_time_arr['flexi_emi_'+$rootScope.life_time_arr['life_time_emi']] < 20){
					$mdToast.show(
						$mdToast.simple()
						.content("9 Months EMI Premium% should be 20%  or more")
						.position('top right')
						.parent(angular.element(document.querySelector('.display_emi_error')))
						.hideDelay(4000)
					);
					return false;
				}
				
				APIServices.set_pack_emi($stateParams.parid,$rootScope.companyTempInfo['data']['companyname'],$rootScope.budgetVersion,$rootScope.life_time_arr['life_time_emi'],$rootScope.life_time_arr['flexi_emi_'+$rootScope.life_time_arr['life_time_emi']],'package').success(function(response) {
					if(response.error_code == 0){
						$rootScope.showCommonPop = 'life_time_amount';
					}else{
						$mdToast.show(
							$mdToast.simple()
							.content('Please try again')
							.position('top right')
							.parent(angular.element(document.querySelector('.display_emi_error')))
							.hideDelay(4000)
						);
					}
				});
			}
		}
		$rootScope.goToPriceChart = function(){
			$rootScope.showCommonPop = '';
			$state.go('appHome.pricechartnew',{parid:$rootScope.parentid,ver:$scope.sel_version,page:$stateParams.page});
		}
		
		$rootScope.submit_lifetime = function(){
			if($rootScope.life_time_arr["flexi_bud"] < $rootScope.flexi_min){
				if($rootScope.existing_contract == 1){
					$scope.min_msg = 'It is an existing contract, budget should be more than '+$rootScope.flexi_min;
				}else{
					$scope.min_msg = 'Minimum budget is '+$rootScope.flexi_min;
				}
				$mdToast.show(
					$mdToast.simple()
					.content($scope.min_msg)
					.position('top right')
					.parent(angular.element(document.querySelector('.display_budget_error')))
					.hideDelay(4000)
				);
			}else {
				var substrSendPincode = $rootScope.companyTempInfo.data.pincode;
				var all_area_pin_str = '';
				var non_all_area_pin_str = '';
				var all_area_p_arr = [];
				var non_all_area_p_arr = [];
				$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
				$scope.pincode_selected_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
				all_area_p_arr = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
				angular.forEach(all_area_p_arr,function(val,key) {
						all_area_pin_str  +=  val+",";
				});										
				non_all_area_p_arr = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
				angular.forEach(non_all_area_p_arr,function(val1,key) {
					non_all_area_pin_str  +=  val1+",";
				});
				$scope.pincode_selected_new.n_a_a_p = '';
				$scope.pincode_selected_new.a_a_p = '';
				$scope.pincode_selected_new.n_a_a_p = non_all_area_pin_str;
				$scope.pincode_selected_new.a_a_p = all_area_pin_str;
				APIServices.setAreaPincodeData(substrSendPincode,returnState.paridInfo,DATACITY,'TME',$scope.pincode_selected_new).success(function(response) {
					//~ if($scope.selected_arr[0] == '119'){
						//~ if($scope.sel_payment_type.toLowerCase() == 'ecs'){
							//~ $rootScope.showCommonPop = 'life_time_emi'
						//~ }else{
							//~ $rootScope.showCommonPop = 'life_time_amount';
						//~ }
					//~ }else if($scope.selected_arr[0] != '118' && ($scope.selected_arr[0] == '734' || $scope.selected_arr[0] == '73' || $scope.selected_arr[0] == '735' || $scope.selected_arr[0].substr(0,2) == '11' || $scope.selected_arr[0] == '1') || ($scope.selected_arr.length >1 && ($scope.selected_arr[1] == '734' || $scope.selected_arr[1] == '73' || $scope.selected_arr[1] == '735' || $scope.selected_arr[1] == '1'))){  
						//~ $scope.get_pack_details();
					//~ }else{ 
						//~ $state.go('appHome.showExistInventory',{parid:returnState.paridInfo,flow:'fixed',page:$rootScope.extraHandler});
					//~ }
				});
				APIServices.get_pack_emi(returnState.paridInfo,$rootScope.budgetVersion).success(function(response) { 
					if($scope.sel_payment_type.toLowerCase() == 'ecs' && (response.error_code == 1 || response.data == '' || response.data == null)){
						$mdToast.show(
							$mdToast.simple()
							.content('Ecs EMI option Missing')
							.position('top right')
							.parent(angular.element(document.querySelector('.display_budget_error')))
							.hideDelay(4000)
						);
						return false;
					}
					
					if($scope.sel_payment_type.toLowerCase() == 'upfront'){
						$scope.life_time_val = parseFloat($rootScope.life_time_arr["flexi_bud"]);
					}else if($scope.sel_payment_type.toLowerCase() == 'ecs') {
						$scope.life_time_val = parseFloat($rootScope.life_time_arr["flexi_bud"])+parseFloat($rootScope.life_time_arr["flexi_bud"] * response.data/100);
					}
					console.log($scope.life_time_val);
					$cookieStore.put('flexi_bud',$scope.life_time_val);
					$cookieStore.put('flexi_tenure',120);
					$rootScope.showCommonPop = '';
					$scope.get_pack_details();
				});
			}
			
		}
		
		$scope.submit_flexi_budget = function(){
			
			$scope.stop_submitting = 0;
			$scope.pincode_restrict = '';
			angular.forEach($scope.selected_pincode, function(val,key){
				if($scope.pincode_val[val] < $scope.flexi_min && $scope.pincode_selected_budget[val] == true){
					$scope.stop_submitting = 1;
					$scope.pincode_restrict += val+',';
				}
			});
			
			if($scope.stop_submitting == 1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Budget of "+$scope.pincode_restrict+" should be greater than "+$scope.flexi_min;
				return false;
			}else{
				var temp_obj = {};
				$.each($scope.pincode_selected_budget, function(index, value) {					
					if(value == true){
						if($scope.sel_payment_type.toLowerCase() == 'ecs'){ 
							$scope.pincode_val[index] = (parseFloat($scope.pincode_val[index]) + parseFloat($scope.pincode_val[index])*0.2) 
						}
						temp_obj[index] = $scope.pincode_val[index];
						
					}
				}); 
				$scope.pincode_val = {};
				$scope.pincode_val = temp_obj;
				console.log($scope.pincode_val);				
				APIServices.submit_flexi_value(returnState.paridInfo,$rootScope.employees.hrInfo.data.empname,JSON.stringify($scope.pincode_val)).success(function(response) {
					var selected_opt=$cookieStore.get('campaign_str');
					$scope.selected_arr = selected_opt.split(',');
					$scope.package_index =  $scope.selected_arr.indexOf('119');
					if(response.error_code == 0){
						if($scope.selected_arr[$scope.package_index+1] == '73' || $scope.selected_arr[$scope.package_index+1] == '731' || $scope.selected_arr[$scope.package_index+1] == '732' || $scope.selected_arr[$scope.package_index+1] == '735' || $scope.selected_arr[$scope.package_index+1] == '734' || $scope.selected_arr[$scope.package_index+1] == '741' || $scope.selected_arr[$scope.package_index+1] == '748') {
							$state.go('appHome.omnidomainreg',{parid:$stateParams.parid,ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
						}else if($scope.selected_arr[$scope.package_index+1] == '5'){
							$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
						}else if($scope.selected_arr[$scope.package_index+1] == '225'){
							$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
						}else if($scope.selected_arr[$scope.package_index+1] == '22') {
							APIServices.addjdrr($stateParams.parid,$rootScope.budgetVersion,0).success(function(response) {
								if(response.error.code == 0) {
									$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
								}else {
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Genio";
									$rootScope.commonShowContent = response.error.msg;
									return false;
								}
							});
						}else {
							$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
						}
					}else{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "Error while submitting the budget";
						return false;
					}
				});
			}
		}
		
		$scope.merge_pincode = function(){
			$scope.selected_temp = [];
			$scope.selected_pincode = [];
			$scope.pincode_val = {};
			//~ $scope.disable_htl =0;
			$scope.selected_pincode_showBudget = {};
			$scope.selected_pincode_showBudget_area = {}; 
			$scope.pincode_selected_budget= {};
			
			$scope.selected_non_all_area_temp = [];
			$scope.selected_all_area_temp = [];
			
			$scope.selected_non_all_area_temp 	= $scope.pincode_selected_non_all_area_pincode.slice();
			$scope.selected_all_area_temp 		= $scope.pincode_selected_all_area_pincode.slice();
			
			if($scope.pincode_selected_all_area_pincode != undefined && $scope.pincode_selected_non_all_area_pincode != undefined)
				$scope.selected_temp = 	$scope.selected_all_area_temp.concat($scope.selected_non_all_area_temp);
			else if($scope.pincode_selected_all_area_pincode != undefined)	
				$scope.selected_temp = 	$scope.selected_all_area_temp
			else if($scope.pincode_selected_non_all_area_pincode != undefined)	
				$scope.selected_temp = 	$scope.selected_non_all_area_temp
			
			angular.forEach($scope.selected_temp, function(val,key){
				if($scope.selected_pincode.indexOf(val) == -1){
					$scope.selected_pincode.push(val);
					$scope.pincode_val[val] = "";
					if(key == 0){
						$scope.selected_pincode_showBudget[val] = true;
					}else{
					$scope.selected_pincode_showBudget[val] = false;
					}
					$scope.selected_pincode_showBudget_area[val] = false;
					$scope.pincode_selected_budget[val] = true;
				}
			});
			
			console.log($scope.selected_pincode);
				
		}
		$scope.toggleCustomeBudgetArea = function(areas){
			if($scope.selected_pincode_showBudget_area[areas] == false){
				$scope.selected_pincode_showBudget_area[areas] = true;
				$scope.selected_pincode_showBudget[areas] = false;
			}else{
				$scope.selected_pincode_showBudget_area[areas] = false;
			}
		}
		$scope.toggleCustomeBudget = function(areas){
			console.log($scope.selected_pincode_showBudget);
			if($scope.selected_pincode_showBudget[areas] == false){
				$scope.selected_pincode_showBudget[areas] = true;
				$scope.selected_pincode_showBudget_area[areas] = false;
			}else{
				$scope.selected_pincode_showBudget[areas] = false;
			}
		}
		$rootScope.budgetValuesClick = function(budgetVal){ 
			$rootScope.life_time_arr["flexi_bud"] = budgetVal;
		}
		
		$scope.pincodeBudgetCheckbox = function(e,areas){
			if( $scope.pincode_selected_budget[areas] == true && $rootScope.companyTempInfo.data.pincode != areas ){
				if($scope.pincode_selected_non_all_area_pincode != undefined && $scope.pincode_selected_non_all_area_pincode != ''){					
					if($scope.selected_non_all_area_temp.indexOf(areas) > -1){
						var idx_non_area =   $scope.pincode_selected_non_all_area_pincode.indexOf(areas);
						$scope.pincode_selected_non_all_area_pincode.splice(idx_non_area,1);			
					}
				}
				if($scope.pincode_selected_all_area_pincode != undefined && $scope.pincode_selected_all_area_pincode != ''){
					if($scope.selected_all_area_temp.indexOf(areas) > -1){
						var idx_all_area =   $scope.pincode_selected_all_area_pincode.indexOf(areas);
						$scope.pincode_selected_all_area_pincode.splice(idx_all_area,1);			
					}		
				}
				console.log('pincode_selected_non_all_area_pincode after remove---->',$scope.pincode_selected_non_all_area_pincode);
				console.log('pincode_selected_all_area_pincode after remove---->',$scope.pincode_selected_all_area_pincode);
			}else if( $scope.pincode_selected_budget[areas] == false && $rootScope.companyTempInfo.data.pincode != areas ){				
				if($scope.pincode_selected_non_all_area_pincode != undefined && $scope.pincode_selected_non_all_area_pincode != ''){
					if($scope.selected_non_all_area_temp.indexOf(areas) > -1){
						var idx_non_area =   $scope.pincode_selected_non_all_area_pincode.indexOf(areas);
						if(idx_non_area == -1){
							$scope.pincode_selected_non_all_area_pincode.push(areas);
						}
					}					
				}
				if($scope.pincode_selected_all_area_pincode != undefined){
					if($scope.pincode_selected_all_area_pincode != undefined && $scope.pincode_selected_all_area_pincode != ''){
						if($scope.selected_all_area_temp.indexOf(areas) > -1){
							var idx_all_area =   $scope.pincode_selected_all_area_pincode.indexOf(areas);
							if(idx_all_area == -1){
								$scope.pincode_selected_all_area_pincode.push(areas);
							}
						}
					}
				}
				console.log('pincode_selected_non_all_area_pincode after add---->',$scope.pincode_selected_non_all_area_pincode);
				console.log('pincode_selected_all_area_pincode after add---->',$scope.pincode_selected_all_area_pincode);
			}
		}
		
		$scope.removeRowSelPin	=	function(event,pincode) {
			var idx	=	$scope.selectedVals.indexOf(pincode);
			$scope.selectedVals.splice(idx,1);
			var idx2=	$scope.selected.indexOf(pincode);
			$scope.selected.splice(idx2,1);
			$scope.checkAreas[pincode] = false;
		};
		
        
        $scope.removeRowSelPin_non_all_area = function(event,pincode){
			var idx =   $scope.selectedVals.indexOf(pincode);
            $scope.selectedVals.splice(idx,1);
            var idx2=   $scope.selected.indexOf(pincode);
            $scope.selected.splice(idx2,1);
            $scope.checkAreas[pincode] = false;
			var idx3=   $scope.previous_selected_zonal_pins.indexOf(pincode);
            $scope.previous_selected_zonal_pins.splice(idx3,1);
			if($scope.pincode_selected_non_all_area_pincode != undefined && $scope.pincode_selected_non_all_area_pincode != ''){
				var idx_new =   $scope.pincode_selected_non_all_area_pincode.indexOf(pincode);
				$scope.pincode_selected_non_all_area_pincode.splice(idx_new,1);			
				$scope.checkAreas_new[pincode] = false;
			}
		}
		
		$scope.removeRowSelPin_all_area = function(event,pincode){
			var idx =   $scope.selectedVals.indexOf(pincode);
            $scope.selectedVals.splice(idx,1);
            var idx2=   $scope.selected.indexOf(pincode);
            $scope.selected.splice(idx2,1);
            $scope.checkAreas[pincode] = false;
			if($scope.pincode_selected_all_area_pincode != undefined && $scope.pincode_selected_all_area_pincode != ''){
				var idx_new =   $scope.pincode_selected_all_area_pincode.indexOf(pincode);
				$scope.pincode_selected_all_area_pincode.splice(idx_new,1);
				$scope.checkAreas_all_area_new[pincode] = false;
			}
		}
        
        
        // to popluate 2.5 KM pincodes
		$scope.my_click_new_non_allarea = function(ev,flg){
			$scope.show_cat_click = 0;
			$scope.tabValue = '1';
			$scope.selectedAreaIdx  =   ''; 
			var sendAttr = {};
			sendAttr.sendAttr   =   'DIST';
			sendAttr.rds    =   '2.5';    
			$scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
			$scope.selRadio =   2.5;
			$scope.mainSelRadio =   2.5;
			sendAttr.pincode    =   $rootScope.companyTempInfo.data.pincode;
			//~ $scope.showLoader   =   0;
			$scope.allPinValsCity   =   [];
			$scope.my_var_pin = [];
			$scope.topMsg	=	"Please Select Desired Radius for Non All Area Categories";
			$scope.show_all_area_prev = 2;
			$scope.which_page = 'second';
			$scope.checkAreas_new[$rootScope.companyTempInfo.data.pincode]   =   true;
			$scope.pincode_selected_non_all_area_pincode.push($rootScope.companyTempInfo.data.pincode);
			
			
			if($scope.flexiSel == 1){
				$scope.pincode_selected_all_area_pincode = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
				$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
					   return inputArray.indexOf(item) == index;
				});
			}
		}
		/*To Populate 5 KM pincodes*/
		$scope.my_click_new_allarea = function(ev,flg){
			$scope.tabValue = '';
			$scope.selectedAreaIdx  =   ''; 
			$scope.show_cat_click = 0;
			APIServices.getContractData(returnState.paridInfo).success(function(response) {
				$rootScope.companyTempInfo  =   response;				
				APIServices.getCatPreviewData($rootScope.parentid,DATACITY,'ME').success(function(responseCats) {
					if(responseCats['error']['code'] != 0) {
						alert('There are no categories associated'); return false;
					}
					$scope.allAreaCt = 0;
					$scope.nonAllAreaCt = 0;
					if($scope.flexiSel == 1) {
						angular.forEach(responseCats['data'],function(value,key) {
							if(value['type'] == 'All Area' || value['type'] == 'Zonal' || value['type'] =='Superzone') {
								$scope.allAreaCt++;
							} else {
								$scope.nonAllAreaCt++;
							}
						});
					}
					
					var sendAttr    =   {};
					if($scope.allAreaCt >= $scope.nonAllAreaCt) {
						sendAttr.sendAttr   =   'DIST';
						sendAttr.rds    =   '5';
						$scope.showCatDistSel   =   1; //setting distance tabs according to all area categories
						$scope.selRadio	=	5;
						$scope.mainSelRadio	=	5;
						$scope.which_page = 'first';
					} else {
						sendAttr.sendAttr   =   'DIST';
						sendAttr.rds    =   '2.5';    
						$scope.showCatDistSel   =   2; //setting distance tabs according to non all area categories
						$scope.selRadio =   2.5;
						$scope.mainSelRadio =   2.5;
						$scope.already_disp_flg = 1;
						$scope.which_page = 'first';
					}
					var my_flg = 0;
					if($scope.flexiSel == 1 && $scope.allAreaCt > 0){
						$scope.topMsg	=	"Please Select Desired Radius for All Area Categories";
						sendAttr.sendAttr   =   'DIST';
						sendAttr.rds    =   '5';
						$scope.showCatDistSel   =   1; //setting distance tabs according to all area categories
						$scope.selRadio =   5;
						$scope.mainSelRadio =   5;
						my_flg = 1;
						$scope.already_disp_flg = 0;
						$scope.show_all_area_prev = 1;
						$scope.which_page = 'first';
					}
					
				});
				if($scope.flexiSel == 1){
					$scope.pincode_selected_all_area_pincode = $scope.pincode_selected_all_area_pincode.filter( function( item, index, inputArray ) {
						   return inputArray.indexOf(item) == index;
					});
					$scope.pincode_selected_non_all_area_pincode = $scope.pincode_selected_non_all_area_pincode.filter( function( item, index, inputArray ) {
						   return inputArray.indexOf(item) == index;
					});
				}
			});			
		}
        
		$rootScope.showDistData	=	function(selRadio) {
			$scope.areaDataDist	=	{};
			$scope.selRadio	=	selRadio;
			$scope.mainSelRadio	=	selRadio;
			var sendAttr	=	{};
			sendAttr.sendAttr	=	'DIST';
			sendAttr.rds	=	selRadio;
			sendAttr.pincode	=	$rootScope.companyTempInfo.data.pincode;
			$scope.showLoader	=	0;
			APIServices.getAreaPincodeInfo(sendAttr,returnState.paridInfo).success(function(response) {
				$scope.showLoader	=	1;
				$scope.areaDataDist	=	response;
			});
		};

		$rootScope.showBandData	=	function() {
			$scope.limiterBand	=	$scope.limiterBand+1;
		};
	});
});
