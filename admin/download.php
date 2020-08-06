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
    $image_1 = 'sample url link';
    $image_2 = 'sample url link';
    $image_3 = 'sample url link';
    $image_4 = 'sample url link';
    $image_5 = 'sample url link';
    $item_desc = 'Item Description ' . $x;
    $SKU = 'Item SKU ' . $x;
    $currency = $currencycode;
    $price = 100 + $x;
    $stock_qty = 10 * $x;
    $stock_limited = $random_limit[array_rand($random_limit)];
    $variant1 = 'Red/Color/Size/M/Type/B';
    $variant2 = 'Blue/Color/Size/S/Type/A';
    $variant3 = 'Green/Color/Size/L/Type/A';

    $itemsRows = array($itemID,  $merchants_id,  $cats_id, $item_name, $image_1, $image_2, $image_3,  $image_4, $image_5, $item_desc, $SKU, $currency, $price, $stock_qty,$stock_limited,$variant1,$variant2,$variant3);
    fputcsv($fh_items,  $itemsRows);

    echo json_encode(['rows' => $itemsRows ]);    
  } 
  fclose($fh_items);



// foreach($result['Records'] as $orders) {
//     $orderId = $orders['Orders'][0]['ID'];
//     $invoiceId = $orders['InvoiceNo'];
//     $timestamp= date('d/m/Y H:i', $orders['Orders'][0]['CreatedDateTime']);
//     $merchantEmail =  $orders['Orders'][0]['MerchantDetail']['Email'];
//     $consumerEmail =  $orders['Orders'][0]['ConsumerDetail']['Email'];
//     $buyerDisplayName = $orders['Orders'][0]['ConsumerDetail']['DisplayName'];   
//     $paymentStatus = $orders['Orders'][0]['PaymentStatus'];  
//     $orderStatus = $orders['Orders'][0]['FulfilmentStatus']; 
//     $delInfo = $orders['Orders'][0]['CustomFields'][0]['Values'][0];
//     $delInfo = json_decode($delInfo,true);
//     $delName = $delInfo['DeliveryName'];
//     $delCost = $delInfo['DeliveryCost'];  
//     $subtotal = $orders['Total'];
//     $discounts = $orders['Orders'][0]['DiscountAmount'] != null ?  $orders['Orders'][0]['DiscountAmount'] : 0;
//     $adminFee = $orders['Fee'];
//     $grandTotal = $orders['Orders'][0]['GrandTotal'];

//      //pick up or delivery
//      $cartItemType = $orders['Orders'][0]['CartItemDetails'][0]['CartItemType'];

     
//      $delivery=  $cartItemType == 'delivery' ? $delName : '';
//      $pickUp = $cartItemType == 'pickup' ? $delName : '';
     
//     $invoiceRow =  array($invoiceId, $timestamp, $buyerDisplayName, $consumerEmail, $paymentStatus, $orderStatus, $delivery, $pickUp,  $subtotal, $delCost, $discounts, $adminFee, $grandTotal);
//     fputcsv($fh,  $invoiceRow);

//     foreach($orders['Orders'][0]['CartItemDetails'] as $itemDetails){
//         $itemId = $itemDetails['ItemDetail']['ID'];
//         $itemName = $itemDetails['ItemDetail']['Name'];

//         $url = $baseUrl . '/api/v2/items/' . $itemId ; 
//         $items = callAPI("GET", $admin_token['access_token'], $url, false);  
//         //$itemCategory = $items['Categories'][0]['Name'];
//         $categoryList = [];
//         foreach($items['Categories'] as $category) {
//             $categoryList[] = $category['Name'];
//         }
//         $item_categories =  implode("|",$categoryList);
//         $subCategoryName = $itemCategory != null ? $itemCategory : null;

//         $parentCategoryName= '';
//         $variantList = [];
//         foreach($itemDetails['ItemDetail']['Variants'] as $variant) {
//             $variantList[] = $variant['Name'];
//         }
       
//         $variantOption1 = count($variantList) ? $variantList[0]: null;    
//         $variantOption2 = count($variantList) >= 2 ? $variantList[1] : null;  
//         $variantOption3 = count($variantList) >= 3 ? $variantList[2] : null; 

//         $SKU = $itemDetails['ItemDetail']['SKU'];
//         $itemPrice = $itemDetails['ItemDetail']['Price'];
//         $qty = $itemDetails['Quantity'];

//          //populate items csv
//          $itemsRows = array($invoiceId,  $parentCategoryName,  $item_categories, $itemName, $variantOption1, $variantOption2, $variantOption3,  $SKU,  $itemPrice, $qty);
//          fputcsv($fh_items,  $itemsRows);

//     }
   
// }

// $rename = $timestamp . '.csv';
// fclose($fh);
// rename('item.csv', $rename);

// rename('invoice.csv', $rename);

// Timestamp ok
// Buyer display name	ok 
// Buyer Email	ok
// Payment Status ok
// Order Status	 ok 
// Shipping Method	ok
// Order Sub-total	ok
// Shipping Costs	ok
// Discounts	ok
// Admin Fees	ok
// Grand Total	ok

?>

