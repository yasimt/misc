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
print 'city:---',city
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


print 'Starting ..'
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []

#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
def invoiceContents(parentid,version,city):
	generatedurl=''
	if ip == '172.29.64.64':
		generatedurl='http://saritapc.jdsoftware.com/jdbox/services/omni_agreement.php'
	else:
		generatedurl='http://192.168.22.103:800/services/omni_agreement.php'
	paramsGET = {};
	data_city = city
	module = 'me'
	generatedurl = generatedurl+"?parentid="+str(parentid)+"&version="+str(version)+"&data_city="+str(data_city)+"&action=9"+"&module="+str(module)+"&usercode=000000"
	requests_call = requests.get(generatedurl)
	requests_res = json.loads(requests_call.text)
	if requests_res['error']['code']=='1' or  requests_res['error']['code']==1:
		email_body = ''
	else:
		email_body = requests_res['data']['msg']

	return email_body


sendcontent = []
dbemail = MySQLdb.connect(host=messaging_server['serverip'],
					 user=messaging_server['username'],
					 passwd=messaging_server['password'],
					 db=messaging_server['db'],charset='utf8')

def sendEmailInvoiceMail(parentid,version,email_to,db,dbemail,city):
	cur_sel = db.cursor(MySQLdb.cursors.DictCursor)
	cur_sel_new = db.cursor(MySQLdb.cursors.DictCursor)
	#cur_sel.execute("SELECT * FROM tbl_invoice_proposal_details WHERE parentid=%s and version=%s and pdf_generated=1 AND doc_type='custreceipt' AND insert_date > '2018-01-19 00:00:00' ORDER BY insert_date DESC",(parentid,version))
	print 'version',version
	print 'parentid',parentid
	cur_sel.execute("SELECT *,SUBSTRING_INDEX(GROUP_CONCAT(pdf_file_name ORDER BY updatetime DESC),',',1) AS pdf_file_name_new FROM  tbl_invoice_proposal_details  WHERE parentid=%s and version=%s and pdf_generated=1 AND doc_type in('custreceipt') AND mail_sent = 0 AND insert_date >= CURDATE() - INTERVAL 15 DAY AND  insert_date  < CURDATE() + INTERVAL  1 DAY AND (module='me' OR module='tme') GROUP BY parentid",(parentid,version))
	cur_sel_new.execute("SELECT *,SUBSTRING_INDEX(GROUP_CONCAT(pdf_file_name ORDER BY updatetime DESC),',',1) AS pdf_file_name_new_annex FROM  tbl_invoice_proposal_details  WHERE  parentid=%s and version=%s and pdf_generated=1 AND doc_type in('annexure','receipt')  AND mail_sent = 0 AND insert_date >= CURDATE() - INTERVAL 15 DAY AND  insert_date  < CURDATE() + INTERVAL  1 DAY AND (module='me' OR module='tme') GROUP BY parentid",(parentid,version))
	numrows_new = int(cur_sel_new.rowcount)
	print "numrows_new-- ",numrows_new
	numrows = int(cur_sel.rowcount)
	print "numrows-- ",numrows
	if numrows==0:		
		cur_sel = db.cursor(MySQLdb.cursors.DictCursor)
		cur_sel.execute ("UPDATE tbl_invoice_proposal_details set mail_sent=1,process_status='custreceipt',updatetime=now() WHERE parentid='"+str(row['parentid'])+"'  and version='"+row['version']+"' AND flag_send=1 AND doc_type in('custreceipt',annexure')")
		db.commit()
		return
	all_rows = cur_sel.fetchall()
	all_rows_new = cur_sel_new.fetchall()
	print email_to
	subject = "Justdial Customer Receipt."
	pdflink=''
	uid=''
	pdflink_new = ''
	from_id=''
	for row_send in all_rows:
		uid=row_send['userid']
		print row_send['pdf_file_name']
		if row_send['flag_send'] == 1 and row_send['pdf_file_name']!='':
			pdflink +=','+str(row_send['pdf_file_name_new'])
		else:	
			if row_send['pdf_file_name']!='' and (row_send['doc_type_new'] =='custreceipt' or row_send['doc_type_new'] =='annexure'):
				#pdflink +=row_send['download_path']
				if pdflink=='':
					pdflink = row_send['download_path']
				else:
					pdflink = ","+row_send['download_path']
			else:
				pdflink +=','+'http://'+dbnames.GENIO_URL+row_send['download_path']+ 'pdf/'+ str(row_send['pdf_file_name'])
				
	for row_send_new in all_rows_new:
		if row_send_new['flag_send'] == 1 and row_send_new['pdf_file_name_new_annex']!='':
			pdflink_new +=','+str(row_send_new['pdf_file_name_new_annex'])

	if uid!='':
		sso_url="http://192.168.20.237/hrmodule/employee/fetch_employee_info"
		generatedurl = sso_url+"/"+uid+'/'

		requests_call = requests.get(generatedurl)
		requests_res = json.loads(requests_call.text)
		from_id = requests_res['data']['email_id']

	rflag=0
	if city=='remote':
		rflag=1
	gen_url_storage="http://"+dbnames.GENIO_URL+"/api_services/api_storage_invoice.php?rquest=CallPutObject_new&module=ME&parentid="+str(row['parentid'])+"&datacity="+str(city)+"&rflag="+str(rflag)+"&version="+str(row['version']);

	requests_call = requests.get(gen_url_storage)
	#
	#pprint.pprint(requests_call)
	if from_id=='':
		city =sys.argv[1]
		if city=='remote':
			from_id="mumbai@justdial.com"
		from_id=city + "@justdial.com"
	pdflink=pdflink + pdflink_new	
	pdflink=pdflink.lstrip(',')
	email_to = email_to
	#email_to = 'saritha.pc@justdial.com'
	print 'pdflink::--',pdflink
	#email_to = 'pranlin.prakash@justdial.com'
	email_body = invoiceContents(parentid,version,city)
	#~ print 'email_body inside function :---',email_body
	if email_to != '' and pdflink!='' and email_body!='' and email_to!=None:
		cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
		try:
			affected_count = cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id, parent_id, email_subject, email_text,attachment,source) VALUES (%s, %s, %s, %s, %s, %s, %s)",('invoice@justdial.com',email_to,parentid,subject,email_body,pdflink,'invoice-process-dealclose')) #invoice-process-sendProcessEmail
			print("INSERT INTO tbl_common_intimations (sender_email, email_id, parent_id, email_subject, email_text,attachment,source) VALUES (%s, %s, %s, %s, %s, %s, %s)",(from_id,email_to,parentid,subject,email_body,pdflink,'invoice-process-dealclose'))
			print 'affected_count:----' ,affected_count
			dbemail.commit()

			if affected_count==1:
				cursor = db.cursor()
				cursor.execute ("UPDATE tbl_invoice_proposal_details set mail_sent=1,updatetime=now(),process_status='custreceipt' WHERE parentid='"+ str(row['parentid']) +"' and version='"+ str(row['version']) +"' and flag_send=1 and doc_type in ('custreceipt','annexure')")
				db.commit()
				
				cursor.execute("INSERT IGNORE INTO dealclose_terms_conditions (parentid,version,data_city,dealcloseDate,mail_sent,emailid,updatedOn,email_content,source,module) VALUES (%s, %s, %s,%s, %s,%s, %s, %s, %s, %s)",(parentid,row_send['version'],city,row_send['insert_date'],1,email_to, datetime.datetime.now(),email_body,'dealclose_tc',row_send['module']))
				db.commit()
				cursor.execute ("UPDATE db_invoice.tbl_whatsapp_dealclosed_data set mail_sent=1,entry_date=now() WHERE parentid='"+ str(row['parentid']) +"' and version='"+ str(row['version']) +"' and exist_flag=1")
				db.commit()
		except (MySQLdb.Error, MySQLdb.Warning) as e:
				print(e)
		cursor = db.cursor()




#~ cur.execute("SELECT * FROM tbl_invoice_send_details WHERE sent=0  AND send_to!='No Email' AND send_to!='' GROUP BY parentid, version")
#cur.execute("SELECT * FROM tbl_invoice_send_details WHERE  done_flag=0 AND sent=0  AND send_to!='No Email' AND send_to!=''  GROUP BY parentid, version")
cur.execute("SELECT * FROM tbl_invoice_proposal_details WHERE  flag_send=1 AND doc_type IN('custreceipt','annexure')  AND insert_date >= CURDATE() - INTERVAL 15 DAY AND  insert_date  < CURDATE() + INTERVAL  1 DAY AND (module='me' OR module='tme') AND mail_sent=0 GROUP BY parentid ORDER BY insert_date DESC")
numrows  = int(cur.rowcount)
all_rows = cur.fetchall()
count = 0
for row in all_rows:
	count +=1
	#if(row['send_to']!=''):
	content = sendEmailInvoiceMail(row['parentid'],row['version'],row['email_id'],db,dbemail,city)



print 'mail program exit'
print(time.strftime('%a %H:%M:%S'))
# cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
# try:
# 	cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id,  email_subject, email_text,source) VALUES (%s, %s, %s, %s, %s)",('noreply@justdial.com','rajakkal.ganesh@justdial.com','Email Process Done '+city,'Email Process Done '+city,'invoice-process'))
# 	dbemail.commit()
# except (MySQLdb.Error, MySQLdb.Warning) as e:
#         print(e)
# db.close()
