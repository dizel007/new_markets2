<?php
$offset = "../../";
require_once($offset."connect_db.php"); // подключение к БД



$stmt = $pdo->prepare("SELECT * FROM `ostatki_po_skladam` WHERE `active_tovar` = 1 ");
$stmt->execute([]);
$tovar_table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$arr_post = $_POST;



foreach ($tovar_table_data as $item) {
foreach ($arr_post as $key => $value) {

        $article_1c = $item['main_article_1c'];
        


           if ($key == '_mp_wb_anmaks_'.$article_1c) {
                $arr_update[$article_1c]['wb_anmaks'] = $value;
            }
           elseif ($key == '_mp_wb_ip_zel_'.$article_1c) {
                $arr_update[$article_1c]['wb_ip_zel'] = $value;
            }
            elseif ($key == '_mp_ozon_anmaks_'.$article_1c) {
                $arr_update[$article_1c]['ozon_anmaks'] = $value;
            }
            elseif ($key == '_mp_ozon_ip_zel_'.$article_1c) {
                $arr_update[$article_1c]['ozon_ip_zel'] = $value;
            }
            elseif ($key == '_mp_ya_anmaks_fbs_'.$article_1c) {
                $arr_update[$article_1c]['ya_anmaks_fbs'] = $value;
            }
            elseif  ($key == '_mp_block_'.$article_1c) {


                $arr_update[$article_1c]['block'] = 1;
         
          }
    }
}

// print_r($arr_update);



foreach ($arr_update as $key=>$item_for_update) {

    if (!isset($item_for_update['block'])) {
        $item_for_update['block'] =0;
    }
$sql = "UPDATE `ostatki_po_skladam` SET `wb_anmaks` = :wb_anmaks, 
                                                        `wb_ip_zel` = :wb_ip_zel, 
                                                        `ozon_anmaks` = :ozon_anmaks, 
                                                        `ozon_ip_zel` = :ozon_ip_zel, 
                                                        `ya_anmaks_fbs` = :ya_anmaks_fbs,
                                                         `block_tovar` = :block_tovar

                        WHERE `main_article_1c` = :main_article_1c";

$stmt = $pdo->prepare($sql);

$stmt->execute(array('wb_anmaks'     => $item_for_update['wb_anmaks'],
                     'wb_ip_zel'     => $item_for_update['wb_ip_zel'],
                     'ozon_anmaks'   => $item_for_update['ozon_anmaks'],
                     'ozon_ip_zel'   => $item_for_update['ozon_ip_zel'],
                     'ya_anmaks_fbs' => $item_for_update['ya_anmaks_fbs'],
                     'block_tovar'   => $item_for_update['block'],
                    
                     'main_article_1c' => $key));

$info = $stmt->errorInfo();
// print_r($info);
}




header('Location: start_admin_mode.php', true, 301);
exit();


