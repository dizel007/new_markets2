<?php
require_once 'db.php';

function saveTariffs(array $tariffs): int
{
    if (!$tariffs) return 0;

    $pdo = getDb();
    $pdo->beginTransaction();

    try {
        // Вставляем 5 полей. Если совпадают 4 (по индексу), обновляем только цену.
        $sql = "INSERT INTO ozon_logistika_price 
                    (min_litr, max_litr, claster_send, claster_get, cost_norm) 
                VALUES (:min, :max, :from, :to, :price) 
                ON DUPLICATE KEY UPDATE cost_norm = VALUES(cost_norm)";
                
        $stmt = $pdo->prepare($sql);

        foreach ($tariffs as $t) {
            $stmt->execute([
                ':min'   => $t['min'],
                ':max'   => $t['max'],
                ':from'  => $t['from'],
                ':to'    => $t['to'],
                ':price' => $t['price'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw new Exception("Ошибка SQL: " . $e->getMessage());
    }

    return count($tariffs);
}