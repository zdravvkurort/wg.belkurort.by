<?php

$lockfile = fopen("cron.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

#require_once "../functions.php";
require_once(__DIR__ . '/../functions.php');
$array = [
          //'http://wg.belkurort.by/load_in_db/delitionScript.php',
          'http://wg.belkurort.by/load_in_db/contracts_upload.php',
          'http://wg.belkurort.by/load_in_db/contactsNotes.php'];
foreach($array as $url) {
  get_fcontent($url);
}

?>