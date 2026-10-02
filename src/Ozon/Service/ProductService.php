<?php

namespace App\Ozon\Service;

use App\Ozon\OzonClient;

class ProductService
{
    private OzonClient $ozon;

    public function __construct(OzonClient $ozon)
    {
        $this->ozon = $ozon;
    }

    /**
     * @return array Массив товаров, индексированный по offer_id
     */
    public function getSebestoimostTovarov(): array
    {
        // --- 1. Цены и product_id ---
        $prices = $this->ozon->post('v5/product/info/prices', [
            'cursor' => '',
            'filter' => [
                'visibility' => 'ALL',
            ],
            'limit'  => 1000,
        ]);

        if (!isset($prices['items'])) {
            sleep(2);
            $prices = $this->ozon->post('v5/product/info/prices', [
                'cursor' => '',
                'filter' => [
                    'visibility' => 'ALL',
                ],
                'limit'  => 1000,
            ]);
        }

        $arrArticleProducts = [];
        $arrIdProducts      = [];

        foreach ($prices['items'] ?? [] as $item) {
            $offerId = $item['offer_id'];

            $arrArticleProducts[$offerId] = [
                'article'      => $offerId,
                'product_id'   => $item['product_id'],
                'sebestoimost' => $item['price']['net_price'],
            ];

            $arrIdProducts[] = $item['product_id'];
        }

        if (!$arrIdProducts) {
            return [];
        }

        // --- 2. SKU и name по product_id ---
        $attributes = $this->ozon->post('v4/product/info/attributes', [
            'filter' => [
                'product_id' => $arrIdProducts,
                'visibility' => 'ALL',
            ],
            'limit'    => 1000,
            'sort_dir' => 'ASC',
        ]);

        foreach ($attributes['result'] ?? [] as $item) {
            $pid = $item['id'];

            foreach ($arrArticleProducts as &$tovar) {
                if ($pid === $tovar['product_id']) {
                    $tovar['sku']  = $item['sku']  ?? null;
                    $tovar['name'] = $item['name'] ?? null;
                    break;
                }
            }
            unset($tovar);
        }

        return $arrArticleProducts;
    }
}