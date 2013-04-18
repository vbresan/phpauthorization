<?php

class ClientData {
    /**
     * @access public
     * @var string
	 * @orm char(64) unique(client)     
     */
    public $service = null;	
	
    /**
     * @access public
     * @var string
	 * @orm char(64) unique(client)     
     */
    public $address = null;
	
    /**
     * @access public
     * @var string
	 * @orm char(64) unique(client)     
     */
    public $key = null;    
    
    /**
     * @access public
     * @var int
	 * @orm int(12)     
     */
    public $usedCredit = null;
    
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
     * @access public
     * @var int
	 * @orm int(12)     
     */
    public $timestamp = null;     
    

    /**
     * @param int $increment
     */
    public function incrementUsedCredit($increment = 1) {
    	
    	$this->usedCredit += $increment;
    }
}

?>