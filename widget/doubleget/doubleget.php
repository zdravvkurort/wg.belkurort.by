<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); //показываем все ошибки

$lockfile = fopen("leads.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

//подключаем amo
require_once "../../auth.php";

		//подключаемся к внутреннему api amocrm		
		define ( 'ROOT_DIR', dirname ( __FILE__ ) );
		define ( 'LOGIN', $login );
		define ( 'PASSWORD', $pas );
		define ( 'DOMAIN', 'zdravkyrort' );
		define ( 'API', $userhash );

		require_once (ROOT_DIR . '/curl.class.php');
		require_once (ROOT_DIR . '/amocrm.class.php');

//открываем сохранённый файл с timestamp последнего обновления
$tsf = 'ts.txt';
$ts = file_get_contents($tsf);
$tsarr = [];

//получаем изменённые контакты из amoCRM
$allcontacts = $amo->contact->apiList(['limit_rows' => 500], date('Y/m/d H:i',$ts));

//если контактов больше чем 500 - добавляем их в основной массив
if(count($allcontacts) >= 500) {
	$countnewarr = 500;
	$offset = 500;
	while($countnewarr != 0) {
		$newarr = $amo->contact->apiList(['limit_rows' => 500,'limit_offset' => $offset,], date('Y/m/d H:i',$ts));
		$countnewarr = count($newarr);
		$allcontacts = array_merge($allcontacts, $newarr);
		$offset = count($allcontacts);
		sleep(1);
	}
}
//vardump($allcontacts);

//проверяем каждый новый контакт на дубли и объединяем дубли
foreach($allcontacts as $b) {
	setRBPhoneAlias($b);
	if($b['date_create'] >= $ts) {
		//vardump($b["custom_fields"]);
		//генерируем запрос на объединение контактов
		$query = mergeDoubleContact($b["custom_fields"]);
		//vardump($query);
		//отправляем запрос на объединение дублирующих контактов
		$amocrm = new amocrm(LOGIN, PASSWORD, DOMAIN);
		$request = $amocrm->MergeDublicate($query);
		//vardump($request);
	sleep(0.2);
	}
	array_push($tsarr,$b["last_modified"]);
}

//записываем в файл дату последнего обновления последнего контакта
if(count($tsarr)>1) {
$tsn = max($tsarr);
} else {
$tsn = $ts;
}
if($tsn != 0) {
	file_put_contents($tsf, $tsn);
}

//функция, которая генерирует query для объединения контактов
function mergeDoubleContact($arr) {

//подключаем логины и пароли amoCRM
	global $amo;
	global $login;
	global $userhash;
	global $subdomain;

if(isset($arr)) {
	$phones = [];
	foreach($arr as $field) {
		if($field["name"] == "Телефон") {
			foreach($field['values'] as $phone) {
					$val = str_replace(' ', '', $phone["value"]);
					$val = preg_replace("/[^0-9]/", '', $val);
					if(strlen($val) >= 9) {
					array_push($phones, 
						mb_strimwidth($val, strlen($val) < 9 ? 0 : strlen($val) - 9, strlen($val))
					);
				}
			}
		}
	}

	if(count($phones) == 0) {
		return;
	}

	$phones = array_unique($phones);
	
	$contactsArray = [];
	$idArray = [];
	$dateCreactArr = [];
	$phoneArr = [];
	$emailArr = [];
	$leadsId = [];
	$otherFields = [];
	
	$query = "";
//Получаем список по контактам с полученными номерами телефонов
foreach($phones as $phone) {

	$val = $amo->contact->apiList([
        'query' => $phone,
		'limit_rows' => 19,
    ]);
	
	foreach($val as $value) {
		array_push($contactsArray,$value);
		array_push($idArray,$value["id"]);
		array_push($dateCreactArr,$value["date_create"]);
		
		if($value["custom_fields"]) {
			foreach($value["custom_fields"] as $customFields) {
				if(isset($customFields["code"]) and $customFields["code"] == 'EMAIL') {
					foreach($customFields["values"] as $val) {
						array_push($emailArr,$val["value"]);
					}					
				} else if (isset($customFields["code"]) and $customFields["code"] == "PHONE") {
					foreach($customFields["values"] as $val) {
						array_push($phoneArr,$val["value"]);
					}							
				} else {
					foreach($customFields["values"] as $val) {
						if(isset($otherFields[$customFields["id"]])) {
							array_push($otherFields[$customFields["id"]],$val["value"]);
						} else {
							$otherFields[$customFields["id"]] = [$val["value"]];
						}
					}	
				}
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
	
	
	
	if(count($contactsArray) > 1) {

		//узнаём имя самого старого контакта
		$nameVal = oldPar("name",$contactsArray,$dateCreactArr);
		$respVal = oldPar("responsible_user_id",$contactsArray,$dateCreactArr);
		//уникализируем массивы
		$idArray = array_unique($idArray);
		$phoneArr = array_unique($phoneArr);
		$emailArr = array_unique($emailArr);
		$leadsId = array_unique($leadsId);

		//Формируем запрос для объединения дублей
		$query = "";

		$query .= addAttr($idArray,'&id%5B%5D=',$query);
		
		$query .= '&result_element%5BNAME%5D='.urlencode($nameVal);
		$query .= '&result_element%5BMAIN_USER_ID%5D='.$respVal;

		foreach($emailArr as $email) {
				$query .= '&result_element%5Bcfv%5D%5B183783%5D%5B%5D=%7B%22DESCRIPTION%22%3A%22WORK%22%2C%22VALUE%22%3A%22'.urlencode($email).'%22%7D';
			}
		foreach($phoneArr as $phone) {
				$query .= '&result_element%5Bcfv%5D%5B183781%5D%5B%5D=%7B%22DESCRIPTION%22%3A%22WORK%22%2C%22VALUE%22%3A%22'.urlencode($phone).'%22%7D';
			}
		foreach($otherFields as $k => $v) {
				$query .= '&result_element%5Bcfv%5D%5B'.$k.'%5D='.urlencode($v[0]);
			}		
		$query .= addAttr($leadsId,'&result_element%5BLEADS%5D%5B%5D=',$query);
		mb_internal_encoding("UTF-8");
		$query = mb_substr($query,1);
		return $query;		
	}
	}
}

function addAttr($arr,$attr,$query) {
	foreach($arr as $a) {
		$query .= $attr.$a;
	}
	return $query;
}

function vardump($var) {
  echo '<pre>';
  var_dump($var);
  echo '</pre>';
}

function oldPar($param,$contactsArray,$dateCreactArr) {
	if(count($dateCreactArr)>1) {
		$minDateCreate = min($dateCreactArr);
			foreach($contactsArray as $contact) {
				if($contact["date_create"] == $minDateCreate) {
					return $contact[$param];
				};
			}		
	} else {
		return $contactsArray[$param];
	};
}

function setRBPhoneAlias($contact) {
	global $amo;
	$update = false;
	$cfArray = $contact["custom_fields"];
	$stringForUniqueCheck = '';
	$phonesArray = [];
	$newNumbers = [];
	foreach($cfArray as $cf) {
		if(isset($cf["code"]) and $cf["code"] == "PHONE") {
			foreach($cf["values"] as $phoneArr) {
				$phone = $phoneArr["value"];
				array_push($phonesArray,[$phone, 'WORK']);
				$phone = preg_replace("/[^,.0-9]/", '', $phoneArr["value"]);
				preg_match("/^375\d{9}|^80\d{9}/", $phone, $byNumbers);
				foreach($byNumbers as $num) {
					$stringForUniqueCheck .= $num.', ';
					$num = (substr($num, 0, 3) === '375') ? "80".substr($num, -9) : "375".substr($num, -9);
					array_push($newNumbers,$num);
				}
			}
		}
	}
	
	// Проверяем есть ли новые номера в контакте
	foreach($newNumbers as $n) {
		if(strpos($stringForUniqueCheck, $n) === false) {
			$update = true;
			$n = (substr($n, 0, 3) === '375') ? "+".$n : $n;
			array_push($phonesArray, [$n, 'WORK']);
			$stringForUniqueCheck .= $n.", ";
		}
	}
	
	$phonesArray = array_map('unserialize', array_unique(array_map('serialize', $phonesArray)));
	
	//Если есть, добавляем
	if($update) {
			print_r("--------------------------------------------------------------");
			vardump($contact["id"]);
			vardump($phonesArray);

			$cont = $amo->contact;
			$cont->addCustomField((int)'183781', $phonesArray);
			$cont->apiUpdate((int)$contact["id"], 'now');
			sleep(0.3);
	}

}
?>