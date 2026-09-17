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

$shop_name = 'ozon_ip_zel';



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
// echo "<pre>";
// print_r($arr_article_products );

// die();

$priznak_date = 1;
// Настраиваем дату начала отпроса
if (isset($_GET['dateFrom'])) {
    $date_from = $_GET['dateFrom'];
} else {
    $date = date('Y-m-d');
    $day = '01';
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $date_from = $year . '-' . $month . '-' . $day;
    $priznak_date = 0;
}

// Настраиваем дату окончания
if (isset($_GET['dateTo'])) {
    $date_to = $_GET['dateTo'];
} else {
    $date_to = date('Y-m-d');
}

// Настраиваем тип сортировки если он есть 
if (isset($_GET['type_sort'])) {
    $type_sort = base64_decode(($_GET['type_sort']));
} else {
    $type_sort = '';
}

// ."&type_sort=".$type_sort;
//// Отрисовываем форму вводы ДАТ
echo <<<HTML

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/form_dates.css">
    <title>OZON - аналитика</title>
    
</head>
<body>
    <div class="table-container">
    <div class="form-container">
        <form id="dateForm" method="get">
   
            <div class="form-content">
                <div class="date-fields">
                    <div class="date-group">
                        <label for="startDate" class="date-label">Начальная дата</label>
                        <input type="date" id="startDate" class="date-input" value="$date_from" required >
                   </div>
                    <div class="date-group">
                        <label for="endDate" class="date-label">Конечная дата</label>
                        <input type="date" id="endDate" class="date-input" value="$date_to" required>
                  </div>
           
                </div>
                
                 <div class="date-group">
                        <button type="submit" class="submit-btn">Запросить данные</button>
                 </div>
             </div>
            <input hidden type="text" id = "ozon_shop" value="$shop_name">
              
          

        </form>
       </div>
    </div>
 <script src="css/script.js" type="text/javascript"></script>

HTML;



if (!isset($_GET['dateFrom'], $_GET['dateTo'])) {
    die();
}



$ozon_link = 'v1/finance/balance';
$last_id = '';

// $date_from = strtotime($date_from);
// $date_to  = strtotime($date_to);

/******************************************************************************
 * ЗАПРОС ДАННЫХ С ОЗОНА (ВСЕ ДАТЫ)
 ******************************************************************************/

        $send_data = array(
            "date_from" => $date_from,
            "date_to" => $date_to
        );
        $send_data = json_encode($send_data);
        $data_by_day_temp = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link);



echo "<pre>";
print_r($data_by_day_temp);




// 04398351-0221
// echo "<pre>";
// print_r($category);




die();
