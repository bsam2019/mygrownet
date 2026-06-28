<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\MarketingEmail;
use App\Jobs\SendBulkEmails;

class EmailController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Email/Index', [
            'templates' => EmailTemplate::latest()->paginate(10),
            'campaigns' => EmailCampaign::with('template')
                ->latest()
                ->paginate(10),
            'stats' => [
                'total_sent' => EmailCampaign::sum('emails_sent'),
                'total_opened' => EmailCampaign::sum('opens'),
                'total_clicked' => EmailCampaign::sum('clicks')
            ]
        ]);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:marketing,notification,transactional'
        ]);

        EmailTemplate::create($validated);

        return back()->with('success', 'Email template created successfully');
    }

    public function updateTemplate(EmailTemplate $template, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:marketing,notification,transactional'
        ]);

        $template->update($validated);

        return back()->with('success', 'Email template updated successfully');
    }

    public function previewTemplate(EmailTemplate $template)
    {
        // Sample data for preview
        $sampleData = [
            'user' => User::first(),
            'investment_amount' => 1000,
            'return_rate' => 10,
            'company_name' => config('app.name')
        ];

        $renderedContent = $this->renderTemplate($template->content, $sampleData);

        return response()->json([
            'subject' => $template->subject,
            'content' => $renderedContent
        ]);
    }

    public function createCampaign(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template_id' => 'required|exists:email_templates,id',
            'target_audience' => 'required|in:all,active_investors,inactive_users,referrers',
            'scheduled_at' => 'required|date|after:now',
            'custom_data' => 'nullable|array'
        ]);

        $campaign = EmailCampaign::create([
            ...$validated,
            'status' => 'scheduled'
        ]);

        return back()->with('success', 'Email campaign scheduled successfully');
    }

    public function executeCampaign(EmailCampaign $campaign)
    {
        if ($campaign->status !== 'scheduled') {
            return back()->with('error', 'Campaign cannot be executed');
        }

        $recipients = $this->getRecipients($campaign->target_audience);
        $template = $campaign->template;

        $campaign->update([
            'status' => 'processing',
            'started_at' => now()
        ]);

        // Split recipients into chunks and dispatch jobs
        $recipients->chunk(100, function($chunk) use ($campaign, $template) {
            SendBulkEmails::dispatch($chunk, $template, $campaign);
        });

        return back()->with('success', 'Campaign execution started');
    }

    public function campaignStats(EmailCampaign $campaign)
    {
        return Inertia::render('Admin/Email/CampaignStats', [
            'campaign' => $campaign,
            'stats' => [
                'total_recipients' => $campaign->total_recipients,
                'emails_sent' => $campaign->emails_sent,
                'opens' => $campaign->opens,
                'clicks' => $campaign->clicks,
                'bounces' => $campaign->bounces,
                'open_rate' => $campaign->calculateOpenRate(),
                'click_rate' => $campaign->calculateClickRate()
            ],
            'timeline' => $campaign->timeline()->latest()->take(50)->get()
        ]);
    }

    private function getRecipients($targetAudience)
    {
        return match($targetAudience) {
            'all' => User::where('email_subscribed', true)->get(),
            'active_investors' => User::whereHas('investments', function($query) {
                $query->where('status', 'active');
            })->where('email_subscribed', true)->get(),
            'inactive_users' => User::whereDoesntHave('investments')
                ->where('email_subscribed', true)
                ->where('created_at', '<=', now()->subDays(30))
                ->get(),
            'referrers' => User::where('referrals_count', '>', 0)
                ->where('email_subscribed', true)
                ->get()
        };
    }

    private function renderTemplate($content, $data)
    {
        // Replace placeholders with actual data
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                foreach (get_object_vars($value) as $prop => $val) {
                    $content = str_replace(
                        "{{" . $key . "." . $prop . "}}",
                        $val,
                        $content
                    );
                }
            } else {
                $content = str_replace("{{".$key."}}", $value, $content);
            }
        }

        return $content;
    }
}
