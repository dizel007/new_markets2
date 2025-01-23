<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";

require_once "ozon_return_functions.php";

//  Получает даты бесплатного хранения до и после сегодня за 20 дней
$date_now = date('Y-m-d');
$date_start = date('Y-m-d', strtotime($date_now . " - 20 day"));
$date_finish = date('Y-m-d', strtotime($date_now . " + 20 day"));


$send_data_arr = json_encode(array(
    "filter" => array(
        "logistic_return_date" => array(
            "time_from" => $date_start . "T00:00:00Z",
            "time_to" => $date_finish . "T23:59:59Z"
        ),
       
    ),
    "limit" => 500,
    "last_id" => 0
));



// $ozon_dop_url = "v3/returns/company/fbs";
$ozon_dop_url = "v1/returns/list";
// получаем массив возвратов 
$arr_ozon_ooo_returns = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data_arr, $ozon_dop_url);


// echo "<pre>";
// print_r($arr_ozon_ooo_returns['returns'][0]);


// формируем массив с датами, когда были возвраты ДЛЯ ООО
foreach ($arr_ozon_ooo_returns['returns'] as $itemss) {
    $date_n = date('Y-m-d', strtotime($itemss['logistic']['final_moment'])); // дата получения заказа ПРОДАВЦОМ
    $arr_date_n[$date_n] = $date_n;
}


// формируем массив с датами, когда были возвраты ДЛЯ ИП
$arr_ozon_ip_returns = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data_arr, $ozon_dop_url);

foreach ($arr_ozon_ip_returns['returns'] as $itemss) {
    $date_n = date('Y-m-d', strtotime($itemss['logistic']['final_moment'])); // дата получения заказа ПРОДАВЦОМ
    $arr_date_n[$date_n] = $date_n;
}
//  убираем дублирующие даты
asort($arr_date_n);

// echo "<pre>";
// print_r($arr_date_n);
// die();


if (isset($_GET['need_date'])) {
    $need_date = $_GET['need_date'];
} else {
    $need_date = date('Y-m-d');
}

echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>Выберите дату забора возвартов</label>
<select required name="need_date">
HTML;

// выводим выпадающий список с датами, когда были возвраты
foreach ($arr_date_n as $select_date) {
    if($select_date == $need_date) {
echo "<option selected value = \"$select_date\">".$select_date."</option>";

    } else {
echo "<option value = \"$select_date\">".$select_date."</option>";
    }
}
echo <<<HTML
</select>
<input type="submit"  value="START">
</form>
HTML;



echo "<link rel=\"stylesheet\" href=\"css/main_ozon.css\">";


/// Возвраты по ООО ТД АНМ
echo "<h2>Возварты озон ООО ТД Анмакс за $need_date</h2>";

$arr_new_date_returns = print_return_table($pdo, $arr_ozon_ooo_returns, $need_date, 'ozon_anmaks');

if ($arr_new_date_returns != 0) {
    foreach ($arr_new_date_returns as $key =>$ret_item) {
        $sum_return_array[$key]['quantity'] = @$sum_return_array[$key]['quantity']  + $ret_item['quantity'];
        $sum_return_array[$key]['price'] = @$sum_return_array[$key]['price']  + $ret_item['price'];
    }
}

/// Возвраты по ИП ЗЕЛ 
echo "<h2>Возварты озон ИП Зел за $need_date</h2>";
    $arr_new_date_returns = print_return_table($pdo, $arr_ozon_ip_returns, $need_date, 'ozon_ip_zel');
    if ($arr_new_date_returns != 0) {
    foreach ($arr_new_date_returns as $key =>$ret_item) {
        $sum_return_array[$key]['quantity'] = @$sum_return_array[$key]['quantity']  + $ret_item['quantity'];
        $sum_return_array[$key]['price'] = @$sum_return_array[$key]['price']  + $ret_item['price'];
    }
}

if (isset($sum_return_array)) {
    echo "<table class = \"prod_table_small\">";
    echo "<tr>
    <td>пп</td>
    <td>Артикул</td>
    <td>количество</td>
    </tr>";
        $i = 1;
    foreach ($sum_return_array as $key => $item) {
        echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $key . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
        echo "</tr>";
        $i++;
    }

echo "</table>";

echo "<a href=\"excel_returns.php?our_date=$need_date\"> Download excel spisok returns</a>";
}
die();



