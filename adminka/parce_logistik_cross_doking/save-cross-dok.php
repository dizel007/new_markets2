<?php
require_once 'db.php';

function saveCrossDokTariffs(array $tariffs): int
{
    if (!$tariffs) return 0;

    $pdo = getDb();
    
    // ВНИМАНИЕ: Если нужно удалять старые данные перед загрузкой новых, раскомментируйте строку ниже
    // $pdo->exec("TRUNCATE TABLE ozon_logistika_cross_dok");

    $pdo->beginTransaction();

    try {
        // Простой INSERT, так как ключевых полей может не быть (или они не настроены)
        $sql = "INSERT INTO ozon_logistika_cross_dok 
                    (claster_from, claster_to, pallet, box) 
                VALUES (:from, :to, :pallet, :box)";
                
        $stmt = $pdo->prepare($sql);

        foreach ($tariffs as $t) {
            $stmt->execute([
                ':from'   => $t['from'],
                ':to'     => $t['to'],
                ':pallet' => $t['pallet'],
                ':box'    => $t['box'],
            ]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw new Exception("Ошибка SQL: " . $e->getMessage());
    }

    return count($tariffs);
}