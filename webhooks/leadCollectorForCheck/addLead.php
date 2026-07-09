<?php
require "../../functions.php";

// Если не пост не трогаем
if(!isset($_POST)) exit;

// Получаем данные
parse_str(file_get_contents('php://input'), $input);

// Выбираем ids
$leadIds = findIds("add", $input["leads"]);
$leadIds = array_merge($leadIds, findIds("status", $input["leads"]));

if(count($leadIds) === 0) exit;

$leadCollectorFile = 'leadCollectorIds.txt';
$leadCollector = file_get_contents($leadCollectorFile);
  
$leadCollector = json_decode($leadCollector, true);
  
$leadCollector = array_merge($leadCollector, $leadIds);
$leadCollector = array_unique($leadCollector);
file_put_contents($leadCollectorFile, json_encode($leadCollector));

require_once "../../auth.php";
//авторизуемся
authAMO($login, $userhash, $subdomain); 
$allleads = getRequestToAmo('/api/v2/leads?id='.implode(",", $leadCollector));

if(!$allleads) exit;

$leadsInCollector = json_decode(file_get_contents("leads.txt"), true);
$leadsInCollector = array_merge($leadsInCollector, $allleads);
file_put_contents("leads.txt", json_encode($leadsInCollector));

$leadsInCollector = array_map(function($el) {
  return $el['id'];
}, $leadsInCollector);

$leadCollector = array_filter($leadCollector, function($el) use ($leadsInCollector) {
  return !in_array($el, $leadsInCollector);
});

file_put_contents($leadCollectorFile, json_encode($leadCollector));

function findIds($param, $leadsArr) {
  $leadIds = [];
  if(isset($leadsArr[$param])) {
    foreach($leadsArr[$param] as $id) {
      array_push($leadIds, $id["id"]);
    }
  }
  return $leadIds;
}

?>