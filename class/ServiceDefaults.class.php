<?php

include_once(dirname(__FILE__) . "/../ezpdo/ezpdo_runtime.php");

class ServiceDefaults {
    /**
     * @access public
     * @var int
     */
	public static $DEFAULT_MAX_CREDIT = 10;
	
    /**
     * @access public
     * @var int
     */	
	public static $DEFAULT_EXPIRE_IN_DAYS = 1;
	
    /**
     * @access public
     * @var string
	 * @orm char(64) unique(service)     
     */
    public $service = null;	
	
    /**
     * @access public
     * @var int
	 * @orm int(12)     
     */
    public $maxCredit = null;

    /**
     * @access public
     * @var int
	 * @orm int(12)     
     */
    public $expireInDays = null;
    
    /**
     * @param string $service
     * @return int
     */
    public static function getDefaultMaxCredit($service) {
    	
    	$m = epManager::instance();
    	
    	$clientData = $m->create("ServiceDefaults");
		$clientData->setService($service);
		
    	$found = $m->find($clientData);
		if ($found) {
			return $found[0]->getMaxCredit();
		} else {
			return ServiceDefaults::$DEFAULT_MAX_CREDIT;
		}		
    }
    
    /**
     * @param string $service
     * @return int
     */
    public static function getDefaultExpireInDays($service) {
    	
        $m = epManager::instance();
    	
    	$clientData = $m->create("ServiceDefaults");
		$clientData->setService($service);
		
    	$found = $m->find($clientData);
		if ($found) {
			return $found[0]->getExpireInDays();
		} else {
			return ServiceDefaults::$DEFAULT_EXPIRE_IN_DAYS;
		}
    }    
}

?>