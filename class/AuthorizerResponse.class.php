<?php


include_once(dirname(__FILE__) . "/../ezpdo/ezpdo_runtime.php");

class AuthorizerResponse {
	
	/**
	 * @var string
	 */
	private $service = "";
	
	/**
	 * @var string
	 */
	private $address = "";
	
	/**
	 * @var string
	 */
	private $key     = "";
	
	/**
	 * @var int
	 */
	private $credit  = 0;
	
	/**
	 * @return ClientData
	 */
	private function getClientData() {

		$m = epManager::instance();
		
		$clientData = $m->create("ClientData");
		$clientData->setService($this->service);
		$clientData->setAddress($this->address);
		$clientData->setKey($this->key);
		
		return $clientData;
	}

	/**
	 * 
	 */
	private function findClientData($m, $clientData) {
		
		$found = $m->find($clientData);
		if (! $found) {
			
			$oldAddress = $clientData->getAddress();
			$clientData->setAddress("*");
			
			$found = $m->find($clientData);
			
			$clientData->setAddress($oldAddress);
		}
		
		return $found;
	}
	
	/**
	 * @return boolean
	 */
	private function isNewClient($clientData) {
		
		$m = epManager::instance();
		
		$found = $this->findClientData($m, $clientData);
		if (! $found) {
			return true;
		} else {
			return false;
		}		
	}
	
	/**
	 * @param ClientData $clientData  
	 */
	private function addNewClient($clientData) {
		
		if ($clientData->getKey() != "default") {
			return;
		}
		
		$m = epManager::instance();
		
		$service             = $clientData->getService();
		$defaultMaxCredit    = ServiceDefaults::getDefaultMaxCredit($service);
		$defaultExpireInDays = ServiceDefaults::getDefaultExpireInDays($service);
		
		$clientData->setKey("default");
		$clientData->setUsedCredit(0);
		$clientData->setMaxCredit($defaultMaxCredit);
		$clientData->setExpireInDays($defaultExpireInDays);
		$clientData->setTimestamp(time());
		
		try {
			
			$clientData->epSetCommittable(true);
			$m->commit($clientData);
			
		} catch (Exception $e) {
			// do nothing
		}		
	}
	
	/**
	 * 
	 */
	private function storeClient() {

		$clientData = $this->getClientData();
		
		if ($this->isNewClient($clientData)) {
			$this->addNewClient($clientData);
		}
	}

	/**
	 * @return boolean
	 */
	private function authorizeClient() {
		
		$isAuthorized = false;
		
		$m = epManager::instance();
		
		$clientData = $this->getClientData();
		$found = $this->findClientData($m, $clientData);
		if ($found) {
			
			$usedCredit = $found[0]->getUsedCredit();
			$maxCredit  = $found[0]->getMaxCredit(); 

			if (($usedCredit + $this->credit) <= $maxCredit || $maxCredit == -1) {
				
				$found[0]->incrementUsedCredit($this->credit);
				$m->commit($found[0]);
				
				$isAuthorized = true;
			}
		}
		
		return $isAuthorized;
	}
	
	/**
	 * @return string
	 */
	private function isAuthorized() {
		
		$this->storeClient();
		
		return $this->authorizeClient() ? "true" : "false";
	}
	
	/**
	 *
	 * @return int
	 */
	private function getRemainingCredit() {
		
		$remainingCredit = 0;
		
		$m = epManager::instance();
		
		$clientData = $this->getClientData();
		$found = $this->findClientData($m, $clientData);
		if ($found) {
			
			$usedCredit = $found[0]->getUsedCredit();
			$maxCredit  = $found[0]->getMaxCredit();
			
			$remainingCredit = $maxCredit - $usedCredit;
		}

		return $remainingCredit;
	}
	
	/**
	 * @param string $service
	 * @param string $address
	 * @param string $key
	 * @param int $credit
	 */
	public function __construct($service, $address, $key, $credit) {
		
		$this->service = $service;
		$this->address = $address;
		$this->key     = $key;
		$this->credit  = $credit;
	}
	
	/**
	 * @return string
	 */
	public function toString() {
		
		$isAuthorized    = $this->isAuthorized();
		$remainingCredit = $this->getRemainingCredit();
		
		$ret_val  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$ret_val .= '<AuthorizerResponse xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
		$ret_val .= 'xmlns="urn:com:phpauthorization"' . "\n";
		$ret_val .= 'xsi:schemaLocation="urn:com:phpauthorization http://file-depo.appspot.com/phpauthorization/response.xsd">' . "\n";		
		
		$ret_val .= '	<Service>'         . $this->service   . '</Service>'         . "\n";
		$ret_val .= '	<Address>'         . $this->address   . '</Address>'         . "\n";
		$ret_val .= '	<Key>'             . $this->key       . '</Key>'             . "\n";
		$ret_val .= '	<RemainingCredit>' . $remainingCredit . '</RemainingCredit>' . "\n";
		$ret_val .= '	<Authorized>'      . $isAuthorized    . '</Authorized>'      . "\n";
	
		$ret_val .= '</AuthorizerResponse>' . "\n";
		
		return $ret_val;		
	}
}

?>