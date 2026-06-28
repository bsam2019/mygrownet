<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\CrmService;
use App\Infrastructure\Persistence\Eloquent\CMS\LeadModel;
use App\Infrastructure\Persistence\Eloquent\CMS\OpportunityModel;
use App\Infrastructure\Persistence\Eloquent\CMS\FollowUpModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CustomerSegmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CampaignModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CrmController extends Controller
{
    public function __construct(
        private CrmService $crmService
    ) {}

    // Leads
    public function leadsIndex(Request $request)
    {
        $leads = LeadModel::where('company_id', $request->user()->company_id)
            ->with(['assignedTo', 'createdBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/CRM/Leads/Index', ['leads' => $leads]);
    }

    public function storeL ead(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'company_name' => 'nullable|string',
            'source' => 'required|in:website,referral,social_media,cold_call,email,event,other',
            'estimated_value' => 'nullable|numeric',
            'assigned_to' => 'nullable|exists:cms_users,id',
        ]);

        $lead = $this->crmService->createLead([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Lead created successfully.');
    }

    public function convertLead(Request $request, LeadModel $lead)
    {
        $customer = $this->crmService->convertLeadToCustomer($lead, $request->user()->id);
        return redirect()->route('cms.customers.show', $customer)->with('success', 'Lead converted to customer.');
    }

    // Opportunities
    public function opportunitiesIndex(Request $request)
    {
        $opportunities = OpportunityModel::where('company_id', $request->user()->company_id)
            ->with(['customer', 'assignedTo'])
            ->when($request->stage, fn($q) => $q->where('stage', $request->stage))
            ->orderBy('expected_close_date')
            ->paginate(20);

        $pipeline = $this->crmService->getSalesPipeline($request->user()->company_id);

        return Inertia::render('CMS/CRM/Opportunities/Index', [
            'opportunities' => $opportunities,
            'pipeline' => $pipeline,
        ]);
    }

    public function storeOpportunity(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:cms_customers,id',
            'name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'expected_close_date' => 'required|date',
            'assigned_to' => 'nullable|exists:cms_users,id',
        ]);

        $this->crmService->createOpportunity([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Opportunity created successfully.');
    }

    // Follow-ups
    public function followUpsIndex(Request $request)
    {
        $followUps = FollowUpModel::where('company_id', $request->user()->company_id)
            ->with(['followable', 'assignedTo'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->assigned_to, fn($q) => $q->where('assigned_to', $request->assigned_to))
            ->orderBy('due_date')
            ->paginate(20);

        return Inertia::render('CMS/CRM/FollowUps/Index', ['followUps' => $followUps]);
    }

    public function storeFollowUp(Request $request)
    {
        $validated = $request->validate([
            'followable_type' => 'required|string',
            'followable_id' => 'required|integer',
            'title' => 'required|string',
            'due_date' => 'required|date',
            'assigned_to' => 'required|exists:cms_users,id',
        ]);

        $this->crmService->createFollowUp([
            ...$validated,
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Follow-up created successfully.');
    }

    // Customer Metrics
    public function customerMetrics(Request $request)
    {
        $metrics = \App\Infrastructure\Persistence\Eloquent\CMS\CustomerMetricModel::where('company_id', $request->user()->company_id)
            ->with('customer')
            ->orderBy('lifetime_value', 'desc')
            ->paginate(20);

        return Inertia::render('CMS/CRM/CustomerMetrics/Index', ['metrics' => $metrics]);
    }

    public function calculateMetrics(Request $request, int $customerId)
    {
        $this->crmService->calculateCustomerMetrics($request->user()->company_id, $customerId);
        return redirect()->back()->with('success', 'Customer metrics calculated.');
    }
}
