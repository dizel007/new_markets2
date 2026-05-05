<?php
// index.php
// require_once 'connect.php';
require '../vendor/autoload.php';
require_once 'excel_parser.php';
require_once 'raschet_data.php';


$uploadedData = [];
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_files'])) {
    $files = $_FILES['excel_files'];
    $fileCount = count($files['name']);

    if ($fileCount === 0) {
        $errorMessages[] = "Вы не выбрали ни одного файла.";
    } elseif ($fileCount > 10) {
        $errorMessages[] = "Можно загрузить не более 10 файлов. Вы выбрали $fileCount.";
    } else {
        $tempDir = sys_get_temp_dir() . '/excel_uploads_' . uniqid();
        mkdir($tempDir, 0777, true);

        foreach ($files['tmp_name'] as $index => $tmpName) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                $errorMessages[] = "Ошибка загрузки файла \"{$files['name'][$index]}\" (код: {$files['error'][$index]}).";
                continue;
            }

            $originalName = $files['name'][$index];
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            if (!in_array($extension, ['xlsx', 'xls'])) {
                $errorMessages[] = "Файл \"$originalName\" имеет недопустимое расширение. Разрешены только .xlsx и .xls.";
                continue;
            }

            $tempFilePath = $tempDir . '/' . uniqid() . '.' . $extension;
            if (!move_uploaded_file($tmpName, $tempFilePath)) {
                $errorMessages[] = "Не удалось переместить файл \"$originalName\" во временную папку.";
                continue;
            }

            try {
                $result = parseSecondSheetWithMergedCells($tempFilePath);
                $uploadedData[] = [
                    'filename' => $originalName,
                    'result' => $result
                ];
            } catch (Exception $e) {
                $errorMessages[] = "Ошибка при парсинге файла \"$originalName\": " . $e->getMessage();
            } finally {
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }
        }

        if (file_exists($tempDir)) {
            rmdir($tempDir);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Формирование Этикеток Дарвина</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>📎 Загрузка Excel файла Заказ Поставщику Дарвина</h1>
    <p>Парсится <strong>первый лист</strong>. Объединённые ячейки обрабатываются корректно (значение берётся из главной ячейки).</p>

    <form action="" method="post" enctype="multipart/form-data">
        <label>Выберите Excel-файл (.xlsx, .xls):</label>
        <input type="file" name="excel_files[]" accept=".xlsx,.xls" multiple required>
        <small>Можно выбрать 1 файл.</small>
        <br><br>
        <button type="submit">🚀 Загрузить и распарсить</button>
    </form>

    <?php if (!empty($errorMessages)): ?>
        <div class="error-message">
            <strong>⚠️ Возникли ошибки:</strong><br>
            <?php foreach ($errorMessages as $msg): ?>
                <?= htmlspecialchars($msg) ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


<?php 
if (count($uploadedData) > 0) {
require_once "print_table.php"; 
}
?>


    