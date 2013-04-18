<?php

include_once(dirname(__FILE__) . "/class/AuthorizerClient.class.php");

/*******************************************************************************
 * 
 ******************************************************************************/

$credit = 1;
if (AuthorizerClient::hasCredit("MySite", "default", $credit)) {
	echo "You can use the web site!";
} else {
	echo AuthorizerClient::hasNoCreditMessage();
}

?>