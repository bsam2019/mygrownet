<?php

namespace App\Console\Commands;

use App\Domain\Financial\Services\TransactionIntegrityService;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Enums\TransactionStatus;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MigratePaymentsToTransactions extends Command
{
    protected $signature = 'finance:migrate-payments 
                            {--dry-run : Run without making changes}
                            {--user= : Migrate payments for specific user ID}';

    protected $description = 'Migrate verified payments from member_payments to transactions table';

    private TransactionIntegrityService $transactionService;
    private int $migrated = 0;
    private int $skipped = 0;
    private int $errors = 0;

    public function __construct(TransactionIntegrityService $transactionService)
    {
        parent::__construct();
        $this->transactionService = $transactionService;
    }

    public function handle(): int
    {
        $this->info('Starting payment migration to transactions table...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Get all verified payments that don't have corresponding transactions
        $query = MemberPaymentModel::where('status', 'verified');
        
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("Filtering for user ID: {$userId}");
        }

        $payments = $query->orderBy('created_at')->get();

        $this->info("Found {$payments->count()} verified payments to process");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($payments->count());
        $progressBar->start();

        foreach ($payments as $payment) {
            $progressBar->advance();
            
            try {
                $this->migratePayment($payment, $dryRun);
            } catch (\Exception $e) {
                $this->errors++;
                $this->error("\nError migrating payment {$payment->id}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->displaySummary($dryRun);

        return Command::SUCCESS;
    }

    private function migratePayment(MemberPaymentModel $payment, bool $dryRun): void
    {
        // Check if transaction already exists for this payment
        $existingTransaction = Transaction::where('reference_number', 'LIKE', "payment_{$payment->id}_%")
            ->orWhere(function($query) use ($payment) {
                $query->where('user_id', $payment->user_id)
                      ->where('amount', $payment->amount)
                      ->where('created_at', '>=', $payment->created_at->subMinutes(5))
                      ->where('created_at', '<=', $payment->created_at->addMinutes(5));
            })
            ->first();

        if ($existingTransaction) {
            $this->skipped++;
            return;
        }

        // Get user
        $user = User::find($payment->user_id);
        if (!$user) {
            $this->skipped++;
            return;
        }

        // Map payment type to transaction type
        $transactionType = $this->mapPaymentTypeToTransactionType($payment->payment_type, $payment->amount);
        
        if (!$transactionType) {
            $this->skipped++;
            return;
        }

        if ($dryRun) {
            $this->migrated++;
            return;
        }

        // Create transaction record
        $reference = "payment_{$payment->id}_migrated_" . time();

        $transaction = $this->transactionService->recordWalletCredit(
            user: $user,
            amount: $payment->amount,
            type: $transactionType->value,
            description: $this->generateDescription($payment->payment_type, $payment->amount),
            reference: $reference
        );

        // Update transaction with additional metadata
        $transaction->update([
            'transaction_source' => $this->determineTransactionSource($payment->payment_type),
            'notes' => json_encode([
                'payment_id' => $payment->id,
                'payment_type' => $payment->payment_type,
                'payment_reference' => $payment->payment_reference,
                'verified_by' => $payment->verified_by,
                'verified_at' => $payment->verified_at?->format('Y-m-d H:i:s'),
                'source' => 'historical_migration',
                'migrated_at' => now()->format('Y-m-d H:i:s'),
            ]),
            'created_at' => $payment->created_at, // Use original payment date
        ]);

        // Clear wallet cache for this user
        Cache::forget("wallet_balance_{$user->id}");
        Cache::forget("wallet_breakdown_{$user->id}");

        $this->migrated++;
    }

    private function mapPaymentTypeToTransactionType(string $paymentType, float $amount): ?TransactionType
    {
        return match($paymentType) {
            'wallet_topup' => TransactionType::WALLET_TOPUP,
            'subscription' => TransactionType::SUBSCRIPTION_PAYMENT,
            'workshop' => TransactionType::WORKSHOP_PAYMENT,
            'learning_pack' => TransactionType::LEARNING_PACK_PURCHASE,
            'coaching' => TransactionType::COACHING_PAYMENT,
            'upgrade' => TransactionType::SUBSCRIPTION_PAYMENT,
            'product' => $this->determineProductTransactionType($amount),
            default => null,
        };
    }

    private function determineProductTransactionType(float $amount): TransactionType
    {
        $starterKitAmounts = [300, 500, 1000, 2000];
        
        if (in_array($amount, $starterKitAmounts)) {
            return TransactionType::STARTER_KIT_PURCHASE;
        }
        
        return TransactionType::SHOP_PURCHASE;
    }

    private function determineTransactionSource(string $paymentType): string
    {
        return match($paymentType) {
            'wallet_topup' => 'wallet',
            'subscription' => 'platform',
            'workshop' => 'workshops',
            'learning_pack' => 'learning',
            'coaching' => 'coaching',
            'upgrade' => 'platform',
            'product' => 'shop',
            default => 'platform',
        };
    }

    private function generateDescription(string $paymentType, float $amount): string
    {
        return match($paymentType) {
            'wallet_topup' => "Wallet top-up - K" . number_format($amount, 2),
            'subscription' => "Platform subscription payment - K" . number_format($amount, 2),
            'workshop' => "Workshop registration payment - K" . number_format($amount, 2),
            'learning_pack' => "Learning pack purchase - K" . number_format($amount, 2),
            'coaching' => "Coaching session payment - K" . number_format($amount, 2),
            'upgrade' => "Subscription upgrade - K" . number_format($amount, 2),
            'product' => "Product purchase - K" . number_format($amount, 2),
            default => "Payment - K" . number_format($amount, 2),
        };
    }

    private function displaySummary(bool $dryRun): void
    {
        $this->info('Migration Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Migrated', $this->migrated],
                ['Skipped (already exists)', $this->skipped],
                ['Errors', $this->errors],
                ['Total', $this->migrated + $this->skipped + $this->errors],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN - no changes were made');
            $this->info('Run without --dry-run to perform actual migration');
        } else {
            $this->newLine();
            $this->info('Migration completed successfully!');
            $this->info('Wallet caches have been cleared for affected users');
        }
    }
}

