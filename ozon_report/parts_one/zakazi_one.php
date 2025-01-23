<?php 
foreach ($arr_orders as $items) {
    $i++;
    $our_item = $items['items'];
    // @$all_summa_tovarov_ += $items['accruals_for_sale'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)


$new_post_number = make_posting_number ($items['posting']['posting_number']);
$arr_article[$new_post_number]['post_number'] = $new_post_number;
$arr_article[$new_post_number]['SELL'] = 'SELL';
$arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
$arr_article[$new_post_number]['delivery_schema'] = $items['posting']['delivery_schema'];

// количество товаров в заказе 


    foreach ($our_item as $item) {

        $arr_number_tovar[$new_post_number] = @$arr_number_tovar[$new_post_number] + 1; // порядковый номер товара в заказе
        $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']); 
        $c_1c_article = get_article_by_sku_fbs($ozon_sebest, $new_sku);
    // цепляем поочереди все товары в заказе
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['sku'] = $new_sku;
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['c_1c_article'] = $c_1c_article;
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['name'] = $item['name'];
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['accruals_for_sale'] = $items['accruals_for_sale'];
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['amount'] = $items['amount'];
        $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['sale_commission'] = $items['sale_commission'];

   //  общемм количество товаров в заказке
       $arr_article[$new_post_number]['count'] = @$arr_article[$new_post_number]['count'] + 1;


   // Суммируем суммы операции
       $arr_article[$new_post_number]['amount'] = @$arr_article[$new_post_number]['amount'] + $items['amount']/count($our_item); 
   // Суммируем Комиссию за продажу     
       $arr_article[$new_post_number]['sale_commission'] = @$arr_article[$new_post_number]['sale_commission'] + $items['sale_commission']/count($our_item);
   // Цена для покупателя    
       $arr_article[$new_post_number]['accruals_for_sale'] = @$arr_article[$new_post_number]['accruals_for_sale'] + $items['accruals_for_sale']/count($our_item);
    }


/********************************************************************************************
 *  ОБРАБАТЫВАЕМ СЕРВИСЫ В ДОСТАВКЕ 
 *********************************************************************************************/

 foreach ($items['services'] as $services) { // перебираем массив services 
    if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
//логистика
    $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['logistika'] = $services['price'];
     } 
    if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
// обработка отправления
   $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['obrabotka_otpravlenia'] = $services['price'];
     }
    if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
//последняя миля.
    $arr_article[$new_post_number]['items_buy'][$arr_number_tovar[$new_post_number]]['last_mile'] = $services['price'];
      }
    }

}