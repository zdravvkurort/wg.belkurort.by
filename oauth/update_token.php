<?php
//подключаем БД
require "../db_login.php";
require "../functions.php";

$integration_id = "e94ce2bf-274d-4a88-b2df-0eb2930f3b66";

$stmt = $db->query('SELECT * FROM `amo_tokens` WHERE `integration_id` = "'.$integration_id.'" ORDER BY `id` DESC LIMIT 1');
$tokenData = $stmt->fetchAll();

$subdomain = 'zdravkyrort'; //Поддомен нужного аккаунта
$link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

// Соберем данные для запроса 
$data = [
	'client_id' => $integration_id,
	'client_secret' => 'szGCG8KJ6Sr3AkTo9TGGiW3l13nAFgjYM4NtaRFg1djCCQOssV0ILo9HAqwfAClt',
	'grant_type' => 'refresh_token',
	'refresh_token' => $tokenData[0]["refresh_token"],
	'redirect_uri' => 'https://wg.belkurort.by/auth.php',
];


//Нам необходимо инициировать запрос к серверу.
//Воспользуемся библиотекой cURL (поставляется в составе PHP).
//Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.

$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
//Устанавливаем необходимые опции для сеанса cURL 
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
curl_setopt($curl,CURLOPT_URL, $link);
curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
curl_setopt($curl,CURLOPT_HEADER, false);
curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);

$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);
//Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом.
$code = (int)$code;
$errors = [
	400 => 'Bad request',
	401 => 'Unauthorized',
	403 => 'Forbidden',
	404 => 'Not found',
	500 => 'Internal server error',
	502 => 'Bad gateway',
	503 => 'Service unavailable',
];

try
{
// Если код ответа не успешный - возвращаем сообщение об ошибке  
	if ($code < 200 || $code > 204) {
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
	}
}
catch(\Exception $e)
{
	die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
}

//
//Данные получаем в формате JSON, поэтому, для получения читаемых данных,
//нам придётся перевести ответ в формат, понятный PHP
//
$response = json_decode($out, true);

$access_token = $response['access_token']; //Access токен
$refresh_token = $response['refresh_token']; //Refresh токен
$token_type = $response['token_type']; //Тип токена
$expires_in = $response['expires_in']; //Через сколько действие токена истекает

$st = $db->prepare("INSERT INTO `amo_tokens`(`access_token`, `refresh_token`, `token_type`, `expires_in`, `integration_id`) VALUES ('".$access_token."','".$refresh_token."','".$token_type."','".$expires_in."','".$integration_id."')");
$st->execute();