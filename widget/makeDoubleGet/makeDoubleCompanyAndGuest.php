<?
require_once("../../db_login.php");
require_once("../../functions.php");

if(isset($_POST["hash"]) and $_POST["hash"] == 'kdlfgoiwrqgjag6a5gra6reg3arg2aer6ga6rg3' and isset($_POST['originalLeadId']) and isset($_POST['copyLeadId'])) {
  cors();
  $originalLeadId = (int)$_POST['originalLeadId'];
  $copyLeadId = (int)$_POST['copyLeadId'];

  $stmt = $db->query('SELECT * FROM `lead_to_guest` WHERE `lead_id` = '.$originalLeadId);
  $originalL2G = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $guestFields = getColumns($db, 'guests');
  $guestFields = array_filter($guestFields, function($el) {return $el != 'id';});
  $lead2GuestFields = getColumns($db, 'lead_to_guest');
  $lead2GuestFields = array_filter($lead2GuestFields, function($el) {return ($el != 'id' and $el != 'timestamp');});

  if(count($originalL2G)) {
    foreach ($originalL2G as $lead2guest) {
      $guests = getGuest($db, $lead2guest["guest_id"]);
      $copyLead2Guest = $lead2guest;
      foreach($guests as $key => $guest) {
        unset($guest['id']);
        $copyLead2Guest["guest_id"] = insertRecord($db, "guests", $guestFields, $guest);
        $copyLead2Guest["lead_id"] = $copyLeadId;
        unset($copyLead2Guest['id']);
        unset($copyLead2Guest['timestamp']);
        insertRecord($db, "lead_to_guest", $lead2GuestFields, $copyLead2Guest);
      }
    }
  }

  $stmt = $db->query('SELECT * FROM `companies_to_leads` WHERE `lead_id` = '.$originalLeadId);
  $originalL2C = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $companiesFields = getColumns($db, 'companies');
  $companiesFields = array_filter($companiesFields, function($el) {return $el != 'id';});
  $companies_to_leadsFields = getColumns($db, 'companies_to_leads');
  $companies_to_leadsFields = array_filter($companies_to_leadsFields, function($el) {return ($el != 'id');});

  if(count($originalL2C)) {
    foreach ($originalL2C as $lead2companies) {
      $companies = getCompany($db, $lead2companies["company_id"]);
      $copyLead2companies = $lead2companies;
      foreach($companies as $key => $company) {
        unset($company['id']);
        $copyLead2companies["company_id"] = insertRecord($db, "companies", $companiesFields, $company);
        $copyLead2companies["lead_id"] = $copyLeadId;
        unset($copyLead2companies['id']);
        insertRecord($db, "companies_to_leads", $companies_to_leadsFields, $copyLead2companies);
      }
    }
  }
  
  echo json_encode(["result" => "ok"]);
} else {
  echo json_encode(["result" => "error"]); 
}

function getCompany($db, $companyId) {
  $stmt = $db->query('SELECT * FROM `companies` WHERE `id` = '.$companyId);
  $company = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $company;
}

function getGuest($db, $guestId) {
  $stmt = $db->query('SELECT * FROM `guests` WHERE `id` = '.$guestId);
  $guest = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $guest;
}

function insertRecord($db, $tableName, $columns, $record){
  $val = '';
  foreach($columns as $col) {
    $val = $val.":".$col.",";
  }
  $val = substr(trim($val), 0, -1);
  $sql = "INSERT INTO ".$tableName." (".implode(", ", $columns).") VALUES (".$val.")";
  $db->prepare($sql)->execute($record);
  return $db->lastInsertId();
}

function getColumns($db, $table) {
  $q = $db->prepare("DESCRIBE ".$table);
  $q->execute();
  $tableFields = $q->fetchAll(PDO::FETCH_COLUMN);
  return $tableFields;
}
?>