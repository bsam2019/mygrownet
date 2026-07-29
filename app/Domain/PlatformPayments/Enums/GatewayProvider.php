<?php

namespace App\Domain\PlatformPayments\Enums;

enum GatewayProvider: string
{
    case PAWAPAY = 'pawapay';
    case FLUTTERWAVE = 'flutterwave';
    case DPO = 'dpo';
    case MTN_MOMO = 'mtn_momo';
    case AIRTEL_MONEY = 'airtel_money';
    case MONEY_UNIFY = 'money_unify';
    case ZAMTEL_KWACHA = 'zamtel_kwacha';

    public function getLabel(): string
    {
        return match($this) {
            self::PAWAPAY => 'PawaPay',
            self::FLUTTERWAVE => 'Flutterwave',
            self::DPO => 'DPO PayGate',
            self::MTN_MOMO => 'MTN Mobile Money',
            self::AIRTEL_MONEY => 'Airtel Money',
            self::MONEY_UNIFY => 'MoneyUnify',
            self::ZAMTEL_KWACHA => 'Zamtel Kwacha',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::PAWAPAY => 'Multi-provider mobile money aggregator for Africa',
            self::FLUTTERWAVE => 'Accept payments via mobile money, cards, and bank transfers',
            self::DPO => 'Secure payment gateway supporting mobile money and cards',
            self::MTN_MOMO => 'MTN Mobile Money for Zambia',
            self::AIRTEL_MONEY => 'Airtel Money for Zambia',
            self::MONEY_UNIFY => 'Pan-African payment gateway',
            self::ZAMTEL_KWACHA => 'Zamtel mobile money for Zambia',
        };
    }

    public function getWebsite(): string
    {
        return match($this) {
            self::PAWAPAY => 'https://pawapay.io',
            self::FLUTTERWAVE => 'https://flutterwave.com',
            self::DPO => 'https://www.dpogroup.com',
            self::MTN_MOMO => 'https://momodeveloper.mtn.com',
            self::AIRTEL_MONEY => 'https://www.airtel.africa',
            self::MONEY_UNIFY => 'https://moneyunify.com',
            self::ZAMTEL_KWACHA => 'https://www.zamtel.co.zm',
        };
    }
}
