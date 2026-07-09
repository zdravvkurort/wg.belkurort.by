<?
//подключаем amo
require_once "../auth.php";
require_once "../functions.php";
require_once "../db_login.php";

// $users = getUsersIdFromDB($db);
$date = date('d.m.Y',strtotime(date('d.m.Y')));

vardump(getDurationFromCRM($date, $user_id));

/*
foreach($users as $user) {
    $dayStatictic = getDurationFromCRM($date, $user["id"]);
    if(isset($dayStatictic["_embedded"]["items"][6037444]["data"]["online"])) {
      $dataOnline = $dayStatictic["_embedded"]["items"][6037444]["data"]["online"];
      foreach($dataOnline as $data) {
        sendDurationInDB($db, $date, ["current" => $data["current"], "user_id" => $user["id"]]);
      }
    }
}
*/
function sendDurationInDB($db, $date, $data) {
  $duration = (($data["current"]["days"] * 24 + $data["current"]["hours"]) * 60 + $data["current"]["minutes"]) * 60 + $data["current"]["seconds"];
  $d = date('Y-m-d',strtotime($date));
  if(!isDurationSetInDB($db, $d, $data)) {
    $db->query("INSERT INTO `duration_user_online` 
    SET `date`='".$d."',
        `user_id`=".(int)$data["user_id"].",
        `duration`=".$duration);
    return $db->lastInsertId();
  } else {
		$sql = "UPDATE `duration_user_online` 
						SET `duration`='".$duration."'
						WHERE `date` = '".$d."' and `user_id` = ".(int)$data["user_id"];
		$stmt = $db->prepare($sql);
		$stmt->execute();
	}
}

function isDurationSetInDB($db, $date, $data) {
  $stmt = $db->query("SELECT * 
                      FROM `duration_user_online`
                      WHERE `date` = '".$date."' and `user_id` = ".(int)$data["user_id"]);
  $doubles = $stmt->fetchAll();
  if(count($doubles)) {
    return true;
  } else {
    return false;
  }
}

function getDurationFromCRM($date, $user_id) {
  $requestData = ["id[]" => 6037444,
                  "period" => "custom",
                  "pipeline_id" => 1736272,
                  "main_user_id[]" => (int)$user_id,
                  "date_from" => $date,
                  "date_to" => $date];
  return sendRequestToAmo("GET", "/api/v1/dashboard/widgets/data/", $requestData);
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

}
*/
function getUsersIdFromDB($db) {
  $stmt = $db->query('SELECT `id` from `users` WHERE `group_id` = 0');
  return $stmt->fetchAll();
}

?>