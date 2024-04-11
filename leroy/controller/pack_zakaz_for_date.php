<?php

require_once "../require_funcs.php"; // все функции
echo "ПЕРЕХОДИМ К Формированию Листа ЗАКАЗА и листа для 1С<br>";

$array_packingCompleted = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'packingCompleted');
                                // packingCompleted - нужно сделать , ЛИст подборки нужно после комплектации делать
                                // packingStarted - Если посмотреть лист подборки до комплектации

// Смотрим была ли выбрана дата комплектации
if (isset($_GET['date_complete_leroy'])) {
    $date_for_ship = $_GET['date_complete_leroy'];
    echo "ДАТА Формирования :".$date_for_ship,"<br>";


/// выбираем  заказы на нужную дату 
$new_array_list_podbora = get_create_spisok_with_need_date($array_packingCompleted, $_GET['date_complete_leroy']); // сортируем массив по выбранной дате

  // Создаем ЛИСТ ПОДБОРА 
require_once "make_list_podbora.php";
// Создаем файл для с количеством товаров для Заказа-клиента 1С
require_once "make_1c_file.php";  

echo "<a href=\"$link_list_podbora\">Cкачать лист подбора</a>";
echo "<hr>";
echo "<a href=\"$link_list_tovarov\">Cкачать лист для 1С</a>";
echo "<hr>";

} else {
    echo  "НЕТ ЗАКАЗОВ ДЛЯ КОМПЛЕКТАЦИИ";
}

// echo "<pre>";
// print_r($list_tovarov);
echo "<hr>";
echo "<a href=\"../index.php\">Вернуться в начало</a>";
echo "<hr>";

die('КОНЕЦ ФОРМИРОВАНИЯ листа заказа и листа для 1с');

