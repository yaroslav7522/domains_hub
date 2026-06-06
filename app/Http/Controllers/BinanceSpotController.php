<?php

namespace App\Http\Controllers;

use App\Contracts\BinanceSpotServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BinanceSpotController extends Controller
{
    public function __construct(private BinanceSpotServiceInterface $binance) {}


    // -------------------------------------------------------------------------
    // Market data
    // -------------------------------------------------------------------------

    /**
     * GET /api/binance/klines
     *
     * Query params:
     *   symbol   (required) e.g. BTCUSDT
     *   interval (required) e.g. 1h
     *   start    (optional) ISO-8601 or Unix ms
     *   end      (optional) ISO-8601 or Unix ms
     *   limit    (optional, default 500, max 1000)
     */
    public function klines(Request $request): JsonResponse
    {
        $request->validate([
            'symbol'   => 'required|string',
            'interval' => 'required|in:1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M',
            'start'    => 'nullable|string',
            'end'      => 'nullable|string',
            'limit'    => 'nullable|integer|min:1|max:1000',
        ]);

        $data = $this->binance->getKlines(
            symbol:    $request->input('symbol'),
            interval:  $request->input('interval'),
            startTime: $this->parseTimestamp($request->input('start')),
            endTime:   $this->parseTimestamp($request->input('end')),
            limit:     (int) $request->input('limit', 500),
        );

        return response()->json($data);
    }

    /**
     * GET /api/binance/ticker?symbol=BTCUSDT
     */
    public function ticker(Request $request): JsonResponse
    {
        $request->validate(['symbol' => 'required|string']);

        return response()->json(
            $this->binance->getTickerPrice($request->input('symbol'))
        );
    }

    // -------------------------------------------------------------------------
    // Account
    // -------------------------------------------------------------------------

    /**
     * GET /api/binance/account
     */
    public function account(): JsonResponse
    {
        return response()->json($this->binance->getAccount());
    }

    /**
     * GET /api/binance/orders/open?symbol=BTCUSDT
     */
    public function openOrders(Request $request): JsonResponse
    {
        return response()->json(
            $this->binance->getOpenOrders($request->input('symbol'))
        );
    }

    /**
     * GET /api/binance/order?symbol=BTCUSDT&order_id=123
     */
    public function getOrder(Request $request): JsonResponse
    {
        $request->validate([
            'symbol'          => 'required|string',
            'order_id'        => 'nullable|integer',
            'client_order_id' => 'nullable|string',
        ]);

        return response()->json(
            $this->binance->getOrder(
                $request->input('symbol'),
                $request->integer('order_id') ?: null,
                $request->input('client_order_id'),
            )
        );
    }

    // -------------------------------------------------------------------------
    // Orders
    // -------------------------------------------------------------------------

    /**
     * POST /api/binance/order/buy
     *
     * Body (JSON):
     *   symbol         (required)
     *   type           market|limit   (default: market)
     *   quantity       asset quantity (required for limit; optional for market)
     *   quote_qty      spend this much quote currency (market only, alternative to quantity)
     *   price          (required for limit)
     *   time_in_force  GTC|IOC|FOK    (limit only, default GTC)
     */
    public function buy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'symbol'        => 'required|string',
            'type'          => 'nullable|in:market,limit',
            'quantity'      => 'nullable|numeric|min:0',
            'quote_qty'     => 'nullable|numeric|min:0',
            'price'         => 'required_if:type,limit|nullable|numeric|min:0',
            'time_in_force' => 'nullable|in:GTC,IOC,FOK',
        ]);

        $type = strtolower($data['type'] ?? 'market');

        $result = $type === 'limit'
            ? $this->binance->limitBuy(
                $data['symbol'],
                (string) $data['quantity'],
                (string) $data['price'],
                $data['time_in_force'] ?? 'GTC',
            )
            : $this->binance->marketBuy(
                $data['symbol'],
                isset($data['quantity']) ? (string) $data['quantity'] : null,
                isset($data['quote_qty']) ? (string) $data['quote_qty'] : null,
            );

        return response()->json($result, 201);
    }

    /**
     * POST /api/binance/order/sell
     *
     * Body (JSON):
     *   symbol         (required)
     *   type           market|limit   (default: market)
     *   quantity       (required)
     *   price          (required for limit)
     *   time_in_force  GTC|IOC|FOK    (limit only, default GTC)
     */
    public function sell(Request $request): JsonResponse
    {
        $data = $request->validate([
            'symbol'        => 'required|string',
            'type'          => 'nullable|in:market,limit',
            'quantity'      => 'required|numeric|min:0',
            'price'         => 'required_if:type,limit|nullable|numeric|min:0',
            'time_in_force' => 'nullable|in:GTC,IOC,FOK',
        ]);

        $type = strtolower($data['type'] ?? 'market');

        $result = $type === 'limit'
            ? $this->binance->limitSell(
                $data['symbol'],
                (string) $data['quantity'],
                (string) $data['price'],
                $data['time_in_force'] ?? 'GTC',
            )
            : $this->binance->marketSell(
                $data['symbol'],
                (string) $data['quantity'],
            );

        return response()->json($result, 201);
    }

    /**
     * DELETE /api/binance/order
     *
     * Body (JSON):
     *   symbol          (required)
     *   order_id        (required unless client_order_id supplied)
     *   client_order_id (optional)
     */
    public function cancelOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'symbol'          => 'required|string',
            'order_id'        => 'nullable|integer',
            'client_order_id' => 'nullable|string',
        ]);

        return response()->json(
            $this->binance->cancelOrder(
                $data['symbol'],
                $data['order_id'] ?? null,
                $data['client_order_id'] ?? null,
            )
        );
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function parseTimestamp(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        // Already Unix ms
        if (ctype_digit($value)) {
            return (int) $value;
        }

        // ISO-8601 or any strtotime-parseable string
        $ts = strtotime($value);

        return $ts !== false ? $ts * 1000 : null;
    }
}
