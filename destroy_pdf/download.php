<?php
$job = preg_replace('/[^a-f0-9]/', '', $_GET['job'] ?? '');

if ($job === '' || strlen($job) !== 16) {
    http_response_code(400);
    exit('Некорректный идентификатор');
}

$file = 'tmp/' . $job . '/result.zip';

if (!is_file($file)) {
    http_response_code(404);
    exit('Файл не найден или уже удалён');
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="trimmed_' . $job . '.zip"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-store');

readfile($file);
exit;