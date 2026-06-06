<?php

namespace App\Contracts;

interface BinanceSpotServiceInterface
{
    public function getKlines(
        string $symbol,
        string $interval,
        ?int $startTime = null,
        ?int $endTime   = null,
        int $limit      = 500
    ): array;

    public function getTickerPrice(string $symbol): array;

    public function getAccount(): array;

    public function getOpenOrders(?string $symbol = null): array;

    public function getOrder(string $symbol, ?int $orderId = null, ?string $clientOrderId = null): array;

    public function marketBuy(string $symbol, ?string $quantity = null, ?string $quoteOrderQty = null): array;

    public function limitBuy(string $symbol, string $quantity, string $price, string $timeInForce = 'GTC'): array;

    public function marketSell(string $symbol, string $quantity): array;

    public function limitSell(string $symbol, string $quantity, string $price, string $timeInForce = 'GTC'): array;

    public function cancelOrder(string $symbol, ?int $orderId = null, ?string $clientOrderId = null): array;
}
