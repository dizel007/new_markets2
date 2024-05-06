<?php

$sum_procent_raspredelenia_tovarov = 0;
$our_pribil =0;
 
 $sebestoimos = select_all_nomenklaturu($pdo);


  foreach ($arr_key as $key){
 // Находим себестоимость товара
     foreach ($sebestoimos as $sebes_item) {
         $right_key = mb_strtolower(make_right_articl($key));
         $right_atricle = mb_strtolower($sebes_item['main_article_1c']);

         if ($right_atricle ==  $right_key) {
            $sebes_str_item = $sebes_item['min_price'] ;
            $good_price = $sebes_item['main_price'] ;


            break;
         } else {
             $sebes_str_item = 0;
             $good_price = 0 ;
         }
        }

///     Количество проданного наВБ до вычета         
$array_for_table[$key]['count_sell'] = @$arr_count[$key];
///     Сумма выплат с ВБ до вычета 
$array_for_table[$key]['sum_k_pererchisleniu'] =@$arr_sum_k_pererchisleniu[$key];
 // Авансовая оплата за товар без движения
$array_for_table[$key]['sum_avance'] =@$arr_sum_avance[$key];
      echo "<tr>";
 ///     Сумма выплат с возвратов 
 $array_for_table[$key]['sum_vozvratov'] =@$arr_sum_vozvratov[$key];

 ///     Сумма ЛОгистики 
 if (isset($arr_count[$key])  && ($arr_count[$key] > 0 )){
    $logistika_za_shtuku = @$arr_sum_logistik[$key]/@$arr_count[$key];
    $array_for_table[$key]['sum_logistik'] =@$arr_sum_logistik[$key];
    $array_for_table[$key]['logistika_za_shtuku'] = round($logistika_za_shtuku,2);
 } else {
    $array_for_table[$key]['logistika_za_shtuku'] = '--';
 }

 ///     Сумма Комиссии ВБ
 $array_for_table[$key]['sum_voznagrazhdenie_wb'] =@$arr_sum_voznagrazhdenie_wb[$key];

 ///     Сумма к выплате
 $nasha_viplata_za_article[$key] =  @$arr_sum_k_pererchisleniu[$key] - @$arr_sum_vozvratov[$key] + @$arr_sum_avance[$key] +  
 @$arr_sum_brak[$key] - @$arr_sum_logistik[$key] ;

//

$array_for_table[$key]['sum_nasha_viplata'] =@$array_for_table[$key]['sum_nasha_viplata']  + $nasha_viplata_za_article[$key];

 ///     Цена за штуку
if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) {
 $price_for_shtuka = @$nasha_viplata_za_article[$key]/@$arr_count[$key];

 } else {
     $price_for_shtuka = 0;
 }



/// Дельта между хорошей ценой и ценой продажи 
$delta_good_and_sell_prices = round($price_for_shtuka -$good_price,2) ;
$array_for_table[$key]['delta_good_and_sell_prices'] =$delta_good_and_sell_prices;



 $array_for_table[$key]['price_for_shtuka'] =round($price_for_shtuka,2);
 ///     себестоимость
 $array_for_table[$key]['sebes_str_item'] =$sebes_str_item;
// хорошпя цена товара
$array_for_table[$key]['good_price'] =$good_price;
 
 ///     Разница в стоимости
 if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) { // если количество проданного товара не равно Нулю то считаем дельту
 $delta_v_stoimosti = ($price_for_shtuka - $sebes_str_item);
 } else {
     $delta_v_stoimosti = 0;
 }
 
 
$array_for_table[$key]['delta_v_stoimosti'] =round($delta_v_stoimosti,2);
 
 // Наш реальный заработок
$array_for_table[$key]['our_pribil'] = round($delta_v_stoimosti * @$arr_count[$key],2);
 
 }
 
 /******************************************************************************************************************
 *******************  Делаем рассчеты общих сумм ********************
 *******************************************************************************************************************/
$sum_k_pererchisleniu_po_wb = 0;
$sum_nasha_viplata_po_wb = 0;
$sum_our_pribil_po_wb = 0;
$sum_logistik_po_wb = 0;
$sum_avance_po_wb = 0;
$sum_vozvratov_po_wb = 0;
$sum_voznagrazhdenie_wb_po_wb = 0;



 foreach ($array_for_table as $k_item) {
    $sum_k_pererchisleniu_po_wb     += @$k_item['sum_k_pererchisleniu'];
    $sum_nasha_viplata_po_wb        += @$k_item['sum_nasha_viplata'];
    $sum_our_pribil_po_wb           += @$k_item['our_pribil'];
    $sum_logistik_po_wb             += @$k_item['sum_logistik'];
    $sum_avance_po_wb               += @$k_item['sum_avance'];
    $sum_vozvratov_po_wb            += @$k_item['sum_vozvratov'];
    $sum_voznagrazhdenie_wb_po_wb   += @$k_item['sum_voznagrazhdenie_wb'];



 }

 /******************************************************************************************************************
 *******************  Делаем рассчеты с учетом штрафов  ********************
 *******************************************************************************************************************/
$summa_shrafa = - (-$sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak - $sum_storage_correctirovka);
echo "<br> SHTFA=".$summa_shrafa."<br>";
$procent_ot_viplati = $sum_nasha_viplata_po_wb/100;
$procent_all = 0;
$summa_shtrafa_raschet = 0;
$summa_posle_vicheta_shtrafa = 0;

 foreach ($array_for_table as &$t_item) {

    $t_item['procent_ot_summi'] = $t_item['sum_nasha_viplata'] / $procent_ot_viplati;
    $procent_all +=$t_item['procent_ot_summi'];
    $t_item['summa_strafa_article'] = round($summa_shrafa*$t_item['procent_ot_summi'] /100,2);
    $summa_shtrafa_raschet +=  $t_item['summa_strafa_article'];

    $t_item['pribil_posle_vicheta_strafa'] = $t_item['our_pribil'] - $t_item['summa_strafa_article'];
    $summa_posle_vicheta_shtrafa += $t_item['pribil_posle_vicheta_strafa'];


 }