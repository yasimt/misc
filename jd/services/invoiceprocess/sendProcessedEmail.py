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
ip = socket.gethostbyname(socket.gethostname())

db = MySQLdb.connect(host="172.29.67.215",    
                     user="decs_app",         
                     passwd="s@myD#@mnl@sy",  
                     db="db_finance")           
cur = db.cursor(MySQLdb.cursors.DictCursor)


print 'Starting ..'
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []

#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
def invoiceContents(parentid,version):
	generatedurl=''
	if ip == '172.29.64.64':
		generatedurl='http://saritapc.jdsoftware.com/jdbox/services/omni_agreement.php'
	else:
		generatedurl=''
	paramsGET = {};
	data_city = 'mumbai'
	module = ',e'
	generatedurl = generatedurl+"?parentid="+str(parentid)+"&version="+str(version)+"&data_city="+str(data_city)+"&action=4"+"&module="+str(module)+"&usercode=000000"
	requests_call = requests.get(generatedurl) 
	requests_res = json.loads(requests_call.text)
	email_body = requests_res['data']['msg']
	return email_body


sendcontent = []
def buildQuery(parentid,version,email_to): 
	cur_sel = db.cursor(MySQLdb.cursors.DictCursor)
	cur_sel.execute("SELECT * FROM ( SELECT *  FROM tbl_invoice_proposal_details    WHERE parentid=%s and version=%s ORDER BY insert_date DESC ) AS tmp_table GROUP BY doc_type",(parentid,version))
	numrows = int(cur_sel.rowcount)
	all_rows = cur_sel.fetchall()
	email_to =''
	subject = "Justdial Services Invoice."
	from_id = 'noreply@justdial.com'
	pdflink=''
	for row_send in all_rows:
	 	# if row_send['pdf_file_name']!='':
	 	# 	pdflink +=','+'http://ganeshrj.jdsoftware.com/megenio/' +row_send['download_path']+ 'pdf/'+ str(row_send['pdf_file_name'])
		pdflink +=','+'http://ganeshrj.jdsoftware.com/megenio/' +row_send['download_path']+ 'pdf/'+ str(row_send['pdf_file_name'])

	pdflink=pdflink.lstrip(',')
	pdflink='"' + pdflink+ '"'
	email_to = email_to
	email_to = 'ganeshrj2010@gmail.com'
	email_body = invoiceContents(parentid,version) 
	email_body = 'test body' 
	sendarray = []
	sendarray.extend((from_id,email_to,parentid,subject,email_body,pdflink,'invoice-process-sendProcessedEmail')) 
	sendarray=','.join(sendarray)
	sendarray=sendarray.lstrip(',')
	sendarray=sendarray.rstrip(',')
	return sendarray

def sendEmailInvoiceMail(builtcontent):
	contentforinsert=''
	for content in sendcontent:
		contentforinsert+= '(' + content+ '),' 

	contentforinsert=contentforinsert.rstrip(',')
	
	db = MySQLdb.connect(host="172.29.0.33",    
	                     user="decs_app",         
	                     passwd="s@myD#@mnl@sy",  
	                     db="sms_email_sending")           
	cursor = db.cursor()
	data =[contentforinsert]
	pprint.pprint(data)
	
	try:
		stmt = "INSERT INTO tbls_common_intimations (sender_email, email_id, parent_id, email_subject, email_text,attachment,source) VALUES (%s, %s, %s, %s, %s, %s, %s)"
		cursor.executemany(stmt, data)
		db.commit()
	except (MySQLdb.Error, MySQLdb.Warning) as e:
	        print(e)






cur.execute("SELECT * FROM tbl_invoice_send_details where sent=0") 
numrows = int(cur.rowcount)
all_rows = cur.fetchall()
count = 0
for row in all_rows:
	count +=1
	if(row['send_to']!=''):
		content = buildQuery(row['parentid'],row['version'],row['send_to'])
		sendcontent.append(content)
	if 	count % 1 == 0 :
		sendEmailInvoiceMail(sendcontent)
		sendcontent=[]


print 'program exit'
print(time.strftime('%a %H:%M:%S'))
db.close()
