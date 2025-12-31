<?php

namespace App\Domain\GrowBuilder\Payment\Enums;

enum PaymentGateway: string
{
    case PAWAPAY = 'pawapay';
    case FLUTTERWAVE = 'flutterwave';
    case DPO = 'dpo';

    public function getLabel(): string
    {
        return match($this) {
            self::PAWAPAY => 'PawaPay',
            self::FLUTTERWAVE => 'Flutterwave',
            self::DPO => 'DPO PayGate',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PAWAPAY => 'Multi-provider mobile money aggregator for Africa',
            self::FLUTTERWAVE => 'Accept payments via mobile money, cards, and bank transfers',
            self::DPO => 'Secure payment gateway supporting mobile money and cards',
        };
    }

    public function getWebsite(): string
    {
        return match($this) {
            self::PAWAPAY => 'https://pawapay.io',
            self::FLUTTERWAVE => 'https://flutterwave.com',
            self::DPO => 'https://www.dpogroup.com',
        };
    }
}
