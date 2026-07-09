<?php 
if($_SERVER["REQUEST_METHOD"]=="GET" and $_REQUEST['hash'] == "jkdshglsdfoiguosdfignmdfsjhgkshdflgjhdsflkjg"){
	
	require "../../functions.php";
	//$DBConfig["db_name"] = "health_resort";
	require "../../db_login.php";

	$dog_date = $_REQUEST['date'] ? date('Y-m-d H:i:s',strtotime($_REQUEST['date'])) : date("Y-m-d H:i:s");
	var_dump($dog_date);
	// print_r(json_encode(findCursFromNBRB($dog_date)));
}

	function findCursFromNBRB($date) {
		global $db;
		$tommorow = date('Y-m-d H:i:s',strtotime($date) + (24*60*60) - 1);
		$st = $db->query("SELECT * 
							FROM `currency_quotes` 
							WHERE DATE_FORMAT(`created_at`, '%Y-%m-%d') >= DATE_SUB(STR_TO_DATE('".$tommorow."', '%Y-%m-%d'), INTERVAL 3 DAY) AND DATE_FORMAT(`created_at`, '%Y-%m-%d') <= STR_TO_DATE('".$tommorow."', '%Y-%m-%d') ORDER BY `created_at` desc");
		$currency = $st->fetchAll()[0];
		$currency = json_decode($currency['data'], true);	
		return $currency;	
	}

?>