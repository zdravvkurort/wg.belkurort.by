<?php

function getPdfUrl($filePath) {
  $domain = 'https://wg.belkurort.by/widget/docjetV2/';

  $newFilePath = myOwnPdfConverter($filePath, $domain);
  if(!!$newFilePath) return $newFilePath;
  writeError();
  $newFilePath = iLovePdfConverter($filePath, $domain);
  if(!!$newFilePath) return $newFilePath;
  $newFilePath = oldPdfConverter($filePath);
  return $newFilePath;
}

function writeError() {
  $fp = fopen('errorLogging.txt', 'a');
  fwrite($fp, date("m.d.y").' - Документ был сгенерирован через доп конвертер' . PHP_EOL);
  fclose($fp);
}

function myOwnPdfConverter($filePath, $domain) {
  try {
    $fileInfo = pathinfo($filePath);
    $convertToExt = 'pdf';
    $newPath = 'wievDoc/'.$fileInfo['filename'].'.'.$convertToExt;
  
    $data = (object)array("data" => (object)array(),
                          "options" => (object)array("cacheReport" => false, 
                                                      "convertTo" => $convertToExt, 
                                                      "overwrite" => true, 
                                                      "reportName" => strGen()),
                          "template" => (object)array("content" => base64_encode(file_get_contents($filePath)),
                                                      "encodingType" => 'base64',
                                                      "fileType" => $fileInfo['extension']));
  
    $ch = curl_init('https://dg.zdravkurort.by/api/v2/template/render');
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Authorization: Bearer P2diCpAYQ3zxKaBW2IhuVSvs']);
    // //curl_setopt($ch, CURLOPT_HEADER, true); 
    // # Return response instead of printing.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
  
    if(!curl_errno($ch)) {
      $fh = fopen($newPath, 'w');
      fwrite($fh, $result);
      fclose($fh);
      return $domain.$newPath;
    } else {
      throw new Exception('Not respond!');
    }
  } catch(Exception $e) {
    return false;
  } finally {
    curl_close($ch);
  }
}

function oldPdfConverter($filePath) {
  $fileinfo = pathinfo($filePath);
	$path = $fileinfo['dirname'];
	$filename = $fileinfo['filename'];
	$cfile = curl_file_create($filePath);
	$url = "http://194.67.91.207:83/supersecretlogic.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	//Create a POST array with the file in it
	$postData = array(
		'file' => $cfile,
		'atata' => '2131236127369172831432524368'
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

	// Execute the request
	$response = curl_exec($ch);
	$response = json_decode($response,true);
  if(!!$response and !$response["error"] and !!$response["pdf_link"]) {
    return $response["pdf_link"];
  } else {
    return false;
  }
}

function iLovePdfConverter($filePath, $domain) {
  try {
    $fileInfo = pathinfo($filePath);
    $newPath = 'wievDoc/'.$fileInfo['filename'].'.pdf';
    require_once 'ilovepdf/init.php';
    if(rand(1,2) == 1) {
      $ilovepdf = new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc');
    } else {
      $ilovepdf = new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
    }
    $myTaskConvertOffice = $ilovepdf->newTask('officepdf');
    $file1 = $myTaskConvertOffice->addFile($filePath);
    $myTaskConvertOffice->execute();
    $myTaskConvertOffice->download('wievDoc/');
    return $domain.$newPath;
  } catch(Exception $e) {
    return false;
  }
}

function strGen($length = 16) {
  $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
  return substr(str_shuffle($permitted_chars), 0, $length);
}

function getPdfUrl2($file) {
	$fileinfo = pathinfo($file);
	$path = $fileinfo['dirname'];
	$filename = $fileinfo['filename'];
	$cfile = curl_file_create($file);
	$url = "http://194.67.91.207:83/supersecretlogic.php";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	//Create a POST array with the file in it
	$postData = array(
		'file' => $cfile,
		'atata' => '2131236127369172831432524368'
	);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

	// Execute the request
	$response = curl_exec($ch);
	$response = json_decode($response,true);
	if($response == 1) {
		require_once 'ilovepdf/init.php';
		$ilovepdf = (rand(1,2) == 1) ? new Ilovepdf\Ilovepdf('project_public_49e6b7c8e53ef8884b9e72bef42f2179_u5iL104a5d099045bd2e9c624ba89a2674068','secret_key_c60779739c2de8799eaea8ad848e36ce_4M_LYfbd53a674f76b806e05f7219305a19dc') : new Ilovepdf\Ilovepdf('project_public_0dc74e037e4a92250bdef7ba9b17e5b0_Isjsm3bea716e1223fca7a5daf1a65890d856','secret_key_910e4f071b8616f5d9d402dbd3d8f4a7_lQTlma6bdda657debf4d372831ce97db7876e');
		$myTaskConvertOffice = $ilovepdf->newTask('officepdf');
		$file1 = $myTaskConvertOffice->addFile($file);
		$myTaskConvertOffice->execute();
		$myTaskConvertOffice->download($path);
		return ("https://wg.belkurort.by/widget/docjetV2/".$path."/".rawurlencode($filename).".pdf");
	} else {
		return ($response["pdf_link"]);
	}
}

?>