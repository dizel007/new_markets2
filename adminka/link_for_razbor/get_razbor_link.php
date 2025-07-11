<?php


$offset = "../../";
require_once($offset."connect_db.php"); // подключение к БД

require_once($offset."pdo_functions/pdo_functions.php");  // 


// echo "<pre>";
// print_r($_POST);
require_once "select_shop.php";

$order_number = $_POST['order_number'];
$shop_name    = $_POST['shop_name'];
$date_order   = $_POST['date_order'];


$sql = "SELECT * FROM `table_razbor`";

if (($order_number <> '') OR ($shop_name <> '') OR ($date_order <> '')) {
    $sql = $sql . " WHERE "; 
}

$and="";

// выборка по номеру заказа
if ($order_number <> '') {
    $sql = $sql."`number_order` = $order_number";
    $and = "AND";
}
// выборка по магащину
if ($shop_name <> '') {
    if ($and == "AND") {
        $sql = $sql." $and `type_shop` = '$shop_name'";
    } else {
        $sql = $sql." `type_shop` =  '$shop_name' ";
    }
    $and = "AND";
}
// выборка по дате отгрузки
if ($date_order <> '') {
    if ($and == "AND") {
        $sql = $sql." $and `date_razbora` = '$date_order'";
    } else {
        $sql = $sql." `date_razbora` =  '$date_order' ";
    }
    $and = "AND";
}
// сотрировка по дате
$count_zakazov = 30; // количество показываемых заказов
$sql = $sql. " ORDER BY `date_razbora` DESC LIMIT $count_zakazov";

// echo " {{$sql}}";

$stmt = $pdo->prepare($sql);
$stmt->execute([]);
$arr_razbor_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);



echo <<<HTML
<head>
<link rel="stylesheet" href="css/table_razbor.css">
<title>Список разбобранных заказов</title>
</head>

<body>



<h2 class="h2_color_green center">Список последних разборов</h2>
<h2 class="h2_color_green center">Номер заказ: $order_number Дата отгрузки: $date_order Маркет: $shop_name</h2>
<table class="svod_po_ostatkam">


HTML;
foreach ($arr_razbor_orders as $orders) {

 $link1 =  $orders['link1']  ;
 $link1 = str_replace('wb_new_razbor/', '',$link1);
 $link1 = str_replace(DOMAIN_NAME.'/', DOMAIN_NAME,$link1);
 $link1 = str_replace(' ', '%20',$link1);
 
 $link2 =  $orders['link2']  ;
 $link2 = str_replace('wb_new_razbor/', '',$link2);
 $link2 = str_replace(DOMAIN_NAME.'/', DOMAIN_NAME,$link2);
//  $link2 = str_replace('ow2/', 'ow2',$link2);
 $link2 = str_replace(' ', '%20',$link2);
/// **********************************  ВБ ОООО *************************************
 if($orders['type_shop'] == 'wb_anmaks' )  {
    $shop_name = 'ВБ ООО';
    $link1_name = "Скачать QR этикетки $shop_name";
    $link2_name = "Скачать QR коды поставок $shop_name";
/// **********************************  ВБ ИП *************************************
 } elseif ($orders['type_shop'] == 'wb_ip_zel'){
    $shop_name = 'ВБ ИП';
    $link1_name = "Скачать QR этикетки $shop_name";
    $link2_name = "Скачать QR коды поставок $shop_name";
/// **********************************  ОЗОН ООО *************************************
 } elseif ($orders['type_shop'] == 'ozon_anmaks'){
    $shop_name = 'Озон ООО';
    $link1_name = "";
    $link2_name = "Скачать ШТРИХКОДЫ $shop_name";
/// **********************************  ОЗОН ИП *************************************
} elseif ($orders['type_shop'] == 'ozon_ip_zel'){
     $shop_name = 'Озон ИП';
     $link1_name = "";
     $link2_name = "Скачать ШТРИХКОДЫ $shop_name";
} else {
     $shop_name = $orders['type_shop'];
     $link1_name = "XZ";
    
}

echo "<tr>";
    echo "<td>".$shop_name."</td>";
    echo "<td>".$orders['number_order']."</td>";
    echo "<td>".$orders['date_otgruzki']."</td>";
    echo "<td>"."<a href=$link1>$link1_name</a>"."</td>";
    echo "<td>"."<a href=$link2>$link2_name</a>"."</td>";
echo "</tr>";
}

echo <<<HTML
</table>

</body>

HTML;

