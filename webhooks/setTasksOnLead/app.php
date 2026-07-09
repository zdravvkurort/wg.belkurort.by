<?

$lockfile = fopen("block.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");
// Если меняется клиентский менеджер в сделке, то меняем ответственного по задачам со старого на нового

require "../../auth.php";
require "../../db_login.php";
require "../../functions.php";

$tsf = 'ts.txt';
$ts = file_get_contents($tsf);

$notes = getNotesFromDB($db, $ts);
$tasks = [];
foreach($notes as $note) {

	// Если заполняется поле возврата, то ставим задачи оповещения о возврате
	if(($note["type"] == "custom_field_370933_value_changed")) {
		if(count($note["value_after"]) and $note["value_after"][0]["custom_field_value"]["text"] != 0 and count($note["value_before"]) == 0) {
			$lead = getLeadById($note["entity_id"]);
			if(count($lead) and $lead["pipeline_id"] == 1736272) {
				array_push($tasks, (object)["task_type_id" => 1,
																		"text" => "Сделали возврат на сумму ".$note["value_after"][0]["custom_field_value"]["text"]." рос. руб.",
																		"complete_till" => time(),
																		"entity_id" => (int)$lead['id'],
																		"entity_type" => "leads",
																		"responsible_user_id" => (int)$lead["responsible_user_id"]]
																		);

				if($lead['371365']) {
					array_push($tasks, (object)["task_type_id" => 1,
																			"text" => "Сделали возврат на сумму ".$note["value_after"][0]["custom_field_value"]["text"]." рос. руб.",
																			"complete_till" => time(),
																			"entity_id" => (int)$lead['id'],
																			"entity_type" => "leads",
																			"responsible_user_id" => (int)$lead['371365']]);
				}		
			}
		}
	}

	if($note["created_at"] > $ts) {
		$ts = $note["created_at"];
	}
}

if(count($tasks)) {
	sendRequestToAmo("POST", "/api/v4/tasks", $tasks);
}

file_put_contents($tsf, $ts+1);

function getLeadById($id) {
	global $db;
	$id = (int)$id;
	if($id) {
		$stmt = $db->query("SELECT * FROM `leads` WHERE id=".$id);
		$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $leads[0];
	}
	return [];
}

function getNotesFromDB($db, $timestamp) {
  $timestamp = (int)$timestamp;
  $stmt = $db->query("SELECT * FROM `notes_all` WHERE `created_at` >= ".$timestamp." ORDER BY `created_at` DESC");
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