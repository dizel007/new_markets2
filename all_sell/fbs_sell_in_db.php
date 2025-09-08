<?php
$offset = "";
require_once $offset . "../connect_db.php";

require_once $offset . "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";



$date = date('Y-m-d');
$date_minus_one_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));
// print('Next eeDate ' . $date_minus_one_day);


$json_send_data = $_POST['send_data'];
$send_data = json_decode($json_send_data, true );
//  }
echo "<pre>";
print_r($send_data);


foreach ($send_data as $shop_name=>$mp_shop) {
   foreach ($mp_shop as $article=>$count) {


      echo "<br>shop_name=$shop_name;  article=$article; count=$count <br>";

      insert_data_about_sell_fbo_ozon($pdo, $shop_name, $article, $count, $date_minus_one_day);

   }
}
die();



/****************************************************************************************
 * Функция вставки в базу данных данных о продажах
 ****************************************************************************************/
function insert_data_about_sell_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_sell, $date) {
$sth = $pdo->prepare("INSERT INTO `z_ozon_fbo_sell` SET `shop_name`= :shop_name, `1c_article` = :1c_article, 
                                       `fbo_sell`= :fbo_sell, `type_sklad`= :type_sklad, `date` =:date");

$sth->execute(array('shop_name' => $shop_name, 
                    '1c_article' => $a_1c_article,
                    'fbo_sell' => $fbo_sell,
                    'type_sklad' => 'fbs',
                    'date' => $date));

}
