<?php
$offset = "";
// require_once $offset . "../connect_db.php";
require_once ("../main_info.php");
require_once $offset . "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";


       try {  
        $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
        $pdo->exec('SET NAMES utf8');

        } catch (PDOException $e) {
          print "Has errors: " . $e->getMessage();  die();
        }






   // Получаем все токены
    $arr_tokens = get_tokens($pdo);
    
    // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

    





$date = date('Y-m-d');
$date_minus_one_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));
// print('Next eeDate ' . $date_minus_one_day);






// die();
/*********************************************************************
 ********           ПОЛУЧАЕМ ПРОДАЖИ ФБО
 *********************************************************************/

//// для ООО
$token_ozon =  $token_ozon;
$client_id_ozon = $client_id_ozon;
$shop_name = 'ozon_anmaks';

    get_sell_item_for_one_date($pdo, $date_minus_one_day, $token_ozon, $client_id_ozon, $shop_name);



//  для ИП
$token_ozon =  $token_ozon_ip;
$client_id_ozon = $client_id_ozon_ip;
$shop_name = 'ozon_ip_zel';
    get_sell_item_for_one_date($pdo, $date_minus_one_day, $token_ozon, $client_id_ozon, $shop_name);

    
//  for ($i=1; $i<31; $i++) {
//     if (strlen($i) == 1)  {
//         $j = "0".$i;
//     } else {
//         $j = $i;
//     }
// $date = date('2025-08-'.$j);
    

//  }


die();

function get_sell_item_for_one_date($pdo, $date,$token_ozon, $client_id_ozon,$shop_name) {
 
$send_data = array("dir"=> "ASC",
                 "filter"=> array("since" => $date."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date."T23:59:59.000Z"
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

$arr_ozon = make_array_for_print ($token_ozon, $client_id_ozon,$send_data);
// echo "<pre>";
// print_r($arr_ooo['art']);


foreach ($arr_ozon['art'] as $key=>$count) {
 $a_1c_article = $key;
 $fbo_sell = $count['count'];
  insert_data_about_sell_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_sell, $date);
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
                    'type_sklad' => 'fbo',
                    'date' => $date));

}

/****************************************************************************************
 * Функция получения проданных через ФБО товаров
 ****************************************************************************************/
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
   echo "Нет даных для выдачи";
   die();
}

foreach ($res as $item) {
   $article = mb_strtolower($item['products'][0]['offer_id']);
$arr_article[$article]['count'] = @$arr_article[  $article]['count']  + 1;
$arr_article[$article]['price'] = @$arr_article[$article]['price']  + $item['products'][0]['price'];

}

$arr['art'] = $arr_article;


return $arr;
}


