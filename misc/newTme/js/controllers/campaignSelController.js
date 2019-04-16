define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('campaignSelController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		$rootScope.layout = ''; // new Code Added for handling new Design
		$rootScope.parentid	=	returnState.paridInfo;
		$rootScope.extraHandler	=	$stateParams.page;
		$rootScope.setNoMenu	=	1;
		var self = this;
		$scope.showOptionLoader	=	1;
		$scope.tabNo	=	1;
		$scope.limitVals	=	[0];
		$scope.checkDisabled	=	[];
		$scope.selAllCheck	=	[];
		$scope.selectedTabNo    =   0;
		$rootScope.sugBudget    =   0;
		$scope.showFlexiPkg =   0;
		$scope.onlyExclusive = false;
		$scope.exactRenewal = false;
		var selected_opt=$cookieStore.get('campaign_str');
		var main_opt=$cookieStore.get('selected_option');
		if(selected_opt != undefined) {
			$scope.selected_arr = selected_opt.split(',');	
				$scope.sel_temp ="pdg";
				$scope.showFlexiPkg =   0;
                $scope.selectedTabNo    =   0;
                if($scope.selected_arr[0] != '2' && $scope.selected_arr[0] != '746') {
                    $scope.showFlexiPkg =   1;
                    $scope.selectedTabNo    =   3;
                    APIServices.checkMinPackageBudget(returnState.paridInfo).success(function(response) {
                        if(response['errorCode'] == 0) {
                            $rootScope.minPaymentFlexi  =   parseFloat(response['max_bg']);
                            $rootScope.sugBudget    =   parseFloat(response['sug_bg']);
                        }
                    });
                	}
		}else {
			$scope.sel_temp ="package";
		}
		
		//Check Downsell Request	
		APIServices.getversion(returnState.paridInfo,DATACITY).success(function(response) {
			$rootScope.budgetVersion    =   response.version;
			APIServices.checkDiscount(returnState.paridInfo,response.version).success(function(response) {
				if(response.error == 1) {
					$scope.stop_nxt = true;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Down Sell Request Present! Cant Proceed";
					return false;
				}
			});
        });		
		
		if($scope.selected_arr.indexOf("2") != -1 || $scope.selected_arr.indexOf("746") != -1 ||  $scope.selected_arr.indexOf("118") != -1){
			$scope.package_bck = 0;
		}else {
			$scope.package_bck = 1;
		}
			
		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
		
		$scope.optName		=	"Option";
		$scope.catLength	=	0;
		$scope.pinLength	=	0;
		$scope.oldCatLength	=	0;
		$scope.oldPinLength	=	0;
		$scope.limiter		=	10;
		$scope.limiterOuter	=	5;
		//Calling Service for contractInformation
		APIServices.getContractData(returnState.paridInfo).success(function(response) {
			$rootScope.companyTempInfo	=	response;
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
			$scope.topMsg	=	"Please Select Desired Campaign Type";
			if($scope.sel_temp =="pdg") {
				APIServices.getBudgetData(1,1,12,returnState.paridInfo,DATACITY,$rootScope.employees.hrInfo.data.empname,$rootScope.companyTempInfo.data.pincode,0,0,0).success(function(response) {
					$scope.showOptionLoader	=	0;
					$rootScope.typecount = {};
					$scope.catLengthWise	=	{};
					if(typeof response === 'object') {			
						$rootScope.bestBudget	=	response;
						if(response.result.renewal_cnt == 0) {
							$rootScope.showRenew	=	true;
						}
						
						if(($scope.moduleType=='TME' && USERID =='006492') || ($scope.moduleType == 'ME' && USERID =='006492')) {
							$rootScope.showExclusive	=	false;
						} else if($scope.moduleType=='CS'){
							$rootScope.showExclusive	=	false;
						} else {
							$rootScope.showExclusive	=	true;
						}
						angular.forEach(response.result.cat_desc,function(value,key) {
							$rootScope.typecount[key.toLowerCase()] = 0;
						});
						var l=0;
						var k = 0;
						angular.forEach(response.result.c_data,function(value,key) {
							var i=0;
                           	$scope.checkPins[key]  =   {};
							$scope.catLengthWise[key]	=	0;
							$scope.selAllCheck[k] = true;
							$scope.pinLength = 0;
							$scope.oldPinLength = 0;
							angular.forEach(value.pin_data,function(value2,key2) {
								$scope.loadInit(key,key2,value2.best_flg,i,l);
                               	$scope.checkPins[key][i] = true;
								i++;
								$scope.catLengthWise[key]++;
								$scope.pinLength++;
								$scope.oldPinLength++;
								l++;
								if(Object.size(value.pin_data) == i) {
									$scope.actualBudget	=	0;
									angular.forEach($scope.bestBudgetShow,function(value,key) {
										if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
											$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat($rootScope.bestBudget.result.c_data[key].f_bgt);
										} else {
											angular.forEach(value,function(value2,key2) {
												$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat(value2);
											});
										}
									});
									if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
										$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
									} else {
										$scope.cityFactor	=	1;
									}
									
									angular.forEach($scope.bestBudgetShow,function(value,key) {
										$scope.cattotalBudget[key]	=	$rootScope.bestBudget.result.c_data[key].f_bgt * $scope.cityFactor;;
									});
									
									if($rootScope.bestBudget.result.tb_bgt > $rootScope.bestBudget.result.city_bgt) {
										$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.tb_bgt;
									} else {
										$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
									}
								}
							});
							$scope.catLength++;
							$scope.oldCatLength++;
							angular.forEach($rootScope.typecount,function(val,key) {
								if(value.cst.toLowerCase() == key) {
									$rootScope.typecount[key] = val + 1;
								}
							});
							
							k++;
						});
						
						$scope.total =0;
						$scope.zonetotal =0;	
						$scope.alltotal = 0;
						angular.forEach($rootScope.typecount,function(val,key) {
							$scope.total += val;
							if(key =='l' || key =='sz') {
								$scope.zonetotal += val; 
							}else {
								$scope.alltotal += val;
							}
						});
						$scope.zoneper = Math.round(($scope.zonetotal/$scope.total)*100);
						$scope.allper = Math.round(($scope.alltotal/$scope.total)*100);
					} else {
						$rootScope.bestBudget	=	{};
						$rootScope.bestBudget['error_code']	=	2;
					}
				});
			}else{
				$scope.showOptionLoader	=	0;
			}
			
			//APIService for calling existing categories
			APIServices.getExistingCats(returnState.paridInfo,DATACITY).success(function(response) {
				$rootScope.dataExistCats	=	response;
			});
		});
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
		
		$scope.selRadio	=	1;
		$rootScope.selRadioTenure	=	'12-365';
		
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		//Handling for JDA
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://rakeshkotian.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://rakeshkotian.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$rootScope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}
		
		$scope.bestPositionShow	=	{};
		$scope.bestBudgetShow	=	{};
		$scope.bidderValue		=	{};
		$scope.cattotalBudget	=	{};
		$scope.bidValue			=	{};
		$scope.inventory		=	{};
		$scope.callcount		=	{};
		$scope.callcountTotal	=	{};
		$scope.callcountTotalFix=	{};
		$scope.searchcount		=	{};
		$scope.searchcountTotal	=	{};
		$scope.searchcountTotalFix=	{};
		$scope.selected			=	[];
		$scope.totalBudgetShowMain	=	"";
		$scope.actualBudget		=	{};
		var catKeyOld	=	"";
		$scope.pushDisabled	=	{};
		$scope.catListRemove	=	[];
		$scope.callCountTotalTop	=	0;
		$scope.callCountTotalTopExtra	=	0;
		$scope.searchCountTotalTop	=	0;
		$scope.searchCountTotalTopExtra	=	0;
		$scope.cityFactor	=	0;
		$scope.loadInit	=	function(catKey,pin,bestBudg,index,index2) {
			switch($scope.tabNo) {
				case 1:
					$scope.setArrowLimiter	=	1;
				break;
				case 2:
					$scope.setArrowLimiter	=	1;
				break;
				case 3:
					$scope.setArrowLimiter	=	1;
				break;
				case 4:
					$scope.setArrowLimiter	=	1;
				break;
				case 5:
					$scope.setArrowLimiter	=	0;
				break;
			}
			if((catKeyOld	!=	catKey) ||  ($scope.bestPositionShow[catKey] === undefined)) {
				$scope.bestPositionShow[catKey]	=	{};
				$scope.bestBudgetShow[catKey]	=	{};
				$scope.stopEdit[catKey]			=	{};
				$scope.bidderValue[catKey]		=	{};
				$scope.bidValue[catKey]		=	{};
				$scope.inventory[catKey]		=	{};
				$scope.selected[catKey]	=	[];
				$scope.pushDisabled[catKey]	=	{};
				$scope.callcount[catKey]	=	{};
				$scope.searchcount[catKey]	=	{};
			}
			if(index == 0) {
				$scope.callcountTotal[catKey]	=	0;
				$scope.searchcountTotal[catKey]	=	0;
				$scope.callcountTotalFix[catKey]	=	0;
				$scope.searchcountTotalFix[catKey]	=	0;
			}
			if(index2 == 0) {
				$scope.callCountTotalTop	=	0;
				$scope.callCountTotalTopExtra	=	0;
				$scope.searchCountTotalTop	=	0;
				$scope.searchCountTotalTopExtra	=	0;
			}
			
			$scope.bestPositionShow[catKey][pin]	=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].best_flg;
			$scope.bestBudgetShow[catKey][pin]	=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].budget;
			$scope.bidderValue[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidder;
			$scope.bidValue[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidvalue;
			$scope.inventory[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].inv_avail;
			$scope.pushDisabled[catKey][pin]	=	false;
			$scope.callcount[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.callcountTotal[catKey]		=	$scope.callcountTotal[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.searchcount[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].srch_cnt_f;
			$scope.searchcountTotal[catKey]		=	$scope.searchcountTotal[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].srch_cnt_f;
			$scope.callcountTotalFix[catKey]	=	$scope.callcountTotalFix[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.searchcountTotalFix[catKey]	=	$scope.searchcountTotalFix[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].srch_cnt_f;
			$scope.stopEdit[catKey][index]	=	0;
			$scope.actualBudget	=	0;
			catKeyOld	=	catKey;
			$scope.callCountTotalTop		=	$scope.callCountTotalTop+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.callCountTotalTopExtra	=	$scope.callCountTotalTopExtra+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.searchCountTotalTop		=	$scope.searchCountTotalTop+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].srch_cnt_f;
			$scope.searchCountTotalTopExtra	=	$scope.searchCountTotalTopExtra+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].srch_cnt_f;
			if(pin == $rootScope.companyTempInfo.data.pincode) {
				$scope.pushDisabled[catKey][pin]	=	true;
			}
		};
		
		//Function to stop auto sorting by ng-repeat in angular js
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		
		$scope.lowPos	=	function(catKey,pin,currPos,upperIndex,index) {
			$scope.cityFactor	=	0;
			if(currPos	!=	$scope.setArrowLimiter) {
				var lengthArr	=	Object.size($rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos);
				var legthPins	=	Object.size($rootScope.bestBudget.result.c_data[catKey].pin_data);
				if($rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)-1] !== undefined) {
					$scope.actualBudget	=	0;
					$scope.bestPositionShow[catKey][pin]	=	currPos-1;
					$scope.bestBudgetShow[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)-1].budget;
					$scope.inventory[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)-1].inv_avail;
					$scope.bidderValue[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)-1].bidder;
				} else {
					$scope.lowPos(catKey,pin,currPos-1,upperIndex,index);
					return false;
				}
				
				$scope.cattotalBudget[catKey]	=	0;
				
				if(pin	==	$rootScope.companyTempInfo.data.pincode) {
					if($scope.inventory[catKey][pin] == 0) {
						if((currPos-1) == 1) {
							$scope.highPos(catKey,pin,currPos-1,upperIndex,index);
						} else {
							$scope.lowPos(catKey,pin,currPos-1,upperIndex,index);
						}
					}
				} else {
					if($scope.inventory[catKey][pin]	==	0) {
                      				$scope.checkPins[catKey][index] =   false;
						var idx = $scope.selected[catKey].indexOf(pin);
						if(idx == -1) {
							$scope.selected[catKey].push(pin);
							$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]-$scope.callcount[catKey][pin];
							$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]-$scope.searchcount[catKey][pin];
						}
						$scope.pushDisabled[catKey][pin]	=	true;
					} else {
                        			$scope.checkPins[catKey][index] =   true;
						var idx = $scope.selected[catKey].indexOf(pin);
						if(idx > -1) {
							$scope.selected[catKey].splice(idx, 1);
							$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]+$scope.callcount[catKey][pin];
							$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]+$scope.searchcount[catKey][pin];
						}
						$scope.pushDisabled[catKey][pin]	=	false;
					}
				}
				
				var oldKey	=	"";
				$scope.actualBudget	=	0;
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					if($scope.cattotalBudget[key] != 0 || catKey == key) { 
						angular.forEach(value,function(value2,key2) {
							if((oldKey	==	key) || oldKey === undefined) {
								var idx2 = $scope.selected[key].indexOf(key2);
								if(idx2 == -1) {
									$scope.cattotalBudget[key]	=	parseFloat($scope.cattotalBudget[key])+parseFloat(value2);
								}
							} else {
								var idx2 = $scope.selected[key].indexOf(key2);
								if(idx2 == -1) {
									$scope.cattotalBudget[key]	=	0;
									$scope.cattotalBudget[key]	=	parseFloat(value2);
								} else {
									$scope.cattotalBudget[key]	=	0;
								}
							}
							oldKey	=	 key;
						});
						if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
							if($scope.cattotalBudget[key] < $rootScope.bestBudget.result.c_data[key].bm_bgt) {
								$scope.cattotalBudget[key]	=	$rootScope.bestBudget.result.c_data[key].bm_bgt;
							}
						}
					}
					$scope.actualBudget	=	$scope.actualBudget + $scope.cattotalBudget[key];
				});
				
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				if($rootScope.bestBudget.result.c_data[catKey].bflg == 1) {
					if($scope.cattotalBudget[catKey] < $rootScope.bestBudget.result.c_data[catKey].bm_bgt) {
						$scope.cattotalBudget[catKey]	=	$rootScope.bestBudget.result.c_data[catKey].bm_bgt;
					}
				}
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					$scope.cattotalBudget[key]	=	$scope.cattotalBudget[key] * $scope.cityFactor;
				});
				$scope.totalBudgetShowMain	=	0;
				angular.forEach($scope.cattotalBudget,function(value,key) {
					$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value);
				});
				
				if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
					$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
				}
			}
		};

        $rootScope.takeBacktoCat    =   function(parentid) {
            localStorage.setItem('campCat',1);
            $state.go('appHome.catpreview',{parid:parentid});
        };

        $scope.lowPosPack = function(catKey,pin,currPos,upperIndex,index) {
            var bidderCt = 0;
            if(typeof $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['pos'] !== 'undefined') {
                angular.forEach($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['pos'],function(valuePos,keyPos) {
                    if(keyPos != 100) {
                        if(typeof valuePos['bidder'] !== 'undefined') {
                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                bidderCt++;
                            }
                        }
                    }
                });
            }
            if((currPos-1) > bidderCt) {
                var actualPos = (parseInt($scope.flexiBudgetCatPos[catKey][pin])-2)-bidderCt;
                if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'] == null || typeof $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'][actualPos] === 'undefined') {
                    if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid'] == null) {
                        var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bgt'])/12));    
                    } else {
                        if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_pos'] <= $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid'].length) {
                            var lastBudget = Math.min.apply(Math, $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid']);
                            var flexiBgt  = ((parseFloat(lastBudget)*365/12)-1);    
                        } else {
                            var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bgt'])/12));    
                        }    
                    }    
                    $scope.flexiBudgetCatPin[catKey][pin]  =   flexiBgt.toFixed(2);        
                } else {
                    var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'][actualPos]['bpd'])*365/12)+1);
                    $scope.flexiBudgetCatPin[catKey][pin]  =   flexiBgt.toFixed(2);    
                }
                $scope.actualBudget =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                            $scope.actualBudget   =   $scope.actualBudget+parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin]);
                        } else {
                            $scope.actualBudget   =   $scope.actualBudget+0;
                        }
                    });
                });
                if($scope.actualBudget <= 0) {
                    $scope.actualBudget =   $rootScope.minPaymentFlexi;
                }
                if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                
                $scope.totalBudgetShowMain  =   0;
                var j = 0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.cattotalBudget[keyCat]   =   0;
                    var i = 0;
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        $scope.cattotalBudget[keyCat]   =   ($scope.cattotalBudget[keyCat]+(parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin])*$scope.calcFactor));
                        if(keyCat != catKey || keyPin != pin) {
                            $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                        }
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] <= 0) {
                            var idx = $scope.selected[keyCat].indexOf(keyPin);
                            if(idx  ==  -1) {
                                $scope.selected[keyCat].push(keyPin);
                            }
                            $scope.checkPins[keyCat][i] = false;
                        } else {
                            var idx = $scope.selected[keyCat].indexOf(keyPin);
                            if(idx  >  -1) {
                                $scope.selected[keyCat].splice(idx,1);
                            }
                            $scope.checkPins[keyCat][i] = true;
                        }
                        i++;
                    });
                    $scope.totalBudgetShowMain  =   parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.cattotalBudget[keyCat]);
                    j++;
                });
                $scope.actualBudget     =   $scope.totalBudgetShowMain;
                $scope.customBudgetVal  =   $scope.actualBudget;

                $scope.flexiBudgetCatPos[catKey][pin]   =  currPos-1;
                $scope.exitPackVal  =   {};
                $scope.exitPackVal[catKey] =   {};
                $scope.exitPackVal[catKey][pin]    =   1;
            } else {
                alert("Above positions are not biddable"); 
            }
        }
		
		$scope.highPos	=	function(catKey,pin,currPos,upperIndex,index) {
			$scope.cityFactor	=	0;
			
			if($rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)+1] !== undefined) {
				$scope.bestPositionShow[catKey][pin]	=	parseInt(currPos)+1;
				$scope.bestBudgetShow[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)+1].budget;
				$scope.inventory[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)+1].inv_avail;
				$scope.bidderValue[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[parseInt(currPos)+1].bidder;
			} else {
				$scope.bestPositionShow[catKey][pin]	=	100;
				$scope.bestBudgetShow[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[100].budget;
				$scope.inventory[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[100].inv_avail;
				$scope.bidderValue[catKey][pin]			=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[100].bidder;
			}
			
			$scope.cattotalBudget[catKey]	=	0;
			
			if(pin	==	$rootScope.companyTempInfo.data.pincode) {
				if($scope.inventory[catKey][pin] == 0) {
					$scope.highPos(catKey,pin,currPos+1,upperIndex,index);
				}
			} else {
				if($scope.inventory[catKey][pin]	==	0) {
                    $scope.checkPins[catKey][index] =   false;
					var idx = $scope.selected[catKey].indexOf(pin);
					if(idx == -1) {
						$scope.selected[catKey].push(pin);
						$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]-$scope.callcount[catKey][pin];
						$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]-$scope.searchcount[catKey][pin];
					}
					$scope.pushDisabled[catKey][pin]	=	true;
				} else {
                   	$scope.checkPins[catKey][index] =   true;
					var idx = $scope.selected[catKey].indexOf(pin);
					if(idx > -1) {
						$scope.selected[catKey].splice(idx, 1);
						$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]+$scope.callcount[catKey][pin];
						$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]+$scope.searchcount[catKey][pin];
					}
					$scope.pushDisabled[catKey][pin]	=	false;
				}
			}
			
			var oldKey	=	"";
			$scope.actualBudget	=	0;
			angular.forEach($scope.bestBudgetShow,function(value,key) {
				if($scope.cattotalBudget[key] != 0 || catKey == key) {
					angular.forEach(value,function(value2,key2) {
						if((oldKey	==	key) || oldKey === undefined) {
							var idx2 = $scope.selected[key].indexOf(key2);
							if(idx2 == -1) {
								$scope.cattotalBudget[key]	=	parseFloat($scope.cattotalBudget[key])+parseFloat(value2);
							}
						} else {
							var idx2 = $scope.selected[key].indexOf(key2);
							if(idx2 == -1) {
								$scope.cattotalBudget[key]	=	0;
								$scope.cattotalBudget[key]	=	parseFloat(value2);
							} else {
								$scope.cattotalBudget[key]	=	0;
							}
						}
						oldKey	=	 key;
					});
					if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
						if($scope.cattotalBudget[key] < $rootScope.bestBudget.result.c_data[key].bm_bgt) {
							$scope.cattotalBudget[key]	=	$rootScope.bestBudget.result.c_data[key].bm_bgt;
						}
					}
				}
				$scope.actualBudget	=	$scope.actualBudget + $scope.cattotalBudget[key];
			});
			
			if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
				$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
			} else {
				$scope.cityFactor	=	1;
			}
			
			if($rootScope.bestBudget.result.c_data[catKey].bflg == 1) {
				if($scope.cattotalBudget[catKey] < $rootScope.bestBudget.result.c_data[catKey].bm_bgt) {
					$scope.cattotalBudget[catKey]	=	$rootScope.bestBudget.result.c_data[catKey].bm_bgt;
				}
			}
			
			angular.forEach($scope.bestBudgetShow,function(value,key) {
				$scope.cattotalBudget[key]	=	$scope.cattotalBudget[key] * $scope.cityFactor;
			});
			
			$scope.totalBudgetShowMain	=	0;
			angular.forEach($scope.cattotalBudget,function(value,key) {
				$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value);
			});
			
			if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
				$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
			}
		};

        $scope.highPosPack = function(catKey,pin,currPos,upperIndex,index) {
            var bidderCt = 0;
            if(typeof $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['pos'] !== 'undefined') {
                angular.forEach($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['pos'],function(valuePos,keyPos) {
                    if(keyPos != 100) {
                        if(typeof valuePos['bidder'] !== 'undefined') {
                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                bidderCt++;
                            }
                        }
                    }
                });
            }
            var actualPos = (parseInt($scope.flexiBudgetCatPos[catKey][pin]))-bidderCt;
            /*if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'] == null || typeof $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'][actualPos] === 'undefined') {
                alert("You are on the last position. No lower bids available");
            } else {*/
                if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'] == null || typeof $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'][actualPos] === 'undefined') {
                    if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid'] == null) {
                        var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bgt'])/12));    
                    } else {
                        if($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_pos'] <= $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid'].length) {
                            var lastBudget = Math.min.apply(Math, $rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bid']);
                            var flexiBgt  = ((parseFloat(lastBudget)*365/12)-1);    
                        } else {
                            var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bgt'])/12));    
                        }  
                    }
                    $scope.flexiBudgetCatPin[catKey][pin]  =   flexiBgt.toFixed(2);
                } else {
                    var flexiBgt  = ((parseFloat($rootScope.bestBudget.result.c_data[catKey]['pin_data'][pin]['flexi_bidder'][actualPos]['bpd'])*365/12)+1);
                    $scope.flexiBudgetCatPin[catKey][pin]  =   flexiBgt.toFixed(2);
                }

                $scope.actualBudget =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                            $scope.actualBudget   =   $scope.actualBudget+parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin]);
                        } else {
                            $scope.actualBudget   =   $scope.actualBudget+0;
                        }
                    });
                });
                if($scope.actualBudget <= 0) {
                    $scope.actualBudget =   $rootScope.minPaymentFlexi;
                }
                if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                
                $scope.totalBudgetShowMain  =   0;
                var j = 0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.cattotalBudget[keyCat]   =   0;
                    var i = 0;
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        $scope.cattotalBudget[keyCat]   =   ($scope.cattotalBudget[keyCat]+(parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin])*$scope.calcFactor));
                        if(keyCat != catKey || keyPin != pin) {
                            $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                        }
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] <= 0) {
                            var idx = $scope.selected[keyCat].indexOf(keyPin);
                            if(idx  ==  -1) {
                                $scope.selected[keyCat].push(keyPin);
                            }
                            $scope.checkPins[keyCat][i] = false;
                        } else {
                            var idx = $scope.selected[keyCat].indexOf(keyPin);
                            if(idx  >  -1) {
                                $scope.selected[keyCat].splice(idx,1);
                            }
                            $scope.checkPins[keyCat][i] = true;
                        }
                        i++;
                    });
                    $scope.totalBudgetShowMain  =   parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.cattotalBudget[keyCat]);
                    j++;
                });
                
                $scope.actualBudget     =   $scope.totalBudgetShowMain;
                $scope.customBudgetVal  =   $scope.actualBudget;

                $scope.flexiBudgetCatPos[catKey][pin]   =  currPos+1;
                $scope.exitPackVal  =   {};
                $scope.exitPackVal[catKey] =   {};
                $scope.exitPackVal[catKey][pin]    =   1;
            //}
        }
		
		
		$scope.checkPins	=	[];
		$scope.checkPins[0]	=	[];
		$scope.stopEdit		=	[];
		$scope.toggle	=	function(currKey,index,catKey,pin) {
			if($scope.selected[catKey] === undefined) {
				$scope.selected[catKey]	=	[];
			}
			$scope.catLength	=	0;
			$scope.actualBudget	=	0;
			if($scope.checkPins[catKey][index]) {
				var idx = $scope.selected[catKey].indexOf(pin);
				if(idx	==	-1) {
					$scope.selected[catKey].push(pin);
				}
				$scope.cattotalBudget[catKey]	=	parseFloat($scope.cattotalBudget[catKey])	-	parseFloat($scope.bestBudgetShow[catKey][pin]);
				//$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) - parseFloat($scope.bestBudgetShow[catKey][pin]);
				$scope.stopEdit[catKey][index]	=	1;
				$scope.pinLength	=	$scope.pinLength -1;
				$scope.catLengthWise[catKey]--;
				$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]-$scope.callcount[catKey][pin];
				$scope.callCountTotalTop	=	$scope.callCountTotalTop-$scope.callcount[catKey][pin];
				$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]-$scope.searchcount[catKey][pin];
				$scope.searchCountTotalTop	=	$scope.searchCountTotalTop-$scope.searchcount[catKey][pin];
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					if($scope.cattotalBudget[key] == 0 && catKey != key) {
						var setOption	=	1;
					} else {
						var setOption	=	0;
					}
					
					angular.forEach(value,function(value3,key3) {
						if($scope.selected[key].indexOf(key3) == -1 && (setOption == 0)) {
							$scope.actualBudget			=	parseFloat($scope.actualBudget) + parseFloat(value3);
						}
					});					
				});
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				
				angular.forEach($scope.bestBudgetShow,function(value2,key2) {
					if($scope.cattotalBudget[key2] == 0 && catKey != key2) {
						var setOption	=	1;
					} else {
						var setOption	=	0;
					}
					$scope.cattotalBudget[key2]	=	0;
					angular.forEach(value2,function(value3,key3) {
						if($scope.selected[key2].indexOf(key3) == -1 && (setOption == 0)) {
							$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
						}
					});					
				});
			} else {
				var idx = $scope.selected[catKey].indexOf(pin);
				$scope.selected[catKey].splice(idx, 1);
				$scope.pinLength	=	$scope.pinLength -$scope.catLengthWise[catKey];
				$scope.catLengthWise[catKey]	=	0;
				//~ angular.forEach($scope.bestBudgetShow[catKey],function(value,srchKey) {
					//~ var idx2 = $scope.selected[catKey].indexOf(srchKey);
					//~ if(idx2 == -1) {
						//~ $scope.cattotalBudget[catKey]	=	parseFloat($scope.cattotalBudget[catKey])+parseFloat(value * $scope.cityFactor);
						//~ $scope.catLengthWise[catKey]++;
					//~ }
				//~ });
				$scope.pinLength	=	$scope.pinLength +$scope.catLengthWise[catKey];
				//$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.bestBudgetShow[catKey][pin]);
				$scope.stopEdit[catKey][index]	=	0;
				$scope.selAllCheck[currKey]	=	true;
				var index	=	$scope.catListRemove.indexOf(catKey);
				if(index > -1) {
					$scope.catListRemove.splice(index,1);
				}
				$scope.callcountTotal[catKey]	=	$scope.callcountTotal[catKey]+$scope.callcount[catKey][pin];
				$scope.callCountTotalTop	=	$scope.callCountTotalTop+$scope.callcount[catKey][pin];
				$scope.searchcountTotal[catKey]	=	$scope.searchcountTotal[catKey]+$scope.searchcount[catKey][pin];
				$scope.searchCountTotalTop	=	$scope.searchCountTotalTop+$scope.searchcount[catKey][pin];
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					if($scope.cattotalBudget[key] == 0  && catKey != key) {
						var setOption	=	1;
					} else {
						var setOption	=	0;
					}
					
					angular.forEach(value,function(value3,key3) {
						var idx2 = $scope.selected[key].indexOf(key3);
						if(idx2 == -1 && setOption == 0) {
							$scope.actualBudget			=	parseFloat($scope.actualBudget) + parseFloat(value3);
						}
					});					
				});
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				
				angular.forEach($scope.bestBudgetShow,function(value2,key2) {
					if($scope.cattotalBudget[key2] == 0 && catKey != key2) {
						var setOption	=	1;
					} else {
						var setOption	=	0;
					}
					$scope.cattotalBudget[key2]	=	0;
					angular.forEach(value2,function(value3,key3) {
						var idx2 = $scope.selected[key2].indexOf(key3);
						if(idx2 == -1  && (setOption == 0)) {
							$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
						}
					});					
				});
			}
			
			$scope.totalBudgetShowMain	=	0;
			angular.forEach($scope.cattotalBudget,function(value,key) {
				$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value);
			});
			if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
				$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
			}
			
			angular.forEach($scope.bestBudgetShow,function(value,key) {
				var index	=	$scope.catListRemove.indexOf(key);
				if(index == -1) {
					$scope.catLength++;
				}
			});
        };
        $scope.exitPackVal  =   {};
        $scope.togglePinPackage =   function(currKey,index,catKey,pin) {
            if($scope.checkPins[catKey][index]) {
                var idx = $scope.selected[catKey].indexOf(pin);
                if(idx  ==  -1) {
                    $scope.selected[catKey].push(pin);
                }
                
                $scope.actualBudget =   0;
                if($scope.totalBudgetShowMain <= 0) {
                    $scope.actualBudget = $scope.flexiBudgetCatPin[catKey][pin];    
                } else {
                    $scope.actualBudget = $scope.totalBudgetShowMain - $scope.flexiBudgetCatPin[catKey][pin];    
                }
                $scope.flexiBudgetCatPin[catKey][pin]   =   0;
                if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);    
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                $scope.totalBudgetShowMain  =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    var i = 0;
                    $scope.cattotalBudget[keyCat]   =   0;
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        if(typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                            var calcFacVal  =   $scope.flexiBudgetCatPin[keyCat][keyPin];
                            $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+(parseFloat(calcFacVal)*$scope.calcFactor);
                            $scope.flexiBudgetCatPin[keyCat][keyPin]    =   (parseFloat(valuePin)*$scope.calcFactor).toFixed(2);
                            var bidderCt = 0;
                            if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                                angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                    if(keyPos != 100) {
                                        if(typeof valuePos['bidder'] !== 'undefined') {
                                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                                bidderCt++;
                                            }
                                        }
                                    }
                                });
                            }
                            if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                                var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                                $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                            }
                        }
                    });
                    $scope.totalBudgetShowMain      =   parseFloat($scope.totalBudgetShowMain)+parseFloat($scope.cattotalBudget[keyCat]);
                });
                $scope.pinLength = $scope.pinLength-1;
                $scope.stopEdit[catKey][index]  =   1;
                if($scope.pinLength == 0 && $scope.catListRemove.indexOf(catKey) > -1) {
                    $scope.catListRemove.push(catKey);
                    $scope.catLength    =   $scope.catLength -1;
                    $scope.selAllCheck[currKey] =   false;;
                }
            } else {
                var idx = $scope.selected[catKey].indexOf(pin);
                if(idx  >  -1) {
                    $scope.selected[catKey].splice(idx, 1);
                }
                $scope.flexiBudgetCatPin[catKey][pin]   =   (parseFloat($rootScope.bestBudget['result']['c_data'][catKey]['pin_data'][pin]['flexi_bgt'])/12).toFixed(2);
                if($scope.catListRemove.indexOf(catKey) > -1) {
                    $scope.flexiBudgetCatPin[catKey][$rootScope.companyTempInfo.data.pincode]   =   (parseFloat($rootScope.bestBudget['result']['c_data'][catKey]['pin_data'][$rootScope.companyTempInfo.data.pincode]['flexi_bgt'])/12).toFixed(2);
                    var idxCurrPin = $scope.selected[catKey].indexOf($rootScope.companyTempInfo.data.pincode);
                    if(idxCurrPin  >  -1) {
                        $scope.selected[catKey].splice(idxCurrPin, 1);
                    }
                }
                
                if($scope.totalBudgetShowMain <= 0) {
                    if($scope.catListRemove.indexOf(catKey) > -1) {
                        $scope.actualBudget = parseFloat($scope.flexiBudgetCatPin[catKey][pin])+parseFloat($scope.flexiBudgetCatPin[catKey][$rootScope.companyTempInfo.data.pincode]);    
                    } else {
                        $scope.actualBudget = parseFloat($scope.flexiBudgetCatPin[catKey][pin]);    
                    }
                } else {
                    if($scope.catListRemove.indexOf(catKey) > -1) {
                        $scope.actualBudget = parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.flexiBudgetCatPin[catKey][pin]) + parseFloat($scope.flexiBudgetCatPin[catKey][$rootScope.companyTempInfo.data.pincode]);    
                    } else {
                        $scope.actualBudget = parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.flexiBudgetCatPin[catKey][pin]);
                    }
                }
                
                if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);    
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                $scope.totalBudgetShowMain  =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    var z = 0;
                    $scope.cattotalBudget[keyCat]   =   0;
                    angular.forEach($scope.flexiBudgetCatPin[keyCat],function(valuePin,keyPin) {
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                            var calcFacVal  =   $scope.flexiBudgetCatPin[keyCat][keyPin];
                            $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+(parseFloat(calcFacVal)*$scope.calcFactor);
                            $scope.flexiBudgetCatPin[keyCat][keyPin]    =   (parseFloat(valuePin)*$scope.calcFactor).toFixed(2);
                            var bidderCt = 0;
                            if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                                angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                    if(keyPos != 100) {
                                        if(typeof valuePos['bidder'] !== 'undefined') {
                                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                                bidderCt++;
                                            }
                                        }
                                    }
                                });
                            }
                            if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                                var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                                $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                            }
                        }
                        if($scope.catListRemove.indexOf(catKey) > -1) {
                            if(keyCat == catKey && keyPin == $rootScope.companyTempInfo.data.pincode) {
                                $scope.checkPins[catKey][z] = true;
                            }
                        }
                        z++;
                    });
                    $scope.totalBudgetShowMain      =   parseFloat($scope.totalBudgetShowMain)+parseFloat($scope.cattotalBudget[keyCat]);
                });
                $scope.stopEdit[catKey][index]  =   0;
                $scope.pinLength = $scope.pinLength+1;
                if($scope.catListRemove.indexOf(catKey) > -1) {
                    var idx = $scope.catListRemove.indexOf(catKey);
                    $scope.catListRemove.splice(idx,1);
                    $scope.catLength    =   $scope.catLength +1;
                    $scope.selAllCheck[currKey] =   true;
                }
            }
            $scope.actualBudget =   $scope.totalBudgetShowMain;
            $scope.customBudgetVal  =   $scope.actualBudget;
            $scope.exitPackVal  =   {};
            $scope.exitPackVal[catKey] =   {};
            $scope.exitPackVal[catKey][pin]    =   1;
            var pinTrue = {};
            var j = 0;
            angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                pinTrue[keyCat] =   Object.size($scope.checkPins[j]);
                var i = 0;
                angular.forEach(valueCat,function(valuePin,keyPin) {					
                    if($scope.checkPins[keyCat][i] == false) {
                        pinTrue[keyCat]--;
                    }
                    i++;
                });
                j++;
            });
            var pincodeLen = 0;
            var pinCounter = 0;
            angular.forEach(pinTrue,function(value,key) {
                if(pinTrue[key] > pinCounter) {
                    pincodeLen  =   pinTrue[key];
                    pinCounter  =   pinTrue[key];   
                }
            });
            $scope.pinLength =  pincodeLen; 
		};
		
		$scope.imgButAcc	=	[];
        $scope.showImg  =   function(index,keyMain) {
			if(index=='0') {
                $scope.imgButAcc[keyMain] =   "img/ic_remove_circle_24px.svg";
			} else {
                $scope.imgButAcc[keyMain] =   "img/ic_add_circle_24px.svg";
			}
			//$scope.imgButAcc[index]	=	"img/ic_add_circle_24px.svg";
		};
		
      	$scope.clickshrink  =   function(event,index,keyMain) {
			$scope.limitVals.push(index);
			if (false === $(event.target).closest('.budgetPinDiv').find('.dataPinsInt').is(':visible')) {
				//$('.dataPinsInt').hide();
			}
			$(event.target).closest('.budgetPinDiv').find('.dataPinsInt').toggle();
            
			if($(event.target).closest('.budgetPinDiv').find('.dataPinsInt').css('display') == 'block') {
               			$scope.imgButAcc[keyMain] =   "img/ic_remove_circle_24px.svg";
			} else if($(event.target).closest('.budgetPinDiv').find('.dataPinsInt').css('display') == undefined){
                		$scope.imgButAcc[keyMain] =   "img/ic_remove_circle_24px.svg";
			} else {
               			 $scope.imgButAcc[keyMain] =   "img/ic_add_circle_24px.svg";
			}
		};
		
		$scope.expAll	=	function() {
			$('.dataPinsInt').removeClass('none');
			for(var i=0;i<=$('.budgetPinDiv').length;i++) {
				$scope.imgButAcc[i]	=	"img/ic_remove_circle_24px.svg";
			}
		};
		
		$scope.collapseAll	=	function() {
			$('.dataPinsInt').addClass('none');
			for(var i=0;i<=$('.budgetPinDiv').length;i++) {
				$scope.imgButAcc[i]	=	"img/ic_add_circle_24px.svg";
			}
		};
		
		$scope.selectAll	=	function(event,upperId,catKey) {
            $scope.checkPins[catKey]   =   [];
			$scope.actualBudget	=	0;
			if($scope.selAllCheck[upperId]) {
				$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) - parseFloat($scope.cattotalBudget[catKey]);
				$scope.cattotalBudget[catKey]	=	0;
				var k = 0;
				angular.forEach($scope.bestBudgetShow[catKey],function(value,key) {
					if($rootScope.companyTempInfo.data.pincode != key) {
						var idx = $scope.selected[catKey].indexOf(key);
						if(idx == -1) {
							$scope.selected[catKey].push(key);
						}
                        			$scope.checkPins[catKey][k]    =   false;
					} else {
                        			$scope.checkPins[catKey][k]    =   true;
					}
					$scope.stopEdit[catKey][k]	=	1;
					k++;
				});
				
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					if(key != catKey && $scope.cattotalBudget[key] != 0) {
						angular.forEach(value,function(value3,key3) {
							$scope.actualBudget			=	parseFloat($scope.actualBudget) + parseFloat(value3);
						});
					}					
				});
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				
				angular.forEach($scope.bestBudgetShow,function(value2,key2) {
					if(catKey != key2 && $scope.cattotalBudget[key2] != 0) {
						$scope.cattotalBudget[key2]	=	0;
						angular.forEach(value2,function(value3,key3) {
							if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
								$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
							} else {
								if($scope.selected[key2].indexOf(key3) == -1) {
									$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);	
								}
							}
						});						
					}
				});
				
				$scope.pinLength	=	$scope.pinLength - $scope.catLengthWise[catKey];			
				$scope.catLengthWise[catKey]	=	0;
				if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
					$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
				}
				$scope.catLength	=	$scope.catLength -1;
				$scope.callCountTotalTop	=	$scope.callCountTotalTop-$scope.callcountTotal[catKey];
				$scope.callcountTotal[catKey]	=	0;
				$scope.searchCountTotalTop	=	$scope.searchCountTotalTop-$scope.searchcountTotal[catKey];
				$scope.searchcountTotal[catKey]	=	0;
				$scope.catListRemove.push(catKey);
			} else {
				var k=0;
				angular.forEach($scope.bestBudgetShow[catKey],function(value,key) {
					$scope.cattotalBudget[catKey]	=	$scope.cattotalBudget[catKey]+value;
					var idx = $scope.selected[catKey].indexOf(key);
					if(idx > -1) {
						$scope.selected[catKey].splice(idx, 1);
					}
					if($scope.inventory[catKey][key] == 0) {
                        			$scope.checkPins[catKey][k]    =   false;
					}
                    			$scope.checkPins[catKey][k]    =   true;
					$scope.stopEdit[catKey][k]	=	0;
					$scope.catLengthWise[catKey]++;
					k++;
				});
				
				$scope.pinLength	=	$scope.pinLength+$scope.catLengthWise[catKey];	
				if($rootScope.bestBudget.result.c_data[catKey].bflg == 1) {
					if($scope.cattotalBudget[catKey] < $rootScope.bestBudget.result.c_data[catKey].bm_bgt) {
						$scope.cattotalBudget[catKey]	=	$rootScope.bestBudget.result.c_data[catKey].bm_bgt;
					}
				}
				$scope.totalBudgetShowMain	=	0;
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					angular.forEach(value,function(value3,key3) {
						if($scope.cattotalBudget[key] != 0) {
							$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value3);
							$scope.actualBudget			=	parseFloat($scope.actualBudget) + parseFloat(value3);
						}
					});					
				});
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				
				angular.forEach($scope.bestBudgetShow,function(value2,key2) {
					if(parseFloat($scope.cattotalBudget[key2]) > 0) {
						$scope.cattotalBudget[key2]	=	0;
						angular.forEach(value2,function(value3,key3) {
							if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
								$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
							} else {
								if($scope.selected[key2].indexOf(key3) == -1) {
									$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
								}
							}
						});						
					}
				});
				$scope.callcountTotal[catKey]	=	$scope.callcountTotalFix[catKey];
				$scope.searchcountTotal[catKey]	=	$scope.searchcountTotalFix[catKey];
				
				if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
					$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
				}
				$scope.callCountTotalTop	=	$scope.callCountTotalTop+$scope.callcountTotalFix[catKey];
				$scope.searchCountTotalTop	=	$scope.searchCountTotalTop+$scope.searchcountTotalFix[catKey];
				$scope.catLength	=	$scope.catLength +1;
				var index	=	$scope.catListRemove.indexOf(catKey);
				if(index > -1) {
					$scope.catListRemove.splice(index,1);
				}
			}
		};

        $scope.selectAllPackage    =   function(event,upperId,catKey) {
            $scope.checkPins[catKey]   =   [];
            $scope.actualBudget =   0;
            
            /*$scope.actualBudget = $scope.totalBudgetShowMain - $scope.cattotalBudget[catKey];
            if(($rootScope.minPaymentFlexi > $scope.actualBudget) && $scope.actualBudget > 0) {
                $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);
            } else {
                $scope.calcFactor   =   1;
            }*/
            
           if($scope.selAllCheck[upperId]) {
                /*$scope.cattotalBudget[catKey]   =   0;
                angular.forEach($scope.flexiBudgetCatPin[catKey],function(valuePin,keyPin) {
                    $scope.cattotalBudget[catKey]   =   parseFloat($scope.cattotalBudget[catKey])+parseFloat((parseFloat($rootScope.bestBudget['result']['c_data'][catKey]['pin_data'][keyPin]['flexi_bgt'])/12).toFixed(2));
                });*/
                $scope.actualBudget =   0;
                //console.log('cat-total==='+$scope.cattotalBudget[catKey]+'--->'+$scope.totalBudgetShowMain);
                if($scope.totalBudgetShowMain <= 0) {
                    $scope.actualBudget = parseFloat($scope.cattotalBudget[catKey]);    
                } else {
                    $scope.actualBudget = $scope.totalBudgetShowMain - parseFloat($scope.cattotalBudget[catKey]);    
                }
                if($rootScope.minPaymentFlexi > $scope.actualBudget && $scope.actualBudget > 0) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);    
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                //console.log('actual-factor'+$scope.actualBudget+'--->'+$scope.calcFactor);
                $scope.totalBudgetShowMain  =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.cattotalBudget[keyCat]   =   0;
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        if(typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                            if(keyCat == catKey) {
                                $scope.flexiBudgetCatPin[keyCat][keyPin]    =   0;
                            }
                            $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+(parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin])*$scope.calcFactor);
                            if(keyCat != catKey) {
                                $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                            }
                            var bidderCt = 0;
                            if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                                angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                    if(keyPos != 100) {
                                        if(typeof valuePos['bidder'] !== 'undefined') {
                                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                                bidderCt++;
                                            }
                                        }
                                    }
                                });
                            }
                            if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                                var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                                $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                            }
                        }
                    });
                    $scope.totalBudgetShowMain      =   parseFloat($scope.totalBudgetShowMain)+parseFloat($scope.cattotalBudget[keyCat]);
                });
                /*$scope.totalBudgetShowMain  =   parseFloat($scope.totalBudgetShowMain) - parseFloat($scope.cattotalBudget[catKey]);
                $scope.cattotalBudget[catKey]   =   0;*/
                var i = 0;
                angular.forEach($scope.flexiBudgetCatPin[catKey],function(value,key) {
                    $scope.stopEdit[catKey][i]  =   1;
                    $scope.pinLength = $scope.pinLength-1;
                    var idx = $scope.selected[catKey].indexOf(key);
                    if(idx  ==  -1) {
                        $scope.selected[catKey].push(key);
                    }
                    i++;
                });

                $scope.catListRemove.push(catKey);
                $scope.catLength    =   $scope.catLength -1;
            } else {
                $scope.selAllCheckZone[0] = true;
                $scope.cattotalBudget[catKey]   =   0;
                angular.forEach($scope.flexiBudgetCatPin[catKey],function(valuePin,keyPin) {
                    $scope.cattotalBudget[catKey]   =   parseFloat($scope.cattotalBudget[catKey])+parseFloat((parseFloat($rootScope.bestBudget['result']['c_data'][catKey]['pin_data'][keyPin]['flexi_bgt'])/12).toFixed(2));
                });
                $scope.actualBudget =   0;
                if($scope.totalBudgetShowMain <= 0) {
                    $scope.actualBudget = parseFloat($scope.cattotalBudget[catKey]);    
                } else {
                    $scope.actualBudget = $scope.totalBudgetShowMain + parseFloat($scope.cattotalBudget[catKey]);    
                }
                if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                    $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);    
                } else {
                    $scope.calcFactor   =   1;
                }
                $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
                $scope.totalBudgetShowMain  =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.cattotalBudget[keyCat]   =   0;
                    angular.forEach(valueCat,function(valuePin,keyPin) {
                        if($scope.flexiBudgetCatPin[keyCat][keyPin] == 0 || ($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined")) {
                            if(keyCat == catKey) {
                                $scope.flexiBudgetCatPin[keyCat][keyPin]    =   (parseFloat($rootScope.bestBudget['result']['c_data'][catKey]['pin_data'][keyPin]['flexi_bgt'])/12).toFixed(2);
                                $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                            }
                            if(keyCat != catKey) {
                                $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                            }
                            $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+(parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin]));
                            var bidderCt = 0;
                            if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                                angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                    if(keyPos != 100) {
                                        if(typeof valuePos['bidder'] !== 'undefined') {
                                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                                bidderCt++;
                                            }
                                        }
                                    }
                                });
                            }
                            if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                                var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                                $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                            }
                        }
                    });
                    $scope.totalBudgetShowMain      =   parseFloat($scope.totalBudgetShowMain)+parseFloat($scope.cattotalBudget[keyCat]);
                });
                var i = 0;
                angular.forEach($scope.flexiBudgetCatPin[catKey],function(value,key) {
                    $scope.checkPins[catKey][i]    =   true;
                    $scope.stopEdit[catKey][i]  =   0;
                    $scope.pinLength = $scope.pinLength+1;
                    var idx = $scope.selected[catKey].indexOf(key);
                    if(idx  >  -1) {
                        $scope.selected[catKey].splice(idx,1);
                    }
                    i++;
                });
                var index   =   $scope.catListRemove.indexOf(catKey);
                if(index > -1) {
                    $scope.catListRemove.splice(index,1);
                }
                $scope.catLength    =   $scope.catLength + 1;
            }
            $scope.actualBudget     =   $scope.totalBudgetShowMain;
            $scope.customBudgetVal  =   $scope.actualBudget;
        }
		
		$scope.selAllCheckZone	=	[];
		$scope.selAllCheckZone[0]	=	true;
		$scope.selectAllZone	=	function(length,id,list) {
			if($scope.selAllCheckZone[id]) {
				var i=0;
				angular.forEach($rootScope.bestBudget.result.c_data,function(value,key) {
					$scope.selAllCheck[i]	=	false;
					var k=0;
                    $scope.checkPins[key][k] =   [];
					$scope.cattotalBudget[key]	=	0;
					$scope.catLengthWise[key]	=	0;
					angular.forEach(value.pin_data,function(value2,key2) {
						if(key2 != $rootScope.companyTempInfo.data.pincode) {
                            $scope.checkPins[key][k]  =   false;
							$scope.stopEdit[key][k]	=	1;
							var idx = $scope.selected[key].indexOf(key2);
							if(idx == -1) {
								$scope.selected[key].push(key2);
							}
						} else {
							$scope.checkPins[key][k]  =   true;
						}
						k++;
					});
					$scope.catListRemove.push(key);
					i++;
				});
				$scope.totalBudgetShowMain	=	0;
				$scope.actualBudget			=	0;
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
				if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
					$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
				}
				$scope.callCountTotalTop	=	0;
				$scope.searchCountTotalTop	=	0;
				$scope.catLength	=	0;
				$scope.pinLength	=	0;
			} else {
				var i=0;
				$scope.actualBudget			=	0;
				angular.forEach($rootScope.bestBudget.result.c_data,function(value,key) {
					$scope.selAllCheck[i]	=	true;
					var k=0;
					angular.forEach(value.pin_data,function(value2,key2) {
						var bestFlg	=	$scope.bestPositionShow[key][key2];
						
						$scope.cattotalBudget[key]	=	$scope.cattotalBudget[key]+(value2.pos[bestFlg].budget);
                        $scope.checkPins[key][k]  =   true;
						$scope.stopEdit[key][k]	=	0;
						var idx = $scope.selected[key].indexOf(key2);
						$scope.selected[key].splice(idx, 1);
						if($scope.inventory[key][key2] == 0) {
							$scope.checkPins[key][k]  =   false;
						}
						$scope.catLengthWise[key]++;
						k++;
					});
					var index	=	$scope.catListRemove.indexOf(key);
					$scope.catListRemove.splice(index,1);
					
					if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
						if($scope.cattotalBudget[key] < $rootScope.bestBudget.result.c_data[key].bm_bgt) {
							$scope.cattotalBudget[key]	=	$rootScope.bestBudget.result.c_data[key].bm_bgt;
						}
					}
					$scope.totalBudgetShowMain	=	0;
					angular.forEach($scope.cattotalBudget,function(value,key) {
						$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value);
					});
					if($scope.totalBudgetShowMain < $rootScope.bestBudget.result.city_bgt) {
						$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
					}
					i++;
				});
				angular.forEach($scope.bestBudgetShow,function(value,key) {
					if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
						$scope.actualBudget	=	$scope.actualBudget	+	parseFloat($rootScope.bestBudget.result.c_data[key].f_bgt);
					} else {
						angular.forEach(value,function(value3,key3) {
							$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat(value3);
						});
					}
				});
				
				if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
					$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
				} else {
					$scope.cityFactor	=	1;
				}
				
				angular.forEach($scope.bestBudgetShow,function(value2,key2) {
					$scope.cattotalBudget[key2]	=	0;
					if($rootScope.bestBudget.result.c_data[key2].bflg == 1) {
						$scope.cattotalBudget[key2]	=	$rootScope.bestBudget.result.c_data[key2].f_bgt * $scope.cityFactor;
					} else {
						angular.forEach(value2,function(value3,key3) {
							$scope.cattotalBudget[key2]	=	$scope.cattotalBudget[key2]+(value3*$scope.cityFactor);
						});
					}
				});
				
				$scope.totalBudgetShowMain	=	0;
				angular.forEach($scope.cattotalBudget,function(value,key) {
					$scope.totalBudgetShowMain	=	parseFloat($scope.totalBudgetShowMain) + parseFloat(value);
				});
				
				if(list.length > 0) {
					$scope.skipData	=	0;
				} else {
					$scope.skipData	=	1;
				}
				$scope.catLength	=	$scope.oldCatLength;
				$scope.pinLength	=	$scope.oldPinLength;
				$scope.callCountTotalTop	=	$scope.callCountTotalTopExtra;
				$scope.searchCountTotalTop	=	$scope.searchCountTotalTopExtra;
			}
		};
		
        $scope.selectAllZonePack = function(length,id,list) {
            if($scope.selAllCheckZone[id]) {
                var j=0;
                $scope.totalBudgetShowMain  =   0;
                $scope.actualBudget =   $scope.totalBudgetShowMain;
                $scope.pinLength = 0;
                $scope.catLength = 0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.cattotalBudget[keyCat]   =   0;
                    $scope.selAllCheck[j]  =   false;
                    var i = 0;
                    angular.forEach(valueCat,function(valuePin,keyPin){
                        $scope.checkPins[keyCat][i]    =   false;
                        $scope.stopEdit[keyCat][i] =   1;
                        $scope.flexiBudgetCatPin[keyCat][keyPin] = 0;
                        var bidderCt = 0;
                        if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                            angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                if(keyPos != 100) {
                                    if(typeof valuePos['bidder'] !== 'undefined') {
                                        if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                            bidderCt++;
                                        }
                                    }
                                }
                            });
                        }
                        if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                            var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                            $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                        }
                        var idx = $scope.selected[keyCat].indexOf(keyPin);
                        if(idx == -1) {
                            $scope.selected[keyCat].push(keyPin);
                        }
                        i++;
                    });
                    $scope.catListRemove.push(keyCat);
                    j++;
                });
            } else {
                var j=0;
                $scope.totalBudgetShowMain  =   0;
                angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                    $scope.selAllCheck[j]  =   true;
                    var i = 0;
                    $scope.catLength = $scope.catLength+1;
                    $scope.pinLength = 0;
                    angular.forEach(valueCat,function(valuePin,keyPin){
                        $scope.flexiBudgetCatPin[keyCat][keyPin] = (parseFloat($rootScope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bgt'])/12).toFixed(2);
                        $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin]);
                        $scope.checkPins[keyCat][i]    =   true;
                        $scope.pinLength = $scope.pinLength+1;
                        $scope.stopEdit[keyCat][i] =   0;
                        var idx = $scope.selected[keyCat].indexOf(keyPin);
                        $scope.selected[keyCat].splice(idx, 1);
                        var bidderCt = 0;
                        if(typeof $scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'] !== 'undefined') {
                            angular.forEach($scope.bestBudget.result.c_data[keyCat]['pin_data'][keyPin]['pos'],function(valuePos,keyPos) {
                                if(keyPos != 100) {
                                    if(typeof valuePos['bidder'] !== 'undefined') {
                                        if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                            bidderCt++;
                                        }
                                    }
                                }
                            });
                        }
                        if($scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bidder'] != null) {
                            var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[keyCat][keyPin]*12)/365),$scope.bestBudget['result']['c_data'][keyCat]['pin_data'][keyPin]['flexi_bid']);
                            $scope.flexiBudgetCatPos[keyCat][keyPin]  =   bidderCt+closestBinary;
                        }

                        i++;
                    });
                    $scope.totalBudgetShowMain  =   $scope.totalBudgetShowMain + parseFloat($scope.cattotalBudget[keyCat]);
                    var index   =   $scope.catListRemove.indexOf(keyCat);
                    $scope.catListRemove.splice(index,1);
                    j++;
                });
                $scope.actualBudget =   $scope.totalBudgetShowMain;
                $scope.customBudgetVal  =   $scope.actualBudget;
            }
        };

        function numberAs(a,b) {
            return a-b;
        }
		
		 $scope.returnOrdered = function(obj,sortFlag,sortParam) {
			  //console.log(obj);
			var catAmtObj = {};
            if(sortFlag == 'catname') {
                angular.forEach(obj['result']['c_data'],function(value,key) {
                    catAmtObj[value.cnm]    =   {};
                    catAmtObj[value.cnm][0] =   key;
                });
            } else {
                var i = 0;
                for(var key in obj) {
					if(obj[key] in catAmtObj) {
						i= Object.size(catAmtObj[obj[key]]);
						catAmtObj[obj[key]][i] = key;
                    } else {
                        i = 0;
                        catAmtObj[obj[key]] = {};
                        catAmtObj[obj[key]][i] = key;
                    }
                }
            }
             //console.log(JSON.stringify(catAmtObj));
            var objKeysArr  =   Object.keys(catAmtObj);
            if(sortFlag == 'budget') {
                var sortedArr   =   objKeysArr.sort(numberAs);
            } else {
                var sortedArr   =   objKeysArr.sort();
            }
            if(sortParam == 'desc') {
                var reverseArr  =   sortedArr.reverse();
            } else {
                var reverseArr  =   sortedArr;
            }
            
            var newDataObj = [];
            for(var keyRevArr in reverseArr) {
                for(var keyInRev in catAmtObj[reverseArr[keyRevArr]]) {
                    newDataObj.push(catAmtObj[reverseArr[keyRevArr]][keyInRev]);
                }
            }
            return newDataObj;
        }
        

        $scope.budSort = "desc";
        $scope.nameSort = "asc";
        $scope.typeSort = "budget";
        $scope.sortCats = function(type) {
            $scope.typeSort =   type;
            $scope.sortedArr    =   [];
            if(type == 'budget') {
                if($scope.budSort == "asc") {
                    var typeSort = "desc";
                    $scope.budSort = "desc";
                } else {
                    var typeSort = "asc";
                    $scope.budSort = "asc";
                }
                $scope.sortedArr    =   $scope.returnOrdered($scope.cattotalBudget,type,typeSort);
            } else if(type == 'catname') {
                if($scope.nameSort == "asc") {
                    var typeSort = "desc";
                    $scope.nameSort = "desc";
                } else {
                    var typeSort = "asc";
                    $scope.nameSort = "asc";
                }
                $scope.sortedArr    =   $scope.returnOrdered($rootScope.bestBudget,type,typeSort);
            }
            setTimeout(function() {
				$('.dataPinsInt').hide();
			},1000);
        }
        
        $scope.setOnlyExclusive	=	function(tabNo,optNo,tenureNo) {
			if($scope.onlyExclusive == true)
			{
				$scope.onlyExclusive = false;
			}
			else
			{
				$scope.onlyExclusive  = true;
			}
			
			$scope.setOption(tabNo,optNo,tenureNo);
			
		}
		
		
        $scope.setExactRenewal  =   function(tabNo,optNo,tenureNo) {
            if($scope.exactRenewal == true)
            {
                $scope.exactRenewal = false;                
            }
            else
            {
                $scope.exactRenewal  = true;
            }
            
            $scope.bestPositionShow	=	{};
			$scope.bestBudgetShow	=	{};
			$scope.stopEdit			=	{};
			$scope.bidderValue		=	{};
			$scope.bidValue		=	{};
			$scope.inventory		=	{};
			$scope.selected	=	[];
			$scope.pushDisabled	=	{};
			$scope.callcount	=	{};
			$scope.searchcount	=	{};
            $scope.setOption(tabNo,optNo,tenureNo);
            
        }
		

        $scope.showFlexiCalc    =   0;
        $scope.sortedArr    =   [];
		$scope.setOption	=	function(tabNo,optNo,tenureNo,customFlexi) {
			$scope.tabNo	=	tabNo;
			$scope.showOptionLoader	=	1;
			var tenureStr	=	tenureNo.toString();
			var tenureExp	=	tenureStr.split('-');
			$rootScope.selRadioTenure	=	tenureStr;
			$scope.selRadio			=	optNo;
			var flexiBud    =   0;
            if(tabNo == 3) {
                flexiBud    =   customFlexi;
            }
            APIServices.getBudgetData(tabNo,optNo,tenureExp[0],returnState.paridInfo,DATACITY,$rootScope.employees.hrInfo.data.empname,$rootScope.companyTempInfo.data.pincode,0,0,flexiBud,$scope.onlyExclusive,$scope.exactRenewal).success(function(response) {
				$scope.showOptionLoader	=	0;
				$rootScope.bestBudget	=	response;
				$scope.flexiBudgetCatPin    =   {};
				$scope.flexiBudgetCatPos    =   {};
				if(response.error.code == 0) {
					
					
					var k=0;
					var l=0;
					$scope.catRenewFlag	=	{};
					angular.forEach(response.result.c_data,function(value,key) {
						$scope.selAllCheck[k]	=	true;
						var i=0;
						var renewFlag	=	0;
						$scope.checkPins[key] =   {};
						$scope.flexiBudgetCatPin[key]   =   {};
						$scope.flexiBudgetCatPos[key]   =   {};
						angular.forEach(value.pin_data,function(value2,key2) {
							$scope.loadInit(key,key2,value2.best_flg,i,l);
                           	$scope.checkPins[key][i] = true;

							if(value2.renew_flg == 1 || typeof value2.renew_flg === 'undefined') {
								renewFlag++;
							}
							i++;
							l++;
                            
							if(tabNo == 3) {
								$scope.flexiBudgetCatPin[key][key2]   =   (parseFloat($rootScope.bestBudget.result.c_data[key]['pin_data'][key2].flexi_bgt)/12).toFixed(2);
								var bidderCt = 0;
								if(typeof $rootScope.bestBudget.result.c_data[key]['pin_data'][key2]['pos'] !== 'undefined') {
									angular.forEach($rootScope.bestBudget.result.c_data[key]['pin_data'][key2]['pos'],function(valuePos,keyPos) {
										if(keyPos != 100) {
											if(typeof valuePos['bidder'] !== 'undefined') {
												if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
													bidderCt++;
												}
											}
										}
									});
								}
								$scope.flexiBudgetCatPos[key][key2]   =   $rootScope.bestBudget.result.c_data[key]['pin_data'][key2].flexi_pos+bidderCt;
							}
							if(Object.size(value.pin_data) == i) {
								$scope.actualBudget	=	0;
								angular.forEach($scope.bestBudgetShow,function(value,key) {
									if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
										$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat($rootScope.bestBudget.result.c_data[key].f_bgt);
									} else {
										angular.forEach(value,function(value2,key2) {
											$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat(value2);
										});
									}
								});
								if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
									$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
								} else {
									$scope.cityFactor	=	1;
								}
								
                                if(tabNo == 3) {
                                    angular.forEach($scope.bestBudgetShow,function(value,key) {
                                        $scope.cattotalBudget[key]  =   parseFloat($rootScope.bestBudget.result.c_data[key].flexi_bgt/12);
                                        //~ console.log($scope.cattotalBudget[key] +"  pop "+key );
                                    });
                                } else {
									angular.forEach($scope.bestBudgetShow,function(value,key) {
										$scope.cattotalBudget[key]	=	$rootScope.bestBudget.result.c_data[key].f_bgt * $scope.cityFactor;;
									});
                                }
							if(tabNo == 3) {
							    $scope.totalBudgetShowMain  =   $rootScope.bestBudget.result.tb_flexi_bgt/12;
							} else {
								if($rootScope.bestBudget.result.tb_bgt > $rootScope.bestBudget.result.city_bgt) {
									$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.tb_bgt;
								} else {
									$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
                                  				 }
								}
							}
						});
						if(renewFlag == i) {
							$scope.catRenewFlag[key]	=	1;
						} else {
							$scope.catRenewFlag[key]	=	0;
						}
						k++;
					});
		
                  	$scope.sortedArr    =   $scope.returnOrdered($scope.cattotalBudget,'budget','desc');
                  	//~ console.log($scope.sortedArr);
                }
			});			
		};
		
        $scope.closestBin   =   function(v, vs) {
            if (Math.min.apply(Math, vs) >= v) {
                return vs.length + 1;
            }
            if (Math.max.apply(Math, vs) < v) {
                return 1;
            }

            if (vs.length === 0) return 1;
            var left = 0;
            var right = vs.length - 1;
            while ((left + 1) < right) {
                var mid = Math.ceil(left + (right - left) / 2);
                if (parseFloat(v) > parseFloat(vs[mid])) {
                    right = mid;
                } else {
                    left = mid;
                }
            }
            return left + 2;
        }

        Object.size = function(obj) {
            var size = 0, key;
            for (key in obj) {
                if (obj.hasOwnProperty(key)) size++;
            }
            return size;
        };

        $scope.changeAmountPack =   function(cat,pin) {
            $scope.actualBudget =   0;
            $scope.totalBudgetShowMain  =   0;

            angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                angular.forEach(valueCat,function(valuePin,keyPin) {
                    if($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined") {
                        $scope.actualBudget   =   $scope.actualBudget+parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin]);
                    } else {
                        $scope.actualBudget   =   $scope.actualBudget+0;
                    }
                });
            });
            if($rootScope.minPaymentFlexi > $scope.actualBudget) {
                $scope.calcFactor   =   1+(($rootScope.minPaymentFlexi - $scope.actualBudget)/$scope.actualBudget);
            } else {
                $scope.calcFactor   =   1;
            }
            $scope.calcFactor   =   1; // Making calculation factor always 1 to stop apportioning
            var j = 0;
            var pinTrue = {};
            var totPin = 0;
            angular.forEach($scope.flexiBudgetCatPin,function(valueCat,keyCat) {
                var i = 0;
                $scope.cattotalBudget[keyCat]   =   0;
                pinTrue[j] = Object.size($scope.checkPins[keyCat]);
                angular.forEach(valueCat,function(valuePin,keyPin) {
                    if($scope.flexiBudgetCatPin[keyCat][keyPin] != "" && typeof $scope.flexiBudgetCatPin[keyCat][keyPin] !== "undefined" && $scope.flexiBudgetCatPin[keyCat][keyPin] > 0) {
                        $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+(parseFloat($scope.flexiBudgetCatPin[keyCat][keyPin])*$scope.calcFactor);
                        if(keyCat != cat || keyPin != pin) {
                            $scope.flexiBudgetCatPin[keyCat][keyPin]    =   ($scope.flexiBudgetCatPin[keyCat][keyPin]*$scope.calcFactor).toFixed(2);
                        }

                        $scope.checkPins[keyCat][i] = true;
                        
                        var idxCat = $scope.catListRemove.indexOf(keyCat);
                        $scope.selAllCheck[j] = true;
                        var idx = $scope.selected[keyCat].indexOf(keyPin);
                        if(idx  >  -1) {
                            $scope.selected[keyCat].splice(idx, 1);
                        }
                        if($scope.catListRemove.indexOf(keyCat) > -1) {
                            var idx = $scope.catListRemove.indexOf(keyCat);
                            $scope.catListRemove.splice(idx,1);
                            $scope.catLength    =   $scope.catLength +1;   
                        }
                        $scope.stopEdit[keyCat][i] = 0;
                    } else {
                        var idx = $scope.selected[keyCat].indexOf(keyPin);
                        if(idx  ==  -1) {
                            $scope.selected[keyCat].push(keyPin);
                        }
                        $scope.cattotalBudget[keyCat]   =   $scope.cattotalBudget[keyCat]+0;
                        $scope.stopEdit[keyCat][i] = 1;
                        $scope.checkPins[keyCat][i] = false;
                        if($scope.checkPins[keyCat][i] == false) {
                            //~ pinTrue[j]--; // commenting for now 
                            pinTrue[keyCat]--;
                        }
                    }
                    i++;
                });
                $scope.totalBudgetShowMain  =   parseFloat($scope.totalBudgetShowMain) + parseFloat($scope.cattotalBudget[keyCat]);
                j++;
            });
            var pincodeLen = 0;
            var pinCounter = 0;
            angular.forEach(pinTrue,function(value,key) {
                if(pinTrue[key] > pinCounter) {
                    pincodeLen  =   pinTrue[key];
                    pinCounter  =   pinTrue[key];   
                }
            });
            $scope.pinLength =  pincodeLen; 
            var bidderCt = 0;
            if(typeof $scope.bestBudget.result.c_data[cat]['pin_data'][pin]['pos'] !== 'undefined') {
                angular.forEach($scope.bestBudget.result.c_data[cat]['pin_data'][pin]['pos'],function(valuePos,keyPos) {
                    if(keyPos != 100) {
                        if(typeof valuePos['bidder'] !== 'undefined') {
                            if(valuePos['bidder'] != "" && valuePos['bidder'] != null) {
                                bidderCt++;
                            }
                        }
                    }
                });
            }
            if($scope.bestBudget['result']['c_data'][cat]['pin_data'][pin]['flexi_bidder'] != null) {
                var closestBinary   =   $scope.closestBin(parseFloat(($scope.flexiBudgetCatPin[cat][pin]*12)/365),$scope.bestBudget['result']['c_data'][cat]['pin_data'][pin]['flexi_bid']);
                $scope.flexiBudgetCatPos[cat][pin]  =   bidderCt+closestBinary;
            }

            $scope.actualBudget =   $scope.totalBudgetShowMain;
            $scope.customBudgetVal  =   $scope.actualBudget;
            $scope.exitPackVal  =   {};
            $scope.exitPackVal[cat] =   {};
            $scope.exitPackVal[cat][pin]    =   1;
        };

        $scope.showDialogBidders    =   function(ev,catname,pincode,bidStr,tabNo,catid) {
			$rootScope.bidArrVal	=	{};
			$rootScope.catPopup	=	catname;
			$rootScope.bidArrVal1	=	bidStr.pos;
			$rootScope.bidArrVal	=	{};
            
			var catIdData =   "";
			if(typeof catid !== "undefined") {
				catIdData =   catid;
			}
			if(tabNo != 3) {
				var strParid	=	"";
				var k=0;
				angular.forEach($rootScope.bidArrVal1,function(val,i) {
					if(val.bidder != '' && val.bidder != null) {
						var expParId	=	val.bidder.split(',');
						angular.forEach(expParId,function(val2,i2) {
							$rootScope.bidArrVal[k]	=	{};
							var expParid2	=	val2.split('-');
							strParid	+=	expParid2[0]+',';
							$rootScope.bidArrVal[k]['data']	=	val2;
							$rootScope.bidArrVal[k]['pos']	=	i;
							k++;
						});
					}
				});
				APIServices.getMainTabGeneralData(strParid.slice(0,-1)).success(function(response) {
					if(response.errorCode == 0) {
						var l=0;
						$rootScope.sendCompNameBidData	=	{};
						angular.forEach($rootScope.bidArrVal1,function(val,i) {
							if(val.bidder != '' && val.bidder != null) {
								var expParId	=	val.bidder.split(',');
								angular.forEach(expParId,function(val2,i2) {
									var expParid2	=	val2.split('-');
									if(response.data[expParid2[0]] != undefined) {
										$rootScope.sendCompNameBidData[l]	=	response.data[expParid2[0]].companyname;
									} else {
										$rootScope.sendCompNameBidData[l]	=	"";
									}
									l++;
								});
							}
						});
					} else {
						$rootScope.sendCompNameBidData	=	{};
					}
					$rootScope.showCommonPop = 103;
					$rootScope.tabNoPop = tabNo;
				});
				$rootScope.catPin   =   pincode;
				$rootScope.catArea   =   bidStr.anm;
			} else {
				var strParid    =   "";
				var k=0;
				var superCt = 0;
				angular.forEach($rootScope.bidArrVal1,function(val,i) {
					if(val.bidder != '' && val.bidder != null) {
						var expParId    =   val.bidder.split(',');
						angular.forEach(expParId,function(val2,i2) {
							$rootScope.bidArrVal[k] =   {};
							var expParid2   =   val2.split('-');
							strParid    +=  expParid2[0]+',';
							$rootScope.bidArrVal[k]['data'] =   {};
							$rootScope.bidArrVal[k]['data']['c'] =   "";
							$rootScope.bidArrVal[k]['data']['p'] =   expParid2[0];
							$rootScope.bidArrVal[k]['data']['bpd'] =   0;
							$rootScope.bidArrVal[k]['data']['c_bpd'] =   0;
							if((parseInt(i)) == 0) {
								$rootScope.bidArrVal[k]['pos']  =   "Excl";    
							} else if((parseInt(i)) == 1) {
								$rootScope.bidArrVal[k]['pos']  =   "Plat";    
							} else if((parseInt(i)) == 2) {
								$rootScope.bidArrVal[k]['pos']  =   "Dmnd";    
							} else if((parseInt(i)) == 3) {
								$rootScope.bidArrVal[k]['pos']  =   "Gold";    
							} else if((parseInt(i)) == 4) {
								$rootScope.bidArrVal[k]['pos']  =   "Slvr";    
							} else if((parseInt(i)) == 5) {
								$rootScope.bidArrVal[k]['pos']  =   "Fxd 5";    
							} else if((parseInt(i)) == 6) {
								$rootScope.bidArrVal[k]['pos']  =   "Fxd 6";    
							} else if((parseInt(i)) == 7) {
								$rootScope.bidArrVal[k]['pos']  =   "Fxd 7";    
							}
							k++;
						});
						superCt++;
					}
				});
                APIServices.getMainTabGeneralData(strParid.slice(0,-1)).success(function(response) {
                    if(response.errorCode == 0) {
                        var l=0;
                        $rootScope.sendCompNameBidData  =   {};
                        angular.forEach($rootScope.bidArrVal1,function(val,i) {
                            if(val.bidder != '' && val.bidder != null) {
                                var expParId    =   val.bidder.split(',');
                                angular.forEach(expParId,function(val2,i2) {
                                    var expParid2   =   val2.split('-');
                                    if(typeof response.data[expParid2[0]] !== 'undefined') {
                                        $rootScope.bidArrVal[l]['data']['c']   =   response.data[expParid2[0]].companyname+"-"+response.data[expParid2[0]].area;
                                    } else {
                                        $rootScope.bidArrVal[l]['data']['c']   =   response.data.companyname+"-"+response.data.area;
                                    }
                                    l++;
                                });
                            }
                        });
                    } else {
                        $rootScope.sendCompNameBidData  =   {};
                    }
                });
                if(bidStr['flexi_bidder'] != "" && bidStr['flexi_bidder'] != null) {
					for(key in Object.keys(bidStr['flexi_bidder'])) {
						if(bidStr['flexi_bidder'] != '' && bidStr['flexi_bidder'] != null) {
							var compName = [];
							compName[0] = bidStr['flexi_bidder'][key]['c'];
							$rootScope.bidArrVal[k] =   {};
							$rootScope.bidArrVal[k]['data'] = Object.assign({},bidStr['flexi_bidder'][key]);
							$rootScope.bidArrVal[k]['data']['c'] =   "";
							$rootScope.bidArrVal[k]['data']['c'] =   compName[0]+"-"+bidStr['flexi_bidder'][key]['p_a'];
							$rootScope.bidArrVal[k]['data']['c_c'] =   bidStr['flexi_bidder'][key]['c_c'];
							$rootScope.bidArrVal[k]['data']['p_c'] =   bidStr['flexi_bidder'][key]['p_c'];
							$rootScope.bidArrVal[k]['pos']  =   superCt+1;
							superCt++;
							k++;
						}
					};
				}
		if(superCt == 0) {
					alert('Sorry there are no bidders for this category pincode combination');
					return false;
				}
				$rootScope.showCommonPop = 103;
		        $rootScope.tabNoPop = tabNo;
				$rootScope.catPin	=	pincode;
		        $rootScope.catArea   =   bidStr.anm;
		        $scope.exitPackVal  =   {};
		        $scope.exitPackVal[catIdData] =   {};
		        $scope.exitPackVal[catIdData][pincode]    =   1;
		    }
		};
		
		$scope.submitArr	=	{};
		$scope.submitArr['c_data']	=	{};
		$scope.submitArr['packageBudget']	=	0;
		$scope.submitArr['pdgBudget']	=	0;
		$scope.submitArr['tenure']	=	0;
		$scope.showErrPack = 0;
        $scope.showErrPackMsg = "";
        $rootScope.customBudgetVal  =   "";
        $rootScope.minPaymentFlexi  =   0;
        $scope.sendCustomValid = function(customBudget,flexiCalcParam) {
            $scope.showErrPack = 0;
            $scope.showErrPackMsg = "";
            if(customBudget == "") {
                $scope.showErrPack = 1;
                $scope.showErrPackMsg = "Please insert Package value";
                return false;
            }
            if(parseFloat($rootScope.minPaymentFlexi) <= parseFloat(customBudget)) { 
                $scope.setOption(3,1,'12-365',(customBudget*12));
            } else {
                $scope.showErrPack = 1;
                $scope.showErrPackMsg = "Oops! Budget too Low.Enter amount more than "+($rootScope.minPaymentFlexi);

                return false;                           
            }
            if(flexiCalcParam == 1) {
                $scope.showFlexiCalc    =   1;
            }
			if(flexiCalcParam == 1) {
            	$rootScope.customBudgetVal  =   customBudget;
			}
        };

        $rootScope.makeInt = function(param) {
            return parseInt(param);
        };

        $scope.closeRenewal = function() {
            $('.renewalPop').hide();
        };
        
        
        
		//Function called for submitting budget
		$rootScope.submitBudget	=	function(customBudget,ev) {

			if($scope.tabNo == 3) {
				if(Math.round(customBudget) < ($rootScope.minPaymentFlexi)) {
					alert('Oops! Your budget cannot be less than '+($rootScope.minPaymentFlexi)+' INR.');
					return false;
				}
			}

			$scope.submitArr	=	{};
			$scope.submitArr['c_data']	=	{};
			$scope.submitArr['packageBudget']	=	0;
			$scope.submitArr['pdgBudget']	=	0;
			$scope.submitArr['tenure']	=	0;
			var tenureExp	=	$rootScope.selRadioTenure.split('-');
			$scope.submitArr['tenure']	=	tenureExp[0];
			$scope.submitArr['actual_bgt']	=	0;
			var catStr	=	"";
			angular.forEach($scope.catListRemove,function(catValue,catKey) {
				catStr	+=	catValue+',';
			});
			catStr	=	catStr.slice(0,-1);
			$scope.submitArr['removeCatStr']	=	catStr;
			$scope.submitArr['nonpaidStr']	=	"";
			
			 if($scope.tabNo == 6) {
				$scope.submitArr['exactrenewal']   =   $scope.bestBudget['result']['exact_renewal'];
			}
			
			angular.forEach($scope.bestBudgetShow,function(value,key) {
				$scope.submitArr['totBudget']	=	{};
				if(customBudget == 0) {
					$scope.submitArr['totBudget']	=	$scope.totalBudgetShowMain;
				} else {
					$scope.submitArr['totBudget']   =   customBudget*12;
				}
               	$scope.submitArr['customBudget']    =   customBudget*12;
				$scope.submitArr['reg_bgt']		=	$rootScope.bestBudget.result.reg_bgt;
				$scope.submitArr['city_bgt']	=	$rootScope.bestBudget.result.city_bgt;
				if($scope.cattotalBudget[key]) {
					$scope.submitArr['c_data'][key] =   {};
                    if($scope.tabNo == 3) {
                        $scope.submitArr['actual_bgt']  =   $scope.submitArr['actual_bgt'] + ($scope.cattotalBudget[key]*12);
                        $scope.submitArr['c_data'][key]['c_bgt']    =   $scope.cattotalBudget[key]*12;
                        $scope.submitArr['c_data'][key]['flexi_bgt']     =   $scope.cattotalBudget[key]*12;
                    } else {
                        $scope.submitArr['actual_bgt']  =   $scope.submitArr['actual_bgt'] + $scope.cattotalBudget[key];
                        $scope.submitArr['c_data'][key]['c_bgt']    =   $scope.cattotalBudget[key];
                        $scope.submitArr['c_data'][key]['flexi_bgt']     =   $scope.cattotalBudget[key];
                    }
                    $scope.submitArr['c_data'][key]['bflg']		=	$rootScope.bestBudget.result.c_data[key]['bflg'];
					$scope.submitArr['c_data'][key]['bm_bgt']	=	$rootScope.bestBudget.result.c_data[key]['bm_bgt'];
					$scope.submitArr['c_data'][key]['cnm']		=	$rootScope.bestBudget.result.c_data[key]['cnm'];
					$scope.submitArr['c_data'][key]['ncid']		=	$rootScope.bestBudget.result.c_data[key]['ncid'];
                   	$scope.submitArr['c_data'][key]['pin_data']	=	{};
					angular.forEach(value,function(value2,key2) {
						if($scope.selected[key].indexOf(key2) == -1) {
							$scope.submitArr['c_data'][key]['pin_data'][key2]	=	{};
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos']	=	{};
                            $scope.submitArr['c_data'][key]['pin_data'][key2]['anm']    =   $rootScope.bestBudget.result.c_data[key]['pin_data'][key2]['anm'];
							$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']	=	{};
							$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']	=	$rootScope.bestBudget.result.c_data[key]['pin_data'][key2].cnt;	
							$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt_f']	=	$rootScope.bestBudget.result.c_data[key]['pin_data'][key2].cnt_f;	
							
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]	=	{};
                            
                            if($scope.tabNo == 3) {
								$scope.submitArr['c_data'][key]['pin_data'][key2]['flexi_pos']  =   $scope.flexiBudgetCatPos[key][key2];
								$scope.submitArr['c_data'][key]['pin_data'][key2]['flexi_bgt']  =   parseFloat($scope.flexiBudgetCatPin[key][key2]*12);
								$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['budget']  =   parseFloat($scope.flexiBudgetCatPin[key][key2]*12);
							} else {
								$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['budget']	=	value2;
                            }
							if($scope.bestPositionShow[key][key2] == 100) {
								if($scope.tabNo == 3) {
                                    $scope.submitArr['packageBudget']       =   $scope.submitArr['packageBudget']+(parseFloat($scope.flexiBudgetCatPin[key][key2]*12));
                                } else {
                                    $scope.submitArr['packageBudget']       =   $scope.submitArr['packageBudget']+value2;
                                }
							} else {
								$scope.submitArr['pdgBudget']		=	$scope.submitArr['pdgBudget']+value2;
							}
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['bidvalue']	=	$scope.bidValue[key][key2];
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['inventory']	=	$scope.inventory[key][key2];
						}
					});
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
			$scope.showOptionLoader	=	1;	
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,0).success(function(response) {
				$scope.showOptionLoader	=	0;
				
				if(response.error_code == 0) {
					if(response.exact_renewal)
					{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'This is an exact renewal !. Click OK to proceed';  
					}else  if($scope.tabNo == 4 && !response.exact_renewal) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'This is not  an exact renewal !. Click OK to proceed';  
					}
					
					$state.go('appHome.showBudgetPageSub',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
				}else
				{
					
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Some error found while submiting budget. Please contact software team";
				}
				
			});
		};
		
		$rootScope.submitBudgetSkipPackage	=	function(ev) {
			angular.forEach($scope.submitArr['c_data'],function(value,key) {
				var pinCount = 0;
				var pinPackCount = 0;
				angular.forEach(value['pin_data'],function(value2,key2) {
					angular.forEach(value2['pos'],function(value3,key3) {
						if(key3 == '100') {
							delete $scope.submitArr['c_data'][key]['pin_data'][key2];
							$scope.submitArr['c_data'][key]['c_bgt']	=	$scope.submitArr['c_data'][key]['c_bgt'] - value3['budget'];
							if($scope.submitArr.city_bgt < ($scope.submitArr.totBudget - value3['budget'])) {
								$scope.submitArr.totBudget	=	$scope.submitArr.totBudget - value3['budget'];
							} else {
								$scope.submitArr.totBudget	=	$scope.submitArr.city_bgt;
							}
							pinPackCount++;
						}
					});
					pinCount++;
				});
				if(pinCount == pinPackCount) {
					delete $scope.submitArr['c_data'][key];
					$scope.submitArr['nonpaidStr']	=	$scope.submitArr['nonpaidStr']+key+',';
					$scope.submitArr['removeCatStr']	=	$scope.submitArr['removeCatStr']+key+',';
				}
			});
			
			$scope.actualBudget	=	0;
			
			angular.forEach($scope.submitArr['c_data'],function(value,key) {
				if($rootScope.bestBudget.result.c_data[key] != undefined && $rootScope.bestBudget.result.c_data[key].bflg == 1) {
					if($scope.submitArr['c_data'][key].c_bgt > $rootScope.bestBudget.result.c_data[key].f_bgt) {
						$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat($scope.submitArr['c_data'][key].c_bgt);
					} else {
						$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat($rootScope.bestBudget.result.c_data[key].f_bgt);
					}
				} else {
					$scope.actualBudget	=	parseFloat($scope.actualBudget) + parseFloat(value.c_bgt);
				}
			});
			
			if($rootScope.bestBudget.result.city_bgt > $scope.actualBudget) {
				$scope.cityFactor	=	1+(($rootScope.bestBudget.result.city_bgt - $scope.actualBudget)/$scope.actualBudget);
			} else {
				$scope.cityFactor	=	1;
			}
			
			angular.forEach($scope.submitArr['c_data'],function(value,key) {
				$scope.submitArr['c_data'][key].c_bgt	=	$scope.submitArr['c_data'][key].c_bgt * $scope.cityFactor;
			});
			
			$scope.submitArr['packageBudget']	=	0;
			$rootScope.donotProceed	=	0;
			if($scope.submitArr['packageBudget'] == 0 && $scope.submitArr['pdgBudget']	==	0) {
				$mdToast.show(
					$mdToast.simple()
					.content('You cannot skip package as it will convert all categories to nonpaid.')
					.position('bottom right')
					.hideDelay(3000)
				);	
				return false;
			}
			$scope.submitArr['nonpaidStr'].slice(0,-1);
			$scope.submitArr['removeCatStr'].slice(0,-1);
			$scope.showOptionLoader	=	1;	
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,0).success(function(response) {
				$scope.showOptionLoader	=	0;
				if(response.error_code == 0) 
					{
						if($rootScope.bestBudget.result.exact_renewal && !response.exact_renewal) {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = 'This is not  an exact renewal !. Click OK to proceed';  
							setTimeout(function () {
								$state.reload();
							}, 3000);
						}else{
							$state.reload();
						}
				}else
				{
					
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Some error found while submiting budget. Please contact software team';
				}
			});
		};
		
		$scope.submitCustomMixPackage	=	function(customPackVal) {
			var pinPackCount = {};
			angular.forEach($scope.submitArr['c_data'],function(value,key) {
				pinPackCount[key]	=	0;
				angular.forEach(value['pin_data'],function(value2,key2) {
					angular.forEach(value2['pos'],function(value3,key3) {
						if(key3 == '100') {
							$scope.submitArr['c_data'][key]['c_bgt']	=	$scope.submitArr['c_data'][key]['c_bgt'] - $scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget;
							$scope.submitArr.totBudget	=	$scope.submitArr.totBudget - parseFloat($scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget);
							$scope.submitArr['actual_bgt']	=	$scope.submitArr['actual_bgt'] - parseFloat($scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget);
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget	=	0;
							pinPackCount[key]++;
						} else {
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget	=	$scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget;
						}
					});
				});
			});
			
			$scope.submitArr.totBudget	=	parseFloat($scope.submitArr.totBudget)+parseFloat(customPackVal);
			$scope.submitArr['packageBudget']	=	parseFloat(customPackVal);
			$scope.oldTotalPDGBudget	=	$scope.showBudgetFinData['data']['pdgBudget'];
			$scope.oldTotalPackBudget	=	$scope.showBudgetFinData['data']['packageBudget'];
			$scope.submitArr['pdgBudget']		=	parseFloat($scope.submitArr.totBudget) - parseFloat($scope.submitArr['packageBudget']);
			if($scope.submitArr.totBudget < parseFloat($scope.submitArr['city_bgt'])) {
				$scope.submitArr.totBudget = parseFloat($scope.submitArr['city_bgt']);
				$scope.submitArr['pdgBudget']		=	parseFloat($scope.submitArr.totBudget) - parseFloat($scope.submitArr['packageBudget']);
			}
			angular.forEach(pinPackCount,function(value,key) {
				if(value == 0) {
					delete pinPackCount[key];
				}
			});
			
			$scope.pdgFactor	=	1+ (($scope.submitArr['pdgBudget'] - $scope.oldTotalPDGBudget) / $scope.oldTotalPDGBudget);
			$scope.packFactor	=	1+ (($scope.submitArr['packageBudget'] - $scope.oldTotalPackBudget) / $scope.oldTotalPackBudget);
			
			$scope.submitArr['actual_bgt']	=	0;
			angular.forEach($scope.submitArr['c_data'],function(value,key) {
				$scope.submitArr['c_data'][key]['c_bgt']	=	0;
				angular.forEach(value['pin_data'],function(value2,key2) {
					angular.forEach(value2['pos'],function(value3,key3) {
						if(key3 == '100') {
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget	=	$scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget * $scope.packFactor;
						} else {
							$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget	=	$scope.showBudgetFinData['data']['c_data'][key]['pin_data'][key2][key3].budget * $scope.pdgFactor;
						}
						$scope.submitArr['c_data'][key]['c_bgt']	=	$scope.submitArr['c_data'][key]['c_bgt'] + $scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget;
						$scope.submitArr['actual_bgt']				=	$scope.submitArr['actual_bgt'] + parseFloat($scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][key3].budget);
					});
				});
			});
			
			$scope.showOptionLoader	=	1;	
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,0).success(function(response) {
				$scope.showOptionLoader	=	0;
				if(response.error_code == 0) {
				$state.reload();
				}else
				{
					$mdToast.show(
					$mdToast.simple()
					.content('Some error found while submiting budget. Please contact software team')
					.position('top right')
					.hideDelay(3000)
					);
				}
			});
		}
		
		$rootScope.submitBudgetWOSkipPackage	=	function(ev) {
			$scope.showOptionLoader	=	1;
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,0).success(function(response) {
				$scope.showOptionLoader	=	0;
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
		
		Object.size = function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) { size++;}
			}
			return size;
		};
		
		$scope.openCustomBudget	=	function(ev) {
			$mdDialog.show({
				controller: DialogControllerCustomBudget,
				templateUrl: 'partials/dialogCustomBudget.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			})
			.then(function(answer) {
				$scope.alert = 'You said the information was "' + answer + '".';
			}, function() {
				$scope.alert = 'You cancelled the dialog.';
			});
		};
		
		$rootScope.skipPackageVal	=	function(ev) {
			$rootScope.submitBudgetSkipPackage(ev); //Calling submit budget with skip package flag
		};
		
		$rootScope.skipPackageWOVal	=	function(ev) {
			$rootScope.submitBudgetWOSkipPackage(ev);
		};
		
		$rootScope.customMixPackage	=	function(customPackVal) {
			$scope.submitCustomMixPackage(customPackVal);
		};
		
		$rootScope.submitCustomMixPackage_new = function(customMixPackageVal){
			$rootScope.captchaCorrect   =   0;
			$rootScope.customMixPackageVal = customMixPackageVal;
			if($rootScope.customMixPackageVal == "" || $rootScope.customMixPackageVal == null) {
			    $rootScope.captchaCorrect   =   1;
			    $rootScope.errorMessage     =   ' custom budget is blank';
			    return false;
			}
			if(parseFloat($rootScope.customMixPackageVal) < parseFloat($rootScope.bestBudget.result.pinmin_bgt)) {
			    $rootScope.captchaCorrect   =   1;
			    $rootScope.errorMessage     =   ' custom budget is lower then the minimum city level budget '+$rootScope.bestBudget.result.pinmin_bgt;
			    return false;
			}
			$rootScope.customMixPackage($rootScope.customMixPackageVal);
			$rootScope.showCommonPop =0;
			$rootScope.customMixPackageVal ='';
		};



		function DialogSkipPackage($scope, $mdDialog) {
			$scope.skipPackageVal	=	function(ev) {
				$rootScope.submitBudgetSkipPackage(ev);
				$mdDialog.hide();
			};
			
			$scope.goToSelPackage	=	function(ev) {
				$mdDialog.hide();
			};
		}
		
		function DialogController($scope, $mdDialog) {
			$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
			$scope.hide = function() {
				$mdDialog.hide();
			};
			$scope.cancel = function() {
				$mdDialog.cancel();
			};
			$scope.answer = function(answer) {
				$mdDialog.hide(answer);
			};
			$scope.catPopup	=	$rootScope.catPopup;
			$scope.catPin	=	$rootScope.catPin;
			$scope.bidArrVal	=	$rootScope.bidArrVal;
			$scope.sendCompNameBidData	=	$rootScope.sendCompNameBidData;
		}
		
		function DialogCustomMixPackage($scope, $mdDialog) {
			$scope.hide = function() {
				$mdDialog.hide();
			};
			
			$scope.submitCustomMixPackage	=	function() {
				$scope.captchaCorrect	=	0;
				if($scope.customMixPackageVal == "" || $scope.customMixPackageVal == null) {
					$scope.captchaCorrect	=	1;
					$scope.errorMessage		=	' custom budget is blank';
					return false;
				}
				if(parseFloat($scope.customMixPackageVal) < parseFloat($rootScope.bestBudget.result.pinmin_bgt)) {
					$scope.captchaCorrect	=	1;
					$scope.errorMessage		=	' custom budget is lower then the minimum city level budget '+$rootScope.bestBudget.result.pinmin_bgt;
					return false;
				}
				$rootScope.customMixPackage($scope.customMixPackageVal);
				$mdDialog.hide();
			};
		}
		
		function DialogControllerCustomBudget($scope, $mdDialog,$mdToast) {
			$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
			$scope.hide = function() {
				$mdDialog.hide();
			};
			$scope.cancel = function() {
				$mdDialog.cancel();
			};
			$scope.answer = function(answer) {
				$mdDialog.hide(answer);
			};
			
			$scope.submitBudget	=	function(customBudgetVal,ev) {
				if(typeof customBudgetVal !== 'undefined') {
					if(parseInt(customBudgetVal) < parseInt($rootScope.bestBudget.result.city_bgt)) {
						$mdToast.show(
							$mdToast.simple()
							.content('Budget should be more then '+$rootScope.bestBudget.result.city_bgt)
							.position('top right')
							.hideDelay(3000)
						);	
					} else {
						$rootScope.submitBudget(customBudgetVal,ev);
						$scope.hide();
					}
				} else {
					return false;
				}
			}
		}
		
		$scope.showBudgetFinData	=	{};
		$scope.onLoadCall	=	function() {
			APIServices.getDataFinalBUdget(returnState.paridInfo).success(function(response) {
				$scope.showBudgetFinData	=	{};
				$scope.showBudgetFinData	=	response;
			});
		};
		
		$scope.customPackage	=	function(ev) {
			APIServices.getSetBudgetData(returnState.paridInfo,$scope.showBudgetFinData.data.tenure).success(function(response) {
				$scope.submitArr	=	{};
				$scope.submitArr	=	response.budgetjson;
				$mdDialog.show({
					controller: DialogCustomMixPackage,
					templateUrl: 'partials/dialogCustomMixPackage.html',
					parent: angular.element(document.body),
					targetEvent: ev,
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			});
		};
		
		//$scope.submitArr	=	{};
		$scope.skipPackageVals	=	function(ev) {
			APIServices.getSetBudgetData(returnState.paridInfo,$scope.showBudgetFinData.data.tenure).success(function(response) {
				$scope.submitArr	=	response.budgetjson;
				$mdDialog.show({
					controller: DialogSkipPackage,
					templateUrl: 'partials/dialogSkipPackage.html',
					parent: angular.element(document.body),
					targetEvent: ev,
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
				return false;
				//$rootScope.submitBudgetSkipPackage(ev);
			});
		};
		
		$scope.regFeesShow	=	0;
		$scope.showRegFees	=	function() {
			if($scope.regFeesShow == 0) {
				$scope.regFeesShow	=	1;
			} else {
				$scope.regFeesShow	=	0;
			}
		};

		$scope.callpopup = function() {
			var selected_opt=$cookieStore.get('campaign_str');
			var main_opt=$cookieStore.get('selected_option');
			
			APIServices.getversion(returnState.paridInfo,DATACITY).success(function(response) {
				$rootScope.budgetVersion	=	response.version;
				$scope.selected_arr = selected_opt.split(',');
				if($scope.selected_arr.indexOf("1") != -1) {
					$scope.package_index = $scope.selected_arr.indexOf("1");
				}else if($scope.selected_arr.indexOf("111") != -1) {
					$scope.package_index = $scope.selected_arr.indexOf("111");
				}else if($scope.selected_arr.indexOf("112") != -1) {
					$scope.package_index = $scope.selected_arr.indexOf("112");
				}else if($scope.selected_arr.indexOf("113") != -1) {
				    $scope.package_index = $scope.selected_arr.indexOf("113");
				}else if($scope.selected_arr.indexOf("114") != -1) {
				    $scope.package_index = $scope.selected_arr.indexOf("114");
				}else if($scope.selected_arr.indexOf("115") != -1) {
				    $scope.package_index = $scope.selected_arr.indexOf("115");
				}else if($scope.selected_arr.indexOf("116") != -1) {
				    $scope.package_index = $scope.selected_arr.indexOf("116");
                }else if($scope.selected_arr.indexOf("117") != -1) {
                    $scope.package_index = $scope.selected_arr.indexOf("117");
				}else if($scope.selected_arr.indexOf("118") != -1) {
                    $scope.package_index = $scope.selected_arr.indexOf("118");
				}else if($scope.selected_arr.indexOf("119") != -1) {
                    $scope.package_index = $scope.selected_arr.indexOf("119");
				}else {
					$scope.package_index = $scope.selected_arr.indexOf("2");
				}
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


			});

		}
		
	});
	
	tmeModuleApp.controller('existInvSelController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS) {
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
		
		$scope.limitVals	=	[-1];

		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
 
		$rootScope.extraHandler	=	$stateParams.page;
		$rootScope.businessUrl	=	'../business/bform.php?navbar=yes';
		$scope.limiter	=	10;
		
		//Function to stop auto sorting by ng-repeat in angular js
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}

		$scope.usercode	=	USERID;
		
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

		$scope.loaderArea	=	{};
		$scope.imgButAcc	=	{};
		$scope.showImg	=	function(index,version) {
			if(index == 0) {
				$scope.imgButAcc[version]	=	{};
			}
			$scope.imgButAcc[version][index]	=	"img/ic_add_circle_24px.svg";
		};
		
		$scope.clickshrink	=	function(event,index,version) {
			$(event.target).closest('.insideData').find('.dataPinsInt').toggle();
			if($(event.target).closest('.insideData').find('.dataPinsInt').css('display') == 'block') {
				$scope.imgButAcc[version][index]	=	"img/ic_remove_circle_24px.svg";
			} else if($(event.target).closest('.insideData').find('.dataPinsInt').css('display') == undefined){
				$scope.imgButAcc[version][index]	=	"img/ic_remove_circle_24px.svg";
			} else {
				$scope.imgButAcc[version][index]	=	"img/ic_add_circle_24px.svg";
			}
		};
		
        $scope.setLiveInvCheck	=	{};
        $scope.buttonInvRelease	=	{};
        $scope.secbuttonInvRelease	=	{};
        $scope.buttonInvRelease['live']	=	1;
        $scope.buttonInvRelease['shadow']	=	{};
		$scope.secbuttonInvRelease['live']	=	0;
		$scope.secbuttonInvRelease['shadow']=	{};
		$scope.setLiveInvCheck['live']	=	0;
		$scope.setLiveInvCheck['shadow']	=	{};
		$scope.checkPins	=	{};
		$scope.checkPins['live']	=	{};
		$scope.checkPins['shadow']	=	{};
		
		$scope.catPinArr	=	{};
		
		$scope.releaseInvBut	=	function(whatParam,inventory,ev) {
			if(whatParam == 'live') {
				$rootScope.mode		=	"10";
				$rootScope.version	=	"12";
			} else {
				$rootScope.mode		=	"11";
				$rootScope.version	=	inventory;
			}
			$rootScope.confCaptcha	=	0;
			$rootScope.i_reason	=	"Default reason for now";
			$rootScope.whatParam	=	whatParam;
			if(whatParam == 'shadow') {
				$mdDialog.show({
					controller: DialogRelInventoryController,
					templateUrl: 'partials/dialogReleaseInventory.html',
					parent: angular.element(document.body),
					targetEvent: ev,
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			} else {
				$rootScope.inventorySet(whatParam);
			}
		};
		
		$scope.selectAllCheck	=	{};
		$scope.selectAll	=	function(catKey,whichMode,index,version) {
			if($scope.selectAllCheck[version][catKey]) {
				var i=0;
				if($scope.catPinArr[catKey] === undefined) {
					$scope.catPinArr[catKey]	=	{};
				}
				angular.forEach($scope.exisitingInvData.results[whichMode].results[version].inv.results[catKey].pin_data,function(value,key) {
					if(whichMode	==	'live') {
						$scope.checkPins['live'][catKey][i]	=	false;
					} else {
						$scope.checkPins['shadow'][catKey][i]	=	false;
					}
					delete $scope.catPinArr[catKey][key];
					i++;
				});
			} else {
				var i=0;
				if($scope.catPinArr[catKey] === undefined) {
					$scope.catPinArr[catKey]	=	{};
				}
				angular.forEach($scope.exisitingInvData.results[whichMode].results[version].inv.results[catKey].pin_data,function(value,key) {
					if(whichMode	==	'live') {
						$scope.checkPins['live'][catKey][i]	=	true;
					} else {
						$scope.checkPins['shadow'][catKey][i]	=	true;
					}
					$scope.catPinArr[catKey][key]	=	[];
					$scope.catPinArr[catKey][key].push(value[0].pos);
					i++;
				});
			}
		};
		
		$rootScope.inventorySet	=	function(whatParam) {
			$scope.checkPins['live']	=	{};
			$scope.checkPins['shadow']	=	{};
			$scope.selectAllCheck		=	{};
			if(whatParam	==	'live') {
				$scope.setLiveInvCheck['live']	=	1;
				$scope.buttonInvRelease['live']	=	0;
				$scope.secbuttonInvRelease['live']	=	1;
				angular.forEach($scope.exisitingInvData.results.shadow.results,function(value,key){
					$scope.buttonInvRelease['shadow'][key]	=	0;
					$scope.setLiveInvCheck['shadow'][key]	=	0;
				});
				angular.forEach($scope.exisitingInvData.results.live.results,function(value,key){
					$scope.selectAllCheck[key]	=	{};
					angular.forEach(value.inv.results,function(value2,key2) {
						$scope.checkPins['live'][key2]	=	{};
						var $i=0;
						if($i=0) {
							$scope.selectAllCheck[key][key2]	=	false;
						}
						angular.forEach(value2.pin_data,function(value3,key3){
							$scope.checkPins['live'][key2][$i]	=	false;
							$i++;
						});
					});
				});
			} else {
				$scope.setLiveInvCheck['live']	=	0;
				$scope.buttonInvRelease['live']	=	0;
				angular.forEach($scope.exisitingInvData.results.shadow.results,function(value,key){					
					$scope.buttonInvRelease['shadow'][key]	=	0;
					$scope.secbuttonInvRelease['shadow'][key]	=	0;
					$scope.setLiveInvCheck['shadow'][key]	=	0;
					$scope.selectAllCheck[key]	=	{};
					angular.forEach(value.inv.results,function(value2,key2) {
						$scope.checkPins['shadow'][key2]	=	{};
						var $i=0;
						if($i=0) {
							$scope.selectAllCheck[key][key2]	=	false;
						}
						angular.forEach(value2.pin_data,function(value3,key3){
							$scope.checkPins['shadow'][key2][$i]	=	false;
							$i++;
						});
					});
				});
				$scope.buttonInvRelease['shadow'][$rootScope.version]	=	0;
				$scope.secbuttonInvRelease['shadow'][$rootScope.version]	=	1;
				$scope.setLiveInvCheck['shadow'][$rootScope.version]	=	1;
			}
		};
		
		$scope.butInvButRelease	=	function(whatParam,inventory) {
			$scope.catPinArr	=	{};
			$scope.setLiveInvCheck['live']		=	0;
			$scope.buttonInvRelease['live']	=	1;
			$scope.secbuttonInvRelease['live']	=	0;
			angular.forEach($scope.exisitingInvData.results.shadow.results,function(value,key){
				$scope.buttonInvRelease['shadow'][key]	=	1;
				$scope.secbuttonInvRelease['shadow'][key]	=	0;
				$scope.setLiveInvCheck['shadow'][key]	=	0;
			});
		};
		
		$scope.toggle	=	function(pincode,catid,position,whatParam,inventory,index) {
			if($scope.catPinArr[catid] === undefined) {
				$scope.catPinArr[catid]	=	{};
			}
			if($scope.checkPins[whatParam][catid][index]) {
				delete $scope.catPinArr[catid][pincode];
			} else {
				$scope.catPinArr[catid][pincode]	=	[];
				$scope.catPinArr[catid][pincode].push(position);
			}
		};
		
		$rootScope.finalArrSubmitInvRelease	=	{};
		$scope.releaseInventorySubmit	=	function(ev) {
			$rootScope.finalArrSubmitInvRelease	=	{};
			var i = 0;
			angular.forEach($scope.catPinArr,function(value,key) {
				var j=0;
				$rootScope.finalArrSubmitInvRelease[key]	=	{};
				angular.forEach(value,function(value2,key2) {
					if(value2[0] != 'NA') {
						$rootScope.finalArrSubmitInvRelease[key][j]	=	{};
						$rootScope.finalArrSubmitInvRelease[key][j]['pin']	=	key2;
						$rootScope.finalArrSubmitInvRelease[key][j]['pos']	=	value2[0];
						j++;
					}
				});
				if(j == 0) {
					delete $rootScope.finalArrSubmitInvRelease[key];
				}
				i++;
			});
			
			if($scope.isEmpty($rootScope.finalArrSubmitInvRelease) == true) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Not Allowed !!!!";
				$rootScope.commonShowContent = 'Please select a version and cateroy/pincodes to release the inventory';
				return false;
			} else {
				$rootScope.confCaptcha	=	1;
				$mdDialog.show({
					controller: DialogRelInventoryController,
					templateUrl: 'partials/dialogReleaseInventory.html',
					parent: angular.element(document.body),
					targetEvent: ev,
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			}
		};
		
		function DialogRelInventoryController($scope, $mdDialog) {
			$scope.finalArrSubmitInvRelease	=	$rootScope.finalArrSubmitInvRelease;
			$scope.hide = function() {
				$mdDialog.hide();
				$('.md-dialog-container').css('display','none');
			};
			$scope.cancel = function() {
				$mdDialog.cancel();
			};
			$scope.answer = function(answer) {
				$mdDialog.hide(answer);
			};
			
			$scope.randomString = function(length, chars) {
				var result = '';
				for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
				return result;
			}
			$scope.rString = $scope.randomString(5, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
			$scope.i_data	=	JSON.stringify($scope.finalArrSubmitInvRelease);
			$scope.captchaCorrect	=	0;
			$scope.confCaptcha	=	$rootScope.confCaptcha;
			
			$scope.releaseFullInv	=	function() {
				$scope.i_data	=	"";
				$scope.confCaptcha	=	1;
			};
			
			$scope.releasePincodeWise	=	function() {
				$mdDialog.hide();
				$rootScope.inventorySet($rootScope.whatParam);
			};
			$scope.captchaValue	=	"";
			$scope.submitInvRelease	=	function() {
				if($scope.rString	==	$scope.captchaValue) {
					$scope.captchaCorrect	=	0;
					APIServices.releaseInventoryData($rootScope.parentid,$rootScope.version,$rootScope.mode,$rootScope.i_reason,$scope.i_data).success(function(results) {
						if(results.error.code == 0) {
							$mdDialog.hide();
							$rootScope.getMasterCampaignData();
							$scope.captchaCorrect	=	1;
						} else {
							$scope.captchaCorrect	=	1;
							$scope.errorMessage	=	results.error.msg;
							return false;
						}
					});
				} else {
					$scope.captchaCorrect	=	1;
					$scope.errorMessage	=	"correct captcha value is not inserted";
					return false;
				}
			};
		}
		
		$scope.isEmpty	=	function (obj) {
			for(var prop in obj) {
				if(obj.hasOwnProperty(prop))
					return false;
			}
			return true;
		};
		
		$rootScope.parentid	=	returnState.paridInfo;
		$rootScope.setNoMenu	=	1;
		
		$scope.nxt_page = function(){
			if($stateParams.flow == 'fixed'){
				$state.go('appHome.campaignSel',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
			}else if($stateParams.flow == 'package') {
				$state.go('appHome.showBudgetPageSub',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
			}
		}

		$rootScope.getMasterCampaignData	=	function() {
			$scope.setLiveInvCheck	=	{};
			$scope.setLiveInvCheck['live']	=	0;
			$scope.setLiveInvCheck['shadow']	=	{};
			$scope.checkPins	=	{};
			$scope.checkPins['live']	=	{};
			$scope.checkPins['shadow']	=	{};
			$scope.selectAllCheck	=	{};
			
			APIServices.getCampaignMaster().success(function(response) {
				$scope.campaignData	=	response.results;
				APIServices.getExistingInventory(returnState.paridInfo,DATACITY).success(function(result) {
					$scope.exisitingInvData	=	result;
					if(result.results.shadow.error.code == 0) {
						angular.forEach(result.results.shadow.results,function(value,key){
							$scope.buttonInvRelease['shadow'][key]	=	1;
							$scope.secbuttonInvRelease['shadow'][key]	=	0;
						});
					}
					if(result.results.live.error.code == 0) {
						$scope.buttonInvRelease['live']	=	1;
						$scope.secbuttonInvRelease['live']	=	0;
					}
					if(result.results.live.error.code	==	0 || result.results.shadow.error.code	==	0) {
						if($scope.exisitingInvData.results.shadow.error.code == 0) {
							var i=0;
							var j=0;
							angular.forEach($scope.exisitingInvData.results.shadow.results,function(value,key) {
								if(value.updatedby == 'ECS Request') {
									i++;
								}
								j++;
							});
							if((i==j) && (j == 1)) {
								$scope.showArrowInvVal	=	0;
								$scope.buttonInvRelease['shadow']	=	0;
							} else {
								$scope.showArrowInvVal	=	1;
							}
						} else if($scope.exisitingInvData.results.shadow.error.code == 1 || $scope.exisitingInvData.results.shadow.max_dc_date < '2015-10-05 00:00:00') {
							$scope.showArrowInvVal	=	0;
						} else {
							$scope.showArrowInvVal	=	1;
						}
						//Calling Service for contractInformation
						APIServices.getContractData(returnState.paridInfo).success(function(response) {
							$rootScope.companyTempInfo	=	response;
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
					} else {
						if($stateParams.flow == 'fixed'){
							$state.go('appHome.campaignSel',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
						}else if($stateParams.flow == 'package') {
							$state.go('appHome.showBudgetPageSub',{parid:returnState.paridInfo,page:$rootScope.extraHandler});
						}
					}
				});
			});
		};
		$rootScope.getMasterCampaignData();
	});

	tmeModuleApp.controller('customPackageController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$mdToast) {
		
		$rootScope.parentid	=	returnState.paridInfo;
		$rootScope.extraHandler	=	$stateParams.page;
		$rootScope.setNoMenu	=	1;
		var self = this;
		$scope.limitVals	=	[0];
		$scope.checkDisabled	=	[];
		$scope.loaderArea	=	{};
		$scope.optName		=	"Option";
		$scope.catLength	=	0;
		$scope.pinLength	=	0;
		$scope.oldCatLength	=	0;
		$scope.oldPinLength	=	0;
		$scope.limiter	=	10;
		$scope.setArrowLimiter	=	1;
		$scope.tabNo	=	3;
		$scope.selRadio	=	1;
		$rootScope.selRadioTenure	=	'12-365';
		$rootScope.expiredePackval = 0;
		$scope.expired_24 = [];
		$scope.expired_24[0] = false;
		$scope.selected_payment_type = $cookieStore.get('payment_type');
		$scope.selected_campaign_name = $cookieStore.get('campaign_names');
		$scope.package_mini = false;
		
		
		//calling sevices for combopackage price
		$scope.selected_camapign = $cookieStore.get('selected_option');
		$scope.combo = 0;
		
		if($scope.selected_camapign == "combo1"){
			$scope.combo = 1;
		}else if ($scope.selected_camapign == "combo2") {
			$scope.omni_type = 5;
		}else if ($scope.selected_camapign == "omni1") {
			$scope.omni_type = 1;
		}else if ($scope.selected_camapign == "omni2") {
			$scope.omni_type = 2;
		}else if ($scope.selected_camapign == "omniultima") {
			$scope.omni_type = 3;
		}else if ($scope.selected_camapign == "omnisupreme") {
			$scope.omni_type = 4;
		}else if ($scope.selected_camapign == "omni7") {
            $scope.omni_type = 7;
        }
			
	
			
		$scope.showcomboprice = false;
		
			$scope.dependent_flag = 0;
			if($scope.selected_camapign == "omniultima" || $scope.selected_camapign == "omnisupreme" || $scope.selected_camapign == "combo2") {
				APIServices.checkpackagedepend(returnState.paridInfo,returnState.ver,$scope.omni_type).success(function(response) {
					if(response.error.code == 0) {
						if(response.package.package_needed == 1) {
							$scope.dependent_flag = 1;
							$scope.customBudgetText = response.package.package_cost;
                           				 $scope.customtenure = response.package.package_tenure;
						}else {
							$scope.dependent_flag = -1;
						}
					}else {						
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
			
				});
			}
		
		
		
		
		
		//Calling Service for contractInformation
		APIServices.getContractData(returnState.paridInfo).success(function(response) {
			$rootScope.companyTempInfo	=	response;
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
			$scope.topMsg	=	"Please Select Desired Campaign Type";  
			if($state.current.name=='appHome.pricechartnew' || $state.current.name=='appHome.omnidomainopt' || $state.current.name=='appHome.omniappdemo' || $state.current.name=='appHome.bannerspecification' || $state.current.name=='appHome.customPackageNational' || $state.current.name=='appHome.customPackage' || $state.current.name=='appHome.smsselection' || $state.current.name=='appHome.smsnumber')
			{ 
				$scope.onlypackage=1;
			}else
			{
				$scope.onlypackage=0;
			}
			
			if($scope.selected_camapign == "omniultima" || $scope.selected_camapign == "omnisupreme" || $scope.selected_camapign == "combo2" || $state.current.name=='appHome.pricechartnew' || $state.current.name=='appHome.customPackageNational' || $state.current.name=='appHome.customPackage' ) {
				APIServices.getBudgetData(3,1,12,returnState.paridInfo,DATACITY,$rootScope.employees.hrInfo.data.empname,$rootScope.companyTempInfo.data.pincode,1,$scope.onlypackage,0,0).success(function(response) {
					$scope.showOptionLoader	=	0;
					$scope.catLengthWise	=	{};
					if(typeof response === 'object') {			
						$rootScope.bestBudget	=	response;
						if(response.result.renewal_cnt == 0) {
							$rootScope.showRenew	=	true;
						}
						$scope.nationallisting_api = $rootScope.bestBudget.result.minbudget_national / 12;
						$scope.nationallistingfinal = $scope.nationallisting_api * 12;
						
						 $scope.nationallistingfinal_ecs = $rootScope.bestBudget.result.monthly_national_budget;
                        $scope.nationallistingfinal_upfront = $rootScope.bestBudget.result.upfront_national_budget;
						
						var l=0;
						angular.forEach(response.result.c_data,function(value,key) {
							var i=0;
							$scope.catLengthWise[key]	=	0;
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
						
						$scope.regular_bgt = $rootScope.bestBudget.result.upldrts_minbudget / 12;
						$rootScope.premium_bgt = $rootScope.bestBudget.result.upldrts_top_minbudget / 12;
						$scope.rw_mini_bdt = $rootScope.bestBudget.result.package_mini;
				
						if($rootScope.bestBudget.result.expiredePackval != undefined && $rootScope.bestBudget.result.expiredePackval != '' && $rootScope.bestBudget.result.expiredePackval != null && $rootScope.bestBudget.result.expiredePackval != 0) {
							$rootScope.expiredePackval = $rootScope.bestBudget.result.expiredePackval;
							$rootScope.expiredePackval_48 = $rootScope.bestBudget.result.expiredePackval_2yrs;
						}
						
						$scope.showrenewalfixed = false;
						if($rootScope.bestBudget.result.remfxdpospackbdgt != undefined && $rootScope.bestBudget.result.remfxdpospackbdgt != null && $rootScope.bestBudget.result.remfxdpospackbdgt != 0 && $rootScope.bestBudget.result.remfxdpospackbdgt != '') {
							$scope.showrenewalfixed = true;
							$scope.renewalfixedval = $rootScope.bestBudget.result.remfxdpospackbdgt;
						}
						 
						if($scope.selected_camapign != "omnisupreme" && $scope.selected_camapign != "omniultima" && $scope.selected_camapign != "combo2") {
							$scope.customBudgetText = $rootScope.premium_bgt;
						}
						
					} else {
						$rootScope.bestBudget	=	{};
						$rootScope.bestBudget['error_code']	=	2;
					}
				});
			}
			
			//APIService for calling existing categories
			APIServices.getExistingCats(returnState.paridInfo,DATACITY).success(function(response) {
				$rootScope.dataExistCats	=	response;
			});
		});

		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
        
        $scope.bestPositionShow	=	{};
		$scope.bestBudgetShow	=	{};
		$scope.bidderValue		=	{};
		$scope.cattotalBudget	=	{};
		$scope.bidValue			=	{};
		$scope.inventory		=	{};
		$scope.callcount		=	{};
		$scope.callcountTotal	=	{};
		$scope.callcountTotalFix=	{};
		$scope.selected			=	[];
		$scope.totalBudgetShowMain	=	"";
		var catKeyOld	=	"";
		$scope.is_in_datacity_pincode = "";
		$scope.response_2 = "";
		$scope.pushDisabled	=	{};
		$scope.catListRemove	=	[];
		$scope.callCountTotalTop	=	0;
		$scope.callCountTotalTopExtra	=	0;
		$scope.loadInit	=	function(catKey,pin,bestBudg,index,index2) {
			switch($scope.tabNo) {
				case 1:
					$scope.setArrowLimiter	=	1;
				break;
				case 2:
					$scope.setArrowLimiter	=	1;
				break;
				case 3:
					$scope.setArrowLimiter	=	1;
				break;
				case 4:
					$scope.setArrowLimiter	=	1;
				break;
				case 5:
					$scope.setArrowLimiter	=	0;
				break;
			}
			
			if((catKeyOld	!=	catKey) ||  ($scope.bestPositionShow[catKey] === undefined)) {
				$scope.bestPositionShow[catKey]	=	{};
				$scope.bestBudgetShow[catKey]	=	{};
				
				$scope.bidderValue[catKey]		=	{};
				$scope.bidValue[catKey]		=	{};
				$scope.inventory[catKey]		=	{};
				$scope.callcount[catKey]	=	{};
			}
			if(index == 0) {
				$scope.callcountTotal[catKey]	=	0;
				$scope.callcountTotalFix[catKey]	=	0;
			}
			if(index2 == 0) {
				$scope.callCountTotalTop	=	0;
				$scope.callCountTotalTopExtra	=	0;
			}
			$scope.bestPositionShow[catKey][pin]	=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].best_flg;
			$scope.bestBudgetShow[catKey][pin]	=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].budget;
			$scope.bidderValue[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidder;
			$scope.bidValue[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].bidvalue;
			$scope.inventory[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].pos[$scope.bestPositionShow[catKey][pin]].inv_avail;
			$scope.callcount[catKey][pin]		=	$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.callcountTotal[catKey]		=	$scope.callcountTotal[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.callcountTotalFix[catKey]	=	$scope.callcountTotalFix[catKey]+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			
			$scope.cattotalBudget[catKey]	=	$rootScope.bestBudget.result.c_data[catKey].f_bgt;
			if($rootScope.bestBudget.result.tb_bgt > $rootScope.bestBudget.result.city_bgt) {
				$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.tb_bgt;
			} else {
				$scope.totalBudgetShowMain	=	$rootScope.bestBudget.result.city_bgt;
			}
			
			$scope.callCountTotalTop		=	$scope.callCountTotalTop+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			$scope.callCountTotalTopExtra	=	$scope.callCountTotalTopExtra+$rootScope.bestBudget.result.c_data[catKey].pin_data[pin].cnt_f;
			catKeyOld	=	catKey;
		};
        
        $rootScope.setCustomBudgetPackage	=	function(ev,switchParam,page) {
			if(typeof switchParam === 'undefined') {
				var redirectPage	=	'appHome.showExistInventory';
			} else if(switchParam == 'national'){
				var redirectPage	=	'appHome.showBudgetDataNational';
			}else if(switchParam == 'combo' && page != ''  && page != undefined){
				var redirectPage	=	page ;
			}else if(switchParam == 'combo' && (page == '' || page == undefined)){
				var redirectPage	=	'appHome.budgetsummary';
			}else if(switchParam == 'combo2'){
				var redirectPage	=	'appHome.budgetsummary';
			}else {
				var redirectPage	=	'appHome.showBudgetPageSub';
			}
			if($scope.dependent_flag == -1) {
				
				if(redirectPage == 'banner' || redirectPage == 'jdrrplus') {
					$state.go('appHome.bannerspecification',{parid:returnState.paridInfo,type:redirectPage,ver:returnState.ver,page:$rootScope.extraHandler});
				}else {
					$state.go("appHome.budgetsummary",{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
				}
				return false;
			}
			
			APIServices.getAllPincodes(returnState.paridInfo).success(function(response2) {
					//$scope.showLoader	=	1;
				$scope.response_2=response2;
				$scope.is_in_datacity_pincode = response2.is_datacity_pincode;
				
				if($rootScope.expiredePackval != 0){
					if($scope.expired_24[0] == true) {
						$scope.min_budget_mon = Math.round((parseInt($rootScope.expiredePackval_48)/24),0);
						$scope.min_budget = Math.round((parseInt($rootScope.expiredePackval_48)/2),0);
					}else{
						$scope.min_budget_mon = Math.round((parseInt($rootScope.expiredePackval)/12),0);
						$scope.min_budget = $rootScope.expiredePackval;
					}
				}else if($scope.selected_payment_type == "upfront" && parseInt($scope.rw_mini_bdt) < Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0)){
					$scope.min_budget_mon = parseInt($scope.rw_mini_bdt);
					$scope.min_budget = $scope.min_budget_mon * 12;
				}else {
					$scope.min_budget_mon = Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0);
					$scope.min_budget = $rootScope.bestBudget.result.city_bgt;
				}
				if($scope.response_2 && typeof $scope.is_in_datacity_pincode == 'number' && $scope.is_in_datacity_pincode  == 0 && switchParam != 'combo'  && $scope.dependent_flag !=1 )
				{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Oooops !!!! Package Campaign is not allowed .';
						$rootScope.commonShowContent = 'Bform Pincode '+$scope.response_2.physical_pincode+' does not belong to '+DATACITY.toUpperCase();
						return false;
					
				}
				else if(($scope.customBudgetText == "" || typeof $scope.customBudgetText === 'undefined') && switchParam != 'combo' && $scope.customBudgetText !='weekly' && $scope.dependent_flag !=1 && $scope.customBudgetText !='showrenewalfixed') {
					$mdToast.show(
						$mdToast.simple()
						.content('Please select custom package value')
						.position('top right')
						.hideDelay(3000)
					);	
					return false;
				}else if(((parseInt($scope.customBudgetText)*12) < parseInt($scope.min_budget)) && switchParam != 'combo' && $scope.customBudgetText !='weekly' && $scope.dependent_flag !=1 && $scope.customBudgetText !='showrenewalfixed' && $scope.package_mini == false) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Oooops !!!! The package budget is low';
					$rootScope.commonShowContent = 'You have to enter atleast a minimum of Rs. '+$scope.min_budget_mon;
					return false;	
				}else if(((parseInt($scope.customBudgetText)*12) < $rootScope.bestBudget.result.tb_bgt) && switchParam != 'combo' && $scope.customBudgetText !='weekly' && $scope.dependent_flag !=1 && $scope.customBudgetText !='showrenewalfixed' && $scope.package_mini == false) {
					var confirm = $mdDialog.confirm()
						  .title('Budget is less then the suggested budget')
						  .content('Suggested budget is Rs.'+Math.round((parseInt($rootScope.bestBudget.result.tb_bgt)/12),0)+' per month')
						  .ariaLabel('Lucky day')
						  .targetEvent(ev)
						  .ok('Ignore and Proceed')
						  .cancel('Edit and Proceed');
					$mdDialog.show(confirm).then(function() {
						if($scope.expired_24[0] == true && $rootScope.expiredePackval != 0 && $scope.customebudgettxt == true && $scope.customBudgetText !='removedfixedposition' && $scope.customBudgetText !="weekly" && $scope.customBudgetText !="monthly") {
							var customBudget	=	parseInt($scope.customBudgetText)*24;
						}else if(switchParam != 'combo') {
							var customBudget	=	parseInt($scope.customBudgetText)*12;
						}else {
							var customBudget	= $scope.combo_yearly;
						}
						$scope.submitBudget(customBudget,redirectPage);
					}, function() {
						$scope.status = 'You decided to keep your debt.';
					});
			
				} else {
					if($scope.expired_24[0] == true && $rootScope.expiredePackval != 0 && $scope.customebudgettxt == true && $scope.customBudgetText !='removedfixedposition' && $scope.customBudgetText !="weekly" && $scope.customBudgetText !="monthly") {
						var customBudget	=	parseInt($scope.customBudgetText)*24;
					}else if(switchParam != 'combo' && switchParam != 'combo2') {
						var customBudget	=	parseInt($scope.customBudgetText)*12;
					}else if($scope.dependent_flag ==1 ){
						var customBudget	= $scope.customBudgetText;
					}
					if($scope.customBudgetText =='removedfixedposition') {
						$scope.selected_campaign_name = $scope.selected_campaign_name.replace("premium listing","fixed_renewal");
						APIServices.payment_type(returnState.paridInfo,$scope.selected_campaign_name,returnState.ver).success(function(response) {
						});
						var customBudget	= parseInt($scope.renewalfixedval);
					}
					if($scope.package_mini == true) {
						$scope.selected_campaign_name = $scope.selected_campaign_name.replace("premium listing","r&w_mini");
						APIServices.payment_type(returnState.paridInfo,$scope.selected_campaign_name,returnState.ver).success(function(response) {
						});
					}
					$scope.submitBudget(customBudget,redirectPage);
				}
				
				if($rootScope.expiredePackval != 0 && $scope.customebudgettxt == true && $scope.customBudgetText !='removedfixedposition' && $scope.customBudgetText !="weekly" && $scope.customBudgetText !="monthly" &&
				(parseInt($scope.customBudgetText) < parseInt($rootScope.premium_bgt) || ($scope.expired_24[0] == true))){
					
					$scope.selected_campaign_name = $scope.selected_campaign_name.replace("premium listing","package_expired");
					APIServices.payment_type(returnState.paridInfo,$scope.selected_campaign_name,returnState.ver).success(function(response) {
						$cookieStore.put('campaign_names',"package_expired");
					});	
				}
			
			});
		};
		
		
		$scope.show_bonuspop = function(ev,val,type){
			$mdDialog.show({
				controller: bonusmsgcontroller,
				templateUrl: 'partials/bonusmsg.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			})
			
			$rootScope.temp_dispprice = val;
			
			if(type == "mini"){
				$scope.package_mini = true;
			}else{
				$scope.package_mini = false;
			}	
		}
		
		function bonusmsgcontroller($scope,$mdDialog,$mdToast,APIServices) {
			
			$scope.displayprice = $rootScope.temp_dispprice;
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
		}
		
		
		$scope.submitArr	=	{};
		$scope.submitArr['c_data']	=	{};
		$scope.submitArr['packageBudget']	=	0;
		$scope.submitArr['pdgBudget']	=	0;
		$scope.submitArr['tenure']	=	0;
		
		//Function to show custome budget txt box
		$scope.customebudgettxt = false;
		$scope.showcustomebudget = function() {
				$scope.customebudgettxt = !$scope.customebudgettxt;
				$scope.package_mini = false;
		}
		
		//Function called for submitting budget
		$scope.submitBudget	=	function(customBudget,redirectPage) {
			
			var tenureExp	=	$rootScope.selRadioTenure.split('-');
			$scope.submitArr['tenure']	=	tenureExp[0];
            if($rootScope.premium_2years != 0 && $rootScope.premium_2years != undefined ){
				$scope.submitArr['tenure']  = $rootScope.pricechart_rb['package_tenure'];
				$scope.package_10dp =1;
			}else if($rootScope.expiredePackval != 0 && $rootScope.expiredePackval != undefined) {
				$scope.submitArr['tenure']	= $rootScope.pricechart_rb['package_tenure'];
                $scope.package_10dp =0;
            }else if($scope.dependent_flag == 1 && $scope.customtenure != undefined  && $scope.customtenure !=0){
				$scope.submitArr['tenure']  =  $scope.customtenure;
                $scope.package_10dp =0;
            }else{
				$scope.package_10dp =0;
			}
			$scope.submitArr['actual_bgt']	=	0;
			var catStr	=	"";
			$scope.submitArr['removeCatStr']	=	catStr;
			var budgetFactor	=	1 + ((customBudget- $rootScope.bestBudget.result.tb_bgt)/$rootScope.bestBudget.result.tb_bgt);
			
			angular.forEach($scope.bestBudgetShow,function(value,key) {
				$scope.submitArr['totBudget']	=	{};
				if(customBudget == 0) {
					$scope.submitArr['totBudget']	=	$scope.totalBudgetShowMain;
				} else {
					$scope.submitArr['totBudget']	=	customBudget;
				}
				$scope.submitArr['customBudget']	=	customBudget;
				$scope.submitArr['reg_bgt']		=	$rootScope.bestBudget.result.reg_bgt;
				$scope.submitArr['city_bgt']	=	$rootScope.bestBudget.result.city_bgt;
				if($scope.cattotalBudget[key]) {
					$scope.cattotalBudget[key]	=	$scope.cattotalBudget[key] * budgetFactor;
					$scope.submitArr['actual_bgt']	=	$scope.submitArr['actual_bgt'] + $scope.cattotalBudget[key];
					$scope.submitArr['c_data'][key]	=	{};
					$scope.submitArr['c_data'][key]['c_bgt']	=	$scope.cattotalBudget[key];
					$scope.submitArr['c_data'][key]['bflg']		=	$rootScope.bestBudget.result.c_data[key]['bflg'];
					$scope.submitArr['c_data'][key]['bm_bgt']	=	$rootScope.bestBudget.result.c_data[key]['bm_bgt'];
					$scope.submitArr['c_data'][key]['cnm']		=	$rootScope.bestBudget.result.c_data[key]['cnm'];
					$scope.submitArr['c_data'][key]['ncid']		=	$rootScope.bestBudget.result.c_data[key]['ncid'];
					$scope.submitArr['c_data'][key]['pin_data']	=	{};
					angular.forEach(value,function(value2,key2) {
						$scope.submitArr['c_data'][key]['pin_data'][key2]	=	{};
						$scope.submitArr['c_data'][key]['pin_data'][key2]['pos']	=	{};
						$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']	=	{};
						$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt']	=	$rootScope.bestBudget.result.c_data[key]['pin_data'][key2].cnt;	
						$scope.submitArr['c_data'][key]['pin_data'][key2]['cnt_f']	=	$rootScope.bestBudget.result.c_data[key]['pin_data'][key2].cnt_f;	
						
						$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]	=	{};
						$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['budget']	=	value2;
						if($scope.bestPositionShow[key][key2] == 100) {
							$scope.submitArr['packageBudget']		=	$scope.submitArr['packageBudget']+value2;
						} else {
							$scope.submitArr['pdgBudget']		=	$scope.submitArr['pdgBudget']+value2;
						}
						$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['bidvalue']	=	$scope.bidValue[key][key2];
						$scope.submitArr['c_data'][key]['pin_data'][key2]['pos'][$scope.bestPositionShow[key][key2]]['inventory']	=	$scope.inventory[key][key2];
					});
				}
			});
			if($scope.submitArr['actual_bgt'] == 0) {				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please select categories to proceed.";
				return false;		
			}		
			$scope.showOptionLoader	=	1;	
			
			$cookieStore.put('submitArr_package',$scope.submitArr);
			
            APIServices.submitBudgetData(returnState.paridInfo,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,$scope.submitArr,$scope.package_10dp).success(function(response) {
				$scope.showOptionLoader	=	0;
                if(response.error_code == 0)
                {
					if(redirectPage == 'appHome.nationallisting'){
						$state.go('appHome.nationallisting',{parid:$stateParams.parid,type:'nationallisting',ver:$stateParams.ver,page:$rootScope.extraHandler});	
						}
					else if(redirectPage == 'appHome.showExistInventory') {
						$state.go(redirectPage,{parid:returnState.paridInfo,flow:'package',page:$rootScope.extraHandler});
					}else if(redirectPage == 'banner' || redirectPage == 'jdrrplus') {
						$state.go('appHome.bannerspecification',{parid:returnState.paridInfo,type:redirectPage,ver:returnState.ver,page:$rootScope.extraHandler});
					}else if(redirectPage == 'appHome.budgetsummary'){
						$state.go(redirectPage,{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
					}else {
						$state.go(redirectPage,{parid:returnState.paridInfo,page:$rootScope.extraHandler});
					}
				}else
				{
					$mdToast.show(
					$mdToast.simple()
					.content('Some error found while submiting budget. Please contact software team')
					.position('top right')
					.hideDelay(3000)
					);
				}
			});
		};
		
		Object.size = function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) { size++;}
			}
			return size;
		};	
		
		function combopopup($scope,$mdDialog,$mdToast,APIServices){
				setTimeout(
					function() {
						$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
					},100
				);
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			$scope.showbannerpopup= function(ev) {
				$mdDialog.hide();
			}

		}
		
		$scope.open_combo		= function(ev) {

			$mdDialog.show({
				controller: combopopup,
				templateUrl: 'partials/combopopup.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}
		
		$scope.goback = function(from) {
			
			if(from == "national") {
				window.location = "../business/00_disp_categories_package.php";
			}else {
				var wchcombo=$cookieStore.get('selected_option');
				
				if(wchcombo == "combo1" || wchcombo == "combo2") {
					$state.go('appHome.omnidomainreg',{parid:returnState.paridInfo,ver:returnState.ver,page:$stateParams.page});
				}else {
					$state.go('appHome.pricechartnew',{parid:returnState.paridInfo,ver:returnState.ver,page:$stateParams.page});
				}
			}
		}
		
	});

	
	tmeModuleApp.controller('jdrrpopupController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {

		$rootScope.setNoMenu	=	1;
		$rootScope.parentid	=	returnState.paridInfo;
		$scope.showparam = returnState.page;
		$scope.showimg = returnState.image;
		$scope.show_sample_banner = 1;
		$scope.show_jdrr_samaple = 1;
		$scope.position = 0;
		$scope.img_position = 0;
		$rootScope.extraHandler	=	$stateParams.page;
		$scope.show_oly_jdrr = false;


		
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		//Handling for JDA
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}


	
		
		$scope.banner_slider_nxt = function() {
			if($scope.position > -3996){
				$scope.position = $scope.position - 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.position+'px, 0, 0)'})
				$scope.show_sample_banner = $scope.show_sample_banner + 1
			}
		}
		
		$scope.banner_slider_prev = function() {
			if($scope.position < 0){
				$scope.position = $scope.position + 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.position+'px, 0, 0)'})
				$scope.show_sample_banner = $scope.show_sample_banner - 1;
			}
		}
		
		$scope.jdrrplus_proceed  = function() {
			$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:'Jdrr Certificate',image:5,ver:returnState.ver,page:$rootScope.extraHandler});
		}
		$scope.jdrr_slider_nxt = function() {
			if($scope.img_position > -1998){
				$scope.img_position = $scope.img_position - 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.img_position+'px, 0, 0)'})
				$scope.show_jdrr_samaple = $scope.show_jdrr_samaple + 1
			}
		}
		
		$scope.jdrr_slider_prev = function() {
			if($scope.img_position < 0){
				$scope.img_position = $scope.img_position + 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.img_position+'px, 0, 0)'})
				$scope.show_jdrr_samaple = $scope.show_jdrr_samaple - 1;
			}
		}
		
		
		if($scope.showimg == 5 || $scope.showimg == 6 ) {
			setTimeout(
				function() {
					 $('html, body').animate({scrollTop: $('#rating_img').offset().top}, 500);
				},500
			);
		}
		
		
		if($scope.showimg == 4) {
			//~ $rootScope.showbannerdel = true;
			/*APIServices.checkbanner(returnState.paridInfo,returnState.ver).success(function(response) {

				if(response.error.code == 1) {
					$rootScope.showbannerdel = true;
				}
				else {
					$rootScope.showbannerdel = false;
				}
			});*/
		}

		
		if($scope.showimg == 7) {
			//~ APIServices.checkjdrr(returnState.paridInfo,returnState.ver).success(function(response) {
					//~ if(response.code == 0) {
						//~ $rootScope.showjdrrdel = true;
					//~ }else {
						//~ $rootScope.showjdrrdel = false;
					//~ }
			//~ });
		}
		
		$scope.nextbanner = function(pg,opp) {
			$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:pg,image:opp,ver:returnState.ver,page:$rootScope.extraHandler});
		} 

		$scope.goto_budgetsummary = function() {
			$rootScope.targetUrl = 'appHome.pricechartnew';   
			$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,ver:returnState.ver,page:$rootScope.extraHandler});
		}

		$scope.deletebanner = function() {
			if(confirm("This Is A Banner Campaign Contract Are You Sure You Want To Remove Banner Campaign ")) {
				APIServices.deletebanner(returnState.paridInfo,returnState.ver).success(function(response) {
					if(response.error.code == 0) {
					$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'Please try again';
						return false;
					}
				});
			}else {
				return false;
			}

		}
		
		$scope.deletejdrrplus = function() {
			if(confirm("This will delete both JDRR Certificate and Banner")) {
				APIServices.deletejdrrplus(returnState.paridInfo,returnState.ver).success(function(response) {
					if(response.error.code == 0) {
					$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'Please try again';
						return false;
					}
				});
			}else {
				return false;
			}

		}
		
		
		
		
		$scope.deletejdrr = function(){
			if(confirm("This Is A JDRR Campaign Contract Are You Sure You Want To Remove JDRR Campaign ")) {
				APIServices.deletejdrr(returnState.paridInfo,returnState.ver).success(function(response) {
					if(response.error.code == 0) {
						$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:'Banner',image:1,ver:returnState.ver,page:$rootScope.extraHandler});
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'Please try again';
						return false;
					}
				});
			}else {
				return false;
			}
		}


		$scope.bannerpopup = function() {
			APIServices.bannerlog(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response == 0) {
					$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
				}else {
					$mdToast.show(
						$mdToast.simple()
						.content('Please Try Again')
						.position('top left')
						.hideDelay(4000)
					);
					return false;
				}
			});
		}
		
		
		$scope.showjdrrsample = function(ev) {
			$mdDialog.show({
				controller: bannerspecificationpop,
				templateUrl: 'partials/bannerspecificationpop.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
			$rootScope.samplepop ='jdrrcertificate';
		}

		$scope.showbannersample = function(banner_type){
			if (banner_type==5)
			{
				$window.open('../business/livebannerdemo.php?parentid='+returnState.paridInfo+'&module=tme&data_city='+DATACITY+'&banner_type=5', '_blank');
			}else
			{
				$window.open('../business/livebannerdemo.php?parentid='+returnState.paridInfo+'&module=tme&data_city='+DATACITY+'&banner_type=13', '_blank');
			}
			
			
			
		}

		$scope.modeselection = function() {
			APIServices.jdrrlog(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response == 0) {
					$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:'Banner',image:1,ver:returnState.ver,page:$rootScope.extraHandler});
				}else {
					return false;
				}
			});
		}
		
		$scope.addjdrr = function() {
			APIServices.addjdrr(returnState.paridInfo,returnState.ver,0).success(function(response) {
				if(response.error.code == 0) {
					$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:'Banner',image:1,ver:returnState.ver,page:$rootScope.extraHandler});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Please try again';
				}
			});
				
		}
		
		$scope.addonlyjdrr = function() {
			APIServices.addjdrr(returnState.paridInfo,returnState.ver,0).success(function(response) {
				if(response.error.code == 0) {
					$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Please try again';
				}
			});
				
		}
		

		$scope.addspecification = function(ev) {
			$mdDialog.show({
				controller: bannerspecificationpop,
				templateUrl: 'partials/bannerspecificationpop.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});

			$rootScope.samplepop ='banner';
		}


		function bannerspecificationpop($scope,$mdDialog,$mdToast,APIServices){
			
			
			setTimeout(
					function() {
						$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
					},100
				);
			
			

			$scope.banner_specification = {};
			$scope.show_demo_img = 1;
			$scope.popupcond = $rootScope.samplepop;
			
			APIServices.get_banner_spec(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response.error.code == 0){
					$scope.banner_specification[0]  = response.error.msg;
				} else {
					$scope.banner_specification[0] = '';
				}
			});

			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}

			$scope.addbanner = function() {
				if($scope.banner_specification[0] == '' || $scope.banner_specification[0] == null ) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Please Enter Client Instruction';
					return false;

				}else {
					APIServices.addjdrrplus(returnState.paridInfo,returnState.ver,$scope.banner_specification[0],0).success(function(response) {
						if(response.error.code == 0){
							$mdDialog.hide();
							$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
						} else if(response.error.code == 2){
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = response.error.msg;
							$mdDialog.hide();
							$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = response.error.msg;
							return false;
						}
					});
				}
			}

			$scope.shownxtimg = function () {
				if($scope.show_demo_img != 5) {
					$scope.show_demo_img = $scope.show_demo_img + 1;
				}
			}

			$scope.showpreimg = function () {
				if($scope.show_demo_img != 1) {
					$scope.show_demo_img = $scope.show_demo_img - 1;
				}
			}


		}
	});
	
	tmeModuleApp.controller('selljdomniController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		
		//~ APIServices.go_to_payment_page(returnState.paridInfo,returnState.ver,0,'','','','',0,1,0,'').success(function(response) {
		//~ });
		
		$rootScope.extraHandler =  $stateParams.page; 
		
		if($stateParams.page == 'jda') {
			$scope.other_parameter = 1;
		}else {
			$scope.other_parameter = 0;
		}
		
		$scope.category_type = '';
		var template_ids='';
		APIServices.getExistingCats($stateParams.parid,DATACITY).success(function(response) {
			$rootScope.dataExistCats	=	response;
			if(response.error.code == 0) {
				if(response.info){
					if(response.info.template_id){
						template_ids=response.info.template_id;
						APIServices.setTemplateId($stateParams.parid,$stateParams.ver,template_ids,DATACITY).success(function(response) {
							
							APIServices.checkCategoryType($stateParams.parid,$stateParams.ver).success(function(response) {
								if(response.error.code ==0) {
									$scope.category_type = 'product';
								}else {
									$scope.category_type = 'service';
								}
							});
							
						});
					}
					
				}
			}
		});
			
		
		
		$scope.addjdomni = function() {
			APIServices.addjdomni($stateParams.parid,$stateParams.ver,0).success(function(response) {
				if(response.error.code == 0) {
					$state.go('appHome.omnidomainreg',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = 'Please Try Again';
				}
			});
		}
		$scope.addjdomni_proceed = function(ev) {
			$cookieStore.put('skip_demo',$stateParams.parid);
			APIServices.fetchDemoLinkDetails($stateParams.parid,$stateParams.ver).success(function(response){  
				if(response.error.code == 0) { 
					$state.go('appHome.pricechartnew',{parid:$stateParams.parid,ver:$stateParams.ver,page:$stateParams.page});
				}else {
					$rootScope.showCommonPop = 'omni_link';
				}		
			});
		}
		$rootScope.proceed_omni = function(){
			$rootScope.showCommonPop = 0;
			$state.go('appHome.pricechartnew',{parid:$stateParams.parid,ver:$stateParams.ver,page:$stateParams.page});
		}
		$scope.deletejdomni = function() {
			APIServices.deletejdomni($stateParams.parid,$stateParams.ver).success(function(response) {
				if(response.error.code == 0) {
					APIServices.deletedomainname($stateParams.parid,$stateParams.ver).success(function(response1) {
						if(response1.error.code == 0) {
							$state.go('appHome.dialogjdrrpopup',{parid:$stateParams.parid,pageshow:'Jdrr Certificate',image:5,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = response1.error.msg;
						}
					
					});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = response1.error.msg;
				}
			});
		}
		
		$scope.showomni = function() {
			$window.open('http://www.jdomni.com', '_blank');
		}
		
		$scope.showpresentation = function() {
			$window.open('../JD_Omni.pdf', '_blank');
		}
		
		
		$scope.send_demo_pop = function(ev,val) {
			$rootScope.previewTheme = val;
			//console.log($rootScope.previewTheme);
			$mdDialog.show({
				controller: sendomnidemocontroller,
				templateUrl: 'partials/sendomnidemo.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			})
				
			//~ if($scope.category_type == 'product') {
				//~ $mdDialog.show({
					//~ controller: sendomnidemocontroller,
					//~ templateUrl: 'partials/sendomnidemo.html',
					//~ parent: angular.element(document.body),
					//~ targetEvent: ev,
				//~ })
			//~ }else {
				//~ $mdDialog.show(
				  //~ $mdDialog.alert()
					//~ .clickOutsideToClose(true)
					//~ .content('Demo store for your category is not available at this time.')
					//~ .ariaLabel('Alert Dialog Demo')
					//~ .ok('ok')
					//~ .targetEvent(ev)
				//~ );
			//~ }
		
		}
		
		 $scope.send_YOW_pop = function(ev,check) {
			$rootScope.checkYOW = check;
            $mdDialog.show({
						controller: yourownwebsitecontroller,
						templateUrl: 'partials/yourownwebsite.html',
						parent: angular.element(document.body),
						targetEvent: ev,
					})

        }
		
		function sendomnidemocontroller($scope){
			$scope.previewTheme = $rootScope.previewTheme;
			APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
				$scope.mobile_count = [];
				$scope.mobile_arr = [];
				$scope.email_count = [];
				$scope.email_arr = [];
				
				if(response.error.code == 0) {
					$scope.mobile_arr= response.error.result.mobile.split(',');
					$scope.email_arr= response.error.result.email.split(',');
					$scope.mobile_count = Object.keys($scope.mobile_arr);
					$scope.email_count = Object.keys($scope.email_arr);
					
				}else{
					$scope.mobile_count[0] = "0";
					$scope.email_count[0] = "0";
					$scope.mobile_arr[0] = "";
					$scope.email_arr[0] = "";
				}
				
			});
			$rootScope.redirecthref = 0;
			$rootScope.redirectURL = '';
			$scope.addmobile = function() {
				if($scope.mobile_arr[$scope.mobile_count.length -1] != '' && $scope.mobile_arr[$scope.mobile_count.length -1] != undefined){
					$scope.mobile_count.push($scope.mobile_count.length);
				}
			}
			
			
			$scope.addemail = function() {
				if($scope.email_arr[$scope.email_count.length -1] != '' && $scope.email_arr[$scope.email_count.length -1] != undefined){
					$scope.email_count.push($scope.email_count.length);
				}
			}
			
			
			$scope.call_demo_api = function(ev,val){
				$scope.getLinkVal = val;
				var mob_pattern = /^[0-9]{10}$/;
				var email_pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
				
				if($scope.mobile_arr.toString() == '' || $scope.email_arr.toString() == '') {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Website Theme Link';
					$rootScope.commonShowContent = 'Please enter mobile number and emailid';
				}else{
					var invalid_mobile =0;
					var invalid_email =0;
					
					angular.forEach($scope.mobile_arr,function(val,key) {
						if(!mob_pattern.test(val) && val !=''){
							invalid_mobile =1;
						}
					});
					
					angular.forEach($scope.email_arr,function(val,key) {
						if(!email_pattern.test(val) && val !=''){
							invalid_email =1;
						}
					});
					
				$scope.mobiledup=[];
                var moblen= $scope.mobile_arr.length;
                if(moblen >1){
                for(i=0;i<=moblen;i++){
                    if($scope.mobile_arr[i]!=''){
                    var dup = $scope.mobiledup.indexOf($scope.mobile_arr[i]);
                    if(dup == -1){
                        $scope.mobiledup.push($scope.mobile_arr[i]);
                    }else{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Website Theme Link';
						$rootScope.commonShowContent = 'Please do not enter duplicate mobile numbers!';
                    return false;
                    }
                   }
                  }
                 }
				$scope.emaildup=[];
                var emaillen= $scope.email_arr.length;
                if(emaillen >1){
                for(i=0;i<=emaillen;i++){
                    if($scope.email_arr[i]!=''){
                    var edup = $scope.emaildup.indexOf($scope.email_arr[i]);
                    if(edup == -1){
                        $scope.emaildup.push($scope.email_arr[i]);
                    }else{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Website Theme Link';
						$rootScope.commonShowContent = 'Please do not enter duplicate email ids!';
                    return false;
                    }
                   }
                  }
                 }
				
					if(invalid_mobile == 1){
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Website Theme Link';
						$rootScope.commonShowContent = 'Please enter proper mobile number';
						return false;
					}
					
					if(invalid_email == 1){
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Website Theme Link';
						$rootScope.commonShowContent = 'Please enter proper emailid';
						return false;
					}
					
					$rootScope.mobile_str =  $scope.mobile_arr.toString();
					$rootScope.email_str =  $scope.email_arr.toString();
						
					APIServices.sendomnidemo($stateParams.parid,$stateParams.ver,$scope.mobile_arr.toString(),$scope.email_arr.toString(),'',$scope.getLinkVal).success(function(response) {
						if(response.error.code == 0) {
							APIServices.insertDemoLinkDetails($stateParams.parid,$stateParams.ver).success(function(response2){
								if($rootScope.previewTheme == 0){
									$mdDialog.hide();
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = 'Website Theme Link';
									$rootScope.commonShowContent = 'Website Theme Link has been successfully sent.';
								}else if($rootScope.previewTheme == 1){
									$rootScope.redirecthref = 1;
									$rootScope.redirectURL = response.error.result.link;
									//~ $rootScope.redirecturl();
									$window.location.href = response.error.result.link;
									//~ $window.open($rootScope.redirectURL, '_blank');
									
								}
								
							});
							
								
						}else if(response.error.code == 3){
							$mdDialog.show({
								controller: choosedemocategorycontroller,
								templateUrl: 'partials/choosedemocategory.html',
								parent: angular.element(document.body),
								targetEvent: ev,
							});
						}else if(response.error.code == 1){
							$mdDialog.hide();
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = " Your Own Website Demo Link";
							$rootScope.commonShowContent = "Client already has own website.";
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'alert!!';
							$rootScope.commonShowContent = response.error.msg;
							return false;
						}
						
					});
				}
			}
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			
			
		}
		
		$rootScope.redirecturl = function(){
				if($rootScope.redirecthref = 1){
					$window.open($rootScope.redirectURL, '_blank');
				};
			}
		//send your own website link
			function yourownwebsitecontroller($scope,$window,$rootScope){
				$scope.checkYOW = $rootScope.checkYOW;
					APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
						$scope.mobile_count = [];
						$scope.mobile_arr = [];
						$scope.email_count = [];
						$scope.email_arr = [];
						if(response.error.code == 0) {
							$scope.mobile_arr= response.error.result.mobile.split(',');
							$scope.email_arr= response.error.result.email.split(',');
							$scope.mobile_count = Object.keys($scope.mobile_arr);
							$scope.email_count = Object.keys($scope.email_arr);
						}else{
							$scope.mobile_count[0] = "0";
							$scope.email_count[0] = "0";
							$scope.mobile_arr[0] = "";
							$scope.email_arr[0] = "";
						}
					});
					$scope.addmobile = function() {
						if($scope.mobile_arr[$scope.mobile_count.length -1] != '' && $scope.mobile_arr[$scope.mobile_count.length -1] != undefined){
							$scope.mobile_count.push($scope.mobile_count.length);
						}
					}
					$scope.addemail = function() {
						if($scope.email_arr[$scope.email_count.length -1] != '' && $scope.email_arr[$scope.email_count.length -1] != undefined){
							$scope.email_count.push($scope.email_count.length);
						}
					}
					$scope.call_demo_api = function(ev){
						var mob_pattern = /^[0-9]{10}$/;
						var email_pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
						if($scope.mobile_arr.toString() == '' || $scope.email_arr.toString() == '') {
							   $rootScope.showCommonPop = 1;
							$rootScope.commonTitle = " Your Own Website Demo Link";
							$rootScope.commonShowContent = "Please enter mobile number and emailid";
				
						}else{
							var invalid_mobile =0;
							var invalid_email =0;
							angular.forEach($scope.mobile_arr,function(val,key) {
								if(!mob_pattern.test(val) && val !=''){
									invalid_mobile =1;
								}
							});
							angular.forEach($scope.email_arr,function(val,key) {
								if(!email_pattern.test(val) && val !=''){
									invalid_email =1;
								}
							});
							$scope.mobiledup=[];
							var moblen= $scope.mobile_arr.length;
							if(moblen >1){
							for(i=0;i<=moblen;i++){
								if($scope.mobile_arr[i]!=''){
								var dup = $scope.mobiledup.indexOf($scope.mobile_arr[i]);
								if(dup == -1){
									$scope.mobiledup.push($scope.mobile_arr[i]);
								}else{
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = 'Website Theme Link';
									$rootScope.commonShowContent = 'Please do not enter duplicate mobile numbers!';
								return false;
								}
							   }
							  }
							 }
							$scope.emaildup=[];
							var emaillen= $scope.email_arr.length;
							if(emaillen >1){
							for(i=0;i<=emaillen;i++){
								if($scope.email_arr[i]!=''){
								var edup = $scope.emaildup.indexOf($scope.email_arr[i]);
								if(edup == -1){
									$scope.emaildup.push($scope.email_arr[i]);
								}else{
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = 'Website Theme Link';
									$rootScope.commonShowContent = 'Please do not enter duplicate email ids!';
								return false;
								}
							   }
							  }
							 }
							if(invalid_mobile == 1){
								 $rootScope.showCommonPop = 1;
								$rootScope.commonTitle = " Your Own Website Demo Link";
								$rootScope.commonShowContent = "Please enter proper mobile number";
								return false;
							}
							if(invalid_email == 1){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = " Your Own Website Demo Link";
								$rootScope.commonShowContent = "Please enter proper emailid";
								return false;
							}
							$rootScope.mobile_str =  $scope.mobile_arr.toString();
							$rootScope.email_str =  $scope.email_arr.toString();
						APIServices.sendYOWlink($stateParams.parid,$stateParams.ver,$scope.mobile_arr.toString(),$scope.email_arr.toString(),$rootScope.checkYOW).success(function(response) {
								if(response.error.code == 0) {
									APIServices.insertDemoLinkDetails($stateParams.parid,$stateParams.ver).success(function(response2){
										/*$mdDialog.hide();
										$mdDialog.show(
										  $mdDialog.alert()
											.clickOutsideToClose(true)
											.content('Omni Demo links sent successfully.')
											.ariaLabel('Alert Dialog Demo')
											.ok('ok')
											.targetEvent(ev)
										);*/
										
										if(response.check == 1){
											if($rootScope.checkYOW == 1){
												$mdDialog.hide();
												
												
												var url = response.error.result.redirectUrl;
												//~ console.log($window);
												//~ var window = $window;
												//~ $window.open(url,'_blank');
												$window.location.href = response.error.result.redirectUrl;
												//~ $window.open(response.error.result.redirectUrl, '_blank');
												//window.open(url,'_blank'); //location.href = response;
												  //$window.open(url, 'width=500,height=400')
											}else{
												$mdDialog.hide();
												$rootScope.showCommonPop = 1;
												$rootScope.commonTitle = " Your Own Website Demo Link";
												$rootScope.commonShowContent = "Your Own Website & Mobile Site demo link has been successfully sent.";
											}
										}else if(response.check == 0){
											$mdDialog.hide();
											$rootScope.showCommonPop = 1;
											$rootScope.commonTitle = " Your Own Website Demo Link";
											$rootScope.commonShowContent = "Demo website is not ready. Theme Link has been sent to client.";
										}
									});
								}else if(response.error.code == 3){
									$mdDialog.show({
										controller: choosedemocategorycontroller,
										templateUrl: 'partials/choosedemocategory.html',
										parent: angular.element(document.body),
										targetEvent: ev,
									});
								}else if(response.error.code == 1){
										$mdDialog.hide();
										$rootScope.showCommonPop = 1;
										$rootScope.commonTitle = " Your Own Website Demo Link";
										$rootScope.commonShowContent = "Client already has own website.";
								}else {
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = 'alert!!';
									$rootScope.commonShowContent = response.error.msg;
									return false;
								}
							});
						}
					}
					$scope.closepopup = function() {
						$mdDialog.hide();
					}
			}
			
		function choosedemocategorycontroller($scope) {
			$scope.budget_option_sel = '';
			APIServices.fetchdemocategories($stateParams.parid,$stateParams.ver).success(function(response2){
				if(response2.error.code == 0) {
					$scope.demo_categories = response2.data.catlist;
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'alert!!';
					$rootScope.commonShowContent = response.error.msg;
				}
			});
			
			$scope.sendlink = function(ev) {
				if($scope.budget_option_sel == '') {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please Select One Category";
					return false;
				}else {
					APIServices.sendomnidemo($stateParams.parid,$stateParams.ver,$rootScope.mobile_str,$rootScope.email_str,$scope.budget_option_sel).success(function(response) {
						if(response.error.code == 0) {
							APIServices.insertDemoLinkDetails($stateParams.parid,$stateParams.ver).success(function(response2){
								$mdDialog.hide();
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = 'Success!!';
								$rootScope.commonShowContent ='Omni Demo links sent successfully.';
							});
								
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'Alert!!';
							$rootScope.commonShowContent =response.error.msg;
							return false;
						}
					});
				}
			}
			
		}
		
		
		APIServices.customjdrrhandling($stateParams.parid,$stateParams.ver).success(function(response) {
		});
		
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
		
		$scope.viewDemoJdOmniRedirect = function() {
			//~ $window.open('http://www.jdomni.com/?jdDemoRedirect=1#/jdThemeSearch', '_blank');
			$window.open('http://www.jdomni.com/#/ecommerce-themes', '_blank');
        }
		
	});

	tmeModuleApp.controller('selbudgettypeController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$cookies) {
		$rootScope.extraHandler	=	$stateParams.page;
		$scope.omni_3k = false;
		$scope.sel_city = DATACITY.toLowerCase();
		
		APIServices.getversion(returnState.paridInfo,DATACITY).success(function(response) {
			$rootScope.budgetVersion	=	response.version;
			APIServices.tempactualbudgetupdate(returnState.paridInfo,$rootScope.budgetVersion).success(function(response) {
				if(response.error.code == 1) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please contact software team";
					return false;
				}
			});
			
			APIServices.call_disc_api(returnState.paridInfo,response.version,0).success(function(response) {
			
			});
			
		});
		
		$cookieStore.remove("selected_option");
		
		$scope.reset_campaign = function(ev) {
			APIServices.deleteallcampaigns(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response.error.code == 0) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Success!!';
					$rootScope.commonShowContent = 'Success';
					return false;
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = response.error.code;
					return false;
				}
			}); 
		}
		
		$scope.budget_option_sel = '';
		function combopopup($scope,$mdDialog,$mdToast,APIServices){
				setTimeout(
					function() {
						$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
					},100
				);
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			$scope.showbannerpopup= function(ev) {
				$mdDialog.hide();
			}

		}
		$scope.open_combo		= function(ev) {

			$mdDialog.show({
				controller: combopopup,
				templateUrl: 'partials/combopopup.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}	
		$rootScope.$on("callbanner", function(){
           $scope.bannerspecificationpopnew();
        });

		$scope.selected_option = function(ev) {
			if($scope.budget_option_sel == undefined || $scope.budget_option_sel =='')
			{
				$mdToast.show(
				$mdToast.simple()
					.content('Please select any one option')
					.position('top right')
					.hideDelay(3000)
				);	
			}else {
				APIServices.payment_type(returnState.paridInfo,$scope.budget_option_sel,returnState.ver).success(function(response) {  
					if(response.error_code == 0) {
						$cookieStore.put('selected_option', $scope.budget_option_sel);
						if($scope.budget_option_sel == 'fixed') {
							$rootScope.targetUrl	=	"appHome.areaSel";
							$rootScope.submitFinalCats();
						}else if($scope.budget_option_sel == 'package') {
							$rootScope.targetUrl	=	"appHome.customPackage";
							$rootScope.submitFinalCats();
						}else if($scope.budget_option_sel == 'omni1' || $scope.budget_option_sel == 'omni2'){
							$rootScope.targetUrl	=	"appHome.omnidomainreg";
							$rootScope.submitFinalCats();
						}else if($scope.budget_option_sel == 'omni_combo' ){
							$state.go('appHome.selectomnicombo',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
						}else if($scope.budget_option_sel == 'jdrr' ){
							APIServices.addjdrr(returnState.paridInfo,returnState.ver,0).success(function(response) {
								if(response.error.code == 0) {
									$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
								}else {
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Genio";
									$rootScope.commonShowContent = response.error.msg;
								}
							});
						}else if($scope.budget_option_sel == 'banner' ){
							$state.go('appHome.bannerspecification',{parid:returnState.paridInfo,type:$scope.budget_option_sel,ver:returnState.ver,page:$rootScope.extraHandler});
						}else if($scope.budget_option_sel == 'jdrrplus'){
							$state.go('appHome.bannerspecification',{parid:returnState.paridInfo,type:$scope.budget_option_sel,ver:returnState.ver,page:$rootScope.extraHandler});
						}
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = 'Please try again';
					}
					
				});
			}
		}
		
		$scope.gotobannerdemo = function(){
			$state.go('appHome.dialogjdrrpopup',{parid:returnState.paridInfo,pageshow:'Jdrr Certificate',image:7,ver:returnState.ver,page:$rootScope.extraHandler});
		}
		
		$scope.show_omni_3k = function() {
			if(DATACITY.toLowerCase() == 'mumbai') {
				$scope.omni_3k = !$scope.omni_3k;
			}else {
				$scope.omni_3k = false;
			}
		}
		
		
	});

	tmeModuleApp.controller('additionalcampaignsController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$cookies) {
	
		$rootScope.extraHandler	=	$stateParams.page;
		
		$scope.cookie_selected=$cookieStore.get('selected_option');
		
		$scope.show_banner = true;
		$scope.show_jdrr   = true;
		
		if($scope.cookie_selected == 'banner') {
			$scope.show_banner = false;
		}else if($scope.cookie_selected == 'jdrr') {
			$scope.show_jdrr = false;
		} 
		
		$scope.budget_option_sel = '';
		
		if($scope.cookie_selected != 'banner' &&  $scope.cookie_selected != 'jdrr') {
			APIServices.deletejdrrplus(returnState.paridInfo,returnState.ver).success(function(response) {
			});
		}
		
		function bannerspecificationpopnew($scope,$mdDialog,$mdToast,APIServices,$rootScope){
				setTimeout(
					function() {
						$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
					},100
				);
			
			$scope.banner_specification = {};
			$scope.show_demo_img = 1;
			$scope.popupcond = $rootScope.samplepop;
			
			APIServices.get_banner_spec(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response.error.code == 0){
					$scope.banner_specification[0]  = response.error.msg;
				} else {
					$scope.banner_specification[0] = '';
				}
			});

			$scope.addbanner = function() {
				if($scope.banner_specification[0] == '' || $scope.banner_specification[0] == null ) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please Enter Client Instruction";
					return false;

				}else {
					
					APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
						$rootScope.budgetVersion	=	response.version;
					});
					$mdDialog.hide();
					if($rootScope.selected_additional_value=='banner'){
						APIServices.addbanner(returnState.paridInfo,returnState.ver,$scope.banner_specification[0],0).success(function(response) {
							if(response.error.code == 0){
								$mdDialog.hide();
								$rootScope.gotonextpage();
							} else if(response.error.code == 2){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = response.error.msg;
								$mdDialog.hide();
								$rootScope.gotonextpage();
							}else {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = response.error.msg;
								return false;
							}
						});
					}
					else if($rootScope.selected_additional_value=='jdrrplus'){
						APIServices.addjdrrplus(returnState.paridInfo,returnState.ver,$scope.banner_specification[0],0).success(function(response) {
							if(response.error.code == 0){
								$mdDialog.hide();
								$rootScope.gotonextpage();
							} else if(response.error.code == 2){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = response.error.msg;
								$mdDialog.hide();
								$rootScope.gotonextpage();
							}else {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = response.error.msg;
								return false;
							}
						});
					}
				}
			}
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
		}
		
		$scope.selected_option = function(ev) {
			$rootScope.selected_additional_value='';
			if($scope.budget_option_sel == undefined || $scope.budget_option_sel =='')
			{	
				$mdToast.show(
				$mdToast.simple()
					.content('Please select any one option')
					.position('top right')
					.hideDelay(3000)
				);	
			}else {
				$rootScope.selected_additional_value=$scope.budget_option_sel;
				if($scope.budget_option_sel == 'jdrr') {
					APIServices.addjdrr(returnState.paridInfo,returnState.ver,0).success(function(response) {
						if(response.error.code == 0) {
							$rootScope.gotonextpage();
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = "Please Try Again!!";
						}
					});	
				}else if($scope.budget_option_sel == 'banner' || $scope.budget_option_sel == 'jdrrplus') {
					$state.go('appHome.bannerspecification',{parid:returnState.paridInfo,type:$scope.budget_option_sel,ver:returnState.ver,page:$rootScope.extraHandler});
				}else if($scope.budget_option_sel == 'none') {
					$rootScope.gotonextpage();
				}
						
			}
					
		}
		
		
		$rootScope.gotonextpage= function(){
				$state.go('appHome.budgetsummary',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
		}
		
		$scope.gotobudgettype = function(){
			if($scope.cookie_selected == "omni1" || $scope.cookie_selected == "omni2"  ) {
				$state.go('appHome.addfixedcampaign',{parid:returnState.paridInfo,ver:returnState.ver,page:$stateParams.page});
			}else if($scope.cookie_selected == "fixed" ) {
				$state.go('appHome.addomni',{parid:returnState.paridInfo,ver:returnState.ver,page:$stateParams.page});
			}else if($scope.cookie_selected == "package" ) {
				$state.go('appHome.customPackage',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
			}else if($scope.cookie_selected == "omnisupreme") {
				$state.go('appHome.omniappdemo',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
			}else if($scope.cookie_selected == "banner" || $scope.cookie_selected == "jdrr" ) {
				$state.go('appHome.selbudgettype',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
			}
		}
		
		function combopopup($scope,$mdDialog,$mdToast,APIServices){
				setTimeout(
					function() {
						$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
					},100
				);
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			$scope.showbannerpopup= function(ev) {
				$mdDialog.hide();
			}

		}
		
		$scope.open_combo		= function(ev) {

			$mdDialog.show({
				controller: combopopup,
				templateUrl: 'partials/combopopup.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}
		
		
	});
	tmeModuleApp.controller('budgetsummaryController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		
		$scope.temp_bud_arr ={};
		$scope.campaign_bud ={};
		$rootScope.extraHandler	=	returnState.page;
		
		$scope.ucodearr = ['013084','005375','007727'];
		$scope.showdis = true;
		
		
		if($scope.ucodearr.indexOf(USERID) >= 0) {
			$scope.showdis = false;
		}else {
			$scope.showdis = true;
		}
		
        
		
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}

		$scope.selected_camapign = $cookieStore.get('selected_option');
		$scope.combo=0;
		 $scope.camp_selected = $cookieStore.get('campaign_str');
		  $scope.camp_selected = $scope.camp_selected.split(','); 
		$scope.selected_campaign_name = $cookieStore.get('campaign_names');
		$scope.banner_rotation = $cookieStore.get('banner_rotation');
        $scope.camp_selected = $cookieStore.get('campaign_str');
        $scope.camp_selected = $scope.camp_selected.split(','); 
         $scope.campaign_name_arr = $scope.selected_campaign_name.split(',');  
		if($scope.campaign_name_arr.indexOf("pck_dependent") > -1 || $scope.campaign_name_arr.indexOf("pck_flexi_ecs") > -1 ){
			$scope.pck_dependent = 1;
		}else {
			$scope.pck_dependent = 0;
		}
		
		if($scope.selected_camapign == "combo1"){
			$scope.combo = 1;
		}else if ($scope.selected_camapign == "combo2") {
			$scope.omni_type = 5; 
		}else if ($scope.selected_camapign == "omni1") {
			$scope.omni_type = 1;
		}else if ($scope.selected_camapign == "omni2") {
			$scope.omni_type = 2;
		}else if ($scope.selected_camapign == "omniultima") {
			$scope.omni_type = 3; 
		}else if ($scope.selected_camapign == "omnisupreme") {
			$scope.omni_type = 4; 
		}else if ($scope.selected_camapign == "omni7") {
			$scope.omni_type = 7; 
		}
		
		$scope.selected_payment_type = $cookieStore.get('payment_type');
		if($scope.selected_payment_type == 'ecs') {
			$scope.ecs_flag = 1;
		}else if($scope.selected_payment_type == 'upfront') {
			$scope.ecs_flag = 0;
		} 	 
			
		$scope.call_dis_api = 0;
		
		$scope.ecs_tot_price =  0;
		$scope.ecs_tot_offer_price = 0;
		$cookieStore.remove("req_omni_domain");
		
		$('.imgl-offr').click(function(){
			if($('.tt-txt, .text-del').hasClass('dn')) {
				$('.tt-txt, .text-del, .click-star').removeClass('dn');
				$('.actual_price').css('display','none');
				$('.dis_price').css('display','inline');
			}else {
				$('.hide_div').addClass('dn');
				$('.actual_price').css('display','inline');
				$('.dis_price').css('display','none');
			}
		});
		
		$('.tab-main').click(function(){
			$scope.show_omni_input = false;
			if(!$('#tab-1').hasClass('mask_upfront') && !$('#tab-2').hasClass('mask_upfront') ){
				$(this).prev().removeClass('tab-main-active');
				$(this).next().removeClass('tab-main-active');
				$(this).addClass('tab-main-active');
				$('#tab-1').css({'border-left':'0'})
				if($('#tab-1').hasClass('tab-main-active')){
					$('#tab-1').css({'border-left':'1px solid #ccc'})
				}
			}
		})
	
		$('#tab-2').click(function(){
			if(!$('#tab-2').hasClass('mask_upfront')){
				$('#tab-1-content').hide();
				$('#tab-2-content').fadeIn();
				$('.actual_price').css('display','inline');
				$('.dis_price').css('display','none');
				$('.hide_div').addClass('dn');
			}
		});
		
		
		$('#tab-1').click(function(){
			if(!$('#tab-1').hasClass('mask_upfront')){
				$('#tab-2-content').hide();
				$('#tab-1-content').fadeIn();
				$('.actual_price').css('display','inline');
				$('.dis_price').css('display','none');
				$('.hide_div').addClass('dn');
			}
		});
		
		
		$scope.budget_cb = {};		
		$scope.budget_cb[1]= true;
		$scope.budget_cb[2]= true;
		$scope.budget_cb[22]= true;
		$scope.budget_cb[225]= true;
		$scope.budget_cb[72]= true;
		$scope.budget_cb[73]= true;
		$scope.budget_cb[74]= true;
		$scope.budget_cb[75]= true;
		$scope.budget_cb[5]= true;
		$scope.budget_cb[273]= true;
		$scope.budget_cb[573]= true;
		$scope.budget_cb[82]= true;
		$scope.budget_cb[83]= true;
        $scope.budget_cb[84]= true;
		$scope.budget_cb[10]= true;
		
		
		$scope.mon_cb = {};		
		$scope.mon_cb[1]= true;
		$scope.mon_cb[22]= true;
		$scope.mon_cb[225]= true;
		$scope.mon_cb[72]= true;
		$scope.mon_cb[5]= true;
		
		
		$scope.adv_cb = {};		
		$scope.adv_cb[1]= true;
		$scope.adv_cb[116]= true;
		$scope.adv_cb[114]= true;
		$scope.adv_cb[115]= true;
		$scope.adv_cb[1]= true;
       		$scope.adv_cb[111]= true;
		$scope.adv_cb[2]= true;
		$scope.adv_cb[22]= true;
		$scope.adv_cb[225]= true;
		$scope.adv_cb[741]= true;
		$scope.adv_cb[72]= true;
		$scope.adv_cb[73]= true;
		$scope.adv_cb[74]= true;
		$scope.adv_cb[75]= true;
		$scope.adv_cb[5]= true;
		$scope.adv_cb[51]= true;
		$scope.adv_cb[52]= true;
		$scope.adv_cb[53]= true;
		$scope.adv_cb[54]= true;
		$scope.adv_cb[273]= true;
		$scope.adv_cb[573]= true;
		$scope.adv_cb[82]= true;
		$scope.adv_cb[83]= true;
        $scope.adv_cb[84]= true;
		$scope.adv_cb[10]= true;
        $scope.adv_cb[473]= true;

		
		$scope.custom_72 = '';
		$scope.custom_73 = '';
		$scope.custom_5 = '';
		$scope.omni_monthly = '';
		APIServices.get_discount_info(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0) {
				if(response.error.data['73'] != undefined && response.error.data['73'] != '' ) {
					$scope.custom_73 = response.error.data['73'];
				}else {
					$scope.custom_73 = '';
				}
				
				if(response.error.data['72'] != undefined && response.error.data['72'] != '') {
					$scope.custom_72 = response.error.data['72'];
				}else {
					$scope.custom_72 = '';
				}
				
				if(response.error.data['5'] != undefined && response.error.data['5'] != '') {
					$scope.custom_5 = response.error.data['5'];
				}else {
					$scope.custom_5 = '';
				}
				
				if($scope.ecs_flag == 1) {
					$scope.omni_monthly = $scope.custom_73;
				}
				 
			}	
		});	
			
		
		$scope.delete_tot = function(price,offer_price,dis_price,id,index,ecs_flag) {
			
			   if($scope.live_data == 0 && index == 10)
			   {
				   $scope.budget_cb[index]= true;
				   $scope.adv_cb[index] = true;
				   return false
			   }
				
				if($scope.national_change_state == 1 && index == 10)
               {
                   $scope.budget_cb[index]= true;
                   $scope.adv_cb[index] = true;
                   return false;
               }
				
				
				
			   if(id == true && ecs_flag == 0) {
					
					$scope.grand_price_tot =  $scope.grand_price_tot + parseFloat(price);
					$scope.grand_price_offer =  $scope.grand_price_offer + parseFloat(offer_price);
					$scope.grand_price_dis = $scope.grand_price_dis + parseFloat(dis_price);
					
					$scope.budget_cb[index]= true;
					$scope.adv_cb[index] = true;
						
					if(index == 1)
					{
						$scope.budget_cb[2]= true;
						$scope.adv_cb[2] = true;
					}else if(index == 72 || index ==  273 || index ==  573 )
					{
						$scope.budget_cb[73]= true;
						$scope.adv_cb[73] = true;
						
						$scope.budget_cb[72]= true;
						$scope.adv_cb[72] = true;
						
						if($scope.campaign_bud[74] != undefined) {
							$scope.grand_price_tot =  $scope.grand_price_tot + parseFloat($scope.website_price);
							$scope.grand_price_offer =  $scope.grand_price_offer + parseFloat($scope.website_offer_price);
							$scope.grand_price_dis = $scope.grand_price_dis + parseFloat($scope.website_dis_price);
						}
						
						if($scope.campaign_bud[75] != undefined) {
							$scope.grand_price_tot =  $scope.grand_price_tot + parseFloat($scope.campaign_bud[75].price);
							$scope.grand_price_offer =  $scope.grand_price_offer + parseFloat($scope.campaign_bud[75].offer_price);
							$scope.grand_price_dis = $scope.grand_price_dis + parseFloat($scope.campaign_bud[75].discount);
							
							$scope.budget_cb[75]= true;
							$scope.adv_cb[75] = true;
						
						}
						
					}
						
				}else if(id == false && ecs_flag == 0){
					
					$scope.grand_price_tot =  $scope.grand_price_tot - parseFloat(price);
					$scope.grand_price_offer =  $scope.grand_price_offer - parseFloat(offer_price);
					$scope.grand_price_dis = $scope.grand_price_dis - parseFloat(dis_price);
					
					$scope.budget_cb[index]= false;
					$scope.adv_cb[index] = false;
					
					if(index == 1)
					{
						$scope.budget_cb[2]= false;
						$scope.adv_cb[2] = false;
					}else if(index == 72 || index ==  273 || index ==  573)
					{
						$scope.budget_cb[73]= false;
						$scope.adv_cb[73] = false;
						
						$scope.budget_cb[72]= false;
						$scope.adv_cb[72] = false;
						
						if($scope.campaign_bud[74] != undefined) {
							$scope.grand_price_tot =  $scope.grand_price_tot - parseFloat($scope.website_price);
							$scope.grand_price_offer =  $scope.grand_price_offer - parseFloat($scope.website_offer_price);
							$scope.grand_price_dis = $scope.grand_price_dis - parseFloat($scope.website_dis_price);
							
							$scope.budget_cb[74]= false;
							$scope.adv_cb[74] = false;
						
						}
						
						if($scope.campaign_bud[75] != undefined && $scope.grand_price_tot != 0 ) {
							$scope.grand_price_tot =  $scope.grand_price_tot - parseFloat($scope.campaign_bud[75].price);
							$scope.grand_price_offer =  $scope.grand_price_offer - parseFloat($scope.campaign_bud[75].offer_price);
							$scope.grand_price_dis = $scope.grand_price_dis - parseFloat($scope.campaign_bud[75].discount);
							
							$scope.budget_cb[75]= false;
							$scope.adv_cb[75] = false;
						
						}
						
					}
					
						
				}else if(id == true && ecs_flag == 1) {
					
					if(index == 72 || index == 273 || index == 573) {
						if(index == 72) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price) + parseFloat($scope.ecs_bud_monthly[73].price * 12);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price) + parseFloat($scope.ecs_bud_monthly[73].offer_price * 12);
						}else if(index == 273){
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price) + parseFloat($scope.ecs_bud_monthly[273].price * 12);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price) + parseFloat($scope.ecs_bud_monthly[273].offer_price * 12);
						}else {
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price*4);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price*4);
						}
						$scope.budget_cb[73]= true;
						$scope.adv_cb[73]= true;
						
						$scope.budget_cb[72]= true;
						$scope.adv_cb[72]= true;
						
						if($scope.ecs_bud_advance['74'] != undefined) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat($scope.ecs_bud_advance['74'].price);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat($scope.ecs_bud_advance['74'].offer_price);
							$scope.budget_cb[74]= true;
							$scope.adv_cb[74]= true;
						}
						
						if($scope.ecs_bud_advance['75'] != undefined) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat($scope.ecs_bud_advance['75'].price);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat($scope.ecs_bud_advance['75'].offer_price);
							$scope.budget_cb[75]= true;
							$scope.adv_cb[75]= true;
						}
						
					}else if(index == 75  || index == 83 || index == 82){
						$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price);
						$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price);
					 }else if(index == 111){
						$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price);
						$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price);
					}else if(index != 72) {
						if($scope.selected_campaign_name == "package_expired" && (index == 1 || index == 5 )) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price*2);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price*2);
						}else{
							$scope.ecs_tot_price =  $scope.ecs_tot_price + parseFloat(price*4);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price + parseFloat(offer_price*4);
						}
					}
					
					if(index == 1)
					{
						$scope.budget_cb[2]= true;
						$scope.adv_cb[2] = true;
					}
					
					$scope.budget_cb[index]= true;
					$scope.adv_cb[index] = true;
					
				}else if(id == false && ecs_flag == 1){
					if(index == 72 || index == 273 || index == 573) {
						if(index == 72) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price) - parseFloat($scope.ecs_bud_monthly[73].price * 12);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price) - parseFloat($scope.ecs_bud_monthly[73].offer_price * 12);
						}else if(index == 273){
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price) - parseFloat($scope.ecs_bud_monthly[273].price * 12);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price) - parseFloat($scope.ecs_bud_monthly[273].offer_price * 12);
						}else {
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price * 4);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price * 4);
						}
						$scope.budget_cb[73]= false;
						$scope.adv_cb[73]= false;
						
						$scope.budget_cb[72]= false;
						$scope.adv_cb[72]= false;
						if($scope.ecs_bud_advance['74'] != undefined) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat($scope.ecs_bud_advance['74'].price);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat($scope.ecs_bud_advance['74'].offer_price);
							$scope.budget_cb[74]= false;
							$scope.adv_cb[74]= false;
						}
						
						if($scope.ecs_bud_advance['75'] != undefined && $scope.ecs_tot_offer_price!= 0) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat($scope.ecs_bud_advance['75'].price);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat($scope.ecs_bud_advance['75'].offer_price);
							$scope.budget_cb[75]= false;
							$scope.adv_cb[75]= false;
						}
						
					}else if(index == 75  || index == 83 || index == 82 ){
						$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price);
						$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price);
                    			}else if(index == 111){
						$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price);
						$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price);
					}else if(index != 72) {
						if($scope.selected_campaign_name == "package_expired" && (index == 1 || index == 5 )) {
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price*2);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price*2);
						}else{
							$scope.ecs_tot_price =  $scope.ecs_tot_price - parseFloat(price*4);
							$scope.ecs_tot_offer_price =  $scope.ecs_tot_offer_price - parseFloat(offer_price*4);
						}
					}
					
					$scope.budget_cb[index]= false;
					$scope.adv_cb[index] = false;
					
					if(index == 1)
					{ 
						$scope.budget_cb[2]= false;
						$scope.adv_cb[2] = false;
					}
					
				}
		}
		
		
				
				
		
		$scope.upfront_disc = {};
		$scope.upfront_disc[1] = '';
		$scope.show_input = false;
		
		$scope.show_dis_input = function() {
			
			if($scope.show_input == true) {
				$scope.show_input = false;
			}else {
				$scope.show_input = true;
			}
		}
		
		$scope.show_ecs_adv_campaign =0;
		$scope.show_ecs_adv_both =0;
		
		$scope.show_ecs_mon_campaign =0;
		$scope.show_ecs_mon_both =0;
				
		
		$scope.switch_option = function(type) {
			$scope.show_camp_error = false;
			$scope.show_ecs_error = false;
			$scope.show_website = false;
			$scope.sel_option = type;
			
			var selected_opt=$cookieStore.get('campaign_str');
			
			$scope.omniArr	=	[];
			$scope.omniStr	=	'';
			$scope.omniArr	=	selected_opt.split(',');
			angular.forEach($scope.omniArr,function(value,key) {
				if((value.indexOf('72') > -1 || value.indexOf('73') > -1 || value.indexOf('74') > -1) && (value != '742' && value != '743')){
					
					$scope.omniStr	=	value;
				}
			});
			var omni_type = $scope.omniStr;
			
			if(type == "campaign")
			{
				APIServices.campaignpricelist(returnState.paridInfo,returnState.ver,$scope.combo,$scope.omni_type,$scope.camp_selected,$scope.banner_rotation).success(function(response) { 
					if(response.error.code == 0){
						$scope.grand_price_tot = 0;
						$scope.grand_price_offer = 0;
						$scope.grand_price_dis = 0;
						
						$scope.campaign_bud = response.error.msg;
						$scope.show_package_list = 0;
						$scope.show_best_list = 0;
						$scope.show_upfront_inaugral = false;
						
						angular.forEach($scope.campaign_bud, function(value,key) {
							if(value['price'] != value['offer_price']) {
								$scope.show_upfront_inaugral = true;
							}
						});
						
						//omni calculation  
						
						if($scope.campaign_bud["72"] != undefined && $scope.campaign_bud["173"] == undefined ) {
							if($scope.campaign_bud["74"] == undefined) {
								$scope.omni_price = parseFloat($scope.campaign_bud["72"].price) + parseFloat($scope.campaign_bud["73"].price);
								$scope.omni_offer_price = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.omni_offer_price_temp = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.upfront_disc[2] = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.omni_dis_price = parseFloat($scope.campaign_bud["72"].discount) + parseFloat($scope.campaign_bud["73"].discount);
								$scope.omni_dis_percentage = Math.ceil((($scope.omni_price - $scope.omni_offer_price)/$scope.omni_price)*100);
								
								$scope.omni_total_price = parseFloat($scope.campaign_bud["72"].price) + parseFloat($scope.campaign_bud["73"].price);
								$scope.omni_total_offer_price = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.omni_total_dis_price = parseFloat($scope.campaign_bud["72"].discount) + parseFloat($scope.campaign_bud["73"].discount);
								$scope.omni_total_dis_percentage = Math.ceil((($scope.omni_total_price - $scope.omni_total_offer_price)/$scope.omni_total_price)*100);
								
							}else {
								$scope.show_website = true;
								$scope.omni_price = parseFloat($scope.campaign_bud["72"].price) + parseFloat($scope.campaign_bud["73"].price);
								$scope.omni_offer_price = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.omni_offer_price_temp = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.upfront_disc[2] = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
								$scope.omni_dis_price = parseFloat($scope.campaign_bud["72"].discount) + parseFloat($scope.campaign_bud["73"].discount);
								$scope.omni_dis_percentage = Math.ceil((($scope.omni_price - $scope.omni_offer_price)/$scope.omni_price)*100);
								
								$scope.website_price = parseFloat($scope.campaign_bud["74"].price);
								$scope.website_offer_price = parseFloat($scope.campaign_bud["74"].offer_price);
								$scope.website_dis_price = parseFloat($scope.campaign_bud["74"].discount);
								$scope.website_dis_percentage = parseFloat($scope.campaign_bud["74"].discount_percent);
								
								$scope.omni_total_price = parseFloat($scope.campaign_bud["72"].price) + parseFloat($scope.campaign_bud["73"].price) + parseFloat($scope.campaign_bud["74"].price);
								$scope.omni_total_offer_price = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price);
								$scope.omni_total_dis_price = parseFloat($scope.campaign_bud["72"].discount) + parseFloat($scope.campaign_bud["73"].discount) + parseFloat($scope.campaign_bud["74"].discount);
								$scope.omni_total_dis_percentage = Math.ceil((($scope.omni_total_price - $scope.omni_total_offer_price)/$scope.omni_total_price)*100);
							}
						}
						
						if($scope.campaign_bud["173"] != undefined) {
							
							$scope.omni_price = parseFloat($scope.campaign_bud["72"].price);
							$scope.omni_offer_price = parseFloat($scope.campaign_bud["72"].offer_price);
							$scope.omni_offer_price_temp = parseFloat($scope.campaign_bud["72"].offer_price);
							//~ $scope.upfront_disc[2] = parseFloat($scope.campaign_bud["72"].offer_price) + parseFloat($scope.campaign_bud["73"].offer_price);
							$scope.omni_dis_price = parseFloat($scope.campaign_bud["72"].discount);
							$scope.omni_dis_percentage =  parseFloat($scope.campaign_bud["72"].discount_percent);
							
							$scope.omni_total_price = $scope.omni_price;
							$scope.omni_total_offer_price = $scope.omni_offer_price;
							$scope.omni_total_dis_price = $scope.omni_dis_price;
							$scope.omni_total_dis_percentage =$scope.omni_dis_percentage;
								
								
							if($scope.campaign_bud["74"] == undefined) {
								
								$scope.combo1_price = parseFloat($scope.campaign_bud["173"].price) ;
								$scope.combo1_offer_price = parseFloat($scope.campaign_bud["173"].offer_price)  ;
								$scope.combo1_dis_price = parseFloat($scope.campaign_bud["173"].discount) ;
								$scope.combo1_dis_percentage = parseFloat($scope.campaign_bud["173"].discount_percent);
								
								
							}else {
								
								$scope.show_website = true;
								$scope.website_price = parseFloat($scope.campaign_bud["74"].price);
								$scope.website_offer_price = parseFloat($scope.campaign_bud["74"].offer_price);
								$scope.website_dis_price = parseFloat($scope.campaign_bud["74"].discount);
								$scope.website_dis_percentage = parseFloat($scope.campaign_bud["74"].discount_percent);
								
								$scope.combo1_price = parseFloat($scope.campaign_bud["173"].price) + parseFloat($scope.campaign_bud["74"].price) ;
								$scope.combo1_offer_price = parseFloat($scope.campaign_bud["173"].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price) ;
								$scope.combo1_dis_price = parseFloat($scope.campaign_bud["173"].discount) + parseFloat($scope.campaign_bud["74"].discount) ;
								$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
							
							}
							
							
						}
						
						if($scope.campaign_bud["373"] != undefined || $scope.campaign_bud["473"] != undefined  || $scope.campaign_bud["273"] != undefined || $scope.campaign_bud["573"] != undefined || $scope.campaign_bud["741"] != undefined) {
							if($scope.campaign_bud["74"] != undefined) {
								$scope.show_website = true;
								$scope.website_price = parseFloat($scope.campaign_bud["74"].price);
								$scope.website_offer_price = parseFloat($scope.campaign_bud["74"].offer_price);
								$scope.website_dis_price = parseFloat($scope.campaign_bud["74"].discount);
								$scope.website_dis_percentage = parseFloat($scope.campaign_bud["74"].discount_percent);
								
								if($scope.campaign_bud["373"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["373"].price) + parseFloat($scope.campaign_bud["74"].price) ;
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["373"].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price) ;
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["373"].discount) + parseFloat($scope.campaign_bud["74"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["473"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["473"].price) + parseFloat($scope.campaign_bud["74"].price) ;
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["473"].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price) ;
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["473"].discount) + parseFloat($scope.campaign_bud["74"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["273"] != undefined || $scope.campaign_bud["741"] != undefined) {
									if($scope.campaign_bud["741"] != undefined){
										$scope.key	=	'741';
									}else{
										$scope.key	=	'273';
									}
									$scope.combo1_price = parseFloat($scope.campaign_bud[$scope.key].price) + parseFloat($scope.campaign_bud["74"].price) ;
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud[$scope.key].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price) ;
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud[$scope.key].discount) + parseFloat($scope.campaign_bud["74"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["573"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["573"].price) + parseFloat($scope.campaign_bud["74"].price) ;
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["573"].offer_price) + parseFloat($scope.campaign_bud["74"].offer_price) ;
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["573"].discount) + parseFloat($scope.campaign_bud["74"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}
							}else {
									if($scope.campaign_bud["373"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["373"].price) ;
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["373"].offer_price) ;
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["373"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["473"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["473"].price);
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["473"].offer_price);
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["473"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["273"] != undefined || $scope.campaign_bud["741"] != undefined) {
									if($scope.campaign_bud["741"] != undefined){
										$scope.key	=	'741';
									}else{
										$scope.key	=	'273';
									}
									$scope.combo1_price = parseFloat($scope.campaign_bud[$scope.key].price);
                                   	$scope.upfront_disc[$scope.key] = parseFloat($scope.campaign_bud[$scope.key].price);
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud[$scope.key].offer_price);
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud[$scope.key].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}else if($scope.campaign_bud["573"] != undefined) {
									$scope.combo1_price = parseFloat($scope.campaign_bud["573"].price);
									$scope.combo1_offer_price = parseFloat($scope.campaign_bud["573"].offer_price);
									$scope.combo1_dis_price = parseFloat($scope.campaign_bud["573"].discount) ;
									$scope.combo1_dis_percentage = Math.ceil((($scope.combo1_price - $scope.combo1_offer_price)/$scope.combo1_price)*100);
								}
							}
						}
						
						if($scope.campaign_bud["10"] != undefined && $scope.campaign_bud["10"]['nationallive'] == 1)
                        {
                            $scope.live_data = 0;
                        }
						
						if($scope.campaign_bud["10"] != undefined && $scope.campaign_bud["10"]['national_change_state'] == 1)
                        {
                            $scope.national_change_state = 1;
                        }	
						//campaign total  calculation	
						if($scope.campaign_bud["1"] == undefined && $scope.campaign_bud["2"] != undefined) {
							$scope.campaign_price = parseFloat($scope.campaign_bud["2"].price);
							$scope.campaign_offer_price = parseFloat($scope.campaign_bud["2"].offer_price);
							$scope.campaign_dis_price = parseFloat($scope.campaign_bud["2"].discount);
							$scope.campaign_dis_percentage = parseFloat($scope.campaign_bud["2"].discount_percent);
						}else if($scope.campaign_bud["2"] == undefined && $scope.campaign_bud["1"] != undefined) {
							$scope.campaign_price = parseFloat($scope.campaign_bud["1"].price);
							$scope.campaign_offer_price = parseFloat($scope.campaign_bud["1"].offer_price);
							$scope.campaign_dis_price = parseFloat($scope.campaign_bud["1"].discount);
							$scope.campaign_dis_percentage = parseFloat($scope.campaign_bud["1"].discount_percent);
						}else if($scope.campaign_bud["2"] != undefined && $scope.campaign_bud["1"] != undefined) {
							$scope.campaign_price =   parseFloat($scope.campaign_bud["2"].price) + parseFloat($scope.campaign_bud["1"].price) ;
							$scope.campaign_offer_price = parseFloat($scope.campaign_bud["2"].offer_price) + parseFloat($scope.campaign_bud["1"].offer_price) ;
							$scope.campaign_dis_price = parseFloat($scope.campaign_bud["2"].discount) + parseFloat($scope.campaign_bud["1"].discount) ;
							$scope.campaign_dis_percentage = Math.round((($scope.campaign_price - $scope.campaign_offer_price)/$scope.campaign_price)*100);
						}
							
						
						//logic to display package and best budget price
						if($scope.campaign_bud["1"] != undefined && $scope.campaign_bud["2"] != undefined ) {
							$scope.package_price = parseFloat($scope.campaign_bud["1"].price);
							$scope.package_offer_price = parseFloat($scope.campaign_bud["1"].offer_price);
							$scope.package_disc_price = parseFloat($scope.campaign_bud["1"].discount);
							$scope.package_dis_percentage = parseFloat($scope.campaign_bud["1"].discount_percent);
							
							$scope.best_price = parseFloat($scope.campaign_bud["2"].price);
							$scope.best_offer_price = parseFloat($scope.campaign_bud["2"].offer_price);
							$scope.best_disc_price = parseFloat($scope.campaign_bud["2"].discount);
							$scope.best_dis_percentage = parseFloat($scope.campaign_bud["2"].discount_percent);
							
							$scope.show_package_list = 1;
							$scope.show_best_list = 1;
							
						}else if($scope.campaign_bud["1"] == undefined && $scope.campaign_bud["2"] != undefined) {
							
							$scope.best_price = parseFloat($scope.campaign_bud["2"].price);
							$scope.best_offer_price = parseFloat($scope.campaign_bud["2"].offer_price);
							$scope.best_disc_price = parseFloat($scope.campaign_bud["2"].discount);
							$scope.best_dis_percentage = parseFloat($scope.campaign_bud["2"].discount_percent);
							
							$scope.show_package_list = 0;
							$scope.show_best_list = 1;
							
						}else if($scope.campaign_bud["2"] == undefined && $scope.campaign_bud["1"] != undefined) {
							$scope.package_price = parseFloat($scope.campaign_bud["1"].price);
							$scope.package_offer_price = parseFloat($scope.campaign_bud["1"].offer_price);
							$scope.package_disc_price = parseFloat($scope.campaign_bud["1"].discount);
							$scope.package_dis_percentage = parseFloat($scope.campaign_bud["1"].discount_percent);
							
							$scope.show_package_list = 1;
							$scope.show_best_list = 0;
						}else {
							
							$scope.show_package_list = 0;
							$scope.show_best_list = 0;
							
						}
						
						
						if($scope.campaign_dis_percentage != 0 && $scope.campaign_dis_percentage != undefined && $scope.call_dis_api == 0 ) { 
							$scope.upfront_disc[1] = $scope.campaign_dis_percentage;
							$scope.call_disc_api();
							$scope.call_dis_api = 1;
						}
						
						$scope.grand_price_dis_per = 0;
						//to calulate the sum 
						angular.forEach($scope.campaign_bud,function(val,key) {
							if($scope.budget_cb[val.campaignid] != false) {
								$scope.grand_price_tot = $scope.grand_price_tot  + parseFloat(val.price);
								$scope.grand_price_offer = $scope.grand_price_offer  + parseFloat(val.offer_price);
								$scope.grand_price_dis = $scope.grand_price_dis  + parseFloat(val.discount);
								$scope.grand_price_dis_per = $scope.grand_price_dis_per  + parseFloat(val.discount_percent);
							}
						});
					
							$scope.grand_price_offer_temp = $scope.grand_price_offer;
							$scope.grand_price_dis_per = Math.ceil((($scope.grand_price_tot - $scope.grand_price_offer)/$scope.grand_price_tot)*100);
					}else if(response.error.code == 1){
						$scope.show_camp_error = true;
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "Please Try Again!!";
					}
				});
			}
			else if(type == "ecs"){
				$scope.mon_price_tot = 0;
				$scope.mon_price_offer = 0;
				$scope.mon_price_dis = 0;
				
				$scope.adv_price_tot = 0;
				$scope.adv_price_offer = 0;
				$scope.adv_price_dis = 0;
				
				
				$scope.adv_ecs_price = 0;
				$scope.adv_ecs_offer = 0;
				$scope.adv_ecs_dic = 0;
				
				$scope.mon_ecs_price = 0;
				$scope.mon_ecs_offer = 0;
				$scope.mon_ecs_dic = 0;
				
				APIServices.call_disc_api(returnState.paridInfo,returnState.ver,0).success(function(response) {
					
					if(response.error.code == 0) {
						APIServices.ecspricelist(returnState.paridInfo,returnState.ver,$scope.combo,$scope.omni_type,$scope.camp_selected,$scope.banner_rotation).success(function(response) {
							if(response.error.code == 0){
								$scope.ecs_bud_advance = response.error.msg.advance;
								$scope.ecs_bud_monthly = response.error.msg.monthly;
								
								$scope.show_ecs_inaugral = false;
								angular.forEach($scope.ecs_bud_advance, function(value,key) {
									if(value['price'] != value['offer_price']) {
										$scope.show_ecs_inaugral = true;
									}
								});
								
								if($scope.show_ecs_inaugral == false) {
									angular.forEach($scope.ecs_bud_monthly, function(value,key) {
										if(value['price'] != value['offer_price']) {
											$scope.show_ecs_inaugral = true;
										}
									});
								}
								
								
								//console.log($scope.ecs_bud_advance);
							if($scope.ecs_bud_advance["10"] != undefined && $scope.ecs_bud_advance["10"]['nationallive'] == 1)
                            {
                                $scope.live_data = 0;
                            }
                            
                            if($scope.ecs_bud_advance["10"] != undefined && $scope.ecs_bud_advance["10"]['national_change_state'] == 1)
                            {
                                $scope.national_change_state = 1;
                            }
								
								// monthly omni calculation
								if($scope.ecs_bud_monthly["72"] != undefined) {
									if($scope.ecs_bud_monthly["173"] == undefined) {
										$scope.mon_omni_price = Math.ceil(parseFloat($scope.ecs_bud_monthly["72"].price) + parseFloat($scope.ecs_bud_monthly["73"].price));
										$scope.mon_omni_offer_price = Math.ceil(parseFloat($scope.ecs_bud_monthly["72"].offer_price) + parseFloat($scope.ecs_bud_monthly["73"].offer_price));
										$scope.mon_omni_offer_price_temp = Math.ceil(parseFloat($scope.ecs_bud_monthly["72"].offer_price) + parseFloat($scope.ecs_bud_monthly["73"].offer_price));
										$scope.upfront_disc[4] = Math.ceil(parseFloat($scope.ecs_bud_monthly["72"].offer_price) + parseFloat($scope.ecs_bud_monthly["73"].offer_price));
										$scope.mon_omni_dis_price = Math.ceil(parseFloat($scope.ecs_bud_monthly["72"].discount) + parseFloat($scope.ecs_bud_monthly["73"].discount));
										$scope.mon_omni_dis_percentage = parseFloat($scope.ecs_bud_monthly["73"].discount_percent);
									}
								}
								
								
								//monthly sum calculation
								$scope.mon_price_dis_per = 0;
								angular.forEach($scope.ecs_bud_monthly,function(val,key) {
									if($scope.adv_cb[val.campaignid] != false && val.offer_price !=0) {
										$scope.mon_price_tot = $scope.mon_price_tot  + Math.ceil(parseFloat(val.price));
										$scope.mon_price_offer = $scope.mon_price_offer  + Math.ceil(parseFloat(val.offer_price));
										$scope.mon_price_dis = $scope.mon_price_dis  + Math.ceil(parseFloat(val.discount));
									}
									
								});
								
								$scope.mon_price_offer_temp = $scope.mon_price_offer;
								$scope.mon_price_dis_per = Math.ceil((($scope.mon_price_tot - $scope.mon_price_offer)/$scope.mon_price_tot)*100);
								
								
								//monthly campaign handling  
								if($scope.ecs_bud_monthly["1"] != undefined && $scope.ecs_bud_monthly["2"] != undefined ) {
						
									$scope.mon_ecs_price = parseFloat($scope.ecs_bud_monthly["1"].price) + parseFloat($scope.ecs_bud_monthly["2"].price);
									$scope.mon_ecs_offer = parseFloat($scope.ecs_bud_monthly["1"].offer_price) + parseFloat($scope.ecs_bud_monthly["2"].offer_price);
									$scope.mon_ecs_dic = parseFloat($scope.ecs_bud_monthly["1"].discount) + parseFloat($scope.ecs_bud_monthly["2"].discount);
									
									$scope.mon_ecs_dic_per = Math.ceil((($scope.mon_ecs_price - $scope.mon_ecs_offer)/$scope.mon_ecs_price)*100);
									
									$scope.show_ecs_mon_campaign =1;
									$scope.show_ecs_mon_both =1;
									
								}else if($scope.ecs_bud_monthly["1"] == undefined && $scope.ecs_bud_monthly["2"] != undefined ) {
									
									$scope.mon_ecs_price = parseFloat($scope.ecs_bud_monthly["2"].price);
									$scope.mon_ecs_offer = parseFloat($scope.ecs_bud_monthly["2"].offer_price);
									$scope.mon_ecs_dic = parseFloat($scope.ecs_bud_monthly["2"].discount);
									
									$scope.mon_ecs_dic_per = parseFloat($scope.ecs_bud_monthly["2"].discount_percent);
									
									$scope.show_ecs_mon_campaign =1;
									
								}else if($scope.ecs_bud_monthly["2"] == undefined && $scope.ecs_bud_monthly["1"] != undefined) {
									
									$scope.mon_ecs_price = parseFloat($scope.ecs_bud_monthly["1"].price);
									$scope.mon_ecs_offer = parseFloat($scope.ecs_bud_monthly["1"].offer_price);
									$scope.mon_ecs_dic = parseFloat($scope.ecs_bud_monthly["1"].discount);
									
									$scope.mon_ecs_dic_per = parseFloat($scope.ecs_bud_monthly["1"].discount_percent);
									
									$scope.show_ecs_mon_campaign =1;
									
								}else {
									
									
								}
								
								//advance omni calculation
								if($scope.ecs_bud_advance["72"] != undefined) {
									if($scope.ecs_bud_advance["173"] == undefined) {
										$scope.adv_omni_price = parseFloat($scope.ecs_bud_advance["72"].price) + parseFloat($scope.ecs_bud_advance["73"].price);
										$scope.adv_omni_offer_price = parseFloat($scope.ecs_bud_advance["72"].offer_price) + parseFloat($scope.ecs_bud_advance["73"].offer_price);
										$scope.adv_omni_offer_price_temp = parseFloat($scope.ecs_bud_advance["72"].offer_price) + parseFloat($scope.ecs_bud_advance["73"].offer_price);
										$scope.upfront_disc[3] = parseFloat($scope.ecs_bud_advance["72"].offer_price) + parseFloat($scope.ecs_bud_advance["73"].offer_price);
										$scope.adv_omni_dis_price = parseFloat($scope.ecs_bud_advance["72"].discount) + parseFloat($scope.ecs_bud_advance["73"].discount);
										$scope.adv_omni_dis_percentage = Math.ceil((($scope.adv_omni_price - $scope.adv_omni_offer_price)/$scope.adv_omni_price)*100);
									}else {
										$scope.adv_omni_price = parseFloat($scope.ecs_bud_advance["72"].price);
										$scope.adv_omni_offer_price = parseFloat($scope.ecs_bud_advance["72"].offer_price);
										$scope.adv_omni_offer_price_temp = parseFloat($scope.ecs_bud_advance["72"].offer_price);
										$scope.adv_omni_dis_price = parseFloat($scope.ecs_bud_advance["72"].discount);
										$scope.adv_omni_dis_percentage = parseFloat($scope.ecs_bud_advance["72"].discount_percent);
									}
								}
								
								
								//advance total price calculation
								
								$scope.adv_price_dis_per =0;
								angular.forEach($scope.ecs_bud_advance,function(val,key) {
									if($scope.adv_cb[val.campaignid] != false) {
										$scope.adv_price_tot = $scope.adv_price_tot  + parseFloat(val.price);
										$scope.adv_price_offer = $scope.adv_price_offer  + parseFloat(val.offer_price);
										$scope.adv_price_dis = $scope.adv_price_dis  + parseFloat(val.discount);
										$scope.adv_price_dis_per = $scope.adv_price_dis_per  + parseFloat(val.discount_percent);
									}
								});
								
								$scope.adv_price_offer_temp = Math.ceil($scope.adv_price_offer);
								$scope.adv_price_dis_per = Math.ceil((($scope.adv_price_tot - $scope.adv_price_offer)/$scope.adv_price_tot)*100);
								
								//advance campaign handling
								if($scope.ecs_bud_advance["1"] != undefined && $scope.ecs_bud_advance["2"] != undefined ) {
						
									$scope.adv_ecs_price = parseFloat($scope.ecs_bud_advance["1"].price) + parseFloat($scope.ecs_bud_advance["2"].price);
									$scope.adv_ecs_offer = parseFloat($scope.ecs_bud_advance["1"].offer_price) + parseFloat($scope.ecs_bud_advance["2"].offer_price);
									$scope.adv_ecs_dic = parseFloat($scope.ecs_bud_advance["1"].discount) + parseFloat($scope.ecs_bud_advance["2"].discount);
									
									
									$scope.adv_ecs_dic_per = Math.ceil((($scope.adv_ecs_price - $scope.adv_ecs_offer)/$scope.adv_ecs_price)*100);
									
									$scope.show_ecs_adv_campaign =1;
									$scope.show_ecs_adv_both =1;
									
								}else if($scope.ecs_bud_advance["1"] == undefined && $scope.ecs_bud_advance["2"] != undefined) {
									
									$scope.adv_ecs_price = parseFloat($scope.ecs_bud_advance["2"].price);
									$scope.adv_ecs_offer = parseFloat($scope.ecs_bud_advance["2"].offer_price);
									$scope.adv_ecs_dic = parseFloat($scope.ecs_bud_advance["2"].discount);
									
									$scope.adv_ecs_dic_per = parseFloat($scope.ecs_bud_advance["2"].discount_percent);
									
									$scope.show_ecs_adv_campaign =1;
									
								}else if($scope.ecs_bud_advance["2"] == undefined && $scope.ecs_bud_advance["1"] != undefined) {
									
									$scope.adv_ecs_price = parseFloat($scope.ecs_bud_advance["1"].price);
									$scope.adv_ecs_offer = parseFloat($scope.ecs_bud_advance["1"].offer_price);
									$scope.adv_ecs_dic = parseFloat($scope.ecs_bud_advance["1"].discount);
									
									$scope.adv_ecs_dic_per = parseFloat($scope.ecs_bud_advance["1"].discount_percent);
									
									$scope.show_ecs_adv_campaign =1;
									
								}else {
									
									
								}
								//grand total calculation  
								//total price is = 9*all camapign + advance total price + 3*omni and its the same for offer price
                                if((($scope.ecs_bud_monthly["273"] != undefined && $scope.adv_cb[273] != false) || ($scope.ecs_bud_monthly["741"] != undefined && $scope.adv_cb[741] != false)) && $scope.selected_camapign != "combo2" && $scope.selected_camapign != "combo1" && $scope.selected_camapign != "omni2" && $scope.selected_camapign != "omnisupreme") {
									if($scope.ecs_bud_monthly["741"] != undefined){
										$scope.key	=	'741';
									}
                                    $scope.ecs_tot_price =  Math.ceil((($scope.mon_price_tot * 9) +  $scope.adv_price_tot + parseFloat($scope.ecs_bud_monthly[$scope.key].price) * 3));
                                    $scope.ecs_tot_offer_price =  Math.ceil((($scope.mon_price_offer * 9) +  $scope.adv_price_offer + parseFloat($scope.ecs_bud_monthly[$scope.key].offer_price) * 3));
								}else if($scope.selected_camapign == "omni2"){
									$scope.ecs_tot_price =  Math.ceil(($scope.mon_price_tot * 9) +  $scope.adv_price_tot + parseFloat($scope.ecs_bud_monthly["273"].price) * 3);
									$scope.ecs_tot_offer_price =  Math.ceil(($scope.mon_price_offer * 9) +  $scope.adv_price_offer + parseFloat($scope.ecs_bud_monthly["273"].offer_price) * 3);
                               					 }else if($scope.ecs_bud_monthly["111"] != undefined){ 
									$scope.ecs_tot_price =  Math.ceil(($scope.mon_price_tot * 9) +  $scope.adv_price_tot - parseFloat($scope.ecs_bud_monthly["111"].price) * 9);
									$scope.ecs_tot_offer_price =  Math.ceil(($scope.mon_price_offer * 9) +  $scope.adv_price_offer - parseFloat($scope.ecs_bud_monthly["111"].offer_price) * 9);
								  }else{
									if($scope.selected_campaign_name == "package_expired") {
										$scope.ecs_tot_price =  Math.ceil($scope.mon_price_tot * 12);
										$scope.ecs_tot_offer_price =  Math.ceil($scope.mon_price_offer * 12);
									}else if($scope.ecs_bud_monthly["5"] != undefined || $scope.camp_selected.indexOf("119") > -1){
										if($scope.adv_cb[22] != false && typeof $scope.ecs_bud_advance['22'] != 'undefined'){
											$scope.ecs_tot_price =  Math.ceil($scope.mon_price_tot * 12) + parseInt($scope.ecs_bud_advance['22'].price);
											$scope.ecs_tot_offer_price =  Math.ceil($scope.mon_price_offer * 12) + parseInt($scope.ecs_bud_advance['22'].offer_price);
										}else if($scope.adv_cb[225] != false && typeof $scope.ecs_bud_advance['225'] != 'undefined'){
											$scope.ecs_tot_price =  Math.ceil($scope.mon_price_tot * 12) + parseInt($scope.ecs_bud_advance['225'].price);
											$scope.ecs_tot_offer_price =  Math.ceil($scope.mon_price_offer * 12) + parseInt($scope.ecs_bud_advance['225'].offer_price);
										}else {
											$scope.ecs_tot_price =  Math.ceil($scope.mon_price_tot * 12);
											$scope.ecs_tot_offer_price =  Math.ceil($scope.mon_price_offer * 12);
										}
									}else {
										$scope.ecs_tot_price =  Math.ceil((($scope.mon_price_tot * 9) +  $scope.adv_price_tot));
										$scope.ecs_tot_offer_price =  Math.ceil((($scope.mon_price_offer * 9) +  $scope.adv_price_offer));
									}
								}
								
									
							}else if(response.error.code == 1){
								$scope.show_ecs_error = true;
							}
						});
					}
				});
				
			}
		}
		
		$scope.check_ecs =false;
		$scope.tenure_miss = false;
		$scope.tenure_less = false;
		$scope.pure_jdrr = false;
		$scope.stop_procceding = false;
		$scope.omni_ultima = false;
		
		APIServices.check_upfront(returnState.paridInfo,returnState.ver,$scope.omni_type).success(function(response) { 
			if(response.error.code == 1) {
				$scope.tenure_miss = true;
			}else if(response.error.code == 2) {
				$scope.tenure_less = true;
			}else if(response.error.code == 3) {
				$scope.pure_jdrr = true;
			}else if(response.error.code == 4) {
				$scope.weekly_package = true;
			}else if(response.error.code == 5) {
				$scope.omni_ultima = true;
			}
			
			if($rootScope.extraHandler == 'jda') { 
				var module_name = "jda";
			}else {
				var module_name = "tme";
			}
			APIServices.check_ecs(returnState.paridInfo,returnState.ver,module_name).success(function(response) { 
				if(response.error.code == 5 && ($scope.tenure_miss || $scope.tenure_less)) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please Contact CS Team";
				}else if(response.error.code == 5 && $scope.pure_jdrr) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Its an ongoing ECS contract,You cant take pure Jdrr/Banner/Jdrr plus";
					$scope.stop_procceding = true;
					return false;
				}else if((($scope.tenure_miss || $scope.tenure_less || $scope.pure_jdrr || $scope.omni_ultima) || $scope.selected_payment_type == 'upfront') && response.error.code == 0) {
					$scope.switch_option("campaign");
					$('#tab-1-content').fadeIn();
					$('#tab-2-content').hide();
					$('#tab-2').removeClass('tab-main-active');
					$('#tab-1').addClass('tab-main-active');
					$('#tab-2').addClass('mask_upfront');
				}else if((response.error.code == 5 || $scope.weekly_package == true) || $scope.selected_payment_type == 'ecs'){
					$scope.check_ecs =true;
					$scope.switch_option("ecs");
					$('#tab-1-content').hide();
					$('#tab-2-content').fadeIn();
					$('#tab-1').removeClass('tab-main-active');
					$('#tab-2').addClass('tab-main-active');
					$('#tab-1').css({'border-left':'0'})
					$('#tab-1').addClass('mask_upfront');
				}
				else{
					$scope.switch_option("campaign");
				}
			});
			
		});
		
		$scope.camp_per = 0;	
		// js level handling for discount 
		//~ $scope.call_disc_api = function() {
			//~ if($scope.upfront_disc[1] != '' && $scope.upfront_disc[1] != undefined && angular.isNumber(+$scope.upfront_disc[1]) &&  $scope.upfront_disc[1] != 0) {
				//~ 
				//~ if($scope.upfront_disc[1] > 10 ) {
					//~ alert("Discount Cant be more than 10");
					//~ return false;
				//~ }else {
					//~ 
					//~ if($scope.best_price != undefined) {
						//~ 
						//~ $scope.best_disc_price = Math.ceil(($scope.best_price * $scope.upfront_disc[1])/100);
						//~ $scope.best_offer_price = Math.ceil($scope.best_price - $scope.best_disc_price);
						//~ $scope.best_dis_percentage =  $scope.upfront_disc[1];
					//~ 
					//~ }
					//~ 
					//~ if($scope.package_price != undefined) {
						//~ 
						//~ $scope.package_disc_price = Math.ceil(($scope.package_price * $scope.upfront_disc[1])/100);
						//~ $scope.package_offer_price = Math.ceil($scope.package_price - $scope.package_disc_price);
						//~ $scope.package_dis_percentage =  $scope.upfront_disc[1];
					//~ 
					//~ }
					//~ 
					//~ $scope.campaign_dis_price = Math.ceil(($scope.campaign_price * $scope.upfront_disc[1])/100);
					//~ $scope.campaign_offer_price = Math.ceil($scope.campaign_price - $scope.campaign_dis_price);
					//~ $scope.campaign_dis_percentage =  $scope.upfront_disc[1];
					//~ 
					//~ $scope.camp_per = $scope.upfront_disc[1];
				//~ }
				//~ 
			//~ }
		//~ }
		
		
		$scope.call_disc_api = function() {
			if($scope.upfront_disc[1] != '' && $scope.upfront_disc[1] != undefined && angular.isNumber(+$scope.upfront_disc[1])) {
				APIServices.call_disc_api(returnState.paridInfo,returnState.ver,$scope.upfront_disc[1]).success(function(response) {
					if(response.error.code == 0){
						$scope.switch_option("campaign");
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
					}
				});
			}
		}
		
	
		$scope.show_omni_input = false;
		$scope.show_omni_dis = function(){
		if($scope.show_omni_input == true) {
				$scope.show_omni_input = false;
			}else {
				$scope.show_omni_input = true;
			}
		}
		
		//function for omni discount temp variables contains the original value 
		
		//handling for js level discount
		//~ $scope.upfront_omni_offer = function(type,flg) {
			//~ if(type ==  "ecs") {
				//~ var user_offer = $scope.upfront_disc[3]; 
				//~ var user_mon_offer = $scope.upfront_disc[4]; 
				//~ var type_flg = 1;
				//~ if($scope.upfront_disc[3] < 1000) {
					//~ alert("enter at least 1,000/-");
					//~ return false;
				//~ }
				//~ if($scope.upfront_disc[4] < 100) {
					//~ alert("enter at least 100/-");
					//~ return false;
				//~ }
				//~ 
				//~ // advance ecs discount logic 
				//~ if(flg =="adv") {
					//~ // console.log("typed "+$scope.upfront_disc[3]);
					//~ // console.log("original "+$scope.adv_omni_offer_price_temp);
					//~ // console.log("grand price temp "+$scope.adv_price_offer_temp);
					//~ 
					//~ if($scope.adv_omni_offer_price_temp != $scope.upfront_disc[3]) {
						//~ 
						//~ if($scope.upfront_disc[3] < $scope.adv_omni_offer_price_temp) {
							//~ 
							//~ $scope.adv_price_offer = $scope.adv_price_offer_temp - ($scope.adv_omni_offer_price_temp - $scope.upfront_disc[3]);
							//~ $scope.adv_omni_offer_price = $scope.upfront_disc[3];
							//~ 
							//~ // console.log("grand price less "+$scope.adv_price_offer);
							//~ 
						//~ }else if($scope.upfront_disc[3] > $scope.adv_omni_offer_price_temp) {
							//~ 
							//~ $scope.adv_price_offer = $scope.adv_price_offer_temp + ($scope.upfront_disc[3] - $scope.adv_omni_offer_price_temp);
							//~ $scope.adv_omni_offer_price = $scope.upfront_disc[3];
							//~ 
							//~ // console.log("grand price more "+$scope.adv_price_offer);
						//~ }
						//~ 
					//~ }else {
						//~ 
						//~ $scope.adv_price_offer = $scope.adv_price_offer_temp;
						//~ $scope.adv_omni_offer_price = $scope.adv_omni_offer_price_temp;
						//~ 
					//~ }
					//~ 
					//~ $scope.adv_omni_dis_price = Math.ceil($scope.adv_omni_price -  $scope.upfront_disc[3]);
					//~ $scope.adv_omni_dis_percentage = Math.ceil(($scope.adv_omni_dis_price/$scope.adv_omni_price)*100);
					//~ 
					//~ $scope.adv_price_dis = Math.ceil($scope.adv_price_tot -  $scope.adv_price_offer);
					//~ $scope.adv_price_dis_per = Math.ceil(($scope.adv_price_dis/$scope.adv_price_tot)*100);
				//~ }else if(flg =="monthly") {
					//~ 
					//~ //monthly discount logic
					//~ 
					//~ // console.log("typed "+$scope.upfront_disc[4]);
					//~ // console.log("original "+$scope.mon_omni_offer_price_temp);
					//~ // console.log("grand price temp "+$scope.mon_price_offer_temp);
					//~ 
					//~ if($scope.mon_omni_offer_price_temp != $scope.upfront_disc[4]) {
						//~ 
						//~ if($scope.upfront_disc[4] < $scope.mon_omni_offer_price_temp) {
							//~ 
							//~ $scope.mon_price_offer = $scope.mon_price_offer_temp - ($scope.mon_omni_offer_price_temp - $scope.upfront_disc[4]);
							//~ $scope.mon_omni_offer_price = $scope.upfront_disc[4];
							//~ 
							//~ // console.log("grand price less "+$scope.mon_price_offer);
							//~ 
						//~ }else if($scope.upfront_disc[4] > $scope.mon_omni_offer_price_temp) {
							//~ 
							//~ $scope.mon_price_offer = $scope.mon_price_offer_temp + ($scope.upfront_disc[4] - $scope.mon_omni_offer_price_temp);
							//~ $scope.mon_omni_offer_price = $scope.upfront_disc[4];
							//~ 
							//~ // console.log("grand price more "+$scope.mon_price_offer);
						//~ }
						//~ 
					//~ }else {
						//~ 
						//~ $scope.mon_price_offer = $scope.mon_price_offer_temp;
						//~ $scope.mon_omni_offer_price = $scope.mon_omni_offer_price_temp;
						//~ 
					//~ }
					//~ 
					//~ $scope.mon_omni_dis_price = Math.ceil($scope.mon_omni_price -  $scope.upfront_disc[4]);
					//~ $scope.mon_omni_dis_percentage = Math.ceil(($scope.mon_omni_dis_price/$scope.mon_omni_price)*100);
					//~ 
					//~ $scope.mon_price_dis = Math.ceil($scope.mon_price_tot -  $scope.mon_price_offer);
					//~ $scope.mon_price_dis_per = Math.ceil(($scope.mon_price_dis/$scope.mon_price_tot)*100);
					//~ 
				//~ }
				//~ 
				//~ $scope.ecs_tot_offer_price =  Math.ceil((($scope.mon_price_offer * 9) +  $scope.adv_price_offer + parseFloat($scope.upfront_disc[4]) * 3));
				//~ 
			//~ }else if(type ==  "campaign") {
				//~ var user_offer = $scope.upfront_disc[2]; 
				//~ var user_mon_offer = 0; 
				//~ var type_flg = 0;
				//~ if($scope.upfront_disc[2] < 1000) {
					//~ alert("enter at least 1,000/-");
					//~ return false;
				//~ }
				//~ 
				//~ // console.log("typed "+$scope.upfront_disc[2]);
				//~ // console.log("original "+$scope.omni_offer_price_temp);
				//~ // console.log("grand price temp "+$scope.grand_price_offer_temp);
				//~ 
				//~ if($scope.omni_offer_price_temp != $scope.upfront_disc[2]) {
					//~ if($scope.upfront_disc[2] < $scope.omni_offer_price_temp) {
						//~ 
						//~ $scope.grand_price_offer = $scope.grand_price_offer_temp - ($scope.omni_offer_price_temp - $scope.upfront_disc[2]);
						//~ $scope.omni_offer_price = $scope.upfront_disc[2];
						//~ 
						//~ // console.log("grand price less "+$scope.grand_price_offer);
					//~ }else if($scope.upfront_disc[2] > $scope.omni_offer_price_temp) {
						//~ 
						//~ $scope.grand_price_offer = $scope.grand_price_offer_temp + ($scope.upfront_disc[2] - $scope.omni_offer_price_temp);
						//~ $scope.omni_offer_price = $scope.upfront_disc[2];
						//~ 
						//~ // console.log("grand price more "+$scope.grand_price_offer);
					//~ } 
					//~ 
				//~ }else {
					//~ $scope.grand_price_offer = $scope.grand_price_offer_temp;
					//~ $scope.omni_offer_price = $scope.omni_offer_price_temp;
				//~ 
				//~ }
				//~ 
				//~ $scope.omni_dis_price = Math.ceil($scope.omni_price -  $scope.upfront_disc[2]);
				//~ $scope.omni_dis_percentage = Math.ceil(($scope.omni_dis_price/$scope.omni_price)*100);
				//~ 
				//~ $scope.grand_price_dis = Math.ceil($scope.grand_price_tot -  $scope.grand_price_offer);
				//~ $scope.grand_price_dis_per = Math.ceil(($scope.grand_price_dis/$scope.grand_price_tot)*100);
				//~ 
			//~ }
		//~ }
		
		
		$scope.upfront_omni_offer = function(type) {
			if(type ==  "ecs") {
				var user_offer = $scope.upfront_disc[3]; 
				var user_mon_offer = $scope.upfront_disc[4]; 
				var type_flg = 1;
				if($scope.upfront_disc[3] < 20000) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 20,000/-";
					return false;
				}
				if($scope.upfront_disc[4] < 2000) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 2,000/-";
					return false;
				}
			}else if(type ==  "campaign") {
				var user_offer = $scope.upfront_disc[2]; 
				var user_mon_offer = 0; 
				var type_flg = 0;
				
				if($scope.upfront_disc[2] < 65000 && $scope.selected_camapign == "omni1") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 65,000/-";
					return false;
				}
				
				if($scope.upfront_disc[2] < 36000 && $scope.selected_camapign == "omni2") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 36,000/-";
					return false;
				}
						
			}
			
			if(user_offer != undefined && user_mon_offer != undefined) {
				APIServices.addjdomniLive(returnState.paridInfo,returnState.ver,type_flg,user_offer,user_mon_offer,0,$scope.omni_type).success(function(response) {
					if(response.error.code == 0) {
						$scope.switch_option(type);
					} 
				});
			}
		}
		
		
		$scope.jdrr_dis_offer = function(key,type) {
			if(type ==  "ecs") {
				var user_offer = 0; 
				var user_mon_offer = $scope.upfront_disc[6]; 
				var type_flg = 1;
				
				if($scope.upfront_disc[6] < 100){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 100/-";
					return false;
				}
				
			}else if(type ==  "campaign") {
				
				
				var user_offer = $scope.upfront_disc[5]; 
				var user_mon_offer = 0; 
				var type_flg = 0;
				
				if($scope.upfront_disc[5] < 100){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 100/-";
					return false;
				}
			}
			
			if(user_offer != undefined && user_mon_offer != undefined) {
				if(key == 225) {
					APIServices.jdrrplusdiscount(returnState.paridInfo,returnState.ver,type_flg,user_offer,user_mon_offer).success(function(response) {
						if(response.error.code == 0) {
							$scope.switch_option(type);
						} 
					});
				}else if(key == 22) {
					APIServices.addjdrrLive(returnState.paridInfo,returnState.ver,type_flg,user_offer,user_mon_offer,0).success(function(response) {
						if(response.error.code == 0) {
							$scope.switch_option(type);
						} 
					});
				}else if(key == 5) {
					APIServices.addbannerlive(returnState.paridInfo,returnState.ver,type_flg,user_offer,user_mon_offer,0).success(function(response) {
						if(response.error.code == 0) {
							$scope.switch_option(type);
						} 
					});
				}
			}
		}
		
		
		$scope.proceed_payment = function(pay_type,ev) {
			
			
			if($scope.adv_cb[1] == false && $scope.adv_cb[111] == false) {
				if(confirm("If you want to add JD campaign again then You need to recalculate the budget")){
				
				}else {
					return false;
				}
			}
			
			/*if($scope.stop_procceding == true) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Its an ongoing ECS contract,You cant take pure Jdrr/Banner/Jdrr plus";
				return false;
			}*/
			
			
			if(pay_type == "ecs") {
				var type_flg = 1;
				
				
				if($scope.upfront_disc[3] < 1000 && ($scope.selected_camapign != "combo1" && $scope.selected_camapign != "combo2")) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 1,000/-";
					return false;
				}
				
				if($scope.upfront_disc[4] < 100 && ($scope.selected_camapign != "combo1" &&  $scope.selected_camapign != "combo2")) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 100/-";
					return false;
				}
					
				
				
				var true_count = 0;
				var true_value ='';
				angular.forEach($scope.ecs_bud_advance,function(value,key) {
					if($scope.adv_cb[key] == true) {
						true_count++;
						true_value = key;
					}
				});
				
				
				if(true_count == 1 && (true_value == 22 || true_value == 225 )) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = 'ECS is not allowed for pure JDRR and JDRR Plus';
					return false;
				}
					
				if($scope.show_omni_input) {	
					
					var user_offer = $scope.upfront_disc[3]; 
					var user_mon_offer = $scope.upfront_disc[4]; 
					var user_offer_jdrr = 0; 
					var user_mon_offer_jdrr = $scope.upfront_disc[6]; 
				}
				
				if(($scope.ecs_tot_price == 0 && $scope.ecs_tot_offer_price == 0) || ($scope.ecs_tot_price == undefined && $scope.ecs_tot_offer_price == undefined) ) {
					$mdToast.show(
						$mdToast.simple()
						.content("Please Select Atleast One Campaign")
						.position('top right')
						.hideDelay(3000)
					);	
					return false;
				}
				
				
			
			}
			else {
				var type_flg = 0;
				
				if($scope.show_omni_input) {
					var user_offer = $scope.upfront_disc[2]; 
					var user_mon_offer = 0; 
					var user_offer_jdrr = $scope.upfront_disc[5]; 
					var user_mon_offer_jdrr =0; 
					
				}
				
				if($scope.upfront_disc[2] < 1000 && ($scope.selected_camapign != "combo1" && $scope.selected_camapign != "combo2")) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Enter at least 1,000/-";
					return false;
				}
					
				
				if($scope.grand_price_tot == 0 && $scope.grand_price_offer == 0 || ($scope.grand_price_tot == undefined && $scope.grand_price_offer == undefined)) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please Select Atleast One Campaign";
					return false;
				}
				
			}
			
			var selected_opt=$cookieStore.get('campaign_str');
			$scope.omniArr	=	[];
			$scope.omniStr	=	'';
			$scope.omniArr	=	selected_opt.split(',');
			angular.forEach($scope.omniArr,function(value,key) {
				if((value.indexOf('72') > -1 || value.indexOf('73') > -1 || value.indexOf('74') > -1) && (value != '742' && value != '743')){
					
					$scope.omniStr	=	value;
				}
			});
			
			
			
			var combo=0;
			if($scope.selected_camapign == "combo1"){
				combo = 1;
			}else if ($scope.selected_camapign == "combo2") {
				var omni_type = 5;
				combo = 5;
			}else if ($scope.selected_camapign == "omni1") {
				var omni_type = 1;
			}else if ($scope.selected_camapign == "omni2") {
				var omni_type = 2;
			}else if ($scope.selected_camapign == "omniultima") {
				var omni_type = 3;
			}else if ($scope.selected_camapign == "omnisupreme") {
				var omni_type = 4;
			}else if ($scope.selected_camapign == "omni7") {
				var omni_type = 7;
			}
			
			
			var omni_type = $scope.omniStr;
			if($scope.show_omni_input){
				APIServices.delete_unchecked(returnState.paridInfo,returnState.ver,$scope.adv_cb).success(function(response) {
					if(response.error_code == 0) {
						APIServices.go_to_payment_page(returnState.paridInfo,returnState.ver,type_flg,user_offer,user_mon_offer,user_offer_jdrr,user_mon_offer_jdrr,combo,omni_type,$scope.setup_exclude,'',$scope.banner_rotation,$scope.pck_dependent).success(function(response) {
							$state.go('appHome.ecsform',{parid:returnState.paridInfo,ver:$rootScope.budgetVersion,ecsflg:type_flg,page:$rootScope.extraHandler});
						});
					}
				});
			}else {
				APIServices.delete_unchecked(returnState.paridInfo,returnState.ver,$scope.adv_cb).success(function(response) {
					if(response.error_code == 0) {
						APIServices.go_to_payment_page(returnState.paridInfo,returnState.ver,type_flg,$scope.custom_73,$scope.omni_monthly,$scope.custom_5,'',combo,omni_type,$scope.setup_exclude,$scope.custom_72,$scope.banner_rotation,$scope.pck_dependent).success(function(response) {
							$state.go('appHome.ecsform',{parid:returnState.paridInfo,ver:$rootScope.budgetVersion,ecsflg:type_flg,page:$rootScope.extraHandler});
						});
					}
					
				});
			}
			
			
		}
		
		setTimeout(
			function() {
				$('html, body').animate({scrollTop: "2px" }, 500);
			},500
		);
		
	});
	
	tmeModuleApp.controller('paymentsummaryController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$scope.url_ver = returnState.ver;
		$scope.url_paridInfo = returnState.paridInfo;
		$rootScope.extraHandler = returnState.page;
		
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		
		var wchcombo=$cookieStore.get('selected_option');
		$scope.combo=0;
		
		if(wchcombo == "combo1"){
			$scope.combo = 1;
		}else if(wchcombo == "combo2") {
			$scope.omni_type = 5;
		}else if(wchcombo == "omni1") {
			$scope.omni_type = 1;
		}else if(wchcombo == "omni2") {
			$scope.omni_type = 2;
		}else if (wchcombo == "omniultima") {
			$scope.omni_type = 3;
		}else if (wchcombo == "omnisupreme") {
			$scope.omni_type = 4;
		}else if (wchcombo == "omni7") {
			$scope.omni_type = 7;
		}
		
		$scope.show_adv_campaign =0;
		$scope.show_adv_both =0;
		
		$scope.show_mon_campaign =0;
		$scope.show_mon_both =0;
		$rootScope.extraHandler	=	returnState.page;
		
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}
		
		
		APIServices.payment_summary_list(returnState.paridInfo,returnState.ver,$scope.combo,$scope.omni_type).success(function(response) {
			$scope.pay_mon_list = response.error.msg.monthly;
			$scope.pay_adv_list = response.error.msg.advance;
			
			$scope.adv_keys = Object.keys($scope.pay_adv_list);
			
			if($scope.pay_mon_list["72"] != undefined && $scope.pay_mon_list["173"] == undefined) {
				$scope.omni_pay_mon = Math.ceil(parseFloat($scope.pay_mon_list["72"].offer_price) + parseFloat($scope.pay_mon_list["73"].offer_price));
			}else if($scope.pay_mon_list["73"] != undefined && $scope.pay_mon_list["72"] == undefined){
				$scope.omni_pay_mon = parseFloat($scope.pay_mon_list["73"].offer_price);
			}
			
			
			
			
			if($scope.pay_mon_list["1"] != undefined && $scope.pay_mon_list["2"] != undefined ) {
				
				$scope.mon_camp_price = parseFloat($scope.pay_mon_list["1"].offer_price) + parseFloat($scope.pay_mon_list["2"].offer_price);
				$scope.show_mon_campaign = 1;
				$scope.show_mon_both =1;
				
			}else if($scope.pay_mon_list["1"] == undefined && $scope.pay_mon_list["2"] != undefined) {
				
				$scope.mon_camp_price = parseFloat($scope.pay_mon_list["2"].offer_price);
				$scope.show_mon_campaign = 1;
				
			}else if($scope.pay_mon_list["2"] == undefined && $scope.pay_mon_list["1"] != undefined) {
				
				$scope.mon_camp_price = parseFloat($scope.pay_mon_list["1"].offer_price);
				$scope.show_mon_campaign = 1;
				
			}else {
				$scope.show_mon_campaign = 0;
				
			}
			
			
			if($scope.pay_adv_list["1"] != undefined && $scope.pay_adv_list["2"] != undefined ) {
				
				$scope.adv_camp_price = parseFloat($scope.pay_adv_list["1"].offer_price) + parseFloat($scope.pay_adv_list["2"].offer_price);
				$scope.show_adv_campaign =1;
				$scope.show_adv_both =1;
				
			}else if($scope.pay_adv_list["1"] == undefined && $scope.pay_adv_list["2"] != undefined) {
				
				$scope.adv_camp_price = parseFloat($scope.pay_adv_list["2"].offer_price);
				$scope.show_adv_campaign =1;
				
			}else if($scope.pay_adv_list["2"] == undefined && $scope.pay_adv_list["1"] != undefined) {
				
				$scope.adv_camp_price = parseFloat($scope.pay_adv_list["1"].offer_price);
				$scope.show_adv_campaign =1;
				
			}else {
				$scope.show_adv_campaign =0;
				
			}
						
									
			
			$scope.mon_total = response.error.msg.total.monthly;
			$scope.adv_total = response.error.msg.total.advance;
		
			$scope.mon_tax = response.error.msg.tax.monthly;
			$scope.adv_tax = response.error.msg.tax.advance;
		
			$scope.mon_total_payable= response.error.msg.total_payable.monthly;
			$scope.adv_total_payable = response.error.msg.total_payable.advance;
		});
		
		$scope.call_finnace_pg = function(ev) {
			
			if($scope.adv_keys.length == 1 && ($scope.adv_keys[0] == '225' || $scope.adv_keys[0] == "22")){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = 'ECS is not allowed for pure JDRR and JDRR Plus';
				return false;
			}
			
			$window.location.href  = $scope.domainUrl;
		}
		
		setTimeout(
			function() {
				$('html, body').animate({scrollTop: "2px" }, 500);
			},500
		);
		
	});


	tmeModuleApp.controller('ecsformController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}




		$scope.acc_ecs_flag = returnState.ecsflg; 

		$scope.url_ver = returnState.ver;
		$scope.url_paridInfo = returnState.paridInfo;
		$rootScope.extraHandler = returnState.page;
		
		$scope.ecs_form_ip = {};
		$scope.ecs_form_ip[1] = '';
		$scope.ecs_form_ip[2] = '';
		$scope.ecs_form_ip[3] = '';
		$scope.ecs_form_ip[4] = '';
		$scope.ecs_form_ip[5] = '';
		
		$scope.ecs_form_hid = {};
		$scope.ecs_form_hid[1] = '';
		$scope.ecs_form_hid[2] = '';
		$scope.ecs_form_hid[3] = '';
		$scope.ecs_form_hid[4] = '';
		 $scope.ecs_form_hid[5] = '';
		 
		$scope.bank_detail = {};
		$scope.bank_detail[1] ='';
		$scope.bank_detail[2] ='';
		$scope.bank_detail[3] ='';
		
		
		$scope.show_acc_name_hid = false;
		$scope.show_acc_no_hid = false;
		$scope.show_ifsc_hid = false;
		
		
		
		APIServices.get_accountdetials(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.ecs_form_ip[1] = response.error.result.account_name; 
				$scope.ecs_form_ip[2] = response.error.result.account_number ;
				$scope.ecs_form_ip[3] = response.error.result.account_type; 
				$scope.ecs_form_ip[4] = response.error.result.ifsc_code;  
				 $scope.ecs_form_ip[5] = response.error.result.micr_code;
				 
				$scope.ecs_form_hid[1] = response.error.result.account_name; 
				$scope.ecs_form_hid[2] = response.error.result.account_number ;
				$scope.ecs_form_hid[3] = response.error.result.account_type; 
				$scope.ecs_form_hid[4] = response.error.result.ifsc_code;  
				$scope.ecs_form_hid[5] = response.error.result.micr_code;
				
				$scope.bank_detail[1] = response.error.result.bank_name;
				$scope.bank_detail[2] = response.error.result.branch_location; 
				$scope.bank_detail[3] = response.error.result.bank_branch ;
						
			}else {
				$scope.ecs_form_ip[1] = '';
				$scope.ecs_form_ip[2] = '';
				$scope.ecs_form_ip[3] = '';
				$scope.ecs_form_ip[4] = '';
                $scope.ecs_form_ip[5] = '';//
			}
			
		});
		
		
		$scope.unmask = function(type) {
			if(type == "acc_name" ){
				$( "#acc_name" ).prop( "type", "text" );
				$scope.ecs_form_hid[1] = '';
			}
		
			if(type == "acc_no" ){ 
				$( "#acc_num" ).prop( "type", "text" );
				$scope.ecs_form_hid[2] = '';
			}
			
			if(type == "ifsc" ){ 
				$( "#ifc_code" ).prop( "type", "text" );
				$scope.ecs_form_hid[4] = '';
			}
			//
            if(type == "micr" ){
                $( "#micr_code" ).prop( "type", "text" );
                $scope.ecs_form_hid[5] = '';
            }
		}
		
		$scope.confirm_val = function(type){
			if(type == "acc_name" && $scope.ecs_form_ip[1] != ''){
				$scope.show_acc_name_hid = true;
				$( "#acc_name" ).prop( "type", "password" );
			}
			
			if(type == "acc_no" && $scope.ecs_form_ip[2] != ''){
				$scope.show_acc_no_hid = true;
				$( "#acc_num" ).prop( "type", "password" );
			}
			
			if(type == "ifsc" && $scope.ecs_form_ip[4] != ''){
				$scope.show_ifsc_hid = true;
				$( "#ifc_code" ).prop( "type", "password" );
			}
			//
			if(type == "micr" && $scope.ecs_form_ip[5] != ''){
                $scope.show_micr_hid = true;
                $( "#micr_code" ).prop( "type", "password" );
            }
			
		}
		
		
		$scope.show_examples = function(ev) {
			$mdDialog.show({
				controller: exampleController,
				templateUrl: 'partials/exampleDialog.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			})
			.then(function(answer) {
				$scope.alert = 'You said the information was "' + answer + '".';
			}, function() {
				$scope.alert = 'You cancelled the dialog.';
			});
			
		}
		
		function exampleController($mdDialog,$scope) {
			
			$scope.closepopup = function() {
				$mdDialog.hide();
				
			};
		}
		
		
		$scope.getbankdetials = function() { 
			if($scope.ecs_form_hid[4].length  == 11) {
				APIServices.get_bankdetials(returnState.paridInfo,returnState.ver,$scope.ecs_form_hid[4]).success(function(response) {
					if(response.error.code == 0) {
						$scope.bank_detail[1] = response.error.result.bank_name;
						$scope.bank_detail[2] = response.error.result.branch_location; 
						$scope.bank_detail[3] = response.error.result.bank_branch ;
						$scope.ecs_form_hid[4] = response.error.result.branch_ifsc;
						//~ $scope.ecs_form_ip[4] = response.error.result.branch_ifsc;
                        $scope.ecs_form_hid[5] = response.error.result.branch_micr;
					}else {
						$scope.bank_detail[1] = '';
						$scope.bank_detail[2] = ''; 
						$scope.bank_detail[3] = '' ;
						$scope.ecs_form_hid[4] = '' ;
                        $scope.ecs_form_hid[5] = '' ;
                         //~ $scope.ecs_form_ip[4] = '' ;
					}
				});
			}else if($scope.ecs_form_hid[4].length  > 11){
				$mdToast.show(
					$mdToast.simple()
					.content('IFSC code must be 11 characters')
					.position('top right')
					.hideDelay(3000)
				);	
				
				$scope.bank_detail[1] = '';
				$scope.bank_detail[2] = ''; 
				$scope.bank_detail[3] = '' ;
				 $scope.ecs_form_hid[4] = '' ;
                $scope.ecs_form_hid[5] = '' ;
                 //~ $scope.ecs_form_ip[4] = '' ;
				return false;
			}else if($scope.ecs_form_hid[4].length  < 11) {
				$scope.bank_detail[1] = '';
				$scope.bank_detail[2] = ''; 
				$scope.bank_detail[3] = '' ;
				$scope.ecs_form_hid[4] = '' ;
                $scope.ecs_form_hid[5] = '' ;
                 //~ $scope.ecs_form_ip[4] = '' ;
			}
		}
		$scope.get_bankdetialsmicr = function() { //alert($scope.ecs_form_hid[5]);
            if($scope.ecs_form_hid[5].length  == 9) {
                APIServices.get_bankdetialsmicr(returnState.paridInfo,returnState.ver,$scope.ecs_form_hid[5]).success(function(response) {
                    if(response.error.code == 0) {
                        $scope.bank_detail[1] = response.error.result.bank_name;
                        $scope.bank_detail[2] = response.error.result.branch_location;
                        $scope.bank_detail[3] = response.error.result.bank_branch ;
                        $scope.ecs_form_hid[5] = response.error.result.branch_micr;
                        $scope.ecs_form_hid[4] = response.error.result.branch_ifsc;
                        $scope.ecs_form_ip[4] = response.error.result.branch_ifsc;
                    }else {
                        $scope.bank_detail[1] = '';
                        $scope.bank_detail[2] = '';
                        $scope.bank_detail[3] = '' ;
                        $scope.ecs_form_hid[4] = '' ;
                        $scope.ecs_form_hid[5] = '' ;
                        //~ $scope.ecs_form_ip[4] = '' ;
                    }
                });
            }else if($scope.ecs_form_hid[5].length  > 9){
                $mdToast.show(
                    $mdToast.simple()
                    .content('MICR code must be 9 characters')
                    .position('top right')
                    .hideDelay(3000)
                );
                $scope.bank_detail[1] = '';
                $scope.bank_detail[2] = '';
                $scope.bank_detail[3] = '' ;
                $scope.ecs_form_hid[4] = '' ;
                //~ $scope.ecs_form_ip[4] = '' ;
                $scope.ecs_form_hid[5] = '' ;
                return false;
            }else if($scope.ecs_form_hid[5].length  < 9) {
                $scope.bank_detail[1] = '';
                $scope.bank_detail[2] = '';
                $scope.bank_detail[3] = '' ;
                $scope.ecs_form_hid[4] = '' ;
                //~ $scope.ecs_form_ip[4] = '' ;
                $scope.ecs_form_hid[5] = '' ;
            }
        }
        
		$scope.saveaccdetails = function() {
			
			if($scope.ecs_form_ip[1] == '' || $scope.ecs_form_ip[1] == undefined  || $scope.ecs_form_ip[1] == 'undefined' ) {  
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter Account Holder's Name";
				return false;
			}else if($scope.ecs_form_ip[2] == '' || $scope.ecs_form_ip[2] == undefined || $scope.ecs_form_ip[2] == 'undefined' ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter Account Number";
				return false;
			}else if($scope.ecs_form_ip[3] == '' || $scope.ecs_form_ip[3] == undefined || $scope.ecs_form_ip[3] == 'undefined' ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please select Account Type";
				return false;
			}else if($scope.ecs_form_ip[4] == '' || $scope.ecs_form_ip[4] == undefined || $scope.ecs_form_ip[4] == 'undefined' ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter IFSC Code";
				return false;
			}else if($scope.ecs_form_hid[5] == '' || $scope.ecs_form_hid[5] == undefined || $scope.ecs_form_hid[5] == 'undefined' ) {
                $rootScope.showCommonPop = 1;
                $rootScope.commonTitle = "Genio";
                $rootScope.commonShowContent = "Please enter MICR Code";
                return false;
            }else if($scope.ecs_form_ip[1].length  < 3) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Account Holder's Name should be at least three characters";
				return false;
			}else if($scope.ecs_form_ip[2].length  < 2 || $scope.ecs_form_ip[2].length  > 24 ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Account Number must be greater than 2 and less than 24";
				return false;
			}else if($scope.ecs_form_ip[1] !=  $scope.ecs_form_hid[1]) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Mismatch in Account Holder's name";
				return false;
			}else if($scope.ecs_form_ip[2] !=  $scope.ecs_form_hid[2]) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Mismatch in Account Number";
				return false;
			}else if($scope.ecs_form_ip[4] !=  $scope.ecs_form_hid[4]) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Mismatch in IFSC code";
				return false;
			}else if($scope.bank_detail[1] ==  '' || $scope.bank_detail[1] == undefined || $scope.bank_detail[1] == 'undefined' ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter bank name";
				return false;
			}else if($scope.bank_detail[2] ==  '' || $scope.bank_detail[2] == undefined || $scope.bank_detail[2] == 'undefined') {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter Bank City Name";
				return false;
			}else if($scope.bank_detail[3] ==  '' || $scope.bank_detail[3] == undefined || $scope.bank_detail[3] == 'undefined' ) {
				$rootScope.showCommonPop = '1';
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please enter Bank Branch Name";
				return false;
			}
		
			if($scope.acc_ecs_flag == 3 || $scope.acc_ecs_flag == 4) {
				APIServices.save_bankdetials(returnState.paridInfo,returnState.ver,$scope.ecs_form_ip[4],$scope.ecs_form_ip[2],$scope.ecs_form_ip[1],$scope.ecs_form_ip[3],$scope.bank_detail[1],$scope.bank_detail[2],$scope.bank_detail[3],$scope.ecs_form_hid[5]).success(function(response) {
						if(response.error.code == 0) {
							APIServices.transferaccdetailstomain(returnState.paridInfo,returnState.ver).success(function(response) {
								if(response.error.code == 0) {
									APIServices.sendjdpaylink(returnState.paridInfo).success(function(response) {
										if(response.error.code == 0)	{
											if($scope.acc_ecs_flag == 4) {
												$state.go('appHome.demopage',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler,demo_flg:3});
											}else{
												$window.location.href = $scope.businessUrl;
											}
										}

									});
								}

							});
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = response.error.msg;
						}

				});
			}else {
				APIServices.save_bankdetials(returnState.paridInfo,returnState.ver,$scope.ecs_form_ip[4],$scope.ecs_form_ip[2],$scope.ecs_form_ip[1],$scope.ecs_form_ip[3],$scope.bank_detail[1],$scope.bank_detail[2],$scope.bank_detail[3],$scope.ecs_form_hid[5]).success(function(response) {
					if(response.error.code == 0) {
						//~ if(returnState.ecsflg == 1) {
							//~ $state.go('appHome.paymentsummary',{parid:returnState.paridInfo,ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
						//~ }else { 
							$window.location.href  = $scope.domainUrl;					
						//~ }
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
					}
				});
			}
		}
		
		
		$scope.skipdetails = function(){
			//~ if(returnState.ecsflg == 1) {
				//~ $state.go('appHome.paymentsummary',{parid:returnState.paridInfo,ver:$rootScope.budgetVersion,page:$rootScope.extraHandler});
			//~ }else { 
				$window.location.href  = $scope.domainUrl;					
			//~ }
		}
		
		setTimeout(
			function() {
				$('html, body').animate({scrollTop: "2px" }, 500);
			},500
		);
		
			
	});
	
	tmeModuleApp.controller('omnidomainregController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_inn').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
	   $('.own_borBtm').css('border-bottom','2px solid orange');
		$scope.omniregip = {};
		$scope.omniregip[2] = '';
		$scope.omniregip[3] = 'select';
		$scope.show_online_msg = true;
		$scope.domain_type= "myown";
        $('#own_rdo').addClass('rdo-btn-chk');
        $('.own_borBtm').css('border-bottom','2px solid orange');
        $('.new_borBtm').css('border-bottom','');
		$scope.domain_option = false;
		$scope.Other_exten = false;
		$scope.available = false;
		$scope.domain_cb = {};
		$scope.popular_domain = {};
		$scope.other_domain = {};
		$scope.domain_cb[".com"] = true;
		$scope.omniregip['email_id'] = '';
		$scope.omniregip['mob_no'] = '';
		$scope.omniregip['email_id_confirm'] = '';
		$scope.omniregip['mob_no_confirm'] = '';
		$scope.omniregip['contact_person_confirm'] = '';
		$scope.omniregip['contact_person'] = '';
		$scope.omniregip['contact_person_no'] = 'Mr';
		$scope.show_email = 0;
		$scope.show_mobile = 0;
		$scope.show_contactperson = 0;
		$scope.show_available_message = false;
		/**************************************************************************/
		$scope.domain_registerName			=	[];	
		$scope.domain_registerName[0]		=	'';	
		$scope.domain_uerid					=	[];	
		$scope.domain_uerid[0]				=	'';	
		$scope.domain_pass					=	[];	
		$scope.domain_pass[0]				=	'';	
		$scope.domaincheckErr				=	0;
		 $scope.domain_registrant_email      =   []; 
        $scope.domain_registrant_email[0]   =   ''; 
		$scope.omni_domain_option			=	[];
		$scope.omni_domain_option[0]		=	'';
        $scope.action_flag_forget          =   0;
        $scope.action_flag_forgetstatus   =   'Entered';
		/**************************************************************************/

		$scope.switch_option = function(type) { // domain changes
            if(type == "own") {
                $('#own_rdo').addClass('rdo-btn-chk');
                $('.own_borBtm').css('border-bottom','2px solid orange');
                $('.new_borBtm').css('border-bottom','');
                $scope.domain_type= "own";
            }else if(type == "new") {
                $('#own_rdo').removeClass('rdo-btn-chk');
                $('#new_rdo').addClass('rdo-btn-chk');
                $('.new_borBtm').css('border-bottom','2px solid orange');
                $('.own_borBtm').css('border-bottom','');
                $scope.domain_type= "new";
            }else if(type == "myown") {
                $('#own_rdo').removeClass('rdo-btn-chk');
                $('#new_rdo').addClass('rdo-btn-chk');
                $('.own_borBtm').css('border-bottom','2px solid orange');
                $('.new_borBtm').css('border-bottom','');
                $scope.domain_type= "myown";
            }
        }
        
        $scope.switch_skipdomain = function(type) { // domain changes
            if(type == "own") {
                $('#new_rdo').removeClass('rdo-btn-chk');
                $('#own_rdo').addClass('rdo-btn-chk');
                $('#skip2_rdo').removeClass('rdo-btn-chk');
                $('#skip1_rdo').removeClass('rdo-btn-chk');
                $scope.omni_domain_option[0]	= "Enter existing domain details right now";
                $scope.domain_type= "own";
            }else{
				$('#new_rdo').removeClass('rdo-btn-chk');
                $('#own_rdo').removeClass('rdo-btn-chk');
                $('#skip2_rdo').removeClass('rdo-btn-chk');
                $('#skip1_rdo').removeClass('rdo-btn-chk');
                $('#'+type).addClass('rdo-btn-chk');
                if(type == 'skip1_rdo'){
					$scope.omni_domain_option[0]	= "I will provide my existing domain detials later";
				}else{
					$scope.omni_domain_option[0]	= "I want to book other domains like .net,co.in etc. I will book it & give you details later.";
				}
				$scope.domain_type= "myown";
            }
        }
		
		$scope.selected_camapign = $cookieStore.get('selected_option');
		$scope.combo = 0;
		if($scope.selected_camapign == "combo1") {
			$scope.combo = 1;
		}else if($scope.selected_camapign == "combo2") {
			$scope.omni_type = 5;
		}else if($scope.selected_camapign == "omni1") {
			$scope.omni_type = 1;
		}else if($scope.selected_camapign == "omni2") {
			$scope.omni_type = 2;
		}else if($scope.selected_camapign == "omniultima") {
			$scope.omni_type = 3;
		}else if($scope.selected_camapign == "omnisupreme") {
			$scope.omni_type = 4;
		}else if($scope.selected_camapign == "omni7") {
			$scope.omni_type = 7;
		}
		
		$scope.selected_payment_type = $cookieStore.get('payment_type');
		if($scope.selected_payment_type == 'ecs') {
			$scope.ecs_flag = 1;
		}else if($scope.selected_payment_type == 'upfront') {
			$scope.ecs_flag = 0;
		}
		
		
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		 $scope.getEmailId   =   '';
		APIServices.checkemail($stateParams.parid,$stateParams.ver,$scope.other_parameter).success(function(response) {
			if(response.data.email == '' || response.data.email == null){
				$scope.show_email = 1;
			}else{
                // $scope.domain_registrant_email[0]   =   response.data.email;
                // $scope.getEmailId                   =   response.data.email;
            }
			if(response.data.mobile == '' || response.data.mobile == null){
				$scope.show_mobile = 1;
			}
			if(response.data.contact_person == '' || response.data.contact_person == null){
				$scope.show_contactperson = 1;
			}
		});
		
		APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				$scope.omniregip[1] = response.error.result.website;
			}else{
				$scope.omniregip[1] = '';
			}
			
		});
	
		$scope.custom_73 = '';
		$scope.custom_72 = '';
		$scope.omni_monthly = '';
		APIServices.get_discount_info($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				if(response.error.data['73'] != undefined && response.error.data['73'] != '' ) {
					$scope.custom_73 = response.error.data['73'];
				}else {
					$scope.custom_73 = '';
				}
				
				if(response.error.data['72'] != undefined && response.error.data['72'] != '') {
					$scope.custom_72 = response.error.data['72'];
				}else {
					$scope.custom_72 = '';
				}
				
				if($scope.ecs_flag == 1) {
					$scope.omni_monthly = $scope.custom_73;
				}
			}
		});
				
		
		APIServices.getpricelist($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				var i=0;
				angular.forEach(response.result.price,function(value,key) {
					if(i< 6) {
						$scope.popular_domain[key] = value;
					}else {
						$scope.other_domain[key] = value;
					}
					i++;
				});
			}
		});
	$scope.change_domain = function() {
			$scope.domain_type= "new";
			$scope.show_available_message = false;
			
			var w = $(window).height();
			var d = $('.mainContain_inn').height()
			if(d < w){
				$('.mainContain_innr').css({'height':$(window).height() - 270});
			}else{
				$('.mainContain_innr').css({'height':'auto'});
			}
		
			
		}
		
		
		$scope.show_domainoption = function() {
			if($scope.domain_option == false) {
				$scope.domain_option = true;
			}else{
				$scope.domain_option = false;
			}
		}
		
		$scope.show_more = function(){
			if($scope.Other_exten == false) {
				$scope.Other_exten = true;
			}else{
				$scope.Other_exten = false;
			}
		}
		
		$scope.check_availability = function(url,ev) {
			$scope.check_alv = [];
			var domain_reg = /^([A-Za-z0-9])+[A-Za-z0-9-]+([A-Za-z0-9])$/;
			
			url = url.toLowerCase();
			$scope.show_available_message = false;
			$scope.domain_option = false;
			$scope.show_error_msg = '';
			var tlds = '';
			
			if(url != '' && url != undefined ) {
				
				
				angular.forEach($scope.domain_cb,function(value,key) {
					if(value) {
							 tlds += key.slice(1)+',';
					}
				});
				
				var domain_arr	=	url.split('.');
				
				if(domain_arr[0].toLowerCase() == "www") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please type with out www";
					return false;
				}
				
				if(tlds == '') {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please select atleast one domain type";
					return false;
				}
				
				if(domain_arr.length > 1) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please select domain type from select box";
					return false;
				}
				 
				if(domain_reg.test(domain_arr[0]) == false) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Invalid character in domain name or domain name is less than 3 characters";
					return false;
				}
						
				
				APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,url,tlds).success(function(response) {   
					$scope.available_list = response;
					$scope.domain_type = "available";
					$scope.show_available_message = true;
					 $scope.avail_no = 0;
					angular.forEach(response,function(value,key) {
						if(value.error.code == 0) {
							 $scope.avail_no++;
						}
					});
					
					setTimeout(
						function() {
							
							$('.mainContain_innr').css({'height':$(".cnt-wrp-in").height() + 350});
			
						},300
					);
					
				});
			}else {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Please enter the domain name";
				$scope.show_available_message == false;
			}
			
		}
		
		$scope.clear_flag = function() {
			$scope.show_available_message = false
			$scope.show_error_msg ='';
		}

		$rootScope.forget_link_proceed  =   function(){
            $scope.action_flag_forget          =   1;
            $scope.action_flag_forgetstatus   =   'Not Entered';
            $rootScope.showCommonPop = 0;
            var desired_url     = ("http://"+$scope.omniregip[1]).toLowerCase(); // http://www.aaa.in 0 - http://www , 1 - aaa
                    var checkAvailURl   = desired_url.split('.')[1];
                    var desired_urlArr  = desired_url.split('.');
                    $scope.tdls =   '';
                    angular.forEach(desired_urlArr, function(value,key){
                        if(key >=2)
                            $scope.tdls +=value+".";
                    });
                    $scope.tdls = $scope.tdls.slice(0,-1);
                    //console.log($scope.tdls);
                    if($scope.show_email == 1 || $scope.show_mobile == 1 || $scope.show_contactperson == 1 ){ 
						APIServices.savedetails($scope.omniregip['mob_no'],$scope.omniregip['email_id'],$scope.omniregip['contact_person_no']+" "+$scope.omniregip['contact_person'],$stateParams.parid,$stateParams.ver,$scope.omniregip['mob_search'],$scope.omniregip['mob_feedback'],$scope.omniregip['emailid_search'],$scope.omniregip['emailid_feedback']).success(function(response) {
							if(response.error.code == 1 && response.error.msg == "Reg Fees Present! Please go to bform to add the number" ) {
								$rootScope.err_msg = "Reg Fees Present!";
								$mdDialog.show({
									controller: emailredirectDialogController,
									templateUrl: 'partials/dialogemailredirect.html',
									parent: angular.element(document.body),
									targetEvent: ev,
								})
								return false;
							}else if(response.error.code == 1){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = "Details not saved,Please try again";
								return false;
							}else{ // add new columns here 
								APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,checkAvailURl,$scope.tdls).success(function(response) {
									$scope.temp_domainChk = checkAvailURl+'.'+$scope.tdls;
									if(response[$scope.temp_domainChk].error.code	==	0){
										$scope.domaincheckErr	 =	1;
										$timeout(function(){
										   $scope.domaincheckErr = 0;
									    }, 10000);
									}else{
										APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,desired_url,"","",'not applicable',1,$scope.combo,$scope.domain_registerName[0],$scope.domain_uerid[0],$scope.domain_pass[0],$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus,$scope.omni_domain_option[0]).success(function(response) {
											if(response.error.code == 0)  {
												APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
													if(response.error.code == 0) {
														//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
														//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
														$state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
													}else {
														$rootScope.showCommonPop = 1;
														$rootScope.commonTitle = "Genio";
														$rootScope.commonShowContent = "Please Try Again";
													}
												});
											}else {
												$rootScope.showCommonPop = 1;
												$rootScope.commonTitle = "Genio";
												$rootScope.commonShowContent = response.error.msg;
												return false;
											}
										});
									}
								});
							}
						});
					}else { // add new columns here
						APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,checkAvailURl,$scope.tdls).success(function(response) {
							$scope.temp_domainChk = checkAvailURl+'.'+$scope.tdls;
							if(response[$scope.temp_domainChk].error.code	==	0){
										$scope.domaincheckErr	 =	1;
										$timeout(function(){
										   $scope.domaincheckErr = 0;
									    }, 10000);
							}else{
								APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,desired_url,"","",'not applicable',1,$scope.combo,$scope.domain_registerName[0],$scope.domain_uerid[0],$scope.domain_pass[0],$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus,$scope.omni_domain_option[0]).success(function(response) {
									if(response.error.code == 0)  {
										APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
											if(response.error.code == 0) {
												//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});					
												$state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
											}else {
												$rootScope.showCommonPop = 1;
												$rootScope.commonTitle = "Genio";
												$rootScope.commonShowContent = "Please Try Again";
											}
										});
									}else {
										$rootScope.showCommonPop = 1;
										$rootScope.commonTitle = "Genio";
										$rootScope.commonShowContent = response.error.msg;
										return false;
									}
								});
							}
						});
					}
            // $state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
        }
		
		
		$scope.hide_data = function(id){
			$("#"+id).prop( "type", "password" );
		}
		
		$scope.show_data = function(id){
			$("#"+id).prop( "type", "text" );
			$scope.omniregip[id+'_confirm'] = '';
		}
		 $scope.forget_link_Output    =   {};
        $scope.forget_link_Output[0]    =   '';
        $rootScope.check_forget_select    =   0;
        $('.forget_user_pass_a').show();
        $scope.getforgetLink    =   function(){
            if($rootScope.check_forget_select    == 0 && $scope.domain_registerName[0].length != 0){
                $rootScope.showCommonPop = 1;
                $rootScope.commonTitle = "Genio";
                $rootScope.commonShowContent = "Please Select From Autosuggest";
                return false;
            }
        }
        $scope.getUserPass_link     =       function(){
            $window.open($scope.forget_link_Output[0], '_blank');    
        }
		$scope.goto_domain_options = function(ev) {
			var i	=	0;
			if($scope.domain_type == "myown"){//
				$('.myown_rad').each(function(key,val){
					if($(this).hasClass('rdo-btn-chk')){
						i++;
					}
				});
				if(i == 0){
					$rootScope.showCommonPop = 1;
                    $rootScope.commonTitle = "Genio";
                    $rootScope.commonShowContent = "Please select any one option to proceed!.";
                    return false;
				}
			}
			if($scope.omni_domain_option[0]!='' && $scope.omni_domain_option[0]!='Enter existing domain details right now'){
				$scope.addjdomniskip(); return false;
			}
			var domain_reg = /^([A-Za-z0-9])+[A-Za-z0-9-]+([A-Za-z0-9])$/;
			
			if($stateParams.page == 'jda') {
				$scope.other_parameter = 1;
			}else {
				$scope.other_parameter = 0;
			}
			
			if($scope.domain_type == "new" ) {
				if($scope.show_available_message == false){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "please check Availability";
					return false;
				}
			}
			
			var email_reg = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
			var mob_reg = /^[789]\d{9}$/;
			
			if(($scope.omniregip['email_id'] == '' || $scope.omniregip['email_id'] == undefined) && $scope.show_email == 1 ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please enter email id";
				return false;
			}else if(email_reg.test($scope.omniregip['email_id']) == false && $scope.show_email == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please enter proper emailid";
				return false;
			}
			
			if(($scope.omniregip['email_id_confirm'] == '' || $scope.omniregip['email_id_confirm'] == undefined) && $scope.show_email == 1 ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please Confirm  Email Id";
				return false;
			}else if($scope.omniregip['email_id_confirm'].toLowerCase() != $scope.omniregip['email_id'].toLowerCase() && $scope.show_email == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Email ID is not matching";
				$scope.omniregip['email_id_confirm'] = '';
				return false;
			}
			
			
			if(($scope.omniregip['mob_no'] == '' || $scope.omniregip['mob_no'] == undefined) && $scope.show_mobile == 1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please enter mobile number";
				return false;
			}else if(mob_reg.test($scope.omniregip['mob_no']) == false && $scope.show_mobile == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please enter proper mobile number";
				return false;
			}
			
			if(($scope.omniregip['mob_no_confirm'] == '' || $scope.omniregip['mob_no_confirm'] == undefined) && $scope.show_mobile == 1 ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please Confirm  Mobile Number";
				return false;
			}else if($scope.omniregip['mob_no_confirm'].toLowerCase() != $scope.omniregip['mob_no'].toLowerCase()  && $scope.show_mobile == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Mobile Number is not matching";
				$scope.omniregip['mob_no_confirm'] = '';
				return false;
			}
			
			
			if(($scope.omniregip['contact_person'] == ''  || $scope.omniregip['contact_person'] == undefined) && $scope.show_contactperson == 1 ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please enter contact person";
				return false;
			}
			
			if(($scope.omniregip['contact_person_confirm'] == '' || $scope.omniregip['contact_person_confirm'] == undefined) && $scope.show_contactperson == 1 ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please Confirm Contact Person";
				return false;
			}else if($scope.omniregip['contact_person_confirm'].toLowerCase() != $scope.omniregip['contact_person'].toLowerCase()  && $scope.show_contactperson == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Contact Person is not matching";
				$scope.omniregip['contact_person_confirm'] = '';
				return false;
			}
			
				
			if($scope.domain_type == "available" ) {
				if($scope.show_available_message == false){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "please check Availability";
					return false;
				}else if($(".avbldomn-info").find('input[name="domain_avl[]"]:checked').length == 0){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please select one domain";
					return false;
				}else if($(".avbldomn-info").find('input[name="domain_avl[]"]:checked').length > 1){
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please select only one domain";
					return false;
				}else {
					$cookieStore.put('req_omni_domain',$(".avbldomn-info").find('input[name="domain_avl[]"]:checked').val());
					if($scope.show_email == 1 || $scope.show_mobile == 1 || $scope.show_contactperson == 1 ){ 
						APIServices.savedetails($scope.omniregip['mob_no'],$scope.omniregip['email_id'],$scope.omniregip['contact_person_no']+" "+$scope.omniregip['contact_person'],$stateParams.parid,$stateParams.ver,$scope.omniregip['mob_search'],$scope.omniregip['mob_feedback'],$scope.omniregip['emailid_search'],$scope.omniregip['emailid_feedback']).success(function(response) {
							if(response.error.code == 1 && response.error.msg == "Reg Fees Present! Please go to bform to add the number" ) {
								$rootScope.err_msg = "Reg Fees Present!";
								$mdDialog.show({
									controller: emailredirectDialogController,
									templateUrl: 'partials/dialogemailredirect.html',
									parent: angular.element(document.body),
									targetEvent: ev,
								})
								return false;
							}else if(response.error.code == 1){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = "Details not saved,Please try again";
								return false;
							}else{
								$cookieStore.put('req_omni_domain',$(".avbldomn-info").find('input[name="domain_avl[]"]:checked').val());
								$state.go('appHome.omnidomainopt',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
							}
						});
					}else{
						$cookieStore.put('req_omni_domain',$(".avbldomn-info").find('input[name="domain_avl[]"]:checked').val());
						$state.go('appHome.omnidomainopt',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}
				}
			}else if($scope.domain_type == "own") {
				if($scope.omniregip[1] == ''  || $scope.omniregip[1] == undefined) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please enter your domain name";
					return false;
				}else {
					$scope.flg = '';
					var domain_arr	=	$scope.omniregip[1].split('.');		
					$scope.flg      =   $scope.webSiteValidation($scope.omniregip[1]); 			
					if($scope.flg!=true){
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Alert!!";
						$rootScope.commonShowContent = "Please enter Proper Domain name";
						$scope.omniregip[1] = '';
						return false;
					}
					if(domain_arr.length == 1 || domain_arr[1] == "") {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "Please enter with domain type";
						return false;
					}
					if(domain_arr.length > 3){
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Alert!!";
						$rootScope.commonShowContent = "Please enter proper domain name";
						$scope.omniregip[1] = '';
						return false;
					}
					if(domain_arr[0].toLowerCase() != "www") {
						if(domain_reg.test(domain_arr[0]) == false) {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'Alert!!';
							$rootScope.commonShowContent = "Invalid character in domain name or domain name is less than 3 characters";
							return false;
                        }
						if($scope.domain_registerName[0] == "" || $scope.domain_registerName[0] == "Select"){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = "Please select Domain regiter name";
								return false;
						}
						 if($scope.domain_registrant_email[0]    ==  ""){
                            $rootScope.showCommonPop = 1;
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please Enter Domain Registrant Email ID";
                            return false;
                        }
                        if(email_reg.test($scope.domain_registrant_email[0]) == false) {
                            $rootScope.showCommonPop = 1;
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please Enter proper Domain registrant EmailID";
                            return false;
                        }
                         if($scope.domain_uerid[0]   ==  "" && $scope.domain_pass[0] == ""){
                            $scope.omniregip[1] = "www."+$scope.omniregip[1];
                            $rootScope.showCommonPop = 'forget_link';
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please enter Domain Username and Domain password.";
                            $rootScope.commonShowContent1 = "Without this you will not be eligible for Incentive on this contract";
                            return false;
                        }
						$scope.omniregip[1] = "www."+$scope.omniregip[1];
					}else {
						if(domain_reg.test(domain_arr[1]) == false) {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'Alert!!';
							$rootScope.commonShowContent = "Invalid character in domain name or domain name is less than 3 characters";
							return false;
                        }
                        if($scope.domain_registerName[0] == "" || $scope.domain_registerName[0] == "Select"){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = "Please select Domain regiter name";
								return false;
						}
						if($scope.domain_registrant_email[0]    ==  ""){
                            $rootScope.showCommonPop = 1;
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please Enter Domain Registrant Email ID";
                            return false;
                        }
                        if(email_reg.test($scope.domain_registrant_email[0]) == false) {
                            $rootScope.showCommonPop = 1;
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please Enter proper Domain registrant EmailID";
                            return false;
                        }
                        if($scope.domain_uerid[0]   ==  "" && $scope.domain_pass[0] == ""){
                            $rootScope.showCommonPop = 'forget_link';
                            $rootScope.commonTitle = "Genio";
                            $rootScope.commonShowContent = "Please enter Domain Username and Domain password.";
                            $rootScope.commonShowContent1 = "Without this you will not be eligible for Incentive on this contract";
                            return false;
                        }
                    }
                    var desired_url 	= ("http://"+$scope.omniregip[1]).toLowerCase(); // http://www.aaa.in 0 - http://www , 1 - aaa
                    var checkAvailURl	= desired_url.split('.')[1];
                    var desired_urlArr 	= desired_url.split('.');
                    $scope.tdls	=	'';
                    angular.forEach(desired_urlArr, function(value,key){
						if(key >=2)
							$scope.tdls +=value+".";
					});
					$scope.tdls = $scope.tdls.slice(0,-1);
					//console.log($scope.tdls);
                    if($scope.show_email == 1 || $scope.show_mobile == 1 || $scope.show_contactperson == 1 ){ 
						APIServices.savedetails($scope.omniregip['mob_no'],$scope.omniregip['email_id'],$scope.omniregip['contact_person_no']+" "+$scope.omniregip['contact_person'],$stateParams.parid,$stateParams.ver,$scope.omniregip['mob_search'],$scope.omniregip['mob_feedback'],$scope.omniregip['emailid_search'],$scope.omniregip['emailid_feedback']).success(function(response) {
							if(response.error.code == 1 && response.error.msg == "Reg Fees Present! Please go to bform to add the number" ) {
								$rootScope.err_msg = "Reg Fees Present!";
								$mdDialog.show({
									controller: emailredirectDialogController,
									templateUrl: 'partials/dialogemailredirect.html',
									parent: angular.element(document.body),
									targetEvent: ev,
								})
								return false;
							}else if(response.error.code == 1){
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Genio";
								$rootScope.commonShowContent = "Details not saved,Please try again";
								return false;
							}else{ // add new columns here 
								APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,checkAvailURl,$scope.tdls).success(function(response) {
									$scope.temp_domainChk = checkAvailURl+'.'+$scope.tdls;
									if(response[$scope.temp_domainChk].error.code	==	0){
										$scope.domaincheckErr	 =	1;
										$timeout(function(){
										   $scope.domaincheckErr = 0;
									    }, 10000);
									}else{
										APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,desired_url,"","",'not applicable',1,$scope.combo,$scope.domain_registerName[0],$scope.domain_uerid[0],$scope.domain_pass[0],$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus,$scope.omni_domain_option[0]).success(function(response) {
											if(response.error.code == 0)  {
												APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
													if(response.error.code == 0) {
														//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
														//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
														$state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
													}else {
														$rootScope.showCommonPop = 1;
														$rootScope.commonTitle = "Genio";
														$rootScope.commonShowContent = "Please Try Again";
													}
												});
											}else {
												$rootScope.showCommonPop = 1;
												$rootScope.commonTitle = "Genio";
												$rootScope.commonShowContent = response.error.msg;
												return false;
											}
										});
									}
								});
							}
						});
					}else { // add new columns here
						APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,checkAvailURl,$scope.tdls).success(function(response) {
							$scope.temp_domainChk = checkAvailURl+'.'+$scope.tdls;
							if(response[$scope.temp_domainChk].error.code	==	0){
										$scope.domaincheckErr	 =	1;
										$timeout(function(){
										   $scope.domaincheckErr = 0;
									    }, 10000);
							}else{
								APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,desired_url,"","",'not applicable',1,$scope.combo,$scope.domain_registerName[0],$scope.domain_uerid[0],$scope.domain_pass[0],$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus,$scope.omni_domain_option[0]).success(function(response) {
									if(response.error.code == 0)  {
										APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
											if(response.error.code == 0) {
												//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});					
												$state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
											}else {
												$rootScope.showCommonPop = 1;
												$rootScope.commonTitle = "Genio";
												$rootScope.commonShowContent = "Please Try Again";
											}
										});
									}else {
										$rootScope.showCommonPop = 1;
										$rootScope.commonTitle = "Genio";
										$rootScope.commonShowContent = response.error.msg;
										return false;
									}
								});
							}
						});
					}
				}
				
			}
			
		}
		$scope.webSiteValidation = function(str){ 
            if(str != '' && str != undefined){               								
				str_arr = str.split("."); 
				var arr = new Array('.com','.net','.org','.biz','.coop','.info','.museum','.name','.pro','.edu','.gov','.int','.mil','.ac','.ad','.ae','.af','.ag',
								'.ai','.al','.am','.an','.ao','.aq','.ar','.as','.at','.au','.aw','.az','.ba','.bb','.bd','.be','.bf','.bg','.bh','.bi','.bj','.bm',
								'.bn','.bo','.br','.bs','.bt','.bv','.bw','.by','.bz','.ca','.cc','.cd','.cf','.cg','.ch','.ci','.ck','.cl','.cm','.cn','.co','.cr',
								'.cu','.cv','.cx','.cy','.cz','.de','.dj','.dk','.dm','.do','.dz','.ec','.ee','.eg','.eh','.er','.es','.et','.fi','.fj','.fk','.fm',
								'.fo','.fr','.ga','.gd','.ge','.gf','.gg','.gh','.gi','.gl','.gm','.gn','.gp','.gq','.gr','.gs','.gt','.gu','.gv','.gy','.hk','.hm',
								'.hn','.hr','.ht','.hu','.id','.ie','.il','.im','.in','.io','.iq','.ir','.is','.it','.je','.jm','.jo','.jp','.ke','.kg','.kh','.ki',
								'.km','.kn','.kp','.kr','.kw','.ky','.kz','.la','.lb','.lc','.li','.lk','.lr','.ls','.lt','.lu','.lv','.ly','.ma','.mc','.md','.mg',
								'.mh','.mk','.ml','.mm','.mn','.mo','.mp','.mq','.mr','.ms','.mt','.mu','.mv','.mw','.mx','.my','.mz','.na','.nc','.ne','.nf','.ng',
								'.ni','.nl','.no','.np','.nr','.nu','.nz','.om','.pa','.pe','.pf','.pg','.ph','.pk','.pl','.pm','.pn','.pr','.ps','.pt','.pw','.py',
								'.qa','.re','.ro','.rw','.ru','.sa','.sb','.sc','.sd','.se','.sg','.sh','.si','.sj','.sk','.sl','.sm','.sn','.so','.sr','.st','.sv',
								'.sy','.sz','.tc','.td','.tf','.tg','.th','.tj','.tk','.tm','.tn','.to','.tp','.tr','.tt','.tv','.tw','.tz','.ua','.ug','.uk','.um',
								'.us','.uy','.uz','.va','.vc','.ve','.vg','.vi','.vn','.vu','.ws','.wf','.ye','.yt','.yu','.za','.zm','.zw','.shop','.store','.online','.site','.tech','.space','.global','.xyz','.news','.club','.life','.rocks','.design','.website','.guru','.photography','.today','.solutions','.media','.ads','.agency','.associates','.booking','.business','.career','.careers','.center','.ceo','.company','.consulting','.ecom','.enterprises','.farm','.forsale','.forum','.gives','.gle','.gmbh','.haus','.immobilien','.inc','.institute','.insure','.lifeinsurance','.llc','.llp','.ltd','.ltda','.management','.marketing','.moda','.mortgage','.new','.ngo','.partners','.press','.pub','.rehab','.reviews','.rip','.sale','.sarl','.services','.software','.srl','.studio','.trade','.trading','.ventures','.wiki','.xin','.reisen','.reise','.pw','.auction','.bargains','.bid','.buy','.cab','.capital','.charity','.cheap','.claims','.compare','.coupon','.coupons','.deal','.dealer','.deals','.delivery','.discount','.equipment','.exchange','.flights','.free','.furniture','.gifts','.gripe','.grocery','.jewelry','.kaufen','.kitchen','.lotto','.market','.parts','.party','.photos','.pictures','.plumbing','.promo','.qpon','.racing','.repair','.rsvp','.salon','.save','.seat','.shoes','.shopping','.silk','.spa','.supplies','.supply','.taxi','.tickets','.tienda','.tires','.tools','.tour','.tours','.toys','.vacations','.video','.voyage','.watch','.watches','.academy','.college','.courses','.degree','.education','.ged','.mba','.phd','.prof','.scholarships','.school','.schule','.science','.shiksha','.study','.training','.translations','.university','.bar','.beer','.cafe','.catering','.cityeats','.coffee','.cooking','.eat','.food','.menu','.organic','.pizza','.recipes','.rest','.restaurant','.review','.soy','.tips','.wine','.vin','.vodka','.band','.boutique','.build','.buzz','.codes','.condos','.cool','.cruises','.dance','.dating','.democrat','.events','.expert','.exposed','.florist','.foundation','.fun','.fyi','.here','.holiday','.house','.international','.limited','.luxury','.maison','.memorial','.ninja','.one','.onl','.pink','.plus','.Pro','.productions','.properties','.red','.rentals','.rodeo','.security','.social','.solar','.viajes','.villas','.wtf','.works','.zone','.country','.limo','.moe','.Protection','.work','.Tel','.abudhabi','.africa','.alsace','.amsterdam','.aquitaine','.arab','.barcelona','.bayern','.berlin','.boston','.broadway','.brussels','.budapest','.bzh','.capetown','.city','.cologne','.corsica','.cymru','.desi','.doha','.dubai','.durban','.earth','.eus','.gent','.hamburg','.helsinki','.irish','.ist','.istanbul','.joburg','.kiwi','.koeln','.kyoto','.land','.london','.madrid','.melbourne','.miami','.moscow','.nagoya','.nrw','.nyc','.okinawa','.osaka','.paris','.persiangulf','.place','.quebec','.rio','.roma','.ryukyu','.saarland','.scot','.shia','.stockholm','.stream','.swiss','.sydney','.taipei','.thai','.tirol','.tokyo','.town','.vegas','.vlaanderen','.wales','.wanggou','.wien','.world','.yokohama','.zuerich','.airforce','.army','.navy','.republican','.clinic','.dental','.dentist','.docs','.doctor','.health','.healthcare','.hospital','.med','.medical','.pharmacy','.physio','.support','.surgery','.auto','.autos','.bio','.boats','.builders','.cruise','.car','.cars','.casino','.cleaning','.clothing','.construction','.diamonds','.eco','.energy','.glass','.green','.hoteis','.hotel','.hoteles','.hotels','.industries','.ink','.insurance','.lighting','.motorcycles','.active','.actor','.adult','.baby','.beauty','.beknown','.best','.bet','.bible','.bingo','.black','.blue','.camp','.cards','.care','.catholic','.church','.community','.contact','.contractors','.dad','.date','.diy','.dog','.express','.faith','.family','.fan','.fans','.fashion','.futbol','.garden','.gay','.giving','.group','.guide','.hair','.halal','.horse','.imamat','.indians','.islam','.ismaili','.jetzt','.kid','.kids','.kim','.kinder','.lat','.latino','.lgbt','.lifestyle','.live','.living','.love','.luxe','.men','.pet','.pets','.poker','.porn','.ren','.retirement','.sex','.singles','.style','.top','.uno','.vet','.vip','.vision','.vote','.voting','.voto','.wang','.wed','.wedding','.art','.arte','.audible','.book','.feedback','.film','.gallery','.mov','.movie','.movistar','.music','.radio','.show','.sucks','.song','.theater','.theatre','.tunes','.accountant','.accountants','.analytics','.bank','.banque','.broker','.cash','.cashbackbonus','.cfd','.cpa','.credit','.creditcard','.estate','.finance','.financial','.financialaid','.fund','.gold','.gratis','.holdings','.investments','.ira','.loan','.loans','.markets','.money','.mutual','.mutualfunds','.pay','.prime','.reit','.rich','.tax','.trust','.yun','.archi','.architect','.attorney','.author','.dds','.engineer','.engineering','.esq','.lawyer','.legal','.apartments','.casa','.case','.immo','.lease','.realestate','.realtor','.rent','.room','.baseball','.basketball','.bike','.coach','.cricket','.fish','.fishing','.fit','.fitness','.football','.games','.golf','.hockey','.mls','.rugby','.run','.ski','.soccer','.sport','.sports','.spreadbetting','.surf','.team','.tennis','.yoga','.app','.blog','.box','.call','.cam','.camera','.chat','.cloud','.computer','.comsec','.bot','.data','.dev','.digital','.direct','.directory','.domains','.dot','.download','.drive','.email','.fail','.graphics','.host','.mail','.map','.mobile','.network','.phone','.report','.search','.secure','.storage','.systems','.technology','.tube','.web','.webcam','.webs','.weibo','.win','.zip');
				if(str_arr.length>2){
					if(str_arr[2]!='' && str_arr[2]!=undefined){
						str_arr[2] = '.'+str_arr[2];
					}
					if((str_arr[2]!='' && str_arr[2]!=undefined && $.inArray(str_arr[2],arr)=== -1)){ //doesnt exist
						return false;
					}else{
						return true;
					}
				}
				if(str_arr.length>1 && str_arr.length <= 2){
					if(str_arr[1]!='' && str_arr[1]!=undefined){
						str_arr[1] = '.'+str_arr[1];
					}
					if((str_arr[1]!='' && str_arr[1]!= undefined && $.inArray(str_arr[1],arr)=== -1)){
						return false;
					}else{
						return true;
					}
				}
				if(/^(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:[/?#]\S*)?$/i.test(str)) {
					return true;
				} else {
					return false;
				}
            }
        }	
		$scope.addjdomniskip = function() {	// skip from omni page
			//~ if($scope.omniregip[1]!='')
				var desired_url = ("http://"+$scope.omniregip[1]).toLowerCase();
			//~ else
				//~ var desired_url = '';
           APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,desired_url,"","",'not applicable',1,$scope.combo,$scope.domain_registerName[0],$scope.domain_uerid[0],$scope.domain_pass[0],$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus,$scope.omni_domain_option[0]).success(function(response) { // own_website = 2 -- skip case
				if(response.error.code == 0)  {
					//console.log($scope.custom_72+'----'+$scope.custom_73+'--'+$scope.omni_monthly+'--');
					APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
						//console.log('==adsadsa======>'+JSON.stringify(response));
						if(response.error.code == 0) {
							//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});							
							$state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = "Please Try Again";
						}
					});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = response.error.msg;
					return false;
				}
			});
        }

		function emailredirectDialogController($scope) {
			
			$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
			$scope.businessUrl	=	'../business/bform.php?navbar=yes';
			//Handling for JDA
			if($rootScope.extraHandler == 'jda') {
				var expPathUrl	=	CONSTANTS.pathUrl.split('/');
				var windowLoc	=	window.location.host;
				var splwindowLoc	=	windowLoc.split(".");
				if(splwindowLoc[1] == 'jdsoftware'){
					$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
					$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
				} else {
					$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
					$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
				}
			}
		    $scope.gotoBform    =   function(){
			$mdDialog.hide();
			window.location =   $scope.businessUrl;
		    };
		    $scope.sendBack     =   function(){
			$mdDialog.hide();
			window.location =   $scope.businessUrl;
		    };
			
			$scope.err_msg = $rootScope.err_msg;
		
		}
	 
	
	});
	
	tmeModuleApp.controller('omnidomainoptController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_inn').height()
		
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 150});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
	
	
		$scope.omnioptip = {};
		$scope.omnicheckflg = {};
		$scope.omnicheckmsg = {};
		$scope.domain_name = {};
		$scope.type = {};
		$scope.popular_domain = {};
		$scope.other_domain = {};
		$scope.domain_option = {};
		
		$scope.omnicheckflg[0] = false;
		$scope.omnicheckflg[1] = false;
		$scope.omnicheckflg[2] = false;
		$scope.omnicheckflg[3] = false;
		
		$scope.omnicheckmsg[0] = false;
		$scope.omnicheckmsg[1] = false;
		$scope.omnicheckmsg[2] = false;
		$scope.omnicheckmsg[3] = false;
		
		$scope.domain_option[1] = false;
		$scope.domain_option[2] = false;
		$scope.domain_option[3] = false;
		
		$scope.domain_name[0] = '';
		$scope.domain_name[1] = '';
		$scope.domain_name[2] = '';
		$scope.domain_name[3] = '';
		
		
		$scope.type[1] = '.com';
		$scope.type[2] = '.com';
		$scope.type[3] = '.com';
		
		$scope.selected_camapign = $cookieStore.get('selected_option');
		
		$scope.combo = 0;
		
		if($scope.selected_camapign == "combo1") {
			$scope.combo = 1;
		}else if ($scope.selected_camapign == "combo2") {
			$scope.omni_type = 5;
		}else if ($scope.selected_camapign == "omni1") {
			$scope.omni_type = 1;
		}else if ($scope.selected_camapign == "omni2") {
			$scope.omni_type = 2;
		}else if ($scope.selected_camapign == "omniultima") {
			$scope.omni_type = 3;
		}else if($scope.selected_camapign == "omnisupreme") {
			$scope.omni_type = 4;
		}else if($scope.selected_camapign == "omni7") {
			$scope.omni_type = 7;
		}
		
		$scope.omnioptip[0] = $cookieStore.get('req_omni_domain');
		$scope.domain_name[0] = $cookieStore.get('req_omni_domain');
		$scope.selected_payment_type = $cookieStore.get('payment_type');
		if($scope.selected_payment_type == 'ecs') {
			$scope.ecs_flag = 1;
		}else if($scope.selected_payment_type == 'upfront') {
			$scope.ecs_flag = 0;
		} 	
		
		$scope.custom_73 = '';
		$scope.custom_72 = '';
		$scope.omni_monthly = '';
		APIServices.get_discount_info($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				if(response.error.data['73'] != undefined && response.error.data['73'] != '' ) {
					$scope.custom_73 = response.error.data['73'];
				}else {
					$scope.custom_73 = '';
				}
				
				if(response.error.data['72'] != undefined && response.error.data['72'] != '') {
					$scope.custom_72 = response.error.data['72'];
				}else {
					$scope.custom_72 = '';
				}
				
				if($scope.ecs_flag == 1) {
					$scope.omni_monthly = $scope.custom_73;
				}
			}
		});
		
		
		if($scope.omnioptip[0] != '' && $scope.omnioptip[0] != undefined) {
				$scope.omnicheckflg[0] = true;
			
			
			var domain_arr	=	$scope.domain_name[0].split('.');
			
			$scope.domain_name[0] = domain_arr[0];
			
			if(domain_arr.length == 3) {
				$scope.type[0] = domain_arr[domain_arr.length - 2]+"."+domain_arr[domain_arr.length - 1];
				$scope.type[0] = "."+$scope.type[0];
			}else {
				$scope.type[0] = domain_arr[domain_arr.length - 1];
				$scope.type[0] = "."+$scope.type[0];
			}
						
		}
		
		APIServices.getpricelist($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				var i=0;
				angular.forEach(response.result.price,function(value,key) {
					if(i< 6) {
						$scope.popular_domain[key] = value;
					}else {
						$scope.other_domain[key] = value;
					}
					i++;
				});
			}
		});
		
		$scope.Other_exten = false;
		$scope.show_domainoption = function(index) {
			if(index == 1) {
				$scope.domain_option[2] = false;
				$scope.domain_option[3] = false;
			}else if(index == 2) {
				$scope.domain_option[1] = false;
				$scope.domain_option[3] = false;
			}else if(index == 3) {
				$scope.domain_option[2] = false;
				$scope.domain_option[1] = false;
			}
			 
			if($scope.domain_option[index] == false) {
				$scope.domain_option[index] = true;
			}else{
				$scope.domain_option[index] = false;
			}
		}
		
		$scope.show_more = function(){
			if($scope.Other_exten == false) {
				$scope.Other_exten = true;
			}else{
				$scope.Other_exten = false;
			}
		}
		
		
		$scope.check_availability = function(url,index,ev) {
			
			var domain_reg = /^([A-Za-z0-9])+[A-Za-z0-9-]+([A-Za-z0-9])$/;
			$scope.omnicheckflg[index] = false;
			$scope.omnicheckmsg[index] = '';
			$scope.domain_option[index] = false;
			
			if(url != '' && url != undefined ) {
				
				url = url.toLowerCase();
				var domain_arr	=	url.split('.');
				
				if(index ==0) {
					$scope.domain_name[0] = domain_arr[0];
					if(domain_arr.length == 3) {
						$scope.type[0] = domain_arr[domain_arr.length - 2]+"."+domain_arr[domain_arr.length - 1];
						$scope.type[0] = "."+$scope.type[0];
					}else {
						$scope.type[0] = domain_arr[domain_arr.length - 1];
						$scope.type[0] = "."+$scope.type[0];
					}
					
					if(domain_arr.length == 1) {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'Alert!!';
							$rootScope.commonShowContent = "Please enter domain type";
							return false;
					}
					
				}else {
				
					if(domain_arr.length > 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "Please enter proper domain name";
						return false;
					 }
				 
					$scope.domain_name[index] = url;
				}
				
				if(domain_arr[0].toLowerCase() == "www") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please type with out www";
					return false;
				}
				
				if($scope.domain_name[0] == '' ||  $scope.domain_name[0] == undefined ) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Please enter Desired Domain";
					return false;
				}
				
				if(domain_reg.test(domain_arr[0]) == false) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';
					$rootScope.commonShowContent = "Invalid character in domain name or domain name is less than 3 characters";
					return false;
				}
				
				var desire_url = $scope.domain_name[0]+$scope.type[0];
				var first_url = $scope.domain_name[1]+$scope.type[1];
				var second_url = $scope.domain_name[2]+$scope.type[2];
				//~ var third_url = $scope.domain_name[3]+$scope.type[3];
						
				if(index != 0 )	{
					var prev_index = index-1;
					
					var cur_url = $scope.domain_name[index]+$scope.type[index];
					var prev_url = $scope.domain_name[prev_index]+$scope.type[prev_index];
					
					if(desire_url.toLowerCase() == cur_url.toLowerCase() && index != 0) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "option "+index+" domain name is same as desired domain";
						return false;
					}else if(prev_url.toLowerCase() == cur_url.toLowerCase() && index != 0) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "option "+index+" domain name is same as option"+prev_index;
						return false;
					}
					//~ else if(first_url.toLowerCase() == third_url.toLowerCase()) {
						//~ 
						//~ alert("option 3 domain name is same as option 1");
						//~ return false;
					//~ }
				}else {
					if(first_url.toLowerCase() == desire_url.toLowerCase()) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "desire domain name is same as option 1";
						return false;
					}else if(second_url.toLowerCase() == desire_url.toLowerCase()) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';
						$rootScope.commonShowContent = "desire domain name is same as option 2";
						return false;
					}
					//~ else if(third_url.toLowerCase() == desire_url.toLowerCase()) {
						//~ 
						//~ alert("desire domain name is same as option 3");
						//~ return false;
					//~ }
					
				}
			
				APIServices.checkdomainavailibilty($stateParams.parid,$stateParams.ver,$scope.domain_name[index],$scope.type[index].slice(1)).success(function(response) {   
					$scope.temp_domain = $scope.domain_name[index]+$scope.type[index];
					if(response[$scope.temp_domain].error.code == 0) {
						$scope.omnicheckflg[index] = true;
					}else {
						$scope.omnicheckflg[index] = false;
						$scope.omnicheckmsg[index] = true ;
					}
					
				});
			}else {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Please enter the domain name";
			}
			
		}
		
		$scope.clear_flag = function(index) {
			$scope.omnicheckflg[index] = false;
			$scope.omnicheckmsg[index] = '' ;
		}
		
		$scope.goto_bannerpg = function(ev) {
			if($stateParams.page == 'jda') {
				$scope.other_parameter = 1;
			}else {
				$scope.other_parameter = 0;
			}
			
			if($scope.omnicheckflg[0] == false) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Please check  Availability of Desired Domain";
				return false;
			}else if($scope.omnicheckflg[1] == false) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Please check  Availability of option 1";
				return false;
			}else if($scope.omnicheckflg[2] == false) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';
				$rootScope.commonShowContent = "Please check  Availability of option 2";
				return false;
			}else {
				APIServices.checkemail($stateParams.parid,$stateParams.ver,$scope.other_parameter).success(function(response) {
					if(response.error.code == 0) {
						
						$scope.url1 = ("http://www."+$scope.domain_name[0]+$scope.type[0]).toLowerCase();
						$scope.url2 = ("http://www."+$scope.domain_name[1]+$scope.type[1]).toLowerCase();
						$scope.url3 = ("http://www."+$scope.domain_name[2]+$scope.type[2]).toLowerCase();
						//~ $scope.url4 = ("http://www."+$scope.domain_name[3]+$scope.type[3]).toLowerCase();
						
						APIServices.saveomnidomains($stateParams.parid,$stateParams.ver,$scope.url1,$scope.url2,$scope.url3,"applicable",0,$scope.combo).success(function(response) {
							if(response.error.code == 0)  {
								APIServices.addjdomni($stateParams.parid,$stateParams.ver,$scope.combo,$scope.omni_type,$scope.custom_72,$scope.custom_73,$scope.omni_monthly,$scope.ecs_flag).success(function(response) {
									if(response.error.code == 0) {
										//~ $state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
                                        $state.go('appHome.sslcertificate',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler}); 
									}else {
										$rootScope.showCommonPop = 1;
										$rootScope.commonTitle = 'Alert!!';		
										$rootScope.commonShowContent = "Please Try Again";
									}
									
								});

							}else {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = 'Alert!!';		
								$rootScope.commonShowContent = response.error.msg;
								return false;
							}
						});
					
					}else {
						$rootScope.err_msg = response.error.msg;
						$mdDialog.show({
							controller: emailredirectDialogController,
							templateUrl: 'partials/dialogemailredirect.html',
							parent: angular.element(document.body),
							targetEvent: ev,
						})
						
					}
				});
			}
		}
	
	
		function emailredirectDialogController($scope) {
			
			$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
			$scope.businessUrl	=	'../business/bform.php?navbar=yes';
			//Handling for JDA
			if($rootScope.extraHandler == 'jda') {
				var expPathUrl	=	CONSTANTS.pathUrl.split('/');
				var windowLoc	=	window.location.host;
				var splwindowLoc	=	windowLoc.split(".");
				if(splwindowLoc[1] == 'jdsoftware'){
					$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
					$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
				} else {
					$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
					$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
				}
			}
			
			$scope.err_msg = $rootScope.err_msg;
		
		}
	
	});
	
	tmeModuleApp.controller('searchdemoController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		$scope.domainUrl	=	'../00_Payment_Rework/04_payment_mode_selection.php';
		$scope.businessUrl	=	'../business/bform.php?navbar=yes';
		
		$rootScope.extraHandler = $stateParams.page;
		$scope.show_listing = 'free';
		
		if($rootScope.extraHandler == 'jda') {
			var expPathUrl	=	CONSTANTS.pathUrl.split('/');
			var windowLoc	=	window.location.host;
			var splwindowLoc	=	windowLoc.split(".");
			if(splwindowLoc[1] == 'jdsoftware'){
				$scope.domainUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
			} else {
				$scope.domainUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=pay_mode_sel';
				$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
			}
		}
		
		$scope.demo_url = '../business/livebannerdemo.php?parentid='+$stateParams.parid+'&module=tme&data_city='+DATACITY+'&banner_type=13';
		
		$scope.show_listing_img = function(type) {
			$scope.show_listing = type;
		}
		
		$scope.gotoseljdomni = function() {
			$rootScope.demo_flag = 2;
		}
		
		$scope.showlivephoneserach = function() {
			$window.open('../business/livebannerdemo.php?parentid='+$stateParams.parid+'&module=tme&data_city='+DATACITY+'&banner_type=13', '_blank');
		}
		
		$scope.showcompetitors = function() {
			$window.open('../business/livebannerdemo.php?parentid='+$stateParams.parid+'&module=tme&data_city='+DATACITY+'&banner_type=13&competitor=1', '_blank');
		}
		
		$scope.gotoomnidemo = function() {
			$rootScope.demo_flag=3;
		}
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
	
	});
	
	tmeModuleApp.controller('addomniController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = returnState.page;
		
		$scope.calldomain = function() {
			if($scope.omni_selected == undefined || $scope.omni_selected == ''){
				$mdToast.show(
					$mdToast.simple()
					.content('Please select a option')
					.position('top right')
					.hideDelay(3000)
				);	
				return false;	
			}else if($scope.omni_selected == "yes") { 
				$state.go('appHome.omnidomainreg',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
			}else if($scope.omni_selected == "no") {
				APIServices.deletejdomni(returnState.paridInfo,returnState.ver).success(function(response) {
					$state.go('appHome.additionalcampaigns',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
				});
			}
		}
		
		$rootScope.gotoaddonpage = function() {
			$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler});
		}
	
	});
	
	tmeModuleApp.controller('addfixedcampaignController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = returnState.page;
		
		$scope.calldomain = function() {
			if($scope.fixed_selected == undefined || $scope.fixed_selected == ''){
				$mdToast.show(
					$mdToast.simple()
					.content('Please select a option')
					.position('top right')
					.hideDelay(3000)
				);	
				return false;	
			}else {
				if($scope.fixed_selected == "yes") {
					$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler});
				}else {
					$state.go('appHome.additionalcampaigns',{parid:returnState.paridInfo,ver:returnState.ver,page:$rootScope.extraHandler});
				}
			}
		}
		
		$rootScope.gotoaddonpage = function() {
			$state.go('appHome.omniappdemo',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
		}
	});
	
	tmeModuleApp.controller('selectomnicomboController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = returnState.page;
		$scope.combo1custom = false;
		$scope.combo2custom = false;
		$scope.domain_field_incl = false;
		
		
		APIServices.combopricelist(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0) {
				$scope.combo1price = response.error.msg.combo1['price'] ;
				$scope.omni_ultimaprice = response.error.msg.omni_ultima['price'];
				$scope.combo2price = response.error.msg.combo2['price'];
				$rootScope.combo1main = response.error.msg.combo1['price'] ;
				$rootScope.combo2main = response.error.msg.combo2['price'];
				
				$scope.omni_ultima = response.error.msg.omni_ultima['price'] ;
			
				
				APIServices.comboprice(returnState.paridInfo,returnState.ver).success(function(response) {
					if(response.error.code == 0) {
						
						if(response.error.msg['combo'] == 4){
							$scope.combo1price = response.error.msg['combo_cost'] ;
							$scope.combo1custom = true;
						}else if(response.error.msg['combo'] == 5){
							$scope.combo2price = response.error.msg['combo_cost'] ;
							$scope.combo2custom = true;
						}
						
					}
				});
		
		
			}else{
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Please Contact Software Team";
				return false;
			}
		});
		
		APIServices.combopricemin(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.min_combo_bgt =  response.error.msg.min_budget;
			}
		});
		
		
		$scope.opencombo1custom = function() {
		
			if($scope.combo1custom == true){
				$scope.combo1custom = false;
				APIServices.combopricereset(returnState.paridInfo,returnState.ver).success(function(response) {
					$scope.combo1price = $rootScope.combo1main ;
				}); 
			}else if($scope.combo1custom == false){
				$scope.combo1custom = true;
			}
			
		}

		$scope.closecombo1custom = function() {
				$scope.combo1custom = false; 
			
		}
		$scope.opencombo2custom = function() {
			if($scope.combo2custom == true){
				$scope.combo2custom = false;
				APIServices.combopricereset(returnState.paridInfo,returnState.ver).success(function(response) {
					$scope.combo2price = $rootScope.combo2main;
				}); 
			}else if($scope.combo2custom == false){
				$scope.combo2custom = true;
			}
		}
		$scope.resetcombo = function() {
			$scope.combo1custom = false;
			$scope.combo2custom = false;
			APIServices.combopricereset(returnState.paridInfo,returnState.ver).success(function(response) {
				$scope.combo1price = $rootScope.combo1main;
				$scope.combo2price = $rootScope.combo2main;
			}); 
		}
		$scope.calldomain = function(ev) {
			$rootScope.combotype =  $scope.combotype;
			
			if($scope.combotype == "omnisupreme") {
				var combo =1;
				var combo_price = $scope.combo1price;
				var type = 4;
			}else if($scope.combotype == "combo2"){
				var combo =2;
				var combo_price = $scope.combo2price;
				var type =5;
			}
			
				
			if($rootScope.combotype == undefined || $rootScope.combotype == ''){
				$mdToast.show(
					$mdToast.simple()
					.content('Please select a option')
					.position('top right')
					.hideDelay(3000)
				);	
				return false;	
			}else if(parseInt($scope.combo1price) < parseInt($scope.min_combo_bgt)){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Price must be more than "+$scope.min_combo_bgt;
			}else if(parseInt($scope.combo2price) < parseInt($rootScope.combo2main)) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Price must be more than "+parseInt($rootScope.combo2main);
				return false;
			}else if($scope.min_combo_bgt == undefined){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = 'Min price is missing';
			}else {
				APIServices.payment_type(returnState.paridInfo,$rootScope.combotype,returnState.ver).success(function(response) {  
					if(response.error_code == 0) {
						$cookieStore.put('selected_option', $rootScope.combotype);
						if($scope.combo1custom == true || $scope.combo2custom == true  ){
							APIServices.combocustomprice(returnState.paridInfo,returnState.ver,combo_price,0,type).success(function(response) {
								
							});
						}
						
						$rootScope.targetUrl	=	"appHome.omnidomainreg";
						$rootScope.submitFinalCats();
					}
				});
			}
		}
		
		function bonusmsgcontroller($scope,$mdDialog,$mdToast,APIServices) {
			
			if($rootScope.combotype == 'combo1') {
				$scope.displayprice = $rootScope.combo1main;
			}else if($rootScope.combotype == 'combo2') {
				$scope.displayprice = $rootScope.combo2main;
			}
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
		}
		
		$scope.gotobudgettype = function(){
			$state.go('appHome.selbudgettype',{parid:returnState.paridInfo,ver:returnState.ver,page:$stateParams.page});
		}
		
		function combopopup($scope,$mdDialog,$mdToast,APIServices){
			setTimeout(
				function() {
					$('.md-dialog-backdrop.md-opaque.md-default-theme').css({top:'0px'});
				},100
			);
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			
			$scope.showbannerpopup= function(ev) {
				$mdDialog.hide();
			}

		}
		
		$scope.open_combo		= function(ev) {

			$mdDialog.show({
				controller: combopopup,
				templateUrl: 'partials/combopopup.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}
				
	});

	tmeModuleApp.controller('omniappdemoController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {  
		
		var w = $(window).height();
		var d = $('.templete-container').height();
		if(d < w){
			$('.templete-container').css({'height':$(window).height()});
		}else{
			$('.templete-container').css({'height':'auto'});
		}
		
		$scope.sel_tempid = '';
		$scope.sel_tempname = '';
		
		$scope.selected_camapign = $cookieStore.get('selected_option');
		
		if($scope.selected_camapign=='omnisupreme') {
			var camapign_id = '731'
		}else if($scope.selected_camapign=='omniultima') {
			var camapign_id = '732'
		}else if($scope.selected_camapign=='combo2') {
			var camapign_id = '735'
		}else if($scope.selected_camapign=='omni1') {
			var camapign_id = '73'
		}else if($scope.selected_camapign=='omni2') {
			var camapign_id = '734'
		}else if($scope.selected_camapign=='omni7') {
			var camapign_id = '741'
		}	
		var selected_opt=$cookieStore.get('campaign_str');
		$rootScope.selected_arr = selected_opt.split(',');
		$rootScope.package_index = $rootScope.selected_arr.indexOf(camapign_id);
		
		$scope.select_this = function(ev,index,temp_id,temp_name) {
			$scope.sel_class=".sel_temp"+index;
			if($($scope.sel_class).hasClass('selected')){
				$(ev).removeClass('act');
				$($scope.sel_class).removeClass('selected');
				$scope.sel_tempid = '';
				$scope.sel_tempname = '';	
			}else{
				$('.mid_sec').removeClass('selected');
				$('.cusBtn').removeClass('act');
				$($scope.sel_class).addClass('selected');	
				$(ev).addClass('act');	
				$scope.sel_tempid = temp_id;
				$scope.sel_tempname = temp_name;		
			}
		}
		
		
		$scope.save_temp = function(ev) {
			if($scope.sel_tempid == '' || $scope.sel_tempname == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Please select a template";
			}else {
				APIServices.storeomnitemplateinfo($stateParams.parid,$stateParams.ver,$scope.sel_tempid,$scope.sel_tempname).success(function(response) {
					if(response.error.code == 0) {
						if($scope.omni_type=='services'){ 
							APIServices.deleteomnitemplatelive($stateParams.parid,$stateParams.ver).success(function(response) {
								if(response.error.code == 0) {
									$mdDialog.hide();
									$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
								}else{
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = 'Alert!!';		
									$rootScope.commonShowContent = response.error.msg;
								}
								
							});
						}
						else{
							$mdDialog.show({ 
								controller: iostemplateController,
								templateUrl: 'partials/iostemplate.html',
								parent: angular.element(document.body),
								targetEvent: ev,
							})
						}
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						
					}
				});
			}
			
		}
		
		function iostemplateController($scope) {
			$scope.selected_camapign = $cookieStore.get('selected_option');
			$scope.addios = function(ev) {
				APIServices.addomnitemplatetemp($stateParams.parid,$stateParams.ver).success(function(response) {
					if(response.error.code == 0) {
						$mdDialog.hide();
						$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
					}
				});
			}
			
			
			$scope.removeios = function(ev) {
				APIServices.deleteomnitemplatelive($stateParams.parid,$stateParams.ver).success(function(response) {
					if(response.error.code == 0) {
						$mdDialog.hide();
						$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else{
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
					}
					
				});
			}
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			
		}
		
		$scope.omni_type='';
		APIServices.gettemplateurl($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0) {
				$scope.template_url = response.results.data;
				$scope.omni_type = response.results.omni_type;
			}else {
				$mdToast.show(
					$mdToast.simple()
					.content(response.error.msg)
					.position('top right')
					.hideDelay(3000)
				);	
				return false;	
			}
		});
		
		$scope.show_popup = function(ev,show_img) {
			$rootScope.show_img = show_img;
			$mdDialog.show({
				controller: omnidemocontroller,
				templateUrl: 'partials/omnidemo.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}
		
		function omnidemocontroller($scope) {
			$scope.show_img = $rootScope.show_img;
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
		}
			
	});

	tmeModuleApp.controller('bannerspecificationController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {   
		$scope.selected_camapign = $cookieStore.get('selected_option');
		$scope.banner_specification = {};
		$scope.popupcond = $rootScope.samplepop;
		
		var selected_opt=$cookieStore.get('campaign_str');
		$scope.selected_arr = selected_opt.split(',');
		$scope.package_index = $scope.selected_arr.indexOf('5');
		
		if($scope.selected_camapign == "combo1") {
			$scope.combo = 1;
		}else if($scope.selected_camapign == "combo2") {
			$scope.omni_type = 5;
		}else if($scope.selected_camapign == "omni1") {
			$scope.omni_type = 1;
		}else if($scope.selected_camapign == "omni2") {
			$scope.omni_type = 2;
		}else if($scope.selected_camapign == "omniultima") {
			$scope.omni_type = 3;
		}else if($scope.selected_camapign == "omnisupreme") {
			$scope.omni_type = 4;
		}else if($scope.selected_camapign == "omni7") {
			$scope.omni_type = 7;
		}
		
		APIServices.get_banner_spec($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.banner_specification[0]  = response.error.msg;
			} else {
				$scope.banner_specification[0] = '';
			}
		});

		$scope.addbanner = function() {
			if($scope.banner_specification[0] == '' || $scope.banner_specification[0] == null) {
				$scope.banner_specification[0] = "no specification";
			}
			
			
			if($stateParams.type=='banner'){
				$scope.banner_rotation = $cookieStore.get('banner_rotation');
				APIServices.addbanner($stateParams.parid,$stateParams.ver,$scope.banner_specification[0],0,$scope.banner_rotation).success(function(response) {
					if(response.error.code == 0){
						if($scope.selected_arr[$scope.package_index+1] == '22'){
							APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
								if(response.error.code == 0) {
									$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
								}else {
									alert(response.error.msg);
									return false;
								}
							});
						}else {
							$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}
					}else if(response.error.code == 2){
						alert(response.error.msg);
						if($scope.selected_arr[$scope.package_index+1] == '22'){
							APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
								if(response.error.code == 0) {
									$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
								}else {
									alert(response.error.msg);
									return false;
								}
							});
						}else {
							$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}
					}else {
						alert(response.error.msg);
						return false;
					}
				});
			}else if($stateParams.type=='jdrrplus'){
				APIServices.addjdrrplus($stateParams.parid,$stateParams.ver,$scope.banner_specification[0],0).success(function(response) {
					if(response.error.code == 0){
						$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					} else if(response.error.code == 2){
						alert(response.error.msg);
						$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else {
						alert(response.error.msg);
						return false;
					}
				});
			}else if($stateParams.type=='combo'){
				APIServices.addjdrrplus($stateParams.parid,$stateParams.ver,$scope.banner_specification[0],2).success(function(response) {
					if(response.error.code == 0){
						$rootScope.setCustomBudgetPackage('','combo2');
					} else if(response.error.code == 2){
						alert(response.error.msg);
						$rootScope.setCustomBudgetPackage('','combo2');
					}else {
						alert(response.error.msg);
						return false;
					}
				});
			}
			
		}
		
	});
	
	
	tmeModuleApp.controller('jdratingController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$scope.sendratinglink = function(ev){ 
			APIServices.checklive($stateParams.parid).success(function(response) {
				if(response.errorCode == 0){
					APIServices.chkRatingCat($stateParams.parid).success(function(response1) {
						if(response1.errorCode == 0) {
							$mdDialog.show({ 
								controller: sendratingcontroller,
								templateUrl: 'partials/sendrating.html',
								parent: angular.element(document.body),
								targetEvent: ev,
							})
						}else {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = 'Alert!!';		
							$rootScope.commonShowContent = response1.errorStatus;
						}
					});
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'You cannot send JD Rating Link. Data is not present on live!';
				}
			});
		}
		
		$scope.gotojdrrdemo = function() {
			$rootScope.demo_flag = 3;
		}
		
		function sendratingcontroller ($scope) {
			
			$scope.businessUrl	=	'../business/bform.php?navbar=yes';
			//Handling for JDA
			if($rootScope.extraHandler == 'jda') {
				var expPathUrl	=	CONSTANTS.pathUrl.split('/');
				var windowLoc	=	window.location.host;
				var splwindowLoc	=	windowLoc.split(".");
				if(splwindowLoc[1] == 'jdsoftware'){
					$scope.businessUrl	=	'http://richiecarvalho.jdsoftware.com/jda_live/web/include/redirect_doc.php?redirect_path=location_info';
				} else {
					$scope.businessUrl	=	'http://jda.genio.in/jda/web/include/redirect_doc.php?redirect_path=location_info';
				}
			}
			
			APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
				$scope.mobile_arr = [];
				$scope.email_arr = [];
				
				$scope.selected_number = {};
				$scope.selected_email = {};
				
				if(response.error.code == 0) {
					if(response.error.result.mobile != ''){
						$scope.mobile_arr= response.error.result.mobile.split(',');
						$scope.mobile_err = false;
					}else {
						$scope.mobile_err = true;
					}
					if(response.error.result.email != '') {
						$scope.email_arr= response.error.result.email.split(',');
						$scope.email_err = false;
					}else {
						$scope.email_err = true;
					}
				}
				
				
			});
			
			$scope.send_ratings = function(ev) {
				$scope.sel_mob = '';
				$scope.sel_email = '';
				angular.forEach($scope.selected_number,function(value,key){
					if(value) {
						$scope.sel_mob += key+',';
					}	
				});
				angular.forEach($scope.selected_email,function(value,key){
					if(value) {
						$scope.sel_email += key+',';
					}	
				});
				
				if($scope.sel_email == '' && $scope.sel_mob == '') {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'Please select mobile number or email-id !!';
				}else {
					APIServices.sendratinglink($stateParams.parid,$rootScope.companyTempInfo['data']['companyname'],$scope.sel_mob,$scope.sel_email).success(function(response) {
						if(response.data.error_code == 0) {
							$mdDialog.hide();
						}
					});
				}
			}
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
		} 
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
		
	
		
	});
	
	tmeModuleApp.controller('pricechartController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$scope.pricechart_cb = {};
		$scope.disable_cb = {};
		$scope.campaign_order = ['1','2','73','734','731','732','735','5','225','22'];
		$scope.custom_campaign_upfront = ['73','5','731','75','731_setup','735_setup','735','73_setup'];
		$scope.custom_campaign_ecs = ['73','5','731','75','731_setup','735_setup','735','73_setup'];
		$rootScope.extraHandler = $stateParams.page;
		$scope.edit_price = false;
		$cookieStore.remove("selected_option");
		$cookieStore.remove("payment_type");
		$scope.reduce_price = 0;
		
		if($rootScope.extraHandler ==  "jda") {
			var module_name = "jda";
		}else {
			var module_name = "tme";
		}
		
		APIServices.combopricemin(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.min_combo_bgt =  response.error.msg.min_budget;
			}
		});
		
		APIServices.checkemployeeeligible(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.reduce_price = 1;
			}else {
				$scope.reduce_price = 0;
			}
		});
		
		APIServices.tempactualbudgetupdate(returnState.paridInfo,returnState.ver).success(function(response) {
			if(response.error.code == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Please contact software team';
			    return false;    // change alert msg
			}
		});
		
		$scope.disable_campaign = function(key,type,ev) {
			
			if(key == '225') {
				if($scope.ecs_flg != 1 || type != "upfront"){
					$scope.disable_cb['5'] = $scope.pricechart_cb[key];
				}
				$scope.disable_cb['22'] = $scope.pricechart_cb[key] ;
				if($scope.pricechart_cb['2'] == false && $scope.pricechart_cb['1'] == false && $scope.pricechart_cb['731'] == false && ($scope.pricechart_cb['732'] == undefined || $scope.pricechart_cb['732'] == false) && $scope.pricechart_cb['734'] == false && $scope.pricechart_cb['73'] == false && ($scope.ecs_flg != 1 || type != "upfront")){
						$scope.disable_cb['735'] = $scope.pricechart_cb[key];
				}else {
					$scope.disable_cb['735'] = true ;
				}
				
				if($scope.pricechart_cb['225'] == false) {
					APIServices.deletebannerLive(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
					
					APIServices.deletejdrrLive(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
					
				}
			}else if(key == '5' || key == '22') {
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true) {
					$scope.disable_cb['735'] = true ;
					$scope.disable_cb['225'] = true ;
				}else if($scope.pricechart_cb['5'] == false && $scope.pricechart_cb['22'] == false) {
					$scope.disable_cb['225'] = false;
					if($scope.pricechart_cb['2'] == false && $scope.pricechart_cb['1'] == false && $scope.pricechart_cb['731'] == false && ($scope.pricechart_cb['732'] == undefined || $scope.pricechart_cb['732'] == false) && $scope.pricechart_cb['734'] == false && $scope.pricechart_cb['73'] == false && ($scope.ecs_flg != 1 || type != "upfront")){
						$scope.disable_cb['735'] = false;
					}
				}
				if($scope.pricechart_cb['5'] == false  && key == '5') { 
					APIServices.deletebannerLive(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
				}else if($scope.pricechart_cb['22'] == false && key == '22') {
					APIServices.deletejdrrLive(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
				}
			}else if(key == '1' || key == '2') {
				if(key == '1'){
					$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
				}else if(key == '2') {
					$scope.disable_cb['1'] = $scope.pricechart_cb[key]; 
				}
				
				if($scope.pricechart_cb['73'] == true || $scope.pricechart_cb['734'] == true ) {
					$scope.disable_cb['732'] = true; 
					$scope.disable_cb['731'] = true; 
					$scope.disable_cb['735'] = true; 
				}else{
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key];  
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true) {
					$scope.disable_cb['735'] = true;
				}
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletecampaign(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
				}
				
			}else if(key == '731' || key == '732' || key == '735' ) {
				$scope.disable_cb['1'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['733'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				if(key == '731') {
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
				}else if(key == '732') {
					$scope.disable_cb['731'] = $scope.pricechart_cb[key];
					$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
				}else if(key == '735') {
					$scope.disable_cb['731'] = $scope.pricechart_cb[key];
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['225'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['5'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['22'] = $scope.pricechart_cb[key] ;
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true ) {
					$scope.disable_cb['735'] = true;
				}
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletecombolive(returnState.paridInfo,returnState.ver).success(function(response) {
						if( key == '732') {
							$scope.ultima_selected = 0;
						}
					});
				}
				
			}else if(key == '73' || key == '734') {
				if(key == '73') {
					$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				}else {
					$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				}
				
				$scope.disable_cb['733'] = $scope.pricechart_cb[key] ;
				
				if($scope.pricechart_cb['1'] == true || $scope.pricechart_cb['2'] == true){
					$scope.disable_cb['732'] = true; 
					$scope.disable_cb['731'] = true; 
					$scope.disable_cb['735'] = true; 
				}else {
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key];
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true) {
					$scope.disable_cb['735'] = true;
				}
				
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletejdomniLive(returnState.paridInfo,returnState.ver).success(function(response) {
						
					});
				}
			}else if(key == '733') {
				$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
			}else if(key == '735' ) {
				$scope.disable_cb['1'] = $scope.pricechart_cb[key];
				$scope.disable_cb['2'] = $scope.pricechart_cb[key];
			}
		}
		
		
		$scope.switch_option = function(type) {
			$scope.pc_discount = {};
			$scope.pc_actual = {};
			$scope.campaign_name = {};
			$scope.pricechart_cb = {};
			$cookieStore.put('payment_type',type);
			$scope.ecs_flg = 0;
			if(type == 'upfront'){
				$(".pc_details1").css('display','block');
				$(".pc_details2").css('display','none');
				$(".pc_upfront").addClass('pc_active');
				$(".pc_ecs").removeClass('pc_active');
				
				$scope.ultima_selected = 0;
				APIServices.fetchpricechatprice(returnState.paridInfo,returnState.ver,1).success(function(response) {
					if(response.error.code == 0) {
						
						$scope.pricechatup = 	response.data.result;
						angular.forEach($scope.pricechatup.Standard_Plans,function(value,key) {
							$scope.disable_cb[key] = false;
							if(value.checked == true) {
								$scope.pricechart_cb[key] = true;
							}else {
								$scope.pricechart_cb[key] = false;
							}
							$scope.pc_actual[key] = value.actual_price;
							$scope.campaign_name[key] = value.name;
							if($scope.custom_campaign_upfront.indexOf(key) > -1) {
								$scope.pc_discount[key] = value.actual_price;
								if(key == 73) {
									$scope.pc_discount[key+"_setup"] = value.setup;
									$scope.pc_actual[key+"_setup"] = value.setup;
								}
							}
						});
						
						angular.forEach($scope.pricechatup.Recommended_Plans,function(value,key) {
							$scope.disable_cb[key] = false;
							if(value.checked == true) {
								$scope.pricechart_cb[key] = true;
								//~ $scope.disable_campaign(key,'upfront');
								if(key == '732') {
									$scope.ultima_selected = 1;
								}
							}else {
								$scope.pricechart_cb[key] = false;
							}
							$scope.pc_actual[key] = value.actual_price;
							$scope.campaign_name[key] = value.name;
							if($scope.custom_campaign_upfront.indexOf(key) > -1) {
								$scope.pc_discount[key] = value.actual_price;
								if(key == 731 || key == 735) {
									$scope.pc_discount[key+"_setup"] = value.setup;
									$scope.pc_actual[key+"_setup"] = value.setup;
								}
							}
						});
						
						APIServices.check_ecs(returnState.paridInfo,returnState.ver,module_name).success(function(response) { 
							if(response.error.code == 1) {
								$scope.ecs_flg = 1;
								$mdToast.show(
									$mdToast.simple()
									.content('On going Ecs Contract')
									.position('top right')
									.hideDelay(3000)
								);	
								angular.forEach($scope.pricechart_cb,function(value,key) {
									if(key != 22 && key != 225) {
										$scope.disable_cb[key] = true;
										$scope.pricechart_cb[key] = false;
									}
								});
								
							}
						});
						
						angular.forEach($scope.pricechart_cb,function(value,key) {
							if($scope.pricechart_cb[key] ==  true)
							$scope.disable_campaign(key,'upfront');
						});
					}
					
				});
			}else if(type == 'ecs') {
				$(".pc_details2").css('display','block');
				$(".pc_details1").css('display','none');
				$(".pc_ecs").addClass('pc_active');
				$(".pc_upfront").removeClass('pc_active');
				
				APIServices.fetchpricechatprice(returnState.paridInfo,returnState.ver,2).success(function(response) {
					if(response.error.code == 0) {
						$scope.pricechatecs = 	response.data.result;
						angular.forEach($scope.pricechatecs.Standard_Plans,function(value,key) {
							$scope.disable_cb[key] = false;
							if(value.checked == true) {
								$scope.pricechart_cb[key] = true;
								//~ $scope.disable_campaign(key,'ecs');
							}else {
								$scope.pricechart_cb[key] = false;
							}
							$scope.pc_actual[key] = value.emi;
							$scope.campaign_name[key] = value.name;
							if($scope.custom_campaign_ecs.indexOf(key) > -1) {
								$scope.pc_discount[key] = value.emi;
								if(key == 73) {
									$scope.pc_discount[key+"_setup"] = value.setup;
									$scope.pc_actual[key+"_setup"] = value.setup;
								}
							}
						});
						
						angular.forEach($scope.pricechatecs.Recommended_Plans,function(value,key) {
							$scope.disable_cb[key] = false;
							if(value.checked == true) {
								$scope.pricechart_cb[key] = true;
								//~ $scope.disable_campaign(key,'ecs');
							}else {
								$scope.pricechart_cb[key] = false;
							}
							$scope.pc_actual[key] = value.emi;
							$scope.campaign_name[key] = value.name;
							if($scope.custom_campaign_ecs.indexOf(key) > -1) {
								$scope.pc_discount[key] = value.emi;
								if(key == 731 || key == 735) {
									$scope.pc_discount[key+"_setup"] = value.setup;
									$scope.pc_actual[key+"_setup"] = value.setup;
								}
							}
						});
						if($scope.ultima_selected == 1) {
							$scope.pricechart_cb['732'] =  true
						}
						angular.forEach($scope.pricechart_cb,function(value,key) {
							if($scope.pricechart_cb[key] ==  true)
							$scope.disable_campaign(key,'ecs');
						});
						
					}
				});
				
				APIServices.check_ecs(returnState.paridInfo,returnState.ver,module_name).success(function(response) { 
					if(response.error.code == 1) {
						$scope.ecs_flg = 1;
					}
				});
				
			}
		}
		
		$scope.switch_option("upfront");
		
		$scope.submit_campaigns = function(ev) {
			$scope.selected_campaigns = '';
			$scope.selected_campaign_names = '';
			angular.forEach($scope.campaign_order,function(value,key) {
				if($scope.pricechart_cb[value] == true) {
					$scope.selected_campaigns += value+',';
					$scope.selected_campaign_names += $scope.campaign_name[value]+',';
					
					if(value == '73') {
						$cookieStore.put('selected_option','omni1');
					}else if(value == '731') {
						$cookieStore.put('selected_option','omnisupreme');
					}else if(value == '732') {
						$cookieStore.put('selected_option','omniultima');
					}else if(value == '735') {
						$cookieStore.put('selected_option','combo2');
					}else if(value == '734') {
						$cookieStore.put('selected_option','omni2');
					}else if(value == '741') {
						$cookieStore.put('selected_option','omni7');
					} 
				}
			});
			
			if($scope.selected_campaigns == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Please select a campaign';
				return false;
			}
			
			$scope.stop_proceed = 0; 
			angular.forEach($scope.pc_discount,function(value,key) {
				if((parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key])) && key!=731 && key!="731_setup" && key!="735_setup") {
					$scope.stop_proceed = 1;
					return false;
				}else if(key == 731 && (parseInt($scope.pc_discount[key]) < $scope.combo1_min)) {
					$scope.stop_proceed = 1;
					return false;
				}else if($scope.pc_discount[key] == '' || $scope.pc_discount[key] == undefined){
					$scope.stop_proceed = 1;
					return false;
				}else if(parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key]) && $scope.reduce_price == 0 && (key =="731_setup" ||  key=="735_setup" || key=="73_setup")){
					$scope.stop_proceed = 1;
					return false;
				}
				
			});
			
			if($scope.stop_proceed == 1) {
				return false;
			}
			
			$scope.selected_campaigns = $scope.selected_campaigns.slice(0, -1);
			$scope.selected_campaign_names = $scope.selected_campaign_names.slice(0, -1).toLowerCase();
			$cookieStore.put('campaign_str', $scope.selected_campaigns);
			$cookieStore.put('campaign_names',$scope.selected_campaign_names);
			
			$scope.selected_arr = $scope.selected_campaigns.split(',');
			
			APIServices.payment_type(returnState.paridInfo,$scope.selected_campaign_names,$stateParams.ver).success(function(response) {  
				if(response.error_code == 0) {
					if($scope.selected_arr['0'] == '1') {
						$rootScope.targetUrl	=	"appHome.customPackage";
						$rootScope.submitFinalCats();
					}else if($scope.selected_arr['0'] == '2') {
						$rootScope.targetUrl	=	"appHome.areaSel";
						$rootScope.submitFinalCats();
					}else if($scope.selected_arr['0'] == '73' || $scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '732' || $scope.selected_arr['0'] == '735' || $scope.selected_arr['0'] == '734') {
						$rootScope.targetUrl	=	"appHome.omnidomainreg";
						$rootScope.submitFinalCats();
					}else if($scope.selected_arr['0'] == '5' ){
						$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else if($scope.selected_arr['0'] == '225'){
						$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else if($scope.selected_arr['0'] == '22') {
						APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
							if(response.error.code == 0) {
								$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
							}else {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = 'Alert!!';		
								$rootScope.commonShowContent = response.error.msg;
							}
						});
					}
				}
			});	
		}
		
		$scope.open_price = function() {
			$scope.edit_price = !$scope.edit_price;
		}
		
		$scope.call_discount_api = function(ev,key,payment_type) {
			if(payment_type == "upfront") {
				$scope.combo1_min = parseInt($scope.min_combo_bgt)*12;
			}else if(payment_type == "ecs") {
				$scope.combo1_min = parseInt($scope.min_combo_bgt);
			}
			
			if(key.lastIndexOf("_setup") != -1) {
				var pos = key.lastIndexOf("_setup");
				var index= key.slice(0,pos);
			}else {
				var index = key;
			}
			
			
			if($scope.pricechart_cb[index] != true) {
				$scope.pc_discount[key] = $scope.pc_actual[key];
				return false;	
			}
			if((parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key])) && key!=731 && key!="731_setup" && key!="735_setup") {
				$scope.pc_discount[key] = $scope.pc_actual[key];
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "custom value must be more than "+$scope.pc_actual[key];
				return false;
			}else if(key == 731 && (parseInt($scope.pc_discount[key]) < $scope.combo1_min)) {
				$scope.pc_discount[key] = $scope.pc_actual[key];
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "custom value must be more than "+$scope.combo1_min;
				return false;
			}else if($scope.pc_discount[key] == '' || $scope.pc_discount[key] == undefined){
				$scope.pc_discount[key] = $scope.pc_actual[key];
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Custom value cant be empty";
				return false;
			}else if(parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key]) && $scope.reduce_price == 0 && (key =="731_setup" ||  key=="735_setup" || key=="73_setup")){
				$scope.pc_discount[key] = $scope.pc_actual[key];
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Custom value must be more than "+$scope.pc_actual[key];
				return false;
			}
			
			if(key == "73_setup") {
				var custom_val = $scope.pc_discount[key];
				key = 72;
			}else {
				var custom_val = $scope.pc_discount[key];
			
				if(payment_type == "upfront" && key == 5)  {
					custom_val = Math.ceil($scope.pc_discount[key])/12;
				}else if(payment_type == "ecs" && key == 5) {
					custom_val = $scope.pc_discount[key];
				}
			
			}
			
			//~ if(key == "731_setup" || key == "735_setup") {
				//~ key = 731;
			//~ }
			
			if(index != 731 && index != 735) {
				APIServices.insert_discount($stateParams.parid,$stateParams.ver,key,custom_val).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
				});
			}else if(index == 731 || index == 735){
				if(index == 731) {
					$scope.omni_type =4;
				}else if(index == 735) {
					$scope.omni_type =5;
				}
				
				if(payment_type == "upfront")  {
					$scope.dis_price = $scope.pc_discount[index]/12;
				}else if(payment_type == "ecs") {
					$scope.dis_price = $scope.pc_discount[index];
				}
				APIServices.combocustomprice($stateParams.parid,$stateParams.ver,$scope.dis_price,0,$scope.omni_type,$scope.pc_discount[index+'_setup']).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
					}
				});
			}
			
			if(key == 72) {
				APIServices.insert_discount($stateParams.parid,$stateParams.ver,73,$scope.pc_discount[73]).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
				});
			}else if(key == 73) {
				APIServices.insert_discount($stateParams.parid,$stateParams.ver,72,$scope.pc_discount["73_setup"]).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
				});
			}
				
		}
		
		$scope.reset_campaign = function(ev) {
			APIServices.deleteallcampaigns(returnState.paridInfo,returnState.ver).success(function(response) {
				if(response.error.code == 0) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Success!!';		
					$rootScope.commonShowContent = "Success";
					return false;
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = response.error.code;
					return false;
				}
			}); 
		}
			
							
	});

	tmeModuleApp.controller('emailselectionController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_innr').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
		
		
		var email_pattern = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		
		$scope.email_radio = '';
		
		$scope.email_proceed = function(ev) {
			
			if($scope.email_radio == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Please select a option';
				return false;
			}else if($scope.email_radio == 'own') {
				$mdDialog.show({
					controller: existingemailcontroller,
					templateUrl: 'partials/existingemail.html',
					parent: angular.element(document.body),
					targetEvent: ev,
				});
			}else if($scope.email_radio == 'new') {
				$state.go('appHome.emailoption',{parid:$stateParams.parid,ver:$stateParams.ver,package_type:1,page:$rootScope.extraHandler});
			}	
			
		}
		
		function existingemailcontroller($scope) {
			$scope.show_comfirm_msg = false;
			
			APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
				$scope.email_arr = [];
				
				if(response.error.code == 0) {
					$scope.email_arr= response.error.result.email.split(',');
				}else{
					$scope.email_arr[0] = "";
				}
				
			});
			
			$scope.addemail = function(ev) {
				
				if(!email_pattern.test($scope.email_arr[$scope.email_arr.length -1]) || $scope.email_arr[$scope.email_arr.length -1] == ''){
					$mdToast.show(
						$mdToast.simple()
						.content('Please enter proper emailid')
						.position('top right')
						.hideDelay(3000)
					);	
				
					//~ $mdDialog.show(	
						//~ $mdDialog.alert()
							//~ .parent(angular.element(document.querySelector('#popupContainer')))
							//~ .clickOutsideToClose(true)
							//~ .title()
							//~ .content('Please enter proper emailid')
							//~ .ariaLabel('Alert Dialog Demo')
							//~ .ok('Got it!')
							//~ .targetEvent(ev)
					//~ );
					return false;
				}
				
				if($scope.email_arr[$scope.email_arr.length -1] != '' && $scope.email_arr[$scope.email_arr.length -1] != undefined){
					$scope.email_arr.push('');
				}
			}
			
			$scope.close_popup = function() {
				$mdDialog.hide();
			}
			
			$scope.confirm_button = function() {
				$scope.email_str = '';
				var invalid_email = 0;
				angular.forEach($scope.email_arr,function(val,key) {
					if(val!=''){
						if(!email_pattern.test(val)) {
							invalid_email = 1;
						}
						$scope.email_str += val+',';
					}
				});
				$scope.email_str = $scope.email_str.slice(0,-1);
				for(var i=0;i<$scope.email_arr.length -1;i++){
					if($scope.email_arr.length > 1 && i < $scope.email_arr.length -1  && $scope.email_arr[j]!='' && $scope.email_arr[i]!='') {
						for(var j=i+1;j<=$scope.email_arr.length -1;j++){
							if($scope.email_arr[i] == $scope.email_arr[j]) {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = 'Alert!!';		
								$rootScope.commonShowContent = "Please enter different email id";
								return false;
							}
						}
					}
				}
				
				if(invalid_email == 1) {
					$mdToast.show(
						$mdToast.simple()
						.content('Please enter proper emailid')
						.position('top right')
						.hideDelay(3000)
					);	
					return false;
				}else {
					$scope.show_comfirm_msg = true;
				}
			}
			
			$scope.proceed_button = function() {
			
				APIServices.saveemailids($stateParams.parid,$stateParams.ver,$scope.email_str).success(function(response) {
					if(response.error.code == 0) {
						$mdDialog.hide();	
						$state.go('appHome.smsselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else {
						$mdToast.show(
							$mdToast.simple()
							.content('emailid not saved')
							.position('top right')
							.hideDelay(3000)
						);	
						return false;
					}
				});
			}
			
			
		}
		
	});
	
	tmeModuleApp.controller('emailoptionController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_innr').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
		
		$scope.email_package = '';
		$scope.directi_price = 0;
		$scope.google_price = 0;
		
		
		APIServices.emailpackageprice($stateParams.parid,$stateParams.ver,1).success(function(response) {
			if(response.error.code == 0){
				$scope.directi_price =   Math.round(response.data['direct-i'].price*12);
				$scope.google_price = Math.round(response.data['google'].price*12);
			}else {
				$mdToast.show(
					$mdToast.simple()
					.content('Email Price is missing')
					.position('top right')
					.hideDelay(3000)
				);	
				return false;
			}
		});
		
		
		$scope.proceed = function(ev) {
			//console.log($scope.email_package);
			if($scope.email_package == ''){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Please select option";
				return false;
			}else {
					package_type=1;
					
				if($scope.email_package =='google')
					package_type=2;
				else if($scope.email_package =='directi'){
					package_type=1;
				} 
				$state.go('appHome.emailnumber',{parid:$stateParams.parid,ver:$stateParams.ver,package_type:package_type,page:$rootScope.extraHandler});
			}	
		}
		
	});
	
	tmeModuleApp.controller('emailnumberController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_innr').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
		
		$scope.email_type = $stateParams.package_type;
		$scope.email_number = 0;
		$scope.email_price = 0;
		$scope.type = '';
		
		if($scope.email_type == 1){
			$scope.type = 'direct-i';
		}else if($scope.email_type == 2) {
			$scope.type = 'google';
		}
		
		APIServices.emailpackageprice($stateParams.parid,$stateParams.ver,$scope.email_type).success(function(response) {
			if(response.error.code == 0){
				$scope.email_price = response.data[$scope.type].price;
			}else {
				$mdToast.show(
					$mdToast.simple()
					.content('Email Price is missing')
					.position('top right')
					.hideDelay(3000)
				);	
				return false;
			}
		});
		
		$scope.save_email_package  = function(ev) {
			var validationPattern = /^[a-zA-Z0-9._-]+$/;
			if($scope.email_number == 0) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Please enter the number of email id's required";
				return false;
			}
			else if($scope.email_type==2 && $scope.admin_username==''){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Please enter admin username";
				return false;
			}else {
				
				if(validationPattern.test($scope.admin_username) == false) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Invalid Admin Username";
					return false;
				}

				APIServices.emailpackagerequired($stateParams.parid,$stateParams.ver,$scope.email_type,$scope.email_number,$scope.admin_username).success(function(response) {
					if(response.error.code == 0) {
						
						$mdDialog.show({
							controller: emailConformationcontroller,
							templateUrl: 'partials/emailconformation.html',
							parent: angular.element(document.body),
							targetEvent: ev,
						});
						
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}	
					
				});
			}
			
			function emailConformationcontroller($scope) {
				
				$scope.close_popup = function() {
					$mdDialog.hide();
				}
				
				$scope.proceed_sms = function() {
					$mdDialog.hide();
					$state.go('appHome.smsselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}
				
				
			}
		}
	});
	
	tmeModuleApp.controller('smsselectionController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		var w = $(window).height();
		var d = $('.mainContain_innr').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
		
		$scope.selected_camapign = $cookieStore.get('selected_option');
		$rootScope.extraHandler = $stateParams.page;
		$scope.sms_radio = '';
		
		if($scope.selected_camapign=='omnisupreme') {
			var camapign_id = '731'
		}else if($scope.selected_camapign=='omniultima') {
			var camapign_id = '732'
		}else if($scope.selected_camapign=='combo2') {
			var camapign_id = '735'
		}else if($scope.selected_camapign=='omni1') {
			var camapign_id = '73'
		}else if($scope.selected_camapign=='omni2') {
			var camapign_id = '734'
		}else if($scope.selected_camapign=='omni7') {
			var camapign_id = '741'
		}else if($scope.selected_camapign=='omni14') {
			var camapign_id = '748'
		}	
		
		var selected_opt=$cookieStore.get('campaign_str');
		$rootScope.selected_arr = selected_opt.split(',');
		$rootScope.package_index = $rootScope.selected_arr.indexOf(camapign_id);
		
		$scope.sms_proceed = function(ev) {
			if($scope.sms_radio == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Please select a option';
				return false;
			}else if($scope.sms_radio == 'yes') {
				$state.go('appHome.smsnumber',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
			}else if($scope.sms_radio == 'no'){
				//~ if($scope.selected_camapign=='omnisupreme' || $scope.selected_camapign=='omniultima'){
					//~ $rootScope.setCustomBudgetPackage('','combo');
				//~ }else if($scope.selected_camapign =="omni1" || $scope.selected_camapign =="omni2"){
					//~ $state.go('appHome.addfixedcampaign',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }else if($scope.selected_camapign =="fixed"){
					//~ $state.go('appHome.additionalcampaigns',{parid:$stateParams.parid,page:$rootScope.extraHandler,ver:$stateParams.ver});
				//~ }else if($scope.selected_camapign =="combo2"){
					//~ $state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:"combo",ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }else{
					//~ $state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }
				
				if($scope.selected_camapign=='omnisupreme' || $scope.selected_camapign=='omniultima'){
					if($rootScope.selected_arr[$rootScope.package_index+1] == '5') {
						$rootScope.setCustomBudgetPackage('','combo','banner');
					}else if($rootScope.selected_arr[$rootScope.package_index+1] == '225') {
						$rootScope.setCustomBudgetPackage('','combo','jdrrplus');
					}else if($rootScope.selected_arr[$rootScope.package_index+1] == '22'){
						APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
							if(response.error.code == 0) {
								$rootScope.setCustomBudgetPackage('','combo','');
							}else {
								alert(response.error.msg);
								return false;
							}
						});
					}else {
						$rootScope.setCustomBudgetPackage('','combo','');
					}
				}else if($scope.selected_camapign =="combo2"){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:"combo",ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '1') {
					$state.go('appHome.customPackage',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '2') {
					$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '73' || $rootScope.selected_arr[$rootScope.package_index+1] == '731' || $rootScope.selected_arr[$rootScope.package_index+1] == '732' || $rootScope.selected_arr[$rootScope.package_index+1] == '735' ) {
					$state.go('appHome.omnidomainreg',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '5' ){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '225'){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '22') {
					APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
						if(response.error.code == 0) {
							$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}else {
							alert(response.error.msg);
							return false;
						}
					});
				}else {
					$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}
						
				
			}	
			
		}
	
	});
	 tmeModuleApp.controller('sslcertificateController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		$scope.selected_payment_type = $cookieStore.get('payment_type');
        $rootScope.extraHandler 	 = $stateParams.page;
		$scope.ssl_radio	=	'';
		$scope.ssl_disabled	=	'';
        var w = $(window).height();
        var d = $('.mainContain_innr').height()
        if(d < w){
            $('.mainContain_innr').css({'height':$(window).height() - 270});
        }else{
            $('.mainContain_innr').css({'height':'auto'});
        }
		if($scope.selected_payment_type	==	"upfront"){ 
				$scope.ssl_disabled	=	"upfront";
		}else{
				$scope.ssl_disabled	=	"ecs";
		}
        $scope.ssl_proceed  = function(ev) {
			if($scope.ssl_radio == '') {
                $rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please select a option";				
                return false;
            }else if($scope.ssl_radio == 'yes') {
				if($('#purchaseAmt input:radio[name=ssl_payment]:checked').size() == 0){
					$rootScope.showCommonPop 		= 1;
					$rootScope.commonTitle 			= "Genio";
					$rootScope.commonShowContent 	= "Please select Payment type";				
					return false;
				}else{
					var ssl_payment_val		=	$('#purchaseAmt input:radio[name=ssl_payment]:checked').attr('ssl_payment_val');
					var ssl_payment_type	=	$('#purchaseAmt input:radio[name=ssl_payment]:checked').attr('ssl_payment_type');
					
					APIServices.SSLpackagerequired($stateParams.parid,$stateParams.ver,ssl_payment_type,ssl_payment_val).success(function(response) {
						if(response.error.code == 0) {
							$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}else{
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = response.error.msg;
							return false;
						}
					});
					
				}
            }else if($scope.ssl_radio == 'no'){ 
				//$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				APIServices.deleteSSLPackage($stateParams.parid,$stateParams.ver,ssl_payment_type,ssl_payment_val).success(function(response) {
					if(response.error.code == 0) {
						$state.go('appHome.emailselection',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
					}
				});
			}
		};
    });
	tmeModuleApp.controller('smsnumberController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.extraHandler = $stateParams.page;
		
		var w = $(window).height();
		var d = $('.mainContain_innr').height()
		if(d < w){
			$('.mainContain_innr').css({'height':$(window).height() - 270});
		}else{
			$('.mainContain_innr').css({'height':'auto'});
		}
		$rootScope.selected_camapign = $cookieStore.get('selected_option');
		$scope.sms_type = 1;
		$scope.sms_number = 10000;
		$scope.sms_price = 0;
		
		APIServices.smsprice($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.sms_price = response.data.price;
			}
		});
		
		if($scope.selected_camapign=='omnisupreme') {
			var camapign_id = '731'
		}else if($scope.selected_camapign=='omniultima') {
			var camapign_id = '732'
		}else if($scope.selected_camapign=='combo2') {
			var camapign_id = '735'
		}else if($scope.selected_camapign=='omni1') {
			var camapign_id = '73'
		}else if($scope.selected_camapign=='omni2') {
			var camapign_id = '734'
		}	
		
		var selected_opt=$cookieStore.get('campaign_str');
		$rootScope.selected_arr = selected_opt.split(',');
		$rootScope.package_index = $rootScope.selected_arr.indexOf(camapign_id);
		
		//~ APIServices.emailpackageprice($stateParams.parid,$stateParams.ver,$scope.email_type).success(function(response) {
			//~ if(response.error.code == 0){
				//~ $scope.email_price = response.data.price;
			//~ }else {
				//~ $mdToast.show(
					//~ $mdToast.simple()
					//~ .content('Email Price is missing')
					//~ .position('top right')
					//~ .hideDelay(3000)
				//~ );	
				//~ return false;
			//~ }
		//~ });
		
		$scope.save_sms_package  = function(ev) {
			if($scope.sms_number < 10000) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Number of Sms must be more than 10,000";
				return false;
			}else {
				APIServices.smspackagerequired($stateParams.parid,$stateParams.ver,$scope.sms_number).success(function(response) {
					if(response.error.code == 0) {
						
						$mdDialog.show({
							controller: emailConformationcontroller,
							templateUrl: 'partials/emailconformation.html',
							parent: angular.element(document.body),
							targetEvent: ev,
						});
						
					}else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}	
					
				});
			}
		}
			
		function emailConformationcontroller($scope) {
			
			$scope.close_popup = function() {
				$mdDialog.hide();
			}
			
			$scope.proceed_sms = function() {
				$mdDialog.hide();
				//~ if($rootScope.selected_camapign=='omnisupreme' || $rootScope.selected_camapign=='omniultima'){
					//~ $rootScope.setCustomBudgetPackage('','combo');
				//~ }else if($rootScope.selected_camapign =="omni1" || $rootScope.selected_camapign =="omni2"){
					//~ $state.go('appHome.addfixedcampaign',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }else if($rootScope.selected_camapign =="fixed"){
					//~ $state.go('appHome.additionalcampaigns',{parid:$stateParams.parid,page:$rootScope.extraHandler,ver:$stateParams.ver});
				//~ }else if($rootScope.selected_camapign =="combo2"){
					//~ $state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:"combo",ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }else{
					//~ $state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				//~ }  $rootScope
				if($rootScope.selected_camapign=='omnisupreme' || $rootScope.selected_camapign=='omniultima'){
					if($rootScope.selected_arr[$rootScope.package_index+1] == '5') {
						$rootScope.setCustomBudgetPackage('','combo','banner');
					}else if($rootScope.selected_arr[$rootScope.package_index+1] == '225') {
						$rootScope.setCustomBudgetPackage('','combo','jdrrplus');
					}else if($rootScope.selected_arr[$rootScope.package_index+1] == '22'){
						APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
							if(response.error.code == 0) {
								$rootScope.setCustomBudgetPackage('','combo','');
							}else {
								alert(response.error.msg);
								return false;
							}
						});
					}else {
						$rootScope.setCustomBudgetPackage('','combo','');
					}
				}else if($rootScope.selected_camapign =="combo2"){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:"combo",ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '1') {
					$state.go('appHome.customPackage',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '2') {
					$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '73' || $rootScope.selected_arr[$rootScope.package_index+1] == '731' || $rootScope.selected_arr[$rootScope.package_index+1] == '732' || $rootScope.selected_arr[$rootScope.package_index+1] == '735' ) {
					$state.go('appHome.omnidomainreg',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '5' ){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '225'){
					$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else if($rootScope.selected_arr[$rootScope.package_index+1] == '22') {
					APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
						if(response.error.code == 0) {
							$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
						}else {
							alert(response.error.msg);
							return false;
						}
					});
				}else {
					$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}
			}
			
			
		}
			
		
	});
	
	tmeModuleApp.controller('demopageController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$rootScope) {
		
		if($stateParams.demo_flg == '' || $stateParams.demo_flg == undefined) {
			$rootScope.demo_flag = 1;
		}else{
			$rootScope.demo_flag = $stateParams.demo_flg;
		}
		
		$scope.gotoomnidemo = function() {
			$rootScope.demo_flag=3;
		}
		
		$scope.showlivephoneserach = function() {
			$window.open('../business/livebannerdemo.php?parentid='+$stateParams.parid+'&module=me&data_city='+DATACITY+'&banner_type=13', '_blank');
		}
		
		$scope.switch_demo = function(type) {
			if($rootScope.demo_flag == "jdsearch_demo")
				var temp = 3;
			else 
				var temp = $rootScope.demo_flag;
				$rootScope.demo_flag = type;
			/*if(temp > type) {
				$rootScope.demo_flag = type;
			}*/
		}
		
		
		$scope.skip_demo = function(){
			$state.go('appHome.pricechartnew',{parid:$stateParams.parid,ver:$stateParams.ver,page:$stateParams.page});
		}
	});
	
	tmeModuleApp.controller('jdpayController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$rootScope) {
		$rootScope.extraHandler = $stateParams.page;
		
		$scope.gotojdserach = function() {
			$rootScope.demo_flag=4;
		}
		
		$scope.sendpaymentlink = function(ev) {
			
			APIServices.checklive($stateParams.parid).success(function(response) {
				if(response.errorCode == 0){
					$rootScope.jdpaycompanyname = response.value.companyname;
					$mdDialog.show({
						controller: sendjdpaycontroller,
						templateUrl: 'partials/sendjdpaylink.html',
						parent: angular.element(document.body),
						targetEvent: ev,
					})
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'You cannot send JD Pay Link. Data is not present on live!';
				}
			});
		}
			
			
		function sendjdpaycontroller($scope,$mdToast) {
			$scope.jdpay_checked = {};
			$scope.jdpay_checked['mob'] = '';
			$scope.jdpay_checked['email'] = '';
			
			APIServices.getowndomainname($stateParams.parid,$stateParams.ver).success(function(response) {
				$scope.mobile_arr = [];
				$scope.email_arr = [];
				
				if(response.error.code == 0) {
					$scope.mobile_arr= response.error.result.mobile.split(',');
					$scope.email_arr= response.error.result.email.split(',');
				}else{
					$scope.mobile_arr[0] = "";
					$scope.email_arr[0] = "";
				}
				
			});
			
			$scope.closepopup = function() {
				$mdDialog.hide();
			}
			
			$scope.send_link = function(ev) {
				if($scope.jdpay_checked['mob'] == "") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'Please select a mobile number';
					return false;
				}else{
					APIServices.SendJDpaysmsemail($stateParams.parid,$scope.jdpay_checked['mob'],$scope.jdpay_checked['email'],$rootScope.jdpaycompanyname,UNAME).success(function(response) {
						if(response == 1){
							$mdDialog.hide();
							$state.go('appHome.ecsform',{parid:$stateParams.parid,ver:$stateParams.ver,ecsflg:'4',page:$rootScope.extraHandler});
						}else{
							$mdToast.show(
								$mdToast.simple()
								.content('please select a mobile number')
								.position('top left')
								.hideDelay(4000)
							);
						}
					});
				}
			}
			
		}
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
		
	});
	
	tmeModuleApp.controller('jdrrdemoController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$rootScope) {
		$scope.show_listing = 'rating';
		$scope.companyname = '';
		$scope.show_jdrr_samaple = 1;
		$scope.img_position = 0
		
		APIServices.checklive($stateParams.parid).success(function(response) {
			if(response.errorCode == 0){
				$scope.companyname = response.value.companyname;
				$scope.review = response.value.d_web_review;
				$scope.rating = response.value.d_rating;
			}
		});
		
		$scope.jdrr_slider_nxt = function() {
			if($scope.img_position > -1998){
				$scope.img_position = $scope.img_position - 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.img_position+'px, 0, 0)'})
				$scope.show_jdrr_samaple = $scope.show_jdrr_samaple + 1
			}
		}
		
		$scope.jdrr_slider_prev = function() {
			if($scope.img_position < 0){
				$scope.img_position = $scope.img_position + 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.img_position+'px, 0, 0)'})
				$scope.show_jdrr_samaple = $scope.show_jdrr_samaple - 1;
			}
		}
		
		$scope.gotobannerdemo = function() {
			$rootScope.demo_flag=6;
		}
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
	});
	
	
	tmeModuleApp.controller('bannerdemoController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast,$rootScope) {
		$scope.show_listing = 'category';
		$scope.show_sample_banner = 1;
		$scope.position = 0;
		
		$scope.swith_option = function(type) {
			$scope.show_listing = type;
			$scope.show_img = '';
			$scope.show_sample_banner = 1;
			$scope.position = 0;
		}
		
		$scope.banner_slider_nxt = function() {
			if($scope.position > -3996){
				$scope.position = $scope.position - 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.position+'px, 0, 0)'})
				$scope.show_sample_banner = $scope.show_sample_banner + 1
			}
		}
		
		$scope.banner_slider_prev = function() {
			if($scope.position < 0){
				$scope.position = $scope.position + 999;
				$('.bxslider').css({transform: 'translate3d('+$scope.position+'px, 0, 0)'})
				$scope.show_sample_banner = $scope.show_sample_banner - 1;
			}
		}
		
		$scope.gotonxtdemo = function() {
			if($scope.show_img == "category_sample" || $scope.show_img == "competitor_sample"){
				$rootScope.demo_flag=5;
			}else if($scope.show_listing == 'category'){
				$scope.show_img = "category_sample";
			}else if($scope.show_listing == 'competitor'){
				$scope.show_img = "competitor_sample";
			}
		}
		
		
		$scope.slide_menu = function() {
			if($('.right-slidbx').hasClass('slide'))
			{
				$('.right-slidbx').removeClass('slide').addClass('hide-slide');
			}		
			else
			{
			   $('.right-slidbx').addClass('slide').removeClass('hide-slide');
			}
		};	
		
		
	});
	
	
	tmeModuleApp.controller('pricechartnewController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$cookieStore.remove("selected_option");
		$cookieStore.remove("payment_type");
		$cookieStore.remove("campaign_names");
		$cookieStore.remove("campaign_str");
		$cookieStore.remove("banner_rotation");
		$cookieStore.remove("flexi_bud");
        	$cookieStore.remove("flexi_tenure");
       	$cookieStore.put('version',$stateParams.ver);
		$rootScope.pricechart_rb = {};
		$rootScope.pricechart_rb['payment_type'] = '';
		$rootScope.pricechart_rb['package_tenure'] = 12;
        $rootScope.pricechart_rb['premium_tenure'] = 24;
        $rootScope.pricechart_rb['national_tenure'] = 365;
        $rootScope.pricechart_rb['combo_tenure'] = 12;
        $rootScope.pricechart_rb['discount_type'] = "";
		$scope.disable_cb = {};
		$scope.tenurenational='365';
		$rootScope.pricechart_rb['discount_type'] = "";
		$rootScope.extraHandler = $stateParams.page;
		$scope.edit_price = false;
		$scope.edit_price_value = false;
		$scope.reduce_price = 0;
		$scope.show_package_option = true;	
		$scope.show_omni_option = false;
       	$scope.show_banner_option = true;
		$scope.pc_discount = {};
		$scope.pc_discount_ecs = {};
		$scope.pc_discount_2yr = {};
		$scope.pc_actual_2yr = {};
		$scope.pc_actual = {};
		$scope.pc_pck_actual = {};
		$scope.pc_pck_discount = {};
		$scope.live_data = {};
		$scope.pc_actual_ecs = {};
		$scope.campaign_name = {};
		$scope.pricechart_cb = {};
		$scope.pricechat = {};
		$scope.sPriceChat = {};
		$scope.pricechat_model = {};
        $scope.pricechat_model.banner_rotation = 1;
        $scope.campaign_order = ['119','118','1','111','112','113','114','115','116','117','2','746','743','742','745','73','734','731','732','735','736','737','740','741','744','748','5','225','22','10'];
        $scope.custom_campaign_upfront = ['1','111','112','114','115','116','73','5','75','731_setup','735_setup','735','73_setup','10','731','736','737'];
        $scope.custom_campaign_ecs = ['111','112','114','115','116','5','75','735','10','731','736','737'];
       	$scope.custom_campaign_upfront_dis = ['111','115','116','114','731','736','737'];
		$scope.upfront_notallowed =  [];
        $scope.ecs_notallowed =  ['732','113','1','742','743','748'];
       	$scope.active_campaign = 0;
       	$scope.disable_mini =0;
       	$scope.block_ecs = 0;
       	$scope.editcontract = 0; 
	$scope.apply_dis = {};
        $scope.discount_applied =0;
		$scope.nxt_clicked = 0;
		$scope.omni_present = 0;
		$scope.existing_contract = 0;
		 $scope.block_pck_10yr = 0;
       	
       	if(DATACITY!=''){ //make api servcie to check fr one_plus flag
		   APIServices.check_one_plus_block(DATACITY).success(function(response) {			  
				if(response.data.code == 0){ 
					if(response.data.one_plus==1){
						$scope.block_2yr = 1;
					}else{
						$scope.block_2yr = 0;
					}
				}else {
					$scope.block_2yr = 0;
				}
			});		
		}  
       	
		if($rootScope.extraHandler ==  "jda") {
			var module_name = "jda";
		}else {
			var module_name = "tme";
		}
		
		
		$timeout(function(){
			if($rootScope.employees.results.allocId.toLowerCase() == 'sj' || $rootScope.employees.results.secondary_allocID.toLowerCase().indexOf('sj') != -1){
				$scope.disable_mini =1;
			}else{
				$scope.disable_mini =0;
			}
		},500);
							
		APIServices.combopricemin($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.min_combo_bgt =  response.error.msg.min_budget;
			}
		});
		
		APIServices.checkemployeeeligible($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 0){
				$scope.reduce_price = 1;
			}else {
				$scope.reduce_price = 0;
			}
		});
		
		APIServices.tempactualbudgetupdate($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.error.code == 1) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please contact software team";
				return false;    // change alert msg
			}    
		});
		
		//~ APIServices.check_omni_eligible().success(function(response) {
			//~ if(response.errorCode == 0){
				//~ $rootScope.exclude_setupfee = 1;
			//~ }else{
				//~ $rootScope.exclude_setupfee = 0;
			//~ }
        //~ });
		
		var downsell=false;
        APIServices.checkDiscount($stateParams.parid,$stateParams.ver).success(function(response) {

            if(response.error == 1 ) {

            /*  $mdDialog.show(
                      $mdDialog.alert()
                        .parent(angular.element(document.querySelector('#popupContainer')))
                        .clickOutsideToClose(true)
                        .title('')
                        .content('Down Sell Request Present! Cant Proceed')
                        .ariaLabel('Aler t Dialog Demo')
                        .ok('Got it!')
                        .targetEvent(ev)
                    );*/
                downsell=true;

                $('#btnReset').hide();
                $('#next_button').hide();

                //~ alert("Down Sell Request Present! Cant Proceed");
                throw new Error("Down Sell Request Present! Cant Proceed");

            }
        });
        

        APIServices.getmaincampaignids($stateParams.parid,$stateParams.ver).success(function(response) {
			if(response.errorcode == 1){
				$scope.omni_present = 0;
				$scope.existing_contract = 0;
			}else {
				$scope.existing_contract = 1;
				if(response.data.indexOf('72') !=  -1 || response.data.indexOf('73')!= -1 ){
					$scope.omni_present = 1;
				}
			}
		});
		
		
		APIServices.newpricechatval($stateParams.parid,$stateParams.ver,1).success(function(response) {
			if(response.errorCode == 0) {
				$scope.pricechat 	= 	response.data.standard;
				$scope.sPriceChat 	= 	response.present;
				$scope.block_pck_10yr = response.data.activephonesearchcontractflag;
				 angular.forEach($scope.pricechat,function(main_value,main_key) {
					angular.forEach(main_value,function(value,key) {
						$scope.disable_cb[key] = false;
						if(value.checked == true && ((key != 111 && $scope.disable_mini == 1) || $scope.disable_mini == 0)) {
							$scope.pricechart_cb[key] = true;
						}else {
							$scope.pricechart_cb[key] = false;
						}
                        if((key == 51 || key == 52 || key == 53 || key == 54) && value.checked == true){
							$scope.pricechart_cb[5] = true;
							$timeout(function(){
							$('.banner').attr('disabled','disabled');
							$('.banner').addClass('pointer_dis');
							$('#'+key).removeClass('pointer_dis');
							$('#'+key).removeAttr('disabled');
							},500);
							
							$scope.pc_actual['5'] 	=  $scope.pricechat.Banner[key].price_upfront;
							$scope.pc_discount['5'] =  $scope.pricechat.Banner[key].price_upfront;
							
							$scope.pc_discount_ecs['5'] =  $scope.pricechat.Banner[key].price_ecs;
							$scope.pc_actual_ecs['5'] 	=   $scope.pricechat.Banner[key].price_ecs;
							
						}
						$scope.pc_actual[key] 	    =  value.price_upfront;
						$scope.pc_discount[key]	    =  value.price_upfront;
						$scope.pc_actual_ecs[key]   =  value.price_ecs;
						$scope.pc_discount_ecs[key] =  value.price_ecs;
						$scope.pc_discount_2yr[key] =  value.price_upfront_two_years;
						$scope.pc_actual_2yr[key] 	=  value.price_upfront_two_years;
						$scope.pc_pck_actual[key] 	=  value.price_upfront_discount;
						$scope.pc_pck_discount[key] =  value.price_upfront_discount;
						$scope.campaign_name[key]   = value.name;
						$scope.campaign_name['2']	=	'Fixed Position';
						$scope.campaign_name['1']	=	'Package Expired';
						
						if(key == '10')
						{
							$scope.live_data[key]		= value.live;
							$scope.change_state			= value.state_change;
						}
						if( typeof $scope.pricechat['Omni'] != "undefined" && ((typeof $scope.pricechat['Omni']['740'] != "undefined" && key == 740) || (typeof $scope.pricechat['Omni']['741'] != "undefined" && key == 741))) {
							$scope.pc_actual_ecs[key+'_setup']   =  value.down_payment.toString();
							$scope.pc_discount_ecs[key+'_setup'] =  value.down_payment.toString();
						}if(typeof $scope.pricechat['Banner'] != "undefined"){
							$scope.campaign_name[5]   = "banner";	
						}
						
						if(key == 51 && value.banner_rules.no_of_rotation != '' && value.banner_rules.no_of_rotation != undefined) {
							if(value.banner_rules.no_of_rotation > 4){
								value.banner_rules.no_of_rotation = 4;
							} 
							$scope.pricechat_model.banner_rotation = value.banner_rules.no_of_rotation;
						}
						if($scope.pricechat.Banner[51] != undefined && $scope.pricechat.Banner[51].banner_rules.rotation_avl == false) {
							$timeout(function(){
								$('.banner').attr('disabled','disabled');
								$('.banner').addClass('pointer_dis');
								$('#51').removeClass('pointer_dis');
								$('#51').removeAttr('disabled');
							},500);
						}
						$scope.campaign_name[key] = value.name;
					}); 
				 });
				
				if(response.data.discount != undefined && response.data.discount.team_dis != undefined){
					$scope.pck1yrlast = response.data.discount.team_dis;
					$scope.editcontract = 1; 
				}
						
				angular.forEach($scope.pricechart_cb,function(value,key) {
					if($scope.pricechart_cb[key] ==  true)
					$scope.disable_campaign(key,'','');
				});
						
				if(response.data.instrument_type != '' && response.data.instrument_type != undefined) {
					$rootScope.pricechart_rb['payment_type'] = response.data.instrument_type ;
					$scope.setpayment_type(response.data.instrument_type);
					if(response.data.instrument_type == "upfront") {
						$rootScope.pricechart_rb['discount_type'] ="original";
					}
				}
				
				$scope.$watch(function(scope) { return $rootScope.premium_bgt },
				  function(newValue, oldValue) {
						if($rootScope.bestBudget.error.code != 1) {
							$rootScope.rw_mini_bdt = $rootScope.bestBudget.result.package_mini;
							$rootScope.rw_premiun_bdt = Math.round($rootScope.bestBudget.result.package_premium/12);
							$rootScope.package_dp = 3;
                            if($rootScope.bestBudget.result.active_campaign == 1) {
									$scope.active_campaign = 1;
									$rootScope.pricechart_rb['premium_tenure'] =12;
							}
							
							$scope.maxrnwmini =  $rootScope.bestBudget.result.maxrnwmini;
							$scope.maxrnwbasic =  $rootScope.bestBudget.result.maxrnwbasic;
							$scope.maxrnwpremium =  $rootScope.bestBudget.result.maxrnwpremium;
							$scope.cstm_minbudget_package =  $rootScope.bestBudget.result.cstm_minbudget_package;
							
							$scope.pc_actual['1'] =  $rootScope.premium_bgt*12;
							$scope.pc_actual['111'] =  $rootScope.rw_mini_bdt;
                           
							
							$scope.pc_discount_ecs['1'] =   $rootScope.premium_bgt;
							$scope.pc_actual_ecs['1'] =   $rootScope.premium_bgt;
							$scope.pc_discount['1'] =   $rootScope.premium_bgt * 12;
							
							
                           				$scope.pc_discount_ecs['111'] =   $rootScope.bestBudget.result.package_mini_ecs;
                           				$scope.pc_actual_ecs['111'] =   $rootScope.bestBudget.result.package_mini_ecs;
							$scope.pc_discount['111'] =   $rootScope.rw_mini_bdt;
							
							$scope.pc_discount_ecs['112'] =   $rootScope.rw_premiun_bdt;
							$scope.pc_actual_ecs['112'] =   $rootScope.rw_premiun_bdt;
                           
							if($rootScope.bestBudget.result.rnwcstmminpckbgt1yr != undefined && $rootScope.bestBudget.result.rnwcstmminpckbgt1yr != 0) {
								if($scope.pck1yrlast == undefined || $scope.pck1yrlast > $rootScope.bestBudget.result.rnwcstmminpckbgt1yr) {
									$scope.pck1yrlast = $rootScope.bestBudget.result.rnwcstmminpckbgt1yr;
								}
								$scope.editcontract = 1; 
							}
							
							if(typeof $scope.pricechat['Package']['1'] !== 'undefined') {
								if(typeof $rootScope.bestBudget.result.expiredePackval !== undefined && $rootScope.bestBudget.result.expiredePackval != 0) {
									$scope.pricechat['Package']['1']['price_upfront']    = $rootScope.bestBudget.result.expiredePackval;
									$scope.pricechat['Package']['1']['price_upfront_two_years'] = $rootScope.bestBudget.result.expiredePackval_2yrs;
									//~ $scope.pricechat['Package']['1']['price_ecs']        = $rootScope.bestBudget.result.expiredePackval / 12;
									//~ $scope.pricechat['Package']['1']['down_payment']     = $scope.pricechat['Package']['1']['price_ecs'] * $scope.package_dp;
									$scope.pricechat['Package']['1']['price_ecs']        = "-";
									$scope.pricechat['Package']['1']['down_payment']     = "-";
									$scope.pc_actual['1']       =   $scope.pricechat['Package']['1']['price_upfront'];
									$scope.pc_actual_ecs['1']   =  $scope.pricechat['Package']['1']['price_ecs'];
									$scope.pc_discount['1']     =   $scope.pricechat['Package']['1']['price_upfront'];
									$scope.pc_discount_ecs['1'] =   $scope.pricechat['Package']['1']['price_ecs'];
									$scope.pc_discount_2yr['1'] = $rootScope.bestBudget.result.expiredePackval_2yrs;
									$scope.pc_actual_2yr['1'] = $rootScope.bestBudget.result.expiredePackval_2yrs;
								}else{
								$scope.pricechat['Package']['1']['price_upfront']  	 = $rootScope.premium_bgt * 12;
								$scope.pricechat['Package']['1']['down_payment'] 	 =  $rootScope.premium_bgt * $scope.package_dp;
								$scope.pricechat['Package']['1']['price_ecs'] 		 = $rootScope.premium_bgt;
							}
                            }
							
							if(typeof $scope.pricechat['Package']['111'] !== 'undefined') {
								$scope.pricechat['Package']['111']['price_upfront']  = $rootScope.rw_mini_bdt;
                                $scope.pricechat['Package']['111']['price_ecs']      = $rootScope.bestBudget.result.package_mini_ecs;
                                $scope.pricechat['Package']['111']['down_payment']   = $scope.pricechat['Package']['111']['price_ecs'] * 12;
                                $scope.pricechat['Package']['111']['price_upfront_discount'] = $rootScope.bestBudget.result.price_mini_upfront_discount;
                                $scope.pricechat['Package']['111']['price_upfront_two_years'] = $rootScope.bestBudget.result.price_mini_upfront_two_years;
                                //~ if($scope.pricechat['Package']['111']['checked'] == true){
									//~ $scope.edit_price = true;
								//~ }
								$scope.pc_discount_2yr["111"] 	= $rootScope.bestBudget.result.price_mini_upfront_two_years;
								$scope.pc_actual_2yr["111"] 	= $rootScope.bestBudget.result.price_mini_upfront_two_years;
								$scope.pc_pck_actual["111"] 	= $rootScope.bestBudget.result.price_mini_upfront_discount;
								$scope.pc_pck_discount["111"] 	= $rootScope.bestBudget.result.price_mini_upfront_discount;  
							}
							
                            if(typeof $scope.pricechat['Package']['112'] !== 'undefined') {  //package_premium_upfront
								$scope.pricechat['Package']['112']['price_upfront']  = $rootScope.bestBudget.result.package_premium_upfront;
								$scope.pricechat['Package']['112']['down_payment'] 	 =  $rootScope.rw_premiun_bdt * $scope.package_dp;
								$scope.pricechat['Package']['112']['price_ecs'] 	 = $rootScope.rw_premiun_bdt;
								$scope.pricechat['Package']['112']['price_upfront_discount'] = "-";
                                $scope.pricechat['Package']['112']['price_upfront_two_years'] = $rootScope.bestBudget.result.package_premium_upfront;
                                $scope.pc_discount['112'] =  $rootScope.bestBudget.result.package_premium_upfront;
                                $scope.pc_actual['112']   =  $rootScope.bestBudget.result.package_premium_upfront;
                                $scope.pc_discount_2yr["112"] 	= $rootScope.bestBudget.result.package_premium_upfront;
								$scope.pc_actual_2yr["112"] 	= $rootScope.bestBudget.result.package_premium_upfront;
								$scope.pc_pck_actual["112"] 	= "-";
								$scope.pc_pck_discount["112"] 	= "-";
							}
							
							if(typeof $scope.pricechat['Normal']['10'] !== 'undefined') {  //package_premium_upfront
								$scope.upfront_national = $rootScope.bestBudget.result.upfront_national_budget;
								$scope.pricechat['Normal']['10']['price_upfront']  = $rootScope.bestBudget.result.upfront_national_budget;
                                $scope.pricechat['Normal']['10']['down_payment']   =  $rootScope.bestBudget.result.monthly_national_budget * $scope.package_dp;
                                $scope.pricechat['Normal']['10']['price_ecs']      = $rootScope.bestBudget.result.monthly_national_budget;
                                $scope.pricechat['Normal']['10']['price_upfront_discount'] = Math.ceil($rootScope.bestBudget.result.upfront_national_budget * (1- 0.15));
                                $scope.pricechat['Normal']['10']['price_upfront_two_years'] = Math.ceil($rootScope.bestBudget.result.upfront_national_budget * $rootScope.bestBudget.result.increment_factor);
                                $rootScope.national_discount = $scope.pricechat['Normal']['10']['price_upfront_discount'];
                                $rootScope.national_2yr_add_discount = $scope.pricechat['Normal']['10']['price_upfront_two_years']
                                $scope.pricechat['Normal']['10']['price_upfront_discount_2yr']  = $rootScope.bestBudget.result.upfront_national_budget*2;
                                if($scope.change_state == 1)
                                {
									$scope.pricechart_cb['10']      =  true;
								}
                                
                            }
							
							
							
						}else {
							$mdToast.show(
								$mdToast.simple()
								.content($rootScope.bestBudget.error.msg)
								.position('top right')
								.hideDelay(3000)
							);
						}
					}
				);
		
		
			}else{
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Try again';
			}
		});
		
		
		APIServices.check_ecs($stateParams.parid,$stateParams.ver,module_name).success(function(response) { 
			if(response.error.code == 1 || response.error.code == 7) {
				$scope.ecs_flg = 1;
				$rootScope.pricechart_rb['payment_type'] = 'ecs';
				$cookieStore.put('payment_type','ecs');
				$mdToast.show(
					$mdToast.simple()
					.content('Ongoing ECS contract.')
					.position('top right')
					.hideDelay(3000)
				);	
			}
			
			if(response.error.code == 6 || response.error.code == 7){
				$scope.block_ecs = 1;
			}
		});
		
		APIServices.getnationalflag($rootScope.parentid,DATACITY).success(function(response) {
			$scope.getnationallistingflag	=	response.nationallisting;
			$scope.getnationallistingType	=	response.nationallisting_type;
			$scope.nationallistingeligible	=	response.eligible_flag;
		});

		$scope.package_tenure_change = function(){
			 angular.forEach($scope.pricechat.Package,function(value,key) {
				 if($rootScope.pricechart_rb['package_tenure'] == 24) {
					$scope.pc_discount[key] = value.price_upfront_two_years;
					$scope.pc_actual[key] = value.price_upfront_two_years;
				}else if($rootScope.pricechart_rb['package_tenure'] == 12){
					 $scope.pc_discount[key] = value.price_upfront;
					 $scope.pc_actual[key] = value.price_upfront;
				}
			});
			
			 if($rootScope.pricechart_rb['package_tenure'] == 24) {
				 $scope.pc_discount['111'] =  $rootScope.bestBudget.result.price_mini_upfront_two_years;
				 $scope.pc_actual['111'] =  $rootScope.bestBudget.result.price_mini_upfront_two_years;
			 }else if($rootScope.pricechart_rb['package_tenure'] == 12){
				  $scope.pc_discount['111'] =  $rootScope.rw_mini_bdt;
				  $scope.pc_actual['111'] =  $rootScope.rw_mini_bdt;
			 }
				
		}
		
		
		$scope.set_tenure = function(tenure){
			$rootScope.pricechart_rb['package_tenure'] = tenure
			$rootScope.pricechart_rb['payment_type'] ="upfront";
			$cookieStore.put('payment_type','upfront');
		}
		
		
		$rootScope.combo_comfirmation = function(type){
			$scope.temp_arr =  $scope.selected_campaigns.split(',');;
			$scope.temp_arr['0'] = 1;
			$scope.replaced_str = $scope.temp_arr.join();
			APIServices.omnicatlog($stateParams.parid,$scope.replaced_str,$scope.selected_campaign_names,type,$rootScope.pricechart_rb['payment_type']).success(function(response) {
				if(response.error_code == 0){
					if(type == 0){
						if($rootScope.pricechart_rb['payment_type'] == 'upfront'){
							$scope.pck_combo = $scope.pc_discount[$scope.selected_arr['0']];
							if($rootScope.pricechart_rb['package_tenure'] == 24 && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pc_discount_2yr[$scope.selected_arr['0']] != undefined){
								$scope.pck_combo = $scope.pc_discount_2yr[$scope.selected_arr['0']];
								$rootScope.premium_2years = 1;
								$scope.selected_campaign_names = $scope.selected_campaign_names.replace("combo1_2yr_dis","pck_2yr_dis");
							}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['discount_type'] == "discount" && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pc_pck_discount[$scope.selected_arr['0']] != undefined){
								$scope.pck_combo = $scope.pc_pck_discount[$scope.selected_arr['0']];
								$scope.selected_campaign_names = $scope.selected_campaign_names.replace("combo1_1yr_dis","pck_1yr_dis");
							}
						}else if($rootScope.pricechart_rb['payment_type'] == 'ecs'){
							$scope.pck_combo = $scope.pc_discount_ecs[$scope.selected_arr['0']] * 12;
						}
						$scope.selected_campaigns = $scope.selected_campaigns.replace($scope.selected_arr['0'],115)
						$cookieStore.put('campaign_str', $scope.selected_campaigns);
						$cookieStore.put('campaign_names', $scope.selected_campaign_names);
						APIServices.payment_type($stateParams.parid,$scope.selected_campaign_names,$stateParams.ver,$rootScope.pricechart_rb['payment_type']).success(function(response) {
							if(response.error_code == 0) {
						if($scope.selected_arr.indexOf("10") != -1){
							$scope.submitBudget($scope.pck_combo,"appHome.nationallisting");
						}else{
							$scope.submitBudget($scope.pck_combo,"appHome.showExistInventory");
						}
						$cookieStore.put('selected_option','');
						$rootScope.showCommonPop = '';
							}
						});
					}else{
						$scope.selected_campaigns =$scope.selected_campaigns.replace($scope.selected_arr['0'],"731");
						$cookieStore.put('campaign_str', $scope.selected_campaigns);
						if($scope.selected_arr.indexOf("10") != -1){
							$state.go('appHome.nationallisting',{parid:$stateParams.parid,type:'nationallisting',ver:$stateParams.ver,page:$rootScope.extraHandler});
						}else{
							$rootScope.targetUrl    =   "appHome.omnidomainreg";
							$rootScope.submitFinalCats();
						}
						$rootScope.showCommonPop = '';
					}
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Log not updated";
					return false;
				}
			});
			
		}
		
		$scope.change_banner = function(ev){
			if($scope.pricechat_model.banner_rotation == 0){
				return false;
			}
			
			$scope.banner_rotation_mul = 1+parseInt($scope.pricechat_model.banner_rotation);
			if($scope.pricechat_model.banner_rotation == 1) {
				$scope.pricechat.Banner['54'].price_upfront =  $scope.pricechat.Banner['51'].banner_rules.banner_single_unit;
			}else if($scope.pricechat_model.banner_rotation <= $scope.pricechat.Banner['51'].banner_rules.first_slab_upper) {
				$scope.pricechat.Banner['54'].price_upfront =  $scope.pricechat.Banner['51'].banner_rules.banner_upto_10 * $scope.banner_rotation_mul;
			}else{
				$scope.pricechat.Banner['54'].price_upfront =  $scope.pricechat.Banner['51'].banner_rules.banner_above_10 * $scope.banner_rotation_mul;
			}
			$scope.pricechat.Banner['54'].price_ecs = $scope.pricechat.Banner['51'].banner_rules.banner_ecs_per_rotation * $scope.banner_rotation_mul ;
			$scope.pricechat.Banner['54'].down_payment =  $scope.pricechat.Banner['54'].price_ecs * 3;
			
			$scope.pc_actual['5'] 	=  $scope.pricechat.Banner['54'].price_upfront;
			$scope.pc_discount['5'] =  $scope.pricechat.Banner['54'].price_upfront;
			
            $scope.pc_discount_ecs['5'] =  $scope.pricechat.Banner['54'].price_ecs;
            $scope.pc_actual_ecs['5'] 	=   $scope.pricechat.Banner['54'].price_ecs;
        } 
		
		$scope.disable_campaign = function(key,type,ev) {
			
			if(key == '10' && ($scope.live_data['10'] == false || $scope.change_state == 1))
             {
			
                 $scope.pricechart_cb['10'] = true ;
                 return false;
             }

				if(key == '10') {
					if($rootScope.pricechart_rb['payment_type'] == 'ecs') {

							APIServices.calcupdatedatanational($rootScope.parentid,DATACITY,$scope.nationallistingfinal_ecs*12,$rootScope.pricechart_rb['national_tenure'],'0').success(function(response) {

								});
							}
						else if($rootScope.pricechart_rb['payment_type'] == 'upfront') {
							if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'original')
							{

								$scope.nationallistingfinal_upfront_pay = $rootScope.bestBudget.result.upfront_national_budget;

							}
							if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'discount')
							{
								$scope.nationallistingfinal_upfront_pay = $rootScope.national_discount;

							}
							if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'two_year')
							{
								$scope.nationallistingfinal_upfront_pay = $rootScope.national_2yr_add_discount;

								$rootScope.pricechart_rb['national_tenure'] = 730;
							}
							APIServices.calcupdatedatanational($rootScope.parentid,DATACITY, $scope.nationallistingfinal_upfront_pay,$rootScope.pricechart_rb['national_tenure'],'0').success(function(response) {
							//  console.log(response);
								});
							}
				}
				/*
			if(DATACITY.toLowerCase() == 'ahmedabad' || DATACITY.toLowerCase() == 'hyderabad' || DATACITY.toLowerCase() == 'delhi' || DATACITY.toLowerCase() == 'kolkata' || DATACITY.toLowerCase() == 'chandigarh' ){
				if($scope.pricechart_cb[1] ==true){
					$scope.block_2yr =0;
				}else {
					if($rootScope.pricechart_rb['discount_type'] == 'two_year') {
						$rootScope.pricechart_rb['discount_type'] = 'original';
						$scope.pricechart_rb['package_tenure'] = '12';
					}
					
					$scope.block_2yr =1;
				}
			}*/
			
			if(key == '225') {
				if($scope.ecs_flg != 1 || type != "upfront"){
					$scope.disable_cb['5'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['51'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['52'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['53'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['54'] = $scope.pricechart_cb[key];
				}
				$scope.disable_cb['22'] = $scope.pricechart_cb[key] ;
                if($scope.pricechart_cb['2'] == false && ($scope.pricechart_cb['1'] == undefined || $scope.pricechart_cb['1'] == false) && ($scope.pricechart_cb['111'] == undefined || $scope.pricechart_cb['111'] == false) && ($scope.pricechart_cb['112'] == undefined || $scope.pricechart_cb['112'] == false) && ($scope.pricechart_cb['113'] == undefined || $scope.pricechart_cb['113'] == false)  && ($scope.pricechart_cb['114'] == undefined || $scope.pricechart_cb['114'] == false)  && ($scope.pricechart_cb['115'] == undefined || $scope.pricechart_cb['115'] == false)  && ($scope.pricechart_cb['116'] == undefined || $scope.pricechart_cb['116'] == false)  && ($scope.pricechart_cb['117'] == undefined || $scope.pricechart_cb['117'] == false) && ($scope.pricechart_cb['118'] == undefined || $scope.pricechart_cb['118'] == false) && ($scope.pricechart_cb['731'] == undefined || $scope.pricechart_cb['731'] == false) && ($scope.pricechart_cb['732'] == undefined || $scope.pricechart_cb['732'] == false) && ($scope.pricechart_cb['734'] == undefined || $scope.pricechart_cb['734'] == false) && ($scope.pricechart_cb['735'] == undefined || $scope.pricechart_cb['735'] == false) && ($scope.pricechart_cb['736'] == undefined || $scope.pricechart_cb['736'] == false) && ($scope.pricechart_cb['737'] == undefined || $scope.pricechart_cb['737'] == false) && $scope.pricechart_cb['73'] == false){
						$scope.disable_cb['735'] = $scope.pricechart_cb[key];
				}
				
				if($scope.pricechart_cb['225'] == false) {
					APIServices.deletebannerLive($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
					
					APIServices.deletejdrrLive($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
					
				}
			}else if(key == '5' || key == '22') {
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true) {
					$scope.disable_cb['735'] = true ;
					$scope.disable_cb['225'] = true ;
                }else if(($scope.pricechart_cb['5'] == false || $scope.pricechart_cb['5'] == undefined ) && $scope.pricechart_cb['22'] == false) {
					$scope.disable_cb['225'] = false;
                   
                    if($scope.pricechart_cb['2'] == false && ($scope.pricechart_cb['1'] == undefined || $scope.pricechart_cb['1'] == false) && ($scope.pricechart_cb['111'] == undefined || $scope.pricechart_cb['111'] == false) && ($scope.pricechart_cb['112'] == undefined || $scope.pricechart_cb['112'] == false) && ($scope.pricechart_cb['113'] == undefined || $scope.pricechart_cb['113'] == false)  && ($scope.pricechart_cb['114'] == undefined || $scope.pricechart_cb['114'] == false)  && ($scope.pricechart_cb['115'] == undefined || $scope.pricechart_cb['115'] == false)  && ($scope.pricechart_cb['116'] == undefined || $scope.pricechart_cb['116'] == false)  && ($scope.pricechart_cb['117'] == undefined || $scope.pricechart_cb['117'] == false) && ($scope.pricechart_cb['118'] == undefined || $scope.pricechart_cb['118'] == false) && ($scope.pricechart_cb['731'] == undefined || $scope.pricechart_cb['731'] == false) && ($scope.pricechart_cb['732'] == undefined || $scope.pricechart_cb['732'] == false) && ($scope.pricechart_cb['734'] == undefined || $scope.pricechart_cb['734'] == false) && ($scope.pricechart_cb['735'] == undefined || $scope.pricechart_cb['735'] == false) && ($scope.pricechart_cb['736'] == undefined || $scope.pricechart_cb['736'] == false) && ($scope.pricechart_cb['737'] == undefined || $scope.pricechart_cb['737'] == false) && $scope.pricechart_cb['73'] == false){
						$scope.disable_cb['735'] = false;
					}
				}
				if($scope.pricechart_cb['5'] == false  && key == '5') { 
					APIServices.deletebannerLive($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
				}else if($scope.pricechart_cb['22'] == false && key == '22') {
					APIServices.deletejdrrLive($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
				}
            }else if(key == '1' || key == '2' || key == '112' || key == '111' || key == '113' || key == '114' || key == '115' || key == '116' || key == '117' || key == '118' || key == '119' ) {
				if(key == '1'){
					$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				}else if(key == '2') {
					$scope.disable_cb['1'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				}else if(key == '112') {
					$scope.disable_cb['1'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				}else if(key == '111') {
					$scope.disable_cb['1'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
					$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
                }else if(key == '113') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
                }else if(key == '114') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
                }else if(key == '115') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
                }else if(key == '116') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
                }else if(key == '117') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				}else if(key == '118') {
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				}else if(key == '119') {
					if($scope.block_pck_10yr == 1){
						$scope.pricechart_cb[key] = false;
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "Campaign is not available for selection for this client";
						return false;
					}
                    $scope.disable_cb['1'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
				}
				
				if($scope.pricechart_cb['73'] == true || $scope.pricechart_cb['734'] == true ) {
					$scope.disable_cb['732'] = true; 
					$scope.disable_cb['731'] = true; 
					$scope.disable_cb['735'] = true; 
                    $scope.disable_cb['736'] = true;
                    $scope.disable_cb['737'] = true;
				}else{
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key];  
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key];
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true) {
					$scope.disable_cb['735'] = true;
				}
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletecampaign($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
				}
				
            }else if(key == '731' || key == '732' || key == '735' || key == '736' || key == '737'  ) {
				$scope.disable_cb['1'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['111'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['112'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['113'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['114'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['115'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['116'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['117'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['118'] = $scope.pricechart_cb[key] ;
                $scope.disable_cb['119'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['2'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['733'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['748'] = $scope.pricechart_cb[key] ;
				if(key == '731') {
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key];
				}else if(key == '732') {
					$scope.disable_cb['731'] = $scope.pricechart_cb[key];
					$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key];
				}else if(key == '735') {
					$scope.disable_cb['731'] = $scope.pricechart_cb[key];
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['225'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['51'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['52'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['53'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['54'] = $scope.pricechart_cb[key] ;
		    $scope.disable_cb['22'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key] ;
                }else if(key == '736') {
                    $scope.disable_cb['731'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['732'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['735'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key] ;
                }else if(key == '737') {
                    $scope.disable_cb['731'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['732'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['735'] = $scope.pricechart_cb[key] ;
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key] ;
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true ) {
					$scope.disable_cb['735'] = true;
				}
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletecombolive($stateParams.parid,$stateParams.ver).success(function(response) {
						if( key == '732') {
							$scope.ultima_selected = 0;
						}
					});
				}
				
			}else if(key == '73' || key == '734') {
				if(key == '73') {
					$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				}else {
					$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				}
				
				$scope.disable_cb['733'] = $scope.pricechart_cb[key] ;
				
                if($scope.pricechart_cb['1'] == true || $scope.pricechart_cb['2'] == true || $scope.pricechart_cb['112'] == true || $scope.pricechart_cb['111'] == true || $scope.pricechart_cb['113'] == true || $scope.pricechart_cb['114'] == true || $scope.pricechart_cb['115'] == true || $scope.pricechart_cb['116'] == true || $scope.pricechart_cb['117'] == true || $scope.pricechart_cb['119'] == true){
					$scope.disable_cb['732'] = true; 
					$scope.disable_cb['731'] = true; 
					$scope.disable_cb['735'] = true; 
                    $scope.disable_cb['736'] = true;
                    $scope.disable_cb['737'] = true;
				}else {
					$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
					$scope.disable_cb['735'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                    $scope.disable_cb['737'] = $scope.pricechart_cb[key];
				}
				
				if($scope.pricechart_cb['5'] == true || $scope.pricechart_cb['22'] == true || $scope.pricechart_cb['225'] == true) {
					$scope.disable_cb['735'] = true;
				}
				
				
				if($scope.pricechart_cb[key] == false) { 
					APIServices.deletejdomniLive($stateParams.parid,$stateParams.ver).success(function(response) {
						
					});
				}
			}else if(key == '741') {
				$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
                $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                $scope.disable_cb['748'] = $scope.pricechart_cb[key];
			}else if(key == '735' ) {
				$scope.disable_cb['1'] = $scope.pricechart_cb[key];
				$scope.disable_cb['111'] = $scope.pricechart_cb[key];
				$scope.disable_cb['112'] = $scope.pricechart_cb[key];
                $scope.disable_cb['113'] = $scope.pricechart_cb[key];
                $scope.disable_cb['114'] = $scope.pricechart_cb[key];
                $scope.disable_cb['115'] = $scope.pricechart_cb[key];
                $scope.disable_cb['116'] = $scope.pricechart_cb[key];
                $scope.disable_cb['117'] = $scope.pricechart_cb[key];
                $scope.disable_cb['118'] = $scope.pricechart_cb[key];
                $scope.disable_cb['119'] = $scope.pricechart_cb[key];
				$scope.disable_cb['2'] = $scope.pricechart_cb[key];
                $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                $scope.disable_cb['737'] = $scope.pricechart_cb[key];
                $scope.disable_cb['748'] = $scope.pricechart_cb[key];
			}else if(key == '748') {
				$scope.disable_cb['734'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['73'] = $scope.pricechart_cb[key] ;
				$scope.disable_cb['732'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['731'] = $scope.pricechart_cb[key]; 
				$scope.disable_cb['735'] = $scope.pricechart_cb[key]; 
                $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                $scope.disable_cb['736'] = $scope.pricechart_cb[key];
                $scope.disable_cb['741'] = $scope.pricechart_cb[key];
			}
		}
		
		$scope.setpayment_type = function(type) {
			$cookieStore.put('payment_type',type);
			if(type == "ecs") {
				$rootScope.pricechart_rb['discount_type'] = '';
			}
		}
		
        $scope.disable_banner = function(campaign,type,ev){
			
			
			var checkedNum = $('input[name="banner[]"]:checked').length;
			if (checkedNum>0) {
				$('.banner').attr('disabled','disabled');
				$('.banner').addClass('pointer_dis');
				$('#'+campaign).removeAttr('disabled');
				$('#'+campaign).removeClass('pointer_dis');
			}
			else{
				 $('.banner').removeAttr('disabled');
				 $('.banner').removeClass('pointer_dis');
				 $scope.pricechart_cb[5] = false; 
				 $scope.disable_campaign(5,type,ev);
			}
			
			if($scope.pricechart_cb[51] == true){
				$scope.pricechat_model.banner_rotation =1;
				$scope.pc_discount['5'] = $scope.pricechat.Banner[51].price_upfront;
				$scope.pc_discount_ecs['5'] = $scope.pricechat.Banner[51].price_ecs;
				$scope.pc_actual['5'] = $scope.pricechat.Banner[51].price_upfront;
				$scope.pc_actual_ecs['5'] = $scope.pricechat.Banner[51].price_ecs;
				$scope.pricechart_cb[5] = true; 
				$scope.disable_campaign(5,type,ev);
			}else if($scope.pricechart_cb[52] == true){
				$scope.pricechat_model.banner_rotation =2;
				$scope.pc_discount['5'] = $scope.pricechat.Banner[52].price_upfront;
				$scope.pc_discount_ecs['5'] = $scope.pricechat.Banner[52].price_ecs;
				$scope.pc_actual['5'] = $scope.pricechat.Banner[52].price_upfront;
				$scope.pc_actual_ecs['5'] = $scope.pricechat.Banner[52].price_ecs;
				$scope.pricechart_cb[5] = true; 
				$scope.disable_campaign(5,type,ev);
			}else if($scope.pricechart_cb[53] == true){
				$scope.pricechat_model.banner_rotation =3;
				$scope.pc_discount['5'] = $scope.pricechat.Banner[53].price_upfront;
				$scope.pc_discount_ecs['5'] = $scope.pricechat.Banner[53].price_ecs;
				$scope.pc_actual['5'] = $scope.pricechat.Banner[53].price_upfront;
				$scope.pc_actual_ecs['5'] = $scope.pricechat.Banner[53].price_ecs;
				$scope.pricechart_cb[5] = true; 
				$scope.disable_campaign(5,type,ev);
			}else if($scope.pricechart_cb[54] == true){
				$scope.pricechat_model.banner_rotation =4;
				$scope.pc_discount['5'] = $scope.pricechat.Banner[54].price_upfront;
				$scope.pc_discount_ecs['5'] = $scope.pricechat.Banner[54].price_ecs;
				$scope.pc_actual['5'] = $scope.pricechat.Banner[54].price_upfront;
				$scope.pc_actual_ecs['5'] = $scope.pricechat.Banner[54].price_ecs;
				$scope.pricechart_cb[5] = true; 
				$scope.disable_campaign(5,type,ev);
			} 
			
		}

		$scope.open_price = function(ev) {
			if($rootScope.pricechart_rb['payment_type'] == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = 'Please select the payment type';
				return false;
			}
			
			if($scope.discount_applied == 1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Discount is applied custom can not be applied";
				return false;
			}
			
			$scope.edit_price_value = !$scope.edit_price_value;
			$scope.edit_price = !$scope.edit_price;
		}
		
		$scope.call_discount_api = function(ev,key,payment_type,actual_val,discount_val) {
           
           	if(payment_type == "upfront" || payment_type == "upfront_2yr" || payment_type == "upfront_dis" ) {
				$scope.combo1_min = parseInt($scope.min_combo_bgt)*12;
			}else if(payment_type == "ecs") {
				$scope.combo1_min = parseInt($scope.min_combo_bgt);
			}
			
			if(key.lastIndexOf("_setup") != -1) {
				var pos = key.lastIndexOf("_setup");
				var index= key.slice(0,pos);
			}else {
				var index = key;
			}
			
			if($scope.pricechart_cb[index] != true) {
				if(payment_type == "upfront") {
					$scope.pc_discount[key] = $scope.pc_actual[key];
				}else if(payment_type == "ecs"){
					$scope.pc_discount_ecs[key] = $scope.pc_actual_ecs[key];
                		}else if(payment_type == "upfront_2yr"){
					 $scope.pc_discount_2yr[key] = $scope.pc_actual_2yr[key];
				}else if(payment_type == "upfront_dis"){
					 $scope.pc_pck_discount[key] = $scope.pc_pck_actual[key];
				}
                return false;
            }

            if(actual_val == "system budget"){
                $rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = 'Please check your connection and reload the page';
                return false;
            }
			
			if(discount_val == '' || discount_val == undefined){ 
                if(payment_type == "upfront")
                    $scope.pc_discount[key] = $scope.pc_actual[key];
                else if(payment_type == "ecs")
                    $scope.pc_discount_ecs[key] = $scope.pc_actual_ecs[key];
                else if(payment_type == "upfront_2yr")
                    $scope.pc_discount_2yr[key] = $scope.pc_actual_2yr[key];
                else if(payment_type == "upfront_dis")
                    $scope.pc_pck_discount[key] = $scope.pc_pck_actual[key];
                $rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = 'Custom value cant be empty';
                return false;	
			}
			
			if(key == 1) {
               	if($rootScope.expiredePackval != 0 && (payment_type == "upfront" || payment_type == "upfront_2yr")){
					if($rootScope.pricechart_rb['package_tenure'] == 24) { 
						$scope.min_budget_mon = Math.round((parseInt($rootScope.expiredePackval_48)/24),0);
						$scope.min_budget = parseInt($rootScope.expiredePackval_48);
						$scope.entered_val = parseInt(discount_val);
					}else{
						$scope.min_budget_mon = Math.round((parseInt($rootScope.expiredePackval)/12),0);
						$scope.min_budget = $rootScope.expiredePackval;
						$scope.entered_val = parseInt(discount_val)*12;
					}
					if(payment_type == "upfront"){
						$scope.entered_val = parseInt(discount_val);
					}
				}else {  
					if(payment_type == "ecs") {
						if(DATACITY.toLowerCase()	==	"mumbai")
							$scope.min_budget_mon = Math.min(Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0),Math.round((parseInt($scope.cstm_minbudget_package)/12),0),$rootScope.premium_bgt);
						else
							$scope.min_budget_mon = Math.min(Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0),Math.round((parseInt($scope.cstm_minbudget_package)/12),0),$rootScope.premium_bgt,Math.round((parseInt($scope.pck1yrlast)/12),0));
						$scope.min_budget = Math.min(parseInt($rootScope.bestBudget.result.city_bgt),parseInt($scope.cstm_minbudget_package),$rootScope.premium_bgt * 12);
						$scope.entered_val = parseInt(discount_val)*12;
					}else {
						$scope.min_budget_mon = Math.min(Math.round((parseInt($scope.cstm_minbudget_package)/12),0),$rootScope.premium_bgt);
						$scope.min_budget = Math.min(parseInt($scope.cstm_minbudget_package),$rootScope.premium_bgt * 12);
						$scope.entered_val = parseInt(discount_val);
					}
				}
				if(($scope.entered_val < parseInt($scope.min_budget) || parseInt(discount_val)*12 > parseInt($scope.maxrnwbasic)*12) && payment_type == "ecs") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.min_budget_mon+' and lesser than '+$scope.maxrnwbasic;
					$scope.pc_discount_ecs['1'] = $scope.min_budget_mon;
					return false;	
				}else if(($scope.entered_val < parseInt($scope.min_budget) || parseInt(discount_val) > parseInt($scope.maxrnwbasic)*12) && payment_type == "upfront" && $rootScope.expiredePackval == 0) {
                    $rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.min_budget+' and lesser than '+$scope.maxrnwbasic*12;
                    return false;
                }else if(($scope.entered_val < parseInt($scope.min_budget)) && (payment_type == "upfront" || payment_type == "upfront_2yr" ) && $rootScope.expiredePackval != 0) {
                    $rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.min_budget;
			
					if(payment_type == "upfront" )	
						$scope.pc_discount['1'] = $scope.min_budget;
					else if(payment_type == "upfront_2yr" )	
						$scope.pc_discount_2yr['1'] = $scope.min_budget;

                   return false;
               }
			}else if(key == '111'){
				if($scope.editcontract == 1) {
					$scope.oneyrval = Math.min($scope.pck1yrlast,$rootScope.bestBudget.result.package_mini_minimum);
					$scope.twoyrval = actual_val;
				}else {
					$scope.oneyrval = $rootScope.bestBudget.result.package_mini_minimum;
					$scope.twoyrval = actual_val;
				}
				if($scope.pck1yrlast != undefined && $scope.pck1yrlast!= ''  &&  payment_type == "ecs"  && DATACITY.toLowerCase()	!=	"mumbai"){
					actual_val		=	Math.min(actual_val,$scope.pck1yrlast/12);
				}
				if(parseInt(discount_val) < parseInt(actual_val) && payment_type == "ecs")  {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+actual_val;
					$scope.pc_discount_ecs['111'] = actual_val;
					$scope.pricechat['Package']['111']['down_payment'] = actual_val * 12;
					return false; 
                }else if(parseInt(discount_val) < parseInt($scope.twoyrval) && payment_type =="upfront_2yr")  {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.twoyrval;
					$scope.pc_discount_2yr['111'] = $scope.twoyrval;
					return false;
				}else if(parseInt(discount_val) < parseInt($scope.oneyrval) && payment_type == "upfront")  { 
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.oneyrval;
					$scope.pc_discount['111'] = $scope.oneyrval;
					return false;	
                }else if(parseInt(discount_val) < parseInt($rootScope.bestBudget.result.price_mini_upfront_minimum_discount) && payment_type == "upfront_dis")  {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$rootScope.bestBudget.result.price_mini_upfront_minimum_discount;

					$scope.pc_pck_discount['111'] = $rootScope.bestBudget.result.price_mini_upfront_minimum_discount;
					return false;
                }
                
                if(payment_type == "ecs"){
					$scope.pricechat['Package']['111']['down_payment'] = discount_val * 12;
				}
				
			}else if(key == '112'){
				$scope.min_budget_mon = $scope.pc_actual_ecs['112'];
				$scope.min_budget = $scope.pc_actual_ecs['112']*12;
				if($scope.pck1yrlast != undefined && $scope.pck1yrlast!= ''  && payment_type == "ecs"  && DATACITY.toLowerCase()	!=	"mumbai"){
					$scope.min_budget	=	Math.min($scope.min_budget,$scope.pck1yrlast/12);
				}
				if((parseInt(discount_val)*12) < parseInt($scope.min_budget) && payment_type == "ecs") {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+$scope.min_budget_mon;
					$scope.pc_discount_ecs['112'] = $scope.min_budget_mon;
					return false;	
                }else if(parseInt(discount_val) < parseInt(actual_val) && (payment_type == "upfront" || payment_type == "upfront_dis")) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = 'The amount should be greater than '+actual_val;
					
					if(payment_type == "upfront")
						$scope.pc_discount['112'] = actual_val;
					else if(payment_type == "upfront_dis")
						$scope.pc_pck_discount['112'] = actual_val;
                   	return false;
				}else if(parseInt(discount_val) < parseInt($scope.pc_actual_2yr['112']) && payment_type == "upfront_2yr") {
								
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = 'Alert!!';		
						$rootScope.commonShowContent = 'The amount should be greater than '+$scope.pc_actual_2yr['112'];

						$scope.pc_discount_2yr['112'] = $scope.pc_actual_2yr['112'];
						return false;	
				}
			}else if(key == 114 || key == 115 || key == 116){
				if($scope.editcontract == 1) {
					if(payment_type == "ecs"){
					$scope.oneyrval = Math.min(actual_val,$scope.pck1yrlast/12);
					}else {
						$scope.oneyrval = Math.min(actual_val,$scope.pck1yrlast);
					}
					$scope.twoyrval = actual_val;
				}else {
					$scope.oneyrval = actual_val;
					$scope.twoyrval = actual_val;
				}
				if($scope.pck1yrlast != undefined && $scope.pck1yrlast!= '' && payment_type == "ecs"  && DATACITY.toLowerCase()	!=	"mumbai"){
					actual_val	=	Math.min(actual_val,$scope.pck1yrlast/12);
				}
				if((parseInt(discount_val) < parseInt(actual_val)) && payment_type == "ecs") {
					$scope.pc_discount_ecs[key] = actual_val;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+actual_val;

					return false;
				}else if((parseInt(discount_val) < parseInt($scope.oneyrval)) && payment_type == "upfront") {
					$scope.pc_discount[key] = $scope.oneyrval;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+$scope.oneyrval;
					return false;
				}else if((parseInt(discount_val) < parseInt($scope.twoyrval)) && payment_type == "upfront_2yr") {
					$scope.pc_discount_2yr[key] = $scope.twoyrval;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+$scope.twoyrval;
					return false;
				}else if((parseInt(discount_val) < parseInt(actual_val)) && payment_type == "upfront_dis") {
					$scope.pc_pck_discount[key] = actual_val;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+actual_val;
					return false;
				}
				
			}else if((parseInt(discount_val) < parseInt(actual_val)) && key!=731 && key!="731_setup" && key!="735_setup" && key!=736 && key!=737 && key!=741 && key!="741_setup") {
				if(payment_type == "upfront") 
                   	 $scope.pc_discount[key] = actual_val;
				else if(payment_type == "ecs") 
				    $scope.pc_discount_ecs[key] = actual_val;
				else if(payment_type == "upfront_2yr")
				    $scope.pc_discount_2yr[key] = actual_val;
				else if(payment_type == "upfront_dis")
				    $scope.pc_pck_discount[key] = actual_val;
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Custom value must be more than "+actual_val;
				return false;
            }else if((parseInt(discount_val) < parseInt(actual_val)) && key==741 && payment_type == "ecs"){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Custom value must be more than "+actual_val;
				return false;
			}else if(key == 731 || key==736 || key==737) {
				if($scope.editcontract == 1) {
					$scope.oneyrval = Math.min($scope.pck1yrlast,actual_val);
					$scope.twoyrval = actual_val;
				}else {
					$scope.oneyrval = parseInt(actual_val);
					$scope.twoyrval = parseInt(actual_val);
				}
				
				if((parseInt(discount_val) < actual_val) && payment_type == "ecs"){
					$scope.pc_discount_ecs[key] = actual_val;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+parseInt(actual_val);
					return false;
				}else if((parseInt(discount_val) < $scope.oneyrval) && payment_type == "upfront"){
					$scope.pc_discount[key] = $scope.oneyrval;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+parseInt($scope.oneyrval);					
					return false;
				}else if((parseInt(discount_val) < actual_val) && payment_type == "upfront_dis"){
					$scope.pc_pck_discount[key] = actual_val;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+parseInt(actual_val);
					return false;
				}else if((parseInt(discount_val) < $scope.twoyrval) && payment_type == "upfront_2yr"){
					$scope.pc_discount_2yr[key] = $scope.twoyrval;
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = 'Alert!!';		
					$rootScope.commonShowContent = "Custom value must be more than "+parseInt($scope.twoyrval);
					return false;
				}
				
			}else if(parseInt(discount_val) < parseInt(actual_val) && $scope.reduce_price == 0 && (key =="731_setup" ||  key=="735_setup" || key=="73_setup")){
				if(payment_type == "upfront") 
					$scope.pc_discount[key] = $scope.pc_actual[key];
				else if(payment_type == "ecs") 
					$scope.pc_discount_ecs[key] = $scope.pc_actual_ecs[key];
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = 'Alert!!';		
				$rootScope.commonShowContent = "Custom value must be more than "+$scope.pc_actual[key];
				return false;
			}else if(parseInt(discount_val) == parseInt(actual_val)){
				return false;
			}
			
			if(key == "73_setup") {
				var custom_val = discount_val;
				key = 72;
			}else {
				var custom_val = discount_val;
			
				if(payment_type == "upfront" && key == 5)  {
					custom_val = Math.ceil(discount_val)/12;
				}else if(payment_type == "ecs" && key == 5) {
					custom_val = discount_val;
				}
			}
			
            if(index != 731 && index != 737 && index != 736 && index != 735 && index != 1 && index != 111 && index != 112 && index != 114 && index != 115 && index != 116 && index != 734 && index != 741) {
				APIServices.insert_discount($stateParams.parid,$stateParams.ver,key,custom_val).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
				});
            }else if((index == 731 || index == 737 || index == 736 || index == 735) && index != 1 && index != 111 && index != 112 && index != 114 && index != 115 && index != 116  && index != 734) {
                if(index == 731 || index == 737 || index == 736) {
					$scope.omni_type =4;
				}else if(index == 735) {
					$scope.omni_type =5;
				}
				
                if(payment_type == "upfront" || payment_type == "upfront_2yr" ||  payment_type == "upfront_dis")  {
					$scope.dis_price = discount_val/12;
				}else if(payment_type == "ecs") {
					$scope.dis_price = discount_val;
				}
				APIServices.combocustomprice($stateParams.parid,$stateParams.ver,$scope.dis_price,0,$scope.omni_type,0).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
					}
				});
			}else if((index == 740 && typeof $scope.pricechat['Omni']['740'] !== 'undefined') || (index == 741 && typeof $scope.pricechat['Omni']['741'] !== 'undefined') ){
                if(payment_type == "upfront") {
					if(key == index+"_setup"){
						$scope.omni1_setup = discount_val;
                        $scope.omni1_price = $scope.pc_discount[index];
					}else{
						$scope.omni1_setup = parseInt($scope.pc_discount_ecs[index+"_setup"]);
						$scope.omni1_price = Math.ceil(discount_val);
					}
                }else if(payment_type == "ecs"){
                    if(key == index+"_setup") {
                        $scope.omni1_setup = discount_val;
                        $scope.omni1_price = $scope.pc_discount_ecs[index];
                    }else {
                        //~ $scope.omni1_setup = parseInt($scope.pricechat['Omni']['734']['down_payment']);
                        $scope.omni1_setup = parseInt($scope.pricechat['Omni'][index]['down_payment']);
                        $scope.omni1_price = discount_val;
                    }
                }

                APIServices.insert_discount($stateParams.parid,$stateParams.ver,72,$scope.omni1_setup).success(function(response) {
                    if(response.error.code == 1) {
                        $rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
                        return false;
                    }
                });

                APIServices.insert_discount($stateParams.parid,$stateParams.ver,73,$scope.omni1_price).success(function(response) {
                    if(response.error.code == 1) {
                        $rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.error.msg;
                        return false;
                    }
                });


            }
			
		}
		
		$scope.apply_discount = function(){
			if($scope.discount_applied == 1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Discount is already applied";
				return false;
			}
			
			$scope.apply_dis = {};
			$scope.omni_sel = $scope.pck_sel = 0;
			$scope.omni_key = $scope.pck_key = '';
			$scope.sel_count =0;
			
			if($rootScope.pricechart_rb['payment_type'] == ''){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "please select payment type";
				return false;
			}
			
			
			if($scope.edit_price == true){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Custom is applied,discount can not be applied";
				return false;
			}
			
			angular.forEach($scope.pricechart_cb,function(value,key) {
				if(value){
					if((key.substr(0,2) == '74' || key.substr(0,2) == '73') && key !='742' && key !='743' && key !='745' ){
						$scope.omni_sel = 1;
						$scope.omni_key = key;
					}
					if(key.substr(0,2) == '11' || key == '1'){
						$scope.pck_sel = 1;
						$scope.pck_key = key;
					}
					$scope.sel_count++;
				}
			});
			
			$scope.pck_amount = $scope.pc_discount[$scope.pck_key];
			$scope.omni_amount = $scope.pc_discount[$scope.omni_key];
			$scope.pck_ecs_amount = $scope.pc_discount_ecs[$scope.pck_key]*12;
			$scope.omni_amount = $scope.pc_discount_ecs[$scope.omni_key]*12;
			if($scope.omni_sel == 1 && $scope.existing_contract == 1 && $scope.omni_key == 741 && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				if($rootScope.pricechart_rb['payment_type'] == 'upfront'){
					$scope.omni_actual_val  = $scope.pc_actual[$scope.omni_key];
				}else if($rootScope.pricechart_rb['payment_type'] == 'ecs') {
					if($scope.sel_count == 1){
						$scope.discount_applied =0;
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "In Ecs,To apply discount please select omni with other campaign";
						return false;
					}
					
					$scope.omni_actual_val  = $scope.pc_actual_ecs[$scope.omni_key];
				}
				$scope.apply_dis[$scope.omni_key] = true;
				$scope.pricechat.Omni[$scope.omni_key].omni_dis_ecs = 0;
				$scope.pricechat.Omni[$scope.omni_key].omni_dis_price = 0;
				$scope.pc_discount[$scope.omni_key] = 0;
				$scope.pc_discount_ecs[$scope.omni_key] = 0;
				$scope.call_discount_api('','741',$rootScope.pricechart_rb['payment_type'],$scope.omni_actual_val,'0');
				$scope.discount_applied = 1;
			}else if($scope.omni_sel == 1 && $scope.existing_contract == 1 && $scope.omni_key != 741){
				$scope.discount_applied =0;
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Discount is applicable only for Own Dynamic Website & Mobile Site With Transaction Capability";
				return false;
			}else if($scope.omni_sel == 1 && $scope.existing_contract == 1 && $scope.omni_key == 741 && $rootScope.pricechart_rb['payment_type']=='ecs'){
				$scope.discount_applied =0;
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Discount is applicable only for Own Dynamic Website & Mobile Site With Transaction Capability  in Upfront";
				return false;
			}else if($scope.existing_contract == 0){
				$scope.discount_applied =0;
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Discount is applicable only for existing contracts";
				return false;
			}
		}
		
		$scope.submit_campaigns = function(ev) {  
			$scope.selected_campaigns = '';
			$scope.selected_campaign_names = '';
			$scope.stop_allowing_ecs ='';
			$scope.stop_allowing_upfront ='';
			$scope.stop_proceed = 0; 
           		$rootScope.premium_2years = 0;
			
			if($scope.pc_actual_ecs['111'] < Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0) && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$scope.min_budget_mon = $scope.pc_actual_ecs['111'];
				$scope.min_budget = $scope.pc_actual_ecs['111']*12;
			}else{
				$scope.min_budget_mon = Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0);
				$scope.min_budget = $rootScope.bestBudget.result.city_bgt;
			}
					
					
			if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.edit_price == true){
				angular.forEach($scope.pc_discount,function(value,key) {
					if($scope.pricechart_cb[key] == true){
						
						if($scope.editcontract == 1) {
							if(key == 111){
								$scope.pckval1yr =  Math.min($scope.pck1yrlast,$rootScope.bestBudget.result.package_mini_minimum);
							}else{
								$scope.pckval1yr = Math.min($scope.pck1yrlast, $scope.pc_actual[key]);
							}
							$scope.pckval2yr = $scope.pc_actual_2yr[key];
						}else {
							$scope.pckval1yr = $scope.pc_actual[key];
							$scope.pckval2yr = $scope.pc_actual_2yr[key];
						}	
						
						if((parseInt($scope.pc_discount[key]) < parseInt($scope.pckval1yr)) && (key==114 || key==115 || key==116 || key == 731 || key == 736 || key == 737)) {
							$scope.stop_proceed = 1;
							return false;
						}else if((parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key])) && key!=731 && key!="731_setup" && key!="735_setup" && key!=1 && key!= 111 &&  key!= 114 &&  key!= 115 &&  key!= 116 &&  key!= 731 &&  key!= 736 &&  key!= 737  &&  key!= 741) { 
							$scope.stop_proceed = 1;
							return false;
						}else if($scope.pc_discount[key] == '' || $scope.pc_discount[key] == undefined){
							$scope.stop_proceed = 1;
							return false;
						}else if(parseInt($scope.pc_discount[key]) < parseInt($scope.pc_actual[key]) && $scope.reduce_price == 0 && (key =="731_setup" ||  key=="735_setup" || key=="73_setup")){
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 1 && $rootScope.expiredePackval == 0 &&(parseInt($scope.pc_discount[key]) < Math.min(parseInt($scope.cstm_minbudget_package),$rootScope.premium_bgt * 12))) {
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 1 && $rootScope.expiredePackval != 0 &&(parseInt($scope.pc_discount[key]) < parseInt($rootScope.expiredePackval)) && $rootScope.pricechart_rb['package_tenure'] == 12){
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Alert!!";
							$rootScope.commonShowContent = 'Package value must not be less than '+$rootScope.expiredePackval;
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 1 && $rootScope.expiredePackval != 0 &&(parseInt($scope.pc_discount_2yr[key]) < parseInt($rootScope.expiredePackval_48)) && $rootScope.pricechart_rb['package_tenure'] == 24){
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Alert!!";
							$rootScope.commonShowContent = 'Package value must not be less than '+$rootScope.expiredePackval_48;
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 111 && parseInt($scope.pc_discount[key]) < parseInt($scope.pckval1yr)){
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Alert!!";
							$rootScope.commonShowContent = 'Package value must not be less than '+$scope.pckval1yr;
							$scope.stop_proceed = 1;
							return false;
						}else if((key == 111 || key == 731 || key == 736 || key == 737 || key == 114 || key ==115 || key == 116) && $rootScope.pricechart_rb['discount_type'] == "two_year" && parseInt($scope.pc_discount_2yr[key]) < parseInt($scope.pckval2yr)){
							$scope.stop_proceed = 1;
							return false;
						}else if((key == 731 || key == 736 || key == 737 || key == 114 || key ==115 || key == 116) && $rootScope.pricechart_rb['discount_type'] == "discount" && parseInt($scope.pc_pck_discount[key]) < parseInt($scope.pc_pck_actual[key])){
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 111 && $rootScope.pricechart_rb['discount_type'] == "discount" && parseInt($scope.pc_pck_discount['111']) < parseInt($rootScope.bestBudget.result.price_mini_upfront_minimum_discount)){
							$scope.stop_proceed = 1;
							return false;
						}
					}
				});
			}else if($rootScope.pricechart_rb['payment_type'] == 'ecs' && $scope.edit_price == true) { 
				$scope.package_min =   Math.min(Math.round((parseInt($rootScope.bestBudget.result.city_bgt)/12),0),Math.round((parseInt($scope.cstm_minbudget_package)/12),0),$rootScope.premium_bgt,Math.round((parseInt($scope.pck1yrlast)/12),0));
				angular.forEach($scope.pc_discount_ecs,function(value,key) {
					if($scope.pricechart_cb[key] == true){
						if($scope.pck1yrlast != undefined && $scope.pck1yrlast!= ''  && DATACITY.toLowerCase()	!=	"mumbai"){
							$scope.pc_actual_ecs[key]		=	Math.min($scope.pc_actual_ecs[key],$scope.pck1yrlast/12);
						}
						if((parseInt($scope.pc_discount_ecs[key]) < parseInt($scope.pc_actual_ecs[key])) && key!=731 && key!="731_setup" && key!="735_setup"  && key!="734_setup" && key != 1) {
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 731 && (parseInt($scope.pc_discount_ecs[key]) <  parseInt($scope.pc_discount_ecs["737"]))) {
							$scope.stop_proceed = 1;
							return false;
						}else if($scope.pc_discount_ecs[key] == '' || $scope.pc_discount_ecs[key] == undefined){
							$scope.stop_proceed = 1;
							return false;
						}else if(parseInt($scope.pc_discount_ecs[key]) < parseInt($scope.pc_actual_ecs[key]) && $scope.reduce_price == 0 && (key =="731_setup" ||  key=="735_setup" || key=="73_setup" || key=="734_setup" )){
							$scope.stop_proceed = 1;
							return false;
						}else if(key == 1 &&(parseInt($scope.pc_discount_ecs[key]) <  $scope.package_min)) {
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Alert!!";
							$rootScope.commonShowContent = 'Package value must be greater than '+$scope.package_min;
							$scope.stop_proceed = 1;
							return false;
						}
					}
				});
			}
			
			if($scope.stop_proceed == 1) {
				return false;
			}
			
			
			if($rootScope.pricechart_rb['payment_type'] == '' ) {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Please select payment type';
				return false;
			}
			
			if(($rootScope.pricechart_rb['package_tenure'] == '' || $rootScope.pricechart_rb['package_tenure'] == undefined)){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Please select tenure';
				return false;
			}

			if($rootScope.pricechart_rb['package_tenure'] == '24' && $rootScope.pricechart_rb['discount_type'] == 'discount'){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'There is no discount for two years';
				return false;
			}
			
			
			if($rootScope.pricechart_rb['discount_type'] == '' && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Please select upfront payment type";
				return false;
			}
			
			if($scope.nxt_clicked == 0){
				$scope.nxt_clicked = 1;
			}else {
				return false;
			}

			angular.forEach($scope.campaign_order,function(value,key) {
				if($scope.pricechart_cb[value] == true) {
					if($scope.ecs_notallowed.indexOf(value) != -1 && $rootScope.pricechart_rb['payment_type'] == 'ecs'){
						$scope.stop_allowing_ecs += $scope.campaign_name[value]+',';
					}else if($scope.upfront_notallowed.indexOf(value) != -1 && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
						$scope.stop_allowing_upfront += $scope.campaign_name[value]+',';
					}
					
					$scope.selected_campaigns += value+',';
					$scope.selected_campaign_names += $scope.campaign_name[value]+',';
					if(value == '73') {
						$cookieStore.put('selected_option','omni1');
					}else if(value == '731') {
						$cookieStore.put('selected_option','omnisupreme');
                        if($scope.pricechart_rb['package_tenure'] == '24'  && typeof $scope.pricechat.Package['731'] != undefined  && typeof $scope.pc_discount_2yr["731"] != undefined) {
							APIServices.combocustomprice($stateParams.parid,$stateParams.ver,$scope.pc_discount_2yr["731"]/12,0,4,0).success(function(response) {
						  
							});
						}
						if($rootScope.pricechart_rb['payment_type'] == "upfront")  {
                            				$scope.dis_price = $scope.pc_pck_discount[value]/12;
			                        }else if($rootScope.pricechart_rb['payment_type'] == "ecs") {
			                            $scope.dis_price = $scope.pricechat.Package[value].price_ecs;
			                        }
			                        
			                        if($scope.pricechart_rb['package_tenure'] != '24'  && typeof $scope.pricechat.Package['731'] != undefined   && $rootScope.pricechart_rb['discount_type'] == "discount") {
			                            APIServices.combocustomprice($stateParams.parid,$stateParams.ver,$scope.dis_price,0,4,0).success(function(response) {
			                          
			                            });
			                        }
					}else if(value == '732') {
						$cookieStore.put('selected_option','omniultima');
					}else if(value == '735') {
						$cookieStore.put('selected_option','combo2');
					}else if(value == '734') {
						$cookieStore.put('selected_option','omni2');
                    }else if(value == '741') {
                        $cookieStore.put('selected_option','omni7');
                        $scope.omni_type = 7;
                    }else if(value == '748') {
                        $cookieStore.put('selected_option','omni14');
                    }else if(value == '735') {
                        $cookieStore.put('selected_option','combo2');
                    }else if (value == '736' || value == '737') {
						if($rootScope.pricechart_rb['payment_type'] == "ecs"){
							$scope.actual_combo_val =$scope.pc_discount_ecs[value];
						}else if($rootScope.pricechart_rb['discount_type'] == "discount" && typeof $scope.pricechat.Package[value] != undefined){
							$scope.actual_combo_val = $scope.pc_pck_discount[value];
						}else if($scope.pricechart_rb['package_tenure'] == '24' && typeof $scope.pricechat.Package[value] != undefined  && typeof $scope.pc_discount_2yr[value] != undefined ){
							$scope.actual_combo_val = $scope.pc_discount_2yr[value];
						}else{
							$scope.actual_combo_val = $scope.pc_discount[value];
						}
						if($rootScope.pricechart_rb['payment_type'] == "upfront")  {
							$scope.dis_price = $scope.actual_combo_val/12;
						}else if($rootScope.pricechart_rb['payment_type'] == "ecs") {
							$scope.dis_price = $scope.actual_combo_val;
						}
					
						APIServices.combocustomprice($stateParams.parid,$stateParams.ver,$scope.dis_price,0,4,0).success(function(response) {
						  
						});
						$cookieStore.put('selected_option','omnisupreme');
						$scope.combo_cus = value;
						//~ $scope.selected_campaigns =$scope.selected_campaigns.replace(value,"731");
					} 
				}
			});
			
			$scope.selected_campaigns = $scope.selected_campaigns.slice(0, -1);
			$scope.selected_campaign_names = $scope.selected_campaign_names.slice(0, -1).toLowerCase();
			$scope.selected_arr = $scope.selected_campaigns.split(',');
			
			$scope.stop_allowing_ecs = $scope.stop_allowing_ecs.slice(0, -1).toUpperCase(); 
			$scope.stop_allowing_upfront = $scope.stop_allowing_upfront.slice(0, -1).toUpperCase(); 
			
			if($scope.stop_allowing_ecs != ''){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Cant Proceed as '+$scope.stop_allowing_ecs+' campiagn is not allowed in ecs ';
				return false;
			}else if($scope.stop_allowing_upfront != ''){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Cant Proceed as '+$scope.stop_allowing_upfront+' campaign is not allowed in upfront ';
				return false;
			}
			
			if($scope.selected_campaigns == '') {
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Please select a campaign';
				return false;
			}else if($scope.ecs_flg == 1 && $rootScope.pricechart_rb['payment_type'] == 'upfront' && ($scope.selected_arr.length != 1 || $scope.selected_arr.length == 1  && ($scope.selected_arr[0] != 22 && $scope.selected_arr[0] != 225))) {
				/*$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'For ongoing Ecs contract you can select only JDRR/JDRR Plus in upfront';
				return false;*/
			}else if($scope.ecs_flg == 1 && $rootScope.pricechart_rb['payment_type'] == 'ecs' && $scope.selected_arr.length == 1 && ($scope.selected_arr[0] == 22 ||$scope.selected_arr[0] == 225) ){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'ECS is not allowed for pure JDRR and JDRR Plus';
				return false;
			}
			
			if($scope.block_ecs == 1 && $rootScope.pricechart_rb['payment_type'] == 'ecs'){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'ECS is not allowed for GST categories';
				return false;
			}	
			
			
			 if(($rootScope.pricechart_rb['premium_tenure'] == '' || $rootScope.pricechart_rb['premium_tenure'] == undefined)  && $scope.selected_arr.indexOf('112') != -1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Please select tenure';
				return false;
            }
			
			 if(($rootScope.pricechart_rb['national_tenure'] == '' || $rootScope.pricechart_rb['national_tenure'] == undefined)  && $scope.selected_arr.indexOf('10') != -1){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Please select national tenure';
				return false;
            }
			
			
			if($rootScope.pricechart_rb['payment_type'] == 'upfront') {
				if($scope.selected_arr['0'] == 1){
				var check_val = $scope.pc_discount['1']/12;
				}else if($scope.selected_arr['0'] == 112){
					var check_val = $scope.pc_discount['112']/12;
				}
			}else {
				check_val = $scope.pc_discount_ecs['1'];
			}
           if(($scope.selected_arr['0'] == '1' || $scope.selected_arr['0'] == '112')  && $rootScope.expiredePackval != 0 && $rootScope.pricechart_rb['payment_type'] == 'upfront' && ((parseInt(check_val) < parseInt($rootScope.premium_bgt) && $rootScope.pricechart_rb['package_tenure'] == '12') || ($rootScope.pricechart_rb['package_tenure'] == '24' && parseInt($scope.pc_discount_2yr['1']/24) < parseInt($rootScope.premium_bgt)))){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("package expired","package_expired");
			}
            if($scope.selected_arr['0'] == '112' && $rootScope.pricechart_rb['package_tenure'] == 24 && $rootScope.pricechart_rb['payment_type'] == 'upfront' ){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("ultra premium ad package","package_10dp_2yr");
				$rootScope.premium_2years = 1;
			}
            if($scope.selected_arr['0'] == '117'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("flexi 6k","flexilowval12dp");
			}
			
			if($scope.selected_arr['0'] == '111' && $rootScope.pricechart_rb['payment_type'] == 'ecs'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("flexi premium ad package","mini_ecs");
			}
			
			if(($scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '736' || $scope.selected_arr['0'] == '737') && $rootScope.pricechart_rb['discount_type'] == "discount" && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("super premium ad package","combo1_1yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("ultra premium ad package","combo1_1yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("premium ad package","combo1_1yr_dis");
			}
			if(($scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '736' || $scope.selected_arr['0'] == '737') && $scope.pricechart_rb['package_tenure'] == '24' && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("super premium ad package","combo1_2yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("ultra premium ad package","combo1_2yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("premium ad package","combo1_2yr_dis");
			}
			if(($scope.selected_arr['0'] == '114' || $scope.selected_arr['0'] == '115' || $scope.selected_arr['0'] == '116' || $scope.selected_arr['0'] == '111') && $rootScope.pricechart_rb['discount_type'] == "discount" && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("flexi premium ad package","pck_1yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("super premium ad package","pck_1yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("ultra premium ad package","pck_1yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("premium ad package","pck_1yr_dis");
			}
			if(($scope.selected_arr['0'] == '114' || $scope.selected_arr['0'] == '115' || $scope.selected_arr['0'] == '116' || $scope.selected_arr['0'] == '111') && $scope.pricechart_rb['package_tenure'] == '24' && $rootScope.pricechart_rb['payment_type'] == 'upfront'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("flexi premium ad package","pck_2yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("super premium ad package","pck_2yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("ultra premium ad package","pck_2yr_dis");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("premium ad package","pck_2yr_dis");
				$rootScope.premium_2years = 1;
			}
			
			if($scope.selected_arr['0'] == '118'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("highest to lowest","flexi_selected_user");
			}
			
			if($scope.selected_arr['0'] == '119'){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("lfl package","flexi_pincode_budget");
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("vfl package","flexi_pincode_budget");
			}
			
			
			if($scope.selected_arr.indexOf("748") != -1){
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("own dynamic website & mobile site with transaction capability for 10 years","website_10years");
			}
			
			if($scope.pricechat_model.banner_rotation == '' || $scope.pricechat_model.banner_rotation <= 0){
				$scope.pricechat_model.banner_rotation =1;
			}

			$cookieStore.put('campaign_str', $scope.selected_campaigns);
			$cookieStore.put('campaign_names',$scope.selected_campaign_names);
			$cookieStore.put('banner_rotation',$scope.pricechat_model.banner_rotation);
			
			
			if($scope.pricechat_model.banner_rotation > 1 &&  $scope.selected_arr.indexOf('5') != -1)  {
				if($rootScope.pricechart_rb['payment_type'] == 'upfront'){
					$scope.banner_cus = Math.ceil($scope.pc_discount['5'])/12;
				}else {
					$scope.banner_cus = $scope.pc_discount_ecs['5'];
				}
				
				APIServices.insert_discount($stateParams.parid,$stateParams.ver,5,$scope.banner_cus).success(function(response) {
					if(response.error.code == 1) {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Alert!!";
						$rootScope.commonShowContent = response.error.msg;
						return false;
					}
				});
			}
			
			if($scope.selected_arr['0'] == '1' && $rootScope.pricechart_rb['discount_type'] == 'discount'){
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = "Discount can't be applied";
				return false;
			}
			
			if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'original')
            {

				$scope.nationallistingfinal_upfront_pay = $rootScope.bestBudget.result.upfront_national_budget;
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("national listing","nl_original");
			}
             if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'discount')
            {
				$scope.nationallistingfinal_upfront_pay = $rootScope.national_discount;
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("national listing","nl_1yr_discount");
			}
			 if($scope.selected_campaigns.indexOf("10") != -1 && $rootScope.pricechart_rb['discount_type'] == 'two_year')
            {
				$scope.nationallistingfinal_upfront_pay = $rootScope.national_2yr_add_discount;
				$scope.selected_campaign_names = $scope.selected_campaign_names.replace("national listing","nl_2_yrs");
				$rootScope.pricechart_rb['national_tenure'] = 730;
			}
			
			
			$scope.original = 0;
			$scope.discount = 0;
			$scope.two_year = 0;
            if($rootScope.pricechart_rb['discount_type'] == 'original' || $rootScope.pricechart_rb['payment_type'] == 'ecs'){
				$scope.original = 1;
				$scope.discount = 0;
				$scope.two_year = 0;
			}else if($rootScope.pricechart_rb['discount_type'] == 'discount' && $rootScope.pricechart_rb['payment_type'] != 'ecs'){
				$scope.original = 0;
				$scope.discount = 1;
				$scope.two_year = 0;
			}else if($rootScope.pricechart_rb['discount_type'] == 'two_year' && $rootScope.pricechart_rb['payment_type'] != 'ecs'){
				$scope.original = 0;
				$scope.discount = 0;
				$scope.two_year = 1;
			}
			
			APIServices.payment_type($stateParams.parid,$scope.selected_campaign_names,$stateParams.ver,$rootScope.pricechart_rb['payment_type'],$scope.selected_campaigns,$scope.original,$scope.discount,$scope.two_year).success(function(response) {  
				if(response.error_code == 0) {
					if($scope.selected_arr.indexOf("10") != -1)
					{  
						$scope.response_data = null;
						$scope.yearlyNationalBudget = ($rootScope.pricechart_rb['payment_type'] == 'upfront') ? $scope.nationallistingfinal_upfront_pay : ($scope.nationallistingfinal_ecs*12);

						
							
							APIServices.calcupdatedatanational($rootScope.parentid,DATACITY,$scope.yearlyNationalBudget,$rootScope.pricechart_rb['national_tenure'],'1').success(function(response) {
							//  console.log(response);
							$scope.response_data = response;
							});	
						
						//console.log($scope.response_data);	
						$scope.$watch(function(scope) { return $scope.response_data },
						function(newValue, oldValue)
						{
							
						if(response != null)
						{
							if($scope.selected_arr['0'] == '1' || $scope.selected_arr['0'] == '111' || $scope.selected_arr['0'] == '112' || $scope.selected_arr['0'] == '113' || $scope.selected_arr['0'] == '114' || $scope.selected_arr['0'] == '115' || $scope.selected_arr['0'] == '116' || $scope.selected_arr['0'] == '117' )
							{
								if($rootScope.pricechart_rb['payment_type'] == 'ecs') {
									var package_amt = $scope.pc_discount_ecs[$scope.selected_arr['0']] * 12;
									$cookieStore.put('flexi_tenure','12');
								}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['package_tenure'] == 24 && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pc_discount_2yr[$scope.selected_arr['0']] != undefined){
									var package_amt = $scope.pc_discount_2yr[$scope.selected_arr['0']];
									$cookieStore.put('flexi_tenure','24');
								}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['discount_type'] == "original" && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pricechat.Package[$scope.selected_arr['0']]['price_upfront'] != undefined){
									var package_amt = $scope.pc_discount[$scope.selected_arr['0']];
									$cookieStore.put('flexi_tenure','12');
								}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['discount_type'] == "discount" && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pc_pck_discount[$scope.selected_arr['0']] != undefined){
									var package_amt = $scope.pc_pck_discount[$scope.selected_arr['0']];
									$cookieStore.put('flexi_tenure','12');
								}else {
									var package_amt = $scope.pc_discount[$scope.selected_arr['0']];
									$cookieStore.put('flexi_tenure','12');
								}
						
								$cookieStore.put('package_amt',package_amt);
								$cookieStore.put('flexi_bud', package_amt);
								//~ $scope.submitBudget(package_amt,"appHome.nationallisting");
								$state.go('appHome.nationallisting',{parid:$stateParams.parid,type:'nationallisting',ver:$stateParams.ver,page:$rootScope.extraHandler});	
							}
							else
							{
								if($scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '736' || $scope.selected_arr['0'] == '737') {
									$rootScope.showCommonPop = 'combo_confirm';
								}else{
									$state.go('appHome.nationallisting',{parid:$stateParams.parid,type:'nationallisting',ver:$stateParams.ver,page:$rootScope.extraHandler});	
								}
							
							}
						}
					});	
						
						
				}
					else if($scope.selected_arr['0'] == '1' || $scope.selected_arr['0'] == '111' || $scope.selected_arr['0'] == '112' || $scope.selected_arr['0'] == '113' || $scope.selected_arr['0'] == '114' || $scope.selected_arr['0'] == '115' || $scope.selected_arr['0'] == '116' || $scope.selected_arr['0'] == '117') {
						
						if($rootScope.pricechart_rb['payment_type'] == 'ecs') {
                            var package_amt = $scope.pc_discount_ecs[$scope.selected_arr['0']] * 12;
                            $cookieStore.put('flexi_tenure','12');
						}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['package_tenure'] == 24 && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined && typeof $scope.pc_discount_2yr[$scope.selected_arr['0']] != undefined){
							var package_amt = $scope.pc_discount_2yr[$scope.selected_arr['0']];
							 $cookieStore.put('flexi_tenure','24');
						}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['discount_type'] == "original" && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pricechat.Package[$scope.selected_arr['0']]['price_upfront'] != undefined){
							var package_amt = $scope.pc_discount[$scope.selected_arr['0']];
							$cookieStore.put('flexi_tenure','12');
						}else if($rootScope.pricechart_rb['payment_type'] == 'upfront' && $scope.pricechart_rb['discount_type'] == "discount" && typeof $scope.pricechat.Package[$scope.selected_arr['0']] != undefined  && typeof $scope.pc_pck_discount[$scope.selected_arr['0']] != undefined){
							var package_amt = $scope.pc_pck_discount[$scope.selected_arr['0']];
							 $cookieStore.put('flexi_tenure','12');
						}else {
							var package_amt = $scope.pc_discount[$scope.selected_arr['0']];
							 $cookieStore.put('flexi_tenure','12');
						}
						$cookieStore.put('flexi_bud', package_amt);
						$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler,ver:$stateParams.ver});
					}else if($scope.selected_arr['0'] == '2' || $scope.selected_arr['0'] == '118' || $scope.selected_arr['0'] == '119') {
						$rootScope.targetUrl	=	"appHome.areaSel";
						$rootScope.submitFinalCats();
                    }else if($scope.selected_arr['0'] == '73' || $scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '732' || $scope.selected_arr['0'] == '735' || $scope.selected_arr['0'] == '734' || $scope.selected_arr['0'] == '736' || $scope.selected_arr['0'] == '737' || $scope.selected_arr['0'] == '741' || $scope.selected_arr['0'] == '748' ) {
						if($scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '736' || $scope.selected_arr['0'] == '737') {
							$rootScope.showCommonPop = 'combo_confirm';
						}else {
						$rootScope.targetUrl	=	"appHome.omnidomainreg";
						$rootScope.submitFinalCats();
						}
					}else if($scope.selected_arr['0'] == '5' ){
						$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else if($scope.selected_arr['0'] == '225'){
						$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$stateParams.ver,page:$rootScope.extraHandler});
					}else if($scope.selected_arr['0'] == '22') {
						APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) {
							if(response.error.code == 0) {
								$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
							}else {
								$rootScope.showCommonPop = 1;
								$rootScope.commonTitle = "Alert!!";
								$rootScope.commonShowContent = response.error.msg;
							}
						});
					}
				}
			});	
		}
		
		$scope.reset_campaign = function(ev) {
			APIServices.deleteallcampaigns($stateParams.parid,$stateParams.ver).success(function(response) {
				if(response.error.code == 0) {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Success!!";
					$rootScope.commonShowContent = 'Success';
					return false;
				}else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = response.error.code;
					return false;
				}
			}); 
		}
			
		$scope.show_package = function(type) {
			if(type == 'package'){
				$scope.show_package_option = !$scope.show_package_option;
			}else if(type == 'omni') {
				$scope.show_omni_option = !$scope.show_omni_option;
            }else if(type == 'banner') {
                $scope.show_banner_option = !$scope.show_banner_option;
			}
		}						

	});
	
	
	tmeModuleApp.controller('nationallistingcontroller', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdBottomSheet,$mdSidenav,$stateParams,CONSTANTS,$window,$mdToast) {
		
		$rootScope.setNoMenu	=	1;
		var self = this;
		$rootScope.parentid	=	$stateParams.parid;
		$rootScope.extraHandler	=	$stateParams.page;
		$scope.showCity = 0;
		$scope.limitVals	=	[0];
		$scope.nationallistingcity = [];
		$scope.edit_price = false;
		
		APIServices.fetchtempdatanational($rootScope.parentid,DATACITY,$stateParams.ver).success(function(response) {
					$scope.stop_national = response.error;
					$scope.stop_national_msg = response.error_message;
					
					if($scope.stop_national == -2)
					{	
						$scope.stop_national_func($scope.stop_national_msg);		
					}
			
				$scope.nationallistingbudget = response.budget;
				
				
				$scope.payment_type_national = response.payment_type;
				$scope.idx = $scope.payment_type_national.indexOf('nl_2_yrs');
				//alert($scope.idx);
				$scope.nationallistingOgbudget = (response.existing && response.last_budget>0 && $scope.idx == -1) ? Math.min(Math.round((parseInt(response.last_budget)*1.5),0),response.budget) : response.budget;
				//console.log('524');
				//console.log(Math.min(Math.round((parseInt(response.last_budget)*1.5),0),response.budget));
				$scope.tenure				 = response.tenure;
				$scope.tenure_check          = response.tenure;
				$scope.nationallistingcity	 = response.city;
				$scope.city_function();
				$scope.nationallistingType	 = response.type;
		});
		
		$scope.stop_national_func = function(data)
		{
					
					$mdToast.show(
								$mdToast.simple()
								.content(data)
								.position('top right')
								.hideDelay(3000)
							);
			return false;
		}
			$scope.city_function = function()
			{
				
				angular.forEach($scope.nationallistingcity,function(value,key) {
					if(value.toLowerCase() == DATACITY.toLowerCase())
					{
						$scope.showCity = 1;
					}
					
				});
			
			
			}
			
			
		
		
		
		
		APIServices.getContractData($rootScope.parentid).success(function(response) {
			$rootScope.companyTempInfo	=	response;
			if($scope.moduleType	==	'TME' || $scope.moduleType	==	'ME') {
				APIServices.findGetContractData($rootScope.parentid).success(function(response2) {
					if(response2.error.code == 0) {
						$rootScope.getcon_loader = 1;
						APIServices.fetchLiveData($rootScope.parentid).success(function(respmsg) { 
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
		
		$scope.gotopricechart = function() {
			$state.go('appHome.pricechartnew',{parid:$stateParams.parid,ver:$stateParams.ver,page:$stateParams.page});
		}
	
		$scope.imgButAcc	=	[];
		$scope.imgButA =	"img/ic_highlight_off_24px.svg";
		
		
		$scope.showImg	=	function(index) {
			if(index=='0') {
				$scope.imgButAcc[index]	=	"img/ic_remove_circle_24px.svg";
			} else {
				$scope.imgButAcc[index]	=	"img/ic_add_circle_24px.svg";
			}
		};
	
		$scope.clickshrink	=	function(event,index) {
			$scope.limitVals.push(index);
			if (false === $(event.target).closest('.budgetPinDiv').find('.dataPinsInt').is(':visible')) {
				//$('.dataPinsInt').hide();
			}
			$(event.target).closest('.budgetPinDiv').find('.dataPinsInt').toggle();
			//~ for(var i=0;i<=$('.budgetPinDiv').length;i++) {
				//~ $scope.imgButAcc[i]	=	"img/ic_add_circle_24px.svg";
			//~ }
			if($(event.target).closest('.budgetPinDiv').find('.dataPinsInt').css('display') == 'block') {
				$scope.imgButAcc[index]	=	"img/ic_remove_circle_24px.svg";
			} else if($(event.target).closest('.budgetPinDiv').find('.dataPinsInt').css('display') == undefined){
				$scope.imgButAcc[index]	=	"img/ic_remove_circle_24px.svg";
			} else {
				$scope.imgButAcc[index]	=	"img/ic_add_circle_24px.svg";
			}
		};
	
		
		var selected_opt=$cookieStore.get('campaign_str');
		var payment_type=$cookieStore.get('payment_type');
		var package_amt=$cookieStore.get('package_amt');
		
		//console.log('cddgvg');
		//console.log(package_amt);
		
		
		$rootScope.selected_arr = selected_opt.split(',');
		//console.log(selected_opt);
		//console.log(payment_type);
		//console.log(package_amt);
		//console.log(submitArr_package);
		
		$scope.callpopup = function(ev){
		
		if($scope.stop_national == -2)
		{	
			$scope.stop_national_func($scope.stop_national_msg);
			return false;		
		}
		
		if(parseInt($scope.tenure,0) < 180)
			{
		//	console.log(typeof parseInt($scope.tenure,0));
		//	console.log(parseInt($scope.tenure,0));
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Tenure cannot be lesser than 180';
					
					return false;	
			}
			
			if(parseInt($scope.tenure,0) > parseInt($scope.tenure_check,0))
			{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Tenure cannot be greater than '+$scope.tenure_check;
					
					//var edValue = '365';
					//$scope.tenure = edValue;
					return false;	
			}
			
			
			if(parseInt($scope.nationallistingbudget,0) < parseInt($scope.nationallistingOgbudget,0))
			{
				//	console.log(typeof $scope.nationallistingbudget);
				//	console.log(typeof $scope.nationallistingOgbudget);
				//	console.log($scope.nationallistingbudget);
				//	console.log($scope.nationallistingOgbudget);
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Budget cannot be less than '+ $scope.nationallistingOgbudget;
					 
					return false;	
			}
		
			if($scope.nationallistingbudget == "")
            {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Budget cannot be blank';
					return false;
			}
			
			if($scope.tenure == "")
			{
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert!!";
				$rootScope.commonShowContent = 'Tenure cannot be blank';					 
				return false;
				
			}
		APIServices.calcupdatedatanational($rootScope.parentid,DATACITY,$scope.nationallistingbudget,$scope.tenure,'1').success(function(response) {
		//	console.log(response);
		});
		
		var submitArr_package=$cookieStore.get('submitArr_package');
		
		
		
		if($scope.selected_arr['0'] == '1' || $scope.selected_arr['0'] == '111' || $scope.selected_arr['0'] == '112' || $scope.selected_arr['0'] == '113' || $scope.selected_arr['0'] == '114' || $scope.selected_arr['0'] == '115' || $scope.selected_arr['0'] == '116' || $scope.selected_arr['0'] == '117') 
		{	
			$state.go('appHome.areaSel',{parid:$stateParams.parid,page:$rootScope.extraHandler,ver:$stateParams.ver});
		/*	APIServices.submitBudgetData($rootScope.parentid,DATACITY,'TME',$rootScope.employees.results.mktEmpCode,submitArr_package).success(function(response) {
				$scope.showOptionLoader	=	0;
				if(response.error_code == 0) 
				{
					if(redirectPage == 'appHome.showExistInventory') {*/
					$state.go(redirectPage,{parid:$rootScope.parentid,flow:'package',page:$rootScope.extraHandler});
					/*}
				}
			});*/
		}else if($scope.selected_arr['0'] == '2' || $scope.selected_arr['0'] == '118' || $scope.selected_arr['0'] == '119' ) 
		{
				$rootScope.targetUrl	=	"appHome.areaSel";
				$rootScope.submitFinalCats();
		}else if($scope.selected_arr['0'] == '73' || $scope.selected_arr['0'] == '731' || $scope.selected_arr['0'] == '732' || $scope.selected_arr['0'] == '735' || $scope.selected_arr['0'] == '734' || $scope.selected_arr['0'] == '741' ) 
		{
			$rootScope.targetUrl	=	"appHome.omnidomainreg";
			$rootScope.submitFinalCats();
		}else if($scope.selected_arr['0'] == '5' )
		{
			$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'banner',ver:$stateParams.ver,page:$rootScope.extraHandler});
		}else if($scope.selected_arr['0'] == '225')
		{
			$state.go('appHome.bannerspecification',{parid:$stateParams.parid,type:'jdrrplus',ver:$stateParams.ver,page:$rootScope.extraHandler});
		}else if($scope.selected_arr['0'] == '22') 
		{
			APIServices.addjdrr($stateParams.parid,$stateParams.ver,0).success(function(response) 
			{
				if(response.error.code == 0) 
				{
						$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
				}else 
				{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = response.error.msg;					 
				}
			});
		}
			else if($scope.selected_arr['0'] == '10')
			{
				$state.go('appHome.budgetsummary',{parid:$stateParams.parid,ver:$stateParams.ver,page:$rootScope.extraHandler});
			}
		}
	
		$scope.changeTenure = function(evt){
		//	console.log($scope.tenure);
			evt = (evt) ? evt : window.event;
			
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode < 96 || charCode > 105)) {
				
				$scope.tenure = '';
				return false;	
			}
			else
			{
			var edValue = $scope.tenure;
			//var s = edValue.value;
			$scope.tenure = edValue;
			}
		}
	
	
	$scope.changeBudget = function(evt){
			
			
			/*evt = (evt) ? evt : window.event;
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			
			if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode < 96 || charCode > 105)) {
				
				//document.getElementById("NLBudget").value = $scope.nationallistingOgbudget;
				$scope.nationallistingbudget = $scope.nationallistingOgbudget;
				return false;	
			}
			else
			{
				var edValued = $scope.nationallistingbudget;
				
				$scope.nationallistingbudget = edValued;
			}
		*/}
	
	$scope.open_price = function(ev) {
			
			if(parseInt($scope.tenure,0) < 180)
			{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Tenure cannot be lesser than 180';			
					//var edValue = '365';
					//$scope.tenure = edValue;
					return false;	
			}
			
			if(parseInt($scope.tenure,0) > parseInt($scope.tenure_check,0))
			{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Tenure cannot be greater than '+$scope.tenure_check;	
					
					//var edValue = '365';
					//$scope.tenure = edValue;
					return false;	
			}
			
			
			if(parseInt($scope.nationallistingbudget,0) < parseInt($scope.nationallistingOgbudget,0))
			{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Budget cannot be less than '+ $scope.nationallistingOgbudget;	
					 
				//	var edValued = $scope.nationallistingOgbudget;
			//		$scope.nationallistingbudget = edValued;
					return false;	
			}
			
			if($scope.nationallistingbudget == "")
            {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Budget cannot be blank';	
					 
					return false;
			}
			
			if($scope.tenure == "")
			{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert!!";
					$rootScope.commonShowContent = 'Tenure cannot be blank';	
					 
					return false;
				
			}
			
			
			$scope.edit_price = !$scope.edit_price;
			
		}
		
		$scope.remove_local = function(){
			APIServices.removeLocalforNational($rootScope.parentid,DATACITY).success(function(response) {
				$state.reload();
				$scope.noskip = 1;
			});
		}

		$scope.capitalVal = function(input) {
			return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : '';
		}
		
	});
	
	
	
	
	
	
		
});
