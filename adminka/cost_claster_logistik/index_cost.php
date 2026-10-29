<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . 'vendor/autoload.php';

use App\Ozon\OzonClient;

require_once "start_form.php";

$shop_name    = $_GET['ozon_shop'] ?? '';
$cluster_from = $_GET['ClusterFrom'] ?? '';
$cluster_to   = $_GET['ClusterTo'] ?? '';

// Если магазин не выбран — показываем пустую форму
if (empty($shop_name)) {
    render_form([], $cluster_from, $cluster_to);
    exit;
}

// Проверка наличия токена
if (!isset($arr_tokens[$shop_name])) {
    echo "<p style='text-align:center; color:red;'>Ошибка: Не найден магазин '{$shop_name}'.</p>";
    render_form([], $cluster_from, $cluster_to);
    exit;
}

$token     = $arr_tokens[$shop_name]['token'];
$client_id = $arr_tokens[$shop_name]['id_market'];

$ozon = new OzonClient($token, $client_id);

$arr_good_tovar = [];

try {
    $data = [
        "filter" => [
            "offer_id"   => [],
            "product_id" => [],
            "skus"       => [],
            "visibility" => "ALL"
        ],
        "limit" => 100
    ];

    $tovary = $ozon->post('v3/product/list', $data);

    if (!empty($tovary['result']['items'])) {
        $array_sku = [];
        foreach ($tovary['result']['items'] as $item) {
            $arr_good_tovar[$item['offer_id']]['sku'] = $item['sku'];
            $array_sku[] = $item['sku'];
        }

        $data2 = [
            "filter" => [
                "offer_id"   => [],
                "product_id" => [],
                "sku"        => $array_sku,
                "visibility" => "ALL"
            ],
            "limit"    => 100,
            "sort_dir" => "ASC"
        ];

        $harak2 = $ozon->post('v4/product/info/attributes', $data2);

        if (!empty($harak2['result'])) {
            foreach ($harak2['result'] as $item) {
                $arr_good_tovar[$item['offer_id']]['value']  = round($item['height'] * $item['depth'] * $item['width'] / 1000000, 2);
                $arr_good_tovar[$item['offer_id']]['weight'] = $item['weight'];
            }
        }
    }
} catch (Throwable $e) {
    echo "<p style='text-align:center; color:red;'>Ошибка API Ozon: " . htmlspecialchars($e->getMessage()) . "</p>";
}

render_form($arr_good_tovar, $cluster_from, $cluster_to);