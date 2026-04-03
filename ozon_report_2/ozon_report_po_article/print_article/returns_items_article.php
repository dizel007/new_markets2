<?php


foreach ($one_sku_in_reestr as $key=>&$one_string_data) {
/// назодим ID клиента
$pos1 = strpos($one_string_data['post_number'], '-');
$client_number ='';
if ($pos1 > 0) {
$client_number = mb_substr($one_string_data['post_number'], 0, $pos1);
} else {
    $client_number = $one_string_data['post_number'];
}


    if (isset($one_string_data['type']['RETURN'])) {
        
        $one_string_data['client_number'] = $client_number;
        $_jjjjjjjj_one_sku_in_reestr[$client_number][$one_string_data['post_number_gruzomesto']] = $one_string_data;
        $name_tovar = $one_string_data['name'];
        $sku = $one_string_data['sku'];
    }

}


// foreach ($_jjjjjjjj_one_sku_in_reestr as &$one_string_data) {
//     $_jjjjjjjj_one_sku_in_reestr[$client_number]['count_returns'.$client_number] = count($one_string_data);

// }
// echo "<pre>";
// print_r($_jjjjjjjj_one_sku_in_reestr['0116802930']);

uasort($_jjjjjjjj_one_sku_in_reestr, function($a, $b) {
    return count($b)  - count($a);
});

// print_r($_jjjjjjjj_one_sku_in_reestr);



unset($one_sku_in_reestr);
$one_sku_in_reestr = $_jjjjjjjj_one_sku_in_reestr;
///////////// выше удлаить ///////////////////



echo "<link rel=\"stylesheet\" href=\"css/return_ozon_reports.css\">";

// Начинаем отрисовывать таблицу 

echo "<table class=\"real_money fl-table\">";

// ШАПКА ТАблицы
echo <<<HTML
<tr>
<th>пп</th>
<th>Номер покупателя</th>
<th>Название товарв</th>
<th>Кол-во</th>
<th>Номер заказа </th>
<th>Тип<br>отгрузки</th>
<th>Дата заказа</th>
<!-- <th>Операции</th> -->
<th class = "td_logistika" >Логистика</th>
<th class = "td_logistika">Услуги<br>агентов</th>
<th class = "td_logistika">Сервисы</th>
<th>Кластер достаки</th>
</tr>

HTML;
$pp = 0; // номер строки в тублице
$all_summa_zatrat = 0;
$all_count = 0;
// echo "<pre>";
foreach ($one_sku_in_reestr as $key => $one_string_data) {

 echo "<tr>";
        $pp++;   
 $shirina_colonki  = count($one_string_data);
 $all_count +=  $shirina_colonki;
    echo "<td rowspan=\"$shirina_colonki\">" . $pp . "</td>";
    echo "<td rowspan=\"$shirina_colonki\">" . $key . "</td>";
    echo "<td rowspan=\"$shirina_colonki\">" . $name_tovar."<br>".  $sku. "</td>";
    
    echo "<td rowspan=\"$shirina_colonki\">" . $shirina_colonki . "</td>";
$update_claster = 0;
    foreach ($one_string_data as $post_number=>$data_xxx) {

    $link_for_post_delivery_finans_info = 'https://seller.ozon.ru/app/finances/accruals?search='.$post_number.'&tab=ACCRUALS_DETAILS';
    $link_foe_fbo_posting = 'https://seller.ozon.ru/app/postings/'.mb_strtolower($data_xxx['delivery_schema']).'/'. $post_number;

       echo "<td><a href=\"$link_for_post_delivery_finans_info\" target=\"_blank\">" . $post_number . "</a></td>";  
       echo "<td><a href=\"$link_foe_fbo_posting\" target=\"_blank\">" . $data_xxx['delivery_schema'] . "</a></td>";  
    
       echo "<td>" . $data_xxx['order_date'] . "</td>";
       //    $all_operations = implode($data_xxx['type']);
    //    echo "<td>" . $all_operations . "</td>";
  /// СТОБЛЕЦ ЛОГИСТИКА      
        $itogo_logidtik = 0;
        if (isset($data_xxx['Услуги доставки'])) {
             echo "<td>" ;
        foreach ($data_xxx['Услуги доставки'] as $direct_logistik =>$summ_logidtik) {
            // echo $direct_logistik." :".$summ_logidtik."<hr>";
            $itogo_logidtik +=$summ_logidtik;
        }
           echo "ИТОГО логистика :".$itogo_logidtik;
        echo "</td>";
    } else {
        echo "<td>"."-"."</td>";
    }
        

/// СТОБЛЕЦ УСЛУГИ АГЕНТОВ
        $itogo_uslugi_agentov = 0;
        if (isset($data_xxx['Услуги агентов'])) {
             echo "<td>" ;
        foreach ($data_xxx['Услуги агентов'] as $usluga =>$summ_usluga) {
            
// Обрезать до 20 символов
        $usluga = mb_substr($usluga, 0, 16);
            // echo $usluga." :".$summ_usluga."<hr>";
            $itogo_uslugi_agentov +=$summ_usluga;
        }
           echo "ИТОГО услуги агентов :".$itogo_uslugi_agentov;
        echo "</td>";
    } else {
        echo "<td>"."-"."</td>";
    }

$summa_zatrat = $itogo_uslugi_agentov + $itogo_logidtik;
$all_summa_zatrat += $summa_zatrat ;
// сумма затрат
    
       echo "<td>" . $summa_zatrat . "</td>";  

       if ($data_xxx['delivery_schema'] == 'FBO' ) {
        $ozon_dop_url = 'v2/posting/fbo/get';
        } else {
        $ozon_dop_url = 'v3/posting/fbs/get';
        }
if ($update_claster == 0) {
    $cluster_dostavki = get_fbs_or_fbo_adress('78944e3d-8722-4bfe-9d71-1b6970af47dd', '45537', $post_number, $ozon_dop_url );
    usleep(50);
$update_claster  ++;
}

       echo "<td>" . $cluster_dostavki . "</td>"; 

       echo "</tr>";
    
    
    
    }


echo "</tr>";

}

echo "<tr>";

echo "<td>" . "". "</td>";  
echo "<td>" . "". "</td>";  
echo "<td>" . "". "</td>";  
echo "<td>" . "$all_count". "</td>";  
echo "<td>" . "". "</td>";  
echo "<td>" . "". "</td>";  
echo "<td>" . "". "</td>"; 
echo "<td>" . "". "</td>"; 
echo "<td>" . "". "</td>"; 
// echo "<td>" . "". "</td>"; 
echo "<td>" . "$all_summa_zatrat". "</td>"; 


echo "</tr>";


echo "</table>";


function get_fbs_or_fbo_adress($token, $client_id, $number_post,$ozon_dop_url ) {
 $send_data= '{
"posting_number": "'.$number_post.'",
"with": {
"analytics_data": false,
"barcodes": false,
"financial_data": true,
"legal_info": false,
"product_exemplars": false,
"related_postings": false,
"translit": false
}
}';

$t_cluster = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url );
$cluster = $t_cluster['result']['financial_data']['cluster_to'];
return($cluster);

}

