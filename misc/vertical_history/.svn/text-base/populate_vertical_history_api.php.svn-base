<?
session_start();
require_once("../library/config.php");

if(($remote_city_flag ==1) && ($_SERVER['SERVER_ADDR'] == '172.29.64.64')){
	define("REMOTE_CITY_MODULE", "remote_city");
}
require_once("../common/Serverip.php");
require_once(APP_PATH."library/path.php");
GLOBAL $dbarr;
class vertical_history
{
	public function __construct($mandatory_fileds_arr,$dbarr)
	{
		$this->conn_iro  = new DB($dbarr['DB_IRO']);
		$this->conn_local= new DB($dbarr['LOCAL']);
		
		$parentid 		= $mandatory_fileds_arr['parentid'];
		$docid 			= $mandatory_fileds_arr['docid'];
		$companyname 	= $mandatory_fileds_arr['companyname'];
		$data_city 		= $mandatory_fileds_arr['data_city'];
		$vertical_name 	= $mandatory_fileds_arr['vertical_name'];
		$ucode 			= $mandatory_fileds_arr['ucode'];
		$uname 			= $mandatory_fileds_arr['uname'];
		$module 		= $mandatory_fileds_arr['module'];
		$parent_pid 	= $mandatory_fileds_arr['parent_pid'];
		
		if(trim($parentid)=='')
        {
            $message = "Parentid is blank.";
            echo json_encode($this->send_response_message($message,'1'));
            die();
        }
        if(trim($docid)=='')
        {
            $message = "Docid is blank.";
            echo json_encode($this->send_response_message($message,'1'));
            die();
        }
        if(trim($companyname)=='')
		{
			$message = "Company Name is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(trim($data_city)=='')
		{
			$message = "Data City is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
        if(trim($vertical_name)=='')
		{
			$message = "Vertical Name is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(trim($ucode)=='')
        {
			$message = "User Code is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(trim($uname)=='')
		{
			$message = "User Name is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(trim($module)=='')
		{
			$message = "Module is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(trim($parent_pid)=='')
		{
			$message = "Parent Contractid is blank.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		$this->parentid		= $parentid;
		$this->docid		= $docid;
		$this->companyname	= $companyname;
		$this->data_city	= $data_city;
		$this->vertical_name= $vertical_name;
		$this->ucode 		= $ucode;
		$this->uname 		= $uname;
		$this->module 		= $module;
		$this->parent_pid 	= $parent_pid;
		
		
		
	}
	public function populate_vertical_history($old_fields_arr,$new_fields_arr)
	{
		$this->old_fields_arr 	 	= $old_fields_arr;
		$this->new_fields_arr  	 	= $new_fields_arr;
		
		$old_new_matched_keys_arr = array_intersect_key($this->old_fields_arr,$this->new_fields_arr);
		$old_new_matched_keys_name = array_keys($old_new_matched_keys_arr);
		$old_new_matched_keys_name = array_filter($old_new_matched_keys_name);
		$old_new_matched_keys_name = array_unique($old_new_matched_keys_name);
		if(count($old_new_matched_keys_name)>0)
		{
			$business_details_old_arr = array();
			$business_details_new_arr = array();
			foreach($old_new_matched_keys_name as $matched_key_name)
			{
				$valid_key_flag = 0;
				$valid_key_flag = $this->is_valid_key($matched_key_name);
				if($valid_key_flag == 1)
				{
					$old_fields_val = $old_fields_arr[$matched_key_name];
					$old_fields_val_ws = preg_replace('/\s+/', '', $old_fields_val);
					$new_fields_val = $new_fields_arr[$matched_key_name];
					$new_fields_val_ws = preg_replace('/\s+/', '', $new_fields_val);
					if(strtolower($old_fields_val_ws) != strtolower($new_fields_val_ws))
					{
						$business_details_old_arr[$matched_key_name] = $old_fields_val;
						$business_details_new_arr[$matched_key_name] = $new_fields_val;
					}
				}
			}
		}
		else
		{
			$message = "Old and New key do not match.";
			echo json_encode($this->send_response_message($message,'1'));
			die();
		}
		if(count($business_details_old_arr) || count($business_details_new_arr))
		{
			$sqlInsertVerticalHistory ="INSERT INTO tbl_vertical_bform_details SET
											 parentid		= '".$this->parentid."',
											 docid			= '".$this->docid."',
											 companyname	= '".addslashes(stripslashes($this->companyname))."',
											 data_city		= '".$this->data_city."',
											 vertical_name	= '".addslashes(stripslashes($this->vertical_name))."',
											 ucode		 	= '".$this->ucode."',
											 uname		 	= '".$this->uname."',
											 module		 	= '".$this->module."',
											 parent_pid		= '".$this->parent_pid."',
											 insertdate		= '".date("Y-m-d H:i:s")."',
											 business_details_old = '".http_build_query($business_details_old_arr)."',
											 business_details_new = '".http_build_query($business_details_new_arr)."'";
			
			$resInsertVerticalHistory = $this->conn_iro->query_sql($sqlInsertVerticalHistory);
		}
		//print"<pre>";print_r($business_details_old_arr);
		//print"<pre>";print_r($business_details_new_arr);
		$message = "Updated Successfully";
		echo json_encode($this->send_response_message($message,'0'));
		die();
	}
	private function send_response_message($msg,$code)
	{
		$res_msg_arr['results'] = array();	
		$res_msg_arr['error']['code'] = $code;
		$res_msg_arr['error']['msg'] = $msg;
		return $res_msg_arr;
	}
	public function is_valid_key($key_name)
	{
		$valid_key = 0;
		$sqlChkValidKey = "SELECT key_name FROM tbl_vertical_key_details WHERE key_name ='".$key_name."' AND key_value!='' AND active_flag =1";
		$resChkValidKey = $this->conn_iro->query_sql($sqlChkValidKey);
		if($resChkValidKey && mysql_num_rows($resChkValidKey)>0)
		{
			$valid_key = 1;
		}
		return $valid_key;
	}
}

$history_data 			= json_decode($_REQUEST['history_data'],true);


$mandatory_fileds_arr 	= array();
$mandatory_fileds_arr = $history_data['mandatory'];
$mandatory_fileds_arr = array_map('trim',$mandatory_fileds_arr);

$old_fields_arr = array();
$old_fields_arr = $history_data['old'];
$old_fields_arr = array_map('trim',$old_fields_arr);

$new_fields_arr = array();
$new_fields_arr = $history_data['new'];
$new_fields_arr = array_map('trim',$new_fields_arr);


$parentid 		  	= $mandatory_fileds_arr['parentid'];
$docid 			  	= $mandatory_fileds_arr['docid'];
$companyname 	  	= $mandatory_fileds_arr['companyname'];
$data_city 	  	  	= $mandatory_fileds_arr['data_city'];
$vertical_name 	  	= $mandatory_fileds_arr['vertical_name'];
$ucode 			  	= $mandatory_fileds_arr['ucode'];
$uname 			  	= $mandatory_fileds_arr['uname'];
$module 			= $mandatory_fileds_arr['module'];
$parent_pid 	  	= $mandatory_fileds_arr['parent_pid'];
$remote_city_flag 	= $mandatory_fileds_arr['remote_city_flag'];
    



//print"<pre>";print_r($mandatory_fileds_arr);
//print"<pre>";print_r($old_fields_arr);
//print"<pre>";print_r($new_fields_arr);

$vertical_history_obj = new vertical_history($mandatory_fileds_arr,$dbarr);
$vertical_history_obj->populate_vertical_history($old_fields_arr,$new_fields_arr);

//http://imteyazraja.jdsoftware.com/csgenio/vertical_history/populate_vertical_history_api.php?parentid=PXX22.XX22.140904104846.X3M3&new_company=urlencode(test popular company)&old_company=urlencode(test popular company)&paid_flag=0&ucode=10000760&uname=urlencode(Imteyaz Raja)&parent_pid=PXX22.XX22.140904104846.X3M3&data_city=Mumbai&source=urlencode(DE)&landline=65415841&mobile=8888888888&tollfree= // URL Called


?>
