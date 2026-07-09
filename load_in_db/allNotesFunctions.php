<?
function getIncommingChatMessagesNotes($toTs) {
	$notes = [];
	$page = 1;
	$limit = 100;
	do {
		$options = [
					"filter" => [
								"created_at" => (int)$toTs,
								"type" => 'incoming_chat_message'
								/*[
                  "from" => (int)$toTs, 
                  "to" => (int)$toTs+12*60*60
                ]*/
					],
					"limit" => $limit,
					"page" => $page
		];
    $n = sendRequestToAmo('GET','/api/v4/events', $options);
		$n = (isset($n["_embedded"]["events"]) and count($n["_embedded"]["events"]) > 0) ? $n["_embedded"]["events"] : [];
		loadNotesInDB($n);
    $notes = array_merge($notes, $n);
		$page++;
	} while (count($n)-1 == $limit or count($n) == $limit);
	return $notes;
}

function getNotes($toTs) {
	$notes = [];
	$page = 1;
	$limit = 100;
	do {
		$options = [
					"filter" => [
								"created_at" => (int)$toTs
								/*[
                  "from" => (int)$toTs, 
                  "to" => (int)$toTs+12*60*60
                ]*/
					],
					"limit" => $limit,
					"page" => $page
		];
    $n = sendRequestToAmo('GET','/api/v4/events', $options);
		$n = (isset($n["_embedded"]["events"]) and count($n["_embedded"]["events"]) > 0) ? $n["_embedded"]["events"] : [];
		loadNotesInDB($n);
    $notes = array_merge($notes, $n);
		$page++;
	} while (count($n)-1 == $limit or count($n) == $limit);
	return $notes;
}

function loadNotesInDB($notes) {
  global $db;
  foreach($notes as $note) {
		$note['value_after'] = json_encode($note['value_after']);
		$note['value_before'] = json_encode($note["value_before"]);
		$note['_links'] = json_encode($note["_links"]);
		$note['_embedded'] = json_encode($note["_embedded"]);

		$stmt = $db->prepare('SELECT * FROM `notes_all` WHERE id=:id');
		$stmt->execute(['id' => $note["id"]]);
		$findedNote = $stmt->fetchAll();

		if(count($findedNote)) {
			$stmt = $db->prepare("UPDATE notes_all
														SET type = :type,
																entity_id = :entity_id,
																entity_type= :entity_type,
																created_by = :created_by,
																created_at = :created_at,
																value_after = :value_after,
																value_before = :value_before,
																account_id = :account_id,
																_links = :_links,
																_embedded = :_embedded
														WHERE id = :id");
			$stmt->execute($note);
		} else {
			$stmt = $db->prepare("INSERT INTO `notes_all` 
			SET id = :id,
					type = :type,
					entity_id = :entity_id,
					entity_type= :entity_type,
					created_by = :created_by,
					created_at = :created_at,
					value_after = :value_after,
					value_before = :value_before,
					account_id = :account_id,
					_links = :_links,
					_embedded = :_embedded");
			$stmt->execute($note);
		 }
  }

}