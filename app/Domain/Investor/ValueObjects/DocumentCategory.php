<?php

namespace App\Domain\Investor\ValueObjects;

class DocumentCategory
{
    private const VALID_CATEGORIES = [
        'investment_agreements' => [
            'label' => 'Investment Agreements',
            'icon' => 'document-text',
            'description' => 'Investment agreements and legal documents'
        ],
        'financial_reports' => [
            'label' => 'Financial Reports',
            'icon' => 'chart-bar',
            'description' => 'Quarterly and annual financial reports'
        ],
        'tax_documents' => [
            'label' => 'Tax Documents',
            'icon' => 'calculator',
            'description' => 'Tax forms and dividend statements'
        ],
        'company_updates' => [
            'label' => 'Company Updates',
            'icon' => 'newspaper',
            'description' => 'Company announcements and updates'
        ],
        'governance' => [
            'label' => 'Governance',
            'icon' => 'scale',
            'description' => 'Board meeting minutes and governance documents'
        ],
        'certificates' => [
            'label' => 'Certificates',
            'icon' => 'academic-cap',
            'description' => 'Investment certificates and confirmations'
        ],
    ];

    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (!array_key_exists($value, self::VALID_CATEGORIES)) {
            throw new \InvalidArgumentException("Invalid document category: {$value}");
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::VALID_CATEGORIES[$this->value]['label'];
    }

    public function icon(): string
    {
        return self::VALID_CATEGORIES[$this->value]['icon'];
    }

    public function description(): string
    {
        return self::VALID_CATEGORIES[$this->value]['description'];
    }

    public static function all(): array
    {
        return array_map(
            fn($key) => self::fromString($key),
            array_keys(self::VALID_CATEGORIES)
        );
    }

    public static function getAllWithDetails(): array
    {
        $categories = [];
        foreach (self::VALID_CATEGORIES as $key => $details) {
            $categories[] = [
                'value' => $key,
                'label' => $details['label'],
                'icon' => $details['icon'],
                'description' => $details['description'],
            ];
        }
        return $categories;
    }

    public function equals(DocumentCategory $other): bool
    {
        return $this->value === $other->value;
    }
}