<?php
// echo "***********************<br>";
// echo "<pre>";
// print_r($arr_for_sum_table);
// echo "***********************<br>";
// echo "<pre>";
// print_r($arr_sum_services_payment_with_SKU);

require_once "../spravochnik_zatrat.php";
$arr_for_sum_table =[];
 $arr_for_sum_table['Продажи']['-'] = @$arr_sum_all_data['sum_accruals_for_sale_direct'];
 $arr_for_sum_table['Возвраты']['-'] = @$arr_sum_all_data['sum_accruals_for_sale_return'];
 $arr_for_sum_table['Вознаграждение Ozon']['-'] = @$arr_sum_all_data['sum_sale_commission'];

 $arr_for_sum_table['Услуги доставки']['delete'] = 0; // костыль, чтобы это строка выше услуг агенотов была
 $arr_for_sum_table['Услуги агентов']['delete'] = 0;
 $arr_for_sum_table['Услуги ФБО']['delete'] = 0;
 $arr_for_sum_table['Продвижение и реклама']['delete'] = 0;
 $arr_for_sum_table['Другие услуги']['delete'] = 0;
 $arr_for_sum_table['Другие услуги и штрафы']['delete'] = 0;
 $arr_for_sum_table['Компенсации и декомпенсации']['delete'] = 0;
 $arr_for_sum_table['Прочие начисления']['delete'] = 0;

 // сделаем копию массива, чтобы убирать оттуда выбарнные статть затрат 
 if (isset($arr_sum_services_payment)) {$arr_sum_services_payment_copy  = $arr_sum_services_payment;}
 if (isset($arr_sum_services_payment_with_SKU)) {$arr_sum_services_payment_with_SKU_copy  = $arr_sum_services_payment_with_SKU;}
 
//// разбираем логистику ///////////////

foreach ($arr_article as $sku=>$data_sku ) {
// делмаем массив с логистикой
   if (isset($data_sku['logistika']) ) {
      foreach ($data_sku['logistika'] as $key=>$data_logistik ) {
        if ($key == 'direct' OR $key== 'return' ) {
         foreach ($data_logistik as $type_logistik => $summa_logistik) {
            $arr_razbor_logistiki[$type_logistik]  = @$arr_razbor_logistiki[$type_logistik] + $summa_logistik;
         }
       }
     }
  }
  
// начинаем формировать массив Услуги агентов (эквайринг цепляем если он есть)
    if (isset($data_sku['amount_ecvairing'])) {
    $arr_for_sum_table['Услуги агентов']['Эквайринг']   = @$arr_for_sum_table['Услуги агентов']['Эквайринг'] + $data_sku['amount_ecvairing'];
    }
}


//********************************************************************************************* */
/// ******************** Разбираем массив с логистикой, и добавляем ее в arr_for_sum_table
//********************************************************************************************* */


//  print_r($arr_razbor_logistiki);

if (isset($arr_razbor_logistiki)){
$arr_razbor_logistiki_copy = $arr_razbor_logistiki;
raspredelenie_servicnih_rashodov_ozon_report ($arr_for_sum_table, $arr_razbor_logistiki_copy ,  $arr_type_find_servives);
}
unset($arr_for_sum_table['Услуги доставки']['delete']); // костыль, чтобы это строка выше услуг агенотов была 

//********************************************************************************************* */
/// ********************************* Продвижение и реклама, Услуги ФБО, Другие услуги 
//********************************************************************************************* */
// реклама в массиве без привязки к СКУ
 if (isset($arr_sum_services_payment)) {
        raspredelenie_servicnih_rashodov_ozon_report ($arr_for_sum_table, $arr_sum_services_payment_copy ,  $arr_type_find_servives);
    }   
//  реклама в массиве  с СКУ
 if (isset($arr_sum_services_payment_with_SKU)) {
      raspredelenie_servicnih_rashodov_ozon_report ($arr_for_sum_table, $arr_sum_services_payment_with_SKU_copy ,  $arr_type_find_servives);
   }

//********************************************************************************************* */
/// ************* Компенсации и декомпенсации
//********************************************************************************************* */
     if (isset($arr_for_compensation)) {
      $arr_for_compensation_copy_for_check_table = $arr_for_compensation;
            foreach ($arr_for_compensation as $compensation_and_decompensation =>$summa_compensation) {
            $arr_for_sum_table['Компенсации и декомпенсации'][$compensation_and_decompensation] = $summa_compensation;
            unset($arr_for_compensation[$compensation_and_decompensation]);
     }
    }
// 

//********************************************************************************************* */
/// ************* Находим сумму начисленно
//********************************************************************************************* */

 $summa_k_nachisleniu = 0;
foreach ($arr_for_sum_table as $temme) {
    foreach ($temme as $pemme) {
        $summa_k_nachisleniu += $pemme;
    }
}
$summa_k_nachisleniu = round($summa_k_nachisleniu,0);

// print_r($arr_razbor_logistiki);
// print_r($arr_for_compensation);
// print_r($arr_sum_services_payment_with_SKU);
// print_r($arr_sum_services_payment);

 unset($arr_for_sum_table['Услуги доставки']['delete']); // костыль, чтобы это строка выше услуг агенотов)
 unset($arr_for_sum_table['Услуги агентов']['delete']);
 unset($arr_for_sum_table['Услуги ФБО']['delete']);
 unset($arr_for_sum_table['Продвижение и реклама']['delete']);
 unset($arr_for_sum_table['Другие услуги']['delete']);
 unset($arr_for_sum_table['Другие услуги и штрафы']['delete']);
 unset($arr_for_sum_table['Компенсации и декомпенсации']['delete']);
 unset($arr_for_sum_table['Прочие начисления']['delete']);

/* у массива логистики удалим элемент с индексов summa, который раньше зачем то цепляли
это расчетный элемент удалияем не ссым */
if (isset($arr_razbor_logistiki_copy['summa'])) {unset ($arr_razbor_logistiki_copy['summa']); }

/// Теперь проверяем остались ли какие то необработанные индексы, т.е. те,
// которые у нас не прописаны и мы не знаем что они списываею

$summa_ne_naidennih_statei = 0; // сумма затрап котоорые на нашли в нашем перпеесене
$alarm_index_array =[]; // массив куда будем складывать необработанные индексы сервисов

if (isset($arr_razbor_logistiki_copy)) {
  $summa_ne_naidennih_statei += check_count_elements_in_array($alarm_index_array, $arr_razbor_logistiki_copy, 'логистика');
}
if (isset($arr_sum_services_payment_copy)) {
  $summa_ne_naidennih_statei += check_count_elements_in_array($alarm_index_array, $arr_sum_services_payment_copy, 'сервисы БЕЗ SKU');
}
if (isset($arr_sum_services_payment_with_SKU_copy)) {
  $summa_ne_naidennih_statei += check_count_elements_in_array($alarm_index_array, $arr_sum_services_payment_with_SKU_copy, 'сервисы с SKU');
}

/*******************************************************************************************
* Функция проверяем есть ли эелменты в массиве, и если есть, то копирует их индексы и сумму начислений
********************************************************************************************/
function check_count_elements_in_array(&$alarm_index_array, $checking_array, $description_array) {
$all_summa = 0;
  if (count($checking_array) > 0) {
    foreach ($checking_array as $index=>$summa) {
      $alarm_index_array[$description_array][$index] = $summa;
      $all_summa += $summa;
    }
  }
  return $all_summa;
}