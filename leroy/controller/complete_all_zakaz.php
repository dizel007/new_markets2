<?php
require_once "../require_funcs.php";
$date_complete_leroy = $_GET['date_complete_leroy'];
$array_packingStarted = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'packingStarted');
$new_array_packingStarted= get_create_spisok_with_need_date($array_packingStarted, $_GET['date_complete_leroy']); // сортируем массив по выбранной дате

//  echo "<pre>";
//  print_r($new_array_packingStarted);
// die(' DIE при упаковке товара   *** ОТКЛЮЧИТЬ ПРИ РАБОТЕ***');

foreach ($new_array_packingStarted as $item) {
    $id_parcel = $item['id'];
    $dop_link = ':pack';
    $link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
    $rrrr = light_query_without_data_with_post ($jwt_token, $link, 'Запрос на подтверждение Заказа');
    // echo $link."<br>";
    }
    // запускаем переход на формирования листа подбора и листа для 1С
    header('Location: pack_zakaz_for_date.php?date_complete_leroy='.$date_complete_leroy."&type_query=647");
    exit();