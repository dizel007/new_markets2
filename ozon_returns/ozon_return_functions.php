<?php

/*************************************************************************************
 **** Функция выводит на экран таблицу в возвратами, + сводную таблицу 
 *************************************************************************************/
function print_return_table($pdo, $arr_ozon_returns, $need_date, $name_db)
{
// Формируем упрощенный массив с нашим артикулом
    $arr_date = make_simple_return_array_with_our_article ($pdo, $arr_ozon_returns, $name_db);
    unset($items);

    // формируем обощенный массив артикул ключ - количество и цены значения
    $sum_quantity = 0;
    $sum_price = 0;
    foreach ($arr_date as $items) {
        if ($items['returned_to_seller_date_time'] == $need_date) {
            $arr_new_date_returns[$items['article']]['quantity'] = @$arr_new_date_returns[$items['article']]['quantity'] +  $items['quantity'];
            $sum_quantity += $items['quantity'];
            $arr_new_date_returns[$items['article']]['price'] = @$arr_new_date_returns[$items['article']]['price'] +  $items['price'];
            $sum_price += $items['price'];
        }
    }
    // если нет данных до заканчиваем 
    if (!isset($arr_new_date_returns))
    {
        echo "<h3>НЕТ возвратов озон $name_db на дату: $need_date</h3>";
                return 0;
            }


// рисуем таблицу заказов как в акте
    echo "<table class = \"prod_table\">";
    echo "<tr>
        <td>пп</td>
        <td>номер отправления</td>
        <td>Дата забора</td>
        <td>Название</td>
        <td>Артикул</td>
        <td>количество</td>
        <td>цена</td>
     </tr>";
    $i = 1;
    foreach ($arr_date as $item) {
        if ($item['returned_to_seller_date_time'] == $need_date) {

            echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $item['posting_number'] . "</td>";
            echo "<td>" . $item['returned_to_seller_date_time'] . "</td>";
            echo "<td>" . $item['product_name'] . "</td>";
            echo "<td>" . $item['article'] . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
            echo "<td>" . $item['price'] . "</td>";


            echo "</tr>";
            $i++;
        }
    }
// рисуем обощенную таблицу артикул - количество
  


    echo "<table class = \"prod_table_small\">";
    echo "<tr>
    <td>пп</td>
    <td>Артикул</td>
    <td>количество</td>
 </tr>";
    $i = 1;
    foreach ($arr_new_date_returns as $key => $item) {
        echo "<tr>";
            echo "<td>" . $i . "</td>";
            echo "<td>" . $key . "</td>";
            echo "<td>" . $item['quantity'] . "</td>";
        echo "</tr>";
        $i++;
    }

    echo "</table>";
    echo "Дата забора = " . $need_date . "<br>";
    echo "Количество товаров = " . $sum_quantity . "<br>";
    echo "Сумма товаров = " . $sum_price . "<br>";
    echo  "<br><br>";
    echo "</table>";
    if (isset($arr_new_date_returns)) {
return $arr_new_date_returns;
    } else {
        return 0;
    }
}

/*************************************************************************************
 **** Функция формирует кратий массив с возвратами, убираем все лишние поля, и цепляем наш артикул
 *************************************************************************************/
function make_simple_return_array_with_our_article ($pdo, $arr_ozon_ooo_returns, $name_db) {
    $i = 0;
    // Формируем массив и добавляем артикул из нашей базы данных
    foreach ($arr_ozon_ooo_returns['returns'] as $items) {
        $date_n = date('Y-m-d', strtotime($items['returned_to_seller_date_time'])); // дата получения заказа ПРОДАВЦОМ
        $arr_date[$i]['sku'] =  $items['sku'];
        $arr_date[$i]['quantity'] =  $items['quantity'];
        $arr_date[$i]['returned_to_seller_date_time'] =  $date_n;
        $arr_date[$i]['product_name'] =  $items['product_name'];
        $arr_date[$i]['posting_number'] =  $items['posting_number'];
        $arr_date[$i]['price'] =  $items['price'];
        $arr_date[$i]['return_reason_name'] =  $items['return_reason_name'];
    
        $sth = $pdo->prepare("SELECT main_article FROM `$name_db` WHERE `sku` = :sku");
        $sth->execute(array('sku' => $items['sku']));
        $article = $sth->fetch(PDO::FETCH_COLUMN);
    
        $arr_date[$i]['article'] =  mb_strtolower($article); // цепляем артикул товара
    
    
        $i++;
    }
return $arr_date ;
}