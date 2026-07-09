<?
$tsf = 'tsactions.txt';
$ts = file_get_contents($tsf);

//$notes = getNotes($ts);
$notes = getNotesFromDB($db, $ts);

foreach($notes as $note) {
	if($note["type"] == "task_completed" and $note["entity_type"] == "task" and isset($note["entity_id"])) {
		$task = sendRequestToAmo("GET", "/api/v4/tasks/".$note["entity_id"]);
		if($task["task_type_id"] == 2085540 and $task["entity_type"] == "leads" and isset($task["entity_id"])) {
			$lead = sendRequestToAmo("GET", "/api/v4/leads/".$task["entity_id"]);
			if($lead["pipeline_id"] == 3836187 and $lead["status_id"] == 36911073) {
				$options = (object)["status_id" => 36911076, "updated_by" => 0];
				sendRequestToAmo("PATCH", "/api/v4/leads/".$task["entity_id"], $options);
				
				$responsibleClientsManager = findCustomValue(371365, $lead["custom_fields_values"]);
				$responsibleUserId = isset($responsibleClientsManager[0]["value"]) ? (int)$responsibleClientsManager[0]["value"] : 0;
				$options = array((object)["responsible_user_id" => $responsibleUserId, "created_by" => 3449320,"entity_id" => $lead["id"], "entity_type" => "leads", "task_type_id" => 1, "text" => "Запрос отправлен в санаторий", "complete_till" => time() + 5*60]);
				sendRequestToAmo("POST", "/api/v4/tasks", $options);
			}
		}
	} else if(($note["type"] == "custom_field_398366_value_changed" or $note["type"] == "custom_field_398368_value_changed") and $note["entity_type"] == "lead" and count($note["value_before"]) == 0) {
		$lead = sendRequestToAmo("GET", "/api/v4/leads/".$note["entity_id"]);
		if($lead["pipeline_id"] == 3836187) {
			$options = (object)["status_id" => 36911115, "updated_by" => 0];
			sendRequestToAmo("PATCH", "/api/v4/leads/".$note["entity_id"], $options);
			
			$responsibleClientsManager = findCustomValue(371365, $lead["custom_fields_values"]);
			$responsibleUserId = isset($responsibleClientsManager[0]["value"]) ? (int)$responsibleClientsManager[0]["value"] : 0;
			$options = array((object)["responsible_user_id" => $responsibleUserId, "created_by" => 3449320,"entity_id" => $lead["id"], "entity_type" => "leads", "task_type_id" => 1, "text" => "Санаторий вернул деньги, готовим возврат", "complete_till" => time() + 5*60]);
			sendRequestToAmo("POST", "/api/v4/tasks", $options);
		}
	} else if($note["type"] == "custom_field_370933_value_changed" and $note["entity_type"] == "lead" and count($note["value_before"]) == 0 and count($note["value_after"]) > 0) {
		$lead = sendRequestToAmo("GET", "/api/v4/leads/".$note["entity_id"]);
		if($lead["pipeline_id"] == 3836187) {
			$options = (object)["status_id" => 142, "updated_by" => 0];
			sendRequestToAmo("PATCH", "/api/v4/leads/".$note["entity_id"], $options);
		}
	}

	if($note["type"] == "lead_restored" and $note["entity_type"] == "lead" and isset($note["entity_id"])) {
	    require_once(__DIR__ . '/../../load_in_db/leads_upload_functions.php');
		$columns = normalizeLeadsTableStructure();
		$lead = getLeadById(20230356);
		addLeadsInTable([$lead]);
	}
	
	if($note["created_at"] > $ts) {
		$ts = $note["created_at"];
	}
}
file_put_contents($tsf, $ts+1);

function findCustomValue($fieldId, $customFieldsArray) {
	if(count($customFieldsArray) > 0) {
		foreach($customFieldsArray as $cf) {
			if($cf["field_id"] == $fieldId) {
				return $cf["values"];
			}
		}		
	}
}

function getNotesFromDB($db, $timestamp) {
  $timestamp = (int)$timestamp;
  $stmt = $db->query("SELECT * FROM `notes_all` WHERE `created_at` >= ".$timestamp." AND `type` IN ('task_completed', 'custom_field_398366_value_changed', 'custom_field_398368_value_changed', 'custom_field_370933_value_changed', 'lead_restored', ) ORDER BY `created_at` DESC");
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

function getNotes($ts) {
	$notes = [];
	$page = 1;
	$limit = 100;
	do {
		$options = [
					"filter" => [
								//"entity" => ["lead"],
								"created_at" => $ts
					],
					"limit" => $limit,
					"page" => $page
		];

		$n = sendRequestToAmo('GET','/api/v4/events', $options);
		$n = (isset($n["_embedded"]["events"]) and count($n["_embedded"]["events"]) > 0) ? $n["_embedded"]["events"] : [];
		$notes = array_merge($notes, $n);
		$page++;
	} while (count($n) == $limit);
	return $notes;
}

?>