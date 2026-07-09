<?
// Если меняется клиентский менеджер в сделке, то меняем ответственного по задачам со старого на нового

$lockfile = fopen("block.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

require_once(__DIR__ . '/../../auth.php');

require_once(__DIR__ . '/../../db_login.php');

require_once(__DIR__ . '/../../functions.php');


$tsf = 'ts.txt';
$ts = file_get_contents($tsf);

$notes = getNotesFromDB($db, $ts);
foreach($notes as $note) {
	if(($note["type"] == "custom_field_371365_value_changed")) {
		if(count($note["value_before"]) and count($note["value_after"])) {
			$responsibleClientManBefore = (int)$note["value_before"][0]["custom_field_value"]["text"];
			$responsibleClientManAfter = (int)$note["value_after"][0]["custom_field_value"]["text"];
			$tasks = sendRequestToAmo("GET", "/api/v4/tasks?filter[entity_id]=".$note["entity_id"]."&filter[responsible_user_id]=".$responsibleClientManBefore."&limit=250");
			$tasks = $tasks["_embedded"]["tasks"];
			
			if(count($tasks)) {
				$optionsArray = array_map(function($el) {
					global $responsibleClientManAfter;
					return (object)["id" => $el["id"], "responsible_user_id" => $responsibleClientManAfter];
				}, $tasks);
	
				if(count($optionsArray)) {
					sendRequestToAmo("PATCH", "/api/v4/tasks", $optionsArray);
				}
			}
		}
	}

	if($note["created_at"] > $ts) {
		$ts = $note["created_at"];
	}
}
file_put_contents($tsf, $ts+1);

function getNotesFromDB($db, $timestamp) {
  $timestamp = (int)$timestamp;
  $stmt = $db->query("SELECT * FROM `notes_all` WHERE `created_at` >= ".$timestamp." and `type` = 'custom_field_371365_value_changed' ORDER BY `created_at` DESC");
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