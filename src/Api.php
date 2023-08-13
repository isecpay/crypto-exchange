<?php
/**
 * @copyright     Copyright (c) 2023 iSecPay (https://iSecPay.co)
 * @author        iSecPay <php@isecpay.co>
 * @created       01.07.2023
 * @license       MIT
 */

namespace iSecPay\CryptoExchange;

use Exception;
use Throwable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

define('ISPCE_PLUGIN_ROOT', dirname(__DIR__));

class Api
{
    public static string $API_ENDPOINT = 'https://api.isecpay.co/';
    public static string $CACHE_FILE_PATH =  ISPCE_PLUGIN_ROOT . '/var/cache/sys/crypto-exchange-{currency_code}.json';

    protected Client $client;
    protected ?string $key = '';

    public function __construct(?string $key = '')
    {
        $this->client = new Client();
        $this->key = (empty($key)
            ? (empty($_ENV['ISECPAY_CRYPTO_EXCHANGE_API_KEY'])
                ? ''
                : $_ENV['ISECPAY_CRYPTO_EXCHANGE_API_KEY']
            )
            : $key
        );
    }

    /**
     * @throws Exception
     */
    public function loadRates(string $currency, ?bool $ignoreExceptions = true): array
    {
        try {
            try {
                $response = $this->client->get(
                    self::$API_ENDPOINT . '?currency=' . $currency
                    . '&v=' . (string)time()
                    . (empty($this->key) ? '' : '&key=' . $this->key)
                );
            } catch (GuzzleException $e) {
                throw new Exception('Could not load rates: ' . $e->getMessage());
            }

            if (
                empty($response)
                || empty($body = $response->getBody())
                || empty($contents = $body->getContents())
                || empty($responseData = json_decode($contents, true))
                || empty($responseData['data'])
                || empty($data = $responseData['data'])
                || empty($rates = $data['rates'])
            ) {
                throw new Exception('Could not load rates');
            }
            file_put_contents($this->getCacheFilePath($currency), json_encode($responseData));
            return $rates;
        } catch (Throwable $exception) {
            if (empty($ignoreExceptions)) {
                throw new Exception($exception->getMessage());
            }
            $cachedData = json_decode(file_get_contents($this->getCacheFilePath($currency)), true);
            return (empty($cachedData) || empty($cachedData['data']) || empty($cachedData['data']['rates'])) ? [] : $cachedData['data']['rates'];
        }
    }

    /**
     * @throws Exception
     */
    public function getRate(string $from, string $to): float
    {
        $rates = $this->loadRates($from);

        if (
            empty($rates)
            || empty($rates[$to])
        ) {
            throw new Exception('Invalid currency');
        }

        return floatval($rates[$to]);
    }

    /**
     * @throws Exception
     */
    public function convert(string $from, string $to, float $amount, ?bool $fromCrypto = false): float
    {
        return empty($fromCrypto) ? $amount / $this->getRate($from, $to) : $amount * $this->getRate($to, $from);
    }

    private function getCacheFilePath(string $currency): string
    {
        return str_replace('{currency_code}', strtolower($currency), self::$CACHE_FILE_PATH);
    }
}
