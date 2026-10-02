<?php
/*****************************************************************************************************************
 * Функция получения данных о продажах товаров (ежедневные начисления)
 *****************************************************************************************************************/
function get_data_sell_in_mainSEll ($token, $client_id, $date_from, $date_to, $file_name_ozon_mainSell) {

$ozon_link = 'v1/finance/accrual/by-day';
$last_id = '';

$current = strtotime($date_from);
$end_ts  = strtotime($date_to);

while ($current <= $end_ts) {
    $query_date = date('Y-m-d', $current);
    do {
        $send_data = array(
            "date" => $query_date,
            "last_id" => $last_id
        );
        $send_data = json_encode($send_data);
        $data_by_day_temp = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link);
        $last_id = $data_by_day_temp['last_id'];

        foreach ($data_by_day_temp['accruals'] as $t_data) {
            $data_by_days[] = $t_data;
        }
    } while ($last_id  <> '');
    $current = strtotime('+1 day', $current);
}

// сырые данные складываем в файл
$json_types = json_encode($data_by_days, JSON_UNESCAPED_UNICODE);
file_put_contents($file_name_ozon_mainSell, $json_types);
}

/*****************************************************************************************************************
 * Функция получения данных о продажах товаров в страны ЕАЭС
 *****************************************************************************************************************/
function get_data_sell_in_srtani_eaes($token, $client_id, $date_from, $date_to, $file_name_ozon_inostran_prodazhi)
{
    $ozon_dop_url = "v1/finance/products/buyout";
    $send_data = '
            {
            "date_from": "' . $date_from . '",
            "date_to": "' . $date_to . '"
            }';

    
    $arr_sell_v_strani_EAES = send_injection_on_ozon_with_429_repaet($token, $client_id, $send_data, $ozon_dop_url);
if (!isset($arr_sell_v_strani_EAES['products'])) {
    do {
        usleep(100000);
        $arr_sell_v_strani_EAES = send_injection_on_ozon_with_429_repaet($token, $client_id, $send_data, $ozon_dop_url);

    } while (!isset($arr_sell_v_strani_EAES['products']));
}
    // print_r($arr_sell_v_strani_EAES);

    if (isset($arr_sell_v_strani_EAES)) {
           file_put_contents($file_name_ozon_inostran_prodazhi,json_encode($arr_sell_v_strani_EAES, JSON_UNESCAPED_UNICODE));
    }

$summa_prodazh = 0;
    foreach ($arr_sell_v_strani_EAES['products'] as $items) {
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['sku'] = $items['sku'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['offer_id'] = $items['offer_id'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['amount'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['amount'] + $items['amount'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['quantity'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['quantity'] + $items['quantity'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['seller_price_per_instance'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['seller_price_per_instance'] + $items['seller_price_per_instance'];

        $summa_prodazh = $summa_prodazh + $items['amount'];
    }
 $new_arr_sell_v_strani_EAES['summa_prodannogo'] = $summa_prodazh;
 $new_arr_sell_v_strani_EAES['date_from'] = $date_from;
 $new_arr_sell_v_strani_EAES['date_to'] = $date_to;

    unset ($arr_sell_v_strani_EAES);

    return $new_arr_sell_v_strani_EAES;
}