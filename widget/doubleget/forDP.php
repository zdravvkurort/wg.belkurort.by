<?php 
sleep(2); // ожидаем 5 секунд пока все данные в CRM сохранятся
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки

//подключаем amo
require "../../auth.php";

//подключаем БД
//require "../db_login.php";

//получаем уникальные номера телефонов
$phones = [];
if(isset($_REQUEST['contacts']['add'][0]["custom_fields"])) {
foreach($_REQUEST['contacts']['add'][0]["custom_fields"] as $field) {
	if($field["name"] == "Телефон") {
		foreach($field['values'] as $phone) {
				$val = str_replace(' ', '', $phone["value"]);
				if(strlen($val) > 7) {
				array_push($phones, 
					mb_strimwidth($val, strlen($val) < 10 ? 0 : strlen($val) - 10, strlen($val))
				);
			}
		}
	}
}

if(count($phones) == 0) {
	exit;
}

$phones = array_unique($phones);

$contactsArray = [];
$idArray = [];
$dateCreactArr = [];
$phoneArr = [];
$emailArr = [];
$leadsId = [];

$query = "";

//Получаем список по контактам с полученными номерами телефонов
foreach($phones as $phone) {
	$val = $amo->contact->apiList([
        'query' => $phone,
    ]);
	foreach($val as $value) {
		array_push($contactsArray,$value);
		array_push($idArray,$value["id"]);
		array_push($dateCreactArr,$value["date_create"]);
		
		if($value["custom_fields"]) {
			foreach($value["custom_fields"] as $customFields) {
				if($customFields["code"] == 'EMAIL') {
					foreach($customFields["values"] as $val) {
						array_push($emailArr,$val["value"]);
					}					
				} else if ($customFields["code"] == "PHONE") {
					foreach($customFields["values"] as $val) {
						array_push($phoneArr,$val["value"]);
					}							
				} else {}

			}
		}
		
		if($value["linked_leads_id"]) {
			foreach($value["linked_leads_id"] as $Id) {
				array_push($leadsId,$Id);
			}
		}
		
	}
sleep(0.5);
}

if(count($contactsArray) > 0) {

	//узнаём имя самого старого контакта
	$nameVal = oldPar("name");
	$respVal = oldPar("responsible_user_id");

	//уникализируем массивы
	$idArray = array_unique($idArray);
	$phoneArr = array_unique($phoneArr);
	$emailArr = array_unique($emailArr);
	$leadsId = array_unique($leadsId);
	vardump($idArray);

	//Формируем запрос для объединения дублей
	$query = "";

	addAttr($idArray,'&id%5B%5D=');
	$query .= '&result_element%5BNAME%5D='.$nameVal;
	$query .= '&result_element%5BMAIN_USER_ID%5D='.$respVal;

	foreach($emailArr as $email) {
			$query .= '&result_element%5Bcfv%5D%5B138553%5D%5B%5D=%7B%22DESCRIPTION%22%3A%22WORK%22%2C%22VALUE%22%3A%22'.urlencode($email).'%22%7D';
		}
	foreach($phoneArr as $phone) {
			$query .= '&result_element%5Bcfv%5D%5B138551%5D%5B%5D=%7B%22DESCRIPTION%22%3A%22WORK%22%2C%22VALUE%22%3A%22'.urlencode($phone).'%22%7D';
		}
	addAttr($leadsId,'&result_element%5BLEADS%5D%5B%5D=');
	mb_internal_encoding("UTF-8");
	$query = mb_substr($query,1);
	vardump($query);

		//подключаемся к внутреннему api amocrm		
		define ( 'ROOT_DIR', dirname ( __FILE__ ) );
		define ( 'LOGIN', 'sanatoriym.crm@gmail.com' );
		define ( 'PASSWORD', 'VCS3VV' );
		define ( 'DOMAIN', 'sanatorium' );
		define ( 'API', '89ab84581a26a42867b3920b960faad1c66225bf' );

		require_once (ROOT_DIR . '/curl.class.php');
		require_once (ROOT_DIR . '/amocrm.class.php');

		$amocrm = new amocrm(LOGIN, PASSWORD, DOMAIN);
		//генерируем запрос на объединение контактов
				// если контактов больше 5 - разбиваем массив на несколько массивов
		$amocrm->MergeDublicate($query);
}
}

function addAttr($arr,$attr) {
	global $query;
	foreach($arr as $a) {
		$query .= $attr.$a;
	}
}

function vardump($var) {
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
}

function oldPar($param) {
	global $contactsArray;
	global $dateCreactArr;
$minDateCreate = min($dateCreactArr);
foreach($contactsArray as $contact) {
	if($contact["date_create"] == $minDateCreate) {
		return $contact[$param];
	};
}
}
?>