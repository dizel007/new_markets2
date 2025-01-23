<?php

/****************************************************************************************
 *******  // СБОРКА ЗАКАЗОВ ЗА ОПРЕДЕЛЕННУЮ ДАТУ
 ***************************************************************************************/
function select_order_by_check_date($token_wb, $date_orders)
{
    /// **********************ДАТА СБОРА ЗАКАЗОВ ****************************************************
    // $date_orders = '2024-06-02';
    ///////////////////////////////////////////////////////////////////////////////////   

    if ($date_orders == '') {
        // когда дата не выбрана берем все заказы
        $arr_new_zakaz = get_all_new_zakaz($token_wb);
    } else {
        // когда выбрана дата, то выберем заказы только на одну дату
        $raw_arr_orders_t = get_all_new_zakaz($token_wb);
    if (isset($raw_arr_orders_t['orders'])) { // если есть заказы на эту дату, то формируем массив товаров
        foreach ($raw_arr_orders_t['orders'] as $order) {

            if (substr($order['createdAt'], 0, 10) == $date_orders) { // сортировка только по выбранной дате
                $arr_new_zakaz['orders'][] = $order;
            }
        }
    }
    }
    if (isset($arr_new_zakaz)) {
        return $arr_new_zakaz;
    }
}


/****************************************************************************************
 *******  // СБОРКА Обработанных заказов Но не принятые на ВБ
 ***************************************************************************************/
function select_all_old_order($token_wb)
{

    $arr_old_orders = get_all_old_zakaz($token_wb);
    // echo "<pre>";
    // print_r( $arr_old_orders);
    if (isset($arr_old_orders)) {

        foreach ($arr_old_orders['orders'] as $old_orders) {
            $arr_numbers_old_orders[] = $old_orders['id'];
        }
    }



    // если ли страые заказы, то получаем их статутс
    if (isset($arr_numbers_old_orders)) {
        $data = array("orders" => $arr_numbers_old_orders);

        $link_wb = "https://marketplace-api.wildberries.ru/api/v3/orders/status";
        $arr_status_old_orders = light_query_with_data($token_wb, $link_wb, $data);


        // начинаем сортировать старые заказы по статусу;
        foreach ($arr_status_old_orders['orders'] as $order_status) {
            // print_r($order_status);
            if ($order_status['wbStatus'] == "waiting") {
                if (($order_status['supplierStatus'] == "complete") or ($order_status['supplierStatus'] == "confirm")) {
                    $arr_not_works[] = $order_status['id'];
                }
            }
        }

        // Добавляем статус к основному массиву (заказы с данными)
        foreach ($arr_not_works as $order_status) {
            foreach ($arr_old_orders['orders'] as &$order) {

                if ($order_status == $order['id']) {
                    $order['status'] = 'old';
                    $arr_for_return_old_zakaz['orders'][] = $order;
                }
            }
        }

               
            return true;
        
    }

    return false;
}
