<?php

namespace App\Services;

use App\Models\CommunityProject;
use App\Models\ProjectContribution;
use App\Models\ProjectVote;
use App\Models\ProjectProfitDistribution;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommunityProjectService
{
    /**
     * Get projects available for a specific tier
     */
    public function getProjectsForTier(string $tierName): Collection
    {
        return CommunityProject::active()
            ->forTier($tierName)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get projects available for a user based on their current tier
     */
    public function getProjectsForUser(User $user): Collection
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        return $this->getProjectsForTier($tierName);
    }

    /**
     * Create a new community project
     */
    public function createProject(User $creator, array $projectData): CommunityProject
    {
        return DB::transaction(function () use ($creator, $projectData) {
            $project = CommunityProject::create(array_merge($projectData, [
                'created_by' => $creator->id,
                'slug' => \Illuminate\Support\Str::slug($projectData['name']),
                'status' => 'planning'
            ]));

            Log::info('Community project created', [
                'project_id' => $project->id,
                'creator_id' => $creator->id,
                'project_name' => $project->name
            ]);

            return $project;
        });
    }

    /**
     * Process a contribution to a community project
     */
    public function processContribution(
        User $user, 
        CommunityProject $project, 
        float $amount, 
        string $paymentMethod = 'internal_balance',
        array $paymentDetails = []
    ): ProjectContribution {
        if (!$project->canUserContribute($user)) {
            throw new \Exception('User cannot contribute to this project at this time.');
        }

        return DB::transaction(function () use ($user, $project, $amount, $paymentMethod, $paymentDetails) {
            // Create the contribution
            $contribution = $project->addContribution($user, $amount, [
                'payment_method' => $paymentMethod,
                'payment_details' => $paymentDetails,
                'transaction_reference' => 'PROJ-CONTRIB-' . time() . '-' . uniqid()
            ]);

            // Create transaction record
            Transaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'transaction_type' => 'project_contribution',
                'status' => $contribution->status === 'confirmed' ? 'completed' : 'pending',
                'payment_method' => $paymentMethod,
                'description' => "Contribution to project: {$project->name}",
                'reference_number' => $contribution->transaction_reference,
                'processed_at' => $contribution->status === 'confirmed' ? now() : null
            ]);

            Log::info('Project contribution processed', [
                'contribution_id' => $contribution->id,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'amount' => $amount,
                'status' => $contribution->status
            ]);

            return $contribution;
        });
    }

    /**
     * Confirm a pending contribution
     */
    public function confirmContribution(ProjectContribution $contribution, User $confirmedBy): void
    {
        DB::transaction(function () use ($contribution, $confirmedBy) {
            $contribution->confirm($confirmedBy);

            // Update related transaction
            Transaction::where('reference_number', $contribution->transaction_reference)
                ->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'processed_by' => $confirmedBy->id
                ]);

            Log::info('Project contribution confirmed', [
                'contribution_id' => $contribution->id,
                'confirmed_by' => $confirmedBy->id
            ]);
        });
    }

    /**
     * Create a voting session for a project
     */
    public function createVotingSession(
        CommunityProject $project,
        string $voteType,
        string $subject,
        string $description = null,
        int $durationDays = 7
    ): string {
        $sessionId = ProjectVote::createVoteSession(
            $project,
            $voteType,
            $subject,
            $description,
            null,
            $durationDays
        );

        // Create initial vote record to establish the session
        $deadline = now()->addDays($durationDays)->toDateString();
        
        // This is a placeholder vote to establish the session
        // Real votes will be cast by users
        ProjectVote::create([
            'user_id' => $project->created_by, // Project creator as session initiator
            'community_project_id' => $project->id,
            'vote_type' => $voteType,
            'vote_subject' => $subject,
            'vote_description' => $description,
            'vote' => 'abstain', // Placeholder vote
            'voting_power' => 0,
            'tier_at_vote' => 'system',
            'contribution_amount' => 0,
            'voted_at' => now(),
            'vote_session_id' => $sessionId,
            'vote_deadline' => $deadline
        ]);

        Log::info('Voting session created', [
            'project_id' => $project->id,
            'session_id' => $sessionId,
            'vote_type' => $voteType,
            'subject' => $subject
        ]);

        return $sessionId;
    }

    /**
     * Cast a vote in a voting session
     */
    public function castVote(
        User $user,
        CommunityProject $project,
        string $sessionId,
        string $vote,
        string $comments = null
    ): ProjectVote {
        $projectVote = ProjectVote::castVote($user, $project, $sessionId, $vote, $comments);

        Log::info('Vote cast', [
            'vote_id' => $projectVote->id,
            'user_id' => $user->id,
            'project_id' => $project->id,
            'session_id' => $sessionId,
            'vote' => $vote
        ]);

        return $projectVote;
    }

    /**
     * Calculate and create profit distributions for a project
     */
    public function calculateProfitDistributions(
        CommunityProject $project,
        float $totalProfit,
        string $distributionType = 'quarterly',
        Carbon $periodStart = null,
        Carbon $periodEnd = null
    ): array {
        $periodStart = $periodStart ?? now()->startOfQuarter();
        $periodEnd = $periodEnd ?? now()->endOfQuarter();
        $periodLabel = $this->generatePeriodLabel($distributionType, $periodStart);

        return DB::transaction(function () use ($project, $totalProfit, $distributionType, $periodLabel, $periodStart, $periodEnd) {
            $distributions = ProjectProfitDistribution::calculateProjectDistributions(
                $project,
                $totalProfit,
                $distributionType,
                $periodLabel,
                $periodStart,
                $periodEnd
            );

            Log::info('Profit distributions calculated', [
                'project_id' => $project->id,
                'total_profit' => $totalProfit,
                'distribution_count' => count($distributions),
                'period_label' => $periodLabel
            ]);

            return $distributions;
        });
    }

    /**
     * Process profit distribution payments
     */
    public function processDistributionPayments(array $distributionIds, User $paidBy): array
    {
        $results = [];

        DB::transaction(function () use ($distributionIds, $paidBy, &$results) {
            foreach ($distributionIds as $distributionId) {
                $distribution = ProjectProfitDistribution::find($distributionId);
                
                if ($distribution && $distribution->status === 'approved') {
                    try {
                        $paymentReference = 'PROJ-PAY-' . $distribution->id . '-' . time();
                        $distribution->markAsPaid($paidBy, $paymentReference, 'internal_balance');
                        
                        $results[] = [
                            'distribution_id' => $distributionId,
                            'status' => 'success',
                            'amount' => $distribution->distribution_amount,
                            'user_id' => $distribution->user_id
                        ];
                    } catch (\Exception $e) {
                        $results[] = [
                            'distribution_id' => $distributionId,
                            'status' => 'error',
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
        });

        Log::info('Distribution payments processed', [
            'processed_count' => count($results),
            'paid_by' => $paidBy->id
        ]);

        return $results;
    }

    /**
     * Get user's project portfolio
     */
    public function getUserProjectPortfolio(User $user): array
    {
        $contributions = ProjectContribution::forUser($user)
            ->confirmed()
            ->with(['project', 'project.profitDistributions'])
            ->get();

        $totalContributed = $contributions->sum('amount');
        $totalReturns = $contributions->sum('total_returns_received');
        $activeProjects = $contributions->where('project.status', 'active')->count();
        $completedProjects = $contributions->where('project.status', 'completed')->count();

        return [
            'total_contributed' => $totalContributed,
            'total_returns_received' => $totalReturns,
            'net_roi' => $totalContributed > 0 ? (($totalReturns / $totalContributed) * 100) : 0,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'total_projects' => $contributions->count(),
            'contributions' => $contributions->map(function ($contribution) {
                return [
                    'project_name' => $contribution->project->name,
                    'contribution_amount' => $contribution->amount,
                    'returns_received' => $contribution->total_returns_received,
                    'roi' => $contribution->getReturnOnInvestment(),
                    'project_status' => $contribution->project->status,
                    'contributed_at' => $contribution->contributed_at
                ];
            })
        ];
    }

    /**
     * Get project analytics and statistics
     */
    public function getProjectAnalytics(CommunityProject $project): array
    {
        $contributions = $project->contributions()->confirmed()->get();
        $distributions = $project->profitDistributions()->paid()->get();
        
        return [
            'funding_statistics' => [
                'target_amount' => $project->target_amount,
                'current_amount' => $project->current_amount,
                'funding_progress' => $project->getFundingProgress(),
                'remaining_amount' => $project->getRemainingFundingAmount(),
                'total_contributors' => $project->total_contributors,
                'average_contribution' => $contributions->avg('amount'),
                'largest_contribution' => $contributions->max('amount'),
                'smallest_contribution' => $contributions->min('amount')
            ],
            'timeline_statistics' => [
                'days_remaining_funding' => $project->getDaysRemainingForFunding(),
                'project_duration_months' => $project->project_duration_months,
                'funding_period' => [
                    'start' => $project->funding_start_date,
                    'end' => $project->funding_end_date
                ],
                'project_period' => [
                    'start' => $project->project_start_date,
                    'expected_end' => $project->expected_completion_date
                ]
            ],
            'financial_performance' => [
                'expected_annual_return' => $project->expected_annual_return,
                'total_distributions_paid' => $distributions->sum('distribution_amount'),
                'average_distribution' => $distributions->avg('distribution_amount'),
                'distribution_count' => $distributions->count()
            ],
            'community_engagement' => [
                'total_votes' => $project->total_votes,
                'community_approval_rating' => $project->community_approval_rating,
                'contributor_tier_breakdown' => $contributions->groupBy('tier_at_contribution')->map->count()
            ]
        ];
    }

    /**
     * Generate period label for distributions
     */
    private function generatePeriodLabel(string $distributionType, Carbon $periodStart): string
    {
        return match ($distributionType) {
            'monthly' => $periodStart->format('F Y'),
            'quarterly' => 'Q' . $periodStart->quarter . ' ' . $periodStart->year,
            'annual' => $periodStart->year,
            'milestone' => 'Milestone ' . $periodStart->format('Y-m-d'),
            'final' => 'Final Distribution',
            default => $periodStart->format('Y-m-d')
        };
    }

    /**
     * Get community project statistics
     */
    public function getCommunityProjectStatistics(): array
    {
        $projects = CommunityProject::all();
        $contributions = ProjectContribution::confirmed()->get();
        $distributions = ProjectProfitDistribution::paid()->get();

        return [
            'project_statistics' => [
                'total_projects' => $projects->count(),
                'active_projects' => $projects->where('status', 'active')->count(),
                'funding_projects' => $projects->where('status', 'funding')->count(),
                'completed_projects' => $projects->where('status', 'completed')->count(),
                'by_type' => $projects->groupBy('type')->map->count(),
                'by_risk_level' => $projects->groupBy('risk_level')->map->count()
            ],
            'funding_statistics' => [
                'total_target_amount' => $projects->sum('target_amount'),
                'total_raised_amount' => $projects->sum('current_amount'),
                'total_contributions' => $contributions->count(),
                'total_contributors' => $contributions->pluck('user_id')->unique()->count(),
                'average_contribution' => $contributions->avg('amount'),
                'funding_success_rate' => $projects->count() > 0 
                    ? ($projects->where('current_amount', '>=', 'target_amount')->count() / $projects->count()) * 100 
                    : 0
            ],
            'returns_statistics' => [
                'total_distributions_paid' => $distributions->sum('distribution_amount'),
                'total_distributions_count' => $distributions->count(),
                'average_distribution' => $distributions->avg('distribution_amount'),
                'by_distribution_type' => $distributions->groupBy('distribution_type')->map->count()
            ]
        ];
    }
}