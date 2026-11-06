<?php
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
require 'save-cross-dok.php';

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
    // Получаем все строки в виде массива
    $excel_data = $sheet->toArray(null, true, true, false);

    // ---------- 2. Нормализация ----------
    $tariffs = [];
    
    // В вашем файле данные начинаются с 4-й строки (индекс 3), так как там сложная шапка.
    // Если структура изменится, нужно будет поправить этот параметр.
    $startRowIndex = 3; 

    foreach ($excel_data as $index => $row) {
        // Пропускаем заголовки
        if ($index < $startRowIndex) continue;

        // Берем первые 4 столбца: A, B, C, D
        $from   = trim((string)($row[0] ?? '')); // Кластер отправитель
        $to     = trim((string)($row[1] ?? '')); // Кластер получатель
        $pallet = $row[2] ?? null;               // Палета
        $box    = $row[3] ?? null;               // Коробка

        // Проверки: если пустые названия или цены - пропускаем строку
        if ($from === '' || $to === '' || $pallet === null || $box === null) continue;
        
        // На всякий случай проверяем, что бы не попали строки типа "Палета" или "Коробка" (если шапка съехала)
        if (mb_strtolower($from) === 'тарифная зона приёма поставки') continue;

        // Приводим к float, заменяя запятые на точки (для русской локали Excel)
        $palletVal = (float)str_replace(',', '.', (string)$pallet);
        $boxVal    = (float)str_replace(',', '.', (string)$box);

        // Если цены нулевые или отрицательные, возможно, стоит их отфильтровать (по желанию)
        // if ($palletVal <= 0 && $boxVal <= 0) continue;

        $tariffs[] = [
            'from'   => $from,
            'to'     => $to,
            'pallet' => $palletVal,
            'box'    => $boxVal,
        ];
    }

    // ---------- 3. Пишем в БД ----------
    $saved = saveCrossDokTariffs($tariffs);

    echo json_encode([
        'ok'         => true,
        'rows_count' => count($excel_data), // Всего строк в файле
        'processed'  => count($tariffs),    // Сколько отфильтровали
        'saved'      => $saved,             // Сколько записали в БД
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}