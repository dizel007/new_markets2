<?php
function convertPdfTo14($inputFile) {
  

    // 1. Получаем абсолютный путь к входному файлу
    $inputReal = realpath($inputFile);
    if ($inputReal === false) {
        echo "Файл не найден: $inputFile<br>";
        return false;
    }

    $path_parts = pathinfo($inputReal);
    $outputDir = $path_parts['dirname'] . DIRECTORY_SEPARATOR . 'convert';

    // 2. Создаём папку convert, если её нет
    if (!is_dir($outputDir)) {
        if (!mkdir($outputDir, 0777, true)) {
            echo "Не удалось создать папку: $outputDir<br>";
            return false;
        }
    }

    
    $outputFile = $outputDir . DIRECTORY_SEPARATOR . $path_parts['filename'] . '_v14.pdf';

    // 3. Ручное экранирование двойными кавычками (для Windows)
    $inputArg = '"' . $inputReal . '"';
    $outputArg = '"' . $outputFile . '"';

    // 4. Команда без -dQUIET, с перенаправлением stderr
// Делаем разделение для локальной на Виндовс и для VDS  для LINUX
    if (PHP_OS_FAMILY === 'Windows') {
    $gsPath = 'C:\Program Files\gs\gs10.07.1\bin\gswin64c.exe';
    $command = '"' . $gsPath . '" -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $outputArg . ' ' . $inputArg . ' 2>&1';

} else {
    $gsPath = '/usr/bin/gs';   // путь, который вы проверили через which gs
    $command = $gsPath . ' -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=' . $outputArg . ' ' . $inputArg . ' 2>&1';
}


    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        echo "Ошибка Ghostscript (код $returnCode):<br>";
        echo implode("<br>", $output);
        error_log("GS error: " . implode("\n", $output));
        return false;
    }

    return file_exists($outputFile) ? $outputFile : false;
}