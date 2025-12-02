<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\SubscribeToModuleUseCase;
use App\Application\UseCases\Module\CancelSubscriptionUseCase;
use App\Application\UseCases\Module\UpgradeSubscriptionUseCase;
use App\Http\Controllers\Controller;
use App\Presentation\Http\Requests\SubscribeToModuleRequest;
use App\Presentation\Http\Requests\CancelSubscriptionRequest;
use App\Presentation\Http\Requests\UpgradeSubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleSubscriptionController extends Controller
{
    public function __construct(
        private SubscribeToModuleUseCase $subscribeUseCase,
        private CancelSubscriptionUseCase $cancelUseCase,
        private UpgradeSubscriptionUseCase $upgradeUseCase
    ) {}

    /**
     * Subscribe to a module
     */
    public function store(SubscribeToModuleRequest $request): RedirectResponse
    {
        try {
            $subscriptionDTO = $this->subscribeUseCase->execute(
                userId: $request->user()->id,
                moduleId: $request->validated('module_id'),
                tier: $request->validated('tier'),
                amount: $request->validated('amount'),
                currency: $request->validated('currency', 'ZMW'),
                billingCycle: $request->validated('billing_cycle', 'monthly')
            );

            return redirect()
                ->route('home-hub.index')
                ->with('success', 'Successfully subscribed to module!');
                
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a subscription
     */
    public function destroy(CancelSubscriptionRequest $request, int $subscriptionId): RedirectResponse
    {
        try {
            $this->cancelUseCase->execute(
                subscriptionId: $subscriptionId,
                userId: $request->user()->id,
                immediately: $request->validated('immediately', false)
            );

            return back()->with('success', 'Subscription cancelled successfully.');
            
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upgrade a subscription
     */
    public function upgrade(UpgradeSubscriptionRequest $request, int $subscriptionId): RedirectResponse
    {
        try {
            $subscriptionDTO = $this->upgradeUseCase->execute(
                subscriptionId: $subscriptionId,
                userId: $request->user()->id,
                newTier: $request->validated('new_tier'),
                additionalAmount: $request->validated('additional_amount')
            );

            return back()->with('success', 'Subscription upgraded successfully!');
            
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
