<?php
// сылка на имнкоф аналитику
// https://business.tbank.ru/seller/analytics/summary?marketId=demo-data-market&from=Mon%20Feb%2016%202026&to=Sun%20Feb%2022%202026&timeGranule=week&marketplace=wb


$copy_arr_for_sum_table = $arr_for_sum_table;

// echo "<pre>";
// print_r($copy_arr_for_sum_table);
// die();
// Подбиваем суммы продажи и возвратов
if (($copy_arr_for_sum_table['Продажи']['-'] != 0 )) {
    $arr_for_tink_data['Продажи'] = $copy_arr_for_sum_table['Продажи']['-'];
unset ($copy_arr_for_sum_table['Продажи']);
} else { $arr_for_tink_data['Продажи'] = 0 ;}

if ($copy_arr_for_sum_table['Возвраты']['-'] != 0 ) {
    $arr_for_tink_data['Возвраты'] = $copy_arr_for_sum_table['Возвраты']['-'];
   unset ($copy_arr_for_sum_table['Возвраты']); 
} else {$arr_for_tink_data['Возвраты'] = 0 ;}

$arr_for_tink_data['Выкуплено'] = $arr_for_tink_data['Продажи'] + $arr_for_tink_data['Возвраты']; 

// один процент то суммы всех продаж
$one_sell_procent = round($arr_for_tink_data['Выкуплено']/100,2);


// СУмма транспортных расходов
if (isset($copy_arr_for_sum_table['Услуги доставки'])) {
foreach ($copy_arr_for_sum_table['Услуги доставки'] as $logistic_cost ){
 $arr_for_tink_data['Логистика'] = @$arr_for_tink_data['Логистика'] + $logistic_cost;  
    unset ($copy_arr_for_sum_table['Услуги доставки']); 
}
} else {
    $arr_for_tink_data['Логистика'] = 0;
    }
// Комиссия озон
if ($copy_arr_for_sum_table['Вознаграждение Ozon']['-']!= 0 ) {
    $arr_for_tink_data['Комиссия'] = $copy_arr_for_sum_table['Вознаграждение Ozon']['-'];
     unset ($copy_arr_for_sum_table['Вознаграждение Ozon']); 
    } else {
    $arr_for_tink_data['Комиссия'] = 0 ;
    }



// Продвижение и реклама
if (isset($copy_arr_for_sum_table['Продвижение и реклама'])) {
foreach ($copy_arr_for_sum_table['Продвижение и реклама'] as $reklama_cost ){
 $arr_for_tink_data['Реклама'] = @$arr_for_tink_data['Реклама'] + $reklama_cost;  
    unset ($copy_arr_for_sum_table['Продвижение и реклама']); 
}
} else {
    $arr_for_tink_data['Реклама'] = 0;
    }

// Услуги агентов
// убираем эквайринг в услуги агентов , остальное в логистику убирем
if (isset($copy_arr_for_sum_table['Услуги агентов']['Эквайринг'])) {
   $arr_for_tink_data['Услуги агентов'] = $copy_arr_for_sum_table['Услуги агентов']['Эквайринг'];   
   unset ($copy_arr_for_sum_table['Услуги агентов']['Эквайринг']) ;
}

if (isset($copy_arr_for_sum_table['Услуги агентов'])) {
foreach ($copy_arr_for_sum_table['Услуги агентов'] as $agent_cost ){
//  $arr_for_tink_data['Услуги агентов'] = @$arr_for_tink_data['Услуги агентов'] + $agent_cost;
  $arr_for_tink_data['Логистика'] = @$arr_for_tink_data['Логистика'] + $agent_cost;  
  

}
    unset ($copy_arr_for_sum_table['Услуги агентов']); 
} 
// else {
//     $arr_for_tink_data['Услуги агентов'] = 0;
//     }



// echo "<pre>";
// print_r($copy_arr_for_sum_table);
// Прочее 
$arr_for_tink_data['Прочее'] = 0;
foreach ($copy_arr_for_sum_table as $key=>$prochie_uslugi){

    foreach ($prochie_uslugi as $prochie_cost){
        $arr_for_tink_data['Прочее'] = @$arr_for_tink_data['Прочее'] + $prochie_cost;  
     }
unset ($copy_arr_for_sum_table[$key]);
} 

// Итого Основных затрат
$arr_for_tink_data['Основные расходы'] = $arr_for_tink_data['Логистика'] + $arr_for_tink_data['Комиссия'];
// Процент основных расходов от продаж 
$procent_osnov_prodaz = round($arr_for_tink_data['Основные расходы']/$one_sell_procent,1);

// Итого Другие расходы
$arr_for_tink_data['Другие расходы'] = $arr_for_tink_data['Прочее'] + $arr_for_tink_data['Реклама'] + $arr_for_tink_data['Услуги агентов'];
// Процент других услуг от стоимости продаж
$procent_drugih_prodaz = round($arr_for_tink_data['Другие расходы']/$one_sell_procent,1);


// Ищем количество проданноого товароы
foreach ($arr_orders as $orders_item) {
    if ($orders_item['operation_type'] == 'OperationAgentDeliveredToCustomer') {
         foreach($orders_item['items'] as $item) {
            $arr_orders_item[$item['sku']] = @$arr_orders_item[$item['sku']] + 1;
         }
     }
}
unset($item);


// Ищем количество возврата товароы
if (isset($arr_returns)) {
foreach ($arr_returns as $returns_item) {
    if ($returns_item['operation_type'] == 'ClientReturnAgentOperation') {
         foreach($returns_item['items'] as $item) {
            $arr_returns_item[$item['sku']] = @$arr_returns_item[$item['sku']] + 1;
         }
     }
}
unset($item);
}
// вычитаем возвраты из заказов 
if (isset($arr_returns_item)) {
    foreach ($arr_orders_item as $order_key =>&$orders_item) {
        foreach ($arr_returns_item as $return_key =>$return_item) {
            if ($order_key == $return_key) {
                $arr_orders_item[$order_key] =  $arr_orders_item[$order_key] - $arr_returns_item[$return_key] ;
            }
        }
    }
}


// находим себестоимость всех закзаов
foreach ($arr_article as $sku_itemcs => $itmess ) {
   foreach ($arr_article_products as $sebestoimost_data) {
      if($sku_itemcs == $sebestoimost_data['sku']) 
        {
            if (isset($itmess['count']['direct'])) {
           $arr_sebest[$sku_itemcs] = @$arr_sebest[$sku_itemcs] + $sebestoimost_data['sebestoimost']*$itmess['count']['direct'];
            
            } else {
            // $arr_sebest[$sku_itemcs] = @$arr_sebest[$sku_itemcs] + $sebestoimost_data['sebestoimost'] ;
            $arr_sebest[$sku_itemcs] = 0 ;

            }
        }
    }
}




$summa_sebes = - array_sum($arr_sebest);
// Процент себестоимости
$procent_sebestoimosti = round($summa_sebes/$one_sell_procent,1);

// Сумма проджа товаров в тсраны ЕАЭС
$summa_prodannogo_v_strani_EAES;

// Прибылб
$real_pribil = $arr_for_tink_data['Выкуплено'] + $arr_for_tink_data['Основные расходы']  +
                $arr_for_tink_data['Другие расходы'] + $summa_sebes + $summa_prodannogo_v_strani_EAES;
// Цвет профита
if ($real_pribil>=0) {
    $profit_amount = "profit-amount";
    $profit_percent = "profit-percent";
} else {
    $profit_amount = "No_profit-amount";
    $profit_percent = "No_profit-percent";
}

// Процент прибыли
$procent_pribil = round($real_pribil/$one_sell_procent,1);



// Данные для отображения (можно заменить на реальные)

// print_r($arr_returns);
// arr_returns
$sales = [
    
    'sold'          => $arr_for_tink_data['Продажи'],
    'bought'        => $arr_for_tink_data['Выкуплено'],
    'returned'      => $arr_for_tink_data['Возвраты'],
    'sold_percent'  => 100
];

$main_expenses = [
    'total'   => $arr_for_tink_data['Основные расходы'],
    'percent' => $procent_osnov_prodaz,
    'items'   => [
        'Комиссия'      => $arr_for_tink_data['Комиссия'],
        'Логистика'     => $arr_for_tink_data['Логистика']
    ]
];

$other_expenses = [
    'total'   => $arr_for_tink_data['Другие расходы'],
    'percent' => $procent_drugih_prodaz,
    'items'   => [
        'Услуги агентов'  => $arr_for_tink_data['Услуги агентов'],
        'Реклама' =>  $arr_for_tink_data['Реклама'],
        'Прочее' => $arr_for_tink_data['Прочее']
    ]
];


?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доходы и расходы</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .finance {
            max-width: 90%;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            color: #1a1a1a;
        }

        .row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }

        .col {
            flex: 1 1 280px;
            background: #f9fafc;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #edf2f7;
        }

        .col-header {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 8px 12px;
            color: #1e293b;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 12px;
        }

        .col-header .amount {
            font-size: 20px;
            font-weight: 700;
            margin-left: auto;
        }

        .col-header .percent {
            font-size: 16px;
            font-weight: 500;
            color: #64748b;
        }

        .sub-item {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 8px 0;
            font-size: 15px;
            color: #334155;
            border-bottom: 1px solid #e9eef2;
        }

        .sub-item:last-child {
            border-bottom: none;
        }

        .sub-item .amount {
            font-weight: 500;
            font-family: 'Courier New', monospace;
        }

        .amount.negative {
            color: #dc2626;
        }
        .amount.positive {
            color: #059669;
        }
        .percent.negative {
            color: #dc2626;
        }

        .percent.positive {
            color: #059669;
        }


        .profit-block {
            background: #f0f9ff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #b9e0f2;
        }

        .profit-header {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 12px 20px;
            margin-bottom: 16px;
        }

        .profit-title {
            font-size: 18px;
            font-weight: 600;
            color: #0c4a6e;
        }

        .profit-amount {
            font-size: 32px;
            font-weight: 700;
            color: #059669;
        }
        
        .No_profit-amount {
            font-size: 32px;
            font-weight: 700;
            color: #dc2626;
        }

        .profit-percent {
            font-size: 22px;
            font-weight: 600;
            color: #059669;
        }
        .No_profit-percent {
            font-size: 22px;
            font-weight: 600;
            color: #dc2626;
        }
        .profit-note {
            font-size: 15px;
            color: #1e293b;
            background: #e6f0f9;
            padding: 12px 16px;
            border-radius: 12px;
            border-left: 4px solid #059669;
        }

        @media (max-width: 768px) {
            .col-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .col-header .amount {
                margin-left: 0;
            }
            .profit-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <section class="finance">
        <h2>Доходы и расходы</h2>

        <div class="row">
            <!-- Колонка Продано -->
            <div class="col sales">
                <div class="col-header">
                    Выкуплено
                    
                    <span class="amount positive"><?= number_format($sales['bought'], 0, '.', ' ') ?> ₽</span>
                    <span class="percent positive"><?= $sales['sold_percent'] ?>%</span>
                </div>
                <div class="sub-item">Продано <span class="amount positive"><?= number_format($sales['sold'], 0, '.', ' ') ?> ₽</span></div>
                <div class="sub-item">Возвращено <span class="amount negative"><?= number_format($sales['returned'], 0, '.', ' ') ?> ₽</span></div>
            </div>

            <!-- Колонка Основные расходы -->
            <div class="col main-expenses">
                <div class="col-header">
                    Основные расходы
                    <span class="amount negative"><?= number_format($main_expenses['total'], 0, '.', ' ') ?> ₽</span>
                    <span class="percent negative"><?= $main_expenses['percent'] ?>%</span>
                </div>
                <!-- Себестоимость без суммы -->

                <?php 
                // Выводим остальные элементы, начиная со второго (Комиссия, Логистика)
                $rest = array_slice($main_expenses['items'], 1, null, true);
                foreach ($main_expenses['items'] as $name => $value): 
                    if ($value !== null): 
                ?>
                    <div class="sub-item">
                        <?= htmlspecialchars($name) ?>
                        <span class="amount negative"><?= number_format($value, 0, '.', ' ') ?> ₽</span>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>

            <!-- Колонка Другие расходы -->
            <div class="col other-expenses">
                <div class="col-header">
                    Другие расходы
                    <span class="amount negative"><?= number_format($other_expenses['total'], 0, '.', ' ') ?> ₽</span>
                    <span class="percent negative"><?= $other_expenses['percent'] ?>%</span>
                </div>
                <?php foreach ($other_expenses['items'] as $name => $value): ?>
                    <div class="sub-item">
                        <?= htmlspecialchars($name) ?>
                        <span class="amount negative"><?= number_format($value, 0, '.', ' ') ?> ₽</span>
                    </div>
                <?php endforeach; ?>
            </div>
                        <!-- Себестоимость -->
            <div class="col other-expenses">
                <div class="col-header">
                    Себестоимость товаров
                    <span class="amount negative"><?= number_format($summa_sebes, 0, '.', ' ') ?> ₽</span>
                    <span class="percent negative"><?= $procent_sebestoimosti ?>%</span>
                </div>
                    <div class="sub-item">
                        Себестоимость
                        <span class="amount negative"><?= number_format($summa_sebes, 0, '.', ' ') ?> ₽</span>
                    </div>

            </div>
                        <!-- Продажи в страны ЕАЭС -->
            <div class="col other-expenses">
                <div class="col-header">
                    Продажи в страны ЕАЭС
                    <span class="amount positive"><?= number_format($summa_prodannogo_v_strani_EAES, 0, '.', ' ') ?> ₽</span>
          
                </div>
                    <div class="sub-item">
                        Продажи в страны ЕАЭС
                        <span class="amount positive"><?= number_format($summa_prodannogo_v_strani_EAES, 0, '.', ' ') ?> ₽</span>
                    </div>

            </div>

        </div>

        <!-- Блок прибыли -->
        <div class="profit-block">
            <div class="profit-header">
                <span class="profit-title">Прибыль на сегодня</span>
                <span class="<?php echo $profit_amount; ?>"><?= number_format($real_pribil, 0, '.', ' ') ?> ₽</span>
                <span class="<?php echo $profit_percent; ?>"><?= $procent_pribil ?>%</span>
            </div>
            <!-- <div class="profit-note"><?= htmlspecialchars($real_pribil_with_ino_tovari) ?></div> -->
        </div>

    </section>
</body>
</html>