<?php

namespace App\Domain\BizDocs\BusinessIdentity\Entities;

class BusinessProfile
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private string $businessName,
        private string $address,
        private string $phone,
        private ?string $email,
        private ?string $logo,
        private ?string $tpin,
        private ?string $website,
        private ?string $bankName,
        private ?string $bankAccount,
        private ?string $bankBranch,
        private string $defaultCurrency,
        private float $defaultTaxRate,
        private ?string $defaultTerms,
        private ?string $defaultNotes,
        private ?string $defaultPaymentInstructions,
        private ?string $signatureImage,
        private ?string $stampImage,
        private ?string $preparedBy
    ) {
        $this->validate();
    }

    public static function create(
        int $userId,
        string $businessName,
        string $address,
        string $phone,
        string $defaultCurrency = 'ZMW',
        float $defaultTaxRate = 16.00,
        ?string $email = null,
        ?string $tpin = null
    ): self {
        return new self(
            null,
            $userId,
            $businessName,
            $address,
            $phone,
            $email,
            null,
            $tpin,
            null,
            null,
            null,
            null,
            $defaultCurrency,
            $defaultTaxRate,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    public static function fromPersistence(
        ?int $id,
        int $userId,
        string $businessName,
        string $address,
        string $phone,
        ?string $email,
        ?string $logo,
        ?string $tpin,
        ?string $website,
        ?string $bankName,
        ?string $bankAccount,
        ?string $bankBranch,
        string $defaultCurrency,
        float $defaultTaxRate,
        ?string $defaultTerms,
        ?string $defaultNotes,
        ?string $defaultPaymentInstructions,
        ?string $signatureImage,
        ?string $stampImage,
        ?string $preparedBy
    ): self {
        return new self(
            $id,
            $userId,
            $businessName,
            $address,
            $phone,
            $email,
            $logo,
            $tpin,
            $website,
            $bankName,
            $bankAccount,
            $bankBranch,
            $defaultCurrency,
            $defaultTaxRate,
            $defaultTerms,
            $defaultNotes,
            $defaultPaymentInstructions,
            $signatureImage,
            $stampImage,
            $preparedBy
        );
    }

    private function validate(): void
    {
        if (empty($this->businessName)) {
            throw new \InvalidArgumentException('Business name cannot be empty');
        }

        if (empty($this->address)) {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        if (empty($this->phone)) {
            throw new \InvalidArgumentException('Phone cannot be empty');
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
    }

    public function updateBasicInfo(
        string $businessName,
        string $address,
        string $phone,
        ?string $email = null,
        ?string $tpin = null,
        ?string $website = null
    ): void {
        $this->businessName = $businessName;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->tpin = $tpin;
        $this->website = $website;
        $this->validate();
    }

    public function updateBankDetails(?string $bankName, ?string $bankAccount, ?string $bankBranch): void
    {
        $this->bankName = $bankName;
        $this->bankAccount = $bankAccount;
        $this->bankBranch = $bankBranch;
    }

    public function updateBranding(?string $logo, ?string $signatureImage, ?string $stampImage): void
    {
        if ($logo !== null) {
            $this->logo = $logo;
        }
        if ($signatureImage !== null) {
            $this->signatureImage = $signatureImage;
        }
        if ($stampImage !== null) {
            $this->stampImage = $stampImage;
        }
    }

    // Getters
    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function businessName(): string
    {
        return $this->businessName;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function logo(): ?string
    {
        return $this->logo;
    }

    public function tpin(): ?string
    {
        return $this->tpin;
    }

    public function website(): ?string
    {
        return $this->website;
    }

    public function bankName(): ?string
    {
        return $this->bankName;
    }

    public function bankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function bankBranch(): ?string
    {
        return $this->bankBranch;
    }

    public function defaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function defaultTaxRate(): float
    {
        return $this->defaultTaxRate;
    }

    public function defaultTerms(): ?string
    {
        return $this->defaultTerms;
    }

    public function defaultNotes(): ?string
    {
        return $this->defaultNotes;
    }

    public function defaultPaymentInstructions(): ?string
    {
        return $this->defaultPaymentInstructions;
    }

    public function signatureImage(): ?string
    {
        return $this->signatureImage;
    }

    public function stampImage(): ?string
    {
        return $this->stampImage;
    }

    public function preparedBy(): ?string
    {
        return $this->preparedBy;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'businessName' => $this->businessName,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo,
            'tpin' => $this->tpin,
            'website' => $this->website,
            'bankName' => $this->bankName,
            'bankAccount' => $this->bankAccount,
            'bankBranch' => $this->bankBranch,
            'defaultCurrency' => $this->defaultCurrency,
            'defaultTaxRate' => $this->defaultTaxRate,
            'defaultTerms' => $this->defaultTerms,
            'defaultNotes' => $this->defaultNotes,
            'defaultPaymentInstructions' => $this->defaultPaymentInstructions,
            'signatureImage' => $this->signatureImage,
            'stampImage' => $this->stampImage,
            'preparedBy' => $this->preparedBy,
        ];
    }
}
