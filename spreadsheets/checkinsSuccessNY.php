<?php 

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'error' => 'Only GET requests are allowed'
    ]);
    exit;
}

// Проверка заголовка Authorization
$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
if (empty($authHeader) || substr($authHeader, 0, 6) !== 'Bearer') {
    http_response_code(401); // Unauthorized
    echo json_encode([
        'error' => 'Invalid or missing Authorization header'
    ]);
    exit;
}

// Получение токена из заголовка
$token = substr($authHeader, 7); // Удаляем 'Bearer ' из начала

if($token != "WztFNDgab7WUko1ycICtnbHXi4l1KiV1VdJ9TPPBmuYjxpsr") {
	http_response_code(401); // Unauthorized
    echo json_encode([
        'error' => 'Invalid or missing Authorization header'
    ]);
    exit;
}

require "../db_login.php";
require "../functions.php";

$stmt = $db->query('
SELECT 
  IF(`leads`.`305299`="", `pod`.fio, `leads`.`305299`) as `fio`,
  `leads`.`305089` as `sanatoriy`,
  `leads`.`398358` as `date_send_annul`,
  `leads`.`305355` as `bill_link`,
  `leads`.`price` as `price`,
  `leads`.`398360` as `bill_summ_byn`,
  `leads`.`398362` as `bill_summ_rub`,  
  `leads`.`398678` as `comment`,


	`leads`.`305195` as `kol_otdih`,
	`leads`.`305203` as `date_in`,
	`leads`.`305205` as `date_out`,
	`leads`.`305333` as `grazhd`,
	`leads`.`305341` as `share_manager`,
	`leads`.`305369` as `oplata_po_schetu`,
	`leads`.`305339` as `plan_date_pay`,

	`leads`.`371365` as `name_id`,


`users`.`name` as `name`,
`statuses`.`name` as `Статус`,
	`leads`.`362303` as `sum_banketa`,
	IF(leads.377797="", pod.sitizen, leads.377797) as `sitizen_dog`,

	`leads`.`378075` as `s_count`,
	`leads`.`313921` as `s_nummer`,

  lltable.llstatusid as `linked_lead_status_id`
FROM `leads`
left join (
	select lead_to_guest.lead_id as "id", guests.fio as "fio", guests.sitizen as "sitizen"
	from guests 
    inner join lead_to_guest ON lead_to_guest.guest_id = guests.id 
    where guests.id in (
    	select min(guests.id) 
    	from guests 
    	inner join lead_to_guest ON lead_to_guest.guest_id = guests.id 
    	group by lead_to_guest.lead_id)
    group by lead_to_guest.lead_id
) pod on pod.id=leads.id
 inner Join users ON `leads`.`371365` = users.id
 	inner join statuses ON leads.status_id = statuses.id

INNER JOIN ( SELECT leads.id as llid, leads.status_id as llstatusid, 
  YEAR(`leads`.`305203`) as year_in, YEAR(`leads`.`305205`) as year_out from leads ) lltable on lltable.llid =leads.id
where 
		`lltable`.`llstatusid` = 142
		AND
		`lltable`.`year_in` < `lltable`.`year_out`
		AND 
		`lltable`.`year_in` = 2025
		AND
		`lltable`.`year_out` = 2026
	' );
$leadList = $stmt->fetchAll();

$outputleadslist = [];
$number = 1;

foreach($leadList as $lead) {
			array_push($outputleadslist, 
				array(
					$number,
					$lead["fio"],
					$lead["sanatoriy"],
					dateBeautifier($lead["date_in"]) . ' - ' . dateBeautifier($lead["date_out"]),
					$lead["name"],
					$lead["kol_otdih"],
					$lead["sum_banketa"] > 0 ? "да" : "нет",
					$lead['sitizen_dog'],
					$lead["bill_link"],
					$lead["s_count"],
					$lead["s_nummer"],


					// str_replace('.',',',$lead["price"]),
					// str_replace('.',',',$lead["bill_summ_byn"]),
					// str_replace('.',',',$lead["bill_summ_rub"]),
					// $lead["comment"],
					// $lead["share_manager"],
					// $lead["oplata_po_schetu"],
					// $lead["plan_date_pay"],
					$lead["Статус"],
					// $lead["grazhd"],

					




					$lead["linked_lead_status_id"]
				)
			);
	$number++;
}
print_r(json_encode($outputleadslist));

function dateBeautifier($date) {
	return (strpos($date, "0000-00-00") === false and $date != "") ? date('d.m.Y', strtotime($date)) : "";
}
?>