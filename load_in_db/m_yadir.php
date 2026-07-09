<?php
	require_once "../db_login.php";
	require_once "../functions.php";
	require_once 'functions.php';
	require_once 'secrets.php';

	$stmt = $db->query("SELECT MAX(Date) FROM `m_ad_costs` WHERE Channel like '%ндекс%'");
	$max_date = $stmt->fetchAll();

$datenow = date('Y-m-d');
$date_start = ($max_date<date('Y-m-d', strtotime($datenow. ' - 3 days'))) ? $max_date : date('Y-m-d', strtotime($datenow. ' - 3 days'));
$date_start =($date_start == Null) ? "2019-05-01" : $date_start;
//$date_start = date('Y-m-d', strtotime("2019-12-01"));

//получаем расходы по рекламным кампаниям
// $rk_one = get_statistics_ya_dir('AQAAAAAzYTLdAAW--TbN6vUqDkXItS0_tFdNYmw','zdravkyrort@yandex.ru');
$rk_artox = get_statistics_ya_dir($ya_direct_tokens['artox2020'],'Zdravkurort-artox2020@yandex.ru');
$rk_artox_ru = get_statistics_ya_dir($ya_direct_tokens['artox_ru'],'Zdravkurort-artox-RU@yandex.ru');

//Получаем курсы на нужные даты
$currency = getCurrencyByCode("RUB");
$currency = json_decode($currency,true);

//конвертируем бел руб в рос руб
// $rk_one = change_BYN_on_USD($rk_one, 1.2);
$rk_artox = change_BYN_on_USD($rk_artox);
$rk_artox_ru = change_BYN_on_USD($rk_artox_ru);

//умножаем на соответствующий коэффициент
$koef_one = [['from' => '0000-00-00', 'koef' => 1.2]];
// $rk_one = koefCalc($rk_one, $koef_one);
$koef_artox = [['from' => '0000-00-00', 'koef' => 0.9], ['from' => '2020-12-08', 'koef' => 1.07], ['from' => '2022-08-01', 'koef' => 1.06]];

$rk_artox = koefCalc($rk_artox, $koef_artox);
$rk_artox_ru = koefCalc($rk_artox_ru, $koef_artox);

//добавляем данные в БД
// add_in_db($rk_one);
add_in_db($rk_artox);
add_in_db($rk_artox_ru);

/*
function koefCalc($rk, $koef = [['from' => '0000-00-00', "koef" => 1]]) {
	foreach($rk as $item => $value) {
		$koefficient = $koef[0]['koef'];
		foreach($koef as $k) {
			if(strtotime($k['from']) <= strtotime($rk[$item]["Date"])) {
				$koefficient = $k["koef"];
			} else {
				break;
			}
		}
		$rk[$item]['Cost'] = $rk[$item]['Cost']*$koefficient;
	}
	return $rk;
}
*/
function add_in_db($responseBody) {
	global $db;
	$search = [];
	$rsya = [];

	foreach($responseBody as $body) {
		if($body["AdNetworkType"] == "SEARCH") {
			array_push($search,$body);
		} else {
			array_push($rsya,$body);
		}
	};

	(count($search) > 0) ? m_set_costs_in_db($search, 'Яндекс поиск') : print_r("");
	(count($rsya) > 0) ? m_set_costs_in_db($rsya, 'Яндекс РСЯ') : print_r("");
}

function change_BYN_on_USD($responseBody) {
	global $currency;
	//меняем бел руб на рос руб
	foreach($responseBody as $item => $val) {
		$responseBody[$item]['Cost'] = 100/find_currency($currency, $responseBody[$item]['Date']) * $responseBody[$item]['Cost'];
		preg_match('/(?<={)[^}]*(?=})/',$responseBody[$item]["CampaignName"],$matches);
		$responseBody[$item]['Site'] = $matches[0];
	}
	return $responseBody;
}

function get_statistics_ya_dir($token,$clientLogin) {
	global $date_start;
	global $datenow;
// Настройки для вывода содержимого буфера, которые позволяют делать вывод на экран
// при использовании функции sleep
ob_implicit_flush();
//— Входные данные —————————————————//
// Адрес сервиса Reports для отправки JSON-запросов (регистрозависимый)
//$url = 'https://api-sandbox.direct.yandex.com/json/v5/reports'; // при работе с песочницей, используем поддомен api-sandbox
$url = 'https://api.direct.yandex.com/json/v5/reports'; // при работе с песочницей, используем поддомен api-sandbox

//— Подготовка запроса ————————————————//
// Создание тела запроса
$report_name = date("Y-m-d-H-i-s"); // Используем текущую дату и время с точностью до секунд в качестве названия отчёта, т.к. при каждом запросе должно передаваться уникальное название отчёта
$params = [
"params" => [
"SelectionCriteria" => [
"DateFrom" => $date_start, // начальная дата в формате ГГГГ-ММ-ДД
"DateTo" => $datenow // конечная дата для выборки статистики
],
"FieldNames" => ["Date", "AdNetworkType", "CampaignId", "CampaignName", "Impressions", "Clicks", "Cost"],
"ReportName" => "отчёт 1 ".$report_name,
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

		return('Ошибка cURL: '.curl_errno($curl).' — '.curl_error($curl));
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

		return("Параметры запроса указаны неверно или достигнут лимит отчетов в очереди<br>JSON-код ответа сервера:<br>{$responseBody}");

		break;

		} else if ($httpCode == 200) {

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
		return ($responseBody);

		break;

		} else if ($httpCode == 201) {

			// echo "Отчет успешно поставлен в очередь в режиме офлайн<br>";
			// echo "Повторная отправка запроса через {$retryIn} секунд<br>";
			// echo "RequestId: {$requestId}<br>";

			sleep($retryIn);

		} elseif ($httpCode == 202) {

			// echo "Отчет формируется в режиме offline.<br>";
			// echo "Повторная отправка запроса через {$retryIn} секунд<br>";
			// echo "RequestId: {$requestId}<br>";

			sleep($retryIn);

		} elseif ($httpCode == 500) {

			return("При формировании отчета произошла ошибка. Пожалуйста, попробуйте повторить запрос позднее<br>JSON-код ответа сервера:<br>{$responseBody}");

			break;

		} elseif ($httpCode == 502) {

			return("Время формирования отчета превысило серверное ограничение.<br>Пожалуйста, попробуйте изменить параметры запроса — уменьшить период и количество запрашиваемых данных.<br>");

			break;

		} else {

			return("Произошла непредвиденная ошибка.<br>JSON-код ответа сервера:<br>{$responseBody}<br>");

			break;

		}
	}
}
curl_close($curl);
}
?>