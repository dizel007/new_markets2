<?php
//  "ПРОСТО Подтверждаем тут все заказы";

require_once "../../connect_db.php"; // все функции
require_once "../require_funcs.php"; // все функции

// ***** Вычитываем отправления cо статусом CREATED*********************
$new_array_create_sends = get_create_spisok_from_lerua($token_lerua, $art_catalog, 'created', $lerua_limit_items);

// ***** формируем массив для потверждения заказов
if (isset($new_array_create_sends)) { // если есть неподтвержденные отправления, то разбиваем их по грузоместам
      foreach ($new_array_create_sends  as $item) {
           $id_parcel = $item['id'];
           $arr_for_complete[] = $id_parcel; // формируем массив с номерами отправлений, для подтверждения этих заказов
    }
}


/****************************************************************************************************
************************* подтвержаем все заказы
****************************************************************************************************/

foreach ($arr_for_complete as $item) {
$id_parcel = $item;
$dop_link = ':confirm';
$link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
$result_query = light_query_without_data_with_post ($token_lerua, $link, 'Запрос на подтверждение Заказа');
}



/***************** Ждем одну секунду, чтобы статусы в Леура обновились ***************************************/
sleep(1);

/****************************************************************************************************
 разбиваем все заказы по грузоместам
****************************************************************************************************/
// ***** Вычитываем отправления cо статусом packingStarted*
$new_array_create_sends = get_create_spisok_from_lerua($token_lerua, $art_catalog, 'packingStarted', $lerua_limit_items);

// ***** Запускаем функцию по разбитию товаров на грузоместа ***********
if (isset($new_array_create_sends)) { // если есть неподтвержденные отправления, то разбиваем их по грузоместам
   
    foreach ($new_array_create_sends  as $item) {
           $data_send =  make_right_posts_gruzomesta_NEW ($item['id'], $item['products']);


           print_r($data_send); //////////////////////// DELETE///////////////////////////////////////


           $id_parcel = $item['id'];
           $dop_link = '/boxes';
           $link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
           echo "<br>[$link]<br>";
// **********************   Запуск разбития по грузоотправлениям 
           $rrr = query_with_data ($token_lerua, $link, json_encode($data_send), 'Размещение по грузометам' );

$arr_for_complete[] = $id_parcel; // формируем массив с номерами отправлений, для подтверждения этих заказов
    }
}




header('Location: ../index.php');
exit();
