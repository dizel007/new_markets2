<?php
// Подключаем настройки БД (путь может отличаться, у вас $offset = "../../")
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";


// Предположим, у вас есть таблица `marketplace_products` со структурой:
// id, shop (значение, например 'ozon_anmaks'), main_article, mp_article, sku, barcode, mp_name
// Если таблица называется иначе, подставьте правильное имя.

try {
    // Используйте подготовленный запрос для безопасности
    $stmt = $pdo->prepare("SELECT main_article_1c FROM nomenklatura WHERE active_tovar = 1 ORDER BY main_article_1c");
    $stmt->execute([]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Отдаём результат в формате JSON
    header('Content-Type: application/json');
    echo json_encode($products);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "Ошибка базы данных"]);
}