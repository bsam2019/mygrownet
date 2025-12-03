<?php

namespace Database\Seeders;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceItemModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceInvoiceModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GrowFinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@mygrownet.com'],
            [
                'name' => 'Demo Business Owner',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $businessId = $user->id;

        // Initialize chart of accounts
        $accountingService = app(AccountingService::class);
        $accountingService->initializeChartOfAccounts($businessId);

        // Create demo customers
        $customers = $this->createCustomers($businessId);

        // Create demo vendors
        $vendors = $this->createVendors($businessId);

        // Create demo invoices
        $this->createInvoices($businessId, $customers);

        // Create demo expenses
        $this->createExpenses($businessId, $vendors);

        // Update account balances
        $this->updateAccountBalances($businessId);

        $this->command->info('GrowFinance demo data seeded successfully!');
    }

    private function createCustomers(int $businessId): array
    {
        $customersData = [
            ['name' => 'Shoprite Zambia', 'email' => 'orders@shoprite.zm', 'phone' => '+260 211 123456', 'credit_limit' => 50000],
            ['name' => 'Pick n Pay', 'email' => 'procurement@picknpay.zm', 'phone' => '+260 211 234567', 'credit_limit' => 30000],
            ['name' => 'Melissa Banda', 'email' => 'melissa.banda@email.com', 'phone' => '+260 977 111222', 'credit_limit' => 5000],
            ['name' => 'John Mwanza', 'email' => 'john.mwanza@email.com', 'phone' => '+260 966 333444', 'credit_limit' => 3000],
            ['name' => 'Grace Phiri', 'email' => 'grace.phiri@email.com', 'phone' => '+260 955 555666', 'credit_limit' => 2000],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $customers[] = GrowFinanceCustomerModel::create([
                'business_id' => $businessId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'credit_limit' => $data['credit_limit'],
                'outstanding_balance' => 0,
                'is_active' => true,
            ]);
        }

        return $customers;
    }

    private function createVendors(int $businessId): array
    {
        $vendorsData = [
            ['name' => 'Trade Kings Ltd', 'email' => 'sales@tradekings.zm', 'phone' => '+260 211 345678', 'payment_terms' => 'Net 30'],
            ['name' => 'Zambeef Products', 'email' => 'orders@zambeef.zm', 'phone' => '+260 211 456789', 'payment_terms' => 'Net 14'],
            ['name' => 'ZESCO Limited', 'email' => 'billing@zesco.co.zm', 'phone' => '+260 211 567890', 'payment_terms' => 'Due on receipt'],
            ['name' => 'Airtel Zambia', 'email' => 'business@airtel.zm', 'phone' => '+260 211 678901', 'payment_terms' => 'Prepaid'],
            ['name' => 'Landlord Properties', 'email' => 'rent@landlord.zm', 'phone' => '+260 977 789012', 'payment_terms' => 'Monthly'],
        ];

        $vendors = [];
        foreach ($vendorsData as $data) {
            $vendors[] = GrowFinanceVendorModel::create([
                'business_id' => $businessId,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'payment_terms' => $data['payment_terms'],
                'outstanding_balance' => 0,
                'is_active' => true,
            ]);
        }

        return $vendors;
    }

    private function createInvoices(int $businessId, array $customers): void
    {
        $invoiceNumber = 1;

        foreach ($customers as $customer) {
            // Create 2-3 invoices per customer
            $numInvoices = rand(2, 3);

            for ($i = 0; $i < $numInvoices; $i++) {
                $invoiceDate = Carbon::now()->subDays(rand(1, 60));
                $dueDate = $invoiceDate->copy()->addDays(30);
                $status = $this->getRandomStatus($dueDate);

                $invoice = GrowFinanceInvoiceModel::create([
                    'business_id' => $businessId,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-' . str_pad($invoiceNumber++, 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 0,
                    'amount_paid' => 0,
                ]);

                // Add 1-4 line items
                $numItems = rand(1, 4);
                $subtotal = 0;

                for ($j = 0; $j < $numItems; $j++) {
                    $quantity = rand(1, 10);
                    $unitPrice = rand(50, 500) * 10; // K500 to K5000
                    $lineTotal = $quantity * $unitPrice;
                    $subtotal += $lineTotal;

                    GrowFinanceInvoiceItemModel::create([
                        'invoice_id' => $invoice->id,
                        'description' => $this->getRandomProduct(),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax_rate' => 16,
                        'discount_rate' => 0,
                        'line_total' => $lineTotal,
                    ]);
                }

                $taxAmount = $subtotal * 0.16;
                $totalAmount = $subtotal + $taxAmount;
                $amountPaid = $status === 'paid' ? $totalAmount : ($status === 'partial' ? $totalAmount * 0.5 : 0);

                $invoice->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                ]);

                // Update customer outstanding balance
                if ($amountPaid < $totalAmount) {
                    $customer->outstanding_balance += ($totalAmount - $amountPaid);
                    $customer->save();
                }
            }
        }
    }

    private function createExpenses(int $businessId, array $vendors): void
    {
        $expenseCategories = [
            'Rent' => ['min' => 3000, 'max' => 8000, 'recurring' => true],
            'Utilities' => ['min' => 500, 'max' => 2000, 'recurring' => true],
            'Salaries' => ['min' => 5000, 'max' => 15000, 'recurring' => true],
            'Transport' => ['min' => 200, 'max' => 1000, 'recurring' => false],
            'Office Supplies' => ['min' => 100, 'max' => 500, 'recurring' => false],
            'Marketing' => ['min' => 500, 'max' => 3000, 'recurring' => false],
            'Inventory Purchase' => ['min' => 2000, 'max' => 10000, 'recurring' => false],
        ];

        $expenseAccount = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('type', AccountType::EXPENSE->value)
            ->first();

        foreach ($expenseCategories as $category => $config) {
            // Create 1-3 expenses per category
            $numExpenses = rand(1, 3);

            for ($i = 0; $i < $numExpenses; $i++) {
                $amount = rand($config['min'], $config['max']);
                $vendor = $vendors[array_rand($vendors)];

                GrowFinanceExpenseModel::create([
                    'business_id' => $businessId,
                    'vendor_id' => $vendor->id,
                    'account_id' => $expenseAccount?->id,
                    'expense_date' => Carbon::now()->subDays(rand(1, 45)),
                    'category' => $category,
                    'description' => $category . ' - ' . $vendor->name,
                    'amount' => $amount,
                    'tax_amount' => $amount * 0.16,
                    'payment_method' => $this->getRandomPaymentMethod(),
                    'reference' => 'EXP-' . strtoupper(substr(md5(rand()), 0, 6)),
                    'is_recurring' => $config['recurring'],
                ]);
            }
        }
    }

    private function updateAccountBalances(int $businessId): void
    {
        // Calculate total income from paid invoices
        $totalIncome = GrowFinanceInvoiceModel::forBusiness($businessId)->sum('amount_paid');

        // Calculate total expenses
        $totalExpenses = GrowFinanceExpenseModel::forBusiness($businessId)->sum('amount');

        // Update cash account
        $cashAccount = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('code', '1000')
            ->first();

        if ($cashAccount) {
            $cashAccount->current_balance = $totalIncome - $totalExpenses;
            $cashAccount->save();
        }

        // Update accounts receivable
        $arAccount = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('code', '1100')
            ->first();

        if ($arAccount) {
            $outstandingInvoices = GrowFinanceInvoiceModel::forBusiness($businessId)
                ->selectRaw('SUM(total_amount - amount_paid) as outstanding')
                ->first();
            $arAccount->current_balance = $outstandingInvoices->outstanding ?? 0;
            $arAccount->save();
        }

        // Update sales revenue
        $salesAccount = GrowFinanceAccountModel::forBusiness($businessId)
            ->where('code', '4000')
            ->first();

        if ($salesAccount) {
            $salesAccount->current_balance = $totalIncome;
            $salesAccount->save();
        }
    }

    private function getRandomStatus(Carbon $dueDate): string
    {
        $statuses = ['draft', 'sent', 'paid', 'partial'];

        if ($dueDate->isPast()) {
            $statuses[] = 'overdue';
        }

        return $statuses[array_rand($statuses)];
    }

    private function getRandomPaymentMethod(): string
    {
        $methods = ['cash', 'bank', 'mobile_money'];
        return $methods[array_rand($methods)];
    }

    private function getRandomProduct(): string
    {
        $products = [
            'Cooking Oil (5L)',
            'Mealie Meal (25kg)',
            'Sugar (2kg)',
            'Rice (10kg)',
            'Bread Flour (10kg)',
            'Tomato Sauce (500ml)',
            'Washing Powder (2kg)',
            'Soap Bars (12 pack)',
            'Bottled Water (24 pack)',
            'Soft Drinks (24 pack)',
            'Canned Beans (12 pack)',
            'Tea Bags (100 pack)',
            'Coffee (500g)',
            'Milk Powder (1kg)',
            'Biscuits (Assorted)',
        ];

        return $products[array_rand($products)];
    }
}
