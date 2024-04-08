<?php

/**********************************************************************************************************************
 ***********************************   Type  Orders ************************************************************* 
 **********************************************************************************************************************/

 $summa_orders = 0;
$summa_all = 0;
foreach ($arr_orders as $hud) {
    $summa_all = $summa_all + $hud['amount'];  
    $arr_operation_type_name[$hud['operation_type_name']] = $hud['operation_type_name'];
    if ($hud['operation_type_name'] == 'Доставка покупателю') {
        $summa_orders = $summa_orders + $hud['amount'];
    }
}

/// перебираем наш массив заказов
foreach ($arr_orders as $items) {
    $our_item = $items['items'];
    foreach ($our_item as $item) {

        $arr_atricrle[$item['sku']]['name'] = $item['name'];
        $arr_atricrle[$item['sku']]['sku'] = $item['sku'];

    }
    // echo "<br>999999=".count($our_item)."";
    $arr_atricrle[$item['sku']]['count'] = @$arr_atricrle[$item['sku']]['count'] + count($our_item); // количество элементов в массиве
    $arr_atricrle[$item['sku']]['amount'] = @$arr_atricrle[$item['sku']]['amount'] + $items['amount']; // суммма к перечислению
    $arr_atricrle[$item['sku']]['sale_commission'] = @$arr_atricrle[$item['sku']]['sale_commission'] + $items['sale_commission']; // суммма к перечислению

    foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
//логистика
                $arr_atricrle[$item['sku']]['logistika'] = @$arr_atricrle[$item['sku']]['logistika'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
// обработка отправления
                $arr_atricrle[$item['sku']]['sborka'] = @$arr_atricrle[$item['sku']]['sborka'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
//последняя миля.
                $arr_atricrle[$item['sku']]['lastMile'] = @$arr_atricrle[$item['sku']]['lastMile'] + $services['price']; // суммма логистики
            }
    }

}

unset($hud);



// print_r($arr_atricrle);
// die();





/**********************************************************************************************************************
 ***********************************   Type  returns ************************************************************* 
 **********************************************************************************************************************/


$summa_vozvratov = 0;
$summa_vozvratov_without_service = 0;
$summa_all = 0;
foreach ($arr_returns as $hud) {
    $summa_all = $summa_all + $hud['amount'];  
    $arr_operation_type_name[$hud['operation_type']] = $hud['operation_type'];
    
    // if ($hud['operation_type_name'] == 'Доставка и обработка возврата, отмены, невыкупа') {
        $summa_vozvratov = $summa_vozvratov + $hud['amount'];
if (!isset($hud['services'][0])) {
    $summa_vozvratov_without_service = $summa_vozvratov_without_service + $hud['amount'];
}
    // }
    


}


// print_r($arr_returns);

unset($hud);

/**********************************************************************************************************************
 ***********************************   Type  other ************************************************************* 
 **********************************************************************************************************************/

// print_r($arr_new);
$summa_pretenzii = 0;
$summa_ekvairing = 0;
$summa_all = 0;
foreach ($arr_new as $hud) {
    $summa_all = $summa_all + $hud['amount'];  
    $arr_operation_type_name[$hud['operation_type_name']] = $hud['operation_type_name'];
    if ($hud['operation_type_name'] == 'Начисления по претензиям') {
        $summa_pretenzii = $summa_pretenzii + $hud['amount'];
    }
    
    if ($hud['operation_type_name'] == 'Оплата эквайринга') {
        $summa_ekvairing = $summa_ekvairing + $hud['amount'];
    }

}

unset($hud);

/**********************************************************************************************************************
 ***********************************   Type  services     ************************************************************* 
 **********************************************************************************************************************/
$summa_all = 0;
$summa_uslugi_prodvizenia = 0;
$summa_hranenie_utiliz = 0;
$summa_get_otzivi = 0;
$summa_change_pay = 0;
$summa_korr_cost = 0;

foreach ($arr_services as $hud) {
$arr_operation_type_name[$hud['operation_type_name']] = $hud['operation_type_name'];
$summa_all = $summa_all + $hud['amount'];
    if ($hud['operation_type_name'] == 'Услуги продвижения товаров') {$summa_uslugi_prodvizenia = $summa_uslugi_prodvizenia + $hud['amount'];}
    if ($hud['operation_type_name'] == 'Начисление за хранение/утилизацию возвратов') {$summa_hranenie_utiliz = $summa_hranenie_utiliz + $hud['amount'];}
    if ($hud['operation_type_name'] == 'Приобретение отзывов на платформе') {$summa_get_otzivi = $summa_get_otzivi + $hud['amount'];}
    if ($hud['operation_type_name'] == 'Услуга по изменению условий отгрузки') {$summa_change_pay = $summa_change_pay + $hud['amount'];}
    if ($hud['operation_type_name'] == 'Корректировки стоимости услуг') {$summa_korr_cost = $summa_korr_cost + $hud['amount'];}
}



/// перебираем массив и формируем суммы данных 
foreach ($arr_atricrle as $key=>$prod) {
    $name = $arr_atricrle[$key]['name'];
    $qty = $arr_atricrle[$key]['count'];
    $amount = $arr_atricrle[$key]['amount'] ;
    $sale_commission = $arr_atricrle[$key]['sale_commission'] ;
    $logistika = $arr_atricrle[$key]['logistika'];
    $sborka = $arr_atricrle[$key]['sborka'];
    $lastMile = $arr_atricrle[$key]['lastMile'];
    $summa_NACHILS = $amount - $logistika - $sborka - $lastMile;
// Считаем стольбы с суммами
    $sum_amount = @$sum_amount+ $amount;
    $sum_sale_commission = @$sum_sale_commission + $sale_commission;
    $sum_logistika = @$sum_logistika+ $logistika;
    $sum_sborka = @$sum_sborka + $sborka;
    $sum_lastMile = @$sum_lastMile + $lastMile;
    $sum_nas_all = @$sum_nas_all + $summa_NACHILS; // сумма начислений с сервисными сборами
}
////////////////////////////////////////////////////////////////////////////////////////
$summa_vseh_sborov = $summa_vozvratov+
$summa_pretenzii + $summa_ekvairing + $summa_uslugi_prodvizenia +  $summa_hranenie_utiliz + $summa_get_otzivi +
$summa_change_pay + $summa_korr_cost + $logistika + $sborka + $lastMile;



require_once "ozon_get_trans_4.php"; // вывод на экран таблицы
echo "<br>";
require_once "ozon_get_trans_5.php"; ///////////////////////////////////////////////////////////////   Вывод таблицы на экран


//// доп инфа
// echo "<br>Полная сумма возвратов : (".$summa_vozvratov.")";
// echo "<br>Сумма возратов без сервисных сборов : (".$summa_vozvratov_without_service.")";
$temp  = $summa_vozvratov - $summa_vozvratov_without_service;
// echo "<br>Разница межды возвратными суммами :(".$temp.")";

echo "<br><b>Сумма к начислению : (".number_format($sum_nas_all,2, ',', ' ').")</b><br>";
echo "<br><b>Сумма к начислению : (".number_format($sum_nas_all + $summa_vozvratov_without_service,2, ',', ' ').") за вычетом суммы возвратов(без сервисоных)</b><br><br>";

$temp = $sum_amount + $summa_vozvratov + $summa_pretenzii + $summa_ekvairing + $summa_uslugi_prodvizenia + $summa_hranenie_utiliz + $summa_get_otzivi + $summa_change_pay + $summa_korr_cost ;
$temp2 = $sum_amount + $summa_vseh_sborov ;

echo "<b>СУММА за вычетом всего : (".number_format($temp,2, ',', ' ').") </b><br>";
echo "<b>СУММА за вычетом всего !!!!!: (".number_format($temp2,2, ',', ' ').") </b><br>";
die('');