define(['./module'], function (tmeModuleApp) {	
	//This controller is used for speedLinks Settings
	tmeModuleApp.controller('speedLinksController',function($scope,APIServices,Paths,$rootScope,$location,$anchorScroll) {
		$scope.toggle	=	function(event){ 
			//$('.linkBox').removeClass('active');
			if($(event.target).hasClass("active")) {
				$(event.target).removeClass("active");
			} else {
				if($(event.target).closest('.leftPaneDash').find('.active').length	<= 5) {
					$(event.target).addClass("active");
				} else {
					alert("You cannot set more then six menus as favourite");
					return false;
				}
			}
		}
		
		$scope.setSpeedLink =	function(response) {
			var setLink 		=	"";
			var display 		=	"";
			var display_name	=	"";
			var extraVals 		=	"";
			$('.active').each(function(i,val) {
				setLink			+=	$(this).attr('valset_link')+',';
				display			+=	$(this).attr('valset_display')+',';
				display_name	+=	$(this).attr('valset_link_name')+',';
				extraVals		+=	$(this).attr('valset_extraVals')+',';
			});
			setLink			=	setLink.slice(0,-1);
			display			=	display.slice(0,-1);
			display_name	=	display_name.slice(0,-1);
			extraVals		=	extraVals.slice(0,-1);
			APIServices.setSpeedLinks(setLink,display,extraVals,display_name).success(function (response) {
				$rootScope.currSpeedLink	=	setLink.split(",");
				$rootScope.display_menu		=	display.split(",");
				$scope.display_name			=   display_name.split(",");
				$rootScope.extraValsSpeed	=	extraVals.split(",");
				$('.mainDash').animate({ scrollTop: $('.error_msg_speed_lnk').offset().top},'slow');
				$('.error_msg_speed_lnk').show();
				setTimeout(function() { 
					$('.error_msg_speed_lnk').fadeOut(); 
				}, 2222);
			});
		}
	});
});
