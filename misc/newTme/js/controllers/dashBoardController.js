define(['./module'], function (tmeModuleApp) {	
	//This controller is used for dashBoard Settings		
	tmeModuleApp.controller('dashBoardController',function($scope,APIServices,Paths,$rootScope,$location) {
		$scope.dashLinks =	function(linkFlag) {
			$scope.showVal	=	linkFlag;
			if(linkFlag=='4' || linkFlag=='5'){	
				$scope.links	=	[];
				$scope.linksReport	=	[];
				APIServices.getMenuLinks($scope.id).success(function (response) {
					if(linkFlag=='4') {
						angular.forEach(response.data,function(value,key) {
							if(value != null) {
								if(value.display_menu == 1 || value.display_menu == 4) {
									$scope.links.push(value);
								}
							}
						});
					} else if(linkFlag=='5'){
						angular.forEach(response.data,function(value,key) {
							if(value != null) {
								if(value.display_menu == 2 || value.display_menu == 3 || value.display_menu == 5) {
									$scope.linksReport.push(value);
								}
							}
						});
					}					
				});
				
				$scope.setSortMenu =function() { 
					var sortArrMain		=	new Array;
					var sortArrReport	=	new Array;
					var $i	=	0;
					var sortOrder	= '';
					var sortOrderReport	= '';
					var scopeValOrder	=	'';
					if(linkFlag=='4') {
						scopeValOrder	=	$scope.links;
					} else if(linkFlag=='5') {
						scopeValOrder	=	$scope.linksReport;
					}
					angular.forEach(scopeValOrder,function(value,key) {
						if(linkFlag == '4') {
							sortArrMain[$i] = value;
							if(value.display_menu	==	4 || value.display_menu	==	1) {
								sortOrder	+=	value.menu_id +',';
							}
						} else if(linkFlag == '5') {
							sortArrReport[$i] = value;
							if(value.display_menu	==	2 || value.display_menu	==	3 || value.display_menu	==	5) {
								sortOrderReport	+=	value.menu_id +',';
							}
						}
						$i++;
					});
					var sendOrder	=	'';
					if(linkFlag == '4') {
						$rootScope.linksData	=	sortArrMain;
						sendOrder	=	sortOrder.substring(0, sortOrder.length-1); 
					} else if(linkFlag == '5') {
						$rootScope.linksReportIn	=	sortArrReport;
						sendOrder	=	sortOrderReport.substring(0, sortOrderReport.length-1); 
					}
					APIServices.setSortOrder(sendOrder,linkFlag).success(function (response) {  
						$('.mainDash').animate({
							scrollTop: $('.error_msg_sort_lnk').offset().top
						},'slow');
						$('.error_msg_sort_lnk').show();
						setTimeout(function() { 
							$('.error_msg_sort_lnk').fadeOut(); 
						}, 2222);
					});
				}
			} else if(linkFlag=='6') {
				$scope.showChildren	=	{};
				APIServices.getChildInfo($scope.id).success(function (response) {
					$scope.showChildren	=	response;
				});
			}
		}
		
		// Method to show data popup
		$scope.showData	=	function(event,empcode,index) {
			$('.linImg img').removeClass('activeImg');
			$('.linImg').css('opacity','0.3');
			$(event.target).addClass('activeImg');
			$(event.target).closest('.linImg').css('opacity','1');
			
			var divPos	=	$(event.target).closest('.linImg').position().top;
			var divPosHeight	=	$(event.target).closest('.linImg').outerHeight();
			var lengthAllDiv	=	$('.linImg').length;
			$('.setPopupBotDash').hide();
			var finDivPos	=	$(event.target).closest('.linImg').position().top;
			if($(event.target).closest('.linImg').css('top') != '0px'	) {
				$('.setPopupBotDash').css('top',((divPos-$('.setPopupBotDash').outerHeight())+$('.linImg').outerHeight()+7)+'px');
			} else {
				$('.setPopupBotDash').css('top',(divPos+$('.linImg').outerHeight()+7)+'px');
			}
			$('.setPopupBotDash').show();
			$('.linImg').each(function(i,val) {
				if($(this).position().top > divPos) {
					$(this).animate({top:$('.setPopupBotDash').outerHeight()+'px'},300);
				} else {
					$(this).animate({top:'0px'},300);
				}
			});
			$('.mainDash').animate({scrollTop:($('.setPopupBotDash').position().top-110)},300);
			$scope.showDataTagTme(empcode,index);
			setTimeout(function() {
				$(event.target).closest('.linImg').css('top','-25px');
			},500);
		};
		
		$scope.closePopMid	=	function() {
			var i = 1;			
			var lengthAllDiv	=	$('.linImg').length;
			$('.linImg').each(function(i,val) {
				$(this).animate({top:'0px'},300);
				i++;
				if(i	==	lengthAllDiv) {
					$('.setPopupBotDash').slideUp(300);
				}
			});
			$('.linImg img').removeClass('activeImg');
			$('.linImg').css('opacity','1');
		};
		
		$scope.showWhichDecision	=	function(empcode,index,event,decisionParam,topBotDec) {
			$scope.selIndex		=	$scope.showChildren.data.indexOf(index);
			$scope.showLinParam	=	1;
			APIServices.fetchDisposeCount(empcode,decisionParam).success(function (response) {
				$scope.selCountContracts	=	response;
				if(response.errorCode	==	0) {
					if(topBotDec	==	1) {
						$('.linImg img').removeClass('activeImg');
						$('.linImg').css('opacity','0.3');
						$(event.target).addClass('activeImg');
						$(event.target).closest('.linImg').css('opacity','1');
						
						var divPos	=	$(event.target).closest('.linImg').position().top;
						var divPosHeight	=	$(event.target).closest('.linImg').outerHeight();
						var lengthAllDiv	=	$('.linImg').length;
						$('.setPopupBotDash').hide();
						var finDivPos	=	$(event.target).closest('.linImg').position().top;
						if($(event.target).closest('.linImg').css('top') != '0px'	) {
							$('.setPopupBotDash').css('top',((divPos-$('.setPopupBotDash').outerHeight())+$('.linImg').outerHeight()+7)+'px');
						} else {
							$('.setPopupBotDash').css('top',(divPos+$('.linImg').outerHeight()+7)+'px');
						}
						$('.setPopupBotDash').show();
						$('.linImg').each(function(i,val) {
							if($(this).position().top > divPos) {
								$(this).animate({top:$('.setPopupBotDash').outerHeight()+'px'},300);
							} else {
								$(this).animate({top:'0px'},300);
							}
						});
						$('.mainDash').animate({scrollTop:($('.setPopupBotDash').position().top-110)},300);
						$scope.chartsConfiguration(response,decisionParam);
						setTimeout(function() {
							$(event.target).closest('.linImg').css('top','-25px');
						},500);
					} else {
						$scope.chartsConfiguration(response,decisionParam);
					}
				} else {
					$scope.showData(event,empcode,index);
				}
			});
		};
		
		$scope.chartsConfiguration	=	function(response,decisionParam) {
			$scope.chartConfig = {
				"options": {
					"chart": {
						"type": "pie",
						"backgroundColor": "#FFFFFF",
						"polar": true
					},
					plotOptions: {
						pie: {
							allowPointSelect: true,
							cursor: 'pointer',
							dataLabels: {
								enabled: false
							},
							showInLegend: true
						},
						series: {
							dataLabels: {
								enabled: true,
								format: '{point.name}: {point.y:f}'
							}
						}
					},
				},
				"series": [
				{
					"name": "Disposition Count",
					"data": [],
					"id": "series-0",
					"type": "pie",
					"color": "",
					"dashStyle": "Solid",
					"connectNulls": false
				}],
				"plotOptions": {
					"pie": {
						"shadow": true,
						"center": "['50%', '50%']"
					}
				},
				"title": {
					"text": "Hello"
				},
				"credits": {
					"enabled": true
				},
				"loading": false,
				"size": {},
				"xAxis": {
					"currentMin": 0
				}
			}
			
			$scope.whichOptClick	=	0;
			$scope.chartConfig.loading	=	false;
			if(decisionParam	==	1) {
				$scope.chartConfig.title.text	=	'Showing work done on Allocated Contracts';
				$scope.whichOptClick	=	0;
			} else {
				$scope.chartConfig.title.text	=	'Showing work done on all contracts till now';
				$scope.whichOptClick	=	1;
			}
			
			angular.forEach(response.data,function(value,key) {
				var parseIntVar	=	parseInt(value['countVal']);
				switch(value['allocationType']) {
					case '22':
						$scope.chartConfig.series[0].data.push({
							"name":"Callback","y":parseIntVar,"drilldown": "Callback"
						});
					break;
					case '6':
						$scope.chartConfig.series[0].data.push({
							"name":"Not Contactable","y":parseIntVar,"drilldown": "Not Contactable"
						});
					break;
					case '21': 
						$scope.chartConfig.series[0].data.push({
							"name":"Not Intrested","y":parseIntVar,"drilldown": "Not Intrested"
						});
					break;
					case '25':
						$scope.chartConfig.series[0].data.push({
							"name":"Appointment Fixed","y":parseIntVar,"drilldown": "Appointment Fixed"
						});
					break;
					case '24':
						$scope.chartConfig.series[0].data.push({
							"name":"Follow Up","y":parseIntVar,"drilldown": "Follow Up"
						});
					break;
					case '207':
						$scope.chartConfig.series[0].data.push({
							"name":"Paid Client","y":parseIntVar,"drilldown": "Paid Client"
						});
					break;
					case '9':
						$scope.chartConfig.series[0].data.push({
							"name":"DND","y":parseIntVar,"drilldown": "DND"
						});
					break;
				}
			});
		};
		
		// Method to get allocated contracts of a particular TME
		$scope.showDataTagTme	=	function(empcode,index) {
			$scope.showLinParam	=	1;
			$scope.selIndex	=	$scope.showChildren.data.indexOf(index);
			APIServices.getAllocContractsEmp(empcode).success(function(response) {
				$rootScope.allocContracts	=	[];
				$rootScope.allocContracts 	= 	response;
			});
		};
		
		//Method to get timeline information of the contract
		$scope.showContTimeline	=	function(index,empcode,parentid) {
			$scope.showLinParam	=	2;
			$scope.timeIndex	=	$rootScope.allocContracts.data.indexOf(index);
			APIServices.showTimelineData(empcode,parentid).success(function(response) {
				$scope.timeline	=	[];
				$scope.timeline 	= 	response;
			});
		};
		
		$scope.shiftLeftTimeLine	=	function() {
			$('.timeLineBay ul').animate({left:parseInt($('.timeLineBay ul').css('left'))-220});
			$('.detailHolder').animate({left:parseInt($('.detailHolder').css('left'))-785});
		};
		
		$scope.shiftRightTimeLine	=	function() {
			$('.timeLineBay ul').animate({left:parseInt($('.timeLineBay ul').css('left'))+220});
			$('.detailHolder').animate({left:parseInt($('.detailHolder').css('left'))+785});
		};
		
		$scope.backShowLinConts	=	function() {
			$scope.showLinParam	=	1;
		}
	});
});
