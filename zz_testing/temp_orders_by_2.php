<?php
$offset = "";
require_once $offset . "../connect_db.php";
require_once $offset . "../mp_functions/ozon_api_functions.php";

/*****************************************************************
 * Получаем и систематизируем возвраты на озон
 **************************************************************/



echo "выводим количество заказов где больше одного товара Кантри Стандарт"."<br>";

    $client_id = $arr_tokens['ozon_anmaks']['id_market'];
    $token = $arr_tokens['ozon_anmaks']['token'];

// $date_from = "2025-01-01";
// $date_to ="2025-12-31";



// $temp_777['jan'] = get_month_data($token, $client_id, "2025-01-01", "2025-01-31");
// $temp_777['feb'] = get_month_data($token, $client_id, "2025-02-01", "2025-02-28");
// $temp_777['mar'] = get_month_data($token, $client_id, "2025-03-01", "2025-03-31");
// $temp_777['apr'] = get_month_data($token, $client_id, "2025-04-01", "2025-04-30");
// $temp_777['may'] = get_month_data($token, $client_id, "2025-05-01", "2025-05-31");
// $temp_777['jun'] = get_month_data($token, $client_id, "2025-06-01", "2025-06-30");
// $temp_777['jul'] = get_month_data($token, $client_id, "2025-07-01", "2025-07-31");
// $temp_777['avg'] = get_month_data($token, $client_id, "2025-08-01", "2025-08-31");
// $temp_777['sen'] = get_month_data($token, $client_id, "2025-09-01", "2025-09-30");
// $temp_777['oct'] = get_month_data($token, $client_id, "2025-10-01", "2025-10-31");
// $temp_777['noy'] = get_month_data($token, $client_id, "2025-11-01", "2025-11-30");
// $temp_777['dec'] = get_month_data($token, $client_id, "2025-12-01", "2025-12-31");


// file_put_contents('popopo.json' , json_encode($temp_777, JSON_UNESCAPED_UNICODE));

$temp_777 = file_get_contents("popopo.json");
$temp_777 = json_decode($temp_777, true);

// echo  "<pre>";
// print_r($temp_777);


// die;

foreach ($temp_777 as $month) {
foreach ($month as $orf){
    $temp[] = $orf;
}

}



// формируем заказы (заказ привязываем к номеру заказа)
foreach ($temp as $order) {
    $parts = explode('-', $order['posting']['posting_number']);
    array_pop($parts); // Удаляем последнюю часть
    $number_order =  implode('-', $parts);
    $temp_2 [$number_order][] = $order;
}


foreach ($temp_2 as $key => $pp) {
    if (count($pp) > 1) {
        $temp_3[$key] = $pp;
    }
}


foreach ($temp_3 as $key => $pp2) {
    foreach ($pp2 as $pp3) {

            $temp_4[$key][$pp3['items'][0]['name']] = @$temp_4[$key][$pp3['items'][0]['name']] + 1;
            // $temp_4[$key]['delivery_schema'] = $pp3['posting']['delivery_schema'];
    }
}




echo "<pre>";
// print_r($temp_3['08685737-0100']);







foreach ($temp_4 as  $order) {
    foreach ($order as $name=>$item) {
    //  echo "$item<br>";
$temp_5[$name][$item] = @$temp_5[$name][$item] + 1;
    }

    }




foreach ($temp_5 as &$bob) {

    unset($bob[1]);
    ksort($bob);
}

print_r($temp_5);



/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */
/********************************************************************************* */



function get_month_data($token, $client_id, $date_from, $date_to) {

// $date_to = "2023-09-19";
echo "Период запроса с ($date_from) по  ($date_to)<br>";
$ozon_link = 'v3/finance/transaction/list';
$send_data = array(
    "filter" => array(
        "date" => array (
            "from" => $date_from."T00:00:00.000Z",
            "to"=> $date_to."T00:00:00.000Z"
    ),
        "operation_type" => [],
        "posting_number" => "",
        "transaction_type" => "all"
    ),
    "page" => 1,
    "page_size" => 1000
);
$send_data = json_encode($send_data);

$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );


// если ошибка при обмене то выводим е
if (isset($res['message'])) {

    echo "<pre>";
    print_r($res);
    die('ОШИБКА ПРИ ЗАПРОСЕ');
    
}


$page_count = $res['result']['page_count'];
$row_count = $res['result']['row_count'];
echo $page_count ." ". $row_count."<br>";


for ($i=1; $i <=$page_count; $i ++) {
    $send_data = array(
        "filter" => array(
            "date" => array (
                "from" => $date_from."T00:00:00.000Z",
                "to"=> $date_to."T00:00:00.000Z"
        ),
            "operation_type" => [],
            "posting_number" => "",
            "transaction_type" => "all"
        ),
        "page" => $i,
        "page_size" => 1000
    );
    $send_data = json_encode($send_data);
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );
    $prod_array[] = $res['result']['operations'];
    // echo "*<br>";


}
// $prod_array = json_decode(file_get_contents("ozon_report_.json"), true);





foreach ($prod_array as $peros2) {
    foreach ($peros2 as $peros) {
    if ($peros['type'] == 'orders')
//   if (count($peros['items']) > 1) {
    $temp[] = $peros;
//   }

}}





return $temp;
}