<?php 

//подключаем amo
require_once "../auth.php";
require "../functions.php";

//получаем лиды в статусе договора
$allcontracts = $amo->lead->apiList(['status' => '26081356']);
$string = "";
$now = time();
$arr = ['type' => 'lead',
		'note_type' => 3];	


foreach($allcontracts as $k => $v) {
	if($v["status_id"] != 26081356) {
		unset($allcontracts[$k]);
	} else {
		$arr['element_id['.$k.']'] = $v['id'];
	}
};

$all_change_status = $amo->note->apiList($arr);

foreach($all_change_status as $note) {
	$text = json_decode($note["text"], true);
	if($text["STATUS_NEW"] == 26081356 and $note["date_create"] < ($now - (3*24*60*60))) {
		if(find_double_note($all_change_status, $note["element_id"])["id"] == $note["id"]) {
			    $lead = $amo->lead;
				$lead['status_id'] = '28291732';
				$lead->apiUpdate((int)$note["element_id"], 'now');
				sleep(0.5);
		}
	}
}

function find_double_note($change_status, $leadId) {
	$ids = [];
	foreach($change_status as $n) {
	$text = json_decode($n["text"], true);
		if($text["STATUS_NEW"] == 26081356 and $n["element_id"]==$leadId) {
			array_push($ids, $n);
		}
	}
	return array_pop($ids);
}
?>