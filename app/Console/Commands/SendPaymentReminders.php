<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Services\CMS\EmailService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentReminders extends Command
{
    protected $signature = 'cms:send-payment-reminders';
    protected $description = 'Send automated payment reminders for upcoming and overdue invoices';

    public function __construct(
        private readonly EmailService $emailService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting payment reminder process...');

        $remindersSent = 0;
        $overdueNoticesSent = 0;

        // 1. Send reminders for invoices due in 3 days
        $upcomingInvoices = InvoiceModel::whereIn('status', ['sent', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '=', Carbon::today()->addDays(3))
            ->with(['customer', 'company'])
            ->get();

        foreach ($upcomingInvoices as $invoice) {
            if ($this->sendReminder($invoice, 'upcoming', 3)) {
                $remindersSent++;
            }
        }

        $this->info("Sent {$remindersSent} upcoming payment reminders (3 days before due)");

        // 2. Send reminders for invoices due today
        $dueTodayInvoices = InvoiceModel::whereIn('status', ['sent', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '=', Carbon::today())
            ->with(['customer', 'company'])
            ->get();

        $dueTodayCount = 0;
        foreach ($dueTodayInvoices as $invoice) {
            if ($this->sendReminder($invoice, 'due_today', 0)) {
                $dueTodayCount++;
            }
        }

        $this->info("Sent {$dueTodayCount} due today reminders");

        // 3. Send overdue notices for invoices 3, 7, 14, 30 days overdue
        $overdueThresholds = [3, 7, 14, 30];
        
        foreach ($overdueThresholds as $days) {
            $overdueInvoices = InvoiceModel::whereIn('status', ['sent', 'partial', 'overdue'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '=', Carbon::today()->subDays($days))
                ->with(['customer', 'company'])
                ->get();

            foreach ($overdueInvoices as $invoice) {
                if ($this->sendOverdueNotice($invoice, $days)) {
                    $overdueNoticesSent++;
                }
            }
        }

        $this->info("Sent {$overdueNoticesSent} overdue notices");

        $total = $remindersSent + $dueTodayCount + $overdueNoticesSent;
        $this->info("Total emails sent: {$total}");

        Log::info('Payment reminders completed', [
            'upcoming_reminders' => $remindersSent,
            'due_today_reminders' => $dueTodayCount,
            'overdue_notices' => $overdueNoticesSent,
            'total' => $total,
        ]);

        return Command::SUCCESS;
    }

    private function sendReminder(InvoiceModel $invoice, string $type, int $daysUntilDue): bool
    {
        $customer = $invoice->customer;
        
        if (!$customer || !$customer->email) {
            return false;
        }

        try {
            $sent = $this->emailService->sendEmail(
                company: $invoice->company,
                to: $customer->email,
                subject: $this->getReminderSubject($invoice, $type),
                view: 'emails.cms.payment-reminder',
                data: [
                    'invoice' => $invoice,
                    'company' => $invoice->company,
                    'customer' => $customer,
                    'reminderType' => $type,
                    'daysUntilDue' => $daysUntilDue,
                    'recipient_name' => $customer->name,
                ],
                emailType: 'reminder',
                referenceType: 'invoice',
                referenceId: $invoice->id
            );

            if ($sent) {
                $this->line("✓ Sent {$type} reminder to {$customer->email} for invoice {$invoice->invoice_number}");
            }

            return $sent;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send reminder for invoice {$invoice->invoice_number}: {$e->getMessage()}");
            Log::error('Payment reminder failed', [
                'invoice_id' => $invoice->id,
                'customer_email' => $customer->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function sendOverdueNotice(InvoiceModel $invoice, int $daysOverdue): bool
    {
        $customer = $invoice->customer;
        
        if (!$customer || !$customer->email) {
            return false;
        }

        try {
            $sent = $this->emailService->sendEmail(
                company: $invoice->company,
                to: $customer->email,
                subject: "OVERDUE: Invoice {$invoice->invoice_number} - {$daysOverdue} Days Past Due",
                view: 'emails.cms.overdue-notice',
                data: [
                    'invoice' => $invoice,
                    'company' => $invoice->company,
                    'customer' => $customer,
                    'daysOverdue' => $daysOverdue,
                    'recipient_name' => $customer->name,
                ],
                emailType: 'overdue',
                referenceType: 'invoice',
                referenceId: $invoice->id
            );

            if ($sent) {
                $this->line("✓ Sent overdue notice ({$daysOverdue} days) to {$customer->email} for invoice {$invoice->invoice_number}");
                
                // Update invoice status to overdue if not already
                if ($invoice->status !== 'overdue') {
                    $invoice->update(['status' => 'overdue']);
                }
            }

            return $sent;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send overdue notice for invoice {$invoice->invoice_number}: {$e->getMessage()}");
            Log::error('Overdue notice failed', [
                'invoice_id' => $invoice->id,
                'customer_email' => $customer->email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function getReminderSubject(InvoiceModel $invoice, string $type): string
    {
        return match($type) {
            'upcoming' => "Payment Reminder: Invoice {$invoice->invoice_number} Due Soon",
            'due_today' => "Payment Due Today: Invoice {$invoice->invoice_number}",
            default => "Payment Reminder: Invoice {$invoice->invoice_number}",
        };
    }
}
