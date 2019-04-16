<?php
set_time_limit(0);
ini_set('memory_limit','1024M');


if(!isset($_SERVER['HTTP_HOST'])  && count($_SERVER['argv'])>0)
{

if(strpos(trim($_SERVER['PATH_TRANSLATED']), 'production/dataentry_pincodewise/') !== false)
      {
             define("LOCAL_SERVER_PATH","/var/www/production/dataentry_pincodewise/");/*nginx  CS LIVE */
      }else{

	     define('LOCAL_SERVER_PATH' , '/httpdjail/var/www/html/dataentry_pincodewise/');/*apache CS LIVE*/
	}
}
else
{
    define('LOCAL_SERVER_PATH' , '../');
} 
date_default_timezone_set('Asia/Calcutta');
require_once(LOCAL_SERVER_PATH."library/config.php");
require_once(APP_PATH."library/path.php");	

$arg_count	= count($_SERVER['argv']);
$filename	= $_SERVER['argv'][$arg_count-1];

switch($filename)
{
    /*case 'update_history_table':
        include('process_update_history.php');
		break;
    case 'jdgv_update_table':
        include('process_jdgv_update_table.php');
		break;
    case 'process_correct_virtualnumber_linkedcontract':
        include_once("process_allocate_deallocate_virtualnumber_linkedcontract.php");
		break;  
	*/  
	case 'process_catidlineage_search':
		include_once("process_catidlineage_search.php");
		break;
    case 'process_correct_virtualnumber':
        include_once("process_allocate_deallocate_virtualnumber.php");
    break;        
    case 'category_over_booking':
		include_once(APP_PATH. "cron/process_category_over_booking.php");
		break;
	/*case 'update_paid_flag':
		include_once("process_update_paid_flag.php");
		break;*/
    case 'c2s_cron':
		include_once(APP_PATH. "cron/c2s_cron.php");
		break;
    case 'statement_dump':
		include_once(APP_PATH. "cron/cron_stmt_dump.php");
		break;
    case 'statement_genaration':
		include_once(APP_PATH. "cron/cron_stmt_generation.php");
		break;
    case 'statement_send_mailsms':
		include_once(APP_PATH. "cron/cron_send_sms.php");
		break;
    case 'vlc_report_cron':
		include_once(APP_PATH. "cron/cron_vlc_new.php");
		break;
    case 'web_promo_cron':
		include_once(APP_PATH. "cron/cron_web_promo.php");
		break;
    case 'daemon_expiry_cron':
		include_once(APP_PATH. "cron/daemon_expiry_csgenio.php");
		break;
	case 'daemon_expiry_cron_national':
		include_once(APP_PATH. "cron/daemon_expiry_national.php");
		break;
    case 'expired_virtual_number_cron':
		include_once(APP_PATH. "cron/expire_contract_release_virtual.php");
		break;
    case 'national_catid_cron':
		include_once(APP_PATH. "cron/nationalcatidCron.php");
		break;
    case 'vlc_update_cron':
		include_once(APP_PATH. "cron/VIdeoWebSite.php");
		break;
	/*case 'process_reseller_category_update':
		include_once("process_reseller_category_update.php");
		break;*/
    case 'process_correct_enhancement_package':
        include_once(APP_PATH. "cron/process_correct_enhancement_package.php");
        break;
    case 'contract_conversion_process':
        include_once(APP_PATH. "cron/contract_conversion_process.php");
        break;
    case 'process_update_single_mapped_number':
        include_once("process_update_notlink_mappednumber.php");
        break;
	case 'compcatarea_regen':
		include_once(APP_PATH. "library/compcatarea_regen_wrapper.php");
		break;
	case 'MIS_DATA_PROCESS':
		include_once(APP_PATH."reports/MISreport.php");
		break;
	case 'NEWCALLER_DATA_PROCESS':
		include_once(APP_PATH."reports/newcallerReport.php");
		break;	
	case 'MIS_DATA_PROCESS_TEST':
		include_once(APP_PATH."reports/MISreport_test.php");
		break;
	case 'NEWCALLER_DATA_PROCESS_TEST':
		include_once(APP_PATH."reports/newcallertest.php");
		break;		
    case 'process_update_compname_search_singular':
        include_once(APP_PATH."cron/process_update_compname_search_singular.php");
        break;
    case 'update_dnc_flag_virtualnumber':
        include_once(APP_PATH. "cron/process_DncFlagSetter.php");
        break;
	case 'unapprovedcontractdatapopulation':
        include_once(APP_PATH. "library/unapprovedcontractdatapopulation.php");
        break;
	case 'autosuggest_wraper':
        include_once(APP_PATH. "library/autosuggest_wraper.php");
        break;
	case 'backend_data_population_process':
        include_once(APP_PATH. "library/backend_data_population_process.php");
        break;
	case 'Process_update_servicename_autoapproval_affected_cases':
        include_once(APP_PATH. "cron/process_updating_servicesname_vlc.php");
        break;
	case 'process_assign_virtualnumber':
        include_once(APP_PATH. "cron/process_assign_virtualnumber.php");
        break;
	case 'process_update_notconnectflag':
        include_once(APP_PATH. "cron/process_notConnectedNumbers.php");
        break;
    case 'process_update_servicename':
        include_once(APP_PATH. "cron/process_update_servicename.php");
        break;
    case 'process_duplicate_vn':
        include_once(APP_PATH. "cron/process_resolved_duplicate_vn.php");
        break;
	case 'process_unallocated_vncount':
        include_once(APP_PATH. "cron/process_unallocated_vncount.php");
        break;
	case 'process_duplicate_vnlist':
        include_once(APP_PATH. "cron/process_duplicate_vnlist.php");
        break;
    case 'process_assign_remote_jaipur_vn':
        include_once(APP_PATH. "cron/process_assign_remote_jaipur_vn.php");
        break;
    case 'update_fbemail':
        include_once(APP_PATH. "cron/update_fbemail.php");
        break;
	 case 'process_update_mapdetails':
        include_once(APP_PATH. "cron/process_update_mapdetails.php");
        break;
	case 'process_assign_vno_affectedcase':
        include_once(APP_PATH. "cron/process_assign_vno_affectedcase.php");
        break;
	case 'process_update_catspon':
        include_once(APP_PATH. "cron/update_catspon_dealclose.php");
        break;
case 'dbbackend_data_population':
        include_once(APP_PATH. "cron/backendupdateCron.php");
        break;
   case 'populate_search_table':
        include_once(APP_PATH. "cron/populatesearchtable.php");
        break;
	case 'bidperday_update':
        include_once(APP_PATH. "cron/process_supreme_bidperday.php");
        break;
    case 'bidperday_update_mix':
        include_once(APP_PATH. "cron/process_mix_bidperday.php");
        break;
	case 'cs_pending_data':
        include_once(APP_PATH. "cron/cs_pending.php");
        break;
	case 'quarantine_process':
        include_once(APP_PATH. "cron/process_virtualnumber_remove_quarantine.php");
        break;
    case 'dbbackend_cat_add':
        include_once(APP_PATH. "cron/backendCatUpdateCron.php");
        break;
	case 'inventory_catspon':
		include_once(APP_PATH."cron/process_inventory_update_catspon.php");
		break;
	case 'updatesupreme':
		include_once(APP_PATH."cron/process_updatespreme.php");
		break;
	case 'restaurant_attr_removal':
        include_once(APP_PATH. "cron/oldattributes_removal.php");
		break;
	case 'process_schedule_sms_email':
        include_once(APP_PATH. "cron/process_schedule_sms_email.php");
		break;
	case 'process_active_deactive_contracts':
        include_once(APP_PATH. "cron/process_active_deactive_contracts.php");
		break;
	case 'process_affected_vn':
        include_once(APP_PATH. "cron/affected_vn_process.php");
		break;
	case 'process_vncron':
        include_once(APP_PATH. "cron/vnCron.php");
		break;
		
	case 'process_assignvn':
		include_once(APP_PATH. "cron/assignVn.php");
		break;
	case 'process_update_vninventory':
		include_once(APP_PATH. "cron/updateVnInventory.php");
		break;
	case 'process_update_paidstatus':
		include_once(APP_PATH. "cron/process_update_paidstatusFlag.php");
		break;
	case 'process_assignvnjdfos':
		include_once(APP_PATH. "cron/process_assignVnJdfos.php");
		break;
	case 'process_updatecompanyname':
		include_once(APP_PATH. "cron/process_updateCompanyName.php");
		break;
	case 'process_update_vrnstatus':
		include_once(APP_PATH. "cron/process_update_vrnstatus.php");
		break;
	case 'process_update_paidstatus_remote': 
		include_once(APP_PATH. "cron/process_update_paidstatusFlag_remotecity.php");
		break;
	case 'process_remove_vn_series': 
		include_once(APP_PATH. "cron/processRemoveVNSeries.php");
		break;
	case 'process_assign_ccr_approved_category':
		include_once(APP_PATH. "cron/process_assign_ccr_approved_category.php");
		break;
	case 'process_update_hosp_type_flag':
		include_once(APP_PATH. "cron/process_update_hosp_type_flag.php");
		break;
	case 'process_insert_doctor_source':
		include_once(APP_PATH. "cron/process_insert_doctor_source.php");
		break;
	case 'process_insert_vendorid':
		include_once(APP_PATH. "cron/processUpdateIdcvendor_upload_details.php");
		break;
	case 'cron_vendor_approve':
		include_once(APP_PATH. "cron/cron_update_vendor_data.php");
		break;
	case 'cron_expiredvn_qauarantine':
		include_once(APP_PATH. "cron/cron_expirevirtualnumber_quarantine.php");
		break;
	case 'process_expiredvn_quarantine_link':
		include_once(APP_PATH. "cron/process_expiredvn_quarantine_link.php");
		break;
	case 'process_expiredvn_unsuccess_contract':
		include_once(APP_PATH. "cron/process_expiredvn_unsuccess_contract.php");
		break;
	case 'process_remove_expiredvn_quarantine':
		include_once(APP_PATH. "cron/process_remove_expiredvn_quarantine.php");
		break;
	case 'process_fresh_vn_allocation_chandigarh':
		include_once(APP_PATH. "cron/process_fresh_vn_allocation_chandigarh.php");
		break;
	case 'process_fresh_vn_allocation_jaipur':
		include_once(APP_PATH. "cron/process_fresh_vn_allocation_jaipur.php");
		break;
	case 'process_fresh_vn_allocation_coimbatore':
		include_once(APP_PATH. "cron/process_fresh_vn_allocation_coimbatore.php");
		break;
	case 'process_generateddg':
		include_once(APP_PATH. "cron/process_generatebidacatdetailsddg.php");
		break;
	case 'process_affected_restaurant_contracts':
		include_once(APP_PATH. "cron/process_affected_restaurant_contracts.php");
		break;
	case 'process_schedule_brandmark_alert':
		include_once(APP_PATH. "cron/process_schedule_brandmark_alert.php");
		break;
	case 'process_call_genio_vn':
		include_once(APP_PATH. "cron/process_call_genio_vn.php");
		break;
	case 'process_updatefeedback_mobile':
		include_once(APP_PATH. "cron/processUpdateFbVNShop.php");
		break;
	case 'process_updatefeedback_mobile_remotecity':
		include_once(APP_PATH. "cron/processUpdateFbVNShop_remotcity.php");
		break;
	case 'process_doc_hosp_contracts':
		include_once(APP_PATH. "cron/process_doc_hosp_contracts.php");
		break;
	case 'process_resolve_ccr_affected_cases':
		include_once(APP_PATH. "cron/process_resolve_ccr_affected_cases.php");
		break;
	case 'process_block_for_vn_check':
		include_once(APP_PATH. "cron/process_block_for_vn_check.php");
		break;
	case 'process_contract_merging_vn_update':
		include_once(APP_PATH. "cron/process_contract_merging_vn_update.php");
		break;
	case 'process_assign_rcom_vn':
		include_once(APP_PATH. "cron/process_assign_rcom_vn.php");
		break;
	case 'process_affecte_rcom_vn':
		include_once(APP_PATH. "cron/process_fixahmContract.php");
		break;
	case 'process_block_unblock_contact_numbers':
		include_once(APP_PATH. "cron/process_block_unblock_contact_numbers.php");
		break;		
	case 'octdatacorrection':
		include_once(APP_PATH. "ProcessDisp/restcategoriesProcess.php");
		break;
	case 'octdatacorrectionautomation':
		include_once(APP_PATH. "ProcessDisp/restcategoriesProcess_dndeal.php");
		break;
	case 'process_update_vertical_activation_status':
		include_once(APP_PATH. "cron/process_update_vertical_activation_status.php");
		break;
	case 'process_update_brand_paid_status':
		include_once(APP_PATH. "cron/process_update_brand_paid_status.php");
		break;
	case 'process_freeze_closedown_contracts':
		include_once(APP_PATH. "cron/process_freeze_closedown_contracts.php");
		break;
	case 'process_cs_url_identifier':
		include_once(APP_PATH. "cron/process_cs_url_identifier.php");
		break;
	case 'process_companyname_standardization':
		include_once(APP_PATH. "cron/CompanyNameStandardization_India.php");
		break;
	case 'process_corporate_feedback':
		include_once(APP_PATH. "cron/corporate_search_feedback_details.php");
		break;
	case 'process_opening_shortly_companies':
		include_once(APP_PATH. "cron/process_opening_shortly_companies.php");
		break;
	case 'deallocating_virtualNo':
		include_once(APP_PATH. "cron/expire_table_data.php");
		break;
	case 'pendingdb_process':
		include_once(APP_PATH. "cron/pendingdb.php");
		break;
	case 'process_premium_category_affected_contracts' :
		include_once(APP_PATH. "cron/process_premium_category_affected_contracts.php");
		break;
	case 'process_vertical_categories':
		include_once(APP_PATH. "cron/process_vertical_categories.php");
		break;
	case 'process_verify_jdfos_tr_status' :
		include_once(APP_PATH. "cron/process_verify_jdfos_tr_status.php");
		break;
	case 'process_vertical_activate_deactivate' :
		include_once(APP_PATH."cron/process_vertical_activate_deactivate.php");
		break;
	case 'process_premium_approve_contracts' :
		include_once(APP_PATH."cron/process_premium_approve_contracts.php");
		break;
	case 'process_premium_reject_contracts' :
		include_once(APP_PATH."cron/process_premium_reject_contracts.php");
		break;
	case 'process_premium_paid_approve_contracts' : 		
		include_once(APP_PATH."cron/process_premium_paid_approve_contracts.php");
		break;
	case 'process_vertical_status_mail_sender' : 		
		include_once(APP_PATH."cron/process_vertical_status_mail_sender.php");
		break;	
	case 'eligible_contract_vn' : 		
		include_once(APP_PATH."cron/eligible_contract_vn.php");
		break;		
	case 'process_rcom_block_unblock_number' : 		
		include_once(APP_PATH."cron/process_rcom_block_unblock_number.php");
		break;
	case 'process_owner_freeze_closedown_request' : 		
		include_once(APP_PATH."cron/process_owner_freeze_closedown_request.php");
		break;
	case 'release_unapproved_inventory' :
		include_once(APP_PATH. "cron/release_pdg_overdated_inventory.php");
		break;
	case 'disposition_process' :
		include_once(APP_PATH. "cron/cron_disposition_process.php");
		break;
	case 'process_hotcategory' : 		
		include_once(APP_PATH."cron/process_hotcategory.php");
		break;
	case 'process_update_cityname':
		include_once(APP_PATH."cron/process_update_cityname.php");
		break;
	case 'process_addContact_numbers' :
		include_once(APP_PATH. "cron/process_addContact_numbers.php");
		break;
	case 'doc_contract_cron' :
		include_once(APP_PATH. "cron/doc_contract_cron.php");
		break;
	case 'hosp_contract_cron' :
		include_once(APP_PATH. "cron/hosp_contract_cron.php");
		break;
	case 'send_appt_sms' :
		include_once(APP_PATH. "cron/send_appt_sms.php");
		break;
	case 'movie_release_cron' :
		include_once(APP_PATH. "cron/movie_release_cron.php");
		break;
	case 'process_send_whatsapp_msg' :
		include_once(APP_PATH. "cron/process_send_whatsapp_msg.php");
		break;
	case 'onboarding_approval_mails' :
		include_once(APP_PATH. "cron/onboarding_approval_mails.php");
		break;
	case 'cron_instant_live' :
		include_once(APP_PATH. "cron/cron_instant_live.php");
		break;
	case 'process_doc_attr' :
		include_once(APP_PATH. "cron/process_doc_attr.php");
		break;
	case 'process_premium_bulkdata':
		include_once(APP_PATH. "premium_category/process_premium_bulkdata.php");
		break;	
	case 'dmnpstfinaprvl' :
		include_once(APP_PATH. "cron/daemonpostfinapproval.php");
		break;	
	case 'tme_action_update' :
		include_once(APP_PATH. "cron/process_update_tme_action_flag.php");
		break;
	case 'process_attr_irocard' :
		include_once(APP_PATH. "cron/process_attr_irocard.php");
		break;	
	case 'process_brand_bulkdata' :
		include_once(APP_PATH. "brand_name/process_brand_bulkdata.php");
		break;
	case 'process_format_comp_bulk_data' :
		include_once(APP_PATH. "format_company/process_format_comp_bulk_data.php");
		break;
	case 'process_exclusion_bulkdata' :
		include_once(APP_PATH. "reports/exclusion_upload/process_exclusion_bulkdata.php");
		break;
	case 'cron_catids_update' :
		include_once(APP_PATH. "cron/cron_catids_update.php");
		break;	
	case 'cron_webapis_call' : 
		include_once(APP_PATH. "cron/cron_webapis_call.php");
		break;
	case 'process_nonutf_contracts' : 
		include_once(APP_PATH. "cron/process_nonutf_contracts.php");
		break;
	case 'process_nonutf_contracts_new' : 
		include_once(APP_PATH. "cron/process_nonutf_contracts_new.php");
		break;
	case 'process_hopcrr_file' : 
		include_once(APP_PATH. "cron/process_hopcrr_file.php");
		break;
	case 'process_addbudget' :
		include_once(APP_PATH. "cron/process_addbudget.php");
        break;
    case 'process_updatebudget' :
		include_once(APP_PATH. "cron/process_updatebudget.php");
        break;
    case 'process_notification_mail' :
		include_once(APP_PATH. "cron/process_notification_mail2.php");
        break;
	case 'process_update_business_data' :
		include_once(APP_PATH. "cron/process_update_business_data.php");
        break;
    case 'push_expFail_WebAPI' :
		include_once(APP_PATH. "cron/push_incomplete_expired_data.php");
        break;
    case 'process_doctor_attributes' :
		include_once(APP_PATH. "cron/process_doctor_attributes.php");
		break;
	 case 'process_doctor_attributes_new' :
		include_once(APP_PATH. "cron/process_doctor_attributes_new.php");
		break;
	 case 'process_historylog' :
		include_once(APP_PATH. "cron/process_historylog.php");
		break;
	case 'deallocated_quaran_vn_notconn' :
		include_once(APP_PATH. "cron/quarantine_not_connected.php");
		break;
	case 'process_brand_duplicate' :
		include_once(APP_PATH. "cron/process_brand_dup_data.php");
		break;		    
}
?>
