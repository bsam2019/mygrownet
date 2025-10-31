<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { BarChart3, TrendingUp, DollarSign, CheckCircle } from 'lucide-vue-next';

interface Stats {
    total_applications: number;
    approval_rate: number;
    average_score: number;
    total_funded: number;
    total_repaid: number;
    success_rate: number;
}

defineProps<{
    stats: Stats;
    applicationsByStatus: Array<{ status: string; count: number }>;
    projectsByStatus: Array<{ status: string; count: number }>;
    fundingByBusinessType: Array<{ business_type: string; total: number }>;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="BGF Analytics" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">BGF Analytics</h1>
                <p class="mt-2 text-gray-600">Performance metrics and insights</p>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Applications</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.total_applications }}</p>
                        </div>
                        <BarChart3 class="h-8 w-8 text-blue-600" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Approval Rate</p>
                            <p class="text-3xl font-bold text-green-600 mt-2">{{ stats.approval_rate }}%</p>
                        </div>
                        <CheckCircle class="h-8 w-8 text-green-600" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Funded</p>
                            <p class="text-2xl font-bold text-blue-600 mt-2">{{ formatCurrency(stats.total_funded) }}</p>
                        </div>
                        <DollarSign class="h-8 w-8 text-blue-600" />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Success Rate</p>
                            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ stats.success_rate }}%</p>
                        </div>
                        <TrendingUp class="h-8 w-8 text-emerald-600" />
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Applications by Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Applications by Status</h3>
                    <div class="space-y-3">
                        <div v-for="item in applicationsByStatus" :key="item.status" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 capitalize">{{ item.status.replace('_', ' ') }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ item.count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Projects by Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Projects by Status</h3>
                    <div class="space-y-3">
                        <div v-for="item in projectsByStatus" :key="item.status" class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 capitalize">{{ item.status.replace('_', ' ') }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ item.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Funding by Business Type -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Funding by Business Type</h3>
                <div class="space-y-3">
                    <div v-for="item in fundingByBusinessType" :key="item.business_type" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 capitalize">{{ item.business_type }}</span>
                        <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(item.total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
