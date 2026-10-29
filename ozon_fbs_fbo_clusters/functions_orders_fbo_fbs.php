<?php



/*****************************************************************************************************
 *   Запрашиваем перечень заказов ФБС за выбранные даты
 *****************************************************************************************************/
function query_FBS_orders_from_ozon ($token, $client_id, $date_from, $date_to) {

$send_data_array = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                 "to" =>  $date_to."T23:59:59.000Z",

                                "statuses" => ['awaiting_registration', 'acceptance_in_progress', 'awaiting_packaging', 'awaiting_deliver',
                                'delivering', 'delivered' , 'driver_pickup' , 'cancelled', 'awaiting_approve',
                                'arbitration' , 'client_arbitration', 'not_accepted' ],


                                                
                                 ),
                  "limit" =>  100,
                  "cursor" =>  '',
                  "sort_dir" => "ASC",
                  "translit" => false,
                  "with" => array(
                  "analytics_data" => true,
                  "legal_info" => true,
                  "financial_data" => true
                  )
);

       $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  

// запустили запрос на озона
do {
    $res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v4/posting/fbs/list' );
    $summ_data[]=$res['postings']; // положили первый массив и смотрим нужно ли еще искать
    $send_data_array['cursor'] = $res['cursor'];
    $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  
} while ($res['has_next']);

// делаем сплошной массив с заказами
foreach ($summ_data as $oneQueryData) {
    foreach ($oneQueryData as $oneData) {
        $ArrayOrders[]=$oneData;
    }
}

if (!isset($ArrayOrders)) {
   echo "Нет даных для выдачи";
   die();
}


return $ArrayOrders;
}


/*****************************************************************************************************
 *   Формируем массив для вывода все заказанных товаров
 *****************************************************************************************************/

function make_array_with_all_FBS_FBO_orders ($data_array) {

   // формируем массив продаж по ФБО
   foreach ($data_array as $item) {
      $article = mb_strtolower($item['products'][0]['offer_id']);
      $arr_article[$article]['count'] = @$arr_article[$article]['count']  + $item['products'][0]['quantity'];
      $arr_article[$article]['price'] = @$arr_article[$article]['price']  + $item['products'][0]['price']['amount'];
      
   }
return $arr_article;
}

/*****************************************************************************************************
 *   Запрашиваем перечень заказов ФБO за выбранные даты
 *****************************************************************************************************/
function query_FBO_orders_from_ozon ($token, $client_id, $date_from, $date_to) {

$send_data_array = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date_to."T23:59:59.000Z"
                ),
                     "limit" =>  100,
                     "cursor" =>  '',
                     "offset" =>  0,
                     "sort_dir" => "ASC",
                     "translit" => false,
                     "with" => array(
                     "analytics_data" => true,
                     "legal_info" => true,
                     "financial_data" => true
)
);

$json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  

// запустили запрос на озона
do {
    $res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v3/posting/fbo/list' );
    $summ_data[]=$res['postings']; // положили первый массив и смотрим нужно ли еще искать
    $send_data_array['cursor'] = $res['cursor'];
    $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  
} while ($res['has_next']);

// делаем сплошной массив с заказами
foreach ($summ_data as $oneQueryData) {
    foreach ($oneQueryData as $oneData) {
        $ArrayOrders[]=$oneData;
    }
}

if (!isset($ArrayOrders)) {
   return false;
}


return $ArrayOrders;
}

