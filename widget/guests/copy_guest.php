<?php 
require "../../functions.php";
require "../../db_login.php";
cors();
if(isset($_POST) && $_POST['key'] == 'nYK4dxa{bFQoQEEq%AibWTrW' && isset($_POST['guestId']) && isset($_POST["leadId"])) {
	$guestId = $_POST['guestId'];
	$leadId = $_POST['leadId'];
	$col = '';
	$values = '';
	try{
		$stmt = $db->query('SELECT * FROM guests WHERE id='.$guestId);
		$guest = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$guest = $guest[0];

		unset($guest["id"]);
		$keys = array_keys($guest);
		$col = implode(", ", $keys);
		$values = ":".implode(", :", $keys);

		$db->prepare("INSERT INTO guests (".$col.") 
															VALUES (".$values.")")->execute($guest);
		$newGuestId = $db->lastInsertId();
		$guest["id"] = $newGuestId;	

		$stmt = $db->query('SELECT * FROM lead_to_guest WHERE lead_id = '.$leadId.' and guest_id = '.$guestId);
		$link = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$link = $link[0];
		unset($link["id"]);
		array_values($link);
		$keys = array_keys($link);
		$col = implode(", ", $keys);
		$values = ":".implode(", :", $keys);
		$link["guest_id"] = $newGuestId;
		$link["sort_position"] = count($link) + 1;

		$db->prepare("INSERT INTO lead_to_guest (".$col.") 
															VALUES (".$values.")")
										->execute($link);
		$newLinkId = $db->lastInsertId();
		$responce['status'] = true;	
		$responce['guest']	= $guest["id"];		

	} catch (Exception $e) {
		$responce['status'] = false;
		$responce['type_error'] = $e;
	}
	echo json_encode($responce);
} else {
	echo "Неверный ключ";
}
?>