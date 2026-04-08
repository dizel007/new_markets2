<?PHP
// require_once "../_libs_ozon/function_ozon_reports.php"; // массив с себестоимостью товаров
// require_once "../_libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров
require_once "../../mp_functions/report_excel_file.php";

// $ozon_sebest = get_catalog_tovarov_v_mp($ozon_shop, $pdo, 'all');
// echo "<pre>";
// print_r($prod_array);
// die();

// делаем один последовательный массив в операциями
foreach ($prod_array as $items) {
    foreach ($items as $item) {
        $new_prod_array[] = $item;
    }
}


// $new_prod_array = json_decode(file_get_contents('xxx.json'),true);
// print_r($new_prod_array);
// die();
foreach ($new_prod_array as $item) {

    if ($item['type'] == 'orders') {
        // Доставка и обработка возврата, отмены, невыкупа   
        $arr_orders[] = $item;
    } elseif ($item['type'] == 'returns') {
        // Доставка и обработка возврата, отмены, невыкупа
        $arr_returns[] = $item;
    } elseif ($item['type'] == 'other') {
        // эквайринг ;претензиям
        $arr_other[] = $item;
    } elseif ($item['type'] == 'services') {
        //продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
        $arr_services[] = $item;
    } elseif ($item['type'] == 'compensation') {
        //продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
        $arr_compensation[] = $item;
    } else {
        // Если есть неучтенка то сюда
        $arr_index_job[] = $item; /// Проверить нужно будет на существование этого массива

    }
}

/**************************************  Не найденные   ************************************************************/
if (isset($arr_index_job)) {
    echo "<br>Есть новые массивы которые нужно обработать<br>";
}
/**************************************  Не найденные   ************************************************************/

// echo "<pre>";
// print_r( $arr_orders[0]);
$arr_article = [];


$i = 0;
/**************************************  ЗАКАЗЫ ************************************************************/
if (isset($arr_orders)) {
    require_once "_parts_razbor/zakazi.php";
}
/***************************  ВОЗВРАТЫ **********************************************************************/
if (isset($arr_returns)) {
    require_once "_parts_razbor/vozvrati.php";
}
/*************************************  Эквайринг ***********************************************************/
if (isset($arr_other)) {
    require_once "_parts_razbor/ecvairing.php";
}
/************************************* Удержание за недовложение товара *************************************/
if (isset($arr_compensation)) {
    require_once "_parts_razbor/uderzhania.php";
}
/************************************  Сервисы **************************************************************/
if (isset($arr_services)) {
    require_once "_parts_razbor/servici.php";
}
/***********************************************************************************************************/


// суммируем все данные 
foreach ($arr_article as &$one_article_data) {
    // просуммируем всю логистику прямую и обратную для ФБО и ФБС
    if (isset($one_article_data['logistika'])) {
        foreach ($one_article_data['logistika'] as $log_direkt => $logistika) {
            $sum_log = array_sum($logistika);
            $one_article_data['logistika'][$log_direkt]['summa'] = $sum_log;
        }
        $one_article_data['logistika']['summa'] =  @$one_article_data['logistika']['direct']['summa'] + @$one_article_data['logistika']['return']['summa'];
    }
    // находим количество с учетом возвратов
    if (isset($one_article_data['count'])) {
        $one_article_data['count']['summa'] =  @$one_article_data['count']['direct'] - @$one_article_data['count']['return'];
    }
    // находим amount с учетом возвратов
    if (isset($one_article_data['amount'])) {
        $one_article_data['amount']['summa'] =  @$one_article_data['amount']['direct'] + @$one_article_data['amount']['return'];
    }
    // находим sale_commission с учетом возвратов
    if (isset($one_article_data['sale_commission'])) {
        $one_article_data['sale_commission']['summa'] =  @$one_article_data['sale_commission']['direct'] + @$one_article_data['sale_commission']['return'];
    }
    // находим accruals_for_sale с учетом возвратов
    if (isset($one_article_data['accruals_for_sale'])) {
        $one_article_data['accruals_for_sale']['summa'] =  @$one_article_data['accruals_for_sale']['direct'] + @$one_article_data['accruals_for_sale']['return'];
    }
    // находим суммы за 1 штуку

    if (isset($one_article_data['count']['summa']) and ($one_article_data['count']['summa'] > 0)) {
        $one_article_data['amount']['one_item'] = round($one_article_data['amount']['summa'] / $one_article_data['count']['summa'], 2);
        $one_article_data['sale_commission']['one_item'] = round($one_article_data['sale_commission']['summa'] / $one_article_data['count']['summa'], 2);
        $one_article_data['accruals_for_sale']['one_item'] = round($one_article_data['accruals_for_sale']['summa'] / $one_article_data['count']['summa'], 2);
    } else {
        $one_article_data['amount']['one_item'] = 0;
        $one_article_data['sale_commission']['one_item'] = 0;
        $one_article_data['accruals_for_sale']['one_item'] = 0;
    }

    // находим сумму доп сервисов для артикула 
    if (isset($one_article_data['services'])) {
        $one_article_data['services']['summa'] = array_sum($one_article_data['services']);
    }

    // *********************** Вычитаем все, где есть артикул **********************
    $one_article_data['summa_bez_vsego_gde_est_artikul'] =
        @$one_article_data['accruals_for_sale']['summa'] +
        @$one_article_data['sale_commission']['summa'] +
        @$one_article_data['logistika']['summa'] +
        @$one_article_data['amount_ecvairing'] +
        @$one_article_data['services']['summa'];
}
/****************************************************************************************************** */

/************************************************************************************************
 * Подсчитываем все рассчетные суммы аз период
 ************************************************************************************************/
foreach ($arr_article as $article_data) {
    $arr_sum_all_data['sum_count'] = @$arr_sum_all_data['sum_count'] + @$article_data['count']['summa'];
    $arr_sum_all_data['sum_amount'] = @$arr_sum_all_data['sum_amount'] + @$article_data['amount']['summa'];

    $arr_sum_all_data['sum_sale_commission'] = @$arr_sum_all_data['sum_sale_commission'] + @$article_data['sale_commission']['summa'];
    $arr_sum_all_data['sum_sale_commission_direct'] = @$arr_sum_all_data['sum_sale_commission_direct'] + @$article_data['sale_commission']['direct'];
    $arr_sum_all_data['sum_sale_commission_return'] = @$arr_sum_all_data['sum_sale_commission_return'] + @$article_data['sale_commission']['return'];

    $arr_sum_all_data['sum_accruals_for_sale'] = @$arr_sum_all_data['sum_accruals_for_sale'] + @$article_data['accruals_for_sale']['summa'];
    $arr_sum_all_data['sum_accruals_for_sale_direct'] = @$arr_sum_all_data['sum_accruals_for_sale_direct'] + @$article_data['accruals_for_sale']['direct'];
    $arr_sum_all_data['sum_accruals_for_sale_return'] = @$arr_sum_all_data['sum_accruals_for_sale_return'] + @$article_data['accruals_for_sale']['return'];

    $arr_sum_all_data['sum_logistika'] = @$arr_sum_all_data['sum_logistika'] + @$article_data['logistika']['summa'];
    $arr_sum_all_data['sum_logistika_direct'] = @$arr_sum_all_data['sum_logistika_direct'] + @$article_data['logistika']['direct']['summa'];
    $arr_sum_all_data['sum_logistika_return'] = @$arr_sum_all_data['sum_logistika_return'] + @$article_data['logistika']['return']['summa'];



    $arr_sum_all_data['sum_ecvairing'] = @$arr_sum_all_data['sum_ecvairing'] + @$article_data['amount_ecvairing'];
    $arr_sum_all_data['sum_services'] = @$arr_sum_all_data['sum_services'] + @$article_data['services']['summa'];
}


// print_r($arr_sum_all_data);
// echo "Кол-во обработанных Строк : $i<br>";
// Если вдруг появились новые данные, которые не учитываются в разборе
if (isset($arr_nerazjbrannoe)) {
    $temp = count($arr_nerazjbrannoe);
    echo "<br> <b>Кол-во неразобранных товаров (ОЗОН Добавил новые данные в отчет </b>: $temp<br>";
    echo "<pre>";
    print_r($arr_nerazjbrannoe);
}


/****************************************************************************************************
 ******************************* ДОПОЛНИТЕЛЬНЫЙ РАЗБОР ДАННЫХ ( распределяем сумму без артикулов)
 ***************************************************************************************************/
// доставаем всю номенклатуру
$dop_uslugi = 0;
if (isset($arr_sum_services_payment)) {
    $dop_uslugi = array_sum($arr_sum_services_payment);
}
if (isset($arr_for_compensation)) {
    // echo "<br> Компенсации  *******************************************************<br>";
    // print_r($arr_for_compensation);
    $dop_compensation = array_sum($arr_for_compensation);
}
$summ_dop_uslugi_compensation = 0;
$summ_dop_uslugi_compensation = @$dop_uslugi + @$dop_compensation;



foreach ($arr_article as $key => $item) {
    $one_proc_ot_vsey_summi = round($arr_sum_all_data['sum_accruals_for_sale'] / 100, 4);

    if (isset($item['count']['summa']) and (($item['count']['summa']) != 0)) {
        $arr_article[$key]['proc_item_ot_vsey_summi'] = round($arr_article[$key]['accruals_for_sale']['summa'] / $one_proc_ot_vsey_summi, 4);
    } else {
        $arr_article[$key]['proc_item_ot_vsey_summi'] = 0;
    }



    // Распределяем сумму дополнительных услуг в процентоном соотношении
    if ($arr_article[$key]['proc_item_ot_vsey_summi'] > 0.001) {
        $arr_article[$key]['dop_uslugi'] = round((($summ_dop_uslugi_compensation / 100) * abs($arr_article[$key]['proc_item_ot_vsey_summi'])), 4);
        $ddd =  @$ddd + $arr_article[$key]['proc_item_ot_vsey_summi'];
    } else {
        $arr_article[$key]['dop_uslugi'] = 0;
    }
    $arr_article[$key]['summa_bez_vsego'] =  $arr_article[$key]['summa_bez_vsego_gde_est_artikul'] + $arr_article[$key]['dop_uslugi'];
    if (isset($item['count']['summa']) and ($item['count']['summa']) != 0) {
        $arr_article[$key]['real_price_minus_all_one_shtuka'] = round(($arr_article[$key]['summa_bez_vsego'] /  $arr_article[$key]['count']['summa']), 2);
    } else {
        $arr_article[$key]['real_price_minus_all_one_shtuka'] = 0;
    }
}


/************************************************************************************************************ */
/**  Подготовка данных для озоновской таблицы */
/************************************************************************************************************ */
require_once "_make_data_/make_data_for_sum_table.php";
/************************************************************************************************************ */

/************************************************************************************************************ */
// Вставляем данные как JavaScript-объект, а затем сохраняем в localStorage
// Данные по сервисам-штрафам, где есть номер заказа
// Чтобы можно было искать штрафы при нажатии на штрафы в первой таблице
/************************************************************************************************************ */
if (isset($arr_services_with_post_numbers)) {
    $jsonData_li = json_encode($arr_services_with_post_numbers, JSON_UNESCAPED_UNICODE);
    // Вставляем данные как JavaScript-объект, а затем сохраняем в localStorage
        echo "<script>";
        echo "var data = " . $jsonData_li . ";";
        echo "localStorage.setItem('myDataStrafi', JSON.stringify(data));";
        echo "</script>";
}

/************************************************************************************************************ */


// ВЫВОД Первой ТАБЛИЦЫ ////////////////////////////////////////////////////
require_once "_print_/1_print_sum_table.php";
require_once "_print_/1а_sum_data_table.php";
// ВЫВОД АЛАРМ ТАБЛИЦЫ ////////////////////////////////////////////////////
// если нашли неразобранные массивы то выводим алармную талицу
if ($summa_ne_naidennih_statei != 0) {
    // echo __DIR__;
    $file_name_ozon_alarm = $path ."/" . $client_id . "_alarm_index_(" . date('Y-m-d') . ")" . ".json";
    file_put_contents($file_name_ozon_alarm, json_encode($alarm_index_array, JSON_UNESCAPED_UNICODE));
    require_once "_print_/2_print_alarm_table.php";
}


// print_r($arr_sum_services_payment);
// $summa_servoceov_bez_sku = array_sum($arr_sum_services_payment_with_SKU);

/************************************************************************************************************ */
// ВЫВОД ОСНОВНОЙ ТАБЛИЦЫ ////////////////////////////////////////////////////

// посчитаем количество проданнные товаров, если их нет, то не будем выводить таблицу
$sell_count_summa = 0;
foreach ($arr_article as $key => $print_item) {
    $sell_count_summa += @$print_item['count']['summa'];
}
// Если количество  больше нуля то выводим таблицу 
if ($sell_count_summa > 0) {
    require_once "_make_data_/make_data_for_table_real_ozon.php";
    require_once "_print_/3_print_table_po_artikulam_ozon.php";
    require_once "_print_/4_print_rashozhdenia_table.php";
}





// require_once "../returns_zakaz.php";



/***************** ФУНКЦИИ ПОШЛИ **********************************************************************************************
 **********************************************************************************************************************/
function print_one_string_in_table($print_item, $parametr, $color_class = '')
// Выводит одну строку с данными из массива
{
    if (isset($print_item[$parametr])) {
        echo "<td class=\"$color_class\"><b>" . round($print_item[$parametr], 2) . "</b></td>";
    } else {
        echo "<td>" . "" . "</td>";
    }
}

function print_two_strings_in_table_two_parametrs($parametr1, $parametr2, $color_class = '')
// Выводит две строки с данными из массива
{
    if (isset($parametr1) and isset($parametr2)) {
        // echo "<td class=\"$color_class\">" .  number_format(round($parametr1, 0),0 ,',',' ') . "<hr>(" .  number_format(round($parametr2, 0),0 ,',','') ." шт)". "</td>";
        echo "<td class=\"$color_class\"><p class=\"big_font\">" .  number_format(round($parametr1, 0), 0, ',', ' ') . "</p><br><p class = \"small_font\">" .  number_format(round($parametr2, 0), 0, ',', '') . " шт" . "</p></td>";
    } else {
        echo "<td>" . "-" . "</td>";
    }
}

function print_summa_in_table($print_item, $parametr, $color_class = '')
// Выводит одну строку с данными из массива
{
    if (isset($print_item[$parametr])) {
        echo "<td class=\"$color_class\"><b>" . number_format(round($print_item[$parametr], 0), 0, ',', ' ') . "</b></td>";
    } else {
        echo "<td>" . "" . "</td>";
    }
}


function print_two_strings_for_table($data1, $data2, $color_class = '')
// Выводит две строки с данными из массива
{
    if (isset($data1) and isset($data2)) {
        echo "<td class=\"$color_class\">
        <p class=\"big_font \">" .  $data1 . "</p>
        <p class = \"small_font\">" .  number_format(round($data2, 0), 0, ',', '') . " шт" . "</p>
        </td>";
    } else {
        echo "<td>" . "-" . "</td>";
    }
}




function print_three_strings_for_table_red($data1, $data2, $data3)
// Выводит две строки с данными из массива
{
    if (isset($data1) and isset($data2) and isset($data3)) {
        echo "<td>
        <p class=\"big_font red_color\">" . number_format(round($data1, 0), 0, ',', ' ') . "</p>
        <p class = \"small_font\">" .  $data2 .  "</p>
        <p class = \"small_font red_color\">" .  number_format(round($data3, 0), 0, ',', '') . " шт" . "</p>
        </td>";
    } else {
        echo "<td>" . "-" . "</td>";
    }
}
