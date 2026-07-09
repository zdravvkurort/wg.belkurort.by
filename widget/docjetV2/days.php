<?php


// if (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Ислочь") !== false) {
//   $outdate = "2025-12-25";
//   if($data['data_vyezda'] >= $outdate and strpos($data['sutki_dni'], "дн") !== false) {
//     $data['kolichestvo_dney'] = $data['kolichestvo_dney']-1;
// 	if ($data['kolichestvo_dney'] == 1) {
// 	    $data['sutki_dni'] = 'сутки';
// 	}
// 	if ($data['kolichestvo_dney'] != 1) {
// 	    $data['sutki_dni'] = 'суток';
// 	}
//   }
// }


function changeTimeInOut($data) {
    if (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Ислочь парк") !== false or strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Ислочь-Парк") !== false) {
        $outdate = "2026-01-01";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд с 14.00, выезд до 12.00';
        }
    } elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Ислочь") !== false) {
        $outdate = "2025-12-25";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд в первый день путёвки с: 12:00, выезд в последний день путёвки до: 10:00';
        }

  /*
 https://zdravkyrort.amocrm.ru/leads/detail/28088538 поменяйте пожалуйста тут время заезда, заезд в первый день путёвки с: 08:00
выезд в последний день путёвки до: 20:00

поменяйте пожалуйста, срочно надо отправить договор
  */
  if (strpos($data['nomer_dogovora'], "66576/1025") !== false) {
	if($data['data_vyezda'] >= $outdate) {
		$data['dog_chasy_zaezda_vyezda'] = 'заезд в первый день путёвки с: 08:00, выезд в последний день путёвки до: 20:00';
	}
  }


    } elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Лесное") !== false) {
        $outdate = "2025-12-25";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд с 12.00 первого дня путевки, выезд до 10.00 последнего дня путевки';
        }
    } elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Приднепровский") !== false) {
        $outdate = "2025-12-25";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд с 21:00 накануне первого дня путевки (первая услуга «завтрак»), выезд до 18:00 последнего дня путевки (последняя услуга «ужин»)';
        }
    } elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Лётцы") !== false or strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Летцы") !== false) {
        $outdate = "2025-09-18";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд с 8:00 (первая услуга «завтрак»), выезд до 7:30 (последняя услуга «ужин» накануне даты выезда';
        }
    } elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Чаборок") !== false) {
        $outdate = "2025-12-25";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'в день приезда с 12.00, в день отъезда до 10.00';
        }
} elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Ружанский") !== false) {
  $outdate = "2026-01-09";
  if($data['data_vyezda'] >= $outdate) {
    $data['dog_chasy_zaezda_vyezda'] = 'заезд в первый день путевки с 12:00, отъезд в последний день путевки до 10:00';
  }
} elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Золотые пески") !== false) {
        $outdate = "2026-01-01";
        if($data['data_vyezda'] >= $outdate) {
            $data['dog_chasy_zaezda_vyezda'] = 'заезд в первый день путёвки с: 12:00, выезд в последний день путёвки до: 10:00';
        }
} elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Зеленый бор") !== false) {
  $outdate = "2026-01-01";
  if($data['data_vyezda'] >= $outdate) {
    $data['dog_chasy_zaezda_vyezda'] = 'заезд впервый день с 16.00 (ужин), выезд в последний день до 14.00 (обед)';
  }
} elseif (strpos($data['dog_naimenovanie_obekta_razmescheniya'], "Солнечный") !== false) {
  $outdate = "2026-01-01";
  if($data['data_vyezda'] >= $outdate) {
    $data['dog_chasy_zaezda_vyezda'] = 'заезд в первый день путёвки с: 08:30, выезд в последний день путёвки до: 20:00';
  }
}

}
?>