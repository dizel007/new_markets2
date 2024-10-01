<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";


require_once "functions_dimensions.php"; // функции по работе с БД
require_once "print_table_dimentions.php"; // функции по работе с БД

$shop_name = $_POST['ozon_shop'];
// $shop_name = 'ozon_ip_zel';
// unset($_POST['ozon_shop']);

if ($shop_name == 'ozon_anmaks') {
  // ОЗОН АНМКАС
  $client_id = $arr_tokens['ozon_anmaks']['id_market'];
  $token_ozon = $arr_tokens['ozon_anmaks']['token'];
} elseif ($shop_name == 'ozon_ip_zel') {
  // озон ИП зел
  $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
  $token_ozon = $arr_tokens['ozon_ip_zel']['token'];
} else {
	echo "Не нашли маркет" ;
	die();
}


// Получаем список активных товаров с базы данных 
$ozon_catalog    = get_catalog_tovarov_v_mp($shop_name , $pdo, 'active'); // получаем озон каталог

// echo "<pre>";
// print_r($ozon_catalog[0]);

foreach ($ozon_catalog as $items_ozon) {

    $array_from_ozon = get_dimensions_from_SITE_ozon($token_ozon, $client_id, $items_ozon);
    
    $arr_from_db = get_dimensions_from_db_ozon($pdo, $items_ozon['sku']);
    if (isset($arr_from_db[0])) {
        $new_arr_from_db = $arr_from_db[0];
    } else {
        // если ничего не достали, то деаем запись в БД новых значений
        insert_data_in_dimensions_table_ozon($pdo, $shop_name, $array_from_ozon);
        $arr_from_db = get_dimensions_from_db_ozon($pdo, $items_ozon['sku']);
        $new_arr_from_db = $arr_from_db[0];
    }

    


//  if (($new_arr_from_db['height'] != $array_from_ozon['height']) OR 
//      ($new_arr_from_db['width'] != $array_from_ozon['width']) OR 
//      ($new_arr_from_db['depth'] != $array_from_ozon['depth'])) {
//         echo "<br><h3>****** ЕСТЬ РАСХОЖДЕНИЯ ПО АРТИКУЛУ ****".$items_ozon['mp_article']."*************</h3><br>";
//           } else {
//         echo "<br>****** Артикул ".$items_ozon['mp_article']."  НОРМА *****************<br>";
//      }




// записываем все данные в два массива
$arr_sum_db[]=$new_arr_from_db;
$arr_sum_ozon[]=$array_from_ozon;


unset($new_arr_from_db);
unset($array_from_ozon);
}





print_table_with_dimentions($shop_name, $arr_sum_db ,$arr_sum_ozon);
// file_put_contents('ozon.txt', json_encode($arr_sum_ozon, JSON_UNESCAPED_UNICODE));
// file_put_contents('db.txt', json_encode($arr_sum_db, JSON_UNESCAPED_UNICODE) );



// **************************** конец скрипта **********************************
die();



