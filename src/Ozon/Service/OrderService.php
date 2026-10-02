<?php

namespace App\Ozon\Service;

use App\Ozon\OzonClient;

class OrderService
{
    private OzonClient $ozon;

    public function __construct(OzonClient $ozon)
    {
        $this->ozon = $ozon;
    }

    /**
     * FBS-отправления, ожидающие обработки (awaiting_packaging / awaiting_deliver).
     *
     * @param string $dateQueryOzon  Начало периода 'Y-m-d' (фильтр по cutoff_from)
     * @param string $status         'awaiting_packaging' | 'awaiting_deliver'
     * @param int    $dopDaysQuery   На сколько дней вперёд от $dateQueryOzon смотреть (cutoff_to)
     * @return array                 Плоский массив postings
     */
    public function getUnfulfilledFbs(string $dateQueryOzon, string $status, int $dopDaysQuery): array
    {
        $startTs = strtotime($dateQueryOzon . ' 00:00:00');
        if ($startTs === false) {
            throw new \InvalidArgumentException("Некорректная дата начала: $dateQueryOzon");
        }

        $dateQueryEnd = date('Y-m-d', strtotime("+$dopDaysQuery day", $startTs));

        $payload = [
            'sort_dir' => 'ASC',
            'filter'   => [
                'cutoff_from'        => $dateQueryOzon . 'T00:00:00Z',
                'cutoff_to'          => $dateQueryEnd  . 'T23:59:59Z',
                'delivery_method_id' => [],
                'statuses'           => [$status],
                'provider_id'        => [],
                'warehouse_id'       => [],
            ],
            'limit'    => 100,
            'cursor'   => '',
            'offset'   => 0,
            'translit' => false,
            'with'     => [
                'analytics_data' => true,
                'barcodes'       => true,
                'financial_data' => true,
                'legal_info'     => true,
            ],
        ];

        $allPostings = [];
        $guard       = 0;

        do {
            $response = $this->ozon->post('v4/posting/fbs/unfulfilled/list', $payload);

            if (!empty($response['postings']) && is_array($response['postings'])) {
                foreach ($response['postings'] as $posting) {
                    $allPostings[] = $posting;
                }
            }

            $cursor  = isset($response['cursor'])   ? (string) $response['cursor'] : '';
            $hasNext = !empty($response['has_next']);

            // Защита от зацикливания: если Ozon вернёт тот же cursor — выходим
            if ($guard > 0 && $cursor === $payload['cursor']) {
                error_log("OrderService::getUnfulfilledFbs — cursor не изменился, прерываем (status=$status, date=$dateQueryOzon)");
                break;
            }

            $payload['cursor'] = $cursor;

            if (!$hasNext) {
                break;
            }

            // до 500 страниц максимум
            if (++$guard > 500) {
                error_log("OrderService::getUnfulfilledFbs — превышен лимит страниц (status=$status, date=$dateQueryOzon)");
                break;
            }
        } while (true);

        return $allPostings;
    }







    /**
 * Формирует и скачивает PDF с этикетками FBS для набора отправлений.
 *
 * @param array  $postingNumbers  Массив номеров отправлений (строк)
 * @param string $pdfFileName     Имя PDF без расширения (например '2026-09-30')
 * @param string $pathEtiketki    Папка для сохранения PDF
 * @param int    $waitTimeEtikets Секунд ожидания перед запросом файла
 * @return string                 Имя сохранённого файла (с расширением .pdf)
 *
 * @throws \RuntimeException
 */
public function getPackageLabel(
    array $postingNumbers,
    string $pdfFileName,
    string $pathEtiketki,
    int $waitTimeEtikets = 10
): string {
    // --- 1. Создаём задание на формирование этикеток ---
    $create = $this->ozon->post('v3/posting/fbs/package-label/create', [
        'posting_numbers' => $postingNumbers,
    ], true); // throwOnError — хотим знать сразу, если Ozon не принял запрос

    // В v3 ответ плоский: { "tasks": [ { "task_id": ... } ] }
    $taskId = $create['tasks'][0]['task_id'] ?? null;

    if ($taskId === null) {
        throw new \RuntimeException(
            'OrderService::getPackageLabel — Ozon не вернул task_id. Ответ: '
            . json_encode($create, JSON_UNESCAPED_UNICODE)
        );
    }

    // --- 2. Ждём формирования (пока как в оригинале) ---
    sleep($waitTimeEtikets);

    // --- 3. Получаем ссылку на файл ---
    $get = $this->ozon->post('v2/posting/fbs/package-label/get', [
        'task_id' => $taskId,
    ], true);

    // В v2 ответ плоский: { "file_url": "..." }
    $fileUrl = $get['file_url'] ?? null;

    if ($fileUrl === null || $fileUrl === '') {
        throw new \RuntimeException(
            "OrderService::getPackageLabel — Ozon не вернул file_url (task_id=$taskId). Ответ: "
            . json_encode($get, JSON_UNESCAPED_UNICODE)
        );
    }

    // --- 4. Папка должна существовать ---
    if (!is_dir($pathEtiketki) && !mkdir($pathEtiketki, 0775, true) && !is_dir($pathEtiketki)) {
        throw new \RuntimeException("OrderService::getPackageLabel — не удалось создать папку: $pathEtiketki");
    }

    $fileName = $pdfFileName . '.pdf';
    $fullPath = rtrim($pathEtiketki, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    // --- 5. Качаем PDF потоком в файл ---
    $this->downloadToFile($fileUrl, $fullPath);

    return $fileName;
}

/**
 * Скачивает URL в файл потоком (не грузит всё в память).
 *
 * @throws \RuntimeException
 */
private function downloadToFile(string $url, string $destination): void
{
    $fp = fopen($destination, 'wb');
    if ($fp === false) {
        throw new \RuntimeException("Не удалось открыть файл на запись: $destination");
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE           => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    $ok        = curl_exec($ch);
    $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    fclose($fp);

    if ($ok === false || $httpCode < 200 || $httpCode >= 300) {
        if (is_file($destination)) {
            @unlink($destination);
        }
        throw new \RuntimeException(
            "OrderService::downloadToFile — не удалось скачать $url (HTTP $httpCode, curl: $curlError)"
        );
    }
}
}