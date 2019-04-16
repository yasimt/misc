define(['./module'], function (tmeModuleApp) {
'use strict';
	tmeModuleApp.directive('reportshowbutton',function($rootScope,$location,APIServices) {
		return {
			restrict: 'AEC',
			replace: true,
			transclude: false,
			link: function (scope, element, attrs) {
				element.bind('click',function() {
					$('.overlay').removeClass('hide');
					$('.dashWidget').removeClass('hide');
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive to Show Loading Bar before the partial loading occurs
	tmeModuleApp.directive('butterbar', ['$rootScope',function($rootScope) {
		return {
			link: function(scope, element, attrs) {
				element.addClass('hide');
				$rootScope.$on('$stateChangeStart', function() {
					element.removeClass('hide');
				});
				$rootScope.$on('$stateChangeSuccess', function() {
					element.addClass('hide');
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		};
	}]);

	//Directive can be used to create a button to go back to login Page
	tmeModuleApp.directive('gobacklogin',function() {
		return {
			scope: {},
			restrict: 'AEC',
			replace: false,
			transclude: false,
			link: function(scope,element,attrs) {
				element.bind('click',function() {
					window.location.href='../tmAlloc/indexTm.php';
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive can be used to create an overlay
	tmeModuleApp.directive('createoverlaybig',function(){
		return {
			restrict: 'AEC',
			template:'<div class="width100 overlay hide"></div>'
		}
	});

	tmeModuleApp.directive('searchlist', function($rootScope,$location,APIServices) {
		return {
				link :  function (scope, element, attrs) {
					    function split( val ) {
						  return val.split( /,/ );
						}
						function extractLast( term ) {
						  return split( term ).pop();
						}
						
					$(element).autocomplete({
						minLength:2,
						source:function (request, response) {
							var patt = new RegExp(",");
							var splitedarray=[];
								if(patt.test(request.term)==true){
									splitedarray=split(request.term);
								}else if(patt.test(request.term)==false){
									splitedarray[0]=request.term;
								}
							if(splitedarray[splitedarray.length-1].length>=2){
							APIServices.fetchall_tmegenio_autosuggest(scope.team_type,$rootScope.searchpara,$rootScope.empType,$rootScope.city_selected,splitedarray[splitedarray.length-1]).success(function(data){
									var suggestions = [];
									if(data.errorCode == 0) {
										$.each(data.data,function(i,val) {
											suggestions.push({'label': val.title});
										});
									}else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
									}	 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
								});
							}else{
								$('.ui-autocomplete').css('display','none');
							}
						},
						focus:function (event, ui) {
							//~ if(ui.item.label != 'No Matches') {
								//~ element.val(ui.item.label);
							//~ }
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									$rootScope.selectedterm=1;
									//~ scope.title_search	=	ui.item.label;
									console.log(this.value);
									var terms = split( this.value );
									  terms.pop();
									  terms.push( ui.item.label );
									  terms.push( "" );
									  this.value = terms.join( "," );
									  
									  scope.title_search	=	this.value;
									  console.log(this.value);
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						//~ change:function (event, ui) {
							//~ if (ui.item === null) {
								//~ scope.isDisabled	=	false;
							//~ }
						//~ }
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','50');
						console.log(item.value);
						if(item.value != undefined )
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span style='text-transform: capitalize;'>" + item.value + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	//Directive can be used to create menu
	tmeModuleApp.directive('menulinks',function(){
		return {
			restrict:'AEC',
			templateUrl : 'partials/menu.html',
			link : function(scope,element,attrs) {
				element.addClass('hide');
				element.bind('mouseleave',function(){
					scope.searchMenu	=	"";
					element.addClass('hide');
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive can be used to create dashboard
	tmeModuleApp.directive('dashboard',function(){
		return {
			restrict:'AEC',
			templateUrl : "partials/dashboard.html",
			link : function(scope,element,attrs) {
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive used to set width of the applied div according to window resize
	tmeModuleApp.directive('windowResize',function(){
		return {
			restrict:'AEC',
			link : function(scope,element,attrs) {
				var resizeBox = function() {
					var windowHeight = $(window).height();
					var windowWidth = $(window).width();
					element.css('width',(windowWidth-attrs.subtract)+'px');
					if(attrs.heightset	==	'set') {
						element.css('height',(windowHeight)+'px');
					}
				};
				resizeBox();
				$(window).resize(resizeBox);
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive used to set auto height
	tmeModuleApp.directive('setAutoHeight',function(){
		return {
			restrict:'AEC',
			link : function(scope,element,attrs){
				var $i=0;
				var heightSet	=	0;
				var resizeBox = function() {
					if($i==1) {
						$('.menulinks').removeClass('hide');
						heightSet	=	$(element).height();
						$('.menulinks').addClass('hide');
					}
					var windowHeight = $(window).height();
					if(windowHeight < (heightSet+65)) {
						element.css('height',(windowHeight-65)+'px');
					} else {
						if($i > 1) {
							element.css('height',(heightSet)+'px');
						}
					}
					$i++;
				};
				resizeBox();
				$(window).resize(resizeBox);
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive used to take auto Top Margin for applied element
	tmeModuleApp.directive('automartop',function(){
		return {
			restrict:'AEC',
			link : function(scope,element,attrs) {
				var resizeBox = function() {
					var windowHeight = $(window).height();
					element.css('margin-top',(windowHeight/4)+'px');
				};
				resizeBox();
				$(window).resize(resizeBox);
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});
	
		tmeModuleApp.filter('unique', function() {
		   return function(collection, keyname) {
			  var output = [], 
				  keys = [];

			  angular.forEach(collection, function(item) {
				  var key = item[keyname];
				  if(keys.indexOf(key) === -1) {
					  keys.push(key);
					  output.push(item);
				  }
			  });

			  return output;
		   };
		});
	
	//Directive to Set Path Params
	tmeModuleApp.directive('routeConfig', ['$rootScope', function($rootScope, $location){   
		return {
			scope: {},
			restrict: 'AEC',
			link: function(scope, elm, attrs){
				var update =  function (){
					$scope.config = {
						appname: "Ouch" ,
						appurl: $location.url(),
						apppath: $location.path()
					}
				}
				$scope.$on('$routeChangeStart', update)
				update();
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	}]);

	// Directive to set content width for proper slider
	tmeModuleApp.directive('setTotContWidth',function($rootScope,$timeout){
		return {
			restrict:'AEC',
			link : function(scope,element,attrs) {
				var totWidth	=	'';
				var innerWidth	=	0;
				$timeout(function(){
					$(element).children().each(function(){
						innerWidth	+=	$(this).outerWidth(true);
					});
					element.css('width',(innerWidth)+'px');
					
					// This part is forcefully added in the directive to set arrow positions
					var navWidthOut	=	$('.navSlider').outerWidth();
					var navWidthIn	=	$('.navFilters').outerWidth();
					
					if(navWidthOut < navWidthIn) {
						$rootScope.setArrowPos	=	1;
					} else {
						$rootScope.setArrowPos	=	0;
					}
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});
	
	// Directive to set content width for proper slider
	tmeModuleApp.directive('setTotContWidthPage',function($rootScope,$timeout){
		return {
			restrict:'AEC',
			link : function(scope,element,attrs) {
				var totWidth	=	'';
				var innerWidth	=	0;
				$timeout(function(){
					$(element).children().each(function(){
						innerWidth	+=	$(this).outerWidth(true);
					});
					element.css('width',(innerWidth)+'px');
					var widthPosLi	=	0;
					// This part is forcefully added in the directive to set arrow positions
					$('.pageLi').each(function(){
						widthPosLi	=	parseInt(widthPosLi) + $(this).outerWidth();
					});
					var navWidthOut	=	$('.pageSlider').outerWidth();
					if(navWidthOut < widthPosLi) {
						$rootScope.setArrowPosPage	=	1;
					} else {
						$rootScope.setArrowPosPage	=	0;
					}
				},300);
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive to create on scroll event to make header fixed
	tmeModuleApp.directive("scroll", function ($window) {
		return {
			restrict:'AEC',
			link: function(scope, element, attrs) {
				var allSet	=	0;
				angular.element($window).bind("scroll", function() {
					if (this.pageYOffset >= 100) {
						$('.lowerHead').addClass('headFixed');
						$('.floatSide').css('top','0');
						$('.logoSmall').removeClass('hide');
						$('.reportDiv').css('top','56px');
					} else {
						$('.lowerHead').removeClass('headFixed');
						$('.floatSide').css('top','65px');
						$('.logoSmall').addClass('hide');
						$('.reportDiv').css('top','109px');
					}
					if(this.pageYOffset >= 228) {
						$('.headerTable').addClass('headFixedTab');
						if(allSet == 0) {
							$('.setPopupBot').css('top',$('.setPopupBot').offset().top-40);
						}
						allSet	=	1;
					} else {
						$('.headerTable').removeClass('headFixedTab');
						if(allSet	==	1) {
							$('.setPopupBot').css('top',$('.setPopupBot').offset().top+40);
						}
						allSet	=	0;
					}
					scope.$apply();
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});
	
	//directive for jdrr scroll
	tmeModuleApp.directive("jdrrbutton", function ($window) {
		return {
			restrict:'AEC',
			link: function(scope, element, attrs) {
				angular.element($window).bind("scroll", function() {
					if($('#btnSec').length > 0) {
						var scroll = $(window).scrollTop();
						
						var cartSection = $('#btnSec');
						var scrollerGuid = $('#posi_fx');
						var footer = $('.ftr_wrpper');
						var scroll_bot = scrollerGuid.offset().top + scrollerGuid.height();
						var footer_top = footer.offset().top;
						if (scroll_bot < footer_top) {
							cartSection.addClass('_fixed').removeClass('_rel');
						}
						if (scroll_bot > footer_top) {
							cartSection.addClass('_rel').removeClass('_fixed');
						}
						scope.$apply();
					}
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});
	

	//Directive to create lazyload
	tmeModuleApp.directive("lazyload", function ($window) {
		return {
			restrict:'AEC',
			link: function(scope, element, attrs) {
				var allSet	=	0;
				angular.element($window).bind("scroll", function() {
					if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
						scope.limiter	=	scope.limiter+15;
					}
					scope.$apply();
				});
			}
		}
	});
	
	//Directive to create lazyload
	tmeModuleApp.directive("lazyloadcat", function ($window) {
		return {
			restrict:'AEC',
			link: function(scope, element, attrs) {
				var allSet	=	0;
				angular.element($window).bind("scroll", function() {
					if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
						scope.limiterOuter	=	scope.limiterOuter+5;
					}
					scope.$apply();
				});
			}
		}
	});
	
	//Directive to create lazyload for budget pincodes
	tmeModuleApp.directive("lazyloadpinbudget", function ($window) {
		return {
			restrict:'AEC',
			link: function(scope, elementBid, attrs) {
				var allSet	=	0;
				elementBid.bind('scroll',function() {
					if(($(this).scrollTop() + $(this).innerHeight()) >= $(this)[0].scrollHeight) {
						scope.limiter	=	scope.limiter+10;
					}
					scope.$apply();
				});
			}
		}
	});
	
	
	//Directive used to set selected name of menu
	tmeModuleApp.directive("setmenuname",function() {
		return {
			restrict: 'AEC',
			link : function(scope,element,attrs) {
				element.bind('click',function() {
					var clickedHtml	=	element.attr('namemenu');
					$('.activeLink').html(clickedHtml);
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});


	//Directive used to set lazy load
	tmeModuleApp.directive('scroller', function () {
		return {
			restrict: 'AEC',
			link: function (scope, elem, attrs) {
				$('.loaditmore').bind('click', function () {
					scope.$apply('loadAlloc("",1)');
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		};
	});
	
	//Directive used to create autocomplete
	tmeModuleApp.directive('showautocomplete',function(APIServices){
		return {
			link :  function (scope, element, attrs) {
				$(element).autocomplete({
					minLength:1,
					source:function (request, response) {
						//~ if(DATACITY != ''){						
						APIServices.searchCompanies(request.term,USERID).success(function (data) {
							var suggestions = [];
							if(data.errorCode	==	0) {
								$.each(data.data,function(i,val) {
									var strParAttach	=	"";
									$.each(val.contractid,function(j,val2) {
										strParAttach	+=	val2+',';
									});
									strParAttach	=	strParAttach.slice(0,-1);
									suggestions.push({'label': i,'value':strParAttach,'setSource':data.fromSet,'paidstatus':val.paidstatus,'catKnow':'0'});
								});
							} else if(data.errorCode	==	1) {
								suggestions.push({'label':'No Records Found','catKnow':'0'});
							} else if(data.errorCode	==	2) {
								scope.isDisabled	=	false;
								$('.buttonSearch').attr('parId',request.term);
								$('.buttonSearch').attr('page',data.fromSet);
							}
							response(suggestions);
							var posLeft	=	$('.ui-autocomplete').position().left;
							var posTop	=	$('.ui-autocomplete').position().top;
							$('.ui-autocomplete').css('left','390px');
							$('.ui-autocomplete').css('width','272px');
							$('.ui-autocomplete').css('top','50px');
							//$('.ui-autocomplete').css('left',(posLeft-6));
							//$('.ui-autocomplete').css('top',(posTop+6));
						});
						//~ }
					},
					focus:function (event, ui) {
						if(ui.item.label != 'No Records Found') {
							element.val(ui.item.label);
						}
						return false;
					},
					select : function(event,ui) {
						if(ui.item.label	!=	'No Records Found') {
							if(ui.item.paidstatus	==	'1') {
								alert('You are not allowed to select this contract because it is Paid');
								return false;
							} else {
								scope.$apply(function() {
									scope.isDisabled	=	false;
									if(ui.item.catKnow	==	0) {
										$('.buttonSearch').attr('parId',ui.item.value);
										$('.buttonSearch').attr('page',ui.item.setSource);
									} else {
										$('.buttonSearch').attr('parId',$('.searchText').val());
										$('.buttonSearch').attr('page','categorySearch');
									}
								});
							}
						}
						return false;
					},
					change:function (event, ui) {
						if (ui.item === null) {
							scope.isDisabled	=	false;
						}
					}
				}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
					switch(item.setSource) {
						case 'allocation':
							var setSource	=	'TME Allocation';
						break;
						case 'hotData':
							var setSource	=	'Hot Data';
						break;
						case 'newBusiness':
							var setSource	=	'New Business';
						break;
						case 'retentionData':
							var setSource	=	'Retention Data';
						break;
						case 'prospectData':
							var setSource	=	'Prospect Data';
						break;
						case 'jdRatingData':
							var setSource	=	'Jd Rating Data';
						break;
						case 'bouncedData':
							var setSource	=	'Bounced Data';
						break;
						case 'bouncedDataECS':
							var setSource	=	'Bounced ECS Data';
						break;
						case 'instantECS':
							var setSource	=	'Instant ECS Data';
						break;
						case 'workedECSData':
							var setSource	=	'Worked for ECS Data';
						break;
						case 'specialData':
							var setSource	=	'Special Data';
						break;
						case 'workedECSData':
							var setSource	=	'Worked for ECS Data';
						break;
						case 'restaurantData':
							var setSource	=	'Restaurant Data';
						break;
						case 'deactivatedRest':
							var setSource	=	'Deactivated Rest Data';
						break;
						case 'expiredData':
							var setSource	=	'Expired Data';
						break;
						case 'chainRest':
							var setSource	=	'Chain Restaurant';
						break;
						case 'dealClosedRep':
							var setSource	=	'Deal Closed';
						break;
						default :
							var setSource	=	'';
						break;
					}
					if(item.setSource	==	'phoneSrch') {
						var color	=	'';
						if(item.paidstatus	==	'1') {
							color	=	'red';
						}
						return $( "<li></li>" ).data( "item.autocomplete", item ).append( "<a><span style='color:"+color+"'>" + item.label + "</span><span class='right'>" + item.value + "</span></a>" ).appendTo( ul );
					} else {
						return $( "<li></li>" ).data( "item.autocomplete", item ).append( "<a><span>" + item.label + "</span><span class='right'>" + setSource + "</span></a>" ).appendTo( ul );
					}
				};
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});
	
	//Directive used to create autocomplete
	tmeModuleApp.directive('showcatautosuggest',function(APIServices){
		return {
			link :  function (scope, element, attrs) {
				$(element).autocomplete({
					minLength:1,
					source:function (request, response) {
						scope.showLoader	=	1;
						APIServices.catSearch(request.term,scope.getnationallistingflag,scope.getnationallistingType).success(function (data) {
							scope.showLoader	=	0;
							var suggestions = [];
							scope.firstAutoResult	=	[];
							var i=0;
							$.each(data.results,function(i,val) {
								if(i == 0) {
									scope.firstAutoResult.push({'label': val.v,'value':val.id,'mcn':val.mcn});
									scope.searchWhat	=	val.mcn;
								}
								if(val.synf	==	1) {
									suggestions.push({'label': val.mcn+'['+val.v+']','value':val.id,'mcn':val.mcn});
								} else {
									suggestions.push({'label': val.v,'value':val.id,'mcn':val.mcn});
								}
								i++;
							});
							
							response(suggestions);
							var posLeft	=	$('.ui-autocomplete').position().left;
							var posTop	=	$('.ui-autocomplete').position().top;
							
						});
					},
					focus:function (event, ui) {
						if(ui.item.label != 'No Records Found') {
							//element.val(ui.item.label);
						}
						return false;
					},
					select : function(event,ui) {
						element.val(ui.item.mcn);
						scope.showLoader	=	1;
						//scope.autocatsearchBox.text	=	"";
						scope.searchWhat	=	ui.item.mcn;
						APIServices.catSearchDataMode(ui.item.mcn,ui.item.value,DATACITY,scope.getnationallistingflag,scope.getnationallistingType).success(function (data) {
							scope.showLoader	=	0;
							scope.countVars	=	[];
							scope.searchIndi	=	[];
							scope.retDataCatStr	=	data;
							//~ if(ui.item.value == scope.selected_key){
								scope.selected = scope.mrk_arr;
							//~ }
							//~ else{
								//~ scope.selected = [];
							//~ }
							scope.selected_name =  ui.item.mcn;
							scope.selected_key =  ui.item.value;
							scope.autocatsearchBox.text = ui.item.mcn;
							scope.show_cat = 'mrk';
							scope.category_limit = 12;
							scope.skip_cat_step=0;
							if(data.error.code	==	0) {
								var i=1;
								angular.forEach(data.results,function(key,value) {
									scope.countVars[i]	=	12;
									i++;
								});
							}
						});
						return false;
					},
					change:function (event, ui) {
						if (ui.item === null) {
							scope.isDisabled	=	false;
						}
					}
				}).keyup(function (e) {
					if(e.which === 13) {
						$(".ui-autocomplete").hide();
					}            
				}).data("ui-autocomplete")._renderItem = function (ul, item) {
					 return $("<li></li>")
						 .data("item.autocomplete", item)
						 .append("<a>" + item.label + "</a>")
						 .appendTo(ul);
				};
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		}
	});

	//Directive used to convert value in round
	tmeModuleApp.directive('roundConverter', function() {
	  return {
		restrict: 'A',
		link: function(scope, elem, attrs) {
			function roundNumber(val) {
				var parsed = parseFloat(val, 10);
				if(parsed !== parsed) { return null; } // check for NaN
				var rounded = Math.round(parsed);
				return rounded;
			}
			ngModelCtrl.$parsers.push(roundNumber);
			scope.$on("$destroy",function() {
				element.remove();
			}); 
		}
		};
	});

	tmeModuleApp.directive('setcurrmenuname',function($location) {
		//alert($location.url());
	});

	//Directive to call Blur Event
	tmeModuleApp.directive('blur', function () {
		return function (scope, elem, attrs) {
			elem.bind('blur', function () {
				scope.$apply(attrs.blur);
			});
		};
	});
	
	tmeModuleApp.directive('tabdivclick',function(APIServices,$timeout,$rootScope){
		return  {
			restrict: 'AEC',
			link: function(scope, element, attrs) {
				element.bind('click', function(event) {
					if(attrs.funcval != 6) {
						scope.areaData	=	{};
						scope.areaDataZone	=	{};
						scope.areaDataName	=	{};
						scope.areaDataPin	=	{};
						scope.areaDataDist	=	{};
						scope.areaDataBand	=	{};
					}
					switch(attrs.funcval) {
						case '1':
							scope.topMsg	=	"Please Select Desired Areas";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'ALL';
							sendAttr.rds	=	'';
							sendAttr.pincode	=	'';
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaData	=	response;
									scope.areaDataSelPin	=	response;
									scope.skipData	=	0;
								});
							});
							
						break;
						case '2':
							scope.topMsg	=	"Please Select Desired Zones";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'ZONE';
							sendAttr.rds	=	'';
							sendAttr.pincode	=	'';
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaDataZone	=	response;
									scope.skipData	=	0;
								});
							});
							
						break;
						case '3':
							scope.topMsg	=	"Please Select Desired Area Names";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'NAME';
							sendAttr.rds	=	'';
							sendAttr.pincode	=	'';
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaDataName	=	response;
									scope.skipData	=	0;
								});
							});
							
						break;
						case '4':
							scope.topMsg	=	"Please Select Desired Pincodes";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'PIN';
							sendAttr.rds	=	'';
							sendAttr.pincode	=	'';
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaDataPin	=	response;
									scope.skipData	=	0;
								});
							});
							
						break;
						case '5':
							scope.topMsg	=	"Please Select Desired Radius";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'DIST';
							sendAttr.rds	=	scope.selRadio;
							sendAttr.pincode	=	$rootScope.companyTempInfo.data.pincode;
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaDataDist	=	response;
									scope.skipData	=	0;
								});
							});
						break;
						case '6':
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							scope.selectedVals	=	[];
							scope.merge_pincode();
							angular.forEach(scope.selected,function(value,key) {
								if(value != '' && value != null) {
									if(scope.selectedVals.indexOf(value) == -1) {
										scope.selectedVals.push(value);
									}
								}
							});
						break;
						case '7':
							scope.topMsg	=	"Please Select Areas by Band";
							var sendAttr	=	{};
							sendAttr.sendAttr	=	'BAND';
							sendAttr.rds	=	scope.selRadio;
							sendAttr.pincode	=	$rootScope.companyTempInfo.data.pincode;
							scope.showLoader	=	0;
							//scope.checkAreas	=	[];
							//scope.selected	=	[];
							scope.limiterBand	=	1;
							APIServices.getAreaPincodeInfo(sendAttr,$rootScope.parentid).success(function(response) {
								APIServices.getAllPincodes($rootScope.parentid).success(function(response2) {
									scope.showLoader	=	1;
									scope.areaDataBand	=	response;
									scope.skipData	=	0;
								});
							});
						break;
					}
				});
				scope.$on("$destroy",function() {
					element.remove();
				}); 
			}
		};
	});
	
	tmeModuleApp.directive('tabdivbudget', function(APIServices,$rootScope) {
		return {
			restrict: 'AEC',
			link: function(scope, element, attrs) {
				element.bind('click', function(event) {
					scope.bestPositionShow	=	{};
					scope.bestBudgetShow	=	{};
					scope.bidderValue		=	{};
					scope.cattotalBudget	=	{};
					scope.bidValue			=	{};
					scope.inventory		=	{};
					scope.selected			=	[];
					scope.totalBudgetShowMain	=	"";
					scope.optName	=	"";
					switch(attrs.funcval) {
						case '1':
							scope.showOptionLoader	=	1;
							scope.selRadio	=	1;
							$rootScope.selRadioTenure	=	'12-365';
							setTimeout(function() {
								scope.setOption(1,1,'12-365');
							},500);
							scope.optName	=	"Option";
						break;
						case '2':
							scope.showOptionLoader	=	1;
							scope.selRadio	=	1;
							$rootScope.selRadioTenure	=	'12-365';
							setTimeout(function() {
								scope.setOption(2,1,'12-365');
							},500);
							scope.optName	=	"Positon";
						break;
						case '3':
							scope.showOptionLoader	=	1;
							scope.selRadio	=	1;
							$rootScope.selRadioTenure	=	'12-365';
							setTimeout(function() {
								scope.sendCustomValid(3000,0);
								scope.tabNo    =   3;
								scope.showOptionLoader	=	0;
							},500);
						break;
						case '6':
							scope.showOptionLoader	=	1;
							scope.selRadio	=	1;
							$rootScope.selRadioTenure	=	'12-365';
							setTimeout(function() {
								scope.setOption(6,1,'12-365');
							},500);
							scope.optName	=	"Positon";
						break;
						case '5':
							scope.showOptionLoader	=	1;
							scope.selRadio	=	1;
							$rootScope.selRadioTenure	=	'12-365';
							setTimeout(function() {
								scope.setOption(5,1,'12-365');
							},500);
							scope.optName	=	"Positon";
						break;
					}
				});
			}
		}
	});
	
	tmeModuleApp.directive('numbersonly', function(){
		return {
		require: 'ngModel',
		link: function(scope, element, attrs, modelCtrl) {
			modelCtrl.$parsers.push(function (inputValue) {
				if (inputValue == undefined) return '' 
				var transformedInput = inputValue.replace(/[^0-9]/g, ''); 
				if (transformedInput!=inputValue) {
					modelCtrl.$setViewValue(transformedInput);
					modelCtrl.$render();
				}
					return transformedInput;         
				});
			}
		};
	});
		
	/*
	 * Changes Made to get All me data based on AllMeFlag
	 * AllMeFlag=1 means All me based on data_city
	 * AllMeFlag=0 means All me based on pinocde
	 * new Changes made for pincode change from alternate address 
	 * flag responsible: $rootScope.Grb_Normal_alt_add
	*/
	tmeModuleApp.directive('datepicker', function(APIServices,$rootScope) {
		var AllMeFlag		=	0;
		var display_allocateToME =	'';
		var alloc_to_ME_TME		 =	'';
		var maxDate_con			=	'';
		return {
				link :  function (scope, element, attrs) {
					$('#ui-datepicker-div').css('z-index','6 !important');
					//change done here apoorv agrawal
					if(scope.disposeval == '25' || scope.disposeval == '99'){
						maxDate_con	=	'+13D';
					}else if(scope.disposeval == '22' || scope.disposeval == '24'){
						maxDate_con	=	'+31D';
					}else{
						maxDate_con	=	'';
					}
					$(element).datepicker({
						defaultDate: +1,
						maxDate: ''+maxDate_con+'',//change done here apoorv agrawal
						onSelect: function(dateText, inst) {
							$rootScope.dateData = $(this).val();
							if($('#allMebutton').is(":visible")){
								AllMeFlag	=	0;
							}else{
								AllMeFlag	=	1;
							}
							if(SERVICE_PARAM == '8'){
								if($('#GoBack').is(":visible")){
									AllMeFlag	=	1;
								}else{
									AllMeFlag	=	0;
								}
								alloc_to_ME_TME 	=	0;
								display_allocateToME =	0;	
							}
							if($('#backToGrab').is(":visible")){
								alloc_to_ME_TME	=	1;
								display_allocateToME =	1;
							}else{
								alloc_to_ME_TME	=	0;
								display_allocateToME =	0;
							}
							if($rootScope.WhichFlow == 'g'){
								AllMeFlag	=	0;
							}
							if($rootScope.AllMeClickFlg	==	1){
								AllMeFlag	=	1;
							}else{
								AllMeFlag	=	0;
							}
							//~ if($rootScope.directiveFlag	==	1 && $rootScope.AllMeClickFlg==1){
								//~ if($('#allMebutton').is(":visible")){
									//~ AllMeFlag	=	1;
								//~ }else{
									//~ AllMeFlag	=	0;
								//~ }
							//~ }else if($rootScope.directiveFlag	==	1){
								//~ if($('#allMebutton').is(":visible")){
									//~ AllMeFlag	=	1;
								//~ }else{
									//~ AllMeFlag	=	0;
								//~ }
								//~ if($rootScope.errorCode_me	==	1){
									//~ if($('#allMebutton').is(":visible")){
										//~ AllMeFlag	=	0;
									//~ }else{
										//~ AllMeFlag	=	1;
									//~ }
								//~ }
							//~ }
							scope.getDataTime(1,$rootScope.contractData,$rootScope.dateData,AllMeFlag,DATACITY,$rootScope.alternateAddFlag,$rootScope.display_allocateToME,alloc_to_ME_TME,$rootScope.Grb_Normal_alt_add,$rootScope.bypass_autoAlloc);
							
						}
					});
					$(element).datepicker("option", "dateFormat", 'yy/mm/dd');
					$(element).datepicker( "option", "showAnim", "drop" );
					scope.$apply();
				}
		}
	});
	
	/*
	 * Added by Apoorv Agrawal
	*/
	tmeModuleApp.directive('scrollradio', function($window) {		
		return {
				restrict: 'A',
				link: function(scope, element, attrs) {	
				angular.element($window).bind("scroll", function(){
					if($(window).scrollTop() + $(window).height() > $(document).height()-2900) {
						element.attr('style','overflow-x: hidden; position: fixed; top: 56px; width: 69.3% !important; z-index: 9999;border-top: 1px solid rgb(69, 209, 255); border-bottom: 1px solid #45d1ff;');
					} else {
						element.attr('style','overflow-x:hidden;border-top: 1px solid rgb(69, 209, 255); border-bottom: 1px solid #45d1ff;');
					}
				});
			}
		}
	});
	/*
	 * Directive For AotuSuggest Street For Alternate Address
	 * Function For Street Auto Suggest
	 * Added By Apoorv Agrawal
	*/
	tmeModuleApp.directive('streetsearch', function($rootScope,APIServices) { 
		return {
				link :  function (scope, element, attrs) {
					scope.$watch('data',function() {
						var street_response	=	[];
						$(element).autocomplete({
							minLength:1,
							source:function (request, response) {
								APIServices.srchStreet(request.term,scope.area_selected,scope.data_city,scope.parentid).success(function (data) {
									var suggestions = [];
									if(data.errorCode       ==      0) {
										street_response	=	data.data.area_list;
										$.each(data.data.area_list,function(i,val) {
												suggestions.push({'label': val.area+'-'+val.mainarea,'desc':val.mainarea,'street':val.area,'pin':val.pincode});
												
										});
									} else if(data.errorCode        ==      1) {
											suggestions.push({'label':'No Matches'});
									} 
									response(suggestions);
									$('.ui-autocomplete').css('left','6');
									$('.ui-autocomplete').css('top','6');
								});
							},
							focus:function (event, ui) {
								if(ui.item.label != 'No Matches') {
									element.val(ui.item.label);
								}
								return false;
							},
							select : function(event,ui) {
								if(ui.item.label	!=	'No Matches') {
									$("#altStreet").val(ui.item.label);
									scope.selectedStreet	=	ui.item.street;
									scope.ChngFrmDirective	=	1;
									scope.area_selected		=	ui.item.desc;
									scope.directiveFl		=	1;
									scope.getPincodefn(scope.data_city,scope.area_selected,scope.directiveFl);
									scope.pincodeSelected	=	ui.item.pin;
									scope.changeInStreet	=	1;
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
								}
								return false;
							},
							change:function (event, ui) {
								if (ui.item === null) {
									scope.isDisabled	=	false;
								}
							}
						});
					});
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	
	tmeModuleApp.directive('alphanumericonly', function(){
		return {
			require: 'ngModel',
			link: function(scope, element, attrs, modelCtrl) {
				modelCtrl.$parsers.push(function (inputValue) {
					var transformedInput = inputValue.replace(/[^a-zA-Z0-9]/g, ''); 
					if (transformedInput!=inputValue) {
						modelCtrl.$setViewValue(transformedInput);
						modelCtrl.$render();
					}
					return transformedInput;
				});
				
			}
		};
	});
	
	tmeModuleApp.directive('domainreg', function(){
		return {
			require: 'ngModel',
			link: function(scope, element, attrs, modelCtrl) {
				modelCtrl.$parsers.push(function (inputValue) {
					var transformedInput = inputValue.replace(/[^a-zA-Z0-9.-]/g, ''); 
					if (transformedInput!=inputValue) {
						modelCtrl.$setViewValue(transformedInput);
						modelCtrl.$render();
					}
					return transformedInput;
				});
				
			}
		};
	});
	
	
	tmeModuleApp.directive('alphaonly', function(){
		return {
			require: 'ngModel',
			link: function(scope, element, attrs, modelCtrl) {
				modelCtrl.$parsers.push(function (inputValue) {
					var transformedInput = inputValue.replace(/[^a-zA-Z ]/g, ''); 
					if (transformedInput!=inputValue) {
						modelCtrl.$setViewValue(transformedInput);
						modelCtrl.$render();
					}
					return transformedInput;
				});
				
			}
		};
	});

	tmeModuleApp.directive('sitbutdown', function($window){
		return {
			link: function(scope, element, attrs) {
				$(element).css("position","relative");
				angular.element($window).bind("scroll", function() {
					if($(window).scrollTop() + $(window).height() > $(document).height()-50) {
						$(element).css("position","relative");
					} else {
						$(element).css("position","fixed");
					}
				});
			}
		};
	});
	tmeModuleApp.directive('sitbutdowntwo', function($window){
		return {
			link: function(scope, element, attrs) {
				$(element).css("position","relative");
				angular.element($window).bind("scroll", function() {
					if($(window).scrollTop() + $(window).height() > $(document).height()-250) {
						$(element).css("position","relative");
					} else {
						$(element).css("position","fixed");
					}
				});
			}
		};
	});
	tmeModuleApp.directive('cityauto', function(APIServices) {
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							APIServices.searchCities(request.term).success(function (data) {
								var suggestions = [];
								if(data.error.code       ==      0) {
										$.each(data.data,function(i,val) {
												suggestions.push({'label': i});
										});
								} else if(data.error.code        ==      1) {
										suggestions.push({'label':'No Matches'});
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
							});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label);
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','50');
						if(item.value != undefined)
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span>" + item.value + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	
		tmeModuleApp.directive('domainregisterauto',['APIServices', '$rootScope',
        function(APIServices, $rootScope){
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							$rootScope.check_forget_select    =   0;
							APIServices.domainregisterauto(request.term).success(function (data) {
								var suggestions = [];
								if(data.errorCode       ==      0) {
										$.each(data.data,function(i,val) {
												suggestions.push({'label': val});
										});
								} else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
							});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label.provider);
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									$rootScope.check_forget_select    =   1;
									scope.domain_registerName[0] = ui.item.label.provider;
									APIServices.getforgetLink(ui.item.label.provider).success(function(response) {
						                if(response.errorcode == 0){
						                    scope.forget_link_Output[0]    =   response.data;
						                    $('.forget_user_pass_a').show();
						                }else{
						                    $('.forget_user_pass_a').hide();
						                }
						            });
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}else{
								$rootScope.check_forget_select    =   0;
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						$('.ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all').css('z-index','50');
						$('.ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all').css("left","731px");
						$('.ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all').css("top","422px");
						$('.ui-autocomplete ui-front ui-menu ui-widget ui-widget-content ui-corner-all').css("width","358px !important");
						if(item.value.provider != undefined)
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span>" + item.value.provider + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	}]);
	
	tmeModuleApp.directive('catsearch', function(APIServices) { 
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							APIServices.srchCAt(request.term).success(function (data) {
								var suggestions = [];
								if(data.errorCode       ==      0) {
										$.each(data.data,function(i,val) {
												suggestions.push({'label': val});
										});
								} else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
							});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label.category);
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','50');
						if(item.value.category != undefined)
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span>" + item.value.category + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	tmeModuleApp.directive('managerlistauto', function(APIServices) { 
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({ 
						minLength:3,
						source:function (request, response) {
							APIServices.fetchManagerListSSO(request.term).success(function (data) {
								
								var suggestions = [];
								if(data.errorCode       ==      0) {
									$.each(data.data,function(i,val) { 
											suggestions.push({'label': val,'desc':val.city});
									});
								} else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
										
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
							});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label.empname+'-'+ui.item.label.empcode);
							}
							
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									$('.auto_select').val('1');
									scope.lineage_sel[0]	=	ui.item.label.empname+'-'+ui.item.label.empcode;
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','1000');
						ul.css('overflow','auto');
						ul.css('height','280px');
						if(item.value.empname != undefined){
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span style='color:black;'>" + item.value.empname+'-'+item.value.empcode +"("+item.value.city+")"+"</span></a>" ).appendTo( ul );
						}else{
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append("<a><span>No Matches</span></a>").appendTo( ul );
						}
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	
	
	tmeModuleApp.directive('cityautolist', function(APIServices,$rootScope) {
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							APIServices.getcitylist(request.term).success(function(data){
									var suggestions = [];
									if(data.errorCode == 0) {
										$.each(data.data,function(i,val) {
											suggestions.push({'label': val.cities});
										});
									}else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
									}	 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
								});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label);
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									$('.auto_city').val('1');
									scope.city_sel_lin[0]	=	ui.item.label;
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','50');
						if(item.value != undefined )
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span style='text-transform: capitalize;'>" + item.value + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
	
	tmeModuleApp.directive('otpkeyup',function(APIServices,$rootScope) {
		return {
				link : function(scope, element, attrs, modelCtrl) {
				element.bind("keyup", function(event){
											
										if (this.value.length == this.maxLength) {
										  $('#otp2').focus();
										}
										
									})
								}
							}
	});
	
	tmeModuleApp.directive('meauto', function(APIServices) {
		return {
			    link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							APIServices.fetchmelist(request.term,DATACITY).success(function(data) {
								var suggestions = [];
								if(data.errorCode       ==      0) {
										$.each(data.data,function(i,val) {
												suggestions.push({'label': val});
										});
								} else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
								$('.ui-autocomplete').css('z-index','2000');
								$('.ui-autocomplete').css('height','20%');
								$('.ui-autocomplete').css('overflow-y','scroll');
							});
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label.empname+'('+ui.item.label.mktempcode+')');
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						ul.css('z-index','50');
						if(item.value.empname != undefined)
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span>" + item.value.empname +'('+item.value.mktempcode+ ")</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
        
        tmeModuleApp.directive('onFinishRender', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element, attr) {
                if (scope.$last === true) {
                    $timeout(function () {
                        scope.$emit(attr.onFinishRender);
                    });
                }
            }
        }
    });
    
     tmeModuleApp.directive('areaautosuggest', function(APIServices,$rootScope) {
		return {
				link :  function (scope, element, attrs) {
					$(element).autocomplete({
						minLength:1,
						source:function (request, response) {
							APIServices.getAreaInfo(request.term).success(function (data) {
								console.log(data);
								var suggestions = [];
								if(data.errorCode       ==      0) {
										$.each(data.data,function(i,val) {
											suggestions.push({'label': val.alldata.area});
										});
								} else if(data.errorCode        ==      1) {
										suggestions.push({'label':'No Matches'});
								} 
								response(suggestions);
								$('.ui-autocomplete').css('left','6');
								$('.ui-autocomplete').css('top','6');
							});
							
							
						},
						focus:function (event, ui) {
							if(ui.item.label != 'No Matches') {
								element.val(ui.item.label);
							}
							return false;
						},
						select : function(event,ui) {
							if(ui.item.label	!=	'No Matches') {
									scope.$apply(function() {
										scope.isDisabled	=	false;
									});
										
									APIServices.getPincodeInfo(ui.item.label).success(function (data) {
									var suggestions_pincode = [];
									if(data.error.code       ==      0) {
										scope.pincodelist.length=0;
										$.each(data.data,function(i,val) {
											scope.pincodelist.push(i);
										});
									} else if(data.error.code        ==      1) {
											suggestions.push({'label':'No Matches'});
									} 
									$('.ui-autocomplete').css('left','6');
									$('.ui-autocomplete').css('top','6');
								});	
							}
							return false;
						},
						change:function (event, ui) {
							if (ui.item === null) {
								scope.isDisabled	=	false;
							}
						}
					}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
						console.log(JSON.stringify(item))
						ul.css('z-index','50');
						if(item.value != undefined )
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span style='text-transform: capitalize;'>" + item.value + "</span></a>" ).appendTo( ul );
						else
							return $( "<li style='overflow:visible;'></li>" ).data( "item.autocomplete", item ).append( "<a><span> No Matches Found.</span></a>" ).appendTo( ul );
					};
					scope.$on("$destroy",function() {
						element.remove();
					}); 
				}
		}
	});
    
    

});
