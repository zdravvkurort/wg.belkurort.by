<?php
function normalizeLeadsTableStructure() {
	global $amo;
	global $db;
	$columns = [];

	$cf = $amo->account->apiCurrent()['custom_fields']['leads']; //получаем массив доп. полей из AMO
	$stmt = $db->query("SHOW COLUMNS FROM `leads`");//получаем массив столбцов из БД

	while ($row = $stmt->fetch())
	{
			array_push($columns,$row['Field']);
	}

	//если столбца в БД нет - добавляем его
	foreach($cf as $field) {
		if(!in_array($field['id'], $columns)) {
			$stmt = $db->query("ALTER TABLE `leads` ADD `".$field['id']."` TEXT NOT NULL");
			$stmt->fetch();
		}
	}

	return $columns;
}

function getLeadById($id) {
	global $amo;
	$leads = $amo->lead->apiList(['limit_rows' => 500, 'query' => $id]);
	foreach($leads as $lead) {
		if($lead['id'] == $id) {
			return $lead;
		}
	}
}

function addLeadsInTable($array_new_leads) {

	global $db;
	global $amo;
	global $columns;

foreach($array_new_leads as $lead) {
		$query = "";
		$stmt = $db->query("SELECT COUNT(*) FROM `leads` WHERE id = ".$lead['id']); //берём id лида из БД
		$result = $stmt->fetchAll();
		if($result[0][0] != "0") {
			$stmt = $db->prepare( "DELETE FROM `leads` WHERE id = ".$lead['id']);
			$stmt->execute();
		}
			$query .= "INSERT INTO `leads` SET";
			foreach($lead as $name => $val) {	
				if($name != "custom_fields") {
					if(in_array($name, $columns)) {
					$query .= " ".$name."='";
					if(gettype($val) != "array") {
						$text = addslashes($val);
						$query .= $text."',";
					} else if(gettype($val) == "array"){
						if($name == "tags") {
							foreach($val as $v) {
								$text = addslashes($v['name']);
								$query .= $text."&";
							}
						$query=rtrim($query,"& ");
						}
						$query .= "',";
					}}
				} else if($name == "custom_fields") {
					foreach($val as $v) {
						$query .= " `".$v['id']."`='";
							$text = addslashes($v["values"][0]['value']);
							$query .= $text."&";
						$query=rtrim($query,"& ");
						$query .= "',";
					}
				}
			}
		$query=rtrim($query,", ");
			try {
			$st = $db->query($query);
			$insertId=$db->lastInsertId();
			} catch (Exception $e) { 
				echo $e->errorMessage(); 
			}		
}
}

function withoutSpecSymb($text) {
	// $text = str_replace("'", "\'", $text);
	// $text = str_replace('"', '\"', $text);
	$text = addslashes($text);
	// $text = quotemeta($text);
	return $text;
}
?>