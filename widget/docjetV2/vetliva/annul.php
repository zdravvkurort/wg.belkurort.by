<?php 
require "../../../functions.php";
require "../../../db_login.php";
cors();

// Если нет авторизации - выбрасываем ошибку
if(!isset($_POST) or getallheaders()['Authorization'] !== 'nYK4dxa{bFQoQEEq%AibWTrW') {
  throwError('Неверный ключ или тип запроса');
}

$entityBody = json_decode(file_get_contents('php://input'), true);
// Если не заполнены поля - выбрасываем ошибку
if(!$entityBody["leadId"]) {
  throwError('Некорректно задан номер сделки');
}

$leadId = addslashes(str_replace("\"","'", $entityBody["leadId"]));

$stmt = $db->prepare("UPDATE books SET cancellation = 1 WHERE lead_id = ? and cancellation = 0 and type = 'vetliva'");
$stmt->execute([$leadId]);

$result['error'] = false;
$result['message'] = 'Успешно!';

echo json_encode($result);


function throwError($text) {
  $result['error'] = true;
  $result['message'] = $text;
  echo json_encode($result);
  exit;
}