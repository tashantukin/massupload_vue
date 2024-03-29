<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
$packageCustomFields = callAPI("GET", null, $url, false);

//get admin ID
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $admin_token['access_token'], $url, false);
$admin_id = $result['ID'];

$random_merchant = [];
$random_category = [];
$random_limit = ['True','False'];

$currencycode = $marketplaceInfo['CurrencyCode'];
// print_r($currencycode);

// 1. get all merchant id's
$url = $baseUrl . '/api/v2/admins/' . $admin_id .'/users/?role=merchant';
$result = callAPI("GET", $admin_token['access_token'], $url, false);
foreach($result['Records'] as $merchants) {

    $merchant_id =  $merchants['ID'];
    // print_r($merchant_id);
   $random_merchant[] = $merchant_id;
   
}

var_dump($random_merchant);
//2. get all category id's
$url = $baseUrl . '/api/v2/admins/' . $admin_id .'/categories';
$result = callAPI("GET", $admin_token['access_token'], $url, false);
foreach($result['Records'] as $category) {
    $category_id =  $category['ID'];
   $random_category[] = $category_id;
   
}
var_dump($random_category);

$dirItems =  realpath("downloads/example.csv");

$fh_items = fopen($dirItems, 'w');
echo json_encode(['cat' => $random_category ]);    
echo json_encode(['merch' => $random_merchant ]);   
// save the invoice headers
fputcsv($fh_items, array('Item ID', 'Merchant ID', 'Category ID', 'Item Name', 'Image 1', 'Image 2', 'Image 3', 'Image 4', 'Image 5', 'Item Description', 'SKU', 'Currency', 'Price', 'Stock Quantity', 'Stock Limited','Variant 1', 'Variant 2', 'Variant 3'));

for ($x = 1; $x <= 10; $x++) {
   
    $itemID = '*';
    $merchants_id = $random_merchant[array_rand($random_merchant)];
    $cats_id = $random_category[array_rand($random_category)];
    $item_name = 'Sample Item ' . $x;
    $image_1 = 'https://www.pngkit.com/png/full/129-1298403_upload-files-bank.png';
    $image_2 = 'https://images.goodsmile.info/cgm/images/product/20200720/9800/72596/large/ecf483c7e31939c37dc3be0d0894811b.jpg';
    $image_3 = 'sample url link';
    $image_4 = 'sample url link';
    $image_5 = 'sample url link';
    $item_desc = 'Item Description ' . $x;
    $SKU = 'Item SKU ' . $x;
    $currency = $currencycode;
    $price = 100 + $x;
    $stock_qty = 10 * $x;
    $stock_limited = $random_limit[array_rand($random_limit)];
    $variant1 = 'Color/Red/Size/M/Type/B/10';
    $variant2 = 'Color/Blue/Size/S/Type/A/10';
    $variant3 = 'Color/Green/Size/L/Type/A/20';

    $itemsRows = array($itemID,  $merchants_id,  $cats_id, $item_name, $image_1, $image_2, $image_3,  $image_4, $image_5, $item_desc, $SKU, $currency, $price, $stock_qty,$stock_limited,$variant1,$variant2,$variant3);
    fputcsv($fh_items,  $itemsRows);

    echo json_encode(['rows' => $itemsRows ]);    
  } 
  fclose($fh_items);

?>

