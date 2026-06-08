<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Currency Detection Service
 * 
 * Detects user's currency based on:
 * 1. User's saved preference
 * 2. IP geolocation
 * 3. Browser locale
 * 4. Default fallback
 */
class CurrencyDetectionService
{
    /**
     * Currency mapping by country code
     */
    private const COUNTRY_CURRENCIES = [
        // Africa
        'ZM' => 'ZMW', // Zambia
        'ZA' => 'ZAR', // South Africa
        'KE' => 'KES', // Kenya
        'NG' => 'NGN', // Nigeria
        'GH' => 'GHS', // Ghana
        'TZ' => 'TZS', // Tanzania
        'UG' => 'UGX', // Uganda
        'RW' => 'RWF', // Rwanda
        'MW' => 'MWK', // Malawi
        'ZW' => 'USD', // Zimbabwe (uses USD)
        'BW' => 'BWP', // Botswana
        'EG' => 'EGP', // Egypt
        'MA' => 'MAD', // Morocco
        'ET' => 'ETB', // Ethiopia
        
        // Americas
        'US' => 'USD', // United States
        'CA' => 'CAD', // Canada
        'MX' => 'MXN', // Mexico
        'BR' => 'BRL', // Brazil
        'AR' => 'ARS', // Argentina
        
        // Europe
        'GB' => 'GBP', // United Kingdom
        'DE' => 'EUR', // Germany
        'FR' => 'EUR', // France
        'IT' => 'EUR', // Italy
        'ES' => 'EUR', // Spain
        'NL' => 'EUR', // Netherlands
        'BE' => 'EUR', // Belgium
        'CH' => 'CHF', // Switzerland
        'SE' => 'SEK', // Sweden
        'NO' => 'NOK', // Norway
        'DK' => 'DKK', // Denmark
        'PL' => 'PLN', // Poland
        
        // Asia
        'CN' => 'CNY', // China
        'JP' => 'JPY', // Japan
        'IN' => 'INR', // India
        'SG' => 'SGD', // Singapore
        'HK' => 'HKD', // Hong Kong
        'MY' => 'MYR', // Malaysia
        'TH' => 'THB', // Thailand
        'ID' => 'IDR', // Indonesia
        'PH' => 'PHP', // Philippines
        'VN' => 'VND', // Vietnam
        'KR' => 'KRW', // South Korea
        
        // Oceania
        'AU' => 'AUD', // Australia
        'NZ' => 'NZD', // New Zealand
        
        // Middle East
        'AE' => 'AED', // UAE
        'SA' => 'SAR', // Saudi Arabia
        'IL' => 'ILS', // Israel
        'TR' => 'TRY', // Turkey
    ];

    /**
     * Popular currencies for selection
     */
    private const POPULAR_CURRENCIES = [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'flag' => '🇺🇸'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'flag' => '🇪🇺'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'flag' => '🇬🇧'],
        'ZMW' => ['name' => 'Zambian Kwacha', 'symbol' => 'K', 'flag' => '🇿🇲'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'flag' => '🇿🇦'],
        'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'flag' => '🇰🇪'],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'flag' => '🇳🇬'],
        'GHS' => ['name' => 'Ghanaian Cedi', 'symbol' => '₵', 'flag' => '🇬🇭'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'flag' => '🇨🇦'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'flag' => '🇦🇺'],
    ];

    /**
     * Detect user's currency (simplified to ZMW or USD only)
     * 
     * IMPORTANT: This should be called ONCE during registration.
     * The currency is the user's BASE CURRENCY and should NEVER change.
     * Wallet balance is stored in this currency.
     * 
     * @param string|null $ipAddress User's IP address
     * @param int|null $userId User ID if logged in
     * @return string Currency code ('ZMW' for Zambians, 'USD' for foreigners)
     */
    public function detectCurrency(?string $ipAddress = null, ?int $userId = null): string
    {
        // 1. ALWAYS check user's saved currency first (most important)
        if ($userId) {
            $savedCurrency = $this->getUserCurrency($userId);
            if ($savedCurrency) {
                // User's currency is PERMANENT - never convert it
                return $savedCurrency;
            }
        }

        // 2. Check session (for guest users browsing)
        if (session()->has('user_currency')) {
            return session('user_currency');
        }

        // 3. Detect from IP geolocation - only return ZMW or USD
        if ($ipAddress) {
            $currency = $this->detectFromIP($ipAddress);
            if ($currency) {
                // Convert to base currency: ZMW for Zambians, USD for everyone else
                $userCurrency = ($currency === 'ZMW') ? 'ZMW' : 'USD';
                session(['user_currency' => $userCurrency]);
                return $userCurrency;
            }
        }

        // 4. Default to ZMW (since platform is based in Zambia)
        return 'ZMW';
    }

    /**
     * Get user's base currency (ZMW or USD)
     */
    private function getUserCurrency(int $userId): ?string
    {
        try {
            $user = \App\Models\User::find($userId);
            return $user?->user_currency ?? $user?->preferred_currency;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Save user's base currency (ZMW or USD only)
     */
    public function saveUserCurrency(int $userId, string $currency): bool
    {
        try {
            // Normalize to ZMW or USD only
            $currency = strtoupper($currency);
            $userCurrency = ($currency === 'ZMW') ? 'ZMW' : 'USD';
            
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->user_currency = $userCurrency;
                $user->preferred_currency = $userCurrency; // Keep in sync
                $user->save();
                
                // Update session
                session(['user_currency' => $userCurrency]);
                
                Log::info('User currency saved', [
                    'user_id' => $userId,
                    'currency' => $userCurrency,
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to save user currency', [
                'user_id' => $userId,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Detect currency from IP address
     */
    private function detectFromIP(string $ipAddress): ?string
    {
        // Skip local IPs
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return null;
        }

        // Check cache first
        $cacheKey = "currency_ip_{$ipAddress}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Use free IP geolocation service
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}");

            if ($response->successful()) {
                $data = $response->json();
                $countryCode = $data['countryCode'] ?? null;

                if ($countryCode && isset(self::COUNTRY_CURRENCIES[$countryCode])) {
                    $currency = self::COUNTRY_CURRENCIES[$countryCode];
                    
                    // Cache for 24 hours
                    Cache::put($cacheKey, $currency, 86400);
                    
                    Log::info('Currency detected from IP', [
                        'ip' => $ipAddress,
                        'country' => $countryCode,
                        'currency' => $currency,
                    ]);
                    
                    return $currency;
                }
            }
        } catch (\Exception $e) {
            Log::warning('IP geolocation failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Detect currency from browser locale
     */
    private function detectFromLocale(): ?string
    {
        $locale = app()->getLocale();
        
        // Map locale to currency
        $localeMap = [
            'en_US' => 'USD',
            'en_GB' => 'GBP',
            'en_ZM' => 'ZMW',
            'en_ZA' => 'ZAR',
            'en_KE' => 'KES',
            'en_NG' => 'NGN',
            'en_GH' => 'GHS',
            'fr_FR' => 'EUR',
            'de_DE' => 'EUR',
            'es_ES' => 'EUR',
            'it_IT' => 'EUR',
        ];

        return $localeMap[$locale] ?? null;
    }

    /**
     * Get user's saved currency preference
     */
    private function getUserCurrencyPreference(int $userId): ?string
    {
        try {
            $user = \App\Models\User::find($userId);
            return $user?->user_currency ?? $user?->preferred_currency;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Save user's currency preference
     */
    public function saveCurrencyPreference(int $userId, string $currency): bool
    {
        try {
            // Normalize to ZMW or USD
            $currency = strtoupper($currency);
            $userCurrency = ($currency === 'ZMW') ? 'ZMW' : 'USD';
            
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->user_currency = $userCurrency;
                $user->preferred_currency = $userCurrency;
                $user->save();
                
                // Update session
                session(['user_currency' => $userCurrency, 'currency' => $userCurrency]);
                
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to save currency preference', [
                'user_id' => $userId,
                'currency' => $currency,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Get popular currencies for selection
     */
    public function getPopularCurrencies(): array
    {
        return self::POPULAR_CURRENCIES;
    }

    /**
     * Get all supported currencies
     */
    public function getAllCurrencies(): array
    {
        return array_unique(array_values(self::COUNTRY_CURRENCIES));
    }

    /**
     * Get currency info
     */
    public function getCurrencyInfo(string $currency): ?array
    {
        $currency = strtoupper($currency);
        return self::POPULAR_CURRENCIES[$currency] ?? null;
    }

    /**
     * Format amount with currency
     */
    public function formatAmount(float $amount, string $currency): string
    {
        $info = $this->getCurrencyInfo($currency);
        $symbol = $info['symbol'] ?? $currency;
        
        return $symbol . ' ' . number_format($amount, 2);
    }

    /**
     * Get country by currency
     */
    public function getCountryByCurrency(string $currency): ?string
    {
        $currency = strtoupper($currency);
        $countries = array_flip(self::COUNTRY_CURRENCIES);
        return $countries[$currency] ?? null;
    }
}
