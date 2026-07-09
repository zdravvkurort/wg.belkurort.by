<?php
//подключаем БД
require "../db_login.php";
require "../functions.php";

$integration_id = "d9b8c68f-1a18-494f-a6fa-71ac49c2d041";

$stmt = $db->query('SELECT * FROM `amo_tokens` WHERE `integration_id` = "'.$integration_id.'" ORDER BY `timestamp` DESC LIMIT 1');
$tokenData = $stmt->fetchAll();

$subdomain = 'zdravkyrort'; //Поддомен нужного аккаунта
$link = 'https://zdravkyrort.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

// Соберем данные для запроса 
// запрос на обновление
$data = [
	'client_id' => $integration_id,
	'client_secret' => 'Ymm63BS8IN3zJAHFWYEUM0THjOSBr2FDRBVU6IJKr0SNL1hDo6vOSRcVT2wOnTdb',
	'grant_type' => 'refresh_token',
	'refresh_token' => $tokenData[0]["refresh_token"],
	'redirect_uri' => 'https://wg.belkurort.by/widget/buttonGet/index.php',
];

// /** Соберем данные для запроса */
// $data = [
// 	'client_id' => $integration_id,
// 	'client_secret' => 'Ymm63BS8IN3zJAHFWYEUM0THjOSBr2FDRBVU6IJKr0SNL1hDo6vOSRcVT2wOnTdb',
// 	'grant_type' => 'authorization_code',
// 	'code' => 'def50200fb9cb6b6ba6cdfa6de611becfe594e0ccafbec0f70d2141a42ec22e49f775476b4e7d962c5f687ee26f391205cfd8ea6840eeee74393da43ef19d312c58b25bbeaddab8b91d6e949e656354f4484b841168494791771933b4443e8ecb020a0e6c1cbee3b2cd5b2346d67a09796927718d5335f9e74551f7ed570963fa0c3a479fcb2c3114dc88694708e4cda55cf7c566d808c9ff1e6ca5e3b502ef399afdf4d42c9199099464e469de377f05933bc339b141f4899c92ae849508aea01d33542ff90cbfa65932e6b4b799fc1503fee38a96ff7565a55fbf0ced245afdeb885441c7f1c7e8370f65ce145a1002180f346f8df68574f113eefeb63c4ebada603cdb789a6cb7ca7da0fe30de6e1baf5a9cb6b69d76e8a83f26b76cc5094c5096e7bc909757f81195d6b6d6fe27c0edea17bc6903092c827575fd02a54a3c4c5dc89df61396c4d38443f3eaa7a4bd50649e4ae603cc12581143825998c142189f2f601212f2bf7b6ed07a026194ffadea5440cbe6058b449a4e48dc75736b9d9bad07080f1a0e70e9e9658eaa6380702550f5b7ad65d510dac3a4acaf8e9f27b893dd4d8b55291f3148a75892cdb13d84d2b82d5c2c4a075',
// 	'redirect_uri' => 'https://wg.belkurort.by/widget/buttonGet/index.php',
// ];


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

// Данные получаем в формате JSON, поэтому, для получения читаемых данных,
// нам придётся перевести ответ в формат, понятный PHP

$response = json_decode($out, true);

$access_token = $response['access_token']; //Access токен
$refresh_token = $response['refresh_token']; //Refresh токен
$token_type = $response['token_type']; //Тип токена
$expires_in = $response['expires_in']; //Через сколько действие токена истекает

$st = $db->prepare("INSERT INTO `amo_tokens`(`access_token`, `refresh_token`, `token_type`, `expires_in`, `integration_id`) VALUES ('".$access_token."','".$refresh_token."','".$token_type."','".$expires_in."','".$integration_id."')");
$st->execute();