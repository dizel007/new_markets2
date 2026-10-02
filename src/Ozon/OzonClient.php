<?php

namespace App\Ozon;

class OzonClient
{
    private const BASE_URL = 'https://api-seller.ozon.ru/';

    private string $token;
    private string $clientId;

    /** Максимум попыток при 429 / 5xx */
    private int $maxRetries;

    /** Базовая пауза в микросекундах (для backoff) */
    private int $baseDelayUs;

    public function __construct(string $token, string $clientId, int $maxRetries = 30, int $baseDelayUs = 200000)
    {
        $this->token       = $token;
        $this->clientId    = $clientId;
        $this->maxRetries  = $maxRetries;
        $this->baseDelayUs = $baseDelayUs; // 100 000 мкс = 0.1 сек
    }
/********************************************************************************************************** */
   /**
     * POST-запрос с автоматическим retry на 429/5xx.
     *
     * @param string $endpoint
     * @param array  $data
     * @param bool   $throwOnError  Бросать исключение при не-2xx (кроме retry-случаев)
     * @return array
     */
/********************************************************************************************************** */

    public function post(string $dop_ozon_url, array $data = [], bool $throwOnError = false): array
    {
        $dop_ozon_url = ltrim($dop_ozon_url, '/');
        $url      = self::BASE_URL . $dop_ozon_url;
        $body     = json_encode($data, JSON_UNESCAPED_UNICODE);

        $attempt = 0;

        while (true) {
            $attempt++;

            [$raw, $httpCode, $curlError] = $this->doRequest($url, $body);

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
            $msg = "Ozon API [$dop_ozon_url]: cURL error: $curlError";
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
            $msg = "Результат обмена ОЗОН__($dop_ozon_url): HTTP $httpCode. Ответ: $raw";
            error_log($msg);
            if ($throwOnError) {
                throw new \RuntimeException($msg);
            }
            echo $msg . PHP_EOL;
        }

        return $decoded;
    }

    /******************************************************************************************************************
     * Один вызов cURL. Возвращает [raw, httpCode, curlError].
     ******************************************************************************************************************/
    private function doRequest(string $url, string $body): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => [
                'Api-Key: ' . $this->token,
                'Client-Id: ' . $this->clientId,
                'Content-Type: application/json',
            ],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $raw       = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [$raw, $httpCode, $curlError];
    }
/*************************************************************************************************************** */
    private function isRetryable(int $httpCode): bool
    {
        return $httpCode === 429 || ($httpCode >= 500 && $httpCode < 600);
    }
}