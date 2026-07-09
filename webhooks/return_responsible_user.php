<?php 
$lockfile = fopen("resp_user.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
die("");

//подключаем amo
require_once "../auth.php";
require "../functions.php";

//получаем timestamp
$ts = file_get_contents('resp_user_timestamp.txt');

//получаем контакты на админе
$contacts = [];
$limit = 0;
	do {
		$newArr = $amo->contact->apiList([	'limit_rows' => 500,
											'responsible_user_id' => 3406348,
											'limit_offset' => $limit], 
											date("Y-m-d H:i:s",$ts));
		$contacts = array_merge($contacts, $newArr);
	$limit += 500;
	sleep(0.5);
	} while (count($newArr) == 500);

//получаем сделки, которые связаны с полученными ранее контактами
$leads_ids = [];
$counter = 0;
foreach($contacts as $contact) {
	foreach($contact["linked_leads_id"] as $i) {
		$leads_ids['id['.$counter.']'] = $i;
		$counter += 1;
	}
	$ts = ($ts<=$contact["last_modified"]) ? $contact["last_modified"] : $ts;
}

$leads_ids = array_chunk($leads_ids, 500, true);
$leads = [];

foreach($leads_ids as $leadId) {
	$leads = array_merge($leads, $amo->lead->apiList($leadId));
}

//Если по лиду ответственный не Григорий - меняем ответственность по контату на соответствующего ответственного по лиду
foreach($leads as $l) {
	$contact_id = find_contact_id($l["id"]);
	if($contact_id != null) {
		if($l["responsible_user_id"] != 3406348) {
				$cont = $amo->contact;
				$cont['responsible_user_id'] = $l["responsible_user_id"];
				$cont->apiUpdate((int)$contact_id, 'now');
		} else {
				$cont = $amo->contact;
				$cont['responsible_user_id'] = 3449311;
				$cont->apiUpdate((int)$contact_id, 'now');
		}
	}
}

file_put_contents('resp_user_timestamp.txt', $ts);

function find_contact_id($leadId) {
	global $contacts;
	foreach($contacts as $contact) {
		foreach($contact["linked_leads_id"] as $c) {
			if($c == $leadId) {
				return $contact["id"];
			}
		}
	}
}
?>