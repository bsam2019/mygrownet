<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Device Registration Controller
 * 
 * Handles FCM token registration for push notifications
 */
class DeviceController extends Controller
{
    /**
     * Register a device for push notifications
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'platform' => 'required|string|in:web,android,ios',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Upsert device token
            DB::table('user_devices')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'token' => $request->token,
                ],
                [
                    'platform' => $request->platform,
                    'is_active' => true,
                    'last_used_at' => now(),
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );

            Log::info('Device registered for push notifications', [
                'user_id' => $user->id,
                'platform' => $request->platform,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device registered successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to register device', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to register device',
            ], 500);
        }
    }

    /**
     * Unregister a device (logout/disable notifications)
     */
    public function unregister(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            DB::table('user_devices')
                ->where('user_id', $user->id)
                ->where('token', $request->token)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Device unregistered successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unregister device', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to unregister device',
            ], 500);
        }
    }
}
