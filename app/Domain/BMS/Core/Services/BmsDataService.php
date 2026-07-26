<?php

namespace App\Domain\BMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\BMS\ProductModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InventoryModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CmsUserModel;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BudgetModel;
use App\Infrastructure\Persistence\Eloquent\BMS\LoanReceivableModel;
use App\Infrastructure\Persistence\Eloquent\BMS\LoanRepaymentModel;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class BmsDataService
{
    public function getProducts(int $companyId, bool $activeOnly = true): Collection
    {
        $query = ProductModel::where('company_id', $companyId);
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        return $query->with('category')->get();
    }

    public function getProduct(int $companyId, int $productId): ?ProductModel
    {
        return ProductModel::where('company_id', $companyId)
            ->where('id', $productId)
            ->with('category')
            ->first();
    }

    public function findProduct(int $productId): ?ProductModel
    {
        return ProductModel::find($productId);
    }

    public function findOrFailCustomer(int $companyId, string $email): ?CustomerModel
    {
        return CustomerModel::where('company_id', $companyId)
            ->where('email', $email)
            ->first();
    }

    public function createCustomer(array $data): CustomerModel
    {
        return CustomerModel::create($data);
    }

    public function createInvoice(array $data): InvoiceModel
    {
        return InvoiceModel::create($data);
    }

    public function createInventoryMovement(array $data): InventoryModel
    {
        return InventoryModel::create($data);
    }

    public function findCompany(int $companyId): ?CompanyModel
    {
        return CompanyModel::find($companyId);
    }

    public function findCompanyByName(string $name): ?CompanyModel
    {
        return CompanyModel::where('name', $name)->first();
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        ProductModel::find($productId)?->decrement('stock_quantity', $quantity);
    }

    public function findCompanyOwner(int $companyId, string $role = 'owner'): ?CmsUserModel
    {
        return CmsUserModel::where('company_id', $companyId)
            ->where('role', $role)
            ->first();
    }

    public function findExpense(int $expenseId): ?ExpenseModel
    {
        return ExpenseModel::find($expenseId);
    }

    public function getUnsyncedApprovedExpenses(): Collection
    {
        return ExpenseModel::where('approval_status', 'approved')
            ->whereDoesntHave('transaction')
            ->get();
    }

    public function getActiveBudget(int $companyId, string $startDate, string $endDate): ?BudgetModel
    {
        return BudgetModel::where('company_id', $companyId)
            ->where('status', 'active')
            ->forPeriod($startDate, $endDate)
            ->with('items')
            ->first();
    }

    public function getLoansForCompany(int $companyId, string $status = 'all'): Collection
    {
        $query = LoanReceivableModel::forCompany($companyId)
            ->with(['user', 'repayments', 'schedules'])
            ->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getLoansQueryForCompany(int $companyId): Builder
    {
        return LoanReceivableModel::forCompany($companyId);
    }

    public function findLoanById(int $companyId, int $loanId): ?LoanReceivableModel
    {
        return LoanReceivableModel::forCompany($companyId)
            ->with(['user', 'repayments', 'schedules'])
            ->find($loanId);
    }

    public function getUserLoansForCompany(int $companyId, int $userId): Collection
    {
        return LoanReceivableModel::forCompany($companyId)
            ->where('user_id', $userId)
            ->with(['repayments', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOverdueLoansForCompany(int $companyId): Collection
    {
        return LoanReceivableModel::forCompany($companyId)
            ->overdue()
            ->with(['user'])
            ->orderBy('days_overdue', 'desc')
            ->get();
    }

    public function getLoansByRiskForCompany(int $companyId, string $riskCategory): Collection
    {
        return LoanReceivableModel::forCompany($companyId)
            ->byRisk($riskCategory)
            ->with(['user'])
            ->orderBy('outstanding_balance', 'desc')
            ->get();
    }
}
