<?
//подключаем amo

require_once(__DIR__ . '/../auth.php');
require_once(__DIR__ . '/../db_login.php');
require_once(__DIR__ . '/../functions.php');
require_once(__DIR__ . '/./allNotesFunctions.php');

$lockfile = fopen("allNotes.lock", 'w');
if(flock($lockfile, LOCK_EX | LOCK_NB ) !== true)
  die("");

$stmt = $db->query("SELECT MAX(`created_at`) as max_timestamp FROM `notes_all`");
$timestamp = $stmt->fetchAll()[0]['max_timestamp'];
// $timestamp = 1623099600;
// vardump($timestamp);
$notes = getNotes($timestamp-(15*60));
// $notes = getNotes(1681333200);

?>