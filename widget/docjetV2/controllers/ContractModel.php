<?php
function getCurrentContract($leadId) {
  global $db;
  $stmt = $db->query('SELECT * FROM numdoc WHERE leadid = "'.$leadId.'" ORDER BY num ASC');
  $contracts = $stmt->fetchAll();
  return (count($contracts)) ? $contracts[count($contracts) -1] : false ;
}

function createContract($card_id, $payload = '') {
  global $db;
  $today = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
  $stmt = $db->query('SELECT max(num) FROM numdoc');
  $actualnum = $stmt->fetchAll()[0]["max(num)"];
  $actualnum = $actualnum + 1;
  $stmt = $db->prepare("INSERT INTO numdoc (num, date, leadid, payload) VALUES (?, ?, ?, ?)");
  $stmt->execute([$actualnum, $today, (int)$card_id, json_encode($payload)]);
  return $actualnum;
}

function updateContract($num, $payload) {
  global $db;
  $stmt = $db->prepare("UPDATE numdoc SET payload = ? where num = ?");
  $stmt->execute([json_encode($payload), $num]);
  return true;
}
?>