<?php

include_once "../../connect_db.php";

// print_r($_GET);
if (isset($_GET['shop_name'])) {
$shop_name = $_GET["shop_name"];
    if (isset($_GET['all_active'])) {
    $all_active = " WHERE `active_tovar` = 1"; 
    } else {
        $all_active = "";  
    }
} else {
    die('NO SHOP');
}
$stmt = $pdo->prepare("SELECT * FROM `$shop_name`".$all_active);
$stmt->execute([]);
$array_db_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


//// сортировка по сумме продаж
$price = array_column($array_db_items, 'main_article');
array_multisort($price, SORT_ASC, $array_db_items);

// ищем сумму распределние объема на ФБС // если несколько артикулов

// echo "<pre>";
// print_r($array_db_items[5]);

/// Сортируем массив по артикулам
foreach ($array_db_items as $items_sum) {
    $arr_all_fbs[$items_sum['main_article']] = @$arr_all_fbs[$items_sum['main_article']] + $items_sum['fbs'];
}

// print_r($arr_all_fbs);


echo <<<HTML

<head>
<link rel="stylesheet" href="css/table_shops.css">
<title>Список разбобранных заказов</title>
</head>

<body>

<h2 class="h2_color_green center">Список товаров магазина : $shop_name</h2>

<form action="update_shop_data.php" method="post">

<table class="svod_po_ostatkam">
<tr class="green_color">
    <td> Основной<br>артикул</td>
    <td> артикул<br>магазина</td>
    <!-- <td> Наименование </td> -->
    <td> SKU </td>
    <!-- <td> Product_id </td> -->
    <td> barcode </td>
    <td> Объем на ФБС </td>
    <td> ВЕСЬ ОБъем на ФБС </td>
    <td> Активен </td>
    <td> Липовое <br>кол-во </td>
</tr>
    
    
HTML;
$p=0;
foreach ($array_db_items as $items_sum) {

    ($items_sum['active_tovar'] == 0 )?$active_tovar = "orange_color": $active_tovar = "green_color";   
echo "<tr  class=\"$active_tovar\">";
($arr_all_fbs[$items_sum['main_article']] > 100)?$alarm_value = "red_color": $alarm_value = "green_color";
   
    if (@$main_article != $items_sum['main_article']) {
    echo "<td>".$items_sum['main_article']."</td>";
    } else {
    echo "<td>"."--------"."</td>";
    }
    echo "<td>".$items_sum['mp_article']."</td>";
    // echo "<td>".$items_sum['mp_name']."</td>";
    echo "<td>".$items_sum['sku']."</td>";
    // echo "<td>".$items_sum['product_id']."</td>";
    echo "<td>".$items_sum['barcode']."</td>";

    // SKU товара 
    echo "<input type=\"hidden\" readonly type=\"text\" name = \"_sku_{$p}\"  value=\"{$items_sum['sku']}\">";
// % распределниея товара по ФБС 
    echo "<td class=\"\">
          <input  class=\"\" type=\"number\" step=\"1\" min=\"0\" max=\"100\" name=\"_fbs_{$p}\" value =\"{$items_sum['fbs']}\" required>
         </td>";
// % распределниея товара по ФБС на всему артикулу ( Если несколько товаров одного артикула) 
    echo "<td class=\"$alarm_value\">".$arr_all_fbs[$items_sum['main_article']]."</td>";
// Активный ли товар 
if ($items_sum['active_tovar'] == 1) {
    echo  "<td><input type=\"checkbox\" checked name=\"_on_check_{$p}\" value = \"1\"> </td>";
  } else {
    echo  "<td><input type=\"checkbox\" name=\"_on_check_{$p}\" value = \"0\"> </td>";
 }
 // Количество фейковых товаров по ФБС 
    echo "<td class=\"\">
              <input  class=\"\" type=\"number\" step=\"1\" min=\"0\" max=\"500\" name=\"_fake_count_{$p}\" value =\"{$items_sum['fake_count']}\" required>
          </td>";



   

echo "</tr>";
$main_article = $items_sum['main_article'];
$p++;
}
    
echo <<<HTML
</table>
<input type="hidden" readonly type="text" name = "_shop_name_"  value="$shop_name">

<input class="btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ">

</form>
</body>

HTML;
