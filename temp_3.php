<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/wb_api_functions.php";


/******************************
 *  Тестируем ВБ Возвраты
 ******************************/



echo "<br> Отчёт о возвратах и перемещении товаров <br>" ;

$link_wb ="https://seller-analytics-api.wildberries.ru/api/v1/analytics/goods-return?dateFrom=2025-07-18&dateTo=2025-08-18";

 $res = light_query_without_data($token_wb, $link_wb);

 echo "<pre>";
 print_r($res);

