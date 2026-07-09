<?php

$leadsFromCollector = file_get_contents('leads.txt');
$leadsFromCollector = json_decode($leadsFromCollector, true);
var_dump(count($leadsFromCollector));
if(count($leadsFromCollector) > 0) {
	// $allleads = array_merge($allleads, $leadsFromCollector);
	file_put_contents("leads.txt", json_encode([]));
}

?>