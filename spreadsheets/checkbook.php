<?php 

require "../db_login.php";
require "../functions.php";

$stmt = $db->query('
SELECT 
	leads.id, 
	DATE_FORMAT(leads.305203,"%d.%m.%Y") as "Дата заезда",
	DATE_FORMAT(FROM_UNIXTIME(date_contract.date_contract),"%d.%m.%Y") as "Дата договора", 
	leads.305089 as "Санаторий", 
	users.name, 
	leads.305351 as "Заявка", 
	leads.305353 as "Аннуляция",
	statuses.name as "Статус"
FROM `leads`
	inner Join users ON leads.responsible_user_id = users.id
	inner join date_contract on leads.id = date_contract.lead_id
	inner join statuses ON leads.status_id = statuses.id
where not
	(leads.305351 REGEXP "^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}" and
	leads.305353 REGEXP "^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}") and 
	leads.305351 <> "" and 
	leads.status_id <> 142 and 
	leads.328497 <> 1 and
	leads.pipeline_id = 1736272
ORDER BY leads.305203');
$leadslist = $stmt->fetchAll();
$outputleadslist = [];

foreach($leadslist as $lead) {
			
			array_push($outputleadslist, 
			array(
			"https://zdravkyrort.amocrm.ru/leads/detail/".$lead["id"],
			$lead['Дата заезда'],
			$lead['Дата договора'],
			$lead['Санаторий'],
			$lead['name'],
			$lead['Заявка'],
			$lead['Аннуляция'],
			$lead['Статус']
			));
			
}
//vardump($outputleadslist);
print_r(json_encode($outputleadslist));
?>