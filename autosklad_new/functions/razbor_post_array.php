<?php

function razbor_post_massive_mp($arr_post){

    foreach ($arr_post as $key=>$value) {
    
        if (mb_strpos($key, 'mp_BarCode_') > 0){
            $new_key = str_replace('_mp_BarCode_', '', $key);
            $arr_BarCode[$new_key] = $value;
        }

        if (mb_strpos($key, 'mp_value_') > 0){
            $new_key = str_replace('_mp_value_', '', $key);
            $arr_value[$new_key] = $value;
        }
        
        if (mb_strpos($key, 'mp_check_')){
            $new_key = str_replace('_mp_check_', '', $key);
    // формируем массив для обновления (Где стояла галочка в строке)
            $item_quantity[$new_key] = array("sku"    => $arr_BarCode[$new_key],
                            "amount" => (int)$arr_value[$new_key]); // требуется преобразование типа на интегер

        }
    }
if (isset( $item_quantity)){
    return $item_quantity;
} else {
    return "no_data";
}
}
