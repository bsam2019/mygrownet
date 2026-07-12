<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum EngagementType: string
{
    case BOOKKEEPING = 'bookkeeping';
    case FINANCIAL_STATEMENTS = 'financial_statements';
    case TAX_REGISTRATION = 'tax_registration';
    case TAX_RETURN = 'tax_return';
    case PAYROLL = 'payroll';
    case BUSINESS_REGISTRATION = 'business_registration';
    case ANNUAL_RETURN = 'annual_return';
    case CASH_FLOW_MANAGEMENT = 'cash_flow_management';
    case ACCOUNTING_SETUP = 'accounting_setup';
    case BUSINESS_PLAN = 'business_plan';
    case FEASIBILITY_STUDY = 'feasibility_study';
    case FINANCIAL_PROJECTIONS = 'financial_projections';
    case LOAN_SUPPORT = 'loan_support';
    case INVESTMENT_READINESS = 'investment_readiness';
    case COST_REDUCTION = 'cost_reduction';
    case INTERNAL_CONTROLS = 'internal_controls';
    case RETIREMENT_PLANNING = 'retirement_planning';
    case PERSONAL_BUDGETING = 'personal_budgeting';
    case DEBT_MANAGEMENT = 'debt_management';
    case FINANCIAL_GOAL_PLANNING = 'financial_goal_planning';
    case FINANCIAL_LITERACY = 'financial_literacy';
    case STOCKTAKING = 'stocktaking';
    case ASSET_VERIFICATION = 'asset_verification';
    case FINANCIAL_DUE_DILIGENCE = 'financial_due_diligence';
    case GRANT_BUDGET = 'grant_budget';
    case TENDER_DOCUMENTATION = 'tender_documentation';
    case MANAGEMENT_REPORT = 'management_report';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BOOKKEEPING => 'Bookkeeping',
            self::FINANCIAL_STATEMENTS => 'Financial Statements',
            self::TAX_REGISTRATION => 'Tax Registration',
            self::TAX_RETURN => 'Tax Return Filing',
            self::PAYROLL => 'Payroll Processing',
            self::BUSINESS_REGISTRATION => 'Business Registration',
            self::ANNUAL_RETURN => 'Annual Return Filing',
            self::CASH_FLOW_MANAGEMENT => 'Cash Flow Management',
            self::ACCOUNTING_SETUP => 'Accounting Software Setup',
            self::BUSINESS_PLAN => 'Business Plan',
            self::FEASIBILITY_STUDY => 'Feasibility Study',
            self::FINANCIAL_PROJECTIONS => 'Financial Projections',
            self::LOAN_SUPPORT => 'Loan Application Support',
            self::INVESTMENT_READINESS => 'Investment Readiness',
            self::COST_REDUCTION => 'Cost Reduction Analysis',
            self::INTERNAL_CONTROLS => 'Internal Controls',
            self::RETIREMENT_PLANNING => 'Retirement Planning',
            self::PERSONAL_BUDGETING => 'Personal Budgeting',
            self::DEBT_MANAGEMENT => 'Debt Management',
            self::FINANCIAL_GOAL_PLANNING => 'Financial Goal Planning',
            self::FINANCIAL_LITERACY => 'Financial Literacy Training',
            self::STOCKTAKING => 'Private Stocktaking',
            self::ASSET_VERIFICATION => 'Asset Verification',
            self::FINANCIAL_DUE_DILIGENCE => 'Financial Due Diligence',
            self::GRANT_BUDGET => 'Grant Proposal Budget',
            self::TENDER_DOCUMENTATION => 'Tender Documentation',
            self::MANAGEMENT_REPORT => 'Management Report',
            self::OTHER => 'Other',
        };
    }

    public function category(): ServiceCategory
    {
        return match ($this) {
            self::BOOKKEEPING, self::FINANCIAL_STATEMENTS, self::TAX_REGISTRATION,
            self::TAX_RETURN, self::PAYROLL, self::BUSINESS_REGISTRATION,
            self::ANNUAL_RETURN, self::CASH_FLOW_MANAGEMENT, self::ACCOUNTING_SETUP
                => ServiceCategory::ACCOUNTING_COMPLIANCE,

            self::BUSINESS_PLAN, self::FEASIBILITY_STUDY, self::FINANCIAL_PROJECTIONS,
            self::LOAN_SUPPORT, self::INVESTMENT_READINESS, self::COST_REDUCTION,
            self::INTERNAL_CONTROLS, self::MANAGEMENT_REPORT
                => ServiceCategory::BUSINESS_ADVISORY,

            self::RETIREMENT_PLANNING, self::PERSONAL_BUDGETING, self::DEBT_MANAGEMENT,
            self::FINANCIAL_GOAL_PLANNING, self::FINANCIAL_LITERACY
                => ServiceCategory::PERSONAL_FINANCIAL,

            self::STOCKTAKING, self::ASSET_VERIFICATION, self::FINANCIAL_DUE_DILIGENCE,
            self::GRANT_BUDGET, self::TENDER_DOCUMENTATION
                => ServiceCategory::ADDITIONAL_SUPPORT,

            self::OTHER => ServiceCategory::ADDITIONAL_SUPPORT,
        };
    }

    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
