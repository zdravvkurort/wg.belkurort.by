<?php
	function converterValut($guest_price, $guest_valuta, $dog_valuta, $valutes) {
		if($guest_valuta == $dog_valuta) {
			return floatval($guest_price);
		}
		
		$para = $guest_valuta.$dog_valuta;
		$kurs = $valutes[$para];
		
		if($kurs) {
		$quantity = ($para == "BYNRUB" or $para == "RUBBYN") ? 100 : 1 ;
		
			if(	$para == "BYNRUB" or 
					$para == "BYNEUR" or 
					$para == "BYNUSD" or 
					$para == "RUBEUR" or
					$para == "RUBUSD" or
					$para == "EURUSD") {
				$result = $guest_price * $quantity / $kurs ;
			} else {
				$result = $guest_price * $kurs / $quantity;
			}
		}
		return round($result);
	};
?>