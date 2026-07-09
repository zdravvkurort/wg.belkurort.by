<?php 

//подключаем БД
require "../../db_login.php";
require "../../functions.php";
cors();

$query = htmlspecialchars($_GET['query']);
$query = mb_strtolower($query);

$key = $_SERVER["HTTP_AUTH"];

if($_SERVER["REQUEST_METHOD"] !== 'GET' or
	 $key !== 'nYK4dxa{bFQoQEEq%AibWTrW' or 
	 !(strlen($query) >= 4)) {
		 exit;
	 }

$stmt = $db->prepare('SELECT guests.fio, leads.name, leads.id, leads.status_id
										FROM `guests` 
										JOIN `lead_to_guest` ON `guests`.id = `lead_to_guest`.guest_id
										JOIN `leads` ON `lead_to_guest`.lead_id = `leads`.id
										WHERE LOWER(`fio`) REGEXP :query
										ORDER BY `fio`');
$stmt->execute(['query' => $query]);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($leads);

?>