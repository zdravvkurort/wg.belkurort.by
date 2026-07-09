<?php 
error_reporting(E_ERROR | E_PARSE);

require "../db_login.php";
require "../functions.php";

$datestart = strtotime($_REQUEST["startdate"]); //стартовая дата (нужно получить её из запроса)
$enddate = strtotime($_REQUEST["enddate"]);

$stmt = $db->query('SELECT `id`,`name` FROM `users`');
$managerList = $stmt->fetchAll();

$stmt = $db->query('
SELECT 
	leads.id as "id",
	DATE_FORMAT(FROM_UNIXTIME(leads.date_create), "%d.%m.%Y") as "date_create",
	leads.305267 as "rej_reasion",
	users.name as "responsible_user",
	DATE_FORMAT(leads.305361, "%d.%m.%Y") as "date_prepay",
	leads.305359 as "sum_prepay",
	DATE_FORMAT(leads.305367, "%d.%m.%Y") as "date_all_pay",
	leads.305363 as "sum_all_pay",
	DATE_FORMAT(FROM_UNIXTIME(date_contract.date_contract), "%d.%m.%Y") as "date_contract",
	leads.price as "price",
	leads.318631 as "sebestoimost",
	leads.305341 as "dopmanager",
	leads.305343 as "sumdopmanager",
	leads.362303 as `sum_banketa`,
	leads.758042 as `sum_novogod_program`,
	leads.305137 as `transfer`,
	leads.370935 as `date_return`,
	leads.371141 as `type_return`,
	leads.370933 as `sum_return`,
	leads.377103 as `ne_uchet_v_op`,
	leads.378479 as `ne_uchit_vozvr`,
	leads.384509 as `not_use_at_all`,
	leads.385043 as `fine`,
	leads.393708 as `ne_uchit_vozvr2`,
	leads.381913 as `date_return2`,
	leads.381911 as `sum_return2`,
	leads.732148 as `vychet_vozvrata`,
	leads.761672 as `date_managers_deduction`,
	leads.761674 as `sum_managers_deduction`
FROM `leads`
	inner join users on leads.responsible_user_id=users.id
	inner join date_contract on leads.id=date_contract.lead_id
where
	(leads.date_create >= '.$datestart.' and leads.date_create <= '.($enddate+86399).' and leads.pipeline_id = 1736272) or 
	(date_contract.date_contract >= '.$datestart.' and date_contract.date_contract <= '.($enddate+86399).' and leads.pipeline_id = 1736272) or 
	(leads.305361 >= FROM_UNIXTIME('.$datestart.') and leads.305361 <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272) or
	(leads.305367 >= FROM_UNIXTIME('.$datestart.') and leads.305367 <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272) or
	(leads.370935 >= FROM_UNIXTIME('.$datestart.') and leads.370935 <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272) or
	(leads.381913 >= FROM_UNIXTIME('.$datestart.') and leads.381913 <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272)');
$leadslist = $stmt->fetchAll();
$outputleadslist = [];

foreach($leadslist as $lead) {
	if($lead['not_use_at_all'] != "1") {
			$a = 0;
			$b = 0;
			$dopmanagername = "";
			//вычитаем штрафы
			$lead['sum_all_pay'] = $lead['sum_all_pay'] - $lead['fine'];
			$lead['price'] = $lead['price'] - $lead['fine'];
			$lead['sebestoimost'] = $lead['sebestoimost'] - $lead['fine'];

			//Не учитываем возврат 1
			if($lead['ne_uchit_vozvr'] == "1") {
				$lead['type_return'] = "";
				$lead['date_return'] = "";
				$lead['sum_return'] = "";
				$lead['ne_uchet_v_op'] = "";
			}
			
			//Не учитываем возврат 2
			if($lead['ne_uchit_vozvr2'] == "1") {
				$lead['date_return2'] = "";
				$lead['sum_return2'] = "";
			}
			
			if(strtotime($lead['date_prepay'])>=$datestart and strtotime($lead['date_prepay'])<=$enddate) {
				$a = $lead['sum_prepay'] - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_prepay']/$lead['price']));
			}
			
			$aa = ($lead['price'] != 0) ? $lead['sum_prepay'] - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_prepay']/$lead['price'])) : 0;
			
			if(strtotime($lead['date_all_pay'])>=$datestart and strtotime($lead['date_all_pay'])<=$enddate) {
				$b = $lead['sum_all_pay'] - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_all_pay']/$lead['price']));
			}
			$bb = ($lead['price'] != 0) ? $lead['sum_all_pay'] - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_all_pay']/$lead['price'])) : 0;
			
			if($lead['dopmanager'] != 0) {
				foreach($managerList as $manager) {
					if($manager["id"] == $lead['dopmanager']) {
						$dopmanagername = $manager["name"];
					}
				}
			}
			
			$c = ($lead['price'] != 0) ? $lead['sumdopmanager']/($lead['price']) : 0;
			
			$prib = ($a != 0 or $b != 0) ? (($lead['price']-$lead['sum_banketa'] - $lead['sum_novogod_program'] - $lead['sebestoimost']-round($lead['transfer']*0.8))*
																			(($a+$b)/($lead['price']-$lead['sum_banketa'] - $lead['sum_novogod_program'])))
																	 : 0;
			
			$oldPrib = ($aa != 0 or $bb != 0) ? (($lead['price']-$lead['sum_banketa']-$lead['sum_novogod_program']-$lead['sebestoimost']-round($lead['transfer']*0.8))*
																					 (($aa+$bb)/($lead['price']-$lead['sum_banketa']-$lead['sum_novogod_program'])))
																	 : 0;
			
			$pribVozvr = ($lead['sum_return'] != 0) ? ($lead['type_return'] == "Переплата") ? $lead['sum_return'] : $oldPrib*($lead['sum_return'] / ($lead['price'])) : 0 ;
			
			$pribVozvr2 = ($lead['sum_return2'] != 0) ? $oldPrib*($lead['sum_return2'] / ($lead['price'])) : 0 ;
			
			$vychet_vozvrata_predoplata_osn_men = "";
			$vychet_kolvo_vozvrata_predoplata_osn_men = "";
			$vychet_vozvrata_oplata_osn_men = "";
			$vychet_kolvo_vozvrata_oplata_osn_men = "";
			$vychet_vozvrata_predoplata_dop_men = "";
			$vychet_kolvo_vozvrata_predoplata_dop_men = "";
			$vychet_vozvrata_oplata_dop_men = "";
			$vychet_kolvo_vozvrata_oplata_dop_men = "";
			
			if(($lead['price'] - $lead['vychet_vozvrata']) <=0 and $lead['price'] != 0) {
				$vychet_vozvrata_predoplata_osn_men = str_replace('.',',', $lead['sum_prepay'] * (1-$c));
				$vychet_kolvo_vozvrata_predoplata_osn_men = str_replace('.',',', $lead['sum_prepay']/$lead['price'] * (1-$c));
				$vychet_vozvrata_oplata_osn_men = str_replace('.',',', $lead['sum_all_pay'] * (1-$c));
				$vychet_kolvo_vozvrata_oplata_osn_men = str_replace('.',',', $lead['sum_all_pay']/$lead['price'] * (1-$c));
				$vychet_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['sum_prepay'] * $c);
				$vychet_kolvo_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['sum_prepay']/$lead['price'] * $c);
				$vychet_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['sum_all_pay'] * $c);
				$vychet_kolvo_vozvrata_oplata_dop_men = str_replace('.',',', $lead['sum_all_pay']/$lead['price'] * $c);					
			} else if($lead['price'] != 0) {
				$vychet_vozvrata_predoplata_osn_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_prepay']/$lead['price']) * (1-$c));
				$vychet_kolvo_vozvrata_predoplata_osn_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_prepay']/$lead['price']) / $lead['price'] * (1-$c));
				$vychet_vozvrata_oplata_osn_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_all_pay']/$lead['price']) * (1-$c));
				$vychet_kolvo_vozvrata_oplata_osn_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_all_pay']/$lead['price']) / $lead['price'] * (1-$c));
				$vychet_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_prepay']/$lead['price']) * $c);
				$vychet_kolvo_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_prepay']/$lead['price']) / $lead['price'] * $c);
				$vychet_vozvrata_predoplata_dop_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_all_pay']/$lead['price']) * $c);
				$vychet_kolvo_vozvrata_oplata_dop_men = str_replace('.',',', $lead['vychet_vozvrata'] * ($lead['sum_all_pay']/$lead['price']) / $lead['price'] * $c);									
			}
			
			$prib_vycheta_vozvrata_osn_men = "";
			$prib_vycheta_vozvrata_dop_men = "";
			if($lead['vychet_vozvrata'] > 0 and ($lead['price'] > $lead['vychet_vozvrata']) and $lead['price'] > 0) {
				$prib_vycheta_vozvrata_osn_men = $prib * $lead['vychet_vozvrata']/$lead['price'] * (1-$c);
				$prib_vycheta_vozvrata_dop_men = $prib * $lead['vychet_vozvrata']/$lead['price'] * $c;
			} else if($lead['vychet_vozvrata'] > 0 and ($lead['price'] <= $lead['vychet_vozvrata']) and $lead['price'] > 0) {
				$prib_vycheta_vozvrata_osn_men = $prib * (1-$c);
				$prib_vycheta_vozvrata_dop_men = $prib * $c;
			}

			$date_managers_deduction = ($lead['date_managers_deduction'] == "00.00.0000") ? null : $lead['date_managers_deduction'];
			$sum_main_manager_dedction = str_replace('.',',',$lead['sum_managers_deduction'] * (1-$c));
			$sum_dop_manager_dedction = str_replace('.',',',$lead['sum_managers_deduction'] * $c);
			
			$sum_return_main = $lead['sum_return']*(1-$c) - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*(1-$c));
			$sum_return_dop = $lead['sum_return']*$c - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*$c);
			$return_minus_base_main = ($sum_return_main < 0) ? $sum_return_main : 0;
			$return_minus_base_dop = ($sum_return_dop < 0) ? $sum_return_dop : 0;
			$sum_return_2_main = $lead['sum_return2']*(1-$c) + $return_minus_base_main;
			$sum_return_2_dop = $lead['sum_return2']*$c + $return_minus_base_dop;

			array_push($outputleadslist, 
			array(
				$lead["id"],
				$lead['date_create'],
				$lead['rej_reasion'],
				$lead['responsible_user'],
				($lead['date_prepay'] == "00.00.0000") ? null : $lead['date_prepay'],
				($lead['sum_prepay'] == "") ? 0 : str_replace('.',',',($lead['sum_prepay']*(1-$c) - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_prepay']/$lead['price'])))),
				($lead['date_all_pay'] == "00.00.0000") ? null : $lead['date_all_pay'],
				//!
				($lead['sum_all_pay'] == "") ? 0 : str_replace('.',',',($lead['sum_all_pay']*(1-$c) - (($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_all_pay']/$lead['price'])*(1-$c)))),
				$lead['date_contract'],
				($lead['price'] == 0) ? "" : str_replace('.',',',($lead['sum_prepay']*(1-$c))/$lead['price']),
				($lead['price'] == 0) ? "" : str_replace('.',',',($lead['sum_all_pay']*(1-$c))/$lead['price']),
				$dopmanagername,
				
				($dopmanagername != "") ? str_replace('.',',',$prib*$c) : "" ,
				($a != 0 or $b != 0) ? str_replace('.',',',$prib*(1-$c)) : "",
				
	/*			($dopmanagername != "") ? str_replace('.',',',($lead['price']-$lead['sum_banketa']-$lead['sebestoimost']-round($lead['transfer']*0.8))*(($a+$b)/$lead['price'])*$c) : "" ,
				($a != 0 or $b != 0) ? str_replace('.',',',(($lead['price']-$lead['sum_banketa']-$lead['sebestoimost']-round($lead['transfer']*0.8))*(($a+$b)/($lead['price']-$lead['sum_banketa']))*(($lead['price']-$lead['sumdopmanager'])/$lead['price']))) : "",
	*/			($lead['sum_prepay'] == "") ? 0 : str_replace('.',',',$lead['sum_prepay']*$c),
				($lead['sum_all_pay'] == "") ? 0 : str_replace('.',',',$lead['sum_all_pay']*$c-(($lead['sum_banketa'] + $lead['sum_novogod_program'])*($lead['sum_all_pay']/$lead['price'])*$c)),
				($lead['price'] == 0) ? "" : str_replace('.',',',($lead['sum_prepay']*$c)/$lead['price']),
				($lead['price'] == 0) ? "" : str_replace('.',',',($lead['sum_all_pay']*$c)/$lead['price']),
				$lead['type_return'],
				$lead['date_return'],
				$sum_return_main > 0 ? str_replace('.',',',$sum_return_main) : '0',
				$sum_return_dop > 0 ? str_replace('.',',',$sum_return_dop) : '0',
				($lead['price'] == 0) ? "" : round($lead['sum_return']/$lead['price']*(1-$c),2),
				($lead['price'] == 0) ? "" : round($lead['sum_return']/$lead['price']*$c,2),
				str_replace('.',',', $pribVozvr*(1-$c) ),
				str_replace('.',',', $pribVozvr*$c ),
				str_replace('.',',', $lead['ne_uchet_v_op']*(1-$c)),
				str_replace('.',',', $lead['ne_uchet_v_op']*$c),
				$lead['ne_uchet_v_op'] == 0 ? 0 : ($lead['ne_uchet_v_op']/$lead['price'])*(1-$c),
				$lead['ne_uchet_v_op'] == 0 ? 0 : ($lead['ne_uchet_v_op']/$lead['price'])*$c,
				$lead['date_return2'],
				$sum_return_2_main > 0 ? str_replace('.',',',$sum_return_2_main) : '0',
				$sum_return_2_dop > 0 ? str_replace('.',',',$sum_return_2_dop) : '0',
				($lead['price'] == 0) ? "" : round($lead['sum_return2']/$lead['price']*(1-$c),2),
				($lead['price'] == 0) ? "" : round($lead['sum_return2']/$lead['price']*$c,2),
				str_replace('.',',', $pribVozvr2*(1-$c) ),
				str_replace('.',',', $pribVozvr2*$c ),
				$vychet_vozvrata_predoplata_osn_men,
				$vychet_vozvrata_oplata_osn_men,
				$vychet_vozvrata_predoplata_dop_men,
				$vychet_vozvrata_oplata_dop_men,
				$vychet_kolvo_vozvrata_predoplata_osn_men,
				$vychet_kolvo_vozvrata_oplata_osn_men,
				$vychet_kolvo_vozvrata_predoplata_dop_men,
				$vychet_kolvo_vozvrata_oplata_dop_men,
				$prib_vycheta_vozvrata_osn_men,
				$prib_vycheta_vozvrata_dop_men,
				$date_managers_deduction,
				$sum_main_manager_dedction,
				$sum_dop_manager_dedction
	/*			($lead['price'] == 0 or $lead['sebestoimost'] == 0) ? "" : str_replace('.',',',1-($lead['sebestoimost']/$lead['price'])),
				($a == 0 and $b==0) ? 0 : $a+$b */
			));
	}
}
//vardump($outputleadslist);
print_r(json_encode($outputleadslist));
?>