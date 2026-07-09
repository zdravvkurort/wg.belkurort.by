<?php
require __DIR__ . '/vendor/autoload.php';
//подключаем функции
require_once "../functions.php";
require_once "../secrets.php";

$access_token = $fb_secrets['test']['access_token'];
$app_secret = $fb_secrets['test']['app_secret'];
$app_id = '993658914436950';
$id = 'act_457084258363714';

$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => 'v7.0',
  'default_access_token' => $access_token, // optional
  ]);
  
	global $fb;
	try {
	  $response = $fb->get(
		'274530826963314'
	  );
	} catch(FacebookExceptionsFacebookResponseException $e) {
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(FacebookExceptionsFacebookSDKException $e) {
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}
	
	$response = json_decode($response->getBody());
	
	var_dump($response);