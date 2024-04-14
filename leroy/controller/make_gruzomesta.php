<?php
require_once "../require_funcs.php"; // все функции

// echo "РАЗБИВАЕМ ВСЕ ПО ГРУЗОМЕСТАМ<br>";

// die('DIE  в формировании грузомест и подтверждения заказа *** Нужно отключить для работы *** ');
// ***** Вычитываем отправления cо статусом CREATED*
$new_array_create_sends = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'created');


// ***** Запускаем функцию по разбитию товаров на грузоместа ***********
if (isset($new_array_create_sends)) { // если есть неподтвержденные отправления, то разбиваем их по грузоместам
    $dop_link = '/boxes';
    foreach ($new_array_create_sends  as $item) {
           $data_send =  make_right_posts_gruzomesta_NEW ($item['id'], $item['products']);
           $id_parcel = $item['id'];
           $link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
// **********************   Запуск разбития по грузоотправлениям 
           $rrr = query_with_data ($jwt_token, $link, json_encode($data_send), ' Размещение по грузометам' );
           
$arr_for_complete[] = $id_parcel; // формируем массив с номерами отправлений, для подтверждения этих заказов
    }
}


/*
ЗДесь мы только подтвержаем все заказы
*/

foreach ($arr_for_complete as $item) {
$id_parcel = $item;
$dop_link = ':confirm';
$link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
$rrrr = light_query_without_data_with_post ($jwt_token, $link, 'Запрос на подтверждение Заказа');
// echo $link."<br>";
}
// echo __DIR__;
header('Location: ../index.php');
exit();
