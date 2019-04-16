<?php
$db = array();

##	PRODUCTION DATABASE SETTINGS	##
$db['db_local']         	= 	array(DB_HOST_LOC, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_tme']           	= 	array(DB_HOST_LOC, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_iro']           	= 	array(DB_HOST_LOC, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_alloc']           	= 	array(DB_HOST_LOC, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_local_slave']       = 	array(DB_HOST_LOC_SLAVE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_tme_slave']         = 	array(DB_HOST_LOC_SLAVE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_iro_slave']         = 	array(DB_HOST_LOC_SLAVE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_alloc_slave']       = 	array(DB_HOST_LOC_SLAVE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_finance']       	= 	array(DB_HOST_FIN, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave']     = 	array(DB_HOST_FIN_SLAVE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_budget']    = 	array(DB_HOST_FIN_BUDGET, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_sms']           	= 	array(DB_HOST_SMS, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_idc']           	= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_LOCAL);
$db['db_idc_online']        = 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_ONLINE);
$db['db_idc_dialer']        = 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_DIALER);
$db['db_idc_login']        	= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_LOGIN);
$db['db_national']        	= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_NATIONAL);
$db['db_data_correction']   = 	array(DB_HOST_DATA_CORRECTION, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);


## City Wise Constant

$db['db_local_mumbai']           		= 	array(DB_HOST_LOC_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_delhi']           		= 	array(DB_HOST_LOC_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_kolkata']          		= 	array(DB_HOST_LOC_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_bangalore']        		= 	array(DB_HOST_LOC_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_chennai']          		= 	array(DB_HOST_LOC_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_pune']           			= 	array(DB_HOST_LOC_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_hyderabad']        		= 	array(DB_HOST_LOC_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_ahmedabad']        		= 	array(DB_HOST_LOC_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_remote']    				= 	array(DB_HOST_LOC_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);

$db['db_tme_mumbai']           			= 	array(DB_HOST_LOC_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_delhi']           			= 	array(DB_HOST_LOC_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_kolkata']          			= 	array(DB_HOST_LOC_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_bangalore']        			= 	array(DB_HOST_LOC_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_chennai']          			= 	array(DB_HOST_LOC_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_pune']           			= 	array(DB_HOST_LOC_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_hyderabad']        			= 	array(DB_HOST_LOC_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_ahmedabad']        			= 	array(DB_HOST_LOC_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_remote']    				= 	array(DB_HOST_LOC_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);

$db['db_iro_mumbai']           			= 	array(DB_HOST_LOC_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_delhi']           			= 	array(DB_HOST_LOC_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_kolkata']          			= 	array(DB_HOST_LOC_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_bangalore']        			= 	array(DB_HOST_LOC_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_chennai']          			= 	array(DB_HOST_LOC_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_pune']           			= 	array(DB_HOST_LOC_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_hyderabad']        			= 	array(DB_HOST_LOC_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_ahmedabad']        			= 	array(DB_HOST_LOC_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_remote']    				= 	array(DB_HOST_LOC_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);

$db['db_alloc_mumbai']           		= 	array(DB_HOST_LOC_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_delhi']           		= 	array(DB_HOST_LOC_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_kolkata']          		= 	array(DB_HOST_LOC_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_bangalore']        		= 	array(DB_HOST_LOC_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_chennai']          		= 	array(DB_HOST_LOC_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_pune']           			= 	array(DB_HOST_LOC_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_hyderabad']        		= 	array(DB_HOST_LOC_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_ahmedabad']        		= 	array(DB_HOST_LOC_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_remote']    				= 	array(DB_HOST_LOC_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);

$db['db_data_correction_mumbai']        = 	array(DB_HOST_DATA_CORRECTION_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_delhi']         = 	array(DB_HOST_DATA_CORRECTION_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_kolkata']       = 	array(DB_HOST_DATA_CORRECTION_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_bangalore']     = 	array(DB_HOST_DATA_CORRECTION_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_chennai']       = 	array(DB_HOST_DATA_CORRECTION_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_pune']          = 	array(DB_HOST_DATA_CORRECTION_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_hyderabad']     = 	array(DB_HOST_DATA_CORRECTION_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_ahmedabad']     = 	array(DB_HOST_DATA_CORRECTION_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);
$db['db_data_correction_remote']    	= 	array(DB_HOST_DATA_CORRECTION_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_DATA_CORRECTION);

$db['db_finance_mumbai']           		= 	array(DB_HOST_FIN_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_delhi']           		= 	array(DB_HOST_FIN_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_kolkata']          		= 	array(DB_HOST_FIN_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_bangalore']        		= 	array(DB_HOST_FIN_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_chennai']          		= 	array(DB_HOST_FIN_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_pune']           		= 	array(DB_HOST_FIN_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_hyderabad']        		= 	array(DB_HOST_FIN_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_ahmedabad']        		= 	array(DB_HOST_FIN_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_remote']    			= 	array(DB_HOST_FIN_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);


$db['db_finance_budget_mumbai']         = 	array(DB_HOST_FIN_BUDGET_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_delhi']          = 	array(DB_HOST_FIN_BUDGET_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_kolkata']        = 	array(DB_HOST_FIN_BUDGET_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_bangalore']      = 	array(DB_HOST_FIN_BUDGET_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_chennai']        = 	array(DB_HOST_FIN_BUDGET_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_pune']          	= 	array(DB_HOST_FIN_BUDGET_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_hyderabad']      = 	array(DB_HOST_FIN_BUDGET_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_ahmedabad']      = 	array(DB_HOST_FIN_BUDGET_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);
$db['db_finance_budget_remote']    		= 	array(DB_HOST_FIN_BUDGET_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN_BUDGET);

$db['db_sms_mumbai']           			= 	array(DB_HOST_SMS_MUMBAI, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_delhi']           			= 	array(DB_HOST_SMS_DELHI, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_kolkata']          			= 	array(DB_HOST_SMS_KOLKATA, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_bangalore']        			= 	array(DB_HOST_SMS_BANGALORE, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_chennai']          			= 	array(DB_HOST_SMS_CHENNAI, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_pune']           			= 	array(DB_HOST_SMS_PUNE, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_hyderabad']        			= 	array(DB_HOST_SMS_HYDERABAD, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_ahmedabad']        			= 	array(DB_HOST_SMS_AHMEDABAD, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);
$db['db_sms_remote']    				= 	array(DB_HOST_SMS_REMOTE, DB_USER_SMS, DB_PASS_SMS, DB_NAME_SMS);

$db['db_idc_mumbai']           			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_MUMBAI);
$db['db_idc_delhi']           			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_DELHI);
$db['db_idc_kolkata']          			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_KOLKATA);
$db['db_idc_bangalore']        			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_BANGALORE);
$db['db_idc_chennai']          			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_CHENNAI);
$db['db_idc_pune']           			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_PUNE);
$db['db_idc_hyderabad']        			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_HYDERABAD);
$db['db_idc_ahmedabad']        			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_AHMEDABAD);
$db['db_idc_remote_cities']    			= 	array(DB_HOST_IDC, DB_USER_IDC, DB_PASS_IDC, DB_NAME_IDC_REMOTE);

$db['db_local_slave_mumbai']           	= 	array(DB_HOST_LOC_SLAVE_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_delhi']           	= 	array(DB_HOST_LOC_SLAVE_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_kolkata']          	= 	array(DB_HOST_LOC_SLAVE_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_bangalore']        	= 	array(DB_HOST_LOC_SLAVE_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_chennai']          	= 	array(DB_HOST_LOC_SLAVE_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_pune']           	= 	array(DB_HOST_LOC_SLAVE_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_hyderabad']        	= 	array(DB_HOST_LOC_SLAVE_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_ahmedabad']        	= 	array(DB_HOST_LOC_SLAVE_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);
$db['db_local_slave_remote']    		= 	array(DB_HOST_LOC_SLAVE_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOC);

$db['db_tme_slave_mumbai']           	= 	array(DB_HOST_LOC_SLAVE_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_delhi']           	= 	array(DB_HOST_LOC_SLAVE_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_kolkata']          	= 	array(DB_HOST_LOC_SLAVE_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_bangalore']        	= 	array(DB_HOST_LOC_SLAVE_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_chennai']          	= 	array(DB_HOST_LOC_SLAVE_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_pune']           		= 	array(DB_HOST_LOC_SLAVE_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_hyderabad']        	= 	array(DB_HOST_LOC_SLAVE_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_ahmedabad']        	= 	array(DB_HOST_LOC_SLAVE_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);
$db['db_tme_slave_remote']    			= 	array(DB_HOST_LOC_SLAVE_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_TME);

$db['db_iro_slave_mumbai']           	= 	array(DB_HOST_LOC_SLAVE_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_delhi']           	= 	array(DB_HOST_LOC_SLAVE_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_kolkata']          	= 	array(DB_HOST_LOC_SLAVE_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_bangalore']        	= 	array(DB_HOST_LOC_SLAVE_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_chennai']          	= 	array(DB_HOST_LOC_SLAVE_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_pune']           		= 	array(DB_HOST_LOC_SLAVE_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_hyderabad']        	= 	array(DB_HOST_LOC_SLAVE_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_ahmedabad']        	= 	array(DB_HOST_LOC_SLAVE_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);
$db['db_iro_slave_remote']    			= 	array(DB_HOST_LOC_SLAVE_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_IRO);

$db['db_alloc_slave_mumbai']           	= 	array(DB_HOST_LOC_SLAVE_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_delhi']           	= 	array(DB_HOST_LOC_SLAVE_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_kolkata']          	= 	array(DB_HOST_LOC_SLAVE_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_bangalore']        	= 	array(DB_HOST_LOC_SLAVE_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_chennai']          	= 	array(DB_HOST_LOC_SLAVE_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_pune']           	= 	array(DB_HOST_LOC_SLAVE_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_hyderabad']        	= 	array(DB_HOST_LOC_SLAVE_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_ahmedabad']        	= 	array(DB_HOST_LOC_SLAVE_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);
$db['db_alloc_slave_remote']    		= 	array(DB_HOST_LOC_SLAVE_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_ALLOC);


$db['db_finance_slave_mumbai']          = 	array(DB_HOST_FIN_SLAVE_MUMBAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_delhi']           = 	array(DB_HOST_FIN_SLAVE_DELHI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_kolkata']         = 	array(DB_HOST_FIN_SLAVE_KOLKATA, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_bangalore']       = 	array(DB_HOST_FIN_SLAVE_BANGALORE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_chennai']         = 	array(DB_HOST_FIN_SLAVE_CHENNAI, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_pune']           	= 	array(DB_HOST_FIN_SLAVE_PUNE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_hyderabad']       = 	array(DB_HOST_FIN_SLAVE_HYDERABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_ahmedabad']       = 	array(DB_HOST_FIN_SLAVE_AHMEDABAD, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
$db['db_finance_slave_remote']    		= 	array(DB_HOST_FIN_SLAVE_REMOTE, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_FIN);
