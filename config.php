<?php
header('p3p: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
set_include_path(dirname(__FILE__) . ":");
require('vendor/autoload.php');

$config = array(
  "oahu" => array(
    "host"      => getenv('OAHU_HOST'),
    "clientId"  => getenv('OAHU_CLIENT_ID'),
    "appId"     => getenv('OAHU_APP_ID'),
    "appSecret" => getenv('OAHU_APP_SECRET'),
    // Caching with Memcached
    // A activer en PROD pour eviter de faire des requetes coté serveur a chaque fois !!!
    // "debug"     => true,
    // "cache"     => "true",
    // "cacheHost" => "127.0.0.1",
    // "cachePort" => 11211,
    // "cacheExpiration" => 10 // 3 hours ?
  )
);

$oahu = new Oahu_Client($config);
$current_account_id = $oahu->validateUserAccount();
if ($current_account_id) {
    $current_account = $oahu->get("accounts/" . $current_account_id);
    // You can "store the result" in your DB here...
} else {
    $current_account = false;
}
