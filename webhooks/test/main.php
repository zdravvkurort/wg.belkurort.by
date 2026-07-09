<?php 
//подключаем amoCRM
require_once "../../auth.php";
require "amo_functions.php";

$roistatVisitId = null;
$gaid = null;

// $json = '{
// 	"Sheetname": [
// 			{
// 					"Телефон": "+79094099444 ",
// 					"Пол": "-",
// 					"Вопрос": "Добрый день! Интересует стоимость за сутки в ноябре. С лечением и без, если можно.",
// 					"Дата": "2021-10-04 14:41:54",
// 					"Страница": "https://radon.zdravkurort.by/?utm_source=yandex&utm_medium=cpc&utm_campaign=cid|49285172|search&utm_content=gid|4094865291|aid|8582758491|19598722354_&utm_term=стоимость путевки в санаторий радон&pm_source=none&pm_block=premium&pm_position=1&yclid=12880850012932669439",
// 					"Cтатус лида": "Не обработан",
// 					"utm_content": "gid|4094865291|aid|8582758491|19598722354_",
// 					"utm_term": "стоимость путевки в санаторий радон",
// 					"utm_campaign": "cid|49285172|search",
// 					"utm_medium": "cpc",
// 					"utm_source": "yandex"
// 			}
// 	]
// }';

$json = json_decode($json, true);
$json = $json["Sheetname"];
foreach($json as $vlead) {

if(isset($vlead["Страница"])) { 
	$site = parse_url($vlead["Страница"]);
	$site = $site["host"].$site["path"];
	$site = ($site[strlen($site)-1] == "/") ? substr($site,0,-1) : $site;
} else { $site = "";}
if(isset($vlead["Вопрос"])) { $question = $vlead["Вопрос"];} else { $question = "";}
if(isset($vlead["Регион"])) { $geo_country = $vlead["Регион"];} else { $geo_country = "";}
if(isset($vlead["Регион"])) { $geo_city = $vlead["Регион"];} else { $geo_city = "";}
if(isset($vlead["Телефон"])) { $phone = normalize_phone($vlead["Телефон"]);} else { $phone = "";}
if(isset($vlead["Дата"])) { $datein = $vlead["Дата"];} else { $geo_city = "";}

$postcomment = $question." \nСтрана ".$geo_country." \nГород ".$geo_city;

$postfio = "Контакт с сайта ".$site;

	$utm_campaign = (isset($vlead['utm_campaign'])) ? $vlead['utm_campaign'] : "";
	$utm_source = (isset($vlead['utm_source'])) ? $vlead['utm_source'] : "";
	$utm_medium = (isset($vlead['utm_medium'])) ? $vlead['utm_medium'] : "";
	$utm_content = (isset($vlead['utm_content'])) ? $vlead['utm_content'] : "";
	$utm_term = (isset($vlead['utm_term'])) ? $vlead['utm_term'] : "";
	$clientid_ga = (isset($vlead['clientid_ga'])) ? $vlead['clientid_ga'] : "";

$referer = "";

if(isset($vlead["email"]) == false ){
	$email = "";
} else {
	$email = $vlead["email"];
	};
$name = $postfio;
 
$utm_data = array(
	'utm_campaign'=>$utm_campaign,
	'utm_content'=>$utm_content,
	'utm_medium'=>$utm_medium,
	'utm_source'=>$utm_source,
	'utm_term'=>$utm_term,
	'site' => $site,
	'clientid_ga' => $clientid_ga
);

//lg([$phone, $email]);

$pipeline_id = 1736272;
list ($contact_exists, $contact_id, $responsible_id) = is_contact_exists($phone, $email);
/*if($contact_exists) lg('contactt exists');
else lg('contactt dont exist');*/

//lg([$contact_exists, $contact_id, $responsible_id]);


if(!$contact_exists){
	$responsible_id = 3406348; //margo
	$contact_id = create_contact($name, $phone, $email, $responsible_id, $utm_data);
	$lead_exists = false;
//	lg('Created contact with id'.$contact_id);
}
else{ //contact exists
	list($lead_exists, $lead_id) = is_lead_exists($contact_id, $pipeline_id);
}

if(!$lead_exists){
		$lead_id = create_lead($phone, $responsible_id, $pipeline_id, $utm_data, $datein);
//		lg('lead does not exist, create with lead id'.$lead_id);
		connect_lead_to_contact($lead_id, $contact_id);
} else {
//		create_task($lead_id, $responsible_id);
}

$text = "Оставлен новый заказ на сайте: ".$site."\nИмя: $name\nТелефон: $phone\nКомментарий: $postcomment";
create_note($lead_id, $text);
create_task($lead_id, $responsible_id);
}
function getutm($key) {
	global $referer;
	$start = strpos($referer, $key);
	$ravno = strpos($referer, "=",$start)+1;
	$amp = strpos($referer, "&",$start);
		if($amp == false) {
			$amp = strlen($referer);
		}
	$countbody = $amp - $ravno;
	$exit = substr($referer, $ravno, $countbody);
	return $exit;
}
?>