<?php

$send_data = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date_end."T23:59:59.000Z"
                ),
"limit" =>  1000,
"offset" =>  0,
"translit" => true,
"with" => array(
"analytics_data" => true,
"financial_data" => true
)
);




$arr_ooo = make_array_for_print ($token_ozon, $client_id_ozon,$send_data);
$arr_ip = make_array_for_print ($token_ozon_ip, $client_id_ozon_ip,$send_data);

// уберем лишние артикулы 
// echo "<pre>";
// print_r($arr_ip);
// выбирем из общего массива только то, куда мы можем вставить данные 

$number_arr_sell_count = count($array_sell);

foreach ($arr_ooo as $date_fbo=>$items) {
   foreach ($items as $article=>$item_count) {
      if ((@$array_sell['shop_name'] != 'ozon_anmaks') AND 
          (@$array_sell['1c_article'] != $article) AND 
          (@$array_sell['date'] != $date_fbo) AND 
          (@$array_sell['type_sklad'] != 'fbo')) {
            $array_sell[$number_arr_sell_count]['shop_name'] = 'ozon_anmaks';
            $array_sell[$number_arr_sell_count]['1c_article'] = $article;
            $array_sell[$number_arr_sell_count]['fbo_sell'] = $item_count;
            $array_sell[$number_arr_sell_count]['date'] = $date_fbo;
            $array_sell[$number_arr_sell_count]['type_sklad'] = 'fbo';
            $number_arr_sell_count++;
          }
  }
}

unset($items);
foreach ($arr_ip as $date_fbo=>$items) {
   foreach ($items as $article=>$item_count) {
      if ((@$array_sell['shop_name'] != 'ozon_ip_zel') AND 
          (@$array_sell['1c_article'] != $article) AND 
          (@$array_sell['date'] != $date_fbo) AND 
          (@$array_sell['type_sklad'] != 'fbo')) {
            $array_sell[$number_arr_sell_count]['shop_name'] = 'ozon_ip_zel';
            $array_sell[$number_arr_sell_count]['1c_article'] = $article;
            $array_sell[$number_arr_sell_count]['fbo_sell'] = $item_count;
            $array_sell[$number_arr_sell_count]['date'] = $date_fbo;
            $array_sell[$number_arr_sell_count]['type_sklad'] = 'fbo';
            $number_arr_sell_count++;
          }
  }
}






/// функция запрашивает данные по ФБО за выбранные даты
function make_array_for_print ($token_anmaks, $client_id_anmaks,$send_data) {
$priznak_all_orders = 0;
$i=0;
do {
   $json_data_send = json_encode($send_data);
   $temp_res = send_injection_on_ozon($token_anmaks, $client_id_anmaks, $json_data_send, 'v2/posting/fbo/list');
// Записываем все продажи в массив 
        foreach ($temp_res['result'] as $temp_item) {
            $res[] = $temp_item;
        }
    
  
   if (isset($temp_res["result"][999])) {
    $send_data["offset"] = $send_data["offset"]  + 1000; 
   } else {
    $priznak_all_orders = 1;
   }
$i++;
} while ($priznak_all_orders == 0);


if (!isset($res)) {
   echo "Нет даных для выдачи";
   die();
}

foreach ($res as $item) {
   $article = mb_strtolower($item['products'][0]['offer_id']);
  
  $date_order = (new DateTime($item['created_at']))->format('Y-m-d');

   $arr_article[$date_order][$article] = @$arr_article[$date_order][$article]  + 1;
}


return $arr_article;
}

