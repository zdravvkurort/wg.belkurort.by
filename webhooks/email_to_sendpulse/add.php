<?php 

require "../../sp_login.php";
require "../../functions.php";

$bookID = 157592;

$phonenumbers = [];
$emails = [];
$out = array();

foreach($_REQUEST["contacts"]["update"][0]["custom_fields"] as $item) {
	if($item["code"] == "PHONE") {
		foreach($item["values"] as $v) {
			array_push($phonenumbers, $v["value"]);
		}
	}
	else if($item["code"] == "EMAIL") {
		 foreach($item["values"] as $b) {
			 array_push($emails, $b["value"]);
		 }
	}
}
if(count($emails) >0) {
	if(count($phonenumbers) > 0) {
		foreach($emails as $e) {
			array_push($out, array(
				'email' => $e,
				'variables' => array(
					'phone' => $phonenumbers[0],
					'Имя' => $_REQUEST["contacts"]["update"][0]['name'],
				))
			);	
		};
	} else {
		foreach($emails as $e) {
			array_push($out, array(
				'email' => $e,
				'variables' => array(
					'Имя' => $_REQUEST["contacts"]["update"][0]['name'],
				))
			);	
		}
	}
$SPApiClient->addEmails($bookID, $out);	
}





?>