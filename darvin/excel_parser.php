<?php
// excel_parser.php


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Парсит второй лист Excel-файла, корректно обрабатывая объединённые ячейки.
 *
 * @param string $filePath Полный путь к файлу .xlsx или .xls
 * @return array
 * @throws Exception
 */
function parseSecondSheetWithMergedCells(string $filePath): array
{
    if (!file_exists($filePath)) {
        throw new Exception("Файл не найден: " . $filePath);
    }

    // Читаем с форматированием, чтобы получить информацию об объединённых ячейках
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(false);
    $spreadsheet = $reader->load($filePath);

    
    $sheet = $spreadsheet->getSheet(0); // второй лист (индексация с 0)
    $sheetTitle = $sheet->getTitle();

    $mergedRanges = $sheet->getMergeCells();
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

    // Строим карту объединённых ячеек
    $mergeMap = [];
    foreach ($mergedRanges as $range) {
        $rangeBounds = Coordinate::splitRange($range);
        $startCell = $rangeBounds[0][0];
        $endCell = $rangeBounds[0][1];

        $startCoord = Coordinate::coordinateFromString($startCell);
        $endCoord = Coordinate::coordinateFromString($endCell);

        $startRow = (int)$startCoord[1];
        $startCol = Coordinate::columnIndexFromString($startCoord[0]);
        $endRow = (int)$endCoord[1];
        $endCol = Coordinate::columnIndexFromString($endCoord[0]);

        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($col = $startCol; $col <= $endCol; $col++) {
                $key = $row . '_' . $col;
                $mergeMap[$key] = [
                    'masterCell' => $startCoord[0] . $startRow,
                    'range' => $range
                ];
            }
        }
    }

    // Чтение всех ячеек листа
    $data = [];
    for ($row = 1; $row <= $highestRow; $row++) {
        $rowData = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cellKey = $row . '_' . $col;
            if (isset($mergeMap[$cellKey])) {
                $masterCell = $mergeMap[$cellKey]['masterCell'];
                $value = $sheet->getCell($masterCell)->getCalculatedValue();
            } else {
                $cell = $sheet->getCellByColumnAndRow($col, $row);
                $value = $cell->getCalculatedValue();
            }
            $rowData[] = ($value === null || $value === '') ? '' : $value;
        }
        $data[] = $rowData;
    }

    return [
        'sheet_name' => $sheetTitle,
        'data' => $data,
        'merged_ranges' => $mergedRanges,
        'rows' => $highestRow,
        'cols' => $highestColumnIndex
    ];
}