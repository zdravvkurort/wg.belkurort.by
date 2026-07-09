<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки

//$today = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

//подключаем БД
require "../db_login.php";
require "../functions.php";
cors();

if($_SERVER['REQUEST_METHOD'] != 'GET') exit;

$apartIds = $_GET['id'] ? array_map('intval', $_GET['id']) : [];
if(count($apartIds) == 0) exit;

$stmt = $db->query('SELECT * FROM typerooms where id_type IN ('.implode(',', $apartIds).')');
$aparts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($aparts);

?>