<?php
require_once "connect_db.php";

require_once "functions/functions.php";

require_once "main_wb/header.php";

$shop_name = 'Информация по ООО ТД АНМАКС  (нов)';
$token_wb_orders = $token_wb;
require_once "main_wb/get_orders.php"; // отрисовываем тут таблицы



require_once "main_wb/footer.php";
