<?php
// Если сделка связанная, и по ней есть изменения - проводим эти изменение в связанных сделках

//отслеживаемые поля
$billFields = [305355, 378075, 398360, 398362, 371365];
$returnFields = [378479, 377103, 371141, 370933, 370935, 393708, 381911, 381913, 398358, 398364, 398366, 398368, 398370, 398678];
$copiedFields = array_merge($billFields, $returnFields); 

//открываем сохранённый файл с timestamp последнего обновления
$tsf = 'ts.txt';
$ts = file_get_contents($tsf);

$limit = 0;
$limit_rows = 500;
$leads = [];

do {
	$nl = getLeadUpdates($amo, $limit_rows, $limit, $ts);
	$leads = array_merge($leads, $nl);
	$limit += 500;
} while (count($nl) == 500);

if(count($leads) > 0) {
	$ts = $leads[count($leads)-1]["last_modified"];
}

$leads = array_filter($leads, "checkLink"); //фильтруем только сделки со связями
$leads = array_values($leads);

$linksArray = getLinkedLeadArray($leads); //получаем id связанных сделок

try { //запрашиваем инфо по связанным сделкам
    $linkedLeads = $amo->lead->apiList(['id' => $linksArray]);
	sleep(0.3);
} catch (\AmoCRM\Exception $e) {
    printf('Error (%d): %s', $e->getCode(), $e->getMessage());
}

foreach($leads as $lead) { //Проходимся по изменённым лидам
	$linkedLeadsId = checkLink($lead); //Ищем связанные сделки
	foreach($linkedLeadsId as $id) {
		$linkedLead = findLead($id, $linkedLeads);

		$lead["custom_fields"] = array_values(array_filter($lead["custom_fields"], "filterFields"));
		$linkedLead["custom_fields"] = array_values(array_filter($linkedLead["custom_fields"], "filterFields"));
		$CFS = uniqueArray($lead["custom_fields"], $linkedLead["custom_fields"]);
		
		if(count($CFS)) {
			$amoLead = $amo->lead;
			foreach($CFS as $field) {
				$value = getValueByFieldId($field["id"], $CFS);
				$amoLead->addCustomField($field["id"], $value);
			}
			$amoLead->apiUpdate((int)$id, date("Y-m-d H:i:s", $lead["last_modified"]-1));
			sleep(1);
		}
	}
	if($lead["last_modified"] > $ts) {
		$ts = $lead["last_modified"];
	}
}
file_put_contents($tsf, $ts+1);

function getValueByFieldId($id, $fieldsArray) {
	foreach($fieldsArray as $field) {
		if($field['id'] == $id and isset($field['values']) and isset($field['values'][0])) {
			return isset($field['values'][0]["enum"]) ? $field['values'][0]["enum"] : $field['values'][0]["value"];
		}
	}
	return "";
}

function uniqueArray($fieldLead, $fieldLinkedLead) {
	$resultArray = $fieldLead;
	foreach($fieldLinkedLead as $key => $value) {
		$field = findFieldById($value['id'], $resultArray);
		if(!$field) {
			$push_field = $value;
			$push_field["values"] = [];
			array_push($resultArray, $push_field);
		} else if($field and serialize($field) == serialize($value)) {
			$resultArray = deleteFieldById($field['id'], $resultArray);
		}
	}
	return $resultArray;
}

function deleteFieldById($id, $fieldArray) {
	foreach($fieldArray as $k => $v) {
		if($v["id"] == $id) {
			unset($fieldArray[$k]);
		}
	}
	return $fieldArray;
}

function findFieldById($id, $fieldArray) {
	foreach($fieldArray as $field) {
		if($field['id'] == $id) {
			return $field;
		}
	}
}

function filterFields($field) {
	global $copiedFields;
	return in_array($field["id"], $copiedFields);
}

function findLead($id, $leadsArray) {
	foreach($leadsArray as $lead) {
		if($lead['id'] == $id) {
			return $lead;
		}
	}
	return false;
}

function getLinkedLeadArray($leads) {
	$resultArray = [];
	foreach($leads as $lead) {
		$linkedLeadArray = checkLink($lead);
		if($linkedLeadArray) {
			$resultArray = array_merge($resultArray, $linkedLeadArray);
		}
	}
	return $resultArray;
}

function checkLink($lead) {
		$link = findcustomfieldval($lead, 398102);
		$idArray = json_decode($link, true);
		if($link != NULL and count($idArray)) {
			return $idArray;
		} else {
			return false;
		}
}

function getLeadUpdates($amo, $limit_rows, $limit, $ts) {
	try {
		$leads = $amo->lead->apiList(['limit_rows' => $limit_rows, 'limit_offset' => $limit], date("Y-m-d H:i:s",$ts));
		sleep(0.3);
	} catch (\AmoCRM\Exception $e) {
		printf('Error (%d): %s', $e->getCode(), $e->getMessage());
	}
	return (is_array($leads)) ? $leads : [];
}

?>