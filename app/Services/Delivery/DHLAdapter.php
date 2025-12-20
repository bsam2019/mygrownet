<?php

namespace App\Services\Delivery;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * DHL API Adapter (Future Implementation)
 * 
 * This adapter will be used when DHL Zambia API access is set up.
 * Requires DHL business account and API credentials.
 * 
 * For now, use ManualCourierService for all Zambian couriers.
 */
class DHLAdapter implements CourierInterface
{
    private string $apiKey;
    private string $apiSecret;
    private string $apiUrl;
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('marketplace.delivery.couriers.dhl.api_key');
        $this->apiSecret = config('marketplace.delivery.couriers.dhl.api_secret');
        $this->apiUrl = config('marketplace.delivery.couriers.dhl.api_url', 'https://api.dhl.com/v1');
        $this->enabled = config('marketplace.delivery.couriers.dhl.enabled', false);
    }

    public function createShipment(array $data): array
    {
        if (!$this->enabled) {
            throw new \Exception('DHL API is not enabled. Use ManualCourierService instead.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/shipments", [
                'sender' => [
                    'name' => $data['seller_name'],
                    'phone' => $data['seller_phone'],
                    'address' => $data['seller_address'],
                    'province' => $data['seller_province'],
                    'district' => $data['seller_district'],
                ],
                'recipient' => [
                    'name' => $data['buyer_name'],
                    'phone' => $data['buyer_phone'],
                    'address' => $data['delivery_address']['address'],
                    'province' => $data['delivery_address']['province'],
                    'district' => $data['delivery_address']['district'],
                ],
                'package' => [
                    'weight' => $data['weight'] ?? 1, // kg
                    'length' => $data['dimensions']['length'] ?? 30, // cm
                    'width' => $data['dimensions']['width'] ?? 20,
                    'height' => $data['dimensions']['height'] ?? 10,
                    'value' => $data['value'], // in ngwee
                    'description' => $data['description'],
                    'fragile' => $data['fragile'] ?? false,
                ],
                'service_type' => $data['service_type'] ?? 'standard', // standard, express
                'payment_method' => 'prepaid', // Seller pays
                'reference' => $data['order_number'],
            ]);

            if ($response->failed()) {
                Log::error('DHL API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to create shipment: ' . $response->body());
            }

            $result = $response->json();

            return [
                'tracking_number' => $result['tracking_number'],
                'estimated_delivery' => $result['estimated_delivery_date'],
                'cost' => $result['shipping_cost'], // in ngwee
                'label_url' => $result['shipping_label_url'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('DHL createShipment failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    public function getShippingRates(array $data): array
    {
        if (!$this->enabled) {
            return [
                'cost' => 0,
                'estimated_days' => 0,
                'service_name' => 'DHL Zambia (API Disabled)',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
            ])->post("{$this->apiUrl}/rates", [
                'from_province' => $data['from_province'],
                'from_district' => $data['from_district'],
                'to_province' => $data['to_province'],
                'to_district' => $data['to_district'],
                'weight' => $data['weight'] ?? 1,
                'service_type' => $data['service_type'] ?? 'standard',
            ]);

            if ($response->failed()) {
                Log::warning('DHL rates API failed', [
                    'status' => $response->status(),
                ]);
                return [
                    'cost' => 5000, // Default K50
                    'estimated_days' => 2,
                    'service_name' => 'DHL Zambia Standard',
                ];
            }

            $result = $response->json();

            return [
                'cost' => $result['cost'], // in ngwee
                'estimated_days' => $result['estimated_days'],
                'service_name' => $result['service_name'],
            ];
        } catch (\Exception $e) {
            Log::error('DHL getShippingRates failed', [
                'error' => $e->getMessage(),
            ]);
            
            // Return default rates on error
            return [
                'cost' => 5000, // K50
                'estimated_days' => 2,
                'service_name' => 'DHL Zambia Standard',
            ];
        }
    }

    public function trackShipment(string $trackingNumber): array
    {
        if (!$this->enabled) {
            return [
                'status' => 'unknown',
                'location' => 'N/A',
                'estimated_delivery' => null,
                'events' => [],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
            ])->get("{$this->apiUrl}/shipments/{$trackingNumber}/track");

            if ($response->failed()) {
                Log::warning('DHL tracking failed', [
                    'tracking_number' => $trackingNumber,
                    'status' => $response->status(),
                ]);
                return [
                    'status' => 'unknown',
                    'location' => 'Tracking unavailable',
                    'estimated_delivery' => null,
                    'events' => [],
                ];
            }

            $result = $response->json();

            return [
                'status' => $result['status'], // pending, in_transit, out_for_delivery, delivered
                'location' => $result['current_location'],
                'estimated_delivery' => $result['estimated_delivery_date'],
                'events' => $result['tracking_events'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('DHL trackShipment failed', [
                'error' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);
            
            return [
                'status' => 'unknown',
                'location' => 'Tracking unavailable',
                'estimated_delivery' => null,
                'events' => [],
            ];
        }
    }

    public function cancelShipment(string $trackingNumber): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
            ])->delete("{$this->apiUrl}/shipments/{$trackingNumber}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('DHL cancelShipment failed', [
                'error' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);
            return false;
        }
    }

    public function getProofOfDelivery(string $trackingNumber): ?array
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-API-Secret' => $this->apiSecret,
            ])->get("{$this->apiUrl}/shipments/{$trackingNumber}/proof");

            if ($response->failed()) {
                return null;
            }

            $result = $response->json();

            return [
                'signature_url' => $result['signature_url'] ?? null,
                'photo_url' => $result['photo_url'] ?? null,
                'delivered_at' => $result['delivered_at'],
                'recipient_name' => $result['recipient_name'],
            ];
        } catch (\Exception $e) {
            Log::error('DHL getProofOfDelivery failed', [
                'error' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);
            return null;
        }
    }

    public function servesArea(string $province, string $district): bool
    {
        // DHL serves all major provinces in Zambia
        $servedProvinces = [
            'Lusaka',
            'Copperbelt',
            'Southern',
            'Central',
            'Eastern',
            'Northern',
            'Western',
        ];

        return in_array($province, $servedProvinces);
    }
}
