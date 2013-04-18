<?php

ini_set("max_execution_time", 60);

ini_set("log_errors", 1);
ini_set("display_errors", 0);

include_once(dirname(__FILE__) . "/class/AuthorizerResponse.class.php");
include_once(dirname(__FILE__) . "/class/AuthorizerInput.class.php");

/*******************************************************************************
 * 
 ******************************************************************************/

header("Content-type: text/xml; charset=UTF-8");


$authorizerInput = AuthorizerInput::getInstance();

$service = $authorizerInput->getService();
$address = $authorizerInput->getAddress();
$key     = $authorizerInput->getKey();
$credit  = $authorizerInput->getCredit();


$authorizerResponse = new AuthorizerResponse($service, $address, $key, $credit);
echo $authorizerResponse->toString();

?>
