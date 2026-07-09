<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//подключаем amoCRM
require_once "../auth.php";

//подключаем БД
require_once "../db_login.php";

//подключаем функции
require_once "../functions.php";

// получаем список пользователей
$accountInf = $amo->account;
$account = $accountInf->apiCurrent();
$groupslist = $account["groups"];
$piplines = $account['pipelines'];
add_data_in_db($groupslist);

function add_data_in_db($leads_list) {
global $db;
for($i=0;$i<count($leads_list);$i++) {
$stmt = $db->query("SELECT COUNT(*) FROM groups WHERE id = ".$leads_list[$i]['id']); //берём id
$callback = $stmt->fetchAll();

if($callback[0][0] > 0) {
$sql = "UPDATE groups SET 
	name='".$leads_list[$i]['name']."'
 
WHERE id=".$leads_list[$i]['id'];
$stmt = $db->prepare($sql);
$stmt->execute();
}
else {
	$db->query("INSERT INTO groups SET 
	id='".$leads_list[$i]['id']."',
	name='".$leads_list[$i]['name']."'
	");
	$insertId=$db->lastInsertId();
}
}
}
?>