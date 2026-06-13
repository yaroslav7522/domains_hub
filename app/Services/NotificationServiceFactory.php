<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use InvalidArgumentException;

class NotificationServiceFactory
{
    private array $resolved = [];

    public function make(string $channel): NotificationServiceInterface
    {
        if (isset($this->resolved[$channel])) {
            return $this->resolved[$channel];
        }

        $this->resolved[$channel] = match ($channel) {
            'email'    => app(EmailNotificationService::class),
            'telegram' => app(TelegramNotificationService::class),
            default    => throw new InvalidArgumentException("Unknown notification channel: [{$channel}]"),
        };

        return $this->resolved[$channel];
    }
}
