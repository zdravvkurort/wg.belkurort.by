<!-- <?php

$subdomain = 'zdravkyrort'; // Поддомен аккаунта amoCRM
$link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; // URL для запроса токена

// Данные для получения токена по коду авторизации
$data = [
    'client_id' => 'e94ce2bf-274d-4a88-b2df-0eb2930f3b66',
    'client_secret' => 'szGCG8KJ6Sr3AkTo9TGGiW3l13nAFgjYM4NtaRFg1djCCQOssV0ILo9HAqwfAClt',
    'grant_type' => 'authorization_code', // Тип гранта - код авторизации
    'code' => 'def5020007d87e1ff766fc4df1f542a30b458e135f1448cf3dc392f676cfff9efc039ede2cee1148156ec4397ed09799729825ed96b538b4ad135917fab83cc3cbb8374323fe02cdf6baebbcc3461f12e760c223d1150bf64e421e74983349e5472800a370e0283884706a9260c14453e61c535b74f47e3c5f9411f5fd53cfbf3a6c85c701e133f5989da0336525767200ba532c72b3d22364a2745b6a9199fa60b2d4cfa4c7ab0939808a4bc0594b216be66759a9f0bd0ceffb44dd05abc35a2bd90bd239880f047e913f5c9c5ab4bb5bba03a93b251f73f6f873199aeb0761cda6bd4a460158cfed0afc20bf946dff43e4195632636d410d3dc8ddd5f1d3ee5c3404fd28cb60f568386c63beb1456433a27eab9f189e7018773c85fa26060934c2580c52af22b63f5b131ccc3c9eab382b5b8fa18207ccad8c3d662612f5825ee55513b2dc0de0f5f16a38e85914920eda61949c76c09f49ab2b789ac85506a152ce08d154c9d4acf2287bb1cb70a0d844caa89b7d4643389f6ea35e7fa0ca1e66db22c469ae394ea4b20ffe0a2aa3cafe58a9078370eb0d540eb949ca8f2ef06d3df19cce87238f9008a5655a61a114f5b9cd1e8294cad06c88c8ff',
    'redirect_uri' => 'https://wg.belkurort.by/auth.php', // Должен совпадать с настройками интеграции
];

// Инициализация cURL
$curl = curl_init();

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
curl_setopt($curl, CURLOPT_URL, $link);
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

// Выполнение запроса
$out = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

var_dump($out);

// Обработка ответа
if ($code < 200 || $code > 204) {
    die('Ошибка получения токена. Код ответа: ' . $code);
}

var_dump($response);

$response = json_decode($out, true);
$access_token = $response['access_token']; // Access токен
$refresh_token = $response['refresh_token']; // Refresh токен

// Теперь можно использовать $access_token для запросов к API amoCRM

 -->