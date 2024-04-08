<?php

/* * ********
https://docs.ozon.ru/api/seller/#operation/PostingAPI_ShipFbsPostingV4

Выводим информацию о товаре зная его [sku] (ску есть в Заказке) 

            [id] => 246816740 --- = "product_id"			
            [name] => Крепящий анкер универсальный ANMAKS пластиковый, 30 штук, арт. 8910-30
            [offer_id] => 8910-30
            [barcode] => OZN523170685
*** */

// Формируем массив для создания инъекции

function make_packeges_for_one_post($token, $client_id,$one_post_arr_for_zakaz) {

$posting_number =$one_post_arr_for_zakaz["posting_number"];

$send_data_arr  = array  (
    "packages"=> array(
    ),
    "posting_number" => $posting_number, // НОмер отправления
    "with" => array(
    "additional_data"=> true
    )
);




$const_K_12 = 12;
$const_K_16 = 16;
$const_K_8 = 8;

$sku_K_12 = 664697071;
$sku_K_16 = 664720473;
$sku_K_8 = 664665914;



// формируем массив для каждой позиции товара
foreach ($one_post_arr_for_zakaz['products'] as $key => $products) {

    for ($i=0; $i < $products['quantity']; $i++) {
        // echo "i-".$i."<br>";

/* 
  7260 >= 12 штук 
 */
  if ( ($products['sku'] == $sku_K_12 ) AND ($products['quantity'] >= $const_K_12 ) ) {
            $product = array(
                "products" => array(
                    array(
                    "product_id" => $products['sku'],
                    "quantity" => $const_K_12 // ставим нужное количество товаров в упаковке
                    )
                )
            );
            $i = $i - 1 ; // увеличиваем на количество товаров в упаковке
            $products['quantity'] = $products['quantity'] - $const_K_12;

            $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $const_K_12);
 /* 
  7260 МЕНЕЕ 12 штук 
 */

    } elseif ( ($products['sku'] == $sku_K_12 ) AND ($products['quantity'] < $const_K_12 ) ) {

      $product = array(
        "products" => array(
            array(
            "product_id" => $products['sku'],
            "quantity" => $products['quantity'] // ставим оставщиеся товары в отправление 
            )
        )
    );
 
    $i=$i + $products['quantity']; // все товары закидываем в последнюю посылку
    $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $products['quantity']);

    /*
     *****   7245  >=16 штук  *******************************
      */
        }  elseif ( ($products['sku'] == $sku_K_16 ) AND ($products['quantity'] >= $const_K_16 ) ) {

                $product = array(
                    "products" => array(
                        array(
                        "product_id" => $products['sku'],
                        "quantity" => $const_K_16 // ставим нужное количество товаров в упаковке
                        )
                    )
                );
        $i=$i - 1; // увеличиваем на количество товаров в упаковке
        $products['quantity'] = $products['quantity'] - $const_K_16;

        $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $const_K_16);

 /* 
  7245 МЕНЕЕ 16 штук 
 */

} elseif ( ($products['sku'] == $sku_K_16 ) AND ($products['quantity'] < $const_K_16 ) ) {

    $product = array(
      "products" => array(
          array(
          "product_id" => $products['sku'],
          "quantity" => $products['quantity'] // ставим оставщиеся товары в отправление 
          )
      )
  );

  $i=$i + $products['quantity']; // все товары закидываем в последнюю посылку
  $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $products['quantity']);
  
    
    /* ********************************   Смотрим есть ли у нас метровый бордюр 7280-К-8 в количество 8 штук  ******************************* */
        }      elseif ( ($products['sku'] == $sku_K_8 ) AND ($products['quantity'] >= $const_K_8 ) ) {
        
                        $product = array(
                            "products" => array(
                                array(
                                "product_id" => $products['sku'],
                                "quantity" => $const_K_8 // ставим нужное количество товаров в упаковке
                                )
                            )
                        );
                $i=$i - 1; // увеличиваем на количество товаров в упаковке
                $products['quantity'] = $products['quantity'] - $const_K_8;
            
                $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $const_K_8);
                }
 /* 
  7245 МЕНЕЕ 16 штук 
 */

 elseif ( ($products['sku'] == $sku_K_8 ) AND ($products['quantity'] < $const_K_8 ) ) {

    $product = array(
      "products" => array(
          array(
          "product_id" => $products['sku'],
          "quantity" => $products['quantity'] // ставим оставщиеся товары в отправление 
          )
      )
  );

  $i=$i + $products['quantity']; // все товары закидываем в последнюю посылку
  $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => $products['quantity']);       
  
  // Все остальгные отправления делаем по 1 штуке
} else {
            $product = array(
                "products" => array(
                    array(
                    "product_id" => $products['sku'],
                    "quantity" => 1 
                    )
                )
            );
            // Готовим лист подбора
            $new_arr_list_podbora[$key][]= array ("sky" => $products['sku'] , "name" => $products['name'] , "quantity" => 1);
            
        }
            $send_data_arr['packages'][] =  $product;
    }
}

// echo "<pre>";
// print_r($new_arr_list_podbora);
// echo "<pre>";
$res['list_podbora'] = $new_arr_list_podbora;
// echo "<br>***********************************************************************************<br>";

// die('make_new_ZAAKZ_DIE');

$send_data_arr_js = json_encode($send_data_arr);
$ozon_dop_url = "v4/posting/fbs/ship";

// НЕПОСРЕДСТВЕННЫЙ ЗАПУСК ИНЪЕКЦИИ НА САЙТЕ ОЗОН (перевод заказа в собранный)
/* раскоментировать для работы */

$res['obmen'] = send_injection_on_ozon($token, $client_id, $send_data_arr_js, $ozon_dop_url );
return $res;
};

