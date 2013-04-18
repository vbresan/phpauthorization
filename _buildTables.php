<?php

include_once(dirname(__FILE__) . "/ezpdo/ezpdo_runtime.php");

$m = epManager::instance();

$ea1 = $m->create("ServiceDefaults");
$m->find($ea1);

$ea2 = $m->create("ClientData");
$m->find($ea2);

echo "Done!";

?>