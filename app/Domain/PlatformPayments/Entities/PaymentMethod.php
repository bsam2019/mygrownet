<?php

namespace App\Domain\PlatformPayments\Entities;

enum PaymentMethod: string
{
    case MTNMoMo = 'mtn_momo';
    case AirtelMoney = 'airtel_money';
    case MoneyUnify = 'moneyunify';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Wallet = 'wallet';
}
