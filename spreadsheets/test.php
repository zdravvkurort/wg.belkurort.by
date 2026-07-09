<?
require "../db_login.php";
require "../functions.php";

$stmt = $db->query('SELECT `id`,`name` FROM `users`');
$managerList = $stmt->fetchAll();

$stmt = $db->query('SELECT * FROM leads limit 5');
$leadslist = $stmt->fetchAll();
vardump($leadslist);
?>