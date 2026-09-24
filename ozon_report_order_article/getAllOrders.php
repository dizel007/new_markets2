<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "functions_orders_all.php";
require_once "print_FBO_FBS_sells.php";

// доставем всю номенсклатуру
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
$nomenclature = [];

foreach ($arr_all_nomenklatura as $zzz) {
    $nomenclature[] = mb_strtolower($zzz['main_article_1c']);
}

// Вставляем форму для ввода даты.
// Здесь же определяются: $date_from, $date_to, $shop_name, $token_ozon, $client_id_ozon, $select_nomenclature
require_once "start_form_article.php";

// Проверяем и если нет папки, то создаем ее, для каждого пользователя своя папка в !cache
$dir_for_cache = "../!cache/" . $userdata['user_login'] . "/";

if (!is_dir($dir_for_cache)) {
    if (!mkdir($dir_for_cache, 0755, true) && !is_dir($dir_for_cache)) {
        die("Не удалось создать папку кеша.");
    }
}

/**
 * Ключ кеша зависит только от магазина и дат.
 * Если магазин или даты изменились — ключ другой, будет новый файл.
 * Артикул на API-запрос не влияет, поэтому в ключ его не добавляем.
 */
$cache_key = md5($shop_name . '|' . $date_from . '|' . $date_to);

$file_fbs = $dir_for_cache . "FBS_orders_" . $cache_key . ".json";
$file_fbo = $dir_for_cache . "FBO_orders_" . $cache_key . ".json";

/***************************************************************************************************************
 * Заказы FBS
 **************************************************************************************************************/

$orders_FBS_ooo = null;

if (is_file($file_fbs)) {
    $json = file_get_contents($file_fbs);
    $decoded = json_decode($json, true);

    if (is_array($decoded)) {
        $orders_FBS_ooo = $decoded;
    }
}

if ($orders_FBS_ooo === null) {
    // В кеше нет или файл битый — получаем из API
    $orders_FBS_ooo = query_FBS_orders_from_ozon(
        $token_ozon,
        $client_id_ozon,
        $date_from,
        $date_to
    );

    file_put_contents(
        $file_fbs,
        json_encode($orders_FBS_ooo, JSON_UNESCAPED_UNICODE)
    );
}

/***************************************************************************************************************
 * Заказы FBO
 **************************************************************************************************************/

$orders_FBO_ooo = null;

if (is_file($file_fbo)) {
    $json = file_get_contents($file_fbo);
    $decoded = json_decode($json, true);

    if (is_array($decoded)) {
        $orders_FBO_ooo = $decoded;
    }
}

if ($orders_FBO_ooo === null) {
    // В кеше нет или файл битый — получаем из API
    $orders_FBO_ooo = query_FBO_orders_from_ozon(
        $token_ozon,
        $client_id_ozon,
        $date_from,
        $date_to
    );

    file_put_contents(
        $file_fbo,
        json_encode($orders_FBO_ooo, JSON_UNESCAPED_UNICODE)
    );
}

/***************************************************************************************************************
 * Формируем общий массив заказов по артикулам
 **************************************************************************************************************/

$orders_our_article = make_array_with_all_orders($orders_FBS_ooo, $select_nomenclature);

$orders_our_article = array_merge(
    $orders_our_article,
    make_array_with_all_orders($orders_FBO_ooo, $select_nomenclature)
);

require_once "get_orders_by_article.php";