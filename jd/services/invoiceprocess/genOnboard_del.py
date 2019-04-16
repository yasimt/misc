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
import os, errno

datelist = []
today = datetime.date.today()
#mylist.append(today)
#todaydate= datelist[0] 

reload(sys)  
sys.setdefaultencoding('utf8')
city =sys.argv[1]
#city ='mumbai'
cityip=dbnames.DB[(city,'cs')]
print city
cityip=cityip['serverip']

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

cur.execute("SELECT * FROM tbl_onboarding_invoice_details WHERE pdf_gen=0 AND inserted_date >= DATE(NOW()) - INTERVAL 1 DAY")
#cur.execute("SELECT *FROM tbl_onboarding_invoice_details  WHERE parentid='PXX22.XX22.101228205513.I8R8' and pdf_gen=1")


numrows = int(cur.rowcount)
all_rows = cur.fetchall()
count = 10
db.close()
#print 'Starting ..',all_rows
filepath = os.path.dirname(os.path.abspath(__file__)) #current folder path

print(time.strftime('%a %H:%M:%S')) #process start time
processes = []

#function to send email
#emailid,from,subject,emailtext,source,pdflink,parentid,
#function to create pdf
def gethtml(row,db,city):
	rflag=0
	if city=='remote':
		rflag=1
	db = MySQLdb.connect(host=finance_server['serverip'],    
							 user=finance_server['username'],         
							 passwd=finance_server['password'],  
							 db=finance_server['db']) 
	cursor = db.cursor(MySQLdb.cursors.DictCursor)						  
	#cursor = db.cursor()
	date_new = row['inserted_date']
	cursor.execute ("SELECT a.parentid as parentid,a.instrumentid,a.version,DATE(b.finalapprovaldate) as approvalDate,a.entry_date as entryDate,a.entry_doneby FROM payment_instrument_summary AS a INNER JOIN payment_clearance_details AS b ON a.instrumentId = b.instrumentId WHERE a.parentid='" + str(row['parentid']) + "' AND a.approvalStatus=1 AND (a.version='" +row['version']+ "' OR a.app_version='" +row['version']+ "') AND DATE(b.finalapprovaldate) =' " + datetime.datetime.strftime(date_new,"%Y-%m-%d") + "' GROUP BY b.finalapprovaldate DESC")
	all_inst = cursor.fetchall()
		#cursor._last_executed
	db.commit()
	for value in all_inst:
		print value
		genio="http://genio.in/api_services/api_invoice_generation_approval_new.php?rquest=htmlpdfgen_ahm"
		generatedurl = genio+"&parentid="+str(row['parentid'])+"&datacity="+str(city)+"&version="+str(row['version'])+"&module=cs&rflag="+str(rflag)+"&invDate="+str(value['approvalDate'])+"&instrumentid="+str(value['instrumentid'])+"&approval_date="+str(date_new)+"&usrcd="+str(value['entry_doneby']);
		print generatedurl
		requests_call = requests.get(generatedurl)
		requests_res = requests_call.text
		#return requests_res

def checkFolderCreation(cityip):
	folderapi = 'http://'+str(cityip)+'/api_services/foldercreation.php?rquest=CreatFld&fldnm=logs/invoice'
	#folderapi = 'http://ganeshrj.jdsoftware.com/csgenio/api_services/foldercreation.php?rquest=CreatFld&fldnm=logs/invoice'
	requests_call = requests.get(folderapi)
	requests_res = json.loads(requests_call.text)
	if city=='remote':
		path = '/httpdjail'+requests_res['result']['path'][0]
	else:
		path = requests_res['result']['path'][0]
	#path=path.split('invoice') /httpdjail/
	#path=list(path)
	return path
	

def generate_pdf(row,db,cityip,city):
	
	htmlchanged=gethtml(row,db,city)
	#~ if htmlchanged=='telangana':
		#~ print htmlchanged
		#~ exit(0)
	htmlchanged_arr=htmlchanged.split('#####################################################')
	htmlchanged_arr=list(htmlchanged_arr)
	
	if htmlchanged!='':

		options = {
		    'quiet': ''
		    }
		path=checkFolderCreation(cityip)
		path_ins=path
		path_ins=path_ins.split('logs/')
		path_ins=list(path_ins)
		path_ins='logs/'+str(path_ins[1])
		pdfpath=path+"/pdf/"		
		ii=0
		for acthtml in htmlchanged_arr:
			acthtml = acthtml.replace('images/',str(filepath)+'/')
			if ii==0:
				invtype='receipt'
			else:
				invtype='invoice'
			ii=ii+1
			timestr= int(row['inserted_date'].strftime("%s"))
			pdfname = row['parentid']+ invtype + str(timestr) + '.pdf'
			fullpath=pdfpath+ str(pdfname)
			print fullpath
			try:		
				pdfkit.from_string(acthtml, fullpath , options=options)
			except:
				print 'Pdf Could Not be generated'
			db = MySQLdb.connect(host=finance_server['serverip'],    
			                     user=finance_server['username'],         
			                     passwd=finance_server['password'],  
			                     db=finance_server['db'])  
			cursor = db.cursor()
			try:
				cursor.execute ("UPDATE tbl_onboarding_invoice_details SET pdf_gen=%s WHERE parentid=%s  ", (1, str(row['parentid']))) 
				#cursor._last_executed
				db.commit() 

				cursor.execute ("UPDATE tbl_invoice_proposal_details SET path=%s, download_path=%s, pdf_file_name=%s WHERE parentid=%s  and doc_type=%s", (str(path), path_ins, pdfname,str(row['parentid']), invtype))
				db.commit() 
			except:
				print ':('

			db.close()


if __name__ == '__main__':
	for row in all_rows:
		#~ count +=1
		#~ if count % 20 == 0:
			#~ print "waiting for process to finish"
			#~ for p in processes:
				#~ p.join()
		gethtml(row,db,city)		
		#htmlchanged=''
		#pdfname=''
		# if row['html_file_name']=='':
		# 	continue
		# filename= str(row['path']) + 'html/'+ str(row['html_file_name'])
		# newvar =os.path.exists(filename)
		# if os.path.exists(filename):
		# 	openedfile = open(filename,'r')
		# 	htmlchanged = openedfile.read()
			
		# else:
		# 	continue
		#generate_pdf(row,db,cityip,city)
		#~ try:
		   #~ pr = Process(target=generate_pdf, args=(row,db,cityip,city,)) 
		   #~ processes.append(pr)
		   #~ pr.start()
		#~ except:
		   #~ print "Cant iniliaze process"
   


	
	
	#~ for p in processes:
		#~ p.join()
	#~ print 'program exit'
