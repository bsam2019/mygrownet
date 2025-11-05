<template>
    <AdminLayout>
        <Head title="KPI Management" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">KPI Management</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage key performance indicators for positions
                        </p>
                    </div>
                    <button
                        @click="showCreateModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add KPI
                    </button>
                </div>

                <!-- KPIs by Department -->
                <div class="space-y-6">
                    <div v-for="(departmentKpis, departmentName) in kpis" :key="departmentName" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ departmentName }}</h3>
                            
                            <div class="space-y-4">
                                <div v-for="kpi in departmentKpis" :key="kpi.id" class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ kpi.kpi_name }}</h4>
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ kpi.position.title }}
                                                </span>
                                                <span v-if="!kpi.is_active" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    Inactive
                                                </span>
                                            </div>
                                            <p v-if="kpi.kpi_description" class="text-sm text-gray-600 mt-1">{{ kpi.kpi_description }}</p>
                                            
                                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                <span>Target: <strong class="text-gray-900">{{ kpi.target_value }} {{ kpi.measurement_unit }}</strong></span>
                                                <span>Frequency: <strong class="text-gray-900">{{ formatFrequency(kpi.measurement_frequency) }}</strong></span>
                                            </div>
                                        </div>

                                        <div class="ml-4 flex items-center space-x-2">
                                            <button
                                                @click="editKpi(kpi)"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="Object.keys(kpis).length === 0" class="bg-white shadow rounded-lg">
                        <div class="px-4 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No KPIs defined</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first KPI.</p>
                            <div class="mt-6">
                                <button
                                    @click="showCreateModal = true"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                                >
                                    Add KPI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit KPI Modal -->
        <KpiModal
            :show="showCreateModal || showEditModal"
            :kpi="selectedKpi"
            :positions="positions"
            @close="closeModal"
            @saved="handleSaved"
        />
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import KpiModal from '@/components/Admin/Organization/KpiModal.vue';

defineProps({
    kpis: {
        type: Object,
        required: true
    },
    positions: {
        type: Array,
        required: true
    }
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const selectedKpi = ref(null);

const editKpi = (kpi) => {
    selectedKpi.value = kpi;
    showEditModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    selectedKpi.value = null;
};

const handleSaved = () => {
    closeModal();
};

const formatFrequency = (frequency) => {
    const labels = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'monthly': 'Monthly',
        'quarterly': 'Quarterly',
        'annual': 'Annual'
    };
    return labels[frequency] || frequency;
};
</script>
