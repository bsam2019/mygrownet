<?php

namespace App\Http\Controllers\GrowStream;

use App\Domain\GrowStream\Repositories\VideoRentalRepositoryInterface;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use App\Domain\GrowStream\Services\RentalService;
use App\Domain\PlatformPayments\Services\SharedPaymentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoRentalController extends Controller
{
    public function __construct(
        private VideoRepositoryInterface $videoRepo,
        private VideoRentalRepositoryInterface $rentalRepo,
        private RentalService $rentalService,
        private SharedPaymentService $payments,
    ) {}

    /**
     * Start a PPV rental: create pending rental + initiate PawaPay deposit.
     * POST /videos/{video}/rent
     */
    public function store(Request $request, int $videoId): JsonResponse
    {
        $video = $this->videoRepo->findById($videoId);
        if (! $video) {
            return response()->json(['success' => false, 'error' => 'Video not found'], 404);
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|min:9',
        ]);

        $user = $request->user();
        $price = (float) config('growstream.ppv.price', 15);

        // Check for existing active rental
        if ($this->rentalService->hasActiveRental($user->id, $videoId)) {
            return response()->json([
                'success' => true,
                'already_rented' => true,
                'message' => 'You already have access to this video.',
            ]);
        }

        // Create a pending rental record
        $rental = $this->rentalRepo->create([
            'user_id' => $user->id,
            'video_id' => $videoId,
            'price' => $price,
            'currency' => 'ZMW',
            'access_duration' => config('growstream.ppv.access_duration', '48_hours'),
            'granted_at' => now(),
            'expires_at' => now()->addHours(48),
            'provider_reference' => null,
            'status' => 'pending',
        ]);

        $providerReference = 'rental_' . $rental->id;

        // Initiate payment via the shared service
        $result = $this->payments->initiate(
            organizationId: 0,
            amount: $price,
            currency: 'ZMW',
            phoneNumber: $validated['phone_number'],
            gateway: 'pawapay',
            description: 'Rent: ' . $video->title,
            reference: $providerReference,
            metadata: [
                'rental_id' => $rental->id,
                'video_id' => $videoId,
                'type' => 'ppv_rental',
            ],
        );

        // Attach the provider reference to the rental for webhook matching
        $rental->provider_reference = $providerReference;
        $rental->save();

        return response()->json([
            'success' => true,
            'transaction' => [
                'reference' => $result['transaction']->providerReference() ?? $providerReference,
                'status' => 'pending',
                'amount' => $price,
                'currency' => 'ZMW',
            ],
            'video' => [
                'id' => $video->id,
                'title' => $video->title,
            ],
        ]);
    }

    /**
     * Poll rental payment status by reference.
     * GET /videos/rental-status/{reference}
     */
    public function status(Request $request, string $reference): JsonResponse
    {
        $rental = $this->rentalRepo->query()
            ->where('provider_reference', $reference)
            ->first();

        if (! $rental) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $rental->status,
            'video_id' => $rental->video_id,
        ]);
    }
}
