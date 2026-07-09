<?php 

require "../db_login.php";
require "../functions.php";

$stmt = $db->query('SELECT users.name as "user_name"
from users
where users.group_id = 0 and users.active = 1');

$leadslist = $stmt->fetchAll();
$outputleadslist = [];

foreach($leadslist as $lead) {
			array_push($outputleadslist, 
			array(
			$lead["user_name"]
			));
}
print_r(json_encode($outputleadslist));
?>