<?php
header('Content-Type: application/json; charset=utf-8');

$offset = "../../";
require_once $offset . "connect_db.php";
global $pdo;

try {
    $from = $_GET['from'] ?? '';
    $to   = $_GET['to'] ?? '';

    if ($from === '' || $to === '') {
        throw new Exception('Неверные параметры');
    }

    // Ищем тариф кросс-докинга по маршруту
    // Используем кодировку utf8mb4, поэтому просто сравниваем строки
    $stmt = $pdo->prepare("
        SELECT pallet, box
        FROM ozon_logistika_cross_dok
        WHERE claster_from = :from
          AND claster_to = :to
        LIMIT 1
    ");

    $stmt->execute([
        ':from' => $from,
        ':to'   => $to
    ]);

    $tariff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tariff) {
        echo json_encode([
            'ok'     => true,
            'pallet' => (float)$tariff['pallet'], // Цена за 1 литр в паллетах
            'box'    => (float)$tariff['box']     // Цена за 1 литр в коробках
        ]);
    } else {
        echo json_encode([
            'ok'     => true,
            'pallet' => null,
            'box'    => null
        ]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage()
    ]);
}