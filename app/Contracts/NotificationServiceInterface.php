<?php

namespace App\Contracts;

interface NotificationServiceInterface
{
    public function send(string $recipient, string $subject, string $message): void;
}
