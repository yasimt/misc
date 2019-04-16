define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('bformMulticityController', function($scope, $location, APIServices,Paths,$timeout,$rootScope,$http,$state,$cookieStore,$mdDialog,$mdToast,$mdSidenav,$stateParams,CONSTANTS,returnState) {
		$rootScope.layout = ''; // new Code Added for handling new Design
		$rootScope.setNoMenu	=	1;
		var self = this; 
		$scope.national_val = [];
		$scope.state_name = {};
		//$scope.national_val['listing_type'] = 'national';
		$scope.showLoader	=	0;
		$scope.data_city	=	DATACITY;
		$rootScope.parentid	=	returnState.paridInfo;
		$scope.zone_type ={};
		$scope.selected_zone =[];
		$scope.selected_state =[];
		$scope.state_all = {};
		$rootScope.zone_cb = {};
		$scope.isReplyForm = [];
		$scope.min_upfront_cost = 0;
		$scope.show_warning = false;
		$scope.show_Block 	= false;
		$scope.show_overlay = false;
		$scope.show_data_error = false;
		$scope.isReplyForm22 = [];
		$scope.stop_data = 0;
		//$scope.getShadowInfoData = [];
		//$scope.zone_cb = {};
	
	
		
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
				
					APIServices.getBudgetData(3,1,12,returnState.paridInfo,DATACITY,$rootScope.employees.hrInfo.data.empname,$rootScope.companyTempInfo.data.pincode,1,0).success(function(response3) {
					
					$scope.budget_arr = Object.keys(response3).map(function(k) { return response3[k] });
					//console.log($scope.budget_arr);
					
				/*	$scope.min_upfront_cost = response3.result.upfront_national_budget;
					//console.log($scope.min_upfront_cost);
					/*$scope.min_upfront_cost_initial = response3.result.minupfrontbudget_national;
					$scope.max_upfront_cost = response3.result.maxupfrontbudget_national;
					$scope.state_upfront_cost = response3.result.stateupfrontbudget_national;
					//console.log(response3.result);*/
					});
				});			
			}
		});
		
		$scope.go_to_bform = function(){
			$scope.show_overlay = false;
			$('#error_popup').css("display","none");
			$('#error_content').html('');
			window.location.href= '../business/bform.php?navbar=yes';
		}
		
		
		APIServices.bformvalidation($scope.parentid).success(function(response){
			console.log(response);
			if(response != ''){
				$scope.show_overlay = true;
				$scope.show_data_error = true;
				$('#error_content').html(response);
			}else {
				$('#error_content').html('');
				$scope.show_data_error = false;
			}
		});
		
		$rootScope.extraHandler	=	$stateParams.page;
		
		
		
		var request = APIServices.getShadowTabData($scope.parentid);
		
		request.promise.then(function(result) {
			$scope.getShadowInfoData = result.data;
			
				APIServices.checkmulticity($scope.parentid,$scope.getShadowInfoData.catidlineage_nonpaid).success(function(response) {
				
				
				
			if(response.city_errorCode == 2){
				$scope.national_val['listing_type'] = 'local';
				
			}else if(response.mul_errorCode == 3){
				$scope.national_val['listing_type'] = 'national';
				$scope.show_listing_val  = true;
				$scope.national_val['national_type'] = response['selected_data']['state_zone'];
				$scope.sel_city = response['selected_data']['Category_city'].split(',');
				
				
				////console.log($scope.min_upfront_cost);
					
				$scope.show_options('state','');	
				var i = 0;
				
				angular.forEach($scope.sel_city,function(value,key)  {
					
					$rootScope.zone_cb[value] = true; 
					if(value != "") {
						
						$scope.selected_zone[i] = value;
						i++;
					}
				});
				
				
				
			}else if(response.city_errorCode != 1){
				$scope.national_val['listing_type'] = "local"
			}
			
			if(response.city_errorCode == 1){
				$scope.disable = 'local';
				$scope.disabled_msg =  response.city_data;
			}else if(response.mul_errorCode == 1){
				$scope.disable = 'mul';
				$scope.disabled_msg =  response.mul_data;
			}
			
			if(response.ecs_status == 1){
				$scope.show_warning = true;
				$scope.show_overlay = true;
				
			}
			
			if(response.block_edit == 1 && $rootScope.employees.results.team_type.toUpperCase() != "RD" && $rootScope.employees.results.team_type.toUpperCase() != "BD"){
				$scope.show_Block = true;
				$scope.show_overlay = true;
				
			}
				
		
	
		});
			 
		
		}, function(reason) {
			//console.log(reason);
		});
		
		
		
		$scope.show_options = function(type,ev) {
			//console.log($scope.min_upfront_cost);
			//$scope.min_upfront_cost_data = $scope.min_upfront_cost;
			$scope.selected_zone = [];
			$rootScope.zone_cb ={};
			if(type=="state"){
				APIServices.getStateListings($scope.parentid).success(function(response) {
					if(response.errorCode == 0){
						$scope.zoneData = response.data;
						$scope.min_upfront_cost_initial = response.minupfrontbudget_national;
						$scope.min_upfront_cost = response.minupfrontbudget_national;
						$scope.max_upfront_cost= response.maxupfrontbudget_national;
						$scope.state_upfront_cost = response.stateupfrontbudget_national;
						
						
						//$scope.check_data();
						
						angular.forEach($scope.zoneData,function(val,key){
							angular.forEach(val,function(val_d,key_d){
							
								
								if(typeof $scope.state_name[key] !='undefined')
								$scope.state_name[key]  = $scope.state_name[key]+ ',' +key_d.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
								else
								$scope.state_name[key] = key_d.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
								//	console.log(val);
								//	console.log(key);
								
							});
						});
						$scope.check_data();
						
					}else {						
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.errorMsg;
					}
				});
			
			}
			
		}
		
		$scope.check_data  = function() {
			
			//console.log($scope.min_upfront_cost);
			var i = 0
			angular.forEach($scope.zoneData,function(val,key){
				
				angular.forEach(val,function(val_data,key_data){
					
					angular.forEach($scope.sel_city,function(val_city,key_city){
					
					for(var j=0; j< val_data.length ; j++)
					{ 	
						if(val_city === val_data[j].city)
						{
								var checkdata = $scope.selected_state.indexOf(key_data);
								////console.log(checkdata);
								
								if(checkdata  == -1)
								{
									if($scope.min_upfront_cost <= $scope.max_upfront_cost)
									{
										$scope.min_upfront_cost = $scope.min_upfront_cost + $scope.state_upfront_cost;
									}
									$scope.zone_type[key_data] = true;
									$scope.zone_type[key] = true;
									
									$scope.selected_state.push(key_data);
									
									i++;
								}
							
							//console.log($scope.min_upfront_cost);
						}
					}
					});
				}); 
			});
			
			
			//console.log($scope.min_upfront_cost);
			//console.log($scope.selected_state.length);
			
			
			if($scope.min_upfront_cost >= $scope.max_upfront_cost && $scope.selected_state.length == 6)
			{
				$scope.min_upfront_cost = $scope.max_upfront_cost - 4000;
			}
			else if($scope.min_upfront_cost >= $scope.max_upfront_cost)
			{
				$scope.min_upfront_cost = $scope.max_upfront_cost;
			}
			else if($scope.min_upfront_cost != $scope.min_upfront_cost_initial) 
			{
				$scope.min_upfront_cost = $scope.min_upfront_cost - $scope.state_upfront_cost;
			}
			
			
			
			
			//console.log($scope.min_upfront_cost);
			
			
			
		}
		
		
		
		
		$scope.select_all = function(zone,state){
			
			
			if($rootScope.zone_cb[zone][state] == true) {
				$scope.zone_type[zone] = true;
				angular.forEach($scope.zoneData[zone][state],function(val,key){
					
					$rootScope.zone_cb[val.city] = true; 
					var idxCity	=	$scope.selected_zone.indexOf(val.city);
					if(idxCity == -1) {
						$scope.selected_zone.push(val.city);
						
					}
				});
				
				$scope.zone_type[state] = true;
					var idxstate	=	$scope.selected_state.indexOf(state);
				
					if(idxstate == -1){
					$scope.selected_state.push(state);
					
					$scope.zone_type[state] = true;
					if(($scope.min_upfront_cost < $scope.max_upfront_cost) && $scope.selected_state.length>1)
					{
						$scope.min_upfront_cost = $scope.min_upfront_cost + $scope.state_upfront_cost;
						if($scope.min_upfront_cost > $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost;
						}
					}
				 }
					
				
			}else if($rootScope.zone_cb[zone][state] == false){
				
				$scope.zone_type[state] = false;
				angular.forEach($scope.zoneData[zone][state],function(val,key){
					$rootScope.zone_cb[val.city] = false; 
					var idxCity	=	$scope.selected_zone.indexOf(val.city);
					////console.log(val.city+'=='+idxCity);
					if(idxCity > -1) {
						$scope.selected_zone.splice(idxCity, 1);
						
					}
				});
					
					
					var keepGoing = 1;
					angular.forEach($scope.check_selected,function(val,key){
					 if(keepGoing) {
						var idxstate = $scope.selected_zone.indexOf(val.city);
						
						if(idxstate > -1){
							keepGoing = 0;
							var checkdata = $scope.selected_state.indexOf(state);
								
						} 
					 }
				 });
				 
				 if(keepGoing == 1)
				 {
					 $scope.zone_type[state] = false;
					 
					 var checkdata = $scope.selected_state.indexOf(state);
					 
					 $scope.selected_state.splice(checkdata,1);
					 
					 if(($scope.min_upfront_cost > $scope.min_upfront_cost_initial) && ($scope.min_upfront_cost <= $scope.max_upfront_cost) && $scope.selected_state.length <7)
					 {
						 //console.log($scope.min_upfront_cost);
						if($scope.min_upfront_cost == $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost - 4000;
						}
						else
						{
							$scope.min_upfront_cost=$scope.min_upfront_cost - $scope.state_upfront_cost;
						}
					 }
					 
				 }
					
					
			
			}
			
		}
		
		$scope.selected_zone_cities = function (city,zone,state){
			
			if($rootScope.zone_cb[city] == true){
				$scope.selected_zone.push(city);
				 var idxstate	=	$scope.selected_state.indexOf(state);
				
				 if(idxstate == -1){
					$scope.selected_state.push(state);
					
					$scope.zone_type[state] = true;
					$scope.zone_type[zone] = true;
					
					if(($scope.min_upfront_cost < $scope.max_upfront_cost) && $scope.selected_state.length>1)
					{
						$scope.min_upfront_cost = $scope.min_upfront_cost + $scope.state_upfront_cost;
						if($scope.min_upfront_cost > $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost;
						}
					}
				 }
				
			}else if($rootScope.zone_cb[city] == false){
				
				var idxCity	=	$scope.selected_zone.indexOf(city);
				if(idxCity > -1) {
					$scope.selected_zone.splice(idxCity,1);
				}
				$scope.check_selected = $scope.zoneData[zone][state];
			//	$scope.zone_cb[zone][state] = false;
				var keepGoing = 1;
				angular.forEach($scope.check_selected,function(val,key){
					 if(keepGoing) {
						var idxstate = $scope.selected_zone.indexOf(val.city);
						
						if(idxstate > -1){
							keepGoing = 0;
							var checkdata = $scope.selected_state.indexOf(state);
								
						} 
					 }
				 });
				
				 if(keepGoing == 1)
				 {
					 $scope.zone_type[state] = false;
					 
					 var checkdata = $scope.selected_state.indexOf(state);
					 $scope.selected_state.splice(checkdata,1);
					
					if(($scope.min_upfront_cost > $scope.min_upfront_cost_initial) && ($scope.min_upfront_cost <= $scope.max_upfront_cost) && $scope.selected_state.length <7)
					 {
						 //console.log($scope.min_upfront_cost);
						if($scope.min_upfront_cost == $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost - 4000;
						}
						else
						{
							$scope.min_upfront_cost=$scope.min_upfront_cost - $scope.state_upfront_cost;
						}
					 }
				 }
				
			
			}
		}
		
	
		$scope.save_type = function(ev){
				
			if($scope.national_val['listing_type'] == '' || $scope.national_val['listing_type'] == undefined) {				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = "Please select search type";
				return false;
			}
			
			if($scope.disable == 'local' && $scope.national_val['listing_type'] == 'local'){				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = $scope.disabled_msg;
				return false;
			}
			
			if($scope.disable == 'mul' && $scope.national_val['listing_type'] == 'national'){				
				$rootScope.showCommonPop = 1;
				$rootScope.commonTitle = "Genio";
				$rootScope.commonShowContent = $scope.disabled_msg;
				return false;
			}
			
			if($scope.national_val['listing_type'] == "national") {
				
				if($scope.selected_zone == '' || $scope.selected_zone == undefined) 
				{
					$rootScope.showCommonPop = 1;
					$rootScope.commonTitle = "Genio";
					$rootScope.commonShowContent = "Please select atleast one city";
					return false;
				}
				
			/*	angular.forEach($scope.zoneData,function(state,zone){
					
					if($scope.zone_type[zone] == true)
					{
						angular.forEach($scope.selected_zone,function(val,key){
							
							//console.log(state);
						});
						
					}
				});
				//console.log($scope.zoneData['west zone']);
				return false;
				*/
				APIServices.saveNationallistingData($scope.parentid,$scope.selected_zone,$scope.getShadowInfoData.latitude,$scope.getShadowInfoData.longitude,$scope.national_val['national_type']).success(function(response) {
					if(response.errorCode == 0){
						$state.go('appHome.category',{parid:$scope.parentid});
					}else{						
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.errorMsg;
					}
				});
			}else if($scope.national_val['listing_type'] == "local"){
				APIServices.insertLocalListingval($scope.parentid,$scope.getShadowInfoData.sphinx_id).success(function(response) {
					
					if(response.errorCode == 0) {
						$state.go('appHome.category',{parid:$scope.parentid});
						//~ window.location ="../business/category.php";
					}else{						
						$rootScope.showCommonPop = 1;
						$rootScope.commonTitle = "Genio";
						$rootScope.commonShowContent = response.errorMsg;
					}
				});	
			}
		}
		
		
		
		
		$scope.show_listings = function(type,ev,call){
			if(type == 'national'){
			$scope.show_listing_val  = true;
			$scope.national_val['listing_type'] = 'national';
			
			if($scope.stop_data == 0)
			$scope.show_options('state','ev');
			//alert('vabvb');
			}else if(type == 'city') {
					/*if($scope.disable == 'local' && type == 'city'){
					$mdDialog.show(
					  $mdDialog.alert()
						.parent(angular.element(document.querySelector('#popupContainer')))
						.clickOutsideToClose(true)
						.title('')
						.content($scope.disabled_msg)
						.ariaLabel('Alert Dialog Demo')
						.ok('ok')
						.targetEvent(ev)
					);
					return false;
				}*/
				$scope.stop_data = 1;
				//$scope.state_name = {};
				$scope.show_listing_val  = false;
				$scope.national_val['listing_type'] = 'local';
			}
		
		}
		
		$scope.check_zone = function(type){
			//$scope.isReplyFormOpen[type] = !$scope.isReplyFormOpen[type];
			////console.log($scope.isReplyFormOpen);
		}
		$scope.show_state = function(zone,state){
		
			
		}
		
		$scope.show_function = function(key){
			$scope.isReplyFormOpen[key] = !$scope.isReplyFormOpen[key];
			
		}
		
		
		
		/*$scope.$watch('min_upfront_cost', function(newVal, oldVal){
			//console.log(newVal + " " + oldVal);
			$scope.min_upfront_cost = newVal;
		  },true);
		*/
		
	//console.log($scope.min_upfront_cost);
	
	$scope.show_zone = function(key,ev){
		
		$scope.zone_type[key] = true;
		if($scope.clicked_zone != key)
		{
			$scope.isReplyForm[key] = false;
			$scope.clicked_zone = key;
		}
		if($scope.isReplyForm[key] == undefined)
		{
			$scope.clicked_zone = key;
			$scope.isReplyForm[key] = true;
		}
		//console.log($scope.isReplyForm);
	}
	
	
	$scope.show_states = function(key2,key){
		//alert('22');
		//alert('22'+$scope.isReplyForm[key2]);
		/*if(($scope.isReplyForm[key2] == undefined ||  $scope.isReplyForm[key2] == true) && ($scope.zone_type[key2] == true ||$scope.zone_type[key2] == undefined))
		{
		
			$scope.zone_type[key2] = true;
			//$scope.select_all(key,key2);
		//	alert('543'+$scope.zone_type[key2]);
		}
		*/
		if($scope.zone_type[key2] == false)
		{
			//$scope.state_checked(key,key2);
			//$scope.zone_cb[key][key2] = false;
		}
		
		if($scope.clicked_states != key2)
		{
			
			$scope.isReplyForm[key2] = false;
		//	alert('scs');
			$scope.clicked_states = key2;
		}
		if($scope.isReplyForm[key2] == undefined)
		{
			$scope.clicked_states = key2;
			$scope.isReplyForm[key2] = true;
		}
		
		
	}
	
	
	
	$scope.show_crid = function(zone,type,state)
	{
		if(type == 1)
		{	
			angular.forEach($scope.zoneData,function(val,key){
				//console.log(key);
				if(key == zone)
				$scope.isReplyForm22[key] = !$scope.isReplyForm22[key];
				else
				$scope.isReplyForm22[key] = false;
			});
			//console.log($scope.isReplyForm22[zone]);
		}
		else
		{
			
			angular.forEach(state,function(val,key){
			
				if(key == zone && $scope.isReplyForm[key] == true)
				$scope.isReplyForm22[key] = !$scope.isReplyForm22[key];
				else
				$scope.isReplyForm22[key] = false;
			
			});
		}
	}
	
	$scope.state_checked = function(zone,state){
		
		if($rootScope.zone_cb[zone] = {})
		//alert('11'+$scope.zone_type[state]);
		//alert($scope.isReplyForm[state]);
		
		if(($scope.zone_type[state] == true || $scope.zone_type[state] == false) && $scope.isReplyForm[state] == false)
		{
			$scope.isReplyForm[state] = true;
		}
	
		
		if($scope.zone_type[state] == true) {
				
				
				//if($scope.zone_cb[zone].length = '')
				
				
				$scope.zone_type[zone] = true;
				
				
				$rootScope.zone_cb[zone][state] = true;	
				angular.forEach($scope.zoneData[zone][state],function(val,key){
					
					$rootScope.zone_cb[val.city] = true; 
					var idxCity	=	$scope.selected_zone.indexOf(val.city);
					if(idxCity == -1) {
						$scope.selected_zone.push(val.city);
						
					}
				});
				
				
					var idxstate	=	$scope.selected_state.indexOf(state);
				
					if(idxstate == -1){
					$scope.selected_state.push(state);
					
					$scope.zone_type[state] = true;
					if(($scope.min_upfront_cost < $scope.max_upfront_cost) && $scope.selected_state.length>1)
					{
						$scope.min_upfront_cost = $scope.min_upfront_cost + $scope.state_upfront_cost;
						if($scope.min_upfront_cost > $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost;
						}
					}
				 }
					
				
			}else if($scope.zone_type[state] == false){
				
				//$scope.zone_cb[zone] = {}
				$rootScope.zone_cb[zone][state] = false;	
				console.log($rootScope.zone_cb);
				angular.forEach($scope.zoneData[zone][state],function(val,key){
					$rootScope.zone_cb[val.city] = false; 
					var idxCity	=	$scope.selected_zone.indexOf(val.city);
					//console.log(val.city+'=='+idxCity);
					if(idxCity > -1) {
						$scope.selected_zone.splice(idxCity, 1);
						
					}
				});
					
					
					var keepGoing = 1;
					angular.forEach($scope.check_selected,function(val,key){
					 if(keepGoing) {
						var idxstate = $scope.selected_zone.indexOf(val.city);
						
						if(idxstate > -1){
							keepGoing = 0;
							var checkdata = $scope.selected_state.indexOf(state);
								
						} 
					 }
				 });
				 
				 if(keepGoing == 1)
				 {
				
					 
					 var checkdata = $scope.selected_state.indexOf(state);
					 
					 $scope.selected_state.splice(checkdata,1);
					 
					 if(($scope.min_upfront_cost > $scope.min_upfront_cost_initial) && ($scope.min_upfront_cost <= $scope.max_upfront_cost) && $scope.selected_state.length <7)
					 {
						 //console.log($scope.min_upfront_cost);
						if($scope.min_upfront_cost == $scope.max_upfront_cost)
						{
							$scope.min_upfront_cost = $scope.max_upfront_cost - 4000;
						}
						else
						{
							$scope.min_upfront_cost=$scope.min_upfront_cost - $scope.state_upfront_cost;
						}
					 }
					 
				 }
				
				 var keepgoing2 = 1;
				angular.forEach($scope.zoneData[zone],function(val,key){ 
					if(keepgoing2 == 1)
					{
						var checkdata = $scope.selected_state.indexOf(key);
						//console.log(checkdata);	 
						
						if(checkdata > -1)
						keepgoing2 = 0;
						
					}
				});
				
				if(keepgoing2 != 0)
				$scope.zone_type[zone] = false;
			
			}
			
		
		
		
		
	};
	
});		
});
