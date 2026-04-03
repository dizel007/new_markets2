<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сравнение FBO и FBS</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href = "css/style_sum_table.css">
</head>
<body>
    
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-chart-bar"></i>
                Сравнение FBO и FBS
            </h1>
            <p>Анализ прибыльности по разным моделям логистики</p>
        </div>
        
       
        
        <div class="products-table">
            <?php if (!empty($data['items'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 10%;">Артикул</th>
                            <th style="width: 4%;">Тип</th>
                            <th style="width: 12%;">Цена товара</th>
                            <th style="width: 8%;">Комиссия Ozon</th>
                            <th style="width: 20%;">Стоимость логистики</th>
                            <th style="width: 12%;">Итого затрат</th>
                            <th style="width: 12%;">Осталось после вычета</th>
                            <th style="width: 5%;">Рент-сть</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['items'] as $index => $item): 
                        // комиссмя ФБО
$commissionFBO = $item['price']['marketing_seller_price']*$item['commissions']['sales_percent_fbo']/100;
        // лоигстика ФБО
$ozon_commision_FBO_delivery_koef       = $item['commissions']['fbo_direct_flow_trans_min_amount']*$average_delivery_time['current_tariff']['tariff_value']/100;
$ozon_commision_FBO_delivery_cost_tovar = $item['price']['marketing_seller_price']*$average_delivery_time['current_tariff']['fee']/100;

$ozon_commision_FBO_delivery_direct     = $item['commissions']['fbo_direct_flow_trans_min_amount'] + 
                                       + $ozon_commision_FBO_delivery_koef + $ozon_commision_FBO_delivery_cost_tovar;

$logisticsFBO = $ozon_commision_FBO_delivery_direct + $item['commissions']['fbo_deliv_to_customer_amount'] + $item['acquiring'];
        
// Итого затрат по бо
$AllZatratiFBO = $commissionFBO + $logisticsFBO;
        // Комиссии FBS

$commissionFBS = $item['price']['marketing_seller_price']*$item['commissions']['sales_percent_fbs']/100;
$logisticsFBS = $item['commissions']['fbs_direct_flow_trans_min_amount'] +
                $item['commissions']['fbs_deliv_to_customer_amount'] +
                $item['commissions']['fbs_first_mile_max_amount'] +
                $item['acquiring'];


 // Итого затрат по бо
$AllZatratiFBS = $commissionFBS + $logisticsFBS;       
        // Прибыль
        $profitFBO = $item['price']['marketing_seller_price'] - $commissionFBO - $logisticsFBO;
        $profitFBS = $item['price']['marketing_seller_price'] - $commissionFBS - $logisticsFBS;
        $profitabilityFBO =0;
        $profitabilityFBS =0;
                        ?>
                        <!-- FBO строка -->
                        <tr class="fbo-row">
                            <td rowspan="2">
                                <div  class="offer-id"><?php echo htmlspecialchars($item['offer_id']); ?></div>
                            </td>
                            <td>
                                <span class="type-badge type-fbo">
                                    <i class="fas fa-warehouse"></i> FBO
                                </span>
                            </td>

                            <!-- стоимость товара ФБО -->
                            <td class="price-cell">
                                <div class="shop-price"><?php echo number_format($item['price']['marketing_seller_price'], 0, ',', ' '); ?> ₽</div>

                                <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 10px;">
                                    Себестоимость: <?php echo number_format($item['price']['net_price'], 0, ',', ' '); ?> ₽
                                </div>
                            </td>

                            <!-- комиссия озон ФБО -->
                            <td class="commission-cell commission-fbo">
                                <?php echo number_format($commissionFBO, 2, ',', ' '); ?> ₽
                                <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 5px;">
                                    <?php echo $item['commissions']['sales_percent_fbo']; ?>%
                                </div>
                            </td>
                            <td>
                                <div class="logistics-cell">
                                    <div class="logistics-item">
                                        <span class="logistics-label">Стоимость доставки ФБО:</span>
                                        <span class="logistics-value"><?php echo number_format($item['commissions']['fbo_direct_flow_trans_min_amount'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span style="color: #e46767;"class="logistics-label">Удорожание логистики: <?php echo number_format($average_delivery_time['current_tariff']['tariff_value'], 0, ',', ''); ?>%</span>
                                        <span  style="color: #e46767;" class="logistics-value"><?php echo number_format($ozon_commision_FBO_delivery_koef, 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div  class="logistics-item">
                                        <span style="color: #e46767;" class="logistics-label">Добавка ст-ти товара: <?php echo number_format($average_delivery_time['current_tariff']['fee'], 0, ',', ''); ?>%</span>
                                        <span style="color: #e46767;" class="logistics-value"><?php echo number_format($ozon_commision_FBO_delivery_cost_tovar, 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span class="logistics-label">Доставка клиенту:</span>
                                        <span class="logistics-value"><?php echo number_format($item['commissions']['fbo_deliv_to_customer_amount'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span class="logistics-label">Эквайринг:</span>
                                        <span class="logistics-value"><?php echo number_format($item['acquiring'], 2, ',', ' '); ?> ₽</span>
                                    </div>

                                </div>
                                <div style="margin-top: 2px; padding-top: 2px; border-top: 1px solid #eee; font-weight: bold;">
                                    Итого: <?php echo number_format($logisticsFBO, 2, ',', ' '); ?> ₽
                                </div>
                            </td>
   <!-- Итого затрат ФБО -->
                            <td class="price-cell">
                                <div class="current-price"><?php echo number_format($AllZatratiFBO, 0, ',', ' '); ?> ₽</div>
                            </td>

    <!-- Профит ФБО -->
                            <td class="profit-cell">
                                <div class="profit-amount" style="color: <?php echo $profitFBO >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                                    <?php echo number_format($profitFBO, 2, ',', ' '); ?> ₽
                                </div>
                                <div style="font-size: 0.85rem; color: #7f8c8d;">
                                    Осталось после коммиссий
                                </div>
                            </td>
                            <td>
                                <?php 
                                $profitabilityClass = '';
                                if ($profitabilityFBO > 20) {
                                    $profitabilityClass = 'profit-positive';
                                } elseif ($profitabilityFBO > 0) {
                                    $profitabilityClass = 'profit-neutral';
                                } else {
                                    $profitabilityClass = 'profit-negative';
                                }
                                ?>
                                <div class="profit-percent <?php echo $profitabilityClass; ?>">
                                    <?php echo number_format($profitabilityFBO, 1, ',', ' '); ?>%
                                </div>
                            </td>
                        </tr>
                        
                        <!-- FBS строка -->
                        <tr class="fbs-row">
                            <td>
                                <span class="type-badge type-fbs">
                                    <i class="fas fa-store"></i> FBS
                                </span>
                            </td>
                            <!-- цена товара ФБС -->
                            <td class="price-cell">
                                <div class="shop-price"><?php echo number_format($item['price']['marketing_seller_price'], 0, ',', ' '); ?> ₽</div>
                                <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 10px;">
                                    Себестоимость: <?php echo number_format($item['price']['net_price'], 0, ',', ' '); ?> ₽
                                </div>
                            </td>
                            <!-- комиссия озона ФБС -->

                            <td class="commission-cell commission-fbs">
                                <?php echo number_format($commissionFBS, 2, ',', ' '); ?> ₽
                                <div style="font-size: 0.85rem; color: #7f8c8d; margin-top: 5px;">
                                    <?php echo $item['commissions']['sales_percent_fbs']; ?>%
                                </div>
                            </td>
                            <td>
                                <div class="logistics-cell">
                                    <div class="logistics-item">
                                        <span class="logistics-label">Стоимость ФБC доставки:</span>
                                        <span class="logistics-value"><?php echo number_format($item['commissions']['fbs_direct_flow_trans_max_amount'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span class="logistics-label">Доставка до места выдачи:</span>
                                        <span class="logistics-value"><?php echo number_format($item['commissions']['fbs_deliv_to_customer_amount'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span class="logistics-label">Обработка отправления:</span>
                                        <span class="logistics-value"><?php echo number_format($item['commissions']['fbs_first_mile_max_amount'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                    <div class="logistics-item">
                                        <span class="logistics-label">Эквайринг:</span>
                                        <span class="logistics-value"><?php echo number_format($item['acquiring'], 2, ',', ' '); ?> ₽</span>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; font-weight: bold;">
                                    Итого: <?php echo number_format($logisticsFBS, 2, ',', ' '); ?> ₽
                                </div>
                            </td>

<!-- Итого затрат ФБС -->
                            <td class="price-cell">
                                <div class="current-price"><?php echo number_format($AllZatratiFBS, 0, ',', ' '); ?> ₽</div>
                            </td>


<!-- профит с товара ФБС -->
                            <td class="profit-cell">
                                <div class="profit-amount" style="color: <?php echo $profitFBS >= 0 ? '#27ae60' : '#e74c3c'; ?>;">
                                    <?php echo number_format($profitFBS, 2, ',', ' '); ?> ₽
                                </div>
                                <div style="font-size: 0.85rem; color: #7f8c8d;">
                                    Осталось после коммиссий
                                </div>
                            </td>
                            <td>
                                <?php 
                                $profitabilityClass = '';
                                if ($profitabilityFBS > 20) {
                                    $profitabilityClass = 'profit-positive';
                                } elseif ($profitabilityFBS > 0) {
                                    $profitabilityClass = 'profit-neutral';
                                } else {
                                    $profitabilityClass = 'profit-negative';
                                }
                                ?>
                                <div class="profit-percent <?php echo $profitabilityClass; ?>">
                                    <?php echo number_format($profitabilityFBS, 1, ',', ' '); ?>%
                                </div>
                                <?php if ($profitabilityFBS > $profitabilityFBO): ?>
                                    <div class="actions-badge">
                                        <i class="fas fa-arrow-up"></i> Выгоднее
                                    </div>
                                <?php elseif ($profitabilityFBS < $profitabilityFBO): ?>
                                    <div class="actions-badge" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                                        <i class="fas fa-arrow-down"></i> Менее выгодно
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <!-- Разделитель -->
                        <?php if ($index < count($data['items']) - 1): ?>
                        <tr>
                            <td colspan="8" style="background: linear-gradient(90deg, #f1c40f, #f39c12); height: 3px; padding: 0;"></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php endforeach; ?>
                    </tbody>
                </table>
               

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Нет данных для отображения</h3>
                    <p>Добавьте товары для анализа прибыльности FBO и FBS</p>
                </div>
            <?php endif; ?>
        </div>
    </div>


</body>
</html>