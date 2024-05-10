<?php 
/******************************************************************
 * Добавляем все нужную информацию в каталог товаров
 ********************************************************/

function add_all_info_in_catalog ($mp_catalog, $arr_sell_tovari) {
// print_r($mp_catalog);
// die();
    foreach ($mp_catalog as &$article) {
  // получаем процент распределения товаров по каждому артикулу для каждого магазина
         
  

            
  // Продано товаров во всех магазинах
        if (isset($arr_sell_tovari[mb_strtolower($article['main_article'])])) {
            $article['all_sell'] = $arr_sell_tovari[mb_strtolower($article['main_article'])];
        }
// Сколько товара с учетом проданных товаров во всех магазинах
if (!isset($article['real_ostatok'])) {
    // print_r($article);
    $article['real_ostatok'] = 0; // КОСТЫЛЬ, ЕСЛИ нет никакой 
}
       $ostatki_s_prodannim = $article['real_ostatok'] - @$arr_sell_tovari[mb_strtolower($article['main_article'])];
       $article['ostatok_s_prodazjami'] = $ostatki_s_prodannim;
            
// Если товар продается только на ФБО, то убираем его с распределния по магазинам
            if ($article['fbo_only'] == 1 ) {
                // $koef_prodazh_FBO = 0;
                // процент распределения товаров с учетом ФБО
                $mag_proc_from_all_tovar = 0;
                // $article['mag_proc_from_all_tovar'] = $mag_proc_from_all_tovar;
            }
            else {
                // $koef_prodazh_FBO = 1;
                // $ggg = $article['mp_article'];
                // echo "<br>$ggg<br>";
                $mag_proc_from_all_tovar = floor($article['procent_raspredelenia']);
                // $article['mag_proc_from_all_tovar'] = $mag_proc_from_all_tovar;
            }
            
           // ********   Количество товара для данного магазина 
            $kolvo_tovarov_dlya_magazina = floor(($ostatki_s_prodannim/100) * $mag_proc_from_all_tovar * $article['fbs']/100)-1;
            $kolvo_tovarov_dlya_magazina <0 ? $kolvo_tovarov_dlya_magazina=0 : $z=1;
            $article['kolvo_tovarov_dlya_magazina'] = $kolvo_tovarov_dlya_magazina;  
            $fake_kolvo_tovarov_dlya_magazina = $kolvo_tovarov_dlya_magazina + $article['fake_count'];
            $article['update_kolvo_tovarov_dlya_magazina'] = $kolvo_tovarov_dlya_magazina + $article['fake_count'];
                        
            // $temp_article = $article['main_article'];
            // $temp_sku = $article['sku'];
            // $temp_barcode = $article['barcode'];
            
    if (isset($article['quantity']))  {     
        ($fake_kolvo_tovarov_dlya_magazina == $article['quantity'])?  $check_update = 0:  $z=1;
        ($fake_kolvo_tovarov_dlya_magazina > $article['quantity'])?  $check_update = 1:  $z=1;
        ($fake_kolvo_tovarov_dlya_magazina < $article['quantity'])?  $check_update = 1:  $z=1;
        } else {
            $check_update = 0; 
        }
    ($article['fbo_only'] == 1) ? $check_update = 0:  $z=1; // если поставки только по ФБО то снимаем значем

    $article['nead_update'] = $check_update;  

            }
            
  return $mp_catalog;
    }
    