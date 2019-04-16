define(['./app'], function (tmeModuleApp) {
		'use strict';
		return tmeModuleApp.config(function($stateProvider, $urlRouterProvider,$locationProvider,$httpProvider,cfpLoadingBarProvider) {
		cfpLoadingBarProvider.includeSpinner = true;
		$urlRouterProvider.otherwise('/welcome');
		$stateProvider.
		state("welcome",{url:'/welcome',
			views:{
				"initload@": {templateUrl:"partials/welcome.html",controller:"welcomeController"}
			}
		,authenticated:true}).
		/*#################### Realizable Value ######################## */            
        //~ state("realizableValue",{url:'^/realizableValue',
            //~ views:{
                    //~ "initload@": {templateUrl:"partials/realizableValue.html",controller:"realizableValueController"}
            //~ },
            //~ data: {
                    //~ css: ['css/app_new.css']
            //~ },
        //~ authenticated:true}).
        /*#################### END END ######################## */
		
		state("welcome.profile",{url:'',
			views:{
				"profile": {templateUrl:"partials/profile.html"}
			},
		authenticated:true}).
		state("regulation",{url:'/regulation',
			views:{
				"initload@": {templateUrl:"partials/regulation.html"}
			},
		authenticated:true}).
		state("appHome", {
			url:'^/appHome',
			views : {
				"initload@" : {
					templateUrl: "partials/appHome.html", 
					controller:"mktgPageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				},
				"pageload@appHome" : {
					templateUrl: "partials/allocation.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							//$cookieStore.put('currLink','');
							//$cookieStore.remove('city');
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:'allocationController',
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			}, 
		authenticated:true}).
		state("appHome.home",{url:'^/appHome',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/allocation.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','');
							$cookieStore.remove("catSrchVal");
							//~ $cookieStore.remove('city');
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:'allocationController',
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.hotData",{url:'^/hotData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/hotData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.hotData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"hotDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.jdrrPropectData",{url:'^/jdrrPropectData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdrrPropectData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.jdrrPropectData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"jdrrPropectDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.jdrrCourierData",{url:'^/jdrrCourierData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdrrCourierData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.jdrrCourierData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"jdrrCourierDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.package",{url:'^/package',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/package.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.package');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"packageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.iitfData",{url:'^/iitfData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/iitfData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.iitfData');
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"iitfDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.accountDetRest",{url:'^/accountDetRest',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/accountDetRest.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.accountDetRest');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"accountDetRestController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.specialData",{url:'^/specialData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/specialData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.specialData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"specialDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.ecsRequest",{url:'^/ecsRequest',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/ecsRequest.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.ecsRequest');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"ecsRequestController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		
		state("appHome.leadComplaints",{url:'^/leadComplaints',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/leadComplaints.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.leadComplaints');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"leadComplaintsController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		
		state("appHome.unsoldData",{url:'^/unsoldData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/unsoldData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.unsoldData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"unsoldDataDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.nonEcsData",{url:'^/nonEcsData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/nonEcsData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.nonEcsData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"nonEcsDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.inventoryData",{url:'^/inventoryData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/inventoryData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.inventoryData');
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"inventoryDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.inventoryMorethanFifty",{url:'^/inventoryMorethanFifty',
			views:{
				
				"pageload@appHome": {
					templateUrl:"partials/inventoryMorethanFifty.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.inventoryMorethanFifty');
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"inventoryMorethanFiftyController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.tmeAlloc",{url:'^/tmeAlloc',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/tmeAlloc.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.tmeAlloc');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"tmeAllocController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.topCalledExpiredData",{url:'^/topCalledExpiredData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/topCalledExpiredData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.topCalledExpiredData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"topCalledExpiredDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.magazineData",{url:'^/magazineData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/magazine.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','magazineData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"magazineDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.pdgdata",{url:'^/pdgdata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/pdgdata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.pdgdata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"pdgdataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.retention",{url:'^/retention',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/retention.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.retention');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"retentionController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.bouncedData",{url:'^/bouncedData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/bouncedData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.bouncedData');
							$cookieStore.remove("catSrchVal");	
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"bouncedDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.JdrIro",{url:'^/JdrIro',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/JdrIro.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.JdrIro');
							$cookieStore.remove("catSrchVal");	
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"JdrIroController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},	
		authenticated:true}).
		state("appHome.WebIro",{url:'^/WebIro',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/WebIro.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.WebIro');
							$cookieStore.remove("catSrchVal");	
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"WebIroController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},		
		authenticated:true}).
		state("appHome.whatsappcalled",{url:'^/whatsappcalled',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/whatsappcalled.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.whatsappcalled');
							$cookieStore.remove("catSrchVal");	
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"whatsappcalledController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},		
		authenticated:true}).
		state("appHome.bouncedDataECS",{url:'^/bouncedDataECS',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/bouncedDataECS.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.bouncedDataECS');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"bouncedDataECSController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.instantECS",{url:'^/instantECS',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/instantECS.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.instantECS');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"instantECSController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.newBusiness",{url:'^/newBusiness',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/newBusiness.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.newBusiness');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"newBusinessController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.restaurantdealsoffer",{url:'^/restaurantdealsoffer',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/restaurantdealsoffer.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.restaurantdealsoffer');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"restaurantdealsofferController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.superhotdata",{url:'^/superhotdata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/superhotdata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.superhotdata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"superhotdataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.ownershippaiddata",{url:'^/ownershippaiddata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/ownershippaiddata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.ownershippaiddata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"ownershippaiddataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.othertmepaiddata",{url:'^/othertmepaiddata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/othertmepaiddata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.othertmepaiddata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"othertmepaiddataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.ownershipexpireddata",{url:'^/ownershipexpireddata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/ownershipexpireddata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.ownershipexpireddata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"ownershipexpireddataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.othertmeexpireddata",{url:'^/othertmeexpireddata',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/othertmeexpireddata.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.othertmeexpireddata');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"othertmeexpireddataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.prospectData",{url:'^/prospectData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/prospectData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.prospectData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"prospectDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.retentionData",{url:'^/retentionData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/retentionData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.retentionData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"retentionDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.jdRatingData",{url:'^/jdRatingData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdRatingData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore) {
							$cookieStore.put('currLink','.jdRatingData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"jdRatingDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.paidExpVNData",{url:'^/paidExpVNData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/paidExpVNData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.paidExpVNData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"paidExpVNDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.workedECS",{url:'^/workedECSData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/workedECSData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.workedECS');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"workedECSDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.restaurantData",{url:'^/restaurantData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/restaurantData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.restaurantData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"restaurantDataController"
				}
			},
		authenticated:true}).
		state("appHome.deactivatedRest",{url:'^/deactivatedRest',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/deactivatedRest.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.deactivatedRest');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"deactivatedRestController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.expiredData",{url:'^/expiredData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/expiredData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.expiredData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"expiredDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.paidEcsData",{url:'^/paidEcsData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/paidEcsData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.paidEcsData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"paidEcsDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.chainRest",{url:'^/chainRest',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/chainRest.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.chainRest');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"chainRestController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.shopfrontData",{url:'^/shopfrontData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/shopfrontData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.shopfrontData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"shopfrontDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.callcountData",{url:'^/callcountData',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/callcountData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.callcountData');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"callcountDataController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.resellerAudit",{url:'^/resellerAudit',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/resellerAudit.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.resellerAudit');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"resellerAuditController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.report",{url:'^/:currPage/:extraVals',
			views:{
				"pageload@appHome" : {
					templateUrl: function($stateParams){
						return 'partials/appointmentRep.html';
					},
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.report');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich,extraVals:$stateParams.extraVals,currentPage:$stateParams.currPage};
						}
					},
					controllerProvider: function($stateParams) {
						if($stateParams.currPage != 'dealClosedRep') {
							return "appointmentRepController";
						} else {
							return 'dealClosedRepController';
						}
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.filter",{url:'^/filter/:srchparam/:srchWhich/:currPage/:extraVals',
			views:{
				"pageload@appHome" : {
					templateUrl: function($stateParams){
						if($stateParams.extraVals	!= '' || $stateParams.currPage == 'dealClosedRep' || $stateParams.currPage == 'appointmentRep') { 
							return 'partials/appointmentRep.html';
						} else if($stateParams.extraVals	== ''){	
							return 'partials/'+$stateParams.currPage+'.html';
						}
					},
					resolve:{
						returnState:function($stateParams){
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich,extraVals:$stateParams.extraVals,currentPage:$stateParams.currPage};
						}
					},
					controllerProvider: function($stateParams,$cookieStore) {
						if($stateParams.extraVals	==	'') {
							$cookieStore.put('currLink','.filter');
							$cookieStore.put('currPageFilter',$stateParams.currPage);
							$cookieStore.put('cookExtraVal',$stateParams.srchWhich);
							$cookieStore.remove("catSrchVal");
							if($cookieStore.get('filterVal')	==	$stateParams.srchparam) {
								if($cookieStore.get("thisPage") != 'filter') {
									$cookieStore.put("pageNo",0);
									$cookieStore.put("thisPage",'filter');
								} else {
									//$cookieStore.put("pageNo",0);
								}
							} else {
								$cookieStore.put("pageNo",0);
								$cookieStore.put("thisPage",'filter');
							}
							$cookieStore.put('filterVal',$stateParams.srchparam);
							return $stateParams.currPage+"Controller";
						} else if($stateParams.extraVals	!=	null && $stateParams.currPage != 'dealClosedRep') {
							return "appointmentRepController";
						} else if($stateParams.extraVals	!=	null && $stateParams.currPage != 'appointmentRep') {
							return "dealClosedRepController";
						}
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
			authenticated:true
		}).
		state("appHome.search",{url:'^/search/:parid/:currPage',
			views:{
				"pageload@appHome" : {
					templateUrl: function($stateParams){
						if($stateParams.currPage == 'dealClosedRep') {
							return 'partials/appointmentRep.html';
						} else {
							return 'partials/'+$stateParams.currPage+".html";
						}
					},
					resolve:{
						returnState:function($stateParams){
							return {parid:$stateParams.parid};
						}
					},
					controllerProvider: function($stateParams) {
						return $stateParams.currPage+"Controller";
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
			authenticated:true
		}).
		state("redirectDispose",{url:'/redirectDispose?parentIdSt&stVal',
			views:{
				"initload@" : {
					templateUrl:"partials/redirectDispose.html",
					resolve:{
						
						returnState:function($stateParams){
							return {parid:$stateParams.parentIdSt,stVal:$stateParams.stVal};
						}
					},
					controllerProvider: function($stateParams) {
						return "redirectDisposeController";
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
			authenticated:true
		}).
		state("appHome.category",{url:'^/catSearch/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/category.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"categoryController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.catpreview",{url:'^/catpreview/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/catpreview.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"catPreviewController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.attributes",{url:'^/attributes/:parid/:page',
            views:{
                "pageload@appHome": {
                    templateUrl:"partials/attributes.html",
                    resolve:{
                        returnState:function($stateParams,$cookieStore){
                            return {paridInfo:$stateParams.parid};
                        }
                    },
                    controller:"attributesController",
                    onEnter: function(){
                        $scope.$new;
                    },
                    onExit: function(){
                        $scope.$destroy;
                    }
                }
            },
        authenticated:true}).
		state("appHome.areaSel",{url:'^/areaSel/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/areaSelHome.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"areaSelectionController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.campaignSel",{url:'^/campaignSel/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/campaignSelHome.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"campaignSelController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.customPackage",{url:'^/customPackage/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/customPackage.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.customPackageNational",{url:'^/customPackageNational/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/customPackageNational.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.showBudgetPageSub",{url:'^/showBudgetDet/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/showBudgetData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"campaignSelController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.showBudgetDataNational",{url:'^/showBudgetDataNational/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/showBudgetDataNational.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"campaignSelController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.showExistInventory",{url:'^/showExistInventory/:parid/:flow/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/showExistInventoryData.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"existInvSelController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.categoryPaidData",{url:'^/categoryPaidData',
            views:{
                "pageload@appHome": {
                    templateUrl:"partials/categoryPaidData.html",
                    resolve:{
                        returnState:function($stateParams,$cookieStore){
                            $cookieStore.put('currLink','.categoryPaidData');
                            return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
                        }
                    },
                    controller:"categoryPaidDataController",
                    onEnter: function(){
                        $scope.$new;
                    },
                    onExit: function(){
                        $scope.$destroy;
                    }
                }
            },
        authenticated:true}).
        state("appHome.dialogjdrrpopup",{url:'^/dialogjdrrpopup/:parid/:pageshow/:image/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/dialogjdrrpopup.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.pageshow,image:$stateParams.image,ver:$stateParams.ver};
						}
					},
					controller:"jdrrpopupController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		 state("appHome.dialogbanner",{url:'^/dialogbanner/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/dialogbanner.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"jdrrpopupController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.selljdomni",{url:'^/selljdomni/:paridInfo/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/selljdomni.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.paridInfo,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"selljdomniController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.selbudgettype",{url:'^/selbudgettype/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/selbudgettype.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"selbudgettypeController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.additionalcampaigns",{url:'^/additionalcampaigns/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/additionalcampaigns.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"additionalcampaignsController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.budgetsummary",{url:'^/budgetsummary/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/budgetsummary.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"budgetsummaryController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.paymentsummary",{url:'^/paymentsummary/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/paymentsummary.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"paymentsummaryController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.ecsform",{url:'^/ecsform/:parid/:ver/:ecsflg/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/ecsform.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,ecsflg:$stateParams.ecsflg};
						}
					},
					controller:"ecsformController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.appointmentAlloc",{url:'^/appointmentAlloc/:parid/:dispVal',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/appointmentAlloc.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,disposeVal:$stateParams.dispVal};
						}
					},
					controller:"appointmentAllocController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.omnidomainreg",{url:'^/omnidomainreg/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/omnidomainreg.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},     
					controller:"omnidomainregController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.omnidomainopt",{url:'^/omnidomainopt/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/omnidomainopt.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"customPackageController",  
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.searchdemo",{url:'^/searchdemo/:paridInfo/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/searchdemo.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.paridInfo,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"searchdemoController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.addomni",{url:'^/addomni/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/addomni.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"addomniController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.addfixedcampaign",{url:'^/addfixedcampaign/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/addfixedcampaign.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"addfixedcampaignController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.selectomnicombo",{url:'^/selectomnicombo/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/selectomnicombo.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"selectomnicomboController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.omniappdemo",{url:'^/omniappdemo/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/omniappdemo.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"customPackageController",  
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.bannerspecification",{url:'^/bannerspecification/:parid/:type/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/bannerspecification.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,type:$stateParams.type};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.jdrating",{url:'^/jdrating/:paridInfo/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdrating.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.paridInfo,page:$stateParams.page,ver:$stateParams.ver,payment_type:$stateParams.payment_type};
						}
					},
					controller:"jdratingController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		
		state("appHome.expiredDataEcs",{url:'^/expiredDataEcs',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/expiredDataEcs.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.expiredDataEcs');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"expiredDataEcsController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.expiredDataNonEcs",{url:'^/expiredDataNonEcs',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/expiredDataNonEcs.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.expiredDataNonEcs');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"expiredDataNonEcsController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		
			state("appHome.deliverySystem",{url:'^/deliverySystem',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/deliverySystem.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.deliverySystem');
							$cookieStore.remove("catSrchVal");
							return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
						}
					},
					controller:"deliverySystemController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.pricechart",{url:'^/pricechart/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/pricechart.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"pricechartController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.emailselection",{url:'^/emailselection/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/emailSelection.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"emailselectionController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.emailoption",{url:'^/emailoption/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/emailoption.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"emailoptionController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.emailnumber",{url:'^/emailnumber/:parid/:ver/:package_type/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/emailnumber.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver,package_type:$stateParams.package_type};
						}
					},
					controller:"emailnumberController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.smsselection",{url:'^/smsselection/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/smsSelection.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.smsnumber",{url:'^/smsnumber/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/smsnumber.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.demopage",{url:'^/demopage/:parid/:ver/:demo_flg/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/demopage.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"demopageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).  
		state("appHome.jdpay",{url:'^/jdpay/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdpay.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"jdpayController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.jdrrdemo",{url:'^/jdrrdemo/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/jdrrdemo.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"jdrrdemoController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.bannerdemo",{url:'^/bannerdemo/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/bannerdemo.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"bannerdemoController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.nationallisting",{url:'^/nationallisting/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/nationallisting_new.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"nationallistingcontroller",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.bformMultiCity",{url:'^/bformMultiCity/:parid/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/national_listing.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid};
						}
					},
					controller:"bformMulticityController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.pricechartnew",{url:'^/pricechartnew/:parid/:ver/:page',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/pricechart_new.html",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					controller:"customPackageController",
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.learningcenter",{url:'^/learningcenter',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/learningcenter.html",
					controller:"learningcenterController",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.learningcenter');
							$cookieStore.remove("catSrchVal");
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.tmecallrecords",{url:'^/tmecallrecords',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/tmecallrecords.html",
					controller:"tmecallrecordsController",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.tmecallrecords');
							$cookieStore.remove("catSrchVal");
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
//		state("appHome.realizableValue",{url:'^/realizableValue',
//			views:{
//				"pageload@appHome": {
//					templateUrl:"partials/realizableValue.html",
//					controller:"realizableValueController",
//					resolve:{
//						returnState:function($stateParams,$cookieStore){
//							$cookieStore.put('currLink','.realizableValue');
//							$cookieStore.remove("catSrchVal");
//							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
//						}
//					},
//					onEnter: function(){
//						$scope.$new;
//					},
//					onExit: function(){
//						$scope.$destroy;
//					}
//				}
//			},
//		authenticated:true}).
                state("appHome.discountReport",{url:'^/discountReport',
            views:{
                "pageload@appHome": {
                    templateUrl:"partials/discountReport.html",
                    resolve:{
                        returnState:function($stateParams,$cookieStore){
                            $cookieStore.put('currLink','.discountReport');
                            $cookieStore.remove("catSrchVal");
                            return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
                        }
                    },
                    controller:"discountReportController"
                }
            },
        authenticated:true}).
         state("appHome.penalty",{url:'^/penalty',
            views:{
                "pageload@appHome": {
                    templateUrl:"partials/penaltyReport.html",
                    resolve:{
                        returnState:function($stateParams,$cookieStore){
                            $cookieStore.put('currLink','.penalty');
                           
                            $cookieStore.remove("catSrchVal");
                            return {stateParam:$stateParams.srchparam,whichParam:$stateParams.srchWhich};
                        }
                    },
                    controller:"penaltyReportController"
                }
            },
        authenticated:true}).
               state("appHome.kpi",{url:'^/kpi',
			views:{
				"pageload@appHome": {
					templateUrl:"partials/kpi.html",
					controller:"kpiController",
					resolve:{
						returnState:function($stateParams,$cookieStore){
							$cookieStore.put('currLink','.kpi');
							$cookieStore.remove("catSrchVal");
							return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
						}
					},
					onEnter: function(){
						$scope.$new;
					},
					onExit: function(){
						$scope.$destroy;
					}
				}
			},
		authenticated:true}).
		state("appHome.sslcertificate",{url:'^/sslcertificate/:parid/:ver/:page',
            views:{
                "pageload@appHome": {
                    templateUrl:"partials/sslcertificate.html",
                    resolve:{
                        returnState:function($stateParams,$cookieStore){
                            return {paridInfo:$stateParams.parid,page:$stateParams.page,ver:$stateParams.ver};
                        }
                    },
                    controller:"sslcertificateController",
                    onEnter: function(){
                        $scope.$new;
                    },
                    onExit: function(){
                        $scope.$destroy;
                    }
                }
            },
        authenticated:true}).
		state("notAuthorised", {url:'/notAuthorised',
			views:{
				"initload": {templateUrl:"partials/notAuthorised.html"}
			}
		,authenticated:false});
		$locationProvider.html5Mode(true);
	}).run(function($location,$rootScope,$stateParams,$state) {
		$rootScope.$state = $state;
		$rootScope.$stateParams = $stateParams;
		$rootScope.$on('$locationChangeStart', function (event,newUrl,oldUrl) {
			var locUrl	=	$location.url();
			var locUrlSpl	=	locUrl.split('/');
			$rootScope.activeMenu	=	locUrlSpl[1];
			$rootScope.activeFilter	=	locUrlSpl[4];
			$('.reportDiv').addClass('ng-hide');
			if(USERID	==	'') {
				$location.url('/notAuthorised');
			} else {
				$rootScope.userid	=	USERID;
				$rootScope.oldPath	=	$location.url();
			}
		});
	});
});
