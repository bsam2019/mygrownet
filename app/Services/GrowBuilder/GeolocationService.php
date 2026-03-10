<?php

namespace App\Services\GrowBuilder;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const FALLBACK_COUNTRY = 'Unknown';
    
    /**
     * Get country from IP address using multiple fallback services
     */
    public function getCountryFromIp(string $ipAddress): string
    {
        // Skip local/private IPs
        if ($this->isLocalIp($ipAddress)) {
            return 'Local';
        }
        
        // Check cache first
        $cacheKey = "geolocation:country:{$ipAddress}";
        $cachedCountry = Cache::get($cacheKey);
        
        if ($cachedCountry) {
            return $cachedCountry;
        }
        
        // Try multiple services with fallbacks
        $country = $this->tryIpApiCo($ipAddress) 
                ?? $this->tryIpInfo($ipAddress)
                ?? $this->tryFreeGeoIp($ipAddress)
                ?? self::FALLBACK_COUNTRY;
        
        // Cache the result
        Cache::put($cacheKey, $country, self::CACHE_TTL);
        
        return $country;
    }
    
    /**
     * Get detailed location info from IP (country, region, city)
     */
    public function getLocationFromIp(string $ipAddress): array
    {
        if ($this->isLocalIp($ipAddress)) {
            return [
                'country' => 'Local',
                'countryCode' => 'XX',
                'region' => null,
                'city' => null,
            ];
        }
        
        $cacheKey = "geolocation:location:{$ipAddress}";
        $cachedLocation = Cache::get($cacheKey);
        
        if ($cachedLocation) {
            return $cachedLocation;
        }
        
        $location = $this->tryDetailedIpApiCo($ipAddress) ?? [
            'country' => self::FALLBACK_COUNTRY,
            'countryCode' => 'XX',
            'region' => null,
            'city' => null,
        ];
        
        Cache::put($cacheKey, $location, self::CACHE_TTL);
        
        return $location;
    }
    
    /**
     * Primary service: ipapi.co (free tier: 1000 requests/day)
     */
    private function tryIpApiCo(string $ipAddress): ?string
    {
        try {
            $response = Http::timeout(3)->get("https://ipapi.co/{$ipAddress}/country_name/");
            
            if ($response->successful() && $response->body() && !str_contains($response->body(), 'error')) {
                return trim($response->body());
            }
        } catch (\Exception $e) {
            Log::warning("ipapi.co geolocation failed for {$ipAddress}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Detailed location from ipapi.co
     */
    private function tryDetailedIpApiCo(string $ipAddress): ?array
    {
        try {
            $response = Http::timeout(3)->get("https://ipapi.co/{$ipAddress}/json/");
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['country_name']) && !isset($data['error'])) {
                    return [
                        'country' => $data['country_name'],
                        'countryCode' => $data['country_code'] ?? 'XX',
                        'region' => $data['region'] ?? null,
                        'city' => $data['city'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("ipapi.co detailed geolocation failed for {$ipAddress}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Fallback service: ipinfo.io (free tier: 50,000 requests/month)
     */
    private function tryIpInfo(string $ipAddress): ?string
    {
        try {
            $response = Http::timeout(3)->get("https://ipinfo.io/{$ipAddress}/country");
            
            if ($response->successful() && $response->body()) {
                $countryCode = trim($response->body());
                return $this->getCountryNameFromCode($countryCode);
            }
        } catch (\Exception $e) {
            Log::warning("ipinfo.io geolocation failed for {$ipAddress}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Fallback service: freegeoip.app (free tier: 15,000 requests/hour)
     */
    private function tryFreeGeoIp(string $ipAddress): ?string
    {
        try {
            $response = Http::timeout(3)->get("https://freegeoip.app/json/{$ipAddress}");
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['country_name'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning("freegeoip.app geolocation failed for {$ipAddress}: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Check if IP is local/private
     */
    private function isLocalIp(string $ipAddress): bool
    {
        return in_array($ipAddress, ['127.0.0.1', '::1', 'localhost']) ||
               filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
    
    /**
     * Convert country code to country name
     */
    private function getCountryNameFromCode(string $countryCode): string
    {
        $countries = [
            'ZM' => 'Zambia',
            'ZA' => 'South Africa',
            'KE' => 'Kenya',
            'TZ' => 'Tanzania',
            'MW' => 'Malawi',
            'BW' => 'Botswana',
            'ZW' => 'Zimbabwe',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IN' => 'India',
            'CN' => 'China',
            'JP' => 'Japan',
            'BR' => 'Brazil',
            'NG' => 'Nigeria',
            'GH' => 'Ghana',
            'UG' => 'Uganda',
            'RW' => 'Rwanda',
        ];
        
        return $countries[strtoupper($countryCode)] ?? $countryCode;
    }
    
    /**
     * Get visitor's real IP address (handles proxies/load balancers)
     */
    public function getRealIpAddress(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}