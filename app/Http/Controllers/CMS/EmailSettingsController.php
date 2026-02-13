<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Services\CMS\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class EmailSettingsController extends Controller
{
    public function __construct(
        private readonly EmailService $emailService
    ) {}

    public function index(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $company = CompanyModel::findOrFail($cmsUser->company_id);

        // Get email statistics
        $stats = $this->emailService->getEmailStats($company->id);

        return Inertia::render('CMS/Settings/Email', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'email_provider' => $company->email_provider ?? 'platform',
                'email_from_address' => $company->email_from_address,
                'email_from_name' => $company->email_from_name,
                'email_reply_to' => $company->email_reply_to,
                'smtp_host' => $company->smtp_host,
                'smtp_port' => $company->smtp_port,
                'smtp_username' => $company->smtp_username,
                'smtp_encryption' => $company->smtp_encryption ?? 'tls',
            ],
            'stats' => $stats,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_provider' => 'required|in:platform,custom',
            'email_from_address' => 'required_if:email_provider,custom|nullable|email',
            'email_from_name' => 'required_if:email_provider,custom|nullable|string|max:255',
            'email_reply_to' => 'nullable|email',
            'smtp_host' => 'required_if:email_provider,custom|nullable|string|max:255',
            'smtp_port' => 'required_if:email_provider,custom|nullable|integer|min:1|max:65535',
            'smtp_username' => 'required_if:email_provider,custom|nullable|string|max:255',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'required_if:email_provider,custom|nullable|in:tls,ssl,none',
        ]);

        $cmsUser = $request->user()->cmsUser;
        $company = CompanyModel::findOrFail($cmsUser->company_id);

        // Update company email settings
        $company->email_provider = $validated['email_provider'];
        $company->email_reply_to = $validated['email_reply_to'];

        if ($validated['email_provider'] === 'custom') {
            $company->email_from_address = $validated['email_from_address'];
            $company->email_from_name = $validated['email_from_name'];
            $company->smtp_host = $validated['smtp_host'];
            $company->smtp_port = $validated['smtp_port'];
            $company->smtp_username = $validated['smtp_username'];
            $company->smtp_encryption = $validated['smtp_encryption'];

            // Only update password if provided
            if (!empty($validated['smtp_password'])) {
                $company->smtp_password = Crypt::encryptString($validated['smtp_password']);
            }
        } else {
            // Clear custom SMTP settings when switching to platform
            $company->email_from_address = null;
            $company->email_from_name = null;
            $company->smtp_host = null;
            $company->smtp_port = null;
            $company->smtp_username = null;
            $company->smtp_password = null;
            $company->smtp_encryption = 'tls';
        }

        $company->save();

        return back()->with('success', 'Email settings updated successfully');
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_encryption' => 'required|in:tls,ssl,none',
        ]);

        $result = $this->emailService->testSmtpConnection($validated);

        return response()->json($result);
    }

    public function logs(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;

        $filters = [
            'type' => $request->type,
            'status' => $request->status,
            'search' => $request->search,
        ];

        $logs = $this->emailService->getEmailLogs($cmsUser->company_id, $filters);

        return Inertia::render('CMS/Settings/EmailLogs', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'company' => 'required|exists:cms_companies,id',
            'email' => 'required|email',
            'type' => 'required|in:all,marketing,reminders',
        ]);

        \App\Infrastructure\Persistence\Eloquent\CMS\EmailUnsubscribeModel::updateOrCreate(
            [
                'company_id' => $validated['company'],
                'email_address' => $validated['email'],
            ],
            [
                'unsubscribe_type' => $validated['type'],
                'unsubscribed_at' => now(),
            ]
        );

        return Inertia::render('CMS/EmailUnsubscribed', [
            'type' => $validated['type'],
        ]);
    }

    public function templates(Request $request): Response
    {
        $cmsUser = $request->user()->cmsUser;
        $companyId = $cmsUser->company_id;

        $templates = \App\Infrastructure\Persistence\Eloquent\CMS\EmailTemplateModel::where('company_id', $companyId)
            ->get();

        // Default templates structure
        $defaultTemplates = [
            'invoice_sent' => [
                'subject' => 'Invoice {{invoice_number}} from {{company_name}}',
                'body_html' => file_get_contents(resource_path('views/emails/cms/invoice-sent.blade.php')),
                'variables' => ['company_name', 'customer_name', 'invoice_number', 'invoice_date', 'due_date', 'total_amount', 'balance_due'],
            ],
            'payment_received' => [
                'subject' => 'Payment Received - Receipt #{{receipt_number}}',
                'body_html' => file_get_contents(resource_path('views/emails/cms/payment-received.blade.php')),
                'variables' => ['company_name', 'customer_name', 'receipt_number', 'payment_date', 'amount', 'payment_method'],
            ],
            'payment_reminder' => [
                'subject' => 'Payment Reminder: Invoice {{invoice_number}}',
                'body_html' => file_get_contents(resource_path('views/emails/cms/payment-reminder.blade.php')),
                'variables' => ['company_name', 'customer_name', 'invoice_number', 'due_date', 'balance_due', 'days_until_due'],
            ],
            'overdue_notice' => [
                'subject' => 'OVERDUE: Invoice {{invoice_number}}',
                'body_html' => file_get_contents(resource_path('views/emails/cms/overdue-notice.blade.php')),
                'variables' => ['company_name', 'customer_name', 'invoice_number', 'due_date', 'balance_due', 'days_overdue'],
            ],
        ];

        return Inertia::render('CMS/Settings/EmailTemplates', [
            'templates' => $templates,
            'defaultTemplates' => $defaultTemplates,
        ]);
    }

    public function updateTemplate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:500',
            'body_html' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $cmsUser = $request->user()->cmsUser;

        $template = \App\Infrastructure\Persistence\Eloquent\CMS\EmailTemplateModel::where('company_id', $cmsUser->company_id)
            ->findOrFail($id);

        $template->update($validated);

        return back()->with('success', 'Email template updated successfully');
    }
}
