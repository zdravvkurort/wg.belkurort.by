<?php 

//подключаем БД
require "../../db_login.php";
require "../../functions.php";
cors();
if(isset($_REQUEST['sanid']) and $_REQUEST['sanid'] != 0) {
	$found = preg_replace("/[^0-9]/", '', $_REQUEST['sanid']);

	$stmt = $db->query('SELECT id_type, name_type FROM typerooms where id_foundation ='.$found.' and active = 1');
	$indb = $stmt->fetchAll();
	
	$stmt = $db->query('SELECT id, name FROM programs where foundation_id ='.$found.' and active = 1');
	$programs = $stmt->fetchAll();

	$stmt = $db->query('SELECT id, name FROM food where foundation_id ='.$found);
	$food = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$salesout = [["option" => "Нет", "id" => "0"]];
	if($_POST['checkin'] and $_POST['checkout']) {
		$stmt = $db->query("SELECT * FROM `sales` where `foundation_id` = ".$found." and `finish` >= '".date('Y-m-d',strtotime($_POST['checkin']))."' and `start` <= '".date('Y-m-d',strtotime($_POST['checkout']))."'");
		$sales = $stmt->fetchAll();
		foreach($sales as $key => $val) {
			array_push($salesout, (object) ["option" => $val["name"],
								 "id" => $val["id"]]);
		}
	}
	
	if(isset($indb[0]) and !is_null($indb[0])) {
		$typenum = array();
		$progs = array();
		$foods = array();
		foreach($indb as $key => $val) {
			array_push($typenum, (object) ["option" => $val["name_type"],
								 "id" => $val["id_type"]]);
		}
		
		foreach($programs as $key => $val) {
			array_push($progs, (object) ["option" => $val["name"],
								 "id" => $val["name"]]);
		}

		foreach($food as $key => $val) {
			array_push($foods, (object) ["option" => $val["name"],
								 "id" => $val["name"]]);
		}
		
		if(count($progs) == 0) {
			array_push($progs, (object) ["option" => "С лечением",
								 "id" => "С лечением"]);				
			array_push($progs, (object) ["option" => "Без лечения",
								 "id" => "Без лечения"]);	 
		}
		
		$outarr = array(
			"error" => false,
			"programs" => $progs,
			"type_appart" => $typenum,
			"sales" => $salesout,
			"foods" => $foods);
	} else {
		$outarr = array(
		"error" => false,
		"type_appart" => array());
	}
	echo json_encode($outarr);

} else {
	$outarr = array("error" => true, "type_error" => "Задайте санаторий");
	echo json_encode($outarr);
}
?>