import pdfkit
import time
import datetime
import sys
import MySQLdb 
import pprint
import requests
from multiprocessing import Process
import multiprocessing
import os
import socket
import json
import subprocess
import dbnames
reload(sys)  
sys.setdefaultencoding('utf8')
ip = socket.gethostbyname(socket.gethostname())

city =sys.argv[1]
#city = 'ahmedabad'
print city
if city == []:
	print 'Please pass valid city name'
	exit (0)
finance_server= dbnames.DB[(city,'fin','master')]
messaging_server= dbnames.DB[(city,'messaging','master')]
db = MySQLdb.connect(host=finance_server['serverip'],    
                     user=finance_server['username'],         
                     passwd=finance_server['password'],  
                     db=finance_server['db'])      
cur = db.cursor(MySQLdb.cursors.DictCursor)

#cityip=dbnames.DB[(city,'cs')]
#cityip=cityip['serverip']

cityip=dbnames.DB[(city,'cs')]
cityip=cityip['serverip']

print 'Starting ..'
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []

#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
def invoiceContents(parentid,version,city):
	generatedurl=''
	if ip == '172.29.64.64':
		generatedurl='http://ganeshrj.jdsoftware.com/jdbox_api/services/omni_agreement.php'
	else:
		generatedurl='http://192.168.22.103:800/services/omni_agreement.php'
	paramsGET = {};
	data_city = city
	module = 'me'
	generatedurl = generatedurl+"?parentid="+str(parentid)+"&version="+str(version)+"&data_city="+str(data_city)+"&action=3"+"&module="+str(module)+"&usercode=000000"

	requests_call = requests.get(generatedurl) 
	requests_res = json.loads(requests_call.text)

	if requests_res['error']['code']=='1' or  requests_res['error']['code']==1:
		email_body = ''
	else:
		email_body = requests_res['data']['html']
	#email_body = email_body.replace('\\r\\n','')
	# print email_body
	# exit(0)
	return email_body


sendcontent = []
dbemail = MySQLdb.connect(host=messaging_server['serverip'],    
                     user=messaging_server['username'],
                     passwd=messaging_server['password'],  
                     db=messaging_server['db'],charset='utf8') 

def sendEmailInvoiceMail(parentid,version,email_to,db,dbemail,city,cityip): 
	cur_sel = db.cursor(MySQLdb.cursors.DictCursor)
	cur_sel.execute("SELECT * FROM tbl_invoice_proposal_details where parentid=%s and version=%s and doc_type IN ('invoice','receipt','annexure') AND flag_send=1 AND companyname!='' AND mobile!='' AND mail_sent=0",(parentid,version))
	numrows = int(cur_sel.rowcount)
	if numrows==0:
		return
	all_rows = cur_sel.fetchall()
	print email_to
	subject = "Thank you for your registration with Just Dial Services."
	pdflink=''
	uid=''
	from_id=''
	if numrows>0:
		for row_send in all_rows:
			uid=row_send['userid']
			#print row_send['pdf_file_name']
			print row_send['flag_send']
			if row_send['pdf_file_name']!='':
				if row_send['flag_send'] == 1:
					pdflink +=','+str(row_send['pdf_file_name'])
				else:
					pdflink +=','+'http://'+str(cityip)+'/'+row_send['download_path']+ '/pdf/'+ str(row_send['pdf_file_name'])
				if row_send['flag_send'] == 1:
					mobile =row_send['mobile']
					contact_person =row_send['contact_person']
					companyname =row_send['companyname']
			print 'pdglink====',mobile
			print 'pdglink====',contact_person
			print 'pdglink====',companyname
			comments = contact_person+'##'+companyname
			print 'pdglink====',pdflink
	#sso_url="http://192.168.20.237/hrmodule/employee/fetch_employee_info"
	#generatedurl = sso_url+"/"+uid+'/'
	
	#requests_call = requests.get(generatedurl) 
	#requests_res = json.loads(requests_call.text)
	#from_id = requests_res['data']['email_id']
	from_id = 'invoice@justdial.com'

	rflag=0
	if city=='remote':
		rflag=1
	# gen_url_storage="http://"+dbnames.GENIO_URL+"/api_services/api_storage_invoice.php?rquest=CallPutObject&module=CS&parentid="+str(row['parentid'])+"&datacity="+str(city)+"&rflag="+str(rflag)+"&version="+str(row['version']);
	
	# requests_call = requests.get(gen_url_storage) 
	#pprint.pprint(requests_call)
	if from_id=='':
		city =sys.argv[1]
		if city=='remote':
			from_id="mumbai@justdial.com"	
		from_id=city + "@justdial.com"
	pdflink=pdflink.lstrip(',')
	# email_to = email_to
	# email_to = 'ganeshrj2010@gmail.com'
	email_body = invoiceContents(parentid,version,city)
	
	if email_to != '' and pdflink!=''  and email_body!='':
		cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
		try:
			
			cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id, parent_id, email_subject, email_text,attachment,source,comments,mobile) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",(from_id,email_to,parentid,subject,email_body,pdflink,'invoice-process_test',comments,mobile))
			
			dbemail.commit()
		except (MySQLdb.Error, MySQLdb.Warning) as e:
		        print(e)
		cursor = db.cursor()
		cursor.execute ("UPDATE tbl_onboarding_invoice_details set cron=%s WHERE parentid=%s  and version=%s ", (1, str(row['parentid']), row['version'])) 
		db.commit()
		cursor.execute ("UPDATE tbl_invoice_proposal_details set mail_sent=1,mail_sent_date=now(),process_status='approval_process' WHERE parentid='"+str(row['parentid'])+"' and version='"+str(row['version'])+"' and flag_send=1  AND companyname!='' AND mobile!=''")
		db.commit()


datelist = []
# today = datetime.date.today()
# mylist.append(today)
# todaydate= datelist[0] 
cur.execute("SELECT * FROM tbl_onboarding_invoice_details WHERE pdf_gen=1 AND cron=0 AND sent_to!='' AND inserted_date >= DATE(NOW()) - INTERVAL 1 DAY")

numrows = int(cur.rowcount)
all_rows = cur.fetchall()
count = 0
for row in all_rows:
	count +=1
	#if(row['send_to']!=''):
	content = sendEmailInvoiceMail(row['parentid'],row['version'],row['sent_to'],db,dbemail,city,cityip)
		


print 'mail program exit'
print(time.strftime('%a %H:%M:%S'))
# cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
# try:
# 	cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id,  email_subject, email_text,source) VALUES (%s, %s, %s, %s, %s)",('noreply@justdial.com','rajakkal.ganesh@justdial.com','Email Process Done '+city,'Email Process Done '+city,'invoice-process'))
# 	dbemail.commit()
# except (MySQLdb.Error, MySQLdb.Warning) as e:
#         print(e)
# db.close()
