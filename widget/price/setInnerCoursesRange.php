<?php	
	$inputJSON = file_get_contents('php://input');
	$input= json_decode( $inputJSON, TRUE ); 

	$hash = ($input['hash']) ? $input['hash'] : $_POST['hash'];
	$range = ($input['range']) ? $input['range'] : $_POST['range'];
	$range = json_decode($range, true);

if($_SERVER["REQUEST_METHOD"]=="POST" and $hash == "jkdshglsdfoiguosdfignmdfsjhgkshdflgjhdsflkjg" and $range){
	
	require "../../functions.php";
	require "../../db_login.php";
	cors();

	$result = updateCourses($range);

	print_r(json_encode(['result' => $result]));
}

	function updateCourses($range) {
		global $db;
	  
		$result = false;
		foreach($range as $data) {
			unset($data['date']);
			$sql = "UPDATE inner_courses 
							SET BYNRUB=:BYNRUB, 
									RUBBYN=:RUBBYN, 
									BYNEUR=:BYNEUR,
									EURBYN=:EURBYN,
									BYNUSD=:BYNUSD,
									USDBYN=:USDBYN,
									RUBEUR=:RUBEUR,
									EURRUB=:EURRUB,
									RUBUSD=:RUBUSD,
									USDRUB=:USDRUB,
									EURUSD=:EURUSD,
									USDEUR=:USDEUR
							WHERE id=:id";
			$stmt= $db->prepare($sql);
			$result = $stmt->execute($data);
		}
		return $result;
	}

?>