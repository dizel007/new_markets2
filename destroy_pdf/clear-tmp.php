<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

const TMP_DIR = 'tmp';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Только POST'], JSON_UNESCAPED_UNICODE);
    exit;
}

$token = $_POST['csrf'] ?? '';
if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Неверный токен'], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Рекурсивно удаляет содержимое папки, сама папка остаётся.
 * @return array{deleted:int, failed:int}
 */
function clearDir(string $dir): array
{
    $deleted = 0;
    $failed  = 0;

    if (!is_dir($dir)) {
        return ['deleted' => 0, 'failed' => 0];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    $base = realpath($dir);

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $real = realpath($path);

        // Защита от выхода за пределы tmp (симлинки и пр.)
        if ($real === false || $base === false || strpos($real, $base) !== 0) {
            $failed++;
            continue;
        }

        try {
            if ($item->isDir() && !$item->isLink()) {
                if (@rmdir($path)) $deleted++; else $failed++;
            } else {
                if (@unlink($path)) $deleted++; else $failed++;
            }
        } catch (Throwable $e) {
            $failed++;
        }
    }

    return ['deleted' => $deleted, 'failed' => $failed];
}

try {
    if (!is_dir(TMP_DIR)) {
        throw new RuntimeException('Папка tmp/ не существует');
    }
    if (!is_writable(TMP_DIR)) {
        throw new RuntimeException('Нет прав на запись в tmp/');
    }

    $stats = clearDir(TMP_DIR);

    echo json_encode([
        'ok'      => true,
        'deleted' => $stats['deleted'],
        'failed'  => $stats['failed'],
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}