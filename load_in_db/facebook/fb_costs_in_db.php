<?php
require __DIR__ . '/vendor/autoload.php';
//подключаем функции
require_once "../functions.php";
require_once "../../functions.php";
require_once "../secrets.php";

$access_token = $fb_secrets['fb_costs_in_db']['access_token'];
$app_secret = $fb_secrets['fb_costs_in_db']['app_secret'];
$app_id = '293540614998630';
$id = 'act_457084258363714';

$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => 'v7.0',
  'default_access_token' => $access_token, // optional
  ]);

//$campagins = postRequest("act_457084258363714/insights?fields=campaign_id,campaign_name,impressions,clicks,spend&level=campaign&time_range={'since':'2020-05-22','until':'2020-05-22'}");

$array = [];
$date = date("Y-m-d");

for($i=0;$i<3;$i++) {
	$d = date('Y-m-d', strtotime($date. " - ".$i." day"));
	$info = postRequest("act_457084258363714/insights?fields=campaign_id,campaign_name,impressions,clicks,spend&level=campaign&time_range={'since':'".$d."','until':'".$d."'}")->data;
		foreach($info as $c) {
			array_push($array, Array(
			"Date" => $d,
			"CampaignId" => $c->campaign_id,
			"CampaignName" => $c->campaign_name,
			"Site" => "instagram",
			"Impressions" => $c->impressions,
			"Clicks" => $c->clicks,
			"Cost" => $c->spend
			));		
		};
}

vardump($array);

// m_set_costs_in_db($array, 'Instagram');

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