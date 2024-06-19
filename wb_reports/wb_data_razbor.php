<?php
// $pp=0;
foreach ($arr_result as $item) {

// Костыль чтобы убрать не нужные отчеты (по периоду)
    // if ($item['create_dt'] == '2024-03-04') {
    //     continue;
    // }

// КОСТЫЛЬ, чтобы посмотреть только РОСИИИ ИЛИ ЗАГРАНКУ   
// if ($item['report_type'] <> 1) {
//     continue;
// }    

// КОСТЫЛЬ, чтобы УБРАТЬ лишние непонятные продажи 
// echo $item['date_to']."=====". $dateTo."<br>";
// if ($item['date_to'] > $dateTo) {
//     continue;
// }  

$article_new = make_right_articl($item['sa_name']); // Подставляем стандартный артикул

$arr_type[$item['supplier_oper_name']]= $item['supplier_oper_name'];
//******* Сумма К перечислению за товар ************************************************************************************************************
    
if (($item['supplier_oper_name'] == 'Продажа') ) {

            $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
            $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
            $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
            $prodazh++;

}elseif (($item['supplier_oper_name'] == 'Добровольная компенсация при возврате') ) {

                $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
                $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
                
 } elseif (($item['supplier_oper_name'] == 'Коррекция продаж') ) {

    $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] - $item['ppvz_for_pay'];
    $sum_k_pererchisleniu = $sum_k_pererchisleniu  - $item['ppvz_for_pay'];
    $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];
    $prodazh--;
} elseif( $item['supplier_oper_name'] == 'Компенсация потерянного товара') {
    // Компенсация потерянного товара
    $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
    $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
    $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];



} elseif ($item['supplier_oper_name'] == 'Авансовая оплата за товар без движения') { 
 //************** Авансовая оплата за товар без движения ******************************
 
    $arr_sum_avance[$article_new] = @$arr_sum_avance[$article_new] + $item['ppvz_for_pay'];
    $sum_avance = $sum_avance  + $item['ppvz_for_pay'];
} 
elseif (($item['supplier_oper_name'] == 'Частичная компенсация брака') || ($item['supplier_oper_name'] == 'Компенсация подмененного товара') ) {
//  *********************Частичная компенсация брака  ИЛИ Компенсация подмененного товара
    $sum_brak = $sum_brak  + $item['ppvz_for_pay'];
}
 elseif (($item['supplier_oper_name'] == 'Компенсация брака')) {
        //  *********************Частичная компенсация брака  ИЛИ Компенсация подмененного товара
    $sum_brak = $sum_brak  + $item['ppvz_for_pay'];

            


} elseif ($item['supplier_oper_name'] == 'Возврат')  {
  // Сумма возвоатов ************************************************************************************************************

    $arr_sum_vozvratov[$article_new] = @$arr_sum_vozvratov[$article_new] + $item['ppvz_for_pay'];
    $sum_vozvratov = $sum_vozvratov  + $item['ppvz_for_pay'];
    $arr_count[$article_new] = @$arr_count[$article_new] - 1;

} elseif (($item['supplier_oper_name'] == 'Корректная продажа') ) {

// Сумма к перечислению (Корректная продажа) ********************************************************************************************
        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
        $correctProdazh++;
 
} elseif (($item['supplier_oper_name'] == 'Сторно продаж') ) {
// ********************Сторно продаж *****************************************************************************************
        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] - $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  - $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];
        $stornoprodazh++;
} elseif ($item['supplier_oper_name'] == 'Логистика') {
    // Сумма логистики ************************************************************************************************************
        $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  + $item['delivery_rub'];
        // $pp++;
} elseif ($item['supplier_oper_name'] == 'Возмещение издержек по перевозке') {
    // Сумма логистики ИПЕШНИКАМ ************************************************************************************************************
    // $summa_izderzhik_po_perevozke = $summa_izderzhik_po_perevozke + $item['rebill_logistic_cost'];
    //     $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['rebill_logistic_cost'];
    //     $sum_logistiki = $sum_logistiki  + $item['rebill_logistic_cost'];
} elseif ($item['supplier_oper_name'] == 'Логистика сторно') {

    $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] - $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  - $item['delivery_rub'];

} elseif ($item['supplier_oper_name'] == 'Хранение') {
 // Стоимость ХРАНЕНИЯ  ****************************************************************************************************
        $sum_storage = $sum_storage  + $item['storage_fee'];

} elseif ($item['supplier_oper_name'] == 'Корректировка хранения') {
    // Стоимость Корректировка ХРАНЕНИЯ  ****************************************************************************************************
    $sum_storage_correctirovka = $sum_storage_correctirovka  + $item['storage_fee'];


} elseif ($item['supplier_oper_name'] == 'Корректировка эквайринга') {
    // Стоимость Корректировка эквайринга  ****************************************************************************************************
    $sum_korrectirovka_eqvairinga = $sum_korrectirovka_eqvairinga  + $item['acquiring_fee'];



}  elseif (($item['supplier_oper_name'] == 'Удержания') || ($item['supplier_oper_name'] == 'Удержание')) {
 // Стоимость ПРОЧИЕЕ УДЕРЖАНИЯ ****************************************************************************************************
    $sum_uderzhania = $sum_uderzhania  + $item['deduction'];

} elseif (($item['supplier_oper_name'] == 'Штрафы') || ($item['supplier_oper_name'] == 'Штраф') || ($item['supplier_oper_name'] == 'Штрафы и доплаты')){
 // Стоимость ШТРАФЫ И ДОПЛАТЫ  ****************************************************************************************************
    $sum_shtafi_i_doplati = $sum_shtafi_i_doplati  + $item['penalty'];

// Возмещение издержек по перевозке/по складским операциям с товаром
} elseif($item['supplier_oper_name'] == 'Возмещение издержек по перевозке/по складским операциям с товаром') {
$return_logistok = @$return_logistok + $item['rebill_logistic_cost'];

} else {
    $array_neuchet[] = $item;
}
    
// Вознаграждение ВБ  (Добавляем если есть артикул )************************************************************************************************************
    $arr_sum_voznagrazhdenie_wb[$article_new] = @$arr_sum_voznagrazhdenie_wb[$article_new] + $item['ppvz_vw']  + $item['ppvz_vw_nds'];
    $sum_voznagrazhdenie_wb = $sum_voznagrazhdenie_wb  + $item['ppvz_vw']  + $item['ppvz_vw_nds'];

}