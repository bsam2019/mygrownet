<?php

return [
    'id' => 'primeedge',
    'name' => 'PrimeEdge Advisory',
    'slug' => 'primeedge',
    'description' => 'Finance, compliance, and business advisory for SMEs',
    'status' => 'active',
    'version' => '1.0.0',
    'icon' => 'BriefcaseIcon',
    'color' => 'emerald',

    'services' => [
        'accounting_compliance' => [
            'label' => 'Accounting & Compliance',
            'items' => [
                ['type' => 'bookkeeping', 'label' => 'Bookkeeping & Accounting'],
                ['type' => 'financial_statements', 'label' => 'Financial Statement Preparation'],
                ['type' => 'tax_registration', 'label' => 'Tax Registration'],
                ['type' => 'tax_return', 'label' => 'Tax Return Preparation'],
                ['type' => 'payroll', 'label' => 'Payroll Processing (PAYE, NAPSA, NHIMA)'],
                ['type' => 'business_registration', 'label' => 'Business Registration'],
                ['type' => 'annual_return', 'label' => 'Annual Returns Filing'],
                ['type' => 'cash_flow_management', 'label' => 'Cash Flow Management'],
                ['type' => 'accounting_setup', 'label' => 'Accounting Software Setup'],
            ],
        ],
        'business_advisory' => [
            'label' => 'Business Advisory',
            'items' => [
                ['type' => 'business_plan', 'label' => 'Business Plans'],
                ['type' => 'feasibility_study', 'label' => 'Feasibility Studies'],
                ['type' => 'financial_projections', 'label' => 'Financial Projections'],
                ['type' => 'loan_support', 'label' => 'Loan Application Support'],
                ['type' => 'investment_readiness', 'label' => 'Investment Readiness'],
                ['type' => 'cost_reduction', 'label' => 'Cost Reduction Analysis'],
                ['type' => 'internal_controls', 'label' => 'Internal Controls'],
            ],
        ],
        'personal_financial' => [
            'label' => 'Personal Financial Advisory',
            'items' => [
                ['type' => 'retirement_planning', 'label' => 'Retirement Planning'],
                ['type' => 'personal_budgeting', 'label' => 'Personal Budgeting'],
                ['type' => 'debt_management', 'label' => 'Debt Management'],
                ['type' => 'financial_goal_planning', 'label' => 'Financial Goal Planning'],
                ['type' => 'financial_literacy', 'label' => 'Financial Literacy Training'],
            ],
        ],
        'additional_support' => [
            'label' => 'Additional Support',
            'items' => [
                ['type' => 'stocktaking', 'label' => 'Private Stocktaking'],
                ['type' => 'asset_verification', 'label' => 'Asset Verification'],
                ['type' => 'financial_due_diligence', 'label' => 'Financial Due Diligence'],
                ['type' => 'grant_budget', 'label' => 'Grant Proposal Budgets'],
                ['type' => 'tender_documentation', 'label' => 'Tender Documentation'],
                ['type' => 'management_report', 'label' => 'Management Reports'],
            ],
        ],
    ],

    'currencies' => ['ZMW', 'USD'],
    'default_currency' => 'ZMW',

    'compliance' => [
        'default_reminder_days' => 7,
        'process_overdue_hours' => 24,
    ],
];
