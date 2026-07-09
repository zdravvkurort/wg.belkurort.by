<?php	
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE ); 

	$hash = ($input['hash']) ? $input['hash'] : $_POST['hash'];
	$date = ($input['date']) ? $input['date'] : $_POST['date'];
if($_SERVER["REQUEST_METHOD"]=="POST" and $hash == "jkdshglsdfoiguosdfignmdfsjhgkshdflgjhdsflkjg"){
	
	require "../../functions.php";
	require "../../db_login.php";
	cors();
	$dog_date = $date ? date('Y-m-d H:i:s',strtotime($date)) : date("Y-m-d H:i:s");

	print_r(json_encode(getInnerCursesFromDB($dog_date)));
}

	function getInnerCursesFromDB($date) {
		global $db;
		$st = $db->query("SELECT * FROM inner_courses ORDER BY date ASC");
		$currencies = $st->fetchAll();
		$result = [
			"BYNRUB" => 0,
			"RUBBYN" => 0,
			"BYNEUR" => 0,
			"EURBYN" => 0,
			"BYNUSD" => 0,
			"USDBYN" => 0,
			"RUBEUR" => 0,
			"EURRUB" => 0,
			"RUBUSD" => 0,
			"USDRUB" => 0,
			"EURUSD" => 0,
			"USDEUR" => 0,
		];

		foreach($currencies as $currency) {
			if(strtotime($date)>=strtotime($currency["date"])) {
				foreach($result as $key => $value) {
					if($currency[$key] != 0) {
						$result[$key] = $currency[$key];
					}
				};	
			}
		};
		
		return $result;	
	}

?>