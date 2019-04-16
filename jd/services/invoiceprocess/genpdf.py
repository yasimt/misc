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
import sys
reload(sys)  
sys.setdefaultencoding('utf8')
city =sys.argv[1]

print 'city:---',city

if city == []:
	print 'Please pass valid city name'
	exit (0)
finance_server= dbnames.DB[(city,'fin','master')]
idc_server= dbnames.DB[(city,'idc','master')]
messaging_server= dbnames.DB[(city,'messaging','master')]
db = MySQLdb.connect(host=finance_server['serverip'],    
                     user=finance_server['username'],         
                     passwd=finance_server['password'],  
                     db=finance_server['db'])           
cur = db.cursor(MySQLdb.cursors.DictCursor)

#cur.execute("SELECT * FROM tbl_invoice_proposal_details WHERE pdf_generated=0 AND insert_date > '2018-01-19 19:16:00' and (doc_type='custreceipt') and (module='ME' or module='me')")
cur.execute("SELECT * FROM  tbl_invoice_proposal_details  WHERE pdf_generated=0  AND (doc_type='custreceipt')  AND insert_date >= CURDATE() - INTERVAL 7 DAY AND  insert_date  < CURDATE() + INTERVAL  1 DAY AND (module='ME' OR module='me')")
#cur.execute("SELECT * FROM tbl_invoice_proposal_details WHERE parentid='PXX22.XX22.170704163026.W6U4' AND (doc_type='custreceipt') AND (module='ME' OR module='me')")

print 'after select query'
numrows = int(cur.rowcount)
all_rows = cur.fetchall()
count = 10
db.close()
print 'Starting ..'
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []
options1 = {
	'quiet': ''
}
#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
#function to create pdf
def gethtml(row,db,city):
	rflag=0
	if city=='remote':
		rflag=1
	genio="http://192.168.20.17/api_services/api_invoice_generation_new.php?rquest=htmlpdfgen"
	
	generatedurl = genio+"&parentid="+str(row['parentid'])+"&datacity="+str(city)+"&version="+str(row['version'])+"module=me&rflag="+str(rflag)
	requests_call = requests.get(generatedurl) 
	requests_res = requests_call.text
	htmlchanged = requests_res.replace('images/',str(filepath)+'/')
	print 'htmlchanged-----------',htmlchanged, row['parentid']
	str_find = "#####################################################"
	if str_find in htmlchanged:
		htmlchangedArr = htmlchanged.split('#####################################################')
		htmlchangedArr=list(htmlchangedArr)
		#print 'htmlArr --- ',htmlchangedArr
		if htmlchangedArr[1]:			
		    htmlchanged = htmlchangedArr[1]
		else:
			htmlchanged=htmlchangedArr[2]			
		print 'if:----'
	else:
		#htmlchangedArr=list(htmlchangedArr)
		htmlchanged=htmlchanged
		print '----htmlchanged---' ,htmlchanged
		print '---else -------'
	
	
	generate_pdf(htmlchanged,row,db)
def generate_pdf(html,row,db):
	print 'generate_pdf'
	htmlchanged = html.replace('images/',str(filepath)+'/')
	#pprint.pprint(htmlchanged)
	print "inside"
	pdfname = row['parentid']+ 'custreceipt' + str(row['gen_timestamp']) + '.pdf'
	print 'pdfname---',pdfname
	#print 'htmlchanged---',htmlchanged
	#subprocess.call(['chmod', '-R', '+w', row['path']])
	if htmlchanged!='':
		pdfpath_ins=str(row['path'])
    	pdfpath=str(row['path']) +'pdf/' + str(pdfname)
    	try:		
    		pdfkit.from_string(htmlchanged, pdfpath , options=options1)
    	except:
    		now = datetime.datetime.now()
    		pdfpath_ins="/var/www/production/me_live_remotecity/logs/invoice/"+str(now.year)+"/"+str(now.month)+"/"+str(now.day)+"/"
    		pdfpath="/var/www/production/me_live_remotecity/logs/invoice/"+str(now.year)+"/"+str(now.month)+"/"+str(now.day)+"/pdf/"
    		pdfkit.from_string(htmlchanged, pdfpath , options=options1)
    	db = MySQLdb.connect(host=finance_server['serverip'],    
    	                     user=finance_server['username'],         
    	                     passwd=finance_server['password'],  
    	                     db=finance_server['db'])  
    	cursor = db.cursor()
    	try:
    		cursor.execute ("UPDATE tbl_invoice_proposal_details SET cron_run=%s, pdf_generated=%s, cron_run_time=%s,pdf_file_name=%s,path=%s WHERE parentid=%s  and gen_timestamp=%s and doc_type=%s ", (1, 1, datetime.datetime.now(),pdfname, pdfpath_ins,str(row['parentid']), int(row['gen_timestamp']),row['doc_type'])) 
    		print ("UPDATE tbl_invoice_proposal_details SET cron_run=%s, pdf_generated=%s, cron_run_time=%s,pdf_file_name=%s,path=%s WHERE parentid=%s  and version=%s and doc_type=%s ", (1, 1, datetime.datetime.now(),pdfname, pdfpath_ins,str(row['parentid']), int(row['gen_timestamp']),row['doc_type'])) 
    		cursor._last_executed
    		db.commit() 
    	except:
    		print ':('

    	db.close()


if __name__ == '__main__':
	for row in all_rows:
		count +=1
		if count % 20 == 0:
			print "waiting for process to finish"
			for p in processes:
				p.join()
		htmlchanged=''
		pdfname=''
		# if row['html_file_name']=='':
		# 	continue
		# filename= str(row['path']) + 'html/'+ str(row['html_file_name'])
		# newvar =os.path.exists(filename)
		# if os.path.exists(filename):
		# 	openedfile = open(filename,'r')
		# 	htmlchanged = openedfile.read()
			
		# else:
		# 	continue

		if row['html_text']!='' and row['html_text'] is not None:
			try:
			   print 'target=generate_pdf:---'
			   pr = Process(target=generate_pdf, args=(row['html_text'],row,db,)) 
			   processes.append(pr)
			   pr.start()
			except:
			   print "Cant iniliaze process"
	   	elif row['html_text'] is None or row['html_text'] =='':
	   		try:
			   print 'target=gethtml:---'
			   pr = Process(target=gethtml, args=(row,db,city,)) 
			   processes.append(pr)
			   pr.start()
			except:
			   print "Cant iniliaze process"


	
	#db.close()
	for p in processes:
		p.join()
	print 'program exit'
	# print(time.strftime('%a %H:%M:%S'))
	# dbemail = MySQLdb.connect(host=messaging_server['serverip'],    
	#                      user=messaging_server['username'],         
	#                      passwd=messaging_server['password'],  
	#                      db=messaging_server['db']) 
	# cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
	# try:
	# 	cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id,  email_subject, email_text,source) VALUES (%s, %s, %s, %s, %s)",('noreply@justdial.com','rajakkal.ganesh@justdial.com','Pdf Process Done '+city,'Pdf Process Done '+city,'invoice-process'))
	# 	dbemail.commit()
	# except (MySQLdb.Error, MySQLdb.Warning) as e:
	#         print(e)
