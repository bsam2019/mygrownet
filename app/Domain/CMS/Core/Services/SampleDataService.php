<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\InvoiceItemModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleDataService
{
    public function generateSampleData(int $companyId, int $userId): array
    {
        return DB::transaction(function () use ($companyId, $userId) {
            $customers = $this->generateSampleCustomers($companyId, $userId);
            $jobs = $this->generateSampleJobs($companyId, $userId, $customers);
            $invoices = $this->generateSampleInvoices($companyId, $userId, $customers, $jobs);

            return [
                'customers' => count($customers),
                'jobs' => count($jobs),
                'invoices' => count($invoices),
            ];
        });
    }

    private function generateSampleCustomers(int $companyId, int $userId): array
    {
        $sampleCustomers = [
            [
                'name' => 'ABC Construction Ltd',
                'email' => 'contact@abcconstruction.zm',
                'phone' => '+260 211 123456',
                'type' => 'business',
                'address' => '123 Independence Avenue',
                'city' => 'Lusaka',
                'country' => 'Zambia',
            ],
            [
                'name' => 'John Mwansa',
                'email' => 'john.mwansa@email.com',
                'phone' => '+260 977 123456',
                'type' => 'individual',
                'address' => '45 Cairo Road',
                'city' => 'Lusaka',
                'country' => 'Zambia',
            ],
            [
                'name' => 'Green Valley Farms',
                'email' => 'info@greenvalley.zm',
                'phone' => '+260 211 789012',
                'type' => 'business',
                'address' => 'Plot 234, Great East Road',
                'city' => 'Lusaka',
                'country' => 'Zambia',
            ],
        ];

        $customers = [];
        foreach ($sampleCustomers as $data) {
            $customer = CustomerModel::create([
                'company_id' => $companyId,
                'customer_number' => 'CUST-' . str_pad(count($customers) + 1, 4, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'type' => $data['type'],
                'address' => $data['address'],
                'city' => $data['city'],
                'country' => $data['country'],
                'status' => 'active',
                'created_by' => $userId,
            ]);
            $customers[] = $customer;
        }

        return $customers;
    }

    private function generateSampleJobs(int $companyId, int $userId, array $customers): array
    {
        $sampleJobs = [
            [
                'title' => 'Office Renovation Project',
                'description' => 'Complete renovation of office space including painting, flooring, and electrical work',
                'status' => 'in_progress',
                'estimated_value' => 25000,
                'actual_value' => 25000,
                'material_cost' => 15000,
                'labor_cost' => 8000,
                'start_date' => now()->subDays(10),
                'deadline' => now()->addDays(20),
            ],
            [
                'title' => 'Website Development',
                'description' => 'Design and development of company website with CMS',
                'status' => 'completed',
                'estimated_value' => 8000,
                'actual_value' => 8500,
                'material_cost' => 2000,
                'labor_cost' => 6000,
                'start_date' => now()->subDays(45),
                'deadline' => now()->subDays(15),
                'completed_at' => now()->subDays(15),
            ],
            [
                'title' => 'Farm Equipment Maintenance',
                'description' => 'Quarterly maintenance of farm equipment and machinery',
                'status' => 'pending',
                'estimated_value' => 5000,
                'actual_value' => 5000,
                'material_cost' => 3000,
                'labor_cost' => 1500,
                'start_date' => now()->addDays(5),
                'deadline' => now()->addDays(15),
            ],
        ];

        $jobs = [];
        foreach ($sampleJobs as $index => $data) {
            $customer = $customers[$index % count($customers)];
            
            $job = JobModel::create([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'job_number' => 'JOB-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'],
                'priority' => 'medium',
                'estimated_value' => $data['estimated_value'],
                'actual_value' => $data['actual_value'],
                'material_cost' => $data['material_cost'],
                'labor_cost' => $data['labor_cost'],
                'total_cost' => $data['material_cost'] + $data['labor_cost'],
                'start_date' => $data['start_date'],
                'deadline' => $data['deadline'],
                'completed_at' => $data['completed_at'] ?? null,
                'assigned_to' => $userId,
                'created_by' => $userId,
            ]);
            $jobs[] = $job;
        }

        return $jobs;
    }

    private function generateSampleInvoices(int $companyId, int $userId, array $customers, array $jobs): array
    {
        $sampleInvoices = [
            [
                'customer_index' => 0,
                'job_index' => 0,
                'status' => 'partial',
                'items' => [
                    ['description' => 'Labor - Office Renovation', 'quantity' => 1, 'unit_price' => 8000],
                    ['description' => 'Materials - Paint and Flooring', 'quantity' => 1, 'unit_price' => 15000],
                    ['description' => 'Electrical Work', 'quantity' => 1, 'unit_price' => 2000],
                ],
                'amount_paid' => 15000,
                'due_date_offset' => 15,
            ],
            [
                'customer_index' => 1,
                'job_index' => 1,
                'status' => 'paid',
                'items' => [
                    ['description' => 'Website Design', 'quantity' => 1, 'unit_price' => 3500],
                    ['description' => 'Website Development', 'quantity' => 1, 'unit_price' => 4000],
                    ['description' => 'CMS Integration', 'quantity' => 1, 'unit_price' => 1000],
                ],
                'amount_paid' => 8500,
                'due_date_offset' => -10,
            ],
            [
                'customer_index' => 2,
                'job_index' => 2,
                'status' => 'sent',
                'items' => [
                    ['description' => 'Equipment Inspection', 'quantity' => 1, 'unit_price' => 1500],
                    ['description' => 'Parts Replacement', 'quantity' => 1, 'unit_price' => 3000],
                    ['description' => 'Labor', 'quantity' => 1, 'unit_price' => 500],
                ],
                'amount_paid' => 0,
                'due_date_offset' => 30,
            ],
        ];

        $invoices = [];
        foreach ($sampleInvoices as $index => $data) {
            $customer = $customers[$data['customer_index']];
            $job = $jobs[$data['job_index']];

            $subtotal = array_sum(array_map(fn($item) => $item['quantity'] * $item['unit_price'], $data['items']));
            $taxAmount = $subtotal * 0.16; // 16% VAT
            $totalAmount = $subtotal + $taxAmount;

            $invoice = InvoiceModel::create([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'job_id' => $job->id,
                'invoice_number' => 'INV-' . now()->format('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'invoice_date' => now()->subDays(5),
                'due_date' => now()->addDays($data['due_date_offset']),
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $data['amount_paid'],
                'amount_due' => $totalAmount - $data['amount_paid'],
                'status' => $data['status'],
                'notes' => 'Sample invoice generated during onboarding',
                'terms' => 'Payment due within 30 days',
                'created_by' => $userId,
            ]);

            // Create invoice items
            foreach ($data['items'] as $itemData) {
                InvoiceItemModel::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'amount' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            $invoices[] = $invoice;
        }

        return $invoices;
    }

    public function clearSampleData(int $companyId): bool
    {
        return DB::transaction(function () use ($companyId) {
            // Delete invoices and their items (cascade will handle items)
            InvoiceModel::where('company_id', $companyId)
                ->where('notes', 'LIKE', '%Sample invoice generated during onboarding%')
                ->delete();

            // Delete jobs
            JobModel::where('company_id', $companyId)
                ->whereIn('job_number', ['JOB-0001', 'JOB-0002', 'JOB-0003'])
                ->delete();

            // Delete customers
            CustomerModel::where('company_id', $companyId)
                ->whereIn('customer_number', ['CUST-0001', 'CUST-0002', 'CUST-0003'])
                ->delete();

            return true;
        });
    }
}
