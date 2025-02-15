<?php
die('jjjjjjjjjjjjjjj');
require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";

require_once "../mp_functions/report_excel_file.php";

$ozon_shop = $_GET['ozon_shop'];
// $test_posting = '16450199-0120';




// echo $pos2."<br>";
// echo $pos4;


$t = file_get_contents('ozon_est.json');
$array_with_all_sellers = json_decode($t, true);

echo "<pre>";
$i=0;
foreach ($array_with_all_sellers as $arr_temp) {
    foreach ($arr_temp as $add) {

        $array_MINI[] = $add;
     
}



}
foreach ($array_MINI as $item) {
   
    $posting_number = $item['posting']['posting_number'];
    $posting_number_for_array = make_posting_number ($posting_number);
  
    $prod_array_2[$posting_number_for_array][] = $item;
    $prod_array_posts[$posting_number_for_array][] = $posting_number_for_array;

}

$prod_array = $prod_array_2;
require "razbor_dannih_one_item.php";
// unset ($prod_array);
// die();
// }

die();








