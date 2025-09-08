<?php

$offset = "";
require_once  "connect_db.php";

/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "mp_functions/ozon_api_functions.php";
require_once "pdo_functions/pdo_functions.php";



$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}

// echo "<pre>";
// print_r($arr_poriadkovii_number);
   // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

// die();
// Вставляем форму для ввода
// require_once "start_form.php";
$temp_date = "2025-06-30";

for ($i = 1; $i<32; $i++) { 
$date_start = date('Y-m-d', strtotime($temp_date . ' +'.$i.' day'));
echo "$date_start<br>";
// }
// die();

$send_data = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_start."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date_start."T23:59:59.000Z"
                ),
"limit" =>  1000,
"offset" =>  0,
"translit" => true,
"with" => array(
"analytics_data" => true,
"financial_data" => true
)
);





// echo "<pre>";
// print_r($send_data);
// Получаем массив продаж и сортируем по количеству и метсу
$arr_ooo = make_array_for_print ($token_ozon, $client_id_ozon,$send_data);
if ($arr_ooo != 0) {
    $arr[$date_start]['ozon_anmaks'] = $arr_ooo;
}

// Получаем массив продаж и сортируем по количеству и метсу для второй организации
$arr_ip = make_array_for_print ($token_ozon_ip, $client_id_ozon_ip,$send_data);
if ($arr_ip != 0) {
$arr[$date_start]['ozon_ip_zel'] = $arr_ip;
}



// echo "<pre>";
// print_r($arr_ooo);
// print_r($arr_ip);
// echo   "<br>********************************************************************* <br>";
usleep(200);


}

echo "<pre>";
print_r($arr);


foreach ($arr as $date_n=>$shops) {
    foreach($shops as $shop_name=>$k) {
        foreach($k as $article=>$count) {

            echo "SHOP =  $shop_name ; ART =  $article; COUNT =  $count ; DATE = $date_n <br>";

            insert_data_about_sell_fbo_ozon($pdo, $shop_name, $article, $count, $date_n);
        }
    }
}



/****************************************************************************************
 * Функция вставки в базу данных данных о продажах
 ****************************************************************************************/
function insert_data_about_sell_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_sell, $date) {
$sth = $pdo->prepare("INSERT INTO `z_ozon_fbo_sell` SET `shop_name`= :shop_name, `1c_article` = :1c_article, 
                                       `fbo_sell`= :fbo_sell, `type_sklad`= :type_sklad, `date` =:date");

$sth->execute(array('shop_name' => $shop_name, 
                    '1c_article' => $a_1c_article,
                    'fbo_sell' => $fbo_sell,
                    'type_sklad' => 'fbo',
                    'date' => $date));

}





function make_array_for_print ($token_anmaks, $client_id_anmaks,$send_data) {
$priznak_all_orders = 0;
$i=0;
do {
   $json_data_send = json_encode($send_data);
   $temp_res = send_injection_on_ozon($token_anmaks, $client_id_anmaks, $json_data_send, 'v2/posting/fbo/list');
// Записываем все продажи в массив 
        foreach ($temp_res['result'] as $temp_item) {
            $res[] = $temp_item;
        }
    
  
   if (isset($temp_res["result"][999])) {
    $send_data["offset"] = $send_data["offset"]  + 1000; 
   } else {
    $priznak_all_orders = 1;
   }
$i++;
} while ($priznak_all_orders == 0);


if (!isset($res)) {
   echo "Нет даных для выдачи<br>";
   return 0;
//    die();
}

foreach ($res as $item) {
   $article = mb_strtolower($item['products'][0]['offer_id']);
$arr_article[$article] = @$arr_article[$article]  + 1;
}



return $arr_article;
}

