<?php
	require_once("../../auth.php");

function getLead($card_id) {
  global $amo;
  $leads = $amo->lead->apiList([
    'id' => $card_id,
  ]);
  $lead = [];
  foreach($leads as $key) {
    if($key['id'] == $card_id) {
      $lead = $key;
    }
  }
  return $lead;
}

// function getLead3($card_id) {
//   $attempt = 0;
//     try{
//     return getLead($card_id)
//     } catch(Exception $e) {
//     $attempt++;
//       echo 'Произошла ошибка '.$e;
//     return getLead($card_id)
//     }
// }

function changeLeadCustomField($lead_id, $field_id, $value) {
    try{
      global $amo;
      $lead = $amo->lead;
      $lead->addCustomField($field_id, $value);
      $id = $lead->apiUpdate((int)$lead_id, 'now');
      return $id;
    } catch(Exception $e) {
      echo 'Произошла ошибка обновления номера договора в сделке'.$e;
    }
}

function getAccInfo() {
  global $amo;
  return $amo->account->apiCurrent();
}

?>