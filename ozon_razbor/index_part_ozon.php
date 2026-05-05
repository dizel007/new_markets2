<?php
require_once "../connect_db.php";
require_once "main_ozon/header.php";
require_once 'include_funcs.php';

$get_shop_name = $_GET['shop_name'];
if ($get_shop_name == 'ozon_anmaks' ) {
    $token_ozon =  $token_ozon;
    $client_id_ozon = $client_id_ozon;

} elseif  ($get_shop_name == 'ozon_ip_zel' ) {
    $token_ozon =  $token_ozon_ip;
    $client_id_ozon = $client_id_ozon_ip;
} else {
    die('Магазин не нашли');
}

/*****************************************************************************************************************
 *****   НАстраиваем дату начала сбора и количество дней 
 **************************************************************************************************************** */
$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -15 day'));
// $date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' +1 day'));
$dop_days_query = 20;

echo "<h1>ЧАСТИНЧЫЙ  РАЗБОР ТОВАРОВ</h1>";
// ****************************************************************************************************************
// Запрашиваем данные по отправлениям за несколько дней
// ****************************************************************************************************************
   $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging" , $dop_days_query);

// Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.
$kolvo_tovarov = 0;
$summa_tovarov = 0;
$array_art_price = [];
   foreach ($res['result']['postings'] as $posts) {

      foreach ($posts['products'] as $prods) 
        {
           $array_art[$prods['offer_id']]= @$array_art[$prods['offer_id']] + $prods['quantity'];
           $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
           $summa_tovarov= $summa_tovarov + $prods['price'] * $prods['quantity'];
        //    echo $prods['price']."<br>";
          $array_art_price[$prods['offer_id']] = array("price"    => $prods['price'],
                                                       "quantity" => $array_art[$prods['offer_id']],
                                                        "name"    => $prods['name']);
        }
 }

 // ****************************************************************************************************************
 //  Выводим таблицу с Количество купленно

  // Ссылка для запуска сбора всех заказов

        $link ="controller/make_part_zakaz.php";


 // если мы определилисть что будем собиать, то выводим кнопку формы для начала разбора товаров

echo <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами - садовые бордюры</title>
 <link rel="stylesheet" href = "css/razbor_part.css">
</head>
<body>
<div class="container">

    <div class="table-wrapper">
        <form method="POST" id="productsForm" action="$link">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>№ п/п</th>
                        <th>Артикул</th>
                        <th>Наименование</th>
                        <th>Кол-во (факт)</th>
                        <th>Новое количество<br><span style="font-size:0.75rem; font-weight:normal;">(≤ факт.)</span></th>
                        <th>Выбрать</th>
                    </tr>
                </thead>
                <tbody>
HTML;
?>
                   <?php 
                   $idx = 0;
                   foreach ($array_art_price as $art => $product): 
                        $maxQty = $product['quantity'];
                        $idx++;
                    ?>
                    <tr data-index="<?= $idx ?>" data-max-qty="<?= $maxQty ?>">
                        <td class="index-cell"><?= $idx  ?></td>
                        <td><span class="article-cell"><?= htmlspecialchars($art) ?></span></td>
                        <td class="name-cell"><?= htmlspecialchars($product['name']) ?></td>
                        <td class="qty-stock-cell"><?= htmlspecialchars($product['quantity']) ?></td>
                        <td class="new-qty-cell">
                            <input type="number" 
                                   name="new_qty[<?php echo $art; ?>]" 
                                   class="new-qty-input" 
                                   value="0" 
                                   min="0" 
                                   max="<?= $maxQty ?>" 
                                   step="1"
                                   >
                        </td>
                        <td class="checkbox-cell">
                            <input type="checkbox" 
                                   name="selected[<?php echo $art; ?>]" 
                                   class="row-checkbox" 
                                   value="1" 
                                   >
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>

    <!-- ****************************************************************************************** -->
<div>

               
                  <input hidden type="text" name="ozon_shop"        value="<?= $get_shop_name?>">
                  <input hidden type="text" name="date_query_ozon"  value="<?= $date_query_ozon ?>">
                  <input hidden type="text" name="dop_days_query"   value="<?= $dop_days_query ?>">
                  <input hidden type="text" name="now_date_razbora" value="<?= $now_date_razbora?>">

        <div id="down_input" class="LockOff">
               <label class="number_order_label" for="number_order">Номер заказа</label>
               <input class="number_order"  required type="text" name="number_order" value="">
             <div class="action-bar">
                <div class="btn-group">
                      <!-- <input type="submit"  class="btn btn-primary" value="✅ СОБРАТЬ ВЫБРАННЫЕ ТОВАРЫ!" onclick="alerting();">  -->
                      <input type="submit" class="btn btn-primary" value="✅ СОБРАТЬ ВЫБРАННЫЕ ТОВАРЫ!" onclick="return alerting();">
                              <!-- <button type="submit" class="btn btn-primary" name="apply" value="1" >✅ СОБРАТЬ ВЫБРАННЫЕ ТОВАРЫ</button> -->
                </div>

                <div class="select-all-wrap">
                              <label for="selectAllCheckbox">Выбрать все</label>
                              <input type="checkbox" id="selectAllCheckbox" title="Отметить все товары">
                </div>
         </div>


                     <!-- <input type="submit" value="СОБРАТЬ  выбранную дату!" onclick="alerting();">  -->
               
         </div>  
         <div  id="OnLock_textLockPane" class="LockOn btn btn-primary">
                     Обрабатываем запрос.........
         </div>

</div>
<!-- ****************************************************************************************** -->


    </form>
</div>



 <script src="js/part_razbor.js"></script>
 <script type="text/javascript" src="js/js_functions.js"></script>
</body>
</html>
