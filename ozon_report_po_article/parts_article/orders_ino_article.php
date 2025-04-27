<?php 
$full_summa_accruals_for_sale = 0; // сумма цен для покупателя 
$full_summa_amount = 0;
$full_summa_sale_commission = 0; 
$full_summa_sale_logistika = 0; 

foreach ($arr_orders_ino as $items) {
    $i++;
    $our_item = $items['items'];
    // @$all_summa_tovarov_ += $items['accruals_for_sale'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)


$new_post_number = make_posting_number ($items['posting']['posting_number']);
$new_post_number_full = $items['posting']['posting_number'];
$arr_article[$new_post_number]['post_number'] = $new_post_number;
$arr_article[$new_post_number]['SELL'] = 'SELL';
$arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
$arr_article[$new_post_number]['delivery_schema'] = $items['posting']['delivery_schema'];
if (($items['accruals_for_sale'] == 0) && ($items['accruals_for_sale'] == 0)) {
    $arr_article[$new_post_number]['kazahi'] = 'ino';
}
// количество товаров в заказе 


    foreach ($our_item as $item) {

        $arr_number_tovar[$new_post_number] = @$arr_number_tovar[$new_post_number] + 1; // порядковый номер товара в заказе
        $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']); 
        $c_1c_article = get_article_by_sku_fbs($ozon_sebest, $new_sku);
    // цепляем поочереди все товары в заказе
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['sku'] = $new_sku;
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['c_1c_article'] = $c_1c_article;
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['name'] = $item['name'];
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['accruals_for_sale'] = $items['accruals_for_sale'];
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['amount'] = $items['amount'];
        $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['sale_commission'] = $items['sale_commission'];

   //  общемм количество товаров в заказке
       $arr_article[$new_post_number]['count'] = @$arr_article[$new_post_number]['count'] + 1;

// суммы 
$full_summa_accruals_for_sale += $items['accruals_for_sale'];
$full_summa_amount += $items['amount'];
$full_summa_sale_commission += $items['sale_commission']; 
}


/********************************************************************************************
 *  ОБРАБАТЫВАЕМ СЕРВИСЫ В ДОСТАВКЕ 
 *********************************************************************************************/

 foreach ($items['services'] as $services) { // перебираем массив services 
    if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
//логистика
    $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['logistika'] = $services['price'];
    $full_summa_sale_logistika += $services['price']; 
     } 
    if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
// обработка отправления
   $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['obrabotka_otpravlenia'] = $services['price'];
     }
    if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
//последняя миля.
    $arr_article[$new_post_number]['items_buy'][$new_post_number_full]['last_mile'] = $services['price'];
      }
    }

}