<?php



use Com\Tecnick\Barcode\Barcode;


 $folder = "temp_darvin_pdf";
$data_for_shtrihCode = razbor_all_tranzactions_yandex($uploadedData);


try {
    foreach ($data_for_shtrihCode as $article=>$code) {
        $codeNumber = $code['strihcode'];
    // Генерация штрих-кода (EAN13)
    $barcode = new Barcode();
    $bobj = $barcode->getBarcodeObj(
        'EAN13',
         $codeNumber,
        200,
        100,
        'black',
        [0, 0, 0, 0]
    )->setBackgroundColor('white');
    $barcodePngData = $bobj->getPngData();


     $pdf = new TCPDF('L', 'mm', [60, 40], true, 'UTF-8', false);
for ($i =0; $i < $code['count']; $i++ ) {
    // Создание PDF с размером страницы 60x40 мм
   
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setAutoPageBreak(false);   // отключаем авто-перенос страниц
    $pdf->AddPage();

    // Вставка штрих-кода (x=5, y=5, ширина 50 мм, высота автоматическая)
    $pdf->Image('@' . $barcodePngData, 2, 1, 56, 0, 'PNG');

    // Подключаем шрифт (если не добавили заранее, лучше сделать один раз)
    $fontname = TCPDF_FONTS::addTTFfont('arialbd.ttf', 'TrueTypeUnicode', '', 96);
    // $fontname = 'helvetica'; // или 'arialbd', если успешно добавили
    $pdf->SetFont($fontname, '', 8);

    // Размещаем текст под штрих-кодом (при высоте кода примерно 25 мм)
    $pdf->SetY(30);  // Y=32 мм от верхнего края (высота листа 40 мм, отступ снизу 8 мм)
    $pdf->Cell(40, 2, $codeNumber , 0, 1, 'C');
    $pdf->SetY(34);  
    $pdf->SetX(2);  
$text_1 = ($i+1)." DARVIN";
    $pdf->Cell(15, 4, $text_1, 1, 1, 'C');
    $pdf->SetY(34);  
    $pdf->SetX(20);  
    $pdf->Cell(16, 4, "арт.".$article, 0, 1, 'C');
}
    // Сохраняем PDF
   
    $pdfPath = __DIR__ . '/'.$folder.'/'.$article.'_darvin_.pdf';
    $pdf->Output($pdfPath, 'F');
    }
    // echo "✅ PDF успешно создан: " . $pdfPath;
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}





///////////////////////////////////////////////////////////////
// После создания всех PDF-файлов и перед zip:
$zipFileName = "temp_archive_zip/darvin_blya.zip";

// Удаляем все PDF из папки temp_darvin/
    array_map('unlink', glob($folder . '*.pdf'));

$zip = new ZipArchive();
if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $folder . DIRECTORY_SEPARATOR . $item;
        if (is_file($fullPath)) {
            $zip->addFile($fullPath, $item);
        }
    }
    $zip->close();
    // НЕ выводим echo "Архив создан" – это испортит скачивание!
} else {
    // Ошибка – но тоже не выводим текст, а пишем в лог или прерываем через die()
    error_log("Не удалось создать архив");
    die("Ошибка создания архива");
}


// ссылка на скачивание
if (file_exists($zipFileName)) {
echo <<<HTML
   <a class="download" href="$zipFileName">СКАЧАТЬ</a>.
HTML;
}

// // Отправка архива
// if (file_exists($zipFileName)) {
//     // Очищаем все буферы вывода (на случай, если что-то попало)
//     while (ob_get_level()) ob_end_clean();

//     header('Content-Description: File Transfer');
//     header('Content-Type: application/zip');
//     header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
//     header('Content-Length: ' . filesize($zipFileName));
//     header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//     header('Pragma: public');
//     header('Expires: 0');

//     readfile($zipFileName);

//     // Удаляем временный ZIP и PDF-файлы
//     unlink($zipFileName);

//     exit;
// } else {
//     // Тут тоже нельзя просто echo, если уже начались заголовки. Но в этом месте заголовки ещё не отправлены.
//     // die("Архив не найден");
// }