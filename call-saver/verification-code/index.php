<?php

// Проверяем, есть ли файл request.txt, если нет, создаем его
if (!file_exists('request.txt')) {
    touch('request.txt');
}

// Получаем параметры запроса
$params = $_REQUEST;

// Преобразуем параметры в строку
$params_str = json_encode($params, JSON_PRETTY_PRINT);

// Записываем параметры в файл
file_put_contents('request.txt', $params_str . "\n", FILE_APPEND);


?>
<head>
    <script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-token-with-polyfills-latest.js"></script>
</head>
<script>
    YaSendSuggestToken('https://wg.belkurort.by/call-saver/')
</script>