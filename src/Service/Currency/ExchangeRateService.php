<?php

namespace App\Service\Currency;

use App\Helper\Env;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateService
{
    private HttpClientInterface $client;
    private const EXCHANGE_RATES = [
        'USD' => 1.1497,
        'JPY' => 129.53,
        'EUR' => 1,
    ];

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function convert(float $amount, string $fromCurrency, string $toCurrency, bool $default): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }
        $convertedAmount = 0;
        try {
            $response = $this->client->request('GET', Env::getenv('RATE_API_URL'), [
                'query' => [
                    'access_key' => Env::getenv('RATE_API_KEY'),
                    'base' => $fromCurrency,
                    'symbols' => $toCurrency,
                ],
            ]);

            $data = $response->toArray();
            $rate = $data['rates'][$toCurrency];
            $rate = !$default && !$rate ? self::EXCHANGE_RATES[$fromCurrency] : ($default && !$rate ? (self::EXCHANGE_RATES[$toCurrency] ?? 1.0) : $rate);
            $convertedAmount = !$default && !$rate ? $amount / $rate : ($default && !$rate ? $amount * $rate : $convertedAmount);

        } catch (\Exception $e) {
            error_log('Error fetching exchange rate: ' . $e->getMessage());
            $rate = !$default ? self::EXCHANGE_RATES[$fromCurrency] : (self::EXCHANGE_RATES[$toCurrency] ?? 1.0);
            $convertedAmount = !$default ? ($amount / $rate) : ($amount * $rate);
        }

        return ceil(round(($convertedAmount), 2));

    }
}