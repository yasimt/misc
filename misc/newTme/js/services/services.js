define(['./module'], function (tmeModuleApp) {
	'use strict';
	tmeModuleApp.constant('CONSTANTS',{
		ServerUrl:window.location.host,
		pathUrl:window.location.pathname,
	});
	
	tmeModuleApp.factory('APIServices', function($http,$rootScope,CONSTANTS,$q) {
		var expPathUrl	=	CONSTANTS.pathUrl.split('/');
		if(expPathUrl[1]	==	'tmegenio' || expPathUrl[1]	==	'TMEGENIO') {
			var APIURL	=	'http://'+CONSTANTS.ServerUrl+'/'+expPathUrl[1];
		} else {
			var APIURL	=	'http://'+CONSTANTS.ServerUrl;
		}
		// alert(APIURL);
		$http.defaults.headers.common['beartoken'] =	'a0a1d340-aec6-44d6-a466-d7c736a56a9b';
		var transform = function (data) {
			return $.param(data);
		}
		
		var APIService = {};
		APIService.getEmployees = function() {
		  return $http({
			method: 'POST', 
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json, text/plain, */*' },
			transformRequest: transform,
			url: APIURL+'/jdboxNode/user/get-user-info',
			data : {
				empcode 	: 	USERID,
				data_city	:	DATACITY,
				module:'demo'
			}
		  });
		}
		
		APIService.getDealClosed = function() { // Pending
			return $http({
				method: 'POST', 
				url: '../timeline/autosuggest_timeline.php?flag=2'
			});
		}
		
		APIService.getAhdLineage	=	function() {
			return $http({
				method:'POST',
				url: APIURL+'/tme_services/tmeInfo/getAhdLineage/',
				data : {
					empcode		: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.callEventPull	=	function() { // Pending
			return $http({
				method:'POST',
				url:'../tmAlloc/eventpull.php?read=true&ucode='+USERID
			});
		}
		
		APIService.deleteProspectData	=	function(parIdStr) {
			return $http({
				method:'POST',
				url: APIURL+'/tme_services/tmeInfo/delProspectData/',
				data : {
					empId : USERID,
					parid : parIdStr,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchFreebeesEmp	=	function() {
			return $http({
				method:'POST',
				url: APIURL+'/tme_services/tmeInfo/fetchFreebeesEmp/',
				data : {
					empcode 	: 	USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchDisposeCount = function(empcode,decisionParam) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/getDataCountTME/',
				data : {
					tmecode : empcode,
					decisionParam:decisionParam,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getMenuLinks = function(allocid,secondary) { // API
		  return $http({
			method: 'POST', 
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json, text/plain, */*' },
			transformRequest: transform,
			url: APIURL+'/jdboxNode/user/get-menudispostion-info',
			data : {
				empcode		: USERID,
				empname		: UNAME,
				allocid 	: allocid,
				secondaryid	: secondary,
				data_city	: DATACITY,
				type		: 'both',
				module		: 'demo'
			}
		  });
		}
		APIService.fetchjdrrPropectData = function($srchParam,$srchWhich,$pageShow) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchjdrrPropectData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					ucode : USERID
				}
			});
		}
		APIService.fetchjdrrCourierData = function($srchParam,$srchWhich,$pageShow) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchjdrrCourierData/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode   : USERID,
					data_city :	DATACITY
				}
			});
		}
		APIService.updateRdFlg = function(parentid,data_city,contractEmpCode) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/updateRdFlg/',
				data : {
					parentid : parentid,
					data_city : data_city,
					contractEmpCode : contractEmpCode,
					ucode : USERID
				}
			});
		}
		APIService.getAllocContracts = function($srchParam,$srchWhich,$pageShow,$data_city,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/getAllocatedContracts/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					city      : $data_city,
					parid	  :	$parentid
				}
				//headers: {'Content-Type': 'application/x-www-form-urlencoded'}
			});
		}
		
		APIService.getAllocContractsEmp = function($userId) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/getAllocatedContracts/',
				data : {
					empcode   : $userId,
					data_city : DATACITY
				}
			});
		}
		
		APIService.campaignFilter = function($srchParam) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/getAllocatedContracts/',
				data : {
					srchparam : $srchParam,
					empcode 	: USERID,
					data_city :	DATACITY,
					srchwhich : 'where',
					campaign_sr  :	1,
				}
			});
		}
		
		APIService.showTimelineData = function($userId,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/showTimelineData/'+$userId,
				data : {
					empcode 	: $userId,
					parid		:	$parentid,
					extraVals	:	'25,99',
					data_city	:	DATACITY,
					empId		:	USERID
				}
			});
		}
		
		APIService.getCategories = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/getContractCatLive/'+$parentid,
				data : {
					empId 		: 	USERID,
					parid		:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.FetchEcsDetailsForm = function($parentid,$empcode) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/FetchEcsDetailsForm/',
				data : {
					parentid	:	$parentid,
					empcode : $empcode,
					data_city	:	DATACITY
				}
			});
		}
		
		
		APIService.getCategoriespaid = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/getContractCatLivepaid/'+$parentid,
				data : {
					empId 		: USERID,
					parid		:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getBalanceData = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/showContractBalance/',
				data : {
					empId : USERID,
					parentid : $parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchcallcountData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/fetchCallCountData/'+USERID,
				data : {
					empcode   : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.checkTrackerRep = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/checkTrackerRep/'+$parentid,
				data : {
					empId 		: 	USERID,
					parid		:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		APIService.fetchSpecialData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchSpecialData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		
		APIService.fetchEcsRequestData = function($srchParam,$srchWhich,$pageShow,$userid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchEcsRequestData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					userid	:	$userid,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow
				}
			});
		}
		
		APIService.SendRemainderEcsLead = function($parentid,$action_flag,$companyname) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/SendRemainderEcsLead/',
				data : {
					parentid	:	$parentid,
					action_flag : 	$action_flag,
					companyname : 	$companyname,
					data_city	:	DATACITY
				}
			});
		}
		
		
		APIService.fetchEditListingData = function($parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchEditListingData/'+$parentid,
				data : {
					parentid	:	$parentid,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		}
		
		APIService.fetchEditListingEntry = function($parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/fetchEditListingEntry/'+$parentid,
				data : {
					parentid	:	$parentid,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		}
		
		
		APIService.categoryInstantLive	=	function(parentid) {
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/categoryInstantLive/',
				data : {
					parentid :parentid,
					ucode	: USERID,
					data_city:DATACITY,
					uname	:	UNAME
				}
			});
		}
		
		APIService.isPhoneSearchCampaign	=	function(parentid) {
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/isPhoneSearchCampaign/',
				data : {
					parentid :parentid,
					data_city	:	DATACITY
				}
			});
		}
		/**************************************Unsold Data*********************************************/

		APIService.fetchunsoldData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchunsoldData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchinventoryMorethanFifty = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchinventoryMorethanFifty/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					empid	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchinventoryData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchinventoryData/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					empid	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchContractinventoryMorethanFifty = function($srchParam,$srchWhich,$pageShow,$parentid,companyname) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchContractinventoryMorethanFifty/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchContractInventory = function($srchParam,$srchWhich,$pageShow,$parentid,companyname) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchContractInventory/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchtmeAllocData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/gettmeAllocData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchtopCalledExpiredData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchtopCalledExpiredData/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		
		APIService.fetchcategoryPaidData = function($srchParam,$srchWhich,$pageShow,$data_city,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchcategoryPaidData/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					cat_search  : $data_city,
					parid	  :	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		
		APIService.fetchpdgdata = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchpdgdata/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	  :	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchReversedRetentionData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchReversedRetentionData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.updatestopflag = function($srchParam,$srchWhich,$pageShow,$parentid,$stop_flag,parentid,compname,tmename) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/updatestopflag/'+USERID,
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parentid	:	parentid,
					stop_flag : $stop_flag,
					companyname : compname,
					userid : USERID,
					tmename :tmename,
					data_city	:	DATACITY
				}
			});
		} 
		
		APIService.getTempStatus = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/tempContract/'+$parentid,
				data : {
					parid		:	$parentid,
					data_city	: 	DATACITY,
					empcode		:	USERID
				}
			});
		}


		APIService.udateusereditdata = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/udateusereditdata/'+$parentid,
				data : {
					parid		:	$parentid,
					data_city	: 	DATACITY,
					empcode		:	USERID
				}
			});
		}


		
		APIService.fetchHotData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/getHotData/',
				data : {
					empcode   : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	: DATACITY
				}
			});
		}
	
		APIService.fetchAccountDetRest = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/accountDetRest/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchNewBusiness = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchNewBusiness/',
				data : {					
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchrestaurantdealsoffer = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchrestaurantdealsoffer/',
				data : {					
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchsuperhotdata = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchsuperhotdata/',
				data : {					
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.updatesuperhotdata = function($parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/updatesuperhotdata/',
				data : {
					parentid		:	$parentid,
					empcode 	: 	USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.ownershipdata = function($srchParam,$srchWhich,$pageShow,$parentid,$val) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/ownershipdata/',
				data : {					
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY,
					data_flag : $val
				}
			});
		}
		
		APIService.fetchleadComplaints = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/fetchleadComplaints/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.actEcsRetention = function($parentid,$flag) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/actEcsRetention/',
				data : {
					parentid 	: 	$parentid,
					flag 		: 	$flag,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		}
		
		APIService.fetchRetentionData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchRetentionData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.reactivaterequest = function($parentid,$tmecode,$tmename) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/reactivaterequest/',
				data : {
						parentid : $parentid,
						tmecode :$tmecode,
						tmename : $tmename,
						data_city	:	DATACITY
					}
			});
		}
		
		
		APIService.getHistory = function(parentid,companyname) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/getHistory',
				data : {
					parentid	:	parentid,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		}
		
		
		APIService.fetchmelist = function(term,data_city) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchmelist',
				data : {
					data_city	:	data_city,
					term:term,
					empcode		:	USERID
				}
			});
		}
		
		APIService.fetchManagerList = function($srchData) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchManagerList',
				data : {
					srchData : $srchData,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchManagerListSSO = function($srchData) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/getSSOEmp',
				data : {
					srchData : $srchData,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchAllocId = function() {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchAllocId',
				data : {
					data_city	:	DATACITY
				}
			});
		}
		
		//~ APIService.insertInformPopUp = function($manager,$allocId,$take_calls) { 
			//~ return $http({			
				//~ method:'POST',
				//~ url:APIURL+'/tme_services/tmeInfo/insertInformPopUp/'+DATACITY,
				//~ data : {
					//~ manager:$manager,
					//~ allocId:$allocId,
					//~ take_calls:$take_calls,
					//~ userid:USERID,
					//~ data_city: DATACITY
				//~ }
			//~ });
		//~ }
		
		APIService.insertInformPopUp = function($manager,$randomNum) { 
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/insertInformPopUp/'+DATACITY,
				data : {
					manager:$manager,
					randomNum:$randomNum,
					userid:USERID,
					data_city: DATACITY
				}
			});
		}
		
		APIService.fetchNewRequest = function($tmecode,$flag) { 
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchNewRequest/'+USERID,
				data : {
					tmecode :$tmecode,
					userid:USERID,
					flag:$flag,
					data_city: DATACITY
				}
			});
		}
		
		APIService.acceptNewreq = function($email_id,$manager,$emp_mob,$manager_code,$empcode,$flag,$index,$empkey,$city,$man_mob,$man_email) { 
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/AcceptNewRequest/'+USERID,
				data : {
					email_id :$email_id,
					manager :$manager,
					emp_mob :$emp_mob,
					index :$index,
					manager_code :$manager_code,
					empcode :$empcode,
					userid:USERID,
					flag:$flag,
					empkey:$empkey,
					man_mob	: $man_mob,
					man_email: $man_email,
					data_city: $city
				}
			});
		}
		
		APIService.getEmptypeInfo = function($empcode) { 
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/getEmptypeInfo/'+USERID,
				data : {
					empcode :$empcode,
					userid:USERID,
					data_city: DATACITY
				}
			});
		}
		
		
		APIService.insertmename = function(empname,parentid,final_mecode) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/insertmename',
				data : {
					empname	: empname,
					parentid : parentid,
					final_mecode : final_mecode,
					tmecode : USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchInformationPopUp = function() {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchInformationPopUp/',
				data : {
					tmecode 	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchreportStatusmanager = function() {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchreportStatusmanager/',
				data : {
					tmecode 	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.updateEmpstatusmanager = function() {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/updateEmpstatusmanager/',
				data : {
					tmecode 	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.setSpeedLinks = function($setLink,$display,$extraVals,$setLinkName) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/setSpeedLinks/',
				data : {
					setLink 	: $setLink,
					setLinkName	: $setLinkName,
					display		: $display,
					empcode		: USERID,
					extraVals	: $extraVals,
					data_city	: DATACITY
				}
			});
		}
		
		APIService.getSpeedLinks = function($setLink) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/getSpeedLinks/',
				data : {
					setLink 	: $setLink,
					empCode		: USERID,
					data_city	: DATACITY

				}
			});
		}
		
		APIService.fetchCallBackPopData	=	function() {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmenewInfo/getCallBackData/',
				data : {
					empcode		: USERID,
					data_city	: DATACITY
				}
			});
		}
		
		
		/*penalty service */
		
		APIService.getPenaltyInfo	=	function() {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/getPenaltyDetails/',
				data : {
					empCode		: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getPenaltyInforeport	=	function(city,date,year,page,empdata) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/getPenaltyDetails/',
				data : {
					empCode		: USERID,
					data_city	:	DATACITY,
					city		: city,
					action		: 'report',
					date		: date,
					year		: year,
					'page'		: page,
					'empdata'	: empdata
				}
			});
		}
		
		APIService.getDoDont	=	function() {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/tmeInfo/getDoDontDetails/',
				data : {
					empCode		: USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.remindLater	=	function($allocId) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/remindLaterCallBack/',
				data : {
					allocId		: 	$allocId,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		};
		
		APIService.removeAll	=	function($allocId) {
			return $http({
				method:'POST',
				url : APIURL+'/tme_services/contractInfo/removeAllCallBack/',
				data : {
					allocId		: 	$allocId,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		};
		
		APIService.fetchTmeComments	=	function($parentid,$flag,tmeComment) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchTmeRetentionComments/'+USERID,
				data : {
					empcode : USERID,
					parid	:	$parentid,
					flag	:	$flag,
					tme_comment	:	tmeComment,
					data_city	:	DATACITY
				}
			});
		};
		
		APIService.fetchProspectData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchProspectData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		
		/*****************************************************/
		
		APIService.fetchNonecsData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchNonecsData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		/*****************************************************/
		
		
		APIService.fetchJdRatingData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchJDRatingData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchWorkedForECS = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchEcsData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.SendVLC = function($parentid,$data_city,$reminder) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/SendVLC/',
				data : {
					parid		:	$parentid,
					tmecode		: 	USERID,
					city		:	$data_city,
					reminder	:	$reminder,
					data_city	:	DATACITY,
					uname		:	UNAME
				}
			});
		}
		
		APIService.setSortOrder = function($sortOrder,$linkFlag) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/setSortOrder/',
				data : {
					tmecode		: USERID,
					sortOrder 	: $sortOrder,
					linkFlag	: $linkFlag,
					data_city	: DATACITY
				}
			});
		}
		
		APIService.StoreComment = function($Comment,$parentid,$empcode) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/StoreCommentECS/',
				data : {
					parid		:$parentid,
					Comment		:$Comment,
					empcode		:$empcode,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.StoreCommentretention = function($Comment,$parentid,$empcode) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/StoreCommentretention/',
				data : {
					parid		:$parentid,
					Comment		:$Comment,
					empcode		:$empcode,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchRestaurantData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchRestaurantData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchExpiredData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchExpiredData/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		/*APIService.fetchMagazineData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchMagazineData/',
				//~ url:APIURL+'/tme_services/tmenewInfo/fetchMagazineData/',
				data : {
					empcode   : USERID,				
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	  :	$parentid
				}
			});
		}*/
		
		APIService.fetchMagazineData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchMagazineData/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchPaidEcsData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchEcsPaidData/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.fetchReportData = function($srchParam,$srchWhich,$pageShow,$extraVals) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchReportData/',
				data : {
					data_city : DATACITY,
					empcode   :  USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					extraVals :	$extraVals
				}
			});
		}
		/*New API Call for cancel Appointment Added Here By Apoorv Agrawal*/
		// ($rootScope.cancel_parid,$rootScope.companyname,$rootScope.parentCode,$rootScope.MECode,$rootScope.actionTime)
		APIService.update_appt	=	function(cancel_parid,companyname,parentCode,MECode,actionTime) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/cancelApptInfo/cancel_appt',
				data 	:	{
					parentid 	: cancel_parid,
					empCode  	: MECode,
					parentCode  : parentCode,
					apptTime 	: actionTime,
					companyname	: companyname,
					logged_user_id	: USERID,
					data_city	:	DATACITY
				}
			});
		}
		/*
		 * Update Contact Person as per the new Rerquirment
		 * Addded By Apoorv Agrawal
		 * 30-03-2017
		 */
		APIService.update_generalinfo_shadow	=	function(contact_person,selectedsalutations,parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/update_generalinfo_shadow/',
				data : {
					parentid 		:	parentid,
					contact_person	:	contact_person,
					salute 			:	selectedsalutations,
					data_city		:	DATACITY,
					empcode			:	USERID,
					empname			:	UNAME
				}
			});
		};
		/*
		 * Get if ME is absent or not
		 * Addded By Apoorv Agrawal
		 * 11-04-2017
		 */
		APIService.meisabsent	=	function(meCode,dateOfAppt,slotOfAppt) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/meisabsent/',
				data : {
					meCode 			:	meCode,
					dateOfAppt		:	dateOfAppt,
					slotOfAppt		:	slotOfAppt,
					data_city	:	DATACITY
				}
			});
		};
		APIService.dealClosedReportData = function($srchParam,$srchWhich,$pageShow,$extraVals,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchDealClosedReport/'+USERID,
				data : {
					empcode : USERID,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					extraVals :	$extraVals,
					parid	  : $parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.insertDataAlloc = function($parentid,$stVal,DATACITY) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/insertDisposeVal',
				data : {
					parentid : $parentid,
					stVal : $stVal,
					empcode : USERID,
					data_city : DATACITY
				}
			});
		}
		APIService.fetchBounceData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchBounceData/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		
		/******************************AddNewLink*****************************/
		
		APIService.JdrIro = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/JdrIro/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.WebIro = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/WebIro/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.whatsappcalled = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/whatsappcalled/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		
		
		/**********************************END********************************/
		
		APIService.fetchBounceECSData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchBounceECSData/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	  :	$parentid,
					data_city :	DATACITY
				}
			});
		}
		APIService.fetchInstantECSData = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchInstantECSData/',
				data : {
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					empcode	  :	USERID,
					parid	:	$parentid,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.insertStopECSReq = function($parentid,$flag) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/insertECSStatus',
				data : {
					parentid : $parentid,
					flag : $flag,
					empcode : USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getSingleChequeStat = function($parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchMulticityTagging/'+$parentid,
				data : {
					parentid 	: 	$parentid,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});
		}
		
		APIService.searchCompanies	=	function($srchData,$empCode) {
			return $http({
				method:'POST',
				//url:APIURL+'/tme_services/tmeInfo/companyAutoSuggest/',
				url:APIURL+'/tme_services/tmenewInfo/companyAutoSuggest/',
				data : {
					srchData : $srchData,
					srchparam : 'compnameLike',
					srchwhich : 'where',
					empcode	:	$empCode,
					data_city	:	DATACITY
				}
			});			
		}
		
		
		APIService.searchCities	=	function($srchData) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchcities/',
				data : {
					srchData : $srchData,
					data_city	:	DATACITY,
					empcode	:	USERID,
					empname	:	UNAME
				}
			});			
		}
		
		APIService.srchCAt	=	function($srchData) { // not in use
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getContractCatPaidSearch/',
				data : {
					srchData : $srchData,
					data_city	:	DATACITY,
					empcode		:	USERID
				}
			});			
		}
		
		APIService.fetchPhoneCompany	=	function($pageShow,$parentid) {
			return $http({
				method:'POST',
				//url:APIURL+'/tme_services/tmeInfo/fetchNumberData/'+USERID,
				url:APIURL+'/tme_services/tmenewInfo/fetchNumberData/'+USERID,
				data : {
					empcode   : USERID,
					parid     :$parentid,
					pageShow  : $pageShow,
					data_city :	DATACITY
				}
			});			
		}
		
		APIService.fetchCategoryCompany	=	function($pageShow,$parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchCategoryData/'+USERID,
				data : {
					empcode : USERID,
					parid:$parentid,
					pageShow  : $pageShow,
					data_city	:	DATACITY
				}
			});			
		}
		
		APIService.getChildInfo	=	function($pageShow,$parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchChildInfo/',
				data : {
					empId 		: USERID,
					data_city	:	DATACITY
				}
			});			
		}
		
		APIService.checkemployeedeclaration	=	function() {
			
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/checkemployeedeclaration',
				data : {
					empcode	    : USERID,
					data_city	:	DATACITY
				}
			});			
		}
		
		APIService.storeemp	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/storeemp',
				data : {
					empcode : USERID,
					data_city	:	DATACITY
				}
			});			
		}

		APIService.catSearch	=	function($search,national,nationalType) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/getCatAutoSuggest/',
				data : {
					searchText 	: 	$search,
					national	:national,
					nationalType:nationalType,
					data_city	:	DATACITY
				}
			});			
		}

		APIService.domainregisterauto =   function($srchData) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/domainregisterauto/',
                data : {
                    srchData 	: $srchData,
                    data_city	:	DATACITY,
                    empcode		:	USERID
                }
            });
        }
		
		APIService.getContractData	=	function($parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getShadowTabGeneralData/'+$parentid,
				data : {
					parentid : $parentid,
					data_city	:	DATACITY
				}
			});			
		}
		
		$rootScope.user	=	USERID;
		
		APIService.catSearchDataMode	=	function($srchStr,$srchId,$srchCity,stp,ntp) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/getCatData/',
				data : {
					srchStr : $srchStr,
					srchId : $srchId,
					srchCity: $srchCity,
					stp:stp,
					ntp:ntp,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getExistingCats	=	function($parid,$srchCity) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/getExistingCatsContract/',
				data : {
					parentid : $parid,
					srchCity: $srchCity,
					ucode : USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getCatPreviewData	=	function($parid,$srchCity,$module,$oldCat) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/catPreviewData/',
				data : {
					parentid : $parid,
					data_city: $srchCity,
					module:$module,
					old_category:$oldCat,
					ucode : USERID
				}
			});
		}
		
		APIService.submitCatData	=	function(parentid,data_city,module,catArr,existArr) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/submitCategories/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					catArr:catArr,
					existArr:existArr,
					ucode : USERID
				}
			});
		}
		
		APIService.check_att_pre = function(parentid,data_city,module,ucode) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/categoryInfo/check_attribute_present/',
                data : {
                    parentid : parentid,
                    data_city: data_city,
                    module:module,
                    ucode:ucode
                }
            });
        }
        
		APIService.getAttributesPage	=	function(parentid,data_city,module,ucode) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/attributesPage/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					ucode:ucode
				}
			});
		}
		
		APIService.updateAttributes	=	function(parentid,data_city,attrTaken,attributes,unique_code_str,validateData) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/updateAttributes/',
				data : {
					parentid : parentid,
					data_city: data_city,
					attrTaken:attrTaken,
					attributes:attributes,
					unique_code_str:unique_code_str,
					validateData:validateData,
					ucode : USERID
				}
			});
		}
		
		APIService.multiParentage	=	function(parentid,data_city,module,uname,ucode,companyname,allcatidlist,removecatidlist) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/findMultiParentage/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					uname:uname,
					ucode:ucode,
					companyname:companyname,
					allcatidlist:allcatidlist,
					removecatidlist:removecatidlist,
					rquest:'check_multiparentange'
				}
			});
		}
		
		APIService.sendCatsForModeration	=	function(catStrRemoved,parentid,catidsel,CatidToBeModerated,data_city,module,companyname,ucode,uname) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/sendCatsForModeration/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					uname:uname,
					ucode:ucode,
					companyname:companyname,
					allcatidlist:catidsel,
					removecatidlist:catStrRemoved,
					CatidToBeModerated:CatidToBeModerated,
					rquest:'insertIntoCCRMultiParent'
				}
			});
		}
		
		APIService.checkCatRestriction	=	function(parentid,data_city,module,catStr,catStrRemoved,ucode) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/checkCatRestriction/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					allcatidlist:catStr,
					removecatidlist:catStrRemoved,
					ucode:ucode
				}
			});
		}
		
		APIService.submitCatsFinal	=	function(parentid,data_city,module,catStr,catStrRemoved,movie_timing,authorisedPaid,nonAuthorisedPaid,authorisedNonPaid,nonAuthorisedNonPaid,catStrNp,instantFlag,allpaidcat,allnonpaidcat) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/submitCatPreview/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					allcatidlist:catStr,
					removecatidlist:catStrRemoved,
					movie_timing:movie_timing,
					paid_auth:authorisedPaid,
					paid_nonauth:nonAuthorisedPaid,
					nonpaid_auth:authorisedNonPaid,
					nonpaid_nonauth:nonAuthorisedNonPaid,
					nonpaid_catlist:catStrNp,
					ucode:USERID,
					uname:UNAME,
					instantFlag:instantFlag,
					allpaidcat:allpaidcat,
					allnonpaidcat:allnonpaidcat,
				}
			});
		}
		
		APIService.save_dc_cat	=	function(parentid,data_city,module,catStr,catStrRemoved,catStrNp,allpaidcat,allnonpaidcat) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/save_dc_cat/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					allcatidlist:catStr,
					removecatidlist:catStrRemoved,
					nonpaid_catlist:catStrNp,
					ucode:USERID,
					uname:UNAME,
					allpaidcat:allpaidcat,
					allnonpaidcat:allnonpaidcat,
				}
			});
		}
		
		APIService.getAreaPincodeInfo	=	function($searchid,parent_id) {
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/areaPincodeInfo/getAllArea/',
				data : {
					opt : $searchid,
					parentid:parent_id,
					data_city:DATACITY
				}
			});
		}
		
		/*
		APIService.getAreaInfo	=	function(term) {
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/bformInfo/getAreaInfo/',
				data : {
					search : term,
					city:DATACITY,
					noparentid:1,
					data_city:DATACITY,
					ucode:USERID,
					uname:UNAME
				}
			});
		}
		*/
		
		
		APIService.getAreaInfo	=	function(term,pincode) {
			if(pincode!= '') 
                pincode = pincode;
            else
				pincode = '';
				
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/getAreaXHR/',
				data : {
					search : term,
					city:DATACITY,
					server_city:DATACITY,
					s_deptCity:DATACITY,
					pincode:pincode,
					ucode:USERID,
					uname:UNAME
				}
			});
		}
		
		APIService.getPincodeInfo	=	function(term) {
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/bformInfo/getPincodeInfo/',
				data : {
					area : term,
					city:DATACITY,
					noparentid:1,
					data_city:DATACITY,
					ucode:USERID,
					uname:UNAME
				}
			});
		}
		
		
		
		APIService.getAllPincodes	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/areaPincodeInfo/getAllPincodes/'+parentid+'/'+DATACITY+'/',
				data : {
					parentid:parentid,
					data_city:DATACITY
				}
			});
		}
		
		APIService.getBudgetData	=	function(tabNo,optNo,tenureNo,parentid,data_city,username,pincode,customPackageVal,onlypackage,flexiPackVal,onlyExclusive,exactRenewal) {
			var flexiVal = 0;
            if(typeof flexiPackVal !== 'undefined') {
                flexiVal = flexiPackVal;
            }
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getBestCampaignInfo/',
				data : {
					optNo	:	optNo,
					tabNo	:	tabNo,
					parentid	:	parentid,
					data_city	:	data_city,
					tenure	:	tenureNo,
					empcode	:	USERID,
					username:	username,
					pincode:pincode,
					customPackage:customPackageVal,
					onlypackageprice:onlypackage ,
					flexiVal:flexiVal,
					only_exclusive:onlyExclusive,
					exact_renewal  : exactRenewal 
				}
			});
		}
		
        APIService.submitBudgetData =   function(parentid,data_city,module,empcode,dataArr,package_10dp_2yr) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/setBudgetData/',
				data : {
					parentid	:	parentid,
					data_city	:	data_city,
					empcode		:	empcode,
					dataArr		:	dataArr,
                   			 module      :   module,
                    			package_10dp_2yr: package_10dp_2yr
				}
			});
		}
		
		APIService.setAreaPincodeData	=	function(pincodeStr,parentid,data_city,module,pincodejson) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/areaPincodeInfo/setAreaPincodeInfo/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					pincodeStr:pincodeStr,
					pincodejson : pincodejson
				}
			});
		}
		
		APIService.getDataFinalBUdget	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getDataBudgetFinal/',
				data : {
					parentid : parentid,
					data_city:DATACITY,
					ucode:USERID
				}
			});
		}
		
		APIService.findSearchPlusFlag	=	function(parentid,data_city) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/searchPlusCampFinder/',
				data : {
					parentid : parentid,
					data_city:data_city
				}
			});
		}
		
		APIService.docHospRedirectCheck	=	function(parentid,data_city,vertical_id) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/docHospRedirectCheck/',
				data : {
					parentid : parentid,
					data_city:data_city,
					vertical_id:vertical_id
				}
			});
		}
		
		APIService.othersVerticalRedirect	=	function(parentid,data_city,ucode,others_vertical_name) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/othersVerticalRedirect/',
				data : {
					parentid 	: parentid,
					data_city	: data_city,
					ucode		: ucode,
					others_vertical_name : others_vertical_name
				}
			});
		}
		
		APIService.categoryResetAPI	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/categoryResetAPI/',
				data : {
					parentid : parentid,
					data_city:DATACITY,
					ucode : USERID
				}
			});
		}

		APIService.getExistingInventory	=	function(parentid,data_city) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getExistingInventory/',
				data : {
					parentid : parentid,
					data_city:data_city
				}
			});
		}
		
		APIService.getCampaignMaster	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getCampaignMaster/',
				data : {
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getDispositionList	=	function(allocid,secondary_allocid) {	//API
		  return $http({
			method: 'POST', 
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json, text/plain, */*' },
			transformRequest: transform,
			url: APIURL+'/jdboxNode/user/get-menudispostion-info',
			data : {
				allocid 	: allocid,
				secondaryid : secondary_allocid,
				data_city	: DATACITY,
				empcode		: USERID,
				type		: 'disposition',
				module		: 'demo'
			}
		  });
		}
		
		APIService.compareBform	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/compareInfo/compareBform/'+parentid,
				data : {
					parentid 	: 	parentid,
					data_city	:	DATACITY,
					empcode		:	USERID,
					empname		:	UNAME
				}
			});
		}   
		
		APIService.insertLogBformDC	=	function(DATA) { // Done
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/compareInfo/insertLogBformDC/',
				data:DATA
			});
		}  

		APIService.getModuleType	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getModuleType/',
				data : {
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getEcsEmpcode	=	function(empcode) {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getEcsEmpcode/',
				data : {
					empcode		:	empcode,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.getMainTabGeneralData	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getMainTabGeneralData/'+parentid,
				data : {
					parentid : parentid,
					data_city	:	DATACITY
				}
			});
		}  

		APIService.findGetContractData	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getContractDataInfo/',
				data : {
					parentid:parentid,
					data_city:DATACITY,
					ucode:USERID
				}
			});
		}
		
		APIService.fetchLiveData  =   function(parentid) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/fetchLiveData/',
                data : {
                    parentid : parentid,
                    data_city: DATACITY,
                    ucode	 : USERID,
                    uname 	 : UNAME
                }
            });
        }
		
		APIService.releaseInventoryData	=	function(parentid,version,whichMode,i_reason,i_data) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/releaseInventory/',
				data : {
					parentid:parentid,
					version:version,
					astate:whichMode,
					data_city:DATACITY,
					i_reason:i_reason,
					i_updatedby:USERID,
					i_data:i_data
				}
			});
		}   
		
		
		APIService.send_mngr_request	=	function(parentid,eventParam,empcode) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/send_mngr_request/',
				data : {
					parentid :  parentid,
					eventParam : eventParam,
					empcode : empcode,
					data_city: DATACITY
				}
			});
		}
		
		APIService.fetch_mngr_approval_condition	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetch_mngr_approval_condition/',
				data : {
					parentid :  parentid,
					data_city: DATACITY
				}
			});
		}

		APIService.checkvccontract	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/checkvccontract/'+parentid,
				data : {
					parentid :  parentid,
					data_city: DATACITY
				}
			});
		}

		APIService.checkvccondition	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/checkvccondition/'+parentid,
				data : {
					parentid :  parentid,
					data_city: DATACITY
				}
			});
		}

		APIService.getJdrrPath	=	function(parentid,data_city) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getJdrrPath/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					empcode   : USERID
				}
			});
		}
		
		
		APIService.addjdrr	=	function(parentid,version,combo) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addjdrr/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city :  DATACITY,
					empcode : USERID,
					combo : combo
				}
			});
		}
      
		APIService.addbanner	=	function(parentid,version,instruction,combo,no_of_rotation) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addbanner/',
				data : {
					parentid :  parentid,
					version :  version,
					instruction :instruction,
					s_deptCity :  DATACITY,
					combo :combo,
					no_of_rotation:no_of_rotation,
					data_city :  DATACITY,
					empcode : USERID
				}
			});
		}
		
		
		APIService.addjdrrplus	=	function(parentid,version,instruction,combo,type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addjdrrplus/',
				data : {
					parentid :  parentid,
					version :  version,
					instruction :instruction,
					s_deptCity :  DATACITY,
					combo :combo,
					type:type,
					data_city :  DATACITY
				}
			});
		}
		APIService.addcombotwo=	function(parentid,version,instruction) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addcombotwo/',
				data : {
					parentid :  parentid,
					version :  version,
					instruction :instruction,
					s_deptCity :  DATACITY,
					data_city :  DATACITY
				}
			});
		}
		
		


		APIService.bannerlog	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/bannerlog/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city :  DATACITY

				}
			});
		}



		APIService.checkbanner	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkbanner/',
				data : {
					parentid :  parentid,
					version :  version,
					s_deptCity :  DATACITY,
					data_city :  DATACITY
				}
			});
		}

		APIService.deletebanner	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletebanner/',
				data : {
					parentid :  parentid,
					version :  version,
					s_deptCity :  DATACITY,
					data_city :  DATACITY,
					empcode	: USERID
				}
			});
		}
		
		APIService.deletejdrrplus	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletejdrrplus/',
				data : {
					parentid :  parentid,
					version :  version,
					s_deptCity :  DATACITY,
					data_city :  DATACITY
				}
			});
		}
		
		//////////////////////timer status///////////////////////////////
		APIService.getTimerStatus	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getTimerStatus?empcode='+USERID,
				data : {
					empcode :  USERID,
					data_city :  DATACITY

				}
			});
		}
		
		APIService.stopTimer	=	function(disposition,parentid,empname) { // Pending
			return $http({
				method:'POST',
				url:APIURL+'/api/service.stopTimer.php?empcode='+USERID+'&parentid='+parentid+'&disposition='+disposition+'&empname='+empname,
			});
		}
		
		APIService.secondTimer	=	function() { // Pending
			return $http({
				method:'POST',
				url:APIURL+'/api/service.secondTimer.php?empcode='+USERID,
			});
		}
		
		APIService.timeUp	=	function() { // Pending
			return $http({
				method:'POST',
				url:APIURL+'/api/service.timeup.php?empcode='+USERID,
			});
		}
		
		//////////////////////timer status///////////////////////////////


		APIService.jdrrlog	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/jdrrlog/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city :  DATACITY
				}
			});
		}

		APIService.checkjdrr	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkjdrr/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city :  DATACITY,
					empcode : USERID
				}
			});
		}


		APIService.deletejdrr	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletejdrr/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city : DATACITY,
					empcode : USERID
				}
			});
		}

		APIService.get_banner_spec	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/get_banner_spec/',
				data : {
					parentid :  parentid,
					version :  version,
					data_city : DATACITY
				}
			});
		}
		
		APIService.getversion	=	function(parentid,data_city) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getVersion/',
				data : {
					parentid :  parentid,
					data_city :  data_city,
					usercode : USERID
				}
			});	
		}
		
		APIService.getSetBudgetData	=	function(parentid,duration) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/getSetBudgetData/',
				data : {
					parentid : parentid,
					data_city: DATACITY,
					usercode:USERID,
					duration:duration
				}
			});
		}
		
		APIService.resetCampaign	=	function(parentid,empname) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/campaignInfo/resetCampaign/',
				data : {
					parentid : parentid,
					usercode : USERID,
					username : empname,
					data_city : DATACITY
				}
			});
		}
		APIService.abc	=	function() {
			console.log("dddd");
		}
		/*
		 * API'S Added For mktgJrPage Allocation Page
		 * Author: Apoorv Agrawal
		 * Start: 15-03-2016
		 * changes in servies And API as Alternate Address is Now Vissible in GRAB FLOW 
		 * FLAG RESPOSIBLE Grb_Normal_alt_add
		 * Changes Done @ 06-05-2016(Apoorv Agrawalss)
		 */
		APIService.apptData	=	function(grabFlag,pincode,actionDate,stVal,parentid,AllMeFlag,data_city,USERID,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add,allocateToJdaClick,bypass_autoAlloc,team_type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/get_me/',
				data : {
					graBFlag 		:	grabFlag,
					pincode 		:	pincode,
					date 			:	actionDate,
					stVal			:	stVal,
					parentid		:	parentid,
					AllMeFlag		:	AllMeFlag,
					data_city		:	data_city,
					tme_code		:	USERID,
					allocToME		:	display_allocateToME,
					alloc_to_ME_TME	:	alloc_to_ME_TME,
					Grb_Normal_alt_add	:	Grb_Normal_alt_add,
					allocateToJdaClick	:	allocateToJdaClick,
					bypass_autoAlloc	:	bypass_autoAlloc,
					team_type	: team_type,
					login_city : LOGIN_CITY
				}
			});
		}
		//~ APIService.getTime	=	function(key,data_city,pincode,parentid,actionDate,empId,empName,exten,stVal,textInst,jrCode,AllMeFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add) {
			//~ return $http({
				//~ method	:	'POST',
				//~ url		:	APIURL+'/tme_services/dispositionInfo/disposeFlow',
				//~ data 	:	{
					//~ parentid 		: 	parentid,
					//~ actTime 		: 	key,
					//~ meCode 			: 	jrCode,
					//~ actDate 		: 	actionDate,
					//~ disposeVal		: 	stVal,
					//~ pincode 		:	pincode,
					//~ Extn_id			:	STATID,
					//~ ucode			: 	empId,
					//~ data_city		:	data_city,
					//~ instrct			:  	textInst,//Instructions
					//~ AllMeFlag		:	AllMeFlag,
					//~ allocToME		:	display_allocateToME,
					//~ alloc_to_ME_TME	:	alloc_to_ME_TME,
					//~ Grb_Normal_alt_add	:	Grb_Normal_alt_add
				//~ }
			//~ });
		//~ }
		/* 
		 * New Code Added As the functionality Changed
		 * Done By Apoorv Agrawal
		 * Date: 06-01-2017
		 * new Flag Added Today (27-02-2017) moveToIDCFlag
		*/
		APIService.getTime	=	function(key,data_city,pincode,parentid,actionDate,empId,empName,exten,stVal,textInst,jrCode,AllMeFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add,elgFlag,eligibilty,list_of_me_pincodewise,TMERANK,ignore_followUp,logInsert,TOTALTME,moveToIDCFlag) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/disposeFlow',
				data 	:	{
					parentid 		: 	parentid,
					actTime 		: 	key,
					meCode 			: 	jrCode,
					actDate 		: 	actionDate,
					disposeVal		: 	stVal,
					pincode 		:	pincode,
					Extn_id			:	STATID,
					ucode			: 	empId,
					uname			:	empName,
					data_city		:	data_city,
					instrct			:  	textInst,//Instructions
					AllMeFlag		:	AllMeFlag,
					allocToME		:	display_allocateToME,
					alloc_to_ME_TME	:	alloc_to_ME_TME,
					Grb_Normal_alt_add	:	Grb_Normal_alt_add,
					elgFlag				:	elgFlag,
					eligibilty			:	eligibilty,
					list_of_me_pincodewise	:list_of_me_pincodewise,
					TMERANK	:TMERANK,
					ignore_followUp	:ignore_followUp,
					logInsert	:logInsert,
					TOTALTME	:TOTALTME,
					moveToIDCFlag	:moveToIDCFlag
				}
			});
		}
		/*
		 * This Service will check if a particular ME's time slot has already been booked by any TME or Not
		 * Author : Apoorv  Agrawal
		 * Start Date: 17-10-2016
		 * End Date: 17-10-2016
		*/
		APIService.checkSlotForMe	=	function(key,data_city,pincode,parentid,actionDate,empId,empName,exten,stVal,textInst,jrCode,AllMeFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/checkSlotForMe',
				data 	:	{
					parentid 		: 	parentid,
					actTime 		: 	key,
					meCode 			: 	jrCode,
					actDate 		: 	actionDate,
					disposeVal		: 	stVal,
					pincode 		:	pincode,
					Extn_id			:	STATID,
					ucode			: 	empId,
					data_city		:	data_city,
					instrct			:  	textInst,//Instructions
					AllMeFlag		:	AllMeFlag,
					allocToME		:	display_allocateToME,
					alloc_to_ME_TME	:	alloc_to_ME_TME,
					Grb_Normal_alt_add	:	Grb_Normal_alt_add
				}
			});
		}
		/*New Code Added Here*/
		APIService.create_otp	=	function(mktEmpCode,empName,parentid,companyname) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/create_otp',
				data 	:	{
					parentid 		: 	parentid,
					tme_code 		: 	mktEmpCode,
					tme_name 		: 	empName,
					companyname		: 	companyname,
					data_city :  DATACITY
				}
			});
		}
		/*New Code Added Here*/
		APIService.checkOTP_otp	=	function(mktEmpCode,otp,parentid) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/checkOTP_otp',
				data 	:	{
					parentid 		: 	parentid,
					otp 			: 	otp,
					tme_code 		: 	mktEmpCode,
					data_city :  DATACITY
				}
			});
		}
		/*New Code Added Here to capture ALL ME option Appt*/
		APIService.insertAllMeDetails	=	function(key,data_city,pincode,parentid,actionDate,empId,empName,exten,stVal,textInst,jrCode,AllMeFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add,elgFlag,eligibilty,list_of_me_pincodewise,TMERANK,ignore_followUp,logInsert,TOTALTME,moveToIDCFlag) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/insertAllMeDetails',
				data 	:	{
					parentid 		: 	parentid,
					actTime 		: 	key,
					meCode 			: 	jrCode,
					actDate 		: 	actionDate,
					disposeVal		: 	stVal,
					pincode 		:	pincode,
					Extn_id			:	STATID,
					ucode			: 	empId,
					data_city		:	data_city,
					instrct			:  	textInst,//Instructions
					AllMeFlag		:	AllMeFlag,
					allocToME		:	display_allocateToME,
					alloc_to_ME_TME	:	alloc_to_ME_TME,
					Grb_Normal_alt_add	:	Grb_Normal_alt_add,
					elgFlag				:	elgFlag,
					eligibilty			:	eligibilty,
					list_of_me_pincodewise	:list_of_me_pincodewise,
					TMERANK	:TMERANK,
					ignore_followUp	:ignore_followUp,
					logInsert	:logInsert,
					TOTALTME	:TOTALTME,
					moveToIDCFlag	:moveToIDCFlag
				}
			});
		}
		APIService.insertpincodeDetails	=	function(key,data_city,pincode,parentid,actionDate,empId,empName,exten,stVal,textInst,jrCode,AllMeFlag,display_allocateToME,alloc_to_ME_TME,Grb_Normal_alt_add,elgFlag,eligibilty,list_of_me_pincodewise,TMERANK,ignore_followUp,logInsert,TOTALTME,moveToIDCFlag) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/dispositionInfo/insertpincodeDetails',
				data 	:	{
					parentid 		: 	parentid,
					actTime 		: 	key,
					meCode 			: 	jrCode,
					actDate 		: 	actionDate,
					disposeVal		: 	stVal,
					pincode 		:	pincode,
					Extn_id			:	STATID,
					ucode			: 	empId,
					data_city		:	data_city,
					instrct			:  	textInst,//Instructions
					AllMeFlag		:	AllMeFlag,
					allocToME		:	display_allocateToME,
					alloc_to_ME_TME	:	alloc_to_ME_TME,
					Grb_Normal_alt_add	:	Grb_Normal_alt_add,
					elgFlag				:	elgFlag,
					eligibilty			:	eligibilty,
					list_of_me_pincodewise	:list_of_me_pincodewise,
					TMERANK	:TMERANK,
					ignore_followUp	:ignore_followUp,
					logInsert	:logInsert,
					TOTALTME	:TOTALTME,
					moveToIDCFlag	:moveToIDCFlag
				}
			});
		}
		/*
		 * Service for all me Login
		 */
		APIService.allMelogin	=	function(loginId,logInPassword) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/mkgJrInfo/allMelogin',
				data 	:	{
					loginid 		: 	loginId,
					logInPassword 	: 	logInPassword,
					data_city :  DATACITY
				}
			});
		}
		/*
		 * Services For Alternate Address
		 */ 
		APIService.getArea	=	function(city) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/locationInfo/get_area',
				data 	:	{
					city_name : city,
					data_city : DATACITY,
					empcode   : USERID
				}
			});
		}
		APIService.getPincode	=	function(city,area_selected) {
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/locationInfo/pincode_master',
				data 	:	{
					city 	: 	city,
					area	:	area_selected,
					data_city :  DATACITY,
					empcode : USERID
				}
			});
		}
		/*
		 * Auto Suggest Street Search
		 * Author: Apoorv Agrawal
		*/
		APIService.srchStreet	=	function($srchData,selectedArea,data_city,parentid,pincodeSelected) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/locationInfo/street_master_auto/',
				data : {
					search 	  :	$srchData,
					area	  :	selectedArea,
					city	  :	data_city,
					pincode	  :	pincodeSelected,
					data_city :  DATACITY,
					module	  : 'tme',
					parentid  : parentid,
					empcode   : USERID,
					empname   : UNAME
					//display_area Show Display Area
				}
			});			
		}
		/*
		 * Inserting Alternate Address
		 * Author: Apoorv Agrawal
		*/
		APIService.saveAlternaleAdd	=	function(data_city,companyname,bldingselected,selectedStreet,lanmarkselected,area_selected,pincodeSelected,state,ucode,countryCode,parentid){
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/addAlternateAddress/',
				data : {
					companyname 	:	companyname,
					parentid		:	parentid,
					country			:	countryCode,
					city			:	data_city,
					state			:	state,
					area			:	area_selected,
					building		:	bldingselected,
					street			:	selectedStreet,
					landmark		:	lanmarkselected,
					pincode			:	pincodeSelected,
					ucode			:	ucode,
					data_city 		:  DATACITY
					//display_area Show Display Area
				}
			});			
		}
		
		 APIService.addjdomni	=	function(parentid,version,combo,type,user_price_setup,user_price,user_price_monthly,ecs_flag) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addjdomni/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					combo :combo,
					type :type,
					user_price_setup :user_price_setup,
					user_price:user_price,
					user_price_monthly :user_price_monthly,
					ecs_flag : ecs_flag
				}
			});
		}
		
		APIService.deletejdomni	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletejdomni/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					empcode : USERID
				}
			});
		}
		
		APIService.payment_type	=	function(parentid,type,version,payment_mode,campaignids,original_flg,disc_flg,twoyear_flg) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/payment_type/',
				data : {
					parentid :  parentid,
					type :  type,
					version :version,
					payment_mode:payment_mode,
					campaignids:campaignids,
                    original_flg:original_flg,
                    disc_flg:disc_flg,
                    twoyear_flg:twoyear_flg,
                    data_city :  DATACITY,
                    empcode:USERID
				}
			});
		}
		
		
		
		APIService.campaignpricelist	=	function(parentid,version,combo,omni_type,camp_selected,banner_rotation) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/campaignpricelist/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					combo :combo,
					omni_type : omni_type,
					camp_selected:camp_selected,
					banner_rotation:banner_rotation
				}
			});
		}
		
		
		APIService.ecspricelist	=	function(parentid,version,combo,omni_type,camp_selected,banner_rotation) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/ecspricelist/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					combo :combo,
					omni_type : omni_type,
					camp_selected:camp_selected,
					banner_rotation:banner_rotation
				}
			});
		}
		
		
		
        APIService.go_to_payment_page   =   function(parentid,version,ecs_flg,user_offer,user_mon_offer,user_offer_jdrr,user_mon_offer_jdrr,combo,omni_type,setup_exclude,user_price_setup,no_of_rotation,dependent) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/go_to_payment_page/',
				data : {
					parentid :  parentid,
					s_deptCity :  DATACITY,
					data_city :  DATACITY,
					version :  version,
					ecs_flag :  ecs_flg,
					user_price :user_offer,
					user_price_monthly:user_mon_offer,
					user_offer_jdrr :user_offer_jdrr,
					user_mon_offer_jdrr:user_mon_offer_jdrr,
					instruction:'',
					empId : USERID,
					combo : combo,
					omni_type : omni_type,
					setup_exclude : setup_exclude,
					user_price_setup :user_price_setup,
					no_of_rotation:no_of_rotation,
					dependent:dependent
				}
			});
		}
		
		
		APIService.payment_summary_list	=	function(parentid,version,combo,omni_type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/payment_summary_list/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					combo :combo,
					omni_type : omni_type
				}
			});
		}
		
		APIService.delete_unchecked	=	function(parentid,version,unck_arr) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/delete_unchecked/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					s_deptCity :DATACITY,
					version :  version,
					unck_arr :  unck_arr,
					usercode :  USERID
				}
			});
		}
		
		APIService.deleteallcampaigns	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deleteallcampaigns/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					s_deptCity :DATACITY,
					version :  version,
					usercode :  USERID 
				}
			}); 
		}		
		
		APIService.call_disc_api	=	function(parentid,version,discount) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/call_disc_api/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version :  version,
					discount :  discount,
					usercode :  USERID
				}
			});
		} 
		
		
		APIService.check_ecs	=	function(parentid,version,module_name) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/check_ecs/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					module_name : module_name,
					usercode :  USERID
				}
			});
		}
		
		
		APIService.get_bankdetials	=	function(parentid,version,ifcs) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/get_bankdetials/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					ifcs :  ifcs,
				}
			});
		}
		 APIService.get_bankdetialsmicr  =   function(parentid,version,micr) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/get_bankdetialsmicr/',
                data : {
                    parentid :  parentid,
                    data_city :  DATACITY,
                    version :  version,
                    micr :  micr,
                }
            });
        }
		APIService.save_bankdetials	=	function(parentid,version,ifcs,acc_num,acc_name,acc_type,bank_name,branch_location,bank_branch,micr) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/save_bankdetials/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					ifcs :  ifcs,
					acc_num :  acc_num,
					acc_name :  acc_name,
					acc_type :  acc_type,
					bank_name :  bank_name,
					branch_location :  branch_location,
					bank_branch :  bank_branch,
					micr:micr
				}
			});
		}
		
		APIService.get_accountdetials	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/get_accountdetials/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
				}
			});
		}
		
		
		APIService.check_upfront	=	function(parentid,version,type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/check_upfront/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					type :type
				}
			});
		}
		
		
		APIService.customjdrrhandling	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/customjdrrhandling/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
				}
			});
		}
		
		
		APIService.jdrrplusdiscount	=	function(parentid,version,ecs_flg,user_offer,user_mon_offer) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/jdrrplusdiscount/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					ecs_flag :  ecs_flg,
					user_offer :user_offer,
					user_mon_offer:user_mon_offer,
					empId : USERID
				}
			});
		}
		
		
		APIService.addjdrrLive	=	function(parentid,version,ecs_flg,user_price,user_price_monthly,combo) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addjdrrLive/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					ecs_flag :  ecs_flg,
					user_offer_jdrr :user_price,
					user_mon_offer_jdrr:user_price_monthly,
					empId : USERID,
					combo :combo
				}
			});
		}
		
		APIService.addbannerlive	=	function(parentid,version,ecs_flg,user_price,user_price_monthly,combo) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addbannerlive/',
				data : {
					parentid :  parentid,
					s_deptCity :  DATACITY,
					version :  version,
					instruction:'',
					ecs_flag :  ecs_flg,
					user_offer_jdrr :user_price,
					user_mon_offer_jdrr:user_price_monthly,
					empId : USERID,
					combo :combo,
					data_city :  DATACITY
				}
			});
		}
		
		
		APIService.addjdomniLive	=	function(parentid,version,ecs_flg,user_price,user_price_monthly,combo,omni_type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addjdomniLive/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					s_deptCity :  DATACITY,
					version :  version,
					ecs_flag :  ecs_flg,
					user_price :user_price,
					user_price_monthly:user_price_monthly,
					empId : USERID,
					combo :combo,
					omni_type : omni_type
				}
			});
		}
		
		
		
		APIService.tempactualbudgetupdate	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/tempactualbudgetupdate/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					usercode :  USERID,
					version :  version
				}
			});
		}
		
		
		APIService.checkdomainavailibilty	=	function(parentid,version,domain_name,tlds) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkdomainavailibilty/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					user_code : USERID,
					domain_name:domain_name,
					tlds:tlds
					
				}
			});
		}
		
		APIService.checkdomainoptavailibilty	=	function(parentid,version,domain_name) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkdomainoptavailibilty/',
				data : {
					parentid :  parentid,
					data_city :  DATACITY,
					version :  version,
					user_code : USERID,
					domain_name:domain_name
					
				}
			});
		}
		
		
		
		
       //$scope.domain_registrant_email[0],$scope.forget_link_Output[0],$scope.action_flag_forget,$scope.action_flag_forgetstatus
        APIService.saveomnidomains  =   function(parentid,version,website1,website2,website3,payment_type,own_website,combo,domain_registername,domain_userid,domain_pass,domain_regiter_emailId,domainReg_forget_link,action_flag_forget,action_flag_forgetstatus,omni_domain_option) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/saveomnidomains/',
                data : {
                    data_city :  DATACITY,
                    user_code : USERID,
                    parentid  :  parentid,
                    version   :  version,
                    website1  : website1,
                    website2  : website2,
                    website3  : website3,
                    payment_type:payment_type,
                    own_website : own_website,
                    combo : combo,
                    domain_registername : domain_registername,
                    domain_userid : domain_userid,
                    domain_pass : domain_pass,
                    domain_regiter_emailId : domain_regiter_emailId,
                    domainReg_forget_link : domainReg_forget_link,
                    action_flag_forget : action_flag_forget,
                    action_flag_forgetstatus : action_flag_forgetstatus,
                    omni_domain_option : omni_domain_option
                }
            });
        }
		
		
		APIService.getowndomainname	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getowndomainname/',
				data : {
					data_city :  DATACITY,
					user_code : USERID,
					parentid :  parentid,
					version :  version,
				}
			});
		}
		
		
		APIService.deletedomainname	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletedomainname/',
				data : {
					data_city :  DATACITY,
					usercode : USERID,
					parentid :  parentid,
					version :  version,
				}
			});
		}
		
		APIService.checkemail	=	function(parentid,version,other_parameter) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkemail/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version,
					other_parameter : other_parameter
				}
			});
		}
		
		
		APIService.getpricelist	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getpricelist/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version,
					usercode : USERID
				}
			});
		}
		
		APIService.combopackageprice	=	function(parentid,version,combo) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/combopackageprice/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version,
					combo : combo
				}
			});
		}
		
		
		APIService.combocustomprice	=	function(parentid,version,combo_price,domain_field_incl,type,custom_setup_fees) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/combocustomprice/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version,
					combo_price : combo_price,
					domain_field_incl :domain_field_incl,
					type : type,
					custom_setup_fees :custom_setup_fees
				}
			});
		}
		
		APIService.combopricereset	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/combopricereset/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version
				}
			});
		}
		
		APIService.comboprice	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/comboprice/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version
				}
			});
		}
		
		APIService.combopricelist	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/combopricelist/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version
				}
			});
		}
		
		APIService.combopricemin	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/combopricemin/',
				data : {
					data_city :  DATACITY,
					parentid :  parentid,
					version :  version
				}
			});
		}

		APIService.storeloyaltyinfo	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/storeloyaltyinfo/'+USERID,
				data : {
					empcode :  USERID,
					data_city :  DATACITY
				}
			});
		};
		
		APIService.fetchloyaltyinfo	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/fetchloyaltyinfo/'+USERID,
				data : {
					empcode :  USERID,
					data_city :  DATACITY
				}
			});
		};

		APIService.getforgetLink   =   function(registername) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/getforgetLink/',
                data : {
                    data_city :  DATACITY,
                    empCode   :  USERID,
                    registername  : registername
                }
            });
        }

		APIService.setTemplateId	=	function(parentid,version,template_id,data_city) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/setTemplateId/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					template_id :  template_id,
					usercode :  USERID
				}
			});
		} 
		
		APIService.sendomnidemo	=	function(parentid,version,mobile,emailid,national_catid,getLinkVal) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/sendomnidemo/',
				data : {
					parentid  :  parentid,
					data_city :  DATACITY,
					version    :  version,
					mobile :  mobile,
					usercode :  USERID,
					emailid :emailid,
					username : UNAME,
					national_catid: national_catid,
					getLinkVal : getLinkVal
				}
			});
		} 
		APIService.sendYOWlink =   function(parentid,version,mobile,emailid,checkflg,national_catid) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/sendYOWlink/',
                data : {
                    parentid  :  parentid,
                    data_city :  DATACITY,
                    version    :  version,
                    mobile :  mobile,
                    usercode :  USERID,
                    emailid :emailid,
                    username : UNAME,
                    national_catid: national_catid,
                    checkflg:checkflg
                }
            });
        }
		APIService.checkCategoryType	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkCategoryType/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}
		
		APIService.insertDemoLinkDetails	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/insertDemoLinkDetails/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}
		
		APIService.fetchDemoLinkDetails	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchDemoLinkDetails/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}
		
		APIService.EmpMessageDetails	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/messageBroadcast/messageDetails/',
				data : {
					EmpCode	: USERID,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.EmpMessageUpdates	=	function(mediaid,emp) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/messageBroadcast/messageUpdates/',
				data : {
					id	: mediaid,
					emp  : emp,
					data_city:  DATACITY
				}
			});
		}

		APIService.fetchdemocategories	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchdemocategories/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}
		APIService.transferaccdetailstomain	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/transferaccdetailstomain/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}

		APIService.sendjdpaylink	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/sendjdpaylink/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY
				}
			});
		}

		APIService.fetchpaymentype	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchpaymentype/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.setecs	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/setecs/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
				}
			});
		}		

		APIService.sendratinglink	=	function(parentid,compname,mobile,email) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/sendratinglink/',
				data : {
					parentid :  parentid,
					compname:  compname,
					mobile :mobile,
					email :email,
					empcode :USERID,
					data_city:  DATACITY
					
				}
			});
		}
		
		APIService.checklive	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checklive/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					empcode: USERID,
					empname : UNAME
				}
			});
		}
		
		APIService.chkRatingCat	=	function(parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/chkRatingCat/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					empcode:USERID,
					empname : UNAME
				}
			});
		}
		
		APIService.gettemplateurl	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/gettemplateurl/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode : USERID
				}
			});
		}
		
		APIService.storeomnitemplateinfo	=	function(parentid,version,template_id,template_name) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/storeomnitemplateinfo/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode : USERID,
					template_id : template_id,
					template_name : template_name
				}
			});
		}	
		
		APIService.addomnitemplatetemp	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addomnitemplatetemp/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.deleteomnitemplatetemp	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deleteomnitemplatetemp/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.addomnitemplatelive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/addomnitemplatelive/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.deleteomnitemplatelive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deleteomnitemplatelive/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.checkpackagedepend	=	function(parentid,version,type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkpackagedepend/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					type :type
				}
			});
		}
		
APIService.ecsTransfer = function(ecs_flag,extn) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/ecsTransfer',
				data : {
					ecs_flag	: ecs_flag,
					extn : extn,
					data_city:  DATACITY
				}
			});
		}

APIService.iroAppTransfer = function(ecs_flag,extn) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/iroAppTransfer',
				data : {
					ecs_flag	: ecs_flag,
					extn : extn,
					data_city:  DATACITY
				}
			});
		}

	APIService.iroAppSaveExit = function(parentid,city,paidflag) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/iroAppSaveExit',
				data : {
					parentid	: parentid,
					city : city,
					usercode:USERID,
					paidflag:paidflag,
					data_city:  DATACITY
				}
			});
		}

		APIService.proceedCompany = function(parentid,city,Uniquefield) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/mkgJrInfo/proceedCompany',
				data : {
					parentid	: parentid,
					city : city,
					usercode:USERID,
					Uniquefield:Uniquefield,
					uname:UNAME,
					data_city:  DATACITY
				}
			});
		}



		APIService.fetchExpiredDataEcs = function($srchParam,$srchWhich,$pageShow,$parentid) {
		
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchExpiredDataEcs/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	:	$parentid
				}
			});
		}
		
		APIService.fetchExpiredDataNonEcs = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchExpiredDataNonEcs/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	  :	$parentid
				}
			});
		}
		
		APIService.fetchdeliverySystem = function($srchParam,$srchWhich,$pageShow,$parentid) {
			return $http({			
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchdeliverySystem/',
				data : {
					empcode : USERID,
					data_city : DATACITY,
					srchparam : $srchParam,
					srchwhich : $srchWhich,
					pageShow  : $pageShow,
					parid	  :	$parentid
				}
			});
		}

		APIService.checkaccess	=	function(parentid,version,module) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkaccess/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					module :module,
					usercode:USERID
				}
			});
		}
		
		APIService.fetchpricechatprice	=	function(parentid,version,type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/fetchpricechatprice/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					type :type,
				}
			});
		}
		
		
		APIService.insert_discount	=	function(parentid,version,campaignid,custom_value) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/insert_discount/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode :USERID,
					campaignid :campaignid,
					custom_value :custom_value
				}
			});
		}
		
		APIService.get_discount_info	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/get_discount_info/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode :USERID,
				}
			});
		}
		APIService.check_one_plus_block = function(datacity){
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/check_one_plus_block/',
				data : {
					data_city:  DATACITY,
					empcode	:	USERID
				}
			});
		}
		APIService.checkemployeeeligible	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/checkemployeeeligible/',
				data : {
					parentid :  parentid,
					data_city:  DATACITY,
					version:  version,
					usercode :  USERID
				}
			});
		}

		APIService.deletejdrrLive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletejdrrLive/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.deletejdomniLive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletejdomniLive/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.deletebannerLive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletebannerLive/',
				data : {
					parentid :  parentid,
					version:  version,
					s_deptCity:  DATACITY,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.deletecampaign	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletecampaign/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode :USERID
				}
			});
		}
		
		APIService.deletecombolive	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/deletecombolive/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}

		APIService.saveemailids	=	function(parentid,version,email) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/saveemailids/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					email :email,
					usercode:USERID
				}
			});
		}
		
		APIService.emailpackageprice	=	function(parentid,version,email_type) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/emailpackageprice/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					email_type :email_type,
					usercode:USERID
				}
			});
		}
		
		APIService.emailpackagerequired	=	function(parentid,version,email_type,no_emailid,admin_username) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/emailpackagerequired/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					email_type :email_type,
					usercode:USERID,
					no_emailid : no_emailid,
					admin_username : admin_username 
				}
			});
		}
		
		APIService.smspackagerequired	=	function(parentid,version,no_sms) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/smspackagerequired/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode:USERID,
					no_sms : no_sms
				}
			});
		}		
		APIService.SSLpackagerequired   =   function(parentid,version,ssl_payment_type,ssl_val) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/SSLpackagerequired/',
                data : {
                    parentid :  parentid,
                    version:  version,
                    data_city:  DATACITY,
                    usercode:USERID,
                    ssl_payment_type : ssl_payment_type,
                    ssl_val :ssl_val
                }
            });
        }
        
        APIService.deleteSSLPackage   =   function(parentid,version,ssl_payment_type,ssl_val) {
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/deleteSSLPackage/',
                data : {
                    parentid :  parentid,
                    version:  version,
                    data_city:  DATACITY,
                    usercode:USERID,
                    ssl_payment_type : ssl_payment_type,
                    ssl_val :ssl_val
                }
            });
        }
		APIService.smsprice	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/smsprice/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY
				}
			});
		}
		
		APIService.SendJDpaysmsemail = function(parentid,numbers,email,companyname,username) { // Pending
			 return $http({
				method: 'POST', 
				url: APIURL+'/library/RatingEmailSms.php?parentid='+parentid+'&mobile='+numbers+'&email='+email+'&company='+companyname+'&city='+DATACITY+'&ucode='+USERID+'&uname='+username+'&bform=1&action=JdPayLink&bform=1'
			});
		}

		APIService.newpricechatval	=	function(parentid,version) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/newpricechatval/',
				data : {
					parentid :  parentid,
					version:  version,
					data_city:  DATACITY,
					usercode :USERID
				}
			});
		}
		
		APIService.getnationalflag = function(parentid,data_city){
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/getnationalflag',
				data : {
					parentid : parentid,
					data_city: data_city,
					ucode :USERID
				}
			});
		}
		
		APIService.calcupdatedatanational = function(parentid,data_city,budget,tenure,recalculate_flag){
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/calcupdatedatanational',
				data : {
					parentid : parentid,
					data_city: data_city,
					budget: budget,
					tenure: tenure,
					recalculate_flag: recalculate_flag,
					
				}
			});
		}
		APIService.fetchtempdatanational = function(parentid,data_city,version){
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/fetchtempdatanational',
				data : {
					parentid : parentid,
					data_city: data_city,
					version: version
				}
			});
		}
		
		APIService.removeLocalforNational = function(parentid,data_city){
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/removeLocalforNational',
				data : {
					parentid : parentid,
					data_city: data_city
				}
			});
		}
		
		APIService.submitRCatData	=	function(parentid,data_city,module,catArr,eCatArr,save_for) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/submitRelevantCat/',
				data : {
					parentid : parentid,
					data_city: data_city,
					module:module,
					catArr:catArr,
					ecatArr:eCatArr,
					save_for:save_for,
                    ucode:USERID
				}
			});
		}
		
		APIService.getPopularCat = function(parentid,category_type,stp,ntp){
				return $http({
				method:'POST',
				url:APIURL+'/tme_services/categoryInfo/getPopularCat',
				data : {
					parentid : parentid,
					data_city: DATACITY,
					category_type:category_type,
					stp :stp,
					ntp:ntp,
                    ucode:USERID
					
				}
			});
		}	
		
		APIService.knowledgemedia	=	function(pagevalue) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/getalldata/',
				data : {
					userid 		: USERID,
					data_city:  DATACITY,
					pagevalue	:pagevalue,
					limit		: 20
				}
			});
		}
		
				APIService.fetchall_tmegenio	=	function(page,searchpara,search_title,location,emptype,teamtype){
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/knowledge/getalldata',
				data	:	{
								page			:	page,
								searchpara		:	searchpara,
								search_title	:	search_title,
								location		:	location,
								emptype			:	emptype,
								userid			:	USERID,
								data_city		:  DATACITY,
								teamtype        :  teamtype
								
				
				}
			});
		}
		APIService.fetchall_tmegenio_mandatory	=	function(searchpara,search_title,location,emptype,mediaid,teamtype){
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/knowledge/getalldata_mandatory',
				data	:	{
								searchpara		:	searchpara,
								search_title	:	search_title,
								location		:	location,
								emptype			:	emptype,
								userid			:	USERID,
								data_city		:  DATACITY,
								mediaid			:	mediaid,
								teamtype        :   teamtype
				
				}
			});
		}
		APIService.fetchall_tmegenio_mandatory_popup	=	function(teamtype){
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/knowledge/getalldata_mandatory_popup',
				data	:	{
								userid			:	USERID,
								data_city		:  DATACITY,
								teamtype 		:   teamtype
				
				}
			});
		}
		APIService.teamtype	=	function() {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/knowledge/teamtype/',
				data : {
					empcode 		: USERID,
					data_city:  DATACITY
				}
			});
		}
		APIService.fetchall_tmegenio_autosuggest	=	function(teamtype,searchpara,emptype,location,term){
			return $http({
				method	:	'POST',
				url		:	APIURL+'/tme_services/knowledge/fetchall_autosuggest_tmegenio',
				data	:	{
								searchpara		:	searchpara,
								location		:	location,
								emptype			:	emptype,
								userid			:	USERID,
								term			:	term,
								teamtype        :   teamtype,
								data_city:  DATACITY
				
				}
			});
		}
			
		APIService.getLineage	=	function() { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/getLineage/',
				data : {
					empcode		:	USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		
		
		
		APIService.getcitylist	=	function(srchData) {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/getcitylist/',
				data : {
					srchData	:	srchData,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.insertlineageDetails	=	function(managername,managercode,city,mobile_num,field,otp,citytype,teamname,confirmed,status) { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/insertlineageDetails/',
				data : {
					reporting_head_name :	managername, 
					reporting_head_code	:	managercode,
					empcode				:	USERID,
					empname				:	UNAME,
					city				:	city,
					off_sales			:	field,
					mobile_num			:	mobile_num,
					otp					:	otp,
					confirmed			:	confirmed,
					status				:	status,
					city_type			:	citytype,
					teamname            :   teamname,
					data_city			:	DATACITY
				}
			});
		}
		
		APIService.fetchreportees	=	function() {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/fetchreportees/',
				data : {
					empcode		:	USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.accetRejectRequest	=	function(reportee,status,confirmed) { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/accetRejectRequest/',
				data : {
					reportee			:	reportee,
					status				:	status,
					confirmed			:	confirmed,
					reporting_head_code	:	USERID,
					empcode				:	USERID,
					reporting_head_name	:	UNAME,
					data_city			:	DATACITY
				}
			});
		}
		APIService.checkUpdatedOn	=	function() {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/checkUpdatedOn/',
				data : {
					empcode		: 	USERID,
					data_city	:	DATACITY
				}
			});
		}
			
		APIService.insertReportDetails	=	function(manager) { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/insertReportDetails/',
				data : {
					data_city	:	DATACITY,
					empname		:	UNAME,
					empcode		:	USERID
				}
			});
		}
		
		APIService.insertPenDate	=	function() {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/insertPenaltyUpdatedOn/',
				data : {
					empcode		:	USERID,
					data_city	:	DATACITY
				}
			});
		}
		
		APIService.sendOTP	=	function(mobno,manager,otp) { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/sendOTP/',
				data : {
					mobno		: mobno,
					empcode		: USERID,
					otp			: otp,
					managercode	: manager,
					data_city	:	DATACITY
				}
			});
			}
			
			APIService.checkOTP	=	function(mobno,manager) { //API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/checkOTP/',
				data : {
					mobno		: mobno,
					empcode		: USERID,
					managercode	: manager,
					data_city	:	DATACITY
				}
			});
			}
			
			APIService.countRequest	=	function() {	//API
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmenewInfo/countRequest/',
				data : {
					empcode				: USERID,
					data_city			: DATACITY
				}
			});
			}
		APIService.ctiCallfunction	=	function(empcode,parentid) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/call_cticlicktocall/',
				data : {
					parentid :  parentid,
					empcode	 :	empcode,
					data_city:  DATACITY,
					usercode :	USERID,
					station_id:  STATID,
					login_city:  LOGIN_CITY
				}
			});
		}
		
		APIService.getStateListings = function(parentid) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/getStateListings/',
            data : {
                    parentid        :   parentid,
                    data_city       :   DATACITY,
                    usercode		:	USERID
            }
          });
		}
		
		APIService.getShadowTabData = function(parentid) {
			var canceller = $q.defer();
            var cancel = function(reason) {
              canceller.resolve(reason);
            };
           
            var promise = $http({
				method: 'POST',
				url: APIURL+'/tme_services/contractInfo/getShadowTabData/'+parentid,
				data : {
					contractid  : parentid,
					city        : DATACITY,
					data_city	: DATACITY
				},
			  }).then(function(response) {
						return response.data;
				  });
            return {
              promise: promise,
              cancel: cancel
            };

        }
		
		 APIService.checkmulticity = function(parentid,catidlineage_nonpaid) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/checkmulticity/',
            data : {
                    parentid        :   parentid,
                    data_city       :   DATACITY,
                    catidlineage_nonpaid : catidlineage_nonpaid,
                    usercode		:	USERID
            }
          });
		}
		
		APIService.saveNationallistingData = function(parentid,citystr,latitude,longitude,type) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/saveNationallistingData/',
            data : {
                    parentid        :   parentid,
                    data_city       :   DATACITY,
                    citystr         :   citystr,
                    latitude        :   latitude,
                    longitude       :   longitude,
                    type            :   type,
                    usercode		:	USERID
            }
          });
		}
		APIService.insertLocalListingval = function(parentid,sphinxid) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/insertLocalListingval/',
            data : {
                    parentid        :   parentid,
                    data_city       :   DATACITY,
                    sphinxid        :   sphinxid,
                    usercode		:	USERID
            }
          });
		}
		
		APIService.bformvalidation = function(parentid){
            return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/bformvalidation/',
                data : {
                    parentid                :   parentid,
                    ucode                   :   USERID,
                    data_city               :   DATACITY
                }
            });
		}
		
		APIService.getTMECallLogs	=	function(data) { // Pending
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/tmeInfo/getTMECallLogs/',
				data : data
			});
		}

		APIService.savedetails	=	function(mobile,email,contact_person,parentid,version,mob_disp,mob_feed,email_disp,email_feed) {
			return $http({
				method:'POST',
				url:APIURL+'/tme_services/contractInfo/savedetails/',
				data : {
					email	:	email,
					mobile	:	mobile,
					contact_person	:	contact_person,
					version		:	version,
					parentid	:	parentid,
					ucode	:	USERID,
					data_city	:	DATACITY,
					mob_disp :mob_disp,
					mob_feed:mob_feed,
					email_disp:email_disp,
					email_feed:email_feed     
				}
			});
		}
		
		 APIService.omnicatlog  =   function(parentid,campainid,campainname,type,payment_type) {
			 return $http({
                method:'POST',
                url:APIURL+'/tme_services/contractInfo/omnicatlog/',
                data : {
                    parentid:parentid,
                    campainid:campainid,
                    campainname:campainname,
                    type:type,
                    payment_type:payment_type,
                    data_city:  DATACITY
                }
            });
		}
		//Discount report service
                APIService.getBudgetService = function($status) {
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/getBudgetService/',
                data : {
                    empId       :   USERID,
                    data_city   :   DATACITY,
                    status      :   $status
                }
            });
        }
                
                APIService.getCollectData = function(data){ // Pending

			return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/getCollectData/',
                data : data
            });
	}
                APIService.updtLogoutTime = function(data){ // Pending

			return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/updtLogoutTime/',
                data : data
            });
	}
		
		APIService.docVerticalCheck  =   function(parentid) {
			 return $http({
                method:'POST',
                url:APIURL+'/tme_services/categoryInfo/docVerticalCheck/',
                data : {
                    parentid	:	parentid,
                    data_city	:	DATACITY,
                    ucode		:   USERID
                }
            });
		}
		
        APIService.checkDiscount = function(parentid,version) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/checkDiscount/',
            data : {
                    parentid        :   parentid,
                    data_city       :   DATACITY,
                    version         :   version
            }
          });
        }
        
         APIService.updateBudgetService = function($id) {
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/updateBudgetService/',
                data : {
                    empId       :   USERID,
                    data_city   :   DATACITY,
                    id          :   $id
                }
            });
        }
		//Discount report service
		
		//~ APIService.check_omni_eligible = function(){
			 //~ return $http({
                //~ method:'POST',
                //~ url:APIURL+'/tme_services/contractInfo/check_omni_eligible',
                //~ data : {
                    //~ empcode:USERID
                //~ }
            //~ });
		//~ }
		
		APIService.getmaincampaignids= function(parentid,version){
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/contractInfo/getmaincampaignid/',
                data : {
                    version : version,
                    parentid : parentid,
                },
            });
        }
        
        APIService.checkMinPackageBudget= function(parentid){
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/campaignInfo/getMinimumBudgetFlexi/',
                data : {
                    parentid : parentid,
                    data_city: DATACITY
                },
            });
        }
        /////////////////////////SAVE AS FREELISTING/////////////////////////
       
         APIService.saveLatLngTempdata= function(parentid,lat,lng,dcflag){
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/saveLatLngTempdata/',
                data : {
                    data_city : DATACITY,
                    parentid : parentid,
                    usercode : USERID,
                    username:UNAME,
                    lat :lat,
                    module:'tme',
                    lng : lng,
                    dcflag:dcflag
                },
            });
        }
         APIService.updMnTabSaveAsNonPaid= function(parentid,exitFromPage,landline){
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/updMnTabSaveAsNonPaid/',
                data : {
                    data_city : DATACITY,
                    parentid : parentid,
                    usercode : USERID,
                    username:UNAME,
                    landline :landline,
                    module:'tme',
                    action : exitFromPage,
                    me_jda_flag : 5
                },
            });
        }
         APIService.insertSaveLogs= function(parentid,resp171,web_resp,jda_resp){
            return $http({
                method:'POST',
                url : APIURL+'/tme_services/tmeInfo/insertSaveLogs/',
                data : {
                    parentid : parentid,
                    usercode : USERID,
                    resp171  :resp171,
                    web_resp :web_resp,
                    jda_resp :jda_resp
                },
            });
        }
        APIService.sendVerificationCode= function(mobile,email,compname,parentid){
            return $http({
                method:'POST',
                url : APIURL+'/00_Payment_Rework/sendVerificationCode.php',
                data : {
                    city 	 : DATACITY,
                    compname : compname,
                    mob  	 : mobile+'~'+email,
                    email 	 : email,
                    parentid : parentid,
                    fromJdVerified : '1',
                    mode     :   'send'
                },
            });
        }
        APIService.verifyVerificationCode= function(mobile,code,parentid){
            return $http({
                method:'POST',
                url : APIURL+'/00_Payment_Rework/sendVerificationCode.php',
                data : {
                    mob  : mobile,
                    validaioncode : code,
                    parentid : parentid,
                    fromJdVerified : '1',
                    mode    :   'validate'
                },
            });
        }
        /////////////////////////SAVE AS FREELISTING/////////////////////////
        
        
         APIService.submit_flexi_value = function(parentid,username,price_arr) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/campaignInfo/submit_flexi_value/',
            data : {
				parentid : parentid,
				data_city   : DATACITY,
				empcode : USERID,
				username:username,
				price_arr:price_arr
			}
          });
        }

	APIService.set_pack_emi = function(parentid,companyname,version,selected_emi,budget_multiplier,campaign) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/set_pack_emi/',
            data : {
				parentid : parentid,
				companyname:companyname,
				version   : version,
				selected_emi:selected_emi,
				budget_multiplier:budget_multiplier,
				campaign:campaign,
				data_city:DATACITY,
				empcode:USERID
			}
          });
        }
        
        APIService.get_pack_emi = function(parentid,version) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/get_pack_emi/',
            data : {
				parentid : parentid,
				version   : version,
				data_city	:	DATACITY,
				empcode	:	USERID
			}
          });
        }
        
        APIService.check_existing_budget = function(parentid,payment_type) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/contractInfo/check_existing_budget/',
            data : {
				parentid : parentid,
				data_city   : DATACITY,
				empcode : USERID,
				payment_type:payment_type
			}
          });
        }
        
        APIService.getbypassdet = function(empcode) {
          return $http({
            method: 'POST',
            url: APIURL+'/tme_services/bformInfo/getbypassdet/',
            data : {
				data_city   : DATACITY,
				ucode : USERID,
				uname : UNAME
			}
          });
        }
		
		return APIService;
	});

	tmeModuleApp.factory('Paths', function($location) {
		return {
			appname: "vfdfvdfv" ,
			appurl: $location.url(),
			apppath: $location.path(),
			applocation: $location
		};
	});
});
