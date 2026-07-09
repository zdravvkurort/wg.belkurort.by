<?php 

//подключаем amo
require "../auth.php";
require "../functions.php";
require "../db_login.php";

//получаем timestamp
$ts = file_get_contents('check_contact.txt');

//получаем контакты на Плешивцевой
$limit = 0;
//	do {
		$contacts = $amo->contact->apiList([	
											'limit_rows' => 500,
											'responsible_user_id' => 3406348,
											'limit_offset' => $limit], 
											date("Y-m-d H:i:s",$ts));
		//перебираем контакты
		foreach($contacts as $contact) {
			//перебираем лиды
			foreach($contact["linked_leads_id"] as $i) {
				//получаем информацию по лиду
				$stmt = $db->query('SELECT 
										leads.id, 
										leads.responsible_user_id,
										leads.status_id
									FROM `leads`
									where leads.id = '.$i.' and leads.status_id <> 142');
				$lead = $stmt->fetchAll();
				if(count($lead) > 0) {
					$lead = $lead[0];
					//если ответственный по контакту не равен ответственному по лиду
					if(intval($contact["responsible_user_id"]) != intval($lead["responsible_user_id"])) {
						//меняем ответственного по контакту на ответственного по лиду
							$cont = $amo->contact;
							$cont['responsible_user_id'] = intval($lead["responsible_user_id"]);
							$cont->apiUpdate((int)$contact['id'], 'now');
							sleep(0.3);
							vardump($contact['id']);
					}					
				}
			}
				$ts = ($ts<=$contact["last_modified"]) ? $contact["last_modified"] : $ts;
				file_put_contents('check_contact.txt', $ts);
		}
		sleep(0.5);
//	} while (count($newArr) == 500);
