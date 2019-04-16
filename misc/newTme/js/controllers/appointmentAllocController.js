define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('appointmentAllocController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,returnState,$mdDialog,$mdBottomSheet,$mdToast,$mdSidenav,$stateParams,CONSTANTS) {
		$rootScope.layout = 'style_appt';
		//~ cssInjector.add("../../css/app_new.css");
		$rootScope.TOTALTME		=	''; // new var declared here
		$scope.itemsPerPage 	=	12;
		$scope.currentPage 		= 	0;
		$scope.items			=	[];
		$scope.selColor			=	[];
		$rootScope.dateData			=	"";
		$scope.meInfoLength		=	"";
		$scope.class  			=	"";
		$scope.div_height		=	0;
		//$scope.checked[0]  	=	false;
		$scope.counter			=	0;
		$scope.showLeftPos		=	0;
		$scope.parentid			=	'';
		$scope.textInst			=	'';
		$scope.disposeval		=	0;
		$scope.meCount			=	0;
		$scope.pincode			=	0;
		$scope.allocType		=	'';
		$scope.normalStat		=	'';
		$scope.callbackdate		=	'';// For Call Back Functionality
		var AllMeFlag			=	0;
		var pincode				=	'';
		var dateSelected;
		$scope.btntxt				=	'';
		$rootScope.alternateAddFlag	=	1;
		$rootScope.pincodeSelected	=	0;
		$rootScope.SERVICE_PARAM		=	SERVICE_PARAM;
		$rootScope.display_allocateToME	=	0; // 0 means it won't display button
		$rootScope.alloc_to_ME_TME		=	0; // Alloc to ME Flag for TME's
		$rootScope.allME_DELHI			=	0; // ALL ME FLAG FOR DELHI
		$rootScope.ME_NA				=	0;
		$rootScope.Grb_Normal_alt_add	=	0; // 1 means normal ME's Flow and 2 means Grab Flow
		
		$rootScope.automated_flg			=	0;  // as new automated flow should work for selected TME's not All
		$rootScope.eligibility				=	''; // latest declared variables
		$rootScope.list_of_me_pincodewise	=	[]; // latest declared variables
		$rootScope.total_tme				=	''; // latest declared variables
		$rootScope.tme_rank					=	''; // latest declared variables
		$rootScope.errorCode_me				=	''; // latest declared variables
		$rootScope.TMERANK					=	''; // latest declared variables	
		$scope.new_selColor					=	[]; // latest declared variables 07-02-2017
		
		$rootScope.apptContact_person		=	[];
		$rootScope.apptContact_person[0] 	= 	'';
		
		$rootScope.bypass_autoAlloc 	=	0; // new var for new logic auto appt
		
		$rootScope.newLogic_optns	=	0; // new var for new logic auto appt
		
		$rootScope.click_pincode_ex	=	0; // new var for new logic auto appt
		
		$rootScope.click_city_exc	=	0; // new var for new logic auto appt
		
		$rootScope.AllMeClickFlg	=	0; // new variable declared here
		
		$rootScope.bfromPincode	=	'';
		
		$rootScope.bfromPincode_click	=	0;
		
		$rootScope.alertnateAddress_click	=	0;
		
		$rootScope.textIns	=	'';
		
		$rootScope.apptInstr	=	[];
		
		$scope.dealCloseMEDetails = {};
		
		$rootScope.deal_close_jda = 0;
		
		////////////bypass
		
		$rootScope.bypassGeniolite	=	0;
		$rootScope.bypassuserid 	= 	USERID;
		$rootScope.bypasscity 		= 	DATACITY;
		$rootScope.getbypassdet = function(){
			APIServices.getbypassdet().success(function(response) {
				if(response == 1)
					$rootScope.bypassGeniolite	=	1;
				else
					$rootScope.bypassGeniolite	=	0;
			});
		};
		$rootScope.getbypassdet();
		
		////////////bypass
		
		$scope.go_to_automated_flow	=	function(ev){
			$rootScope.allocateToJdaClick	=	0;
			$rootScope.bypass_autoAlloc	= 0;
			$rootScope.newLogic_optns	=	0; // new var for new logic auto appt
			$rootScope.click_pincode_ex	=	0; // new var for new logic auto appt
			$rootScope.click_city_exc	=	0; // new var for new logic auto appt
			$rootScope.AllMeClickFlg	=	0;
			$rootScope.AllMeFlag	 =	0;
			$rootScope.bfromPincode_click	=	0;
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,0,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
		}
		
		$scope.go_to_bfrom_automated_flow	=	function(ev){
			$rootScope.allocateToJdaClick	=	0;
			$rootScope.bypass_autoAlloc	= 0;
			$rootScope.newLogic_optns	=	0; // new var for new logic auto appt
			$rootScope.click_pincode_ex	=	0; // new var for new logic auto appt
			$rootScope.click_city_exc	=	0; // new var for new logic auto appt
			$rootScope.AllMeClickFlg	=	0;
			$rootScope.AllMeFlag	 	=	0;
			$rootScope.bfromPincode_click	=	1;
			$rootScope.alertnateAddress_click	=	0;
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,0,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
		}
		//~ $scope.apptContact_person	=	[];
		/*
		 * New Code Added here by Apoorv Agrawal 
		 * Handling Show/Hide of Alternate Address Option Start
		 * Date 09-05-2017
		 * 1 --> means show the alternate addr btn else hide
		*/
		$rootScope.showAlternate_addr_appt	=	1; // setting flag here for showing alternate Adrress option
		if(SERVICE_PARAM == 0){
			$rootScope.showAlternate_addr_appt	=	2;
			//~ $rootScope.newLogic_optns	=	1;
		}
		/*		 
		 * Handling Show/Hide of Alternate Address Option End
		*/
		
		if(SERVICE_PARAM == 0 || SERVICE_PARAM == 50) {
			$rootScope.showAllMEButton	=	1; // if 1 show popup
		} else {
			$rootScope.showAllMEButton	=	0; // if 0 don't show popup
		}
		
		$rootScope.alternate_addrss_pincode	=	'';
		
		$rootScope.WhichFlow	=	'';
		$rootScope.directiveFlag	=	0; // new directive variable declared here
		
		$rootScope.allocateToJda	=	0;
		
		$rootScope.autoDialer = new Array;
		for(var $ext=6000;$ext<7000;$ext++){
			$rootScope.autoDialer.push($ext);
		}
		APIServices.getContractData(returnState.paridInfo).success(function(response) {
			$rootScope.contractData	=	response;
			$rootScope.getDataTime(1,response,'',AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add);
			$scope.pincode		=	response.data.pincode;
			$rootScope.bfromPincode	=	response.data.pincode; // new variable added Here
			pincode				=	response.data.pincode;
			$scope.disposeval	=	returnState.disposeVal;
			if($scope.disposeval==25){
				$scope.btntxt	=	"Fix Appointment";
			}else if($scope.disposeval==99){
				$scope.btntxt	=	"Fix Appointment";
			}else if($scope.disposeval==24){
				$scope.btntxt	 	=	"Submit";
			}else if($scope.disposeval==22){
				$scope.btntxt	 	=	"Submit";
			}else if($scope.disposeval==317){
				$scope.btntxt	 	=	"Submit";
			}
		});
		$scope.UnCheck_ME	=	function(){
			console.log("dgdgsdg");
			console.log($scope.radioMod);
			//~ return false;
			$scope.timearr	=	'';
			
			$scope.radioMod = 	"";
			if($scope.jrCode == ''){
				angular.forEach($scope.selColor,function(value,key){	
					//~ $scope.selColor[key]	=	"#61bcf4";	
				});
			}
		}		
		$rootScope.getDataTime	=	function(flag,response,dateSel,AllMeFlag,DATACITY,alternateAddFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add){			
			if(alternateAddFlag	==	1){
				$rootScope.pincodeSelected			=	response.data.pincode;			
			}
			else{
				if($rootScope.alternate_addrss_pincode != ''){
					$rootScope.pincodeSelected	=	$rootScope.alternate_addrss_pincode;
				}
			}
			if($rootScope.bfromPincode_click == 1){
				$rootScope.pincodeSelected	=	$rootScope.bfromPincode;
			}else{
				if($rootScope.alternate_addrss_pincode != ''){
					$rootScope.pincodeSelected	=	$rootScope.alternate_addrss_pincode;
				}
			}
			if($rootScope.alertnateAddress_click == 1){
				if($rootScope.alternate_addrss_pincode != ''){
					$rootScope.pincodeSelected	=	$rootScope.alternate_addrss_pincode;
				}
			}
			//~ alert($rootScope.pincodeSelected);
			APIServices.apptData(flag,$rootScope.pincodeSelected,dateSel,returnState.disposeVal,$rootScope.contractData.data.parentid,AllMeFlag,DATACITY,USERID,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add,$rootScope.allocateToJdaClick,$rootScope.bypass_autoAlloc,$rootScope.employees.results.allocId).success(function(response) {
				//~ alert('----response---'+JSON.stringify(response));
				if(response.deal_close_jda == 1){
					$rootScope.deal_close_jda = 1;
				}
				if(response.allocateToJda == 1){
					// show the Allocate to JDA button 
					$rootScope.allocateToJda	=	1;
				}else{
					$rootScope.allocateToJda	=	0;
					// don't show the Allocate to JDA button
				}
				if(returnState.disposeVal == '25'|| returnState.disposeVal == '99'){
					if(typeof response.get_deal_close_arr != "undefined"){
						if(response.get_deal_close_arr.error_code == 0){
							$scope.dealCloseMEDetails = response.get_deal_close_arr[$rootScope.contractData.data.parentid];
						}else{
							$scope.dealCloseMEDetails = response.get_deal_close_arr;
						}
					}
				}
				$rootScope.automated_flg	=	response.automated_flg;
				$rootScope.errorCode_me	=	response.errorCode_me;
				$scope.timeData	=	response;
				if(response.TMERANK	==	undefined){
					$rootScope.TMERANK	=	'';
				}else{
					$rootScope.TMERANK	=	response.TMERANK;
				}
				$rootScope.directiveFlag	=	response.directiveFlag; // new variable declared here
				if($rootScope.automated_flg == 1 && (returnState.disposeVal == '25'|| returnState.disposeVal == '99')){
					$rootScope.eligibility				=	response.elgbility;// latest declared variables
					$rootScope.list_of_me_pincodewise	=	response.me_data.LISTOFME; // latest declared variables
					$rootScope.total_tme				=	response.tme_data.TOTALTME;// latest declared variables
					$rootScope.tme_rank					=	response.tme_data.cumulative_rank_city_tme;// latest declared variables
					$rootScope.TOTALTME					=	response.TOTALTME;
					dateSelected	=	dateSel;
					var x = 0;
					angular.forEach(response.data,function(value,key) {
						if(value.flag == 3){
							$scope.new_selColor[key]	=	"#6a6a6a"; //  new code Added Here 07-02-2017 #0B7188
						}else{
							$scope.selColor[x]	=	"#c9e7ff";
						}
						x++;
					});
					if(dateSel){
						$rootScope.dateData	=	dateSel;
					}else{
						$rootScope.dateData	=	$scope.timeData.future_Date;
					}
					$('.allMebtn').blur();
					$('.allMebtn').focusout();
					$('.rotateRightRadio').animate({left:0},20);
					$scope.parentid			=	$rootScope.contractData.data.parentid;
					$scope.whichFlow 		=	response.whichFlow;
					if(response.ME_NA==1){
						$rootScope.ME_NA	=	1;
					}else{
						$rootScope.ME_NA	= 	0;
					}
				}else{
					$scope.timeData					=	response;
					$scope.timeData.errorCode_me	=	0;
					dateSelected	=	dateSel;
					$('.allMebtn').blur();
					$('.allMebtn').focusout();
					$('.rotateRightRadio').animate({left:0},20);
					$scope.parentid			=	$rootScope.contractData.data.parentid;
					$scope.whichFlow 		=	response.whichFlow;
					if(response.ME_NA==1){
						$rootScope.ME_NA	=	1;
					}else{
						$rootScope.ME_NA	= 	0;
					}		
					if(response.whichFlow == "normal" && response.errorCode==0){
						$rootScope.Grb_Normal_alt_add	=	1; // Normal Flow
						$rootScope.WhichFlow		=	'n';
						$scope.meCount			=	$scope.timeData.data.meInfo.length;
						if($scope.meCount>12){
							$scope.itemsPerPage 	=	12;
						}else{
							$scope.itemsPerPage		=	$scope.meCount;
						}
						$.each($scope.timeData.data.meInfo, function (i, news) {
							if(news.mobile==''){
							}
							return i<$scope.itemsPerPage;
						});
						$scope.pageCount = function() {
							return Math.ceil($scope.meCount/$scope.itemsPerPage);
						};
						if($scope.disposeval==25){
							$scope.btntxt		=	"Fix Appointment";
							$scope.normalStat 	= 'Appointment Fix | Total number of MEs:'+$scope.meCount+' for PINCODE:'+$rootScope.pincodeSelected+'';
						}else if($scope.disposeval==99){
							$scope.btntxt	=	"Fix Appointment";
							$scope.normalStat = 'Appointment Re-Fix | Total number of MEs:'+$scope.meCount+' for PINCODE:'+$rootScope.pincodeSelected+'';
						}else if($scope.disposeval==24){
							$scope.btntxt	 	=	"Submit";
							$scope.normalStat 	= 	'Please select timings for FOLLOW UP';
						}else if($scope.disposeval==22){
							$scope.btntxt	 	=	"Submit";
							$scope.normalStat = 'Please select timings for CALL BACk';
						}else if($scope.disposeval==317){
							$scope.btntxt	 	=	"Submit";
							$scope.normalStat = 'Please select timings for JD OMINI INVITE';
						}
						if(response.allME_DELHI == 1){
							// display Allocate To ME button
							$rootScope.allME_DELHI	=	1;
						}else{
							$rootScope.allME_DELHI	=	0;
							// don't show button
						}
					}else{
						if($scope.disposeval==25){
							$scope.btntxt		=	"Grab Appointment";
							$scope.allocType 	= 'Please select timings for GRAB FLOW FOR PINCODE: '+$rootScope.pincodeSelected;
						}else if($scope.disposeval==99){
							$scope.btntxt		=	"Grab Appointment";
							$scope.allocType 	= 'Please select timings for GRAB FLOW FOR PINCODE: '+$rootScope.pincodeSelected;
						}else if($scope.disposeval==24){
							$scope.btntxt	 	=	"Submit";
							$scope.allocType 	= 'Please select timings for FOLLOW UP';
						}else if($scope.disposeval==22){
							$scope.btntxt	 	=	"Submit";
							$scope.allocType 	= 'Please select timings for CALL BACK';
						}else if($scope.disposeval==317){
							$scope.btntxt	 	=	"Submit";
							$scope.allocType 	= 'Please select timings for JD OMINI INVITE';
						}
						if(response.whichFlow == "grab" && response.errorCode==0){
							$rootScope.WhichFlow		=	'g';
								$rootScope.Grb_Normal_alt_add	=	2;// 2 means Grab Flow on Alternate Address Change
						}
						$rootScope.display_allocateToME	=	0;
						$rootScope.alloc_to_ME_TME		=	0;
						//~ if(response.superCad_flag == 1){
							//~ // display Allocate To ME button
							//~ $rootScope.display_allocateToME	=	1;
						//~ }else{
							//~ $rootScope.display_allocateToME	=	0;
							//~ // don't show button
						//~ }
						//~ if(response.allocTOME == 1){
							//~ // display Allocate To ME button
							//~ $rootScope.alloc_to_ME_TME	=	1;
						//~ }else{
							//~ $rootScope.alloc_to_ME_TME	=	0;
							//~ // don't show button
						//~ }
					}
					//~ if($scope.disposeval==25){
						//~ $scope.allocType = 'Please select timings for APPOINTMENT FIX';
					//~ }else if($scope.disposeval==24){
						//~ $scope.allocType = 'Please select timings for FOLLOW UP';
					//~ }else if($scope.disposeval==22){
						//~ $scope.allocType = 'Please select timings for CALL BACK';
					//~ }else if($scope.disposeval==99){
						//~ $scope.allocType = 'Please select timings for APPOINTMENT REFIX';
					//~ }
					if(dateSel){
						$rootScope.dateData	=	dateSel;
					}else{
						$rootScope.dateData	=	$scope.timeData.future_Date;
					}
					
					setTimeout(function() {
						var i=0;
						$scope.setWidthRow	=	0;
						$('.headDiv').each(function() {
							i++;
							$scope.setWidthRow += $(this).outerWidth()+250;
						});
						$scope.$apply();
						return i<$scope.itemsPerPage;
					},1000);
					var x = 0;
					angular.forEach(response.data,function(value,key) {
						$scope.selColor[x]	=	"#61bcf4";
						x++;
					});
				}
				/*
				 * New Code added fro updating Contact Person if missing in Bform Start
				 * Date : 30-03-2017
				 * Apoorv Agrawal
				 */
				if(($scope.disposeval==25 || $scope.disposeval==99) && response.show_contact_pop_up == 1){
					/*$mdDialog.show({
						controller: contactPersonController,
						templateUrl: 'partials/contactPerson.html',
						parent: angular.element(document.body),
						clickOutsideToClose: false,
						escapeToClose: false
					});*/
					//Commented because an issue was found in the overlay of the contact person. Will be uncommented after resolving the issue.
				}
				function contactPersonController($scope,APIServices){
					$scope.salutaion	=	['Mr','Ms','Dr'];
					$scope.selectedsalutations = '';
					$scope.contact_person = '';
					$scope.save_person	=	function(ev){
						if($scope.contact_person == ''){
							$("#contact_person").focus();
							var error_html	=	'<span style="color: red; font-size: 17px; font-weight: bold;">* Enter the Contact Person</span>';
							$("#error_msg").html(error_html);
							setTimeout(function() {
								$("#error_msg").html('');
							},2000);
							return false;
						}else if($scope.selectedsalutations == ''){
							var error_html	=	'<span style="color: red; font-size: 17px; font-weight: bold;">* Select the Salutation</span>'
							$("#error_msg").html(error_html);
							setTimeout(function() {
								$("#error_msg").html('');
							},2000);
							return false;
						}else{
							APIServices.update_generalinfo_shadow($scope.contact_person,$scope.selectedsalutations,$rootScope.contractData.data.parentid).success(function(response) {
								if(response.errorCode == 0){
									$mdDialog.hide();
								}
							});
						}
					}
				}
				/*New Code added fro updating Contact Person if missing in Bform ENDS */
			});
		};
		/*New Code Added Here for Contact Person list PopUp*/
		$scope.openContact_person_popup	=	function(ev){
			if($rootScope.contractData.data != undefined && $rootScope.contractData.data.contact_person != ''){
				$mdDialog.show({
					controller: contactPersonListController,
					templateUrl: 'partials/contactPerson_list.html',
					parent: angular.element(document.body),
					clickOutsideToClose: false,
					escapeToClose: true
				});
			}
		}
		function contactPersonListController($scope,$rootScope){
			console.log($rootScope.contractData.data.contact_person);			
			$scope.listOfPesrons	=	[];
			$scope.listOfcheckBox	=	[];
			if($rootScope.contractData.data != undefined && $rootScope.contractData.data.contact_person != ''){
				var res_split_person	=	$rootScope.contractData.data.contact_person.split(",");
				for(var i=0 ; i< res_split_person.length;i++){
					$scope.listOfPesrons[i]		=	res_split_person[i];
					$scope.listOfcheckBox[i]	=	'';
				}
			}
			$scope.stateChanged	=	function(key,ev){
				for(var i=0;i<$scope.listOfPesrons.length;i++){
					if(key != i){
						$scope.listOfcheckBox[i] = false;
					}
				}
			}
			$scope.save_contact_person	=	function(ev){
				for(var i=0;i<$scope.listOfPesrons.length;i++){
					if($scope.listOfcheckBox[i] == true || $scope.listOfcheckBox[i] == 'true'){
						$rootScope.apptContact_person[0]	=	'';
						$rootScope.apptContact_person[0]	=	$scope.listOfPesrons[i];
					}
				}
				$mdDialog.hide();
			}
			$scope.not_save_contact_person	=	function(ev){
				$mdDialog.hide();
			}
		}
		
		/*
		 * New fucntion Created
		 * for Allocating to JDA
		 * Created by Apoorv Agrawal
		 * Date: 21-04-2017
		*/
		$rootScope.backToMEbtn	=	0;
		$scope.allocateToJdafunc	=	function(allocateToJdaClick,ev){
			$rootScope.allocateToJdaClick	=	allocateToJdaClick;
			$rootScope.backToMEbtn	=	1;
			console.log($rootScope.allocateToJdaClick);
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.allocateToJdaClick);
		}
		$scope.backToMEfunc		=	function(allocateToJdaClick,ev){
			$rootScope.allocateToJdaClick	=	allocateToJdaClick;
			$rootScope.backToMEbtn	=	0;
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.allocateToJdaClick);
		}
		
		$scope.timearr 	= "";
		// Declaring Variables for new Eligibilty Flow 
		$rootScope.dateData		=	'';
		$rootScope.disposeVal	=	''; // new variables declared here
		$rootScope.textIns		=	''; // new variables declared here
		$rootScope.jrCode		=	''; // new variables declared here
		$rootScope.timearr		=	''; // new variables declared here
		$rootScope.eligibleResp		=	[];  // new variables declared here
		$scope.saveMktg = function(textIns,ev){
			$scope.textIns	=	textIns;
			$rootScope.textIns	=	textIns;
						
			textIns	=	$rootScope.apptInstr[0];
			$rootScope.textIns = $rootScope.apptInstr[0];
			$scope.textIns = $rootScope.apptInstr[0];
			
			//~ if($rootScope.apptContact_person[0] == ''){
				//~ $mdToast.show(
					//~ $mdToast.simple()
					//~ .content('Fill the name of Contact Person')
					//~ .position('bottom right')
					//~ .hideDelay(3000)
				//~ );	
				//~ return false;
			//~ }
			if($scope.timearr == "") {
				$mdToast.show(
					$mdToast.simple()
					.content('Please select any time slot.')
					.position('bottom right')
					.hideDelay(3000)
				);	
				return false;
			}
			// Assigning Values for New Flow
			$rootScope.timearr		=	$scope.timearr;
			$rootScope.dateData		=	$scope.dateData;
			$rootScope.disposeVal	=	returnState.disposeVal;
			
			if($rootScope.automated_flg ==1){
				APIServices.getTime($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,textIns,$scope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'chck',$rootScope.eligibility,$rootScope.list_of_me_pincodewise,$rootScope.TMERANK,'','logInsert',$rootScope.TOTALTME,'').success(function(response){
					$rootScope.eligibleResp		=	response;											
					$rootScope.showEligResp	=	response.data;
					$scope.hideMainPage		=	1;
					$mdDialog.show({
						controller: appConfirmController,
						templateUrl: 'partials/appConfirm.html',
						parent: angular.element(document.body),
						clickOutsideToClose: false,
						escapeToClose: false,
						targetEvent: ev,
					});
				});
			}else{
				$('.save_btn_final_non').attr('disabled','disabled'); // added on 03-03-2017
				/*New Code added for capturing All ME appt.*/
				if($rootScope.click_pincode_ex == 1){
					APIServices.insertpincodeDetails($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,textIns,$scope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'nochck','','','','','','','').success(function(insertResp){						
						
					});
				}
				if($rootScope.AllMeClickFlg == 1 || $rootScope.click_city_exc == 1){
					APIServices.insertAllMeDetails($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,textIns,$scope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'nochck','','','','','','','').success(function(insertResp){						
						
					});
				}
				// Make An Ajax Call Here to check if the data is already present for that ME on that time Slot
				APIServices.checkSlotForMe($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,textIns,$scope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add).success(function(resp){
					if(resp.errorCode_checkSlotForMe	==	0){
						APIServices.getTime($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,textIns,$scope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'nochck','','','','','','','').success(function(response){
							$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
							if(response.errorCode == 0) {
								$scope.callbackdate		=	response.callbackdate;
								$scope.data_city		=	DATACITY;
								$rootScope.setNoMenu	=	0;
								if(STATID && $.inArray(parseInt(STATID),$rootScope.autoDialer) !== -1) {
									if(returnState.disposeVal==24 || returnState.disposeVal== 22  ||  returnState.disposeVal== 124 ||  returnState.disposeVal== 210 || returnState.disposeVal == 317){
										callback_followup(returnState.disposeVal,$scope.callbackdate,$rootScope.employees.remoteAddr);
									}else{
										disposition_set(returnState.disposeVal,$rootScope.employees.remoteAddr);
									}
								}else{
								}
								console.log('Here 1',response);
								if(response.errorStatus_tblContractAllocation_MoveToIDC.allocated_resp_type != '2'){
									if(response.live_flg==1){
										APIServices.stopTimer(returnState.disposeVal,$rootScope.contractData.data.parentid,$rootScope.employees.results.empName).success(function(response){
											window.location.href=response.live_url;
										});
									}else{
										APIServices.stopTimer(returnState.disposeVal,$rootScope.contractData.data.parentid,$rootScope.employees.results.empName).success(function(response){
											window.location.href = '../newTme/welcome';
										});
									}
								}else{
									$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
									var confirm = $mdDialog.confirm()
										.title('The Time Slot for this particular ME has already been booked by Another TME.')
										.ok('Ok')
										.cancel('');
									$mdDialog.show(confirm).then(function() {
										$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
										$scope.UnCheck_ME();
										//~ $route.reload();
									}, function() {
										$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
										$scope.UnCheck_ME();
										return false;
									});	
								}	
							}
						});
					}else{
						$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
						var confirm = $mdDialog.confirm()
							.title('The Time Slot for this particular ME has already been booked by Another TME.')
							.ok('Ok')
							.cancel('');
						$mdDialog.show(confirm).then(function() {
							$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
							$scope.UnCheck_ME();
							//~ $route.reload();
						}, function() {
							$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
							$scope.UnCheck_ME();
							return false;
						});	
					}
					
				});
			}
			
		}
		$rootScope.moveToIDCFlag	=	''; //  changes done here today (27-02-2017)	
		/*New code and Controller added for automated Flow*/
		function appConfirmController($scope,APIServices,$rootScope){
			$scope.newLogic_optns	=	0;
			$scope.click_city_exc	=	0
			$scope.click_pincode_ex	=	0;
			$scope.newLogic_optns	=	$rootScope.newLogic_optns; // new var for new logic auto appt
			$scope.click_pincode_ex	=	$rootScope.click_pincode_ex; // new var for new logic auto appt
			$scope.click_city_exc	=	$rootScope.click_city_exc; // new var for new logic auto appts
			$scope.contractData 	=	$rootScope.contractData.data;
			$scope.eligibleResp 	=	$rootScope.eligibleResp;
			$scope.timearr			=	$rootScope.timearr;
			$scope.dateData			=	$rootScope.dateData;
			$scope.disposeVal		=	returnState.disposeVal;
			$scope.textIns			=	$rootScope.textIns;
			$scope.jrCode			=	'';
			$scope.showcontentone	=	'';
			$scope.showcontentone	=	1;
			$scope.follow_up_arr_new	=	[];
			//~ console.log($scope.eligibleResp.follow_up_arr.errorCode);
			if($scope.eligibleResp.follow_up != undefined && ($scope.eligibleResp.follow_up_arr.errorCode==0 && $rootScope.eligibleResp.follow_up_me_busy_flag == 2 && $rootScope.eligibleResp.follow_up_arr.add_to_prosp != 0)){
				//~ alert("In IF");
				$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.follow_up;
			}else if($scope.eligibleResp.follow_up_arr != undefined && ($scope.eligibleResp.follow_up_arr.errorCode == 1 && $rootScope.eligibleResp.follow_up_me_busy_flag == 2) && $rootScope.eligibleResp.follow_up_arr.add_to_prosp != 0){
				//~ alert("in 1st Else If");
				$scope.eligibleResp 	=	$rootScope.eligibleResp;
				$rootScope.jrCode		=	$scope.eligibleResp.final_me_elig.mktEmpCode;								
				$scope.showcontentone	=	2;
			}else if($scope.eligibleResp.follow_up != undefined && $rootScope.eligibleResp.follow_up_me_busy_flag == 1 && $rootScope.eligibleResp.follow_up_arr != undefined && $rootScope.eligibleResp.follow_up_arr.add_to_prosp != 0){
				//~ alert("in 2nd Else If");
				$scope.showcontentone	=	8;
				//~ alert($scope.showcontentone);
				$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.follow_up;
				$scope.eligibleResp.final_me_elig	=	$rootScope.eligibleResp.final_me_elig;
				//~ $rootScope.jrCode	=	$scope.eligibleResp.follow_up.mktEmpCode;
				$scope.follow_up_arr_new	=	$scope.eligibleResp.follow_up_arr;
				console.log("asdlkfasjdlfaslkfj========>");
				console.log($scope.follow_up_arr_new);
				console.log("<=================asdlkfasjdlfaslkfj");
			}else if($scope.eligibleResp.follow_up == undefined && ($scope.eligibleResp.final_me_elig != undefined && $scope.eligibleResp.final_me_elig.mktEmpCode != '')){
				//~ alert("in 3rd Else If");
				//~ $scope.eligibleResp 	=	$rootScope.eligibleResp;
				if(($scope.eligibleResp.follow_up_arr != undefined && $scope.eligibleResp.follow_up_arr.errorCode == 0) && (($scope.eligibleResp.follow_up_arr.follow_up != undefined || $scope.eligibleResp.follow_up_arr.follow_up != '') && ($scope.eligibleResp.follow_up_arr.follow_up.follow_up_me_found_in_list!=undefined && $scope.eligibleResp.follow_up_arr.follow_up.follow_up_me_found_in_list!='' && $scope.eligibleResp.follow_up_arr.follow_up.follow_up_me_found_in_list==2))){
					//~ alert("in 3rd Else-If If");
					$scope.showcontentone	=	4;
					//~ $scope.showEligResp		=	response;
				}else{
					//~ alert("in 3rd Else-If Else");
					$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.final_me_elig;
					$rootScope.jrCode		=	$scope.eligibleResp.final_me_elig.mktEmpCode;								
					$scope.showcontentone	=	2;
				}
			}else if(($scope.eligibleResp.follow_up_arr != undefined && $scope.eligibleResp.follow_up_arr.errorCode == 1) && ($scope.eligibleResp.final_me_elig != undefined && $scope.eligibleResp.final_me_elig.mktEmpCode != '')){
				$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.final_me_elig;
			}else{
				//~ alert("in last Else");
				$scope.eligibleResp.follow_up	=	$scope.eligibleResp;
				$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.follow_up;
				$scope.eligibleResp.final_me_elig	=	$rootScope.eligibleResp.final_me_elig;
				if($scope.eligibleResp.follow_up == undefined){
					
				}
				$rootScope.jrCode	=	$scope.eligibleResp.follow_up.mktEmpCode;
				if($scope.eligibleResp.follow_up_arr.add_to_prosp == 1){
					$scope.showcontentone	=	6;// changes made here by Apoorv Agrawal - today (27-01-2017)
				}else if($scope.eligibleResp.follow_up_arr.add_to_prosp == 0){
					$scope.showcontentone	=	7;
				}else if($scope.eligibleResp.follow_up_arr.ecs_mandate == 0){
					$scope.showcontentone	=	9;
				}
			}
			//~ console.log($scope.eligibleResp);
			var monthNames = [
			  "January", "February", "March",
			  "April", "May", "June", "July",
			  "August", "September", "October",
			  "November", "December"
			];
			var date = new Date($scope.dateData);
			var day = date.getDate();
			var monthIndex = date.getMonth();
			var year = date.getFullYear();
			$scope.displayDate	=	(day + ' ' + monthNames[monthIndex] + ' ' + year);
			
			$scope.yes_proceed	=	function(ev){
				if($scope.get_click_ev_alloc_appt_flg == 'ALF'){
					APIServices.getTime($rootScope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$rootScope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,$rootScope.disposeVal,$rootScope.textIns,$rootScope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'chck',$rootScope.eligibility,$rootScope.list_of_me_pincodewise,$rootScope.TMERANK,'ignore_followUp','logInsert',$rootScope.TOTALTME,'').success(function(response){
						if(response.follow_up == undefined){
							if(response.eligiblerrorCode	==	1){
								$scope.showcontentone	=	3;
							}else{
								$rootScope.eligibleResp		=	response;
								$rootScope.showEligResp		=	response.data;
								$scope.eligibleResp 		=	$rootScope.eligibleResp;
								$rootScope.jrCode	=	$scope.eligibleResp.final_me_elig.mktEmpCode;								
								$scope.showcontentone	=	2;
							}
						}
						// Appending dialog to document.body to cover sidenav in docs app
						//TEST APPOINTMENT PLEASE IGNORE
					});
				}else{
					APIServices.getTime($rootScope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$rootScope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,$rootScope.disposeVal,$rootScope.textIns,$rootScope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'chck',$rootScope.eligibility,$rootScope.list_of_me_pincodewise,$rootScope.TMERANK,'','logInsert',$rootScope.TOTALTME,'').success(function(response){
						console.log(response);
						$rootScope.moveToIDCFlag	=	response.moveToIDCFlag; // lines added here today (27-02-2017)
						if(response.follow_up == undefined && response.follow_up == null){
							if(response.eligiblerrorCode	==	1){
								$scope.showcontentone	=	3;
							}else{
								$rootScope.eligibleResp		=	response;
								$rootScope.showEligResp		=	response.data;
								$scope.eligibleResp 		=	$rootScope.eligibleResp;
								$rootScope.jrCode	=	$scope.eligibleResp.final_me_elig.mktEmpCode;
								//~ console.log("$rootScope.jrCode=========>"+$rootScope.jrCode);
								$scope.showcontentone	=	2;
							}
						}else{
							//~ if(response.follow_up.follow_up_me_found_in_list == 2){
								//~ $scope.showcontentone	=	4;
								//~ $scope.showEligResp		=	response;
							//~ }
							if(response.follow_up_me_busy_flag == 1){
								$rootScope.eligibleResp		=	response;
								$rootScope.showEligResp		=	response.data;
								$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.follow_up;
								$scope.eligibleResp.final_me_elig	=	$rootScope.eligibleResp.final_me_elig;
								$rootScope.jrCode	=	$scope.eligibleResp.follow_up.mktEmpCode;
								$scope.showcontentone	=	8;
								
								$scope.follow_up_arr_new	=	response.follow_up_arr;
							}else{
								$rootScope.eligibleResp		=	response;
								$rootScope.showEligResp		=	response.data;
								$rootScope.eligibleResp.final_me_elig	=	$scope.eligibleResp.follow_up;
								$scope.eligibleResp.final_me_elig	=	$rootScope.eligibleResp.final_me_elig;
								$rootScope.jrCode	=	$scope.eligibleResp.follow_up.mktEmpCode;
								//~ console.log("$rootScope.jrCode=========>"+$rootScope.jrCode);
								//~ console.log($rootScope.eligibleResp.final_me_elig);
								if(response.follow_up_arr.add_to_prosp == 1){
									$scope.showcontentone	=	6;// changes made here by Apoorv Agrawal - today (27-01-2017)
								}else if(response.follow_up_arr.add_to_prosp == 0){
									$scope.showcontentone	=	7;
								}else if(response.follow_up_arr.ecs_mandate == 0){
									$scope.showcontentone	=	9;
								}
							}
						}
						$scope.final_proceed();
						// Appending dialog to document.body to cover sidenav in docs app
						//TEST APPOINTMENT PLEASE IGNORE
					});
				}
			}
			// changes made here by Apoorv Agrawal - today (27-01-2017)
			$scope.allocate_fresh	=	function(ev){
				APIServices.getTime($rootScope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$rootScope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,$rootScope.disposeVal,$rootScope.textIns,$rootScope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'chck',$rootScope.eligibility,$rootScope.list_of_me_pincodewise,$rootScope.TMERANK,'ignore_followUp','logInsert',$rootScope.TOTALTME,'').success(function(response){
					if(response.follow_up == undefined){
						if(response.eligiblerrorCode	==	1){
							$scope.showcontentone	=	3;
						}else{
							$rootScope.eligibleResp		=	response;
							$rootScope.showEligResp		=	response.data;
							$scope.eligibleResp 		=	$rootScope.eligibleResp;
							$rootScope.jrCode	=	$scope.eligibleResp.final_me_elig.mktEmpCode;
							//~ console.log("$rootScope.jrCode=========>"+$rootScope.jrCode);
							$scope.showcontentone	=	2;
						}
					}
					// Appending dialog to document.body to cover sidenav in docs app
					//TEST APPOINTMENT PLEASE IGNORE
				});
			}
			$scope.closethepopup	=	function(ev){
				$mdDialog.hide();
			}
			$scope.final_proceed	=	function(ev){
				$('.save_btn_final').attr('disabled','disabled'); // added on 03-03-2017
				//~ alert("final_proceed");
				APIServices.checkSlotForMe($scope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$scope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,returnState.disposeVal,$scope.textIns,$rootScope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add).success(function(resp){
					if(resp.errorCode_checkSlotForMe	==	0){
						APIServices.getTime($rootScope.timearr,DATACITY,$rootScope.pincodeSelected,$rootScope.contractData.data.parentid,$rootScope.dateData,$rootScope.employees.results.mktEmpCode,$rootScope.employees.results.empName,$rootScope.employees.results.extn,$rootScope.disposeVal,$rootScope.textIns,$rootScope.jrCode,$rootScope.AllMeFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,'nochck','','','','','logNoInsert',$rootScope.TOTALTME,$rootScope.moveToIDCFlag).success(function(response){
							$('.save_btn_final').removeAttr('disabled'); // added on 03-03-2017
							if(response.errorCode == 0) {
								$scope.callbackdate		=	response.callbackdate;
								$scope.data_city		=	DATACITY;
								$rootScope.setNoMenu	=	0;
								if(STATID && $.inArray(parseInt(STATID),$rootScope.autoDialer) !== -1) {
									if(returnState.disposeVal==24 || returnState.disposeVal== 22  ||  returnState.disposeVal== 124 ||  returnState.disposeVal== 210 || returnState.disposeVal == 317){
										callback_followup(returnState.disposeVal,$scope.callbackdate,$rootScope.employees.remoteAddr);
									}else{
										disposition_set(returnState.disposeVal,$rootScope.employees.remoteAddr);
									}
								}else{
								}
								console.log('Here 2',response);
								if(response.errorStatus_tblContractAllocation_MoveToIDC.allocated_resp_type != '2'){
									if(response.live_flg == 1){
										APIServices.stopTimer(returnState.disposeVal,$rootScope.contractData.data.parentid,$rootScope.employees.results.empName).success(function(response){
											window.location.href=response.live_url;
										});
									}else{
										APIServices.stopTimer(returnState.disposeVal,$rootScope.contractData.data.parentid,$rootScope.employees.results.empName).success(function(response){
											window.location.href = '../newTme/welcome';
										});
									}
								}else{
									$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
									var confirm = $mdDialog.confirm()
										.title('The Time Slot for this particular ME has already been booked by Another TME.')
										.ok('Ok')
										.cancel('');
									$mdDialog.show(confirm).then(function() {
										$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
										$scope.UnCheck_ME();
										//~ $route.reload();
									}, function() {
										$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
										$scope.UnCheck_ME();
										return false;
									});	
								}
							}
						});
					}else{
						$mdDialog.hide();
						$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
						var confirm = $mdDialog.confirm()
							.title('The Time Slot for this particular ME has already been booked by Another TME.')
							.ok('Ok')
							.cancel('');
						$mdDialog.show(confirm).then(function() {
							$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
							$scope.UnCheck_ME();
							//~ $route.reload();
						}, function() {
							$('.save_btn_final_non').removeAttr('disabled'); // added on 03-03-2017
							$scope.UnCheck_ME();
							return false;
						});
					}
				});
			}
			$scope.no_stop	=	function(ev){
				$mdDialog.hide();
			}
			/* VAM -> View All Me City wise ME List
			 * VLM -> View List of pincode wise ME List
			 * ALF -> AlLocate Fresh
			 * FIP -> Auto Allocate only
			*/
			$scope.get_click_ev_alloc_appt_flg = '';
			$scope.set_flag	=	function(get_click,ev){
				if(get_click == 'VAM'){
					$scope.get_click_ev_alloc_appt_flg	=	'VAM';
				}else if(get_click == 'VLM'){
					$scope.get_click_ev_alloc_appt_flg	=	'VLM';
				}else if(get_click == 'ALF'){
					$scope.get_click_ev_alloc_appt_flg	=	'ALF';
					//~ $scope.showcontentone	=	6;
				}else if(get_click == 'FIP'){
					$scope.get_click_ev_alloc_appt_flg	=	'FIP';
					//~ $scope.showcontentone	=	6;
				}else if(get_click == 'CLOSE'){
					$scope.get_click_ev_alloc_appt_flg	=	'CLOSE';
				}
				console.log(get_click);
			}
			
			$scope.decide_appt_flow	=	function(ev){
				if($scope.get_click_ev_alloc_appt_flg == 'VAM'){
					$mdDialog.hide();
					$rootScope.AllMeFlag	 =	1;
					$rootScope.AllMeClickFlg =	1;
					$rootScope.bypass_autoAlloc	=	0;
					$rootScope.click_pincode_ex	=	0;
					$scope.click_pincode_ex	=	$rootScope.click_pincode_ex;
					
					$rootScope.click_city_exc	=	1;
					$scope.click_city_exc	=	$rootScope.click_city_exc;
					
					$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
					//~ $scope.get_click_ev_alloc_appt_flg	=	'VAM';
				}else if($scope.get_click_ev_alloc_appt_flg == 'VLM'){
					$mdDialog.hide();
					$rootScope.bypass_autoAlloc	=	1;
					$rootScope.click_pincode_ex	=	1;
					$scope.click_pincode_ex	=	$rootScope.click_pincode_ex;
					
					$rootScope.click_city_exc	=	0;
					$scope.click_city_exc	=	$rootScope.click_city_exc;
					
					$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,0,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
					//~ $scope.get_click_ev_alloc_appt_flg	=	'VLM';
				}else if($scope.get_click_ev_alloc_appt_flg == 'ALF'){
					//~ $scope.get_click_ev_alloc_appt_flg	=	'ALF';
					
					$scope.showcontentone	=	6;
					
				}else if($scope.get_click_ev_alloc_appt_flg == 'FIP'){
					//~ $scope.get_click_ev_alloc_appt_flg	=	'FIP';
					$scope.showcontentone	=	6;
				}else if($scope.get_click_ev_alloc_appt_flg	==	'CLOSE'){
					$mdDialog.hide();
				}
			}
			/*Function To Pupolate the pincode wise list of ME's*/
			$scope.view_list_me 	=	function(ev){
				$mdDialog.hide();
				$rootScope.bypass_autoAlloc	=	1;
				$rootScope.click_pincode_ex	=	1;
				$scope.click_pincode_ex	=	$rootScope.click_pincode_ex;
				
				$rootScope.click_city_exc	=	0;
				$scope.click_city_exc	=	$rootScope.click_city_exc;
				
				$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,0,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
			}
			/*Function To Pupolate the City wise list of ME's*/
			$scope.view_all_me 	=	function(ev){
				$mdDialog.hide();
				$rootScope.AllMeFlag	 =	1;
				$rootScope.AllMeClickFlg =	1;
				$rootScope.bypass_autoAlloc	=	0;
				$rootScope.click_pincode_ex	=	0;
				$scope.click_pincode_ex	=	$rootScope.click_pincode_ex;
				
				$rootScope.click_city_exc	=	1;
				$scope.click_city_exc	=	$rootScope.click_city_exc;
				
				$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.bypass_autoAlloc);
			}
		}
		
		/*
		 *Function to get AllMe Data 
		 * parameters Send is all_me_flag = 1, disposeVal = 25/99, And Flow is Non-Grab and pincode is empty
		 */
		 $scope.showPrompt = function(ev) {
			// Appending dialog to document.body to cover sidenav in docs app
			$rootScope.AllMeClickFlg	=	1; // new variable declared here
			if($rootScope.showAllMEButton	==	1){
				$mdDialog.show({
				controller: logindialog,
				templateUrl: 'partials/dialogAllme.html',
				parent: angular.element(document.body),
				targetEvent:ev
				})
				.then(function(answer) {
					$scope.alert = 'You said the information was "' + answer + '".';
				}, function() {
					$scope.alert = 'You cancelled the dialog.';
				});
			}else{
				$rootScope.AllMeFlag	=	1;
				$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME);
				$('#allMebutton').addClass("hide");
				$('#GoBack').removeClass("hide");
				$("#AddAlternateAdd").addClass('hide');
			}
		}
		
		/*controller for login details*/
		function logindialog($scope,$mdDialog) {
			$scope.contractData		=	$rootScope.contractData;
			$scope.parentid			=	$rootScope.contractData.data.parentid;
			$scope.pincode			=	pincode;
			$scope.dateSelected		=	$rootScope.dateData;
			$scope.disposeVal		=	returnState.disposeVal;
			$rootScope.AllMeFlag	=	1;
			$scope.itemsPerPage 	=	9;
			$scope.currentPage 		= 	0;
			var loginIdFlag			=	0;
			$scope.loginMe	=	function(all_me_loginid,all_me_password){
				if(all_me_loginid=='' || all_me_loginid == undefined){
					loginIdFlag	=	0;
				}else{
					loginIdFlag	=	1;
				}
				if(all_me_password	==	'' || all_me_password == undefined){
					
					loginpassFlag	=	0;
				}else{
					loginpassFlag	=	1;
				}
				if(loginIdFlag	==	0){
					$('#errorContainer').removeClass('hide');
					$('.ErrorLabel').text('* Please provide Login-Id');
					$('#melogId').focus();
					setTimeout(function() {
						$('#errorContainer').addClass('hide');
						$('#melogId').blur();
					},2000);
					
					return false;
				}
				if(loginpassFlag	==	0){
					$('#errorContainer').removeClass('hide');
					$('.ErrorLabel').text('* Please provide Password');
					$('#mePassword').focus();
					setTimeout(function() {
						$('#errorContainer').addClass('hide');
						$('#mePassword').blur();
					},2000);
					
					return false;
				}
				if(loginIdFlag==1 && loginpassFlag==1){
					$('#errorContainer').addClass('hide');
					$("#AddAlternateAdd").addClass('hide');
					APIServices.allMelogin(all_me_loginid,all_me_password).success(function(response){
						if(response.errorCode == 0) {
							if(response.ls	==	1){
								$mdDialog.hide();
								$('#allMebutton').addClass("hide");
								$('#GoBack').removeClass("hide");
								$rootScope.AllMeFlag	=	1;
								$rootScope.getDataTime(1,$scope.contractData,$scope.dateSelected,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME);
							}else{
								$('#errorContainer').removeClass('hide');
								$('.ErrorLabel').text('*LoginId or password not match');
								setTimeout(function() {
									$('#errorContainer').addClass('hide');
								},2000);
							}
						}
					});
				}
			}
			$scope.resetMe	=	function(all_me_loginid,all_me_password){
				$scope.all_me_loginid	=	'';
				$scope.all_me_password	=	'';
			}
			$scope.cancelAllMe	=	function(){
				$mdDialog.hide();
			}
		}
		/*
		 *Function To Add Alternate Address
		 * added by Apoorv Agrawal 
		 */
		$scope.addAlternateAdd	=	function(ev) {
			$rootScope.alertnateAddress_click	=	1;
			// Appending dialog to document.body to cover sidenav in docs app
			$mdDialog.show({
				controller: addAlternateAddr,
				templateUrl: 'partials/addAlternateAddr.html',
				parent: angular.element(document.body),
				targetEvent:ev
			})
		}
		function addAlternateAddr($scope,$mdDialog){
			$scope.contractData		=	$rootScope.contractData;
			$scope.moduleType		=	$rootScope.moduleType;
			$scope.companyname		=	$scope.contractData.data.companyname;
			$scope.data_city		=	DATACITY;// For PAN INDIA
			//$scope.remote_city	=	$scope.contractData.data.city;// for remote Cities
			$scope.parentid			=	$scope.contractData.data.parentid;
			$scope.areaList			=	[];
			$scope.pincodeList		=	[];
			$scope.area_selected	=	'';
			$scope.areaCount		=	0;
			$scope.pincodeCount		=	0;
			$scope.pincodeSelected	=	'';
			$scope.areaDefault		=	'Select Area';
			$scope.pincodeDefault	=	'Select Pincode';
			$scope.isDisabled		=	false;
			$scope.selectedStreet	=	'';
			$scope.changeInStreet	=	0;
			$scope.bldingselected	=	'';
			$scope.lanmarkselected	=	'';
			$scope.countryCode		=	$rootScope.contractData.data.country;
			$scope.ucode			=	$rootScope.employees.results.mktEmpCode;
			$scope.state			=	$rootScope.contractData.data.state;
			$scope.directiveFl		=	0;
			/*
			 * function to get All Area based on data_city on pop-up load
			 * Added by Apoorv Agrawal
			*/
			APIServices.getArea(DATACITY).success(function(response){
				if(response.errorCode == 0) {
					$scope.areaList		=	response.data.area_list;
					$scope.areaDefault	=	'Select Area';
				}
			});
			/*
			 * Function to check the changes in street
			*/
			
			$scope.changeStreet		=	function(){
				$scope.changeInStreet	=	0;
				if($scope.selectedStreet=='' || $scope.selectedStreet==undefined){
				}
			}
			/*
			 * function to get All Pincode based on data_city on pop-up load
			 * Added by Apoorv Agrawal
			*/
			
			$scope.getPincodefn	=	function(DATACITY,area_selected,directiveFl){
				APIServices.getPincode(DATACITY,area_selected).success(function(response){
					if(response.errorCode == 0) {
						$scope.pincodeList	=	response.data;
						if(response.pincode_count==1){
							$scope.selected_pincode_list	=	$scope.pincodeList;
							$scope.pincodeCount				=	response.pincode_count;
							$scope.pincodeSelected			=	$scope.pincodeList[0];
							$scope.pincodeDefault			=	$scope.pincodeSelected;
							$scope.pincodeDefault			=	parseInt($scope.pincodeDefault);
						}else{
							$scope.pincodeDefault			=	'Select Pincode';
							$scope.selected_pincode_list	=	$scope.pincodeList;
							$scope.pincodeCount				=	response.pincode_count;
							if($scope.directiveFl==0){
								$scope.pincodeSelected			=	'';
							}else{
							}							
						}
						$scope.$apply();
					}
				});
			}
			$scope.getPincodefn(DATACITY,$scope.area_selected,$scope.directiveFl);
			$scope.areaSelected 		=	function(){
				$scope.selectedStreet	=	'';
				$scope.directiveFl		=	0;
				$scope.getPincodefn(DATACITY,$scope.area_selected,$scope.directiveFl);
			}
			$scope.PincodeSelected 	=	function(){
			}
			
			/*
			 * function to insert Alternate Address Data
			*/
			$scope.addAlternateAddress	=	function(){
				if($scope.changeInStreet	!=	1 && $scope.selectedStreet!=''){
					$('#ErrorAddLabel').removeClass('hide');
					$('#ErrorAddLabel').text('* Please Select Street From Auto-Suggest');
					setTimeout(function() {
						$('#ErrorAddLabel').addClass('hide');
					},2000);
					return false;
				}
				if($scope.pincodeSelected=='' || $scope.pincodeSelected== undefined){
					$('#ErrorAddLabel').removeClass('hide');
					$('#ErrorAddLabel').text('* Please Select Pincode');
					setTimeout(function() {
						$('#ErrorAddLabel').addClass('hide');
					},2000);
					return false;
				}
				$rootScope.alternate_addrss_pincode	=	$scope.pincodeSelected;
				APIServices.saveAlternaleAdd(DATACITY,$scope.companyname,$scope.bldingselected,$scope.selectedStreet,$scope.lanmarkselected,$scope.area_selected,$scope.pincodeSelected,$scope.state,$scope.ucode,$scope.countryCode,$scope.parentid).success(function(response){
					if(response.errorCode == 0) {
						$rootScope.alternateAddFlag		=	2;
						$rootScope.pincodeSelected		=	$scope.pincodeSelected;
						$('.success').addClass('hide');
						$('#SaveContainer').removeClass('hide');
						$('#SaveContainerText').addClass('successStyle');
						$('#SaveContainerText').text("Data Saved !!");
						setTimeout(function() {
							$('.success').removeClass('hide');
							$('#SaveContainer').addClass('hide');
							$('#AddAlternateAdd').blur();
							$mdDialog.hide();
						},100);
						$rootScope.AllMeFlag	=	0;
						if($('#allMebutton').is(":visible")){
							$('#allMebutton').removeClass('hide');
							$('#GoBack').addClass('hide');
						}else{
							$('#allMebutton').removeClass('hide');
							$('#GoBack').addClass('hide');
						}
						$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add);
					}else{
						$('.success').addClass('hide');
						$('#SaveContainer').removeClass('hide');
						$('#SaveContainerText').addClass('FailStyle');
						$('#SaveContainerText').text("Some Error While Saving Address Please Try Again !!");
						setTimeout(function() {
							$('.success').removeClass('hide');
							$('#SaveContainer').addClass('hide');
						},100);
					}
					$scope.$apply();
				});
				
			}
			/*
			 * Function To Close The dialog Box for alternate Address
			 */
			 $scope.ClosepopUpAtlAdd	=	function(){
				$('#AddAlternateAdd').blur();
				$mdDialog.hide();
			}
		}
		$scope.replaceCss	=	function(){
			$('.sliding_me_div').animate({left:0},20);
			$scope.showLeftPos	=	0;
		}
		/*
		 * Function To toggle Between All ME and Go Back To Me
		 */
		$scope.toggleAllMeToMe	= function(){
			$('#allMebutton').removeClass("hide");
			$('#GoBack').addClass("hide");
			$("#AddAlternateAdd").removeClass('hide');
			$rootScope.AllMeFlag	=	0;
			$scope.alternateAddFlag	=	1;
			$rootScope.AllMeClickFlg	=	0;
			$rootScope.getDataTime(1,$scope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$scope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME);
		}
		var counter 			=	0;
		$scope.currentIndex 	=	[];
		$scope.hello			=	1;
		$scope.setTimeOut  = function(){	
			$scope.radioMod = "";
		}
		/*
		 * Code For Shifting/ scrolling Start
		*/
		$scope.setPageShiftLeft	=	function(currentPage){
			$scope.itemsPerPage	=	($scope.itemsPerPage+10);
			if($scope.currentPage < $scope.pageCount()) {
			  $scope.currentPage	=	currentPage;
			}
			$scope.currentPage	=	0;
			setTimeout(function() {
				var i=0;
				$scope.setWidthRow	=	0;
				$('.headDiv').each(function() {
					i++;
					$scope.setWidthRow += $(this).outerWidth()+250;
				});
				$scope.$apply();
				return i<(10);
			},1000);
			var showLeftPos	=	$('.bodyRadioTable').outerWidth() - $('.timingSlotsFree').outerWidth();
			if(Math.abs(parseInt($('.rotateRightRadio').css("left").slice(0,-2)) -(showLeftPos-73)) < $('.rotateRightRadio').outerWidth()) {
				$('.rotateRightRadio').animate({left:parseInt($('.rotateRightRadio').css("left").slice(0,-2))-(showLeftPos-73)},100);
			}else{
			}
		};
		
		$scope.setPageShiftRight	=	function(currentPage){
			if(parseInt($('.rotateRightRadio').css("left").slice(0,-2)) != 0) {
				var showLeftPos	=	$('.bodyRadioTable').outerWidth() - $('.timingSlotsFree').outerWidth();
				$('.rotateRightRadio').animate({left:parseInt($('.rotateRightRadio').css("left").slice(0,-2))+(showLeftPos-73)},100);
			}
		};
		/*
		 * Code For Shifting/ scrolling END
		*/
		$scope.selectTimingsGrab = function(key,index,ev){
			$scope.timearr 		=	key;
			if($scope.whichFlow == "normal" && $rootScope.automated_flg == 0){
				$scope.radioMod	=	index+"_"+key;
				/* Code for handling time problem*/
				var time		=	key;
				var timeArray 	= 	'';
				var t2 			=	"01:00";
				var t3			=	'';
				t3 				=	t2.split(":");
				timeArray 		=	time.split(":");
				t2 				=	(parseInt(t3[0])*60+parseInt(t3[1]));
				timeinMints 	=	(parseInt(timeArray[0])*60+parseInt(timeArray[1]));
				ti 				=	timeinMints+t2;
				timeVal 		=	Math.round(ti/60);
				var timeMod 	=	0;
				timeMod			=	(ti%60);
				if(timeMod	>=	5){
					timeMod		=	'30';
					timeVal 	=	(timeVal-1);
				}else{
					timeMod 	=	'00';
				}
				if(parseInt(timeVal) == 24){
					timeVal 	= 	'23';
					timeMod		=	'30';
				}
				var t5 		= 	'';
				t5			=	timeVal.toString()+":"+timeMod;
				t5 			=	t5.toString();// Final Key
				//~ console.log($scope.timeData.data.timeArr);
				//~ console.log(t5);
				//~ console.log($scope.timeData.data.timeArr.t5);
				/* Code for handling time problem end*/
				$scope.jrCode = '';
				$scope.jrCode = index; 
				var blockFlag = 0;
				if($scope.timeData.data.timeArr[t5] != undefined && ($scope.timeData.data.timeArr[t5].flag == 1 || $scope.timeData.data.timeArr[t5].flag == 2)) {
					if(($scope.timeData.data.timeArr[t5].allocData[$scope.jrCode] == undefined)){
						blockFlag = 2;
					}else{
						blockFlag = 1;
					}
				} else {
					blockFlag = 2;
				}
				if(blockFlag == 2){
					APIServices.meisabsent(index,$rootScope.dateData,key).success(function(response) {
						//console.log(response);
						if(response.data.showPopUp == 0){
							$scope.timearr	=	'';
							$mdDialog.show(
							  $mdDialog.alert()
								.parent(angular.element(document.querySelector('#popupContainer')))
								.clickOutsideToClose(false)
								.escapeToClose(false)
								.title('')
								.content('ME is Absent for this time slot. Please select a different Time Slot.')
								.ariaLabel('Alert Dialog Demo')
								.ok('Close')
								.targetEvent(ev)
							).then(function() {
								$scope.radioMod = "";
							});
							return false;
						}
					});
				}
				if(blockFlag == 1){
						// Appending dialog to document.body to cover sidenav in docs app
						// Modal dialogs should fully cover application
						// to prevent interaction outside of dialog
						$scope.timearr	=	'';
						$mdDialog.show(
						  $mdDialog.alert()
							.parent(angular.element(document.querySelector('#popupContainer')))
							.clickOutsideToClose(false)
							.escapeToClose(false)
							.title('Error !!!!')
							.content('You can not make appointment for the selected time, as the time difference is too low !!!')
							.ariaLabel('Alert Dialog Demo')
							.ok('Ok. I got it !!!!')
							.targetEvent(ev)
						).then(function() {
							$scope.radioMod = "";
						  });
					blockFlag	=	0;
					return false;
				}else{
					//~ alert("sdfasdf");
					//~ return false;
					// Make The API Call HERE 
					//key1 ===> appt slot,dataMe.mktEmpCode ===> Appt ME
					// $rootScope.dateData ===> date of Appt
				}
			}else{
				$scope.jrCode = ''; 
			}
			
			angular.forEach($scope.selColor,function(value,key) {
				$scope.selColor[key]	=	"#c9e7ff";
			});
			$scope.selColor[index]		=	"#fff";
		}
		/*
		 * Code to impelemt lazy loading
		 */
		tmeModuleApp.filter('offset', function() {
		  return function(input, start) {
			start = +start;
			return input.slice(start);
		  };
		});
		/*
		 * function to implement Allocate to ME for Super CAD TME's 
		*/
		$scope.AllocToME 	=	function(){
			if($("#AllocToME").length>0){
				$("#AllocToME").addClass('hide');
			}
			if($("#backToGrab").length>0){
				$("#backToGrab").removeClass('hide');
			}	
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME);
		}
		/*
		 * function to go back to grab flow again
		*/
		$scope.backGrab 	=	function(){
			$rootScope.display_allocateToME = 0;
			$rootScope.alloc_to_ME_TME		=	0;
			$rootScope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,$rootScope.AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,$rootScope.alloc_to_ME_TME);
			if($("#AllocToME").length>0){
				$("#AllocToME").removeClass('hide');
			}
			if($("#backToGrab").length>0){
				$("#backToGrab").addClass('hide');
			}
		}
		/*
		 *Function to implement Click To Call 
		 */
		$scope.clickToCallFunc	=	function(mobnumber,allocId){
			if(mobnumber	==	"Number NotAvl"){
				alert("Number Not avilable");
				return false;
			}else{
				mobnumber	=	parseInt(mobnumber);
				var aspect_color	=	'green';
				var dnc_flag		=	''; // or it can be 5
				var REMOTE_ADDR		=	$rootScope.employees.remoteAddr;
				/*   Function To make ClickToCall For Me's	 */
				if(STATID && $.inArray(parseInt(STATID),$rootScope.autoDialer) !== -1) {
					if(returnState.disposeVal==25 || returnState.disposeVal== 99){
						//alert("Click To CALl Functionality");
						window.parent.ctiCallfunction(mobnumber,REMOTE_ADDR,aspect_color,'',allocId,dnc_flag);
					}
				}else{
				}
			}
		};
		/*New Code Added Here Start 08-02-2017*/
		$scope.showbusyPupUp	=	function(key,index,ev){
			angular.forEach($scope.selColor,function(value,k) {
				//~ $scope.selColor[k]	=	"#c9e7ff"; // blue dark
			});
			$scope.timearr	=	'';
			$scope.hideMainPage		=	1;
			$mdDialog.show({
				controller :  newConfirmController,
				templateUrl: 'partials/appConfirm.html',
				parent	   : angular.element(document.body),
				clickOutsideToClose: false,
				escapeToClose: false,
				targetEvent: ev,
			});
		}
		function newConfirmController($scope,APIServices){
			//~ alert('newConfirmController');
			$scope.showcontentone	=	3;
			$scope.closethepopup	=	function(ev){
				$mdDialog.hide();
				$scope.showcontentone	=	'';
			}
		}
		/*New Code Added Here END 08-02-2017*/
	});
});

function disposition_set($stVal,remoteAddr) {
	window.parent.fn_dispostion($stVal,remoteAddr);
}
function callback_followup($stVal,callBackDate,remoteAddr){
	window.parent.fn_callback_followup($stVal,callBackDate,remoteAddr);
}
