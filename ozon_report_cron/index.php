<?php

require_once "../main_info.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once '../vendor/autoload.php';

// require_once "../".__DIR__ . '/vendor/autoload.php'; // если это не точка входа, а отдельный скрипт


use App\Ozon\OzonClient;
use App\Ozon\Service\ProductService;
use App\Ozon\Service\FinanceService;





require_once "function_report/razbor_type_item.php";
require_once "function_report/razbor_type_posting_number.php";
// require_once "function_report/get_sebestoimost.php";
// require_once "function_report/get_balance.php";
// require_once "function_report/get_prodazhi.php";
require_once "../mp_functions/get_token_by_shop.php";


/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
 *********************************************************************************************************/
// echo  "ПРОШЛИ КОННЕКТ<br>";


      try {  
        $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
        $pdo->exec('SET NAMES utf8');

        } catch (PDOException $e) {
          print "Has errors: " . $e->getMessage();  die();
        }



$shop_name = 'ozon_anmaks';
$token = '78944e3d-8722-4bfe-9d71-1b6970af47dd';
$client_id = '45537';
// получаем токен запрашиваемого магазина


$ozon    = new OzonClient($token, $client_id);
$product = new ProductService($ozon);
$finance = new FinanceService($ozon);


 //**** получаем себестоимость товараов *****************************************************************************************************/

$arr_article_products  = $product->getSebestoimostTovarov();
//******************************************************************************************************
//******************************************************************************************************
// Настраиваем дату начала отпроса

    $date = date('Y-m-d');
    $day = '01';
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $date_from = $year . '-' . $month . '-' . $day;
    $priznak_date = 0;
// Настраиваем дату окончания
$date_to = date('Y-m-d');

$ozon_array_balance = $finance->getBalance($date_from, $date_to);


// echo "<pre>";
// print_r($ozon_array_balance);
// echo "<br>$date_from<br>";
// echo "<br>$date_to<br>";
// die();
/******************************************************************************
 *  ЗАПРОС фин отчета по проанным товарам  С ОЗОНА (ВСЕ ДАТЫ)
 ******************************************************************************/
$userdata['user_login'] = 'zeld';
//  Запрос товаров проданных в РФ
$dir = '../!cache/' . $userdata['user_login'];
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

$file_name_ozon_mainSell = $dir . '/' . $client_id . '_main_data_cron.json';
$finance->getAccrualsByDay( $date_from,  $date_to, $file_name_ozon_mainSell);


// get_data_sell_in_mainSEll ($token, $client_id, $date_from, $date_to, $file_name_ozon_mainSell);

$data_by_days = json_decode(file_get_contents($file_name_ozon_mainSell), true);


// echo "<pre>";
// print_r($data_by_days);

//  Запрос инстранных товаров
$file_name_ozon_inostran_prodazhi = $dir . '/' .$client_id . "_ino_main_data_cron" . ".json";
// $arr_sell_v_strani_EAES = get_data_sell_in_srtani_eaes($token, $client_id, $date_from, $date_to, $file_name_ozon_inostran_prodazhi);

$arr_sell_v_strani_EAES =  $finance->getBuyoutEaes( $date_from,  $date_to, $file_name_ozon_inostran_prodazhi);
/******************************************************************************
 * КОНЕЦ запроса данных с озона (ВСЕ ДАТЫ)
 ******************************************************************************/


 /// Разбиваем массив по категориям расходов ///////////////////////////////
foreach ($data_by_days as $one_data) {
    $category[$one_data['accrued_category']] = $one_data['accrued_category'];
    if (isset($one_data['accrued_category'])) {
        $arr_by_accrued_category[$one_data['accrued_category']][] = $one_data;
    } else {
        $arr_other[] = $one_data;  // или любой другой массив
    }
}
////////////////////////////////////////////////////////////////////
if (isset($arr_other)) {
    echo "<br>НАйден массив без категории<br>";
}

//////////////////////////////////////////////////////////////////////////////////////////////////////
// берем из JSON файла статьи всех расходов с ID и названием и описанием
//////////////////////////////////////////////////////////////////////////////////////////////////////
$type_fees = json_decode(file_get_contents('spravochnik/types_spend.json'), true);
//////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
//  Разбираем массив по категориям затрат
//////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset ($arr_by_accrued_category['ITEM'])) {
    $sum_array = razbor_ITEM_category($sum_array, $arr_by_accrued_category['ITEM'], $type_fees);
}
if (isset ($arr_by_accrued_category['NON_ITEM'])) {
$sum_array = razbor_NON_ITEM_category($sum_array, $arr_by_accrued_category['NON_ITEM'], $type_fees);
}
if (isset ($arr_by_accrued_category['POSTING'])) {
$sum_array = razbor_POSTING_category($sum_array, $arr_by_accrued_category['POSTING'], $type_fees);
}
foreach ($sum_array as $item_sum) {
    foreach ($item_sum as $key_sum=>$sum) {
        @$sum_array_al_data[$key_sum] += $sum;
   }
}

//===============================================================================================
// цепляем иностарнные продажи *************************
//===============================================================================================
$ino_summa = 0;
if (isset ($arr_sell_v_strani_EAES['products'])) {
foreach ($arr_sell_v_strani_EAES['products'] as $ino_sku=>$ino_tovar) {
    foreach ($sum_array as $sku=>&$tovar) {
        if ($ino_sku == $sku) {
            $tovar['ino_prodazhi'] = $ino_tovar['amount'];
            $ino_summa += $ino_tovar['amount'];
            break 1;
        }
    }
}

}
$sum_array_to_redakt = $sum_array;

/******************************************************************************************
 * выводим на печать нашу таблицу
 * весь вывод сделал в отдельном файле
 * 
 ****************************************************************************************/
// echo "<pre>";
// print_r($ozon_array_balance );

require_once "print_info/print_sku_table.php";


$file_name_ozon_cron = $dir . '/' . $client_id . 'pribil.txt';

$text = date('Y-m-d H:i:s') . " Приб= $sum_pribil, Выпл=$sum_viplata_na_rs, Ино.=$sum_ino_prodazh,  Себ=$sum_article_sebestoimost, Сумма=$sum_seller_price, Ком-я=$sum_commission, Лог=$sum_logistika, Серв=$sum_service,  Эк= $sum_equairing, Доп=$sum_no_sku_trati_raschet\n";

// moneyNum($sum_viplata_na_rs) . "</b></td>";
//     echo "<td data-total-key=\"ino_prodazhi\"><b>"   . moneyNum($sum_ino_prodazh) . "</b></td>";
//     echo "<td data-total-key=\"sebestoimost\"><b>"   . moneyNum($sum_article_sebestoimost) . "</b></td>";
//     echo "<td data-total-key=\"profit\"><b>"         . moneyNum($sum_pribil) 

file_put_contents($file_name_ozon_cron, $text, FILE_APPEND | LOCK_EX);



die();
