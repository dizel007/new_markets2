<?php
/******************************************************************************
 * ЗАПРОС Баланса по начислениям с озона 
 ******************************************************************************/
function get_ozon_balance ($token, $client_id,  $date_from, $date_to) {
$ozon_sum_array['viplati'] = 0;    
$ozon_link = 'v1/finance/balance';


        $send_data = array(
            "date_from" => $date_from,
            "date_to" => $date_to
        );
        $send_data = json_encode($send_data);
        $data_by_day_temp = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link);

$ozon_sum_array['viplati'] =  @$data_by_day_temp['total']['accrued']['value'];
$ozon_sum_array['cashflows'] = @$data_by_day_temp['cashflows']['sales']['amount']['value'] +
                               @$data_by_day_temp['cashflows']['returns']['amount']['value'];
$ozon_sum_array['commision'] = $data_by_day_temp['cashflows']['sales']['fee']['value'] +
                               $data_by_day_temp['cashflows']['returns']['fee']['value'];

// echo "<pre>";
// print_r($ozon_sum_array );

return $ozon_sum_array;
}