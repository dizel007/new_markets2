<?php
// ini_set('display_errors', '0');
// error_reporting(E_ALL);

// ВРЕМЕННО ВКЛЮЧАЕМ ОШИБКИ ДЛЯ ОТЛАДКИ
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'PHP fatal: ' . $err['message']], JSON_UNESCAPED_UNICODE);
    }
});

require '../../vendor/autoload.php';
require 'save-tariffs.php';

$excel_data = [];

try {
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Файл не загружен');
    }
    $tmp  = $_FILES['file']['tmp_name'];
    $name = $_FILES['file']['name'];

    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'xlsx') {
        throw new RuntimeException('Нужен файл .xlsx');
    }

    // ---------- 1. Читаем Excel ----------
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($tmp);

    $sheet      = $spreadsheet->getSheet(0);
    $highestRow = $sheet->getHighestDataRow();
    $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
        $sheet->getHighestDataColumn()
    );

    for ($r = 1; $r <= $highestRow; $r++) {
        $row   = [];
        $empty = true;
        for ($c = 1; $c <= $highestCol; $c++) {
            $val = $sheet->getCellByColumnAndRow($c, $r)->getValue();
            if (is_string($val)) $val = trim($val);
            $row[] = $val;
            if ($val !== null && $val !== '') $empty = false;
        }
        if (!$empty) $excel_data[] = $row;
    }

    // ---------- 2. Нормализация ----------
    $tariffs = [];
    foreach ($excel_data as $row) {
        if (count($row) < 5) continue;

        $volumeRaw = (string)($row[1] ?? '');
        $from      = (string)($row[2] ?? '');
        $to        = (string)($row[3] ?? '');
        $priceRaw  = $row[4] ?? null;

        if ($volumeRaw === '' || $from === '' || $to === '' || $priceRaw === null) continue;
        if (mb_strtolower($volumeRaw) === 'объём' || mb_strtolower($volumeRaw) === 'объем') continue;

        $range = parseVolumeRange($volumeRaw);
        if (!$range) continue;

        $price = (float)$priceRaw;
        if ($price <= 0) continue;

        $tariffs[] = [
            'min'   => $range['min'],
            'max'   => $range['max'],
            'from'  => $from,
            'to'    => $to,
            'price' => $price,
        ];
    }

    // ---------- 3. Пишем в БД ----------
    $saved = saveTariffs($tariffs);

    echo json_encode([
        'ok'         => true,
        'rows_count' => count($excel_data),
        'processed'  => count($tariffs),
        'saved'      => $saved,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/* ---------- helpers ---------- */

function parseVolumeRange(string $s): ?array
{
    $s = str_replace(['л.', 'л', ' '], ['', '', ''], $s);
    $s = str_replace(',', '.', $s);

    if (preg_match('/^от([\d.]+)$/ui', $s, $m)) {
        return ['min' => (float)$m[1], 'max' => 1e9];
    }
    if (preg_match('/^([\d.]+)-([\d.]+)$/u', $s, $m)) {
        return ['min' => (float)$m[1], 'max' => (float)$m[2]];
    }
    return null;
}