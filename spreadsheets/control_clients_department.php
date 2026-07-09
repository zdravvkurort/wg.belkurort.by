<?
require "../functions.php";
require "../db_login.php";
// if(isset($_GET['startdate']) and isset($_GET['enddate']) and isset($_GET['offset'])) {
  $date_start = strtotime($_GET['startdate']);
  $date_end = strtotime($_GET['enddate']);
  $offset = (int)$_GET['offset'];

  $datesArray = makeDatesArray($date_start, $date_end);
  $managerStore = makeUsersArray($datesArray);
  $managerStore = getCountCRMEvents($managerStore);

  $managerStore = getCalls($managerStore);
  $managerStore = getStartEndWorking($managerStore, $datesArray);
  
  $notes = getRestPeriods($date_start, $date_end);

  $managerStore = addRests($managerStore, $notes);
  
/*
  $onlineDurations = getOnlineDuration($db, $date_start, $date_end);
  $managerStore = addDurations($managerStore, $onlineDurations);

  $total = mathTotal($managerStore);
  $table = makeTable([$total], $offset);*/
  $table = makeTable($managerStore, $offset);

  print_r(json_encode($table));

// }


function makeTable($managerStore, $offset) {
  $resultArray = [];
  foreach($managerStore as $manager) {
    $resultArray = array_merge($resultArray, makeSector($manager, $offset));
  }
  return $resultArray;
}

function makeSector($manager, $offset) {
  $resultArray = [];
  array_push($resultArray, makeRow($manager, "count_events", $offset));
  array_push($resultArray, makeRow($manager, "count_calls", $offset));
  array_push($resultArray, makeRow($manager, "duration_calls", $offset));
  array_push($resultArray, makeRow($manager, "rests", $offset));
  return $resultArray;
}

function makeRow($manager, $indicator, $offset = 0) {
  $indicatorsArray = ["start_working" => ["name" => "Начало рабочего дня", "week_sum" => 0, "mounth_sum" => 0],
                      "end_working" => ["name" => "Конец рабочего дня", "week_sum" => 0, "mounth_sum" => 0],
                      "rests" => ["name" => "Количество перерывов более получаса", "week_sum" => 0, "mounth_sum" => 0],
                      "online_duration" => ["name" => "Продолжительность использования CRM", "week_sum" => 0, "mounth_sum" => 0],
                      "count_events" => ["name" => "Количество действий в CRM", "week_sum" => 0, "mounth_sum" => 0],
                      "count_calls" => ["name" => "Количество звонков", "week_sum" => 0, "mounth_sum" => 0],
                      "duration_calls" => ["name" => "Продолжительность звонков", "week_sum" => 0, "mounth_sum" => 0]
                      ];
  $row = ($indicator == "count_events") ? [$manager["name"]] : [""];
  array_push($row, $indicatorsArray[$indicator]["name"]);

  for($i = 0; $i < $offset; $i++) {
    array_push($row,"");
  }

  $preventDayNum = 10;
  foreach($manager["indicators"] as $date => $day) {
    if ($preventDayNum == 0) list($row, $indicatorsArray) = addWeekSumm($row, $indicatorsArray, $indicator);
    $preventDayNum = (int)date('w', strtotime($date));
    $pushValue = cellFormatter($indicator, $day[$indicator]);
    $indicatorsArray = horizontalSumCalc($indicator, $day, $indicatorsArray);
    array_push($row, $pushValue);
  }
  
  if ($preventDayNum) {
    for($a = 0; $a < (7-$preventDayNum); $a++) {
      array_push($row, "");
    }
  }
  
  list($row, $indicatorsArray) = addWeekSumm($row, $indicatorsArray, $indicator);
    
  $rowLength = count($row);
  for($i = 0; $i < (51 - $rowLength); $i++) {
    if($i == (51 - $rowLength - 1)) {
      array_push($row, cellFormatter($indicator, $indicatorsArray[$indicator]["mounth_sum"]));
    } else {
      array_push($row, "");
    }
  }

  return $row;
}

function addWeekSumm($row, $indicatorsArray, $indicator) {
  $pushValue = cellFormatter($indicator, $indicatorsArray[$indicator]["week_sum"]);
  array_push($row, $pushValue);
  $indicatorsArray[$indicator]["mounth_sum"] += $indicatorsArray[$indicator]["week_sum"];
  $indicatorsArray[$indicator]["week_sum"] = 0;
  return [$row, $indicatorsArray];
}

function horizontalSumCalc($indicator, $day, $indicatorsArray) {
  switch ($indicator) {
    case "start_working":
        if($indicatorsArray[$indicator]["week_sum"] > $day[$indicator] or $indicatorsArray[$indicator]["week_sum"] == 0) {
/*        $indicatorsArray[$indicator]["week_sum"] = $day[$indicator];
          $indicatorsArray[$indicator]["mounth_sum"] = $day[$indicator];*/
        }
        break;
    case "end_working":
        if($indicatorsArray[$indicator]["week_sum"] < $day[$indicator] or $indicatorsArray[$indicator]["week_sum"] == 0) {
/*        $indicatorsArray[$indicator]["week_sum"] = $day[$indicator];
          $indicatorsArray[$indicator]["mounth_sum"] = $day[$indicator];*/
        }
        break;
    case "rests":
/*      $indicatorsArray[$indicator]["week_sum"] += is_array($day[$indicator]) ? count($day[$indicator]) : (int)$day[$indicator];
        $indicatorsArray[$indicator]["mouth_sum"] += is_array($day[$indicator]) ? count($day[$indicator]) : (int)$day[$indicator];*/
        break;
    case "online_duration":
        $indicatorsArray[$indicator]["week_sum"] += (int)$day[$indicator];
        $indicatorsArray[$indicator]["mounth_sum"] += $day[$indicator];
        break;
    case "count_events":
        $indicatorsArray[$indicator]["week_sum"] += (int)$day[$indicator];
        $indicatorsArray[$indicator]["mounth_sum"] += $day[$indicator];
        break;
    case "count_calls":
        $indicatorsArray[$indicator]["week_sum"] += (int)$day[$indicator];
        $indicatorsArray[$indicator]["mounth_sum"] += $day[$indicator];
        break;
    case "duration_calls":
        $indicatorsArray[$indicator]["week_sum"] += (int)$day[$indicator];
        $indicatorsArray[$indicator]["mounth_sum"] += $day[$indicator];
        break;
  }
  return $indicatorsArray;
}

function cellFormatter($indicator, $whatPush) {
  $pushValue = "";
  switch ($indicator) {
    case "start_working":
        $pushValue = (isset($whatPush) and $whatPush != 0) ? date('H:i', $whatPush) : "";
        break;
    case "end_working":
        $pushValue = (isset($whatPush) and $whatPush != 0) ? date('H:i', $whatPush) : "";
        break;
    case "rests":
        $pushValue = ($whatPush != 0) ? durationRestToString($whatPush) : "";
        break;
    case "online_duration":
        $pushValue = secondtotime($whatPush);
        break;
    case "count_events":
        $pushValue = ($whatPush != 0) ? $whatPush : "";
        break;
    case "count_calls":
        $pushValue = ($whatPush != 0) ? $whatPush : "";
        break;
    case "duration_calls":
        $pushValue = secondtotime($whatPush);
        break;
  }
  return $pushValue;
}

function durationRestToString($arrayDurations) {
  $result = "";
  if(is_array($arrayDurations)) {
    foreach($arrayDurations as $duration) {
      $result = $result."
".$duration["created_at"]." (".secondtotime($duration["duration"]).")";
    }
  } else {
    return $arrayDurations;
  }
  return count($arrayDurations).$result;
}

function secondtotime($seconds) {
    if($seconds == 0) return "";
    $hours = (string)floor($seconds/(60*60));
    $minutes = (string)floor(($seconds - ($hours*60*60))/60);
    $hours = (strlen($hours)==1) ? '0'.$hours : $hours;
    $minutes = (strlen($minutes)==1) ? '0'.$minutes : $minutes;
    return $hours.":".$minutes; 
    //return sprintf('%02d:%02d', $hours, $minutes);
}

function mathTotal($managerStore) {
  $totalArr = [
              "name"=> "Итого", 
              "indicators" => []
              ];
  foreach($managerStore as $manager) {
    if(isset($manager["indicators"])) {
      foreach($manager["indicators"] as $date => $indicators) {
        if(!isset($totalArr["indicators"][$date])) {
          $totalArr["indicators"][$date] = $indicators;
          if(isset($totalArr["indicators"][$date]["rests"])) {
            $totalArr["indicators"][$date]["rests"] = count($totalArr["indicators"][$date]["rests"]);
          } else {
            $totalArr["indicators"][$date]["rests"] = 0;
          }
        } else {
          if(isset($indicators["start_working"]) and ($totalArr["indicators"][$date]["start_working"] > $indicators["start_working"] or !isset($totalArr["indicators"][$date]["start_working"]))) {
            $totalArr["indicators"][$date]["start_working"] = $indicators["start_working"];
          }
          if(isset($indicators["end_working"]) and $totalArr["indicators"][$date]["end_working"] < $indicators["end_working"]) {
            $totalArr["indicators"][$date]["end_working"] = $indicators["end_working"];
          }
          $totalArr["indicators"][$date]["rests"] += count($indicators["rests"]);
          $totalArr["indicators"][$date]["online_duration"] += $indicators["online_duration"];
        }
      }
    }
  }
  return $totalArr;
}

function addDurations($managerStore, $onlineDurations) {
  foreach($onlineDurations as $duration) {
    if(isset($duration["duration"]) and (int)$duration["duration"] != 0 and isset($managerStore[(int)$duration["user_id"]]["indicators"][date('d.m.Y',strtotime($duration["date"]))])) {
      $managerStore[(int)$duration["user_id"]]["indicators"][date('d.m.Y',strtotime($duration["date"]))]["online_duration"] = (int)$duration["duration"];
    }
  }
  return $managerStore;
}

function getOnlineDuration($db, $dateStart, $dateEnd) {
  $stmt = $db->query("SELECT date, user_id, duration
                      FROM `duration_user_online`
                      WHERE date >= '".date('Y-m-d', $dateStart)."' and date <= '".date('Y-m-d', $dateEnd)."'");
  return $stmt->fetchAll();
}

function addRests($managerStore, $notes) {
  foreach($notes as $note) {
    $hours = (int)date("H", $note["created_at"]);
    if(isset($managerStore[(int)$note["created_by"]]) and isset($managerStore[(int)$note["created_by"]]["indicators"][date('d.m.Y', $note["created_at"])]) and $hours > 9 and $hours < 18) {
      array_push($managerStore[(int)$note["created_by"]]["indicators"][date('d.m.Y', $note["created_at"])]["rests"], ["created_at" => date('H:i',$note["created_at"]), "duration" => $note["diff"]]);
    }
  }
  return $managerStore;
}

function getRestPeriods($dateStart, $dateEnd) {
  global $db;
  $stmt = $db->query("SELECT created_by, created_at, diff 
                      FROM( SELECT IF(@prev_by=created_by, @prev_at-created_at, NULL) diff, @prev_by:=created_by AS created_by, @prev_at:=created_at as created_at 
                            FROM (SELECT @prev_by:=NULL, @prev_at:=NULL) x, notes_all 
                            WHERE notes_all.created_at >= ".$dateStart." AND notes_all.created_at <= ".($dateEnd + 3*24*60*60 - 1)." AND notes_all.created_by != 0 
                            ORDER BY created_by, created_at DESC) pod 
                      WHERE diff > 30*60");
  return $stmt->fetchAll();
}

function getStartEndWorking($managerStore, $datesArray) {
  global $db;

  foreach($datesArray as $key => $date) {
    $stmt = $db->query("SELECT users.id, users.name, MIN(notes_all.`created_at`) as start_working, MAX(notes_all.`created_at`) as end_working
                        FROM users
                        INNER JOIN notes_all ON users.id = notes_all.created_by
                        WHERE notes_all.`created_at` >= ".$date["start"]." and notes_all.`created_at` <= ".$date["end"]." and users.group_id = 239857 and users.active = 1
                        GROUP BY users.id");
    $managersIndicatorsByDay = $stmt->fetchAll();
    foreach($managersIndicatorsByDay as $managerIndicator) {
      $managerStore[$managerIndicator['id']]['indicators'][$key]["start_working"] = $managerIndicator["start_working"];
      $managerStore[$managerIndicator['id']]['indicators'][$key]["end_working"] = $managerIndicator["end_working"];
      $managerStore[$managerIndicator['id']]['indicators'][$key]["rests"] = [];
    }
  }
  return $managerStore;
}

function getCalls($managerStore) {
  global $db;

  foreach ($managerStore as $key => $manager) {
    $dateStart = array_slice($manager["indicators"], 0, 1);
    $dateStart = reset($dateStart);
    $dateEnd = array_slice($manager["indicators"], -1, 1);
    $dateEnd = reset($dateEnd);

    $stmt = $db->query("SELECT *
                        FROM notes_contacts
                        WHERE (created_by = ".$manager['id']." or `responsible_user_id` = ".$manager['id'].") and created_at >= ".$dateStart["start"]." and created_at <= ".$dateEnd["end"]."");
    $calls = $stmt->fetchAll();
    $calls = array_filter($calls, function($call) {
      $json = json_decode($call['params'], true);
      return ($json["call_status"] == 4 and $json['source'] != "moizvonkiru");
    });
    foreach($calls as $call) {
      $date_call = date('d.m.Y', $call["created_at"]);
      $json = json_decode($call['params'], true);
      $managerStore[$key]['indicators'][$date_call]["count_calls"] = $managerStore[$key]['indicators'][$date_call]["count_calls"] + 1;
      $managerStore[$key]['indicators'][$date_call]["duration_calls"] = $managerStore[$key]['indicators'][$date_call]["duration_calls"] + $json["duration"];
    }
  }
  return $managerStore;
}

function getCountCRMEvents($managerStore) {
  global $db;

  foreach($managerStore as $key => $manager) {
    $dateStart = array_slice($manager["indicators"], 0, 1);
    $dateStart = reset($dateStart);
    $dateEnd = array_slice($manager["indicators"], -1, 1);
    $dateEnd = reset($dateEnd);

    $stmt = $db->query("SELECT `created_by`, FROM_UNIXTIME(`created_at`, '%d.%m.%Y') as 'date', COUNT(`num`) as 'count'
                        FROM `notes_all`
                        WHERE notes_all.created_by = ".$manager['id']." and created_at >= ".$dateStart["start"]." and created_at <= ".$dateEnd["end"]."
                        GROUP BY created_by, FROM_UNIXTIME(`created_at`, '%d.%m.%Y')");
    $indicators = $stmt->fetchAll();

    foreach($indicators as $indicator) {
      $managerStore[$key]["indicators"][$indicator['date']]['count_events'] = $indicator['count'];
    }
  }
  return $managerStore;
  
}

function makeUsersArray($datesArray) {
  global $db;
  $stmt = $db->query("SELECT id, name 
                      FROM `users`
                      where `group_id` = 239857 and `active` = 1");
  $managerStore = $stmt->fetchAll();
  $usersArray = [];
  foreach($managerStore as $manager) {
    $usersArray[$manager['id']] = ["id" => $manager['id'], "name" => $manager['name'], "indicators" => $datesArray];
  }
  return $usersArray;
}

function makeDatesArray($date_start, $date_end) {
  $outAray = [];
  for($i = $date_start; $i <= $date_end; $i = $i + 24*60*60) {
    $date_formatted = date('d.m.Y', $i);
    $outAray[date('d.m.Y', $i)] = ['start'=>strtotime(date('d.m.Y', $i)), 'end'=> strtotime(date('d.m.Y', $i)) + ((24*60*60) - 1)];
  }
  return $outAray;
}
?>