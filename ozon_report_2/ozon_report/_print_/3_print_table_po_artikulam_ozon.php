<?php 

// echo "<pre>";
// print_r($arr_real_ozon_data);

// die();

// 
$link_sort_accruals_for_sale           = $queryString."&type_sort=".base64_encode('sort_accruals_for_sale_ot_max_k_min')."#ancor_table";
$link_sort_accruals_for_sale_one_item  = $queryString."&type_sort=".base64_encode('sort_accruals_for_sale_one_item_ot_max_k_min')."#ancor_table";
$link_sort_accruals_for_sale_procent   = $queryString."&type_sort=".base64_encode('sort_accruals_for_sale_procent_ot_max_k_min')."#ancor_table";

// комиссия озона
$link_sort_sale_commission             = $queryString."&type_sort=".base64_encode('sort_sale_commission_ot_max_k_min')."#ancor_table";
$link_sort_sale_commission_one_procent = $queryString."&type_sort=".base64_encode('sort_sale_commission_one_procent_ot_max_k_min')."#ancor_table";
$link_sort_sale_commission_one_item    = $queryString."&type_sort=".base64_encode('sort_sale_commission_one_item_ot_max_k_min')."#ancor_table";


/// ссылки логистики
$link_sort_logistika                 = $queryString."&type_sort=".base64_encode('sort_logistika_ot_max_k_min')."#ancor_table";
$link_sort_logistika_one_procent     = $queryString."&type_sort=".base64_encode('sort_logistika_one_procent_ot_max_k_min')."#ancor_table";
$link_sort_logistika_one_item        = $queryString."&type_sort=".base64_encode('sort_logistika_one_item_ot_max_k_min')."#ancor_table";

// цена amount 
$link_sort_amount                    = $queryString."&type_sort=".base64_encode('sort_amount_ot_max_k_min')."#ancor_table";
$link_sort_amount_one_item           = $queryString."&type_sort=".base64_encode('sort_amount_one_item_ot_max_k_min')."#ancor_table";

//// ссылки сервисы 
$link_sort_services                  = $queryString."&type_sort=".base64_encode('sort_services_ot_max_k_min')."#ancor_table";
$link_sort_services_one_procent      = $queryString."&type_sort=".base64_encode('sort_services_one_procent_ot_max_k_min')."#ancor_table";
$link_sort_services_one_item         = $queryString."&type_sort=".base64_encode('sort_services_one_item_ot_max_k_min')."#ancor_table";

/// Эквайринг
$link_sort_ecvairing                  = $queryString."&type_sort=".base64_encode('sort_ecvairing_ot_max_k_min')."#ancor_table";
$link_sort_ecvairing_one_procent      = $queryString."&type_sort=".base64_encode('sort_ecvairing_one_procent_ot_max_k_min')."#ancor_table";
$link_sort_ecvairing_one_item         = $queryString."&type_sort=".base64_encode('sort_ecvairing_one_item_ot_max_k_min')."#ancor_table";

/// Цена за вычетом всего где есть арктикул
$link_sort_bez_vsego_gde_est_artikul = $queryString."&type_sort=".base64_encode('sort_bez_vsego_gde_est_artikul_ot_max_k_min')."#ancor_table";
$link_sort_bez_vsego_gde_est_artikul_one_item = $queryString."&type_sort=".base64_encode('sort_bez_vsego_gde_est_artikul_one_item_ot_max_k_min')."#ancor_table";

///
$link_sort_bez_vsego                 = $queryString."&type_sort=".base64_encode('sort_bez_vsego_ot_max_k_min')."#ancor_table";
$link_sort_pribil                    = $queryString."&type_sort=".base64_encode('sort_pribil_ot_max_k_min')."#ancor_table";

if (strpos($type_sort, '_ot_max_k_min') !== false){
  $temp_name_perem = "link_".str_replace('_ot_max_k_min', '' , $type_sort);
  $temp_string = str_replace('_ot_max_k_min', '_ot_min_k_max', $type_sort);

//    echo "type_sort=".$type_sort."<br>";
//    echo "temp_name_perem=".$temp_name_perem."<br>";
//    echo "temp_string=".$temp_string."<br>";

  $$temp_name_perem = $queryString."&type_sort=".base64_encode($temp_string)."#ancor_table";
}

  

// ШАПКА ТАблицы
echo <<<HTML
<div class="h100proc_artikul">
<h3 class = "shapka_tabla">Юнит-экономика с возможностью сортировки по всем параметрам</h3>

<!-- Начинаем отрисовывать таблицу  -->
<table id="ancor_table" class="real_money fl-table">
<thead>
<tr>
    <th class ="name_row">Наименование</th>
    <th>SKU<br>Артикул</th>
    
    <th>К-во<br>Заказ<br>(шт)</th>
    <th>К-во<br>Возвр<br>(шт)</th>
    <th>К-во<br>продн<br>(шт)</th>
<!-- Стоимость в ЛК  -->
   <th>Стоимость<br>товара<br>в ЛК (руб)</th>
<!-- Комиссия озон   -->
    <th>Комиссия<br>озон<br>(руб)</th>
<!-- Логистика  -->
    <th>Стоимость<br>логистики<br>(руб)</th>


<!-- Сумма продаж без комиссии и логистики  -->
 <th>Сумма<br>продаж без<br>комис и логис<br>(руб)</th>


<!-- Сервисы -->
    <th>Стоимость<br>сервисов<br>(руб)</th>


<!-- Эквайринг -->
    <th>Эквайринг<br>(руб)</th>

<!-- Цена за вычетом всего где есть арктикул -->
    <th>Цена за<br>вычетом<br>всего<br>(руб)</th>

<!-- <th>% от общей<br>суммы<br>продаж<br>(руб)</th> -->
    <th>доп.услуги<br>(руб)</th>
    <th>К начисле<br>нию<br>(руб)</th>
    <th>Иностр<br>продажи(руб)</th>
    <th>Выплаты<br>(руб)</th>
    <th>Себестсть<br>(руб)</th>
    <th>Прибыль<br>(руб)</th>
    
</tr>
</thead>


<tr>
    <td class="numbers_th">
<!--  окно для ввода артикула -->
        <div class="article-filter-container">
            <input style="width: 250px; text-align: center;" type="text" placeholder="Введите артикул или SKU..." id="article-filter">
        </div>
      
    </td>
    <td class="numbers_th"></td>
    <td class="numbers_th">(1)<br>\xE2\x80\x8B</td>
    <td class="numbers_th">(2)<br>\xE2\x80\x8B</td>
    <td class="numbers_th">(3)=1-2<br>\xE2\x80\x8B</td>
<!-- Цена в ЛК  -->
    <td class="numbers_th">(4)<br>        
        <a class="sort_setup" href="?$link_sort_accruals_for_sale">Σ</a>
        <a class="sort_setup" href="?$link_sort_accruals_for_sale_one_item">шт</a>  
    </td>
<!-- Комиссия озон   -->
    <td class="numbers_th">(5)<br> 
        <a class="sort_setup" href="?$link_sort_sale_commission">Σ</a>
        <a class="sort_setup" href="?$link_sort_sale_commission_one_procent">%</a>
        <a class="sort_setup" href="?$link_sort_sale_commission_one_item">шт</a>  
    </td>


<!-- Логистика озон   -->
    <td class="numbers_th">(6)<br>
        <a class="sort_setup" href="?$link_sort_logistika">Σ</a>
        <a class="sort_setup" href="?$link_sort_logistika_one_procent">%</a>
        <a class="sort_setup" href="?$link_sort_logistika_one_item">шт</a>  
    </td>
<!-- Сумма продаж без комиссии и логистики  -->
    <td class="numbers_th">(7)=4+5+6<br>
        <a class="sort_setup" href="?$link_sort_amount">Σ</a>
        <a class="sort_setup" href="?$link_sort_amount_one_item">шт</a>  
    </td>

<!--Стоимость сервисов  -->
  <td class="numbers_th">(8)<br>
        <a class="sort_setup" href="?$link_sort_services">Σ</a>
        <a class="sort_setup" href="?$link_sort_services_one_procent">%</a>
        <a class="sort_setup" href="?$link_sort_services_one_item">шт</a>  
  </td>
<!-- Эквайринг -->
  <td class="numbers_th">(9)<br>
        <a class="sort_setup" href="?$link_sort_ecvairing">Σ</a>
        <a class="sort_setup" href="?$link_sort_ecvairing_one_procent">%</a>
        <a class="sort_setup" href="?$link_sort_ecvairing_one_item">шт</a>  
  </td>
<!-- Цена за вычетом всего где есть арктикул -->
    <td class="numbers_th">(10)=7+8+9<br>
        <a class="sort_setup" href="?$link_sort_bez_vsego_gde_est_artikul">Σ</a>
        <a class="sort_setup" href="?$link_sort_bez_vsego_gde_est_artikul_one_item">шт</a>  
    </td>
    <!-- <td>(11)</td> -->

    <td class="numbers_th">(11)<br>\xE2\x80\x8B</td>
    
    <td class="numbers_th">(12)=10+11<br>\xE2\x80\x8B</td>

    <td class="numbers_th">(12a)<br>\xE2\x80\x8B</td>
    <td class="numbers_th">(12б)<br>\xE2\x80\x8B</td>



    <!-- <td class="numbers_th">(14)<br>\xE2\x80\x8B</td> -->
    <td class="numbers_th">(13)<br>\xE2\x80\x8B</td>
<!-- Прибыль -->    
    <td class="numbers_th">(14)=13*3<br>
        <a class="sort_setup" href="?$link_sort_pribil">Σ</a>
    </td>
    
</tr>

HTML;

 
echo "<tbody id=\"filterable-table-body\">";


foreach ($arr_real_ozon_data as $sku_ozon=>$item_for_print) {

/**************************************************************************************/
// костыль для уцененных товаров, у которых нет нашего артикула 
/**************************************************************************************/
if (!isset($item_for_print['mp_article'])) {
   $item_for_print['mp_article'] = '';
}

// echo "<pre>";
// print_r($item_for_print);

// готовим информацию для передачи GET запроса в поартикульную разбор
     $original_data = "ozon_shop=".$shop_name.
     "&sku=". $item_for_print['sku'].
     "&file_name_ozon_small=$file_name_ozon_small".
     "&sebest=".$item_for_print['min_price'].
     "&acquiring=".$item_for_print['summa']['amount_ecvairing'].
     "&acquiring_count=".$item_for_print['count']['direct'].
     "&dop_uslugi=".$item_for_print['summa']['dop_uslugi'].
     "&article=".$item_for_print['mp_article'];

     $base64_encoded = base64_encode($original_data);
     $url_encoded = urlencode($base64_encoded);

//      echo "<pre>";
//      print_r($params);

// die();

// echo "<tr>";
echo "<tr data-article=\"" . htmlspecialchars($item_for_print['sku']) . "\" data-mp-article=\"" . htmlspecialchars($item_for_print['mp_article']) . "\">";
// $temp_per_test = rand(1000,100000);
// Название товара
// $Name_for_instruction = 'Название товара '.$temp_per_test;
//    echo "<td>". " <a class=\"tovar_name\" href =\"".$item_for_print['link_for_site_ozon']."\" target=\"_blank\">". $Name_for_instruction."</a>". "</td>";
   echo "<td>". " <a class=\"tovar_name\" href =\"".$item_for_print['link_for_site_ozon']."\" target=\"_blank\">". $item_for_print['name']."</a>". "</td>";

// Артикул и СКУ 

// $sku_for_instruction = 'SKU-'.$temp_per_test;
    // echo "<td>". $sku_for_instruction."<hr>".

   echo "<td>". // ссылка по поартикульную таблицу расходов

 "<a href=\"../ozon_report_po_article/index_ozon_razbor_article.php?data=".$url_encoded."\" target=\"_blank\">"
   .$item_for_print['sku']
   ."</a> <hr>".

//  $Article_for_instruction = 'артикул-'.$temp_per_test;
//    "<a href=\"../ozon_data_one_item/?clt=$secret_client_id&art=".$Article_for_instruction."\" target=\"_blank\">".$Article_for_instruction."</a> </td>";
 "<a href=\"../ozon_data_one_item/index_one_item.php?ozon_shop=$shop_name&art=".$item_for_print['mp_article']."\" target=\"_blank\">"
    .$item_for_print['mp_article'].
   "</a> </td>";



// Количество заказаыын товаров
print_one_string_in_table($item_for_print['count'],  'direct'); 
// Количество возвратов товаров
print_one_string_in_table($item_for_print['count'],  'return');
// Количетво выкупленных товаров
print_one_string_in_table($item_for_print['count'],  'summa'); 

/**************************************************************************************/
// Цена для покупателя (стоимость товара в личном кабинете)
/**************************************************************************************/

    $color_class = 'green_color';
  echo "<td>
        <p class=\"big_font $color_class\">". number_format($item_for_print['summa']['accruals_for_sale'],0 ,',',' ') ."</p>
        <p class = \"small_font\">" .  number_format($item_for_print['proc_item_ot_vsey_summi'],2 ,',',' ')." %"."</p>
        <p class = \"small_font $color_class\">" .  number_format($item_for_print['one_item']['accruals_for_sale'],0 ,',','') ." руб". "</p>
   </td>";



//  print_two_strings_in_table_two_parametrs($item_for_print['summa']['accruals_for_sale'],
//                                           $item_for_print['one_item']['accruals_for_sale'], 
//                                           $color_class = '');
/**************************************************************************************/
/// *******************   Комиссия озона   **************************
/**************************************************************************************/
print_three_strings_for_table_red($item_for_print['summa']['sale_commission'],
                                $item_for_print['one_procent']['sale_commission']."%", 
                                $item_for_print['one_item']['sale_commission']);
 /**************************************************************************************/
/// *******************   логистка  **************************
/**************************************************************************************/
print_three_strings_for_table_red($item_for_print['summa']['logistika'],
                                  $item_for_print['one_procent']['logistika']."%", 
                                  $item_for_print['one_item']['logistika']);

// ************************** Цена продажи *******************************************************************
print_two_strings_in_table_two_parametrs($item_for_print['summa']['amount'],
                                         $item_for_print['one_item']['amount'], 
                                         $color_class = '');

  /// *******************  Сервисы  **************************
print_three_strings_for_table_red($item_for_print['summa']['services'],
                              $item_for_print['one_procent']['services']."%", 
                              $item_for_print['one_item']['services']);

  /// ******************* Эквайринг  **************************

print_three_strings_for_table_red($item_for_print['summa']['amount_ecvairing'],
                               $item_for_print['one_procent']['ecvairing']."%", 
                               $item_for_print['one_item']['ecvairing']);

 // Цена за вычетом всего где есть артикул

print_two_strings_in_table_two_parametrs($item_for_print['summa']['bez_vsego_gde_est_artikul'],
                                            $item_for_print['one_item']['bez_vsego_gde_est_artikul'], 
                                            $color_class = '');
 

// **************** Процент распределения стоимости *****************
// print_one_string_in_table($item_for_print,  'proc_item_ot_vsey_summi');

 // Дополнительные услуги   
print_three_strings_for_table_red($item_for_print['summa']['dop_uslugi'],
                              $item_for_print['one_procent']['dop_uslugi']."%", 
                              $item_for_print['one_item']['dop_uslugi']);

////////////////////////////////////////////////////////////////////////////////////////
// Цена за вычетом всех расходов 
////////////////////////////////////////////////////////////////////////////////////////
print_two_strings_in_table_two_parametrs($item_for_print['summa']['bez_vsego'],
                                            $item_for_print['one_item']['bez_vsego'], 
                                            $color_class = '');

/////////////////////////////////////////////////////////////////////////////////////////////
// Иностранные продажи
/////////////////////////////////////////////////////////////////////////////////////////////

print_two_strings_in_table_two_parametrs($item_for_print['summa']['prodazh_v_eaes'],
                                         $item_for_print['count']['prodazh_v_eaes'], 
                                         $color_class = '');

/////////////////////////////////////////////////////////////////////////////////////////////
// ВЫплаты
/////////////////////////////////////////////////////////////////////////////////////////////

print_two_strings_in_table_two_parametrs($item_for_print['summa']['bez_vsego_s_ino_tovarami'],
                                         $item_for_print['one_item']['bez_vsego_s_ino_tovarami'], 
                                         $color_class = '');

/************************************************************************************/
// Себестоимость и разница от цена продажи после всех вычетов
/************************************************************************************/

    $item_for_print['diff_min_price'] >=0? $color_class = 'green_color':$color_class = 'red_color';
  echo "<td>
        <p class=\"big_font $color_class\">". number_format($item_for_print['summa']['sebestoimost'],0 ,',',' ') ."</p>
        <p class = \"small_font\"><a href =\"https://seller.ozon.ru/app/prices/manager/".$item_for_print['product_id']."/prices\" target=\"_blank\">" .  $item_for_print['min_price']." руб"."</a></p>
        <p class = \"small_font $color_class\">" .  number_format($item_for_print['diff_min_price'],0 ,',','') ." руб". "</p>
        </td>";
/************************************************************************************/
// Прибыль считается от себестоимости
/************************************************************************************/

 $item_for_print['summa']['pribil'] >=0? $color_class = 'green_color':$color_class = 'red_color';
 echo "<td>
        <p class=\"big_font $color_class\">". number_format($item_for_print['summa']['pribil'],0 ,',',' ') ."</p>
      </td>";



}

// СТРОКА ИТОГО ТАблицы
    echo "<tr>"; 
        echo "<td>"."ИТОГО"."</td>"; 
        echo "<td>".""."</td>";
        echo "<td>".""."</td>";
        echo "<td>".""."</td>";
        echo "<td>".""."</td>";
        print_summa_in_table($arr_summ, 'Цена для покупателя', $color_class = '');
        print_summa_in_table($arr_summ, 'Комиссия озона', 'red_color');
        print_summa_in_table($arr_summ, 'Логистика', 'red_color');
        print_summa_in_table($arr_summ, 'Сумма продаж', $color_class = '');
        print_summa_in_table($arr_summ, 'Сервисы', 'red_color');
        print_summa_in_table($arr_summ, 'Эквайринг', 'red_color');
        print_summa_in_table($arr_summ, 'Цена за вычетом с арктикулом', $color_class = '');
        // echo "<td>".round($arr_summ['Процент распределения стоимости'],2)."</td>";
        print_summa_in_table($arr_summ, 'Сумма распределения доп.услуг', 'red_color');
        print_summa_in_table($arr_summ, 'Сумма без всего', $color_class = '');
        print_summa_in_table($arr_summ, 'Сумма за ино_товары', $color_class = '');
        print_summa_in_table($arr_summ, 'Сумма без всего с иностр.товарами', $color_class = '');
        // echo "<td>".$arr_summ['Сумма за ино_товары']."</td>";
        print_summa_in_table($arr_summ, 'Сумма себестоимость', $color_class = '');
        $arr_summ['Сумма прибыль'] >=0? $color_class = 'green_color':$color_class = 'red_color'; 
        print_summa_in_table($arr_summ, 'Сумма прибыль', $color_class);
    echo "</tr>";

echo <<<HTML
</tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Находим элементы на странице
    const filterInput = document.getElementById('article-filter');
    const tableBody = document.getElementById('filterable-table-body');
    
    if (filterInput && tableBody) {
        // Функция фильтрации
        filterInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const sku = row.getAttribute('data-article') || '';
                const mpArticle = row.getAttribute('data-mp-article') || '';
                
                // Получаем название товара из первой ячейки строки
                const nameCell = row.querySelector('td:first-child');
                const productName = nameCell ? nameCell.textContent.toLowerCase() : '';
                
                // Объединяем все данные для поиска
                const searchData = (sku + ' ' + mpArticle + ' ' + productName).toLowerCase();
                
                if (searchTerm === '' || 
                    sku.toLowerCase().includes(searchTerm) || 
                    mpArticle.toLowerCase().includes(searchTerm) ||
                    productName.includes(searchTerm) ||
                    searchData.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
               
        // Добавляем класс к ячейке с названием для удобства
        const nameCells = tableBody.querySelectorAll('td:first-child');
        nameCells.forEach(cell => {
            cell.classList.add('searchable-name');
        });
    } else {
        console.error('Не найдены элементы для фильтрации');
    }
});
</script>
</div>
HTML;