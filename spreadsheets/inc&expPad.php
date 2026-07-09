<?php
error_reporting(0);
require "../functions.php";

$datestart = strtotime($_REQUEST["startdate"]); //стартовая дата (нужно получить её из запроса)
$enddate = strtotime($_REQUEST["enddate"]);

if(!$datestart or !$enddate) {
	exit;
}

$incExps = json_decode(file_get_contents("http://wg.belkurort.by/spreadsheets/inc&exp.php?startdate=".date('Y-m-d',$datestart)."&enddate=".date('Y-m-d',$enddate)), true);

$incExps = array_map(function($el) {
  return dotToComma($el, [2, 4, 6, 7, 8, 24, 25, 26, 29, 30, 31, 32, 34, 39]);
}, $incExps);

$prepay = array_filter($incExps, function($el) use ($datestart, $enddate) { 
  return (strtotime($el[3]) >= $datestart and strtotime($el[3]) < $enddate);
});

$prepay = array_map(function($el) {
  $num = 4;
  $base = $el[$num]/$el[2];
  $sebestoimost = $el[7]*$base;
  $sum4FirstMan = round(($el[2]-$el[25])*$base,2);
  $prib = ($el[2]-$el[7])*$base;
  $mainPrib = $prib*(($el[2]-$el[25])/$el[2]);
  return [date('d.m.Y', strtotime($el[3])), 
          $el[0], 
          $el[1], 
          $el[$num], 
          $sebestoimost, 
          $el[8]*$base, 
          $el[29]*$base,
          $el[26]*$base,
          $prib,
          ($el[2]-$el[7])*$el[$num]/$el[2]/$el[$num],
          $el[36],
          ($el[9]+$el[10])*$base,
          $el[10]*$base,
          $el[11],
          'предоплата',
          ($el[23] == 1 or $el[13] == 1) ? 0 : ($sebestoimost - $el[21]),
          date('d.m.Y',strtotime($el[12])),
          ($el[41]) ? date('d.m.Y',strtotime($el[41])) : '',
          date('m.Y',strtotime($el[12])),
          $base,
          $el[14],
          $el[15]*($el[9]+$el[10])*$base,
          $el[16],
          ($el[20] == "") ? "Обычная продажа": $el[20],
          $el[18],
          date('d.m.Y',strtotime($el[19])),
          $el[21],
          ($el[0] == 0) ? '' : '=HYPERLINK("https://zdravkyrort.amocrm.ru/leads/detail/'.$el[0].'";"Перейти в amo >>>>>")',
          $el[24],
          $sum4FirstMan,
          $sum4FirstMan,
          $el[$num]-$sum4FirstMan,
          $mainPrib,
          $prib - $mainPrib,
          $base*($sum4FirstMan/$el[$num]),
          $base*(($el[$num]-$sum4FirstMan)/$el[$num]),
          $el[27],
          ($el[28]) ? date('d.m.Y',strtotime($el[28])) : '',
          $el[30]*$base,
          ($el[31]*$base)+($el[32]*$base),
          $el[37],
          $el[34],
          (string)$el[40]
        ];
}, $prepay);

$allpay = array_filter($incExps, function($el) use ($datestart, $enddate) { 
  return (strtotime($el[5]) >= $datestart and strtotime($el[5]) < $enddate);
});

$allpay = array_map(function($el) {
  $num = 6;
  $base = $el[$num]/$el[2];
  $sebestoimost = $el[7]*$base;
  $sum4FirstMan = round(($el[2]-$el[25])*$base,2);
  $prib = ($el[2]-$el[7])*$base;
  $mainPrib = $prib*(($el[2]-$el[25])/$el[2]);
  return [date('d.m.Y', strtotime($el[5])), 
          $el[0], 
          $el[1], 
          $el[$num], 
          $sebestoimost, 
          $el[8]*$base, 
          $el[29]*$base,
          $el[26]*$base,
          $prib,
          (!!$el[$num]) ? ($el[2]-$el[7])*$el[$num]/$el[2]/$el[$num] : 0,
          $el[36],
          ($el[9]+$el[10])*$base,
          $el[10]*$base,
          $el[11],
          'полная оплата',
          ($el[23] == 1 or $el[13] == 1) ? 0 : ($sebestoimost - 0),
          date('d.m.Y',strtotime($el[12])),
          ($el[41]) ? date('d.m.Y',strtotime($el[41])) : '',
          date('m.Y',strtotime($el[12])),
          $base,
          $el[14],
          $el[15]*($el[9]+$el[10])*$base,
          $el[16],
          ($el[20] == "") ? "Обычная продажа": $el[20],
          $el[18],
          date('d.m.Y',strtotime($el[19])),
          0,
          ($el[0] == 0) ? '' : '=HYPERLINK("https://zdravkyrort.amocrm.ru/leads/detail/'.$el[0].'";"Перейти в amo >>>>>")',
          $el[24],
          $sum4FirstMan,
          $sum4FirstMan,
          $el[$num]-$sum4FirstMan,
          $mainPrib,
          $prib - $mainPrib,
          (!!$el[$num]) ? $base*($sum4FirstMan/$el[$num]) : 0,
          (!!$el[$num]) ? $base*(($el[$num]-$sum4FirstMan)/$el[$num]) : 0,
          $el[27],
          ($el[28]) ? date('d.m.Y',strtotime($el[28])) : '',
          $el[30]*$base,
          ($el[31]*$base)+($el[32]*$base),
          $el[37],
          $el[34],
          (string)$el[40]
        ];
}, $allpay);

$returnOverpayment = array_filter($incExps, function($el) use ($datestart, $enddate) { 
  return (strtotime($el[33]) >= $datestart and strtotime($el[33]) < $enddate and $el[35] === 'Переплата');
});

$returnOverpayment = array_map(function($el) {
  $num = 34;
  $base = $el[$num]/$el[2];
  $sum4FirstMan = round(($el[2]-$el[25])*$base,2);
  $prib = $el[$num];
  $mainPrib = $prib*(($el[2]-$el[25])/$el[2]);
  return [date('d.m.Y', strtotime($el[33])), 
          $el[0], 
          $el[1], 
          $el[$num], 
          0, 
          $el[$num], 
          0,
          0,
          $prib,
          $base,
          $el[36],
          0,
          0,
          $el[11],
          'Возврат по переплате',
          0,
          date('d.m.Y',strtotime($el[12])),
          ($el[41]) ? date('d.m.Y',strtotime($el[41])) : '',
          date('m.Y',strtotime($el[12])),
          $base,
          $el[14],
          0,
          $el[16],
          ($el[20] == "") ? "Обычная продажа": $el[20],
          $el[18],
          date('d.m.Y',strtotime($el[19])),
          0,
          ($el[0] == 0) ? '' : '=HYPERLINK("https://zdravkyrort.amocrm.ru/leads/detail/'.$el[0].'";"Перейти в amo >>>>>")',
          $el[24],
          $sum4FirstMan,
          $sum4FirstMan,
          $el[$num]-$sum4FirstMan,
          $mainPrib,
          $prib - $mainPrib,
          0,
          0,
          $el[27],
          ($el[28]) ? date('d.m.Y',strtotime($el[28])) : '',
          $el[30]*$base,
          ($el[32]*$base),
          $el[37],
          $el[34],
          (string)$el[40]
        ];
}, $returnOverpayment);

$returnPartandFull = array_filter($incExps, function($el) use ($datestart, $enddate) { 
  return (strtotime($el[33]) >= $datestart and strtotime($el[33]) < $enddate and $el[35] !== 'Переплата');
});

$returnPartandFull = array_map(function($el) {
  $num = 34;
  $base = $el[$num]/$el[2];
  $sebestoimost = $el[7]*$base;
  $sum4FirstMan = round(($el[2]-$el[25])*$base,2);
  $prib = ($el[2]-$el[7])*$base;
  $mainPrib = $prib*(($el[2]-$el[25])/$el[2]);
  return [date('d.m.Y', strtotime($el[33])), 
          $el[0], 
          $el[1], 
          $el[$num], 
          $sebestoimost, 
          $el[8]*$base, 
          0,
          0,
          $prib,
          0,
          $el[36],
          0,
          0,
          $el[11],
          'Полный или частичный возврат',
          ($el[23] == 1 or $el[13] == 1) ? 0 : ($sebestoimost - 0),
          date('d.m.Y',strtotime($el[12])),
          ($el[41]) ? date('d.m.Y',strtotime($el[41])) : '',
          date('m.Y',strtotime($el[12])),
          $base,
          $el[14],
          $el[15]*($el[9]+$el[10])*$base,
          $el[16],
          ($el[20] == "") ? "Обычная продажа": $el[20],
          $el[18],
          date('d.m.Y',strtotime($el[19])),
          0,
          ($el[0] == 0) ? '' : '=HYPERLINK("https://zdravkyrort.amocrm.ru/leads/detail/'.$el[0].'";"Перейти в amo >>>>>")',
          $el[24],
          $sum4FirstMan,
          $sum4FirstMan,
          $el[$num]-$sum4FirstMan,
          $mainPrib,
          $prib - $mainPrib,
          $base*($sum4FirstMan/$el[$num]),
          $base*(($el[$num]-$sum4FirstMan)/$el[$num]),
          $el[27],
          ($el[28]) ? date('d.m.Y',strtotime($el[28])): '',
          $el[30]*$base,
          ($el[31]*$base)+($el[32]*$base),
          $el[37],
          $el[34],
          (string)$el[40]
        ];
}, $returnPartandFull);

$returnSecond = array_filter($incExps, function($el) use ($datestart, $enddate) { 
  return (strtotime($el[38]) >= $datestart and strtotime($el[38]) < $enddate);
});

$returnSecond = array_map(function($el) {
  $num = 39;
  $base = $el[$num]/$el[2];
  $sebestoimost = $el[7]*$base;
  $sum4FirstMan = round(($el[2]-$el[25])*$base, 2);
  $prib = ($el[2]-$el[7])*$base;
  $mainPrib = $prib*(($el[2]-$el[25])/$el[2]);
  return [date('d.m.Y', strtotime($el[38])), 
          $el[0], 
          $el[1], 
          $el[$num], 
          $sebestoimost, 
          $el[8]*$base, 
          0,
          0,
          $prib,
          0,
          $el[36],
          0,
          0,
          $el[11],
          'Второй возврат',
          ($el[23] == 1 or $el[13] == 1) ? 0 : ($sebestoimost - 0),
          date('d.m.Y',strtotime($el[12])),
          ($el[41]) ? date('d.m.Y',strtotime($el[41])) : '',
          date('m.Y',strtotime($el[12])),
          $base,
          $el[14],
          $el[15]*($el[9]+$el[10])*$base,
          $el[16],
          ($el[20] == "") ? "Обычная продажа": $el[20],
          $el[18],
          date('d.m.Y',strtotime($el[19])),
          0,
          ($el[0] == 0) ? '' : '=HYPERLINK("https://zdravkyrort.amocrm.ru/leads/detail/'.$el[0].'";"Перейти в amo >>>>>")',
          $el[24],
          $sum4FirstMan,
          $sum4FirstMan,
          $el[$num]-$sum4FirstMan,
          $mainPrib,
          $prib - $mainPrib,
          $base*($sum4FirstMan/$el[$num]),
          $base*(($el[$num]-$sum4FirstMan)/$el[$num]),
          $el[27],
          ($el[28]) ? date('d.m.Y',strtotime($el[28])) : '',
          $el[30]*$base,
          ($el[31]*$base)+($el[32]*$base),
          $el[37],
          $el[34],
          (string)$el[40]
        ];
}, $returnSecond);

$result = array_merge($prepay, $allpay, $returnOverpayment, $returnPartandFull, $returnSecond);
unset($prepay);
unset($allpay);
unset($returnOverpayment);
unset($returnPartandFull);
unset($returnSecond);
unset($incExps);

$result = array_map(function($el) {
  return commaToDot($el, [3, 4, 5, 6, 7, 8, 15, 25, 27, 28, 29, 30, 31, 32, 33, 34, 37, 38, 40]);
}, $result);

usort($result, function($x, $y) {
  if (strtotime($x[0]) < strtotime($y[0])) {
      return false;
  }
  if (strtotime($x[0]) > strtotime($y[0])) {
      return true;
  }
  return 0;
  });

$result = array_map( function($el, $index) {
  array_unshift($el, $index+1);
  return $el;
}, $result, array_keys($result));

print_r(json_encode($result));


function dotToComma($el, $indexes) {
  foreach ($indexes as $i) {
    $el[$i] = str_replace(",", ".", $el[$i]);
  }
  return $el;
}
function commaToDot($el, $indexes) {
  foreach ($indexes as $i) {
    $el[$i] = str_replace(".", ",", (string)$el[$i]);
  }
  return $el;
}
?>