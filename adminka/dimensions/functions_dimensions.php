<?php
/************************************************************************************************
 ******  Вставляем новую строку в БД  WB ************************************************
 ************************************************************************************************/

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
 ******  Достаем  размеры товаров с сайта ОЗОНА  *********************************
 ************************************************************************************************/
///// Достаем каталог товароы из БД 

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


        
            $ozon_dop_url = "v3/products/info/attributes";
        $oz_catalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);
        
        
        
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