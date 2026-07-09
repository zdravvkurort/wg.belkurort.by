<?php 

require "../db_login.php";
require "../functions.php";

$datestart = strtotime($_REQUEST["startdate"]); //стартовая дата (нужно получить её из запроса)
$enddate = strtotime($_REQUEST["enddate"]);

$stmt = $db->query('
SELECT 
	`id`, 
	`name`, 
	`305287` as `dogdate`, 
	`318631` as `costsan`, 
	`305361` as `dateprepay`, 
	`305359` as `sumprepay`, 
	`305367` as `dateallpay`, 
	`305363` as `sumallpay`, 
	`370935` as `datereturn`, 
	`370933` as `sumreturn`, 
	`381913` as `datereturn2`, 
	`381911` as `sumreturn2`,
	`305333` as `valdog`
FROM `leads` 
where 
	(`370933` > 0 and `370935` >= FROM_UNIXTIME('.$datestart.') and `370935` <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272) or 
	(`381911` > 0 and `381913` >= FROM_UNIXTIME('.$datestart.') and `381913` <= FROM_UNIXTIME('.($enddate+86399).') and leads.pipeline_id = 1736272)' );
$leadList = $stmt->fetchAll();


$stmt = $db->query('SELECT DATE_FORMAT(`created_at`,"%d.%m.%Y") as "date", data, max(`created_at`)
					FROM `currency_quotes`
					group by DATE_FORMAT(`created_at`,"%d.%m.%Y")');
$currency = $stmt->fetchAll();

$valutes = [
"рос. руб." => "RUB",
"бел. руб." => "BYN",
"евро" => "EUR",
"дол. США" => "USD"
];

$outputleadslist = [];

foreach($leadList as $lead) {
			$valuta = $valutes[$lead["valdog"]];
			$dogdate = dateBeautifier($lead["dogdate"]);
			$dateprepay = dateBeautifier($lead["dateprepay"]);
			$dateallpay = dateBeautifier($lead["dateallpay"]);
			$datereturn = dateBeautifier($lead["datereturn"]);
			$datereturn2 = dateBeautifier($lead["datereturn2"]);
			
			array_push($outputleadslist, 
				array(
					$lead["id"],
					$lead["name"],
					$dogdate,
					$valuta,
					converterInBYN($lead["costsan"], $valuta, $dateallpay),
					str_replace('.',',',($lead["costsan"])),
					$dateprepay,
					($valuta == "BYN") ? converterInBYN($lead["sumprepay"], $valuta, $dogdate) : converterInBYN($lead["sumprepay"], $valuta, $dateprepay),
					($valuta == "BYN" or $lead["sumprepay"] == 0) ? '' : $lead["sumprepay"].' '.$valuta,
					$dateallpay,
					($valuta == "BYN") ? converterInBYN($lead["sumallpay"], $valuta, $dogdate) : converterInBYN($lead["sumallpay"], $valuta, $dateallpay),
					($valuta == "BYN" or $lead["sumallpay"] == 0) ? '' : $lead["sumallpay"].' '.$valuta,
					$datereturn,
					converterInBYN($lead["sumreturn"], $valuta, $datereturn),
					$datereturn2,
					converterInBYN($lead["sumreturn2"], $valuta, $datereturn2),
				)
			);
}
//vardump($outputleadslist);
print_r(json_encode($outputleadslist));

function converterInBYN($money, $valuta, $date) {
	$result = '';
	$curs = findCoursesOnDate($date);
	if($curs) {
		$targetcurs = findTargetValuta($curs, "RUB");
		if($targetcurs) {
			$result = $money * $targetcurs->Cur_OfficialRate / $targetcurs->Cur_Scale;
		}
	}
	return $result;
}

function findTargetValuta($curs, $valuta) {
	foreach($curs as $c) {
		if($c->Cur_Abbreviation == $valuta) {
			return $c;
		}
	}
	return false;
}

function findCoursesOnDate($date) {
		global $currency;
		foreach($currency as $curs) {
			if($curs["date"] == $date) {
				return json_decode($curs["data"]);
			}
		}
		return false;
}

function dateBeautifier($date) {
	return (strpos($date, "0000-00-00") === false and $date != "") ? date('d.m.Y', strtotime($date)) : "";
}
?>