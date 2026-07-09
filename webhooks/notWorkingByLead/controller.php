<?
$lockfile = fopen("lockfile.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");
if((date("w") >= 1 and date("w") <= 5 and (date("H") < 9 or date("H") >= 18)) or (date("w") == 6 and (date("H") < 10 or date("H") > 17)) or date("w") == 7)
  die("");

sleep(1);
require "../../auth.php";
require "../../functions.php";
require "../../db_login.php";

$stmt = $db->query('SELECT leads.`id`, leads.`name`, leads.`main_contact_id`, leads.date_create, calls_notes.`min_created_at` as "calls_note_created_time", chat_messages.`min_created_at` as "chat_event_created_time", call_events.`min_created_at` as "calls_events_created_time"
                    FROM `leads`
                    INNER JOIN users ON leads.responsible_user_id = users.id
                    LEFT JOIN (
                        SELECT `entity_id`, MIN(`created_at`) as "min_created_at"
                        FROM `notes_contacts`
                        WHERE `note_type` = "call_out"
                        GROUP BY `entity_id`)calls_notes ON calls_notes.`entity_id` = leads.main_contact_id
                    LEFT JOIN (	SELECT  entity_id, MIN(created_at) as `min_created_at`
                                FROM `notes_all`
                                where `type` = "outgoing_chat_message"
                                GROUP BY `entity_id`)chat_messages ON chat_messages.entity_id = leads.id
                    LEFT JOIN (	SELECT entity_id, MIN(created_at) as `min_created_at` 
                                FROM `notes_all` 
                                WHERE `type` = "outgoing_call"
                                GROUP BY `entity_id`)call_events on call_events.entity_id = leads.main_contact_id
                    WHERE users.group_id = 0 AND
                    leads.pipeline_id = 1736272 AND
                    (leads.status_id = 26081347 OR leads.status_id = 26081350 OR leads.status_id = 26081353) AND
                    leads.date_create >= 1609448400');
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Выбрали только заявки в которых нет ни звонков ни чатов
$leads = array_filter($leads, function($el) {
  return ($el["calls_note_created_time"] == NULL and $el["chat_event_created_time"] == NULL and $el["calls_events_created_time"] == NULL);
});
$leads = array_values($leads);

//Отметаем случаи, когда после взятия заявки есть входящий звонок и по нему поговорили
$leads = array_filter($leads, function($el) {
  global $db;
  $stmt = $db->query('SELECT *
                      FROM `notes_contacts` 
                      WHERE `note_type` = "call_in" and `entity_id` = '.(int)$el["main_contact_id"].' and `created_at` >= '.((int)$el["date_create"] - 5*60).'
                      ORDER BY `created_at` ASC');
  $callins = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $isConversation = true;
  foreach($callins as $call) {
    $params = json_decode($call["params"], true);
    if($params["call_status"] == 4 and $params["duration"] > 0) {
      $isConversation = false;
    }
  }
  return $isConversation;
});

//Отметаем сделки, которые взяли менее 10 минут назад
$leads = array_filter($leads, function($el) {
  global $db;
  $stmt = $db->query('SELECT * 
                      FROM `notes_all` 
                      WHERE `entity_id` = '.$el["id"].'
                        and (`type` = "entity_responsible_changed" or `type` = "lead_added") 
                      ORDER BY `notes_all`.`created_at` DESC');
  $times = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $isTimesUp = true;
  return $times[0]["created_at"] < (time() - 10*60);
});

// Создаём массив ID сделок
$leadIds = array_map(function($el) {
  return (int)$el['id'];
}, $leads);

//Забираем задачи по сделкам из AMO
$tasks = getTasks($leadIds);

//создаём массив тасков для сделок
$tasksArr = [];
$textTaskForBoss = "Не работает по новой сделке уже более 10 минут.";
  foreach($leads as $el) {
    $taskForBossExist = checkTask($tasks, $textTaskForBoss, $el["id"]);
    if(!$taskForBossExist) {
      array_push($tasksArr, (object)["responsible_user_id" => 3406348,
      "entity_id" => (int)$el["id"],
      "entity_type" => "leads",
      "task_type_id" => 2109414,
      "text" => $textTaskForBoss,
      "complete_till" => time() + 5*60,
      "created_by" => 0]);
    }
  }


//Создаём таски в AMO
$result = sendRequestToAmo("POST", "/api/v4/tasks", $tasksArr);

function checkTask($tasks, $text, $leadId) {
  $isTaskExist = false;
  foreach($tasks as $task) {
    if(!$task["is_completed"] and $task["text"] == $text and $task["entity_id"] == $leadId) {
      $isTaskExist = true;
    }
  }
  return $isTaskExist;
}

function getTasks($leadIds) {
  $tasks = [];
  $page = 1;
  $limit = 250;
  do {
    $options = [
      "page" => $page,
      "limit" => $limit,
      "filter" => [
        "entity_id" => $leadIds,
        "task_type" => 2109414
      ] 
    ];
    $t = sendRequestToAmo('GET','/api/v4/tasks', $options);
    $t = isset($t["_embedded"]["tasks"]) ? $t["_embedded"]["tasks"] : [];
    $tasks = array_merge($tasks, $t);
    $page++;
  } while(count($t) == $limit);
  return $tasks;
}
?>