<?php

namespace App\Domain\GrowStream\Infrastructure\Listeners;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoView;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\WatchHistory;
use App\Services\PointService;
use App\Models\User;
use App\Models\LifePointSetting;
use App\Models\BonusPointSetting;
use Illuminate\Support\Facades\Log;

class AwardVideoPointsListener
{
    public function __construct(
        private PointService $pointService
    ) {}

    /**
     * Award points when user starts watching a video
     * Only MLM members (account_type = 'member') earn points
     */
    public function handleVideoWatched($event)
    {
        try {
            $video = Video::find($event->videoId);
            $user = User::find($event->userId);

            if (!$video || !$user) {
                return;
            }

            // Only award points to MLM members
            if (!$user->isMember()) {
                Log::info("User is not an MLM member, skipping point award", [
                    'user_id' => $user->id,
                    'account_type' => $user->account_type
                ]);
                return;
            }

            // Get point values from centralized settings
            $lpAmount = LifePointSetting::getLPValue('growstream_video_watch');
            $bpAmount = BonusPointSetting::getBPValue('growstream_video_watch');

            if ($lpAmount <= 0 && $bpAmount <= 0) {
                return;
            }

            // Check if user already received watch points for this video
            $alreadyAwarded = \App\Models\PointTransaction::where('user_id', $user->id)
                ->where('source', 'growstream_video_watch')
                ->where('reference_type', Video::class)
                ->where('reference_id', $video->id)
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            // Award both LP and MAP for watching videos
            $this->pointService->awardPoints(
                user: $user,
                source: 'growstream_video_watch',
                lpAmount: $lpAmount,
                mapAmount: $bpAmount,
                description: "Watched video: {$video->title}",
                reference: $video
            );

            Log::info("Awarded watch points to MLM member", [
                'user_id' => $user->id,
                'video_id' => $video->id,
                'lp' => $lpAmount,
                'map' => $bpAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to award video watch points", [
                'error' => $e->getMessage(),
                'video_id' => $event->videoId ?? null,
                'user_id' => $event->userId ?? null
            ]);
        }
    }

    /**
     * Award points when user completes watching a video
     * Only MLM members (account_type = 'member') earn points
     */
    public function handleVideoCompleted($event)
    {
        try {
            $video = Video::find($event->videoId);
            $user = User::find($event->userId);

            if (!$video || !$user) {
                return;
            }

            // Only award points to MLM members
            if (!$user->isMember()) {
                Log::info("User is not an MLM member, skipping point award", [
                    'user_id' => $user->id,
                    'account_type' => $user->account_type
                ]);
                return;
            }

            // Get point values from centralized settings
            $lpAmount = LifePointSetting::getLPValue('growstream_video_completion');
            $bpAmount = BonusPointSetting::getBPValue('growstream_video_completion');

            if ($lpAmount <= 0 && $bpAmount <= 0) {
                return;
            }

            // Check if user already received completion points for this video
            $alreadyAwarded = \App\Models\PointTransaction::where('user_id', $user->id)
                ->where('source', 'growstream_video_completion')
                ->where('reference_type', Video::class)
                ->where('reference_id', $video->id)
                ->exists();

            if ($alreadyAwarded) {
                return;
            }

            // Award both LP and MAP for completing videos
            $this->pointService->awardPoints(
                user: $user,
                source: 'growstream_video_completion',
                lpAmount: $lpAmount,
                mapAmount: $bpAmount,
                description: "Completed video: {$video->title}",
                reference: $video
            );

            // Award additional starter kit points if applicable
            // Starter kit points are LP-focused (for level advancement)
            if ($video->is_starter_kit_content && $video->starter_kit_points_reward > 0) {
                $this->pointService->awardPoints(
                    user: $user,
                    source: 'growstream_starter_kit_content',
                    lpAmount: $video->starter_kit_points_reward,
                    mapAmount: 0,
                    description: "Completed starter kit video: {$video->title}",
                    reference: $video
                );
            }

            Log::info("Awarded completion points to MLM member", [
                'user_id' => $user->id,
                'video_id' => $video->id,
                'lp' => $lpAmount,
                'map' => $bpAmount,
                'starter_kit_lp' => $video->starter_kit_points_reward ?? 0
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to award video completion points", [
                'error' => $e->getMessage(),
                'video_id' => $event->videoId ?? null,
                'user_id' => $event->userId ?? null
            ]);
        }
    }

    /**
     * Award points when user shares a video
     * Only MLM members (account_type = 'member') earn points
     */
    public function handleVideoShared($event)
    {
        try {
            $video = Video::find($event->videoId);
            $user = User::find($event->userId);

            if (!$video || !$user) {
                return;
            }

            // Only award points to MLM members
            if (!$user->isMember()) {
                Log::info("User is not an MLM member, skipping point award", [
                    'user_id' => $user->id,
                    'account_type' => $user->account_type
                ]);
                return;
            }

            // Get point values from centralized settings
            $lpAmount = LifePointSetting::getLPValue('growstream_video_share');
            $bpAmount = BonusPointSetting::getBPValue('growstream_video_share');

            if ($lpAmount <= 0 && $bpAmount <= 0) {
                return;
            }

            // Award both LP and MAP for sharing videos
            $this->pointService->awardPoints(
                user: $user,
                source: 'growstream_video_share',
                lpAmount: $lpAmount,
                mapAmount: $bpAmount,
                description: "Shared video: {$video->title}",
                reference: $video
            );

            Log::info("Awarded share points to MLM member", [
                'user_id' => $user->id,
                'video_id' => $video->id,
                'lp' => $lpAmount,
                'map' => $bpAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to award video share points", [
                'error' => $e->getMessage(),
                'video_id' => $event->videoId ?? null,
                'user_id' => $event->userId ?? null
            ]);
        }
    }
}