<?php 

//подключаем БД
require "../../../db_login.php";
require "../../../functions.php";
cors();
if(isset($_POST['guest_id']) and $_POST['guest_id'] != 0) {
	$found = preg_replace("/[^0-9]/", '', $_REQUEST['guest_id']);

	$stmt = $db->query('SELECT companies.id, companies.companyName, companies.represented, companies.basis, companies.address, companies.addressPost, companies.checkingAcc, companies.bankCode, companies.korrSchet, companies.bankAddress, companies.unp, companies.phone
						FROM companies
						INNER JOIN companies_to_leads ON companies_to_leads.company_id = companies.id
						where companies_to_leads.id ='.$found);
	$indb = $stmt->fetchAll();
	if(isset($indb[0]) and !is_null($indb[0])) {
			$typenum =(object) [	"id" => $indb[0]["id"],
									"link_id" => $found,
									"companyName" => $indb[0]["companyName"],
									"represented" => $indb[0]["represented"],
									"basis" => $indb[0]["basis"],
									"address" => $indb[0]["address"],
									"addressPost" => $indb[0]["addressPost"],
									"checkingAcc" => $indb[0]["checkingAcc"],
									"bankCode" => $indb[0]["bankCode"],
									"korrSchet" => $indb[0]["korrSchet"],
									"bankAddress" => $indb[0]["bankAddress"],
									"unp" => $indb[0]["unp"],
									"phone" => $indb[0]["phone"],
									];
		
		$outarr = $typenum;
	} else {
		$outarr = array(
		"error" => false,
		"type_appart" => array());
	}
	echo json_encode($outarr);

} else {
	echo "Задайте санаторий";
}
?>