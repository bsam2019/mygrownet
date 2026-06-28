<?php

namespace App\Http\Controllers;

use Telegram\Bot\Laravel\Facades\Telegram;
use App\Application\UseCases\Notification\LinkTelegramAccountUseCase;
use App\Models\User;
use Illuminate\Http\Request;

class TelegramBotController extends Controller
{
    public function __construct(
        private LinkTelegramAccountUseCase $linkAccountUseCase
    ) {}

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        if ($update->getMessage()) {
            $message = $update->getMessage();
            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Handle /start command
            if (str_starts_with($text, '/start')) {
                $this->handleStart($chatId, $text);
            }
            // Handle /status command
            elseif ($text === '/status') {
                $this->handleStatus($chatId);
            }
            // Handle /balance command
            elseif ($text === '/balance') {
                $this->handleBalance($chatId);
            }
            // Handle /help command
            elseif ($text === '/help') {
                $this->handleHelp($chatId);
            }
            // Handle link code
            elseif (preg_match('/^[A-Z0-9]{8}$/', $text)) {
                $this->handleLinkCode($chatId, $text);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleStart($chatId, $text)
    {
        // Check if link code provided: /start LINKCODE
        $parts = explode(' ', $text);
        if (count($parts) === 2) {
            $this->handleLinkCode($chatId, $parts[1]);
            return;
        }

        $message = "👋 *Welcome to MyGrowNet Bot!*\n\n";
        $message .= "To link your account:\n";
        $message .= "1. Go to your MyGrowNet dashboard\n";
        $message .= "2. Navigate to Settings → Notifications\n";
        $message .= "3. Click 'Link Telegram'\n";
        $message .= "4. Send the code here\n\n";
        $message .= "Or use: /help for more commands";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleLinkCode($chatId, $code)
    {
        $user = $this->linkAccountUseCase->linkAccount((string)$chatId, $code);

        if ($user) {
            $message = "✅ *Account Linked Successfully!*\n\n";
            $message .= "Welcome, {$user->name}!\n\n";
            $message .= "You'll now receive instant notifications for:\n";
            $message .= "• Payment confirmations\n";
            $message .= "• Level upgrades\n";
            $message .= "• Commission earnings\n";
            $message .= "• Withdrawal updates\n\n";
            $message .= "Use /status to check your account anytime.";
        } else {
            $message = "❌ *Invalid Link Code*\n\n";
            $message .= "Please get a new code from your dashboard.";
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleStatus($chatId)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Account not linked. Use /start to link your account."
            ]);
            return;
        }

        $message = "📊 *Your Account Status*\n\n";
        $message .= "Name: {$user->name}\n";
        $message .= "Level: {$user->professional_level}\n";
        $message .= "Points: " . number_format($user->lifetime_points ?? 0) . " LP\n";
        $message .= "Network: " . ($user->network_size ?? 0) . " members\n\n";
        $message .= "Dashboard: " . route('dashboard');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleBalance($chatId)
    {
        $user = User::where('telegram_chat_id', (string)$chatId)->first();

        if (!$user) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Account not linked. Use /start to link your account."
            ]);
            return;
        }

        $message = "💰 *Your Wallet Balance*\n\n";
        $message .= "Available: K" . number_format($user->wallet_balance ?? 0, 2) . "\n";
        $message .= "Pending: K" . number_format($user->pending_balance ?? 0, 2) . "\n\n";
        $message .= "View details: " . route('wallet.index');

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleHelp($chatId)
    {
        $message = "📖 *MyGrowNet Bot Commands*\n\n";
        $message .= "/start - Link your account\n";
        $message .= "/status - Check account status\n";
        $message .= "/balance - View wallet balance\n";
        $message .= "/help - Show this help message\n\n";
        $message .= "Need support? Contact us through the app.";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}
