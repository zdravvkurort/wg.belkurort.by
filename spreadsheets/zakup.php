<?php 
require "../amo_login_zakup.php";
require "../functions.php";


$leadslist = get_leads_list(0);
$outputleadslist = [];
foreach($leadslist as $lead) {
	if($_REQUEST["type"] == "1") {
			array_push($outputleadslist, 
			array(
			$lead['name'],
			findcustomfieldval($lead,357375),
			findcustomfieldval($lead,357411)." ".findcustomfieldval($lead,359027),
			(findcustomfieldval($lead,357413)==1) ? "Есть" : "Нет",
			findcustomfieldval($lead,359155),
			findcustomfieldval($lead,358933)." ".findcustomfieldval($lead,358939),
			findcustomfieldval($lead,359205)." ".findcustomfieldval($lead,359209),
			(findcustomfieldval($lead,358941)==1) ? "Есть" : "Нет",
			findcustomfieldval($lead,359221),
			findcustomfieldval($lead,359015)." ".findcustomfieldval($lead,359019),
			findcustomfieldval($lead,359223),
			findcustomfieldval($lead,415613),
			findcustomfieldval($lead,357637),
			findcustomfieldval($lead,357625),
			findcustomfieldval($lead,357641),
			findcustomfieldval($lead,357645),
			findcustomfieldval($lead,357649),
			findcustomfieldval($lead,357653),
			findcustomfieldval($lead,357729),
			findcustomfieldval($lead,357747),
			findcustomfieldval($lead,415287),
			findcustomfieldval($lead,635944),
			findcustomfieldval($lead,357785),
			findcustomfieldval($lead,357791)
			));	
	} else {
			array_push($outputleadslist, 
			array(
			$lead['name'],
			findcustomfieldval($lead,357375),
			//findcustomfieldval($lead,357411)." ".findcustomfieldval($lead,359027),
			(findcustomfieldval($lead,357413)==1 || findcustomfieldval($lead,358941)==1) ? "Да" : "Нет",
			findcustomfieldval($lead,415613),
			//findcustomfieldval($lead,358933)." ".findcustomfieldval($lead,358939),
			//findcustomfieldval($lead,359205)." ".findcustomfieldval($lead,359209),
			findcustomfieldval($lead,357645),
			findcustomfieldval($lead,357641),
			findcustomfieldval($lead,357653),
			findcustomfieldval($lead,357729),
			findcustomfieldval($lead,357747),
			findcustomfieldval($lead,635944),
			findcustomfieldval($lead,357785)
			));			
	}
}

//vardump($outputleadslist);
print_r(json_encode($outputleadslist));

function get_leads_list($date_start) {
$time_start = date("Y-m-d H:i:s",$date_start);
$array_new_leads = new_leads($time_start,0);
$a = count($array_new_leads);
$list = $array_new_leads;
	while(($a % 500) == 0) {
		$array_new_leads = new_leads($time_start,$a);
		$a = $a+count($array_new_leads); 
		$list = array_merge($array_new_leads,$list);
		sleep(1);
	}
	return $list;
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