<?php
//********************************************************************************************
// Функция находит из каталога ОЗОНов товары где нет product ID и добавляет их в базу данных
//********************************************************************************************

$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";



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
// print_r($ozon_catalog);

// метод получает информацию о товаре по СКУ 
// $ozon_dop_url = "v2/product/info";
$ozon_dop_url = 'v3/product/info/list';


foreach ($ozon_catalog as &$items) {
    if ($items['product_id'] == 0 ) {
        
            // $send_data = json_encode(array("offer_id" => "",
            //                    "product_id" => 0,
            //                    "sku" => $items['sku']));

          $send_data =  json_encode(array("sku" => array($items['sku'])));
            // $send_data = json_encode($send_data);
            
            $ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);

            // print_r($ozcatalog);

            // $items['id_ozon'] = $ozcatalog['result']['id'];
            $items['id_ozon'] = $ozcatalog['items'][0]['id'];


            $item_for_update['product_id'] =  $items['id_ozon'];
            $item_for_update['sku'] =  $items['sku'];
// echo "<pre>";
//             print_r($item_for_update);
// die();
// обновляем базу данных (Добавляем product_id (OZON))
$info_update = update_catalog_mp_ozon($pdo, $shop_name, $item_for_update) ;
// если обмен с ошибками, то ывозим сообщение 
        if ((int)$info_update[0] != 0 ) {
        echo "<br>Какой то облом с апдейтом!  SKU  " . $items['sku'] ."<br>";
        var_dump($info_update);
        }
   }
}

// echo "<pre>";
// print_r($ozon_catalog);
echo "ОБНОВИЛИ ВСЕ ЧТО НУЖНО (БЕЗ ОШИБОК)";

die();



function update_catalog_mp_ozon($pdo, $shop_name, $item_for_update) {
    
    $sql = "UPDATE `$shop_name` SET `product_id` = :product_id WHERE `sku` = :sku";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array('product_id' => $item_for_update['product_id'],
                         'sku'        => $item_for_update['sku']));

$info = $stmt->errorInfo();
return $info;


}

   