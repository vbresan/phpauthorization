<?php

include_once(dirname(__FILE__) . "/class/AuthorizerClient.class.php");

/*******************************************************************************
 * 
 ******************************************************************************/

$credit = 1;
if (AuthorizerClient::hasCredit("MyService", "SecretKey", $credit)) {
	echo "You can use the service!";
} else {
	echo AuthorizerClient::hasNoCreditMessage();
}

?>