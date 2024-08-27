<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';






echo <<<HTML

<img src="../pics/ozon.jpg">

HTML;

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



if (isset($_GET['date_query_ozon'])) {
    $date_query_ozon = $_GET['date_query_ozon'];  
    $dop_days_query = $_GET['dop_days_query'];

}else {
    // $date_query_ozon =''; 
    $date_query_ozon = date('Y-m-d'); 
    $dop_days_query = 0;

}

echo <<<HTML
<h2>ОЗОН ООО ТД АНМАКС</h2>
<h2>Найти заказы для комплектации по дате</h2>
    <div>
        <form method="get" action="#">
        <div id="up_input" class="LockOff">
            <input  hidden type="text" name="shop_name" value="$get_shop_name">
            <input  required type="date" name="date_query_ozon" value="$date_query_ozon">
            <input type="submit" value="Найти заказы на выбранную дату">
        
        </div>
        <br>
        <label for="dop_days_query">Количество дополнительных дней</label>
        <select name="dop_days_query" >
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>

            </select>
            </form>    
    </div>
<hr>
HTML;

// если есть Дата поиска, то начинаем вычитывать данные с сайта ОЗОН
if (isset($date_query_ozon)) {
    if ($date_query_ozon <> '') {
   // получаем массив всех отправления на эту дату
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

 //  Выводим таблицу с Количество купленно
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
        <label for="date_query_ozon">Дата заказов</label>
        <input  type="date" name="date_query_ozon" value="$date_query_ozon" readonly>
        <br><br>
        <label for="number_order">Номер заказа</label>
        <input required type="text" name="number_order" value="">
        
        <input hidden type="text" name="ozon_shop" value="$get_shop_name">
        
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

        // echo "Собрать все Заказы<a href=\"$link\">*СТАРТ*</a> ";
 } else {
    echo "<h2>НЕТ ДАННЫХ ДЛЯ ВЫДАЧИ</h2>";
 }

  }
 }




require_once "main_ozon/footer.php";