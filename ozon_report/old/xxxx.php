<?PHP
require_once '../../mp_sklad/functions/ozon_catalog.php';
require_once "libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров

// $ozon_catalog = get_catalog_ozon ();
$ozon_sebest = get_sebestiomost_ozon_with_sku_FBO ();

echo "<pre>";


print_r($ozon_sebest);

die();
// делаем один последовательный массив в операциями
// foreach ($prod_array as $items) {
//     foreach ($items as $item) {
//         $new_prod_array[] = $item;
//     }
// }

$new_prod_array = json_decode(file_get_contents('xxx.json'),true);



// file_put_contents('xxx.json', json_encode($new_prod_array, JSON_UNESCAPED_UNICODE));


foreach ($new_prod_array as $item) {
    
    if ($item['type'] == 'orders') { 
// Доставка и обработка возврата, отмены, невыкупа   
        $arr_orders[] = $item; 
    } elseif ($item['type'] == 'returns') {
// Доставка и обработка возврата, отмены, невыкупа
        $arr_returns[] = $item;
    } elseif ($item['type'] == 'other') { 
// эквайринг ;претензиям
        $arr_other[] = $item;
    } elseif ($item['type'] == 'services') { 
//продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
        $arr_services[] = $item;
    } elseif ($item['type'] == 'compensation') { 
//продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
                $arr_compensation[] = $item;
    } else {
// Если есть неучтенка то сюда
        $arr_index_job[] = $item; /// Проверить нужно будет на существование этого массива

    }
}

/************************************************************* */
// foreach ($arr_orders as $items) {
//     $our_item = $items['items'];
// // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
//     foreach ($our_item as $item) {
// // print_r($items);
// // die();
//         $arr_article[$item['sku']]['name'] = $item['name'];
//         $arr_article[$item['sku']]['sku'] = $item['sku'];
//             if ($items['posting']['delivery_schema'] == 'FBO') {
//                 // количество товаров в заказе 
//                 $arr_article[$item['sku']]['countFBO'] = @$arr_article[$item['sku']]['countFBO'] + 1;
//                 // Суммируем суммы операции
//                 $arr_article[$item['sku']]['amountFBO'] = @$arr_article[$item['sku']]['amountFBO'] + $items['amount']/count($our_item); 
//             } elseif ($items['posting']['delivery_schema'] == 'FBS') {
//                 // количество товаров в заказе 
//                 $arr_article[$item['sku']]['countFBS'] = @$arr_article[$item['sku']]['countFBS'] + 1;
//                 // Суммируем суммы операции
//                 $arr_article[$item['sku']]['amountFBS'] = @$arr_article[$item['sku']]['amountFBS'] + $items['amount']/count($our_item); 
//             } else {
//                 // количество товаров в заказе 
//                 $arr_article[$item['sku']]['countXXX'] = @$arr_article[$item['sku']]['countXXX'] + 1;
//                 // Суммируем суммы операции
//                 $arr_article[$item['sku']]['amountXXX'] = @$arr_article[$item['sku']]['amountXXX'] + $items['amount']/count($our_item);   
//             }
     
//     }
// }

/****************************************************************** */


$i=0;

function change_SKU_fbo_fbs($ozon_sebest, $sku){

    foreach ($ozon_sebest as $item_cat) {
        if ($sku == $item_cat['skuFBO']) {
            $sku  = $item_cat['skuFBO'];
            break;
           
    }
    return $sku;
    }
}
/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ************************************************************
 *************************************************************************************************************/
foreach ($arr_orders as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
    foreach ($our_item as $item) {
            $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);
        $arr_article[$item['sku']]['name'] = $item['name'];
        $arr_article[$item['sku']]['sku'] = $item['sku'];
     // количество товаров в заказе 
       $arr_article[$item['sku']]['count'] = @$arr_article[$item['sku']]['count'] + 1;
     // Суммируем суммы операции
       $arr_article[$item['sku']]['amount'] = @$arr_article[$item['sku']]['amount'] + $items['amount']/count($our_item); 
     // Суммируем Комиссию за продажу     
      $arr_article[$item['sku']]['sale_commission'] = @$arr_article[$item['sku']]['sale_commission'] + $items['sale_commission']/count($our_item);
//***************************** РАЗБИВАЕМ ТОВАРЫ ПО СХЕМЕ ПОСТАВКИ ************************ */
        if ($items['posting']['delivery_schema'] == 'FBO') {
            // количество товаров в заказе 
            $arr_article[$item['sku']]['countFBO'] = @$arr_article[$item['sku']]['countFBO'] + 1;
            // Суммируем суммы операции
            $arr_article[$item['sku']]['amountFBO'] = @$arr_article[$item['sku']]['amountFBO'] + $items['amount']/count($our_item); 
        } elseif ($items['posting']['delivery_schema'] == 'FBS') {
            // количество товаров в заказе 
            $arr_article[$item['sku']]['countFBS'] = @$arr_article[$item['sku']]['countFBS'] + 1;
            // Суммируем суммы операции
            $arr_article[$item['sku']]['amountFBS'] = @$arr_article[$item['sku']]['amountFBS'] + $items['amount']/count($our_item); 
        } else {
            // количество товаров в заказе 
            $arr_article[$item['sku']]['countXXX'] = @$arr_article[$item['sku']]['countXXX'] + 1;
            // Суммируем суммы операции
            $arr_article[$item['sku']]['amountXXX'] = @$arr_article[$item['sku']]['amountXXX'] + $items['amount']/count($our_item);   
        }
//*************************************************** */

    }



    foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
//логистика
                $arr_article[$item['sku']]['logistika'] = @$arr_article[$item['sku']]['logistika'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
// обработка отправления
                $arr_article[$item['sku']]['sborka'] = @$arr_article[$item['sku']]['sborka'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
//последняя миля.
                $arr_article[$item['sku']]['lastMile'] = @$arr_article[$item['sku']]['lastMile'] + $services['price']; // суммма логистики
            }
    }

}

/**************************************************************************************************************
 **************************************  ВОЗВРАТЫ
 *************************************************************************************************************/
foreach ($arr_returns as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {
            $arr_article[$item['sku']]['name'] = $item['name'];
            $arr_article[$item['sku']]['sku'] = $item['sku'];
    // количество товаров в заказе, которые вернули
            $arr_article[$item['sku']]['count_vozvrat'] = @$arr_article[$item['sku']]['count_vozvrat'] + 1;
  // Суммируем суммы операции, которые возвраты
  $arr_article[$item['sku']]['amount_vozrat'] = @$arr_article[$item['sku']]['amount_vozrat'] + $items['amount']/count($our_item);  
        }

    
 
}

/**************************************************************************************************************
 * Эквайринг 
 *************************************************************************************************************/


foreach ($arr_other as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)

// [MarketplaceRedistributionOfAcquiringOperation] => Оплата эквайринга
// [OperationClaim] => Начисления по претензиям

if ($items['operation_type'] == 'MarketplaceRedistributionOfAcquiringOperation') //Оплата эквайринга
    { 
        foreach ($our_item as $item) 
            {
                $arr_article[$item['sku']]['name'] = $item['name'];
                $arr_article[$item['sku']]['sku'] = $item['sku'];
           // количество товаров в заказе, Эквайринг
                $arr_article[$item['sku']]['count_ecvairing'] = @$arr_article[$item['sku']]['count_ecvairing'] + 1;
                $arr_article[$item['sku']]['amount_ecvairing'] = @$arr_article[$item['sku']]['amount_ecvairing'] + round($items['amount']/count($our_item),2);
            }
    }
// СУмма претензий (ОНа не привязана к артикулу) /Начисления по претензиям
if ($items['operation_type'] == 'OperationClaim') 
    {
        $Summa_pretensii = @$Summa_pretensii  + $items['amount']; // сумма начислений по претензиям
    }
    
}

/**************************************************************************************************************
 * Удержание за недовложение товара
 *************************************************************************************************************/
if (isset($arr_compensation)){
foreach ($arr_compensation as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {
            $arr_article[$item['sku']]['name'] = $item['name'];
            $arr_article[$item['sku']]['sku'] = $item['sku'];
    
        }
// количество товаров в заказе, которые вернули
    $arr_article[$item['sku']]['count_compensation'] = @$arr_article[$item['sku']]['count_compensation'] + count($our_item);
// Суммируем суммы операции, которые возвраты
    $arr_article[$item['sku']]['compensation'] = @$arr_article[$item['sku']]['compensation'] + $items['amount']; 
}
}
/**************************************************************************************************************
 ***********************  Сервисы ******************************************************
 *************************************************************************************************************/

foreach ($arr_services as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {
            $arr_article[$item['sku']]['name'] = $item['name'];
            $arr_article[$item['sku']]['sku'] = $item['sku'];
        if (($items['operation_type'] == 'OperationMarketplaceReturnStorageServiceAtThePickupPointFbs')  OR 
            ($items['operation_type'] == 'OperationMarketplaceReturnDisposalServiceFbs') )
            {
                // Начисление за хранение/утилизацию возвратов
                $arr_article[$item['sku']]['count_hranenie'] = @$arr_article[$item['sku']]['count_hranenie'] + 1;
                $arr_article[$item['sku']]['amount_hranenie'] = @$arr_article[$item['sku']]['amount_hranenie'] + $items['amount']/count($our_item);
            }
        }
// СУмма по сервисами
if ($items['operation_type'] == 'MarketplaceMarketingActionCostOperation') 
    { // Услуги продвижения товаров
        $Summa_uslugi_prodvizhenia_tovara = @$Summa_uslugi_prodvizhenia_tovara  + $items['amount']; 
    } 
elseif ($items['operation_type'] == 'MarketplaceSaleReviewsOperation')
    {  //Приобретение отзывов на платформе
        $Summa_buy_otzivi = @$Summa_buy_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceDefectRate')
    {  //Услуга по изменению условий отгрузки
        $Summa_izmen_uslovi_otgruzki = @$Summa_izmen_uslovi_otgruzki  + $items['amount']; 
    }
}








// (
//        [MarketplaceMarketingActionCostOperation] => Услуги продвижения товаров
//        [OperationMarketplaceReturnStorageServiceAtThePickupPointFbs] => Начисление за хранение/утилизацию возвратов
//        [MarketplaceSaleReviewsOperation] => Приобретение отзывов на платформе
//        [OperationMarketplaceReturnDisposalServiceFbs] => Начисление за хранение/утилизацию возвратов
//        [OperationMarketplaceDefectRate] => Услуга по изменению условий отгрузки
// )

// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";



echo "<table class=\"fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
    echo "<th style=\"width:10%\">Наименование</th>";
    echo "<th>Кол-во<br>продано<br>(шт)</th>";
    echo "<th>Кол-во<br>продано<br>FBO(шт)</th>";
    echo "<th>Кол-во<br>продано<br>FBS(шт)</th>";
    echo "<th>Сумма продаж<br>(руб)</th>";
    echo "<th>Хранение<br>утилизация<br>(руб)</th>";
    echo "<th>Удержание<br>за недовлож<br>(руб)</th>";
    echo "<th>Эквайринг<br>(руб)</th>";
    echo "<th>Возвраты<br>(шт)</th>";
    echo "<th>Возвраты<br>(руб)</th>";

    echo "<th>Комиссия Озон<br>(руб)</th>";

    echo "<th>Логистика<br>(руб)</th>";
    echo "<th>Сборка<br>(руб)</th>";
    echo "<th>Посл.миля<br>(руб)</th>";

echo "</tr>";


foreach ($arr_article as $key=>$item) {
    @$amount +=$item['amount']; // сумма продажи 
    
    @$count +=$item['count']; // сумма продажи 
    @$countFBO +=$item['countFBO']; // сумма продажи 
    @$countFBS +=$item['countFBS']; // сумма продажи 


    @$amount_hranenie +=$item['amount_hranenie']; // общая стоимость хранения 
    @$amount_ecvairing +=$item['amount_ecvairing']; // Общая стоимость эквайринга
    @$compensation += $item['compensation'] ; // Общая стоимость недовлажений
    @$amount_vozrat +=$item['amount_vozrat']; // Общая стоимость возвратов
    
    @$sale_commission +=$item['sale_commission']; // Общая стоимость 
    @$logistika +=$item['logistika']; // Общая стоимость 
    @$sborka +=$item['sborka']; // Общая стоимость 
    @$lastMile +=$item['lastMile']; // Общая стоимость 
   

    echo "<tr>";

        if (isset($item['name'])){echo "<td>".$item['name']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['count'])){echo "<td>".$item['count']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['countFBO'])){echo "<td>".$item['countFBO']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['countFBS'])){echo "<td>".$item['countFBS']."</td>";}else{echo "<td>"."</td>";}


        if (isset($item['amount'])){echo "<td>".$item['amount']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount_hranenie'])){echo "<td>".$item['amount_hranenie']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['compensation'])){echo "<td>".$item['compensation']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount_ecvairing'])){echo "<td>".$item['amount_ecvairing']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['count_vozvrat'])){echo "<td>".$item['count_vozvrat']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount_vozrat'])){echo "<td>".$item['amount_vozrat']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sale_commission'])){echo "<td>".$item['sale_commission']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['logistika'])){echo "<td>".$item['logistika']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sborka'])){echo "<td>".$item['sborka']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['lastMile'])){echo "<td>".$item['lastMile']."</td>";}else{echo "<td>"."</td>";}

    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>";
    echo "<td></td>"; // Наименование
    echo "<td>$count</td>"; // Количество
    echo "<td>$countFBO</td>"; // Количество
    echo "<td>$countFBS</td>"; // Количество

    echo "<td>$amount</td>"; // общая сумма
    if (isset($amount_hranenie)){echo "<td>".$amount_hranenie."</td>";}else{echo "<td>"."</td>";} // сумма хранения
    if (isset($compensation)){echo "<td>".$compensation."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    if (isset($amount_ecvairing)){echo "<td>".$amount_ecvairing."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    echo "<td></td>";
    if (isset($amount_vozrat)){echo "<td>".$amount_vozrat."</td>";}else{echo "<td>"."</td>";} // сумма возвратов

    if (isset($sale_commission)){echo "<td>".$sale_commission."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий

    if (isset($logistika)){echo "<td>".$logistika."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($sborka)){echo "<td>".$sborka."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($lastMile)){echo "<td>".$lastMile."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий

echo "</tr>";

echo "</table>";

//////////////////////////////////////////////////////////////////////////////////////////////////////
echo "<br>";
echo "ВЫПЛАТА С СЕВРИСНЫМИ СБОРАМ : $amount<br>";
echo "СТОИМОСТЬ ХРАНЕНИЯ          : $amount_hranenie<br>";
echo "СТОИМОСТЬ ЭКВАЙРИНГА        : $amount_ecvairing<br>";

echo "СТОИМОСТЬ ВОЗВРАТОВ         : $amount_vozrat<br>";

echo "КОММИССИЯ                   : $sale_commission<br>";
echo "Логистика                   : $logistika<br>";
echo "Сборка                      : $sborka<br>";
echo "Посл.миля                   : $lastMile<br>";

$summa_NACHILS = $amount - $logistika - $sborka - $lastMile;
echo "<br>";
echo "НАЧИСЛЕННО                  : $summa_NACHILS<br>";

echo "<br>";
echo "Услуги продвижения товаров : $Summa_uslugi_prodvizhenia_tovara<br>";
echo "Приобретение отзывов на платформе : $Summa_buy_otzivi<br>";

if (isset($Summa_izmen_uslovi_otgruzki)){echo "Услуга по изменению условий отгрузки : $Summa_izmen_uslovi_otgruzki<br>";}
if (isset($Summa_pretensii)){echo "сумма начислений по претензиям : $Summa_pretensii<br>";}


echo "Кол-во обработанных итэмс : $i<br>";


print_r($arr_article);