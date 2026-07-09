<?php 
//Умираем, если файл уже работает
$lockfile = fopen("copy.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
die("");

sleep(0.5); //Ждём пока все изменения применятся на сервере AMO

//подключаем amo
require_once(__DIR__ . '/../../auth.php');
require_once(__DIR__ . '/../../db_login.php');
require_once(__DIR__ . '/../../functions.php');

get_fcontent('http://wg.belkurort.by/load_in_db/allNotes.php');

require_once(__DIR__ . '/linked_leads.php');
require_once(__DIR__ . '/check_actions.php');


?>