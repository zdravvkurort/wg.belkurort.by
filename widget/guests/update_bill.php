<?php 
require "../../functions.php";
require "../../db_login.php";
cors();
$post_data = json_decode(file_get_contents('php://input'), true);
if(!isset($_POST) or !isset($post_data['key']) or $post_data['key'] !== 'nYK4dxa{bFQoQEEq%AibWTrW') {
	echo json_encode(array("error_message" => "Wrong key", "status" => "error"));
	exit;
}

$sql = "UPDATE `lead_to_guest` SET bill_num = :bill_num, bill_sum = :bill_sum, bill_currency = :bill_currency WHERE guest_id = :guest_id and lead_id = :lead_id";
$stmt = $db->prepare($sql);
$stmt->bindParam(':bill_num', $post_data["bill_num"]);
$stmt->bindParam(':bill_sum', $post_data["bill_sum"]);
$stmt->bindParam(':bill_currency', $post_data["bill_currency"]);
$stmt->bindParam(':guest_id', $post_data["id"]);
$stmt->bindParam(':lead_id', $post_data["leadId"]);

try {
    $stmt->execute();
	echo json_encode(array("status" => "success"));
} catch (PDOException $e) {
    echo json_encode(array("status" => "error", "error_message" => $e->getMessage()));
}

?>