<?php


$date_from = $_GET['dateFrom'] ?? date('Y-m-d');
$date_to   = $_GET['dateTo']   ?? date('Y-m-d');
$select_shop = $_GET['ozon_shop'] ?? '';
 $stop_priznak_net_magazina = 0;

if ($select_shop == 'ozon_ip_zel') {
   $select_ooo = '';
   $select_ip = 'selected';
   $token_ozon =  $token_ozon_ip;
   $client_id_ozon =  $client_id_ozon_ip;
   $shop_name = 'ozon_ip_zel';


} elseif ($select_shop == 'ozon_anmaks') {
   $select_ooo = 'selected';
   $select_ip = '';
   $token_ozon =  $token_ozon;
   $client_id_ozon =  $client_id_ozon;
   $shop_name = 'ozon_anmaks';


} else {
   $select_ooo = 'selected';
   $select_ip = '';
   $stop_priznak_net_magazina = 999;
}


echo <<<HTML
<head>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/sell_fbo_fbs_table.css">

</head>
<body>

<form  action="#" method="get">
   <label>Магазин</label>
   <select required name="ozon_shop">
      <option $select_ooo value = "ozon_anmaks">OZON ООО </option>
      <option $select_ip  value = "ozon_ip_zel">OZON ИП ЗЕЛ</option>
   </select>

   <label>дата начала</label>
   <input required type="date" name = "dateFrom" value="$date_from">
   <label>дата окончания</label>
   <input required type="date" name = "dateTo" value="$date_to">
   <input type="submit"  value="START">
</form>


HTML;


// die();
if (($date_from == false) or ($date_to == false)) {
   die ('Нужно выбрать даты');
 } 

 if ($stop_priznak_net_magazina == 999) {
   die ('');
 } 
