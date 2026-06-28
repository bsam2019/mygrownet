<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopModel;
use App\Infrastructure\Persistence\Eloquent\Workshop\WorkshopRegistrationModel;
use App\Application\Workshop\UseCases\RegisterForWorkshopUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkshopController extends Controller
{
    public function __construct(
        private RegisterForWorkshopUseCase $registerUseCase
    ) {}

    public function index(Request $request)
    {
        $query = WorkshopModel::where('status', 'published')
            ->where('start_date', '>', now());

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->format) {
            $query->where('delivery_format', $request->format);
        }

        $workshops = $query->orderBy('start_date')
            ->get()
            ->map(function ($workshop) {
                return [
                    'id' => $workshop->id,
                    'title' => $workshop->title,
                    'slug' => $workshop->slug,
                    'description' => $workshop->description,
                    'category' => $workshop->category,
                    'delivery_format' => $workshop->delivery_format,
                    'price' => number_format($workshop->price, 2),
                    'lp_reward' => $workshop->lp_reward,
                    'bp_reward' => $workshop->bp_reward,
                    'start_date' => $workshop->start_date->format('M j, Y g:i A'),
                    'end_date' => $workshop->end_date->format('M j, Y g:i A'),
                    'location' => $workshop->location,
                    'instructor_name' => $workshop->instructor_name,
                    'featured_image' => $workshop->featured_image,
                    'registered_count' => $workshop->registeredCount(),
                    'available_slots' => $workshop->availableSlots(),
                    'is_full' => $workshop->max_participants && $workshop->registeredCount() >= $workshop->max_participants,
                ];
            });

        return Inertia::render('MyGrowNet/Workshops/Index', [
            'workshops' => $workshops,
            'filters' => $request->only(['category', 'format']),
        ]);
    }

    public function show(string $slug)
    {
        $workshop = WorkshopModel::where('slug', $slug)->firstOrFail();
        
        $userRegistration = null;
        $walletBalance = 0;
        
        if (auth()->check()) {
            $user = auth()->user();
            
            $userRegistration = WorkshopRegistrationModel::where('workshop_id', $workshop->id)
                ->where('user_id', auth()->id())
                ->first();
            
            // Calculate wallet balance
            $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
            $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
            $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
                ->where('payment_type', 'wallet_topup')
                ->where('status', 'verified')
                ->sum('amount') ?? 0);
            $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
            $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
            $workshopExpenses = (float) (WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
                ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
                ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
                ->sum('workshops.price') ?? 0);
            $walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
        }

        return Inertia::render('MyGrowNet/Workshops/Show', [
            'workshop' => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'slug' => $workshop->slug,
                'description' => $workshop->description,
                'category' => $workshop->category,
                'delivery_format' => $workshop->delivery_format,
                'price' => number_format($workshop->price, 2),
                'price_raw' => $workshop->price,
                'lp_reward' => $workshop->lp_reward,
                'bp_reward' => $workshop->bp_reward,
                'start_date' => $workshop->start_date->format('M j, Y g:i A'),
                'end_date' => $workshop->end_date->format('M j, Y g:i A'),
                'location' => $workshop->location,
                'meeting_link' => $workshop->meeting_link,
                'requirements' => $workshop->requirements,
                'learning_outcomes' => $workshop->learning_outcomes,
                'instructor_name' => $workshop->instructor_name,
                'instructor_bio' => $workshop->instructor_bio,
                'featured_image' => $workshop->featured_image,
                'registered_count' => $workshop->registeredCount(),
                'available_slots' => $workshop->availableSlots(),
                'is_full' => $workshop->max_participants && $workshop->registeredCount() >= $workshop->max_participants,
            ],
            'user_registration' => $userRegistration ? [
                'id' => $userRegistration->id,
                'status' => $userRegistration->status,
                'registered_at' => $userRegistration->created_at->format('M j, Y'),
            ] : null,
            'wallet_balance' => number_format($walletBalance, 2),
            'wallet_balance_raw' => $walletBalance,
        ]);
    }

    public function register(Request $request, int $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $workshop = WorkshopModel::findOrFail($id);
            $user = auth()->user();

            // Calculate current wallet balance
            $commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
            $profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
            $walletTopups = (float) (\App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::where('user_id', $user->id)
                ->where('payment_type', 'wallet_topup')
                ->where('status', 'verified')
                ->sum('amount') ?? 0);
            $totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
            $totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
            $workshopExpenses = (float) (WorkshopRegistrationModel::where('workshop_registrations.user_id', $user->id)
                ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
                ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
                ->sum('workshops.price') ?? 0);
            $walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
            
            // Check wallet balance
            if ($walletBalance < $workshop->price) {
                return back()->withErrors(['error' => 'Insufficient wallet balance (K' . number_format($walletBalance, 2) . '). Please top up your wallet first.']);
            }

            // Create registration
            $registration = $this->registerUseCase->execute(
                $id,
                auth()->id(),
                $request->notes
            );

            // Auto-confirm since payment is from wallet
            $registrationModel = WorkshopRegistrationModel::find($registration->id());
            $registrationModel->status = 'registered';
            $registrationModel->save();

            // Award points for workshop attendance
            $user->awardPointsForActivity('workshop_attendance', "Registered for workshop: {$workshop->title}");

            return redirect()->route('mygrownet.workshops.my-workshops')
                ->with('success', 'Successfully registered! K' . number_format($workshop->price, 2) . ' deducted from your wallet.');
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function myWorkshops()
    {
        $registrations = WorkshopRegistrationModel::with('workshop')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'workshop' => [
                        'id' => $registration->workshop->id,
                        'title' => $registration->workshop->title,
                        'slug' => $registration->workshop->slug,
                        'start_date' => $registration->workshop->start_date->format('M j, Y'),
                        'category' => $registration->workshop->category,
                        'delivery_format' => $registration->workshop->delivery_format,
                    ],
                    'status' => $registration->status,
                    'registered_at' => $registration->created_at->format('M j, Y'),
                    'completed_at' => $registration->completed_at?->format('M j, Y'),
                    'certificate_issued' => $registration->certificate_issued,
                    'points_awarded' => $registration->points_awarded,
                ];
            });

        return Inertia::render('MyGrowNet/Workshops/MyWorkshops', [
            'registrations' => $registrations,
        ]);
    }
}
