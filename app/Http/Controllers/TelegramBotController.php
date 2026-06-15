<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramMessageJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function __construct() {}

    public function webhook(Request $request): JsonResponse
    {
        $update = $request->all();

        Log::info('Telegram webhook received', $update);

        $message = $update['message'] ?? null;

        if (!$message) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) ($message['chat']['id'] ?? '');
        $text   = trim($message['text'] ?? '');

        if (!$chatId) {
            return response()->json(['ok' => true]);
        }

        if ($text === '/start') {
            SendTelegramMessageJob::dispatch($chatId, 'Welcome', 'Please enter your Email');
            return response()->json(['ok' => true]);
        }

        if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $text)->first();

            if (!$user) {
                SendTelegramMessageJob::dispatch($chatId, 'Not Found', 'No account found with that email address. Please check and try again.');
                return response()->json(['ok' => true]);
            }

            $user->update(['telegram_chat_id' => $chatId]);

            SendTelegramMessageJob::dispatch(
                $chatId,
                'Account Linked',
                "Your Telegram account has been linked to {$user->email}. You will now receive domain notifications here."
            );

            Log::info("Telegram chat_id {$chatId} linked to user {$user->id} ({$user->email})");

            return response()->json(['ok' => true]);
        }

        SendTelegramMessageJob::dispatch($chatId, 'Help', 'Please send /start to begin or enter your email address to link your account.');

        return response()->json(['ok' => true]);
    }
}
