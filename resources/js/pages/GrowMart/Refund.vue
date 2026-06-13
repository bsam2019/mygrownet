<template>
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Refund Policy</h1>
        <div class="prose prose-gray max-w-none space-y-4">
            <p>Last updated: June 2026</p>
            <h2 class="text-xl font-semibold mt-8">Cancellation</h2>
            <p>You may cancel your order while it is in <strong>pending</strong> or <strong>confirmed</strong> status. Once an order is processing or out for delivery, cancellations are no longer accepted.</p>
            <h2 class="text-xl font-semibold mt-8">Refunds</h2>
            <p>If you cancel a pending order before payment verification, no charge will be applied. If payment has already been verified, a refund will be processed within 5–7 business days to your original payment method.</p>
            <h2 class="text-xl font-semibold mt-8">Damaged or Incorrect Items</h2>
            <p>If you receive damaged or incorrect items, please contact us within 48 hours of delivery. We will arrange a replacement or full refund.</p>
            <h2 class="text-xl font-semibold mt-8">Contact</h2>
            <p>For refund requests, please <Link :href="route('growmart.contact')" class="text-emerald-600 underline">contact us</Link> or email {{ contactEmail }}.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    contactEmail: string;
}>();
</script>
