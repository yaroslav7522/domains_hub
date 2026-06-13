<?php

namespace App\Services;

use App\Models\CheckHistory;
use App\Models\Domain;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainCheckService
{
    public function check(Domain $domain): CheckHistory
    {
        $url = 'https://' . $domain->domain;
        $timeout = $domain->request_timeout ?? 30;
        $method = strtolower($domain->check_method ?? 'get');

        $start = hrtime(true);

        try {
            $response = Http::timeout($timeout)->{$method}($url);

            $responseTimeMs = (int) ((hrtime(true) - $start) / 1_000_000);
            $httpCode = $response->status();
            $isUp = $response->successful() || $response->redirect();

            return $domain->checkHistories()->create([
                'status'          => $isUp ? 'up' : 'down',
                'http_code'       => $httpCode,
                'error'           => $isUp ? null : "HTTP {$httpCode}",
                'response_time_ms' => $responseTimeMs,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $responseTimeMs = (int) ((hrtime(true) - $start) / 1_000_000);

            Log::warning("Domain check failed [{$domain->domain}]: {$e->getMessage()}");

            return $domain->checkHistories()->create([
                'status'          => 'down',
                'http_code'       => null,
                'error'           => $e->getMessage(),
                'response_time_ms' => $responseTimeMs,
            ]);
        }
    }
}
