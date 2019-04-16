<?php
session_start();
require_once(APP_PATH."library/config.php");
include_once(APP_PATH."library/path.php");
require_once(APP_PATH."library/genio_functions.php");
$sphinx_id = $_SESSION['sphinx_id'];
if(isset($_SESSION['sphinx_id']))
{
	if($sphinx_id == '' || $sphinx_id ==0) {
		$sphinx_id = getContractSphinxId($_SESSION['parentid']);
	}
		$genio_variables = get_company_data($sphinx_id);
}

?>
