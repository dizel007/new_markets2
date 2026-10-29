<?php 
require_once ("../connect_db.php"); // подключение к БД
require_once "../pdo_functions/pdo_functions.php";

// require_once '../libs/PDFMerger/PDFMerger.php';

require_once "functions/functions.php";

require_once "../vendor/autoload.php";
use App\Yandex\YandexClient;
use App\Yandex\Service\YandexOrderService;

// Получаем токены ЯМ
$ya_token =  get_token_yam($pdo);
$campaignId =  get_id_company_yam($pdo);

// echo "<pre>";

// получаем даты на которую нужно разобрать заказы по грузоместам
if  (isset($_GET['select_date'])) {
    $need_date_temp = $_GET['select_date'];
    $need_date = date('Y-m-d' , strtotime($need_date_temp)); 
    // echo $need_date."<br>"; 
} else {
    echo "<br>NET DATE DIE<br>";
    die('die without date');
}
 
//=========================================================================================================================
// находим business_id для нашего магазинаа
//=========================================================================================================================
$yandex = new YandexClient($ya_token, $campaignId);
$campaigns = $yandex->yandex_post('v2/campaigns');
foreach ($campaigns['campaigns'] as $campaign) {
    if ($campaignId == $campaign['id']) {
        $business_id = $campaign['business']['id'];
        break;
    }

}

$orders = new YandexOrderService ($yandex, $campaignId, $business_id);

//=========================================================================================================================
// запрашваем все заказы со статусом PROCESSING -- STARTED //
//=========================================================================================================================

$arr_all_new_orders =$orders->YandexgetUnfulfilledFby($need_date_temp,  'PROCESSING',  'STARTED');

   
// выбираем из массива только заказы на выбранную дату
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
       $need_ship_date = $order['delivery']['shipment']['shipmentDate'];
             if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
              $new_arr_select_date[] = $order;
            }
    
    }

echo "* ЗАКАЗЫ ДЛЯ РАЗБИВКИ ПО ГРУЗОМЕСТАМ *";

// echo "<pre>";
// print_r($new_arr_select_date);
// die();
foreach ($new_arr_select_date as $order) { // перебираем все новые заказы

    $orderId = $order['orderId']; // ID  выбранного заказа

    echo "<br>* НОМЕР ЗАКАЗА = <b>$orderId </b> *";
        // print_r($items);
        // $res[] = razbivaev_zakazi_po_gruzomestam ($ya_token, $campaignId, $orderId, $order['items']);
foreach ($order['items'] as $item_in_order){
    for ($i = 0; $i < $item_in_order['count'] ; $i++) { // перебираем все количество этого артикула, и разбиваем по грузоместам
    $arr_one_gruz_place['items'][0] =  
    array ("id" => $item_in_order['id'],
           "fullCount"=> 1,
    );

    $arr_boxes_all["boxes"][] = $arr_one_gruz_place;
    }
}

$arr_boxes_all["allowRemove"] = false; // добавляем параметр, что мы ничего из поставки не удаляем.

// echo "<br>************************ arr_boxes_all *********************************<br>";
// print_r($arr_boxes_all);


$ya_link = 'campaigns/'.$campaignId.'/orders/'.$orderId.'/boxes';
$res[] = $yandex->yandex_put($ya_link, $arr_boxes_all) ;
unset ($arr_boxes_all);

// print_r($res); 
    
    }

echo "<h3> Количество заказов в поставке =  ".count($new_arr_select_date)."</h3>";

////////////////////////////////////////////////////////////////////////////////////////////
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    
    $orderId = $order['orderId']; // ID  выбранного заказа
    $item_number = 0; // порядквый номер товаров, если их несколько
    $need_ship_date = $order['delivery']['shipment']['shipmentDate'];
    $id_shipment = $order['delivery']['shipment']['id'];
  
        if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
          
            foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
               unset ($items['subsidies']);
                $arr_all_items[] = $items;
            }
        }

}

// Выаодим Тбалицу
if (isset($arr_all_items)) {
    print_table_with_orders ($arr_all_items, $need_date_temp);
    /// переход на разбивку заказа
echo <<<HTML

<form action="poluchenie_yarlikov.php" method= "get">
    <label>Дата сбора заказа</label>
    <input required type="date" name="select_date" value=$need_date_temp readonly>
    <label>Введите номер заказа</label>
    <input required type="text" name="order_number" value="">
    <br><br>
    <input type="submit" value="Получить Ярлыки">
</form>

    
HTML;  
}

// print_r($res);
