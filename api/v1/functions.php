<?php
function sendError($text) {
  $response["status"] = "Error";
  $response["text"] = $text;
  echo json_encode($response);
  exit;
}

function sendSuccess($payload) {
  $response["status"] = "OK";
  $response["payload"] = $payload;
  echo json_encode($response);
}
?>