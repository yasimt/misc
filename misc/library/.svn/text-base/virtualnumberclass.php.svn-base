<?php
class Virtualnumber
{
    private $parentid;
    private $module;
    private $city_vn;
    private $conn_local;
    private $conn_idc;
    private $log_path;
    private $techinfo_app_arr;

    const TOTAL_REQUIRE_MAPPEDNO = 8; /* total mapped numbers tech info can store */
    const TOTAL_TECHINFO_MAPPENO = 8; /* total mapped numbers return form techinfo virtual for perticular virtual number*/
    const CURL_TIMEOUT = 5;

	function __construct($parentid,$module,$dndobj,$city_vn,$conn,$conn_local,$conn_idc=null)
	{
		if(trim($parentid) == '')
		{
			 die("parentid found as blank");
		}
		if(trim($module)=='' || !in_array(strtoupper($module), array('CS','TME','ME')))
		{
			$error = 2;
		}
        if(trim($city_vn)=='')
        {
            $error = 3 ;
        }
        if(!is_object($dndobj))
        {
            die("dnd object not create");
        }
        if(!is_object($conn_local))
        {
            die("Local connection not done");
        }
        if(!is_object($conn_idc))
        {
            die("IDC connection not done");
        }
        if($error==2)
        {
            die("Please login through propar logging");
        }
        if($error==3)
        {
            die("City is not getting.");
        }
        if(!defined('APP_PATH'))
        {
            die("APP PATH not define....");
        }
        $this->log_path = APP_PATH . '/logs/virtualNoLogs/';
		$this->parentid = $parentid;
		$this->module   = $module;
        $this->dndobj = $dndobj;
        $this->city_vn = $city_vn;
        $this->old_conn=$conn;
        $this->conn_local=$conn_local;
        $this->conn_idc=$conn_idc;
        $this->techinfo_app_arr = array('MUMBAI','KOLKATA','BANGALORE','CHENNAI','PUNE','HYDERABAD','AHMEDABAD', 'DELHI');
    }
    function idc_sanity_check()
    {
        if(!is_object($this->conn_idc))
        {
            die("IDC connection not done");
        }
    }
    function virtual_number_setting($appval_prc_vn='')
    {
        list($initial_num_rows,$virtualNo,$virtual_map_no,$pincode,$hide_virtual)=$this->initialcheck();
        if($initial_num_rows==0)
        {
            $extra_str="[data not found on live server]";
            $this->logmsgvirtualno("This is new contract, now check it this supreme contract??",$this->log_path,'Approval process',$this->parentid,$extra_str);
            
            $city_region=$this->getcity($pincode);
            if($city_region==0)
            {
                $extra_str="[city function return zero rows. Pincode is :".$pincode."][virtual number is not assign for this contract because pincode in not exist in that city.]";
                $this->logmsgvirtualno("check pincode is exist in that city",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return 0;
            }
            else
            {
                $extra_str="[Pincode is exist. Pincode is :".$pincode."]";
                $this->logmsgvirtualno("check pincode is exist in that city",$this->log_path,'Approval process',$this->parentid,$extra_str);
                list($comp_refer_row,$ref_parentid,$ref_scheme_parentid)=$this->check_supreme_contact();
                if($comp_refer_row==0)  
                {
                    $extra_str="[so get new virtual number for this contract";
                    $this->logmsgvirtualno("This contract is not having any link contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    $get_virtual_map_no=$this->getVirtual_map_number();
                    if(trim($get_virtual_map_no)!='' || $get_virtual_map_no!=0)
                    {
                        if(LIVE_APP==1)
                        {
                            if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                            {
                                $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                $this->logmsgvirtualno("virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            else
                            {
                                $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                        }
                        else
                        {
                            $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                            $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                            $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                    }
                    if(trim($get_virtual_map_no)!='' && trim($virtual_number)!='')
                    {
                        $updt_flag=$this->updatecompanymaster($virtual_number,$get_virtual_map_no);
                        if($updt_flag==1)
                        {
                            $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."] line number 110";
                            $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                    }
                    if(intval(trim($hide_virtual))==1)
                    {
                        $hide_parent_arr[0]=$this->parentid;
                        $this->update_hide_flag_intable($hide_parent_arr,$hide_virtual);
                    }
                }
                else
                {
                    $extra_str="[Refer parent id : ".$ref_parentid."][scheme parentid : ".$ref_scheme_parentid."]";
                    $this->logmsgvirtualno("This contract  have other link contract ",$this->log_path,'Approval process',$this->parentid,$extra_str);

                    list($virtual_parent_row,$parent_virtual,$parent_virtual_map)=$this->get_virtual_from_parent($ref_parentid,$ref_scheme_parentid);
                    if($virtual_parent_row==0)
                    {
                        $extra_str="[Refer parentid not have virtual number : ".$ref_parentid."]";
                        $this->logmsgvirtualno("Refer parentid is not have virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);

                        $get_virtual_map_no=$this->getVirtual_map_number();
                        if(trim($get_virtual_map_no)!='' || trim($get_virtual_map_no)!=0)
                        {

                            if(LIVE_APP==1)
                            {
                                if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                                {
                                    $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                    $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                    $this->logmsgvirtualno("virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                                else
                                {
                                    $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                    $extra_str="[get free virtual number from database is : ".$virtual_number."]for this mapped number :".$get_virtual_map_no." [for this parentid : ".$this->parentid."}";
                                    $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                            else
                            {
                                $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                $extra_str="[get free virtual number from database is : ".$virtual_number."]for this mapped number :".$get_virtual_map_no." [for this parentid : ".$this->parentid."}";
                                $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            $updt_flag=$this->updatecompanymaster($virtual_number,$get_virtual_map_no);
                            if($updt_flag==1)
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."]";
                                $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            $updt_ref_flag=$this->updatecompanymasterperent($ref_parentid,$virtual_number,$get_virtual_map_no);
                            if($updt_ref_flag==1)
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."][other link contract update:".$ref_parentid."]";
                                $this->logmsgvirtualno("company master updated successfully for all referance parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                        }
                    }
                    else
                    {
                        if($parent_virtual!='' && $parent_virtual_map!='')
                        {
                            $updt_flag=$this->updatecompanymaster($parent_virtual,$parent_virtual_map);
                            if($updt_flag==1)
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$parent_virtual_map."][virtual number:".$parent_virtual."] line number 166";
                                $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            if(intval(trim($hide_virtual))==1)
                            {
                                $hide_parent_arr[0]=$this->parentid;
                                $this->update_hide_flag_intable($hide_parent_arr,$hide_virtual);
                            }
                        }
                    }
                }
            }
        }
        else
        {
            $extra_str="[Contract is present on live server : ".$this->parentid."]";
            $this->logmsgvirtualno("This is old paid contract",$this->log_path,'Approval process',$this->parentid,$extra_str);

            $city_region=$this->getcity($pincode);

            if($city_region==0)
            {
                $extra_str="[city function return zero rows. Pincode is :".$pincode."][virtual number is not assign for this contract because pincode in not exist in that city.]";
                $this->logmsgvirtualno("check pincode is exist in that city",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return 0;
            }
            else
            {
                $extra_str="[Pincode is exist. Pincode is :".$pincode."]";
                $this->logmsgvirtualno("check pincode is exist in that city",$this->log_path,'Approval process',$this->parentid,$extra_str);

                if(intval(trim($virtualNo))!=0 && intval(trim($virtual_map_no))!=0)
                {
                    $extra_str="[ vitual number is : ".$virtualNo."][ vitual mapped number is :".$virtual_map_no."]";
                    $this->logmsgvirtualno("virtual number is already present for approval contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    /*CHECK VIRTUAL MAPPED NUMBER IS CHANGE OR NOT*/
                    list($virtual,$mappno)=$this->check_mapped_number_present(trim($virtualNo),trim($virtual_map_no));
                    if(trim($virtual)!='' && trim($mappno)!='')
                    {
                        $updt_flag=$this->updatecompanymaster(trim($virtual),trim($mappno));
                        if($updt_flag==1)
                        {
                            $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$mappno."][virtual number:".$virtual."][hide for virtual number:".$hide_virtual."] line number 199";
                            $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                        if(intval(trim($hide_virtual))==1)
                        {
                            $hide_parent_arr[0]=$this->parentid;
                            $this->update_hide_flag_intable($hide_parent_arr,$hide_virtual);
                        }
                        if(LIVE_APP==1)
                        {
                            list($virtualnumber,$virtual_mapped_phone_number,$fbemail_str,$stdcode,$comp_pincode,$area,$parentid,$companyName,$landline_number_str,$mobile_number_str,$mobile_feedback_str)=$this ->get_all_info();

                            if(trim($stdcode)=='' || intval(trim($stdcode))=='0')
                            {
                                $stdcode=$this->get_stdcode($comp_pincode,$area);
                            }

                            /*GET TOP 8 CONTACT NUMBER FROM TBL_COMPANYMASTER_GENERALINFO TABLE*/
                            $get_top_eight_contcts=$this->get_top_mappednumber($landline_number_str,$mobile_number_str);

                            $extra_str="[Top 8 contacts :".implode(",",$get_top_eight_contcts)."]";
                            logmsgvirtualno("Get top 8 contact number from databse in approval process at 250.",$this->log_path,'Approval process',$this->parentid,$extra_str);

                            if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                            {
                                $new_get_top_eight_contcts = $this->concat_stdcode_zero($get_top_eight_contcts,$stdcode);

                                $techInfo_array=$this->get_top_mappfromtechinfo(trim($virtual));

                                if(count($techInfo_array)>0)
                                {
                                    if(trim($this->parentid)==trim($techInfo_array['BusinessId']))
                                    {
                                        $change_flag=$this->check_mapped_number($new_get_top_eight_contcts,$techInfo_array);
                                    }
                                    else
                                    {
                                        $link_arr_pid[0]=trim($this->parentid);
                                        $linkpid_array=$this->recursive_parentid($link_arr_pid);

                                        if($linkpid_array[0]==trim($techInfo_array['BusinessId']))
                                        {
                                            $change_flag=$this->check_mapped_number($new_get_top_eight_contcts,$techInfo_array);
                                        }
                                        else
                                        {
                                            $extra_str = "[previous virtual number :".$virtual."][techinfo business id :".trim($techInfo_array['BusinessId'])."][]";
                                            logmsgvirtualno("assign new virtual number because of techinfo and our parentid is different for previous virtual number after check its link contract condition line 268",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                            $forceful_vn_null='';
                                            $forceful_vmapn_null='';
                                            $forceful_null_vn=$this ->updatecompanymaster($forceful_vn_null,$forceful_vmapn_null);
                                            $extra_str = "[previous virtual number :".$virtual."][techinfo business id :".trim($techInfo_array['BusinessId'])."][force fully remove virtual number from tbl_companymaster table because this virtual number is assign to other parentid in techinfo server which is not linkcontract, so assign new virtual number for this contract]";
                                            logmsgvirtualno("Forcefully remove virtual number ",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                            $apprv_flag=0;
                                            $virtualno=$this ->virtual_number_setting($apprv_flag); 

                                            $extra_str = "[old virtual number :".$virtual."][old virtual mapped number :".$mappno."][new virtual number : ".$virtual."]";
                                            logmsgvirtualno("",$this->log_path,'approval process',$this->parentid,$extra_str);
                                        }
                                    }
                                    if($change_flag==0)
                                    {
                                        if((trim($mobile_feedback_str)!=trim($techInfo_array['Mobile'])) || (trim($fbemail_str)!=trim($techInfo_array['Email'])))
                                        {
                                            $change_flag=1;
                                        }
                                    }
                                    if($change_flag==1)
                                    {
                                        $tech_parentId=trim($techInfo_array['BusinessId']);
                                        /*if(trim($stdcode)=='' || intval(trim($stdcode))==0)
                                        {
                                            $stdcode = $this->get_stdcode($comp_pincode,$area);
                                        }*/
                                        $status='A';
                                        $virtual = $this->url_virtual_number($virtual,$mappno,$tech_parentId,$stdcode,$companyName,$landline_number_str,$mobile_number_str,$status,$fbemail_str,$mobile_feedback_str);


                                        $extra_str="[virtual no: ".$virtual."] [virtual mapped no :".$get_top_eight_contcts[0]."][change flag :".$change_flag."]";
                                        logmsgvirtualno("Mapped virtual number using URL succesfully for renew contract or already having virtual number (mapped number is change). line 285 virtual class",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    }
                                    else
                                    {
                                        $extra_str="[virtual no: ".$virtual."] [virtual mapped no :".$get_top_eight_contcts[0]."][change flga :".$change_flag."]";
                                        $this->logmsgvirtualno("Mapped virtual number using URL succesfully for edit contrcat or already having virtual number (mapped number is change). line 290 virtual class",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    }
                                }
                                else
                                {
                                    $extra_str="function return array is blank because techinfo url is not work.[check for this virtual no: ".$virtual."]";
                                    $this->logmsgvirtualno("Get techinfo information using URL, line 290 virtual class",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                            else
                            {
                                $virtual=$this->update_mapped_numebr_for_virtual($get_top_eight_contcts[0],$virtual);
                            }
                        }
                        $virtual_number = $virtual; 
                    }
                    else
                    {
                        if(LIVE_APP==1)
                        {
                            if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$mappno."][virtual number:".$virtual."]";
                                $this->logmsgvirtualno("virtual number and virtual mapped number both are null after checking all condition",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            else
                            {
                                $updt_remov_flag=$this->remove_inventory($virtualNo,$virtual_map_no);
                                if($updt_remov_flag==1)
                                {
                                    $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$virtual_map_no."][virtual number:".$virtualNo."]";
                                    $this->logmsgvirtualno("remove inventory for mapping master table, because no virtual mapped number ia found in contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                        }
                        else
                        {
                            $updt_remov_flag=$this->remove_inventory($virtualNo,$virtual_map_no);
                            if($updt_remov_flag==1)
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$virtual_map_no."][virtual number:".$virtualNo."]";
                                $this->logmsgvirtualno("remove inventory for mapping master table, because no virtual mapped number ia found in contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                        }
                        $updt_flag=$this->updatecompanymaster(trim($virtualNo),trim($virtual_map_no));
                        if($updt_flag==1)
                        {
                            $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$virtual_map_no."][virtual number:".$virtualNo."] line number 228";
                            $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                        if(intval(trim($hide_virtual))==1)
                        {
                            $hide_parent_arr[0]=$this->parentid;
                            $this->update_hide_flag_intable($hide_parent_arr,$hide_virtual);
                        }
                        $virtual_number = $virtualNo;
                    }
                }
                else  
                {
                    list($getparentcontract_row,$superparent)=$this->getparentcontract($appval_prc_vn);
                    $extra_str="[Return row :".$getparentcontract_row."] [ Super_parent : ".$superparent."]";
                    $this->logmsgvirtualno("check parentid is have any link contract(get super parent).",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    if($getparentcontract_row==0)
                    {
                        $extra_str="[parentid :".$this->parentid."]";
                        $this->logmsgvirtualno("This contract is not having any link contract.So assign new virtual number.",$this->log_path,'Approval process',$this->parentid,$extra_str);

                        $get_virtual_map_no=$this->getVirtual_map_number();
                        if(trim($get_virtual_map_no)!='' || $get_virtual_map_no!=0)
                        {
                            if(LIVE_APP==1)
                            {
                                if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                                {
                                    $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                    $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                    $this->logmsgvirtualno(" line number 265 virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str); 
                                }
                                else
                                {
                                    $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                    $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                    $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                            else
                            {
                                $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                        }
                        if($get_virtual_map_no!='' && $virtual_number!='')
                        {
                            $updt_flag=$this->updatecompanymaster($virtual_number,$get_virtual_map_no);
                            if($updt_flag==1)
                            {
                                $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."] line number 269";
                                $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            if(intval(trim($hide_virtual))==1)
                            {
                                $hide_parent_arr[0]=$this->parentid;
                                $this->update_hide_flag_intable($hide_parent_arr,$hide_virtual);
                            }
                        }
                    }
                    else
                    {
                        list($getdependentabove_row,$linkparents)=$this->getschemecontract($superparent);
                        if($getdependentabove_row==0)
                        {
                            $extra_str="[no child parent][main parentid:".$superparent."]";
                            $this->logmsgvirtualno("No link contract for this contract(NO child parnetid).so get new virtual number and allocate .",$this->log_path,'Approval process',$this->parentid,$extra_str);

                            $get_virtual_map_no=$this->getVirtual_map_number();
                            if(trim($get_virtual_map_no)!='' || $get_virtual_map_no!=0)
                            {
                                if(LIVE_APP==1)
                                {
                                    if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                                    {
                                        $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                        $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                        $this->logmsgvirtualno("virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    }
                                    else
                                    {
                                        $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                        $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                        $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    }
                                }
                                else
                                {
                                    $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                    $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                    $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                            if($get_virtual_map_no!='' && $virtual_number!='')
                            {
                                $updt_flag=$this->updatecompanymaster($virtual_number,$get_virtual_map_no);
                                if($updt_flag==1)
                                {
                                    $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."]";
                                    $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                            if(intval(trim($hide_virtual))==1)
                            {
                                $extra_str="[hide for virtual number:".$hide_virtual."][ this parentid is hide for virtual number:".implode(",",$superparent)."] line 325";
                                $this->logmsgvirtualno("no link contract found for this parent id, its hice for virtual number is one so upadte in companymaster genralinfo",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                $this->update_hide_flag_intable($superparent,$hide_virtual);
                            }
                        }
                        else
                        {
                            $extra_str="[Total child parentid :".$getdependentabove_row."][all child parentd id :".$linkparents."]";
                            $this->logmsgvirtualno("contract having other link contract(child contract present)",$this->log_path,'Approval process',$this->parentid,$extra_str);

                            if(is_array($linkparents))
                            {
                                for($i=0;$i<count($linkparents);$i++)
                                {
                                    if(isset($linkparents[$i]) && $linkparents[$i]!='')
                                    {
                                        $linkallparents[]=$linkparents[$i];
                                    }
                                }
                            }
                            $linkallparents[]=$superparent['superparent'];

                            $extra_str="[All link contract parentd id array :".implode(",",$linkallparents)."][for this main parentid:".$superparent['superparent']."]";
                            $this->logmsgvirtualno("Get all parent id array (super parentd + scheme parentid)",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            if(is_array($linkallparents))
                            {
                                if(count($linkallparents)>0)
                                {
                                    $get_sup_parent_arr[0]=$this->parentid;
                                    $get_main_parent_arr=$this->recursive_parentid($get_sup_parent_arr);

                                    $extra_str="[approval contract id : ".$this->parentid."][For this main parentid is :".implode(",",$get_main_parent_arr)."]";
                                    $this->logmsgvirtualno("Get main parent for link contract .....",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                    $mainparent_virtualno=$this->get_virtualno_frmCompMstr($get_main_parent_arr[0]);

                                    list($virtualnumber,$virtual_mapped_phone_number,$fbemail_str,$stdcode,$comp_pincode,$area,$parentid,$companyName,$landline_number_str,$mobile_number_str,$mobile_feedback_str)=$this ->get_all_info();

                                    if(trim($stdcode)=='' || intval(trim($stdcode))=='0')
                                    {
                                        $stdcode = $this->get_stdcode($comp_pincode,$area);
                                    }
                                    /*GET TOP 8 CONTACT NUMBER FROM TBL_COMPANYMASTER_GENERALINFO TABLE*/
                                    $get_top_eight_contcts=$this->get_top_mappednumber($landline_number_str,$mobile_number_str);

                                    $extra_str="[Top 8 contacts :".implode(",",$get_top_eight_contcts)."]";
                                    $this->logmsgvirtualno("Get top 8 contact numbers for link contract ",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    if(LIVE_APP==1)
                                    {
                                        if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                                        {
                                            $new_get_top_eight_contcts = $this->concat_stdcode_zero($get_top_eight_contcts,$stdcode);

                                            $techInfo_array=$this->get_top_mappfromtechinfo(trim($mainparent_virtualno));
                                            if(count($techInfo_array)>0)
                                            {
                                                if(trim($this->parentid)==trim($techInfo_array['BusinessId']))
                                                {
                                                    $change_flag=$this->check_mapped_number($new_get_top_eight_contcts,$techInfo_array);
                                                }
                                                else
                                                {
                                                    $link_arr_pid[0]=trim($techInfo_array['BusinessId']);
                                                    $linkpid_array=$this->recursive_parentid($link_arr_pid);
                                                    if($linkpid_array[0]==trim($techInfo_array['BusinessId']))
                                                    {
                                                        $change_flag=$this->check_mapped_number($new_get_top_eight_contcts,$techInfo_array);
                                                    }
                                                    else
                                                    {
                                                        $extra_str = "[Tech info database virtual number :".$techInfo_array['BusinessId']."][ our main parentid :".$link_arr_pid[0]."][virtaul number is :".trim($mainparent_virtualno)."]";
                                                        logmsgvirtualno("main parent virtual number is different in tech info database",'../logs/virtualNoLogs/','dealclose process',$parentId,$extra_str);
                                                    }
                                                }

                                                if($change_flag==0)
                                                {
                                                    if((trim($mobile_feedback_str)!=trim($techInfo_array['Mobile'])) || (trim($fbemail_str)!=trim($techInfo_array['Email'])))
                                                    {
                                                        $change_flag=1;
                                                    }
                                                }
                                                if($change_flag==1)
                                                {
                                                    $tech_parentId=trim($techInfo_array['BusinessId']);
                                                    $status='A';
                                                    $virtual = $this->url_virtual_number($mainparent_virtualno,$virtual_mapped_phone_number,$tech_parentId,$stdcode,$companyName,$landline_number_str,$mobile_number_str,$status,$fbemail_str,$mobile_feedback_str);

                                                    $extra_str="[virtual no: ".$virtual."][change flag :".$change_flag."]";
                                                    $this->logmsgvirtualno("Mapped number in tech info url for link contract",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                                }
                                                else
                                                {
                                                    $extra_str="[virtual no: ".$virtual."][change flag :".$change_flag."]";
                                                    $this->logmsgvirtualno("All mapped number in techinfo are same so nathing is change",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                                }
                                            }
                                            else
                                            {
                                                $extra_str="Techinfo server is down therefor get techinfo arraay is blank. count of techinfo arra :".count($techInfo_array)." and array contain ".implode(",",$techInfo_array)." ,[main parentid virtual no: ".$mainparent_virtualno."]";
                                                $this->logmsgvirtualno("get information from Techinfo url ",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                            }
                                            
                                        }
                                        else
                                        {
                                            $this->get_virtual_allcontract($linkallparents,$appval_prc_vn);
                                        }
                                    }
                                    else
                                    {
                                        $this->get_virtual_allcontract($linkallparents,$appval_prc_vn);
                                    }
                                    $updt_flag=$this->updatecompanymaster(trim($mainparent_virtualno),trim($get_top_eight_contcts[0]));
                                    if($updt_flag==1)
                                    {
                                        $extra_str="[parentid:".$parentId."][virtaul mapped number:".trim($get_top_eight_contcts[0])."][virtual number:".$virtual."][main parentid virtual number :".$mainparent_virtualno."]";
                                        logmsgvirtualno("company master updated successfully for on of link contract",$this->log_path,'Approval process',$parentId,$extra_str);
                                    }
                                }
                                else
                                {
                                    $extra_str="[All link contract parentd id array :".implode(",",$linkallparents)."](link array count is zero)";
                                    $this->logmsgvirtualno("All link contract array is null, so assign new virtual number",$this->log_path,'Approval process',$this->parentid,$extra_str);

                                    $get_virtual_map_no=$this->getVirtual_map_number();
                                    if(trim($get_virtual_map_no)!='' || $get_virtual_map_no!=0)
                                    {
                                        if(LIVE_APP==1)
                                        {
                                            if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                                            {
                                                $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                                $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                                $this->logmsgvirtualno("virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                            }
                                            else
                                            {
                                                $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                                $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                                $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                            }
                                        }
                                        else
                                        {
                                            $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                            $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                            $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                        }
                                    }
                                }
                                if(intval(trim($hide_virtual))==1)
                                {
                                    $this->update_hide_flag_intable($linkallparents,$hide_virtual);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $virtual_number;
    }
    function initialcheck()
    {
        $initialcheck="SELECT virtualnumber,virtual_mapped_number, pincode,blockforvirtual FROM ".DB_IRO.".tbl_companymaster_generalinfo WHERE parentid = '" . $this->parentid . "' and paid=1";
	    $initialresult=$this->conn_local->query_sql($initialcheck);
        if($initialresult)
        {
            $initial_num_rows=mysql_num_rows($initialresult);
            if($initialresult_row=mysql_fetch_assoc($initialresult))
            {
                $virtualNo      =   $initialresult_row['virtualnumber'];
                $virtual_map_no =   $initialresult_row['virtual_mapped_number'];
                $pincode        =   $initialresult_row['pincode'];
                $hide_virtual  =   $initialresult_row['blockforvirtual'];
                //log existing virtual number
                $extra_str="[virtual no: ".$virtualNo."] [virtual mapped no: ".$virtual_map_no."][hide for virtual number : ".$hide_virtual."]";
                $this->logmsgvirtualno("initial check....contract already exist.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
        }
        if($initial_num_rows==0)
        {
            //log not existing found
            $extra_str="[virtual no: not assign] [virtual mapped no: not assign]";
            $this->logmsgvirtualno("initial check.... contract not exist.",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
        return array($initial_num_rows,$virtualNo,$virtual_map_no,$pincode,$hide_virtual);
    }
    function getcity($pincode)
    {
        $getcity="SELECT display FROM d_jds.tbl_areas_count WHERE pin_code LIKE '%" . $pincode . "%' AND display > 0";
        $resultcity=$this->conn_local->query_sql($getcity);
        if($resultcity)
        {
            $getcity_row=mysql_num_rows($resultcity);
		}
        return $getcity_row;
    }
    function check_supreme_contact($appval_prc_vn='')
    {
        $qry_sel_comp_ref="SELECT parentid, GROUP_CONCAT(scheme_parentid) AS scheme_id FROM d_jds.tbl_company_refer WHERE  parentid = '".$this->parentid."' or scheme_parentid = '".$this->parentid."'";
        $res_sel_comp_ref = $this->conn_local->query_sql($qry_sel_comp_ref);
        /*if($appval_prc_vn==1)
        {
            $this->idc_sanity_check();
            $res_sel_comp_ref = $this->conn_idc->query_sql($qry_sel_comp_ref);
        }
        else
        {
            $res_sel_comp_ref = $this->conn_local->query_sql($qry_sel_comp_ref);
        }*/
        if($res_sel_comp_ref)
        {
            $comp_refer_row=mysql_num_rows($res_sel_comp_ref);
            if($row_sel_comp_ref=mysql_fetch_assoc($res_sel_comp_ref))
            {
                $ref_parentid=$row_sel_comp_ref['parentid'];
                $ref_scheme_parentid=$row_sel_comp_ref['scheme_id'];
            }
        }
        return array($comp_refer_row,$ref_parentid,$ref_scheme_parentid);
    }
    function getVirtual_map_number($appval_prc_vn='')
    {
        //global $dndobj;
        $Reason='';
        $contact_details_arr = array();
        $landline_arr = array();
        $mobile_arr   = array();

        $sql_compsrch = "SELECT landline, mobile FROM db_iro.tbl_companymaster_generalinfo WHERE parentid = '".$this->parentid."'";
        $res_compsrch = $this->conn_local->query_sql($sql_compsrch);
        /*if($appval_prc_vn==1)
        {
            $this->idc_sanity_check();
            $res_compsrch = $this->conn_idc->query_sql($sql_compsrch);
        }
        else
        {
            $res_compsrch = $this->conn_local->query_sql($sql_compsrch);
        }*/
        if($res_compsrch && mysql_num_rows($res_compsrch)>0)
        {
            $row_compsrch = mysql_fetch_assoc($res_compsrch);
            if(trim($row_compsrch['landline'])!='')
            {
                $landline_arr = explode(",",trim($row_compsrch['landline']));  
            }
            if($row_compsrch['mobile'])
            {
                $mobile_arr = explode(",",trim($row_compsrch['mobile']));
            }
            $contact_details_arr = array_merge($landline_arr,$mobile_arr);
        }
        if(is_array($contact_details_arr))
        {
            // log for available number in contract
            $extra_str="[contract communication details : ".implode(",",$contact_details_arr)."]";
            $this->logmsgvirtualno("Get virtual mapped number from contract details.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            foreach($contact_details_arr as $con_key => $con_value)
            {
                if(trim($con_value)!='')
                {
                    $extra_str="[Virtual mapped number : ".$con_value."]";
                    $this->logmsgvirtualno("Get virtual mapped number.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                    return $con_value;
                    /*if(strlen(trim($con_value))<=8)
                    {
                        // log for virtual mapped number
                        $extra_str="[landline number mapped as Virtual mapped number : ".$con_value."]";
                        $this->logmsgvirtualno("Get landline number for virtual mapped number.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        return $con_value;
                    }
                    else if(strlen(trim($con_value))==10)
                    {
                        if(is_object($this->dndobj))
                        {
                            $Reason = $this->dndobj->IsInDNClist($this->old_conn,trim($con_value));
                            if($Reason==0)
                            {
                                // log for virtual mapped number
                                $extra_str="[mobile number mapped as Virtual mapped number : ".$con_value."]";
                                $this->logmsgvirtualno("Get Mobile number for virtual mapped number.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                return $con_value;
                            }
                        }
                        else
                        {
                            $this->dndobj= new DNDNumber();
                            $Reason = $this->dndobj->IsInDNClist($this->old_conn,trim($con_value));
                            if($Reason==0)
                            {
                                // log for virtual mapped number
                                $extra_str="[mobile number mapped as Virtual mapped number : ".$con_value."]";
                                $this->logmsgvirtualno("Get MObile number for virtual mapped number.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                return $con_value;
                            }
                        }
                    }*/
                }
            }
        }
        // log for no virtual mapp number get
        $extra_str="[no numbers(no landline/ no mobile number mapped as virtual mapped number) : because no landline number in contract and all mobile numbers in DND list]";
        $this->logmsgvirtualno("no virtual mapped number.",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return "";
    }
    function allocate_virtual($get_virtual_map_no)
    {
        $v_number = '';
        //global $dndobj;
        $sql = "SELECT mm.virtualNo FROM " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master mm LEFT JOIN " . DB_IRO . ".tbl_companymaster_generalinfo cm ON (mm.virtualNo=cm.virtualNumber) WHERE (cm.virtualnumber='' OR cm.virtualnumber IS NULL) AND (mm.contractid='' OR mm.contractid IS NULL) AND free_flag=0 AND (mm.mappedno='' or mm.mappedno='0' or mm.mappedno is null) AND mm.approved_flag=0 AND mm.virtualNo!=0 LIMIT 1";    
        $res = $this->conn_local->query_sql($sql);
        if($res && mysql_num_rows($res)>0)
        {
            $row=mysql_fetch_assoc($res);
            $sql_up_vr_mst = "UPDATE " . DB_JDS_LIVE . ".tbl_virtual_num_mapping_master SET mappedNo = '".addslashes($get_virtual_map_no)."', contractid = '". $this->parentid. "', free_flag = 1, approved_flag = 0 WHERE virtualNo = '".$row['virtualNo']."'";
            $sql_up_vr_mst_res= $this->conn_local->query_sql($sql_up_vr_mst);
            $v_number=$row['virtualNo'];
        }
        else
        {
            if(mysql_num_rows($res)==0)
            {
                $extra_str="[virtual number count:".mysql_num_rows($res)."]";
                $this->logmsgvirtualno("Virtual number invetory is full..",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
        }
        return $v_number;  
    }
    function updatecompanymaster($virtual_number,$get_virtual_map_no)
    {
        $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$get_virtual_map_no."][parentid :".$this->parentid."]";
        $this->logmsgvirtualno("Update virtual number and virtual maaped number in company master generalinfo on live server.",$this->log_path,'Approval process',$this->parentid,$extra_str);

        $qry_updt_tbl_comp_getner_idc="UPDATE db_iro.tbl_companymaster_generalinfo SET virtualNumber='".trim($virtual_number)."', virtual_mapped_number='".trim($get_virtual_map_no)."' WHERE parentid='".$this->parentid."'";
        $this->idc_sanity_check();
        $res_updt_tbl_comp_getner_idc	= $this->conn_local->query_sql($qry_updt_tbl_comp_getner_idc);
        $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$get_virtual_map_no."][parentid:".$this->parentid."][Qry run:".$qry_updt_tbl_comp_getner_idc."][Qry resulr: ".$res_updt_tbl_comp_getner_idc."]";
        $this->logmsgvirtualno("Update virtual number and virtual mapped number on live companymaster general info successfuly.",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return $res_updt_tbl_comp_getner_idc;
    }
    function get_virtual_from_parent($ref_parentid,$ref_scheme_parentid)
    {
        $sql = "SELECT virtualNo, mappedNo FROM ".DB_JDS_LIVE.".tbl_virtual_num_mapping_master WHERE contractid = '".$ref_parentid."'";					
        $res = $this->conn_local->query_sql($sql);
        if($res)
        {
            $virtual_parent_row=mysql_num_rows($res);
            if($row=mysql_fetch_assoc($res))
            {
                $parent_virtual=$row['virtualNo'];
                $parent_virtual_map=$row['mappedNo'];
            }
        }
        return array($virtual_parent_row,$parent_virtual,$parent_virtual_map);
    }
    function updatecompanymasterperent($ref_parentid,$virtual_number,$get_virtual_map_no)
    {
        $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$get_virtual_map_no."][Refer parent id:".$ref_parentid."]";
        $this->logmsgvirtualno("Update virtual number and virtual maaped number in company master generalinfo on local  server for refer parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);

        $qry_updt_tbl_comp_getner="UPDATE db_iro.tbl_companymaster_generalinfo SET virtualNumber='".trim($virtual_number)."', virtual_mapped_number='".trim($get_virtual_map_no)."' WHERE parentid='".$ref_parentid."'";
        $res_updt_tbl_comp_getner= $this->conn_local->query_sql($qry_updt_tbl_comp_getner);

        $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$get_virtual_map_no."][refer parentid:".$ref_parentid."]";
        $this->logmsgvirtualno("Update virtual number and virtual mapped number in companymaster general info on local  server successfuly.",$this->log_path,'Approval process',$this->parentid,$extra_str);

        return $res_updt_tbl_comp_getner;
    }
    function getparentcontract($appval_prc_vn='')
    {
        $getparentcontract="SELECT parentid  AS superparent FROM d_jds.tbl_company_refer WHERE scheme_parentid = '" . $this->parentid . "' OR parentid = '" . $this->parentid . "'";
        $resultofparentcontract=$this->conn_local->query_sql($getparentcontract);
        /*if($appval_prc_vn==1)
        {
            $this->idc_sanity_check();
            $resultofparentcontract=$this->conn_idc->query_sql($getparentcontract);
        }
        else
        {
            $resultofparentcontract=$this->conn_local->query_sql($getparentcontract);
        }*/
        if($resultofparentcontract)
        {
            $getparentcontract_row=mysql_num_rows($resultofparentcontract);
            if($superparent=mysql_fetch_assoc($resultofparentcontract))
            {
                if(trim($superparent['superparent'])!='' && $superparent['superparent'][0] != 'P')
                {
                    $superparent['superparent']='P'.$superparent['superparent'];
                }
                return array($getparentcontract_row,$superparent);
            }
        }
    }
    function getschemecontract($superparent,$appval_prc_vn='')
    {
        $$linkparents=array();
        $getdependentcontracts="SELECT DISTINCT scheme_parentid AS childparent FROM d_jds.tbl_company_refer WHERE parentid = '" . trim($superparent['superparent']) . "'";
        $getdependentabove=$this->conn_local->query_sql($getdependentcontracts);
        /*if($appval_prc_vn==1)
        {
            $this->idc_sanity_check();
            $getdependentabove=$this->conn_idc->query_sql($getdependentcontracts);
        }
        else
        {
            $getdependentabove=$this->conn_local->query_sql($getdependentcontracts);
        }*/
        if($getdependentabove)
        {
            $getdependentabove_row=mysql_num_rows($getdependentabove);
            while($row2=mysql_fetch_assoc($getdependentabove))
            {
                if( $row2['childparent'][0]!='P' && $row2['childparent'])
                {
                    $row2['childparent']="P".$row2['childparent'];
                }
                $linkparents[]=$row2['childparent'];
            }
        }
        $extra_str="[Total row of child parent : ".$getdependentabove_row."][for Main parent :".$superparent['superparent']."][ link child parentd id: ".$linkparents."]";
        $this->logmsgvirtualno("Get all child parentids for main parentid",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return array($getdependentabove_row,$linkparents);
    }
    function get_virtual_allcontract($linkallparents,$appval_prc_vn='')
    {
        if(is_array($linkallparents))
        {
            $dependentcontracts="'" . implode("', '", $linkallparents) . "'";
            $checklinkcontracts="SELECT virtualnumber, virtual_mapped_number, parentid FROM ".DB_IRO.".tbl_companymaster_generalinfo WHERE parentid IN (" .  $dependentcontracts . ")";
            $getresultcontract=$this->conn_local->query_sql($checklinkcontracts);
            if($getresultcontract)
            {
                $getresultcontract_row=mysql_num_rows($getresultcontract);
                if($getresultcontract_row>0)
                {
                    while($numbertobecheck=mysql_fetch_assoc($getresultcontract))
                    {
                        if($numbertobecheck['virtualnumber']>0 && $numbertobecheck['virtual_mapped_number']>0)
                        {
                            $mappednumber=$numbertobecheck['virtual_mapped_number'];
                            $virtualnumber=$numbertobecheck['virtualnumber'];
                            $parentid1=$numbertobecheck['parentid'];
                            break;
                        }
                    }
                }
                if($mappednumber>0 && $virtualnumber>0)
                {
                    if($parentid1!='')
                    {
                        $key=array_search($parentid1, $linkallparents); 
                        if($key!='')
                        {
                            unset($linkallparents[$key]);
                        }
                    }
                    $other_depanetcontract="'" . implode("', '", $linkallparents) . "'";
                    $checkmappednumber="SELECT parentid FROM db_iro.tbl_companymaster_extradetails WHERE parentid != '" . $parentid1 . "' AND parentid IN (" .  $other_depanetcontract . ") AND ((landline_addinfo like '"  . $mappednumber . "%' OR landline_addinfo like '%"  . $mappednumber . "' OR landline_addinfo like '%"  . $mappednumber . "%') OR (mobile_addinfo like '"  . $mappednumber . "%' OR mobile_addinfo like '%"  . $mappednumber . "' OR mobile_addinfo like '%"  . $mappednumber . "%'))";
                    $resultofnum=$this->conn_local->query_sql($checkmappednumber);
                    if($resultofnum)
                    {
                        $resultofnum_nows=mysql_num_rows($resultofnum);
                        if($resultofnum_nows>0)
                        {
                            $updatelinkcontract="UPDATE ".DB_IRO.".tbl_companymaster_generalinfo SET virtualnumber = '" . $virtualnumber . "', virtual_mapped_number = '" . $mappednumber . "' WHERE parentid IN (" .  $dependentcontracts . ")";
                            $updateresult=$this->conn_local->query_sql($updatelinkcontract);
                            /*if($appval_prc_vn==1)
                            {
                                $this->idc_sanity_check();
                                $updateresult=$this->conn_idc->query_sql($updatelinkcontract);
                            }
                            else
                            {
                                $updateresult=$this->conn_local->query_sql($updatelinkcontract);
                            }*/
                            return ;
                        }
                        else
                        {
                            $this->reportmail("linked contract but doesnot have same contact numbers",$numbertobecheck['virtual_mapped_number']);
                            return 0;
                        }
                    }
                }
                else
                {
                    $extra_str="[parentid :".$this->parentid."][all link contract: ".implode(",", $linkallparents)."]";
                    $this->logmsgvirtualno("Any of link contract is not having virtual number .So assign new virtual number.",$this->log_path,'Approval process',$this->parentid,$extra_str);

                    $get_virtual_map_no=$this->getVirtual_map_number($appval_prc_vn);

                    if(trim($get_virtual_map_no)!='' || $get_virtual_map_no!=0)
                    {
                       if (LIVE_APP==1)
                        {
                            if(in_array(strtoupper($this->city_vn),$this->techinfo_app_arr))
                            {
                                 $virtual_number=$this->allocatevirtual_url($get_virtual_map_no,$appval_prc_vn);
                                $extra_str="[get free virtual number from URL is : ".$virtual_number."]and assign to this contract";
                                $this->logmsgvirtualno(" line number 709 virtual number is get from URL table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            else
                            {
                                $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                                $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                                $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                        }
                        else
                        {
                            $virtual_number=$this->allocate_virtual($get_virtual_map_no);
                            $extra_str="[get free virtual number from database is : ".$virtual_number."]and assign to this contract";
                            $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                    }
                    if($get_virtual_map_no!='' && $virtual_number!='')
                    {
                        //updatecompanymaster($virtual_number,$get_virtual_map_no);
                        $updet_all_link_flag=$this->update_all_linkcompanymaster($linkallparents,$virtual_number,$get_virtual_map_no);
                        if($updet_all_link_flag==1)
                        {
                            $extra_str="[parentid:".$this->parentid."][virtaul mapped number:".$get_virtual_map_no."][virtual number:".$virtual_number."][update all link::".$linkallparents."]";
                            $this->logmsgvirtualno("company master updated successfully",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                    }
                }
            }
        }
    }
    function update_all_linkcompanymaster($linkallparents,$virtual_number,$get_virtual_map_no)
    {
        if(is_array($linkallparents))
        {
            $dependentcontracts = "'".implode("','",$linkallparents)."'";
        }
        $updatelinkcontract="UPDATE ".DB_IRO.".tbl_companymaster_generalinfo SET virtualnumber = '" . $virtual_number . "', virtual_mapped_number = '" . $get_virtual_map_no . "' WHERE parentid IN (" . $dependentcontracts . ")";
        $updateresult=$this->conn_local->query_sql($updatelinkcontract);

        $extra_str="[virtual number assign to all link contract: ".$virtual_number."][virtual mapped number assign to all link contract : ".$get_virtual_map_no."][all link contract to whome assign virtual number :".implode(",",$linkallparents)."]";
        $this->logmsgvirtualno("virtual number is get from inventory table",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return $updateresult;
    }
    function check_mapped_number_present($virtualNo,$virtual_map_no)
    {
        $qry_sel_contachdetail = "SELECT stdcode,landline,mobile FROM db_iro.tbl_companymaster_generalinfo WHERE parentid='".$this->parentid."'";
        $res_sel_contachdetail = $this->conn_local->query_sql($qry_sel_contachdetail);
        if($res_sel_contachdetail && mysql_num_rows($res_sel_contachdetail)>0)
        {
            $contact_arr = array();
            $row_sel_contachdetail = mysql_fetch_assoc($res_sel_contachdetail);
            $landline_no = $row_sel_contachdetail['landline'];
            $mobile_no = $row_sel_contachdetail['mobile'];
            $city_stdcode= $row_sel_contachdetail['stdcode'];
            $landline_array=explode(",",$landline_no);
            $landline_array= array_filter($landline_array);
            $landline_array=array_merge($landline_array);
            $mobile_array = explode(",",$mobile_no);
            $mobile_array = array_filter($mobile_array);
            $mobile_array = array_merge($mobile_array);
            $contact_arr = array_merge($landline_array,$mobile_array);
            if(is_array($contact_arr))
            {
                if(in_array($virtual_map_no,$contact_arr))
                {
                    $extra_str="[ vitual mapped number is :".$virtual_map_no."][all contact number:".implode(",",$contact_arr)."][virtual number:".$virtualNo."]";

                    $this->logmsgvirtualno("Mapped virtual number is present in this contract ",$this->log_path,'Approval process',$this->parentid,$extra_str);

                    return array($virtualNo,$contact_arr[0]);
                }
                else
                {
                    $extra_str="[vitual mapped number is :".$virtual_map_no."][all contact number:".implode(",",$contact_arr)."]";

                    $this->logmsgvirtualno("Mapped virtual number is not present in this contract ",$this->log_path,'Approval process',$this->parentid,$extra_str);

                    $old_virtual_mapped_number = $virtual_map_no;
                    $virtual_map_no='';
                    
                    if(is_array($landline_array))
                    {
                        $afterremovenull=array_filter($landline_array);
                        if(count($afterremovenull)==0)
                        {
                            $extra_str="[all contact number:".implode(",",$afterremovenull)."]";

                            $this->logmsgvirtualno("contract not having landline number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                        }
                        else if(count($afterremovenull)>0)
                        {
                            foreach($afterremovenull as $key=>$value)
                            {
                                $virtual_map_no = $value;

                                $extra_str="[old vitual mapped number is :".$old_virtual_mapped_number."][new virtual mapped number:".$virtual_map_no."][virtual number already get from virtual mapping master]";

                                $this->logmsgvirtualno("Get new mapped number where virtual number inventory already reversed.(for landline)",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                //return $virtual_map_no;
                                return array($virtualNo,$virtual_map_no);
                            }
                        }
                    }
                    if(trim($virtual_map_no)=='' || $virtual_map_no==0)
                    {
                        if(is_array($mobile_array))
                        {
                            $afterremovenull_mobile=array_filter($mobile_array);
                            if(count($afterremovenull_mobile)==0)
                            {
                                $extra_str="[all contact number:".implode(",",$afterremovenull_mobile)."][virtual mapped number :".$virtual_map_no."][old mapped number :".$old_virtual_mapped_number."][old virtual number:".$virtualNo."]";

                                $this->logmsgvirtualno("contract not having mobile number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                            }
                            else if(count($afterremovenull_mobile)>0)
                            {
                                foreach($afterremovenull_mobile as $mobile_key =>$mobile_value)
                                {
                                    $virtual_map_no = $mobile_value;
                                    $extra_str = "[mobile number : ".$mobile_value."][virtual mapped number:".$virtual_map_no."]";
                                    $this->logmsgvirtualno("Assign mobile number as virtual mapped number",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    //return $virtual_map_no;
                                    return array($virtualNo,$virtual_map_no);
                                    /*$Reason = $this->dndobj->IsInDNClist($this->old_conn,trim($mobile_value));
                                    if($Reason==0)
                                    {
                                        $extra_str = "[mobile number : ".$mobile_value."][extra: mobile number is get as virtual mapped number which is not in DND list also]";
                                        $this->logmsgvirtualno("check mobile number is in DND list or not",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                        $virtual_map_no = $mobile_value;
                                        $extra_str = "[mobile number : ".$mobile_value."][virtual mapped number:".$virtual_map_no."]";
                                        $this->logmsgvirtualno("check mobile number is in DND list or not",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                        //return $virtual_map_no;
                                        return array($virtualNo,$virtual_map_no);
                                    }
                                    else
                                    {
                                        $extra_str = "[mobile number : ".$mobile_value."][extra: mobile number is in DND list ]";
                                        $this->logmsgvirtualno("check mobile number is in DND list or not",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                    }*/
                                }
                                if(trim($virtual_map_no)=='' || $virtual_map_no==0)
                                {
                                    $extra_str = "[all mobile numbers :".implode(",",$afterremovenull_mobile)."]";
                                    $this->logmsgvirtualno("All mobile number is in dnd list",$this->log_path,'Approval process',$this->parentid,$extra_str);
                                }
                            }
                        }
                    }
                }
            }
        }
        return array($virtualNo,$virtual_map_no);
    }
    function remove_inventory($virtualNo,$virtual_map_no)
    {
        $extra_str="[ vitual number is : ".$virtualNo."][ vitual mapped number is :".$virtual_map_no."]";
        $this->logmsgvirtualno("update into virtual mapping master because mapped number is not get, so free virtual number inventory...",$this->log_path,'Approval process',$this->parentid,$extra_str);
        $up_vr_mst = "UPDATE d_jds.tbl_virtual_num_mapping_master SET mappedNo='',contractid='' ,free_flag = 0,approved_flag = 0 WHERE virtualNo='".$virtualNo."'";
        $up_vr_mst_res= $this->conn_local->query_sql($up_vr_mst);
        return $up_vr_mst_res;
    }
    function logmsgvirtualno($sMsg, $sNamePrefix,$process,$contractid,$extra_str='')
    {
        $log_msg=''; 
        // fetch directory for the file
        $pathToLog = dirname($sNamePrefix); 
        if (!file_exists($pathToLog)) {
            mkdir($pathToLog, 0755, true);
        }
        /*$file_n=$sNamePrefix.$contractid.".txt"; */
        $file_n=$sNamePrefix.$contractid.".html";
        // Set this to whatever location the log file should reside at.
        $logFile = fopen($file_n, 'a+');

        // Change this to point to the User ID variable in session.
        if (isset($_SESSION['ucode']) || isset($_SESSION['mktgEmpCode'])) {
            $userID = isset($_SESSION['ucode']) ? $_SESSION['ucode'] : $_SESSION['mktgEmpCode']; //  Switches between TME_Live Session ID and DATAENTRY Session ID
        } else {
            $userID = 'unknown'; // stands for "default"  or "unknown"
        }
        /*$log_msg.=  "Parentid:-".$contractid."\n [$sMsg] \n ".$extra_str." [user id: $userID] [Action: $process] [Date : ".date('Y-m-d H:i:s')."]";*/
        $pageName 		= wordwrap($_SERVER['PHP_SELF'],22,"\n",true);
        $log_msg.= "<table border=0 cellpadding='0' cellspacing='0' width='100%'>
                        <tr valign='top'>
                            <td style='width:10%; border:1px solid #669966'>Date :".date('Y-m-d H:i:s')."</td>
                            <td style='width:10%; border:1px solid #669966'>File name:".$pageName."</td>
                            <td style='width:30%; border:1px solid #669966'>Message:".$sMsg."</td>
                            <td style='width:30%; border:1px solid #669966'>Extra Message: ".$extra_str."</td>
                            <td style='width:10%; border:1px solid #669966'>User Id :".$userID."</td>
                            <td style='width:10%; border:1px solid #669966'>Action :".$process."</td>
                        </tr>
                    </table>";
        fwrite($logFile, $log_msg);
        fclose($logFile);
    }
    function reportmail($conn,$parentid,$msg,$virtualno)
    {
        $saddr=$_SERVER['SERVER_ADDR'];
        $addArray = explode('.',$saddr);
        $cities = array(0=>"mumbai", 8=>"delhi", 16=>"kolkata", 26=>"bangalore", 32=>"chennai", 40=>"pune", 50=>"hyderabad", 35=>"ahmedabad", 56=>"ahmedabad");
        $_SESSION['s_deptCity'] = $cities[$addArray[2]];
        $cityname= $cities[$addArray[2]];	

        
        $to      = 'dilipkoora@justdial.com';
        $subject =$msg;
        $message = "check out some problem in this contract  $msg $parentid -->$virtualno $cityname ";
        $headers = 'From: softwareteam@justdial.com' . "\r\n" .
        
        'X-Mailer: PHP/' . phpversion();
        $headers  .= 'MIME-Version: 1.0' . "\r\n";
        $headers  .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        mail($to, $subject, $message, $headers);
    }
    function allocatevirtual_url($get_virtual_map_no,$appval_prc_vn='')
    {
        global $dndobj;
		$sql_tcm = "SELECT parentid,stdcode,virtualNumber,virtual_mapped_number,mobile_feedback,email_feedback,companyname,blockforvirtual,landline,mobile,area,pincode from ".DB_IRO.".tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
        $res_tcm = $this->conn_local->query_sql($sql_tcm); 
        /*if($appval_prc_vn==1)
        {
            $this->idc_sanity_check();
            $res_tcm = $this->conn_idc->query_sql($sql_tcm);
        }
        else
        {
            $res_tcm = $this->conn_local->query_sql($sql_tcm);
        }*/
        if($res_tcm && mysql_num_rows($res_tcm) > 0)
		{
            $row_tcm   			    = mysql_fetch_assoc($res_tcm);
			$virtualnumber 		    = $row_tcm['virtualNumber'];
			$virtual_mapped_number  = $row_tcm['virtual_mapped_number'];
            $mobile_feedback_str    = $row_tcm['mobile_feedback'];
			$email_str 			    = $row_tcm['email_feedback'];
            $std_code               = $row_tcm['stdcode'];
			$parentid 			    = $row_tcm['parentid'];
			$companyName 		    = $row_tcm['companyname'];
            $blockforvirtual 	    = $row_tcm['blockforvirtual'];
            $landline_number_str 	= $row_tcm['landline'];
            $mobile_number_str 	    = $row_tcm['mobile'];
            $area                   = $row_tcm['area'];
            $comp_pincode           = $row_tcm['pincode'];

            $extra_str="[virtual no: ".$virtualnumber."] [virtual map no: ".$virtual_mapped_number."][Mobile feedback :".$mobile_feedback_str."] [email : ".$email_str."] [black for virtual flag : ".$blockforvirtual ."] [landline no: ".$landline_number_str."] [mobile no: ".$mobile_number_str."][parentid:".$parentid."][area :".$area."][pincode :".$comp_pincode."]";
            $this->logmsgvirtualno("Get all information from company master general info.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
        }
        if(intval(trim($virtualnumber))==0 && intval(trim($virtual_mapped_number))== 0)
        {
            $extra_str="[virtual no: ".$virtualnumber."] [virtual mapped no :".$virtual_mapped_number."][parentid :".$parentid."]";
            $this->logmsgvirtualno("Call url for assign virtualnumber.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
            $status='A';
            if(trim($std_code)=='' || intval(trim($std_code))=='0' )
            {
                $std_code=$this->get_stdcode($comp_pincode,$area);
            }
            $virtual_number = $this->url_virtual_number($virtualnumber,$get_virtual_map_no,$parentid,$std_code,$companyName,$landline_number_str,$mobile_number_str,$status,$email_str,$mobile_feedback_str);
            $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$get_virtual_map_no."][parentid :".$parentid."]";
            $this->logmsgvirtualno("Mapped virtual number using URL succesfully .",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
            return $virtual_number;
        }
    }
    function concat_stdcode_zero($contactnos,$stdcode)
    {
        if(!is_array($contactnos))
        {
            $contactnos = array($contactnos);

        }
        if(trim($stdcode)!='' && intval($stdcode)!=0)
        {
            $valid_std=1;
        }
        foreach($contactnos as $key=>$contact)
        {
            if(strlen($contact)<10 && $valid_std==1)
            {
                //concat stdcode
                $contact = '0' . ltrim($stdcode, 0).ltrim($contact, '0');
                $contactnos[$key]= '0' . ltrim($stdcode, 0).ltrim($contact, '0');
            }
            if(strlen($contact)>=10)
            {
                //concat zero
                $contactnos[$key]= '0'.ltrim($contact, '0');
            }
        }
        return $contactnos;
    }

    function url_virtual_number($virtualnumber,$virtual_mapped_phone_number,$parentid,$std_code,$companyName,$landline_number_str,$mobile_number_str,$status,$fbemail_str,$mobile_feedback_str)
	{
        global $dndobj;
        $landline_arr = array();
        $mobile_arr   = array();
        $mail_arr   = array();
		$ch = curl_init();
        $str='';
        $vn_str = '';
        $landline_str='';
        $i=1;
        if(trim($virtualnumber)!=''  && strlen(trim($virtualnumber))>0 && intval($virtualnumber)!=0)
        {
            $vn_str ="VN=".trim($virtualnumber)."&";
        }
        /*GET TOP 8 CONTACT NUMBER FROM TBL_COMPANYMASTER_GENERALINFO TABLE*/
        $get_top_eight_contcts=$this->get_top_mappednumber($landline_number_str,$mobile_number_str);
        $new_get_top_eight_contcts = $this->concat_stdcode_zero($get_top_eight_contcts,$std_code);
        foreach($new_get_top_eight_contcts as $contactno)
        {
            if($i>8)
            {
                break;
            }
            else
            {
                $landline_str .="Ph".($i)."=".trim($contactno);
                $landline_str.="&";
            }
            $i++;
        }
        if(trim($mobile_feedback_str)!='')
        {
            $fb_mobile=trim($mobile_feedback_str);
        }
        if(trim($fbemail_str)!='')
        {
            $fbemail=trim($fbemail_str);
        }
        /*if(trim($fbemail_str)!='')
        {
            $mail_arr=explode(",",$fbemail_str);
        }
        if(is_array($mail_arr))
        {
            $fbemail=$mail_arr[0];
        }*/

        if($status=='A')
        {
            $curl="http://".constant(strtoupper($this->city_vn ).'_TAG_IP')."/justdial/allocate.php?".$vn_str.$landline_str."User=".trim($_SESSION['ucode'])."&BusinessId=".trim($parentid)."&Email=".trim($fbemail)."&Mobile=".$fb_mobile."&BusinessName=".trim($companyName)."";
        }
        else if($status=='D')
        {
            if(trim($virtualnumber)!=0 && strlen(trim($virtualnumber))>0)
            {
                $curl="http://".constant(strtoupper($this->city_vn ).'_TAG_IP')."/justdial/deallocate.php?VN=".trim($virtualnumber)."&User=".trim($_SESSION['ucode']);
            }
        }
		$ans=curl_setopt($ch, CURLOPT_URL,$curl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
		$output =curl_exec($ch);
        if($output===false)
        {
            $extra_str="Url is not working. [virtual no: ".$virtual_number."] [virtual mapped no :".$virtual_mapped_phone_number."] for allocation url . and url is :[".$curl."][status : ".$status."]";
            $this->logmsgvirtualno("Log url code when virtual number allocated/deallocate (final).",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
		    return $virtual_number;
        }
        else
        {
            $xmlDoc = new DOMDocument();
            $xmlDoc->loadXML($output);
            $Result = $xmlDoc->getElementsByTagName( "Result" );
            if($status=='A')
            {
                foreach($Result as $obj)
                {
                    $Code = $obj->getElementsByTagName("Code");
                    $Code = $Code->item(0)->nodeValue;
                    $text = $obj->getElementsByTagName("Text");
                    $text = $text->item(0)->nodeValue; 
                    $vn   = $obj->getElementsByTagName("VN");
                    $vn   = $vn->item(0)->nodeValue; 
                }
            }
            elseif($status=='D')
            {
                foreach($Result as $obj)
                {
                    $Code = $obj->getElementsByTagName("Code");
                    $Code = $Code->item(0)->nodeValue;
                    $text = $obj->getElementsByTagName("Text");
                    $text = $text->item(0)->nodeValue; 
                }
            }
            if($Code==0 && $text=='Success' && $status==='A')
            {
                $virtual_number = $vn;
                $extra_str="[virtual no: ".$vn."] [virtual mapped no :".$virtual_mapped_phone_number."] [code from url: ".$Code."][curl url: ".$curl."][parentid:".$parentid."]";
                $this->logmsgvirtualno("Log url code.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
            }
            elseif($Code==0 && $text=='Success' && $status==='D')
            {
                $virtual_number = '';
                $extra_str="[deallocated virtual no: ".$virtualnumber."] [virtual mapped no :".$virtual_mapped_phone_number."] [code from url: ".$Code."][curl url:".$curl."][parentid :".$parentid."]";
                $this->logmsgvirtualno("Log url code when deallocated virtual number.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
            }
        }
        $extra_str="[virtual no: ".$virtual_number."] [virtual mapped no :".$virtual_mapped_phone_number."] [code from url: ".$Code."][status:".$status."][text return from url:".$text."][curl url:".$curl."][parentid :".$parentid."]";
        $this->logmsgvirtualno("Log url code when virtual number allocated/deallocate (final).",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
		return $virtual_number;
	}
    
    function update_mapped_numebr_for_virtual($get_virtual_map_no,$virtualNo)
    {
        $update_virtual_mapp_no= "UPDATE d_jds.tbl_virtual_num_mapping_master SET mappedNo='".$get_virtual_map_no."' ,approved_flag = 0 WHERE virtualNo='".$virtualNo."'";
        $res_update_virtual_mapp_no= $this->conn_local->query_sql($update_virtual_mapp_no);

        $extra_str="[ vitual number is : ".$virtualNo."][ vitual mapped number is :".$get_virtual_map_no."][qry run:".$update_virtual_mapp_no."][qry result:".$res_update_virtual_mapp_no."]";
        $this->logmsgvirtualno("update into virtual mapping master because mapped number is change get, ",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return $virtualNo;
    }

    function update_hide_flag_intable($linkallparents,$hide_virtual)
    {
        $link_parent_str = implode("','",$linkallparents);
        $updt_hide_flg_compnymstr = "UPDATE ".DB_IRO.".tbl_companymaster_generalinfo SET blockforvirtual ='".$hide_virtual."' WHERE parentid IN ('".$link_parent_str."')";
        $res_updt_hide_flg_compnymstr= $this->conn_local->query_sql($updt_hide_flg_compnymstr);

        $extra_str="[ hide for virtual  number flag : ".$hide_virtual."] [ for this parentids :".$link_parent_str."][qry run:".$update_virtual_mapp_no."] [qry run:".$updt_hide_flg_compnymstr."] [qry result:".$res_updt_hide_flg_compnymstr."]";

        $this->logmsgvirtualno("update hide for virtual number in tbl_companymaster_genaralinfo table for give parent id at the time of approval, ",$this->log_path,'Approval process',$this->parentid,$extra_str);
        return $res_updt_hide_flg_compnymstr;
    }
    function get_top_mappednumber($landline_no,$mobile_no)
    {
        $landline_array=array();
        $mobile_array=array();
        $all_contacts=array();
        $all_contacts_cnt = 0;
        if(trim($mobile_no)!='')
        {
            $mobile_array = explode(",",trim($mobile_no));
            $mobile_array = array_filter($mobile_array);
            $mobile_array=array_merge($mobile_array);
            /*$mobile_array=$this->get_not_dnc_mobile($mobile_array);*//*remove this because allow DNC number as virtual mapeed number*/
        }
        $mobile_cnt = count($mobile_array);
        if(trim($landline_no)!='')
        {
            $landline_array = explode(",",trim($landline_no));
            $landline_array=array_filter($landline_array);
            $landline_array=array_merge($landline_array);
        }
        $landline_cnt = count($landline_array);
        $req_landline_cnt = 4;
        $req_mobile_cnt = 4;
        if($landline_cnt<$req_landline_cnt || $mobile_cnt<$req_mobile_cnt)
        {
            if($mobile_cnt>=$req_mobile_cnt && $landline_cnt<$req_landline_cnt)
            {
                $req_mobile_cnt += ($req_landline_cnt - $landline_cnt);
                $req_landline_cnt = $landline_cnt;
                if(($req_landline_cnt+$req_mobile_cnt)>self::TOTAL_REQUIRE_MAPPEDNO)
                {
                    $req_mobile_cnt = self::TOTAL_REQUIRE_MAPPEDNO - $req_landline_cnt;

                }
                if($req_mobile_cnt>$mobile_cnt)
                {
                    $req_mobile_cnt=$mobile_cnt;
                }
            }
            elseif($mobile_cnt<$req_mobile_cnt && $landline_cnt>=$req_landline_cnt)
            {
                $req_landline_cnt += ($req_mobile_cnt - $mobile_cnt);
                $req_mobile_cnt = $mobile_cnt;
                if(($req_landline_cnt+$req_mobile_cnt)>self::TOTAL_REQUIRE_MAPPEDNO)
                {
                    $req_landline_cnt = self::TOTAL_REQUIRE_MAPPEDNO - $req_mobile_cnt;

                }
                if($req_landline_cnt>$landline_cnt)
                {
                    $req_landline_cnt=$landline_cnt;
                }
            }
            else
            {
                $req_landline_cnt = $landline_cnt;
                $req_mobile_cnt = $mobile_cnt;
            }
        }
        $req_lanline_no = array_slice($landline_array,0,$req_landline_cnt);
        $req_mobile_no = array_slice($mobile_array,0,$req_mobile_cnt);
        $top_eight_array = array_merge($req_lanline_no,$req_mobile_no);
        $without_space_top_eight_array = array();
        foreach($top_eight_array as $number)
        {
            $number = explode(" ", trim($number));
            $without_space_top_eight_array[] = $number[0];
        }
        $top_eight_array = $without_space_top_eight_array;


        $extra_str="[top mapped number from database:".implode(",",$top_eight_array)."]";
        $this->logmsgvirtualno("Get top most contact numbers fro virtual mapped number.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);

        return $top_eight_array;
    }
    function get_top_mappfromtechinfo($vrn)
    {
        $techinfo_array= array();
        $ch = curl_init();
        $curl="http://".constant(strtoupper($this->city_vn ).'_TAG_IP')."/justdial/vrnsearch.php?VN=".$vrn;
        $ans=curl_setopt($ch, CURLOPT_URL,$curl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
		$output =curl_exec($ch);
        if($output!=false)
        {
            $output = str_replace('&nbsp;', '&#160;', $output);
            $output = str_replace('Bussiness Id', 'BusinessId', $output);
            $xmlDoc = new DOMDocument();
            $xmlDoc->loadXML($output);
            $Result = $xmlDoc->getElementsByTagName( "Result" );
            foreach($Result as $obj)
            {
                $Code = $obj->getElementsByTagName("Code");
                $Code = $Code->item(0)->nodeValue;
                $text = $obj->getElementsByTagName("Text");
                $text = $text->item(0)->nodeValue; 
                $vn   = $obj->getElementsByTagName("VN");
                $vn   = $vn->item(0)->nodeValue;
                $vn_arry = explode(" ",$vn);
                $Status = $obj->getElementsByTagName("Status");
                $Status = $Status->item(0)->nodeValue;
                $Status_arry = explode(" ",$Status);
                $BusinessId = $obj->getElementsByTagName("BusinessId");
                $BusinessId = $BusinessId->item(0)->nodeValue;
                $BusinessId_arry = explode(" ",$BusinessId);
                $Ph1 = $obj->getElementsByTagName("Ph1");
                $Ph1 = $Ph1->item(0)->nodeValue;
                $Ph1_arry = explode(" ",$Ph1);
                $Ph2 = $obj->getElementsByTagName("Ph2");
                $Ph2 = $Ph2->item(0)->nodeValue;
                $Ph2_arry = explode(" ",$Ph2);
                $Ph3 = $obj->getElementsByTagName("Ph3");
                $Ph3 = $Ph3->item(0)->nodeValue;
                $Ph3_arry = explode(" ",$Ph3);
                $Ph4 = $obj->getElementsByTagName("Ph4");
                $Ph4 = $Ph4->item(0)->nodeValue;
                $Ph4_arry = explode(" ",$Ph4);
                $Ph5 = $obj->getElementsByTagName("Ph5");
                $Ph5 = $Ph5->item(0)->nodeValue;
                $Ph5_arry = explode(" ",$Ph5);
                $Ph6 = $obj->getElementsByTagName("Ph6");
                $Ph6 = $Ph6->item(0)->nodeValue;
                $Ph6_arry = explode(" ",$Ph6);
                $Ph7 = $obj->getElementsByTagName("Ph7");
                $Ph7 = $Ph7->item(0)->nodeValue;
                $Ph7_arry = explode(" ",$Ph7);
                $Ph8 = $obj->getElementsByTagName("Ph8");
                $Ph8 = $Ph8->item(0)->nodeValue;
                $Ph8_arry = explode(" ",$Ph8);
                $Mobile = $obj->getElementsByTagName("Mobile");
                $Mobile = $Mobile->item(0)->nodeValue;
                $Mobile_arry = explode(" ",$Mobile);
                $Email = $obj->getElementsByTagName("Email");
                $Email = $Email->item(0)->nodeValue;
                $Email_arry = explode(" ",$Email);
            }
            if($Code==='0')
            {
                $techinfo_array['Code']= $Code;
                $techinfo_array['Text']= $text;
                $techinfo_array['VN']= $vn_arry[1];
                $techinfo_array['Status']= $Status_arry[1];
                $techinfo_array['BusinessId']= $BusinessId_arry[1];
                $techinfo_array['Ph1']= $Ph1_arry[1];
                $techinfo_array['Ph2']= $Ph2_arry[1];
                $techinfo_array['Ph3']= $Ph3_arry[1];
                $techinfo_array['Ph4']= $Ph4_arry[1];
                $techinfo_array['Ph5']= $Ph5_arry[1];
                $techinfo_array['Ph6']= $Ph6_arry[1];
                $techinfo_array['Ph7']= $Ph7_arry[1];
                $techinfo_array['Ph8']= $Ph8_arry[1];
                $techinfo_array['Mobile']= $Mobile_arry[1];
                $techinfo_array['Email']= $Email_arry[1];

                $get_arry_str='';
                $get_arry_str = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($techinfo_array), array_values($techinfo_array));

                $extra_str="[Top 4 Techinfo mapped numbers array : ".implode($get_arry_str)."][Url send:".$curl."][Url retusn Result: ".$Code."]";

                $this->logmsgvirtualno("Get Top 4 Techinfo mapped numbers, get success",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $techinfo_array;
            }
            else if($Code>0)
            {
                $techinfo_array['Code']= $Code;
                $get_arry_str='';
                $get_arry_str = array_map(create_function('$key, $value', 'return $key.":".$value." # ";'), array_keys($techinfo_array), array_values($techinfo_array));

                $extra_str="[Url send:".$curl."][Url retusn Result: ".$Code."][array send :".implode($get_arry_str)."]";

                $this->logmsgvirtualno("Get Top 4 Techinfo mapped numbers",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $techinfo_array;
            }        
        }
        else
        {
            $extra_str="techinfo server us down when get information from url with virtual number retrun techinfo count array is".count($techinfo_array)."-->".implode(",",$techinfo_array)."Url run :[".$curl."]";
            $this->logmsgvirtualno("Techinfo server is down or not working",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
        return $techinfo_array;
    }
    function recursive_parentid($parent_link_arr)
    {
        if(is_array($parent_link_arr))
        {
            $parent_link_arr=array_filter($parent_link_arr);
            $parent_link_arr=array_merge($parent_link_arr);
            $link_str=implode("','",$parent_link_arr);
            $qry_get_unique_parent = "SELECT group_concat(distinct parentid separator ',') as parent_id FROM d_jds.tbl_company_refer where parentid in ('".$link_str."') or scheme_parentid in ('".$link_str."') having parent_id!='' ;";
            $res_get_unique_parent = $this->conn_local->query_sql($qry_get_unique_parent);
            if($res_get_unique_parent && mysql_num_rows($res_get_unique_parent)>0)
            {
                $row_get_unique_parent = mysql_fetch_assoc($res_get_unique_parent);
                $get_parent_arry=explode(",",$row_get_unique_parent['parent_id']);
                $get_parent_arry_wo_null=array_filter($get_parent_arry);
                $get_parent_arry_unique=array_unique($get_parent_arry_wo_null);print_r($get_parent_arry_unique);
                $get_unique_parent_count=count($get_parent_arry_unique);
            }
            if((count($parent_link_arr)==$get_unique_parent_count) && (trim($parent_link_arr[0])==trim($get_parent_arry_unique[0])))
            {
                return $parent_link_arr;
            }
            else
            {
                return $this->recursive_parentid($get_parent_arry_unique);
            }
        }
    }
    function check_mapped_number($genio_top_mapp,$techinfo_top_mapp)
    {
        $change_flag=0;
        for($j=0;$j<self::TOTAL_TECHINFO_MAPPENO;$j++)
        {
            if($genio_top_mapp[$j]!=trim($techinfo_top_mapp['Ph'.($j+1)]))
            {
                $change_flag=1;
                break;
            }
        }
        return $change_flag;
    }
    function get_all_info()
    {
        $sql_tcm = "SELECT parentid,virtualNumber,virtual_mapped_number,mobile_feedback,email_feedback,stdcode,companyname,blockforvirtual,landline,mobile,area,pincode from ".DB_IRO.".tbl_companymaster_generalinfo where parentid='".$this->parentid."'";
        $res_tcm = $this->conn_local->query_sql($sql_tcm); 
        if($res_tcm && mysql_num_rows($res_tcm) > 0)
		{
            $row_tcm   			    = mysql_fetch_assoc($res_tcm);
			$virtualnumber 		    = $row_tcm['virtualNumber'];
			$virtual_mapped_number  = $row_tcm['virtual_mapped_number'];
            $mobile_feedback_str    = $row_tcm['mobile_feedback'];
			$email_str 			    = $row_tcm['email_feedback'];
            $stdcode                = $row_tcm['stdcode'];
			$parentid 			    = $row_tcm['parentid'];
			$companyName 		    = $row_tcm['companyname'];
            $hideforvirtual 	    = $row_tcm['blockforvirtual'];
            $landline_number_str 	= $row_tcm['landline'];
            $mobile_number_str 	    = $row_tcm['mobile'];
            $comp_pincode           = $row_tcm['pincode'];
            $area                   = $row_tcm['area'];

            $extra_str="[parent id : ".$this->parentid."][virtual no: ".$virtualnumber."] [virtual map no: ".$virtual_mapped_number."] [Mobile feedback : ".$mobile_feedback_str."] [email : ".$email_str."] [hide for virtual flag : ".$hideforvirtual ."] [landline no: ".$landline_number_str."] [mobile no: ".$mobile_number_str."][STD code : ".$stdcode."][company pincode : ".$comp_pincode."][company area :".$area."]";
            $this->logmsgvirtualno("Get all information from company master general info.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
        }
        return array($virtualnumber,$virtual_mapped_phone_number,$email_str,$stdcode,$comp_pincode,$area,$parentid,$companyName,$landline_number_str,$mobile_number_str,$mobile_feedback_str);
    }
    function update_hide_flag($hide_flag)
    {
        $update_hide="UPDATE ".DB_IRO.".tbl_companymaster_generalinfo SET blockforvirtual = '".$hide_flag."' WHERE parentid='".$this->parentid."'";
        $res_update_hide = $this->conn_local->query_sql($update_hide);

        $extra_str="[Hide flag status : ".$hide_flag."][Qry run : ".$update_hide."][Qry result : ".$res_update_hide."]";
        $this->logmsgvirtualno("Update hide for virtual number flag in company master general info",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);

        return $res_update_hide;
    }
    function updt_map_number($new_map)
    {
        $update_map_compny="UPDATE ".DB_IRO.".tbl_companymaster_generalinfo SET virtual_mapped_number ='".$new_map."' WHERE parentid ='".$this->parentid."'";
        $res_update_map_compny = $this->conn_local->query_sql($update_map_compny);

        $extra_str="[virtual mapped number :".$new_map."][Qry run : ".$update_map_compny."][Qry result : ".$res_update_map_compny."]";
        $this->logmsgvirtualno("update virtual mapped number in compnaymaster using update_map_number function.",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);

        return $res_update_map_compny;
    }
    
    function get_stdcode($comp_pincode,$comp_area)
    {
        $qry_get_stdcode="SELECT stdcode FROM d_jds.tbl_area_master WHERE pincode='".trim($comp_pincode)."' AND area='".trim($comp_area)."'";
        $res_get_stdcode = $this->conn_local->query_sql($qry_get_stdcode);
        if($res_get_stdcode && mysql_num_rows($res_get_stdcode)>0)
        {
            $row_get_stdcode = mysql_fetch_assoc($res_get_stdcode);
            $new_stdcode = $row_get_stdcode['stdcode'];
            
            $extra_str="[std code from area master: ".$new_stdcode."][area : ".$comp_area."][company master pincode : ".$comp_pincode."]";

            $this->logmsgvirtualno("company master stdcode is blank so get stdcode from area master",APP_PATH.'/logs/virtualNoLogs/','Approval process',$this->parentid,$extra_str);
            return $new_stdcode;
        }
        return $new_stdcode;
    }

    function get_virtualno_frmCompMstr($virtualno_parentid)
    {
        $qry_get_old_virtualno="SELECT virtualnumber FROM ".DB_IRO.".tbl_companymaster_generalinfo WHERE parentid='".trim($virtualno_parentid)."'";
        $res_get_old_virtualno = $this->conn_local->query_sql($qry_get_old_virtualno);
        if($res_get_old_virtualno && mysql_num_rows($res_get_old_virtualno)>0)
        {
            $row_get_old_virtualno = mysql_fetch_assoc($res_get_old_virtualno);
            $get_virtual_number = $row_get_old_virtualno['virtualnumber'];
        }
        return $get_virtual_number;
    }

    function get_not_dnc_mobile($mobile_number_array)
    {
        $new_array = array();
        if(!is_object($this->dndobj))
        {
            if(!class_exists('DNDNumber'))
            {
                $extra_str="[DND class is not exist.]";
                $this->logmsgvirtualno("Call DND object",$this->log_path,'Approval process',$this->parentid,$extra_str);
                return $new_array;
            }
            $this->dndobj= new DNDNumber();
        }
        foreach($mobile_number_array as $con_value)
        {
            $Reason = $this->dndobj->IsInDNClist($this->old_conn,trim($con_value));
            if($Reason==0)
            {
                // log for virtual mapped number
                $extra_str="[mobile number mapped : ".$con_value."][mobile number is in not DND list]";
                $this->logmsgvirtualno("Check mobile number is in DND list.",$this->log_path,'Approval process',$this->parentid,$extra_str);
                $new_array[]=trim($con_value);
            }
            else
            {
                $extra_str="[mobile number mapped : ".$con_value."][mobile number is in DND list.]";
                $this->logmsgvirtualno("Check mobile number is in DND list.",$this->log_path,'Approval process',$this->parentid,$extra_str);
            }
        }
        return $new_array;
    }

    function cron_dealloc_virtualno($vrnno)
    {
        $ch = curl_init();
        $curl="http://".constant(strtoupper($this->city_vn).'_TAG_IP')."/justdial/deallocate.php?VN=".trim($vrnno)."&User=CRON"; 
        $ans=curl_setopt($ch, CURLOPT_URL,$curl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
        $output =curl_exec($ch); 
        if($output===false)
        {
            $extra_str="cron deallocate url . virtual number :[".$vrnno."]";
            $this->logmsgvirtualno("Tecch- info server is dowbn or not working",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
        else
        {
            $xmlDoc = new DOMDocument();
            $xmlDoc->loadXML($output);
            $Result = $xmlDoc->getElementsByTagName( "Result" );
            foreach($Result as $obj)
            {
                $Code = $obj->getElementsByTagName("Code");
                $Code = $Code->item(0)->nodeValue;
                $text = $obj->getElementsByTagName("Text");
                $text = $text->item(0)->nodeValue; 
            }
        }
        return array($vrnno,$Code,$text);
    }
  

    function get_techinfo_free_virtualno_count()
    {
        $ch = curl_init();
        $curl="http://".constant(strtoupper($_SESSION['s_deptCity']).'_TAG_IP')."/justdial/vrnshow.php"; 
        $ans=curl_setopt($ch, CURLOPT_URL,$curl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CURL_TIMEOUT );
        curl_setopt( $ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT );
        $output =curl_exec($ch); 
        if($output===false)
        {
            $extra_str="get free virtual number count from techinfo server";
            $this->logmsgvirtualno("Tecch- info server is down or not working",$this->log_path,'Approval process',$this->parentid,$extra_str);
        }
        else
        {
            $xmlDoc = new DOMDocument();
            $xmlDoc->loadXML($output);
            $Result = $xmlDoc->getElementsByTagName( "Result" );
            foreach($Result as $obj)
            {
                $Code = $obj->getElementsByTagName("Code");
                $Code = $Code->item(0)->nodeValue;
                $text = $obj->getElementsByTagName("Text");
                $text = $text->item(0)->nodeValue; 
                $Count = $obj->getElementsByTagName("Count");
                $Count = $Count->item(0)->nodeValue; 
                $Allocated = $obj->getElementsByTagName("Allocated");
                $Allocated = $Allocated->item(0)->nodeValue;
            }
        }
    }
    
    function check_link_contract_count($pid)
    {
        global $conn_decs;
        $link_contract_count=0;
        $get_link_contract_count="SELECT parentid, GROUP_CONCAT(scheme_parentid) AS scheme_id FROM ".DB_JDS_LIVE .".tbl_company_refer WHERE  parentid = '".trim($pid)."' or scheme_parentid = '".trim($pid)."'";
        $res_link_contract_count = $this->conn_local->query_sql($get_link_contract_count);
        if($res_link_contract_count)
        {
            $link_contract_count=mysql_num_rows($res_link_contract_count);
        }
        return $link_contract_count;
    }
}

?>
