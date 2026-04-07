<?php

	
/* * ******************************************************************************************************
Выводим список заказов ОЗОН на определенную дату 
РАБОЧАЯ ВЕРСИЯ 
*** ожидает упаковки ****
*************************************************************************************************************** */
function get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 



$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));


$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

 $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  


$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";


// запустили запрос на озона
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
return $res;
}


/*******************************************************************************************************
********      Достаем фактические остатки товаров и цепляем их к каталогу товаров***********************
*******************************************************************************************************/
function  get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog) {
    // FПолучаем фактическое количество товаров указанное на складе ОЗОН
    $ozon_dop_url = 'v2/product/info/stocks-by-warehouse/fbs';
    $data = '';
    
    foreach ($ozon_catalog as $item)
     {
         $data .="\"".$item['sku']."\",";
    }
    $data = substr($data, 0, -1);
    $send_data ='{"sku": ['.$data.'], "limit": 1000}';
    
    $res = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
    
// echo "<pre>";
// print_r($res) ;
// die();

    foreach ($res['products'] as $items) {
    foreach ($ozon_catalog as &$prods) {
        if ($prods['sku'] == $items['sku']) {
            $prods['quantity'] = $items['present'] - $items['reserved'];
            break 1;
        }
    }
    }
    return $ozon_catalog;
     }

/*******************************************************************************************************
********      Достаем фактические заказанные товары и цепляем их к каталогу товаров*********************
*******************************************************************************************************/

function get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog) {
    $date_query_ozon = date('Y-m-d');
    $date_query_ozon = date('Y-m-d', strtotime('-4 day', strtotime($date_query_ozon))); // начальную датк на 4 дня раньше берем
    
    $dop_days_query = 14; // захватывает 14 дней после сегодняшней даты
    
    //  Получаем фактические заказы с сайта озона (4 дня доо и 14 после сегодняшне йдаты)
    $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, 'awaiting_packaging', $dop_days_query);
    
    // echo "<pre>";
    
    // print_r($res);
    

    if ($res['result']['count'] <> 0 ) { // если нет заказов на озоне, то просто возвращаем массив товаров назад
        foreach ($res['result']['postings'] as $items) {
            foreach ($items['products'] as $product) {
                
                $arr_products[$product['offer_id']] = @$arr_products[$product['offer_id']] + $product['quantity'];
                $arr_summa_sell_products[$product['offer_id']] = @$arr_summa_sell_products[$product['offer_id']] + $product['price']*$product['quantity'];
                

            }
            
        }

    //  print_r ($arr_summa_sell_products);   

// добавляем в каталог данные о количестве проданного товара
        foreach ($arr_products as $key=>$prods) {
            foreach ($ozon_catalog as &$items_ozon) {

                if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {

                    $items_ozon['sell_count'] = $prods;
                } 
            }
        }
  // добавляем в каталог данные о сумме проданного товара      
        foreach ($arr_summa_sell_products as $key=>$Sell_summa) {
            foreach ($ozon_catalog as &$items_ozon) {

                if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {

                    $items_ozon['sell_summa'] = $Sell_summa;
                } 
            }
        }

    }
    
    return $ozon_catalog;
    }



/*******************************************************************************************************
********      Достаем фактические заказанные товары выбранную дату ОЗОН *********************
*******************************************************************************************************/
    function get_new_zakazi_ozon_one_date ($token_ozon, $client_id_ozon, $ozon_catalog, $date_query_ozon) {
        // $date_query_ozon = date('Y-m-d');
        // $date_query_ozon = date('Y-m-d', strtotime('-4 day', strtotime($date_query_ozon))); // начальную датк на 4 дня раньше берем
        
        $dop_days_query = 0; // захватывает 14 дней после сегодняшней даты
        
        //  Получаем фактические заказы с сайта озона (4 дня доо и 14 после сегодняшне йдаты)
        $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, 'awaiting_packaging', $dop_days_query);
        
        // echo "<pre>";
        
        // print_r($res);
        
    
        if ($res['result']['count'] <> 0 ) { // если нет заказов на озоне, то просто возвращаем массив товаров назад
            foreach ($res['result']['postings'] as $items) {
                foreach ($items['products'] as $product) {
                    
                    $arr_products[$product['offer_id']] = @$arr_products[$product['offer_id']] + $product['quantity'];
                    $arr_summa_sell_products[$product['offer_id']] = @$arr_summa_sell_products[$product['offer_id']] + $product['price']*$product['quantity'];
                    
    
                }
                
            }
    
        //  print_r ($arr_summa_sell_products);   
    
    // добавляем в каталог данные о количестве проданного товара
            foreach ($arr_products as $key=>$prods) {
                foreach ($ozon_catalog as &$items_ozon) {
    
                    if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {
    
                        $items_ozon['sell_count'] = $prods;
                    } 
                }
            }
      // добавляем в каталог данные о сумме проданного товара      
            foreach ($arr_summa_sell_products as $key=>$Sell_summa) {
                foreach ($ozon_catalog as &$items_ozon) {
    
                    if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {
    
                        $items_ozon['sell_summa'] = $Sell_summa;
                    } 
                }
            }
    
        }
     return $ozon_catalog;
}


/******************************************************************************************
 *  Вставляем новую строку в БД  WB
 ******************************************************************************************/


 function insert_data_in_dimensions_table_ozon($pdo, $shop_name, $data_for_input)
 {
	 $article = $data_for_input['mp_article'];
	 $sku = $data_for_input['sku'];
	 $product_id = $data_for_input['product_id'];
	 $height = $data_for_input['height'];
	 $width = $data_for_input['width'];
	 $depth = $data_for_input['depth'];
	 $weight = $data_for_input['weight'];
	 $date_write = date('Y-m-d H:i:s');
 
 
	 $stmt  = $pdo->prepare("INSERT INTO `mp_dimensions` (shop_name, mp_article, product_id,  sku, 	height, width, depth, 
												 weight, date_write)
										 VALUES (:shop_name, :mp_article, :product_id, :sku, :height, :width, :depth, 
												 :weight, :date_write)");
 
	 $stmt->bindParam(':shop_name', $shop_name);

     $stmt->bindParam(':mp_article', $article);
	 $stmt->bindParam(':sku', $sku);
	 $stmt->bindParam(':product_id', $product_id);

	 $stmt->bindParam(':height', $height);
	 $stmt->bindParam(':width', $width);
	 $stmt->bindParam(':depth', $depth);
	 $stmt->bindParam(':weight', $weight);

     $stmt->bindParam(':date_write', $date_write);
 
 
	 if (!$stmt->execute()) {
		 print_r($stmt->ErrorInfo());
		 die("<br>Померли на Инсерет в БД Demension ($shop_name)");
	 }
	 return $stmt;
 }



  /************************************************************************************************
 ******  Достаем последние размеры товаров из БД (ЕСЛИ ОНИ ЕСТЬ) *********************************
 ************************************************************************************************/
///// Достаем каталог товароы из БД 

function get_dimensions_from_db_ozon($pdo, $sku) {
    // $stmt = $pdo->prepare("SELECT * FROM $market_name WHERE `active_tovar` = 1");
    $stmt = $pdo->prepare("SELECT * FROM `mp_dimensions` WHERE `sku` = $sku ORDER BY date_write DESC LIMIT 1");
 
    $stmt->execute();
    $arr_catalog = $stmt->fetchAll(PDO::FETCH_ASSOC); 
return $arr_catalog;

}



/************************************************************************************************
 ******  Достаем  размеры товаров с сайта ОЗОНА  по product_id  *********************************
 ************************************************************************************************/
function get_dimensions_from_SITE_ozon($token_ozon, $client_id, $items_ozon) {

            $send_data = '{
                "filter": {
                "product_id": [
                "'.$items_ozon['product_id'].'"
                ],
                "visibility": "ALL"
                },
                "limit": 100,
                "sort_dir": "ASC"
                }';


        
            // $ozon_dop_url = "v3/products/info/attributes";
            $ozon_dop_url = "v4/product/info/attributes";

        $oz_catalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);
        

        // echo "<pre>";
        // print_r($oz_catalog);

        // die();
        
        $data_for_input['mp_article'] = $oz_catalog['result'][0]['offer_id'];
        $data_for_input['product_id'] =$items_ozon['product_id'];
        $data_for_input['sku'] = $items_ozon['sku'];
        
        
        $data_for_input['height'] = $oz_catalog['result'][0]['height'];
        $data_for_input['width']  = $oz_catalog['result'][0]['width'];
        $data_for_input['depth']  = $oz_catalog['result'][0]['depth'];
        $data_for_input['weight'] = $oz_catalog['result'][0]['weight'];
        
        
        
        // echo  "<br>".$ozcatalog['result'][0]['offer_id'];
        // echo  "<br>height=".$ozcatalog['result'][0]['height'];
        // echo  "<br>depth=".$ozcatalog['result'][0]['depth'];
        // echo  "<br>width=".$ozcatalog['result'][0]['width'];
        // echo  "<br>weight=".$ozcatalog['result'][0]['weight'];
        // echo  "<br><br>";
        // insert_data_in_dimensions_table_ozon($pdo, $shop_name, $data_for_input);
        $arr_with_dimensions = $data_for_input;

return $arr_with_dimensions;

}



/************************************************************************************************
 ******  Достаем  размеры товаров с сайта ОЗОНА  по product_id  *********************************
 ************************************************************************************************/
function get_dimensions_from_ozon_by_article($token_ozon, $client_id, $ozon_article) {

            $send_data = '{
                "filter": {
                "offer_id": [
                "'.$ozon_article.'"
                ],
                "visibility": "ALL"
                },
                "limit": 100,
                "sort_dir": "ASC"
                }';


        
            // $ozon_dop_url = "v3/products/info/attributes";
            $ozon_dop_url = "v4/product/info/attributes";

        $oz_catalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);
        

        // echo "<pre>";
        // print_r($oz_catalog);

        // die();
        
        // $data_for_input['mp_article'] = $oz_catalog['result'][0]['offer_id'];
        // $data_for_input['product_id'] =$items_ozon['product_id'];
        // $data_for_input['sku'] = $items_ozon['sku'];
        
        
        // $data_for_input['height'] = $oz_catalog['result'][0]['height'];
        // $data_for_input['width']  = $oz_catalog['result'][0]['width'];
        // $data_for_input['depth']  = $oz_catalog['result'][0]['depth'];
        // $data_for_input['weight'] = $oz_catalog['result'][0]['weight'];
        
        
        
        // echo  "<br>".$ozcatalog['result'][0]['offer_id'];
        // echo  "<br>height=".$ozcatalog['result'][0]['height'];
        // echo  "<br>depth=".$ozcatalog['result'][0]['depth'];
        // echo  "<br>width=".$ozcatalog['result'][0]['width'];
        // echo  "<br>weight=".$ozcatalog['result'][0]['weight'];
        // echo  "<br><br>";
        // insert_data_in_dimensions_table_ozon($pdo, $shop_name, $data_for_input);
        // $arr_with_dimensions = $data_for_input;

return $oz_catalog;

}