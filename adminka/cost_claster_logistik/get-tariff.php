<?php
header('Content-Type: application/json; charset=utf-8');

$offset = "../../";
require_once $offset . "connect_db.php";
global $pdo;

try {
    $from   = $_GET['from'] ?? '';
    $to     = $_GET['to'] ?? '';
    $volume = isset($_GET['volume']) ? (float)$_GET['volume'] : 0;

    if ($from === '' || $to === '' || $volume <= 0) {
        throw new Exception('Неверные параметры');
    }

    $stmt = $pdo->prepare("
        SELECT cost_norm
        FROM ozon_logistika_price
        WHERE claster_send = :from
          AND claster_get = :to
          AND :volume >= min_litr
          AND :volume <= max_litr
        LIMIT 1
    ");

    $stmt->execute([
        ':from'   => $from,
        ':to'     => $to,
        ':volume' => $volume
    ]);

    $price = $stmt->fetchColumn();

    echo json_encode([
        'ok'    => true,
        'price' => ($price !== false) ? (float)$price : null
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage()
    ]);
}