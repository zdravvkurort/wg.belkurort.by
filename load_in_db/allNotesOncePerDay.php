<?
//подключаем amo
require_once "../auth.php";
require_once "../db_login.php";
require_once "../functions.php";
require_once "./allNotesFunctions.php";
// $lockfile = fopen("allNotes.lock", 'w');
// if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
//   die("");

$stmt = $db->query("SELECT MAX(`created_at`) as max_timestamp FROM `notes_all`");
$timestamp = $stmt->fetchAll()[0]['max_timestamp'];
// $timestamp = 1623099600;
// vardump($timestamp);
$notes = getIncommingChatMessagesNotes($timestamp-(24*60*60));
// $notes = getNotes(1681333200);