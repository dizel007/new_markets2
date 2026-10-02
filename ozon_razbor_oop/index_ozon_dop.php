<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';
require_once "../mp_functions/get_token_by_shop.php"; 
require_once '../vendor/autoload.php';

use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;


echo <<<HTML

<img src="../pics/ozon.jpg">
<link rel="stylesheet" href="css/main_ozon.css">
HTML;



// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$ozon_shop = $_GET['ozon_shop'];
}

// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $ozon_shop);

/*****************************************************************************************************************
 *****   НАстраиваем дату начала сбора и количество дней 
 **************************************************************************************************************** */
$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -15 day'));
$dop_days_query = 20;

/*****************************************************************************************************************
 *****   Форма для вводы данных 
 **************************************************************************************************************** */

echo <<<HTML
<h1>Данные по ОЗОН : $ozon_shop</h1>
<h1>ДОПОЛНИТЕЛЬНО</h1>
<hr>
   <h1>Все заказы в статусе ОЖИДАЮТ ОТГУЗКИ начиная с $date_query_ozon</h1>

HTML;


/*****************************************************************************************************************
 *****  если есть Дата поиска, то начинаем вычитывать данные с сайта ОЗОН
 **************************************************************************************************************** */


$ozon   = new OzonClient($token, $client_id);
$orders = new OrderService($ozon);




if (isset($date_query_ozon)) {
    if ($date_query_ozon <> '') {
        // получаем массив всех отправления на эту дату
$ArrayOrders = $orders->getUnfulfilledFbs($date_query_ozon, 'awaiting_deliver', $dop_days_query);

        // $ArrayOrders = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_deliver", $dop_days_query);


        // Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.
        $kolvo_tovarov = 0;
        $summa_tovarov = 0;

        foreach ($ArrayOrders as $posts) {
            foreach ($posts['products'] as $prods) {
                $array_art[$prods['offer_id']] = @$array_art[$prods['offer_id']] + $prods['quantity'];
                $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
                $summa_tovarov = $summa_tovarov + $prods['price']['amount'] * $prods['quantity'];
                //    echo $prods['price']."<br>";
                $array_art_price[$prods['offer_id']] = array(
                    "price"    => $prods['price']['amount'],
                    "quantity" => $array_art[$prods['offer_id']],
                    "name"    => $prods['name']
                );
            }
        }

        //  Выводим таблицу с Количество купленно
        if (isset($array_art_price)) {
            echo "<h3>Список купленных товаров</h3>";
               make_spisok_sendings_ozon_1С($array_art_price);
            echo "<h3>Сумма купленных товаров : $summa_tovarov руб. </h3>";
            echo "<h3>Количество  товаров : $kolvo_tovarov шт. </h3>";
            //  Выводим таблицу с Заказами
            echo "<h2>Перечень заказов</h2>";
            make_spisok_sendings_ozon($ArrayOrders);
            // Ссылка для запуска сбора всех заказов
            $link = "controller/make_etikets_for_all_dopX_2.php";

            echo <<<HTML
<div class="ozon-form-wrapper">
    <h1 class="ozon-form-title">Дополнительно</h1>

    <form action="$link" method="get" class="ozon-form">

        <p class="ozon-form-hint">
            Все заказы в статусе <b>ОЖИДАЮТ ОТГРУЗКИ</b>
        </p>

        <div class="ozon-field">
            <label for="number_order">Номер заказа</label>
            <input required type="text" id="number_order" name="number_order" value="" placeholder="Введите номер заказа">
        </div>

        <input hidden type="text" name="ozon_shop"          value="$ozon_shop">
        <input hidden type="text" name="date_query_ozon"    value="$date_query_ozon">
        <input hidden type="text" name="dop_days_query"     value="$dop_days_query">
        <input hidden type="text" name="now_date_razbora"   value="$now_date_razbora">

        <div id="down_input" class="LockOff ozon-form-actions">
            <input type="submit" class="ozon-btn" value="СОБРАТЬ выбранную дату!" onclick="alerting();">
        </div>

        <div id="OnLock_textLockPane" class="LockOn ozon-lock-pane">
            <span class="ozon-spinner"></span>
            Обрабатываем запрос…
        </div>
    </form>
</div>

<script type="text/javascript" src="js/js_functions.js"></script>
HTML;


            // echo "Собрать все Заказы<a href=\"$link\">*СТАРТ*</a> ";
        } else {
            echo "<h2>НЕТ ДАННЫХ ДЛЯ ВЫДАЧИ</h2>";
        }
    }
}



require_once "main_ozon/footer.php";
