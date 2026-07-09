<?
//подключаем amo
require_once "../auth.php";
require_once "../db_login.php";
require_once "../functions.php";

$lockfile = fopen("contactsNotes.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

$stmt = $db->query("SELECT MAX(`created_at`) as max_timestamp FROM `notes_contacts`");
$timestamp = $stmt->fetchAll();

$timestamp = (isset($timestamp[0]['max_timestamp'])) ? $timestamp[0]['max_timestamp'] : 1609448400;
// // $timestamp = 1609448400;
// // $timestamp = time();
$notes = getNotes($timestamp);

function getNotes($toTs) {
	$notes = [];
	$page = 1;
	$limit = 100;
	do {
		$options = [
					"filter" => [
								"updated_at" => [
                  "from" => (int)$toTs-24*60*60, 
                  "to" => (int)$toTs+24*60*60
                ]
					],
					"limit" => $limit,
					"page" => $page
		];
    $n = sendRequestToAmo('GET','/api/v4/contacts/notes', $options);
		$n = (isset($n["_embedded"]["notes"]) and count($n["_embedded"]["notes"]) > 0) ? $n["_embedded"]["notes"] : [];

    loadNotesInDB($n);
    $notes = array_merge($notes, $n);
		$page++;
	} while (count($n)-1 == $limit or count($n) == $limit);
	return $notes;
}

function loadNotesInDB($notes) {
  global $db;
  foreach($notes as $note) {
			$note['params'] = json_encode($note['params']);
			$note['_links'] = json_encode($note["_links"]);
			
			$stmt = $db->prepare('SELECT * FROM `notes_contacts` WHERE id=:id');
			$stmt->execute(['id' => $note["id"]]);
			$findedNote = $stmt->fetchAll();
			if(count($findedNote)) {
				$stmt = $db->prepare("UPDATE notes_contacts 
															SET entity_id = :entity_id,
																	created_by = :created_by,
																	updated_by = :updated_by,
																	created_at = :created_at,
																	updated_at = :updated_at,
																	responsible_user_id = :responsible_user_id,
																	group_id = :group_id,
																	note_type = :note_type,
																	params = :params,
																	account_id = :account_id,
																	_links = :_links
															WHERE id = :id");
				$stmt->execute($note);
			} else {
				$stmt = $db->prepare("INSERT INTO `notes_contacts` 
															SET `id` = :id,
																	`entity_id` = :entity_id,
																	`created_by` = :created_by,
																	`updated_by` = :updated_by,
																	`created_at` = :created_at,
																	`updated_at` = :updated_at,
																	`responsible_user_id` = :responsible_user_id,
																	`group_id` = :group_id,
																	`note_type` = :note_type,
																	`params` = :params,
																	`account_id` = :account_id,
																	`_links` = :_links");
				$stmt->execute($note);
			 }
  }
}
/*
function sendAmoRequest($type, $url, $data = []) {
	global $subdomain;
	global $access_token;
	$link = 'https://' . $subdomain . $url; //Формируем URL для запроса
	$headers = ['Authorization: Bearer ' . $access_token];
	$curl = curl_init(); //Сохраняем дескриптор сеанса cURL
	if($type == "POST") {
		curl_setopt($curl, CURLOPT_POST, 1);
		array_push($headers, "Content-Type: application/json");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	} else if($type == "GET") {
		if(count($data) > 0) {
			$link = $link."?".http_build_query($data);			
		}
	} else if("PATCH") {
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
		array_push($headers, "Content-Type: application/json");
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	}
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	

	$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
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
		if ($code < 200 || $code > 204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
		}
	}
	catch(\Exception $e)
	{
		die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
  }
	sleep(0.5);
	return json_decode($out,true);

}*/
?>