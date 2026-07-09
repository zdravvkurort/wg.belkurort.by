<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки

$today = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

//подключаем БД
require "../db_login.php";

if(!isset($_REQUEST['lead_id'])) exit;

$lid = $_REQUEST['lead_id'];

$stmt = $db->query('SELECT max(num) FROM numdoc where leadid ='.$lid);
$indb = $stmt->fetchAll()[0]['max(num)'];

if(!is_null($indb)) {
  echo $indb;
  exit;
}

$stmt = $db->query('SELECT max(num) FROM numdoc');
$actualnum = $stmt->fetchAll()[0]["max(num)"];
$actualnum = $actualnum + 1;
$db->exec("INSERT INTO numdoc (num, date, leadid) VALUES ('$actualnum', '$today','$lid')");
echo $actualnum;

/*
$stmt = $db->prepare("INSERT INTO numdoc (num, date, leadid) VALUES (?, ?, ?)");
$stmt->execute([$actualnum, $today, $lid]);
*/
?>