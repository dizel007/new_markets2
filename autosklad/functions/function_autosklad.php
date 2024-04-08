<?php

/**************************************************************************************
* Функция возвращаем процент, товара для данного магазина, если товар тут продается
**************************************************************************************/
function get_procent_tovarov_magazina ($arr_article, $arr_mp, $sklads_proc) {
  
    foreach ($arr_mp as $zz) {
        if (mb_strtolower($zz['main_article']) == mb_strtolower($arr_article['main_article'])){
            $mp_proc_ = $sklads_proc;
            break 1;
        } else {
            $mp_proc_ = 0;
        }
    }
return $mp_proc_;
}


/**************************************************************************************
* Функция возвращаем процент товара, который определен в базе данных этому складу,
**************************************************************************************/
function get_db_procent_magazina ($mp_catalog, $sklads, $name_sklad, $arr_new_ostatoki_MP) {

foreach ($mp_catalog as &$mp_item) {
    $string_main_article = mb_strtolower((string)$mp_item['main_article']);
        foreach ($arr_new_ostatoki_MP as $key=>$ostatok) {
            if ($key == $string_main_article) {
                $mp_item['real_ostatok'] = $ostatok;
            }
            $mp_item['procent_raspredelenia'] = $sklads[$name_sklad]['procent'];
        }

}

return $mp_catalog;
}

/**************************************************************************************
* Функция возвращаем массив всех купленных товаров,
**************************************************************************************/

function make_array_all_sell_tovarov($all_catalogs) {
    foreach ($all_catalogs as $catalog) {
        foreach ($catalog as $zz) {
            if (isset ($zz['sell_count'])){
                // echo $zz['sell_count']."***".$zz['main_article']."<br>";
                $arr_sell_tovari[mb_strtolower($zz['main_article'])] =  @$arr_sell_tovari[mb_strtolower($zz['main_article'])] + $zz['sell_count'];
         
            } 

    }
}
return $arr_sell_tovari;
}