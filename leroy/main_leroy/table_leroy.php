<?php
require_once "require_funcs.php"; // все функции
require_once "controller/function_make_1c_file.php";

require_once '../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once "functions/excel_style.php";



echo <<<HTML
<a href="lerua_get_token_(work).php">GET NEW TOKEN</a>
<br>

<table>
<tr>

<td class="main_screen"><img src="../pics/leroy.jpg"></td>
</tr>


</table>

HTML;

// Смотрим есть ли новые неподтверждениие заказы

$new_array_create_sends = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'created');

echo "NEN ";

// echo "<pre>";
// print_r($new_array_create_sends);


if (isset($new_array_create_sends)) {
    echo "<h2>Неподтвержденные позиции (лимит ".MAX_LIMIT_ORDERS." заказов)</h2>";
    make_spisok_sendings ($new_array_create_sends);
echo "<a href=\"controller\make_gruzomesta.php\">Разобрать по грузоместам и подтвердить ВСЕ Заказы </a>";

/*****************************************************************************************************************************
 * ****************** пробууем сделать 1С предфайл
 ******************************************************************************************************************************/
$xls = new PHPExcel();
$link_list_tovarov =  make_file_for_1c ($new_array_create_sends, $art_catalog,  $xls );

} else {
    echo "<h2>НЕТ НЕподтвержденных позиций</h2>";  
}



// Смотрим есть ли подтвержденные заказы на определенную дату 
echo "<hr>";

/// вводим переменную, чтобы в инпуты ввода даты подставлять
$date_for_input1='';
if ((isset($_GET['date_complete_leroy'])) && (isset($_GET['type_query']))) {
    if ($_GET['type_query'] == "345") {
        $date_for_input1=$_GET['date_complete_leroy'];
    }
} 

echo <<<HTML
<h2>Найти заказы для комплектации по дате</h2>
<div>
    <form method="get" action="#">
    <input  required type="date" name="date_complete_leroy" value="$date_for_input1">
    <input  hidden type="number" name="type_query" value="345"> 
    <input type="submit" value="Найти заказы для комплектации">
    </form>
</div>
HTML;

if (isset($_GET['date_complete_leroy']) AND ($_GET['type_query'] == "345")) {
    $date_complete_leroy = $_GET['date_complete_leroy'];
    $array_packingStarted = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'packingStarted');
    if (isset($array_packingStarted)){
    $new_array_packingStarted= get_create_spisok_with_need_date($array_packingStarted, $_GET['date_complete_leroy']); // сортируем массив по выбранной дате
    }
    // echo "<pre>";
    // print_r($new_array_packingStarted);
    // die('PACK');


    if (isset($new_array_packingStarted)) {
        make_spisok_sendings ($new_array_packingStarted);

        echo "<a href=\"controller\complete_all_zakaz.php?date_complete_leroy=$date_complete_leroy\"><h2>Скомплектовать ВСЕ Заказы </h2></a>";
        // Создаем файл для с количеством товаров для Заказа-клиента 1С
    

        } else {
            echo "<h2><b>НЕТ ПОДТВЕРЖДЕННЫХ заказов</b> на эту ДАТУ ($date_complete_leroy)</h2>";  
            
        }

}

//  ***************************  Смотрим есть ли скомплектованные заказы на определенную дату *******************************************
echo "<hr>";
$date_for_input2='';
/// вводим переменную, чтобы в инпуты ввода даты подставлять
if ((isset($_GET['date_complete_leroy'])) && (isset($_GET['type_query']))) {
    if ($_GET['type_query'] == "647") {
        $date_for_input2=$_GET['date_complete_leroy'];
    }
} 

echo <<<HTML

<h2>Найти заказы СКОМПЛЕКТОВАННЫЕ ЗАКАЗЫ по дате (Функция для создания Листа подбора и листа для 1С)</h2>
<div>
    <form method="get" action="#">
    <input  required type="date" name="date_complete_leroy" value="$date_for_input2">
    <input  hidden type="number" name="type_query" value="647"> 
    <input type="submit" value="Найти скомплектованные заказы">
    </form>
</div>
HTML;

if (isset($_GET['date_complete_leroy']) AND ($_GET['type_query'] == "647")) {
    $array_packingCompleted = get_create_spisok_from_lerua($jwt_token, $art_catalog, 'packingCompleted');
    if (isset($array_packingCompleted)){
    $new_array_packingCompleted= get_create_spisok_with_need_date($array_packingCompleted, $_GET['date_complete_leroy']); // сортируем массив по выбранной дате
    }


    if (isset($new_array_packingCompleted)) {
        make_spisok_sendings ($new_array_packingCompleted);
        $date_complete_leroy = $_GET['date_complete_leroy'];
        echo "<a href=\"controller\pack_zakaz_for_date.php?date_complete_leroy=".$date_complete_leroy."&type_query=647\"><h2>Сформировать лист Подбора и лист для 1С </h2></a>";
   } else {
        echo "<h2>НЕТ Заказов для комплектации на эту ДАТУ</h2>";  
   }

}