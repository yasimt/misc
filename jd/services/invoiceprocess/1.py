#import pdfkit
import time
import sys
import MySQLdb 
import pprint
import requests
db = MySQLdb.connect(host="172.29.67.215",    # your host, usually localhost
                     user="decs_app",         # your username
                     passwd="s@myD#@mnl@sy",  # your password
                     db="db_finance")        # name of the data base
cur = db.cursor(MySQLdb.cursors.DictCursor) # for using column name
#cur.execute("SELECT * FROM tbl_invoice_proposal_details WHERE cron_run=0 and insert_date > '2016-01-01'")
cur.execute("SELECT *FROM tbl_invoice_proposal_details WHERE insert_date > '2016-11-01 00:00:00' AND doc_type='invoice' AND module='me' AND userid='10024775'")

# get the number of rows in the resultset
numrows = int(cur.rowcount)

all_rows = cur.fetchall()
count = 10
# thefile = open('lol1.html', 'wb') for checking generated html
#pprint.pprint(all_rows)  #for debugging
#sys.exit(0)
print(time.strftime('%a %H:%M:%S'))
for row in all_rows:
	count +=1
	#html = row['html_text']
	#htmlchanged = html.replace('images/','/home/justdial/Desktop/pythonscripts/')
	#htmlchanged = htmlchanged.replace('#####################################################','')
	#print>> thefile , htmlchanged to check generated html
	
	generatedurl='http://ganeshrj.jdsoftware.com/megenio_121216' + row['download_path'] + 	'html/' +row['html_file_name']

	print generatedurl
	html = requests.get(generatedurl) 
	html = html.text 
	#pprint.pprint(html.text)  #for debugging
	#sys.exit(0)
	htmlchanged = html.replace('images/','/home/justdial/Desktop/pythonscripts/')
	htmlchanged = htmlchanged.replace('#####################################################','')
	
    
print 'Done'
print(time.strftime('%a %H:%M:%S'))
db.close()

