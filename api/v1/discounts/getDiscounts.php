<?php
require "./discounts.php";
require "../functions.php";
require "../../../functions.php";
cors();
$id = (int)$_REQUEST['foundationId'];
$typeRoomId = (int)$_REQUEST['typeRoomId'];

$result = getDiscountsByFoundaion($id);

sendSuccess($result);
?>