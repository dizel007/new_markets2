<?php
function soglasovanie_zaiavki_na_skidku($token_ozon, $client_id_ozon, $arr_data) {
    $send_data =  array (
        "tasks" => array (array(
            "id" => $arr_data['id'],
            "approved_price" => $arr_data['price'],
            "seller_comment" =>  "OK",
            "approved_quantity_min" => $arr_data['min_count'],
            "approved_quantity_max" => $arr_data['max_count']
        ))
    );
    
    
    $send_data = json_encode($send_data);
    $ozon_dop_url = 'v1/actions/discounts-task/approve';
    $res = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
   
    if ($res['result']['success_count'] == 1) {
        echo "<br> СОГЛАСОВАНО ";   
    } else {
        echo "<br> ОТКЛОНЕНО ";    
        echo "<br> Причина : ".$res['result']['fail_details'][0]['error_for_user'];    

    }
     
    echo "<br> **************************************************************";
    }


function perebor_skidok($token, $client_id, $arr_zapros_skidki, $procent_skidki) {
//  print_r($arr_zapros_skidki);
//  die();
    foreach ($arr_zapros_skidki as $zapros_skidki) {
//  print_r($zapros_skidki);
        echo "<br> * ID = ".$zapros_skidki['id'] ."*";
        echo "<br> * Артикул = ".$zapros_skidki['offer_id'] ."*";
        echo "<br> * Основная цена = ".$zapros_skidki['base_price'] ."*";  
        echo "<br> * Минимальная цена = ".$zapros_skidki['original_price'] ."*";

        $arr_data['id'] = $zapros_skidki['id'];
        $temp_price = $zapros_skidki['base_price'];
        $price_for_discount = round($temp_price - $temp_price*$procent_skidki/100 + 1,0);
        $arr_data['price'] = $price_for_discount;
        
        // if ($zapros_skidki['offer_id'] == '82401-ч') {
        //     $price_for_discount = 873;
        // }
        echo "<br> * Цена со скидкой  =  ". $price_for_discount ."*";

        $arr_data['min_count'] = $zapros_skidki['requested_quantity_min'];
        $arr_data['max_count'] = $zapros_skidki['requested_quantity_max'];
     
    
        $res = soglasovanie_zaiavki_na_skidku($token, $client_id, $arr_data);
    
  
        echo "<br>";
    usleep(50000);
    // print_r($arr_data['price']);
 
    }
    return $res;
}