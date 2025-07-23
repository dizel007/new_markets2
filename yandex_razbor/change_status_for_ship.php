<?php 
require_once ("../connect_db.php"); // подключение к БД

require_once '../libs/PDFMerger/PDFMerger.php';


require_once "../pdo_functions/pdo_functions.php";

require_once "functions/functions_yandex.php";
require_once "functions/functions.php";

require_once "../mp_functions/yandex_functions.php";

// Получаем токены ЯМ
$ya_token =  get_token_yam($pdo);
$campaignId = get_id_company_yam($pdo);


$link_zip_file = $_GET['link_zip_file'];

$arr_all_new_orders = get_new_orders($ya_token, $campaignId);
// print_r($arr_all_new_orders);





// Изменяем статус заказа - на "готов к отгузке"
// доки
// https://yandex.ru/dev/market/partner-api/doc/ru/reference/orders/updateOrderStatuses?tabs=defaultTabsGroup-q78zwmu4_info


// формируем массив с заказами в которых будем изменять статус 
foreach ($arr_all_new_orders['orders'] as $order) {
 $arr_temp_id = array(
    "id" => $order['id'],
    "status" => "PROCESSING",
    "substatus" => "READY_TO_SHIP"
   );

$arr_for_change['orders'][] =  $arr_temp_id;
}

// переводим массив в json формат
$json_array_for_send = json_decode($arr_for_change);
// адрес запроса
$ya_link = "https://api.partner.market.yandex.ru/campaigns/$campaignId/orders/status-update";

// запуск смены статуса
 yandex_post_query_with_data($ya_token, $ya_link, $json_array_for_send);

echo "<br>ВЫШЛИ С ИЗМЕНЕНИЯ СТАТУСА ЗАКАЗОВ<br>";

// Ссылка для сачивания ярлыка
  echo <<<HTML
  <br><br>
  <a href="$link_zip_file"> скачать архив со стикерамии листом подбора</a>
  <br><br>
  HTML;
