<?php

class AuthorizerInput {
	
	/**
	 * @var AuthorizerInput
	 */
	private static $instance = null; 
	
	/**
	 * @var string
	 */
	private $service = null;
	
	/**
	 * @var string
	 */	
	private $address = null;
	
	/**
	 * @var string
	 */	
	private $key = null;
	
	/**
	 * @var int
	 */	
	private $credit = null;
	
	/**
	 *
	 */
	private function __construct() {
		
		$this->service = isset($_GET["service"]) ? $_GET["service"] : "";
		$this->address = isset($_GET["address"]) ? $_GET["address"] : "";
		$this->key     = isset($_GET["key"])     ? $_GET["key"]     : "";
		$this->credit  = isset($_GET["credit"])  ? $_GET["credit"]  : "";
		
		if (strlen($this->key) == 0) {
			$this->key = "default";
		}
	}
	
	/**
	 * @return AuthorizerInput
	 */
	public static function getInstance() {
		
		if (AuthorizerInput::$instance == null) {
			AuthorizerInput::$instance = new AuthorizerInput();
		}
		
		return AuthorizerInput::$instance;
	}
	
	/**
	 * @return string
	 */
	public function getService() {
		
		return $this->service;
	}
	
	/**
	 * @return string
	 */	
	public function getAddress() {
		
		return $this->address;
	}

	/**
	 * @return string
	 */	
	public function getKey() {
		
		return $this->key;
	}

	/**
	 * @return int
	 */	
	public function getCredit() {
		
		return $this->credit;
	}
}

?>