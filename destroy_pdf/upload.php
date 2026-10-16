<?php
require 'process.php';

header('Content-Type: application/json; charset=utf-8');

const MAX_FILES   = 100;
const MAX_SIZE_MB = 20;
const TMP_DIR     = 'tmp';

try {
    if (empty($_FILES['files']['name'][0])) {
        throw new RuntimeException('Файлы не переданы');
    }

    if (!is_dir(TMP_DIR) && !mkdir(TMP_DIR, 0775, true) && !is_dir(TMP_DIR)) {
        throw new RuntimeException('Не удалось создать папку tmp/');
    }

    $count = count($_FILES['files']['name']);
    if ($count > MAX_FILES) {
        throw new RuntimeException('Максимум ' . MAX_FILES . ' файлов, получено ' . $count);
    }

    $jobId  = bin2hex(random_bytes(8));
    $jobDir = TMP_DIR . '/' . $jobId;

    if (!mkdir($jobDir, 0775, true) && !is_dir($jobDir)) {
        throw new RuntimeException('Не удалось создать папку задачи');
    }

    $results = [];
    $zipPath = $jobDir . '/result.zip';
    $zip     = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Не удалось создать ZIP-архив');
    }

    foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
        $name = (string)$_FILES['files']['name'][$i];
        $err  = (int)$_FILES['files']['error'][$i];
        $size = (int)$_FILES['files']['size'][$i];

        if ($err !== UPLOAD_ERR_OK) {
            $results[] = ['name' => $name, 'ok' => false, 'error' => 'Ошибка загрузки #' . $err];
            continue;
        }

        if ($size > MAX_SIZE_MB * 1024 * 1024) {
            $results[] = ['name' => $name, 'ok' => false, 'error' => 'Файл больше ' . MAX_SIZE_MB . ' МБ'];
            continue;
        }

        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'pdf') {
            $results[] = ['name' => $name, 'ok' => false, 'error' => 'Не PDF'];
            continue;
        }

        $safeBase = preg_replace('/[^\w\-\.]+/u', '_', pathinfo($name, PATHINFO_FILENAME));
        if ($safeBase === '' || $safeBase === null) {
            $safeBase = 'file_' . $i;
        }

        // Уникализируем имя внутри задачи, чтобы не перезаписывать друг друга
        $uniqueBase = sprintf('%03d_%s', $i, $safeBase);
        $inPath     = $jobDir . '/' . $uniqueBase . '_in.pdf';
        $outPath    = $jobDir . '/' . $uniqueBase . '_trimmed.pdf';

        if (!move_uploaded_file($tmp, $inPath)) {
            $results[] = ['name' => $name, 'ok' => false, 'error' => 'Не удалось сохранить файл'];
            continue;
        }

        try {
            removeLastPage($inPath, $outPath);
            $zip->addFile($outPath, $safeBase . '_trimmed.pdf');
            $results[] = ['name' => $name, 'ok' => true];
        } catch (Throwable $e) {
            $results[] = ['name' => $name, 'ok' => false, 'error' => $e->getMessage()];
        } finally {
            @unlink($inPath);
        }
    }

    $zip->close();

    $okCount = 0;
    foreach ($results as $r) {
        if ($r['ok']) $okCount++;
    }

    if ($okCount === 0) {
        // Полная неудача — чистим и сообщаем
        $items = glob($jobDir . '/*') ?: [];
        foreach ($items as $f) {
            if (is_file($f)) @unlink($f);
        }
        @rmdir($jobDir);

        throw new RuntimeException('Ни один файл не был обработан');
    }

    echo json_encode([
        'ok'      => true,
        'jobId'   => $jobId,
        'zipUrl'  => 'download.php?job=' . $jobId,
        'results' => $results,
        'total'   => $count,
        'success' => $okCount,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}