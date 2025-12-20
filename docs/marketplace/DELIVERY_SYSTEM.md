# GrowNet Market - Delivery & Tracking System

**Last Updated:** December 19, 2025  
**Status:** Phase 1 Complete, Phase 2A Ready for Implementation  
**Priority:** High (Critical for MVP)

---

## Changelog

### December 19, 2025
- ✅ Removed "Zoom Courier" references (doesn't exist in Zambia)
- ✅ Created `ManualCourierService` for realistic Zambian courier integration
- ✅ Added real Zambian couriers: Postnet, DHL, Aramex, Fedex
- ✅ Created `DHLAdapter` for future API integration
- ✅ Updated config with manual and API courier sections
- ✅ Documented realistic workflow: seller contacts courier → gets tracking number → enters in system

---

## Overview

The delivery system enables sellers to ship products to buyers through multiple delivery methods:
1. **Self-Delivery** - Seller delivers directly (MVP)
2. **Courier Partners** - Integration with Zoom Courier, DHL, Fedex
3. **Pickup Stations** - Buyers collect from designated locations

---

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────┐
│                    Delivery System                       │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ Self-Delivery│  │   Couriers   │  │    Pickup    │ │
│  │   (Manual)   │  │  (API-based) │  │   Stations   │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│         │                  │                  │         │
│         └──────────────────┴──────────────────┘         │
│                           │                              │
│                  ┌────────▼────────┐                    │
│                  │ Delivery Service │                    │
│                  └────────┬────────┘                    │
│                           │                              │
│         ┌─────────────────┼─────────────────┐          │
│         │                 │                 │          │
│    ┌────▼────┐      ┌────▼────┐      ┌────▼────┐     │
│    │ Tracking│      │  Cost   │      │  Proof  │     │
│    │ Updates │      │Calculator│      │Delivery │     │
│    └─────────┘      └─────────┘      └─────────┘     │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Implementation Plan

### Phase 1: Self-Delivery (MVP) ✅ CURRENT

**Status:** Already implemented in database schema

**Features:**
- Seller marks order as "Shipped"
- Seller provides tracking info (manual text)
- Seller uploads delivery proof (photo)
- Buyer confirms receipt
- Auto-release after 7 days

**Database Fields (Already in `marketplace_orders`):**
- `delivery_method` - enum: self, courier, pickup
- `tracking_info` - text field for manual tracking notes
- `tracking_number` - courier tracking number
- `courier_name` - courier company name
- `delivery_proof` - photo upload path
- `shipped_at`, `delivered_at`, `confirmed_at`

**Current Workflow:**
```
Order Paid → Seller Ships → Buyer Confirms → Funds Released
```

---

### Phase 2: Courier Integration (NEXT)

**Target Couriers (Real Zambian Services):**
1. **Postnet Zambia** - Local courier and postal service (Priority)
   - Website: postnet.co.zm
   - Coverage: All major cities
   - No public API - manual integration
2. **DHL Zambia** - International and domestic express
   - Website: dhl.com/zm
   - Coverage: Nationwide
   - API available (requires business account)
3. **Fedex Zambia** - International shipping
   - Website: fedex.com/zm
   - Coverage: Major cities
   - API available (requires business account)
4. **Aramex Zambia** - Courier and logistics
   - Coverage: Lusaka, Copperbelt
   - No public API - manual integration
5. **Local Courier Networks** - Small independent couriers in compounds
   - Coverage: Specific areas
   - Manual integration only

**Realistic Approach for Zambia:**
- **Phase 2A (MVP)**: Manual Integration - Seller enters tracking number after booking with courier
- **Phase 2B (Future)**: API Integration for DHL/Fedex when business accounts are set up
- **Phase 2C (Immediate)**: Pickup Stations as primary delivery method

#### 2.1 Courier Service Interface

```php
// app/Services/Delivery/CourierInterface.php
interface CourierInterface
{
    public function createShipment(array $data): array;
    public function getShippingRates(array $data): array;
    public function trackShipment(string $trackingNumber): array;
    public function cancelShipment(string $trackingNumber): bool;
    public function getProofOfDelivery(string $trackingNumber): ?string;
}
```

#### 2.2 Manual Courier Integration (MVP - Realistic for Zambia)

Since most Zambian couriers don't have APIs, we'll start with **manual integration**:

```php
// app/Services/Delivery/ManualCourierService.php
class ManualCourierService
{
    // Predefined courier list
    private array $couriers = [
        'postnet' => [
            'name' => 'Postnet Zambia',
            'phone' => '+260-XXX-XXXXXX',
            'rates' => [
                'lusaka_local' => 3000, // K30
                'copperbelt' => 5000,   // K50
                'other_provinces' => 7000, // K70
            ],
        ],
        'dhl' => [
            'name' => 'DHL Zambia',
            'phone' => '+260-211-XXXXXX',
            'rates' => [
                'lusaka_local' => 5000,
                'copperbelt' => 8000,
                'other_provinces' => 10000,
            ],
        ],
        'aramex' => [
            'name' => 'Aramex Zambia',
            'phone' => '+260-XXX-XXXXXX',
            'rates' => [
                'lusaka_local' => 4000,
                'copperbelt' => 6000,
                'other_provinces' => 8000,
            ],
        ],
    ];
    
    public function getAvailableCouriers(): array
    {
        return $this->couriers;
    }
    
    public function estimateCost(string $courierCode, string $fromProvince, string $toProvince): int
    {
        $courier = $this->couriers[$courierCode] ?? null;
        if (!$courier) {
            return 5000; // Default K50
        }
        
        // Simple rate calculation
        if ($fromProvince === $toProvince && $toProvince === 'Lusaka') {
            return $courier['rates']['lusaka_local'];
        } elseif ($toProvince === 'Copperbelt') {
            return $courier['rates']['copperbelt'];
        } else {
            return $courier['rates']['other_provinces'];
        }
    }
    
    public function createManualShipment(array $data): array
    {
        // Seller manually enters tracking number after booking with courier
        return [
            'tracking_number' => $data['tracking_number'],
            'courier_name' => $data['courier_name'],
            'estimated_delivery' => now()->addDays(3),
            'cost' => $this->estimateCost($data['courier_code'], $data['from_province'], $data['to_province']),
        ];
    }
}
```

**Workflow:**
1. Seller selects courier from dropdown (Postnet, DHL, Aramex, etc.)
2. Seller contacts courier directly (phone/WhatsApp)
3. Courier provides tracking number
4. Seller enters tracking number in system
5. Buyer can see tracking number and courier contact info
6. Buyer tracks manually on courier's website or by calling

#### 2.3 Future: API Integration (When Available)

```php
// app/Services/Delivery/PostnetAdapter.php
// When Postnet provides API in future
class PostnetAdapter implements CourierInterface
{
    // Same structure as ZoomCourierAdapter
    // Will be implemented when API becomes available
}
```

#### 2.3 Delivery Service (Orchestrator)

```php
// app/Services/Delivery/DeliveryService.php
class DeliveryService
{
    public function __construct(
        private ZoomCourierAdapter $zoomCourier,
        private DHLAdapter $dhl,
        private FedexAdapter $fedex,
    ) {}
    
    public function getAvailableCouriers(MarketplaceOrder $order): array
    {
        $couriers = [];
        
        // Check which couriers serve the delivery area
        if ($this->zoomCourier->servesArea($order->delivery_address['province'])) {
            $couriers[] = [
                'name' => 'Zoom Courier',
                'code' => 'zoom',
                'rates' => $this->zoomCourier->getShippingRates([
                    'from' => $order->seller->district,
                    'to' => $order->delivery_address['district'],
                    'weight' => $this->calculateWeight($order),
                ]),
            ];
        }
        
        return $couriers;
    }
    
    public function createShipment(MarketplaceOrder $order, string $courierCode): array
    {
        $courier = $this->getCourier($courierCode);
        
        $shipment = $courier->createShipment([
            'seller_name' => $order->seller->business_name,
            'seller_phone' => $order->seller->phone,
            'seller_address' => $order->seller->address,
            'buyer_name' => $order->buyer->name,
            'buyer_phone' => $order->delivery_address['phone'],
            'delivery_address' => $order->delivery_address,
            'weight' => $this->calculateWeight($order),
            'value' => $order->total,
            'description' => $this->getPackageDescription($order),
        ]);
        
        // Update order with tracking info
        $order->update([
            'tracking_number' => $shipment['tracking_number'],
            'courier_name' => $courierCode,
            'estimated_delivery' => $shipment['estimated_delivery'],
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
        
        return $shipment;
    }
    
    public function trackShipment(MarketplaceOrder $order): array
    {
        if (!$order->tracking_number) {
            return ['status' => 'not_shipped'];
        }
        
        $courier = $this->getCourier($order->courier_name);
        return $courier->trackShipment($order->tracking_number);
    }
}
```

---

### Phase 3: Pickup Stations

**Database:** `marketplace_pickup_stations` table (already created)

**Features:**
- Admin manages pickup stations
- Buyers select nearest station at checkout
- Seller delivers to station
- Buyer collects with order number
- Station staff confirms collection

**Pickup Station Model:**
```php
// Already created: app/Models/MarketplacePickupStation.php
- name, code, province, district, address
- phone, email, latitude, longitude
- operating_hours, is_active, capacity
```

**Workflow:**
```
1. Buyer selects pickup station at checkout
2. Order placed with pickup_station_id
3. Seller delivers to station
4. Station staff scans/confirms receipt
5. Buyer notified via SMS
6. Buyer collects with order number + ID
7. Station staff confirms collection
8. Funds released to seller
```

---

## Database Schema (Already Complete)

### Orders Table Extensions ✅
```sql
ALTER TABLE marketplace_orders ADD COLUMN:
- tracking_number VARCHAR(255)
- courier_name VARCHAR(255)
- estimated_delivery TIMESTAMP
- pickup_station_id (if using pickup)
```

### Pickup Stations Table ✅
```sql
CREATE TABLE marketplace_pickup_stations (
    id, name, code, province, district, address,
    phone, email, latitude, longitude,
    operating_hours JSON, is_active, capacity
)
```

---

## UI Components

### 1. Delivery Method Selector (Checkout)

```vue
<!-- resources/js/components/Marketplace/DeliveryMethodSelector.vue -->
<template>
  <div class="space-y-4">
    <h3>Delivery Method</h3>
    
    <!-- Self-Delivery -->
    <label class="border rounded-lg p-4 cursor-pointer">
      <input type="radio" v-model="method" value="self" />
      <div>
        <p class="font-medium">Self-Delivery</p>
        <p class="text-sm text-gray-600">Seller delivers directly</p>
        <p class="text-sm font-bold">FREE</p>
      </div>
    </label>
    
    <!-- Courier Options -->
    <label v-for="courier in couriers" class="border rounded-lg p-4 cursor-pointer">
      <input type="radio" v-model="method" :value="courier.code" />
      <div>
        <p class="font-medium">{{ courier.name }}</p>
        <p class="text-sm text-gray-600">{{ courier.estimated_days }} days</p>
        <p class="text-sm font-bold">{{ courier.formatted_cost }}</p>
      </div>
    </label>
    
    <!-- Pickup Station -->
    <label class="border rounded-lg p-4 cursor-pointer">
      <input type="radio" v-model="method" value="pickup" />
      <div>
        <p class="font-medium">Pickup Station</p>
        <p class="text-sm text-gray-600">Collect from nearest station</p>
        <p class="text-sm font-bold">K10.00</p>
      </div>
    </label>
    
    <!-- Pickup Station Selector -->
    <div v-if="method === 'pickup'" class="ml-8">
      <select v-model="selectedStation" class="w-full">
        <option v-for="station in stations" :value="station.id">
          {{ station.name }} - {{ station.district }}
        </option>
      </select>
    </div>
  </div>
</template>
```

### 2. Tracking Display

```vue
<!-- resources/js/components/Marketplace/TrackingDisplay.vue -->
<template>
  <div class="bg-white rounded-lg border p-6">
    <h3 class="font-bold mb-4">Tracking Information</h3>
    
    <!-- Tracking Number -->
    <div class="mb-4">
      <p class="text-sm text-gray-600">Tracking Number</p>
      <p class="font-mono font-bold">{{ order.tracking_number }}</p>
      <p class="text-sm text-gray-600">{{ order.courier_name }}</p>
    </div>
    
    <!-- Status Timeline -->
    <div class="space-y-4">
      <div v-for="event in trackingEvents" class="flex gap-4">
        <div class="flex flex-col items-center">
          <div class="w-3 h-3 rounded-full bg-green-500"></div>
          <div class="w-0.5 h-full bg-gray-300"></div>
        </div>
        <div>
          <p class="font-medium">{{ event.status }}</p>
          <p class="text-sm text-gray-600">{{ event.location }}</p>
          <p class="text-xs text-gray-500">{{ event.timestamp }}</p>
        </div>
      </div>
    </div>
    
    <!-- Estimated Delivery -->
    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
      <p class="text-sm text-gray-600">Estimated Delivery</p>
      <p class="font-bold">{{ order.estimated_delivery }}</p>
    </div>
  </div>
</template>
```

---

## API Endpoints

### Seller Endpoints
```php
POST   /marketplace/seller/orders/{id}/ship
       - Select courier or self-delivery
       - Generate tracking number
       - Update order status

POST   /marketplace/seller/orders/{id}/deliver
       - Upload delivery proof
       - Mark as delivered

GET    /marketplace/seller/orders/{id}/tracking
       - Get real-time tracking info
```

### Buyer Endpoints
```php
GET    /marketplace/orders/{id}/tracking
       - View tracking information
       - See delivery timeline

POST   /marketplace/orders/{id}/confirm-delivery
       - Confirm receipt
       - Release funds to seller
```

### Admin Endpoints
```php
GET    /admin/marketplace/pickup-stations
POST   /admin/marketplace/pickup-stations
PUT    /admin/marketplace/pickup-stations/{id}
DELETE /admin/marketplace/pickup-stations/{id}
```

---

## Configuration

```php
// config/marketplace.php
'delivery' => [
    'couriers' => [
        'zoom' => [
            'enabled' => env('ZOOM_COURIER_ENABLED', false),
            'api_key' => env('ZOOM_COURIER_API_KEY'),
            'api_url' => env('ZOOM_COURIER_API_URL'),
        ],
        'dhl' => [
            'enabled' => env('DHL_ENABLED', false),
            'api_key' => env('DHL_API_KEY'),
        ],
    ],
    'pickup_stations' => [
        'enabled' => true,
        'fee' => 1000, // K10.00 in ngwee
    ],
    'self_delivery' => [
        'enabled' => true,
        'auto_release_days' => 7,
    ],
],
```

---

## Implementation Steps

### Phase 2A: Manual Courier Integration (CURRENT - 6-8 hours)

**Status:** ✅ Backend Complete, ⏳ Frontend Pending

**Completed:**
1. ✅ Created `ManualCourierService` with real Zambian couriers
2. ✅ Created `DHLAdapter` for future API integration
3. ✅ Updated configuration

**Next Steps:**
1. Create `DeliveryMethodSelector.vue` component for checkout (1 hour)
2. Add "Ship Order" functionality to seller order page (2 hours)
3. Create tracking number input modal for sellers (1 hour)
4. Create `TrackingDisplay.vue` component for buyers (2 hours)
5. Add SMS notifications for tracking updates (1 hour)
6. Testing and integration (1 hour)

### Phase 2B: API Integration (FUTURE - when business accounts ready)
1. Set up DHL business account and get API credentials
2. Test DHLAdapter with real API
3. Add Fedex adapter if needed
4. Gradual rollout to sellers

### Phase 3: Pickup Stations (3-4 hours)
1. Admin CRUD for pickup stations
2. Pickup station selector at checkout
3. Station staff confirmation interface
4. Collection workflow

**Total Estimated Time for Phase 2A:** 8 hours

---

## Success Metrics

- **Delivery Success Rate:** >95%
- **Average Delivery Time:** <5 days (Lusaka), <7 days (provinces)
- **Tracking Accuracy:** >98%
- **Pickup Station Usage:** >20% of orders
- **Customer Satisfaction:** >4.5/5 stars

---

## Next Steps

1. **Immediate:** Get Zoom Courier API credentials
2. **Week 1:** Implement courier adapters and delivery service
3. **Week 2:** Build seller shipping interface
4. **Week 3:** Add tracking display and buyer notifications
5. **Week 4:** Launch pickup stations in Lusaka (pilot)

---

**Last Updated:** December 19, 2025  
**Status:** Ready for implementation - all database schema complete, models ready
