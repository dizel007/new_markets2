<?php

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";

require '../vendor/autoload.php';

require_once "function_report_3/razbor_type_item.php";
require_once "function_report_3/razbor_type_posting_number.php";
require_once "function_report_3/get_sebestoimost.php";
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
 *********************************************************************************************************/
// echo  "ПРОШЛИ КОННЕКТ<br>";

$shop_name = $_GET['ozon_shop'];



// $shop_name = 'ozon_ip_zel';

if ($shop_name == 'ozon_anmaks') {
    // ОЗОН АНМКАС
    $token  = $arr_tokens['ozon_anmaks']['token'];
    $client_id = $arr_tokens['ozon_anmaks']['id_market'];
} elseif ($shop_name == 'ozon_ip_zel') {
    // озон ИП зел
    $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
    $token = $arr_tokens['ozon_ip_zel']['token'];
}

/**********************************************************************************************************
 *     *************** Получаем список товаров с себестоимсотью 
 *       [article] => 82401-з
         [product_id] => 875165455
         [sebestoimost] => 350
         [sku] => 1423019221
         [name] => Пластиковый садовый бордюр Кантри зеленый, длина 10 м, высота 110 мм
 *********************************************************************************************************/
$arr_article_products = get_sebestoimost_tovarov($token, $client_id);
//****************************************************************************************************** 
//****************************************************************************************************** 

//****************************************************************************************************** 
// ВЫчитываем основные продажи
//****************************************************************************************************** 
$file_name_ozon = '_cache/' . $client_id . "_main_data" . ".json";
$data_by_days = json_decode(file_get_contents($file_name_ozon), true);

//****************************************************************************************************** 
// ВЫчитываем иностранные  продажи
//****************************************************************************************************** 
$file_name_ozon_inostran_prodazhi = '_cache/'.$client_id . "_ino_main_data" . ".json";
$ino_prodazhi = json_decode(file_get_contents($file_name_ozon_inostran_prodazhi), true);


//****************************************************************************************************** 
/// Разбиваем массив по категориям расходов ///////////////////////////////
//****************************************************************************************************** 
foreach ($data_by_days as $one_data) {
    $category[$one_data['accrued_category']] = $one_data['accrued_category'];
    if (isset($one_data['accrued_category'])) {
        $arr_by_accrued_category[$one_data['accrued_category']][] = $one_data;
    } else {
        $arr_other[] = $one_data;  // или любой другой массив
    }
}

//****************************************************************************************************** 
// Если есть категории какие то то сообщим об этом 
//****************************************************************************************************** 
if (isset($arr_other)) {
    echo "<br>НАйден массив без категории<br>";
}


//////////////////////////////////////////////////////////////////////////////////////////////////////
// берем из JSON файла статьи всех расходов с ID и названием и описанием 
// https://api-seller.ozon.ru/v1/finance/accrual/types  
// полученные через этот метод  (Он хренова работает поэтому данные сохраниили )
//////////////////////////////////////////////////////////////////////////////////////////////////////
$type_fees = json_decode(file_get_contents('types_spend.json'), true);
//////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////
$sku_ozon = $_GET['sku_ozon'];

//==========================================================================================================
//  Разбираем массив POSTING
//==========================================================================================================
$arr_posting_orders = razbor_POSTING_by_posting_numbers($arr_posting_orders, $arr_by_accrued_category['POSTING'], $type_fees);

//==========================================================================================================
//  Разбираем массив NON_ITEM 
//==========================================================================================================
[$arr_posting_orders, $summa_unset_for_orders] = razbor_NON_ITEM_by_posting_numbers($arr_posting_orders, $arr_by_accrued_category['NON_ITEM'], $type_fees, $sku_ozon);

//==========================================================================================================
//  Разбираем массив ITEM 
//==========================================================================================================
[$arr_posting_orders, $arr_item_orders] = razbor_ITEM_by_posting_numbers($arr_posting_orders, $arr_by_accrued_category['ITEM'], $type_fees, $sku_ozon);

// $arr_item_orders  - массив без СКУ и номеров заказа
// просто буем его суммировать и добавлять к расходам
$summa_nerazobrannogo_iz_massiva_ITEM = 0;
foreach ($arr_item_orders[$sku_ozon] as $key => $amount_arr) {
    foreach ($amount_arr as $amount_z) {
        $summa_nerazobrannogo_iz_massiva_ITEM += $amount_z;
    }
}
unset($arr_item_orders[$sku_ozon]);

//==========================================================================================================

$Arr[$sku_ozon] = $arr_posting_orders[$sku_ozon];
//======================================================================================
// Чтобы найти процентр распределения суммы БЕЗ ИтЕМ находим суммы всех товаров и нуэного нам артикула
//======================================================================================
$summa_non_item_bez_viborki_po_postingam = 0;
foreach ($arr_by_accrued_category['NON_ITEM'] as $b) {
    // print_r($b);
    // $description_type = change_id_by_description_spend($type_fees, $b['non_item_fee']['type_id']);
    //  @$h1[$description_type] += $b['non_item_fee']['accrued']['amount'];
    $summa_non_item_bez_viborki_po_postingam += $b['non_item_fee']['accrued']['amount'];
}

$sum_array = razbor_POSTING_category($sum_array, $arr_by_accrued_category['POSTING'], $type_fees);
foreach ($sum_array as $item_sku) {
    @$all_summ_seller_price += $item_sku['seller_price'];
}
$one_procent_ot_sum_seller_price = $all_summ_seller_price / 100;
$porc_kotori_nuzhno_raspredelit = $sum_array[$sku_ozon]['seller_price'] /  $one_procent_ot_sum_seller_price;
// сумма рекламы и прочей херни, которую не привязвли к СКУ 
$summa_non_item_dlia_sku = $summa_non_item_bez_viborki_po_postingam * $porc_kotori_nuzhno_raspredelit / 100;

// находим количество отправлений 

// print_R($Arr[$sku_ozon]);
foreach ($Arr[$sku_ozon] as $g) {
    foreach ($g as $posting_number => $d) {
        @$j[$posting_number] = $posting_number;
    }
}
$count_posting  = count($j);
//  сумму которю нужно будет разить между всем количеством отправлений
$summa_for_each_posting = round(($summa_non_item_dlia_sku + $summa_nerazobrannogo_iz_massiva_ITEM - $summa_unset_for_orders) / $count_posting, 2);

// die();


//==========================================================================================================
//  Цепляем дополнительную информацию к массиву заказ/отправления **
//==========================================================================================================

/// формируем массив SKU  - себестоимость
foreach ($arr_article_products as $product) {
    $prod_arr[$product['sku']] = $product['article'];
    $sebes_arr[$product['sku']] = $product['sebestoimost'];
}


$arr_dop_logistika = [
    'Доставка до места выдачи силами Ozon',
    'Доставка до места выдачи',
    'Обработка возвратов, отмен и невыкупов партнёрами'
];

$arr_dop_strafi = [
    'Отгрузка в нерекомендованный слот',
];


// цепляем к массиву себестоимсть и артикул
foreach ($Arr as $sku => &$item_order) {
    foreach ($item_order as $order => &$item_posting) {
        foreach ($item_posting as $post => &$posting) {
            $posting['article'] = $prod_arr[$sku];
            $posting['sebestoimost'] = $sebes_arr[$sku];
                

////  Все дополнительные расходы
            $posting['dop_rashod'] = $summa_for_each_posting;  //  добавляем стоимость усредненную рекламы++ для каждого товара
            //=================================================================================================================================
            // Oбъеденяем всю дополнительную логистику
            //=================================================================================================================================
            foreach ($posting as $key_posting => $summa_dop_log) {
                foreach ($arr_dop_logistika as $log_rashod) {
                    if ($key_posting == $log_rashod) {
                        @$posting['dop_logistika'] += $summa_dop_log;
                        unset($Arr[$sku][$order][$post][$key_posting]);
                    }
                }
                //=================================================================================================================================
                // Oбъеденяем все штрафы
                //=================================================================================================================================
                foreach ($arr_dop_strafi as $strafi_rashod) {
                    if ($key_posting == $strafi_rashod) {
                        @$posting['dop_strafi'] += $summa_dop_log;
                        unset($Arr[$sku][$order][$post][$key_posting]);
                    }
                }
            }

//// Посчитаем сколько денег должны нам перевести на Р/С (как вЛК ОЗОНЕ)
            $posting['deneg_na_rs'] =  @$posting['seller_price'] +
                                       @$posting['commission']  +
                                       @$posting['Логистика'] +
                                       @$posting['Обратная логистика'] +
                                       @$posting['Эквайринг'] + 
                                       @$posting['Продвижение бренда'] +
                                       @$posting['dop_logistika'] +
                                       @$posting['dop_strafi'] ;
                                        
 // Считаем прибыль с заказа 
 // если возврат, тосебестоимость равно 0 
 if (isset($posting['_return_'])) {
   $posting['sebestoimost'] = 0; 
 } 


//  echo "<br> ************* $post **************";
//================================================================================================
//                            Добавляем иностанные продажи 
//================================================================================================

foreach ($ino_prodazhi['products'] as $jh=>&$ino_items ) {
    if (($ino_items['posting_number'] == $post)) {
        @$posting['seller_price'] += $ino_items['amount'];
        @$posting['article']  = $posting['article'].'(ino)';
        @$posting['ino']  = '_ino_';
        unset ($ino_prodazhi['products'][$jh]); // удаляем то ч 
    }

}

//================================================================================================
//                            Считаем прибыль на Р/С 
//================================================================================================

     $posting['pribil'] =  @$posting['deneg_na_rs'] +
                           @$posting['dop_rashod'] - 
                           @$posting['sebestoimost']; 
// если иностранные продажи и ЮЛ продажи, то добавляем сумму из отчета по иностранным продажам 
    if (isset($posting['ino'])) {
        @$posting['pribil'] += $posting['seller_price'];
     }



            foreach ($posting as $key_posting => $summa_dop_log) {
                $ggg[$key_posting] = $key_posting;
            }
        }
    }
}




$data = $Arr[$sku_ozon];

// echo "<pre>";
// print_r($ino_prodazhi['products']);
// print_r($data );

// die();

//==========================================================================================================
//  выводим массив в виде таблицы 
//==========================================================================================================


require_once "repor_33.php";


die();
