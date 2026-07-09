<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки

//$today = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

//подключаем БД
require "../db_login.php";
require "../functions.php";
cors();
if(isset($_REQUEST['sanid']) and $_REQUEST['sanid'] != 0) {
	$found = preg_replace("/[^0-9]/", '', $_REQUEST['sanid']);

	$stmt = $db->query('SELECT name_type FROM typerooms where id_foundation ='.$found);
	$indb = $stmt->fetchAll();
	
	if(isset($indb[0]) and !is_null($indb[0])) {
		$typenum = array();
		foreach($indb as $type) {
			array_push($typenum,preg_replace("/\r?\n/", "",addslashes(htmlspecialchars($type["name_type"]))));
		}
		$outarr = array(
			"error" => false,
			"type_appart" => $typenum);
	} else {
		$outarr = array(
		"error" => false,
		"type_appart" => array());
	}
	echo json_encode($outarr);

}
?>