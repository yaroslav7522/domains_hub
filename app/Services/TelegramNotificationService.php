<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService implements NotificationServiceInterface
{
    private string $token;
    private string $apiUrl = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
    }

    public function send(string $recipient, string $subject, string $message): void
    {
        $text = "*{$subject}*\n\n{$message}";

        Http::post("{$this->apiUrl}{$this->token}/sendMessage", [
            'chat_id'    => $recipient,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ])->throw();

        Log::info("Telegram notification sent to chat [{$recipient}]: {$subject}");
    }
}
