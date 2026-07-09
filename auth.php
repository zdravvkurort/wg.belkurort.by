<?php
//подключаем БД
require_once "db_login.php";

//require_once __DIR__ . '/amocrm.phar';
require_once 'oauth/vendor/autoload.php';

$stmt = $db->query('SELECT * FROM `amo_tokens` WHERE `integration_id` = "e94ce2bf-274d-4a88-b2df-0eb2930f3b66" ORDER BY `id` DESC LIMIT 1');
$tokenData = $stmt->fetchAll();

$access_token = $tokenData[0]['access_token'];
$login = 'zdravkyrort@yandex.ru';
// $userhash = 'a4ced5fd3143976bb5f758d85309de767cf7c218';
$userhash = $access_token;
$subdomain = 'zdravkyrort.amocrm.ru';
$pas = 'zdravkurort6130031';

$amo = new \AmoCRM\Client('zdravkyrort.amocrm.ru', 'zdravkyrort@yandex.ru', $access_token);
?>