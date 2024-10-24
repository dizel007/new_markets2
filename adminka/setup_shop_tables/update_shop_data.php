<?php
include_once "../../connect_db.php";



$shop_name = "ozon_ip_zel";
$stmt = $pdo->prepare("SELECT * FROM `$shop_name`");
$stmt->execute([]);
$array_db_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


// echo "<pre>";


$shop_name  = $_POST['_shop_name_'];
unset($_POST['_shop_name_']);


foreach ($_POST as $key=>$temp) {
   /// Находим SKU товара
 if (mb_strpos($key, "sku_")) {
    $sku_key = $temp;
    $key_for_array[$sku_key]['sku'] = $temp;
 }
/// Находим % распределения товара
if (mb_strpos($key, "fbs_")) {
    $key_for_array[$sku_key]['fbs'] = $temp;
    }
 /// Находим фейкоовое количетво товара
if (mb_strpos($key, "fake_count_")) {
    $key_for_array[$sku_key]['fake_count'] = $temp;
    }   

 /// Находим активен ли товар
 if (mb_strpos($key, "on_check_")) {
    // echo "*";
    $key_for_array[$sku_key]['on_check'] = 1;
}  
}






foreach ($array_db_items as &$item_db) {
    foreach ($key_for_array as $item_update) {

        if ($item_db['sku'] == $item_update['sku']) {
            
            ($item_db['fbs'] != $item_update['fbs'])?($item_db['fbs'] = $item_update['fbs']):($item_db['fbs'] = $item_db['fbs']);
            ($item_db['fake_count'] != $item_update['fake_count'])?($item_db['fake_count'] = $item_update['fake_count']):($item_db['fake_count'] = $item_db['fake_count']);
            (isset($item_update['on_check']))?($item_db['active_tovar'] = 1):($item_db['active_tovar'] = 0);

        }
  
    
    }   
}




// print_r($array_db_items);


// die();

foreach ($array_db_items as $item_for_update) {

$sql = "UPDATE `$shop_name` SET `fbs` = :fbs, 
                                `fake_count` = :fake_count, 
                                `active_tovar` = :active_tovar
                                
                        WHERE `sku` = :sku";

$stmt = $pdo->prepare($sql);

$stmt->execute(array('fbs'     => $item_for_update['fbs'],
                     'fake_count'     => $item_for_update['fake_count'],
                     'active_tovar'   => $item_for_update['active_tovar'],
                                      
                     'sku' => $item_for_update['sku']));

$info = $stmt->errorInfo();
// print_r($info);
}




header('Location: start_shop_tables.php?shop_name='.$shop_name, true, 301);
exit();


