<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';


echo <<<HTML

<img src="../pics/ozon.jpg">
<link rel="stylesheet" href="css/main_ozon.css">
HTML;

if (isset($_GET['date_query_ozon'])) {
    $date_query_ozon = $_GET['date_query_ozon'];  

}else {
    // $date_query_ozon =''; 
    $date_query_ozon = date('Y-m-d'); 

}

echo <<<HTML
<h1>ОЗОН ИП Зел</h1>
<h1>ДОПОЛНИТЕЛЬНО</h1>
<hr><hr>

<h2>Получить этикетки сформированных заказов на Дату: </h2>
<div>
    <form method="get" action="#">
    <input  hidden type="text" name="transition" value="1111121">
    <input  required type="date" name="date_query_ozon" value="$date_query_ozon">
    <input type="submit" value="Найти заказы на выбранную дату">
   
</div>
</form>    
<hr>
HTML;

// если есть Дата поиска, то начинаем вычитывать данные с сайта ОЗОН
if (isset($date_query_ozon)) {
    if ($date_query_ozon <> '') {
   // получаем массив всех отправления на эту дату
   $res = get_all_waiting_posts_for_need_date($token_ozon_ip, $client_id_ozon_ip, $date_query_ozon, "awaiting_deliver" , 0);
   



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

    echo "<h3>Сумма купленных товаров : $summa_tovarov руб. </h3>";


        echo "<h3>Список купленных товаров</h3>";
        make_spisok_sendings_ozon_1С ($array_art_price);


        echo "<h3>Количество  товаров : $kolvo_tovarov шт. </h3>";

        //  Выводим таблицу с Заказами
        echo "<h2>Перечень заказов</h2>";
        make_spisok_sendings_ozon ($res['result']['postings']);
        // Ссылка для запуска сбора всех заказов
        $link ="controller/make_etikets_for_all_dopX_2.php";


        echo "<form action=\"$link\" method=\"get\">";
        echo "<label for=\"date_query_ozon\">Дата заказов</label>";
        echo "<input  type=\"date\" name=\"date_query_ozon\" value=\"$date_query_ozon\" readonly>";
        echo "<br><br>";
        echo "<label for=\"number_order\">Номер заказа</label>";
        echo "<input required type=\"text\" name=\"number_order\" value=\"\">";
        
        echo "<input hidden type=\"text\" name=\"ozon_shop\" value=\"ozon_ip_zel\">";

        echo "<br><br>";
        echo "<input  type=\"submit\" value=\"СОБРАТЬ ЗАКАЗЫ выбранную дату\">";
        echo "</form>";



        // echo "Собрать все Заказы<a href=\"$link\">*СТАРТ*</a> ";
 } else {
    echo "<h2>НЕТ ДАННЫХ ДЛЯ ВЫДАЧИ</h2>";
 }

  }
 }




require_once "main_ozon/footer.php";