<?php	
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE ); 

	$hash = ($input['hash']) ? $input['hash'] : $_POST['hash'];
	$dateFrom = ($input['dateFrom']) ? $input['dateFrom'] : $_POST['dateFrom'];
	$dateTo = ($input['dateTo']) ? $input['dateTo'] : $_POST['dateTo'];
if($_SERVER["REQUEST_METHOD"]=="POST" and $hash == "jkdshglsdfoiguosdfignmdfsjhgkshdflgjhdsflkjg"){
	
	require "../../functions.php";
	require "../../db_login.php";
	cors();
	$dateFrom = $dateFrom ? date('Y-m-d H:i:s',strtotime($dateFrom)) : date("Y-m-d H:i:s");
	$dateTo = $dateTo ? date('Y-m-d H:i:s',strtotime($dateTo)) : date("Y-m-d H:i:s");
	print_r(json_encode(getInnerCursesFromDB($dateFrom, $dateTo)));
}

	function getInnerCursesFromDB($dateFrom, $dateTo) {
		global $db;
		$st = $db->query("SELECT * FROM inner_courses WHERE date >= '$dateFrom' and date <= '$dateTo' ORDER BY date DESC");
		$currencies = $st->fetchAll(PDO::FETCH_ASSOC);
		return $currencies;
	}

?>