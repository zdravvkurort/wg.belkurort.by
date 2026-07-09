<?php
$prices = [
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '30',
    "name"=> "3 гр-на РБ (17 и старше)",
    "valuta"=> "BYN",
    "price"=> 302
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '21',
    "name"=> "2 гр-на РБ (17 и старше) + 1 гр-н РБ (3-16 лет)",
    "valuta"=> "BYN",
    "price"=> 270
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '12',
    "name"=> "1 гр-на РБ (17 и старше) + 2 гр-на РБ (3-16 лет)",
    "valuta"=> "BYN",
    "price"=> 254
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '20',
    "name"=> "2 гр-на РБ (17 и старше)",
    "valuta"=> "BYN",
    "price"=> 208
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '11',
    "name"=> "1 гр-на РБ (17 и старше) + 1 гр-на РБ (3-16 лет)",
    "valuta"=> "BYN",
    "price"=> 192
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '10',
    "name"=> "1 гр-на РБ (17 и старше)",
    "valuta"=> "BYN",
    "price"=> 146
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '30',
    "name"=> "3 гр-на РФ (17 и старше)",
    "valuta"=> "RUB",
    "price"=> 10179
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '21',
    "name"=> "2 гр-на РФ (17 и старше) + 1 гр-н РФ (3-16 лет)",
    "valuta"=> "RUB",
    "price"=> 9109
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '12',
    "name"=> "1 гр-на РФ (17 и старше) + 2 гр-на РФ (3-16 лет)",
    "valuta"=> "RUB",
    "price"=> 8583
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '20',
    "name"=> "2 гр-на РФ (17 и старше)",
    "valuta"=> "RUB",
    "price"=> 7020
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '11',
    "name"=> "1 гр-на РФ (17 и старше) + 1 гр-на РФ (3-16 лет)",
    "valuta"=> "RUB",
    "price"=> 6494
  ],
  (object)[
    "foundation_id"=> 450649,
    "type_room_id"=> 109,
    "date_from"=> strtotime("2021-08-30"),
    "date_to"=> strtotime("2021-12-01"),
    "code" => '10',
    "name"=> "1 гр-на РФ (17 и старше)",
    "valuta"=> "RUB",
    "price"=> 4914
  ]
];

function getActualPrice($params) {
  global $prices;
  $result = array_filter($prices, function ($price) use($params) {
    global $params;
    return ($price->foundation_id == $params["foundation"] and
            $price->code === $params["code"] and
            $price->valuta === $params["valuta"]);
  });
  $result = array_values($result);

  if(count($result)) return $result[0];
  return null;
}

function getPricesByFoundaion($id, $typeRoomId) {
  global $prices;
  $result = array_filter($prices, function($price) use ($id, $typeRoomId) {
    return ($price->foundation_id === $id and $price->type_room_id === $typeRoomId);
  });
  $result = array_values($result);
  return $result;
}
?>