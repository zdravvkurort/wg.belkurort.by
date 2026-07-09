<?php 
//блокируем файл для одновременного соединения
$lockfile = fopen("leadsscrip.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");
//выводим все возможные ошибки на экран
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//подключаем amoCRM
require_once(__DIR__ . '/../auth.php');

//подключаем БД
require_once(__DIR__ . '/../db_login.php');

//подключаем функции
require_once(__DIR__ . '/../functions.php');

//и ещё функций
require_once(__DIR__ . '/../load_in_db/leads_upload_functions.php');


//получили дату начала (понадобится только для первого раза)
$timethatmonth = mktime(0, 0, 0, date("m"),   18,   date("Y")); 
$timetoday = mktime(0, 0, 0, date("m"),   date("d"),   date("Y"));

$stmt = $db->query('SELECT max(`last_modified`) FROM `leads`');
$timelastupdate =  $stmt->fetchAll()[0][0]-120;

//$timelastupdate = 1577750400;
//vardump($timelastupdate);

$columns = normalizeLeadsTableStructure();

// //получаем массив доп. полей из AMO
// $cf = $amo->account->apiCurrent()['custom_fields']['leads'];

// //получаем массив столбцов из БД
// $stmt = $db->query("SHOW COLUMNS FROM `leads`");
// $columns = [];

// while ($row = $stmt->fetch())
// {
//     array_push($columns,$row['Field']);
// }

// //если столбца в БД нет - добавляем его
// foreach($cf as $field) {
// 	if(!in_array($field['id'], $columns)) {
// 		$stmt = $db->query("ALTER TABLE `leads` ADD `".$field['id']."` TEXT NOT NULL");
// 		$stmt->fetch();
// 	}
// }

$leads_list = get_leads_list($timelastupdate); //получили список лидов

flush();//сбрасываем кеш

//отправляем инфу по лидам в БД
/////////////////////////////////ФУНКЦИИ///////////////////////////////////////////////////////
// function addLeadsInTable($array_new_leads) {

// 	global $db;
// 	global $amo;
// 	global $columns;

// foreach($array_new_leads as $lead) {
// 		$query = "";
// 		$stmt = $db->query("SELECT COUNT(*) FROM `leads` WHERE id = ".$lead['id']); //берём id лида из БД
// 		$result = $stmt->fetchAll();
// 		if($result[0][0] != "0") {
// 			$stmt = $db->prepare( "DELETE FROM `leads` WHERE id = ".$lead['id']);
// 			$stmt->execute();
// 		}
// 			$query .= "INSERT INTO `leads` SET";
// 			foreach($lead as $name => $val) {	
// 				if($name != "custom_fields") {
// 					if(in_array($name, $columns)) {
// 					$query .= " ".$name."='";
// 					if(gettype($val) != "array") {
// 						$text = withoutSpecSymb($val);
// 						$query .= $text."',";
// 					} else if(gettype($val) == "array"){
// 						if($name == "tags") {
// 							foreach($val as $v) {
// 								$text = withoutSpecSymb($v['name']);
// 								$query .= $text."&";
// 							}
// 						$query=rtrim($query,"& ");
// 						}
// 						$query .= "',";
// 					}}
// 				} else if($name == "custom_fields") {
// 					foreach($val as $v) {
// 						$query .= " `".$v['id']."`='";
// 							$text = withoutSpecSymb($v["values"][0]['value']);
// 							$query .= $text."&";
// 						$query=rtrim($query,"& ");
// 						$query .= "',";
// 					}
// 				}
// 			}
// 		$query=rtrim($query,", ");
// 			try {
// 			$st = $db->query($query);
// 			$insertId=$db->lastInsertId();
// 			} catch (Exception $e) { 
// 				echo $e->errorMessage(); 
// 			}		
// }
// }

// function withoutSpecSymb($text) {
// 	$text = str_replace("'", "\'", $text);
// 	$text = str_replace('"', '\"', $text);
// 	return $text;
// }

function get_leads_list($date_start) {
$time_start = date("Y-m-d H:i:s",$date_start);
$array_new_leads = new_leads($time_start,0);
$a = count($array_new_leads);
addLeadsInTable($array_new_leads);
	while(($a % 500) == 0) {
		$array_new_leads = new_leads($time_start,$a);
		$a = $a+count($array_new_leads); 
		addLeadsInTable($array_new_leads);
		sleep(1);
	}
}

function new_leads($time_start,$offset) {
	global $amo;
	sleep(1);
		return $amo->lead->apiList([
		'limit_rows' => 500,
		'limit_offset' => $offset,
    ], $time_start);
};
?>