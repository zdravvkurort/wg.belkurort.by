<?php
$discounts = [
  (object)[
    "foundation_id" => 450649,
    "name" => "Скидка за длительное проживание",
    "end_date" => strtotime("2021-11-30"),
    "checkIn_from" => strtotime("2021-09-01"),
    "checkIn_to" => strtotime("2021-11-30"),
    "count_night_from" => 7,
    "discount_value" => 7
  ],
  (object)[
    "foundation_id" => 450649,
    "name" => "Скидка за длительное проживание",
    "end_date" => strtotime("2021-11-30"),
    "checkIn_from" => strtotime("2021-09-01"),
    "checkIn_to" => strtotime("2021-11-30"),
    "count_night_from" => 10,
    "discount_value" => 10
  ],
  (object)[
    "foundation_id" => 450649,
    "name" => "Скидка за длительное проживание",
    "end_date" => strtotime("2021-11-30"),
    "checkIn_from" => strtotime("2021-09-01"),
    "checkIn_to" => strtotime("2021-11-30"),
    "count_night_from" => 15,
    "discount_value" => 15
  ],
  (object)[
    "foundation_id" => 450649,
    "name" => "Скидка раннего бронирования",
    "end_date" => strtotime("2021-11-30"),
    "checkIn_from" => strtotime("+32 day"),
    "checkIn_to" => strtotime("+99999 day"),
    "count_night_from" => 2,
    "discount_value" => 10
  ]
];

function getBestDiscount($params) {
  //$params["countNight"];
  //$params["checkIn"];
  //$params["foundation"];
  global $discounts;
  $result = array_filter($discounts, function ($disc) use($params) {
    return ($params["foundation"] == $disc->foundation_id and 
            $params["countNight"] >= $disc->count_night_from and 
            $params["checkIn"] >= $disc->checkIn_from and 
            $params["checkIn"] <= $disc->checkIn_to);
  });

  $result = array_values($result);
  usort($result, function($a, $b) {
    return $a->discount_value < $b->discount_value;
  });

  if(count($result)) return $result[0];
  return null;
}

function getDiscountsByFoundaion($id) {
  global $discounts;
  $result = array_filter($discounts, function($discount) use ($id) {
    return ($discount->foundation_id === $id);
  });
  $result = array_values($result);
  return $result;
}
?>