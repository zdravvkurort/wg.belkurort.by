<?
require "../db_login.php";
require "../functions.php";

$tsf = 'ts_delitionScript.txt';
$ts = file_get_contents($tsf);

$stmt = $db->query("SELECT leads.id as id, notes_all.created_at as created_at
										FROM `notes_all` 
										INNER JOIN leads ON notes_all.entity_id = leads.id
										WHERE `notes_all`.`type` = 'lead_deleted' and `notes_all`.`created_at` > ".$ts);
$deletedLeadsIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(!count($deletedLeadsIds)) exit;

$leadsIds = implode(',', array_map(function($el) {
																		return $el['id'];
							          					}, $deletedLeadsIds));

foreach($deletedLeadsIds as $del_el) {
	if($ts < $del_el["created_at"]) {
		$ts = $del_el["created_at"];
	}
}

$count = $db->exec("DELETE FROM leads WHERE leads.id IN (".$leadsIds.")");
file_put_contents($tsf, $ts);
?>