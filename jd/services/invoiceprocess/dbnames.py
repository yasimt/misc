import socket
ip= socket.gethostbyname(socket.gethostname())
APP_USER="decs_app"
APP_PASS="s@myD#@mnl@sy"
APP_USER_LIVE="jdbox"
APP_PASS_LIVE="jDb0X@#@!"
RESELLER_DEV_USER="application"
RESELLER_LIVE_USER="reseller"
DB_ONLINE1="online_regis1"
UAT_USER="uattesting"
UAT_PASS='U@#t3$T!nG'
WEB_SERVICES_API= "192.168.20.102:9001"
MUMBAI_DULICATE_IP= "172.29.0.141"
DELHI_DULICATE_IP= "172.29.8.141"
KOLKATA_DULICATE_IP= "172.29.16.141"
BANGALORE_DULICATE_IP= "172.29.26.141"
CHENNAI_DULICATE_IP= "172.29.32.141"
PUNE_DULICATE_IP= "172.29.40.141"
HYDERABAD_DULICATE_IP= "172.29.50.141"
AHMEDABAD_DULICATE_IP= "172.29.56.141"
REMOTE_CITIES_DULICATE_IP= "192.168.17.141"
GENIO_URL= "192.168.22.103"
SSO_URL= "192.168.20.237"

REMOTE_DULICATE_IP=REMOTE_CITIES_DULICATE_IP
DB = {}
if(ip=='172.29.64.64' or ip=='172.29.86.27'):
	DB[('mumbai','fin','master')]		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('hyderabad','fin','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('kolkata','fin','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('bangalore','fin','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('chennai','fin','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('delhi','fin','master')] 		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('pune','fin','master')] 		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('ahmedabad','fin','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('remote','fin','master')] 		= {'serverip':'192.168.6.86', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('mumbai','fin','slave')]  		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('hyderabad','fin','slave')]  	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('kolkata','fin','slave')]  	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('bangalore','fin','slave')]  	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('chennai','fin','slave')]  	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('delhi','fin','slave')]  		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('pune','fin','slave')]  		= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('ahmedabad','fin','slave')]  	= {'serverip':'172.29.67.215', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}
	DB[('remote','fin','slave')]  		= {'serverip':'192.168.6.86', 'username':APP_USER, 'password':APP_PASS, 'db':'db_finance'}

	DB[('mumbai','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('hyderabad','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('kolkata','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('bangalore','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('chennai','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('delhi','db_budgeting','master')] 		= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('pune','db_budgeting','master')] 		= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('ahmedabad','db_budgeting','master')] 	= {'serverip':'172.29.67.215', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('remote','db_budgeting','master')] 	= {'serverip':'192.168.6.86', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_budgeting'}
	DB[('mumbai','iro','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('hyderabad','iro','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('kolkata','iro','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('bangalore','iro','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('chennai','iro','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('delhi','iro','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('pune','iro','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('ahmedabad','iro','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('remote','iro','master')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('mumbai','iro','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('hyderabad','iro','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('kolkata','iro','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('bangalore','iro','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('chennai','iro','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('delhi','iro','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('pune','iro','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('ahmedabad','iro','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('remote','iro','slave')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'db_iro'}
	DB[('mumbai','d_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('hyderabad','d_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('kolkata','d_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('bangalore','d_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('chennai','d_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('delhi','d_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('pune','d_jds','master')] 			= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('ahmedabad','d_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('remote','d_jds','master')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}

	DB[('mumbai','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('hyderabad','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('kolkata','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('bangalore','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('chennai','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('delhi','d_jds','slave')] 			= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('pune','d_jds','slave')] 			= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('ahmedabad','d_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}
	DB[('remote','d_jds','slave')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'d_jds'}


	DB[('mumbai','tme_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('hyderabad','tme_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('kolkata','tme_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('bangalore','tme_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('chennai','tme_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('delhi','tme_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('pune','tme_jds','master')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('ahmedabad','tme_jds','master')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('remote','tme_jds','master')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}

	DB[('mumbai','tme_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('hyderabad','tme_jds','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('kolkata','tme_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('bangalore','tme_jds','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('chennai','tme_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('delhi','tme_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('pune','tme_jds','slave')] 		= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('ahmedabad','tme_jds','slave')] 	= {'serverip':'172.29.67.213', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('remote','tme_jds','slave')] 		= {'serverip':'192.168.6.96', 'username':APP_USER, 'password':APP_PASS, 'db':'tme_jds'}
	DB[('mumbai','idc','master')] 		= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_mumbai'}
	DB[('hyderabad','idc','master')] 	= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_hyderabad'}
	DB[('kolkata','idc','master')] 	= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_kolkata'}
	DB[('bangalore','idc','master')] 	= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_bangalore'}
	DB[('chennai','idc','master')] 	= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_chennai'}
	DB[('delhi','idc','master')] 		= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_delhi'}
	DB[('pune','idc','master')] 		= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_pune'}
	DB[('ahmedabad','idc','master')] 	= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_ahmedabad'}
	DB[('remote','idc','master')] 		= {'serverip':'192.168.6.52', 'username':APP_USER, 'password':APP_PASS, 'db':'online_regis_remote_cities'}

	DB[('mumbai','messaging','master')]			= {'serverip':'172.29.0.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('hyderabad','messaging','master')]			= {'serverip':'172.29.50.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('kolkata','messaging','master')]			= {'serverip':'172.29.16.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('bangalore','messaging','master')]			= {'serverip':'172.29.26.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('chennai','messaging','master')]			= {'serverip':'172.29.32.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('delhi','messaging','master')]			    = {'serverip':'172.29.8.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('pune','messaging','master')]			    = {'serverip':'172.29.40.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('ahmedabad','messaging','master')]			= {'serverip':'192.168.35.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('remote','messaging','master')]			= {'serverip':'192.168.6.133', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
else:
	DB[('mumbai','fin','master')]		= {'serverip':'172.29.0.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('mumbai','local','master')]		= {'serverip':'172.29.0.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('mumbai','messaging','master')]			= {'serverip':'172.29.0.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('delhi','fin','master')]		= {'serverip':'172.29.8.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('delhi','local','master')]		= {'serverip':'172.29.8.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('delhi','messaging','master')]			= {'serverip':'172.29.8.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('kolkata','fin','master')]		= {'serverip':'172.29.16.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('kolkata','local','master')]		= {'serverip':'172.29.16.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('kolkata','messaging','master')]			= {'serverip':'172.29.16.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('bangalore','fin','master')]		= {'serverip':'172.29.26.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('bangalore','local','master')]		= {'serverip':'172.29.26.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('bangalore','messaging','master')]			= {'serverip':'172.29.26.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('chennai','fin','master')]		= {'serverip':'172.29.32.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('chennai','local','master')]		= {'serverip':'172.29.32.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('chennai','messaging','master')]			= {'serverip':'172.29.32.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('pune','fin','master')]		= {'serverip':'172.29.40.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('pune','local','master')]		= {'serverip':'172.29.40.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('pune','messaging','master')]			= {'serverip':'172.29.40.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('hyderabad','fin','master')]		= {'serverip':'172.29.50.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('hyderabad','local','master')]		= {'serverip':'172.29.50.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('hyderabad','messaging','master')]			= {'serverip':'172.29.50.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('ahmedabad','fin','master')]		= {'serverip':'192.168.35.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('ahmedabad','local','master')]		= {'serverip':'192.168.35.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('ahmedabad','messaging','master')]			= {'serverip':'192.168.35.33', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('remote','fin','master')]		= {'serverip':'192.168.17.161', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_finance'}
	DB[('remote','local','master')]		= {'serverip':'192.168.17.171', 'username':APP_USER_LIVE, 'password':APP_PASS_LIVE, 'db':'db_iro'}
	DB[('remote','messaging','master')]			= {'serverip':'192.168.6.133', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'sms_email_sending'}
	DB[('mumbai','idc','master')] 		= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_mumbai'}
	DB[('hyderabad','idc','master')] 	= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_hyderabad'}
	DB[('kolkata','idc','master')] 	= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_kolkata'}
	DB[('bangalore','idc','master')] 	= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_bangalore'}
	DB[('chennai','idc','master')] 	= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_chennai'}
	DB[('delhi','idc','master')] 		= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_delhi'}
	DB[('pune','idc','master')] 		= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_pune'}
	DB[('ahmedabad','idc','master')] 	= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_ahmedabad'}
	DB[('remote','idc','master')] 		= {'serverip':'192.168.17.233', 'username':RESELLER_DEV_USER, 'password':APP_PASS, 'db':'online_regis_remote_cities'}
	DB[('mumbai','tme')] 		= {'serverip':'172.29.0.237:97'}
	DB[('remote','tme')] 		= {'serverip':'192.168.17.237:197'}
	DB[('hyderabad','tme')] 		= {'serverip':'172.29.50.237:97'}
	DB[('kolkata','tme')] 		= {'serverip':'172.29.16.237:97'}
	DB[('bangalore','tme')] 		= {'serverip':'172.29.26.237:97'}
	DB[('chennai','tme')] 		= {'serverip':'172.29.32.237:97'}
	DB[('delhi','tme')] 		= {'serverip':'172.29.8.237:97'}
	DB[('pune','tme')] 		= {'serverip':'172.29.40.237:97'}
	DB[('ahmedabad','tme')] 		= {'serverip':'192.168.35.237:97'}
	DB[('mumbai','cs')] 		= {'serverip':'172.29.0.217:81'}
	DB[('delhi','cs')] 		= {'serverip':'172.29.8.217:81'}
	DB[('kolkata','cs')] 		= {'serverip':'172.29.16.217:81'}

	# DB[('chennai','cs')] 		= {'serverip':'172.29.32.217:81'}
	# DB[('bangalore','cs')] 		= {'serverip':'172.29.26.217:81'}
	# #DB[('hyderabad','cs')] 		= {'serverip':'172.29.50.217:81'}
	# DB[('hyderabad','cs')] 		= {'serverip':'192.168.22.103'}
	# DB[('ahmedabad','cs')] 		= {'serverip':'172.29.56.237:81'}
	# DB[('pune','cs')] 			= {'serverip':'172.29.40.217:81'}
	# DB[('remote','cs')] 		= {'serverip':'192.168.17.217:81'}


	DB[('chennai','cs')] 		= {'serverip':'192.168.22.103'}
	DB[('bangalore','cs')] 		= {'serverip':'192.168.22.103'}
	#DB[('hyderabad','cs')] 		= {'serverip':'172.29.50.217:81'}
	DB[('hyderabad','cs')] 		= {'serverip':'192.168.22.103'}
	DB[('ahmedabad','cs')] 		= {'serverip':'192.168.22.103'}
	DB[('pune','cs')] 			= {'serverip':'192.168.22.103'}
	DB[('remote','cs')] 		= {'serverip':'192.168.22.103'}

	# DB[('remote','tme')] 		= {'serverip':'192.168.17.237'}
	# DB[('remote','tme')] 		= {'serverip':'192.168.17.237'}
	# DB[('remote','tme')] 		= {'serverip':'192.168.17.237'}
	# DB[('remote','tme')] 		= {'serverip':'192.168.17.237'}
	# DB[('remote','tme')] 		= {'serverip':'192.168.17.237'}
