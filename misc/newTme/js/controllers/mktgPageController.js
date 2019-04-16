/************************** Grabbed Notification Code *************************************/
var socket = io.connect('http://103.20.126.28:8889');
/************************** Grabbed Notification Code *************************************/
define(['./module'], function (tmeModuleApp) {
	//The controller used for Employee Information
	tmeModuleApp.controller('employeeController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdSidenav,$mdUtil,$mdBottomSheet,$mdDialog) {
		$rootScope.layout = ''; // new Code Added for handling new Design
		  //  jq$ = jQuery.noConflict();
		//jq$("[id*='abc']").hide();
		$scope.returnRes = [];
		$rootScope.employees = null;
		$scope.add_Visible = true;
		$scope.cityCode = cityCode;
		$rootScope.superHotReadcount	=	0;
		$rootScope.showForwardAhd		=	0;
		//Freebees Approval Access Employees
		APIServices.fetchFreebeesEmp().success(function (response) {
			$scope.resellerHods		=	response.data;
		});
		$rootScope.EmpCity = '';
		//Freebees Approval Access Employees
		APIServices.getEmployees($scope.id).success(function (response) {
			$rootScope.employees = response.data;
			if($rootScope.employees.remoteAddr == '172.29.87.77'){
				$rootScope.employees.results.allocId = 'SJ';
			}
			$rootScope.EmpCity = $rootScope.employees.hrInfo.data.city;
			$scope.moduleType	=	response.data.module;
			APIServices.getMenuLinks(response.data.results.allocId,response.data.results.secondary_allocID).success(function (response) {
				$rootScope.linksData	=	[];
				$rootScope.linksReportIn	=	[];
				$rootScope.linksSortInvent	=	[];
				angular.forEach(response.data.menu.data,function(value,key) {
					if(value != null) {	
						if(value.display_menu	==	1 || value.display_menu	==	4 || value.display_menu	==	7 || value.display_menu	==	8) {	
							if(value.menu_name	==	'Inventory less than 50' || value.menu_name	==	'Inventory greater than 50'){
								$rootScope.linksSortInvent.push(value);
							}else{
								$rootScope.linksData.push(value);
							}
						} else {
							$rootScope.linksReportIn.push(value);
						}
					}
				});
				if(response.data.disposition.errorCode == 0) {
					$rootScope.disposelist =	response.data.disposition.data; 
				}
			});
			APIServices.getEcsEmpcode(response.data.results.mktEmpCode).success(function(response){
				$scope.getEcsEmpcode = response;
			});
			/// Ahd lineage check
			if(DATACITY.toLowerCase() == 'delhi' || $rootScope.EmpCity.toLowerCase() =='jaipur' || $rootScope.EmpCity.toLowerCase() == 'chandigarh' || $rootScope.EmpCity.toLowerCase() == 'kolkata' || $rootScope.EmpCity.toLowerCase() == 'mumbai' || $rootScope.EmpCity.toLowerCase() == 'pune' || $rootScope.EmpCity.toLowerCase() == 'ahmedabad'){
				APIServices.getAhdLineage().success(function(response){
					if(response.errorcode == 0){
						$rootScope.showForwardAhd = 0;
					}else{
						$rootScope.showForwardAhd = 1;
					}
				});
			}
			/// Ahd lineage check
		});
		
			
		$scope.show_waitDetails = function(ev,flag){
			$rootScope.client_flag = flag;
			if(flag == 1){
			$mdDialog.show({
							controller: clientVisit_dataController,
							templateUrl: 'partials/clientVisit_data.html',
							parent: angular.element(document.body),
							targetEvent: ev,
							clickOutsideToClose:false
						});
			}else{
				$mdDialog.show({
							controller: clientVisit_dataController,
							templateUrl: 'partials/clientVisit_data.html',
							parent: angular.element(document.body),
							targetEvent: ev,
							clickOutsideToClose:false
						});
				//~ alert("Kindly go back to Allocation Page to View the Contracts and Meet the Client !!");
				//~ return false;
			}
		}
		
		function clientVisit_dataController($scope, $mdDialog, $rootScope,APIServices) {
			$scope.client_data = [];
			$scope.client_data = $rootScope.employees.client_waiting.data;
			$scope.clent_show_flag = $rootScope.client_flag;
				
			$scope.ecs_mdDialog_hide = function() {
			    $mdDialog.hide();
			};
			
			$scope.ClientBform_Redirect	=	function(parentid){
				APIServices.getTempStatus(parentid).success(function(response){
					window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+parentid+"&flgSrc=2&flgAfterCall=0&hotData=1&flgPaid=1";
				});
			};
		}
		
		$rootScope.autoDialer = new Array;
		for(var $ext=6000;$ext<7000;$ext++){	
			$rootScope.autoDialer.push($ext);
		}
		
		$scope.showImgSpeedLink	=	function(){
			$scope.hideFavourite =! $scope.hideFavourite; 
			$scope.hidereport = false;
			$scope.hidelogout = false;
			$scope.hidefinance = false;
		}
		
		$scope.showImgReportMenu	=	function(){
			$scope.hidereport = ! $scope.hidereport; 
			$scope.hideFavourite = false;
			$scope.hidelogout = false;
			$scope.hidefinance = false;
		    $('#Menuscroll').animate({scrollTop:($('.repMenu').offset().top-70)},'slow');
		}

		$scope.showImgFinanceMenu	=	function(){
			$scope.hidefinance = ! $scope.hidefinance; 
			$scope.hideFavourite = false;
			$scope.hidelogout = false;
			$scope.hidereport = false;
			$('#Menuscroll').animate({scrollTop:($('.finMenu').offset().top-50)},'slow');
		}
		
		$scope.showLogoutpopup = function() {
			$scope.hidelogout = ! $scope.hidelogout; 
			$scope.hidereport = false; 
			$scope.hideFavourite=false;
			$scope.hidefinance = false;
		}
		
		/*APIServices.getModuleType().success(function(response) {
			$scope.moduleType	=	response[0];
		});*/
		
		$scope.decsURL	=	"";
		var windowLoc	=	window.location.host;
		var splwindowLoc	=	windowLoc.split(".");
		if(splwindowLoc[2] == '17') {
			$scope.decsURL	=	"192.168.17.217:81";
			if(splwindowLoc[3] == '237:197' || splwindowLoc[3] == '237:19700' )
			{
				$scope.tmeURL	=	'192.168.17.237:197';
			}else if(splwindowLoc[3] == '227:197' || splwindowLoc[3] == '227:19700'){
				$scope.tmeURL	=	'192.168.17.227:197';
			}else {
				$scope.tmeURL	=	"192.168.17.217:197";
			}
		} else if(splwindowLoc[1] == 'jdsoftware'){
			$scope.decsURL	=	windowLoc+'/csgenio';
			$scope.tmeURL	=	windowLoc+'/tmegenio';
		} else {
			$scope.decsURL	=	"172.29."+splwindowLoc[2]+".217:81";
			$scope.tmeURL	=	"172.29."+splwindowLoc[2]+".237:97";
		}
		
		
		//Method used for Handling Event Pull
		var callEventPull	=	function() {
			$http.get('../tmAlloc/eventpull.php?read=true&ucode='+USERID,{ignoreLoadingBar: true}).success(function(response){
				$rootScope.spinner	=	false;
				var rep = eval(response);
				if(rep[0] == 'screenpop' || rep[0] == 'preview') {
					var loc	=	'';
					var reseller =  rep[1].split('-');
					
					//alert(reseller[1]);
					
					if(reseller[1]=='RS') {
						loc = "../tmAlloc/mktgGetResellerData.php?parentid="+reseller[0]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1";
						top.frame2.location.href = loc; 
					} else if(reseller[1]=='OTH') {
						loc = "../business/bform_intermediate.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
						top.frame2.location.href = loc; 
					} else if(reseller[1]=='inbound') {
						loc = "../business/bfrom_inbound.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
						top.frame2.location.href = loc; 
					} else if(reseller[1]=='jdrr') {
						loc = "../business/bform_jdrr.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
						top.frame2.location.href = loc; 
					} else if(reseller[1]=='newstrategy') {
						loc = "../business/bform_showcategories.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
						top.frame2.location.href = loc; 
					} else if(reseller[1]=='ECS') {
						APIServices.ecsTransfer(reseller[1],reseller[0]).success(function(response) { 
							if(response.errorCode	==	0){
								if(response.data.parentid !='')
								{
									loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+response.data.parentid+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1&ecs_flag="+reseller[1]+"&extn="+reseller[0];
									top.frame2.location.href = loc; 
								}else
								{
									alert('This is new Contract For '+response.opt_name.opt_name);
								}
							}
						});


					}else if(reseller[1]=='web_dialer'){
						loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+reseller[0]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1&web_dialer=1";    
						top.frame2.location.href = loc; 
					}else if(reseller[1]=='IROAPP')
					{
						//alert(reseller[1]);
						
						APIServices.iroAppTransfer(reseller[1],reseller[0]).success(function(response) { 
							$scope.irodata = {};
							//alert('Transfer Call :'+response.errorCode);
							if(response.errorCode	==	0){
								if(response.data[0].Parentid !='' || response.data[0].type ==1)
								{
									$scope.irodata = response.data[0];
									$scope.popTransfer = 1;
									$scope.pincodelist		 		= [];
									
									
									$("#area").keyup(function(event) {
										if ($(this).val() == '' ) {
												console.log($(this).val());
												APIServices.getPincodeInfo().success(function(data) {
													if(data.error.code       ==      0) {
														$scope.pincodelist.length=0;
														$.each(data.data,function(i,val) {
															$scope.pincodelist.push(i);
														});
													}
													
												});
												
											}
									});
									
									if(response.data.pincode == '' || response.data.pincode ==null)
									{
										APIServices.getPincodeInfo().success(function(data) {
											if(data.error.code       ==      0) {
												$scope.pincodelist.length=0;
												$.each(data.data,function(i,val) {
													$scope.pincodelist.push(i);
												});
											}
										});	
									}else
									{
										$("#area").keyup(function(event) {
											if ($(this).val() == '' ) {
												console.log($(this).val());
												APIServices.getPincodeInfo().success(function(data) {
													if(data.error.code       ==      0) {
														$scope.pincodelist.length=0;
														$.each(data.data,function(i,val) {
															$scope.pincodelist.push(i);
														});
													}
													
												});
												
											}
											
										});
										
										
										
										$scope.pincode		 		= response.data.pincode;
										$scope.pincodelist.push($scope.pincode);
									}
										
										
										$scope.parentid 			= response.data[0].Parentid;
										$scope.data_city 			= response.data[0].City;
										$scope.paidFlag 			= response.data.paid;
										$scope.saveAsnonPaid 		= response.data.saveas_nonpaid;
										
										
										$scope.area 				= response.data.area;
										$scope.source 				= response.data.source;
									
									
									
									/*$scope.parentid 			= response.data[0].Parentid;
									$scope.data_city 			= response.data[0].City;
									$scope.paidFlag 			= response.data.paid;
									$scope.saveAsnonPaid 		= response.data.saveas_nonpaid;
									$scope.pincode		 		= response.data.pincode;
									$scope.pincodelist.push($scope.pincode);
									$scope.area 				= response.data.area;
									$scope.source 				= response.data.source;
									*/
									
									$('.iroAppoverlay').show();
									$('.popTopBarBroad').show();
								}else
								{
									$scope.popTransfer = 0;
									$('.iroAppoverlay').hide();
								}
							}else
							{
									$('.iroAppoverlay').hide();
									$('.popTopBarBroad').hide();
							}
						});
						
					} else {
						loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+rep[1]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1";
						top.frame2.location.href = loc; 
					}
	
					//top.frame2.location.href = loc; 
				}
				var timer	=	$timeout(callEventPull, 2000);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
		};
		
		/*******************start call on click event pull for drop online case*********************/
		
		var callEventPull_transfer	=	function(cliextn) {
			
			$http.get('../tmAlloc/eventpull.php?event=screenpop&agtid='+USERID+'&contractid='+cliextn+'-IROAPP'+'&callid='+STATID,{ignoreLoadingBar: true}).success(function(response){
			
				console.log('event pull call on submit',USERID);
				$http.get('../tmAlloc/eventpull.php?read=true&ucode='+USERID,{ignoreLoadingBar: true}).success(function(response){
					$rootScope.spinner	=	false;
					var rep = eval(response);
					
					if(rep[0] == 'screenpop' || rep[0] == 'preview') {
						var loc	=	'';
						var reseller =  rep[1].split('-');
						
						if(reseller[1]=='IROAPP')
						{
							//alert(reseller[1]);
							
							APIServices.iroAppTransfer(reseller[1],reseller[0]).success(function(response) { 
								$scope.irodata = {};
								alert('Transfer Call :'+response.errorCode);
								if(response.errorCode	==	0){
									if(response.data[0].Parentid !='')
									{
										$scope.irodata = response.data[0];
										$scope.popTransfer = 1;
										
										$scope.paidFlag 			= response.data.paid;
										$scope.saveAsnonPaid 		= response.data.saveas_nonpaid;
										
										$('.iroAppoverlay').show();
										$('.popTopBarBroad').show();
									}else
									{
										$scope.popTransfer = 0;
										$('.iroAppoverlay').hide();
									}
								}else
								{
										$('.iroAppoverlay').hide();
										$('.popTopBarBroad').hide();
								}
							});
							
						} 
		
						//top.frame2.location.href = loc; 
					}
					
				});
			});
		};
		
		
		$scope.openTransferPop	=	function(ev){
			$rootScope.extension_new = [];
				$mdDialog.show({
					controller: onlineTransferDropCntrl,
					templateUrl: 'partials/onlinetransfer.html',
					parent: angular.element(document.body),
					clickOutsideToClose: false,
					escapeToClose: false
				});
			
		}
		
		function onlineTransferDropCntrl($scope,$rootScope){
			$scope.extension_new = $rootScope.extension_new;
			$scope.closetransfer = function(ev){
				console.log('sdfsadf',$scope.extension_new);
				if($scope.extension_new == ''){
					alert('Please Enter Extension');
					return false;
				}else{
					callEventPull_transfer($scope.extension_new);
					$mdDialog.hide();
				}
				//$scope.showtrnsdiv = 	1;
			};
		};
		
		/***********************************End****************************************************/
		
		
		$scope.toggleLeft = buildToggler('left');
		$scope.toggleRight = buildToggler('right');
		
		function buildToggler(navID) {
			var debounceFn =  $mdUtil.debounce(function(){
				$mdSidenav(navID)
					.toggle()
					.then(function () {
						
					});
				},300);
			return debounceFn;
		}
		
		// IF TECHINFO IS CALLED
		if(STATID && $.inArray(parseInt(STATID),$scope.autoDialer) !== -1) {
			if(ALLOWNEWEVENTPULL == 1) {
				var connectionCall = new autobahn.Connection({transports: [{'type': 'websocket','url': 'ws://192.168.55.105:8086/ws'}],realm: "tmecallconnect"});
				if(!connectionCall.isConnected) {
					connectionCall.onopen = function (session, details) {
						console.log('Connection to callconnect crossbar');
						session.subscribe('com.tme.callconnect'+USERID, function(info) {
							var reseller =  info[0]['parentid'].split('-');
							if(reseller[1]=='RS') {
								loc = "../tmAlloc/mktgGetResellerData.php?parentid="+reseller[0]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1";
								top.frame2.location.href = loc;
							} else if(reseller[1]=='OTH') {
								loc = "../business/bform_intermediate.php?id="+info[0]['parentid']+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
								top.frame2.location.href = loc;
							} else if(reseller[1]=='inbound') {
								loc = "../business/bfrom_inbound.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
								top.frame2.location.href = loc;
							} else if(reseller[1]=='jdrr') {
								loc = "../business/bform_jdrr.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
								top.frame2.location.href = loc;
							} else if(reseller[1]=='newstrategy') {
								loc = "../business/bform_showcategories.php?id="+reseller[0]+"&data_city="+DATACITY+"&ucode="+USERID+"&uname="+UNAME;
								top.frame2.location.href = loc; 
							} else if(reseller[1]=='ECS') {
								APIServices.ecsTransfer(reseller[1],reseller[0]).success(function(response) {
									if(response.errorCode	==	0){
										if(response.data.parentid !='')
										{
											loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+response.data.parentid+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1&ecs_flag="+reseller[1]+"&extn="+reseller[0];
											top.frame2.location.href = loc;
										}else
										{
											alert('This is new Contract For '+response.opt_name.opt_name);
										}
									}
								});


							}else if(reseller[1]=='web_dialer'){
								loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+reseller[0]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1&web_dialer=1";    
								top.frame2.location.href = loc; 
							}else if(reseller[1]=='IROAPP'){
								APIServices.iroAppTransfer(reseller[1],reseller[0]).success(function(response) {
									$scope.irodata = {};

									if(response.errorCode	==	0){
										if(response.data[0].Parentid !='')
										{
											$scope.irodata = response.data[0];
											$scope.popTransfer = 1;

											$scope.paidFlag 			= response.data.paid;
											$scope.saveAsnonPaid 		= response.data.saveas_nonpaid;

											$('.iroAppoverlay').show();
											$('.popTopBarBroad').show();
										}else
										{
											$scope.popTransfer = 0;
											$('.iroAppoverlay').hide();
										}
									}else
									{
											$('.iroAppoverlay').hide();
											$('.popTopBarBroad').hide();
									}
								});

							} else {
								loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+reseller[0]+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1";
								top.frame2.location.href = loc;
							}
						});
					};
					connectionCall.open();
					
					connectionCall.onclose = function (reason, details) {
						console.log("Connection lost CALL DISPOSITION : " + reason);
					}
				}
			} else {
				var timer	=	$timeout(callEventPull, 2000);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			}
		}
		$scope.showLoaderMenu	=	0;
		$scope.openMenuSP	=	function() {
			if($('.showSPList').hasClass('none')) {
				$scope.showLoaderMenu	=	1;
				APIServices.findSearchPlusFlag($rootScope.parentid,DATACITY).success(function(response){
					$scope.showLoaderMenu	=	0;
					$scope.searchPlusCampaigns	=	response;
					$scope.redirectUrlSP	=	{};
					if($scope.searchPlusCampaigns.error.code == 0) {
						angular.forEach($scope.searchPlusCampaigns.data,function(value,key) {
							switch(value.vertical_id) {
								case '1':
									$scope.redirectUrlSP[value.vertical_id] = '../business/restaurant_bform.php?f_R_W='+value.type_flag;
								break;
								case '2':
									$scope.redirectUrlSP[value.vertical_id] = '../business/restaurant_bform.php?f_R_W='+value.type_flag;
								break;
								case '3':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_shopfront_bform.php?parentid='+$rootScope.parentid;
								break;
								case '4':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_service_bform.php?parentid='+$rootScope.parentid;
								break;
								case '5':
									$scope.redirectUrlSP[value.vertical_id] = '../business/pharmacy_bform.php?parentid='+$rootScope.parentid;
								break;
								case '6':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_acservice_bform.php?parentid='+$rootScope.parentid;
								break;
								case '7':
									$scope.redirectUrlSP[value.vertical_id] = '../business/grocery_bform.php?parentid='+$rootScope.parentid;
								break;
							   
								case '8':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_testdriveservice_bform.php?parentid='+$rootScope.parentid;
								break;
							   
								case '9':
									$scope.redirectUrlSP[value.vertical_id] = '../business/laundry_bform.php?parentid='+$rootScope.parentid;
								break;
								case '10':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_wpservice_bform.php?parentid='+$rootScope.parentid;
								break;
								case '13':
									$scope.redirectUrlSP[value.vertical_id] = '../business/bform_lab.php?parentid='+$rootScope.parentid;
								break;
								case '14':
								case '15':
									$scope.redirectUrlSP[value.vertical_id] = '../business/restaurant_bform.php?f_R_W='+value.type_flag;
								break;
								case '16':
									$scope.redirectUrlSP[value.vertical_id] = '../business/book_mineralwater_bform.php?parentid='+$rootScope.parentid;
								break;   
								case '17':
									$scope.redirectUrlSP[value.vertical_id] = '../business/bform.courier.php?parentid='+$rootScope.parentid;
								break;
								case '18':
									$scope.redirectUrlSP[value.vertical_id] = '../business/hotel_bform.php?parentid='+$rootScope.parentid;
								break;
								case '19':
									$scope.redirectUrlSP[value.vertical_id] = '../business/bform_spa_salon.php?parentid='+$rootScope.parentid;
								break;
								case '21':
									$scope.redirectUrlSP[value.vertical_id] = '../business/bform.php?parentid='+$rootScope.parentid;
								break;
							}
						});
					}
				});
				$('.showSPList').removeClass('none');
			} else {
				$('.showSPList').addClass('none');
			}
		};
		
		$scope.hospitalDocHandle	=	function(vertical_id) {
			if(vertical_id == 11) {
				$scope.vertical_name	=	'doctor';
			} else {
				$scope.vertical_name	=	'hospital';
			}
			APIServices.docHospRedirectCheck($rootScope.parentid,DATACITY,$scope.vertical_name).success(function(response){
				if(vertical_id == 11) {
					if(response.data == 1) {
						window.location	=	'../business/docs_hosp_list.php?parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
					} else {
						window.location	=	'../business/bform_doctor.php?flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
					}
				} else {
					if(response.data == 1) {
						window.location	=	'../business/hosp_docs_list.php?parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
					} else {
						window.location	=	'../business/bform_hospital.php?flow_flag=1&parentid='+$rootScope.parentid+'&root_parentid='+$rootScope.parentid;
					}
				}
			});
		};
		
		$scope.othersVerticalHandle	=	function(vertical_id) {
			if(vertical_id == 20) {
				$scope.others_vertical_name = 'banquet hall';
			}
			APIServices.othersVerticalRedirect($rootScope.parentid,DATACITY,USERID,$scope.others_vertical_name).success(function(response){
				switch(vertical_id)
				{
					case '20' :
						window.location	=	response.vertical_redirect_url;
					break;
				} 
			});
		}
		$scope.bringOverlay	=	function() {
			var posLeft	=	$('.pro-pic-welcome').position().left;
			var posTop	=	$('.pro-pic-welcome').position().top;
			$('.onTheTop').css('left',posLeft+'px');
			$('.onTheTop').css('top',posTop+'px');
			$scope.show	=	!$scope.show;
		}
		
		$scope.closeOverlay	=	function(obj,$event) {
			$scope.show	=	!$scope.show;
		}
		
		$scope.closeDash	=	function(obj,event) {
			$('.overlay').addClass('hide');
			$('.dashWidget').addClass('hide');
		}
		
		$scope.name	=	Paths.appname;
                
		$scope.logoutTme	=	function() {
                        $scope.getLogoutData = {};
                        $scope.getLogoutData['employee_id'] = USERID;
                        
                        APIServices.updtLogoutTime($scope.getLogoutData).success(function (response) {
                            
                        });
                        
			if(STATID && $.inArray(parseInt(STATID),$scope.autoDialer) !== -1) {
				window.parent.logoutDialer(USERID,$rootScope.employees.remoteAddr);
			} else {
				ForLogout(USERID,'');
			}
			$cookieStore.remove('currLink');
			$cookieStore.remove('currPage');
			$cookieStore.remove('extraVals');
			$cookieStore.remove('thisPage');
			$cookieStore.remove('pageNo');
		};
		
		if(STATID) {
			$rootScope.stationId	=	1;
		} else {
			$rootScope.stationId	=	0;
		}
		
		//Service Identifier to get Menu Links
		//~ APIServices.getMenuLinks($scope.id).success(function (response) {
			//~ $rootScope.linksData	=	[];
			//~ $rootScope.linksReportIn	=	[];
			//~ angular.forEach(response.data,function(value,key) {
				//~ if(value != null) {	
					//~ if(value.display_menu	==	1 || value.display_menu	==	4) {	
						//~ $rootScope.linksData.push(value);
					//~ } else {
						//~ $rootScope.linksReportIn.push(value);
					//~ }
				//~ }
			//~ });
		//~ });
		
		$scope.openPayNarrat	=	function() {
			window.open('../business/Parentid/PaymentNar.php?Parentid='+$rootScope.parentid,'mywindow','width=800,height=550,left=400,top=300,screenX=0,screenY=100,scrollbars=1');
		}
		
		$scope.showBottomOtherCampList = function($event) {
			$scope.alert = '';
			$mdBottomSheet.show({
				templateUrl: 'partials/showOtherCampaignCard.html',
				controller: 'showOtherCampList',
				targetEvent: $event
			}).then(function(clickedItem) {
				$scope.alert = clickedItem.name + ' clicked!';
			});
		};
	}).controller('LeftCtrl', function ($scope, $timeout, $mdSidenav, $log) {
		$scope.close = function () {
			$mdSidenav('left').close()
				.then(function () {
				});
		};
	}).controller('showOtherCampList', function($scope, $mdBottomSheet) {
		$scope.items = [
			{ name: 'SMS Leads',clickLink: '../business/index1.php'},
			//{ name: 'Competitors Banner',clickLink: '../business/web_promo.php' },
			{ name: 'Banner Campaign',clickLink: '../business/category_sponsership.php' },
			//~ { name: 'Category Filter Banner',clickLink: '../business/category_filter_banner.php' },
			//~ { name: 'Category Text Banner',clickLink: '../business/category_text_banner.php' },
		  ];
		$scope.listItemClick = function($index) {
			var clickedItem = $scope.items[$index];
			$mdBottomSheet.hide(clickedItem);
		};
	});
	
	tmeModuleApp.controller('welcomeController',function($scope,$state,$cookieStore,$rootScope,APIServices) {
		$scope.currLinkValue		=	$cookieStore.get('currLink');
		$scope.extraValsCook		=	$cookieStore.get('extraVals');
		$scope.currPageReportCook	=	$cookieStore.get('currPageReport');
		//$cookieStore.remove('currLink');
		
		if(typeof $scope.currLinkValue !== 'undefined' && $scope.currLinkValue !== '') {
			if($scope.currLinkValue	==	'.report') {
				$state.go('appHome.report',{currPage:$scope.currPageReportCook,srchparam:'',srchWhich:'',extraVals:$scope.extraValsCook});
			} else if($scope.currLinkValue	==	'.filter') {
				$state.go('appHome'+$scope.currLinkValue,{currPage:$cookieStore.get('currPageFilter'),srchparam:$cookieStore.get('filterVal'),srchWhich:$cookieStore.get('cookExtraVal')});
			} else {
				$state.go('appHome'+$scope.currLinkValue,{srchparam:'',srchWhich:'',extraVals:$scope.extraValsCook});
			}
		} else if($scope.currLinkValue == '') {
			$state.go('appHome',{srchparam:'',srchWhich:'',extraVals:''});
		}
	});

	// This controller is common controller ,used for mktgPage
	tmeModuleApp.controller('mktgPageController',function($scope,APIServices,$rootScope,$state,$location,$http,$cookieStore,$timeout,$mdToast,$mdDialog,$sce,$filter) {
		/// Ahd lineage check
		if(DATACITY.toLowerCase() == 'delhi'){
			APIServices.getAhdLineage().success(function(response){
				if(response.errorcode == 1){
					$rootScope.showCommonPop = 'ahmdlineage';
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please update your Lineage on My Jd app in Lineage Selection.";
				}
			});
		}
		/// Ahd lineage check
		$scope.chk_city	=	DATACITY.toLowerCase();
		var time = 0;
		var data	=	{};
		data['empcode'] = USERID;
		data['timer'] = 120;
		data['secondTime'] = 5;
		$rootScope.loginUserId		=	USERID;
		$rootScope.loginUserCity	=	DATACITY;
		var connectionCall = new autobahn.Connection({transports: [{'type': 'websocket','url': 'ws://192.168.55.105:8082/ws'}],realm: "disposition_mandate"});
		if(!connectionCall.isConnected) {
			connectionCall.onopen = function (session, details) {
				console.log('Connecte TO Crossbar');
				session.publish('call.disconnect.gettime', [data]);
				session.subscribe('call.disconnect.timerupdate'+USERID, function(info) {
					console.log('inside timerupdate');
					time = parseInt(info[0].time);
					console.log('time'+time);
					publishTME(time,data);
				});
			};
			connectionCall.open();
			
			connectionCall.onclose = function (reason, details) {
				console.log("Connection lost CALL DISPOSITION : " + reason);
			}
		}
		
		
		var status 						=	 0;
		var interval 					=	'';
		var intervalReset 				= 	'';
		$scope.moveFrom					=	false;
		$scope.moveTo					=	false;
		$scope.callDisconnectPopUp		=	false;
		$scope.disp_overlay				=	false;
		$scope.counterVal				=	{};
		$scope.dispose_value			=	[];
		$scope.dispose_value[0]			=	'';
		$scope.dispose_name				=	[];
		$scope.dispose_name[0]			=	'';
		$scope.counterVal['timer']		=	0;
		var level						=	0;
		
		function publishTME(time,data){
		var statTime	=	data['timer'];
		if(time == -1) {
			if(status != 1) {
				$('.counterVal').html('No Timer');
			}
		}
		else {
			var interval = setInterval(function(){
				time = time - 1;
				if(time < 0) {
					if(status != 1) {
						$('.counterVal').html('Times Ups');
					}
					
				}else if(time == 0 || time == 1){
					APIServices.getTimerStatus().success(function(response) {
							if(response.errorCode == 0){
								$scope.conStatus	=	response.data.isConnected;
								console.log($scope.conStatus);
								if($scope.conStatus	==	1){
									APIServices.timeUp().success(function(response) {
										status = 1;
										$scope.dispname				=	'No status';
										$rootScope.dispose 			=	402;
										if($rootScope.companyTempInfo == undefined){
											$scope.pid = 'PXX888888';
											$state.go('redirectDispose',{parentIdSt:$scope.pid,stVal:$rootScope.dispose});
										}else{
											$state.go('redirectDispose',{parentIdSt:$rootScope.companyTempInfo.data.parentid,stVal:$rootScope.dispose});
										}
										$('.counter').html('Time Ups @ '+moment().format('MMMM Do YYYY, h:mm:ss a'));
									});
								}
							}else{
								APIServices.timeUp().success(function(response) {
										status = 1;
										$scope.dispname				=	'No status';
										$rootScope.dispose 			=	402;
										if($rootScope.companyTempInfo == undefined){
											$scope.pid = 'PXX888888';
											$state.go('redirectDispose',{parentIdSt:$scope.pid,stVal:$rootScope.dispose});
										}else{
											$state.go('redirectDispose',{parentIdSt:$rootScope.companyTempInfo.data.parentid,stVal:$rootScope.dispose});
										}
										clearInterval(interval);
										$('.counter').html('Time Ups @ '+moment().format('MMMM Do YYYY, h:mm:ss a'));
									});
							}
						});
				}
				else {
					APIServices.getTimerStatus().success(function(response) {
						console.log(JSON.stringify(response));
						if(response.errorCode	==	0){
							$scope.conStatus	=	response.data.isConnected;
							if($scope.conStatus	==	0){
								APIServices.stopTimer().success(function(response) {
								});
							}else{
									if(response.data.isConnected == 1){
										if(level != 1){
											$scope.moveTo = true;
											watch(time,statTime);
											$('.counterVal').html(time);
											status = 1;
										}else{
											watch(time,statTime);
											$('.counterVal').html(time);
											status = 1;
										}
									}
								}
						}else{
							if(level != 1){
								$scope.moveTo = true;
								watch(time,statTime);
								$('.counterVal').html(time);
								status = 1;
							}else{
								watch(time,statTime);
								$('.counterVal').html(time);
								status = 1;
							}
						}
					});
					if(time == data['secondTime']){
						$scope.moveTo 		= false;
						APIServices.getTimerStatus().success(function(response) {
							if(response.errorCode == 0){
								if(response.data.isConnected	==	1){
									APIServices.secondTimer().success(function(response) {
										$scope.auto_dispose	=	true;
										$scope.callDisconnectPopUp = false;
										$scope.disp_overlay = true;
										$scope.$apply();
									});
								}
							}else{
								APIServices.secondTimer().success(function(response) {
									$scope.auto_dispose	=	true;
									$scope.callDisconnectPopUp = false;
									$scope.disp_overlay = true;
									$scope.$apply();
								});
							}
						});
					}
				}
			},1000);
		}
	}
			 
			 
	 function watch(time,statTime){
			var timeThresh	=	statTime;
			var timePercen	=	(time/statTime)*100;
			Highcharts.setOptions({
				colors: '#4082C4',
				backgroundColor: '#CCCCCC'
			});	
			
			Highcharts.chart('timercon', {
				chart: {
					renderTo:'container',
					type: 'solidgauge',
					backgroundColor:'rgba(255, 255, 255, 0.1)'
				},
				title: '',
				tooltip: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				pane: {
					center: ['50%', '40%'],
					size: '80%',
					startAngle: 0,
					endAngle: 360,
					background: {
						backgroundColor: '#CCCCCC',
						innerRadius: '90%',
						outerRadius: '100%',
						borderWidth: 0
					}
				},
				yAxis: {
					stops: [
					[0.1, '#db2828'],
					[0.2, '#db2828'],
					[0.3, '#db2828']
					   ],
					min: 0,
					max: 100,
					labels: {
						enabled: false
					},
			
					lineWidth: 0,
					minorTickInterval: null,
					tickPixelInterval: 400,
					tickWidth: 0
				},
				plotOptions: {
					solidgauge: {
						innerRadius: '90%'
					}
				},
				series: [{
					name: 'Speed',
					data: [timePercen],
					dataLabels: {
						enabled: false
					}
				}]
			});
			
			
			
			Highcharts.chart('timercont', {
				chart: {
					renderTo:'container',
					type: 'solidgauge',
					backgroundColor:'rgba(255, 255, 255, 0.1)' 
				},
				title: null,
				tooltip: {
					enabled: false
				},
				credits: {
					enabled: false
				},
				pane: {
					center: ['50%', '40%'],
					size: '80%',
					startAngle: 0,
					endAngle: 360,
					background: {
						backgroundColor: '#CCCCCC',
						innerRadius: '90%',
						outerRadius: '100%',
						borderWidth: 1
					}
				},
				yAxis: {
					stops: [
					[0.1, '#db2828'],
					[0.2, '#db2828'],
					[0.3, '#db2828']
					   ],
					min: 0,
					max: 100,
					labels: {
						enabled: false
					},
			
					lineWidth: 0,
					minorTickInterval: null,
					tickPixelInterval: 400,
					tickWidth: 0
				},
				plotOptions: {
					solidgauge: {
						innerRadius: '90%'
					}
				},
				series: [{
					name: 'Speed',
					data: [timePercen],
					dataLabels: {
						enabled: false
					}
				}]
			});
		}	
		
		$scope.returnRes = [];
		$rootScope.filename = [];
		$scope.links = null;
		$scope.isDisabled = true;
		$scope.showSearchNorm	=	true;
		
		
	
/************************** Grabbed Notification Code *************************************/	
		$scope.grabbedCount = 0;
		$scope.ungrabbedCount = 0;
		$scope.showNotificationValue = false;
		
		
		socket.on(USERID+'_notificationCount', function(data) {
			console.log(data);
			$mdToast.show(
				$mdToast.simple()
				.content(data.message)
				.position('top right')
				.hideDelay(10000)
			);
	   });
	   var mainInfo = {};
	   mainInfo['emp_code'] = USERID;
	   mainInfo['data_city'] = DATACITY;
	   socket.emit('getNotification', mainInfo);
	   setInterval(function(){socket.emit('getNotification', mainInfo);},1800000);
	   
	   socket.on('allNotification', function(data)
	   {
		  var counter = 0;
		  if(data['grabbed'] != undefined) {
			$.each(data['grabbed'], function(key, value)
			{
				counter = counter+1;
			});
			$scope.grabbedCount = counter;
			$scope.grabbednotificationData = data['grabbed'];
		  } 
		  else {
			  $scope.grabbedCount = 0;
		  }
		  counter = 0;
		  if(data['ungrabbed'] != undefined) {
			$.each(data['ungrabbed'], function(key, value)
			{
				counter = counter+1;
			});
			$scope.ungrabbedCount = counter;
			$scope.ungrabbednotificationData = data['ungrabbed'];
		  } 
		  else {
			  $scope.ungrabbedCount = 0;
		  }
	   });
	   
	   socket.on(USERID+'_notificationCount', function(data)
	   {
		  socket.emit('getNotification', data);
	   });
	   
	   $scope.showNotification = function(process)
	   {
		   if(process == 'grabbed') {
			   $scope.notificationData = $scope.grabbednotificationData;
			   if($scope.grabbedCount != 0) {
				   if($scope.previousNotification == process && $scope.showNotificationValue === true) {
						$scope.showNotificationValue = false;
					}
					else {
						$scope.showNotificationValue = true;
					}
					var sendData = {};
					sendData['emp_code'] = USERID;
					sendData['data_city'] = DATACITY;
					socket.emit('resetNotification', sendData);
				}
			}
			if(process == 'ungrabbed') {
				$scope.notificationData = $scope.ungrabbednotificationData;
			   if($scope.ungrabbedCount != 0) {
				   if($scope.previousNotification == process && $scope.showNotificationValue === true) {
						$scope.showNotificationValue = false;
					}
					else {
						$scope.showNotificationValue = true;
					}
				}
			}
			$scope.previousNotification = process;
	   };
	
	
	
		/************************************Disposition**************************************************************/
		
		
	   $rootScope.hide_commonPopup = function(){
			$rootScope.showCommonPop = 0;
	   }
	   
	   $rootScope.redirectMainDown = function(){
			window.location = '../newTme';
	   }
	   
		
	   $scope.recieverNotify   =       function() {
		  
			$scope.conStatus	=	'';
			var interval	=	 setInterval(function(){
				if(connectionCall.isConnected	==	1){
				connectionCall.session.subscribe('disposition_mandate.call.disconnect.'+USERID, function(info){
						connectionCall.session.publish('call.disconnect', [data]);
						console.log('Data Publish');
						level = 1;
						APIServices.getTimerStatus().success(function(response) {
							if(response.errorCode	==	0){
								$scope.conStatus	=	response.data.isConnected;
								console.log($scope.conStatus);
								if($scope.conStatus	==	0){
									APIServices.stopTimer().success(function(response) {
										if(response == 1){
											$scope.disp_overlay 		= true;
											$scope.callDisconnectPopUp 	= true;
											$scope.moveFrom 			= true;
											$scope.show_all_disp		= true;
											$scope.disp_list1			= true;
											$scope.moveTo 				= false;
											$scope.$apply();
										}
									});
								}else{
									$scope.disp_overlay 		= true;
									$scope.callDisconnectPopUp 	= true;
									$scope.moveFrom 			= true;
									$scope.show_all_disp		= true;
									$scope.disp_list1			= true;
									$scope.moveTo 				= false;
									$scope.$apply();
								}
							}else{
								$scope.disp_overlay 		= true;
								$scope.callDisconnectPopUp 	= true;
								$scope.moveFrom 			= true;
								$scope.show_all_disp		= true;
								$scope.disp_list1			= true;
								$scope.moveTo 				= false;
								$scope.$apply();
							}
						});
					});
					clearInterval(interval);
					}
				},1000);
			
		};
		
		
			
			
		
		$scope.suspendDispose	=	function(event){
			$scope.callDisconnectPopUp	=	false;
			$scope.moveFrom				=	false;
			$scope.moveTo				=	true;
			$scope.disp_overlay 		=  false;
		}
		
		$scope.show_all				= function(event){
			$scope.disp_list2		=	 true;
			$scope.show_imp_disp	= 	true;
			$scope.show_all_disp	=	false;
		};
		
		$scope.show_imp				= 	function(event){
			$scope.disp_list2		=	false;
			$scope.show_imp_disp	= 	false;
			$scope.show_all_disp	=	true;
		};
		
		
		$scope.select_disposition	=	 function(key,value,event){
			$scope.dispose_value[0]		=	key;
			$scope.dispose_name[0]		=	value;
		};
		
		

		
				
		$scope.submitDispose	=	function(event){
			if($scope.dispose_value[0] == 0 || $scope.dispose_value[0] == undefined){
				$mdToast.show(
				$mdToast.simple()
				.content('Please select disposition')
				.parent('.disposition')
				.position('top right')
				.hideDelay(10000)
				);
				return false;
			}else{
  				$rootScope.dispose =  $scope.dispose_value[0];
  				$scope.dispname	   =	$scope.dispose_name[0];
			}
			$scope.disposeLoader	=	true;
			$scope.saveDispose(event);
		};
		
		$scope.recieverNotify();
		/************************************Disposition**************************************************************/
		
	/***************************************************Default Penalty Pop Up**************************************************************************************/
			$scope.showDoDont			=	0;
			$scope.penaltyData			=	{};	
			//$scope.index				=	1;
			$scope.indx				=	1;
			$scope.displayPenalty		=	0;
			$scope.totalDef				=	0;
			$scope.checkCon				=	[];
			$scope.checkCon[0]			=	0;	
			$scope.doDontMsgs			=	{};
			$scope.doDontMsgs['do_msg']		=	{};
			$scope.doDontMsgs['dont_msg']	={};
			$scope.penMonth				=	'';
			$scope.penYear				=	'';


			APIServices.getDoDont().success(function(response){
				if(response.errorCode	==	0){
				
					//~ alert(JSON.stringify(response.data));
					angular.forEach(response.data,function(key,val) {
						
						if(key.do_msg != ''){
							$scope.doDontMsgs['do_msg'][val]			=	key.do_msg;			
						}
						
						if(key.dont_msg != ''){
							$scope.doDontMsgs['dont_msg'][val]			=	key.dont_msg;			
						}
					});
				}
			}); 
			
		
			
			$scope.nextDefaulter	=	function(){
				if($scope.indx < $scope.totalDef){
					$scope.indx	=	$scope.indx + 1;
				}else if($scope.indx == $scope.totalDef){
					$scope.displayPenalty	=	0;
					$scope.showDoDont		=	1;
				}
			}
			
			$scope.prevDefaulter	=	function(){
				if($scope.indx > 1){
					$scope.indx	=	$scope.indx - 1;
				}
			}
			
			$scope.previousDef	=	function(){
				$scope.indx			=	$scope.totalDef;
				$scope.displayPenalty	=	1;
					$scope.showDoDont	=	0;
			}
			
			$scope.checkboxWarning = function(){
				var checked = this.checked;
			};
			$scope.dateafter7days = '';
			$scope.dateoftoday = '';
			$scope.closeDef	=	function(){
				if($scope.checkCon[0]	==	false){
					alert('Please click the checkbox to accept the same');
					return false;
				}else{ //onclose of previous popup
					APIServices.insertPenDate().success(function(response){
						if(response.errorCode	==	0){
							$scope.displayPenalty	=	0;
							$scope.showDoDont		=	0;
						}
					});	
				}
			};
			/***************************************************Default Penalty Pop Up**************************************************************************************/
	
	
	/********************OMNI PDF***************************************/
	
		$scope.showOmniPDF	=	function(){
				window.open("img/Send_Omni_Link_Training_Doc3-1.pdf");
		};
	
	
	/********************OMNI PDF***************************************/
	/*******************************Add Transfer Call Function IRO*********************************/

$scope.saveExit=	function(parentid,city,paidflag){
	APIServices.iroAppSaveExit(parentid,city,paidflag).success(function(response) { 
		if(response.errorCode == 0)
		{
			alert('Data Save Sucessfully');
			$('.iroAppoverlay').hide();
			$('.popTopBarBroad').hide();
		}
	});
}

$scope.close_fn=	function(){
	$('.iroAppoverlay').hide();
	$('.popTopBarBroad').hide();
}
$scope.proceedWithCompany = function(Parentid,city,Uniquefield,source,businessname){
		var loc	=	'';
		
		var area = $('#area').val();
		var pincode = $('#pincode').val();
		
		/*if(area == null || area == '')
		{
			alert('Please enter area');
			return false;
		}
		if(pincode == null ||  pincode == '')
		{
			alert('Please enter pincode');
			return false;
		}*/
		
		var calldis=0;
		if ($('#linedis').is(":checked"))
		{
			 calldis = 1;
		}
		
		APIServices.proceedCompany(Parentid,city,Uniquefield,area,pincode,source,businessname,area,pincode,calldis).success(function(response) { 
		
		if(response.errorCode == 0)
		{
			loc = "../tmAlloc/mktgGetContDataNew.php?parentid="+Parentid+"&flgSrc=2&flgAfterCall=1&dialFlgDialer=1";
			location.href = loc; 
			//alert('Data Save Sucessfully');
			$('.iroAppoverlay').hide();
			$('.popTopBarBroad').hide();
		}
	});
		
}
	
//~ $scope.maindivMsg = false;
//~ $scope.disPlaymsgmessageBroadcastPop=	function(){
	//~ $scope.Messagedetails = {};
	//~ $scope.empCode = '';
	//~ $scope.autoId = '';
	//~ APIServices.EmpMessageDetails().success(function(response) { 
		//~ $scope.Messagedetails = response;
		//~ $scope.empCode = $scope.Messagedetails.id;
		//~ $scope.autoId = $scope.Messagedetails.auto_id;
		//~ console.log($scope.empCode);
		//~ console.log($scope.autoId);
		//~ if($scope.Messagedetails != null)
		//~ {
			//~ $scope.maindivMsg= true;
			//~ $scope.MessageOk = function()
			//~ {
				//~ APIServices.EmpMessageUpdates($scope.empCode,$scope.autoId).success(function(response) { 
					//~ $scope.Messageupdate = response;
					//~ if($scope.Messageupdate == true)
					//~ {
						//~ var toast = $mdToast.simple()
						//~ .content('Thank You')
						//~ .action('OK')
						//~ .highlightAction(true)
						//~ .hideDelay(0)
						//~ .position('bottom right')
						//~ .parent('#messageDiv');
						//~ $mdToast.show(toast);
						//~ $scope.maindivMsg = false;
					//~ }
				//~ });
			//~ }
			//~ $scope.MessageCancel = function()
			//~ {
				//~ 
				//~ var toast = $mdToast.simple()
				//~ .content('Thank You')
				//~ .action('OK')
				//~ .highlightAction(true)
				//~ .hideDelay(0)
				//~ .position('bottom right')
				//~ .parent('#conMsg');
				//~ $mdToast.show(toast);
				//~ $scope.maindivMsg = false;
		//~ 
			//~ }
		//~ }
	//~ });
	//~ 
//~ }
//~ 
//~ 
//~ setTimeout(function(){
		//~ connection.session.subscribe('personal.messageTo.'+USERID, function(info)
		//~ {
			 //~ var toast = $mdToast.simple()
			//~ .content(info[0].message)
			//~ .action('OK')
			//~ .highlightAction(true)
			//~ .hideDelay(0)
			//~ .position('bottom right')
			//~ .parent('#mainContent');
			//~ $mdToast.show(toast);
			//~ notifyMe(info[0]);
		//~ });
	//~ },1000);
	//~ 
	   //~ 
	   
$scope.Showbroad =0;

		$scope.notArr   			=   [];
        $scope.notArrLength 		=   0;
        $scope.newMsgCount  		=   0;
		$scope.msg_timeDisp			=	[];
		$scope.MessageDisp			=	[];
		$scope.MytrustedHtmlDisp	=	[];
		$scope.showBroadDisp		=	0;
		$scope.media_id             =   [];
		$scope.media_path           =   [];
		$scope.media_show           =   [];
		$scope.title                =   [];
		$scope.para                 =   [];
		$scope.indexarr				=   [];
		$scope.emptypebroadcast="TME";
		$scope.limiter = 10;
		$scope.index=0;
		APIServices.EmpMessageDetails(USERID,$scope.emptypebroadcast).success(function(response) {
            if(response.errorCode   ==  0) {
                $scope.notArr   =   response.data;
            }

            angular.forEach($scope.notArr,function(value,key) {
                 if(value.flag == 1){
                     $scope.newMsgCount = $scope.newMsgCount + 1;
                     $scope.showBroadDisp			 =	1;
                      $scope.index=key;
					$scope.msg_timeDisp[key]         = 	value.msg_time;
					$scope.MessageDisp[key]  		 = 	value.message;
					$scope.media_id[key]  		 = 	value.media_id;
					$scope.media_path[key]  		 = 	value.media_path;
					$scope.media_show[key]  		 = 	value.media_show;
					$scope.title[key]  		 = 	value.title;
					$scope.para[key]				='onload';
					$scope.MytrustedHtmlDisp[key] 	 = 	$sce.trustAsHtml($scope.MessageDisp[key]);
                 }
                 //value.msg_time = new Date(value.msg_time);
                 $scope.notArrLength = $scope.notArrLength+1;
            });



        });
         $scope.closePopTopBroadDsip =   function(key,id,parameter){
			if(parameter=='onload'){
				$scope.index=key-1;
			}
			else if(parameter=='noti'){
				console.log($scope.indexarr);
				$scope.i=$scope.i-1;
				$scope.index=$scope.indexarr[$scope.i];
				$scope.indexcheck--;
			}
			console.log($scope.index);
				$scope.newMsgCount--;
                $('.broaddisp_'+key).remove();
                var timesend  = new Date();
				var timesenddate =timesend.toLocaleDateString();
				var timesendtime=timesend.toLocaleTimeString();
				var time= timesenddate +"  "+ timesendtime;
				var emp ={ employee_id : USERID,
						   employee_name : UNAME,
						  read_date_time : time,
						  flag :0	
					};
				APIServices.EmpMessageUpdates(id,emp).success(function(response) {
					 angular.forEach($scope.notArr,function(value,key) {
						 if(value.media_id == id){ 
							if(value.flag == 1){ 
								value.flag = 0; 
								}
							}
						});
				});
                 if($('.broadDisp').length	==	0 && ($scope.index<0 || $scope.indexcheck==0)){
					$scope.showBroadDisp	=	0;
					$scope.index=0;
				}
        };
		$scope.newMsgCount=0;
		$scope.showNotBox =0;
        $scope.recieverNotify   =       function() {
            var connection = new autobahn.Connection({transports: [{'type': 'websocket','url': 'ws://192.168.12.138:8080/ws'},{'type': 'longpoll','url': 'http://192.168.12.138:8080//lp'}],realm: "realm1"});
            connection.onopen = function (session, details) {
                sess = session;
                var controllerChannelId = null;
                console.log("dgdg"+$scope.notArr.length);
                sess.subscribe('personal.messagebroadcast.'+USERID, onNotification).then(
                    function(subscription) {
                        currentSubscription = subscription;
                        //console.log(currentSubscription);
                    },
                    function(error) {
                        //console.log("subscription error ", error);
                    }
                );
            };
            connection.open();
        };
         function onNotification(args, kwargs, details) {
			 console.log($scope.notArr.length);
			 console.log(args[0]);
                var scope = angular.element($("#homeData")).scope();
				setTimeout(function() {
                scope.$apply(function(){
                    ////console.log(scope.notArr);
                     var ObjLength           =   Object.size(scope.notArr);
                    scope.notArrLength      =   ObjLength+1;
                    scope.notArr[ObjLength] =   {};
                    scope.notArr[ObjLength] =   args[0];
                    scope.notArr[ObjLength].senderId    =   'Justdial';
					console.log(args[0]);
					$scope.newMsgCount 				 = 	$scope.newMsgCount + 1;
					$scope.indexcheck=0;
					$scope.i=0;
                    angular.forEach(scope.notArr,function(value,key) {
                        if(value.flag == 1){
							 $scope.indexcheck++;
                            $scope.index		=						key;
                            $scope.para[key]				='noti';
                            $scope.showBroadDisp			 =	1;
							$scope.msg_timeDisp[key]         = 	value.msg_time;
							$scope.MessageDisp[key]  		 = 	value.message;
							$scope.media_id[key]  		 = 	value.media_id;
							$scope.media_path[key]  		 = 	value.media_path;
							$scope.media_show[key]  		 = 	value.media_show;
							$scope.title[key]  		 = 	value.title;
							$scope.MytrustedHtmlDisp[key] 	 = 	$sce.trustAsHtml($scope.MessageDisp[key]);
							$scope.indexarr[++$scope.i]=key;
                        }
                    });

                });
                }, 2000);

        };
         $scope.showNotBoxFunc   =   function(val) {
        if(val == 0){
            $scope.hidelogout = false;
            if($scope.newMsgCount == 0){
				 angular.forEach($scope.notArr,function(value,key) {
						if(value.flag == 1){ 
							value.flag = 0; 
							var timesend  = new Date();
							var timesenddate =timesend.toLocaleDateString();
							var timesendtime=timesend.toLocaleTimeString();
							var time= timesenddate +"  "+ timesendtime;
							var emp ={ employee_id : USERID,
									   employee_name : UNAME,
									  read_date_time : time,
									  flag :0	
								};
							APIServices.EmpMessageUpdates(value.media_id,emp).success(function(response) {
								angular.forEach($scope.notArr,function(value,key) {
										if(value.flag == 1){ 
											value.flag = 0; 
											}
									});
							});
						}
					 });
            }
            if($scope.showNotBox    ==  0) {
                $scope.showNotBox   =   1;
                 $scope.newMsgCount = 0;
            } else {
                //console.log("flag set");
                $scope.showNotBox   =   0;
                $scope.showBroadDisp	=	0;
                angular.forEach($scope.notArr,function(value,key) {
                    value.flag = 0;
                    //value.msg_time = new Date(value.msg_time);
                });
            }
        }
    };
     $scope.Showbroad =0;
        $scope.Messagedetails = {};
        $scope.msg_time ={};
        $scope.Message = '';
        $scope.view_msg = function(data,ev){
			console.log(data);
            $scope.Messagedetails   = data;
            $scope.showNotBox       =   0;
            $scope.newMsgCount      = 0;
            $scope.msg_time         = $scope.Messagedetails.msg_time;
            $scope.msg_time         = $filter('date')($scope.msg_time, "MMMM dd HH:mm a");
            $scope.Message = $scope.Messagedetails.message;
            $scope.MytrustedHtml = $sce.trustAsHtml($scope.Message);
            console.log($scope.MytrustedHtml);

            if($scope.Messagedetails != null)
                {
                    $scope.Showbroad = 1;
                    $scope.showPopTopBroadcast();
                }else
                {
                    $('.popTopBarBroad').slideUp("slow",function(){});
                    $scope.Showbroad = 0;
                }


        }

        $scope.showPopTopBroadcast  =   function() {
            $('.popTopBarBroadcast_overlay').show();
            $('.popTopBarBroadcast').show();
            
        };

        $scope.closePopTopBroad =   function(id,flag){
                $('.popTopBarBroad_overlay').hide();
                $('.popTopBarBroadcast').hide();
                
                var timesend  = new Date();
                var timesenddate =timesend.toLocaleDateString();
				var timesendtime=timesend.toLocaleTimeString();
				var time= timesenddate +"  "+ timesendtime;
                var emp ={ employee_id : USERID,
						   employee_name : UNAME,
						  read_date_time : time,
						  flag :0
							
					};
					if(flag==1){
					APIServices.EmpMessageUpdates(id,emp).success(function(response) {
						 angular.forEach($scope.notArr,function(value,key) {
							 if(value.media_id == id){ 
								if(value.flag == 1){ 
									value.flag = 0; 
									}
								}
							});
						});
					}
        };

        
        $scope.buttonText = "Show More";
        $scope.show_more_msg = function(){
            $scope.limiter = $scope.limiter + 10;
            if($scope.limiter > $scope.notArrLength) {
                $scope.buttonText = "No more messages";
            }
        }
        $scope.recieverNotify();  
/************************** Grabbed Notification Code *************************************/			
		
		/********************************LINEAGE****************************/
		//~ APIServices.countRequest().success(function (response) {
						//~ $scope.lincount = response.data.count;
						//~ $scope.err 		= response.errorCode;
					//~ });
		$scope.empLineage			=	0;
		$rootScope.fetchLineageInfo	=	[];
		$scope.dataAllPopup			=	0;
		$scope.lineage_sel				=	[];
		$scope.city_sel_lin				=	[];
		$scope.fetchLineageInfo			=	[];
		$scope.lineage_sel[0]			=	'';
		$scope.city_sel_lin[0]			=	'';
		$scope.showDiv 					= 	1;
		$scope.status					=	'';
		$scope.checklineage				=	0;
		$scope.fetchLineageInfo			=	{};
		$scope.getLineageStat			=	0;
		$scope.showverdiv 				= 	0;
		$scope.showManagerDiv 			= 	0;
		$scope.mobno 					= 	'';
		$scope.radval1					=	'';
		$scope.radval2					=	'';
		$scope.field					=	'';
		$scope.verinput1				=	'';
		$scope.verinput2				=	'';
		$scope.citytype					=	'';
		$scope.team_name				= 	[];
		$scope.team_name[0]				=  	'none';
		$scope.valclose 				= 	'';
		$scope.showlineagePopUp = function(val) {
			$scope.valclose = val;
			APIServices.getLineage().success(function (response) {
				//check the Lineage Entry in table
				$scope.fetchLineageInfo	=	response;
				if(val	==	0){	// data present,so dont show on load 
					$scope.dataAllPopup	=	1;
						$scope.showDiv		=	0;
					}
				//~ if(($scope.fetchLineageInfo.errorCode	==	0 ) && val	==	0){	// data present,so dont show on load 
					//~ $scope.dataAllPopup	=	0;
						//~ $scope.showDiv		=	1;
					//~ }
					//~ else if(($scope.fetchLineageInfo.errorCode	==	1 ) && val	==	0){ // data not present, so show on load
						//~ $scope.dataAllPopup	=	1;
						//~ $scope.showDiv		=	0;
					//~ }
					else if(($scope.fetchLineageInfo.errorCode	==	0) && val	==	1){	//show pop up onclick of lineage as data present
						if($scope.fetchLineageInfo.errorStatus	!=	"Software employee"){
							$scope.city_sel_lin[0]	=	$scope.fetchLineageInfo.data.city;
							$scope.mobno			=	$scope.fetchLineageInfo.data.employee_mobile;
							$scope.lineage_sel[0]	=	$scope.fetchLineageInfo.data.reporting_head_name+'-'+$scope.fetchLineageInfo.data.reporting_head_code;
							$scope.managercode		=	$scope.fetchLineageInfo.data.reporting_head_code;
							$scope.radval1			=	$scope.fetchLineageInfo.data.employee_type;
							$scope.radval2			=	$scope.fetchLineageInfo.data.off_calls;
							$scope.field			=	$scope.fetchLineageInfo.data.off_calls;
							$scope.getStatusLin		=	$scope.fetchLineageInfo.data.status;
							$scope.citytype			= 	$scope.fetchLineageInfo.data.city_type;
							if($scope.fetchLineageInfo.data.team_name != ''){
							$scope.team_name[0]		=  	$scope.fetchLineageInfo.data.team_name;
							}else{
							$scope.team_name[0]				=  	'none';	
							}
						}
						$scope.checklineage = 2;
						$('.navbar-fixed').css({
							'z-index': '0'
						});
						$scope.dataAllPopup	=	1;
						$scope.showDiv		=	0;
					}
					else if(($scope.fetchLineageInfo.errorCode	==	1 ) && val	==	1){ //data not present  show pop up onclick
						$scope.dataAllPopup	=	1;
						$scope.showDiv		=	0;
					}
					//~ else{	//	already accepted , No need to show pop up onload
						//~ $scope.dataAllPopup	=	0;
						//~ $scope.showDiv = 1;
					//~ }
				else{ // No entry - let them select manager from the list
					$scope.checklineage = 1;
					$('.navbar-fixed').css({
						'z-index': '0'
					});
					$scope.dataAllPopup	=	1;
					$scope.showDiv		=	0;
				}
			});
		};
		
		$scope.go_back_form	=	function(){
			$scope.checklineage	=	1;
		};
		$scope.closelineage	=	function(){
			$scope.showDiv = 	1;
			$scope.dataAllPopup	=	0;
			
		};
		$scope.submit_lineage	=	function(){	//	insert into the table 
			if($('.auto_select').val()	!=1){
				$mdToast.show(
					$mdToast.simple({
								textContent : "Select From Autosuggest!!",
								parent : 	$document[0].querySelector('.lineage_main_div'),
								position:'top right',
								hideDelay: 2000
							})
					);
					return false;
			}
			$scope.req_sta	=	[];
			var manCodeArr = [];
            manCodeArr 	   = $scope.lineage_sel[0].split('-');
			APIServices.insertlineageDetails(manCodeArr[1],'','').success(function (response) {
				$scope.req_sta		=	response;
				$scope.checklineage	=	3;
			});
		};
		$scope.reporteeInfo	=	[];
		//for fetching reprtees
		$scope.viewReportees	=	function(){
			$scope.showDiv 					= 	1;
			APIServices.fetchreportees().success(function (response) {
				$scope.reporteeInfo		=	response;
				$scope.showManagerDiv = 1;
				$scope.showDiv 		  = 1;
			});
		};
		//
		$scope.back_lineage	=	function(){
			$scope.showDiv 					= 	0;
		};
		$document	=	$(document);
		$scope.report_sel	=	[];
		//for manager not found
		$scope.submit_reportmanager	=	function(){
			APIServices.insertReportDetails().success(function (response) {
				if(response.errorCode	==	0){
					var toast = $mdToast.simple()
						.content('Request sent!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
					$scope.report_sel[0]	=	'';
						$timeout(function () 
						{
						$scope.showDiv = 1;
						$scope.dataAllPopup =0;
						}, 3000);
				}
			});
		};
		$scope.acc_rej_reportee	=	function(reportee,status,confirmed,key){
			if(status	==	1){
				$('.acc_'+key).addClass('act');
				$('.rej_'+key).removeClass('rejectedact');
				//~ $('.rejectCol').css('color','#747474');
			}else{
				$('.rej_'+key).addClass('rejectedact');
				$('.acc_'+key).removeClass('act');
				//~ $('.acceptCol').css('color','#747474');
			}
			APIServices.accetRejectRequest(reportee,status,confirmed).success(function (response) {
				if(response.errorCode	==	0){
				}
			});
		};

			
			$scope.radvals2= function(val){
			if(val == "0"){
				$scope.field="0";
			}else{
				$scope.field="1";
			}		console.log($scope.field);
			};
			
			$scope.city_sel_auto	=	function(){
				if($('.auto_city').val()	!=1 && $scope.city_sel_lin[0]!=''){
					var toast = $mdToast.simple()
						.content('Select From City Autosuggest!!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						$scope.city_sel_lin[0]	=	'';
					return false;
				}
			};
			
			$scope.lin_sel_auto	=	function(){
				if($('.auto_select').val()	!=1 && $scope.lineage_sel[0]!=''){
					var toast = $mdToast.simple()
						.content('Select From Managers Autosuggest!!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
					$scope.lineage_sel[0]	=	'';
					return false;
				}
			};
			
			$scope.empsubmit=function(mobno,city){
				if(mobno	==	""){
					var toast = $mdToast.simple()
						.content('Enter Mobile Number!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if($scope.city_sel_lin[0]	==	undefined || $scope.city_sel_lin[0]	==	""){
					var toast = $mdToast.simple()
						.content('Enter Working City!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if($scope.lineage_sel[0]	==	undefined || $scope.lineage_sel[0]	==	""){
					var toast = $mdToast.simple()
						.content('Enter Your Manager!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if($scope.field	==	""){
					var toast = $mdToast.simple()
						.content('Select Call Value!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if($scope.citytype	==	""){
					var toast = $mdToast.simple()
						.content('Tag Your Working City!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if($scope.team_name[0]	==	"none" || $scope.team_name[0]	==	"" ){
					var toast = $mdToast.simple()
						.content('Select your Team Name!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else if(mobno.length != 10 && mobno!=''){
					var toast = $mdToast.simple()
						.content('Enter Valid Mobile Number!')
						.highlightAction(true)
						.hideDelay(2000)
						.position('top right')
						.parent('#empdet');
						$mdToast.show(toast);
						return false;
				}else {
					
					$scope.req_sta	=	[];
					var manCodeArr = [];
					manCodeArr 	   = $scope.lineage_sel[0].split('-');
					console.log(manCodeArr);
					$scope.managername=manCodeArr[0];
					$scope.managercode=manCodeArr[1];
					$scope.workingCity = city;
					$scope.otpcode = Math.floor(100000 + Math.random() * 900000);
					$scope.otp = $scope.otpcode;
					$scope.mobno=mobno;
					APIServices.insertlineageDetails($scope.managername,$scope.managercode,$scope.workingCity,$scope.mobno,$scope.field,$scope.otpcode,$scope.citytype,$scope.team_name[0]).success(function (response) {
							$scope.req_sta		=	response;
						
							//~ APIServices.sendOTP($scope.otp,$scope.mobno,$scope.managercode).success(function (response) {
								//~ alert("otp sent");	
								//~ });
						});
					
					
					$scope.showverdiv = 1;
					$scope.showDiv =1;	
				}
				
				
				
				};
				
			$scope.resendOTP= function (){
				$scope.otpcode = Math.floor(100000 + Math.random() * 900000);
				APIServices.sendOTP($scope.mobno,$scope.managercode,$scope.otpcode).success(function (response) {
							if(response.errorCode ==0){
								var toast = $mdToast.simple()
								.content('OTP has been sent again')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.verpopup');
								$mdToast.show(toast);
								return false;
						}
					});
				
				
				};
			
					
			$scope.closever	=	function(){
				$scope.showverdiv = 	0;
				$scope.dataAllPopup	=	0;
				
			};
			
			$scope.closemanagerdiv	=	function(){
				$scope.showManagerDiv = 	0;
				$scope.dataAllPopup	  =		0;
				
			};
			
			
			$scope.Reentermob = function(){
				$scope.showverdiv 	= 	0;
				$scope.showDiv 		=  0;
			};
			
			$scope.submitver = function(input1,input2){
				if((input1 != '' && input2!= '')){
					$scope.verinput = input1.concat(input2);
					console.log($scope.verinput);
					APIServices.checkOTP($scope.mobno,$scope.managercode).success(function (response) {
						if(response.data.verification_code	==	$scope.verinput){
							var toast = $mdToast.simple()
								.content('User Verified!!')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.verpopup');
								$mdToast.show(toast);
								$timeout(function () 
							{
							$scope.showverdiv 	= 	0;
							$scope.dataAllPopup		=  0;
							if($scope.valclose == 0){
							APIServices.checkUpdatedOn().success(function(response) {
								if(response.errorCode == 0){
									if(response.showpenalty == 1){
											APIServices.getPenaltyInfo().success(function(response){
												if(response.errorCode	==	0){
													$scope.displayPenalty		=	1;
													angular.forEach(response.data,function(key,val) {
														$scope.penaltyData[val]	=	key;
														$scope.totalDef	=	parseInt(val);
													});
												}
											});
										}
									}
								});
						}
							}, 3000);
							
						}else{
							var toast = $mdToast.simple()
								.content('Please enter correct OTP!!')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.verpopup');
								$mdToast.show(toast);
								return false;
						}
						
							
						
					});		
				}else{
					var toast = $mdToast.simple()
								.content('Enter the OTP!')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.verpopup');
								$mdToast.show(toast);
								return false;
				}	
			};
			
			
			$scope.accrejsubmit = function(){
				var length  = $('input:radio:checked').length;
				if(length	!=	$scope.reporteeInfo.data.length){
					var toast = $mdToast.simple()
								.content('Please Accept/Reject the request to Proceed!')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.manpopup');
								$mdToast.show(toast);
								return false;
				}else{
				var toast = $mdToast.simple()
								.content('Updated Successfully!')
								.highlightAction(true)
								.hideDelay(2000)
								.position('top right')
								.parent('.manpopup');
								$mdToast.show(toast);
						$timeout(function () 
						{
						$scope.showManagerDiv = 0;
						$scope.dataAllPopup =0;
						}, 3000);
					}
				};
				
				$scope.accrejreset = function(){
					$('.lineagestyle').removeClass('act');
					$('.lineagestyle').removeClass('rejectedact');
					$('.rejectCol').css('color','#747474');
					$('.acceptCol').css('color','#747474');
					$('.resetCls').attr('checked',false);
				};
				$scope.lincount	=	'';
				$scope.err		=	'';
			
				
				$scope.seltagcity = function(tagcity){
					$scope.citytype = tagcity;
					}
			
		/********************************LINEAGE****************************/
		
		/*APIServices.checkUpdatedOn().success(function(response) {
				if(response.errorCode == 0){
						if(response.data.penalty_submitted_on != null){
							
							var date = new Date(response.data.penalty_submitted_on);
							var newdate = new Date(date);
							newdate.setDate(newdate.getDate() + 30);
							var dd30 = newdate.getDate();
							var mm30 = newdate.getMonth() + 1;
							var y30 = newdate.getFullYear();
							$scope.dateafter30days = y30 + '-' + mm30 + '-' + dd30;
							
							var todaysdate = new Date();
							var ddt = todaysdate.getDate();
							var mmt = todaysdate.getMonth() + 1;
							var yt = todaysdate.getFullYear();
							$scope.dateoftoday = yt + '-' + mmt + '-' + ddt;
							
							if($scope.dateoftoday == $scope.dateafter30days){
								APIServices.getPenaltyInfo().success(function(response){
									if(response.errorCode	==	0){
										$scope.displayPenalty		=	1;
										angular.forEach(response.data,function(key,val) {
											$scope.penaltyData[val]	=	key;
											$scope.totalDef	=	parseInt(val);
										});
									}
								});
							}
						}
					}else{
						
						APIServices.getPenaltyInfo().success(function(response){
									if(response.errorCode	==	0){
										$scope.displayPenalty		=	1;
										angular.forEach(response.data,function(key,val) {
											$scope.penaltyData[val]	=	key;
											$scope.totalDef	=	parseInt(val);
										});
									}
						});
					}
				});*/
							
						
		
		//Onload Pop Ups
		$scope.onloadPopUps = function(){
			APIServices.checkUpdatedOn().success(function(response) {
					if(response.errorCode == 0){
						//~ if(response.showpopup == 1 ){
									//~ /************lineage CAll*************/
									//~ $scope.showlineagePopUp(0);	
									//~ /************lineage CAll*************/
						//~ }else
						 if(response.showpenalty == 1 ){
								APIServices.getPenaltyInfo().success(function(response){
								if(response.errorCode	==	0){
									$scope.displayPenalty		=	1;
									$scope.penMonth				=	response.month;
									$scope.penYear				=	response.year;
									angular.forEach(response.data,function(key,val) {
										$scope.penaltyData[val]	=	key;
										$scope.totalDef	=	parseInt(val);
									});
								}
							});
						}
					}
					//~ else{
							//~ /************lineage CAll*************/
							//~ $scope.showlineagePopUp(0);	
							//~ /************lineage CAll*************/
						//~ }
					}); 
				};
		
		$scope.setSearchBut	=	function(event,whichOne) {
			if(whichOne	==	1) {
				$scope.showSearchNorm	=	true;
				$('.divOneBord').css('border-color','#0d96d5 transparent transparent');
				$('.divTwoBord').css('border-color','transparent #FFFFFF #FFFFFF transparent');
				$('.divTwo').removeClass('backSelBlue');
				$('.divOne').addClass('backSelBlue');
				$('.divTwo').css('color','#1673d5');
				$('.divOne').css('color','#FFFFFF');
				$scope.isDisabled = true;
			} else {
				$scope.showSearchNorm	=	false;
				$('.divOne').removeClass('backSelBlue');
				$('.divOneBord').css('border-color','#FFFFFF transparent transparent');
				$('.divTwoBord').css('border-color','transparent #0d96d5 #0d96d5 transparent');
				$('.divTwo').addClass('backSelBlue');
				$('.divOne').css('color','#1673d5');
				$('.divTwo').css('color','#FFFFFF');
				$scope.isDisabled = false;
				$('.buttonSearch').attr('page','categorySearch');
			}
		};
		//~ $scope.disable_emp =['012435','006492','000003'];
		$scope.disable_emp =['012435','000003'];
		
		$scope.disable_arr =["10012409","10013063","10014088","10027134","10026992","10012595","10028028","10033024","10028035","10028037","10007826","10007826","10008833","10005656","10011777","10015838","10017003","10018336","10010256","10025701","10017682","10012015","10018855","10016580","10009564","10011137","10002029","10018003","10014264","10014843","10014267","10016115","10031590","10027061","10027060","10015744","10003036","10012167","10015718","10015766","10008951","10015204","10009264","10021282","10017891","10021267","10015558","10026063","10013625","10006000","10022630","10026885","10029752","10013117","10033119","10033093","10033116","10033107","10028525","10019628","10020796","10021855","10024897","10029813","10031744","10017230","10029855","10022230","10024097","10028387","10033944","10033942","10034253","10034250","10034259","10007825"];
		
		// Method to show data popup
		$scope.showDataMode	=	function(event,popMode,$parentid,$companyName) {
			$scope.whichPop	=	popMode;
			$scope.compNamePop	=	$companyName;
			var divPos	=	$(event.target).closest('.panelDiv').offset().top;
			var divPosHeight	=	$(event.target).closest('.panelDiv').outerHeight();
			var lengthAllDiv	=	$('.panelDiv').length;
			$('.setPopupBot').hide();
			var finDivPos	=	$(event.target).closest('.panelDiv').position().top;
			if($(event.target).closest('.panelDiv').css('top') != '0px'	) {
				$('.setPopupBot').css('top',((divPos-$('.setPopupBot').outerHeight())+$('.panelDiv').outerHeight()+7)+'px');
				$('.setPopupBot').css('left',($('.panelDiv').offset().left+'px'));
			} else {
				$('.setPopupBot').css('top',(divPos+$('.panelDiv').outerHeight()+7)+'px');
				$('.setPopupBot').css('left',($('.panelDiv').offset().left+'px'));
			}
			$('.setPopupBot').show();
			$('.panelDiv').each(function(i,val) {
				if($(this).offset().top > divPos) {
					$(this).animate({top:$('.setPopupBot').outerHeight()+'px'},300);
				} else {
					$(this).animate({top:'0px'},300);
				}
			});
			$('body,html').animate({scrollTop:($('.setPopupBot').offset().top - 100)},300);
			$scope.contractid	=	$parentid;
			switch($scope.whichPop) {
				case 1:
					$scope.showBalance();
				break;
				case 8:
					$scope.getHistory($parentid,$companyName);
				break;  
			}
		};
		
		// Method to show data popup
		$scope.showDataModeTab	=	function(event,popMode,$parentid,$companyName,$index) {
			$scope.whichPop	=	popMode;
			$scope.compNamePop	=	$companyName;
			$scope.formatNumber = function(i) {
				return (Math.round(i * 100)/100).toFixed(2); 
			}
			var divPos	=	$(event.target).closest('.tab-row').offset().top;
			var divPosHeight	=	$(event.target).closest('.tab-row').outerHeight();
			var lengthAllDiv	=	$('.tab-row').length;
			$('.setPopupBot').hide();
			if(($cookieStore.get('currLink')	==	'.inventoryMorethanFifty' || $cookieStore.get('currLink')	==	'.inventoryData') && ($scope.whichPop	==	11 || $scope.whichPop	==	12)){
				$('.setPopupBot').css({'background':'white','color':'black'});
				$('.closePop').css({'margin-top':'20px !important'});
			}else{
				$('.setPopupBot').css({'background':'black','color':'white'});
			}
			var finDivPos	=	$(event.target).closest('.tab-row').position().top;
			if($(event.target).closest('.tab-row').css('top') != '0px'	) {
				$('.setPopupBot').css('top',((divPos-$('.setPopupBot').outerHeight())+$('.tab-row').outerHeight())+'px');
				$('.setPopupBot').css('left',($('.tab-row').offset().left+'px'));
			} else {
				$('.setPopupBot').css('top',(divPos+$('.tab-row').outerHeight())+'px');
				$('.setPopupBot').css('left',($('.tab-row').offset().left+'px'));
			}
			$('.setPopupBot').show();
			$('.tab-row').each(function(i,val) {
				if($(this).offset().top > divPos) {
					$(this).animate({top:$('.setPopupBot').outerHeight()+'px'},300);
				} else {
					$(this).animate({top:'0px'},300);
				}
			});
			$('body,html').animate({scrollTop:($('.setPopupBot').offset().top - 200)},300);
			$scope.contractid	=	$parentid;
			$scope.currIndexPop	=	$index;	
			switch($scope.whichPop) {
				case 1:
					$scope.showBalance();
				break;
				case 2:
					$scope.showCategories();
				break;
				case 5:
				   $rootScope.tmeCommentBoxOpen(event,$parentid,$index);
				break;
				case 8:
					$scope.getHistory($parentid,$companyName);
				break;
				case 11:
					$scope.fetchContractInventory($parentid,$companyName);
				break;
				case 12:
					$scope.fetchContractinventoryMorethanFifty($parentid,$companyName);
				break;
			}
		};

		// Method to close data popup
		$scope.closePopMid	=	function(viewParam) {
			var i = 1;
			if(viewParam	==	1) {
				var lengthAllDiv	=	$('.panelDiv').length;
				$('.panelDiv').each(function(i,val) {
					$(this).animate({top:'0px'},300);
					i++;
					if(i	==	lengthAllDiv) {
						$('.setPopupBot').slideUp(300);
					}
				});
			} else {
				var lengthAllDiv	=	$('.tab-row').length;
				$('.tab-row').each(function(i,val) {
					$(this).animate({top:'0px'},300);
					i++;
					if(i	==	lengthAllDiv) {
						$('.setPopupBot').slideUp(300);
					}
				});
			}
		};
		
		
		
		
		$scope.getHistory  = function(parentid,compname) {
			$scope.compNamePop = compname;
			APIServices.getHistory(parentid,compname).success(function (response) {
				$scope.history_list = response['history'];
			});
		};
		
		$scope.fetchContractInventory  = function(parentid,companyname) {
			$scope.compNamePop = companyname;
			APIServices.fetchContractInventory(parentid,companyname).success(function (response) {
				if(response.errorCode	==	0){
					$scope.inventory_info	=	response['data'];
					$scope.pincodes			=	response['pincodes_val'];
					
					angular.forEach($scope.inventory_info,function(value,key) {
						angular.forEach(value.Category,function(value1,key1) {
							angular.forEach(value1,function(value2,key2) {
								angular.forEach($scope.pincodes,function(pin1,pinval1) {
									
								});
								
							});
						});
					});
				}else{
					
				}
			});
			
		};
		
		$scope.fetchContractinventoryMorethanFifty  = function(parentid,companyname) {
			$scope.compNamePop = companyname;
			APIServices.fetchContractinventoryMorethanFifty(parentid,companyname).success(function (response) {
				if(response.errorCode	==	0){
					$scope.inventory_info	=	response['data'];
					$scope.pincodes			=	response['pincodes_val'];
					
					angular.forEach($scope.inventory_info,function(value,key) {
						angular.forEach(value.Category,function(value1,key1) {
							angular.forEach(value1,function(value2,key2) {
								angular.forEach($scope.pincodes,function(pin1,pinval1) {
									
								});
								
							});
						});
					});
				}else{
					
				}
			});
			
		};
		
		// Method to show balance for the contract
		$scope.showBalance	=	function() {
			$scope.balanceData	=	[];
			APIServices.getBalanceData($scope.contractid).success(function (response) {	
				$scope.balanceData	=	response;
				var sumDataFirst	=	0;
				if(response.firstRow.errorCode	==	0) {
					sumDataFirst	=	parseFloat(response.firstRow.data.surplusAmount);
				}
				
				var sumDataSec	=	0;
				if(response.partSec.errorCode	==	0) {
					for(var i=0; i< response.partSec.data.length; i++) {
						if(response.partSec.data[i]['balance']['numRows'] > 0 && parseFloat(response.partSec.data[i]['balance'][0]) > 0) {
							sumDataSec	+=	parseFloat(response.partSec.data[i]['balance'][0]);
						}
					}
				}
				$scope.part1Total	=	sumDataFirst;
				$scope.part2Total	=	sumDataSec;
				$scope.totalbalance	=	sumDataFirst+sumDataSec;
			});
		};
		
		$scope.showCategories	=	function() {
			$scope.categories	=	[];
			APIServices.getCategories($scope.contractid).success(function (response) {
				$scope.categories	=	response;
			});
		};
		
		$rootScope.findSMSEmailVal	=	1;
		$scope.setSendSMSBut	=	function(event,whichOne) {
			if(whichOne	==	1) {
				$rootScope.findSMSEmailVal	=	1;
				$('.emailDivSelIn').css('border-color','#0d96d5 transparent transparent');
				$('.smsDivSelIn').css('border-color','transparent #FFFFFF #FFFFFF transparent');
				$('.smsDivSel').removeClass('backSelBlue');
				$('.emailDivSel').addClass('backSelBlue');
				$('.smsDivSel').css('color','#1673d5');
				$('.emailDivSel').css('color','#FFFFFF');
				$scope.isDisabled = true;
			} else {
				$rootScope.findSMSEmailVal	=	0;
				$('.emailDivSel').removeClass('backSelBlue');
				$('.emailDivSelIn').css('border-color','#FFFFFF transparent transparent');
				$('.smsDivSelIn').css('border-color','transparent #0d96d5 #0d96d5 transparent');
				$('.smsDivSel').addClass('backSelBlue');
				$('.emailDivSel').css('color','#1673d5');
				$('.smsDivSel').css('color','#FFFFFF');
				$scope.isDisabled = false;
				$('.buttonSearch').attr('page','categorySearch');
			}
		};
		
		$scope.autocompleteModel = null;
		$scope.autocompleteModelId = null;
		$scope.isDisabled = true;
		$rootScope.hidereport = false;
		//Function used to take show the search results
		$scope.searchBut	=	function(event) {
			//~ if(DATACITY == ""){
				//~ alert("Search Functionality is blocked for now."); return false;
			//~ }else{
				var $targetClick	=	$(event.target).attr('parId');
				var $targetClickPage	=	$(event.target).attr('page'); 
				if($(event.target).attr('page')	==	'categorySearch') {
					$state.go('appHome.search',{parid:$('.searchText').val(),currPage:$targetClickPage});
				} else {
					$state.go('appHome.search',{parid:$targetClick,currPage:$targetClickPage});
				}
			//~ }
		};
		
		
		$scope.showPageNum	=	50;
		
		//currPageReport
		$scope.user	=	$rootScope.user;
		
		$rootScope.donotOpen	=	function() {
			alert('This contract is a paid contract. You are not allowed to open this contract');
			return false;
		};
		
		$scope.divInnerShow	=	0;	//Scope Variable used to show which partial is going to be shown inside the popup
		
		//Scope Variable to show Popup Bar
		$scope.showPopTop	=	function() {
			$('html, body').animate({scrollTop:0},1000);
			$('.popTopBar').slideDown("slow",function(){});
		};
		
		//Scope Variable to close Popup Bar
		//~ $scope.closePopTop	=	function() {
			//~ $('.popTopBar').slideUp("slow",function(){});
		//~ };
		
		$scope.showBroadPopDiv	=	0;
		$scope.closePopTop	=	function(val) {
			
			var divLen = $('.innerPopDiv').length;
			if(divLen >=1) {
				if(val == 1) {
					$('.callBackTab').closest('.innerPopDiv').slideUp("slow",function(){
						$('.callBackTab').closest('.innerPopDiv').html("");
						$scope.divInnerShow = 0;
						$scope.$apply();
						if($('.broadcastTab').length == 0){
							$('.popTopBar').slideUp("slow",function(){});
						}
						
					});
				} else if(val == 2){
					
					APIServices.EmpMessageUpdates($rootScope.empCode,$rootScope.autoId).success(function(response) { 
						//$scope.Messageupdate = response;
					});	
					
					$('.broadcastTab').closest('.innerPopDiv').slideUp("slow",function(){
						$scope.showBroadPopDiv	=	1;
						$scope.$apply();
						if($('.callBackTab').length == 0){
							$('.popTopBar').slideUp("slow",function(){});
						}
					});
				}
			} else {
				$('.popTopBar').slideUp("slow",function(){});
			}
		};
		
		
		//Method used for handlink Callback Popup
		var callBackPopup	=	function() {
			APIServices.fetchCallBackPopData().success(function(response) {
				$rootScope.spinner	=	false;
				if(response.errorCode	==	0) {
					$scope.divInnerShow	=	1;
					$scope.showPopTop();
					$scope.callBackData	=	response.data;
				} else {
					$scope.closePopTop(1);
					$scope.divInnerShow = 0;
				}
			});
			var timer	=	setTimeout(callBackPopup, 45000);
			$scope.$on("$destroy", function() {
				if (timer) {
					$timeout.cancel(timer);
				}
			});
		};
		callBackPopup();
		
		$rootScope.setArrowPos	=	0;
		$rootScope.setArrowPosPage	=	0;
		$scope.setNewIcon	=	0;
		$(window).resize(function(){
			//console.log(window.innerWidth);
			$scope.$apply(function() {
				$rootScope.setSmallMenu();
				$rootScope.setArrowDisp();
				$rootScope.setArrowDispPage();
				$scope.setwidthList();
			});
		});
		
		$scope.setwidthList	=	function() {
			var setWidth	=	0;
			setTimeout(function() {
				$('.headFont').each(function() {
					setWidth	=	setWidth	+	$(this).outerWidth();
				});
				$('<style>.tab-row{width:'+(setWidth+100)+'px;}</style>').appendTo('head');
				$('.rotateRight').animate({left:0},200);
			},1000);
		};
		$scope.setwidthList();
		
		$scope.setPageShiftLeft	=	function() {
			var showLeftPos	=	$('.bodyTable').outerWidth() - $('.widthFree').outerWidth();
			if(Math.abs(parseInt($('.rotateRight').css("left").slice(0,-2)) -(showLeftPos-73)) < $('.rotateRight').outerWidth()) {
				$('.rotateRight').animate({left:parseInt($('.rotateRight').css("left").slice(0,-2))-(showLeftPos-73)},200);
			}
		};
		
		$scope.setPageShiftRight	=	function() {
			if(parseInt($('.rotateRight').css("left").slice(0,-2)) != 0) {
				var showLeftPos	=	$('.bodyTable').outerWidth() - $('.widthFree').outerWidth();
				$('.rotateRight').animate({left:parseInt($('.rotateRight').css("left").slice(0,-2))+(showLeftPos-73)},200);
			}
		};
		
		$scope.remindLater	=	function(allocId,event) {
			APIServices.remindLater(allocId).success(function(response) {
				if(response.results.errorCode	==	0) {
					$(event.target).closest('.callBackcards').slideUp("slow",function() {$(this).remove()});
					if($('.callBackcards').length	==	1) {
						$scope.closePopTop(1);
					}
				}
			});
		};
		
		$scope.removeAll	=	function(allocId,event) {
			APIServices.removeAll(allocId).success(function(response) {
				if(response.results.errorCode	==	0) {
					$(event.target).closest('.callBackcards').slideUp("slow",function() {$(this).remove()});
					if($('.callBackcards').length	==	1) {
						$scope.closePopTop(1);
					}
				}
			});
		}
		
		$rootScope.setSmallMenu	=	function() {
			if(window.innerWidth < 939) {
				$scope.setNewIcon	=	1;
			} else {
				$scope.setNewIcon	=	0;
			}
			
			if(window.innerWidth < 500) {
				$scope.setNewLogo	=	1;
			} else {
				$scope.setNewLogo	=	0;
			}
		};
		
		//Function used to set arrow position of the slider based on content width
		$rootScope.setArrowDisp	=	function() {
			var navWidthOut	=	$('.navSlider').outerWidth();
			var navWidthIn	=	$('.navFilters').outerWidth();
			
			if(navWidthOut < navWidthIn) {
				$rootScope.setArrowPos	=	1;
			} else {
				$rootScope.setArrowPos	=	0;
				$('.navFilters').closest('.slideIndi').find('.repPop').animate({left:'0px'});
			}
		};
		
		//Function used to show arrows for pagination
		$rootScope.setArrowDispPage	=	function() {
			var widthPosLi	=	0;
			$('.pageLi').each(function(){
				widthPosLi	=	parseInt(widthPosLi) + $(this).outerWidth();
			});
			var navWidthOut	=	$('.pageSlider').outerWidth();
			if(navWidthOut < widthPosLi) {
				$rootScope.setArrowPosPage	=	1;
			} else {
				$rootScope.setArrowPosPage	=	0;
				$('.navFilters').closest('.slideIndi').find('.repPop').animate({left:'0px'});
			}
		};
		
		//Function is used to show menu on mouseover of Menu Link
		$scope.showMenu	=	function() {
			var $i=0;
			if($i==0) {
				$('.menulinks').removeClass('hide');
				var heightSet	=	$('.floatSide').height();
				$('.menulinks').addClass('hide');
			}
			$('.menulinks').removeClass('hide');
			var $windowHeight	=	$(window).height();
			if($windowHeight < (heightSet+65)) {
				$('.floatSide').css('height',($windowHeight-65)+'px');
			} else {
				$('.floatSide').css('height',(heightSet)+'px');
			}
			$i++;
		}
		
		//Method used to set Sub Menu
		$scope.hideMenu	=	"hide";
		var navIn;
		var innerSub;
		var addtoNav;
		var subtoNav;
		$scope.showSubMenu	=	function(event) {	
			if($(event.target).find('ul').hasClass('hide')) {	//alert('if...44444444444444444444');
				if($(event.target).hasClass('lastLiTop')){		//alert('3333333333333');	submenuUl
					navIn		=  $('.navFilters').innerWidth();	
					innerSub	=  $(event.target).find('.submenuUl').innerWidth(); 
					addtoNav	=  navIn + innerSub;	//alert(addtoNav+90);
					$('.navSlider .navFilters').css('width',(addtoNav+90) + "px");
				} else if(!$(event.target).closest('ul').find('.lastLiTop').find('.submenuUl').hasClass('hide')) {	//alert('else if...2222222222222');
					navIn		=  $('.navFilters').innerWidth();
					subtoNav 	=  navIn - innerSub;	//alert(addtoNav-90);
					$('.navSlider .navFilters').css('width',(subtoNav-90) + "px");
				}	//alert('5555555555555555555');
				$('.submenuUl').addClass('hide');
				$(event.target).find('.submenuUl').removeClass('hide');
			} else {	//alert('else...');
				if($(event.target).hasClass('lastLiTop')){	//alert('1111111111');
					navIn		=  $('.navFilters').innerWidth();
					subtoNav 	=  navIn - innerSub;
					$('.navSlider .navFilters').css('width',(subtoNav-50) + "px");
				}	//alert('666666');
				$(event.target).find('.submenuUl').addClass('hide');
			}
		};
		
		$scope.expiredSub_menu	=	function(event) {
			$('.mainUlDiv').hide();
			if ($(event.target).is(":checked"))
				$(event.target).closest('.exprd').find('.campaign').show();
			else
				$(event.target).closest('.exprd').find('.campaign').hide();
				
			if ($(event.target).is(":checked"))
				$(event.target).closest('.exprd_likely').find('.campaignlikely').show();
			else
				$(event.target).closest('.exprd_likely').find('.campaignlikely').hide();
				
			if ($(event.target).is(":checked"))
				$(event.target).closest('.groupid_div').find('.groupid').show();
			else
				$(event.target).closest('.groupid_div').find('.groupid').hide();
				
			if ($(event.target).is(":checked"))
				$(event.target).closest('.callcnt_div').find('.callcnt').show();
			else
				$(event.target).closest('.callcnt_div').find('.callcnt').hide();		
			
			if ($(event.target).is(":checked"))
				$(event.target).closest('.createdon_div').find('.createdon').show();
			else
				$(event.target).closest('.createdon_div').find('.createdon').hide();
			
			if ($(event.target).is(":checked"))
				$(event.target).closest('.Updatedon_div').find('.Updated_on').show();
			else
				$(event.target).closest('.Updatedon_div').find('.Updated_on').hide();		
					
				
		};
		
		
		//Method used to redirect contract to bform -- For Grab
		$rootScope.grabGoToBform	=	function($parentid){
			APIServices.getTempStatus($parentid).success(function(response){
				window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+$parentid+"&flgSrc=2&flgAfterCall=0&hotData=1&flgPaid=1";
			});
		};
		
		
		//Method used to redirect contract to bform -- will be changed in future
		$rootScope.goToBform = function($contractInfo) {

			APIServices.udateusereditdata($contractInfo.contractid).success(function(response){


				$cookieStore.put('showlearn',1);
				var checkTemp	=	APIServices.getTempStatus($contractInfo.contractid).success(function(response){
					var loc	=	'';
					if(response.count	==	1) {
						var flgSrc	=	2;
					} else {
						var flgSrc	=	1;
					}
					if($contractInfo.paidstatus == 1) {
						var flgPaid	=	1;
					} else {
						var flgPaid	=	0;
					}
					if(flgSrc == 1 && flgPaid != '*'){
						loc = "&flgPaid="+flgPaid; 
					}
					if(parseInt(flgPaid) == 0) {
						loc = "&convert=1";
					}
					// check status of downsel
					$rootScope.downsellFlag	=	0;
					$scope.downStatus		=	'';
					APIServices.getversion($contractInfo.contractid,DATACITY).success(function(responsever) {
						$rootScope.downselversion    =   responsever.version;
						APIServices.checkDiscount($contractInfo.contractid,responsever.version).success(function(responsedis) {
								if(responsedis.status	==	0){
									$scope.downStatus	=	'Pending';
								}else{
									$scope.downStatus	=	'Approved';
								}
								if(responsedis.error == 1) {
								$rootScope.downsellFlag=	1;
								alert(responsedis.message);
								}else{
									if(($contractInfo.BD ==null) || ($contractInfo.BD == undefined)){// check downsel request is present if so block them.
										
										//if($scope.chk_city == 'ahmedabad')
											window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+$contractInfo.contractid+"&flgSrc="+flgSrc+"&flgAfterCall=0&hotData=1"+loc;
										/*else
											window.location = "../tmAlloc/mktgGetContData.php?parentid="+$contractInfo.contractid+"&flgSrc="+flgSrc+"&flgAfterCall=0&hotData=1"+loc;*/
											
									}else{// check downsel request is present if so block them.
										
										//if($scope.chk_city == 'ahmedabad')
											window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+$contractInfo.contractid+"&flgSrc="+flgSrc+"&flgAfterCall=0&BD=1&hotData=1"+loc;
										/*else
											window.location = "../tmAlloc/mktgGetContData.php?parentid="+$contractInfo.contractid+"&flgSrc="+flgSrc+"&flgAfterCall=0&BD=1&hotData=1"+loc;*/
									}
								}
						});
					});
				});

			})

			

			
		};
		$rootScope.goToBform_jdrr = function($contractInfo) {
			$cookieStore.put('showlearn',1);
			
			// Call The API to update read_flag to 1			
			APIServices.updateRdFlg($contractInfo.parentid,DATACITY,$contractInfo.empcode).success(function(resp){
				console.log(resp);
			});
			
			var checkTemp	=	APIServices.getTempStatus($contractInfo.parentid).success(function(response){
				var loc	=	'';
				if(response.count	==	1) {
					var flgSrc	=	2;
				} else {
					var flgSrc	=	1;
				}
				if($contractInfo.paidstatus == 1) {
					var flgPaid	=	1;
				} else {
					var flgPaid	=	0;
				}
				if(flgSrc == 1 && flgPaid != '*'){
					loc = "&flgPaid="+flgPaid; 
				}
				if(parseInt(flgPaid) == 0) {
					loc = "&convert=1";
				}
				// check status of downsel
				$rootScope.downsellFlag	=	0;
				$scope.downStatus		=	'';
				APIServices.getversion($contractInfo.parentid,DATACITY).success(function(responsever) {
					$rootScope.downselversion    =   responsever.version;
					APIServices.checkDiscount($contractInfo.parentid,responsever.version).success(function(responsedis) {
							if(responsedis.status	==	0){
								$scope.downStatus	=	'Pending';
							}else{
								$scope.downStatus	=	'Approved';
							}
							if(responsedis.error == 1 ) {
								$rootScope.downsellFlag=	1;
								alert(responsedis.message);
							}else{
								if(($contractInfo.BD ==null) || ($contractInfo.BD == undefined)){// check downsel request is present if so block them.
									
									//if($scope.chk_city == 'ahmedabad')
										window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+$contractInfo.parentid+"&flgSrc="+flgSrc+"&flgAfterCall=0&hotData=1"+loc;
									/*else
										window.location = "../tmAlloc/mktgGetContData.php?parentid="+$contractInfo.parentid+"&flgSrc="+flgSrc+"&flgAfterCall=0&hotData=1"+loc;*/
										
								}else{// check downsel request is present if so block them.
									
									//if($scope.chk_city == 'ahmedabad')
										window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+$contractInfo.parentid+"&flgSrc="+flgSrc+"&flgAfterCall=0&BD=1&hotData=1"+loc;
									/*else
										window.location = "../tmAlloc/mktgGetContData.php?parentid="+$contractInfo.parentid+"&flgSrc="+flgSrc+"&flgAfterCall=0&BD=1&hotData=1"+loc;*/
								}
							}
					});
				});
			});
		};
		$rootScope.subECSTrackRep	=	function($contractid,empcode,flag) {
			$scope.parentIdTrackRep	=	$contractid;
			$scope.flagTrackRep		=	1;
			$scope.empCodeTrackRep	=	empcode;
			if(flag	==	1) {
				var timer	=	setTimeout(function() { $('.trackRepEcs').submit();},500);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			} else if(flag	==	2) {
				var timer	=	setTimeout(function() { $('.trackRepSI').submit();},500);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			}
		};
		
		$rootScope.gotToBformCallBack	=	function(parentid,flgSrc,flgAfterCall,flgPaid,nonpaidFlag) {
			var loc	=	'';
			if(flgSrc == 1 && flgPaid != '*'){ 
				loc += "&flgPaid="+flgPaid; 
			}
			if(parseInt(nonpaidFlag) == 1) {
				loc += "&convert=1";
			}
			window.location = "../tmAlloc/mktgGetContDataNew.php?parentid="+parentid+"&flgSrc="+flgSrc+"&flgAfterCall="+flgAfterCall+"&hotData=1"+loc;
		};
		
		//Function is used to slide the small sliders left
		$rootScope.slideleft	=	function(event){
			var leftPos	=	$(event.target).closest('.slideIndi').find('.repPop').css('left').slice(0,-2);
			var widthAll	=	'';
			var widthCurrent='';
			widthAll	=	$(event.target).closest('.slideIndi').find('.repPop').width();
			widthCurrent=	widthAll-200;
			if(leftPos>-widthCurrent) {
				$(event.target).closest('.slideIndi').find('.repPop').animate({left:(parseInt(leftPos)-100)+'px'},200);
			}
		};
		
		//Function is used to slide the small sliders right
		$rootScope.slideright	=	function(event){
			var leftPos	=	$(event.target).closest('.slideIndi').find('.repPop').css('left').slice(0,-2);
			if(leftPos < 0) {
				$(event.target).closest('.slideIndi').find('.repPop').animate({left:(parseInt(leftPos)+100)+'px'},200);
			}
		};
		
		//Function is used to slide the page slider right
		$rootScope.slideleftPage	=	function(event) {
			var leftPos	=	$(event.target).closest('.slideIndi').find('.repPop').css('left').slice(0,-2);
			var widthAll	=	'';
			var widthCurrent='';
			widthAll	=	$(event.target).closest('.slideIndi').find('.repPop').width();
			widthCurrent=	widthAll-parseInt($('.pageLi').outerWidth());
			if(leftPos>-widthCurrent) {
				$(event.target).closest('.slideIndi').find('.repPop').animate({left:(parseInt(leftPos)-parseInt($('.pageLi').outerWidth()))+'px'},200);
			}
		};
		
		//Function is used to slide the page slider left
		$rootScope.sliderRightPage	=	function(event) {
			var leftPos	=	$(event.target).closest('.slideIndi').find('.repPop').css('left').slice(0,-2);
			if(leftPos < 0) {
				$(event.target).closest('.slideIndi').find('.repPop').animate({left:(parseInt(leftPos)+parseInt($('.pageLi').outerWidth()))+'px'},200);
			}
		};
		
		//Function is used to show categories in overlay
		$rootScope.showCats	=	function(event,$parentid){ 
			$scope.catpaidcurrlink		=	$cookieStore.get('currLink');
			
			$('.catupeven').addClass('hide');
			$(event.target).closest('.contDivs').find('.catupeven').removeClass('hide');
			$(event.target).closest('.contDivs').find('.loadingMini').removeClass('hide');
			$scope.categories	=	[];
			if($scope.catpaidcurrlink == '.categoryPaidData'){	
				APIServices.getCategoriespaid($parentid).success(function (response) {
					$scope.categories	=	response;
					$(event.target).closest('.contDivs').find('.loadingMini').addClass('hide');
				});
			}else{ 
				APIServices.getCategories($parentid).success(function (response) {
					$scope.categories	=	response;
					$(event.target).closest('.contDivs').find('.loadingMini').addClass('hide');
				});
			}	
		};
		
		//RootScope Function used for Checking Tracker Report Options
		$rootScope.checkTrackerRep	=	function(event,$parentid) {
			$('.trackeroverlay').addClass('hide');
			$(event.target).closest('.contDivs').find('.trackeroverlay').removeClass('hide');
			$(event.target).closest('.contDivs').find('.loadingMini').removeClass('hide');
			APIServices.checkTrackerRep($parentid).success(function (response) {
				$scope.trackRepChk	=	response;
				$(event.target).closest('.contDivs').find('.loadingMini').addClass('hide');
			});
		};
		
		$scope.showActive= function(event) {
			$('.tabDivRep ul li').removeClass('selectedTab');
			$(event.target).addClass('selectedTab'); 
		};
		
		//Function used for closing indi divs overlays
		$scope.closeOverlay	=	function(event) {
			$(event.target).closest('.contDivs').find('.coverOverlay').addClass('hide');
		};
		
		$scope.moveToTop	=	function(event) {	
			$('html, body').animate({scrollTop: 0}, 500);
		};
		
		//Function to Show Background Image of text box
		$scope.showIcon		=	function(event) {		
			$(event.target).find('.SearchCompany').css('background-image','url("../img/blackCross.jpeg")');//.show();
		};
		
		$scope.showCompleteMenu	=	function(event) {
			if($(event.target).find('.nameContainLink').text().length	> 14) {
				$(event.target).closest('li').attr('title',$(event.target).find('.nameContainLink').text());
			}
		}
		
		if(typeof $cookieStore.get("viewParam") === 'undefined') {
			$scope.viewParam	=	1; //View Param for Grid View
		} else {
			$scope.viewParam	=	$cookieStore.get("viewParam"); //View Param for Grid View
		}
		
		$scope.changeView	=	function(event,eventParam) {
	
			if(eventParam	==	1) {
				$scope.viewParam	=	1;
				$scope.closePopMid(1);
				$cookieStore.put("viewParam",1);
			} else if(eventParam	==	2) {
				$scope.viewParam	=	2;
				$scope.setwidthList();
				$scope.closePopMid(2);
				$cookieStore.put("viewParam",2);
			}
		};
		
		//ecs_upgrade_degrade requests//
		$scope.send_ecs_request	=	function(event,parentid,eventParam,acc_rej_flag,empcode) {
			$scope.ecs_hours	=	'';
			
						if(acc_rej_flag == 1)
							{
								var ecs_flag = 'Upgrade Request';
								var upd = 'Upgraded';
							}
							else
							{
								var ecs_flag = 'Degrade Request';
								var upd = 'Degraded';
							}
							
							if(eventParam	==	1) {
							if(confirm('Are you Sure you want to Accept '+ecs_flag+'!!')) {     
								APIServices.send_mngr_request(parentid,eventParam,empcode).success(function(response){
								if(response.errorCode == 0)
								{
									alert(ecs_flag+' Accepted Successfully !!');
									$(event.target).closest('.contDivs').addClass('hide');
									return false;
								}
								else
								{
									alert('Process Failed !!');
									return false;
								}
								//~ $rootScope.vc_flag =  response.vc_flag;
							
							});
							}		//~ $cookieStore.put("viewParam",1);
						}
						else if(eventParam	==	0)
						{
							if(confirm('Are you Sure you want to Reject '+ecs_flag+'!!')) {     
								APIServices.send_mngr_request(parentid,eventParam,empcode).success(function(response){
									if(response.errorCode == 0)
									{
										alert('Rejected Successfully !!');
										$(event.target).closest('.contDivs').addClass('hide');
										return false;
									}
									else
									{
										alert('Process Failed !!');
										return false;
									}
									//~ $rootScope.vc_flag =  response.vc_flag;
						
								});
							}
						}
						else
						{
							return false;
						} 
		};
		//ecs_upgrade_degrade requests//
		
	
		$scope.dispose_list = false;
		$scope.dispname ='Select Disposition';
		$scope.getDispositionList = function() {
			$scope.dispose_list = true;
			$scope.close_overlay = true;

			APIServices.checkvccontract($rootScope.companyTempInfo.data.parentid).success(function(response){
				$rootScope.vc_flag =  response.vc_flag;
			}); 

			APIServices.getDispositionList($rootScope.employees.results.allocId,$rootScope.employees.results.secondary_allocID).success(function(response) {
				if(response.errorCode == 0) {
					$rootScope.disposelist =response.data; 
				}
			});
		}
		
		$scope.gotolearn = function(){
			$state.go('appHome.learningcenter',{});
		};
		
		$scope.gototmecallrecords = function(){
			
			$state.go('appHome.tmecallrecords',{});
		};
                
		$scope.getkpi = function(){
			
			$state.go('appHome.kpi',{});
		};
		
		$scope.notSorted = function(obj){
            if (!obj) {
                return [];
            }
            return Object.keys(obj);
        }
        
	
		$scope.setdispval = function(name,val) {
			$scope.dispname = name;
			$rootScope.dispose =val;	
			$scope.dispose_list = false;
			$scope.close_overlay = false;
		}
		
		$scope.closedisplist = function(){
			$scope.dispose_list = false;
			$scope.close_overlay = false;
		}
		
		
		$scope.saveDispose	=	function(event){
			if($rootScope.companyTempInfo == undefined){
				$rootScope.timer_parentid  = 'PXX888888';
			}else{
				$rootScope.timer_parentid		=	$rootScope.companyTempInfo.data.parentid;
			}
			if($rootScope.dispose	!=	'25' && $rootScope.dispose	!=	'22' && $rootScope.dispose	!=	'24' && $rootScope.dispose	!=	'99' && $rootScope.dispose	!=	'124' && $rootScope.dispose	!=	'317'){
				APIServices.stopTimer($rootScope.dispose,$rootScope.timer_parentid,UNAME).success(function(response) {
					$scope.callDispose(event);
				});
			}else{
				$scope.callDispose(event);
			}
		}
		
		
		$scope.callDispose	=	function(event){
			if($rootScope.companyTempInfo == undefined){
				$rootScope.timer_parentid  = 'PXX888888';
			}else{
				$rootScope.timer_parentid		=	$rootScope.companyTempInfo.data.parentid;
			}
			if($rootScope.vc_flag == 1) {
				APIServices.checkvccondition($rootScope.timer_parentid).success(function(response){
					if(response.category_count == 0) {
						$mdToast.show(
							$mdToast.simple()
							.content('Visiting card contract must have category')
							.position('top left')
							.hideDelay(4000)
						);
						return false;
					}else if(response.land_count == 0 && response.mob_count == 0) {
						$mdToast.show(
							$mdToast.simple()
							.content('Visiting card contract must have atleast one phone number')
							.position('top left')
							.hideDelay(4000)
						);
						return false;
					}else if(response.pincode == '' || response.pincode == null ) {
						$mdToast.show(
							$mdToast.simple()
							.content('Visiting card contract must have pincode please check in bform')
							.position('top left')
							.hideDelay(4000)
						);
						return false;
					}else if(response.compname == '' || response.compname == null ) {
						$mdToast.show(
							$mdToast.simple()
							.content('Visiting card contract must have company name please check in bform')
							.position('top left')
							.hideDelay(4000)
						);
						return false;
					}

					if($scope.dispname == undefined || $scope.dispname == 'Select Disposition'){
						$mdToast.show(
							$mdToast.simple()
							.content('Please select a Disposition')
							.position('top left')
							.hideDelay(4000)
						);
						return false;
					}
					angular.forEach($rootScope.disposelist,function(value,key){
						angular.forEach(value,function(value1,key1){
							if(value1['disposition_value'] == $rootScope.dispose){
								$rootScope.redirectUrl = value1['redirect_url'];
							}
						});
					});
					
					APIServices.getMainTabGeneralData($rootScope.timer_parentid).success(function(response) {
						if(response.errorCode == 0 && response.data.paid == 1)
						{
							APIServices.compareBform($rootScope.timer_parentid).success(function(response) {
								if(response.errorCode == 1) {
									$scope.check(response);
									$mdDialog.show({
										controller: DialogDatacorrectionController,
										templateUrl: 'partials/dcdialog.html',
										parent: angular.element(document.body),
										targetEvent:event
									})
									.then(function(answer) {
										$scope.alert = 'You said the information was "' + answer + '".';
									}, function() {
										$scope.alert = 'You cancelled the dialog.';
									});
								}else {
									if($rootScope.redirectUrl == '') {
										$state.go('redirectDispose',{parentIdSt:$rootScope.timer_parentid,stVal:$rootScope.dispose});
									}else {
										$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.timer_parentid+"&stVal="+$rootScope.dispose; 
										window.location =$rootScope.redirectUrl;
									}
								}
							});
						}else {
							if($rootScope.redirectUrl == '') {
								$state.go('redirectDispose',{parentIdSt:$rootScope.timer_parentid,stVal:$rootScope.dispose});
							}else {
								$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.timer_parentid+"&stVal="+$rootScope.dispose; 
								window.location =$rootScope.redirectUrl;
							}
						}
					});

				}); 
			}else {

				if($scope.dispname == undefined || $scope.dispname == 'Select Disposition'){
					$mdToast.show(
						$mdToast.simple()
						.content('Please select a Disposition')
						.position('top left')
						.hideDelay(4000)
					);
					return false;
				}
				angular.forEach($rootScope.disposelist,function(value,key){
					angular.forEach(value,function(value1,key1){
						if(value1['disposition_value'] == $rootScope.dispose){
							$rootScope.redirectUrl = value1['redirect_url'];
						}
					});
				});
				
				APIServices.getMainTabGeneralData($rootScope.timer_parentid).success(function(response) {
					if(response.errorCode == 0 && response.data.paid == 1)
					{
						APIServices.compareBform($rootScope.timer_parentid).success(function(response) {
							if(response.errorCode == 1) {
								$scope.check(response);
								$mdDialog.show({
									controller: DialogDatacorrectionController,
									templateUrl: 'partials/dcdialog.html',
									parent: angular.element(document.body),
									targetEvent:event
								})
								.then(function(answer) {
									$scope.alert = 'You said the information was "' + answer + '".';
								}, function() {
									$scope.alert = 'You cancelled the dialog.';
								});
							}else {
								if($rootScope.redirectUrl == '') {
									$state.go('redirectDispose',{parentIdSt:$rootScope.timer_parentid,stVal:$rootScope.dispose});
								}else {
									$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.timer_parentid+"&stVal="+$rootScope.dispose; 
									window.location =$rootScope.redirectUrl;
								}
							}
						});
					}else {
						if($rootScope.redirectUrl == '') {
							$state.go('redirectDispose',{parentIdSt:$rootScope.timer_parentid,stVal:$rootScope.dispose});
						}else {
							$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.timer_parentid+"&stVal="+$rootScope.dispose; 
							window.location =$rootScope.redirectUrl;
						}
					}
				});
			}
		
		};
		
		
		

		//DC dialog controller
		function DialogDatacorrectionController($scope,$rootScope,$mdToast) {
			$scope.Dcdata = $rootScope.diffarr;
			$scope.dccheckmodel = [];
			
			setTimeout(function() {
				$('.rowComp').each(function() {
					if($(this).attr('compcolumn')	==	'area' || $(this).attr('compcolumn')	==	'city' || $(this).attr('compcolumn')	==	'state') {
						var $thisObj	=	$(this);
						$(this).closest('table').find('.rowComp').each(function() {
							if($(this).attr('compcolumn')	==	'pincode' || ($(this).attr('compcolumn')	==	'area' && $thisObj.attr('compcolumn') == 'city') || ($(this).attr('compcolumn')	==	'city' && $thisObj.attr('compcolumn') == 'state')) {
								$(this).find('.checkDCBform').prop('disabled','disabled');
							}
						});
					}
				});
			},300);
				
			
			$scope.checkDisable = function(event) {
				if($(event.target).closest('tr').attr('compcolumn')	==	'area' || $(event.target).closest('tr').attr('compcolumn')	==	'city' || $(event.target).closest('tr').attr('compcolumn')	==	'state') {
					var $thisObj	=	$(event.target);
					$(event.target).closest('table').find('.rowComp').each(function() {
						if($(this).attr('compcolumn')	==	'pincode' || $(this).attr('compcolumn')	==	'area' || $(this).attr('compcolumn')	==	'city') {
							if($thisObj.is(':checked')) {
								$(this).find('.checkDCBform').prop('checked','checked');
							} else {
								$(this).find('.checkDCBform').removeAttr('checked');
							}
						}
					});
				}
			};
			
			$scope.senddc = function() {
				if($('.checkDCBform:checked').length == 0) {
					$mdToast.show(
						$mdToast.simple()
						.content('Please check fields to send for Data Correction')
						.position('top right')
						.hideDelay(4000)
					);
					return false;
				}
				var confirmSendDC	=	confirm('Do you want to send these changes for data correction? Pls recheck to avoid penalty.');
				if(confirmSendDC) {
					var jsonArrSend = {};
					jsonArrSend['checkedData']	=	{};
					jsonArrSend['diffData']	=	{};
					$('.checkDCBform:checked').each(function(i,val) {
						var thisAttr	=	$(this).closest('tr').attr('compcolumn');
						jsonArrSend['checkedData'][thisAttr]	=	{};
						if(thisAttr	==	'fb_prefered_language' || thisAttr	==	'catidlineage') {
							jsonArrSend['checkedData'][thisAttr]['oldVal']	=	($(this).closest('tr').find('.oldVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.oldVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
							jsonArrSend['checkedData'][thisAttr]['newVal']	=	($(this).closest('tr').find('.newVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.newVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
						}else{
							jsonArrSend['checkedData'][thisAttr]['oldVal']	=	($(this).closest('tr').find('.oldVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.oldVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
							jsonArrSend['checkedData'][thisAttr]['newVal']	=	($(this).closest('tr').find('.newVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.newVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));	
						}
					});
					$('.checkDCBform').each(function(index,value) {
						var thisAttr	=	$(this).closest('tr').attr('compcolumn');
						jsonArrSend['diffData'][thisAttr]	=	{};
						if(thisAttr	==	'fb_prefered_language' || thisAttr	==	'catidlineage') {
							jsonArrSend['diffData'][thisAttr]['oldVal']	=	($(this).closest('tr').find('.oldVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.oldVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
							jsonArrSend['diffData'][thisAttr]['newVal']	=	($(this).closest('tr').find('.newVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.newVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
						}else{
							jsonArrSend['diffData'][thisAttr]['oldVal']	=	($(this).closest('tr').find('.oldVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.oldVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
							jsonArrSend['diffData'][thisAttr]['newVal']	=	($(this).closest('tr').find('.newVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.newVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
						}
					});
					jsonArrSend['parentid']	=	$rootScope.companyTempInfo.data.parentid;
					jsonArrSend['empcode']	=	USERID;
					jsonArrSend['flag']	=	1;
					jsonArrSend['data_city']	=	DATACITY;
					jsonArrSend['disposeVal']	= $rootScope.dispose;
					APIServices.insertLogBformDC(jsonArrSend).success(function(response) {
						if(response.results.errorCode	==	0) {
							if($rootScope.redirectUrl == '') {
								$state.go('redirectDispose',{parentIdSt:$rootScope.companyTempInfo.data.parentid,stVal:$rootScope.dispose});
							}else {
								$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.companyTempInfo.data.parentid+"&stVal="+$rootScope.dispose; 
								window.location =$rootScope.redirectUrl;
							}
						}
							
					});
				}else {
					return false;
				}
			};
			
			$scope.ignoredc = function() {
				var jsonArrSend = {};
				jsonArrSend['checkedData']	=	{};
				jsonArrSend['diffData']	=	{};
				
				$('.checkDCBform').each(function(index,value) {
					var thisAttr	=	$(this).closest('tr').attr('compcolumn');
					jsonArrSend['diffData'][thisAttr]	=	{};
					jsonArrSend['diffData'][thisAttr]['oldVal']	=	($(this).closest('tr').find('.oldVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.oldVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
					jsonArrSend['diffData'][thisAttr]['newVal']	=	($(this).closest('tr').find('.newVal').html()	==	'&nbsp;' ? '' : $.trim($(this).closest('tr').find('.newVal').html().replace(/[\u200B-\u200D\uFEFF]/g,'')));
				});
				jsonArrSend['parentid']	=	$rootScope.companyTempInfo.data.parentid;
				jsonArrSend['empcode']	=	USERID;
				jsonArrSend['flag']	=	2;
				jsonArrSend['data_city']	=	DATACITY;
				jsonArrSend['disposeVal']	= $rootScope.dispose;
				APIServices.insertLogBformDC(jsonArrSend).success(function(response) {
					if(response.results.errorCode	==	0) {
						if($rootScope.redirectUrl == '') {
							$state.go('redirectDispose',{parentIdSt:$rootScope.companyTempInfo.data.parentid,stVal:$rootScope.dispose});
						}else {
							$rootScope.redirectUrl = "../"+$rootScope.redirectUrl+"?parentIdSt="+$rootScope.companyTempInfo.data.parentid+"&stVal="+$rootScope.dispose; 
							window.location =$rootScope.redirectUrl;
						}
					}
				
				});
			}
			
		}
		
		$scope.check = function(returnValue) {
			$rootScope.diffarr =  {};
			angular.forEach(returnValue.data,function(val,i) {
				if(val.errorCode	==	2){
					$rootScope.diffarr[val.errorContain] = new Array;
					var j=0;
					angular.forEach(val.data,function(val2,i2) {
						if(val2.errorCode	==	1) {
							$rootScope.diffarr[val.errorContain][j] = new Array;
							if(i	==	'contactComp') {
								switch(i2) {
									case 'contact_person' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Contact Person';
									break;
									case 'landline' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Landline';
									break;
									case 'mobile' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Mobile';
									break;
									case 'email' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Email';
									break;
									case 'website' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Website';
									break;
									case 'social_media_url' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Social Media URL';
									break;
									case 'fax' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'FAX';
									break;
									case 'tollfree' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Tollfree';
									break;
									case 'othercity_number' :
										$rootScope.diffarr[val.errorContain][j]['heading']	=	'Other City Number';
									break;
								}
								if(val2.mainTabVal	===	null) {
									expPhoneMainStr[0]	=	'';
								} else {
									expPhoneMainStr	=	val2.mainTabVal.split('|^|');
								}
								expPhoneShadStr	=	val2.newVal.split('|^|');
								
								$rootScope.diffarr[val.errorContain][j]['oldval']	= (expPhoneMainStr[0] == '' || expPhoneMainStr[0] == null ? '' : expPhoneMainStr[0]+(expPhoneMainStr[1] === undefined || expPhoneMainStr[1] == '' ? '' : '-'+expPhoneMainStr[1]));
								$rootScope.diffarr[val.errorContain][j]['newval']	= (expPhoneShadStr[0] == '' || expPhoneShadStr[0] == null ? '' : expPhoneShadStr[0]+(expPhoneShadStr[1] === undefined || expPhoneMainStr[1] == '' ? '' : '-'+expPhoneShadStr[1]));
								$rootScope.diffarr[val.errorContain][j]['keyName']	= val2.keyName;
							}else {
								if(val2.keyName	==	'fb_prefered_language') {
									$rootScope.diffarr[val.errorContain][j]['heading'] =	"Preferred Language";
									$rootScope.diffarr[val.errorContain][j]['oldval'] = (val2.mainTabValDisplay == '' || val2.mainTabValDisplay == null ? '' : val2.mainTabValDisplay);
									$rootScope.diffarr[val.errorContain][j]['newval'] = (val2.newValDisplay == '' || val2.newValDisplay == null ? '' : val2.newValDisplay);
									$rootScope.diffarr[val.errorContain][j]['keyName']	= val2.keyName;
								}else{
									$rootScope.diffarr[val.errorContain][j]['heading'] = i2;
									$rootScope.diffarr[val.errorContain][j]['oldval'] = (val2.mainTabVal == '' || val2.mainTabVal == null ? '' : val2.mainTabVal);
									$rootScope.diffarr[val.errorContain][j]['newval'] = (val2.newVal == '' || val2.newVal == null ? '' : val2.newVal);
									$rootScope.diffarr[val.errorContain][j]['keyName']	= val2.keyName;
								}
							}
							j++;
						}
					});
				}
			});
		}
		$timeout(function(){
			if($cookieStore.get('showlearn')	==	undefined || $cookieStore.get('showlearn') == ''){
				$state.go('appHome.learningcenter',{});
				//$state.go('appHome.kpi',{});
			}
		},10);
	});
	
	//Controller defined for Allocation Page
	tmeModuleApp.controller('allocationController',function($scope,APIServices,returnState,$location,$cookieStore,$rootScope,$timeout,$mdDialog) {
		if($cookieStore.get('showlearn')== undefined || $cookieStore.get('showlearn') == ''){
                    return false;
		}
		$scope.allocContracts	=	[];
		$cookieStore.put('showpopup_once',0);
		var loadNumber	=	0;
		$scope.cityCode = cityCode;
		//function for onload pop ups
		$scope.onloadPopUps();
		//~ /************lineage CAll*************/
			//~ $scope.showlineagePopUp(0);	//show only on contractcity page
		//~ /************lineage CAll*************/
		$scope.showdeclarationpopup = false;
		$scope.showloyaltypop = false;
		
		
		

		APIServices.checkemployeedeclaration().success(function(response) {
			if(response.data.numrow == 0 || response.data.time_flag == 1 ){
				$scope.showdeclarationpopup = true;
			}
		});
		

		//~ APIServices.fetchloyaltyinfo().success(function(response) {
			//~ if(response.data.count == 0 || response.data.time_flag == 1 ) {
				//~ $scope.showloyaltypop = true;
			//~ }
		//~ });
		//~ 
		//~ $scope.closepopup = function() {
			//~ APIServices.storeloyaltyinfo().success(function(response) {
				//~ $scope.showloyaltypop = false;
			//~ });
		//~ }

		/***campaign serach********************/
		
		
		$scope.campaignall=[];
		$scope.checkAttr    =   {};
		$scope.checkAttr['ALL'] = false;
		$scope.checkAttr['PDG'] = false;
		$scope.checkAttr['Package'] = false;
		$scope.checkAttr['H2L'] = false;
		$scope.checkAttr['LPPPackage'] = false;
		$scope.checkAttr['JDRR2019'] = false;
		
		
		$scope.campaignOrder = function(id) {
				if($('#'+id).is(':checked')===true){
					$scope.checkAttr[id] = true;		
					$scope.campaignall.push(id);
				}else{
					$scope.checkAttr[id] = false;		
					var idx = $scope.campaignall.indexOf(id);
					$scope.campaignall.splice(idx,1);
				}
				var campStr = "";
				for(key in $scope.campaignall) {
					campStr+=$scope.campaignall[key]+",";
				}
				campStr	=	campStr.slice(0,-1);
				//console.log(JSON.stringify($scope.campaignall));return false;
				$scope.allocContracts	=	[];
				APIServices.campaignFilter(campStr).success(function(response) { 
					
						$scope.allocContracts = response;
					//console.log($scope.allocContracts);
				});
				
			};
			
		/************End******************/
			

		$scope.storeemp = function() {
			if($('.accept_flag').is(':checked')){
				APIServices.storeemp().success(function(response) {
					if(response.errorCode == 0){
						$scope.showdeclarationpopup = false;
					}else {
						alert('please try again');
					}
				});
			}else{
				alert('Please Accept the Data Regulation Rules');
			}
		}
				
		$scope.loadAlloc = function(returnState,$page) {
			var pageShow = '';
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			$scope.remCity		=	$cookieStore.get('city');
			if($scope.remCity == '' || $scope.remCity == undefined){
				$cookieStore.remove('city');
				var city_search	=	$scope.remCity;			
			}else{ 
				var city_search	=	$scope.remCity;			
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Allocated Contracts
			APIServices.getAllocContracts(returnState.stateParam,returnState.whichParam,pageShow,city_search,returnState.parid).success(function(response) {
				if(response.errorCode	==	1)
					$cookieStore.remove('city');	
				$scope.allocContracts	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.allocContracts = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.allocContracts.data = response.data;
					$scope.allocContracts.allocData = response.allocData;
					$scope.allocContracts.count = response.count;
					$scope.allocContracts.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				// Calculation for getting Likely Expiry Status
				if($scope.allocContracts.errorCode	==	0) {
					$scope.totCountShow		=	response.counttot;
					$scope.totCountShowGr	=	response.countGroup;
					$scope.allocContracts.totCount	=	response.counttot;
					
					$scope.pageContracts	=	Math.ceil($scope.allocContracts.totCount/$scope.showPageNum);
					
					$scope.allocContracts.data.forEach(function(contract) {
						contract.expondo	=	function() {
							var exp_on	=	'--';
							var month	=	'';
							var exxp_on	=	'';
							if(contract.exp_on > 6 ){ exp_on = 'More than 6 Months'; }
							else if(contract.exp_on == null){ exp_on = '-'; }
							else if(contract.exp_on == 0){ exp_on = 'Pending for Renewal'; }
							else if(contract.exp_on >= 0.01 && contract.exp_on <= 0.99 ) {
								exxp_on = ((contract.exp_on * 30)%30);
								exp_on = exxp_on+' days'; 
							} else if(contract.exp_on >= 1 && contract.exp_on <= 6 ) {
								exxp_on = ((contract.exp_on * 30)%30)+'</br>';
								month = Math.floor(contract.exp_on);
								if(exxp_on > 0){
									exp_on = month+' Month'+exxp_on+'days';
								} else {
									exp_on = month+' Month';
								}
							} else { 
								exp_on = exp_on+'Month'; 
							}
							return exp_on;
						};
					});
					if($scope.viewParam	==	2) {
						$scope.setwidthList();
					}
					$('html, body').animate({scrollTop: 0}, 1000);
				}
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},400);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
		};
		
		var city_search	=	$('.searchCity').val(); 
			if($.trim(city_search) == '' || $.trim(city_search) == null)
				$scope.isDisabled	=	true;
			else
				$scope.isDisabled	=	false;
				
		//Function to fetch contracts of given city
		$scope.searchCity	=	function(event,$page) {
			var pageShow = '';
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			$scope.isDisabled	=	true;
			var city_search	=	$('.searchCity').val(); 
			
			$cookieStore.put('city',$('.searchCity').val());
			$scope.checkCity	=	$cookieStore.get('city');	//console.log($scope.checkCity+'-------'); 
			
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.getAllocContracts(returnState.stateParam,returnState.whichParam,pageShow,city_search,returnState.parid).success(function (response) {
				$scope.allocContracts	=	[];
				if(response.errorCode	==	1)
					$cookieStore.remove('city');
				if(pageShow == '' || pageShow == null) {
					$scope.allocContracts = response;
					$scope.mainPage = scopeUrlExp[1]; 
				} else {
					$scope.allocContracts.data = response.data;
					$scope.allocContracts.allocData = response.allocData;
					$scope.allocContracts.count = response.count;
					$scope.allocContracts.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				
				// Calculation for getting Likely Expiry Status
				if($scope.allocContracts.errorCode	==	0) {
					$scope.totCountShow		=	response.counttot;
					$scope.totCountShowGr	=	response.countGroup;
					$scope.allocContracts.totCount	=	response.counttot;
					
					$scope.pageContracts	=	Math.ceil($scope.allocContracts.totCount/$scope.showPageNum);
					
					$scope.allocContracts.data.forEach(function(contract) {
						contract.expondo	=	function() {
							var exp_on	=	'--';
							var month	=	'';
							var exxp_on	=	'';
							if(contract.exp_on > 6 ){ exp_on = 'More than 6 Months'; }
							else if(contract.exp_on == null){ exp_on = '-'; }
							else if(contract.exp_on == 0){ exp_on = 'Pending for Renewal'; }
							else if(contract.exp_on >= 0.01 && contract.exp_on <= 0.99 ){
								exxp_on = ((contract.exp_on * 30)%30);
								exp_on = exxp_on+' days'; 
							} else if(contract.exp_on >= 1 && contract.exp_on <= 6 ) {
								exxp_on = ((contract.exp_on * 30)%30)+'</br>';
								month = Math.floor(contract.exp_on);
								if(exxp_on > 0){
									exp_on = month+' Month'+exxp_on+'days';
								} else {
									exp_on = month+' Month';
								}
							} else { 
								exp_on = exp_on+'Month'; 
							}
							return exp_on;
						};
					});
					if($scope.viewParam	==	2) {
						$scope.setwidthList();
					}
					$('html, body').animate({scrollTop: 0}, 1000);
				}
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},400);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});	
		};
		
		$scope.setImageCheck	=	[];
		$scope.parDelSend		=	[];
		$scope.countCheckedPros	=	0;
		$scope.checkDelProspect	=	function(event,contractid,index) {
			if($scope.setImageCheck[index] == 1) {
				$scope.setImageCheck[index]	=	0;
				$scope.parDelSend.splice(index,1);
				$scope.countCheckedPros--;
			} else {
				$scope.setImageCheck[index]	=	1;
				$scope.parDelSend[index]		=	contractid;
				$scope.countCheckedPros++;
			}
		};
		
		$scope.sendSmsEmailAlloc	=	function() {
			if($scope.countCheckedPros > 0) {
				var strParIdDelPros	=	"";
				$scope.parDelSend.forEach(function(val,i) {
					strParIdDelPros	+=	val+',';
				});
				
				if($rootScope.findSMSEmailVal == 1) {
					window.open ("../tmAlloc/sendemail.php?contractID="+strParIdDelPros+"", "workshops", " location = 1, resizable = yes, status = 1, scrollbars = 1, width = 750, height=500 ");
				} else {
					window.open ("../tmAlloc/sendsms.php?contractID="+strParIdDelPros+"", "workshops", " location = 1, resizable = yes, status = 1, scrollbars = 1, width = 750, height=500 ");
				}
			} else {
				alert('Please select atleast one contract to send Email/SMS');
				return false;
			}
		};
		
		if($cookieStore.get("thisPage") == 'allocation') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'allocation');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {	
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);	
			if($scope.citySrch !=''){	//alert('with city');
				$scope.searchCity(returnState,loadNumber);
			
			}else{	//alert('with out city');
				$scope.loadAlloc(returnState,loadNumber);
			}
		};
	
	});
	
	//Controller defined for Hot Data
	tmeModuleApp.controller('hotDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout){
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		var pageShow	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching HotData Contracts
			APIServices.fetchHotData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response){
				$scope.hotData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.hotData 	= response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.hotData.data = response.data;
					$scope.hotData.count = response.count;
					$scope.hotData.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.hotData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil($scope.hotData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		if($cookieStore.get("thisPage") == 'hotData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter'  && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'hotData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	tmeModuleApp.controller('jdrrPropectDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout){
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		var pageShow	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching HotData Contracts
			APIServices.fetchjdrrPropectData(returnState.stateParam,returnState.whichParam,pageShow).success(function(response){
				$scope.hotData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.hotData 	= response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.hotData.data = response.data;
					$scope.hotData.count = response.count;
					$scope.hotData.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.hotData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil($scope.hotData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		if($cookieStore.get("thisPage") == 'jdrrPropectData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter'  && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'jdrrPropectData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	tmeModuleApp.controller('jdrrCourierDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout){
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		var pageShow	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching HotData Contracts
			APIServices.fetchjdrrCourierData(returnState.stateParam,returnState.whichParam,pageShow).success(function(response){
				$scope.hotData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.hotData 	= response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.hotData.data = response.data;
					$scope.hotData.count = response.count;
					$scope.hotData.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.hotData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil($scope.hotData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		if($cookieStore.get("thisPage") == 'jdrrCourierData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter'  && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'jdrrCourierData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Bounced Data
	tmeModuleApp.controller('bouncedDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page === 'undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Bounced Data
			APIServices.fetchBounceData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.bounceData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.bounceData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.bounceData.data = response.data;
					$scope.bounceData.count = response.count;
					$scope.bounceData.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.bounceData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.bounceData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'bouncedData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'bouncedData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//jdiro
	tmeModuleApp.controller('JdrIroController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page === 'undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Bounced Data
			APIServices.JdrIro(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.JdrIro	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.JdrIro = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.JdrIro.data = response.data;
					$scope.JdrIro.count = response.count;
					$scope.JdrIro.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.JdrIro.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.JdrIro.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'JdrIro') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'JdrIro');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//Webiro
	tmeModuleApp.controller('WebIroController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page === 'undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Bounced Data
			APIServices.WebIro(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.WebIro	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.WebIro = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.WebIro.data = response.data;
					$scope.WebIro.count = response.count;
					$scope.WebIro.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.WebIro.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.WebIro.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'WebIro') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'WebIro');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//whatsappcalled
	
	tmeModuleApp.controller('whatsappcalledController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page === 'undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Bounced Data
			APIServices.whatsappcalled(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.whatsappcalled	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.whatsappcalled = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.whatsappcalled.data = response.data;
					$scope.whatsappcalled.count = response.count;
					$scope.whatsappcalled.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.whatsappcalled.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.whatsappcalled.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'whatsappcalled') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'whatsappcalled');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//Controller used for Bounce Data ECS
	tmeModuleApp.controller('bouncedDataECSController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Bounce Data ECS
			APIServices.fetchBounceECSData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.bouncedDataECS	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.bouncedDataECS = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.bouncedDataECS.data = response.data;
					$scope.bouncedDataECS.count = response.count;
					$scope.bouncedDataECS.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.bouncedDataECS.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.bouncedDataECS.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'bouncedDataECS') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'bouncedDataECS');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller defined for Account Details Restaurant Data
	tmeModuleApp.controller('accountDetRestController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout){
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		var pageShow	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching HotData Contracts
			APIServices.fetchAccountDetRest(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response){
				$scope.hotData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.accountDetRest 	= response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.accountDetRest.data = response.data;
					$scope.accountDetRest.count = response.count;
					$scope.accountDetRest.errorCode	=	0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.accountDetRest.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil($scope.accountDetRest.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		if($cookieStore.get("thisPage") == 'accountDetRest') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter'  && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'accountDetRest');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});

	//Controller used for Instant ECS
	tmeModuleApp.controller('instantECSController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Instant ECS
			APIServices.fetchInstantECSData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.instantECS	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.instantECS = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.instantECS.data = response.data;
					$scope.instantECS.count = response.count;
					$scope.instantECS.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.instantECS.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.instantECS.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'instantECS') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'instantECS');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});

	//Controller defined for New Business
	tmeModuleApp.controller('newBusinessController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching New Business Contracts
			APIServices.fetchNewBusiness(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.newBusiness	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.newBusiness = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.newBusiness.data = response.data;
					$scope.newBusiness.count = response.count;
					$scope.newBusiness.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.newBusiness.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.newBusiness.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'newBusiness') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'newBusiness');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller defined for Restaurant Offer
	tmeModuleApp.controller('restaurantdealsofferController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.restaurantdealsoffer	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.fetchrestaurantdealsoffer(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.restaurantdealsoffer	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.restaurantdealsoffer = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.restaurantdealsoffer.data = response.data;
					$scope.restaurantdealsoffer.count = response.count;
					$scope.restaurantdealsoffer.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.restaurantdealsoffer.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.restaurantdealsoffer.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'restaurantdealsoffer') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'restaurantdealsoffer');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
	});
	
	//Controller defined for Super Hot data
	tmeModuleApp.controller('superhotdataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout,$rootScope) {
		$scope.superhotdata	=	[];
		var loadNumber	=	0;
		APIServices.updatesuperhotdata(returnState.parid).success(function(responseUp) {
		});
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			$rootScope.superHotReadcount	=	0;
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.fetchsuperhotdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.superhotdata	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.superhotdata = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.superhotdata.data = response.data;
					$scope.superhotdata.count = response.count;
					$scope.superhotdata.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.superhotdata.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.superhotdata.totCount/$scope.showPageNum);
				$rootScope.superHotReadcount	=	response.readCount;
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'superhotdata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'superhotdata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
	});
	
	
	//Controller defined for ownershippaiddata othertmepaiddata
	tmeModuleApp.controller('ownershippaiddataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.ownershipdata	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.ownershipdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid,'1').success(function(response) {
				$scope.ownershipdata	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.ownershipdata = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.ownershipdata.data = response.data;
					$scope.ownershipdata.count = response.count;
					$scope.ownershipdata.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.ownershipdata.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.ownershipdata.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'ownershippaiddata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'ownershippaiddata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller defined for othertmepaiddata  ownershipexpireddata
	tmeModuleApp.controller('othertmepaiddataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching New Business Contracts
			APIServices.ownershipdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid,'3').success(function(response) {
				$scope.newBusiness	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.newBusiness = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.newBusiness.data = response.data;
					$scope.newBusiness.count = response.count;
					$scope.newBusiness.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.newBusiness.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.newBusiness.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'othertmepaiddata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'othertmepaiddata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller defined for ownershipexpireddata   ownershippaiddataController
	tmeModuleApp.controller('ownershipexpireddataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching New Business Contracts
			APIServices.ownershipdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid,'2').success(function(response) {
				$scope.newBusiness	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.newBusiness = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.newBusiness.data = response.data;
					$scope.newBusiness.count = response.count;
					$scope.newBusiness.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.newBusiness.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.newBusiness.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'ownershipexpireddata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'ownershipexpireddata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller defined for ownershippaiddataController   
	tmeModuleApp.controller('othertmeexpireddataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching New Business Contracts
			APIServices.ownershipdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid,'4').success(function(response) {
				$scope.newBusiness	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.newBusiness = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.newBusiness.data = response.data;
					$scope.newBusiness.count = response.count;
					$scope.newBusiness.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.newBusiness.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.newBusiness.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'othertmeexpireddata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'othertmeexpireddata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
		//Controller defined for Lead related complaints
	tmeModuleApp.controller('leadComplaintsController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout,$mdDialog,$rootScope) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.dispSelected = '';
		
		$scope.loadAlloc	=	function(returnState,$page) {
			
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Ecs Transfer Call Contracts
			APIServices.fetchleadComplaints(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.leadComplaints	=	[];
				
				if(pageShow == '' || pageShow == null) {
					$scope.leadComplaints = response;
					$scope.mainPage = scopeUrlExp[1];
					
				} else {
					$scope.leadComplaints.data = response.data;
					$scope.leadComplaints.count = response.count;
					$scope.leadComplaints.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.leadComplaints.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil($scope.leadComplaints.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		if($cookieStore.get("thisPage") == 'leadComplaints') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'leadComplaints');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
		$scope.OpenUploadedFiles = function(ev,uploadArr)
		{
			$rootScope.csgenio_url = $scope.decsURL;
			$rootScope.filename.length = 0;
			$.each(uploadArr, function(key, value)
			{
				$rootScope.filename.push({'file' : value.filename,'display_file' : value.filename+" - "+value.update_date});
			});
			
			$mdDialog.show({
				 controller: Transfer_EmailUploadsController,
				 templateUrl: 'partials/Transfer_EmailUploads.html',
				 parent: angular.element(document.body),
				 targetEvent: ev,
				 clickOutsideToClose:false
			})
		};
		
		function Transfer_EmailUploadsController($scope, $mdDialog, $rootScope,APIServices) {
		$scope.uploaded_details = [];
		$scope.uploaded_details = $rootScope.filename;
		$scope.total_files = $rootScope.filename.length;
		
		$scope.open_uploadedFile = function(file){
			var OpenFile = "http://"+$rootScope.csgenio_url+"/transfer_email_uploads/"+file;
			window.location.href = OpenFile;
		};
		
		$scope.transfer_mdDialog_hide = function() {
			  $mdDialog.hide();
			};
		}
	});
	
	
	
	//Controller defined for Retention Data
	tmeModuleApp.controller('retentionDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout,$rootScope) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Retention Data
			APIServices.fetchRetentionData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$rootScope.retentionData	=	[];
				if(pageShow == '' || pageShow == null) {
					$rootScope.retentionData = response;
					$rootScope.mainPage = scopeUrlExp[1];
				} else {
					$rootScope.retentionData.data = response.data;
					$rootScope.retentionData.count = response.count;
					$rootScope.retentionData.errorCode = 0;
				}
				$rootScope.selectedIndex	=	pageShow;
				$rootScope.retentionData.totCount	=	response.counttot;	
				$rootScope.pageContracts	=	Math.ceil($scope.retentionData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'retentiondata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'retentiondata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		//Method used to show Status of TME
		$scope.showStatusTme	=	function(event,eventStatus,index) {
			if(eventStatus	==	'1') {
				alert('This ECS is already retained');
				return false;
			} else if(eventStatus	==	'2') {
				alert('This ECS is already stopped');
				return false;
			} else if(eventStatus	==	'0' || eventStatus	==	'3' || eventStatus	==	'4') {
				if($scope.viewParam	==	1) {
					$('.tmeStatus').addClass('hide');
					$(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');
				} else {
					$scope.showDataModeTab(event,6,$rootScope.retentionData.data[index].contractid,$rootScope.retentionData.data[index].compname,index);
				}
			}
		};
		
		//Method used to retain contract
		$rootScope.retainContract	=	function(event,$parentid,$flag,$index) {
			switch($flag) {
				case 1:
					var message	=	'Are you sure you want to retain this contract?'; break;
				case 2:
					var message	=	'Are you sure you took an effort to retain the client & still client is Not Interested ?'; break;
				case 3:
					var message	=	'Are you sure you want to tag it as Follow Up?'; break;
				case 4:
					var message	=	'Are you sure you want to tag it as Not Contactable?'; break;
			}
			var confRetain	=	confirm(message);
			if(confRetain) {
				APIServices.actEcsRetention($parentid,$flag).success(function(response) {
					if(response.results.errorCode	==	0) {
						switch($flag) {
							case 1:
								alert('Status Retained'); break;
							case 2:
								alert('Status Retained'); break;
							case 3:
								alert('Status Updated'); break;
							case 4:
								alert('Status Updated'); break;
						}
						$('.tmeStatus').addClass('hide');
						$scope.retentionData.data[$index]['retention_stop_flag']	=	$flag;
					}
				});
			} else {
				return false;
			}
		};
		
		//Method used to used to open comment box
		$rootScope.tmeCommentBoxOpen	=	function(event,$parentid,$index) {
			$rootScope.tmeCommentMsg	=	'';
			APIServices.fetchTmeComments($parentid,1).success(function(response) {
				$('.tmeComment').addClass('hide');
				$(event.target).closest('.contDivs').find('.tmeComment').removeClass('hide');
				if(response.errorCode	==	0) {
					$rootScope.tmeCommentMsg	=	response.data.tme_comments;
					if(response.data.tme_comments	==	'' || response.data.tme_comments == null) {
						$rootScope.tmeCommStat	=	'Add';
					} else {
						$rootScope.tmeCommStat	=	'Edit';
					}
				}
			});
		};
		
		//Method used to submit the comment
		$rootScope.submitComm	=	function($parentid) {
			var tmeComment	=	this.sendDataComment;
			APIServices.fetchTmeComments($parentid,2,tmeComment).success(function(response) {
				if(response.results.errorCode	==	0) {
					alert('Comment Successfully Updated');
				} else {
					alert('Comment Not Updated. Please try again later');
				}
				$('.tmeComment').addClass('hide');
			});
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Prospect Data
	tmeModuleApp.controller('prospectDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Prospect Data
			APIServices.fetchProspectData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.prospectData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.prospectData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.prospectData.data = response.data;
					$scope.prospectData.count = response.count;
					$scope.prospectData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.prospectData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.prospectData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		
		$scope.setImageCheck	=	[];
		$scope.parDelSend		=	[];
		$scope.countCheckedPros	=	0;
		$scope.checkDelProspect	=	function(event,contractid,index) {
			if($scope.setImageCheck[index] == 1) {
				$scope.setImageCheck[index]	=	0;
				$scope.parDelSend.splice(index,1);
				$scope.countCheckedPros--;
			} else {
				$scope.setImageCheck[index]	=	1;
				$scope.parDelSend[index]		=	contractid;
				$scope.countCheckedPros++;
			}
		};
		
		$scope.delProspect	=	function() {
			var strParIdDelPros	=	"";
			$scope.parDelSend.forEach(function(val,i) {
				strParIdDelPros	+=	val+',';
			});
			if($scope.countCheckedPros > 0) {
				APIServices.deleteProspectData(strParIdDelPros.slice(0,-1)).success(function(response) {
					var len	=	$scope.prospectData.data.length;
					for (var i = $scope.prospectData.data.length - 1; i >= 0; i--) {
						if (typeof $scope.setImageCheck[i] !== 'undefined' && $scope.setImageCheck[i] === 1) {
							$scope.prospectData.data.splice(i, 1);
							$scope.setImageCheck[i]	=	0;
							$scope.parDelSend.splice(i,1);
							len--;
						}
					}
					if(len == 0) {
						$scope.prospectData.errorCode	=	1;
					}
				});
			} else {
				alert('Please select prospect data first');
				return false;
			}
		};
		
		if($cookieStore.get("thisPage") == 'prospectData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'prospectData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Method used for jdRating Data
	tmeModuleApp.controller('jdRatingDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Jd Rating Data
			APIServices.fetchJdRatingData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.jdRatingData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.jdRatingData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.jdRatingData.data = response.data;
					$scope.jdRatingData.count = response.count;
					$scope.jdRatingData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.jdRatingData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.jdRatingData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'jdRatingData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'jdRatingData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	tmeModuleApp.factory("transformRequestAsFormPost",function() {
		function transformRequest( data, getHeaders ) {
			var headers = getHeaders();
			headers[ "Content-type" ] = "application/x-www-form-urlencoded; charset=utf-8";
			return( serializeData( data ) );
		}
		
		return( transformRequest );
		function serializeData( data ) {
			if ( ! angular.isObject( data ) ) {
			return( ( data == null ) ? "" : data.toString() );
			}
			var buffer = [];
			for ( var name in data ) {
				if ( ! data.hasOwnProperty( name ) ) {
					continue;
				}
				var value = data[ name ];
				buffer.push(
				encodeURIComponent( name ) + "=" + encodeURIComponent( ( value == null ) ? "" : value ));
			}
			var source = buffer.join( "&" ).replace( /%20/g, "+" );
			return( source );
		}
	});
	
	//Controller used for Worked for ECS Data
	tmeModuleApp.controller('workedECSDataController',function($scope,$rootScope,APIServices,returnState,$location,$cookieStore,$http,transformRequestAsFormPost,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.newComment	=	"";
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Worked for ECS Data
			APIServices.fetchWorkedForECS(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$rootScope.workedForECSData	=	[];
				if(pageShow == '' || pageShow == null) {
					$rootScope.workedForECSData = response;
					$rootScope.mainPage = scopeUrlExp[1];
				} else {
					$rootScope.workedForECSData.data = response.data;
					$rootScope.workedForECSData.count = response.count;
					$rootScope.workedForECSData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$rootScope.workedForECSData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($rootScope.workedForECSData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
			//Method used for Setting the comment
			$rootScope.setComment =function(event,$parentid,$index,$onsubmit) {
				var $currComment	=	this.setCommentWorked;
				if(typeof $currComment	===	"undefined") {
					alert("Please enter a comment");
					return false;
				} else {
					var Comment = $onsubmit;
					var finComment	=	$currComment;
					APIServices.StoreComment(finComment,$parentid,USERID).success(function (response) {
						$('.Opaque_commentWindow_add').addClass('hide');
						$rootScope.workedForECSData.data[$index]['tme_comments']	=	response.results.retData;
						$scope.newComment	=	"";
						alert('Comment Added Successfully');
					});
				}
			};
			
			$scope.showReqTabs	=	function(event) {
				$('.tmeStatus').addClass('hide');
				$(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');				
			};
			
			$rootScope.requestToDo	=	function(event,$parentid,status,st,compname,index,selfFlag) {
				var confirmMsg	=	"";
				if(selfFlag == 0) {
					switch(status) {
						case 1:
							confirmMsg	=	"Are you sure, you want to stop the ECS?";
						break;
						case 5:
							confirmMsg	=	"Are you sure, client agreed to continue for ECS?";
						break;
						case 4:
							confirmMsg	=	"Are you sure to mark it as Follow Up? Pls Confirm";
						break;
						case 7	:
							confirmMsg	=	"Are you sure to mark it as Upgrade? Pls Confirm";
						break;
						case 8	:
							confirmMsg	=	"Are you sure to mark it as Degrade? Pls Confirm";
						break;
					}
					if(confirm(confirmMsg)) {
						var request	=	$http({
							method : "POST",
							url : "../tmAlloc/ajaxData.php",
							headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						    transformRequest: function(obj) {
								var str = [];
								for(var p in obj)
								str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
								return str.join("&");
						   },
							data: {
								parentid: $parentid,
								action_flag: status,
								status: status,
								st : st,
								flag :'old'
							}
						});
						
						request.success(function(response) {
							if($.trim(response) === "ECS") {
								window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(compname)+"&parent_id="+$parentid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&city="+DATACITY+"&flag=old");
								alert("The Status has been updated successfully");
							} else if($.trim(response) == 'CCSI') {
								window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(compname)+"&parent_id="+$parentid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&city="+DATACITY+"&flag=old");
								alert("The Status has been updated successfully");
							} else if ($.trim(response) == 'NONE'){
								alert("Nothing to Stop");
							} else {
								alert("The Status has been updated successfully");
							}
							
							$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
						});
					} else {
						return false;
					}
				} else {
					var request	=	$http({
						method : "POST",
						url : "../tmAlloc/ajaxData.php",
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					    transformRequest: function(obj) {
							var str = [];
							for(var p in obj)
							str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
							return str.join("&");
						},
						data: {
							parentid: $parentid,
							action_flag: status,
							status: status,
							st : st,
							flag :'old'
						}
					});
					
					request.success(function(response) {
						if($.trim(response) === "ECS") {
							window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(compname)+"&parent_id="+$parentid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&city="+DATACITY+"&flag=old");
						} else if($.trim(response) == 'CCSI') {
							window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(compname)+"&parent_id="+$parentid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&city="+DATACITY+"&flag=old");
						} else if ($.trim(response) == 'NONE'){
							alert("Nothing to Stop");
						} else {
							alert("The Status has been updated successfully");
						}
						
						$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
					});
				}
			};
			
			//Method used for Setting Type of comment to show Edit or Add Image
			$scope.commentType	=	function(event,$parentid,$commentAction) {
				if($commentAction	==	1) {
					$scope.commentMsg	=	'Add';
				} else {
					$scope.commentMsg	=	'Submit';
				}
				$('.Opaque_commentWindow_add').addClass('hide');
				$(event.target).closest('.contDivs').find('.Opaque_commentWindow_add').removeClass('hide');
			};
			
			//Method used for Adding data for VLC Upload
			$scope.attachvlc	=	function($parentid,$data_city,$reminder){
				if($parentid !=''){
					APIServices.SendVLC($parentid,$data_city,$reminder).success(function(response){
						if(response.errorCode	==	0) {
							var resp=response.data.split('~');
							
							if(resp['0'] == '1') {
								alert('Data sent to the vendor successfully');
								$(obj).attr('disabled',true);
							} else if(resp['0'] == '0'){
								alert('There is some issue while sending data to vendor. Please try again later');
							} else if(resp['0'] == '2'){
								var name	= resp['1'];
								var up_stat	= resp['2'];
								if(up_stat == 1) {
									var msg = 'Video id also Already uploaded';
								} else {
									var msg =' Do you want to send the reminder mail? ';
								}
								var res = confirm("Data already sent to the vendor '"+name+"'"+msg);
								if(res){
									$reminder='1';
									APIServices.SendVLC($parentid,$data_city,$reminder).success(function(response){
										if(response.errorCode	==	0) {
											alert("Remainder sent to the vendor");
										} else {
											alert("Remainder was not sent. Please contact software team for contract "+$parentid);
										}
									});
								} else {
									return false;
								}
							} else if(resp['0'] == '3'){
								alert("No vendor found for the pincode of the contract. Mail sent to the web team for assigning to the vendor");
							} else if(resp['0'] == '4'){
								alert("No vendor found for the pincode of the contract. No mail sent to the web team. Please contact software team");
							} else if(resp['0'] == '5'){
								alert("The contract is already assigned to "+resp['1']+" which is deactivated. Mail sent to the co-ordinator");
							} else if(resp['0'] == '10'){
								alert("Video is already uploaded for the contract.");
							}
						} else {
							alert('VLC was not sent. Please contact softwware team for contract ' + $parentid);
						}
					});
				}
			}
		};
		if($cookieStore.get("thisPage") == 'workedEcsData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'workedEcsData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Restaurant Data
	tmeModuleApp.controller('restaurantDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Restaurant Data
			APIServices.fetchRestaurantData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.restaurantData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.restaurantData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.restaurantData.data = response.data;
					$scope.restaurantData.count = response.count;
					$scope.restaurantData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.restaurantData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.restaurantData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'restaurantData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'restaurantData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Expired Data
	tmeModuleApp.controller('expiredDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchExpiredData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.expiredData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.expiredData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.expiredData.data = response.data;
					$scope.expiredData.count = response.count;
					$scope.expiredData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.expiredData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.expiredData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'expiredData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'expiredData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Magazine Data
	tmeModuleApp.controller('magazineDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchMagazineData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.magazineData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.magazineData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.magazineData.data = response.data;
					$scope.magazineData.count = response.count;
					$scope.magazineData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.magazineData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.magazineData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'magazineData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'magazineData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});

	
	//Controller used for Paid Ecs Data
	tmeModuleApp.controller('paidEcsDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchPaidEcsData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.paidEcsData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.paidEcsData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.paidEcsData.data = response.data;
					$scope.paidEcsData.count = response.count;
					$scope.paidEcsData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.paidEcsData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.paidEcsData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'paidEcsData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'paidEcsData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});

	//Controller used for Callcount Data
	tmeModuleApp.controller('callcountDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching ShopFront Data
			APIServices.fetchcallcountData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.callcountData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.callcountData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.callcountData.data = response.data;
					$scope.callcountData.count = response.count;
					$scope.callcountData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.callcountData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.callcountData.totCount/$scope.showPageNum);
				var timer	=	setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'callcountData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'callcountData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
	});

	//Controller used for Appointment Report Data
	tmeModuleApp.controller('appointmentRepController',function($scope,APIServices,returnState,$location,$cookieStore,$state,$timeout,$rootScope,$mdDialog,$window) {
		$scope.cityCode = cityCode;
		$scope.allocContracts	=	[];
		$rootScope.reportData	=	[]; // new Variable Created
		var loadNumber			=	0;
		$scope.imgctiCall		=	{};
		//~ $scope.techinfoDisp		=	'';
		$scope.callfn_function	=	function(index){	
			var stVal			=	208;
			var dispo_cal 		= returnFormat($scope.techinfoDisp,stVal,$rootScope.employees.remoteAddr);
			$window.parent.iframecti.location.href = dispo_cal;
			$scope.imgctiCall[index]	=	0;
		};
		$scope.ctiCallfunction	=	function(empcode,parid,index) {
			APIServices.ctiCallfunction(empcode,parid).success(function(response) {
					var dncnum = response.number;
					var allow_flag = 0;
					if(SERVICE_CITY	==	1)
						var var_remote = 'remote_city';
					else
						var var_remote = 'main_city';
					if(var_remote == "remote_city" && response.aspect_color=='green' ) {
						dncnum = '055'+response.number;
					}else if(var_remote == "remote_city" && response.aspect_color=='red' && (response.hotdata == 1 || (response.allocid == 'O' || response.allocid == 'o'))) {
							dncnum = '055'+response.number;		
					}else if(response.aspect_color=='red' && (response.dnc_flag == 2 || response.dnc_flag == 3 || response.dnc_flag == 1) && response.hotdata == 1) {
							dncnum = '055'+response.number;		
					}else if(response.aspect_color=='red') {
							dncnum = '055'+response.number;		
					}else if(response.aspect_color=='green'){
						dncnum = '055'+response.number;
					}else if(response.aspect_color=='red') {
						dncnum ='';
					} else {
						dncnum = response.number;
					}
					if(dncnum !=''){
						$scope.techinfo				=	response.techinfoUrl;
						$scope.techinfoDisp	   		= 	response.techinfoDisp;
						$scope.imgctiCall[index]	=	1;
						var make_cal = returnFormat($scope.techinfo,dncnum,response.ctinum);
						$window.parent.iframecti.location.href = make_cal;
					}else{
						alert("Can't Call DNC number");
					}
			});
		};
		function returnFormat(sFormatString, sParam1, sParam2, sParam3, sParam4, sParam5) {
			if (typeof(sParam1) != 'undefined') {
				sFormatString = sFormatString.replace(/\%param1\%/i, sParam1);
			}
			if (typeof(sParam2) != 'undefined') {
				sFormatString = sFormatString.replace(/\%param2\%/i, sParam2);
			}
			if (typeof(sParam3) != 'undefined') {
				sFormatString = sFormatString.replace(/\%param3\%/i, sParam3);
			}
			if (typeof(sParam4) != 'undefined') {
				sFormatString = sFormatString.replace(/\%param4\%/i, sParam4);
			}
			if (typeof(sParam5) != 'undefined') {
				sFormatString = sFormatString.replace(/\%param5\%/i, sParam5);
			}
			return sFormatString;
		}
		$scope.loadAlloc	=	function(returnState,$page) {
			var pageShow	=	'';
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			$scope.extraVals	=	returnState.extraVals;
			$scope.currentPage	=	returnState.currentPage;
			
			$cookieStore.put('currPageReport',$scope.currentPage);
			$cookieStore.put('extraVals',$scope.extraVals);
			
			$scope.reportOrder	= function(srchParam,orderFlag){ 
				var currentPage 	=	$scope.currentPage;
				var extraVals 	=	$scope.extraVals;  
				$state.go('appHome.filter',{srchparam:srchParam,srchWhich:orderFlag,currPage:currentPage,extraVals:extraVals}); 
			}
			// Service identifier for Fetching Appointment Data
			APIServices.fetchReportData(returnState.stateParam,returnState.whichParam,pageShow,returnState.extraVals).success(function(response) {
				$scope.reportData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.reportData = response;
					$rootScope.reportData	=	$scope.reportData; // code Added Here
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.reportData.data = response.data;
					$rootScope.reportData.data	=	$scope.reportData.data; // code Added Here
					$scope.reportData.count = response.count;
					$scope.reportData.errorCode = 0;
				}
				//~ $scope.techinfo		   = response.techinfoUrl;
				//~ $scope.techinfoDisp	   = response.techinfoDisp;
				//console.log('0-=-==-=-=-=-'+$scope.techinfo);
				angular.forEach($scope.reportData.data,function(value,key){
					$scope.imgctiCall[key]	=	0;
				});
				$scope.selectedIndex	=	pageShow;
				$scope.reportData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.reportData.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.setwidthList();
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
		};
		if($cookieStore.get("thisPage") == 'reportdata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'reportdata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		/*Code Added Here By Apoorv Agrawal Start*/
		$scope.cancel_appt	=	function(ev,parentid,companyname,parentCode,MECode,actionTime){
			$rootScope.cancel_parid	=	parentid;
			$rootScope.companyname	=	companyname;
			$rootScope.parentCode	=	parentCode;
			$rootScope.MECode		=	MECode;
			$rootScope.actionTime	=	actionTime;
			$mdDialog.show({
				controller: cancelapptController,
				templateUrl: 'partials/CancelAppt.html',
				parent: angular.element(document.body),
				targetEvent: ev,
			});
		}
		function cancelapptController($scope,$mdDialog,APIServices){
			$scope.cancel_parid		=	$rootScope.cancel_parid;
			$scope.companyname		=	$rootScope.companyname;
			$scope.tmeCode			=	$rootScope.parentCode;
			$scope.yes_proceed	=	function(ev){
				APIServices.update_appt($rootScope.cancel_parid,$rootScope.companyname,$rootScope.parentCode,$rootScope.MECode,$rootScope.actionTime).success(function(response) {
					for(var i = 0; i<$rootScope.reportData.data.length;i++){
						if($rootScope.reportData.data[i].contractid == $scope.cancel_parid){
							$rootScope.reportData.data[i].cancel_flag	=	1;
						}
					}
					$mdDialog.hide();
				});
			}
			$scope.no_stop	=	function(ev){
				$mdDialog.hide();
			}
		}
		/*Code Added Here By Apoorv Agrawal Ends*/
	});

	//Controller used for Special Data
	tmeModuleApp.controller('specialDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Chain Restaurant
			APIServices.fetchSpecialData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.specialData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.specialData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.specialData.data = response.data;
					$scope.specialData.count = response.count;
					$scope.specialData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.specialData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.specialData.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
		};
		if($cookieStore.get("thisPage") == 'specialData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'specialData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		//~ var selectedLeft	=	$('.selectedPage').offset().left;
		//~ console.log(selectedLeft);
	});
	
	//controller for ECS Upgrade/Degrade Requests//
	
	
	//~ {"data":[{"parentid":"P1550310","EmpCode":"10031872","EmpName":"Ramya A","MngrCode":"006492","Acc_Reg_Flag":"1","companyname":"","pincode":"400102","mobile":"9820184509","email":"m.sudhakar_mj@yahoo.com","contact_person":"Mr M Sudhakar(Owner)","Mngr_Flag":null,"city":"Mumbai"}],"count":1,"counttot":1,"errorCode":0,"errorStatus":"Data Found"}
	
	
	tmeModuleApp.controller('ecsRequestController',function($scope,$rootScope,APIServices,returnState,$location,$cookieStore,$timeout,$mdDialog) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		var userid = USERID;
		
		  $scope.view_ecs_details = function(ev,parentid,empcode) {
			 
			 $rootScope.ecs_parid = parentid;
			 $rootScope.ecs_empcode = empcode;
			 $mdDialog.show({
							controller: DialogEcsRequestDivController,
							templateUrl: 'partials/ecsDetailsModal.html',
							 parent: angular.element(document.body),
							targetEvent: ev,
							clickOutsideToClose:false
						})
	  };
   
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
		
			APIServices.fetchEcsRequestData(returnState.stateParam,returnState.whichParam,pageShow,$scope.userid).success(function(response) {
				$scope.ecsRequest	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.ecsRequest = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.ecsRequest.data = response.data;
					$scope.ecsRequest.count = response.count;
					$scope.ecsRequest.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.ecsRequest.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.ecsRequest.totCount)/$scope.showPageNum);
				
				var timer = setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
		};
		if($cookieStore.get("thisPage") == 'ecsRequest') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'ecsRequest');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber		=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
		function DialogEcsRequestDivController($scope, $mdDialog, $rootScope,APIServices) {	
		
			$scope.parentid			=	$rootScope.ecs_parid;
			$scope.ecs_empcode			=	$rootScope.ecs_empcode;
			
			$scope.ecs_contract_details = [];
			APIServices.FetchEcsDetailsForm($scope.parentid,$scope.ecs_empcode).success(function(response) {
				if(response.errorCode	==	0){
					$scope.ecs_contract_details = response.data;
					
				}
				else{
					$scope.ecs_contract_details = response.errorCode;
					//~ $mdDialog.hide();
					//~ alert('No Details Found !!');
					//~ return false;
					//~ 
				}
				
				 $scope.ecs_mdDialog_hide = function() {
					  $mdDialog.hide();
					};
			});
		}
		
	});
	/**************************************Unsold Data*********************************************/

	tmeModuleApp.controller('unsoldDataDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching unsold data
			APIServices.fetchunsoldData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.unsoldData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.unsoldData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.unsoldData.data = response.data;
					$scope.unsoldData.count = response.count;
					$scope.unsoldData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.unsoldData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.unsoldData.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
		};
		if($cookieStore.get("thisPage") == 'unsoldData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'unsoldData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		//~ var selectedLeft	=	$('.selectedPage').offset().left;
		//~ console.log(selectedLeft);
	});
	
	/**************************************Unsold Data*********************************************/

	
	/************************************************************************************/
		
			//Controller used for Fetching Non Ecs data
			tmeModuleApp.controller('nonEcsDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
				$scope.allocContracts	=	[];
				var loadNumber	=	0;
				
				$scope.loadAlloc	=	function(returnState,$page) {
					if(typeof $page==='undefined') {
						var pageShow = '';
					} else {
						var pageShow = $page;
					}
					var scopeUrlExp	=	$location.url().split('/');
					// Service identifier for Fetching Non Ecs data
					APIServices.fetchNonecsData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
						$scope.nonEcsData	=	[];
						if(pageShow == '' || pageShow == null) {
							$scope.nonEcsData = response;
							$scope.mainPage = scopeUrlExp[1];
						} else {
							$scope.nonEcsData.data = response.data;
							$scope.nonEcsData.count = response.count;
						}
						$scope.selectedIndex		=	pageShow;
						$scope.nonEcsData.totCount	=	response.counttot;
						$scope.pageContracts		=	Math.ceil(parseInt($scope.nonEcsData.totCount)/$scope.showPageNum);
						var timer = setTimeout(function() {
							if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
								var selectedLeft	=	$('.selectedPage').position().left;
								$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
							}
						},300);
						$scope.$on("$destroy", function() {
							if (timer) {
								$timeout.cancel(timer);
							}
						});
						if($scope.viewParam	==	2) {
							$scope.setwidthList();
						}
					});
				};
				if($cookieStore.get("thisPage") == 'nonEcsData') {
					$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
				} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
					$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
				} else {
					$cookieStore.put("thisPage",'nonEcsData');
					$cookieStore.put("pageNo",'');
					$scope.loadAlloc(returnState);
				}
				$scope.clickLoad	=	function(n) {
					loadNumber	=	n;
					$cookieStore.put("pageNo",n);
					$scope.loadAlloc(returnState,loadNumber);
				};
				//~ var selectedLeft	=	$('.selectedPage').offset().left;
				//~ console.log(selectedLeft);
			});
	
			
	/************************************************************************************/
	
	//Controller used for Inventory Data
	tmeModuleApp.controller('inventoryMorethanFiftyController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Inventory Data
			APIServices.fetchinventoryMorethanFifty(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.inventoryMorethanFifty	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.inventoryMorethanFifty = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.inventoryMorethanFifty.data = response.data;
					$scope.inventoryMorethanFifty.count = response.count;
					$scope.inventoryMorethanFifty.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.inventoryMorethanFifty.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.inventoryMorethanFifty.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
		};
		
		if($cookieStore.get("thisPage") == 'inventoryMorethanFifty') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'inventoryMorethanFifty');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	
	//Controller used for Inventory Data
	tmeModuleApp.controller('inventoryDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Inventory Data
			APIServices.fetchinventoryData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.inventoryData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.inventoryData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.inventoryData.data = response.data;
					$scope.inventoryData.count = response.count;
					$scope.inventoryData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.inventoryData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.inventoryData.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
		};
		
		if($cookieStore.get("thisPage") == 'inventoryData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'inventoryData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//Controller used for TME Allocation Data
	tmeModuleApp.controller('tmeAllocController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Tme Allocated (Online Allocated)
			APIServices.fetchtmeAllocData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.tmeAlloc	=	[];
				if(pageShow == '' || pageShow == null) {	
					$scope.tmeAlloc = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {	
					$scope.tmeAlloc.data = response.data;
					$scope.tmeAlloc.count = response.count;
					$scope.tmeAlloc.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.tmeAlloc.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.tmeAlloc.totCount)/$scope.showPageNum);
				setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
			});
		};
		if($cookieStore.get("thisPage") == 'tmeAlloc') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'tmeAlloc');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		//~ var selectedLeft	=	$('.selectedPage').offset().left;
		//~ console.log(selectedLeft);
	});
	
	//Controller used for Top Called Expired Data
	tmeModuleApp.controller('topCalledExpiredDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Tme Allocated (Online Allocated)
			APIServices.fetchtopCalledExpiredData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.topCalledExpiredData	=	[];	
				if(pageShow == '' || pageShow == null) {	
					$scope.topCalledExpiredData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {	
					$scope.topCalledExpiredData.data = response.data;
					$scope.topCalledExpiredData.count = response.count;
					$scope.topCalledExpiredData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.topCalledExpiredData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.topCalledExpiredData.totCount)/$scope.showPageNum);
				setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
			});
		};
		if($cookieStore.get("thisPage") == 'topCalledExpiredData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'topCalledExpiredData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		//~ var selectedLeft	=	$('.selectedPage').offset().left;
		//~ console.log(selectedLeft);
	});
	
	//Controller used for PDG Data
	tmeModuleApp.controller('pdgdataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.fetchpdgdata(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				//alert(JSON.stringify(response));
				$scope.pdgdata	=	[];	
				if(pageShow == '' || pageShow == null) {	
					$scope.pdgdata = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {	
					$scope.pdgdata.data = response.data;
					$scope.pdgdata.count = response.count;
					$scope.pdgdata.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.pdgdata.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.pdgdata.totCount)/$scope.showPageNum);
				setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
			});
		};
		if($cookieStore.get("thisPage") == 'pdgdata') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'pdgdata');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Category Paid Data
	tmeModuleApp.controller('categoryPaidDataController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) { 
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.itemsPerPage	=	50;
		$scope.cat_value	=	'';
		$scope.catvalue    = [];
		
		$scope.loadAlloc	=	function(returnState,$page) {
			$scope.srchCats_val	=	$cookieStore.get("catSrchVal");		
			if($scope.srchCats_val == '' || $scope.srchCats_val == undefined){
				$cookieStore.remove("catSrchVal");
				var cat_search	=	$scope.srchCats_val;			
			}else{ 
				var cat_search	=	$scope.srchCats_val;			
			}
			
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			
			APIServices.fetchcategoryPaidData(returnState.stateParam,returnState.whichParam,pageShow,cat_search,returnState.parid).success(function(response) {
				$scope.categoryPaidData	=	[];	
				$scope.categoriespaid	=	[];
				
				if(pageShow == '' || pageShow == null) {	
					$scope.categoryPaidData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {	
					$scope.categoryPaidData.data = response.data;
					$scope.categoryPaidData.count = response.count;
					$scope.categoryPaidData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.categoryPaidData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.categoryPaidData.totCount)/$scope.showPageNum);
				setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},500);
			});
		};
		
		
		//Function to fetch contracts of given Category
		$scope.srchCat	=	function(event,$page) { 
			var pageShow = '';
			if(typeof $page==='undefined') {
				pageShow = '';
			} else {
				pageShow = $page;
			}
			var cat_search	=	$('.srchCat').val(); 
			$cookieStore.put('catSrchVal',$('.srchCat').val());
			var scopeUrlExp	=	$location.url().split('/');
			APIServices.fetchcategoryPaidData(returnState.stateParam,returnState.whichParam,pageShow,cat_search,returnState.parid).success(function(response) {
				$scope.categoryPaidData	=	[];	
				$scope.categoriespaid	=	[];
				if(pageShow == '' || pageShow == null) {	
					$scope.categoryPaidData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {	
					$scope.categoryPaidData.data = response.data;
					$scope.categoryPaidData.count = response.count;
					$scope.categoryPaidData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.categoryPaidData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.categoryPaidData.totCount)/$scope.showPageNum);
				setTimeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},500);
			});
		};
		
		if($cookieStore.get("thisPage") == 'categoryPaidData') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'categoryPaidData');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.currentPage = n;
			$scope.selectedIndex	=	n;
		};
		
		$scope.openCatdiv	=	function(event) {
			$('.catupeven').addClass('hide');
			$(event.target).closest('.contDivs').find('.catupeven').removeClass('hide');
			$(event.target).closest('.contDivs').find('.loadingMini').removeClass('hide');
		};
	});
	
	
	// controller used in reversed retention template
	tmeModuleApp.controller('retentionController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout,$http,transformRequestAsFormPost,$rootScope,$filter,$mdDialog,$state) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.dispdate = ''; 
		$scope.compFilter = '';
		$scope.model = {};
		$scope.retention_model={};
		$scope.dispSelected = '';
		$scope.retention_model.skip_month = "";
		$scope.retention_model.month_option = 1;
		//~ $scope.month_arr = {'january':'January','february':"February","march":"March","april":"April","may":"May","june":"June","july":"July","august":"August","september":"September","october":"October","november":"November","december":"December"};
		$scope.month_arr = ["January","February","March","April","May","June","July","August","September","October","November","December"];
		$scope.close_popup = function() {
			$scope.showmelist = false;
			$scope.showoption = false;
			$scope.showoverlay = false;
			$scope.showdaterange = false;
		}
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			
			$scope.showReqTabs	=	function(event) {
				$('.tmeStatus').addClass('hide');
				$(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');				
			};
			
			$scope.showmelist = false;
			$scope.showoption = false;
			$scope.showoverlay = false;
			//function that does the requested operations like retain,stop
			$rootScope.requestToDo	=	function(event,contract,status,st,index,selfFlag,$data_city) { 
				var confirmMsg	=	"";
				$scope.selected_mon = '';
				if(selfFlag == 0) {
					switch(status) {
						case 9:
							confirmMsg	=	"Are you sure, you want to stop the ECS?";
						break;
						case 5:
							confirmMsg	=	"Are you sure, client agreed to continue for ECS?";
						break;
						case 4:
							confirmMsg	=	"Are you sure to mark it as Follow Up? Pls Confirm";
						break;
						case 16	:
							confirmMsg	=	"Are you sure to mark it as Upgrade? Pls Confirm";
							$rootScope.goToBform(contract);
						break;
						case 17	:
							confirmMsg	=	"Are you sure to mark it as Degrade? Pls Confirm";
							$rootScope.goToBform(contract);
						break;
						case 21	:
							confirmMsg	=	"Are you sure to mark it as Ringing?";
						break;
						case 23	:
							confirmMsg	=	"Are you sure to mark it as Not Contactable-ECS Continued?";
						break;
						case 24	:
							confirmMsg	=	"Are you sure, you want to skip ECS for "+$scope.retention_model.month_option+" month ?";
						break;
						case 25	:
							confirmMsg	=	"Are you sure to mark it as Ignore Request?";
						break;
						case 26	:
							confirmMsg	=	"Are you sure to mark it as Invalid Data?";
						break;
						case 27	:
							confirmMsg	=	"Are you sure to mark it as Ecs Clarification Call?";
						break;
						case 35	:
							confirmMsg	=	"Are you sure to Send the Business CloseDown Validation Request?";
						break;
						case 38	:
							confirmMsg	=	"Are you sure to Send to Web Suppert Team?";
						break;
					}
					if(confirm(confirmMsg)) {                                                                                          
						var request	=	$http({
							method : "POST",
							url : "../tmAlloc/ajaxData.php",
							headers: {'Content-Type': 'application/x-www-form-urlencoded'},
							transformRequest: function(obj) {
								var str = [];
								for(var p in obj)
								str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
								return str.join("&");
							},
							data: {
								parentid:contract.contractid,
								action_flag: status,
								status: status,
								st : st,
								compname :contract.compname,
								tmename :UNAME,
								ucode : USERID,
								flag :'new',
								ecs_skip : $scope.retention_model.month_option
							}
						});
						
						request.success(function(response) {
							if(response == 1 && status == 5){
								$scope.showoverlay = true;
								$scope.showoption = true;
								$scope.retainedparentid = contract.contractid;
							}else if($.trim(response) === "ECS") {
								window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(contract.compname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state="+contract.state+"&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&insert_date="+contract.date_str+"&flag=new&tmename="+contract.tmename);
							} else if($.trim(response) == 'CCSI') {
								window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(contract.compname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state="+contract.state+"&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&insert_date="+contract.date_str+"&flag=new&tmename="+contract.tmename);
							} else if ($.trim(response) == 'NONE'){
								alert("Nothing to Stop");
							}else if ($.trim(response) == 'REQUEST_SENT'){
									alert("The Request for Business CloseDown Validation has been sent successfully");
								}else if ($.trim(response) == 'REQUEST_NOT_SENT'){
									alert("The Request for Business CloseDown Validation has Failed. Try again Later !!");
								}else if ($.trim(response) == 'WEBTEAM_REQ_SENT'){
									alert("The Contract has been Transferred to Web Support Team Successfully");
								}else if ($.trim(response) == 'WEBTEAM_REQ_NOT_SENT'){
									alert("Transfer to Web Support Team Failed. Try again Later !!");
								}else if(response == 3){
								alert("ECS is not paused,Please try again");
							} else {
								alert("The Status has been updated successfully");
								$scope.showoverlay = false;
								$scope.showdaterange = false;
								window.location.href	=	'retention';
							}
							
							$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
						});
					} else {
						return false;
					}
				} else {
					var request	=	$http({
						method : "POST",
						url : "../tmAlloc/ajaxData.php",
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						transformRequest: function(obj) {
							var str = [];
							for(var p in obj)
							str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
							return str.join("&");
						},
						data: {
							parentid: contract.contractid,
							action_flag: status,
							status: status,
							st : st,
							compname :contract.compname,
							tmename :UNAME,
							ucode : USERID,
							flag :'new'
						}
					});
					
					request.success(function(response) {
						if(response == 1 && status == 5){
							$scope.showoverlay = true;
							$scope.showoption = true;
						}else if($.trim(response) === "ECS") {
							window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(contract.compname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state="+contract.state+"&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&flag=new&tmename="+contract.tmename);
						} else if($.trim(response) == 'CCSI') {
							window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(contract.compname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state="+contract.state+"&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&flag=new&tmename="+contract.tmename);
						} else if ($.trim(response) == 'NONE'){
							alert("Nothing to Stop");
						} else {
							alert("The Status has been updated successfully");
							window.location.href	=	'retention';
						}
						
						$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
					});
				}
			};
			
			
			$rootScope.showdatepopup = function(contract) {
				$scope.showoverlay = true;
				$scope.showdaterange = true;
				$scope.main_arr = contract;
			}
			
			$scope.showmepopup = function(flag) {
				if(flag == 'notinvoled') {
					$scope.showoption = false;
					$scope.showoverlay = false;
				}else if(flag == 'involed'){
					$scope.showoption = false;
					$scope.showmelist = true;
				}
			};
			
			$scope.searchedme = [];
			$scope.me_name_Arr = [];
			$scope.searchedme[0] ='';
			
			$scope.storeme = function(parentid) {
				if($scope.searchedme[0] == '') {
					alert('Please select a ME'); 
					return false;
				}
				$scope.me_name_Arr = $scope.searchedme[0].split('(');
				var meArr_length = $scope.me_name_Arr.length - 1;
				$scope.MeCode = $scope.me_name_Arr[meArr_length];
				$scope.final_mecode = $scope.MeCode.slice(0, -1);
				
				APIServices.insertmename($scope.searchedme[0],parentid,$scope.final_mecode).success(function(response) {
						if(response.errorCode == 0) {
							alert("selected successfully");
							$scope.searchedme[0] ='';
							$scope.showoverlay = false;
							$scope.showoption = false;
							$scope.showmelist = false;
						}else {
							alert('please try again');
						}			
				});
			};
			
			//function used for storing comments
			$scope.submitComm	=	function($parentid) {
				var tmeComment	=	this.tmeCommentMsg;
				APIServices.fetchTmeComments($parentid,2,tmeComment).success(function(response) {
					if(response.results.errorCode	==	0) {
						alert('Comment Successfully Updated');
					} else {
						alert('Comment Not Updated. Please try again later');
					}
					$('.tmeComment').addClass('hide');
				});
		   };
	 	
	 	   // function that is used while requesting for reactivation
			$scope.reactivaterequest = function(contractinfo,event,$index) {
				if(confirm("Are you sure You want to Reactivate this contract") == true ) {
					if($scope.viewParam ==2) {
						$scope.showDataModeTab(event,10,contractinfo.contractid,contractinfo.compname,$index)
					}
					$('.Opaque_retentionWindow_add').addClass('hide');
					$(event.target).closest('.contDivs').find('.Opaque_retentionWindow_add').removeClass('hide');
				}
			}
			
			//function used for sorting date 
			$scope.sortdate = function(flag) {
				if(flag == 'asc') {
					$rootScope.retention.data = $filter('orderBy')($rootScope.retention.data,'date');
				} else if (flag == 'desc') {
					$rootScope.retention.data = $filter('orderBy')($rootScope.retention.data,'-date')
				}
			}
			
			//function that is used for storing reactivation comments 
			$rootScope.setReactivateComment =function(event,$contract,$index) {
				var $currComment	=	this.ReactivateComment;
				if(typeof $currComment	===	"undefined") {
					alert("Please enter a comment");
					return false;
				} else {
					var Comment = $contract.tme_comment;
					var finComment	=	$currComment;   
					APIServices.StoreCommentretention(finComment,$contract.contractid,USERID).success(function (response) {
						$('.Opaque_retentionWindow_add').addClass('hide');
						$scope.retention.data[$index]['tme_comments']	=	response.results.retData;
						$scope.newComment	=	"";
					});
					APIServices.reactivaterequest($contract.contractid,$contract.tmecode,$contract.tmename).success(function(response) {
						if(response == 0) {	
							alert("Your request has been updated");
						} else {
							alert("Please try again");
						}
					});
						
				}
			};
			
			//retrives the retention data 
			APIServices.fetchReversedRetentionData(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.login_user = USERID;
				$rootScope.retention	=	[];
				if(pageShow == '' || pageShow == null) {
					$rootScope.retention = response;
					$rootScope.temparr = response.data;
					$rootScope.mainPage = scopeUrlExp[1];
				} else {	
					$rootScope.retention.data = response.data;
					$rootScope.temparr = response.data;
					$rootScope.retention.count = response.count;
					$rootScope.retention.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.retention.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.retention.totCount)/$scope.showPageNum);
				$timeout(function() {
					if($('.pageSlider').outerWidth() < $('.setTotContWidthPage').outerWidth()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
			});
			
			//function used for searching in list view 
			$scope.checktext = function() {
				if($('#view2searchbox').val() != '') {
					$rootScope.retention.data = $filter('filter')($rootScope.temparr,$('#view2searchbox').val());
					$scope.showx = true;
				}else if($('#view2searchbox').val() == '')	{
					 $rootScope.retention.data = $rootScope.temparr;
				}
			};
			
			//function used to for clearing the search box in list view
			$scope.clearsearch = function() {
				$('#view2searchbox').attr('value','');
				$scope.showx = false;
				$rootScope.retention.data = $rootScope.temparr;
			}
			
			$scope.commentType	=	function(event,$parentid,$commentAction) {
				if($commentAction	==	1) {
					$scope.commentMsg	=	'Add';
				} else {
					$scope.commentMsg	=	'Submit';
				}
				$('.Opaque_commentWindow_add').addClass('hide');
				$(event.target).closest('.contDivs').find('.Opaque_commentWindow_add').removeClass('hide');
			};
		
			$rootScope.setComment =function(event,$parentid,$index,$onsubmit) {
				var $currComment	=	this.setCommentWorked;
				if(typeof $currComment	===	"undefined") {
					alert("Please enter a comment");
					return false;
				} else {
					var Comment = $onsubmit;
					var finComment	=	$currComment;   
					APIServices.StoreCommentretention(finComment,$parentid,USERID).success(function (response) {
						$('.Opaque_commentWindow_add').addClass('hide');
						$scope.retention.data[$index]['tme_comments']	=	response.results.retData;
						$scope.newComment	=	"";
						alert('Comment Added Successfully');
					});
				}
			};
			
			$scope.attachvlc	=	function($parentid,$data_city,$reminder){
				if($parentid !=''){
					APIServices.SendVLC($parentid,$data_city,$reminder).success(function(response){
						if(response.errorCode	==	0) {
							var resp=response.data.split('~');
							
							if(resp['0'] == '1') {
								alert('Data sent to the vendor successfully');
								$(obj).attr('disabled',true);
							} else if(resp['0'] == '0'){
								alert('There is some issue while sending data to vendor. Please try again later');
							} else if(resp['0'] == '2'){
								var name	= resp['1'];
								var up_stat	= resp['2'];
								if(up_stat == 1) {
									var msg = 'Video id also Already uploaded';
								} else {
									var msg =' Do you want to send the reminder mail? ';
								}
								var res = confirm("Data already sent to the vendor '"+name+"'"+msg);
								if(res){
									$reminder='1';
									APIServices.SendVLC($parentid,$data_city,$reminder).success(function(response){
										if(response.errorCode	==	0) {
											alert("Remainder sent to the vendor");
										} else {
											alert("Remainder was not sent. Please contact software team for contract "+$parentid);
										}
									});
								} else {
									return false;
								}
							} else if(resp['0'] == '3'){
								alert("No vendor found for the pincode of the contract. Mail sent to the web team for assigning to the vendor");
							} else if(resp['0'] == '4'){
								alert("No vendor found for the pincode of the contract. No mail sent to the web team. Please contact software team");
							} else if(resp['0'] == '5'){
								alert("The contract is already assigned to "+resp['1']+" which is deactivated. Mail sent to the co-ordinator");
							} else if(resp['0'] == '10'){
								alert("Video is already uploaded for the contract.");
							}
						} else {
							alert('VLC was not sent. Please contact softwware team for contract ' + $parentid);
						}
					});
				}
			}
			
		};
		
		if($cookieStore.get("thisPage") == 'retention') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'retention');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
		
		$scope.OpenUploadedFiles = function(ev,uploadArr)
		{
			$rootScope.csgenio_url = $scope.decsURL;
			$rootScope.filename.length = 0;
			$.each(uploadArr, function(key, value)
			{
				 $rootScope.filename.push({'file' : value.filename,'display_file' : value.filename+" - "+value.update_date});
			});
			
			$mdDialog.show({
				 controller: Transfer_EmailUploadsController,
				 templateUrl: 'partials/Transfer_EmailUploads.html',
				 parent: angular.element(document.body),
				 targetEvent: ev,
				 clickOutsideToClose:false
			})
		};
		
		function Transfer_EmailUploadsController($scope, $mdDialog, $rootScope,APIServices) {
		$scope.uploaded_details = [];
		$scope.uploaded_details = $rootScope.filename;
		$scope.total_files = $rootScope.filename.length;
		
		$scope.open_uploadedFile = function(file){
			var OpenFile = "http://"+$rootScope.csgenio_url+"/transfer_email_uploads/"+file;
			window.location.href = OpenFile;
		};
		
		$scope.transfer_mdDialog_hide = function() {
			  $mdDialog.hide();
			};
		}
	});
	
	//Controller used for Deal Closed Report Data
	tmeModuleApp.controller('dealClosedRepController',function($scope,APIServices,returnState,$location,$cookieStore,$state,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			$scope.extraVals	=	returnState.extraVals;
			if(!returnState.parid) {
				$scope.currentPage	=	returnState.currentPage;
				
				$cookieStore.put('currPageReport',returnState.currentPage);
			} else {
				$scope.currentPage	=	'dealClosedRep';
			}
			$scope.userid=USERID;
			// Service identifier for Fetching Appointment Data
			APIServices.dealClosedReportData(returnState.stateParam,returnState.whichParam,pageShow,returnState.extraVals,returnState.parid).success(function(response) {
				$scope.reportData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.reportData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.reportData.data = response.data;
					$scope.reportData.count = response.count;
					$scope.reportData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.reportData.totCount	=	response.counttot;
				$scope.pageContracts	=	Math.ceil(parseInt($scope.reportData.totCount)/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.setwidthList();
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
			});
			
			$scope.reportOrder	= function(srchParam,orderFlag){ 
				var currentPage 	=	$scope.currentPage;
				var extraVals 	=	$scope.extraVals;  
				$state.go('appHome.filter',{srchparam:srchParam,srchWhich:orderFlag,currPage:currentPage,extraVals:extraVals}); 
			}
		};
		if($cookieStore.get("thisPage") == 'dealClosed') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'dealClosed');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.singleCheque	=	function(event,$parentid,$compName) {
			APIServices.getSingleChequeStat($parentid).success(function (response) {
				if(response.errorCode	==	0) {
					document.forms.add_multicity_cheque.parentid.value = $parentid;
					document.forms.add_multicity_cheque.compname.value = $compName;
					document.forms.add_multicity_cheque.submit();
				} else {
					alert('This contract does not have Single Cheque');
				}
				return false;
			});
		};
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Phone Search
	tmeModuleApp.controller('phoneSrchController',function($scope,APIServices,returnState,$location,$cookieStore,$rootScope,$timeout,$http,$mdDialog) {
		$scope.login_user = USERID;
		$scope.allocContracts	=	[];
		$scope.emp_secondaryAllocId = [];
		var loadNumber	=	0;
		$scope.retention_model={};
		$scope.retention_model.skip_month = "";
		$scope.retention_model.month_option = 1;
		
		$scope.close_popup = function() {
			$scope.showmelist = false;
			$scope.showoption = false;
			$scope.showoverlay = false;
			$scope.showdaterange = false;
		}
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			$rootScope.srchNumber	=	returnState.parid;
			APIServices.fetchPhoneCompany(pageShow,returnState.parid).success(function(response) {
				if(pageShow == '' || pageShow == null) {
					$rootScope.phoneSrch = response;
					
					$rootScope.phoneSrch.empAlloc_id = $rootScope.employees.results.allocId;
					$scope.emp_secondaryAllocId = $rootScope.employees.results.secondary_allocID.split(',');
					$rootScope.phoneSrch.sec_alloc_id = $scope.emp_secondaryAllocId.indexOf("RD");
					
					$scope.mainPage = scopeUrlExp[1];
				} else {
					angular.forEach(response.data.own,function(eachConts,key) {
						$rootScope.phoneSrch.data.own.push(eachConts);
					});
					angular.forEach(response.data.other,function(eachContsOth,key2) {
						$rootScope.phoneSrch.data.other.push(eachContsOth);
					});
					$rootScope.phoneSrch.count = response.count;
				}
			});
		};
		$scope.loadAlloc(returnState);
		
		$scope.tmeCommentBoxOpen	=	function(event,$parentid,$index) {
			$scope.tmeCommentMsg	=	'';
			APIServices.fetchTmeComments($parentid,1).success(function(response) {
				$('.tmeComment').addClass('hide');
				$(event.target).closest('.contDivs').find('.tmeComment').removeClass('hide');
				if(response.errorCode	==	0) {
					$scope.tmeCommentMsg	=	response.data.tme_comment;
					if(response.data.tme_comment	==	'' || response.data.tme_comment == null) {
						$scope.tmeCommStat	=	'Add';
					} else {
						$scope.tmeCommStat	=	'Edit';
					}
				}
			});
		};
		
		$scope.submitComm	=	function($parentid) {
			var tmeComment	=	this.tmeeditMsg;
			APIServices.StoreCommentretention(tmeComment,$parentid,USERID).success(function(response) {
				if(response.results.errorCode	==	0) {
					alert('Comment Successfully Updated');
				} else {
					alert('Comment Not Updated. Please try again later');
				}
				$('.tmeComment').addClass('hide');
			});
		};
		
		$rootScope.requestToDo	=	function(event,contract,status,st,index,selfFlag,$data_city) {
			var confirmMsg	=	"";
			if(selfFlag == 0) {
				switch(status) {
					case 9:
						confirmMsg	=	"Are you sure, you want to stop the ECS?";
					break;
					case 5:
						confirmMsg	=	"Are you sure, client agreed to continue for ECS?";
					break;
					case 4:
						confirmMsg	=	"Are you sure to mark it as Follow Up? Pls Confirm";
					break;
					case 16	:
						confirmMsg	=	"Are you sure to mark it as Upgrade? Pls Confirm";
						$rootScope.goToBform(contract);
					break;
					case 17	:
						confirmMsg	=	"Are you sure to mark it as Degrade? Pls Confirm";
						$rootScope.goToBform(contract);
					break;
					case 21	:
						confirmMsg	=	"Are you sure to mark it as Ringing?";
					break;
					case 23	:
							confirmMsg	=	"Are you sure to mark it as Not Contactable-ECS Continued?";
					break;
					case 24	:
						confirmMsg	=	"Are you sure, you want to skip ECS for "+$scope.retention_model.month_option+" month ?";
					break;
					case 25	:
						confirmMsg	=	"Are you sure to mark it as Ignore Request?";
					break;
					case 26	:
						confirmMsg	=	"Are you sure to mark it as Invalid Data?";
					break;
					case 27	:
						confirmMsg	=	"Are you sure to mark it as Ecs Clarification Call?";
					break;
					case 35	:
						confirmMsg	=	"Are you sure to Send the Business CloseDown Validation Request?";
					break;
					case 38	:
						confirmMsg	=	"Are you sure to Send to Web Suppert Team?";
					break;
				}
				if(confirm(confirmMsg)) {
					var request	=	$http({
						method : "POST",
						url : "../tmAlloc/ajaxData.php",
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						transformRequest: function(obj) {
							var str = [];
							for(var p in obj)
							str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
							return str.join("&");
						},
						data: {
							parentid:contract.contractid,
							action_flag: status,
							status: status,
							st : st,
							req_source: 'phone search',
							compname :contract.companyname,
							tmename :UNAME,
							ucode : USERID,
							flag :'new',
							ecs_skip : $scope.retention_model.month_option
						}
					});
					
					request.success(function(response) {
						if(response == 1 && status == 5){
							$scope.showoverlay = true;
							$scope.showoption = true;
							$scope.retainedparentid = contract.contractid;
						}else if($.trim(response) === "ECS") {
							window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(contract.companyname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state=2&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&insert_date="+contract.date_str+"&flag=new&tmename="+UNAME);
						} else if($.trim(response) == 'CCSI') {
							window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(contract.companyname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state=2&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&insert_date="+contract.date_str+"&flag=new&tmename="+UNAME);
						} else if ($.trim(response) == 'NONE'){
							alert("Nothing to Stop");
						}else if ($.trim(response) == 'REQUEST_SENT'){
									alert("The Request for Business CloseDown Validation has been sent successfully");
							}else if ($.trim(response) == 'REQUEST_NOT_SENT'){
								alert("The Request for Business CloseDown Validation has Failed. Try again Later !!");
							}else if ($.trim(response) == 'WEBTEAM_REQ_SENT'){
								alert("The Contract has been Transferred to Web Support Team Successfully");
							}else if ($.trim(response) == 'WEBTEAM_REQ_NOT_SENT'){
								alert("Transfer to Web Support Team Failed. Try again Later !!");
							}else if(response == 3){
							alert("ECS is not paused,Please try again");
						} else {
							alert("The Status has been updated successfully");
							$scope.showoverlay = false;
							$scope.showdaterange = false;
						}
						
						$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
					});
				} else {
					return false;
				}
			} else {
				var request	=	$http({
					method : "POST",
					url : "../tmAlloc/ajaxData.php",
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					transformRequest: function(obj) {
						var str = [];
						for(var p in obj)
						str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
						return str.join("&");
					},
					data: {
						parentid: contract.contractid,
						action_flag: status,
						status: status,
						st : st,
						compname :contract.companyname,
						tmename :UNAME,
						ucode :USERID,
						req_source: 'phone search', 
						flag :'new'
					}
				});
				
				request.success(function(response) {
					if(response == 1 && status == 5){
						$scope.showoverlay = true;
						$scope.showoption = true;
					}else if($.trim(response) === "ECS") {
						window.open("http://"+$scope.decsURL+"/paid/stopEcsTme.php?compName="+encodeURIComponent(contract.companyname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state=2&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&flag=new&tmename="+UNAME);
					} else if($.trim(response) == 'CCSI') {
						window.open("http://"+$scope.decsURL+"/paid/stopSiTme.php?compName="+encodeURIComponent(contract.companyname)+"&parent_id="+contract.contractid+"&ecsstatus=1&billDeskId=&pgg=2&ucode="+USERID+"&state=2&escalated_details="+contract.escalated_details+"&city="+DATACITY+"&flag=new&tmename="+UNAME);
					} else if ($.trim(response) == 'NONE'){
						alert("Nothing to Stop");
					} else {
						alert("The Status has been updated successfully");
					}
					
					$(event.target).closest('.contDivs').find('.tmeStatus').addClass('hide');				
				});
			}
		}
				
		$rootScope.showdatepopup = function(contract) {
				$scope.showoverlay = true;
				$scope.showdaterange = true;
				$scope.main_arr = contract;
		}
			
		$scope.showReqStop	=	function(event,contract) {
			if(contract.action_flag == 9 && contract.state == 3)
			{
				alert('This Contract is already present in HOD Module. You Cannot Perform any actions in it !!');
				return false;
			}
			else
			{
				$rootScope.contract = contract;
				if(contract.EcsUpdate_Flag == 0)
				{
					if(contract.tmecode != USERID && contract.tmecode != '')
					{
						 $mdDialog.show({
							 //~ locals:{dataToPass: contract},   
							controller: EcsLeadRemainder,
							templateUrl: 'partials/DialogToSendRemainderEcs.html',
							 parent: angular.element(document.body),
							clickOutsideToClose:false
						});
					}
					else
					{
						$('.tmeStatus').addClass('hide');
						$(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');
					}
				}
				else
				{
					$('.tmeStatus').addClass('hide');
					$(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');
				}
			}
			
			//~ $('.tmeStatus').addClass('hide');
			//~ $(event.target).closest('.contDivs').find('.tmeStatus').removeClass('hide');
		};
		
		$scope.showmepopup = function(flag) {
			if(flag == 'notinvoled') {
				$scope.showoption = false;
				$scope.showoverlay = false;
			}else if(flag == 'involed'){
				$scope.showoption = false;
				$scope.showmelist = true;
			}
		};
			
		$scope.searchedme = [];
		$scope.me_name_Arr = [];
		$scope.searchedme[0] ='';
		
		$scope.storeme = function(parentid) {
			if($scope.searchedme[0] == '') {
				alert('Please select a ME'); 
				return false;
			}
			
			$scope.me_name_Arr = $scope.searchedme[0].split('(');
			var meArr_length = $scope.me_name_Arr.length - 1;
			$scope.MeCode = $scope.me_name_Arr[meArr_length];
			$scope.final_mecode = $scope.MeCode.slice(0, -1);
				
			APIServices.insertmename($scope.searchedme[0],parentid,$scope.final_mecode).success(function(response) {
					if(response.errorCode == 0) {
						alert("selected successfully");
						$scope.searchedme[0] ='';
						$scope.showoverlay = false;
						$scope.showoption = false;
						$scope.showmelist = false;
					}else {
						alert('please try again');
					}			
								
			});
		};
			
		
		$scope.clickLoad	=	function() {
			loadNumber	=	loadNumber+1;
			$scope.loadAlloc(returnState,loadNumber);
		};
		
		
		function EcsLeadRemainder($scope, $mdDialog, $rootScope,APIServices) {
			
			//~ $scope.parentid			=	$rootScope.ecs_parid;
			//~ $scope.ecs_empcode			=	$rootScope.ecs_empcode;
			//~ 
			//~ $scope.ecs_contract_details = [];
			//~ APIServices.FetchEcsDetailsForm($scope.parentid,$scope.ecs_empcode).success(function(response) {
				//~ if(response.errorCode	==	0){
					$scope.ecs_contract_details = $rootScope.contract;
					
				//~ }
				//~ else{
					//~ $scope.ecs_contract_details = response.errorCode;
					//~ $mdDialog.hide();
					//~ alert('No Details Found !!');
					//~ return false;
					//~ 
				//~ }
				
				$scope.send_ecs_lead_remainder = function(contract) {
						APIServices.SendRemainderEcsLead(contract.contractid,contract.action_flag,contract.companyname).success(function(response) {
							if(response.errorCode == 0)
							{
								alert('Remainder Sent Successfully !!');
								return false;
							}
							else
							{
								alert('Process Failed !!');
								return false;
							}
							
						});
					};
				
				 $scope.Retention_dialog_hide = function() {
					  $mdDialog.hide();
					};
			//~ });
		}
		
	});
	
	//Controller used when Searched for Category
	tmeModuleApp.controller('categorySearchController',function($scope,APIServices,returnState,$location,$cookieStore,$rootScope,$timeout) {
		$rootScope.catSrch	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			$rootScope.srchCategory	=	returnState.parid;
			APIServices.fetchCategoryCompany(pageShow,returnState.parid).success(function(response) {
				if(pageShow == '' || pageShow == null) {
					$rootScope.catSrch = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					angular.forEach(response.data,function(eachConts,key) {
						$rootScope.catSrch.data.push(eachConts);
					});
					$rootScope.catSrch.count = response.count;
				}
			});
		};
		$scope.loadAlloc(returnState);
		
		$scope.clickLoad	=	function() {
			loadNumber	=	loadNumber+1;
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//Controller used for Expired Ecs Data
	tmeModuleApp.controller('expiredDataEcsController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchExpiredDataEcs(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.expiredData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.expiredData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.expiredData.data = response.data;
					$scope.expiredData.count = response.count;
					$scope.expiredData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.expiredData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.expiredData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'expiredDataEcs') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'expiredDataEcs');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	//Controller used for Expired Ecs Data
	tmeModuleApp.controller('expiredDataNonEcsController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchExpiredDataNonEcs(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.expiredData	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.expiredData = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.expiredData.data = response.data;
					$scope.expiredData.count = response.count;
					$scope.expiredData.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.expiredData.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.expiredData.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'expiredDataNonEcs') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'expiredDataNonEcs');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	tmeModuleApp.controller('deliverySystemController',function($scope,APIServices,returnState,$location,$cookieStore,$timeout) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		
		$scope.loadAlloc	=	function(returnState,$page) {
			if(typeof $page==='undefined') {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Expired Data
			APIServices.fetchdeliverySystem(returnState.stateParam,returnState.whichParam,pageShow,returnState.parid).success(function(response) {
				$scope.deliverySystem	=	[];
				if(pageShow == '' || pageShow == null) {
					$scope.deliverySystem = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					$scope.deliverySystem.data = response.data;
					$scope.deliverySystem.count = response.count;
					$scope.deliverySystem.errorCode = 0;
				}
				$scope.selectedIndex	=	pageShow;
				$scope.deliverySystem.totCount	=	response.counttot;	
				$scope.pageContracts	=	Math.ceil($scope.deliverySystem.totCount/$scope.showPageNum);
				var timer = setTimeout(function() {
					if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
						var selectedLeft	=	$('.selectedPage').position().left;
						$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
					}
				},300);
				$scope.$on("$destroy", function() {
					if (timer) {
						$timeout.cancel(timer);
					}
				});
				if($scope.viewParam	==	2) {
					$scope.setwidthList();
				}
			});
			$('html, body').animate({scrollTop: 0}, 1000);
		};
		if($cookieStore.get("thisPage") == 'deliverySystem') {
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
			$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
		} else {
			$cookieStore.put("thisPage",'deliverySystem');
			$cookieStore.put("pageNo",'');
			$scope.loadAlloc(returnState);
		}
		
		$scope.clickLoad	=	function(n) {
			loadNumber	=	n;
			$cookieStore.put("pageNo",n);
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
	
	
	//Controller used when user is redirected to home page after giving a disposition
	tmeModuleApp.controller('redirectDisposeController',function($scope,APIServices,returnState,$rootScope,$cookieStore) {
		if(returnState.stVal == null) {
			window.location.href	=	'welcome';
		} else {
			
			APIServices.insertDataAlloc(returnState.parid,returnState.stVal,DATACITY).success(function(response) {
				if(STATID && $.inArray(parseInt(STATID),$rootScope.autoDialer) !== -1) {
					disposition_set_mktg(returnState.stVal,$rootScope.employees.remoteAddr,response);
				}else{
					if(response.live_flg == '1') {
						window.location.href=response.live_url;
					}else {
						if(response.errorCode	==	0) {
							window.location.href	=	'welcome';
						} else {
							alert('Dispositions not saved. Please try again');
							window.location.href	=	'welcome';
						}
				   }
				}
			});
		}
	});

	tmeModuleApp.controller('dealClosedController',function($scope,APIServices,$cookieStore){
		$scope.dealClosedCount	=	[];
		APIServices.getDealClosed($scope.id).success(function(response) {
			$scope.dealClosedCount	=	response;
		});
	});
	
	tmeModuleApp.controller('learningcenterController',function($scope,APIServices,returnState,$rootScope,$cookieStore,$sce,$timeout){
		//function for onload pop ups
		$scope.onloadPopUps();
		 /*****knowledge transfer module start******/
		$cookieStore.put('showlearn',2);
        $rootScope.search_title={};
		$rootScope.searchpara='all';
		$rootScope.search_title.search='';
		$rootScope.emptype='';
		$scope.searchmediatype={};
		$rootScope.search_title={};
		$rootScope.searchpara='all';
		$rootScope.search_title.search='';
		$scope.searchmediatype.searchmediavalue='all';
		$scope.media_index=0;
		$rootScope.selectedterm=0;
		$scope.team_type="";
		$scope.cityarray=['Mumbai','Delhi','Kolkata','Bangalore','Chennai','Pune','Hyderabad','Ahemdabad','all','mumbai','delhi','kolkata','bangalore','chennai','pune','hyderabad','ahemdabad'];
		$rootScope.city_selected=DATACITY;

		
			$rootScope.empType='TME';
		
		 if ($scope.cityarray.indexOf(DATACITY)==-1){
			//$rootScope.city_selected='remote';
			$rootScope.city_selected = LOGIN_CITY;
		}
		if($rootScope.selectedterm==0){
				$rootScope.search_title.search='';
			}
			else if($rootScope.selectedterm==1){
				$scope.search_title.search=document.getElementById("textsearch").value;
			}
			$scope.pageno_count=function(page){
				$timeout(function (){
					if($rootScope.selectedterm==0){
						$rootScope.search_title.search='';
					}
					else if($rootScope.selectedterm==1){
						$scope.search_title.search=document.getElementById("textsearch").value;
					}
				$scope.currentPage  = 1;
				$scope.pagearr = [];
				var value = 1;
					APIServices.fetchall_tmegenio_count($rootScope.searchpara,$rootScope.search_title.search,$rootScope.city_selected,$rootScope.empType,$scope.team_type).success(function(response) {
						$rootScope.count        =   response.total;
						$scope.numOfpages   =   Math.ceil($rootScope.count/20);
						if($scope.numOfpages == 1){

							$scope.pagearr[0]=1;
						}else{
							for(var u = 1; u <= $scope.currentPage+1 ;u++){
								$scope.pagearr += u;
							}
						}
					});
					}, 300);
		};
		
		$scope.filterchange = function(){
				$rootScope.searchpara=$scope.searchmediatype.searchmediavalue;
				$rootScope.upload_module_show(1,$rootScope.searchpara,$rootScope.search_title.search,$rootScope.city_selected,$rootScope.empType);
			};
			 APIServices.teamtype().success(function(response) {
						if(typeof response.team.allocId ==='undefined' || response.team.allocId == null || response.team.allocId == "" ){
							$scope.team_type="Others";
						}
						else {
								$scope.team_typecode=response.team.allocId;
								if($scope.team_typecode=="OTH"){
									$scope.team_type="Others";
								}
								if($scope.team_typecode=="BD"){
									$scope.team_type="Bounce";
								}
								if($scope.team_typecode=="HD"){
									$scope.team_type="Hot Data";
								}
								if($scope.team_typecode=="O"){
									$scope.team_type="Online";
								}
								if($scope.team_typecode=="RD"){
									$scope.team_type="Retention";
								}
								if($scope.team_typecode=="BE"){
									$scope.team_type="Revival";
								}
								if($scope.team_typecode=="S"){
									$scope.team_type="Super";
								}
								if($scope.team_typecode=="SJ"){
									$scope.team_type="Super Cats";
								}
								if($scope.team_typecode==""){
									$scope.team_type="All";
								}
							
						}
			APIServices.fetchall_tmegenio_mandatory_popup($scope.team_type).success(function(response) {
				$scope.check_mandatory=response;
					if($scope.check_mandatory.error.code == 3){
						APIServices.fetchall_tmegenio_mandatory($scope.searchmediatype.searchmediavalue,$rootScope.search_title.search,$rootScope.city_selected,$rootScope.empType,$scope.check_mandatory.result.data.new_media_id,$scope.team_type).success(function(response) {
							$scope.data_tmegenio_mandatory=response;
							if(response.errorCode==0){
								if(response.mandatorycount>0){
									console.log($scope.check_mandatory);
									if($scope.check_mandatory.result.entry_exist==0){
										$scope.showmediamandatorydiv=1;
										$scope.showdivmediamandatoryoverlay=1;
									}
									else if($scope.check_mandatory.result.entry_exist==1){
										$scope.showmediamandatorydiv=0;
										$scope.showdivmediamandatoryoverlay=0;
									}
									$scope.closemediamandatorypopup=0;
									$scope.totalmadatory=response.mandatorycount;
									if($scope.data_tmegenio_mandatory.data[$scope.media_index].media_show=='audio'){
										$scope.audio_show_mandatory="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.trustSrc($scope.data_tmegenio_mandatory.data[$scope.media_index].media_path)+"&employee_id="+USERID+"&media_id="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_id+"&employee_name="+UNAME+"&title="+$scope.data_tmegenio_mandatory.data[$scope.media_index].title+"&media_type="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_type;;
									}
									if($scope.media_index==response.mandatorycount-1){
										$scope.closemediamandatorypopup=1;
									}
								}
								else{
									$scope.showdivmediamandatoryoverlay=0;
									$scope.showmediamandatorydiv=0;
								}
							}
						});	
					}
				});
		});
				
        $rootScope.upload_module_show=function(page,searchpara,search_title,city_seleted){
			if (page ==1 ){
				//~ $scope.pageno_count(page);
			}
			if($rootScope.selectedterm==0){
				$rootScope.search_title.search='';
			}
			else if($rootScope.selectedterm==1){
				$scope.search_title.search=document.getElementById("textsearch").value;
			}
			if(typeof searchpara==='undefined'){
				searchpara='all';
			}
			if(page ==  'prev'){
                    $scope.currentPage  =   $scope.currentPage -1;
                }
                else if (page   ==  'next'){
                   if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
							$scope.pagearr += $scope.currentPage +1;
							$scope.currentPage	=	$scope.currentPage +1;
						}else{
								$scope.currentPage	=	$scope.currentPage +1;
								if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
									$scope.pagearr += $scope.currentPage +1;
								}
							}
                }
                else if(page    ==  1){
                    $scope.currentPage  =   1;
                }else{
                    $scope.currentPage  = page;
						if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
							$scope.pagearr += $scope.currentPage +1;
						}
                    }
                    var value=$scope.currentPage;
                     APIServices.teamtype().success(function(response) {
						if(typeof response.team.allocId ==='undefined' || response.team.allocId == null || response.team.allocId == "" ){
							$scope.team_type="Others";
						}
						else {
								$scope.team_typecode=response.team.allocId;
								if($scope.team_typecode=="OTH"){
									$scope.team_type="Others";
								}
								if($scope.team_typecode=="BD"){
									$scope.team_type="Bounce";
								}
								if($scope.team_typecode=="HD"){
									$scope.team_type="Hot Data";
								}
								if($scope.team_typecode=="O"){
									$scope.team_type="Online";
								}
								if($scope.team_typecode=="RD"){
									$scope.team_type="Retention";
								}
								if($scope.team_typecode=="BE"){
									$scope.team_type="Revival";
								}
								if($scope.team_typecode=="S"){
									$scope.team_type="Super";
								}
								if($scope.team_typecode=="SJ"){
									$scope.team_type="Super Cats";
								}
								if($scope.team_typecode==""){
									$scope.team_type="ALL";
								}
							
						}
					APIServices.fetchall_tmegenio(value,searchpara,$rootScope.search_title.search,$rootScope.city_selected,$rootScope.empType,$scope.team_type).success(function(response) {
						$scope.alldata_tmegenio=response;
						$rootScope.search_title.search='';
						$rootScope.welcomediv=1;
						if(page==1){
								$scope.pagearr = [];
								$scope.count        =   response.total;
								$scope.numOfpages   =   Math.ceil($scope.count/20);
								if($scope.numOfpages == 1){

									$scope.pagearr[0]=1;
								}else{
									$scope.currentPage  = 1;
									for(var u = 1; u <= $scope.currentPage+1 ;u++){
											//~ if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
												$scope.pagearr += u;
											//~ }
									}
									$scope.currentPage  = page;
								}
							}
						});
					});
						
			};
			$rootScope.upload_module_show(1,$rootScope.searchpara,$rootScope.search_title.search,$rootScope.city_selected,$rootScope.empType);
			$scope.closewelcomediv=function(){
				$rootScope.searchpara='all';
				$rootScope.search_title.search='';
				$rootScope.welcomediv=0;
				
			}
			$scope.trustSrc = function(src) {
				return $sce.trustAsResourceUrl(src);
			};
			$scope.showpopupdiv_func = function(index,data_arr){
				$scope.index=index;
				if($scope.alldata_tmegenio.data[index].media_show=='document'){
					 var win = window.open($scope.alldata_tmegenio.data[index].media_path, '_blank');
					 win.focus();
					 return false;
				}
				else if($scope.alldata_tmegenio.data[index].media_show=='audio'){
					$scope.audio_show="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.trustSrc($scope.alldata_tmegenio.data[index].media_path)+"&employee_id="+USERID+"&media_id="+data_arr.media_id+"&employee_name="+UNAME+"&title="+data_arr.title+"&media_type="+data_arr.media_type;
					$scope.showpopupdivaudio=1;
				}
				else{
					$scope.showpopupdiv=1;
				}

			};
			$scope.showpopupdiv_close = function(){
				$scope.showpopupdivaudio=0;
				$scope.showpopupdiv=0;
				$scope.searchmediatype.searchmediavalue='all';
				$rootScope.searchpara='all';
			};
			$scope.increaserow=function(index){
					$scope.alldata_tmegenio.data[index].length_desc=$scope.alldata_tmegenio.data[index].description.length;
				}
				$scope.decreaserow=function(index){
					$scope.alldata_tmegenio.data[index].length_desc=100;
				}
				$scope.showpopupdiv_close_mandatory=function(){
				$scope.showdivmediamandatoryoverlay=0;
				$scope.showmediamandatorydiv=0;
			};
			$scope.nextmedia=function(){
				$scope.media_index++;
				$scope.pevmedia_button=1;
				if($scope.data_tmegenio_mandatory.data[$scope.media_index].media_show=='audio'){
						$scope.audio_show_mandatory="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.trustSrc($scope.data_tmegenio_mandatory.data[$scope.media_index].media_path)+"&employee_id="+USERID+"&media_id="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_id+"&employee_name="+UNAME+"&title="+$scope.data_tmegenio_mandatory.data[$scope.media_index].title+"&media_type="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_type;;
					}
				if($scope.media_index==$scope.totalmadatory-1){
					$scope.closemediamandatorypopup=1;
				}
			};
			$scope.closemedia=function(){
				$scope.showdivmediamandatoryoverlay=0;
				$scope.showmediamandatorydiv=0;
			};
			$scope.premedia=function(){
				if($scope.media_index==1){
					$scope.pevmedia_button=0;
					$scope.media_index--;
					if($scope.data_tmegenio_mandatory.data[$scope.media_index].media_show=='audio'){
						$scope.audio_show_mandatory="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.trustSrc($scope.data_tmegenio_mandatory.data[$scope.media_index].media_path)+"&employee_id="+USERID+"&media_id="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_id+"&employee_name="+UNAME+"&title="+$scope.data_tmegenio_mandatory.data[$scope.media_index].title+"&media_type="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_type;;
					}
				}
				else{
					$scope.media_index--;
					$scope.closemediamandatorypopup=0;
					if($scope.data_tmegenio_mandatory.data[$scope.media_index].media_show=='audio'){
						$scope.audio_show_mandatory="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.trustSrc($scope.data_tmegenio_mandatory.data[$scope.media_index].media_path)+"&employee_id="+USERID+"&media_id="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_id+"&employee_name="+UNAME+"&title="+$scope.data_tmegenio_mandatory.data[$scope.media_index].title+"&media_type="+$scope.data_tmegenio_mandatory.data[$scope.media_index].media_type;;
					}
				}
			};
            /*****knowledge transfer module End******/
		
	});
	
	
	tmeModuleApp.controller('tmecallrecordsController',function($mdDialog,$scope,$filter,APIServices,returnState,$rootScope,$cookieStore,$sce,$timeout){
		
		$scope.login_duration = '';
		$scope.total_empcnt = 1;
		$scope.avg_login_duration = '';
		$scope.trunk_duration = '';
		$scope.break_duration = '';
		$scope.block_duration = '';
		$scope.wrapup_duration = '';
		$scope.inbound_calls = '';
		$scope.manual_calls = '';
		$scope.dialer_calls = '';
		$scope.call_count = '';
		$scope.total_call_per_tme_count = '';
		$scope.final_idle_duration = '';
		$scope.ring_duration = '';
		$scope.total_talk_duration_count = '';
		$scope.tme_name =  UNAME;
		$scope.fromdate = '';
		$scope.todate = '';
		$scope.showLoader   = 1;
		$scope.showData = 0;
		
		$( "#from_date" ).datepicker(
				{ maxDate: 0,
					dateFormat:"yy-mm-dd"
				});
				$( "#to_date" ).datepicker(
				{ maxDate: 0,
					dateFormat:"yy-mm-dd"
				});
				$("#from_date").datepicker("setDate", new Date())
				$( "#to_date" ).datepicker("setDate", new Date());
				
			$scope.showmediapopup = function(index,media_path,data_arr){
				
					$scope.index=index;
					$scope.showmediadivaudio=1;
					$scope.fileName = media_path;
					$scope.audio_show="plugins/jPlayer/examples/blue.monday/audioplayer.php?file="+$scope.fileName+"&employee_id="+USERID+"&media_id=&employee_name="+UNAME+"&title=&media_type=&disable_track=1";
					
			};
			
			$scope.showpopupdiv_close = function(){
				$scope.showmediadivaudio=0;
			};
                        
                        $scope.dataTable = function () {

                                $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {


                                    $scope.table = $('#empDataTable').DataTable({
                                        "paging": true,
                                        "pageLength": 10,
                                        "pagingType": "simple_numbers",
                                        "bRetrieve": true,
                                        "aoColumns": [
                                            {"bSortable": false},
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                            null,
                                        ]
                                    });
                                });
                         }
		$scope.dataTable();
                
		$scope.fetchCallData = function(){
			$scope.showData = 0;
			$scope.showLoader = 1;
			$scope.getData = {};
			$scope.fromdate = $('#from_date').val();
			$scope.todate = $('#to_date').val();
			
			$scope.getData['from_date'] = $scope.fromdate;
			$scope.getData['to_date'] = $scope.todate;
			$scope.getData['emp_id'] = USERID;
			
			APIServices.getTMECallLogs($scope.getData).success(function(response) {
				
				$scope.showData = 1;
				$scope.showLoader = 0;
				$scope.fetchData = response;
				$scope.loginCity = LOGIN_CITY;
                                
				if(response.error.code == 0){
						$scope.alldata_call_logs = response.result;
						$scope.totalEmpData = response.data.stats;
						$scope.manual_call_count_sum = parseInt($scope.totalEmpData.call_count.M + $scope.totalEmpData.call_count.C);
						$scope.login_duration =  $scope.totalEmpData.login_duration.total_raw_duration;
						$scope.avg_login_duration = $scope.login_duration/$scope.total_empcnt/1000/3600;
						$scope.trunk_duration = $scope.totalEmpData.trunk_duration;
						$scope.break_duration = $scope.totalEmpData.break_duration;
						$scope.block_duration =  $scope.totalEmpData.block_duration;
						$scope.wrapup_duration =  $scope.totalEmpData.wrapup_duration;
						$scope.inbound_calls =  $scope.totalEmpData.call_count.I;
						$scope.manual_calls =  $scope.manual_call_count_sum;
						$scope.dialer_calls =  $scope.totalEmpData.call_count.O;
						$scope.call_count =   $scope.totalEmpData.total_call_count;
						$scope.total_call_per_tme_count =  $scope.totalEmpData.total_call_count;
						$scope.total_talk_duration_count =  $scope.totalEmpData.trunk_duration;
						$scope.ring_duration =  $scope.totalEmpData.ring_duration;
							 
						$scope.busy_duration_raw = $scope.ring_duration + $scope.trunk_duration + $scope.wrapup_duration + $scope.break_duration + $scope.block_duration;
							 
						$scope.raw_duration = $scope.totalEmpData.login_duration.total_raw_duration;
							 
						$scope.raw_login_duration = $scope.raw_duration / 1000;
							 
						$scope.final_idle_duration = ($scope.raw_login_duration - $scope.busy_duration_raw)/$scope.total_empcnt;
						
			}
			
			
                
			});
			
			if ($.fn.DataTable.isDataTable("#empDataTable")) {
                
                            $('#empDataTable').DataTable().clear().destroy();
                        }
                        $scope.dataTable();
		}
                
                $scope.fetchCallData();
                
                $scope.tmeCallData = function(event){
                    $scope.date1 = new Date($('#from_date').val());
	            $scope.date2 = new Date($('#to_date').val());
                    
                    $scope.dayDiff = new Date($scope.date2 - $scope.date1);
                    $scope.days = $scope.dayDiff/1000/60/60/24;
                   
                if ($scope.days > 2) {
                    $mdDialog.show($mdDialog.alert().content('Date range cannot be greater than 2 days').ok('OK!')
                    .targetEvent(event));
                } 
                else{
                  $scope.fetchCallData();  
                }
                    
                }
	});
        


	tmeModuleApp.controller('penaltyReportController',function($scope,APIServices,$rootScope,$cookieStore,$mdSidenav,$http,$interval,$timeout,$mdToast,$mdDialog,returnState,$location,$state){
		
		console.log($location);
		
		$scope.displayPenaltyreport=0;
		$scope.citysearch=[];
		$scope.citysearch[0]='panindia';
		$scope.currentmonth =  '';
		$scope.year =  '';
		var date = new Date();
		$scope.citylistarray=['Pan India','Mumbai', 'Delhi', 'Pune', 'Bangalore', 'Ahmedabad', 'Hyderabad', 'Chennai', 'Kolkata'];
		$scope.cityselect=[];
		$scope.cityselect[0]='Pan India';
		console.log($scope.citylistarray);
		$scope.monthArray = [];
		$scope.monthArray[0] = "January";
		$scope.monthArray[1] = "February";
		$scope.monthArray[2] = "March";
		$scope.monthArray[3] = "April";
		$scope.monthArray[4] = "May";
		$scope.monthArray[5] = "June";
		$scope.monthArray[6] = "July";
		$scope.monthArray[7] = "August";
		$scope.monthArray[8] = "September";
		$scope.monthArray[9] = "October";
		$scope.monthArray[10] = "November";
		$scope.monthArray[11] = "December";
		$scope.listyearArray=[];
		$scope.empdata=[];
		$scope.empdata[0]='';
		
		var d = new Date();
		$scope.year =  d.getFullYear();
		$scope.monthname =$scope.monthArray[date.getMonth()];
		$scope.monthselect=[];
		$scope.monthselect[0]=$scope.monthname+"-"+$scope.year;
		
		//~ $scope.monthselect[0]='';
		
		 $scope.minyear = $scope.year-1;
		$scope.maxyear = $scope.year;
		
		for(var i=$scope.minyear;i<=$scope.maxyear;i++){
			for(var j=0;j<=11;j++){
				
				if(i==2017 && $scope.monthArray[j]=='December'){
					var monthyear=$scope.monthArray[j]+"-"+i;
					$scope.listyearArray.push(monthyear);
				}else if(i==2018 ){
					if($scope.monthArray[j]!=$scope.monthname){
						var monthyear=$scope.monthArray[j]+"-"+i;
						$scope.listyearArray.push(monthyear);
					}
					else{
						var monthyear=$scope.monthArray[j]+"-"+i;
						$scope.listyearArray.push(monthyear);
						break;
					}
				}
			}
		}
		console.log($scope.listyearArray);
		
		$scope.pagecount_penalty = function(page,count){
			$scope.currentPage  = 1;
			$scope.pagearr = [];
			$scope.count        =  count;
			$scope.numOfpages   =   Math.ceil($scope.count/20);
			if($scope.numOfpages == 1){

				$scope.pagearr[0]=1;
			}else{
				$scope.currentPage  = 1;
				for(var u = 1; u <= $scope.currentPage+1 ;u++){
						//~ if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
							$scope.pagearr.push(u);
						//~ }
				}
				$scope.currentPage  = page;
				console.log($scope.pagearr);
			}
		};
		$scope.reportdata = function(para){
			if(para !='city'){
				if(para=='next'){
					if($scope.month==11){
						$scope.month=0;
						$scope.year = $scope.year+1;
					}else{
						$scope.month=$scope.month+1;
					}
				}else if(para=='prev'){
					if($scope.month==0){
						$scope.year = $scope.year-1;
						$scope.month=11;
					}else{
						$scope.month=$scope.month-1;
					}
				}else{
					var d = new Date();
					$scope.month =  d.getMonth();
					$scope.citysearch[0]='panindia';
					$scope.monthname =$scope.monthArray[$scope.month];
					$scope.year =  d.getFullYear();
					para='none';
				}
			}	
			$scope.dateselected=$scope.monthselect[0].split("-");
			
			var page=1;
			$scope.monthname =$scope.monthArray[$scope.month];
			APIServices.getPenaltyInforeport($scope.cityselect[0],$scope.monthArray.indexOf($scope.dateselected[0])+1,$scope.dateselected[1],page,$scope.empdata[0]).success(function(response){
				console.log(response);
				$scope.empdata[0]='';
				$scope.penaltyReportData=response;
				$scope.pagecount_penalty(page,$scope.penaltyReportData.count);
			});
		};
		$scope.reportdatapage = function(page){
			if(page ==  'prev'){
					$scope.currentPage  =   $scope.currentPage -1;
				}
				else if (page   ==  'next'){
				   if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
							$scope.pagearr.push($scope.currentPage +1);
							$scope.currentPage	=	$scope.currentPage +1;
						}else{
							$scope.currentPage	=	$scope.currentPage +1;
							if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
								$scope.pagearr.push($scope.currentPage +1);
							}
							}
				}
				else if(page    ==  1){
					$scope.currentPage  =   1;

				}else{
					 $scope.currentPage  = page;
						if(($scope.currentPage != $scope.numOfpages) && ($scope.pagearr.indexOf($scope.currentPage+1) == -1)){
							console.log($scope.pagearr);
							$scope.pagearr.push($scope.currentPage +1);
							
								}
				}
				APIServices.getPenaltyInforeport($scope.cityselect[0],$scope.monthArray.indexOf($scope.dateselected[0])+1,$scope.dateselected[1],$scope.currentPage,$scope.empdata[0]).success(function(response){
					console.log(response);
					$scope.penaltyReportData=response;
				});
				console.log($scope.currentPage);
		}
		$scope.showpenaltypopup = function(index){
			$scope.indexpopup=index;
			$scope.displayPenaltyreport=1;
		};
		
		$scope.showpenaltypopupclose = function(){
			$scope.displayPenaltyreport=0;
		}
		
		$scope.showpenaltypopup = function(index){
			$scope.indexpopup=index;
			$scope.displayPenaltyreport=1;
		};
		
		$scope.showpenaltypopupclose = function(){
			$scope.displayPenaltyreport=0;
		}
		$scope.reportdata();
		$("#employeetext").keyup(function(event) {
			if (event.keyCode === 13) {
				$("#searchbu").click();
			}
		});
		$scope.report_reload = function(){
			location.reload();
		};
		
		
	});
	
	//Dicsount Report Controller start
        tmeModuleApp.controller('discountReportController',function($scope,APIServices,$rootScope,$cookieStore,$mdSidenav,$http,$interval,$timeout,$mdToast,$mdDialog,returnState,$location){
			$scope.discount_stat 	= 	[];
			$scope.discount_stat[0] = 	0;
			var loadNumber			=	0;
			$scope.cancel_request = function(id){
				APIServices.updateBudgetService(id).success(function(response) {
					if(response['errorCode'] == 0) {
						alert('The request is canceled');
						$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
					}else{
						alert(response['errorStatus']);
						return false;
					}
				});
			}
		   //Method used to redirect contract to Order Summary Inter Mediate
			$scope.goToOdrSummryInter = function($contractInfo) {
				var checkTemp   =   APIServices.getTempStatus($contractInfo.contractid).success(function(response){
					var loc =   '';
					if(response.count   ==  1) {
						var flgSrc  =   2;
					} else {
						var flgSrc  =   1;
					}
					if($contractInfo.paidstatus == 1) {
						var flgPaid =   1;
					} else {
						var flgPaid =   0;
					}
					if(flgSrc == 1 && flgPaid != '*'){
						loc = "&flgPaid="+flgPaid;
					}
					if(parseInt(flgPaid) == 0) {
						loc = "&convert=1";
					}
					if($contractInfo.status==1 && $contractInfo.dealclose_flag==2){
						alert('This request is already deal closed after approval, you cannot go further.'); return false;
					}else if($contractInfo.status==2){
						alert('This request is already Rejected, you cannot go further.'); return false;
					}else{
						if($contractInfo.dealclose_flag==2 && $contractInfo.status==0){
							alert('This request is already deal closed, you cannot go further.'); return false;
						}else{
							window.location = "../payments-i/order_summary_inter.php?parentid="+$contractInfo.contractid+"&ecs="+$contractInfo.ecs_flag+"&version="+$contractInfo.version;
						}
					}
					/*else if($contractInfo.status==0){
							alert('This request is pending, you cannot go further.'); return false;
						}*/
				});
			};
			
			$scope.loadAlloc		=	function(returnState,$page) {
				if(typeof $page==='undefined') {
					var pageShow = '';
				} else {
					var pageShow = $page;
				}
				var scopeUrlExp	=	$location.url().split('/');
				// Service identifier for Discount report Contracts
				APIServices.getBudgetService($scope.discount_stat[0]).success(function(response) {
					$scope.discountReport			=	[];
					if(pageShow == '' || pageShow == null) {
						$scope.discountReport = response;
						$scope.mainPage = scopeUrlExp[1];
					} else {
						$scope.discountReport.data = response.data;
						$scope.discountReport.count = response.totCount;
						$scope.discountReport.errorCode = 0;
					}
					$scope.selectedIndex			=	pageShow;
					$scope.discountReport.totCount	=	response.totCount;	
					$scope.pageContracts			=	Math.ceil($scope.discountReport.totCount/$scope.showPageNum);
					console.log("num ==="+$scope.pageContracts);
					var timer = setTimeout(function() {
						if($('.pageSlider').width() < $('.setTotContWidthPage').width()) {
							var selectedLeft	=	$('.selectedPage').position().left;
							$('.setTotContWidthPage').animate({left:'-'+selectedLeft+'px'},200);
						}
					},300);
					$scope.$on("$destroy", function() {
						if (timer) {
							$timeout.cancel(timer);
						}
					});
					if($scope.viewParam	==	2) {
						$scope.setwidthList();
					}
				});
				$('html, body').animate({scrollTop: 0}, 1000);
			};
			if($cookieStore.get("thisPage") == 'discountReport') {
				$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
			} else if($cookieStore.get("thisPage") == 'filter' && $cookieStore.get("currLink") == '.filter'){
				$scope.loadAlloc(returnState,$cookieStore.get("pageNo"));
			} else {
				$cookieStore.put("thisPage",'discountReport');
				$cookieStore.put("pageNo",'');
				$scope.loadAlloc(returnState);
			}
			$scope.clickLoad	=	function(n) {
				loadNumber	=	n;
				$cookieStore.put("pageNo",n);
				$scope.loadAlloc(returnState,loadNumber);
			};
    });
    // discount report controller ends
    
    tmeModuleApp.controller('kpiController',function($scope,$filter,APIServices,returnState,$rootScope,$cookieStore,$sce,$timeout){

        var divs = ["section_30days", "section_month", "section_years"];
        

        $scope.showDiv = '';
      //  $scope.activeTab = "4";
        $scope.activeTab = "5";
        $scope.activeMainTab = "1";
        $scope.showLoader = 1;
        
        $scope.loginEmpId = USERID;
        
        $scope.activeData = function (type) {
            $scope.showLoader = 1;
            $scope.activeTab = type;
            $scope.getCollectData();
        };
        
        $scope.mainTab = function(type){
            $scope.showLoader = 1;
            
            $scope.activeMainTab = type;
            if(type == 1){
                $scope.activeData(4);
            }
            else{
                $scope.activeData(1);
            }
        }
        
        $scope.getCollectData = function () {

            $scope.colData = {};
            $scope.colData['date_type'] = $scope.activeTab;


            $scope.currRealVal = 0;
            $scope.oldRealVal = 0;

            $scope.currColDay = 0;
            $scope.oldColDay = 0;

            $scope.currContVal = 0;
            $scope.oldContVal = 0;

            $scope.currAvpcVal = 0;
            $scope.oldAvpcVal = 0;

            $scope.currEcsVal = 0;
            $scope.oldEcsVal = 0;

            $scope.currDigiPay = 0;
            $scope.oldDigiPay = 0;

            $scope.currClrCol = 0;
            $scope.oldClrCol = 0;

            $scope.currNonEcs = 0;
            $scope.oldNonEcs = 0;

            $scope.currCol = 0;
            $scope.oldCol = 0;
            
            $scope.currAvpcValperCol = 0;
            $scope.oldAvpcValperCol = 0;
            
            $scope.currEcsCarry = 0;
            $scope.oldEcsCarry = 0;
            
            $scope.currTarget = 0;
            
            $scope.currCollTarget = 0;
            $scope.collTargetVal = 0;
            
            $scope.finalCollTarget = 0;
            $scope.changeCollTarget = 0;
            
            $scope.showData = 0;

            $scope.CampaignWise = {};
            
            $scope.colData['employee_id'] = $scope.loginEmpId;
            
            APIServices.getCollectData($scope.colData).success(function (response) {

                $scope.current_result = response.result.current.current_result.result;
                $scope.previous_result = response.result.previous.previous_result.result;
                
                $scope.current_result = response.result.current.current_result.result;
                $scope.previous_result = response.result.previous.previous_result.result;
                
                $scope.currStartDateDays = response.result.current.current_start_date;
                $scope.currEndDateDays = response.result.current.current_end_date;
                
                $scope.currStartDateMonth = response.result.current.current_start_date;
                $scope.currEndDateMonth = response.result.current.current_end_date;
                
                $scope.currStartDateYear = response.result.current.current_start_date;
                $scope.currEndDateYear = response.result.current.current_end_date;
                
                $scope.currStartDateMtd = response.result.current.current_start_date;
                $scope.currEndDateMtd = response.result.current.current_end_date;
                
                $scope.showLoader = 0;
                
                if (response.result.current.current_result.error.code == 0) {
                    $scope.showData = 0;
                    $scope.currRealVal = $scope.current_result.stats.realisable_value / $scope.current_result.stats.total_days;
                    $scope.currColDay = ($scope.current_result.stats.collection + $scope.current_result.stats.pending_collection + $scope.current_result.stats.bounced_collection) / $scope.current_result.stats.total_days;
                    $scope.currContVal = $scope.current_result.stats.appts_dc / ($scope.current_result.stats.total_days * $scope.current_result.stats.total_subordinates);
                  //  $scope.collTargetVal = $scope.current_result.stats.collection / $scope.current_result.stats.total_days;
                    //$scope.collTargetVal = $scope.current_result.stats.collection / $scope.current_result.stats.working_total_days;
                    
                    if($scope.current_result.stats.working_total_days != 0 && $scope.current_result.stats.working_total_days != null){
						$scope.collTargetVal = $scope.current_result.stats.collection / $scope.current_result.stats.working_total_days;
					}
					else{
						$scope.collTargetVal = 0;
					}
                   
                    $scope.currMonthName = $scope.current_result.stats.rv_target_month_name;
                    
                    $scope.currTarget = $scope.current_result.stats.rv_target; 
                    $scope.currCol = $scope.current_result.stats.collection; 
                    
                    $scope.currCollTarget = $scope.current_result.stats.coll_target;
                    if($scope.currCollTarget == 0 && $scope.collTargetVal == 0){
                        
                        $scope.graphmsg = 1;
                    }
                    else if($scope.currCollTarget == 0 && $scope.collTargetVal != 0){
                        
                       $scope.graphmsg = 1; 
                    }
                    else{
                        
                        $scope.graphmsg = 0; 
                    }
                    
                    if ($scope.current_result.stats.appts_dc != 0) {
                        //$scope.currAvpcVal = $scope.current_result.stats.budget / $scope.current_result.stats.appts_dc;
                        $scope.currAvpcVal = $scope.current_result.stats.collection / $scope.current_result.stats.appts_dc;
                        $scope.currEcsVal = $scope.current_result.stats.ecs_dc / $scope.current_result.stats.appts_dc;
                        $scope.currAvpcValperCol = $scope.current_result.stats.realisable_value / $scope.current_result.stats.appts_dc;
                    }

                    $scope.currDigiPay = 30;
//                    $scope.currClrCol = $scope.current_result.stats.collection;
                    $scope.currClrCol = $scope.current_result.stats.collection + parseInt($scope.current_result.stats.ecs_carried_forward);
                    $scope.currNonEcs = $scope.current_result.stats.collection - $scope.current_result.stats.ecs_collection;
                    
                    $scope.currEcsCarry = $scope.current_result.stats.ecs_carried_forward;
                    
                    //$scope.currAvpcValperCol = $scope.current_result.stats.collection / $scope.current_result.stats.total_days;
                    
                    
                    if($scope.current_result.stats.campaign_wise){
                        
                        $.each($scope.current_result.stats.campaign_wise, function (i, val) {
                        campaign_name = val['campaign_name'];

                        if (!$scope.CampaignWise[campaign_name]) {
                            $scope.CampaignWise[campaign_name] = {};
                            $scope.CampaignWise[campaign_name]['old_budget'] = 0;
                        }

                        $scope.CampaignWise[campaign_name]['campaign_name'] = val['campaign_name'];
                        $scope.CampaignWise[campaign_name]['current_budget'] = val['budget'];
                        $scope.CampaignWise[campaign_name]['change_value'] = ($scope.CampaignWise[campaign_name]['current_budget'] - $scope.CampaignWise[campaign_name]['old_budget']);
                        $scope.CampaignWise[campaign_name]['change_pc'] = 100;

                        if ($scope.CampaignWise[campaign_name]['old_budget'] != 0) {

                            $scope.CampaignWise[campaign_name]['change_pc'] = ($scope.CampaignWise[campaign_name]['current_budget'] - $scope.CampaignWise[campaign_name]['old_budget']) / $scope.CampaignWise[campaign_name]['old_budget'];
                        }

                        });
                    }
                    else{
                        console.log('No data found for curent result'); 
                    }
                    
                }
                else{
                   $scope.showData = 1;
                   $scope.showMessage = 'No data found';
                }

                if (response.result.previous.previous_result.error.code == 0) {
                   
                    $scope.prevStartDateDays = response.result.previous.prev_start_date;
                    $scope.prevEndDateDays = response.result.previous.prev_end_date;
                
                    $scope.prevStartDateMonth = response.result.previous.prev_start_date;
                    $scope.prevEndDateMonth = response.result.previous.prev_end_date;
                
                    $scope.prevStartDateYear = response.result.previous.prev_start_date;
                    $scope.prevEndDateYear = response.result.previous.prev_end_date;

                    $scope.prevStartDateMtd = response.result.previous.prev_start_date;
                    $scope.prevEndDateMtd = response.result.previous.prev_end_date;
                    
                    $scope.showData = 0;
                    $scope.oldRealVal = $scope.previous_result.stats.realisable_value / $scope.previous_result.stats.total_days;
                    $scope.oldColDay = ($scope.previous_result.stats.collection + $scope.previous_result.stats.pending_collection + $scope.previous_result.stats.bounced_collection) / $scope.previous_result.stats.total_days;
                    $scope.oldContVal = $scope.previous_result.stats.appts_dc / ($scope.previous_result.stats.total_days * $scope.previous_result.stats.total_subordinates);

                    if ($scope.previous_result.stats.appts_dc != 0) {
                       // $scope.oldAvpcVal = $scope.previous_result.stats.budget / $scope.previous_result.stats.appts_dc;
                        $scope.oldAvpcVal = $scope.previous_result.stats.collection / $scope.previous_result.stats.appts_dc;
                        $scope.oldEcsVal = $scope.previous_result.stats.ecs_dc / $scope.previous_result.stats.appts_dc;
                        $scope.oldAvpcValperCol = $scope.previous_result.stats.realisable_value / $scope.previous_result.stats.appts_dc;
                    }

                    $scope.oldDigiPay = 30;
                   // $scope.oldClrCol = $scope.previous_result.stats.collection;
                    $scope.oldClrCol = $scope.previous_result.stats.collection + parseInt($scope.previous_result.stats.ecs_carried_forward);
                    $scope.oldNonEcs = $scope.previous_result.stats.collection - $scope.previous_result.stats.ecs_collection;
					//$scope.oldAvpcValperCol = $scope.previous_result.stats.collection / $scope.previous_result.stats.total_days;
					$scope.OldEcsCarry = $scope.previous_result.stats.ecs_carried_forward;
                    
                    
                   if($scope.previous_result.stats.campaign_wise){
                       $.each($scope.previous_result.stats.campaign_wise, function (i, val) {
                        campaign_name = val['campaign_name'];

                        if (!$scope.CampaignWise[campaign_name]) {
                            $scope.CampaignWise[campaign_name] = {};
                            $scope.CampaignWise[campaign_name]['current_budget'] = 0;
                        }

                        $scope.CampaignWise[campaign_name]['campaign_name'] = val['campaign_name'];
                        $scope.CampaignWise[campaign_name]['old_budget'] = val['budget'];
                        $scope.CampaignWise[campaign_name]['change_value'] = ($scope.CampaignWise[campaign_name]['current_budget'] - $scope.CampaignWise[campaign_name]['old_budget']);
                        $scope.CampaignWise[campaign_name]['change_pc'] = 100;

                        if ($scope.CampaignWise[campaign_name]['old_budget'] != 0) {
                            $scope.CampaignWise[campaign_name]['change_pc'] = ($scope.CampaignWise[campaign_name]['current_budget'] - $scope.CampaignWise[campaign_name]['old_budget']) / $scope.CampaignWise[campaign_name]['old_budget'];
                        }

                    });
                   }
                   else{
                       console.log('No data found for previous result'); 
                   }
                    
                }
                else{
                   $scope.showData = 1;
                   $scope.showMessage = 'No data found';
                }

                //console.log($scope.CampaignWise);



                $scope.changeRealVal = $scope.currRealVal - $scope.oldRealVal;
                $scope.changeColDay = $scope.currColDay - $scope.oldColDay;
                $scope.changeContVal = $scope.currContVal - $scope.oldContVal;
                $scope.changeAvpcVal = $scope.currAvpcVal - $scope.oldAvpcVal;
                $scope.changeEcsVal = $scope.currEcsVal - $scope.oldEcsVal;
                $scope.changeDigiPay = $scope.currDigiPay - $scope.oldDigiPay;
                $scope.changeClrCol = $scope.currClrCol - $scope.oldClrCol;
                $scope.changeNonEcs = $scope.currNonEcs - $scope.oldNonEcs;
                $scope.changeCol = $scope.currCol - $scope.oldCol;
                $scope.changeAvpcValperCol = $scope.currAvpcValperCol - $scope.oldAvpcValperCol;
                $scope.changeEcsCarry = $scope.currEcsCarry - $scope.OldEcsCarry;
                
                if($scope.collTargetVal <= $scope.currCollTarget){
                    $scope.changeCollTarget = $scope.currCollTarget - $scope.collTargetVal;
                }
                else{
                    $scope.changeCollTarget = $scope.collTargetVal - $scope.currCollTarget;
                }
                
                if($scope.oldRealVal == 0){
                    $scope.finalRealVal = 100;
                }
                else{
                   $scope.finalRealVal =  ($scope.changeRealVal / $scope.oldRealVal)*100;
                }
                
                if($scope.oldContVal == 0){
                    $scope.finalContVal = 100;
                }
                else{
                   $scope.finalContVal =  ($scope.changeContVal / $scope.oldContVal)*100;
                }
                
                if($scope.oldAvpcVal == 0){
                    $scope.finalAvpcVal = 100;
                }
                else{
                   $scope.finalAvpcVal =  ($scope.changeAvpcVal / $scope.oldAvpcVal)*100;
                }
                
                if($scope.oldColDay == 0){
                    $scope.finalColDay = 100;
                }
                else{
                   $scope.finalColDay =  ($scope.changeColDay / $scope.oldColDay)*100;
                }
                
                if($scope.oldClrCol == 0){
                    $scope.finalClrCol = 100;
                }
                else{
                   $scope.finalClrCol =  ($scope.changeClrCol / $scope.oldClrCol)*100;
                }
                
                if($scope.oldNonEcs == 0){
                    $scope.finalNonEcs = 100;
                }
                else{
                   $scope.finalNonEcs =  ($scope.changeNonEcs / $scope.oldNonEcs)*100;
                }
                
                if($scope.oldCol == 0){
                    $scope.finaloldCol = 100;
                }
                else{
                   $scope.finaloldCol =  ($scope.changeCol / $scope.oldCol)*100;
                }
                
                if($scope.oldAvpcValperCol == 0){
                    $scope.finalAvpcValperCol = 100;
                }
                else{
                   $scope.finalAvpcValperCol =  ($scope.changeAvpcValperCol / $scope.oldAvpcValperCol)*100;
                }
                
                if($scope.currRealVal == 0){
                    $scope.finalTarget = 100;
                }
                else{
                   
                   $scope.finalTarget =  ($scope.currRealVal / $scope.currTarget)*100;
                }
                
                if($scope.currEcsCarry == 0){
                    $scope.finalEcsCarry = 100;
                }
                else{
                   $scope.finalEcsCarry =  ($scope.changeEcsCarry / $scope.currEcsCarry)*100;
                }
                
                if($scope.collTargetVal == 0){
                    $scope.finalCollTarget = 0;
                    $scope.percentColor = "red";
                    $scope.tagColor="kpi_red";
                }
                else{
                   
                   $scope.finalCollTarget = ($scope.collTargetVal / $scope.currCollTarget)*100;
                   
                   if($scope.finalCollTarget >= 95){
                       $scope.percentColor = "green";
                       $scope.tagColor="kpi_gren";
                   }
                   else if($scope.finalCollTarget >= 75 && $scope.finalCollTarget < 94.99){
                        $scope.percentColor = "#e59400";
                       $scope.tagColor = "kpi_yellow";

                   }
                   else{
                        $scope.percentColor = "red";
                        $scope.tagColor = "kpi_red";
                   }
                }
                
            });

        };
        $scope.getCollectData();
        
        $scope.getGraphData = function(){
            
            $scope.collectData = {};
            
            $scope.collectData['date_type'] = $scope.activeTab;
            $scope.collectData['employee_id'] = $scope.loginEmpId;
            
            APIServices.getCollectData($scope.collectData).success(function (response) {
                
                if(response.error.code == 0){
                    
                  //  $scope.collectionGraph(response);
                    $scope.doughnutChart(response);
                }
                else{
                    $scope.errmsg = 'No Data Found';
                }
                
            });
            
        }
        
      //  $scope.getGraphData();
        

     
        $scope.doughnutChart = function(response){
           
        $scope.doughCurrentResult = response.result.current.current_result;
            
        $scope.doughTarget = Math.round($scope.doughCurrentResult.result.stats.rv_target);
            
        $scope.doughTargetAchieved = Math.round($scope.doughCurrentResult.result.stats.realisable_value / $scope.doughCurrentResult.result.stats.total_days);
            
        var achievedPercnt = Math.round(($scope.doughTargetAchieved / $scope.doughTarget)*100);
            
            if ($scope.doughTargetAchieved < $scope.doughTarget) {
                var pendingPercent = Math.round((($scope.doughTarget - $scope.doughTargetAchieved) / $scope.doughTarget) * 100);
            } else {
                var pendingPercent = 0;
            }
            
        var pending_name = "Pending "+pendingPercent+"%";
        
        var achieved_name = "Achieved "+achievedPercnt+"%";
        
        $scope.totTarget =  Math.round((($scope.doughTargetAchieved / $scope.doughTarget)*100));
        
        if($scope.totTarget <= 25){//0-25
            var titleMsg = "We worry your performance! Your achieved  target is only "+$scope.totTarget+"%";
        }
        else if($scope.totTarget >= 25 && $scope.totTarget < 50){//25-50
            var titleMsg = "Buckle Up to achieve your target! Your achieved  target is only "+$scope.totTarget+"%";
        }
        else if($scope.totTarget >= 50 && $scope.totTarget < 75){//50-75
           var titleMsg = "Looks Ok! Keep going ahead! Your achieved  target is only "+$scope.totTarget+"%"; 
        }
        else if($scope.totTarget >= 75 && $scope.totTarget < 100){//75-100
            var titleMsg = "Appreciate your effort ! Keep going good to achieve your target %";
        }
        else if($scope.totTarget >=  100){//75-100
            var titleMsg = "Superb! You have achieved your targets. Keep going ahead! ";
        }
        

        $('#doughnut').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
         title: {
                text: titleMsg,
            },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    enabled: true,
                    distance: -50,
                    style: {
                        fontWeight: 'bold',
                        color: 'white',
                        textShadow: '0px 1px 2px black'
                    }
                },
                
                center: ['50%', '50%']
            }
        },
        series: [{
            type: 'pie',
            name: "Target",
            showInLegend:true,
            size: '100%',
                innerSize: '60%',
            //innerSize: '50%',
            data: [{
                name: pending_name,
                y: pendingPercent,
                color: "#FA938C" 
            },
            {
                name: achieved_name,
                y: achievedPercnt,
                color: "#DAF7A6" 
            },
                                 
              
               
            ]
        }],
    credits:{
                enabled: false
            }
    });
        }
        
         $(".subCollect").click(function () {

            $subCollect = $(this);
            //getting the next element
            $vlue_section = $subCollect.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $vlue_section.slideToggle("slow", function () {
                //execute this after slideToggle is done
                //change text of header based on visibility of content div

            });
        });

        $('.value_head').click(function () {
            $(this).find('.genio_collect').toggleClass('plus-icon');
            $(this).find('.genio_collect').toggleClass('mins-icon');
        });

        $(".value_head").click(function () {

            $value_head = $(this);
            //getting the next element
            $pize_tble = $value_head.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $pize_tble.slideToggle("slow", function () { });
        });
        
    });
});




function disposition_set_mktg($stVal,remoteAddr,response) {
	window.parent.fn_dispostion($stVal,remoteAddr);
	console.log('response in disposition_set---->',response);
	if(response.live_flg == '1') {
		window.location.href=response.live_url;
	}else {		
		if(response.errorCode	==	0) {
			window.location.href	=	'welcome';
		} else {
			alert('Dispositions not saved. Please try again');
			window.location.href	=	'welcome';
		}
   }
}

function IroTransfercall(pId, flgSrc, flgAfterCall, flgPaid, nonpaidFlag,irocode){
	var loc = "mktgGetContDataNew.php?parentid="+pId+"&flgSrc="+flgSrc+"&flgAfterCall="+flgAfterCall+"&irocode="+irocode+"&hotdata=1";
	if(flgSrc == 1 && flgPaid != '*'){ loc += "&flgPaid="+flgPaid; }
	if(parseInt(nonpaidFlag) == 1){
		loc += "&convert=1";
	}
	window.location = loc;
}
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};
