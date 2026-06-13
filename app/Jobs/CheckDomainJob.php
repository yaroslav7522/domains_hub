<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Services\DomainCheckService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckDomainJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly Domain $domain) {}

    public function handle(DomainCheckService $service): void
    {
        $service->check($this->domain);
    }
}
