<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
// date_default_timezone_set($timezone_name);
$timestamp = date("d/m/Y H:i"); 

$baseUrl = getMarketplaceBaseUrl();
$GLOBALS['baseUrl'] = $baseUrl ;
$admin_token = getAdminToken();
$GLOBALS['admin_token '] = $admin_token;
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
$categoryfound = 0;
function isCategoryValid($catID){
  $url =  $GLOBALS['baseUrl'] . '/api/v2/categories';
  $result =  callAPI("GET", $GLOBALS['admin_token']['access_token'], $url, false);
  foreach($result['Records'] as $categoryId) {
    // echo json_encode(['cat1' =>  $catID]); 
      if($categoryId['ID'] == $catID) { 
          return true;
      }
         continue;
  }
}
function isShippingValid($shippingId, $merchantId) {
  $url =  $GLOBALS['baseUrl'] . '/api/v2/merchants/' . $merchantId . '/shipping-methods/';
  $result =  callAPI("GET", $GLOBALS['admin_token']['access_token'], $url, false);
  foreach($result['Records'] as $shippingID) {
    if($shippingID['ID'] == $shippingId) {
        return true;
    }
        continue;
  }
}

$csv = array_map('str_getcsv', $csvdata);
array_shift($csv); //remove the headers
  $upload_result = [];
  
  $upload_counter = 0;
  $success_counter = 0;
  $failed_counter = 0;

foreach($csv as $line) {
   $upload_error = [];
   $category_error_count= 0;
   $shipping_error_count= 0;
   $allimages = [];
   $allvariants = [];
   
   //category check
   $categories  = !strlen($line[3]) == 0 ? explode('/', $line[2]) : null;
   $allcategories = [];
    foreach($categories as $category) {
      isCategoryValid($category) ? $allcategories[] = array('ID'  =>  $category) : $category_error_count++; $failed_counter++; 
    }
    //shipping details check
    $shipping = !strlen($line[17]) == 0 ? explode('/', $line[16]) : null;
    $allshipping = [];
    foreach($shipping as $shippingId) {
      isShippingValid($shippingId,$line[1]) ? $allshipping[] = array('ID' => $shippingId) : $shipping_error_count++; 
    }

   //image check
  //  $imageslist = [5,6,7,8,9];
   foreach(range(4, 8) as $eachimage) {
     !strlen($eachimage) == 0 ?  $allimages[] = array('MediaUrl' => $eachimage) : '';
    }
    //check if no images found
    empty($allimages) ? $upload_error[] = 'No Media found.' : '';
    $category_error_count != 0 ? $upload_error[] = $category_error_count . ' Category ID error/s found' : '';
    $shipping_error_count != 0 ? $upload_error[] = $shipping_error_count . ' Shipping ID error/s found' : '';

    //variants check
    // $variantlist = [18,19,20];
    foreach (range(17, 19) as $eachvariant) {
      $variants = !strlen($line[$eachvariant]) == 0 ? explode('/', $line[$eachvariant]) : null;  
      $variants != null ?  $allvariants[] = array('Variants' => [ array('ID' => '', 'Name' => $variants[0], 'GroupName' => $variants[1], 'SortOrder' => $variants[3])],  'SKU' => 'random', 'Price' => $variants[2], 'StockLimited' => false, 'StockQuantity' => $variants[2] ) : ''; 
    }
    //return error on each item
   
    // $upload_result[] = array('Name' => $line[3],);
    // $upload_result[0]['Error']  = $upload_error;
        $item_details = array('SKU' =>  $line[10], 
        'Name' => $line[3],
        'BuyerDescription' => $line[9],
        'SellerDescription' => $line[9],
        'Price' => $line[12],
        'PriceUnit' => null,
        'StockLimited' => $line[14],
        'StockQuantity' =>  $line[13],
        'IsVisibleToCustomer' => true,
        'Active' => true,
        'IsAvailable' => '',
        'CurrencyCode' =>  $line[11],
        'Categories' =>  $allcategories,
        'ShippingMethods'  => $allshipping,
        'PickupAddresses' => [ array('ID' => $line[15])], 
        'Media' => $allimages,
        'Tags' => null, 
        'ChildItems' =>  $allvariants
     );

      $url =  $baseUrl . '/api/v2/merchants/'. $line[1] .'/items';
      $result =  callAPI("POST",$admin_token['access_token'], $url, $item_details);   
      $result1 = json_encode(['err' => $result]);
      $upload_counter++;
      $itemresult =  array_key_exists("Message", $result) ? $result['InnerErrors'][0]['Message'] : 'No Error'; //if meerchant ID is invalid
    //  echo json_encode(['result' => $result]);  
      $upload_result[] = array('Name' => $line[3], 'Error' => $upload_error, 'code' =>  $itemresult);
    //  array_key_exists('Message',
}
  $upload_result[0]['Total'] = $upload_counter;
       echo json_encode(['result' => $upload_result]);  
    
// }



?>