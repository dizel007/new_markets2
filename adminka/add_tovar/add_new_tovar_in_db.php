<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";

// echo "<pre>";
// print_r($_POST);
$shop_name = $_POST['shop'];
$main_article  = $_POST['existing_article'];
$mp_article    =  $_POST['mp_article'];

// проверяем может уже есть такие данные , то уходит
try {
$stmt = $pdo->prepare("SELECT * FROM ozon_ip_zel WHERE `main_article` = :main_article AND `mp_article` = :mp_article");
$stmt->execute(['main_article' => $main_article,
                'mp_article' => $mp_article]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
    if (count($products) > 0 ) {
            // print_r($products);
      // echo "<br>";
    // echo count($products);

        header ("Location: start_page.php");
        exit;
    }
    // Отдаём результат в формате JSON
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "Ошибка базы данных"]);
}



// если ОЗОН 
if (($shop_name == 'ozon_anmaks') OR ($shop_name =='ozon_ip_zel') )
    {
       $date_razbora = date('Y-m-d H:m:s+0300');
       $sql = "INSERT INTO `$shop_name` SET `main_article` = :main_article, 
                                             `mp_article` = :mp_article,
                                             `sku` = :sku, 
                                             `barcode` = :barcode, 
                                             `fbs` = 100,
                                             `active_tovar` = 1,
                                             `mp_name` = :mp_name";
       
       $stmt = $pdo->prepare($sql);
       
       $stmt->execute(array('main_article' => $_POST['existing_article'],
                            'mp_article'   => $_POST['mp_article'],
                            'sku'          => $_POST['sku'],
                            'barcode'      => $_POST['barcode'],
                            'mp_name'      => $_POST['mp_name']
                            ));
       
    //    $info = $stmt->errorInfo();
    //     print_r($info);
    // добавляем product_id
    require_once "find_product_id_ozon.php";

    }

elseif(($shop_name == 'wb_anmaks') OR ($shop_name =='wb_ip_zel') ) {

        $date_razbora = date('Y-m-d H:m:s+0300');
       $sql = "INSERT INTO `$shop_name` SET `main_article` = :main_article, 
                                             `mp_article` = :mp_article,
                                             `sku` = :sku, 
                                             `barcode` = :barcode, 
                                             `fbs` = 100,
                                             `active_tovar` = 1,
                                             `mp_name` = :mp_name";
       
       $stmt = $pdo->prepare($sql);
       
       $stmt->execute(array('main_article' => $_POST['existing_article'],
                            'mp_article'   => $_POST['mp_article'],
                            'sku'          => $_POST['sku'],
                            'barcode'      => $_POST['barcode'],
                            'mp_name'      => $_POST['mp_name']
                            ));


}


header ("Location: ../setup_shop_tables/start_shop_tables.php?shop_name=$shop_name");
