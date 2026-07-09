<?php
require_once "../discounts/discounts.php";
require_once "prices.php";
require_once "../tourservices/tourservices.php";

require_once "../functions.php";
// $params["countNight"] = 2;
// $params["checkIn"] = strtotime('2021-09-21');
// $params["code"] = '21';
// $params["valuta"] = "RUB";
function getCostSanAndDiscount ($params) {
  $bestDiscount = getBestDiscount($params);
  $actualPrice = getActualPrice($params);

  $payload['foundationPriceName'] = $actualPrice->name;
  $payload['foundationPrice'] = $actualPrice->price*$params["countNight"];
  $payload['discountName'] = $bestDiscount->name;
  $payload['discount'] = floor($payload['foundationPrice'] * $bestDiscount->discount_value / 100);
  $payload['discount_percent'] = $bestDiscount->discount_value;

  return $payload;
}
?>