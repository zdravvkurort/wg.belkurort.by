<?php 
require "../../../functions.php";
require "../../../db_login.php";
cors();
if(isset($_POST) && isset($_POST['key']) && $_POST['key'] == 'nYK4dxa{bFQoQEEq%AibWTrW') {
	$responce['status'] = false;
	
	$comp_id = quotemeta($_POST['comp_id']);
	$companyName = addslashes(str_replace("\"","'",$_POST['companyName']));
	$represented = addslashes(str_replace("\"","'",$_POST['represented']));
	$basis = addslashes(str_replace("\"","'",$_POST['basis']));
	$address = addslashes(str_replace("\"","'",$_POST['address']));
	$addressPost = addslashes(str_replace("\"","'",$_POST['addressPost']));
	$checkingAcc = addslashes(str_replace("\"","'",$_POST['checkingAcc']));
	$bankCode = addslashes(str_replace("\"","'",$_POST['bankCode']));
	$korrSchet = addslashes(str_replace("\"","'",$_POST['korrSchet']));
	$bankAddress = addslashes(str_replace("\"","'",$_POST['bankAddress']));
	$unp = addslashes(str_replace("\"","'",$_POST['unp']));
	$phone = addslashes(str_replace("\"","'",$_POST['phone']));
	
	$lead_id = addslashes(str_replace("\"","'",$_POST['lead_id']));
	$responce['date'] = $address;
	
	if($comp_id == 0) {
		
		try{
		
		$db->exec("INSERT INTO companies (companyName, represented, basis, address, addressPost, checkingAcc, bankCode, korrSchet, bankAddress, unp, phone) 
								VALUES ('$companyName', '$represented','$basis','$address','$addressPost','$checkingAcc','$bankCode','$korrSchet','$bankAddress','$unp','$phone')");
		$id = $db->lastInsertId();
		
		$db->exec("INSERT INTO companies_to_leads (lead_id, company_id) 
		VALUES ('$lead_id','$id')");
		
		$responce['status'] = true;						
		} catch (Exception $e) {
			$responce['type_error'] = $e;
		}
		
	echo json_encode($responce);
	
	} else if($_POST['form_id'] == "changeCompanyForm") {
		
		try{		
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	
			$stmt = $db->prepare("UPDATE companies
								SET 
								companyName='".$companyName."', 
								represented='".$represented."', 
								basis='".$basis."',  
								address='".$address."', 
								addressPost='".$addressPost."', 
								checkingAcc='".$checkingAcc."', 
								bankCode='".$bankCode."', 
								korrSchet='".$korrSchet."', 
								bankAddress='".$bankAddress."', 
								unp='".$unp."', 
								phone='".$phone."'
				
								where id ='".$comp_id."'");
			$stmt->execute();
			$responce['status'] = true;	
		} catch(Exception $e) {
			$responce['type_error'] = $e;
		}
		
	echo json_encode($responce);
	
	}
	
} else {
	echo "Неверный ключ";
}
?>