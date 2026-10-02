<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Частичный разбор товаров</title>
    <link rel="stylesheet" href="css/razbor_part.css">
</head>
<body>
<div class="container">
    <h1>Частичный разбор товаров</h1>

    <div class="table-wrapper">
        <form method="POST" id="productsForm" action="<?= htmlspecialchars($viewData['formAction']) ?>">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>№ п/п</th><th>Артикул</th><th>Наименование</th>
                        <th>Кол-во (факт)</th><th>Новое количество<br><span style="font-size:0.75rem;">(≤ факт.)</span></th>
                        <th>Выбрать</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                <?php $idx = 0; foreach ($viewData['items'] as $article => $product): ?>
                    <?php $idx++; ?>
                    <tr data-index="<?= $idx ?>" data-max-qty="<?= (int)$product['quantity'] ?>">
                        <td class="index-cell"><?= $idx ?></td>
                        <td><span class="article-cell"><?= htmlspecialchars($article) ?></span></td>
                        <td class="name-cell"><?= htmlspecialchars($product['name']) ?></td>
                        <td class="qty-stock-cell"><?= (int)$product['quantity'] ?></td>
                        
                        <td class="new-qty-cell">
                            <input type="number" name="new_qty[<?= htmlspecialchars($article) ?>]"
                                   class="new-qty-input" value="<?= (int)$product['quantity'] ?>" min="0"
                                   max="<?= (int)$product['quantity'] ?>" step="1">
                        </td>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="selected[<?= htmlspecialchars($article) ?>]" class="row-checkbox" value="1">
                        </td>

                        <td class="qty-stock-cell"><?= (int)$product['price'] ?></td>

                    </tr>
                <?php endforeach; ?>

<tr>

    <td></td>
    <td></td>
    <td></td>
    <td><?php echo "<b>".$arr_sum_items['quantity']. "</b>"; ?></td>
    <td></td>
    <td></td>
    <td><?php echo "<b>".number_format($arr_sum_items['price'],0, ',', ' '). "</b>"; ?></td>

</tr>




                </tbody>
            </table>

            <!-- hidden fields -->
            <input type="hidden" name="ozon_shop"        value="<?= htmlspecialchars($viewData['shopName']) ?>">
            <input type="hidden" name="date_query_ozon"  value="<?= htmlspecialchars($viewData['dateQuery']) ?>">
            <input type="hidden" name="dop_days_query"   value="<?= (int)$viewData['dopDays'] ?>">
            <input type="hidden" name="now_date_razbora" value="<?= htmlspecialchars($viewData['nowDate']) ?>">

            <div id="down_input" class="LockOff">
                <label class="number_order_label" for="number_order">Номер заказа</label>
                <input class="number_order" required type="text" name="number_order" value="">
                <div class="action-bar">
                    <div class="btn-group">
                        <input type="submit" class="btn btn-primary" value="✅ СОБРАТЬ ВЫБРАННЫЕ ТОВАРЫ!" onclick="return alerting();">
                    </div>
                    <div class="select-all-wrap">
                        <label for="selectAllCheckbox">Выбрать все</label>
                        <input type="checkbox" id="selectAllCheckbox" title="Отметить все товары">
                    </div>
                </div>
            </div>
            <div id="OnLock_textLockPane" class="LockOn btn btn-primary">Обрабатываем запрос.........</div>
        </form>
    </div>
</div>
<script src="js/part_razbor.js"></script>
<script src="js/js_functions_part_razbor.js"></script>
</body>
</html>