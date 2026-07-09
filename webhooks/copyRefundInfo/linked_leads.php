<?php

// require "../../auth.php";
// require "../../db_login.php";
// require "../../functions.php";

// Если сделка связанная, и по ней есть изменения - проводим эти изменение в связанных сделках

//отслеживаемые поля
$billFields = [305355, 378075, 398360, 398362, 371365, 371365];
$returnFields = [378479, 377103, 371141, 370933, 370935, 393708, 381911, 381913, 398358, 398364, 398366, 398368, 398370, 398678];
$copiedFields = array_merge($billFields, $returnFields); 

//открываем сохранённый файл с timestamp последнего обновления
$tsf = 'ts.txt';
$ts = file_get_contents($tsf);

$notes = getNotesFromDB2($db, $ts, $copiedFields);
if(!count($notes)) exit;

$notes = uniqueNotes($notes);

$leadIds = array_map(function($note) {
	return $note["entity_id"];
}, $notes);

$leadIds = array_unique($leadIds);

$leadChunks = array_chunk($leadIds, 50);
$leads = [];

foreach ($leadChunks as $chunk) {
	$nl = sendRequestToAmo("GET", '/api/v4/leads', ["filter" => ["id" => $chunk]]);
	$leads = array_merge($leads, $nl["_embedded"]["leads"]);
}

if(!count($leads)) exit;

$leads = addChangedFields($leads);

$options = [];
foreach($leads as $lead) {

	$fieldLinkedLead = findFieldById(398102, $lead);
	if(!$fieldLinkedLead) continue;

	$linkedLeadValue = ($fieldLinkedLead["values"][0]["value"]) ? json_decode($fieldLinkedLead["values"][0]["value"], true) : NULL;
	if(!count($lead["changedFields"]) or !$linkedLeadValue) continue;
	$customFieldsArray = [];
	$updatedAt = time();
	foreach($lead["changedFields"] as $field) {
		$isDelete = true;
		if(count($field["value_after"])) {
			$cfv = $field["value_after"][0]["custom_field_value"];
			$isDelete = false;
		} else {
			$cfv = $field["value_before"][0]["custom_field_value"];
		}

		if(!$cfv) continue;

		$val = [(object)["value" => $cfv["text"]]];

		if(isset($cfv["timestamp"])) $val = [(object)["value" => $cfv["timestamp"]]];
		if(isset($cfv["field_type"]) and $cfv["field_type"] == 3) $val = [(object)["value" => (bool)$cfv["text"]]];
		if($isDelete) $val = NULL;

		array_push($customFieldsArray, (object)["field_id" => $cfv["field_id"],
																						"values" => $val]);
		if($field["created_at"] < $updatedAt) {
			$updatedAt = $field["created_at"];
		}
	}
	array_push($options, (object)["id" => $linkedLeadValue[0], 
																"updated_at" => $updatedAt,
																"updated_by" => 0,
																"custom_fields_values" => $customFieldsArray]);
}

if(count($options)) {
	$optionChunks = array_chunk($options, 50);
	foreach($optionChunks as $optChunk) {
		sendRequestToAmo("PATCH", "/api/v4/leads", $optChunk);
	}
}

foreach($notes as $note) {
	if($note['created_at'] > $ts) {
		$ts = $note['created_at'];
	}
}

file_put_contents($tsf, $ts+1);

function uniqueNotes($notes) {
	$noDoublesNotes = [];
	foreach($notes as $currentNote) {
		if(isNoteExist($noDoublesNotes, $currentNote)) continue;
		$theSameNotes = array_filter($notes, function($note) use($currentNote) {
			return ($note["entity_id"] == $currentNote["entity_id"] and 
							$note["entity_type"] == $currentNote["entity_type"] and 
							$note["type"] == $currentNote["type"]);
		});
		usort($theSameNotes, function($a, $b) {
			return ((int)$a["created_at"] < (int)$b["created_at"]) ? 1 : -1;
		});
		array_push($noDoublesNotes, $theSameNotes[0]);
	}
	return $noDoublesNotes;
}

function isNoteExist($array, $note) {
	return count(array_filter($array, function($el) use($note) {
		return ($el["entity_id"] == $note["entity_id"] and $el["type"] == $note["type"]);
	}));
}

function addChangedFields($leads) {
	global $notes;
	foreach($leads as $leadKey => $leadValue) {
		$id = $leadValue['id'];
		$changedFieldsByLead = array_filter($notes, function($note) use($id) {
			return ($note['entity_id'] == $id and $note['entity_type'] == 'lead');
		});
		$resultFields = [];
		$leads[$leadKey]["changedFields"] = $changedFieldsByLead;
	}
	return $leads;
}

function findFieldById($id, $lead) {
	foreach($lead["custom_fields_values"] as $customField) {
		if($customField["field_id"] == $id) {
			return $customField;
		}
	}
}

function groupNotesByLeads($notes) {
	$resultArray = [];
	foreach ($notes as $note) {
		if($note["entity_type"] == "lead") {
			if(!isset($resultArray[$note["entity_id"]])) $resultArray[$note["entity_id"]] = [];
			array_push($resultArray[$note["entity_id"]], $note);
		}
	}
	return $resultArray;
}

function getNotesFromDB2($db, $timestamp, $copiedFields) {
  $timestamp = (int)$timestamp;
	$regexp = join($copiedFields, "|");
  $stmt = $db->query("SELECT * FROM `notes_all` WHERE `created_at` >= $timestamp AND `type` REGEXP '$regexp' AND `created_by` != 0 ORDER BY `created_at` DESC");
  $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($notes as $nk => $note) {
    foreach($note as $key => $value) {
      if($key == "value_after" or $key == "value_before" or $key == "_links" or $key == "_embedded") {
        $notes[$nk][$key] = json_decode($value, true);
      } else if($key == "entity_id" or $key == "created_by" or $key == "created_at" or $key == "account_id") {
        $notes[$nk][$key] = (int)$value;
      }
    }
  }
   return $notes;
}
?>