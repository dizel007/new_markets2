<?php
// Скрип что складывать среднее время доставки по ФБО
// Считываем для двух магазинов озон
// В начале скрипта

require_once ("../main_info.php");
require_once ("../mp_functions/ozon_api_functions.php");
require_once "../pdo_functions/pdo_functions.php";


       try {  
        $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
        $pdo->exec('SET NAMES utf8');

        } catch (PDOException $e) {
          print "Has errors: " . $e->getMessage();  die();
        }

   // Получаем все токены
    $arr_tokens = get_tokens($pdo);
    
    // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

$date = date('Y-m-d');
// $date_minus_one_day = date('Y-m-d', strtotime('-1 day', strtotime($date)));

/*********************************************************************
 ********           ПОЛУЧАЕМ ПРОДАЖИ ФБО для предыдущий день ********
 *********************************************************************/

//// для ООО
$token_ozon =  $token_ozon;
$client_id_ozon = $client_id_ozon;
$shop_name = 'ozon_anmaks';

$arr_data =  get_middle_time_delivery_from_ozon ($token_ozon, $client_id_ozon);
insert_data_about_middle_time_delivery($pdo, $shop_name, $arr_data);


//  для ИП
$token_ozon =  $token_ozon_ip;
$client_id_ozon = $client_id_ozon_ip;
$shop_name = 'ozon_ip_zel';

$arr_data = get_middle_time_delivery_from_ozon ($token_ozon, $client_id_ozon);
insert_data_about_middle_time_delivery($pdo, $shop_name, $arr_data);

  

die();


/****************************************************************************************
 * Функция вставки в базу данных данных о среднем времени доставки
 ****************************************************************************************/
function insert_data_about_middle_time_delivery($pdo, $shop_name, $arr_data) {

$sql = "INSERT INTO `z_ozon_cron_middle_time_delivery` 
        (`shop_name`, `date_stamp`, `average_delivery_time`, `perfect_delivery_time`, 
         `tariff_value`, `fee`, `lost_profit`) 
        VALUES 
        (:shop_name, :date_stamp, :average_delivery_time, :perfect_delivery_time, 
         :tariff_value, :fee, :lost_profit)";

$sth = $pdo->prepare($sql);

// Данные для вставки
$data = [
    'shop_name' => $shop_name, 
    'date_stamp' => $arr_data['updated_at'], 
    'average_delivery_time' => $arr_data['average_delivery_time'],
    'perfect_delivery_time' => $arr_data['perfect_delivery_time'],
    'tariff_value' => $arr_data['current_tariff']['tariff_value'],
    'fee' => $arr_data['current_tariff']['fee'],
    'lost_profit' => $arr_data['lost_profit']
];

try {                                    
    $sth->execute($data);
    // echo "Запись успешно добавлена. ID новой записи: " . $pdo->lastInsertId();
} catch (PDOException $e) {
    echo "Ошибка при добавлении записи: " . $e->getMessage();
}

}

/****************************************************************************************
 * Функция получения проданных через ФБО товаров
 ****************************************************************************************/
function get_middle_time_delivery_from_ozon ($token, $client_id) {
// находим время доставки за последнюю неделю 
$ozon_dop_url = "v1/analytics/average-delivery-time/summary";
$send_data = '';
$average_delivery_time = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url ) ;
return $average_delivery_time;

}
