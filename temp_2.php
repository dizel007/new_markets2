<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/wb_api_functions.php";


 // Запрос поисковых фраз по товару
 
 

//  echo $token_wb;
//  die();
/**
 * https://dev.wildberries.ru/openapi/analytics#tag/Poiskovye-zaprosy/paths/~1api~1v2~1search-report~1table~1details/post
 */
    $link_wb = "https://seller-analytics-api.wildberries.ru/api/v2/search-report/product/search-texts";

    $data = '{
"currentPeriod": {
"start": "2024-10-01",
"end": "2024-10-31"
},
"pastPeriod": {
"start": "2024-09-01",
"end": "2024-09-30"
},
"nmIds": [
215488593,
215495142,
216952104,
9376932
],
"topOrderBy": "openToCart",
"orderBy": {
"field": "avgPosition",
"mode": "asc"
},
"limit": 30
}';
    
  $ff =  light_query_with_data($token_wb, $link_wb, $data);

  echo "<pre>";

  print_r($ff);