<?php

$json = file_get_contents('php://input');
file_put_contents('notes.json', $json);
$obj = json_decode($json, true);

switch ($obj['type']) {
//Если это уведомление для подтверждения адреса...
case 'confirmation':
//...отправляем строку для подтверждения
echo "d5d2360b";
break;

case 'lead_forms_new':
if($obj['secret'] == "5k399zk8guc3oz6riyugupk2") {

	// $json = json_encode($obj);
	// file_put_contents('text.txt', $json);

require_once "../../auth.php";
require "amo_functions.php";

$roistatVisitId = null;
$gaid = null;

$postform = "Вконтакте";
$postcomment = "";
$utm_campaign = "";
$utm_source = "";
$utm_medium = "";
$utm_content = "";
$utm_term = "";
$email = "";

$name = "Новый контакт из Вконтакте";
$phone = "";

$postcomment .= "
Название формы: ".$obj['object']['form_name']."
";

foreach($obj['object']['answers'] as $item) {
	if($item['key'] == "first_name") {
		$name = $item['answer'];
	}
	else if($item['key'] == "phone_number") {
		$phone = normalize_phone($item['answer']);
	} else {
		$postcomment .= $item['question']." ".$item['answer']."\n";
	}
}

$utm_data = array(
	'utm_campaign'=>$utm_campaign,
	'utm_content'=>$utm_content,
	'utm_medium'=>$utm_medium,
	'utm_source'=>$utm_source,
	'utm_term'=>$utm_term,
);

//lg([$phone, $email]);

$pipeline_id = 1736272;
list ($contact_exists, $contact_id, $responsible_id) = is_contact_exists($phone, $email);
/*if($contact_exists) lg('contact exists');
else lg('contact dont exist');*/

//lg([$contact_exists, $contact_id, $responsible_id]);

if(!$contact_exists){
	$responsible_id = 12485533;
	$contact_id = create_contact($name, $phone, $email, $responsible_id, $utm_data, "https://vk.ru/id".$obj['object']['user_id']);
	$lead_exists = false;
//	lg('Created contact with id'.$contact_id);
}
else{ //contact exists
	list($lead_exists, $lead_id) = is_lead_exists($contact_id, $pipeline_id);
}


if(!$lead_exists){
		$lead_id = create_lead($phone, $responsible_id, $pipeline_id, $utm_data, $postform, $roistatVisitId, $gaid);
//		lg('lead does not exist, create with lead id'.$lead_id);
		connect_lead_to_contact($lead_id, $contact_id);
} else {
//		create_task($lead_id, $responsible_id);
}


$text = "Оставлен новый лид: Vkontakte.ru пользователем https://vk.ru/id".$obj['object']['user_id']."\nИмя: $name\nТелефон: $phone\nКомментарий: $postcomment";
create_note($lead_id, $text);
if($responsible_id != 3406348) {
	create_task($lead_id, $responsible_id);
}
echo("ok");
}
break;

}
?>
