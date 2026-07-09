<?php 
// require_once __DIR__ . '/amocrm.phar';
// require_once __DIR__ . '/amocrm.phar';


// //подключаем БД
// require_once "db_login.php";

// //require_once __DIR__ . '/amocrm.phar';
// require_once 'oauth/vendor/autoload.php';

// $stmt = $db->query('SELECT * FROM `amo_tokens` WHERE `integration_id` = "e94ce2bf-274d-4a88-b2df-0eb2930f3b66" ORDER BY `id` DESC LIMIT 1');
// $tokenData = $stmt->fetchAll();

// $access_token = $tokenData[0]['access_token'];


// $login = 'zdravkyrort@yandex.ru';
// $userhash = '29ee7d3b4c2aa0a177748895f5f6b8e0dfe1ba4d';
// $subdomain = 'zdravzakup.amocrm.ru';
// $amo = new \AmoCRM\Client($subdomain, $login, $userhash);
// $amo = new \AmoCRM\Client($subdomain, $login, $access_token);
// $amo = new \AmoCRM\Client('zdravzakup.amocrm.ru', 'zdravkyrort@yandex.ru', $access_token);


// $login = 'zdravkyrort@yandex.ru';
// $userhash = 'a4ced5fd3143976bb5f758d85309de767cf7c218';
// $subdomain = 'zdravzakup.amocrm.ru';
// $pas = 'zdravkurort6130031';
// $amo = new \AmoCRM\Client($subdomain, $login, $userhash);



//подключаем БД
require_once "db_login.php";

//require_once __DIR__ . '/amocrm.phar';
require_once 'oauth/vendor/autoload.php';

$stmt = $db->query('SELECT * FROM `amo_tokens` WHERE `integration_id` = "e94ce2bf-274d-4a88-b2df-0eb2930f3b66" ORDER BY `id` DESC LIMIT 1');
$tokenData = $stmt->fetchAll();

$access_token = $tokenData[0]['access_token'];
$login = 'zdravzakup.amocrm.ru/';
// $userhash = 'a4ced5fd3143976bb5f758d85309de767cf7c218';
$userhash = $access_token;
$subdomain = 'zdravzakup.amocrm.ru';
$pas = 'zdravkurort6130031';

$amo = new \AmoCRM\Client('zdravzakup.amocrm.ru', 'zdravkyrort@yandex.ru', $access_token);


?>