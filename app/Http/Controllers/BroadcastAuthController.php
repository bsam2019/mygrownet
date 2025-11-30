<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use App\Models\InvestorAccount;
use App\Infrastructure\Persistence\Eloquent\Support\SupportTicketModel;

class BroadcastAuthController extends Controller
{
    /**
     * Custom broadcasting auth that handles both Laravel auth and session-based investor auth
     */
    public function authenticate(Request $request)
    {
        $channelName = $request->input('channel_name');
        
        \Log::info('Custom broadcast auth', [
            'channel' => $channelName,
            'has_user' => auth()->check(),
            'investor_id' => session('investor_id'),
        ]);
        
        // Handle investor support channels with session-based auth
        if (preg_match('/^private-investor\.support\.(\d+)$/', $channelName, $matches)) {
            $ticketId = (int) $matches[1];
            return $this->authorizeInvestorChannel($request, $ticketId);
        }
        
        // For all other channels, use default Laravel broadcasting auth
        if (auth()->check()) {
            return Broadcast::auth($request);
        }
        
        // No auth available
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    /**
     * Authorize investor support channel
     */
    private function authorizeInvestorChannel(Request $request, int $ticketId)
    {
        // Check session-based investor auth
        $investorId = session('investor_id');
        
        if ($investorId) {
            $investor = InvestorAccount::find($investorId);
            if (!$investor) {
                \Log::warning('Broadcast auth: Investor not found', ['investor_id' => $investorId]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $ticket = SupportTicketModel::find($ticketId);
            if (!$ticket) {
                \Log::warning('Broadcast auth: Ticket not found', ['ticket_id' => $ticketId]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            // Check ownership
            $isOwner = $ticket->investor_account_id === $investor->id ||
                       ($investor->user_id && $ticket->user_id === $investor->user_id && $ticket->source === 'investor');
            
            if ($isOwner) {
                \Log::info('Broadcast auth: Investor authorized for channel', [
                    'ticket_id' => $ticketId,
                    'investor_id' => $investor->id,
                ]);
                
                // Return Pusher/Reverb compatible auth response
                return $this->generateAuthResponse($request, [
                    'id' => $investor->id,
                    'name' => $investor->name,
                    'type' => 'investor',
                ]);
            }
        }
        
        // Check Laravel auth for support staff
        if (auth()->check()) {
            $user = auth()->user();
            $isSupport = $user->hasRole('support') || 
                         $user->hasRole('admin') || 
                         $user->hasRole('Admin') ||
                         $user->hasRole('Administrator') || 
                         $user->is_admin;
            
            if ($isSupport) {
                \Log::info('Broadcast auth: Support staff authorized for investor channel', [
                    'ticket_id' => $ticketId,
                    'user_id' => $user->id,
                ]);
                
                return $this->generateAuthResponse($request, [
                    'id' => $user->id,
                    'name' => $user->name,
                    'type' => 'support',
                ]);
            }
        }
        
        \Log::warning('Broadcast auth: Access denied to investor channel', [
            'ticket_id' => $ticketId,
            'investor_id' => $investorId,
        ]);
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    
    /**
     * Generate Pusher/Reverb compatible auth response
     */
    private function generateAuthResponse(Request $request, array $userData)
    {
        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');
        
        // For Reverb, get key and secret from reverb config
        $key = config('reverb.apps.apps.0.key') ?? env('REVERB_APP_KEY');
        $secret = config('reverb.apps.apps.0.secret') ?? env('REVERB_APP_SECRET');
        
        if (!$key || !$secret) {
            // Fallback to pusher config
            $key = config('broadcasting.connections.pusher.key');
            $secret = config('broadcasting.connections.pusher.secret');
        }
        
        \Log::debug('Broadcast auth generating response', [
            'channel' => $channelName,
            'socket_id' => $socketId,
            'has_key' => !empty($key),
            'has_secret' => !empty($secret),
        ]);
        
        // Create signature for presence channel
        $stringToSign = $socketId . ':' . $channelName;
        
        // For presence channels, include user data
        if (str_starts_with($channelName, 'presence-')) {
            $userDataJson = json_encode(['user_id' => $userData['id'], 'user_info' => $userData]);
            $stringToSign .= ':' . $userDataJson;
            $signature = hash_hmac('sha256', $stringToSign, $secret);
            
            return response()->json([
                'auth' => $key . ':' . $signature,
                'channel_data' => $userDataJson,
            ]);
        }
        
        // For private channels
        $signature = hash_hmac('sha256', $stringToSign, $secret);
        
        return response()->json([
            'auth' => $key . ':' . $signature,
        ]);
    }
}
