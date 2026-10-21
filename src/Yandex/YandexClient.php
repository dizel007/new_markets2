<?php

namespace App\Yandex;

class YandexClient
{

private const BASE_YANDEX_API_URL = 'https://api.partner.market.yandex.ru/';
private string $ya_token;
private string $campaignId;
private string $ya_business_id;

private int $maxRetries;
private int $baseDelayUs;

public function __construct(string $ya_token, string $campaignId, int $maxRetries = 30, int $baseDelayUs = 300000)
{
    $this->ya_token = $ya_token;
    $this->campaignId = $campaignId;
    $this->maxRetries = $maxRetries;
    $this->baseDelayUs = $baseDelayUs;

}



    public function yandex_post(string $dop_ya_url, array $data = [], bool $throwOnError = false): array
    {
        $dop_ozon_url = ltrim($dop_ya_url, '/');
        $url      = self::BASE_YANDEX_API_URL . $dop_ozon_url;
        $body     = json_encode($data, JSON_UNESCAPED_UNICODE);

        $attempt = 0;

        while (true) {
            $attempt++;
    if (empty($data)) {

         [$raw, $httpCode, $curlError] = $this->doRequest_without_data($url);
    } else {
         [$raw, $httpCode, $curlError] = $this->doRequest($url, $body);
    }

            // 429 или 5xx — повторяем
            if ($this->isRetryable($httpCode) && $attempt <= $this->maxRetries) {
                $delay = $this->baseDelayUs ; // экспоненциальный backoff
                error_log(sprintf(
                    'Ozon API [%s]: HTTP %d, повтор через %d мс (попытка %d/%d)',
                    $dop_ozon_url,
                    $httpCode,
                    (int) round($delay / 1000),
                    $attempt,
                    $this->maxRetries
                ));
                usleep($delay);
                continue;
            }

            break;
        }

        if ($raw === false) {
            $msg = "Yandex API [$dop_ozon_url]: cURL error: $curlError";
            if ($throwOnError) {
                throw new \RuntimeException($msg);
            }
            error_log($msg);
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        if (intdiv($httpCode, 100) !== 2) {
            $msg = "Результат обмена Яндекс : ($dop_ozon_url): HTTP $httpCode. Ответ: $raw";
            error_log($msg);
            if ($throwOnError) {
                throw new \RuntimeException($msg);
            }
            // echo $msg . PHP_EOL;
        }

        return $decoded;
    }   

    /******************************************************************************************************************
     * Один вызов cURL. Возвращает [raw, httpCode, curlError].
     ******************************************************************************************************************/
    private function doRequest_without_data(string $url): array
    {
        $ch = curl_init($url);
             curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->ya_token,
                'Content-Type:application/json'
            ));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);


        $raw       = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [$raw, $httpCode, $curlError];
    }

    /******************************************************************************************************************
     * Один вызов cURL. Возвращает [raw, httpCode, curlError].
     ******************************************************************************************************************/
    private function doRequest(string $url, string $body): array
    {

    	$ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->ya_token,
                'Content-Type:application/json'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);


        $raw       = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        print_r($curlError);

        return [$raw, $httpCode, $curlError];
    }
/*************************************************************************************************************** */
    private function isRetryable(int $httpCode): bool
    {
        return $httpCode === 429 || ($httpCode >= 500 && $httpCode < 600);
    }



    /******************************************************************************************************************
     * PUT - запрос на изменения данных в Яндексе
     ******************************************************************************************************************/

    
    public function yandex_put (string $dop_ya_url, array $data, bool $throwOnError = false): array
    {
        $dop_ozon_url = ltrim($dop_ya_url, '/');
        $url      = self::BASE_YANDEX_API_URL . $dop_ozon_url;
        $body     = json_encode($data, JSON_UNESCAPED_UNICODE);

        $attempt = 0;

        while (true) {
            $attempt++;

         [$raw, $httpCode, $curlError] = $this->doPutRequest($url, $body);
    
            // 429 или 5xx — повторяем
            if ($this->isRetryable($httpCode) && $attempt <= $this->maxRetries) {
                $delay = $this->baseDelayUs ; // экспоненциальный backoff
                error_log(sprintf(
                    'Ozon API [%s]: HTTP %d, повтор через %d мс (попытка %d/%d)',
                    $dop_ozon_url,
                    $httpCode,
                    (int) round($delay / 1000),
                    $attempt,
                    $this->maxRetries
                ));
                usleep($delay);
                continue;
            }

            break;
        }

        if ($raw === false) {
            $msg = "Yandex API [$dop_ozon_url]: cURL error: $curlError";
            if ($throwOnError) {
                throw new \RuntimeException($msg);
            }
            error_log($msg);
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        if (intdiv($httpCode, 100) !== 2) {
            $msg = "Результат обмена Яндекс : ($dop_ozon_url): HTTP $httpCode. Ответ: $raw";
            error_log($msg);
            if ($throwOnError) {
                throw new \RuntimeException($msg);
            }
            // echo $msg . PHP_EOL;
        }

        return $decoded;
    }  


        private function doPutRequest(string $url, string $body): array
    {

    	$ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$this->ya_token,
                'Content-Type:application/json'
            ));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);


        $raw       = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        print_r($curlError);

        return [$raw, $httpCode, $curlError];
    }

}