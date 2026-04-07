<?php
/**
 * Красивая таблица с динамическими столбцами для расчётов по регионам
 * Последние 4 столбца формируются в цикле и могут содержать любое количество строк
 */


?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Аналитическая таблица по регионам</title>
    <!-- Google Fonts + Font Awesome для иконок -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">

</head>
<body>
<div class="container">
    <!-- Карточка с общей информацией о товаре (фиксированные данные) -->
    <div class="product-card">
        <div class="product-info">
            <div class="product-info-item"><i class="fas fa-tag"></i> Артикул: <strong><?= htmlspecialchars($article) ?></strong></div>
            <div class="product-info-item"><i class="fas fa-ruble-sign"></i> Цена товара: <strong><?= number_format($marketing_seller_price, 0, '.', ' ') ?> ₽</strong></div>
            <div class="product-info-item"><i class="fas fa-ruble-sign"></i>
            <a href = "https://seller.ozon.ru/app/prices/manager/<?php echo $product_id; ?>/prices" target="_blank"> 
                Себестоимость : <strong><?= number_format($net_price, 0, '.', ' ') ?> ₽
             </a>
    </strong></div>
            <div class="product-info-item"><i class="fas fa-ruble-sign"></i> Объем : <strong><?= number_format($volume, 3, '.', ' ') ?> л</strong></div>

            <div class="product-info-item"><i class="fas fa-percent"></i> Комиссия Ozon: <strong><?= number_format($commissionFBS, 2, '.', ' ') ?> ₽</strong></div>
            <div class="product-info-item">
                <i class="fas fa-box"></i> Эквайринг : <strong><?= number_format($acquiring, 0, '.', ' ') ?> ₽ </strong>
                <i class="fas fa-box"></i> Доставка до места выдачи: <strong><?= number_format($fbs_deliv_to_customer_amount, 0, '.', ' ') ?> ₽ </strong>
                <i class="fas fa-box"></i> Обработка отправления: <strong><?= number_format($fbs_first_mile_max_amount, 0, '.', ' ') ?> ₽ </strong>
           </div>
        
        


        </div>
              <div class="product-info-item"><i class="fas fa-ruble-sign"></i> ИТОГО за вычетом всего : <strong><?= number_format($cost_krome_logistiki, 0, '.', ' ') ?> ₽</strong></div>
    </div>

    <!-- Таблица -->
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
            <tr>
                <th><i class="fas fa-map-marker-alt"></i> Регион </th>
                <th><i class="fas fa-ruble-sign"></i> Стоимость доставки</th>
                <th><i class="fas fa-hand-holding-usd"></i> На р/с </th>
                <th><i class="fas fa-chart-line"></i> Прибыль</th>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($data_by_our_volme as $print_city): ?>
                <?php
                // Доп. класс для окраски рентабельности
                $profitClass = '';
                if ($print_city['profit'] < 150) $profitClass = 'low';
                elseif ($print_city['profit'] < 250) $profitClass = 'medium';
                ?>
                <tr>
                    <td>
                        <i class="fas fa-location-dot" style="color:#3b82f6; margin-right: 8px;"></i>
                        <?= htmlspecialchars($print_city['claster_get']) ?>
                    </td>
                    <td><?= number_format($print_city['cost_norm'], 0, '.', ' ') ?> ₽</td>
                    <td><?= number_format($print_city['s_logistikoi'], 0, '.', ' ') ?> ₽</td>
                    <td class="profit-cell <?= $profitClass ?>"><?= number_format($print_city['profit'], 0, '.', ' ') ?> ₽</td>
         
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        <div class="table-footer">
            <i class="fas fa-chart-line"></i> Всего регионов: <?= count($data_by_our_volme) ?>
        </div>
    </div>
</div>
</body>
</html>