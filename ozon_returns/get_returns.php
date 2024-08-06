<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";


// // ОЗОН АНМКАС
// $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
// $token_ozon = $arr_tokens['ozon_anmaks']['token'];
// // озон ИП зел
// $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
// $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];


//  Получает даты бесплатного хранения до и после сегодня за 20 дней
$date_now = date('Y-m-d');
$date_start = date('Y-m-d', strtotime($date_now . " - 20 day"));
$date_finish = date('Y-m-d', strtotime($date_now . " + 20 day"));

// формируем запрос на эти даты
$send_data_arr = json_encode(array(
    "filter" => array(
        "last_free_waiting_day" => array(
            "time_from" => $date_start . "T00:00:00Z",
            "time_to" => $date_finish . "T23:59:59Z"
        ),


        "status" => "returned_to_seller"
    ),
    "limit" => 1000,
    "last_id" => 0
));
$ozon_dop_url = "v3/returns/company/fbs";

// получаем массив возвратов 
$arr_ozon_ooo_returns = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data_arr, $ozon_dop_url);

foreach ($arr_ozon_ooo_returns['returns'] as $itemss) {
    $date_n = date('Y-m-d', strtotime($itemss['returned_to_seller_date_time'])); // дата получения заказа ПРОДАВЦОМ
    $arr_date_n[$date_n] = $date_n;
}

// echo "<pre>";
// print_r($arr_ozon_ooo_returns);


$arr_ozon_ip_returns = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data_arr, $ozon_dop_url);
foreach ($arr_ozon_ip_returns['returns'] as $itemss) {
    $date_n = date('Y-m-d', strtotime($itemss['returned_to_seller_date_time'])); // дата получения заказа ПРОДАВЦОМ
    $arr_date_n[$date_n] = $date_n;
}
asort($arr_date_n);

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
echo "<h1>Возварты озон ООО ТД Анмакс за $need_date</h1>";
print_return_table($pdo, $arr_ozon_ooo_returns, $need_date, 'ozon_anmaks');


echo "<h1>Возварты озон ИП Зел за $need_date</h1>";
print_return_table($pdo, $arr_ozon_ip_returns, $need_date, 'ozon_ip_zel');

die();




function print_return_table($pdo, $arr_ozon_returns, $need_date, $name_db)
{
    $i = 0;
    // Формируем массив и добавляем артикул из нашей базы данных
    foreach ($arr_ozon_returns['returns'] as $items) {
        $date_n = date('Y-m-d', strtotime($items['returned_to_seller_date_time'])); // дата получения заказа ПРОДАВЦОМ
        $arr_date[$i]['sku'] =  $items['sku'];
        $arr_date[$i]['quantity'] =  $items['quantity'];
        $arr_date[$i]['returned_to_seller_date_time'] =  $date_n;
        $arr_date[$i]['product_name'] =  $items['product_name'];
        $arr_date[$i]['posting_number'] =  $items['posting_number'];
        $arr_date[$i]['price'] =  $items['price'];
        $arr_date[$i]['return_reason_name'] =  $items['return_reason_name'];

        $sth = $pdo->prepare("SELECT main_article FROM `$name_db` WHERE `sku` = :sku");
        $sth->execute(array('sku' => $items['sku']));
        $article = $sth->fetch(PDO::FETCH_COLUMN);

        $arr_date[$i]['article'] =  $article; // цепляем артикул товара


        $i++;
    }

    unset($items);

    // формируем обощенный массив артикул ключ - количество и цены значения
    $sum_quantity = 0;
    $sum_price = 0;
    foreach ($arr_date as $items) {
        if ($items['returned_to_seller_date_time'] == $need_date) {
            $arr_new_date_returns[$items['article']]['quantity'] = @$arr_new_date_returns[$items['article']]['quantity'] +  $items['quantity'];
            $sum_quantity += $items['quantity'];
            $arr_new_date_returns[$items['article']]['price'] = @$arr_new_date_returns[$items['article']]['price'] +  $items['price'];
            $sum_price += $items['price'];
        }
    }



// рисуем таблицу заказов как в акте
    echo "<table class = \"prod_table\">";
    echo "<tr>
        <td>пп</td>
        <td>номер отправления</td>
        <td>Дата забора</td>
        <td>Название</td>
        <td>Артикул</td>
        <td>количество</td>
        <td>цена</td>
     </tr>";
    $i = 1;
    foreach ($arr_date as $item) {
        if ($item['returned_to_seller_date_time'] == $need_date) {

            echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $item['posting_number'] . "</td>";
            echo "<td>" . $item['returned_to_seller_date_time'] . "</td>";
            echo "<td>" . $item['product_name'] . "</td>";
            echo "<td>" . $item['article'] . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . $item['price'] . "</td>";


            echo "</tr>";
            $i++;
        }
    }
// рисуем обощенную таблицу артикул - количество
    echo "</table>";


    echo "<table class = \"prod_table_small\">";
    echo "<tr>
    <td>пп</td>
    <td>Артикул</td>
    <td>количество</td>
 </tr>";
    $i = 1;
    foreach ($arr_new_date_returns as $key => $item) {
        echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $key . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
        echo "</tr>";
        $i++;
    }

    echo "</table>";
    echo "Дата забора = " . $need_date . "<br>";
    echo "Количество товаров = " . $sum_quantity . "<br>";
    echo "Сумма товаров = " . $sum_price . "<br>";
    echo  "<br><br>";

}
