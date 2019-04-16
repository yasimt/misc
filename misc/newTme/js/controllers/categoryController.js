define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('categoryController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		$rootScope.layout = ''; // new Code Added for handling new Design
		$rootScope.setNoMenu	=	1;
		var self = this;
		$scope.takeitup	=	function(event){
			if($scope.moduleType == 'CS') {
				$(event.target).closest(".upCatSearch").animate({top:"250px"},500);
			} else {
				$(event.target).closest(".upCatSearch").animate({top:"190px"},500);
			}
			//$(event.target).closest(".upCatSearch").find('.heading').fadeOut();
		};
		$scope.showLoader	=	0;
		$rootScope.parentid	=	returnState.paridInfo;
		$rootScope.extraHandler	=	$stateParams.page;

		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
        
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
		
		//Scope Variable for selected categories
		$scope.selected = [];
		$scope.selectedChecks = [];
		$scope.total_arr  = [];
		$scope.mrk_arr  = [];
		$scope.pop_arr  = [];
		$scope.child_arr  = [];
		$scope.sib_arr  = [];
		$scope.del_exit_arr  = [];
		$scope.autocatsearchBox  = {};
		$scope.autocatsearchBox.text  = '';
		$scope.quantity	=	10;
		$scope.show_cat ="mrk";
		$scope.skip_cat_step=0;
		
		
		
		//Function to stop auto sorting by ng-repeat in angular js
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		
		//APIService for calling existing categories
		APIServices.getExistingCats(returnState.paridInfo,DATACITY).success(function(response) {
			$rootScope.dataExistCats	=	response;
			if(response.error.code == 0) {
				if(response.data){
					if(response.data.TEMP){
						if(response.data.TEMP.PAID){
							angular.forEach(response.data.TEMP.PAID,function(value,key) {
								$scope.selectedChecks.push(key);
								$scope.skip_cat_step=1;
							});
						}
						if(response.data.TEMP.NONPAID){
							angular.forEach(response.data.TEMP.NONPAID,function(value,key) {
								$scope.selectedChecks.push(key);
								$scope.skip_cat_step=1;
							});
						}
					}
				}
			}
		});

		
		 APIServices.getnationalflag(returnState.paridInfo,DATACITY).success(function(response) {
			$scope.getnationallistingflag	=	response.nationallisting;
			$scope.getnationallistingType	=	response.nationallisting_type;
			$scope.nationallistingeligible	=	response.eligible_flag;
		});
		
		
		//Scope Var to show More contracts
		$scope.clickMore	=	0;
		$scope.showMoreCats	=	function(index,length) {
			$scope.clickMore	=	1;
			$scope.countVars[index]		=	$scope.countVars[index] + 12;
		};
		
		$scope.loadMoreCats	=	function() {
			$scope.clickMore	=	1;
			$scope.category_limit		=	$scope.category_limit + 12;
		};
		
		
		//Scope Var to skip step
		$scope.skipData	=	1;
		
		$scope.showExistCat	=	function(ev) {
			$rootScope.dataDialog	=	$rootScope.dataExistCats;
			$rootScope.companyInfoDialog	=	$rootScope.companyTempInfo;
			$rootScope.showCommonPop = 'showExistCat';
			
			
		};
		
		//alert(navigator.userAgent);
		$scope.exists = function (item, list) {
			return list.indexOf(item) > -1;
		};
	
		$scope.check_selected = function (item, list) {
			if(list.indexOf(item) > -1) {
				return true;
			}else {
				return false;	
			}
		};
		
		$scope.checkCats	=	[];
		$scope.toggle = function (item, list) {
			if($scope.checkCats[item]) {
				var idx = list.indexOf(item);
				list.splice(idx, 1);
			} else {
				list.push(item);
			}
		};
		
		$scope.delete_pre = function(id){
			if($scope.checkCats[id]) {
				var idx = $scope.selectedChecks.indexOf(id);
				$scope.selectedChecks.splice(idx, 1);
				$scope.del_exit_arr.push(id);
			}
		}
		
		
		$scope.addCcrCatMenu = function(param) {
			if(param == 1) {
				window.open("http://"+$scope.tmeURL+"/ccr/ccr_form.php?parentid="+returnState.paridInfo,"add_cat","");
			} else if(param == 2){
				window.open("http://"+$scope.tmeURL+"/ccr/ccr_history.php?parentid="+returnState.paridInfo+"&user_code="+USERID+"&dept=TME&showhistory=1","request_cat","");
			}else if(param == 3){
				window.open("http://"+$scope.tmeURL+"/ccr/multiparentage_history.php?parentid="+returnState.paridInfo+"&user_code="+USERID+"&dept=TME&showhistory=1","mutiparent_cat","");
			}
		};

		$scope.submitRelevantCats	=	function(event) {
			
			if(($scope.autocatsearchBox.text == '' || $scope.autocatsearchBox.text == undefined) && ($scope.selected.length == 0 && $scope.selectedChecks.length == 0)) {				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert";
				$rootScope.commonShowContent = "please select a category to move forward";
				return false;				
			}
			
			$scope.pre_cat = [];
			$scope.mrk_arr = $scope.selected;
			angular.forEach($scope.retDataCatStr.results['MRK'],function(val,key){
				if($scope.selectedChecks.indexOf(val.cid) != -1 && $scope.selected.indexOf(val.cid) == -1) {
					$scope.pre_cat.push(val.cid);
				}
			});
			
			if($scope.pre_cat.length == 0 && $scope.selected.length == 0) {
			$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert";
				$rootScope.commonShowContent = "please select a category to move forward";
				return false;
			}else {
				APIServices.submitRCatData(returnState.paridInfo,DATACITY,'ME',$scope.selected,$scope.pre_cat,$scope.show_cat).success(function(response) {
					if(response.error.code == 0) {
						$scope.category_limit = 12;
						$scope.getnextcat(event);
					} else {
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Alert";
						$rootScope.commonShowContent = "Some error found while adding categories. Please try again later";
					}
				});
			}
		};
		
		
		$scope.submitpopularCats	=	function(event) {
			
			$scope.pre_cat = [];
			if($scope.show_cat == "pop"){
				$scope.pop_arr = $scope.selected;
			}else if($scope.show_cat == "child"){
				$scope.child_arr = $scope.selected;
			}else if($scope.show_cat == "sib"){
				$scope.sib_arr = $scope.selected;
			}
			
			if($scope.populardata.data !=undefined) {
				angular.forEach($scope.populardata.data['cat_details'],function(val,key){
					if($scope.selectedChecks.indexOf(val.cid) != -1) {
						$scope.pre_cat.push(val.cid);
					}
				});
			}
			
			APIServices.submitRCatData(returnState.paridInfo,DATACITY,'ME',$scope.selected,$scope.pre_cat,$scope.show_cat).success(function(response) {
				if(response.error.code == 0) {
					$scope.category_limit = 12;
					if($scope.show_cat != 'child') {
						$scope.getnextcat(event);
					}else {
						$scope.submitCats(1);
					}
				} else {					
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert";
					$rootScope.commonShowContent = "Some error found while adding categories. Please try again later";
				}
			});
			
		};
		
		$scope.getmrkcat = function(){
			APIServices.catSearchDataMode($scope.selected_name,$scope.selected_key,DATACITY,$scope.getnationallistingflag,$scope.getnationallistingType).success(function (data) {
				$scope.retDataCatStr	=	data;
				$scope.show_cat = 'mrk';
				$scope.selected = $scope.mrk_arr; 
			});
		}
		
		$scope.getnextcat = function(event){
			if($scope.show_cat == "mrk"){
				$scope.category_type = 2; 
			}else if($scope.show_cat == "pop"){
				$scope.category_type = 3;
			}else if($scope.show_cat == "child"){
				$scope.category_type = 4;
			}
			APIServices.getPopularCat(returnState.paridInfo,$scope.category_type,$scope.getnationallistingflag,$scope.getnationallistingType).success(function(response){
				$scope.populardata	=	response;
                if(response.error.code == 1){
					 if($scope.show_cat == "mrk"){
						 $scope.show_cat = "pop";
						 $scope.getnextcat(event);
						 return false;
					 }else if($scope.show_cat == "pop"){
						  $scope.show_cat = "child";
						  $scope.getnextcat(event);
						  return false;
					 }else if($scope.show_cat == "child"){
						  $scope.show_cat = "sib";
						   $scope.getnextcat(event);
						   return false;
					 }else if($scope.show_cat == "sib"){
						 $scope.submitCats(1);
					 }
				}
				if($scope.show_cat == "mrk"){
					$scope.show_cat = "pop";
					if($scope.pop_arr.length !=0){
						$scope.selected = $scope.pop_arr;
					}else{
						$scope.selected = [];
					}
				}else if($scope.show_cat == "pop"){
					$scope.show_cat = "child";
					if($scope.child_arr.length !=0){
						$scope.selected = $scope.child_arr;
					}else{
						$scope.selected = [];
					}
				}else if($scope.show_cat == "child"){
					$scope.show_cat = "sib";
					if($scope.sib_arr.length !=0){
						$scope.selected = $scope.sib_arr;
					}else{
						$scope.selected = [];
					}
				}
			});
		}
		
		
		$scope.getprevcat = function(event){
			if($scope.show_cat == "child"){
				$scope.category_type = 2;
				$scope.show_cat = "pop";
				$scope.selected = $scope.pop_arr; 
			}else if($scope.show_cat == "sib"){
				$scope.category_type = 3;
				$scope.show_cat = "child";
				$scope.selected = $scope.child_arr;   
			}
			APIServices.getPopularCat(returnState.paridInfo,$scope.category_type,$scope.getnationallistingflag,$scope.getnationallistingType).success(function(response){
				$scope.populardata	=	response;
                if(response.error.code == 1){
					 if($scope.show_cat == "child"){
						 $scope.show_cat = "pop";
						 $scope.getprevcat(event);
						 return false;
					 }else if($scope.show_cat == "pop"){
						  $scope.show_cat = "mrk";
						  $scope.getmrkcat(event);
						  return false;
					 }
				}
			});
		}		
		
		$scope.submitCats	=	function(param) {
			$scope.finalArr	=	[];
			$scope.finalArr[0]	=	[];
			$scope.total_arr.push($scope.mrk_arr);
			$scope.total_arr.push($scope.pop_arr);
			$scope.total_arr.push($scope.child_arr);
			$scope.total_arr.push($scope.sib_arr);
	//		$scope.total_arr.push($scope.selectedChecks);
			angular.forEach($scope.total_arr,function(value,key){
				angular.forEach(value,function(val,key1){
					$scope.finalArr[0].push(val);
				});
			});
			APIServices.submitCatData(returnState.paridInfo,DATACITY,'TME',$scope.finalArr,$scope.del_exit_arr).success(function(response) {
				if(response.error.code == 0) {
					$state.go('appHome.catpreview',{parid:returnState.paridInfo,page:''});
				} else {
					$mdToast.show(
						$mdToast.simple()
						.content('Some error found while adding categories. Please try again later')
						.position('top')
						.hideDelay(3000)
					);
				}
			});
		};
		
		$scope.showFreeText	=	function() {
			if($scope.autocatsearchBox.text	!=	"") {
				if($scope.firstAutoResult.length == 0) {
					$scope.retDataCatStr	=	{};
					$scope.retDataCatStr['error']	=	{};
					$scope.retDataCatStr['error']['code']	=	1;
					$scope.retDataCatStr['error']['msg']	=	'No Results Found';
				} else {
					$scope.showLoader	=	1;
					APIServices.catSearchDataMode($scope.firstAutoResult[0]['mcn'],$scope.firstAutoResult[0]['value'],DATACITY,$scope.getnationallistingflag,$scope.getnationallistingType).success(function (data) {
						$scope.showLoader	=	0;
						$scope.retDataCatStr	=	data;
						$scope.category_limit = 12;
						$scope.show_cat = 'mrk';
                        $scope.selected = $scope.mrk_arr;
						$scope.skip_cat_step=0;
						
					});
				}
			}
		};
		
		/*function DialogController($scope, $mdDialog) {
			$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
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
			
			$scope.showLoader	=	0;
			$scope.dataDialog	=	$rootScope.dataExistCats;
		}*/
		
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
	});
	
	tmeModuleApp.controller('catPreviewController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		$rootScope.setNoMenu	=	1;
		var self = this;
		
		$scope.showFlag	=	0;
		$rootScope.parentid	=	$stateParams.parid;
		$rootScope.extraHandler	=	$stateParams.page;

		var PathSplice	=	$state.current.url.split('/');
		$rootScope.PathSet	=	PathSplice[1];
		
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
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
		
		//Calling Service for contractInformation
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
					}else if(response2.error.code == 2){ // we are doing extra check here.. to check if account details are not filled and its downsel request present then do not block them let them fil and proceed to dealclose
						 APIServices.getversion($rootScope.parentid,DATACITY).success(function(response1) { //console.log('----'+JSON.stringify(response1));
							  APIServices.get_accountdetials($rootScope.parentid,response1.version).success(function(response) { //console.log('---adas---->-'+JSON.stringify(response));
									 if(response.error.code == 0){ // present
										 if(response.error.result.account_number != ''){
											 $rootScope.downselVal		=	response2.error.msg;
											 $rootScope.showCommonPop	=	'downsel_check';
										 }
									 }else{// not present
										 if($state.current.name	!=	'appHome.ecsform'){
											 $rootScope.downselVal		=	response2.error.msg;
											 $rootScope.showCommonPop	=	'downsel_check';
										 }
									 }
							  });
                          });
					}
				});			
			}
			$rootScope.showCatPreviewData();
		});
		
		$scope.showFlagData	=	function() {
			if($scope.showFlag	==	0) {
				$scope.showFlag	=	1;
			} else if($scope.showFlag	==	1) {
				$scope.showFlag	=	0;
			}
		}
		
		$rootScope.showCatPreviewData	=	function() {
			$scope.imgSvgTog	=	{};
			$scope.showTogImg	=	{};
			$scope.showCatNarr	=	{};
			$scope.colorCheck	=	{};
			$scope.compname	=	"";
			$scope.ucode	=	USERID;
			$scope.uname	=	"";
			$scope.module	=	'TME';
			$scope.data_city	=	DATACITY;
			$rootScope.catStr	=	'';
			$rootScope.allpaidcat	=	'';
			$rootScope.allnonpaidcat	=	'';
			$rootScope.empname	=	"";
			$scope.movieTimings	=	{};
			$scope.checkPins	=	{};
			$scope.selected		=	[];
			$scope.authorisedPaid	=	[];
			$scope.nonAuthorisedPaid	=	[];
			$scope.authorisedNonPaid	=	[];
			$scope.nonAuthorisedNonPaid	=	[];
			$scope.pushDisabled	=	{};
			$scope.showNpSwitch	=	{};
			$scope.switchNpDisabled	=	{};
			//~ $scope.catData			=	{};
			$scope.catEditData			=	{};
			$scope.cur_date			=	"";
			$scope.rec_cat			=	[];
			$scope.old_cat			=	[];
			$scope.new_rec_cat		=	[];
			$scope.cat_data			=	[];

			APIServices.getCatPreviewData($rootScope.parentid,$scope.data_city,$scope.module,'').success(function(response) {
				$scope.uname	=	$rootScope.employees.hrInfo.data.empname;
				$scope.compname	=	$rootScope.companyTempInfo.data.companyname;
				$rootScope.empname	=	$scope.employees.hrInfo.data.empname;
				$rootScope.catData	=	response;
				$scope.sameCount	=	0;
				$scope.diffCount	=	0;
				$scope.showColourCode	=	0;
				if(response.error.code == 0) {
				
				APIServices.isPhoneSearchCampaign($rootScope.parentid).success(function(response){
					if(response == 0){
							APIServices.fetchEditListingEntry($rootScope.parentid).success(function(response){
								if(response.errorCode	==	1 || response.flag	==	1){//change to 1
									APIServices.fetchEditListingData($rootScope.parentid).success(function(response) {
										//~ alert(JSON.stringify(response));
										if(response.errorCode == 0){
											$scope.showColourCode	=	1;
											$scope.catEditData = response.data;
											
											if($scope.catEditData.recommend_category != '' && $scope.catEditData.recommend_category != null){
												$scope.rec_cat		=	$scope.catEditData.recommend_category.split(',');
											}
											
											if($scope.catEditData.old_category != '' && $scope.catEditData.old_category != null){
												$scope.old_cat		=	$scope.catEditData.old_category.split(',');
											}
											
											var i= 0;
											angular.forEach($scope.rec_cat,function(value1,key1) {
												$scope.new_rec_cat[i]	=	value1.replace(/\//g,'');
												i++;
											});
											var j	=	0;
											angular.forEach($scope.old_cat,function(value,key) {
												$scope.cat_data[j]	=	value.replace(/\//g,'');
												j++;
											});
											APIServices.getCatPreviewData($rootScope.parentid,$scope.data_city,$scope.module,$scope.cat_data).success(function(response) {
													$rootScope.catData	=	response;
											});
											
											var common = $.grep($scope.new_rec_cat, function(element) {
											    return $.inArray(element, $scope.cat_data ) !== -1;
											});
											
											var diffCatArrNew = $scope.new_rec_cat.filter(function(obj) { return $scope.cat_data.indexOf(obj) == -1; });
											
											var diffCatArrOld = $scope.cat_data.filter(function(obj) { return $scope.new_rec_cat.indexOf(obj) == -1; });
											
											
											setTimeout(function(){
												if(common != ''){
													angular.forEach(common,function(value,key) {
														$('.'+value).css('background','#87CEFA');
														$('.'+key).css('background','#87CEFA');
													});
												}
												
												if(diffCatArrNew != ''){
													angular.forEach(diffCatArrNew,function(value,key) {
														$('.'+value).css('background','#3CB371');
														$('.'+key).css('background','#3CB371');
													});
												}
												
												
												if(diffCatArrOld != ''){
													angular.forEach(diffCatArrOld,function(value,key) {
														$('.'+value).css('background','#ff6666');
														$('.'+key).css('background','#ff6666');
													});
												}
											},1000);
																				
										}
									});
								}
							});
						}
					});
					angular.forEach(response.data,function(value,key) {
						$scope.showCatNarr[key]	=	'';
						if(value.narr	!=	""){
							$scope.showCatNarr[key] = value.narr;
						}
						$scope.showTogImg[key]	=	false;
						if(value.paid	==	"Y") {
							$scope.showNpSwitch[key]	=	false;
							$scope.switchNpDisabled[key]		=	false;
							$rootScope.allpaidcat += key+',';
						} else {
							$scope.showNpSwitch[key]	=	true;
							$scope.switchNpDisabled[key]		=	true;
							$rootScope.allnonpaidcat += key+',';
						}
						if(value.cmnt == 'Authorised' && value.show == 1) {
							if(value.athchk == 1) {
								$scope.checkPins[key] = true;
								if(value.paid	==	'Y') {
									$scope.authorisedPaid.push(value.athcnm+'|~~|'+value.athcid);
								} else {
									$scope.authorisedNonPaid.push(value.athcnm+'|~~|'+value.athcid);
								}
							} else {
								$scope.checkPins[key] = false;
							}
						}
						$rootScope.catStr	+=	key+',';
						$scope.imgSvgTog[key] 	= 	'img/ic_check_box_24px.svg';
						$scope.colorCheck[key] 	= 	'#1278b7';
						if(value.cmnt	==	"Show Timings") {
							$scope.movieTimings[key]	=	value.slgn;
						}
					});
					$rootScope.catStr	=	$rootScope.catStr.slice(0,-1);
				}
			});
		};
			
			$scope.removedCat	=	[];
			$scope.catIdNpList	=	[];
			$scope.toggle	=	function(catKey) {
				if($scope.showTogImg[catKey]) {
					$scope.showTogImg[catKey]	=	false;
					$scope.imgSvgTog[catKey]	=	'img/ic_check_box_24px.svg';
					$scope.colorCheck[catKey]	=	'#1278b7';
					var idx = $scope.removedCat.indexOf(catKey);
					$scope.removedCat.splice(idx, 1);
					if(typeof $rootScope.catData.data[catKey].athcid !== 'undefined') {
						var idx2	=	$scope.removedCat.indexOf($rootScope.catData.data[catKey].athcid);
						$scope.removedCat.splice(idx2, 1);
					}
					
					if($rootScope.catData.data[catKey].athchk == 1) {
						$scope.checkPins[catKey]	=	true;
					}
					$scope.pushDisabled[catKey]	=	false;
					if($rootScope.catData.data[catKey].paid == "Y") {
						$scope.switchNpDisabled[catKey]		=	false;
					}
					var catInfo	=	$rootScope.catData.data[catKey];
					if(catInfo.cmnt == 'Authorised' && catInfo.show == 1) {
						if(catInfo.athchk == 1) {
							if(catInfo.paid	==	'Y') {
								$scope.authorisedPaid.push(catInfo.athcnm+'|~~|'+catInfo.athcid);
							} else {
								$scope.authorisedNonPaid.push(catInfo.athcnm+'|~~|'+catInfo.athcid);
							}
						} else {
							$scope.checkPins[catKey] = false;
						}
					}
				} else {
					$scope.showTogImg[catKey]	=	true;
					$scope.imgSvgTog[catKey]	=	'img/ic_indeterminate_check_box_24px.svg';
					$scope.removedCat.push(catKey);
					$scope.colorCheck[catKey]	=	'#F44336';
					if(typeof $rootScope.catData.data[catKey].athcid !== 'undefined') {
						$scope.removedCat.push($rootScope.catData.data[catKey].athcid);
					}
					if($scope.checkPins[catKey] == true) {
						$scope.checkPins[catKey]	=	false;
					}
					$scope.pushDisabled[catKey]	=	true;
					if($rootScope.catData.data[catKey].paid == "Y") {
						$scope.switchNpDisabled[catKey]		=	true;
						$scope.showNpSwitch[catKey]	=	false;
					}
					var indexStch	=	$scope.catIdNpList.indexOf(catKey);
					if(indexStch	>	-1) {
						$scope.catIdNpList.splice(indexStch,1);
					}
					
					var catInfo	=	$rootScope.catData.data[catKey];
					var index	=	$scope.authorisedPaid.indexOf(catInfo.athcnm+'|~~|'+catInfo.athcid);
					if(index > -1) {
						$scope.authorisedPaid.splice(index,1);
					}
					
					var index2	=	$scope.authorisedNonPaid.indexOf(catInfo.athcnm+'|~~|'+catInfo.athcid);
					if(index2 > -1) {
						$scope.authorisedNonPaid.splice(index2,1);
					}
				}
			};
			
			$rootScope.submitCats	=	function(event,targetUrl,secTargetUrl) {
				if($rootScope.catData.error.code == 1){  //$scope.removedCat.length == Object.keys($rootScope.catData.data).length					
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert";
					$rootScope.commonShowContent = "Please select a category to continue";
					return false;
				}
				$rootScope.catStrRemoved	=	"";
				$rootScope.catStrNp	=	"";
				angular.forEach($scope.removedCat,function(value,key) {
					$rootScope.catStrRemoved	+=	value+',';
				});
				angular.forEach($scope.catIdNpList,function(value2,key2) {
					$rootScope.catStrNp	+=	value2+',';
				});
				$rootScope.catStrRemoved	=	$rootScope.catStrRemoved.slice(0,-1);
				$rootScope.catStrNp			=	$rootScope.catStrNp.slice(0,-1);
				$rootScope.targetUrl		=	targetUrl;
				$rootScope.secTargetUrl		=	secTargetUrl;
				$rootScope.checkAuthFlag(1,event,secTargetUrl);
			};
			
			$rootScope.checkAuthFlag	=	function(whichFlag,event,secTargetUrl) {
				if(whichFlag	==	1) {
					var chkauthcatcnt	=	$scope.authorisedPaid.length+$scope.authorisedNonPaid.length;
					if(($rootScope.catData.authltrflg == 0 || (chkauthcatcnt > $rootScope.catData.authcatcnt)) && chkauthcatcnt > 0) {
						$rootScope.proceedStopFlag	=	3;
						$rootScope.next_step = secTargetUrl;
						//$scope.proceedStopCategoryPopupHandle(event);
                         $rootScope.showCommonPop = 'premium_cat_check';
						return false;
					} else {
						$scope.multiparentageCheck(event,secTargetUrl);
					}
				} else {
					$scope.multiparentageCheck(event,secTargetUrl);
				}
			};
			
			
			$scope.toggleCheck	=	function(key,item,cnm,athcid) {
				if($scope.checkPins[key]) {
					if($rootScope.catData.data[key].athchk == 1) {
						if($rootScope.catData.data[key].paid	==	'Y') {
							$scope.nonAuthorisedPaid.push(cnm+'|~~|'+athcid);
							var idx = $scope.authorisedPaid.indexOf(cnm+'|~~|'+athcid);
							if(idx !=-1){
								$scope.authorisedPaid.splice(idx, 1);
							}
						} else {
							$scope.nonAuthorisedNonPaid.push(cnm+'|~~|'+athcid);
							var idx = $scope.authorisedNonPaid.indexOf(cnm+'|~~|'+athcid);
							if(idx !=-1){
								$scope.authorisedNonPaid.splice(idx, 1);
							}
						}
						if($rootScope.catData.data[key].athchk == 1){
							$scope.removedCat.push($rootScope.catData.data[key].athcid);
						}
					} else {
						if($rootScope.catData.data[key].paid	==	'Y') {
							var idx = $scope.authorisedPaid.indexOf(cnm+'|~~|'+athcid);
							if(idx !=-1){
								$scope.authorisedPaid.splice(idx, 1);
							}
						} else {
							var idx = $scope.authorisedNonPaid.indexOf(cnm+'|~~|'+athcid);
							if(idx !=-1){
								$scope.authorisedNonPaid.splice(idx, 1);
							}
						}
					}
				} else {
					if($rootScope.catData.data[key].paid	==	'Y') {
						$scope.authorisedPaid.push(cnm+'|~~|'+athcid);
						var idx = $scope.nonAuthorisedPaid.indexOf(cnm+'|~~|'+athcid);
						if(idx !=-1){
							$scope.nonAuthorisedPaid.splice(idx, 1);
						}
					} else {
						$scope.authorisedNonPaid.push(cnm+'|~~|'+athcid);
						var idx = $scope.nonAuthorisedNonPaid.indexOf(cnm+'|~~|'+athcid);
						if(idx !=-1){
							$scope.nonAuthorisedNonPaid.splice(idx, 1);
						}
					}
					if($rootScope.catData.data[key].athchk == 1){
						var idx2 = $scope.removedCat.indexOf($rootScope.catData.data[key].athcid);
						if(idx2 !=-1){
							$scope.removedCat.splice(idx2, 1);
						}
					}
				}
			};
			
			$scope.multiparentageCheck	=	function(event,secTargetUrl) {
				var parentid;
				$scope.mp_block_msg_obj = '';
				$scope.data_city	=	DATACITY;
				$scope.module	=	'TME';
				$scope.multiparentage_block	=	0;
				$scope.eventHandler	=	event;
				APIServices.multiParentage($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.employees.hrInfo.data.empname,USERID,$rootScope.companyTempInfo.data.companyname,$rootScope.catStr,$rootScope.catStrRemoved).success(function(result) {
					if(result !='' && result !=null) {
						if(result.error.message) {
							var message = result.error.message.trim();
							if(message == 'show popup') {
								var popup_type = result.data.popup_type;
								switch(popup_type) {
									case 'edit' :
										var CatidToBeModerated = result.data.catid;
										var CatnameToBeModerated = result.data.catname;
										$rootScope.CatnameToBeModerated = result.data.catname;
										$scope.mp_block_msg_obj	={'message':'SendCategoryForModeration','CatidToBeModerated':CatidToBeModerated,'CatnameToBeModerated':CatnameToBeModerated};
									break;
									case 'new' :
										var ParentageInfo = result.data.parentage_info;
										$rootScope.ParentageInfo = ParentageInfo;
										$scope.mp_block_msg_obj	={'message':'ShowParentageInfo','ParentageInfo':ParentageInfo};
									break;
								}
								$rootScope.CatidToBeModerated = {};
								$rootScope.CatnameToBeModerated = {};
								$rootScope.mainParentageInfo	=	{};
								switch($scope.mp_block_msg_obj.message) {
									case 'SendCategoryForModeration' :
										$rootScope.CatidToBeModerated = $scope.mp_block_msg_obj.CatidToBeModerated;
										$rootScope.CatnameToBeModerated = $scope.mp_block_msg_obj.CatnameToBeModerated;
										if($rootScope.CatidToBeModerated !='' && $rootScope.CatnameToBeModerated !=''){
											$rootScope.dialogMulti	=	1;
											$rootScope.multiParentagePopupHandle($scope.eventHandler);
											$scope.multiparentage_block = 1;
										}
									break;
									case 'ShowParentageInfo' :
										$rootScope.dialogMulti	=	2;
										$rootScope.mainParentageInfo	=	$scope.mp_block_msg_obj.ParentageInfo;
										if($scope.mp_block_msg_obj.ParentageInfo !=''){
											$rootScope.multiParentagePopupHandle($scope.eventHandler);
											$scope.multiparentage_block = 1;
										}
									break;
								}
								
							} else {
								if($scope.multiparentage_block == 0) {
									$scope.catBlockCheck($scope.eventHandler,secTargetUrl);
								}
							}
						}
					} else {
						if($scope.multiparentage_block == 0) {
							$scope.catBlockCheck($scope.eventHandler,secTargetUrl);
						}
					}
				});
			};

			$scope.changeSwitchNp	=	function(state,catid) {
				if(state	==	true) {
					$scope.catIdNpList.push(catid);
					if($rootScope.catData.data[catid].athchk == 1){
						$scope.catIdNpList.push($rootScope.catData.data[catid].athcid);
					}
				} else {
					var index	=	$scope.catIdNpList.indexOf(catid);
					if(index	>	-1) {
						$scope.catIdNpList.splice(index,1);
					}
					if($rootScope.catData.data[catid].athchk == 1){
						var idx2 = $rootScope.catIdNpList.indexOf($rootScope.catData.data[catid].athcid);
						if(idx2 !=-1){
							$scope.catIdNpList.splice(idx2, 1);
						}
					}
				}
			};
			
			$scope.selectedCats	=	"";
			$rootScope.submitMultiParentage	=	function(selectedCats,event) {
				$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
				if(selectedCats != "") {
					$rootScope.multiparentAddedCat = {};
					$rootScope.multiparentRemoveCat = {};
					$scope.catnameRemoveStr	=	"";
					$scope.catRestStr	=	"";
					$rootScope.catStrRemoved	=	"";
					$scope.parentageInfo    =   $rootScope.mainParentageInfo;
					$scope.selectedCatsString	=	$scope.parentageInfo[selectedCats].catid.replace(/\|~\|/g, ",");
					$scope.selectedCatnameStr   =   $scope.parentageInfo[selectedCats].catname.replace(/\|~\|/g, ",");
					angular.forEach($scope.parentageInfo,function(value,key) {
						if(key != selectedCats) {
							$scope.catRestStr	+=	value.catid.replace(/\|~\|/g, ",")+',';
							$scope.catnameRemoveStr	+=	value.catname.replace(/\|~\|/g, ",")+',';
						}
					});
					$scope.catRestStr	=	$scope.catRestStr.slice(0,-1);
					$scope.catnameRemoveStr	=	$scope.catnameRemoveStr.slice(0,-1);
					
					$scope.catnameRemoveArr	= {};
					$scope.catnameRemoveArr = $scope.catnameRemoveStr.split(',');
					
					$scope.selectedCatnameArr	=	{};
					$scope.selectedCatnameArr = $scope.selectedCatnameStr.split(',');
					
					$scope.catRemoveUniqueArr = [];
					$.each($scope.catnameRemoveArr, function(i, val){
					  if ($.inArray(val,$scope.catRemoveUniqueArr) === -1 && $.inArray(val,$scope.selectedCatnameArr) === -1){
						$scope.catRemoveUniqueArr.push(val);
					  }
					});
					$scope.catRemoveUniqueStr = "";
					$scope.catRemoveUniqueStr = $scope.catRemoveUniqueArr.join(',');
					
					$rootScope.multiparentAddedCat = $scope.selectedCatnameStr;
					$rootScope.multiparentRemoveCat = $scope.catRemoveUniqueStr;
					
					APIServices.sendCatsForModeration($rootScope.catStrRemoved,$scope.companyInfoDialog.data.parentid,$scope.selectedCatsString,$scope.catRestStr,$scope.data_city,$scope.module,$rootScope.companyTempInfo.data.companyname,USERID,$rootScope.empname).success(function(response) {
						if(response.error.code == 0) {
							$rootScope.dialogMulti	=	4;
							$rootScope.multiParentagePopupHandle(event);
							$rootScope.showCatPreviewData();
							//$scope.answer('done');
							$rootScope.showCommonPop = 0;
						} else {
							//alert('There is some issue faced while sending categories for moderation of multi parentage. Please try again.');
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Alert";
							$rootScope.commonShowContent = "There is some issue faced while sending categories for moderation of multi parentage. Please try again.";
							return false;
						}
					});
				} else {
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Alert";
					$rootScope.commonShowContent = "Please select a category to continue";
					return false;
				}
			};
			
			
			$rootScope.sendCatsForMultiParentageMod	=	function(event) {
				$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
				$scope.CatidToBeModerated	=	$rootScope.CatidToBeModerated.replace(/\|~\|/g, ",");
				
				APIServices.sendCatsForModeration($rootScope.catStrRemoved,$scope.companyInfoDialog.data.parentid,"",$scope.CatidToBeModerated,$scope.data_city,$scope.module,$rootScope.companyTempInfo.data.companyname,USERID,$rootScope.empname).success(function(response) {
					if(response.error.code == 0) {
						$rootScope.dialogMulti	=	3;
						$rootScope.multiParentagePopupHandle(event);
						$rootScope.showCatPreviewData();
					} else {
						//alert('There is some issue faced while sending categories for moderation of multi parentage. Please try again.');
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "There is some issue faced while sending categories for moderation of multi parentage. Please try again.";
						return false;
					}
				});
			};
			
			$rootScope.multiParentagePopupHandle	=	function(event) {
				$rootScope.showCommonPop = 'multiParentage'; // popupcommon1.html flag to show multiparentage popup popcommon1.html
			};
			
			$scope.blockForCategoryPopupHandle	=	function(event) {
				$mdDialog.show({
					controller: DialogControllerBlockCategory,
					templateUrl: 'partials/dialogBlockForCat.html',
					parent: angular.element(document.body),
					targetEvent:event
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			};
			
			$scope.proceedStopCategoryPopupHandle	=	function(event) {
				$mdDialog.show({
					controller: DialogControllerproceedStopCategory,
					templateUrl: 'partials/dialogproceedStopForCat.html',
					parent: angular.element(document.body),
					targetEvent:event
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			};
			
			$scope.finalPopPackagePos	=	function(event,url,secTargetUrl) {
				$rootScope.complete_flg = 0;
				//APIServices.getAttributesPage($rootScope.parentid,DATACITY,'CS',USERID).success(function(response) {
				if(secTargetUrl!='demo' && secTargetUrl!=undefined){
					APIServices.check_att_pre($rootScope.parentid,DATACITY,'TME',USERID).success(function(response) {
						if(response.error.code == 0) {
							$rootScope.complete_flg = 1;
							$rootScope.targetUrl = 'appHome.attributes';
							$rootScope.submitFinalCats();
						}else{
							$rootScope.targetUrl = 'appHome.demopage';
							$rootScope.submitFinalCats();
						}
						
					});				
				}else{
					$rootScope.targetUrl = 'appHome.demopage';
					$rootScope.submitFinalCats();
				}
				
				/*APIServices.check_att_pre($rootScope.parentid,DATACITY,'TME',USERID).success(function(response) {
					if(response.error.code == 0) {
							$rootScope.complete_flg = 1;
							$rootScope.targetUrl = 'appHome.attributes';
							$rootScope.submitFinalCats();						
						
					}else{
						$rootScope.targetUrl = 'appHome.demopage';
						$rootScope.submitFinalCats();
					}
					
				});	*/
			};
			$rootScope.PharmTaggedCategory		=	'';
			$rootScope.DocHospTaggedCategory	=	'';
			$rootScope.HotelTaggedCategory		=	'';
			$rootScope.RestTaggedCategory		=	'';
			$rootScope.vetNotVetTaggedCategory		=	'';
			
			$scope.catDataCheck	=	{};
			$scope.catBlockCheck	=	function(event,secTargetUrl) {
				$scope.block_msg_obj	=	"";
				APIServices.checkCatRestriction($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,USERID).success(function(data) {
					$rootScope.counterProceedStop	=	1;
					$scope.catDataCheck	=	data;
					if(data !='' && data !=null){
						if(data.BLOCK) {
							var message = data.BLOCK.message.trim();							
							switch(message) {
								case '5StarAndHomeDelivery' :
									var FiveStarHotelCategory 	= data.BLOCK.FiveStarHotelCategory.trim();
									var HomeDeliveryRestaurant 	= data.BLOCK.HomeDeliveryRestaurant.trim();
									$scope.block_msg_obj	={'message':message,'FiveStarHotelCategory':FiveStarHotelCategory,'HomeDeliveryRestaurant':HomeDeliveryRestaurant};		
								break;
								case 'SingleBrandTagged' :
									var SingleBrandCategory = data.BLOCK.SingleBrandCategory.trim();
									$scope.block_msg_obj	={'message':message,'SingleBrandCategory':SingleBrandCategory};
								break;
								case 'RestPriceFilterTagged' :
									var RestPriceRangeCategory = data.BLOCK.RestPriceRangeCategory.trim();
									$scope.block_msg_obj	={'message':message,'RestPriceRangeCategory':RestPriceRangeCategory};
								break;
								case 'LandlineMandatoryTagged' :
									var LandlineMandatoryCategory = data.BLOCK.LandlineMandatoryCategory.trim();
									$scope.block_msg_obj	={'message':message,'LandlineMandatoryCategory':LandlineMandatoryCategory};
								break;
								case 'ExclusiveTagged' :
									var ExclusiveTaggedCategory = data.BLOCK.ExclusiveTaggedCategory.trim();
									$scope.block_msg_obj	={'message':message,'ExclusiveTaggedCategory':ExclusiveTaggedCategory};
								break;
								case 'StarRatingTagged' :
									var StarRatingTaggedCategory = data.BLOCK.StarRatingTaggedCategory.trim();
									$scope.block_msg_obj	={'message':message,'StarRatingTaggedCategory':StarRatingTaggedCategory};
								break;
								case 'PharmCatRestriction' :  
									var PharmTaggedCategory 	= data.BLOCK.PharmTaggedCategory.trim();
									var DocHospTaggedCategory 	= data.BLOCK.DocHospTaggedCategory.trim();
									$scope.block_msg_obj	={'message':message,'PharmTaggedCategory':PharmTaggedCategory,'DocHospTaggedCategory':DocHospTaggedCategory};
								break;
								case 'HotelRestRestriction' :
									var HotelTaggedCategory = data.BLOCK.HotelTaggedCategory.trim();
									var RestTaggedCategory 	= data.BLOCK.RestTaggedCategory.trim();
									$scope.block_msg_obj	={'message':message,'HotelTaggedCategory':HotelTaggedCategory,'RestTaggedCategory':RestTaggedCategory};		
								break;
								case 'PizzaOutletsRest' :
									var PizzaoutletsBrand = data.BLOCK.PizzaoutletsBrand.trim();
									var PizzaoutletsCategory = data.BLOCK.PizzaoutletsCategory.trim();
									$scope.block_msg_obj	={'message':message,'PizzaoutletsBrand':PizzaoutletsBrand,'PizzaoutletsCategory':PizzaoutletsCategory};		
								break;
								case 'drySateCatRest' :
									var drySateRestCategory = data.BLOCK.drySateRestCategory.trim();
									$scope.block_msg_obj	={'message':message,'drySateRestCategory':drySateRestCategory};
								break;
								case 'moviescategoryRestriction' :
									var moviesTaggedCatid = data.BLOCK.movietagdcatid.trim();
									var moviesTaggedCatName = data.BLOCK.movietagdcatname.trim();
									$scope.block_msg_obj	={'message':message,'moviesTaggedCategory':moviesTaggedCatName};
								break;
								case 'PriceFiltersRestriction' :									
									var restTaggedPriceCombo = data.BLOCK.RestPriceRangeCategory.trim();
									$scope.block_msg_obj	={'message':message,'restTaggedPriceCombo':restTaggedPriceCombo};
								break;
								case 'PurevegNonvegRestriction' :
									var PureVegCategory = data.BLOCK.PureVegCategory.trim();
									var NonVegCategory 	= data.BLOCK.NonVegCategory.trim();
									$scope.block_msg_obj	={'message':message,'PureVegCategory':PureVegCategory,'NonVegCategory':NonVegCategory};		
								break;
								case 'vetNonvetRestriction' :
									var vetNonVetCatid 		= data.BLOCK.vet_catid.trim();
									var vetNonVetCategory 	= data.BLOCK.vet_catname.trim();
									$scope.block_msg_obj	={'message':message,'vetNonVetCatid':vetNonVetCatid,'vetNonVetCategory':vetNonVetCategory};		
									
								break;
							}
							
							$rootScope.blockCatPop	=	0;
							$rootScope.FiveStarHotelCategory	=	{};
							$rootScope.HomeDeliveryRestaurant	=	{};
							$rootScope.SingleBrandCategoryArr	=	{};
							$rootScope.RestPriceRangeCategory	=	{};
							$rootScope.LandlineMandatoryTagged	=	{};
							$rootScope.ExclusiveTaggedCategory	=	{};
							$rootScope.StarRatingTaggedCategory	=	{};
							$rootScope.PizzaoutletsBrand		=	{};
							$rootScope.PizzaoutletsCategory		=	{};
							$rootScope.drySateRestCategory		=	{};
							$rootScope.moviesTaggedCategory		=	{};
							$rootScope.restTaggedPriceCombo		= 	{};
							$rootScope.NonVegCategory 			=	{};
							$rootScope.PureVegCategory 			= 	{};
							switch($scope.block_msg_obj.message) {
								case '5StarAndHomeDelivery' :
									$rootScope.FiveStarHotelCategory 	= $scope.block_msg_obj.FiveStarHotelCategory;
									$rootScope.HomeDeliveryRestaurant 	= $scope.block_msg_obj.HomeDeliveryRestaurant;
									if($rootScope.FiveStarHotelCategory !='' && $rootScope.HomeDeliveryRestaurant !=''){
										$rootScope.blockCatPop	=	1;
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
									}
								break;
								case 'SingleBrandTagged' :
									$rootScope.SingleBrandCategoryArr = $scope.block_msg_obj.SingleBrandCategory;
									if($rootScope.SingleBrandCategoryArr !=''){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	2;
									}
								break;
								case 'RestPriceFilterTagged' :
									$rootScope.RestPriceRangeCategory = $scope.block_msg_obj.RestPriceRangeCategory.trim();
									if($rootScope.RestPriceRangeCategory !=''){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	3;
									}
								break;
								case 'LandlineMandatoryTagged' :
									$rootScope.LandlineMandatoryCategory = $scope.block_msg_obj.LandlineMandatoryCategory.trim();
									if($rootScope.LandlineMandatoryCategory !=''){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	4;
									}
								break;
								case 'ExclusiveTagged' :
									$rootScope.ExclusiveTaggedCategory = $scope.block_msg_obj.ExclusiveTaggedCategory.trim();
									if($rootScope.ExclusiveTaggedCategory !=''){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	5;
									}
								break;
								case 'StarRatingTagged' :
									$rootScope.StarRatingTaggedCategory = $scope.block_msg_obj.StarRatingTaggedCategory.trim();
									if($rootScope.StarRatingTaggedCategory !=''){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	6;
									}
								break;
								case 'PharmCatRestriction' :
									$rootScope.PharmTaggedCategory 		= $scope.block_msg_obj.PharmTaggedCategory.trim();
									$rootScope.DocHospTaggedCategory 	= $scope.block_msg_obj.DocHospTaggedCategory.trim();
									if(($rootScope.PharmTaggedCategory !='') && ($rootScope.DocHospTaggedCategory !='')){
										 //$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat'; 
										$rootScope.blockCatPop	=	7;
									}
								break;
								case 'HotelRestRestriction' :
									$rootScope.HotelTaggedCategory 	= $scope.block_msg_obj.HotelTaggedCategory;
									$rootScope.RestTaggedCategory 	= $scope.block_msg_obj.RestTaggedCategory;
									if($rootScope.HotelTaggedCategory !='' && $rootScope.RestTaggedCategory !=''){
										$rootScope.blockCatPop	=	8;
										//$scope.blockForCategoryPopupHandle(event);
                                        $rootScope.showCommonPop = 'block_for_cat';
									}
								break;
								case 'PizzaOutletsRest' :
									$rootScope.PizzaoutletsBrand = $scope.block_msg_obj.PizzaoutletsBrand.trim();
									$rootScope.PizzaoutletsCategory = $scope.block_msg_obj.PizzaoutletsCategory.trim();
									if($rootScope.PizzaoutletsBrand !='' && $rootScope.PizzaoutletsCategory !=''){
										//$scope.blockForCategoryPopupHandle(event);
										$rootScope.blockCatPop	=	9;
										$rootScope.showCommonPop = 'block_for_cat';
									}
								break;
								case 'drySateCatRest' :
									$rootScope.drySateRestCategory = $scope.block_msg_obj.drySateRestCategory;
									if($rootScope.drySateRestCategory !=''){
										//$scope.blockForCategoryPopupHandle(event);
										$rootScope.blockCatPop	=	10;
										$rootScope.showCommonPop = 'block_for_cat';
									}
								break;
								case 'moviescategoryRestriction' :
									//console.log(moviesTaggedCategory);									
									$rootScope.moviesTaggedCategory = $scope.block_msg_obj.moviesTaggedCategory;
									console.log("here---"+$rootScope.moviesTaggedCategory);
									if($rootScope.moviesTaggedCategory !=''){
										//$scope.blockForCategoryPopupHandle(event);
										$rootScope.blockCatPop	=	11;
										$rootScope.showCommonPop = 'block_for_cat';
									}
								break;
								case 'PriceFiltersRestriction' :
									//console.log(moviesTaggedCategory);									
									$rootScope.restTaggedPriceCombo = $scope.block_msg_obj.restTaggedPriceCombo;									
									if($rootScope.restTaggedPriceCombo !=''){
										//$scope.blockForCategoryPopupHandle(event);
										$rootScope.blockCatPop	=	12;
										$rootScope.showCommonPop = 'block_for_cat';
									}
								break;
								case 'PurevegNonvegRestriction' :
									$rootScope.NonVegCategory 		= $scope.block_msg_obj.NonVegCategory;
									$rootScope.PureVegCategory 		= $scope.block_msg_obj.PureVegCategory;
									if($rootScope.NonVegCategory !='' && $rootScope.PureVegCategory !=''){
										$scope.PureVegCategoryCount	= ($rootScope.PureVegCategory.split('|~|')).length;
										$scope.NonVegCategoryCount	= ($rootScope.NonVegCategory.split('|~|')).length;
										$rootScope.blockCatPop		=	13;
                                        $rootScope.showCommonPop 	= 	'block_for_cat';
									}
								break;
								case 'vetNonvetRestriction' :								
									$rootScope.vetNotVetTaggedCategory 		= $scope.block_msg_obj.vetNonVetCategory;									
									if($rootScope.vetNotVetTaggedCategory !='' ){										
										$rootScope.blockCatPop		=	14;
                                        $rootScope.showCommonPop 	= 	'block_for_cat';
									}
									console.log($rootScope.vetNotVetTaggedCategory);
								break;
							}
						} else if(data.CANPROCEED) {
							if(data.CANPROCEED.popupcount > 0){
								$rootScope.restFunctionCheck($rootScope.counterProceedStop,event);
							}else{
								if($rootScope.targetUrl == 'appHome.areaSel') {
									$scope.url = 'appHome.areaSel';
									$scope.finalPopPackagePos(event,$scope.url,secTargetUrl);
								} else {
									$rootScope.submitFinalCats();
								}
							}
						}
					}
				});
			};
			
			$rootScope.proceedStopFlag	=	"";
			$scope.PremiumTaggedCategory	=	{};
			$scope.PromtRatingsCategory	=	{};
			$rootScope.ExclCategory = {};
			$rootScope.RestaurantMissingCategory	=	{};
			$rootScope.AuthorisedTaggedCategory	=	{};
			$rootScope.RestrictedTaggedCategory = {};
			$rootScope.DocumentList	=	{};
			$rootScope.DocumentURL	= "";
			$rootScope.premium_flag	=	"";
			$rootScope.restFunctionCheck	=	function(checkParam,event,next_url) {
				$rootScope.showCommonPop = 0;
				$rootScope.counterProceedStop++;
				if(typeof $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam] !== 'undefined') {
					switch($scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].message) {
						case 'PremiumCategory':
							$rootScope.proceedStopFlag	=	1;
							$rootScope.PremiumTaggedCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].PremiumTaggedCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'RestMissingCategory' :
							$rootScope.proceedStopFlag	=	2;
							$rootScope.RestaurantMissingCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].RestaurantMissingCategory.trim();
							$rootScope.premium_flag = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].premium_flag;
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'AuthorisedCategory' :
							$rootScope.proceedStopFlag	=	3;
							$rootScope.AuthorisedTaggedCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].AuthorisedTaggedCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'DocumentRequired' :
							$rootScope.proceedStopFlag	=	4;
							$rootScope.RestrictedTaggedCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].RestrictedTaggedCategory.trim();
							$rootScope.DocumentList = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].DocumentList.trim();
							$rootScope.DocumentURL = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].DocumentURL.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'PromtRatings' :
							$rootScope.proceedStopFlag	=	5;
							$rootScope.PromtRatingsCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].PromtRatingsCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'ExclTagged' :
							$rootScope.proceedStopFlag	=	6;
							$rootScope.ExclCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].ExclCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
                            $rootScope.showCommonPop = 'premium_cat_check';
						break;
						case '_24hrsTagged' :
							$rootScope.proceedStopFlag	=	7;
							$rootScope._24hrsCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam]._24hrsCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
							$rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'cinemaHallsTagged' :
							$rootScope.proceedStopFlag	=	8;
							$rootScope.cinemaHallsCategory = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].cinemaHallsCategory.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
							$rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'ApprovedModerateCat' :
							$rootScope.proceedStopFlag	=	9;
							$rootScope.ApprovedModerateCat = $scope.catDataCheck.CANPROCEED['popupmsg'+checkParam].ApprovedModerateCat.trim();
							//$scope.proceedStopCategoryPopupHandle(event);
							$rootScope.showCommonPop = 'premium_cat_check';
						break;
						case 'CuisinePriceMissing' :
							$rootScope.proceedStopFlag	=	10;
							$rootScope.showCommonPop = 'premium_cat_check';
						break;	
					}
				} else {
					$scope.movie_timing	=	"";
					if($rootScope.targetUrl == 'appHome.areaSel') {
						$scope.url = 'appHome.areaSel';
						$scope.finalPopPackagePos(event,$scope.url,next_url);
					} else {
						$rootScope.submitFinalCats();
					}
				}
			};
			
			$rootScope.submitFinalCats	=	function() {
		
				$scope.instant_yes	=	function(){ 
					$scope.instantOverlay	=	0;
					$scope.instantConfirm	=	0;
					$scope.instantFlag		=	1;
					
					//~ APIServices.save_dc_cat($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$rootScope.catStrNp,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) {
						APIServices.submitCatsFinal($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$scope.movieTimings,$scope.authorisedPaid,$scope.nonAuthorisedPaid,$scope.authorisedNonPaid,$scope.nonAuthorisedNonPaid,$rootScope.catStrNp,$scope.instantFlag,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) {
						if(response.error.code == 0) {
							if($rootScope.targetUrl == "") {
								if($scope.instantFlag == 1){
									APIServices.categoryInstantLive($rootScope.parentid).success(function(response) {
										if(response.error.code	==	0){
											window.location	=	$rootScope.secTargetUrl;
										}else{
											alert(response.error.msg);
											window.location	=	$rootScope.secTargetUrl;
										}
									});
								}else{
									window.location	=	$rootScope.secTargetUrl;
								}
							} else {
								APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
									$rootScope.budgetVersion	=	response.version;
									if($scope.instantFlag == 1){
										APIServices.categoryInstantLive($rootScope.parentid).success(function(response) {
											if(response.error.code	==	0){
												
												APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
													if(respdoc.error.code	==	0){
														if(respdoc.data == 1) {
															window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.budgetVersion+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
														} else {
															window.location	=	'../business/bform_doctor.php?ver='+$rootScope.budgetVersion+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
														}
													}else{
														$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
													}
												});
											}else{
												alert(response.error.msg);
												APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
													if(respdoc.error.code	==	0){
														if(respdoc.data == 1) {
															window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.budgetVersion+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
														} else {
															window.location	=	'../business/bform_doctor.php?ver='+$rootScope.budgetVersion+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
														}
													}else{
														$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
													}
												});
											}
										});
									}else{
										APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
											if(respdoc.error.code	==	0){
												if(respdoc.data == 1) {
													window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.budgetVersion+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
												} else {
													window.location	=	'../business/bform_doctor.php?ver='+$rootScope.budgetVersion+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
												}
											}else{
												$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
											}
										});										
									}
								});
							}
						} else {
							$mdToast.show(
								$mdToast.simple()
								.content('Data Cannot be submitted. Please try again later')
								.position('bottom right')
								.hideDelay(4000)
							);
						}
						});
					//~ });
				};
				
				$scope.instant_no	=	function(){ 
					$scope.instantOverlay	=	0;
					$scope.instantConfirm	=	0;
					//~ APIServices.save_dc_cat($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$rootScope.catStrNp,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) {
						APIServices.submitCatsFinal($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$scope.movieTimings,$scope.authorisedPaid,$scope.nonAuthorisedPaid,$scope.authorisedNonPaid,$scope.nonAuthorisedNonPaid,$rootScope.catStrNp,$scope.instantFlag,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) {
						if(response.error.code == 0) {
							
							if($rootScope.targetUrl == "") {
								window.location	=	$rootScope.secTargetUrl;
							} else {
								APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
									$rootScope.budgetVersion	=	response.version;
									APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
										if(respdoc.error.code	==	0){
											if(respdoc.data == 1) {
												window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.budgetVersion+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
											} else {
												window.location	=	'../business/bform_doctor.php?ver='+$rootScope.budgetVersion+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
											}
										}else{
											$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
										}
									});
								});
							}
						} else {
							$mdToast.show(
								$mdToast.simple()
								.content('Data Cannot be submitted. Please try again later')
								.position('bottom right')
								.hideDelay(4000)
							);
						}
						});
					//~ });
				};
				
				if($scope.showColourCode == 1){
					$scope.instantOverlay	=	1;
					$scope.instantConfirm	=	1;
					$scope.instantFlag		=	0;
				}else{
					//~ APIServices.save_dc_cat($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$rootScope.catStrNp,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) { 
						APIServices.submitCatsFinal($rootScope.parentid,$scope.data_city,$scope.module,$rootScope.catStr,$rootScope.catStrRemoved,$scope.movieTimings,$scope.authorisedPaid,$scope.nonAuthorisedPaid,$scope.authorisedNonPaid,$scope.nonAuthorisedNonPaid,$rootScope.catStrNp,$scope.instantFlag,$rootScope.allpaidcat,$rootScope.allnonpaidcat).success(function(response) {
						if(response.error.code == 0) {
							
							if($rootScope.targetUrl == "") {
								window.location	=	$rootScope.secTargetUrl;
							} else {
								APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
									$rootScope.budgetVersion	=	response.version;
									if($rootScope.targetUrl == 'appHome.demopage'){
										APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
											if(respdoc.error.code	==	0){
												if(respdoc.data == 1) {
													window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.budgetVersion+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
												} else {
													window.location	=	'../business/bform_doctor.php?ver='+$rootScope.budgetVersion+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
												}
											}else{
												$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
											}
										});
									}else{
										$state.go($rootScope.targetUrl,{parid:$rootScope.parentid,page:$rootScope.extraHandler,ver:response.version});
									}
								});
							}
						} else {
							$mdToast.show(
								$mdToast.simple()
								.content('Data Cannot be submitted. Please try again later')
								.position('bottom right')
								.hideDelay(4000)
							);
						}
					});
				//~ });
			}
				
		};
		
		function DialogPopPackagePos($scope, $mdDialog,$mdToast) {
			$scope.goToAreaPage	=	function() {
				$mdDialog.hide();
				$rootScope.submitFinalCats();
			};
			
			$scope.goToSelPackage	=	function() {
				$mdDialog.hide();
				$rootScope.targetUrl	=	"appHome.customPackage";
				$rootScope.submitFinalCats();
			};
			
			$scope.saveAndExit	=	function() {
				$mdDialog.hide();
				$rootScope.saveAndExitCat();
			};
			
			$scope.resetCampAskDiv	=	0;
			$scope.resetCampaignAsk	=	function(ev) {
				$scope.resetCampAskDiv	=	1;
			};
			
			$scope.resetCampaign	=	function() {
				$mdDialog.hide();
				$rootScope.campaignReset();
			};
			
			$scope.closeResetDiv	=	function(ev) {
				$scope.resetCampAskDiv	=	0;
			};
		}
		
		$rootScope.campaignReset	=	function() {
			APIServices.resetCampaign($rootScope.parentid,$rootScope.employees.hrInfo.data.empname).success(function(response) {
				$state.reload();
				if(response.error_code == 0) {
					var message	=	"Campaigns Reset done successfully";
				} else {
					var message	=	response.message;
				}
				$mdToast.show(
					$mdToast.simple()
					.content(message)
					.position('bottom right')
					.hideDelay(3000)
				);
			});
		};
		
		/*function DialogControllerMultiParentage($scope, $mdDialog,$mdToast) {
			$scope.companyInfoDialog	=	$rootScope.companyTempInfo;
			$scope.hide = function() {
				$mdDialog.hide();
			};
			$scope.cancel = function() {
				$mdDialog.cancel();
			};
			$scope.answer = function(answer) {
				$mdDialog.hide();
			};
			$scope.multiparentAddedCat = $rootScope.multiparentAddedCat;
			$scope.multiparentRemoveCat = $rootScope.multiparentRemoveCat;
			$scope.showLoader	=	0;
			$scope.dialogMulti	=	$rootScope.dialogMulti;
			$scope.data_city	=	DATACITY;
			$scope.module	=	'TME';
			$scope.parentageInfo	=	$rootScope.mainParentageInfo;
			$scope.CatnameToBeModerated	=	$rootScope.CatnameToBeModerated;
			$scope.sendCatsForMultiParentageMod	=	function(event) {
				$scope.CatidToBeModerated	=	$rootScope.CatidToBeModerated.replace(/\|~\|/g, ",");
				$scope.CatnameToBeModerated	=	$rootScope.CatnameToBeModerated;
				$scope.showCatName			=	$rootScope.CatnameToBeModerated.replace(/\|~\|/g, ",");
				
				APIServices.sendCatsForModeration($rootScope.catStrRemoved,$scope.companyInfoDialog.data.parentid,"",$scope.CatidToBeModerated,$scope.data_city,$scope.module,$rootScope.companyTempInfo.data.companyname,USERID,$rootScope.empname).success(function(response) {
					if(response.error.code == 0) {
						$rootScope.dialogMulti	=	3;
						$rootScope.multiParentagePopupHandle(event);
						$rootScope.showCatPreviewData();
						$scope.answer('done');
					} else {
						//alert('There is some issue faced while sending categories for moderation of multi parentage. Please try again.');
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = "There is some issue faced while sending categories for moderation of multi parentage. Please try again.";
						return false;
					}
				});
			};
			
			$scope.selectedCats	=	"";
			$scope.submitMultiParentage	=	function(selectedCats,event) {
				if(selectedCats != "") {
					$rootScope.multiparentAddedCat = {};
					$rootScope.multiparentRemoveCat = {};
					$scope.catnameRemoveStr	=	"";
					$scope.catRestStr	=	"";
					$rootScope.catStrRemoved	=	"";
					$scope.selectedCatsString	=	$scope.parentageInfo[selectedCats].catid.replace(/\|~\|/g, ",");
					$scope.selectedCatnameStr   =   $scope.parentageInfo[selectedCats].catname.replace(/\|~\|/g, ",");
					angular.forEach($scope.parentageInfo,function(value,key) {
						if(key != selectedCats) {
							$scope.catRestStr	+=	value.catid.replace(/\|~\|/g, ",")+',';
							$scope.catnameRemoveStr	+=	value.catname.replace(/\|~\|/g, ",")+',';
						}
					});
					$scope.catRestStr	=	$scope.catRestStr.slice(0,-1);
					$scope.catnameRemoveStr	=	$scope.catnameRemoveStr.slice(0,-1);
					
					$scope.catnameRemoveArr	= {};
					$scope.catnameRemoveArr = $scope.catnameRemoveStr.split(',');
					
					$scope.selectedCatnameArr	=	{};
					$scope.selectedCatnameArr = $scope.selectedCatnameStr.split(',');
					
					$scope.catRemoveUniqueArr = [];
					$.each($scope.catnameRemoveArr, function(i, val){
					  if ($.inArray(val,$scope.catRemoveUniqueArr) === -1 && $.inArray(val,$scope.selectedCatnameArr) === -1){
						$scope.catRemoveUniqueArr.push(val);
					  }
					});
					$scope.catRemoveUniqueStr = "";
					$scope.catRemoveUniqueStr = $scope.catRemoveUniqueArr.join(',');
					
					$rootScope.multiparentAddedCat = $scope.selectedCatnameStr;
					$rootScope.multiparentRemoveCat = $scope.catRemoveUniqueStr;
					
					APIServices.sendCatsForModeration($rootScope.catStrRemoved,$scope.companyInfoDialog.data.parentid,$scope.selectedCatsString,$scope.catRestStr,$scope.data_city,$scope.module,$rootScope.companyTempInfo.data.companyname,USERID,$rootScope.empname).success(function(response) {
						if(response.error.code == 0) {
							$rootScope.dialogMulti	=	4;
							$rootScope.multiParentagePopupHandle(event);
							$rootScope.showCatPreviewData();
							$scope.answer('done');
						} else {
							//alert('There is some issue faced while sending categories for moderation of multi parentage. Please try again.');
							$rootScope.showCommonPop = 1;
							$rootScope.commonTitle = "Genio";
							$rootScope.commonShowContent = "There is some issue faced while sending categories for moderation of multi parentage. Please try again.";
							return false;
						}
					});
				} else {
					$mdToast.show(
						$mdToast.simple()
						.content('Please select a category to continue')
						.position('bottom right')
						.hideDelay(4000)
					);
					return false;
				}
			};
		}*/
		
		/*function DialogControllerBlockCategory($scope, $mdDialog,$mdToast) {
			$scope.hide = function() {
				$mdDialog.hide();
			};
			APIServices.getModuleType().success(function(response) {
			$scope.moduleType	=	response[0];
				
			});
			
			$scope.parentid					=   $rootScope.parentid;
			$scope.blockCatPop					=	$rootScope.blockCatPop;
			$scope.FiveStarHotelCategory		=	$rootScope.FiveStarHotelCategory;
			$scope.HomeDeliveryRestaurant		=	$rootScope.HomeDeliveryRestaurant;
			$scope.SingleBrandCategoryArr		=	$rootScope.SingleBrandCategoryArr;
			$scope.RestPriceRangeCategory		=	$rootScope.RestPriceRangeCategory;
			$scope.LandlineMandatoryCategory	=	$rootScope.LandlineMandatoryCategory;
			$scope.ExclusiveTaggedCategory		=	$rootScope.ExclusiveTaggedCategory;
			$scope.StarRatingTaggedCategory		=	$rootScope.StarRatingTaggedCategory;
			$scope.RestrictedTaggedCategory		=	$rootScope.RestrictedTaggedCategory;
			$scope.DocumentList					=	$rootScope.DocumentList;
			$scope.DocumentURL					=	$rootScope.DocumentURL;
			$scope.uploadRedirect	=	function() {
				window.open($rootScope.DocumentURL,"docupload","");
				setTimeout(function () {
					$mdDialog.hide();
				}, 1000);
			};
			$scope.PharmTaggedCategory			=	$rootScope.PharmTaggedCategory;
			$scope.DocHospTaggedCategory		=	$rootScope.DocHospTaggedCategory;
			if($rootScope.PharmTaggedCategory !== '') {
				$scope.PharmTaggedCount				=  ($rootScope.PharmTaggedCategory.split('|~|')).length;
			}
			if($rootScope.DocHospTaggedCategory !== '') {
				$scope.DocHospTaggedCount			=  ($rootScope.DocHospTaggedCategory.split('|~|')).length;
			}
			$scope.HotelTaggedCategory			=	$rootScope.HotelTaggedCategory;
			$scope.RestTaggedCategory			=	$rootScope.RestTaggedCategory;
			if($rootScope.HotelTaggedCategory !== '') {
				$scope.HotelTaggedCount				=  ($rootScope.HotelTaggedCategory.split('|~|')).length;
			}
			if($rootScope.RestTaggedCategory !== '') {
				$scope.RestTaggedCount			=  ($rootScope.RestTaggedCategory.split('|~|')).length;
			}			
		}*/
		 $rootScope.uploadRedirect   =   function() {
                window.open($rootScope.DocumentURL,"docupload","");
                $rootScope.showCommonPop=0;
                /*setTimeout(function () {
                    //$mdDialog.hide();
                    $rootScope.showCommonPop=0;
                }, 1000);*/
		};
		function DialogControllerproceedStopCategory($scope, $mdDialog,$mdToast) {
			$scope.hide = function() {
				$mdDialog.hide();
			};
			
			$scope.proceedStopFlag	=	$rootScope.proceedStopFlag;
			$scope.PremiumTaggedCategory	=	$rootScope.PremiumTaggedCategory;
			$scope.PromtRatingsCategory = $rootScope.PromtRatingsCategory;
			$scope._24hrsCategory		=   $rootScope._24hrsCategory;
			$scope.cinemaHallsCategory	=   $rootScope.cinemaHallsCategory;
			
			$scope.counterProceedStop	=	$rootScope.counterProceedStop;
			$scope.restFunctionCheck	=	function(paramSend,event) {
				$mdDialog.hide();
				$rootScope.restFunctionCheck(paramSend,event);
			}
			
			$scope.checkAuthFlag	=	function(event) {
				$mdDialog.hide();
				$rootScope.checkAuthFlag(2,event);
			}
			$scope.premium_flag					=	$rootScope.premium_flag;
			$scope.RestaurantMissingCategory	=	$rootScope.RestaurantMissingCategory;
			$scope.AuthorisedTaggedCategory		=	$rootScope.AuthorisedTaggedCategory;
			$scope.RestrictedTaggedCategory		=	$rootScope.RestrictedTaggedCategory;
			$scope.ExclCategory					=	$rootScope.ExclCategory;
			$scope.DocumentList					=	$rootScope.DocumentList;
			$scope.DocumentURL					=	$rootScope.DocumentURL;
			$scope.uploadRedirect	=	function() {
				window.open($rootScope.DocumentURL,"docupload","");
				setTimeout(function () {
					$mdDialog.hide();
				}, 1000);
			};
		}
		$scope.reset_category = function() {
			if($rootScope.catData.error.code != 1){
				APIServices.categoryResetAPI($rootScope.parentid).success(function(response) {
					 $state.go('appHome.category',{parid:$rootScope.parentid,page:''})
				});
			}
		}
		
		APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
			$rootScope.budgetVersion	=	response.version;
		});
		
		$scope.gotopricechart = function() {
			$state.go('appHome.pricechartnew',{parid:$stateParams.parid,ver:$stateParams.ver,page:$stateParams.page});
		}
		
		///////////////////////////////////////////////SAVE AS FREELISTING/////////////////////////////////////////////////////
		
		$rootScope.saveAsNonPaid    =   function(event) {
				$rootScope.noPointsUpdate = 0;
				$('.navbar-fixed').css({
							'z-index': '0'
				});
				$mdDialog.show({
					controller: saveFreelistingController,
					templateUrl: 'partials/saveFreeListing.html',
					parent: angular.element(document.body)
				})
        };

        function saveFreelistingController($scope){
                $scope.showVal  =   5; //  $rootScope.showValDiv  =   5; -- same as assigning to $rootScope.showValDiv -- if its 3 - it will show to select warm,cold ,hot-- if its 1 then it will show the verification code , 5 - will redirect to show the message of saving data.
                $scope.email 	= 	'';
                $scope.mobileForverifyMultiple = $rootScope.companyTempInfo.data.mobile;
                var mobile_arr = $scope.mobileForverifyMultiple.split(',');
                $scope.mobileForVerification ='';
                $scope.enteredVerificationCode = {};
                $scope.enteredVerificationCode.code = '';
                $scope.enteredVerificationCode.errorCode = '';
                $.each( mobile_arr, function( key, value ) {
                    if(value!='' && value.length == 10){
                        $scope.mobileForVerification = value;
                        return false;
                    }
                });
                $scope.emailForVerification  = $rootScope.companyTempInfo.data.email;
                var email_arr = $scope.emailForVerification.split(',');
                $.each( email_arr, function( key, value ) {
                    if(value!=''){
                        $scope.email = value;
                        return false;
                    }
                });                
                $scope.exitFromPage = 'DEMOPAGE';
                $scope.sendVerificationCode =   function(){
                    APIServices.sendVerificationCode($scope.mobileForVerification,$scope.email,$rootScope.companyTempInfo.data.companyname,$rootScope.companyTempInfo.data.parentid).success(function(response) {
                        $('.show_vc_alerts_back').show();
                        $('#show_vc_alerts').show();
                        $('#show_vc_alerts_msg').html(response.errorMsg);
                        $scope.enteredVerificationCode.errorCode = response.errorCode;
                    });
                };
                $scope.callNextVcDiv    =   function(errorCode){ 
                        $('.show_vc_alerts_back').hide();
                        $('#show_vc_alerts').hide();
                        $('#show_vc_alerts_msg').html('');
                        if(errorCode==0){
                            $scope.show_next($scope.showVal);
                        }
                };
                $scope.verifyVerificationCode   =   function(){
                    var verificationCode = $scope.enteredVerificationCode['code'];
                    APIServices.verifyVerificationCode($scope.mobileForVerification,verificationCode,$rootScope.companyTempInfo.data.parentid).success(function(response) {
                        $('.show_vc_alerts_back').show();
                        $('#show_vc_alerts').show();
                        $('#show_vc_alerts_msg').html(response.errorMsg);
                        $scope.enteredVerificationCode.errorCode = response.errorCode;
                        if(response.errorCode==0){
                            $scope.enteredVerificationCode.verifiedStatus=1;
                        }
                    });
                };
                $scope.submitFreeListingContract    =   function(data_tag){
                    if(data_tag== 'follow up' && ($scope.enteredVerificationCode.followUpDate   ==  undefined || $scope.enteredVerificationCode.followUpDate    ==  '')){
                          $mdToast.show(
                            $mdToast.simple({
                                textContent: "Please Select Date!",
                                parent: $document[0].querySelector('.sendVCPOPup_show'),
                                position: 'top right',
                                hideDelay: 5000
                            })
                        );
                        return false;
                    }else{
                        //~ // insert into 171 tables and also in web
                        $scope.enteredVerificationCode.verifiedStatus=1;
						APIServices.updMnTabSaveAsNonPaid($rootScope.companyTempInfo.data.parentid,'').success(function(response1){
								$mdDialog.show(
									$mdDialog.alert()
									.clickOutsideToClose(false)
									.title('Save As Free Listing ')
									.content(response1.responsemsg)
									.ariaLabel('Alert Dialog Demo')
									.ok('Ok')
								).then(function() {
										APIServices.insertSaveLogs($rootScope.parentid,response1.response171,response1.responseweb,'').success(function(resp){
											$mdDialog.hide();
											if(response1.responsemsg == 'Not Allowed to Proceed as this is a Paid Contract!!!'){
												return false;
											}else{
												$rootScope.setNoMenu	=	0;
												$state.go('appHome',{currPage:'',srchparam:'',srchWhich:'',extraVals:''});
											}
										});
								});
						});
                    }
                };
                $scope.show_next    =   function(val){
                    val =   (val    +   1);
                    if(val==3){
                        $('.show_vc_alerts_back').show();
                        $('#show_vc_alerts').show();
                        $('#show_vc_alerts_msg').html("Saving Data. Please Wait...");
                        $('.show_vc_alerts_ok_btn').hide();
                        $scope.submitFreeListingContract('warm');
                    }else if(val==6){
                        $scope.submitFreeListingContract('warm');
                    }else{
                        $scope.showVal  =   val;
                    }
                };
                $scope.preventDefaultKeydown    =   function(event){
                    event.preventDefault();
                    return false;
                };
                $scope.closesaveList    =   function(){
                    $mdDialog.hide();
                };
                if($scope.showVal==5){ // onclick it will invoke this
                    $scope.show_next($scope.showVal);
                }
            }
		
		
		/////////////////////////////////////////////SAVE AS FREELISTING///////////////////////////////////////////////////////
	});
	tmeModuleApp.controller('attributesController',function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		$rootScope.setNoMenu	=	1;
		var self = this;
		$rootScope.parentid	=	returnState.paridInfo;
		$rootScope.extraHandler	=	$stateParams.page;
		
		//Function used to close sidenav
		$mdSidenav('left').close().then(function () {
			$('.showSPList').addClass('none');
        });
        
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
		$scope.checkAttr    =   {};
        $scope.selectAttr   =   {};
        $rootScope.radioAttr   =   {};
        $scope.radioAttr1   =   {};
        $scope.frmAttr      =   {};
        $scope.toAttr       =   {};
        $scope.timeAttr     =   {};
        $scope.textAttr     =   {};
        $scope.chkMulAttr   =   {};
        $scope.chkMulAttr1   =   {};
        $rootScope.fin_res  =  {}
		$rootScope.attributeData = {};
		$scope.facilitynames   = [];    
		$scope.result_final = []; 
		$scope.limitArr = {}; 
		$scope.unique_code_str  =   {};
		$rootScope.checkmult_model = {};
		$scope.usercode    =   USERID;
		$scope.dispDiv   = [];
		$scope.dispDiv1  = {};
		$scope.radio_yes = {};
		$scope.radio_no  = {};
		$scope.timefrmto_pre  = {};
		$scope.timefrmto_vals = {}
		$scope.selected_pre   = {};
		$scope.radio_pre   = {};
		$rootScope.sub_grp_name   = {};
		$scope.flg_show = false;
		$rootScope.set_demo_flag = 0;
		//if count of total records is more than 2000,we need to display show more option for each subgroup		
        APIServices.getAttributesPage(returnState.paridInfo,DATACITY,'CS',USERID).success(function(response) {	
			//console.log("response--"+JSON.stringify(response));	 //
			$mdDialog.hide();   
			$scope.attr_name 		    = response.attr_name;    
			$rootScope.fin_res 			= response;     //console.log("fin_res---"+JSON.stringify($rootScope.fin_res));
			$rootScope.attributeData    = response.data;         
			$rootScope.existing         = response.existing;     //only selected attr's          
			$rootScope.default_time     = response.time;
			$scope.facilitynames		= response.facilitynames;
			$rootScope.sub_grp_name	    = response.sub_grp_name;
			$rootScope.name_sub_id_arr	= response.sub_id_name;
			if(response.error ==undefined || response==undefined){
				APIServices.getversion($rootScope.parentid,DATACITY).success(function(response1) {
					$rootScope.ver    =   response1.version;										
					$state.go('appHome.demopage',{parid:$rootScope.parentid,ver:$rootScope.ver,page:$rootScope.extraHandler,demo_flg:1});	
				});
			}
			else if(response.error.code == 0) {					
				$scope.restmustry = response.restmustry;
				var i=0; var x=0;
				angular.forEach(response.data,function(value2,key2){
					$scope.checkAttr[key2]    =   {};
					$scope.selectAttr[key2]   =   {};
					$rootScope.radioAttr[key2]    =   {};
					$scope.radioAttr1[key2]    =   {};
					$scope.frmAttr[key2]      =   {};
					$scope.toAttr[key2]       =   {};
					$scope.timeAttr[key2]     =   {};
					$scope.textAttr[key2]     =   {};
					$scope.chkMulAttr[key2]     =   {};
					$scope.chkMulAttr1[key2]     =   {};
					$rootScope.checkmult_model[key2] = {};
					$scope.limitArr[key2] = {};
					if(x==0 || x==1){
						$scope.flg_show = true;
						$scope.dispDiv[x] = {
							"display" : ""
						}
					}else{
						$scope.flg_show = false;
						$scope.dispDiv[x] = {
							"display" : "none"
						}
					}
					angular.forEach(value2,function(value_arr,key_arr){
						$scope.radioAttr1[key2][value_arr.uCde] = {};
						$scope.chkMulAttr1[key2][value_arr.uCde] =   {};
						$rootScope.checkmult_model[key2][value_arr.uCde] = {};
						if(value_arr.tpe=="sel"){
							if(value_arr.selval!='' && value_arr.selval!=undefined){
								$scope.selectAttr[key2][value_arr.uCde] = value_arr.selval;
								if(value_arr.selval!='' && value_arr.selval!=undefined){
										$scope.dispDiv1[value_arr.uCde] = {
										"display" : ""
									}
									$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : value_arr.selval
									}
								}else{
									$scope.selectAttr[key2][value_arr.uCde] = "";
									$scope.dispDiv1[value_arr.uCde] = {
										"display" : "none"
									}
									$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : ""
									}
								}
							}else{
								$scope.selectAttr[key2][value_arr.uCde] = "";
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : "none"
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
									"val" : ""
								}
							}
						}
						
						if (value_arr.tpe=="rad") {
							$scope.radioAttr1[key2][value_arr.uCde]['options']  = value_arr.options;
							if(value_arr.selval!='' && value_arr.selval!=undefined){									
								$rootScope.radioAttr[key2]['frm'+value_arr.uCde] = value_arr.selval;
								if(value_arr.selval.toLowerCase()=='yes'){
									$scope.radio_yes[value_arr.uCde] = {
										"display" : ""
									}
									$scope.radio_no[value_arr.uCde] = {
										"display" : "none"
									}
								}else if(value_arr.selval.toLowerCase()=='no'){
									$scope.radio_yes[value_arr.uCde] = {
										"display" : "none"
									}
									$scope.radio_no[value_arr.uCde] = {
										"display" : ""
									}
								}
								$scope.radio_pre[value_arr.uCde] = {
									"display" : "block"
								}
							}else{
								$rootScope.radioAttr[key2]['frm'+value_arr.uCde] ="";
								$scope.radio_pre[value_arr.uCde] = {
									"display" : "none"
								}
							}
						}
						if(value_arr.tpe=='mulsel'){														
							$scope.chkMulAttr1[key2][value_arr.uCde]['options']  = value_arr.options;									
							if(angular.isArray(value_arr['selval']) && value_arr['selval'].length>0){
								$scope.check_selected = '';		
								angular.forEach(value_arr.options, function(v1,k1){
									$rootScope.checkmult_model[key2][value_arr.uCde][v1] = {};											
									$rootScope.checkmult_model[key2][value_arr.uCde][v1]['val'] = v1;										
									if(angular.isArray(value_arr['selval']) && value_arr['selval'].length>0){											
										$rootScope.checkmult_model[key2][value_arr.uCde][v1]['val'] = true;										
										if($.inArray(v1,value_arr['selval']) == -1){											
											$rootScope.checkmult_model[key2][value_arr.uCde][v1]['val'] = false;		
										}else{
											if($scope.check_selected!=''){
												$scope.check_selected += ','+v1;
											}else{
												$scope.check_selected += v1;
											}
										}												
									}
									if(angular.isArray(value_arr['selval']) && value_arr['selval'].length>=1 && value_arr['selval']!=undefined && $scope.check_selected!=''){											
										$scope.dispDiv1[value_arr.uCde] = {
											"display" : ""
										}
										$scope.timefrmto_vals[value_arr.uCde] = {
											"val" : $scope.check_selected
										}
									}else{
										$scope.dispDiv1[value_arr.uCde] = {
											"display" : "none"
										}
										$scope.timefrmto_vals[value_arr.uCde] = {
											"val" : ""
										}
									}
								});
							}else{									
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : "none"
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
									"val" : ""
								}
							}								
						}
						
						if(value_arr.tpe == 'tfrmto') {
							if(value_arr.selfrm !=  "" && value_arr.selto != "") {
									$scope.frmAttr[key2]['frm'+value_arr.uCde] = value_arr.selfrm;
									$scope.toAttr[key2]['to'+value_arr.uCde] = value_arr.selto;
									$scope.timefrmto_pre[value_arr.uCde] = {
										"display" : ""
									}
									$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : value_arr.selfrm+ ' TO ' +value_arr.selto
									}
							}else{									
								$scope.frmAttr[key2]['frm'+value_arr.uCde] = "";
								$scope.toAttr[key2]['to'+value_arr.uCde]   = "";
								$scope.timefrmto_pre[value_arr.uCde] = {
										"display" : "none"
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : ""
								}
							}
						}
						if(value_arr.tpe == 'tim') {
							if(value_arr.selval !=  "") {
								$scope.timeAttr[key2][value_arr.uCde] = value_arr.selval;
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : ""
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : value_arr.selval
								}
							}else{
								$scope.timeAttr[key2][value_arr.uCde] = "";
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : "none"
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : ""
								}
							}
						}
						if(value_arr.tpe == "chk") {
							var code = value_arr.uCde
							if(value_arr.selval == 'on') {																		
								$scope.dispDiv1[code] = {
									"display" : "block"
								}
								$scope.checkAttr[key2][value_arr.uCde] = true;		
							} 
							else if(value_arr.selval == 'off'){
								$scope.checkAttr[key2][value_arr.uCde] = false;								
								$scope.dispDiv1[code] = {
									"display" : "none"
								}
							}else{
								$scope.dispDiv1[code] = {
									"display" : "none"
								}
							}																
						}
						if(value_arr.tpe=="txt"){
							
							if(value_arr.selval !=  "" && value_arr.selval != undefined) {
								$scope.textAttr[key2][value_arr.uCde] = value_arr.selval;
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : ""
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : value_arr.selval
								}
							}else{
								$scope.textAttr[key2][value_arr.uCde] = "";
								$scope.dispDiv1[value_arr.uCde] = {
									"display" : "none"
								}
								$scope.timefrmto_vals[value_arr.uCde] = {
										"val" : ""
								}
							}
						}
						i++;
						//i wil have to put inside, bcoz limit is fr each subgroup
						$scope.limitArr[key2]	=	200;
					});
					x++;
				});
				$rootScope.complete_flg = 2;
			}else if(response.error.code!=0 || response.error.code==1){
					$rootScope.set_demo_flag = 1;
					$rootScope.showCommonPop = 'multi_grp1';
					$rootScope.commonTitle = "Success";
					$rootScope.commonShowContent = response.error.msg;
			}
        });
        		
		$scope.show_hidden_divs = function(event,textId){
			if($('#'+textId).val()!=''){
				$(".tab").each(function(i,val) {
					$(this).next().show();
				});
			}
		}
		$scope.openDiv_slide = function(event,id,clsName,key){				
			console.log(id+'--id--'+clsName+'--clsName--'+key+'--key--');
			$("#heading_"+key).slideToggle();
			$("#"+id).toggleClass('actv');			
			//angular.element(document.querySelector('#landLine' + i)).attr('type', 'password');


			$('html, body').animate({
				scrollTop: $("#heading_"+key).offset().top
			}, 500);
		}
		
		$scope.setDropFocus = function(){
			//alert('1');
			console.log('blur--');
			$scope.edit = false;
		}
		
		$scope.openDiv = function(index){		
			var paginationDiv = 'openDiv_'+index;	 
			
			$('#'+paginationDiv).not(this).each(function(){				
				 $(this).slideToggle();
			});
			$(this).slideToggle();
		}
		$scope.clickMore	=	0;
		$scope.showMoreAttributes	=	function(index,length,group_id) {			
			$scope.clickMore	=	1;
			$scope.limitArr[group_id]		=	$scope.limitArr[group_id] + 200;			
		};
		
		var pagesShown = 1;
		var pageSize = 25;
		$scope.paginationLimit = function(data) {
			$scope.tot_res =  pageSize * pagesShown;
			return $scope.tot_res;
		};
		$scope.hasMoreItemsToShow = function() {
			if($scope.result_final!=undefined || $scope.result_final!=null){
				$scope.has_more = pagesShown < ($scope.result_final.length / pageSize);
				return $scope.has_more;
			}
		};
		$scope.showMoreItems = function() {
			pagesShown = pagesShown + 1;       
		};	
		
		$scope.showLessItems = function() {
			pagesShown = pagesShown - 1;       
		};
		$scope.isEmpty  =   function (obj) {
			for(var prop in obj) {
				if(obj.hasOwnProperty(prop))
					return false;
			}
            return true;
        };
		$scope.notSorted = function(obj){
			if (!obj) {
				return [];
			}
			return Object.keys(obj);
		}
		$scope.attributesPopupHandle	=	function(event) {
				$mdDialog.show({
					controller: DialogControllerAttributes,
					templateUrl: 'partials/dialogExistAttributes.html',
					parent: angular.element(document.body),
					targetEvent:event
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
		};
		 $scope.removeCrossed1 = function(value,unique_id,id,identifier,key_head){
			$("#"+unique_id).attr('checked', false);			
			$("#yes_"+unique_id).attr('checked', false);
			$("#no_"+unique_id).attr('checked', false);
			$("#no_"+unique_id).val('');
			if(identifier=='checkbox'){
				$scope.checkAttr[key_head][unique_id] = false;
			}else if(identifier=='radioButton'){
				$rootScope.radioAttr[key_head]['frm'+unique_id] = false;	
			}else if(identifier=='textArea'){
				$(".showSelected").find('#text_'+unique_id).html('-');
				$scope.textAttr[key_head][unique_id] = '';
				$("#text_"+unique_id).val('');
				$("#"+unique_id).val('');				
			}else if(identifier=='select'){
				$scope.selectAttr[key_head][unique_id] = '';
				$(".showSelected").find('#select_'+unique_id).html('-');
				$("#select_"+unique_id).val('');
				$("#sel_"+unique_id).val('Select');
			}else if(identifier=='timeFrmTo' || identifier=='simeplTime'){
				if(identifier=='timeFrmTo' && identifier!='simeplTime'){
					$(".showSelected").find('#timeMul_'+unique_id).html('');
					$scope.frmAttr[key_head]['frm'+unique_id] = ''; 
					$scope.toAttr[key_head]['to'+unique_id] = '';
					$("#timeFrm_"+unique_id).val('Select');
					$("#timeTo_"+unique_id).val('Select');
				}else{
					$(".showSelected").find('#timeSimple_'+unique_id).html('');
					$scope.timeAttr[key_head][unique_id] = '';
					$("#simeplTime_"+unique_id).val('Select');					
				}
			}else if(identifier=='multiSelect'){
				$(".showSelected").find('#multiSelect_'+unique_id).html('');				
				$("#multiSel_"+unique_id).val('Select');	
				angular.forEach($scope.chkMulAttr1[key_head][unique_id]['options'], function(v1,k1){														
					if($rootScope.checkmult_model[key_head][unique_id][v1]['val']==true){						
						$rootScope.checkmult_model[key_head][unique_id][v1]['val'] = false;
					}
				});		
			}									
			$(".showSelected").find('#exist_remove_'+unique_id).hide();
		}
        $scope.removeCrossed = function(value,unique_id,id,identifier,key_head){
			$("#"+unique_id).attr('checked', false);			
			$("#yes_"+unique_id).attr('checked', false);
			$("#no_"+unique_id).attr('checked', false);
			$("#no_"+unique_id).val('');
			if(identifier=='checkbox'){
				$scope.checkAttr[key_head][unique_id] = false;
			}else if(identifier=='radioButton'){			
				$rootScope.radioAttr[key_head]['frm'+unique_id] = false;	
							
			}else if(identifier=='textArea'){
				$(".showSelected").find('#text_'+unique_id).html('-');
				$scope.textAttr[key_head][unique_id] = '';
				$("#text_"+unique_id).val('');
				$("#"+unique_id).val('');				
			}else if(identifier=='select'){
				$scope.selectAttr[key_head][unique_id] = '';
				$(".showSelected").find('#select_'+unique_id).html('-');
				$("#select_"+unique_id).val('');
				$("#sel_"+unique_id).val('Select');
			}else if(identifier=='timeFrmTo' || identifier=='simeplTime'){
				if(identifier=='timeFrmTo' && identifier!='simeplTime'){
					$(".showSelected").find('#timeMul_'+unique_id).html('');
					$scope.frmAttr[key_head]['frm'+unique_id] = ''; 
					$scope.toAttr[key_head]['to'+unique_id] = '';
					$("#timeFrm_"+unique_id).val('Select');
					$("#timeTo_"+unique_id).val('Select');
				}else{
					$(".showSelected").find('#timeSimple_'+unique_id).html('');
					$scope.timeAttr[key_head][unique_id] = '';
					$("#simeplTime_"+unique_id).val('Select');					
				}
			}else if(identifier=='multiSelect'){
				$(".showSelected").find('#multiSelect_'+unique_id).html('');				
				$("#multiSel_"+unique_id).val('Select');	
				angular.forEach($scope.chkMulAttr1[key_head][unique_id]['options'], function(v1,k1){														
					if($rootScope.checkmult_model[key_head][unique_id][v1]['val']==true){						
						$rootScope.checkmult_model[key_head][unique_id][v1]['val'] = false;
					}
				});		
			}									
			$(".showSelected").find('#remove_'+unique_id).hide();
		}
		
		
        $scope.showSelected = function(identifier, value,id,ngModeVal,ngModeVal1,index){			
			var id_to_remove = 'remove_'+id;
			var id_to_remove_exist = 'exist_remove_'+id;
			if(identifier=='checkBox'){				
				if($('#' + id).is(":checked")){	
					if($('#'+id_to_remove_exist).is(':visible')==true){
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}
					$('.showSelected').find('#'+id_to_remove).show();
				}else{					
					$("#"+id).attr('checked', false);
					$(".showSelected").find('#'+id_to_remove).hide();
					$(".showSelected").find('#'+id_to_remove_exist).hide();
				}
			}else if(identifier=='radioButton'){				
				if(ngModeVal!=''){
					if(ngModeVal.toLowerCase()=='yes'){
						$('.showSelected').find('#no_'+id).hide();
						$('.showSelected').find('#yes_'+id).show();
						
					}else{
						$('.showSelected').find('#yes_'+id).hide();
						$('.showSelected').find('#no_'+id).show();
					}
					if($('#'+id_to_remove_exist).is(':visible')==true){
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}
					$('.showSelected').find('#'+id_to_remove).show();
				}
			}else if(identifier=='textArea'){
				var text = $('#'+id).val();
				if(text!=''){
					var html = text;
					$('.showSelected').find('#text_'+id).html(html);					
					$('.showSelected').find('#text_'+id).show();
					$('.showSelected').find('#'+id_to_remove).show();
					if($('#'+id_to_remove_exist).is(':visible')==true){
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}
				}else{
					$('.showSelected').find('#text_'+id).html('-');
					$('.showSelected').find('#text_'+id).hide();
					$('.showSelected').find('#'+id_to_remove).hide();
					$(".showSelected").find('#'+id_to_remove_exist).hide();
				}
			}else if(identifier=='select'){				
				var selectOpt = $('#sel_'+id).val();
				var selected = ngModeVal[selectOpt];
				
				if(selected!='' && selected.toLowerCase()!='select'){
					$('.showSelected').find('#select_'+id).html('');
					$('.showSelected').find('#select_'+id).html('-');
					$('.showSelected').find('#select_'+id).append(selected);					
					$('.showSelected').find('#select_'+id).show();															
					$('.showSelected').find('#'+id_to_remove).show();
					if($('#'+id_to_remove_exist).is(':visible')==true){
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}
				}else{
					$('.showSelected').find('#select_'+id).html('-');
					$('.showSelected').find('#select_'+id).hide();
					$('.showSelected').find('#'+id_to_remove).hide();
					$(".showSelected").find('#'+id_to_remove_exist).hide();
				}				
			}else if(identifier=='timefromto' || identifier=='simeplTime'){
				if(identifier=='timefromto'){
					
					var timeFrm = $('#timeFrm_'+id).val();
					var timeTo  = $('#timeTo_'+id).val();
					var frmTime = ngModeVal[timeFrm];
					var toTime = ngModeVal[timeTo];
					if(frmTime!='' && frmTime.toLowerCase()!='select' && toTime!='' && toTime.toLowerCase()!='select'){
						$('.showSelected').find('#timeMul_'+id).html('');
						$('.showSelected').find('#timeMul_'+id).append(frmTime +' TO '+toTime);		
						$('.showSelected').find('#timeMul_'+id).show();	
						$('.showSelected').find('#'+id_to_remove).show();
						if($('#'+id_to_remove_exist).is(':visible')==true){
							$(".showSelected").find('#'+id_to_remove_exist).hide();
						}
					}
				}else{
					var simpleTime = $('#simeplTime_'+id).val();
					var frmTime = ngModeVal[timeFrm];
					if(frmTime!='' && frmTime.toLowerCase()!='select'){
						$('.showSelected').find('#timeSimple_'+id).html('');
						$('.showSelected').find('#timeSimple_'+id).append(frmTime);		
						$('.showSelected').find('#timeSimple_'+id).show();	
						$('.showSelected').find('#'+id_to_remove).show();
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}					
				}				
			}else if(identifier=='multiDropdown'){
				var key = ngModeVal;
				var value = ngModeVal1;
				//if($('#multiSel_'+key).is(":checked")){
				if($('#multiSel_'+index+'_'+id).is(":checked")){
					if( $('#multiSelect_'+id).text()!=''){
						var existing = $('#multiSelect_'+id).text();
						var html = existing+","+value;						
					}else{
						var html = value;
					}
					html = html.trim();
					var uniqueNames = [];
					var text_str = html.split(',');
					$.each(text_str, function(i, el){
						if($.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
					});	
					var join = uniqueNames.join();
					$('.showSelected').find('#multiSelect_'+id).html(join);		
					$('.showSelected').find('#multiSelect_'+id).show();	
					$('.showSelected').find('#'+id_to_remove).show();
					if($('#'+id_to_remove_exist).is(':visible')==true){
						$(".showSelected").find('#'+id_to_remove_exist).hide();
					}
					
				}else{
					if($('#multiSelect_'+id).text()!=''){
						var text = $('#multiSelect_'+id).text();
						var text_str = text.split(',');			
						//for( var i = text_str.length; i--;){							
						for( var i = 0; i < text_str.length; i++){	
							if ( text_str[i].toLowerCase().trim() == value.toLowerCase().trim()){
								text_str.splice(i--, 1);//array.splice(i--,1);
							}							 
						}
						var uniqueNames = [];
						$.each(text_str, function(i, el){
							if($.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
						});										
						var join = uniqueNames.join();
						$('.showSelected').find('#multiSelect_'+id).html(join);								
						$('.showSelected').find('#multiSelect_'+id).show();	
						$('.showSelected').find('#'+id_to_remove).show();
						if($('#'+id_to_remove_exist).is(':visible')==true){
							$(".showSelected").find('#'+id_to_remove_exist).hide();
						}						
					}
					
				}				
				
			}			
			
		}	
		$rootScope.submitAttributes =   function(event) {			
			if($rootScope.fin_res.error.code == 0){
				$rootScope.validateData 	= [];
				$scope.attrSubmitData   =   {};
				$rootScope.attrHtmlData =   {};
				$scope.data_city     =   DATACITY;
				$rootScope.attrTaken = 0;
				$scope.invalidsel    = 0;				
				angular.forEach($rootScope.attributeData,function(value_arr,srvname_arr){					
					$scope.attrSubmitData[srvname_arr]  =   {};
                    $scope.unique_code_str[srvname_arr]  =   {};
                    //$scope.checkmult_model[srvname_arr] = {};
					angular.forEach(value_arr,function(value,srvname){
						//$scope.checkmult_model[srvname_arr][value.uCde] = {};
						$rootScope.attrHtmlData[value.uCde]  = {};
						$rootScope.attrHtmlData[value.uCde]  = {};
						
						if(value.tpe=="chk"){
							if($scope.checkAttr[srvname_arr][value.uCde]){
								if($rootScope.validateData=='' || $rootScope.validateData==null){
									$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'1')";
								}else{
									$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'1')";
								}
								$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~";
                                $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
								$rootScope.attrTaken = 1;
							}
						}
						if(value.tpe == "sel") {
							if($scope.selectAttr[srvname_arr][value.uCde]){
								if($rootScope.validateData=='' || $rootScope.validateData==null){
									$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$scope.selectAttr[srvname_arr][value.uCde]+"')";
								}else{
									$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$scope.selectAttr[srvname_arr][value.uCde]+"')";
								}
								$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$scope.selectAttr[srvname_arr][value.uCde];
                                $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
								$rootScope.attrTaken = 1;
							}
						}
						if(value.tpe=='mulsel'){
							
							if($scope.chkMulAttr1[srvname_arr][value.uCde]['options'].length > 0){
								$scope.attr_name = '';$scope.attr_val = '';								
								angular.forEach($scope.chkMulAttr1[srvname_arr][value.uCde]['options'], function(v1,k1){	
									//$scope.checkmult_model[srvname_arr][value.uCde][v1] = {};								
									$scope.attr_name = value.aNm+"~~~";
									if($rootScope.checkmult_model[srvname_arr][value.uCde][v1]['val']==true){
										$scope.attr_val += v1+',';										
									}
								});
								if($scope.attr_val!=''){
									if($rootScope.validateData=='' || $rootScope.validateData==null){
										$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$scope.attr_val+"')";
									}else{
										$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$scope.attr_val+"')";
									}
									$scope.attrSubmitData[srvname_arr][value.uCde] =value.aNm+"~~~"+$scope.attr_val;
									$scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
									$rootScope.attrTaken = 1;
								}
							}
							
							
						}
						if(value.tpe == "txt") {
							if($scope.textAttr[srvname_arr][value.uCde]){
								if($rootScope.validateData=='' || $rootScope.validateData==null){
									$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$scope.textAttr[srvname_arr][value.uCde]+"')";
								}else{
									$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$scope.textAttr[srvname_arr][value.uCde]+"')";
								}
								$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$scope.textAttr[srvname_arr][value.uCde];
                                $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
								$rootScope.attrTaken = 1;
							}
						}
						if (value.tpe=="rad") {							
							if($rootScope.radioAttr[srvname_arr]['frm'+value.uCde]!=''){
								if($rootScope.validateData=='' || $rootScope.validateData==null){
									$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$rootScope.radioAttr[srvname_arr]['frm'+value.uCde].toLowerCase()+"')";
								}else{
									$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$rootScope.radioAttr[srvname_arr]['frm'+value.uCde].toLowerCase()+"')";
								}
								$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$rootScope.radioAttr[srvname_arr]['frm'+value.uCde];
								$rootScope.attrTaken = 1;  
							}
							if($rootScope.radioAttr[srvname_arr]['frm'+value.uCde]!='' && $rootScope.radioAttr[srvname_arr]['frm'+value.uCde]!=undefined){
								if ($rootScope.radioAttr[srvname_arr]['frm'+value.uCde].toLowerCase()=='yes') {
									$scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
								}
							}                         
						}					
						if(value.tpe=="tfrmto"){
							 if($scope.frmAttr[srvname_arr]["frm"+value.uCde]){
								if($scope.isEmpty($scope.toAttr[srvname_arr]["to"+value.uCde]) == true) {
									delete $scope.toAttr[srvname_arr]["to"+value.uCde];
								}
								if(typeof $scope.toAttr[srvname_arr]["to"+value.uCde] !== 'undefined'){
									$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$scope.frmAttr[srvname_arr]["frm"+value.uCde]+" TO "+$scope.toAttr[srvname_arr]["to"+value.unique_coded];
                                    $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
									$rootScope.attrTaken = 1;
								}else{
									$scope.invalidsel = 1;
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Alert";
									$rootScope.commonShowContent = 'Kindly select To Timing for '+value.aNm;
								
									return false;
								}
							}
							if($scope.toAttr[srvname_arr]["to"+value.uCde])
							{
								if($scope.isEmpty($scope.frmAttr[srvname_arr]["frm"+value.uCde]) == true) {
									delete $scope.frmAttr[srvname_arr]["frm"+value.uCde];
								}
								if(typeof $scope.frmAttr[srvname_arr]["frm"+value.uCde] !== 'undefined'){
									$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$scope.frmAttr[srvname_arr]["frm"+value.uCde]+" TO "+$scope.toAttr[srvname_arr]["to"+value.uCde];
                                    $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
                                    if($rootScope.validateData=='' || $rootScope.validateData==null){
										$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$scope.frmAttr[srvname_arr]["frm"+value.uCde]+" TO "+$scope.toAttr[srvname_arr]["to"+value.uCde]+"')";
									}else{
										$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$scope.frmAttr[srvname_arr]["frm"+value.uCde]+" TO "+$scope.toAttr[srvname_arr]["to"+value.uCde]+"')";
									}
									$rootScope.attrTaken = 1;
								}else{
									$scope.invalidsel = 1;
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Alert";
									$rootScope.commonShowContent = 'Kindly select From Timing for '+value.aNm;
									
									return false;
								}
							}
							
							var start_time = $scope.frmAttr[srvname_arr]["frm"+value.uCde];
							var end_time = $scope.toAttr[srvname_arr]["to"+value.uCde];							
							if(start_time!='' && start_time!=undefined && end_time!='' && end_time!=undefined){								
								var stt = new Date("November 13, 2013 " + start_time);
								stt = stt.getTime();
								var endt = new Date("November 13, 2013 " + end_time);
								endt = endt.getTime();	
								if(stt == endt){
									$scope.invalidsel = 1;
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Alert";
									$rootScope.commonShowContent = 'Kindly Select Different start and end timing for '+value.aNm
									
									return false;
								}						
								if(stt > endt) {									
									$scope.invalidsel = 1;
									$rootScope.showCommonPop = 1;
									$rootScope.commonTitle = "Alert";
									$rootScope.commonShowContent = 'Kindly Select Proper Timing for '+value.aNm;
									
									return false;
								}
							}
						}
						if(value.tpe=="tim"){
							if($scope.timeAttr[srvname_arr][value.uCde]){
								if($rootScope.validateData=='' || $rootScope.validateData==null){
									$rootScope.validateData += "('"+value.uCde+"',"+value.attr_grp+",'"+$scope.timeAttr[srvname_arr][value.uCde]+"')";
								}else{
									$rootScope.validateData += ",('"+value.uCde+"',"+value.attr_grp+",'"+$scope.timeAttr[srvname_arr][value.uCde]+"')";
								}
								$scope.attrSubmitData[srvname_arr][value.uCde] = value.aNm+"~~~"+$scope.timeAttr[srvname_arr][value.uCde];
                                $scope.unique_code_str[srvname_arr][value.uCde] = value.uCde;
								$rootScope.attrTaken = 1;
							}
						}
					});
				});
			
			    //console.log("=unique_code_str="+JSON.stringify($scope.unique_code_str));
			    //console.log($rootScope.parentid+"=="+$scope.data_city+"=arrTaken="+$rootScope.attrTaken+"=attSubm="+JSON.stringify($scope.attrSubmitData));
				//return false;
				
				if($scope.invalidsel !=1){
					
					
					if($rootScope.attrTaken == 1){
						$rootScope.attrSubmitData = $scope.attrSubmitData;
						$rootScope.unique_code_str = $scope.unique_code_str;
						$rootScope.showCommonPop = 'attr_condition';
						$rootScope.commonTitle = "Reminder Alert";					
						$rootScope.commonShowContent = "Collecting all relevant attributes associated to business is absolutely mandatory else heavy penalty will be levied. By clicking OK, you undertake responsibility that no attributes are missed";
					}else{
						$rootScope.showCommonPop = '1';
						$rootScope.commonTitle = "Error !!!";
						$rootScope.commonShowContent = "No Attributes Selected";
					}
				}
			}
			else{
				//window.location = '../00_Payment_Rework/04_payment_mode_selection.php';
				$mdDialog.hide();
				$state.go('appHome.catpreview',{parid:$rootScope.parentid,page:''});
			}
        };
        
         $rootScope.submit_attrs = function(){	
			$rootScope.showCommonPop = 0;					
//			console.log("before updateAttributes:----"+JSON.stringify($rootScope.validateData));
			APIServices.updateAttributes($rootScope.parentid,$scope.data_city,$rootScope.attrTaken,$rootScope.attrSubmitData,$rootScope.unique_code_str,$rootScope.validateData).success(function(response) {
				$rootScope.data_err = [];
				$rootScope.attr_result = {};
				if(response.error.code == 0) {
					$rootScope.set_demo_flag = 1;
					$scope.proceed_to_demopage();
				}else if(response.error.code == 2){	
					$rootScope.set_demo_flag = 1;			
					$rootScope.showCommonPop = '1';
					$rootScope.commonTitle = "Alert";
					$rootScope.commonShowContent = response.error.msg;
				}else if(response.error.code ==3){
					$rootScope.data_err = response.data;
					angular.forEach($rootScope.data_err, function(value, key) {
						angular.forEach(value, function(value2, key2){
							$rootScope.attr_result[key2] = value2;
						});						
					});					
					$rootScope.showCommonPop = "attr_invalid";
					$rootScope.commonTitle = "genio.in";
					$rootScope.commonShowContent = response.error.msg;
				}else {
					$mdToast.show(
						$mdToast.simple()
						.content('Data Cannot be submitted. Please try again later')
						.position('bottom right')
						.hideDelay(4000)
						);
				}
			});
		}
        
		$scope.notAuthMustTryEdit = function(event) {
			$timeout(function() {				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Alert";
				$rootScope.commonShowContent = "You dont have access to edit Recommended Dishes / Must Try for Restaurant";
			});
		}
		$rootScope.proceed_to_demopage = function(){
			console.log('proceed_to_demopage--'+$rootScope.set_demo_flag)					
			if($rootScope.set_demo_flag!=undefined && $rootScope.set_demo_flag==1){				
				$rootScope.showCommonPop = 0;
				APIServices.getversion($rootScope.parentid,DATACITY).success(function(response) {
					$rootScope.ver    =   response.version;										
					//$state.go('appHome.demopage',{parid:$rootScope.parentid,ver:$rootScope.ver,page:$rootScope.extraHandler,demo_flg:1});				
					APIServices.docVerticalCheck($rootScope.parentid).success(function(respdoc) {
						if(respdoc.error.code	==	0){
							if(respdoc.data == 1) {
								window.location	=	'../business/docs_hosp_list.php?ver='+$rootScope.ver+'&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
							} else {
								window.location	=	'../business/bform_doctor.php?ver='+$rootScope.ver+'&flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
							}
						}else{							
							$state.go('appHome.demopage',{parid:$rootScope.parentid,ver:$rootScope.ver,page:$rootScope.extraHandler,demo_flg:1});				
						}
					});	
				});
			}else{
			    $scope.urlToRedirect   =  "../newTme/catpreview/"+$rootScope.parentid+"/";
				window.location =   $scope.urlToRedirect;			
			}		
			
		}
		function DialogControllerAttributes($scope, $mdDialog,$mdToast) {
			$scope.hide = function() {
				$mdDialog.hide();
			};
			$scope.parentid				=   $rootScope.parentid;
			$scope.compname				=	$rootScope.companyTempInfo.data.companyname;
			$scope.attrTaken			=	$rootScope.attrTaken;
			$scope.attrHtmlData			=	$rootScope.attrHtmlData;
		}
		
	});
});
