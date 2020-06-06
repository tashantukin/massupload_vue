<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$timestamp = date("d/m/Y H:i"); 
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();

$customFieldPrefix = getCustomFieldPrefix();
// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);
// Query to get package custom fields

//get all the merchants first
// then get all the items per merchant
// delete the items
// viola!

$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $admin_token['access_token'], $url, false);
$admin_id = $result['ID'];

$url =  $baseUrl . '/api/v2/admins/' . $admin_id .'/users/?role=merchant';
$merchantDetails =  callAPI("GET", $admin_token['access_token'], $url, false);
    foreach($merchantDetails['Records'] as $merchants) {
        $merchantID =  $merchants['ID'];

        $url =  $baseUrl . '/api/v2/items?sellerID='. $merchantID;
        $itemDetails = callAPI("GET", $admin_token['access_token'], $url, false);
        foreach($itemDetails['Records'] as $items){
            $itemid =  $items['ID'];

        $url =  $baseUrl . '/api/v2/merchants/' . $merchantID . '/items/' . $itemid;
        $itemDetails = callAPI("DELETE", $admin_token['access_token'], $url);

    }

}
?>