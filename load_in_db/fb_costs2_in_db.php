<?php
require_once "../db_login.php";
require __DIR__ . '/facebook/vendor/autoload.php';
//подключаем функции
require_once "functions.php";
require_once "../functions.php";

// $stmt = $db->query("SELECT * FROM `facebook_tokens` ORDER BY `id` DESC"); 
// $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
// $token = $tokens[0];
// $access_token = $token['token'];
// $app_id = '293540614998630';
$accounts = [
	[
		'id' => '2643658322559624',
		'valuta' => 'PLN',
		'koef' => [['from' => '2022-08-01', 'koef' => 1.27]]
	],
	[
		'id' => '457084258363714',
		'valuta' => 'PLN',
		'koef' => [['from' => '0000-00-00', 'koef' => 1], ['from' => '2020-12-08', 'koef' => 1.05], ['from' => '2022-08-01', 'koef' => 1.27]]
	],
	// [
	// 	'id' => '687463786005674',
	// 	'valuta' => 'USD',
	// 	'koef' => [['from' => '0000-00-00', 'koef' => 1.05], ['from' => '2022-08-01', 'koef' => 1.27]]
	// ]
];


// if(strtotime($token["date"]) <= time()) {
// 	$fb = new Facebook\Facebook([
// 	'app_id' => $app_id,
// 	'app_secret' => $app_secret,
// 	'default_graph_version' => 'v20.0',
// 	'default_access_token' => $access_token, // optional
// 	'http_client_handler' => 'curl',
// 	'curl_options' => [
// 		CURLOPT_CAINFO => './facebook/fb_ca_chain_bundle.crt'
// 	],
// 	]);
//   $oAuth2Client = $fb->getOAuth2Client();
//   $newAccessToken = $oAuth2Client->getLongLivedAccessToken($access_token);
// 	$expiresAt = $newAccessToken->getExpiresAt(); // получаем реальное время истечения
// 	$expiresAt = date("Y-m-d", $expiresAt->getTimestamp());
	
// 	$db->query("INSERT INTO `facebook_tokens` 
// 				SET date='".$expiresAt."',
// 					token='".$newAccessToken->getValue()."'");
// 	$db->lastInsertId();
// 	$access_token = $newAccessToken->getValue(); // сохраняем как строку
// }

// $fb = new Facebook\Facebook([
// 	'app_id' => $app_id,
// 	'app_secret' => $app_secret,
// 	'default_graph_version' => 'v20.0',
// 	'default_access_token' => $access_token, // optional
// 	'http_client_handler' => 'curl',
// 	'curl_options' => [
// 		CURLOPT_CAINFO => './facebook/fb_ca_chain_bundle.crt'
// 	],
// 	]);

foreach($accounts as $acc) {
	$array = [];
	$date = date("Y-m-d");

	$valCurs = json_decode(getCurrencyByCode($acc['valuta']),true);
	$ruCurs = json_decode(getCurrencyByCode('RUB'),true);

	foreach($valCurs as $key => $value) {
		$valCurs[$key]["Cur_OfficialRate"] = $value["Cur_OfficialRate"]/$value["Cur_Scale"]*100/find_currency($ruCurs, $value["Date"]);
	};

	for($i=0;$i<30;$i++) {
		$d = date('Y-m-d', strtotime($date. " - ".$i." day"));
		$info = sendPostRequest('https://n8n.zdravkurort.by/webhook/0e987849-df21-4479-af31-eb3b914b2e40', $acc['id'], $d, $d);
		// $info = postRequest('act_'.$acc['id']."/insights?fields=campaign_id,campaign_name,impressions,clicks,spend&level=campaign&time_range={'since':'".$d."','until':'".$d."'}")->data;
		foreach($info as $c) {
				array_push($array, Array(
				"Date" => $d,
				"CampaignId" => $c->campaign_id,
				"CampaignName" => $c->campaign_name,
				"Site" => "instagram",
				"Impressions" => $c->impressions,
				"Clicks" => $c->clicks,
				"Cost" => $c->spend*find_currency($valCurs, $d)
				));		
		};
	}

	$array = koefCalc($array, $acc['koef']);
	
	m_set_costs_in_db($array, 'Instagram');
}

function sendPostRequest($endpoint, $adId, $dateFrom, $dateTo) {
    // Create the request body
    $data = [
        'ad_id' => $adId,
        'date_from' => $dateFrom,
        'date_to' => $dateTo
    ];
    
    // Initialize cURL session
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer vyvea3fF6yfARHsATOD3bbDmBzawbTPa',
        'Accept: application/json'
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    
    // Check for errors
    if(curl_errno($ch)) {
        throw new Exception('cURL Error: ' . curl_error($ch));
    }
    
    // Get HTTP status code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Close cURL session
    curl_close($ch);
    
    // Return response and status code
    return json_decode($response)->data;
}

function postRequest($req) {
	global $fb;
	try {
	  $response = $fb->get(
		$req
	  );
	} catch(FacebookExceptionsFacebookResponseException $e) {
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(FacebookExceptionsFacebookSDKException $e) {
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}
	return json_decode($response->getBody());
}

function InfoEssence($ess) {
    //Передан объект
    if(is_object($ess)){
        $class = get_class($ess); //класс объекта
        $obj = $ess;
        $vars_obj = '<pre>' . print_r(get_object_vars($obj), true) . '</pre>';
    //Передан класс
    } else {
        $class = $ess;
        $vars_obj = null;
    }  
  
    $vars_class = '<pre>' . print_r(get_class_vars($class), true) . '</pre>';    
    $methods = '<pre>' . print_r(get_class_methods($class), true) . '</pre>';

    if ($vars_obj) echo 'Свойства объекта- экземпляра класса '.$class.':'.$vars_obj;
    echo 'Свойства класса '.$class.':'.$vars_class.
         'Методы класса '.$class.':'.$methods;
}