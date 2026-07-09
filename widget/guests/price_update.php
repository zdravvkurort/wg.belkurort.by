<?php 
require "../../functions.php";
require "../../db_login.php";

//подключаем amoCRM
require "../../auth.php";
cors();
require_once("../convertValutFunction.php");
if($_SERVER["REQUEST_METHOD"]=="POST" and $_POST['hash'] == "3e4a93d9c8e250ffe0bf43ad4ccb52ba"){
	try {
		$final_price = 0;
		$final_arr = array();
		
		$valutes = [
			"437777" => "RUB",
			"437779" => "BYN",
			"524825" => "USD",
			"524827" => "EUR",
		];
		
		$sitizens = [
			"Азербайджан" => "RUB",
			"Армения" => "USD",
			"Германия" => "EUR",
			"Грузия" => "USD",
			"Израиль" => "USD",
			"Ирак" => "USD",
			"Ирландия" => "USD",
			"Испания" => "EUR",
			"Казахстан" => "RUB",
			"КНР" => "USD",
			"Латвия" => "EUR",
			"Литва" => "EUR",
			"Молдова" => "USD",
			"Нигерия" => "USD",
			"Нидерланды" => "USD",
			"Польша" => "USD",
			"РБ" => "BYN",
			"РФ" => "RUB",
			"США" => "USD",
			"Таджикистан" => "USD",
			"Туркмения" => "USD",
			"Турция" => "USD",
			"Узбекистан" => "RUB",
			"Украина" => "EUR",
			"Франция" => "EUR",
			"Филиппины" => "USD",
			"Швейцария" => "EUR",
			"Швеция" => "EUR",
			"Эстония" => "EUR",	
			"Вид на жительство" => "RUB",
		];
		
		$lead_id = (int)$_POST['lead_id'];
		$dog_valuta = $valutes[$_POST['valuta_dog']];
		$dog_date = $_POST['date_dog'] ? date('Y-m-d H:i:s',strtotime($_POST['date_dog'])) : date("Y-m-d H:i:s");
		
		//получили гостей
			$stmt = $db->query("SELECT `lead_to_guest`.`lead_id` as `lead_id`,
										`lead_to_guest`.`guest_id` as `guest_id`,
										`lead_to_guest`.`checkguest` as `checkguest`,
										guests.price as `guest_price`, 
										guests.valuta_price as `guest_valuta`,
										guests.sitizen as `guest_sitizen`
								FROM `lead_to_guest`
								inner join `guests`
								ON `lead_to_guest`.`guest_id` = `guests`.`id`
								where `lead_to_guest`.`lead_id` =".$lead_id."
								ORDER BY `guests`.`id`");
			$guests = $stmt->fetchAll();
		/*
		//получили сделку
		$lead_info = $amo->lead->apiList([
				'id' => $lead_id,
			])[0];
		*/
		
		//получаем внутренний курс валют
		$valutes = getInnerCursesFromDB($dog_date);
		$coursesNBRB = findCursFromNBRB($dog_date);

		if(strtotime($dog_date)>=strtotime("2020-07-16 00:00:00")) {
			foreach($guests as $guest) {
				//Если Валюта договора не задана, берём валюту из первого гостя, на которого составляется договор
				if($dog_valuta == null) {
					$dog_valuta = ($sitizens[$guest['guest_sitizen']]) ? $sitizens[$guest['guest_sitizen']] : "USD";
				}
				
				if($guest["guest_price"] != 0 and $guest["checkguest"] == 1) {
					//Если валюты совпадают, то просто плюсуем в результат
					if($guest["guest_valuta"] == $dog_valuta) {
						$final_price += $guest["guest_price"];
						//vardump($guest["guest_price"].$guest["guest_valuta"]." = ".$guest["guest_price"].$guest["guest_valuta"]." по курсу 1");
					} else {
					//Если не совпадают, конвертим в валюту договора и плюсуем в результат
						$final_price += converterValut($guest["guest_price"], $guest["guest_valuta"], $dog_valuta, $valutes, $coursesNBRB);
					}
				}
			}
			$final_price = round($final_price);
			if($final_price == 0) {
				print_r(json_encode(["message" => "Сумма равна нулю"]));
			} else {
				//vardump("Общая сумма по сделке ".$final_price.$dog_valuta);	
				$final_arr["price_in_dog_valuta"] = $final_price;
				$final_arr["dog_valuta"] = $dog_valuta;
				// $coursesNBRB = findCursFromNBRB($dog_date);
				
				if($dog_valuta != "RUB") {
					if($dog_valuta != "BYN") {
						$c = find_kurs($dog_valuta, $coursesNBRB);
						$final_price = $final_price * $c["Cur_OfficialRate"];
						//Берём валюту переводим в бел руб
					}
					$crr = find_kurs("RUB", $coursesNBRB);
					
						//vardump($coursesNBRB);
						$final_price = $final_price * $crr["Cur_Scale"] / $crr["Cur_OfficialRate"];
						//Берём бел рубли и переводим в рос руб по курсу НБРБ
				}
		
				$final_price = round($final_price);
				$final_arr["final_price"] = $final_price;
				print_r(json_encode($final_arr));
				//vardump("В сделку запишем ".round($final_price)."RUB");
			}
		} else {
			print_r(json_encode(["final_price" => 0]));
		}
	} catch (Exception $e) {
			print_r(json_encode(["message" => $e->getMessage()]));
	}
	
}
	// function converterValut($guest_price, $guest_valuta, $dog_valuta, $valutes, $coursesNBRB) {
	// 	$para = $guest_valuta.$dog_valuta;
	// 	//Получаем курсы валют из таблицы Насти на дату договора
	// 	$kurs = $valutes[$para];
		
	// 	if($kurs) {
	// 		$quantity = ($para == "BYNRUB" or $para == "RUBBYN") ? 100 : 1 ;
		
	// 		if($para == "RUBBYN") {
	// 			$kursNBRB = find_kurs("RUB", $coursesNBRB);
	// 			$result = $guest_price * $kursNBRB["Cur_OfficialRate"] / $kursNBRB["Cur_Scale"];
	// 		} else if($para == "BYNRUB" or 
	// 			$para == "BYNEUR" or 
	// 			$para == "BYNUSD" or 
	// 			$para == "RUBEUR" or
	// 			$para == "RUBUSD" or
	// 			$para == "EURUSD") {
	// 				$result = $guest_price * $quantity / $kurs ;
	// 		} else {
	// 				$result = $guest_price * $kurs / $quantity;
	// 		}
	// 	}
	// 	//vardump($guest_price.$guest_valuta." = ".$result.$dog_valuta." по курсу ".$kurs);
	// 	return $result;
	// }
	function getActualValutes($date) {
		// инициализируем cURL
		$ch = curl_init();
		// устанавливаем url, с которого будем получать данные
		curl_setopt($ch, CURLOPT_URL, 'https://script.google.com/macros/s/AKfycbwacM6mwmHs7CE2szz05_P5DgESKVUFbxtTFAK8BBc60i10nJ2x/dev');
		// устанавливаем опцию, чтобы содержимое вернулось нам в string
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "date=".$date);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// выполняем запрос
		$output = curl_exec($ch);
		// закрываем cURL
		curl_close($ch);
		return json_decode($output,true);
	}
	
	function findCursFromNBRB($date) {
		global $db;
		$tommorow = date('Y-m-d H:i:s',strtotime($date) + (24*60*60) - 1);
		$st = $db->query("SELECT data FROM `currency_quotes` where `created_at` < STR_TO_DATE('".$tommorow."', '%Y-%m-%d %H:%i:%s') ORDER BY id desc limit 10");
		$currency = $st->fetchAll()[0];
		
		$currency = json_decode($currency['data'], true);	
		return $currency;	
	}

	function find_kurs($cur, $currency) {
		foreach($currency as $val) {
			if($val["Cur_Abbreviation"] == $cur) {
				return $val;
			}
		}
	}
	
	function getInnerCursesFromDB($date) {
		global $db;
		$st = $db->query("SELECT * FROM inner_courses ORDER BY date ASC");
		$currencies = $st->fetchAll();
		$result = [
			"BYNRUB" => 0,
			"RUBBYN" => 0,
			"BYNEUR" => 0,
			"EURBYN" => 0,
			"BYNUSD" => 0,
			"USDBYN" => 0,
			"RUBEUR" => 0,
			"EURRUB" => 0,
			"RUBUSD" => 0,
			"USDRUB" => 0,
			"EURUSD" => 0,
			"USDEUR" => 0,
		];

		foreach($currencies as $currency) {
			if(strtotime($date)>=strtotime($currency["date"])) {
				foreach($result as $key => $value) {
					if($currency[$key] != 0) {
						$result[$key] = $currency[$key];
					}
				};	
			}
		};
		
		return $result;	
	}
	
?>