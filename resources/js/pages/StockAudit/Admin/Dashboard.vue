<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';

defineProps<{
    stats: {
        total_companies: number;
        active_companies: number;
        total_users: number;
    };
}>();
</script>

<template>
    <StockAuditLayout title="Admin Dashboard">
        <Head title="StockFlow Admin Dashboard" />
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border">
                        <p class="text-sm font-medium text-gray-500">Total Companies</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_companies }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border">
                        <p class="text-sm font-medium text-gray-500">Active Companies</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-1">{{ stats.active_companies }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_users }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('stockflow.admin.companies.create')" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors text-sm font-medium">
                            Create New Company
                        </Link>
                        <Link :href="route('stockflow.admin.companies.index')" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                            Manage Companies
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
