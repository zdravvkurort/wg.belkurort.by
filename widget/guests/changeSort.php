<?php 

//подключаем БД
require "../../db_login.php";
require "../../functions.php";
cors();
$result = [];

if(isset($_POST) && isset($_POST['key']) && $_POST['key'] == 'nYK4dxa{bFQoQEEq%AibWTrW') {
	
	if(!isset($_POST['leadId'])) exit; 

	$leadId = (int)$_POST['leadId'];
	$guestsSortAmo = $_POST['guests'];

	try {
		$query = $db->query('	SELECT *
													FROM `lead_to_guest` 
													WHERE lead_id = '.$leadId.'
													ORDER BY `lead_to_guest`.`sort_position` ASC');
		$guestsDB = $query->fetchAll(PDO::FETCH_ASSOC);
	} catch (\Throwable $th) {
		exit;
	}

		foreach($guestsSortAmo as $amoSortId => $guestAmo) {
			$dbObject = findLink($guestAmo, $guestsDB);

			if($dbObject and (int)$dbObject["sort_position"] != $amoSortId+1) {
			$db->prepare('UPDATE `lead_to_guest` SET sort_position=? WHERE id=?')->execute([$amoSortId+1, $dbObject['id']]);
			$db->lastInsertId();
			}
		}

}

function findLink($guestId, $guestsDB) {
	foreach($guestsDB as $guestDB) {
		if($guestId == $guestDB['guest_id']) {
			return $guestDB;
		}
	}
	return false;
}

	// $query = $db->query('SELECT `lead_id`
	// 											FROM `lead_to_guest` 
	// 											WHERE ISNULL(`sort_position`)
	// 											GROUP BY `lead_id`
	// 											ORDER BY `lead_to_guest`.`id` ASC');
	// $leads = $query->fetchAll(PDO::FETCH_ASSOC);

	// foreach($leads as $lead) {
	// $query = $db->query('	SELECT *
	// 											FROM `lead_to_guest` 
	// 											WHERE lead_id = '.$lead["lead_id"].'
	// 											ORDER BY `lead_to_guest`.`id` ASC');
	// $guests = $query->fetchAll(PDO::FETCH_ASSOC);

	// 	foreach($guests as $key => $guest) {
	// 		$db->prepare('UPDATE `lead_to_guest` SET sort_position=? WHERE id=?')->execute([$key+1, $guest['id']]);
	// 		$id = $db->lastInsertId();
	// 		vardump($id);
	// 	}
	// }

?>