<?php

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService implements NotificationServiceInterface
{
    public function send(string $recipient, string $subject, string $message): void
    {
        Mail::raw($message, function ($mail) use ($recipient, $subject) {
            $mail->to($recipient)->subject($subject);
        });

        Log::info("Email notification sent to [{$recipient}]: {$subject}");
    }
}
