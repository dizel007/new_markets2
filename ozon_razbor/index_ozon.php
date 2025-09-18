<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';

$get_shop_name = $_GET['shop_name'];
if ($get_shop_name == 'ozon_anmaks' ) {
    $token_ozon =  $token_ozon;
    $client_id_ozon = $client_id_ozon;

} elseif  ($get_shop_name == 'ozon_ip_zel' ) {
    $token_ozon =  $token_ozon_ip;
    $client_id_ozon = $client_id_ozon_ip;
} else {
    die('Магазин не нашли');
}

/*****************************************************************************************************************
 *****   НАстраиваем дату начала сбора и количество дней 
 **************************************************************************************************************** */
$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -15 day'));
$dop_days_query = 20;

echo "<h1>ПЕРВИЧНЫЙ РАЗБОР ТОВАРОВ</h1>";
// ****************************************************************************************************************
// Запрашиваем данные по отправлениям за несколько дней
// ****************************************************************************************************************
   $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging" , $dop_days_query);

// Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.
$kolvo_tovarov = 0;
$summa_tovarov = 0;

   foreach ($res['result']['postings'] as $posts) {

      foreach ($posts['products'] as $prods) 
        {
           $array_art[$prods['offer_id']]= @$array_art[$prods['offer_id']] + $prods['quantity'];
           $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
           $summa_tovarov= $summa_tovarov + $prods['price'] * $prods['quantity'];
        //    echo $prods['price']."<br>";
          $array_art_price[$prods['offer_id']] = array("price"    => $prods['price'],
                                                       "quantity" => $array_art[$prods['offer_id']],
                                                        "name"    => $prods['name']);
        }
 }

 // ****************************************************************************************************************
 //  Выводим таблицу с Количество купленно
// ****************************************************************************************************************

 if (isset($array_art_price)){
       echo "<h2>Сумма купленных товаров : $summa_tovarov руб. </h2>";
       echo "<h2>Список купленных товаров</h2>";

        make_spisok_sendings_ozon_1С ($array_art_price);

   //  Выводим таблицу с Заказами
        echo "<h2>Перечень заказов</h2>";
        make_spisok_sendings_ozon ($res['result']['postings']);
   // Ссылка для запуска сбора всех заказов
        $link ="controller/make_all_zakaz.php";

echo <<<HTML
        <form action="$link" method="get">
         <label for="number_order">Номер заказа</label>
          <input required type="text" name="number_order" value="">
        
          <input required hidden type="text" name="ozon_shop" value="$get_shop_name">
          <input required hidden type="text" name="date_query_ozon" value="$date_query_ozon">
          <input required hidden type="text" name="dop_days_query" value="$dop_days_query">
          <input required hidden type="text" name="now_date_razbora" value="$now_date_razbora">
        
        <br><br>
        <div id="down_input" class="LockOff">
             <input type="submit" value="СОБРАТЬ  выбранную дату!" onclick="alerting();"> 
        </div>  
        <div id="OnLock_textLockPane" class="LockOn">
             Обрабатываем запрос.........
        </div>  
       
         
        </form>

     
<script type="text/javascript" src="js/js_functions.js"></script>

HTML;

 } else {
    echo "<h2>НЕТ ДАННЫХ ДЛЯ ВЫДАЧИ</h2>";
 }


require_once "main_ozon/footer.php";