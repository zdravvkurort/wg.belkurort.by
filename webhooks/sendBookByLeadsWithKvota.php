<?php
require "../functions.php";
require "../db_login.php";

$plus20timestamp = mktime(0, 0, 0, date("m"), date("d")+20, date("Y"));

$stmt = $db->query("SELECT * FROM `leads` WHERE `351975` != '' AND `305351` = '' AND `status_id` = 142 AND UNIX_TIMESTAMP(`leads`.`305203`) = $plus20timestamp AND `398102` = '' AND `343217` = ''");
$leads = $stmt->fetchAll();

if(!count($leads)) exit;

foreach($leads as $lead) {
  if(!isset($lead['id'])) continue;

  $myCurl = curl_init();
  curl_setopt_array ($myCurl, array(
    CURLOPT_URL => 'http://wg.belkurort.by/widget/docjetV2/AMO_Script.php?card_id='.$lead['id'].'&card_type=lead&doc=dog212&userid=3406348&docType=2&buttonType=autosend',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true)
  );
  $response = curl_exec($myCurl);
  curl_close($myCurl);

  sleep(1);
}

?>