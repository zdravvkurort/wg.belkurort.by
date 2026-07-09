<?php 
# $username = 'sanprimo_zdrav';
# $password = '7iCztH?I+W+s';
$username = 'wg_user';
$password = 'HzYikb6DhztsfYX';
$db = new PDO('mysql:host=localhost;dbname=sanprimo_zdrav', $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$db->exec("set names utf8");
?>