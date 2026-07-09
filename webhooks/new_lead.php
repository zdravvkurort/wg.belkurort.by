<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL); //показываем все ошибки
exit;
//блокируем файл для одновременного соединения
$lockfile = fopen("leads.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

sleep(2);

//подключаем amo
require_once(__DIR__ . '/../auth.php');
require_once(__DIR__ . '/../functions.php');

//открываем сохранённый файл с timestamp последнего обновления
$tsf = 'ts.txt';
$ts = file_get_contents($tsf);
$istochnick_zadan = false;

//подключаемся к внутреннему api amocrm		
define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'LOGIN', $login );
define ( 'PASSWORD', $pas );
define ( 'DOMAIN', 'zdravkyrort' );
define ( 'API', $userhash );

require_once(__DIR__ . '/../widget/doubleget/curl.class.php');
require_once(__DIR__ . '/../widget/doubleget/amocrm.class.php');

$trashnumbers = ["1234567","123456789","1234567890","111111111","222222222","333333333","444444444","555555555","666666666","777777777","888888888","999999999","000000000","987654321","9876543210"];
$siteSettingArr = ["primorski.zdravkurort.by" => 515963, 
					"zhemchuzhina.zdravkurort.by" => 519283,
					"priozerny.zdravkurort.by" => 523259,
					"pridneprovsky.zdravkurort.by" => 535136,
					"westa.zdravkurort.by" => 536789,
					"borovoe.zdravkurort.by" => 537431,
					"belkurort.by" => 437453,
					"krinitsa.zdravkurort.by" => 734423,
					"sosnovy-bor.zdravkurort.by" => 737105,
					"alfa-radon.zdravkurort.by" => 738211,
					"radon.zdravkurort.by" => 737637,
					"yunost.zdravkurort.by" => 737953,
					"belorussia-mishor.zdravkurort.by" => 738339,
					"plissa.zdravkurort.by" => 738507, 
					"sosny-mn.zdravkurort.by" => 738539,
					"ruzhansky.zdravkurort.by" => 739221,
					"rassvet-luban.zdravkurort.by" => 741695,
					"porechie.zdravkurort.by" => 741713,
					"belorusochka.zdravkurort.by" => 741719,
					"letsy.zdravkurort.by" => 741721,
					"chenki.zdravkurort.by" => 741723,
					"lenina.zdravkurort.by" => 741725,
					"narochanka.zdravkurort.by" => 741727,
					"san-naroch.zdravkurort.by" => 741729,
					"lesnye-ozera.zdravkurort.by" => 741733,
					"bug.zdravkurort.by" => 741735,
					"rudnya.zdravkurort.by" => 742473,
					"volma.zdravkurort.by" => 742497,
					"isloch.zdravkurort.by" => 743137,
					"mashinostroitel.zdravkurort.by" => 743261,
					"magistralny.zdravkurort.by" => 743543,
					"zheleznyaki.zdravkurort.by" => 743773,
					"raduga.zdravkurort.by" => 744009,
					"energetik-grodno.zdravkurort.by" => 744241,
					"chaborok.zdravkurort.by" => 744243,
					"lepelsky.zdravkurort.by" => 744537,
					"berezka.zdravkurort.by" => 744621,
					"sputnik.zdravkurort.by" => 744975,
					"berestie.zdravkurort.by" => 745153, 
					"zolotye-peski.zdravkurort.by" => 745253,
					"ozerny.zdravkurort.by" => 745599,
					"belaya-rus.zdravkurort.by" => 745809,
					"shinnik.zdravkurort.by" => 746039,
					"zheleznodorozhnik.zdravkurort.by" => 746047,
					"serebr-kluchi.zdravkurort.by" => 746149,
					"neman72.zdravkurort.by" => 746279,
					"svitaz.zdravkurort.by" => 746509,
					"juravushka.zdravkurort.by" => 746803,
					"naroch-bereg.zdravkurort.by" => 747659, 
					"belarus-sochi.zdravkurort.by" => 747985, 
					"belarus-krasnaya-polyana.zdravkurort.by" => 748381,
					"belarus-tuapse.zdravkurort.by" => 748383,
					"yaselda.zdravkurort.by" => 785162,
					"tutbonus.zdravkurort.by" => 761712,
					"zdravkurort.by/newyear" => 780310,
					"zdravkurort.by/covid-19-yunost" => 782468,
					"zdravkurort.by/covid-19" => 764930,
					"silichy.zdravkurort.by" => 787746,
					"zdravkurort.by/putevki/westa" => 793082,
					"lesnoe.zdravkurort.by" => 795726,
					"naftan.zdravkurort.by" => 796660,
					"solnechny-bereg.zdravkurort.by" => 800116,
					"solnechny.zdravkurort.by" => 796686,
					"raketa.zdravkurort.by" => 802446];
//авторизуемся
$allleads = getRequestToAmo('/api/v2/leads?filter%5Bdate_create%5D%5Bfrom%5D='.((int)($ts+1)).'&limit_rows=500&limit_offset=0'); //получаем список сделок

//если сделок больше чем 500, забираем все
if(count($allleads) >= 500) {
	$countnewarr = 500;
	$offset = 500;
	while($countnewarr != 0) {
		$newarr = getRequestToAmo('/api/v2/leads?filter%5Bdate_create%5D%5Bfrom%5D='.((int)($ts+1)).'&limit_rows=500&limit_offset='.$offset);

		$countnewarr = count($newarr);
		$allleads = array_merge($allleads, $newarr);
		$offset = count($allleads);
		sleep(1);
	}
}

// Добавляем к выборке лиды из инсты и вконтакте
$leadsFromCollector = file_get_contents('leadCollectorForCheck/leads.txt');
$leadsFromCollector = json_decode($leadsFromCollector, true);
if(count($leadsFromCollector) > 0) {
	file_put_contents("leadCollectorForCheck/leads.txt", json_encode([]));
	$leadsFromCollector = array_filter($leadsFromCollector, function($el){return $el['pipeline_id'] == 1736272;});
	$allleads = array_merge($allleads, $leadsFromCollector);
}

if(count($allleads) == 0) exit;

usort($allleads,"cmp");

//готовим список id контактов на проверку по номеру телефона
$idsContacts = array();
$allContacts = array();
if(count($allleads) != 0) {
	foreach($allleads as $l) {
		if($l["responsible_user_id"] == 3406348 and $l["status_id"] == 26081347) {
			if(isset($l["contacts"]["id"])) {
				foreach($l["contacts"]["id"] as $contactId) {
						array_push($idsContacts,$contactId);
				}
			}
		}
	}
	//получаем инфу по всем контактам
	$arrayForContactsQuery = array_chunk($idsContacts, 499);
		foreach($arrayForContactsQuery as $a) {
			array_push($allContacts,getRequestToAmo('/api/v2/contacts/?id%5B%5D='.implode('&id%5B%5D=', $a)));
		}
}

$allContacts = (isset($allContacts[0])) ? $allContacts[0] : array();
$allC = array();
//Ищем сделки по найденным контактам
if(count($allContacts) > 0) {
	foreach($allContacts as $c) {
		$allC[$c["id"]] = $c;
		//итерируем каждое поле customField
		foreach($c["custom_fields"] as $custom_field) {
			//если поле для телефона
			if(isset($custom_field["code"]) and $custom_field["code"] == "PHONE") {
				foreach($custom_field["values"] as $phone) {
					//Берём номер телефона и оставляем только цифры
					$p = preg_replace("/[^0-9]/", '', $phone['value']);
					//Проверяем подходит ли номер по количеству символов
					if(strlen($p) > 9) {
						//Обрезаем номер до 9 символов справа
						$p = substr($p, strlen($p) - 9);
						//Ищем сделки с таким же номером телефона
						// $leadsListWithTargetPhone = getRequestToAmo('/api/v2/leads?query='.$p);

						//Ищем контакты по номеру телефона
						$contactsByPhone = getRequestToAmo('/api/v2/contacts?query='.$p);
						//Перебираем контакты для получения id заявок
						$leadsIds = [];
						foreach($contactsByPhone as $el) {
							$leadsIds = array_merge($leadsIds, $el['leads']['id']);
						}

						if(count($leadsIds)) {
							sleep(0.5);
							//Получаем сделки по контактам
							$leadsListWithTargetPhone = getRequestToAmo('/api/v2/leads?id='.implode(",", $leadsIds));
									
							foreach($leadsListWithTargetPhone as $key => $value) {
								//Записываем найденные сделки в контакт
								$allC[$c["id"]]['connected_leads'][$value['id']] = $value;
							}
						}
					}
				}
			}
		}
	}
}
unset($allContacts);

if(count($allleads) != 0) {
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<старая логика>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
	foreach($allleads as $l) {
		// if(findTrashNumberPhone($l)) { //Проверяем не спамовый ли номер телефона, e-mail
		// 	deleteLeads([$l['id']]);
		// } else 
		if($l["pipeline_id"] == 1736272) {
			$lead = $amo->lead;
			if($l["created_at"] > $ts) {
				$ts = $l["created_at"];
			}
			//заполняем источник сайт
			if(findcustomfieldval($l,305083) == NULL) {
					if(count($l['tags']) > 0 ) {
						foreach($l['tags'] as $tag) {
							if(stripos($tag['name'], "marqu") !== false) {
								$lead->addCustomField(305083, 446093);
							} else if(stripos($tag['name'], "входящ") !== false) {
								$lead->addCustomField(305085, 437457);
							} else if(stripos($tag['name'], "fb") !== false) {
								$lead->addCustomField(305083, 751069);
								$lead->addCustomField(305085, 533937);
								$istochnick_zadan = true;
							}
						}
					}
				}
			$lname = $l['name'];
			$siteFieldInAmoCrm = findcustomfieldval($l,352877);
			if($siteFieldInAmoCrm != NULL) {
				$siteId = getIstocknicId($siteFieldInAmoCrm, $siteSettingArr);
				if($siteId) {
					$lead->addCustomField(305083, $siteId);
				}
			} else if($lname) {
				$siteId = getIstocknicId($lname, $siteSettingArr);
				if($siteId) {
					$lead->addCustomField(305083, $siteId);
				}
			}

$istochnicSettingArr = ["yandexRSYA" => 437461, 
												"yand" => 437457,
												"googl" => 437459,
												"vk" => 446095,
												"target" => 487035,
												"push" => 501103,
												"zen" => 746497,
												"tutby" => 761714,
												"instagram" => 533937,
												"email" => 502821,
												"telegram" => 779692];
		
			//заполняем источник заявки
			$source = findcustomfieldval($l,306047);
			$source2 = findcustomfieldval($l,737163);
			if(count(findcustomfieldval($l,305085)) == 0 and $istochnick_zadan == false) {
				$istochnicId = getIstocknicId($source, $istochnicSettingArr);
				$istochnic2Id = getIstocknicId($source2, $istochnicSettingArr);
				if($istochnicId) {
					$lead->addCustomField(305085, $istochnicId);
				} else if($istochnic2Id) {
					$lead->addCustomField(305085, $istochnic2Id);
				} else {
					$lead->addCustomField(305085, 460319);
				}
			}

			//задаём значения по умолчанию
			if(!findcustomfieldval($l,305333)) {
				$lead->addCustomField(305333, 437777);
			}
			$turobluzhivanie = (!findcustomfieldval($l,305091)) ? 25 : findcustomfieldval($l,305091);
			$infouslugi = (!findcustomfieldval($l,305093)) ? 25 : findcustomfieldval($l,305093);
			
			if(!findcustomfieldval($l,305139)) {
				$lead->addCustomField(305139, 0);
			}
			if(!findcustomfieldval($l,305137)) {
				$lead->addCustomField(305137, 0);
			}

			//создаём примечание
			$textnote = '';
				
			if($l["status_id"] == 26081347 and (findcustomfieldval($l,352869) != NULL || findcustomfieldval($l,352817) != NULL || findcustomfieldval($l,352871) != NULL || findcustomfieldval($l,352873) != NULL)) {
				$textnote .= (findcustomfieldval($l,352877) != NULL) ? "Заявка с сайта: ".findcustomfieldval($l,352877) : "";
				$textnote .= (findcustomfieldval($l,352869) != NULL) ? "
				Дата заезда: ".findcustomfieldval($l,352869) : "";
				$textnote .= (findcustomfieldval($l,352817) != NULL) ? "
				Дата выезда: ".findcustomfieldval($l,352817) : "";
				$textnote .= (findcustomfieldval($l,352871) != NULL) ? "
				Количество взрослых: ".findcustomfieldval($l,352871) : "";
				$textnote .= (findcustomfieldval($l,352873) != NULL) ? "
				Количество и возраст детей: ".findcustomfieldval($l,352873) : "";
			} else if (findcustomfieldval($l,389402) != NULL) {
				$textnote .= "Форма: ".findcustomfieldval($l,389402);
			}

			if($textnote != "") {
				$note = $amo->note;
				$note['element_id'] = $l['id'];
				$note['element_type'] = 2; // 1 - contact, 2 - lead
				$note['note_type'] = 4; // 
				$note['text'] = $textnote;
				$id = $note->apiAdd();
				sleep(0.5);					
			}
	
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Новая логика>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
			$tags_for_lead = array();

			foreach($l['tags'] as $tag) {
				$tags_for_lead[] = $tag["name"];
			}
			$tags_for_lead[] = "чат";
		
			$otherLeads = is_one_lead($l,$allC);//Это единственная сделка с этим номером телефона?
			$otherLeads = array_filter($otherLeads, function($el) {
				return ($el["pipeline_id"] == 1736272);
			});
			// Проверяем есть ли успешно реализованные сделки. Если есть, меняем им туробслуживание и инфоуслуги по текущей сделке
			$successLeads = array_filter($otherLeads, function($el) {
				return ($el["status_id"] == 142);
			});

			if(count($successLeads)) {
				$turobluzhivanie = 30;
				$infouslugi = 30;
			} 
			
			//Эта сделка на Свиридович Григории?
			if(($l["responsible_user_id"] == 3406348 or $l["responsible_user_id"] == 3449311) and $l["status_id"] == 26081347) {
				if(count($otherLeads) > 0) {	//
						$activeLeads = getActiveLeads($otherLeads); //Есть активные сделки или сделки в успешно реализованном и которыми ещё занимается клиентский?
						usort($activeLeads,"cmp"); //сортируем активные заявки по дате их прихода
						$resp_us = get_resp_us($otherLeads);
						
						if(count($activeLeads) > 0) {
							
								//обновляем инфу в массиве $allC
								$allC = get_lead_in_143($l,$allC);

								// $lead['status_id'] = 143; //(1) Отправляем эту сделку в закрыто и нереализовано
								// $lead->addCustomField(305267, 437737); //ставим нецелевой статус
								$lead['responsible_user_id'] = $resp_us; //Cтавим ответственным менеджером предыдущего менеджера
								
								if($resp_us != 3406348) { //Делаем задачу на первую активную сделку либо на продажи, либо на клиентского
									$task = $amo->task;
									$task['element_id'] = $activeLeads[count($activeLeads)-1]["id"];
									$task['element_type'] = 2;
									$task['date_create'] = 'NOW';
									$task['task_type'] = 1;
									$task['text'] = "Клиент оставил повторную заявку ".$l['name'];
									$task['responsible_user_id'] = ($activeLeads[count($activeLeads)-1]["status_id"] == 142) ? findcustomfieldval($activeLeads[count($activeLeads)-1], 371365) : $resp_us;
									$task['complete_till'] = 'NOW';
									$id = $task->apiAdd();
									sleep(0.5);
								} else {
									$lead['status_id'] = 26081347;
								}
								
								// //Добавляем примечания в основную сделку
								// $notes = $amo->note->apiList([
								// 	'type' => 'lead',
								// 	'element_id' => $l['id'],
								// 	'note_type' => 4
								// ]);
								// sleep(0.5);

								// $notes_array = [];
								// foreach($notes as $note) {
								// 	$n = $amo->note;
								// 	$n['element_id'] = $activeLeads[count($activeLeads)-1]["id"];
								// 	$n['element_type'] = 2;
								// 	$n['note_type'] = 4;
								// 	$n['text'] = $note["text"];
								// 	array_push($notes_array, $n);
								// }
								// if(count($notes_array)) {
								// 	$amo->note->apiAdd($notes_array);
								// }
								// sleep(0.5);
								$mergedLead = $activeLeads[count($activeLeads)-1];
								if($mergedLead['responsible_user_id'] == 3406348){
									$mergedLead['status_id'] = 26081347;
								}
								mergeLeads($l, $mergedLead);
								// deleteLeads([$l['id']]);
						} else { //Нет активных сделок и сделок, которыми занимается клиентский
								$lead['tags'] = $tags_for_lead;
								if($resp_us == 3406348) { //Если ответственный Гриша, то в распределение
									$lead['status_id'] = 26081347;
								// 	//vardump($l['id']." ".$l['name']." В Распределение");
								} else {
									
								// 	//Если нет, ставим сделку на ответственного и ставим ему задачу
									$lead['responsible_user_id'] = $resp_us; //Cтавим ответственным менеджером предыдущего менеджера
									$task = $amo->task;
									$task['element_id'] = $l['id'];
									$task['element_type'] = 2;
									$task['date_create'] = 'NOW';
									$task['task_type'] = 1;
									$task['text'] = "Твой старый клиент оставил новую заявку";
									$task['responsible_user_id'] = $resp_us;
									$task['complete_till'] = 'NOW';
									$id = $task->apiAdd();
									sleep(0.5);
								// 	//vardump($l['id']." ".$l['name']." Меняем ответственного и задачу на старого ответственного");
								}
							}
				} //Не трогаем сделку
			}		//Не трогаем сделку
			
			$lead['tags'] = $tags_for_lead;
			//задаём туробслуживание и инфоуслуги
			$lead->addCustomField(305091, $turobluzhivanie);
			$lead->addCustomField(305093, $infouslugi);
			$lead->addCustomField(305169, (int)$turobluzhivanie + (int)$infouslugi);
			$lead->addCustomField(305337, (int)$turobluzhivanie + (int)$infouslugi);

			$lead->apiUpdate((int)$l["id"], 'now');
			file_put_contents($tsf, $ts);
		}
	}
};

			function is_one_lead($l,$allC) {
				$arr = array();
				if(isset($l["contacts"]["id"])) {
					foreach($l["contacts"]["id"] as $cont_id) {
						if(isset($allC[$cont_id]["connected_leads"])) {
							foreach($allC[$cont_id]["connected_leads"] as $lead) {
								if($lead["id"] != $l["id"]) {
									$arr[$lead["id"]] = $lead;
								}
							}
						}
					}
				}
	//			if(isset($arr[$l["id"]])) { unset($arr[$l["id"]]); };
				return $arr;
			}

					function getActiveLeads($otherLeads) {
						$activeLeads = array();
						foreach($otherLeads as $v) {
							// if($v["pipeline_id"] == 1736272 and $v["status_id"] != 143) {
							if($v["pipeline_id"] == 1736272) {
								if($v["status_id"] == 142 and strtotime(findcustomfieldval($v, 305205)) > time() and findcustomfieldval($v, 305205) != null) {
									$activeLeads[] = $v;
								} else if($v["status_id"] != 142) {
									$activeLeads[] = $v;
								}
							}
						};
						
						return $activeLeads;
					}	
					
					function get_resp_us($leads) {
						$out = 3406348;
						$last_timestamp = 999999999999;
						foreach($leads as $le) {
							if($le["responsible_user_id"] != 3406348 and $le["created_at"] < $last_timestamp) {
								$out = $le["responsible_user_id"];
								$last_timestamp = $le["created_at"];
							}
						}
						return $out;
					}
					
					function get_lead_in_143($l,$allC) {
						foreach($allC as $key => $value) {
							if(isset($value['connected_leads'][$l['id']])) {
								$allC[$key]['connected_leads'][$l['id']]["status_id"] = 143;
							}
						}
						return $allC;
					}
					
		function findTrashNumberPhone($l) {
			global $allC;
			global $trashnumbers;
			if(isset($l["contacts"]["id"])) {
				foreach($l["contacts"]["id"] as $contact) {
					if(isset($allC[$contact])) {
						foreach($allC[$contact]["custom_fields"] as $cf) {
								foreach($cf["values"] as $val) {
									$p = preg_replace("/[^0-9]/", '', $val["value"]);
									//if(strlen($p) < 6) return true;
									foreach($trashnumbers as $trn) {
										if(strpos($p, $trn) !== false) {
											return true;
										}
									}
								}							
						}
					}
				}
			}
			return false;
		}
					
function cmp($a, $b) 
{
    return ($a["created_at"] < $b["created_at"]);
}

function mergeLeads($lead, $toLead) {
    require_once(__DIR__ . '/../auth.php');
    require_once(__DIR__ . '/../functions.php');

	if(!$lead or !$toLead) return;

	$resultData = [
		'id' => [$toLead["id"], $lead["id"]],
		'result_element' => [
			'NAME' => $toLead["name"],
			'MAIN_USER_ID' => $toLead["responsible_user_id"],
			'DATE_CREATE' => date("Y-m-d H:i:s", $toLead["created_at"]),
			'PRICE' => ($toLead["sale"]) ? $toLead["sale"].' ₽' : '0 ₽',
			'STATUS' => $toLead["status_id"],
			'PIPELINE_ID' => $toLead["pipeline_id"],
			'TAGS' => getTags([$lead, $toLead]),
			'cfv' => getCFV($lead, $toLead),
			'CONTACTS' => getContacts([$lead, $toLead]),
			'ID' => $toLead["id"],
		],
		// 'ID' => $toLead["id"],
	];

	$toLeadCompanyUID = $toLead["responsible_user_id"]["company"]["id"];
	$leadCompanyUID = $lead["responsible_user_id"]["company"]["id"];

	if($leadCompanyUID) {
		$resultData['COMPANY_UID'] = $leadCompanyUID;
	}

	if($toLeadCompanyUID) {
		$resultData['COMPANY_UID'] = $toLeadCompanyUID;
	}

	$resultData = http_build_query($resultData);
	$resultData = preg_replace('/(id%5B)\d+(%5D)/', '$1%5D', $resultData);
	$resultData = preg_replace('/(result_element%5BCONTACTS%5D%5B)\d+(%5D)/', '$1%5D', $resultData);

	// $mergeLogTxt = 'merge.log.txt';
	// $mergeLog = file_get_contents($mergeLogTxt);
	// $mergeLog = $mergeLog.'
	// '.$resultData;
	// file_put_contents($mergeLogTxt, $mergeLog);

	$response = sendRequestToAmo("POST", '/ajax/merge/leads/save/', $resultData);
	return $response;
}
	function getContacts($leadsArr) {
		$result = [];
		foreach ($leadsArr as $lead) {
			foreach($lead["contacts"]["id"] as $id) {
				array_push($result, $id);
			}
		};
		$result = array_unique($result);
		return $result;
	}

	function getCFV($lead, $toLead) {
		$result = [];
		foreach($toLead["custom_fields"] as $field) {
			$result[$field['id']] = $field["values"][0]['value'];
		}
		foreach($lead["custom_fields"] as $field) {
			if(!isset($result[$field['id']])) {
				$result[$field['id']] = $field["values"][0]['value'];
			}
		}
		return $result;
	}

	function getTags($leadsArr) {
		$result = [];
		foreach ($leadsArr as $lead) {
			foreach($lead["tags"] as $tag) {
				array_push($result, $tag['id']);
			}
		};
		$result = array_unique($result);
		return $result;
	}

	function getIstocknicId($findString, $lib) {
		foreach ($lib as $istName => $istId) {
			if(stripos($findString, $istName) !== false) {
				return $istId;
			}
		}
		return;
	}

	function build_query_with_array_indexes($data) {
		return preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($data));
	}
?>