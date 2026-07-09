<?php 
//блокируем файл для одновременного соединения
$lockfile = fopen("contracts_script.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

//выводим все возможные ошибки на экран
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//подключаем amoCRM
require_once "../auth.php";

//подключаем БД
require_once "../db_login.php";

//подключаем amoCRM
require_once "../functions.php";

//получили дату начала (понадобится только для первого раза)
// $timethatmonth = mktime(0, 0, 0, date("m"),   1,   date("Y")); 

$stmt = $db->query('SELECT max(`last_modified`) FROM `notes_statchange`');
$timelastupdate =  $stmt->fetchAll()[0][0]-1;

$notes_list = get_notes_list($timelastupdate);

flush();//сбрасываем кеш

function add_contracts_in_db($notes){
	global $db;
	foreach($notes as $index => $note) {
		$stmt = $db->query("SELECT COUNT(*) FROM `notes_statchange` WHERE amo_id = '".$note['id']."'"); //берём id договора из БД
		$callback = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$note["STATUS_NEW"] = $note["value_after"][0]["lead_status"]["id"];
		$note["STATUS_OLD"] = $note["value_before"][0]["lead_status"]["id"];
		$note["PIPELINE_ID_NEW"] = $note["value_after"][0]["lead_status"]["pipeline_id"];
		$note["PIPELINE_ID_OLD"] = $note["value_before"][0]["lead_status"]["pipeline_id"];

		if($callback[0]['COUNT(*)'] > 0) {
			// $stmt = $db->prepare('UPDATE notes_statchange SET element_id= :entity_id, element_type=2, note_type=3, date_create= :created_at, created_user_id= :created_by, last_modified= :created_at, STATUS_NEW= :STATUS_NEW, STATUS_OLD= :STATUS_OLD, PIPELINE_ID_NEW= :PIPELINE_ID_NEW, PIPELINE_ID_OLD = :PIPELINE_ID_OLD WHERE amo_id= :id');
			// $stmt->execute($note);
			$sql = "UPDATE notes_statchange SET 
							element_id='".$note['entity_id']."',
							element_type='2',
							note_type='3',
							date_create='".$note['created_at']."',
							created_user_id='".$note['created_by']."',
							last_modified='".$note['created_at']."',
							STATUS_NEW='".$note['STATUS_NEW']."',
							STATUS_OLD='".$note['STATUS_OLD']."',
							PIPELINE_ID_NEW='".$note['PIPELINE_ID_NEW']."',
							PIPELINE_ID_OLD='".$note['PIPELINE_ID_OLD']."',
							WHERE amo_id=".$note['id'];

			$stmt = $db->prepare($sql);
			$stmt->execute();
		} else {
			// $stmt = $db->prepare('INSERT INTO notes_statchange SET element_id=:entity_id, element_type=2, note_type=3, date_create=:created_at, created_user_id=:created_by, last_modified=:created_at, STATUS_NEW=:STATUS_NEW, STATUS_OLD=:STATUS_OLD, PIPELINE_ID_NEW=:PIPELINE_ID_NEW, PIPELINE_ID_OLD=:PIPELINE_ID_OLD, amo_id=:id');
			// $stmt->execute($note);
			$db->query("INSERT INTO notes_statchange SET 
									id=null,
									element_id='".$note['entity_id']."',
									element_type='2',
									note_type='3',
									date_create='".$note['created_at']."',
									created_user_id='".$note['created_by']."',
									last_modified='".$note['created_at']."',
									STATUS_NEW='".$note['STATUS_NEW']."',
									STATUS_OLD='".$note['STATUS_OLD']."',
									PIPELINE_ID_NEW='".$note['PIPELINE_ID_NEW']."',
									PIPELINE_ID_OLD='".$note['PIPELINE_ID_OLD']."',
									amo_id='".$note['id']."'");
			$insertId=$db->lastInsertId();
		}	
	}
}

function new_notes_statchange($timetoday,$offset) {
	$result = [];
	$newArr = [];
	$ts = strtotime($timetoday);
	$page = 1;
	do {
		$newArr = sendRequestToAmo('GET', '/api/v4/events?filter[type]=lead_status_changed&filter[created_at][from]='.$ts.'&page='.$page.'&limit=100');
		$result = array_merge($result, $newArr["_embedded"]["events"]);
		$page++;
	} while(isset($newArr["_links"]["next"]["href"]));
	return $result;
};

function get_notes_list($date_start) {
	$time_start = date("Y-m-d H:i:s",$date_start);
	$array_new_notes = new_notes_statchange($time_start,0);
	add_contracts_in_db($array_new_notes);
};
?>