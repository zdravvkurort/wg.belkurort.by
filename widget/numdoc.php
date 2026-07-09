<?php /*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки
*/
$today = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

//подключаем БД
require "../db_login.php";
require "../functions.php";
require_once "docjetV2/controllers/ContractModel.php";
cors();
if(!isset($_REQUEST['lead_id'])) exit;

$lid = $_REQUEST['lead_id'];

$stmt = $db->query('SELECT max(num) FROM numdoc where leadid ='.$lid);
$indb = $stmt->fetchAll()[0]['max(num)'];

if(!is_null($indb)) {
  echo $indb;
  exit;
}

$num = createContract($lid);
echo $num;
/*
$stmt = $db->query('SELECT max(num) FROM numdoc');
$actualnum = $stmt->fetchAll()[0]["max(num)"];
$actualnum = $actualnum + 1;
$db->exec("INSERT INTO numdoc (num, date, leadid) VALUES ('$actualnum', '$today','$lid')");
echo $actualnum;


$stmt = $db->prepare("INSERT INTO numdoc (num, date, leadid) VALUES (?, ?, ?)");
$stmt->execute([$actualnum, $today, $lid]);
*/
?>