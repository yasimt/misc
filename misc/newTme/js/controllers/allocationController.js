define(['./module'], function (tmeModuleApp) {
	tmeModuleApp.controller('allocationController',function($scope,APIServices,returnState,$location) {
		$scope.allocContracts	=	[];
		var loadNumber	=	0;
		$scope.loadAlloc = function(returnState,$page) {
			if(typeof $page===undefined) {
				var pageShow = '';
			} else {
				var pageShow = $page;
			}
			var city_search	='';
			var scopeUrlExp	=	$location.url().split('/');
			// Service identifier for Fetching Allocated Contracts
			APIServices.getAllocContracts(returnState.stateParam,returnState.whichParam,pageShow,city_search,returnState.parid).success(function(response) {
				if(pageShow == '' || pageShow == null) {
					$scope.allocContracts = response;
					$scope.mainPage = scopeUrlExp[1];
				} else {
					response.data.forEach(function(eachConts) {
						$scope.allocContracts.data.push(eachConts);
					});
					$scope.allocContracts.count = response.count;
				}
				// Calculation for getting Likely Expiry Status
				if($scope.allocContracts.errorCode	==	0) {
					$scope.allocContracts.data.forEach(function(contract) {
						contract.expondo	=	function() {
							var exp_on	=	'';
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
				}
			});
		};
		
		
		$scope.loadAlloc(returnState);
			
		$scope.clickLoad	=	function() {
			loadNumber	=	loadNumber+1;
			$scope.loadAlloc(returnState,loadNumber);
		};
	});
});

