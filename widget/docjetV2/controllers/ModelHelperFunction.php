<?php
function matchCurrentAndExist($current, $exist) {
  if($current == '""' or $exist == '""') return false;
  if($current == '' or $exist == '') return false;

  $current = json_decode($current, true);
  $exist = json_decode($exist, true);

  if(count($current) == 0) return false;
  if(count($exist) == 0) return false;

  foreach($current as $key => $value) {
    if($current[$key] != $exist[$key]) return false;
  }

  return true;
}
?>