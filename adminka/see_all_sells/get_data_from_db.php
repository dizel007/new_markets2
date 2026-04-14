<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset ."mp_functions/ozon_api_functions.php";
require_once $offset ."pdo_functions/pdo_functions.php";

require_once "get_article_and_date.php"; // Форма по выбору даты и артикулов



try {
    // Используйте подготовленный запрос для безопасности
        $sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_sell` WHERE `date` >= :date_start AND `date` <= :date_end");
        $sth->execute(array('date_start' => $date_start , 'date_end' => $date_end));
        $array_sell = $sth->fetchAll(PDO::FETCH_ASSOC);

        // $sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_stocks` WHERE `date` >= :date_start AND `date` <= :date_end");
        // $sth->execute(array('date_start' => $date_start, 'date_end' => $date_end));
        // $array_stock = $sth->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "Ошибка базы данных"]);
}




 /***********************************************************************************************
 ********************************* Проверяем нужно ли тянуть данные с сайта озона *************** 
 ***********************************************************************************************/
// if (isset($_GET['data_from_site'])) {
//     $data_from_site = $_GET['data_from_site'];

    if ($date_start == $date_end) {
    $date_from = $date_start;    
    } else {
    $date_from = date('Y-m-d', strtotime('-1 day', strtotime($date_end)));
    }
    require_once "get_fbo_last_days.php";

// } else {
//     $data_from_site = "no";
// }

$i=0;
foreach ($array_sell as &$items) {
        $test_article = $items['1c_article'];
            if (!in_array($test_article, $need_article, true)) {
               unset($array_sell[$i]);
             }
       $i++;
}
// Формруем массив проданных товаровпо датам / артикулам
foreach ($array_sell as $items) {
        
       $arr_sells_for_print [$items['date']][$items['1c_article']][$items['type_sklad']][$items['shop_name']][] =  $items['fbo_sell'];
 // добавляем сумму товаров артикула за один день
        $arr_sells_for_print [$items['date']][$items['1c_article']]['summa'] =  
        @$arr_sells_for_print [$items['date']][$items['1c_article']]['summa'] + $items['fbo_sell'];

// Формируем массив сколько товаров артикула продано за день период
        $arr_sum_all_date[$items['1c_article']] =  @$arr_sum_all_date[$items['1c_article']] + $items['fbo_sell'];
// Формируем массив сколько товаров артикула продано за день период
        $arr_sum_one_day_one_type_sklad[$items['1c_article']][$items['type_sklad']][$items['shop_name']] = 
         @$arr_sum_one_day_one_type_sklad[$items['1c_article']][$items['type_sklad']][$items['shop_name']] + $items['fbo_sell'];


// массив артикулов
        $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
// массив дат
        $arr_dates[$items['date']] = $items['date']; // массив дат

        
}



echo '<link rel="stylesheet" href="css/see_all_sells.css">';
echo '<link rel="stylesheet" href="css/test.css">';

if (!isset($arr_dates)) {
      echo "Нет данных для вывода";
      die();
}



// сортируем даты по возрастанию
sort($arr_dates);


// echo "<pre>";
// print_r($array_sell);
// die();


require_once "print_sells_table.php";