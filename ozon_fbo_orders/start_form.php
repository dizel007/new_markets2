<?php

$ozon_shop = $_GET['ozon_shop'];
if ($_GET['ozon_shop'] == 'ozon_anmaks') {
       $token =  $token_ozon;
       $client_id =  $client_id_ozon;
       $name_mp_shop = 'OZON ООО АНМАКС';
 
   }
       
elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
       $token =  $token_ozon_ip;
       $client_id =  $client_id_ozon_ip;
       $name_mp_shop = 'OZON ИП ЗЕЛ';
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }


echo <<<HTML
<head>
<link rel="stylesheet" href="../css/main_ozon.css">
</head>
HTML;


if (isset($_GET['dateFrom'])) {
   $date_from = $_GET['dateFrom'];
} else {
   $date_from = false;
}

if (isset($_GET['dateTo'])) {
   $date_to = $_GET['dateTo'];
} else {
   $date_to = false;
}


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>Магазин</label>
<select required name="ozon_shop">
HTML;
if ($_GET['ozon_shop'] == 'ozon_anmaks') {
  echo "<option selected value = \"ozon_anmaks\">OZON</option>";
  echo "<option value = \"ozon_ip_zel\">OZON ИП ЗЕЛ</option>";
} else {
   echo "<option  value = \"ozon_anmaks\">OZON</option>";
   echo "<option selected value = \"ozon_ip_zel\">OZON ИП ЗЕЛ</option>";
}
   

echo <<<HTML
</select>


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$date_from">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$date_to">
<input type="submit"  value="START">
</form>
HTML;

if (($date_from == false) or ($date_to == false)) {
   die ('Нужно выбрать даты');
   } 


