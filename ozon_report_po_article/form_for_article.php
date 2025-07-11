<?php


if (($date_from == false) or ($date_to == false)) {
    die ('Нужно выбрать даты');
    } 

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

// Запрашиваем все страницы отчета
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
}

// echo "<pre>";
// print_r($prod_array);