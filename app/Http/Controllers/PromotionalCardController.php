<?php

namespace App\Http\Controllers;

use App\Application\Services\Promotion\PromotionalCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class PromotionalCardController extends Controller
{
    public function __construct(
        private PromotionalCardService $cardService
    ) {}

    /**
     * Display the promotional cards gallery
     */
    public function index(Request $request): Response
    {
        $category = $request->query('category');
        $cards = $this->cardService->getActiveCards($category);

        $userStats = null;
        if (auth()->check()) {
            $userStats = $this->cardService->getUserShareStats(auth()->id());
        }

        return Inertia::render('PromotionalCards/Index', [
            'cards' => $cards,
            'userStats' => $userStats,
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Display a single promotional card
     */
    public function show(string $slug): Response
    {
        $card = $this->cardService->getCardBySlug($slug);

        if (!$card) {
            abort(404, 'Promotional card not found');
        }

        // Record view
        $this->cardService->recordView($card->id);

        return Inertia::render('PromotionalCards/Show', [
            'card' => $card,
        ]);
    }

    /**
     * Record a share event
     */
    public function recordShare(Request $request, int $cardId): JsonResponse
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:facebook,twitter,whatsapp,linkedin,copy_link,other',
        ]);

        $this->cardService->recordShare(
            $cardId,
            auth()->id(),
            $validated['platform'],
            $request->ip()
        );

        $userStats = $this->cardService->getUserShareStats(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Share recorded successfully',
            'stats' => $userStats,
        ]);
    }
}
