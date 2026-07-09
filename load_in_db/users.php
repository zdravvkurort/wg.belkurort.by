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
$usersAmo = $account['users'];
$statusesAmo = $account["leads_statuses"];


//vardump($usersAmo);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE users SET active=0";
    $stmt = $db->prepare($sql);
    $stmt->execute();

// Заносим Юзеров в БД
foreach($usersAmo as $user) {
    $user_id = $user['id'];
    $user_name = $user['name'];
    $user_last = $user['last_name'];
	$login = $user['login'];
	$photo_url = $user["photo_url"];
    $user_group_id = $user['group_id'];
	$active = $user['active'];
	
$stmt =  $db->query("SELECT * FROM users where id =".$user_id);
$userfromdb = $stmt->fetchAll();

if(count($userfromdb) > 0) {
     // set the PDO error mode to exception
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "UPDATE users SET name='".$user_name."', active=1, last_name='".$user_last."', group_id='".$user_group_id."', photo_url='".$photo_url."', login='".$login."' WHERE id='".$user_id."'";
    $stmt = $db->prepare($sql);
    $stmt->execute();

$conn = null;
} else {
//$sql = "INSERT INTO users (id, active, name, last_name, group_id, photo_url, login) VALUES (?,?,?,?,?,?,?)";
//$db->prepare($sql)->execute([$user_id, 1, $user_name, $user_last, $user_group_id, $photo_url, $login]);
	$db->query("INSERT INTO users (id, name, last_name, group_id, photo_url, login, active) VALUES('".$user_id."','".$user_name."','".$user_last."','".$user_group_id."','".$photo_url."','".$login."', '1')");
//	$db->execute();
}
}

//Удаляем статусы из БД
			$stmt = $db->prepare( "DELETE FROM statuses");
			$stmt->execute();

//Заносим статусы в БД
foreach($statusesAmo as $status) {
	$stmt = $db->prepare("INSERT INTO statuses VALUES('".$status['id']."','".$status['name']."','".$status['pipeline_id']."')");
	$stmt->execute();
}

?>