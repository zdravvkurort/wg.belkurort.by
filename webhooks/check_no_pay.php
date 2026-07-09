<?php 
set_time_limit(100);

$lockfile = fopen("check_142_status.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

//подключаем amo
require "../functions.php";
require "../db_login.php";
require_once "../auth.php";

// $ts = file_get_contents('log_no_pay.txt');
// $ts .= "
// ".time();
// file_put_contents('log_no_pay.txt', $ts);


$date = strtotime(date('d.m.Y'). ' + 7 days');
$date = date('d.m.Y',$date);

	$stmt = $db->query("SELECT id, status_id, `378299` as annul FROM `leads` where leads.status_id != 143 and STR_TO_DATE(leads.`305203`,'%Y-%m-%d') < STR_TO_DATE('".$date."','%d.%m.%Y') and leads.305369 != 1 and STR_TO_DATE(leads.`305203`,'%Y-%m-%d') != 0 and leads.pipeline_id = 1736272");
	$leads = $stmt->fetchAll();


foreach($leads as $lead) {
	if($lead['annul'] != 1) {
		if($lead['status_id'] == "142" || $lead['status_id'] == "26726761") {
								$task = $amo->task;
								$task['element_id'] = $lead['id'];
								$task['element_type'] = 2;
								$task['task_type'] = 1457761;
								$task['text'] = "Скоро заезд, а оплаты в санаторий всё ещё нет!!!";
								$task['responsible_user_id'] = 3449320;
								$task['complete_till'] = 'NOW';
								$id = $task->apiAdd();
								sleep(1);

								$task = $amo->task;
								$task['element_id'] = $lead['id'];
								$task['element_type'] = 2;
								$task['task_type'] = 1457761;
								$task['text'] = "Скоро заезд, а мы санаторий мы всё ещё не оплатили!!!";
								$task['responsible_user_id'] = 12485533;
								$task['complete_till'] = 'NOW';
								$id = $task->apiAdd();
		} else if($lead['status_id'] != '26081347' and $lead['status_id'] != '26081350' and $lead['status_id'] != '26081353'){
								$task = $amo->task;
								$task['element_id'] = $lead['id'];
								$task['element_type'] = 2;
								$task['task_type'] = 1;
								$task['text'] = "До заезда осталось менее 7 дней! Что с этой сделкой?";
								$task['responsible_user_id'] = 3449320;
								$task['complete_till'] = 'NOW';
								$id = $task->apiAdd();		
								sleep(1);

								$task = $amo->task;
								$task['element_id'] = $lead['id'];
								$task['element_type'] = 2;
								$task['task_type'] = 1;
								$task['text'] = "До заезда менее 7 дней, а оплаты от клиента нет!";
								$task['responsible_user_id'] = 12485533;
								$task['complete_till'] = 'NOW';
								$id = $task->apiAdd();
		}
		sleep(1);
	}
}

?>