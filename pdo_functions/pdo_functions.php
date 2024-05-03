<?php
/****
 * Делаем выборку все номенклатуры из БД
 *****/

 function select_all_nomenklaturu($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM `nomenklatura` ORDER BY `number_in_spisok`");
    $stmt->execute();
    $arr_nomenclatura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $arr_nomenclatura;
 }

  /*************************************************
 * Делаем выборка активных товаров из номенклатуры 
 ****************************************************/
 function select_active_nomenklaturu($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `nomenklatura`  WHERE `active_tovar` = 1 ORDER BY `number_in_spisok`");
   $stmt->execute();
   $arr_nomenclatura = $stmt->fetchAll(PDO::FETCH_ASSOC);
   return $arr_nomenclatura;
}



 /*************************************************
 * Делаем выбовод со складов в процентам распределения между всеми
 ****************************************************/

 function select_info_about_sklads($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `sklad_raspredelenie` WHERE `active_sklad`=1");
   $stmt->execute();
   $arr_sklads = $stmt->fetchAll(PDO::FETCH_ASSOC);

   foreach ($arr_sklads as $sklad) {
      $new_arr_sklads[$sklad['sklad_name']]['procent'] = $sklad['procent'];
      $new_arr_sklads[$sklad['sklad_name']]['warehouseId'] = $sklad['warehouseId'];
      $new_arr_sklads[$sklad['sklad_name']]['sklad_name'] = $sklad['sklad_name'];
      $new_arr_sklads[$sklad['sklad_name']]['active_sklad'] = $sklad['active_sklad'];
      $new_arr_sklads[$sklad['sklad_name']]['type_mp'] = $sklad['type_mp'];
   }
   return $new_arr_sklads;
}

/**************************************************************************************
* Функция возвращаем процент, товара для данного магазина, если товар тут продается
**************************************************************************************/

function get_procent_tovarov_marketa($pdo) {
   // $stmt = $pdo->prepare("SELECT * FROM `ostatki_po_skladam` WHERE `active_tovar` = 1");
   $stmt = $pdo->prepare("SELECT * FROM `ostatki_po_skladam`");
   
   $stmt->execute();
   $arr_raspredelenie = $stmt->fetchAll(PDO::FETCH_ASSOC);
   return $arr_raspredelenie;
}

 /*************************************************
 * Делаем выборку все товаров в маркетплэйсе 
 ****************************************************/

function get_catalog_tovarov_v_mp($market_name, $pdo, $type) {
if ($type == 'active') {
   $stmt = $pdo->prepare("SELECT * FROM $market_name WHERE `active_tovar` = 1");
} else {
   $stmt = $pdo->prepare("SELECT * FROM $market_name");
}
   
   $stmt->execute();
   $arr_catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
   foreach ($arr_catalog as $catalog) {
      $new_arr_cat[$catalog['id']] = $catalog['main_article'];
   }
   
   $new_arr_cat= array_unique($new_arr_cat,SORT_STRING );
   foreach ($new_arr_cat as $key => $item) {
      foreach ($arr_catalog as $cata) {
      if ($item == $cata['main_article']) {
         $super_new_arr [] = $cata;

            }
   }}

// print_r($new_arr_cat);
// print_r($super_new_arr);
//    die();
return $super_new_arr;

}

 /****************************************************
 * Функция возвращает массив с минимальными остатками
 ****************************************************/

function get_min_ostatok_tovarov($pdo) {
   $arr = select_active_nomenklaturu($pdo);
   foreach ($arr as $item) {
      $new_arr[$item['main_article_1c']] =  $item['min_ostatok'];
   }
   return $new_arr;
}

 /*************************************************
 * Делаем выборку все товаров в маркетплэйсе 
 ****************************************************/

 function get_tokens($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `tokens`");
   $stmt->execute();
   $arr_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($arr_tokens as $tokens) {
   $new_arr_tokens[$tokens['name_market']] = $tokens;
}

return $new_arr_tokens;

}


 /*************************************************
 * Получаем токен для МП
 ****************************************************/
function get_token_yam($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `tokens` WHERE `name_market` = 'ya_anmaks_fbs'");
   $stmt->execute();
   $ya_token_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
   $ya_token =  $ya_token_info[0]['token'];
return $ya_token;
}

 /*************************************************
 * Получаем id company для МП
 ****************************************************/
function get_id_company_yam($pdo) {
   $stmt = $pdo->prepare("SELECT * FROM `tokens` WHERE `name_market` = 'ya_anmaks_fbs'");
   $stmt->execute();
   $ya_token_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
   $id_company =  $ya_token_info[0]['id_market'];
return $id_company;
}