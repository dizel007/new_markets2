<?php

namespace App\Ozon\Service;

use App\Ozon\OzonClient;

class FinanceService
{
    private OzonClient $ozon;

    public function __construct(OzonClient $ozon)
    {
        $this->ozon = $ozon;
    }

    /**
     * Баланс по начислениям за период.
     *
     * @param string $dateFrom Например '2026-09-01'
     * @param string $dateTo   Например '2026-09-30'
     * @return array{viplati: float, cashflows: float, commision: float}
     */
    public function getBalance(string $dateFrom, string $dateTo): array
    {
        $data = $this->ozon->post('v1/finance/balance', [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]);

        $viplati   = $data['total']['accrued']['value'] ?? 0;
        $salesAmt  = $data['cashflows']['sales']['amount']['value'] ?? 0;
        $returnsAmt = $data['cashflows']['returns']['amount']['value'] ?? 0;
        $salesFee   = $data['cashflows']['sales']['fee']['value'] ?? 0;
        $returnsFee = $data['cashflows']['returns']['fee']['value'] ?? 0;

        return [
            'viplati'   => $viplati,
            'cashflows' => $salesAmt + $returnsAmt,
            'commision' => $salesFee + $returnsFee,
        ];
    }

    /**
     * Начисления по дням за период (v1/finance/accrual/by-day).
     * Данные забираются по дням, для каждого дня — постранично (last_id).
     *
     * @param string      $dateFrom    'Y-m-d'
     * @param string      $dateTo      'Y-m-d'
     * @param string|null $filePath    Если задан — сырые данные дополнительно пишутся в файл
     * @return array       Массив всех accruals за период
     */
    public function getAccrualsByDay(string $dateFrom, string $dateTo, ?string $filePath = null): array
    {
        $allAccruals = [];

        $current = strtotime($dateFrom);
        $endTs   = strtotime($dateTo);

        if ($current === false || $endTs === false) {
            throw new \InvalidArgumentException('Некорректная дата: ' . $dateFrom . ' / ' . $dateTo);
        }

        while ($current <= $endTs) {
            $queryDate = date('Y-m-d', $current);
            $lastId    = '';

            // Защита от бесконечного цикла, если Ozon вдруг вернёт last_id сам на себя
            $guard = 0;

            do {
                $data = $this->ozon->post('v1/finance/accrual/by-day', [
                    'date'    => $queryDate,
                    'last_id' => $lastId,
                ]);

                $lastId = isset($data['last_id']) ? (string) $data['last_id'] : '';

                if (!empty($data['accruals']) && is_array($data['accruals'])) {
                    foreach ($data['accruals'] as $row) {
                        $allAccruals[] = $row;
                    }
                }

                // если за 200 итераций не вышли — что-то не так, ломаемся
                if (++$guard > 200) {
                    error_log("FinanceService::getAccrualsByDay — возможное зацикливание на $queryDate, last_id=$lastId");
                    break;
                }
            } while ($lastId !== '');

            $current = strtotime('+1 day', $current);
        }

        // сырые данные пишем в файл, если путь задан
        if ($filePath !== null && $filePath !== '') {
            $json = json_encode($allAccruals, JSON_UNESCAPED_UNICODE);
            $ok   = @file_put_contents($filePath, $json);
            if ($ok === false) {
                error_log("FinanceService::getAccrualsByDay — не удалось записать файл: $filePath");
            }
        }

        return $allAccruals;
    }


    /**
     * Продажи товаров в страны ЕАЭС (buyout).
     *
     * Агрегирует «products» по sku: суммирует amount, quantity, seller_price_per_instance.
     *
     * @param string      $dateFrom
     * @param string      $dateTo
     * @param string|null $filePath  Если задан — сырой ответ Ozon пишется сюда
     * @return array{products: array, summa_prodannogo: float, date_from: string, date_to: string}
     */
    public function getBuyoutEaes(string $dateFrom, string $dateTo, ?string $filePath = null): array
    {
        $response = $this->ozon->post('v1/finance/products/buyout', [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ]);

        // Сохраняем сырой ответ (если нужно)
        if ($filePath !== null && $filePath !== '') {
            $json = json_encode($response, JSON_UNESCAPED_UNICODE);
            if (@file_put_contents($filePath, $json) === false) {
                error_log("FinanceService::getBuyoutEaes — не удалось записать файл: $filePath");
            }
        }

        $result = [
            'products'         => [],
            'summa_prodannogo' => 0,
            'date_from'        => $dateFrom,
            'date_to'          => $dateTo,
        ];

        $products = isset($response['products']) && is_array($response['products'])
            ? $response['products']
            : [];

        $summa = 0;

        foreach ($products as $item) {
            $sku = $item['sku'];

            if (!isset($result['products'][$sku])) {
                $result['products'][$sku] = [
                    'sku'                      => $sku,
                    'offer_id'                 => $item['offer_id'],
                    'amount'                   => 0,
                    'quantity'                 => 0,
                    'seller_price_per_instance' => 0,
                ];
            }

            $result['products'][$sku]['amount']                    += $item['amount'];
            $result['products'][$sku]['quantity']                  += $item['quantity'];
            $result['products'][$sku]['seller_price_per_instance'] += $item['seller_price_per_instance'];

            $summa += $item['amount'];
        }

        $result['summa_prodannogo'] = $summa;

        return $result;
    }
}
