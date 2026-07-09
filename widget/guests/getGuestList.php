<?php 

//подключаем БД
require "../../db_login.php";
require "../../functions.php";
cors();
if(isset($_REQUEST['lead_id']) and $_REQUEST['lead_id'] != 0) {
	$found = preg_replace("/[^0-9]/", '', $_REQUEST['lead_id']);
	
	$company_query = $db->query('SELECT companies_to_leads.id as id, companies.companyName as nameCompany
								FROM companies
								INNER JOIN companies_to_leads ON companies_to_leads.company_id = companies.id
								WHERE companies_to_leads.lead_id = '.$found.'
								ORDER BY companies.id
								LIMIT 1');
	$company = $company_query->fetchAll();
	
	$outarr['company']['values'] = $company;
	
		$stmt = $db->query('SELECT 	guests.id, 
																guests.fio, 
																DATE_FORMAT(guests.birthday, "%d.%m.%Y") as "birthday", 
																typerooms.name_type, 
																guests.vidrazm, 
																guests.food, 
																guests.health, 
																guests.sitizen,
																guests.banket,
																lead_to_guest.bill_num,
																lead_to_guest.bill_sum,
																lead_to_guest.bill_currency
		FROM `lead_to_guest` 
		inner join guests on lead_to_guest.guest_id = guests.id
		left join typerooms on guests.typerooms = typerooms.id_type
		where lead_to_guest.lead_id ='.$found.'
		ORDER BY lead_to_guest.sort_position');
		$indb = $stmt->fetchAll();
		if(isset($indb[0]) and !is_null($indb[0])) {
			$typenum = array();
			foreach($indb as $key => $val) {
				array_push($typenum, (object) [	"id" => $val["id"],
												"guestname" => $val["fio"],
												"birthday" => $val["birthday"],
												"typeroom" => $val["name_type"],
												"vidrazm" => $val["vidrazm"],
												"food" => $val["food"],
												"health" => $val["health"],
												"nationality" => $val["sitizen"],
												"banket" => $val["banket"],
												"bill_num" => $val["bill_num"],
												"bill_sum" => $val["bill_sum"],
												"bill_currency" => $val["bill_currency"]]);
			}
			$outarr['guests'] = array(
				"values" => $typenum);
		}

	echo json_encode($outarr);

} else {
	echo "Задайте санаторий";
}
?>