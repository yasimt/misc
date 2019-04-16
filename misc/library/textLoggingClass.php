<?php
// this class log message into text file 
class textLoggingClass
{

private $log_path;
private $log_msg;
private $UserName;
private $Userid;
private $pageName;
private $parentid;
private $filehandle;


//($parentid,$log_dir="logs",$Userid=null,$UserName=null, $pageName=null)
public function __construct($parentid,$log_dir="logs",$Userid=null,$UserName=null, $pageName=null)
{

$this->parentid=$parentid;
$this->Userid = ($Userid==null ? $_SESSION['uname']:$Userid);
$this->UserName =($UserName==null ? $_SESSION['uname']:$UserName);
$this->pageName =($pageName==null ? wordwrap($_SERVER['PHP_SELF'],22,"\n",true) :$pageName);

$this->log_path = APP_PATH.$log_dir;

	$sNamePrefix= $this->log_path;
	// fetch directory for the file
	$pathToLog = dirname($sNamePrefix); 
	if (!file_exists($pathToLog)) {
		mkdir($pathToLog, 0755, true);
	}
	/*$file_n=$sNamePrefix.$contractid.".txt"; */
	$file_n=$sNamePrefix.$parentid.".html";
	// Set this to whatever location the log file should reside at.
	//$logFile = fopen($file_n, 'a+');
$this->filehandle = fopen($file_n, 'a+');

}

function __destruct()
{
fclose($this->filehandle);
unset($this->filehandle);
unset($this->parentid);
unset($this->Userid);
unset($this->UserName);
unset($this->pageName);

}

function logMessage($sMsg,$query = null)
{
	$querymsg=null;
	if($query!=null && strlen(trim($query))>2)
	{
		$querymsg="<tr><td style='width:100%; border:1px solid #669966' >Query :".$query."</td></tr>";
	}
	/*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
	
	$log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
					<tr valign='top'>
						<td style='width:15%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
						<td style='width:15%; border:1px solid #669966'>File name:".$this->pageName."</td>
						<td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
						<td style='width:30%; border:1px solid #669966'>User Name: ".$_SESSION['uname']."</td>
						<td style='width:10%; border:1px solid #669966'>User Id :".$_SESSION['ucode']."</td>
						</tr>
						".$querymsg."
				</table>";
	fwrite($this->filehandle, $log_msg);

}


}// end of class
