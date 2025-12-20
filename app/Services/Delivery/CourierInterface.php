<?php

namespace App\Services\Delivery;

interface CourierInterface
{
    /**
     * Create a new shipment
     * 
     * @param array $data Shipment details
     * @return array ['tracking_number', 'estimated_delivery', 'cost', 'label_url']
     */
    public function createShipment(array $data): array;

    /**
     * Get shipping rates for a route
     * 
     * @param array $data Route and package details
     * @return array ['cost', 'estimated_days', 'service_name']
     */
    public function getShippingRates(array $data): array;

    /**
     * Track a shipment
     * 
     * @param string $trackingNumber
     * @return array ['status', 'location', 'estimated_delivery', 'events']
     */
    public function trackShipment(string $trackingNumber): array;

    /**
     * Cancel a shipment
     * 
     * @param string $trackingNumber
     * @return bool
     */
    public function cancelShipment(string $trackingNumber): bool;

    /**
     * Get proof of delivery
     * 
     * @param string $trackingNumber
     * @return array|null ['signature_url', 'photo_url', 'delivered_at', 'recipient_name']
     */
    public function getProofOfDelivery(string $trackingNumber): ?array;

    /**
     * Check if courier serves a specific area
     * 
     * @param string $province
     * @param string $district
     * @return bool
     */
    public function servesArea(string $province, string $district): bool;
}
