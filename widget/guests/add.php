<?php 
require "../../functions.php";
require "../../db_login.php";
cors();
if(isset($_POST) && isset($_POST['key']) && $_POST['key'] == 'nYK4dxa{bFQoQEEq%AibWTrW') {
	$responce['status'] = false;
	$id = quotemeta($_POST['guest_id']);
	$fio = addslashes(str_replace("\"","'",$_POST['fio']));
	$type_doc_approve_person = addslashes(str_replace("\"","'",$_POST['type_doc_approve_person']));
	$serial_num_person_doc = addslashes(str_replace("\"","'",$_POST['serial_num_person_doc']));
	$address = addslashes(str_replace("\"","'",$_POST['address']));
	$type_room = addslashes(str_replace("\"","'",$_POST['type_room']));
	$vid_razm = addslashes(str_replace("\"","'",$_POST['vid_razm']));
	$food = addslashes(str_replace("\"","'",$_POST['food']));
	$type_health = addslashes(str_replace("\"","'",$_POST['type_health']));
	$banket = addslashes(str_replace("\"","'",$_POST['banket']));
	$banket_price = addslashes(str_replace("\"","'",$_POST['banket_price']));
	$banket_cur = addslashes(str_replace("\"","'",$_POST['banket_cur']));
	$child_banket = addslashes(str_replace("\"","'",$_POST['child_banket']));
	$lead_id = addslashes(str_replace("\"","'",$_POST['lead_id']));
	$sitizen = addslashes(str_replace("\"","'",$_POST['sitizen']));
	$checkguest = ($_POST['checkguest']) ? 1 : 0;
	$birthday = date("Y-m-d H:i:s",strtotime($_POST['birthday']));
	$swim_pool = addslashes(str_replace("\"","'",$_POST['swim_pool']));

	$dateApproveDocument = (isset($_POST['dateApproveDocument']) and $_POST['dateApproveDocument'] != '') ? date("Y-m-d H:i:s",strtotime($_POST['dateApproveDocument'])) : Null;
	$departmentApproveDocument = (isset($_POST['departmentApproveDocument'])) ?  addslashes(str_replace("\"","'",$_POST['departmentApproveDocument'])) : '';
	$addressLife = (isset($_POST['addressLife'])) ? addslashes(str_replace("\"","'",$_POST['addressLife'])) : '';

	$responce['date'] = $address;
	
	$checkin = (empty($_POST['checkin'])) ? NULL : date("Y-m-d H:i:s",strtotime($_POST['checkin']));
	$checkout = (empty($_POST['checkout'])) ? NULL : date("Y-m-d H:i:s",strtotime($_POST['checkout']));
	
	$guest_price = addslashes(str_replace("\"","'",$_POST['guest_price']));
	$cur_price = addslashes(str_replace("\"","'",$_POST['cur_price']));
	
	$sale_id = (int)addslashes(str_replace("\"","'",$_POST['sales']));
	
	if($_POST['form_id'] == "add_guest_form") {
		
		try{
		//записываем данные по гостю и получаем id
		$sql = "INSERT INTO guests (fio, typedoc, serial_numb_doc, sitizen, address, typerooms, vidrazm, food, health, birthday, banket, price, valuta_price, sale_id, checkIn, checkOut, swim_pool, child_banket, addressLife, departmentApproveDocument, dateApproveDocument, banket_price, banket_cur) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$db->prepare($sql)->execute([$fio, $type_doc_approve_person, $serial_num_person_doc, $sitizen, $address, $type_room, $vid_razm, $food, $type_health, $birthday, $banket, $guest_price, $cur_price, $sale_id, $checkin, $checkout, $swim_pool, $child_banket, $addressLife, $departmentApproveDocument, $dateApproveDocument, $banket_price, $banket_cur]);
		$id = $db->lastInsertId();

		$query = $db->query('	SELECT MAX(`sort_position`) as current_sort_position 
													FROM `lead_to_guest` 
													WHERE lead_id = '.$lead_id);
		$lines = $query->fetchAll(PDO::FETCH_ASSOC);
		$position = $lines[0]['current_sort_position']+1;

		/*$db->exec("INSERT INTO guests (fio, typedoc, serial_numb_doc, sitizen, address, typerooms, vidrazm, food, health, birthday, banket, price, valuta_price, sale_id, checkIn, checkOut) 
					VALUES ('$fio', '$type_doc_approve_person','$serial_num_person_doc','$sitizen','$address','$type_room','$vid_razm','$food','$type_health','$birthday','$banket','$guest_price','$cur_price','$sale_id','$checkin', '$checkout')");
		$id = $db->lastInsertId();*/
		//записываем id гостя в таблицу связей со сделками
		$db->exec("INSERT INTO lead_to_guest (lead_id, guest_id, checkguest, sort_position) 
		VALUES ('$lead_id','$id',".$_POST['checkguest'].",'$position')");
		
		$responce['status'] = true;						
		} catch (Exception $e) {
			$responce['type_error'] = $e;
		}
		
	echo json_encode($responce);
	
	} else if($_POST['form_id'] == "changeGuestForm") {
		
		try{
			$sql = "UPDATE guests SET fio=?, typedoc=?, serial_numb_doc=?, sitizen=?, address=?, typerooms=?, vidrazm=?, food=?, health=?, birthday=?, banket=?, price=?, valuta_price=?, sale_id=?, checkIn=?, checkOut=?, swim_pool=?, child_banket=?, addressLife=?, departmentApproveDocument=?, dateApproveDocument=?, banket_price=?, banket_cur=? where id=?";
			$stmt= $db->prepare($sql)->execute([$fio, $type_doc_approve_person, $serial_num_person_doc, $sitizen, $address, $type_room, $vid_razm, $food, $type_health, $birthday, $banket, $guest_price, $cur_price, $sale_id, $checkin, $checkout, $swim_pool, $child_banket, $addressLife, $departmentApproveDocument, $dateApproveDocument, $banket_price, $banket_cur, $id]);
						
			$st = $db->prepare("UPDATE `lead_to_guest` 
								SET `checkguest`=".$_POST['checkguest']."
								WHERE `guest_id`=".$id);
			$st->execute();
			$responce['status'] = true;	
		} catch(Exception $e) {
			$responce['type_error'] = $e;
		}
		
	echo json_encode($responce);
	
	}
	
} else {
	echo "Неверный ключ";
}
?>