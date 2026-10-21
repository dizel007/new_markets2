<?php 
require_once ("../connect_db.php"); // подключение к БД

require_once '../libs/PDFMerger/PDFMerger.php';


require_once "../pdo_functions/pdo_functions.php";

require_once "functions/functions_yandex.php";
require_once "functions/functions.php";

require_once "../mp_functions/yandex_functions.php";
require_once "../mp_functions/yandex_api_functions.php";
// Получаем токены ЯМ
$ya_token =  get_token_yam($pdo);
$campaignId = get_id_company_yam($pdo);


$link_zip_file = $_GET['link_zip_file'];
$need_date = $_GET['need_date'];

// **************************************************************************************
// Получаем все новые заказы
// **************************************************************************************
$arr_all_new_orders = get_new_orders($ya_token, $campaignId);

$del_i = 0; // переменная которая показывает какой заказ нужно удалить с массива
$i=0;
$max_size_for_patch = 0; // максимально 30 заказов можно изменить
// **************************************************************************************
// удаляем заказы на другие даты, а из нужных дат формируем массив для изменения состояния 
// **************************************************************************************
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    $need_ship_date = $order['delivery']['shipments'][0]['shipmentDate'];
        if ($need_date != $need_ship_date)  {    /// выбор даты дня отгрузки
            // если дата не совпадает, то мы удаляем этот заказ из массива
            unset($arr_all_new_orders['orders'][$del_i]);
        } else {
         // если дата совпадает, то формируем массив для перевода в отгрузку
            $arr_temp_id = array(
               "id" => $order['id'],
               "status" => "PROCESSING",
               "substatus" => "READY_TO_SHIP"
             );

             $arr_for_change[$i]['orders'][] =  $arr_temp_id;
             $max_size_for_patch ++;
             // создаем следующий массив, тк максимум у 30 заказов можно изменить статус за один заропс
             if ($max_size_for_patch >=29) {
               $max_size_for_patch=0;
               $i++;
             }

        }

 $del_i++;
}




// echo "<pre>";
// print_r($arr_for_change);

// die();
// Изменяем статус заказа - на "готов к отгузке"
// доки
// https://yandex.ru/dev/market/partner-api/doc/ru/reference/orders/updateOrderStatuses?tabs=defaultTabsGroup-q78zwmu4_info

// die();
// адрес запроса
$ya_link = "https://api.partner.market.yandex.ru/campaigns/$campaignId/orders/status-update";

// запуск смены статуса
foreach ($arr_for_change as $arr_for_patch) {
   // print_r($arr_for_patch);
 yandex_post_query_with_data($ya_token, $ya_link, $arr_for_patch);
}

//  print_r($result);

// echo "<br>ВЫШЛИ С ИЗМЕНЕНИЯ СТАТУСА ЗАКАЗОВ<br>";

echo <<<HTML

<head>
<link rel="stylesheet" href="css/polychenie_yarlikov.css">  
</head>
<body>
  <div class="container">
      <div class="title_up">Заказы готовы к отгрузке</div>
     <div class="title"> </div>
    <div class="buttons">
      <a href="$link_zip_file"> <button class="button_download"> Cкачать архив со стикерамии листом подбора</button></a>
     </div>
    <div class="footer">Разбор заказов ЯндексМаркета завершен!</div>
</div>

</div>
</body>
HTML;

// echo <<<HTML
// <div class="buttons">
//       <a href="$link_zip_file"> <button class="button_download"> Cкачать архив со стикерамии листом подбора</button></a>
//      </div>

// HTML;