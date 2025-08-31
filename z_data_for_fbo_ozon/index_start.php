<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";


if (!isset($_GET['date_start']) OR !isset($_GET['date_end'])) {

$date_start = date('Y-m-d');
$date_end = date('Y-m-d');
} else {

$date_start = $_GET['date_start'];
$date_end = $_GET['date_end'];

}


echo <<<HTML
<head>
<link rel="stylesheet" href="../css/main_ozon.css">

</head>
HTML;




echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>дата начала</label>
<input required type="date" name = "date_start" value="$date_start">
<label>дата окончания</label>
<input required type="date" name = "date_end" value="$date_end">
<input type="submit"  value="START">
</form>
HTML;
echo "Период запроса с ($date_start) по  ($date_end)<br>";
