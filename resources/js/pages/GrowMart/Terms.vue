<template>
    <div class="max-w-3xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Terms of Service</h1>
        <div class="prose prose-gray max-w-none space-y-4">
            <p>Last updated: June 2026</p>
            <h2 class="text-xl font-semibold mt-8">1. Introduction</h2>
            <p>Welcome to GrowMart. By placing an order through our platform, you agree to these Terms of Service.</p>
            <h2 class="text-xl font-semibold mt-8">2. Orders & Payment</h2>
            <p>All orders are subject to product availability. Payment must be made via one of our accepted methods. Orders are only processed once payment is confirmed.</p>
            <h2 class="text-xl font-semibold mt-8">3. Delivery</h2>
            <p>We offer Yango delivery within Lusaka, own vehicle collection, and store pickup. Delivery times are estimates and not guaranteed.</p>
            <h2 class="text-xl font-semibold mt-8">4. Cancellations & Refunds</h2>
            <p>Orders can be cancelled while in pending or confirmed status. Please see our <Link :href="route('growmart.refund')" class="text-emerald-600 underline">Refund Policy</Link> for details.</p>
            <h2 class="text-xl font-semibold mt-8">5. Contact</h2>
            <p>For questions, please <Link :href="route('growmart.contact')" class="text-emerald-600 underline">contact us</Link> or email {{ contactEmail }}.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    contactEmail: string;
}>();
</script>
