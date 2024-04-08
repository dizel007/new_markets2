<?php
$pp=0;
foreach ($arr_result as $item) {
   
$article_new = make_right_articl($item['sa_name']); // Подставляем стандартный артикул

//******* Сумма К перечислению за товар ************************************************************************************************************
    
   if (($item['supplier_oper_name'] == 'Продажа') ) {

            $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
            $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
            $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
            $prodazh++;
    }

 //************** Авансовая оплата за товар без движения ******************************
 elseif ($item['supplier_oper_name'] == 'Авансовая оплата за товар без движения') {
    $arr_sum_avance[$article_new] = @$arr_sum_avance[$article_new] + $item['ppvz_for_pay'];
    $sum_avance = $sum_avance  + $item['ppvz_for_pay'];
}

//  *********************Частичная компенсация брака  ИЛИ Компенсация подмененного товара
elseif (($item['supplier_oper_name'] == 'Частичная компенсация брака') || ($item['supplier_oper_name'] == 'Компенсация подмененного товара') )  {
    $sum_brak = $sum_brak  + $item['ppvz_for_pay'];
}
  // Сумма возвоатов ************************************************************************************************************
  elseif ($item['supplier_oper_name'] == 'Возврат') {
    $arr_sum_vozvratov[$article_new] = @$arr_sum_vozvratov[$article_new] + $item['ppvz_for_pay'];
    $sum_vozvratov = $sum_vozvratov  + $item['ppvz_for_pay'];
    $arr_count[$article_new] = @$arr_count[$article_new] - 1;

}

// Сумма к перечислению (Корректная продажа) ********************************************************************************************
    
    elseif (($item['supplier_oper_name'] == 'Корректная продажа') ) {

        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
        $correctProdazh++;
 
    }

// ********************Сторно продаж *****************************************************************************************
    elseif (($item['supplier_oper_name'] == 'Сторно продаж') ) {

        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] - $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  - $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];
        $stornoprodazh++;
      }
   
    // Сумма логистики ************************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Логистика') {
        // echo "<br> ************************* $pp **************************************";
        // print_r($item);
        $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  + $item['delivery_rub'];
        $pp++;
    }
    // Сумма логистики ИПЕШНИКАМ ************************************************************************************************************
    // elseif ($item['supplier_oper_name'] == 'Возмещение издержек по перевозке') {
    //     $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['rebill_logistic_cost'];
    //     $sum_logistiki = $sum_logistiki  + $item['rebill_logistic_cost'];
    // }
    
    // elseif ($item['supplier_oper_name'] == 'Логистика сторно') {
    //     $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] - $item['delivery_rub'];
    //     $sum_logistiki = $sum_logistiki  - $item['delivery_rub'];
    // }
  
// Стоимость ХРАНЕНИЯ  ****************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Хранение') {
        $sum_storage = $sum_storage  + $item['storage_fee'];
    }
// Стоимость Корректировка ХРАНЕНИЯ  ****************************************************************************************************
elseif ($item['supplier_oper_name'] == 'Корректировка хранения') {
    $sum_storage_correctirovka = $sum_storage_correctirovka  + $item['storage_fee'];
}
 // Стоимость ПРОЧИЕЕ УДЕРЖАНИЯ ****************************************************************************************************
 elseif ($item['supplier_oper_name'] == 'Удержания') {
    $sum_uderzhania = $sum_uderzhania  + $item['deduction'];
}
 // Стоимость ШТРАФЫ И ДОПЛАТЫ  ****************************************************************************************************
 elseif ($item['supplier_oper_name'] == 'Штрафы и доплаты') {
    $sum_shtafi_i_doplati = $sum_shtafi_i_doplati  + $item['penalty'];
}
// Сумма ШТРАФОв   ************************************************************************************************************
elseif ($item['supplier_oper_name'] == 'Штрафы') {
    $sum_shtraf = $sum_shtraf  + $item['penalty'];
}


 else {
    $array_neuchet[] = $item;
}
    
// Вознаграждение ВБ  (Добавляем если есть артикул )************************************************************************************************************
// elseif ($article_new] <> '') {
    $arr_sum_voznagrazhdenie_wb[$article_new] = @$arr_sum_voznagrazhdenie_wb[$article_new] + $item['ppvz_vw']  + $item['ppvz_vw_nds'];
    $sum_voznagrazhdenie_wb = $sum_voznagrazhdenie_wb  + $item['ppvz_vw']  + $item['ppvz_vw_nds'];
// } 

}