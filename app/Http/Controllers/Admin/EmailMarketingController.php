<?php

namespace App\Http\Controllers\Admin;

use App\Application\Services\EmailCampaignService;
use App\Application\UseCases\EmailMarketing\ActivateCampaignUseCase;
use App\Application\UseCases\EmailMarketing\CreateCampaignUseCase;
use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailCampaignModel;
use App\Infrastructure\Persistence\Eloquent\EmailMarketing\EmailTemplateModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailMarketingController extends Controller
{
    public function __construct(
        private EmailCampaignRepository $campaignRepository,
        private EmailCampaignService $campaignService,
        private CreateCampaignUseCase $createCampaignUseCase,
        private ActivateCampaignUseCase $activateCampaignUseCase
    ) {}

    /**
     * Display campaign list
     */
    public function index()
    {
        $campaigns = EmailCampaignModel::with(['sequences', 'subscribers'])
            ->withCount(['subscribers', 'sequences'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/EmailMarketing/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show create campaign form
     */
    public function create()
    {
        $templates = EmailTemplateModel::select('id', 'name', 'category', 'subject')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/EmailMarketing/Create', [
            'templates' => $templates,
        ]);
    }

    /**
     * Store new campaign
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:onboarding,engagement,reactivation,upgrade,custom',
            'trigger_type' => 'required|in:immediate,scheduled,behavioral',
            'trigger_config' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'sequences' => 'required|array|min:1',
            'sequences.*.template_id' => 'required|exists:email_templates,id',
            'sequences.*.delay_days' => 'required|integer|min:0',
            'sequences.*.delay_hours' => 'nullable|integer|min:0|max:23',
        ]);

        $campaign = $this->createCampaignUseCase->execute($validated);

        return redirect()
            ->route('admin.email-campaigns.show', $campaign->id)
            ->with('success', 'Campaign created successfully!');
    }

    /**
     * Show campaign details
     */
    public function show(int $id)
    {
        $campaign = EmailCampaignModel::with([
            'sequences.template',
            'subscribers' => fn($q) => $q->latest()->limit(10),
            'analytics' => fn($q) => $q->latest()->limit(30),
        ])
            ->withCount(['subscribers', 'sequences'])
            ->findOrFail($id);

        // Calculate stats
        $stats = [
            'total_sent' => $campaign->analytics->sum('emails_sent'),
            'total_delivered' => $campaign->analytics->sum('emails_delivered'),
            'total_opened' => $campaign->analytics->sum('emails_opened'),
            'total_clicked' => $campaign->analytics->sum('emails_clicked'),
            'open_rate' => $campaign->analytics->avg('open_rate') ?? 0,
            'click_rate' => $campaign->analytics->avg('click_rate') ?? 0,
        ];

        return Inertia::render('Admin/EmailMarketing/Show', [
            'campaign' => $campaign,
            'stats' => $stats,
        ]);
    }

    /**
     * Show edit campaign form
     */
    public function edit(int $id)
    {
        $campaign = EmailCampaignModel::with('sequences')->findOrFail($id);

        $templates = EmailTemplateModel::select('id', 'name', 'category', 'subject')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/EmailMarketing/Edit', [
            'campaign' => $campaign,
            'templates' => $templates,
        ]);
    }

    /**
     * Update campaign
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_config' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'sequences' => 'required|array|min:1',
            'sequences.*.id' => 'nullable|exists:email_sequences,id',
            'sequences.*.template_id' => 'required|exists:email_templates,id',
            'sequences.*.delay_days' => 'required|integer|min:0',
            'sequences.*.delay_hours' => 'nullable|integer|min:0|max:23',
        ]);

        $campaign = EmailCampaignModel::findOrFail($id);
        
        // Only allow editing draft campaigns
        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Only draft campaigns can be edited.');
        }

        $campaign->update([
            'name' => $validated['name'],
            'trigger_config' => $validated['trigger_config'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        // Update sequences
        $campaign->sequences()->delete();
        foreach ($validated['sequences'] as $index => $sequence) {
            $campaign->sequences()->create([
                'sequence_order' => $index + 1,
                'template_id' => $sequence['template_id'],
                'delay_days' => $sequence['delay_days'],
                'delay_hours' => $sequence['delay_hours'] ?? 0,
            ]);
        }

        return redirect()
            ->route('admin.email-campaigns.show', $campaign->id)
            ->with('success', 'Campaign updated successfully!');
    }

    /**
     * Activate campaign
     */
    public function activate(int $id)
    {
        $this->activateCampaignUseCase->execute($id);

        return back()->with('success', 'Campaign activated successfully!');
    }

    /**
     * Pause campaign
     */
    public function pause(int $id)
    {
        $campaign = EmailCampaignModel::findOrFail($id);
        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campaign paused successfully!');
    }

    /**
     * Resume campaign
     */
    public function resume(int $id)
    {
        $campaign = EmailCampaignModel::findOrFail($id);
        
        if ($campaign->status !== 'paused') {
            return back()->with('error', 'Only paused campaigns can be resumed.');
        }

        $campaign->update(['status' => 'active']);

        return back()->with('success', 'Campaign resumed successfully!');
    }

    /**
     * Delete campaign
     */
    public function destroy(int $id)
    {
        $campaign = EmailCampaignModel::findOrFail($id);
        
        // Only allow deleting draft campaigns
        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Only draft campaigns can be deleted.');
        }

        $campaign->delete();

        return redirect()
            ->route('admin.email-campaigns.index')
            ->with('success', 'Campaign deleted successfully!');
    }

    /**
     * Show templates library
     */
    public function templates()
    {
        $templates = EmailTemplateModel::latest()->paginate(20);

        return Inertia::render('Admin/EmailMarketing/Templates', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show create template form
     */
    public function createTemplate()
    {
        return Inertia::render('Admin/EmailMarketing/TemplateBuilder');
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:onboarding,engagement,reactivation,upgrade,custom',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'html_content' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        EmailTemplateModel::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'html_content' => $validated['html_content'],
            'variables' => json_encode($validated['variables'] ?? []),
            'is_system' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.email-campaigns.templates')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Show edit template form
     */
    public function editTemplate(int $id)
    {
        $template = EmailTemplateModel::findOrFail($id);

        return Inertia::render('Admin/EmailMarketing/TemplateBuilder', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category,
                'subject' => $template->subject,
                'preview_text' => $template->preview_text,
                'html_content' => $template->html_content,
                'variables' => json_decode($template->variables ?? '[]', true),
            ],
        ]);
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:onboarding,engagement,reactivation,upgrade,custom',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'html_content' => 'required|string',
            'variables' => 'nullable|array',
        ]);

        $template = EmailTemplateModel::findOrFail($id);

        // Don't allow editing system templates
        if ($template->is_system) {
            return back()->with('error', 'System templates cannot be edited.');
        }

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'subject' => $validated['subject'],
            'preview_text' => $validated['preview_text'] ?? null,
            'html_content' => $validated['html_content'],
            'variables' => json_encode($validated['variables'] ?? []),
        ]);

        return redirect()
            ->route('admin.email-campaigns.templates')
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Delete template
     */
    public function destroyTemplate(int $id)
    {
        $template = EmailTemplateModel::findOrFail($id);

        // Don't allow deleting system templates
        if ($template->is_system) {
            return back()->with('error', 'System templates cannot be deleted.');
        }

        $template->delete();

        return back()->with('success', 'Template deleted successfully!');
    }

    /**
     * Show analytics dashboard
     */
    public function analytics()
    {
        $campaigns = EmailCampaignModel::with('analytics')
            ->where('status', '!=', 'draft')
            ->get();

        $overallStats = [
            'total_campaigns' => $campaigns->count(),
            'active_campaigns' => $campaigns->where('status', 'active')->count(),
            'total_sent' => $campaigns->sum(fn($c) => $c->analytics->sum('emails_sent')),
            'total_opened' => $campaigns->sum(fn($c) => $c->analytics->sum('emails_opened')),
            'total_clicked' => $campaigns->sum(fn($c) => $c->analytics->sum('emails_clicked')),
            'avg_open_rate' => $campaigns->avg(fn($c) => $c->analytics->avg('open_rate')) ?? 0,
            'avg_click_rate' => $campaigns->avg(fn($c) => $c->analytics->avg('click_rate')) ?? 0,
        ];

        return Inertia::render('Admin/EmailMarketing/Analytics', [
            'campaigns' => $campaigns,
            'stats' => $overallStats,
        ]);
    }
}
