<?php

namespace App\Services;

use App\Contracts\BinanceSpotServiceInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BinanceSpotService implements BinanceSpotServiceInterface
{
    private string $baseUrl = 'https://api.binance.com';
    private string $apiKey;
    private string $apiSecret;

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey   = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    // -------------------------------------------------------------------------
    // Public market data (no auth)
    // -------------------------------------------------------------------------

    /**
     * Get candlestick (kline) history for charting.
     *
     * @param  string   $symbol    e.g. "BTCUSDT"
     * @param  string   $interval  1m | 3m | 5m | 15m | 30m | 1h | 2h | 4h | 6h | 8h | 12h | 1d | 3d | 1w | 1M
     * @param  int|null $startTime Unix ms
     * @param  int|null $endTime   Unix ms
     * @param  int      $limit     Max 1000, default 500
     * @return array    Each element: [openTime, open, high, low, close, volume, closeTime, quoteVolume, trades, ...]
     */
    public function getKlines(
        string $symbol,
        string $interval,
        ?int $startTime = null,
        ?int $endTime   = null,
        int $limit      = 500
    ): array {
        $params = array_filter([
            'symbol'    => strtoupper($symbol),
            'interval'  => $interval,
            'startTime' => $startTime,
            'endTime'   => $endTime,
            'limit'     => min($limit, 1000),
        ], fn($v) => $v !== null);

        $response = Http::get("{$this->baseUrl}/api/v3/klines", $params);
        $this->assertOk($response);

        return array_map(fn(array $k) => [
            'open_time'    => $k[0],
            'open'         => $k[1],
            'high'         => $k[2],
            'low'          => $k[3],
            'close'        => $k[4],
            'volume'       => $k[5],
            'close_time'   => $k[6],
            'quote_volume' => $k[7],
            'trades'       => $k[8],
        ], $response->json());
    }

    /**
     * Get current best bid/ask price for a symbol.
     */
    public function getTickerPrice(string $symbol): array
    {
        $response = Http::get("{$this->baseUrl}/api/v3/ticker/price", [
            'symbol' => strtoupper($symbol),
        ]);
        $this->assertOk($response);

        return $response->json();
    }

    // -------------------------------------------------------------------------
    // Account (signed)
    // -------------------------------------------------------------------------

    /**
     * Get spot account balances and permissions.
     */
    public function getAccount(): array
    {
        $response = $this->signedGet('/api/v3/account');

        return $response->json();
    }

    /**
     * Get all open orders, optionally filtered by symbol.
     */
    public function getOpenOrders(?string $symbol = null): array
    {
        $params = array_filter(['symbol' => $symbol ? strtoupper($symbol) : null]);
        $response = $this->signedGet('/api/v3/openOrders', $params);

        return $response->json();
    }

    /**
     * Query a specific order by orderId or clientOrderId.
     */
    public function getOrder(string $symbol, ?int $orderId = null, ?string $clientOrderId = null): array
    {
        $params = array_filter([
            'symbol'            => strtoupper($symbol),
            'orderId'           => $orderId,
            'origClientOrderId' => $clientOrderId,
        ], fn($v) => $v !== null);

        $response = $this->signedGet('/api/v3/order', $params);

        return $response->json();
    }

    // -------------------------------------------------------------------------
    // Orders (signed)
    // -------------------------------------------------------------------------

    /**
     * Place a MARKET buy order.
     *
     * @param  string     $symbol   e.g. "BTCUSDT"
     * @param  string     $quantity Asset quantity to buy
     * @param  string|null $quoteOrderQty Spend this much quote currency instead of a fixed quantity
     */
    public function marketBuy(string $symbol, ?string $quantity = null, ?string $quoteOrderQty = null): array
    {
        return $this->placeOrder([
            'symbol' => strtoupper($symbol),
            'side'   => 'BUY',
            'type'   => 'MARKET',
        ], $quantity, null, $quoteOrderQty);
    }

    /**
     * Place a LIMIT buy order (GTC by default).
     */
    public function limitBuy(string $symbol, string $quantity, string $price, string $timeInForce = 'GTC'): array
    {
        return $this->placeOrder([
            'symbol'      => strtoupper($symbol),
            'side'        => 'BUY',
            'type'        => 'LIMIT',
            'timeInForce' => $timeInForce,
            'price'       => $price,
        ], $quantity);
    }

    /**
     * Place a MARKET sell order.
     */
    public function marketSell(string $symbol, string $quantity): array
    {
        return $this->placeOrder([
            'symbol' => strtoupper($symbol),
            'side'   => 'SELL',
            'type'   => 'MARKET',
        ], $quantity);
    }

    /**
     * Place a LIMIT sell order (GTC by default).
     */
    public function limitSell(string $symbol, string $quantity, string $price, string $timeInForce = 'GTC'): array
    {
        return $this->placeOrder([
            'symbol'      => strtoupper($symbol),
            'side'        => 'SELL',
            'type'        => 'LIMIT',
            'timeInForce' => $timeInForce,
            'price'       => $price,
        ], $quantity);
    }

    /**
     * Cancel an open order.
     */
    public function cancelOrder(string $symbol, ?int $orderId = null, ?string $clientOrderId = null): array
    {
        $params = array_filter([
            'symbol'            => strtoupper($symbol),
            'orderId'           => $orderId,
            'origClientOrderId' => $clientOrderId,
        ], fn($v) => $v !== null);

        $response = $this->signedDelete('/api/v3/order', $params);

        return $response->json();
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function placeOrder(
        array $base,
        ?string $quantity      = null,
        ?string $price         = null,
        ?string $quoteOrderQty = null
    ): array {
        $params = array_merge($base, array_filter([
            'quantity'      => $quantity,
            'quoteOrderQty' => $quoteOrderQty,
        ], fn($v) => $v !== null));

        $response = $this->signedPost('/api/v3/order', $params);
        $this->assertOk($response);

        return $response->json();
    }

    private function signedGet(string $path, array $params = []): Response
    {
        $params = $this->withTimestamp($params);
        $params['signature'] = $this->sign(http_build_query($params));

        $response = Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->get("{$this->baseUrl}{$path}", $params);

        $this->assertOk($response);

        return $response;
    }

    private function signedPost(string $path, array $params = []): Response
    {
        $params = $this->withTimestamp($params);
        $params['signature'] = $this->sign(http_build_query($params));

        $response = Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->asForm()
            ->post("{$this->baseUrl}{$path}", $params);

        $this->assertOk($response);

        return $response;
    }

    private function signedDelete(string $path, array $params = []): Response
    {
        $params = $this->withTimestamp($params);
        $params['signature'] = $this->sign(http_build_query($params));

        $response = Http::withHeaders(['X-MBX-APIKEY' => $this->apiKey])
            ->delete("{$this->baseUrl}{$path}", $params);

        $this->assertOk($response);

        return $response;
    }

    private function withTimestamp(array $params): array
    {
        $params['timestamp'] = (int) round(microtime(true) * 1000);
        $params['recvWindow'] = 5000;

        return $params;
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->apiSecret);
    }

    private function assertOk(Response $response): void
    {
        if ($response->failed()) {
            $body = $response->json();
            $msg  = $body['msg'] ?? $response->body();
            $code = $body['code'] ?? $response->status();

            throw new \RuntimeException("Binance API error [{$code}]: {$msg}");
        }
    }
}
