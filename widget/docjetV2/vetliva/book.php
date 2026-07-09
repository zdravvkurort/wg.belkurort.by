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
if(!$entityBody["bookingNumber"] or !$entityBody["leadId"] or !$entityBody["foundationId"]) {
  throwError('Некорректно задан номер бронирования, номер сделки или санаторий');
}

$bookId = addslashes(str_replace("\"","'", $entityBody["bookingNumber"]));
$leadId = addslashes(str_replace("\"","'", $entityBody["leadId"]));
$foundationId = addslashes(str_replace("\"","'", $entityBody["foundationId"]));

// Проверяем, есть ли активные бронирования
$stmt = $db->query('SELECT * FROM books WHERE lead_id = "'.$leadId.'" AND cancellation = 0');
$activeBooks = $stmt->fetchAll();

if(count($activeBooks) > 0) {
  throwError('По этой сделке уже есть активные бронирования. Аннулируйте предыдущую бронь, на сайте Vetliva.by, чтобы создать новое бронирование.');
}

// Добавляем в сделку ссылку на бронирование
require_once("../../../auth.php");
$leadAmo = $amo->lead;
$leadAmo->addCustomField(305351, "https://vetliva.ru/agent/private-office/order-list/detail.php?order_id=".$bookId);
$leadAmo->apiUpdate((int)$leadId, 'now');

// Добавляем примечание
create_note((int)$leadId, 'Создано бронирование №'.$bookId.' на сайте Vetliva.by');

// Записываем новое бронирование в БД
$stmt = $db->prepare("INSERT INTO books (lead_id, foundation, type) VALUES (?, ?, ?)");
$stmt->execute([$leadId, $foundationId, 'vetliva']);

// Отображаем ответ сервера
$result['error'] = false;
$result['message'] = 'Ваше бронирование создано. Теперь Вы можете перейти к генерации договора.';
echo json_encode($result);

function throwError($text) {
  $result['error'] = true;
  $result['message'] = $text;
  echo json_encode($result);
  exit;
}