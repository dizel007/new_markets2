<?php

namespace App\Yandex\Service;

use App\Yandex\YandexClient;

class YandexOrderService
{
    private YandexClient $yandex;
    private string $campaignId;
    private string $businessId;

    public function __construct(YandexClient $yandex,  string $campaignId, string $businessId)
    {
        $this->yandex     = $yandex;
        $this->campaignId = $campaignId;
        $this->businessId = $businessId;
    }

    /**
     * FBS-отправления, ожидающие обработки (awaiting_packaging / awaiting_deliver).
     *
     * @param string $dateQueryYandex  Начало периода 'Y-m-d' (фильтр по cutoff_from)
     * @param string $status         'awaiting_packaging' | 'awaiting_deliver'
     * @param int    $dopDaysQuery   На сколько дней вперёд от $dateQueryOzon смотреть (cutoff_to)
     * @return array                 Плоский массив postings
     */
    public function YandexgetUnfulfilledFby(string $dateQueryYandex, string $status, string $substatus, int $dopDaysQuery = 30): array
    {
        $startTs = strtotime($dateQueryYandex);
        if ($startTs === false) {
            throw new \InvalidArgumentException("Некорректная дата начала: $dateQueryYandex");
        }

        $dateQueryEnd = date('Y-m-d', strtotime("-$dopDaysQuery day", $startTs));

// echo  $dateQueryEnd ;
        // die();
        $data =  array(
            //   "orderIds" => [0],
            //   "externalOrderIds" => ["example"],
            //   "programTypes" => ["FBY"],
            "campaignIds" => [$this->campaignId],
            "statuses" => [$status],
            "substatuses" => [$substatus],
            "dates" => array(
                "creationDateFrom" => "$dateQueryEnd",
                // "creationDateTo"=> "2026-10-18",
                // "shipmentDateFrom"=> "2026-10-01",
                // "shipmentDateTo"=> "2026-10-25",
                // "updateDateFrom"=> "2026-10-18T00:00:00Z",
                // "updateDateTo"=> "2026-10-18T00:00:00Z"
            ),
            "fake" => false,
            //   "waitingForCancellationApprove"=> true,
            //   "sourcePlatforms"=> ["MARKET"]
        );

        $allPostings = [];
        $result =[];
        $countItemsQuery = 50;

    // print_r($data);
do {

            if (isset($allPostings['paging']['nextPageToken'])) {
                            $ya_link = 'v1/businesses/' . $this->businessId . '/orders/?limit='.$countItemsQuery.'&pageToken=' . $allPostings['paging']['nextPageToken'];
            } else {
                $ya_link = 'v1/businesses/' . $this->businessId . '/orders?limit='.$countItemsQuery;
                // echo "<br>$ya_link<br>";
            }

            $allPostings = $this->yandex->yandex_post($ya_link, $data);
       foreach ($allPostings['orders'] as $order) {
               $result['orders'][] = $order;
           }

} while (isset($allPostings['paging']['nextPageToken']));


        return $result;
    }
}
