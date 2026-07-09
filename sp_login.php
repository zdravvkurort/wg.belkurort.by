<?php 

require("src/sendpulse/Storage/TokenStorageInterface.php");
require("src/sendpulse/Storage/FileStorage.php");
require("src/sendpulse//ApiInterface.php");
require("src/sendpulse//ApiClient.php");
use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

define('API_USER_ID', '5374e542e8c5a62c31a1ad644ae95bde');
define('API_SECRET', 'c4a94170b77d70f78076d012ec0af1a0');
define('PATH_TO_ATTACH_FILE', __FILE__);

// создаем клиента 
$SPApiClient = new ApiClient(API_USER_ID, API_SECRET, new FileStorage()); 

?>