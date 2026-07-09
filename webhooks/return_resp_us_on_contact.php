<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); //показываем все ошибки

//подключаем amo
require_once "../auth.php";
require "../functions.php";
require "../db_login.php";

$today = date('d.m.Y', mktime(0, 0, 0, date("m"), date("d")-14, date("Y")));
$leadsId = array();

				//Забираем сделки в 142 статусе где клиенты уже 2 недели как выехали из санатория
				$stmt = $db->query("SELECT * FROM `leads` where `status_id` = 142 and leads.`305205` = STR_TO_DATE('".$today."','%d.%m.%Y')");
				$leadsFromDb = $stmt->fetchAll();
				
				foreach($leadsFromDb as $lead) {				
					$leadsId[] = $lead["id"];
				}
				
				$arrayForLeadsQuery = array_chunk($leadsId, 499);
				
				//Забираем инфу по сделкам
				if(count($arrayForLeadsQuery) > 0) {
					foreach($arrayForLeadsQuery as $a) {
						$leads = getRequestToAmo('/api/v2/leads/?id%5B%5D='.implode('&id%5B%5D=', $a));
						if(count($leads) > 0) {
							foreach($leads as $l) {
								if(isset($l["contacts"]["id"])) {
									foreach($l["contacts"]["id"] as $idContact) {
										//Переводим отвественность по контакту на соответствующего ответственного по сделке
										//vardump($idContact." - ".$l["responsible_user_id"]." - ".$l["id"]);
										$contact = $amo->contact;
										$contact['responsible_user_id'] = $l["responsible_user_id"];
										$contact->apiUpdate((int)$idContact, 'now');
										sleep(0.5);
									}
								}
							}
						}
					}
				}
?>