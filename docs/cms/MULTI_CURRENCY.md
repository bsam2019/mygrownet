# Multi-Currency Support System

**Last Updated:** February 12, 2026  
**Status:** Production Ready  
**Version:** 1.0.0

---

## Overview

The Multi-Currency Support system allows businesses to work with multiple currencies, manage exchange rates, and automatically convert amounts between currencies. This is essential for businesses dealing with international clients or operating in multiple countries.

---

## Features

✅ Currency configuration (20+ currencies pre-loaded)  
✅ Exchange rate management (manual and API-ready)  
✅ Multi-currency invoices  
✅ Currency conversion  
✅ Exchange rate history  
✅ Base currency setting  
✅ Currency display formatting  
✅ Automatic conversion to base currency  
✅ Historical rate tracking  
✅ Support for different decimal places  

---

## Implementation

### Database Tables

**cms_currencies:**
- ISO 4217 currency codes
- Currency names and symbols
- Decimal places configuration
- Display format templates

**cms_exchange_rates:**
- Company-specific rates
- Historical rate tracking
- Source tracking (manual/API)
- Effective date support

**Updated Tables:**
- cms_companies: base_currency, multi_currency_enabled
- cms_invoices: currency, exchange_rate
- cms_payments: currency, exchange_rate
- cms_expenses: currency, exchange_rate
- cms_quotations: currency, exchange_rate
- cms_recurring_invoices: currency

### Backend Files

- `database/migrations/2026_02_12_150000_add_multi_currency_support.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CurrencyModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/ExchangeRateModel.php`
- `app/Domain/CMS/Core/Services/CurrencyService.php`
- `app/Http/Controllers/CMS/CurrencyController.php`
- `database/seeders/CurrenciesSeeder.php`

### Routes

All routes under `/cms/settings/currency`:
- GET `/` - Currency settings page
- POST `/` - Update settings
- POST `/exchange-rate` - Set exchange rate
- GET `/exchange-rate` - Get exchange rate
- POST `/convert` - Convert amount
- GET `/history` - Rate history
- POST `/fetch-live-rates` - Fetch from API

---

## Supported Currencies

### African Currencies
- ZMW - Zambian Kwacha
- ZAR - South African Rand
- NGN - Nigerian Naira
- KES - Kenyan Shilling
- GHS - Ghanaian Cedi
- TZS - Tanzanian Shilling
- UGX - Ugandan Shilling
- MWK - Malawian Kwacha
- BWP - Botswana Pula
- ZWL - Zimbabwean Dollar

### Major International
- USD - US Dollar
- EUR - Euro
- GBP - British Pound
- JPY - Japanese Yen
- CNY - Chinese Yuan
- INR - Indian Rupee
- AUD - Australian Dollar
- CAD - Canadian Dollar
- CHF - Swiss Franc
- AED - UAE Dirham

---

## Usage

### Enable Multi-Currency

1. Navigate to Settings > Currency
2. Toggle "Enable Multi-Currency"
3. Select base currency (default: ZMW)
4. Save settings

### Set Exchange Rates

1. Go to Currency Settings
2. Select currency pair (e.g., USD to ZMW)
3. Enter exchange rate
4. Set effective date (optional)
5. Save

### Create Multi-Currency Invoice

1. Create new invoice
2. Select currency from dropdown
3. System automatically fetches current exchange rate
4. Invoice amounts stored in selected currency
5. Conversion to base currency for reporting

### Currency Conversion

```php
// Convert amount
$zmwAmount = $currencyService->convert(
    amount: 100,
    fromCurrency: 'USD',
    toCurrency: 'ZMW',
    companyId: 1
);

// Convert to base currency
$baseAmount = $currencyService->convertToBaseCurrency(
    amount: 100,
    fromCurrency: 'USD',
    companyId: 1
);
```

---

## Exchange Rate Management

### Manual Rates

Set rates manually through the UI or API:
- Immediate effect or future-dated
- Historical tracking
- Override previous rates

### API Integration (Ready)

The system is ready for API integration with services like:
- exchangerate-api.com
- fixer.io
- openexchangerates.org

Simply add API key and uncomment the implementation in `CurrencyService::fetchLiveRates()`.

### Rate History

View historical rates for any currency pair:
- Date range filtering
- Rate change tracking
- Source identification (manual/API)

---

## Display Formatting

Each currency has customizable display format:

```
{symbol}{amount} → $100.00
{amount} {symbol} → 100.00 د.إ
```

Examples:
- USD: $100.00
- EUR: €100.00
- ZMW: K100.00
- AED: 100.00 د.إ

---

## Reporting

All financial reports automatically convert to base currency:
- Profit & Loss
- Cash Flow
- Sales Summary
- Tax Reports

Original currency and exchange rate preserved for audit trail.

---

## API Examples

### Get Exchange Rate

```php
$rate = $currencyService->getExchangeRate(
    companyId: 1,
    fromCurrency: 'USD',
    toCurrency: 'ZMW',
    date: Carbon::today()
);
```

### Set Exchange Rate

```php
$currencyService->setExchangeRate(
    companyId: 1,
    fromCurrency: 'USD',
    toCurrency: 'ZMW',
    rate: 25.50,
    date: Carbon::today(),
    source: 'manual'
);
```

### Format Amount

```php
$formatted = $currencyService->formatAmount(1000, 'USD');
// Returns: $1,000.00
```

---

## Best Practices

1. **Set Base Currency First** - Choose your primary operating currency
2. **Update Rates Regularly** - Keep exchange rates current
3. **Use Historical Rates** - System uses rate effective on transaction date
4. **Enable Only When Needed** - Multi-currency adds complexity
5. **Audit Trail** - All conversions logged with rates used

---

## Troubleshooting

### Rate Not Found

- Ensure rate is set for the currency pair
- Check effective date is on or before transaction date
- Verify multi-currency is enabled

### Incorrect Conversion

- Verify exchange rate is correct
- Check decimal places configuration
- Ensure using correct currency codes

### Display Issues

- Check currency format template
- Verify symbol is correctly encoded (UTF-8)
- Test with different browsers

---

## Future Enhancements

- [ ] Automatic rate updates from API
- [ ] Rate alerts (notify when rate changes significantly)
- [ ] Multi-currency bank reconciliation
- [ ] Currency gain/loss reporting
- [ ] Bulk rate import
- [ ] Rate forecasting
- [ ] Custom currency support

---

## Changelog

### February 12, 2026
- Initial implementation
- Database schema created
- 20 currencies pre-loaded
- Service and controller implemented
- Exchange rate management
- Currency conversion
- Historical tracking
- Routes configured
- Currency settings UI page created with:
  - Base currency selection
  - Multi-currency toggle
  - Exchange rate management table
  - Add/Edit rate modal
  - Currency converter tool
  - Real-time conversion

**Status:** Production ready - 100% complete

---

**Note:** System is fully functional and ready for production use. API integration for live rates can be enabled by adding API key to CurrencyService.
