<?php 

//подключаем БД
require "../../db_login.php";
require "../../functions.php";
cors();
if(isset($_REQUEST['guest_id']) and $_REQUEST['guest_id'] != 0) {
	$found = preg_replace("/[^0-9]/", '', $_REQUEST['guest_id']);

	$stmt = $db->query('SELECT 	lead_to_guest.checkguest as checkguest,
								guests.fio, 
								DATE_FORMAT(guests.birthday, "%d.%m.%Y") as "birthday", 
								guests.typedoc,
								guests.serial_numb_doc,
								guests.sitizen,
								guests.address,
								guests.typerooms,
								guests.vidrazm,
								guests.food,
								guests.health,
								guests.banket,
								guests.banket_price,
								guests.banket_cur,
								guests.price,
								guests.valuta_price,
								guests.sale_id,
								guests.swim_pool,
								guests.child_banket,
								guests.addressLife,
								guests.departmentApproveDocument,
								DATE_FORMAT(guests.dateApproveDocument, "%d.%m.%Y") as "dateApproveDocument",
								DATE_FORMAT(guests.checkIn, "%d.%m.%Y") as "checkIn",
								DATE_FORMAT(guests.checkOut, "%d.%m.%Y") as "checkOut"
						FROM guests 
						left join typerooms on guests.typerooms = typerooms.id_type
						left join lead_to_guest on guests.id = lead_to_guest.guest_id
						where guests.id ='.$found);
	$indb = $stmt->fetchAll();
	if(isset($indb[0]) and !is_null($indb[0])) {
			$typenum =(object) [	"guestid" => $found,
									"offguest" => ($indb[0]["checkguest"]==0) ? false : true,
									"fio" => $indb[0]["fio"],
									"birthday" => $indb[0]["birthday"],
									"approve" => $indb[0]["typedoc"],
									"sernumdoc" => $indb[0]["serial_numb_doc"],
									"national" => $indb[0]["sitizen"],
									"address" => $indb[0]["address"],
									"typeroom" => $indb[0]["typerooms"],
									"vidrazm" => $indb[0]["vidrazm"],
									"food" => $indb[0]["food"],
									"banket" => $indb[0]["banket"],
									"banket_price" => $indb[0]["banket_price"],
									"banket_cur" => $indb[0]["banket_cur"],
									"health" => $indb[0]["health"],
									"guest_price" => $indb[0]["price"],
									"cur_price" => $indb[0]["valuta_price"],
									"sale_id" => $indb[0]["sale_id"],
									"checkin" => $indb[0]["checkIn"],
									"checkout" => $indb[0]["checkOut"],
									"swim_pool" => $indb[0]["swim_pool"],
									"child_banket" => $indb[0]["child_banket"],
									"addressLife" => $indb[0]["addressLife"],
									"departmentApproveDocument" => $indb[0]["departmentApproveDocument"],
									"dateApproveDocument" => $indb[0]["dateApproveDocument"],
									];
		
		$outarr = $typenum;
	} else {
		$outarr = array(
		"error" => false,
		"type_appart" => array());
	}
	echo json_encode($outarr);

} else {
	echo "Задайте санаторий";
}
?>