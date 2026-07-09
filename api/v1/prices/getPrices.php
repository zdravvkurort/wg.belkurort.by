<?php
require "../prices/prices.php";
require "../functions.php";
require "../../../functions.php";
cors();
$id = (int)$_REQUEST['foundationId'];
$typeRoomId = (int)$_REQUEST['typeRoomId'];

$result = getPricesByFoundaion($id, $typeRoomId);

sendSuccess($result);
?>