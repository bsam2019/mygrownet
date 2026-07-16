<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
</script>

<template>
    <Head title="Supplier Portal" />
    <StockFlowLayout title="Supplier Portal">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-bold mb-6">Supplier Portal</h1>
            <p class="text-gray-600">View and manage your purchase orders, update order status, and manage your profile.</p>
        </div>
    </StockFlowLayout>
</template>
