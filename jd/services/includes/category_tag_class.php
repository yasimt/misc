<?php
class category_tag_class extends DB
{
	
	var  $params  	= null;
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');	
	
	var $ucode =null;
	var $parentid= null;
	var $version= null;
	var $data_city	= null;
	var $reqcity	= null;
	var $endtime	= null;
	var $starttime	= null;
	var $datebetween =null;
	
	function __construct($params)
	{		
		$this->params = $params;
	
		if(trim($this->params['action']) == "")
		{
			$result_msg_arr['error']['code'] = 1;
			$result_msg_arr['error']['msg'] = "Action Missing";
			echo json_encode($result_msg_arr);exit;
		}
		else
			$this->action  = strtolower($this->params['action']); 
			
		
		if(isset($this->params['ucode']) && trim($this->params['ucode']) != "")
		{
			$this->ucode = $this->params['ucode'];
		}
		
		if(isset($this->params['uname']) && trim($this->params['uname']) != "")
		{
			$this->uname = $this->params['uname'];
		}
		
		
		if(isset($this->params['category']) &&  trim($this->params['category']) != "")
		{
			$category_arr   = json_decode($this->params['category'],true);
			$this->category = implode(",",$category_arr);
			if(count($category_arr) <=0 )
			{
				$result_msg_arr['error']['code'] = 3;
				$result_msg_arr['error']['msg'] = "Invalid Category Passed";
				echo json_encode($result_msg_arr);exit;
			}			
		}else{
			$result_msg_arr['error']['code'] = 3;
			$result_msg_arr['error']['msg'] = "category Missing";
			echo json_encode($result_msg_arr);exit;
		}
		
		if(isset($this->params['data_city']) && trim($this->params['data_city']) != "")
		{
			$this->data_city = $this->params['data_city'];
		}
		
		$this->setServers();
		$this->categoryClass_obj = new categoryClass();
	}
	
	
	function setServers()
	{	
		global $db;		
		
		$data_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->dbConDjds  		= $db[$data_city]['d_jds']['master'];
		if(DEBUG_MODE)
		{
			echo '<pre> $db d_jds  ' ; print_r($db[$data_city]['d_jds']);
			
		}
		
	}
	
	function getMergeCategory()
	{
		$sql = "SELECT * FROM tbl_category_merge_mapping WHERE catid_src IN (".$this->category.")";
		$res = parent::execQuery($sql, $this->dbConDjds);
		if(DEBUG_MODE)
		{
			echo '<br> sql :: '.$sql;
			echo '<br> rows :: '.mysql_num_rows($res);
			
		}
		if($res && mysql_num_rows($res))
		{
			while($row = mysql_fetch_assoc($res))
			{
				$merge_dest_ids[$row['catid_des']] = $row['catid_src'];
			}
			
			if(is_array($merge_dest_ids) && count($merge_dest_ids)>0)
			{
				//$sql_category = "SELECT catid,category_name FROM tbl_categorymaster_generalinfo WHERE catid in (".implode(',',array_keys($merge_dest_ids)).")";
				//$res_category = parent::execQuery($sql_category, $this->dbConDjds);
				$catid_list = implode(',',array_keys($merge_dest_ids));
				$cat_params = array();
				$cat_params['page']= 'category_tag_class';
				$cat_params['data_city'] 	= $this->data_city;		
				$cat_params['return']		= 'catid,category_name';

				$where_arr  	=	array();
				$where_arr['catid']			= $catid_list;		
				$cat_params['where']		= json_encode($where_arr);

				$cat_res_arr = array();
				if($catid_list!=''){
					$cat_res	=	$this->categoryClass_obj->getCatRelatedInfo($cat_params);			
				}
				if($cat_res!=''){
					$cat_res_arr =	json_decode($cat_res,TRUE);
				}
			
				if(DEBUG_MODE)
				{
					echo '<br> sql :: '.$sql_category;
					echo '<br> rows :: '.mysql_num_rows($res_category);
					
				}
				
				if($cat_res_arr['errorcode']=='0' && count($cat_res_arr['results'])>0)
				{
					foreach($cat_res_arr['results'] as $key =>$cat_arr)
					{
						$merge_categories[$merge_dest_ids[$cat_arr['catid']]]['dest_catid'] = $cat_arr['catid'];
						$merge_categories[$merge_dest_ids[$cat_arr['catid']]]['dest_name']  = $cat_arr['category_name'];
					}
					if(DEBUG_MODE)
					{
						echo '<pre>';
						print_r($merge_categories);
						
					}
					
					$result_msg_arr['error']['code'] = 0;
					$result_msg_arr['error']['res']  = $merge_categories;
					
					return $result_msg_arr;
				}
			}else
			{
				$result_msg_arr['error']['code'] = -1;
				$result_msg_arr['error']['msg'] = "No Categories Found In Merging";
				return $result_msg_arr;
			}
			
		}else
		{
			$result_msg_arr['error']['code'] = -1;
			$result_msg_arr['error']['msg'] = "No Categories Found In Merging";
			return $result_msg_arr;
		}
		
	}
	

}

?>
