<?php

	require_once "../db_login.php";
	require_once "../functions.php";
	require_once 'functions.php';


/*	$filename = 'somefile.txt';
	$text = file_get_contents('php://input');
	//записываем текст в файл
	file_put_contents($filename, $text);
*/

$info = file_get_contents('php://input');
	$info = json_decode($info,1);
	if($info[0]['hash'] == 'asdugjmndbaiurbidfsmdksfogjfsjy882bkf728gkg2287') {
		$datenow = date('Y-m-d');
		$date_start = date('Y-m-d', strtotime($datenow.' - 13 days'));
		
		//Получаем курсы на нужные даты
		$currencyUSD = getCurrencyByCode('USD');
		$currencyUSD = json_decode($currencyUSD,true);

		sleep(2);
		$currencyRUB = getCurrencyByCode('RUB');
		$currencyRUB = json_decode($currencyRUB,true);

		foreach($info as $ind => $value) {
			if($info[$ind]['Cost'] != 0) {
				$info[$ind]['Cost'] = $info[$ind]['Cost'] * find_currency($currencyUSD, $info[$ind]['Date']) * (100/ find_currency($currencyRUB, $info[$ind]['Date']));
				preg_match('/(?<={)[^}]*(?=})/',$info[$ind]["CampaignName"],$matches);
				$info[$ind]["Site"] = $matches[0];
			} else {
				unset($info[$ind]);
			}
		}
		
		$koef = [['from' => '0000-00-00', 'koef' => 1.2], ['from' => '2020-12-08', 'koef' => 1.01], ['from' => '2022-04-01', 'koef' => 1.03], ['from' => '2022-08-01', 'koef' => 1.23]];
		$info = koefCalc($info, $koef);

		array_values($info);
		m_set_costs_in_db($info, 'Google поиск');
	}

	function gc($num) {
		global $db;
		if(!isset($db)) {require_once $_SERVER['DOCUMENT_ROOT']."/db_login.php";}
		$num = (int)$num;
		global $date_start;
		global $datenow;
		$resultArray = [];
		$d = date('Y-m-d', strtotime($datenow. ' +1 DAY'));

		$stmt = $db->query("SELECT DATE_FORMAT(`created_at`,'%d.%m.%Y') as date, `updated_at` as 'MAX(`updated_at`)', `data` 
												FROM `currency_quotes` 
												INNER JOIN (SELECT DATE_FORMAT(`created_at`,'%d.%m.%Y') as cr_at, MAX(`updated_at`) as up_at 
																		FROM `currency_quotes` 
																		GROUP BY cr_at) subtable 
												ON DATE_FORMAT(`currency_quotes`.`created_at`,'%d.%m.%Y') = `subtable`.`cr_at` 
														AND `currency_quotes`.`updated_at` = `subtable`.`up_at` 
												WHERE `created_at` >= STR_TO_DATE('".$date_start."', '%Y-%m-%d') AND `created_at` <= STR_TO_DATE('".$d."', '%Y-%m-%d')");
		$coursesFromDB = $stmt->fetchAll();

		if(count($coursesFromDB)>0) {
			foreach($coursesFromDB as $date) {
				$datas = json_decode($date['data'], true);
				if($datas) {
					foreach($datas as $data) {
						if($data["Cur_ID"] == $num) {
							array_push($resultArray, (object)["Cur_ID" => $data["Cur_ID"], "Date" => $data["Date"], "Cur_OfficialRate" => $data["Cur_OfficialRate"]]);
						}
					}
				}
			}
		}
		return json_encode($resultArray);
	}
?>