<?php 
require "../../functions.php";
require "../../db_login.php";
cors();
if(isset($_REQUEST) && isset($_REQUEST['guest_id'])) {
	$id = $_POST['guest_id'];
	try{
		$stmt = $db->prepare( "DELETE FROM lead_to_guest WHERE guest_id =:id" );
        $stmt->bindParam(':id', $id);
		$responce['status'] = $stmt->execute();					
	} catch (Exception $e) {
		$responce['status'] = false;
		$responce['type_error'] = $e;
	}
	echo json_encode($responce);
} else {
	echo "Неверный ключ";
}
?>