<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";


require_once "functions_dimensions.php"; // функции по работе с БД


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

echo "<pre>";
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

    


 if (($new_arr_from_db['height'] != $array_from_ozon['height']) OR 
     ($new_arr_from_db['width'] != $array_from_ozon['width']) OR 
     ($new_arr_from_db['depth'] != $array_from_ozon['depth'])) {
        echo "<br><h3>****** ЕСТЬ РАСХОЖДЕНИЯ ПО АРТИКУЛУ ****".$items_ozon['mp_article']."*************</h3><br>";
          } else {
        echo "<br>****** Артикул ".$items_ozon['mp_article']."  НОРМА *****************<br>";
     }





    // print_r($new_arr_from_db);
    // print_r($array_from_ozon);






unset($new_arr_from_db);
unset($array_from_ozon);
}



die();

foreach ($ozon_catalog as $itmmm) {

$send_data = '{
    "filter": {
    "product_id": [
    "'.$itmmm['product_id'].'"
    ],
    "visibility": "ALL"
    },
    "limit": 100,
    "last_id": "",
    "sort_dir": "ASC"
    }';

    $ozon_dop_url = "v3/products/info/attributes";
$ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);



$data_for_input['mp_article'] = $ozcatalog['result'][0]['offer_id'];
$data_for_input['product_id'] =$itmmm['product_id'];
$data_for_input['sku'] = $itmmm['sku'];


$data_for_input['height'] = $ozcatalog['result'][0]['height'];
$data_for_input['width'] = $ozcatalog['result'][0]['width'];
$data_for_input['depth'] = $ozcatalog['result'][0]['depth'];
$data_for_input['weight'] = $ozcatalog['result'][0]['weight'];



// echo  "<br>".$ozcatalog['result'][0]['offer_id'];
// echo  "<br>height=".$ozcatalog['result'][0]['height'];
// echo  "<br>depth=".$ozcatalog['result'][0]['depth'];
// echo  "<br>width=".$ozcatalog['result'][0]['width'];
// echo  "<br>weight=".$ozcatalog['result'][0]['weight'];
// echo  "<br><br>";
insert_data_in_dimensions_table_ozon($pdo, $shop_name, $data_for_input);
$arr_with_dimensions[$itmmm['mp_article']] = $data_for_input[0];
}

echo "<pre>";
print_r($arr_with_dimensions);

