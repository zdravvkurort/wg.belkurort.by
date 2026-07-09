<?php

	require_once "../db_login.php";
	require_once "../functions.php";
	require_once 'functions.php';
	
	$stmt = $db->query("SELECT MAX(Date) FROM `m_ad_costs` WHERE Channel like 'Таргетинг ВК'");
	$max_date = $stmt->fetchAll()[0][0];
	
$datenow = date('Y-m-d');

$date_start = ($max_date < date('Y-m-d', strtotime($datenow. ' - 3 days'))) ? $max_date : date('Y-m-d', strtotime($datenow. ' - 3 days'));
//$date_start = date('Y-m-d', strtotime("2020-12-01"));
		$method = 'ads.getStatistics';
		$token = 'vk1.a.g74cUB7AseZdDsQg0yXRxYkDF2xb6cWSnjiS8FXfN3jKHJtMRWSowplyQWnXBLOz1aBH6LV-nSf9ItHgMKRUTQUNQLfsd55q5NnCh7CqGoha-SAhLsn9gCL3Iorcuz38WlwFfGXST3ff7Ffe9AUYy6PG_yaVGZ2cvZuOovGn-YI86gw56nGWL1RgQX93t-Bf';
		$version = '5.95';

		$params = http_build_query([
            'account_id' => '1900013579',
            'ids_type'    => 'office',
            'ids'     => '1900013579',
						'period' => 'day',
						'date_from' => $date_start,
						'date_to' => '0',
						'v' => $version
		]);

		$url = "https://api.vk.ru/method/{$method}?{$params}&access_token={$token}&v={$version}";
		$stats = json_decode(file_get_contents($url), true);
		$stats=$stats["response"][0]["stats"];

		if(count($stats)) {
			foreach($stats as $item => $v) {
				if(!is_array($v)) {
					unset($stats[$item]);
				} else {
					foreach($v as $key => $val) {
						$stats[$item][ucfirst($key)] = $val;
						unset($stats[$item][$key]);
						}
					$stats[$item] = change_attr($stats[$item],'Day','Date');
					$stats[$item] = change_attr($stats[$item],'Spent','Cost');
					$stats[$item]['Cost'] = $stats[$item]['Cost'];
					$stats[$item]['Site'] = "Leads Форма";
					unset($stats[$item]["Join_rate"]);
				}
			}
			$koef = [['from' => '0000-00-00', 'koef' => 1.2], ['from' => '2020-12-08', 'koef' => 1.01], ['from' => '2022-04-01', 'koef' => 1.02], ['from' => '2022-08-01', 'koef' => 1.13]];
			$stats = koefCalc($stats, $koef);
			m_set_costs_in_db($stats, 'Таргетинг ВК');
		}

?>