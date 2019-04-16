<?php

class View {

	function __construct() {
		//echo 'this is the view';
	}

	public function render($name, $noInclude = false, $noInclude_subhead = false)
	{
		if(file_exists('views/' . $name . '.php')) {
			if ($noInclude == true) {
			require 'views/' . $name . '.php';	
			}
			else if($noInclude == false	&&	$noInclude_subhead	==	false){
				require 'views/header.php';
				require	'views/sub_header.php';
				require 'views/sidebar.php';
				require 'views/' . $name . '.php';
				require 'views/footer.php';	
			}
			else {
				require 'views/header.php';
				require 'views/' . $name . '.php';
				require 'views/footer.php';	
			}
		} else {
			require 'views/header.php';
			require 'views/login_form.php';	
			require 'views/footer.php';	
		}
	}
}
