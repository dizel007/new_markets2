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
    echo "<br> ********** ".$arr_data['id'] ."*****************************************************";
    print_r($res);
    
    }


function perebor_skidok($token, $client_id, $arr_zapros_skidki, $procent_skidki) {
    foreach ($arr_zapros_skidki['result'] as $zapros_skidki) {

        $arr_data['id'] = $zapros_skidki['id'];
        $temp_price = $zapros_skidki['base_price'];
      
        $arr_data['price'] = round($temp_price - $temp_price*$procent_skidki/100 - 1,0);
        $arr_data['min_count'] = $zapros_skidki['requested_quantity_min'];
        $arr_data['max_count'] = $zapros_skidki['requested_quantity_max'];
     
    
        soglasovanie_zaiavki_na_skidku($token, $client_id, $arr_data);
    
    usleep(50000);
    // print_r($arr_data['price']);
    }
}