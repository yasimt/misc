<?php
function curlCall($url,$fields=''){
	$ch = curl_init();
	# Set Options #
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($fields!=''){
		curl_setopt($ch,CURLOPT_POST, TRUE);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields);
	}
	# Execute Call #
	$resultString = curl_exec($ch);
	# Close Request #
	curl_close($ch);
	/*if(empty($resultString))
	{
		return "Connection time out";
	}
	else
	{
		return $resultString;
	}*/
	return $resultString;
}
?>
