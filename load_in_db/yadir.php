<?php
	require_once "../db_login.php";
	require_once "../functions.php";
	require_once 'functions.php';

	$stmt = $db->query("SELECT MAX(Date) FROM `ad_costs` WHERE Channel like '%ндекс%'");
	$max_date = $stmt->fetchAll();

$datenow = date('Y-m-d');
$date_start = ($max_date<date('Y-m-d', strtotime($datenow. ' - 3 days'))) ? $max_date : date('Y-m-d', strtotime($datenow. ' - 3 days'));
$date_start =($date_start == Null) ? "2019-05-01" : $date_start;

// Настройки для вывода содержимого буфера, которые позволяют делать вывод на экран
// при использовании функции sleep
ob_implicit_flush();
//— Входные данные —————————————————//
// Адрес сервиса Reports для отправки JSON-запросов (регистрозависимый)
//$url = 'https://api-sandbox.direct.yandex.com/json/v5/reports'; // при работе с песочницей, используем поддомен api-sandbox
$url = 'https://api.direct.yandex.com/json/v5/reports'; // при работе с песочницей, используем поддомен api-sandbox
// OAuth-токен пользователя, от имени которого будут выполняться запросы, его получали на предыдущем этапе
$token = 'AgAAAAAzYTLdAAW--aD80yUPXUnWqAwansxb7hA';
// Логин клиента рекламного агентства
// Обязательный параметр, если запросы выполняются от имени рекламного агентства
// Укажите здесь логин аккаунта на котором получен тестовый доступ к API Директа
$clientLogin = 'zdravkyrort@yandex.ru';
//— Подготовка запроса ————————————————//
// Создание тела запроса
$report_name = date("Y-m-d-H-i-s"); // Используем текущую дату и время с точностью до секунд в качестве названия отчёта, т.к. при каждом запросе должно передаваться уникальное название отчёта
$params = [
"params" => [
"SelectionCriteria" => [
"DateFrom" => $date_start, // начальная дата в формате ГГГГ-ММ-ДД
"DateTo" => $datenow // конечная дата для выборки статистики
],
"FieldNames" => ["Date", "AdNetworkType", "Impressions", "Clicks", "Cost"],
"ReportName" => "отчёт 1 old".$report_name,
"ReportType" => "CUSTOM_REPORT", // прописываем произвольный тип отчёта
"DateRangeType" => "CUSTOM_DATE", // произвольный период запроса
"Format" => "TSV", // формат ответа API
"IncludeVAT" => "YES",
"IncludeDiscount" => "YES"
]
];
// Преобразование входных параметров запроса в формат JSON
$body = json_encode($params);

// Создание HTTP-заголовков запроса
$headers = array(
// OAuth-токен. Использование слова Bearer обязательно
"Authorization: Bearer $token",
// Логин клиента рекламного агентства
"Client-Login: $clientLogin",
// Язык ответных сообщений
"Accept-Language: ru",
// Режим формирования отчета
"processingMode: auto",
// Формат денежных значений в отчете
"returnMoneyInMicros: false",
// Не выводить в отчете строку с названием отчета и диапазоном дат
"skipReportHeader: true",
// Не выводить в отчете строку с названиями полей
// "skipColumnHeader: true",
// Не выводить в отчете строку с количеством строк статистики
"skipReportSummary: true"
);

// Инициализация cURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

/*
Для полноценного использования протокола HTTPS можно включить проверку SSL-сертификата сервера API Директа.
Чтобы включить проверку, установите опцию CURLOPT_SSL_VERIFYPEER в true, а также раскомментируйте строку с опцией CURLOPT_CAINFO и укажите путь к локальной копии корневого SSL-сертификата.
*/
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($curl, CURLOPT_CAINFO, getcwd().'\CA.pem');

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLINFO_HEADER_OUT, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

// — Запуск цикла для выполнения запросов —
// Если получен HTTP-код 200, то выводится содержание отчета
// Если получен HTTP-код 201 или 202, выполняются повторные запросы
while (true) {
sleep(1);
$result = curl_exec($curl);

if (!$result) {

//echo ('Ошибка cURL: '.curl_errno($curl).' — '.curl_error($curl));

break;

} else {

// Разделение HTTP-заголовков и тела ответа
$responseHeadersSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$responseHeaders = substr($result, 0, $responseHeadersSize);
$responseBody = substr($result, $responseHeadersSize);

// Получение кода состояния HTTP
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Извлечение HTTP-заголовков ответа
// Идентификатор запроса
$requestId = preg_match('/RequestId: (\d+)/', $responseHeaders, $arr) ? $arr[1] : false;
// Рекомендуемый интервал в секундах для проверки готовности отчета
$retryIn = preg_match('/retryIn: (\d+)/', $responseHeaders, $arr) ? $arr[1] : 60;

if ($httpCode == 400) {
/*
echo "Параметры запроса указаны неверно или достигнут лимит отчетов в очереди<br>";
echo "RequestId: {$requestId}<br>";
echo "JSON-код запроса:<br>{$body}<br>";
echo "JSON-код ответа сервера:<br>{$responseBody}<br>";
*/
break;

} elseif ($httpCode == 200) {

$responseBody = explode ("\n",$responseBody); // преобразовываем отчёт в массив, разделитель "новая строка"
foreach($responseBody as $key => $value) {
	$responseBody[$key] = explode("	",$responseBody[$key]);
	if($key == 0) {
		$params = $responseBody[$key];
	} else {
		foreach($responseBody[$key] as $k => $v) {
			$responseBody[$key][$params[$k]] = $v;
			unset($responseBody[$key][$k]);
			$k = $params[$k];
		}
	}
}

array_shift($responseBody);
array_pop($responseBody);

//Получаем курсы на нужные даты
$currency = file('http://www.nbrb.by/API/ExRates/Rates/Dynamics/298?startDate='.$date_start.'&endDate='.$datenow);
$currency = json_decode($currency[0],true);

//меняем бел руб на рос руб
foreach($responseBody as $item => $val) {
	$responseBody[$item]['Cost'] = 100/find_currency($currency, $responseBody[$item]['Date']) * $responseBody[$item]['Cost']*1.2;
}
$search = [];
$rsya = [];
foreach($responseBody as $body) {
	if($body["AdNetworkType"] == "SEARCH") {
		array_push($search,$body);
	} else {
		array_push($rsya,$body);
	}
};

(count($search) > 0) ? set_costs_in_db($search, 'Яндекс поиск') : print_r("");
(count($rsya) > 0) ? set_costs_in_db($search, 'Яндекс РСЯ') : print_r("");

break;

} else if ($httpCode == 201) {
/*
echo "Отчет успешно поставлен в очередь в режиме офлайн<br>";
echo "Повторная отправка запроса через {$retryIn} секунд<br>";
echo "RequestId: {$requestId}<br>";
*/
sleep($retryIn);

} elseif ($httpCode == 202) {
/*
echo "Отчет формируется в режиме offline.<br>";
echo "Повторная отправка запроса через {$retryIn} секунд<br>";
echo "RequestId: {$requestId}<br>";
*/
sleep($retryIn);

} elseif ($httpCode == 500) {
/*
echo "При формировании отчета произошла ошибка. Пожалуйста, попробуйте повторить запрос позднее<br>";
echo "RequestId: {$requestId}<br>";
echo "JSON-код ответа сервера:<br>{$responseBody}<br>";
*/
break;

} elseif ($httpCode == 502) {
/*
echo "Время формирования отчета превысило серверное ограничение.<br>";
echo "Пожалуйста, попробуйте изменить параметры запроса — уменьшить период и количество запрашиваемых данных.<br>";
echo "RequestId: {$requestId}<br>";
*/
break;

} else {
/*
echo "Произошла непредвиденная ошибка.<br>";
echo "RequestId: {$requestId}<br>";
echo "JSON-код запроса:<br>{$body}<br>";
echo "JSON-код ответа сервера:<br>{$responseBody}<br>";
*/
break;

}
}
}
curl_close($curl);
?>