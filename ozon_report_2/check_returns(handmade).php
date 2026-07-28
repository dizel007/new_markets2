<?php
echo "Returns";
$json_string = file_get_contents('../!cache/1724451_main_data.json');
$prod_array = json_decode($json_string, true); // true — вернуть ассоциативный массив

echo "<pre>";

// делаем один последовательный массив в операциями
foreach ($prod_array as $items) {
    foreach ($items as $item) {$new_prod_array[] = $item;}
}

foreach ($new_prod_array as $item) {
    if ($item['type'] == 'returns') {
        // Доставка и обработка возврата, отмены, невыкупа   
        $arr_returns_razbor[] = $item;
    }
}





$i=0;
$arr_test_spisok_returns_postnumbers = array();
foreach ($arr_returns_razbor as $items) {

$str = $items['posting']['posting_number'];
$post_number_all = explode('-', $str);
$post_number = implode('-', array_slice($post_number_all, 0, 1));

$arr_ret_razbor[$post_number][$str] = $str;


}


print_r($arr_ret_razbor);
