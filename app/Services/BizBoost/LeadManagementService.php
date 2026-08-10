<?php

namespace App\Services\BizBoost;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class LeadManagementService
{
    /**
     * Get or create default pipeline & stages for a business.
     */
    public function ensureDefaultPipeline(int $businessId): int
    {
        if (!Schema::hasTable('bizboost_lead_pipelines')) {
            return 0;
        }

        $pipeline = DB::table('bizboost_lead_pipelines')
            ->where('business_id', $businessId)
            ->where('is_default', true)
            ->first();

        if ($pipeline) {
            return $pipeline->id;
        }

        // Create default pipeline
        $pipelineId = DB::table('bizboost_lead_pipelines')->insertGetId([
            'business_id' => $businessId,
            'name' => 'Sales Pipeline',
            'slug' => 'sales-pipeline',
            'is_default' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default stages
        $stages = [
            ['name' => 'New', 'color' => '#3b82f6', 'sort_order' => 1, 'sla_target_minutes' => 30],
            ['name' => 'Contacted', 'color' => '#8b5cf6', 'sort_order' => 2, 'sla_target_minutes' => 60],
            ['name' => 'Qualified', 'color' => '#06b6d4', 'sort_order' => 3, 'sla_target_minutes' => 120],
            ['name' => 'Quotation', 'color' => '#f59e0b', 'sort_order' => 4, 'sla_target_minutes' => 240],
            ['name' => 'Won', 'color' => '#10b981', 'sort_order' => 5, 'is_won' => true, 'sla_target_minutes' => 0],
            ['name' => 'Lost', 'color' => '#ef4444', 'sort_order' => 6, 'is_lost' => true, 'sla_target_minutes' => 0],
        ];

        foreach ($stages as $stage) {
            DB::table('bizboost_pipeline_stages')->insert(array_merge($stage, [
                'pipeline_id' => $pipelineId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        return $pipelineId;
    }

    /**
     * Get pipeline stages with lead cards and SLA breach indicators.
     */
    public function getPipelineBoard(int $businessId, ?int $pipelineId = null): array
    {
        if (!$pipelineId) {
            $pipelineId = $this->ensureDefaultPipeline($businessId);
        }

        $stages = DB::table('bizboost_pipeline_stages')
            ->where('pipeline_id', $pipelineId)
            ->orderBy('sort_order', 'asc')
            ->get();

        $board = [];

        foreach ($stages as $stage) {
            $leads = DB::table('bizboost_leads as l')
                ->leftJoin('bizboost_customers as c', 'l.customer_id', '=', 'c.id')
                ->where('l.business_id', $businessId)
                ->where('l.stage_id', $stage->id)
                ->select(
                    'l.*',
                    'c.name as customer_name',
                    'c.phone as customer_phone',
                    'c.email as customer_email'
                )
                ->orderBy('l.created_at', 'desc')
                ->get();

            // Calculate SLA breach for leads
            $leadsWithSla = $leads->map(function ($lead) use ($stage) {
                $created = Carbon::parse($lead->created_at);
                $isBreached = false;
                
                if (!$lead->first_response_at && $stage->sla_target_minutes > 0) {
                    $minutesPending = $created->diffInMinutes(now());
                    if ($minutesPending > $stage->sla_target_minutes) {
                        $isBreached = true;
                    }
                }

                $lead->is_sla_breached = $isBreached;
                return $lead;
            });

            $board[] = [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'is_won' => (bool) $stage->is_won,
                'is_lost' => (bool) $stage->is_lost,
                'sla_target_minutes' => (int) $stage->sla_target_minutes,
                'total_leads' => $leadsWithSla->count(),
                'total_value' => $leadsWithSla->sum('estimated_value_zmw'),
                'leads' => $leadsWithSla,
            ];
        }

        return [
            'pipeline_id' => $pipelineId,
            'stages' => $board,
        ];
    }
}
