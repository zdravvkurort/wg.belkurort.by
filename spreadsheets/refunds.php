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
	`leads`.`398358` as `date_send_annul`,
    lltable.sanatoriy2 as `sanatoriy`,
  IF(`leads`.`name`="", lltable.name2, `leads`.`name`) as `fio`,
    lltable.price2 as `price`,
  `leads`.`305355` as `bill_link`,
  `leads`.`398360` as `bill_summ_byn`,
  `leads`.`398362` as `bill_summ_rub`,
  `leads`.`398364` as `date_san_returned`,
  `leads`.`398366` as `byn_san_returned`,
  `leads`.`398368` as `rub_san_returned`,
  `leads`.`398370` as `dolg_percent`,
  `leads`.`370935` as `datereturn`, 
	`leads`.`370933` as `sumreturn`, 
	`leads`.`381913` as `datereturn2`, 
	`leads`.`381911` as `sumreturn2`,
	`leads`.`398678` as `comment`,
  `leads`.`status_id` as `linked_lead_status_id`
FROM `leads`
INNER JOIN ( 
    SELECT 
    leads.id as llid, 
    leads.pipeline_id as pipeline, 
	`leads`.`398358` as date_send_annul2,
    `leads`.`305089` as sanatoriy2,
	`leads`.`price` as price2,
	`leads`.`name` as name2,
    leads.status_id as llstatusid 
    from leads 
) lltable on lltable.llid = REPLACE(REPLACE(`leads`.`398102`, "[", ""), "]","")
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
where 
    `leads`.`pipeline_id` = 3836187 and 
    `leads`.`398358` != "" and
		lltable.llstatusid = 142 and
    `leads`.`status_id` != 142 and
    `leads`.`status_id` in (36911073, 36911076, 36911079, 36911115)
	' );
$leadList = $stmt->fetchAll();

$outputleadslist = [];
$number = 1;

foreach($leadList as $lead) {
	$vozvratFL = $lead["sumreturn2"] + $lead["sumreturn"];
			array_push($outputleadslist, 
				array(
					$number,
					dateBeautifier($lead["date_send_annul"]),
					$lead["sanatoriy"],
					$lead["fio"],
					str_replace('.',',',$lead["price"]),
					$lead["comment"],
					$lead["bill_link"],
					str_replace('.',',',$lead["bill_summ_byn"]),
					str_replace('.',',',$lead["bill_summ_rub"]),
					dateBeautifier($lead["date_san_returned"]),
					str_replace('.',',',$lead["byn_san_returned"]),
					str_replace('.',',',$lead["rub_san_returned"]),
					($lead["bill_summ_byn"] != "") ? (int)$lead["bill_summ_byn"]-(int)$lead["byn_san_returned"] : (int)$lead["bill_summ_rub"] - (int)$lead["rub_san_returned"],
					$lead["price"]-($lead["price"] * str_replace(',','.',$lead["dolg_percent"])/100),
					($lead["sumreturn2"] > $lead["sumreturn"]) ? dateBeautifier($lead["datereturn2"]) : dateBeautifier($lead["datereturn"]),
					str_replace('.',',',$vozvratFL),
					str_replace('.',',',$lead["price"] - $vozvratFL),
					($lead["price"] != 0) ? str_replace('.',',',(1-($vozvratFL/$lead["price"]))*100)."%" : "",
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