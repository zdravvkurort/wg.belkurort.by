<?php
if(isset($_POST)) {
  if($_POST["account"]["subdomain"] === "zdravkyrort") {
    $iterationArr = $_POST["contacts"]["add"];
    if(count($iterationArr) > 0) {
      foreach($iterationArr as $contact) {
        $dateCreate = (int)$contact["date_create"];
        if(isset($contact["date_create"]) and $dateCreate != 0) {
          $tsf = 'ts.txt';
          $ts = (int)file_get_contents($tsf);
    
          if($dateCreate < $ts) {
            file_put_contents($tsf, (string)$dateCreate);
          }
        }
      }
    }
  }
}
?>