<?php
$offset = "../";
require_once $offset."connect_db.php";

require_once "functions/functions.php";

require_once "main_wb/header.php";

require_once "get_zakaz_by_check_date.php"; // функция выбора заказоа с учетом выбранной даты

$shop_name = 'Информация по ИП Зел';
$token_wb_orders = $token_wb_ip;
// $transition_wb = 11;
require_once "main_wb/get_orders.php"; // отрисовываем тут таблицы

require_once "main_wb/footer.php";
