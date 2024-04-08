<?php 
/******************************************************************
 * Добавляем все нужную информацию в каталог товаров
 ********************************************************/

function add_all_info_in_catalog ($mp_catalog, $all_catalogs, $arr_sell_tovari) {

    foreach ($mp_catalog as &$article) {
         // получаем процент распределения товаров по каждому артикулу для каждого магазина
         
            $all_procents=0;
            foreach ($all_catalogs as $catalog) {
                foreach ($catalog as $item_catalog) {
                    if (mb_strtolower($item_catalog['main_article']) == mb_strtolower($article['main_article'])){
                        $mp_proc_ = $item_catalog['procent_raspredelenia'];
                    // если артикул в данном каталоге только для ФБО , то не распределяем на него товар
                        $item_catalog['fbo_only']? $mp_proc_ = 0: $z = 1; // 
                        break 1;
                    } else {
                        $mp_proc_ = 0;
                    }
            }
                $all_procents = $all_procents + $mp_proc_;
             }
    
            $article['all_procents'] = $all_procents;
            
  // Продано товаров во всех магазинах
        if (isset($arr_sell_tovari[mb_strtolower($article['main_article'])])) {
            $article['all_sell'] = $arr_sell_tovari[mb_strtolower($article['main_article'])];
        }
// Сколько товара с учетом проданных товаров во всех магазинах
       $ostatki_s_prodannim = $article['real_ostatok'] - @$arr_sell_tovari[mb_strtolower($article['main_article'])];
       $article['ostatok_s_prodazjami'] = $ostatki_s_prodannim;
            
// Если товар продается только на ФБО, то убираем его с распределния по магазинам
            if ($article['fbo_only'] == 1 ) {
                $koef_prodazh_FBO = 0;
                // процент распределения товаров с учетом ФБО
                $mag_proc_from_all_tovar = 0;
                $article['mag_proc_from_all_tovar'] = $mag_proc_from_all_tovar;
            }
            else {
                $koef_prodazh_FBO = 1;
                $mag_proc_from_all_tovar = floor($article['procent_raspredelenia']/$all_procents*100*$koef_prodazh_FBO);
                $article['mag_proc_from_all_tovar'] = $mag_proc_from_all_tovar;
            }
            
           // ********   Количество товара для данного магазина 
            $kolvo_tovarov_dlya_magazina = floor(($ostatki_s_prodannim/100) * $mag_proc_from_all_tovar * $article['fbs'] /100)-1;
            $kolvo_tovarov_dlya_magazina <0 ? $kolvo_tovarov_dlya_magazina=0 : $z=1;
            $article['kolvo_tovarov_dlya_magazina'] = $kolvo_tovarov_dlya_magazina;  
                        
            $temp_article = $article['main_article'];
            $temp_sku = $article['sku'];
            $temp_barcode = $article['barcode'];
            
          
    ($kolvo_tovarov_dlya_magazina == $article['quantity'])?  $check_update = 0:  $z=1;
    ($kolvo_tovarov_dlya_magazina > $article['quantity'])?  $check_update = 1:  $z=1;
    ($kolvo_tovarov_dlya_magazina < $article['quantity'])?  $check_update = 1:  $z=1;
    ($article['fbo_only'] == 1) ? $check_update = 0:  $z=1; // если поставки только по ФБО то снимаем значем

    $article['nead_update'] = $check_update;  

            }
            
  return $mp_catalog;
    }
    