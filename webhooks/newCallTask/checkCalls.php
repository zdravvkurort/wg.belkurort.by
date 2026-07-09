<?
// Блокировка одновременного выполнения файла
$lockfile = fopen("lockfile.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
die("");

// Подключаем AMO, функции для разработки и БД
require "../../auth.php";
require "../../functions.php";
require "../../db_login.php";

// Берём из файла крайний номер события
$tsf = 'num.txt';
$ts = file_get_contents($tsf);

// Настройки
$limit = 250; // Максимально возможное количество сделок/контактов за 1 запрос который отдаёт AMO
$taskType = 2230827; // Тип задачи, которую отслеживаем

// Ищем сделки по которым нужно проставить задачу
$calls = getCalls($db, $ts); // Забираем события из БД
$contactIds = getContactsFromCalls($calls);// Формируем массив id контактов
$contacts = getEntitiesByIds($contactIds, "contacts", $limit); // Получаем контакты
$contacts = namingArray($contacts); // именуем массив
$contactTasks = getActiveTasks($contactIds, "contacts", $taskType); // Получаем задачи по контактам
$contacts = mergeContactsAndTasks($contacts, $contactTasks); // Мержим задачи с контактами

$leadsIds = getLeadsFromContacts($contacts); // Получаем список id сделок
// leads = getLeadsInfo($db, $leadsIds); // Получаем лиды
$leads = getEntitiesByIds($leadsIds, "leads", $limit); // Получаем лиды
$leadsTasks = getActiveTasks($leadsIds, "leads", $taskType); // Получаем задачи по лидам
$leads = namingArray($leads); // именуем массив
$leads = mergeLeadsAndTasks($leads, $leadsTasks); // Мержим лиды и задачи
$contacts = mergeContactsAndLeads($contacts, $leads); // Мержим контакты с лидами

$contacts = sliceTaskedContact($contacts); // Отсекаем контакты в которых есть задачи либо контакты в сделках которых есть задачи

// формируем список задач
$tasks = createTasksArray($contacts, $taskType);

// Отправить задачи в AMO
$tasksArr = array_chunk($tasks, 50);

for($i = 0; $i <= count($tasksArr); $i++) {
	if(count($tasksArr[$i])) sendRequestToAmo('POST', '/api/v4/tasks', $tasksArr[$i]);
}

// Формируем массив обновлений для лидов
$updatedLeads = createUpdatedLeadsArray($contacts, $calls);

// Отправляем изменения по сделкам в AMO
if(count($updatedLeads)) sendRequestToAmo('PATCH', '/api/v4/leads', $updatedLeads);

$ts = getMaxMessageNum($calls, $ts); //Получаем максимальный номер
file_put_contents($tsf, $ts); // Записываем его в файл

function createTasksArray($contacts, $taskType) {
	$tasks = [];
	foreach($contacts as $contact) {
		// Если по контакту нет лидов и активных задач, то добавляем задачу в список
		if(!count($contact["_embedded"]["leads"])) array_push($tasks, (object)[	"responsible_user_id" => (int)$contact["responsible_user_id"],
																																						"entity_id" => (int)$contact["id"],
																																						"entity_type" => "contacts",
																																						"task_type_id" => (int)$taskType,
																																						"text" => "Входящий звонок",
																																						"complete_till" => (time() + 1*60),
																																						"created_by" => 0]);
	
		if(count($contact["_embedded"]["leads"])) {
			list($leadId, $responsibleUserId) = leadsController($contact["_embedded"]["leads"]);
			if($leadId != NULL and $responsibleUserId != NULL) {
				array_push($tasks, (object)[	"responsible_user_id" => (int)$responsibleUserId,
																			"entity_id" => (int)$leadId,
																			"entity_type" => "leads",
																			"task_type_id" => (int)$taskType,
																			"text" => "Входящий звонок",
																			"complete_till" => (time() + 1*60),
																			"created_by" => 0]);
			}
		}
	}
	return $tasks;
}

function leadsController($leads) {
	$leads = array_filter($leads, function($el) { 
		return $el["lead"]["pipeline_id"] == 1736272; 
	});
	$leads = array_values($leads);

	usort($leads, function($a, $b) {
		if($a["lead"]["created_at"] == $b["lead"]["created_at"]) return 0;
		return ($a["lead"]["created_at"] > $b["lead"]["created_at"]) ? 1 : -1;
	});

	// Если новая заявка Оксаны, то не ставим никаких задач
	if(isOksanaNewLead($leads[0])) return [NULL, NULL];

	// Если с реализованной сделкой работает клиентский, то ставим задачу на реализованную сделку на клиентского
	$clienticsLeads = array_filter($leads, function($el) {
		$checkOut = fcfv($el["lead"], 305205);
		$clientsManager = fcfv($el["lead"], 371365);
		return ($el["lead"]["status_id"] == 142 and 
						$checkOut + 30*24*60*60 > time() and 
						$clientsManager != NULL);

	});
	$clienticsLeads = array_values($clienticsLeads);
	if(count($clienticsLeads)) return [$clienticsLeads[0]["lead"]["id"], fcfv($clienticsLeads[0]["lead"], 371365)];

	// В ином случае, ищем активную сделку и на неё ставим задачу на ответственного
	$activeLeads = array_filter($leads, function($el) {
		return $el["lead"]["status_id"] != 142 and $el["lead"]["status_id"] != 143;
	});
	$activeLeads = array_values($activeLeads);
	if(count($activeLeads)) return [$activeLeads[0]["lead"]["id"], $activeLeads[0]["lead"]["responsible_user_id"]];

	// в ином случае, ставим задачу на самую старую сделку на ответственного за сделку
	return [$leads[0]["lead"]["id"], $leads[0]["lead"]["responsible_user_id"]];
}

function fcfv($lead, $num) {
	if(!count($lead["custom_fields_values"])) return NULL;
	foreach($lead["custom_fields_values"] as $cf) {
		if($cf["field_id"] == $num) {
			return $cf["values"][0]["value"];
		}
	}
}

function sliceTaskedContact($contacts) {
	$resultArray = [];
	foreach($contacts as $contact) {
		$toogle = true;
		if(count($contact["_embedded"]["tasks"])) $toogle = false;
		foreach($contact["_embedded"]["leads"] as $lead) {
			if(count($lead["lead"]["_embedded"]["tasks"])) {
				$toogle = false;
			}
		}
		if($toogle) array_push($resultArray, $contact);
	}
	return $resultArray;
}

function createUpdatedLeadsArray($contacts, $calls) {
	$contacts = namingArray($contacts); // именуем массив
	$missedCalls = array_filter($calls, function($call) { // Оставляем только неотвеченные звонки
		$payload = json_decode($call["params"], true);
		return $payload["call_status"] == 6;
	});
	$missedCalls = array_values($missedCalls);
	
	$updatedLeads = [];
	foreach($missedCalls as $call) {
		$contact = $contacts[$call["entity_id"]];
		if((bool)$contact and (bool)count($contact["_embedded"]["leads"])) {
			foreach($contact["_embedded"]["leads"] as $lead) {
				// Если входящий неотвеченный, ответственная по сделке Плешивцева и статус сделки Новая заявка, то меняем ответственность на Гришу
				if(isOksanaNewLead($lead)) {
					array_push($updatedLeads, (object)[	"id" => (int)$lead["lead"]["id"],
																							"responsible_user_id" => (int)3406348]);
				}
			}
		}
	}
	return $updatedLeads;
}

function isOksanaNewLead($lead) {
	return ($lead["lead"]["responsible_user_id"] == 3449311 and $lead["lead"]["status_id"] == 26081347);
}

function namingArray($array) {
	if(!count($array)) return $array;
	$newArray = [];
	foreach($array as $element) {
		$newArray[$element['id']] = $element;
	}
	return $newArray;
}

function mergeLeadsAndTasks($leads, $leadsTasks) {
	foreach($leadsTasks as $task) {
		if(!$leads[$task['entity_id']]["_embedded"]["tasks"]) $leads[$task['entity_id']]["_embedded"]["tasks"] = [];
		array_push($leads[$task['entity_id']]["_embedded"]["tasks"], $task);
	}
	return $leads;
}

function mergeContactsAndTasks($contacts, $contactTasks) {
		foreach($contactTasks as $task) {
			if(!$contacts[$task['entity_id']]["_embedded"]["tasks"]) $contacts[$task['entity_id']]["_embedded"]["tasks"] = [];
			array_push($contacts[$task['entity_id']]["_embedded"]["tasks"], $task);
		}
	return $contacts;
}

function mergeContactsAndLeads($contacts, $leads) {
	if(!count($contacts)) exit;
	if(!count($leads)) return $contacts;
	foreach($contacts as $contactKey => $contactValue) {
		if(count($contactValue["_embedded"]["leads"])) {
			foreach($contactValue["_embedded"]["leads"] as $k => $l) {
				$contacts[$contactKey]["_embedded"]["leads"][$k]['lead'] = findLead($l['id'], $leads);
			}
		}
	}
	return $contacts;
}

function findLead($id, $leads) {
	foreach($leads as $lead) {
		if($lead['id'] == $id) return $lead;
	}
}

function getLeadsInfo($db, $leadsIds) {
	if(count($leadsIds) < 1) exit;
	$in  = str_repeat('?,', count($leadsIds) - 1) . '?';
	$sql = "SELECT * FROM leads WHERE id IN ($in)";
	$stm = $db->prepare($sql);
	$stm->execute($leadsIds);
	return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveTasks($entityIds, $entityType, $taskType) {
	if(count($entityIds) < 1) exit;
  $tasks = [];
  $limit = 100;
  $page = 1;
	$groupsEntityIds = array_chunk($entityIds, $limit);
	foreach($groupsEntityIds as $entityIds) {
		$query = '?limit='.$limit.'&filter[entity_type]='.$entityType."&filter[task_type]=".$taskType."&filter[is_completed]=0";
		foreach($entityIds as $entityId) {
			$query = $query."&filter[entity_id][]=".$entityId;
		}
		$t = sendRequestToAmo("GET", "/api/v4/tasks".$query);
		if(isset($t["_embedded"]["tasks"]) and count($t["_embedded"]["tasks"]) > 0) {
			array_push($tasks, $t["_embedded"]["tasks"]);
		}
	}
	return array_reduce($tasks, function($acc, $item){return array_merge($acc, $item);}, []);
}

function getEntitiesByIds($entitiesIds, $entityType, $limit) {
	if(count($entitiesIds) < 1) exit;
	$entities = [];
	$groupsEntitiesIds = array_chunk($entitiesIds, $limit);
	foreach($groupsEntitiesIds as $entitiesIds) {
		$with = ($entityType === "contacts") ? "leads": "contacts";
		$query = 'with='.$with.'&limit='.$limit;
		foreach($entitiesIds as $entityId) {
			$query = $query."&filter[id][]=".$entityId;
		}
		$entity = sendRequestToAmo("GET", "/api/v4/".$entityType."?".$query);
		if(isset($entity["_embedded"][$entityType])) {
			array_push($entities, $entity["_embedded"][$entityType]);
		}
	}
	return array_reduce($entities, function($acc, $item){return array_merge($acc, $item);}, []);
}

function getContactsFromCalls($calls) {
	if(count($calls) < 1) exit;
	$ids = [];
	foreach($calls as $call) {
			array_push($ids, $call["entity_id"]);
	}
	return $ids;
}

function getLeadsFromContacts($contacts) {
	if(count($contacts) < 1) exit;
	$ids = [];
	foreach($contacts as $contact) {
		if(count($contact["_embedded"]["leads"])) {
			foreach($contact["_embedded"]["leads"] as $l) {
				array_push($ids, $l["id"]);
			}
		}
	}
	return $ids;
}

function getMaxMessageNum($messages, $currentNum) {
	foreach($messages as $message) {
		if((int)$currentNum < (int)$message['num']) {
			$currentNum = (int)$message['num'];
		}
	}
	 return $currentNum;
}

function getCalls($db, $ts) {
	$stmt = $db->query('SELECT `entity_id`,`note_type`, MIN(`created_at`) as `created_at`, `num`, `params`
											FROM `notes_contacts`
											WHERE `note_type` = "call_in" AND `num` > '.$ts.'
											GROUP BY `entity_id`
											ORDER BY `num` ASC');
 return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>