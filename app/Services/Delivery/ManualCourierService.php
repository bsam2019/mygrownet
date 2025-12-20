<?php

namespace App\Services\Delivery;

use App\Models\MarketplaceOrder;
use Illuminate\Support\Facades\Log;

/**
 * Manual Courier Service for Zambian Couriers
 * 
 * Most Zambian couriers don't have public APIs, so this service
 * provides a manual integration where sellers:
 * 1. Select a courier from predefined list
 * 2. Contact courier directly (phone/WhatsApp)
 * 3. Get tracking number from courier
 * 4. Enter tracking number in system
 * 5. Buyers can track manually on courier's website
 */
class ManualCourierService
{
    /**
     * Predefined courier list with contact info and estimated rates
     */
    private array $couriers = [
        'postnet' => [
            'name' => 'Postnet Zambia',
            'phone' => '+260-211-256-000',
            'whatsapp' => '+260-97-XXXXXXX',
            'website' => 'https://postnet.co.zm',
            'tracking_url' => 'https://postnet.co.zm/track?number={tracking}',
            'coverage' => ['Lusaka', 'Copperbelt', 'Southern', 'Central', 'Eastern'],
            'rates' => [
                'lusaka_local' => 3000, // K30 within Lusaka
                'copperbelt' => 5000,   // K50 to Copperbelt
                'other_provinces' => 7000, // K70 to other provinces
            ],
            'estimated_days' => [
                'lusaka_local' => 1,
                'copperbelt' => 2,
                'other_provinces' => 3,
            ],
        ],
        'dhl' => [
            'name' => 'DHL Zambia',
            'phone' => '+260-211-256-400',
            'whatsapp' => null,
            'website' => 'https://www.dhl.com/zm-en/home.html',
            'tracking_url' => 'https://www.dhl.com/zm-en/home/tracking.html?tracking-id={tracking}',
            'coverage' => ['Lusaka', 'Copperbelt', 'Southern', 'Central', 'Eastern', 'Northern', 'Western'],
            'rates' => [
                'lusaka_local' => 5000,
                'copperbelt' => 8000,
                'other_provinces' => 10000,
            ],
            'estimated_days' => [
                'lusaka_local' => 1,
                'copperbelt' => 2,
                'other_provinces' => 3,
            ],
        ],
        'aramex' => [
            'name' => 'Aramex Zambia',
            'phone' => '+260-211-XXXXXX',
            'whatsapp' => null,
            'website' => 'https://www.aramex.com',
            'tracking_url' => 'https://www.aramex.com/track/results?ShipmentNumber={tracking}',
            'coverage' => ['Lusaka', 'Copperbelt'],
            'rates' => [
                'lusaka_local' => 4000,
                'copperbelt' => 6000,
                'other_provinces' => 8000,
            ],
            'estimated_days' => [
                'lusaka_local' => 1,
                'copperbelt' => 2,
                'other_provinces' => 3,
            ],
        ],
        'fedex' => [
            'name' => 'Fedex Zambia',
            'phone' => '+260-211-XXXXXX',
            'whatsapp' => null,
            'website' => 'https://www.fedex.com/en-zm/home.html',
            'tracking_url' => 'https://www.fedex.com/fedextrack/?trknbr={tracking}',
            'coverage' => ['Lusaka', 'Copperbelt', 'Southern'],
            'rates' => [
                'lusaka_local' => 6000,
                'copperbelt' => 9000,
                'other_provinces' => 12000,
            ],
            'estimated_days' => [
                'lusaka_local' => 1,
                'copperbelt' => 1,
                'other_provinces' => 2,
            ],
        ],
    ];

    /**
     * Get all available couriers
     */
    public function getAvailableCouriers(): array
    {
        return $this->couriers;
    }

    /**
     * Get couriers that serve a specific province
     */
    public function getCouriersForProvince(string $province): array
    {
        $available = [];

        foreach ($this->couriers as $code => $courier) {
            if (in_array($province, $courier['coverage'])) {
                $available[$code] = $courier;
            }
        }

        return $available;
    }

    /**
     * Get courier details by code
     */
    public function getCourier(string $courierCode): ?array
    {
        return $this->couriers[$courierCode] ?? null;
    }

    /**
     * Estimate shipping cost
     */
    public function estimateCost(string $courierCode, string $fromProvince, string $toProvince): int
    {
        $courier = $this->couriers[$courierCode] ?? null;
        
        if (!$courier) {
            return 5000; // Default K50
        }

        // Check if courier serves the destination
        if (!in_array($toProvince, $courier['coverage'])) {
            return 0; // Courier doesn't serve this area
        }

        // Simple rate calculation based on destination
        if ($fromProvince === $toProvince && $toProvince === 'Lusaka') {
            return $courier['rates']['lusaka_local'];
        } elseif ($toProvince === 'Copperbelt') {
            return $courier['rates']['copperbelt'];
        } else {
            return $courier['rates']['other_provinces'];
        }
    }

    /**
     * Estimate delivery days
     */
    public function estimateDeliveryDays(string $courierCode, string $fromProvince, string $toProvince): int
    {
        $courier = $this->couriers[$courierCode] ?? null;
        
        if (!$courier) {
            return 3; // Default 3 days
        }

        if ($fromProvince === $toProvince && $toProvince === 'Lusaka') {
            return $courier['estimated_days']['lusaka_local'];
        } elseif ($toProvince === 'Copperbelt') {
            return $courier['estimated_days']['copperbelt'];
        } else {
            return $courier['estimated_days']['other_provinces'];
        }
    }

    /**
     * Create manual shipment record
     * 
     * This doesn't actually create a shipment with the courier.
     * The seller must contact the courier separately and then
     * enter the tracking number they receive.
     */
    public function createManualShipment(MarketplaceOrder $order, array $data): array
    {
        $courierCode = $data['courier_code'];
        $courier = $this->getCourier($courierCode);

        if (!$courier) {
            throw new \Exception("Invalid courier code: {$courierCode}");
        }

        $fromProvince = $order->seller->province ?? 'Lusaka';
        $toProvince = $order->delivery_address['province'] ?? 'Lusaka';

        $cost = $this->estimateCost($courierCode, $fromProvince, $toProvince);
        $days = $this->estimateDeliveryDays($courierCode, $fromProvince, $toProvince);

        // Update order with courier selection
        $order->update([
            'courier_name' => $courier['name'],
            'delivery_method' => 'courier',
            'shipping_cost' => $cost,
            'estimated_delivery' => now()->addDays($days),
        ]);

        Log::info('Manual shipment created', [
            'order_id' => $order->id,
            'courier' => $courier['name'],
            'cost' => $cost,
            'estimated_days' => $days,
        ]);

        return [
            'courier_name' => $courier['name'],
            'courier_phone' => $courier['phone'],
            'courier_whatsapp' => $courier['whatsapp'],
            'courier_website' => $courier['website'],
            'estimated_delivery' => now()->addDays($days)->format('Y-m-d'),
            'estimated_days' => $days,
            'cost' => $cost,
            'instructions' => $this->getShippingInstructions($courier, $order),
        ];
    }

    /**
     * Add tracking number to order
     * 
     * Called after seller contacts courier and receives tracking number
     */
    public function addTrackingNumber(MarketplaceOrder $order, string $trackingNumber): void
    {
        $courierCode = $this->getCourierCodeFromName($order->courier_name);
        $courier = $this->getCourier($courierCode);

        $order->update([
            'tracking_number' => $trackingNumber,
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        Log::info('Tracking number added', [
            'order_id' => $order->id,
            'tracking_number' => $trackingNumber,
            'courier' => $order->courier_name,
        ]);

        // TODO: Send SMS/email to buyer with tracking info
    }

    /**
     * Get tracking URL for buyer
     */
    public function getTrackingUrl(MarketplaceOrder $order): ?string
    {
        if (!$order->tracking_number) {
            return null;
        }

        $courierCode = $this->getCourierCodeFromName($order->courier_name);
        $courier = $this->getCourier($courierCode);

        if (!$courier || !$courier['tracking_url']) {
            return null;
        }

        return str_replace('{tracking}', $order->tracking_number, $courier['tracking_url']);
    }

    /**
     * Get shipping instructions for seller
     */
    private function getShippingInstructions(array $courier, MarketplaceOrder $order): string
    {
        $instructions = "To ship this order with {$courier['name']}:\n\n";
        $instructions .= "1. Contact {$courier['name']}:\n";
        $instructions .= "   Phone: {$courier['phone']}\n";
        
        if ($courier['whatsapp']) {
            $instructions .= "   WhatsApp: {$courier['whatsapp']}\n";
        }
        
        $instructions .= "   Website: {$courier['website']}\n\n";
        $instructions .= "2. Provide package details:\n";
        $instructions .= "   - Order Number: {$order->order_number}\n";
        $instructions .= "   - Delivery Address: {$order->delivery_address['address']}, {$order->delivery_address['district']}, {$order->delivery_address['province']}\n";
        $instructions .= "   - Recipient: {$order->buyer->name}\n";
        $instructions .= "   - Phone: {$order->delivery_address['phone']}\n\n";
        $instructions .= "3. Get tracking number from courier\n\n";
        $instructions .= "4. Enter tracking number in the system\n\n";
        $instructions .= "5. Buyer will be notified automatically";

        return $instructions;
    }

    /**
     * Get courier code from courier name
     */
    private function getCourierCodeFromName(string $courierName): ?string
    {
        foreach ($this->couriers as $code => $courier) {
            if ($courier['name'] === $courierName) {
                return $code;
            }
        }
        return null;
    }

    /**
     * Get shipping rates for checkout
     */
    public function getShippingRatesForCheckout(string $fromProvince, string $toProvince): array
    {
        $rates = [];

        foreach ($this->couriers as $code => $courier) {
            // Check if courier serves destination
            if (!in_array($toProvince, $courier['coverage'])) {
                continue;
            }

            $cost = $this->estimateCost($code, $fromProvince, $toProvince);
            $days = $this->estimateDeliveryDays($code, $fromProvince, $toProvince);

            $rates[] = [
                'code' => $code,
                'name' => $courier['name'],
                'cost' => $cost,
                'formatted_cost' => 'K' . number_format($cost / 100, 2),
                'estimated_days' => $days,
                'estimated_delivery' => now()->addDays($days)->format('M d, Y'),
            ];
        }

        // Sort by cost (cheapest first)
        usort($rates, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return $rates;
    }
}
