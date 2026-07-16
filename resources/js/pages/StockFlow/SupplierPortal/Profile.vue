<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
</script>

<template>
    <Head title="Supplier Profile" />
    <StockFlowLayout title="Supplier Profile">
        <div class="max-w-4xl mx-auto py-6 px-4">
            <h1 class="text-2xl font-bold mb-6">My Profile</h1>
            <p class="text-gray-600">Manage your supplier profile and contact information.</p>
        </div>
    </StockFlowLayout>
</template>
