<?
// // Блокировка одновременного выполнения файла
// $lockfile = fopen("lockfile.lock", 'w');
// if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
// die("");

// // Подключаем AMO, функции для разработки и БД
// require "../../auth.php";
// require "../../functions.php";
// require "../../db_login.php";

// // Берём из файла крайний номер события
// $tsf = 'num.txt';
// $ts = file_get_contents($tsf);

// // Настройки
// $limit = 250; // Максимально возможное количество сделок/контактов за 1 запрос который отдаёт AMO
// $taskType = 2105502; // Тип задачи, которую отслеживаем

// // Ищем сделки по которым нужно проставить задачу
// $messages = getMessages($db, $ts); // Забираем события из БД
// $leadsIds = getLeadsIdsFromMessage($messages); // Получаем id сделок
// $leads = getEntitiesByIds($leadsIds, "leads", $limit); // Получаем сделки по списку
// $contactsIds = getIds($leads, "contacts"); // Выбираем из сделок список контактов
// $contactIdsFromMessage = getContactsIdsFromMessage($messages); // Получаем список контактов из сообщений
// $contactsIds = array_merge($contactsIds, $contactIdsFromMessage);// Добавляем в список контактов контакты с сообщениями
// $contacts = getEntitiesByIds($contactsIds, "contacts", $limit); // Выбираем все контакты по списку
// $leadsIds = getIds($contacts, "leads"); // Выбираем список id сделок по контактам
// $leadsIds = array_unique($leadsIds); // Уникализируем id
// $leadsIds = deleteIsExitsActiveTask($leadsIds, $taskType); // Фильтруем сделки с уже активными задачами
// $leads = getLeadsInfo($db, $leadsIds); // Получаем сделки по id из БД
// $leads = array_filter($leads,"checkPipline"); // Проверяем правильность воронки
// $leads = array_filter($leads,"checkIsNotAdmin"); // убираем сделки на Григории в статусе новая заявка
// $leadsWidthChat = array_filter($leads,"checkTags"); // Проверяем наличие тега Чат

// $tasksArray = makeTasksArray($leadsWidthChat, $taskType); // Создаём на каждую сделку по задаче

// if(count($tasksArray)) {
// 		sendRequestToAmo('POST', '/api/v4/tasks', $tasksArray); // Отправляем задачи в AMO
// }

// $adminLeads = array_filter($leads, "getAdminLeads"); // Выбираем сделки на Григории
// $leadsArray = makeLeadsUpdate($adminLeads); // Если сделка на Григории и не в статусе успешно реализована ставим в распределение

// if(count($leadsArray) != 0) {
// 		sendRequestToAmo('PATCH', '/api/v4/leads', $leadsArray); // Отправляем изменения в amoCRM
// }

// $ts = getMaxMessageNum($messages, $ts); //Получаем максимальный номер
// file_put_contents($tsf, $ts); // Записываем его в файл

function makeLeadsUpdate($leads) {
	$params = [];
	if(count($leads) < 1) return $params;
	foreach($leads as $lead) {
		if($lead['status_id'] != '142') {
			array_push($params, (object) [
					"id" => (int)$lead["id"],
					"pipeline_id" => 1736272,
					"status_id" => 26081347,
			]);
		}
	};
	return $params;
}

function makeTasksArray($leads, $taskType) {
	if(count($leads) < 1) return [];
	$params = [];
	foreach($leads as $lead) {
		$responsibleUserId = setResponsibleUserId($lead);
		if($responsibleUserId != '3406348') {
			array_push($params, (object)[	"responsible_user_id" => (int)$responsibleUserId,
																		"entity_id" => (int)$lead["id"],
																		"entity_type" => "leads",
																		"task_type_id" => (int)$taskType,
																		"text" => "Клиент написал сообщение",
																		"complete_till" => (time() + 5*60),
																		"created_by" => 0
																		]);
		}
	}
	return $params;
}

function setResponsibleUserId($lead) {
	if($lead["responsible_user_id"] == '3406348' and $lead['status_id'] == "142") return $lead["371365"];
	if(in_array($lead["id"], [20305868, 21667902, 22058094])) return $lead["371365"];
	if(in_array($lead["id"], [22482896, 22834714])) return $lead["responsible_user_id"];
	if($lead["pipeline_id"] == "3836187" and isset($lead["371365"])) {
		return $lead["371365"];
	} else if((($lead["pipeline_id"] == "1736272" and $lead['status_id'] == "142") or ($lead["pipeline_id"] == "3836187")) and strtotime($lead["305205"])+30*24*60*60 > time() and isset($lead["371365"])) {
		return $lead["371365"];
	} else {
		return $lead["responsible_user_id"];
	}
}

function checkTags($el) {
	if(in_array($el["pipeline_id"],["6862105"])) {
		return !in_array("Без сообщений", explode("&", $el['tags']));
	}
	return in_array("чат", explode("&", $el['tags']));
}

function checkPipline($el) {
	return in_array($el["pipeline_id"],["1736272", "3836187", "6862105"]);
}

function checkIsNotAdmin($el) {
	return !($el["responsible_user_id"] == '3406348' and $lead['status_id'] == '26081347');
}

function getAdminLeads($el) {
	return $el["responsible_user_id"] == '3406348';
}

function getLeadsInfo($db, $leadsIds) {
	if(count($leadsIds) < 1) exit;
	$in  = str_repeat('?,', count($leadsIds) - 1) . '?';
	$sql = "SELECT * FROM leads WHERE id IN ($in)";
	$stm = $db->prepare($sql);
	$stm->execute($leadsIds);
	return $stm->fetchAll(PDO::FETCH_ASSOC);
}


function isActiveTaskExist($tasksArray, $leadId) {
	global $db;
	$lead = getLeadsInfo($db, [$leadId])[0];
	$responsible_user_id = setResponsibleUserId($lead);
	foreach($tasksArray as $task) {
		if(	$task["entity_id"] == $leadId and 
				$task["entity_type"] == "leads" and
				$task["responsible_user_id"] == $responsible_user_id) {
			return true;
		}
	}
	return false;
}

function deleteIsExitsActiveTask($leadsIds, $taskType) {
	if(count($leadsIds) < 1) exit;
	$resultArray = [];
	$activeTasks = getActiveTasks($leadsIds, "leads", $taskType);
	foreach($leadsIds as $leadId) {
		if(!isActiveTaskExist($activeTasks, $leadId)) {
			array_push($resultArray, $leadId);
		}
	}
	return $resultArray;
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
	if(count($entitiesIds) < 1) return [];
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

function getLeadsIdsFromMessage($messages) {
	if(count($messages) < 1) return [];
	$leadsIds = [];
	foreach($messages as $message) {
		if($message["entity_type"] === "lead") {
			array_push($leadsIds, $message["entity_id"]);
		}
	}
	return $leadsIds;
}

function getContactsIdsFromMessage($messages) {
	if(count($messages) < 1) return [];
	$leadsIds = [];
	foreach($messages as $message) {
		if($message["entity_type"] === "contact") {
			array_push($leadsIds, $message["entity_id"]);
		}
	}
	return $leadsIds;
}

function getIds($entities, $type) {
	$ids = [];
	foreach($entities as $entity) {
		if(isset($entity["_embedded"][$type])) {
			foreach($entity["_embedded"][$type] as $link) {
				array_push($ids, $link["id"]);
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

function getMessages($db, $ts) {
	$stmt = $db->query('SELECT `entity_id`, `entity_type`, MIN(`created_at`) as `created_at`, `num`
	FROM `notes_all` 
	WHERE `type` = "incoming_chat_message" AND `num` > '.$ts.' 
	GROUP BY notes_all.entity_id
	ORDER BY notes_all.num ASC');
 return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>