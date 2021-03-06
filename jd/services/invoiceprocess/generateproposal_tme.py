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
import urllib
reload(sys)  
sys.setdefaultencoding('utf8')
city =sys.argv[1]
if city == []:
	print 'Please pass valid city name'
	exit (0)


finance_server= dbnames.DB[(city,'fin','master')]
idc_server= dbnames.DB[(city,'idc','master')]
local_server= dbnames.DB[(city,'local','master')]
messaging_server= dbnames.DB[(city,'messaging','master')]
cityip=dbnames.DB[(city,'tme')]
cityip=cityip['serverip']

db = MySQLdb.connect(host=finance_server['serverip'],    
					 user=finance_server['username'],         
					 passwd=finance_server['password'],  
					 db=finance_server['db'])           
cur = db.cursor(MySQLdb.cursors.DictCursor)


#cur.execute("SELECT *FROM tbl_invoice_proposal_details  WHERE cron_run=0 and doc_type='proposal' AND insert_date >=CURDATE() and  module='TME' AND email_id IS NOT NULL") 
cur.execute("SELECT *FROM tbl_invoice_proposal_details  WHERE insert_date>= DATE_SUB(NOW(), INTERVAL 7 DAY) AND insert_date<=NOW()  AND cron_run=0 AND doc_type='proposal'  AND  (module='TME' OR module='me') AND email_id IS NOT NULL")


numrows = int(cur.rowcount)
all_rows = cur.fetchall()
count = 10
db.close()
print 'Starting ..'
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []



#function to 
def getEmpDetails(userid):
	query="empcode="+userid+"&auth_token=gcTfVCDA67Noof7wfpj42D1!14w-mbh0OmV0acC5qsUag6bX"
	token=urllib.quote_plus(query)
	url="http://192.168.20.237/api/fetch_employee_info.php?"+query
	user_details = requests.get(url) 
	emp_dets=json.loads(user_details.text) 
	emp_name= emp_dets['data'][0]['empname']
	email_id= emp_dets['data'][0]['email_id']
	emp_details=[emp_name,email_id]	
	return emp_details
	

def getProposalSub(idc_server,parentid,userid,module,city,email_id):

	#print 'parentid---',parentid, userid, module, city, email_id
	db = MySQLdb.connect(host=idc_server['serverip'],    
						 user=idc_server['username'],         
						 passwd=idc_server['password'],  
						 db=idc_server['db'])           
	cur = db.cursor(MySQLdb.cursors.DictCursor)
	#parentid = (parentid,)
	print 'api call Starting ..'
	table_name = 'tbl_companymaster_generalinfo_shadow'
	#gen_comp_details = "http://192.168.22.103:800/services/mongoWrapper.php?action=getdata&post_data=1&parentid="+parentid+"&data_city="+city+"&module="+module+"&table="+table_name
	gen_comp_details = "http://192.168.22.103:800/services/mongoWrapper.php?action=getdata&post_data=1&parentid="+parentid+"&data_city="+city+"&module="+module+"&table="+table_name
	#print 'gen_comp_details------->>'	,gen_comp_details
	result = requests.get(gen_comp_details) 	
	comp_details = result.json(); 	
	#print 'comp_details-------------',comp_details
	return_arr = ''
	if comp_details=='':		
		try:
			cursor.execute ("INSERT INTO online_regis1.tbl_proposal_failed_logs (parentid, insert_date, module, email_id, api_result, api_url) VALUES (%s, %s,%s,%s,%s,%s)",(parentid,datetime.datetime.now(),module,email_id,gen_comp_details, comp_details))
			db.commit()
		except (MySQLdb.Error, MySQLdb.Warning) as e:
			print(e)
		return return_arr
	print 'api call ended ..'

	if comp_details!='': 
		companyname = comp_details['companyname']
		data_city   = comp_details['data_city']
		contact_person = comp_details['contact_person']			
		my_subject = "Justdial in Association with "+companyname+ " ("+data_city+")";
		
		header_cal = requests.get("http://messaging.justdial.com/email_header.php") 
		header= header_cal.text

		footer_cal = requests.get("http://messaging.justdial.com/email_footer.php") 
		footer= footer_cal.text
		user_det=getEmpDetails(userid)

		my_message = header+"<br><br>Dear "+contact_person+",<br>Thank you for your interest in Just Dial Limited.<br>We are attaching herewith the Proposal for your perusal.<br>Hope to have a long and fruitful business relation with you.<br><br>Thanking you,<br>Just Dial Limited<br>"+user_det[0]+"<br><br>"+footer
		return_arr = [my_subject,my_message,user_det[1]]
		return return_arr
	else:		
		return return_arr




#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
#function to create pdf
def generate_pdf(html,row,db,idc_server,city,cityip,local_server):
	
	htmlchanged = html.replace('images/',str(filepath)+'/')
	htmlchanged = htmlchanged.replace('683','583')
	htmlchanged = htmlchanged.replace('582','482')
	htmlchanged = htmlchanged.replace('525','375')
	htmlchanged = htmlchanged.replace('#####################################################','')
	#pdfname = row['parentid']+ 'proposal' + str(row['gen_timestamp']) + '.pdf'
	
	if row['pdf_file_name']!='':		
		pdfname = row['pdf_file_name']
	else: 		
		pdfname = row['parentid']+ 'proposal' + str(row['gen_timestamp']) + '.pdf'

	if htmlchanged!='':
		options = {
			'quiet': ''
			}
		#pdfpath=str(row['path']) +'pdf/' + str(pdfname)
		#print pdfpath
		if row['pdf_file_name']!='':
			pdfpath = row['pdf_file_name']
		else :	
			pdfpath=str(row['path']) +'pdf/' + str(pdfname)


		if row['pdf_file_name']=='':		
			pdfkit.from_string(htmlchanged, pdfpath , options=options)

		db = MySQLdb.connect(host=finance_server['serverip'],    
							 user=finance_server['username'],         
							 passwd=finance_server['password'],  
							 db=finance_server['db'])  
		cursor = db.cursor()
		cursor.execute ("UPDATE tbl_invoice_proposal_details SET cron_run=%s, pdf_generated=%s, cron_run_time=%s,pdf_file_name=%s,filetype=%s WHERE parentid=%s  and gen_timestamp=%s and doc_type=%s ", (1, 1, datetime.datetime.utcnow(),pdfname, 'html,pdf',str(row['parentid']), row['gen_timestamp'],row['doc_type'])) 
		db.commit() 
		db.close()

		dbemail = MySQLdb.connect(host=messaging_server['serverip'],    
							 user=messaging_server['username'],         
							 passwd=messaging_server['password'],  
							 db=messaging_server['db']) 
		cursor = dbemail.cursor(MySQLdb.cursors.DictCursor)
		#pdflink ='http://'+str(cityip)+row['download_path']+ 'pdf/'+ str(pdfname)
		
		proposaldetails=getProposalSub(idc_server,row['parentid'],row['userid'], row['module'],city, row['email_id'])
		if row['pdf_file_name']!='':		
			pdflink = row['pdf_file_name']
		else: 					
			pdflink ='http://'+str(cityip)+row['download_path']+ 'pdf/'+ str(pdfname)	
		
		print 'after pdflink'	
		message= proposaldetails[1]
		subject= proposaldetails[0]
		user_det=getEmpDetails(row['userid'])
		
		if user_det[1]=='':
			user_det[1] ='mumbai@justdial.com'
		if row['email_id'] != '':
			try:
				cursor.execute ("INSERT INTO tbl_common_intimations (sender_email, email_id, parent_id, email_subject, email_text,attachment,source) VALUES (%s, %s, %s, %s, %s, %s, %s)",(user_det[1],row['email_id'],row['parentid'],subject,message,pdflink,'proposal-process-tme'))
				dbemail.commit()
			except (MySQLdb.Error, MySQLdb.Warning) as e:
					print(e)
		rflag=0
		if city=='remote':
			rflag=1
		gen_url_storage="http://"+cityip+"/api_services/api_storage_invoice.php?rquest=CallPutObject_proposal&module=TME&parentid="+str(row['parentid'])+"&datacity="+str(city)+"&rflag="+str(rflag)+"&version="+str(row['version'])+"&filenm="+pdfname
		print 'gen_url_storage--',gen_url_storage
		requests_call = requests.get(gen_url_storage) 
		print '--requests_call--',requests_call
		#pprint.pprint(requests_call)

if __name__ == '__main__':
	for row in all_rows:
		count +=1
		if count % 20 == 0:
			print "waiting for process to finish"
			for p in processes:
				p.join()
		htmlchanged=''
		pdfname=''
		if row['html_text']=='':
			continue
		htmlchanged = row['html_text']
			
		
		if row['html_text']!='':
			try:
			   pr = Process(target=generate_pdf, args=(htmlchanged,row,db,idc_server,city,cityip,local_server,)) 
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
