<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\LeadModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OpportunityModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CommunicationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\FollowUpModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CampaignModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerSegmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerMetricModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrmService
{
    public function __construct(
        private AuditTrailService $auditTrail
    ) {}

    // Lead Management
    public function createLead(array $data): LeadModel
    {
        if (empty($data['lead_number'])) {
            $lastLead = LeadModel::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastLead ? (int) substr($lastLead->lead_number, 5) + 1 : 1;
            $data['lead_number'] = 'LEAD-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
        }

        $lead = LeadModel::create($data);

        $this->auditTrail->log(
            companyId: $lead->company_id,
            userId: $data['created_by'],
            entityType: 'lead',
            entityId: $lead->id,
            action: 'created',
            newValues: $lead->toArray()
        );

        return $lead;
    }

    public function convertLeadToCustomer(LeadModel $lead, int $userId): CustomerModel
    {
        return DB::transaction(function () use ($lead, $userId) {
            $customer = CustomerModel::create([
                'company_id' => $lead->company_id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company_name' => $lead->company_name,
                'created_by' => $userId,
            ]);

            $lead->update([
                'status' => 'won',
                'converted_at' => now(),
                'converted_to_customer_id' => $customer->id,
            ]);

            $this->auditTrail->log(
                companyId: $lead->company_id,
                userId: $userId,
                entityType: 'lead',
                entityId: $lead->id,
                action: 'converted_to_customer',
                newValues: ['customer_id' => $customer->id]
            );

            return $customer;
        });
    }

    // Opportunity Management
    public function createOpportunity(array $data): OpportunityModel
    {
        if (empty($data['opportunity_number'])) {
            $lastOpp = OpportunityModel::where('company_id', $data['company_id'])
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastOpp ? (int) substr($lastOpp->opportunity_number, 4) + 1 : 1;
            $data['opportunity_number'] = 'OPP-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
        }

        $data['weighted_amount'] = ($data['amount'] * $data['probability']) / 100;

        $opportunity = OpportunityModel::create($data);

        $this->auditTrail->log(
            companyId: $opportunity->company_id,
            userId: $data['created_by'],
            entityType: 'opportunity',
            entityId: $opportunity->id,
            action: 'created',
            newValues: $opportunity->toArray()
        );

        return $opportunity;
    }

    public function updateOpportunityStage(OpportunityModel $opportunity, string $stage, int $userId): OpportunityModel
    {
        $oldStage = $opportunity->stage;
        
        $opportunity->update([
            'stage' => $stage,
            'last_activity_at' => now(),
        ]);

        if (in_array($stage, ['closed_won', 'closed_lost'])) {
            $opportunity->update(['actual_close_date' => now()]);
        }

        $this->auditTrail->log(
            companyId: $opportunity->company_id,
            userId: $userId,
            entityType: 'opportunity',
            entityId: $opportunity->id,
            action: 'stage_changed',
            oldValues: ['stage' => $oldStage],
            newValues: ['stage' => $stage]
        );

        return $opportunity;
    }

    // Communication History
    public function logCommunication(array $data): CommunicationModel
    {
        $communication = CommunicationModel::create($data);

        // Update last contacted date on related entity
        if ($communication->communicable_type === LeadModel::class) {
            $communication->communicable->update(['last_contacted_at' => $data['communicated_at']]);
        }

        if ($communication->communicable_type === OpportunityModel::class) {
            $communication->communicable->update(['last_activity_at' => $data['communicated_at']]);
        }

        return $communication;
    }

    // Follow-up Reminders
    public function createFollowUp(array $data): FollowUpModel
    {
        return FollowUpModel::create($data);
    }

    public function completeFollowUp(FollowUpModel $followUp, string $notes, int $userId): FollowUpModel
    {
        $followUp->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $notes,
        ]);

        $this->auditTrail->log(
            companyId: $followUp->company_id,
            userId: $userId,
            entityType: 'follow_up',
            entityId: $followUp->id,
            action: 'completed'
        );

        return $followUp;
    }

    // Customer Segmentation
    public function createSegment(array $data): CustomerSegmentModel
    {
        $segment = CustomerSegmentModel::create($data);
        $this->updateSegmentCount($segment);
        return $segment;
    }

    public function updateSegmentCount(CustomerSegmentModel $segment): void
    {
        $count = $this->getSegmentCustomers($segment)->count();
        $segment->update(['customer_count' => $count]);
    }

    public function getSegmentCustomers(CustomerSegmentModel $segment)
    {
        $query = CustomerModel::where('company_id', $segment->company_id);
        
        foreach ($segment->criteria as $criterion) {
            $field = $criterion['field'];
            $operator = $criterion['operator'];
            $value = $criterion['value'];
            
            $query->where($field, $operator, $value);
        }
        
        return $query;
    }

    // Marketing Campaigns
    public function createCampaign(array $data): CampaignModel
    {
        return CampaignModel::create($data);
    }

    public function launchCampaign(CampaignModel $campaign, int $userId): CampaignModel
    {
        $campaign->update([
            'status' => 'active',
            'start_date' => now(),
        ]);

        $this->auditTrail->log(
            companyId: $campaign->company_id,
            userId: $userId,
            entityType: 'campaign',
            entityId: $campaign->id,
            action: 'launched'
        );

        return $campaign;
    }

    // Customer Lifetime Value
    public function calculateCustomerMetrics(int $companyId, int $customerId): CustomerMetricModel
    {
        $customer = CustomerModel::findOrFail($customerId);
        
        // Get all invoices
        $invoices = InvoiceModel::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->get();

        // Get all jobs
        $jobs = JobModel::where('company_id', $companyId)
            ->where('customer_id', $customerId)
            ->where('status', 'completed')
            ->get();

        $totalRevenue = $invoices->sum('total_amount');
        $totalProfit = $jobs->sum('profit_amount');
        $totalOrders = $invoices->count();
        $totalJobs = $jobs->count();

        $firstPurchase = $invoices->min('invoice_date');
        $lastPurchase = $invoices->max('invoice_date');
        
        $daysSinceLastPurchase = $lastPurchase ? Carbon::parse($lastPurchase)->diffInDays(now()) : null;
        
        $purchaseFrequency = null;
        if ($totalOrders > 1 && $firstPurchase && $lastPurchase) {
            $daysBetween = Carbon::parse($firstPurchase)->diffInDays(Carbon::parse($lastPurchase));
            $purchaseFrequency = $daysBetween / ($totalOrders - 1);
        }

        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Determine tier
        $tier = 'bronze';
        if ($totalRevenue >= 100000) $tier = 'platinum';
        elseif ($totalRevenue >= 50000) $tier = 'gold';
        elseif ($totalRevenue >= 20000) $tier = 'silver';

        // Determine churn risk
        $churnRisk = 'low';
        if ($daysSinceLastPurchase > 180) $churnRisk = 'high';
        elseif ($daysSinceLastPurchase > 90) $churnRisk = 'medium';

        return CustomerMetricModel::updateOrCreate(
            ['company_id' => $companyId, 'customer_id' => $customerId],
            [
                'lifetime_value' => $totalRevenue,
                'average_order_value' => $avgOrderValue,
                'total_orders' => $totalOrders,
                'total_jobs' => $totalJobs,
                'first_purchase_date' => $firstPurchase,
                'last_purchase_date' => $lastPurchase,
                'days_since_last_purchase' => $daysSinceLastPurchase,
                'purchase_frequency_days' => $purchaseFrequency,
                'total_revenue' => $totalRevenue,
                'total_profit' => $totalProfit,
                'profit_margin' => $profitMargin,
                'customer_tier' => $tier,
                'churn_risk' => $churnRisk,
                'calculated_at' => now(),
            ]
        );
    }

    public function getSalesPipeline(int $companyId): array
    {
        $opportunities = OpportunityModel::where('company_id', $companyId)
            ->whereNotIn('stage', ['closed_won', 'closed_lost'])
            ->get();

        $pipeline = [
            'prospecting' => ['count' => 0, 'value' => 0, 'weighted_value' => 0],
            'qualification' => ['count' => 0, 'value' => 0, 'weighted_value' => 0],
            'proposal' => ['count' => 0, 'value' => 0, 'weighted_value' => 0],
            'negotiation' => ['count' => 0, 'value' => 0, 'weighted_value' => 0],
        ];

        foreach ($opportunities as $opp) {
            if (isset($pipeline[$opp->stage])) {
                $pipeline[$opp->stage]['count']++;
                $pipeline[$opp->stage]['value'] += $opp->amount;
                $pipeline[$opp->stage]['weighted_value'] += $opp->weighted_amount;
            }
        }

        return $pipeline;
    }
}
