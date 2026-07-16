<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
</script>

<template>
    <Head title="My Orders" />
    <StockFlowLayout title="My Orders">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-bold mb-6">My Purchase Orders</h1>
            <p class="text-gray-600">Purchase orders from your clients will appear here.</p>
        </div>
    </StockFlowLayout>
</template>
