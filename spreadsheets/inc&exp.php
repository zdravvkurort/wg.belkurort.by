<?php 

require "../db_login.php";
require "../functions.php";

$datestart = strtotime($_REQUEST["startdate"]); //стартовая дата (нужно получить её из запроса)
$enddate = strtotime($_REQUEST["enddate"]);

if(!$datestart or !$enddate) {
	exit;
}

$stmt = $db->query('SELECT id as user_id, name as user_name from users');
$usersAmo = $stmt->fetchAll();

$stmt = $db->query('
SELECT 
	leads.id as `id`,
	IF(leads.305299="", pod.fio, leads.305299) as `fio_dog`,
	leads.price as `budget`,
	leads.305361 as `date_prepay`,
	leads.305359 as `sum_prepay`,
	leads.305367 as `date_all_pay`,
	leads.305363 as `sum_all_pay`,
	leads.318631 as `san_cost`,
	leads.305357 as `komission`,
	leads.305195 as `kol_otdih`,
	leads.305089 as `sanatoriy`,
	leads.305203 as `date_in`,
	leads.305205 as `date_out`,
	leads.305369 as `oplata_po_schetu`,
	leads.305083 as `ist_rekl`,
	leads.313133 as `kol_dney`,
	leads.305333 as `grazhd`,
	users.name as `manager`,
	leads.305339 as `plan_date_pay`,
	leads.305341 as `share_manager`,
	leads.305343 as `share_sum`,
	leads.305091 as `turobsl`,
	leads.305093 as `infousl`,
	leads.339925 as `bookfrom`,
	leads.343217 as `date_pay_in_san`,
	leads.362303 as `sum_banketa`,
	leads.758042 as `sum_novogod_program`,
	leads.305137 as `transfer`,
	leads.305169 as `sum_scheta`,
	leads.370935 as `date_return_money`,
	leads.370933 as `sum_return_money`,
	leads.371141 as `type_return`,
	leads.377319 as `checked`,
	leads.378479 as `ne_uchit_vozvr`,
	leads.393708 as `ne_uchit_vozvr2`,
	leads.381911 as `sum_return_money2`,
	leads.381913 as `date_return_money2`,
	leads.378299 as `annul`,
	leads.384509 as `not_use_at_all`,
	leads.385043 as `fine`,
	leads.378075 as `num_and_date_bill`,
	IF(leads.377797="", pod.sitizen, leads.377797) as `sitizen_dog`

FROM `leads`
	inner join users on leads.responsible_user_id=users.id
	inner join groups on users.group_id=groups.id
left join(
    select lead_to_guest.lead_id as "id", guests.fio as "fio", guests.sitizen as "sitizen"
    from guests 
    inner join lead_to_guest ON lead_to_guest.guest_id = guests.id 
    where guests.id in (
        select min(guests.id) 
        from guests 
        inner join lead_to_guest ON lead_to_guest.guest_id = guests.id 
        group by lead_to_guest.lead_id)
    group by lead_to_guest.lead_id
  )pod on pod.id=leads.id

where 
	(leads.305361 >= FROM_UNIXTIME('.$datestart.') and leads.305361 <= FROM_UNIXTIME('.$enddate.') and leads.status_id IN (142, 26726761) and leads.pipeline_id = 1736272) 
	or 
	(leads.305367 >= FROM_UNIXTIME('.$datestart.') and leads.305367 <= FROM_UNIXTIME('.$enddate.') and leads.status_id IN (142, 26726761) and leads.pipeline_id = 1736272)
	or 
	(leads.370935 >= FROM_UNIXTIME('.$datestart.') and leads.370935 <= FROM_UNIXTIME('.$enddate.') and leads.status_id IN (142, 26726761) and leads.pipeline_id = 1736272)
	or
	(leads.381913 >= FROM_UNIXTIME('.$datestart.') and leads.381913 <= FROM_UNIXTIME('.$enddate.') and leads.status_id IN (142, 26726761) and leads.pipeline_id = 1736272)
');
$leadslist = $stmt->fetchAll();
$outputleadslist = [];

foreach($leadslist as $lead) {
	if($lead['not_use_at_all'] != "1") {	
			if($lead['ne_uchit_vozvr'] == "1") {
				$lead['type_return'] = "";
				$lead['date_return_money'] = "";
				$lead['sum_return_money'] = "";
			}
			if($lead['ne_uchit_vozvr2'] == "1") {
				$lead['date_return_money2'] = "";
				$lead['sum_return_money2'] = "";
			}
			//вычитаем штрафы
			$lead['sum_all_pay'] = $lead['sum_all_pay'] - $lead['fine'];
			$lead['budget'] = $lead['budget'] - $lead['fine'];
			$lead['san_cost'] = $lead['san_cost'] - $lead['fine'];
			
			$date_prepay = ($lead['date_prepay'] == "0000-00-00") ? "" : $lead['date_prepay'];
			$date_allpay = ($lead['date_all_pay'] == "0000-00-00") ? "": $lead['date_all_pay'];
			$date_return_money = ($lead['date_return_money'] == "0000-00-00") ? "": $lead['date_return_money'];
			$date_return_money2 = ($lead['date_return_money2'] == "0000-00-00") ? "": $lead['date_return_money2'];
			$costService = $lead['turobsl'] + $lead['infousl']; 
			
			array_push($outputleadslist, 
			array(
			$lead["id"],
			$lead['fio_dog'],
			($lead['budget'] == "") ? 0 : str_replace('.',',',($lead['budget'] - $lead['sum_banketa'] - $lead['sum_novogod_program'])),
			$date_prepay,
			($lead['sum_prepay'] == "" or $lead['budget'] == "") ? 0 : str_replace('.',',',($lead['sum_prepay'] - $lead['sum_prepay']*(($lead['sum_banketa'] + $lead['sum_novogod_program'])/$lead['budget']))),
			$date_allpay,
			($lead['sum_all_pay'] == "" or $lead['budget'] == "") ? 0 : str_replace('.',',',($lead['sum_all_pay'] - ($lead['sum_all_pay']*(($lead['sum_banketa'] + $lead['sum_novogod_program'])/$lead['budget'])))),
//			str_replace('.',',',$lead['cf438795']),
			($lead['san_cost'] == "") ? 0 : str_replace('.',',',$lead['san_cost']+round($lead['transfer']*0.8)),
			($lead['komission'] == "") ? 0 : str_replace('.',',',$lead['komission']),
			$lead['kol_otdih'],
			0,
			$lead['sanatoriy'],
			$lead['date_in'],
			($lead['annul'] == 1) ? 1 : $lead['oplata_po_schetu'],
			$lead['ist_rekl'],
			($lead['kol_dney'] == "") ? 0 : $lead['kol_dney'],
			$lead['grazhd'],
			"",
			$lead['manager'],
			$lead['plan_date_pay'],
			"",
			"",
			"",
			"",
			searchuserbyid ($lead['share_manager']),
			str_replace('.',',',$lead['share_sum']),
			$costService,
			$lead['bookfrom'],
			$lead['date_pay_in_san'],
			str_replace('.',',',$lead['transfer']),
			str_replace('.',',',$lead['sum_scheta']),
			str_replace('.',',',$lead['san_cost']),
			str_replace('.',',',$lead['sum_banketa'] + $lead['sum_novogod_program']),
			$date_return_money,
			str_replace('.',',',$lead['sum_return_money']*(-1)),
			$lead['type_return'],
			($lead['checked']) ? "Проверено" : "Не проверено",
			$lead['sitizen_dog'],
			$date_return_money2,
			str_replace('.',',',$lead['sum_return_money2']*(-1)),
			$lead['num_and_date_bill'],	
			$lead['date_out']
			));
	}
}
//vardump($outputleadslist);
print_r(json_encode($outputleadslist));


function findcurrency($findVal) {
	$currency = json_decode(file_get_contents("http://www.nbrb.by/API/ExRates/Rates?Periodicity=0"),true);
	foreach($currency as $cur) {
		if($findVal == $cur["Cur_Abbreviation"]) {
			return $cur["Cur_OfficialRate"] / $cur["Cur_Scale"];
		}
	}
	return "Ошибка";
}
?>