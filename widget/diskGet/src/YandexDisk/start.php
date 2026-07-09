<?
$token = '';
$response = sendRequest($token, 
						'https://cloud-api.yandex.net/v1/disk/resources/', 
						["path" => "/newfolder"], 
						"PUT");
var_dump($response);

function sendRequest($token, $url, $params = [], $method = "GET") {
	if(($method == "GET" or $method == "PUT") and count($params) > 0) {
		$ch = curl_init($url."?".http_build_query($params));
	} else {
		$ch = curl_init($url);
	}
	if($method == "PUT") {
		curl_setopt($ch, CURLOPT_PUT, true);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $token));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);
	curl_close($ch);
	$res = json_decode($res, true);

	return $res;
}
?>