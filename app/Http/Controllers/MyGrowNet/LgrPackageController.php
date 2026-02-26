<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Models\LgrPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LgrPackageController extends Controller
{
    /**
     * Display available packages for purchase/upgrade
     */
    public function index(): Response
    {
        $user = auth()->user();
        $packages = LgrPackage::getActive();
        $currentPackage = $user->lgrPackage;

        return Inertia::render('GrowNet/LoyaltyReward/Packages', [
            'packages' => $packages,
            'currentPackage' => $currentPackage,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'wallet_balance' => $user->wallet_balance ?? 0,
            ],
        ]);
    }

    /**
     * Show package purchase confirmation page
     */
    public function show(LgrPackage $package): Response
    {
        $user = auth()->user();
        $currentPackage = $user->lgrPackage;

        // Check if this is an upgrade
        $isUpgrade = $currentPackage && $currentPackage->id !== $package->id;
        $upgradeCost = 0;

        if ($isUpgrade) {
            // Calculate upgrade cost (difference between packages)
            $upgradeCost = max(0, $package->package_amount - $currentPackage->package_amount);
        }

        return Inertia::render('GrowNet/LoyaltyReward/PackageCheckout', [
            'package' => $package,
            'currentPackage' => $currentPackage,
            'isUpgrade' => $isUpgrade,
            'upgradeCost' => $upgradeCost,
            'finalAmount' => $isUpgrade ? $upgradeCost : $package->package_amount,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'wallet_balance' => $user->wallet_balance ?? 0,
            ],
        ]);
    }

    /**
     * Process package purchase
     */
    public function purchase(Request $request, LgrPackage $package): RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|in:wallet,mobile_money,bank_transfer',
            'phone_number' => 'required_if:payment_method,mobile_money',
        ]);

        $user = auth()->user();
        $currentPackage = $user->lgrPackage;

        // Check if this is an upgrade
        $isUpgrade = $currentPackage && $currentPackage->id !== $package->id;
        $amount = $isUpgrade 
            ? max(0, $package->package_amount - $currentPackage->package_amount)
            : $package->package_amount;

        // Prevent downgrade
        if ($isUpgrade && $amount < 0) {
            return back()->with('error', 'Cannot downgrade to a lower package.');
        }

        // Prevent purchasing same package
        if ($currentPackage && $currentPackage->id === $package->id) {
            return back()->with('error', 'You already have this package.');
        }

        try {
            DB::beginTransaction();

            // Handle payment based on method
            $paymentMethod = $request->input('payment_method');

            if ($paymentMethod === 'wallet') {
                // Check wallet balance
                if ($user->wallet_balance < $amount) {
                    return back()->with('error', 'Insufficient wallet balance.');
                }

                // Deduct from wallet
                $user->decrement('wallet_balance', $amount);

                // Update user's package
                $user->lgr_package_id = $package->id;
                $user->save();

                // Create transaction record
                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'type' => $isUpgrade ? 'lgr_upgrade' : 'lgr_purchase',
                    'amount' => $amount,
                    'status' => 'completed',
                    'description' => $isUpgrade 
                        ? "Upgraded to {$package->name}" 
                        : "Purchased {$package->name}",
                    'metadata' => json_encode([
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'previous_package_id' => $currentPackage?->id,
                        'is_upgrade' => $isUpgrade,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                return redirect()
                    ->route('mygrownet.loyalty-reward.index')
                    ->with('success', $isUpgrade 
                        ? "Successfully upgraded to {$package->name}!" 
                        : "Successfully purchased {$package->name}!");

            } else {
                // For mobile money and bank transfer, create pending payment
                $payment = DB::table('payments')->insertGetId([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'phone_number' => $request->input('phone_number'),
                    'status' => 'pending',
                    'type' => $isUpgrade ? 'lgr_upgrade' : 'lgr_purchase',
                    'metadata' => json_encode([
                        'package_id' => $package->id,
                        'package_name' => $package->name,
                        'previous_package_id' => $currentPackage?->id,
                        'is_upgrade' => $isUpgrade,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                return redirect()
                    ->route('mygrownet.loyalty-reward.index')
                    ->with('info', 'Payment pending. Your package will be activated once payment is confirmed.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('LGR Package purchase failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to process purchase. Please try again.');
        }
    }
}

