<?php
require_once "../connect_db.php";

require_once "get_access_token.php";
require_once "modules/functions_reklama.php";

$access_token_reklama = get_access_token_reklama_ozon();

// массив с данными по рекламным компаниям
$arr_info_company = get_company_id_ozon ($access_token_reklama , 'CAMPAIGN_STATE_RUNNING');
foreach ($arr_info_company as $item_company) {
    $arr_company_id[] = $item_company['id'];
    $arr_company_id_by_type[$item_company['advObjectType']][] = $item_company['id'];
}

// echo "<pre>";

// file_put_contents('aaa', json_encode($arr_company_id_by_type));

// $arr_company_id_by_type = json_decode(file_get_contents('aaa'),true);

// print_r($arr_company_id_by_type);
// die();


sleep(1);
 
//********* Формируем запрос по всем компаниям ;

$UUID_request = request_UUID_for_reklam_company ($access_token_reklama, $arr_company_id , '2024-09-01', '2024-09-30');


if (!isset($UUID_request)) {
    die('<br>ОЗОН не UUID .... <br> Попробуйте повторить запрос через 10 минут ....');
} else  {
    // echo "<br>";
echo "<br> UUID запроса = ";
print_r($UUID_request);
}

// $UUID_request ='9ae3ac61-5ad5-4f6f-b3f3-3a50637969d0';
sleep(7);
$limit_time=0;
do {
$check_status = check_status_UUID_request($access_token_reklama, $UUID_request);
echo "<br> Статус запроса отчета по UUID :";
print_r($check_status);
sleep(2);
$limit_time ++;
if ($limit_time > 5) {
    die('<br>ОЗОН не вернул отчет по рекламе .... <br>')
    ;}

} while ($check_status !='OK');




//************  Получаес ссылку на файл и формируем файл с указанным названием   *************/

$link_for_report_request_UUID = link_for_report_request_UUID ($access_token_reklama, $UUID_request);
$file_name = date('Y-M-d')."_(".rand(10000000,2).")_temp_zip_arc";


// $file_name = "2024-Oct-28_(2693887)_temp_zip_arc";

file_put_contents("temp/".$file_name.".zip", $link_for_report_request_UUID);
$zip = new ZipArchive();
$zip->open("temp/".$file_name.".zip");
$zip->extractTo("temp_csv/".$file_name);
$zip->close();

// echo "<pre>";
// print_r($link_for_report_request_UUID);

$dir    = "temp_csv/$file_name/";
$arr_files = scandir($dir, SCANDIR_SORT_DESCENDING);

// print_r($arr_files);
// print_r($arr_company_id_by_type);

foreach ($arr_company_id_by_type as $key_company=>$arr_id_company) {
    foreach ($arr_id_company as $id_company) {
    foreach ($arr_files as $files) {
        // echo "$files --------- $id_company<br>";
        if (strpos("__".$files, $id_company)  == true ) {

            $arr_comp_type[$key_company][$id_company] = $files; 

        }
        
    }}
}

// print_r($arr_comp_type);


/// парсим файлики с разбивкой на тип рекл компании

foreach ($arr_comp_type as $key_company=>$arr_id_company) {
    foreach ($arr_id_company as $key_id_company=>$file_url_company) {

$arr_all_data_reklam_company[$key_company][$key_id_company] = parce_csv_trafaret_and_vivod_v_top ($dir .$file_url_company);
}
}

// print_r($arr_all_data_reklam_company['SKU']);
// echo "<br>*********************************************<br>";





echo "<pre>";

foreach ($arr_all_data_reklam_company as $key_reklam_company=> $data) {
foreach ($data as $key_id_company=>$arr_data_company) {
    if ($key_reklam_company == 'SKU') {
        print_table_tarfareti_and_top ($key_id_company, $arr_data_company);
    } elseif ($key_reklam_company == 'SEARCH_PROMO') {

        //********************************************************************** */
        // формируем суммированный массив по продвигаемым товарам
        //********************************************************************** */
        foreach ($arr_data_company as $items){
            $temp_db_data = find_real_article_by_sku($pdo, $items['3'] , 'ozon_ip_zel') ;
            $mp_article_buy = $temp_db_data['mp_article']; // что купили
            $temp_db_data = find_real_article_by_sku($pdo, $items['4'] , 'ozon_ip_zel') ;
            $mp_article_reklam = $temp_db_data['mp_article']; // что рекламированили

            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['buy_sku']     =  $items['3'];
            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['buy_article'] = $mp_article_buy;

            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['reklam_sku']     =  $items['4'];
            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['reklam_article'] = $mp_article_reklam;


            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['count']   =  @$arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['count'] + $items['7'];
            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['summa']   =  @$arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['summa'] + str_replace(",", ".", $items['9']);
            $arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['stavka']  =  @$arr_sum_rekl_tovar[$mp_article_reklam][$mp_article_buy]['stavka'] + str_replace(",", ".", $items['12']);
        }
        
        // print_table_poisk ($pdo , $key_id_company, $arr_data_company);
    }
}

}



foreach ($arr_sum_rekl_tovar as &$rekl_items){
    foreach ($rekl_items as &$item){
        $item['reklam'] = round(($item['stavka']/$item['summa']) * 100,2);
    }
}


// echo "<pre>";
// print_r($arr_sum_rekl_tovar['7280-к-6-18']);

//
print_table_poisk_summ_data ($pdo, $key_id_company, $arr_sum_rekl_tovar);

die();




