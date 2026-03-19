<?php

namespace App\Jobs\GrowBuilder;

use App\Models\AgencyClientInvoice;
use App\Notifications\GrowBuilder\InvoiceOverdueAlert;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckOverdueInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('CheckOverdueInvoices job started');

        // Get all unpaid/partial invoices that are past due date
        $overdueInvoices = AgencyClientInvoice::whereIn('payment_status', ['sent', 'partial'])
            ->whereDate('due_date', '<', Carbon::now())
            ->with(['client.agency.users', 'client'])
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Update status to overdue if not already
            if ($invoice->payment_status !== 'overdue') {
                $invoice->update(['payment_status' => 'overdue']);
            }

            // Calculate days overdue
            $daysOverdue = Carbon::now()->diffInDays($invoice->due_date);

            // Send notifications on specific days: 1, 7, 14, 30 days overdue
            if (in_array($daysOverdue, [1, 7, 14, 30])) {
                $agency = $invoice->client->agency;
                
                if ($agency && $agency->users) {
                    foreach ($agency->users as $user) {
                        $user->notify(new InvoiceOverdueAlert($invoice, $daysOverdue));
                    }
                }

                Log::info("Overdue invoice alert sent", [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client' => $invoice->client->client_name,
                    'days_overdue' => $daysOverdue,
                    'balance' => $invoice->balance,
                ]);
            }
        }

        Log::info('CheckOverdueInvoices job completed', [
            'total_overdue' => $overdueInvoices->count(),
        ]);
    }
}
