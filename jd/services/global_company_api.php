<?
require_once("../config.php");

class GlobalApi extends DB{
	var  $dataservers 	= array('mumbai', 'delhi', 'pune', 'bangalore', 'ahmedabad', 'hyderabad', 'chennai', 'kolkata', 'remote');

	function __construct($params_arr)
	{	
		$this->error_array = array();
		if(trim($params_arr['data_city'])=='')
        {
            $msg = "data city is blank.";
            echo json_encode($this->sendDieMsg($msg));
            die();
        }
        if(isset($params_arr['compname'])){
            $params_arr['compname'] = rawurldecode($params_arr['compname']);
         }
        //decoding due to ++ symbol issue php treats + as space

        $this->data_city	=	trim($params_arr['data_city']);
        $this->catchkignore	=	0;
        $this->excl_cat_flag= 	0;
        $this->compname_univ_flag   =   0;
        $this->setServers();
        $this->categoryClass_obj = new categoryClass();
        $this->companyClass_obj =   new companyClass();
        $this->debug = 0;
        $this->debug_resp = array();
       	if($params_arr['trace']==1){
       		echo "Params :<pre>";print_r($params_arr);
       		$this->debug = 1;
       	}
		$this->fieldwise = 0;
		if($params_arr['fieldwise']==1){
       		$this->fieldwise = 1;
       	}

        if(count($params_arr)>0){
        	$compname 		= trim($params_arr['compname']);
        	$parentid       = trim($params_arr['parentid']);
        	$area  			= trim($params_arr['area']);
        	$pincode  		= trim($params_arr['pincode']);
        	$contact_person = trim($params_arr['contact_person']);
        	$designation 	= trim($params_arr['designation']);
        	$landline 		= trim($params_arr['landline']);
        	$stdcode 		= trim($params_arr['stdcode']);
        	$mobile 		= trim($params_arr['mobile']);
        	$tollfree 		= trim($params_arr['tollfree']);
        	$fax 			= trim($params_arr['fax']);
        	$email 			= trim($params_arr['email']);      	
        	$website 		= trim($params_arr['website']);
        	$year_of_est	= trim($params_arr['year_of_est']);
        	$module			= trim($params_arr['module']);
			$helpline_flag  = trim($params_arr['helpline_flag']);
			$check_pincode  = trim($params_arr['check_pincode']);

            $this->helpline_flag  = $helpline_flag;

        	$building_name	= trim($params_arr['building_name']);
        	$street			= trim($params_arr['street']);
        	$landmark		= trim($params_arr['landmark']);

        	$this->building_name = $building_name;
        	$this->street 		 = $street;
        	$this->landmark 	 = $landmark;
        	if($module==''){
        		$msg = "module is blank.";
	            echo json_encode($this->sendDieMsg($msg));
	            die();
        	}
        	$this->module = strtoupper($module);
        	if($parentid !=''){
        		$this->parentid = $parentid;
        	}
        	
        	if($building_name!='' || $street!=''|| $landmark!=''){
        		$this->addressValidation();
        	}
			if(isset($params_arr['state']) && $params_arr['state']==''){	        	
				$this->error_array['block']['msg'][]  = "State is blank";				
				$this->error_array_field['block']['msg']['state']  = "State is blank";				
       		}
       		if(isset($params_arr['city']) && $params_arr['city']==''){	        	
				$this->error_array['block']['msg'][]  = "City is blank";				
				$this->error_array_field['block']['msg']['city']  = "City is blank";				
       		}
            if($this->module=='DE'){
                $this->excl_cat_flag = trim($params_arr['excl_cat_flag']);        
            }
            if($this->module=='ME' || $this->module=='TME'){
                $dispose_val = trim($params_arr['dispose_val']);
                $address_flag= 0;
                if(isset($params_arr['building_name']) || isset($params_arr['street']) || isset($params_arr['landmark'])){
                    if((isset($params_arr['building_name']) && $building_name!='')){
                       $address_flag =1;
                    }
                    if((isset($params_arr['street']) && $street!='')){
                       $address_flag =1;
                    }
                    if((isset($params_arr['landmark']) && $landmark!='')){
                       $address_flag =1;
                    }
                    $skip_dispose =2;
                    if(isset($dispose_val) && !empty($dispose_val) && $dispose_val!=25){
                        $skip_dispose= 1;
                    }
                    if(($address_flag !=1) && $skip_dispose ==2){
                        if($skip_dispose!=1){
                            $this->error_array['block']['msg'][]  = "Please enter any one from Building/Street name/Landmark.";
                            $this->error_array_field['block']['msg']['address']  = "Please enter any one from Building/Street name/Landmark.";
                        }
                    }
                }
            }
        	$format_compname  = trim($params_arr['format_compname']);
            
            if(isset($params_arr['compname'])){				
				$this->compname = $compname;
				$this->contractExclusionInfo();
				
				if($this->compname_univ_flag !=1){        		
					$this->companyNameValidation();
					$this->checkHomeKeyChar();
				}
				if($format_compname==1){
					$this->formatCompanyname($compname);
				}
			}
        	
        	if(!empty($area)){
        		$this->area = $area;
        		if($this->module=='DE' || $this->module=='CS' || $this->module=='TME')
					$this->area_HO_GPO_Validation();
        	}
        	if($pincode!=''){
				$this->pincode 		 = $pincode;
        		$this->pincodeValidation($check_pincode);
        	}
             $othercity_number = trim($params_arr['othercity_number']);
             if($othercity_number!=''){
                $othercity_number_arr       = array();
                $othercity_number_final_arr = array();
                $othercity_number_arr       =   explode("|~|",$othercity_number);

                if(count($othercity_number_arr)>0){
                    foreach($othercity_number_arr as $key=>$number_str){
                        if($number_str!=''){
                           $number_std_arr = array();
                           $number_std_arr = explode("-",$number_str);
                           $other_stdcode  =   trim($number_std_arr['0']);
                           $other_number   =   trim($number_std_arr['1']);
                           if($other_stdcode!=''){
                                $str_arr    =   str_split($other_stdcode);
                                if($str_arr['0']=='0'){
                                    $other_stdcode =    substr($other_stdcode, 1);
                                }else{
                                    $other_stdcode =    $other_stdcode;
                                }                       
                            }
                            $total_length = strlen($other_stdcode)+strlen($other_number);
                            if($total_length==10){
                                $othercity_number_final_arr[$other_stdcode] = $other_number;
                            }
                        }
                    }                 
                    if(count($othercity_number_final_arr)>0){
                        $block_num_other = $this->blockOtherCityNumber($othercity_number_final_arr);
                        if($block_num_other!=''){
                            $this->error_array['block']['msg'][]  = "These other city numbers cannot be added as its Blocked For Entry :".$block_num_other;
                            $this->error_array_field['block']['msg']['other_city_number']  = "These other city numbers cannot be added as its Blocked For Entry :".$block_num_other;
                            //Please don't change above message,as it is being used somewhere.
                        }
                    }                    
                }           
             }

        	 switch($this->module){
	        	case 'CS':
	        	//echo "jj".$mobile;
	        			if($mobile =='' && $landline =='' && $tollfree ==''){
	        				$this->error_array['block']['msg'][]  = "Please enter at least one Mobile/Landline/Tollfree number";
	        				$this->error_array_field['block']['msg']['single_number']  = "Please enter at least one Mobile/Landline/Tollfree number";
	        				break;
	        			}        			
      		  }
        	$contact_person_arr 	= array();
        	$contact_person_err_arr	= array();
        	if($contact_person!=''){        		
        		$contact_person_arr	= array_filter(explode("|~|",$contact_person));
        		foreach ($contact_person_arr as $key => $contact_person){
        			$contact_person_err	=	$this->contactPersonValidation($contact_person);
        			$contact_person_err_arr[$contact_person_err][]=	$contact_person;
        		}
        		if(count($contact_person_err_arr)>0){
        			foreach ($contact_person_err_arr as $rule => $value) {
        				switch ($rule){
        					case 'RULE_CONTACT_ALPHA':
        						$this->error_array['block']['msg'][]  = "Contact person name should be only Albhabetic : ".implode(",",array_filter(array_unique($contact_person_err_arr['RULE_CONTACT_ALPHA'])));
        						$this->error_array_field['block']['msg']['contact_person']  = "Contact person name should be only Albhabetic : ".implode(",",array_filter(array_unique($contact_person_err_arr['RULE_CONTACT_ALPHA'])));
        							break;
        					case 'RULE_CONTACT_4_ALPHA':
        						$this->error_array['block']['msg'][]  = "Contact person name contains more than 4 repeated alphabets : ".implode(",",array_filter(array_unique($contact_person_err_arr['RULE_CONTACT_4_ALPHA'])));
        						$this->error_array_field['block']['msg']['contact_person']  = "Contact person name contains more than 4 repeated alphabets : ".implode(",",array_filter(array_unique($contact_person_err_arr['RULE_CONTACT_4_ALPHA'])));
        							break;
        				}
        			}
        		}
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($contact_person_arr);
        		if(count($result_arr)>0){
	        		$this->error_array['block']['msg'][]  = "Contact person has duplicate value : ".implode(",",$result_arr);	        	
	        		$this->error_array_field['block']['msg']['contact_person']  = "Contact person has duplicate value : ".implode(",",$result_arr);	        	
        		}    		
        	}
        	if($designation!=''){
        		$designation_arr = array();
        		$designation_arr = explode("|~|",$designation);
        		if(count($designation_arr)>0){
        			$this->designationValidation($designation_arr);
        		}
        	}
        	//echo strrpos($stdcode,"0");die;
        	if($stdcode!=''){
        		$str_arr	=	str_split($stdcode);
        		if($str_arr['0']=='0'){
        			$this->stdcode =	substr($stdcode, 1);
        		}else{
        			$this->stdcode =	$stdcode;
        		}						
			}
        	
        	if($landline!=''){
        		/*if($this->stdcode==''){
        			$this->error_array['stdcode']['msg'][]  = "stdcode is blank";
        		}*/
        		$landline_err_arr  = array();
        		$landline_arr 	= array();
        		$landline_arr	= explode("|~|",$landline);
        		//echo "<pre>";print_r($landline_arr);
        		//echo count($landline_arr);die;
                if(($this->module=='CS' ||$this->module=='DE') && $this->helpline_flag==1 && $this->helpline_flag!=''){
                    //No validation For landline length Block number 
                }
                else{
            		if(count($landline_arr)>0){
            			foreach ($landline_arr as $land_line) {
            				if($land_line!=''){
    	        				$landline_err =  $this->landlineValidation($land_line);
    	        				if($landline_err!=''){
    	        					$landline_err_arr[$landline_err][] = $land_line;
    	        				}
            				}       			
            			}
            		}
            		//echo "<pre>";print_r($landline_err_arr);
            		if(count($landline_err_arr)>0){
            			foreach ($landline_err_arr as $rule => $value) {
            				switch ($rule){
            					case 'RULE_LANDLINE_ERR':
            						$this->error_array['block']['msg'][]  = "Landline number should be numeric starting from 2-7   : ".implode(",",array_filter(array_unique($landline_err_arr['RULE_LANDLINE_ERR'])));
            						$this->error_array_field['block']['msg']['landline']  = "Landline number should be numeric starting from 2-7   : ".implode(",",array_filter(array_unique($landline_err_arr['RULE_LANDLINE_ERR'])));
            							break;
            					case 'RULE_LANDLINE_LENGTH':
            						$this->error_array['block']['msg'][]  = "Length of Stdcode and Landline number should be 10 digit  : Stdcode->".$this->stdcode." | Numbers->".implode(",",array_filter(array_unique($landline_err_arr['RULE_LANDLINE_LENGTH'])));
            						$this->error_array_field['block']['msg']['landline']  = "Length of Stdcode and Landline number should be 10 digit  : Stdcode->".$this->stdcode." | Numbers->".implode(",",array_filter(array_unique($landline_err_arr['RULE_LANDLINE_LENGTH'])));
            							break;
                                //~ case 'RULE_VIRTUAL_ERR':
                                    //~ $this->error_array['block']['msg'][]  = "This landline number (".implode(",",array_filter(array_unique($landline_err_arr['RULE_VIRTUAL_ERR']))).") is not allowed since same number exist in our virtual number series, Please change number";
                                        //~ break;
                                    //Please don't change above message,as it is being used somewhere.
            				}
            			}
            		}
    				$block_num	=	$this->blockNumber($landline_arr,'Phone');
    				if($block_num!=''){
    					$this->error_array['block']['msg'][]  = "These numbers cannot be added as its Blocked For Entry :".$block_num;
    					$this->error_array_field['block']['msg']['block_number']  = "These numbers cannot be added as its Blocked For Entry :".$block_num;
                        //Please don't change above message,as it is being used somewhere.
    				}
                 }
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($landline_arr);
        		if(count($result_arr)>0){
	        		$this->error_array['block']['msg'][]  = "Landline number has duplicate value : ".implode(",",$result_arr);	        		
	        		$this->error_array_field['block']['msg']['landline']  = "Landline number has duplicate value : ".implode(",",$result_arr);	        		
        		}
        	}       	
        	if($mobile!=''){        		
        		$mobile_err_arr  = array();
        		$mobile_err_arr  = array();
        		$mobile_arr	=	explode("|~|",$mobile);
        		if(count($mobile_arr)>0){
        			foreach ($mobile_arr as $mobile) {
        				if($mobile!=''){
	        				$mobile_err	=	$this->mobileValidation($mobile);
	        				if($mobile_err!=''){
	        					$mobile_err_arr[$mobile_err][] = $mobile;
	        				}
						}					      			
        			}        			 
        		}
        		if(count($mobile_err_arr)>0){
        			foreach ($mobile_err_arr as $rule => $value) {
        				switch ($rule){
        					case 'RULE_MOBILE_ERR':
        						$this->error_array['block']['msg'][]  = "Mobile number should be 10 digit starting from 6/7/8/9  : ".implode(",",array_filter(array_unique($mobile_err_arr['RULE_MOBILE_ERR'])));
        						$this->error_array_field['block']['msg']['mobile']  = "Mobile number should be 10 digit starting from 6/7/8/9  : ".implode(",",array_filter(array_unique($mobile_err_arr['RULE_MOBILE_ERR'])));
        							break;
        				}
        			}
        		}
        		$block_num	=	$this->blockNumber($mobile_arr,'Mobile');
				if($block_num!=''){
					$this->error_array['block']['msg'][]  = "These numbers cannot be added as its Blocked For Entry :".$block_num;
					$this->error_array_field['block']['msg']['block_number']  = "These numbers cannot be added as its Blocked For Entry :".$block_num;
                    //Please don't change above message,as it is being used somewhere.
				}
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($mobile_arr);
        		if(count($result_arr)>0){	        		
	        		$this->error_array['block']['msg'][]  = "Mobile number has duplicate value : ".implode(",",$result_arr);	        		
	        		$this->error_array_field['block']['msg']['mobile']  = "Mobile number has duplicate value : ".implode(",",$result_arr);	        		
        		}
        		
        		//5+ Mobile Number check
        		if(strtoupper($this->module) != 'DE' && !empty($this->parentid) && count($mobile_arr)>0)
        		{
					$five_mobile_arr	=	$this->five_plus_mobile_check(implode(',',$mobile_arr));
				}
        	}
        	if(count($mobile_arr)>0 ||count($landline_arr)>0){
				$virtual_num_arr = array();
				$virtual_num_arr =	$this->checkVirtulNumber($mobile_arr,$landline_arr);
				if(count($virtual_num_arr)>0){
					$this->error_array['block']['msg'][]  = "These numbers (".implode(",",$virtual_num_arr).") is not allowed since same number exist in our virtual number series, Please change number";
					$this->error_array_field['block']['msg']['block_number']  = "These numbers (".implode(",",$virtual_num_arr).") is not allowed since same number exist in our virtual number series, Please change number";
				}
			}
        	###############-----toll free----################################
        	if($tollfree!=''){
        		$tollfree_arr 	= array();
        		$tollfree_arr	=	explode("|~|",$tollfree);
        		$tollfree_err_arr = array();
        		if(count($tollfree_arr)>0){
        			foreach ($tollfree_arr as $tollfree) {
        				if($tollfree!=''){
        					$tollfree_err =	$this->tollfreeValidation($tollfree);
        					if($tollfree_err!=''){
        						$tollfree_err_arr[]= $tollfree;
        					}
        				}
        			}
        		}
        		if(count($tollfree_err_arr)>0){
        			$this->error_array['block']['msg'][]  =  "Tollfree number should be 8-13 digits starting from 1800/0008/1860 :".implode(",",$tollfree_err_arr);
        			$this->error_array_field['block']['msg']['tollfree']  =  "Tollfree number should be 8-13 digits starting from 1800/0008/1860 :".implode(",",$tollfree_err_arr);
        		}
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($tollfree_arr);
        		if(count($result_arr)>0){
	        		$this->error_array['block']['msg'][]  = "Tollfree number has duplicate value : ".implode(",",$result_arr);	        		
	        		$this->error_array_field['block']['msg']['tollfree']  = "Tollfree number has duplicate value : ".implode(",",$result_arr);	        		
        		}        		
        	}
        	###########################################################
        	###############-----FAX----##########################
        	if($fax!=''){
        		$fax_arr 	= 	array();
        		$fax_arr	=	explode("|~|",$fax);
        		$fax_err_arr = array();
        		if(count($fax_arr)>0){
        			foreach ($fax_arr as $fax) {
        				if($fax!=''){
        					$fax_err =	$this->faxValidation($fax);
        					if($fax_err!=''){
        						$fax_err_arr[$fax_err][]= $fax;
        					}
        				}
        			}
        		}
        		if(count($fax_err_arr)>0){
        			$this->error_array['block']['msg'][]  = "Length of Stdcode and Fax number should be 10 digit  : Stdcode->".$this->stdcode." | Numbers->".implode(",",array_filter(array_unique($fax_err_arr['RULE_FAX_LENGTH'])));            							
        			$this->error_array_field['block']['msg']['contact_no_Lengthh']  = "Length of Stdcode and Fax number should be 10 digit  : Stdcode->".$this->stdcode." | Numbers->".implode(",",array_filter(array_unique($fax_err_arr['RULE_FAX_LENGTH'])));            							
        		}
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($fax_arr);
        		if(count($result_arr)>0){
	        		$this->error_array['block']['msg'][]  = "Fax number has duplicate value : ".implode(",",$result_arr);	        		
	        		$this->error_array_field['block']['msg']['fax']  = "Fax number has duplicate value : ".implode(",",$result_arr);	        		
        		}        		
        	}
        	########################################################
        	if($email!=''){
        		$email_arr 	= array();
        		$email_err_arr = array();
        		$email_arr	=	explode("|~|",$email);
        		if(count($email_arr)>0){
        			foreach ($email_arr as $email) {
        				if($email!=''){
	        				$email_err = $this->emailValidation($email);
	        				if($email_err!=''){
	        					$email_err_arr[]= $email; 
	        				}   			
        				}
        			}
        			if(count($email_err_arr)>0){
        				$this->error_array['block']['msg'][]  = "Invalid Email address : ".implode(",",array_filter(array_unique($email_err_arr)));
        				$this->error_array_field['block']['msg']['email']  = "Invalid Email address : ".implode(",",array_filter(array_unique($email_err_arr)));
        			}
        			$result_arr = array();
	        		$result_arr =	$this->getDuplicateArr($email_arr);
	        		if(count($result_arr)>0){		        		
		        		$this->error_array['block']['msg'][]  = "Email address has duplicate value : ".implode(",",$result_arr);
		        		$this->error_array_field['block']['msg']['email']  = "Email address has duplicate value : ".implode(",",$result_arr);
		        		
	        		}
        		}        		       		
        	}
        	if($website!=''){

        		$website_arr 	= array();
        		$website_arr	=	explode("|~|",$website);
        		$web_err_arr 	= array();
        		/*if(count($website_arr)>2){
        			$this->error_array['block']['msg'][]  = "Website url contains more than two domain";
        		}*/
        		//else{
        			foreach ($website_arr as $website) {
        				if($website!=''){
	        				$web_rule_err = $this->websiteValidation($website);
	        				if($web_rule_err!=''){
	        					$web_err_arr[$web_rule_err][]= $website;
	        				}
	        			}   			
        			}
        		//}
        		//echo "<pre>";print_r($web_err_arr);
        		if(count($web_err_arr)>0){
        			foreach ($web_err_arr as $rule => $value) {
        				switch ($rule){
        					case 'RULE_WEB_INVALID':
        						$this->error_array['block']['msg'][]  = "Invalid Website address : ".implode(",",$web_err_arr['RULE_WEB_INVALID']);
        						$this->error_array_field['block']['msg']['website']  = "Invalid Website address : ".implode(",",$web_err_arr['RULE_WEB_INVALID']);
        							break;
        					case 'RULE_WEB_HTTP':        						
        						$this->error_array['block']['msg'][]  = "Website url contains http/https : ".implode(",",$web_err_arr['RULE_WEB_HTTP']);
        						$this->error_array_field['block']['msg']['website']  = "Website url contains http/https : ".implode(",",$web_err_arr['RULE_WEB_HTTP']);
								break;
							case 'RULE_WEB_DOT':        						
        						$this->error_array['block']['msg'][]  = "Website url should contains www. at first position : ".implode(",",$web_err_arr['RULE_WEB_DOT']);
								break;
							//~ case 'RULE_WEB_LENGTH':        						
        						//~ $this->error_array['block']['msg'][]  = "Website url should not contain more than 63 character exluding .com/.in/www.: ".implode(",",$web_err_arr['RULE_WEB_LENGTH']);
								//~ break;
								
        				}
        			}
        		}
        		$result_arr = array();
        		$result_arr =	$this->getDuplicateArr($website_arr);
        		if(count($result_arr)>0){	        		
	        		$this->error_array['block']['msg'][]  = "Website url has duplicate value : ".implode(",",$result_arr);
	        		$this->error_array_field['block']['msg']['website']  = "Website url has duplicate value : ".implode(",",$result_arr);
	        		
        		}      		
        	}
        	if($year_of_est!=''){
        		$this->year_of_est = $year_of_est;
        		$this->yearValidation();
        	}
        }



        if(count($this->error_array)>0){
        	$this->error_array['error']['code'] 	= 1;
        	$this->error_array_field['error']['code'] 	= 1;
        }
        else{
        	$this->error_array['error']['code'] 	= 0;
        	$this->error_array['error']['msg'] 		= 'Valid Data';	
        	
        	$this->error_array_field['error']['code'] 	= 0;
        	$this->error_array_field['error']['msg'] 	= 'Valid Data';	
        }
		if(isset($this->fieldwise) && $this->fieldwise == 1)	
		{		
			echo json_encode($this->error_array_field); 
			
		}	
		else 	
			echo json_encode($this->error_array); 
        if($this->debug){
			print"<pre>";print_r($this->debug_resp);
			print"<pre>";print_r($this->error_array_field);
		}
	}
    private function formatCompanyname($compname){                    
		$new_comp_disp = $this->formatCompName($compname); 
		//$new_comp_disp = trim($new_comp_disp);
		$c1 = preg_replace("/[\(\)]/","",$compname);
		$c2 = preg_replace("/[\(\)]/","",$new_comp_disp);				
		if($c1!=trim($c2))
		{
			$error_code = 1;	
		}
		else
		{
			$error_code = 0;
		}
       
		if($error_code==1){
			//$this->error_array['error']['code']  = 1;
			$this->error_array['format_action']['msg'][]  = $new_comp_disp;
			$this->error_array_field['format_action']['msg']['companyname']  = $new_comp_disp;
        }      
    }
   
    private function formatCompName($compname)
	{
		if(preg_match("/[^A-Za-z0-9()\s]/",$compname))
		{				
			return $compname;
		}
		else
		{	
			$parenthesis_flag = 0; 
			$bracket1 =	preg_match("/[(]/",$compname);
			$bracket2 =	preg_match("/[)]/",$compname);
			if($bracket1 && $bracket2){
				$parenthesis_flag = 1; 
				$compname =	preg_replace("/(\(\s)/","(",$compname);
				$compname =	preg_replace("/(\)\s)/",")",$compname);
				$compname = str_replace("("," ( ",$compname);
				$compname = str_replace(")"," ) ",$compname);
			}
			

			$compname = trim($compname);
					
			if(strlen($compname)>2){			
				$compname_arr 		= 	explode(" ", $compname);
				$compname_arr		=	array_filter($compname_arr);
				if(count($compname_arr) > 0){
					
					$compname_str 	= implode("','",$this->addslashesArr($compname_arr));
					$sqlCompList 	= "SELECT word FROM online_regis1.tbl_formatted_company_list WHERE word IN ('".$compname_str."') AND active_flag =1 "; 
					$resCompList 	= parent::execQuery($sqlCompList,$this->conn_idc);
					$word_arr		= array();
					$format_word_arr	=	array();
					if($resCompList && parent::numRows($resCompList)>0)
					{			
						while($row_comp_name = parent::fetchData($resCompList))
						{	
							$format_word_exact 	= trim($row_comp_name['word']);
							$format_word_lower	= strtolower($format_word_exact);
							$word_arr[$format_word_lower] = $format_word_exact;
						}
						if(count($word_arr)>0){
							$format_word_arr = array_keys($word_arr);
						}
					}
					$final_compname_arr = array();
					foreach($compname_arr as $compval) {
						$compval = strtolower($compval);
							
						if(in_array($compval,$format_word_arr))
							array_push($final_compname_arr, $word_arr[$compval]);
						else
							array_push($final_compname_arr, ucfirst($compval));	 
					}
					if(count($final_compname_arr)>0){
						$compname = implode(' ',$final_compname_arr);					
						$error_code = 0;					
					}				
				}			
			}
			if($parenthesis_flag == 1 ){
				$compname = str_replace("( ","(",$compname);
				$compname = str_replace(") ",")",$compname);
				$compname = str_replace(" (","(",$compname);
				$compname = str_replace(" )",")",$compname);
				$compname = str_replace("("," (",$compname);
				$compname = str_replace(")",") ",$compname);
			}
			return $compname;		
		}		
	}
	private	function addslashesArr($resultArray)
	{
		foreach($resultArray AS $key=>$value)
		{
			$resultArray[$key] = addslashes(stripslashes(trim($value)));
		}
		
		return $resultArray;
	}
	private function companyNameValidation(){
		
		if($this->debug==1){
			$comp_st1 = date('H:i:s');
			$this->debug_resp['COMP - Start Time'] = $comp_st1;
		}
		if($this->compname==''){
			$this->error_array['block']['msg'][]  = "Company name is blank";
			$this->error_array_field['block']['msg']['companyname']  = "Company name is blank";
		}
		$spec_char_chk = array("'","&","(",")",".","-","!");
		
		
		$compfirst = substr($this->compname, 0, 1);
		
		if(in_array($compfirst,$spec_char_chk)){
			$this->error_array['block']['msg'][]  = "Company name starting with special characters";

			$this->error_array_field['block']['msg']['companyname']  = "Company name starting with special characters";
			return;
		}
		$compname_ws	=	preg_replace('/\s/','',$this->compname);	
        $pattern =   "/[&'().-]{2}$/";
        $match_flag =   $this->patternMatch($pattern,$compname_ws);
        if($match_flag ==1){
            $this->error_array['block']['msg'][]  = "Company name can not contain more than one special characters at the end.";
            $this->error_array_field['block']['msg']['companyname']  = "Company name can not contain more than one special characters at the end.";
        }
		if(strlen($this->compname)>120){
			$this->error_array['block']['msg'][]  = "Company name is more than 120 characters";
			$this->error_array_field['block']['msg']['companyname']  = "Company name is more than 120 characters";
		}
		#-------------------------------------------------------
		preg_match_all('!\d+!', $this->compname, $matches);
		$result = 0;
		foreach($matches as $key => $value){
			foreach($value as $val){
				$result = $result + strlen($val);
			}
		}
		if($result>6){
			$this->error_array['block']['msg'][]  = "Company name contains more than 6 numbers";
			$this->error_array_field['block']['msg']['companyname']  = "Company name contains more than 6 numbers";
		}
		#--------------------------------------------------------
		$str 		 =	preg_replace('/\s+/','',$this->compname);
		$number_flag =	$this->numberCheckInName($str);
		if($number_flag == 1){
			$this->error_array['block']['msg'][]  = "Company name contains more than 4 repeated numbers";
			$this->error_array_field['block']['msg']['companyname']  = "Company name contains more than 4 repeated numbers";
		}
		#--------------------------------------------------------
		
        $str_without_spl         =  preg_replace('/[^a-z|A-Z|0-9]/','',$this->compname);
        
        if(strlen($str_without_spl)<1){
            $this->error_array['block']['msg'][]  = "Company name is Invalid";
            $this->error_array_field['block']['msg']['companyname']  = "Company name is Invalid";
        }else if(strlen($str_without_spl)<3){
			$this->error_array['block']['msg'][]  = "Company name is less than 3 characters";
			$this->error_array_field['block']['msg']['companyname']  = "Company name is less than 3 characters";
		}
		#--------------------------------------------------------
		$str 			=	preg_replace('/\s+/','',$this->compname);
		$match_flag 	=	$this->repeatedCharValidation($str);
		if($match_flag==1){
			$this->error_array['block']['msg'][]  = "Company name contains more than 4 repeated characters";
			$this->error_array_field['block']['msg']['companyname']  = "Company name contains more than 4 repeated characters :".$this->compname;
		}
		#--------------------------------------------------------
		$pattern 	= "/[^a-zA-z0-9\s&'()@.!-]|([_^`\]\[])/";
		$match_flag	=	$this->patternMatch($pattern,$this->compname);
		if($match_flag==1){
			$this->error_array['block']['msg'][]  = "Company name contains special characters";
			$this->error_array_field['block']['msg']['companyname']  = "Company name contains special characters :".$this->compname;
		}else{
			$pattern 	= "/\\\\/";
			$match_flag	=	$this->patternMatch($pattern,$this->compname);
			if($match_flag==1){
				$this->error_array['block']['msg'][]  = "Company name contains special characters";
				$this->error_array_field['block']['msg']['companyname']  = "Company name contains special characters :".$this->compname;
			}
		}
		#--------------------------------------------------------
		$pattern =	"/www\.|https?/";
		$match_flag	=	$this->patternMatch($pattern,$this->compname);
		if($match_flag ==1){
			$this->error_array['block']['msg'][]  = "Company name contains www./http";
			$this->error_array_field['block']['msg']['companyname']  = "Company name contains www./http :".$this->compname;
		}
		#---------------------------------------------------------
		if($this->module!='MEP'){
	    	$match_brand_flag = $this->checkBrandname();
	    	if($match_brand_flag==1){
	    		$this->error_array['prompt']['msg'][]  = "Company name matches with Brand name";
	    		$this->error_array_field['prompt']['msg']['companyname']  = "Company name matches with Brand name :".$this->compname;
			}
		}
		if($this->debug){
			$comp_tkn_tm1 =  strtotime(date('H:i:s')) - strtotime($comp_st1);
			$comp_tkn_tm1 =  gmdate("H:i:s", $comp_tkn_tm1);
			$this->debug_resp['CMP1']['action'] 		= "Company Validation Till Brand Name Check";
			$this->debug_resp['CMP1']['timespend'] 		= $comp_tkn_tm1;
		}
		if($this->catchkignore !=1 && $this->excl_cat_flag!=1){
			$cat_match_flag = $this->checkCategoryName($this->compname);
			if($cat_match_flag!=1){
				$str_wt_spl 	= preg_replace('/[^a-z|A-Z|0-9\s]/', ' ',$this->compname); 
				$cat_match_flag = $this->checkCategoryName($str_wt_spl);
			}
		}
		if($cat_match_flag==1){
			$this->error_array['block']['msg'][]  = "Company name matches with Category name";
			$this->error_array_field['block']['msg']['companyname']  = "Company name matches with Category name :".$this->compname;
		}
		#---------------------------------------------------------
		if($this->catchkignore !=1 && $this->excl_cat_flag!=1){
			$cat_syno_match	=	$this->checkCatSynonym($this->compname);
			if($cat_syno_match!=1){
				$syno_wt_spl 	= preg_replace('/[^a-z|A-Z|0-9\s]/', ' ',$this->compname); 
				$cat_syno_match = $this->checkCatSynonym($syno_wt_spl);
			}
		}
		if($cat_syno_match==1){
			$this->error_array['block']['msg'][]  = "Company name matches with Category synonym";
			$this->error_array_field['block']['msg']['companyname']  = "Company name matches with Category synonym :".$this->compname;
		}
		
		if($this->debug){
			$comp_tkn_tm2 =  strtotime(date('H:i:s')) - strtotime($comp_st1);
			$comp_tkn_tm2 =  gmdate("H:i:s", $comp_tkn_tm2);
			$this->debug_resp['CMP2']['action'] 		= "Company Validation Till Category";
			$this->debug_resp['CMP2']['timespend'] 		= $comp_tkn_tm2;
		}
		#---------------------------------------------------------
		$this->checkProfainWord($this->compname);
		
		if($this->debug){
			$comp_tkn_tm3 =  strtotime(date('H:i:s')) - strtotime($comp_st1);
			$comp_tkn_tm3 =  gmdate("H:i:s", $comp_tkn_tm3);
			$this->debug_resp['CMP3']['action'] 		= "Company Validation Till Profain Word";
			$this->debug_resp['CMP3']['timespend'] 		= $comp_tkn_tm3;
		}
		#=--------------------------------------------------------
		$city_match_flag =	$this->checkCityName($this->compname);
		if($city_match_flag==1){
			$this->error_array['block']['msg'][]  = "Company name matches with City name";
			$this->error_array_field['block']['msg']['companyname']  = "Company name matches with City name";
		}
		$state_match_flag =	$this->checkStateName($this->compname);
		if($state_match_flag==1){
			$this->error_array['block']['msg'][]  = "Company name matches with State name";
			$this->error_array_field['block']['msg']['companyname']  = "Company name matches with State name";
		}
		if($this->module=='DE'){
			$this->compSynonym();
		}
		$this->checkNonUTFChar($this->compname);
	}
	
	private function compSynonym(){
		$match_flag =0 ;		
		if($this->parentid!=''){
			$business_name_ws   = str_replace(' ','',$this->compname); 
						
			$sql_comp_syno =	"SELECT synname_singular as synname, synname_without_space FROM tbl_compsyn WHERE parentid ='".$this->parentid."' ";
			$res_comp_syno = parent::execQuery($sql_comp_syno,$this->conn_local);
			if(parent::numRows($res_comp_syno)>0){
				while ($row_comp_syno = parent::fetchData($res_comp_syno)){
					$ext_synname 	= trim($row_comp_syno['synname']);
					$ext_synname_ws = trim($row_comp_syno['synname_without_space']);
					
					if(strtolower($ext_synname) == strtolower($this->compname)){
						$match_flag = 1;
						break;		
					}
					else if(strtolower($ext_synname_ws) == strtolower($business_name_ws)){
						$match_flag = 1;
						break;
					}
				}
			}
			if($match_flag ==1 ){
				$this->error_array['block']['msg'][]  =  "Please change company name as it already exists as company synonym.";
			}
		}		
	}
	
	private function checkHomeKeyChar(){
		
		if(!empty($this->compname))
		{
				$compname = $this->compname;
				$sql_get_homekey	=	"SELECT word FROM online_regis1.tbl_cmp_prefix_restrict_word WHERE  word_type='home_key' AND active_flag=1";
				$res_get_homekey = parent::execQuery($sql_get_homekey, $this->conn_idc); 
				
				$homekey_character_arr	=	array();
				if(parent::numRows($res_get_homekey)>0)
				{
					while($rows_get_homekey = parent::fetchData($res_get_homekey))
					{
						$homekey_character_arr[]	=	$rows_get_homekey['word'];
					}
				}
				
				$homekey_char_flag = 0;
				foreach($homekey_character_arr as $key_homekey =>$val_homekey)
				{
					if(stripos($compname, $val_homekey) !== false)
					{
						$homekey_char_flag	=	1;
						break;
					}
				}
				if($homekey_char_flag	== 1)
				{
						$this->error_array['block']['msg'][]  = "Enter Proper Company Name as it Contains Invalid Word : ".$val_homekey.".";
						$this->error_array_field['block']['msg']['companyname']  = "Enter Proper Company Name as it Contains Invalid Word : ".$val_homekey.".";
				}		
		}
		
	}
	
	
	private function contractExclusionInfo()
	{
		if(!empty($this->parentid)){
			$sql_comp_tag = "SELECT GROUP_CONCAT(reasonid) as reasonid FROM tbl_contract_bypass_exclusion WHERE parentid = '".$this->parentid."' AND reasonid IN(5,6)"; 		
			$res_comp_tag = parent::execQuery($sql_comp_tag,$this->conn_iro);
			if($res_comp_tag && parent::numRows($res_comp_tag)>0){
				$row_comp_tag = parent::fetchData($res_comp_tag);
				$reasonid = trim($row_comp_tag['reasonid']);
				$reasonid_arr = array();
				$reasonid_arr = explode(",",$reasonid);
				$reasonid_arr = array_filter($reasonid_arr);
				if(count($reasonid_arr)>0){
					if(in_array(5,$reasonid_arr)){
						$this->catchkignore = 1; // Co. Category exclusion
					}
					if(in_array(6,$reasonid_arr)){
						$this->compname_univ_flag = 1; // Company Name Universal
					}
				}				
			}
		}
		if($this->compname_univ_flag!=1){
			$sql_comp_list  =	"SELECT word FROM online_regis1.tbl_compname_exclusion WHERE word ='".$this->compname."' AND active_flag =1 ";
			$res_comp_list  =  parent::execQuery($sql_comp_list,$this->conn_idc);
			if($res_comp_list && parent::numRows($res_comp_list)>0){
				$this->compname_univ_flag = 1;
			}
		}
	}
	private function checkCatSynonym($compname){
		$cat_syno_match = 0;
		if($compname!=''){
			//$business_name 		= preg_replace('/[^a-z|A-Z|0-9\s]/', '',$compname); 
			$business_name      = trim($compname);
			$business_name      = preg_replace('/\s+/', ' ', $business_name);
			$business_name_ws   = str_replace(' ','',$business_name); 
			
			$sql_cat_synonym =	"SELECT GROUP_CONCAT(national_catid) as national_catid FROM tbl_synonym WHERE (synonym_name = '".$this->stringProcess($business_name)."' OR synname_search_processed_ws = '".$this->stringProcess($business_name_ws)."') AND active_flag = 1 LIMIT 1";
			$res_cat_synonym = parent::execQuery($sql_cat_synonym,$this->conn_local);
			if($this->debug){
				echo "<hr><br> Query : ".$sql_cat_synonym;
				echo "<br><br> Response : ".$res_cat_synonym;
				echo "<br><br><br>";
			}
			if($res_cat_synonym && parent::numRows($res_cat_synonym)>0){
				$row_synonm =    parent::fetchData($res_cat_synonym);
				$national_catid =   trim($row_synonm['national_catid']);
				
				if($national_catid){
					$catsyn_natcatid_arr = array();
					$catsyn_natcatid_arr = explode(",",$national_catid);
					$catsyn_natcatid_arr = array_unique(array_filter($catsyn_natcatid_arr));
					if(count($catsyn_natcatid_arr)>0){
						$chainout_natcatid_arr = array();
						$chainout_natcatid_arr = $this->getChainOutletsCat($catsyn_natcatid_arr);
						
						$syn_matched_cat_arr = array();
						$syn_matched_cat_arr = array_diff($catsyn_natcatid_arr,$chainout_natcatid_arr);
						if(count($syn_matched_cat_arr)>0){
							$cat_syno_match = 1;
						}
					}
				}
			}
		}
		return $cat_syno_match;
	}

    private function getChainOutletsCat($nat_catids_arr){
		$chain_outlets_arr = array();;
		if(count($nat_catids_arr)>0){
			$nat_catids_str = implode(",",$nat_catids_arr);
			//$sql_chain_outlet_cat  =  "SELECT GROUP_CONCAT(national_catid) as national_catid FROM d_jds.tbl_categorymaster_generalinfo WHERE national_catid IN (".$nat_catids_str.") AND miscellaneous_flag&16=16 LIMIT 1";
			//$res_chain_outlet_cat  	=   parent::execQuery($sql_chain_outlet_cat,$this->conn_local);
            $cat_params = array();
            $cat_params['page']         = 'global_company_api';
            $cat_params['skip_log']     = '1';
            $cat_params['data_city']    = $this->data_city;
            $cat_params['return']       = 'national_catid';

            $where_arr      =   array();
            if($nat_catids_str!=''){
                $where_arr['national_catid']         = $nat_catids_str;
                $where_arr['miscellaneous_flag']     = "16";
                $cat_params['where']        = json_encode($where_arr);
                $cat_res    =   $this->categoryClass_obj->getCatRelatedInfo($cat_params);
            }
            $cat_res_arr = array();
            if($cat_res!=''){
                $cat_res_arr =  json_decode($cat_res,TRUE);
            }
            $national_catid_arr  =array();
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				//$row_chain_outlet 	= 	parent::fetchData($res_chain_outlet_cat);
                foreach ($cat_res_arr['results'] as $key => $cat_arr) {
                    $national_catid     =   trim($cat_arr['national_catid']);
                    if($national_catid!=''){
                        $national_catid_arr[] = $national_catid;
                    }
                }
				
				if(count($national_catid_arr)>0){
					//$chain_outlets_arr = explode(",",$national_catid);
					$chain_outlets_arr = array_unique(array_filter($national_catid_arr));
				}
			}
		}
		return $chain_outlets_arr;
    }
	private function checkCategoryName($str){
		$match_flag = 0;
		if($str!=''){
			//$str 			= preg_replace('/[^a-z|A-Z|0-9\s]/', '',$str); 
			$business_name 	= trim($str);
			$business_name 	= preg_replace('/\s+/', ' ', trim($str)); 
			$b1				= $this->getSingular(strtolower(trim($str)));
			$b1_ws 			= str_replace(' ','',$b1);
			
			if(strtolower($business_name) == strtolower($b1)){
				$wherecond = "(category_name = '".$this->stringProcess($business_name)."' 
								OR catname_search_processed_ws = '".$this->stringProcess($b1_ws)."'  
								OR catname_search_processed = '".$this->stringProcess($b1)."') AND miscellaneous_flag&16!=16 LIMIT 1";
			}else{
				$wherecond = "(category_name = '".$this->stringProcess($business_name)."' 
								OR category_name = '".$this->stringProcess($b1)."' 
								OR catname_search_processed_ws = '".$this->stringProcess($b1_ws)."'  
								OR catname_search_processed = '".$this->stringProcess($b1)."') AND miscellaneous_flag&16!=16 LIMIT 1";
			}
			//$sql_cat_check = "SELECT category_name FROM tbl_categorymaster_generalinfo WHERE ".$wherecond;
			//$res_cat_check	=	parent::execQuery($sql_cat_check,$this->conn_local);
            $cat_params = array();
            $cat_params['page']         = 'global_company_api';
            $cat_params['skip_log']     = '1';
            $cat_params['data_city']    = $this->data_city;
            $cat_params['return']       = 'category_name';
            $cat_params['limit']        = '1';

            $where_arr      =   array();
            if($business_name!=''){
                $where_arr['category_name']         = $this->stringProcess($business_name);
                $where_arr['miscellaneous_flag']    = "!16";
                $cat_params['where']        = json_encode($where_arr);
                $cat_res    =   $this->categoryClass_obj->getCatRelatedInfo($cat_params);
            }
            $cat_res_arr = array();
            if($cat_res!=''){
                $cat_res_arr =  json_decode($cat_res,TRUE);
            }

			if($this->debug){
				echo "<hr><br> Query : ".$sql_cat_check;
				echo "<br><br> Response : ".$res_cat_check;
				echo "<br><br><br>";
			}
			if($cat_res_arr['errorcode'] =='0' && count($cat_res_arr['results'])>0){
				$match_flag = 1;
			}
		}
		return $match_flag;
	}

	private function getSingular($str = ''){
		$s = array();
		$t = explode(' ',$str);
		$e = array('shoes'=>'shoe','shoe'=>'shoes',
					'glasses'=>'glass','glass'=>'glasses',
					'mattresses'=>'mattress','mattress'=>'mattresses',
					'watches'=>'watch','watch'=>'watches',
					'classes'=>'class','class'=>'classes');
		$r = array('ss'=>false,'os'=>'o','ies'=>'y','xes'=>'x','oes'=>'o','ies'=>'y','ves'=>'f','s'=>'');
		foreach($t as $v){
			if(strlen($v)>=4){
				$f = false;
				foreach(array_keys($r) as $k){
					if(substr($v,(strlen($k)*-1))!=$k){
						continue;
					}
					else{
						$f = true;
						if(array_key_exists($v,$e))
							$s[] = $e[$v];
						else
							$s[] = substr($v,0,strlen($v)-strlen($k)).$r[$k];

						break;
					}
				}
				if(!$f){
					$s[] = $v;
				}
			}
			else{
				$s[] = $v;
			}
		}
		return implode(' ',$s);
	}

	private function checkStateName($state){
		$state_match_flag = 0;

		$sql_state_check =	"SELECT state FROM tbl_zone_cities WHERE state='".$this->stringProcess($state)."' LIMIT 1 ";
		$res_state_check	=	parent::execQuery($sql_state_check,$this->conn_local);
		if(parent::numRows($res_state_check)>0){
			$state_match_flag = 1;
		}
		return $state_match_flag;
	}
	private function checkCityName($city){
		$city_match_flag = 0;

		$sql_city_check =	"SELECT ct_name,display_flag FROM d_jds.city_master WHERE ct_name='".$this->stringProcess($city)."' AND display_flag=1 ";
		$res_city_check	=	parent::execQuery($sql_city_check,$this->conn_local);
		if(parent::numRows($res_city_check)>0){
			$city_match_flag = 1;
		}
		return $city_match_flag;
	}
	private function checkBrandname(){
		$compname = $this->compname;
		$matched_flag = 0;	
		if(!empty($compname))
		{
			$companystr 	= $this->fn_stemming($compname);
			$sql_brand_name = "SELECT GROUP_CONCAT(brand_name separator '|~|') as brand_name, GROUP_CONCAT(source separator '|~|') as source FROM tbl_brand_names WHERE MATCH(brand_name) AGAINST('".$companystr."' IN BOOLEAN MODE) LIMIT 1";
			$res_brand	= parent::execQuery($sql_brand_name,$this->conn_iro);

			if(parent::numRows($res_brand)>0){				
				$row 		= parent::fetchData($res_brand);
				$brand_name = trim($row['brand_name']);
                if($brand_name!=''){
    				$brand_name = strtolower($brand_name);
    				$source 	= trim($row['source']);
    				$brand_name_arr = explode("|~|",$brand_name);
    				$source_arr = explode("|~|",$source);
    				$matched_brand = '';
    				$matched_source = ''; 
    				if(count($brand_name_arr)>0){
    					foreach($brand_name_arr as $key => $value){
    						if(strpos($companystr, $value) !== false) {
    							$matched_brand = $value;
    							$matched_source = $source_arr[$key];
    							break;
    						}
    					}
    				}
    				if($matched_brand){
    					$matched_flag = 1;
    				}
                }
			}
		}
		return $matched_flag;
	}
	private function fn_stemming($word){		
		$string = strtolower($word); 
		$word = preg_replace("/[^A-Za-z0-9\s]/", " ", $string);
		return $word;
	}
	
	private function numberCheckInName($string){
		$numbers = preg_replace("/[^0-9]/","",$string);
		$str_arr	=	str_split($numbers);
		$matches 	= array_count_values($str_arr);

		$error_flag = 0;
		//echo preg_match_all('/(.{10,})\\1{2,}/', $string,$matches);
		//echo "<pre>";print_r($matches);
		foreach ($matches as $key => $value) {
			if($value>4){				
				$error_flag = 1;
				break;
			}
		}
		return $error_flag;
	}

	private function get_paid_status()
	{
		require_once('includes/contract_type_class.php');
		
		global $params;
		$params				   	=   array();	
		$params['rquest']		=	'get_contract_type';
		$params['data_city']	=	$this->data_city;
		$params['parentid']		=	$this->parentid;
		
		$contract_type_class_obj  	= new contract_type_class($params);
		$contract_type_info_arr 	= $contract_type_class_obj->fetch_contract();
		return $contract_type_info_arr;
	}

	private function five_plus_mobile_check($mobile)
	{
		require_once('includes/mobile_check_class.php');
		
		global $params;
		$params				   	=   array();	
		$params['rquest']		=	'mobile_employee_check';
		$params['data_city']	=	$this->data_city;
		$params['module']		=	$this->module;
		$params['mobile']		=	$mobile;
		$params['parentid']		=	$this->parentid;
		
		$mobile_check_class_obj = 	new mobile_check_class($params);
		$res_arr 				=	$mobile_check_class_obj->fetch_mobile();
		$five_plus_mobile_arr = array();
		if(!empty($res_arr) && count($res_arr['data'])>0)
		{
			foreach($res_arr['data'] as $key => $val)
			{
				if($val['company_count']>=5)
				{
					$five_plus_mobile_arr[] = $key;
				}
			}
		}
		
		if(count($five_plus_mobile_arr)>0)
		{
			$paid_status_arr	=	$this->get_paid_status();
			if(!empty($paid_status_arr) && $paid_status_arr['result']['paid'] == '0')
				$this->error_array['prompt']['msg'][]  = 'The mobile number '.implode(',',$five_plus_mobile_arr).' is present in 5 and more active contracts, this will be updated after moderation.';
			else
				$this->error_array['prompt']['msg'][]  = 'The mobile number '.implode(',',$five_plus_mobile_arr).' is present in 5 and more active contracts, this will be reviewed by database team.';	
		}
	} 

	private function checkProfainWord($str,$field=''){ 
		
		require_once('includes/location_class.php');
		
		global $params;
		$params				   	=   array();	
		$params['rquest']		=	'badword_check';
		$params['companyname']	=	urlencode($str);
		$params['data_city']	=	urlencode($this->data_city);
		$params['parentid']		=	$this->parentid;
		$params['module']		=	$this->module;
		
		$location_class_obj  	= 	new location_class($params);
		$res_arr 				=	$location_class_obj->fetch_details();
		
		if($res_arr['result']['allow_flag']==2){
			if($field == 'building_name')
				$msg	=	str_replace('company name','building name',$res_arr['result']['msg']);
			elseif($field == 'landmark')
				$msg	=	str_replace('company name','landmark',$res_arr['result']['msg']);
			elseif($field == 'street')
				$msg	=	str_replace('company name','street',$res_arr['result']['msg']);	
			else
				$msg	=	$res_arr['result']['msg'];		
							
            $this->error_array['block']['msg'][]  = $msg;
            $this->error_array_field['block']['msg']['companyname']  = $msg;
		}
        if($res_arr['result']['allow_flag']==1){
            if($field=='' && ($this->module=='CS' || $this->module=='TME' || $this->module=='ME' )){
                $this->error_array['prompt']['msg'][]  = $res_arr['result']['msg'];
                $this->error_array_field['prompt']['msg'][]  = $res_arr['result']['msg'];
            }
        }			
	}
    private function checkNonUTFChar($compname){
        $flag=0;
        if(preg_match('/[\x00-\x1F\x80-\xFF]/', $compname)){
            $flag=1;    
        }
        if($flag==1){
            $this->error_array['block']['msg'][]       = "Companyname contains Non-UTF characters \r\nkindly re-enter companyname manually";
            $this->error_array_field['block']['msg']['companyname']       = "Companyname contains Non-UTF characters \r\nkindly re-enter companyname manually";
        }
    }
	private function curlPostData($curlurl,$data_arr){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $curlurl);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_arr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$content  = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	private function addressValidation(){
		$digit_pattern = '/([^A-Za-z])(\1{6,})/';

		$address_length	=	strlen($this->building_name)+strlen($this->street)+strlen($this->landmark);
		if($address_length<5){
			$this->error_array['block']['msg'][]  = "Total length of Building,Street,Landmark is less than 5 characters";
			$this->error_array_field['block']['msg']['address']  = "Total length of Building,Street,Landmark is less than 5 characters";
		}
        
        if($address_length>250){
            $this->error_array['block']['msg'][]  = "Total length of Building,Street,Landmark should be less than 250 characters";
            $this->error_array_field['block']['msg']['address']  = "Total length of Building,Street,Landmark should be less than 250 characters";
        }
		if($this->building_name!=''){
			$bldg_wt_spl =	preg_replace('/[^a-zA-Z0-9]/','',$this->building_name);
			if($bldg_wt_spl==''){
				$this->error_array['block']['msg'][]  = "Building name contains only special characters :".$this->building_name;
				$this->error_array_field['block']['msg']['building_name']  = "Building name contains only special characters :".$this->building_name;
			}
			$building_name =	preg_replace('/\s+/','',$this->building_name);
			$match_flag =	$this->repeatedCharValidation(strtolower($building_name));
			if($match_flag==1){
				$this->error_array['block']['msg'][]  = "Building name contains more than 4 repeated alphabets";
				$this->error_array_field['block']['msg']['building_name']  = "Building name contains more than 4 repeated alphabets :".$this->building_name;
			}			
			$match_flag	=	$this->patternMatch($digit_pattern,$building_name);
			if($match_flag == 1){
				$this->error_array['block']['msg'][]  = "Building name contains more than 6 repeated digits";
				$this->error_array_field['block']['msg']['building_name']  = "Building name contains more than 6 repeated digits :".$this->building_name;
			}
			
			$this->checkProfainWord($this->building_name,'building_name');		
		}
		if($this->landmark!=''){
			$landmark_wt_spl =	preg_replace('/[^a-zA-Z0-9]/','',$this->landmark);
			if($landmark_wt_spl==''){
				$this->error_array['block']['msg'][]  = "Landmark name contains only special characters";
				$this->error_array_field['block']['msg']['landmark']  = "Landmark name contains only special characters";
			}
			$landmark 	=	preg_replace('/\s+/','',$this->landmark);
			$match_flag =	$this->repeatedCharValidation($landmark);
			if($match_flag==1){
				$this->error_array['block']['msg'][]  = "Landmark name contains more than 4 repeated alphabets";
				$this->error_array_field['block']['msg']['landmark']  = "Landmark name contains more than 4 repeated alphabets :".$this->landmark;
			}
			$match_flag	=	$this->patternMatch($digit_pattern,$landmark);
			if($match_flag == 1){
				$this->error_array['block']['msg'][]  = "Landmark name contains more than 6 repeated digits";
				$this->error_array_field['block']['msg']['landmark']  = "Landmark name contains more than 6 repeated digits :".$this->landmark;
			}
			$this->checkProfainWord($this->landmark,'landmark');			
		}
		if($this->street!=''){
            $street_length    =   strlen($this->street);
            if($street_length<4){
                $this->error_array['block']['msg'][]  = "Street name should not be less than 4 Characters.";
            }

			$street_wt_spl =	preg_replace('/[^a-zA-Z0-9]/','',$this->street);
			if($street_wt_spl==''){
				$this->error_array['block']['msg'][]  = "Street name contains only special characters";
				$this->error_array_field['block']['msg']['street']  = "Street name contains only special characters :".$this->street;
			}
            preg_match_all('/[^a-zA-Z0-9\s]/',$this->street,$street_spl);
            if(count($street_spl['0'])>4){
                $this->error_array['block']['msg'][]  = "Street name should not contains more than 4 special characters";
            }
			$street 	=	preg_replace('/\s+/','',$this->street);
			$match_flag =	$this->repeatedCharValidation($street);
			if($match_flag==1){
				$this->error_array['block']['msg'][]  = "Street name contains more than 4 repeated alphabets";
				$this->error_array_field['block']['msg']['street']  = "Street name contains more than 4 repeated alphabets :".$this->street;
			}
			$match_flag	=	$this->patternMatch($digit_pattern,$street);
			if($match_flag == 1){
				$this->error_array['block']['msg'][]  = "Street name contains more than 6 repeated digits";
				$this->error_array_field['block']['msg']['street']  = "Street name contains more than 6 repeated digits :".$this->street;
			}
			$this->checkProfainWord($this->street,'street');	
		}
		if((strtolower($this->building_name)==strtolower($this->street))&&  $this->building_name!='' && $this->street!=''){
			$this->error_array['block']['msg'][]  = "Building name and Street name should not be same";
			$this->error_array_field['block']['msg']['building_name']  = "Building name and Street name should not be same :".$this->building_name;
		}
		else if((strtolower($this->landmark)==strtolower($this->street)) && $this->landmark!='' && $this->street!=''){
			$this->error_array['block']['msg'][]  = "Landmark name and Street name should not be same";
			$this->error_array_field['block']['msg']['landmark']  = "Landmark name and Street name should not be same :".$this->landmark;
		}
		else if((strtolower($this->building_name)==strtolower($this->landmark))&& $this->landmark!='' && $this->building_name!=''){
			$this->error_array['block']['msg'][]  = "Building name and Landmark name should not be same";
			$this->error_array_field['block']['msg']['building_name']  = "Building name and Landmark name should not be same :".$this->building_name;
		}
	}

	private function repeatedCharValidation($str){		
		$pattern =	'/([A-Za-z])(\1{4,})/';
		return $this->patternMatch($pattern,$str);		
	}
	private function area_HO_GPO_Validation(){
		if(!empty($this->parentid))
		{
            $row_area_check =   array();
            $cat_params = array();
            $cat_params['data_city']    = $this->data_city;
            $cat_params['table']        = 'gen_info_id';
            $cat_params['module']       = $this->module;
            $cat_params['parentid']     = $this->parentid;
            $cat_params['action']       = 'fetchdata';
            $cat_params['fields']       = 'area';
            $cat_params['page']         = 'global_api';
            $cat_params['skip_log']     = 1;

            $res_gen_info1      =   array();
            $res_gen_info1      =   json_decode($this->companyClass_obj->getCompanyInfo($cat_params),true);
			
            if(!empty($res_gen_info1) && $res_gen_info1['errors']['code']==0){
                
                $row_area_check         =   $res_gen_info1['results']['data'][$this->parentid];
				
                if(strtolower(trim($row_area_check['area'])) != strtolower($this->area))
                {
                    if(preg_match("/( HO| H.O.| GPO| G.P.O.)$/",strtoupper($this->area)))
                    {
                        $this->error_array['block']['msg'][]  = "Area should not be contain GPO/HO : ".$this->area;
                        $this->error_array_field['block']['msg']['area']  = "Area should not be contain GPO/HO : ".$this->area;
                    }   
                    elseif(preg_match("/( H O| H. O.| G P O| G. P. O.)/",strtoupper($this->area)))
                    {
                        $this->error_array['block']['msg'][]  = "Area should not be contain GPO/HO : ".$this->area;
                        $this->error_array_field['block']['msg']['area']  = "Area should not be contain GPO/HO : ".$this->area;
                    }   
                }
            }	
			
		}
	}
	private function pincodeValidation($check_pincode){
		$pattern =	"/^\d{6}$/";
		$match_flag	=	$this->patternMatch($pattern,$this->pincode);
		if($match_flag !=1){
			$this->error_array['block']['msg'][]  = "Pincode should be 6 digit numbers :".$this->pincode;
			$this->error_array_field['block']['msg']['pincode']  = "Pincode should be 6 digit numbers :".$this->pincode;
		}
		if($check_pincode == 1){
			$sql_valid_pin =  "SELECT  pincode FROM  tbl_areamaster_consolidated_v3 WHERE data_city ='".$this->data_city."'  AND display_flag =1 AND type_flag=1 AND pincode= '".$this->pincode."' LIMIT 1";
			$res_valid_pin = parent::execQuery($sql_valid_pin,$this->conn_local);			
			if(parent::numRows($res_valid_pin)<=0){
				$this->error_array['block']['msg'][]  = "Pincode ".$this->pincode." is not exist for city ".$this->data_city;
				$this->error_array_field['block']['msg']['pincode']  = "Pincode ".$this->pincode." is not exist for city ".$this->data_city;
			}
		}
	}
	private function contactPersonValidation($contact_person){
		$error_rule = ''; 
		$pattern 	= "/[^a-zA-z\s]/";
		$match_flag = $this->patternMatch($pattern,$contact_person);
		if($match_flag==1){
			$error_rule		= 'RULE_CONTACT_ALPHA';			
		}
		$contact_person =	preg_replace('/\s+/','',$contact_person);
		$match_flag	=	$this->repeatedCharValidation($contact_person);
		if($match_flag == 1){
			$error_rule		= 'RULE_CONTACT_4_ALPHA';
			//$this->error_array['block']['msg'][]  = "Contact person name contains more than 4 repeated alphabets";
		}
		return $error_rule;
	}
	private function designationValidation($designation_arr){
		$error_arr = array();
		foreach ($designation_arr as $key => $designation) {
			if($designation!=''){
				$numbers = preg_replace("/[0-9]/","",$designation);

				$str_arr	=	str_split($numbers);
				$matches 	=   array_count_values($str_arr);
				
				foreach ($matches as $key => $value) {
					if($value>4){
						$error_arr[$designation] = $designation;
						break;
					}
				}
			}
		}
		//echo "<pre>";print_r($error_arr);	
		if(count($error_arr)> 0){
			$this->error_array['block']['msg'][]  = "Designation contains more than 4 repeated characters :".implode(",",$error_arr);
			$this->error_array_field['block']['msg']['designation']  = "Designation contains more than 4 repeated characters :".implode(",",$error_arr);
		}		
	}
	private function landlineValidation($land_line){
		$error_rule = '';
		if($this->stdcode!=''){
			$total_length = strlen($this->stdcode)+strlen($land_line);
			//echo "<br>";
			if($total_length!=10){
				$error_rule = 'RULE_LANDLINE_LENGTH';
			}
		}
		
		$match_flag	=	$this->numberVallidation($land_line);
		if($match_flag==1){
			$error_rule = 'RULE_LANDLINE_ERR';
		}
		$match_flag	=	$this->startNumValidation($land_line);
		if($match_flag==1){
			$error_rule = 'RULE_LANDLINE_ERR';
		}
		$pattern =	"/^[2-7]/";
		$match_flag =	$this->patternMatch($pattern,$land_line);
		if($match_flag!=1){
			$error_rule = 'RULE_LANDLINE_ERR';
		}
        //~ $match_flag  =   $this->checkVirtulNumber($land_line,'landline');
        //~ if($match_flag==1){
            //~ $error_rule = 'RULE_VIRTUAL_ERR';
        //~ }
		return $error_rule;		
	}
	private function faxValidation($fax){
		$error_rule = '';
		if($this->stdcode!=''){
			$total_length = strlen($this->stdcode)+strlen($fax);
			//echo "<br>".$total_length;
			if($total_length!=10){
				$error_rule = 'RULE_FAX_LENGTH';
			}
		}
		return $error_rule;
	}
	private function mobileValidation($mobile){
		$error_rule = '';
		
		$pattern =	"/^[6789]\d{9}$/";
		$match_flag =	$this->patternMatch($pattern,$mobile);
		if($match_flag!=1){
			$error_rule = 'RULE_MOBILE_ERR';
		}
		/*if($error_flag==1){
			$this->error_array['mobile']['msg'][]  = "Mobile number should be 10 digit Start from 7/8/9 :".$mobile;
		}*/
		return $error_rule;
	}
    private function checkVirtulNumber($mobile_arr,$landline_arr){
		$virtual_final_arr 		= array();
		$mobile_landline_arr 	= array();
		if(count($mobile_arr)>0 && count($landline_arr)>0){
			$mobile_landline_arr	= array_merge($mobile_arr,$landline_arr);
		}
		else if(count($mobile_arr)>0){
			$mobile_landline_arr	= $mobile_arr;
		}
		else if(count($landline_arr)>0){
			$mobile_landline_arr	= $landline_arr;
		}
		if(count($mobile_landline_arr)>0){
			$mobile_landline_str = implode(",",$mobile_landline_arr);
		}
		if($mobile_landline_str!=''){
			$curl_url = VIRTUAL_API."?nos=".$mobile_landline_str;
			//echo "<br>";
			$virtual_res =	$this->curlCallGet($curl_url);
			//echo "<br>";
			$virtual_res_arr =  array();
			if($virtual_res!=''){
				$virtual_res_arr =	json_decode($virtual_res,true);
			}
			$virtual_match_arr  = array();
			if($virtual_res_arr['virtual_numbers']!=''){
				$virtual_match_arr	=	explode(",",$virtual_res_arr['virtual_numbers']);
			}
		}		
		if(count($landline_arr)>0){
			foreach($landline_arr as $land_line){
				$virtual_match_flag = 0;
				$sql_check_virtual = "SELECT city FROM tbl_virtual_number_range WHERE city = '". $this->data_city ."'  AND '". $land_line ."' BETWEEN start_number AND end_number";
				$res_check_virtual = parent::execQuery($sql_check_virtual,$this->conn_local);
				if(parent::numRows($res_check_virtual)>0){
					$virtual_match_arr[] = $land_line; 
				}
			}
		}
		if(count($virtual_match_arr)>0){		
			$virtual_final_arr  =	array_filter(array_unique($virtual_match_arr));
		}
		return $virtual_final_arr;
    }
	private function curlCallGet($curl_url){
		$ch = curl_init($curl_url);
		$ans=curl_setopt($ch, CURLOPT_URL,$curl_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		$resstr = curl_exec($ch);
		//print "curl result : ".$resstr ;exit;
		curl_close($ch);
		return $resstr;
	}
	private function blockNumber($contact_number_arr,$contact_type){
		$blocked_number_arr = array();
		$blocked_number_str = '';
		$contract_stdcode 	= $this->stdcode;
		if(count($contact_number_arr)>0)
		{
			$contact_number_str = implode("','",$contact_number_arr);
			$sqlBlockedNumber = "SELECT blocknumber,TRIM(LEADING 0 FROM stdcode) AS stdcode_final FROM dnc.tbl_blockNumbers WHERE blocknumber IN  ('".$contact_number_str."') AND block_status = '1'";
			$resBlockedNumber	=parent::execQuery($sqlBlockedNumber,$this->conn_dnc);
			if($resBlockedNumber && parent::numRows($resBlockedNumber)>0)
			{
				while($row_blocked_number = parent::fetchData($resBlockedNumber))
				{
					$blocknumber = $row_blocked_number['blocknumber'];
					if($contact_type == 'Mobile')
					{
						$blocked_number_arr[] =  $blocknumber;
					}
					else if($contact_type == 'Phone')
					{
						$contract_stdcode 		 = ltrim($contract_stdcode,0);
						$contract_stdcode_intval = intval($contract_stdcode);
						$blocked_stdcode  		 = ltrim($row_blocked_number['stdcode_final'],0);
						$blocked_stdcode_intval  = intval($blocked_stdcode);
						if(($contract_stdcode_intval > 0) && ($blocked_stdcode_intval > 0))
						{
							if(trim($contract_stdcode_intval) == trim($blocked_stdcode_intval))
							{
								$blocked_number_arr[] =  $blocknumber;
							}
						}
					}
				}
			}			
		}
		if(count($blocked_number_arr)>0){
			$blocked_number_str = implode(",",$blocked_number_arr);
		}
		return $blocked_number_str;
	}
    private function blockOtherCityNumber($contact_number_arr){
        $blocked_number_arr = array();
        $blocked_number_str = '';
        if(count($contact_number_arr)>0)
        {
            $contact_number_str = implode("','",array_values($contact_number_arr));
            $sqlBlockedNumber = "SELECT blocknumber,TRIM(LEADING 0 FROM stdcode) AS stdcode_final FROM dnc.tbl_blockNumbers WHERE blocknumber IN  ('".$contact_number_str."') AND block_status = '1'";
            $resBlockedNumber   =parent::execQuery($sqlBlockedNumber,$this->conn_dnc);
            if($resBlockedNumber && parent::numRows($resBlockedNumber)>0)
            {
                while($row_blocked_number = parent::fetchData($resBlockedNumber))
                {
                    $blocknumber = $row_blocked_number['blocknumber'];                    
                    $contract_stdcode=   array_search($blocknumber,$contact_number_arr);
                    
                    $contract_stdcode        = ltrim($contract_stdcode,0);
                    $contract_stdcode_intval = intval($contract_stdcode);
                    $blocked_stdcode         = ltrim($row_blocked_number['stdcode_final'],0);
                    $blocked_stdcode_intval  = intval($blocked_stdcode);
                    if(($contract_stdcode_intval > 0) && ($blocked_stdcode_intval > 0))
                    {
                        if(trim($contract_stdcode_intval) == trim($blocked_stdcode_intval))
                        {
                            $blocked_number_arr[] =  $blocknumber;
                        }
                    }                                      
                }
            }           
        }
        if(count($blocked_number_arr)>0){
            $blocked_number_str = implode(",",$blocked_number_arr);
        }
        return $blocked_number_str;
    }
	private function numberVallidation($str){
		$pattern =	"/[^0-9]/";
		return $this->patternMatch($pattern,$str);		
	}
	private function startNumValidation($str){
		$pattern =	"/^[0-1]/";
		return $this->patternMatch($pattern,$str);				
	}
	private function tollfreeValidation($tollfree){
		$tollfree_err = '';
		$pattern =	"/^(((1800){1})|((1860){1})|((0008){1}))\d{4,9}$/";
		$match_flag =	$this->patternMatch($pattern,$tollfree);
		if($match_flag!=1){
			$tollfree_err = 'RULE_TOLLFREE_ERR';			
		}
		return $tollfree_err;
	}
	private function emailValidation($email){
		$email_err = '';
		$pattern =	"/^[+a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,15}$/";
		$match_flag =	$this->patternMatch($pattern,$email);
		if($match_flag!=1){
			$email_err = 'RULE_EMAIL_ERR';
		}
		return $email_err;
	}
	private function websiteValidation($website){
		$err_rule = '';

		$pattern =  "/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,15}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/";

		$match_flag =	$this->patternMatch($pattern,$website);
		if($match_flag!=1){
			$err_rule= 'RULE_WEB_INVALID';
		}

		/*$pattern = '/(justdial.com)/';
		$match_flag =	$this->patternMatch($pattern,$website);
		if($match_flag==1){
			$err_rule=  'RULE_WEB_JD';
		}*/

		$pattern ="/https?:\/\//";
		$match_flag =	$this->patternMatch($pattern,$website);
		if($match_flag==1){
			$err_rule= 'RULE_WEB_HTTP';
		}
		$firstchar =	substr($website, 0, 3);
		if(strtolower($firstchar)=='www'){
			$firstfour =	substr($website, 0, 4);
			if(strtolower($firstfour)!='www.'){
				$err_rule= 'RULE_WEB_DOT';
			}
		}

		$web_processed =  preg_replace('/(www\.)|(\.com)|(\.in)/','',$website);
		
		if(strlen($web_processed)>63){
			//$err_rule= 'RULE_WEB_LENGTH';
		}
		$pattern ="/https?:\/\//";
		$match_flag =	$this->patternMatch($pattern,$website);
		if($match_flag==1){
			$err_rule= 'RULE_WEB_HTTP';
		}
		
		
		if($err_rule != 'RULE_WEB_INVALID')
		{
			$pattern ="/w{4,}/";
			$match_flag =	$this->patternMatch($pattern,$website);
			if($match_flag==1){
				$err_rule= 'RULE_WEB_INVALID';
			}
			else
			{
				if(substr_count($website,"www.")>=2)
					$err_rule= 'RULE_WEB_INVALID';
			}
			
		}
		
		return $err_rule;
	}
	private function yearValidation(){		
		$pattern ="/^\d{4}$/";
		$match_flag =	$this->patternMatch($pattern,$this->year_of_est);
		if($match_flag!=1){
			$this->error_array['block']['msg'][]  = "Invalid year";
			$this->error_array_field['block']['msg']['year_of_est']  = "Invalid year : ".$this->year_of_est;
		}

		if($this->year_of_est!=''){
			if($this->year_of_est>date('Y')){
				$msg = "Year should not  be greater than current year";
			}
			if($this->year_of_est<'1800'){
				$msg = "Year should be greater than 1800";
			}
			if($msg!=''){
				$this->error_array['block']['msg'][]   = $msg." : ".$this->year_of_est;
				$this->error_array_field['block']['msg']['year_of_est']   = $msg." : ".$this->year_of_est;
			}
		}		
	}
	private function getDuplicateArr($arr){
		$dups_Arr = array();
		foreach(array_count_values($arr) as $val => $c){
		    if($c > 1) {
		    	$dups_Arr[] = $val;
		    }
		}
		return $dups_Arr;
	}
	private function patternMatch($pattern,$str){
		$match_flag = 0;
		/*echo $pattern;
		echo "----".$str;*/
		//echo preg_match($pattern,$str);die;
		if(preg_match($pattern,$str)==1){
			$match_flag = 1;
		}
		return $match_flag;
	}	
	private function setServers(){
		GLOBAL $db;
		$conn_city 		= ((in_array(strtolower($this->data_city), $this->dataservers)) ? strtolower($this->data_city) : 'remote');
		
		$this->conn_iro    		= $db[$conn_city]['iro']['master'];
		$this->conn_local  		= $db[$conn_city]['d_jds']['master'];
		$this->conn_local_slave  		= $db[$conn_city]['d_jds']['slave'];		
		$this->conn_idc 		= $db[$conn_city]['idc']['master'];
		$this->conn_dnc   		= $db['dnc'];

	}
    private function stringProcess($string){
        $string = trim($string);
        $string = addslashes(stripslashes($string));
        return $string;
    }
	private function sendDieMsg($msg){
		$res_arr = array();
		$res_arr['error']['code'] = 1;
		$res_arr['block']['msg'][]  = $msg;
		return $res_arr;
	}
}

$params_arr = array();
if(count($_REQUEST)>0){
	foreach($_REQUEST as $key=>$value)
	{
		$params_arr[$key] = $value;
	}
}
else{
	header('Content-Type: application/json');
	$params_arr	= json_decode(file_get_contents('php://input'),true);

}
$globalObj = new GlobalApi($params_arr);
 
?>
