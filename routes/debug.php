<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

// Debug route to check user statuses and payments
Route::get('/debug/user-statuses', function() {
    $users = User::with('memberPayments')->get();
    
    $data = [];
    foreach ($users as $user) {
        $data[] = [
            'id' => $user->id,
            'name' => $user->name,
            'status' => $user->status,
            'referrer_id' => $user->referrer_id,
            'referrer_name' => $user->referrer?->name,
            'payment_count' => $user->memberPayments->count(),
            'verified_payments' => $user->memberPayments->where('status', 'verified')->count(),
            'pending_payments' => $user->memberPayments->where('status', 'pending')->count(),
            'has_active_subscription' => $user->hasActiveSubscription(),
            'payments' => $user->memberPayments->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'status' => $payment->status,
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        ];
    }
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');