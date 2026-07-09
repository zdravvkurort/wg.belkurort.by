<?php
require_once "../functions.php";
require_once "../../../functions.php";
require_once "../tourservices/tourservices.php";
cors();

if($_SERVER['REQUEST_METHOD'] !== 'POST') exit;
$post = file_get_contents('php://input');
$post = json_decode($post, true);
if(isset($post["token"]) and $post["token"] !== "NFMDrXXDOncf9zfGsNXsfiIY") exit;

$orderNum = (isset($post["orderNum"]) and !!$post['orderNum']) ? (string)quotemeta($post['orderNum']) : exit;
$foundation = (isset($post['foundation']) and !!$post['foundation']) ? (int)quotemeta($post['foundation']) : exit;
$currencyId = (isset($post['currencyId']) and !!$post['currencyId']) ? (string)quotemeta($post['currencyId']) : exit;
$typeRoom = (isset($post['typeRoom']) and !!$post['typeRoom']) ? (int)quotemeta($post['typeRoom']) : exit;
$guestsCode = (isset($post['code']) and !!$post['code']) ? (string)quotemeta($post['code']) : exit;
$countNight = (isset($post['countNight']) and !!$post['countNight']) ? (int)quotemeta($post['countNight']) : exit;
$checkIn = (isset($post['checkIn']) and !!$post['checkIn']) ? (int)strtotime($post['checkIn']) : exit;
$guests = array_sum(str_split($guestsCode));

// $orderNum = "1";
// $foundation = 450649;
// $currencyId = "BYN";
// $typeRoom = 109;
// $guestsCode = "21";
// $countNight = 12;
// $checkIn = strtotime("2021-10-10");
// $guests = array_sum(str_split($guestsCode));

$TEST = 0; // Тестовый аккаунт
$WSBCODE = !!$TEST ? 'qPVbfEUJTV35Z#dIIXvz}oR}Q%DQnRF~' : 'snbE~P@KAf8Vy8BE~4bBZzwuNrLgEsrQ'; // Токен из личного кабинета
$STOREID = !!$TEST ? 474694843 : 589588593; // Номер магазина
$VERSION = 2; // Версия API
$return_url = 'https://zdravkurort.by/success_pay?countGuests='.$guests;
$errorURL = 'https://zdravkurort.by/error_pay';

require_once "../prices/getCostSan.php";
$params = [
  "foundation" => $foundation, 
  "typeRoom" => $typeRoom,
  "checkIn" => $checkIn,
  "countNight" => $countNight,
  "valuta" => $currencyId, 
  "code" => $guestsCode,
];

$sanPrice = getCostSanAndDiscount($params);

$invoice = [
  [
    "name" => "Санаторно-курортная путёвка в санаторий Веста с ".date("d.m.Y", $checkIn)." на $countNight ночей для ".$sanPrice['foundationPriceName']." в номер категории 'Двухместный однокомнатный Twin/Double (2 корпус)'",
    "quantity" => 1,
    "price" => $sanPrice["foundationPrice"]
  ],
  [
    "name" => "Туробслуживание",
    "quantity" => 1,
    "price" => floor($tourservices[$currencyId] / 2)
  ],
  [
    "name" => "Инфоуслуги",
    "quantity" => 1,
    "price" => floor($tourservices[$currencyId] / 2)
  ],
];

list($wsb_invoice_item_name, $wsb_invoice_item_quantity, $wsb_invoice_item_price, $wsb_total) = getInvoice($invoice);
$wsb_total = (float)$wsb_total - (int)$sanPrice["discount"];

$wsb_seed = (string)randomString(64);
$wsb_order_num = $orderNum;
$wsb_currency_id = $currencyId;

$sha1 = sha1($wsb_seed.$STOREID.$wsb_order_num.$TEST.$wsb_currency_id.$wsb_total.$WSBCODE);

$sendedInfo = Array(
  'wsb_storeid' => (int)$STOREID,
  'wsb_order_num' => (string)$wsb_order_num,
  'wsb_currency_id' => (string)$wsb_currency_id,
  'wsb_version' => (int)$VERSION,
  'wsb_seed' => (string)$wsb_seed,
  'wsb_test' => (int)$TEST,
  'wsb_invoice_item_name' => $wsb_invoice_item_name,
  'wsb_invoice_item_quantity' => $wsb_invoice_item_quantity,
  'wsb_invoice_item_price' => $wsb_invoice_item_price,
  'wsb_total' => (float)$wsb_total,
  'wsb_signature' => (string)$sha1,
  'wsb_order_tag' => 'online',
  'wsb_return_url' => $return_url,
  'wsb_cancel_return_url' => $errorURL
);
if(isset($sanPrice["discountName"]) and $sanPrice["discountName"]) $sendedInfo['wsb_discount_name'] = $sanPrice["discountName"];
if(isset($sanPrice["discount"]) and $sanPrice["discount"]) $sendedInfo['wsb_discount_price'] = $sanPrice["discount"];

if(isset($post['name'])) $sendedInfo['wsb_customer_name'] = (string)$post['name'];
if(isset($post['phone'])) $sendedInfo['wsb_phone'] = (string)$post['phone'];
if(isset($post['email'])) $sendedInfo['wsb_email'] = (string)$post['email'];

$link = getLink((object)$sendedInfo);

if(isset($link['error'])) {
  echo json_encode(['error' => $link['error']]);
} else {
  echo json_encode($link["data"]);
}

function getLink($data) {
  global $TEST;
  $url = ($TEST) ? 'securesandbox' : 'payment';
  $data_string = json_encode($data);
  $ch = curl_init('https://'.$url.'.webpay.by/api/v1/payment');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data_string))
  );

  $result = curl_exec($ch);
  return json_decode($result, true);
}

function getInvoice($invoice) {
  $names = [];
  $quantities = [];
  $prices = [];
  $price = 0;
  foreach($invoice as $i) {
    if(!!$i['name'] and !!$i['quantity'] and !!$i['price']) {
      array_push($names, (string)$i['name']);
      array_push($quantities, (int)$i['quantity']);
      array_push($prices, (float)$i['price']);
      $price = (float)$price + ((float)$i['price'] * (int)$i['quantity']);
    }
  }
  return [$names, $quantities, $prices, $price];
}

function randomString($length = 8){
  $chars = 'qwertyuiop[]asdfghjkl;zxcvbnm,./QWERTYUIOPASDFGHJKLZXCVBNM1234567890';
  $numChars = strlen($chars);
  $string = '';
  for ($i = 0; $i < $length; $i++) {
   $string .= substr($chars, rand(1, $numChars) - 1, 1);
  }
  return $string;
}
?>