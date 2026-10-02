<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';
require_once "../mp_functions/get_token_by_shop.php";   ///

require_once '../vendor/autoload.php';
use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;



// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_GET['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);


/*****************************************************************************************************************
 *****   НАстраиваем дату начала сбора и количество дней 
 **************************************************************************************************************** */
$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -15 day'));
$dop_days_query = 20;

echo "<h1> НОВЫЙ РАЗБОР ТОВАРОВ С РАЗБИВКОЙ ТОВАРОВ НА ОТПРАВЛЕНИЯ</h1>";
// ****************************************************************************************************************
// Запрашиваем данные по отправлениям за несколько дней
// ****************************************************************************************************************

$ozon   = new OzonClient($token, $client_id);
$orders = new OrderService($ozon);

$ArrayOrders = $orders->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);
   // $ArrayOrders = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging" , $dop_days_query);

// Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.
$kolvo_tovarov = 0;
$summa_tovarov = 0;


// echo "<pre>";
// print_r($ArrayOrders);
// die();

   foreach ($ArrayOrders as $posts) {
      // print_r($posts);
      // echo "<br>****************************************";
      foreach ($posts['products'] as $prods) 
        {
           $array_art[$prods['offer_id']]= @$array_art[$prods['offer_id']] + $prods['quantity'];
           $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
           $summa_tovarov= $summa_tovarov + $prods['price']['amount'] * $prods['quantity'];
        //    echo $prods['price']."<br>";
          $array_art_price[$prods['offer_id']] = array("price"    => $prods['price']['amount'],
                                                       "quantity" => $array_art[$prods['offer_id']],
                                                        "name"    => $prods['name']);
        }
 }
// die();
// echo "<pre>";
// print_r($array_art_price);

 // ****************************************************************************************************************
 //  Выводим таблицу с Количество купленно
// ****************************************************************************************************************
   // Ссылка для запуска сбора всех заказов
        $link ="controller/make_all_zakaz.php";
        
 if (isset($array_art_price)){
      echo "<h2>Сумма купленных товаров : $summa_tovarov руб. </h2>";
        echo "<h3>Количество  товаров : $kolvo_tovarov шт. </h3>";
          echo "<h2>Список купленных товаров</h2>";
           make_spisok_sendings_ozon_1С ($array_art_price);
   //  Выводим таблицу с Заказами
        echo "<h2>Перечень заказов</h2>";
        make_spisok_sendings_ozon ($ArrayOrders);


echo <<<HTML
<div class="ozon-form-wrapper">
    <h1 class="ozon-form-title">Первичный разбор</h1>

    <form action="$link" method="get" class="ozon-form">

        <p class="ozon-form-hint">
            Все заказы в статусе <b>ОЖИДАЮТ СБОРКИ</b>
           
        </p>

        <div class="ozon-field">
            <label for="number_order">Номер заказа</label>
            <input required type="text" id="number_order" name="number_order" value="" placeholder="Введите номер заказа">
        </div>

        <input hidden type="text" name="ozon_shop"          value="$shop_name">
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


 } else {
    echo "<h2>НЕТ ДАННЫХ ДЛЯ ВЫДАЧИ</h2>";
 }


require_once "main_ozon/footer.php";