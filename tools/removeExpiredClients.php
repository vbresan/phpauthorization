<?php

include_once(dirname(__FILE__) . "/../ezpdo/ezpdo_runtime.php");
include_once(dirname(__FILE__) . "/../class/ClientData.class.php");

$m = epManager::instance();

/*****************************************************************************/

$toFind = $m->create("ClientData");

$found = $m->find($toFind);
foreach ($found as $limitData) {
	
	$expireInDays = $limitData->getExpireInDays(); 
	if ($expireInDays != 0) {
		
		$expireTime = $expireInDays * 24 * 60 * 60 + $limitData->getTimestamp();
		if (time() > $expireTime) {
			$m->delete($limitData);
		}
	}
}

?>