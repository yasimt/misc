<?

Class Common {
	
	# Common Fn. to replace multiple white spaces and maintain only a single space between words #
	function replaceDBlSpace($str='') {
		if(trim($str) == '')
			return;
		
		while ($pos = strpos($str,"  ")) {
			$str = str_replace("  "," ",$str);
		}

		return $str;
	}
	# Common Fn. to replace multiple white spaces and maintain only a single space between words #

	# Check Whether given Pattern of number is Mobile No. or Not #
	function isMobNum($number='') {
		if(trim($number) == '' || !is_numeric($number))
			return;
		
		$numLen = strlen($number);

		if (substr($number,0,3) == '919' && $numLen > 10)
			$number = substr($number,2,$numLen);
		else if (substr($number,0,5) == '00919' && $numLen > 10)
			$number = substr($number,4,$numLen);

		if(substr($number,0,1) == '9' && $numLen >= 10) {
			return $number;
		} else {
			return "";
		}
	}
	# Check Whether given Pattern of number is Mobile No. or Not #

	# Check Whether given Pattern of number is Phone No. or Not #
	function isPhoneNum($number='') {
		if(trim($number) == '' || !is_numeric($number))
			return;
		
		if(!$this->isMobNum($number)) {
			$numLen = strlen($number);

			if (substr($number,0,2) == '22' && $numLen > 8) {
				return substr($number,2,$numLen);				
			} else if (substr($number,0,3) == '022' && $numLen > 8) {
				return substr($number,3,$numLen);				
			} else {
				return $number;
			}
			
		} else {
			return "";
		}
	}
	# Check Whether given Pattern of number is Phone No. or Not #
}
?>