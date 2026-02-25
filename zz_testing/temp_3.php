<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/wb_api_functions.php";


/******************************
 *  ВБ отгрузка в ПВЗ (создание коробок)
 ******************************/



// echo "<br> $token_wb <br>" ;
echo "<br> TEMP_3 <br>" ;





// $link_wb ="https://marketplace-api.wildberries.ru/api/v3/supplies/WB-GI-182058219";
$link_wb ="https://marketplace-api.wildberries.ru/api/v3/supplies/WB-GI-182058219/trbx";
$data = array ("amount"=>1);

$link_wb ="https://marketplace-api.wildberries.ru/api/v3/supplies/WB-GI-182058219/trbx/stickers?type=svg";

echo "<pre>";
$data_2 = array ("trbxIds" => 
    array("WB-MP-22072661")
);

print_r($data_2);

print_r(json_encode($data_2));
// die();


//  $res = light_query_without_data($token_wb, $link_wb);
 $res = light_query_with_data($token_wb, $link_wb, $data_2);
 echo "<pre>";
 print_r($res);

$filedata = base64_decode($res['stickers'][0]['file']);
file_put_contents('2.svg', $filedata, FILE_APPEND); // добавляем данные в файл с накопительным итогом