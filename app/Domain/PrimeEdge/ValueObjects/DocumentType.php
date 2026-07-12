<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum DocumentType: string
{
    case FINANCIAL_STATEMENT = 'financial_statement';
    case TAX_RETURN = 'tax_return';
    case TAX_CLEARANCE = 'tax_clearance';
    case BUSINESS_PLAN = 'business_plan';
    case FEASIBILITY_REPORT = 'feasibility_report';
    case INVOICE = 'invoice';
    case RECEIPT = 'receipt';
    case CONTRACT = 'contract';
    case REPORT = 'report';
    case CERTIFICATE = 'certificate';
    case CORPORATE_DOC = 'corporate_document';
    case ID_DOCUMENT = 'id_document';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FINANCIAL_STATEMENT => 'Financial Statement',
            self::TAX_RETURN => 'Tax Return',
            self::TAX_CLEARANCE => 'Tax Clearance',
            self::BUSINESS_PLAN => 'Business Plan',
            self::FEASIBILITY_REPORT => 'Feasibility Report',
            self::INVOICE => 'Invoice',
            self::RECEIPT => 'Receipt',
            self::CONTRACT => 'Contract',
            self::REPORT => 'Report',
            self::CERTIFICATE => 'Certificate',
            self::CORPORATE_DOC => 'Corporate Document',
            self::ID_DOCUMENT => 'ID Document',
            self::OTHER => 'Other',
        };
    }
}
