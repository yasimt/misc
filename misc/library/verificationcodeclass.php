<?php

class verificationCode
{


function GenerateRandomValidationCode()
{
	return rand(100000,999999);
}

function writeValidationCodeInTable($connection,$parentid,$validationcode)
{
	//echo "\n parentid--".$parentid."--validationcode".$validationcode;
	$sql= "Insert into mobilemail_verification_code set
	parentid='".$parentid."',
	validationcode='".$validationcode."'
	ON Duplicate KEY UPDATE
	validationcode='".$validationcode."'";
	$connection->query_sql($sql);
}

function readValidationCodeFromTable($connection,$parentid,$validationcode)
{
	$validationcode=null;
	$sql= "select validationcode from mobilemail_verification_code where parentid='".$parentid."'";	
	$res = $connection->query_sql($sql);

	if($res)
	{
		$resarr = $connection->fetchdata($res);
		$validationcode= $resarr['validationcode'];
	}
	
	return $validationcode;	
}

function varifyValidationCode($conn,$parentid,$code)
{
	//echo "\n parentid--".$parentid."--code".$code;
	$validationstatus='fail';
	$sql= "select validationcode from mobilemail_verification_code where parentid='".$parentid."'";	
	$res = $conn->query_sql($sql);

	if($res)
	{
		$resarr = $conn->fetchdata($res);
		if($code == $resarr['validationcode'] && $code!='')
		{	
			$validationstatus='pass';
		}
	}
	
	return $validationstatus;	
}
	
}


?>
