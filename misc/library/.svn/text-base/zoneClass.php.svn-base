<?
class zone extends dbconn
{
	function getName()
	{
		$arr=array();
		$qry	= "SELECT code, name FROM zone order by name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$arr[$row['code']]=$row['name'];
			}
		}
		return $arr;
	}

	function viewZone($zoneid='')
	{
		global $page, $pp, $limit, $fltr;

		if(!isset($pp)) $limit = "";
		else $limit = " limit ".$page.", ".$pp;

		if(!isset($fltr)) $mdfyqry = "";
		else $mdfyqry = $fltr;

		if($zoneid)
			$qry	= "SELECT name,zone1,zone2,zone3,zone4 FROM zone where code=".$zoneid;
		else
		   $qry	   = "SELECT code,name,zone1,zone2,zone3,zone4 FROM zone ".$mdfyqry." order by name ".$limit;
		return $this->execQry($qry);
	}

	function addZone($zonenm,$sp1,$sp2,$sp3,$sp4)
	{
		$strzone = $this->chkZone($zonenm);
		$arr = explode("|",$strzone);
		$strExist = $arr[0];
		$strNotExist = $arr[1];
		if($strExist=="")
		{
			//insert into db
			$qry ="INSERT INTO zone(name,zone1,zone2,zone3,zone4) VALUES('".strtoupper($zonenm)."','".$sp1."','".$sp2."','".$sp3."','".$sp4."')";
			$flg=$this->execQry($qry);
			if($flg)
				return  "New zone added in zone list";
		}
		else
			return strtoupper($strExist)." already exist in zone list";
	}

	function chkZone($zonenm)
	{
		$arrzonenm = explode(",",trim($zonenm));
		$arrZone =$this->getName();

		if(count($arrZone)==0)
			$notexist='-99';
		else
		{
			for($i=0;$i<count($arrzonenm);$i++)
			{
				$fl_array = preg_grep ("/((^\s*)|,)".$arrzonenm[$i]."((\s*$)|,)/i",$arrZone);

				 if(count($fl_array)==0)
					 $strZone1 .= $arrzonenm[$i].",";
				else
					 $strZone .= $arrzonenm[$i].",";
			}

			$exist=substr($strZone,0,strlen($strZone)-1);
			$notexist=substr($strZone1,0,strlen($strZone1)-1);
		}
		return ($exist."|".$notexist);

	}

	function editZone($zoneid,$sp1,$sp2,$sp3,$sp4)
	{
		$qry ="UPDATE zone set zone1='".$sp1."', zone2='".$sp2."', zone3='".$sp3."', zone4='".$sp4."' where code=".$zoneid;
		$flg=$this->execQry($qry);
		if($flg)
			return  "Super zones updated successfully";

	}

	function getDropDownZone($selected='')
	{
		$arrName = $this->getName();

		while (list ($key, $val) = each ($arrName)) {
			if($selected==$key)
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			else
				$strdropdwn .= "<option value='$key'>$val</option>";
		}
		return $strdropdwn;
	}

	function viewArea($areaid='')
	{
		if($areaid)
			$qry	= "SELECT name,type,zone,synname FROM area where areaid=".$areaid;
		else
			$qry	= "SELECT areaid,code,name,type,zone,synname FROM area WHERE zone<>31 order by name";

		return $this->execQry($qry);
	}

	function chkArea($areanm,$type,$zone)
	{
		$qry	= "SELECT count(*) FROM area WHERE name='".strtoupper($areanm)."' and type='".$type."' and zone='".$zone."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addArea($areanm,$sp1,$pincode,$areasyn,$code)
	{
		$flg = $this->chkArea($areanm,$pincode,$sp1);

		if($flg ==0)
		{
			$qryZone= "SELECT zone,code FROM area WHERE name='".strtoupper($areanm)."'";
			$result	= $this->execQry($qryZone);
			$getZone= mysql_fetch_row($result);

			if($getZone[0]=="")
			{
				$qryMaxCode= "SELECT max(code) FROM area";
				$rscode    = $this->execQry($qryMaxCode);
				$maxCode   = mysql_fetch_row($rscode);
				$areacode  = $maxCode[0]+1;
			}
			else
				$areacode = $getZone[1];

			if($getZone[0]==$sp1 || $getZone[0]=="")
			{
				$qry ="INSERT INTO area (code,name,type,zone,synname,updateby,updatedt) VALUES('".$areacode."','".strtoupper($areanm)."','".$pincode."','".$sp1."','".$areasyn."','".$code."',now())";
				$msg = "Area inserted successfully";

				$flg=$this->execQry($qry);
			}
			else
				$msg="Existing area map with different zone";
		}
		else
			$msg="Area already exist";
		return $msg;
	}

	function editArea($areanm,$sp1,$pincode,$areasyn,$code,$areaid)
	{
		$flg = $this->chkArea($areanm,$pincode,$sp1);

		if($flg ==0)
		{
			$qryZone= "SELECT zone,code FROM area WHERE name='".strtoupper($areanm)."'";
			$result	= $this->execQry($qryZone);
			$getZone= mysql_fetch_row($result);

			if($getZone[0]=="")
			{
				$qryMaxCode= "SELECT max(code) FROM area";
				$rscode    = $this->execQry($qryMaxCode);
				$maxCode   = mysql_fetch_row($rscode);
				$areacode  = $maxCode[0]+1;
			}
			else
				$areacode = $getZone[1];

			if($getZone[0]==$sp1 || $getZone[0]=="")
			{

				if($areaid)
				{
					$qry ="UPDATE area set code='".$areacode."',name='".strtoupper($areanm)."',type='".$pincode."',zone='".$sp1."',synname='".$areasyn."',updateby='".$code."',updatedt=now() where areaid=".$areaid;
					$msg = "Area Updated successfully";
				}
				else
				{
					$qry ="INSERT INTO area (code,name,type,zone,synname,updateby,updatedt) VALUES('".$areacode."','".strtoupper($areanm)."','".$pincode."','".$sp1."','".$areasyn."','".$code."',now())";
					$msg = "Area inserted successfully";
				}

				$flg=$this->execQry($qry);
				return $msg;
			}
			else
				return "Existing area map with different zone";
		}
		else
			return "Area already exist";
	}

/*************************////////////////
	function getAreaNameOld()
	{
		$arr=array();
		$qry	= "SELECT distinct aid, name, linage FROM tbl_arealineage where display=1 order by name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				
				if($row['name'] != $areaname)
				{
					$lineage = $row['linage'];
					$arrlineage = explode("/",$lineage);
					$arrcnt = count($arrlineage);
					if($arrcnt==6) {
						$arr[$row['aid']]=$row['name'];
					}
				}
			}
		}
		return $arr;
	}

	function getDropDownAreaOld($selected='')
	{
		$arrName = $this->getAreaName();

		while (list ($key, $val) = each ($arrName)) {
			if($selected==$key)
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			else
				$strdropdwn .= "<option value='$key'>$val</option>";
		}
		return $strdropdwn;
	}

	function getDropDownArea($selected='')
	{
		$qry	= "SELECT distinct areaid, areaname FROM tbl_areapin WHERE areaname <> '' and displayflag=1 order by areaname";
		$result = $this->execQry($qry);
		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				if($selected==$row['areaid'])
					$strdropdwn .= "<option value='$row[areaid]' selected>$row[areaname]</option>";
				else
					$strdropdwn .= "<option value='$row[areaid]'>$row[areaname]</option>";
			}
		}
		return $strdropdwn;
	}
/*************************////////////////

	function chkExistArea($areanm)
	{
		$qry	= "SELECT count(*) FROM tbl_arealineage WHERE upper(name)='".strtoupper($areanm)."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addAreaData($areanm,$parareanm,$parareaid,$areasyn,$state,$flgstatus)
	{
		#CHECK WHEATHER AREA EXIST OR NOT
		#INSERT INTO SYNONYM IN AREASYN TABLE
		#INSERT AREA IN AREALINEAGE TABLE

		$flg = $this->chkExistArea($areanm);

		if($flg ==0)
		{
			if($areasyn!="")
			{
				$arrsyn	= explode("\n",$areasyn);
				$inSyn	= "insert into tbl_areasyn (mainword, synname) values ('".$areanm."','".$areanm."')";
				for($iCnt=0;$iCnt<count($arrsyn);$iCnt++)
				{
					#$inSyn .= "insert into tbl_catsyn(mainword,synname) values('".$catName."','".$arrsyn[$iCnt]."');";
					$inSyn .= ",('".$areanm."','".$arrsyn[$iCnt]."')";
				}
				$resultset = $this->execQry($inSyn);
			}

			if($parareaid!=0)
			{
				$qryZone	= "SELECT linage,country, state, city, zone, aid, aidlinage FROM tbl_arealineage WHERE aid='".$parareaid."'";
				$result		= $this->execQry($qryZone);
				$getLinage	= mysql_fetch_row($result);
				$lineage	= $getLinage[0]."".$areanm."/";
				$arrln		= explode("/",$lineage);

				$country	= ($getLinage[1]) ? $getLinage[1] : $arrln[1];
				$city		= ($getLinage[3]) ? $getLinage[3] : $arrln[2];
				$zone		= ($getLinage[4]) ? $getLinage[4] : $arrln[3];
				$area		= ($arrln[4]!="") ? $arrln[4] : $arrln[4];

				if($getLinage[6]!='')
				{
					$aidlineage=$getLinage[6]."".$getLinage[5]."/";
					$area = $areanm;
				} else {
					$aidlineage="/".$getLinage[5]."/";
				}
			}
			else
			{
				$parareaid=0;
				$lineage = "/".$areanm."/";

				if($flgstatus == 1)
					$country = $areanm;

				if($flgstatus == 3)
					$area = "";

			}

			$qry ="INSERT INTO tbl_arealineage(name, parentID, linage, country, state, city, zone, area, aidlinage)VALUES('".strtoupper($areanm)."','".$parareaid."','".strtoupper($lineage)."','".strtoupper($country)."','".strtoupper($state)."','".strtoupper($city)."','".strtoupper($zone)."','".strtoupper($area)."','".$aidlineage."')";

			$flg=$this->execQry($qry);
			$msg="Area inserted successfully";

		}
		else
			$msg="Area already exist";

		return $msg;
	}

	function chkExistAreaPin($areaid,$pincode)
	{
		$qry	= "SELECT count(*) FROM tbl_areapin WHERE areaid='".$areaid."' and pincode='".$pincode."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addAreaPinData($areaid,$areaname,$pincode)
	{
		$flg = $this->chkExistAreaPin($areaid,$pincode);
		if($flg==0)
		{
			$qryAdd ="INSERT INTO tbl_areapin(areaid, areaname, pincode) VALUES('".$areaid."','".$areaname."','".$pincode."')";
			$flg=$this->execQry($qryAdd);
			$msg="Pincode inserted successfully";
		}
		else
			$msg="Area -$areaname with Pincode already exist";
		return $msg;
	}

	function chkExistAreaGeo($apid,$areaid,$subarea,$building,$txtlong,$txtlat)
	{
		$qry = "SELECT count(*) FROM tbl_areageo WHERE areaid='".$areaid."' and longitude = '".$txtlong."' and latitude = '".$txtlat."' ";
		if($subarea) $qry .= " and subarea = '".urldecode($subarea)."' ";
		if($building) $qry .= " and building = '".urldecode($building)."' ";
		$rs  = $this->execQry($qry);
		$cnt = mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addAreaGeoData($pincode,$areaid,$areaname,$subarea,$building,$txtlong,$txtlat)
	{
		$sqlAPid = $this->execQry("SELECT apid FROM tbl_areapin WHERE areaid = '".$areaid."' and pincode ='".$pincode."' and displayflag=1");
		$objAPid = mysql_fetch_object($sqlAPid);
		$apid = $objAPid->apid;

		$flg = $this->chkExistAreaGeo($apid,$areaid,$subarea,$building,$txtlong,$txtlat);
		
		if($flg==0)
		{
			$qryAdd ="INSERT INTO tbl_areageo(apid, areaid, areaname, subarea, building, longitude, latitude) VALUES('".$apid."','".$areaid."','".urldecode($areaname)."','".urldecode($subarea)."','".urldecode($building)."','".$txtlong."','".$txtlat."')";
			$flg=$this->execQry($qryAdd);
			$msg="Geocode inserted successfully";
		}
		else
			$msg="Geocode for $areaname Area already exist";
		return $msg;
	}

	function chkExistState($countryid,$txtState)
	{
		$qry	= "SELECT count(*) FROM state_master WHERE st_name='".$txtState."' and country_id='".$countryid."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addState($countryid,$countryname,$txtState)
	{
		$flg = $this->chkExistState($countryid,$txtState);
		if($flg==0)
		{
			$qryAdd ="INSERT INTO state_master(st_name, country_id) VALUES('".strtoupper($txtState)."','".$countryid."')";
			$flg=$this->execQry($qryAdd);
			$msg="State inserted successfully";
		}
		else
			$msg="State -$txtState already exist";
		return $msg;
	}

	function getDropDownState($selected='')
	{
		$arr=array();
		$qry = "SELECT state_id,st_name FROM state_master order by st_name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				//$arr[$row['state_id']]=$row['st_name'];
				$arr[$row['st_name']]=$row['st_name'];
			}
		}
		$strdropdwn = "<option value=''>Select State</option>";
		while (list ($key, $val) = each ($arr))
		{
			if($selected==$key)
			{
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			}
			else
			{
				$strdropdwn .= "<option value='$key'>$val</option>";
			}
		}
		$strdropdwn .= "<option value='Others'>Others</option>";
		return $strdropdwn;
	}

	function getDropDownCity($stateid='',$selected='')
	{
		$arr=array();
		if($stateid=='')
			$qry = "SELECT city_id,ct_name FROM city_master order by ct_name";
		else
			$qry = "SELECT city_id,ct_name FROM city_master where state_id=$stateid order by ct_name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$arr[$row['city_id']]=$row['ct_name'];
			}
		}
		while (list ($key, $val) = each ($arr)) {
			if($selected==$key)
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			else
				$strdropdwn .= "<option value='$key'>$val</option>";
		}
		return $strdropdwn;
	}


	function chkExistCity($stateid,$txtCity)
	{
		$qry	= "SELECT count(*) FROM city_master WHERE ct_name='".$txtCity."' and state_id='".$stateid."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addCity($stateid,$statename,$txtCity)
	{
		$flg = $this->chkExistCity($stateid,$txtCity);
		if($flg==0)
		{
			$qryAdd ="INSERT INTO city_master(ct_name, state_id,country_id) VALUES('".strtoupper($txtCity)."','".$stateid."',(select country_id from state_master where state_id='$stateid'))";
			$flg=$this->execQry($qryAdd);
			$msg="City inserted successfully";
		}
		else
			$msg="City -$txtCity already exist";
		return $msg;
	}

	function getDropDownStateID($selected='')
	{
		$arr=array();
		$qry = "SELECT state_id,st_name FROM state_master order by st_name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$arr[$row['state_id']]=$row['st_name'];
			}
		}
		$strdropdwn = "<option value=''>Select State</option>";
		while (list ($key, $val) = each ($arr)) {
			if($selected==$key)
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			else
				$strdropdwn .= "<option value='$key'>$val</option>";
		}
		$strdropdwn .= "<option value='Others'>Others</option>";
		return $strdropdwn;
	}

	function getDropDownCityID($stateid='',$selected='')
	{
		$arr=array();
		if($stateid=='')
			$qry = "SELECT city_id,ct_name FROM city_master order by ct_name";
		else
			$qry = "SELECT city_id,ct_name FROM city_master where state_id=$stateid order by ct_name";
		$result = $this->execQry($qry);

		if(mysql_num_rows($result)!=0)
		{
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$arr[$row['city_id']]=$row['ct_name'];
			}
		}
		while (list ($key, $val) = each ($arr)) {
			if($selected==$key)
				$strdropdwn .= "<option value='$key' selected>$val</option>";
			else
				$strdropdwn .= "<option value='$key'>$val</option>";
		}
		return $strdropdwn;
	}
	

	function chkExistSubArea($areaid,$txtsubarea)
	{
		$qry	= "SELECT count(*) FROM tbl_subarea WHERE areaid='".$areaid."' and subarea = '".$txtsubarea."'";
		$rs		= $this->execQry($qry);
		$cnt	= mysql_fetch_row($rs);
		return $cnt[0];
	}

	function addSubAreaData($areaid,$areaname,$txtsubarea)
	{
		$flg = $this->chkExistSubArea($areaid,$txtsubarea);
		if($flg==0)
		{
			$qryAdd ="INSERT INTO tbl_subarea(areaid, areaname, subarea) VALUES('".$areaid."','".$areaname."','".$txtsubarea."')";
			$flg=$this->execQry($qryAdd);
			$msg="Subarea inserted successfully";
		}
		else
			$msg="Subarea for $areaname Area already exist";
		return $msg;
	}
}
?>