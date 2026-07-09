<?

function saveFileByLinkOnYaDisk($fileURL, $objectType = 'leads', $objectId, $fileName, $path = '') {
  $url = 'https://diskget.belkurort.by/disk/resources/loadResource';
  // $fileURL = dirname($fileURL).'/'.rawurlencode(basename($fileURL));
  $fileURL = dirname($fileURL).'/'.basename($fileURL);
  $data = array('fileUrl' => $fileURL, 
                'objectType' => $objectType, 
                'objectId' => $objectId, 
                'fileName' => $fileName,
                'folder' => $path);

  try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Origin: https://wg.belkurort.by",
      "Auth: 9035wu4goiejkfd0g9iw54'pkogk]w45oasdb[v]obfpdgbf;lbk,xgpkr[gtw[oe5gt09i04etrpko]]",
      'Content-Type: application/json;charset=utf-8'
    ));
    // Edit: prior variable $postFields should be $postfields;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
    $result = curl_exec($ch);
    $result = json_decode($result, true);
    return $result;
  } catch (\Throwable $th) {
    return false;
  }
}

function saveFileToYaDisk($entity, $entityId, $folder, $filePath) {
  $pathArray = explode("/", $filePath);
  $fileName = $pathArray[count($pathArray) - 1];
  $fileParams = getLoadLink($entity, $entityId, $folder, $fileName);
  if($fileParams) sendFileToYaDisk($fileParams, $filePath);
}

function sendFileToYaDisk($params, $filePath) {
  $url_path_str = $params["href"];
  $file_path_str = $filePath;
  try {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, ''.$url_path_str.'');
    curl_setopt($ch, CURLOPT_PUT, 1);

    $fh_res = fopen($file_path_str, 'r');

    curl_setopt($ch, CURLOPT_INFILE, $fh_res);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path_str));

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $curl_response_res = curl_exec ($ch);
    fclose($fh_res);
    return true;
  } catch (\Throwable $th) {
    return false;
  }
}

function getLoadLink($ObjectType = 'leads', $objectId, $path = '', $fileName) {
  $url = 'https://diskget.belkurort.by/disk/resources/loadLinks';
  $data = array('objectType' => $ObjectType, 'objectId' => $objectId, "path" => $path, "fileName" => $fileName);

  try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Origin: https://wg.belkurort.by",
      "Auth: 9035wu4goiejkfd0g9iw54'pkogk]w45oasdb[v]obfpdgbf;lbk,xgpkr[gtw[oe5gt09i04etrpko]]",
      'Content-Type: application/json;charset=utf-8'
    ));
    // Edit: prior variable $postFields should be $postfields;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
    $result = curl_exec($ch);
    $result = json_decode($result, true);
    return $result;
  } catch (\Throwable $th) {
    return false;
  }
}

function myUrlEncode($string) {
  $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
  $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
  return str_replace($entities, $replacements, urlencode($string));
}

?>