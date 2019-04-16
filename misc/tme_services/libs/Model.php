<?php

class Model {
	protected $db;
	function __construct($config=""){
		//echo "<pre>";
		require "config/database.php";
		require "config/config.php";
		require "config/genioconfig.php";
		$this->genioconfig = $genioconfig;
		$this->db = $db;
		//print_r($this->db);
		if(is_array($config))
		{
		   //print_r($config);
		   $this->db = new Database();
		   //$this->db = new DB($config);
		}       
	}

}
