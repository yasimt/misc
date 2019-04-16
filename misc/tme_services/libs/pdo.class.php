<?php
/***************************************************
 * Library : PDOLib
 * This Library is used to combine all the important
 * features of PDO(PHP Data Objects) and make them 
 * for one easy to use.
 * Author : Sumesh Dubey
 * Copyright : Justdial Limited
 * ************************************************/
class PDOLib extends PDO{
	public function PDOLib($dbType,$con,$debug=-1) {
		$this->dbType	=	$dbType;
		$this->host		=	$con['0'];
		$this->dbName	=	$con['3'];
		$this->dbUser	=	$con['1'];
		$this->dbPass	=	$con['2'];
		
		try {
			$this->dbh = new PDO($this->dbType.":host=".$this->host.";dbname=".$this->dbName."", $this->dbUser, $this->dbPass);
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
		$this->debug($debug);
	}
	
	#Finding Installed Drivers in the server for PDO
	public function findInstalledDrivers() {
		$returnVal = PDO::getAvailableDrivers();
		return $returnVal;
	}
	
	# Method used for Prepared Statements
	public function debug($debug) {
		if($debug == 1) {
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
	}
	
	# Method used for Prepared Statements
	public function prepareStatements($query,$arrPrepare=array()) {
		$this->statement = $this->dbh->prepare($query);
		$this->statement->execute($arrPrepare);
		return $this->statement;
	}
	
	# Method used for Returning All Fetched Data
	public function fetchAll() {
		$statement	=	$this->statement->fetchAll();
		return $statement;
	}
	
	# Executing Query
	public function query($query) {
		$this->exec	=	$this->dbh->query($query);
		return $this->exec;
	}
	
	# Method Used for Fetching an Associative Array for the result of the query
	public function fetchAssoc() {
		$results = $this->dbh->fetch(PDO::FETCH_ASSOC);
		return $results;
	}
	
	# Method used for Fetching Number of results returned
	public function fetchNum() {
		$results = $this->dbh->fetch(PDO::FETCH_NUM);
		return $results;
	}
}
