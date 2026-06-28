<?php

namespace App\Application\Services\Promotion;

use App\Infrastructure\Persistence\Eloquent\Promotion\PromotionalCardModel;
use App\Infrastructure\Persistence\Eloquent\Promotion\PromotionalCardShareModel;
use App\Services\LgrActivityTrackingService;
use App\Services\SocialShareTrackingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PromotionalCardService
{
    public function __construct(
        private LgrActivityTrackingService $lgrTrackingService,
        private SocialShareTrackingService $socialShareTrackingService
    ) {}

    /**
     * Get all active promotional cards
     */
    public function getActiveCards(?string $category = null)
    {
        $query = PromotionalCardModel::active()->ordered();

        if ($category) {
            $query->byCategory($category);
        }

        return $query->get();
    }

    /**
     * Get a single card by slug
     */
    public function getCardBySlug(string $slug): ?PromotionalCardModel
    {
        return PromotionalCardModel::where('slug', $slug)
            ->active()
            ->first();
    }

    /**
     * Get all cards (for admin)
     */
    public function getAllCards()
    {
        return PromotionalCardModel::withTrashed()
            ->with('creator')
            ->ordered()
            ->get();
    }

    /**
     * Create a new promotional card
     */
    public function createCard(array $data): PromotionalCardModel
    {
        DB::beginTransaction();
        try {
            // Generate slug if not provided
            if (!isset($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Ensure unique slug
            $originalSlug = $data['slug'];
            $counter = 1;
            while (PromotionalCardModel::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }

            $card = PromotionalCardModel::create($data);

            DB::commit();

            Log::info('Promotional card created', [
                'card_id' => $card->id,
                'title' => $card->title,
            ]);

            return $card;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create promotional card: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a promotional card
     */
    public function updateCard(int $cardId, array $data): PromotionalCardModel
    {
        DB::beginTransaction();
        try {
            $card = PromotionalCardModel::findOrFail($cardId);

            // If title changed, regenerate slug
            if (isset($data['title']) && $data['title'] !== $card->title) {
                $data['slug'] = Str::slug($data['title']);
                
                // Ensure unique slug
                $originalSlug = $data['slug'];
                $counter = 1;
                while (PromotionalCardModel::where('slug', $data['slug'])
                    ->where('id', '!=', $cardId)
                    ->exists()) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $card->update($data);

            DB::commit();

            Log::info('Promotional card updated', [
                'card_id' => $card->id,
                'title' => $card->title,
            ]);

            return $card->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update promotional card: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a promotional card
     */
    public function deleteCard(int $cardId): bool
    {
        try {
            $card = PromotionalCardModel::findOrFail($cardId);
            $card->delete();

            Log::info('Promotional card deleted', [
                'card_id' => $cardId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete promotional card: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Toggle card active status
     */
    public function toggleActive(int $cardId): PromotionalCardModel
    {
        $card = PromotionalCardModel::findOrFail($cardId);
        $card->update(['is_active' => !$card->is_active]);

        return $card->fresh();
    }

    /**
     * Record a card view
     */
    public function recordView(int $cardId): void
    {
        try {
            $card = PromotionalCardModel::findOrFail($cardId);
            $card->increment('view_count');
        } catch (\Exception $e) {
            Log::error('Failed to record card view: ' . $e->getMessage());
        }
    }

    /**
     * Record a card share and trigger LGR activity
     * CRITICAL: This is the main integration point for LGR
     */
    public function recordShare(
        int $cardId,
        int $userId,
        string $platform,
        ?string $ipAddress = null
    ): void {
        DB::beginTransaction();
        try {
            $card = PromotionalCardModel::findOrFail($cardId);

            // Record the share
            PromotionalCardShareModel::create([
                'promotional_card_id' => $cardId,
                'user_id' => $userId,
                'platform' => $platform,
                'ip_address' => $ipAddress,
                'shared_at' => now(),
            ]);

            // Increment card share count
            $card->increment('share_count');

            // Record in social share tracking (for 5-share threshold)
            $this->socialShareTrackingService->recordShare(
                $userId,
                'content',
                $platform,
                route('promotional-cards.show', $card->slug),
                $ipAddress,
                request()->userAgent()
            );

            DB::commit();

            Log::info('Promotional card share recorded', [
                'card_id' => $cardId,
                'user_id' => $userId,
                'platform' => $platform,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record card share: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user's share statistics
     */
    public function getUserShareStats(int $userId): array
    {
        $todayShares = PromotionalCardShareModel::forUser($userId)
            ->today()
            ->count();

        $totalShares = PromotionalCardShareModel::forUser($userId)->count();

        return [
            'today_shares' => $todayShares,
            'total_shares' => $totalShares,
        ];
    }

    /**
     * Get card statistics (for admin)
     */
    public function getCardStatistics(int $cardId): array
    {
        $card = PromotionalCardModel::findOrFail($cardId);

        return [
            'total_shares' => $card->share_count,
            'total_views' => $card->view_count,
            'today_shares' => $card->getTodaySharesCount(),
            'unique_sharers' => $card->shares()->distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Reorder cards
     */
    public function reorderCards(array $order): void
    {
        DB::beginTransaction();
        try {
            foreach ($order as $index => $cardId) {
                PromotionalCardModel::where('id', $cardId)
                    ->update(['sort_order' => $index]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reorder cards: ' . $e->getMessage());
            throw $e;
        }
    }
}
