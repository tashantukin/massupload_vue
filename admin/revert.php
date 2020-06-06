<?php

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i"); 

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);
// Query to get package custom fields
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
$packageCustomFields = callAPI("GET", null, $url, false);
$csvdata= $content['data'];

function get_http_response_code($domain1) {
  $headers = get_headers($domain1);
  return substr($headers[0], 9, 3);
}


$csv = array_map('str_getcsv', $csvdata);

foreach($csv as $line) {

    //category check
   
    $categories  = !strlen($line[3]) == 0 ? explode('/', $line[3]) : null;
    $allcategories = [];

  
    foreach($categories as $category) {
      $allcategories[] = array("'ID'" . "=> " . '"' . $category . '"');
      // echo json_encode(['result' => $category]);   
      // echo json_encode(['result' => $allcategories]);   
    }
// try {
    echo json_encode(['allcategories' => $allcategories]);   
   
    $item_details = array('SKU' =>  $line[11], 
    'Name' => $line[4],
    'BuyerDescription' => $line[10],
    'SellerDescription' => $line[10],
    'Price' => $line[13],
    'PriceUnit' => null,
    'StockLimited' => $line[15],
    'StockQuantity' =>  $line[14],
    'IsVisibleToCustomer' => true,
    'Active' => true,
    'IsAvailable' => '',
    'CurrencyCode' =>  $line[12],
    'Categories' =>  [ array('ID' => '768a4152-fd27-4a08-8023-c6ebc64ec659')],
     // $allcategories,
    // [ array('ID' => '768a4152-fd27-4a08-8023-c6ebc64ec659')]
    'ShippingMethods'  => null, 
    // [ array( 'ID' => '')],
    'PickupAddresses' => null, 
    // [ array( 'ID' => '')],
    'Media' => [ array('MediaUrl' => $line[5])],
    'Tags' => null, 
    'ChildItems' => null
    //  [ 
    //     array('Variants' => [ array('ID' => '', 'Name' => '', 'GroupID' => '', 'GroupName' => '', 'PriceChange'=>'', 'SortOrder' => '')]
    //     )]
);



echo json_encode(['itemdetails' => $item_details]);       
    $url =  $baseUrl . '/api/v2/merchants/'. $line[1] .'/items';
    echo json_encode(['url' => $url]);        
  $result =  callAPI("POST",$admin_token['access_token'], $url, $item_details);
//    print_r($result);
$get_http_response_code = get_http_response_code($url);

echo json_encode(['response' => $get_http_response_code ]);       
   error_log(['result' => $result]);       
  // }catch(Exception $e) {
  //   echo json_encode(['Message: ' .$e->getMessage()]);
  // }
   

}
// }



?>