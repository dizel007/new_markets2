<?php 

// echo "<pre>";
// print_r($arr_article);
// die();
function get_data_sell_tovar($data) { 
    if (isset($data)) {return round($data,0);} else {return 0;}
}
/****************************************************************************
******************** формируем массив сумм 
/****************************************************************************/
    $arr_summ['Цена для покупателя'] = 0;
    $arr_summ['Сумма продаж'] = 0;
    $arr_summ['Комиссия озона'] = 0;
    $arr_summ['Логистика'] = 0;
    $arr_summ['Сервисы'] = 0;
    $arr_summ['Эквайринг'] = 0;
    $arr_summ['Цена за вычетом с арктикулом'] = 0;
    $arr_summ['Процент распределения стоимости'] = 0;
    $arr_summ['Сумма распределения доп.услуг'] = 0;
    $arr_summ['Сумма без всего'] = 0;
    $arr_summ['Сумма за ино_товары'] = 0;
    $arr_summ['Сумма без всего с иностр.товарами'] = 0;


$arr_real_ozon_data = [];





foreach ($arr_article as $sku_ozon=>$print_item) {   
/**************************************************************************************/
// цепояем артикул товара
/**************************************************************************************/
foreach ($arr_article_products as $item_hj) {
       if  ($sku_ozon == $item_hj['sku']) {
           $arr_real_ozon_data[$sku_ozon]['mp_article']   =  $item_hj['article'];
           $arr_real_ozon_data[$sku_ozon]['product_id']   =  $item_hj['product_id'];
           $arr_real_ozon_data[$sku_ozon]['min_price']  =  $item_hj['sebestoimost'];
       }


}

    $arr_real_ozon_data[$sku_ozon]['name'] = $print_item['name'];
    $arr_real_ozon_data[$sku_ozon]['sku'] = $print_item['sku'];


/**************************************************************************************/
// цепояем сумму товаров проданных в страны ЕАЭС
/**************************************************************************************/
if (isset($arr_data_sell_in_srtani_eaes[$sku_ozon])) {
    $arr_real_ozon_data[$sku_ozon]['summa']['prodazh_v_eaes'] = round($arr_data_sell_in_srtani_eaes[$sku_ozon]['amount'],0);
    $arr_real_ozon_data[$sku_ozon]['count']['prodazh_v_eaes'] = $arr_data_sell_in_srtani_eaes[$sku_ozon]['quantity'];
    $arr_summ['Сумма за ино_товары'] = $arr_summ['Сумма за ино_товары'] + round($arr_data_sell_in_srtani_eaes[$sku_ozon]['amount'],0);

 }
else {
    $arr_real_ozon_data[$sku_ozon]['summa']['prodazh_v_eaes'] = 0;
    $arr_real_ozon_data[$sku_ozon]['count']['prodazh_v_eaes'] = 0;
}

//*********************************************************** */

// Формируем квери параметры для разбора по артикулу
  $query_data = [
    'file_name_ozon_small' => $file_name_ozon_small,
    'article' => $sku_ozon,
    'clt' => $client_id,
    ];
    $query_string = http_build_query($query_data);
    $secret_query_string =  base64_encode($query_string);
    
    $arr_real_ozon_data[$sku_ozon]['link_for_report_article'] = "../ozon_report_po_article/index_ozon_razbor_article.php?data=$secret_query_string";
    $arr_real_ozon_data[$sku_ozon]['link_for_site_ozon'] = "https://www.ozon.ru/product/".$print_item['sku'];
/**************************************************************************************/ 
///    Количество продаж 
/**************************************************************************************/
 $arr_real_ozon_data[$sku_ozon]['count']['direct'] =  get_data_sell_tovar(@$print_item['count']['direct']);
 $arr_real_ozon_data[$sku_ozon]['count']['return'] =  get_data_sell_tovar(@$print_item['count']['return']);
 $arr_real_ozon_data[$sku_ozon]['count']['summa']  =  get_data_sell_tovar(@$print_item['count']['summa']);

/**************************************************************************************/
//  Данные по стоимости продаж (Цена товара в ЛК)
/**************************************************************************************/
 $arr_real_ozon_data[$sku_ozon]['summa']['accruals_for_sale']  =  get_data_sell_tovar(@$print_item['accruals_for_sale']['summa']);
 $arr_real_ozon_data[$sku_ozon]['one_item']['accruals_for_sale']  =  get_data_sell_tovar(@$print_item['accruals_for_sale']['one_item']);

if (isset($print_item['accruals_for_sale']['summa'])) {
    $arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] = round( $arr_real_ozon_data[$sku_ozon]['summa']['accruals_for_sale']/100,4);
    $arr_summ['Цена для покупателя'] = @$arr_summ['Цена для покупателя'] +  $arr_real_ozon_data[$sku_ozon]['summa']['accruals_for_sale'];
} else {
    $arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] = 0;
}



/**************************************************************************************/
/// *******************   Комиссия озона   **************************
/**************************************************************************************/
 $arr_real_ozon_data[$sku_ozon]['summa']['sale_commission']  =  get_data_sell_tovar(@$print_item['sale_commission']['summa']);
 $arr_real_ozon_data[$sku_ozon]['one_item']['sale_commission']  =  get_data_sell_tovar(@$print_item['sale_commission']['one_item']);

 $arr_summ['Комиссия озона'] = $arr_summ['Комиссия озона'] +  $arr_real_ozon_data[$sku_ozon]['summa']['sale_commission'];
 
 /**************************************************************************************/
/// *******************   логистка  **************************
/**************************************************************************************/
 $arr_real_ozon_data[$sku_ozon]['summa']['logistika']  =  get_data_sell_tovar(@$print_item['logistika']['summa']);
 $arr_summ['Логистика'] = $arr_summ['Логистика'] + $arr_real_ozon_data[$sku_ozon]['summa']['logistika'];

/**************************************************************************************/
// ************************** Цена продажи *******************************************************************
/**************************************************************************************/
// $arr_real_ozon_data[$sku_ozon]['summa']['amount']  =  get_data_sell_tovar(@$print_item['amount']['summa']);
$arr_real_ozon_data[$sku_ozon]['summa']['amount']  =   $arr_real_ozon_data[$sku_ozon]['summa']['accruals_for_sale'] +
                                                       $arr_real_ozon_data[$sku_ozon]['summa']['sale_commission'] + 
                                                       $arr_real_ozon_data[$sku_ozon]['summa']['logistika'];

$arr_summ['Сумма продаж'] = @$arr_summ['Сумма продаж'] + $arr_real_ozon_data[$sku_ozon]['summa']['amount'];

/**************************************************************************************/
/// *******************  Сервисы  **************************
/**************************************************************************************/
 $arr_real_ozon_data[$sku_ozon]['summa']['services']  =  get_data_sell_tovar(@$print_item['services']['summa']);
 $arr_summ['Сервисы'] = $arr_summ['Сервисы'] +  $arr_real_ozon_data[$sku_ozon]['summa']['services'];

/**************************************************************************************/
/// ******************* Эквайринг  **************************
/**************************************************************************************/
$arr_real_ozon_data[$sku_ozon]['summa']['amount_ecvairing']  =  get_data_sell_tovar(@$print_item['amount_ecvairing']);
$arr_summ['Эквайринг'] = $arr_summ['Эквайринг'] + $arr_real_ozon_data[$sku_ozon]['summa']['amount_ecvairing'];
/**************************************************************************************/
 // Цена за вычетом всего где есть артикул
/**************************************************************************************/
$arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_gde_est_artikul']  =  get_data_sell_tovar(@$print_item['summa_bez_vsego_gde_est_artikul']);
$arr_summ['Цена за вычетом с арктикулом'] = $arr_summ['Цена за вычетом с арктикулом'] + $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_gde_est_artikul'];

/**************************************************************************************/
// **************** Процент распределения стоимости *****************
/**************************************************************************************/
if (isset($print_item['proc_item_ot_vsey_summi'])) {
    $arr_real_ozon_data[$sku_ozon]['proc_item_ot_vsey_summi'] =  $print_item['proc_item_ot_vsey_summi'];
} else {$arr_real_ozon_data[$sku_ozon]['proc_item_ot_vsey_summi'] = 0;}
$arr_summ['Процент распределения стоимости'] = $arr_summ['Процент распределения стоимости'] +  $arr_real_ozon_data[$sku_ozon]['proc_item_ot_vsey_summi'];

/**************************************************************************************/
// **************** Дополнительные услуги   
/**************************************************************************************/


$arr_real_ozon_data[$sku_ozon]['summa']['dop_uslugi']  =  get_data_sell_tovar(@$print_item['dop_uslugi']);
$arr_summ['Сумма распределения доп.услуг'] = $arr_summ['Сумма распределения доп.услуг'] + $arr_real_ozon_data[$sku_ozon]['summa']['dop_uslugi'];


/**************************************************************************************/
// Цена за вычетом всех расходов 
/**************************************************************************************/
if (isset($print_item['summa_bez_vsego'])) {
    $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego']  =  $print_item['summa_bez_vsego'];
} else {
    $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego']  =  0;
}

$arr_summ['Сумма без всего'] = @$arr_summ['Сумма без всего'] + $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego'];


/**************************************************************************************/
// Цена за вычетом всех расходов Но с иностранными товарами (которые с отдельного отчета тянутся)
/**************************************************************************************/
$arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_s_ino_tovarami']  =  $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego'] +
                                                                        $arr_real_ozon_data[$sku_ozon]['summa']['prodazh_v_eaes']                  ;
$arr_summ['Сумма без всего с иностр.товарами'] =  @$arr_summ['Сумма без всего с иностр.товарами'] + $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_s_ino_tovarami'];



/**************************************************************************************/
// костыль для уцененных товаров, у которых нет себестоимости 
/**************************************************************************************/
if (!isset($arr_real_ozon_data[$sku_ozon]['min_price'])) {
   $arr_real_ozon_data[$sku_ozon]['min_price'] =0;
}


/**************************************************************************************/
/// Считаем все для одного товара 
/**************************************************************************************/

if ($arr_real_ozon_data[$sku_ozon]['count']['summa'] !=0) {
    $arr_real_ozon_data[$sku_ozon]['one_item']['logistika']  =  round ($arr_real_ozon_data[$sku_ozon]['summa']['logistika']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['amount']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['amount']/ $arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['services']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['services']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['ecvairing']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['amount_ecvairing']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego_gde_est_artikul']  =  round ($arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_gde_est_artikul']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['dop_uslugi']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['dop_uslugi']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego_s_ino_tovarami']  =  round($arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_s_ino_tovarami']/$arr_real_ozon_data[$sku_ozon]['count']['summa'],0);
    $arr_real_ozon_data[$sku_ozon]['diff_min_price']   =   $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego_s_ino_tovarami'] - $arr_real_ozon_data[$sku_ozon]['min_price'] ;
//     $arr_real_ozon_data[$sku_ozon]['diff_main_price']   =  $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego'] - $arr_real_ozon_data[$sku_ozon]['main_price'] ;

 } else {
    $arr_real_ozon_data[$sku_ozon]['one_item']['logistika']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['amount']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['services']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['ecvairing']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego_gde_est_artikul']  = 0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['dop_uslugi']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego']  =  0;
    $arr_real_ozon_data[$sku_ozon]['one_item']['bez_vsego_s_ino_tovarami']  =  0;
    $arr_real_ozon_data[$sku_ozon]['diff_min_price']   = 0;
    $arr_real_ozon_data[$sku_ozon]['diff_main_price']  = 0;

 


 }

/**************************************************************************************/
/// Считаем проценты затрат
/**************************************************************************************/

if ($arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] !=0) {

   $arr_real_ozon_data[$sku_ozon]['one_procent']['logistika'] = abs(round(($arr_real_ozon_data[$sku_ozon]['summa']['logistika']/$arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] ),1));
   $arr_real_ozon_data[$sku_ozon]['one_procent']['services'] =  abs(round(($arr_real_ozon_data[$sku_ozon]['summa']['services']/$arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] ),1));
   $arr_real_ozon_data[$sku_ozon]['one_procent']['ecvairing'] = abs(round(($arr_real_ozon_data[$sku_ozon]['summa']['amount_ecvairing']/$arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] ),1));
   $arr_real_ozon_data[$sku_ozon]['one_procent']['sale_commission'] = abs(round(($arr_real_ozon_data[$sku_ozon]['summa']['sale_commission']/$arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] ),1));
   $arr_real_ozon_data[$sku_ozon]['one_procent']['dop_uslugi'] = abs(round(($arr_real_ozon_data[$sku_ozon]['summa']['dop_uslugi']/$arr_real_ozon_data[$sku_ozon]['one_procent_from_accruals_for_sale'] ),1));

  } else {
   $arr_real_ozon_data[$sku_ozon]['one_procent']['logistika'] = 0;
   $arr_real_ozon_data[$sku_ozon]['one_procent']['services'] = 0;
   $arr_real_ozon_data[$sku_ozon]['one_procent']['ecvairing'] = 0;
   $arr_real_ozon_data[$sku_ozon]['one_procent']['sale_commission'] = 0;
   $arr_real_ozon_data[$sku_ozon]['one_procent']['dop_uslugi'] = 0;

  }


/**************************************************************************************/
// ***************  Данные по себестоимости
/**************************************************************************************/
$arr_real_ozon_data[$sku_ozon]['summa']['sebestoimost'] = $arr_real_ozon_data[$sku_ozon]['min_price'] * $arr_real_ozon_data[$sku_ozon]['count']['direct']; 
$arr_summ['Сумма себестоимость'] = @$arr_summ['Сумма себестоимость'] + $arr_real_ozon_data[$sku_ozon]['summa']['sebestoimost'] ;

/**************************************************************************************/
// чистая прибыль ///
/**************************************************************************************/
$arr_real_ozon_data[$sku_ozon]['summa']['pribil'] = $arr_real_ozon_data[$sku_ozon]['summa']['bez_vsego_s_ino_tovarami'] - $arr_real_ozon_data[$sku_ozon]['summa']['sebestoimost']; 
$arr_summ['Сумма прибыль'] = @$arr_summ['Сумма прибыль'] + $arr_real_ozon_data[$sku_ozon]['summa']['pribil'];

                           
}


// echo "<pre>";
// print_r($arr_real_ozon_data);


// Если не выбран тип сортировки, то не сортируем
if ($type_sort != '') {uasort($arr_real_ozon_data, "$type_sort");}

// Сортировка по сумме продаж )цена продавца)
        function sort_accruals_for_sale_ot_max_k_min ($a, $b) {return  $b['summa']['accruals_for_sale'] - $a['summa']['accruals_for_sale'];}
        function sort_accruals_for_sale_ot_min_k_max ($a, $b) {return  $a['summa']['accruals_for_sale'] - $b['summa']['accruals_for_sale'];}

        function sort_accruals_for_sale_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['accruals_for_sale'] - $a['one_item']['accruals_for_sale'];}
        function sort_accruals_for_sale_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['accruals_for_sale'] - $b['one_item']['accruals_for_sale'];}

// Сортировка комиссии озон
        function sort_sale_commission_ot_max_k_min ($a, $b) {return  $b['summa']['sale_commission'] - $a['summa']['sale_commission'];}
        function sort_sale_commission_ot_min_k_max ($a, $b) {return  $a['summa']['sale_commission'] - $b['summa']['sale_commission'];}

        function sort_sale_commission_one_procent_ot_max_k_min ($a, $b) {return  $b['one_procent']['sale_commission']*100 - $a['one_procent']['sale_commission']*100;}
        function sort_sale_commission_one_procent_ot_min_k_max ($a, $b) {return  $a['one_procent']['sale_commission']*100 - $b['one_procent']['sale_commission']*100;}

        function sort_sale_commission_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['sale_commission'] - $a['one_item']['sale_commission'];}
        function sort_sale_commission_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['sale_commission'] - $b['one_item']['sale_commission'];}

// Сортировка логистике 
        function sort_logistika_ot_max_k_min ($a, $b) {return  $b['summa']['logistika'] - $a['summa']['logistika'];}
        function sort_logistika_ot_min_k_max ($a, $b) {return  $a['summa']['logistika'] - $b['summa']['logistika'];}

        function sort_logistika_one_procent_ot_max_k_min ($a, $b) {return  $b['one_procent']['logistika']*100 - $a['one_procent']['logistika']*100;}
        function sort_logistika_one_procent_ot_min_k_max ($a, $b) {return  $a['one_procent']['logistika']*100 - $b['one_procent']['logistika']*100;}

        function sort_logistika_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['logistika'] - $a['one_item']['logistika'];}
        function sort_logistika_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['logistika'] - $b['one_item']['logistika'];}


// Сортировка сумме продаж (цена озона)
        function sort_amount_ot_max_k_min ($a, $b) {return  $b['summa']['amount'] - $a['summa']['amount'];}
        function sort_amount_ot_min_k_max ($a, $b) {return  $a['summa']['amount'] - $b['summa']['amount'];}

        function sort_amount_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['amount'] - $a['one_item']['amount'];}
        function sort_amount_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['amount'] - $b['one_item']['amount'];}


// Сортировка сумме продаж сервисам
        function sort_services_ot_max_k_min ($a, $b) {return  $b['summa']['services'] - $a['summa']['services'];}
        function sort_services_ot_min_k_max ($a, $b) {return  $a['summa']['services'] - $b['summa']['services'];}

        function sort_services_one_procent_ot_max_k_min ($a, $b) {return  $b['one_procent']['services']*100 - $a['one_procent']['services']*100;}
        function sort_services_one_procent_ot_min_k_max ($a, $b) {return  $a['one_procent']['services']*100 - $b['one_procent']['services']*100;}

        function sort_services_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['services'] - $a['one_item']['services'];}
        function sort_services_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['services'] - $b['one_item']['services'];}

// Сортировка сумме продаж сервисам
        function sort_ecvairing_ot_max_k_min ($a, $b) {return  $b['summa']['amount_ecvairing'] - $a['summa']['amount_ecvairing'];}
        function sort_ecvairing_ot_min_k_max ($a, $b) {return  $a['summa']['amount_ecvairing'] - $b['summa']['amount_ecvairing'];}

        function sort_ecvairing_one_procent_ot_max_k_min ($a, $b) {return  $b['one_procent']['ecvairing']*100 - $a['one_procent']['ecvairing']*100;}
        function sort_ecvairing_one_procent_ot_min_k_max ($a, $b) {return  $a['one_procent']['ecvairing']*100 - $b['one_procent']['ecvairing']*100;}

        function sort_ecvairing_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['ecvairing'] - $a['one_item']['ecvairing'];}
        function sort_ecvairing_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['ecvairing'] - $b['one_item']['ecvairing'];}

// Сортировка цене за вычетом всего  где есть артикул
        function sort_bez_vsego_gde_est_artikul_ot_max_k_min ($a, $b) {return  $b['summa']['bez_vsego_gde_est_artikul'] - $a['summa']['bez_vsego_gde_est_artikul'];}
        function sort_bez_vsego_gde_est_artikul_ot_min_k_max ($a, $b) {return  $a['summa']['bez_vsego_gde_est_artikul'] - $b['summa']['bez_vsego_gde_est_artikul'];}

        function sort_bez_vsego_gde_est_artikul_one_item_ot_max_k_min ($a, $b) {return  $b['one_item']['bez_vsego_gde_est_artikul'] - $a['one_item']['bez_vsego_gde_est_artikul'];}
        function sort_bez_vsego_gde_est_artikul_one_item_ot_min_k_max ($a, $b) {return  $a['one_item']['bez_vsego_gde_est_artikul'] - $b['one_item']['bez_vsego_gde_est_artikul'];}

// Сортировка цене за вычетом всего 
        function sort_bez_vsego_ot_max_k_min ($a, $b) {return  $b['summa']['bez_vsego'] - $a['summa']['bez_vsego'];}
        function sort_bez_vsego_ot_min_k_max ($a, $b) {return  $a['summa']['bez_vsego'] - $b['summa']['bez_vsego'];}

// Сортировка по прибыли
        function sort_pribil_ot_max_k_min ($a, $b) {return  $b['summa']['pribil'] - $a['summa']['pribil'];}
        function sort_pribil_ot_min_k_max ($a, $b) {return  $a['summa']['pribil'] - $b['summa']['pribil'];}



