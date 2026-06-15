<?php

namespace App\Jobs;

use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly string $chatId,
        public readonly string $subject,
        public readonly string $message,
    ) {}

    public function handle(TelegramNotificationService $telegram): void
    {
        $telegram->send($this->chatId, $this->subject, $this->message);
    }
}
