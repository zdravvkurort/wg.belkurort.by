<?php
//подключаем БД
//$DBConfig["db_name"] = "zdravkurort";
require "../db_login.php";

$data = json_decode(file_get_contents('php://input'),true);
if($_SERVER["REQUEST_METHOD"]=="POST" and $data["hash"] == "kdjfjgdsljgiigtjojsgtjgtlrlwe") {
	foreach($data["values"] as $value) {
			$date = strtotime($value[0]);
			$date = date('Y-m-d H:i:s',$date);
			$stmt = $db->query('SELECT * FROM `inner_courses` where date = "'.$date.'"');
			$cours = $stmt->fetchAll();
				
			
			if(count($cours) != 0) {
				$st = $db->prepare('UPDATE
										`inner_courses`
									SET
										`BYNRUB` = '.floatval($value[1]).',
										`RUBBYN` = '.floatval($value[2]).',
										`BYNEUR` = '.floatval($value[3]).',
										`EURBYN` = '.floatval($value[4]).',
										`BYNUSD` = '.floatval($value[5]).',
										`USDBYN` = '.floatval($value[6]).',
										`RUBEUR` = '.floatval($value[7]).',
										`EURRUB` = '.floatval($value[8]).',
										`RUBUSD` = '.floatval($value[9]).',
										`USDRUB` = '.floatval($value[10]).',
										`EURUSD` = '.floatval($value[11]).',
										`USDEUR` = '.floatval($value[12]).'
									WHERE
										`id` = '.$cours[0]['id']);
				$st->execute();
			} else {
			$query = "INSERT INTO `inner_courses` (
									`id`, 
									`date`, 
									`BYNRUB`, 
									`RUBBYN`, 
									`BYNEUR`, 
									`EURBYN`, 
									`BYNUSD`, 
									`USDBYN`, 
									`RUBEUR`, 
									`EURRUB`, 
									`RUBUSD`, 
									`USDRUB`, 
									`EURUSD`, 
									`USDEUR`) 
						VALUES (
									NULL, 
									'".$date."', 
									'".floatval($value[1])."', 
									'".floatval($value[2])."', 
									'".floatval($value[3])."', 
									'".floatval($value[4])."', 
									'".floatval($value[5])."', 
									'".floatval($value[6])."', 
									'".floatval($value[7])."', 
									'".floatval($value[8])."', 
									'".floatval($value[9])."', 
									'".floatval($value[10])."', 
									'".floatval($value[11])."', 
									'".floatval($value[12])."');";
			$db->prepare($query)->execute();
			}
	}
	print_r(json_encode([]));
}



?>